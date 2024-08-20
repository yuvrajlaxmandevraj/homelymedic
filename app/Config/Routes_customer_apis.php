<?php


/*
=======================
    Customer APIs
=======================
*/


$routes->post('api/v1/index', 'api/V1::index');
$routes->post('/api/v1/manage_user', 'api/V1::manage_user');
$routes->post('/api/v1/update_user', 'api/V1::update_user');
$routes->post('/api/v1/update_fcm', 'api/V1::update_fcm');
$routes->post('/api/v1/get_settings', 'api/V1::get_settings');
$routes->post('/api/v1/get_sections', 'api/V1::get_sections');
$routes->post('/api/v1/add_transaction', 'api/V1::add_transaction');
$routes->post('/api/v1/get_transactions', 'api/V1::get_transactions');
$routes->post('/api/v1/add_address', 'api/V1::add_address');
$routes->post('/api/v1/delete_address', 'api/V1::delete_address');
$routes->post('/api/v1/get_address', 'api/V1::get_address');
$routes->post('/api/v1/validate_promo_code', 'api/V1::validate_promo_code');
$routes->post('/api/v1/get_promo_codes', 'api/V1::get_promo_codes');
$routes->post('/api/v1/get_categories', 'api/V1::get_categories');
$routes->post('/api/v1/get_sub_categories', 'api/V1::get_sub_categories');
$routes->post('/api/v1/get_sliders', 'api/V1::get_sliders');
$routes->post('/api/v1/get_providers', 'api/V1::get_providers');
$routes->post('/api/v1/get_services', 'api/V1::get_services');
$routes->post('/api/v1/get_cities', 'api/V1::get_cities');
$routes->post('/api/v1/is_city_deliverable', 'api/V1::is_city_deliverable');
$routes->post('/api/v1/manage_cart', 'api/V1::manage_cart');
$routes->post('/api/v1/remove_from_cart', 'api/V1::remove_from_cart');
$routes->post('/api/v1/get_cart', 'api/V1::get_cart');

$routes->post('/api/v1/place_order', 'api/V1::place_order'); 

$routes->post('/api/v1/get_orders', 'api/V1::get_orders');
$routes->post('/api/v1/manage_notification', 'api/V1::manage_notification');
$routes->post('/api/v1/get_notifications', 'api/V1::get_notifications');
$routes->post('/api/v1/get_ticket_types', 'api/V1::get_ticket_types');
$routes->post('/api/v1/add_ticket', 'api/V1::add_ticket');
$routes->post('/api/v1/edit_ticket', 'api/V1::edit_ticket');
$routes->post('/api/v1/get_tickets', 'api/V1::get_tickets');
$routes->post('/api/v1/send_message', 'api/V1::send_message');
$routes->post('/api/v1/get_messages', 'api/V1::get_messages');
$routes->post('/api/v1/book_mark', 'api/V1::book_mark');
$routes->post('/api/v1/update_order_status', 'api/V1::update_order_status');

$routes->post('/api/v1/get_available_slots', 'api/V1::get_available_slots');
$routes->post('/api/v1/check_available_slot', 'api/V1::check_available_slot');
$routes->post('/api/v1/razorpay_create_order', 'api/V1::razorpay_create_order');
$routes->post('/api/v1/update_service_status', 'api/V1::update_service_status');
$routes->post('/api/v1/get_faqs', 'api/V1::get_faqs');
$routes->post('/api/v1/verify_user', 'api/V1::verify_user');


$routes->post('/api/v1/test', 'api/V1::test');
$routes->post('/api/v1/get_ratings', 'api/V1::get_ratings');
$routes->post('/api/v1/add_rating', 'api/V1::add_rating');
$routes->post('/api/v1/update_rating', 'api/V1::update_rating');
$routes->post('/api/v1/manage_service', 'api/V1::manage_service');
$routes->post('/api/v1/delete_user_account', 'api/V1::delete_user_account');
$routes->post('/api/v1/logout', 'api/V1::logout');

// 
$routes->post('/api/v1/get_home_screen_data', 'api/V1::get_home_screen_data');

$routes->post('/api/v1/provider_check_availability', 'api/V1::provider_check_availability');



// for payment gateways
$routes->post('/api/v1/generate_paytm_txn_token', 'api/V1::generate_paytm_txn_token');
$routes->post('/api/v1/validate_paytm_checksum', 'api/V1::validate_paytm_checksum');
$routes->post('/api/v1/flutterwave', 'api/V1::flutterwave');
$routes->post('/api/v1/paystack', 'api/V1::paystack');
$routes->post('/api/v1/get_paypal_link', 'api/V1::get_paypal_link');
$routes->get('/api/v1/paypal_transaction_webview', 'api/V1::paypal_transaction_webview');
// $routes->post('/api/v1/paypal_transaction_webview', 'api/V1::paypal_transaction_webview');
$routes->get('/api/v1/app_payment_status', 'api/V1::app_payment_status');
$routes->post('/api/v1/ipn', 'api/V1::ipn');


// $routes->get('admin/languages/change/(:any)', "admin\Languages::change/$1");

$routes->post('/api/v1/invoice-download', 'api/V1::invoice_download');


$routes->post('/api/v1/get-time-slots', 'api/V1::get_time_slots');

$routes->post('/api/v1/verify-transaction', 'api/V1::verify_transaction');


$routes->post('/api/v1/contact_us_api', 'api/V1::contact_us_api');
$routes->post('/api/v1/search', 'api/V1::search');


$routes->get('/api/v1/getPlaceAddress', 'api/V1::getPlaceAddress');
$routes->post('/api/v1/search_services_providers', 'api/V1::search_services_providers');





$routes->get('/api/v1/capturePayment', 'api/V1::capturePayment');





