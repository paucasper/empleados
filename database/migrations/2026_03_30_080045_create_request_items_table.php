<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')
                ->constrained('requests')
                ->cascadeOnDelete();

            $table->string('expense_type'); // kilometraje | otros_gastos | media_dieta | dieta_completa
            $table->date('expense_date');

            $table->string('description')->nullable();

            // Lógica:
            // - otros_gastos -> usa amount
            // - resto -> usa quantity y se puede calcular amount
            $table->decimal('quantity', 10, 2)->nullable();
            $table->decimal('unit_amount', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();

            $table->boolean('is_card_payment')->default(false);

            $table->string('ticket_path')->nullable();
            $table->string('ticket_original_name')->nullable();

            $table->timestamps();

            $table->index('request_id');
            $table->index('expense_type');
            $table->index('expense_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_items');
    }
};