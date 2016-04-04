<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 휴대폰 국번 확인
        \Validator::extend('mobile_phone', function($attribute, $value, $parameters, $validator) {
            $phone = str_replace ('-', '', $value);
            $mobile_sp = substr($phone, 0, 3);

            return is_numeric($phone) && in_array($mobile_sp, ['010', '011', '017', '019']);
        });

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
