<?php

function isTenant(){

    $website   = app(\Hyn\Tenancy\Environment::class)->tenant();
    if($website){
        return true;
    }
    else{
        return false;
    }


}

function isSaas(){
    $website   = app(\Hyn\Tenancy\Environment::class)->tenant();
    if($website){
        return false;
    }
    else{
        return true;
    }


/*    $env = app(\Hyn\Tenancy\Environment::class);

    if ($fqdn = optional($env->hostname())->fqdn) {

        return false;


    }
    else{
        return true;
    }*/
}

function setTenantDb(){
    $website   = app(\Hyn\Tenancy\Environment::class)->tenant();
    if($website){

        $tenancy = app(\Hyn\Tenancy\Environment::class);
        $tenancy->tenant($website);
        config(['database.default' => 'tenant']);
    }



}

function filesize_r($path){
    if(!file_exists($path)) return 0;
    if(is_file($path)) return filesize($path);
    $ret = 0;
    foreach(glob($path."/*") as $fn)
        $ret += filesize_r($fn);
    return $ret;
}
