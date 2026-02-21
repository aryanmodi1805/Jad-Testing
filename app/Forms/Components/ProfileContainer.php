<?php

namespace App\Forms\Components;

use App\Models\Seller;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;


class ProfileContainer extends Field
{
    protected string $view = 'forms.components.profile-container';

    public ?Seller $seller = null;
    public $gallery = [];

    public function seller(Seller $seller): static
    {
        $this->seller = $seller;

        return $this;
    }

    public function getSeller(): Seller|\Illuminate\Contracts\Auth\Authenticatable|null
    {
        return $this->seller ?? (Filament::auth()->user() instanceof Seller ? Filament::auth()->user() : null);
    }

    public function getGallery()
    {
       return  $this->gallery = $this->seller->getMedia('images')->map(function($media) {
            $media->checkWidthHeight();

            return [
                'src' => $media->getUrl(),
                'thumb' => $media->getUrl('preview'),
                'width' => $media->width, // Replace with actual width if available
                'height' => $media->height, // Replace with actual height if available
                'alt' => $media->name,
            ];
        });
    }


    public function getProjectGallery($project)
    {
        return $project->getMedia('projects.more')->map(function(\App\Models\Media $media) {
            $media->checkWidthHeight();
            return [
                'src' => $media->getUrl(),
                'thumb' => $media->getUrl('preview'),
                'width' => $media->width, // Replace with actual width if available
                'height' => $media->height, // Replace with actual height if available// Replace with actual height if available
                'alt' => $media->name,
            ];
        });
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->hiddenLabel();

    }

}
