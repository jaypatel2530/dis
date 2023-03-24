<?php

namespace App\Http\Controllers;
use App\Classes\ApiManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Log;
use Endroid\QrCode\QrCode;
use Excel;
use Mail;
use Image;
use Auth;
use DataTables;
use App\User;
use App\Model\City;
use App\Model\UserBank;
use App\Model\Transactions;
use App\Model\ResponseTable;
use App\Model\KycDoc;
use App\Model\WhatsappSend;
use App\Model\UserMember;
use App\Model\Notifications;
use App\Model\Setting;
use App\Model\Event;
use App\Model\EventEntry;
use App\Model\Payment;

class HomeController extends Controller
{
    public function __construct(ApiManager $apiManager) {
        // $this->middleware('auth');
        $this->apiManager = $apiManager;
    }
    
    public function getsScanner()
    {
        $date = date('Y-m-d');
        $events = Event::whereDate('event_date', $date)->get();
        
        // $image_bg = public_path('').'/bg.png';
        // echo $image_bg;exit;
        return view('pages.scanner',compact('events'));
    }
    
    public function getTerms()
    {
        $ts = Setting::first();
        // $image_bg = public_path('').'/bg.png';
        // echo $image_bg;exit;
        return view('terms',compact('ts'));
    }
    
    public function postsScannerData(Request $request) {
        // Log::info($request->user_number);
        
        $user = User::where('member_id',$request->user_number)->first();
        // Log::info($user);
        if(!$user) {
           return response()->json(['success' => false, 'message' => 'Record not found!']);
        }else{
            if($user->status == 0){
                return response()->json(['success' => false, 'message' => 'Member is Inactive!']);
            }
            $check_event = EventEntry::where('event_id',$request->event_id)->where('user_id', $user->id)->first();
            if(!$check_event){
                $data = new EventEntry();
                $data->event_id = $request->event_id;
                $data->user_id = $user->id;
                $data->save();
                return response()->json(['success' => true, 'message' => 'Member Can Enter.','name'=>$user->name,'mobile'=>$user->mobile]);
            }else{
                return response()->json(['success' => false, 'message' => 'Already Enter!']);
            }
            
            return response()->json(['success' => true, 'message' => 'Record Fetch.','name'=>$user->name,'mobile'=>$user->mobile]);
            
        }
    }
    
    public function getMember()
    {
        // $image_bg = public_path('').'/bg.png';
        // echo $image_bg;exit;
        return view('member');
    }
    
    public function postMember(Request $request)
    {
        // print_r($_POST);exit;
        $user = User::where('mobile',$request->mobile)->where('status',0)->first();
        if($user){
            
        }else{
            $user = User::where('mobile',$request->mobile)->where('status',1)->first();
            if($user){
                Session::flash('error', 'Mobile Number Already Registered. Please contact dis.hemel@gmail.com for assistance with your membership.');
                return redirect()->back()->withInput();
            }
            $user = new User(); 
        }
        
        if(empty($request->membershipyear)){
            Session::flash('error', 'Please Select Membership Year');
            return redirect()->back()->withInput();
        }
        
        $usertoken = $this->apiManager->getUserToken();
        $password = $this->apiManager->getPassword(8);
        $member_id = $this->apiManager->memberID(strtoupper($_POST['last_name']));
        
           
        $user->name = ucfirst($_POST['first_name'].' '.$_POST['last_name']);
        $user->mobile = trim($request->mobile);
        $user->status = 1;
        $user->user_type = 2;
        $user->reg_completed = 1;
        $user->email = $request->get('email');
        $user->password = bcrypt($password);
        $user->user_token = $usertoken;
        
        $user->profession = trim($request->profession);
        $user->postcode = strtoupper(trim($request->postcode));
        $user->town = ucfirst($request->town);
        $user->address_line_1 = $request->address_line_1;
        $user->address_line_2 = $request->address_line_2;
        $user->gender = trim($request->gender);
        $user->whatsapp_status = $request->exist;
        $user->payment_type = $request->payment;
        if($request->payment == 'bank'){
            $user->payment_status = 0;
        }else{
            if($request->get('status') == 'COMPLETED'){
                $user->payment_status = 1;
            }else{
                $user->payment_status = 0;
            }
            
        }
        
        $user->member_id = $member_id;
        $user->membership = ucfirst($request->membership);
        $user->membership_year = $request->membershipyear;
        $user->membership_date = date('Y-m-d');
        
        //createqr
        $qrstring = $member_id;
        $qrCode = new QrCode($qrstring);
        header('Content-Type: '.$qrCode->getContentType());
        $qrCode->writeString();
        $qrcode_name = 'QR'.$member_id.'IMG'.time().'.png';
        $qrCode->writeFile(public_path().'/uploads/qrcodes/'.$qrcode_name);
        
        $user->qr_status = 1;
        $user->qr_img = $qrcode_name;
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
        
        if(isset($_POST['first_name_m'])){
            $count = count($_POST['first_name_m']);
            
            for($i=0;$i<$count;$i++){
                $user_m = new UserMember();
                $user_m->user_id = $user->id;
                $user_m->name = ucfirst($_POST['first_name_m'][$i].' '.$_POST['last_name_m'][$i]);
                $user_m->mobile = trim($_POST['mobile_m'][$i]);
                $user_m->email = $_POST['email_m'][$i];
                $user_m->gender = trim($_POST['gender_m'][$i]);
                $user_m->profession = trim($_POST['profession_m'][$i]);
                $user_m->relation_with_user = trim($_POST['relation_with_user'][$i]);
                $user_m->save();
            }
        }
        
        if($user->membership == 'Family'){
            $fee = 15;
        }else{
            $fee = 7;
        }
        
        
        $email = $request->get('email');
        $data = array('name'=>ucfirst($_POST['first_name'].' '.$_POST['last_name']),'login_id'=>$request->mobile,'password'=>$password,'qrcode_name'=>$qrcode_name,'member_id'=>$member_id,'user'=>$user,'fee'=>$fee);
              Mail::send('new_user', $data, function($message)use ($email) {
                 $message->to($email, 'DIS')->subject('Welcome To DIS');
                 $message->from('dis.hemel@gmail.com','DIS - Support Team');
              });
        
        
        Session::flash('success', 'Member Registred Successfully. Please check your email ID for a QR code and your permanent member ID.');
        // return redirect()->back();
        return view('payment_done',compact('user','qrcode_name','fee'));
        
    }
    
