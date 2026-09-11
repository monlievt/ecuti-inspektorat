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
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);
        }

        // 7th attempt should receive 429 Too Many Requests
        $response = $this->actingAs($this->user)->post('/profil/ubah-password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(429);
    }
}
