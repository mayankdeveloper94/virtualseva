<?php

namespace App\Policies;

use App\User;
use App\Sms;
use Illuminate\Auth\Access\HandlesAuthorization;

class SmsPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any sms.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the sms.
     *
     * @param  \App\User  $user
     * @param  \App\Sms  $sms
     * @return mixed
     */
    public function view(User $user, Sms $sms)
    {
        //
    }

    /**
     * Determine whether the user can create sms.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the sms.
     *
     * @param  \App\User  $user
     * @param  \App\Sms  $sms
     * @return mixed
     */
    public function update(User $user, Sms $sms)
    {
        //
    }

    /**
     * Determine whether the user can delete the sms.
     *
     * @param  \App\User  $user
     * @param  \App\Sms  $sms
     * @return mixed
     */
    public function delete(User $user, Sms $sms)
    {
        return $user->id == $sms->user_id;
    }

    /**
     * Determine whether the user can restore the sms.
     *
     * @param  \App\User  $user
     * @param  \App\Sms  $sms
     * @return mixed
     */
    public function restore(User $user, Sms $sms)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the sms.
     *
     * @param  \App\User  $user
     * @param  \App\Sms  $sms
     * @return mixed
     */
    public function forceDelete(User $user, Sms $sms)
    {
        //
    }
}
