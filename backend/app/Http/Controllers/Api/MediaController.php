<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MediaFile;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function audio(MediaFile $media)
    {
        if ($media->type !== 'audio') {
            abort(404);
        }

        if (! Storage::disk($media->disk)->exists($media->path)) {
            abort(404);
        }

        return Storage::disk($media->disk)->response(
            $media->path,
            $media->original_name,
            [
                'Content-Type' => $media->mime_type ?? 'audio/mpeg',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }
}