    public function getPayment()
    {
        $qrcode_name = "QRPATELSAT230128488114IMG1674848751.png";
        $user = User::find(306);
        if($user->membership == 'Family'){
            $fee = 15;
        }else{
            $fee = 7;
        }
        // $image_bg = public_path('').'/bg.png';
        // echo $image_bg;exit;
        return view('payment_done',compact('user','qrcode_name','fee'));
    }
    
    public function postGetMemberYears(Request $request)
    {
        $member_type = $request->member_type;
        // $image_bg = public_path('').'/bg.png';
        // echo $image_bg;exit;
        if($member_type == 'Family'){
            $fee = 15;
        }else{
            $fee = 7;
        }
        return view('member_year',compact('fee'));
    }
    
    public function postGetPaymentMethod(Request $request)
    {
        $len = $request->len;
        // $image_bg = public_path('').'/bg.png';
        // echo $image_bg;exit;
        return view('member_payment_method');
    }
    
    public function postAddMemberfFront(Request $request)
    {
        $len = $request->len;
        // $image_bg = public_path('').'/bg.png';
        // echo $image_bg;exit;
        return view('addmember',compact('len'));
    }
    
    
    
    public function getSendImage()
    {
        $nmae = WhatsappSend::where('mobile','!=','')->where('status','3')->get();
        
        print_r(count($nmae));
    }
    
    public function getImage()
    {
        // $image_bg = public_path('').'/bg.png';
        // echo $image_bg;exit;
        return view('image');
    }
    
    public function postImage(Request $request)
    {
        header('Content-Type: text/html; charset=UTF-8');
        
            // ->getRealPath()
        
            $this->validate($request, [
                'file' => 'required|image|mimes:jpg,jpeg,png,gif,svg|max:4096',
                'name' => 'required',
            ]);
            $image = $request->file('file');
            $input['file'] = time().'.'.$image->getClientOriginalExtension();
            $image_bg = 'bg1.png';
            $image_bg_1 = "/home4/mylarab3/public_html/main/public/bg.png";
            $image_bg_2 = "/home4/mylarab3/public_html/main/public/bg1.png";
            $imgFile = Image::make($image_bg_1);
            
            $watermark = Image::make($image->getRealPath());
            $watermark->resize(800, 900);
            $imgFile->insert($watermark , 'center', 400, 600);
            
            $imgFile->text($request->name,1200, 2050, function($font) { 
                $font->file('FontsFree-Net-PlaylistScript.ttf');
                $font->size(75);  
                $font->color('#a53860');   
                $font->align('right'); 
            })->save($image_bg_2);  
            
        
        return back()
        	->with('success','File successfully uploaded.')
        	->with('fileName',$image_bg);         
    }

