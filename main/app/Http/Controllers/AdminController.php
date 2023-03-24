<?php

namespace App\Http\Controllers;
use App\Classes\ApiManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use DataTables;
use Excel;
use Auth;

use App\User;
use App\Model\Address;
use App\Model\AdminEkoBank;
use App\Model\AdminEkoSettlementRequest;
use App\Model\Dispute;
use App\Model\State;
use App\Model\UsersMapping;
use App\Model\UsersRelation;
use App\Model\KycDoc;
use App\Model\ResponseTable;
use App\Model\Setting;
use App\Model\Operators;
use App\Model\Notifications;
use App\Model\AppBanner;
use App\Model\Transactions;
use App\Model\GoogleCode;
use App\Model\Event;
use App\Model\EventEntry;

class AdminController extends Controller
{
    public function __construct(ApiManager $apiManager) {
        $this->middleware('auth');
        $this->apiManager = $apiManager;
    }
    
    public function getlogs() {
        $data = ResponseTable::orderBy('id','DESC')->get();
        return view('pages.admin_panel.logs',compact('data'));
    }
    
    public function getAddDistributor() {
        $states = State::where('country_id',1)->get();
        return view('pages.admin_panel.distributors.add_distributor',compact('states'));
    }
    
    public function postAddDistributor(Request $request) {
        $user_id = Auth::user()->id;
        
        $mobile = $request->get('mobile');
        
        $checkMobile = User::where('mobile', $mobile)->first();
        
        if ($checkMobile) {
            Session::flash('error', 'Mobile number already in use!');
            return redirect()->back()->withInput();
        }
        
        $password = $this->apiManager->getPassword(8);   
            
        $user = new User();    
        $user->name = ucfirst($request->get('name'));
        $user->mobile = $mobile;
        $user->dob = $request->get('dob');
        $user->email = $request->get('email');
        $user->password = bcrypt($password);
        $user->status = 1;
        $user->user_type = 3;
        $user->save();
        
        $address = new Address();
        $address->user_id = $user->id;
        $address->address = $request->get('address');
        $address->pincode = $request->get('pincode');
        $address->state = $request->get('state');
        $address->city = $request->get('city');
        $address->save();
        
        $dist_id = $user->id;
        
        $mapping = new UsersMapping();
        $mapping->user_id = $dist_id;
        $mapping->toplevel_id = $user_id;
        $mapping->save();
        
        $relation = new UsersRelation();
        $relation->distributor_id = $dist_id;
        $relation->admin_id = $user_id;
        $relation->save();
        
        $sms_message = 'Welcome to '.env('APP_NAME').' family, Your user I’d : '.$mobile.', password : '.$password.' Login : '.env('APP_URL');
        $sendsms = $this->apiManager->sendSMS($dist_id,$sms_message);
        
        Session::flash('success', 'Distributor registred successfully');
        return redirect()->back();
    }
    
    public function getManageDistributors() {
        return view('pages.admin_panel.distributors.manage_distributors');
    }
    
    public function getManageDistributorsData() {  
        
        $user_id = Auth::User()->id;
        
        $data = User::select('users.*','cities.name as cityname','states.name as statename','kyc_docs.status as kyc_status')
        ->join('users_mappings','users_mappings.user_id','users.id')
        ->leftjoin('kyc_docs','kyc_docs.user_id','users.id')
        ->leftjoin('addresses','addresses.user_id','users.id')
        ->leftjoin('cities','cities.id','addresses.city')
        ->leftjoin('states','states.id','addresses.state')
        ->where('users_mappings.toplevel_id',$user_id)
        ->where('users.user_type','3')
        ->get();
        
        return DataTables::of($data)->make(true);
    }
    
    public function getRetailersPendingKyc() {
        return view('pages.admin_panel.kyc.manage_retailers_kyc');
    }
    
    public function getRetailersPendingKycData() {
        $data = User::select('users.*','cities.name as cityname','states.name as statename','kyc_docs.status as kyc_status')
        ->join('kyc_docs','kyc_docs.user_id','users.id')
        ->leftjoin('addresses','addresses.user_id','users.id')
        ->leftjoin('cities','cities.id','addresses.city')
        ->leftjoin('states','states.id','addresses.state')
        ->where('users.user_type','2')
        ->where('kyc_docs.status','0')
        ->get();
        return DataTables::of($data)->make(true);
    }

