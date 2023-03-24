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
use App\Model\Transactions;
use App\Model\UsersMapping;
use App\Model\BankDetail;
use App\Model\MoneyRequest;
use App\Model\TopupRequest;

use App\Model\BbpsBiller;
use App\Model\BbpsCategory;
use App\Model\BbpsBillersParam;

class TestApiController extends Controller
{
    public function __construct(ApiManager $apiManager){
        $this->apiManager = $apiManager;
    }

    //UPI REQUEST
    public function upiRequest(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        //check_service_status
        $service_status = User::where('id',$user_id)->where('upi_request_service',0)->first();
        if($service_status) {
            return response()->json(['success' => false, 'message' => 'Your service is disabled!']);
        }
            
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response)
        {
            $user = User::find($user_id);
            $user_wallet = $user->wallet;
            $payment_type = 'UPI';
            $amount = $request->get('amount');
            $note = $request->get('note') ? $request->get('note') : 'RPPAY';
            $txn_note = $request->get('note') ? $request->get('note') : 'RPPAY';
            
            $txn_id = $this->apiManager->txnId("UPIR");
            
            $upi_id  = $request->get('upi_id');
            
            //before api call entry
            $transaction = new Transactions();
            $transaction->transaction_id = $txn_id;
            $transaction->user_id = $user_id;
            $transaction->event = 'UPIREQUEST';
            $transaction->transfer_type = $payment_type;
            $transaction->ben_ac_number = $upi_id;
            $transaction->amount = round($amount,5);
            $transaction->current_balance = $user_wallet;
            $transaction->final_balance = $user_wallet;
            $transaction->status = 0; //pending
            $transaction->txn_note = $txn_note;
            $transaction->save();
            
            $now = Carbon::now(); 
            $expiry_time = $now->addMinutes(15);
            
            $url = "https://partners.hypto.in/api/upi_collections?upi_id=".$upi_id."&amount=".$amount."&order_id=".$txn_id."&note=".$note."&expiry_time=".$expiry_time."&connected_banking=false";
            
            $params = ["upi_id" => $upi_id, 
            "amount" => $amount, 
            "order_id" => $txn_id, 
            "note" => $note, 
            "expiry_time" => (string)$expiry_time, 
            "connected_banking" => false];
            
            $apicall = $this->apiManager->hpytoUPIrequestPostApiCall($params); 
                
            $reponse_data = new ResponseTable();
            $reponse_data->response = $apicall;
            $reponse_data->api_name = 'HYPTO_UPI_REQUEST'; //HYPTOAPI
            $reponse_data->request = $url;
            $reponse_data->save();
            
            $response = json_decode($apicall);
            
            if($response->success) {
                
                $status_res = $response->data->status;
                
                if($status_res == 'PENDING') {
                    $status = 0; //pending
                }elseif($status_res == 'COMPLETED') {
                    $status = 1; // success
                }else{
                    $status = 2; // failed
                }                    
                
                //send notification
                $title = 'UPI Request';
                $message = "UPI collection request sent and your txn id: ".$txn_id;
                $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
                // $sendsms = $this->apiManager->sendSMS($user_id,$message);
            
                return response()->json(['success' => true, 'message' => 'UPI collection request sent']);
            }
            else
            {
                $transaction = Transactions::find($transaction->id);
                $transaction->reason = $response->reason[0];
                $transaction->response_reason = $response->message;
                $transaction->status = 2;
                $transaction->save();
                
                $title = 'UPI Request';
                $message = "Your UPI collection txn no. ". $txn_id ." is failed";
                $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
                // $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
                if(isset($response->reason)) {
                    $message = $response->reason[0];
                }
                else {
                    $message = "Transaction fail due to server error!";
                }
                return response()->json(['success' => false, 'message' => $message]);
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function upiRequestHistory(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response) {
            
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);

            $data = Transactions::select('id','transaction_id','status','event','transfer_type','utr',
            'ben_name','ben_ac_number','ben_ac_ifsc','commission','created_at','txn_note','reason','response_reason',
            DB::raw('ROUND(current_balance,2) as current_balance'),
            DB::raw('ROUND(final_balance,2) as final_balance'),
            DB::raw('ROUND(amount,2) as amount'),
            DB::raw('ROUND(commission,2) as commission'),
            DB::raw('ROUND(retailer_commission,2) as retailer_commission')
            )
            ->where('user_id',$user_id)
            ->where('event','UPIREQUEST')
            ->whereBetween('created_at', array($from, $to))
            ->orderBy('id','desc')->get();
            
            return response()->json(['success' => true, 'message' => 'Online fund history', 'data' => $data]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function upiRequestCallback(Request $request) {
     
        $response_table = new ResponseTable();
        $response_table->response = json_encode($_POST);
        $response_table->api_name = 'HYPTO_UPI_REQUEST_CALLBACK';
        $response_table->save();
        
        if(isset($_POST['status'])) {
            
            $txn_id = $_POST['order_id'];
            $txn = Transactions::where('transaction_id',$txn_id)->first();
            
            if(!$txn){
                return response()->json(['success' => false, 'message' => 'Something went wrong!']);
            }
            
            $user_id = $txn->user_id;
            $user =  User::find($user_id);
            $user_wallet = $user->wallet;
                    
            $status = $_POST['status'];
            $txn_id = $_POST['order_id'];
            $bank_ref_num = $_POST['bank_ref_num'];
            $note = $_POST['note'];
            $amount = $_POST['amount'];
            
            if($status == 'COMPLETED') {
                $fee = $amount * 0.35 / 100;
                $amount_fina = $amount - $fee;
                $txn->amount = round($amount,5);
                $txn->commission = round($fee,5);
                $txn->current_balance = round($user_wallet,5);
                $txn->final_balance = round($user_wallet + $amount_fina,5);
                $txn->transferTime  = $_POST['completion_time'];
                $txn->referenceId  = $bank_ref_num;
                $txn->txn_type = 'Credit';
                $txn->status = 1;
                $txn->save();
        
                $user->wallet = round($user_wallet + $amount_fina,5);
                $user->save();
                    
                $title = 'UPI Request Done';
                $message = "Dear merchant, Your UPI request is approved, Amount ". $amount." Ref Id: ".$txn_id;
                $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
                $sendsms = $this->apiManager->sendSMS($user_id,$message);
                    
                return response()->json(['success' => true, 'message' => 'Transaction done!']);
            }
            elseif($status == 'PENDING'){
                
                $txn = Transactions::where('transaction_id',$txn_id)->first();
                $txn->status = 0;
                $txn->save();
                
                $title = 'UPI Request Under Process';
                $message = "Dear merchant, Your UPI request is under process, Amount ". $amount." Ref Id: ".$txn_id;
                $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
                $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
                return response()->json(['success' => true, 'message' => 'Transaction under process!']);
            }
            else 
            {
                $txn = Transactions::where('transaction_id',$txn_id)->first();
                $txn->transferTime  = $_POST['failure_time'];
                $txn->referenceId  = $bank_ref_num;
                $txn->reason  = "Request timeout";
                $txn->status = 2;
                $txn->save();
                
                $title = 'UPI Request Failed';
                $message = "Dear merchant, Your UPI request is failed, Amount ". $amount." Ref Id: ".$txn_id;
                $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
                $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
                return response()->json(['success' => false, 'message' => 'Transaction failed!']);
            }
        }
        else{
            return response()->json(['success' => false, 'message' => 'Something went wrong!']);
        }
        
    }
    
    //cashfree
    public function getCashfreePGtoken(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        if($response) {
        
            $amount = $request->get("amount");
            $event = $request->get("event"); //CPGTOPUP(add fund),CPGPACKAGE(for package activation) 
        
            $txn_id = $this->apiManager->txnId("CPG");
        
            $transaction = new Transactions();
            $transaction->transaction_id = $txn_id;
            $transaction->user_id = $user_id;
            $transaction->amount = round($amount,5);
            $transaction->event = $event;
            $transaction->commission = 0;
            $transaction->current_balance = 0;
            $transaction->final_balance = 0;
            $transaction->status = 0;
            $transaction->save();

            $app_id = env('CASHFREE_APP_ID');
            $app_key = env('CASHFREE_SECRET_KEY');
        
            $url = env('CASHFREE_PG_URL').'cftoken/order';
            
            $params = ['orderId' => $txn_id, 'orderAmount' => $amount, 'orderCurrency' => 'INR'];
    
            $params = json_encode($params);
    
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $headers = [
                'Content-Type: application/json',
                'x-client-id:'.$app_id,
                'x-client-secret:'.$app_key,
            ];

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $api_response = curl_exec ($ch);
            curl_close ($ch);
            $data = json_decode($api_response);
        
            if($data->status=="OK") {
                $token = $data->cftoken;
                return response()->json(['success' => true, 'message' => $data->message,
                'amount' =>$amount, 
                'cftoken' => $token, 
                'transaction_id' => $txn_id, 
                'mode' => env('CASHFREE_MODE'), 
                'app_id' => env('CASHFREE_APP_ID'), 
                'secrect_key' => env('CASHFREE_SECRET_KEY'), 
                'callback_url' => env('APP_URL').'/api/cashfree_callback']);
            }
            else {
                return response()->json(['success' => false, 'message' => $data->message]);
            }
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!', 'user_status'=>0]);
        }
    }
    
    public function cashfreeCallback(Request $request) {
        
        $reponse_data = new ResponseTable();
        $reponse_data->response = json_encode($_POST);
        $reponse_data->api_name = 'CASHFREE_CALLBACK';
        $reponse_data->save();
        
        $orderId = $_POST['orderId'];
        $txStatus = $_POST['txStatus'];
        $referenceId = $_POST['referenceId'];
        $amount = $_POST['orderAmount'];
        $paymentMode = $_POST['paymentMode'];
        $txTime = $_POST['txTime'];

        if($_POST['txStatus'] == 'SUCCESS') {
            $status = 1;
        }
        elseif($_POST['txStatus'] == 'PENDING' || $_POST['txStatus'] == 'FLAGGED') {
            $status = 0;
        }
        else {
            $status = 2;
        }

        //Get User Id From OrderID from Transaction table
        $transaction = Transactions::select('*')->where('transaction_id',$orderId)->where('status',0)->first();
        
        if($transaction)
        {
            $user_id = $transaction->user_id;
            $user = User::find($user_id);
        
            if($transaction->event == 'CPGPACKAGE') {
                
                if($status == 1) {
                    $transaction->current_balance = $user->wallet;
                    $transaction->final_balance = $user->wallet;
                    $transaction->txn_type = 'Debit';
                    $transaction->txn_note = 'Online Payment';
                    $transaction->transfer_type = $paymentMode;
                    
                    $user->payment_status = 1; //Paid
                    $message = "Dear merchant your service is activated";
                }
                elseif($status == 0) {
                    $user->payment_status = 3; //Pending
                    $message = "Dear merchant, your activation under process, we will notify when it done";
                }
                else {
                    $user->payment_status = 2;//Failed
                    $message = "Dear merchant, your activation has failed, please try again";
                }
                
                $user->save();
                
                $transaction->referenceId = $referenceId;
                $transaction->status = $status;
                $transaction->save(); 
                
                $title = 'Service Activation';
                $response = $this->apiManager->sendfcmNotification($user_id, $title, $message);
                $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
                return response()->json(['success' => true, 'message' => 'Done!']);
            }
            
            $user_prev_wallet  = $user->wallet;
            $user_after_wallet  = $user->wallet + $amount;
    
            if($_POST['txStatus'] == 'SUCCESS') { 
                
                //Update User Wallet
                // if($_POST['paymentMode'] == 'NET_BANKING'){
                //     $fee = 15;
                // }elseif($_POST['paymentMode'] == 'UPI'){
                //     $fee = 0.05*$amount / 100;
                // }elseif($_POST['paymentMode'] == 'DEBIT_CARD'){
                //     $fee = 1*$amount / 100;
                // }elseif($_POST['paymentMode'] == 'CREDIT_CARD'){
                //     $fee = 2*$amount / 100;
                // }else{
                //     $fee = 0.05*$amount / 100;
                // }
                
                if($_POST['paymentMode'] == 'NET_BANKING'){
                    $fee = 15;
                }elseif($_POST['paymentMode'] == 'UPI'){
                    $fee = 2*$amount / 100;
                }elseif($_POST['paymentMode'] == 'DEBIT_CARD'){
                    $fee = 2*$amount / 100;
                }elseif($_POST['paymentMode'] == 'CREDIT_CARD'){
                    $fee = 2*$amount / 100;
                }else{
                    $fee = 2*$amount / 100;
                }
                
                // $gst = $fee * 18 / 100;
                $gst = 0;
                $user_after_wallet = $user_after_wallet - $gst - $fee;
                $user->wallet = $user_after_wallet;
                $user->save();
                
                $transaction->commission = $fee;
                $transaction->gst = $gst;
                $transaction->current_balance = $user_prev_wallet;
                $transaction->final_balance = $user_after_wallet;
                $transaction->txn_type = 'Credit';
                $transaction->transfer_type = $paymentMode;
            }
    
            $transaction->referenceId = $referenceId;
            $transaction->status = $status;
            $transaction->save(); 
            
            $money_request = new TopupRequest();
            $money_request->user_id = $user_id;
            $money_request->bank_id = 1;
            $money_request->transaction_id = $orderId;
            $money_request->amount = $amount;
            $money_request->bank_ref = $referenceId;
            $money_request->transfer_type = $paymentMode;
            $money_request->status = $status;
            $money_request->approved_at = $txTime;
            $money_request->save();
            
            if($status == 1) {
                $title = 'TopUp';
                $message = "Dear Merchant, Rs.".$amount." credited in your account balance.";
                $response = $this->apiManager->sendfcmNotification($user_id, $title, $message);
                $sendsms = $this->apiManager->sendSMS($user_id,$message);
            }
            
            return response()->json(['success' => true, 'message' => 'Done!']);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Wrong!']);
        }
    }
    
    public function cashfreeTransactionsHistory(Request $request) {
        
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
            ->where('event','CPGTOPUP')
            ->whereBetween('created_at', array($from, $to))
            ->orderBy('id','desc')->get();
            
            return response()->json(['success' => true, 'message' => 'Online fund history', 'data' => $data]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function ekoInitiateFundTransfer(Request $request) {
        
        $initiator_id = env('EKO_AEPS_INITIATOR_ID');
    
        $api_url = "https://api.eko.in:25002/ekoicici/v1/agent/user_code:16731001/settlement/";
        
        $txn_id = $txn_id = $this->apiManager->txnId("EKOFUND");
        
        $params = [
        'initiator_id' => $initiator_id,
        'amount' => 100,
        'payment_mode' => 5,
        'client_ref_id' => $txn_id,
        'recipient_name' => 'bizzweb',
        'ifsc' => 'YESB0CMSNOC',
        'account' => '70809083326392',
        'service_code' => 45,
        'sender_name' => 'bizzweb',
        'source' => 'NEWCONNECT',
        'tag' => 'pay',
        'beneficiary_account_type' => 2];
    
        $api_params = http_build_query($params, '', '&');
        
        $key = env('EKO_AEPS_KEY');
        $encodedKey = base64_encode($key);
        $secret_key_timestamp = "".round(microtime(true) * 1000);
        $signature = hash_hmac('SHA256', $secret_key_timestamp, $encodedKey, true);
        $secret_key = base64_encode($signature);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$api_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS,$api_params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $headers = [
            "Cache-Control: no-cache",
            "Content-Type: application/x-www-form-urlencoded",
            "developer_key: ".env('EKO_AEPS_DEVELOPER_KEY'),
            "secret-key: ".$secret_key,
            "secret-key-timestamp: ".$secret_key_timestamp
        ];
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $api_response = curl_exec ($ch);
        $curl = curl_init();
        curl_close($curl);
        
        $api_data = json_decode($api_response);
        
        $reponse_table = new ResponseTable();
        $reponse_table->response = $api_response;
        $reponse_table->request = json_encode($params);
        $reponse_table->api_name = 'EKO_INITIATE_FUND_TRANSFER';
        $reponse_table->save();
        
        return response()->json(['success' => true, 'message' => 'SUCCESS']);
        
    }
    
    public function getSendNotify(Request $request) {
        
        // $url = "https://apis.softcareinfotech.in/NPCI_BBPS/BillFetch?tokenkey=S8HAGOKS0WCRICOTBWA1RDVBEOHZLD8TRWRA0ZOHYNISIGBXGLQ&knumber=87203124134&billerid=PGVCL0000GUJ01&agentcode=SOAPI392862";
        
        // $url = "https://apis.softcareinfotech.in/NPCI_BBPS/Biller_Info?tokenkey=S8HAGOKS0WCRICOTBWA1RDVBEOHZLD8TRWRA0ZOHYNISIGBXGLQ&agentcode=SOAPI392862";
        
        // $curl = curl_init();
        
        // curl_setopt_array($curl, array(
        //   CURLOPT_URL => $url,
        //   CURLOPT_RETURNTRANSFER => true,
        //   CURLOPT_ENCODING => "",
        //   CURLOPT_MAXREDIRS => 10,
        //   CURLOPT_TIMEOUT => 0,
        //   CURLOPT_FOLLOWLOCATION => true,
        //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //   CURLOPT_CUSTOMREQUEST => "GET",
        // ));
        
        // $response = curl_exec($curl);
        
        // curl_close($curl);
        // echo $response;
    
        // $response_array = json_decode($response);

        // return response()->json(['success' => true, 'message' => 'Done' ,'data' => $response_array]);
        
        // exit;
        
    

        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://aeps.apiseva.com/HMMoneyTransfer/SendMoneyTransfer?tokenkey=S8HAGOKS0WCRICOTBWA1RDVBEOHZLD8TRWRA0ZOHYNISIGBXGLQ&amount=100&account=259427666688&ifsc=INDB0000079&purpose=testing&type=1&mobile=8140666688&retailer_orderId=rp03",
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
        echo $response;
        
        return response()->json(['success' => true, 'message' => 'SUCCESS']);
    }
    
    
    // public function cashfreeUpdateTxnStatus(Request $request) {
        
    //     $user_id = $request->get('user_id');
    //     $usertoken = $request->get("user_token");
        
    //     $transaction_id = $request->get("transaction_id");
    //     $status = $request->get("status");

    //     $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

    //     if($response) {
        
    //         $transaction = Transactions::where('transaction_id',$transaction_id)->first();
            
    //         if($transaction) {
                
    //             $transaction->status = $status;
    //             $transaction->save();
                
    //             $title = 'Service Activation';
    //             $message = "Dear Merchant, Your transaction has failed. Ref Id : ".$transaction_id;
    //             $response = $this->apiManager->sendfcmNotification($user_id, $title, $message);
    //             // $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
    //             return response()->json(['success' => true, 'message' => 'Transaction failed']);
                
    //         }
    //         else{
    //             return response()->json(['success' => false, 'message' => 'Transaction not found']);
    //         }
    //     }
    //     else {
    //         return response()->json(['success' => false, 'message' => 'Unauthorized access!', 'user_status'=>0]);
    //     }
    // }
    
    // public function adiPayRechargeCallback(Request $request) {
    //     $reponse_table = new ResponseTable();
    //     $reponse_table->response = json_encode($_GET);
    //     $reponse_table->request = '';
    //     $reponse_table->api_name = 'ADIPAY_RECHARGE_CALLBACK';
    //     $reponse_table->save();
        
    //     // {"reqid":"1234567890","status":"SUCCESS","remark":"Recharge Success","balance":"100.00","mn":"9804616262","field1":"1805192101270013"}
    //     if(isset($_GET)) {
            
    //         $txn_id = $_GET['reqid'];
    //         $status = $_GET['status'];
    //         $remark = $_GET['remark'];
    //         // $apirefid = $_GET['apirefid'];
            
    //         if($status == 'SUCCESS'){
    //             // SUCCESS
    //             $updatetrans = Transactions::where('transaction_id',$txn_id)->where('status',0)->first();
    //             if($updatetrans) {
                    
    //                 $updatetrans->referenceId = 0;
    //                 $updatetrans->response_reason = $remark;
    //                 $updatetrans->status = 1;
    //                 $updatetrans->save();
                
    //                 $opcomm = $this->apiManager->addOpCommission($txn_id, $updatetrans->op_id);
                
    //                 return response()->json(['success' => true, 'message' => 'success']);
    //             }
    //             else{
    //                 return response()->json(['success' => false, 'message' => 'transaction not found or status already updated!']);
    //             }
    //         }
    //         elseif($status == 'FAILED' || $status == 'REFUND'){
                
    //             $refund =  $this->apiManager->refundAmount($txn_id);
                
    //             return response()->json(['success' => true, 'message' => 'success from failed']);
    //         }
    //         else{
    //             return response()->json(['success' => false, 'message' => 'success from pending']);
    //         }
    //     }
    // }

    // public function payworldAepsCallback(Request $request) {
    //     $reponse_table = new ResponseTable();
    //     $reponse_table->response = json_encode($_GET);
    //     $reponse_table->request = '';
    //     $reponse_table->api_name = 'PAYWORLD_AEPS_CALLBACK';
    //     $reponse_table->save();
    // }  
    
    // public function payworldAepsVerificationCallback(Request $request) {
    //     $reponse_table = new ResponseTable();
    //     $reponse_table->response = json_encode($_GET);
    //     $reponse_table->request = '';
    //     $reponse_table->api_name = 'PAYWORLD_AEPS_VERIFICATION_CALLBACK';
    //     $reponse_table->save();
    // }  
    
    public function instantpayDmtCallback(Request $request) {
        $reponse_table = new ResponseTable();
        $reponse_table->response = json_encode($_GET);
        $reponse_table->request = '';
        $reponse_table->api_name = 'INSTANTPAY_DMT_CALLBACK';
        $reponse_table->save();
        
        return response()->json(['success' => true, 'message' => 'success']);
        
    }  
    
}