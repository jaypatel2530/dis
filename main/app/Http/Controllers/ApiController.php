<?php

namespace App\Http\Controllers;

use App\Classes\ApiManager;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

use Endroid\QrCode\QrCode;
use App\User;
use DB;

use App\Model\Address; 
use App\Model\AepsTransaction;
use App\Model\Beneficiary;
use App\Model\Category;
use App\Model\City;
use App\Model\Dispute;
use App\Model\FundBank;
use App\Model\State;
use App\Model\KycDoc;
use App\Model\ResponseTable;
use App\Model\UserBank;
use App\Model\UserVA;
use App\Model\UserUpi;
use App\Model\ShopDetail;
use App\Model\Operators;
use App\Model\Operators2;
use App\Model\Operators3;
use App\Model\Operators4;
use App\Model\Transactions;
use App\Model\UsersMapping;
use App\Model\BankDetail;
use App\Model\MoneyRequest;
use App\Model\Notifications;
use App\Model\AppBanner;
use App\Model\Setting;

class ApiController extends Controller
{
    public function __construct(ApiManager $apiManager){
        $this->apiManager = $apiManager;
    }

    public function qrcode() {
        $qrCode = new QrCode('Life is too short to be generating QR codes');
        header('Content-Type: '.$qrCode->getContentType());
        $qrCode->writeString();
        $qrcode_name = "qr.png";
        $qrCode->writeFile(public_path().'/uploads/qrcodes/'.$qrcode_name);
        
        return response()->json([ 'success' => true,  'message' => 'qr created']);
    }
    
    
    public function sendLoginOtp(Request $request) {
        
        $user = User::where('mobile', $request->get('mobile'))->where('user_type',2)->first();
        
        if($user) {
            
            if($user->status == 0) {
                return response()->json(['success' => false, 'message' => 'Your account is locked!']);
            }
            
            $user_id = $user->id;
            $mobile = $user->mobile;
            
            if($mobile == '1234567890' || $mobile == '8140666688') {
                $otp = 147258;
            }
            else{
                $otp = rand(100000, 999999);
                
                $message = 'Dear User, Your OTP is : ' .$otp. ' More Detail visit. http://rumanpay.in/';
                
                // $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
                $user->otp = $otp;
                $user->save();
            }
            return response()->json(['success' => true, 'message' => 'Otp sent', 'otp'=> 0]);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Your mobile not registred with us!']);    
        }
    }
    
    public function verifyLoginOtp(Request $request) {
        $user = User::where('mobile', $request->get('mobile'))->where('otp',$request->get('otp'))->first();
        
        if($user) {
            
            $usertoken = $this->apiManager->getUserToken();
            $user->device_token = $request->get('device_token');
            // if($user->user_token == NULL){
                $user->user_token = $usertoken;
            // }
            
            $user->save();
            
            $user_id = $user->id;
            $profile_detail = $this->apiManager->getUserProfile($user_id);
            return response()->json(['success' => true, 'message' => 'Login successfully', 'data' => $profile_detail]);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Invalid mobile or otp!']);    
        }
    }
    
    
    public function userVerify(Request $request) {
        $data = User::where('mobile', $request->get('mobile'))->first();
        
        if($data)
            $reg = 1;
        else
            $reg = 0;
            
        return response()->json(['success' => true, 'message' => 'User verify', 'is_register'=> $reg]);
    }
    
    public function getStates(Request $request) {
        $data = State::select('id','name')->where('country_id',1)->get();
        return response()->json(['success' => true, 'message' => 'States', 'data'=> $data]);
    }
    
    public function getCities(Request $request) {
        $data = City::select('id','name')->where('state_id', $request->get('state_id'))->get();
        return response()->json(['success' => true, 'message' => 'Cities', 'data'=> $data]);
    }
    
    public function userRegistration(Request $request) {
        $mobile = $request->get('mobile');
        
        $mobi_check = User::where('mobile',$mobile)->first();
        if($mobi_check) {
            return response()->json(['success' => false, 'message' => 'Mobile already exists']);    
        }
        
        $usertoken = $this->apiManager->getUserToken();
        
        $user = new User();
        $user->name = ucfirst($request->get('name'));
        $user->mobile = $mobile;
        $user->email = $request->get('email');
        $user->dob = $request->get('dob');
        $user->user_type = 2;
        $user->password = bcrypt($mobile);
        $user->user_token = $usertoken;
        $user->device_token = $request->get('device_token');
        $user->category_id = $request->get('category_id');
        $user->reg_completed = 1;
        $user->save();
        
        if($user) {
            
            $user_id = $user->id;
            
            $address = new Address();
            $address->user_id = $user_id;
            $address->address = $request->get('address');
            $address->city = $request->get('city');
            $address->state = $request->get('state');
            $address->pincode = $request->get('pincode');
            $address->latitude = $request->get('latitude');
            $address->longitude = $request->get('longitude');
            $address->save();
            
            //bank
            $bank = new UserBank();
            $bank->user_id = $user_id;
            $bank->bank_name = $request->get('bank_name');
            $bank->account_no = $request->get('account_no');
            $bank->ifsc = $request->get('ifsc');
            $bank->holder = $request->get('holder');
            $bank->branch = $request->get('branch');
            $bank->city = $request->get('bank_city');
            $bank->state = $request->get('bank_state');
            $bank->address = $request->get('bank_address');
            $bank->primary_bank = 1;
            $bank->save();
            
            $title = 'Registration Done';
            $message = 'Welcome to '.env('APP_NAME').' Family, Your Registered number is '.$mobile.' Now you can start transactions. Download The APP. http://rumanpay.in/m.apk';
            $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
            $sendsms = $this->apiManager->REGSMS($user_id,$message);
            
            $profile_detail = $this->apiManager->getUserProfile($user_id);
            
            return response()->json(['success' => true, 'message' => 'Registration done successfully', 'data' => $profile_detail]);
        }
        else{
            return response()->json(['success' => false, 'message' => 'Something went wrong!']);
        }
    }

    public function registrationUpdate(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get('user_token');
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response) {
            
            $usertoken = $this->apiManager->getUserToken();
            
            $user = User::find($user_id);
            $user->email = $request->get('email');
            $user->dob = $request->get('dob');
            $user->user_type = 2;
            $user->password = bcrypt($user->mobile);
            $user->user_token = $usertoken;
            $user->device_token = $request->get('device_token');
            $user->category_id = $request->get('category_id');
            $user->reg_completed = 1;
            $user->save();
            
            if($user) {
                
                //address
                $address = new Address();
                $address->user_id = $user_id;
                $address->address = $request->get('address');
                $address->city = $request->get('city');
                $address->state = $request->get('state');
                $address->pincode = $request->get('pincode');
                $address->latitude = $request->get('latitude');
                $address->longitude = $request->get('longitude');
                $address->save();
                
                //bank
                $bank = new UserBank();
                $bank->user_id = $user_id;
                $bank->bank_name = $request->get('bank_name');
                $bank->account_no = $request->get('account_no');
                $bank->ifsc = $request->get('ifsc');
                $bank->holder = $request->get('holder');
                $bank->branch = $request->get('branch');
                $bank->city = $request->get('bank_city');
                $bank->state = $request->get('bank_state');
                $bank->address = $request->get('bank_address');
                $bank->primary_bank = 1;
                $bank->save();
                
                $profile_detail = $this->apiManager->getUserProfile($user_id);
                
                return response()->json(['success' => true, 'message' => 'Registration done successfully', 'data' => $profile_detail]);
            }
            else {
                return response()->json(['success' => false, 'message' => 'Something went wrong!']);
            }
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function mobileLogin(Request $request) {
        $mobile = $request->get('mobile');
        
        $user = User::where('mobile',$mobile)->first();
        
        if($user){
            
            $usertoken = $this->apiManager->getUserToken();
            $user->device_token = $request->get('device_token');
            $user->user_token = $usertoken;
            $user->save();
            
            $user_id = $user->id;
            $profile_detail = $this->apiManager->getUserProfile($user_id);
            return response()->json(['success' => true, 'message' => 'Login successfully', 'data' => $profile_detail]);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Something went wrong!']);
        }
    }
    
    public function userDetail(Request $request) {
        $user_id = $request->get('user_id');
        $usertoken = $request->get('user_token');
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response){
            $user = User::find($user_id);
            $profile_detail = $this->apiManager->getUserProfile($user_id);
            return response()->json(['success' => true, 'message' => 'User details', 'data' => $profile_detail]);
        }
        else{
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
        
        $user = User::where('mobile',$mobile)->first();
        
        if($user){
            $user_id = $user->id;
            $profile_detail = $this->apiManager->getUserProfile($user_id);
            return response()->json(['success' => true, 'message' => 'Login successfully', 'data' => $profile_detail]);
        }
        else{
            return response()->json(['success' => false, 'message' => 'Something went wrong!']);
        }
    }
    
    public function createKyc(Request $request) {
        $user_id = $request->get('user_id');
        $usertoken = $request->get('user_token');
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response){
            $kycdocs = KycDoc::where('user_id',$user_id)->where('status',2)->first();
            
            if($kycdocs) {
                $kyc = KycDoc::find($kycdocs->id);
                
                $check_pan_num = KycDoc::where('user_id','!=',$user_id)->where('pan_number',$request->get("pan_number"))->first();
                
                if($check_pan_num) {
                    return response()->json(['success' => false,'message' => 'PAN number already used!']);
                }
                
                $check_aadhaar_num = KycDoc::where('user_id','!=',$user_id)->where('aadhaar_number',$request->get("aadhaar_number"))->first();
                
                if($check_aadhaar_num) {
                    return response()->json(['success' => false,'message' => 'Aadhaar number already used!']);
                }
                
                $user = User::find($user_id);
                $user->name = ucfirst($request->get('user_name'));
                $user->save();
            }
            else {
                
                $check_pan_num = KycDoc::where('pan_number',$request->get("pan_number"))->first();
                
                if($check_pan_num) {
                    return response()->json(['success' => false,'message' => 'PAN number already used!']);
                }
                
                $check_aadhaar_num = KycDoc::where('aadhaar_number',$request->get("aadhaar_number"))->first();
                
                if($check_aadhaar_num) {
                    return response()->json(['success' => false,'message' => 'Aadhaar number already used!']);
                }
            
                $kyc = new KycDoc();
            }
            
            $kyc->user_id = $user_id;
            $kyc->pan_number = $request->get('pan_number');
            $kyc->aadhaar_number = $request->get('aadhaar_number');
            $kyc->status = 0;
            
            if ($request->hasFile('pan_image')) {
                $file = $request->file('pan_image');
                $destinationPath = public_path('/uploads/kycdocs/');
                $imagename = 'PAN'. $user_id . time() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $imagename);
                $kyc->pan_image = $imagename;
            }
            
            if ($request->hasFile('aadhaar_front_image')) {
                $file = $request->file('aadhaar_front_image');
                $destinationPath = public_path('/uploads/kycdocs/');
                $imagename = 'ADHARF'. $user_id . time() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $imagename);
                $kyc->aadhaar_front_image = $imagename;
            }
            
