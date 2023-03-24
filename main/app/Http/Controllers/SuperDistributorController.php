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

class SuperDistributorController extends Controller
{
    public function __construct(ApiManager $apiManager) {
        $this->middleware('auth');
        $this->apiManager = $apiManager;
    }
    
    public function getAddDistributor() {
        $states = State::where('country_id',1)->get();
        return view('pages.super_distributor_panel.distributors.add_distributor',compact('states'));
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
        $relation->super_distributor_id = $user_id;
        $relation->admin_id = 1;
        $relation->save();
        
        $sms_message = 'Welcome to '.env('APP_NAME').' family, Your user I’d : '.$mobile.', password : '.$password.' Login : https://my.rppay.in';
        $sendsms = $this->apiManager->sendSMS($dist_id,$sms_message);
        
        Session::flash('success', 'Distributor registred successfully');
        return redirect()->back();
    }
    
    public function getManageDistributors() {
        return view('pages.super_distributor_panel.distributors.manage_distributors');
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
    
    //WALLETLOAD
    public function postSDAddMoneyToCustomer(Request $request) {
        $user_id = $request->get('user_id');
        $amount = $request->get('wallet_amount');
        
        $user = User::find($user_id);
        
        if($user) {
            
            $sd_id = Auth::User()->id;
            $sd = User::find($sd_id);
            
            $sd_current_balance = $sd->wallet;
            $sd_final_balance = $sd->wallet-$amount;
            
            if($amount > $sd_current_balance) {
                Session::flash('error', 'Wallet balance is low!');
                return redirect()->back()->withInput();
            }
            
            $txnid = $this->apiManager->txnId("TRF");
            $transaction = new Transactions();
            $transaction->transaction_id = $txnid;
            $transaction->user_id = $sd_id;
            $transaction->referenceId = $user_id;
            $transaction->event = 'WALLETTRANSFER';
            $transaction->amount = round($amount,5);
            $transaction->current_balance = $sd_current_balance;
            $transaction->final_balance = $sd_final_balance;
            $transaction->txn_type = 'Debit';
            $transaction->txn_note = 'Wallet amount transfer to distributor';
            $transaction->status = 1;
            $transaction->save();
        
            $sd->wallet = $sd_final_balance;
            $sd->save();
            
            //Distributor entry
            $current_balance = $user->wallet;
            $final_balance = $user->wallet+$amount;
            
            $txn_id = $this->apiManager->txnId("WLT");
            $transaction = new Transactions();
            $transaction->transaction_id = $txn_id;
            $transaction->user_id = $user_id;
            $transaction->referenceId = $sd_id;
            $transaction->event = 'WALLETLOAD';
            $transaction->amount = round($amount,5);
            $transaction->current_balance = $current_balance;
            $transaction->final_balance = $final_balance;
            $transaction->txn_type = 'Credit';
            $transaction->txn_note = 'Super distributor loaded wallet';
            $transaction->status = 1;
            $transaction->save();
        
            $user->wallet = $final_balance;
            $user->save();
            
            $title = 'Wallet Load';
            $message = "Your wallet loaded with amount of Rs. ".$amount;
            if($user->user_type == 2) {
                $notification = $this->apiManager->sendfcmNotification($user_id, $title, $message);
            }
            $sendsms = $this->apiManager->sendSMS($user_id,$message);
            
            Session::flash('success', 'Wallet loaded successfully');
            return redirect()->back();
        }
        else {
            Session::flash('error', 'Something went wrong please try again.');
            return redirect()->back();
        }
    }
    
    
    
}