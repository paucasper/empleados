<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\AbsenceRequest;
use App\Models\HrRequest;
use App\Models\RequestStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

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
        View::composer('layouts.app-shell', function ($view) {
            $user = Auth::user();

            if (!$user) {
                $view->with('pendingApprovalsCount', 0);
                return;
            }

            $pendingAbsences = AbsenceRequest::where('signer_user_id', $user->id)
                ->where('status', 'pending_signer_signature')
                ->count();

            $pendingExpenses = HrRequest::where('type', HrRequest::TYPE_EXPENSE)
                ->where(function ($q) use ($user) {
                    $q->where(function ($q) use ($user) {
                        $q->where('approver_user_id', $user->id)
                        ->whereHas('status', fn($s) => $s->where('code', 'pending_approval'));
                    })
                    ->orWhere(function ($q) use ($user) {
                        $q->where('admin_user_id', $user->id)
                        ->whereHas('status', fn($s) => $s->where('code', 'pending_admin_approval'));
                    });
                })
                ->count();

            $view->with('pendingApprovalsCount', $pendingAbsences + $pendingExpenses);
        });
    }
}
