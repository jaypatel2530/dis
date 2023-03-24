<?php

namespace App\Http\Controllers;
use App\Classes\ApiManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

use Auth;
use DataTables;
use App\User;
use App\Model\Address; 
use App\Model\AepsTransaction;
use App\Model\KycDoc;
use App\Model\ResponseTable;
use App\Model\ShopDetail;
use App\Model\Transactions;

use App\Model\Operators;
use App\Model\Operators2;
use App\Model\Operators3;
use App\Model\Operators4;

class AepsApiController extends Controller
{
    public function __construct(ApiManager $apiManager) {
        $this->apiManager = $apiManager;
    }
    
    public function aepsRequestOtp(Request $request) {
        return response()->json(['success' => false,'message' => 'Servce Not Available', 'data' => '']);
        $user_id = $request->get('user_id');
        $mobile = $request->get('mobile');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        
        if($response)
        {
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            $api_url = env('EKO_AEPS_URL')."request/otp";
    
            $params = [
                'initiator_id' => $initiator_id,
                'mobile' => $mobile
            ];
        
            $api_params = http_build_query($params, '', '&');
        
            $api_response = $this->apiManager->ekoPostCall($api_url,$api_params);
            
            $api_data = json_decode($api_response);
            
            $reponse_table = new ResponseTable();
            $reponse_table->response = $api_response;
            $reponse_table->api_name = 'EKO_ONBOARDING_OTP';
            $reponse_table->request = $api_url.'?'.$api_params;
            $reponse_table->save();
            
            if(isset($api_data->status)) {
                if($api_data->status == 0) {
                    
                    if($api_data->response_type_id == 1317) 
                        return response()->json(['success' => false, 'message' => 'User already verified', 'isverify' => true]);    
                    else
                        return response()->json(['success' => true, 'message' => 'Request otp response', 'data' => $api_data]);
                }
                else {
                    return response()->json(['success' => false, 'message' => 'Something went wrong!']);
                }
            }
            else{
                return response()->json(['success' => false, 'message' => 'Api response error!', 'data' => $api_response ]);
            }
        }
        else
        {
            return response()->json(['success' => false,'message' => 'Unauthorized access!']);
        }
    }
    
    public function aepsVerifyOtp(Request $request) {
        return response()->json(['success' => false,'message' => 'Servce Not Available', 'data' => '']);
        $user_id = $request->get('user_id');
        $mobile = $request->get('mobile');
        $usertoken = $request->get("user_token");
        $otp = $request->get("otp");
        
        $response = $this->apiManager->verifyUserToken($user_id, $usertoken);
        
        if(!$response) {
            return response()->json(['success' => false,'message' => 'Unauthorized access!']);
        }
        else
        {
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            $api_url = env('EKO_AEPS_URL')."verify";
    
            $params = [
                'initiator_id' => $initiator_id,
                'mobile' => $mobile,
                'otp' => $otp
            ];
        
            $api_params = http_build_query($params, '', '&');
        
            $api_response = $this->apiManager->ekoPostCall($api_url,$api_params);
            $api_data = json_decode($api_response);
            
            if($api_data->status == 0) {
                return response()->json(['success' => true, 'message' => $api_data->message, 'data' => $api_data]);
            }
            else {
                return response()->json(['success' => false, 'message' => $api_data->message]);
            }
        }
    }
    
