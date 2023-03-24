<?php

namespace App\Http\Controllers;
use App\Classes\ApiManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

use Excel;

use Auth;
use DataTables;
use App\User;
use App\Model\KycDoc;
use App\Model\BankDetail;
use App\Model\UsersMapping;
use App\Model\MoneyRequest;
use App\Model\Transactions;


class MoneyRequestController extends Controller
{
    public function __construct(ApiManager $apiManager) {
        $this->middleware('auth');
        $this->apiManager = $apiManager;
    }
    
    //add_bank_details
    public function getAddBankDetails() {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        
        if($user_type == 3) {
            $kycdocs = kycdoc::where('user_id',$user_id)->where('status',1)->first();
            if(!$kycdocs) {
                Session::flash('error', 'Please verify your kyc first');
                return redirect()->back();
            }
        }
        
        return view('pages.comman.bank_details.add_bank_detail');
    }
    
    public function postAddBankDetails(Request $request) {
        $user_id = Auth::user()->id;
        
        $bank = new BankDetail();
        $bank->user_id = $user_id;
        $bank->bank_name = trim($request->get('bank_name'));
        $bank->bank_ifsc = trim($request->get('bank_ifsc'));
        $bank->acc = trim($request->get('acc'));
        $bank->acc_type = trim($request->get('acc_type'));
        $bank->bank_branch  = trim($request->get('bank_branch'));  
        $bank->bank_address = trim($request->get('bank_address'));
        $bank->bank_city = trim($request->get('bank_city'));
        $bank->bank_district = trim($request->get('bank_district'));
        $bank->bank_state = trim($request->get('bank_state'));
        
        if($request->hasFile('img')) {
            $img = $request->file('img');
            $image_name = 'IMG'.time() . '.' . $img->getClientOriginalExtension();
            $img->move(public_path('uploads/banks/'), $image_name);
            $bank->img =  $image_name;
        }
        
        $bank->save();

        if($bank) {
            Session::flash('success', 'Bank added successfully');
            return redirect()->back();
        }
        else {
            Session::flash('error', 'Something went wrong!');
            return redirect()->back();
        }
    }

    public function getBankDetails(Request $request) {
        return view('pages.comman.bank_details.manage_bank_details');
    }
    
    public function getBankDetailsData(Request $request) {
        $user_id = Auth::user()->id;
        $banks = BankDetail::select('*')->where('status',1)->where('user_id',$user_id)->get();
        return Datatables::of($banks)->make(true);
    }
    
    //add_money
    public function getAddMoney() {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        
        if($user_type == 3 || $user_type == 4) {
            $kycdocs = kycdoc::where('user_id',$user_id)->where('status',1)->first();
            if(!$kycdocs) {
                Session::flash('error', 'Please verify your kyc first');
                return redirect()->back();
            }
        }
        
        $banks = BankDetail::where('user_id',1)->get();
        
        if($user_type == 4) { // super distributors
            $usermapping = UsersMapping::where('user_id',$user_id)->first();
            if($usermapping)
                $banks = BankDetail::where('user_id',$usermapping->toplevel_id)->get();
            else 
                $banks;
        }   
        elseif($user_type == 3) { // distributors
            $usermapping = UsersMapping::where('user_id',$user_id)->first();
            if($usermapping)
                $banks = BankDetail::where('user_id',$usermapping->toplevel_id)->get();
            else 
                $banks;
        }
        elseif($user_type == 2) { // retailers
            $usermapping = UsersMapping::where('user_id',$user_id)->first();
            if($usermapping)
                $banks = BankDetail::where('user_id',$usermapping->toplevel_id)->get();
            else 
                $banks;
        }
        else {
            $banks;
        }
       
        return view('pages.comman.money_requests.add_money',compact('banks'));
    }
    
    public function postAddMoney(Request $request) {
        
        $user_id = Auth::user()->id;
        
        $amount = $request->get("amount");
        $transfer_type = $request->get("transfer_type");
        $bank_id = $request->get("bank");
        $bank_ref = $request->get("bank_ref");
            
        $user = User::where('id',$user_id)->first();
            
        if($amount > 0) {
            
            $check_entry = MoneyRequest::where('bank_id',$bank_id)->where('bank_ref',$bank_ref)->whereIn('status',[1,0])->first();
            
            // if($check_entry) {
            //     Session::flash('error', 'You can not request with same bank and refrence id!');
            //     return redirect()->back(); 
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
                
            if($money_request) {
                Session::flash('success', 'Money request sent');
            }
            else {
                Session::flash('error', 'Something went wrong!');
            }
            
            return redirect()->back(); 
        }
    }
    
    public function getAddMoneyReport(Request $request) {
        return view('pages.comman.money_requests.add_money_report');
    }
    
    public function getAddMoneyReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
        $query = User::query();
        
        if($request->get('txn_status')) {
            if($request->get('txn_status') == 'P') {  $stat = 0; }
            $query->where('money_requests.status',$request->get('txn_status'));
        }
        
