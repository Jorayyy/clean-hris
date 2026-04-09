<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('db:export-sqlite-to-sql {path?}')]
#[Description('Export SQLite database to standard SQL for deployment to Hostinger (MySQL)')]
class ExportSqliteToSql extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = base_path('database/database.sqlite');
        if (!file_exists($path)) {
            $this->error('SQLite database not found at ' . $path);
            return;
        }

        $sqlPath = $this->argument('path') ?? base_path('database/sql/hostinger_import.sql');
        $directory = dirname($sqlPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        try {
            $db = new \PDO("sqlite:" . $path);
            $output = "-- Database structure for Hostinger Import\n-- Created at: " . now() . "\n\n";

            // Get tables
            $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                // Drop table if exists
                $output .= "DROP TABLE IF EXISTS `$table`;\n";
                
                // Get create table sql
                $createSql = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'")->fetchColumn();
                // Replace some sqlite specific syntax to be more compatible
                $createSql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $createSql);
                $createSql = str_replace(['"','`'], '`', $createSql); 
                $output .= $createSql . ";\n\n";

                // Get data
                $rows = $db->query("SELECT * FROM `$table`")->fetchAll(\PDO::FETCH_ASSOC);
                if (count($rows) > 0) {
                    $output .= "INSERT INTO `$table` VALUES \n";
                    $values = [];
                    foreach ($rows as $row) {
                        $escapedValues = array_map(function($val) use ($db) {
                            if ($val === null) return 'NULL';
                            // Special case for payroll_system numeric/date strings
                            return $db->quote($val);
                        }, array_values($row));
                        $values[] = "(" . implode(', ', $escapedValues) . ")";
                    }
                    $output .= implode(",\n", $values) . ";\n\n";
                }
            }

            file_put_contents($sqlPath, $output);
            $this->info("Exported " . count($tables) . " tables to " . $sqlPath);
            $this->warn("Reminder: Review data types (like TEXT vs DATETIME) for MySQL/Postgres compatibility on Hostinger.");
        } catch (\Exception $e) {
            $this->error("Error exporting: " . $e->getMessage());
        }
    }
}
