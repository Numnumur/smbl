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
        Schema::create('pickup_deliveries', function (Blueprint $table) {
            $table->id();
            $table->datetime('date_and_time')->nullable();
            $table->enum('type', ['Antar', 'Jemput']);
            $table->enum('status', ['Menunggu Konfirmasi', 'Sudah Dikonfirmasi', 'Selesai', 'Ditolak']);
            $table->text('customer_note')->nullable();
            $table->text('laundry_note')->nullable();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickup_deliveries');
    }
};
