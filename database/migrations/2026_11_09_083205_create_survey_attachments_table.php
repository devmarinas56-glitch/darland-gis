<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('survey_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_survey_id')->nullable()->constrained('land_surveys')->cascadeOnDelete();
            $table->foreignId('survey_lot_id')->nullable()->constrained('survey_lots')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->text('file_data');                     // base64-encoded binary — survives Render restarts
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_attachments');
    }
};
