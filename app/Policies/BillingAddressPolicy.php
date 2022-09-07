<?php

namespace App\Policies;

use App\Models\User;
use App\Models\BillingAddress;
use Illuminate\Auth\Access\HandlesAuthorization;

class BillingAddressPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the billingAddress.
     *
     * @param  \App\User  $user
     * @param  \App\BillingAddress  $billingAddress
     * @return mixed
     */
    public function view(User $user, BillingAddress $billingAddress)
    {
        //
        return $user->id === $billingAddress->user_id;
    }

    /**
     * Determine whether the user can create billingAddresses.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
        return true;
    }

    /**
     * Determine whether the user can update the billingAddress.
     *
     * @param  \App\User  $user
     * @param  \App\BillingAddress  $billingAddress
     * @return mixed
     */
    public function update(User $user, BillingAddress $billingAddress)
    {

        //
        return $user->id === $billingAddress->user_id;
    }

    /**
     * Determine whether the user can delete the billingAddress.
     *
     * @param  \App\User  $user
     * @param  \App\BillingAddress  $billingAddress
     * @return mixed
     */
    public function delete(User $user, BillingAddress $billingAddress)
    {
        //
        return $user->id === $billingAddress->user_id;
    }
}
