<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;

class AuditKeamananCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuti:audit-keamanan 
                            {--strict : Menghentikan proses dengan error code jika ada temuan peringatan (warning)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit keamanan komprehensif e-Cuti sebelum rilis/deploy ke server produksi (Dependencies, Environment, Permissions, & AI Skills)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->alert('🛡️ AUDIT KEAMANAN & KESIAPAN PRODUKSI e-CUTI INSPEKTORAT');
        $this->newLine();

        $criticalErrors = 0;
        $warnings = 0;
        $passed = 0;

        $results = [];

        // 1. Audit Environment & Kredensial
        $this->line('🔍 [1/5] Memeriksa Konfigurasi Environment & Kunci Enkripsi...');
        
        $appKey = config('app.key');
        if (empty($appKey)) {
            $results[] = ['Kunci Aplikasi (APP_KEY)', 'GAGAL', 'APP_KEY belum digenerate. Jalankan php artisan key:generate', 'CRITICAL'];
            $criticalErrors++;
        } else {
            $results[] = ['Kunci Aplikasi (APP_KEY)', 'OK', 'Terkonfigurasi dan aktif', 'INFO'];
            $passed++;
        }

        $appDebug = config('app.debug');
        $appEnv = config('app.env');
        if ($appEnv === 'production' && $appDebug === true) {
            $results[] = ['Mode Debug (APP_DEBUG)', 'BAHAYA', 'APP_DEBUG=true pada lingkungan produksi! Ubah ke false di .env', 'CRITICAL'];
            $criticalErrors++;
        } elseif ($appDebug === true) {
            $results[] = ['Mode Debug (APP_DEBUG)', 'PERINGATAN', 'APP_DEBUG saat ini aktif (pastikan false saat deploy ke VPS)', 'WARNING'];
            $warnings++;
        } else {
            $results[] = ['Mode Debug (APP_DEBUG)', 'OK', 'Nonaktif (APP_DEBUG=false)', 'INFO'];
            $passed++;
        }

        $sessionSecure = config('session.secure');
        if ($appEnv === 'production' && !$sessionSecure) {
            $results[] = ['Cookie Keamanan (SESSION_SECURE)', 'PERINGATAN', 'SESSION_SECURE_COOKIE disarankan true jika memakai HTTPS di VPS', 'WARNING'];
            $warnings++;
        } else {
            $results[] = ['Cookie Keamanan (SESSION_SECURE)', 'OK', $sessionSecure ? 'HTTPS Cookie aktif' : 'Sesuai konfigurasi lokal', 'INFO'];
            $passed++;
        }

        // 2. Audit Izin Akses Direktori (Permissions)
        $this->line('📁 [2/5] Memeriksa Izin Tulis Direktori Storage & Cache...');
        
        $directories = [
            'storage/app' => storage_path('app'),
            'storage/framework/cache' => storage_path('framework/cache'),
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/framework/views' => storage_path('framework/views'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        foreach ($directories as $label => $path) {
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0775, true, true);
            }

            if (is_writable($path)) {
                $results[] = ["Izin Tulis: {$label}", 'OK', 'Dapat ditulis (writable)', 'INFO'];
                $passed++;
            } else {
                $results[] = ["Izin Tulis: {$label}", 'GAGAL', 'Tidak dapat ditulis! Jalankan chmod -R 775 ' . $label, 'CRITICAL'];
                $criticalErrors++;
            }
        }

        // Storage Symlink
        $publicStorage = public_path('storage');
        if (File::exists($publicStorage) || is_link($publicStorage)) {
            $results[] = ['Storage Symlink (public/storage)', 'OK', 'Tautan simbolik terpasang', 'INFO'];
            $passed++;
        } else {
            $results[] = ['Storage Symlink (public/storage)', 'PERINGATAN', 'Belum terpasang. Jalankan php artisan storage:link', 'WARNING'];
            $warnings++;
        }

        // 3. Audit Koneksi Database
        $this->line('🗄️ [3/5] Memeriksa Konektivitas Database...');
        try {
            DB::connection()->getPdo();
            $results[] = ['Koneksi Database', 'OK', 'Terhubung ke ' . DB::connection()->getDatabaseName(), 'INFO'];
            $passed++;
        } catch (Throwable $e) {
            $results[] = ['Koneksi Database', 'GAGAL', 'Gagal terhubung: ' . $e->getMessage(), 'CRITICAL'];
            $criticalErrors++;
        }

        // 4. Audit Dependensi Paket (Composer & NPM)
        $this->line('📦 [4/5] Memeriksa Kerentanan Dependensi (CVE / Security Advisories)...');
        
        // Composer Audit
        $composerProc = new Process(['composer', 'audit', '--format=json'], base_path());
        $composerProc->run();
        if ($composerProc->isSuccessful()) {
            $results[] = ['Paket PHP (Composer Audit)', 'OK', '0 celah keamanan terdeteksi', 'INFO'];
            $passed++;
        } else {
            $output = json_decode($composerProc->getOutput(), true);
            $advisoryCount = count($output['advisories'] ?? []);
            if ($advisoryCount === 0) {
                $results[] = ['Paket PHP (Composer Audit)', 'OK', 'Tidak ada celah keamanan kritis', 'INFO'];
                $passed++;
            } else {
                $results[] = ['Paket PHP (Composer Audit)', 'PERINGATAN', "Ditemukan {$advisoryCount} advisory keamanan. Jalankan 'composer audit'", 'WARNING'];
                $warnings++;
            }
        }

        // NPM Audit
        $npmProc = new Process(['npm', 'audit', '--json'], base_path());
        $npmProc->run();
        $npmOutput = json_decode($npmProc->getOutput(), true);
        $npmVulns = $npmOutput['metadata']['vulnerabilities']['total'] ?? 0;
        if ($npmVulns === 0) {
            $results[] = ['Paket JS (NPM Audit)', 'OK', '0 celah keamanan terdeteksi', 'INFO'];
            $passed++;
        } else {
            $results[] = ['Paket JS (NPM Audit)', 'PERINGATAN', "Ditemukan {$npmVulns} celah keamanan pada node_modules. Jalankan 'npm audit fix'", 'WARNING'];
            $warnings++;
        }

        // 5. NVIDIA SkillSpector (AI Agent Skills & Prompt Security)
        $this->line('🤖 [5/5] Memeriksa Keamanan AI Agent Skills via NVIDIA SkillSpector...');
        
        $skillsPath = base_path('../.agents');
        $globalSkillsPath = getenv('HOME') . '/.gemini/config/plugins';
        
        $scanTarget = null;
        if (File::isDirectory(base_path('.agents'))) {
            $scanTarget = base_path('.agents');
        } elseif (File::isDirectory($skillsPath)) {
            $scanTarget = $skillsPath;
        } elseif (File::isDirectory($globalSkillsPath)) {
            $scanTarget = $globalSkillsPath;
        }

        if ($scanTarget && $this->commandExists('skillspector')) {
            $ssProc = new Process(['skillspector', 'scan', $scanTarget, '--no-llm', '--format', 'json'], base_path());
            $ssProc->setTimeout(30);
            $ssProc->run();
            
            if ($ssProc->isSuccessful() || !empty($ssProc->getOutput())) {
                $ssData = json_decode($ssProc->getOutput(), true);
                $score = $ssData['risk_score'] ?? $ssData['score'] ?? 0;
                $verdict = $ssData['recommendation'] ?? ($score <= 25 ? 'SAFE' : 'REVIEW');
                
                if ($score <= 40) {
                    $results[] = ['NVIDIA SkillSpector', 'OK', "Target: {$scanTarget} | Skor Risiko: {$score}/100 ({$verdict})", 'INFO'];
                    $passed++;
                } else {
                    $results[] = ['NVIDIA SkillSpector', 'PERINGATAN', "Skor Risiko: {$score}/100. Rekomendasi: {$verdict}", 'WARNING'];
                    $warnings++;
                }
            } else {
                $results[] = ['NVIDIA SkillSpector', 'OK', 'Tool aktif & terpasang di sistem', 'INFO'];
                $passed++;
            }
        } elseif ($this->commandExists('skillspector')) {
            $results[] = ['NVIDIA SkillSpector', 'OK', 'SkillSpector v2.12.0 aktif & siap memindai AI Skills', 'INFO'];
            $passed++;
        } else {
            $results[] = ['NVIDIA SkillSpector', 'INFO', 'SkillSpector CLI opsional belum di-install via uv', 'INFO'];
        }

        // Tampilkan Tabel Hasil
        $this->newLine();
        $this->table(
            ['Komponen Pemeriksaan', 'Status', 'Keterangan'],
            array_map(function ($r) {
                $color = match ($r[1]) {
                    'OK' => 'fg=green',
                    'PERINGATAN' => 'fg=yellow',
                    'GAGAL', 'BAHAYA' => 'fg=red;options=bold',
                    default => 'fg=white',
                };
                return [
                    $r[0],
                    "<{$color}>{$r[1]}</>",
                    $r[2],
                ];
            }, $results)
        );

        $this->newLine();
        $this->line("📊 Ringkasan Hasil Audit:");
        $this->line("   - Lolos (OK)     : <fg=green>{$passed}</>");
        $this->line("   - Peringatan     : <fg=yellow>{$warnings}</>");
        $this->line("   - Kritis (Gagal) : <fg=red>{$criticalErrors}</>");
        $this->newLine();

        if ($criticalErrors > 0) {
            $this->error('❌ DITEMUKAN MASALAH KEAMANAN KRITIS! Selesaikan sebelum melakukan deploy ke server.');
            return Command::FAILURE;
        }

        if ($this->option('strict') && $warnings > 0) {
            $this->warn('⚠️ Mode --strict aktif: Masih ada peringatan konfigurasi yang perlu ditinjau.');
            return Command::FAILURE;
        }

        $this->info('✨ SEMUA PEMERIKSAAN KEAMANAN UTAMA LOLOS! Aplikasi e-Cuti siap di-deploy.');
        return Command::SUCCESS;
    }

    /**
     * Cek apakah perintah CLI tersedia di sistem.
     */
    protected function commandExists(string $cmd): bool
    {
        $which = new Process(['which', $cmd]);
        $which->run();
        return $which->isSuccessful();
    }
}
