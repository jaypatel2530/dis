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
use App\Model\Transactions;
use App\Model\Operators;

class BbpsApiController extends Controller
{
    public function __construct(ApiManager $apiManager) {
        $this->apiManager = $apiManager;
    }
    
    public function roundpayFetchBill(Request $request) {
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
            
            $operator_code = $operator->op_code_rp;
            
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
            
            $amount = 0;
            $geo_code = $latitude.','.$longitude;
            $pincode = '360005';
            
            $params = [
                "UserID" => env('ROUNDPAY_UID'),
                "Token" => env('ROUNDPAY_TOKEN'),
                "Account" => $bill_number,
                "Amount" => $amount,
                "SPKey" => $operator_code,
                "APIRequestID" => $txn,
                "Optional1" => $optional1,
                "Optional2" => $optional2,
                "GEOCode" => $geo_code,
                "CustomerNumber" => $customer_mobile,
                "Pincode" => $pincode,
                "Format" => 1
            ];
            
            $param_string = http_build_query($params);
  
            $api_url = "https://roundpay.net/API/FetchBill?".$param_string;
            
            // $mplan_opcode = $operator->op_code_mplan;
            
            // $api_url = "https://www.mplan.in/api/electricinfo.php?apikey=e00fe27f601e5d3badff32533f04544f&offer=roffer&operator=".$mplan_opcode."&tel=".$bill_number;
       
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $Get_Response = curl_exec($ch);
            curl_close($ch);
            // print_r($Get_Response);exit;
            
            $response_table = new ResponseTable();
            $response_table->request = $api_url;
            $response_table->response = $Get_Response;
            $response_table->api_name = 'ROUNDPAYFETCHBILL';
            $response_table->save();
            
            // {
            //     "tel": "1419700",
            //     "operator": "TORRENTAHME",
            //     "records": [
            //         {
            //             "CustomerName": "MALI UMESHBHAI FULCHAND",
            //             "BillNumber": "203033050180",
            //             "Billdate": "15-12-2020",
            //             "Billamount": "2000.00",
            //             "Duedate": "29-12-2020",
            //             "status": 1
            //         }
            //     ],
            //     "status": 1
            // }
            
            $bill_details =  json_decode($Get_Response);
            
            // if($bill_details->status==1) {
                
                // $abcccc = array();
                // $abcccc['success'] = true;
                // $abcccc['message'] = "No Payment(s) Due.";
                // $abcccc['amount'] = 0;
                // $abcccc_data = array();
                // $abcccc_data['account'] = $bill_number;
                // $abcccc_data['rpid'] = $bill_details->records[0]->BillNumber;
                // $abcccc_data['dueamount'] = $bill_details->records[0]->Billamount;
                // $abcccc_data['customername'] = $bill_details->records[0]->CustomerName;
                // $abcccc_data['duedate'] = $bill_details->records[0]->Duedate;
                // $abcccc_data['billdate'] = $bill_details->records[0]->Billdate;
                // $abcccc_data['billnumber'] = $bill_details->records[0]->BillNumber;
                // $abcccc_data['refid'] = $bill_details->records[0]->BillNumber;
                
                // $abcccc['data'] = $abcccc_data;
              
                // return response()->json(['success' => true, 'message' => 'Bill details', 'amount' => $bill_details->records[0]->Billamount, 
                // 'data' => $abcccc_data]);
            // }
            // else {
                // return response()->json(['success' => false, 'message' => 'Bill not fetch at the moment due to operator down!']);
            // }
            
            // $bill_details =  json_decode($Get_Response);
            
            if($bill_details->status==2) {
                return response()->json(['success' => true, 'message' => 'Bill details', 'amount' => $bill_details->amount, 'data' => $bill_details]);
            }
            else {
                return response()->json(['success' => false, 'message' => $bill_details->msg, 'amount' => $amount, 'data' => $bill_details]);
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function roundpayPayBill(Request $request) {
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
            $rpid = $request->get('rpid'); 
            
            $geo_code = $latitude.','.$longitude;
            $pincode = '560001';
            
            $user = User::find($user_id);
            
            $cbalance = $user->wallet;
            $fbalance = $user->wallet-$amount;
            
            $txn_id =  $this->apiManager->txnId("BP");
            
            $op_code = $request->get('op_code');
            $bill_number = $request->get("bill_number");
            $customer_mobile = $request->get('customer_mobile');
            
            $operator = Operators::where('op_code',$op_code)->first(); 
            
            if(!$operator) {
                return response()->json(['success' => false, 'message' => 'Operator not found']);
            }
            
            if($cbalance < $amount){
                return response()->json(['success' => false, 'message' => 'Balance is low.']);
            }
            
            if($cbalance < $user->node_balance) {
                return response()->json(['success' => false, 'user_status'=> 1, 
                'message' => 'Mini. Wallet Balance 1000 Rs. Required For Recharge and BBPS services. For more information contact admin.', 
                'transaction_id'=>$txn_id, 'date'=>date("h:i:s A, F d Y") ]);
            }
            
            $fee_type = $operator->commission_type;
            if($fee_type == 'flat'){
                $fee = $operator->commission;
            }else{
                $fee = $amount * $operator->commission / 100;
            }

            $transaction = new Transactions();
            $transaction->user_id = $user_id;
            $transaction->transaction_id = $txn_id;
            $transaction->referenceId = $rpid;
            $transaction->amount = $amount;
            $transaction->event = $event;
            $transaction->mobile = $bill_number;
            $transaction->status = 0;
            $transaction->op_id = $operator->id;
            $transaction->op_code = $op_code;
            $transaction->commission = 0;
            $transaction->current_balance = 0;
            $transaction->final_balance = 0;
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
            
            $operator_code = $operator->op_code_rp;
            
            $params = [
                "UserID" => env('ROUNDPAY_UID'),
                "Token" => env('ROUNDPAY_TOKEN'),
                "Account" => $bill_number,
                "Amount" => $amount,
                "SPKey" => $operator_code,
                "APIRequestID" => $txn_id,
                "RefID" => $rpid,
                "Optional1" => $optional1,
                "Optional2" => $optional2,
                "GEOCode" => $geo_code,
                "Pincode" => $pincode,
                "Format" => 1
            ];
            
            $param_string = http_build_query($params);
  
            $api_url = "https://roundpay.net/API/TransactionAPI?".$param_string;
       
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $Get_Response = curl_exec($ch);
            curl_close($ch);
            
            $bill_details =  json_decode($Get_Response);
            
            $response_table = new ResponseTable();
            $response_table->request = $api_url;
            $response_table->response = $Get_Response;
            $response_table->api_name = 'ROUNDPAYBILLPAYMENT';
            $response_table->save();
                        
            if($bill_details->status==1) {
                // 1=pending
                
                $txn = Transactions::find($transaction->id);
                $txn->referenceId = $bill_details->rpid;
                $txn->commission = $fee;
                $txn->current_balance = $cbalance;
                $txn->final_balance = $fbalance + $fee;
                $txn->status = 0; //pending
                $txn->txn_type = 'Debit';
                $txn->save();
                
                $user_u = User::find($user_id);
                $user_u->wallet = $fbalance + $fee;
                $user_u->save();
                
                return response()->json(['success' => true, 'message' => 'Payment status under process', 'amount' => $amount, 'data' => $transaction]);
            }
            elseif($bill_details->status==2) {
                // 2=success
                
                $txn = Transactions::find($transaction->id);
                $txn->referenceId = $bill_details->rpid;
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
            else {
                
                $txn = Transactions::find($transaction->id);
                $txn->status = 3;
                $txn->save();
                
                if(isset($bill_details->errorcode)) {
                    
                    if($bill_details->errorcode == '128')
                        return response()->json(['success' => false, 'message' => 'Transaction failed, Due to Operator down!', 'amount' => $amount, 'data' => $txn]);
                    else
                        return response()->json(['success' => false, 'message' => $bill_details->msg, 'amount' => $amount, 'data' => $txn]);
                }else{
                    return response()->json(['success' => false, 'message' => $bill_details->msg, 'amount' => $amount, 'data' => $txn]);
                }
                
                
            }
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function roundpayCallback(Request $request) {
        
        $response_table = new ResponseTable();
        $response_table->response = json_encode($_GET);
        $response_table->api_name = 'ROUNDPAY_CALLBACK'; //ROUNDPAYCALLBACK
        $response_table->save();
        
        $txn = $request->AGENTID;
        $status = $request->STATUS;
        $opid = $request->TRANID;
        $ref = $request->TRANID;
        
        $trans = Transactions::where('transaction_id',$txn)->where('status',0)->first();
        
        if(!$trans) {
            return response()->json(['success' => false, 'message' => 'Transaction not found!']);
        }
        
        $op_name = Operators::where('op_code',$trans->op_code)->first();
        $user_id = $trans->user_id; 
        $amount = $trans->amount; 
        $mobile = $trans->mobile;
        $operator_name = $op_name->name;
        
        if($status == '2'){
    
            $updatetrans = Transactions::limit(1)->find($trans->id);
            $updatetrans->status = 1;
            $updatetrans->referenceId = $ref;
            $updatetrans->op_id = $opid;
            $updatetrans->save();
            
            $opcomm = $this->apiManager->addOpCommission($trans->transaction_id, $op_name->id);
          
            //success
            //notification and sms
            $title = 'Recharge/Bill';
            $message = "Rs. ".$amount." successfully paid for ".$operator_name." for Mobile Number/Consumer Number ".$mobile." TXN:".$trans->transaction_id;
            $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
            $sendsms = $this->apiManager->sendSMS($user_id,$message);
            
            // $user_mobile = User::find($user_id);
            // $params = ['From' => env('SMS_FROM'), 'To' => $user_mobile->mobile, 'TemplateName' => 'FiniRechargeSuccess', 'VAR1' => $amount, 'VAR2' => $operator_name, 'VAR3' => $operator_name, 'VAR4' => $trans->transaction_id ];
            // $send_sms = $this->apiManager->sendSMS($user_mobile->mobile,$params);
            
        }elseif($status == '0'){
    
        }
        else
        {
            $trans = Transactions::where('transaction_id',$txn)->first();
            $sts = $trans->status;
            $updatetrans = Transactions::limit(1)->find($trans->id);
            $updatetrans->status = 2;
            $updatetrans->referenceId = $ref;
            $updatetrans->op_id = $opid;
            $updatetrans->save();
            
            if($sts == 0 || $sts == 1){
                $refund =  $this->apiManager->refundamount($txn);
            }
            
            //failed
            //notification and sms
            $title = 'Recharge/Bill';
            $message = "Recharge/Bill of ".$operator_name." Mobile ".$mobile." for Rs. ".$amount." has failed. We have successfully initiated a refund of Rs. ".$amount." to your wallet.";
            $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
            $sendsms = $this->apiManager->sendSMS($user_id,$message);
            
            // $user_mobile = User::find($user_id);
            // $params = ['From' => env('SMS_FROM'), 'To' => $user_mobile->mobile, 'TemplateName' => '', 'VAR1' => $operator_name, 'VAR2' => $mobile, 'VAR3' => $amount, 'VAR4' => $amount];
            // $send_sms = $this->apiManager->sendSMS($user_mobile->mobile,$params);
        }
        return response()->json(['success' => true,'message' => ' Recharge Is done.']);
    } 
    
    public function vastwebindiaCallback(Request $request) {
        
        $response_table = new ResponseTable();
        $response_table->response = json_encode($_GET);
        $response_table->api_name = 'VASTWEB_CALLBACK'; //ROUNDPAYCALLBACK
        $response_table->save();
        
        $txn = $request->rchid;
        $status = $request->Status;
        $opid = $request->operatorid;
        $ref = $request->operatorid;
        
        $trans = Transactions::where('transaction_id',$txn)->where('status',0)->first();
        
        if(!$trans) {
            return response()->json(['success' => false, 'message' => 'Transaction not found!']);
        }
        
        $op_name = Operators::where('op_code',$trans->op_code)->first();
        $user_id = $trans->user_id; 
        $amount = $trans->amount; 
        $mobile = $trans->mobile;
        $operator_name = $op_name->name;
        
        if($status == 'SUCCESS'){
    
            $updatetrans = Transactions::limit(1)->find($trans->id);
            $updatetrans->status = 1;
            $updatetrans->referenceId = $ref;
            $updatetrans->op_id = $opid;
            $updatetrans->save();
            
            $opcomm = $this->apiManager->addOpCommission($trans->transaction_id, $op_name->id);
          
            //success
            //notification and sms
            $title = 'Recharge/Bill';
            $message = "Rs. ".$amount." successfully paid for ".$operator_name." for Mobile Number/Consumer Number ".$mobile." TXN:".$trans->transaction_id;
            $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
            $sendsms = $this->apiManager->sendSMS($user_id,$message);
            
            // $user_mobile = User::find($user_id);
            // $params = ['From' => env('SMS_FROM'), 'To' => $user_mobile->mobile, 'TemplateName' => 'FiniRechargeSuccess', 'VAR1' => $amount, 'VAR2' => $operator_name, 'VAR3' => $operator_name, 'VAR4' => $trans->transaction_id ];
            // $send_sms = $this->apiManager->sendSMS($user_mobile->mobile,$params);
            
        }elseif($status == '0'){
    
        }
        else
        {
            $trans = Transactions::where('transaction_id',$txn)->first();
            $sts = $trans->status;
            $updatetrans = Transactions::limit(1)->find($trans->id);
            $updatetrans->status = 2;
            $updatetrans->referenceId = $ref;
            $updatetrans->op_id = $opid;
            $updatetrans->save();
            
            if($sts == 0 || $sts == 1){
                $refund =  $this->apiManager->refundamount($txn);
            }
            
            //failed
            //notification and sms
            $title = 'Recharge/Bill';
            $message = "Recharge/Bill of ".$operator_name." Mobile ".$mobile." for Rs. ".$amount." has failed. We have successfully initiated a refund of Rs. ".$amount." to your wallet.";
            $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
            $sendsms = $this->apiManager->sendSMS($user_id,$message);
            
            // $user_mobile = User::find($user_id);
            // $params = ['From' => env('SMS_FROM'), 'To' => $user_mobile->mobile, 'TemplateName' => '', 'VAR1' => $operator_name, 'VAR2' => $mobile, 'VAR3' => $amount, 'VAR4' => $amount];
            // $send_sms = $this->apiManager->sendSMS($user_mobile->mobile,$params);
        }
        return response()->json(['success' => true,'message' => ' Recharge Is done.']);
    }
    
    
    
    
}