<?php

namespace App\Http\Controllers\Tenant\Member;

use App\Application;
use App\Department;
use App\Field;
use App\Http\Controllers\Tenant\Controller;

use App\Lib\HelperTrait;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class MembersController extends Controller
{
    use HelperTrait;

    public function __construct()
    {
        $this->middleware('user-limit')->only(['store','import','saveImport']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->authorize('dept_allows','show_members');
        $keyword = $request->get('search');
        $perPage = 24;
        $deptName = null;

        if (!empty($keyword)) {
            $members = getDepartment()->users()->whereRaw("match(name,email,telephone) against (? IN NATURAL LANGUAGE MODE)", [$keyword]);
        } else {
            $members = getDepartment()->users()->orderBy('name');
        }

        $total = $members->count();
        $members = $members->paginate($perPage);
        $admins = getDepartment()->users()->wherePivot('department_admin', 1)->get();

        return view('member.members.index', compact('members','deptName','admins','total'));
    }

    public function search(Request $request){
        $keyword = $request->get('term');

        if(empty($keyword)){
            return response()->json([]);
        }

        $members = getDepartment()->users()->whereRaw("match(name,email,telephone) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->limit(500)->get();

        $formattedUsers = [];

        foreach($members as $member){
            if($request->get('format')=='number'){
                $formattedUsers[] = ['id'=>$member->id,'text'=>"{$member->name} ({$member->telephone})"];
            }
            else{
                $formattedUsers[] = ['id'=>$member->id,'text'=>"{$member->name} <{$member->email}>"];
            }

        }

       // $formattedUsers['pagination']=['more'=>false];
        return response()->json($formattedUsers);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('administer');
        return view('member.members.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $this->authorize('administer');

        //check if this email exists
        $user = User::where('email',$request->email)->first();
        if($user){
            $deptId = getDepartment()->id;
            $user->departments()->attach($deptId);

            try{
                $msg = __('admin.new-dept-msg',['name'=>$user->name,'site'=>setting('general_site_name'),'dept'=>getDepartment()->name]);
                $this->sendEmail($user->email,__('admin.dept-welcome',['name'=>getDepartment()->name]),$msg);
            }catch (\Exception $ex){

            }

            return redirect('member/members')->with('flash_message',  __('admin.member-exists'));

        }

        $this->validate($request,[
            'name'=>'required',
            'email'=>'required|unique:users',
            'password'=>'required|min:6',
            'gender'=>'required',
            'picture' => 'file|max:10000|mimes:jpeg,png,gif',
        ]);

        $requestData = $request->all();


        $requestData['password'] = Hash::make($requestData['password']);
        $requestData['role_id'] = 2;
        $requestData['status']=1;

        if($request->hasFile('picture')) {

            $path =  $request->file('picture')->store(MEMBERS,'public_uploads');

            $file = 'uploads/'.$path;
            $img = Image::make($file);

            $img->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($file);

            $requestData['picture'] = $file;
        }
        else{

            $requestData['picture'] =null;
        }

        $user = User::create($requestData);

        try{
            $msg = __('admin.new-account-msg',['name'=>$user->name,'site'=>setting('general_site_name'),'email'=>$user->email,'password'=>$request->password]);
            $this->sendEmail($user->email,__('admin.new-account'),$msg);
        }catch (\Exception $ex){

        }

        $customValues = [];
        //attach custom values
        foreach(Field::orderBy('sort_order','asc')->get() as $field){
            if(isset($requestData[$field->id]))
            {
                $customValues[$field->id] = ['value'=>$requestData[$field->id]];
            }


        }

        $user->fields()->attach($customValues);

        $deptId = getDepartment()->id;
        $user->departments()->attach($deptId);

        return redirect('member/members')->with('flash_message',  __('admin.member').' '.__('admin.added'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $this->authorize('dept_allows','show_members');

        $member = User::findOrFail($id);

        //ensure this member belongs to this department
        $this->authorize('department_member',$member);

        return view('member.members.show', compact('member'));
    }




    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        $this->authorize('administer');

        $user= User::find($id);
        $user->departments()->detach(getDepartment()->id);

        return redirect('member/members')->with('flash_message',__('admin.member').' '.__('admin.deleted'));
    }

    public function applications(Request $request){

        $department = getDepartment();

        $applications = $department->applications();
        if(!empty($request->status)){
            $applications->where('status',$request->status);
        }

        $keyword = $request->get('search');

        if (!empty($keyword)) {
            $applications->whereHas('user',function(Builder $q) use ($keyword) {
                $q->whereRaw("match(name,email,telephone) against (? IN NATURAL LANGUAGE MODE)", [$keyword]);
            });

        }

        $applications = $applications->latest()->paginate(30);

        return view('member.members.applications',compact('applications'));
    }

    public function application(Application $application){

        $this->authorize('view',$application);

        $fields = $application->user->departmentFields()->where('department_id',$application->department_id)->get();

        return view('member.members.application',compact('application','fields'));
    }

    public function updateApplication(Request $request,Application $application){
        $this->authorize('view',$application);

        $this->validate($request,[
            'status'=>'required'
        ]);

        $requestData= $request->all();
        $application->fill($requestData);
        $application->save();

        //if the user has been approved, add to department
        $department = getDepartment();
        $user = $application->user;
        $deptId = getDepartment()->id;
        if($requestData['status']=='a'){
            //send welcome email to user


            $user->departments()->sync([$deptId], false);

            try{
                $msg = __('admin.new-dept-msg',['name'=>$user->name,'site'=>setting('general_site_name'),'dept'=>getDepartment()->name]);
                if(!empty($request->comment))
                {
                    $msg .= '<strong>'.__('admin.comment').'</strong>: '.$request->comment.'<br/> ';
                }
                $this->sendEmail($user->email,__('admin.dept-welcome',['name'=>getDepartment()->name]),$msg);
            }catch (\Exception $ex){

            }

        }
        else{

            $user->departments()->detach($deptId);
            try{
                $msg = __('admin.new-dept-reject-msg',['name'=>$user->name,'site'=>setting('general_site_name'),'dept'=>getDepartment()->name]);
                if(!empty($request->comment))
                {
                    $msg .= '<strong>'.__('admin.comment').'</strong>: '.$request->comment.'<br/> ';
                }
                $this->sendEmail($user->email,__('admin.dept-reject',['name'=>getDepartment()->name]),$msg);
            }catch (\Exception $ex){

            }

        }


        return redirect()->route('member.members.applications')->with('flash_message',__('admin.changes-saved'));


    }



    public function setAdmin(Request $request,User $user,$mode=1){
        $this->authorize('administer');

        $department = getDepartment();
        $user->departments()->updateExistingPivot($department->id, ['department_admin'=>$mode]);


        if($mode==1){
            return back()->with('flash_message',__('admin.admin-added'));
        }
        else{
            return back()->with('flash_message',__('admin.admin-removed'));
        }

    }



    public function export(Request $request){
        $this->authorize('administer');
        $keyword = $request->get('search');
        $perPage = 25;
        $deptName = null;

        if (!empty($keyword)) {
            $members = getDepartment()->users()->whereRaw("match(name,email,telephone) against (? IN NATURAL LANGUAGE MODE)", [$keyword]);
        } else {
            $members = getDepartment()->users()->orderBy('name');
        }

        //  $members = $members->get();

        $file = "export.txt";
        if (file_exists($file)) {
            unlink($file);
        }

        $myfile = fopen($file, "w") or die("Unable to open file!");
        $columns = array('#',__('admin.name'),__('admin.telephone'),__('admin.email'),__('admin.gender'),__('admin.about'));

        $fields = Field::where('enabled',1)->orderBy('sort_order')->get();

        foreach($fields as $row){
            $columns[] = $row->name;
        }
        fputcsv($myfile,$columns );
        foreach($members->cursor() as $row){
            $csvData = array($row->id,$row->name,$row->telephone,$row->email,gender($row->gender),$row->about);

            foreach($fields as $field){

                if($field->users()->where('id',$row->id)->first()){
                    $fieldRow = $field->users()->where('id',$row->id)->first()->pivot->value;

                }
                else{
                    $fieldRow='';
                }

                if(empty($fieldRow)){
                    $csvData[] ='';
                }
                elseif($field->type=='checkbox'){
                    $csvData[] = boolToString($fieldRow);
                }
                else{
                    $csvData[] = $fieldRow ;
                }


            }

            fputcsv($myfile,$csvData );
        }

        fclose($myfile);
        header('Content-type: text/csv');
        // It will be called downloaded.pdf
        header('Content-Disposition: attachment; filename="member_export_'.date('d/M/Y').'.csv"');

        // The PDF source is in original.pdf
        readfile($file);
        unlink($file);
        exit();

    }

    public function import(){
        $this->authorize('administer');
        return view('member.members.import');
    }

    public function saveImport(Request $request){
        $this->authorize('administer');
        $this->validate($request,[
            'file' => 'file|max:10000|mimes:csv,txt',
        ]);


        $requestData = $request->all();

        $file = $request->file->path();
        $file = fopen($file,"r");

        $all_rows = array();
        $header = null;
        while ($row = fgetcsv($file)) {
            if ($header === null) {
                $header = $row;
                continue;
            }
            $all_rows[] = array_combine($header, $row);
        }

        $total = 0;
        $failure = 0;
        foreach($all_rows as $value){
            $dbData = array();
            $dbData['name']=$value['name'];

            $dbData['telephone']=$value['telephone'];
            $dbData['email']=$value['email'];
            $dbData['gender']= strtolower(substr($value['gender'],0,1));

            if(empty($dbData['name']) || empty($dbData['email']) || empty($dbData['gender'])){
                continue;
            }

            $dbData['status']=1;
            $userPassword = Str::random(6);;
            $dbData['password']= Hash::make($userPassword);
            $dbData['role_id'] = 2;


            // dd($dbData);

            try{
                if(!User::where('email',$dbData['email'])->first()){
                    $total++;
                    $user = User::create($dbData);

                    try{
                        $msg = __('admin.new-account-msg',['name'=>$user->name,'site'=>setting('general_site_name'),'email'=>$user->email,'password'=>$userPassword]);
                        $this->sendEmail($user->email,__('admin.new-account'),$msg);
                    }catch (\Exception $ex){

                    }

                    $customValues = [];
                    //attach custom values
                    foreach(Field::orderBy('sort_order','asc')->get() as $field){
                        if(isset($requestData[$field->id]))
                        {
                            $customValues[$field->id] = ['value'=>''];
                        }


                    }

                    $user->fields()->attach($customValues);


                    $user->departments()->syncWithoutDetaching(getDepartment()->id);



                }
                else{
                    $user =User::where('email',$dbData['email'])->first();
                    $user->departments()->syncWithoutDetaching(getDepartment()->id);
                }

            }
            catch(\Exception $ex){
                $failure++;
            }

        }
        $message = __('admin.import-msg',['total'=>$total]);
        if(!empty($failure)){
            //   $message .= __('admin.import-fail',['failure'=>$failure]);
        }


        return back()->with('flash_message',$message);


    }


}
