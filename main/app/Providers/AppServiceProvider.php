<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\User;
use App\Model\FundBank;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        view()->composer(['*'], function ($view) {
            
            $super_distributor_pending_kyc = User::where('user_type',4)->join('kyc_docs','kyc_docs.user_id','users.id')->where('kyc_docs.status',0)->count(); 
            $distributor_pending_kyc = User::where('user_type',3)->join('kyc_docs','kyc_docs.user_id','users.id')->where('kyc_docs.status',0)->count();
            $retailer_pending_kyc = User::where('user_type',2)->join('kyc_docs','kyc_docs.user_id','users.id')->where('kyc_docs.status',0)->count();
            $fund_banks = FundBank::where('status',1)->get();
            
            $view->with([
                'super_distributor_pending_kyc' => $super_distributor_pending_kyc,
                'distributor_pending_kyc' => $distributor_pending_kyc,
                'retailer_pending_kyc' => $retailer_pending_kyc,
                'fund_banks' => $fund_banks
            ]);
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
