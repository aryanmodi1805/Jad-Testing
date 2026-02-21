<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Section;
use Closure;
use Filament\Forms\Components\Concerns\CanBeMarkedAsRequired;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

class Translatable extends Section
{
    protected bool | Closure $isRequired = false;


    public function isRequired(): bool
    {
        return (bool) $this->evaluate($this->isRequired);
    }

    public function required(bool | Closure $condition = true): static
    {
        $this->isRequired = $condition;

        return $this;
    }
    public static function make(string | array | Htmlable | Closure | null $heading =  'الاسم'): static
    {
        $static = app(static::class, ['heading' => $heading]);
        $static->configure();
        if($heading == "الاسم") {
            $static->name();
        }
        return $static;
    }

    public function name(string | Htmlable | Closure | null $name = 'name', $customLiable=null): static
    {
        $this->statePath($name)
            ->schema([
                TextInput::make('ar')->label(__('string.arabic',['attribute'=>$this->getHeading()??$customLiable]))->required(fn()=>$this->isRequired())->maxLength(255),
                TextInput::make('en')->label(__('string.english',['attribute'=>$this->getHeading()??$customLiable]))->required(fn()=>$this->isRequired())->maxLength(255),
            ])->compact();

        return $this;
    }

    public function richName(string | Htmlable | Closure | null $name = 'name'): static
    {
        $this->statePath($name)
            ->schema([
                RichEditor::make('ar')->label(__('string.arabic',['attribute'=>$this->getHeading()]))->required(fn()=>$this->isRequired()),
                RichEditor::make('en')->label(__('string.english',['attribute'=>$this->getHeading()]))->required(fn()=>$this->isRequired()),
            ])->compact();

        return $this;
    }

    public function areaName(string | Htmlable | Closure | null $name = 'name', $customLiable=null): static
    {

        $this->statePath($name)
            ->schema([
                Textarea::make('ar')->label(__('string.arabic',['attribute'=>$this->getHeading()??$customLiable]))->required(fn()=>$this->isRequired()),
                Textarea::make('en')->label(__('string.english',['attribute'=>$this->getHeading()??$customLiable]))->required(fn()=>$this->isRequired()),

            ])->compact();

        return $this;
    }
}
