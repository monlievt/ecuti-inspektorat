<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAllUserPasswordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-passwords {password? : Kata sandi baru (opsional, jika dikosongkan akan diminta secara tersembunyi)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset kata sandi semua pengguna secara aman';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $newPassword = $this->argument('password');

        if (empty($newPassword)) {
            $newPassword = $this->secret('Masukkan kata sandi baru untuk semua user');
            $confirmation = $this->secret('Ulangi kata sandi baru untuk konfirmasi');

            if ($newPassword !== $confirmation) {
                $this->error('❌ Kata sandi dan konfirmasi kata sandi tidak cocok!');
                return self::FAILURE;
            }
        }

        if (strlen((string) $newPassword) < 6) {
            $this->error('❌ Kata sandi minimal harus 6 karakter.');
            return self::FAILURE;
        }

        $hashed = Hash::make($newPassword);

        $total = User::query()->update(['password' => $hashed]);

        $this->info("✅ Berhasil memperbarui kata sandi untuk {$total} pengguna secara aman.");

        return self::SUCCESS;
    }
}
