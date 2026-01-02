<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property string $first_name
 * @property string $last_name
 * @property string|null $middle_name
 * @property string|null $dob
 * @property string|null $citizenship
 * @property string|null $phone
 * @property string|null $emergency_c_name
 * @property string|null $emergency_c_phone
 * @property string|null $language
 * @property string|null $gender
 * @property string $user_id
 * @property int $id
 */
class UserDetail extends Model {
    use HasFactory;
    protected $fillable = [
        'user_id',
        'gender',
        'first_name',
        'dob',
        'middle_name',
        'last_name',
        'citizenship',
        'phone',
        'avatar',
        'emergency_c_name',
        'emergency_c_phone',
        'language',
    ];
    protected $appends = ['full_name', 'short_name'];
    protected $casts = [
        'avatar' => 'array',
    ];

    public const DEFAULT_BADGE_TEXT = '#FFFFFF';
    public const DEFAULT_BADGE_BACKGROUND = '#4E4E4E';

    /**
     * Mutator: Set Personal Details to Uppercase.
     */
    public function setFirstNameAttribute($value) {
        $this->attributes['first_name'] = strtoupper($value);
    }

    public function setLastNameAttribute($value) {
        $this->attributes['last_name'] = strtoupper($value);
    }

    public function setMiddleNameAttribute($value) {
        $this->attributes['middle_name'] = strtoupper($value);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute() {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }
    public function getShortNameAttribute() {
        return "{$this->first_name} {$this->last_name}";
    }

    public function badgeColors(): Attribute {
        return Attribute::get(function () {
            $avatar = $this->avatar;
            $badge = $avatar['badge'] ?? [];

            $text = $this->isValidHex($badge['text'] ?? null) ? strtoupper($badge['text']) : self::DEFAULT_BADGE_TEXT;

            $bg = $this->isValidHex($badge['background'] ?? null)
                ? strtoupper($badge['background'])
                : self::DEFAULT_BADGE_BACKGROUND;

            return ['text' => $text, 'background' => $bg];
        });
    }

    public function badgeStyle(): array {
        $c = $this->badge_colors;
        return ['color' => $c['text'], 'backgroundColor' => $c['background']];
    }

    protected function isValidHex(?string $hex): bool {
        if (!$hex) {
            return false;
        }
        return (bool) preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $hex);
    }
}
