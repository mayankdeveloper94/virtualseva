<?php

Route::group(['middleware'=>['web','storage'],'namespace'=>'App\\Http\\Controllers\\Tenant\\'],function(){

    Route::get('/','HomeController@site')->name('site.home');
	
    Auth::routes();

    Route::get('/home', 'HomeController@site')->name('home');

    Route::get('/departments','Site\DepartmentsController@index')->name('site.departments');
    Route::get('/departments/{department}','Site\DepartmentsController@details')->name('site.department');
    Route::get('cron','HomeController@cron')->name('cron');


    Route::group(['prefix'=>'admin','middleware'=>['auth','admin']],function(){
        Route::get('/dashboard','Admin\IndexController@dashboard')->name('admin.dashboard');
        Route::resource('departments', 'Admin\DepartmentsController');
        Route::resource('categories', 'Admin\CategoriesController');
        Route::get('departments/remove-picture/{id}','Admin\DepartmentsController@removePicture')->name('dept.remove-picture');
        Route::get('departments/members/{department}','Admin\DepartmentsController@members')->name('dept.members');
        Route::get('departments/all-members/{department}','Admin\DepartmentsController@allMembers')->name('dept.all-members');
        Route::post('departments/add-members/{department}','Admin\DepartmentsController@addMembers')->name('dept.add-members');
        Route::post('departments/remove-members/{department}','Admin\DepartmentsController@removeMembers')->name('dept.remove-members');
        Route::get('departments/set-admin/{department}/{user}/{mode}','Admin\DepartmentsController@setAdmin')->name('dept.set-admin');

        Route::get('members/search','Admin\MembersController@search')->name('members.search');
        Route::post('members/export','Admin\MembersController@export')->name('members.export');
        Route::get('members/import','Admin\MembersController@import')->name('members.import');
        Route::post('members/import','Admin\MembersController@saveImport')->name('members.save-import');
        Route::resource('members', 'Admin\MembersController');
        Route::get('members/remove-picture/{id}','Admin\MembersController@removePicture')->name('members.remove-picture');
        Route::resource('fields', 'Admin\FieldsController');

        Route::get('settings/{group}','Admin\SettingsController@settings')->name('admin.settings');
        Route::post('save-settings','Admin\SettingsController@saveSettings')->name('admin.save-settings');
        Route::get('settings/remove-picture/{setting}','Admin\SettingsController@removePicture')->name('settings.remove-picture');
        Route::resource('admins', 'Admin\AdminsController');
        Route::get('sms-settings','Admin\SettingsController@smsGateways')->name('settings.sms_gateways');
        Route::post('save-sms-setting','Admin\SettingsController@saveSmsSetting')->name('settings.save-sms-setting');
        Route::get('sms-gateway/{smsGateway}','Admin\SettingsController@smsFields')->name('settings.edit-sms-gateway');
        Route::post('save-sms-gateway/{smsGateway}','Admin\SettingsController@saveField')->name('settings.save-sms-gateway');
        Route::get('gateway-status/{smsGateway}/{status}','Admin\SettingsController@setSmsStatus')->name('settings.sms-status');
        Route::get('language','Admin\SettingsController@language')->name('settings.language');
        Route::post('save-language','Admin\SettingsController@saveLanguage')->name('settings.save-language');

        Route::post('emails/upload-attachment/{id}','Admin\EmailsController@upload')->name('emails.upload');
        Route::post('emails/remove-upload/{id}','Admin\EmailsController@removeUpload')->name('emails.remove-upload');
        Route::get('emails/delete-email/{id}','Admin\EmailsController@destroy')->name('email.delete');
        Route::post('emails/delete-multiple','Admin\EmailsController@deleteMultiple')->name('email.delete-multiple');
        Route::get('emails/view-image/{emailAttachment}','Admin\EmailsController@viewImage')->name('email.view-image');
        Route::get('emails/download-attachment/{emailAttachment}','Admin\EmailsController@downloadAttachment')->name('email.download-attachment');
        Route::get('emails/download-attachments/{email}','Admin\EmailsController@downloadAttachments')->name('email.download-attachments');

        Route::get('emails/inbox','Admin\EmailsController@inbox')->name('emails.inbox');
        Route::get('emails/delete-inbox/{id}','Admin\EmailsController@destroyInbox')->name('email.delete-inbox');
        Route::get('emails/view-inbox/{email}','Admin\EmailsController@viewInbox')->name('email.view-inbox');
        Route::post('emails/delete-multiple-inbox','Admin\EmailsController@deleteMultipleInbox')->name('email.delete-multiple-inbox');


        Route::resource('emails', 'Admin\EmailsController');


        Route::get('sms/delete-email/{id}','Admin\SmsController@destroy')->name('sms.delete');
        Route::post('sms/delete-multiple','Admin\SmsController@deleteMultiple')->name('sms.delete-multiple');
        Route::get('sms/inbox','Admin\SmsController@inbox')->name('sms.inbox');
        Route::get('sms/delete-inbox/{id}','Admin\SmsController@destroyInbox')->name('sms.delete-inbox');
        Route::post('sms/delete-multiple-inbox','Admin\SmsController@deleteMultipleInbox')->name('sms.delete-multiple-inbox');



        Route::resource('sms', 'Admin\SmsController');



    });



    Route::group(['prefix'=>'member','middleware'=>['auth','department']],function(){
        Route::get('/dashboard','Member\IndexController@dashboard')->name('member.dashboard');

        Route::get('teams/my-teams','Member\TeamsController@myTeams')->name('member.my-teams');

        Route::resource('teams', 'Member\TeamsController');
        Route::get('members/search','Member\MembersController@search')->name('member.members.search');

        Route::get('events/roster','Member\EventsController@roster')->name('member.events.roster');
        Route::post('events/roaster-opt-out/{shift}','Member\EventsController@optOut')->name('member.events.opt-out');
        Route::get('events/my-shifts','Member\EventsController@shifts')->name('member.events.shifts');

        Route::group(['middleware'=>'department.admin'],function(){

            Route::get('members/applications','Member\MembersController@applications')->name('member.members.applications');
            Route::get('members/application/{application}','Member\MembersController@application')->name('member.members.application');
            Route::post('members/application/{application}','Member\MembersController@updateApplication')->name('member.members.update-application');

            Route::get('members/remove/{id}','Member\MembersController@destroy')->name('member.members.remove');

            Route::post('members/export','Member\MembersController@export')->name('member.members.export');
            Route::get('members/import','Member\MembersController@import')->name('member.members.import');
            Route::post('members/import','Member\MembersController@saveImport')->name('member.members.save-import');

            Route::get('settings/general','Member\SettingsController@general')->name('member.settings.general');
            Route::post('settings/save-settings','Member\SettingsController@saveSettings')->name('member.settings.save-settings');
            Route::get('settings/remove-picture','Member\SettingsController@removePicture')->name('member.settings.remove-picture');

            Route::resource('fields', 'Member\FieldsController');

            Route::get('members/set-admin/{user}/{mode}','Member\MembersController@setAdmin')->name('member.members.set-admin');

            Route::resource('events', 'Member\EventsController');

            Route::get('events/shifts/{event}','Member\ShiftsController@index')->name('member.shifts.index');
            Route::get('events/shifts/create/{event}','Member\ShiftsController@create')->name('member.shifts.create');
            Route::post('events/shifts/store/{event}','Member\ShiftsController@store')->name('member.shifts.store');
            Route::get('events/shifts/{shift}/tasks/','Member\ShiftsController@tasks')->name('member.shifts.tasks');
            Route::post('events/shifts/{shift}/save-tasks','Member\ShiftsController@saveTasks')->name('member.shifts.save-tasks');
            Route::resource('shifts', 'Member\ShiftsController');

            Route::resource('galleries', 'Member\GalleriesController');




        });


        Route::resource('members', 'Member\MembersController');



        Route::post('emails/upload-attachment/{id}','Member\EmailsController@upload')->name('member.emails.upload');
        Route::post('emails/remove-upload/{id}','Member\EmailsController@removeUpload')->name('member.emails.remove-upload');
        Route::get('emails/delete-email/{id}','Member\EmailsController@destroy')->name('member.email.delete');
        Route::post('emails/delete-multiple','Member\EmailsController@deleteMultiple')->name('member.email.delete-multiple');
        Route::get('emails/view-image/{emailAttachment}','Member\EmailsController@viewImage')->name('member.email.view-image');
        Route::get('emails/download-attachment/{emailAttachment}','Member\EmailsController@downloadAttachment')->name('member.email.download-attachment');
        Route::get('emails/download-attachments/{email}','Member\EmailsController@downloadAttachments')->name('member.email.download-attachments');

        Route::get('emails/inbox','Member\EmailsController@inbox')->name('member.emails.inbox');
        Route::get('emails/delete-inbox/{id}','Member\EmailsController@destroyInbox')->name('member.email.delete-inbox');
        Route::get('emails/view-inbox/{email}','Member\EmailsController@viewInbox')->name('member.email.view-inbox');
        Route::post('emails/delete-multiple-inbox','Member\EmailsController@deleteMultipleInbox')->name('member.email.delete-multiple-inbox');


        Route::resource('emails', 'Member\EmailsController');

        Route::resource('announcements', 'Member\AnnouncementsController');

        Route::get('downloads/view-image/{downloadFile}','Member\DownloadsController@viewImage')->name('member.download.view-image');
        Route::get('downloads/download-file/{downloadFile}','Member\DownloadsController@downloadAttachment')->name('member.download.download-attachment');
        Route::get('downloads/download-files/{download}','Member\DownloadsController@downloadAttachments')->name('member.download.download-attachments');
        Route::get('downloads/browse','Member\DownloadsController@browse')->name('member.downloads.browse');
        Route::resource('downloads', 'Member\DownloadsController');

        Route::get('forum-topics/view-image/{forumAttachment}','Member\ForumTopicsController@viewImage')->name('member.forum.view-image');
        Route::get('forum-topics/download-file/{forumAttachment}','Member\ForumTopicsController@forumAttachment')->name('member.forum.download-attachment');
        Route::get('forum-topics/download-files/{forumThread}','Member\ForumTopicsController@forumAttachments')->name('member.forum.download-attachments');

        Route::resource('forum-topics', 'Member\ForumTopicsController');

        Route::group(['middleware'=>'sms'],function(){
            Route::get('sms/delete-email/{id}','Member\SmsController@destroy')->name('member.sms.delete');
            Route::post('sms/delete-multiple','Member\SmsController@deleteMultiple')->name('member.sms.delete-multiple');
            Route::get('sms/inbox','Member\SmsController@inbox')->name('member.sms.inbox');
            Route::get('sms/delete-inbox/{id}','Member\SmsController@destroyInbox')->name('member.sms.delete-inbox');
            Route::post('sms/delete-multiple-inbox','Member\SmsController@deleteMultipleInbox')->name('member.sms.delete-multiple-inbox');



            Route::resource('sms', 'Member\SmsController');
        });



    });

	//general auth
    Route::group(['middleware'=>['auth']],function(){

        Route::get('account/profile','Admin\AccountController@profile')->name('account.profile');
        Route::post('account/save-profile','Admin\AccountController@saveProfile')->name('account.save-profile');
        Route::get('account/password','Admin\AccountController@password')->name('account.password');
        Route::post('account/save-password','Admin\AccountController@savePassword')->name('account.save-password');
        Route::get('account/remove-picture','Admin\AccountController@removePicture')->name('account.remove-picture');

        Route::get('select-department','Site\DepartmentsController@myDepartments')->name('site.select-department');
        Route::get('join-department/{department}','Site\DepartmentsController@join')->name('site.join-department');
        Route::get('apply/{department}','Site\DepartmentsController@apply')->name('site.apply');
        Route::post('save-application/{department}','Site\DepartmentsController@saveApplication')->name('site.save-application');
        Route::get('my-applications','Site\DepartmentsController@myApplications')->name('site.my-applications');
        Route::get('department-login/{department}','Site\DepartmentsController@login')->name('site.department-login');
        Route::get('delete-application/{application}','Site\DepartmentsController@deleteApplication')->name('site.delete-application');

    });

});