<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccomplishmentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'program',
        'date_of_activity',
        'description',
        'documents',
        'status',
        'reviewer_notes',
    ];

    protected $casts = [
        'documents'        => 'array',
        'date_of_activity' => 'date',
    ];

    // ── Relationships ───────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ──────────────────────────────────────────────────
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('date_of_activity', $year);
    }
}