    public function getDistributorsPendingKyc() {
        return view('pages.admin_panel.kyc.manage_distributors_kyc');
    }
    
    public function getDistributorsPendingKycData() {
        $data = User::select('users.*','cities.name as cityname','states.name as statename','kyc_docs.status as kyc_status')
        ->join('kyc_docs','kyc_docs.user_id','users.id')
        ->leftjoin('addresses','addresses.user_id','users.id')
        ->leftjoin('cities','cities.id','addresses.city')
        ->leftjoin('states','states.id','addresses.state')
        ->where('users.user_type','3')
        ->where('kyc_docs.status','0')
        ->get();
        return DataTables::of($data)->make(true);
    }
    
    public function getSuperDistributorsPendingKyc() {
        return view('pages.admin_panel.kyc.manage_super_distributors_kyc');
    }
    
    public function getSuperDistributorsPendingKycData() {
        $data = User::select('users.*','cities.name as cityname','states.name as statename','kyc_docs.status as kyc_status')
        ->join('kyc_docs','kyc_docs.user_id','users.id')
        ->leftjoin('addresses','addresses.user_id','users.id')
        ->leftjoin('cities','cities.id','addresses.city')
        ->leftjoin('states','states.id','addresses.state')
        ->where('users.user_type','4')
        ->where('kyc_docs.status','0')
        ->get();
        return DataTables::of($data)->make(true);
    }
    
    public function getKYCdocs(Request $request) {
        
        $data = User::find($request->get('user_id'));
        $kycdocs = KycDoc::where('user_id',$data->id)->where('status','0')->first();
        
        $url = env('IMAGE_URL')."uploads/kycdocs/";
        
        if($kycdocs) {
            return response()->json(["status" => true, "message" => "KYC detailes", 
            'pan_number' => $kycdocs->pan_number, 
            'aadhaar_number' =>$kycdocs->aadhaar_number,
            'pan_image' => $url.$kycdocs->pan_image,
            'aadhaar_front_image' => $url.$kycdocs->aadhaar_front_image,
            'aadhaar_back_image' => $url.$kycdocs->aadhaar_back_image,
            'created_at' => $kycdocs->created_at,
            'name' => $data->name,
            'mobile' => $data->mobile,
            'dob' => $data->dob]);
        }
        else {
            return response()->json(["status" => false, "message" => "Something went worng!"]);
        }
    }
    
    public function updateKYCstatus(Request $request) {
        
        $kyc_status = $request->get('status'); 
        $user_id = $request->get('user_id');
        
        $user = User::find($user_id);
        
        $username = $user->name;
        
        if($kyc_status == 'approve') {
           
            $title = 'KYC Approval';
            $message = "Dear ".$username." your kyc is approved. Now you can start transactions";
            $status = 1;
            $re_message = 'Kyc approved for ';
            $status_action = 'approved';
        }
        else {
        
            $title = 'KYC Rejection';
            $message = "Dear ".$username." your kyc is rejected due to ".$request->get('kyc_reject_reason');
            $status = 2;
            $re_message = 'Kyc rejected for ';
            $status_action = 'rejected';
        }
        
        $kycdocs = KycDoc::where('user_id',$user_id)->first();
        if($kycdocs) {
            
            $kycdocs->status = $status;
            $kycdocs->approved_by = Auth::User()->id;
            $kycdocs->reject_reason = $request->get('kyc_reject_reason');
            $kycdocs->save();
        }
        
        $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
//        $sendsms = $this->apiManager->sendSMS($user_id,$message);
        
        if($user)
            return json_encode(array("status" => true, "message" => $re_message.$username));
        else
            return json_encode(array("status" => false, "message" => "Something went worng!"));
    }
    
    public function getAddEkoBanks() {
        return view('pages.admin_panel.eko_bank.add_eko_bank');
    }
    
