<?php

namespace App\Infolists\Components;

use Filament\Infolists\Components\Entry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttachmentViewer extends Entry
{
    protected string $view = 'infolists.components.attachment-viewer';

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function getAttachmentDetails(): array
    {
        $attachments = $this->getState();
        if (!is_array($attachments)) {
            Log::error('Attachments are not an array', ['attachments' => $attachments]);
            return [];
        }

        return array_map(function ($path) {
            // Ensure $path is the relative path within the storage disk
            if (filter_var($path, FILTER_VALIDATE_URL)) {
                $url = $path;
                $path = str_replace(Storage::disk('public')->url(''), '', $path);
            } else {
                $url = Storage::disk('public')->url($path);
            }

            $mime = Storage::disk('public')->mimeType($path);
            try {
                $size = Storage::disk('public')->size($path);
            } catch (\Exception $e) {
                Log::error('Error retrieving file size', ['path' => $path, 'exception' => $e]);
                $size = null;
            }

            if ($mime === false) {
                Log::error('Error retrieving MIME type', ['path' => $path]);
                $mime = 'Unknown';
            }

            return [
                'url' => $url,
                'name' => basename($path),
                'mime' => $mime,
                'size' => $size,
            ];
        }, $attachments);
    }
}
