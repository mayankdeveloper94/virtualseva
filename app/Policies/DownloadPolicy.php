<?php

namespace App\Policies;

use App\User;
use App\Download;
use Illuminate\Auth\Access\HandlesAuthorization;

class DownloadPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any downloads.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the download.
     *
     * @param  \App\User  $user
     * @param  \App\Download  $download
     * @return mixed
     */
    public function view(User $user, Download $download)
    {
        return $download->department_id == getDepartment()->id;
    }

    /**
     * Determine whether the user can create downloads.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the download.
     *
     * @param  \App\User  $user
     * @param  \App\Download  $download
     * @return mixed
     */
    public function update(User $user, Download $download)
    {
        return $download->department_id == getDepartment()->id;
    }

    /**
     * Determine whether the user can delete the download.
     *
     * @param  \App\User  $user
     * @param  \App\Download  $download
     * @return mixed
     */
    public function delete(User $user, Download $download)
    {
        return $download->department_id == getDepartment()->id;
    }

    /**
     * Determine whether the user can restore the download.
     *
     * @param  \App\User  $user
     * @param  \App\Download  $download
     * @return mixed
     */
    public function restore(User $user, Download $download)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the download.
     *
     * @param  \App\User  $user
     * @param  \App\Download  $download
     * @return mixed
     */
    public function forceDelete(User $user, Download $download)
    {
        //
    }
}
