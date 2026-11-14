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
        Schema::create('operational_cashes', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('time')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20); // 'pengeluaran' (Belanja Barang) | 'tabungan' (Setoran/Tabungan)
            $table->string('category', 50)->nullable();
            $table->string('name');
            $table->unsignedBigInteger('amount');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('date');
            $table->index('branch_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_cashes');
    }
};
