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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->datetime('entry_date');
            $table->datetime('exit_date')->nullable();
            $table->enum('status', ['Baru', 'Selesai Diproses', 'Terkendala', 'Selesai']);
            $table->string('order_package');
            $table->string('type');
            $table->double('price');

            $table->string('discount_name')->nullable();
            $table->enum('discount_type', ['Persentase', 'Langsung'])->nullable();
            $table->double('discount_value')->nullable();
            $table->double('total_price_before_discount')->nullable();
            $table->double('total_price')->nullable();

            $table->double('length')->nullable();
            $table->double('width')->nullable();
            $table->double('weight')->nullable();
            $table->double('quantity')->nullable();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->text('laundry_note')->nullable();
            $table->text('retrieval_proof')->nullable();
            $table->text('delivery_proof')->nullable();
            $table->boolean('whatsapp_notified')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
