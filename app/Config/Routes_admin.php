<?php



//Admin login

$routes->get('/admin/login', 'Auth::login');


// Get Route For Show Payment Form
$routes->get('payment-form', 'RazorpayController::payWithRazorpay');
// Post Route For making Payment Request
$routes->post('payment', 'RazorpayController::processPayment');

//...

$routes->get('update_subscription_status', 'admin/Dashboard::update_subscription_status');
$routes->get('cancle_elapsed_time_order', 'admin/Dashboard::cancle_elapsed_time_order');





$routes->get('/customer_privacy_policy', 'Auth::customer_privacy_policy');

$routes->add('admin/forgot-password', 'admin/Dashboard::forgot_password');
// $routes->get('/homeStripe', 'StripePaymentController::index');
//...
$routes->add('admin', 'admin/Dashboard::index');


$routes->add('admin/dashboard', 'admin/Dashboard::index');
$routes->add('admin/dashboard/recent_booking', 'admin/Dashboard::recent_orders');
$routes->add('admin/dashboard/top_trending_services', 'admin/Dashboard::top_trending_services');






$routes->add('admin/profile', 'admin/Profile::index');
$routes->add('admin/profile/update', 'admin/Profile::update');

//this is for languages

$routes->get('lang/(:any)', 'Language::index/$1');
$routes->get('admin/languages/', "admin\Languages::index");
$routes->post('admin/languages/create', "admin/Languages::create");
$routes->post('admin/languages/set_labels', "admin/Languages::set_labels");
$routes->get('admin/languages/change/(:any)', "admin\Languages::change/$1");




//this os for settings
$routes->add('admin/settings', 'admin/Settings::index');
$routes->add('admin/settings/themes', 'admin/Settings::themes');
$routes->add('admin/settings/general-settings', 'admin/Settings::general_settings');
// $routes->add('admin/settings/general-settings', 'admin/Settings::general_settings');

$routes->add('admin/settings/email-settings', 'admin/Settings::email_settings');
$routes->add('admin/settings/pg-settings', 'admin/Settings::pg_settings');
$routes->add('admin/settings/api_key_settings', 'admin/Settings::api_key_settings');
$routes->add('admin/settings/system_tax_settings', 'admin/Settings::system_tax_settings');
$routes->add('admin/settings/app_settings', 'admin/Settings::app_settings');
$routes->add('admin/settings/customer_privacy_policy_page', 'admin/Settings::customer_privacy_policy_page');
$routes->add('admin/settings/partner_privacy_policy_page', 'admin/Settings::partner_privacy_policy_page');
$routes->add('admin/settings/firebase_settings', 'admin/Settings::firebase_settings');



$routes->add('admin/settings/customer_terms_and_condition', 'admin/Settings::customer_tearms_and_condition');
$routes->add('admin/settings/provider_terms_and_condition', 'admin/Settings::provider_terms_and_condition');
$routes->add('admin/settings/refund_policy_page', 'admin/Settings::refund_policy_page');



$routes->add('admin/settings/system-settings', 'admin/Settings::main_system_setting_page');

//this is for categories
$routes->add('admin/categories/', 'admin/Categories::index');
$routes->add('admin/category/add_category', 'admin/Categories::add_category');
$routes->add('admin/category/remove_category', 'admin/Categories::remove_category');
$routes->add('admin/category/update_category', 'admin/Categories::update_category');
$routes->add('admin/categories/list', 'admin/Categories::list');
$routes->add('admin/test/test', 'admin/Test::test');




$routes->add('admin/offers', 'admin/Offers::index');
$routes->add('admin/offers/list', 'admin/Offers::list');
$routes->add('admin/offers/add_offer', 'admin/Offers::add_offer');
$routes->add('admin/offers/delete_offer', 'admin/Offers::delete_offer');
$routes->add('admin/offers/update_offer', 'admin/Offers::update_offer');




