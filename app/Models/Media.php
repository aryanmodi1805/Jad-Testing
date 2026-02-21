<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Spatie\MediaLibrary\Support\ImageFactory;

class Media extends BaseMedia
{

    protected $appends = ['width', 'height'];

    public function getWidthAttribute()
    {
        return $this->custom_properties['width'] ?? null;
    }

    public function getHeightAttribute()
    {
        return $this->custom_properties['height'] ?? null;
    }

    public function checkWidthHeight(): void
    {
        if (!isset($this->custom_properties['width'], $this->custom_properties['height'])) {
            $factory = ImageFactory::load($this->getPath());
            $this->custom_properties = [
                'width' => $factory->getWidth(),
                'height' => $factory->getHeight()
            ];

            $this->save();
        }
    }


}
