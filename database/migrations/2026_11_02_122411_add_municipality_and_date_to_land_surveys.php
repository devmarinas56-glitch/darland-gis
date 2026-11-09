<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('land_surveys', function (Blueprint $table) {
            $table->string('municipality')->nullable()->after('original_owner');
            $table->date('date_registered')->nullable()->after('municipality');
        });
    }

    public function down(): void
    {
        Schema::table('land_surveys', function (Blueprint $table) {
            $table->dropColumn(['municipality', 'date_registered']);
        });
    }
};
