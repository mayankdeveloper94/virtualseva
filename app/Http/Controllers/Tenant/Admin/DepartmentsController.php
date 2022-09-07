<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Category;
use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Department;
use App\Lib\HelperTrait;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class DepartmentsController extends Controller
{

    use HelperTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $category = $request->get('category');
        $perPage = 25;

        if (!empty($keyword)) {

            $departments = Department::whereRaw("match(name,description) against (? IN NATURAL LANGUAGE MODE)", [$keyword]);
        } else {
            $departments = Department::orderBy('name');
        }

        if(!empty($category) && Category::find($category)){
            $categoryName = Category::find($category)->name;

            $departments = $departments->whereHas('categories',function($q) use ($category){
                $q->where('id',$category);
            });

        }
        else{
            $categoryName='';
        }

        $departments = $departments->paginate($perPage);

        return view('admin.departments.index', compact('departments','categoryName'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.departments.create');
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
            'picture' => 'file|max:10000|mimes:jpeg,png,gif',
        ]);
        $requestData = $request->all();


        if($request->hasFile('picture')) {
            $path =  $request->file('picture')->store(DEPARTMENTS,'public_uploads');

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
            $department = Department::create($requestData);

            //get categories
            $categories = [];
            foreach ($requestData as $key => $value) {
                if (preg_match('#cat_#', $key)) {
                    $categories[] = $value;
                }
            }


        $department->categories()->attach($categories);




        return redirect('admin/departments')->with('flash_message', __('admin.department').' '.__('admin.added'));
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
        $department = Department::findOrFail($id);

        return view('admin.departments.show', compact('department'));
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
        $department = Department::findOrFail($id);

        return view('admin.departments.edit', compact('department'));
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
        $this->validate($request,[
            'name'=>'required',
            'picture' => 'file|max:10000|mimes:jpeg,png,gif',
        ]);

        $requestData = $request->all();

        $department = Department::findOrFail($id);

        if($request->hasFile('picture')){
            @unlink($department->picture);

            $path =  $request->file('picture')->store(DEPARTMENTS,'public_uploads');

            $file = 'uploads/'.$path;
            $img = Image::make($file);

            $img->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($file);

            $requestData['picture'] = $file;
        }

        $department->update($requestData);

        $categories = [];
        foreach($requestData as $key=>$value){
            if(preg_match('#cat_#',$key)){
                $categories[] = $value;
            }
        }

        $department->categories()->sync($categories);

        return redirect('admin/departments')->with('flash_message', __('admin.department').' '.__('admin.updated'));
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
        $dept = Department::find($id);
        @unlink($dept->picture);

        Department::destroy($id);

        return redirect('admin/departments')->with('flash_message', __('admin.department').' '.__('admin.deleted'));
    }

    public function removePicture($id){
        $dept = Department::find($id);
        @unlink($dept->picture);
        $dept->picture = null;
        $dept->save();
        return back()->with('flash_message',__('admin.picture').' '.__('admin.deleted'));
    }

    public function members(Request $request,Department $department){
        $perPage = 30;

        $keyword = $request->get('search');
        $members = $department->users()->orderBy('name');

        if (!empty($keyword)) {

            $members = $members->whereRaw("match(name,email,telephone) against (? IN NATURAL LANGUAGE MODE)", [$keyword]);
        }
        $total = $members->count();
        $members = $members->paginate($perPage);
      //  $total = $department->users()->count();

        //get administrators
        $admins = $department->users()->wherePivot('department_admin', 1)->get();

        return view('admin.departments.members',compact('members','total','department','admins'));
    }

    public function allMembers(Request $request,Department $department){
        $keyword = $request->get('search');
        $perPage = 30;

        $deptName = null;

        if (!empty($keyword)) {
            $members = User::latest()->whereRaw("match(name,email,telephone) against (? IN NATURAL LANGUAGE MODE)", [$keyword]);
        } else {
            $members = User::latest();
        }

        $members->whereDoesntHave('departments', function (Builder $query) use ($department){
            $query->where('id', $department->id);
        });

        $total = $members->count();
        $members = $members->paginate($perPage);

        return view('admin.departments.all_members', compact('members','total','department'));
    }

    public function addMembers(Request $request,Department $department){

        $post = $request->all();
        unset($post['_token']);

        $ids= [];
        $count = 0;
        foreach($post as $value){
            $value = intval($value);
            if(is_int($value) && User::find($value)){
                $ids[] = intval($value);
                $count++;
            }
        }

        $department->users()->attach($ids);

        return back()->with('flash_message',$count.' '.__('admin.members-added'));
    }

    public function removeMembers(Request $request,Department $department){

        $post = $request->all();

        $ids= [];
        $count = 0;
       // dd($post);
        foreach($post as $key=>$value){
           // $value = intval($value);
            if(is_int($key) && User::find($value)){
                $ids[] = intval($value);
                $count++;
            }
        }


        $department->users()->detach($ids);

        return back()->with('flash_message',$count.' '.__('admin.members-removed'));
    }

    public function setAdmin(Request $request,Department $department,User $user,$mode=1){

        //$data = [$department->id=>['department_admin'=>$mode]];
        //$user->departments()->syncWithoutDetaching($data);
        $user->departments()->updateExistingPivot($department->id, ['department_admin'=>$mode]);


        if($mode==1){
            return back()->with('flash_message',__('admin.admin-added'));
        }
        else{
            return back()->with('flash_message',__('admin.admin-removed'));
        }

    }



}
