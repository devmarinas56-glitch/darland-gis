<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('land_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('lsn')->unique();          // Land Survey Number e.g. "Psd-123456"
            $table->string('original_owner');         // Owner before division
            $table->decimal('total_area', 12, 2);     // Total area in sqm
            $table->decimal('center_lat', 10, 7);     // Map center latitude
            $table->decimal('center_lng', 10, 7);     // Map center longitude
            $table->integer('lot_count');             // How many lots it was divided into
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Replace old land_lots with new structure
        Schema::create('survey_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_survey_id')->constrained()->cascadeOnDelete();
            $table->integer('lot_number');            // 1, 2, 3 ... within the LSN
            $table->string('owner_name');             // Current owner of this lot
            $table->decimal('area', 12, 2);           // Computed area in sqm
            $table->text('polygons');                 // JSON array of rectangles [[lat,lng,lat,lng], ...]
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_lots');
        Schema::dropIfExists('land_surveys');
    }
};
