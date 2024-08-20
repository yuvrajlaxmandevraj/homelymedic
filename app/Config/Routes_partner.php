<?php

/*
==================================
        Parnter Panel Routes
==================================
*/

// Login
$routes->get('/partner/login', 'Auth::login');

// Dashbord
$routes->get('partner/', 'partner/Dashboard::index');
$routes->get('/partner/dashboard', 'partner/Dashboard::index');
$routes->get('/partner/dashboard/fetch_sales', 'partner/Dashboard::fetch_sales');
$routes->get('/partner/dashboard/fetch_data', 'partner/Dashboard::fetch_data');
$routes->get('/partner/stripe', 'partner/StripePaymentController::index');



// Services For Partners 
$routes->add('partner/services', 'partner/Services::index');
$routes->add('partner/services/list', 'partner/Services::list');
$routes->add('partner/services/add', 'partner/Services::add');

$routes->add('partner/services/add_service', 'partner/Services::add_service');
$routes->add('partner/services/update_service', 'partner/Services::update_service');
$routes->add('partner/services/delete_service', 'partner/Services::delete');

$routes->add('partner/services/edit_service/(:any)', 'partner/Services::edit_service');


// wallet for partner
$routes->add('partner/wallet_transactions', 'partner/Wallet_transactions::index');

// for profile

$routes->add('partner/profile', 'partner/Profile::index');
$routes->add('partner/update-profile', 'partner/Profile::update');
$routes->add('partner/update_profile', 'partner/Profile::update_profile');

// KYC for Partner
$routes->add('partner/kyc', 'partner/KYC::index');
$routes->add('partner/kyc/add_details', 'partner/KYC::add_details');

// Categories
$routes->add('partner/categories', 'partner/Categories::index');

//check numbe
$routes->add('auth/check_number', 'Auth::check_number');
$routes->add('auth/check_number_for_forgot_password', 'Auth::check_number_for_forgot_password');

// $routes->get('auth/check_number_for_forgot_password', 'Auth::check_number_for_forgot_password');

$routes->add('auth/reset_password_otp', 'Auth::reset_password_otp');


// orders
$routes->add('partner/orders', 'partner/Orders::index');
$routes->add('partner/orders/list', 'partner/Orders::list');
$routes->add('partner/orders/veiw_orders/(:any)', 'partner/Orders::view_orders');
$routes->add('partner/orders/invoice/(:any)', 'partner/Orders::invoice');
$routes->add('partner/orders/invoice_table/(:any)', 'partner/Orders::invoice_table');
$routes->add('partner/orders/order_summary_table/(:any)', 'partner/Orders::order_summary_table');
$routes->add('partner/orders/update_order_status', 'partner/Orders::update_order_status');
$routes->add('partner/orders/get_slots', 'partner/Orders::get_slots');
$routes->add('partner/orders/change_order_status', 'partner/Orders::change_order_status');
$routes->add('partner/orders/newList', 'partner/Orders::newList');

$routes->add('partner/orders/test', 'partner/Orders::test');

// promot codes
$routes->add('partner/promo_codes', 'partner/Promo_codes::index');
$routes->add('partner/promo_codes/add', 'partner/Promo_codes::add');
$routes->add('partner/promo_codes/save', 'partner/Promo_codes::save');
$routes->add('partner/promo_codes/list', 'partner/Promo_codes::list');
$routes->add('partner/promo_codes/delete', 'partner/Promo_codes::delete');

$routes->add('partner/withdrawal_requests', 'partner/Withdrawal_requests::index');
$routes->add('partner/withdrawal_requests/save', 'partner/Withdrawal_requests::save');
$routes->add('partner/withdrawal_requests/send', 'partner/Withdrawal_requests::send');
$routes->add('partner/withdrawal_requests/delete', 'partner/Withdrawal_requests::delete');
$routes->add('partner/withdrawal_requests/list', 'partner/Withdrawal_requests::list');
$routes->add('partner/review', 'partner/Partner::review');
$routes->add('partner/review_list', 'partner/Partner::review_list');

$routes->add('partner/cash_collection', 'partner/Partner::cash_collection');
$routes->add('partner/cash_collection_list', 'partner/Partner::cash_collection_history_list');

$routes->add('partner/settlement', 'partner/Partner::settlement');
$routes->add('partner/settlement_list', 'partner/Partner::settlement_list');




$routes->add('partner/transactions', 'partner/Transactions::index');
$routes->add('partner/transactions/list', 'partner/Transactions::list');

$routes->add('partner/update_partner', 'admin/Partners::update_partner');

$routes->add('partner/subscription', 'partner/Partner::subscription_list');
// $routes->add('partner/subscription', 'partner/Partner::razorpay_payment');


$routes->add('partner/subscription_history', 'partner/Partner::subscription_history');
$routes->add('partner/subscription-payment', 'partner/Subscription::subscription_payment');

$routes->add('partner/subscription_history_list', 'partner/Partner::subscription_history_list');


$routes->add('partner/subscription/pre-payment-setup', 'partner/Subscription::pre_payment_setup');
$routes->post('partner/make_payment_for_subscription', 'partner/Partner::make_payment_for_subscription');
$routes->get('partner/stripe_success', 'partner/Partner::success');
$routes->get('partner/cancel', 'partner/Partner::cancel');


$routes->get('partner/payment/checkout/(:any)', 'partner/Partner::checkout');
$routes->get('payment/intent/(:any)', 'partner/Partner::createPaymentIntent/');


$routes->get('razorpay-payment-form', 'partner/Partner::payWithRazorpay');
// Post Route For making Payment Request
$routes->post('razorpay-payment', 'partner/Partner::processPayment');
