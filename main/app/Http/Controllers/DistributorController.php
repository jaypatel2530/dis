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
use App\Model\Address; 
use App\Model\State;
use App\Model\UserBank;
use App\Model\UsersMapping;
use App\Model\UsersRelation;
use App\Model\Transactions;
use App\Model\RetailerIdsHistory;
use App\Model\UserMember;
use App\Model\Payment;
use App\Model\Category;

class DistributorController extends Controller
{
    public function __construct(ApiManager $apiManager) {
        $this->middleware('auth');
        $this->apiManager = $apiManager;
    }
    
    public function getAddRetailer() {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        
        $user = User::find($user_id);
        
        $kycdocs = kycdoc::where('user_id',$user_id)->where('status',1)->first();
        if(!$kycdocs) {
            Session::flash('error', 'Please verify your kyc first');
            return redirect()->back();
        }
        
        if($user->retailer_ids <= 0 && $user_type !=1) {
            Session::flash('error', 'Please purchase retailers registration ids');
            return redirect()->back();
        }
        
        $states = State::where('country_id',1)->get();
        $categories = Category::select('id','cat_name')->get();
        return view('pages.distributor_panel.retailers.add_retailer',compact('states','categories'));
    }
    
    public function postAddRetailer(Request $request) {
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        
       
        $distributor = User::find($user_id);
        
        $pre_stock = $distributor->retailer_ids;
        $cur_stock = $distributor->retailer_ids - 1;
        $retailer_ids = $pre_stock;
        if($retailer_ids <= 0 && $user_type !=1){
            Session::flash('error', 'Please purchase retailers registration ids');
            return redirect()->back();
        }
        
        $mobile = $request->get('mobile');
        $checkMobile = User::where('mobile', $mobile)->first();
        
        if($checkMobile) {
            Session::flash('error', 'Mobile number already in use!');
            return redirect()->back()->withInput();
        }
        
        $check_pan_num = KycDoc::where('pan_number',$request->get("pan_number"))->first();
        if($check_pan_num) {
            Session::flash('error', 'PAN number already in use!');
            return redirect()->back()->withInput();
        }
        
        $check_aadhaar_num = KycDoc::where('aadhaar_number',$request->get("aadhaar_number"))->first();
        if($check_aadhaar_num) {
            Session::flash('error', 'Aadhaar number already in use!');
            return redirect()->back()->withInput();
        }
        
        $usertoken = $this->apiManager->getUserToken();
            
        $user = new User();    
        $user->name = trim(ucfirst($request->name));
        $user->mobile = trim($request->mobile);
        $user->status = 1;
        $user->user_type = 2;
        $user->reg_completed = 1;
        $user->email = $request->get('email');
        $user->dob = $request->get('dob');
        $user->category_id = $request->get('category_id');
        $user->user_type = 2;
        $user->password = bcrypt($mobile);
        $user->user_token = $usertoken;
        $user->payment_status = 1;
        $user->save();
        
        $retailer_id = $user->id;
        
        $mapping = new UsersMapping();
        $mapping->user_id = $retailer_id;
        $mapping->toplevel_id = $user_id;
        $mapping->save();
        
        $has_super_distributor = UsersRelation::where('distributor_id',$user_id)->first();
        
        if($has_super_distributor) 
            $super_distributor_id = $has_super_distributor->super_distributor_id;
        else
            $super_distributor_id = NULL;
        
        $relation = new UsersRelation();
        $relation->retailer_id = $retailer_id;
        $relation->distributor_id = $user_id;
        $relation->super_distributor_id = $super_distributor_id;
        $relation->admin_id = 1;
        $relation->save();
        
        //Address
        $address = new Address();
        $address->user_id = $retailer_id;
        $address->address = $request->get('address');
        $address->city = $request->get('city');
        $address->state = $request->get('state');
        $address->pincode = $request->get('pincode');
        $address->latitude = $request->get('latitude');
        $address->longitude = $request->get('longitude');
        $address->save();
            
        //Bank
        $bank = new UserBank();
        $bank->user_id = $retailer_id;
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
        
        //KYC
        $kyc = new KycDoc();
        $kyc->user_id = $retailer_id;
        $kyc->pan_number = strtoupper($request->get('pan_number'));
        $kyc->aadhaar_number = $request->get('aadhaar_number');
        $kyc->status = 0;
        
        if ($request->hasFile('pan_image')) {
            $file = $request->file('pan_image');
            $destinationPath = public_path('/uploads/kycdocs/');
            $imagename = 'PAN'. $retailer_id . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $imagename);
            $kyc->pan_image = $imagename;
        }
        if ($request->hasFile('aadhaar_front_image')) {
            $file = $request->file('aadhaar_front_image');
            $destinationPath = public_path('/uploads/kycdocs/');
            $imagename = 'ADHARF'. $retailer_id . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $imagename);
            $kyc->aadhaar_front_image = $imagename;
        }
        if($request->hasFile('aadhaar_back_image')) {
            $file = $request->file('aadhaar_back_image');
            $destinationPath = public_path('/uploads/kycdocs/');
            $imagename = 'ADHARB'. $retailer_id . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $imagename);
            $kyc->aadhaar_back_image = $imagename;
        }
        $kyc->save();
        
        $distributor->retailer_ids = $cur_stock;
        $distributor->save();
        
        $history = new RetailerIdsHistory();
        $history->distributor_id = $user_id;
        $history->retailer_id = $retailer_id;
        $history->previous_stock = $pre_stock;
        $history->current_stock = $cur_stock;
        $history->txn_type = "Debit";
        $history->save();
        
        $sms = 'Welcome to '.env('APP_NAME').' Family, Your Registered number is '.$mobile.' Your KYC under process. Thank You';
        $sendsms = $this->apiManager->sendSMS($retailer_id,$sms);
        
        Session::flash('success', 'Retailer registred successfully');
        return redirect()->back();
    }
    
    public function getAddMember() {
        return view('pages.distributor_panel.retailers.add_member');
    }
    
    public function postAddMember(Request $request) {
        
        $user_id = Auth::User()->id;
        $user_type = Auth::User()->user_type;
        $user_m = new UserMember();
        $user_m->user_id = $user_id;
        $user_m->name = ucfirst($request->name);
        $user_m->mobile = trim($request->mobile);
        $user_m->email = $request->email;
        $user_m->gender = trim($request->gender);
        $user_m->profession = $request->profession;
        $user_m->relation_with_user = $request->relation_with_user;
        $user_m->save();
        
        Session::flash('success', 'Member add successfully');
        return redirect()->back();
    }
    
    public function getEditMember($idd) {
        $date = date("Ymd");
        $id = rtrim($idd, "$date");
        $user_id = Auth::User()->id;
        $user_type = Auth::User()->user_type;
        if($user_type == 1){
            $edit = UserMember::where('id',$id)->first();
        }else{
            $edit = UserMember::where('user_id',$user_id)->where('id',$id)->first();
        }
        
        // print_r($id);exit;
        return view('pages.distributor_panel.retailers.edit_member',compact('edit'));
    }
    
    public function postEditMember(Request $request) {
        
        $user_id = Auth::User()->id;
        $user_type = Auth::User()->user_type;
        
        if($user_type == 1){
            $user_m = UserMember::where('id',$request->user_id)->first();
        }else{
            $user_m = UserMember::where('user_id',$user_id)->where('id',$request->user_id)->first();
        }
        if($user_m){
            
        
            // $user_m->user_id = $user_id;
            $user_m->name = ucfirst($request->name);
            $user_m->mobile = trim($request->mobile);
            $user_m->email = $request->email;
            $user_m->gender = trim($request->gender);
            $user_m->profession = $request->profession;
            $user_m->relation_with_user = $request->relation_with_user;
            $user_m->save();
            
            Session::flash('success', 'Member Updated successfully');
            return redirect()->back();
        }else{
            Session::flash('error', 'You can not update member.');
            return redirect()->back();
        }
    }
    
    public function postViewMember(Request $request) {
        $members = UserMember::where('user_id',$request->user_id)->get();
        return view('pages.view_member_list',compact('members'));
    }
    
    public function postDeleteMember(Request $request) {
        $user_id = Auth::User()->id;
        $user_m = UserMember::where('user_id',$user_id)->where('id',$request->user_id)->delete();
        
        return response()->json(['success' => true, 'message' => 'Deleted successfully.' , 'status' => 1]);
    }
    
    public function getManageMember() {
        return view('pages.distributor_panel.retailers.manage_member');
    }
    
    public function getManageMemberData() {
        
        $user_id = Auth::User()->id;
        $user_type = Auth::User()->user_type;
            $data = UserMember::where('user_id',$user_id)->get();
        return DataTables::of($data)->make(true);
    }
    
    public function getManageRetailer() {
        return view('pages.distributor_panel.retailers.manage_retailers');
    }
    
    public function getManageRetailerData() {
        
        $user_id = Auth::User()->id;
        $user_type = Auth::User()->user_type;
        
        
        if($user_type == 1) {
            
            $data = User::select('users.*','cities.name as cityname','states.name as statename','kyc_docs.status as kyc_status',
            'distributor.name as dist_name','distributor.mobile as dist_mobile')
            ->leftjoin('users_mappings','users_mappings.user_id','users.id')
            ->leftjoin('users as distributor','distributor.id','users_mappings.toplevel_id')
            ->leftjoin('kyc_docs','kyc_docs.user_id','users.id')
            ->leftjoin('addresses','addresses.user_id','users.id')
            ->leftjoin('cities','cities.id','addresses.city')
            ->leftjoin('states','states.id','addresses.state')
            ->where('users.user_type','2')->where('users.status','1')->get();
            
        }
        else{
            $data = User::select('users.*','cities.name as cityname','states.name as statename','kyc_docs.status as kyc_status')
            ->join('users_mappings','users_mappings.user_id','users.id')
            ->leftjoin('kyc_docs','kyc_docs.user_id','users.id')
            ->leftjoin('addresses','addresses.user_id','users.id')
            ->leftjoin('cities','cities.id','addresses.city')
            ->leftjoin('states','states.id','addresses.state')
            ->where('users_mappings.toplevel_id',$user_id)
            ->where('users.user_type','2')->get();
        
        }
        
        
        return DataTables::of($data)->make(true);
    }
    
    public function getUploadKycDocuments() {
        
        $user_id = Auth::User()->id;
        $kycdocs = KycDoc::where('user_id',$user_id)->whereIn('status',[1,0])->first();
            
        if($kycdocs) {
            Session::flash('error', 'Kyc already uploaded!');
            return redirect()->back()->withInput();
        }
        
        
        return view('pages.distributor_panel.kyc.upload_kyc_documents');
    }
    
    public function postUploadKycDocuments(Request $request) {
        
        $user_id = Auth::User()->id;
        $kycdocs = KycDoc::where('user_id',$user_id)->where('status',2)->first();
            
        if($kycdocs) {
            $kyc = KycDoc::find($kycdocs->id);
            
            $check_pan_num = KycDoc::where('user_id','!=',$user_id)->where('pan_number',$request->get("pan_number"))->first();
            
            if($check_pan_num) {
                Session::flash('error', 'PAN number already used!');
                return redirect()->back()->withInput();
            }
            
            $check_aadhaar_num = KycDoc::where('user_id','!=',$user_id)->where('aadhaar_number',$request->get("aadhaar_number"))->first();
            
            if($check_aadhaar_num) {
                Session::flash('error', 'Aadhaar number already used!');
                return redirect()->back()->withInput();
            }
        }
        else {
            
            $check_pan_num = KycDoc::where('pan_number',$request->get("pan_number"))->first();
            
            if($check_pan_num) {
                Session::flash('error', 'PAN number already used!');
                return redirect()->back()->withInput();
            }
            
            $check_aadhaar_num = KycDoc::where('aadhaar_number',$request->get("aadhaar_number"))->first();
            
            if($check_aadhaar_num) {
                Session::flash('error', 'Aadhaar number already used!');
                return redirect()->back()->withInput();
            }
            
            $kyc = new KycDoc();
        }
        
        $kyc->user_id = $user_id;
        $kyc->pan_number = strtoupper($request->get('pan_number'));
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
        
        Session::flash('success', 'KYC documents uploaded successfully');
        return redirect('dashboard');
        // return redirect()->route("dashboard");
    }
    
    public function getDistributorPassbook($mobile) {
       
        $user_id = Auth::User()->id;
        $user_type = Auth::User()->user_type;
        
        $distributor = User::where('mobile',$mobile)->where('user_type','3')->first();
        if($distributor) {
            $distributor_id = $distributor->id;
            $distributor_name = $distributor->name;
            
            return view('pages.distributor_panel.passbook.distributor_passbook',compact('mobile','distributor_id','distributor_name'));
        }
        else {
            Session::flash('error', 'Distributor not found!');
            return redirect()->back();
        }
    }
    
    public function getMyPassbook() {
        $user_id = Auth::User()->id;
        $user_type = Auth::User()->user_type;
    
        if($user_type == 3) {
            return view('pages.distributor_panel.passbook.distributor_passbook');
        }
        elseif($user_type == 4) {
            return view('pages.distributor_panel.passbook.super_distributor_passbook');
        }
        else{
            Session::flash('error', 'Unauthorised access!');
            return redirect()->back();
        }
    }
    
    public function getDistributorPassbookData(Request $request) {
        $user_id = Auth::User()->id;
        $user_type = Auth::User()->user_type;
        
        if($user_type == 1) {
            $distributor_id = $request->get('distributor_id');
            $data = Transactions::where('user_id',$distributor_id)
            ->whereDate('created_at', $request->get('start_date'))
            ->get();
        }
        elseif($user_type == 3){
            $data = Transactions::where('user_id',$user_id)
            ->whereDate('created_at', $request->get('start_date'))
            ->get();
        }
        else{
            $data = [];
        }
        
        // return DataTables::of($data)->make(true);
        $total_success_sum = collect($data)->sum('amount');
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
    }
    
    public function getRetailerPassbook($mobile) {
        
        $user_id = Auth::User()->id;
        $user_type = Auth::User()->user_type;
    
        $retailer = User::where('mobile',$mobile)->where('user_type',2)->first();
        if($retailer) {
            $retailer_id = $retailer->id;
            $retailer_name = $retailer->name;
            
            $mapping = UsersMapping::where('user_id',$retailer_id)->where('toplevel_id',$user_id)->first();
            
            if($mapping) {
                return view('pages.distributor_panel.passbook.retailer_passbook',compact('mobile','retailer_id','retailer_name'));
            }
            elseif($user_type == 1) {
                return view('pages.distributor_panel.passbook.retailer_passbook',compact('mobile','retailer_id','retailer_name'));
            }
            else {
                Session::flash('error', 'Retailer not found!');
                return redirect()->back();
            }
        }
        else {
            Session::flash('error', 'Retailer not found!');
            return redirect()->back();
        }
    }
    
    public function getRetailerPassbookData(Request $request) {
        $user_id = Auth::User()->id;
        $user_type = Auth::User()->user_type;
        
        $retailer_id = $request->get('retailer_id');
        $retailer_mobile = $request->get('retailer_mobile');
        
        $mapping = UsersMapping::where('user_id',$retailer_id)->where('toplevel_id',$user_id)->first();
        
        if($mapping) {
            $data = Transactions::where('user_id',$retailer_id)
            ->whereDate('created_at', $request->get('start_date'))
            ->get();
        }
        elseif($user_type == 1) {
            $data = Transactions::where('user_id',$retailer_id)
            ->whereDate('created_at', $request->get('start_date'))
            ->get();
        }
        else {
            $data = [];
        }
        return DataTables::of($data)->make(true);
    }
    
    public function getCommissionReport() {
        return view('pages.comman.reports.commission_report');
    }
    
    public function getCommissionReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        $data = [];
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
        $commissoin_events = $this->apiManager->getCommissionEvents();
        
        $data = Transactions::select('transactions.*',
        'txn_tbl.amount as txn_amount','txn_tbl.event as txn_event')
        ->join('transactions as txn_tbl','txn_tbl.transaction_id','transactions.ref_txn_id')
        ->whereIn('transactions.event',$commissoin_events)
        ->where('transactions.user_id',$user_id)
        ->where('transactions.amount','>',0)
        ->whereBetween('transactions.created_at', array($from, $to))->get();
        
        $total_success_sum = collect($data)->sum('amount');
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
    }
    
    public function getSuperDistributorPassbook($mobile) {
       
        $user_id = Auth::User()->id;
        $user_type = Auth::User()->user_type;
        
        $super_distributor = User::where('mobile',$mobile)->where('user_type','4')->first();
        if($super_distributor) {
            $super_distributor_id = $super_distributor->id;
            $super_distributor_name = $super_distributor->name;
            
            return view('pages.distributor_panel.passbook.super_distributor_passbook',compact('mobile','super_distributor_id','super_distributor_name'));
        }
        else {
            Session::flash('error', 'Super Distributor not found!');
            return redirect()->back();
        }
    }
    
    public function getSuperDistributorPassbookData(Request $request) {
        $user_id = Auth::User()->id;
        $user_type = Auth::User()->user_type;
        
        if($user_type == 1) {
            $super_distributor_id = $request->get('super_distributor_id');
            $data = Transactions::where('user_id',$super_distributor_id)
            ->whereDate('created_at', $request->get('start_date'))
            ->get();
        }
        elseif($user_type == 4){
            $data = Transactions::where('user_id',$user_id)
            ->whereDate('created_at', $request->get('start_date'))
            ->get();
        }
        else{
            $data = [];
        }
        $total_success_sum = collect($data)->sum('amount');
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
    }
    
    
    //Retailer Ids
    public function getPurchaseRetailerIds() {
        return view('pages.distributor_panel.retailers.purchase_retailer_ids');
    }
    
    public function postPurchaseRetailerIds(Request $request) {
    
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        
        $user = User::find($user_id);
        $quantity = $request->get('quantity');
        $plan_amount = env('RETAILER_IDS_FEE');
        $plan_min_qty = env('RETAILER_IDS_MIN_QTY');
        
        $stock_amount = $quantity * $plan_amount;
        
        if($quantity < $plan_min_qty) {
            Session::flash('error', "Minimum ".$plan_min_qty." retailer ids required");
            return redirect()->back();
        }
        elseif($stock_amount > $user->wallet) {
            Session::flash('error', 'Wallet amount is too low!');
            return redirect()->back();
        }else {
            
            $current_balance = $user->wallet;
            $final_balance = $user->wallet-$stock_amount;
            
            $pre_stock = $user->retailer_ids;
            $cur_stock = $user->retailer_ids+$quantity;
            
            $user->wallet = $final_balance;
            $user->retailer_ids = $cur_stock;
            $user->save();
            
            $txn_id = $this->apiManager->txnId("RID");
            
            $transaction = new Transactions();
            $transaction->transaction_id = $txn_id;
            $transaction->user_id = $user_id;
            $transaction->event = 'ADDRETAILERIDS';
            $transaction->amount = $stock_amount;
            $transaction->current_balance = $current_balance;
            $transaction->final_balance = $final_balance;
            $transaction->status = 1;
            $transaction->txn_type = 'Debit';
            $transaction->txn_note = 'Retailer Ids purchased';
            $transaction->save();
            
            $history = new RetailerIdsHistory();
            $history->distributor_id = $user_id;
            $history->previous_stock = $pre_stock;
            $history->current_stock = $cur_stock;
            $history->txn_type = "Credit";
            $history->save();
            
            Session::flash('success', 'Retailer Ids added successfully');
            return redirect()->back();
        }
    }
    
    public function getPurchaseRetailerIdsReport(Request $request) {
        return view('pages.distributor_panel.retailers.purchase_retailer_ids_report');
    }
    
    public function getPurchaseRetailerIdsReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
        $query = RetailerIdsHistory::query();
    
        // $data = $query->select('transactions.*','users.retailer_ids')
        // ->join('users','users.id','transactions.user_id')
        // ->where('transactions.user_id',$user_id)
        // ->where('transactions.event','ADDRETAILERIDS')
        // ->whereBetween('transactions.created_at', array($from, $to))
        // ->get();
        
        
        $data = $query->select('*')
        ->where('distributor_id',$user_id)
        ->whereBetween('created_at', array($from, $to))
        ->get();
        
        return DataTables::of($data)->make(true);
    }
    
    public function getEditRetailer($mobile) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        
        $retailer = User::where('mobile',$mobile)->first();
        
        if($retailer) {
            
            $retailer_id = $retailer->id;
            
            $edit = $retailer;
            
            // dd($edit);
            
            return view('pages.distributor_panel.retailers.edit_retailer',compact('edit','retailer_id'));
        } 
        else{
            Session::flash('error', 'Unauthorized access!');
            return redirect()->back();   
        }
        
        
       
    }
    
    public function postEditRetailer(Request $request) {
        $user = User::where('id',$request->user_id)->where('mobile',$request->mobile)->first();    
        if($user){
            
            $user->name = ucfirst($request->name);
            $user->mobile = trim($request->mobile);
            $user->email = $request->get('email');
            $user->profession = $request->profession;
            $user->postcode = strtoupper(trim($request->postcode));
            $user->town = ucfirst($request->town);
            $user->address_line_1 = $request->address_line_1;
            $user->address_line_2 = $request->address_line_2;
            $user->gender = trim($request->gender);
            $user->save();
            
            Session::flash('success', 'Updated successfully');
            return redirect()->back();
        }else{
            Session::flash('error', 'Something Wrong!');
            return redirect()->back();   
        }
    }
    
    //WALLETLOAD
    public function postDistributorAddMoneyToCustomer(Request $request) {
        
        
        $user_id = $request->get('user_id');
        $amount = $request->get('wallet_amount');
        if($amount < 0){
            Session::flash('error', 'Amount Not Allow');
            return redirect()->back();
        }
        $user = User::find($user_id);
        
        if($user) {
            
            $distributor_id = Auth::User()->id;
            $distributor = User::find($distributor_id);
            
            $distributor_current_balance = $distributor->wallet;
            $distributor_final_balance = $distributor->wallet-$amount;
            
            if($amount > $distributor_current_balance) {
                Session::flash('error', 'Wallet balance is low!');
                return redirect()->back()->withInput();
            }
            
            $txnid = $this->apiManager->txnId("TRF");
            $transaction = new Transactions();
            $transaction->transaction_id = $txnid;
            $transaction->user_id = $distributor_id;
            $transaction->referenceId = $user_id;
            $transaction->event = 'WALLETTRANSFER';
            $transaction->amount = round($amount,5);
            $transaction->current_balance = $distributor_current_balance;
            $transaction->final_balance = $distributor_final_balance;
            $transaction->txn_type = 'Debit';
            $transaction->txn_note = 'Wallet amount transfer to retailer';
            $transaction->status = 1;
            $transaction->save();
        
            $distributor->wallet = $distributor_final_balance;
            $distributor->save();
            
            //Retailer entry
            $current_balance = $user->wallet;
            $final_balance = $user->wallet+$amount;
            
            $txn_id = $this->apiManager->txnId("WLT");
            $transaction = new Transactions();
            $transaction->transaction_id = $txn_id;
            $transaction->user_id = $user_id;
            $transaction->referenceId = $distributor_id;
            $transaction->event = 'WALLETLOAD';
            $transaction->amount = round($amount,5);
            $transaction->current_balance = $current_balance;
            $transaction->final_balance = $final_balance;
            $transaction->txn_type = 'Credit';
            $transaction->txn_note = 'Distributor loaded wallet';
            $transaction->status = 1;
            $transaction->save();
        
            $user->wallet = $final_balance;
            $user->save();
            
            $title = 'Wallet Load';
            $message = "Your wallet loaded with amount of Rs. ".$amount;
            $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
            //$sendsms = $this->apiManager->sendSMS($user_id,$message);
            
            Session::flash('success', 'Wallet loaded successfully');
            return redirect()->back();
        }
        else {
            Session::flash('error', 'Something went wrong please try again.');
            return redirect()->back();
        }
    }
    
    public function getMemberRenewal() {
        return view('pages.renewal');
    }
    
    public function postMemberRenewal(Request $request) {
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        $user = User::find($user_id);
        if($request->payment == 'bank'){
            $user->payment_status = 0;
        }else{
            if($request->get('status') == 'COMPLETED'){
                $user->payment_status = 1;
            }else{
                $user->payment_status = 0;
            }
            
        }
        $user->membership = ucfirst($request->membership);
        $user->membership_year = $request->membershipyear;
        $user->membership_date = date('Y-m-d');
        $user->save();
        
        if($request->payment == 'PayPal'){
            $pay = new Payment();
            $pay->user_id = $user->id;
            $pay->paypal_id = $request->get('paypal_id');
            $pay->payer_name = $request->get('payer_name');
            $pay->payer_surname = $request->get('payer_surname');
            $pay->payer_email = $request->get('payer_email');
            $pay->payer_id = $request->get('payer_id');
            $pay->payment_time = $request->get('payment_time');
            $pay->status = $request->get('status');
            $pay->amount = $request->get('amount');
            $pay->save();
        }
        
        if($user->membership == 'Family'){
            $fee = 15;
        }else{
            $fee = 7;
        }
        
        return view('pages.payment_done',compact('user','fee'));
    }
    
}