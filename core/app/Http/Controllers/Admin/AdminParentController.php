<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\IuranBulanan;
use Illuminate\Http\Request;

class AdminParentController extends Controller
{
    public function index()
    {
        $parents = User::where('role', 'orang_tua')
                    ->withCount('children')
                    ->orderBy('name')
                    ->paginate(20);

        return view('admin.parent.index', compact('parents'));
    }

    public function updatePromo(Request $request, $id)
    {
        $request->validate([
            'promo_type' => 'required|string|in:none,sibling,sponsor,beasiswa'
        ]);

        $parent = User::where('role','orang_tua')->findOrFail($id);
        $parent->promo_type = $request->promo_type;
        $parent->save();

        $prices = config('promo.prices');
        $newPrice = $prices[$parent->promo_type] ?? $prices['none'];

        IuranBulanan::whereIn('siswa_id', $parent->children->pluck('id'))
            ->where('status', 'pending')
            ->update(['jumlah' => $newPrice]);

        return back()->with('success','Promo parent diperbarui.');
    }
}
