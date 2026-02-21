<?php

namespace App\Models;

use App\Enums\QuestionType;
use App\Observers\CustomAnswerObserver;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

#[ObservedBy(CustomAnswerObserver::class)]
class CustomerAnswer extends Model
{
    use HasFactory, HasUuids;
    use HasTranslations;

    protected $translatable = [
        'question_label',
        'answer_label'
    ];

    protected $fillable = [
        'request_id', 'question_id', 'answer_id', 'val', 'is_custom', 'custom_answer', 'text_answer',
        'is_attachment', 'attachment', 'voice_note', 'voice_note_text', 'voice_note_moderation',
        'is_location', 'latitude', 'longitude', 'time','question_type','question_sort',
        'duration', 'duration_type', "question_label", "answer_label"
    ];

    protected $casts = [
        'attachment' => 'array',
        'question_type' => QuestionType::class,
        'voice_note_moderation' => 'array',
        'is_attachment' => 'boolean',
        'is_custom' => 'boolean',
        'is_location' => 'boolean'
    ];

    protected $appends = [
        'location',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function getAttachmentAttribute($value): ?array
    {
        if (is_array($value)) {
            return $this->convertPathsToUrls($value);
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $this->convertPathsToUrls($decoded);
            }
        }

        return null;
    }

    /**
     * Convert file storage paths to URLs.
     */
    private function convertPathsToUrls(array $paths): array
    {
        return array_map(function ($path) {
            return $path;
//            Storage::disk('public')->url($path);
        }, $paths);
    }

    /**
     * Get the MIME type of the attachment.
     */
    public function getAttachmentMimeType(string $path): string
    {
        return Storage::disk('public')->mimeType($path);
    }


    /**
     * Returns the 'latitude' and 'longitude' attributes as the computed 'location' attribute,
     * as a standard Google Maps style Point array with 'lat' and 'lng' attributes.
     *
     * Used by the Filament Google Maps package.
     *
     * Requires the 'location' attribute be included in this model's $fillable array.
     *
     * @return array
     */

    public function getLocationAttribute(): array
    {
        return [
            "lat" => (float)$this->latitude,
            "lng" => (float)$this->longitude,
        ];
    }

    /**
     * Takes a Google style Point array of 'lat' and 'lng' values and assigns them to the
     * 'latitude' and 'longitude' attributes on this model.
     *
     * Used by the Filament Google Maps package.
     *
     * Requires the 'location' attribute be included in this model's $fillable array.
     *
     * @param ?array $location
     * @return void
     */
    public function setLocationAttribute(?array $location): void
    {
        if (is_array($location)) {
            $this->attributes['latitude'] = $location['lat'];
            $this->attributes['longitude'] = $location['lng'];
            unset($this->attributes['location']);
        }
    }

    /**
     * Get the lat and lng attribute/field names used on this table
     *
     * Used by the Filament Google Maps package.
     *
     * @return string[]
     */
    public static function getLatLngAttributes(): array
    {
        return [
            'lat' => 'latitude',
            'lng' => 'longitude',
        ];
    }

    /**
     * Get the name of the computed location attribute
     *
     * Used by the Filament Google Maps package.
     *
     * @return string
     */
    public static function getComputedLocation(): string
    {
        return 'location';
    }

}
