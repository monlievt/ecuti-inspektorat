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

    public function test_settings_persist_after_seed_defaults_and_index_page_visit(): void
    {
        // 1. Simpan token & chat ID
        SettingService::set('telegram_bot_token', '123456:BOT-TOKEN-TEST');
        SettingService::set('telegram_chat_id', '-100987654321');

        // 2. Jalankan seedDefaults() berulang kali
        SettingService::seedDefaults();

        // 3. Kunjungi halaman index admin setting
        $response = $this->actingAs($this->adminUser)->get(route('admin.setting.index'));
        $response->assertStatus(200);

        // 4. Pastikan nilai TIDAK HILANG / TIDAK TERTIMPA
        $this->assertEquals('123456:BOT-TOKEN-TEST', SettingService::get('telegram_bot_token'));
        $this->assertEquals('-100987654321', SettingService::get('telegram_chat_id'));
        $response->assertSee('123456:BOT-TOKEN-TEST');
        $response->assertSee('-100987654321');
    }

    public function test_admin_can_detect_telegram_chat_id(): void
    {
        Http::fake([
            'https://api.telegram.org/*/getUpdates' => Http::response([
                'ok' => true,
                'result' => [
                    [
                        'update_id' => 1234567,
                        'message' => [
                            'message_id' => 10,
                            'chat' => [
                                'id' => -1001987654321,
                                'title' => 'Grup Backup Inspektorat',
                                'type' => 'supergroup'
                            ],
                            'text' => 'halo bot'
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.setting.detect-telegram-chat-id'), [
            'telegram_bot_token' => 'mock_token',
        ]);

        $response->assertRedirect(route('admin.setting.index'));
        $response->assertSessionHas('success');
        $this->assertEquals('-1001987654321', SettingService::get('telegram_chat_id'));
    }

    public function test_login_page_renders_recaptcha_when_enabled(): void
    {
        SettingService::set('recaptcha_enabled', '1', 'keamanan', 'Aktifkan Google reCAPTCHA', 'boolean');
        SettingService::set('recaptcha_site_key', '6LeIx0cD_TEST_SITE_KEY_123', 'keamanan', 'Google reCAPTCHA Site Key', 'string');

        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('https://www.google.com/recaptcha/api.js', false);
        $response->assertSee('class="g-recaptcha"', false);
        $response->assertSee('data-sitekey="6LeIx0cD_TEST_SITE_KEY_123"', false);
        $response->assertDontSee('Tulis hasil angka saja');
    }

    public function test_login_page_renders_math_captcha_when_recaptcha_disabled(): void
    {
        SettingService::set('recaptcha_enabled', '0', 'keamanan', 'Aktifkan Google reCAPTCHA', 'boolean');
        SettingService::set('recaptcha_site_key', '', 'keamanan', 'Google reCAPTCHA Site Key', 'string');

        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertDontSee('https://www.google.com/recaptcha/api.js', false);
        $response->assertDontSee('class="g-recaptcha"', false);
        $response->assertSee('Verifikasi Keamanan:');
        $response->assertSee('Tulis hasil angka saja');
    }

    public function test_setting_page_renders_cleanly_and_without_error(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.setting.index'));
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Sistem');
        $response->assertSee('Telegram (Backup)');
        $response->assertSee('WhatsApp Gateway (WAHA)');
        $response->assertSee('reCAPTCHA &amp; Keamanan', false);
    }

    public function test_admin_can_save_recaptcha_settings(): void
    {
        $response = $this->actingAs($this->adminUser)->put(route('admin.setting.update'), [
            'recaptcha_enabled' => '1',
            'recaptcha_site_key' => '6LeIx0cD_LIVE_KEY_TEST',
            'recaptcha_secret_key' => '6LeIx0cD_SECRET_TEST',
        ]);

        $response->assertRedirect(route('admin.setting.index'));
        $response->assertSessionHas('success');

        $this->assertTrue(SettingService::get('recaptcha_enabled'));
        $this->assertEquals('6LeIx0cD_LIVE_KEY_TEST', SettingService::get('recaptcha_site_key'));
        $this->assertEquals('6LeIx0cD_SECRET_TEST', SettingService::get('recaptcha_secret_key'));

        // Logout user first so /login doesn't redirect to /dashboard
        auth()->logout();

        // Check login page immediately shows recaptcha
        $loginRes = $this->get(route('login'));
        $loginRes->assertStatus(200);
        $loginRes->assertSee('class="g-recaptcha"', false);
        $loginRes->assertSee('data-sitekey="6LeIx0cD_LIVE_KEY_TEST"', false);
    }

    public function test_admin_can_view_and_update_pejabat_bkpsdm_in_master_data(): void
    {
        // 1. Akses halaman master BKPSDM
        $response = $this->actingAs($this->adminUser)->get(route('admin.master.bkpsdm'));
        $response->assertStatus(200);
        $response->assertSee('Data Master Pejabat Kepala BKPSDM');
        $response->assertSee('HERI YULIANTO, S.Sos., M.Si.');

        // 2. Non admin ditolak
        $nonAdmin = $this->actingAs($this->regularUser)->get(route('admin.master.bkpsdm'));
        $nonAdmin->assertStatus(403);

        // 3. Update data pejabat BKPSDM
        $updateRes = $this->actingAs($this->adminUser)->post(route('admin.master.bkpsdm.update'), [
            'kepala_bkpsdm_nama' => 'DRS. BUDI PRASETYO, M.M.',
            'kepala_bkpsdm_nip' => '197505052000031001',
            'kepala_bkpsdm_pangkat_golongan' => 'Pembina Utama Madya (IV/d)',
            'kepala_bkpsdm_jabatan' => 'Plt. Kepala BKPSDM Kabupaten Trenggalek',
        ]);

        $updateRes->assertRedirect(route('admin.master.bkpsdm'));
        $updateRes->assertSessionHas('success');

        $this->assertEquals('DRS. BUDI PRASETYO, M.M.', SettingService::get('kepala_bkpsdm_nama'));
        $this->assertEquals('197505052000031001', SettingService::get('kepala_bkpsdm_nip'));
    }

    public function test_admin_can_crud_hari_libur_dan_cuti_bersama(): void
    {
        // 1. Tambah Hari Libur
        $response = $this->actingAs($this->adminUser)->post(route('admin.master.libur'), [
            'tanggal' => '2026-05-01',
            'keterangan' => 'Hari Buruh Internasional',
        ]);
        $response->assertRedirect(route('admin.master.libur'));
        $response->assertSessionHas('success');

        $libur = \App\Models\CutiHariLibur::where('keterangan', 'Hari Buruh Internasional')->first();
        $this->assertNotNull($libur);

        // 2. Edit / Update Hari Libur
        $updateRes = $this->actingAs($this->adminUser)->put(route('admin.master.libur.update', $libur), [
            'tanggal' => '2026-05-01',
            'keterangan' => 'Hari Buruh Internasional (May Day)',
        ]);
        $updateRes->assertRedirect(route('admin.master.libur'));
        $this->assertEquals('Hari Buruh Internasional (May Day)', $libur->fresh()->keterangan);

        // 3. Hapus Hari Libur
        $deleteRes = $this->actingAs($this->adminUser)->delete(route('admin.master.libur.destroy', $libur));
        $deleteRes->assertRedirect(route('admin.master.libur'));
        $this->assertNull(\App\Models\CutiHariLibur::find($libur->id));

        // 4. Tambah Cuti Bersama
        $cbRes = $this->actingAs($this->adminUser)->post(route('admin.master.cuti-bersama'), [
            'tanggal' => '2026-05-02',
            'keterangan' => 'Cuti Bersama Hari Buruh',
            'nomor_keppres' => 'Keppres No. 1 Tahun 2026',
        ]);
        $cbRes->assertRedirect(route('admin.master.cuti-bersama'));

        $cb = \App\Models\CutiBersama::where('keterangan', 'Cuti Bersama Hari Buruh')->first();
        $this->assertNotNull($cb);

        // 5. Update Cuti Bersama
        $cbUpdate = $this->actingAs($this->adminUser)->put(route('admin.master.cuti-bersama.update', $cb), [
            'tanggal' => '2026-05-02',
            'keterangan' => 'Cuti Bersama Nasional',
            'nomor_keppres' => 'Keppres No. 2 Tahun 2026',
        ]);
        $cbUpdate->assertRedirect(route('admin.master.cuti-bersama'));
        $this->assertEquals('Cuti Bersama Nasional', $cb->fresh()->keterangan);

        // 6. Hapus Cuti Bersama
        $cbDelete = $this->actingAs($this->adminUser)->delete(route('admin.master.cuti-bersama.destroy', $cb));
        $cbDelete->assertRedirect(route('admin.master.cuti-bersama'));
        $this->assertNull(\App\Models\CutiBersama::find($cb->id));
    }

    public function test_authenticated_user_can_access_panduan_page(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('panduan'));
        $response->assertStatus(200);
        $response->assertSee('Panduan Pengguna Aplikasi e-Cuti');
        $response->assertSee('Inspektorat Kabupaten Trenggalek');
    }
}
