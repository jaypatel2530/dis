<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('send_login_otp', 'ApiController@sendLoginOtp');
Route::post('verify_login_otp', 'ApiController@verifyLoginOtp');
Route::post('update_payment_status', 'ApiController@updatePaymentStatus');
Route::post('my_passbook', 'ApiController@getMyPassbook');

Route::post('user_verify', 'ApiController@userVerify');
Route::post('get_states', 'ApiController@getStates');
Route::post('get_cities', 'ApiController@getCities');
Route::post('user_registration', 'ApiController@userRegistration');
Route::post('registration_update', 'ApiController@registrationUpdate');

Route::post('mobile_login', 'ApiController@mobileLogin');
Route::post('create_kyc', 'ApiController@createKyc');
Route::post('create_va', 'ApiController@createVA');
Route::post('user_detail', 'ApiController@userDetail');

Route::post('category_list', 'ApiController@getCategoryList');
Route::post('create_upi', 'ApiController@createUPI');

Route::get('get_bank_list', 'ApiController@getBankList'); //callback

Route::post('quick_transfer_callback', 'ApiController@quickTransferCallback'); //callback
Route::post('upi_credit_callback', 'ApiController@upiCreditCallback'); //callback
Route::post('va_credit_callback', 'ApiController@vaCreditCallback');  //callback
Route::post('fund_added_callback', 'ApiController@fundAddedCallback'); //callback

Route::post('fund_added_history', 'ApiController@fundAddedHistory');

Route::post('add_shop', 'ApiController@addShop');
Route::post('get_shop', 'ApiController@getShop');

//eko_aeps
Route::post('aeps_request_otp', 'AepsApiController@aepsRequestOtp');
Route::post('aeps_verify_otp', 'AepsApiController@aepsVerifyOtp');
Route::post('aeps_onboard', 'AepsApiController@aepsOnboard');
Route::post('aeps_activate_service', 'AepsApiController@aepsActivateService');
Route::post('aeps_user_service_enquiry', 'AepsApiController@aepsUserServiceEnquiry');
Route::post('aeps_keys_data', 'AepsApiController@aepsKeysData');
Route::any('aeps_callback_url', 'AepsApiController@aepsCallbackUrl'); //callback
Route::post('aeps_transaction_history', 'AepsApiController@aepsTransactionHistory');

Route::post('add_beneficiary', 'ApiController@addBeneficiary');
Route::post('get_beneficiary', 'ApiController@getBeneficiary');
Route::post('delete_beneficiary', 'ApiController@deleteBeneficiary');

Route::post('quick_transfer', 'ApiController@quickTransfer');
Route::post('quick_transfer_hypto', 'ApiController@quickTransferHypto');
Route::post('quick_transfer_history', 'ApiController@quickTransferHistory');

Route::post('quick_upi_transfer', 'ApiController@quickUPItransfer');
Route::post('quick_upi_transfer_history', 'ApiController@quickUPItransferHistory');

Route::post('transaction_detail', 'ApiController@transactionDetail');
Route::post('check_refund_status', 'ApiController@checkRefundStatus');

Route::post('upi_collection_history', 'ApiController@upiCollectionHistory');
Route::post('vka_collection_history', 'ApiController@vkaCollectionHistory');

Route::post('commission_report', 'ApiController@commissionReport');
Route::post('user_va_accounts', 'ApiController@userVAaccounts');

Route::post('banks_list', 'ApiController@getBanks');
Route::post('create_money_request', 'ApiController@createMoneyRequest');
Route::post('money_request_report', 'ApiController@moneyRequestReport');
Route::post('change_money_request_status', 'ApiController@moneyReuquestChangeStatus');

Route::post('get_fund_bank', 'ApiController@getFundBank');
Route::post('my_passbook', 'ApiController@myPassbook');
Route::post('refund_report', 'ApiController@refundReport');
Route::post('profile_update', 'ApiController@profileUpdate');

Route::post('user_verify_bank_account', 'ApiController@getUserVerifyBankAccount');
Route::post('get_commissions', 'ApiController@getCommissions');

Route::post('raise_dispute', 'ApiController@raiseDispute');
Route::post('get_dispute', 'ApiController@getDispute');

//settlement
Route::post('do_settlement', 'ApiController@doSettlement');
Route::post('settlement_history', 'ApiController@settlementHistory');

