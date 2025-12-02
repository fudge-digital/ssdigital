<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\IuranBulanan;
use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
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

            // ====================
            // ROLE ADMIN
            // ====================
            if ($role === 'admin') {

                // Pending payment grouping
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
                        'created_at' => now(),
                    ];
                });

                $pendingCount = $pendingGrouped->count();

                // Request billing notifications (Collection filter)
                $requestBillingNotif = $user->unreadNotifications->filter(function($notif){
                    return $notif->type === 'App\\Notifications\\IuranRequestNotification';
                });

                $requestBillingCount = $requestBillingNotif->count();

                // ---------------- Parent baru ----------------
                $newParentsNotif = $user->unreadNotifications->filter(function($notif){
                    return $notif->type === 'App\\Notifications\\NewParentRegistered';
                })->map(function($notif){
                    return [
                        'type' => 'new_parent',
                        'parent' => User::find($notif->data['parent_id']),
                        'created_at' => $notif->created_at,
                    ];
                });

                $newParentCount = $newParentsNotif->count();

                // ---------------- Combine all notifications ----------------
                $combinedNotif = collect();

                foreach ($pendingGrouped as $item) {
                    if($item['parent']) {
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
                    if($np['parent']) {
                        $combinedNotif->push($np);
                    }
                }

                // Sort by latest
                $combinedNotif = $combinedNotif->sortByDesc('created_at')->values();
            }

            // ====================
            // ROLE ORANG TUA
            // ====================
            if ($role === 'orang_tua') {

                // List pembayaran approved
                $verifiedList = IuranBulanan::where('status', 'paid')
                    ->whereIn('siswa_id', $user->children->pluck('id'))
                    ->latest()
                    ->take(10)
                    ->get();

                $verifiedCount = $verifiedList->count();

                // Notifications for approved billing requests
                $approvedNotification = $user->unreadNotifications->filter(function($notif){
                    return $notif->type === 'App\\Notifications\\IuranApprovedNotification';
                });

                $approvedNotifCount = $approvedNotification->count();

                // Final badge for parent
                $notificationCount = $approvedNotifCount;

                $view->with([
                    'verifiedList' => $verifiedList,
                    'verifiedCount' => $verifiedCount,
                    'approvedNotification' => $approvedNotification,
                    'approvedNotifCount' => $approvedNotifCount,
                    'notificationCount' => $notificationCount,
                ]);
            }

            // SEND TO ALL VIEWS
            $view->with([
                'role' => $role,
                'pendingCount'   => $pendingCount,
                'pendingGrouped' => $pendingGrouped,
                'verifiedCount'  => $verifiedCount,
                'verifiedList'   => $verifiedList,
                'requestBillingNotif' => $requestBillingNotif,
                'requestBillingCount' => $requestBillingCount,
                'combinedNotif'       => $combinedNotif,
            ]);
        });
    }
}
