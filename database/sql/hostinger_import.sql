-- Database structure for Hostinger Import
-- Created at: 2026-04-09 20:48:27

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (`id` integer primary key autoincrement not null, `migration` varchar not null, `batch` integer not null);

INSERT INTO `migrations` VALUES 
('1', '0001_01_01_000000_create_users_table', '1'),
('2', '0001_01_01_000001_create_cache_table', '1'),
('3', '0001_01_01_000002_create_jobs_table', '1'),
('4', '2026_04_07_000000_create_payroll_system_tables', '1'),
('5', '2026_04_07_155109_add_role_and_employee_id_to_users_table', '1'),
('6', '2026_04_07_155750_create_support_tickets_table', '1'),
('7', '2026_04_07_161302_add_break_times_to_attendances_table', '1'),
('8', '2026_04_07_161556_add_web_bundy_code_to_employees_table', '1'),
('9', '2026_04_08_000000_add_payroll_groups', '1'),
('10', '2026_04_07_165915_create_schedules_table', '2'),
('11', '2026_04_07_172947_add_registered_ip_to_employees_table', '3'),
('12', '2026_04_07_173448_create_authorized_networks_table', '4'),
('13', '2026_04_07_174249_create_app_settings_table', '5'),
('15', '2026_04_07_202446_optimize_payroll_and_attendance_indices', '6'),
('16', '2026_04_07_204330_create_dtrs_table', '7'),
('17', '2026_04_07_211730_add_personal_info_to_employees_table', '8'),
('18', '2026_04_07_212015_add_employment_info_to_employees_table', '9'),
('19', '2026_04_07_212232_add_account_and_contact_info_to_employees_table', '10'),
('20', '2026_04_07_212411_add_address_and_other_info_to_employees_table', '11'),
('22', '2026_04_07_213033_add_web_bundy_to_app_settings', '12'),
('23', '2026_04_07_213514_rollback_web_bundy_app_settings', '12'),
('24', '2026_04_09_201954_create_audit_logs_table', '12'),
('25', '2026_04_09_202926_add_dtr_edit_password_to_app_settings_table', '13'),
('26', '2026_04_09_204425_add_dtr_password_to_users_table', '14');

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (`email` varchar not null, `token` varchar not null, `created_at` datetime, primary key (`email`));

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (`id` varchar not null, `user_id` integer, `ip_address` varchar, `user_agent` text, `payload` text not null, `last_activity` integer not null, primary key (`id`));