    public function aepsOnboard(Request $request) {
        return response()->json(['success' => false,'message' => 'Servce Not Available', 'data' => '']);
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        
        $response = $this->apiManager->verifyUserToken($user_id, $usertoken);
        if(!$response) {
            return response()->json(['success' => false,'message' => 'Unauthorized access!']);
        }
        else 
        {
            $user = User::where('id',$user_id)->first();
            $kyc = KycDoc::where('user_id',$user_id)->where('status',1)->first();
            $shop = ShopDetail::where('user_id',$user_id)->first();  
            $address = Address::where('user_id',$user_id)->first();  
            
            $profile_detail = $this->apiManager->getUserProfile($user_id);
            
            if(!$address){
                return response()->json(['success' => false,'message' => 'Please enter address!']);
            }
            
            if($kyc){
                $pan_number = $kyc->pan_number;
            }else{
                return response()->json(['success' => false,'message' => 'Verify your kyc first!']);
            }
            
            if($shop){
                $shop_name = $shop->shop_name;
            }else{
                return response()->json(['success' => false,'message' => 'Add your shop detail!']);
            }
            
            $mobile = $user->mobile;
            
            $first_name = $profile_detail->name;
            $last_name = '';
            $email = $profile_detail->email;
            $residence_address = $address->address;
            $residence_city = $profile_detail->city_name;
            $residence_state = $profile_detail->state_name; 
            $residence_pincode = $address->pincode;
            $dob = $profile_detail->dob;
            
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            $api_url = env('EKO_AEPS_URL')."onboard";
        
            $address_data = ['line' => $residence_address, 
                'city' => $residence_city, 
                'state' => $residence_state, 
                'pincode' => $residence_pincode, 
                'district' => $residence_city,
                'area' => $residence_city,
            ];
    
            $params = [
                'initiator_id' => $initiator_id,
                'pan_number' => $pan_number,
                'mobile' => $mobile,
                'first_name' => $first_name,
                'email' => $email,
                'residence_address' => json_encode($address_data),
                'dob' => $dob,
                'shop_name' => $shop_name,
            ];
    
            $api_params = http_build_query($params, '', '&');
        
            $api_response = $this->apiManager->ekoPostCall($api_url,$api_params);
            $api_data = json_decode($api_response);
            
            $reponse_table = new ResponseTable();
            $reponse_table->response = $api_response;
            $reponse_table->api_name = 'EKO_ONBOARDING';
            $reponse_table->request = json_encode($params);
            $reponse_table->save();
            
            if($api_data->status == 0) {
                
                if($api_data->response_type_id == 1290) { 
                    
                    $eko_update = User::find($user_id);
                    $eko_update->eko_user_code = $api_data->data->user_code;
                    $eko_update->eko_status = 4;
                    $eko_update->save();
                    
                    return response()->json(['success' => true, 'message' => $api_data->message, 'user_code' => $api_data->data->user_code, 'data' => $api_data]); 
                    
                }elseif($api_data->response_type_id == 1307){
                    $eko_update = User::find($user_id);
                    $eko_update->eko_user_code = $api_data->data->user_code;
                    $eko_update->eko_status = 4;
                    $eko_update->save();
                    return response()->json(['success' => false, 'message' => $api_data->message, 'data' => $api_data]);
                }else {
                    return response()->json(['success' => false, 'message' => $api_data->message, 'data' => $api_data]);
                }
            }
            else 
            {
                return response()->json(['success' => false, 'message' => $api_data->message, 'data' => $api_data]);
            }
        }
    }
    
