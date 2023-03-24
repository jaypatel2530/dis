<?php

namespace App\Http\Controllers;
use App\Classes\ApiManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use DB;

use Auth;
use DataTables;
use App\User;
use App\Model\Address; 
use App\Model\AepsTransaction;
use App\Model\KycDoc;
use App\Model\ResponseTable;
use App\Model\Transactions;
use App\Model\Operators;

class ApiboxBbpsController extends Controller
{
    public function __construct(ApiManager $apiManager) {
        $this->apiManager = $apiManager;
    }
    
    public function apiboxFetchBill(Request $request) {
        return response()->json(['success' => false,'message' => 'Servce Not Available', 'data' => '']);
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        
        if($response)
        {   
            $user = User::find($user_id);
            $mobile = $user->mobile;
            
            $txn =  $this->apiManager->txnId("BF");
            
            $op_code = $request->get('op_code');
            $bill_number = $request->get("bill_number");
            $customer_mobile = $request->get('customer_mobile');
            
            $latitude = $request->get('latitude');
            $longitude = $request->get('longitude');
            
            $operator = Operators::where('op_code',$op_code)->first(); 
            
            if(!$operator) {
                return response()->json(['success' => false, 'message' => 'Operator not found']);
            }
            
            $operator_code = $operator->op_code_venfone;
            
            if($request->get('optional1')) {
                $optional1 = $request->get('optional1');
            }
            else {
                $optional1 = '';
            }
            
            if($request->get('optional2')) {
                $optional2 = $request->get('optional2');
            }
            else {
                $optional2 = '';
            }
            
            
            $skey = $operator_code;
            
            $amount = 0;
            $geo_code = $latitude.','.$longitude;
            $pincode = '560032';
            $ipaddress = "119.18.54.162";
            $apibox_token = env("APIBOX_TOKEN");
            $agentmobile = env("APIBOX_AGENT_MOBILE");
            
            $params = [
                "token" => $apibox_token,
                "skey" => $skey,
                "reqid" => $txn,
                "outlet_id" =>"15090",
                "p1" => "dueamt",
                "p2" => $bill_number,
                "p18" => "AGT",
                "p19" => $agentmobile,
                "p20" => $geo_code,
                "p21" => $pincode,
                "p25" => $customer_mobile,
                "format" => "json",
                "p4" => $optional1,
                "p5" => $optional2,
            ];

            $param_string = http_build_query($params);
  
            $api_url = "https://www.apibox.xyz/api/Fetch/Bill?".$param_string;
            
           
            $curl = curl_init();
 
            curl_setopt_array($curl, array(
                CURLOPT_URL => $api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 200,
                CURLOPT_CUSTOMREQUEST => "GET"
            ));
             
            $response = curl_exec($curl);
            $err = curl_error($curl);
             
            curl_close($curl);
             
            // if ($err) {
            //   echo "cURL Error #:" . $err;
            // } else {
            //   echo $response;
            // }
           
            $response_table = new ResponseTable();
            $response_table->request = $api_url;
            $response_table->response = $response;
            $response_table->api_name = 'APIBOXFETCHBILL';
            $response_table->save();
            
            /*{
                "response": {
                    "reqid": "RP01",
                    "CustomerID": "4656010025A",
                    "ResultID": 932092,
                    "due_amt": 2028,
                    "BillDetail": {
                        "CustomerName": "R.KRISHNAMOORTHY, S/O RATHINAVEL,",
                        "Amount": 2028,
                        "DueDate": "2021-02-16",
                        "BillDate": "2021-01-27",
                        "BillNumber": "NA",
                        "BillPeriod": "Monthly"
                    },
                    "AdditionalInformation": [],
                    "response_time": "2021-01-27 12:31:40",
                    "status_code": "RCS",
                    "desc": "Request Completed Successfully"
                },
                "billing": "Billing Not Applicable"
            }*/
            
            $details =  json_decode($response);
            
            if(isset($details->response)) {
                
                $res = $details->response;
            
                if($res->status_code=="RCS") {
                    return response()->json(['success' => true, 'message' => 'Bill details', 'amount' => $res->due_amt,
                    'data' => ['due_amt' => $res->due_amt, 'result_id' => $res->ResultID, 'due_date' => $res->BillDetail->DueDate,
                    'customer_name' => $res->BillDetail->CustomerName], 'reponse_data' => $res ]);
                }
                else {
                    
                    if($res->status_code=="IRP")
                        return response()->json(['success' => false, 'message' => 'Please enter valid customer number', 'amount' => $amount, 'data' => $details]);
                    else
                        return response()->json(['success' => false, 'message' => $res->desc, 'amount' => $amount, 'data' => $details]);
                }
            }
            else {
                return response()->json(['success' => false, 'message' => 'Server down!']);
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function apiboxPayBill(Request $request) {
        return response()->json(['success' => false,'message' => 'Servce Not Available', 'data' => '']);
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        
        if($response)
        {   
            //check_service_status
            $service_status = User::where('id',$user_id)->where('recharge_service',0)->first();
            if($service_status){
                return response()->json(['success' => false, 'message' => 'Your service is disabled!']);
            }
            
            $event = $request->get('event');
            $latitude = $request->get('latitude');
            $longitude = $request->get('longitude');
            $amount = $request->get('amount');
            $result_id = $request->get('result_id'); 
            
            $geo_code = $latitude.','.$longitude;
  
            $pincode = '110019';
            $ipaddress="119.18.54.162";
            $apibox_token = env("APIBOX_TOKEN");
            $agentmobile = env("APIBOX_AGENT_MOBILE");
            
            $user = User::find($user_id);
            
            $cbalance = $user->wallet;
            $fbalance = $user->wallet-$amount;
            
            $txn_id =  $this->apiManager->txnId("BP");
            
            $op_code = $request->get('op_code');
            $bill_number = $request->get("bill_number");
            $customer_mobile = $request->get('customer_mobile');
            
            // $operator = Operators::where('op_code',$op_code)->first(); 
            
            if($user->slab != 1){
                $optable = "operators".$user->slab;
            }else{
                $optable = "operators";
            }
            $operator = DB::Table($optable)->where('op_code',$op_code)->first(); 
            
            if(!$operator) {
                return response()->json(['success' => false, 'message' => 'Operator not found']);
            }
            
            if($cbalance < $amount){
                return response()->json(['success' => false, 'message' => 'Balance is low.']);
            }
            
            // if($cbalance < $user->node_balance) {
            //     return response()->json(['success' => false, 'user_status'=> 1, 
            //     'message' => 'Mini. Wallet Balance 1000 Rs. Required For Recharge and BBPS services. For more information contact admin.', 
            //     'transaction_id'=>$txn_id, 'date'=>date("h:i:s A, F d Y") ]);
            // }
            
            $fee_type = $operator->commission_type;
            if($fee_type == 'flat'){
                $fee = $operator->commission;
            }else{
                $fee = $amount * $operator->commission / 100;
            }

            $transaction = new Transactions();
            $transaction->user_id = $user_id;
            $transaction->transaction_id = $txn_id;
            $transaction->tid = $result_id;
            $transaction->amount = $amount;
            $transaction->event = $event;
            $transaction->mobile = $bill_number;
            $transaction->status = 0;
            $transaction->op_id = $operator->id;
            $transaction->op_code = $op_code;
            $transaction->commission = 0;
            $transaction->current_balance = $cbalance;
            $transaction->final_balance = $cbalance;
            $transaction->save();
            
            if($request->get('optional1')) {
                $optional1 = $request->get('optional1');
            }
            else {
                $optional1 = '';
            }
            
            if($request->get('optional2')) {
                $optional2 = $request->get('optional2');
            }
            else {
                $optional2 = '';
            }
            
            $operator_code = $operator->op_code_venfone;
            
            $params = [
                "outlet_id" => "15090",
                "ResultId" => $result_id,
                "token" => $apibox_token,
                "p1" =>  "new",
                "p2" => $bill_number,
                "p3" =>  $amount,
                "skey" => $operator_code,
                "reqid" => $txn_id,
                "p18" => "AGT",
                "p19" => $agentmobile,
                "p20" => $geo_code,
                "p21" => $pincode,
                "p23" => "Cash",
                "p24" => "Cash Bill Payment",
                "p25" => $customer_mobile,
                "format" => "json",
                "p4" => $optional1,
                "p5" => $optional2
            ];
            
            $param_string = http_build_query($params);
  
            $api_url = "https://www.apibox.xyz/api/Action/transact?".$param_string;
       
            $curl = curl_init();
 
            curl_setopt_array($curl, array(
                CURLOPT_URL => $api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 200,
                CURLOPT_CUSTOMREQUEST => "GET"
            ));
             
            $response = curl_exec($curl);
            $err = curl_error($curl);
             
            curl_close($curl);
             
            // if ($err) {
            //   echo "cURL Error #:" . $err;
            // } else {
            //   echo $response;
            // }
        
            $response_table = new ResponseTable();
            $response_table->request = $api_url;
            $response_table->response = $response;
            $response_table->api_name = 'APIBOXBILLPAYMENT';
            $response_table->save();
                        
                        
            // {
            //     "response": {
            //         "OrderId": "A180716201332TLIK",
            //         "reqid": "3453523trans",
            //         "CustomerID": "102124226",
            //         "Opr_txn_id": "1021001363856",
            //         "Status": "COMPLETED",
            //         "response_time": "2018-07-16 13:13:33",
            //         "status_code": "TXN",
            //         "desc": "Transaction Successful"
            //     },
            //     "billing": {
            //         "opbal": "943.4863",
            //         "txn_value": "610",
            //         "charged_amt": 610,
            //         "clbal": 333.4863
            //     }
            // }
            
            $details =  json_decode($response);
            
            if(isset($details->response)) {
                
                $res = $details->response;
                
                
                
                
                
                
                if($res->status_code=="TXN") { //completed
                    
                    $txn = Transactions::find($transaction->id);
                    $txn->referenceId = $res->OrderId;
                    $txn->commission = $fee;
                    $txn->current_balance = $cbalance;
                    $txn->final_balance = $fbalance + $fee;
                    $txn->status = 1;
                    $txn->txn_type = 'Debit';
                    $txn->save();
                    
                    $user_u = User::find($user_id);
                    $user_u->wallet = $fbalance + $fee;
                    $user_u->save();
                    
                    $opcomm = $this->apiManager->addOpCommission($txn_id, $operator->id);
                    
                    return response()->json(['success' => true, 'message' => 'Payment done', 'amount' => $amount, 'data' => $txn]);
                }
                elseif($res->status_code=="TUP") { //pending
                    
                    $txn = Transactions::find($transaction->id);
                    $txn->referenceId = $res->OrderId;
                    $txn->commission = $fee;
                    $txn->current_balance = $cbalance;
                    $txn->final_balance = $fbalance + $fee;
                    $txn->status = 0; //pending
                    $txn->txn_type = 'Debit';
                    $txn->save();
                    
                    $user_u = User::find($user_id);
                    $user_u->wallet = $fbalance + $fee;
                    $user_u->save();
                    
                    return response()->json(['success' => true, 'message' => 'Payment under process', 'amount' => $amount, 'data' => $txn]);
                }
                else { //failed
                    
                    $txn = Transactions::find($transaction->id);
                    if(isset($res->OrderId)) {
                        $txn->referenceId = $res->OrderId;
                    }
                    
                    $txn->response_reason = $res->desc;
                    $txn->status = 3;
                    $txn->save();
                    
                    $msg =  $res->desc;
                    
                    if(isset($res->status_code)) {
                        if($res->status_code=="IAB") {
                            $msg = 'Transaction failed due to server error';
                        }
                    }
                    
                    return response()->json(['success' => false, 'message' => $msg, 'amount' => $amount, 'data' => $details]);
                }
            }
            else {
                return response()->json(['success' => false, 'message' => 'Server down!']);
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function apiboxPayBillCallback(Request $request) {
        
        $response_table = new ResponseTable();
        $response_table->response = json_encode($_GET);
        $response_table->api_name = 'APIBOX_CALLBACK'; 
        $response_table->save();
        
        // {
        //     "OrderId": "A210127171511LHZV",
        //     "reqid": "DUMMYRQST202101271711",
        //     "Oprtxnid": "DUMMYOPID",
        //     "responsetime": "2021-01-27 17:15:11",
        //     "statuscode": "TXN",
        //     "desc": "Transaction Successful",
        //     "Status": "COMPLETED"
        // }
        
        $data = json_encode($_GET);
        
        $details = json_decode($data);
        
        if(isset($details->Status)){
            
            $transaction_id = $details->reqid;
            $transaction = Transactions::where('transaction_id',$transaction_id)->where('status',0)->first();
            if(!$transaction) {
                return response()->json(['success' => false, 'message' => 'Transaction not found!']);
            }
            
            if($transaction->event == "QUICKDMT") {
                
                $res = $details->response;
                if($res->Status=="COMPLETED" || $res->Status=="PENDING") {
                    
                    $utr = $res->UTR;
                    $referenceId = $res->OrderId;
                    $user_id = $transaction->user_id;
                    $transfer_type = $transaction->transfer_type;
                    
                    if($res->Status=="COMPLETED") {
                        $status = 1;
                        $message = "Your txn no. ". $transaction_id ." is successfully transferred";
                        $opcomm = $this->apiManager->addDMTCommission($transaction_id, $transaction->op_id);
                    }
                    else {
                        $status = 0;
                        $message = "Your txn no. ". $transaction_id ." is under process"; 
                    }
                    
                    $txn = Transactions::find($transaction->id);
                    $txn->status = $status;
                    $txn->referenceId = $referenceId;
                    $txn->utr = $utr;
                    $txn->save();
                 
                    return response()->json(['success' => true, 'message' => $message, 'data' => $txn]);
                }
                else {
                    
                    $txn =Transactions::find($transaction->id);
                    $txn->status = 2;
                    $txn->save();
                    
                    $title = $transfer_type.' Transaction';
                    $message = "Your txn no. ". $transaction_id ." is failed";
                    $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
                    return response()->json(['success' => false, 'message' => 'Transaction failed!']);
                }
            }
            
            
            // BILLPAYMENT CALLBACK SETTINGS
            $op_name = Operators::where('op_code',$transaction->op_code)->first();
            
            $user_id = $transaction->user_id; 
            $amount = $transaction->amount; 
            $mobile = $transaction->mobile;
            $operator_name = $op_name->name;
            
            
            if($details->Status == "COMPLETED") { //completed
            
                $updatetrans = Transactions::limit(1)->find($transaction->id);
                $updatetrans->status = 1;
                $updatetrans->referenceId = $details->OrderId;
                $updatetrans->save();
            
                $opcomm = $this->apiManager->addOpCommission($transaction_id, $op_name->id);
            
                //success notification and sms
                $title = 'Recharge/Bill';
                $message = "Rs. ".$amount." successfully paid for ".$operator_name." for Mobile Number/Consumer Number ".$mobile." TXN:".$transaction_id;
                $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
                // $sendsms = $this->apiManager->sendSMS($user_id,$message);
                
            }
            elseif($details->Status == "PENDING") { //pending
                
            }
            else{
                    
                $trans = Transactions::where('transaction_id',$txn)->first();
                $sts = $trans->status;
                $updatetrans = Transactions::limit(1)->find($trans->id);
                $updatetrans->status = 2;
                $updatetrans->referenceId = $details->OrderId;
                $updatetrans->op_id = $opid;
                $updatetrans->save();
            
                $refund =  $this->apiManager->refundamount($txn);
            
                //failed notification and sms
                $title = 'Recharge/Bill';
                $message = "Recharge/Bill of ".$operator_name." Mobile ".$mobile." for Rs. ".$amount." has failed. We have successfully initiated a refund of Rs. ".$amount." to your wallet.";
                $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
                // $sendsms = $this->apiManager->sendSMS($user_id,$message);
            }
        }
        else {
            return response()->json(['success' => false, 'message' => 'Response error!']);
        }
        
        exit;
        
        // $txn = $request->AGENTID;
        // $status = $request->STATUS;
        // $opid = $request->TRANID;
        // $ref = $request->TRANID;
        
        // $trans = Transactions::where('transaction_id',$txn)->where('status',0)->first();
        
        // if(!$trans) {
        //     return response()->json(['success' => false, 'message' => 'Transaction not found!']);
        // }
        
        // $op_name = Operators::where('op_code',$trans->op_code)->first();
        // $user_id = $trans->user_id; 
        // $amount = $trans->amount; 
        // $mobile = $trans->mobile;
        // $operator_name = $op_name->name;
        
        // if($status == '2'){
    
        //     $updatetrans = Transactions::limit(1)->find($trans->id);
        //     $updatetrans->status = 1;
        //     $updatetrans->referenceId = $ref;
        //     $updatetrans->op_id = $opid;
        //     $updatetrans->save();
            
        //     $opcomm = $this->apiManager->addOpCommission($trans->transaction_id, $op_name->id);
          
        //     //success
        //     //notification and sms
        //     $title = 'Recharge/Bill';
        //     $message = "Rs. ".$amount." successfully paid for ".$operator_name." for Mobile Number/Consumer Number ".$mobile." TXN:".$trans->transaction_id;
        //     $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
        //     $sendsms = $this->apiManager->sendSMS($user_id,$message);
            
        //     // $user_mobile = User::find($user_id);
        //     // $params = ['From' => env('SMS_FROM'), 'To' => $user_mobile->mobile, 'TemplateName' => 'FiniRechargeSuccess', 'VAR1' => $amount, 'VAR2' => $operator_name, 'VAR3' => $operator_name, 'VAR4' => $trans->transaction_id ];
        //     // $send_sms = $this->apiManager->sendSMS($user_mobile->mobile,$params);
            
        // }elseif($status == '0'){
    
        // }
        // else
        // {
        //     $trans = Transactions::where('transaction_id',$txn)->first();
        //     $sts = $trans->status;
        //     $updatetrans = Transactions::limit(1)->find($trans->id);
        //     $updatetrans->status = 2;
        //     $updatetrans->referenceId = $ref;
        //     $updatetrans->op_id = $opid;
        //     $updatetrans->save();
            
        //     if($sts == 0 || $sts == 1){
        //         $refund =  $this->apiManager->refundamount($txn);
        //     }
            
        //     //failed
        //     //notification and sms
        //     $title = 'Recharge/Bill';
        //     $message = "Recharge/Bill of ".$operator_name." Mobile ".$mobile." for Rs. ".$amount." has failed. We have successfully initiated a refund of Rs. ".$amount." to your wallet.";
        //     $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
        //     $sendsms = $this->apiManager->sendSMS($user_id,$message);
            
        //     // $user_mobile = User::find($user_id);
        //     // $params = ['From' => env('SMS_FROM'), 'To' => $user_mobile->mobile, 'TemplateName' => '', 'VAR1' => $operator_name, 'VAR2' => $mobile, 'VAR3' => $amount, 'VAR4' => $amount];
        //     // $send_sms = $this->apiManager->sendSMS($user_mobile->mobile,$params);
        // }
        // return response()->json(['success' => true,'message' => ' Recharge Is done.']);
    } 
    
    
    
    
}