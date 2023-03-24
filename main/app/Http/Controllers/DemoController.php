<?php

namespace App\Http\Controllers;

use App\Classes\ApiManager;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

use Endroid\QrCode\QrCode;
use App\User;
use DB;

use App\Model\Address; 
use App\Model\AepsTransaction;
use App\Model\Beneficiary;
use App\Model\Category;
use App\Model\City;
use App\Model\Dispute;
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
use App\Model\TopupRequest;


class DemoController extends Controller
{
    public function __construct(ApiManager $apiManager){
        $this->apiManager = $apiManager;
    }
    

    public function demoRequest(Request $request) {
        
        echo $this->apiManager->distance(12.9716,77.5946,13.0318368,77.6047986,"K");
        // return response()->json(['success' => true, 'message' => 'Transaction Done!']);
        
    }

}