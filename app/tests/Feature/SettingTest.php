<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin Kepegawaian',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin_cuti'
        ]);

        $this->regularUser = User::create([
            'name' => 'Pegawai Biasa',
            'email' => 'pegawai@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai'
        ]);
    }

    public function test_non_admin_cannot_access_settings(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.setting.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_settings_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.setting.index'));
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Sistem & Integrasi');
        $response->assertSee('Telegram (Backup)');
        $response->assertSee('WhatsApp Gateway (WAHA)');
        $response->assertSee('reCAPTCHA & Keamanan');
    }

    public function test_admin_can_update_settings(): void
    {
        $response = $this->actingAs($this->adminUser)->put(route('admin.setting.update'), [
            'telegram_bot_token' => '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11',
            'telegram_chat_id' => '-1001234567899',
            'recaptcha_enabled' => '1',
            'recaptcha_site_key' => 'mock-site-key-123',
            'recaptcha_secret_key' => 'mock-secret-key-456',
            'waha_base_url' => 'http://localhost:3000',
            'waha_session' => 'default',
        ]);

        $response->assertRedirect(route('admin.setting.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11', SettingService::get('telegram_bot_token'));
        $this->assertEquals('-1001234567899', SettingService::get('telegram_chat_id'));
        $this->assertTrue(SettingService::get('recaptcha_enabled'));
        $this->assertEquals('mock-site-key-123', SettingService::get('recaptcha_site_key'));
    }

    public function test_admin_can_test_telegram_connection(): void
    {
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 101]], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.setting.test-telegram'), [
            'telegram_bot_token' => 'mock_token',
            'telegram_chat_id' => '-100999888',
        ]);

        $response->assertRedirect(route('admin.setting.index'));
        $response->assertSessionHas('success');
    }

    public function test_admin_can_test_telegram_via_put_and_get(): void
    {
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 101]], 200),
        ]);

        // Test PUT (when button inside form with @method('PUT') is clicked)
        $responsePut = $this->actingAs($this->adminUser)->put(route('admin.setting.test-telegram'), [
            'telegram_bot_token' => 'mock_token',
            'telegram_chat_id' => '-100999888',
        ]);
        $responsePut->assertStatus(302);
        $responsePut->assertSessionHas('success');

        // Test GET (when accessed directly via browser URL)
        SettingService::set('telegram_bot_token', 'mock_token');
        SettingService::set('telegram_chat_id', '-100999888');

        $responseGet = $this->actingAs($this->adminUser)->get(route('admin.setting.test-telegram'));
        $responseGet->assertStatus(302);
        $responseGet->assertSessionHas('success');
    }

    public function test_admin_can_test_whatsapp_connection(): void
    {
        Http::fake([
            'http://localhost:3000/api/sendText' => Http::response(['status' => 'success'], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.setting.test-whatsapp'), [
            'test_nomor_wa' => '081234567890',
        ]);

        $response->assertRedirect(route('admin.setting.index'));
        $response->assertSessionHas('success');
    }

    public function test_admin_can_test_whatsapp_via_put(): void
    {
        Http::fake([
            'http://localhost:3000/api/sendText' => Http::response(['status' => 'success'], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.setting.test-whatsapp'), [
            'test_nomor_wa' => '081234567890',
        ]);

        $response->assertRedirect(route('admin.setting.index'));
        $response->assertSessionHas('success');
    }
}
