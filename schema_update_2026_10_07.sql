-- Schema update: brings a database at the 2026_09_05 state up to 2026_10_07.
-- Equivalent to running these 5 Laravel migrations:
--   2026_09_26_170418_add_assigned_on_to_project_assignment_logs_table
--   2026_09_26_174814_add_assigned_from_to_project_assignment_logs_table
--   2026_09_29_191612_add_deca_no_to_projects_table
--   2026_09_30_195050_create_notifications_table
--   2026_10_07_190435_add_deca_no_to_project_assignment_logs_table
-- Back up the database before running. Run once only.

-- 1. project_assignment_logs.assigned_on
ALTER TABLE `project_assignment_logs`
  ADD COLUMN `assigned_on` date DEFAULT NULL AFTER `note`;

UPDATE `project_assignment_logs`
SET `assigned_on` = DATE(`created_at`)
WHERE `action` IN ('ASSIGNED', 'REASSIGNED')
  AND `created_at` IS NOT NULL;

-- 2. project_assignment_logs.assigned_from (+ foreign key to users)
ALTER TABLE `project_assignment_logs`
  ADD COLUMN `assigned_from` bigint(20) unsigned DEFAULT NULL AFTER `assigned_by`,
  ADD KEY `project_assignment_logs_assigned_from_foreign` (`assigned_from`),
  ADD CONSTRAINT `project_assignment_logs_assigned_from_foreign`
    FOREIGN KEY (`assigned_from`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Backfill: for each REASSIGNED log, assigned_from = assignee of the previous
-- ASSIGNED/REASSIGNED log of the same project.
CREATE TEMPORARY TABLE `tmp_previous_assignee` AS
SELECT `id`, `action`,
       LAG(`assigned_to`) OVER (PARTITION BY `project_id` ORDER BY `created_at`, `id`) AS `previous_assignee`
FROM `project_assignment_logs`
WHERE `action` IN ('ASSIGNED', 'REASSIGNED');

UPDATE `project_assignment_logs` l
JOIN `tmp_previous_assignee` t ON t.`id` = l.`id`
SET l.`assigned_from` = t.`previous_assignee`
WHERE t.`action` = 'REASSIGNED'
  AND t.`previous_assignee` IS NOT NULL;

DROP TEMPORARY TABLE `tmp_previous_assignee`;

-- 3. projects.deca_no
ALTER TABLE `projects`
  ADD COLUMN `deca_no` varchar(50) DEFAULT NULL AFTER `work_code`;

-- 4. notifications table
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`, `notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. project_assignment_logs.deca_no
ALTER TABLE `project_assignment_logs`
  ADD COLUMN `deca_no` varchar(50) DEFAULT NULL AFTER `note`;

-- Record the migrations so `php artisan migrate` won't try to run them again.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT m.`migration`, (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`)
FROM (
  SELECT '2026_09_26_170418_add_assigned_on_to_project_assignment_logs_table' AS `migration`
  UNION ALL SELECT '2026_09_26_174814_add_assigned_from_to_project_assignment_logs_table'
  UNION ALL SELECT '2026_09_29_191612_add_deca_no_to_projects_table'
  UNION ALL SELECT '2026_09_30_195050_create_notifications_table'
  UNION ALL SELECT '2026_10_07_190435_add_deca_no_to_project_assignment_logs_table'
) m;
