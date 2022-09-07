<?php

namespace App\Policies;

use App\Application;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can delete the email.
     *
     * @param  \App\User  $user
     * @param  \App\Email  $email
     * @return mixed
     */
    public function delete(User $user, Application $application)
    {
        return $user->id == $application->user_id;
    }

    public function view(User $user, Application $application){

        return $application->department_id == getDepartment()->id;
    }
}
