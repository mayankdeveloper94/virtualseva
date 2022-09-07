<?php

namespace App\Policies;

use App\User;
use App\Shift;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShiftPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any shifts.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the shift.
     *
     * @param  \App\User  $user
     * @param  \App\Shift  $shift
     * @return mixed
     */
    public function view(User $user, Shift $shift)
    {
        return $shift->event->department_id == getDepartment()->id;
    }

    /**
     * Determine whether the user can create shifts.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the shift.
     *
     * @param  \App\User  $user
     * @param  \App\Shift  $shift
     * @return mixed
     */
    public function update(User $user, Shift $shift)
    {
        //
        return $shift->event->department_id == getDepartment()->id;
    }

    /**
     * Determine whether the user can delete the shift.
     *
     * @param  \App\User  $user
     * @param  \App\Shift  $shift
     * @return mixed
     */
    public function delete(User $user, Shift $shift)
    {
        //
        return $shift->event->department_id == getDepartment()->id;
    }

    /**
     * Determine whether the user can restore the shift.
     *
     * @param  \App\User  $user
     * @param  \App\Shift  $shift
     * @return mixed
     */
    public function restore(User $user, Shift $shift)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the shift.
     *
     * @param  \App\User  $user
     * @param  \App\Shift  $shift
     * @return mixed
     */
    public function forceDelete(User $user, Shift $shift)
    {
        //
    }
}
