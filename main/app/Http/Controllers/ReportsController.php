<?php

namespace App\Http\Controllers;
use App\Classes\ApiManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use DataTables;
use Excel;
use Auth;
use DB;

use App\User;
use App\Model\Address; 
use App\Model\AepsTransaction;
use App\Model\Beneficiary;
use App\Model\Category;
use App\Model\City;
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

class ReportsController extends Controller
{
    public function __construct(ApiManager $apiManager) {
        $this->middleware('auth');
        $this->apiManager = $apiManager;
    }
    
    public function getRetailersTransactionsReport() {
        return view('pages.comman.reports.retailers_transactions_report');
    }
    
    public function getRetailersTransactionsReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        
        $event = $request->report_for;
    
        $data = [];
        
        if($user_type == 1) {
            $data = User::select('users.id','users.name','users.mobile',
            'distributor.name as dist_name','distributor.mobile as dist_mobile')
            ->leftjoin('users_mappings','users_mappings.user_id','users.id')
            ->leftjoin('users as distributor','distributor.id','users_mappings.toplevel_id')
            ->where('users.user_type','2')->get();
        }
        else {
            $data = User::select('users.id','users.name','users.mobile')
            ->join('users_mappings','users_mappings.user_id','users.id')
            ->where('users_mappings.toplevel_id',$user_id)
            ->where('users.user_type','2')->get();
        }
        
        if($event == 'CREDITVA' || $event == 'UPICREDIT' || $event == 'AEPSTXN') {
            
            foreach($data as  $key => $user) {
                $pending = Transactions::where('user_id',$user->id)->where('status','0')
                ->where('event',$event)->whereDate('created_at', $request->start_date)->sum('amount');
                
                $success = Transactions::where('user_id',$user->id)->where('status','1')
                ->where('event',$event)->whereDate('created_at', $request->start_date)->sum('amount');
                
                $failed = Transactions::where('user_id',$user->id)->where('status','2')
                ->where('event',$event)->whereDate('created_at', $request->start_date)->sum('amount');
                
                $data[$key]['success_sum'] = $success;
                $data[$key]['pending_sum'] = $pending;
                $data[$key]['failed_sum'] = $failed;
            }
        }
        else{
            
            switch ($event) {
              case "DMT":
                $events = $this->apiManager->getDmtEvents();
                break;
              case "BILL":
                $events = $this->apiManager->getBillPaymentEvents();
                 break;
              case "RECHARGE":
                $events = $this->apiManager->getRechargeEvents();
                 break;
              default:
                $events = [];
                break;
            }
            
            foreach($data as  $key => $user) {
                $pending = Transactions::where('user_id',$user->id)->where('status','0')
                ->whereIn('event',$events)->whereDate('created_at', $request->start_date)->sum('amount');
                
                $success = Transactions::where('user_id',$user->id)->where('status','1')
                ->whereIn('event',$events)->whereDate('created_at', $request->start_date)->sum('amount');
                
                $failed = Transactions::where('user_id',$user->id)->where('status','2')
                ->whereIn('event',$events)->whereDate('created_at', $request->start_date)->sum('amount');
                
                $data[$key]['success_sum'] = $success;
                $data[$key]['pending_sum'] = $pending;
                $data[$key]['failed_sum'] = $failed;
            }
        }
        