    public function aepsActivateService(Request $request) {
        return response()->json(['success' => false,'message' => 'Servce Not Available', 'data' => '']);
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        
        $response = $this->apiManager->verifyUserToken($user_id, $usertoken);
        
        if(!$response) {
            return response()->json(['success' => false,'message' => 'Unauthorized access!']);
        }
        else 
        {
            $profile_detail = $this->apiManager->getUserProfile($user_id);
            $address = Address::where('user_id',$user_id)->first(); 
            
            $service_code = 43;
            $user_code = $profile_detail->eko_user_code;
            
            $office_address = $request->get('office_address');
            $office_city = $request->get('office_city');
            $office_state = $request->get('office_state');
            $office_pincode = $request->get('office_pincode');
            
            $residence_address = $address->address;
            $residence_city =  $profile_detail->city_name;
            $residence_state =  $profile_detail->state_name;
            $residence_pincode = $address->pincode;
            
            $devicenumber = $request->get('devicenumber');
            $devicename = $request->get('devicename');
            
            $kyc_pan = KycDoc::where('user_id',$user_id)->where('status',1)->first();
            
            if($kyc_pan){
                $image_name_pan = $kyc_pan->pan_image;
                $image_name_aadhar = $kyc_pan->aadhaar_front_image;
                $image_name_aadhar_b = $kyc_pan->aadhaar_back_image;
            }else{
                return response()->json(['success' => false,'message' => 'Verify your kyc first!']);
            }
            
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            $api_url = env('EKO_AEPS_URL')."service/activate";
        
            $office_data = ['line' => $office_address, 
                'city' => $office_city, 
                'state' => $office_state, 
                'pincode' => $office_pincode, 
            ];
            
            $residence_data = ['line' => $residence_address, 
                'city' => $residence_city, 
                'state' => $residence_state, 
                'pincode' => $residence_pincode,
            ];
    
            $params = [
                'service_code' => $service_code,
                'initiator_id' => $initiator_id,
                'user_code' => $user_code,
                'devicenumber' => $devicenumber,
                'modelname' => $devicename,
                //   'office_address' => rawurlencode($office_data),
            // 'address_as_per_proof' => rawurlencode($residence_data),
            ];
            
            
            // 'office_address' => json_encode($office_data),
            // 'address_as_per_proof' => json_encode($residence_data),
                
            $api_params = http_build_query($params, '', '&');
            
            // $api_params = http_build_query($params, '', '&');
            
            $string = $api_params.'&office_address='.json_encode($office_data).'&address_as_per_proof='.json_encode($residence_data);
            
            // exit;
                // $api_params = urlencode($params);
            
            $cfile1 = new \CURLFile(realpath(public_path('uploads/kycdocs/').$image_name_pan));
            $cfile2 = new \CURLFile(realpath(public_path('uploads/kycdocs/').$image_name_aadhar));
            $cfile3 = new \CURLFile(realpath(public_path('uploads/kycdocs/').$image_name_aadhar_b));
        
            $post = array(
            'pan_card' => $cfile1,
            'aadhar_front' => $cfile2,
			'aadhar_back' => $cfile3,
			'form-data' => $string ,
			); 
        
            $api_response = $this->apiManager->ekoPostCall($api_url,$post);
            $api_data = json_decode($api_response);
            
            $reponse_table = new ResponseTable();
            $reponse_table->response = $api_response;
            $reponse_table->api_name = 'EKO_SERIVCESACTIVE';
            $reponse_table->request = json_encode($post);
            $reponse_table->save();
            
            if($api_data->status == 0) {
                
                if($api_data->response_type_id == 1259) {
                    
                    $eko_update = User::find($user_id);
                    $eko_update->eko_status = 2; //pending
                    $eko_update->save();
                
                    return response()->json(['success' => true, 'message' => $api_data->message, 'data' => $api_data]);   
                }
                else {
                    return response()->json(['success' => false, 'message' => $api_data->message,'data' => $api_data ]);
                }
            }
            else
                return response()->json(['success' => false, 'message' => $api_data->message,'data' => $api_data ]);
        }
    }
    
    public function aepsUserServiceEnquiry(Request $request) {
        return response()->json(['success' => false,'message' => 'Servce Not Available', 'data' => '']);
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        
        $response = $this->apiManager->verifyUserToken($user_id, $usertoken);
        
        if(!$response) {
            return response()->json(['success' => false,'message' => 'Unauthorized access!']);
        }
        else 
        {
            $user_data = User::find($user_id);
            
            $service_code = 43;
            $user_code = $user_data->eko_user_code;
            
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            $api_url = env('EKO_AEPS_URL')."services/user_code:".$user_code."?initiator_id=".$initiator_id;
        
            $api_response = $this->apiManager->ekoGetCall($api_url);
            
            $reponse_table = new ResponseTable();
            $reponse_table->response = $api_response;
            $reponse_table->api_name = 'EKO_SERIVCE_ENQUIRY';
            $reponse_table->request = $api_url;
            $reponse_table->save();
            
            $api_data = json_decode($api_response);
            
            if($api_data->status == 0) {
                
                // $var = json_decode($api_data->data->service_status_list[0]);
                
                if(isset($api_data->data->service_status_list)) {
                
                    $verification_status = $api_data->data->service_status_list[0]->verification_status; 
                    $status = $api_data->data->service_status_list[0]->status; 
                    
                    if($status == 1) {
                        
                        $eko_update = User::find($user_id);
                        $eko_update->eko_status = 1; //success
                        $eko_update->save();
                        
                        return response()->json(['success' => true, 'message' => 'Service activated successfully.']);
                        
                    } elseif($status == 0) {
                        
                        $eko_update = User::find($user_id);
                        $eko_update->eko_status = 3; //rejected
                        $eko_update->save();
                        
                        return response()->json(['success' => false, 'message' => 'User needs to upload the documents again using activate service API']);
                    }
                    else {
                        return response()->json(['success' => false, 'message' => 'User status id pending for the service']);
                    }
                }
            }
            else {
                return response()->json(['success' => false, 'message' => $api_data->message, 'data' => $api_data ]);
            }
        }
    }
    
