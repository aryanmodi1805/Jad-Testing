<?php

namespace App\Models;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'locked' => 'boolean',
        'payload' => 'array',
        'is_active' => 'boolean',
    ];
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function getFormComponent()
    {
        switch ($this->type) {
            case 'boolean':
                return Checkbox::make('value')->label('Value');
            default:
                return TextInput::make('value')->label('Value');
        }
    }
}
