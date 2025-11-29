<?php

namespace App\Services;

use App\Models\IuranBulanan;
use Illuminate\Support\Facades\Auth;

class ParentFinanceSummaryService
{
    public function getSummary()
    {
        $parent = Auth::user();
        $siswaIds = $parent->children->pluck('id');


        return [
            'totalPending' => IuranBulanan::whereIn('siswa_id', $siswaIds)
                                ->where('status', 'unpaid')
                                ->sum('jumlah'),

            'countPending' => IuranBulanan::whereIn('siswa_id', $siswaIds)
                                ->where('status', 'unpaid')
                                ->count(),
        ];
    }
}