//features and sections
$routes->add('admin/Featured_sections', 'admin/Featured_sections::index');
$routes->add('admin/featured_sections/add_featured_section', 'admin/Featured_sections::add_featured_section');
$routes->add('admin/featured_sections/get_custom_services', 'admin/Featured_sections::get_custom_services');
$routes->add('admin/featured_sections/list', 'admin/Featured_sections::list');
$routes->add('admin/featured_sections/update_featured_section', 'admin/Featured_sections::update_featured_section');
$routes->add('admin/featured_sections/delete_featured_section', 'admin/Featured_sections::delete_featured_section');
$routes->add('admin/featured-section/change-order', 'admin/Featured_sections::change_order');




$routes->add('admin/promo_codes', 'admin/Promo_codes::index');
$routes->add('admin/promo_codes/list', 'admin/Promo_codes::list');
$routes->add('admin/promo_codes/delete', 'admin/Promo_codes::delete_promo_code');

// $routes->add('admin/promo_codes/add', 'admin/Promo_codes::add');


$routes->add('admin/promo_codes/add', 'admin/Promo_codes::add');
$routes->add('admin/promo_codes/save', 'admin/Promo_codes::save');
$routes->add('admin/promo_codes/update', 'admin/Promo_codes::update');

$routes->add('admin/sliders', 'admin/Sliders::index');
$routes->add('admin/sliders/list', 'admin/Sliders::list');
$routes->add('admin/sliders/add_slider', 'admin/Sliders::add_slider');
$routes->add('admin/sliders/update_slider', 'admin/Sliders::update_slider');
$routes->add('admin/sliders/delete_sliders', 'admin/Sliders::delete_sliders');




$routes->add('admin/partners', 'admin/Partners::index');
$routes->add('admin/partners/list', 'admin/Partners::list');
$routes->add('admin/partners/add_partner', 'admin/Partners::add_partner');
$routes->add('admin/partner/insert_partner', 'admin/Partners::insert_partner');

$routes->add('admin/partners/edit_partner/(:any)', 'admin/Partners::edit_partner');

$routes->add('admin/partners/general_outlook/(:any)', 'admin/Partners::general_outlook');
$routes->add('admin/partners/partner_company_information/(:any)', 'admin/Partners::partner_company_information');
$routes->add('admin/partners/partner_service_details/(:any)', 'admin/Partners::partner_service_details');
$routes->add('admin/partners/partner_order_details/(:any)', 'admin/Partners::partner_order_details');
$routes->add('admin/partners/partner_order_details_list/(:any)', 'admin/Partners::partner_order_details_list');

$routes->add('admin/partners/partner_promocode_details/(:any)', 'admin/Partners::partner_promocode_details');
$routes->add('admin/partners/partner_promocode_details_list/(:any)', 'admin/Partners::partner_promocode_details_list');


$routes->add('admin/partners/partner_review_details/(:any)', 'admin/Partners::partner_review_details');
$routes->add('admin/partners/partner_review_details_list/(:any)', 'admin/Partners::partner_review_details_list');


$routes->add('admin/partners/partner_fetch_sales/(:any)', 'admin/Partners::partner_fetch_sales');



$routes->add('admin/partners/partner_subscription/(:any)', 'admin/Partners::partner_subscription');


$routes->get('admin/partners/all_subscription/(:any)', 'admin/Partners::all_subscription_list');











$routes->add('admin/partners/view_partner/(:any)', 'admin/Partners::view_partner');
$routes->add('admin/partners/partner_details/(:any)', 'admin/Partners::partner_details');
$routes->add('admin/partners/banking_details/(:any)', 'admin/Partners::banking_details');
$routes->add('admin/partners/timing_details/(:any)', 'admin/Partners::timing_details');
$routes->add('admin/partners/service_details/(:any)', 'admin/Partners::service_details');

$routes->post('admin/partner/deactivate_partner', 'admin/Partners::deactivate_partner');
$routes->post('admin/partner/activate_partner', 'admin/Partners::activate_partner');
$routes->post('admin/partner/approve_partner', 'admin/Partners::approve_partner');
$routes->post('admin/partner/disapprove_partner', 'admin/Partners::disapprove_partner');
$routes->post('admin/partner/delete_partner', 'admin/Partners::delete_partner');

