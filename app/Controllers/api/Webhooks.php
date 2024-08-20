<?php

namespace App\Controllers\api;

use App\Controllers\BaseController;
use App\Libraries\Flutterwave;
use App\Libraries\Paystack;
use App\Libraries\Razorpay;
use App\Libraries\Stripe;
use App\Libraries\Paypal;


class Webhooks extends BaseController
{
    private $stripe;


    public function __construct()
    {
        $this->stripe = new Stripe;
        $this->paypal_lib = new Paypal();
        helper('api');
        helper("function");
        $this->settings = get_settings('general_settings', true);
        date_default_timezone_set($this->settings['system_timezone']); // Added user timezone
    }
    public function stripe()
    {
        $credentials = $this->stripe->get_credentials();
        $request_body = file_get_contents('php://input');
        $event = json_decode($request_body, FALSE);


        if (!empty($event->data->object->payment_intent)) {
            $txn_id = (isset($event->data->object->payment_intent)) ? $event->data->object->payment_intent : "";


            if (!empty($txn_id)) {

                if (isset($event->data->object->metadata) && !empty($event->data->object->metadata->order_id)) {
                    // Process the metadata and retrieve order details
                    $amount = ($event->data->object->amount / 100);
                    $currency = $event->data->object->currency;
                    $order_id = $event->data->object->metadata->order_id;

                    $order_data = fetch_details('orders', ["id" => $order_id]);
                    $user_id = $order_data[0]['user_id'];
                    $partner_id = $order_data[0]['partner_id'];
                    // Continue with your code logic
                }
            }
        } else {
            $order_id = 0;
            $amount = 0;
            $currency = (isset($event->data->object->currency)) ? $event->data->object->currency : "";
        }

        $http_stripe_signature = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : "";
        $result = $this->stripe->construct_event($request_body, $http_stripe_signature, $credentials['webhook_key']);
        if ($result == "Matched") {
            log_message('error', '$event ' . var_export($event, true));
            if ($event->type == 'charge.succeeded') {

                if (!isset($event->data->object->metadata->transaction_id) && !empty($event->data->object->metadata->transaction_id)) {
                    $transaction_details_for_subscription = fetch_details('transactions', ['id' => $event->data->object->metadata->transaction_id]);
                    $details_for_subscription = fetch_details('subscriptions', ['id' => $transaction_details_for_subscription[0]['subscription_id']]);


                    if (!empty($transaction_details_for_subscription)) {

                        if (isset($transaction_details_for_subscription[0])) {
                            log_message('error', 'FOR SUBSCRIPTION');
                            update_details(['status' => 'success', 'txn_id' => $event->data->object->payment_intent], ['id' => $event->data->object->metadata->transaction_id], 'transactions');
                            // update_details(['status' => 'active'], ['subscription_id' => $transaction_details_for_subscription[0]['subscription_id'],'partner_id'=>$transaction_details_for_subscription[0]['user_id'],'status'=>'pending'], 'partner_subscriptions');

                            $purchaseDate = date('Y-m-d');
                            $subscriptionDuration = $details_for_subscription[0]['duration'];
                            $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
                            if ($subscriptionDuration == "unlimited") {
                                $subscriptionDuration = 0;
                            }
                            update_details(['status' => 'active', 'is_payment' => '1', 'purchase_date' => $purchaseDate, 'expiry_date' => $expiryDate, 'updated_at' => date('Y-m-d h:i:s')], [
                                'subscription_id' => $transaction_details_for_subscription[0]['subscription_id'],
                                'partner_id' => $transaction_details_for_subscription[0]['user_id'],
                                'transaction_id' => $event->data->object->metadata->transaction_id,
                            ], 'partner_subscriptions');
                        }
                    }
                } else {
                    $data = [
                        'transaction_type' => 'transaction',
                        'user_id' => $user_id,
                        'partner_id' => $partner_id,
                        'order_id' => $order_id,
                        'type' => 'stripe',
                        'txn_id' => $txn_id,
                        'amount' => $amount,
                        'status' => 'success',
                        'currency_code' => $currency,
                        'message' => 'Order placed successfully',
                    ];


                    $insert_id = add_transaction($data);


                    send_web_notification('New Booking Notification', 'We are pleased to inform you that you have received a new Booking.');
                    $settings = get_settings('general_settings', true);
                    $icon = $settings['logo'];
                    //customer email
                    $userdata = fetch_details('users', ['id' => $user_id], ['email', 'username']);
                    $data = array(
                        'name' => $userdata[0]['username'],
                        'title' => "Booking Received Confirmation",
                        'logo' => base_url("public/uploads/site/" . $icon),
                        'first_paragraph' => 'We are thrilled to inform you that your Booking has been successfully placed and confirmed. Thank you for choosing our services to fulfill your needs.',
                        'second_paragraph' => 'If you have any questions or concerns regarding your Booking, please do not hesitate to contact us. We will be more than happy to assist you.',
                        'third_paragraph' => 'Thank you again for choosing our services. We look forward to doing business with you again.',
                        'company_name' => $settings['company_title'],
                    );


                    if (!empty($userdata[0]['email'])) {
                        $user_email = email_sender($userdata[0]['email'], 'Booking Received Confirmation', view('backend/admin/pages/provider_email', $data));
                    }
                    //for provider
                    $partner_data = fetch_details('partner_details', ['partner_id' => $partner_id], ['company_name']);
                    $user_partner_data = fetch_details('users', ['id' => $partner_id], ['email', 'username']);
                    $data1 = array(
                        'name' => $partner_data[0]['company_name'],
                        'title' => "New Booking Notification",
                        'order_id' => $order_id,
                        'logo' => base_url("public/uploads/site/" . $icon),
                        'first_paragraph' => 'We are pleased to inform you that you have received a new Booking. ',
                        'second_paragraph' => 'Please note that the customer expects high-quality service from our providers. We kindly ask that you deliver the Booking by the expected delivery date and maintain excellent communication with the customer throughout the process.',
                        'third_paragraph' => 'Thank you for your cooperation and dedication to providing excellent service. We look forward to continuing our partnership with you.',
                        'company_name' => $settings['company_title'],
                    );
                    if (!empty($user_partner_data[0]['email'])) {
                        $user_parter_email = email_sender($user_partner_data[0]['email'], 'New Booking Notification', view('backend/admin/pages/provider_email', $data1));
                    }
                    //for app notification
                    $db      = \Config\Database::connect();
                    $to_send_id = $partner_id;
                    $builder = $db->table('users')->select('fcm_id,email,username,platform');
                    $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                    foreach ($users_fcm as $ids) {
                        if ($ids['fcm_id'] != "") {
                            $fcm_ids['fcm_id'] = $ids['fcm_id'];
                            $fcm_ids['platform'] = $ids['platform'];
                            $email = $ids['email'];
                        }
                    }
                    if (!empty($fcm_ids)) {
                        $registrationIDs = $fcm_ids;
                        $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                        $fcmMsg = array(
                            'content_available' => true,
                            'title' => " New Booking Notification",
                            'body' => "We are pleased to inform you that you have received a new Booking. ",
                            'type' => 'order',
                            'order_id' => $order_id,
                            'type_id' => $to_send_id,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                        send_notification($fcmMsg, $registrationIDs_chunks);
                    }




                    update_details(['payment_status' => 1], ['id' => $order_id], 'orders');
                    if ($insert_id) {

                        //  log_message('error', 'Transaction successfully done ' . var_export($event, true));
                        $response['error'] = false;
                        $response['transaction_status'] = $event->type;
                        $response['message'] = "Transaction successfully done";
                        return $this->response->setJSON($response);
                    } else {
                        $response['error'] = true;
                        $response['message'] = "something went wrong";
                        return $this->response->setJSON($response);
                    }
                }
            } elseif ($event->type == 'charge.failed') {

                log_message('error', 'Stripe Webhook | charge.failed ');
                $data = [
                    'transaction_type' => 'transaction',
                    'user_id' => $user_id,
                    'partner_id' => $partner_id,
                    'order_id' => $order_id,
                    'type' => 'stripe',
                    'txn_id' => $txn_id,
                    'amount' => $amount,
                    'status' => 'failed',
                    'currency_code' => $currency,
                    'message' => 'Order is cancelled',
                ];



                $insert_id = add_transaction($data);
                update_details(['payment_status' => 2], ['id' => $order_id], 'orders');
                update_details(['status' => 'cancelled'], ['id' => $order_id], 'orders');
            } elseif ($event->type == 'charge.pending') {
                $data = [
                    'transaction_type' => 'transaction',
                    'user_id' => $user_id,
                    'partner_id' => $partner_id,
                    'order_id' => $order_id,
                    'type' => 'stripe',
                    'txn_id' => $txn_id,
                    'amount' => $amount,
                    'status' => 'pending',
                    'currency_code' => $currency,
                    'message' => 'Order placed successfully',
                ];
                $insert_id = add_transaction($data);
                update_details(['payment_status' => 0], ['id' => $order_id], 'orders');
                return false;
            } elseif ($event->type == 'charge.expired') {
                $data = [
                    'transaction_type' => 'transaction',
                    'user_id' => $user_id,
                    'partner_id' => $partner_id,
                    'order_id' => $order_id,
                    'type' => 'stripe',
                    'txn_id' => $txn_id,
                    'amount' => $amount,
                    'status' => 'failed',
                    'currency_code' => $currency,
                    'message' => 'Order placed successfully',
                ];
                $insert_id = add_transaction($data);
                return false;
            } elseif ($event->type == 'charge.refunded') {
                // $charge = $event->data->object;
                // $data = [
                //     'transaction_type' => 'transaction',
                //     'user_id' => $user_id,
                //     'partner_id' => $partner_id,
                //     'order_id' => $order_id,
                //     'type' => 'stripe',
                //     'txn_id' => $txn_id,
                //     'amount' => $amount,
                //     'status' => 'refund',
                //     'currency_code' => $currency,
                //     'message' => 'Order placed successfully',
                // ];
                // $insert_id = add_transaction($data);
                // return false;
            } else {
                $response['error'] = true;
                $response['transaction_status'] = $event->type;
                $response['message'] = "Transaction could not be detected.";
                echo json_encode($response);
                return false;
            }
        } else {
            log_message('error', 'Stripe Webhook | Invalid Server Signature  --> ');
            return false;
        }
    }
    public function paystack()
    {
        log_message('error', 'paystack Webhook Called');

        $system_settings = get_settings('system_settings', true);
        $paystack = new Paystack;
        $credentials = $paystack->get_credentials();

        $secret_key = $credentials['secret'];
        $request_body = file_get_contents('php://input');
        $event = json_decode($request_body, true);
        log_message('error', 'paystack Webhook --> ' . var_export($event, true));


        if (!empty($event['data'])) {

            // $txn_id = (isset($event['data']['reference'])) ? $event['data']['reference'] : "";
            $txn_id = (isset($event['data']['id'])) ? $event['data']['id'] : "";
            // log_message('error', 'paystack Webhook SERVER Variable --> ' . var_export($txn_id, true));


            if (isset($txn_id) && !empty($txn_id)) {
                $transaction = fetch_details('transactions', ['txn_id' => $txn_id]);
                if (!empty($transaction)) {
                    $order_id = $transaction[0]['order_id'];
                    $user_id = $transaction[0]['user_id'];
                } else {

                    if (!empty($event['data']['metadata']['transaction_id'])) {
                    } else {
                        if (isset($event['data']['metadata']['order_id']) && !empty($event['data']['metadata']['order_id'])) {

                            $order_id = 0;
                            $order_id = $event['data']['metadata']['order_id'];
                            $order_data = fetch_details('orders', ["id" => $order_id]);
                            $user_id = $order_data[0]['user_id'];
                            $partner_id = $order_data[0]['partner_id'];
                        }
                    }
                }
            }
            $amount = $event['data']['amount'];
            $currency = $event['data']['currency'];
        } else {
            $order_id = 0;
            $amount = 0;
            $currency = (isset($event['data']['currency'])) ? $event['data']['currency'] : "";
        }



        if ($event['event'] == 'charge.success') {


            if (!empty($event['data']['metadata']['transaction_id'])) {
                $transaction_details_for_subscription = fetch_details('transactions', ['id' => $event['data']['metadata']['transaction_id']]);
                $details_for_subscription = fetch_details('subscriptions', ['id' => $transaction_details_for_subscription[0]['subscription_id']]);

                log_message('error', 'FOR SUBSCRIPTION');
                update_details(['status' => 'success', 'txn_id' => $txn_id], ['id' => $event['data']['metadata']['transaction_id']], 'transactions');

                $purchaseDate = date('Y-m-d');
                $subscriptionDuration = $details_for_subscription[0]['duration'];
                $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
                if ($subscriptionDuration == "unlimited") {
                    $subscriptionDuration = 0;
                }
                update_details(['status' => 'active', 'is_payment' => '1', 'purchase_date' => $purchaseDate, 'expiry_date' => $expiryDate, 'updated_at' => date('Y-m-d h:i:s')], [
                    'subscription_id' => $transaction_details_for_subscription[0]['subscription_id'],
                    'partner_id' => $transaction_details_for_subscription[0]['user_id'],
                    'status !=' => 'active',
                    'transaction_id' => $event['data']['metadata']['transaction_id'],

                ], 'partner_subscriptions');

                // log_message('error', 'METAFDATA --> ' . var_export($event['data']['metadata']['transaction_id'], true));
            } else {


                if (!empty($order_id)) {     /* To do the wallet recharge if the order id is set in the pattern */

                    /* process the order and mark it as received */
                    $order = fetch_details('orders', ['id' => $order_id]);

                    log_message('error', 'Paystack Webhook | order --> ' . var_export($order, true));

                    /* No need to add because the transaction is already added just update the transaction status */
                    if (!empty($transaction)) {
                        $transaction_id = $transaction[0]['id'];
                        update_details(['status' => 'success'], ['id' => $transaction_id], 'transactions');
                    } else {
                        /* add transaction of the payment */
                        $amount = ($event['data']['amount'] / 100);
                        $data = [
                            'transaction_type' => 'transaction',
                            'user_id' => $user_id,
                            'partner_id' => $partner_id,
                            'order_id' => $order_id,
                            'type' => 'paystack',
                            'txn_id' => $txn_id,
                            'amount' => $amount,
                            'status' => 'success',
                            'currency_code' => $currency,
                            'message' => 'Order placed successfully',
                            'reference' => (isset($event['data']['reference'])) ? $event['data']['reference'] : "",

                        ];
                        $insert_id = add_transaction($data);
                        if ($insert_id) {
                            update_details(['payment_status' => 1], ['id' => $order_id], 'orders');
                            // send_web_notification('New Order', 'Please check new order ' . $order_id, $partner_id);
                            send_web_notification('New Booking Notification', 'We are pleased to inform you that you have received a new Booking.');

                            $settings = get_settings('general_settings', true);
                            $icon = $settings['logo'];
                            //customer email
                            $userdata = fetch_details('users', ['id' => $user_id], ['email', 'username']);
                            $data = array(
                                'name' => $userdata[0]['username'],
                                'title' => "Booking Received Confirmation",
                                'logo' => base_url("public/uploads/site/" . $icon),
                                'first_paragraph' => 'We are thrilled to inform you that your Booking has been successfully placed and confirmed. Thank you for choosing our services to fulfill your needs.',
                                'second_paragraph' => 'If you have any questions or concerns regarding your order, please do not hesitate to contact us. We will be more than happy to assist you.',
                                'third_paragraph' => 'Thank you again for choosing our services. We look forward to doing business with you again.',
                                'company_name' => $settings['company_title'],
                            );
                            if (!empty($userdata[0]['email'])) {
                                $user_email = email_sender($userdata[0]['email'], 'Booking Received Confirmation', view('backend/admin/pages/provider_email', $data));
                            }
                            //for provider
                            $partner_data = fetch_details('partner_details', ['partner_id' => $partner_id], ['company_name']);
                            $user_partner_data = fetch_details('users', ['id' => $partner_id], ['email', 'username']);
                            $data1 = array(
                                'name' => $partner_data[0]['company_name'],
                                'title' => "New Booking Notification",
                                'order_id' => $order_id,
                                'logo' => base_url("public/uploads/site/" . $icon),
                                'first_paragraph' => 'We are pleased to inform you that you have received a new Booking. ',
                                'second_paragraph' => 'Please note that the customer expects high-quality service from our providers. We kindly ask that you deliver the Booking by the expected delivery date and maintain excellent communication with the customer throughout the process.',
                                'third_paragraph' => 'Thank you for your cooperation and dedication to providing excellent service. We look forward to continuing our partnership with you.',
                                'company_name' => $settings['company_title'],
                            );
                            if (!empty($user_partner_data[0]['email'])) {
                                $user_parter_email = email_sender($user_partner_data[0]['email'], 'New Order Notification', view('backend/admin/pages/provider_email', $data1));
                            }
                            //for app notification
                            $db      = \Config\Database::connect();
                            $to_send_id = $partner_id;
                            $builder = $db->table('users')->select('fcm_id,email,username,platform');
                            $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                            foreach ($users_fcm as $ids) {
                                if ($ids['fcm_id'] != "") {
                                    $fcm_ids['fcm_id'] = $ids['fcm_id'];
                                    $fcm_ids['platform'] = $ids['platform'];
                                    $email = $ids['email'];
                                }
                            }
                            if (!empty($fcm_ids)) {
                                $registrationIDs = $fcm_ids;
                                $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                                $fcmMsg = array(
                                    'content_available' => true,
                                    'title' => " New Booking Notification",
                                    'body' => "We are pleased to inform you that you have received a new Booking. ",
                                    'type' => 'order',
                                    'order_id' => $order_id,
                                    'type_id' => $to_send_id,
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                );
                                send_notification($fcmMsg, $registrationIDs_chunks);
                            }



                            $response['error'] = false;
                            $response['transaction_status'] = "paystack";
                            $response['message'] = "Transaction successfully done";
                            return $this->response->setJSON($response);
                        } else {
                            $response['error'] = true;
                            $response['message'] = "something went wrong";
                            return $this->response->setJSON($response);
                        }
                    }


                    log_message('error', 'Paystack Webhook inner Success --> ' . var_export($event, true));

                    log_message('error', 'Paystack Webhook order Success --> ' . var_export($event, true));
                } else {
                    /* No order ID found / sending 304 error to payment gateway so it retries wenhook after sometime*/
                    log_message('error', 'Paystack Webhook | Order id not found --> ' . var_export($event, true));
                    return $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(304)
                        ->set_output(json_encode(array(
                            'message' => '304 Not Modified - order/transaction id not found',
                            'error' => true
                        )));
                }
            }
        } else if ($event['event'] == 'charge.dispute.create') {
            if (!empty($order_id) && is_numeric($order_id)) {
                $order = fetch_details('orders', ['id' => $order_id]);

                if ($order['order_data']['0']['active_status'] == 'received' || $order['order_data']['0']['active_status'] == 'processed') {
                    update_details(['status' => 'awaiting'], ['id' => $order_id], 'orders');
                }

                if (!empty($transaction)) {
                    $transaction_id = $transaction[0]['id'];
                    update_details(['status' => 'pending'], ['id' => $transaction_id], 'transactions');
                }

                log_message('error', 'Paystack Transaction is Pending --> ' . var_export($event, true));
            }
        } else if ($event['event'] == 'refund.processed') {

            // log_message('error', 'Paystack Webhook | refund.proces --> ' . var_export($event, true));

            //  log_message('error', 'Paystack Webhook | txn_id --> ' . var_export($txn_id, true));
            // //   $transaction = fetch_details('transactions', ['txn_id' => $txn_id]);
            // //     if (empty($transaction)) {
            // //         return false;
            // //     }
            //     process_refund($order_id, 'cancelled', $user_id);
        } else {

            if (!empty($order_id) && is_numeric($order_id)) {
                update_details(['status' => 'cancelled'], ['id' => $order_id], 'orders');
            }
            /* No need to add because the transaction is already added just update the transaction status */
            if (!empty($transaction)) {
                $transaction_id = $transaction[0]['id'];
                update_details(['status' => 'failed'], ['id' => $transaction_id], 'transactions');
                update_details(['payment_status' => 2], ['id' => $order_id], 'orders');
            }

            $response['error'] = true;
            $response['transaction_status'] = $event['event'];
            $response['message'] = "Transaction could not be detected.";
            // log_message('error', 'Paystack Webhook | Transaction could not be detected --> ' . var_export($event, true));
            echo json_encode($response);
            return false;
        }
    }


    public function razorpay()
    {

        //Debug in server first
        if ((strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') || !array_key_exists('HTTP_X_RAZORPAY_SIGNATURE', $_SERVER))
            exit();
        $razorpay = new Razorpay;
        $system_settings = get_settings('system_settings', true);
        $credentials = $razorpay->get_credentials();

        $request = file_get_contents('php://input');

        $request = json_decode($request, true);

        define('RAZORPAY_SECRET_KEY', $credentials['secret']);

        $http_razorpay_signature = isset($_SERVER['HTTP_X_RAZORPAY_SIGNATURE']) ? $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] : "";
        // log_message('error', 'Razorpay --> ' . var_export($request, true));

        log_message('error', 'Razorpay --> ' . var_export($request, true));

        $txn_id = (isset($request['payload']['payment']['entity']['id'])) ? $request['payload']['payment']['entity']['id'] : "";


        if (!empty($request['payload']['payment']['entity']['id'])) {
            if (!empty($txn_id)) {
                $transaction = fetch_details('transactions', ['txn_id' => $txn_id]);
            }
            $amount = $request['payload']['payment']['entity']['amount'];
            $amount = ($amount / 100);
            $currency = (isset($request['payload']['payment']['entity']['currency'])) ? $request['payload']['payment']['entity']['currency'] : "";
        } else {
            $amount = 0;
            $currency = (isset($request['payload']['payment']['entity']['currency'])) ? $request['payload']['payment']['entity']['currency'] : "";
        }

        if (!empty($transaction)) {
            $order_id = $transaction[0]['order_id'];
            $user_id = $transaction[0]['user_id'];
            $order_data = fetch_details('orders', ["id" => $order_id]);
            $user_id = $order_data[0]['user_id'];
            $partner_id = $order_data[0]['partner_id'];
        } else if (!empty($request['payload']['payment']['entity']['notes']['transaction_id'])) {

            $transaction_id_actual = isset($request['payload']['payment']['entity']['notes']['transaction_id']) ? $request['payload']['payment']['entity']['notes']['transaction_id'] : "abcd";

            //  log_message('error', 'transaction_id ID ********* ' . $request['payload']['payment']['entity']['notes']['transaction_id']);
        } else {
            $order_id = 0;
            $order_id = (isset($request['payload']['order']['entity']['notes']['order_id'])) ? $request['payload']['order']['entity']['notes']['order_id'] : $request['payload']['payment']['entity']['notes']['order_id'];
            $order_data = fetch_details('orders', ["id" => $order_id]);
            $user_id = $order_data[0]['user_id'];
            $partner_id = $order_data[0]['partner_id'];
        }
        if ($http_razorpay_signature) {
            if ($request['event'] == 'payment.authorized') {
                $currency = (isset($request['payload']['payment']['entity']['currency'])) ? $request['payload']['payment']['entity']['currency'] : "INR";
                $response = $razorpay->capture_payment($amount * 100, $txn_id, $currency);
                return;
            }
            if ($request['event'] == 'payment.captured' || $request['event'] == 'order.paid') {


                if (!empty($transaction_id_actual)) {
                    log_message('error', 'FOR SUBSCRIPTION');
                    log_message('error', ' ID ********* ' . $request['payload']['payment']['entity']['notes']['transaction_id']);
                    log_message('error', 'transaction_id  ********* ' . $txn_id);
                    $transaction_details_for_subscription = fetch_details('transactions', ['id' => $request['payload']['payment']['entity']['notes']['transaction_id']]);
                    $details_for_subscription = fetch_details('subscriptions', ['id' => $transaction_details_for_subscription[0]['subscription_id']]);
                    update_details(['status' => 'success', 'txn_id' => $txn_id], ['id' => $request['payload']['payment']['entity']['notes']['transaction_id']], 'transactions');
                    // update_details(['status' => 'active'], ['subscription_id' => $transaction_details_for_subscription[0]['subscription_id'],'partner_id'=>$transaction_details_for_subscription[0]['user_id'],'status'=>'pending'], 'partner_subscriptions');
                    $purchaseDate = date('Y-m-d');
                    $subscriptionDuration = $details_for_subscription[0]['duration'];
                    $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
                    if ($subscriptionDuration == "unlimited") {
                        $subscriptionDuration = 0;
                    }
                    update_details(['status' => 'active', 'is_payment' => '1', 'purchase_date' => $purchaseDate, 'expiry_date' => $expiryDate, 'updated_at' => date('Y-m-d h:i:s')], [
                        'subscription_id' => $transaction_details_for_subscription[0]['subscription_id'],
                        'partner_id' => $transaction_details_for_subscription[0]['user_id'],
                        'status !=' => 'active',
                        'transaction_id' => $request['payload']['payment']['entity']['notes']['transaction_id'],

                    ], 'partner_subscriptions');
                }
                if ($request['event'] == 'order.paid') {
                    $order_id = $request['payload']['order']['entity']['receipt'];
                    $order_data = fetch_details('orders', ["id" => $order_id]);
                    $user_id = $order_data[0]['user_id'];
                    $partner_id = $order_data[0]['partner_id'];
                }



                if (!empty($order_id)) {


                    /* No need to add because the transaction is already added just update the transaction status */
                    if (!empty($transaction)) {
                        $transaction_id = $transaction[0]['id'];
                        update_details(['status' => 'success'], ['id' => $transaction_id], 'transactions');
                    } else {
                        /* add transaction of the payment */
                        $currency = (isset($request['payload']['payment']['entity']['currency'])) ? $request['payload']['payment']['entity']['currency'] : "";
                        $data = [
                            'transaction_type' => 'transaction',
                            'user_id' => $user_id,
                            'partner_id' => $partner_id,
                            'order_id' => $order_id,
                            'type' => 'razorpay',
                            'txn_id' => $txn_id,
                            'amount' => $amount,
                            'status' => 'success',
                            'currency_code' => $currency,
                            'message' => 'Order placed successfully',
                        ];
                        $insert_id = add_transaction($data);
                        if ($insert_id) {
                            update_details(['payment_status' => 1], ['id' => $order_id], 'orders');

                            send_web_notification('New Booking', 'Please check new Booking ' . $order_id, $partner_id);
                            $settings = get_settings('general_settings', true);
                            $icon = $settings['logo'];
                            //customer email
                            $userdata = fetch_details('users', ['id' => $user_id], ['email', 'username']);
                            $data = array(
                                'name' => $userdata[0]['username'],
                                'title' => "Booking Received Confirmation",
                                'logo' => base_url("public/uploads/site/" . $icon),
                                'first_paragraph' => 'We are thrilled to inform you that your Booking has been successfully placed and confirmed. Thank you for choosing our services to fulfill your needs.',
                                'second_paragraph' => 'If you have any questions or concerns regarding your Booking, please do not hesitate to contact us. We will be more than happy to assist you.',
                                'third_paragraph' => 'Thank you again for choosing our services. We look forward to doing business with you again.',
                                'company_name' => $settings['company_title'],
                            );


                            if (!empty($userdata[0]['email'])) {
                                $user_email = email_sender($userdata[0]['email'], 'Booking Received Confirmation', view('backend/admin/pages/provider_email', $data));
                            }
                            //for provider
                            $partner_data = fetch_details('partner_details', ['partner_id' => $partner_id], ['company_name']);
                            $user_partner_data = fetch_details('users', ['id' => $partner_id], ['email', 'username']);
                            $data1 = array(
                                'name' => $partner_data[0]['company_name'],
                                'title' => "New Order Notification",
                                'order_id' => $order_id,
                                'logo' => base_url("public/uploads/site/" . $icon),
                                'first_paragraph' => 'We are pleased to inform you that you have received a new Booking. ',
                                'second_paragraph' => 'Please note that the customer expects high-quality service from our providers. We kindly ask that you deliver the Booking by the expected delivery date and maintain excellent communication with the customer throughout the process.',
                                'third_paragraph' => 'Thank you for your cooperation and dedication to providing excellent service. We look forward to continuing our partnership with you.',
                                'company_name' => $settings['company_title'],
                            );
                            if (!empty($user_partner_data[0]['email'])) {
                                $user_parter_email = email_sender($user_partner_data[0]['email'], 'New Booking Notification', view('backend/admin/pages/provider_email', $data1));
                            }
                            //for app notification
                            $db      = \Config\Database::connect();
                            $to_send_id = $partner_id;
                            $builder = $db->table('users')->select('fcm_id,email,username,platform');
                            $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                            foreach ($users_fcm as $ids) {
                                if ($ids['fcm_id'] != "") {
                                    $fcm_ids['fcm_id'] = $ids['fcm_id'];
                                    $fcm_ids['platform'] = $ids['platform'];
                                    $email = $ids['email'];
                                }
                            }
                            if (!empty($fcm_ids)) {
                                $registrationIDs = $fcm_ids;
                                $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                                $fcmMsg = array(
                                    'content_available' => true,
                                    'title' => " New Order Notification",
                                    'body' => "We are pleased to inform you that you have received a new order. ",
                                    'type' => 'order',
                                    'order_id' => $order_id,
                                    'type_id' => $to_send_id,
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                );
                                send_notification($fcmMsg, $registrationIDs_chunks);
                            }
                        }
                    }
                    // update_details(['active' => 'confirmed'], ['id' => $order_id], 'orders');

                } else {
                    log_message('error', 'Razorpay Order id not found --> ' . var_export($request, true));
                    /* No order ID found */
                }
            } elseif ($request['event'] == 'payment.failed') {
                update_details(['payment_status' => 2], ['id' => $order_id], 'orders');
                update_details(['status' => 'cancelled'], ['id' => $order_id], 'orders');




                if (!empty($transaction)) {
                    $transaction_id = $transaction[0]['id'];
                    update_details(['status' => 'failed'], ['id' => $transaction_id], 'transactions');
                } else {
                    /* add transaction of the payment */
                    $currency = (isset($request['payload']['payment']['entity']['currency'])) ? $request['payload']['payment']['entity']['currency'] : "";
                    $data = [
                        'transaction_type' => 'transaction',
                        'user_id' => $user_id,
                        'partner_id' => $partner_id,
                        'order_id' => $order_id,
                        'type' => 'razorpay',
                        'txn_id' => $txn_id,
                        'amount' => $amount,
                        'status' => 'failed',
                        'currency_code' => $currency,
                        'message' => 'Order is cancelled',
                    ];
                    $insert_id = add_transaction($data);
                }

                log_message('error', 'Razorpay Webhook | Transaction is failed --> ' . var_export($request['event'], true));
            } elseif ($request['event'] == 'payment.authorized') {
                if (!empty($order_id)) {
                    update_details(['active_status' => 'awaiting'], ['id' => $order_id], 'orders');
                    update_details(['active_status' => 'awaiting'], ['order_id' => $order_id], 'order_items');
                }
            } elseif ($request['event'] == "refund.processed") {
                //Refund Successfully
                $transaction = fetch_details('transactions', ['txn_id' => $request['payload']['refund']['entity']['payment_id']]);
                if (empty($transaction)) {
                    return false;
                }
                process_refund($order_id, 'cancelled', $user_id);
                $response['error'] = false;
                $response['transaction_status'] = $request['event'];
                $response['message'] = "Refund successfully done. ";
                log_message('error', 'Razorpay Webhook | Payment refund done --> ' . var_export($request['event'], true));
                echo json_encode($response);
                return false;
            } elseif ($request['event'] == "refund.failed") {
                $response['error'] = true;
                $response['transaction_status'] = $request['event'];
                $response['message'] = "Refund is failed. ";
                log_message('error', 'Razorpay Webhook | Payment refund failed --> ' . var_export($request['event'], true));
                echo json_encode($response);
                return false;
            } else {
                $response['error'] = true;
                $response['transaction_status'] = $request['event'];
                $response['message'] = "Transaction could not be detected.";
                log_message('error', 'Razorpay Webhook | Transaction could not be detected --> ' . var_export($request['event'], true));
                echo json_encode($response);
                return false;
            }
        } else {
            log_message('error', 'razorpay Webhook | Invalid Server Signature  --> ' . var_export($request['event'], true));
            return false;
        }
    }

    public function edie($error_msg)
    {
        global $debug_email;
        $report =  "ERROR : " . $error_msg . "\n\n";
        $report .= "POST DATA\n\n";
        foreach ($_POST as $key => $value) {
            $report .= "|$key| = |$value| \n";
        }
        log_message('error', $report);
        die($error_msg);
    }

    public function paypal()
    {

        $req = 'cmd=_notify-validate';
        $request_body = file_get_contents('php://input');

        parse_str($request_body, $event);

        log_message('error', 'paypal------' . var_export($event, true));

        $txn_id = (isset($event['txn_id'])) ? $event['txn_id'] : "";

        if (!empty($request_body)) {

            $ipnCheck = $this->paypal_lib->validate_ipn($event);

            if ($ipnCheck) {

                if (!empty($event['txn_id'])) {
                    if (!empty($txn_id)) {
                        $transaction = fetch_details('transactions', ['txn_id' => $txn_id]);
                    }
                    $amount = $event['payment_gross'];
                    $amount = ($amount);
                    $currency = (isset($event['mc_currency'])) ? $event['mc_currency'] : "";
                } else {
                    $amount = 0;
                    $currency = (isset($event['mc_currency'])) ? $event['mc_currency'] : "";
                }


                // $subsciption_data = explode('|', $event['custom']); // Split the invoice string
                // $transaction_id = $subsciption_data[0] ?? null;

                $custom_data = explode('|', $event['custom']); // Split the invoice string
                $is_subscripition = $custom_data[2] ?? null;
                //  log_message('error', var_export($is_subscripition, true));
                //  log_message('error', '----------------------------------------------------------*****-------');
                if (!empty($transaction)) {

                    $order_id = $transaction[0]['order_id'];
                    $order_data = fetch_details('orders', ["id" => $order_id]);
                    $user_id = $order_data[0]['user_id'];
                    $partner_id = $order_data[0]['partner_id'];
                } else {
                    $order_id = 0;
                    $order_id = (isset($event['item_number'])) ? $event['item_number'] : $event['item_number'];
                    $order_data = fetch_details('orders', ["id" => $order_id]);
                    if (!empty($order_data)) {

                        $user_id = $order_data[0]['user_id'];
                        $partner_id = $order_data[0]['partner_id'];
                    }
                }
                // log_message('error', var_export($transaction, true));



                if ($event['payment_status'] == "Completed") {

                    if ($is_subscripition == "subscription") {
                        if (isset($event['custom']) && !empty($event['custom'])) {
                            $subsciption_data = explode('|', $event['custom']); // Split the invoice string
                            $transaction_id = $subsciption_data[0] ?? null;


                            if (!empty($transaction_id) && $transaction_id != null) {
                                $transaction_details_for_subscription = fetch_details('transactions', ['id' => $transaction_id]);

                                if (!empty($transaction_details_for_subscription)) {
                                    $details_for_subscription = fetch_details('subscriptions', ['id' => $transaction_details_for_subscription[0]['subscription_id']]);

                                    log_message('error', 'FOR SUBSCRIPTION');
                                    update_details(['status' => 'success', 'txn_id' => $txn_id], ['id' => $transaction_id], 'transactions');

                                    $purchaseDate = date('Y-m-d');
                                    $subscriptionDuration = $details_for_subscription[0]['duration'];
                                    $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
                                    if ($subscriptionDuration == "unlimited") {
                                        $subscriptionDuration = 0;
                                    }
                                    update_details(['status' => 'active', 'is_payment' => '1', 'purchase_date' => $purchaseDate, 'expiry_date' => $expiryDate, 'updated_at' => date('Y-m-d h:i:s')], [
                                        'subscription_id' => $transaction_details_for_subscription[0]['subscription_id'],
                                        'partner_id' => $transaction_details_for_subscription[0]['user_id'],
                                        'status !=' => 'active',
                                        'transaction_id' => $transaction_id,

                                    ], 'partner_subscriptions');
                                }


                                // log_message('error', 'METAFDATA --> ' . var_export($event['data']['metadata']['transaction_id'], true));
                            }
                        }
                    } else {
                        if (!empty($order_id)) {



                            /* No need to add because the transaction is already added just update the transaction status */
                            if (!empty($transaction)) {
                                $transaction_id = $transaction[0]['id'];
                                update_details(['status' => 'success'], ['id' => $transaction_id], 'transactions');
                            } else {

                                log_message('error', 'add transaction of the payment');
                                /* add transaction of the payment */
                                $currency = (isset($event['mc_currency'])) ? $event['mc_currency'] : "";
                                $data = [
                                    'transaction_type' => 'transaction',
                                    'user_id' => $user_id,
                                    'partner_id' => $partner_id,
                                    'order_id' => $order_id,
                                    'type' => 'paypal',
                                    'txn_id' => $txn_id,
                                    'amount' => $amount,
                                    'status' => 'success',
                                    'currency_code' => $currency,
                                    'message' => 'Order placed successfully',
                                ];
                                $insert_id = add_transaction($data);
                            }
                            if ($insert_id) {
                                update_details(['payment_status' => 1], ['id' => $order_id], 'orders');


                                // send_web_notification('New Order', 'Please check new order ' . $order_id, $partner_id);
                                send_web_notification('New Booking Notification', 'We are pleased to inform you that you have received a new Booking.');

                                $settings = get_settings('general_settings', true);
                                $icon = $settings['logo'];
                                //customer email
                                $userdata = fetch_details('users', ['id' => $user_id], ['email', 'username']);
                                $data = array(
                                    'name' => $userdata[0]['username'],
                                    'title' => "Order Received Confirmation",
                                    'logo' => base_url("public/uploads/site/" . $icon),
                                    'first_paragraph' => 'We are thrilled to inform you that your Booking has been successfully placed and confirmed. Thank you for choosing our services to fulfill your needs.',
                                    'second_paragraph' => 'If you have any questions or concerns regarding your Booking, please do not hesitate to contact us. We will be more than happy to assist you.',
                                    'third_paragraph' => 'Thank you again for choosing our services. We look forward to doing business with you again.',
                                    'company_name' => $settings['company_title'],
                                );


                                if (!empty($userdata[0]['email'])) {
                                    $user_email = email_sender($userdata[0]['email'], 'Booking Received Confirmation', view('backend/admin/pages/provider_email', $data));
                                }
                                //for provider
                                $partner_data = fetch_details('partner_details', ['partner_id' => $partner_id], ['company_name']);
                                $user_partner_data = fetch_details('users', ['id' => $partner_id], ['email', 'username']);
                                $data1 = array(
                                    'name' => $partner_data[0]['company_name'],
                                    'title' => "New Order Notification",
                                    'order_id' => $order_id,
                                    'logo' => base_url("public/uploads/site/" . $icon),
                                    'first_paragraph' => 'We are pleased to inform you that you have received a new Booking. ',
                                    'second_paragraph' => 'Please note that the customer expects high-quality service from our providers. We kindly ask that you deliver the Booking by the expected delivery date and maintain excellent communication with the customer throughout the process.',
                                    'third_paragraph' => 'Thank you for your cooperation and dedication to providing excellent service. We look forward to continuing our partnership with you.',
                                    'company_name' => $settings['company_title'],
                                );
                                if (!empty($user_partner_data[0]['email'])) {
                                    $user_parter_email = email_sender($user_partner_data[0]['email'], 'New Order Notification', view('backend/admin/pages/provider_email', $data1));
                                }
                                //for app notification
                                $db      = \Config\Database::connect();
                                $to_send_id = $partner_id;
                                $builder = $db->table('users')->select('fcm_id,email,username,platform');
                                $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                                foreach ($users_fcm as $ids) {
                                    if ($ids['fcm_id'] != "") {
                                        $fcm_ids['fcm_id'] = $ids['fcm_id'];
                                        $fcm_ids['platform'] = $ids['platform'];
                                        $email = $ids['email'];
                                    }
                                }
                                if (!empty($fcm_ids)) {
                                    $registrationIDs = $fcm_ids;
                                    $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                                    $fcmMsg = array(
                                        'content_available' => true,
                                        'title' => " New Booking Notification",
                                        'body' => "We are pleased to inform you that you have received a new Booking. ",
                                        'type' => 'order',
                                        'order_id' => $order_id,
                                        'type_id' => $to_send_id,
                                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                    );
                                    send_notification($fcmMsg, $registrationIDs_chunks);
                                }
                                $response['error'] = false;
                                $response['transaction_status'] = $event['payment_status'];
                                $response['message'] = "Transaction successfully done";
                                log_message('error', 'Transaction successfully done');
                            } else {
                                $response['error'] = true;
                                $response['message'] = "something went wrong";
                                log_message('error', 'something went wrong');
                            }

                            // update_details(['status' => 'confirmed'], ['id' => $order_id], 'orders');

                            $response['error'] = false;
                            $response['transaction_status'] = $event['payment_status'];
                            $response['message'] = "Transaction successfully done";
                            echo json_encode($response);
                            log_message('error', 'Transaction successfully done ');
                        }
                    }


                    // if (!empty($order_id)) {



                    //     /* No need to add because the transaction is already added just update the transaction status */
                    //     if (!empty($transaction)) {
                    //         $transaction_id = $transaction[0]['id'];
                    //         update_details(['status' => 'success'], ['id' => $transaction_id], 'transactions');
                    //     } else {

                    //         log_message('error', 'add transaction of the payment');
                    //         /* add transaction of the payment */
                    //         $currency = (isset($event['mc_currency'])) ? $event['mc_currency'] : "";
                    //         $data = [
                    //             'transaction_type' => 'transaction',
                    //             'user_id' => $user_id,
                    //             'partner_id' => $partner_id,
                    //             'order_id' => $order_id,
                    //             'type' => 'paypal',
                    //             'txn_id' => $txn_id,
                    //             'amount' => $amount,
                    //             'status' => 'success',
                    //             'currency_code' => $currency,
                    //             'message' => 'Order placed successfully',
                    //         ];
                    //         $insert_id = add_transaction($data);
                    //     }
                    //     if ($insert_id) {
                    //         update_details(['payment_status' => 1], ['id' => $order_id], 'orders');


                    //         // send_web_notification('New Order', 'Please check new order ' . $order_id, $partner_id);
                    //         send_web_notification('New Order Notification', 'We are pleased to inform you that you have received a new order.');

                    //         $settings = get_settings('general_settings', true);
                    //         $icon = $settings['logo'];
                    //         //customer email
                    //         $userdata = fetch_details('users', ['id' => $user_id], ['email', 'username']);
                    //         $data = array(
                    //             'name' => $userdata[0]['username'],
                    //             'title' => "Order Received Confirmation",
                    //             'logo' => base_url("public/uploads/site/" . $icon),
                    //             'first_paragraph' => 'We are thrilled to inform you that your order has been successfully placed and confirmed. Thank you for choosing our services to fulfill your needs.',
                    //             'second_paragraph' => 'If you have any questions or concerns regarding your order, please do not hesitate to contact us. We will be more than happy to assist you.',
                    //             'third_paragraph' => 'Thank you again for choosing our services. We look forward to doing business with you again.',
                    //             'company_name' => $settings['company_title'],
                    //         );


                    //         if (!empty($userdata[0]['email'])) {
                    //             $user_email = email_sender($userdata[0]['email'], 'Order Received Confirmation', view('backend/admin/pages/provider_email', $data));
                    //         }
                    //         //for provider
                    //         $partner_data = fetch_details('partner_details', ['partner_id' => $partner_id], ['company_name']);
                    //         $user_partner_data = fetch_details('users', ['id' => $partner_id], ['email', 'username']);
                    //         $data1 = array(
                    //             'name' => $partner_data[0]['company_name'],
                    //             'title' => "New Order Notification",
                    //             'order_id' => $order_id,
                    //             'logo' => base_url("public/uploads/site/" . $icon),
                    //             'first_paragraph' => 'We are pleased to inform you that you have received a new order. ',
                    //             'second_paragraph' => 'Please note that the customer expects high-quality service from our providers. We kindly ask that you deliver the order by the expected delivery date and maintain excellent communication with the customer throughout the process.',
                    //             'third_paragraph' => 'Thank you for your cooperation and dedication to providing excellent service. We look forward to continuing our partnership with you.',
                    //             'company_name' => $settings['company_title'],
                    //         );
                    //         if (!empty($user_partner_data[0]['email'])) {
                    //             $user_parter_email = email_sender($user_partner_data[0]['email'], 'New Order Notification', view('backend/admin/pages/provider_email', $data1));
                    //         }
                    //         //for app notification
                    //         $db      = \Config\Database::connect();
                    //         $to_send_id = $partner_id;
                    //         $builder = $db->table('users')->select('fcm_id,email,username,platform');
                    //         $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                    //         foreach ($users_fcm as $ids) {
                    //             if ($ids['fcm_id'] != "") {
                    //                 $fcm_ids['fcm_id'] = $ids['fcm_id'];
                    //                 $fcm_ids['platform'] = $ids['platform'];
                    //                 $email = $ids['email'];
                    //             }
                    //         }
                    //         if (!empty($fcm_ids)) {
                    //             $registrationIDs = $fcm_ids;
                    //             $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                    //             $fcmMsg = array(
                    //                 'content_available' => true,
                    //                 'title' => " New Order Notification",
                    //                 'body' => "We are pleased to inform you that you have received a new order. ",
                    //                 'type' => 'order',
                    //                 'order_id' => $order_id,
                    //                 'type_id' => $to_send_id,
                    //                 'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    //             );
                    //             send_notification($fcmMsg, $registrationIDs_chunks);
                    //         }
                    //         $response['error'] = false;
                    //         $response['transaction_status'] = $event['payment_status'];
                    //         $response['message'] = "Transaction successfully done";
                    //         log_message('error', 'Transaction successfully done');
                    //     } else {
                    //         $response['error'] = true;
                    //         $response['message'] = "something went wrong";
                    //         log_message('error', 'something went wrong');
                    //     }

                    //     // update_details(['status' => 'confirmed'], ['id' => $order_id], 'orders');

                    //     $response['error'] = false;
                    //     $response['transaction_status'] = $event['payment_status'];
                    //     $response['message'] = "Transaction successfully done";
                    //     echo json_encode($response);
                    //     log_message('error', 'Transaction successfully done ');
                    // }else{
                    //     if (isset($event['custom']) && !empty($event['custom'])) {
                    //         $subsciption_data = explode('|', $event['custom']); // Split the invoice string
                    //         $transaction_id = $subsciption_data[0] ?? null;


                    //         if (!empty($transaction_id) && $transaction_id != null) {
                    //             $transaction_details_for_subscription = fetch_details('transactions', ['id' => $transaction_id]);

                    //             if (!empty($transaction_details_for_subscription)) {
                    //                 $details_for_subscription = fetch_details('subscriptions', ['id' => $transaction_details_for_subscription[0]['subscription_id']]);

                    //                 log_message('error', 'FOR SUBSCRIPTION');
                    //                 update_details(['status' => 'success', 'txn_id' => $txn_id], ['id' => $transaction_id], 'transactions');

                    //                 $purchaseDate = date('Y-m-d');
                    //                 $subscriptionDuration = $details_for_subscription[0]['duration'];
                    //                 $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
                    //                 if ($subscriptionDuration == "unlimited") {
                    //                     $subscriptionDuration = 0;
                    //                 }
                    //                 update_details(['status' => 'active', 'is_payment' => '1', 'purchase_date' => $purchaseDate, 'expiry_date' => $expiryDate, 'updated_at' => date('Y-m-d h:i:s')], [
                    //                     'subscription_id' => $transaction_details_for_subscription[0]['subscription_id'],
                    //                     'partner_id' => $transaction_details_for_subscription[0]['user_id'],
                    //                     'status !=' => 'active',
                    //                     'transaction_id' => $transaction_id,

                    //                 ], 'partner_subscriptions');
                    //             }


                    //             // log_message('error', 'METAFDATA --> ' . var_export($event['data']['metadata']['transaction_id'], true));
                    //         }
                    //     } 
                    // }

                } else {
                    log_message('error', 'Something went wrong');
                }


                log_message('error', 'SUCCESS');
            } else {
                log_message('error', 'IPN failed');
            }
        }
    }
}