        $query->whereBetween('money_requests.created_at', array($from, $to));
        
        $data = $query->select('users.*','money_requests.*',
        'money_requests.created_at','bank_details.bank_name','money_requests.status as money_requests_status')
        ->join('money_requests','money_requests.user_id','users.id')
        ->leftjoin('bank_details','bank_details.id','money_requests.bank_id')
        ->where('money_requests.user_id',$user_id)->get();
       
        $total_success_sum = collect($data)->sum('amount');
       
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
    }

    //money_requests_retailers
    public function getMoneyRequests() {
        $requests_for = "mapping_retailers";
        return view('pages.comman.money_requests.money_request',compact('requests_for'));
    }
    
    public function getSuperDistributorMoneyRequests() {
        $requests_for = "super_distributors";
        return view('pages.comman.money_requests.money_request',compact('requests_for'));
    }
    
    public function getSDDistributorMoneyRequests() {
        $requests_for = "sd_distributors";
        return view('pages.comman.money_requests.money_request',compact('requests_for'));
    }

    public function getDistributorMoneyRequests() {
        $requests_for = "distributors";
        return view('pages.comman.money_requests.money_request',compact('requests_for'));
    }
    
    public function getRetailerMoneyRequests() {
        $requests_for = "retailers";
        return view('pages.comman.money_requests.money_request',compact('requests_for'));
    }
    
    public function getMoneyRequestsData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
        $requests_for = $request->get('requests_for');
        
        $query = User::query();
        
        // $query->whereBetween('money_requests.created_at', array($from, $to));
        
        if($user_type == 1 && $requests_for == 'super_distributors') { // Admin (Super Distributor money requests)
            $data = $query->select('users.*','money_requests.*',
            'money_requests.created_at','bank_details.bank_name','money_requests.status as money_requests_status')
            ->join('users_mappings','users_mappings.user_id','users.id')
            ->join('money_requests','money_requests.user_id','users.id')
            ->join('bank_details','bank_details.id','money_requests.bank_id')
            ->where('users_mappings.toplevel_id',$user_id)
            ->where('users.user_type','4')
            ->where('money_requests.status','0')
            ->get();
        }
        elseif($user_type == 1 && $requests_for == 'distributors') { // Admin (Distributor money requests)
            $data = $query->select('users.*','money_requests.*',
            'money_requests.created_at','bank_details.bank_name','money_requests.status as money_requests_status')
            ->join('users_mappings','users_mappings.user_id','users.id')
            ->join('money_requests','money_requests.user_id','users.id')
            ->join('bank_details','bank_details.id','money_requests.bank_id')
            ->where('users_mappings.toplevel_id',$user_id)
            ->where('users.user_type','3')
            ->where('money_requests.status','0')
            ->get();
        }
        elseif($user_type == 1 && $requests_for == 'retailers') { //Admin Admin (Retailers money requests)
            $data = $query->select('users.*','money_requests.*',
            'money_requests.created_at','bank_details.bank_name','money_requests.status as money_requests_status')
            // ->leftjoin('users_mappings','users_mappings.user_id','users.id')
            ->join('money_requests','money_requests.user_id','users.id')
            ->join('bank_details','bank_details.id','money_requests.bank_id')
            // ->whereNull('users_mappings.toplevel_id')
            ->where('users.user_type','2')
            ->where('money_requests.status','0')
            ->get();
        }
        elseif($user_type == 4 && $requests_for == 'sd_distributors') { // Super distributors (Distributor money requests)
            $data = $query->select('users.*','money_requests.*',
            'money_requests.created_at','bank_details.bank_name','money_requests.status as money_requests_status')
            ->join('users_mappings','users_mappings.user_id','users.id')
            ->join('money_requests','money_requests.user_id','users.id')
            ->join('bank_details','bank_details.id','money_requests.bank_id')
            ->where('users_mappings.toplevel_id',$user_id)
            ->where('users.user_type','3')
            ->where('money_requests.status','0')
            ->get();
        }
        elseif($user_type == 3) { // Distributor (Retailers money requests)
            $data = $query->select('users.*','money_requests.*',
            'money_requests.created_at','bank_details.bank_name','money_requests.status as money_requests_status')
            ->join('users_mappings','users_mappings.user_id','users.id')
            ->join('money_requests','money_requests.user_id','users.id')
            ->join('bank_details','bank_details.id','money_requests.bank_id')
            ->where('users_mappings.toplevel_id',$user_id)
            ->where('users.user_type','2')
            ->where('money_requests.status','0')
            ->get();
        }
        else 
        {
            $data = [];
        }
        
        $total_success_sum = collect($data)->sum('amount');
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
    }
    
    public function moneyReuquestChangeStatus(Request $request) {
    
        $login_id = Auth::User()->id;
        $login_user = User::find($login_id);
        
        $login_type = $login_user->user_type;
        $login_wallet = $login_user->wallet;
        
        $status = $request->get('status'); 
        $action_id = $request->get('action_id');
        
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
    }
    
    public function getMoneyRequestsReport() {
        $requests_for = "mapping_retailers";
        return view('pages.comman.money_requests.money_request_report',compact('requests_for'));
    }
    
    public function getSuperDistributorMoneyRequestsReport() {
        $requests_for = "super_distributors";
        return view('pages.comman.money_requests.money_request_report',compact('requests_for'));
    }
    
    public function getSDDistributorMoneyRequestsReport() {
        $requests_for = "sd_distributors";
        return view('pages.comman.money_requests.money_request_report',compact('requests_for'));
    }
    
    public function getDistributorMoneyRequestsReport() {
        $requests_for = "distributors";
        return view('pages.comman.money_requests.money_request_report',compact('requests_for'));
    }
    
    public function getMoneyRetailerRequestsReport() {
        $requests_for = "retailers";
        return view('pages.comman.money_requests.money_request_report',compact('requests_for'));
    }
    
    public function getMappedMoneyRetailerRequestsReport() {
        $requests_for = "mapped_retailers";
        return view('pages.comman.money_requests.money_request_report',compact('requests_for'));
    }
    
    public function getMoneyRequestsReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
        $requests_for = $request->get('requests_for');
        
        $query = User::query();
        
        $query->whereBetween('money_requests.created_at', array($from, $to));
        
        if($request->get('txn_status')) {
            if($request->get('txn_status') == 'P') 
                $stat = 0; 
            else
                $stat = $request->get('txn_status'); 
                
            $query->where('money_requests.status',$stat);
        }
        
        if($user_type == 1 && $requests_for == 'super_distributors') { //Admin
            $data = $query->select('users.*','money_requests.*',
            'money_requests.created_at','bank_details.bank_name','money_requests.status as money_requests_status')
            ->join('users_mappings','users_mappings.user_id','users.id')
            ->join('money_requests','money_requests.user_id','users.id')
            ->join('bank_details','bank_details.id','money_requests.bank_id')
            ->where('users_mappings.toplevel_id',$user_id)
            ->where('users.user_type','4')
            ->get();
        }
        elseif($user_type == 1 && $requests_for == 'distributors') { //Admin
            $data = $query->select('users.*','money_requests.*',
            'money_requests.created_at','bank_details.bank_name','money_requests.status as money_requests_status')
            ->join('users_mappings','users_mappings.user_id','users.id')
            ->join('money_requests','money_requests.user_id','users.id')
            ->join('bank_details','bank_details.id','money_requests.bank_id')
            ->where('users_mappings.toplevel_id',$user_id)
            ->where('users.user_type','3')
            ->get();
        }
        elseif($user_type == 1 && $requests_for == 'retailers') { //Admin
            $data = $query->select('users.*','money_requests.*',
            'money_requests.created_at','bank_details.bank_name','money_requests.status as money_requests_status')
            ->leftjoin('users_mappings','users_mappings.user_id','users.id')
            ->join('money_requests','money_requests.user_id','users.id')
            ->join('bank_details','bank_details.id','money_requests.bank_id')
            ->whereNull('users_mappings.toplevel_id')
            ->where('users.user_type','2')
            ->get();
        }
        elseif($user_type == 1 && $requests_for == 'mapped_retailers') { //Admin
            $data = $query->select('users.*','money_requests.*',
            'money_requests.created_at','bank_details.bank_name','money_requests.status as money_requests_status')
            ->leftjoin('users_mappings','users_mappings.user_id','users.id')
            ->join('money_requests','money_requests.user_id','users.id')
            ->join('bank_details','bank_details.id','money_requests.bank_id')
            ->whereNotNull('users_mappings.toplevel_id')
            ->where('users.user_type','2')
            ->get();
        }
        elseif($user_type == 4 && $requests_for == 'sd_distributors') { //Admin
            $data = $query->select('users.*','money_requests.*',
            'money_requests.created_at','bank_details.bank_name','money_requests.status as money_requests_status')
            ->join('users_mappings','users_mappings.user_id','users.id')
            ->join('money_requests','money_requests.user_id','users.id')
            ->join('bank_details','bank_details.id','money_requests.bank_id')
            ->where('users_mappings.toplevel_id',$user_id)
            ->where('users.user_type','3')
            ->get();
        }
        elseif($user_type == 3) { //distributors
            $data = $query->select('users.*','money_requests.*',
            'money_requests.created_at','bank_details.bank_name','money_requests.status as money_requests_status')
            ->join('users_mappings','users_mappings.user_id','users.id')
            ->join('money_requests','money_requests.user_id','users.id')
            ->join('bank_details','bank_details.id','money_requests.bank_id')
            ->where('users_mappings.toplevel_id',$user_id)
            ->where('users.user_type','2')
            ->get();
        }
        else {
            $data = [];
        }
        
        $total_success_sum = collect($data)->sum('amount');
       
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
    }

    

}