$routes->add('admin/partners/payment_request', 'admin/Partners::payment_request');
$routes->add('admin/partners/payment_request_list', 'admin/Partners::payment_request_list');
$routes->add('admin/partners/payment_request_multiple_update', 'admin/Partners::payment_request_multiple_update');

$routes->add('admin/partners/payment_request_settement_status','admin/Partners::payment_request_settement_status');




$routes->add('admin/partners/edit_request', 'admin/Partners::payment_request_list');
$routes->add('admin/partners/pay_partner', 'admin/Partners::pay_partner');
$routes->add('admin/partners/delete_request', 'admin/Partners::delete_request');

// 
$routes->add('admin/users', 'admin/Users::index');
$routes->add('admin/users/deactivate', 'admin/Users::deactivate');
$routes->add('admin/users/activate', 'admin/Users::activate');
$routes->add('admin/list-user', 'admin/Users::list_user');
$routes->add('admin/addresses', 'admin/Addresses::index');
$routes->add('admin/addresses/list', 'admin/Addresses::list');


$routes->add('admin/services', 'admin/Services::index');
$routes->add('admin/services/list', 'admin/Services::list');

$routes->add('admin/services/add_service', 'admin/Services::add_service_view');
$routes->add('admin/services/insert_service', 'admin/Services::add_service');

$routes->add('admin/services/delete_service', 'admin/Services::delete_service');

$routes->add('admin/services/edit_service/(:any)', 'admin/Services::edit_service');


$routes->add('admin/services/update_service', 'admin/Services::update_service');

$routes->add('admin/services/service_detail/(:any)', 'admin/Services::service_detail');

//
$routes->add('admin/orders', 'admin/Orders::index');
$routes->add('admin/orders/list', 'admin/Orders::list');
$routes->add('admin/orders/veiw_orders/(:any)', 'admin/Orders::view_orders');
$routes->add('admin/orders/view_user/(:any)', 'admin/Orders::view_user');
$routes->add('admin/orders/view_payment_details/(:any)', 'admin/Orders::view_payment_details');
$routes->add('admin/orders/change_order_status', 'admin/Orders::change_order_status');
$routes->add('admin/orders/upload_file', 'admin/Orders::upload_file');


// $routes->add('admin/orders/list','admin/Orders::list');


$routes->add('admin/orders', 'admin/Orders::index');
$routes->add('admin/orders/list', 'admin/Orders::list');
$routes->add('admin/Orders/delete_orders', 'admin/Orders::delete_orders');

$routes->add('admin/orders/veiw_orders/(:any)', 'admin/Orders::view_orders');
$routes->add('admin/orders/invoice/(:any)', 'admin/Orders::invoice');
$routes->add('admin/orders/invoice_table/(:any)', 'admin/Orders::invoice_table');
$routes->add('admin/orders/customer_details/(:any)', 'admin/Orders::customer_details');
$routes->add('admin/orders/payment_details/(:any)', 'admin/Orders::payment_details');
$routes->add('admin/orders/partner_details/(:any)', 'admin/Orders::partner_details');

$routes->add('admin/faqs', 'admin/Faqs::index');
$routes->add('admin/faqs/add_faqs', 'admin/Faqs::add_faqs');
$routes->add('admin/faqs/list', 'admin/Faqs::list');
$routes->add('admin/faqs/remove_faqs', 'admin/Faqs::remove_faqs');
$routes->add('admin/faqs/edit_faqs', 'admin/Faqs::edit_faqs');


$routes->add('admin/notification', 'admin/Notification::index');
$routes->add('admin/notification/add_notification', 'admin/Notification::add_notification');
$routes->add('admin/notification/delete_notification', 'admin/Notification::delete_notification');
$routes->add('admin/notification/list', 'admin/Notification::list');








$routes->add('admin/taxes', 'admin/Tax::index');
$routes->add('admin/tax/add_tax', 'admin/Tax::add_tax');
$routes->add('admin/tax/list', 'admin/Tax::list');
$routes->add('admin/tax/edit_taxes', 'admin/Tax::edit_taxes');
$routes->add('admin/tax/remove_taxes', 'admin/Tax::remove_taxes');






