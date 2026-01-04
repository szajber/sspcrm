<?php

namespace App\Providers;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        Livewire::component('profile.update-profile-information-form', \App\Livewire\Profile\UpdateProfileInformationForm::class);

        if (Schema::hasTable('company_settings')) {
            View::share('company', CompanySetting::first());
        }
    }
}
