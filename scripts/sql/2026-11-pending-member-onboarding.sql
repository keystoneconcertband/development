-- Migration: Pending Member Activation, Self-Service Info Completion & Reminders
-- Target table: kcb_members

ALTER TABLE `kcb_members`
    ADD COLUMN `pending_info` TINYINT(1) NOT NULL DEFAULT 0 AFTER `accountType`,
    ADD COLUMN `activation_token` VARCHAR(64) NULL DEFAULT NULL AFTER `pending_info`,
    ADD COLUMN `activated_dt_tm` DATETIME NULL DEFAULT NULL AFTER `activation_token`,
    ADD COLUMN `info_completed_dt_tm` DATETIME NULL DEFAULT NULL AFTER `activated_dt_tm`,
    ADD COLUMN `reminder_count` INT NOT NULL DEFAULT 0 AFTER `info_completed_dt_tm`,
    ADD COLUMN `last_reminder_dt_tm` DATETIME NULL DEFAULT NULL AFTER `reminder_count`,
    ADD INDEX `idx_kcb_members_activation_token` (`activation_token`),
    ADD INDEX `idx_kcb_members_pending_info` (`pending_info`, `disabled`, `accountType`);
