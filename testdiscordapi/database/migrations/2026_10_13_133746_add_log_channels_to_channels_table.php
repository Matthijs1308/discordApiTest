<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE channels
            ADD COLUMN deletion_logs_channel_id BIGINT DEFAULT NULL,
            ADD COLUMN edit_logs_channel_id BIGINT DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE channels
            DROP COLUMN deletion_logs_channel_id,
            DROP COLUMN edit_logs_channel_id");
    }
};
