<?php

namespace App\Controllers\api;

require_once  'vendor/autoload.php';

use App\Controllers\BaseController;
use App\Libraries\Flutterwave;
use App\Libraries\JWT;
use App\Libraries\Paypal;
use App\Libraries\Paystack;
use App\Libraries\Razorpay;
use App\Libraries\Stripe;

use App\Models\Addresses_model;
use App\Models\Bookmarks_model;
use App\Models\Category_model;
use App\Models\City_model;
use App\Models\Faqs_model;
use App\Models\Notification_model;
use App\Models\Orders_model;
use App\Models\Partner_subscription_model;
use App\Models\Partners_model;
use App\Models\Promo_code_model;
use App\Models\Service_model;
use App\Models\Service_ratings_model;
use App\Models\Slider_model;
use App\Models\Transaction_model;
use Aws\ElastiCache\Exception\ElastiCacheException;
use DateTime;
use Exception;
use Razorpay\Api\Api;

class V1 extends BaseController
{
    protected $request;
    public $bank_transfer, $paytm;
    /*
    ---------------------------------
    API keys list
    ---------------------------------
    1.  manage_user
    2.  update_user
    3.  update_fcm
    4.  get_settings
    5.  get_sections
    6.  add_transaction
    6.  get_transactions
    7.  add_address
    8.  delete_address
    9.  get_address
    10. validate_promo_code
    11. get_promo_codes
    12. get_categories
    13. get_sub_categories
    14. get_sliders
    15. get_providers
    16. get_services
    17. get_cities
    18. is_city_deliverable
    19. manage_cart
    20. remove_from_cart
    21. get_cart
    22. place_order
    23. get_orders
    24. manage_notification
    25. get_notifications
    26. get_ticket_types
    27. add_ticket
    28. edit_ticket
    29. get_tickets
    30. send_message
    31. get_messages
    32. book_mark
    33. generate_paytm_checksum
    34. generate_paytm_txn_token
    35. validate_paytm_checksum
    36. update_order_status
    37. get_ratings
     */
    /**
     *   @var array $excluded_routes is an array of uri strings which we want to exclude from jwt verification.
     */
    protected $excluded_routes =
    [
        "/api/v1/index",
        "/api/v1",
        "/api/v1/get_services",
        "/api/v1/manage_user",
        "/api/v1/verify_user",
        "/api/v1/get_sections",
        "/api/v1/get_sliders",
        "/api/v1/get_categories",
        "/api/v1/get_sub_categories",
        "/api/v1/flutterwave",
        "/api/v1/get_providers",
        "/api/v1/get_home_screen_data",
        "/api/v1/get_settings",
        "/api/v1/get_faqs",
        "/api/v1/get_ratings",
        "/api/v1/provider_check_availability",
        "/api/v1/invoice-download",
        "/api/v1/get_paypal_link",
        "/api/v1/paypal_transaction_webview",
        "/api/v1/app_payment_status",
        "/api/v1/ipn",
        "/api/v1/get-time-slots",
        "/api/v1/get_promo_codes",
        "/api/v1/contact_us_api",
        "/api/v1/search",
        "/api/v1/getPlaceAddress",
        "/api/v1/search_services_providers",
        "/api/v1/capturePayment"



    ];
    private $user_details = [];
    private $allowed_settings = ["general_settings", "terms_conditions", "privacy_policy", "about_us", 'payment_gateways_settings'];
    private $user_data = ['id', 'username', 'phone', 'email', 'fcm_id', 'web_fcm_id', 'image', 'latitude', 'longitude', 'friends_code', 'referral_code', 'city', 'country_code'];
    // protected $paypal_lib;
    public function __construct()
    {
        helper('api');
        helper("function");
        $this->paypal_lib = new Paypal();
        $this->request = \Config\Services::request();

        $this->flutterwave = new Flutterwave();
        $this->paystack = new paystack();
        $this->razorpay = new Razorpay();
        $this->JWT = new JWT();
        $current_uri = uri_string();
        if (!in_array($current_uri, $this->excluded_routes)) {
            $token = verify_app_request();
            if ($token['error']) {
                header('Content-Type: application/json');
                http_response_code($token['status']);
                print_r(json_encode($token));
                die();
            }
            $this->user_details = $token['data'];
        } else {
            // check if the token has been sent even if it is excluded. then set the user details if found.
            $token = verify_app_request();
            if (!$token['error'] && isset($token['data']) && !empty($token['data'])) {
                $this->user_details = $token['data'];
            }
        }
    }
    public function index()
    {
        $response = \Config\Services::response();
        helper("filesystem");
        $response->setHeader('content-type', 'Text');
        return $response->setBody(file_get_contents(base_url('apidocs.txt')));
    }
    // 1. manage_user
    // 1. manage_user
    public function manage_user()
    {





        // try {
        $config = new \Config\IonAuth();
        $validation = \Config\Services::validation();
        $request = \Config\Services::request();
        $identity_column = $config->identity;
        if ($identity_column == 'phone') {
            $identity = $request->getPost('mobile');
            $validation->setRule('mobile', 'mobile', 'required|numeric');
        } elseif ($identity_column == 'email') {
            $identity = $request->getPost('email');
            $validation->setRule('email', 'Email', 'required|valid_email');
        } else {
            $validation->setRule('identity', 'Identity', 'required');
        }
        if ($request->getPost('fcm_id')) {
            $validation->setRule('fcm_id', 'FCM ID', 'permit_empty');
        }
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }


        $userCheck = fetch_details('users', [$identity_column => $identity]);

        if (!empty($userCheck)) {

            $user_group = fetch_details('users_groups', ['user_id' => $userCheck[0]['id'], 'group_id' => '2']);
        } else {
            $user_group = [];
        }

