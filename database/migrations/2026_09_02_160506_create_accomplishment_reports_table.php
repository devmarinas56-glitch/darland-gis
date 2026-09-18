<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accomplishment_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('program');
            $table->date('date_of_activity');
            $table->text('description');
            $table->json('documents')->nullable();       // array of stored file paths
            $table->enum('status', ['pending', 'approved', 'returned'])->default('pending');
            $table->text('reviewer_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accomplishment_reports');
    }
};
