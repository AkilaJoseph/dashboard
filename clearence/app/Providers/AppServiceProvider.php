<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Attachment;
use App\Models\Clearance;
use App\Models\ClearanceApproval;
use App\Models\Department;
use App\Models\User;
use App\Policies\AttachmentPolicy;
use App\Policies\ApprovalPolicy;
use App\Policies\ClearancePolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\UserPolicy;

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
        Gate::policy(Clearance::class,        ClearancePolicy::class);
        Gate::policy(ClearanceApproval::class, ApprovalPolicy::class);
        Gate::policy(Attachment::class,       AttachmentPolicy::class);
        Gate::policy(User::class,             UserPolicy::class);
        Gate::policy(Department::class,       DepartmentPolicy::class);

        \Illuminate\Support\Facades\Notification::extend(
            'webpush',
            fn ($app) => $app->make(\App\Notifications\Channels\WebPushChannel::class)
        );
    }
}
