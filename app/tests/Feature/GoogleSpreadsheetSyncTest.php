<?php

namespace Tests\Feature;

use App\Events\StatusCutiBerubah;
use App\Models\CutiJenis;
use App\Models\CutiPengajuan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\User;
use App\Services\GoogleSpreadsheetSyncService;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleSpreadsheetSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Pegawai $pegawai;
    protected CutiPengajuan $pengajuan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin Kepegawaian',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin_cuti',
        ]);

        $unit = UnitKerja::create(['nama' => 'Inspektorat Pembantu I', 'kode' => 'IRBAN1']);
        $this->pegawai = Pegawai::create([
            'user_id' => $this->adminUser->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => Carbon::parse('2010-01-01'),
            'pangkat_golongan' => 'III/c',
            'jabatan' => 'Auditor Muda',
            'unit_kerja_id' => $unit->id,
            'jenis_pegawai' => 'PNS',
            'aktif' => true,
        ]);

        $jenisCuti = CutiJenis::create([
            'nama' => 'Cuti Tahunan',
            'kode' => 'tahunan',
            'maksimal_hari' => 12,
            'satuan' => 'hari_kerja',
            'butuh_lampiran' => false,
        ]);

        $this->pengajuan = CutiPengajuan::create([
            'nomor_pengajuan' => 'CUTI/2026/09/0001',
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $jenisCuti->id,
            'alasan' => 'Keperluan keluarga penting',
            'tanggal_mulai' => '2026-10-05',
            'tanggal_selesai' => '2026-10-07',
            'jumlah_hari_kerja' => 3,
            'status' => CutiPengajuan::STATUS_MENUNGGU_ATASAN,
        ]);
    }

    public function test_service_is_configured_logic(): void
    {
        $service = app(GoogleSpreadsheetSyncService::class);

        SettingService::set('spreadsheet_sync_enabled', '0', 'spreadsheet');
        SettingService::set('spreadsheet_webhook_url', '', 'spreadsheet');
        $this->assertFalse($service->isConfigured());

        SettingService::set('spreadsheet_sync_enabled', '1', 'spreadsheet');
        SettingService::set('spreadsheet_webhook_url', 'https://script.google.com/macros/s/TEST/exec', 'spreadsheet');
        $this->assertTrue($service->isConfigured());
    }

    public function test_webhook_test_connection_successful(): void
    {
        Http::fake([
            'https://script.google.com/macros/s/TEST/exec' => Http::response([
                'status' => 'success',
                'message' => 'Koneksi Webhook Google Spreadsheet Berhasil Terhubung!',
            ], 200),
        ]);

        $service = app(GoogleSpreadsheetSyncService::class);
        $res = $service->testConnection('https://script.google.com/macros/s/TEST/exec');

        $this->assertTrue($res['success']);
        $this->assertStringContainsString('Berhasil', $res['message']);
    }

    public function test_sync_pengajuan_sends_correct_payload_and_upsert(): void
    {
        Http::fake([
            'https://script.google.com/macros/s/TEST/exec' => Http::response([
                'status' => 'success',
                'message' => 'Data pengajuan berhasil dicatat ke Spreadsheet',
            ], 200),
        ]);

        SettingService::set('spreadsheet_sync_enabled', '1', 'spreadsheet');
        SettingService::set('spreadsheet_webhook_url', 'https://script.google.com/macros/s/TEST/exec', 'spreadsheet');

        $service = app(GoogleSpreadsheetSyncService::class);
        $res = $service->syncPengajuan($this->pengajuan);

        $this->assertTrue($res['success']);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $request->url() === 'https://script.google.com/macros/s/TEST/exec' &&
                   $data['action'] === 'upsert' &&
                   $data['nomor_pengajuan'] === 'CUTI/2026/09/0001' &&
                   $data['nama_pegawai'] === 'Budi Santoso' &&
                   $data['status'] === 'Menunggu Persetujuan Atasan';
        });
    }

    public function test_event_status_cuti_berubah_triggers_spreadsheet_sync(): void
    {
        Http::fake([
            'https://script.google.com/macros/s/TEST/exec' => Http::response(['status' => 'success'], 200),
        ]);

        SettingService::set('spreadsheet_sync_enabled', '1', 'spreadsheet');
        SettingService::set('spreadsheet_webhook_url', 'https://script.google.com/macros/s/TEST/exec', 'spreadsheet');

        event(new StatusCutiBerubah($this->pengajuan));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://script.google.com/macros/s/TEST/exec' &&
                   $request['nomor_pengajuan'] === 'CUTI/2026/09/0001';
        });
    }

    public function test_admin_can_save_settings_and_trigger_test_endpoint(): void
    {
        Http::fake([
            'https://script.google.com/macros/s/TEST/exec' => Http::response(['status' => 'success', 'message' => 'Connected'], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.setting.update'), [
            'spreadsheet_sync_enabled' => '1',
            'spreadsheet_webhook_url' => 'https://script.google.com/macros/s/TEST/exec',
        ]);

        $response->assertRedirect(route('admin.setting.index'));
        $this->assertEquals('1', SettingService::get('spreadsheet_sync_enabled'));
        $this->assertEquals('https://script.google.com/macros/s/TEST/exec', SettingService::get('spreadsheet_webhook_url'));

        // Test endpoint
        $testRes = $this->actingAs($this->adminUser)->post(route('admin.setting.test-spreadsheet'), [
            'spreadsheet_webhook_url' => 'https://script.google.com/macros/s/TEST/exec',
        ]);
        $testRes->assertRedirect(route('admin.setting.index'));
        $testRes->assertSessionHas('success');
    }

    public function test_sync_master_pegawai_sends_all_employees_with_balances(): void
    {
        Http::fake([
            'https://script.google.com/macros/s/TEST/exec' => Http::response(['status' => 'success'], 200),
        ]);

        SettingService::set('spreadsheet_sync_enabled', '1', 'spreadsheet');
        SettingService::set('spreadsheet_webhook_url', 'https://script.google.com/macros/s/TEST/exec', 'spreadsheet');

        $service = app(GoogleSpreadsheetSyncService::class);
        $res = $service->syncMasterPegawai();

        $this->assertTrue($res['success']);
        $this->assertEquals(1, $res['total']);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $request->url() === 'https://script.google.com/macros/s/TEST/exec' &&
                   $data['action'] === 'sync_master_pegawai' &&
                   count($data['data_pegawai']) === 1 &&
                   $data['data_pegawai'][0]['nip'] === '198501012010011001' &&
                   $data['data_pegawai'][0]['nama_lengkap'] === 'Budi Santoso' &&
                   $data['data_pegawai'][0]['unit_kerja'] === 'Inspektorat Pembantu I';
        });

        // Test endpoint sync master
        $endpointRes = $this->actingAs($this->adminUser)->post(route('admin.setting.sync-master-spreadsheet'));
        $endpointRes->assertRedirect(route('admin.setting.index'));
        $endpointRes->assertSessionHas('success');
    }
}
