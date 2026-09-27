<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ProfilePictureController extends Controller
{
    public function show(string $filename)
    {
        abort_unless(
            preg_match('/\A[a-zA-Z0-9][a-zA-Z0-9_-]*\.(?:jpe?g|png|gif)\z/i', $filename),
            404
        );

        $path = 'profile_pics/' . $filename;
        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        return $disk->response($path, $filename, [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
