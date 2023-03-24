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

use App\Model\BbpsBiller;
use App\Model\BbpsCategory;
use App\Model\BbpsBillersParam;

class SoftcareBbpsController extends Controller
{
    public function __construct(ApiManager $apiManager) {
        $this->apiManager = $apiManager;
    }
    
    public function getBbpsCategories(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response) {
            $data = BbpsCategory::select('id','category')->where('status',1)->get();
            return response()->json(['success' => true, 'message' => 'Categories list' , 'data' => $data]);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function getBbpsBillers(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        $category_id = $request->get("category_id");
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response) {
            
            $data = BbpsBiller::select('id','biller_id','biller_name')->where('category_id',$category_id)
            ->where('status',1)->orderBy('biller_name')->get();
            
            return response()->json(['success' => true, 'message' => 'Biller list','total'=>count($data) , 'data' => $data]);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function getBbpsBillerParams(Request $request) {
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        $biller_id = $request->get("biller_id");
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response) {
            $data = BbpsBillersParam::select('id','field_name','reg_exp','display_value','required')->where('bbps_biller_id',$biller_id)->get();
            return response()->json(['success' => true, 'message' => 'Biller parameters','total'=>count($data) , 'data' => $data]);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function getBbpsBillfetch(Request $request) {
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        $biller_id = $request->get("biller_id");
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response) {
            
            // https://apis.softcareinfotech.in/NPCI_BBPS/BillFetch?tokenkey=token_key&knumber=123456789011&billerid=CESC00000KOL01&agentcode=ABC123
            
            // https://apis.softcareinfotech.in/NPCI_BBPS/BillFetch?tokenkey=token_key&knumber=87203124134&billerid=PGVCL0000GUJ01&agentcode=ABC123
            
            $knumber = $request->get('customer_number');
            $billerid = $request->get('biller_id');
            
            $params = 'knumber='.$knumber.'&billerid='.$billerid;
            
            $url = "https://apis.softcareinfotech.in/NPCI_BBPS/BillFetch";
            
            $api_response = $this->apiManager->softcareGetApiCall($url,$params); 
            
            $array_data = json_decode($api_response);
            
            // print_r($array_data);
            
            // exit;
            
            // {
            //     "success": true,
            //     "message": "Bill detail",
            //     "data": {
            //         "ref_id": "BPRE692026217653FN",
            //         "status": "SUCCESS",
            //         "data": {
            //             "biller_fetch_response": {},
            //             "biller_details": {
            //                 "biller_id": "TORR00000AHM02"
            //             },
            //             "bill_details": {
            //                 "more_info": [],
            //                 "amount": "580",
            //                 "Customer_Name": "I V DEVELOPERS",
            //                 "Bill_Date": null,
            //                 "Due_Date": "2021-01-15"
            //             }
            //         }
            //     }
            // }
            
            
            if(isset($array_data->ref_id)) {
                $res = $array_data->data->bill_details;
                $data = ['ref_id'=> $array_data->ref_id, 'amount' => $res->amount, 'customer_name' => $res->Customer_Name, 'due_date' => $res->Due_Date ];
                return response()->json(['success' => true, 'message' => 'Bill detail', 'data' => $data]);
            }
            else{
                return response()->json(['success' => false, 'message' => $array_data->Message, 'api_reponse' => $array_data ]);
            }

            
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    public function getBbpsPaybill(Request $request) {
        
        exit;
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        $biller_id = $request->get("biller_id");
        
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response) {
            
            $knumber = $request->get('customer_number');
            $billerid = $request->get('biller_id');
            $refid = $request->get('refid');
            $amount = $request->get('amount');
            
            $user = User::find($user_id);
            
            $cbalance = $user->wallet;
            $fbalance = $user->wallet-$amount;
            
            $transaction_id =  $this->apiManager->txnId("BP");
            $op_code = "BBPS";
            
            $operator = Operators::where('op_code',$op_code)->first(); 
            
            if(!$operator) {
                return response()->json(['success' => false, 'message' => 'Operator not found']);
            }
            
            if($cbalance < $amount) {
                return response()->json(['success' => false, 'message' => 'Balance is low.']);
            }
            
            if($cbalance < $user->node_balance) {
                return response()->json(['success' => false, 'user_status'=> 1, 
                'message' => 'Mini. Wallet Balance 1000 Rs. Required For Recharge & BBPS services. For more information contact admin.', 
                'transaction_id'=> $transaction_id, 'date'=>date("h:i:s A, F d Y") ]);
            }
            
            $fee_type = $operator->commission_type;
            if($fee_type == 'flat'){
                $fee = $operator->commission;
            }else{
                $fee = $amount * $operator->commission / 100;
            }

            $transaction = new Transactions();
            $transaction->user_id = $user_id;
            $transaction->transaction_id = $transaction_id;
            $transaction->referenceId = $refid;
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
            
            
            
            
            
            

            $params = 'knumber='.$knumber.'&billerid='.$billerid.'&amount='.$amount."&refid=".$refid;
            
            $url = "https://apis.softcareinfotech.in/NPCI_BBPS/BillPay";
            
            $api_response = $this->apiManager->softcareGetApiCall($url,$params); 
            
            $response_table = new ResponseTable();
            $response_table->request = $url.$params;
            $response_table->response = $api_response;
            $response_table->api_name = 'SOFTCARE_BBPS_BILLPAY';
            $response_table->save();
            
            $array_data = json_decode($api_response);
            
            // print_r($array_data);
            // exit;
            
            // if($bill_details->status==1) {
            //     // 1=pending
                
            //     $txn = Transactions::find($transaction->id);
            //     $txn->referenceId = $bill_details->rpid;
            //     $txn->commission = $fee;
            //     $txn->current_balance = $cbalance;
            //     $txn->final_balance = $fbalance + $fee;
            //     $txn->status = 0; //pending
            //     $txn->txn_type = 'Debit';
            //     $txn->save();
                
            //     $user_u = User::find($user_id);
            //     $user_u->wallet = $fbalance + $fee;
            //     $user_u->save();
                
            //     return response()->json(['success' => true, 'message' => 'Payment status under process', 'amount' => $amount, 'data' => $transaction]);
            // }
            // elseif($bill_details->status==2) {
            //     // 2=success
                
            //     $txn = Transactions::find($transaction->id);
            //     $txn->referenceId = $bill_details->rpid;
            //     $txn->commission = $fee;
            //     $txn->current_balance = $cbalance;
            //     $txn->final_balance = $fbalance + $fee;
            //     $txn->status = 1;
            //     $txn->txn_type = 'Debit';
            //     $txn->save();
                
            //     $user_u = User::find($user_id);
            //     $user_u->wallet = $fbalance + $fee;
            //     $user_u->save();
                
            //     $opcomm = $this->apiManager->addOpCommission($txn_id, $operator->id);
                
            //     return response()->json(['success' => true, 'message' => 'Payment done', 'amount' => $amount, 'data' => $txn]);
            // }
            // else {
                
            //     $txn = Transactions::find($transaction->id);
            //     $txn->status = 3;
            //     $txn->save();
                
            //     if(isset($bill_details->errorcode)) {
                    
            //         if($bill_details->errorcode == '128')
            //             return response()->json(['success' => false, 'message' => 'Transaction failed, Due to Operator down!', 'amount' => $amount, 'data' => $txn]);
            //         else
            //             return response()->json(['success' => false, 'message' => $bill_details->msg, 'amount' => $amount, 'data' => $txn]);
            //     }else{
            //         return response()->json(['success' => false, 'message' => $bill_details->msg, 'amount' => $amount, 'data' => $txn]);
            //     }
                
                
            // }
            
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    
    
    
}