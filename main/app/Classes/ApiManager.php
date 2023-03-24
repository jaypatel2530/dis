<?php

namespace App\Classes;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

use Auth;
use DB;
use App\User;
use App\Model\KycDoc;
use App\Model\Operators;
use App\Model\Operators2;
use App\Model\Operators3;
use App\Model\Operators4;
use App\Model\Transactions;
use App\Model\Notifications;
use App\Model\MoneyRequest;
use App\Model\UsersMapping;
use App\Model\ResponseTable;

class ApiManager
{
    public function getUserToken() {
        // Produces something like "2019-03-11 12:25:00"
        $current_date_time = Carbon::now()->toDateTimeString(); 
        return bcrypt($current_date_time);
    }
    
    public function distance($lat1, $lon1, $lat2, $lon2, $unit) {
          $theta = $lon1 - $lon2;
          $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
          $dist = acos($dist);
          $dist = rad2deg($dist);
          $miles = $dist * 60 * 1.1515;
          $unit = strtoupper($unit);
        
          if ($unit == "K") {
              return ($miles * 1.609344);
          } else if ($unit == "N") {
              return ($miles * 0.8684);
          } else {
              return $miles;
          }
    }
    
    public function getPassword($length_of_string) { 
    	$str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
    // 	$str_result = '0123456789'; 
    	return substr(str_shuffle($str_result),0, $length_of_string); 
    } 
    
    public function getOTP() {
        return rand(100000, 999999);
    }
    
    public function fetchFromDate($sdate) {
        return date('Y-m-d'. ' 00:00:00', strtotime($sdate));
    }
    
    public function fetchToDate($edate) {
        return date('Y-m-d'. ' 23:59:59', strtotime($edate));
    }
    
    public function getInflowEvents() {
        return $inflow_events = ['AEPSTXN','CREDITVA','UPICREDIT','UPIREQUEST','FUNDADDED'];
    }
    
    public function getOutflowEvents() {
        return $outflow_events = ['QUICKDMT','QUICKUPI','DTH','PREPAID','POSTPAID','WATER','ELECTRICITY','LANDLINE','GAS','SETTLETXN'];
    }
    
    public function getCommissionEvents() {
        return $commissoin_events = ['DMTCOMM','OPCOMM'];
    }
    
    public function getDmtEvents() {
        return $commissoin_events = ['QUICKDMT','QUICKUPI'];
    }

    public function getBillPaymentEvents() {
        return $bill_events = ['POSTPAID','GAS','WATER','ELECTRICITY','LANDLINE'];
    }
    
    public function getRechargeEvents() {
        return $recharge_events = ['DTH','PREPAID'];
    }
    
    public function memberID($prifix=null) {
        $day = now()->format('D');
        $txn = rand(1000, 9999);
        $txn_id = strtoupper($prifix . date('ymd') . $txn);
        
        $User = User::where('member_id',$txn_id)->first();
        
        if($User) {
            return $this->memberID($prifix=null);
        }
        else {
            return $txn_id;
        }
    }
    
    
    public function txnId($prifix=null) {

        $day = now()->format('D');
        $txn = rand(100000, 999999);
        $txn_id = strtoupper($day . $prifix . date('ymd') . $txn);
        
        $check_trans = Transactions::where('transaction_id',$txn_id)->first();
        $check_money_request = MoneyRequest::where('transaction_id',$txn_id)->first();
        
        if($check_trans || $check_money_request) {
            return $this->txnId($prifix=null);
        }
        else {
            return $txn_id;
        }
    }
    
    public function verifyUserToken($user_id,$usertoken) {
        $check = User::where('id',$user_id)->where('user_token',$usertoken)->where('status',1)->first();
        if($check)
            return true;
        else
            return false;
    }
    