    public function index() {
        $user_type = Auth::User()->user_type;
        $inflow_events = $this->apiManager->getInflowEvents();
        $outflow_events = $this->apiManager->getOutflowEvents();
        
        $today = Carbon::today();
        
        $todays_inflow = Transactions::whereIn('event',$inflow_events)->where('status',1)
         ->whereDate('transactions.created_at', $today)->sum('amount');
        $todays_outflow = Transactions::whereIn('event',$outflow_events)->where('status',1)
         ->whereDate('transactions.created_at', $today)->sum('amount');
        
        if($user_type == 1){
            
        
            $single_user = User::where('membership','Single')->where('user_type',2)->get()->groupBy(function($date) {
                                return Carbon::parse($date->created_at)->format('m'); // grouping by months
                            })->toArray();
            $usermcount_single = [];
            $userArr_single = "";
            
            foreach ($single_user as $key => $value) {
                $usermcount_single[(int)$key] = count($value);
            }
            
            for($i = 1; $i <= 12; $i++){
                if(!empty($usermcount_single[$i])){
                    $userArr_single .= $usermcount_single[$i];    
                }else{
                    $userArr_single .= 0;    
                }
                if($i != 12){
                    $userArr_single .= ","; 
                }
            }
            
            $family_user = User::where('membership','Family')->where('user_type',2)->get()->groupBy(function($date) {
                                return Carbon::parse($date->created_at)->format('m'); // grouping by months
                            })->toArray();
            $usermcount_family = [];
            $userArr_family = "";
            
            foreach ($family_user as $key => $value) {
                $usermcount_family[(int)$key] = count($value);
            }
            
            for($i = 1; $i <= 12; $i++){
                if(!empty($usermcount_family[$i])){
                    $userArr_family .= $usermcount_family[$i];    
                }else{
                    $userArr_family .= 0;    
                }
                if($i != 12){
                    $userArr_family .= ","; 
                }
            }
                            // print_r($usermcount);
                            // print_r($userArr);
                            // exit;
            $active_users = User::where('payment_status','1')->where('user_type',2)->count();
            $inactive_users = User::where('payment_status','0')->where('user_type',2)->count();
            return view('pages.dashboard',compact('userArr_single','userArr_family','active_users','inactive_users'));    
        }else{
        $notify = Notifications::where('user_id',Auth::user()->id)->get();
        return view('pages.dashboard',compact('notify'));
        }
    }
    
    public function getInflowOutflow(Request $request) {
        $date = $request->get('searchdate');
        
        $inflow_events = $this->apiManager->getInflowEvents();
        $outflow_events = $this->apiManager->getOutflowEvents();
        
        $inflow = Transactions::whereIn('event',$inflow_events)->where('status',1)
         ->whereDate('transactions.created_at', $date)->sum('amount');
        $outflow = Transactions::whereIn('event',$outflow_events)->where('status',1)
         ->whereDate('transactions.created_at', $date)->sum('amount');
        
        return response()->json([ 'success' => true,  'message' => 'Inflow and Outflow Data', 'inflow' => $inflow ,'outflow' => $outflow]);
    }
    
    public function getStatesCity(Request $request) {
        $state = $request->state;
        $cities = City::where('state_id',$state)->orderBy('name')->get();
        return view('pages.ajax.city',compact('cities'));
    }
    
    public function getEditStatesCity(Request $request) {
        $state = $request->state;
        $selected_city = $request->city;
        
        $cities = City::where('state_id',$state)->orderBy('name')->get();
        return view('pages.ajax.city',compact('cities','selected_city'));
    }
    
    
 
    public function getProfile() {
        $user_id = Auth::User()->id;
        $profile = $this->apiManager->getUserProfile($user_id);
        $user_bank = UserBank::where('user_id',$user_id)->first();
        
        return view('pages.profile',compact('profile','user_bank'));
    }
    
    public function postProfile(Request $request) {
        $user_id = Auth::User()->id;
        
        $user = User::find($user_id);
        
        if ($request->hasFile('profile_pic')) {
            $file = $request->file('profile_pic');
            $destinationPath = public_path('/uploads/profile_pics/');
            $imagename = 'IMG'. $user_id . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $imagename);
            $user->profile_pic = $imagename;
        }
        
        $user->save();
        
