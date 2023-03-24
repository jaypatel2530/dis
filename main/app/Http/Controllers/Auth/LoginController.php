<?php

namespace App\Http\Controllers\Auth;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Session;

use App\User;
use App\Classes\ApiManager;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ApiManager $apiManager)
    {
        $this->middleware('guest')->except('logout');
        $this->apiManager = $apiManager;
    }

    
    protected function credentials(Request $request)
    {
        if(is_numeric($request->get('email'))){
            return ['mobile'=>$request->get('email'),'password'=>$request->get('password')];
        }
        return $request->only($this->username(), 'password');
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if($this->guard()->validate($this->credentials($request))) {
            
            if(Auth::attempt(['mobile' => $request->email, 'password' => $request->password, 'user_type' => 1 ,'status' => 1])) {
                return redirect()->intended('dashboard');
            } 
            elseif(Auth::attempt(['mobile' => $request->email, 'password' => $request->password, 'user_type' => 2 ,'status' => 1])) {
                if(Auth::user()->first_login == 0){
                    Session::flash('error', 'Please Change Your Password!');
                    return redirect('profile');
                }
                return redirect()->intended('dashboard');
            }
            elseif(Auth::attempt(['mobile' => $request->email, 'password' => $request->password, 'user_type' => 4 ,'status' => 1])) {
                return redirect()->intended('dashboard');
            }
            else {
                $this->incrementLoginAttempts($request);
                return redirect()->back()->withInput($request->only('email', 'remember'))
                ->withErrors(['password' => 'Credentials dose not match our database.']);
            }
        } 
        else {
            $this->incrementLoginAttempts($request);
            return redirect()->back()->withInput($request->only('email', 'remember'))
            ->withErrors(['password' => 'Credentials dose not match our database.']);
        }
    }
    
    public function logout(Request $request){
        $this->guard()->logout();
        $request->session()->invalidate();
        return redirect('/login');
    }
    
    public function getForgotPassword(){
        return view('auth.forgot_password');
    }
    
    public function forgotPasswordSendOtp(Request $request){
        $mobile = $request->get('mobile');
        $user = User::where('mobile', $mobile)->where('status','1')->whereIn('user_type',[3,4])->first();
        if($user)  {
            $user_id = $user->id;
            $otp = $this->apiManager->getOTP();
        	$mobile = $user->mobile;
        
        	$update_user = User::find($user_id);
        	$update_user->otp = $otp;
        	$update_user->save();
    	    
    	    $message = 'Dear User, Your OTP is : ' .$otp;
    	    $sendsms = $this->apiManager->sendSMS($user_id,$message);
            
            return response()->json(['success' => true,'message' => 'OTP sent to your registred mobile number.']);
        }
        else {
            return response()->json(['success' => false,'message' => 'Please enter registred mobile number.']);
        }
    }
    
    public function forgotPasswordVerifyOtp(Request $request)
    {
        $mobile = $request->get('mobile');
        $otp = $request->get('otp');
        
        $user = User::where('mobile', $mobile)->where('otp', $otp)->where('status','1')->whereIn('user_type',[3,4])->first();
        
        if($user)  {
            
            $user_id = $user->id;
            
            $password = $this->apiManager->getPassword(6);
            $user->password = bcrypt($password); 
            $user->save();
            
            $message = "Your login password is ".$password;
            $sendsms = $this->apiManager->sendSMS($user_id,$message);
        
            return response()->json(['success' => true, 'message' => 'Your temporary password sent to your registered number']);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Invaid OTP']);
        }
    }
    
    
}
