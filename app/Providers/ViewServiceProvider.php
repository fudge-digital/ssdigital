<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\ParentFinanceSummaryService;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('partials.iuran-summary', function ($view) {
            if (auth()->check() && auth()->user()->role === 'orang_tua') {
                $summaryService = app(\App\Services\ParentFinanceSummaryService::class);
                $view->with('iuranSummary', $summaryService->getSummary());
            }
        });
    }
}
