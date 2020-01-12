<?php

namespace App\Providers;

use App\User;
use App\Models\Petugas;
use App\Models\Keluhan;
use App\Models\Saran;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        /**
         * Register Policy
         */
        
         // Petugas Policy
         Gate::define('admin', function ($petugas) {
            return $petugas->role == 'super admin' || $petugas->role == 'admin';
        });
        // Petugas Policy Ends

        $this->app['auth']->viaRequest('api', function ($request) {
            return app('auth')->setRequest($request)->user();
            /*if ($request->input('api_token')) {
                return Petugas::where('api_token', $request->input('api_token'))->first();
            }*/
        });
    }
}
