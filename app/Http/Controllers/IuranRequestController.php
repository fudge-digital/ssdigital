<?php

namespace App\Http\Controllers;

use App\Models\IuranRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\IuranRequestCreated;
use App\Notifications\IuranRequestApproved;

class IuranRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'months' => 'required|array|min:1'
        ]);

        $months = implode(',', $request->months);

        $iuranRequest = IuranRequest::create([
            'parent_id' => auth()->id(),
            'months' => $months,
            'status' => 'pending'
        ]);

        // Notifikasi ke admin
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new IuranRequestCreated($iuranRequest));

        return back()->with('success', 'Request berhasil diajukan. Menunggu persetujuan admin.');
    }

    public function approve(IuranRequest $requestIuran)
    {
        $requestIuran->update(['status' => 'approved']);

        Notification::send($requestIuran->parent, new IuranRequestApproved($requestIuran));

        return back()->with('success', 'Request telah disetujui');
    }

    public function reject(IuranRequest $requestIuran)
    {
        $requestIuran->update(['status' => 'rejected']);

        return back()->with('success', 'Request ditolak');
    }
}

