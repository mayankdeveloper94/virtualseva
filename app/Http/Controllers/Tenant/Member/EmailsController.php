<?php

namespace App\Http\Controllers\Tenant\Member;

use App\Department;
use App\EmailAttachment;
use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Email;
use App\Mail\UserMessage;
use App\Team;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmailsController extends Controller
{

    public function __construct()
    {
       // $this->authorizeResource(Email::class, 'email');
    }

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
        if (!empty($keyword)) {
            $emails = $user->emails()->latest()->whereRaw("match(subject,message,notes) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->paginate($perPage);
        } else {
            $emails = $user->emails()->latest()->paginate($perPage);
        }

        return view('member.emails.index', compact('emails'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {

        $teams = getDepartment()->teams()->orderBy('name')->get();
        $msgId = Str::random(10);

        $replyUser = false;
        $subject = '';
        $replyEmail = null;
        if($request->reply && Email::find($request->reply))
        {
            $replyEmail = Email::find($request->reply);
            $replyUser = $replyEmail->user;
            $subject = 'Re: '.$replyEmail->subject;

        }

        if($request->user && User::find($request->user))
        {
            $replyUser = User::find($request->user);
        }


        return view('member.emails.create',compact('teams','msgId','replyUser','subject','replyEmail'));
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
           'subject'=>'required',
            'message'=>'required'
        ]);

        $requestData = $request->all();

        if(empty($requestData['members']) && empty($requestData['teams']) && empty($requestData['all_members'])){
            return back()->withInput($requestData)->with('alert-danger',__('admin.recipient-error'));
        }

        $requestData['user_id'] = Auth::user()->id;
        $requestData['message'] = saveInlineImages($requestData['message']);
        $email = Email::create($requestData);

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
                    $destDir = ATTACHMENT_PATH.'/'.$email->id;
                    if(!is_dir($destDir)){
                        mkdir($destDir);
                    }

                    foreach($files as $value){
                        $newName = $destDir.'/'.$value;
                        $oldName = $path.'/'.$value;
                        rename($oldName,$newName);
                        //attach record
                        $email->emailAttachments()->create([
                            'file_name'=>$value,
                            'file_path'=>$newName
                        ]);

                    }
                }
                @rmdir($path);
          }
        //create all recipients

        $recipients = [];

        if(isset($requestData['all_members']) && $requestData['all_members']==1){
            $this->authorize('administer');
            $allMembers = getDepartment()->users;
            foreach($allMembers as $user){
                $recipients[$user->id] = $user->id;
            }

        }
        else{

            if(isset($requestData['members'])){
                //loop through members
                foreach($requestData['members'] as $value){
                    $recipients[$value] = $value;
                }
            }


            if(isset($requestData['teams'])){
                //now loop through departments
                foreach($requestData['teams'] as $value){
                    $team = Team::find($value);

                    foreach($team->users as $user){
                        $recipients[$user->id] = $user->id;
                    }
                }
            }


        }




        $recipients = array_values($recipients);

        $email->users()->attach($recipients);

        Mail::to($email->users)->send(new UserMessage($email));

        return redirect('member/emails')->with('flash_message', __('admin.message-sent'));
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

        $email = Email::findOrFail($id);

        $this->authorize('view',$email);

        return view('member.emails.show', compact('email'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    /*public function edit($id)
    {
        $email = Email::findOrFail($id);

        return view('member.emails.edit', compact('email'));
    }*/

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
   /* public function update(Request $request, $id)
    {

        $requestData = $request->all();

        $email = Email::findOrFail($id);
        $email->update($requestData);

        return redirect('member/emails')->with('flash_message', 'Email updated!');
    }*/

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        $this->authorize('delete',Email::find($id));
        //delete attachment directory
        $path = ATTACHMENT_PATH.'/'.$id;
        removeDirectory($path);

        Email::destroy($id);

        return redirect('member/emails')->with('flash_message', __('admin.message').' '.__('admin.deleted'));
    }

    public function deleteMultiple(Request $request){
        $data = $request->all();
        $count = 0;
        foreach($data as $key=>$value){
            $email = Email::find($key);

            if($email){
                $this->authorize('delete',$email);
                $path = ATTACHMENT_PATH.'/'.$email->id;
                removeDirectory($path);

                $email->delete();
                $count++;
            }

        }

        return back()->with('flash_message',"{$count} ".__('admin.deleted'));
    }

    public function upload(Request $request,$id){


        $validator = Validator::make($request->all(), [
            'file' => 'file|max:10000|mimes:jpeg,png,gif,pdf,doc,docx,ppt,pptx,zip,mp3,mp4',
        ]);

        if ($validator->fails()) {

            $errorString = implode(",",$validator->messages()->all());
            return response($errorString, 500)
                ->header('Content-Type', 'text/plain');
        }

        $name= safeFile($_FILES['file']['name']);
        $tmpName = $_FILES['file']['tmp_name'];



        //create temp dir
        $path = TEMP_DIR.$id;
        if(!is_dir($path)){
            mkdir($path);
        }



        $newName = $path.'/'.$name;
        //movefile
        if(!rename($tmpName,$newName)){
            return response(__('admin.upload-failed'), 500)
                ->header('Content-Type', 'text/plain');
        }

       /* $path =  $request->file('file')->store('attachments','public_uploads');
        $file = 'uploads/'.$path;*/
        echo $newName;

         //now upload file
    }

    public function removeUpload(Request $request,$id){

        $name = $request->name;
        $path = TEMP_DIR.$id.'/'.safeFile($name);
        unlink($path);
        echo 'done';
    }

    public function viewImage(EmailAttachment $emailAttachment){
        $file = $emailAttachment->file_path;

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

    public function downloadAttachment(EmailAttachment $emailAttachment){
        $path = $emailAttachment->file_path;

        header('Content-type: '.getFileMimeType($path));

// It will be called downloaded.pdf
        header('Content-Disposition: attachment; filename="'.basename($path).'"');

// The PDF source is in original.pdf
        readfile($path);
        exit();
    }

    public function downloadAttachments(Email $email){

        $zipname = safeUrl($email->subject).'.zip';
        $zip = new \ZipArchive;
        $zip->open($zipname, \ZipArchive::CREATE);


        $deleteFiles = [];
        foreach ($email->emailAttachments as $row) {
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

    public function inbox(Request $request){
        $keyword = $request->get('search');
        $perPage = 25;

        $user = Auth::user();
        if (!empty($keyword)) {
            $emails = $user->receivedEmails()->latest()->whereRaw("match(subject,message,notes) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->paginate($perPage);
        } else {
            $emails = $user->receivedEmails()->latest()->paginate($perPage);
        }

        return view('member.emails.inbox', compact('emails'));
    }


    public function destroyInbox($id)
    {

        $user = Auth::user();
        $user->receivedEmails()->detach($id);


        return redirect()->route('emails.inbox')->with('flash_message', __('admin.message').' '.__('admin.deleted'));
    }

    public function deleteMultipleInbox(Request $request){
        $user = Auth::user();
        $data = $request->all();
        $count = 0;
        foreach($data as $key=>$value){
            $email = Email::find($key);

            if($email){
                $user->receivedEmails()->detach($key);

                $count++;
            }

        }

        return back()->with('flash_message',"{$count} ".__('admin.deleted'));
    }

    public function viewInbox(Email $email){
        $this->authorize('view',$email);
       //update pivot table
        $user = Auth::user();
        $user->receivedEmails()->updateExistingPivot($email->id, ['read'=>1]);

       return view('member.emails.inbox_show', compact('email'));
    }

}
