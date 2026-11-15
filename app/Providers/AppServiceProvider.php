<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->useLangPath(resource_path('lang'));
    }

    public function boot(): void
    {
        Paginator::defaultView('pagination::bootstrap-3');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-3');

        Validator::extend('mobile_phone', function ($attribute, $value) {
            $phone = str_replace('-', '', $value);

            return is_numeric($phone)
                && in_array(substr($phone, 0, 3), ['010', '011', '017', '019']);
        });
    }
}
