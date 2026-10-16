<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        DB::statement("
            CREATE TABLE IF NOT EXISTS `roles` (
              `guild_id` BIGINT NOT NULL,
              `been_setup` TINYINT(1) NOT NULL DEFAULT 0,
              `+_role_id` BIGINT DEFAULT NULL,
              `owner_role_id` BIGINT DEFAULT NULL,
              `community_manager_role_id` BIGINT DEFAULT NULL,
              `developer_role_id` BIGINT DEFAULT NULL,
              `loa_role_id` BIGINT DEFAULT NULL,
              `sr_mod_role_id` BIGINT DEFAULT NULL,
              `mod_role_id` BIGINT DEFAULT NULL,
              `trial_mod_role_id` BIGINT DEFAULT NULL,
              `staff_team_role_id` BIGINT DEFAULT NULL,
              `bot_role_id` BIGINT DEFAULT NULL,
              `content_creator_role_id` BIGINT DEFAULT NULL,
              `level_20_role_id` BIGINT DEFAULT NULL,
              `level_15_role_id` BIGINT DEFAULT NULL,
              `level_10_role_id` BIGINT DEFAULT NULL,
              `level_5_role_id` BIGINT DEFAULT NULL,
              `member_role_id` BIGINT DEFAULT NULL,
              PRIMARY KEY (`guild_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS `channels` (
              `guild_id` BIGINT NOT NULL,
              `rules_channel_id` BIGINT DEFAULT NULL,
              `news_channel_id` BIGINT DEFAULT NULL,
              `faq_channel_id` BIGINT DEFAULT NULL,
              `game_announcements_channel_id` BIGINT DEFAULT NULL,
              `game_updates_channel_id` BIGINT DEFAULT NULL,
              `game_sneaks_channel_id` BIGINT DEFAULT NULL,
              `game_polls_channel_id` BIGINT DEFAULT NULL,
              `game_codes_channel_id` BIGINT DEFAULT NULL,
              `general_channel_id` BIGINT DEFAULT NULL,
              `media_channel_id` BIGINT DEFAULT NULL,
              `exclusive_channel_id` BIGINT DEFAULT NULL,
              `bot_commands_channel_id` BIGINT DEFAULT NULL,
              `bug_reports_channel_id` BIGINT DEFAULT NULL,
              `suggestions_channel_id` BIGINT DEFAULT NULL,
              `lounge_one_id` BIGINT DEFAULT NULL,
              `lounge_two_id` BIGINT DEFAULT NULL,
              `lounge_three_id` BIGINT DEFAULT NULL,
              `lounge_four_id` BIGINT DEFAULT NULL,
              `lounge_five_id` BIGINT DEFAULT NULL,
              `lounge_six_id` BIGINT DEFAULT NULL,
              `staff_rules_channel_id` BIGINT DEFAULT NULL,
              `staff_news_channel_id` BIGINT DEFAULT NULL,
              `staff_chat_channel_id` BIGINT DEFAULT NULL,
              `staff_commands_channel_id` BIGINT DEFAULT NULL,
              `staff_loa_channel_id` BIGINT DEFAULT NULL,
              `staff_banproof_channel_id` BIGINT DEFAULT NULL,
              `staff_vc_id` BIGINT DEFAULT NULL,
              `modlogs_channel_id` BIGINT DEFAULT NULL,
              `automod_logs_channel_id` BIGINT DEFAULT NULL,
              `message_logs_channel_id` BIGINT DEFAULT NULL,
              `admin_announcements_channel_id` BIGINT DEFAULT NULL,
              `admin_chat_channel_id` BIGINT DEFAULT NULL,
              `admin_logs_channel_id` BIGINT DEFAULT NULL,
              `anti_bot_channel_id` BIGINT DEFAULT NULL,
              PRIMARY KEY (`guild_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS `moderation_actions` (
              `id` BIGINT NOT NULL AUTO_INCREMENT,
              `guild_id` BIGINT NOT NULL,
              `user_id` BIGINT NOT NULL,
              `action` VARCHAR(50) NOT NULL,
              `reason` TEXT DEFAULT NULL,
              `mod_id` BIGINT NOT NULL,
              `duration` VARCHAR(50) DEFAULT NULL,
              `case_id` INT NOT NULL,
              `time_of_moderation` DATETIME NOT NULL,
              `username` VARCHAR(100) DEFAULT NULL,
              PRIMARY KEY (`id`),
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS `user_message_history` (
              `id` BIGINT NOT NULL AUTO_INCREMENT,
              `guild_id` BIGINT NOT NULL,
              `user_id` BIGINT NOT NULL,
              `message_id` BIGINT NOT NULL,
              `channel_id` BIGINT NOT NULL,
              `content` TEXT DEFAULT NULL,
              `attachment_urls` TEXT DEFAULT NULL,
              `reply_to_user_id` BIGINT DEFAULT NULL,
              `reply_to_message_id` BIGINT DEFAULT NULL,
              `timestamp` DATETIME NOT NULL,
              PRIMARY KEY (`id`),
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS `temp_moderations` (
              `id` BIGINT NOT NULL AUTO_INCREMENT,
              `guild_id` BIGINT NOT NULL,
              `user_id` BIGINT NOT NULL,
              `unban_time` DATETIME NOT NULL,
              PRIMARY KEY (`id`),
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('temp_moderations');
        Schema::dropIfExists('user_message_history');
        Schema::dropIfExists('moderation_actions');
        Schema::dropIfExists('channels');
        Schema::dropIfExists('roles');
    }
};