    public function getUserProfile($user_id) {
        
        $baseurl = env('IMAGE_URL').'uploads/qrcodes/';
        $qr_img = DB::raw("CONCAT('$baseurl',users.qr_img) AS qr_img");
        
        $baseurl2 = env('IMAGE_URL').'uploads/profile_pics/';
        $profile_pic = DB::raw("CONCAT('$baseurl2',users.profile_pic) AS profile_pic");
                
        $data = User::select('users.*',DB::raw('ROUND((users.wallet),2) as wallet'),
        $qr_img,$profile_pic)
        ->where('users.id',$user_id)
        ->first();
        
        $kycdocs = KycDoc::where('user_id',$user_id)->first();
        if($kycdocs)
            $kyc_status=$kycdocs->status;
        else
            $kyc_status=3;
            
        $upi_collections = Transactions::where('user_id',$user_id)->where('event','UPICREDIT')->where('status',1)
        ->whereDate('created_at', Carbon::today())->sum('amount');
        
        $va_collections = Transactions::where('user_id',$user_id)->where('event','CREDITVA')->where('status',1)
        ->whereDate('created_at', Carbon::today())->sum('amount');
        
        $money_transfers = Transactions::where('user_id',$user_id)->whereIn('event',['QUICKUPI','QUICKDMT'])->where('status',1)
        ->whereDate('created_at', Carbon::today())->sum('amount');
        
        $aeps_txns = 0;
            
        $data['kyc_status'] = $kyc_status; 
        $data['dmt_active'] = 1; 
        
        $data['today_upi_collections_total'] = number_format($upi_collections,2); 
        $data['today_va_collections_total'] = number_format($va_collections,2); 
        $data['today_money_transfers_total'] = number_format($money_transfers,2); 
        $data['today_aeps_txns_total'] = number_format($aeps_txns,2); 
        $data['settlement_charge'] = env('SETTLEMENT_CHARGE'); 
        $data['payu_charge'] = env('PAYU_CHARGE'); 
        $data['retailer_activation_fee'] = env('RETAILER_ACTIVATION_FEE');
        $data['topup_net_banking_charge'] = env('TOPUP_NET_BANKING_CHARGE');
        $data['topup_other_charge'] = env('TOPUP_OTHER_CHARGE');
        
        return $data;
    }
    
    public function hyptoRefId() {
        $num = rand(1000000000, 9999999999);
        $ref_id = 'RP' . date('md') . strtoupper($num);
        $check = User::where('va_ref_id',$ref_id)->first();
        if($check) {
            return $this->hyptoRefId();
        }
        return $ref_id;
    }
    