        Session::flash('success', 'Profile updated successfully!');
        return redirect()->back();
    }
    
    public function postAddUserBank(Request $request) {
        $user_id = Auth::User()->id;
        
        $check_bank = UserBank::where('user_id',$user_id)->first();
        if($check_bank) {
            Session::flash('error', 'Bank already registred!');
            return redirect()->back();
        }
        
        $bank = new UserBank();
        $bank->user_id = $user_id;
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
            
        Session::flash('success', 'Bank added successfully!');
        return redirect()->back();
    }
    
            
    public function checkMobileRegistration(Request $request) {
        $mobile = $request->get('mobile');
        $registered = User::where('mobile',$mobile)->first();
        
        if($registered) {
            return response()->json(['success' => true, 'message' => 'Mobile number already registered!']);
        }
        else{
            return response()->json(['success' => false, 'message' => 'Mobile number not registered!']);
        }
    }
    
    public function checkPanRegistration(Request $request) {
        $pan = $request->get('pan');
        $registered = KycDoc::where('pan_number',$pan)->first();
        
        if($registered) {
            return response()->json(['success' => true, 'message' => 'PAN number already registered!']);
        }
        else{
            return response()->json(['success' => false, 'message' => 'PAN number not registered!']);
        }
    }
    
    public function checkAadhaarRegistration(Request $request) {
        $aaddhar = $request->get('aadhaar_number');
        $registered = KycDoc::where('aadhaar_number',$aaddhar)->first();
        
        if($registered) {
            return response()->json(['success' => true, 'message' => 'Aadhaar number already registered!']);
        }
        else{
            return response()->json(['success' => false, 'message' => 'Aadhaar number not registered!']);
        }
    }
    
    
    public function getUPIRequest(Request $request) {
        
        $user_id = Auth::User()->id;
        
        $service_status = User::where('id',$user_id)->where('upi_request_service',0)->first();
        if($service_status) {
            Session::flash('error', 'Your service is disabled!');
            return redirect()->back();
        } 
         
        return view('pages.comman.upi_requests.upi_request');
    }

    public function postUPIRequest(Request $request) {
        
        $user_id = Auth::User()->id;
        $upi_id = $request->get('upi_id');
        $amount = $request->get('amount');
        
        $service_status = User::where('id',$user_id)->where('upi_request_service',0)->first();
        if($service_status) {
            Session::flash('error', 'Your service is disabled!');
            return redirect()->back();
        } 
         
        if(!$amount){
            Session::flash('error', 'Please enter valid amount');
            return redirect()->back();
        }
        
        $user_id = Auth::User()->id; 
        
        $user = User::find($user_id);
        $user_wallet = $user->wallet;
        $payment_type = 'UPI';
        $amount = $request->get('amount');
        $note = $request->get('note') ? $request->get('note') : 'RPPAY';
        $txn_note = $request->get('note') ? $request->get('note') : 'RPPAY';
        
        $txn_id = $this->apiManager->txnId("UPIR");
        
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
        
            Session::flash('success', 'UPI collection request sent');
            return redirect()->back();
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
            
            Session::flash('success', $message);
            return redirect()->back();
        } 
    }
    
    public function getUPIRequestReport(Request $request) {
        return view('pages.comman.upi_requests.upi_request_report');
    }
    
    public function getUPIRequestReportData(Request $request) {
        
        $user_id = Auth::User()->id;  
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);

        $data = Transactions::select('id','transaction_id','status','event','transfer_type','utr','referenceId',
        'ben_name','ben_ac_number','ben_ac_ifsc','commission','created_at','txn_note','reason','response_reason',
        \DB::raw('ROUND(current_balance,2) as current_balance'),
        \DB::raw('ROUND(final_balance,2) as final_balance'),
        \DB::raw('ROUND(amount,2) as amount'),
        \DB::raw('ROUND(commission,2) as commission'),
        \DB::raw('ROUND(retailer_commission,2) as retailer_commission')
        )
        ->where('user_id',$user_id)
        ->where('event','UPIREQUEST')
        ->whereBetween('created_at', array($from, $to))
        ->orderBy('id','desc')->get();
            
        $total_success_sum = collect($data)->sum('amount');
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])
        ->make(true);
      
    }
    
    public function changePassword(Request $request) {

        $user_id = Auth::User()->id;

        $user = User::find($user_id);

        $password = $request->password;

        $old_password = $request->old_password;

        $cnf_password = $request->cnf_password;

        

        if($password != $cnf_password){

            Session::flash('error', 'Password Not Match');

            return redirect()->back()->withInput();

        }

        $user_test = Hash::check($old_password, $user->password);

        if($user_test){

            
            $user->first_login = 1;
            $user->password = bcrypt($password);

            $user->save();

            Session::flash('success', 'Password Change Successfully.');

            return redirect()->back();

            

        }else{

            Session::flash('error', 'Old Password Wrong!!');

            return redirect()->back();

        }

    }
    

}