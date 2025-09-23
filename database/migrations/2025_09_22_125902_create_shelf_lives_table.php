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
        Schema::create('shelf_lives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('operation_id')->nullable();
            $table->integer('store_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->string('product_name')->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('days_to_expire')->nullable();
            $table->string('condition')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shelf_lives');
    }
};
