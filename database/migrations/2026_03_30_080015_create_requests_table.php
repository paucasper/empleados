<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();

            $table->string('type'); // expense | vacation

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('sap_employee_id')->index();

            $table->foreignId('approver_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('status_id')
                ->constrained('request_statuses')
                ->restrictOnDelete();

            $table->string('title')->nullable();
            $table->text('description')->nullable();

            // Flujo de firmas / estados
            $table->timestamp('employee_signature_at')->nullable();
            $table->timestamp('approver_signature_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->text('rejection_reason')->nullable();

            // Integración SAP
            $table->string('sap_file_path')->nullable();
            $table->timestamp('sap_sent_at')->nullable();
            $table->longText('sap_response')->nullable();

            $table->timestamps();

            $table->index('type');
            $table->index('user_id');
            $table->index('approver_user_id');
            $table->index('status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};