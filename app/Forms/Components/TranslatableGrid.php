<?php

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;

class TranslatableGrid extends Grid
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

    public function textInput(string | Htmlable | Closure | null $name = 'name', $customLabel=null): static
    {
        $this->statePath($name)
            ->schema([
                TextInput::make('ar')->label(fn() => __('string.arabic',['attribute'=>$this->getLabel()??$customLabel ?? $name]))
                    ->required(fn()=>$this->isRequired())->maxLength(255)->rtlDirection(),
                TextInput::make('en')->label(fn() => __('string.english',['attribute'=>$this->getLabel()??$customLabel ?? $name]))
                    ->required(fn()=>$this->isRequired())->maxLength(255)->ltrDirection(),
            ]);

        return $this;
    }

    public function nameTextInput(string | Htmlable | Closure | null $name = 'name', $customLabel=null): static
    {
        $this->statePath($name)
            ->schema([
                TextInput::make('ar')->label(fn() => __('string.arabic',['attribute'=>$this->getLabel()??$customLabel ?? $name]))
                    ->required(fn()=>$this->isRequired())->maxLength(40)->rtlDirection(),
                TextInput::make('en')->label(fn() => __('string.english',['attribute'=>$this->getLabel()??$customLabel ?? $name]))
                    ->required(fn()=>$this->isRequired())->maxLength(40)->ltrDirection(),
            ]);

        return $this;
    }

    public function textArea(string | Htmlable | Closure | null $name = 'name', $customLabel=null): static
    {
        $this->statePath($name)
            ->schema([
                Textarea::make('ar')->label(fn() => __('string.arabic',['attribute'=>$this->getLabel()??$customLabel ?? $name]))
                    ->required(fn()=>$this->isRequired())->maxLength(255)->rtlDirection(),
                Textarea::make('en')->label(fn() => __('string.english',['attribute'=>$this->getLabel()??$customLabel ?? $name]))
                    ->required(fn()=>$this->isRequired())->maxLength(255)->ltrDirection(),
            ]);

        return $this;
    }

}
