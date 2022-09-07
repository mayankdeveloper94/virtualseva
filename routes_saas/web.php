<?php
Route::group(['middleware'=>['currency']],function(){

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/','Site\IndexController@index')->name('homepage');
Route::post('save-mailing-list','Site\IndexController@saveEmail')->name('site.save-email');
Route::get('mailing-list','Site\IndexController@mailingList')->name('site.list');

Route::get('blog','Site\BlogPostController@listing')->name('blog.listing');
Route::get('blog/{blogPost}/{slug}','Site\BlogPostController@post')->name('blog.post');
Route::get('blog-search','Site\BlogPostController@search')->name('blog.search');

Route::group(['prefix' => 'docs'], function () {
    Route::get('/','Site\DocsController@index')->name('docs.index');
    Route::get('post/{id}/{slug?}','Site\DocsController@post')->name('docs.post');
    Route::get('search','Site\DocsController@search')->name('docs.search');
    Route::get('category/{id}','Site\DocsController@category')->name('docs.category');
    Route::get('offline', function () {
        return view('site.docs.offline');
    })->name('docs.offline');
    Route::post('download','Site\DocsController@download')->name('docs.download');
});

Route::get('feature/{feature}/{slug}','Site\IndexController@feature')->name('site.feature');
Route::get('article/{article}/{slug}','Site\IndexController@article')->name('site.article');
Route::get('pricing','Site\IndexController@pricing')->name('site.pricing');
Route::get('contact','Site\IndexController@contact')->name('site.contact');
Route::post('contact','Site\IndexController@send')->name('site.send-msg');
Route::get('set-currency/{currency}','Site\IndexController@currency')->name('set.currency');
Route::get('pay/{hash}', 'Site\PayController@pay')->name('pay');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::get('/test','TestController@index');

Route::get('set-currency/{currency}','PayController@currency')->name('set.currency');

Route::group(['middleware'=>['auth','admin'],'prefix' => 'admin', 'as' => 'admin.','namespace'=>'Admin'],function() {

    Route::get('/', 'HomeController@index')->name('dashboard');
    Route::resource('plans', 'PlansController');

    Route::get('subscribers/stats/{user}', 'SubscribersController@stats')->name('subscribers.stats');
    Route::get('search-subscribers','SubscribersController@search')->name('subscribers.search');
    Route::resource('subscribers', 'SubscribersController');


    Route::get('hostnames/{website}','HostnamesController@index')->name('hostnames.index');
    Route::get('hostnames/create/{website}','HostnamesController@create')->name('hostnames.create');
    Route::post('hostnames/{website}','HostnamesController@store')->name('hostnames.store');
    Route::resource('hostnames', 'HostnamesController')->except([
        'create', 'store', 'index','show'
    ]);

    Route::get('approve-invoice/{invoice}','InvoicesController@approve')->name('invoices.approve');
    Route::resource('invoices', 'InvoicesController');

    Route::resource('currencies', 'CurrenciesController');

    Route::resource('features', 'FeaturesController');

    Route::resource('article-categories', 'ArticleCategoriesController');

    Route::resource('articles', 'ArticlesController');
    Route::resource('help-posts', 'HelpPostsController');
    Route::resource('help-categories', 'HelpCategoriesController');
    Route::resource('blog-categories', 'BlogCategoriesController');
    Route::resource('blog-posts', 'BlogPostsController');

    Route::get('blog-remove-picture/{id}','BlogPostsController@removePicture')->name('blog.remove-picture');


    Route::get('settings/{group}','SettingsController@settings')->name('settings');
    Route::post('save-settings','SettingsController@saveSettings')->name('save-settings');
    Route::get('settings/remove-picture/{setting}','SettingsController@removePicture')->name('remove-picture');
    Route::get('language','SettingsController@language')->name('language');
    Route::post('save-language','SettingsController@saveLanguage')->name('save-language');

    Route::get('trial','SettingsController@trial')->name('trial');
    Route::post('save-trial','SettingsController@saveTrial')->name('save-trial');

    Route::get('payment-methods','PaymentMethodsController@index')->name('payment-methods');
    Route::get('payment-methods/edit/{paymentMethod}','PaymentMethodsController@edit')->name('payment-methods.edit');
    Route::post('payment-methods/update/{paymentMethod}','PaymentMethodsController@update')->name('payment-methods.update');

    Route::resource('admins', 'AdminsController');
    Route::get('profile','SettingsController@profile')->name('profile');
    Route::post('profile','SettingsController@saveProfile')->name('save-profile');
    
    Route::resource('sliders', 'SlidersController');
    
    Route::get('about-us','AboutUsController@edit')->name('about-us');
    Route::put('about-us','AboutUsController@update')->name('about-us.update');
    
    Route::resource('services', 'ServicesController');
    
    Route::resource('faqs', 'FAQsController');
    
    Route::resource('our-works', 'OurWorksController');
    
    
});

Route::group(['middleware'=>['auth'],'prefix' => 'account', 'as' => 'user.','namespace'=>'Subscriber'],function() {

    Route::get('/', 'HomeController@index')->name('dashboard');

    Route::middleware(['setup'])->group(function () {
        Route::get('setup','SetupController@index')->name('setup');

     //   Route::get('setup','SetupController@step1')->name('wizard.step1');
        Route::post('process-wizard','SetupController@process')->name('process-wizard');
        Route::get('setup-complete','SetupController@complete')->name('setup-complete');
        Route::get('username-check','SetupController@username')->name('username-check');
    });

    Route::middleware(['subscriber'])->group(function () {
       //
    });

    Route::get('plans','PlansController@index')->name('plans');


    Route::post('billing/create-invoice','InvoiceController@create')->name('invoice.create');
    Route::get('cart','InvoiceController@cart')->name('invoice.cart')->middleware('cart');
    Route::get('cart/cancel','InvoiceController@cancel')->name('invoice.cancel')->middleware('cart');
    Route::get('checkout','InvoiceController@checkout')->name('invoice.checkout')->middleware('cart','billingAddress');
    Route::get('checkout/change-address','InvoiceController@selectAddress')->name('invoice.change-address')->middleware('cart','billingAddress');
    Route::get('checkout/set-address/{id}','InvoiceController@setAddress')->name('invoice.set-address')->middleware('cart','billingAddress');
    Route::any('checkout/callback','InvoiceController@callback')->name('invoice.callback')->middleware('cart');
    Route::post('cart/set-method','InvoiceController@setMethod')->name('invoice.set-method');
    Route::get('payment-complete','InvoiceController@complete')->name('invoice.payment-complete');
    Route::get('paypal/setup','PaypalController@setup')->name('paypal.setup');
    Route::get('paypal/callback','PaypalController@callback')->name('paypal.callback');

    Route::get('billing/invoices','InvoiceController@index')->name('billing.invoices');
    Route::get('billing/invoice/{invoice}','InvoiceController@view')->name('billing.invoice')->middleware('billingAddress');
    Route::get('billing/pay-invoice/{invoice}','InvoiceController@pay')->name('billing.pay');


    Route::get('domains','HomeController@domains')->name('domains');
    Route::post('domains/save','HomeController@saveDomain')->name('domains.save');

    Route::resource('billing-address','BillingAddressController');

    Route::get('stats','HomeController@stats')->name('stats');
    Route::get('get-stats','HomeController@getStats')->name('get-stats');

    Route::get('profile','HomeController@profile')->name('profile');
    Route::post('profile','HomeController@saveProfile')->name('save-profile');

});


});