//BBPS_ROUNDPAY
Route::post('roundpay_fetch_bill', 'BbpsApiController@roundpayFetchBill');
Route::post('roundpay_pay_bill', 'BbpsApiController@roundpayPayBill');
Route::get('roundpay_callback', 'BbpsApiController@roundpayCallback'); //callback
Route::get('vastwebindia_callback', 'BbpsApiController@vastwebindiaCallback'); //callback

//ServiceController
Route::post('operators', 'ServiceApiController@getOperators');
Route::post('get_recharge_plans', 'ServiceApiController@getRechargePlans');
Route::post('get_recharge_roffer', 'ServiceApiController@getRechargeRoffer');
Route::post('get_dth_info', 'ServiceApiController@getDthInfo');
Route::post('get_dth_offer', 'ServiceApiController@getDthOffer');

Route::post('recharge_service', 'ServiceApiController@rechargeService');
Route::post('services_history', 'ServiceApiController@getServicesHistory');

Route::post('change_pin', 'ServiceApiController@changePin');
Route::post('google_pay_code', 'ServiceApiController@googlepaycode');

//notifications
Route::post('get_notifications', 'ApiController@getNotifications');
Route::post('update_notifications', 'ApiController@updateNotifications');

//paytm
Route::post('get_paytm_order_id', 'ApiController@getPaytmOrderId');
Route::get('paytm_callback', 'ApiController@paytmCallback');
Route::post('online_fund_history', 'ApiController@onlineFundHistory');

//payu
Route::post('get_payu_order_id', 'ApiController@getPayuOrderId');
Route::post('payu_callback', 'ApiController@payuCallback');


//cashfree
Route::post('get_cashfree_pg_token', 'TestApiController@getCashfreePGtoken');
Route::post('cashfree_callback', 'TestApiController@cashfreeCallback');
Route::post('cashfree_transactions_history', 'TestApiController@cashfreeTransactionsHistory');
Route::post('cashfree_update_txn_status', 'TestApiController@cashfreeUpdateTxnStatus');

//upi request
Route::post('upi_request', 'TestApiController@upiRequest');
Route::post('upi_request_history', 'TestApiController@upiRequestHistory');
Route::post('upi_request_callback', 'TestApiController@upiRequestCallback');

//Eko initiate fund transfer
Route::post('eko_initiate_fund_transfer', 'TestApiController@ekoInitiateFundTransfer');

Route::post('send_sms', 'ApiController@sendSMS');
Route::get('send_notify', 'TestApiController@getSendNotify');

Route::get('demo_request', 'DemoController@demoRequest');

//Softcare
// Route::get('softcarerechargecallback', 'ServiceApiController@softcareRechargeCallback');
// Route::get('softcarecallbackbbps', 'TestApiController@softcareBbpsCallback');
// Route::get('softcarecallbackaeps', 'TestApiController@softcareAepsCallback');
// Route::get('softcarecallbackimps', 'TestApiController@softcareImpsCallback');

// Route::post('softcare_bbps_categories', 'SoftcareBbpsController@getBbpsCategories');
// Route::post('softcare_bbps_billers', 'SoftcareBbpsController@getBbpsBillers');
// Route::post('softcare_bbps_biller_params', 'SoftcareBbpsController@getBbpsBillerParams');
// Route::post('softcare_bbps_billfetch', 'SoftcareBbpsController@getBbpsBillfetch');
// Route::post('softcare_bbps_paybill', 'SoftcareBbpsController@getBbpsPaybill');

// CHANGES
// Route::get('adipay_recharge_callback', 'TestApiController@adiPayRechargeCallback');
// Route::get('payworld_aeps_callback', 'TestApiController@payworldAepsCallback');
// Route::get('payworld_aeps_verification_callback', 'TestApiController@payworldAepsVerificationCallback');
// APIBOX 27-01-2020
Route::post('apibox_fetchbill', 'ApiboxBbpsController@apiboxFetchBill');
Route::post('apibox_paybill', 'ApiboxBbpsController@apiboxPayBill');
Route::get('apibox_paybill_callback', 'ApiboxBbpsController@apiboxPayBillCallback');


Route::get('instantpay_dmt_callback', 'TestApiController@instantpayDmtCallback');

Route::post('app_banners', 'ApiController@getAppBanners');