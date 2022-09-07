<?php

namespace App\Http\Controllers\Tenant\Member;

use App\ForumAttachment;
use App\ForumThread;
use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\ForumTopic;
use App\Lib\HelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ForumTopicsController extends Controller
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
        $perPage = 25;

        if (!empty($keyword)) {
            $forumtopics = getDepartment()->forumTopics()->whereRaw("match(topic) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->paginate($perPage);
        } else {
            $forumtopics = getDepartment()->forumTopics()->latest()->paginate($perPage);
        }

        return view('member.forum-topics.index', compact('forumtopics'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('dept_allows','allow_members_create_topics');
        $msgId = Str::random(10);
        return view('member.forum-topics.create',compact('msgId'));
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
            'topic'=>'required',
            'content'=>'required'
        ]);
        $this->authorize('dept_allows','allow_members_create_topics');
        $requestData = $request->all();
        $requestData['user_id']= Auth::user()->id;
        $requestData['department_id'] = getDepartment()->id;
        $requestData['content'] = saveInlineImages($requestData['content']);


        $forumTopic = ForumTopic::create($requestData);

       $thread=  $forumTopic->forumThreads()->create([
           'user_id'=>Auth::user()->id,
           'content'=>saveInlineImages($requestData['content'])
       ]);

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
                $destDir = FORUM_PATH.'/'.$thread->id;
                if(!is_dir($destDir)){
                    mkdir($destDir);
                }

                foreach($files as $value){
                    $newName = $destDir.'/'.$value;
                    $oldName = $path.'/'.$value;
                    rename($oldName,$newName);
                    //attach record
                    $thread->forumAttachments()->create([
                        'file_path'=>$newName
                    ]);

                }
            }
            @rmdir($path);
        }

        return redirect('member/forum-topics')->with('flash_message',__('admin.forum-topic').' '.__('admin.added'));
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
        $forumtopic = ForumTopic::findOrFail($id);
        $this->authorize('view',$forumtopic);

        $threads = $forumtopic->forumThreads()->paginate(100);
        $msgId = Str::random(10);
        return view('member.forum-topics.show', compact('forumtopic','threads','msgId'));
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
        $forumtopic = ForumTopic::findOrFail($id);
        $this->authorize('view',$forumtopic);
        $msgId = Str::random(10);


        return view('member.forum-topics.edit', compact('forumtopic','msgId'));
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
            'content'=>'required'
        ]);
        $requestData = $request->all();

        $forumtopic = ForumTopic::findOrFail($id);
        $this->authorize('view',$forumtopic);

       $thread= $forumtopic->forumThreads()->create([
           'user_id'=>Auth::user()->id,
           'content'=>saveInlineImages($requestData['content'])
        ]);
        $tid = $thread->id;
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
                $destDir = FORUM_PATH.'/'.$thread->id;
                if(!is_dir($destDir)){
                    mkdir($destDir);
                }

                foreach($files as $value){
                    $newName = $destDir.'/'.$value;
                    $oldName = $path.'/'.$value;
                    rename($oldName,$newName);
                    //attach record
                    $thread->forumAttachments()->create([
                        'file_path'=>$newName
                    ]);

                }
            }
            @rmdir($path);
        }

        //send notfication mail to participants
        $threads= $forumtopic->forumThreads()->select('user_id')->distinct()->where('user_id','!=',Auth::user()->id)->get();

        $link = url('/member/forum-topics/' . $forumtopic->id);
        $url = "<a href=\"{$link}\">{$link}</a>";
        $message = __('admin.forum-mail',['name'=>Auth::user()->name,'topic'=>$forumtopic->topic,'content'=>$requestData['content'],'link'=>$url ]);
        $subject = __('admin.forum-mail-subject',['topic'=>$forumtopic->topic]);

        foreach($threads as $thread){
            $this->sendEmail($thread->user->email,$subject,$message);
        }

        return redirect('member/forum-topics/'.$forumtopic->id.'#thread'.$tid)->with('flash_message', __('admin.changes-saved'));
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
        $forumTopic = ForumTopic::find($id);
        if(!(Auth::user()->id==$forumTopic->user_id || isDeptAdmin(Auth::user()))){
            return back();
        }

        $threads= $forumTopic->forumThreads()->has('forumAttachments')->get();
        foreach($threads as $thread){
            $destDir = FORUM_PATH.'/'.$thread->id;
            @deleteDir($destDir);
        }


        ForumTopic::destroy($id);

        return redirect('member/forum-topics')->with('flash_message', __('admin.deleted'));
    }


    public function forumAttachment(ForumAttachment $forumAttachment){
        $this->authorize('view',$forumAttachment->forumThread->forumTopic);

        $path = $forumAttachment->file_path;

        header('Content-type: '.getFileMimeType($path));

// It will be called downloaded.pdf
        header('Content-Disposition: attachment; filename="'.basename($path).'"');

// The PDF source is in original.pdf
        readfile($path);
        exit();
    }

    public function forumAttachments(ForumThread $forumThread){
        $this->authorize('view',$forumThread->forumTopic);
        $zipname = 'attachments.zip';
        $zip = new \ZipArchive;
        $zip->open($zipname, \ZipArchive::CREATE);


        $deleteFiles = [];
        foreach ($forumThread->forumAttachments as $row) {
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

    public function viewImage(ForumAttachment $forumAttachment){
        $this->authorize('view',$forumAttachment->forumThread->forumTopic);
        $file = $forumAttachment->file_path;

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
