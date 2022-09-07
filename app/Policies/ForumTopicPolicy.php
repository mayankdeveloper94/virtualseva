<?php

namespace App\Policies;

use App\User;
use App\ForumTopic;
use Illuminate\Auth\Access\HandlesAuthorization;

class ForumTopicPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any forum topics.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the forum topic.
     *
     * @param  \App\User  $user
     * @param  \App\ForumTopic  $forumTopic
     * @return mixed
     */
    public function view(User $user, ForumTopic $forumTopic)
    {
        return $forumTopic->department_id == getDepartment()->id;
    }

    /**
     * Determine whether the user can create forum topics.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the forum topic.
     *
     * @param  \App\User  $user
     * @param  \App\ForumTopic  $forumTopic
     * @return mixed
     */
    public function update(User $user, ForumTopic $forumTopic)
    {
        return $forumTopic->department_id == getDepartment()->id;
    }

    /**
     * Determine whether the user can delete the forum topic.
     *
     * @param  \App\User  $user
     * @param  \App\ForumTopic  $forumTopic
     * @return mixed
     */
    public function delete(User $user, ForumTopic $forumTopic)
    {
        return $forumTopic->department_id == getDepartment()->id;
    }

    /**
     * Determine whether the user can restore the forum topic.
     *
     * @param  \App\User  $user
     * @param  \App\ForumTopic  $forumTopic
     * @return mixed
     */
    public function restore(User $user, ForumTopic $forumTopic)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the forum topic.
     *
     * @param  \App\User  $user
     * @param  \App\ForumTopic  $forumTopic
     * @return mixed
     */
    public function forceDelete(User $user, ForumTopic $forumTopic)
    {
        //
    }
}