        $total_success_sum = collect($data)->sum('success_sum');
        $total_pending_sum = collect($data)->sum('pending_sum');
        $total_failed_sum = collect($data)->sum('failed_sum');
        
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum, 
        'total_pending_sum' => $total_pending_sum, 
        'total_failed_sum' => $total_failed_sum])->make(true);
    }
 
    public function getFundAddedReport() {
        return view('pages.comman.reports.fund_added_report');
    }
    
    public function getFundAddedReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);

        if($user_type == 1) {
            $data = Transactions::select('transactions.*','users.id as user_id','users.name','users.mobile as user_mobile')
            ->join('users','users.id','transactions.user_id')
            // ->join('users_mappings','users_mappings.user_id','users.id')
            // ->where('users_mappings.toplevel_id',$user_id)
            ->where('event','WALLETLOAD')
            ->whereBetween('transactions.created_at', array($from, $to))->get();
        }
        elseif($user_type == 3) {
            
            $data = Transactions::select('transactions.*','users.id as user_id','users.name','users.mobile as user_mobile')
            ->join('users','users.id','transactions.user_id')
            ->join('users_mappings','users_mappings.user_id','users.id')
            ->where('users_mappings.toplevel_id',$user_id)
            ->where('event','WALLETLOAD')
            ->whereBetween('transactions.created_at', array($from, $to))->get();
            
        }
        else {
           $data = [];
        }
        
        $total_success_sum = collect($data)->sum('amount');
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
    }
    
    public function getDmtReport() {
        return view('pages.comman.reports.dmt_report');
    }
    
    public function getDmtReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        $data = [];
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
        if($user_type == 1) { //Admin
            $data = Transactions::select('transactions.*','users.name','users.mobile')
            ->join('users','users.id','transactions.user_id')
            ->whereIn('transactions.event',['QUICKDMT','QUICKUPI'])
            ->whereBetween('transactions.created_at', array($from, $to))->get();
            
            $success_data = Transactions::select('transactions.*','users.name','users.mobile')
            ->join('users','users.id','transactions.user_id')
            ->where('transactions.status',1)
            ->whereIn('transactions.event',['QUICKDMT','QUICKUPI'])
            ->whereBetween('transactions.created_at', array($from, $to))->get();
        }
        
        $total_success_sum = collect($success_data)->sum('amount');
        $total_success_commission = collect($success_data)->sum('retailer_commission');
        $total_success_fee = collect($success_data)->sum('commission');
        
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum, 'total_success_fee' => $total_success_fee, 
        'total_success_commission' => $total_success_commission])->make(true);
    }
    
    public function getAepsReport() {
        return view('pages.comman.reports.aeps_report');
    }
    
    public function getAepsReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        $data = [];
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
        if($user_type == 1) { //Admin
            $data = Transactions::select('transactions.*','users.name','users.mobile')
            ->join('users','users.id','transactions.user_id')
            ->where('transactions.event','AEPSTXN')
            ->whereBetween('transactions.created_at', array($from, $to))->get();
            
            $success_data = Transactions::select('transactions.*','users.name','users.mobile')
            ->join('users','users.id','transactions.user_id')
            ->where('transactions.event','AEPSTXN')
            ->where('transactions.status',1)
            ->whereBetween('transactions.created_at', array($from, $to))->get();
            
        }
        
        $total_success_sum = collect($success_data)->sum('amount');
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
    }

    public function getUpiCollectionReport() {
        return view('pages.comman.reports.upi_collection_report');
    }
    
    public function getUpiCollectionReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        $data = [];
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
        if($user_type == 1) { //Admin
            $data = Transactions::select('transactions.*','users.name','users.mobile')
            ->join('users','users.id','transactions.user_id')
            ->where('transactions.event','UPICREDIT')
            ->whereBetween('transactions.created_at', array($from, $to))->get();
        }
        
        $total_success_sum = collect($data)->sum('amount');
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
    }
    
    public function getVACollectionReport() {
        return view('pages.comman.reports.va_collection_report');
    }
    
    public function getVACollectionReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        $data = [];
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
        if($user_type == 1) { //Admin
            $data = Transactions::select('transactions.*','users.name','users.mobile')
            ->join('users','users.id','transactions.user_id')
            ->where('transactions.event','CREDITVA')
            ->whereBetween('transactions.created_at', array($from, $to))->get();
        }
        
        $total_success_sum = collect($data)->sum('amount');
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
    }
    
    public function getRechargeReport() {
        return view('pages.comman.reports.recharge_report');
    }
    
    public function getRechargeReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        $data = [];
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
        $events = $this->apiManager->getRechargeEvents();
        
        if($user_type == 1) { //Admin
            $data = Transactions::select('transactions.*','users.name','users.mobile as user_mobile','operators.name as op_name')
            ->join('users','users.id','transactions.user_id')
            ->leftjoin('operators','operators.op_code','transactions.op_code')
            ->whereIn('transactions.event',$events)
            ->whereBetween('transactions.created_at', array($from, $to))->get();
            
            $success_data = Transactions::select('transactions.*','users.name','users.mobile','operators.name as op_name')
            ->join('users','users.id','transactions.user_id')
            ->leftjoin('operators','operators.op_code','transactions.op_code')
            ->whereIn('transactions.event',$events)
            ->where('transactions.status',1)
            ->whereBetween('transactions.created_at', array($from, $to))->get();
        }
        
        $total_success_sum = collect($success_data)->sum('amount');
        $total_success_commission = collect($success_data)->sum('commission');
        
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum, 'total_success_commission' => $total_success_commission])->make(true);
    }
    
    public function getBillPaymentsReport() {
        return view('pages.comman.reports.bill_payment_report');
    }
    
    public function getBillPaymentsReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        $data = []; 
        $success_data = [];
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
        $events = $this->apiManager->getBillPaymentEvents();
        
        if($user_type == 1) { //Admin
            $data = Transactions::select('transactions.*','users.name','users.mobile','operators.name as op_name')
            ->join('users','users.id','transactions.user_id')
            ->leftjoin('operators','operators.op_code','transactions.op_code')
            ->whereIn('transactions.event',$events)
            ->whereBetween('transactions.created_at', array($from, $to))->get();
            
            $success_data = Transactions::select('transactions.*','users.name','users.mobile','operators.name as op_name')
            ->join('users','users.id','transactions.user_id')
            ->leftjoin('operators','operators.op_code','transactions.op_code')
            ->whereIn('transactions.event',$events)
            ->where('transactions.status',1)
            ->whereBetween('transactions.created_at', array($from, $to))->get();
        }
        
        $total_success_sum = collect($success_data)->sum('amount');
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
    }
    
    public function getDistributorsCommissionReport() {
        return view('pages.comman.reports.distributors_commission_report');
    }
    
    public function getDistributorsCommissionReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        $data = [];
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
        $commissoin_events = $this->apiManager->getCommissionEvents();
        
        $data = User::select('users.id','users.name','users.mobile',
        DB::raw('sum(transactions.amount) as commission'))
        ->leftjoin('transactions','transactions.user_id','users.id')
        ->whereIn('transactions.event',$commissoin_events)     
        ->where('user_type',3)
        ->whereBetween('transactions.created_at', array($from, $to))
        ->groupby('users.id')
        ->get();

        $total_success_sum = collect($data)->sum('commission');
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
    }
    
    public function getRetailersCommissionReport() {
        return view('pages.comman.reports.retailers_commission_report');
    }
    
    public function getRetailersCommissionReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        $data = [];
        
        $users = User::where('user_type',2)->get();
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
        $commission_total = 0;
        $final_total = 0;
        
        foreach($users as $key => $user) {
            
            $retailer_id = $user->id;
            
            $txns = Transactions::where('user_id',$retailer_id)
            ->whereBetween('created_at', array($from, $to))
            ->whereNotIn('event', ['SETTLETXN','CPGTOPUP'])->get();
            
            $retailer_commission = 0;
            
            foreach($txns as $txn) {
                
                if($txn->event == "QUICKDMT") {
                    $retailer_commission = $retailer_commission + $txn->retailer_commission;
                }
                else {
                    $retailer_commission = $retailer_commission + $txn->commission;
                }
               
            }
            
            $data[$key]['id'] = $retailer_id;
            $data[$key]['name'] = $user->name;
            $data[$key]['mobile'] = $user->mobile;
            $data[$key]['commission'] = $retailer_commission;
        }
        
        // $data = User::select('users.id','users.name','users.mobile',
        // DB::raw('sum(transactions.commission) as commission'))
        // ->leftjoin('transactions','transactions.user_id','users.id')
        // ->where('transactions.commission','>',0)
        // ->where('transactions.status',1)
        // ->where('user_type',2)
        // ->groupby('users.id')
        // ->get();

        $total_success_sum = collect($data)->sum('commission');
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
    }
    
    public function getRefundReport() {
        return view('pages.comman.reports.refund_report');
    }
    
    public function getRefundReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        $data = [];
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
        if($user_type == 1) { //Admin
            $data = Transactions::select('transactions.*','users.name','users.mobile')
            ->join('users','users.id','transactions.user_id')
            ->where('transactions.event','REFUND')
            ->whereBetween('transactions.created_at', array($from, $to))->get();
        }
        
        $total_success_sum = collect($data)->sum('amount');
        return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
    }
    
    public function getDistributorWalletReport() {
        return view('pages.comman.reports.distributor_wallet_report');
    }
    
    public function getDistributorWalletReportData(Request $request) {
        
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        $data = [];
        
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);
        
        if($user_type == 1) { //Admin
            $data = Transactions::select('transactions.*','users.name','users.mobile')
            ->join('users','users.id','transactions.user_id')
            ->where('transactions.event','REFUND')
            ->whereBetween('transactions.created_at', array($from, $to))->get();
        }
        
        return Datatables::of($data)->make(true);
    }
    
    public function getUPIRequestAdminReport(Request $request) {
        return view('pages.comman.reports.upi_request_report');
    }
    
    public function getUPIRequestAdminReportData(Request $request) {
        
        $user_id = Auth::User()->id;  
        $from = $this->apiManager->fetchFromDate($request->start_date);
        $to = $this->apiManager->fetchToDate($request->end_date);

        $data = Transactions::select('transactions.*','users.name','users.mobile as user_mobile')
        ->join('users','users.id','transactions.user_id')
        ->where('event','UPIREQUEST')->whereBetween('transactions.created_at', array($from, $to))->get();
        
        $total_success_sum = Transactions::select('transactions.*','users.name','users.mobile as user_mobile')
        ->join('users','users.id','transactions.user_id')->where('transactions.status',1)
        ->where('event','UPIREQUEST')->whereBetween('transactions.created_at', array($from, $to))->sum('amount');
        
        $total_pending_sum = Transactions::select('transactions.*','users.name','users.mobile as user_mobile')
        ->join('users','users.id','transactions.user_id')->where('transactions.status',0)
        ->where('event','UPIREQUEST')->whereBetween('transactions.created_at', array($from, $to))->sum('amount');
        
        $total_failed_sum = Transactions::select('transactions.*','users.name','users.mobile as user_mobile')
        ->join('users','users.id','transactions.user_id')->where('transactions.status',2)
        ->where('event','UPIREQUEST')->whereBetween('transactions.created_at', array($from, $to))->sum('amount');
            
        // $total_success_sum = collect($data)->sum('success_sum');
        // $total_pending_sum = collect($data)->sum('pending_sum');
        // $total_failed_sum = collect($data)->sum('failed_sum');
        
         return Datatables::of($data)->with(['total_success_sum' => $total_success_sum, 
         'total_pending_sum' => $total_pending_sum, 
         'total_failed_sum' => $total_failed_sum])->make(true);
      
    }
    
    
    public function getAllSuccessTransactionReport(Request $request) {
        if($request->get('json_data')) {
            
            $data = Transactions::select('transactions.*','users.name','users.mobile as user_mobile')->where('transactions.status',1)
            ->join('users','users.id','transactions.user_id')
            ->whereNotIn('event',['DMTCOMM','OPCOMM','REFUND','ADDMONEY'])
            ->whereDate('transactions.created_at', $request->get('start_date'))
            ->get();
            
            $money_request_data = MoneyRequest::where('status',1)->where('approved_by',1)
            ->whereDate('created_at', $request->get('start_date'))->sum('amount');
            
            $total_success_sum = collect($data)->sum('amount') + $money_request_data;
            return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
        }
        else {
            return view('pages.comman.reports.all_success_transaction_report');
        }
    }
    
    public function getPackageActivationReport(Request $request) {
        if($request->get('json_data')) {
            
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);
        
            $data = Transactions::select('transactions.*','users.name','users.mobile as user_mobile')
            ->join('users','users.id','transactions.user_id')
            ->where('transactions.event','CPGPACKAGE')
            ->whereBetween('transactions.created_at', array($from, $to))
            ->get();
            
            $success_data = Transactions::select('transactions.*')
            ->where('transactions.status',1)->where('event','CPGPACKAGE')
            ->whereBetween('transactions.created_at', array($from, $to))
            ->get();
            
            $total_success_sum = collect($success_data)->sum('amount');
            return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
        }
        else {
            return view('pages.comman.reports.package_activation_report');
        }
    }
    
    
    public function getManagePendingTransactions(Request $request) {
        if($request->get('json_data')) {
            
            $from = $this->apiManager->fetchFromDate($request->start_date);
            $to = $this->apiManager->fetchToDate($request->end_date);
            
            $data = Transactions::select('transactions.*','users.name','users.mobile as user_mobile')
            ->join('users','users.id','transactions.user_id')
            ->where('transactions.status',0)
            ->whereIn('event',['PREPAID','DTH','QUICKDMT','QUICKUPI'])
            ->whereBetween('transactions.created_at', array($from, $to))
            ->get();
            
            $total_success_sum = collect($data)->sum('amount');
            return Datatables::of($data)->with(['total_success_sum' => $total_success_sum])->make(true);
        }
        else {
            return view('pages.comman.reports.manage_pending_transactions');
        }
    }
    
    public function updateTxnStatus(Request $request) {
        
        $id = $request->get('id');
        $txnstatus = $request->get('txnstatus');
        
        $txn = Transactions::where('id',$id)->where('status',0)->first();
        
        if($txn){
            
            $transaction_id = $txn->transaction_id;
            
            $event = $txn->event;
            
            if($event == "PREPAID" || $event == "DTH" ) {
                
                if($txnstatus == 2){
                    $refund =  $this->apiManager->refundAmount($transaction_id);
                    return response()->json(['success' => true, 'message' => 'Transaction updated successfully!']);      
                }
                else {
                    $txn->status = 1;
                    $txn->save();
                    return response()->json(['success' => true, 'message' => 'Transaction updated successfully!']);  
                }
            }
            elseif($event == "QUICKDMT" || $event == "QUICKUPI") {
                if($txnstatus == 2){

                    $trans = Transactions::where('transaction_id',$transaction_id)->first();

                    if($trans->status == 1 || $trans->status == 0 || $trans->status == 2){

                        // if($trans->status != 0){

                            

                        

                            $user = User::limit(1)->find($trans->user_id);

                            $cbalance = $user->wallet;

                            $fbalance = $user->wallet + $trans->amount - $trans->retailer_commission + $trans->commission;

                            $user->wallet = round($fbalance,5);

                            $user->save();

                        // }

                        

                        $updatetrans = Transactions::limit(1)->find($trans->id);

                        $updatetrans->status = 3;

                        $updatetrans->save();

                        

                        // if($trans->status != 0){

                            $txnid = $this->apiManager->txnId('RF');

                            

                            $transaction = new Transactions();

                            $transaction->user_id = $trans->user_id;

                            $transaction->transaction_id = $txnid;

                            $transaction->ref_txn_id = $trans->transaction_id;

                            $transaction->amount = round($trans->amount,5);

                            $transaction->retailer_commission = round($trans->retailer_commission,5);

                            $transaction->commission = round($trans->commission,5);

                            $transaction->current_balance = round($cbalance,5);

                            $transaction->final_balance = round($fbalance,5);

                            $transaction->event = 'REFUND';

                            $transaction->txn_type = 'Credit';

                            $transaction->status = 1;

                            $transaction->save();

                        // }

                    }

                    

                    return response()->json(['success' => true, 'message' => 'Transaction updated successfully!']);      

                }

                else {

                    

                    $txn->status = 1;

                    $txn->save();

                    return response()->json(['success' => true, 'message' => 'Transaction updated successfully!']);  

                }

                

                return response()->json(['success' => true, 'message' => 'Transaction updated successfully!']);   
            }
            else{
                return response()->json(['success' => false, 'message' => 'Transaction mismatch!']);      
            }
            
              
        }
        else{
            return response()->json(['success' => false, 'message' => 'Someething went wrong!']);    
        }
        
        
    }
    
    
    
}

