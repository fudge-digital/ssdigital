<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\CarbonPeriod;

class IuranRequest extends Model
{
    protected $fillable = ['parent_id', 'months', 'status', 'student_count', 'total_tagihan'];

    public function getMonthListAttribute()
    {
        $start = now()->startOfMonth()->addMonth();
        $end = $start->copy()->addMonths($this->months - 1);

        $period = CarbonPeriod::create($start, '1 month', $end);

        $months = [];
        foreach ($period as $date) {
            $months[] = $date->translatedFormat('F Y');
        }

        return $months; // array
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

}
