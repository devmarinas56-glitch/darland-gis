<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandSurvey extends Model
{
    protected $fillable = [
        'lsn', 'original_owner', 'total_area',
        'center_lat', 'center_lng', 'lot_count',
        'notes', 'created_by',
    ];

    public function lots()
    {
        return $this->hasMany(SurveyLot::class)->orderBy('lot_number');
    }

    // Area per lot (equal division)
    public function getAreaPerLotAttribute(): float
    {
        return $this->lot_count > 0 ? round($this->total_area / $this->lot_count, 2) : 0;
    }
}