    public function aepsKeysData(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        
        $response = $this->apiManager->verifyUserToken($user_id, $usertoken);
        
        if(!$response) {
            return response()->json(['success' => false,'message' => 'Unauthorized access!']);
        }
        else 
        {
            $user = User::find($user_id);
            
            $environment = env('EKO_AEPS_ENVIRONMENT');
            $developer_key = env('EKO_AEPS_DEVELOPER_KEY');
            $initiator_id = env('EKO_AEPS_INITIATOR_ID');
            $key = env('EKO_AEPS_KEY');
            
            $encodedKey = base64_encode($key);
            $secret_key_timestamp = "".round(microtime(true) * 1000);
            $signature = hash_hmac('SHA256', $secret_key_timestamp, $encodedKey, true);
            $secret_key = base64_encode($signature);
            
            return response()->json(['success' => true,'message' => 'AEPS Keys data', 
            'secret_key' => $secret_key,
            'secret_key_timestamp' => $secret_key_timestamp,
            'environment' => $environment,
            'developer_key' => $developer_key,
            'initiator_id' => $initiator_id,
            'user_code' => $user->eko_user_code,
            'logo' => env('IMAGE_URL').'/theme/img/logo.png',
            'partner_name' => env('APP_NAME'),
            'callbackurl' => env('APP_URL').'/api/aeps_callback_url'
            
            ]);
        }
    }
    
