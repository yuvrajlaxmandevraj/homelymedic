<?php

namespace App\Controllers\partner;

use Illuminate\Support\Facades\Session;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use Stripe\Exception\AuthenticationException;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use Config\CustomPaypal;

use App\Controllers\BaseController;
use App\Libraries\Paypal;
// use App\Libraries\Paystack;
use App\Libraries\Razorpay;
use App\Models\Cash_collection_model;
use App\Models\Partners_model;
use App\Models\Service_model;
use App\Models\Service_ratings_model;
use App\Models\Settlement_model;

use App\Libraries\Stripe;
use App\Models\Partner_subscription_model;
use App\Models\Subscription_model;
use Exception;
use PDO;
use Matscode\Paystack\Transaction;
use Matscode\Paystack\Utility\Debug; // for Debugging purpose
use Matscode\Paystack\Utility\Http;
use Yabacon\Paystack;
use Yabacon\Paystack\MetadataBuilder;


require APPPATH . 'Views/backend/partner/Razorpay.php';

use Razorpay\Api\Api;

class Partner extends BaseController
{
    public function __construct()
    {
        helper('function', 'form', 'url', 'filesystem');

        $this->validation = \Config\Services::validation();
        $this->ionAuth = new \IonAuth\Libraries\IonAuth();
        $user = $this->ionAuth->user()->row();


        $this->data['admin'] = $this->userIsAdmin;
        $this->data['partner'] = $this->userIsPartner;
        // $this->data['partnerId'] = $user->id;
        // $this->data['Identity'] = $this->userIdentity;
        $this->settle_commission = new Settlement_model();
        $this->cash_collection = new Cash_collection_model();

        $this->data['settings'] = $this->settings;
        $this->partner = new Partners_model();
        $this->subscription = new Partner_subscription_model();

        $session = session();
        $lang = $session->get('lang');
        if (empty($lang)) {
            $lang = 'en';
        }
        $this->data['current_lang'] = $lang;
        // $this->data['username'] =  $user->username;
        $this->data['languages_locale'] = fetch_details('languages', [], [], null, '0', 'id', 'ASC');
        $profile = '';
        if (!empty($data)) {
            $data = $data[0];
            if ($data['image'] != '') {
                if (check_exists(base_url($data['image']))) {
                    $profile = '<img alt="image" src="' .  base_url($data['image']) . '" class="rounded-circle mr-1">';
                } else {
                    $profile = '<figure class="avatar mb-2 avatar-sm mt-1" data-initial="' . strtoupper($data['username'][0]) . '"></figure>';
                }
            } else {
                $profile = '<figure class="avatar mb-2 avatar-sm mt-1" data-initial="' . strtoupper($data['username'][0]) . '"></figure>';
            }
            $this->data['profile_picture'] = $profile;
        }
        $this->data['profile_picture'] = $profile;

        $this->db      = \Config\Database::connect();
        $this->builder = $this->db->table('settings');
        $this->builder->select('value');
        $this->builder->where('variable', 'payment_gateways_settings');
        $query = $this->builder->get()->getResultArray();
        if (count($query) == 1) {
            $settings = $query[0]['value'];
            $settings = json_decode($settings, true);
        }
        // echo "<pre>";
        // print_r($settings);
        // die;
        $this->stripe_secret_key = $settings['stripe_secret_key'];
        $this->stripe_currency = $settings['stripe_currency'];
    }
    public function review()
    {
        if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
            return redirect('partner/profile');
        }
        $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);
        if (empty($is_already_subscribe)) {
            return redirect('partner/subscription');
        }

        $this->data['title'] = 'Reviews | Partner Panel';
        $this->data['main_page'] = 'reviews';
        return view('backend/partner/template', $this->data);
    }
    public function review_list()
    {

        
        if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
            return redirect('partner/profile');
        }
        $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);
        if (empty($is_already_subscribe)) {
            return redirect('partner/subscription');
        }

        $uri = service('uri');
        $partner_id = $this->userId;

        $ratings_model = new Service_ratings_model();
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';


        return json_encode($ratings_model->ratings_list(false, $search, $limit, $offset, $sort, $order, ['s.user_id' => $partner_id]));
    }
    public function cash_collection()
    {
        if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
            return redirect('partner/profile');
        }
        $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);
        if (empty($is_already_subscribe)) {
            return redirect('partner/subscription');
        }

        $this->data['title'] = 'Cash Collection | Partner Panel';
        $this->data['main_page'] = 'cash_collection_history';
        return view('backend/partner/template', $this->data);
    }

    public function cash_collection_history_list()
    {
        if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
            return redirect('partner/profile');
        }
        $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);
        if (empty($is_already_subscribe)) {
            return redirect('partner/subscription');
        }

        // try {
        $this->data['title'] = 'Cash Collection  | Admin Panel';

        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $where['c.partner_id'] = $this->userId;

        print_r(json_encode($this->cash_collection->list(false, $search, $limit, $offset, $sort, $order, $where)));
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }


    public function settlement()
    {
        if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
            return redirect('partner/profile');
        }
        $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);
        if (empty($is_already_subscribe)) {
            return redirect('partner/subscription');
        }

        $this->data['title'] = 'Commission Settlement | Partner Panel';
        $this->data['main_page'] = 'settlement_history';
        return view('backend/partner/template', $this->data);
    }
    public function settlement_list()
    {
        if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
            return redirect('partner/profile');
        }
        $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);
        if (empty($is_already_subscribe)) {
            return redirect('partner/subscription');
        }

        try {
            $this->data['title'] = 'Commission Settlement | Admin Panel';

            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $where['provider_id'] = $this->userId;
            print_r(json_encode($this->settle_commission->list(false, $search, $limit, $offset, $sort, $order, $where)));
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function payment()
    {
        if ($this->isLoggedIn) {
            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }

            // echo "123";
            $this->data['title'] = 'payment | Partner Panel';
            $this->data['main_page'] = 'payment';
            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
        // Load the payment view
        // return view('partner/payment');
    }


    public function subscription_list()
    {

        if ($this->ionAuth->loggedIn()) {

            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }
            $subscription_details = fetch_details('subscriptions', ['status' => 1, 'publish' => 1]);

            $this->data['subscription_details'] = $subscription_details;
            $user = $this->ionAuth->user()->row();


            $active_subscription_details = fetch_details('partner_subscriptions', ['partner_id' => $user->id, 'status' => 'active']);

            $this->data['active_subscription_details'] = $active_subscription_details;
            $symbol =   get_currency();



            $razorpay = new Razorpay;
            $credentials = $razorpay->get_credentials();


            $key_id = $credentials['key'];
            $secret = $credentials['secret'];

            $data = get_settings('general_settings', true);
            $partner = fetch_details('partner_details', ['partner_id' => $this->userId])[0];


            $this->stripe = new Stripe;
            $stripe_credentials = $this->stripe->get_credentials();

            $this->data['currency'] = $symbol;
            $this->data['title'] = 'Subscription | Partner Panel';
            $this->data['main_page'] = 'subscription';
            $this->data['partner'] = $partner;

            $this->data['data'] = $data;
            $this->data['key_id'] = $key_id;
            $this->data['secret'] = $secret;
            $this->data['stripe_credentials'] = $stripe_credentials;

            $current_active_payment_gateway = get_settings('payment_gateways_settings', true);

            if (isset($current_active_payment_gateway['paypal_status']) && $current_active_payment_gateway['paypal_status'] === 'enable') {
                $payment_gateway = "paypal";
            }
            if ($current_active_payment_gateway['razorpayApiStatus'] === 'enable') {
                $payment_gateway = "razorpay";
            }
            if ($current_active_payment_gateway['paystack_status'] === 'enable') {
                $payment_gateway = "paystack";
            }
            if ($current_active_payment_gateway['stripe_status'] === 'enable') {
                $payment_gateway = "stripe";
            }

            $this->data['payment_gateway'] = $payment_gateway;

            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }


    public function make_payment_for_subscription()
    {


        $subscription_id = $_POST['subscription_id'];
        $subscription_details = fetch_details('subscriptions', ['id' => $subscription_id]);
        $partner_id = $this->ionAuth->user()->row()->id;
        $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $partner_id, 'status' => 'active']);
        if (!empty($is_already_subscribe)) {
            $errorMessage = "Already have active subscription";
            session()->setFlashdata('error', $errorMessage);
            return redirect()->back();
        }
        if ($subscription_details[0]['price'] == "0") {


            add_subscription($subscription_id, $partner_id);
            $errorMessage = "Subscription Activated.";
            session()->setFlashdata('success', $errorMessage);
            return redirect()->back();
        } else {
            $current_active_payment_gateway = get_settings('payment_gateways_settings', true);
            if ($current_active_payment_gateway['paypal_status'] === 'enable') {
                $payment_gateway = "paypal";
            }
            if ($current_active_payment_gateway['razorpayApiStatus'] === 'enable') {
                $payment_gateway = "razorpay";
            }
            if ($current_active_payment_gateway['paystack_status'] === 'enable') {
                $payment_gateway = "paystack";
            }
            if ($current_active_payment_gateway['stripe_status'] === 'enable') {
                $payment_gateway = "stripe";
            }

            $price = calculate_subscription_price($subscription_details[0]['id']);
            $data['client_id'] = $this->userId;
            $data['package_id'] = $subscription_details[0]['id'];
            $data['net_amount'] = $price[0]['price_with_tax'];
            if ($payment_gateway == "stripe") {
                try {
                    \Stripe\Stripe::setApiKey($this->stripe_secret_key);
                    $paymentLink = $this->generatePaymentLink($data);
                    return redirect()->to($paymentLink);
                } catch (AuthenticationException $e) {
                    $errorMessage = "Invalid API Key provided.";
                    session()->setFlashdata('error', $errorMessage);
                    return redirect()->back();
                }
            } else if ($payment_gateway == "razorpay") {
                try {
                    $paymentLink = $this->RazorpaygeneratePaymentLink($data);
                    return redirect()->to($paymentLink);
                } catch (Exception $e) {

                    $errorMessage = "Invalid API Key provided.";
                    session()->setFlashdata('error', $errorMessage);
                    return redirect()->back();
                }
            } else if ($payment_gateway == "paystack") {
                try {
                    $paymentLink = $this->PaystackgeneratePaymentLink($data);
                    return redirect()->to($paymentLink);
                } catch (AuthenticationException $e) {
                    $errorMessage = "Invalid API Key provided.";
                    session()->setFlashdata('error', $errorMessage);
                    return redirect()->back();
                }
            } else if ($payment_gateway == "paypal") {

                $paymentLink = $this->PaypalgeneratePaymentLink($data);


                //  base_url('/api/v1/paypal_transaction_webview?' . 'user_id=' . $user_id . '&order_id=' . $order_id . '&amount=' . intval($amount)),

            }
        }
    }

    private function PaystackgeneratePaymentLink($param)
    {
        // $paystack = new Paystack;

        // $credentials = "";

        $user_data = fetch_details('users', ['id' => $this->userId])[0];
        $secret_key = "sk_test_9beda262a7940bc4569922d43b3e5b8a18391ea7";

        $data = [
            'transaction_type' => 'transaction',
            'user_id' => $this->userId,
            'partner_id' =>  $this->userId,
            'order_id' =>  "0",
            'type' => 'paystack',
            'txn_id' => "0",
            'amount' => $param['net_amount'],
            'status' => 'pending',
            'currency_code' => NULL,
            'subscription_id' => $param['package_id'],
            'message' => 'subscription successfull'
        ];
        $insert_id = add_transaction($data);
        $paystack = new \Yabacon\Paystack($secret_key);
        $metadata = new MetadataBuilder;
        // $metadata->withCustomField('transaction_id', $insert_id);

        $metadata->withTransactionId($insert_id);

        try {
            $transaction = $paystack->transaction->initialize([
                'amount'     => $param['net_amount'] * 100,
                'email'      => $user_data['email'],
                'reference'  => rand(),
                'metadata' => $metadata->build()
            ]);
            $authorization_url = $transaction->data->authorization_url;
            add_subscription($param['package_id'], $this->userId, $insert_id);
            return $authorization_url;
        } catch (\Yabacon\Paystack\Exception\ApiException $e) {
            $errorMessage = $$e;
            session()->setFlashdata('error', $errorMessage);
            return redirect()->back();
        }
        // return ($response->authorizationUrl);
    }
    private function RazorpaygeneratePaymentLink($param)
    {
        $razorpay = new Razorpay;
        $credentials = $razorpay->get_credentials();
        $key_id = $credentials['key'];
        $secret = $credentials['secret'];
        $api = new Api($key_id, $secret);
        $data = [
            'transaction_type' => 'transaction',
            'user_id' => $this->userId,
            'partner_id' =>  $this->userId,
            'order_id' =>  "0",
            'type' => 'razorpay',
            'txn_id' => "0",
            'amount' => $param['net_amount'] * 100,
            'status' => 'pending',
            'currency_code' => NULL,
            'subscription_id' => $param['package_id'],
            'message' => 'subscription successfull'
        ];
        $insert_id = add_transaction($data);
        $checkout = $api->paymentLink->create(array(
            'amount' =>  floatval($param['net_amount']),
            'currency' => "INR",
            'accept_partial' => false,
            'notify' => array('sms' => true, 'email' => true),
            'reminder_enable' => true,
            'notes' => array('policy_name' => 'Subscription', 'transaction_id' => $insert_id),
            'callback_url' => base_url() . '/partner/stripe_success',
            'callback_method' => 'get'
        ));


        add_subscription($param['package_id'], $this->userId, $insert_id);
        return $checkout['short_url'];
    }

    public function PaypalgeneratePaymentLink($param)
    {
        $this->paypal_lib = new Paypal();
        $user = fetch_details('users', ['id' => $this->userId]);

        $data['user'] = $user[0];

        $data['payment_type'] = "paypal";


        $data1 = [
            'transaction_type' => 'transaction',
            'user_id' => $this->userId,
            'partner_id' =>  $this->userId,
            'order_id' =>  "0",
            'type' => 'paypal',
            'txn_id' => "0",
            'amount' => $param['net_amount'],
            'status' => 'pending',
            'currency_code' => NULL,
            'subscription_id' => $param['package_id'],
            'message' => 'subscription successfull'
        ];
        $insert_id = add_transaction($data1);
        add_subscription($param['package_id'], $this->userId, $insert_id);


        $returnURL = base_url() . '/partner/stripe_success';
        $cancelURL = base_url() . '/partner/cancel';
        $notifyURL = base_url() . '/api/webhooks/paypal';
        $userID = $this->userId;
        $payeremail = $data['user']['email'];   // Add fields to paypal form
        $this->paypal_lib->add_field('return', $returnURL);
        $this->paypal_lib->add_field('cancel_return', $cancelURL);
        $this->paypal_lib->add_field('notify_url', $notifyURL);
        $this->paypal_lib->add_field('item_name', 'Test');
        $this->paypal_lib->add_field('custom',  $insert_id. '|' . $payeremail.'|subscription');
        $this->paypal_lib->add_field('item_number', $insert_id);
        $this->paypal_lib->add_field('amount', $param['net_amount']);
        ($this->paypal_lib->paypal_auto_form());
    }


    private function generatePaymentLink($param)
    {
        $this->session       = \Config\Services::session();
        $client_id = $param['client_id'];
        $package_id = $param['package_id'];
        //$amount = floatval($param['net_amount']);
        $amount = floatval($param['net_amount']) * 100;

        $this->db      = \Config\Database::connect();
        $package_name = $this->db->query("SELECT * FROM `subscriptions` WHERE id='$package_id'")->getFirstRow();
        $package_name = ($package_name) ? $package_name->name : '';

        $result = $this->db->query("SELECT * FROM `users` WHERE id='$client_id'")->getFirstRow();

        if ($result->strip_id == '') {
            $customer = \Stripe\Customer::create(array(
                'email' => $result->email,
                'description' => $result->id
            ));
            $this->db->table('users')->update(['strip_id' => $customer['id']], ['id' => $client_id]);
            $stripid = $customer['id'];
            $email = $result->email;
        } else {
            $stripid = $result->strip_id;
            $email = $result->email;
        }
        $this->session->remove('POSTDATA');
        $this->session->set('POSTDATA', $param);
        $data = [
            'transaction_type' => 'transaction',
            'user_id' => $this->userId,
            'partner_id' =>  $this->userId,
            'order_id' =>  "0",
            'type' => 'stripe',
            'txn_id' => "0",
            'amount' => $param['net_amount'],
            'status' => 'pending',
            'currency_code' => NULL,
            'subscription_id' => $package_id,
            'message' => 'subscription successfull'
        ];
        $insert_id = add_transaction($data);
        $metadata = ['transaction_id' => $insert_id];
        $checkout_payment = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $this->stripe_currency,
                    'unit_amount' => $amount,
                    'product_data' => [
                        'name' => $package_name,
                    ],
                ],
                'quantity' => 1,
            ]],
            'customer' => $stripid,
            'client_reference_id' => $client_id,
            'mode' => 'payment',
            'success_url' => base_url() . '/partner/stripe_success',
            'cancel_url' => base_url() . '/partner/cancel',
            'payment_intent_data' => [
                'metadata' => $metadata
            ],
        ]);

        $payment_id = $checkout_payment['payment_intent'];
        $this->session->remove('payment_intent');
        $this->session->set('payment_intent', $payment_id);
        add_subscription($package_id, $this->userId, $insert_id);
        return $checkout_payment['url'];
    }
    public function success()
    {
        $settings = get_settings('general_settings', true);
        $this->data['company'] = (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand Services";
        $this->data['main_page'] = 'payment-success';
        $this->data['title'] = 'Payment Success | ';
        $this->data['keywords'] = 'Payment Success, ';
        $this->data['description'] = 'Payment Success | ';
        $this->data['meta_description'] = '';

        // Redirect to the partner/subscription page after 3 seconds
        header('Refresh: 2; URL=' . base_url() . '/partner/subscription');

        return view('backend/partner/template', $this->data);
    }




    public function cancel()
    {

        $settings = get_settings('general_settings', true);

        $this->data['company'] = (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand Services";
        $this->data['main_page'] = 'payment-cancel';
        $this->data['title'] = 'Payment Cancel | ';
        $this->data['keywords'] = 'Payment Cancel, ';
        $this->data['description'] = 'Payment Cancel | ';
        $this->data['meta_description'] = '';
        return view('backend/partner/template', $this->data);
    }

    public function subscription_history()
    {


        $this->data['company'] = (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand Services";
        $this->data['main_page'] = 'subscription_history';
        $this->data['title'] = 'Subscription History  ';
        $this->data['keywords'] = 'Subscription History , ';
        $this->data['description'] = 'Subscription History   ';
        $this->data['meta_description'] = '';
        return view('backend/partner/template', $this->data);
    }

    public function subscription_history_list()
    {

        // if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
        //     return redirect('partner/profile');
        // }
        // $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);
        // if (empty($is_already_subscribe)) {
        //     return redirect('partner/subscription');
        // }

        // try {

        $this->data['title'] = 'Subscription List | Provider Panel';

        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $where['ps.partner_id'] = $this->userId;
        print_r(json_encode($this->subscription->list(false, $search, $limit, $offset, $sort, $order, $where)));


        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }


    public function razorpay_payment()
    {
        $subscription_details = fetch_details('subscriptions', ['status' => 1, 'publish' => 1]);

        $this->data['subscription_details'] = $subscription_details;
        $user = $this->ionAuth->user()->row();
        $db      = \Config\Database::connect();
        $builder = $db->table('partner_subscriptions ps');


        $active_subscription_details = fetch_details('partner_subscriptions', ['partner_id' => $user->id, 'status' => 'active']);

        $this->data['active_subscription_details'] = $active_subscription_details;

        $razorpay = new Razorpay;
        $credentials = $razorpay->get_credentials();
        $key_id = $credentials['key'];
        $secret = $credentials['secret'];
        $api = new Api($key_id, $secret);
        $order = $api->order->create([
            'receipt' => 'order_receipt_01',
            'amount' => 500,
            'currency' => "INR",
        ]);

        $data = get_settings('general_settings', true);
        $partner = fetch_details('partner_details', ['partner_id' => $this->userId])[0];
        $symbol =   get_currency();
        $this->data['currency'] = $symbol;
        $this->data['title'] = 'Subscription | Partner Panel';
        $this->data['partner'] = $partner;
        $this->data['order'] = $order;
        $this->data['data'] = $data;
        $this->data['key_id'] = $key_id;
        $this->data['secret'] = $secret;
        $this->data['main_page'] = 'subscription';
        return view('backend/partner/template', $this->data);
    }
}