$routes->add('admin/tickets', 'admin/Tickets::index');
$routes->add('admin/tickets/add_tickets', 'admin/Tickets::add_tickets');
$routes->add('admin/tickets/list', 'admin/Tickets::list');
$routes->add('admin/tickets/remove_tickets', 'admin/Tickets::remove_tickets');
$routes->add('admin/tickets/edit_tickets', 'admin/Tickets::edit_tickets');

// chat system stuffs
$routes->add('admin/show_tickets', 'admin/Show_tickets::index');
$routes->add('admin/show_tickets/list', 'admin/Show_tickets::list');
$routes->add('admin/show_tickets/fetch_chat', 'admin/Show_tickets::fetch_chat');
$routes->add('admin/show_tickets/send_message', 'admin/Show_tickets::send_message');
$routes->add('admin/show_tickets/change_status', 'admin/Show_tickets::change_status');
$routes->add('admin/show_tickets/roll_up_chat', 'admin/Show_tickets::roll_up_chat');








// THIS ROUTES WILL LEAD TO THE INTERNAL PAGES OF SETTINGS
$routes->add('admin/settings/terms-and-conditions', 'admin/Settings::terms_and_conditions');
$routes->add('admin/settings/privacy-policy', 'admin/Settings::privacy_policy');
$routes->add('admin/settings/refund-policy', 'admin/Settings::refund_policy');
// 

// NEW ONES IN COLLECTIONS
$routes->add('admin/settings/customer-terms-and-conditions', 'admin/Settings::customer_terms_and_conditions');
$routes->add('admin/settings/customer-privacy-policy', 'admin/Settings::customer_privacy_policy');
//

// Language removing from here 
$routes->add('admin/languages/remove', 'admin/Languages::remove');
$routes->add('admin/settings/updater', 'admin/Updater::index');
$routes->add('admin/upload_update_file', 'admin/Updater::upload_update_file');
$routes->add('admin/settings/about-us', 'admin/Settings::about_us');
$routes->add('admin/settings/contact-us', 'admin/Settings::contact_us');
$routes->add('admin/settings/app', 'admin/Settings::app_settings');

$routes->add('admin/settings/country_codes', 'admin/Settings::contry_codes');
$routes->add('admin/settings/add_contry_code', 'admin/Settings::add_contry_code');
$routes->add('admin/settings/fetch_contry_code', 'admin/Settings::fetch_contry_code');
$routes->add('admin/settings/delete_contry_code', 'admin/Settings::delete_contry_code');
$routes->add('admin/settings/store_default_language', 'admin/Settings::store_default_language');
$routes->add('admin/settings/update_country_codes', 'admin/Settings::update_country_codes');




//subscription
// $routes->add('admin/subscription/', 'admin/Subscription::index');
$routes->add('admin/subscription/', 'admin/Subscription::index', ['as' => 'admin_subscription']);

$routes->add('admin/subscription/add_subscription', 'admin/Subscription::add_subscription');
$routes->add('admin/subscription/add_store_subscription', 'admin/Subscription::add_store_subscription');
$routes->add('admin/subscription/edit_subscription_page/(:any)', 'admin/Subscription::edit_subscription_page');
$routes->add('admin/subscription/edit_subscription', 'admin/Subscription::edit_subscription');
$routes->add('admin/subscription/delete_subscription', 'admin/Subscription::delete_subscription');

$routes->add('admin/subscription/list', 'admin/Subscription::list');
$routes->add('admin/add_ons/', 'admin/Subscription::add_ons_index');
$routes->add('admin/add_ons/create_add_ons', 'admin/Subscription::add_on_create_page');
$routes->add('admin/subscription/subscriber_list', 'admin/Subscription::subscriber_list');
$routes->add('admin/subscription/partner_subscriber_list', 'admin/Subscription::partner_subscription_list');






$routes->add('admin/transactions', 'admin/Transactions::index');
$routes->add('admin/transactions/list-transactions', 'admin/Transactions::list_transactions');





