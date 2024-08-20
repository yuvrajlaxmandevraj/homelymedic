<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(true);
$routes->set404Override(
    function () {
        $data['title'] = "Page not found";
        $data['main_page'] = "error404";
        $data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
        $data['meta_description'] = "";
        return view('frontend/retro/template', $data);
    }
);
$routes->setAutoRoute(true);


if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}


// $routes->get('/admin/login', 'Auth::login');

$routes->add('unauthorised', 'Home::unauthorised');

/**
 *      for migrations
 */

$routes->add('migration/index', 'Migrate::index');
$routes->add('migration/createmigrations', 'Migrate::createmigrations');



/*
======================================
    Customer Route Files
======================================
*/

//1.Admin Route: all the admin routes will in this file
include_once('Routes_admin.php');

//2 Partner Route: all the partner routes will in this file
include_once('Routes_partner.php');

//3 Partner Route APis: all the partner apis routes will in this file
// include_once('Routes_partner_apis.php');

//4 Customer Route APis: all the customer apis routes will in this file
include_once('Routes_customer_apis.php');

$routes->post('partner/api/v1', 'partner/api/V1::index');
$routes->post('partner/api/v1/login', 'partner/api/V1::login');
$routes->post('partner/api/v1/register', 'partner/api/V1::register');
$routes->post('partner/api/v1/verify_user', 'partner/api/V1::verify_user');
$routes->post('partner/api/v1/get_orders', 'partner/api/V1::get_orders');
$routes->post('partner/api/v1/delete_orders', 'partner/api/V1::delete_orders');
$routes->post('partner/api/v1/update_order_status', 'partner/api/V1::update_order_status');

$routes->post('partner/api/v1/get_statistics', 'partner/api/V1::get_statistics');
$routes->post('partner/api/v1/profile', 'partner/api/V1::get_partner');
$routes->post('partner/api/v1/get_settings', 'partner/api/V1::get_settings');

$routes->post('partner/api/v1/get_categories', 'partner/api/V1::get_categories');
$routes->post('partner/api/v1/get_sub_categories', 'partner/api/V1::get_sub_categories');
$routes->post('partner/api/v1/get_all_categories', 'partner/api/V1::get_all_categories');

$routes->post('partner/api/v1/update_fcm', 'partner/api/V1::update_fcm');
$routes->post('partner/api/v1/get_taxes', 'partner/api/V1::get_taxes');
$routes->post('partner/api/v1/get_services', 'partner/api/V1::get_services');
$routes->post('partner/api/v1/manage_service', 'partner/api/V1::manage_service');
$routes->post('partner/api/v1/delete_service', 'partner/api/V1::delete_service');
$routes->post('partner/api/v1/update_service_status', 'partner/api/V1::update_service_status');

$routes->post('partner/api/v1/get_promocodes', 'partner/api/V1::get_promocodes');
$routes->post('partner/api/v1/get_transactions', 'partner/api/V1::get_transactions');
$routes->post('partner/api/v1/manage_promocode', 'partner/api/V1::manage_promocode');
$routes->post('partner/api/v1/delete_promocode', 'partner/api/V1::delete_promocode');
$routes->post('partner/api/v1/get_service_ratings', 'partner/api/V1::get_service_ratings');
$routes->post('partner/api/v1/get_notifications', 'partner/api/V1::get_notifications');
$routes->post('partner/api/v1/get_available_slots', 'partner/api/V1::get_available_slots');




$routes->post('partner/api/v1/send_withdrawal_request', 'partner/api/V1::send_withdrawal_request');
$routes->post('partner/api/v1/get_withdrawal_request', 'partner/api/V1::get_withdrawal_request');
$routes->post('partner/api/v1/delete_withdrawal_request', 'partner/api/V1::delete_withdrawal_request');
$routes->post('partner/api/v1/change-password', 'partner/api/V1::change_password');
$routes->post('partner/api/v1/forgot-password', 'partner/api/V1::forgot_password');


$routes->add('/api/webhooks/stripe', 'api/Webhooks::stripe');
$routes->add('/api/webhooks/paystack', 'api/Webhooks::paystack');
$routes->add('/api/webhooks/razorpay', 'api/Webhooks::razorpay');
$routes->add('/api/webhooks/paypal', 'api/Webhooks::paypal');


$routes->post('/api/v1/is_area_deliverable', 'api/V1::is_area_deliverable');



$routes->post('partner/api/v1/get_cash_collection', 'partner/api/V1::get_cash_collection');

$routes->post('partner/api/v1/get_settlement_history', 'partner/api/V1::get_settlement_history');

$routes->post('partner/api/v1/delete_provider_account', 'partner/api/V1::delete_provider_account');


// subscription 
$routes->post('partner/api/v1/get_subscription', 'partner/api/V1::get_subscription');
$routes->post('partner/api/v1/buy_subscription', 'partner/api/V1::buy_subscription');

$routes->post('partner/api/v1/add_transaction', 'partner/api/V1::add_transaction');
$routes->post('partner/api/v1/razorpay_create_order', 'partner/api/V1::razorpay_create_order');

$routes->post('partner/api/v1/get_subscription_history', 'partner/api/V1::get_subscription_history');



$routes->get('partner/api/v1/paypal_transaction_webview', 'partner/api/V1::paypal_transaction_webview');

$routes->post('partner/api/v1/app_payment_status', 'api/V1::verify_transaction');
// app/Config/Routes.php
