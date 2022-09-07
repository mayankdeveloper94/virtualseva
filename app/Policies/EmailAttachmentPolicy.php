<?php

namespace App\Policies;

use App\User;
use App\EmailAttachment;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmailAttachmentPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any email attachments.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can view the email attachment.
     *
     * @param  \App\User  $user
     * @param  \App\EmailAttachment  $emailAttachment
     * @return mixed
     */
    public function view(User $user, EmailAttachment $emailAttachment)
    {
        //
    }

    /**
     * Determine whether the user can create email attachments.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the email attachment.
     *
     * @param  \App\User  $user
     * @param  \App\EmailAttachment  $emailAttachment
     * @return mixed
     */
    public function update(User $user, EmailAttachment $emailAttachment)
    {
        //
    }

    /**
     * Determine whether the user can delete the email attachment.
     *
     * @param  \App\User  $user
     * @param  \App\EmailAttachment  $emailAttachment
     * @return mixed
     */
    public function delete(User $user, EmailAttachment $emailAttachment)
    {
        //
    }

    /**
     * Determine whether the user can restore the email attachment.
     *
     * @param  \App\User  $user
     * @param  \App\EmailAttachment  $emailAttachment
     * @return mixed
     */
    public function restore(User $user, EmailAttachment $emailAttachment)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the email attachment.
     *
     * @param  \App\User  $user
     * @param  \App\EmailAttachment  $emailAttachment
     * @return mixed
     */
    public function forceDelete(User $user, EmailAttachment $emailAttachment)
    {
        //
    }
}
