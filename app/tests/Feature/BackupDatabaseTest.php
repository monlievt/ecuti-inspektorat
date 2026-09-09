<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\TelegramBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class BackupDatabaseTest extends TestCase
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

    public function test_telegram_backup_service_creates_valid_gzip_dump(): void
    {
        $service = new TelegramBackupService();
        $dump = $service->createDatabaseDump();

        $this->assertFileExists($dump['path']);
        $this->assertStringEndsWith('.sql.gz', $dump['filename']);
        $this->assertGreaterThan(0, $dump['size_bytes']);

        // Test uncompressing gzip to verify content
        $compressed = File::get($dump['path']);
        $uncompressed = gzdecode($compressed);
        $this->assertNotFalse($uncompressed);
        $this->assertStringContainsString('e-Cuti Database Backup Dump', $uncompressed);
    }

    public function test_backup_command_sends_to_telegram_with_http_mock(): void
    {
        config([
            'services.telegram.bot_token' => 'mock-token-12345',
            'services.telegram.chat_id' => '-100987654321',
        ]);

        Http::fake([
            'https://api.telegram.org/botmock-token-12345/sendDocument' => Http::response([
                'ok' => true,
                'result' => [
                    'message_id' => 999,
                    'document' => ['file_name' => 'backup_test.sql.gz']
                ]
            ], 200),
        ]);

        $this->artisan('cuti:backup-db')
            ->expectsOutputToContain('Memulai proses pencadangan database e-Cuti')
            ->expectsOutputToContain('File dump berhasil dibuat')
            ->expectsOutputToContain('Berkas backup database berhasil dikirim ke Telegram')
            ->assertSuccessful();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.telegram.org') &&
                   str_contains($request->url(), 'sendDocument');
        });
    }

    public function test_admin_can_access_backup_page_and_trigger_backup(): void
    {
        config([
            'services.telegram.bot_token' => 'mock-token-12345',
            'services.telegram.chat_id' => '-100987654321',
        ]);

        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        // 1. Regular user is forbidden
        $responseRegular = $this->actingAs($this->regularUser)->get(route('admin.backup.index'));
        $responseRegular->assertStatus(403);

        // 2. Admin can view the page
        $responseAdmin = $this->actingAs($this->adminUser)->get(route('admin.backup.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Pencadangan Database (Backup)');

        // 3. Admin can trigger backup
        $responsePost = $this->actingAs($this->adminUser)->post(route('admin.backup.proses'));
        $responsePost->assertRedirect();
        $responsePost->assertSessionHas('success');
    }
}
