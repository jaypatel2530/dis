<?php

/*
|--------------------------------------------------------------------------
| Web Routes - ISHWAR BHATI
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('member', ['as' => 'get:member', 'uses' => 'HomeController@getMember']);
Route::post('add-member-front', ['as' => 'post:add_member_front', 'uses' => 'HomeController@postAddMemberfFront']);
Route::post('member', ['as' => 'post:member', 'uses' => 'HomeController@postMember']);
Route::post('get-member-years', ['as' => 'post:get_member_years', 'uses' => 'HomeController@postGetMemberYears']);
Route::post('get-payment-method', ['as' => 'post:get_payment_method', 'uses' => 'HomeController@postGetPaymentMethod']);


Route::get('payment', ['as' => 'get:payment', 'uses' => 'HomeController@getPayment']);

Route::get('image', ['as' => 'get:image', 'uses' => 'HomeController@getImage']);
Route::post('image', ['as' => 'post:image', 'uses' => 'HomeController@postImage']);

Route::get('terms', ['as' => 'get:terms', 'uses' => 'HomeController@getTerms']);


Route::get('sendimage', ['as' => 'get:send_image', 'uses' => 'HomeController@getSendImage']);

Route::get('/email', function () {
    return view('new_user');
});

Route::get('/', function () {
    return view('auth.login');
});

Route::get('distributor-registration', ['as' => 'get:distributor_registration', 'uses' => 'Auth\RegisterController@getDistributorRegistration']);
Route::post('distributor-registration', ['as' => 'post:distributor_registration', 'uses' => 'Auth\RegisterController@postDistributorRegistration']);
Route::post('get-stateid-city', ['as' => 'post:get_stateid_city', 'uses' => 'Auth\RegisterController@getStatesIdCity']);
Route::get('chat-page/{number}/{name}', ['as' => 'get:chat_page', 'uses' => 'Controller@getChatPage']);

Auth::routes();

Route::get('/', function () {
    if(Auth::check()) { return redirect('/dashboard');} 
    else {return view('auth.login');}
});

Route::get('forgot-password', ['as' => 'get:forgot_password', 'uses' => 'Auth\LoginController@getForgotPassword']);
Route::post('send-forgot-otp', ['as'=>'post:send_forgot_otp','uses'=>'Auth\LoginController@forgotPasswordSendOtp']);
Route::post('verify-forgot-otp', ['as'=>'post:verify_forgot_otp','uses'=>'Auth\LoginController@forgotPasswordVerifyOtp']);


Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
Route::get('/dashboard', 'HomeController@index')->name('dashboard');
Route::post('get-inflow-outflow', ['as' => 'post:get_inflow_outflow', 'uses' => 'HomeController@getInflowOutflow']);

Route::get('logs', ['as' => 'get:logs', 'uses' => 'AdminController@getlogs']);

Route::get('scanner', ['as' => 'get:scanner', 'uses' => 'HomeController@getsScanner']);
Route::post('scanner-data', ['as' => 'post:scanner_data', 'uses' => 'HomeController@postsScannerData']);

Route::get('profile', ['as' => 'get:profile', 'uses' => 'HomeController@getProfile']);
Route::post('profile', ['as' => 'post:profile', 'uses' => 'HomeController@postProfile']);
Route::post('add_user_bank', ['as' => 'post:add_user_bank', 'uses' => 'HomeController@postAddUserBank']);

Route::post('get-state-city', ['as' => 'post:get_state_city', 'uses' => 'HomeController@getStatesCity']);
Route::post('get-edit-state-city', ['as' => 'post:get_edit_state_city', 'uses' => 'HomeController@getEditStatesCity']);
Route::post('change-password', ['as' => 'post:change_password', 'uses' => 'HomeController@changePassword']);

//google code
Route::get('add-google-code', ['as' => 'get:add_google_code', 'uses' => 'AdminController@getAddGoogleCode']);
Route::post('add-google-code', ['as' => 'post:add_google_code', 'uses' => 'AdminController@postAddGoogleCode']);
Route::get('manage-google-code', ['as' => 'get:manage_google_code', 'uses' => 'AdminController@getManageGoogleCode']);
Route::get('manage-google-code-data', ['as' => 'get:manage_google_code_data', 'uses' => 'AdminController@getManageGoogleCodeData']);
Route::get('purchase-google-code-data', ['as' => 'get:purchase_google_code_data', 'uses' => 'AdminController@getPurchaseGoogleCodeData']);
Route::get('purchase-google-code', ['as' => 'get:purchase_google_code', 'uses' => 'AdminController@getPurchaseGoogleCode']);
Route::get('delete-google-code/{id}', ['as' => 'get:delete_google_code', 'uses' => 'AdminController@getDeleteGoogleCode']);

//bank_details
Route::get('add-bank-detail', ['as' => 'get:add_bank_detail', 'uses' => 'MoneyRequestController@getAddBankDetails']);
Route::post('add-bank-details', ['as' => 'post:add_bank_details', 'uses' => 'MoneyRequestController@postAddBankDetails']);
Route::get('manage-bank-details', ['as' => 'get:manage_bank_details', 'uses' => 'MoneyRequestController@getBankDetails']);
Route::get('manage-bank-details-data', ['as' => 'get:manage_bank_detail_data', 'uses' => 'MoneyRequestController@getBankDetailsData']);

//add_money_distributor
Route::get('add-money', ['as' => 'get:add_money', 'uses' => 'MoneyRequestController@getAddMoney']);
Route::post('add-money', ['as' => 'post:add_money', 'uses' => 'MoneyRequestController@postAddMoney']);
Route::get('add-money-report', ['as' => 'get:add_money_report', 'uses' => 'MoneyRequestController@getAddMoneyReport']);
Route::get('add-money-report-data', ['as' => 'get:add_money_report_data', 'uses' => 'MoneyRequestController@getAddMoneyReportData']);

//money_request_retailer
Route::get('money-requests', ['as' => 'get:money_requests', 'uses' => 'MoneyRequestController@getMoneyRequests']);
Route::get('money-requests-data', ['as' => 'get:money_requests_data', 'uses' => 'MoneyRequestController@getMoneyRequestsData']);
Route::post('money-requests-change-status', ['as' => 'post:money_requests_change_status', 'uses' => 'MoneyRequestController@moneyReuquestChangeStatus']); 
Route::get('money-requests-report', ['as' => 'get:money_requests_report', 'uses' => 'MoneyRequestController@getMoneyRequestsReport']);    
Route::get('money-requests-report-data', ['as' => 'get:money_requests_report_data', 'uses' => 'MoneyRequestController@getMoneyRequestsReportData']);    
    
Route::get('retailers/manage-retailers', ['as' => 'get:manage_retailer', 'uses' => 'DistributorController@getManageRetailer']);
Route::get('retailers/manage-retailers-data', ['as' => 'get:manage_retailer_data', 'uses' => 'DistributorController@getManageRetailerData']);  

Route::post('member/view-member', ['as' => 'post:view_member', 'uses' => 'DistributorController@postViewMember']);

Route::get('member/renewal', ['as' => 'get:member_renewal', 'uses' => 'DistributorController@getMemberRenewal']);
Route::post('member/renewal', ['as' => 'post:member_renewal', 'uses' => 'DistributorController@postMemberRenewal']);

Route::get('member/add-member', ['as' => 'get:add_member', 'uses' => 'DistributorController@getAddMember']);
Route::post('member/add-member', ['as' => 'post:add_member', 'uses' => 'DistributorController@postAddMember']);

Route::get('member/edit-member/{idd}', ['as' => 'get:edit_member', 'uses' => 'DistributorController@getEditMember']);
Route::post('member/edit-member', ['as' => 'post:edit_member', 'uses' => 'DistributorController@postEditMember']);

Route::post('member/delete-member', ['as' => 'post:delete_member', 'uses' => 'DistributorController@postDeleteMember']);

Route::get('member/manage-member', ['as' => 'get:manage_member', 'uses' => 'DistributorController@getManageMember']);
Route::get('member/manage-member-data', ['as' => 'get:manage_member_data', 'uses' => 'DistributorController@getManageMemberData']);  

Route::get('passbook/retailer-passbook/{mobile}', ['as' => 'get:retailer_passbook', 'uses' => 'DistributorController@getRetailerPassbook']);
Route::get('passbook/retailer-passbook-data', ['as' => 'get:retailer_passbook_data', 'uses' => 'DistributorController@getRetailerPassbookData']);

Route::get('passbook/distributor-passbook/{mobile}', ['as' => 'get:distributor_passbook', 'uses' => 'DistributorController@getDistributorPassbook']);
Route::get('passbook/distributor-passbook-data', ['as' => 'get:distributor_passbook_data', 'uses' => 'DistributorController@getDistributorPassbookData']);

Route::get('passbook/super-distributor-passbook/{mobile}', ['as' => 'get:super_distributor_passbook', 'uses' => 'DistributorController@getSuperDistributorPassbook']);
Route::get('passbook/super-distributor-passbook-data', ['as' => 'get:super_distributor_passbook_data', 'uses' => 'DistributorController@getSuperDistributorPassbookData']);

Route::get('reports/retailers-transactions-report', ['as' => 'get:retailers_transactions_report', 'uses' => 'ReportsController@getRetailersTransactionsReport']);
Route::get('reports/retailers-transactions-data', ['as' => 'get:retailers_transactions_report_data', 'uses' => 'ReportsController@getRetailersTransactionsReportData']);

Route::get('reports/fund-added-report', ['as' => 'get:fund_added_report', 'uses' => 'ReportsController@getFundAddedReport']);
Route::get('reports/fund-added-report-data', ['as' => 'get:fund_added_report_data', 'uses' => 'ReportsController@getFundAddedReportData']);

Route::post('change-service-status', ['as' => 'post:change_service_status', 'uses' => 'AdminController@changeServiceStatus']);

Route::post('check-mobile-registration', ['as' => 'post:check_mobile_registration', 'uses' => 'HomeController@checkMobileRegistration']);
Route::post('check-pan-registration', ['as' => 'post:check_pan_registration', 'uses' => 'HomeController@checkPanRegistration']);
Route::post('check-aadhaar-registration', ['as' => 'post:check_aadhaar_registration', 'uses' => 'HomeController@checkAadhaarRegistration']);

Route::get('upi/upi-request', ['as' => 'get:upi_request', 'uses' => 'HomeController@getUPIRequest']);
Route::post('upi/upi-request', ['as' => 'post:upi_request', 'uses' => 'HomeController@postUPIRequest']);
Route::get('upi/upi-request-report', ['as' => 'get:upi_request_report', 'uses' => 'HomeController@getUPIRequestReport']);
Route::get('upi/upi-request-report-data', ['as' => 'get:upi_request_report_data', 'uses' => 'HomeController@getUPIRequestReportData']);

Route::get('upload-kyc-documents', ['as' => 'get:upload_kyc_documents', 'uses' => 'DistributorController@getUploadKycDocuments']);
Route::post('post-upload-kyc-documents', ['as' => 'post:upload_kyc_documents', 'uses' => 'DistributorController@postUploadKycDocuments']);

Route::get('passbook/my-passbook', ['as' => 'get:my_passbook', 'uses' => 'DistributorController@getMyPassbook']);

Route::get('retailers/add-retailer', ['as' => 'get:add_retailer', 'uses' => 'DistributorController@getAddRetailer']);
Route::post('retailers/add-retailer', ['as' => 'post:add_retailer', 'uses' => 'DistributorController@postAddRetailer']);
	
Route::get('retailers/edit-retailer/{mobile}', ['as' => 'get:edit_retailer', 'uses' => 'DistributorController@getEditRetailer']);
Route::post('retailers/edit-retailer', ['as' => 'post:edit_retailer', 'uses' => 'DistributorController@postEditRetailer']);	
	Route::post('add-money-to-user', ['as' => 'post:add_money_to_user', 'uses' => 'AdminController@postAddMoneyToCustomer']);

//DISTRIBUTORS-ROUTES-STARTS-----------------------------------------------------------------------------------------
Route::group(['middleware' => 'App\Http\Middleware\DistributorMiddleware'], function() {

	Route::get('reports/commission-report', ['as' => 'get:commission_report', 'uses' => 'DistributorController@getCommissionReport']);
    Route::get('reports/commission-report-data', ['as' => 'get:commission_report_data', 'uses' => 'DistributorController@getCommissionReportData']);
	
	
	
	Route::get('retailers-ids/purchase-retailer-ids', ['as' => 'get:purchase_retailer_ids', 'uses' => 'DistributorController@getPurchaseRetailerIds']);
	Route::post('retailers-ids/purchase-retailer-ids', ['as' => 'post:purchase_retailer_ids', 'uses' => 'DistributorController@postPurchaseRetailerIds']);
	
	Route::get('retailers-ids/purchase-retailer-ids-report', ['as' => 'get:purchase_retailer_ids_report', 'uses' => 'DistributorController@getPurchaseRetailerIdsReport']);
	Route::get('retailers-ids/purchase-retailer-ids-report-data', ['as' => 'get:purchase_retailer_ids_report_data', 'uses' => 'DistributorController@getPurchaseRetailerIdsReportData']);
	
	Route::post('distributor/add-money-to-user', ['as' => 'post:distributor_add_money_to_user', 'uses' => 'DistributorController@postDistributorAddMoneyToCustomer']);
	
});

//ADMIN-ROUTES_STARTS------------------------------------------------------------------------------------------------
Route::group(['middleware' => 'App\Http\Middleware\AdminMiddleware'], function() {
    
    Route::get('add-event', ['as' => 'get:add_event', 'uses' => 'AdminController@getAddEvent']);
    Route::post('add-event', ['as' => 'post:add_event', 'uses' => 'AdminController@postAddEvent']);
    
    Route::get('manage-event', ['as' => 'get:manage_event', 'uses' => 'AdminController@getManageEvent']);
    Route::get('manage-event-data', ['as' => 'get:manage_event_data', 'uses' => 'AdminController@postManageEventData']);
    
    Route::post('delete-event', ['as' => 'post:delete_event', 'uses' => 'AdminController@postDeleteEvent']);
    
    Route::get('event-attendance', ['as' => 'get:event_attendance', 'uses' => 'AdminController@getEventAttendance']);
    Route::get('event-attendance-data', ['as' => 'get:event_attendance_data', 'uses' => 'AdminController@getEventAttendanceData']);

    Route::get('super-distributors/add-super-distributor', ['as' => 'get:add_super_distributor', 'uses' => 'AdminController@getAddSuperDistributor']);
	Route::post('super-distributors/add-super-distributor', ['as' => 'post:add_super_distributor', 'uses' => 'AdminController@postAddSuperDistributor']);
	Route::get('super-distributors/manage-super-distributors', ['as' => 'get:manage_super_distributors', 'uses' => 'AdminController@getManageSuperDistributors']);
	Route::get('super-distributors/manage-super-distributor-data', ['as' => 'get:manage_super_distributors_data', 'uses' => 'AdminController@getManageSuperDistributorsData']);
    
	Route::get('distributors/add-distributor', ['as' => 'get:add_distributor', 'uses' => 'AdminController@getAddDistributor']);
	Route::post('distributors/add-distributor', ['as' => 'post:add_distributor', 'uses' => 'AdminController@postAddDistributor']);
	Route::get('distributors/manage-distributors', ['as' => 'get:manage_distributors', 'uses' => 'AdminController@getManageDistributors']);
	Route::get('distributors/manage-distributor-data', ['as' => 'get:manage_distributors_data', 'uses' => 'AdminController@getManageDistributorsData']);
    
    
    
    Route::get('manage-kyc/retailers-pending-kyc', ['as' => 'get:retailers_pending_kyc', 'uses' => 'AdminController@getRetailersPendingKyc']);
    Route::get('manage-kyc/retailers-pending-kyc-data', ['as' => 'get:retailers_pending_kyc_data', 'uses' => 'AdminController@getRetailersPendingKycData']);
    Route::get('manage-kyc/distributors-pending-kyc', ['as' => 'get:distributors_pending_kyc', 'uses' => 'AdminController@getDistributorsPendingKyc']);
    Route::get('manage-kyc/distributors-pending-kyc-data', ['as' => 'get:distributors_pending_kyc_data', 'uses' => 'AdminController@getDistributorsPendingKycData']);
    Route::get('manage-kyc/super-distributors-pending-kyc', ['as' => 'get:super_distributors_pending_kyc', 'uses' => 'AdminController@getSuperDistributorsPendingKyc']);
    Route::get('manage-kyc/super-distributors-pending-kyc-data', ['as' => 'get:super_distributors_pending_kyc_data', 'uses' => 'AdminController@getSuperDistributorsPendingKycData']);
    Route::post('manage-kyc/get-kyc-docs', ['as' => 'post:get_kyc_docs', 'uses' => 'AdminController@getKYCdocs']);
	Route::post('manage-kyc/update-kyc-status', ['as' => 'post:update_kyc_status', 'uses' => 'AdminController@updateKYCstatus']);
	
	Route::get('add-eko-bank', ['as' => 'get:add_eko_bank', 'uses' => 'AdminController@getAddEkoBanks']);
    Route::post('add-eko-bank', ['as' => 'post:add_eko_bank', 'uses' => 'AdminController@postAddEkoBanks']);
    Route::get('manage-eko-bank', ['as' => 'get:manage_eko_bank', 'uses' => 'AdminController@getManageEkoBanks']);
    
    Route::get('supports/manage-pending-disputes', ['as' => 'get:manage_pending_disputes', 'uses' => 'AdminController@getManagePendingDisputes']);
	Route::post('reply-to-dispute', ['as' => 'post:reply_to_dispute', 'uses' => 'AdminController@postReplyToDispute']);
	Route::get('supports/disputes-report', ['as' => 'get:disputes_report', 'uses' => 'AdminController@getDisputesReport']);
	
	Route::get('super-distributor-money-requests', ['as' => 'get:super_distributor_money_requests', 'uses' => 'MoneyRequestController@getSuperDistributorMoneyRequests']);
    Route::get('super-distributor-money-requests-report', ['as' => 'get:super_distributor_money_requests_report', 'uses' => 'MoneyRequestController@getSuperDistributorMoneyRequestsReport']); 
	
	Route::get('distributor-money-requests', ['as' => 'get:distributor_money_requests', 'uses' => 'MoneyRequestController@getDistributorMoneyRequests']);
    Route::get('distributor-money-requests-report', ['as' => 'get:distributor_money_requests_report', 'uses' => 'MoneyRequestController@getDistributorMoneyRequestsReport']);    
    
    Route::get('retailer-money-requests', ['as' => 'get:retailer_money_requests', 'uses' => 'MoneyRequestController@getRetailerMoneyRequests']);
    Route::get('retailer-money-requests-report', ['as' => 'get:retailers_money_requests_report', 'uses' => 'MoneyRequestController@getMoneyRetailerRequestsReport']);    
    
    Route::get('mapped-retailer-money-requests-report', ['as' => 'get:mapped_retailers_money_requests_report', 'uses' => 'MoneyRequestController@getMappedMoneyRetailerRequestsReport']);    
    
    Route::get('reports/dmt-report', ['as' => 'get:dmt_report', 'uses' => 'ReportsController@getDmtReport']);
    Route::get('reports/dmt-report-data', ['as' => 'get:dmt_report_data', 'uses' => 'ReportsController@getDmtReportData']);
    
    Route::get('reports/aeps-report', ['as' => 'get:aeps_report', 'uses' => 'ReportsController@getAepsReport']);
    Route::get('reports/aeps-report-data', ['as' => 'get:aeps_report_data', 'uses' => 'ReportsController@getAepsReportData']);
    
    Route::get('reports/upi-collection-report', ['as' => 'get:upi_collection_report', 'uses' => 'ReportsController@getUpiCollectionReport']);
    Route::get('reports/upi-collection-report-data', ['as' => 'get:upi_collection_report_data', 'uses' => 'ReportsController@getUpiCollectionReportData']);

    Route::get('reports/va-collection-report', ['as' => 'get:va_collection_report', 'uses' => 'ReportsController@getVACollectionReport']);
    Route::get('reports/va-collection-report-data', ['as' => 'get:va_collection_report_data', 'uses' => 'ReportsController@getVACollectionReportData']);
    
    Route::get('reports/recharge-report', ['as' => 'get:recharge_report', 'uses' => 'ReportsController@getRechargeReport']);
    Route::get('reports/recharge-report-data', ['as' => 'get:recharge_report_data', 'uses' => 'ReportsController@getRechargeReportData']);
    
    Route::get('reports/bill-payments-report', ['as' => 'get:bill_payments_report', 'uses' => 'ReportsController@getBillPaymentsReport']);
    Route::get('reports/bill-payments-report-data', ['as' => 'get:bill_payments_report_data', 'uses' => 'ReportsController@getBillPaymentsReportData']);
    
    Route::get('reports/distirbutors-commission-report', ['as' => 'get:distributors_commission_report', 'uses' => 'ReportsController@getDistributorsCommissionReport']);
    Route::get('reports/distirbutors-commission-report-data', ['as' => 'get:distributors_commission_report_data', 'uses' => 'ReportsController@getDistributorsCommissionReportData']);

    Route::get('reports/retailers-commission-report', ['as' => 'get:retailers_commission_report', 'uses' => 'ReportsController@getRetailersCommissionReport']);
    Route::get('reports/retailers-commission-report-data', ['as' => 'get:retailers_commission_report_data', 'uses' => 'ReportsController@getRetailersCommissionReportData']);
    
    Route::get('reports/refund-report', ['as' => 'get:refund_report', 'uses' => 'ReportsController@getRefundReport']);
    Route::get('reports/refund-report-data', ['as' => 'get:refund_report_data', 'uses' => 'ReportsController@getRefundReportData']);

    Route::get('reports/distributor-wallet-report', ['as' => 'get:distributor_wallet_report', 'uses' => 'ReportsController@getDistributorWalletReport']);
    Route::get('reports/distributor-wallet-report-data', ['as' => 'get:distributor_wallet_report_data', 'uses' => 'ReportsController@getDistributorWalletReportData']);
    
    Route::get('reports/upi-request-report', ['as' => 'get:upi_request_admin_report', 'uses' => 'ReportsController@getUPIRequestAdminReport']);
    Route::get('reports/upi-request-report-data', ['as' => 'get:upi_request_admin_report_data', 'uses' => 'ReportsController@getUPIRequestAdminReportData']);

    Route::get('mapping/distributor-mapping', ['as' => 'get:distributor_mapping', 'uses' => 'AdminController@getDistributorMapping']);
    Route::post('mapping/distributor-mapping', ['as' => 'post:distributor_mapping', 'uses' => 'AdminController@postDistributorMapping']);
    
    Route::get('mapping/retailer-mapping', ['as' => 'get:retailer_mapping', 'uses' => 'AdminController@getRetailerMapping']);
    Route::post('mapping/retailer-mapping', ['as' => 'post:retailer_mapping', 'uses' => 'AdminController@postRetailerMapping']);
    
    Route::get('retailers/manage-services', ['as' => 'get:retailers_manage_services', 'uses' => 'AdminController@getRetailersManageServices']);
    
    Route::get('reports/all-success-transaction-report', ['as' => 'get:all_success_transaction_report', 'uses' => 'ReportsController@getAllSuccessTransactionReport']);
    
    Route::get('reports/package-activation-report', ['as' => 'get:package_activation_report', 'uses' => 'ReportsController@getPackageActivationReport']);
    
    // --- NEW CODE ISHWAR
    Route::get('settings/app-settings', ['as' => 'get:app_settings', 'uses' => 'AdminController@getAppSettings']);
    Route::post('settings/app-settings', ['as' => 'post:app_settings', 'uses' => 'AdminController@postAppSettings']);
    
    Route::get('commissions/manage-commissions', ['as' => 'get:manage_commissions', 'uses' => 'AdminController@getManageCommission']);
    Route::post('commissions/update-commissions', ['as' => 'post:update_commissions', 'uses' => 'AdminController@postUpdateCommission']);
    
    Route::get('commissions/manage-dmt-commissions', ['as' => 'get:manage_dmt_commissions', 'uses' => 'AdminController@getManageDmtCommission']);
    Route::post('commissions/add-dmt-commission', ['as' => 'post:add_dmt_commission', 'uses' => 'AdminController@postAddDmtCommission']);
    
    Route::get('commissions/manage-aeps-commissions', ['as' => 'get:manage_aeps_commissions', 'uses' => 'AdminController@getManageAepsCommission']);
    Route::post('commissions/add-aeps-commission', ['as' => 'post:add_aeps_commission', 'uses' => 'AdminController@postAddAepsCommission']);
    
    Route::post('delete_operator', ['as' => 'post:delete_operator', 'uses' => 'AdminController@deleteOperator']);
    
    Route::get('notifications/send-notification', ['as' => 'get:send_notification', 'uses' => 'AdminController@getSendNotification']);
    Route::post('notifications/send-notification', ['as' => 'post:send_notification', 'uses' => 'AdminController@postSendNotification']);
    
    Route::get('settings/add-app-banners', ['as' => 'get:add_app_banners', 'uses' => 'AdminController@getAddAppBanners']);
    Route::post('settings/add-app-banners', ['as' => 'post:add_app_banners', 'uses' => 'AdminController@postAddAppBanners']);
    Route::get('settings/manage-app-banners', ['as' => 'get:manage_app_banners', 'uses' => 'AdminController@manageAppBanners']);
    Route::post('settings/delete-banner', ['as' => 'post:delete_banner', 'uses' => 'AdminController@postDeleteBanner']);
    
    Route::get('reports/manage-pending-transactions', ['as' => 'get:manage_pending_transactions', 'uses' => 'ReportsController@getManagePendingTransactions']);
    Route::post('report/update-txn-status', ['as' => 'post:update_txn_status', 'uses' => 'ReportsController@updateTxnStatus']);
    
});


//RETAILER-ROUTES-STARTS-----------------------------------------------------------------------------------------
Route::group(['middleware' => 'App\Http\Middleware\SuperDistributorMiddleware'], function() {
    
    Route::get('distributor/add-distributor', ['as' => 'get:sd_add_distributor', 'uses' => 'SuperDistributorController@getAddDistributor']);
    Route::post('distributor/add-distributor', ['as' => 'post:sd_add_distributor', 'uses' => 'SuperDistributorController@postAddDistributor']);
    Route::get('distributor/manage-distributors', ['as' => 'get:sd_manage_distributors', 'uses' => 'SuperDistributorController@getManageDistributors']);
    Route::get('distributor/manage-distributor-data', ['as' => 'get:sd_manage_distributors_data', 'uses' => 'SuperDistributorController@getManageDistributorsData']);
    
    Route::get('sd/distributor-money-requests', ['as' => 'get:sd_distributor_money_requests', 'uses' => 'MoneyRequestController@getSDDistributorMoneyRequests']);
    Route::get('sd/distributor-money-requests-report', ['as' => 'get:sd_distributor_money_requests_report', 'uses' => 'MoneyRequestController@getSDDistributorMoneyRequestsReport']);    
    
	
	Route::post('sd/add-money-to-user', ['as' => 'post:sd_add_money_to_user', 'uses' => 'SuperDistributorController@postSDAddMoneyToCustomer']);
	
    
});


//RETAILER-ROUTES-STARTS-----------------------------------------------------------------------------------------
Route::group(['middleware' => 'App\Http\Middleware\RetailerMiddleware'], function() {
    
    
    
    
});






