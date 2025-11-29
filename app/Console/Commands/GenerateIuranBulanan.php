<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\IuranBulanan;
use Carbon\Carbon;

class GenerateIuranBulanan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iuran:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate iuran bulanan untuk semua siswa aktif';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bulanSekarang = Carbon::now()->format('Y-m');

        $siswaList = User::where('role', 'siswa')
            ->whereHas('siswaProfile', fn($q) => $q->where('status', 'aktif'))
            ->get();

        foreach ($siswaList as $siswa) {

            IuranBulanan::firstOrCreate(
                ['siswa_id' => $siswa->id, 'bulan' => $bulanSekarang],
                ['jumlah' => 325000, 'status' => 'pending']
            );
        }

        $this->info("Iuran bulan $bulanSekarang berhasil dibuat untuk {$siswaList->count()} siswa.");
    }
}
