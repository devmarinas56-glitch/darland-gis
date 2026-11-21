<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('survey_attachments', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('file_data');
            $table->boolean('needs_correction')->default(false)->after('remarks');
        });
    }

    public function down(): void
    {
        Schema::table('survey_attachments', function (Blueprint $table) {
            $table->dropColumn(['remarks', 'needs_correction']);
        });
    }
};
