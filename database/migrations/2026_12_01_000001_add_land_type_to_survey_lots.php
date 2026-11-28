<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('survey_lots', function (Blueprint $table) {
            $table->string('land_type', 50)->nullable()->default('agricultural')->after('area');
        });
    }

    public function down(): void
    {
        Schema::table('survey_lots', function (Blueprint $table) {
            $table->dropColumn('land_type');
        });
    }
};
