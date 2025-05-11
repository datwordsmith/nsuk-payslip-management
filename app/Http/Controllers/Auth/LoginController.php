<?php

namespace App\Http\Controllers\Auth;

use App\Models\UserSession;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
    //protected $redirectTo = RouteServiceProvider::HOME;
    protected function authenticated()
    {
        if(Auth::user()->status == '1') {

            // Log the user's login session
            UserSession::create([
                'user_id' => auth()->id(),
                'login_time' => now(),
            ]);

            return redirect('t/payslips')->with('status', 'Welcome to the Trackr Dashboard');
        } else {
            Auth::logout();
            return redirect('/')->with('error', 'Access Denied. Contact Administrator');
        }
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function logout(Request $request)
    {
        // Get the currently authenticated user
        $user = Auth::user();

        // Find the user's last login session
        $userSession = UserSession::where('user_id', $user->id)
        ->whereNull('logout_time')
        ->latest()
        ->first();

        // Update the logout time for the last login session
        if ($userSession) {
            $userSession->update(['logout_time' => now()]);
        }

        // Perform the default logout operation
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Redirect the user after logout
        return $this->loggedOut($request) ?: redirect('/');
    }
}
