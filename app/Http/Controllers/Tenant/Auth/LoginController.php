<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Field;
use App\Http\Controllers\Tenant\Controller;
use App\Lib\HelperTrait;
use App\Lib\RedirectTrait;
use App\User;
use Hybridauth\Hybridauth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to you300r home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    use HelperTrait;
    //use RedirectTrait;

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


    protected function sendFailedLoginResponse(Request $request)
    {
        return redirect()->route('login')
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors([
                $this->username() => __('auth.failed'),
            ]);
    }

    protected function redirectTo()
    {
        $user = Auth::user();
        
        if($user->role_id==1){
            return route('admin.dashboard');
        }
        else{

            //check if user belongs to any department
            $totalDepts = $user->departments()->count();
            // dd($totalDepts);
            if($totalDepts==1){
                $department = $user->departments()->first();
                $this->loginToDepartment($department->id);
                return route('member.dashboard');
            }
            elseif($totalDepts > 1){
                return route('site.select-department');
            }
            else{
                return route('site.departments');
            }

        }
    }


    //login via selected network. Then ask the user to select their role.
    public function social($network,Request $request){

        /*   $userProfile = new \stdClass();
           $userProfile->firstName = 'Ayokunle';
           return tview('auth.social',compact('userProfile'));*/

        //create config
        $config = array('callback'=> route('social.login',['network'=>$network]));

        if (setting('social_enable_facebook')==1) {
            $config['providers']['Facebook']=  array (
                "enabled" => true,
                "keys"    => array ( "id" => trim(setting('social_facebook_app_id')), "secret" => trim(setting('social_facebook_app_secret')) ),
                "scope"   => "email",
                "trustForwarded" => false
            );
        }

        if (setting('social_enable_google')==1) {
            $config['providers']['Google']=  array (
                "enabled" => true,
                "keys"    => array ( "id" => trim(setting('social_google_app_id')), "secret" => trim(setting('social_google_app_secret')) ),

            );
        }

        $config['debug_mode']=true;
        $config['debug_file']='hybridlog.txt';

        try{

            // create an instance for Hybridauth with the configuration file path as parameter
            $hybridauth = new Hybridauth( $config );

            // try to authenticate the user with twitter,
            // user will be redirected to Twitter for authentication,
            // if he already did, then Hybridauth will ignore this step and return an instance of the adapter
            $authSession = $hybridauth->authenticate($network);


            // get the user profile
            $userProfile = $authSession->getUserProfile();

            //check if the user exists
            $email = $userProfile->email;
            $user = User::where('email',$email)->first();
            if($user){
                $this->saveUserPicture($user,$userProfile->photoURL);
                //now login user
                Auth::login($user);
                return redirect($this->redirectTo());


            }
            elseif(empty(setting('general_enable_registration')) ){
                return redirect()->route('login')->with('flash_message',__('site.registration-disabled'));
            }

            $userClass = new \stdClass();
            $userClass->firstName = $userProfile->firstName;
            $userClass->lastName = $userProfile->lastName;
            $userClass->email = $userProfile->email;
            $userClass->phone = $userProfile->phone;
            $userClass->gender = $userProfile->gender;
            $userClass->photoURL= $userProfile->photoURL;

            //store user in session
            session()->put('social_user',serialize($userClass));


        }catch(\Exception $ex){
            return back()->with('flash_message',$ex->getMessage());
        }


        return redirect()->route('social.form');

    }



    public function completeSocial(){
        //now get all relevant fields
        if(setting('general_enable_registration')!=1){
            abort(401);
        }

        $user = session('social_user');
        if(!$user){
            return redirect()->route('login')->with('flash_message',__('site.invalid-login'));
        }

        $user  = unserialize($user);

        $name= $user->firstName.' '.$user->lastName;
        $member = $user;

        return view('auth.social.form',compact('name','user','member'));
    }

    public function saveSocial(Request $request){
        if(setting('general_enable_registration')!=1){
              abort(401);
        }

        $socialUser = session('social_user');
        if(!$socialUser){
            return redirect()->route('login')->with('flash_message',__('site.invalid-login'));
        }

        $socialUser  = unserialize($socialUser);

        $name= $socialUser->firstName.' '.$socialUser->lastName;

        $rules = [
            'telephone'=>'required'
        ];

        foreach(Field::get() as $field){

            if($field->type=='file'){
                $required = '';
                if($field->required==1){
                    $required = 'required|';
                }

                $rules['field_'.$field->id] = 'nullable|'.$required.'max:'.env('MAX_UPLOAD_SIZE').'|mimes:'.env('ALLOWED_FILES');
            }
            elseif($field->required==1){
                $rules['field_'.$field->id] = 'required';
            }
        }
        $this->validate($request,$rules);
        $requestData = $request->all();

        $requestData['name']= $name;
        $requestData['email'] = $socialUser->email;


        $password= Str::random(10);
        $requestData['password'] = Hash::make($password);
        $requestData['role_id'] = 2;




        //now save custom fields
        $fields = Field::get();

        //First create user
        $user= User::create($requestData);


        if(isset($socialUser->photoURL) && !empty($socialUser->photoURL)){
            $this->saveUserPicture($user,$socialUser->photoURL);
        }



        $customValues = [];
        //attach custom values
        foreach($fields as $field){
            if(isset($data['field_'.$field->id]))
            {
                $customValues[$field->id] = ['value'=>$data['field_'.$field->id]];
            }
        }

        $user->fields()->attach($customValues);

        //now login user
        Auth::login($user, true);
        session()->remove('social_user');

        //redirect to relevant page

            return redirect($this->redirectTo());


    }


    private function saveUserPicture(User $user,$photoUrl){
        if(!empty($user->picture)){
            return true;
        }

        if(empty($photoUrl)){
            return true;
        }

        //download the image
        try{
            $photoUrl = str_ireplace('=150','=500',$photoUrl);
            $remoteName = basename($photoUrl);
            $filename = time().'-'.$remoteName;
            $tempImage = tempnam(sys_get_temp_dir(), $filename);
            copy($photoUrl, $tempImage);

            $file = 'uploads/members/social-'.uniqid().'.jpg';
            $img = Image::make($tempImage);

            $img->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($file);

            $user->picture = $file;
            $user->save();
            @unlink($tempImage);

        }
        catch(\Exception $ex){
            //dd($ex->getMessage());
        }



    }

}
