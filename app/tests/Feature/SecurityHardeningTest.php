<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $unitKerja = UnitKerja::create([
            'nama' => 'Sekretariat',
            'kode' => 'SEKR',
            'aktif' => true,
        ]);

        $this->user = User::create([
            'name' => 'Security Tester',
            'email' => 'security@test.com',
            'password' => bcrypt('password123'),
            'role' => 'pegawai',
        ]);

        $pegawai = Pegawai::create([
            'user_id' => $this->user->id,
            'nama_lengkap' => 'Security Test Pegawai',
            'nip' => '199001012020011001',
            'unit_kerja_id' => $unitKerja->id,
            'jabatan' => 'Auditor Pertama',
            'pangkat_golongan' => 'Penata Muda (III/a)',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => '2020-01-01',
            'jenis_pegawai' => 'PNS',
            'nomor_hp' => '081234567890',
            'aktif' => true,
        ]);

        $this->user->update(['pegawai_id' => $pegawai->id]);
    }

    public function test_login_page_has_strict_security_and_anti_cache_headers(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Verify Anti-Cache
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
    }

    public function test_authenticated_dashboard_has_strict_anti_cache_headers(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        $response->assertHeader('Pragma', 'no-cache');
    }

    public function test_session_cookie_name_is_isolated(): void
    {
        $cookieName = config('session.cookie');
        $this->assertEquals('ecuti_insp_session', $cookieName);
    }

    public function test_password_change_route_is_rate_limited(): void
    {
        // 6 attempts allowed per minute
        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($this->user)->post('/profil/ubah-password', [
                'password_lama' => 'wrongpassword',
                'password_baru' => 'NewP@ssw0rd123!',
                'password_baru_confirmation' => 'NewP@ssw0rd123!',
            ]);
        }

        // 7th attempt should receive 429 Too Many Requests
        $response = $this->actingAs($this->user)->post('/profil/ubah-password', [
            'password_lama' => 'wrongpassword',
            'password_baru' => 'NewP@ssw0rd123!',
            'password_baru_confirmation' => 'NewP@ssw0rd123!',
        ]);

        $response->assertStatus(429);
    }

    public function test_password_change_rejects_weak_passwords(): void
    {
        // 1. All numbers (like 1234567812)
        $response = $this->actingAs($this->user)->post('/profil/ubah-password', [
            'password_lama' => 'password123',
            'password_baru' => '1234567812',
            'password_baru_confirmation' => '1234567812',
        ]);
        $response->assertSessionHasErrors('password_baru');

        // 2. All letters without numbers or symbols
        $response2 = $this->actingAs($this->user)->post('/profil/ubah-password', [
            'password_lama' => 'password123',
            'password_baru' => 'SecretPassword',
            'password_baru_confirmation' => 'SecretPassword',
        ]);
        $response2->assertSessionHasErrors('password_baru');
    }

    public function test_password_change_rejects_same_password(): void
    {
        $response = $this->actingAs($this->user)->post('/profil/ubah-password', [
            'password_lama' => 'password123',
            'password_baru' => 'password123',
            'password_baru_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors('password_baru');
    }

    public function test_password_change_succeeds_with_strong_password_and_invalidates_sessions(): void
    {
        // Insert dummy session for this user in sessions table
        if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
            \Illuminate\Support\Facades\DB::table('sessions')->insert([
                'id' => 'dummy_session_id_123',
                'user_id' => $this->user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0',
                'payload' => 'dummy_payload',
                'last_activity' => time(),
            ]);
        }

        $oldHash = $this->user->password;

        $response = $this->actingAs($this->user)->post('/profil/ubah-password', [
            'password_lama' => 'password123',
            'password_baru' => 'NewSecur3P@ssword!',
            'password_baru_confirmation' => 'NewSecur3P@ssword!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $this->user->refresh();

        // Password must be changed and match new password
        $this->assertNotEquals($oldHash, $this->user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewSecur3P@ssword!', $this->user->password));

        // User must be logged out
        $this->assertGuest();

        // Database sessions for this user must be destroyed
        if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
            $this->assertDatabaseMissing('sessions', ['id' => 'dummy_session_id_123']);
        }
    }

    public function test_admin_reset_password_enforces_strong_password_and_invalidates_sessions(): void
    {
        $admin = User::create([
            'name' => 'Admin Kepegawaian',
            'email' => 'admin_pegawai@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin_cuti',
        ]);

        $pegawai = Pegawai::where('user_id', $this->user->id)->first();

        // 1. Admin attempts weak password (e.g. 123456)
        $responseWeak = $this->actingAs($admin)->post(route('admin.pegawai.update', $pegawai->id), [
            'nip' => $pegawai->nip,
            'nama_lengkap' => $pegawai->nama_lengkap,
            'email' => $this->user->email,
            'jenis_kelamin' => 'L',
            'tmt_cpns' => '2020-01-01',
            'pangkat_golongan' => 'Penata Muda (III/a)',
            'jabatan' => 'Auditor',
            'unit_kerja_id' => $pegawai->unit_kerja_id,
            'jenis_pegawai' => 'PNS',
            'role' => 'pegawai',
            'bisa_beri_izin_sementara' => 0,
            'aktif' => 1,
            'jatah_tahun_berjalan' => 12,
            'carry_over_n1' => 0,
            'carry_over_n2' => 0,
            'tambahan_cuti_bersama' => 0,
            'terpakai' => 0,
            'password' => '12345678', // Weak: only numbers
        ]);

        $responseWeak->assertSessionHasErrors('password');

        // 2. Insert dummy session for target user
        if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
            \Illuminate\Support\Facades\DB::table('sessions')->insert([
                'id' => 'target_pegawai_session_abc',
                'user_id' => $this->user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0',
                'payload' => 'payload',
                'last_activity' => time(),
            ]);
        }

        // 3. Admin provides strong password
        $responseStrong = $this->actingAs($admin)->post(route('admin.pegawai.update', $pegawai->id), [
            'nip' => $pegawai->nip,
            'nama_lengkap' => $pegawai->nama_lengkap,
            'email' => $this->user->email,
            'jenis_kelamin' => 'L',
            'tmt_cpns' => '2020-01-01',
            'pangkat_golongan' => 'Penata Muda (III/a)',
            'jabatan' => 'Auditor',
            'unit_kerja_id' => $pegawai->unit_kerja_id,
            'jenis_pegawai' => 'PNS',
            'role' => 'pegawai',
            'bisa_beri_izin_sementara' => 0,
            'aktif' => 1,
            'jatah_tahun_berjalan' => 12,
            'carry_over_n1' => 0,
            'carry_over_n2' => 0,
            'tambahan_cuti_bersama' => 0,
            'terpakai' => 0,
            'password' => 'Str0ngResetP@ssword!',
        ]);

        $responseStrong->assertRedirect(route('admin.pegawai.index'));
        $responseStrong->assertSessionHas('success');

        $this->user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('Str0ngResetP@ssword!', $this->user->password));

        // Target user's session must be destroyed
        if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
            $this->assertDatabaseMissing('sessions', ['id' => 'target_pegawai_session_abc']);
        }
    }
}
