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
use App\Model\Operators2;
use App\Model\GoogleCode;

class ServiceApiController extends Controller
{
    public function __construct(ApiManager $apiManager) {
        $this->apiManager = $apiManager;
    }
    
    public function getOperators(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");
        $service_id = $request->get("service_id");
        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        if($response) {
            
            if($service_id == '0') {
                $baseurl = env('APP_URL').'/uploads/operators/';
                $image = \DB::raw("CONCAT('$baseurl',op_image) AS op_image");
                $operators = Operators::select('*',$image)->where('status','1')->whereNotNull('commission_type')->orderBy('name')->get();
            }
            else 
            {
                $baseurl = env('APP_URL').'/uploads/operators/';
                $image = \DB::raw("CONCAT('$baseurl',op_image) AS op_image");
                $operators = Operators::select('*',$image)->where('status','1')->where('service_id',$service_id)->whereNotNull('commission_type')->orderBy('name')->get();
            }
            
            if($operators)
                return response()->json(['success' => true, 'message' => 'Operators list' , 'data' => $operators]);
            else
                return response()->json(['success' => false, 'message' => 'Something went wrong!']);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!']);
        }
    }
    public function getRechargeRoffer(Request $request) {
        $op = $request->get('op');
        $tel = $request->get('tel');
        $a = "https://www.mplan.in/api/plans.php?apikey=".env('MPLANKEY')."&offer=roffer&tel=".$tel."&operator=".urlencode($op);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $a);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $Get_Response = curl_exec($ch);
        curl_close($ch);
        return $Get_Response;
    }
    public function getRechargePlans(Request $request) {
        
        $crcl = $request->get('crcl');
        $op = $request->get('op');
        
        if($crcl == 'Andhra Pradesh'){
            
            $crcl = 'Andhra Pradesh Telangana';
            
        }elseif($crcl == 'Bihar' || $crcl == 'Jharkhand'){
            
            $crcl = 'Bihar Jharkhand';
            
        }elseif($crcl == 'Delhi'){
            
            $crcl = 'Delhi NCR';
            
        }elseif($crcl == 'Jammu & Kashmir'){
            
            $crcl = 'Jammu Kashmir';
            
        }elseif($crcl == 'Madhya Pradesh' || $crcl == 'Chattisgarh'){
            
            $crcl = 'Madhya Pradesh Chhattisgarh';
            
        }elseif($crcl == 'Maharashtra' || $crcl == 'Goa'){
            
            $crcl = 'Maharashtra Goa';
            
        }elseif($crcl == 'Uttar Pradesh'){
            
            $crcl = 'UP East';
            
        }else{
            
        }
        
        $a = "https://www.mplan.in/api/plans.php?apikey=".env('MPLANKEY')."&cricle=".urlencode($crcl)."&operator=".urlencode($op);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $a);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $Get_Response = curl_exec($ch);
        curl_close($ch);
        return $Get_Response;
    }
    
    public function getDthInfo(Request $request) {
        
        $crcl = $request->number;
        $op = $request->op;
        if( $op == "Dish TV"){
            $op = "Dishtv";
        }elseif($op == "Airtel dth"){
            $op = "Airteldth";
        }elseif($op == "Tata Sky"){
            $op = "TataSky";
        }elseif($op == "Videocon"){
            $op = "Videocon";
        }elseif($op == "Sun Direct"){
            $op = "Sundirect";
        }
        $a = "https://www.mplan.in/api/Dthinfo.php?apikey=".env('MPLANKEY')."&offer=roffer&tel=".$crcl."&operator=".$op;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $a);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $Get_Response = curl_exec($ch);
        curl_close($ch);
        return $Get_Response;
    }
    
    public function getDthOffer(Request $request) {
        $op = $request->op;
        
        $a = "https://www.mplan.in/api/dthplans.php?apikey=".env('MPLANKEY')."&operator=".urlencode($op);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $a);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $Get_Response = curl_exec($ch);
        curl_close($ch);
        return $Get_Response;
    }
    
    public function rechargeService(Request $request) {
        
        $remitindiaapi = false;
        $instantpayapi = false;
        $roundpayapi = true;
        $adipay = false;
        $softcare = false;
        
        $user_id = $request->get("user_id");
        $usertoken = $request->get("user_token");
        $mobile = $request->get("mobile");
        $opcode = $request->get("opcode");
        $amount = $request->get("amount");
        $event = $request->get("event");
        $txn_pin = $request->get("txn_pin");
      
        $txn = $this->apiManager->txnId("RC");
        
        
        // return response()->json(['success' => false, 'message' => 'Verify your kyc first']);
        $check = User::where('id',$user_id)->where('user_token',$usertoken)->where('txn_pin',$txn_pin)->first();
        if($check){
          
            //check_service_status
            $service_status = User::where('id',$user_id)->where('recharge_service',0)->first();
            if($service_status){
                return response()->json(['success' => false, 'message' => 'Your Recharge service is disabled!']);
            }
        
            $getkyc = KycDoc::where('user_id',$user_id)->where('status',1)->first();
            if(!$getkyc) {
                return response()->json(['success' => false, 'message' => 'Verify your kyc first']);
            }

            $opdetail = Operators::where('op_code',$opcode)->first();
            $api = $opdetail->api_id;
            if($opdetail->api_id == 1){
                
                $opcode_api = $opdetail->op_code1;
            }elseif($opdetail->api_id == 2){
                
                $opcode_api = $opdetail->op_code_venfone;
            }elseif($opdetail->api_id == 3){
                
                $opcode_api = $opdetail->op_code_rp;
            }else{
                 return response()->json(['success' => false, 'message' => 'Operator Down!']);
            }
            
            
            $cbalance = $check->wallet;
            
            if($cbalance < $amount){
                return response()->json(['success' => false, 'user_status'=> 1, 'message' => 'Insufficient Balance.', 
                'transaction_id'=>$txn, 'date'=>date("h:i:s A, F d Y") ]);
            }
            
            // if($cbalance < $check->node_balance) {
            //     return response()->json(['success' => false, 'user_status'=> 1, 
            //     'message' => 'Mini. Wallet Balance 1000 Rs. Required For Recharge and BBPS services. For more information contact admin.', 
            //     'transaction_id'=>$txn, 'date'=>date("h:i:s A, F d Y") ]);
            // }
            
            // if($cbalance < 1000){
            //     if($user_id == 73 || $user_id == 108 || $user_id == 134){
                    
            //     }else{
            //         return response()->json(['success' => false, 'user_status'=> 1, 'message' => 'Mini. Wallet Balance 1000 Rs. Required For Recharge and BBPS services. For more information contact admin.', 
            //     'transaction_id'=>$txn, 'date'=>date("h:i:s A, F d Y") ]);
            //     }
                
            // }
            
            if($opdetail->commission_type == 'flat') {
                $commission = $opdetail->commission;
            }elseif($opdetail->commission_type == 'percentage') {
                $commission = $amount * $opdetail->commission / 100;
            }else{
                return response()->json(['success' => false, 'user_status' => 1, 'message' => 'Operator Not Working.', 
                'transaction_id'=>$txn,'date'=>date("h:i:s A, F d Y") ]);
            }
            
            $fbalance = $cbalance - $amount + $commission;

            $transaction = new Transactions();
            $transaction->user_id = $user_id;
            $transaction->transaction_id = $txn;
            $transaction->amount = $amount;
            $transaction->event = $event;
            $transaction->mobile = $mobile;
            $transaction->status = 0;
            $transaction->op_id = $opdetail->id;
            $transaction->op_code = $opcode;
            $transaction->commission = round($commission,5);
            $transaction->current_balance = round($cbalance,5);
            $transaction->final_balance = round($fbalance,5);
            $transaction->txn_type = 'Debit';
            $transaction->save();
            $tid = $transaction->id;

            $user = User::limit(1)->find($check->id);
            $user->wallet = round($fbalance,2);
            $user->save();
            
            if($api == 2){
                
                // if($opdetail->stv == 1){
                //     $stv = "op=*";
                // }else{
                //     $stv = "op=rtopup";
                // }
                
                // $strg = "api_token=ff82f18f-5701-47f5-90fc-b8fa20e66818&mobile_no=$mobile&amount=$amount&company_id=$opcode_api&order_id=$txn&is_stv=$stv";
                $url = "http://mofuse.co/API/TransactionAPI?UserID=1513&Token=da469da21c28fd80e2ae4cc5f2107004&Account=$mobile&Amount=$amount&SPKey=$opcode_api&APIRequestID=$txn&Optional1=&Optional2=&Optional3=&Optional4=&GEOCode=19.0760,72.8777&CustomerNumber=7013123112&Pincode=400004Format=1";
                // ini_set("allow_url_fopen", 1);
                // $callapi = file_get_contents($url);
                $curl = curl_init();

                curl_setopt_array($curl, array(
                  CURLOPT_URL => $url,
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'GET',
                ));
                
                $callapi = curl_exec($curl);
                
                curl_close($curl);

                $reponse_data = new ResponseTable();
                $reponse_data->response = $callapi;
                $reponse_data->api_name = 'mofuse';
                $reponse_data->request = $url;
                $reponse_data->save();
                
                // $callapi =  $this->apiManager->rechargeApiPostCall($mobile,$opcode,$amount,$txn);
                
                $output = json_decode($callapi);
                
                if(isset($output->STATUS)) {
                    
                    $status = $output->STATUS;
                    
                    if($status == '2') {
                        
                        $updatetrans = Transactions::limit(1)->find($tid);
                        $updatetrans->referenceId = $output->OPID;
                        $updatetrans->status = 1;
                        $updatetrans->save();
                    
                        $opcomm = $this->apiManager->addOpCommission($txn, $opdetail->id);
                    
                        return response()->json(['success' => true,'status'=>1,'message' => $opdetail->name.' Recharge is successfully done.', 'transaction_id'=>$txn,'date'=>date("h:i:s A, F d Y")]);
                    }
                    elseif($status == 'Failure') {
                        $refund =  $this->apiManager->refundAmount($txn);
                        return response()->json(['success' => true,'status'=>2,'message' => $opdetail->name.' Recharge is Failed.', 'transaction_id'=>$txn,'date'=>date("h:i:s A, F d Y")]);
                    }
                    else{
                        return response()->json(['success' => true,'status'=>0,'message' => $opdetail->name.' Recharge is Pending.', 'transaction_id'=>$txn,'date'=>date("h:i:s A, F d Y")]);
                    }
                }
                else {
                    
                    $refund =  $this->apiManager->refundAmount($txn);
                    return response()->json(['success' => true,'status'=>2,'message' => $opdetail->name.' Recharge is Failed.', 'transaction_id'=>$txn,'date'=>date("h:i:s A, F d Y")]);
                    
                    
                    // return response()->json(['success' => true, 'data' => $callapi]);    
                }
                // return response()->json(['success' => true, 'data' => $output]);
            }
            elseif($api == 1){
                return response()->json(['success' => true,'status'=>2,'message' => $opdetail->name.' Recharge is Failed.', 'transaction_id'=>$txn,'date'=>date("h:i:s A, F d Y")]);
                // if($opdetail->stv == 1){
                //     $stv = "op=*";
                // }else{
                //     $stv = "op=rtopup";
                // }
                
                // $strg = "api_token=ff82f18f-5701-47f5-90fc-b8fa20e66818&mobile_no=$mobile&amount=$amount&company_id=$opcode_api&order_id=$txn&is_stv=$stv";
                // $url = "http://venfoneapp.co.in/ws/$opcode_api?uid=waseembanglore@gmail.com&apitoken=fcaea38518f240e5af006015290be097&mn=$mobile&amt=$amount&reqid=$txn&$stv";
                // $url = "http://earnwayplus.money/Recharge/Recharge_Get?UserID=rumanrecharge@gmail.com&Customernumber=$mobile&Optcode=$opcode_api&Amount=$amount&Yourrchid=$txn&Tokenid=mXQpXVCW4SG+ulrN+8+Hiw==&optional1=&optional2=";
                $url = "https://www.vastwebindia.com/API/API/Recharge?Mobile=$mobile&OptCode=$opcode_api&Amount=$amount&Token=6bc9ede4-11a8-4edd-845c-55b4322a23fd&Userid=mustaq.ahemad59@gmail.com&rch_id=$txn";
                // ini_set("allow_url_fopen", 1);
                // $callapi = file_get_contents($url);
                $curl = curl_init();

                curl_setopt_array($curl, array(
                  CURLOPT_URL => $url,
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'GET',
                ));
                
                $callapi = curl_exec($curl);
                
                curl_close($curl);

                $reponse_data = new ResponseTable();
                $reponse_data->response = $callapi;
                $reponse_data->api_name = 'vastwebindia';
                $reponse_data->request = $url;
                $reponse_data->save();
                
                // $callapi =  $this->apiManager->rechargeApiPostCall($mobile,$opcode,$amount,$txn);
                
                $output = json_decode($callapi);
                
                if(isset($output->Status)) {
                    
                    $status = $output->Status;
                    
                    if($status == 'SUCCESS') {
                        
                        $updatetrans = Transactions::limit(1)->find($tid);
                        $updatetrans->referenceId = $output->Operatorid;
                        $updatetrans->status = 1;
                        $updatetrans->save();
                    
                        $opcomm = $this->apiManager->addOpCommission($txn, $opdetail->id);
                    
                        return response()->json(['success' => true,'status'=>1,'message' => $opdetail->name.' Recharge is successfully done.', 'transaction_id'=>$txn,'date'=>date("h:i:s A, F d Y")]);
                    }
                    elseif($status == 'FAILED') {
                        $refund =  $this->apiManager->refundAmount($txn);
                        return response()->json(['success' => true,'status'=>2,'message' => $opdetail->name.' Recharge is Failed.', 'transaction_id'=>$txn,'date'=>date("h:i:s A, F d Y")]);
                    }
                    else{
                        return response()->json(['success' => true,'status'=>0,'message' => $opdetail->name.' Recharge is Pending.', 'transaction_id'=>$txn,'date'=>date("h:i:s A, F d Y")]);
                    }
                }
                else {
                    
                    $refund =  $this->apiManager->refundAmount($txn);
                    return response()->json(['success' => true,'status'=>2,'message' => $opdetail->name.' Recharge is Failed.', 'transaction_id'=>$txn,'date'=>date("h:i:s A, F d Y")]);
                    
                    
                    // return response()->json(['success' => true, 'data' => $callapi]);    
                }
                // return response()->json(['success' => true, 'data' => $output]);
            }elseif($api == 3){
                return response()->json(['success' => true,'status'=>2,'message' => $opdetail->name.' Recharge is Failed.', 'transaction_id'=>$txn,'date'=>date("h:i:s A, F d Y")]);
                if($opdetail->stv == 1){
                    $stv = true;
                }else{
                    $stv = false;
                }
                $strg = "api_token=ea04be58-92ae-4db2-8f5a-afc7a0e01c34&mobile_no=$mobile&amount=$amount&company_id=$opcode_api&order_id=$txn&is_stv=$stv";
                $url = "https://mrobotics.in/api/recharge";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $strg);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $callapi = curl_exec ($ch);
                curl_close ($ch);
                // print_r($callapi);exit;
                //roundpay api start
                // $callapi =  $this->apiManager->roundpayApi($mobile,$opcode,$amount,$txn);
                $reponse_data = new ResponseTable();
                $reponse_data->response = $callapi;
                $reponse_data->api_name = 'MROBOTICS';
                $reponse_data->save();
                
                $output=json_decode($callapi);
                if($output->status == 'success') {
                    $updatetrans = Transactions::limit(1)->find($tid);
                    $updatetrans->referenceId = $output->tnx_id;
                    $updatetrans->status = 1;
                    $updatetrans->save();
                    
                    $opcomm = $this->apiManager->addOpCommission($txn, $opdetail->id);
                    
                    return response()->json(['success' => true,'status'=>1,'message' => $opdetail->name.' Recharge is successfully done.', 'transaction_id'=>$txn,'date'=>date("h:i:s A, F d Y")]);
                }elseif($output->status == 'failure' || $output->status == 4) {
                    $refund =  $this->apiManager->refundAmount($txn);
                    return response()->json(['success' => true,'status'=>2,'message' => $opdetail->name.' Recharge is Failed.', 'transaction_id'=>$txn,'date'=>date("h:i:s A, F d Y")]);
                }else {
                    return response()->json(['success' => true,'status'=>0,'message' => $opdetail->name.' Recharge is Pending.', 'transaction_id'=>$txn,'date'=>date("h:i:s A, F d Y")]);
                }
            
                //roundpay api end
            }
            else {
                $refund =  $this->apiManager->refundAmount($txn);
                return response()->json(['success' => true,'status'=>2,'message' => $opdetail->name.' Recharge is Failed.', 'transaction_id'=>$txn,'date'=>date("h:i:s A, F d Y")]);
            }
        }
        else {
            return response()->json(['success' => false, 'user_status'=>0,'message' => 'Unauthorized access!', 
            'transaction_id'=>$txn, 'date' => date("h:i:s A, F d Y")]);
        }
    }
    
    public function getServicesHistory(Request $request) {
        
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $event = $request->get('event');
        $report_for = $request->get("report_for");

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);

        $baseurl = env('APP_URL').'/uploads/operators/';

        if($response)
        {
            if($report_for == "1") //Todays transactions
            {
                $image = \DB::raw("CONCAT('$baseurl',op_image) AS op_image");
                $data = Transactions::select('transactions.id','transactions.mobile','transactions.transaction_id','transactions.referenceId',
                'transactions.status','transactions.created_at','operators.name as op_name',$image,
                \DB::raw('FORMAT((amount),2) as amount'),
                \DB::raw('FORMAT((current_balance),2) as current_balance'),
                \DB::raw('FORMAT((final_balance),2) as final_balance'),
                \DB::raw('FORMAT((transactions.commission),2) as commission'))
                ->leftjoin('operators','operators.op_code','transactions.op_code')
                ->where('transactions.user_id',$user_id)
                ->where('transactions.event',$event)
                ->whereDate('transactions.created_at', Carbon::today())
                ->orderBy('transactions.id','desc')
                ->get();
            }
            else if($report_for == "2") //last 5 transactions
            {
                $image = \DB::raw("CONCAT('$baseurl',op_image) AS op_image");
                $data = Transactions::select('transactions.id','transactions.mobile','transactions.transaction_id','transactions.referenceId',
                'transactions.status','transactions.created_at','operators.name as op_name',$image,
                \DB::raw('FORMAT((amount),2) as amount'),
                \DB::raw('FORMAT((current_balance),2) as current_balance'),
                \DB::raw('FORMAT((final_balance),2) as final_balance'),
                \DB::raw('FORMAT((transactions.commission),2) as commission'))
                ->leftjoin('operators','operators.op_code','transactions.op_code')
                ->where('transactions.user_id',$user_id)
                ->where('transactions.event',$event)
                ->orderBy('transactions.id','desc')->limit('5')->get();
            }
            else if($report_for == "3") //last 10 transactions
            {
                $image = \DB::raw("CONCAT('$baseurl',op_image) AS op_image");
                $data = Transactions::select('transactions.id','transactions.mobile','transactions.transaction_id','transactions.referenceId',
                'transactions.status','transactions.created_at','operators.name as op_name',$image,
                \DB::raw('FORMAT((amount),2) as amount'),
                \DB::raw('FORMAT((current_balance),2) as current_balance'),
                \DB::raw('FORMAT((final_balance),2) as final_balance'),
                \DB::raw('FORMAT((transactions.commission),2) as commission'))
                ->leftjoin('operators','operators.op_code','transactions.op_code')
                ->where('transactions.user_id',$user_id)
                ->where('transactions.event',$event)
                ->orderBy('transactions.id','desc')->limit('10')->get();
            }
            else if($report_for == "4") //Date range
            {   
                $from = $this->apiManager->fetchFromDate($request->start_date);
                $to = $this->apiManager->fetchToDate($request->end_date);

                $image = \DB::raw("CONCAT('$baseurl',op_image) AS op_image");
                $data = Transactions::select('transactions.id','transactions.mobile','transactions.transaction_id','transactions.referenceId',
                'transactions.status','transactions.created_at','operators.name as op_name',$image,
                \DB::raw('FORMAT((amount),2) as amount'),
                \DB::raw('FORMAT((current_balance),2) as current_balance'),
                \DB::raw('FORMAT((final_balance),2) as final_balance'),
                \DB::raw('FORMAT((tds),2) as tds'),
                \DB::raw('FORMAT((gst),2) as gst'),
                \DB::raw('FORMAT((transactions.commission),2) as commission'),
                \DB::raw('FORMAT((retailer_commission),2) as retailer_commission'))
                ->leftjoin('operators','operators.op_code','transactions.op_code')
                ->where('transactions.user_id',$user_id)
                ->where('transactions.event',$event)
                ->whereBetween('transactions.created_at', array($from, $to))
                ->orderBy('transactions.id','desc')->get();
            }

            if($data) {
                if(count($data) > 0)
                    return response()->json(['success' => true, 'message' => 'Services report' , 'data' => $data]);
                else
                    return response()->json(['success' => true, 'message' => 'History not found' , 'data' => $data]);
            }
            else
                return response()->json(['success' => false, 'message' => 'Something went wrong!', 'user_status'=>1]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!', 'user_status'=>0]);
        }
    }
    
    
    public function softcareRechargeCallback(Request $request) {
        
        $response_table = new ResponseTable();
        $response_table->response = $_GET;
        $response_table->api_name = 'RECHARGE_CALLBACK';
        $response_table->save();
        
        if(isset($_GET)) {
            
            $txn_id = $_GET['Ref_Id'];
            $status = $_GET['status'];
            // https://yourdomain.com/api/mobilerechapi.aspx?Ref_Id=Referance_ID&Status=Recharge_Status&Optional=Any_Other_Info_Can_Send_in_this_Parm
            
            
            
        }
        
        
    }
    
    public function googlepaycode(Request $request) {
        return response()->json(['success' => false, 'message' => 'Service Disable', 'user_status'=>0]);
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $event = $request->get('event');
        $amount = $request->get('amount');
        $mobile = $request->get('mobile');
        

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        
        if($response){
            $txn = $this->apiManager->txnId("GOC");
            $check = User::where('id',$user_id)->where('user_token',$usertoken)->first();
            
            $cbalance = $check->wallet;
            
            if($cbalance < $amount){
                return response()->json(['success' => false, 'user_status'=> 1, 'message' => 'Insufficient Balance.', 
                'transaction_id'=>$txn, 'date'=>date("h:i:s A, F d Y") ]);
            }
            
            $url = '';
            
            // $curl = curl_init();

            // curl_setopt_array($curl, array(
            //   CURLOPT_URL => 'http://earnway-lite.com/API/TransactionAPI?UserID=2048&Token=aa49b453aa62ec07c1fe00225e82f16b&Account='.$mobile.'&Amount='.$amount.'&SPKey=13&APIRequestID='.$txn.'&Optional1=&Optional2=&Optional3=&Optional4=&Format=1',
            //   CURLOPT_RETURNTRANSFER => true,
            //   CURLOPT_ENCODING => '',
            //   CURLOPT_MAXREDIRS => 10,
            //   CURLOPT_TIMEOUT => 0,
            //   CURLOPT_FOLLOWLOCATION => true,
            //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //   CURLOPT_CUSTOMREQUEST => 'GET',
            // ));
            
            // $response = curl_exec($curl);
            
            // curl_close($curl);
            // // print_r($response);
            // $reponse_data = new ResponseTable();
            // $reponse_data->response = $response;
            // $reponse_data->api_name = 'GOOGLECODE';
            // $reponse_data->request = '';
            // $reponse_data->save();
                
            // $data = json_decode($response);
            
            $gc_ck = GoogleCode::where('amount',$amount)->where('status',1)->first();
            
            if($gc_ck){
                
                $gc_ck_u = GoogleCode::find($gc_ck->id);
                $gc_ck_u->status = 2;
                $gc_ck_u->save();
            
                $fbalance = $cbalance - $amount;
                
                $transaction = new Transactions();
                $transaction->user_id = $user_id;
                $transaction->transaction_id = $txn;
                $transaction->amount = $amount;
                $transaction->event = $event;
                $transaction->mobile = $mobile;
                $transaction->status = 1;
                $transaction->op_id = 272;
                $transaction->op_code = 'GGLCOD';
                $transaction->commission = 0;
                $transaction->current_balance = round($cbalance,5);
                $transaction->final_balance = round($fbalance,5);
                $transaction->txn_type = 'NONE';
                $transaction->referenceId = $gc_ck->gcode;
                $transaction->save();
                
                $user = User::limit(1)->find($check->id);
                $user->wallet = round($fbalance,2);
                $user->save();
                
                return response()->json(['success' => true, 'message' => 'Successfully Done','code'=>$gc_ck->gcode]);
            }else{
                return response()->json(['success' => false, 'message' => 'Sorry Try again later.']);
            }
            
            
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!', 'user_status'=>0]);
        }
    }
    
    public function changePin(Request $request) {
        $user_id = $request->get('user_id');
        $usertoken = $request->get("user_token");

        $txn_pin = $request->get('txn_pin');
        

        $response = $this->apiManager->verifyUserToken($user_id,$usertoken);
        
        if($response){
            $user = User::limit(1)->find($user_id);
            $user->txn_pin = $txn_pin;
            $user->save();
            
            return response()->json(['success' => true, 'message' => 'Successfully Updated']);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized access!', 'user_status'=>0]);
        }
    }
    
}