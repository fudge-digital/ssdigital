<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\IuranBulanan;
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
                    ->get();

                $pendingGrouped = $pending->groupBy(function ($item) {
                    return $item->siswa->parents->first()->id ?? null;
                })->map(function ($group) {
                    $parent = $group->first()->siswa->parents->first();
                    return [
                        'type' => 'pending',
                        'parent' => $parent,
                        'total_transaksi' => $group->count(),
                        'total_nominal' => $group->sum('jumlah'),
                        'created_at' => now(),
                    ];
                });

                $pendingCount = $pendingGrouped->count();

                // Request billing notifications
                $requestBillingNotif = $user->unreadNotifications()
                    ->where('type', 'App\\Notifications\\IuranRequestNotification')
                    ->get();

                $requestBillingCount = $requestBillingNotif->count();

                // Combine both
                foreach ($pendingGrouped as $item) {
                    $combinedNotif->push($item);
                }

                foreach ($requestBillingNotif as $notif) {
                    $combinedNotif->push([
                        'type' => 'request',
                        'title' => $notif->data['title'],
                        'message' => $notif->data['message'],
                        'created_at' => $notif->created_at,
                    ]);
                }

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
                $approvedNotification = $user->unreadNotifications()
                    ->where('type', 'App\\Notifications\\IuranApprovedNotification')
                    ->get();

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
