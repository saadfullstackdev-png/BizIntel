<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Helpers\GeneralFunctions;
use App\Models\UserLoginLogs;
use Socialite;
use Auth;
use Response;
use Session;
use DB;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User;

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
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/admin/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * The user has been authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     * @return mixed
     */
    public function authenticated(Request $request, $user)
    {

        $session_id = Session::getId();
        $ip = Null;
        $deep_detect = TRUE;
        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
            $ip = $_SERVER["REMOTE_ADDR"];
            if ($deep_detect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }
        // $ip = '202.163.113.36';

        $xml = @simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=" . $ip);

        $MAC = exec('getmac');
        $user_mac = strtok($MAC, ' ');

        //dd($xml);
        // $country = (string) $xml->geoplugin_countryName;
        // $city =  (string) $xml->geoplugin_city;
        // $area =  (string) $xml->geoplugin_areaCode;
        // $code =  (string) $xml->geoplugin_countryCode;
        // $long =  (string) $xml->geoplugin_longitude;
        // $lat =  (string) $xml->geoplugin_latitude;

        $country = 'Pakistan';
        $city = 'Lahore';
        $area = 'Johar Town';
        $code = '92';
        $long = '31.52340433518787';
        $lat = '74.34378613140484';

        $user_agent = $request->header('User-Agent');
        $os_platform = "Unknown OS Platform";
        $os_array = array(
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 10/i' => 'Windows 10',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        );
        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $os_platform = $value;
            }
        }
        $browser = "Unknown Browser";
        $browser_array = array(
            '/msie/i' => 'Internet Explorer',
            '/firefox/i' => 'Firefox',
            '/safari/i' => 'Safari',
            '/chrome/i' => 'Chrome',
            '/edge/i' => 'Edge',
            '/opera/i' => 'Opera',
            '/netscape/i' => 'Netscape',
            '/maxthon/i' => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i' => 'Handheld Browser'
        );
        foreach ($browser_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $browser = $value;
            }
        }
        $userLoginLog = new UserLoginLogs();
        $userLoginLog->user_id = $user->id;
        $userLoginLog->user_ip = $ip;
        $userLoginLog->user_mac = $user_mac;
        $userLoginLog->longitude = $long;
        $userLoginLog->latitude = $lat;
        $userLoginLog->location = $city . ',' . $country . ',' . $code;
        $userLoginLog->country_code = $code;
        $userLoginLog->country_code = $code;
        $userLoginLog->machine_name = gethostname();
        $userLoginLog->browser = $browser;
        $userLoginLog->os = $os_platform;
        $userLoginLog->country = $country;
        $userLoginLog->login_time = Carbon::now();
        $userLoginLog->session_id = Session::getId();
        $userLoginLog->account_id = Auth::User()->account_id;
        $userLoginLog->save();
        $account_id = Auth::User()->account_id;
        session(['account_id' => $account_id]);
        $account = DB::table('accounts')->find($account_id);
        session(['account' => $account]);
    }

    public function login(\Illuminate\Http\Request $request)
    {
        $this->validateLogin($request);
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }
        // This section is the only change
        if ($this->guard()->validate($this->credentials($request))) {
            $user = $this->guard()->getLastAttempted();
            $user_info = User::where('email', $request->email)->where('un_subscribe', '0')->first();
            // Make sure the user is active
            if ($user->active && $this->attemptLogin($request)) {
                if ($request->isLogin) {
                    Auth::logoutOtherDevices($request->password);
                    DB::table('user_login_logs')
                        ->where('user_id', $user_info->id)
                        ->update(['logout_time' => Carbon::now()]);
                    return $this->sendLoginResponse($request);
                } else {
                    if ($user_info->session_id == null) {
                        return $this->sendLoginResponse($request);
                    } else {
                        $request['isLogin'] = true;
                        Auth::logout();
                        Session::flash('isLogin', true);
                        return redirect()
                            ->back()
                            ->withInput($request->only($this->username(), 'remember', 'isLogin'))
                            ->withErrors(['active' => 'You are already login to other devices. Please first logout from other devices']);
                    }
                }
            } else {
                // Increment the failed login attempts and redirect back to the
                // login form with an error message.
                $this->incrementLoginAttempts($request);
                return redirect()
                    ->back()
                    ->withInput($request->only($this->username(), 'remember'))
                    ->withErrors(['active' => 'Your account has been deactivated, please contact administrator.']);
            }
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }
    /* public function login(\Illuminate\Http\Request $request) {
         $this->validateLogin($request);

         // If the class is using the ThrottlesLogins trait, we can automatically throttle
         // the login attempts for this application. We'll key this by the username and
         // the IP address of the client making these requests into this application.
         if ($this->hasTooManyLoginAttempts($request)) {
             $this->fireLockoutEvent($request);
             return $this->sendLockoutResponse($request);
         }

         // This section is the only change
         if ($this->guard()->validate($this->credentials($request))) {
             $user = $this->guard()->getLastAttempted();

             // Make sure the user is active
             if ($user->active && $this->attemptLogin($request)) {
                 // Send the normal successful login response
                 return $this->sendLoginResponse($request);
             } else {
                 // Increment the failed login attempts and redirect back to the
                 // login form with an error message.
                 $this->incrementLoginAttempts($request);
                 return redirect()
                     ->back()
                     ->withInput($request->only($this->username(), 'remember'))
                     ->withErrors(['active' => 'Your account has been deactivated, please contact administrator.']);
             }
         }

         // If the login attempt was unsuccessful we will increment the number of attempts
         // to login and redirect the user back to the login form. Of course, when this
         // user surpasses their maximum number of attempts they will get locked out.
         $this->incrementLoginAttempts($request);

         return $this->sendFailedLoginResponse($request);
     } */

    /**
     * Check user session.
     *
     * @return Response
     */
    public function checkSession()
    {
        return Response::json(['guest' => Auth::guest()]);
    }


    public function logout(Request $request)
    {
        $user_info = User::find(Auth::id());
        $user_info->session_id = null;
        $user_info->save();
        $current_user_session_id = Session::getId();
        $update_user_login = UserLoginLogs::where('session_id', '=', $current_user_session_id)->first();
        if ($update_user_login) {
            $update_user_login->logout_time = Carbon::now();
            $update_user_login->update();
        }

        $this->guard()->logout();
        $request->session()->invalidate();
        return $this->loggedOut($request) ?: redirect('/');
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $previous_session = Auth::User()->session_id;
        if ($previous_session) {
            Session::getHandler()->destroy($previous_session);
        }

        Auth::user()->session_id = Session::getId();
        Auth::user()->save();
        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended($this->redirectPath());
    }
}
