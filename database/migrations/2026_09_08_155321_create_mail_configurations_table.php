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
        Schema::create('mail_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('smtp_host');
            $table->unsignedInteger('smtp_port');
            $table->string('username');
            $table->string('password');
            $table->string('encryption');
            $table->string('from_name');
            $table->string('from_email');
            $table->string('reply_to_email')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_configurations');
    }
};
