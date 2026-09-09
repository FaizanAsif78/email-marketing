<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulk_mailings', function (Blueprint $table) {
            $table->string('job_batch_id')->nullable()->after('status');
            $table->timestamp('completed_at')->nullable()->after('job_batch_id');

            $table->index('job_batch_id');
        });
    }

    public function down(): void
    {
        Schema::table('bulk_mailings', function (Blueprint $table) {
            $table->dropIndex(['job_batch_id']);
            $table->dropColumn(['job_batch_id', 'completed_at']);
        });
    }
};