INSERT INTO `sessions` VALUES 
('iafMVTte0zuQnN44NOUkWwWwkTpvctjmkyGQ6MsB', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJJMGp0azdmRlg1bU9VNmNVTTF0UEhUcVJyMWxxOVpvSElkc0MwUURTIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2FkbWluXC9zZXR0aW5ncyIsInJvdXRlIjoiYWRtaW4uc2V0dGluZ3MuaW5kZXgifSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjF9', '1775767599');

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (`key` varchar not null, `value` text not null, `expiration` integer not null, primary key (`key`));

INSERT INTO `cache` VALUES 
('laravel-cache-system_settings', 'a:2:{s:8:"app_name";s:10:"MEBS HIYAS";s:8:"app_logo";s:50:"logos/xtaYuCLjxRGVaKucxOrJZdTp0w5FaBeZ09bFZH5j.jpg";}', '1775853339');

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (`key` varchar not null, `owner` varchar not null, `expiration` integer not null, primary key (`key`));

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (`id` integer primary key autoincrement not null, `queue` varchar not null, `payload` text not null, `attempts` integer not null, `reserved_at` integer, `available_at` integer not null, `created_at` integer not null);

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (`id` varchar not null, `name` varchar not null, `total_jobs` integer not null, `pending_jobs` integer not null, `failed_jobs` integer not null, `failed_job_ids` text not null, `options` text, `cancelled_at` integer, `created_at` integer not null, `finished_at` integer, primary key (`id`));

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (`id` integer primary key autoincrement not null, `uuid` varchar not null, `connection` text not null, `queue` text not null, `payload` text not null, `exception` text not null, `failed_at` datetime not null default CURRENT_TIMESTAMP);

DROP TABLE IF EXISTS `attendances`;
CREATE TABLE `attendances` (`id` integer primary key autoincrement not null, `employee_id` integer not null, `date` date not null, `time_in` time not null, `time_out` time not null, `total_hours` numeric not null, `late_minutes` integer not null, `undertime_minutes` integer not null, `created_at` datetime, `updated_at` datetime, `break1_out` time, `break1_in` time, `break2_out` time, `break2_in` time, foreign key(`employee_id`) references `employees`(`id`) on delete cascade);

INSERT INTO `attendances` VALUES 
('1', '1', '2026-04-06', '08:00', '17:00', '-9', '0', '0', '2026-04-07 16:52:51', '2026-04-07 16:52:51', NULL, NULL, NULL, NULL),
('2', '2', '2026-04-06', '08:30', '17:30', '-9', '-30', '0', '2026-04-07 16:52:51', '2026-04-07 16:52:51', NULL, NULL, NULL, NULL),
('3', '1', '2026-04-07', '08:00', '17:00', '-9', '0', '0', '2026-04-07 16:52:51', '2026-04-07 16:52:51', NULL, NULL, NULL, NULL),
('4', '2', '2026-04-07', '08:30', '17:30', '-9', '-30', '0', '2026-04-07 16:52:51', '2026-04-07 16:52:51', NULL, NULL, NULL, NULL),
('5', '1', '2026-04-08', '08:00', '17:00', '-9', '0', '0', '2026-04-07 16:52:51', '2026-04-07 16:52:51', NULL, NULL, NULL, NULL),
('6', '2', '2026-04-08', '08:30', '17:30', '-9', '-30', '0', '2026-04-07 16:52:51', '2026-04-07 16:52:51', NULL, NULL, NULL, NULL),
('7', '1', '2026-04-09', '08:00', '17:00', '-9', '0', '0', '2026-04-07 16:52:51', '2026-04-07 16:52:51', NULL, NULL, NULL, NULL),
('8', '2', '2026-04-09', '08:30', '17:30', '-9', '-30', '0', '2026-04-07 16:52:51', '2026-04-07 16:52:51', NULL, NULL, NULL, NULL),
('9', '1', '2026-04-10', '08:00', '17:00', '-9', '0', '0', '2026-04-07 16:52:51', '2026-04-07 16:52:51', NULL, NULL, NULL, NULL),
('10', '2', '2026-04-10', '08:30', '17:30', '-9', '-30', '0', '2026-04-07 16:52:51', '2026-04-07 16:52:51', NULL, NULL, NULL, NULL);

DROP TABLE IF EXISTS `payroll_items`;
CREATE TABLE `payroll_items` (`id` integer primary key autoincrement not null, `payroll_id` integer not null, `employee_id` integer not null, `total_days` integer not null, `total_hours` numeric not null, `basic_pay` numeric not null, `overtime_pay` numeric not null, `night_diff` numeric not null, `bonuses` numeric not null, `deductions_sss` numeric not null, `deductions_pagibig` numeric not null, `deductions_philhealth` numeric not null, `other_deductions` numeric not null, `net_pay` numeric not null, `created_at` datetime, `updated_at` datetime, foreign key(`payroll_id`) references `payrolls`(`id`) on delete cascade, foreign key(`employee_id`) references `employees`(`id`) on delete cascade);

INSERT INTO `payroll_items` VALUES 
('4', '3', '1', '5', '-45', '6000', '0', '0', '500', '300', '120', '180', '0', '5900', '2026-04-09 15:49:49', '2026-04-09 15:49:49'),
('5', '4', '2', '5', '-45', '5000', '0', '0', '500', '250', '100', '150', '0', '5000', '2026-04-09 19:30:43', '2026-04-09 19:30:43');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (`id` integer primary key autoincrement not null, `name` varchar not null, `email` varchar not null, `email_verified_at` datetime, `password` varchar not null, `remember_token` varchar, `created_at` datetime, `updated_at` datetime, `role` varchar not null default 'employee', `employee_id` integer, `dtr_password` varchar, foreign key(`employee_id`) references `employees`(`id`) on delete set null);

INSERT INTO `users` VALUES 
('1', 'HR Admin', 'admin@test.com', NULL, '$2y$12$3l/qigz.2XlGIa6HVZEunONoKqcdNTE/DhcP2OgX0PjNHVdqPzIni', NULL, '2026-04-07 16:52:52', '2026-04-07 16:52:52', 'admin', NULL, NULL),
('2', 'John Doe', 'employee@test.com', NULL, '$2y$12$VdD7vnMbNaEY/XAw0kmiLeBl1ems1xpd9tqFI4nGObuUcTG/MP1lS', NULL, '2026-04-07 16:52:52', '2026-04-07 16:52:52', 'employee', '1', NULL);

DROP TABLE IF EXISTS `support_tickets`;
CREATE TABLE `support_tickets` (`id` integer primary key autoincrement not null, `employee_id` integer not null, `type` varchar not null, `subject` varchar not null, `description` text not null, `status` varchar not null default 'pending', `priority` varchar not null default 'normal', `admin_reply` text, `created_at` datetime, `updated_at` datetime, foreign key(`employee_id`) references `employees`(`id`) on delete cascade);

INSERT INTO `support_tickets` VALUES 
('1', '1', 'Forgot Punch', 'DTR correction for April 9', 'sorry po hehe', 'pending', 'normal', NULL, '2026-04-09 20:07:24', '2026-04-09 20:07:24'),
('2', '1', 'Salary Discrepancy', 'payroll/dtr fix', 'hahahaha', 'pending', 'high', NULL, '2026-04-09 20:16:12', '2026-04-09 20:16:12');

DROP TABLE IF EXISTS `payroll_groups`;
CREATE TABLE `payroll_groups` (`id` integer primary key autoincrement not null, `name` varchar not null, `description` varchar, `created_at` datetime, `updated_at` datetime);

INSERT INTO `payroll_groups` VALUES 
('2', 'Tacloban Site', 'Lopez Jaena Street', '2026-04-09 15:40:15', '2026-04-09 15:40:15'),
('3', 'Maasin Site', 'site for maasin', '2026-04-09 19:17:42', '2026-04-09 19:17:42');

DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (`id` integer primary key autoincrement not null, `employee_id` varchar not null, `first_name` varchar not null, `last_name` varchar not null, `email` varchar not null, `position` varchar not null, `daily_rate` numeric not null, `status` varchar not null default ('active'), `created_at` datetime, `updated_at` datetime, `web_bundy_code` varchar, `payroll_group_id` integer, `registered_ip` varchar, `title` varchar, `middle_name` varchar, `name_extension` varchar, `birthday` date, `gender` varchar, `civil_status` varchar, `place_of_birth` varchar, `blood_type` varchar, `citizenship` varchar, `religion` varchar, `photo` varchar, `company` varchar, `location` varchar, `employment_type` varchar, `classification` varchar, `date_employed` date, `tax_code` varchar, `pay_type` varchar, `report_to` varchar, `bank_name` varchar, `account_no` varchar, `tin_no` varchar, `sss_no` varchar, `pagibig_no` varchar, `philhealth_no` varchar, `mobile_no_1` varchar, `mobile_no_2` varchar, `tel_no_1` varchar, `tel_no_2` varchar, `facebook_url` varchar, `twitter_url` varchar, `instagram_url` varchar, `permanent_address_brgy` text, `permanent_address_province` varchar, `present_address_brgy` text, `present_address_province` varchar, `other_information` text, foreign key(`payroll_group_id`) references `payroll_groups`(`id`) on delete set null);

INSERT INTO `employees` VALUES 
('1', '222065', 'John', 'Doe', 'john@admin.com', 'Senior Developer', '1200', 'active', '2026-04-07 16:52:51', '2026-04-09 15:47:09', '222065', '2', NULL, NULL, NULL, NULL, '2001-05-05', 'Male', 'Single', NULL, NULL, NULL, NULL, NULL, 'Your Company Name', 'Tacloban City', 'Regular', 'None', '2026-04-09', 'S/ME', 'Monthly', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '+639319704073', NULL, NULL, NULL, NULL, NULL, NULL, 'Zone 1, Poblacion', 'Leyte', 'Zone 1, Poblacion', 'Leyte', NULL),
('2', '222066', 'Jane', 'Smith', 'jane@hr.com', 'HR Manager', '1000', 'active', '2026-04-07 16:52:51', '2026-04-09 19:29:55', '222066', '3', NULL, NULL, NULL, NULL, '2026-04-10', 'Female', 'Single', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Regular', 'STAFF', '2026-04-01', 'S/ME', 'Weekly', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '+639923542643', NULL, NULL, NULL, NULL, NULL, NULL, 'Pleasantville
Mahayahay, Sagkahan', 'Leyte', 'Pleasantville
Mahayahay, Sagkahan', 'Leyte', NULL),
('3', '222163', 'Reniel', 'Udtohan', 'reniel@gmail.com', 'Staff', '500', 'active', '2026-04-09 20:22:48', '2026-04-09 20:22:48', '222163', '2', NULL, NULL, NULL, NULL, '2003-07-22', 'Male', 'Single', NULL, NULL, NULL, NULL, NULL, NULL, 'Tacloban', 'Regular', 'STAFF', '2026-01-01', 'S/ME', 'Weekly', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Babatngaon', 'Leyte', 'Babatngaon', 'Leyte', NULL);

DROP TABLE IF EXISTS `payrolls`;
CREATE TABLE `payrolls` (`id` integer primary key autoincrement not null, `payroll_code` varchar not null, `start_date` date not null, `end_date` date not null, `pay_date` date not null, `status` varchar not null default ('draft'), `created_at` datetime, `updated_at` datetime, `payroll_group_id` integer, foreign key(`payroll_group_id`) references `payroll_groups`(`id`) on delete cascade);

INSERT INTO `payrolls` VALUES 
('3', 'PAY-20260409-154935', '2026-04-06', '2026-04-10', '2026-04-18', 'processed', '2026-04-09 15:49:46', '2026-04-09 15:49:49', '2'),
('4', 'PAY-20260409-193011', '2026-04-06', '2026-04-10', '2026-04-18', 'processed', '2026-04-09 19:30:27', '2026-04-09 19:30:43', '3');

DROP TABLE IF EXISTS `schedules`;
CREATE TABLE `schedules` (`id` integer primary key autoincrement not null, `name` varchar, `time_in` time not null, `time_out` time not null, `days` varchar not null default '[`Monday`, `Tuesday`, `Wednesday`, `Thursday`, `Friday`]', `payroll_group_id` integer, `employee_id` integer, `created_at` datetime, `updated_at` datetime, foreign key(`payroll_group_id`) references `payroll_groups`(`id`) on delete cascade, foreign key(`employee_id`) references `employees`(`id`) on delete cascade);

DROP TABLE IF EXISTS `authorized_networks`;
CREATE TABLE `authorized_networks` (`id` integer primary key autoincrement not null, `name` varchar not null, `ip_address` varchar not null, `is_active` tinyint(1) not null default '1', `created_at` datetime, `updated_at` datetime);

INSERT INTO `authorized_networks` VALUES 
('1', 'GLOBE TACLOBAN', '138.84.66.187', '1', '2026-04-07 17:38:41', '2026-04-07 17:38:41'),
('2', 'vcode', '127.0.0.1', '1', '2026-04-09 20:26:53', '2026-04-09 20:26:53');

DROP TABLE IF EXISTS `app_settings`;
CREATE TABLE `app_settings` (`id` integer primary key autoincrement not null, `app_name` varchar not null default 'HRIS Payroll', `app_logo` varchar, `created_at` datetime, `updated_at` datetime, `web_bundy_code` varchar, `dtr_edit_password` varchar);

INSERT INTO `app_settings` VALUES 
('1', 'MEBS HIYAS', 'logos/xtaYuCLjxRGVaKucxOrJZdTp0w5FaBeZ09bFZH5j.jpg', '2026-04-07 17:43:07', '2026-04-09 20:35:39', '1234', 'password');

DROP TABLE IF EXISTS `dtrs`;
CREATE TABLE `dtrs` (`id` integer primary key autoincrement not null, `employee_id` integer not null, `start_date` date not null, `end_date` date not null, `total_late_minutes` numeric not null default '0', `total_undertime_minutes` numeric not null default '0', `total_overtime_hours` numeric not null default '0', `total_regular_hours` numeric not null default '0', `total_absent_days` numeric not null default '0', `status` varchar check (`status` in ('draft', 'verified', 'finalized')) not null default 'draft', `verified_by` integer, `finalized_by` integer, `verified_at` datetime, `finalized_at` datetime, `admin_notes` text, `created_at` datetime, `updated_at` datetime, foreign key(`employee_id`) references `employees`(`id`), foreign key(`verified_by`) references `users`(`id`), foreign key(`finalized_by`) references `users`(`id`));

INSERT INTO `dtrs` VALUES 
('3', '2', '2026-04-06 00:00:00', '2026-04-10 00:00:00', '-150', '0', '0', '40', '0', 'finalized', '1', '1', '2026-04-09 20:17:10', '2026-04-09 20:17:19', NULL, '2026-04-09 20:17:05', '2026-04-09 20:17:19'),
('4', '1', '2026-04-06 00:00:00', '2026-04-10 00:00:00', '0', '0', '0', '40', '0', 'finalized', '1', '1', '2026-04-09 20:30:57', '2026-04-09 20:31:28', NULL, '2026-04-09 20:27:15', '2026-04-09 20:31:28');

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (`id` integer primary key autoincrement not null, `user_id` integer, `action` varchar not null, `model_type` varchar not null, `model_id` varchar not null, `details` text, `ip_address` varchar, `user_agent` varchar, `created_at` datetime, `updated_at` datetime, foreign key(`user_id`) references `users`(`id`) on delete set null);

INSERT INTO `audit_logs` VALUES 
('1', '1', 'DTR_DELETION', 'App\Models\Dtr', '1', '{"employee":"John  Doe","period":"2026-04-01 to 2026-04-30","status_at_deletion":"finalized","reason":"Administrative Deletion","ip":"127.0.0.1"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-09 20:26:24', '2026-04-09 20:26:24'),
('2', '1', 'DTR_MANUAL_UPDATE', 'App\Models\Dtr', '5', '{"employee":"Reniel  Udtohan","period":"2026-03-30 to 2026-04-03","old_values":{"id":5,"employee_id":3,"start_date":"2026-03-30T00:00:00.000000Z","end_date":"2026-04-03T00:00:00.000000Z","total_late_minutes":0,"total_undertime_minutes":0,"total_overtime_hours":0,"total_regular_hours":0,"total_absent_days":0,"status":"draft","verified_by":null,"finalized_by":null,"verified_at":null,"finalized_at":null,"admin_notes":null,"created_at":"2026-04-09T20:37:58.000000Z","updated_at":"2026-04-09T20:37:58.000000Z"},"new_values":{"id":5,"employee_id":3,"start_date":"2026-03-30T00:00:00.000000Z","end_date":"2026-04-03T00:00:00.000000Z","total_late_minutes":"0","total_undertime_minutes":"0","total_overtime_hours":"0","total_regular_hours":"0","total_absent_days":0,"status":"draft","verified_by":null,"finalized_by":null,"verified_at":null,"finalized_at":null,"admin_notes":"ahahaha","created_at":"2026-04-09T20:37:58.000000Z","updated_at":"2026-04-09T20:40:01.000000Z","employee":{"id":3,"employee_id":"222163","first_name":"Reniel","last_name":"Udtohan","email":"reniel@gmail.com","position":"Staff","daily_rate":500,"status":"active","created_at":"2026-04-09T20:22:48.000000Z","updated_at":"2026-04-09T20:22:48.000000Z","web_bundy_code":"222163","payroll_group_id":2,"registered_ip":null,"title":null,"middle_name":null,"name_extension":null,"birthday":"2003-07-22","gender":"Male","civil_status":"Single","place_of_birth":null,"blood_type":null,"citizenship":null,"religion":null,"photo":null,"company":null,"location":"Tacloban","employment_type":"Regular","classification":"STAFF","date_employed":"2026-01-01","tax_code":"S\/ME","pay_type":"Weekly","report_to":null,"bank_name":null,"account_no":null,"tin_no":null,"sss_no":null,"pagibig_no":null,"philhealth_no":null,"mobile_no_1":null,"mobile_no_2":null,"tel_no_1":null,"tel_no_2":null,"facebook_url":null,"twitter_url":null,"instagram_url":null,"permanent_address_brgy":"Babatngaon","permanent_address_province":"Leyte","present_address_brgy":"Babatngaon","present_address_province":"Leyte","other_information":null,"full_name":"Reniel  Udtohan"}},"ip":"127.0.0.1"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-09 20:40:01', '2026-04-09 20:40:01'),
('3', '1', 'DTR_DELETION', 'App\Models\Dtr', '5', '{"employee":"Reniel  Udtohan","period":"2026-03-30 to 2026-04-03","status_at_deletion":"finalized","reason":"Administrative Deletion","ip":"127.0.0.1"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-09 20:41:53', '2026-04-09 20:41:53');

