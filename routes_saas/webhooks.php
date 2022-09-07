<?php


Route::any('webhooks/stripe','Subscriber\WebhooksController@stripe')->name('webhooks.stripe');
Route::any('webhooks/paypal','Subscriber\PaypalController@webhook')->name('webhooks.paypal');
