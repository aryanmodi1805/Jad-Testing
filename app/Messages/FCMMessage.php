<?php

namespace App\Messages;

class FCMMessage
{
    public ?string $link = null;

    public string $locale = 'ar';

    public ?string $title = null;

    public ?string $text = null;

    public ?string $screen = null;
    public ?array $args = [];

    public function text(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function locale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function link(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function screen(string $screen): self
    {
        $this->screen = $screen;

        return $this;
    }

    public function args(array $args): self
    {
        $this->args = $args;

        return $this;
    }


}
