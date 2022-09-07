<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Department;
use App\Field;
use App\Http\Controllers\Tenant\Controller;

use App\Lib\HelperTrait;
use App\User;
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
        $keyword = $request->get('search');
        $department = $request->get('department');
        $perPage = 25;
        $deptName = null;

        if (!empty($keyword)) {
            $members = User::whereRaw("match(name,email,telephone) against (? IN NATURAL LANGUAGE MODE)", [$keyword]);
        } else {
            $members = User::orderBy('name');
        }
		
		$members = $members->when(setting('general_enable_kyc')==1, function ($q) {
						return $q->where('is_kyc_verified', 1);
					});

        if(!empty($department) && Department::find($department)){
             $deptName = Department::find($department)->name;

            $members = $members->whereHas('departments',function($q) use ($department){
                $q->where('id',$department);
            });


        }


        $members = $members->paginate($perPage);

        return view('admin.members.index', compact('members','deptName'));
    }

    public function export(Request $request){
        $keyword = $request->get('search');
        $department = $request->get('department');
        $perPage = 25;
        $deptName = null;

        if (!empty($keyword)) {
            $members = User::whereRaw("match(name,email,telephone) against (? IN NATURAL LANGUAGE MODE)", [$keyword]);
        } else {
            $members = User::orderBy('name');
        }

        if(!empty($department) && Department::find($department)){
            $deptName = Department::find($department)->name;

            $members = $members->whereHas('departments',function($q) use ($department){
                $q->where('id',$department);
            });


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
        $departments = Department::orderBy('name')->get();
        return view('admin.members.import', compact('departments'));
    }

    public function saveImport(Request $request){
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


                    $user->departments()->attach($requestData['departments']);



                }
                else{
                    $user =User::where('email',$dbData['email'])->first();
                    $user->departments()->syncWithoutDetaching($requestData['departments']);
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

    public function search(Request $request){
        $keyword = $request->get('term');

        if(empty($keyword)){
            return response()->json([]);
        }

        $members = User::whereRaw("match(name,email,telephone) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->limit(500)->get();

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

        return view('admin.members.create');
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


        $user->departments()->attach($requestData['departments']);

        return redirect('admin/members')->with('flash_message',  __('admin.member').' '.__('admin.added'));
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
        $member = User::findOrFail($id);

        return view('admin.members.show', compact('member'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {

        $member = User::findOrFail($id);

        return view('admin.members.edit', compact('member'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {

        $rules=[
            'name'=>'required',
            'gender'=>'required',
            'picture' => 'file|max:10000|mimes:jpeg,png,gif',
        ];
        $member = User::findOrFail($id);
        $requestData = $request->all();

        if($requestData['email']!=$member->email){
            $rules['email'] = 'required|unique:users';
        }
        else{
            $rules['email'] = 'required';
        }

        if(!empty($requestData['password'])){
            $rules['password']='min:6';
        }

        $this->validate($request,$rules);




        if($request->hasFile('picture')){
            @unlink($member->picture);

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
            $requestData['picture'] = $member->picture;
        }

        if(!empty($requestData['password'])){
            $requestData['password'] = Hash::make($requestData['password']);
        }
        else{
            unset($requestData['password']);
        }

        $member->update($requestData);

        $customValues = [];
        //attach custom values
        foreach(Field::orderBy('sort_order','asc')->get() as $field){
            if(isset($requestData[$field->id]))
            {
                $customValues[$field->id] = ['value'=>$requestData[$field->id]];
            }


        }

        $member->fields()->sync($customValues);

        if(isset($requestData['departments'])){
            $member->departments()->sync($requestData['departments']);
        }


        return redirect('admin/members')->with('flash_message',  __('admin.member').' '.__('admin.updated'));
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
        User::destroy($id);

        return redirect('admin/members')->with('flash_message',__('admin.member').' '.__('admin.deleted'));
    }

    public function removePicture($id){
        $user = User::find($id);
        @unlink($user->picture);
        $user->picture = null;
        $user->save();
        return back()->with('flash_message',__('admin.picture').' '.__('admin.deleted'));
    }

    public function kyc_verify(Request $request){
		if(setting('general_enable_kyc')!=1){
			return redirect()->route('admin.dashboard');
		}
        $keyword = $request->get('search');
        $department = $request->get('department');
        $perPage = 25;
        $deptName = null;

        if (!empty($keyword)) {
            $members = User::where('is_kyc_updated',1)->where('is_kyc_verified',0)->whereRaw("match(name,email,telephone) against (? IN NATURAL LANGUAGE MODE)", [$keyword]);
        } else {
            $members = User::where('is_kyc_updated',1)->where('is_kyc_verified',0)->orderBy('name');
        }

        if(!empty($department) && Department::find($department)){
             $deptName = Department::find($department)->name;

            $members = $members->whereHas('departments',function($q) use ($department){
                $q->where('id',$department);
            });


        }


        $members = $members->paginate($perPage);

        return view('admin.kyc.index', compact('members','deptName'));
    }

    public function kyc_verify_edit($id)
    {
		if(setting('general_enable_kyc')!=1){
			return redirect()->route('admin.dashboard');
		}
        $member = User::findOrFail($id);

        return view('admin.kyc.edit', compact('member'));
    }

    public function kyc_verify_update(Request $request, $id)
    {
		if(setting('general_enable_kyc')!=1){
			return redirect()->route('admin.dashboard');
		}
        $member = User::findOrFail($id);
        $requestData = $request->all();

        if($requestData['is_kyc_verified']==1){
            $member->is_kyc_updated = 1;
            $member->is_kyc_verified = 1;
			$member->ref = generateRefCode($id);
        }else{
            $member->is_kyc_verified = 0;
        }		
		
		$member->save();

        return redirect('admin/kyc_verify')->with('flash_message',__('admin.changes-saved'));
    }


}
