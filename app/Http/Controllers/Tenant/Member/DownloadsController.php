<?php

namespace App\Http\Controllers\Tenant\Member;

use App\DownloadFile;
use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Download;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DownloadsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 25;

        $user = Auth::user();
        if(isDeptAdmin($user) || $user->role_id==1){
            if (!empty($keyword)) {
                $downloads = getDepartment()->downloads()->whereRaw("match(name,description) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->paginate($perPage);
            } else {
                $downloads = getDepartment()->downloads()->latest()->paginate($perPage);
            }

        }
        else{
            if (!empty($keyword)) {
                $downloads = $user->downloads()->where('department_id',getDepartment()->id)->whereRaw("match(name,description) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->paginate($perPage);
            } else {
                $downloads = $user->downloads()->where('department_id',getDepartment()->id)->latest()->paginate($perPage);
            }

        }
        $manage = true;
        $title = ucfirst(__('admin.department')).' '.__('admin.downloads');
        $route = url('/member/downloads');

        return view('member.downloads.index', compact('downloads','manage','title','route'));
    }

    public function browse(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 25;

        if (!empty($keyword)) {
            $downloads = getDepartment()->downloads()->whereRaw("match(name,description) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->paginate($perPage);
        } else {
            $downloads = getDepartment()->downloads()->latest()->paginate($perPage);
        }

        $manage=false;
        $title = __('admin.view').' '.__('admin.downloads');
        $route = route('member.downloads.browse');

        return view('member.downloads.index', compact('downloads','manage','title','route'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('dept_allows','allow_members_upload');
        $msgId = Str::random(10);
        return view('member.downloads.create',compact('msgId'));
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
        $this->authorize('dept_allows','allow_members_upload');
        $this->validate($request,[
            'name'=>'required'
        ]);

        $requestData = $request->all();
        $requestData['user_id']= Auth::user()->id;
        $requestData['department_id'] = getDepartment()->id;


        $download = Download::create($requestData);

        //get email id
        $messageId = $requestData['msg_id'];

        //check for any attachments
        $path = TEMP_DIR.$messageId;

        //scan directory for files
        if(is_dir($path)){


            $files = scandir($path);
            $files = array_diff(scandir($path), array('.', '..'));

            if(count($files) > 0){
                //check for directory
                $destDir = DOWNLOAD_PATH.'/'.$download->id;
                if(!is_dir($destDir)){
                    mkdir($destDir);
                }

                foreach($files as $value){
                    $newName = $destDir.'/'.$value;
                    $oldName = $path.'/'.$value;
                    rename($oldName,$newName);
                    //attach record
                    $download->downloadFiles()->create([
                        'file_path'=>$newName
                    ]);

                }
            }
            @rmdir($path);
        }

        return redirect('member/downloads')->with('flash_message', __('admin.download').' '.__('admin.added'));
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
        $download = Download::findOrFail($id);

        return view('member.downloads.show', compact('download'));
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
        $this->authorize('dept_allows','allow_members_upload');
        $download = Download::findOrFail($id);

        if(!isDeptAdmin(Auth::user())){
            $this->authorize('is_owner',$download);
        }



        $this->authorize('update',$download);

        return view('member.downloads.edit', compact('download'));
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
        $this->authorize('dept_allows','allow_members_upload');
        $this->validate($request,[
            'name'=>'required'
        ]);

        $requestData = $request->all();

        $download = Download::findOrFail($id);

        if(!isDeptAdmin(Auth::user())){
            $this->authorize('is_owner',$download);
        }

        $this->authorize('update',$download);
        $download->update($requestData);

        return redirect('member/downloads')->with('flash_message', __('admin.changes-saved'));
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
        $this->authorize('delete',Download::find($id));

        Download::destroy($id);

        return redirect('member/downloads')->with('flash_message', __('admin.deleted'));
    }

    public function downloadAttachment(DownloadFile $downloadFile){
        $this->authorize('view',$downloadFile->download);
        $path = $downloadFile->file_path;

        header('Content-type: '.getFileMimeType($path));

// It will be called downloaded.pdf
        header('Content-Disposition: attachment; filename="'.basename($path).'"');

// The PDF source is in original.pdf
        readfile($path);
        exit();
    }

    public function downloadAttachments(Download $download){
        $this->authorize('view',$download);
        $zipname = safeUrl($download->name).'.zip';
        $zip = new \ZipArchive;
        $zip->open($zipname, \ZipArchive::CREATE);


        $deleteFiles = [];
        foreach ($download->downloadFiles as $row) {
            $path =  $row->file_path;

            if (file_exists($path)) {
                $newFile = basename($path);
                copy($path,$newFile);
                $zip->addFile($newFile);

                $deleteFiles[] = $newFile;
            }



        }
        $zip->close();

        foreach($deleteFiles as $value){
            unlink($value);
        }

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.$zipname);
        header('Content-Length: ' . filesize($zipname));
        readfile($zipname);
        unlink($zipname);
        exit();
    }

    public function viewImage(DownloadFile $downloadFile){
        $this->authorize('view',$downloadFile->download);
        $file = $downloadFile->file_path;

        if (file_exists($file))
        {
            $size = getimagesize($file);

            $fp = fopen($file, 'rb');

            if ($size and $fp)
            {
                header('Content-Type: '.$size['mime']);
                header('Content-Length: '.filesize($file));

                fpassthru($fp);

                exit;
            }
        }
    }

}
