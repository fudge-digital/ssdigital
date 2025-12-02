<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\IuranBulanan;
use App\Models\PembayaranSiswa;
use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {

            if (!Auth::check()) {
                return;
            }

            $user = Auth::user();
            $role = $user->role;

            // Default values
            $pendingGrouped = collect();
            $requestBillingNotif = collect();
            $combinedNotif = collect();
            $verifiedList = collect();

            $pendingCount = 0;
            $requestBillingCount = 0;
            $verifiedCount = 0;
            $approvedNotifCount = 0;
            $notificationCount = 0;
            $newParentCount = 0;

            // ====================
            // ROLE ADMIN
            // ====================
            if ($role === 'admin') {

                // Pending iuran bulanan
                $pending = IuranBulanan::with('siswa.parents.userProfile')
                    ->where('status', 'pending')
                    ->latest()
                    ->get();

                $pendingGrouped = $pending->groupBy(function ($item) {
                    return $item->siswa->parents->first()->id ?? null;
                })->map(function ($group) {
                    $parent = $group->first()->siswa->parents->first() ?? null;
                    return [
                        'type' => 'pending',
                        'parent' => $parent,
                        'total_transaksi' => $group->count(),
                        'total_nominal' => $group->sum('jumlah'),
                        'created_at' => $group->first()->created_at,
                    ];
                });

                $pendingCount = $pendingGrouped->count();

                // Billing request notif
                $requestBillingNotif = $user->unreadNotifications->filter(function ($notif) {
                    return $notif->type === 'App\\Notifications\\IuranRequestNotification';
                });

                $requestBillingCount = $requestBillingNotif->count();

                // Notifikasi parent baru
                $newParentsNotif = $user->unreadNotifications->filter(function ($notif) {
                    return $notif->type === 'App\\Notifications\\NewParentRegistered';
                })->map(function ($notif) {
                    return [
                        'type' => 'new_parent',
                        'parent' => User::find($notif->data['parent_id']),
                        'created_at' => $notif->created_at,
                    ];
                });

                $newParentCount = $newParentsNotif->count();

                // ====================
                // ADD TO COMBINED FOR ADMIN
                // ====================
                $combinedNotif = collect();

                foreach ($pendingGrouped as $item) {
                    if ($item['parent']) {
                        $combinedNotif->push($item);
                    }
                }

                foreach ($requestBillingNotif as $notif) {
                    $combinedNotif->push([
                        'type' => 'request',
                        'title' => $notif->data['title'],
                        'message' => $notif->data['message'],
                        'created_at' => $notif->created_at,
                    ]);
                }

                foreach ($newParentsNotif as $np) {
                    if ($np['parent']) {
                        $combinedNotif->push($np);
                    }
                }

                // urutkan terbaru
                $combinedNotif = $combinedNotif->sortByDesc('created_at')->values();

                // FINAL badge admin
                $notificationCount = $pendingCount + $requestBillingCount + $newParentCount;
            }

            // ====================
            // ROLE ORANG_TUA
            // ====================
            if ($role === 'orang_tua') {

                // show approved iuran
                $verifiedList = IuranBulanan::where('status', 'paid')
                    ->whereIn('siswa_id', $user->children->pluck('id'))
                    ->latest()
                    ->take(10)
                    ->get();

                $verifiedCount = $verifiedList->count();

                $approvedNotification = $user->unreadNotifications->filter(function ($notif) {
                    return $notif->type === 'App\\Notifications\\IuranApprovedNotification';
                });

                $approvedNotifCount = $approvedNotification->count();

                // FINAL badge parent
                $notificationCount = $approvedNotifCount;

                $view->with([
                    'verifiedList'   => $verifiedList,
                    'verifiedCount'  => $verifiedCount,
                    'approvedNotification' => $approvedNotification,
                    'approvedNotifCount'   => $approvedNotifCount,
                    'notificationCount'    => $notificationCount,
                ]);
            }

            // GLOBAL VALUES
            $view->with([
                'role' => $role,
                'pendingCount'   => $pendingCount,
                'pendingGrouped' => $pendingGrouped,
                'verifiedCount'  => $verifiedCount,
                'verifiedList'   => $verifiedList,
                'requestBillingNotif' => $requestBillingNotif,
                'requestBillingCount' => $requestBillingCount,
                'combinedNotif'       => $combinedNotif,
                'newParentCount'      => $newParentCount,
                'notificationCount'   => $notificationCount,
            ]);
        });
    }
}
