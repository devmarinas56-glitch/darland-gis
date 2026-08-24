<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyLot extends Model
{
    protected $fillable = [
        'land_survey_id', 'lot_number', 'owner_name',
        'area', 'polygons', 'notes',
    ];

    public function survey()
    {
        return $this->belongsTo(LandSurvey::class, 'land_survey_id');
    }

    public function getParsedPolygonsAttribute(): array
    {
        return json_decode($this->polygons ?? '[]', true) ?: [];
    }
}
