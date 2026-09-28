<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentQrTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('landing_contents', function (Blueprint $table) {
            $table->id();
            $table->string('content_key')->unique();
            $table->text('content_value');
            $table->timestamps();
        });
    }

    public function test_payment_qr_route_serves_the_configured_public_storage_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('payment_qrs/gcash-test.png', 'test-qr-image');
        DB::table('landing_contents')->insert([
            'content_key' => 'gcash_qr',
            'content_value' => 'payment_qrs/gcash-test.png',
        ]);

        $response = $this->get(route('payment.qr', 'gcash'));

        $response->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame('test-qr-image', $response->streamedContent());
    }

    public function test_payment_qr_route_rejects_unknown_providers(): void
    {
        $this->get('/payment-qr/unknown')->assertNotFound();
    }

    public function test_admin_can_update_wallet_numbers_without_replacing_existing_qrs(): void
    {
        $admin = new User();
        $admin->forceFill([
            'id' => 1,
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.modified.content.update'), [
                'about_title' => 'About',
                'about_content' => 'Main content',
                'about_secondary' => 'Secondary content',
                'gcash_number' => '09123456789',
                'paymaya_number' => '09987654321',
                'contact_description' => 'Contact us',
                'contact_address' => 'Carmona',
                'contact_email' => 'support@example.com',
                'contact_phone' => '09123456789',
                'contact_hours' => 'Daily',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('landing_contents', [
            'content_key' => 'gcash_number',
            'content_value' => '09123456789',
        ]);
        $this->assertDatabaseHas('landing_contents', [
            'content_key' => 'paymaya_number',
            'content_value' => '09987654321',
        ]);
        $this->assertDatabaseHas('landing_contents', [
            'content_key' => 'gcash_qr',
            'content_value' => 'Gcash/gcash_qr.jpg',
        ]);
        $this->assertDatabaseHas('landing_contents', [
            'content_key' => 'paymaya_qr',
            'content_value' => 'Gcash/maya_qr.jpg',
        ]);
    }
}
