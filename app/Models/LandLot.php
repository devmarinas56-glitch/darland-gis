<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandLot extends Model
{
    protected $fillable = [
        'land_id', 'owner_name', 'barangay', 'location',
        'land_type', 'area', 'status', 'date_registered',
        'notes', 'geojson', 'user_id',
    ];

    protected $casts = [
        'date_registered' => 'date',
    ];

    public function getLandTypeBadgeColorAttribute(): string
    {
        return match($this->land_type) {
            'residential'  => 'badge-residential',
            'commercial'   => 'badge-commercial',
            'agricultural' => 'badge-agricultural',
            'industrial'   => 'badge-industrial',
            default        => 'badge-residential',
        };
    }

    public function getMapColorAttribute(): string
    {
        return match($this->land_type) {
            'residential'  => '#4caf50',
            'commercial'   => '#ff9800',
            'agricultural' => '#9c27b0',
            'industrial'   => '#f44336',
            default        => '#4caf50',
        };
    }
}
