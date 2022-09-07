<?php

namespace App\Http\Middleware;

use App\Lib\HelperTrait;
use App\Lib\HelperTraitSaas;
use Closure;
use Illuminate\Support\Facades\Auth;

class BillingAddressPresent
{
    use HelperTraitSaas;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $addresses = $user->billingAddresses->count();
        if(empty($addresses)){
            $this->warningMessage(__('saas.add-billing-msg'));
            return redirect()->route('user.billing-address.create');
        }
        return $next($request);
    }
}
