<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_lists', function (Blueprint $table) {
            $table->id();

            $table->date('date');
            $table->string('name');
             $table->string('mobile')->nullable();
            $table->string('address')->nullable();
            $table->string('profession')->nullable();
            $table->enum('status', ['Interested','Highly Motivated','Not Interested'])->default('Interested');
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_lists');
    }
};
