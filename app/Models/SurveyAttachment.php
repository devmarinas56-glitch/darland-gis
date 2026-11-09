<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyAttachment extends Model
{
    protected $fillable = [
        'land_survey_id', 'survey_lot_id',
        'original_name', 'mime_type', 'file_size',
        'file_data', 'uploaded_by',
    ];

    public function survey()
    {
        return $this->belongsTo(LandSurvey::class, 'land_survey_id');
    }

    public function lot()
    {
        return $this->belongsTo(SurveyLot::class, 'survey_lot_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Decode base64/hex file_data back to raw binary.
     * Handles three PostgreSQL bytea return formats:
     *  1. PHP resource  (pgsql driver sometimes returns a stream)
     *  2. \x<hex>       (PostgreSQL hex escaping)
     *  3. plain base64  (our stored format)
     */
    public function readBinary(): string
    {
        $data = $this->file_data;
        if (is_resource($data)) return stream_get_contents($data);
        $str = (string) $data;
        if (str_starts_with($str, '\\x') || str_starts_with($str, '\x')) {
            return hex2bin(substr($str, 2));
        }
        return base64_decode($str);
    }

    /** Human-readable file size (e.g. "2.4 MB") */
    public function getFileSizeLabelAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    /** Broad category for icon display */
    public function getFileTypeAttribute(): string
    {
        $mime = strtolower($this->mime_type ?? '');
        if (str_contains($mime, 'pdf'))   return 'pdf';
        if (str_contains($mime, 'image')) return 'image';
        if (str_contains($mime, 'word') || str_contains($mime, 'document')) return 'doc';
        if (str_contains($mime, 'sheet') || str_contains($mime, 'excel'))  return 'xls';
        return 'file';
    }
}
