<?php

namespace App\Policies;

use App\User;
use App\Email;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmailPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any emails.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can view the email.
     *
     * @param  \App\User  $user
     * @param  \App\Email  $email
     * @return mixed
     */
    public function view(User $user, Email $email)
    {

        //check if user has pivot
        return ($user->id == $email->user_id || $user->receivedEmails()->where('email_id',$email->id));
    }

    /**
     * Determine whether the user can create emails.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the email.
     *
     * @param  \App\User  $user
     * @param  \App\Email  $email
     * @return mixed
     */
    public function update(User $user, Email $email)
    {
        return $user->id == $email->user_id;
    }

    /**
     * Determine whether the user can delete the email.
     *
     * @param  \App\User  $user
     * @param  \App\Email  $email
     * @return mixed
     */
    public function delete(User $user, Email $email)
    {
        return $user->id == $email->user_id;
    }

    /**
     * Determine whether the user can restore the email.
     *
     * @param  \App\User  $user
     * @param  \App\Email  $email
     * @return mixed
     */
    public function restore(User $user, Email $email)
    {
        return $user->id == $email->user_id;
    }

    /**
     * Determine whether the user can permanently delete the email.
     *
     * @param  \App\User  $user
     * @param  \App\Email  $email
     * @return mixed
     */
    public function forceDelete(User $user, Email $email)
    {
        return $user->id == $email->user_id;
    }
}