// Cities
$routes->add('admin/cities', 'admin/Cities::index');
$routes->add('admin/add-city', 'admin/Cities::add_city');
$routes->add('admin/city/list', 'admin/Cities::list');
$routes->add('admin/cities/remove_city', 'admin/Cities::remove_city');
$routes->add('admin/cities/edit_city', 'admin/Cities::edit_city');


//comman routes
$routes->add('admin/delete_details', 'admin/Admin::delete_details');



// 
$routes->add('admin/system_users', 'admin/System_users::index');
$routes->add('admin/system_users/list', 'admin/System_users::list');
$routes->add('admin/system_users/deactivate_user', 'admin/System_users::deactivate_user');
$routes->add('admin/system_users/activate_user', 'admin/System_users::activate_user');
$routes->add('admin/system_users/delete_user', 'admin/System_users::delete_user');
// 

$routes->add('admin/system_users/add_user', 'admin/System_users::add_user');
$routes->add('admin/system_users/permit', 'admin/System_users::permit');
$routes->add('admin/system_users/edit_permit', 'admin/System_users::edit_permit');

// cron job
$routes->add('admin/cron_job', 'admin/Cron_job::index');

// for unsettled commissions
$routes->add('admin/partners/settle_commission', 'admin/Partners::settle_commission');
$routes->add('admin/partners/commission_list', 'admin/Partners::commission_list');
$routes->add('admin/partners/bulk_commission_settelement', 'admin/Partners::bulk_commission_settelement');



$routes->add('admin/partners/commission_pay_out', 'admin/Partners::commission_pay_out');
$routes->add('admin/partners/view_ratings/(:any)', 'admin/Partners::view_ratings');
$routes->add('admin/partners/delete_rating', 'admin/Partners::delete_rating');
$routes->add('admin/partners/cash_collection', 'admin/Partners::cash_collection');
$routes->add('admin/partners/cash_collection_list', 'admin/Partners::cash_collection_list');
$routes->add('admin/partners/cash_collection_deduct', 'admin/Partners::cash_collection_deduct');
$routes->add('admin/partners/cash_collection_history', 'admin/Partners::cash_collection_history');  
$routes->add('admin/partners/manage_commission_history', 'admin/Partners::settle_commission_history');  
$routes->add('admin/partners/manage_commission_history_list', 'admin/Partners::manage_commission_history_list');
$routes->add('admin/partners/cash_collection_history_list', 'admin/Partners::cash_collection_history_list');
$routes->add('admin/partners/bulk_cash_collection', 'admin/Partners::bulk_cash_collection');



// for ordered services
$routes->add('admin/orders/view_ordered_services', 'admin/Orders::view_ordered_services');
$routes->add('admin/orders/view_ordered_services_list', 'admin/Orders::view_ordered_services_list');
$routes->add('admin/orders/cancel_order_service', 'admin/Orders::cancel_order_service');
$routes->add('admin/orders/get_slots', 'admin/Orders::get_slots');

$routes->post('admin/languages/insert', "admin/Languages::insert");


$routes->get('download_sample_file','admin/Languages::language_sample');

$routes->add('download_old_file/(:any)','admin/Languages::language_old');
$routes->add('admin/language/list', 'admin/Languages::list');
$routes->add('admin/language/update', 'admin/Languages::update');
$routes->add('admin/language/remove_langauge', 'admin/Languages::remove');



$routes->add('save-web-token','admin/Dashboard::save_web_token');


$routes->post('test', "admin/Dashboard::test");





$routes->post('admin/assign_subscription_to_partner', 'admin/Partners::assign_subscription_to_partner');
$routes->post('admin/cancle_subscription_plan', 'admin/Partners::cancel_subscription_plan');


$routes->add('admin/settings/web_setting', 'admin/Settings::web_setting_page');
$routes->add('admin/settings/web_setting_update', 'admin/Settings::web_setting_update');





// $routes->add('admin/partners/general_outlook/(:any)', 'admin/Partners::general_outlook');