    public function hpytoPostApiCall($url) {
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
        CURLOPT_HTTPHEADER => array(
        "Authorization: ".env('HYPTO_TOKEN'),
        "Content-Type: application/json"),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    
    public function hpytoUPIrequestPostApiCall($params) {
        $data = json_encode($params);        
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://partners.hypto.in/api/upi_collections",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS =>$data,
          CURLOPT_HTTPHEADER => array(
            "Authorization: ".env('HYPTO_TOKEN'),
            "Content-Type: application/json",
            "Content-Type: text/plain"
          ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    
    public function hpytoGetApiCall($url) {
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
        "Authorization: ".env('HYPTO_TOKEN'),
        "Content-Type: application/json"
        ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }
    
    public function ekoPostCall($url,$params) {
        
        // print_r($params);
        
        // exit;
        $key = env('EKO_AEPS_KEY');
        $encodedKey = base64_encode($key);
        $secret_key_timestamp = "".round(microtime(true) * 1000);
        $signature = hash_hmac('SHA256', $secret_key_timestamp, $encodedKey, true);
        $secret_key = base64_encode($signature);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $api_url = env('EKO_AEPS_URL')."service/activate";
        if($api_url == $url) {
            
            $headers = [
                "Cache-Control: no-cache",
                "Content-Type: multipart/form-data",
                "developer_key: ".env('EKO_AEPS_DEVELOPER_KEY'),
                "secret-key: ".$secret_key,
                "secret-key-timestamp: ".$secret_key_timestamp
            ];
        }
        else {
            $headers = [
                "Cache-Control: no-cache",
                "Content-Type: application/x-www-form-urlencoded",
                "developer_key: ".env('EKO_AEPS_DEVELOPER_KEY'),
                "secret-key: ".$secret_key,
                "secret-key-timestamp: ".$secret_key_timestamp
            ];
        }        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $api_response = curl_exec ($ch);
        $curl = curl_init();
        curl_close($curl);
        return $api_response;
    }
    
    public function ekoGetCall($url) {
        $key = env('EKO_AEPS_KEY');
        $encodedKey = base64_encode($key);
        $secret_key_timestamp = "".round(microtime(true) * 1000);
        $signature = hash_hmac('SHA256', $secret_key_timestamp, $encodedKey, true);
        $secret_key = base64_encode($signature);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $headers = [
            "Accept: */*",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Host: api.eko.in:25002",
            "cache-control: no-cache",
            "Content-Type: application/x-www-form-urlencoded",
            "developer_key: ".env('EKO_AEPS_DEVELOPER_KEY'),
            "secret-key: ".$secret_key,
            "secret-key-timestamp: ".$secret_key_timestamp
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $api_response = curl_exec($ch);
        curl_close($ch);

        return $api_response;
    }
    
    public function addDMTCommission($transaction_id, $operator_id) {
        //$operator = Operators::find($operator_id);
    
    	$txn_data = Transactions::where('transaction_id',$transaction_id)->first();
    
        if($txn_data) {
    
        	$retailer_id = $txn_data->user_id;
        	$amount = $txn_data->amount;
    
            $retailer = User::where('id',$retailer_id)->where('user_type','2')->first();
            
            //---------------- Distributor -----------------------
            $distributor = UsersMapping::where('user_id',$retailer_id)->first();
            
            if(!$distributor) {
                return false;
            }
            
            $distributor_id = $distributor->toplevel_id;
            $dist = User::find($distributor_id);
            
            if($dist->slab == 2) {
                $operator = Operators2::find($operator_id);
            }
            elseif($dist->slab == 3) {
                $operator = Operators3::find($operator_id);
            }
            elseif($dist->slab == 4) {
                $operator = Operators4::find($operator_id);
            }
            else{
                $operator = Operators::find($operator_id);
            }
                
            $dist_commission_type = $operator->dist_commission_type;
            if($dist_commission_type == 'flat'){
                $dist_comm = $operator->dist_commission;
            }else{
                $dist_comm = $amount * $operator->dist_commission / 100;
            }
    
            $dist_current_wallet = $dist->wallet;
            $dist_final_wallet = $dist_current_wallet + $dist_comm;
    
            $transaction2 = new Transactions();
            $transaction2->transaction_id = $this->txnId("DC");
            $transaction2->user_id = $distributor_id;
            $transaction2->event = 'DMTCOMM';
            $transaction2->amount = $dist_comm;
            $transaction2->current_balance = $dist_current_wallet;
            $transaction2->final_balance = $dist_final_wallet;
            $transaction2->referenceId = $transaction_id;
            $transaction2->status = 1;
            $transaction2->txn_type = 'Credit';
            $transaction2->ref_txn_id = $transaction_id;
            $transaction2->save();
            
            $dist->wallet = $dist_final_wallet;
            $dist->save();
        
            return true;
        }
        else {
            return false;
        }
    }
    
    public function sendfcmNotification($user_id, $title, $message) {
        $user = User::select('device_token')->where('id',$user_id)->whereNotNull('device_token')->first();
        if($user) 
        {
            $app_key = env('NOTIFICATION_KEY');
            $token = $user->device_token;
            
            $registrationIds = array($token);
            
            $msg = array (
                'title' => $title,
                'message' => $message,
            );
            
            $fields = array('registration_ids' => $registrationIds, 'data' => $msg);
    
            $headers = array('Authorization: key='.$app_key, 'Content-Type: application/json');
    
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            curl_close($ch);
            $res = json_decode($result);
            
            if ($res->success == 1) {
                $create_notify = new Notifications();
                $create_notify->user_id = $user_id;
                $create_notify->title = $title;
                $create_notify->message = $message;
                $create_notify->save();
            } 
            
            return true;
        }
    }
    
    public function roundpayApi($mobile,$opcode,$amount,$txn) {
        $opdetail = Operators::where('op_code',$opcode)->first();
        
        $url = "https://roundpay.net/API/TransactionAPI?UserID=".env('ROUNDPAY_UID')."&Token=".env('ROUNDPAY_TOKEN')."&Account=$mobile&Amount=$amount&SPKey=".$opdetail->op_code_rp."&APIRequestID=$txn&Format=1";
        ini_set("allow_url_fopen", 1);
        $Get_Response = file_get_contents($url);
        
        $reponse_data = new ResponseTable();
        $reponse_data->response = $Get_Response;
        $reponse_data->api_name = 'ROUNDPAY';
        $reponse_data->save();
        
        return $Get_Response;
    }
    
    public function addOpCommission($transaction_id, $operator_id) {
        
    	$operator = Operators::find($operator_id);
    	$txn_data = Transactions::where('transaction_id',$transaction_id)->first();
    
        if($txn_data) {
    
        	$retailer_id = $txn_data->user_id;
        	$amount = $txn_data->amount;
    
            $retailer = User::where('id',$retailer_id)->where('user_type','2')->first();
            
            //---------------- Distributor -----------------------
            $distributor = UsersMapping::where('user_id',$retailer_id)->first();
            
            if(!$distributor) {
                return false;
            }
            
            $distributor_id = $distributor->toplevel_id;
    
            $dist_commission_type = $operator->dist_commission_type;
            if($dist_commission_type == 'flat'){
                $dist_comm = $operator->dist_commission;
            }else{
                $dist_comm = $amount * $operator->dist_commission / 100;
            }
    
            $dist = User::find($distributor_id);
    
            $dist_current_wallet = $dist->wallet;
            $dist_final_wallet = $dist_current_wallet + $dist_comm;
    
            $transaction2 = new Transactions();
            $transaction2->transaction_id = $this->txnId("OC");
            $transaction2->user_id = $distributor_id;
            $transaction2->event = 'OPCOMM';
            $transaction2->amount = round($dist_comm,5);
            $transaction2->current_balance = round($dist_current_wallet,5);
            $transaction2->final_balance = round($dist_final_wallet,5);
            $transaction2->referenceId = $transaction_id;
            $transaction2->status = 1;
            $transaction2->txn_type = 'Credit';
            $transaction2->save();
            
            $dist->wallet = round($dist_final_wallet,5);
            $dist->save();
            
            return true;
        }
        else {
            return false;
        }
    }
    
    public function addAEPSCommission($transaction_id, $operator_id) {
        
    	$txn_data = Transactions::where('transaction_id',$transaction_id)->first();
    
        if($txn_data) {
    
        	$retailer_id = $txn_data->user_id;
        	$amount = $txn_data->amount;
    
            $retailer = User::where('id',$retailer_id)->where('user_type','2')->first();
            
            //---------------- Distributor -----------------------
            $distributor = UsersMapping::where('user_id',$retailer_id)->first();
            
            if(!$distributor) {
                return false;
            }
            
            $distributor_id = $distributor->toplevel_id;
            $dist = User::find($distributor_id);
            
            if($dist->slab == 2) {
                $operator = Operators2::find($operator_id);
            }
            elseif($dist->slab == 3) {
                $operator = Operators3::find($operator_id);
            }
            elseif($dist->slab == 4) {
                $operator = Operators4::find($operator_id);
            }
            else{
                $operator = Operators::find($operator_id);
            }
                
            $dist_commission_type = $operator->dist_commission_type;
            if($dist_commission_type == 'flat'){
                $dist_comm = $operator->dist_commission;
            }else{
                $dist_comm = $amount * $operator->dist_commission / 100;
            }
    
            $dist_current_wallet = $dist->wallet;
            $dist_final_wallet = $dist_current_wallet + $dist_comm;
    
            $transaction2 = new Transactions();
            $transaction2->transaction_id = $this->txnId("DC");
            $transaction2->user_id = $distributor_id;
            $transaction2->event = 'AEPSCOMM';
            $transaction2->amount = $dist_comm;
            $transaction2->current_balance = $dist_current_wallet;
            $transaction2->final_balance = $dist_final_wallet;
            $transaction2->referenceId = $transaction_id;
            $transaction2->status = 1;
            $transaction2->txn_type = 'Credit';
            $transaction2->ref_txn_id = $transaction_id;
            $transaction2->save();
            
            $dist->wallet = $dist_final_wallet;
            $dist->save();
            
            //---------------- Super Distributor -----------------------
            $super_distributor = UsersMapping::where('user_id',$distributor_id)->first();
            
            if(!$super_distributor) {
                return false;
            }
            
            $super_distributor_id = $super_distributor->toplevel_id;
            $super_dist = User::find($super_distributor_id);
            
            if($super_dist->slab == 2) {
                $operator = Operators2::find($operator_id);
            }
            elseif($super_dist->slab == 3) {
                $operator = Operators3::find($operator_id);
            }
            elseif($super_dist->slab == 4) {
                $operator = Operators4::find($operator_id);
            }
            else {
                $operator = Operators::find($operator_id);
            }
                
            $super_dist_commission_type = $operator->sd_commission_type;
            if($super_dist_commission_type == 'flat') {
                $super_dist_comm = $operator->sd_commission;
            }else {
                $super_dist_comm = $amount * $operator->sd_commission / 100;
            }
    
            $super_dist_current_wallet = $super_dist->wallet;
            $super_dist_final_wallet = $super_dist_current_wallet + $super_dist_comm;
    
            $transaction3 = new Transactions();
            $transaction3->transaction_id = $this->txnId("SDC");
            $transaction3->user_id = $super_distributor_id;
            $transaction3->event = 'AEPSCOMM';
            $transaction3->amount = $super_dist_comm;
            $transaction3->current_balance = $super_dist_current_wallet;
            $transaction3->final_balance = $super_dist_final_wallet;
            $transaction3->referenceId = $transaction_id;
            $transaction3->status = 1;
            $transaction3->txn_type = 'Credit';
            $transaction3->ref_txn_id = $transaction_id;
            $transaction3->save();
            
            $super_dist->wallet = $super_dist_final_wallet;
            $super_dist->save();
            
            return true;
        }
        else {
            return false;
        }
    }

    public function refundAmount($txn) {
        
        $trans = Transactions::where('transaction_id',$txn)->first();
        if($trans->status == 1 || $trans->status == 0 || $trans->status == 2){
    
            $user = User::limit(1)->find($trans->user_id);
            $cbalance = $user->wallet;
            if($trans->event == 'QUICKDMT'){
                $fbalance = $user->wallet + $trans->amount + $trans->commission - $trans->retailer_commission;
            }else{
                $fbalance = $user->wallet + $trans->amount - $trans->commission;
            }
            
            $user->wallet = round($fbalance,5);
            $user->save();
    
            $updatetrans = Transactions::limit(1)->find($trans->id);
            $updatetrans->status = 3;
            $updatetrans->save();
            
            $txnid = $this->txnId('RF');
    
            $transaction = new Transactions();
            $transaction->user_id = $trans->user_id;
            $transaction->transaction_id = $txnid;
            $transaction->ref_txn_id = $trans->transaction_id;
            $transaction->amount = round($trans->amount,5);
            $transaction->commission = round($trans->commission,5);
            $transaction->current_balance = round($cbalance,5);
            $transaction->final_balance = round($fbalance,5);
            $transaction->event = 'REFUND';
            $transaction->txn_type = 'Credit';
            $transaction->status = 1;
            $transaction->save();
            
            return true;
        }
        else {
            return true;
        }
    }
    
    public function instantpayApi($mobile,$opcode,$amount,$txn) {
        
        $opdetail = Operators::where('op_code',$opcode)->first();
        $req = "{\r\n\t\"token\"\t: \"393297ff6a016226a86ea495fe715769\",\r\n\t\"request\":{\r\n\t\t\"request_type\" : \"PAYMENT\",\r\n\t\t\"outlet_id\":\"82580\",\r\n\t\t\"biller_id\" : \"".$opdetail->op_code_inst."\",\r\n\t\t\"reference_txnid\":{\r\n\t\t\t\"agent_external\":\"$txn\",\r\n\t\t\t\"billfetch_internal\":\"$txn\",\r\n\t\t\t\"validate_internal\":\"\"\r\n\t\t},\r\n\t\t\"params\":{\r\n\t\t\t\"param1\":\"$mobile\",\r\n\t\t\t\"param2\":\"4405\"\r\n\t\t},\r\n\t\t\"payment_channel\" : \"AGT\",\r\n\t\t\"payment_mode\":\"Cash\",\r\n\t\t\"payment_info\":\"bill\",\r\n\t\t\"device_info\" : {\r\n\t\t\t\"TERMINAL_ID\":\"$mobile\",\r\n\t\t\t\"MOBILE\":\"9898461265\",\r\n\t\t\t\"GEOCODE\":\"12.1234,12.1234\",\r\n\t\t\t\"POSTAL_CODE\":\"110001\"\r\n\t\t},\r\n\t\t\"remarks\":{\r\n\t\t\t\"param1\":\"$mobile\",\r\n\t\t\t\"param2\":\"\"\r\n\t\t},\r\n\t\t\"amount\":\"$amount\"\r\n\t}\r\n}";
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://www.instantpay.in/ws/services/bbps/api",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $req,
          CURLOPT_HTTPHEADER => array(
            "Accept: application/json",
            "Content-Type: application/json"
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        $reponse_data = new ResponseTable();
        $reponse_data->response = $response;
        $reponse_data->api_name = 'INSTANTPAY';
        $reponse_data->request = $req;
        $reponse_data->save();
        
        return $response;
    }
    
    public function remitindiaApi($mobile,$opcode,$amount,$txn) {
      $opdetail = Operators::where('op_code',$opcode)->first();
      
      $url = "https://www.remitindia.in/NTDAPI/RechargeAPI.aspx?MobileNo=8939418182&APIKey=wh7cL8QgcwbGC3uLw5HZTDHb4JKfc0FTWLS&REQTYPE=RECH&STV=$opdetail->stv&REFMOBILENO=&CUSTNO=$mobile&SERCODE=$opdetail->op_code1&AMT=$amount&REFNO=$txn&RESPTYPE=JSON";
      ini_set("allow_url_fopen", 1);
      $Get_Response = file_get_contents($url);
      $reponse_data = new ResponseTable();
      $reponse_data->response = $Get_Response;
      $reponse_data->api_name = 'REMITINDIA';
      $reponse_data->save();

      return $Get_Response;
    }
   
    // public function sendSMS($to, $params) {
    //     $url = env('SMS_URL');
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL,$url);
    //     curl_setopt($ch, CURLOPT_POST, 1);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     $api_response = curl_exec ($ch);
    //     curl_close ($ch);
    //     return true;
    // }
    
    // public function sendSMS($user_id,$message) {
        
    //     $user = User::find($user_id);
    //     if($user) {
            
    //         $mobile = $user->mobile;
    //         $apiKey = urlencode('752971-7edf4d-795453-beb3da-4a885a');
        
    //         $numbers = urlencode($mobile);
    //         $sender = urlencode('SMSIND');
    //         $message = rawurlencode($message);
            
    //         $data = 'AuthKey='.$apiKey.'&MobileNo='.$numbers.'&SenderId='.$sender.'&Message='.$message.'&route=1&type=text';
        
    //         $ch = curl_init('https://www.jetmsg.in/api/v1/sendsms.php?' . $data);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         $response = curl_exec($ch);
    //         curl_close($ch);
            
    //         // $reponse_data = new ResponseTable();
    //         // $reponse_data->response = $response;
    //         // $reponse_data->api_name = 'SMS';
    //         // $reponse_data->request = $data;
    //         // $reponse_data->save();
    //     }
        
    //     return true;
    // }
    public function sendSMS_mobile($mobile,$message) {
        $mobile = $mobile;
        $msg  = urlencode($message);
        
        // $data = "username=ruman&apikey=64C1C-323D9&apirequest=Text&sender=IRUMAN&mobile=".$mobile."&message=".$msg."&route=TRANS&TemplateID=1207161811958331678&format=JSON";
            
        $ch = curl_init('http://www.alots.in/sms-panel/api/http/index.php?' . $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $reponse_data = new ResponseTable();
        $reponse_data->response = $response;
        $reponse_data->api_name = 'SMS';
        $reponse_data->request = $message;
        $reponse_data->save();
            
        return true;    
    }
    public function sendSMS($user_id,$message) {
        
        $user = User::find($user_id);
        if($user) {
            
            $sms = $message;
            $mobile = $user->mobile;
            $msg  = urlencode($message);
            
            // $data = "username=ruman&apikey=64C1C-323D9&apirequest=Text&sender=IRUMAN&mobile=".$mobile."&message=".$msg."&route=TRANS&TemplateID=1207161786317444059&format=JSON";
            
            $ch = curl_init('http://www.alots.in/sms-panel/api/http/index.php?' . $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            
            $reponse_data = new ResponseTable();
            $reponse_data->response = $response;
            $reponse_data->api_name = 'SMS';
            $reponse_data->request = $sms;
            $reponse_data->save();
        }
        
        return true;
    }
    public function REGSMS($user_id,$message) {
        
        $user = User::find($user_id);
        if($user) {
            
            $sms = $message;
            $mobile = $user->mobile;
            $msg  = urlencode($message);
            
             $data = "username=ruman&apikey=64C1C-323D9&apirequest=Text&sender=IRUMAN&mobile=".$mobile."&message=".$msg."&route=TRANS&TemplateID=1207161832850692131&format=JSON";
            
            $ch = curl_init('http://www.alots.in/sms-panel/api/http/index.php?' . $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            
            $reponse_data = new ResponseTable();
            $reponse_data->response = $response;
            $reponse_data->api_name = 'SMS';
            $reponse_data->request = $sms;
            $reponse_data->save();
        }
        
        return true;
    }
    public function rechargeApiPostCall($mobile,$opcode,$amount,$txn_id) {
  
        $opdetail = Operators::where('op_code',$opcode)->first();
        
        $token = 'S8HAGOKS0WCRICOTBWA1RDVBEOHZLD8TRWRA0ZOHYNISIGBXGLQ';
        $agentcode = 'SOAPI392862';
        
        // https://apis.softcareinfotech.in/MobileRecharge/Recharge?tokenkey=Token_Key&mobileno=Customer_Mobile_No&operatorid=SOFTVIPREPAID&agentcode=1321&amount=10
        
        $string = "tokenkey=".$token."&mobileno=".$mobile."&operatorid=".$opdetail->op_code_softcare."&amount=".$amount."&agentcode=".$agentcode;
        
        $url = "https://apis.softcareinfotech.in/MobileRecharge/Recharge?".$string;
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array("Content-Type: application/json"),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        $reponse_data = new ResponseTable();
        $reponse_data->response = $response;
        $reponse_data->request = $url;
        $reponse_data->api_name = 'RECHARGE_API';
        $reponse_data->save();
        
        return $response;
    }
    
    public function softcareGetApiCall($url,$params) {
    
        $token_params = "?tokenkey=".env('SOFTCARE_TOKEN')."&agentcode=".env('SOFTCARE_AGENTCODE').'&';
        
        $url = $url.$token_params.$params;
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
        ));
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        $reponse_data = new ResponseTable();
        $reponse_data->response = $response;
        $reponse_data->api_name = 'BBPS_BILLFETCH';
        $reponse_data->request = $url;
        $reponse_data->save();
        
        return $response;
        
      
        
    }
    
    public function softcarePostApiCall($url,$params) {
    exit;
        $token_params = "?tokenkey=".env('SOFTCARE_TOKEN')."&agentcode=".env('SOFTCARE_AGENTCODE').'&';
        
        $url = $url.$token_params.$params;
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://apis.softcareinfotech.in/NPCI_BBPS/BillPay?tokenkey=Token_Key&knumber=123456789012&billerid=CESC00000KOL01&refid=jsd73xvt7z040112464&agentcode=ABC123",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS =>"{\n\"ref_id\": \"10091712282187127212\",\n\"bill_details\": {\n  \"K Number\": \"123456789013\",\n   \"amount\":\"310\"\n   },\n\"biller_details\": {\n  \"biller_id\": \"CESC00000KOL01\"\n}\n}\n",
          CURLOPT_HTTPHEADER => array(
            "jwt_token: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6Ijc3Nzc3Nzc3NzciLCJhZ2VudElkIjoiMTEwNjUxMDgiLCJpYXQiOjE1ODUzNzQ0ODIsImV4cCI6MTU4NTM3ODA4Mn0.2koP_zWV0Gm4_wB4SHZXIJdbwLUqTTs_avL6DVtKUxs",
            "content-type: application/json"
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        echo $response;
        
        
        
        
        
        
        
        
        
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
        ));
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }
    
    
    
}