    public function postAddEkoBanks(Request $request) {
        
        $bank_id = $request->get('bank_id');
        $account = $request->get('account_number');
        $ifsc = $request->get('ifsc');
        $holder_name = $request->get('holder_name');
        
        $bank_name = '';
        
        $favcolor = "red";

        switch ($bank_id) {
          case "7":
            $bank_name = 'ICICI Bank';
            break;
          case "16":
            $bank_name = 'Yes Bank';
            break;
          case "48":
            $bank_name = 'IndusInd Bank';
            break;
        }
        
        $initiator_id = env('EKO_AEPS_INITIATOR_ID');
        // $api_url = env('EKO_AEPS_URL')."user_code:16731001/settlementaccount/";
        $api_url = "https://api.eko.in:25002/ekoicici/v1/agent/user_code:16731001/settlementaccount/";
        
        $params = [
            'initiator_id' => $initiator_id,
            'bank_id' => $bank_id,
            'ifsc' => $ifsc,
            'service_code' => 39,
            'account' => $account,
        ];
    
        $api_params = http_build_query($params, '', '&');
    
        $api_response = $this->apiManager->ekoPostCall($api_url,$api_params);
        $api_data = json_decode($api_response);
        
        $reponse_table = new ResponseTable();
        $reponse_table->response = $api_response;
        $reponse_table->request = json_encode($params);
        $reponse_table->api_name = 'EKO_ADD_ADMIN_BANK';
        $reponse_table->save();
        
        if($api_data->status == 0) {
                
            if($api_data->response_type_id == 1336) {
                
                $recipient_id = $api_data->data->recipient_id;
                
                $add_bank = new AdminEkoBank();
                $add_bank->bank_id = $bank_id;
                $add_bank->bank_name = $bank_name;
                $add_bank->account_number = $account;
                $add_bank->ifsc = $ifsc;
                $add_bank->holder_name = $holder_name;
                $add_bank->recipient_id = $recipient_id;
                $add_bank->save();
                
                Session::flash('success', 'Bank added successfully');
                return redirect()->back();
            }
            else{
                Session::flash('error', $api_data->message);
                return redirect()->back();
            }
        }
        else {
            Session::flash('error', 'Something went wrong');
            return redirect()->back();
        }
    }
    
    public function getManageEkoBanks(Request $request) {
        
        if($request->get('json_data')) {
            $data = AdminEkoBank::where('status',1)->get();
            return DataTables::of($data)->make(true);
        }
        else{
            return view('pages.admin_panel.eko_bank.manage_eko_banks');    
        }
    }
    
    public function getManagePendingDisputes(Request $request) {
        
        if($request->get('json_data')) {
            $data = Dispute::select('disputes.*','transactions.status as txn_status','users.name','users.mobile')
            ->join('transactions','transactions.transaction_id','disputes.transaction_id')
            ->join('users','users.id','transactions.user_id')
            ->where('disputes.status',0)->get();
            return DataTables::of($data)->make(true);
        }
        else {
            return view('pages.admin_panel.supports.manage_pending_disputes');    
        }
    }
    
    public function postReplyToDispute(Request $request) {
        $dispute_id = $request->get('dispute_id');
        $admin_remark = $request->get('admin_remark');
        $dispute = Dispute::find($dispute_id);
        
        if($dispute) {
            
            $user_id = $dispute->user_id;
            
            $dispute->status = 1;
            $dispute->admin_remark = $admin_remark;
            $disputer->remark_time = now();
            $dispute->save();
            
            $title='Dispute Remark';
            $message = $dispute->transaction_id.' disputed query solved';
            $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
            
            return response()->json(["status" => true, "message" => "Replied to disputed query"]);
        }
        else{
            return response()->json(["status" => false, "message" => "Something went worng!"]);
        }
        
    }
    
