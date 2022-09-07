<?php

namespace App\Http\Controllers\Admin;

use App\Lib\HelperTraitSaas;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    use HelperTraitSaas;
    
    public function index(){

        $output = [];
        $output['totalSubscribers'] = User::where('role_id',2)->where('trial',0)->whereHas('subscriber',function($q){
            $q->where('expires','>=',time());
            $q->orderBy('expires','asc');
        })->count();

        $output['totalSales'] = Invoice::where('paid',1)->sum('amount');
        $currentMonth = date('m');
        $output['monthSales'] = Invoice::where('paid',1)->whereRaw('MONTH(created_at) = ?',[$currentMonth])->sum('amount');
        $output['yearSales'] = Invoice::where('paid',1)->whereYear('created_at', date('Y'))->sum('amount');

        $output['currency'] = defaultCurrency()->country->symbol_left;
        $months = array_map('getMonthStr', range(-7,0));

        $monthlySales = [];
        $monthlyCount = [];
        foreach(range(-7,0) as $offset){
            //get the
            $start= date("Y-m-d", strtotime("$offset months first day of this month"));
            $end = date("Y-m-d", strtotime("$offset months last day of this month"));
            $monthlySales[] = Invoice::where('paid',1)->whereDate('created_at','>=', $start)->whereDate('created_at','<=', $end)->sum('amount');
            $monthlyCount[] = Invoice::where('paid',1)->whereDate('created_at','>=', $start)->whereDate('created_at','<=', $end)->count();
        }

        $output['monthSaleData'] = json_encode($monthlySales);
        $output['monthSaleCount'] = json_encode($monthlyCount);
        
        $output['monthSaleMin'] = min($monthlySales);
        $output['monthSaleMax'] = max($monthlySales);
        
        $output['monthList']= json_encode($months);
        $output['controller'] = $this;
        $output['recentSubscribers'] = User::where('role_id',2)->latest()->limit(8)->get();
        $output['recentInvoices'] = Invoice::latest()->limit(8)->get();



        return view('admin.home._index',$output);
    }

}