        if (!empty($userCheck) && !empty($user_group)) {



            if ((($userCheck[0]['country_code'] == null) || ($userCheck[0]['country_code'] == $this->request->getPost('country_code'))) && (($userCheck[0]['phone'] == $identity))) {
                // if (exists(['phone' => $identity], 'users')) {
                // Login Success
                $update_data = $data = $token_data = [];
                // $data = fetch_details('users', ["phone" => $identity], $this->user_data)[0];


                $db      = \Config\Database::connect();
                $builder = $db->table('users u');
                $builder->select('u.*,ug.group_id')
                    ->join('users_groups ug', 'ug.user_id = u.id')
                    ->where('ug.group_id', 2)
                    ->where(['phone' => $identity]);
                $data = $builder->get()->getResultArray()[0];
                // print_R($data);
                // die;
                if (($request->getPost('fcm_id')) && !empty($request->getPost('fcm_id'))) {
                    $update_data = ["fcm_id" => $request->getPost('fcm_id')];
                }
                array_push($this->user_data, "api_key");
                $token = generate_tokens($identity, 2);

                if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                    // $update_data['api_key'] = $token;
                    $token_data['user_id'] = $data['id'];
                    $token_data['token'] = $token;
                    if (isset($token_data) && !empty($token_data)) {
                        insert_details($token_data, 'users_tokens');
                    }
                } else {

                    if ($this->request->getPost('latitude') && !empty($this->request->getPost('latitude'))) {

                        if (!preg_match('/^-?(90|[1-8][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('latitude'))) {

                            $response['error'] = true;
                            $response['message'] = "Please enter valid latitude";

                            return $this->response->setJSON($response);
                        }



                        $data['latitude'] = $this->request->getPost('latitude');
                    }
                    if ($this->request->getPost('longitude') && !empty($this->request->getPost('longitude'))) {
                        if (!preg_match('/^-?(180|1[1-7][0-9][.][0-9]{1,20}|[1-9][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('longitude'))) {
                            $response['error'] = true;
                            $response['message'] = "Please enter valid Longitude";

                            return $this->response->setJSON($response);
                        }
                        $data['longitude'] = $this->request->getPost('longitude');
                    }


                    // $update_data['api_key'] = $token;
                    $token_data['user_id'] = $data['id'];
                    $token_data['token'] = $token;
                    if ($this->request->getPost('latitude') || !empty($this->request->getPost('latitude'))) {
                        $data['latitude'] = $update_data['latitude'] = $this->request->getPost('latitude');
                    }
                    if ($this->request->getPost('longitude') || !empty($this->request->getPost('longitude'))) {
                        $data['longitude'] = $update_data['longitude'] = $this->request->getPost('longitude');
                    }
                    if ($this->request->getPost('country_code') || !empty($this->request->getPost('country_code'))) {
                        $data['country_code'] = $update_data['country_code'] = $this->request->getPost('country_code');
                    }
                    if (isset($token_data) && !empty($token_data)) {
                        insert_details($token_data, 'users_tokens');
                    }
                    if (isset($update_data) && !empty($update_data)) {
                        update_details($update_data, ['phone' => $identity], "users", false);
                    }
                }
                $data['image'] = (isset($data['image']) && !empty($data['image'])) ? base_url('public/backend/assets/profiles/' . $data['image']) : "";
                //remove null values
                $data = remove_null_values($data);
                $response = [
                    'error' => false,
                    "token" => $token,
                    'message' => 'User Logged successfully',
                    'data' => $data,
                ];
                return $this->response->setJSON($response);
            }
        } else {

            //for no registered users
            if ($this->request->getPost('mobile') && empty($this->request->getPost('mobile'))) {
                return response('mobile required');
            }
            if ($this->request->getPost('latitude') && !empty($this->request->getPost('latitude'))) {

                if (!preg_match('/^-?(90|[1-8][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('latitude'))) {

                    $response['error'] = true;
                    $response['message'] = "Please enter valid latitude";

                    return $this->response->setJSON($response);
                }



                $data['latitude'] = $this->request->getPost('latitude');
            }
            if ($this->request->getPost('longitude') && !empty($this->request->getPost('longitude'))) {
                if (!preg_match('/^-?(180|1[1-7][0-9][.][0-9]{1,20}|[1-9][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('longitude'))) {
                    $response['error'] = true;
                    $response['message'] = "Please enter valid Longitude";

                    return $this->response->setJSON($response);
                }
                $data['longitude'] = $this->request->getPost('longitude');
            }


            // $user_is_exist = fetch_details('users', [$identity_column => $identity]);
            // $user_group=fetch_details('users_groups',['user_id'=>$user_is_exist[0]['id'],'group_id'=>'2']);
            if (!empty($_FILES['image']) && isset($_FILES['image'])) {
                $file = $this->request->getFile('image');
                $path = 'public/backend/assets/profiles/';
                $newName = $file->getRandomName();
                $file->move($path, $newName);
                $data['image'] = $path . $newName;
            }
            $data['phone'] = $identity;
            $data['active'] = 1;
            $data['username'] = $this->request->getPost('username');
            $data['email'] = $this->request->getPost('email');
            $data['fcm_id'] = $this->request->getPost('fcm_id');
            $data['friends_code'] = $this->request->getPost('friends_code');
            $data['referral_code'] = $this->request->getPost('referral_code');
            $data['city'] = $this->request->getPost('city');
            $data['country_code'] = $this->request->getPost('country_code');
            // $insert_user = insert_details($data, 'users');
            if ($insert_user = insert_details($data, 'users')) {
                if (!exists(["user_id" => $insert_user['id'], "group_id" => 2], 'users_groups')) {
                    $group_data['user_id'] = $insert_user['id'];
                    $group_data['group_id'] = 2;
                    insert_details($group_data, 'users_groups');
                }
                $data = fetch_details('users', ['id' => $insert_user['id']], $this->user_data)[0];
                $token = generate_tokens($identity, 2);
                $token_data['user_id'] = $data['id'];
                $token_data['token'] = $token;
                if (isset($token_data) && !empty($token_data)) {
                    insert_details($token_data, 'users_tokens');
                }
                $response = [
                    'error' => false,
                    "token" => $token,
                    'message' => 'User Login successfully',
                    'data' => remove_null_values($data),
                ];
                return $this->response->setJSON($response);
            }
            $response['error'] = true;
            $response['message'] = 'Incorrect password !';
            return $this->response->setJSON($response);
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    // 2. update_user
    public function update_user()
    {
        // try {
        /*
        username:test             {optional}
        email:test@gmail.com      {optional}
        mobile:9874565478         {optional}
        image:FILE                {optional}
        referral_code:MY_CODE     {optional}
        fcm_id:YOUR_FCM_ID        {optional}
        friends_code:45dsrwr      {optional}
        city_id:10                {optional}
        latitude:66.89            {optional}
        longitude:67.8            {optional}
         */
        helper(['form', 'url']);
        if (!isset($_POST)) {
            $response = [
                'error' => true,
                'message' => "Please use Post request",
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $validation = \Config\Services::validation();
        $config = new \Config\IonAuth();
        $tables = $config->tables;
        $validation->setRules(
            [
                'email' => 'permit_empty|valid_email',
                'phone' => 'permit_empty|numeric|is_unique[' . $tables['users'] . '.phone]',
                'username' => 'permit_empty',
                'referral_code' => 'permit_empty',
                'friends_code' => 'permit_empty',
                'city_id' => 'permit_empty',
                'latitude' => 'permit_empty',
                'longitude' => 'permit_empty',
            ],
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        //Data
        $arr = [];
        if ($this->request->getPost('username') && !empty($this->request->getPost('username'))) {
            $arr['username'] = $this->request->getPost('username');
        }
        if ($this->request->getPost('email') && !empty($this->request->getPost('email'))) {
            $arr['email'] = $this->request->getPost('email');
        }
        if ($this->request->getPost('mobile') && !empty($this->request->getPost('mobile'))) {
            $arr['phone'] = $this->request->getPost('mobile');
        }
        if ($this->request->getPost('referral_code') && !empty($this->request->getPost('referral_code'))) {
            $arr['referral_code'] = $this->request->getPost('referral_code');
        }
        if ($this->request->getPost('friends_code') && !empty($this->request->getPost('friends_code'))) {
            $arr['friends_code'] = $this->request->getPost('friends_code');
        }
        if ($this->request->getPost('city_id') && !empty($this->request->getPost('city_id'))) {
            // if (!exists(['id' => $this->request->getPost('city_id')], 'cities')) {
            //     return response('City not exist');
            // }
            $arr['city'] = $this->request->getPost('city_id');
        }
        if ($this->request->getPost('latitude') && !empty($this->request->getPost('latitude'))) {
            $arr['latitude'] = $this->request->getPost('latitude');
        }
        if ($this->request->getPost('longitude') && !empty($this->request->getPost('longitude'))) {
            $arr['longitude'] = $this->request->getPost('longitude');
        }
        $user_id = $this->user_details['id'];
        if (!exists(['id' => $user_id], 'users')) {
            $response = [
                'error' => true,
                'message' => 'Invalid User Id',
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        if ($this->request->getFile('image')) {
            $file = $this->request->getFile('image');
            if (!$file->isValid()) {
                $response = [
                    'error' => true,
                    'message' => 'Something went wrong please try after some time.',
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $type = $file->getMimeType();
            if ($type == 'image/jpeg' || $type == 'image/png' || $type == 'image/jpg') {
                $path = FCPATH . 'public/backend/assets/profiles/';
                $check_image = fetch_details('users', ['id' => $this->user_details['id']], 'image');
                if (!empty($check_image)) {
                    $image_name = $check_image[0]['image'];
                    $profile_image = (file_exists($path . $image_name)) ?
                        $path . $image_name : ((file_exists(FCPATH . $image_name)) ? $image_name : null);

                    if ((!empty($check_image[0]['image']) || $check_image[0]['image'] != '') && !empty($profile_image)) {
                        if (check_exists(base_url('public/backend/assets/profiles/' . $profile_image)) || check_exists(base_url('/public/uploads/users/partners/' . $profile_image)) || check_exists($profile_image)) {

                            unlink($profile_image);
                        }
                    }
                }
                $image = $file->getName();
                $newName = $file->getRandomName();
                $file->move($path, $newName);
                $arr['image'] = $newName;
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Please attach a valid image file.',
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
        }
        if (!empty($arr)) {
            $status = update_details($arr, ['id' => $user_id], 'users');
            if ($status) {
                $data = fetch_details('users', ['id' => $user_id], $this->user_data)[0];
                $data['image'] = base_url('public/backend/assets/profiles/' . $data['image']);
                $response = [
                    'error' => false,
                    'message' => 'User updated successfully.',
                    'data' => remove_null_values($data),
                ];
                return $this->response->setJSON($response);
            }
        } else {
            $response = [
                'error' => true,
                'message' => 'Please insert any one field to update.',
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    // 3. update_fcm
    public function update_fcm()
    {
        try {
            /*
        fcm_id:1564ad654asd5754a5sd
         */
            $validation = \Config\Services::validation();
            $request = \Config\Services::request();
            $validation->setRules(
                [
                
                    'platform' => 'required'
                ],


            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $fcm_id = $this->request->getPost('fcm_id');
            $platform = $this->request->getPost('platform');

            if (update_details(['fcm_id' => $fcm_id, 'platform' => $platform], ['id' => $this->user_details['id']], 'users')) {
                return response('fcm id updated succesfully', true, ['fcm_id' => $fcm_id]);
            } else {
                return response();
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function get_settings()
    {
        // try {
        //     /*
        //         variable:{variable Name}    {privacy_policy, general_settings} (optional)
        //     */
        $validation = \Config\Services::validation();
        $request = \Config\Services::request();
        $variable = (isset($_POST['variable']) && !empty($_POST['variable'])) ? $_POST['variable'] : 'all';
        $setting = array();
        $setting = fetch_details('settings', '', 'variable', '', '', '', 'ASC');
        if (isset($variable) && !empty($variable) && in_array(trim($variable), $this->allowed_settings)) {
            $setting_res[$variable] = get_settings($variable, true);
        } else {
            foreach ($setting as $type) {
                $notallowed_settings = ["languages", "email_settings", "country_codes", "api_key_settings", "test"];
                if (!in_array($type['variable'], $notallowed_settings)) {
                    $setting_res[$type['variable']] = get_settings($type['variable'], true);
                }
            }
        }
        $this->toDateTime = date('Y-m-d H:i');
        $general_settings = $setting_res['general_settings'];
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('settings');
        $system_time_zone = isset($setting_res['general_settings']['system_timezone']) ? $setting_res['general_settings']['system_timezone'] : "Asia/Kolkata";
        date_default_timezone_set($system_time_zone);
        $customer_app_maintenance_mode_schedule_date = isset($setting_res['general_settings']['customer_app_maintenance_schedule_date']) ? (explode("to", $setting_res['general_settings']['customer_app_maintenance_schedule_date'])) : null;
        if (!empty($customer_app_maintenance_mode_schedule_date)) {
            $customer_app_maintenance_mode_start_date = isset($customer_app_maintenance_mode_schedule_date[0]) ? $customer_app_maintenance_mode_schedule_date[0] : "";
            $customer_app_maintenance_mode_end_date = isset($customer_app_maintenance_mode_schedule_date[1]) ? $customer_app_maintenance_mode_schedule_date[1] : "";
        } else {
            $customer_app_maintenance_mode_start_date = null;
            $customer_app_maintenance_mode_end_date = null;
        }
        if (isset($setting_res['general_settings']['customer_app_maintenance_mode']) && $setting_res['general_settings']['customer_app_maintenance_mode'] == 1) {
            $today = strtotime(date('Y-m-d H:i'));
            $start_time = strtotime(date('Y-m-d H:i', strtotime($customer_app_maintenance_mode_start_date)));
            $expiry_time = strtotime(date('Y-m-d H:i', strtotime($customer_app_maintenance_mode_end_date)));
            if (($today >= $start_time) && ($today <= $expiry_time)) {
                $setting_res['general_settings']['customer_app_maintenance_mode'] = "1";
                // $general_settings['customer_app_maintenance_mode'] = "1";
                // $json_string = json_encode($general_settings);
                // $this->builder->where('variable', "general_settings");
                // $this->builder->update(['value' => $json_string]);
                // $this->db->transComplete();
            } else {
                $setting_res['general_settings']['customer_app_maintenance_mode'] = "0";
                // $general_settings['customer_app_maintenance_mode'] = "0";
                // $json_string = json_encode($general_settings);
                // $this->builder->where('variable', "general_settings");
                // $this->builder->update(['value' => $json_string]);
                // $this->db->transComplete();
            }
        } else {
            $setting_res['general_settings']['customer_app_maintenance_mode'] = "0";
        }
        // if (isset($setting_res['general_settings']['at_store']) && $setting_res['general_settings']['at_store'] == 1) {

        //     $setting_res['general_settings']['at_store'] = "1";
        // } else {
        //     $setting_res['general_settings']['at_store'] = "0";
        // }
        // if (isset($setting_res['general_settings']['at_doorstep']) && $setting_res['general_settings']['at_doorstep'] == 1) {

        //     $setting_res['general_settings']['at_doorstep'] = "1";
        // } else {
        //     $setting_res['general_settings']['at_doorstep'] = "0";
        // }


        $setting_res['general_settings']['favicon'] =  isset($setting_res['general_settings']['favicon']) ? base_url("public/uploads/site/" . ($setting_res['general_settings']['favicon'])) : "";
        $setting_res['general_settings']['logo'] =  isset($setting_res['general_settings']['logo']) ? base_url("public/uploads/site/" . ($setting_res['general_settings']['logo'])) : "";
        $setting_res['general_settings']['half_logo'] =  isset($setting_res['general_settings']['half_logo']) ? base_url("public/uploads/site/" . ($setting_res['general_settings']['half_logo'])) : "";
        $setting_res['general_settings']['partner_favicon'] =  isset($setting_res['general_settings']['partner_favicon']) ? base_url("public/uploads/site/" . ($setting_res['general_settings']['partner_favicon'])) : "";
        $setting_res['general_settings']['partner_logo'] =  isset($setting_res['general_settings']['partner_logo']) ? base_url("public/uploads/site/" . ($setting_res['general_settings']['partner_logo'])) : "";
        $setting_res['general_settings']['partner_half_logo'] =  isset($setting_res['general_settings']['partner_half_logo']) ? base_url("public/uploads/site/" . ($setting_res['general_settings']['partner_half_logo'])) : "";




        $provider_app_maintenance_mode_schedule_date = isset($setting_res['general_settings']['provider_app_maintenance_schedule_date']) ? (explode("to", $setting_res['general_settings']['provider_app_maintenance_schedule_date'])) : null;
        if (!empty($provider_app_maintenance_mode_schedule_date)) {
            $provider_app_maintenance_mode_start_date = isset($provider_app_maintenance_mode_schedule_date[0]) ? $provider_app_maintenance_mode_schedule_date[0] : "";
            $provider_app_maintenance_mode_end_date = isset($provider_app_maintenance_mode_schedule_date[1]) ? $provider_app_maintenance_mode_schedule_date[1] : "";
        } else {
            $provider_app_maintenance_mode_start_date = null;
            $provider_app_maintenance_mode_end_date = null;
        }
        if (isset($setting_res['general_settings']['provider_app_maintenance_mode']) && $setting_res['general_settings']['provider_app_maintenance_mode'] == 1) {
            $today = strtotime(date('Y-m-d H:i'));
            $start_time = strtotime(date('Y-m-d H:i', strtotime($provider_app_maintenance_mode_start_date)));
            $expiry_time = strtotime(date('Y-m-d H:i', strtotime($provider_app_maintenance_mode_end_date)));
            if (($today >= $start_time) && ($today <= $expiry_time)) {
                $setting_res['general_settings']['provider_app_maintenance_mode'] = "1";
                // $general_settings['provider_app_maintenance_mode'] = "1";
                // $json_string = json_encode($general_settings);
                // $this->builder->where('variable', "general_settings");
                // $this->builder->update(['value' => $json_string]);
                // $this->db->transComplete();
            } else {
                $setting_res['general_settings']['provider_app_maintenance_mode'] = "0";
                // $general_settings['provider_app_maintenance_mode'] = "0";
                // $json_string = json_encode($general_settings);
                // $this->builder->where('variable', "general_settings");
                // $this->builder->update(['value' => $json_string]);
                // $this->db->transComplete();
            }
        } else {
            $setting_res['general_settings']['provider_app_maintenance_mode'] = "0";
        }
        if (isset($setting_res['general_settings']['provider_location_in_provider_details']) && $setting_res['general_settings']['provider_location_in_provider_details'] == 1) {
            $setting_res['general_settings']['provider_location_in_provider_details'] = "1";
        } else {
            $setting_res['general_settings']['provider_location_in_provider_details'] = "0";
        }

        $setting_res['web_settings']['web_logo'] =  isset($setting_res['web_settings']['web_logo']) ? base_url("public/uploads/web_settings/" . ($setting_res['web_settings']['web_logo'])) : "";
        $setting_res['web_settings']['web_favicon'] =  isset($setting_res['web_settings']['web_favicon']) ? base_url("public/uploads/web_settings/" . ($setting_res['web_settings']['web_favicon'])) : "";
        $setting_res['web_settings']['web_half_logo'] =  isset($setting_res['web_settings']['web_half_logo']) ? base_url("public/uploads/web_settings/" . ($setting_res['web_settings']['web_half_logo'])) : "";



        if (!empty($setting_res['web_settings']['social_media'])) {

            foreach ($setting_res['web_settings']['social_media'] as &$row) {
                $row['file'] = isset($row['file']) ? base_url("public/uploads/web_settings/" . $row['file']) : "";
            }
        } else {
            $setting_res['web_settings']['social_media'] = [];
        }



        $setting_res['server_time'] = $this->toDateTime;
        $setting_res['web_settings']['demo_mode'] = (ALLOW_MODIFICATION == 1) ? "0" : "1";



        if (array_key_exists('refund_policy', $setting_res)) {


            unset($setting_res['refund_policy']);
        }
        if (isset($setting_res) && !empty($setting_res)) {
            $response = [
                'error' => false,
                'message' => "setting recieved Successfully",
                'data' => $setting_res,
            ];
        } else {
            $response = [
                'error' => true,
                'message' => "No data found in setting",
                'data' => $setting_res,
            ];
        }
        return $this->response->setJSON($response);
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }

    public function get_home_screen_data()
    {


        // try {
        /*
        latitude:12.1234578
        longitude:12.1234578
        id:15                   {optional}
        sort:id                 {optional}
        order:asc               {optional}
        search:test             {optional}
         */
        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'latitude' => 'required',
                'longitude' => 'required',
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
        $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
        $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
        $where = $additional_data = [];
        $multipleWhere = $partner_ids = '';
        $db = \Config\Database::connect();
        $builder = $db->table('sections');
        $sortable_fields = ['id' => 'id', 'title' => 'title', 'categories' => 'categories', 'style' => 'style', 'service_type' => 'service_type'];
        if (isset($search) and $search != '') {
            $multipleWhere = ['`id`' => $search, '`title`' => $search];
        }
        if ($this->request->getPost('id')) {
            $where['id'] = $this->request->getPost('id');
        }
        // count of section
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        $total = $builder->select(' COUNT(id) as `total` ');
        $offer_count = $builder->get()->getResultArray();
        $total = $offer_count[0]['total'];
        // get section data
        $builder->select();
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        $offer_recorded = $builder->where('status', 1)->orderBy('rank', $order)
            ->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($offer_recorded as $row) {
            $partners = $sub_category_ids = $partners_ids = [];

            //CATEGORY
            if ($row['section_type'] == 'categories') {
                if (!is_null($row['category_ids'])) {
                    $partners = $db->table('categories c');
                    $category_ids = explode(',', $row['category_ids']);
                    $ids = (!empty($sub_category_ids)) ? $sub_category_ids : $category_ids;
                    $partners = $partners->Select('c.*')
                        ->whereIn('c.id', $ids)
                        ->where('c.status', 1)
                        ->get()
                        ->getResultArray();
                    for ($i = 0; $i < count($partners); $i++) {
                        $partners[$i]['image'] = (!empty($partners[$i]['image'])) ? base_url('/public/uploads/categories/' . $partners[$i]['image']) : "";
                        $partners[$i]['discount'] = $partners[$i]['upto'] = "";
                        unset($partners[$i]['created_at']);
                        unset($partners[$i]['updated_at']);
                        unset($partners[$i]['deleted_at']);
                        unset($partners[$i]['slug']);
                        unset($partners[$i]['admin_commission']);
                        unset($partners[$i]['status']);
                        $parent_ids = array_values(array_unique(array_column($partners, "parent_id")));
                        $parent_ids = implode(", ", $parent_ids);
                    }
                }
                $type = 'sub_categories';
            }
            //PREVIOUS ORDER
            else if ($row['section_type'] == 'previous_order') {

                if (!empty($this->user_details['id'])) {
                    $order_limit = !empty($row['limit']) ? $row['limit'] : 10;

                    $orders = new Orders_model();
                    $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
                    $where['o.status'] = 'completed';
                    $where['o.user_id'] =  $this->user_details['id'];

                    $order_data = $orders->list(true, $search, $order_limit, $offset, $sort, "DESC", $where, '', '', '', '', '', false);


                    if (!empty($order_data)) {
                        $order_data_id = array_values(array_unique(array_column($order_data['data'], "id")));
                    } else {
                        $order_data_id = ""; // Assign a default value if $order_data is empty
                    }
                }

                $type = 'previous_order';
            }
            //  ONGOING ORDER
            else if ($row['section_type'] == 'ongoing_order') {

                if (!empty($this->user_details['id'])) {
                    $order_limit = !empty($row['limit']) ? $row['limit'] : 10;
                    $ongoing_orders = new Orders_model();
                    $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
                    $where1['o.status '] = ['started'];
                    $where1['o.user_id'] =  $this->user_details['id'];

                    $ongoing_order_data = $ongoing_orders->list(true, $search, $order_limit, $offset, $sort, "DESC", $where1, '', '', '', '', '', false);

                    if (!empty($ongoing_order_data)) {
                        $onging_order_data_id = array_values(array_unique(array_column($ongoing_order_data['data'], "id")));
                    } else {
                        $onging_order_data_id = ""; // Assign a default value if $order_data is empty
                    }
                }

                $type = 'ongoing_order';
            }
            //TOP RATED PARTNER

            else if ($row['section_type'] == 'top_rated_partner') {


                $is_latitude_set = "";
                $rated_provider_limit = !empty($row['limit']) ? $row['limit'] : 10;
                $settings = get_settings('general_settings', true);

                $additional_data = [
                    'latitude' => $this->request->getPost('latitude'),
                    'longitude' => $this->request->getPost('longitude'),
                    // 'city_id' => $token['data']['city_id'],
                    'max_serviceable_distance' => $settings['max_serviceable_distance'],
                ];
                if (isset($additional_data['latitude']) && !empty($additional_data['latitude'])) {
                    $latitude1 = $this->request->getPost('latitude');
                    $longitude1 = $this->request->getPost('longitude');
                    // $is_latitude_set1 = " st_distance_sphere(POINT(' $longitude1','$latitude1'), POINT(`p`.`longitude`, `p`.`latitude` ))/1000  as distance";
                    $is_latitude_set1 = "st_distance_sphere(POINT($longitude1, $latitude1), POINT(`longitude`, `latitude` ))/1000  as distance";
                }
                $rating_data = $db->table('partner_details pd')->select('p.id,p.username,p.company,pc.minimum_order_amount,p.image,pd.banner,pc.discount,pc.discount_type,pd.company_name,
                ps.status as subscription_status,' . $is_latitude_set1)
                    ->join('services s', 's.id=pd.partner_id', 'left')
                    ->join('services_ratings sr', 'sr.service_id = s.id', 'left')
                    ->join('users p', 'p.id=pd.partner_id')
                    ->join('partner_subscriptions ps', 'ps.partner_id=pd.partner_id')
                    ->join('users_groups ug', 'ug.user_id = p.id')
                    ->join('promo_codes pc', 'pc.partner_id=pd.id', 'left')
                    ->where('ps.status', 'active')
                    ->having('distance < ' . $additional_data['max_serviceable_distance'])
                    ->orderBy('pd.ratings', 'desc')
                    ->limit($rated_provider_limit)->get()->getResultArray();



                $rating_data = array_values($rating_data);



                $ids2 = [];
                foreach ($rating_data as $key => $row2) {
                    $ids2[] = $row2['id'];
                }


                //new added for order - start
                foreach ($ids2 as $key2 => $id) {
                    $partner_subscription = fetch_details('partner_subscriptions', ['partner_id' => $id, 'status' => 'active']);
                    if ($partner_subscription) {
                        $subscription_purchase_date = $partner_subscription[0]['updated_at'];
                        $partner_order_limit = fetch_details('orders', ['partner_id' => $id, 'parent_id' => null, 'created_at >' => $subscription_purchase_date]);
                        $partners_subscription = $db->table('partner_subscriptions ps');
                        $partners_subscription_data = $partners_subscription->select('ps.*')->where('ps.status', 'active')
                            ->get()
                            ->getResultArray();

                        $subscription_order_limit = $partners_subscription_data[0]['max_order_limit'];
                        if ($partners_subscription_data[0]['order_type'] == "limited") {
                            if (count($partner_order_limit) >= $subscription_order_limit) {
                                unset($rating_data[$key2]);
                            }
                        }
                    } else {
                        // Handle case where no active subscription is found for the partner
                        unset($rating_data[$key2]);
                    }
                }



                $rating_data = array_values($rating_data);


                if (!empty($rating_data)) {


                    $rate_parent_ids = array_values(array_unique(array_column($rating_data, "id")));


                    if (is_array($rate_parent_ids) && !empty($rate_parent_ids)) {

                        $partners = $db->table('services s');
                        $rating_data = $partners->Select('p.id,p.username,p.company,pc.minimum_order_amount,p.image,pd.banner,pc.discount,pc.discount_type,
                        count(sr.rating) as number_of_rating, 
                        SUM(sr.rating) as total_rating,
                        (SUM(sr.rating) / count(sr.rating)) as average_rating,
                         (SELECT COUNT(*) FROM orders o WHERE o.partner_id = p.id AND o.parent_id IS NULL) as number_of_orders,pd.company_name,' . $is_latitude_set)
                            ->join('services_ratings sr', 'sr.service_id = s.id', 'left')
                            ->join('users p', 'p.id=s.user_id')
                            ->join('partner_details pd', 'pd.partner_id=s.user_id')
                            ->join('promo_codes pc', 'pc.partner_id=p.id', 'left')
                            ->whereIn('s.user_id', $rate_parent_ids)
                            ->where('pd.is_approved', '1')
                            ->groupBy('p.id')
                            ->get()
                            ->getResultArray();

                        for ($i = 0; $i < count($rating_data); $i++) {
                            $rating_data[$i]['upto'] = $rating_data[$i]['minimum_order_amount'];
                            if (!empty($rating_data[$i]['image'])) {
                                $image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $rating_data[$i]['image'])) ? base_url('public/backend/assets/profiles/' . $rating_data[$i]['image']) : ((file_exists(FCPATH . $rating_data[$i]['image'])) ? base_url($rating_data[$i]['image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $rating_data[$i]['image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $rating_data[$i]['image'])));


                                $rating_data[$i]['image'] = $image;
                                $banner_image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $rating_data[$i]['banner'])) ? base_url('public/backend/assets/profiles/' . $rating_data[$i]['banner']) : ((file_exists(FCPATH . $rating_data[$i]['banner'])) ? base_url($rating_data[$i]['banner']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $rating_data[$i]['banner'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $rating_data[$i]['banner'])));
                                $rating_data[$i]['banner_image'] = $banner_image;
                                unset($rating_data[$i]['banner']);
                                if ($rating_data[$i]['discount_type'] == 'percentage') {
                                    unset($rating_data[$i]['discount_type']);
                                }
                            }
                            unset($rating_data[$i]['minimum_order_amount']);
                        }



                        for ($i = 0; $i < count($rating_data); $i++) {
                            $rate_parent_ids = array_values(array_unique(array_column($rating_data, "id")));
                            $rate_parent_ids = implode(", ", $rate_parent_ids);
                        }
                    } else {
                        $rate_parent_ids = "";
                    }




                    $type = 'top_rated_partner';
                }
            }

            //PARTNERS
            else {

                if (!is_null($row['partners_ids'])) {
                    $partners_ids = explode(',', $row['partners_ids']);
                }



                $settings = get_settings('general_settings', true);
                $Partners_model = new Partners_model();
                if (($this->request->getPost('latitude') && !empty($this->request->getPost('latitude')) && ($this->request->getPost('longitude') && !empty($this->request->getPost('longitude'))))) {
                    $additional_data = [
                        'latitude' => $this->request->getPost('latitude'),
                        'longitude' => $this->request->getPost('longitude'),
                        'max_serviceable_distance' => $settings['max_serviceable_distance'],
                    ];
                }
                $is_latitude_set = "";
                if (isset($additional_data['latitude']) && !empty($additional_data['latitude'])) {
                    $latitude = $this->request->getPost('latitude');
                    $longitude = $this->request->getPost('longitude');
                    $is_latitude_set = " st_distance_sphere(POINT(' $longitude','$latitude'), POINT(`p`.`longitude`, `p`.`latitude` ))/1000  as distance";
                }
                $builder1 = $db->table('users u1');

                $partners1 = $builder1->Select("u1.username,u1.city,u1.latitude,u1.longitude,u1.id,pc.minimum_order_amount,
                (SELECT COUNT(*) FROM orders o WHERE o.partner_id = u1.id AND o.parent_id IS NULL) as number_of_orders,st_distance_sphere(POINT($longitude, $latitude),
                 POINT(`longitude`, `latitude` ))/1000  as distance")
                    ->join('users_groups ug1', 'ug1.user_id=u1.id')
                    ->join('services s', 's.id=u1.id', 'left')
                    ->join('services_ratings sr', 'sr.service_id = s.id', 'left')
                    ->join('partner_subscriptions ps', 'ps.partner_id=u1.id')
                    ->join('promo_codes pc', 'pc.partner_id=u1.id', 'left')
                    ->where('ps.status', 'active')
                    ->where('ug1.group_id', '3')
                    ->whereIn('u1.id', $partners_ids)
                    ->having('distance < ' . $additional_data['max_serviceable_distance'])
                    ->orderBy('distance')
                    ->get()->getResultArray();


                $ids = [];
                foreach ($partners1 as $key => $row1) {
                    $ids[] = $row1['id'];
                }

                //new added for order - start
                foreach ($ids as $key => $id) {
                    $partner_subscription = fetch_details('partner_subscriptions', ['partner_id' => $id, 'status' => 'active']);
                    if ($partner_subscription) {
                        $subscription_purchase_date = $partner_subscription[0]['updated_at'];
                        $partner_order_limit = fetch_details('orders', ['partner_id' => $id, 'parent_id' => null, 'created_at >' => $subscription_purchase_date]);
                        $partners_subscription = $db->table('partner_subscriptions ps');
                        $partners_subscription_data = $partners_subscription->select('ps.*')->where('ps.status', 'active')
                            ->get()
                            ->getResultArray();

                        $subscription_order_limit = $partners_subscription_data[0]['max_order_limit'];
                        if ($partners_subscription_data[0]['order_type'] == "limited") {
                            if (count($partner_order_limit) >= $subscription_order_limit) {
                                unset($ids[$key]);
                            }
                        }
                    } else {

                        unset($ids[$key]);
                    }
                }


                $parent_ids = array_values($ids);
                if (is_array($ids) && !empty($ids)) {
                    $partners = $db->table('services s');
                    $partners = $partners->Select('p.id,p.username,p.company,pc.minimum_order_amount,p.image,pd.banner,pc.discount,pc.discount_type,
                    count(sr.rating) as number_of_rating, 
                    SUM(sr.rating) as total_rating,
                    (SUM(sr.rating) / count(sr.rating)) as average_rating,
                     (SELECT COUNT(*) FROM orders o WHERE o.partner_id = p.id AND o.parent_id IS NULL) as number_of_orders,pd.company_name,' . $is_latitude_set)
                        ->join('services_ratings sr', 'sr.service_id = s.id', 'left')
                        ->join('users p', 'p.id=s.user_id')
                        ->join('partner_details pd', 'pd.partner_id=s.user_id')
                        ->join('promo_codes pc', 'pc.partner_id=p.id', 'left')
                        ->whereIn('s.user_id', $ids)
                        ->where('pd.is_approved', '1')
                        ->groupBy('p.id')
                        ->get()
                        ->getResultArray();

                    for ($i = 0; $i < count($partners); $i++) {
                        $partners[$i]['upto'] = $partners[$i]['minimum_order_amount'];
                        if (!empty($partners[$i]['image'])) {
                            $image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $partners[$i]['image'])) ? base_url('public/backend/assets/profiles/' . $partners[$i]['image']) : ((file_exists(FCPATH . $partners[$i]['image'])) ? base_url($partners[$i]['image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $partners[$i]['image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $partners[$i]['image'])));
                            $partners[$i]['image'] = $image;
                            $banner_image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $partners[$i]['banner'])) ? base_url('public/backend/assets/profiles/' . $partners[$i]['banner']) : ((file_exists(FCPATH . $partners[$i]['banner'])) ? base_url($partners[$i]['banner']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $partners[$i]['banner'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $partners[$i]['banner'])));
                            $partners[$i]['banner_image'] = $banner_image;
                            unset($partners[$i]['banner']);

                            if ($partners[$i]['discount_type'] == 'percentage') {
                                // $discount = $partners[$i]['discount'];
                                $upto = $partners[$i]['minimum_order_amount'];
                                unset($partners[$i]['discount_type']);
                            }
                        }


                        unset($partners[$i]['minimum_order_amount']);
                    }
                    $parent_ids = implode(", ", $parent_ids);
                } else {
                    // $data1['sections'] = [];
                    // $data1['sliders'] = [];
                    // $data1['categories'] = [];
                    // $data = $data1;
                    // $message = "data not found";
                    // $error = true;
                    // return response($message, $error, $data, 200);
                }
                $type = 'partners';
            }
            $tempRow['id'] = $row['id'];
            $tempRow['title'] = $row['title'];
            $tempRow['section_type'] = $type;

            if ($type == 'partners') {
                $tempRow['parent_ids'] = $parent_ids;
                $tempRow['partners'] = $partners;
                $tempRow['sub_categories'] = [];
                $tempRow['previous_order'] = [];
                $tempRow['ongoing_order'] = [];
            } else if ($type == 'sub_categories') {
                $tempRow['sub_categories'] = $partners;
                $tempRow['parent_ids'] = (isset($parent_ids) && !empty($parent_ids)) ? $parent_ids : "";
                $tempRow['partners'] = [];
                $tempRow['previous_order'] = [];
                $tempRow['ongoing_order'] = [];
            } else if ($type == 'top_rated_partner') {
                // $tempRow['top_rated_partner'] = $rating_data;
                $tempRow['parent_ids'] = $rate_parent_ids;
                $tempRow['sub_categories'] = [];
                $tempRow['partners'] = $rating_data;
                $tempRow['previous_order'] =  [];
                $tempRow['ongoing_order'] = [];
            } else if ($type == 'previous_order') {
                if (!empty($this->user_details['id'])) {

                    $tempRow['parent_ids'] = $order_data_id;
                    $tempRow['previous_order'] = $order_data['data'];
                    $tempRow['sub_categories'] = [];
                    $tempRow['partners'] = [];
                    $tempRow['ongoing_order'] = [];
                } else {
                    $tempRow['parent_ids'] = [];
                    $tempRow['ongoing_order'] = [];
                    $tempRow['sub_categories'] = [];
                    $tempRow['partners'] = [];
                    $tempRow['previous_order'] = [];
                }
                // $tempRow['top_rated_partner'] = $rating_data;

            } else if ($type == 'ongoing_order') {
                if (!empty($this->user_details['id'])) {
                    $tempRow['parent_ids'] = $onging_order_data_id;
                    $tempRow['ongoing_order'] = $ongoing_order_data['data'];
                    $tempRow['sub_categories'] = [];
                    $tempRow['partners'] = [];
                    $tempRow['previous_order'] = [];
                } else {
                    $tempRow['parent_ids'] = [];
                    $tempRow['ongoing_order'] = [];
                    $tempRow['sub_categories'] = [];
                    $tempRow['partners'] = [];
                    $tempRow['previous_order'] = [];
                }
                // $tempRow['top_rated_partner'] = $rating_data;

            }
            $rows[] = $tempRow;
        }


        $section_data = remove_null_values($rows);
        $slider = new Slider_model();
        $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 50;
        $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
        $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'sl.id';
        $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
        $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
        $where = [];
        if ($this->request->getPost('id')) {
            $where['id'] = $this->request->getPost('id');
        }
        if ($this->request->getPost('type')) {
            $where['type'] = $this->request->getPost('type');
        }
        if ($this->request->getPost('type_id')) {
            $where['type_id'] = $this->request->getPost('type_id');
        }
        $slider_data = $slider->list(true, $search, $limit, $offset, $sort, $order, $where);
        // returns everything from model
        // for categories
        $categories = new Category_model();
        $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
        $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
        $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
        $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
        $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
        $where = [];
        if ($this->request->getPost('id')) {
            $where['id'] = $this->request->getPost('id');
        }
        if ($this->request->getPost('slug')) {
            $where['slug'] = $this->request->getPost('slug');
        }
        $where['parent_id'] = 0;
        $category_data = $categories->list(true, $search, null, null, $sort, $order, $where);
        $data['sections'] = $section_data;
        $data['sliders'] = remove_null_values($slider_data['data']);
        $data['categories'] = remove_null_values($category_data['data']);
        if (!empty($rows)) {
            $error = false;
            $message = 'sections fetched successfully';
        } else {
            $error = true;
            $message = 'data not found';
        }
        return response($message, $error, $data, 200);
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    // 5. get_sections
    public function get_sections()
    {
        try {
            /*  
        latitude:12.1234578
        longitude:12.1234578
        id:15                   {optional}
        limit:10                {optional}
        offset:0                {optional}
        sort:id                 {optional}
        order:asc               {optional}
        search:test             {optional}
         */
            $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $where = $additional_data = [];
            $multipleWhere = $partner_ids = '';
            $db = \Config\Database::connect();
            $builder = $db->table('sections');
            $sortable_fields = ['id' => 'id', 'title' => 'title', 'categories' => 'categories', 'style' => 'style', 'service_type' => 'service_type'];
            if (isset($search) and $search != '') {
                $multipleWhere = ['`id`' => $search, '`title`' => $search];
            }
            if ($this->request->getPost('id')) {
                $where['id'] = $this->request->getPost('id');
            }
            // count of section
            if (isset($multipleWhere) && !empty($multipleWhere)) {
                $builder->orWhere($multipleWhere);
            }
            if (isset($where) && !empty($where)) {
                $builder->where($where);
            }
            $total = $builder->select(' COUNT(id) as `total` ');
            $offer_count = $builder->get()->getResultArray();
            $total = $offer_count[0]['total'];
            // get section data
            $builder->select();
            if (isset($multipleWhere) && !empty($multipleWhere)) {
                $builder->orWhere($multipleWhere);
            }
            if (isset($where) && !empty($where)) {
                $builder->where($where);
            }
            $offer_recorded = $builder->orderBy($sort, $order)->limit($limit, $offset)
                ->get()->getResultArray();
            $bulkData = array();
            $bulkData['total'] = $total;
            $rows = array();
            $tempRow = array();
            foreach ($offer_recorded as $row) {
                $partners = $sub_category_ids = $partners_ids = [];
                if ($row['section_type'] == 'categories') {
                    if (!is_null($row['category_ids'])) {
                        $partners = $db->table('categories c');
                        $category_ids = explode(',', $row['category_ids']);
                        $ids = (!empty($sub_category_ids)) ? $sub_category_ids : $category_ids;
                        $partners = $partners->Select('c.*')
                            ->whereIn('c.id', $ids)
                            ->get()
                            ->getResultArray();
                        for ($i = 0; $i < count($partners); $i++) {
                            $partners[$i]['image'] = (!empty($partners[$i]['image'])) ? base_url('/public/uploads/categories/' . $partners[$i]['image']) : "";
                            $partners[$i]['discount'] = $partners[$i]['upto'] = "";
                            unset($partners[$i]['created_at']);
                            unset($partners[$i]['updated_at']);
                            unset($partners[$i]['deleted_at']);
                            unset($partners[$i]['slug']);
                            unset($partners[$i]['admin_commission']);
                            unset($partners[$i]['status']);
                            $parent_ids = array_values(array_unique(array_column($partners, "parent_id")));
                            $parent_ids = implode(", ", $parent_ids);
                        }
                    }
                    $type = 'sub_categories';
                } else {
                    if (!is_null($row['partners_ids'])) {
                        $partners_ids = explode(',', $row['partners_ids']);
                    }
                    if (is_array($partners_ids) && !empty($partners_ids)) {
                        $partners = $db->table('services s');
                        $partners = $partners->Select('p.id,p.username,pc.minimum_order_amount,p.image,pc.discount,pc.discount_type')
                            ->join('users p', 'p.id=s.user_id')
                            ->join('promo_codes pc', 'pc.partner_id=p.id', 'left')
                            ->whereIn('s.user_id', $partners_ids)
                            ->groupBy('p.id')
                            ->get()
                            ->getResultArray();
                        for ($i = 0; $i < count($partners); $i++) {
                            $partners[$i]['upto'] = $partners[$i]['minimum_order_amount'];
                            if (!empty($partners[$i]['image'])) {
                                $partners[$i]['image'] = base_url('public/uploads/users/partners/' . $partners[$i]['image']);
                                if ($partners[$i]['discount_type'] == 'percentage') {
                                    $discount = $partners[$i]['discount'];
                                    $upto = $partners[$i]['minimum_order_amount'];
                                    unset($partners[$i]['discount_type']);
                                }
                            }
                            unset($partners[$i]['minimum_order_amount']);
                        }
                    }
                    $parent_ids = implode(", ", $partners_ids);
                    $type = 'partners';
                }
                $tempRow['id'] = $row['id'];
                $tempRow['title'] = $row['title'];
                $tempRow['section_type'] = $type;
                if ($type == 'partners') {
                    $tempRow['parent_ids'] = $parent_ids;
                    $tempRow['partners'] = $partners;
                    $tempRow['sub_categories'] = [];
                } else if ($type == 'sub_categories') {
                    $tempRow['sub_categories'] = $partners;
                    $tempRow['parent_ids'] = (isset($parent_ids) && !empty($parent_ids)) ? $parent_ids : "";
                    $tempRow['partners'] = [];
                }
                $rows[] = $tempRow;
            }
            if (!empty($rows)) {
                $error = false;
                $message = 'sections fetched successfully';
            } else {
                $error = true;
                $message = 'data not found';
            }
            return response($message, $error, remove_null_values($rows), 200, ['total' => $total]);
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 5. add_transaction()
    public function add_transaction()
    {
        // try {
        /*
            order_id:  23
            status : success / failure
         */
        $validation = service('validation');
        $validation->setRules([
            'order_id' => 'required|numeric',
            'status' => 'required',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }

        $transaction_model = new Transaction_model();
        $order_id = (int) $this->request->getVar('order_id');
        $status = $this->request->getVar('status');

        $user = fetch_details('users', ['id' => $this->user_details['id']]);
        if (empty($user)) {
            $response = [
                'error' => true,
                'message' => "User not found!",
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }

        $order = fetch_details('orders', ['id' => $this->request->getVar('order_id')]);


        $transaction = fetch_details('transactions', ['order_id' => $this->request->getVar('order_id')]);


        if (!empty($order)) {

            $data['status'] = $status;
            $update =  update_details(['status' => "awaiting"], ['id' => $order_id, 'status' => 'awaiting', 'user_id' => $this->user_details['id']], 'orders');

            if ($status == "success") {

                $transaction = fetch_details('transactions', ['order_id' => $order[0]['id'], 'user_id' => $this->user_details['id']]);
                if (!empty($transaction)) {

                    $data1['status'] = 'success';
                    update_details($data1, ['order_id' => $order_id, 'user_id' => $this->user_details['id']], 'transactions');
                }
                $cart_data = fetch_cart(true, $this->user_details['id']);
                if (!empty($cart_data)) {

                    foreach ($cart_data['data'] as $row) {
                        delete_details(['id' => $row['id']], 'cart');
                    }
                }
            } else {
                if (!empty($transaction)) {
                    $data1['status'] = 'failed';
                    update_details($data1, ['order_id' => $order_id, 'user_id' => $this->user_details['id']], 'transactions');
                    update_details(['status' => "cancle"], ['id' => $order_id, 'status' => 'awaiting', 'user_id' => $this->user_details['id']], 'orders');
                } else {

                    $data = [
                        'transaction_type' => 'transaction',
                        'user_id' => $this->user_details['id'],
                        'partner_id' => "",
                        'order_id' => $order_id,
                        'type' => '',
                        'txn_id' => "",
                        'amount' => $order[0]['final_total'],
                        'status' => 'failed',
                        'currency_code' => "",
                        'message' => 'payment cancle by customer',
                    ];
                    add_transaction($data);
                    update_details(['status' => "cancelled"], ['id' => $order_id, 'status' => 'awaiting', 'user_id' => $this->user_details['id']], 'orders');
                }
            }

            if ($update) {
                $response['error'] = false;
                $response['message'] = 'Status Updated';
            }
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        // }
        return $this->response->setJSON($response);
    }
    // 6. get_transactions
    public function get_transactions()
    {
        try {
            /*
        limit:10            {optional}
        offset:0            {optional}
        sort:id             {optional}
        order:asc           {optional}
         */
            $request = \Config\Services::request();
            $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'DESC';
            $user_id = $this->user_details['id'];
            if (!exists(['id' => $user_id], 'users')) {
                $response = [
                    'error' => true,
                    'message' => 'Invalid User Id.',
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $res = fetch_details('transactions', ['user_id' => $user_id], ['id', 'user_id', 'order_id', 'type', 'txn_id', 'amount', 'status', 'message', 'transaction_date', 'status'], $limit, $offset, $sort, $order);
            // $res = fetch_details('transactions', ['user_id' => $user_id], ['id', 'user_id', 'order_id', 'type', 'txn_id', 'amount', 'status', 'message'], $limit, $offset, $sort, $order);
            $total = count($res);
            if (!empty($res)) {
                $response = [
                    'error' => false,
                    'message' => 'Transactions recieved successfully.',
                    'total' => $total,
                    'data' => $res,
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => 'No data found',
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 7. add_address
    public function add_address()
    {
        try {
            /*
        address_id:40        {optional}
        mobile:1234567890
        address:time square empire
        city_id:1
        lattitude:12.123456
        longitude:123.45645
        area:bhuj
        type:office
        country_code:91     {optional}
        pincode:987654      {optional}
        state:gujrat        {optional}
        country:inda        {optional}
        is_default:1        {optional}
        landmark:#123 ,jaynagar Bhuj    {optional}
        alternate_mobile:7896541230     {optional}
         */
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'address_id' => 'permit_empty',
                    'mobile' => 'required|numeric',
                    'address' => 'required|',
                    'city_name' => 'required',
                    'lattitude' => 'required|numeric',
                    'longitude' => 'required|numeric',
                    'area' => 'required',
                    'type' => 'required',
                    'country_code' => 'permit_empty',
                    'alternate_mobile' => 'permit_empty|numeric',
                    'landmark' => 'permit_empty',
                    'pincode' => 'permit_empty|numeric',
                    'state' => 'permit_empty',
                    'country' => 'permit_empty',
                    'is_default' => 'permit_empty',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            // if city not exist
            // if (!exists(['id' => $this->request->getPost('city_id')], 'cities')) {
            //     return response('Cities not exist');
            // }
            $data = [
                'user_id' => $this->user_details['id'],
                'type' => $this->request->getPost('type'),
                'address' => $this->request->getPost('address'),
                'area' => $this->request->getPost('area'),
                'mobile' => $this->request->getPost('mobile'),
                'city' => $this->request->getPost('city_name'),
                'lattitude' => $this->request->getPost('lattitude'),
                'longitude' => $this->request->getPost('longitude'),
                'alternate_mobile' => ($this->request->getPost('alternate_mobile') && !empty($this->request->getPost('alternate_mobile'))) ? $this->request->getPost('alternate_mobile') : null,
                'pincode' => ($this->request->getPost('pincode') && !empty($this->request->getPost('pincode'))) ? $this->request->getPost('pincode') : null,
                'landmark' => ($this->request->getPost('landmark') && !empty($this->request->getPost('landmark'))) ? $this->request->getPost('landmark') : null,
                'state' => ($this->request->getPost('state') && !empty($this->request->getPost('state'))) ? $this->request->getPost('state') : null,
                'country' => ($this->request->getPost('country') && !empty($this->request->getPost('country'))) ? $this->request->getPost('country') : null,
                'is_default' => ($this->request->getPost('is_default') && !empty($this->request->getPost('is_default'))) ? $this->request->getPost('is_default') : 0,
            ];
            //insert details in table
            if ($this->request->getPost('address_id')) {
                if (!exists(['id' => $this->request->getPost('address_id')], 'addresses')) {
                    return response('address not exist');
                }
                $address_id = $this->request->getPost('address_id');
                if (isset($data['is_default']) && $data['is_default'] == 1) {
                    $address = fetch_details('addresses', ['id' => $address_id]);
                    update_details(['is_default' => '0'], ['user_id' => $address[0]['user_id']], 'addresses');
                    update_details(['is_default' => '1'], ['id' => $address_id], 'addresses');
                }
                if (update_details($data, ['id' => $address_id], 'addresses', false)) {
                    $action = true;
                    $message = "address updated successfully";
                } else {
                    $action = false;
                    $message = "address not updated";
                }
            } else {
                if ($address = insert_details($data, 'addresses')) {
                    $last_added_id = $address['id'];
                    if (isset($data['is_default']) && $data['is_default'] == 1) {
                        update_details(['is_default' => '0'], ['user_id' => $data['user_id']], 'addresses');
                        update_details(['is_default' => '1'], ['id' => $last_added_id], 'addresses');
                    }
                    $action = true;
                    $message = "address added successfully";
                    $address_id = $address['id'];
                } else {
                    $action = false;
                    $message = "address not added";
                }
            }
            if ($action) {
                $data = [];
                return response($message, false, $data);
            } else {
                return response($message);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 8. delete_address
    public function delete_address()
    {
        // try {
        /*
        address_id:12
         */
        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'address_id' => 'required',
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $address_id = $this->request->getPost('address_id');
        if (!exists(['id' => $this->request->getPost('address_id'), 'user_id' => $this->user_details['id']], 'addresses')) {
            return response('address not exist');
        }
        if (delete_details(['id' => $address_id], 'addresses')) {



            $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 20;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $where = [];
            $where['a.user_id'] = $this->user_details['id'];
            if ($this->request->getPost('address_id')) {
                $where['a.id'] = $this->request->getPost('address_id');
            }
            if (!empty($address_id)) {
                $where['a.id'] = $address_id;
            }

            $is_default_counter = fetch_details('addresses', ['user_id' => $this->user_details['id'], 'is_default' => '1']);

            if (empty($is_default_counter)) {
                $data = fetch_details('addresses', ['user_id' => $this->user_details['id']]);

                update_details(['is_default' => '1'], ['id' => $data[0]['id']], 'addresses');
                $data1 = fetch_details('addresses', ['user_id' => $this->user_details['id']]);
            }

            return response('Address Deleted successfully', false, $data1);
        } else {
            return response('Address not deleted');
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    // 9. get_address
    public function get_address($address_id = 0)
    {
        try {
            /*
        address_id:11   {optional}
        limit:10        {optional}
        offset:0        {optional}
        sort:id         {optional}
        order:asc       {optional}
        search:bhuj     {optional}
         */
            $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 20;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $where = [];
            $where['a.user_id'] = $this->user_details['id'];
            if ($this->request->getPost('address_id')) {
                $where['a.id'] = $this->request->getPost('address_id');
            }
            if (!empty($address_id)) {
                $where['a.id'] = $address_id;
            }
            $address_model = new Addresses_model();
            $address = $address_model->list(true, $search, $limit, $offset, $sort, $order, $where);
            $is_default_counter = array_count_values(array_column($address['data'], 'is_default'));
            if (!isset($is_default_counter['1']) && !empty($address['data'])) {
                update_details(['is_default' => '1'], ['id' => $address['data'][0]['id']], 'addresses');
            }
            if (!empty($address_id)) {
                return remove_null_values($address['data']);
            }
            if (!empty($address['data'])) {
                return response('addresses fetched successfully', false, remove_null_values($address['data']), 200, ['total' => $address['total']]);
            } else {
                return response('address not found', false);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 10. validate_promo_code
    public function validate_promo_code()
    {
        try {
            /*
        promo_code:FOREVER
        final_total:100
         */
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'promo_code' => 'required',
                    'final_total' => 'required|numeric',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $promo_code = $this->request->getPost('promo_code');
            $final_total = $this->request->getPost('final_total');
            if (!exists(['promo_code' => $promo_code], 'promo_codes')) {
                return response('promo code not exist');
            }

            $fetch_promococde = fetch_details('promo_codes', ['promo_code' => $promo_code]);
            $promo_code = validate_promo_code($this->user_details['id'], $fetch_promococde[0]['id'], $final_total);
            if ($promo_code['error'] == false) {
                return response($promo_code['message'], false, remove_null_values($promo_code['data']));
            } else {
                return response($promo_code['message']);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 11. get_promo_codes
    public function get_promo_codes()
    {
        // try {
        /*
        partner_id:12
        // limit:10            {optional}
        // offset:0            {optional}
        sort:id             {optional}
        order:asc           {optional}
        search:forever      {optional}
         */
        $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
        $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
        $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
        $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
        $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
        $where = [];
        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'partner_id' => 'required',
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $partner_id = $this->request->getPost('partner_id');
        $where = ['pc.partner_id' => $partner_id, 'pc.status' => 1, ' start_date <= ' => date('Y-m-d'), '  end_date >= ' => date('Y-m-d')];
        $promo_codes_model = new Promo_code_model();
        $promo_codes = $promo_codes_model->list(true, $search, null, null, $limit, $order, $where);
        if (!empty($promo_codes['data'])) {
            return response('promo codes fetched successfully', false, remove_null_values($promo_codes['data']), 200, ['total' => $promo_codes['total']]);
        } else {
            return response('Data Not Found');
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    // 12. get_categories
    // public function get_categories()
    // {
    //     /*
    //     id:10                   {optional}
    //     slug:repairing works    {optional}
    //     sort:id                 {optional}
    //     order:asc               {optional}
    //     search:repairing        {optional}
    //      */
    //     $categories = new Category_model();
    //     // $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
    //     // $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
    //     $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
    //     $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
    //     $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
    //     $where = [];
    //     if ($this->request->getPost('id')) {
    //         $where['id'] = $this->request->getPost('id');
    //     }
    //     if ($this->request->getPost('slug')) {
    //         $where['slug'] = $this->request->getPost('slug');
    //     }
    //     $where['parent_id'] = 0;
    //     $data = $categories->list(true, $search, null, null, $sort, $order, $where);
    //     if (!empty($data['data'])) {
    //         return response('Categories fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
    //     } else {
    //         return response('categories not found', false);
    //     }
    // }
    public function get_categories()
    {
        // try {
        /*
        id:10                   {optional}
        slug:repairing works    {optional}
        sort:id                 {optional}
        order:asc               {optional}
        search:repairing        {optional}
         */
        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'latitude' => 'required',
                'longitude' => 'required',
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $categories = new Category_model();
        // $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
        // $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
        $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
        $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
        $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
        $where = [];
        if ($this->request->getPost('id')) {
            $where['id'] = $this->request->getPost('id');
        }
        if ($this->request->getPost('slug')) {
            $where['slug'] = $this->request->getPost('slug');
        }
        $where['parent_id'] = 0;
        $data = $categories->list(true, $search, null, null, $sort, $order, $where);
        $db = \Config\Database::connect();
        $customer_latitude = $this->request->getPost('latitude');
        $customer_longitude = $this->request->getPost('longitude');
        $settings = get_settings('general_settings', true);
        $builder = $db->table('users u');
        $distance = isset($settings['max_serviceable_distance']) ? $settings['max_serviceable_distance'] : "50";
        // $cart_details = fetch_cart(true, $this->user_details['id']);
        // print_r($cart_details);
        // die;
        // $provider_data = fetch_details('users', ['id' => $cart_details['provider_id']]);
        // $provider_latitude = $provider_data[0]['latitude'];
        // $provider_longitude = $provider_data[0]['longitude'];
        $partners = $builder->Select("u.username,u.city,u.latitude,u.longitude,u.id,st_distance_sphere(POINT($customer_longitude, $customer_latitude),POINT(`u`.`longitude`, `u`.`latitude` ))/1000 as distance")
            ->join('users_groups ug', 'ug.user_id=u.id')
            ->where('ug.group_id', '3')
            ->where('u.latitude is  NOT NULL')
            ->where('u.longitude is  NOT NULL')
            ->having('distance < ' . $distance)
            ->orderBy('distance')
            ->get()->getResultArray();
        if (!empty($partners)) {
            if (!empty($data['data'])) {
                return response('Categories fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
            } else {
                return response('categories not found', false);
            }
        } else {
            return response('categories not found', false);
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    // // 13. get_sub_categories
    // public function get_sub_categories()
    // {
    //     /*
    //     category_id:12
    //     id:150                  {optional}
    //     sort:id                 {optional}
    //     order:asc               {optional}
    //     search:repairing        {optional}
    //      */
    //     $validation = \Config\Services::validation();
    //     $validation->setRules(
    //         [
    //             'category_id' => 'required',
    //         ]
    //     );
    //     if (!$validation->withRequest($this->request)->run()) {
    //         $errors = $validation->getErrors();
    //         $response = [
    //             'error' => true,
    //             'message' => $errors,
    //             'data' => [],
    //         ];
    //         return $this->response->setJSON($response);
    //     }
    //     $categories = new Category_model();
    //     // $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
    //     // $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
    //     $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
    //     $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
    //     $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
    //     $where = [];
    //     if ($this->request->getPost('id')) {
    //         $where['id'] = $this->request->getPost('id');
    //     }
    //     if ($this->request->getPost('slug')) {
    //         $where['slug'] = $this->request->getPost('slug');
    //     }
    //     if ($this->request->getPost('category_id')) {
    //         $where['parent_id'] = $this->request->getPost('category_id');
    //     }
    //     if (!exists(['parent_id' => $this->request->getPost('category_id')], 'categories')) {
    //         return response('no sub categories found');
    //     }
    //     $data = $categories->list(true, $search, null, null, $sort, $order, $where);
    //     if (!empty($data['data'])) {
    //         return response('Sub Categories fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
    //     } else {
    //         return response('Sub categories not found', false);
    //     }
    // }
    public function get_sub_categories()
    {
        try {
            /*
        category_id:12
        id:150                  {optional}
        sort:id                 {optional}
        order:asc               {optional}
        search:repairing        {optional}
         */
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'category_id' => 'required',
                    'latitude' => 'required',
                    'longitude' => 'required',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $categories = new Category_model();
            // $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
            // $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $where = [];
            if ($this->request->getPost('id')) {
                $where['id'] = $this->request->getPost('id');
            }
            if ($this->request->getPost('id')) {
                $where['status'] = 1;
            }
            if ($this->request->getPost('slug')) {
                $where['slug'] = $this->request->getPost('slug');
            }
            if ($this->request->getPost('category_id')) {
                $where['parent_id'] = $this->request->getPost('category_id');
            }
            if (!exists(['parent_id' => $this->request->getPost('category_id')], 'categories')) {
                return response('no sub categories found');
            }
            $data = $categories->list(true, $search, null, null, $sort, $order, $where);
            $db = \Config\Database::connect();
            $customer_latitude = $this->request->getPost('latitude');
            $customer_longitude = $this->request->getPost('longitude');
            $settings = get_settings('general_settings', true);
            $builder = $db->table('users u');
            $distance = $settings['max_serviceable_distance'];
            $partners = $builder->Select("u.username,u.city,u.latitude,u.longitude,u.id,st_distance_sphere(POINT($customer_longitude, $customer_latitude),POINT(`u`.`longitude`, `u`.`latitude` ))/1000 as distance")
                ->join('users_groups ug', 'ug.user_id=u.id')
                ->where('ug.group_id', '3')
                ->having('distance < ' . $distance)
                ->orderBy('distance')
                ->get()->getResultArray();
            if (!empty($partners)) {
                if (!empty($data['data'])) {
                    return response('Sub Categories fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
                } else {
                    return response('Sub categories not found', false);
                }
            } else {
                return response('Sub categories not found', false);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 14. get_sliders
    public function get_sliders()
    {
        try {
            /*
        type_id:12              {optional}
        id:150                  {optional}
        type:default            {optional}
        limit:10                {optional}
        offset:0                {optional}
        sort:id                 {optional}
        order:asc               {optional}
        search:repairing        {optional}
         */
            $slider = new Slider_model();
            $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $where = [];
            if ($this->request->getPost('id')) {
                $where['id'] = $this->request->getPost('id');
            }
            if ($this->request->getPost('type')) {
                $where['type'] = $this->request->getPost('type');
            }
            if ($this->request->getPost('type_id')) {
                $where['type_id'] = $this->request->getPost('type_id');
            }
            $data = $slider->list(true, $search, $limit, $offset, $sort, $order, $where);
            if (!empty($data['data'])) {
                return response('slider fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
            } else {
                return response('slider not found');
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }



    public function get_providers()
    {
        /*
        latitude:22.839715
        longitude:69.704199
        partner_id:12           {optional}
        category_id:150         {optional}
        service_id:12           {optional}
        sub_category_id:172     {optional}
        limit:10                {optional}
        offset:0                {optional}
        sort:id                 {optional}
        order:asc               {optional}
        search:repairing        {optional}
         */
        // try {
        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'latitude' => 'required',
                'longitude' => 'required',
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $Partners_model = new Partners_model();
        $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 0;
        $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
        $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('sort'))) ? $this->request->getPost('sort') : 'pd.id';
        $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
        $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
        $filter = ($this->request->getPost('filter') && !empty($this->request->getPost('filter'))) ? $this->request->getPost('filter') : '';
        $where = $additional_data = [];
        $customer_id = '';
        $city_id = '';
        $token = verify_app_request();
        $settings = get_settings('general_settings', true);

        if (empty($settings)) {
            $response = [
                'error' => true,
                'message' => "Finish the general settings in panel",
                // 'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        if ($token['error'] == 0) {
            $customer_id = $token['data']['id'];
            $additional_data = [
                'customer_id' => $customer_id,
            ];
            $settings = get_settings('general_settings', true);
            if (empty($settings)) {
                $response = [
                    'error' => true,
                    'message' => "Finish the general settings in panel",
                    // 'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            if (empty($settings['max_serviceable_distance'])) {
                $response = [
                    'error' => true,
                    'message' => "First set Max serviceable distance in panel",
                    // 'data' => [],
                ];
                return $this->response->setJSON($response);
            }

            if (($this->request->getPost('latitude') && !empty($this->request->getPost('latitude')) && ($this->request->getPost('longitude') && !empty($this->request->getPost('longitude'))))) {
                $additional_data = [
                    'latitude' => $this->request->getPost('latitude'),
                    'longitude' => $this->request->getPost('longitude'),
                    // 'city_id' => $token['data']['city_id'],
                    'max_serviceable_distance' => $settings['max_serviceable_distance'],
                ];
            }
        }
        $settings = get_settings('general_settings', true);
        if (($this->request->getPost('latitude') && !empty($this->request->getPost('latitude')) && ($this->request->getPost('longitude') && !empty($this->request->getPost('longitude'))))) {
            if (empty($settings)) {
                $response = [
                    'error' => true,
                    'message' => "Finish the general settings in panel",
                    // 'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            if (empty($settings['max_serviceable_distance'])) {
                $response = [
                    'error' => true,
                    'message' => "First set Max serviceable distance in panel",
                    // 'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $additional_data = [
                'latitude' => $this->request->getPost('latitude'),
                'longitude' => $this->request->getPost('longitude'),
                // 'city_id' => $token['data']['city_id'],
                'max_serviceable_distance' => $settings['max_serviceable_distance'],
            ];
        }
        //---------------
        if ($this->request->getPost('partner_id') && !empty($this->request->getPost('partner_id'))) {

            $where['pd.partner_id'] = $this->request->getPost('partner_id');
            $where_condition_for_max_order_limit = '';
            $where['ps.status'] = 'active';
        }
        $where['ps.status'] = 'active';
        $where['pd.is_approved'] = "1";
        if ($this->request->getPost('category_id') && !empty($this->request->getPost('category_id'))) {
            $category_id[] = $this->request->getPost('category_id');
            $subcategory_data = fetch_details('categories', ['id' => $category_id], ['id', 'parent_id']);
            foreach ($subcategory_data as $res) {
                array_push($category_id, $res['parent_id']);
            }
            $c_id = implode(",", $category_id);
            $partner_ids = get_partner_ids('category', 'category_id', [$c_id], true);



            $where['ps.status'] = 'active';
            // $data = (!empty($partner_ids)) ? $Partners_model->list(true, $search, $limit, $offset, $sort, $order, $where, 'pd.partner_id', $partner_ids, $additional_data, 'yes') : [];
            $data = (!empty($partner_ids)) ? $Partners_model->list(true, $search, $limit, $offset, $sort, $order, $where, 'pd.partner_id', $partner_ids, $additional_data, 'yes') : [];


            if ((!empty($partner_ids)) && ($filter != '' && $filter == 'ratings')) {
                $where['ps.status'] = 'active';
                $data = $Partners_model->list(true, $search, $limit, $offset, ' pd.ratings', 'desc', $where, 'pd.partner_id', $partner_ids, $additional_data, 'yes');
            }
            if ((!empty($partner_ids)) && ($filter != '' && $filter == 'discount')) {
                $where['ps.status'] = 'active';
                $data = $Partners_model->list(true, $search, $limit, $offset, ' maximum_discount_up_to', 'desc', $where, 'pd.partner_id', $partner_ids, $additional_data, 'yes');
            }
            if ((!empty($partner_ids)) && ($filter != '' && $filter == 'popularity')) {
                $where['ps.status'] = 'active';
                $data = $Partners_model->list(true, $search, $limit, $offset, ' number_of_orders', 'desc', $where, 'pd.partner_id', $partner_ids, $additional_data, 'yes');
            }

            $where_condition_for_max_order_limit = '';
        } else if ($this->request->getPost('service_id') && !empty($this->request->getPost('service_id'))) {
            $where['ps.status'] = 'active';
            $service_id[] = $this->request->getPost('service_id');
            $partner_ids = get_partner_ids('service', 'id', $service_id, true);
            $data = (!empty($partner_ids)) ? $Partners_model->list(true, $search, $limit, $offset, $sort, $order, $where, 'pd.partner_id', $partner_ids, $additional_data, 'yes') :
                [];

            if ((!empty($partner_ids)) && ($filter != '' && $filter == 'ratings')) {

                $data = $Partners_model->list(true, $search, $limit, $offset, ' pd.ratings', $order, $where, 'pd.partner_id', $partner_ids, $additional_data, 'yes');
            }
            if ((!empty($partner_ids)) && ($filter != '' && $filter == 'discount')) {
                $data = $Partners_model->list(true, $search, $limit, $offset, ' maximum_discount_up_to', $order, $where, 'pd.partner_id', $partner_ids, $additional_data, 'yes');
            }
            if ((!empty($partner_ids)) && ($filter != '' && $filter == 'popularity')) {

                $data = $Partners_model->list(true, $search, $limit, $offset, ' number_of_orders', $order, $where, 'pd.partner_id', $partner_ids, $additional_data, 'yes');
            }
            $where_condition_for_max_order_limit = '';
            $where['ps.status'] = 'active';
        } else if ($this->request->getPost('sub_category_id') && !empty($this->request->getPost('sub_category_id'))) {
            $where['ps.status'] = 'active';
            $sub_category_id[] = $this->request->getPost('sub_category_id');
            $partner_ids = get_partner_ids('category', 'category_id', $sub_category_id, true);
            $data = (!empty($partner_ids)) ? $Partners_model->list(true, $search, $limit, $offset, $sort, $order, $where, 'pd.partner_id', $partner_ids, $additional_data, 'yes') : [];

            if ((!empty($partner_ids)) && ($filter != '' && $filter == 'ratings')) {

                $data = $Partners_model->list(true, $search, $limit, $offset, 'pd.ratings', $order, $where, 'pd.partner_id', $partner_ids, $additional_data, 'yes');
            }
            if ((!empty($partner_ids)) && ($filter != '' && $filter == 'discount')) {
                $data = $Partners_model->list(true, $search, $limit, $offset, 'maximum_discount_up_to', $order, $where, 'pd.partner_id', $partner_ids, $additional_data, 'yes');
            }
            if ((!empty($partner_ids)) && ($filter != '' && $filter == 'popularity')) {
                $data = $Partners_model->list(true, $search, $limit, $offset, 'number_of_orders', $order, $where, 'pd.partner_id', $partner_ids, $additional_data, 'yes');
            }
            $where_condition_for_max_order_limit = '';
            $where['ps.status'] = 'active';
        } elseif ($filter != '' && $filter == 'popularity') {
            $where['ps.status'] = 'active';
            $data = $Partners_model->list(true, $search, $limit, $offset, 'number_of_orders', 'desc', $where, 'partner_id', [], $additional_data, 'yes');
        } elseif ($filter != '' && $filter == 'ratings') {

            $where['ps.status'] = 'active';

            $data = $Partners_model->list(true, $search, $limit, $offset, ' pd.ratings', 'desc', $where, 'pd.partner_id', [], $additional_data, 'yes');
        } elseif ($filter != '' && $filter == 'discount') {
            $data = $Partners_model->list(true, $search, $limit, $offset, 'maximum_discount_up_to', 'desc', $where, 'pd.partner_id', [], $additional_data, 'yes');
        } else {


            $where['ps.status'] = 'active';
            $additional_data = [
                'latitude' => $this->request->getPost('latitude'),
                'longitude' => $this->request->getPost('longitude'),
                // 'city_id' => $token['data']['city_id'],
                'max_serviceable_distance' => $settings['max_serviceable_distance'],
            ];

            $where_condition_for_max_order_limit = '';
            $where['ps.status'] = 'active';


            $data = $Partners_model->list(true, $search, $limit, $offset, $sort, $order, $where, 'pd.id', [], $additional_data, 'yes');
        }


        $where['ps.status'] = 'active';

        if (!empty($data['data'])) {
            for ($i = 0; $i < count($data['data']); $i++) {
                unset($data['data'][$i]['national_id']);
                unset($data['data'][$i]['passport']);
                unset($data['data'][$i]['tax_name']);
                unset($data['data'][$i]['tax_number']);
                unset($data['data'][$i]['bank_name']);
                unset($data['data'][$i]['account_number']);
                unset($data['data'][$i]['account_name']);
                unset($data['data'][$i]['bank_code']);
                unset($data['data'][$i]['swift_code']);
                unset($data['data'][$i]['type']);
                // unset($data['data'][$i]['advance_booking_days']);
                unset($data['data'][$i]['admin_commission']);
            }
            return response('partners fetched successfully', false, remove_null_values($data['data']), 200, ['total' => $data['total']]);
        } else {


            // return response('partners fetched successfully', false, remove_null_values($data['data']), 200, ['total' => $data['total']]);
            return response('partners fetched successfully', false, remove_null_values(isset($data['data']) ? $data['data'] : array()), 200, ['total' => isset($data['total']) ? $data['total'] : 0]);


            return response('partners not found..', false);
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    // 16. get_services
    public function get_services()
    {
        /*
        latitude:22.839715
        longitude:69.704199
        partner_id:12           {optional}
        category_id:150         {optional}
        limit:10                {optional}
        offset:0                {optional}
        sort:id                 {optional}
        order:asc               {optional}
        search:repairing        {optional}
         */
        // $validation = \Config\Services::validation();
        // $validation->setRules(
        //     [
        //         // 'latitude' => 'required',
        //         // 'longitude' => 'required',
        //     ]
        // );
        // if (!$validation->withRequest($this->request)->run()) {
        //     $errors = $validation->getErrors();
        //     $response = [
        //         'error' => true,
        //         'message' => $errors,
        //         'data' => [],
        //     ];
        //     return $this->response->setJSON($response);
        // }
        // try {
        $Service_model = new Service_model();
        $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
        $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
        $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
        $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
        $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
        $db      = \Config\Database::connect();
        $where = $additional_data = [];
        $where['s.status'] = '1';


        $where = [];
        $at_store = 0;
        $at_doorstep = 0;
        if ($this->request->getPost('partner_id') && !empty($this->request->getPost('partner_id'))) {
            $partner_details = fetch_details('partner_details', ['partner_id' => $this->request->getPost('partner_id')]);
            if (isset($partner_details[0]['at_store']) && $partner_details[0]['at_store'] == 1) {
                $at_store = 1;
            }
            if (isset($partner_details[0]['at_doorstep']) && $partner_details[0]['at_doorstep'] == 1) {
                $at_doorstep = 1;
            }



            $where['s.user_id'] = $this->request->getPost('partner_id');
        }

        if ($this->request->getPost('category_id') && !empty($this->request->getPost('category_id'))) {
            $where['category_id'] = $this->request->getPost('category_id');
        }

        if (isset($this->user_details['id']) && $this->user_details['id']) {
            $additional_data = ['s.user_id' => $this->user_details['id']];
        }



        $data = $Service_model->list(true, $search, $limit, $offset, $sort, $order, $where, $additional_data, '', '', '', $at_store, $at_doorstep);


        if (isset($data['error'])) {
            return response($data['message']);
        }



        if (!empty($data['data'])) {
            return response('services fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
        } else {
            return response('services not found');
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    // 17. get_cities
    public function get_cities()
    {
        try {
            /*
        id:12                   {optional}
        name:bhuj               {optional}
        limit:10                {optional}
        offset:0                {optional}
        sort:id                 {optional}
        order:asc               {optional}
        search:test             {optional}
         */
            $City_model = new City_model();
            $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $where = [];
            if ($this->request->getPost('id')) {
                $where['id'] = $this->request->getPost('id');
            }
            if ($this->request->getPost('name')) {
                $where['name'] = $this->request->getPost('name');
            }
            $data = $City_model->list(true, $search, $limit, $offset, $sort, $order, $where, $this->user_details);
            if (!empty($data['data'])) {
                return response('cities fetched successfully', false, remove_null_values($data['data']), 200, ['total' => $data['total']]);
            } else {
                return response('cities not found');
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 18. is_city_deliverable
    public function is_city_deliverable()
    {
        /*
        city_id:7
        OR
        name:bhuj
         */
        try {
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'id' => 'permit_empty',
                    'name' => 'permit_empty',
                ],
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $where = [];
            if (empty($this->request->getPost('id')) && empty($this->request->getPost('name'))) {
                return response('required city id or name');
            }
            if ($this->request->getPost('id')) {
                $where['id'] = $this->request->getPost('id');
            }
            if ($this->request->getPost('name')) {
                $where['name'] = $this->request->getPost('name');
            }
            if (exists($where, 'cities')) {
                $city_id = fetch_details('cities', $where, 'id')[0]['id'];
                update_details(['city_id' => $city_id], ['id' => $this->user_details['id']], 'users');
                return response('city is deliverable', false, [], 200, ['city_id' => $city_id]);
            } else {
                return response('city is not deliverable', true, [], 200, ['city_id' => "0"]);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 19. manage_cart
    // 
    // 
    public function manage_cart()
    {
        /*
          service_id:12
          qty:1
          is_saved_for_later:1    {optional}
           */
        // try {
        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'service_id' => 'required|numeric',
                'qty' => 'required|numeric|greater_than[0]',
                'is_saved_for_later' => 'permit_empty|numeric',
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $service = fetch_details('services', ['id' => $this->request->getPost('service_id')], ['max_quantity_allowed']);
        if (empty($service)) {
            return response('service not found');
        }
        if ($service[0]['max_quantity_allowed'] < $this->request->getPost('qty')) {
            return response('max quanity allowed ' . $service[0]['max_quantity_allowed']);
        }
        $current_service_id = $this->request->getPost('service_id');
        $get_service_id = fetch_details('services', ['id' => $current_service_id]);
        $has_booked_before = fetch_details('cart', ['user_id' => $this->user_details['id']], ['id', 'service_id']);
        $cart_data = fetch_details('cart', ['service_id' => $this->request->getPost('service_id'), 'user_id' => $this->user_details['id']], ['id', 'is_saved_for_later']);
        if (exists(['service_id' => $this->request->getPost('service_id'), 'user_id' => $this->user_details['id']], 'cart')) {
            if (update_details(
                [
                    'qty' => $this->request->getPost('qty'),
                    'is_saved_for_later' => ($this->request->getPost('is_saved_for_later') == '') ? $cart_data[0]['is_saved_for_later']
                        : $this->request->getPost('is_saved_for_later'),
                ],
                ['service_id' => $this->request->getPost('service_id'), 'user_id' => $this->user_details['id']],
                'cart'
            )) {
                $error = false;
                $message = 'cart updated successfully';
                $user_id = $this->user_details['id'];
                // print_r($user_id);
                $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 0;
                $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
                $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
                $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
                $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
                $where = [];
                $cart_data = fetch_details('cart', ['user_id' => $user_id]);
                if (empty($cart_data)) {
                    return response('item not found');
                } else {
                    $cart_details = fetch_cart(true, $this->user_details['id'], $search, $limit, $offset, $sort, $order, $where);
                    if (!empty($cart_details['data'])) {
                        return response(
                            $message,
                            $error,
                            remove_null_values($cart_details['data']),
                            200,
                            remove_null_values(
                                [
                                    'provider_id' => $cart_details['provider_id'],
                                    'provider_names' => $cart_details['provider_names'],
                                    'service_ids' => $cart_details['service_ids'],
                                    'qtys' => $cart_details['qtys'],
                                    'visiting_charges' => $cart_details['visiting_charges'],
                                    'advance_booking_days' => $cart_details['advance_booking_days'],
                                    'company_name' => $cart_details['company_name'],
                                    'total_duration' => $cart_details['total_duration'],
                                    'is_pay_later_allowed' => $cart_details['is_pay_later_allowed'],
                                    'total_quantity' => $cart_details['total_quantity'],
                                    'sub_total' => $cart_details['sub_total'],
                                    // 'tax_amount' => $cart_details['taxable_amount'],
                                    'overall_amount' => $cart_details['overall_amount'],
                                    'total' => $cart_details['total'],
                                    "at_store" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_store'] : "0",

                                    "at_doorstep" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_doorstep'] : "0",
                                ]
                            )
                        );
                    } else {
                        return response('item not found');
                    }
                }
            } else {
                $error = true;
                $message = 'cart not updated';
                return response($message, $error);
            }
        } else {
            if (sizeof($has_booked_before) > 0) {
                $current_partner_id = $get_service_id[0]['user_id'];
                $pervious_service_id = $has_booked_before[0]['service_id'];
                // print_r($pervious_service_id);
                // die;
                $pervious_user_id = fetch_details('services', ['id' => $pervious_service_id], ['user_id']);
                if (empty($pervious_user_id)) {
                    $pervious_user_id = 0;
                } else {
                    $pervious_user_id = fetch_details('services', ['id' => $pervious_service_id], ['user_id'])[0]['user_id'];
                }
                if ($current_partner_id == $pervious_user_id) {
                    if (insert_details(['service_id' => $this->request->getPost('service_id'), 'qty' => $this->request->getPost('qty'), 'is_saved_for_later' => ($this->request->getPost('is_saved_for_later' != '')) ? $this->request->getPost('is_saved_for_later') : 0, 'user_id' => $this->user_details['id']], 'cart')) {
                        $error = false;
                        $message = 'cart added successfully';
                        $user_id = $this->user_details['id'];
                        // print_r($user_id);
                        $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 0;
                        $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
                        $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
                        $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
                        $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
                        $where = [];
                        $cart_data = fetch_details('cart', ['user_id' => $user_id]);
                        if (empty($cart_data)) {
                            return response('item not found');
                        } else {
                            $cart_details = fetch_cart(true, $this->user_details['id'], $search, $limit, $offset, $sort, $order, $where);
                            if (!empty($cart_details['data'])) {
                                return response(
                                    $message,
                                    $error,
                                    remove_null_values($cart_details['data']),
                                    200,
                                    remove_null_values(
                                        [
                                            'provider_id' => $cart_details['provider_id'],
                                            'provider_names' => $cart_details['provider_names'],
                                            'service_ids' => $cart_details['service_ids'],
                                            'qtys' => $cart_details['qtys'],
                                            'visiting_charges' => $cart_details['visiting_charges'],
                                            'advance_booking_days' => $cart_details['advance_booking_days'],
                                            'company_name' => $cart_details['company_name'],
                                            'total_duration' => $cart_details['total_duration'],
                                            'is_pay_later_allowed' => $cart_details['is_pay_later_allowed'],
                                            'total_quantity' => $cart_details['total_quantity'],
                                            'sub_total' => $cart_details['sub_total'],
                                            // 'tax_amount' => $cart_details['taxable_amount'],
                                            'overall_amount' => $cart_details['overall_amount'],
                                            'total' => $cart_details['total'],
                                            "at_store" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_store'] : "0",
                                            "at_doorstep" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_doorstep'] : "0",
                                        ]
                                    )
                                );
                            } else {
                                return response('item not found');
                            }
                        }
                        // return response($message, $error);
                    } else {
                        $error = true;
                        $message = 'cart not added';
                        return response($message, $error);
                    }
                } else {
                    $user_id = $this->user_details['id'];
                    delete_details(['user_id' => $user_id], 'cart');
                    insert_details(['service_id' => $this->request->getPost('service_id'), 'qty' => $this->request->getPost('qty'), 'is_saved_for_later' => ($this->request->getPost('is_saved_for_later' != '')) ? $this->request->getPost('is_saved_for_later') : 0, 'user_id' => $this->user_details['id']], 'cart');
                    $cart_details = fetch_cart(true, $this->user_details['id'], '', 10, 0, '', '', '');
                    // print_r($cart_details);
                    $error = false;
                    // print_r($cart_details);
                    $message = 'cart added successfully';
                    if (!empty($cart_details['data'])) {
                        return response(
                            $message,
                            $error,
                            remove_null_values($cart_details['data']),
                            200,
                            remove_null_values(
                                [
                                    'provider_id' => $cart_details['provider_id'],
                                    'provider_names' => $cart_details['provider_names'],
                                    'service_ids' => $cart_details['service_ids'],
                                    'qtys' => $cart_details['qtys'],
                                    'visiting_charges' => $cart_details['visiting_charges'],
                                    'advance_booking_days' => $cart_details['advance_booking_days'],
                                    'company_name' => $cart_details['company_name'],
                                    'total_duration' => $cart_details['total_duration'],
                                    'is_pay_later_allowed' => $cart_details['is_pay_later_allowed'],
                                    'total_quantity' => $cart_details['total_quantity'],
                                    'sub_total' => $cart_details['sub_total'],
                                    // 'tax_percentage' => $cart_details['tax_percentage'],
                                    // 'tax_amount' => $cart_details['taxable_amount'],
                                    'overall_amount' => $cart_details['overall_amount'],
                                    'total' => $cart_details['total'],
                                    "at_store" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_store'] : "0",

                                    "at_doorstep" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_doorstep'] : "0",
                                ]
                            )
                        );
                    } else {
                        return response('item not found');
                    }
                }
            } else {
                if (insert_details(
                    [
                        'service_id' => $this->request->getPost('service_id'),
                        'qty' => $this->request->getPost('qty'),
                        'is_saved_for_later' => ($this->request->getPost('is_saved_for_later') != '') ? $this->request->getPost('is_saved_for_later') : '0', 'user_id' => $this->user_details['id'],
                    ],
                    'cart'
                )) {
                    $error = false;
                    $message = 'cart added successfully';
                    $user_id = $this->user_details['id'];
                    // print_r($user_id);
                    $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
                    $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
                    $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
                    $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
                    $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
                    $where = [];
                    $cart_data = fetch_details('cart', ['user_id' => $user_id]);
                    // die();
                    if (empty($cart_data)) {
                        return response('item not found');
                    } else {
                        $cart_details = fetch_cart(true, $this->user_details['id'], $search, $limit, $offset, $sort, $order, $where);
                        if (!empty($cart_details['data'])) {
                            return response(
                                $message,
                                $error,
                                remove_null_values($cart_details['data']),
                                200,
                                remove_null_values(
                                    [
                                        'provider_id' => $cart_details['provider_id'],
                                        'provider_names' => $cart_details['provider_names'],
                                        'service_ids' => $cart_details['service_ids'],
                                        'qtys' => $cart_details['qtys'],
                                        'visiting_charges' => $cart_details['visiting_charges'],
                                        'advance_booking_days' => $cart_details['advance_booking_days'],
                                        'company_name' => $cart_details['company_name'],
                                        'total_duration' => $cart_details['total_duration'],
                                        'is_pay_later_allowed' => $cart_details['is_pay_later_allowed'],
                                        'total_quantity' => $cart_details['total_quantity'],
                                        'sub_total' => $cart_details['sub_total'],
                                        // 'tax_percentage' => $cart_details['tax_percentage'],
                                        // 'tax_amount' => $cart_details['taxable_amount'],
                                        'overall_amount' => $cart_details['overall_amount'],
                                        'total' => $cart_details['total'],
                                        "at_store" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_store'] : "0",

                                        "at_doorstep" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_doorstep'] : "0",
                                    ]
                                )
                            );
                        } else {
                            return response('item not found');
                        }
                    }
                    // return response($message, $error);
                } else {
                    $error = true;
                    $message = 'cart not added';
                    return response($message, $error);
                }
            }
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    // 20. remove_from_cart
    public function remove_from_cart()
    {
        // try {
        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'cart_id' => 'permit_empty',
                'service_id' => 'permit_empty|numeric',
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $tax = get_settings('system_tax_settings', true)['tax'];
        $db = \Config\Database::connect();
        if (empty($this->request->getPost('cart_id')) && empty($this->request->getPost('service_id'))) {
            return response('required cart id or service id');
        }
        if (!empty($this->request->getPost('cart_id'))) {
            if (!exists(['id' => $this->request->getPost('cart_id'), 'user_id' => $this->user_details['id']], 'cart')) {
                return response('cart id not exist in cart');
            }
            if (delete_details(['id' => $this->request->getPost('cart_id')], 'cart')) {
                $error = false;
                $message = 'service removed from cart';
                $user_id = $this->user_details['id'];
                $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 0;
                $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
                $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
                $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
                $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
                $where = [];
                $cart_data = fetch_details('cart', ['user_id' => $user_id]);
                if (empty($cart_data)) {
                    return response($message, $error);
                } else {
                    $cart_details = fetch_cart(true, $this->user_details['id'], $search, $limit, $offset, $sort, $order, $where);
                    if (!empty($cart_details['data'])) {
                        return response(
                            $message,
                            $error,
                            remove_null_values($cart_details['data']),
                            200,
                            remove_null_values(
                                [
                                    'provider_id' => $cart_details['provider_id'],
                                    'provider_names' => $cart_details['provider_names'],
                                    'service_ids' => $cart_details['service_ids'],
                                    'qtys' => $cart_details['qtys'],
                                    'visiting_charges' => $cart_details['visiting_charges'],
                                    'advance_booking_days' => $cart_details['advance_booking_days'],
                                    'company_name' => $cart_details['company_name'],
                                    'total_duration' => $cart_details['total_duration'],
                                    'is_pay_later_allowed' => $cart_details['is_pay_later_allowed'],
                                    'total_quantity' => $cart_details['total_quantity'],
                                    'sub_total' => $cart_details['sub_total'],
                                    // 'tax_percentage' => $cart_details['tax_percentage'],
                                    // 'tax_amount' => $cart_details['taxable_amount'],
                                    'overall_amount' => $cart_details['overall_amount'],
                                    'total' => $cart_details['total'],
                                    "at_store" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_store'] : "0",
                                    "at_doorstep" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_doorstep'] : "0",

                                ]
                            )
                        );
                    } else {
                        return response('item not found');
                    }
                }
            } else {
                $error = true;
                $message = 'service not removed from cart';
                return response($message, $error);
            }
        } else {
            if (!exists(['service_id' => $this->request->getPost('service_id'), 'user_id' => $this->user_details['id']], 'cart')) {
                return response('service not exist in cart');
            }
            if (delete_details(['service_id' => $this->request->getPost('service_id')], 'cart')) {
                $error = false;
                $message = 'service removed from cart';
                $user_id = $this->user_details['id'];
                $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 0;
                $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
                $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
                $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
                $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
                $where = [];
                $cart_data = fetch_details('cart', ['user_id' => $user_id]);
                if (empty($cart_data)) {
                    return response($message, $error);
                } else {
                    $cart_details = fetch_cart(true, $this->user_details['id'], $search, $limit, $offset, $sort, $order, $where);
                    if (!empty($cart_details['data'])) {
                        return response(
                            $message,
                            $error,
                            remove_null_values($cart_details['data']),
                            200,
                            remove_null_values(
                                [
                                    'provider_id' => $cart_details['provider_id'],
                                    'provider_names' => $cart_details['provider_names'],
                                    'service_ids' => $cart_details['service_ids'],
                                    'qtys' => $cart_details['qtys'],
                                    'visiting_charges' => $cart_details['visiting_charges'],
                                    'advance_booking_days' => $cart_details['advance_booking_days'],
                                    'company_name' => $cart_details['company_name'],
                                    'total_duration' => $cart_details['total_duration'],
                                    'is_pay_later_allowed' => $cart_details['is_pay_later_allowed'],
                                    'total_quantity' => $cart_details['total_quantity'],
                                    'sub_total' => $cart_details['sub_total'],
                                    // 'tax_percentage' => $cart_details['tax_percentage'],
                                    // 'tax_amount' => $cart_details['taxable_amount'],
                                    'overall_amount' => $cart_details['overall_amount'],
                                    'total' => $cart_details['total'],
                                    "at_store" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_store'] : "0",
                                    "at_doorstep" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_doorstep'] : "0",
                                ]
                            )
                        );
                    } else {
                        return response('item not found');
                    }
                }
            } else {
                $error = true;
                $message = 'service not removed from cart';
                return response($message, $error);
            }
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
        /*
        cart_id:1       {optional}
        service_id:12   {optional}
         */
    }
    // 21. get_cart
    public function get_cart()
    {
        // try {
        $user_id = $this->user_details['id'];
        $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 0;
        $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
        $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
        $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
        $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
        $where = [];
        $cart_data = fetch_details('cart', ['user_id' => $user_id]);


        $reorder_details = fetch_cart(true, $this->user_details['id'], $search, $limit, $offset, $sort, $order, $where, null, 'yes', $this->request->getPost('order_id'));


        if (empty($cart_data) && empty($reorder_details)) {
            return response('item not found');
        } else {
            $cart_details = fetch_cart(true, $this->user_details['id'], $search, $limit, $offset, $sort, $order, $where, []);

            if (!empty($this->request->getPost('order_id'))) {
                $reorder_details = fetch_cart(true, $this->user_details['id'], $search, $limit, $offset, $sort, $order, $where, null, 'yes', $this->request->getPost('order_id'));
                if (empty($reorder_details)) {
                    $response['error'] = true;
                    $response['message'] = 'order not found';
                    return $this->response->setJSON($response);
                }
            }


            $data = array();
            $data['cart_data'] = [
                "data" => (!empty($cart_details) && isset($cart_details)) ? remove_null_values($cart_details['data']) : "",
                "provider_id" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['provider_id'] : "",
                "provider_names" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['provider_names'] : "",
                "service_ids" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['service_ids'] : "",
                "qtys" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['qtys'] : "",
                "visiting_charges" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['visiting_charges'] : "",
                "advance_booking_days" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['advance_booking_days'] : "",
                "company_name" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['company_name'] : "",
                "total_duration" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['total_duration'] : "",
                "is_pay_later_allowed" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['is_pay_later_allowed'] : "",
                "total_quantity" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['total_quantity'] : "",
                "sub_total" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['sub_total'] : "",
                // "tax_percentage" => $cart_details['tax_percentage'],
                // "tax_amount" => $cart_details['taxable_amount'],
                "overall_amount" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['overall_amount'] : "",
                "total" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['total'] : "",
                "at_store" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_store'] : "0",

                "at_doorstep" => (!empty($cart_details) && isset($cart_details)) ? $cart_details['at_doorstep'] : "0",

            ];

            $data['reorder_data'] = [
                "data" => (!empty($reorder_details) && isset($reorder_details)) ? remove_null_values($reorder_details['data']) : "",
                "provider_id" => (!empty($reorder_details) && isset($reorder_details)) ? $reorder_details['provider_id'] : "",
                "provider_names" => (!empty($reorder_details) && isset($reorder_details)) ? $reorder_details['provider_names'] : "",
                "service_ids" => (!empty($reorder_details) && isset($reorder_details)) ? $reorder_details['service_ids'] : "",
                "qtys" => (!empty($reorder_details) && isset($reorder_details)) ? $reorder_details['qtys'] : "",
                "visiting_charges" => (!empty($reorder_details) && isset($reorder_details)) ? $reorder_details['visiting_charges'] : "",
                "advance_booking_days" => (!empty($reorder_details) && isset($reorder_details)) ? $reorder_details['advance_booking_days'] : "",
                "company_name" => (!empty($reorder_details) && isset($reorder_details)) ? $reorder_details['company_name'] : "",
                "total_duration" => (!empty($reorder_details) && isset($reorder_details)) ? $reorder_details['total_duration'] : "",
                "is_pay_later_allowed" => (!empty($reorder_details) && isset($reorder_details)) ? $reorder_details['is_pay_later_allowed'] : "",
                "total_quantity" => (!empty($reorder_details) && isset($reorder_details)) ? $reorder_details['total_quantity'] : "",
                "sub_total" => (!empty($reorder_details) && isset($reorder_details)) ? $reorder_details['sub_total'] : "",
                // "tax_percentage" => $cart_details['tax_percentage'],
                // "tax_amount" => $cart_details['taxable_amount'],
                "overall_amount" => (!empty($reorder_details) && isset($reorder_details)) ? $reorder_details['overall_amount'] : "",
                "total" => (!empty($reorder_details) && isset($reorder_details)) ? $reorder_details['total'] : "",
                "at_store" => (!empty($reorder_details) && isset($reorder_details)) ? $reorder_details['at_store'] : "0",
                "at_doorstep" => (!empty($reorder_details) && isset($reorder_details)) ? $reorder_details['at_doorstep'] : "0",
            ];
            return response(
                'cart fetched successfully',
                false,
                $data,
                200,

            );
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
        /*
        limit:10                {optional}
        offset:0                {optional}
        sort:id                 {optional}
        order:asc               {optional}
        search:test             {optional}
         */
    }
    // 22. place_order


    //currently working - 16 January 2024

    // public function place_order()
    // {


    //     $validation = \Config\Services::validation();
    //     $rules = [
    //         'promo_code' => 'permit_empty',
    //         'payment_method' => 'required',
    //         'status' => 'required',
    //         'date_of_service' => 'required|valid_date[Y-m-d]',
    //         'starting_time' => 'required',
    //     ];

    //     $at_store = $this->request->getVar('at_store');
    //     if ($at_store == 1) {
    //         $rules['address_id'] = 'permit_empty|numeric';
    //     } else {
    //         $rules['address_id'] = 'required|numeric';
    //     }

    //     $validation->setRules($rules);

    //     if (!$validation->withRequest($this->request)->run()) {
    //         $errors = $validation->getErrors();
    //         $response = [
    //             'error' => true,
    //             'message' => $errors,
    //             'data' => ['type' => 'neworder'],
    //         ];
    //         return $this->response->setJSON($response);
    //     }

    //     if (empty($this->request->getVar('order_id'))) {

    //         $cart_data = fetch_cart(true, $this->user_details['id']);

    //         // print_R($cart_data);
    //         // die;
    //     }

    //     if (empty($this->request->getVar('order_id'))) {

    //         if (empty($cart_data)) {
    //             return response("Please add some item in cart", true);
    //         }
    //     }

    //     $db = \Config\Database::connect();

    //     if ((empty($this->request->getVar('order_id')))) {

    //         $service_ids = $cart_data['service_ids'];
    //         $quantity = $cart_data['qtys'];
    //         $total = $cart_data['sub_total'];
    //     } else {
    //         $order = fetch_details('order_services', ['order_id' => $this->request->getPost('order_id')]);
    //         $service_ids = [];
    //         foreach ($order as $row) {
    //             $service_ids[] = $row['service_id'];
    //         }
    //         $all_service_data = array();
    //         foreach ($service_ids as $row2) {
    //             $service_data_array = fetch_details('services', ['id' => $row2]);
    //             $service_data = $service_data_array[0];
    //             $all_service_data[] = $service_data;
    //         }
    //         $quantities = [];
    //         foreach ($order as $row) {
    //             $quantities[] = $row['quantity'];
    //         }
    //         $quantity = implode(',', $quantities);
    //         $total = 0;
    //         $tax_value = 0;
    //         $sub_total = 0;
    //         $duartion = 0;


    //         $builder = $db->table('order_services os');
    //         $service_record = $builder
    //             ->select('os.id as order_service_id,os.service_id,os.quantity,s.*,s.title as service_name,p.username as partner_name,pd.visiting_charges as visiting_charges,cat.name as category_name')
    //             ->join('services s', 'os.service_id=s.id', 'left')
    //             ->join('users p', 'p.id=s.user_id', 'left')
    //             ->join('categories cat', 'cat.id=s.category_id', 'left')
    //             ->join('partner_details pd', 'pd.partner_id=s.user_id', 'left')
    //             ->where('os.order_id',  $this->request->getPost('order_id'))->get()->getResultArray();

    //         foreach ($service_record as $s1) {
    //             // print_R($s1);
    //             $taxPercentageData = fetch_details('taxes', ['id' => $s1['tax_id']], ['percentage']);
    //             if (!empty($taxPercentageData)) {
    //                 $taxPercentage = $taxPercentageData[0]['percentage'];
    //             } else {
    //                 $taxPercentage = 0;
    //             }
    //             if ($s1['discounted_price'] == "0") {
    //                 $tax_value = ($s1['tax_type'] == "excluded") ? number_format(((($s1['price'] * ($taxPercentage) / 100))), 2) : 0;
    //                 $price = number_format($s1['price'], 2);
    //             } else {
    //                 $tax_value = ($s1['tax_type'] == "excluded") ? number_format(((($s1['discounted_price'] * ($taxPercentage) / 100))), 2) : 0;
    //                 $price = number_format($s1['discounted_price'], 2);
    //             }
    //             $sub_total = $sub_total + (floatval(str_replace(",", "", $price)) + $tax_value) * $s1['quantity'];

    //             $duartion = $duartion + $s1['duration'] * $s1['quantity'];
    //         }

    //         $total = $sub_total;
    //     }







    //     if ($at_store == "1") {
    //         $visiting_charges = 0;
    //     } else {

    //         if (empty($this->request->getPost('order_id'))) {

    //             $visiting_charges = $cart_data['visiting_charges'];
    //         } else {
    //             $builder = $db->table('services s');
    //             $extra_data = $builder
    //                 ->select('SUM(IF(s.discounted_price  > 0 , (s.discounted_price * os1.quantity) , (s.price *  os1.quantity))) as subtotal,
    //             SUM( os1.quantity) as total_quantity,pd.visiting_charges as visiting_charges,SUM(s.duration *  os1.quantity) as total_duration,pd.advance_booking_days as advance_booking_days,
    //             pd.company_name as company_name')
    //                 ->join('order_services os1', 'os1.service_id = s.id')
    //                 ->join('partner_details pd', 'pd.partner_id=s.user_id')
    //                 ->where('os1.order_id',  $this->request->getPost('order_id'))
    //                 ->whereIn('s.id', $service_ids)->get()->getResultArray();
    //             $visiting_charges = $extra_data[0]['visiting_charges'];
    //         }
    //     }



    //     $promo_code = $this->request->getVar('promo_code');
    //     $payment_method = $this->request->getVar('payment_method');
    //     $address_id = ($at_store == 1) ? 0 : $this->request->getVar('address_id');
    //     $status = strtolower($this->request->getVar('status'));
    //     $date_of_service = $this->request->getVar('date_of_service');

    //     $starting_time = ($this->request->getVar('starting_time'));


    //     $order_note = ($this->request->getVar('order_note')) ? $this->request->getVar('order_note') : "";


    //     if (empty($this->request->getPost('order_id'))) {

    //         $minutes = strtotime($starting_time) + ($cart_data['total_duration'] * 60);
    //     } else {
    //         $minutes = strtotime($starting_time) + ($duartion * 60);
    //     }

    //     $ending_time = date('H:i:s', $minutes);

    //     if ($at_store != 1) {

    //         if (!exists(['id' => $address_id], 'addresses')) {
    //             return response('Address not exist');
    //         }
    //     }

    //     // $final_total = intval($total) + intval($cart_data['taxable_amount'])+intval($visiting_charges);
    //     $final_total = ceil($total) + ceil($visiting_charges);




    //     if (empty($this->request->getPost('order_id'))) {

    //         $ids = explode(',', $service_ids ?? '');
    //     } else {

    //         $ids = $service_ids;
    //     }

    //     $qtys = explode(',', $quantity ?? '');
    //     $service_data = fetch_details('services', [], '', '', '', '', '', 'id', $ids);
    //     $partner_id = $service_data[0]['user_id'];
    //     // $availability = check_availability($partner_id, $date_of_service, $starting_time);
    //     $current_date = date('Y-m-d');
    //     $service_total_duration = 0;
    //     $service_duration = 0;


    //     if (empty($this->request->getPost('order_id'))) {
    //         foreach ($cart_data['data'] as $main_data) {

    //             $service_duration = ($main_data['servic_details']['duration']) * $main_data['qty'];
    //             $service_total_duration = $service_total_duration + $service_duration;
    //         }
    //     } else {

    //         $service_total_duration = $duartion;
    //     }




    //     $availability =  checkPartnerAvailability($partner_id, $date_of_service . ' ' . $starting_time, $service_total_duration, $date_of_service, $starting_time);
    //     $insert_order = "";
    //     if (isset($availability) && $availability['error'] == "0") {

    //         $location_data = fetch_details('addresses', ['id' => $address_id]);
    //         $address['mobile'] = isset($location_data) && !empty($location_data) ? $location_data[0]['mobile'] : '';
    //         $address['address'] = isset($location_data) && !empty($location_data) ? $location_data[0]['address'] : '';
    //         $address['area'] = isset($location_data) && !empty($location_data) ? $location_data[0]['area'] : '';
    //         $address['city'] = isset($location_data) && !empty($location_data) ? $location_data[0]['city'] : '';
    //         $address['state'] = isset($location_data) && !empty($location_data) ? $location_data[0]['state'] : '';
    //         $address['country'] = isset($location_data) && !empty($location_data) ? $location_data[0]['country'] : '';
    //         $address['pincode'] = isset($location_data) && !empty($location_data) ? $location_data[0]['pincode'] : '';
    //         $city_id = isset($location_data) && !empty($location_data) ? $location_data[0]['city'] : '';


    //         $outputArray = array(
    //             $address['address'],
    //             $address['area'],
    //             $address['city'],
    //             $address['state'],
    //             $address['country'],
    //             $address['pincode'],
    //             $address['mobile']
    //         );

    //         $finaladdress = implode(',', $outputArray);






    //         $service_total_duration = 0;
    //         $service_duration = 0;




    //         if (empty($this->request->getPost('order_id'))) {
    //             foreach ($cart_data['data'] as $main_data) {
    //                 $service_duration = ($main_data['servic_details']['duration']) * $main_data['qty'];
    //                 $service_total_duration = $service_total_duration + $service_duration;
    //             }
    //         } else {
    //             $service_total_duration = $duartion;
    //         }


    //         // $time_slots = get_available_slots($partner_id, $date_of_service, isset($service_total_duration) ? $service_total_duration : 0, $starting_time); //working
    //         $time_slots = get_slot_for_place_order($partner_id, $date_of_service, $service_total_duration, $starting_time);
    //         $timestamp = date('Y-m-d h:i:s '); // Example timestamp format: 2023-08-08 03:30:00 PM


    //         if ($time_slots['slot_avaialble']) {

    //             $duration_minutes = $service_total_duration;

    //             if ($time_slots['suborder']) {

    //                 $end_minutes = strtotime($starting_time) + ((sizeof($time_slots['order_data']) * 30) * 60);
    //                 $ending_time = date('H:i:s', $end_minutes);


    //                 $day = date('l', strtotime($date_of_service));
    //                 $timings = getTimingOfDay($partner_id, $day);
    //                 $closing_time = $timings['closing_time']; // Replace with the actual closing time
    //                 if ($ending_time > $closing_time) {
    //                     $ending_time = $closing_time;
    //                 }

    //                 $start_timestamp = strtotime($starting_time);
    //                 $ending_timestamp = strtotime($ending_time);
    //                 $duration_seconds = $ending_timestamp - $start_timestamp;
    //                 $duration_minutes = $duration_seconds / 60;
    //             }

    //             $order = [
    //                 'partner_id' => $partner_id,
    //                 'user_id' => $this->user_details['id'],
    //                 'city' => $city_id,
    //                 'total' => $total,
    //                 'payment_method' => $payment_method,
    //                 'address_id' => isset($address_id) ? $address_id : "0",
    //                 'visiting_charges' => $visiting_charges,
    //                 'address' => isset($finaladdress) ? $finaladdress : "",
    //                 'date_of_service' => $date_of_service,
    //                 'starting_time' => $starting_time,
    //                 'ending_time' => $ending_time,
    //                 'duration' => $duration_minutes,
    //                 'status' => $status,
    //                 'remarks' => $order_note,
    //                 'otp' => random_int(100000, 999999),
    //                 'order_latitude' => $this->user_details['latitude'],
    //                 'order_longitude' => $this->user_details['longitude'],
    //                 'created_at' => $timestamp,
    //             ];





    //             if (!empty($promo_code)) {


    //                 $fetch_promococde = fetch_details('promo_codes', ['promo_code' => $promo_code]);
    //                 $promo_code = validate_promo_code($this->user_details['id'], $fetch_promococde[0]['id'], $final_total);

    //                 if ($promo_code['error']) {


    //                     return $response['message'] = ($promo_code['message']);
    //                 }
    //                 //add dicounted final total
    //                 $final_total = $promo_code['data'][0]['final_total'];
    //                 $order['promo_code'] = $promo_code['data'][0]['promo_code'];
    //                 $order['promo_discount'] = $promo_code['data'][0]['final_discount'];
    //                 $order['promocode_id'] = $fetch_promococde[0]['id'];
    //             }
    //             // if (!empty($promo_code)) {
    //             //     $promo_code = validate_promo_code($this->user_details['id'], $promo_code, $final_total);

    //             //     if ($promo_code['error']) {


    //             //         return $response['message'] = ($promo_code['message']);
    //             //     }
    //             //     //add dicounted final total
    //             //     $final_total = $promo_code['data'][0]['final_total'];
    //             //     $order['promo_code'] = $promo_code['data'][0]['promo_code'];
    //             //     $order['promo_discount'] = $promo_code['data'][0]['final_discount'];
    //             // }


    //             $order['final_total'] = $final_total;
    //             $insert_order = insert_details($order, 'orders');
    //         }

    //         if ($time_slots['suborder']) {






    //             $next_day_date = date('Y-m-d', strtotime($date_of_service . ' +1 day'));
    //             $next_day_slots = get_next_days_slots($closing_time, $date_of_service, $partner_id, $service_total_duration, $current_date);

    //             // if(!empty($next_day_available_slots)){
    //             $next_day_available_slots = $next_day_slots['available_slots'];

    //             $next_Day_minutes = strtotime($next_day_available_slots[0]) + (($service_total_duration - $duration_minutes) * 60);
    //             $next_day_ending_time = date('H:i:s', $next_Day_minutes);

    //             $next_day_ending_time = date('H:i:s', $next_Day_minutes);



    //             $sub_order = [
    //                 'partner_id' => $partner_id,
    //                 'user_id' => $this->user_details['id'],
    //                 'city' => $city_id,
    //                 'total' => $total,
    //                 'payment_method' => $payment_method,
    //                 'address_id' => isset($address_id) ? $address_id : "",
    //                 'visiting_charges' => $visiting_charges,
    //                 'address' => isset($finaladdress) ? $finaladdress : "",
    //                 'date_of_service' =>   $next_day_date,
    //                 'starting_time' => isset($next_day_available_slots[0]) ? $next_day_available_slots[0] : 00,
    //                 'ending_time' => $next_day_ending_time,
    //                 'duration' => $service_total_duration - $duration_minutes,
    //                 'status' => $status,
    //                 'remarks' => "sub_order",
    //                 'otp' => random_int(100000, 999999),
    //                 'parent_id' => $insert_order['id'],
    //                 'order_latitude' => $this->user_details['latitude'],
    //                 'order_longitude' => $this->user_details['longitude'],
    //                 'created_at' => $timestamp,
    //             ];
    //             if (!empty($this->request->getVar('promo_code'))) {

    //                 $fetch_promococde = fetch_details('promo_codes', ['promo_code' => $this->request->getVar('promo_code')]);
    //                 $promo_code = validate_promo_code($this->user_details['id'], $fetch_promococde[0]['id'], $final_total);





    //                 // die;
    //                 if ($promo_code['error']) {
    //                     return $response['message'] = ($promo_code['message']);
    //                 }
    //                 //add dicounted final total
    //                 $final_total = $promo_code['data'][0]['final_total'];
    //                 $sub_order['promo_code'] = $promo_code['data'][0]['promo_code'];
    //                 $sub_order['promo_discount'] = $promo_code['data'][0]['final_discount'];
    //             }
    //             $sub_order['final_total'] = $final_total;
    //             $sub_order = insert_details($sub_order, 'orders');
    //         }


    //         if ($insert_order) {



    //             for ($i = 0; $i < count($ids); $i++) {



    //                 $service_details = get_taxable_amount($ids[$i]);
    //                 $data = [
    //                     'order_id' => $insert_order['id'],
    //                     'service_id' => $ids[$i],
    //                     'service_title' => $service_details['title'],
    //                     'tax_percentage' => $service_details['tax_percentage'],
    //                     'tax_amount' => number_format(($service_details['tax_amount']), 2),
    //                     // 'tax_amount' => intval($service_details['tax_amount']) * $qtys[$i],
    //                     'price' => $service_details['price'],
    //                     'discount_price' => $service_details['discounted_price'],
    //                     'quantity' => $qtys[$i],


    //                     'sub_total' =>  strval(str_replace(',', '', number_format(strval(($service_details['taxable_amount'] * ($qtys[$i]))), 2))),
    //                     'status' => $status,
    //                 ];

    //                 insert_details($data, 'order_services');


    //                 $orderId['order_id'] = $insert_order['id'];
    //                 $orderId['paypal_link'] = ($payment_method == "paypal") ? base_url() . '/api/v1/paypal_transaction_webview?user_id=' . $this->user_details['id'] . '&order_id=' . $insert_order['id'] . '&amount=' . ceil(number_format(strval($final_total), 2)) . '' : "";
    //             }


    //             if ($payment_method == 'cod') {
    //                 send_web_notification('New Order', 'Please check new order ' . $insert_order['id'], $partner_id);

    //                 //for app notification
    //                 $db      = \Config\Database::connect();
    //                 $to_send_id = $partner_id;
    //                 $builder = $db->table('users')->select('fcm_id,email,username,platform');
    //                 $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
    //                 foreach ($users_fcm as $ids) {
    //                     if ($ids['fcm_id'] != "") {
    //                         $fcm_ids['fcm_id'] = $ids['fcm_id'];
    //                         $fcm_ids['platform'] = $ids['platform'];

    //                         $email = $ids['email'];
    //                     }
    //                 }
    //                 if (!empty($fcm_ids)) {
    //                     $registrationIDs = $fcm_ids;
    //                     // $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
    //                     $registrationIDs_chunks = array_chunk($users_fcm, 1000);
    //                     $fcmMsg = array(
    //                         'content_available' => true,
    //                         'title' => " New Order Notification",
    //                         'body' => "We are pleased to inform you that you have received a new order. ",
    //                         'type' => 'order',
    //                         'order_id' => $insert_order['id'],
    //                         'type_id' => $to_send_id,
    //                         'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
    //                     );
    //                     send_notification($fcmMsg, $registrationIDs_chunks);
    //                 }
    //             }

    //             $this->checkAndUpdateSubscriptionStatus($partner_id);

    //             return response('Order Placed successfully', false, remove_null_values($orderId));
    //         } else {
    //             return response('order not placed');
    //         }
    //     } else {

    //         return response($availability['message'], true);
    //     }



    //     // end







    // }


    public function place_order()
    {



        $validation = \Config\Services::validation();
        $rules = [
            'promo_code' => 'permit_empty',
            'payment_method' => 'required',
            'status' => 'required',
            'date_of_service' => 'required|valid_date[Y-m-d]',
            'starting_time' => 'required',
        ];

        $at_store = $this->request->getVar('at_store');
        if ($at_store == 1) {
            $rules['address_id'] = 'permit_empty|numeric';
        } else {
            $rules['address_id'] = 'required|numeric';
        }

        $validation->setRules($rules);

        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => ['type' => 'neworder'],
            ];
            return $this->response->setJSON($response);
        }

        if (empty($this->request->getVar('order_id'))) {

            $cart_data = fetch_cart(true, $this->user_details['id']);

            // print_R($cart_data);
            // die;
        }

        if (empty($this->request->getVar('order_id'))) {

            if (empty($cart_data)) {
                return response("Please add some item in cart", true);
            }
        }

        $db = \Config\Database::connect();

        if ((empty($this->request->getVar('order_id')))) {

            $service_ids = $cart_data['service_ids'];
            $quantity = $cart_data['qtys'];
            $total = $cart_data['sub_total'];
        } else {
            $order = fetch_details('order_services', ['order_id' => $this->request->getPost('order_id')]);
            $service_ids = [];
            foreach ($order as $row) {
                $service_ids[] = $row['service_id'];
            }
            $all_service_data = array();
            foreach ($service_ids as $row2) {
                $service_data_array = fetch_details('services', ['id' => $row2]);
                $service_data = $service_data_array[0];
                $all_service_data[] = $service_data;
            }
            $quantities = [];
            foreach ($order as $row) {
                $quantities[] = $row['quantity'];
            }
            $quantity = implode(',', $quantities);
            $total = 0;
            $tax_value = 0;
            $sub_total = 0;
            $duartion = 0;


            $builder = $db->table('order_services os');
            $service_record = $builder
                ->select('os.id as order_service_id,os.service_id,os.quantity,s.*,s.title as service_name,p.username as partner_name,pd.visiting_charges as visiting_charges,cat.name as category_name')
                ->join('services s', 'os.service_id=s.id', 'left')
                ->join('users p', 'p.id=s.user_id', 'left')
                ->join('categories cat', 'cat.id=s.category_id', 'left')
                ->join('partner_details pd', 'pd.partner_id=s.user_id', 'left')
                ->where('os.order_id',  $this->request->getPost('order_id'))->get()->getResultArray();

            foreach ($service_record as $s1) {
                // print_R($s1);
                $taxPercentageData = fetch_details('taxes', ['id' => $s1['tax_id']], ['percentage']);
                if (!empty($taxPercentageData)) {
                    $taxPercentage = $taxPercentageData[0]['percentage'];
                } else {
                    $taxPercentage = 0;
                }
                if ($s1['discounted_price'] == "0") {
                    $tax_value = ($s1['tax_type'] == "excluded") ? number_format(((($s1['price'] * ($taxPercentage) / 100))), 2) : 0;
                    $price = number_format($s1['price'], 2);
                } else {
                    $tax_value = ($s1['tax_type'] == "excluded") ? number_format(((($s1['discounted_price'] * ($taxPercentage) / 100))), 2) : 0;
                    $price = number_format($s1['discounted_price'], 2);
                }
                $sub_total = $sub_total + (floatval(str_replace(",", "", $price)) + $tax_value) * $s1['quantity'];

                $duartion = $duartion + $s1['duration'] * $s1['quantity'];
            }

            $total = $sub_total;
        }







        if ($at_store == "1") {
            $visiting_charges = 0;
        } else {

            if (empty($this->request->getPost('order_id'))) {

                $visiting_charges = $cart_data['visiting_charges'];
            } else {
                $builder = $db->table('services s');
                $extra_data = $builder
                    ->select('SUM(IF(s.discounted_price  > 0 , (s.discounted_price * os1.quantity) , (s.price *  os1.quantity))) as subtotal,
                SUM( os1.quantity) as total_quantity,pd.visiting_charges as visiting_charges,SUM(s.duration *  os1.quantity) as total_duration,pd.advance_booking_days as advance_booking_days,
                pd.company_name as company_name')
                    ->join('order_services os1', 'os1.service_id = s.id')
                    ->join('partner_details pd', 'pd.partner_id=s.user_id')
                    ->where('os1.order_id',  $this->request->getPost('order_id'))
                    ->whereIn('s.id', $service_ids)->get()->getResultArray();
                $visiting_charges = $extra_data[0]['visiting_charges'];
            }
        }



        $promo_code = $this->request->getVar('promo_code');
        $payment_method = $this->request->getVar('payment_method');
        $address_id = ($at_store == 1) ? 0 : $this->request->getVar('address_id');
        $status = strtolower($this->request->getVar('status'));
        $date_of_service = $this->request->getVar('date_of_service');

        $starting_time = ($this->request->getVar('starting_time'));


        $order_note = ($this->request->getVar('order_note')) ? $this->request->getVar('order_note') : "";


        if (empty($this->request->getPost('order_id'))) {

            $minutes = strtotime($starting_time) + ($cart_data['total_duration'] * 60);
        } else {
            $minutes = strtotime($starting_time) + ($duartion * 60);
        }

        $ending_time = date('H:i:s', $minutes);

        if ($at_store != 1) {

            if (!exists(['id' => $address_id], 'addresses')) {
                return response('Address not exist');
            }
        }

        // $final_total = intval($total) + intval($cart_data['taxable_amount'])+intval($visiting_charges);
        $final_total = ceil($total) + ceil($visiting_charges);




        if (empty($this->request->getPost('order_id'))) {

            $ids = explode(',', $service_ids ?? '');
        } else {

            $ids = $service_ids;
        }

        $qtys = explode(',', $quantity ?? '');
        $service_data = fetch_details('services', [], '', '', '', '', '', 'id', $ids);
        $partner_id = $service_data[0]['user_id'];
        // $availability = check_availability($partner_id, $date_of_service, $starting_time);
        $current_date = date('Y-m-d');
        $service_total_duration = 0;
        $service_duration = 0;


        if (empty($this->request->getPost('order_id'))) {
            foreach ($cart_data['data'] as $main_data) {

                $service_duration = ($main_data['servic_details']['duration']) * $main_data['qty'];
                $service_total_duration = $service_total_duration + $service_duration;
            }
        } else {

            $service_total_duration = $duartion;
        }




        $availability =  checkPartnerAvailability($partner_id, $date_of_service . ' ' . $starting_time, $service_total_duration, $date_of_service, $starting_time);
        $insert_order = "";
        if (isset($availability) && $availability['error'] == "0") {

            $location_data = fetch_details('addresses', ['id' => $address_id]);
            $address['mobile'] = isset($location_data) && !empty($location_data) ? $location_data[0]['mobile'] : '';
            $address['address'] = isset($location_data) && !empty($location_data) ? $location_data[0]['address'] : '';
            $address['area'] = isset($location_data) && !empty($location_data) ? $location_data[0]['area'] : '';
            $address['city'] = isset($location_data) && !empty($location_data) ? $location_data[0]['city'] : '';
            $address['state'] = isset($location_data) && !empty($location_data) ? $location_data[0]['state'] : '';
            $address['country'] = isset($location_data) && !empty($location_data) ? $location_data[0]['country'] : '';
            $address['pincode'] = isset($location_data) && !empty($location_data) ? $location_data[0]['pincode'] : '';
            $city_id = isset($location_data) && !empty($location_data) ? $location_data[0]['city'] : '';


            $outputArray = array(
                $address['address'],
                $address['area'],
                $address['city'],
                $address['state'],
                $address['country'],
                $address['pincode'],
                $address['mobile']
            );

            $finaladdress = implode(',', $outputArray);






            $service_total_duration = 0;
            $service_duration = 0;




            if (empty($this->request->getPost('order_id'))) {
                foreach ($cart_data['data'] as $main_data) {
                    $service_duration = ($main_data['servic_details']['duration']) * $main_data['qty'];
                    $service_total_duration = $service_total_duration + $service_duration;
                }
            } else {
                $service_total_duration = $duartion;
            }


            // $time_slots = get_available_slots($partner_id, $date_of_service, isset($service_total_duration) ? $service_total_duration : 0, $starting_time); //working
            $time_slots = get_slot_for_place_order($partner_id, $date_of_service, $service_total_duration, $starting_time);
            $timestamp = date('Y-m-d h:i:s '); // Example timestamp format: 2023-08-08 03:30:00 PM


            if ($time_slots['slot_avaialble']) {

                $duration_minutes = $service_total_duration;

                if ($time_slots['suborder']) {

                    $end_minutes = strtotime($starting_time) + ((sizeof($time_slots['order_data']) * 30) * 60);
                    $ending_time = date('H:i:s', $end_minutes);


                    $day = date('l', strtotime($date_of_service));
                    $timings = getTimingOfDay($partner_id, $day);
                    $closing_time = $timings['closing_time']; // Replace with the actual closing time
                    if ($ending_time > $closing_time) {
                        $ending_time = $closing_time;
                    }

                    $start_timestamp = strtotime($starting_time);
                    $ending_timestamp = strtotime($ending_time);
                    $duration_seconds = $ending_timestamp - $start_timestamp;
                    $duration_minutes = $duration_seconds / 60;
                }

                $order = [
                    'partner_id' => $partner_id,
                    'user_id' => $this->user_details['id'],
                    'city' => $city_id,
                    'total' => $total,
                    'payment_method' => $payment_method,
                    'address_id' => isset($address_id) ? $address_id : "0",
                    'visiting_charges' => $visiting_charges,
                    'address' => isset($finaladdress) ? $finaladdress : "",
                    'date_of_service' => $date_of_service,
                    'starting_time' => $starting_time,
                    'ending_time' => $ending_time,
                    'duration' => $duration_minutes,
                    'status' => $status,
                    'remarks' => $order_note,
                    'otp' => random_int(100000, 999999),
                    'order_latitude' => $this->user_details['latitude'],
                    'order_longitude' => $this->user_details['longitude'],
                    'created_at' => $timestamp,
                ];





                if (!empty($promo_code)) {


                    $fetch_promococde = fetch_details('promo_codes', ['promo_code' => $promo_code]);
                    $promo_code = validate_promo_code($this->user_details['id'], $fetch_promococde[0]['id'], $final_total);

                    if ($promo_code['error']) {


                        return $response['message'] = ($promo_code['message']);
                    }
                    //add dicounted final total
                    $final_total = $promo_code['data'][0]['final_total'];
                    $order['promo_code'] = $promo_code['data'][0]['promo_code'];
                    $order['promo_discount'] = $promo_code['data'][0]['final_discount'];
                    $order['promocode_id'] = $fetch_promococde[0]['id'];
                }


                $order['final_total'] = $final_total;
                $insert_order = insert_details($order, 'orders');
            }

            if ($time_slots['suborder']) {






                $next_day_date = date('Y-m-d', strtotime($date_of_service . ' +1 day'));
                $next_day_slots = get_next_days_slots($closing_time, $date_of_service, $partner_id, $service_total_duration, $current_date);

                // if(!empty($next_day_available_slots)){
                $next_day_available_slots = $next_day_slots['available_slots'];

                $next_Day_minutes = strtotime($next_day_available_slots[0]) + (($service_total_duration - $duration_minutes) * 60);
                $next_day_ending_time = date('H:i:s', $next_Day_minutes);

                $next_day_ending_time = date('H:i:s', $next_Day_minutes);



                $sub_order = [
                    'partner_id' => $partner_id,
                    'user_id' => $this->user_details['id'],
                    'city' => $city_id,
                    'total' => $total,
                    'payment_method' => $payment_method,
                    'address_id' => isset($address_id) ? $address_id : "",
                    'visiting_charges' => $visiting_charges,
                    'address' => isset($finaladdress) ? $finaladdress : "",
                    'date_of_service' =>   $next_day_date,
                    'starting_time' => isset($next_day_available_slots[0]) ? $next_day_available_slots[0] : 00,
                    'ending_time' => $next_day_ending_time,
                    'duration' => $service_total_duration - $duration_minutes,
                    'status' => $status,
                    'remarks' => "sub_order",
                    'otp' => random_int(100000, 999999),
                    'parent_id' => $insert_order['id'],
                    'order_latitude' => $this->user_details['latitude'],
                    'order_longitude' => $this->user_details['longitude'],
                    'created_at' => $timestamp,
                ];
                if (!empty($this->request->getVar('promo_code'))) {

                    $fetch_promococde = fetch_details('promo_codes', ['promo_code' => $this->request->getVar('promo_code')]);
                    $promo_code = validate_promo_code($this->user_details['id'], $fetch_promococde[0]['id'], $final_total);





                    // die;
                    if ($promo_code['error']) {
                        return $response['message'] = ($promo_code['message']);
                    }
                    //add dicounted final total
                    $final_total = $promo_code['data'][0]['final_total'];
                    $sub_order['promo_code'] = $promo_code['data'][0]['promo_code'];
                    $sub_order['promo_discount'] = $promo_code['data'][0]['final_discount'];
                }
                $sub_order['final_total'] = $final_total;
                $sub_order = insert_details($sub_order, 'orders');
            }


            if ($insert_order) {



                for ($i = 0; $i < count($ids); $i++) {



                    $service_details = get_taxable_amount($ids[$i]);
                    $data = [
                        'order_id' => $insert_order['id'],
                        'service_id' => $ids[$i],
                        'service_title' => $service_details['title'],
                        'tax_percentage' => $service_details['tax_percentage'],
                        'tax_amount' => number_format(($service_details['tax_amount']), 2),
                        // 'tax_amount' => intval($service_details['tax_amount']) * $qtys[$i],
                        'price' => $service_details['price'],
                        'discount_price' => $service_details['discounted_price'],
                        'quantity' => $qtys[$i],


                        'sub_total' =>  strval(str_replace(',', '', number_format(strval(($service_details['taxable_amount'] * ($qtys[$i]))), 2))),
                        'status' => $status,
                    ];

                    insert_details($data, 'order_services');


                    $orderId['order_id'] = $insert_order['id'];


                    // if ($payment_method == "stripe") {
                    //     $stripe_intent = create_stripe_payment_intent();
                    //     $txn_id = $stripe_intent['id'];
                    //     $reference = "";

                    //     $orderId['stripe_intent'] = ($payment_method == "stripe") ?  $stripe_intent : "";
                    //     add_transaction_for_place_order($this->user_details['id'], $insert_order['id'], $payment_method, ceil(number_format(strval($final_total), 2)), $txn_id, $reference);
                    // } else if ($payment_method == "razorpay") {
                    //     $razorpay_order = razorpay_create_order_for_place_order($insert_order['id']);
                    //     $txn_id = $razorpay_order['data']['id'];
                    //     $reference = "";
                    //     $orderId['razorpay_order'] = ($payment_method == "razorpay") ?  $razorpay_order : "";
                    //     add_transaction_for_place_order($this->user_details['id'], $insert_order['id'], $payment_method, ceil(number_format(strval($final_total), 2)), $txn_id, $reference);
                    // } else if ($payment_method == "paystack") {
                    //     $reference = "ChargedFromAndroid_" . rand();
                    //     $orderId['reference'] = ($payment_method == "paystack") ?  $reference : "";
                    //     $txn_id = "-";
                    //     add_transaction_for_place_order($this->user_details['id'], $insert_order['id'], $payment_method, ceil(number_format(strval($final_total), 2)), $txn_id, $reference);
                    // }



                    $orderId['paypal_link'] = ($payment_method == "paypal") ? base_url() . '/api/v1/paypal_transaction_webview?user_id=' . $this->user_details['id'] . '&order_id=' . $insert_order['id'] . '&amount=' . ceil(number_format(strval($final_total), 2)) . '' : "";
                }


                if ($payment_method == 'cod') {
                    send_web_notification('New Order', 'Please check new order ' . $insert_order['id'], $partner_id);

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
                        // $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
                        $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                        $fcmMsg = array(
                            'content_available' => true,
                            'title' => " New Order Notification",
                            'body' => "We are pleased to inform you that you have received a new order. ",
                            'type' => 'order',
                            'order_id' => $insert_order['id'],
                            'type_id' => $to_send_id,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                        send_notification($fcmMsg, $registrationIDs_chunks);
                    }
                }

                $this->checkAndUpdateSubscriptionStatus($partner_id);

                return response('Order Placed successfully', false, remove_null_values($orderId));
            } else {
                return response('order not placed');
            }
        } else {

            return response($availability['message'], true);
        }



        // end







    }

    // 23. get_orders
    public function get_orders()
    {
        // try {
        /*
        id:10                   {optional}
        status:awating          {optional}
        limit:10                {optional}
        offset:0                {optional}
        sort:id                 {optional}
        order:asc               {optional}
        search:test             {optional}
        download_invoice:0 // { default - 0 } optional
         */
        $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
        $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
        $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
        $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'DESC';
        $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
        $download_invoice = ($this->request->getPost('download_invoice') && !empty($this->request->getPost('download_invoice'))) ? $this->request->getPost('download_invoice') : 1;
        $where = $additional_data = [];
        if ($this->request->getPost('id') && !empty($this->request->getPost('id'))) {
            $where['o.id'] = $this->request->getPost('id');
        }
        if ($this->request->getPost('status') && !empty($this->request->getPost('status'))) {
            $where['o.status'] = $this->request->getPost('status');
        }
        if ($this->user_details['id'] != '') {
            $where['o.user_id'] = $this->user_details['id'];
        }
        $orders = new Orders_model();
        $order_detail = $orders->list(true, $search, $limit, $offset, $sort, $order, $where, $download_invoice, '', '', '', '', false);




        if (!empty($order_detail['data'])) {
            return response('Order fetched successfully', false, remove_null_values($order_detail['data']), 200, ['total' => $order_detail['total']]);
        } else {
            return response('Order not found');
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    // 24. manage_notification
    public function manage_notification()
    {
        try {
            /*
        notification_id:15
        is_readed:0             {optional}
        delete_notification:1   {optional}
         */
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'notification_id' => 'required',
                    'is_readed' => 'permit_empty|numeric',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $nfcs = fetch_details('notifications', ['id' => $this->request->getPost('notification_id')]);
            if (empty($nfcs)) {
                // print_r($nfcs);
                return response('notification not found!');
            }
            // if (!exists(['id' => $this->request->getPost('notification_id')], 'notifications')) {
            // }
            // delete notification
            if ($this->request->getPost('delete_notification') && $this->request->getPost('delete_notification') == 1) {
                $data = ['id' => $this->request->getPost('notification_id'), 'user_id' => $this->user_details['id']];
                if (exists(['id' => $this->request->getPost('notification_id'), 'notification_type' => 'general'], 'notifications')) {
                    if (exists(['notification_id' => $this->request->getPost('notification_id'), 'user_id' => $this->user_details['id']], 'delete_general_notification')) {
                        update_details(['is_deleted' => 1], ['notification_id' => $this->request->getPost('notification_id'), 'user_id' => $this->user_details['id']], 'delete_general_notification');
                        return response('Notification deleted successfully', false);
                    } else {
                        insert_details(['is_deleted' => 1, 'notification_id' => $this->request->getPost('notification_id'), 'user_id' => $this->user_details['id']], 'delete_general_notification');
                        return response('Notification deleted successfully', false);
                    }
                }
                if (!exists($data, 'notifications')) {
                    return response('notification not found');
                }
                if (delete_details($data, 'notifications')) {
                    return response('Notification deleted successfully', false);
                } else {
                    return response('Something get wrong');
                }
            }
            $data = ['id' => $this->request->getPost('notification_id'), 'user_id' => $this->user_details['id']];
            if (!exists($data, 'notifications')) {
                return response('notification not found..');
            }
            if (exists(['id' => $this->request->getPost('notification_id'), 'notification_type' => 'general'], 'notifications')) {
                if (exists(['notification_id' => $this->request->getPost('notification_id'), 'user_id' => $this->user_details['id']], 'delete_general_notification')) {
                    update_details(['is_deleted' => !empty($this->request->getPost('is_readed')) ? 1 : 0], ['notification_id' => $this->request->getPost('notification_id'), 'user_id' => $this->user_details['id']], 'delete_general_notification');
                    return response('Notification updated successfully', false);
                } else {
                    $set = [
                        'is_readed' => $this->request->getPost('is_readed') != '' ? 1 : 0,
                        'notification_id' => $this->request->getPost('notification_id'),
                        'user_id' => $this->user_details['id'],
                    ];
                    insert_details($set, 'delete_general_notification');
                    return response('Notification updated successfully', false);
                }
            }
            $update_notifications = update_details(
                ['is_readed' => $this->request->getPost('is_readed') != '' ? 1 : 0],
                ['id' => $this->request->getPost('notification_id'), 'user_id' => $this->user_details['id']],
                'notifications'
            );
            if ($update_notifications == true) {
                $notifcations = $this->get_notifications($this->request->getPost('notification_id'));
                if (!empty($notifcations)) {
                    $error = false;
                    $message = 'notification updated successfully';
                } else {
                    $error = true;
                    $message = 'notification not found';
                }
                return response($message, $error, remove_null_values($notifcations));
            } else {
                return response('something get wrong');
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 25. get_notifications
    // public function get_notifications($id = 0)
    // {
    //     /*
    //     id:10                   {optional}
    //     limit:10                {optional}
    //     offset:0                {optional}
    //     sort:id                 {optional}
    //     order:asc               {optional}
    //     search:test             {optional}
    //      */
    //     // try {
    //     $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
    //     $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
    //     $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
    //     $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'DESC';
    //     $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
    //     $where = $additional_data = [];
    //     if ($this->request->getPost('id') && !empty($this->request->getPost('id'))) {
    //         $where['id'] = $this->request->getPost('id');
    //     }
    //     if (!empty($id)) {
    //         $where['id'] = $id;
    //     }
    //     $whereIn['target'] = ['all_users', 'specific_user', 'customer'];


    //     $notifications = new Notification_model();
    //     $get_notifications = $notifications->list(true, $search, $limit, $offset, $sort, $order, $where, $whereIn);
    //     foreach ($get_notifications['data'] as $key => $row) {
    //         if (is_array(json_decode($row['user_id']))) {
    //             $decodedArray = json_decode($row['user_id']);
    //             if (!in_array($this->user_details['id'], $decodedArray)) {
    //                 unset($get_notifications['data'][$key]);
    //             }
    //         }
    //     }

    //     foreach ($get_notifications['data'] as $key => $notifcation) {
    //         $dateTime = new DateTime($notifcation['date_sent']);
    //         $date = $dateTime->format('Y-m-d');
    //         $time = $dateTime->format('H:i');
    //         if ($date == date('Y-m-d')) {
    //             $start = strtotime($time);
    //             $end = time();
    //             $duration = $start - $end;
    //             $duration = date('H', $duration) . ' hours ago';
    //         } else {
    //             $now = time(); // or your date as well
    //             $date = strtotime($date);
    //             $datediff = $now - $date;
    //             $duration = round($datediff / (60 * 60 * 24)) . ' days ago';
    //         }
    //         $get_notifications['data'][$key]['duration'] = $duration;
    //     }

    //     // print_R($get_notifications);
    //     // die;
    //     if (!empty($id)) {
    //         return $get_notifications['data'];
    //     }
    //     if (!empty($get_notifications['data'])) {
    //         return response('Notifications fetched successfully', false, remove_null_values($get_notifications['data']), 200, ['total' => sizeOf($get_notifications['data'])]);
    //     } else {
    //         return response('Notification Not Found');
    //     }
    //     // } catch (\Exception $th) {
    //     //     $response['error'] = true;
    //     //     $response['message'] = 'Something went wrong';
    //     //     return $this->response->setJSON($response);
    //     // }
    // }
    public function get_notifications($id = 0)
    {
        /*
        id:10                   {optional}
        limit:10                {optional}
        offset:0                {optional}
        sort:id                 {optional}
        order:asc               {optional}
        search:test             {optional}
         */
        // try {
        $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
        $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
        $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
        $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'DESC';
        $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
        $where = $additional_data = [];
        if ($this->request->getPost('id') && !empty($this->request->getPost('id'))) {
            $where['id'] = $this->request->getPost('id');
        }
        if (!empty($id)) {
            $where['id'] = $id;
        }
        $whereIn['target'] = ['all_users', 'specific_user', 'customer'];


        $notifications = new Notification_model();
        $get_notifications = $notifications->list(true, $search, $limit, $offset, $sort, $order, $where, $whereIn);


        foreach ($get_notifications['data'] as $key => $row) {
            if (is_array(json_decode($row['user_id']))) {
                $decodedArray = json_decode($row['user_id']);
                if (!in_array($this->user_details['id'], $decodedArray)) {
                    unset($get_notifications['data'][$key]);
                }
            }
        }

        foreach ($get_notifications['data'] as $key => $notifcation) {
            $dateTime = new DateTime($notifcation['date_sent']);
            $date = $dateTime->format('Y-m-d');
            $time = $dateTime->format('H:i');
            if ($date == date('Y-m-d')) {
                $start = strtotime($time);
                $end = time();
                $duration = $start - $end;
                $duration = date('H', $duration) . ' hours ago';
            } else {
                $now = time(); // or your date as well
                $date = strtotime($date);
                $datediff = $now - $date;
                $duration = round($datediff / (60 * 60 * 24)) . ' days ago';
            }
            $get_notifications['data'][$key]['duration'] = $duration;
        }

        // print_R($get_notifications);
        // die;
        if (!empty($id)) {
            return $get_notifications['data'];
        }
        if (!empty($get_notifications['data'])) {
            return response('Notifications fetched successfully', false, remove_null_values($get_notifications['data']), 200, ['total' => ($get_notifications['total'])]);
        } else {
            return response('Notification Not Found');
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    // 26. get_ticket_types
    public function get_ticket_types()
    {
        try {
            helper("function");
            $type = fetch_details('ticket_types', [], ['id', 'title'], '', '', '', 'ASC');
            if (!empty($type)) {
                return response('Tickets type fetched successfuly', false, $type);
            } else {
                return response('Currently no tickets type available', true);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 27. add_ticket
    public function add_ticket()
    {
        try {
            /*
        ticket_type_id:1
        subject:test
        email:test@gmail.com
        description:testing
         */
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'ticket_type_id' => 'required|trim|numeric',
                    'subject' => 'required|trim',
                    'email' => 'required|trim|valid_email',
                    'description' => 'required|trim',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $ticket_type_id = $this->request->getPost('ticket_type_id');
            $subject = $this->request->getPost('subject');
            $email = $this->request->getPost('email');
            $description = $this->request->getPost('description');
            if (!exists(['id' => $ticket_type_id], 'ticket_types')) {
                return response('ticket type not exits');
            }
            if (exists(['user_id' => $this->user_details['id'], 'ticket_type_id' => $ticket_type_id, 'subject' => $subject], 'tickets')) {
                return response('ticket already created');
            }
            $data = [
                'ticket_type_id' => $ticket_type_id,
                'user_id' => $this->user_details['id'],
                'subject' => $subject,
                'email' => $email,
                'description' => $description,
                'status' => "0",
            ];
            $ticket = insert_details($data, 'tickets');
            if ($ticket) {
                $data = $this->get_tickets($ticket['id']);
                if (!empty($data[0])) {
                    $data = $data[0];
                }
                return response('ticket generated successfuly', false, remove_null_values($data));
            } else {
                return response('Some thing get wrong', true);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 28. edit_ticket
    public function edit_ticket()
    {
        try {
            /*
        ticket_id:45
        ticket_type_id:1
        subject:test
        email:test@gmail.com
        description:testing
        status:1
         */
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'ticket_id' => 'required|trim|numeric',
                    'ticket_type_id' => 'required|trim|numeric',
                    'subject' => 'required',
                    'email' => 'required|trim|valid_email',
                    'description' => 'required',
                    'status' => 'permit_empty|numeric',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $ticket_id = $this->request->getPost('ticket_id');
            $ticket_type_id = $this->request->getPost('ticket_type_id');
            $subject = $this->request->getPost('subject');
            $email = $this->request->getPost('email');
            $description = $this->request->getPost('description');
            $status = $this->request->getPost('status');
            if (!exists(["id" => $ticket_id], 'tickets')) {
                return response('ticket does not exist', true, []);
            }
            if (!exists(["id" => $ticket_type_id], 'ticket_types')) {
                return response('ticket type does not exist', true, []);
            }
            if ($status == 4 && !exists(['status' => 3, 'id' => $ticket_id], 'tickets')) {
                return response('ticket is not closed', true, []);
            } else if ($status != 2 && $status != 4) {
                return response('user can only resolve or reopen ticket', true, []);
            }
            /* check if the user is updating his own ticket only or not. */
            $ticket_details = fetch_details('tickets', ['id' => $ticket_id], [])[0];
            if ($this->user_details['id'] != $ticket_details['user_id']) {
                $response = [
                    'error' => true,
                    'message' => "Invalid ticket ID supplied.",
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $data = [
                'ticket_type_id' => $ticket_type_id,
                'user_id' => $this->user_details['id'],
                'subject' => $subject,
                'email' => $email,
                'description' => $description,
                'status' => $status,
            ];
            $ticket = update_details($data, ['id' => $ticket_id], 'tickets');
            if ($ticket) {
                $data = $this->get_tickets($ticket_id);
                return response('ticket updated successfuly', false, $data[0]);
            } else {
                return response('Some thing get wrong', true);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 29. get_tickets
    public function get_tickets()
    {
        try {
            /*
        ticket_id:1         {optional}
        limit:10            {optional}
        offset:0            {optional}
         */
            $limit = !empty($this->request->getPost("limit")) ? $this->request->getPost("limit") : 10;
            $offset = !empty($this->request->getPost("offset")) ? $this->request->getPost("offset") : 0;
            $db = \Config\Database::connect();
            $where = ['t.user_id' => $this->user_details['id']];
            if ($this->request->getPost('ticket_id') && !empty($this->request->getPost('ticket_id'))) {
                $where['t.id'] = $this->request->getPost('ticket_id');
            }
            $ticket_id = ($this->request->getPost('ticket_id') != '') ? $this->request->getPost('ticket_id') : 0;
            $builder = $db->table('tickets t');
            $total = $builder->select('count(t.id) as total')
                ->join('ticket_types ttype', 't.ticket_type_id=ttype.id')
                ->where($where)
                ->get()
                ->getResultArray();
            $tickets = $builder->select('t.*,ttype.title as ticket_type')
                ->join('ticket_types ttype', 't.ticket_type_id=ttype.id')
                ->where($where)
                ->limit($limit, $offset)
                ->get()->getResultArray();
            $status = [
                0 => "Pending",
                1 => "Opened",
                2 => "Resolved",
                3 => "Closed",
                4 => "Reopened",
            ];
            $rows = [];
            foreach ($tickets as $ticket) {
                $temp = [];
                $temp['id'] = $ticket['id'];
                $temp['ticket_type_id'] = $ticket['ticket_type_id'];
                $temp['ticket_type'] = $ticket['ticket_type'];
                $temp['user_id'] = $ticket['user_id'];
                $temp['description'] = $ticket['description'];
                $temp['subject'] = $ticket['subject'];
                $temp['email'] = $ticket['email'];
                $temp['status_code'] = $ticket['status'];
                $temp['status'] = $status[$ticket['status']];
                $rows[] = $temp;
            }
            if ($ticket_id != 0) {
                return $rows;
            }
            if (!empty($rows)) {
                return response('Tickets  fetched successfuly', false, $rows, 200, ['total' => $total[0]['total']]);
            } else {
                return response('Currently no tickets  available', true);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 30. send_message
    public function send_message()
    {
        try {
            /*
        ticket_id:10
        message:test
        attachments[] : FILES  // {optional}
         */
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'ticket_id' => 'required|trim|numeric',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $ticket_id = $this->request->getPost('ticket_id');
            $message = $this->request->getPost('message');
            /* check if the user is sending message to his own ticket only or not. */
            $ticket_details = fetch_details('tickets', ['id' => $ticket_id]);
            if (!empty($ticket_details)) {
                if ($this->user_details['id'] != $ticket_details[0]['user_id']) {
                    $response = [
                        'error' => true,
                        'message' => "Invalid user ID supplied.",
                        'data' => [],
                    ];
                    return $this->response->setJSON($response);
                }
            }
            if (!exists(['id' => $ticket_id], 'tickets')) {
                $response = [
                    'error' => true,
                    'message' => "Invalid ticket ID supplied.",
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $store_attac = [];
            if (!empty($_FILES['attachments']) && isset($_FILES['attachments'])) {
                $attachments = $this->request->getFileMultiple('attachments');
                /* just validate every file at first. */
                foreach ($attachments as $attachment) {
                    if (!$attachment->isValid()) {
                        $response = [
                            'error' => true,
                            'message' => 'Something went wrong please try after some time.',
                            'data' => [],
                        ];
                        return $this->response->setJSON($response);
                    }
                    $attachment_type = $attachment->getMimeType();
                    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'video/mp4', 'video/mkv', 'video/mov'];
                    if (!in_array($attachment_type, $allowed_types)) {
                        $response = [
                            'error' => true,
                            'message' => 'Please attach a valid  file.',
                            'data' => [],
                        ];
                        return $this->response->setJSON($response);
                    }
                }
                /* if nothing goes wrong with attachments on top loop, then finally upload the attachments */
                $files = $this->request->getFileMultiple('attachments');
                $path = './public/support-ticket/';
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                foreach ($files as $file) {
                    if ($file->isValid()) {
                        $newName = $file->getRandomName();
                        $file->move($path, $newName);
                        $store_attac[] = 'public/support-ticket/' . $file->getName();
                    }
                }
            } else if ((!isset($_FILES['attachments']) || $this->request->getFileMultiple('attachments') == []) && (!isset($_POST['message']) || empty(trim($_POST['message'])))) {
                return response("Oops! Blank message cannot be sent");
            }
            $data = [
                'user_type' => 'user',
                'user_id' => $this->user_details['id'],
                'ticket_id' => $ticket_id,
                'message' => $message,
                'attachments' => (isset($store_attac) && !empty($store_attac) ? json_encode($store_attac) : ""),
            ];
            $ticket_messages = insert_details($data, 'ticket_messages');
            if ($ticket_messages) {
                // add domain path images
                if (isset($store_attac) && !empty($store_attac)) {
                    for ($i = 0; $i < count($store_attac); $i++) {
                        $store_attac[$i] = base_url($store_attac[$i]);
                    }
                }
                $data['attachments'] = (!empty($store_attac)) ? $store_attac : [];
                return response('Message sent successfully', false, $data);
            } else {
                return response('Some thing get wrong', true);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 31. get_messages
    public function get_messages()
    {
        try {
            /*
        ticket_id:10
        limit:10           {optional}
        offset:0           {optional}
        sort:id             {optional}
        order:asc           {optional}
         */
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'ticket_id' => 'required|numeric',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $db = \Config\Database::connect();
            $limit = !empty($this->request->getPost("limit")) ? $this->request->getPost("limit") : 10;
            $offset = !empty($this->request->getPost("offset")) ? $this->request->getPost("offset") : 0;
            $sort = !empty($this->request->getPost("sort")) ? $this->request->getPost("sort") : 'tm.id';
            $order = !empty($this->request->getPost("order")) ? $this->request->getPost("order") : 'DESC';
            $ticket_id = $this->request->getPost('ticket_id');
            $ticket_details = fetch_details('tickets', ['id' => $ticket_id], [])[0];
            if ($this->user_details['id'] != $ticket_details['user_id']) {
                $response = [
                    'error' => true,
                    'message' => "Invalid ticket ID supplied.",
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $builder = $db->table('ticket_messages tm');
            $total = $builder->select('count(tm.id) as total')
                ->where('tm.ticket_id', $ticket_id)
                ->get()
                ->getResultArray();
            $ticket_messages = $builder->select('tm.*')
                ->where('tm.ticket_id', $ticket_id)
                ->limit($limit, $offset)
                ->orderBy($sort, $order)
                ->get()
                ->getResultArray();
            $rows = [];
            $status = [
                0 => "Pending",
                1 => "Opened",
                2 => "Resolved",
                3 => "Closed",
                4 => "Reopened",
            ];
            $rows['ticket_details'] = [
                'status' => $status[$ticket_details['status']],
                'status_code' => $ticket_details['status'],
            ];
            foreach ($ticket_messages as $message) {
                $message['attachments'] = json_decode(str_replace("'", '', $message['attachments']));
                if (!empty($message['attachments'])) {
                    // add domain path in images
                    for ($i = 0; $i < count($message['attachments']); $i++) {
                        $message['attachments'][$i] = base_url($message['attachments'][$i]);
                    }
                }
                $temp = [];
                $temp['id'] = $message['id'];
                $temp['user_type'] = $message['user_type'];
                $temp['message'] = $message['message'];
                $temp['attachments'] = (!empty($message['attachments'])) ? $message['attachments'] : [];
                $rows['messages'][] = $temp;
            }
            if (!empty($rows['messages'])) {
                return response('Messages Retrived successfully', false, $rows, 200, ['total' => $total[0]['total']]);
            } else {
                return response('Currently no messages are available', true, $rows);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 32. book_mark
    public function book_mark()
    {
        try {
            /*
        type:add/remove/list
        partner_id:12
        limit:10            {optional}
        offset:0            {optional}
        sort:id             {optional}
        order:asc           {optional}
        search:test         {optional}
         */
            $book_marks = new Bookmarks_model();
            $validation = \Config\Services::validation();
            $user_id = $this->user_details['id'];
            $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $where = ['b.user_id' => $user_id];
            $rules = [
                'type' => [
                    "rules" => 'required|in_list[add,remove,list]',
                    "errors" => [
                        "required" => "Type is required",
                        "in_list" => "Type value is incorrect",
                    ],
                ],
            ];
            if ($this->request->getPost('type') == "list") {
                $rules['latitude'] = [
                    "rules" => 'required',
                ];
                $rules['longitude'] = [
                    "rules" => 'required',
                ];
            }
            $validation->setRules($rules);
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $type = $this->request->getPost('type');
            if ($type == 'add' || $type == "remove") {
                $validation->setRules(
                    [
                        'partner_id' => 'required',
                    ]
                );
                if (!$validation->withRequest($this->request)->run()) {
                    $errors = $validation->getErrors();
                    $response = [
                        'error' => true,
                        'message' => $errors,
                        'data' => [],
                    ];
                    return $this->response->setJSON($response);
                }
            }
            $partner_id = $this->request->getPost('partner_id');
            $is_booked = is_bookmarked($user_id, $partner_id)[0]['total'];
            $partner_details = fetch_details('partner_details', ['partner_id' => $partner_id]);
            $data = [
                'user_id' => $user_id,
                'partner_id' => $partner_id,
            ];
            if ($type == 'add' && !empty($partner_details)) {
                if ($is_booked == 0) {
                    if ($book_marks->save($data)) {
                        return response('Added to book marks', false, [], 200);
                    } else {
                        return response('Could not add to the book marks', true, [], 200);
                    }
                } else {
                    return response('This partner is already bookmarked', true, [], 200);
                }
            } else if ($type == 'remove' && !empty($partner_details)) {
                $remove = delete_bookmark($user_id, $partner_id);
                if ($is_booked > 0) {
                    if ($remove) {
                        return response('Removed from book marks', false, [], 200);
                    } else {
                        return response('Could not remove form', true, [], 200);
                    }
                } else {
                    return response('No partner selected', true, [], 200);
                }
            } elseif ($type == "list") {
                $Partners_model = new Partners_model();
                $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
                $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
                $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('sort'))) ? $this->request->getPost('sort') : 'id';
                $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
                $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
                $where = $additional_data = [];
                $filter = ($this->request->getPost('filter') && !empty($this->request->getPost('filter'))) ? $this->request->getPost('filter') : '';
                $customer_id = $this->user_details['id'];
                $settings = get_settings('general_settings', true);
                if (($this->request->getPost('latitude') && !empty($this->request->getPost('latitude')) && ($this->request->getPost('longitude') && !empty($this->request->getPost('longitude')))) && $customer_id != '') {
                    $additional_data = [
                        'latitude' => $this->request->getPost('latitude'),
                        'longitude' => $this->request->getPost('longitude'),
                        'customer_id' => $customer_id,
                        'max_serviceable_distance' => $settings['max_serviceable_distance'],
                    ];
                }
                // if ($customer_id != '') {
                //     $additional_data = [
                //     ];
                // }
                $partner_ids = favorite_list($user_id);
                if (!empty($partner_ids)) {
                    $data = $Partners_model->list(true, $search, $limit, $offset, $sort, $order, $where, 'pd.partner_id', $partner_ids, $additional_data);
                }
                $user = ['user_id' => $user_id];
                if (!empty($data['data'])) {
                    for ($i = 0; $i < count($data['data']); $i++) {
                        unset($data['data'][$i]['national_id']);
                        unset($data['data'][$i]['passport']);
                        unset($data['data'][$i]['tax_name']);
                        unset($data['data'][$i]['tax_number']);
                        unset($data['data'][$i]['bank_name']);
                        unset($data['data'][$i]['account_number']);
                        unset($data['data'][$i]['account_name']);
                        unset($data['data'][$i]['bank_code']);
                        unset($data['data'][$i]['swift_code']);
                        unset($data['data'][$i]['type']);
                        unset($data['data'][$i]['advance_booking_days']);
                        unset($data['data'][$i]['admin_commission']);
                        array_merge($data['data'][$i], $user);
                    }
                    return response('Bookmarks Retrieved successfully', false, remove_null_values($data['data']), 200, ['total' => $data['total']]);
                } else {
                    return response("No Bookmarks found", false);
                }
                $data = $book_marks->list(true, $search, $limit, $offset, $sort, $order, $where);
                return response('Data Retrived successfully', false, remove_null_values($data['data']), 200, ['total' => $data['total']]);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 33. generate_paytm_checksum
    public function generate_paytm_checksum()
    {
        try {
            $validation = \Config\Services::validation();
            /*
        order_id:1001
        amount:1099
        user_id:73              //{ optional }
        industry_type:Industry  //{ optional }
        channel_id:WAP          //{ optional }
        website:website link    //{ optional }
         */
            $validation->setRules(
                [
                    'order_id' => 'required',
                    'amount' => 'required|numeric',
                ],
                [
                    'subscription_id' => [
                        'required' => 'User id is required',
                    ],
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            } else {
                $settings = get_settings('payment_gateways_settings', true);
                $credentials = $this->paytm->get_credentials();
                $paytm_params["MID"] = $settings['paytm_merchant_id'];
                $paytm_params["ORDER_ID"] = $this->request->getPost('order_id');
                $paytm_params["TXN_AMOUNT"] = $this->request->getPost('amount');
                $paytm_params["CUST_ID"] = $this->user_details['id'];
                $paytm_params["WEBSITE"] = (($this->request->getPost('website', true) != null) && !empty($this->request->getPost('website'))) ? $this->request->getPost('website', true) : '';
                $paytm_params["CALLBACK_URL"] = $credentials['url'] . "theia/paytmCallback?ORDER_ID=" . $paytm_params["ORDER_ID"];
                $paytm_checksum = $this->paytm->generateSignature($paytm_params, $settings['paytm_merchant_key']);
                if (!empty($paytm_checksum)) {
                    $response['error'] = false;
                    $response['message'] = "Checksum created successfully";
                    $response['order id'] = $paytm_params["ORDER_ID"];
                    $response['data'] = $paytm_params;
                    $response['signature'] = $paytm_checksum;
                    return $this->response->setJSON($response);
                } else {
                    $response['error'] = true;
                    $response['message'] = "Data not found!";
                    return $this->response->setJSON($response);
                }
                $data['error'] = true;
                $data['message'] = "checking if we're here";
                $data['data'] = $paytm_params;
                return $this->response->setJSON($data);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 34. generate_paytm_txn_token
    public function generate_paytm_txn_token()
    {
        try {
            $validation = \Config\Services::validation();
            /*
        amount:100.00
        order_id:102
        user_id:73
        industry_type:      //{optional}
        channel_id:      //{optional}
        website:      //{optional}
         */
            if (!$user_token = verify_tokens()) {
                $status = $this->response->getStatusCode();
                return $this->response->setStatusCode($status);
            }
            $validation->setRules(
                [
                    'order_id' => 'required',
                    'amount' => 'required|numeric',
                    'user_id' => 'required|numeric',
                ],
                [
                    'user_id' => [
                        'required' => 'User id is required',
                    ],
                    'order_id' => [
                        'required' => 'order id is required',
                    ],
                    'amount' => [
                        'required' => 'amount id is required',
                    ],
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            } else {
                $credentials = $this->paytm->get_credentials();
                $order_id = $_POST['order_id'];
                $amount = $_POST['amount'];
                $user_id = $_POST['user_id'];
                $paytmParams = array();
                $paytmParams["body"] = array(
                    "requestType" => "Payment",
                    "mid" => $credentials['paytm_merchant_id'],
                    "websiteName" => "WEBSTAGING",
                    "orderId" => $order_id,
                    "callbackUrl" => $credentials['url'] . "theia/paytmCallback?ORDER_ID=" . $order_id,
                    "txnAmount" => array(
                        "value" => $amount,
                        "currency" => "INR",
                    ),
                    "userInfo" => array(
                        "custId" => $user_id,
                    ),
                );
                $checksum = $this->paytm->generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $credentials['paytm_merchant_key']);
                $paytmParams["head"] = array(
                    "signature" => $checksum,
                );
                $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);
                $url = $credentials['url'] . "/theia/api/v1/initiateTransaction?mid=" . $credentials['paytm_merchant_id'] . "&orderId=" . $order_id;
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
                $paytm_response = curl_exec($ch);
                if (!empty($paytm_response)) {
                    $paytm_response = json_decode($paytm_response, true);
                    if (isset($paytm_response['body']['resultInfo']['resultMsg']) && ($paytm_response['body']['resultInfo']['resultMsg'] == "Success" || $paytm_response['body']['resultInfo']['resultMsg'] == "Success Idempotent")) {
                        $response['error'] = false;
                        $response['message'] = "Transaction token generated successfully";
                        $response['txn_token'] = $paytm_response['body']['txnToken'];
                        $response['paytm_response'] = $paytm_response;
                        return $this->response->setJSON($response);
                    } else {
                        $response['error'] = true;
                        $response['message'] = $paytm_response['body']['resultInfo']['resultMsg'];
                        $response['txn_token'] = "";
                        $response['paytm_response'] = $paytm_response;
                        return $this->response->setJSON($response);
                    }
                }
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // 35. validate_paytm_checksum
    public function validate_paytm_checksum()
    {
        try {
            $validation = \Config\Services::validation();
            if (!$user_token = verify_tokens()) {
                $status = $this->response->getStatusCode();
                return $this->response->setStatusCode($status);
            }
            $validation->setRules(
                [
                    'order_id' => 'required',
                ],
                [
                    'order_id' => [
                        'required' => 'User id is required',
                    ],
                ]
            );
            $order_id = $this->request->getPost('order_id');
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            } else {
                $data = verify_payment_transaction($order_id, 'paytm');
                $response = [
                    'error' => false,
                    'message' => 'success full paymnet',
                    'data' => $data,
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
        /**
         *  paytm_checksum:PAYTM_CHECKSUM
         *  order_id:1001
         *  amount:1099
         *  user_id:73              //{ optional }
         *  industry_type:Industry  //{ optional }
         *  channel_id:WAP          //{ optional }
         *  website:website link    //{ optional }
         */
    }
    public function update_order_status()
    {
        // try {
        /*
        order_id:42
        status:rescheduled
        date:2022-11-28 {optional} only enter when update status to rescheduled
        time:11:00:00 {optional} only enter when update status to rescheduled
         */
        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'order_id' => 'required|numeric',
                'status' => 'required',
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $order_id = $this->request->getPost('order_id');
        $customer_id = $this->user_details['id'];
        $status = $this->request->getPost('status');
        $date = $this->request->getPost('date');
        $selected_time = $this->request->getPost('time');


        if ($status == "rescheduled") {

            $validate = validate_status($order_id, $status, $date, $selected_time);
            $where['o.id'] = $order_id;
            $orders = new Orders_model();
            $order_detail = $orders->list(true, '', 10, 0, 'o.id', 'DESC', $where, '', '', '', '', '', false);
            $response['error'] = $validate['error'];
            $response['message'] = $validate['message'];
            $response['data'] = $order_detail;
            return $this->response->setJSON($response);
        } else {
            $validate = validate_status($order_id, $status);
        }
        if ($validate['error']) {
            $response['error'] = true;
            $response['message'] = $validate['message'];

            return $this->response->setJSON($response);
        } else {
            if ($validate['error']) {
                $response['error'] = true;
                $response['message'] = $validate['message'];
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['data'] = array();
                return $this->response->setJSON($response);
            }


            if ($status == "awaiting") {
                $response = [
                    'error' => false,
                    'message' => "Order is in Awaiting!",
                ];
                return $this->response->setJSON($response);
            }
            if ($status == "confirmed") {
                $response = [
                    'error' => false,
                    'message' => "Order is Confirmed!",
                ];
                return $this->response->setJSON($response);
            }
            if ($status == "cancelled") {
                $orders = new Orders_model();
                $where['o.id'] = $order_id;
                $order_detail = $orders->list(true, '', 10, 0, 'o.id', 'DESC', $where, '', '', '', '', '', false);
                $response = [
                    'error' => false,
                    'message' => "Order is cancelled!",
                    'data' => $order_detail,
                ];
                return $this->response->setJSON($response);
            }
            if ($status == "completed") {
                $commision = unsettled_commision($this->userId);
                update_details(['balance' => $commision], ['id' => $this->userId], 'users');
                $response = [
                    'error' => false,
                    'message' => "Order Completed successfully!",


                ];
                return $this->response->setJSON($response);
            }
            //custom notification message
            if ($status == 'awaiting') {
                $type = ['type' => "customer_order_awaiting"];
            } elseif ($status == 'confirmed') {
                $type = ['type' => "customer_order_confirmed"];
            } elseif ($status == 'rescheduled') {
                $type = ['type' => "customer_order_rescheduled"];
            } elseif ($status == 'cancelled') {
                $type = ['type' => "customer_order_cancelled"];
            } elseif ($status == 'completed') {
                $type = ['type' => "customer_order_completed"];
            }
            // $custom_notification = fetch_details('notifications', $type);
            // $app_name = isset($settings['company_title']) && !empty($settings['company_title']) ? $settings['company_title'] : '';
            // $user_res = fetch_details('users', ['id' => $customer_id], 'username,fcm_id,platform');
            // $customer_msg = (!empty($custom_notification)) ? $custom_notification[0]['message'] : 'Hello Dear ' . $user_res[0]['username'] . ' order status updated to ' . $status . ' for your order ID #' . $order_id . ' please take note of it! Thank you for shopping with us. Regards ' . $app_name . '';
            // $fcm_ids = array();
            // if (!empty($user_res[0]['fcm_id'])) {
            //     $fcmMsg = array(
            //         'title' => (!empty($custom_notification)) ? $custom_notification[0]['title'] : "Order status updated",
            //         'body' => $customer_msg,
            //         'type' => "order",
            //     );
            //     $fcm_ids[0][] = $user_res[0]['fcm_id'];
            //     $fcm_ids[0][] = $user_res[0]['fcm_id'];

            //     send_notification($fcmMsg, $fcm_ids);
            // }
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    public function get_booked_slot()
    {
        try {
            // partner_id : 50 [required]
            // date : '2022-08-04' [Y-m-d] [required]
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'partner_id' => 'required|numeric',
                    'date' => 'required|valid_date[Y-m-d]',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $partner_id = $this->request->getPost('partner_id');
            $date = $this->request->getPost('date');
            $booked_slots = booked_timings($partner_id, $date);
            if (!empty($booked_slots)) {
                $response = [
                    'error' => false,
                    'message' => 'Booked slots',
                    'data' => $booked_slots,
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Booked slots not available!',
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    // available slots api - without reorder
    // public function get_available_slots()
    // {
    //     // try {
    //     //     /*
    //     //         partner_id : 15
    //     //         date : 2022-11-01
    //     //     */
    //     $validation = \Config\Services::validation();
    //     $validation->setRules(
    //         [
    //             'partner_id' => 'required|numeric',
    //             'date' => 'required|valid_date[Y-m-d]',
    //         ]
    //     );
    //     if (!$validation->withRequest($this->request)->run()) {
    //         $errors = $validation->getErrors();
    //         $response = [
    //             'error' => true,
    //             'message' => $errors,
    //             'data' => [],
    //         ];
    //         return $this->response->setJSON($response);
    //     }
    //     $days = [
    //         'Mon' => 'monday',
    //         'Tue' => 'tuesday',
    //         'Wed' => 'wednesday',
    //         'Thu' => 'thursday',
    //         'Fri' => 'friday',
    //         'Sat' => 'saturday',
    //         'Sun' => 'sunday',
    //     ];

    //     $partner_id = $this->request->getPost('partner_id');
    //     $date = $this->request->getPost('date');
    //     $time = $this->request->getPost('date');
    //     $date = new DateTime($date);
    //     $date = $date->format('Y-m-d');
    //     $day = date('D', strtotime($date));
    //     $whole_day = $days[$day];
    //     $partner_data = fetch_details('partner_details', ['partner_id' => $partner_id], ['advance_booking_days']);


    //     //for passing total duartion 
    //     // $user_cart_data = fetch_details('cart', ['user_id' => $this->user_details['id']]);

    //     $cart_data = fetch_cart(true, $this->user_details['id']);

    //     $duration = 0;




    //     // $time_slots = get_available_slots($partner_id, $date, $duration); //working

    //     $time_slots = get_available_slots($partner_id, $date, isset($cart_data['total_duration']) ? $cart_data['total_duration'] : 0); //working


    //     $available_slots = $busy_slots = $time_slots['all_slots'] = [];





    //     if (isset($time_slots['available_slots']) && !empty($time_slots['available_slots'])) {
    //         $available_slots = array_map(function ($time_slot) {
    //             return ["time" => $time_slot, "is_available" => 1];
    //         }, $time_slots['available_slots']);
    //     }
    //     if (isset($time_slots['busy_slots']) && !empty($time_slots['busy_slots'])) {
    //         $busy_slots = array_map(function ($time_slot) {
    //             return ["time" => $time_slot, "is_available" => 0];
    //         }, $time_slots['busy_slots']);
    //     }



    //     $time_slots['all_slots'] = array_merge($available_slots, $busy_slots);



    //     array_sort_by_multiple_keys($time_slots['all_slots'], ["time" => SORT_ASC]);


    //     $remaining_duration = $cart_data['total_duration'];

    //     $day = date('l', strtotime($date));
    //     $timings = getTimingOfDay($partner_id, $day);

    //     if (empty($timings)) {
    //         $response = [
    //             'error' => true,
    //             'message' => 'Provider is closed!',
    //             'data' => [],
    //         ];
    //         return $this->response->setJSON(remove_null_values($response));
    //     }

    //     $closing_time = $timings['closing_time'];
    //     $current_date = date('Y-m-d');
    //     $next_day_slots = get_next_days_slots($closing_time, $date, $partner_id, $cart_data['total_duration'], $current_date);

    //     if (count($next_day_slots) > 0) {

    //         $remaining_duration = $remaining_duration - 30;
    //         $number_of_slot = $remaining_duration / 30;
    //         $last_slot = count($time_slots['all_slots']) - 1;

    //         $loop_count = count($time_slots['all_slots']);



    //         for ($i = $loop_count - 1; $i >= ($loop_count - $number_of_slot); $i--) {
    //             if ($time_slots['all_slots'][$i]['is_available'] == "1") {
    //                 $time_slots['all_slots'][$i]['message'] = "Order scheduled for the multiple days";
    //             }
    //         }
    //     }



    //     $partner_timing = fetch_details('partner_timings', ['partner_id' => $partner_id, "day" => $whole_day]);
    //     if (!empty($partner_data) && $partner_data[0]['advance_booking_days'] > 0) {
    //         $allowed_advanced_booking_days = $partner_data[0]['advance_booking_days'];
    //         $current_date = new DateTime();
    //         $max_available_date = $current_date->modify("+ $allowed_advanced_booking_days day")->format('Y-m-d');
    //         if ($date > $max_available_date) {
    //             $response = [
    //                 'error' => true,
    //                 'message' => "You'can not choose date beyond available booking days which is + $allowed_advanced_booking_days days",
    //                 'data' => [],
    //             ];
    //             return $this->response->setJSON(remove_null_values($response));
    //         }
    //     } else if (!empty($partner_data) && $partner_data[0]['advance_booking_days'] == 0) {
    //         $current_date = new DateTime();
    //         if ($date > $current_date->format('Y-m-d')) {
    //             $response = [
    //                 'error' => true,
    //                 'message' => "Advanced Booking for this partner is not available",
    //                 'data' => [],
    //             ];
    //             return $this->response->setJSON(remove_null_values($response));
    //         }
    //     } else {
    //         $response = [
    //             'error' => true,
    //             'message' => "No Partner Found",
    //             'data' => [],
    //         ];
    //         return $this->response->setJSON(remove_null_values($response));
    //     }

    //     if (!empty($time_slots)) {
    //         $response = [
    //             'error' => $time_slots['error'],
    //             'message' => ($time_slots['error'] == false) ? 'Found Time slots' : $time_slots['message'],
    //             'data' => [
    //                 'all_slots' => (!empty($time_slots) && $time_slots['error'] == false) ? $time_slots['all_slots'] : [],
    //                 // 'available_slots' => (!empty($time_slots) && $time_slots['error'] == false) ? $time_slots['available_slots'] : [],
    //                 // 'busy_slots' => (!empty($time_slots) && $time_slots['error'] == false) ? $time_slots['busy_slots'] : []
    //             ],
    //         ];
    //         return $this->response->setJSON(remove_null_values($response));
    //     } else {
    //         $response = [
    //             'error' => true,
    //             'message' => 'No slot is available on this date!',
    //             'data' => [],
    //         ];
    //         return $this->response->setJSON(remove_null_values($response));
    //     }
    //     // } catch (\Exception $th) {
    //     //     $response['error'] = true;
    //     //     $response['message'] = 'Something went wrong';
    //     //     return $this->response->setJSON($response);
    //     // }
    // }

    public function get_available_slots()
    {
        // try {
        //     /*
        //         partner_id : 15
        //         date : 2022-11-01
        //     */
        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'partner_id' => 'required|numeric',
                'date' => 'required|valid_date[Y-m-d]',
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $days = [
            'Mon' => 'monday',
            'Tue' => 'tuesday',
            'Wed' => 'wednesday',
            'Thu' => 'thursday',
            'Fri' => 'friday',
            'Sat' => 'saturday',
            'Sun' => 'sunday',
        ];

        $partner_id = $this->request->getPost('partner_id');
        $date = $this->request->getPost('date');
        $time = $this->request->getPost('date');
        $date = new DateTime($date);
        $date = $date->format('Y-m-d');
        $day = date('D', strtotime($date));
        $whole_day = $days[$day];
        $partner_data = fetch_details('partner_details', ['partner_id' => $partner_id], ['advance_booking_days']);


        //for passing total duartion 
        // $user_cart_data = fetch_details('cart', ['user_id' => $this->user_details['id']]);

        $cart_data = fetch_cart(true, $this->user_details['id']);

        $duration = 0;





        if ($this->request->getPost('order_id')) {

            $order = fetch_details('order_services', ['order_id' => $this->request->getPost('order_id')]);
            $service_ids = [];
            foreach ($order as $row) {
                $service_ids[] = $row['service_id'];
            }
            $total_duration = 0;
            foreach ($service_ids as $row) {
                $service_data = fetch_details('services', ['id' => $row])[0];
                $total_duration = $total_duration + $service_data['duration'];
            }
            $time_slots = get_available_slots($partner_id, $date, isset($total_duration) ? $total_duration : 0); //working
        } else {


            $time_slots = get_available_slots($partner_id, $date, isset($cart_data['total_duration']) ? $cart_data['total_duration'] : 0); //working
        }


        $available_slots = $busy_slots = $time_slots['all_slots'] = [];





        if (isset($time_slots['available_slots']) && !empty($time_slots['available_slots'])) {
            $available_slots = array_map(function ($time_slot) {
                return ["time" => $time_slot, "is_available" => 1];
            }, $time_slots['available_slots']);
        }
        if (isset($time_slots['busy_slots']) && !empty($time_slots['busy_slots'])) {
            $busy_slots = array_map(function ($time_slot) {
                return ["time" => $time_slot, "is_available" => 0];
            }, $time_slots['busy_slots']);
        }



        $time_slots['all_slots'] = array_merge($available_slots, $busy_slots);



        array_sort_by_multiple_keys($time_slots['all_slots'], ["time" => SORT_ASC]);


        $remaining_duration = isset($cart_data['total_duration']) ? $cart_data['total_duration'] : 0;

        $day = date('l', strtotime($date));
        $timings = getTimingOfDay($partner_id, $day);

        if (empty($timings)) {
            $response = [
                'error' => true,
                'message' => 'Provider is closed!',
                'data' => [],
            ];
            return $this->response->setJSON(remove_null_values($response));
        }

        $closing_time = $timings['closing_time'];
        $current_date = date('Y-m-d');
        $next_day_slots = get_next_days_slots($closing_time, $date, $partner_id, isset($cart_data['total_duration']) ? $cart_data['total_duration'] : 0, $current_date);

        if (count($next_day_slots) > 0) {

            $remaining_duration = $remaining_duration - 30;
            $number_of_slot = $remaining_duration / 30;
            $last_slot = count($time_slots['all_slots']) - 1;

            $loop_count = count($time_slots['all_slots']);



            for ($i = $loop_count - 1; $i >= ($loop_count - $number_of_slot); $i--) {
                if ($time_slots['all_slots'][$i]['is_available'] == "1") {
                    $time_slots['all_slots'][$i]['message'] = "Order scheduled for the multiple days";
                }
            }
        }



        $partner_timing = fetch_details('partner_timings', ['partner_id' => $partner_id, "day" => $whole_day]);
        if (!empty($partner_data) && $partner_data[0]['advance_booking_days'] > 0) {
            $allowed_advanced_booking_days = $partner_data[0]['advance_booking_days'];
            $current_date = new DateTime();
            $max_available_date = $current_date->modify("+ $allowed_advanced_booking_days day")->format('Y-m-d');
            if ($date > $max_available_date) {
                $response = [
                    'error' => true,
                    'message' => "You'can not choose date beyond available booking days which is + $allowed_advanced_booking_days days",
                    'data' => [],
                ];
                return $this->response->setJSON(remove_null_values($response));
            }
        } else if (!empty($partner_data) && $partner_data[0]['advance_booking_days'] == 0) {
            $current_date = new DateTime();
            if ($date > $current_date->format('Y-m-d')) {
                $response = [
                    'error' => true,
                    'message' => "Advanced Booking for this partner is not available",
                    'data' => [],
                ];
                return $this->response->setJSON(remove_null_values($response));
            }
        } else {
            $response = [
                'error' => true,
                'message' => "No Partner Found",
                'data' => [],
            ];
            return $this->response->setJSON(remove_null_values($response));
        }

        if (!empty($time_slots)) {
            $response = [
                'error' => $time_slots['error'],
                'message' => ($time_slots['error'] == false) ? 'Found Time slots' : $time_slots['message'],
                'data' => [
                    'all_slots' => (!empty($time_slots) && $time_slots['error'] == false) ? $time_slots['all_slots'] : [],
                    // 'available_slots' => (!empty($time_slots) && $time_slots['error'] == false) ? $time_slots['available_slots'] : [],
                    // 'busy_slots' => (!empty($time_slots) && $time_slots['error'] == false) ? $time_slots['busy_slots'] : []
                ],
            ];
            return $this->response->setJSON(remove_null_values($response));
        } else {
            $response = [
                'error' => true,
                'message' => 'No slot is available on this date!',
                'data' => [],
            ];
            return $this->response->setJSON(remove_null_values($response));
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    public function get_ratings()
    {
        try {
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'partner_id' => 'permit_empty',
                ],
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $limit = (isset($_POST['limit']) && !empty($_POST['limit'])) ? $_POST['limit'] : 10;
            $offset = (isset($_POST['offset']) && !empty($_POST['offset'])) ? $_POST['offset'] : 0;
            $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $_POST['sort'] : 'id';
            $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $_POST['order'] : 'ASC';
            $search = (isset($_POST['search']) && !empty($_POST['search'])) ? $_POST['search'] : '';
            $partner_id = ($this->request->getPost('partner_id') != '') ? $this->request->getPost('partner_id') : '';

            // Define the default sort column and order
            $defaultSort = 'id';
            $defaultOrder = 'ASC';

            // Check if the provided sort column is valid
            $validSortColumns = ['id', 'rating', 'created_at'];
            if (in_array($sort, $validSortColumns)) {
                $defaultSort = $sort;
            }
            // Check if the provided order is valid
            $validOrders = ['ASC', 'DESC'];
            if (in_array($order, $validOrders)) {
                $defaultOrder = $order;
            }

            if (!empty($this->request->getPost('service_id'))) {

                $where = "s.user_id={$partner_id} AND service_id={$this->request->getPost('service_id')}";
            } else {


                $where = "s.user_id={$partner_id} ";
            }

            // Set the sort column and order for the ratings query
            $sortColumn = $defaultSort;
            $sortOrder = $defaultOrder;

            // If sorting by rating or rating date, override the default sort column and order
            if ($sort === 'rating' || $sort === 'created_at') {
                $sortColumn = $sort;
                $sortOrder = $order;
            }

            // $where=['s.user_id' => $partner_id];
            $ratings = new Service_ratings_model();
            if ($partner_id != '') {
                $data = $ratings->ratings_list(true, $search, $limit, $offset, $sort, $order, $where);
            } else {
                $data = $ratings->ratings_list(true, $search, $limit, $offset, $sort, $order, $where);
            }


            return response('Data Retrieved successfully', false, remove_null_values($data['data']), 200, ['total' => $data['total']]);
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function add_rating()
    {
        try {
            $validation = \Config\Services::validation();
            $ratings_model = new Service_ratings_model();
            $validation->setRules(
                [
                    'service_id' => 'required|numeric',
                    'rating' => 'required|numeric|greater_than[0]|less_than_equal_to[5]',
                    'comment' => 'permit_empty',
                ],
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $user_id = $this->user_details['id'];
            $service_id = $this->request->getPost('service_id');
            $orders = has_ordered($user_id, $service_id);

            // print_r($service_id);
            // die;
            if ($orders['error'] == true) {
                return response($orders['message'], true, [], 200);
            }
            $rd = fetch_details('services_ratings', ['user_id' => $user_id, 'service_id' => $service_id]);
            if (empty($rd)) {
                $rating = $this->request->getPost('rating');
                $comment = (isset($_POST['comment']) && $_POST['comment'] != "") ? $this->request->getPost('comment') : "";
                $uploaded_images = $this->request->getFiles('images');
                $data = [
                    'user_id' => $user_id,
                    'service_id' => $service_id,
                    'rating' => $rating,
                    'comment' => $comment,
                ];
                $names = "";
                $image_names['name'] = [];
                $data['images'] = [];
                $path = "public/uploads/ratings/";
                if (isset($uploaded_images['images'])) {
                    foreach ($uploaded_images['images'] as $images) {
                        $validate_image = valid_image($images);
                        if ($validate_image == true) {
                            return response("Invalid Image", true, []);
                        }
                        $newName = $images->getRandomName();
                        if ($newName != null) {
                            move_file($images, $path, $newName);
                            $name = "public/uploads/ratings/$newName";
                            array_push($image_names['name'], $name);
                        }
                    }
                    $names = json_encode($image_names['name']);
                }
                $data['images'] = $names;
                $saved_data = $ratings_model->save($data);
                if ($saved_data) {
                    update_ratings($service_id, $rating);
                    return response("Rating Saved", false, remove_null_values($data), 200);
                } else {
                    return response("Could not save ratings", true, [], 200);
                }
            } else {
                $rating_id = $rd[0]['id'];
                $rating = (isset($_POST['rating'])) ? $this->request->getPost('rating') : "";
                $comment = (isset($_POST['comment'])) ? $this->request->getPost('comment') : "";
                $data = [
                    'rating' => ($rating != "") ? $rating : $rd[0]['rating'],
                    'comment' => ($comment != "") ? $comment : $rd[0]['comment'],
                ];
                $data['images'] = [];
                $uploaded_images = $this->request->getFiles('images');
                $path = "public/uploads/ratings/";
                if (isset($uploaded_images['images'])) {
                    foreach ($uploaded_images['images'] as $images) {
                        $validate_image = valid_image($images);
                        if ($validate_image == true) {
                            return response("Invalid Image", true, []);
                        }
                        $newName = $images->getRandomName();
                        if ($newName == null) {
                            $image = null;
                        } else {
                            move_file($images, $path, $newName);
                            $name = "public/uploads/ratings/$newName";
                            array_push($data['images'], $name);
                        }
                    }
                    $data['images'] = json_encode($data['images']);
                } else {
                    $data['images'] = $rd[0]['images'];
                }
                $updated_data = $ratings_model->update($rating_id, $data);
                if ($updated_data) {
                    update_ratings($service_id, $rating);
                    return response("Rating Updated Successfully", false, [], 200);
                } else {
                    return response("Rating couldn't be Updated", true, [], 200);
                }
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function update_rating()
    {
        try {
            $validation = \Config\Services::validation();
            $ratings_model = new Service_ratings_model();
            $validation->setRules(
                [
                    'rating_id' => 'required',
                    'rating' => 'permit_empty',
                    'comment' => 'permit_empty',
                    'image' => 'permit_empty',
                ],
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $user_id = $this->user_details['id'];
            $rating_id = $this->request->getPost('rating_id');
            $ratings = has_rated($user_id, $rating_id);
            // print_r($ratings);
            if ($ratings['error']) {
                return response($ratings['message'], true, [], 200);
            }
            // echo "<pre>";
            // print_r($ratings['data'][0]);
            // return;
            $rating = (isset($_POST['rating'])) ? $this->request->getPost('rating') : "";
            $comment = (isset($_POST['comment'])) ? $this->request->getPost('comment') : "";
            if ($rating > 5) {
                return response("Can not rate More than 5", true, [], 200);
            }
            $data = [
                'rating' => ($rating != "") ? $rating : $ratings['data'][0]['rating'],
                'comment' => ($comment != "") ? $comment : $ratings['data'][0]['comment'],
            ];
            $data['images'] = [];
            $uploaded_images = $this->request->getFiles('images');
            $path = "public/uploads/ratings/";
            if (isset($uploaded_images['images'])) {
                // $og_images = json_decode($ratings['data'][0]['images']);
                // foreach ($og_images as $og_image) {
                //     unlink($og_image);
                // }
                foreach ($uploaded_images['images'] as $images) {
                    $validate_image = valid_image($images);
                    if ($validate_image == true) {
                        return response("Invalid Image", true, []);
                    }
                    $newName = $images->getRandomName();
                    if ($newName == null) {
                        $image = null;
                    } else {
                        move_file($images, $path, $newName);
                        $name = "public/uploads/ratings/$newName";
                        array_push($data['images'], $name);
                    }
                }
                $data['images'] = json_encode($data['images']);
            } else {
                $data['images'] = $ratings['data'][0]['images'];
            }
            $updated_data = $ratings_model->update($rating_id, $data);
            if ($updated_data) {
                return response("Ranking Updated Successfully", false, [], 200);
            } else {
                return response("Ranking Updated UnSuccessful", true, [], 200);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function is_area_deliverable()
    {
        try {
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'partner_id' => 'required',
                    'latitude' => 'permit_empty',
                    'longitude' => 'permit_empty',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $partner_id = $this->request->getPost('partner_id');
            $manual_latitude = $this->request->getPost('latitude');
            $manual_longitude = $this->request->getPost('longitude');
            $user_id = $this->user_details['id'];
            $lat1 = (!isset($manual_latitude) && $manual_latitude == "") ? $this->user_details['latitude'] : $manual_latitude;
            $lon1 = (!isset($manual_longitude) && $manual_longitude == "") ? $this->user_details['longitude'] : $manual_longitude;
            $partner_cred = fetch_details('users', ['id' => $partner_id])[0];
            $partner_details = fetch_details('partner_details', ['partner_id' => $partner_id])[0];
            $lat2 = $partner_cred['latitude'];
            $lon2 = $partner_cred['longitude'];
            $range = $partner_details['service_range'];
            $units = get_settings('range_units');
            $unit = 'k';
            $data = distance_finder($lat1, $lon1, $lat2, $lon2, $unit);
            $distance['distance'] = $data;
            if ($data == 0) {
                return response('Yes!, service is available', false, remove_null_values($distance), 200);
            } else if ($data <= $range) {
                return response('Yes!, service is available', false, remove_null_values($distance), 200);
            } else {
                return response('No!, service is not available', true, remove_null_values($distance), 200);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    //without reorder
    // public function check_available_slot()
    // {



    //     // start
    //     // Usage example

    //     // end

    //     // try {
    //     //     /*
    //     //         partner_id : 15
    //     //         date : 2022-11-01
    //     //         time:12:35:00
    //     //     */
    //     $validation = \Config\Services::validation();
    //     $validation->setRules(
    //         [
    //             'partner_id' => 'required|numeric',
    //             'date' => 'required|valid_date[Y-m-d]',
    //             'time' => 'required',
    //         ]
    //     );
    //     if (!$validation->withRequest($this->request)->run()) {
    //         $errors = $validation->getErrors();
    //         $response = [
    //             'error' => true,
    //             'message' => $errors,
    //             'data' => [],
    //         ];
    //         return $this->response->setJSON($response);
    //     }
    //     $partner_id = $this->request->getPost('partner_id');
    //     $date = $this->request->getPost('date');
    //     $time = $this->request->getPost('time');

    //     $cart_data = fetch_cart(true, $this->user_details['id']);
    //     if (empty($cart_data)) {
    //         return response("Please add some item in cart", true);
    //     }
    //     $service_total_duration = 0;
    //     $service_duration = 0;
    //     foreach ($cart_data['data'] as $main_data) {
    //         $service_duration = ($main_data['servic_details']['duration']) * $main_data['qty'];
    //         $service_total_duration = $service_total_duration + $service_duration;
    //     }
    //     $data = checkPartnerAvailability($partner_id, $date . ' ' . $time, $service_total_duration, $date, $time);


    //     return $this->response->setJSON($data);
    //     // } catch (\Exception $th) {
    //     //     $response['error'] = true;
    //     //     $response['message'] = 'Something went wrong';
    //     //     return $this->response->setJSON($response);
    //     // }
    // }


    //working

    public function check_available_slot()
    {



        // start
        // Usage example

        // end

        // try {
        //     /*
        //         partner_id : 15
        //         date : 2022-11-01
        //         time:12:35:00
        //     */
        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'partner_id' => 'required|numeric',
                'date' => 'required|valid_date[Y-m-d]',
                'time' => 'required',
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $partner_id = $this->request->getPost('partner_id');
        $date = $this->request->getPost('date');
        $time = $this->request->getPost('time');


        if ($this->request->getPost('order_id')) {
            $order = fetch_details('order_services', ['order_id' => $this->request->getPost('order_id')]);
            $service_ids = [];
            foreach ($order as $row) {
                $service_ids[] = $row['service_id'];
            }
            $service_total_duration = 0;
            foreach ($service_ids as $row) {
                $service_data = fetch_details('services', ['id' => $row])[0];
                $service_total_duration = $service_total_duration + $service_data['duration'];
            }
        } else {
            $cart_data = fetch_cart(true, $this->user_details['id']);
            if (empty($cart_data)) {
                return response("Please add some item in cart", true);
            }
            $service_total_duration = 0;
            $service_duration = 0;
            foreach ($cart_data['data'] as $main_data) {
                $service_duration = ($main_data['servic_details']['duration']) * $main_data['qty'];
                $service_total_duration = $service_total_duration + $service_duration;
            }
        }



        $data = checkPartnerAvailability($partner_id, $date . ' ' . $time, $service_total_duration, $date, $time);

        return $this->response->setJSON($data);
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }

    public function razorpay_create_order()
    {
        try {
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'order_id' => 'required|numeric',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $order_id = $this->request->getPost('order_id');
            if ($this->request->getPost('order_id') && !empty($this->request->getPost('order_id'))) {
                $where['o.id'] = $this->request->getPost('order_id');
            }
            $orders = new Orders_model();
            $order_detail = $orders->list(true, "", null, null, "", "", $where);
            $settings = get_settings('payment_gateways_settings', true);
            if (!empty($order_detail) && !empty($settings)) {
                $currency = $settings['razorpay_currency'];
                $price = $order_detail['data'][0]['final_total'];
                $amount = intval($price * 100);
                $create_order = $this->razorpay->create_order($amount, $order_id, $currency);
                if (!empty($create_order)) {
                    $response = [
                        'error' => false,
                        'message' => 'razorpay order created',
                        'data' => $create_order,
                    ];
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'razorpay order not created',
                        'data' => [],
                    ];
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => 'details not found"',
                    'data' => [],
                ];
            }
            return $this->response->setJSON($response);
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function update_service_status()
    {
        try {
            //     /*
            //         order_id : 149
            //         service_id : 17
            //         status : pending/awaiting/confirmed/rescheduled/cancelled/completed
            //     */
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'service_id' => 'required|numeric',
                    'status' => 'required',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $order_id = $this->request->getPost('order_id');
            $service_id = $this->request->getPost('service_id');
            $status = strtolower($this->request->getPost('status'));
            $all_status = ['pending', 'awaiting', 'confirmed', 'rescheduled', 'cancelled', 'completed'];
            if (in_array(strtolower($status), $all_status)) {
                $res = update_details(['status' => $status], ['service_id' => $service_id, 'order_id' => $order_id], 'order_services');
                $data = fetch_details('order_services', ['service_id' => $service_id, 'order_id' => $order_id]);
                if ($res) {
                    $response = [
                        'error' => false,
                        'message' => 'Service status updated successfully!',
                        'data' => $data,
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'Service status cant be changed!',
                        'data' => [],
                    ];
                    return $this->response->setJSON($response);
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Please enter valid status!',
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function get_faqs()
    {
        try {
            /*
        limit:10                {optional}
        offset:0                {optional}
        sort:id                 {optional}
        order:asc               {optional}
        search:test             {optional}
         */
            $Faqs_model = new Faqs_model();
            $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $data = $Faqs_model->list(true, $search, $limit, $offset, $sort, $order);
            if (!empty($data['data'])) {
                return response('faqs fetched successfully', false, remove_null_values($data['data']), 200, ['total' => $data['total']]);
            } else {
                return response('faqs not found');
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function verify_user()
    {
        // 101:- Mobile number already registered and Active
        // 102:- Mobile number is not registered
        // 103:- Mobile number is Deactive (edited) 
        // try {
        $config = new \Config\IonAuth();
        $validation = \Config\Services::validation();
        $request = \Config\Services::request();
        $identity_column = $config->identity;
        $identity = $request->getPost('mobile');
        $country_code = $request->getPost('country_code');



        $db      = \Config\Database::connect();

        $builder = $db->table('users u');

        $builder->select('u.*,ug.group_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('ug.group_id', "2")
            ->where('u.phone', $identity);

        $user = $builder->get()->getResultArray();
        // print_r($user);
        // die;
        // $user = fetch_details('users', ["phone" => $identity]);


        if (!empty($user)) {
            $fetched_country_code = $user[0]['country_code'];
            $fetched_user_mobile = $user[0]['phone'];
            if (($fetched_user_mobile == $identity) && ($fetched_country_code == $country_code)) {
                if (($user[0]['active'] == 1)) {
                    $response = [
                        'error' => true,
                        'message_code' => "101",
                    ];
                } else {
                    $response = [
                        'error' => true,
                        'message_code' => "103",
                    ];
                }
            } else if (($fetched_user_mobile == $identity)) {
                $data = fetch_details('users', ["phone" => $identity], $this->user_data)[0];
                $data['country_code'] = $update_data['country_code'] = $this->request->getPost('country_code');
                update_details($update_data, ['phone' => $identity], "users", false);
                if (($user[0]['active'] == 1)) {
                    $response = [
                        'error' => true,
                        'message_code' => "101",
                    ];
                } else {
                    $response = [
                        'error' => true,
                        'message_code' => "103",
                    ];
                }
            } else if (($fetched_user_mobile != $identity)) {

                $response = [
                    'error' => false,
                    'message_code' => "102",
                ];
            } else if (($fetched_user_mobile != $identity) && ($fetched_country_code != $country_code)) {
                $response = [
                    'error' => false,
                    'message_code' => "102",
                ];
            }
        } else {
            $response = [
                'error' => false,
                'message_code' => "102",
            ];
        }


        return $this->response->setJSON($response);
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    public function delete_user_account()
    {
        try {
            $user_id = $this->user_details['id'];
            if (!exists(['id' => $user_id], 'users')) {
                return response('user does not exist please enter valid user ID!', true);
            }
            $user_data = fetch_details('users_groups', ['user_id' => $user_id]);
            if (!empty($user_data) && isset($user_data[0]['group_id']) && !empty($user_data[0]['group_id']) && $user_data[0]['group_id'] == 2) {
                if (delete_details(['id' => $user_id], 'users') && delete_details(['user_id' => $user_id], 'users_groups')) {
                    delete_details(['user_id' => $user_id], 'users_tokens');
                    return response('User account deleted successfully', false);
                } else {
                    return response('User account does not delete', true);
                }
            } else {
                return response("This user's account can't delete ", true);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
  
    public function provider_check_availability()
    {


        // try {
        $db = \Config\Database::connect();
        $customer_latitude = $this->request->getPost('latitude');
        $customer_longitude = $this->request->getPost('longitude');
        $settings = get_settings('general_settings', true);
        $general_settings = fetch_details('settings', ['variable' => 'general_settings']);
        $builder = $db->table('users u');
        $sql_distance = $having = '';
        $distance = $settings['max_serviceable_distance'];
        if ($this->request->getPost('is_checkout_process') == '1') {
            $limit = !empty($this->request->getPost('limit')) ? $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $where = [];




            if (!empty($this->request->getPost('order_id'))) {
                $order_details = fetch_details('orders', ['id' => ($this->request->getPost('order_id')), 'user_id' => $this->user_details['id']]);
            } else {

                $cart_details = fetch_cart(true, $this->user_details['id'], $search, $limit, $offset, $sort, $order, $where);
            }

            if (!empty($this->request->getPost('order_id'))) {
                $provider_data = fetch_details('users', ['id' => $order_details[0]['partner_id']]);
            } else {

                $provider_data = fetch_details('users', ['id' => $cart_details['provider_id']]);
            }
            $provider_latitude = $provider_data[0]['latitude'];
            $provider_longitude = $provider_data[0]['longitude'];
            $partners = $builder->Select("u.username,u.city,u.latitude,u.longitude,u.id,p.company_name,u.image ,st_distance_sphere(POINT($customer_longitude, $customer_latitude), POINT($provider_longitude, $provider_latitude ))/1000  as distance")
                ->join('users_groups ug', 'ug.user_id=u.id')
                // ->join('subscriptions s', 's.id=ps.subscription_id')
                ->join('partner_details p', 'p.partner_id=u.id')
                ->where('p.is_approved', '1')
                ->where('ug.group_id', '3')
                ->where('u.id', $provider_data[0]['id'])
                ->having('distance < ' . $distance)
                ->orderBy('distance')
                ->get()->getResultArray();



            foreach ($partners as &$partner) {
                if (!empty($partner['image'])) {
                    $partner['image'] = base_url() . '/' . $partner['image'];
                }
            }
            if (!empty($partners)) {
                $response = [
                    'error' => false,
                    'message' => "Provider is available",
                    "data" => $partners
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => "Provider is not available",
                ];
            }
        } else {




            $partners = $builder->Select("u.username, u.city, u.latitude, u.longitude, p.company_name, u.image, u.id, st_distance_sphere(POINT($customer_longitude, $customer_latitude), POINT(`longitude`, `latitude`)) / 1000 as distance,
            (SELECT COUNT(*) FROM orders o WHERE o.partner_id = u.id AND o.parent_id IS NULL AND o.created_at > ps.purchase_date) as number_of_orders, ps.max_order_limit, ps.order_type")
                ->join('users_groups ug', 'ug.user_id=u.id')
                ->join('partner_subscriptions ps', 'ps.partner_id = u.id', 'left')
                ->join('partner_details p', 'p.partner_id=u.id')
                ->where('ps.status', 'active')
                ->where('ug.group_id', '3')
                ->having('(number_of_orders < max_order_limit OR number_of_orders = 0 OR order_type = "unlimited")')
                ->having('distance < ' . $distance)
                ->orderBy('distance')
                ->get()->getResultArray();



            foreach ($partners as &$partner) {
                if (!empty($partner['image'])) {
                    $partner['image'] = base_url() . '/' . $partner['image'];
                }
            }


            if (!empty($partners)) {
                $response = [
                    'error' => false,
                    'message' => "Providers are available",
                    "data" => $partners
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => "Providers are not available",
                ];
            }
        }
        return $this->response->setJSON($response);
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
        // if (!empty($distance) && !empty($latitude) && !empty($longitude)) {
        //     $radius_km = $distance;
        //     $sql_distance = " ,(((acos(sin((" . $latitude . "*pi()/180)) * sin((`p`.`latitude`*pi()/180))+cos((" . $latitude . "*pi()/180)) * cos((`p`.`latitude`*pi()/180)) * cos(((" . $longitude . "-`p`.`longitude`)*pi()/180))))*180/pi())*60*1.1515*1.609344) as distance ";
        //     $having = " HAVING (distance <= $radius_km) ";
        //     $order_by = ' distance ASC ';
        // } else {
        //     $order_by = ' p.id DESC ';
        // }
        // $db = \Config\Database::connect();
        // $query = $db->query("SELECT p.*" . $sql_distance . " FROM restaurants p $having ORDER BY $order_by");
        // $results = $query->getResultArray();
        // print_r($results);
    }
    public function invoice_download()
    {
        try {
            $validation = \Config\Services::validation();
            $validation->setRules(
                [
                    'order_id' => 'required|numeric',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            $db      = \Config\Database::connect();
            $order_id = $this->request->getPost('order_id');
            $this->orders = new Orders_model();
            $orders  = fetch_details('orders', ['id' => $order_id]);
            if (isset($orders) && empty($orders)) {
                return redirect('admin/orders');
            }
            $order_details = $this->orders->invoice($order_id)['order'];
            $partner_id = $order_details['partner_id'];
            $partner_details = $db
                ->table('partner_details pd')
                ->select('pd.company_name,pd.address, u.*')
                ->join('users u', 'u.id = pd.partner_id')
                // ->join('cities c', 'c.id = u.city_id')
                ->where('partner_id', $partner_id)->get()->getResultArray();
            $user_id = $order_details['user_id'];
            $user_details = $db
                ->table('users u')
                ->select('u.*')
                // ->join('cities c', 'c.id = u.city_id')
                ->where('u.id', $user_id)
                ->get()->getResultArray();
            $data = get_settings('general_settings', true);
            $this->data['currency'] = $data['currency'];
            $this->data['order'] = $order_details;
            $this->data['partner_details'] = $partner_details[0];
            $this->data['user_details'] = $user_details[0];
            $settings = get_settings('general_settings', true);
            $this->data['data'] = $settings;
            $orders  = fetch_details('orders', ['id' => $this->request->getPost('order_id')]);
            if (isset($orders) && empty($orders)) {
                return redirect('admin/orders');
            }
            $orders_model = new Orders_model();
            $data = get_settings('general_settings', true);
            $currency = $data['currency'];
            $tax = get_settings('system_tax_settings', true);
            $orders = $orders_model->invoice($order_id)['order'];
            $services = $orders['services'];
            $total =  count($services);
            if (!empty($orders)) {
                $i = 0;
                $total_tax_amount = 0;
                foreach ($services as $service) {
                    // print_R($service);
                    $rows[$i] = [
                        'service_title' => ucwords($service['service_title']),
                        'price' => $currency . number_format($service['price']),
                        'discount' => ($service['discount_price'] == 0) ? "0" : ($service['price'] - $service['discount_price']),
                        'net_amount' => ($service['discount_price'] != 0) ? $currency . number_format($service['discount_price']) : $currency . ($service['price']),
                        'tax' => ($service['tax_type'] == "excluded") ? $service['tax_percentage'] . '%' : '0%',
                        'tax_amount' => ($service['tax_type'] == "excluded") ? $service['tax_amount'] : 0,
                        'quantity' => ucwords($service['quantity']),
                        'subtotal' => $currency . (number_format($service['sub_total']))
                    ];
                    $i++;
                }
                $total_tax_amount =  ($orders['total'] * $tax['tax']) / 100;
                $empty_row = [
                    'service_title' => "",
                    'price' => "",
                    'discount' => "",
                    'net_amount' => "",
                    'tax' => "",
                    'tax_amount' => "",
                    'quantity' => "",
                    'subtotal' => "",
                ];
                $row = [
                    'service_title' => "",
                    'price' => "",
                    'discount' => "",
                    'net_amount' => "",
                    'tax' => "",
                    'tax_amount' => "",
                    'quantity' => "<strong class='text-dark  '>Total</strong>",
                    'subtotal' => "<strong class='text-dark '>" . $currency . (intval($orders['total'])) . "</strong>",
                    // 'subtotal' => "<strong>" . $currency . $orders['total'] . "</strong>",
                ];
                $tax = [
                    'service_title' => "",
                    'price' => "",
                    'discount' => "",
                    'net_amount' => "",
                    'tax' => "",
                    'tax_amount' => "",
                    'quantity' => "<strong class='text-dark '>Tax Amount</strong>",
                    'subtotal' => "<strong class='text-dark '>" . $currency . $total_tax_amount . "</strong>",
                ];
                $visiting_charges = [
                    'service_title' => "",
                    'price' => "",
                    'discount' => "",
                    'net_amount' => "",
                    'tax' => "",
                    'tax_amount' => "",
                    'quantity' => "<strong class='text-dark '>Visiting Charges</strong>",
                    'subtotal' => "<strong class='text-dark '>" . $currency . $orders['visiting_charges'] . "</strong>",
                ];
                // print_R($orders);
                // $promo_code_discount_amount = (($orders['total'] + $orders['visiting_charges']) * $orders['promo_discount']) / 100;
                $promo_code_discount = [
                    'service_title' => "",
                    'price' => "",
                    'discount' => "",
                    'net_amount' => "",
                    'tax' => "",
                    'tax_amount' => "",
                    'quantity' => "<strong class='text-dark '>Promo Code Discount</strong>",
                    'subtotal' => "<strong class='text-dark '>" . $currency . $orders['promo_discount'] . "</strong>",
                ];
                $payble_amount = $orders['total'] + $orders['visiting_charges'] - $orders['promo_discount'];
                $final_total = [
                    'service_title' => "",
                    'price' => "",
                    'discount' => "",
                    'net_amount' => "",
                    'tax' => "",
                    'tax_amount' => "",
                    'quantity' => "<strong class='text-dark '>Final Total</strong>",
                    'subtotal' => "<strong class='text-dark '>" . $currency . $payble_amount . "</strong>",
                ];
                $array['total'] = $total;
                $array['rows'] = $rows;
                $this->data['rows'] = $rows;
                $this->data['currency'] = $currency;
                // $this->data['rows'] = $rows;
                // print_r($this->data['rows']);
                // die;
                try {
                    $html =  view('backend/admin/pages/invoice_from_api', $this->data);
                    $path = "public/uploads/";
                    $mpdf = new \Mpdf\Mpdf(['tempDir' => $path]);
                    $stylesheet = file_get_contents('public/backend/assets/css/vendor/bootstrap-table.css');
                    $mpdf->WriteHTML($stylesheet, 1); // CSS Script goes here.
                    $mpdf->WriteHTML($html);
                    $this->response->setHeader("Content-Type", "application/pdf");
                    $mpdf->Output('order-ID-' . $order_details['id'] . "-invoice.pdf", 'I');
                } catch (\Mpdf\MpdfException $e) {
                    print "Creating an mPDF object failed with" . $e->getMessage();
                }
            } else {
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function get_paypal_link()
    {
        /*
            user_id : 2
            order_id : 1
            amount : 150
        */
        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'user_id' => 'required|numeric',
                'order_id' => 'required',
                'amount' => 'required',
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $user_id = $_POST['user_id'];
        $order_id = $_POST['order_id'];
        $amount = $_POST['amount'];
        $response = [
            'error' => false,
            'message' => 'Order Detail Founded !',
            'data' => base_url('/api/v1/paypal_transaction_webview?' . 'user_id=' . $user_id . '&order_id=' . $order_id . '&amount=' . intval($amount)),

        ];
        $token = $this->paypal_lib->generate_token();
        return $this->response->setJSON($token);
        print_r($token);
    }
    //paypal_transaction_webview()
    public function paypal_transaction_webview()
    {

        /*
           user_id : 2
           order_id : 1
       */
        header("Content-Type: html");
        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'user_id' => 'required|numeric',
                'order_id' => 'required',
                'amount' => 'required',
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }
        $user_id = $_GET['user_id'];
        $order_id = $_GET['order_id'];


        $amount = $_GET['amount'];


        $user = fetch_details('users', ['id' => $user_id]);

        if (empty($user)) {
            echo "user error update";
            return false;
        }
        // $order_res = $this->db->where('id', $order_id)->get('orders')->result_array();
        $order_res = fetch_details('orders', ['id' => $order_id]);


        $data['user'] = $user[0];
        $data['order'] = $order_res[0];
        $data['payment_type'] = "paypal";
        $settings = get_settings('payment_gateways_settings', true);




        $encryption = order_encrypt($user_id, $amount, $order_id);




        if (!empty($order_res)) {
            $data['user'] = $user[0];
            $data['order'] = $order_res[0];
            $data['payment_type'] = "paypal";

            // Set variables for paypal form
            $returnURL = base_url() . '/api/v1/app_payment_status';
            $cancelURL = base_url() . '/api/v1/app_payment_status?order_id=' . $encryption . '&payment_status=Failed';


            $notifyURL = base_url() . '/api/webhooks/paypal';
            $txn_id = time() . "-" . rand();
            // Get current user ID from the session
            $userID = $data['user']['id'];
            $order_id = $data['order']['id'];
            $payeremail = $data['user']['email'];   // Add fields to paypal form
            $this->paypal_lib->add_field('return', $returnURL);
            $this->paypal_lib->add_field('cancel_return', $cancelURL);
            $this->paypal_lib->add_field('notify_url', $notifyURL);
            $this->paypal_lib->add_field('item_name', 'Test');
            $this->paypal_lib->add_field('custom', $userID . '|' . $payeremail);
            $this->paypal_lib->add_field('item_number', $order_id);
            $this->paypal_lib->add_field('amount', $amount);
            // Render paypal form
            $this->paypal_lib->paypal_auto_form();
        } else {
            $data['user'] = $user[0];
            $data['payment_type'] = "paypal";
            // Set variables for paypal form
            $returnURL = base_url() . '/api/v1/app_payment_status';
            $cancelURL = base_url() . '/api/v1/app_payment_status';
            $notifyURL = base_url() . '/api/webhooks/paypal';
            $txn_id = time() . "-" . rand();
            // Get current user ID from the session
            $userID = $data['user']['id'];
            $order_id = $order_id;
            $payeremail = $data['user']['email'];
            $this->paypal_lib->add_field('return', $returnURL);
            $this->paypal_lib->add_field('cancel_return', $cancelURL);
            $this->paypal_lib->add_field('notify_url', $notifyURL);
            $this->paypal_lib->add_field('item_name', 'Online shopping');
            $this->paypal_lib->add_field('custom', $userID . '|' . $payeremail);
            $this->paypal_lib->add_field('item_number', $order_id);
            $this->paypal_lib->add_field('amount', $amount);
            // Render paypal form
            $this->paypal_lib->paypal_auto_form();
        }
    }


    public function app_payment_status()
    {


        $paypalInfo = $_GET;
        if (!empty($paypalInfo) && isset($_GET['st']) && strtolower($_GET['st']) == "completed") {
            $response['error'] = false;
            $response['message'] = "Payment Completed Successfully";
            $response['data'] = $paypalInfo;

            // $cutome_data = explode("|", $_GET['custom']);
            // $user_id = $cutome_data[0];
            // $order_id = $_GET['item_number'];
            // $total = $_GET['amt'];
            // $txn_id = $_GET['tx'];
            // add_transaction_for_place_order($user_id, $order_id, 'paypal', $total, $txn_id, '');

        } elseif (!empty($paypalInfo) && isset($_GET['st']) && strtolower($_GET['st']) == "authorized") {
            $response['error'] = false;
            $response['message'] = "Your payment is has been Authorized successfully. We will capture your transaction within 30 minutes, once we process your order. After successful capture coins wil be credited automatically.";
            $response['data'] = $paypalInfo;
        } elseif (!empty($paypalInfo) && isset($_GET['st']) && strtolower($_GET['st']) == "Pending") {
            $response['error'] = false;
            $response['message'] = "Your payment is pending and is under process. We will notify you once the status is updated.";
            $response['data'] = $paypalInfo;
        } else {



            $order_id = order_decrypt($_GET['order_id']);

            update_details(['payment_status' => 2], ['id' => $order_id[2]], 'orders');
            update_details(['status' => 'cancelled'], ['id' => $order_id[2]], 'orders');
            $data = [
                'transaction_type' => 'transaction',
                'user_id' => $order_id[0],
                'partner_id' => "",
                'order_id' => $order_id[2],
                'type' => 'paypal',
                'txn_id' => "",
                'amount' => $order_id[1],
                'status' => 'failed',
                'currency_code' => "",
                'message' => 'Order is cancelled',
            ];
            $insert_id = add_transaction($data);

            $response['error'] = true;
            $response['message'] = "Payment Cancelled / Declined ";
            $response['data'] = $_GET;
        }
        print_r(json_encode($response));
    }

    public function get_time_slots()
    {

        $config = new \Config\Database();

        // Create a new database connection
        $db = \Config\Database::connect($config->default);

        // Get the desired date and provider ID from the request
        $date = "2022-04-12";
        $provider_id = '50';

        // Get the provider's information from the database
        $provider = $db->table('partner_timings')->where('partner_id', $provider_id)->get()->getRow();



        // Get the provider's available time slots for the given date
        $available_time_slots = array();
        // $start_time = strtotime($provider->start_time);
        // $end_time = strtotime($provider->end_time);
        $provider_end_time = '18:00:00'; // set the end time to 6:00 PM

        $members_available = 3;
        $start_time = strtotime('09:00:00');
        $end_time = strtotime('17:00:00');
        $appointment_duration = 30; // in minutes
        while ($start_time < $end_time) {
            $formatted_start_time = date('H:i:s', $start_time);
            $formatted_end_time = date('H:i:s', strtotime($formatted_start_time) + ($appointment_duration * 60));

            // Check if the provider has any members available at this time slot
            $members_count = $db->table('orders')->where('partner_id', $provider_id)->where('date_of_service', $date)->where('starting_time >=', $formatted_start_time)->where('ending_time <', $formatted_end_time)->countAllResults();

            if ($members_count < $members_available) {
                // Check if the end time of the appointment is greater than the provider's end time
                $appointment_end_time = strtotime($formatted_end_time);
                $provider_end_time = strtotime($provider_end_time);

                if ($appointment_end_time <= $provider_end_time) {
                    $available_time_slots[] = $formatted_start_time;
                }
            }

            // Increment the start time
            $start_time = strtotime($formatted_end_time);
        }



        // Return the available time slots as a JSON response
        return $this->response->setJSON($available_time_slots);
    }

    // Function to check and update subscription status
    public function checkAndUpdateSubscriptionStatus($partnerId)
    {


        $partnerSubscriptionModel = new Partner_subscription_model();

        $subscriptionData = $partnerSubscriptionModel
            ->where('partner_id', $partnerId)
            ->where('status', 'active')
            ->where('order_type', 'limited')
            ->where('price !=', 0)
            ->first();

        if (!$subscriptionData) {
            return;
        }

        $orderModel = new Orders_model();
        $subscriptionCount = $orderModel
            ->where('partner_id', $partnerId)
            ->where('created_at >=', $subscriptionData['updated_at'])
            ->countAllResults();





        if ($subscriptionCount >= $subscriptionData['max_order_limit']) {

            $data['status'] = 'deactive';
            $where['partner_id'] = $partnerId;
            $where['status'] = 'active';
            update_details($data, $where, 'partner_subscriptions');
            //          

        }
    }

    public function verify_transaction()
    {


        $validation = service('validation');
        $validation->setRules([
            'order_id' => 'required|numeric',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }

        $transaction_model = new Transaction_model();
        $order_id = (int) $this->request->getVar('order_id');

        $transaction = fetch_details('transactions', ['order_id' => $order_id, 'user_id' => $this->user_details['id']]);

        $settings = get_settings('payment_gateways_settings', true);
        if (!empty($transaction)) {

            $transaction_id = $transaction[0]['txn_id'];
            $payment_gateways = $transaction[0]['type'];
            if ($payment_gateways == 'razorpay') {

                $razorpay = new Razorpay;
                $credentials = $razorpay->get_credentials();
                $secret = $credentials['secret'];
                $api = new Api($credentials['key'], $secret);
                $data = $api->payment->fetch($transaction_id);

                $status = $data->status;
                if ($status == "captured") {
                    $cart_data = fetch_cart(true, $this->user_details['id']);
                    if (!empty($cart_data)) {

                        foreach ($cart_data['data'] as $row) {
                            delete_details(['id' => $row['id']], 'cart');
                        }
                    }
                    $response = [
                        'error' => true,
                        'message' => 'verified',
                        'data' => [],
                    ];
                    return $this->response->setJSON($response);
                }
            }

            if ($payment_gateways == "paystack") {



                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . $transaction[0]['reference'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array(
                        "Authorization: Bearer " . $settings['paystack_secret'],
                        "Cache-Control: no-cache",
                    ),
                ));

                $response = curl_exec($curl);


                $err = curl_error($curl);

                curl_close($curl);
                $response = [
                    'error' => false,
                    'message' => 'verified',
                    'data' => json_decode($response),
                ];
                return $this->response->setJSON($response);
            }

            if ($payment_gateways == "paypal") {

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api-m.sandbox.paypal.com/v2/payments/captures/' . $transaction[0]['txn_id'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Basic ' . base64_encode($settings['paypal_client_key'] . ':' . $settings['paypal_secret_key']),
                        'Content-Type: application/json',
                        'Cookie: l7_az=ccg14.slc'
                    ),
                ));

                $response1 = curl_exec($curl);
                curl_close($curl);
                $response = [
                    'error' => false,
                    'message' => 'verified',
                    'data' => json_decode($response1),
                ];
                return $this->response->setJSON($response);

                echo $response;
            }
        }
    }

    public function contact_us_api()
    {



        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'name' => 'required',
                'subject' => 'required',
                'message' => 'required',
                'email' => 'required'
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }


        $name = $_POST['name'];
        $subject = $_POST['subject'];
        $message = $_POST['message'];
        $email = $_POST['email'];



        $admin_contact_query = [
            'user_id' => isset($this->user_details['id']) ? $this->user_details['id'] : "0",
            'name' => $name,
            'subject' => $subject,
            'message' => $message,
            'email' => isset($email) ? $email : "0",

        ];
        insert_details($admin_contact_query, 'admin_contact_query');

        $response['error'] = false;
        $response['message'] = "Query send successfully";
        $response['data'] = $admin_contact_query;
        return $this->response->setJSON($response);
    }


    public function search()
    {
        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'search' => 'required',
                'latitude' => 'required',
                'longitude' => 'required'


            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }

        $search =  $this->request->getPost('search');

        $Partners_model = new Partners_model();
        $settings = get_settings('general_settings', true);

        if (($this->request->getPost('latitude') && !empty($this->request->getPost('latitude')) && ($this->request->getPost('longitude') && !empty($this->request->getPost('longitude'))))) {
            $additional_data = [
                'latitude' => $this->request->getPost('latitude'),
                'longitude' => $this->request->getPost('longitude'),
                'max_serviceable_distance' => $settings['max_serviceable_distance'],
            ];
        }

        if (isset($additional_data['latitude']) && !empty($additional_data['latitude'])) {
            $latitude = $this->request->getPost('latitude');
            $longitude = $this->request->getPost('longitude');
            $is_latitude_set = " st_distance_sphere(POINT(' $longitude','$latitude'), POINT(`p`.`longitude`, `p`.`latitude` ))/1000  as distance";
        }
        $db = \Config\Database::connect();
        $builder1 = $db->table('users u1');

        $partners1 = $builder1->Select("u1.username,u1.city,u1.latitude,u1.longitude,u1.id,pc.minimum_order_amount,
                (SELECT COUNT(*) FROM orders o WHERE o.partner_id = u1.id AND o.parent_id IS NULL) as number_of_orders,st_distance_sphere(POINT($longitude, $latitude),
                 POINT(`longitude`, `latitude` ))/1000  as distance")
            ->join('users_groups ug1', 'ug1.user_id=u1.id')
            ->join('services s', 's.id=u1.id', 'left')
            ->join('services_ratings sr', 'sr.service_id = s.id', 'left')
            ->join('partner_subscriptions ps', 'ps.partner_id=u1.id')
            ->join('promo_codes pc', 'pc.partner_id=u1.id', 'left')
            ->where('ps.status', 'active')
            ->where('ug1.group_id', '3')

            ->having('distance < ' . $additional_data['max_serviceable_distance'])
            ->orderBy('distance')
            ->get()->getResultArray();
        $ids = [];
        foreach ($partners1 as $key => $row1) {
            $ids[] = $row1['id'];
        }

        foreach ($ids as $key => $id) {
            $partner_subscription = fetch_details('partner_subscriptions', ['partner_id' => $id, 'status' => 'active']);
            if ($partner_subscription) {
                $subscription_purchase_date = $partner_subscription[0]['updated_at'];
                $partner_order_limit = fetch_details('orders', ['partner_id' => $id, 'parent_id' => null, 'created_at >' => $subscription_purchase_date]);
                $partners_subscription = $db->table('partner_subscriptions ps');
                $partners_subscription_data = $partners_subscription->select('ps.*')->where('ps.status', 'active')
                    ->get()
                    ->getResultArray();

                $subscription_order_limit = $partners_subscription_data[0]['max_order_limit'];
                if ($partners_subscription_data[0]['order_type'] == "limited") {
                    if (count($partner_order_limit) >= $subscription_order_limit) {
                        unset($ids[$key]);
                    }
                }
            } else {

                unset($ids[$key]);
            }
        }

        $parent_ids = array_values($ids);
        if (is_array($ids) && !empty($ids)) {
            //    $partners = $db->table('services s');
            $builder = $db->table('partner_details pd');
            $partners = $builder->Select('p.id,p.username,p.company,pc.minimum_order_amount,p.image,pd.banner,pc.discount,pc.discount_type,
           count(sr.rating) as number_of_rating, 
           SUM(sr.rating) as total_rating,
           (SUM(sr.rating) / count(sr.rating)) as average_rating,
            (SELECT COUNT(*) FROM orders o WHERE o.partner_id = p.id AND o.parent_id IS NULL) as number_of_orders,pd.company_name,' . $is_latitude_set)
                ->join('services_ratings sr', 'sr.service_id = s.id', 'left')
                ->join('users p', 'p.id=s.user_id')
                ->join('partner_details pd', 'pd.partner_id=s.user_id')
                ->join('promo_codes pc', 'pc.partner_id=p.id', 'left')
                ->whereIn('s.user_id', $ids)
                ->where('pd.is_approved', '1');
            //    ->groupBy('p.id')
            //    ->get()
            //    ->getResultArray();
            if ($search and $search != '') {
                $multipleWhere = [
                    '`pd.id`' => $search,
                    '`pd.company_name`' => $search,
                    '`pd.tax_name`' => $search,
                    '`pd.tax_number`' => $search,
                    '`pd.bank_name`' => $search,
                    '`pd.account_number`' => $search,
                    '`pd.account_name`' => $search,
                    '`pd.bank_code`' => $search,
                    '`pd.swift_code`' => $search,
                    '`pd.created_at`' => $search,
                    '`pd.updated_at`' => $search,
                ];
            }
            if (isset($multipleWhere) && !empty($multipleWhere)) {
                $builder->orWhere($multipleWhere);
            }

            $partner_count = $builder->get()->getResultArray();
            for ($i = 0; $i < count($partners); $i++) {
                $partners[$i]['upto'] = $partners[$i]['minimum_order_amount'];
                if (!empty($partners[$i]['image'])) {
                    $image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $partners[$i]['image'])) ? base_url('public/backend/assets/profiles/' . $partners[$i]['image']) : ((file_exists(FCPATH . $partners[$i]['image'])) ? base_url($partners[$i]['image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $partners[$i]['image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $partners[$i]['image'])));
                    $partners[$i]['image'] = $image;
                    $banner_image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $partners[$i]['banner'])) ? base_url('public/backend/assets/profiles/' . $partners[$i]['banner']) : ((file_exists(FCPATH . $partners[$i]['banner'])) ? base_url($partners[$i]['banner']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $partners[$i]['banner'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $partners[$i]['banner'])));
                    $partners[$i]['banner_image'] = $banner_image;
                    unset($partners[$i]['banner']);

                    if ($partners[$i]['discount_type'] == 'percentage') {
                        $upto = $partners[$i]['minimum_order_amount'];
                        unset($partners[$i]['discount_type']);
                    }
                }


                unset($partners[$i]['minimum_order_amount']);
            }



            $parent_ids = implode(", ", $parent_ids);
            //    $tempRow['parent_ids'] = $parent_ids;
            $tempRow['partners'] = $partners;

            $rows[] = $tempRow;
            $data = $rows;
            return response('serach ', false, $data, 200);
        }
    }

    function getPlaceAddress()
    {

        try {
            $api_key = "AIzaSyD_oeOMoPl0IiplyFLwtNA_ShTRWaqRD8o";
            $latitude = "23.2651898";
            $longitude = "69.6581269";
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=$api_key";

            // Initialize cURL session
            $ch = curl_init($url);


            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            // Execute the cURL session and get the JSON response
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                echo 'Curl error: ' . curl_error($ch);
                return false;
            }

            // Close the cURL session
            curl_close($ch);

            // Parse the JSON response
            $data = json_decode($response);
            print_r($data);
            // Check if the request was successful
            if ($data->status == 'OK') {
                // Extract the formatted address
                $address = $data->results[0]->formatted_address;
                return $address;
            } else {
                return false;
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }



    //withput limit offset working proper 
    // function search_services_providers()
    // {

    //     $validation = \Config\Services::validation();
    //     $validation->setRules(
    //         [
    //             'search' => 'required',
    //             'latitude' => 'required',
    //             'longitude' => 'required',
    //             'type' => 'required'
    //         ]
    //     );
    //     if (!$validation->withRequest($this->request)->run()) {
    //         $errors = $validation->getErrors();
    //         $response = [
    //             'error' => true,
    //             'message' => $errors,
    //             'data' => [],
    //         ];
    //         return $this->response->setJSON($response);
    //     }

    //     $search = $this->request->getPost('search') ?? '';
    //     $latitude = $this->request->getPost('latitude') ?? '';
    //     $longitude = $this->request->getPost('longitude') ?? '';
    //     $db = \Config\Database::connect();

    //     $limit = $this->request->getPost('limit') ?? '5';
    //     $offset = $this->request->getPost('offset') ?? '0';


    //     $type = $this->request->getPost('type');
    //     $data = [];



    //     if ($type == "provider") {
    //         $settings = get_settings('general_settings', true);
    //         if (($this->request->getPost('latitude') && !empty($this->request->getPost('latitude')) && ($this->request->getPost('longitude') && !empty($this->request->getPost('longitude'))))) {
    //             $additional_data = [
    //                 'latitude' => $this->request->getPost('latitude'),
    //                 'longitude' => $this->request->getPost('longitude'),
    //                 'max_serviceable_distance' => $settings['max_serviceable_distance'],
    //             ];
    //         }
    //         $is_latitude_set = "";
    //         if (isset($additional_data['latitude']) && !empty($additional_data['latitude'])) {
    //             $latitude = $this->request->getPost('latitude');
    //             $longitude = $this->request->getPost('longitude');
    //             $is_latitude_set = " st_distance_sphere(POINT(' $longitude','$latitude'), POINT(`p`.`longitude`, `p`.`latitude` ))/1000  as distance";
    //         }
    //         $builder1 = $db->table('users u1');

    //         $partners1 = $builder1->Select("u1.username,u1.city,u1.latitude,u1.longitude,u1.id,pc.minimum_order_amount,pc.discount,pd.company_name,u1.image,pd.banner, pc.discount_type,
    //                ( count(sr.rating)) as number_of_rating,
    //                 ( SUM(sr.rating)) as total_rating,
    //                 ((SUM(sr.rating) / count(sr.rating))) as average_rating,
    //         (SELECT COUNT(*) FROM orders o WHERE o.partner_id = u1.id AND o.parent_id IS NULL) as number_of_orders,st_distance_sphere(POINT($longitude, $latitude),
    //          POINT(`longitude`, `latitude` ))/1000  as distance")
    //             ->join('users_groups ug1', 'ug1.user_id=u1.id')
    //             ->join('partner_details pd', 'pd.partner_id=u1.id')
    //             ->join('services s', 's.user_id=pd.partner_id', 'left')
    //             ->join('services_ratings sr', 'sr.service_id = s.id', 'left')
    //             ->join('partner_subscriptions ps', 'ps.partner_id=u1.id')
    //             ->join('promo_codes pc', 'pc.partner_id=u1.id', 'left')
    //             ->where('ps.status', 'active')
    //             ->where('ug1.group_id', '3')
    //             ->groupBy('pd.partner_id')
    //             ->having('distance < ' . $additional_data['max_serviceable_distance'])
    //             ->orderBy('distance')->limit($limit, $offset);



    //         // $partner_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();

    //         if ($search and $search != '') {
    //             $searchWhere = [
    //                 '`pd.id`' => $search,
    //                 '`pd.company_name`' => $search,
    //                 '`pd.tax_name`' => $search,
    //                 '`pd.tax_number`' => $search,
    //                 '`pd.bank_name`' => $search,
    //                 '`pd.account_number`' => $search,
    //                 '`pd.account_name`' => $search,
    //                 '`pd.bank_code`' => $search,
    //                 '`pd.swift_code`' => $search,
    //                 '`pd.created_at`' => $search,
    //                 '`pd.updated_at`' => $search,
    //                 '`u1.username`' => $search,
    //             ];

    //             if (isset($searchWhere) && !empty($searchWhere)) {
    //                 $builder1->groupStart();
    //                 $builder1->orLike($searchWhere);
    //                 $builder1->groupEnd();
    //             }
    //         }




    //         $partners1 = $builder1->get()->getResultArray();

    //         for ($i = 0; $i < count($partners1); $i++) {
    //             $partners1[$i]['upto'] = $partners1[$i]['minimum_order_amount'];
    //             if (!empty($partners1[$i]['image'])) {
    //                 $image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $partners1[$i]['image'])) ? base_url('public/backend/assets/profiles/' . $partners1[$i]['image']) : ((file_exists(FCPATH . $partners1[$i]['image'])) ? base_url($partners1[$i]['image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $partners1[$i]['image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $partners1[$i]['image'])));
    //                 $partners1[$i]['image'] = $image;
    //                 $banner_image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $partners1[$i]['banner'])) ? base_url('public/backend/assets/profiles/' . $partners1[$i]['banner']) : ((file_exists(FCPATH . $partners1[$i]['banner'])) ? base_url($partners1[$i]['banner']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $partners1[$i]['banner'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $partners1[$i]['banner'])));
    //                 $partners1[$i]['banner_image'] = $banner_image;
    //                 unset($partners1[$i]['banner']);
    //                 if ($partners1[$i]['discount_type'] == 'percentage') {
    //                     // $discount = $partners[$i]['discount'];
    //                     $upto = $partners1[$i]['minimum_order_amount'];
    //                     unset($partners1[$i]['discount_type']);
    //                 }
    //             }
    //             unset($partners1[$i]['minimum_order_amount']);
    //         }


    //         $ids = [];
    //         foreach ($partners1 as $key => $row1) {
    //             $ids[] = $row1['id'];
    //         }


    //         foreach ($ids as $key => $id) {
    //             $partner_subscription = fetch_details('partner_subscriptions', ['partner_id' => $id, 'status' => 'active']);
    //             if ($partner_subscription) {
    //                 $subscription_purchase_date = $partner_subscription[0]['updated_at'];
    //                 $partner_order_limit = fetch_details('orders', ['partner_id' => $id, 'parent_id' => null, 'created_at >' => $subscription_purchase_date]);
    //                 $partners_subscription = $db->table('partner_subscriptions ps');
    //                 $partners_subscription_data = $partners_subscription->select('ps.*')->where('ps.status', 'active')
    //                     ->get()
    //                     ->getResultArray();

    //                 $subscription_order_limit = $partners_subscription_data[0]['max_order_limit'];
    //                 if ($partners_subscription_data[0]['order_type'] == "limited") {
    //                     if (count($partner_order_limit) >= $subscription_order_limit) {
    //                         unset($ids[$key]);
    //                     }
    //                 }
    //             } else {

    //                 unset($ids[$key]);
    //             }
    //         }

    //         $parent_ids = array_values($ids);



    //         $parent_ids = implode(", ", $parent_ids);
    //         $data['total'] = count($partners1);
    //         $data['providers'] = $partners1;
    //     } else if ($type = "service") {

    //         // services 
    //         $multipleWhere = '';
    //         $db      = \Config\Database::connect();
    //         $builder = $db->table('services s');


    //         $services = $builder->select("s.*, c.name as category_name, p.username as partner_name, c.parent_id, pd.company_name, pd.at_store as provider_at_store, pd.at_doorstep as provider_at_doorstep, p.city,
    //             p.latitude, p.longitude, p.id as user_id, pd.banner, p.image,
    //             COALESCE(COUNT(sr.rating), 0) as number_of_rating,
    //             COALESCE(SUM(sr.rating), 0) as total_rating,
    //             (SELECT COUNT(*) FROM orders o WHERE o.partner_id = p.id AND o.parent_id IS NULL) as number_of_orders, st_distance_sphere(POINT($longitude, $latitude),
    //             POINT(p.longitude, p.latitude))/1000 as distance, pc.discount, pc.discount_type, pc.minimum_order_amount")
    //             ->join('users p', 'p.id=s.user_id', 'left')
    //             ->join('partner_details pd', 'pd.partner_id=s.user_id')
    //             ->join('partner_subscriptions ps', 'ps.partner_id=s.user_id')
    //             ->join('services_ratings sr', 'sr.service_id = s.id', 'left')
    //             ->join('promo_codes pc', 'pc.partner_id=p.id', 'left')
    //             ->join('categories c', 'c.id=s.category_id', 'left')
    //             ->where('pd.at_store', 's.at_store', false)
    //             ->where('pd.at_doorstep', 's.at_doorstep', false)
    //             ->where('ps.status', 'active')
    //             ->groupBy('s.id'); // Assuming 's.id' is the primary key of the 'services' table






    //         if ($search and $search != '') {
    //             $multipleWhere = [
    //                 '`s.id`' => $search,
    //                 '`s.title`' => $search,
    //                 '`s.description`' => $search,
    //                 '`s.status`' => $search,
    //                 '`s.tags`' => $search,
    //                 '`s.price`' => $search,
    //                 '`s.discounted_price`' => $search,
    //                 '`s.rating`' => $search,
    //                 '`s.number_of_ratings`' => $search,
    //                 '`s.max_quantity_allowed`' => $search
    //             ];

    //             if (isset($multipleWhere) && !empty($multipleWhere)) {
    //                 $services->groupStart();
    //                 $services->orLike($multipleWhere);
    //                 $services->groupEnd();
    //             }
    //         }

    //         $service_result = $services->get()->getResultArray();



    //         $groupedServices = [];

    //         foreach ($service_result as $row) {
    //             $providerId = $row['user_id'];
    //             $average_rating = $db->table('services s')
    //                 ->select('(SUM(sr.rating) / COUNT(sr.rating)) as average_rating')
    //                 ->join('services_ratings sr', 'sr.service_id = s.id')
    //                 ->where('s.id', $row['id'])
    //                 ->get()->getRowArray();

    //             $row['average_rating'] = isset($average_rating['average_rating']) ? number_format($average_rating['average_rating'], 2) : 0;

    //             $rate_data = get_service_ratings($row['id']);



    //             $row['total_ratings'] = $rate_data[0]['total_ratings'] ?? 0;
    //             $row['rating_5'] = $rate_data[0]['rating_5'] ?? 0;
    //             $row['rating_4'] = $rate_data[0]['rating_4'] ?? 0;
    //             $row['rating_3'] = $rate_data[0]['rating_3'] ?? 0;
    //             $row['rating_2'] = $rate_data[0]['rating_2'] ?? 0;
    //             $row['rating_1'] = $rate_data[0]['rating_1'] ?? 0;

    //             if (isset($row['image']) && !empty($row['image']) && check_exists(base_url($row['image']))) {
    //                 $images = base_url($row['image']);
    //             } else {
    //                 $images = '';
    //             }
    //             $row['image_of_the_service'] = $images;
    //             $tax_data = fetch_details('taxes', ['id' => $row['tax_id']], ['title', 'percentage']);
    //             $taxPercentageData = fetch_details('taxes', ['id' => $row['tax_id']], ['percentage']);
    //             if (!empty($taxPercentageData)) {

    //                 $taxPercentage = $taxPercentageData[0]['percentage'];
    //             } else {
    //                 $taxPercentage = 0;
    //             }
    //             if (empty($tax_data)) {
    //                 $row['tax_title'] = "";
    //                 $row['tax_percentage'] = "";
    //             } else {
    //                 $row['tax_title'] = $tax_data[0]['title'];
    //                 $row['tax_percentage'] = $tax_data[0]['percentage'];
    //             }
    //             if ($row['discounted_price'] == "0") {
    //                 if ($row['tax_type'] == "excluded") {
    //                     $row['tax_value'] = number_format((intval(($row['price'] * ($taxPercentage) / 100))), 2);
    //                     $row['price_with_tax']  = strval($row['price'] + ($row['price'] * ($taxPercentage) / 100));
    //                     $row['original_price_with_tax'] = strval($row['price'] + ($row['price'] * ($taxPercentage) / 100));
    //                 } else {
    //                     $row['tax_value'] = "";
    //                     $row['price_with_tax']  = strval($row['price']);
    //                     $row['original_price_with_tax'] = strval($row['price']);
    //                 }
    //             } else {
    //                 if ($row['tax_type'] == "excluded") {
    //                     $row['tax_value'] = number_format((intval(($row['discounted_price'] * ($taxPercentage) / 100))), 2);
    //                     $row['price_with_tax']  = strval($row['discounted_price'] + ($row['discounted_price'] * ($taxPercentage) / 100));
    //                     $row['original_price_with_tax'] = strval($row['price'] + ($row['discounted_price'] * ($taxPercentage) / 100));
    //                 } else {
    //                     $row['tax_value'] = "";
    //                     $row['price_with_tax']  = strval($row['discounted_price']);
    //                     $row['original_price_with_tax'] = strval($row['price']);
    //                 }
    //             }

    //             if (!isset($groupedServices[$providerId])) {
    //                 $groupedServices[$providerId]['provider']['company_name'] = $row['company_name'];
    //                 $groupedServices[$providerId]['provider']['username'] = $row['partner_name'];
    //                 $groupedServices[$providerId]['provider']['city'] = $row['city'];
    //                 $groupedServices[$providerId]['provider']['latitude'] = $row['latitude'];
    //                 $groupedServices[$providerId]['provider']['longitude'] = $row['longitude'];
    //                 $groupedServices[$providerId]['provider']['id'] = $row['user_id'];
    //                 $groupedServices[$providerId]['provider']['image'] = $row['image'];
    //                 $groupedServices[$providerId]['provider']['banner_image'] = $row['banner'];
    //                 $groupedServices[$providerId]['provider']['number_of_rating'] = $row['number_of_rating'];
    //                 $groupedServices[$providerId]['provider']['total_rating'] = $row['total_rating'];
    //                 $groupedServices[$providerId]['provider']['average_rating'] = $row['average_rating'];
    //                 $groupedServices[$providerId]['provider']['number_of_orders'] = $row['number_of_orders'];
    //                 $groupedServices[$providerId]['provider']['distance'] = $row['distance'];
    //                 $groupedServices[$providerId]['provider']['discount_type'] = $row['discount_type'];
    //                 $groupedServices[$providerId]['provider']['discount'] = $row['discount'];
    //                 $groupedServices[$providerId]['provider']['upto'] = $row['minimum_order_amount'];

    //                 if (!empty($row['image'])) {
    //                     // Set provider image and banner image
    //                     $image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $row['image'])) ? base_url('public/backend/assets/profiles/' . $row['image']) : ((file_exists(FCPATH . $row['image'])) ? base_url($row['image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $row['image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $row['image'])));
    //                     $groupedServices[$providerId]['provider']['image'] = $image;

    //                     $banner_image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $row['banner'])) ? base_url('public/backend/assets/profiles/' . $row['banner']) : ((file_exists(FCPATH . $row['banner'])) ? base_url($row['banner']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $row['banner'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $row['banner'])));
    //                     $groupedServices[$providerId]['provider']['banner_image']  = $banner_image;

    //                     if ($row['discount_type'] == 'percentage') {
    //                         $groupedServices[$providerId]['provider']['upto'] =  $row['minimum_order_amount'];
    //                         unset($groupedServices[$providerId]['provider']['discount_type']);
    //                     }
    //                 }

    //                 unset($row['minimum_order_amount']);
    //                 $groupedServices[$providerId]['provider']['services'] = [];
    //             }

    //             // Add the service to the provider's services array
    //             $groupedServices[$providerId]['provider']['services'][] = $row;
    //         }

    //         // Convert the associative array to a numeric array
    //         $data['Services'] = array_values($groupedServices);
    //     }





    //     $response = [
    //         'error' => false,
    //         "data" => $data
    //     ];

    //     return $this->response->setJSON($response);
    // }
    function search_services_providers()
    {

        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'search' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
                'type' => 'required'
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }

        $search = $this->request->getPost('search') ?? '';
        $latitude = $this->request->getPost('latitude') ?? '';
        $longitude = $this->request->getPost('longitude') ?? '';
        $db = \Config\Database::connect();

        $limit = $this->request->getPost('limit') ?? '5';
        $offset = $this->request->getPost('offset') ?? '0';


        $type = $this->request->getPost('type');
        $data = [];



        if ($type == "provider") {
            $settings = get_settings('general_settings', true);
            if (($this->request->getPost('latitude') && !empty($this->request->getPost('latitude')) && ($this->request->getPost('longitude') && !empty($this->request->getPost('longitude'))))) {
                $additional_data = [
                    'latitude' => $this->request->getPost('latitude'),
                    'longitude' => $this->request->getPost('longitude'),
                    'max_serviceable_distance' => $settings['max_serviceable_distance'],
                ];
            }
            $is_latitude_set = "";
            if (isset($additional_data['latitude']) && !empty($additional_data['latitude'])) {
                $latitude = $this->request->getPost('latitude');
                $longitude = $this->request->getPost('longitude');
                $is_latitude_set = " st_distance_sphere(POINT(' $longitude','$latitude'), POINT(`p`.`longitude`, `p`.`latitude` ))/1000  as distance";
            }


            $builder1 = $db->table('users u1');

            $partners1 = $builder1->Select("u1.username,u1.city,u1.latitude,u1.longitude,u1.id,pc.minimum_order_amount,pc.discount,pd.company_name,u1.image,pd.banner, pc.discount_type,u1.id as partner_id,
                   ( count(sr.rating)) as number_of_rating,
                    ( SUM(sr.rating)) as total_rating,
                    ((SUM(sr.rating) / count(sr.rating))) as average_rating,
            (SELECT COUNT(*) FROM orders o WHERE o.partner_id = u1.id AND o.parent_id IS NULL) as number_of_orders,st_distance_sphere(POINT($longitude, $latitude),
             POINT(`longitude`, `latitude` ))/1000  as distance")
                ->join('users_groups ug1', 'ug1.user_id=u1.id')
                ->join('partner_details pd', 'pd.partner_id=u1.id')
                ->join('services s', 's.user_id=pd.partner_id', 'left')
                ->join('services_ratings sr', 'sr.service_id = s.id', 'left')
                ->join('partner_subscriptions ps', 'ps.partner_id=u1.id')
                ->join('promo_codes pc', 'pc.partner_id=u1.id', 'left')
                ->where('ps.status', 'active')
                 ->where('pd.is_approved', '1')
                
                ->where('ug1.group_id', '3')
                ->groupBy('pd.partner_id')
                ->having('distance < ' . $additional_data['max_serviceable_distance'])
                ->orderBy('distance')->limit($limit, $offset);


            if ($search and $search != '') {
                $searchWhere = [
                    '`pd.id`' => $search,
                    '`pd.company_name`' => $search,
                    '`pd.tax_name`' => $search,
                    '`pd.tax_number`' => $search,
                    '`pd.bank_name`' => $search,
                    '`pd.account_number`' => $search,
                    '`pd.account_name`' => $search,
                    '`pd.bank_code`' => $search,
                    '`pd.swift_code`' => $search,
                    '`pd.created_at`' => $search,
                    '`pd.updated_at`' => $search,
                    '`u1.username`' => $search,
                ];

                if (isset($searchWhere) && !empty($searchWhere)) {
                    $builder1->groupStart();
                    $builder1->orLike($searchWhere);
                    $builder1->groupEnd();
                }
            }




            $partners1 = $builder1->get()->getResultArray();

            for ($i = 0; $i < count($partners1); $i++) {
                $partners1[$i]['upto'] = $partners1[$i]['minimum_order_amount'];
                if (!empty($partners1[$i]['image'])) {
                    $image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $partners1[$i]['image'])) ? base_url('public/backend/assets/profiles/' . $partners1[$i]['image']) : ((file_exists(FCPATH . $partners1[$i]['image'])) ? base_url($partners1[$i]['image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $partners1[$i]['image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $partners1[$i]['image'])));
                    $partners1[$i]['image'] = $image;
                    $banner_image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $partners1[$i]['banner'])) ? base_url('public/backend/assets/profiles/' . $partners1[$i]['banner']) : ((file_exists(FCPATH . $partners1[$i]['banner'])) ? base_url($partners1[$i]['banner']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $partners1[$i]['banner'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $partners1[$i]['banner'])));
                    $partners1[$i]['banner_image'] = $banner_image;
                    unset($partners1[$i]['banner']);
                    if ($partners1[$i]['discount_type'] == 'percentage') {
                        // $discount = $partners[$i]['discount'];
                        $upto = $partners1[$i]['minimum_order_amount'];
                        unset($partners1[$i]['discount_type']);
                    }
                }
                unset($partners1[$i]['minimum_order_amount']);
            }


            $ids = [];
            foreach ($partners1 as $key => $row1) {
                $ids[] = $row1['id'];
            }


            foreach ($ids as $key => $id) {
                $partner_subscription = fetch_details('partner_subscriptions', ['partner_id' => $id, 'status' => 'active']);
                if ($partner_subscription) {
                    $subscription_purchase_date = $partner_subscription[0]['updated_at'];
                    $partner_order_limit = fetch_details('orders', ['partner_id' => $id, 'parent_id' => null, 'created_at >' => $subscription_purchase_date]);
                    $partners_subscription = $db->table('partner_subscriptions ps');
                    $partners_subscription_data = $partners_subscription->select('ps.*')->where('ps.status', 'active')
                        ->get()
                        ->getResultArray();

                    $subscription_order_limit = $partners_subscription_data[0]['max_order_limit'];
                    if ($partners_subscription_data[0]['order_type'] == "limited") {
                        if (count($partner_order_limit) >= $subscription_order_limit) {
                            unset($ids[$key]);
                        }
                    }
                } else {

                    unset($ids[$key]);
                }
            }

            $parent_ids = array_values($ids);



            $parent_ids = implode(", ", $parent_ids);
            $data['providers'] = $partners1;


            // for total ------------------------------

            $builder1_total = $db->table('users u1');

            $partners1_total = $builder1_total->Select("u1.username,u1.city,u1.latitude,u1.longitude,u1.id,pc.minimum_order_amount,pc.discount,pd.company_name,u1.image,pd.banner, pc.discount_type,
                   ( count(sr.rating)) as number_of_rating,
                    ( SUM(sr.rating)) as total_rating,
                    ((SUM(sr.rating) / count(sr.rating))) as average_rating,
            (SELECT COUNT(*) FROM orders o WHERE o.partner_id = u1.id AND o.parent_id IS NULL) as number_of_orders,st_distance_sphere(POINT($longitude, $latitude),
             POINT(`longitude`, `latitude` ))/1000  as distance")
                ->join('users_groups ug1', 'ug1.user_id=u1.id')
                ->join('partner_details pd', 'pd.partner_id=u1.id')
                ->join('services s', 's.user_id=pd.partner_id', 'left')
                ->join('services_ratings sr', 'sr.service_id = s.id', 'left')
                ->join('partner_subscriptions ps', 'ps.partner_id=u1.id')
                ->join('promo_codes pc', 'pc.partner_id=u1.id', 'left')
                ->where('ps.status', 'active')
                ->where('ug1.group_id', '3')
                ->groupBy('pd.partner_id')
                ->having('distance < ' . $additional_data['max_serviceable_distance'])
                ->orderBy('distance');


            if ($search and $search != '') {
                $searchWhere = [
                    '`pd.id`' => $search,
                    '`pd.company_name`' => $search,
                    '`pd.tax_name`' => $search,
                    '`pd.tax_number`' => $search,
                    '`pd.bank_name`' => $search,
                    '`pd.account_number`' => $search,
                    '`pd.account_name`' => $search,
                    '`pd.bank_code`' => $search,
                    '`pd.swift_code`' => $search,
                    '`pd.created_at`' => $search,
                    '`pd.updated_at`' => $search,
                    '`u1.username`' => $search,
                ];

                if (isset($searchWhere) && !empty($searchWhere)) {
                    $builder1_total->groupStart();
                    $builder1_total->orLike($searchWhere);
                    $builder1_total->groupEnd();
                }
            }




            $partners1_total = $builder1_total->get()->getResultArray();


            for ($i = 0; $i < count($partners1_total); $i++) {
                $partners1_total[$i]['upto'] = $partners1_total[$i]['minimum_order_amount'];
                if (!empty($partners1_total[$i]['image'])) {
                    $image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $partners1_total[$i]['image'])) ? base_url('public/backend/assets/profiles/' . $partners1_total[$i]['image']) : ((file_exists(FCPATH . $partners1_total[$i]['image'])) ? base_url($partners1_total[$i]['image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $partners1_total[$i]['image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $partners1_total[$i]['image'])));
                    $partners1_total[$i]['image'] = $image;
                    $banner_image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $partners1_total[$i]['banner'])) ? base_url('public/backend/assets/profiles/' . $partners1_total[$i]['banner']) : ((file_exists(FCPATH . $partners1_total[$i]['banner'])) ? base_url($partners1_total[$i]['banner']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $partners1_total[$i]['banner'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $partners1_total[$i]['banner'])));
                    $partners1_total[$i]['banner_image'] = $banner_image;
                    unset($partners1_total[$i]['banner']);
                    if ($partners1_total[$i]['discount_type'] == 'percentage') {
                        // $discount = $partners[$i]['discount'];
                        $upto = $partners1_total[$i]['minimum_order_amount'];
                        unset($partners1_total[$i]['discount_type']);
                    }
                }
                unset($partners1_total[$i]['minimum_order_amount']);
            }


            $ids = [];
            foreach ($partners1_total as $key => $row1) {
                $ids[] = $row1['id'];
            }


            foreach ($ids as $key => $id) {
                $partner_subscription = fetch_details('partner_subscriptions', ['partner_id' => $id, 'status' => 'active']);
                if ($partner_subscription) {
                    $subscription_purchase_date = $partner_subscription[0]['updated_at'];
                    $partner_order_limit = fetch_details('orders', ['partner_id' => $id, 'parent_id' => null, 'created_at >' => $subscription_purchase_date]);
                    $partners_subscription = $db->table('partner_subscriptions ps');
                    $partners_subscription_data = $partners_subscription->select('ps.*')->where('ps.status', 'active')
                        ->get()
                        ->getResultArray();

                    $subscription_order_limit = $partners_subscription_data[0]['max_order_limit'];
                    if ($partners_subscription_data[0]['order_type'] == "limited") {
                        if (count($partner_order_limit) >= $subscription_order_limit) {
                            unset($ids[$key]);
                        }
                    }
                } else {

                    unset($ids[$key]);
                }
            }

            $data['total'] = count($partners1_total);
            //end for total 

        } else if ($type == "service") {

            // services 
            $multipleWhere = '';
            $db      = \Config\Database::connect();
            $builder = $db->table('services s');


            $services = $builder->select("s.*,s.image as service_image, c.name as category_name, p.username as partner_name, c.parent_id, pd.company_name, pd.at_store as provider_at_store, pd.at_doorstep as provider_at_doorstep, p.city,
                p.latitude, p.longitude, p.id as user_id, pd.banner, p.image,
                COALESCE(COUNT(sr.rating), 0) as number_of_rating,
                COALESCE(SUM(sr.rating), 0) as total_rating,
                (SELECT COUNT(*) FROM orders o WHERE o.partner_id = p.id AND o.parent_id IS NULL) as number_of_orders, st_distance_sphere(POINT($longitude, $latitude),
                POINT(p.longitude, p.latitude))/1000 as distance, pc.discount, pc.discount_type, pc.minimum_order_amount")
                ->join('users p', 'p.id=s.user_id', 'left')
                ->join('partner_details pd', 'pd.partner_id=s.user_id')
                ->join('partner_subscriptions ps', 'ps.partner_id=s.user_id')
                ->join('services_ratings sr', 'sr.service_id = s.id', 'left')
                ->join('promo_codes pc', 'pc.partner_id=p.id', 'left')
                ->join('categories c', 'c.id=s.category_id', 'left')
                ->where('pd.at_store', 's.at_store', false)
                ->where('pd.at_doorstep', 's.at_doorstep', false)
                ->where('ps.status', 'active')
                    ->where('pd.is_approved', '1')
                ->groupBy('s.id');






            if ($search and $search != '') {
                $multipleWhere = [
                    '`s.id`' => $search,
                    '`s.title`' => $search,
                    '`s.description`' => $search,
                    '`s.status`' => $search,
                    '`s.tags`' => $search,
                    '`s.price`' => $search,
                    '`s.discounted_price`' => $search,
                    '`s.rating`' => $search,
                    '`s.number_of_ratings`' => $search,
                    '`s.max_quantity_allowed`' => $search
                ];

                if (isset($multipleWhere) && !empty($multipleWhere)) {
                    $services->groupStart();
                    $services->orLike($multipleWhere);
                    $services->groupEnd();
                }
            }

            $service_result = $services->get()->getResultArray();




            $groupedServices = [];
            $groupedServices1 = [];
            $all_providers = [];

            foreach ($service_result as $row) {

                $all_providers[] = $row['user_id'];

                $providerId = $row['user_id'];
                $average_rating = $db->table('services s')
                    ->select('(SUM(sr.rating) / COUNT(sr.rating)) as average_rating')
                    ->join('services_ratings sr', 'sr.service_id = s.id')
                    ->where('s.id', $row['id'])
                    ->get()->getRowArray();

                $row['average_rating'] = isset($average_rating['average_rating']) ? number_format($average_rating['average_rating'], 2) : 0;

                $rate_data = get_service_ratings($row['id']);



                $row['total_ratings'] = $rate_data[0]['total_ratings'] ?? 0;
                $row['rating_5'] = $rate_data[0]['rating_5'] ?? 0;
                $row['rating_4'] = $rate_data[0]['rating_4'] ?? 0;
                $row['rating_3'] = $rate_data[0]['rating_3'] ?? 0;
                $row['rating_2'] = $rate_data[0]['rating_2'] ?? 0;
                $row['rating_1'] = $rate_data[0]['rating_1'] ?? 0;

                if (isset($row['service_image']) && !empty($row['service_image']) && check_exists(base_url($row['service_image']))) {
                    $images = base_url($row['service_image']);
                } else {
                    $images = '';
                }
                $row['image_of_the_service'] = $images;
                //  $row['image'] = $images;
                
                $tax_data = fetch_details('taxes', ['id' => $row['tax_id']], ['title', 'percentage']);
                $taxPercentageData = fetch_details('taxes', ['id' => $row['tax_id']], ['percentage']);
                if (!empty($taxPercentageData)) {

                    $taxPercentage = $taxPercentageData[0]['percentage'];
                } else {
                    $taxPercentage = 0;
                }
                if (empty($tax_data)) {
                    $row['tax_title'] = "";
                    $row['tax_percentage'] = "";
                } else {
                    $row['tax_title'] = $tax_data[0]['title'];
                    $row['tax_percentage'] = $tax_data[0]['percentage'];
                }
                if ($row['discounted_price'] == "0") {
                    if ($row['tax_type'] == "excluded") {
                        $row['tax_value'] = number_format((intval(($row['price'] * ($taxPercentage) / 100))), 2);
                        $row['price_with_tax']  = strval($row['price'] + ($row['price'] * ($taxPercentage) / 100));
                        $row['original_price_with_tax'] = strval($row['price'] + ($row['price'] * ($taxPercentage) / 100));
                    } else {
                        $row['tax_value'] = "";
                        $row['price_with_tax']  = strval($row['price']);
                        $row['original_price_with_tax'] = strval($row['price']);
                    }
                } else {
                    if ($row['tax_type'] == "excluded") {
                        $row['tax_value'] = number_format((intval(($row['discounted_price'] * ($taxPercentage) / 100))), 2);
                        $row['price_with_tax']  = strval($row['discounted_price'] + ($row['discounted_price'] * ($taxPercentage) / 100));
                        $row['original_price_with_tax'] = strval($row['price'] + ($row['discounted_price'] * ($taxPercentage) / 100));
                    } else {
                        $row['tax_value'] = "";
                        $row['price_with_tax']  = strval($row['discounted_price']);
                        $row['original_price_with_tax'] = strval($row['price']);
                    }
                }



                if (!isset($groupedServices[$providerId])) {
                    $groupedServices[$providerId]['provider']['company_name'] = $row['company_name'];
                    $groupedServices[$providerId]['provider']['username'] = $row['partner_name'];
                    $groupedServices[$providerId]['provider']['city'] = $row['city'];
                    $groupedServices[$providerId]['provider']['latitude'] = $row['latitude'];
                    $groupedServices[$providerId]['provider']['longitude'] = $row['longitude'];
                    $groupedServices[$providerId]['provider']['id'] = $row['user_id'];
                    $groupedServices[$providerId]['provider']['image'] = $row['image'];
                    $groupedServices[$providerId]['provider']['banner_image'] = $row['banner'];
                    $groupedServices[$providerId]['provider']['number_of_rating'] = $row['number_of_rating'];
                    $groupedServices[$providerId]['provider']['total_rating'] = $row['total_rating'];
                    $groupedServices[$providerId]['provider']['average_rating'] = $row['average_rating'];
                    $groupedServices[$providerId]['provider']['number_of_orders'] = $row['number_of_orders'];
                    $groupedServices[$providerId]['provider']['distance'] = $row['distance'];
                    $groupedServices[$providerId]['provider']['discount_type'] = $row['discount_type'];
                    $groupedServices[$providerId]['provider']['discount'] = $row['discount'];
                    $groupedServices[$providerId]['provider']['upto'] = $row['minimum_order_amount'];

                    if (!empty($row['image'])) {
                        // Set provider image and banner image
                        $image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $row['image'])) ? base_url('public/backend/assets/profiles/' . $row['image']) : ((file_exists(FCPATH . $row['image'])) ? base_url($row['image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $row['image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $row['image'])));
                        $groupedServices[$providerId]['provider']['image'] = $image;

                        $banner_image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $row['banner'])) ? base_url('public/backend/assets/profiles/' . $row['banner']) : ((file_exists(FCPATH . $row['banner'])) ? base_url($row['banner']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $row['banner'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $row['banner'])));
                        $groupedServices[$providerId]['provider']['banner_image']  = $banner_image;

                        if ($row['discount_type'] == 'percentage') {
                            $groupedServices[$providerId]['provider']['upto'] =  $row['minimum_order_amount'];
                            unset($groupedServices[$providerId]['provider']['discount_type']);
                        }
                    }

                    unset($row['minimum_order_amount']);
                    $groupedServices[$providerId]['provider']['services'] = [];
                }

                // Add the service to the provider's services array
                $groupedServices[$providerId]['provider']['services'][] = $row;
            }

            // Convert the associative array to a numeric array
            // $data['Services'] = array_values($groupedServices);

            $all_providers = array_unique($all_providers);
            $all_providers = array_slice(($all_providers), $offset, $limit);



            foreach ($service_result as $row) {

                // print_R('provider id -'.$row['user_id']);
                // print_R('filterd provider id -'.$all_providers);

                $providerId = $row['user_id'];

                if (in_array($providerId, $all_providers)) {

                    $average_rating = $db->table('services s')
                        ->select('(SUM(sr.rating) / COUNT(sr.rating)) as average_rating')
                        ->join('services_ratings sr', 'sr.service_id = s.id')
                        ->where('s.id', $row['id'])
                        ->get()->getRowArray();

                    $row['average_rating'] = isset($average_rating['average_rating']) ? number_format($average_rating['average_rating'], 2) : 0;

                    $rate_data = get_service_ratings($row['id']);



                    $row['total_ratings'] = $rate_data[0]['total_ratings'] ?? 0;
                    $row['rating_5'] = $rate_data[0]['rating_5'] ?? 0;
                    $row['rating_4'] = $rate_data[0]['rating_4'] ?? 0;
                    $row['rating_3'] = $rate_data[0]['rating_3'] ?? 0;
                    $row['rating_2'] = $rate_data[0]['rating_2'] ?? 0;
                    $row['rating_1'] = $rate_data[0]['rating_1'] ?? 0;

                    if (isset($row['service_image']) && !empty($row['service_image']) && check_exists(base_url($row['service_image']))) {
                        $images = base_url($row['service_image']);
                    } else {
                        $images = '';
                    }

                    if (!empty($row['other_images'])) {
                        $row['other_images'] = array_map(function ($data) {
                            return base_url($data);
                        }, json_decode($row['other_images'], true));
                    } else {
                        $row['other_images'] = []; // Return an empty array
                    }

                    if (!empty($row['files'])) {
                        $row['files'] = array_map(function ($data) {
                            return base_url($data);
                        }, json_decode($row['files'], true));
                    } else {
                        $row['files'] = []; // Return an empty array
                    }


                    $faqsData = json_decode($row['faqs'], true); // Decode the string into an array

                    if (is_array($faqsData)) {
                        $faqs = [];
                        foreach ($faqsData as $pair) {
                            $faq = [
                                'question' => $pair[0],
                                'answer' => $pair[1]
                            ];
                            $faqs[] = $faq;
                        }

                        $row['faqs'] = $faqs;
                    } else {

                        $row['faqs'] = [];
                        // Handle the case when decoding fails
                        // You can display an error message or set a default value for $data['faqs']
                    }
                    $row['image_of_the_service'] = $images;
                    $row['image'] = $images;
                    
                    unset($row['service_image']);
                    $tax_data = fetch_details('taxes', ['id' => $row['tax_id']], ['title', 'percentage']);
                    $taxPercentageData = fetch_details('taxes', ['id' => $row['tax_id']], ['percentage']);
                    if (!empty($taxPercentageData)) {

                        $taxPercentage = $taxPercentageData[0]['percentage'];
                    } else {
                        $taxPercentage = 0;
                    }


                    if (empty($tax_data)) {
                        $row['tax_title'] = "";
                        $row['tax_percentage'] = "";
                    } else {
                        $row['tax_title'] = $tax_data[0]['title'];
                        $row['tax_percentage'] = $tax_data[0]['percentage'];
                    }
                    if ($row['discounted_price'] == "0") {
                        if ($row['tax_type'] == "excluded") {
                            $row['tax_value'] = number_format((intval(($row['price'] * ($taxPercentage) / 100))), 2);
                            $row['price_with_tax']  = strval($row['price'] + ($row['price'] * ($taxPercentage) / 100));
                            $row['original_price_with_tax'] = strval($row['price'] + ($row['price'] * ($taxPercentage) / 100));
                        } else {
                            $row['tax_value'] = "";
                            $row['price_with_tax']  = strval($row['price']);
                            $row['original_price_with_tax'] = strval($row['price']);
                        }
                    } else {
                        if ($row['tax_type'] == "excluded") {
                            $row['tax_value'] = number_format((intval(($row['discounted_price'] * ($taxPercentage) / 100))), 2);
                            $row['price_with_tax']  = strval($row['discounted_price'] + ($row['discounted_price'] * ($taxPercentage) / 100));
                            $row['original_price_with_tax'] = strval($row['price'] + ($row['discounted_price'] * ($taxPercentage) / 100));
                        } else {
                            $row['tax_value'] = "";
                            $row['price_with_tax']  = strval($row['discounted_price']);
                            $row['original_price_with_tax'] = strval($row['price']);
                        }
                    }



                    if (!isset($groupedServices1[$providerId])) {
                        $groupedServices1[$providerId]['provider']['company_name'] = $row['company_name'];
                        $groupedServices1[$providerId]['provider']['username'] = $row['partner_name'];
                        $groupedServices1[$providerId]['provider']['city'] = $row['city'];
                        $groupedServices1[$providerId]['provider']['latitude'] = $row['latitude'];
                        $groupedServices1[$providerId]['provider']['longitude'] = $row['longitude'];
                        $groupedServices1[$providerId]['provider']['id'] = $row['user_id'];
                        $groupedServices1[$providerId]['provider']['image'] = $row['image'];
                        $groupedServices1[$providerId]['provider']['banner_image'] = $row['banner'];
                        $groupedServices1[$providerId]['provider']['number_of_rating'] = $row['number_of_rating'];
                        $groupedServices1[$providerId]['provider']['total_rating'] = $row['total_rating'];
                        $groupedServices1[$providerId]['provider']['average_rating'] = $row['average_rating'];
                        $groupedServices1[$providerId]['provider']['number_of_orders'] = $row['number_of_orders'];
                        $groupedServices1[$providerId]['provider']['distance'] = $row['distance'];
                        $groupedServices1[$providerId]['provider']['discount_type'] = $row['discount_type'];
                        $groupedServices1[$providerId]['provider']['discount'] = $row['discount'];
                        $groupedServices1[$providerId]['provider']['upto'] = $row['minimum_order_amount'];

                        if (!empty($row['image'])) {
                            // Set provider image and banner image
                            $image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $row['image'])) ? base_url('public/backend/assets/profiles/' . $row['image']) : ((file_exists(FCPATH . $row['image'])) ? base_url($row['image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $row['image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $row['image'])));
                            $groupedServices1[$providerId]['provider']['image'] = $image;

                            $banner_image = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $row['banner'])) ? base_url('public/backend/assets/profiles/' . $row['banner']) : ((file_exists(FCPATH . $row['banner'])) ? base_url($row['banner']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $row['banner'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $row['banner'])));
                            $groupedServices1[$providerId]['provider']['banner_image']  = $banner_image;

                            if ($row['discount_type'] == 'percentage') {
                                $groupedServices1[$providerId]['provider']['upto'] =  $row['minimum_order_amount'];
                                unset($groupedServices1[$providerId]['provider']['discount_type']);
                            }
                        }

                        unset($row['minimum_order_amount']);
                        $groupedServices1[$providerId]['provider']['services'] = [];
                    }

                    // Add the service to the provider's services array
                    $groupedServices1[$providerId]['provider']['services'][] = $row;
                }
            }
            if (!empty($groupedServices1)) {
                $data['total'] = count($groupedServices);

                $data['Services'] = array_values($groupedServices1);
            } else {
                $data['total'] = 0;
                $data['Services'] = [];
            }
        }



        $response = [
            'error' => false,
            "data" => $data
        ];

        return $this->response->setJSON($response);
    }


    public function capturePayment()
    {
        // API endpoint
        $apiEndpoint = 'https://api-m.sandbox.paypal.com';

        // Request data
        $requestData = json_encode([
            "intent" => "CAPTURE",
            "purchase_units" => [
                // ... your purchase_units data
            ],
            "application_context" => [
                "return_url" => "https://example.com/return",
                "cancel_url" => "https://example.com/cancel"
            ]
        ]);

        // cURL options
        $options = [
            CURLOPT_URL            => $apiEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $requestData,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                // Add any other headers if needed
            ],
        ];

        // Initialize cURL session
        $ch = curl_init();
        curl_setopt_array($ch, $options);

        // Execute cURL session
        $response = curl_exec($ch);

        // Close cURL session
        curl_close($ch);

        // Process the API response as needed
        // For example, you can echo the response
        echo $response;
    }
}