    public function aepsCallbackUrl(Request $request) {
        
        // header("Access-Control-Allow-Origin: *");
        // header('Access-Control-Allow-Origin: https://gateway.eko.in');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        // ini_set("allow_url_fopen", 1);
        $body = file_get_contents('php://input');
        
        $reponse_table = new ResponseTable();
        $reponse_table->response = $body;
        $reponse_table->api_name = 'EKO_CALLBACK';
        $reponse_table->save();
        
        $insert_data = ResponseTable::find($reponse_table->id);
        if(empty($insert_data->response)){
            return response()->json(['action' => 'go','allow' => false,'message'=>'Server Down']);
        }
        $data = json_decode($insert_data->response,true);
        
        if($data['action']) {
            
            if($data['action'] == 'debit-hook') {
            
                $type = $data['detail']['data']['type'];
                
                if($type == 3 || $type == 4) {
                    
                    $user_code = $data['detail']['data']['user_code'];
                    $customer_id = $data['detail']['data']['customer_id'];
                    $client_ref_id = $data['detail']['client_ref_id'];
                    $environment = env('EKO_AEPS_ENVIRONMENT');
                    $developer_key = env('EKO_AEPS_DEVELOPER_KEY');
                    $initiator_id = env('EKO_AEPS_INITIATOR_ID');
                    $key = env('EKO_AEPS_KEY');
                    $encodedKey = base64_encode($key);
                    
                    $secret_key_timestamp = "".round(microtime(true) * 1000);
                    
                    $request_signature = $secret_key_timestamp . $customer_id . $user_code;
                    
                    $signature = hash_hmac('SHA256', $secret_key_timestamp, $encodedKey, true);
                    $signature_req_hash = hash_hmac('SHA256', $request_signature, $encodedKey, true);
                    
                    $secret_key = base64_encode($signature);
                    $request_hash = base64_encode($signature_req_hash);
                    
                    return response()->json(['action' => 'go','allow' => true,'secret_key_timestamp' => $secret_key_timestamp,
                    'request_hash' => $request_hash, 'secret_key' =>$secret_key]);
                    
                }
                elseif($type == 2) {
                    
                    $user_code = $data['detail']['data']['user_code'];
                    $customer_id = $data['detail']['data']['customer_id'];
                    $amount = $data['detail']['data']['amount'];
                    $client_ref_id = $data['detail']['client_ref_id'];
                    $BankIIN = $data['detail']['data']['bank_code'];
                    
                    $environment = env('EKO_AEPS_ENVIRONMENT');
                    $developer_key = env('EKO_AEPS_DEVELOPER_KEY');
                    $initiator_id = env('EKO_AEPS_INITIATOR_ID');
                    $key = env('EKO_AEPS_KEY');
                    $encodedKey = base64_encode($key);
                    
                    $secret_key_timestamp = "".round(microtime(true) * 1000);
                    $request_signature = $secret_key_timestamp . $customer_id . $amount . $user_code;
                    
                    $signature = hash_hmac('SHA256', $secret_key_timestamp, $encodedKey, true);
                    $signature_req_hash = hash_hmac('SHA256', $request_signature, $encodedKey, true);
                    
                    $secret_key = base64_encode($signature);
                    $request_hash = base64_encode($signature_req_hash);
                    
                    
                    $txn_id= $this->apiManager->txnId('AE');
                    
                    $registered = User::where('eko_user_code',$user_code)->first();
                    if($registered){
                        $user_id = $registered->id;
                        
                        //Transaction_table
                        $transactions = new Transactions();
                        $transactions->user_id = $user_id;
                        $transactions->transaction_id = $txn_id;
                        $transactions->vendor_id = $customer_id;
                        $transactions->outlet_id = $user_code;
                        $transactions->bank_iin = $BankIIN;
                        $transactions->amount = $amount;
                        $transactions->event = "AEPSTXN";
                        $transactions->transfer_type = "AEPS";
                        $transactions->status = 0;
                        $transactions->referenceId  = $client_ref_id;
                        $transactions->reason = 'Request Accepted!';
                        $transactions->save();
                        
                        return response()->json(['action' => 'go','allow' => true, 'secret_key_timestamp' => $secret_key_timestamp,
                        'request_hash' => $request_hash, 'secret_key' => $secret_key ]);
                    }
                    else {
                        return response()->json(['action' => 'go','allow' => false,'message'=>'Server Down']);
                    }
                }
                else {
                    return response()->json(['action' => 'go', 'allow' => false, 'message' => 'Server Down']);
                }
            }
            elseif($data['action'] == 'eko-response'){
                
                $response_status = $data['detail']['response']['response_status_id'];
                $client_ref_id = $data['detail']['client_ref_id'];
            
                if($response_status == 0) {
                    
                    $status = $data['detail']['response']['data']['tx_status'];
                    if($status == 0) { //success
                        
                        $amount = $data['detail']['response']['data']['amount'];
                        $sender_name = $data['detail']['response']['data']['sender_name'];
                        $tid = $data['detail']['response']['data']['tid'];
                        $merchantname = $data['detail']['response']['data']['merchantname'];
                        $aadhaar = $data['detail']['response']['data']['aadhar'];
                        $bank_ref_num = $data['detail']['response']['data']['bank_ref_num'];
                    
                        $txn_find = Transactions::where('referenceId',$client_ref_id)->where('status',0)->first();
                        
                        if($txn_find) {
                            $user_id = $txn_find->user_id;
                            
                            $user = User::find($user_id);
                            
                            $user_slab = $user->slab;
            
                            if($user_slab == 2) {
                                $operator = Operators2::where('service_id','21')->where('min_amount', '<=', $amount)
                                ->where('max_amount', '>=',  $amount)->where('status',1)->first();
                            }
                            elseif($user_slab == 3) {
                                $operator = Operators3::where('service_id','21')->where('min_amount', '<=', $amount)
                                ->where('max_amount', '>=',  $amount)->where('status',1)->first();
                            }
                            elseif($user_slab == 4) {
                                $operator = Operators4::where('service_id','21')->where('min_amount', '<=', $amount)
                                ->where('max_amount', '>=',  $amount)->where('status',1)->first();
                            }
                            else {
                                $operator = Operators::where('service_id','21')->where('min_amount', '<=', $amount)
                                ->where('max_amount', '>=',  $amount)->where('status',1)->first();
                            }
                            
                            
                            if($operator) {
                                $commission_type = $operator->commission_type;
                                if($commission_type == 'flat'){
                                    $commission = $operator->commission;
                                }else{
                                    $commission = $amount * $operator->commission / 100;
                                }
                            }
                            else {
                                return response()->json(['success' => false, 'message' => 'Please check with Admin']);
                            }
                            
                            //commission
                            // if($amount > 4000) {
                            //     $commission = 9;
                            // }else{
                            //     $commission = $amount * 0.2 / 100;
                            // }
                            
                            $c_amount = $user->wallet;
                            $f_amount = $user->wallet + $amount + $commission;
                            
                            $update_wallet = User::find($user_id);
                            $update_wallet->wallet = round($f_amount,5);
                            $update_wallet->save();
                            
                            $txn_add = Transactions::find($txn_find->id);
                            $txn_add->ben_name = $sender_name;  //sender_name
                            $txn_add->tid = $tid;
                            $txn_add->status = 1;
                            $txn_add->remitterName = $merchantname;  //merchantname
                            $txn_add->aadhaar = $aadhaar;
                            $txn_add->utr = $bank_ref_num;  //rrn
                            $txn_add->current_balance = round($c_amount,5);
                            $txn_add->final_balance = round($f_amount,5);
                            $txn_add->commission = round($commission,5);
                            $txn_add->txn_type = 'Credit';
                            $txn_add->save();
                            
                            $transaction_id = $txn_add->transaction_id;
                            
                            $aepscomm = $this->apiManager->addAEPSCommission($transaction_id, $operator->id);
                        }
                        elseif($status == 1){
                            $txn_find = Transactions::where('referenceId',$client_ref_id)->first();
                            if($txn_find){
                                $txn_add = Transactions::find($txn_find->id);
                                $txn_add->status = 2;
                                $txn_add->save();
                            }
                        }
                        else { }
                    }
                }
                else 
                {
                    $txn_find = Transactions::where('referenceId',$client_ref_id)->first();
                    if($txn_find){
                        $txn_add = Transactions::find($txn_find->id);
                        $txn_add->status = 2;
                        $txn_add->save();
                    }
                }
                
            }
            else
            {
                return response()->json(['action' => 'go','allow' => false,'message'=>'Server is down!']);
            }
        }
        // $api_response = '';
        // return $api_response;
    }

    public function aepsTransactionHistory(Request $request) {
    
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            $query = Transactions::query();
            
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);
            
            $query->whereBetween('created_at', array($from, $to));
            
            $data = $query->select("id","user_id","transaction_id","event","transfer_type","ben_name","utr","referenceId",
            "remitterName","txn_type","reason","vendor_id","outlet_id","bank_iin","aadhaar","created_at","status",
            \DB::raw('FORMAT((amount),2) as amount'),
            \DB::raw('FORMAT((current_balance),2) as current_balance'),
            \DB::raw('FORMAT((final_balance),2) as final_balance'),
            \DB::raw('FORMAT((commission),2) as commission'))
            ->where('user_id',$user_id)
            ->where('event','AEPSTXN')
            ->orderBy('id','desc')->get();
            
            return response()->json(['success' => true, 'message' => 'Aeps history', 'data' => $data]);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    
}