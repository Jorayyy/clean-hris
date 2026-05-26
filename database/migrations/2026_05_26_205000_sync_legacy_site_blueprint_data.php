<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Site;
use App\Models\ScheduleGroup;

return new class extends Migration
{
    public function up(): void
    {
        // Sync legacy data: If a Site has a schedule_group_id, 
        // set that site_id on the corresponding ScheduleGroup.
        $sites = Site::whereNotNull('schedule_group_id')->get();
        
        foreach ($sites as $site) {
            ScheduleGroup::where('id', $site->schedule_group_id)
                ->update(['site_id' => $site->id]);
        }
    }

    public function down(): void
    {
        // No reverse needed as this is just a data sync
    }
};
