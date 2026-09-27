<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePictureTest extends TestCase
{
    public function test_authenticated_user_can_load_a_profile_picture_from_public_storage(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('profile_pics/test-picture.jpg', 'test-image-content');

        $user = new User();
        $user->id = 1;

        $response = $this->actingAs($user)->get(route('profile-pictures.show', 'test-picture.jpg'));

        $response->assertOk();
        $response->assertHeader('content-type', 'image/jpeg');
        $this->assertSame('test-image-content', $response->streamedContent());
    }
}