    public function getDisputesReport(Request $request) {
        if($request->get('json_data')) {
            
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);
        
            $data = Dispute::select('disputes.*','transactions.status as txn_status','users.name','users.mobile')
            ->join('transactions','transactions.transaction_id','disputes.transaction_id')
            ->join('users','users.id','transactions.user_id')
            ->where('disputes.status',1)
            ->whereBetween('disputes.created_at', array($from, $to))->get();
            return DataTables::of($data)->make(true);
        }
        else {
            return view('pages.admin_panel.supports.dispute_report');    
        }
    }
    
    public function changeServiceStatus(Request $request) {
        
        $status =  $request->get('status');
        $service = $request->get('service');
        $userid = $request->get('user_id');
        
        $user = User::limit(1)->find($userid);
        
        if($status == 1) {
            $user->$service = 0;
        }
        else {
            $user->$service = 1;
        }
        
        $user->save();
        
        if($user) {
            if($user->$service==1)
                return response()->json(['success' => true, 'message' => 'Service activated successfully.' , 'status' => 1]);
            else
                return response()->json(['success' => true, 'message' => 'Service deactivated successfully.', 'status' => 0]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Something went wrong!']);
        }
    }
    
    public function getDistributorMapping(Request $request) {
        $users = User::where('user_type','1')->get();
        return view('pages.admin_panel.mappings.distributor_mapping',compact('users'));    
    }
    
    public function postDistributorMapping(Request $request) {
        
        $mobile = $request->get('mobile');
        $toplevel_id = $request->get('toplevel_id');
        
        $user = User::where('mobile',$mobile)->where('user_type','3')->first();
        if($user) {
            $user_id = $user->id;
            $mapped = UsersMapping::where('user_id',$user_id)->first();
            if($mapped){
                
                $mapped->toplevel_id = $toplevel_id;
                $mapped->save();    
                
                $relation = UsersRelation::where('distributor_id',$user_id)->first();
                if($relation){
                    $relation->admin_id = $toplevel_id;
                    $relation->save();
                }else{
                    $relation = new UsersRelation();
                    $relation->distributor_id = $user_id;
                    $relation->admin_id = $toplevel_id;
                    $relation->save();
                }
                
                Session::flash('success', 'Mapping Done!');
                return redirect()->back();
                
            }
            else {
                $mapping = new UsersMapping();
                $mapping->user_id = $user_id;
                $mapping->toplevel_id = $toplevel_id;
                $mapping->save();
                
                $relation = new UsersRelation();
                $relation->distributor_id = $user_id;
                $relation->admin_id = $toplevel_id;
                $relation->save();
            }
        }
        else {
            Session::flash('error', 'Mobile not found!');
            return redirect()->back();
        }
    }
    
    public function getRetailerMapping(Request $request) {
        $users = User::where('user_type','3')->get();
        return view('pages.admin_panel.mappings.retailer_mapping',compact('users'));    
    }
    
    public function postRetailerMapping(Request $request) {
        
        $mobile = $request->get('mobile');
        $toplevel_id = $request->get('toplevel_id');
        
        $user = User::where('mobile',$mobile)->where('user_type','2')->first();
        if($user) {
            $user_id = $user->id;
            $mapped = UsersMapping::where('user_id',$user_id)->first();
            if($mapped){
                $mapped->toplevel_id = $toplevel_id;
                $mapped->save();    
                $admin_id = 1;
                
                $relation = UsersRelation::where('retailer_id',$user_id)->first();
                if($relation){
                    $relation->distributor_id = $toplevel_id;
                    $relation->admin_id = $admin_id;
                    $relation->save();
                }else{
                    $relation = new UsersRelation();
                    $relation->retailer_id = $user_id;
                    $relation->distributor_id = $toplevel_id;
                    $relation->admin_id = $admin_id;
                    $relation->save();
                }
                
                Session::flash('success', 'Mapping Done!');
                return redirect()->back();
            }
            else {
                $mapping = new UsersMapping();
                $mapping->user_id = $user_id;
                $mapping->toplevel_id = $toplevel_id;
                $mapping->save();
                
                $admin_id = 1;
                
                $relation = new UsersRelation();
                $relation->retailer_id = $user_id;
                $relation->distributor_id = $toplevel_id;
                $relation->admin_id = $admin_id;
                $relation->save();
                
                Session::flash('success', 'Mapping Done!');
                return redirect()->back();
            }
        }
        else {
            Session::flash('error', 'Mobile not found!');
            return redirect()->back();
        }
    }
    
    public function getRetailersManageServices(Request $request) {
        
        if($request->get('json_data')) {
            $data = User::select('id','name','mobile','status','upi_status','qr_status','va_status','eko_status','dmt_service','recharge_service')->where('user_type',2)->get();
            return DataTables::of($data)->make(true);
        }
        else{
            return view('pages.comman.reports.retailers_manage_services');    
        }
    }
    
    
    //SUPER DISTRIBUTOR
    public function getAddSuperDistributor() {
        $states = State::where('country_id',1)->get();
        return view('pages.admin_panel.super_distributors.add_super_distributor',compact('states'));
    }
    
    public function postAddSuperDistributor(Request $request) {
        $user_id = Auth::user()->id;
        $mobile = $request->get('mobile');
        
        $checkMobile = User::where('mobile', $mobile)->first();
        
        if ($checkMobile) {
            Session::flash('error', 'Mobile number already in use!');
            return redirect()->back()->withInput();
        }
        
        $password = $this->apiManager->getPassword(8);   
            
        $user = new User();    
        $user->name = ucfirst($request->get('name'));
        $user->mobile = $mobile;
        $user->dob = $request->get('dob');
        $user->email = $request->get('email');
        $user->password = bcrypt($password);
        $user->status = 1;
        $user->user_type = 4; //super distributor
        $user->save();
        
        $address = new Address();
        $address->user_id = $user->id;
        $address->address = $request->get('address');
        $address->pincode = $request->get('pincode');
        $address->state = $request->get('state');
        $address->city = $request->get('city');
        $address->save();
        
        $dist_id = $user->id;
        
        $mapping = new UsersMapping();
        $mapping->user_id = $dist_id;
        $mapping->toplevel_id = $user_id;
        $mapping->save();
        
        $relation = new UsersRelation();
        $relation->super_distributor_id = $dist_id;
        $relation->admin_id = $user_id;
        $relation->save();
        
        $sms_message = 'Welcome to '.env('APP_NAME').' family, Your user I’d : '.$mobile.', password : '.$password.' Login : '.env('APP_URL');
        $sendsms = $this->apiManager->sendSMS($dist_id,$sms_message);
        
        Session::flash('success', 'Super Distributor registred successfully');
        return redirect()->back();
    }
    
    public function getManageSuperDistributors() {
        return view('pages.admin_panel.super_distributors.manage_super_distributors');
    }
    
    public function getManageSuperDistributorsData() {  
        
        $user_id = Auth::User()->id;
        
        $data = User::select('users.*','cities.name as cityname','states.name as statename','kyc_docs.status as kyc_status')
        ->join('users_mappings','users_mappings.user_id','users.id')
        ->leftjoin('kyc_docs','kyc_docs.user_id','users.id')
        ->leftjoin('addresses','addresses.user_id','users.id')
        ->leftjoin('cities','cities.id','addresses.city')
        ->leftjoin('states','states.id','addresses.state')
        ->where('users_mappings.toplevel_id',$user_id)
        ->where('users.user_type','4')
        ->get();
        
        return DataTables::of($data)->make(true);
    }
    
    public function getAppSettings() {
        $data = Setting::first();
        return view('pages.admin_panel.settings',compact('data'));
    }
    
    public function postAppSettings(Request $request) {  
        
        $user_id = Auth::User()->id;
        $update = Setting::where('id', 1)->update([
        "tncs" => $request->get('tncs')
        ]);
        
        
            
        Session::flash('success', 'Settings updated successfully!');
        return redirect()->back();
    }
    
    public function getManageCommission(Request $request) {
        
        if($request->get('json_data')) {
            $data = Operators::where('status',1)->whereIn('service_id',[1,2,14,21,23])->get();
            return DataTables::of($data)->make(true);
        }
        else{
            
            return view('pages.comman.commissions.manage_commissions');
        }
    }
    
    public function postUpdateCommission(Request $request) {
        $id = $request->get('id');
        $operator = Operators::find($id);
        if($operator){
            $operator->sd_commission = $request->get('sd_commission');
            $operator->sd_commission_type = $request->get('sd_commission_type');
            $operator->dist_commission = $request->get('dist_commission');
            $operator->dist_commission_type = $request->get('dist_commission_type');
            $operator->commission = $request->get('retailer_commission');
            $operator->commission_type = $request->get('retailer_commission_type');
            $operator->api_id = $request->get('api_id');
            $operator->save();
            return response()->json(["success" => true, "message" => "Commission updated successfully."]);   
        }
        else{
            return response()->json(["success" => true, "message" => "Something went wrong"]);
        }
    }
    
    public function getManageDmtCommission(Request $request) {
        if($request->get('json_data')) {
            $data = Operators::where('status',1)->where('service_id',14)->get();
            return DataTables::of($data)->make(true);
        }
        else {
            return view('pages.comman.commissions.manage_dmt_commissions');
        }
    }
    
    public function postAddDmtCommission(Request $request) {
        $service_id = 14;
        $operator = new Operators();
        $operator->service_id = $service_id;
        $operator->name = $request->get('operator_name');
        $operator->op_code = $request->get('operator_name');
        $operator->sd_commission = $request->get('sd_commission');
        $operator->sd_commission_type = $request->get('sd_commission_type');
        $operator->dist_commission = $request->get('dist_commission');
        $operator->dist_commission_type = $request->get('dist_commission_type');
        $operator->commission = $request->get('retailer_commission');
        $operator->commission_type = $request->get('retailer_commission_type');
        $operator->fee = $request->get('fee');
        $operator->fee_type = $request->get('fee_type');
        $operator->min_amount = $request->get('min_amount');
        $operator->max_amount = $request->get('max_amount');
        $operator->op_image = 'money_bag.png';
        $operator->status = 1;
        $operator->save();
        
        Session::flash('success', 'DMT commission added successfully!');
        return redirect()->back()->withInput();  
    }
    
    public function getManageAepsCommission(Request $request) {
        if($request->get('json_data')) {
            $data = Operators::where('status',1)->where('service_id',21)->get();
            return DataTables::of($data)->make(true);
        }
        else {
            return view('pages.comman.commissions.manage_aeps_commissions');
        }
    }
    
    public function postAddAepsCommission(Request $request) {
        $service_id = 21;
        $operator = new Operators();
        $operator->service_id = $service_id;
        $operator->name = $request->get('operator_name');
        $operator->op_code = $request->get('operator_name');
        $operator->sd_commission = $request->get('sd_commission');
        $operator->sd_commission_type = $request->get('sd_commission_type');
        $operator->dist_commission = $request->get('dist_commission');
        $operator->dist_commission_type = $request->get('dist_commission_type');
        $operator->commission = $request->get('retailer_commission');
        $operator->commission_type = $request->get('retailer_commission_type');
        $operator->fee = 0;
        $operator->fee_type = 'flat';
        $operator->min_amount = $request->get('min_amount');
        $operator->max_amount = $request->get('max_amount');
        $operator->op_image = 'aeps.png';
        $operator->status = 1;
        $operator->save();
        
        Session::flash('success', 'Aeps commission added successfully!');
        return redirect()->back()->withInput();  
    }
    
    public function deleteOperator(Request $request) {
        
        $delete = Operators::where("id", $request->get('id'))->delete();
        
        if($delete)
            return json_encode(array("success" => true, "message" => "Record deleted"));
        else
            return json_encode(array("success" => false, "message" => "Record not found."));
    }
    
    public function getSendNotification(Request $request) {
        return view('pages.admin_panel.notifications.send_notifications');
    }
    
    public function postSendNotification(Request $request) {
        
        $title = $request->get('title');
        $message = $request->get('message');
        
        $users = User::where('user_type',2)->get();
        
        foreach($users as $user){
            $user_id = $user->id;
            // $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
            $create_notify = new Notifications();
            $create_notify->user_id = $user_id;
            $create_notify->title = $title;
            $create_notify->message = $message;
            $create_notify->save();
        }
        
        Session::flash('success', 'Notifications sent successfully!');
        return redirect()->back();  
    }

    public function getAddAppBanners(Request $request) {
        return view('pages.admin_panel.settings.add_banners');
    }
    
    public function postAddAppBanners(Request $request) {
        
        $title = $request->get('title');
        $link = $request->get('link');
        
        $create_banner = new AppBanner();
        $create_banner->title = $title;
        $create_banner->link = $link;
        
        if ($request->hasFile('banner_image')) {
            $file = $request->file('banner_image');
            $destinationPath = public_path('/uploads/app_banners');
            $imagename = 'IMG' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $imagename);
            $create_banner->image = $imagename;
        }
            
        $create_banner->save();
        
        Session::flash('success', 'Banner added successfully!');
        return redirect()->back();  
    }
    
    public function manageAppBanners(Request $request) {
        
        if($request->get('json_data')) {
            $data = AppBanner::where('status',1)->get();
            return DataTables::of($data)->make(true);
        }
        else{
            return view('pages.admin_panel.settings.manage_banners');    
        }
    }
    
    public function postDeleteBanner(Request $request) {
        $slider= AppBanner::find($request->get('id'));
        
        if(\File::exists(public_path() . '/uploads/app_banners/' . $slider->image)) {
            \File::delete(public_path() . '/uploads/app_banners/' . $slider->image);
        }
        
        $slider->delete();
        
        if($slider)
            return response()->json(['success' => true, 'message' => 'Banner image deleted successfully.']);
        else
            return response()->json(['success' => false, 'message' => 'Something went wrong please try again.']);
    }
    
    public function postAddMoneyToCustomer(Request $request) {

        $user_id = $request->get('user_id');

        $user = User::find($user_id);

        $amount = $request->get('wallet_amount');

        

        if($user) {

            

            $current_balance = $user->wallet;

            $final_balance = $user->wallet+$amount;

            

            $txn_id = $this->apiManager->txnId("WLT");

            

            $transaction = new Transactions();

            $transaction->transaction_id = $txn_id;

            $transaction->user_id = $user_id;

            $transaction->referenceId = Auth::User()->id;

            $transaction->event = 'WALLETLOAD';

            $transaction->amount = round($amount,5);

            $transaction->current_balance = $current_balance;

            $transaction->final_balance = $final_balance;

            $transaction->txn_type = 'Credit';

            $transaction->txn_note = 'Admin wallet load';

            $transaction->status = 1;

            $transaction->save();

        

            $user->wallet = $final_balance;

            $user->save();

            

            $title = 'Wallet Load';

            $message = "Dear User Your Wallet loaded with amount of Rs $amount. Your Current Balance is. $final_balance";

            if($user->user_type == 2) {

                $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);

            }

           // $sendsms = $this->apiManager->sendSMS($user_id,$message);

            

            Session::flash('success', 'Wallet loaded successfully');

            return redirect()->back();

        }

        else {

            Session::flash('error', 'Something went wrong please try again.');

            return redirect()->back();

        }

    }
    
    public function getAddGoogleCode(Request $request) {
        return view('pages.admin_panel.add_google');
    }
    
    public function postAddGoogleCode(Request $request) {
        
        $gc_ck = GoogleCode::where('gcode',$request->gcode)->first();
        if($gc_ck){
            Session::flash('error', 'Google Code already use.');

            return redirect()->back();
        }
        
        $gc = new GoogleCode();
        $gc->amount = $request->amt;
        $gc->gcode = $request->gcode;
        $gc->save();
        
        Session::flash('success', 'Add successfully');

        return redirect()->back();
    }
    
    public function getManageGoogleCode(Request $request) {
        return view('pages.admin_panel.manage_google');
    }
    
    public function getManageGoogleCodeData() {  
        
        $user_id = Auth::User()->id;
        
        $data = GoogleCode::get();
        
        return DataTables::of($data)->make(true);
    }
    
    public function getDeleteGoogleCode($id) { 
        $data = GoogleCode::find($id);
        if($data){
            $data->delete();
            Session::flash('success', 'Delete successfully');
            return redirect()->back();
        }else{
            Session::flash('error', 'Code Not Found.');
            return redirect()->back();
        }
    }
    
    public function getPurchaseGoogleCode(Request $request) {
        return view('pages.admin_panel.purchase_google');
    }
    
    public function getPurchaseGoogleCodeData() {  
        
        $user_id = Auth::User()->id;
        
        $data = GoogleCode::where('status',2)->get();
        
        return DataTables::of($data)->make(true);
    }
    
    public function getAddEvent(Request $request) {
        return view('pages.admin_panel.event.add_event');
    }
    
    public function postAddEvent(Request $request) {
        
        $data = new Event();
        $data->name = $request->name;
        $data->venue = $request->venue;
        $data->event_date = $request->event_date;
        $data->save();
        
        Session::flash('success', 'Add successfully');
            return redirect()->back();
    }
    
    public function getManageEvent(Request $request) {
        return view('pages.admin_panel.event.manage_event');
    }
    
    public function postManageEventData() {
        
        $user_id = Auth::User()->id;
        $user_type = Auth::User()->user_type;
            $data = Event::get();
        return DataTables::of($data)->make(true);
    }
    
    public function postDeleteEvent(Request $request) {
        $user_id = Auth::User()->id;
        $user_m = Event::where('id',$request->event_id)->delete();
        
        return response()->json(['success' => true, 'message' => 'Deleted successfully.' , 'status' => 1]);
    }
    
    public function getEventAttendance(Request $request) {
        return view('pages.admin_panel.event.event_attendance');
    }
    
    public function getEventAttendanceData(Request $request) {
        
        $user_id = Auth::User()->id;
        $user_type = Auth::User()->user_type;
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
            $data = EventEntry::select('users.name as user_name','events.name as event_name','event_entry.created_at as entry_date')->join('events','events.id','event_entry.event_id')->join('users','users.id','event_entry.user_id')->whereBetween('event_entry.created_at', array($from, $to))->get();
        return DataTables::of($data)->make(true);
    }
    
}