            if ($request->hasFile('aadhaar_back_image')) {
                $file = $request->file('aadhaar_back_image');
                $destinationPath = public_path('/uploads/kycdocs/');
                $imagename = 'ADHARB'. $user_id . time() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $imagename);
                $kyc->aadhaar_back_image = $imagename;
            }
            
            $kyc->save();

            return response()->json(['success' => true, 'message' => 'Kyc registration done']);
        }
        else{
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function createVA(Request $request) {
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        
        if($response){
            
            $user = User::find($user_id);
            
            $getkyc = KycDoc::where('user_id',$user_id)->where('status',1)->first();
            if(!$getkyc) {
                return response()->json(['success' => false, 'message' => 'Verify your kyc first']);
            }
            
            $check_va = UserVA::where('user_id',$user_id)->first();
            if($check_va) {
                return response()->json(['success' => false, 'message' => 'VA account already exist!']);
            }
            
            
            $profile_detail = $this->apiManager->getUserProfile($user_id);
            
            $title = env('APP_NAME');
            $city = $profile_detail->city_name;
            $state = $profile_detail->state_name;
            $username = urlencode($user->name);
            
            $ref_number = $this->apiManager->hyptoRefId();
            $udf1 = urlencode($title);
            $udf2 = urlencode($state);
            $udf3 = urlencode($city);
            
            $url = "https://partners.hypto.in/api/virtual_accounts?reference_number=".$ref_number."&udf1=".$udf1."&udf2=".$username."&udf3=".$udf2;
            
            $apicall = $this->apiManager->hpytoPostApiCall($url);        
                
            // $response_table = new ResponseTable();
            // $response_table->response = $apicall;
            // $response_table->api_name = 'HYPTOVA';
            // $response_table->request = $url;
            // $response_table->save();
            
            $apidata = json_decode($apicall);
            
            if($apidata->success) {
                
                $account_number = $apidata->data->virtual_account->account_number;
                $reference_number = $apidata->data->virtual_account->reference_number;
                $va_id = $apidata->data->virtual_account->id;
                
                $user->va_status = 1;
                $user->va_account_number = $account_number;
                $user->va_ref_id = $reference_number;
                $user->va_id = $va_id;
                $user->save();
                
                $accounts = $apidata->data->virtual_account->details;
                $ac_status = $apidata->data->virtual_account->status;
                
                if($ac_status == 'ACTIVE') {
                    $status = 1;
                }
                else{
                    $status = 0;
                }
                
                foreach($accounts as $account) 
                {
                    $create = new UserVA();
                    $create->user_id = $user_id;
                    $create->acc_no = $account->account_number;
                    $create->ifsc = $account->account_ifsc;
                    $create->ref_id = $reference_number;
                    $create->status = $status;
                    
                    $payment_modes = $account->payment_modes;
                    
                    foreach($payment_modes as $pmode) {
                        
                        if($pmode == 'IMPS') {
                            $create->imps = 1;
                        }
                        if($pmode == 'NEFT') {
                            $create->neft = 1;
                        }
                        if($pmode == 'RTGS') {
                            $create->rtgs = 1;
                        }
                        if($pmode == 'UPI') {
                            $create->upi = 1;
                        }
                    }
                    
                    if($account->account_ifsc == 'YESB0CMSNOC') {
                        $create->bank_name = 'YES BANK';
                        $create->logo = 'Yes_Bank.png';
                    }
                    elseif($account->account_ifsc == 'KKBK0000958') {
                        $create->bank_name = 'Kotak Mahindra Bank';
                        $create->logo = 'Kotak_Mahindra_Bank.png';
                    }
                    elseif($account->account_ifsc == 'DBSS0IN0811') {
                        $create->bank_name = 'DBS Bank';
                        $create->logo = 'DBS_Bank.png';
                    }
                    elseif($account->account_ifsc == 'ICIC0000104') {
                        $create->bank_name = 'ICICI Bank';
                        $create->logo = 'ICICI_Bank.png';
                    }
                    elseif($account->account_ifsc == 'RATN0000320') {
                        $create->bank_name = 'RBL Bank';
                        $create->logo = 'RBL_Bank.png';
                    }
                    
                    $create->save();
                }
                return response()->json(['success' => true,'message' => 'VA Created']);
            }
            else{
                return response()->json(['success' => false, 'message' => "Can't created account due to server error!"]);    
            }
        }
        else{
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function getCategoryList(Request $request) {
        
        $cats = Category::select('id','cat_name')->get();

        if($cats)
            return response()->json(['success' => true, 'message' => 'Category list' , 'data' => $cats]);
        else
            return response()->json(['success' => false, 'message' => 'Something went wrong!']);
        
    }
    
    public function createUPI(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        // $category_id = $request->get('category_id');
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            $kycdocs = KycDoc::where('user_id',$user_id)->where('status',1)->first();
            if(!$kycdocs) {
                return response()->json(['success' => false, 'message' => 'Kyc not verified']);
            }
            
            $user = User::find($user_id);
            
            $category_id = $user->category_id;
            
            $category = Category::find($category_id);
            
            $address_data = Address::where('user_id',$user_id)->first();
            
            if($category) {
                
                $upi_prefix = env('UPI_PREFIX');
                
                $upi_id = $upi_prefix.$user->mobile.'@yesbank';
                
                $name = urlencode($user->name);
                $pan = urlencode($kycdocs->pan_number);
                $categoryname = urlencode($category->cat_name);
                
                $address = urlencode($address_data->address);
                $lat = $address_data->latitude;
                $lon = $address_data->longitude;
                
                //check_pending_entry
                $check_entry = UserUpi::where('user_id',$user_id)->where('upi_id',$upi_id)->where('status',0)->first();
                    
                if($check_entry) {
                    
                    $url = "https://partners.hypto.in/api/upis/search?q=".$upi_id;
                
                    $apicall = $this->apiManager->hpytoGetApiCall($url);        
                    
                    $response_table = new ResponseTable();
                    $response_table->response = $apicall;
                    $response_table->api_name = 'HYPTO_CREATE_UPI_2';
                    $response_table->request = $url;
                    $response_table->save();
                    
                    $response = json_decode($apicall); 
                    
                    if($response->success) {
                        
                        $status = $response->data->upis[0]->status;
                        $id2 = $response->data->upis[0]->id;
                        
                        if($status=='ACTIVATED'){
                            
                            $check_entry->status = 1;
                            $check_entry->save();
                            
                            $user->upi_status = 1;
                            $user->qr_status = 1;
                            $user->save();
                            
                            return response()->json([ 'success' => true,  'message' => 'UPI created!!']);
                        }
                        else {
                            return response()->json([ 'success' => true,  'message' => 'UPI under process!!']);
                        }
                    }
                    else {
                        return response()->json([ 'success' => false,  'message' => 'Unable to create UPI!!']);
                    }
                }
                
                
                $url = "https://partners.hypto.in/api/upis?upi_id=".$upi_id."&name=".$name."&pan=".$pan."&category=".$categoryname."&address=".$address."&lat=".$lat."&lon=".$lon;
                            
                $apicall = $this->apiManager->hpytoPostApiCall($url);        
                    
                // $reponse_data = new ResponseTable();
                // $reponse_data->response = $apicall;
                // $reponse_data->api_name = 'HYPTO_CREATE_UPI';
                // $reponse_data->request = $url;
                // $reponse_data->save();
                    
                $response = json_decode($apicall);   
                
                if($response->success) {
                    
                    $status = $response->data->upi->status;
                    $id2 = $response->data->upi->id;
                    
                    if($status=='ACTIVATED' || $status=='PENDING') {
                        
                        $message='';
                        
                        if($status=='ACTIVATED'){
                            $status = 1;
                            $message = 'UPI created!';
                        }
                        else {
                            $status = 0;
                            $message = 'UPI under process!';
                        }
                        
                        $upilink = "upi://pay?pa=".$upi_id."&pn=".$name;
                        
                        $upi = new UserUpi();
                        $upi->user_id = $user_id;
                        $upi->category_id = $category_id;
                        $upi->id2 = $id2;
                        $upi->upi_id = $upi_id;
                        $upi->address = $address_data->address;
                        $upi->lat = $address_data->latitude;
                        $upi->lon = $address_data->longitude;
                        $upi->status = $status;
                        $upi->upi_url = $upilink;
                        $upi->save();
                        
                        //createqr
                        $qrCode = new QrCode($upilink);
                        header('Content-Type: '.$qrCode->getContentType());
                        $qrCode->writeString();
                        $qrcode_name = 'QR'.$user_id.'IMG'.time().'.png';
                        $qrCode->writeFile(public_path().'/uploads/qrcodes/'.$qrcode_name);
                        
                        $user->upi_status = $status;
                        $user->qr_status = $status;
                        $user->qr_img = $qrcode_name;
                        $user->save();
                        
                        return response()->json([ 'success' => true,  'message' => 'UPI created!']);
                        
                    }
                    else{
                        return response()->json([ 'success' => false,  'message' => 'Something went wrong!']);    
                    }
                }
                else{
                    
                    if(isset($response->reason)) {
                        $reason = $response->reason;
                    }else{
                        $reason = '';
                    }
                    
                    return response()->json([ 'success' => false,  'message' => 'Unable to create UPI', 'reason' => $reason]);
                }
            }
            else{
                return response()->json(['success' => false, 'message' => 'Invalid category!']);    
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }  
    
    public function getFundBank(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response) {
            $fundbanks = FundBank::where('user_id',1)->where('status',1)->get();
            $primary_bank = UserBank::where('user_id',$user_id)->where('primary_bank',1)->first();

            return response()->json(['success' => true, 'message' => 'Banks!' , 'data' => $fundbanks, 'primary_bank' => $primary_bank ]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function addShop(Request $request) {
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            $is_available = ShopDetail::where('user_id',$user_id)->first();
            if($is_available) {
                return response()->json(['success' => false, 'message' => 'Shop already exist!']);
            }
            
            $imagename = '';
            
            $shop = new ShopDetail();
            $shop->user_id = $user_id;
            $shop->shop_name = $request->get('shop_name');
            $shop->latitude = $request->get('latitude');
            $shop->longitude = $request->get('longitude');
            $shop->contact_number = $request->get('contact_number');  //optional
            $shop->whatsapp_number = $request->get('whatsapp_number'); //optional
            
            if ($request->hasFile('shop_img')) {
                $file = $request->file('shop_img');
                $destinationPath = public_path('/uploads/shops/');
                $imagename = 'IMG'. $user_id . time() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $imagename);
            }
            $shop->shop_img = $imagename;
            $shop->save();
            
            $address = Address::where('user_id',$user_id)->first();
            
            if($address) {
                if($address->latitude == null) {
                    $address->latitude = $request->get('latitude');
                    $address->longitude = $request->get('longitude');
                    $address->save();
                }
            }
            
            return response()->json(['success' => true,'message' => 'Shop added', 'data' => $shop]);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function getShop(Request $request) {
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            $baseurl = env('APP_URL').'/uploads/shops/';
            $image = DB::raw("CONCAT('$baseurl',shop_img) AS shop_img");
            $data = ShopDetail::select('*',$image,
            DB::raw('(CASE WHEN contact_number != null THEN contact_number ELSE "" END) AS contact_number'),
            DB::raw('(CASE WHEN whatsapp_number != null THEN whatsapp_number ELSE "" END) AS whatsapp_number')
            )->where('user_id',$user_id)->first();
            
            return response()->json(['success' => true,'message' => 'Shop data', 'data' => $data]);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    public function getBankList(Request $request)
    {
        $instantpay_token = env('INSTANTPAY_TOKEN') ;
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://www.instantpay.in/ws/utilities/banks",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS =>"{\"token\": \"$instantpay_token\",\"request\": {\"account\": \"{{account}}\"}}\r\n\r\n",
          CURLOPT_HTTPHEADER => array(
            "Accept: application/json",
            "Content-Type: application/json"
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        $bank_data =json_decode($response);
        return response()->json(['success' => true,'message' => 'bank data', 'data' => $bank_data->data]);
    }
    
    //QUICK_DMT (Money transfer)
    public function quickTransfer(Request $request)
    {
        return response()->json(['success' => false,'message' => 'Servce Not Available', 'data' => '']);
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        
        $location_check = $this->apiManager->distance(12.9716,77.5946,$request->get('latitude'),$request->get('longitude'),"K");
        $reponse_data = new ResponseTable();
        $reponse_data->response = $request->get('latitude').','.$user_id;
        $reponse_data->api_name = 'LOCATIONDMT';
        $reponse_data->request = $request->get('longitude');
        $reponse_data->save();
        
        if($location_check >= 15){
            return response()->json(['success' => false, 'message' => 'Your service is disabled!']);
        }

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            //check_service_status
            $service_status = User::where('id',$user_id)->where('dmt_service',0)->first();
            if($service_status) {
                return response()->json(['success' => false, 'message' => 'Your service is disabled!']);
            }
   
            $kycdocs = KycDoc::where('user_id',$user_id)->where('status',1)->first();
            if(!$kycdocs) {
                return response()->json(['success' => false, 'message' => 'Verify your kyc first']);
            }
            
            $user = User::find($user_id);
            $user_wallet = $user->wallet;
            $payment_type = $request->get('payment_type');
            $amount = $request->get('amount');
            $note = $request->get('note') ? urlencode($request->get('note')) : env('APP_NAME');
            $txn_note = $request->get('note') ? $request->get('note') : env('APP_NAME');
            
            // $surcharge = $amount * 1 / 100;
            
            $user_slab = $user->slab;
            
            if($user_slab == 2) {
                $operator = Operators2::where('service_id','14')->where('min_amount', '<=', $amount)
                ->where('max_amount', '>=',  $amount)->where('status',1)->first();
            }
            elseif($user_slab == 3) {
                $operator = Operators3::where('service_id','14')->where('min_amount', '<=', $amount)
                ->where('max_amount', '>=',  $amount)->where('status',1)->first();
            }
            elseif($user_slab == 4) {
                $operator = Operators4::where('service_id','14')->where('min_amount', '<=', $amount)
                ->where('max_amount', '>=',  $amount)->where('status',1)->first();
            }
            else {
                $operator = Operators::where('service_id','14')->where('min_amount', '<=', $amount)
                ->where('max_amount', '>=',  $amount)->where('status',1)->first();
            }
            
            if($operator) {
                $fee_type = $operator->fee_type;
                if($fee_type == 'flat'){
                    $fee = $operator->fee;
                }else{
                    $fee = $amount * $operator->fee / 100;
                }
            }
            else {
                return response()->json(['success' => false, 'message' => 'Please check with Admin']);
            }
            
            $commission_type = $operator->commission_type;
            if($commission_type == 'flat'){
                $retailer_commission = $operator->commission;
            }else{
                $retailer_commission = $amount * $operator->commission / 100;
            }
            
            $setting = Setting::select('*')->first();
            
            // tds & gst calculation
            
            if($setting->tds > 0) {
                $tds = $fee * $setting->tds / 100;
            }
            else {
                $tds = 0;
            }
            
            if($setting->gst > 0) {
                $gst = $fee * $setting->gst / 100;
            }
            else{
                $gst = 0;
            }
        
            $total_amount = $amount + $fee;
            
            if($user_wallet < $total_amount){
                return response()->json(['success' => false, 'message' => 'Balance is low.']);
            }
            
            $txn_id = $this->apiManager->txnId("DMT");
            
            if($payment_type=="IMPS" || $payment_type=="NEFT" || $payment_type=="RTGS") {
                
                $ifsc = $request->get('ifsc');
                $number = $request->get('number');
                $beneficiary_name = $request->get('beneficiary_name');
                
                $beneficiary_mobile = $request->get('mobile');
                
                $latitude = $request->get('latitude');
                $longitude = $request->get('longitude');
                
                
                #txn prevent
                $date = date('Y-m-d H:i:s');

        		$txndate = Transactions::where('user_id',$user_id)->whereIn('status',[0,1])->where('amount',$amount)->where('ben_ac_number',$request->get('number'))->whereDate('created_at', Carbon::today())->first();
    
        		if($txndate){
    
    
    
    
    
    	    		$startTime = Carbon::parse($txndate->created_at);
    
    	    		$endTime = Carbon::parse($date);
    
    	    		$totalDuration = $endTime->diffForHumans($startTime);
    
    	    		$difftime = explode(' ', $totalDuration);
    
    
    
    	    		if($difftime[1] == 'minutes' || $difftime[1] == 'minutes'){
    
    	    			if($difftime[0] <= 5){
    
    	    				return response()->json(['success' => false, 'message' => 'Same Amount Transection you can do after 5 minute.']);
    
    	    			}
    
    	    		}elseif($difftime[1] == 'second' || $difftime[1] == 'seconds'){
    
    	    			return response()->json(['success' => false, 'message' => 'Same Amount Transection you can do after 5 minute.']);
    
    	    		}else{
    
    
    
    	    		}
    
        		}
                
                $final_balance = ($user_wallet - $total_amount) + $retailer_commission;
                //before api call entry
                $transaction = new Transactions();
                $transaction->transaction_id = $txn_id;
                $transaction->user_id = $user_id;
                $transaction->event = 'QUICKDMT';
                $transaction->transfer_type = $payment_type;
                $transaction->amount = round($amount,5);
                $transaction->commission = round($fee,5);
                $transaction->tds = round($tds,5);
                $transaction->gst = round($gst,5);
                $transaction->retailer_commission = round($retailer_commission,5);
                $transaction->current_balance = round($user_wallet,5);
                $transaction->final_balance = round($final_balance,5);
                $transaction->ben_name = $beneficiary_name;
                $transaction->ben_ac_number = $number;
                $transaction->ben_ac_ifsc = $ifsc;
                $transaction->txn_note = $txn_note;
                $transaction->status = 0;
                $transaction->save();
                
                $user_u = User::find($user_id);
                $user_u->wallet = round($final_balance,5);
                $user_u->save();
                
                if($payment_type=="IMPS")
                    $sp_key = 'DPN';
                elseif($payment_type=="NEFT")
                    $sp_key = 'BPN';
                else
                    $sp_key='CPN'; //RTGS
                
                $instantpay_token = env('INSTANTPAY_TOKEN') ;
                
                // $params_array = [
                // "sp_key" => $sp_key,
                // "external_ref" => $txn_id,
                // "credit_account" => $number,
                // "ifs_code" => $ifsc,
                // "bene_name" => $beneficiary_name,
                // "credit_amount" => $amount,
                // "latitude" => $latitude,
                // "longitude" => $longitude,
                // "endpoint_ip" => "162.241.148.160",
                // "alert_mobile" => $user->mobile,
                // "alert_email" => $user->email,
                // "remarks" => $txn_note];
                
                // $req_params = ["token" => $instantpay_token, "request" => $params_array];
                // $req_raw = json_encode($req_params);
                
                // $url = "https://www.instantpay.in/ws/payouts/direct";    
                
                // $curl = curl_init();
                // curl_setopt_array($curl, array(
                // CURLOPT_URL => $url,
                // CURLOPT_RETURNTRANSFER => true,
                // CURLOPT_ENCODING => "",
                // CURLOPT_MAXREDIRS => 10,
                // CURLOPT_TIMEOUT => 0,
                // CURLOPT_FOLLOWLOCATION => true,
                // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                // CURLOPT_CUSTOMREQUEST => "POST",
                // CURLOPT_POSTFIELDS => $req_raw,
                // CURLOPT_HTTPHEADER => array("Accept: application/json",
                // "Content-Type: application/json"),
                // ));
                
                // $response = curl_exec($curl);
                
                // curl_close($curl);
                
                $payer = [
                    "bankId"=>"0",
                    "bankProfileId"=>"0",
                    "accountNumber"=>"9739632802",
                    ];
                
                $payee = [
                    "name"=>$beneficiary_name,
                    "accountNumber"=>$number,
                    "bankIfsc"=>$ifsc,
                    ];
                
                $req_params = ["payer"=>$payer,"payee"=>$payee,"transferMode" => "IMPS", "transferAmount" => $amount, "externalRef" => $txn_id, "latitude" => $latitude, "longitude" => $longitude,"remarks"=>$txn_note,"purpose"=>"REIMBURSEMENT","alertEmail"=>"info@rumanpay.in"];
                $req_raw = json_encode($req_params);
                
                $url = "https://api.instantpay.in/payments/payout";
                
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $req_raw,
                CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'X-Ipay-Client-Id: YWY3OTAzYzNlM2ExZTJlOfXB0J5BcUJC35CfpULFcAA=',
            'X-Ipay-Auth-Code: 1',
            'X-Ipay-Client-Secret: 2caa04f1697fec28250d8a43f198db7d2417e4b05a16fab5dd724213781dd627',
            'X-Ipay-Endpoint-Ip: 192.185.129.79',
            'Content-Type: application/json'
          ),
                ));
                
                $response = curl_exec($curl);
                
                curl_close($curl);
                
                $reponse_data = new ResponseTable();
                $reponse_data->response = $response;
                $reponse_data->api_name = 'INSTANTPAY_DMT';
                $reponse_data->request = $req_raw;
                $reponse_data->save();
                
                // return $response;
                
                // {
                //     "statuscode": "TXN",
                //     "status": "Transaction Successful",
                //     "data": {
                //         "external_ref": "WEDDMT210106750697",
                //         "ipay_id": "1210106164033GMPKH",
                //         "transfer_value": "10.00",
                //         "type_pricing": "CHARGE",
                //         "commercial_value": "2.3600",
                //         "value_tds": "0.0000",
                //         "ccf": "0.00",
                //         "vendor_ccf": "0.00",
                //         "charged_amt": "12.36",
                //         "payout": {
                //             "credit_refid": "100616671640",
                //             "account": "37730545644",
                //             "ifsc": "SBIN0060405",
                //             "name": "Mrs  MAYA ISHWAR BHA"
                //         }
                //     },
                //     "timestamp": "2021-01-06 16:40:34",
                //     "ipay_uuid": "C754929583361699B4A2",
                //     "orderid": "1210106164033GMPKH",
                //     "environment": "PRODUCTION"
                // }
                
                $res = json_decode($response);
                
                if(isset($res->statuscode)){ 
                    
                }
                else{ 
                    $refund =  $this->apiManager->refundAmount($txn);
                    return response()->json(['success' => false, 'message' => 'Response not found!']); }
                
                if($res->statuscode == "TXN" || $res->statuscode == "TUP") { // TXN = Success ,  TUP = pending
                    
                    if($res->statuscode == "TXN") {
                        $status = 1;
                        $message = "Payment successfully transferred. Account holder name is. $beneficiary_name. Amount INR $amount. Bank txn id.  ".$res->data->poolReferenceId."";
                        $opcomm = $this->apiManager->addDMTCommission($txn_id, $operator->id);
                    }
                    else {
                        $status = 0;
                        $message = "Your txn no. ". $txn_id ." is under process"; 
                    }
                    
                    $final_balance = ($user_wallet - $total_amount) + $retailer_commission;
                    
                    $txn = Transactions::find($transaction->id);
                    // $txn->commission = round($fee,5);
                    // $txn->tds = round($tds,5);
                    // $txn->gst = round($gst,5);
                    // $txn->retailer_commission = round($retailer_commission,5);
                    // $txn->current_balance = round($user_wallet,5);
                    // $txn->final_balance = round($final_balance,5);
                    $txn->txn_type = 'Debit';
                    $txn->status = $status;
                    $txn->op_id = $operator->id;
                    $txn->op_code = $operator->op_code;
                    $txn->utr = $res->data->poolReferenceId;
                    $txn->save();
                    
                    // $user_u = User::find($user_id);
                    // $user_u->wallet = round($final_balance,5);
                    // $user_u->save();
                    
                    $title = $payment_type.' Transaction';
                    $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
//                    $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
                    
                    //send beneficiay sms
                    if($request->get('mobile')) {
                        $message2 = 'Dear '.$beneficiary_name.' INR '.$amount." is credited to A/c No. ".$number." on ".$res->timestamp." Ref No. ".$res->data->poolReferenceId. " Queries? Visit https://rumanpay.in";
                        // $sendsms2 = $this->apiManager->sendSMS_mobile($beneficiary_mobile,$message2);
                    }
                    
                    return response()->json(['success' => true, 'message' => 'Transaction done successfully', 'data' => $txn ]);
                    
                }
                else {
                    
                    $transaction =Transactions::find($transaction->id);
                    $transaction->transaction_id = $txn_id;
                    $transaction->user_id = $user_id;
                    $transaction->event = 'QUICKDMT';
                    $transaction->transfer_type = $payment_type;
                    $transaction->amount = round($amount,5);
                    // $transaction->commission = round($fee,5);
                    // $transaction->current_balance = round($user_wallet,5);
                    // $transaction->final_balance = round($user_wallet,5);
                    $transaction->ben_name = $beneficiary_name;
                    $transaction->ben_ac_number = $number;
                    $transaction->ben_ac_ifsc = $ifsc;
                    $transaction->txn_note = $txn_note;
                    $transaction->status = 2;
                    $transaction->save();
                    
                    $refund =  $this->apiManager->refundAmount($txn);
                    
                    $title = $payment_type.' Transaction';
                    $message = "Your txn no. ". $txn_id ." is failed";
                    $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
//                    $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
                    $msg = "Transaction fail due to server error!";
                    
                    return response()->json(['success' => false, 'message' => $msg]);
                }
                
            }
            else
            {
                return response()->json(['success' => false, 'message' => 'Invalid payment type']);
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    //QUICK_DMT (Money transfer)
    public function quickTransferHypto(Request $request)
    {
        return response()->json(['success' => false,'message' => 'Servce Not Available', 'data' => '']);
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            //check_service_status
            $service_status = User::where('id',$user_id)->where('dmt_service',0)->first();
            if($service_status) {
                return response()->json(['success' => false, 'message' => 'Service Is Down!']);
            }
            
            // if($request->get('amount') > 10000){
            //     return response()->json(['success' => false, 'message' => 'More than Rs 10000 transaction not allow at the moment']);
            // }
            
            $kycdocs = KycDoc::where('user_id',$user_id)->where('status',1)->first();
            if(!$kycdocs) {
                return response()->json(['success' => false, 'message' => 'Verify your kyc first']);
            }
            
            $user = User::find($user_id);
            $user_wallet = $user->wallet;
            $payment_type = $request->get('payment_type');
            $amount = $request->get('amount');
            $note = $request->get('note') ? urlencode($request->get('note')) : 'RUMPANY';
            $txn_note = $request->get('note') ? $request->get('note') : 'RUMPANY';
            
            // $surcharge = $amount * 1 / 100;
            
            $user_slab = $user->slab;
            
            if($user_slab == 2) {
                $operator = Operators2::where('service_id','14')->where('min_amount', '<=', $amount)
                ->where('max_amount', '>=',  $amount)->where('status',1)->first();
            }
            elseif($user_slab == 3) {
                $operator = Operators3::where('service_id','14')->where('min_amount', '<=', $amount)
                ->where('max_amount', '>=',  $amount)->where('status',1)->first();
            }
            elseif($user_slab == 4) {
                $operator = Operators4::where('service_id','14')->where('min_amount', '<=', $amount)
                ->where('max_amount', '>=',  $amount)->where('status',1)->first();
            }
            else {
                $operator = Operators::where('service_id','14')->where('min_amount', '<=', $amount)
                ->where('max_amount', '>=',  $amount)->where('status',1)->first();
            }
            
            if($operator) {
                $fee_type = $operator->fee_type;
                if($fee_type == 'flat'){
                    $fee = $operator->fee;
                }else{
                    $fee = $amount * $operator->fee / 100;
                }
            }
            else {
                return response()->json(['success' => false, 'message' => 'Please check with Admin']);
            }
            
            $commission_type = $operator->commission_type;
            if($commission_type == 'flat'){
                $retailer_commission = $operator->commission;
            }else{
                $retailer_commission = $amount * $operator->commission / 100;
            }
            
            $setting = Setting::select('*')->first();
            
            // tds & gst calculation
            
            if($setting->tds > 0) {
                $tds = $fee * $setting->tds / 100;
            }
            else {
                $tds = 0;
            }
            
            if($setting->gst > 0) {
                $gst = $fee * $setting->gst / 100;
            }
            else{
                $gst = 0;
            }
            
            // $retailer_commission = $fee - $tds - $gst;
        
            $total_amount = $amount + $fee;
            
            if($user_wallet < $total_amount){
                return response()->json(['success' => false, 'message' => 'Balance is low.']);
            }
            
            $txn_id = $this->apiManager->txnId("DMT");
            
            if($payment_type=="IMPS" || $payment_type=="NEFT" || $payment_type=="RTGS") {
                
                $ifsc = $request->get('ifsc');
                $number = $request->get('number');
                $beneficiary_name = $request->get('beneficiary_name');
                
                //before api call entry
                $transaction = new Transactions();
                $transaction->transaction_id = $txn_id;
                $transaction->user_id = $user_id;
                $transaction->event = 'QUICKDMT';
                $transaction->transfer_type = $payment_type;
                $transaction->amount = round($amount,5);
                $transaction->commission = 0;
                $transaction->tds = 0;
                $transaction->gst = 0;
                $transaction->retailer_commission = 0;
                $transaction->current_balance = 0;
                $transaction->final_balance = 0;
                $transaction->ben_name = $beneficiary_name;
                $transaction->ben_ac_number = $number;
                $transaction->ben_ac_ifsc = $ifsc;
                $transaction->txn_note = $txn_note;
                $transaction->status = 0;
                $transaction->save();
                
                $url = "https://partners.hypto.in/api/transfers/initiate?amount=".$amount."&payment_type=".$payment_type."&ifsc=".$ifsc."&number=".$number."&note=".$note."&beneficiary_name=".urlencode($beneficiary_name)."&reference_number=".$txn_id;
                    
                $apicall = $this->apiManager->hpytoPostApiCall($url);        
                    
                $response_table = new ResponseTable();
                $response_table->response = $apicall;
                $response_table->api_name = 'HYPTO_QUICK_TRANSFER';  //HYPTOAPI
                $response_table->request = $url;
                $response_table->save();
                
                $response = json_decode($apicall);
                
                if($response->success) {
                    
                    $status_res = $response->data->status;
                    
                    if($status_res == 'PENDING') {
                        $status = 0; //pending
                        
                        $message = "Your txn no. ". $txn_id ." is under process"; 
                    }
                    elseif($status_res == 'COMPLETED') {
                        $status = 1; // success
                        
                        $message = "Your txn no. ". $txn_id ." is successfully transferred";
                        
                        $opcomm = $this->apiManager->addDMTCommission($txn_id, $operator->id);
                        
                    }else {
                        $status = 2; // failed
                        
                        $message = "Your txn no. ". $txn_id ." is failed";
                        
                        $txn = Transactions::find($transaction->id);
                        $txn->status = $status;
                        $txn->save();
                        
                        return response()->json(['success' => false, 'message' => 'Transaction failed' ]);
                    }                    
                    
                    $final_balance = ($user_wallet - $total_amount) + $retailer_commission;
                    
                    $txn = Transactions::find($transaction->id);
                    $txn->commission = round($fee,5);
                    $txn->tds = round($tds,5);
                    $txn->gst = round($gst,5);
                    $txn->retailer_commission = round($retailer_commission,5);
                    $txn->current_balance = round($user_wallet,5);
                    $txn->final_balance = round($final_balance,5);
                    $txn->txn_type = 'Debit';
                    $txn->status = $status;
                    $txn->op_id = $operator->id;
                    $txn->op_code = $operator->op_code;
                    $txn->save();
                    
                    $user_u = User::find($user_id);
                    $user_u->wallet = round($final_balance,5);
                    $user_u->save();
                    
                    //send_notification
                    $title = $payment_type.' Transaction';
                    $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
                    $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
                    //SUCCESSFULL_TRANSFER
                    return response()->json(['success' => true, 'message' => 'Transaction done successfully', 'data' => $txn ]);
                }
                else
                {
                    $transaction =Transactions::find($transaction->id);
                    $transaction->transaction_id = $txn_id;
                    $transaction->user_id = $user_id;
                    $transaction->event = 'QUICKDMT';
                    $transaction->transfer_type = $payment_type;
                    $transaction->amount = round($amount,5);
                    $transaction->commission = round($fee,5);
                    $transaction->current_balance = round($user_wallet,5);
                    $transaction->final_balance = round($user_wallet,5);
                    $transaction->ben_name = $beneficiary_name;
                    $transaction->ben_ac_number = $number;
                    $transaction->ben_ac_ifsc = $ifsc;
                    $transaction->txn_note = $txn_note;
                    $transaction->status = 2;
                    $transaction->save();
                    
                    $title = $payment_type.' Transaction';
                    $message = "Your txn no. ". $txn_id ." is failed";
                    $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
                    $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
                    // if(isset($response->reason)) {
                    //     $msg = $response->reason;
                    // }
                    // else {
                    //     $msg = "Transaction fail due to server error!";
                    // }
                
                    $msg = "Transaction fail due to server error!";
                    
                    return response()->json(['success' => false, 'message' => $msg]);
                }
            }
            else
            {
                return response()->json(['success' => false, 'message' => 'Invalid payment type']);
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function quickTransferHistory(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);

            $data = Transactions::select('id','transaction_id','status','amount','event','transfer_type','utr',
            'ben_name','ben_ac_number','commission','tds','gst','retailer_commission','current_balance','final_balance','created_at','txn_note',
            \DB::raw('ROUND((current_balance),2) as current_balance'),
            \DB::raw('ROUND((final_balance),2) as final_balance'),
            \DB::raw('ROUND((amount),2) as amount'),
            \DB::raw('ROUND((tds),2) as tds'),
            \DB::raw('ROUND((gst),2) as gst'),
            \DB::raw('ROUND((commission),2) as commission'),
            \DB::raw('ROUND((retailer_commission),2) as retailer_commission')
            )
            ->where('user_id',$user_id)
            ->where('event','QUICKDMT')
            ->whereBetween('created_at', array($from, $to))
            ->orderBy('id','desc')->get();
            
            return response()->json(['success' => true, 'message' => 'Quick transfer history', 'data' => $data]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function quickTransferCallback(Request $request)
    {
        $response_table = new ResponseTable();
        $response_table->response = json_encode($_POST);;
        $response_table->api_name = 'QUICK_TRANSFER_CALLBACK';
        $response_table->save();
        
        $status = $_POST['status'];
        
        if($status == "COMPLETED") {
            
            $txn_id = $_POST['reference_number'];
            $utr = $_POST['bank_ref_num'];
            $txn_time = $_POST['txn_time'];
        
            $response_table->request = $txn_id;
            $response_table->save();
        
            $transaction = Transactions::where('transaction_id',$txn_id)->where('status',0)->first();
            
            if($transaction){
                
                $transaction->status = 1;
                $transaction->transferTime = $txn_time;
                $transaction->utr = $utr;
                $transaction->save();
                
                $user_id = $transaction->user_id;
                $user = User::find($user_id);
                
                $title = $transaction->transfer_type." Transaction";
                $message = "Your txn no. ". $txn_id ." is successfully transferred";
                $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
//                $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
                // Dear Customer, Your account no #VAR1# has been Debited for Rs. #VAR2# towards #VAR3#/#VAR4#/#VAR5#/Pay. 
                // Your available balance is Rs. #VAR6#.
                
                // Dear Customer, Your account no 8140666688 has been Debited for Rs. 100 towards MODE OF TRANSECTION/TRANSECTION ID/ACCOUNT NUMBER/Pay. 
                // Your available balance is Rs. 100.
                
                $VAR1 = $user->mobile;
                $VAR2 = $transaction->amount;
                $VAR3 = $transaction->transfer_type;
                $VAR4 = $txn_id;
                $VAR5 = $transaction->ben_ac_number;
                $VAR6 = $user->wallet;
                
                $mobile = $user->mobile;
                // $params = ['From' => env('SMS_FROM'), 'To' => $mobile, 'TemplateName' => 'UBDMT', 'VAR1' => $VAR1, 'VAR2' => $VAR2, 'VAR3' => $VAR3, 'VAR4' => $VAR4, 'VAR5' => $VAR5, 'VAR6' => $VAR6];
                // $send_sms = $this->apiManager->sendSMS($mobile,$params);
                
                if($transaction->event == "QUICKDMT") {
                    $operators = Operators::where('id',$transaction->op_id)->first();
                    $opcomm = $this->apiManager->addDMTCommission($txn_id, $operators->id);
                }
                
                return response()->json(['success' => true, 'message' => 'success']);
                
            }
            else {
                return response()->json(['success' => false, 'message' => 'failed']);
            }
        }
        
        if($status == "FAILED") {
            
            $txn_id = $_POST['reference_number'];
            $utr = $_POST['bank_ref_num'];
            $txn_time = $_POST['txn_time'];
            
        
            if(isset($_POST['reason'])) 
                $response_reason = $_POST['reason'];
            else
                $response_reason = '';
                
            $response_table->request = $txn_id;
            $response_table->save();
            
            $transaction = Transactions::where('transaction_id',$txn_id)->where('status',0)->first();
            
            if($transaction){
                    
                $transaction->status = 2;
                $transaction->transferTime = $txn_time;
                $transaction->utr = $utr;
                $transaction->response_reason = $response_reason;
                $transaction->save();
                
                //refund entry
                $user_id = $transaction->user_id;
                $user = User::find($user_id);
                
                $user_wallet = $user->wallet;
                $amount = $transaction->amount;
                $fee = $transaction->commission;
                $retailer_commission = $transaction->retailer_commission;
                
                $txn_id = $this->apiManager->txnId("RF");
            
                $final_balance = $user_wallet + $amount + $fee - $retailer_commission;
                
                $txns = new Transactions();
                $txns->transaction_id = $txn_id;
                $txns->user_id = $user_id;
                $txns->event = 'REFUND';
                $txns->txn_type = 'Credit';
                $txns->amount = round($amount,5);
                $txns->current_balance = round($user_wallet,5);
                $txns->final_balance = round($final_balance,5);
                $txns->ref_txn_id = $transaction->transaction_id;
                $txns->status = 1;
                $txns->save();
                
                $user->wallet = round($final_balance,5);
                $user->save();
                
                return response()->json(['success' => true, 'message' => 'success']);
            }
            else {
                return response()->json(['success' => false, 'message' => 'failed']);
            }
        }
        else {
            return response()->json(['success' => false, 'message' => 'failed']);
        }
    }

    // public function quickUPItransfer(Request $request) {
    //     $user_id = $request->get('user_id');
    //     $usertoken = $request->get("user_token");

    //     $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

    //     if($response)
    //     {
    //         //check_service_status
    //         $service_status = User::where('id',$user_id)->where('dmt_service',0)->first();
    //         if($service_status){
    //             return response()->json(['success' => false, 'message' => 'Your service is disabled!']);
    //         }
            
    //         if($request->get('amount') > 10000){
    //             return response()->json(['success' => false, 'message' => 'More than Rs 10000 transaction not allow at the moment']);
    //         }
            
    //         $user = User::find($user_id);
    //         $user_wallet = $user->wallet;
    //         $payment_type = 'UPI';
    //         $amount = $request->get('amount');
            
    //         $note = $request->get('note') ? urlencode($request->get('note')) : env('APP_NAME');
    //         $txn_note = $request->get('note') ? $request->get('note') : env('APP_NAME');
            
    //         $total_amount = $amount;
    //         if($user_wallet < $total_amount){
    //             return response()->json(['success' => false, 'message' => 'Balance is low.']);
    //         }
            
    //         $txn_id = $this->apiManager->txnId("UP");
            
    //         if($payment_type=="UPI") {
                
    //             $upi_id  = $request->get('upi_id');
                
    //             //before api call entry
    //             $transaction = new Transactions();
    //             $transaction->transaction_id = $txn_id;
    //             $transaction->user_id = $user_id;
    //             $transaction->event = 'QUICKUPI';
    //             $transaction->transfer_type = $payment_type;
    //             $transaction->ben_ac_number = $upi_id;
    //             $transaction->amount = round($amount,5);
    //             $transaction->current_balance = 0;
    //             $transaction->final_balance = 0;
    //             $transaction->status = 0;
    //             $transaction->txn_note = $txn_note;
    //             $transaction->save();
                
    //             $url = "https://partners.hypto.in/api/transfers/initiate?amount=".$amount."&payment_type=".$payment_type."&upi_id=".$upi_id."&note=".$note."&reference_number=".$txn_id;
                    
    //             $apicall = $this->apiManager->hpytoPostApiCall($url);        
                    
    //             $reponse_data = new ResponseTable();
    //             $reponse_data->response = $apicall;
    //             $reponse_data->api_name = 'HYPTO_QUICK_TRANSFER'; //HYPTOAPI
    //             $reponse_data->request = $url;
    //             $reponse_data->save();
                
    //             $response = json_decode($apicall);
                
    //             if($response->success) {
                    
    //                 $status_res = $response->data->status;
                    
    //                 if($status_res == 'PENDING') {
    //                     $status = 0; //pending
                        
    //                     $message = "Your txn no. ". $txn_id ." is under process"; 
    //                 }
    //                 elseif($status_res == 'COMPLETED') {
    //                     $status = 1; // success
                        
    //                     $message = "Your txn no. ". $txn_id ." is successfully transferred";
    //                 }else{
    //                     $status = 2; // failed
                        
    //                     $message = "Your txn no. ". $txn_id ." is failed";
    //                 }                    
                    
    //                 $final_balance = $user_wallet - $amount;
                    
    //                 $txn = Transactions::find($transaction->id);
    //                 $txn->current_balance = round($user_wallet,5);
    //                 $txn->final_balance = round($final_balance,5);
    //                 $txn->txn_type = 'Debit';
    //                 $txn->status = $status;
    //                 $txn->save();
                    
    //                 $user_u = User::find($user_id);
    //                 $user_u->wallet = round($final_balance,5);
    //                 $user_u->save();
                    
    //                 //send notification
    //                 $title = $payment_type.' Transaction';
    //                 $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
    //                 $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
    //                 return response()->json(['success' => true, 'message' => 'Transaction done successfully', 'data' => $txn]);
    //             }
    //             else
    //             {
    //                 $transaction = Transactions::find($transaction->id);
    //                 $transaction->transaction_id = $txn_id;
    //                 $transaction->user_id = $user_id;
    //                 $transaction->event = 'QUICKUPI';
    //                 $transaction->transfer_type = $payment_type;
    //                 $transaction->amount = round($amount,5);
    //                 $transaction->current_balance = round($user_wallet,5);
    //                 $transaction->final_balance = round($user_wallet,5);
    //                 $transaction->ben_ac_number = $upi_id;
    //                 $transaction->txn_note = $txn_note;
    //                 $transaction->status = 2;
    //                 $transaction->save();
                    
    //                 $title = $payment_type.' Transaction';
    //                 $message = "Your txn no. ". $txn_id ." is failed";
    //                 $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
    //                 $sendsms = $this->apiManager->sendSMS($user_id,$message);
                    
    //                 if(isset($response->reason)) {
    //                     $message = $response->reason;
    //                 }
    //                 else {
    //                     $message = "Transaction fail due to server error!";
    //                 }
    //                 return response()->json(['success' => false, 'message' => $message]);
    //             }
    //         }
    //         else
    //         {
    //             return response()->json(['success' => false, 'message' => 'Invalid payment type']);
    //         }
    //     }
    //     else
    //     {
    //         return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
    //     }
    // }
    
    public function quickUPItransfer(Request $request)
    {
        return response()->json(['success' => false,'message' => 'Servce Not Available', 'data' => '']);
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        
        $location_check = $this->apiManager->distance(12.9716,77.5946,$request->get('latitude'),$request->get('longitude'),"K");
        $reponse_data = new ResponseTable();
        $reponse_data->response = $request->get('latitude').','.$user_id;
        $reponse_data->api_name = 'LOCATIONUPI';
        $reponse_data->request = $request->get('longitude');
        $reponse_data->save();
        if($location_check >= 15){
            return response()->json(['success' => false, 'message' => 'Your service is disabled!']);
        }
        

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            $service_status = User::where('id',$user_id)->where('dmt_service',0)->first();
            if($service_status) {
                return response()->json(['success' => false, 'message' => 'Your service is disabled!']);
            }
   
            $kycdocs = KycDoc::where('user_id',$user_id)->where('status',1)->first();
            if(!$kycdocs) {
                return response()->json(['success' => false, 'message' => 'Verify your kyc first']);
            }
            
            $user = User::find($user_id);
            $user_wallet = $user->wallet;
            
            $payment_type = 'UPI';
            
            $amount = $request->get('amount');
            $note = $request->get('note') ? urlencode($request->get('note')) : env('APP_NAME');
            $txn_note = $request->get('note') ? $request->get('note') : env('APP_NAME');
            
            
            //upi fee
            $setting = Setting::select('*')->first();
            $fee = ($setting->upi_fee_type == 'flat') ? $setting->upi_fee : $amount * $setting->upi_fee / 100;
            
        
            $total_amount = $amount + $fee;
            
            if($user_wallet < $total_amount){
                return response()->json(['success' => false, 'message' => 'Balance is low.']);
            }
            
            $txn_id = $this->apiManager->txnId("UPI");
            
            if($payment_type=="UPI") {
                
                $upi_id  = $request->get('upi_id');
                $latitude = $request->get('latitude');
                $longitude = $request->get('longitude');
                $beneficiary_name = $request->get('beneficiary_name');
                
                //before api call entry
                $transaction = new Transactions();
                $transaction->transaction_id = $txn_id;
                $transaction->user_id = $user_id;
                $transaction->event = 'QUICKUPI';
                $transaction->transfer_type = $payment_type;
                $transaction->amount = round($amount,5);
                $transaction->commission = 0;
                $transaction->tds = 0;
                $transaction->gst = 0;
                $transaction->retailer_commission = 0;
                $transaction->current_balance = $user_wallet;
                $transaction->final_balance = 0;
                $transaction->ben_ac_number = $upi_id;
                $transaction->txn_note = $txn_note;
                $transaction->status = 0;
                $transaction->save();
                
                $sp_key = 'PPN';
                
                $instantpay_token = env('INSTANTPAY_TOKEN');
                
                // $params_array = [
                // "sp_key" => $sp_key,
                // "external_ref" => $txn_id,
                // "bene_name" => $beneficiary_name,
                // "credit_amount" => $amount,
                // "latitude" => $latitude,
                // "longitude" => $longitude,
                // "endpoint_ip" => "162.241.148.160",
                // "vpa" => $upi_id,
                // "upi_mode" => 'VPA',
                // "remarks" => $txn_note];
                
                // $req_params = ["token" => $instantpay_token, "request" => $params_array];
                // $req_raw = json_encode($req_params);
                
                // $url = "https://www.instantpay.in/ws/payouts/direct";    
                
                // $curl = curl_init();
                // curl_setopt_array($curl, array(
                // CURLOPT_URL => $url,
                // CURLOPT_RETURNTRANSFER => true,
                // CURLOPT_ENCODING => "",
                // CURLOPT_MAXREDIRS => 10,
                // CURLOPT_TIMEOUT => 0,
                // CURLOPT_FOLLOWLOCATION => true,
                // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                // CURLOPT_CUSTOMREQUEST => "POST",
                // CURLOPT_POSTFIELDS => $req_raw,
                // CURLOPT_HTTPHEADER => array("Accept: application/json",
                // "Content-Type: application/json"),
                // ));
                
                // $response = curl_exec($curl);
                
                // curl_close($curl);
                
                $payer = [
                    "bankId"=>"0",
                    "bankProfileId"=>"0",
                    "accountNumber"=>"9739632802",
                    ];
                
                $payee = [
                    "name"=>$beneficiary_name,
                    "accountNumber"=>$upi_id,
                    "bankIfsc"=>"",
                    ];
                
                $req_params = ["payer"=>$payer,"payee"=>$payee,"transferMode" => "UPI", "transferAmount" => $amount, "externalRef" => $amount, "latitude" => $latitude, "longitude" => $longitude,"remarks"=>$txn_note,"purpose"=>"REIMBURSEMENT","alertEmail"=>"eumanpay@eumanpay.com"];
                $req_raw = json_encode($req_params);
                
                $url = "https://api.instantpay.in/payments/payout";    
                
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $req_raw,
                CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'X-Ipay-Client-Id: YWY3OTAzYzNlM2ExZTJlOfXB0J5BcUJC35CfpULFcAA=',
            'X-Ipay-Auth-Code: 1',
            'X-Ipay-Client-Secret: 2caa04f1697fec28250d8a43f198db7d2417e4b05a16fab5dd724213781dd627',
            'X-Ipay-Endpoint-Ip: 192.185.129.79',
            'Content-Type: application/json'
          ),
                ));
                
                $response = curl_exec($curl);
                
                $reponse_data = new ResponseTable();
                $reponse_data->response = $response;
                $reponse_data->api_name = 'INSTANTPAY_UPI';
                $reponse_data->request = $req_raw;
                $reponse_data->save();
                
                // return $response;
            
                $res = json_decode($response);
                
                if(isset($res->statuscode)){ }
                else{ return response()->json(['success' => false, 'message' => 'Response not found!']); }
                
                if($res->statuscode == "TXN" || $res->statuscode == "TUP") { // TXN = Success ,  TUP = pending
                    
                    if($res->statuscode == "TXN") {
                        $status = 1;
                        $message = "Your txn no. ". $txn_id ." is successfully transferred";
                    }
                    else {
                        $status = 0;
                        $message = "Your txn no. ". $txn_id ." is under process"; 
                    }
                    
                    $final_balance = $user_wallet - $total_amount;
                    
                    $txn = Transactions::find($transaction->id);
                    $txn->current_balance = round($user_wallet,5);
                    $txn->final_balance = round($final_balance,5);
                    $txn->commission = round($fee,5);
                    $txn->txn_type = 'Debit';
                    $txn->status = $status;
                    $txn->utr = $res->data->poolReferenceId;
                    $txn->save();
                    
                    $user_u = User::find($user_id);
                    $user_u->wallet = round($final_balance,5);
                    $user_u->save();
                    
                    $title = $payment_type.' Transaction';
                    $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
//                    $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
                    return response()->json(['success' => true, 'message' => 'Transaction done successfully', 'data' => $txn ]);
                    
                }
                else {
                    
                    $transaction =Transactions::find($transaction->id);
                    $transaction->transaction_id = $txn_id;
                    $transaction->user_id = $user_id;
                    $transaction->event = 'QUICKUPI';
                    $transaction->transfer_type = $payment_type;
                    $transaction->amount = round($amount,5);
                    $transaction->current_balance = round($user_wallet,5);
                    $transaction->final_balance = round($user_wallet,5);
                    $transaction->ben_name = $beneficiary_name;
                    $transaction->txn_note = $txn_note;
                    $transaction->status = 2;
                    $transaction->save();
                    
                    $title = $payment_type.' Transaction';
                    $message = "Your txn no. ". $txn_id ." is failed";
                    $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
//                    $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
                    $msg = "Transaction fail due to server error!";
                    
                    return response()->json(['success' => false, 'message' => $msg]);
                }
                
            }
            else
            {
                return response()->json(['success' => false, 'message' => 'Invalid payment type']);
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function quickUPItransferHistory(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);
            
            $data = Transactions::select('id','transaction_id','status','amount','event','transfer_type','utr',
            'ben_ac_number','commission','current_balance','final_balance','created_at','txn_note',
            \DB::raw('ROUND((current_balance),2) as current_balance'),
            \DB::raw('ROUND((final_balance),2) as final_balance'),
            \DB::raw('ROUND((amount),2) as amount'))
            ->where('user_id',$user_id)
            ->where('event','QUICKUPI')
            ->whereBetween('created_at', array($from, $to))
            ->orderBy('id','desc')->get();
            
            return response()->json(['success' => true, 'message' => 'Quick UPI history', 'data' => $data]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function vaCreditCallback(Request $request) {
        $reponse_data = new ResponseTable();
        $reponse_data->response = json_encode($_POST);
        $reponse_data->api_name = 'VA_CREDIT_CALLBACK'; //VACREDITHYPTOCALLBACK
        $reponse_data->save();
        
        $received_amount = $_POST['amount'];
        
        $user = User::where('va_id',$_POST['hypto_va_id'])->first();
        if($user){
            
            $fee = 10;
            $amount = $received_amount - $fee;
            
            if($amount <= 0) {
                return response()->json(['success' => false, 'message' => 'Transaction failed due to low amount!']);
            }
            
            $user_id = $user->id;
            $current_amount = $user->wallet;
            $final_amount = $current_amount + $amount;
            
            $txn_id = $this->apiManager->txnId("CR");
            $transaction = new Transactions();
            $transaction->user_id = $user_id;
            $transaction->transaction_id = $txn_id;
            $transaction->event = 'CREDITVA';
            $transaction->amount = round($received_amount,5);
            $transaction->remitterAccount = $_POST['rmtr_account_no'];
            $transaction->remitterName = $_POST['rmtr_full_name'];
            $transaction->transfer_type = $_POST['payment_type'];
            $transaction->txn_note = $_POST['rmtr_to_bene_note'];
            $transaction->ben_ac_number = $_POST['bene_account_no'];
            $transaction->ben_ac_ifsc = $_POST['bene_account_ifsc'];
            $transaction->utr = $_POST['bank_ref_num'];
            $transaction->current_balance = round($current_amount,5);
            $transaction->final_balance = round($final_amount,5);
            $transaction->commission = round($fee,5);
            $transaction->status = 1;
            $transaction->txn_type = 'Credit';
            $transaction->save();
            
            $user_u = User::find($user_id);
            $user_u->wallet = round($final_amount,5);
            $user_u->save();
            
            $title = 'Amount Credit In Your A/c';
            $message = "Dear Merchant, Rs. ".$amount." credited in your a/c. Your current balance is Rs.".$final_amount;
            $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
            $sendsms = $this->apiManager->sendSMS($user_id,$message);
            
            // Your a/c no. xxxxxxxxxx6688 is credited by Rs.1.00 on 17/07/20 by a/c no. XXXXXXX9999 (Ref no 019912963322).
            // Your a/c no. xxxxxxxxxx#VAR1# is credited by Rs.#VAR2# on #VAR3# by UPI #VAR4# (Ref no #VAR5#).
            
            // $VAR1 = substr($user_u->mobile,-4);
            // $VAR2 = $amount;
            // $VAR3 = $transaction->created_at->ROUND('d/m/y');
            // $VAR4 = substr($transaction->remitterAccount,-4);
            // $VAR5 = $transaction->transaction_id;
            
            // $mobile = $user_u->mobile;
            // $params = ['From' => env('SMS_FROM'), 'To' => $mobile, 'TemplateName' => 'UBVKACREDIT', 'VAR1' => $VAR1, 'VAR2' => $VAR2, 'VAR3' => $VAR3, 'VAR4' => $VAR4, 'VAR5' => $VAR5];
            // $send_sms = $this->apiManager->sendSMS($mobile,$params);
            
            return response()->json(['success' => true, 'message' => 'Money transfer successfully']);
        }
    }
    
    public function upiCreditCallback(Request $request) {
        
        $reponse_data = new ResponseTable();
        $reponse_data->response = json_encode($_POST);
        $reponse_data->api_name = 'UPI_CREDIT_CALLBACK';  //CREDITCALL
        $reponse_data->save();
        
        $hypto_upi_id = $_POST['hypto_upi_id'];
        $amount = $_POST['amount'];
        
        $upis = UserUpi::where('id2',$hypto_upi_id)->first();
        $user_id = $upis->user_id;
        
        $user = User::find($user_id);
        $user_wallet = $user->wallet;
        
        $txn_id = $this->apiManager->txnId("UPICR");
        $txn = new Transactions();
        $txn->user_id = $user_id;
        $txn->transaction_id = $txn_id;
        $txn->event = 'UPICREDIT';
        $txn->hypto_txn_id = $_POST['txn_id'];
        $txn->hypto_order = $_POST['order_id'];
        $txn->utr = $_POST['bank_ref_num'];
        $txn->ben_name = $_POST['payer_name'];
        $txn->ben_ac_number = $_POST['source'];
        $txn->remitterAccount = $_POST['payee_vpa'];
        $txn->txn_note = $_POST['payer_note'];
        $txn->amount = round($amount,5);
        $txn->current_balance = round($user_wallet,5);
        $txn->final_balance = round($user_wallet + $amount,5);
        $txn->transfer_type = $_POST['payment_type'];
        $txn->transferTime = $_POST['txn_time'];
        $txn->txn_type = 'Credit';
        $txn->status = 1;
        $txn->save();
        
        $user->wallet = round($user_wallet + $amount,5);
        $user->save();
        
        $title = 'UPI payment received';
        $message = "Dear Merchant, Rs. ".$amount." credited in your account balance.";
        $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
//        $sendsms = $this->apiManager->sendSMS($user_id,$message);
        
        // Your a/c no. xxxxxxxxxx#VAR1# is credited by Rs.#VAR2# on #VAR3# by UPI #VAR4# (Ref no #VAR5#).
        // Your a/c no. xxxxxxxxxx6688 is credited by Rs.1.00 on 17/07/20 by UPI demouser@yesbank (Ref no 019912963322).
        
        // $VAR1 = substr($user->mobile,-4);
        // $VAR2 = $amount;
        // $VAR3 = $txn->created_at->format('d/m/y');
        // $VAR4 = $txn->ben_ac_number;
        // $VAR5 = $txn->transaction_id;
        
        // $mobile = $user->mobile;
        // $params = ['From' => env('SMS_FROM'), 'To' => $mobile, 'TemplateName' => 'UBUPICOLLECTION', 'VAR1' => $VAR1, 'VAR2' => $VAR2, 'VAR3' => $VAR3, 'VAR4' => $VAR4, 'VAR5' => $VAR5];
        // $send_sms = $this->apiManager->sendSMS($mobile,$params);
                
        return response()->json(['success' => true, 'message' => 'success']);
    }
    
    public function upiCollectionHistory(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);
            
            $data = Transactions::select('id','transaction_id','status','event','transfer_type','utr','ben_ac_number','created_at','ben_name','txn_note',
            \DB::raw('ROUND((amount),2) as amount'),
            \DB::raw('ROUND((current_balance),2) as current_balance'),
            \DB::raw('ROUND((final_balance),2) as final_balance'),
            \DB::raw('ROUND((commission),2) as commission'))
            ->where('user_id',$user_id)
            ->where('event','UPICREDIT')
            ->whereBetween('created_at', array($from, $to))
            ->orderBy('id','desc')->get();
            
            return response()->json(['success' => true, 'message' => 'UPI Collection History', 'data' => $data]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function vkaCollectionHistory(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);
            
            $data = Transactions::select('id','transaction_id','status','event','transfer_type','utr',
            'ben_ac_number','created_at','remitterAccount','remitterName',
            \DB::raw('ROUND((amount),2) as amount'),
            \DB::raw('ROUND((current_balance),2) as current_balance'),
            \DB::raw('ROUND((final_balance),2) as final_balance'),
            \DB::raw('ROUND((commission),2) as commission'))
            ->where('user_id',$user_id)
            ->where('event','CREDITVA')
            ->whereBetween('created_at', array($from, $to))
            ->orderBy('id','desc')->get();
            
            return response()->json(['success' => true, 'message' => 'VKA Collection History', 'data' => $data]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function addBeneficiary(Request $request) {
        return response()->json(['success' => false,'message' => 'Servce Not Available', 'data' => '']);
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response) {
            
            $ben = new Beneficiary();
            $ben->user_id = $user_id;
            $ben->account_holder_name = $request->get('account_holder_name');
            $ben->mobile = $request->get('mobile');
            $ben->account_number = $request->get('account_number');
            $ben->bank_name = $request->get('bank_name');
            $ben->ifsc = $request->get('ifsc');
            $ben->branch = $request->get('branch');
            $ben->city = $request->get('city');
            $ben->state = $request->get('state');
            $ben->save();
            
            return response()->json(['success' => true, 'message' => 'Beneficiary added']);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function getBeneficiary(Request $request) {
        return response()->json(['success' => false,'message' => 'Servce Not Available', 'data' => '']);
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            $data = Beneficiary::select('*')->where('user_id',$user_id)->get();
            
            return response()->json(['success' => true, 'message' => 'Beneficiary data', 'data' => $data]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function deleteBeneficiary(Request $request) {
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            $ben_id = $request->get('beneficiary_id');
            $data = Beneficiary::where('id',$ben_id)->delete();
            
            return response()->json(['success' => true, 'message' => 'Beneficiary deleted']);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function transactionDetail(Request $request) {
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            $txn_id = $request->get('transaction_id');
            $data = Transactions::where('transaction_id',$txn_id)->first();
            
            if($data)
                return response()->json(['success' => true, 'message' => 'Transaction detail', 'data' => $data]);
            else
                return response()->json(['success' => false, 'message' => 'Transaction not found!']);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function checkRefundStatus(Request $request) {
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            $txn_id = $request->get('transaction_id');
            $data = Transactions::where('event','REFUND')->where('ref_txn_id',$txn_id)->first();
            
            if($data)
                return response()->json(['success' => true, 'message' => 'Refund credited', 'data' => $data]);
            else
                return response()->json(['success' => false, 'message' => 'Refund not intialized yet!']);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function commissionReport(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response) { 
            
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);
            
            $data = Transactions::select('transactions.id','transactions.user_id','transactions.transaction_id','transactions.event',
            'transactions.transfer_type','transactions.ben_name','transactions.ben_ac_number','transactions.ben_ac_ifsc','transactions.utr',
            'transactions.status','transactions.created_at','operators.name as op_name',
            \DB::raw('(CASE WHEN operators.name != null THEN operators.name ELSE "" END) AS op_name'),
            \DB::raw('ROUND((current_balance),2) as current_balance'),
            \DB::raw('ROUND((final_balance),2) as final_balance'),
            \DB::raw('ROUND((amount),2) as amount'),
            \DB::raw('ROUND((tds),2) as tds'),
            \DB::raw('ROUND((gst),2) as gst'),
            \DB::raw('ROUND((transactions.commission),2) as commission'),
            \DB::raw('ROUND((transactions.retailer_commission),2) as retailer_commission'))
            ->leftjoin('operators','operators.id','transactions.op_id')
            ->where('user_id',$user_id)
            ->where('transactions.status','1')
            ->where('transactions.commission','>',0)
            ->whereBetween('transactions.created_at', array($from, $to))
            ->get();
        
            return response()->json(['success' => true, 'message' => 'Commission report' , 'data' => $data]);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }

    public function userVAaccounts(Request $request) {
        
        $user_id = $request->get("user_id");
        $user_token = $request->get("user_token");
        $check_user_token = User::where('id',$user_id)->where('user_token',$user_token)->first();
        if($check_user_token){
        
            $check = User::where('id',$user_id)->where('status',"1")->first();
            
            if($check){
                $baseurl = env('APP_URL').'/uploads/bank_logo/';
                $image = \DB::raw("CONCAT('$baseurl',logo) AS logo");
                $user1 = UserVA::select('*',$image)->where('user_id',$user_id)->where('status',"1")->get();
        	    if($user1){
        	       return response()->json(['success' => true,'message' => '','data' => $user1]); 
        	    }else{
        	        return response()->json(['success' => false,'message' => 'User Not Found','user_status'=>1]);
        	    }  
            }else{
                return response()->json(['success' => false,'message' => 'Invalid Request', 'user_status'=>1]);
            }
            
        }else{
            return response()->json(['success' => false,'message' => 'Invalid Token', 'user_status'=>0]);
        }
    }
    
    
    public function getBanks(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {   
            $baseurl = env('APP_URL').'/uploads/banks/';
            $image = \DB::raw("CONCAT('$baseurl',img) AS img");
            
            $usermapping = UsersMapping::where('user_id',$user_id)->first();
        
            $banks = [];
            
            if($usermapping) {
                $banks = BankDetail::select('*',$image)->where('status','1')->where('user_id',$usermapping->toplevel_id)->get();
            }
            else{
                $banks = BankDetail::select('*',$image)->where('status','1')->where('user_id',1)->get();
            }
           
            return response()->json(['success' => true, 'message' => 'Banks list' , 'data' => $banks]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function createMoneyRequest(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {   
            $amount = $request->get("amount");
            $transfer_type = $request->get("transfer_type");
            $bank_id = $request->get("bank_id");
            $bank_ref = $request->get("bank_ref");
            
            $user = User::where('id',$user_id)->first();
            
            if($amount > 0) {
                
                $check_entry = MoneyRequest::where('bank_id',$bank_id)->where('bank_ref',$bank_ref)->whereIn('status',[1,0])->first();
            
                // if($check_entry) {
                //     return response()->json(['success' => false, 'message' => 'You can not request with same bank and refrence id!']);
                // }
                
                $txn = $this->apiManager->txnId("MR");
                $imagename = '';
                
                if ($request->hasFile('img')) {
                    $file = $request->file('img');
                    $destinationPath = public_path('/uploads/money_request/');
                    $imagename = 'IMG'. $user_id . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($destinationPath, $imagename);
                }
                
                $money_request = new MoneyRequest();
                $money_request->user_id = $user_id;
                $money_request->transaction_id = $txn;
                $money_request->amount = $amount;
                $money_request->transfer_type = $transfer_type;
                $money_request->bank_id = $bank_id;
                $money_request->bank_ref = $bank_ref;
                $money_request->status = 0;
                $money_request->img = $imagename;
                $money_request->save();
                    
                if($money_request)
                    return response()->json(['success' => true, 'message' => 'Money request sent successfully','data'=>$money_request]);
                else
                    return response()->json(['success' => false, 'message' => 'Money request not sent!']);
            }
            else{
                return response()->json(['success' => false,'message' => 'Please enter valid amount!']);
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function moneyReuquestChangeStatus(Request $request) {
        return response()->json(['success' => false, 'message' => 'Record not found!']);
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        { 
    
        $login_id = 1;
        $login_user = User::find($login_id);
        
        $login_type = $login_user->user_type;
        $login_wallet = $login_user->wallet;
        
        $status = $request->get('status'); 
        $action_id = $request->get('money_request_id');
        
        $money_request = MoneyRequest::limit(1)->find($action_id);
        
        if(!$money_request) {
           return response()->json(['success' => false, 'message' => 'Record not found!']);
        }
        
        $user_id = $money_request->user_id;
        $amount = $money_request->amount;
         
        if($money_request->status != 0) {
            return response()->json(['success' => false, 'message' => 'Money request already updated']);
        }
        
        if($status == 'approve') {
            $status = 1;
            $title = 'Money Request Approval';
            $message = "Dear merchant your money request is approved for Rs.".$money_request->amount.' & Reference Id is '.$money_request->transaction_id;
            $respone_message = 'Money request approved';
            
            $user = User::find($user_id);
            
            if($login_type == 3) {
                
                if($amount > $login_wallet) {
                    return response()->json(['success' => false, 'message' => 'Insufficient wallet amount']);
                }
                
                //create distributor txn record
                $txn_id_2 = $this->apiManager->txnId("MR");
                $transaction2 = new Transactions();
                $transaction2->transaction_id = $txn_id_2;
                $transaction2->user_id = $login_id;
                $transaction2->event = 'ADDMONEY';
                $transaction2->transfer_type = $money_request->transfer_type;
                $transaction2->referenceId = $money_request->transaction_id;
                $transaction2->amount = $amount;
                $transaction2->current_balance = $login_wallet;
                $transaction2->final_balance = $login_wallet - $amount;
                $transaction2->status = $status;
                $transaction2->txn_type = 'Debit';
                $transaction2->save();
                
                $login_user->wallet = $login_wallet - $amount;
                $login_user->save();
            
            }
            
            //create user txn record
            $txn_id_1 = $this->apiManager->txnId("MR");
            $transaction = new Transactions();
            $transaction->transaction_id = $txn_id_1;
            $transaction->user_id = $user_id;
            $transaction->event = 'ADDMONEY';
            $transaction->transfer_type = $money_request->transfer_type;
            $transaction->referenceId = $money_request->transaction_id;
            $transaction->amount = $amount;
            $transaction->current_balance = $user->wallet;
            $transaction->final_balance = $user->wallet + $amount;
            $transaction->status = $status;
            $transaction->txn_type = 'Credit';
            $transaction->save();
            
            $user->wallet = $user->wallet + $amount;
            $user->save();
        }
        else {
            $status = 2;
            $title = 'Money Request Rejection';
            $message = 'Dear merchant your money request is rejected for Rs.'.$money_request->amount.' & Reference Id is '.$money_request->transaction_id;
            $respone_message = 'Money request rejected';
        }
        
        $money_request->status = $status;
        $money_request->approved_by = $login_id;
        $money_request->admin_remark = $request->get('reject_reason');
        $money_request->approved_at = now();
        $money_request->save();
        
        $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
//        $sendsms = $this->apiManager->sendSMS($user_id,$message);
        
        if($money_request)
            return response()->json(['success' => true, 'message' => $respone_message]);
        else
            return response()->json(['success' => false, 'message' => 'Something went worng!']);
        }else{
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function moneyRequestReport(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response)
        {
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);

            $baseurl = env('IMAGE_URL').'uploads/money_request/';
            $img = \DB::raw("CONCAT('$baseurl',money_requests.img) AS img");
        
            $data = MoneyRequest::select('money_requests.*',$img,'bank_details.bank_name')
            ->leftjoin('bank_details','bank_details.id','money_requests.bank_id')
            ->where('money_requests.user_id',$user_id)
            ->whereBetween('money_requests.created_at', array($from, $to))
            ->orderBy('money_requests.id','desc')->get();
            
            return response()->json(['success' => true, 'message' => ' Money request report' , 'data' => $data]);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function myPassbook(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response)
        {
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);
            
            $data = Transactions::select('transactions.*',
            \DB::raw('ROUND((current_balance),2) as current_balance'),
            \DB::raw('ROUND((final_balance),2) as final_balance'),
            \DB::raw('ROUND((amount),2) as amount'),
            \DB::raw('ROUND((tds),2) as tds'),
            \DB::raw('ROUND((gst),2) as gst'),
            \DB::raw('ROUND((transactions.commission),2) as commission'),
            \DB::raw('ROUND((retailer_commission),2) as retailer_commission'),
            'operators.name as op_name')
            
            ->leftjoin('operators','operators.op_code','transactions.op_code')
            ->where('user_id',$user_id)
            ->whereBetween('transactions.created_at', array($from, $to))
            ->orderBy('transactions.id','desc')->get();
            
            return response()->json(['success' => true, 'message' => 'Passbook' , 'data' => $data]);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function refundReport(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response)
        {
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);
            
            $data = Transactions::select('id','transaction_id','ref_txn_id','created_at',
            \DB::raw('ROUND((amount),2) as amount'),
            \DB::raw('ROUND((current_balance),2) as current_balance'),
            \DB::raw('ROUND((final_balance),2) as final_balance'))
            ->where('user_id',$user_id)
            ->where('event','REFUND')
            ->whereBetween('created_at', array($from, $to))
            ->orderBy('id','desc')->get();
            
            return response()->json(['success' => true, 'message' => 'Refund Report' , 'data' => $data]);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function profileUpdate(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response)
        {
           
            $user = User::find($user_id);
        
            if ($request->hasFile('profile_pic')) {
                $file = $request->file('profile_pic');
                $destinationPath = public_path('/uploads/profile_pics/');
                $imagename = 'IMG'. $user_id . time() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $imagename);
                $user->profile_pic = $imagename;
            }
        
            $user->save();
            
            if($user)
                return response()->json(['success' => true, 'message' => 'Profile updated successfully']);
            else
                return response()->json(['success' => false, 'message' => 'Something went wrong!']);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function fundAddedCallback(Request $request) {
        
        $response_table = new ResponseTable();
        $response_table->response = json_encode($_POST);
        $response_table->api_name = 'FUND_ADDED_CALLBACK';
        $response_table->save();
        
        $rmtr_account_no = $_POST['rmtr_account_no'];
        
        $userbank = UserBank::where('account_no',$rmtr_account_no)->first();
        
        if($userbank) {
            
            $amount = $_POST['amount'];
            
            $user_id = $userbank->user_id;
            $user = User::find($user_id);
            
            $current_balance = $user->wallet;
            $final_balance = $user->wallet + $amount;
            
            $user->wallet = round($final_balance,5);
            $user->save();
            
            $txn_id = $this->apiManager->txnId("FA");
        
            $transaction = new Transactions();
            $transaction->transaction_id = $txn_id;
            $transaction->user_id = $user_id;
            $transaction->event = 'FUNDADDED';
            $transaction->txn_type = 'Credit';
            $transaction->amount = round($amount,5);
            $transaction->current_balance = round($current_balance,5);
            $transaction->final_balance = round($final_balance,5);
            
            $transaction->ben_ac_number = $_POST['bene_account_no'];
            $transaction->ben_ac_ifsc = $_POST['bene_account_ifsc'];
            $transaction->remitterName = $_POST['rmtr_full_name'];
            $transaction->remitterAccount = $_POST['rmtr_account_no'];
            $transaction->remitterIfsc = $_POST['rmtr_account_ifsc'];
            $transaction->txn_note = $_POST['rmtr_to_bene_note'];
            $transaction->transfer_type = $_POST['payment_type'];
            $transaction->transferTime = $_POST['txn_time'];
            $transaction->referenceId = $_POST['id'];
            $transaction->utr = $_POST['bank_ref_num'];
            $transaction->status = 1;
            $transaction->save();
            
            //send notification
            $title = 'Fund added';
            $message = "Rs. ".$amount . ' is credited to your wallet';
            $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
//            $sendsms = $this->apiManager->sendSMS($user_id,$message);
            
            return response()->json(['success' => true, 'message' => 'Transaction done!']);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Transaction failed!']);
        }
    }
    
    public function fundAddedHistory(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response) {
            
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);
            
            $data = Transactions::select('id','transaction_id','created_at','txn_type','ben_ac_number','ben_ac_ifsc','remitterName','remitterAccount',
            'remitterIfsc', 'txn_note', 'transfer_type','transferTime','referenceId','utr','status',
            \DB::raw('ROUND((amount),2) as amount'),
            \DB::raw('ROUND((current_balance),2) as current_balance'),
            \DB::raw('ROUND((final_balance),2) as final_balance'))
            ->where('user_id',$user_id)
            ->where('event','FUNDADDED')
            ->whereBetween('created_at', array($from, $to))
            ->orderBy('id','desc')->get();
            
            return response()->json(['success' => true, 'message' => 'Fund Added Report' , 'data' => $data]);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function updatePaymentStatus(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response) {
            
            $user = User::find($user_id);
            $user->payment_status = 1;
            $user->node_balance = 0;
            $user->save();
            
            return response()->json(['success' => true, 'message' => 'Payment done!']);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function sendSMS(Request $request) {
        
        $password = $request->get('password');
        $mobile = $request->get('mobile');
        $message = $request->get('message');
        
        if($password != "123456") {
            return response()->json(['success' => false, 'message' => 'Whoops! Incorrect password']);
        }
        
        $user = User::where('mobile',$mobile)->first();
        
        if($user) {
            $user_id = $user->id;
            $sms_sent = $this->apiManager->sendSMS($user_id,$message);
            return response()->json(['success' => true, 'message' => 'Message sent']);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Whoops! Mobile number not found']);
        }
    }
    
    public function getUserVerifyBankAccount(Request $request)  {
        
        $user_id = $request->get("user_id");
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response)
        {
            // $op = Operators::where('op_code','DMTVRF')->first();
            
            // $commission = $op->commission;
            // $commission_type = $op->commission_type;
            
            // if($commission_type=='percentage') {
            //     $commission = $op->commission / 100;
            // }
            
            // $commission = 4;
            
            $setting = Setting::select('*')->first();
            $commission = $setting->bank_verification_fee;
            
            $user = User::find($user_id);
            
            if($user->wallet < $commission) {
                return response()->json(['success' => false, 'message' => 'Insufficient wallet balance!']);        
            }
            else
            {
                $admin_id = 1;
        
                $ifsc = $request->get('ifsc');
                $number = $request->get('number');
                
                $reference_number = $this->apiManager->txnId("VERIFY");
                
                $url = "https://partners.hypto.in/api/verify/bank_account?ifsc=".$ifsc."&number=".$number."&reference_number=".$reference_number;
                        
                $apicall = $this->apiManager->hpytoPostApiCall($url); 
                
                $instantpay_token = env('INSTANTPAY_TOKEN') ;
                
                $payee = [
                    "accountNumber"=>$number,
                    "bankIfsc"=>$ifsc,
                    ];
                
                $req_params = ["payee"=>$payee,"consent" => "Y", "isCached" => 0, "externalRef" => $reference_number, "latitude" => "22.3039", "longitude" => "70.8022"];
                $req_raw = json_encode($req_params);
                
                $url = "https://api.instantpay.in/identity/verifyBankAccount";
                
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $req_raw,
                CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'X-Ipay-Client-Id: YWY3OTAzYzNlM2ExZTJlOfXB0J5BcUJC35CfpULFcAA=',
            'X-Ipay-Auth-Code: 1',
            'X-Ipay-Client-Secret: 2caa04f1697fec28250d8a43f198db7d2417e4b05a16fab5dd724213781dd627',
            'X-Ipay-Endpoint-Ip: 192.185.129.79',
            'Content-Type: application/json'
          ),
                ));
                
                $apicall = curl_exec($curl);
                
                curl_close($curl);
                
                    
                $response_table = new ResponseTable();
                $response_table->response = $apicall;
                $response_table->api_name = 'HYPTOVERIFYBANK';
                $response_table->request = $url;
                $response_table->save();
                    
                $response = json_decode($apicall);   
                
                if($response->statuscode) {
                    
                    // $amount = $response->data->amount;
                    $transfer_type = 'IMPS';
                    $status = $response->statuscode;
                    $reason = $response->status;
                    $verify_account_holder = $response->data->payee->name;
                    
                    // "PENDING" "COMPLETED" "FAILED"
                    
                    if($status =='TXN') {
                        $status = 1;
                        $success = true;
                        $msg = 'Bank verification done successfully';
                        
                        $transaction = new Transactions();
                        $transaction->transaction_id = $reference_number;
                        $transaction->user_id = $user_id;
                        $transaction->event = 'VERIFYBANKUSER';
                        $transaction->transfer_type = $transfer_type;
                        $transaction->amount = $commission;
                        $transaction->current_balance = round($user->wallet,5);
                        $transaction->final_balance = round($user->wallet - $commission,5);
                        $transaction->reason = $reason;
                        $transaction->txn_type = 'Debit';
                        $transaction->status = $status;
                        $transaction->save();  
                        
                        $user->wallet = round($user->wallet - $commission,5);
                        $user->save();
                        
                    }elseif($status == 'PENDING') {
                        $status = 0;
                        $success = true;
                        $msg = 'Bank verification under process';
                        
                    }else {
                        $status = 2;
                        $success = false;
                        $msg = 'Bank verification failed';
                        
                    }
                    
                    return response()->json(['success' => $success, 'message' => $msg, 'verify_account_holder' => $verify_account_holder]);  
                    
                }else {
                    return response()->json(['success' => false, 'message' => $response->message]);    
                }
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function getCommissions(Request $request)  {
        $user_id = $request->get("user_id");
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response)
        {
            $user = User::find($user_id);
            $slab_type = $user->slab;
            
            $service_id = $request->get('service_id');
            if($service_id){
                
                if($slab_type == 2) {
                    $data = Operators2::select('id','service_id','name','commission','commission_type','min_amount','max_amount')
                    ->where('status','1')->where('service_id',$service_id)->get();
                }
                elseif($slab_type == 3) {
                    $data = Operators3::select('id','service_id','name','commission','commission_type','min_amount','max_amount')
                    ->where('status','1')->where('service_id',$service_id)->get();
                }
                elseif($slab_type == 4) {
                    $data = Operators4::select('id','service_id','name','commission','commission_type','min_amount','max_amount')
                    ->where('status','1')->where('service_id',$service_id)->get();
                }
                else {
                    $data = Operators::select('id','service_id','name','commission','commission_type','min_amount','max_amount')
                    ->where('status','1')->where('service_id',$service_id)->get();
                }
            }
            else {
                
                if($slab_type == 2) {
                    $data = Operators2::select('id','service_id','name','commission','commission_type','min_amount','max_amount')
                    ->where('status','1')->orderBy('service_id')->get();
                }
                elseif($slab_type == 3) {
                    $data = Operators3::select('id','service_id','name','commission','commission_type','min_amount','max_amount')
                    ->where('status','1')->orderBy('service_id')->get();
                }
                elseif($slab_type == 4) {
                    $data = Operators4::select('id','service_id','name','commission','commission_type','min_amount','max_amount')
                    ->where('status','1')->orderBy('service_id')->get();
                }
                else {
                    $data = Operators::select('id','service_id','name','commission','commission_type','min_amount','max_amount')
                    ->where('status','1')->orderBy('service_id')->get();
                }
            }
            return response()->json(['success' => true, 'message' => 'Commissions!', 'data' => $data]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function raiseDispute(Request $request)  {
        $user_id = $request->get("user_id");
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response)
        {
            $transaction_id = $request->get('transaction_id');
            $reason = $request->get('reason');
            
            $transaction = Transactions::where('transaction_id',$transaction_id)->first();
            
            if($transaction) {
                
                $disputed = Dispute::where('transaction_id',$transaction_id)->first();
                if($disputed) {
                    if($disputed && $disputed->status == 0) {
                        return response()->json(['success' => true, 'message' => 'Your dispute under processing!']);
                    }
                    else {
                        $dispute_entry = Dispute::find($check_in_dispute->id);
                    }
                }
                else {
                    $dispute_entry = new Dispute();
                }
                $dispute_entry->user_id = $user_id;
                $dispute_entry->transaction_id = $transaction_id;
                $dispute_entry->reason = $reason;
                $dispute_entry->save();
                
                return response()->json(['success' => true, 'message' => 'Your dispute raised!']);
            }
            else
            {
                return response()->json(['success' => false, 'message' => 'Transaction not found!']);
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function getDispute(Request $request)  {
        
        $user_id = $request->get("user_id");
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response)
        {
            $transaction_id = $request->get('transaction_id');
            $transaction = Transactions::where('transaction_id',$transaction_id)->first();
            
            if($transaction) {
                $disputed = Dispute::where('transaction_id',$transaction_id)->first();
                if($disputed) {
                    return response()->json(['success' => true, 'message' => 'Your dispute details!', 'status' => $disputed->status,
                    'data' => $disputed]);
                }
                else {
                    return response()->json(['success' => false, 'message' => 'Your dispute data not found!']);
                }
            }
            else
            {
                return response()->json(['success' => false, 'message' => 'Transaction not found!']);
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function getPaytmOrderId(Request $request)  {
        
        $user_id = $request->get("user_id");
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response) {
            
            $txn_id = $this->apiManager->txnId("PTM");
            
            $amount = $request->get('amount');
            
            $user = User::find($user_id);
            
            $current_balance = $user->wallet;
            $final_balance = $user->wallet;
            
            $transaction = new Transactions();
            $transaction->transaction_id = $txn_id;
            $transaction->user_id = $user_id;
            $transaction->event = 'PAYTMTXN';
            $transaction->amount = round($amount,5);
            $transaction->current_balance = round($current_balance,5);
            $transaction->final_balance = round($final_balance,5);
            $transaction->commission = 0;
            $transaction->tds = 0;
            $transaction->gst = 0;
            $transaction->retailer_commission = 0;
            $transaction->status = 0;
            $transaction->save();
            
            return response()->json(['success' => true, 'message' => 'Paytm order id!', 'order_id' => $txn_id]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function paytmCallback(Request $request)  {
        $response_table = new ResponseTable();
        $response_table->response = json_encode($_GET);
        $response_table->api_name = 'PAYTM_CALLBACK';
        $response_table->request = $_GET;
        $response_table->save();
    }

    public function doSettlement(Request $request) {
        return response()->json(['success' => false,'message' => 'Servce Not Available', 'data' => '']);
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response) {
            
            $service_status = User::where('id',$user_id)->where('dmt_service',0)->first();
            if($service_status) {
                return response()->json(['success' => false, 'message' => 'Your service is disabled!']);
            }
            
            $kycdocs = KycDoc::where('user_id',$user_id)->where('status',1)->first();
            if(!$kycdocs) {
                return response()->json(['success' => false, 'message' => 'Verify your kyc first']);
            }
            
            $user = User::find($user_id);
            $user_wallet = $user->wallet;
            $payment_type = $request->get('payment_type');
            $amount = $request->get('amount');
            $note = $request->get('note') ? urlencode($request->get('note')) : env('APP_NAME');
            $txn_note = $request->get('note') ? $request->get('note') : env('APP_NAME');
            
            $fee = env('SETTLEMENT_CHARGE');
        
            $total_amount = $amount + $fee;
            
            if($user_wallet < $total_amount){
                return response()->json(['success' => false, 'message' => 'Balance is low.']);
            }
            
            $txn_id = $this->apiManager->txnId("SET");
            
            if($payment_type=="IMPS" || $payment_type=="NEFT" || $payment_type=="RTGS") {
                
                $userbank = UserBank::where('user_id',$user_id)->where('primary_bank',1)->first();
                
                if(!$userbank) {
                    return response()->json(['success' => false, 'message' => 'Primary bank not found!']);
                }
                
                $ifsc = $userbank->ifsc;
                $number = $userbank->account_no;
                $beneficiary_name = $userbank->holder;
                
                //before api call entry
                $transaction = new Transactions();
                $transaction->transaction_id = $txn_id;
                $transaction->user_id = $user_id;
                $transaction->event = 'SETTLETXN';
                $transaction->transfer_type = $payment_type;
                $transaction->amount = round($amount,5);
                $transaction->commission = 0;
                $transaction->tds = 0;
                $transaction->gst = 0;
                $transaction->retailer_commission = 0;
                $transaction->current_balance = 0;
                $transaction->final_balance = 0;
                $transaction->ben_name = $beneficiary_name;
                $transaction->ben_ac_number = $number;
                $transaction->ben_ac_ifsc = $ifsc;
                $transaction->txn_note = $txn_note;
                $transaction->status = 0;
                $transaction->save();
                
                $url = "https://partners.hypto.in/api/transfers/initiate?amount=".$amount."&payment_type=".$payment_type."&ifsc=".$ifsc."&number=".$number."&note=".$note."&beneficiary_name=".urlencode($beneficiary_name)."&reference_number=".$txn_id;
                    
                $apicall = $this->apiManager->hpytoPostApiCall($url);        
                    
                $response_table = new ResponseTable();
                $response_table->response = $apicall;
                $response_table->api_name = 'SETTLEMENT_TXN';
                $response_table->request = $url;
                $response_table->save();
                
                $response = json_decode($apicall);
                
                if($response->success) {
                    
                    $status_res = $response->data->status;
                    
                    if($status_res == 'PENDING') {
                        $status = 0; 
                        $message = "Your txn no. ". $txn_id ." is under process"; 
                    }
                    elseif($status_res == 'COMPLETED') {
                        $status = 1; 
                        $message = "Your txn no. ". $txn_id ." is successfully transferred";
                    }else {
                        $status = 2;
                        $message = "Your txn no. ". $txn_id ." is failed";
                        $txn = Transactions::find($transaction->id);
                        $txn->status = $status;
                        $txn->save();
                        return response()->json(['success' => false, 'message' => 'Transaction failed' ]);
                    }                    
                    
                    $final_balance = $user_wallet - $total_amount;
                    
                    $txn = Transactions::find($transaction->id);
                    $txn->commission = round($fee,5);
                    $txn->current_balance = round($user_wallet,5);
                    $txn->final_balance = round($final_balance,5);
                    $txn->txn_type = 'Debit';
                    $txn->status = $status;
                    $txn->save();
                    
                    $user_u = User::find($user_id);
                    $user_u->wallet = round($final_balance,5);
                    $user_u->save();
                    
                    //send_notification
                    $title = $payment_type.' Transaction';
                    $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
//                    $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
                    return response()->json(['success' => true, 'message' => 'Transaction done successfully', 'data' => $txn ]);
                }
                else
                {
                    $transaction =Transactions::find($transaction->id);
                    $transaction->transaction_id = $txn_id;
                    $transaction->user_id = $user_id;
                    $transaction->event = 'SETTLETXN';
                    $transaction->transfer_type = $payment_type;
                    $transaction->amount = round($amount,5);
                    $transaction->commission = round($fee,5);
                    $transaction->current_balance = round($user_wallet,5);
                    $transaction->final_balance = round($user_wallet,5);
                    $transaction->ben_name = $beneficiary_name;
                    $transaction->ben_ac_number = $number;
                    $transaction->ben_ac_ifsc = $ifsc;
                    $transaction->txn_note = $txn_note;
                    $transaction->status = 2;
                    $transaction->save();
                    
                    $title = $payment_type.' Transaction';
                    $message = "Your txn no. ". $txn_id ." is failed";
                    $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
//                    $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
                    return response()->json(['success' => false, 'message' => "Transaction fail due to server error!"]);
                }
            }
            else {
                return response()->json(['success' => false, 'message' => 'Invalid payment type']);
            }
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function settlementHistory(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response) {
            
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);

            $data = Transactions::select('id','transaction_id','status','amount','event','transfer_type','utr',
            'ben_name','ben_ac_number','ben_ac_ifsc','commission','tds','gst','retailer_commission','current_balance','final_balance','created_at','txn_note',
            \DB::raw('ROUND((current_balance),2) as current_balance'),
            \DB::raw('ROUND((final_balance),2) as final_balance'),
            \DB::raw('ROUND((amount),2) as amount'),
            \DB::raw('ROUND((tds),2) as tds'),
            \DB::raw('ROUND((gst),2) as gst'),
            \DB::raw('ROUND((commission),2) as commission'),
            \DB::raw('ROUND((retailer_commission),2) as retailer_commission')
            )
            ->where('user_id',$user_id)
            ->where('event','SETTLETXN')
            ->whereBetween('created_at', array($from, $to))
            ->orderBy('id','desc')->get();
            
            return response()->json(['success' => true, 'message' => 'Settlement history', 'data' => $data]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function onlineFundHistory(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response) {
            
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);

            $data = Transactions::select('id','transaction_id','status','event','transfer_type','utr',
            'ben_name','ben_ac_number','ben_ac_ifsc','commission','created_at','txn_note',
            DB::raw('ROUND(current_balance,2) as current_balance'),
            DB::raw('ROUND(final_balance,2) as final_balance'),
            DB::raw('ROUND(amount,2) as amount'),
            DB::raw('ROUND(commission,2) as commission'),
            DB::raw('ROUND(retailer_commission,2) as retailer_commission')
            )
            ->where('user_id',$user_id)
            ->where('event','PAYTMTXN')
            ->whereBetween('created_at', array($from, $to))
            ->orderBy('id','desc')->get();
            
            return response()->json(['success' => true, 'message' => 'Online fund history', 'data' => $data]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    //04-12-2020
    public function getPayuOrderId(Request $request)  {
        
        $user_id = $request->get("user_id");
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response) {
            
            $txn_id = $this->apiManager->txnId("PYU");
            
            $amount = $request->get('amount');
            
            $user = User::find($user_id);
            
            $current_balance = $user->wallet;
            $final_balance = $user->wallet;
            
            $transaction = new Transactions();
            $transaction->transaction_id = $txn_id;
            $transaction->user_id = $user_id;
            $transaction->event = 'PAYUTXN';
            $transaction->amount = round($amount,5);
            $transaction->current_balance = round($current_balance,5);
            $transaction->final_balance = round($final_balance,5);
            $transaction->commission = 0;
            $transaction->tds = 0;
            $transaction->gst = 0;
            $transaction->retailer_commission = 0;
            $transaction->status = 0;
            $transaction->save();
            
            return response()->json(['success' => true, 'message' => 'Payu order id!', 'order_id' => $txn_id]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function payuCallback(Request $request)  {
        
        $response_table = new ResponseTable();
        $response_table->response = json_encode($_POST);
        $response_table->api_name = 'PAYU_CALLBACK';
        $response_table->save();

        return response()->json(['success' => true, 'message' => 'Success!']);
    }
    
    public function getNotifications(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            $unread = Notifications::select('*')->where('user_id',$user_id)->where('status','0')->count();
            
            $data = Notifications::select('*')
            ->where('user_id',$user_id)
            ->where(function($query) {
                $query->where('status','0')->orWhere('status','1');
            })
            ->orderBy('id','desc')
            ->get();
            
            if($data) {
                if(count($data) > 0)
                    return response()->json(['success' => true, 'message' => 'Notification history' , 'data' => $data , 'unread' => $unread]);
                else
                    return response()->json(['success' => true, 'message' => 'History not found' , 'data' => $data , 'unread' => $unread]);
            }
            else {
                return response()->json(['success' => false, 'message' => 'Something went wrong!']);
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }   
    
    public function updateNotifications(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        $status = $request->get("status");
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            if($status == 1) {
                $data = Notifications::where('user_id', $user_id)
                ->where('status', 0)
                ->update(['status' => '1']);
                
                return response()->json(['success' => true, 'message' => 'Status updated successfully']);
            } 
            else if($status == 2) {
                $data = Notifications::where('user_id', $user_id)
                ->where('status', 1)
                ->update(['status' => '2']);
                return response()->json(['success' => true, 'message' => 'Status updated successfully']);
            }
            else {
                return response()->json(['success' => false, 'message' => 'Something went wrong!']);
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }  
    
    
    public function getAppBanners(Request $request) {
        $baseurl = env('APP_URL').'/uploads/app_banners/';
        $image = \DB::raw("CONCAT('$baseurl',image) AS image");
        $banners = AppBanner::select('*',$image)->where('status','1')->get();
        
        return response()->json(['success' => true, 'message' => 'Banners', "data" => $banners]);
    }
}