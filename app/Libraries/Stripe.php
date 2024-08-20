<?php

namespace App\Libraries;

use App\Controllers\BaseController;

/* 
    Strip Payments Library v1.0 for codeigniter 
    by Jaydeep Goswami
*/

/* 
    1. get_credentials()
    2. create_customer($customer_data)
    3. construct_event($request_body, $sigHeader, $secret,$tolerance = DEFAULT_TOLERANCE)
    4. create_payment_intent($c_data)
    5. curl($url, $method = 'GET', $data = [])
*/

const DEFAULT_TOLERANCE = 300;
class Stripe
{
    private $secret_key = "";
    private $publishable_key = "";
    private $webhook_secret_key = "";
    private $currency_code = "";
    private $url = "";

    function __construct()
    {
        helper('url');
        helper('form');
        helper('function');

        $settings = get_settings('payment_gateways_settings', true);


        $this->secret_key = isset($settings['stripe_secret_key']) ? $settings['stripe_secret_key'] : "sk_test_51LERZeSCiHzi4IW1hODcT6ngl88bSZzN4SHqH58CFKJ7eEQKSzniJTXgVNXFQPXuKfu9pAOYVMOe6UeE2q7hY5J400qllsvrye";
        $this->publishable_key = isset($settings['stripe_publishable_key']) ? $settings['stripe_publishable_key'] : "pk_test_51LERZeSCiHzi4IW10a1ecq2n2IqfiHZyvbVM6b4R7ofokfYk6HSSmv4KIjvDsFs8CVezsw50RiSizEswPhSC4SZC00M9HLWZZl";
        $this->webhook_secret_key = isset($settings['stripe_webhook_secret_key']) ? $settings['stripe_webhook_secret_key'] : "whsec_fsFzJNGOI2jxtkRFHY27AMWV7Dtglzq4";
        $this->currency_code = strtolower($settings['stripe_currency']);
        $this->url = "https://api.stripe.com/";
    }
    public function get_credentials()
    {
        $data['secret_key'] = $this->secret_key;
        $data['publishable_key'] = $this->publishable_key;
        $data['webhook_key'] = $this->webhook_secret_key;
        $data['currency'] = $this->currency_code;
        $data['url'] = $this->url;
        return $data;
    }
// Set your secret key. Remember to switch to your live secret key in production.
// See your keys here: https://dashboard.stripe.com/apikeys


    public function create_customer($customer_data)
    {
        $create_customer['name'] = $customer_data['name'];

        $create_customer['address']['line1'] = $customer_data['line1'];
        $create_customer['address']['postal_code'] = $customer_data['postal_code'];
        $create_customer['address']['city'] = $customer_data['city'];
        $url = $this->url . 'v1/customers';
        $method = 'POST';
        $response = $this->curl($url, $method, $create_customer);
        $res = json_decode($response['body'], true);
        return $res;
    }
    // public function construct_event($request_body, $sigHeader, $secret, $tolerance = DEFAULT_TOLERANCE)
    // {
    //     $explode_header = explode(",", $sigHeader);
    //     for ($i = 0; $i < count($explode_header); $i++) {
    //         $data[] = explode("=", $explode_header[$i]);
    //     }
    //     if (empty($data[0][1]) || $data[0][1] == "" || empty($data[1][1]) || $data[1][1] == "") {
    //         $response['error'] = true;
    //         $response['message'] = "Unable to extract timestamp and signatures from header";
    //         return $response;
    //     }
    //     $timestamp = $data[0][1];
    //     $signs = $data[1][1];

    //     $signed_payload = "{$timestamp}.{$request_body}";
    //     $expectedSignature = hash_hmac('sha256', $signed_payload, $secret);
    //     if ($expectedSignature == $signs) {
    //         if (($tolerance > 0) && (\abs(\time() - $timestamp) > $tolerance)) {
    //             $response['error'] = true;
    //             $response['message'] = "Timestamp outside the tolerance zone";
    //             return $response;
    //         } else {
    //             return "Matched";
    //         }
    //     } else {
    //         $response['error'] = true;
    //         $response['message'] = "No signatures found matching the expected signature for payload";
    //         return $response;
    //     }
    // }
    public function construct_event($request_body, $sigHeader, $secret, $tolerance = DEFAULT_TOLERANCE)
    {
        $explode_header = explode(",", $sigHeader);
        for ($i = 0; $i < count($explode_header); $i++) {
            $data[] = explode("=", $explode_header[$i]);
        }
        if (empty($data[0][1]) || $data[0][1] == "" || empty($data[1][1]) || $data[1][1] == "") {
            $response['error'] = true;
            $response['message'] = "Unable to extract timestamp and signatures from header";
            return $response;
        }
        $timestamp = $data[0][1];
        $signs = $data[1][1];

        $signed_payload = "{$timestamp}.{$request_body}";
        $expectedSignature = hash_hmac('sha256', $signed_payload, $secret);
        if ($expectedSignature == $signs) {
            if (($tolerance > 0) && (\abs(\time() - $timestamp) > $tolerance)) {
                $response['error'] = true;
                $response['message'] = "Timestamp outside the tolerance zone";
                return $response;
            } else {
                return "Matched";
            }
        } else {
            $response['error'] = true;
            $response['message'] = "No signatures found matching the expected signature for payload";
            return $response;
        }
    }
    public function create_payment_intent($c_data)
    {
        $c_data['currency'] = $this->currency_code;
        $url = $this->url . 'v1/payment_intents';
        $method = 'POST';
        $response = $this->curl($url, $method, $c_data);
        $res = json_decode($response['body'], true);
        return $res;
    }

    public function refund($payment_intent, $amount)
    {
        $amount = ($amount*100);
        $data = array(
            'payment_intent' => $payment_intent,
            'amount' => $amount,
        );
        $url = $this->url . 'v1/refunds';
        $method = 'POST';
        $response = $this->curl($url, $method, $data);

        if ($response['http_code'] == '200') {
            $res = json_decode($response['body'], true);
            return $res;
        } else {
            return $response;
        }
    }
    public function curl($url, $method = 'GET', $data = [])
    {
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . base64_encode($this->secret_key . ':')
            )
        );
        if (strtolower($method) == 'post') {
            $curl_options[CURLOPT_POST] = 1;
            $curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
        } else {
            $curl_options[CURLOPT_CUSTOMREQUEST] = 'GET';
        }
        curl_setopt_array($ch, $curl_options);
        $result = array(
            'body' => curl_exec($ch),
            'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        );
        return $result;
    }
}