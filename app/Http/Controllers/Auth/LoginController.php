<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    protected function redirectTo()
    {
        $user = Auth::user();
        if($user->role_id==1){
            return route('admin.dashboard');
        }
        else{
            return route('user.dashboard');
        }
    }

    protected function credentials(Request $request)
    {
        $c = $request->only($this->username(), 'password');
        return array_merge($c, ['enabled' => true]);
    }
    
    public function logout(Request $request)
    {
        $this->guard()->logout();
    
        $request->session()->flush();
    
        $request->session()->regenerate();
    
        return redirect('/login');
    }




}
