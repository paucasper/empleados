<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absence_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('signer_user_id')->nullable();

            $table->string('sap_employee_id', 20);
            $table->string('awart', 10);
            $table->date('begda');
            $table->date('endda');

            $table->string('description')->nullable();
            $table->text('comment')->nullable();
            $table->string('location')->nullable();
            $table->string('phone')->nullable();

            $table->string('status', 50)->default('pending_employee_signature');

            $table->timestamp('employee_signed_at')->nullable();
            $table->timestamp('signer_signed_at')->nullable();

            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->string('sap_file_name')->nullable();
            $table->timestamp('sap_exported_at')->nullable();

            $table->timestamps();

            $table->foreign('signer_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_requests');
    }
};