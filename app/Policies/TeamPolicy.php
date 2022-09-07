<?php

namespace App\Policies;

use App\User;
use App\Team;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any teams.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the team.
     *
     * @param  \App\User  $user
     * @param  \App\Team  $team
     * @return mixed
     */
    public function view(User $user, Team $team)
    {

        $department = getDepartment();
        if($team->department_id != $department->id){

            return false;
        }

        //check if user is admin
        $isAdmin = $user->departments()->where('id',$department->id)->first();

        if($isAdmin && $isAdmin->pivot->department_admin==1 || Auth::user()->role_id==1){

            return true;
        }

        //check if user belongs to team and department allows showing members
        $total = $user->teams()->where('team_id',$team->id)->count();
        if(!empty($total) && $department->show_members==1){
            return true;
        }
        else{

            return false;
        }

    }

    /**
     * Determine whether the user can create teams.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the team.
     *
     * @param  \App\User  $user
     * @param  \App\Team  $team
     * @return mixed
     */
    public function update(User $user, Team $team)
    {
        $department = getDepartment();
        if($team->department_id != $department->id){
            return false;
        }
        else{
            return true;
        }
    }

    /**
     * Determine whether the user can delete the team.
     *
     * @param  \App\User  $user
     * @param  \App\Team  $team
     * @return mixed
     */
    public function delete(User $user, Team $team)
    {
        $department = getDepartment();
        if($team->department_id != $department->id){
            return false;
        }
        else{
            return true;
        }
    }

    /**
     * Determine whether the user can restore the team.
     *
     * @param  \App\User  $user
     * @param  \App\Team  $team
     * @return mixed
     */
    public function restore(User $user, Team $team)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the team.
     *
     * @param  \App\User  $user
     * @param  \App\Team  $team
     * @return mixed
     */
    public function forceDelete(User $user, Team $team)
    {
        //
    }
}
