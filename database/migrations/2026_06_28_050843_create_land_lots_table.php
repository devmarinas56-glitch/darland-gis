<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('land_lots', function (Blueprint $table) {
            $table->id();
            $table->string('land_id')->unique();           // e.g. 10293
            $table->string('owner_name');
            $table->string('barangay');
            $table->string('location');                    // full address
            $table->enum('land_type', ['residential', 'commercial', 'agricultural', 'industrial'])->default('residential');
            $table->decimal('area', 10, 2)->nullable();    // in sqm
            $table->enum('status', ['registered', 'pending', 'rejected'])->default('pending');
            $table->date('date_registered')->nullable();
            $table->text('notes')->nullable();
            $table->text('geojson')->nullable();           // polygon coordinates as JSON
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('land_lots');
    }
};
