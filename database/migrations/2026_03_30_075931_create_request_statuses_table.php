<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();   // draft, pending_approval, approved, rejected...
            $table->string('name');             // nombre legible
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_statuses');
    }
};