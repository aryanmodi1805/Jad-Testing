<?php

namespace  App\Concerns;

trait CanGetOriginalFileName
{
    public function getOriginalFileName(string $path, ?array $names): string
    {
        return $names[$path] ?? 'File name not found';
    }
}
