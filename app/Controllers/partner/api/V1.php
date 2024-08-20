<?php

namespace App\Controllers\partner\api;

use App\Controllers\BaseController;
use App\Models\Orders_model;
use App\Models\Partners_model;
use App\Models\Category_model;
use App\Models\Payment_request_model;
use App\Models\Promo_code_model;
use App\Models\Service_model;
use App\Models\Tax_model;
use App\Libraries\Paystack;
use App\Libraries\Razorpay;
use App\Models\Slider_model;
use App\Libraries\Stripe;
use App\Libraries\Flutterwave;
use App\Libraries\Paypal;
use App\Models\Transaction_model;
use App\Models\Service_ratings_model;
use App\Models\Notification_model;
use App\Models\Subscription_model;
use DateTime;
use Exception;
use PDO;
use PhpParser\Node\Stmt\TryCatch;
/*  
1. 
i.login
 ii.Register /update both done
2  get_orders // token jose   done
4. i.get_categories 
ii. get_sub_categories //both done
5. get_services // done token jose
6. get_transactions //done token jose
7. get_statistics //done token jose
9. delete_orders // done token jose 
10. verify_user //done
11. get_settings  //done
12. update_fcm //done
13. get_taxes // done
14. send_withdrawal_request //done token jose
15. get_withdrawal_request //done token jose
24. delete_withdrawal_request //done token jose
17. get_partner //done  token jose
19. delete_service //done token jose
21. update_service_status //done token jose
22. manage_promocode //done token jose
23. delete_promocode //done token jose
24. get_promocodes //done token jose
25. manage_service //done
3. update_order_status // token jose
8. forgot_password 
20. reset_password
21. list_subscription
22. buy_subscription
*/

class V1 extends BaseController
{
    protected $excluded_routes =
    [
        "/partner/api/v1/index",
        "/partner/api/v1",
        "/partner/api/v1/manage_user",
        "/partner/api/v1/register",
        "/partner/api/v1/forgot_password",
        "/partner/api/v1/login",
        "/partner/api/v1/verify_user",
        "/partner/api/v1/get_settings",
        "/partner/api/v1/change-password",
        "/partner/api/v1/forgot-password",
        "/partner/api/v1/paypal_transaction_webview",
    ];
    protected $validationListTemplate = 'list';
    private  $user_details = [];
    private  $allowed_settings = ["general_settings", "terms_conditions", "privacy_policy", "about_us"];
    private  $user_data = ['id', 'first_name', 'last_name', 'phone', 'email', 'fcm_id', 'web_fcm_id', 'image'];
    function __construct()
    {

        helper('api');
        helper("function");
        $this->request = \Config\Services::request();
        $current_uri =  uri_string();
        if (!in_array($current_uri, $this->excluded_routes)) {
            $token = verify_app_request();
            // print_r($token);
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
        $this->razorpay = new Razorpay();
        $this->configIonAuth = config('IonAuth');

        helper('session');
        session()->remove('identity'); // This will clear all sessions
    }
    // 1.f
    public function index()
    {
        $response = \Config\Services::response();
        helper("filesystem");
        $response->setHeader('content-type', 'Text');
        return $response->setBody(file_get_contents(base_url('api-doc.txt')));
    }
    public function login()
    {
        /* 
            mobile : 1234567890
            password : 12345678
            county_code : 12345678
        */
        // try {
        $ionAuth = new \IonAuth\Libraries\IonAuth();
        $config = new \Config\IonAuth();
        $validation =  \Config\Services::validation();
        $request = \Config\Services::request();
        $identity_column = $config->identity;
        // 
        if ($identity_column == 'phone') {
            $identity = $request->getPost('mobile');
            $validation->setRule('mobile', 'Mobile', 'numeric|required');
        } elseif ($identity_column == 'email') {
            $identity = $request->getPost('email');
            $validation->setRule('email', 'Email', 'required|valid_email');
        } else {
            $validation->setRule('identity', 'Identity', 'required');
        }
        $validation->setRule('password', 'Password', 'required');
        $password = $request->getPost('password');
        if ($request->getPost('fcm_id')) {
            $validation->setRule('fcm_id', 'FCM ID', 'trim');
        }
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => []
            ];
            return $this->response->setJSON($response);
        }
        $login = $ionAuth->login($identity, $password, false, $request->getPost('country_code'));
        // $userCheck = fetch_details('users', [$identity_column => $identity]);
        // if (!empty($userCheck)) {

        //     $user_group = fetch_details('users_groups', ['user_id' => $userCheck[0]['id'], 'group_id' => '3']);
        // } else {
        //     $user_group = [];
        // }

        // print_r($userCheck[0]['id']);
        // die;

        $db      = \Config\Database::connect();
        $builder = $db->table('users u');
        $builder->select('u.*,ug.group_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('ug.group_id', 3)
            ->where(['phone' => $identity]);
        $userCheck = $builder->get()->getResultArray();

        if (empty($userCheck)) {
            $response = [
                'error' => true,
                'message' => 'Oops, it seems like this number isn’t registered. Please register to use our services.',

            ];
            return $this->response->setJSON($response);
        }
        // print_R($userCheck);
        // die;
        $subscription = fetch_details('partner_subscriptions', ['partner_id' => $userCheck[0]['id']], [], 1, 0, 'id', 'DESC');

        if (!empty($userCheck)) {
            if ((($userCheck[0]['country_code'] == null) || ($userCheck[0]['country_code'] == $request->getPost('country_code'))) && (($userCheck[0]['phone'] == $identity))) {
                if ($login) {
                    //   echo 'ww1';
                    if (($userCheck[0]['country_code'] == null)) {
                        update_details(['country_code' => $request->getPost('country_code')], ['phone' => $identity], 'users');
                    }
                    // Login Success
                    if (($request->getPost('fcm_id')) && !empty($request->getPost('fcm_id'))) {
                        update_details(['fcm_id' => $request->getPost('fcm_id')], ['phone' => $identity], 'users');
                    }
                    $data = array();
                    array_push($this->user_data, "api_key");
                    $data = fetch_details('users', ['id' => $userCheck[0]['id']], ['id', 'username', 'country_code', 'phone', 'email', 'fcm_id', 'image', 'api_key'])[0];
                    // print_r($data);
                    // die;
                    if (isset($data['image']) && !empty($data['image'])) {
                        $data['image'] = (file_exists(FCPATH . 'public/backend/assets/profiles/' .  $data['image'])) ? base_url('public/backend/assets/profiles/' .  $data['image']) : ((file_exists(FCPATH .  $data['image'])) ? base_url($data['image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" .  $data['image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" .  $data['image'])));
                    } else {
                        $data['image'] = base_url("public/backend/assets/profiles/default.png");
                    }
                    $token = generate_tokens($identity, 3);
                    $token_data['user_id'] = $data['id'];
                    $token_data['token'] = $token;
                    if (isset($token_data) && !empty($token_data)) {
                        insert_details($token_data, 'users_tokens');
                    }
                    $userdata = fetch_details('users', ['id' => $data['id']], ['id', 'username', 'email', 'balance', 'active', 'first_name', 'last_name', 'company', 'phone', 'country_code', 'fcm_id', 'image', 'city_id', 'city', 'latitude', 'longitude'])[0];
                    $partnerData = fetch_details('partner_details', ['partner_id' => $data['id']])[0];
                    $userdata['image'] = (file_exists($userdata['image'])) ? base_url($userdata['image']) : "";
                    $partnerData['banner'] = (file_exists($partnerData['banner'])) ? base_url($partnerData['banner']) : "";
                    $partnerData['address_id'] = (file_exists($partnerData['address_id'])) ? base_url($partnerData['address_id']) : "";
                    $partnerData['passport'] = (file_exists($partnerData['passport'])) ? base_url($partnerData['passport']) : "";
                    $partnerData['national_id'] = (file_exists($partnerData['national_id'])) ? base_url($partnerData['national_id']) : "";
                    if (!empty($partnerData['other_images'])) {
                        $partnerData['other_images'] = array_map(function ($data) {
                            return base_url($data);
                        }, json_decode($partnerData['other_images'], true));
                    } else {
                        $partnerData['other_images'] = []; // Return an empty array
                    }

                    $location_information['city'] = $userdata['city'];
                    $location_information['latitude'] = $userdata['latitude'];
                    $location_information['longitude'] = $userdata['longitude'];
                    $location_information['longitude'] = $userdata['longitude'];
                    $location_information['address'] = $partnerData['address'];
                    $bank_information['tax_name'] = $partnerData['tax_name'];
                    $bank_information['tax_number'] = $partnerData['tax_number'];
                    $bank_information['account_number'] = $partnerData['account_number'];
                    $bank_information['account_name'] = $partnerData['account_name'];
                    $bank_information['bank_code'] = $partnerData['bank_code'];
                    $bank_information['bank_code'] = $partnerData['bank_code'];
                    $bank_information['swift_code'] = $partnerData['swift_code'];
                    $bank_information['bank_name'] = $partnerData['bank_name'];

                    $subscription_information['subscription_id'] = isset($subscription[0]['subscription_id']) ? $subscription[0]['subscription_id'] : "";
                    $subscription_information['isSubscriptionActive'] = isset($subscription[0]['status']) ? $subscription[0]['status'] : "deactive";
                    $subscription_information['created_at'] = isset($subscription[0]['created_at']) ? $subscription[0]['created_at'] : "";
                    $subscription_information['updated_at'] = isset($subscription[0]['updated_at']) ? $subscription[0]['updated_at'] : "";
                    $subscription_information['is_payment'] = isset($subscription[0]['is_payment']) ? $subscription[0]['is_payment'] : "";
                    $subscription_information['id'] = isset($subscription[0]['id']) ? $subscription[0]['id'] : "";
                    $subscription_information['partner_id'] = isset($subscription[0]['partner_id']) ? $subscription[0]['partner_id'] : "";
                    $subscription_information['purchase_date'] = isset($subscription[0]['purchase_date']) ? $subscription[0]['purchase_date'] : "";
                    $subscription_information['expiry_date'] = isset($subscription[0]['expiry_date']) ? $subscription[0]['expiry_date'] : "";
                    $subscription_information['name'] = isset($subscription[0]['name']) ? $subscription[0]['name'] : "";
                    $subscription_information['description'] = isset($subscription[0]['description']) ? $subscription[0]['description'] : "";
                    $subscription_information['duration'] = isset($subscription[0]['duration']) ? $subscription[0]['duration'] : "";
                    $subscription_information['price'] = isset($subscription[0]['price']) ? $subscription[0]['price'] : "";
                    $subscription_information['discount_price'] = isset($subscription[0]['discount_price']) ? $subscription[0]['discount_price'] : "";
                    $subscription_information['order_type'] = isset($subscription[0]['order_type']) ? $subscription[0]['order_type'] : "";
                    $subscription_information['max_order_limit'] = isset($subscription[0]['max_order_limit']) ? $subscription[0]['max_order_limit'] : "";
                    $subscription_information['is_commision'] = isset($subscription[0]['is_commision']) ? $subscription[0]['is_commision'] : "";
                    $subscription_information['commission_threshold'] = isset($subscription[0]['commission_threshold']) ? $subscription[0]['commission_threshold'] : "";
                    $subscription_information['commission_percentage'] = isset($subscription[0]['commission_percentage']) ? $subscription[0]['commission_percentage'] : "";
                    $subscription_information['publish'] = isset($subscription[0]['publish']) ? $subscription[0]['publish'] : "";
                    $subscription_information['tax_id'] = isset($subscription[0]['tax_id']) ? $subscription[0]['tax_id'] : "";
                    $subscription_information['tax_type'] = isset($subscription[0]['tax_type']) ? $subscription[0]['tax_type'] : "";

                    if (!empty($subscription[0])) {

                        $price = calculate_partner_subscription_price($subscription[0]['partner_id'], $subscription[0]['subscription_id'], $subscription[0]['id']);
                    }
                    $subscription_information['tax_value'] = isset($price[0]['tax_value']) ? $price[0]['tax_percentage'] : "";
                    $subscription_information['price_with_tax']  = isset($price[0]['price_with_tax']) ? $price[0]['price_with_tax'] : "";
                    $subscription_information['original_price_with_tax'] = isset($price[0]['original_price_with_tax']) ? $price[0]['original_price_with_tax'] : "";
                    $subscription_information['tax_percentage'] = isset($price[0]['tax_percentage']) ? $price[0]['tax_percentage'] : "";

                    $data1['subscription_information'] = json_decode(json_encode($subscription_information), true);
                    $data1['location_information'] = json_decode(json_encode($location_information), true);
                    $data1['user'] = json_decode(json_encode($userdata), true);
                    unset($data1['user']['city']);
                    unset($data1['user']['latitude']);
                    unset($data1['user']['longitude']);
                    $data1['provder_information'] = json_decode(json_encode($partnerData), true);
                    unset($data1['provder_information']['tax_name']);
                    unset($data1['provder_information']['tax_number']);
                    unset($data1['provder_information']['account_number']);
                    unset($data1['provder_information']['account_name']);
                    unset($data1['provder_information']['bank_code']);
                    unset($data1['provder_information']['swift_code']);
                    unset($data1['provder_information']['bank_name']);
                    unset($data1['provder_information']['address']);
                    $data1['bank_information'] = json_decode(json_encode($bank_information), true);
                    $partner_timing_details = fetch_details('partner_timings', ['partner_id' => $data['id']]);
                    foreach ($partner_timing_details as $k => $val) {
                        $partner_timing_details[$k]['isOpen'] = $partner_timing_details[$k]['is_open'];
                        unset($partner_timing_details[$k]['is_open']);
                        $partner_timing_details[$k]['start_time'] = $partner_timing_details[$k]['opening_time'];
                        unset($partner_timing_details[$k]['opening_time']);
                        $partner_timing_details[$k]['end_time'] = $partner_timing_details[$k]['closing_time'];
                        unset($partner_timing_details[$k]['closing_time']);
                        unset($partner_timing_details[$k]['id']);
                        unset($partner_timing_details[$k]['partner_id']);
                        unset($partner_timing_details[$k]['created_at']);
                        unset($partner_timing_details[$k]['updated_at']);
                    }
                    $data1['working_days'] = json_decode(json_encode($partner_timing_details), true);
                    $response = [
                        'error' => false,
                        "token" => $token,
                        'message' => 'User Logged successfully',
                        'data' => $data1
                    ];
                    return $this->response->setJSON($response);
                } else {
                    // Login Failed
                    if (!exists([$identity_column => $identity], 'users')) {
                        $response = [
                            'error' => true,
                            'message' => 'User does not exists !',
                        ];
                        return $this->response->setJSON($response);
                    } else {
                        $response = [
                            'error' => true,
                            'message' => 'Incorrect login credentials. Please check and try again.',
                        ];
                        return $this->response->setJSON($response);
                    }
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => 'User does not exists !',
                ];
                return $this->response->setJSON($response);
            }
        } else {
            if (!exists([$identity_column => $identity], 'users')) {
                $response = [
                    'error' => true,
                    'message' => 'User does not exists !',
                ];
                return $this->response->setJSON($response);
            }
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    public function get_statistics()
    {
        /**
         * 
         * last_monthly_sales: 6 // default is 6, pass number to see last that many months sales
         * 
         */
        try {
            $db = \Config\Database::connect();
            $last_monthly_sales = (isset($_POST['last_monthly_sales']) && !empty(trim($_POST['last_monthly_sales']))) ? $this->request->getPost("last_monthly_sales") : 6;
            $partner_id = $this->user_details['id'];
            $categories = $db->table('categories c')->select('c.name as name,count(s.id) as total_services')
                ->where(['s.user_id' => $partner_id])
                ->join('services s', 's.category_id=c.id', 'left')
                ->groupBy('s.category_id')
                ->get()->getResultArray();
            if (!empty($categories)) {
                if ($categories[0]['name'] == '' && $categories[0]['total_services'] == 0) {
                    $this->data['caregories'] = [];
                } else {
                    $this->data['caregories'] = $categories;
                }
            } else {
                $categories = [];
            }
            // monthly earnings
            $monthly_sales = $db->table('orders')->select('MONTHNAME(date_of_service) as month, SUM(final_total) as total_amount')
                ->where('date_of_service BETWEEN CURDATE() - INTERVAL ' . $last_monthly_sales . ' MONTH AND CURDATE()')
                ->where(['partner_id' => $partner_id, 'date_of_service < ' => date("Y-m-d H:i:s"), "status" => "completed"])
                ->groupBy("MONTH(date_of_service)")
                ->get()->getResultArray();
            $month_wise_sales['monthly_sales'] = $monthly_sales;
            // $month_wise_sales['total_sales'] = array_map('intval', array_column($month_res, 'total_sale'));
            // $month_wise_sales['month_name'] = array_column($month_res, 'month_name');
            $this->data['monthly_earnings'] = $month_wise_sales;
            $total_orders = $db->table('orders o')->select('count(o.id) as `total`')->join('order_services os', 'os.order_id=o.id')
                ->join('users u', 'u.id=o.user_id')
                ->join('users up', 'up.id=o.partner_id')
                ->join('partner_details pd', 'o.partner_id = pd.partner_id')->where(['o.partner_id' => $partner_id])->get()->getResultArray()[0]['total'];
            $total_services = $db->table('services s')->select('count(s.id) as `total`')->where(['user_id' => $partner_id])->get()->getResultArray()[0]['total'];
            // $total_balance = $db->table('users u')->select('sum(u.balance) as `total`')->where(['id' => $partner_id])->get()->getResultArray()[0]['total'];
            
            
            
            
              $amount = fetch_details('orders', ['partner_id' => $partner_id, 'is_commission_settled' => '0'], ['sum(final_total) as total']);
                $db = \config\Database::connect();
                $builder = $db
                    ->table('orders')
                    ->select('sum(final_total) as total')
                    ->select('SUM(final_total) AS total_sale,DATE_FORMAT(created_at,"%b") AS month_name')
                    ->where('partner_id', $partner_id)
                    ->where('status', 'completed');
            
                // ->where('is_commission_settled', '0');
                $data = $builder->groupBy('created_at')->get()->getResultArray();
                $tempRow = array();
                $row1 = array();
                foreach ($data as $key => $row) {
                    $tempRow = $row['total'];
                    $row1[] = $tempRow;
                }
                // // $provider_earning_total=array($data);
                // if (isset($amount) && !empty($amount)) {
                //     //  commission will be in % here
                //     $admin_commission_percentage = get_admin_commision($partner_id);
                //     $admin_commission_amount = intval($admin_commission_percentage) / 100;
                //     $total = $amount[0]['total'];
                //     $commision = intval($total) * $admin_commission_amount;
                //     $unsettled_amount = $total - $commision;
                // }
                // $total_balance= array_map('intval', array_column($data, 'total_sale'));
            
           
                // $sum = 0; // Initialize a variable to hold the sum
                
                // foreach ($total_balance as $value) {
                //     $sum += $value; // Add each value to the sum
                // }
                $total_balance = unsettled_commision($partner_id);


            //  $total_balance=strval($sum);
            $total_ratings = $db->table('partner_details p')->select('count(p.ratings) as `total`')->where(['id' => $partner_id])->get()->getResultArray()[0]['total'];
            $number_or_ratings = $db->table('partner_details p')->select('count(p.number_of_ratings) as `total`')->where(['id' => $partner_id])->get()->getResultArray()[0]['total'];
            $income = $db->table('orders o')->select('count(o.id) as `total`')->where(['partner_id' => $partner_id])->where("created_at >= DATE(now()) - INTERVAL 7 DAY")->get()->getResultArray()[0]['total'];
            
            
            $total_cancel = $db->table('orders o')->select('count(o.id) as `total`')->where(['partner_id' => $partner_id])->where(["status" => "cancelled"])->get()->getResultArray()[0]['total'];
            $symbol =   get_currency();
            $this->data['total_services'] = ($total_services != 0) ? $total_services : "0";
            $this->data['total_orders'] = ($total_orders != 0) ? $total_orders : "0";
            $this->data['total_cancelled_orders'] = ($total_cancel != 0) ? $total_cancel : "0";
            $this->data['total_balance'] = ($total_balance != 0) ? strval($total_balance) : "0";
            $this->data['total_ratings'] = ($total_ratings != 0) ? $total_ratings : "0";
            $this->data['number_of_ratings'] = ($number_or_ratings != 0) ? $number_or_ratings : "0";
            $this->data['currency'] = $symbol;
            $this->data['income'] = ($income != 0) ? $income : "0";

            if (!empty($this->data)) {
                $response = [
                    'error' => false,
                    'message' => 'data fetched successfully.',
                    'data' => $this->data
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => 'No data found',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
            //throw $th;
        }
        // }
    }
    // public function verify_user()
    // {
    //     /* Parameters to be passed
    //         mobile: 9874565478
    //             or
    //         email: test@gmail.com */
    //     // try {
    //         $request = \Config\Services::request();
    //         $config = new \Config\IonAuth();
    //         $identity_column = $config->identity;
    //         $validation =  \Config\Services::validation();
    //         if ($identity_column == 'email') {
    //             $validation->setRule('email', 'Email', 'valid_email|required');
    //             $identity = $request->getPost('email');
    //         } elseif ($identity_column == 'phone') {
    //             $validation->setRule('mobile', 'Mobile', 'required');
    //             $identity = $request->getPost('mobile');
    //         }
    //         if (!$validation->withRequest($this->request)->run()) {
    //             $response['error'] = true;
    //             $response['message'] = $validation->getErrors();
    //             $response['data'] = array();
    //             return $this->response->setJSON($response);
    //         } else {
    //             if (($request->getPost('mobile')) && exists([$identity_column => $identity], 'users')) {
    //                 $userdata = fetch_details('users', ['phone' => $identity])[0];
    //                     print_r($userdata);
    //                     die;
    //                 $response['error'] = true;
    //                 $response['message'] = 'Phone Number is already registered.Please try again !';
    //                 $response['status']  = $userdata['active'];
    //                 return $this->response->setJSON($response);
    //             }
    //             if (($request->getPost('email')) && exists([$identity_column => $identity], 'users')) {
    //                 $userdata = fetch_details('users', ['phone' => $identity], ['active'])[0];
    //                 $response['error'] = true;
    //                 $response['message'] = 'Email is already registered.Please try again !';
    //                 $response['status']  = $userdata['active'];
    //                 print_r(json_encode($this->response));
    //                 return $this->response->setJSON($response);
    //             }
    //             $response['error'] = false;
    //             $response['message'] = 'User does not exist.';
    //             $response['data'] = array();
    //             return $this->response->setJSON($response);
    //         }
    //     // } catch (\Exception $th) {
    //     //     $response['error'] = true;
    //     //     $response['message'] = 'Something went wrong';
    //     //     return $this->response->setJSON($response);
    //     // }
    // }
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
        $builder = $db->table('partner_details pd');
        $builder->select(
            "pd.*,
            u.username as partner_name,u.balance,u.image,u.active,u.country_code, u.email, u.phone, u.city,u.longitude,u.latitude,u.payable_commision,
            ug.user_id,ug.group_id"
        )
            ->join('users u', 'pd.partner_id = u.id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('ug.group_id', 3)
            ->groupBy('pd.partner_id');
        $user = $builder->orderBy('id', 'ASC')->limit(0, 0)->get()->getResultArray();
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
    public function get_orders()
    {
        // try {
        $orders_model = new Orders_model();
        /*
            partner_id:44             
            limit:10           // { default - 25 } optional
            offset:0            // { default - 0 } optional
            sort:               id / name
                                // { default -row_id } optional
            order:DESC/ASC      // { default - ASC } optional
            search:value        // { optional }
            status:awaiting     // { optional }
        */
        $request = \Config\Services::request();
        $validation =  \Config\Services::validation();
        // $validation->setRules(
        //     [
        //         'partner_id' => 'numeric|required',
        //     ]
        // );
        // if (!$validation->withRequest($this->request)->run()) {
        //     $errors = $validation->getErrors();
        //     $response = [
        //         'error' => true,
        //         'message' => $errors,
        //         'data' => []
        //     ];
        //     return $this->response->setJSON($response);
        // }
        $partner_id = $this->user_details['id'];
        // print_r($partner_id);
        $status = !empty($this->request->getPost('status')) ? $this->request->getPost('status') : '';
        $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
        $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
        $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
        $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'DESC';
        $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
        // $filter['status'] = (isset($_POST['status']) && !empty($_POST['status'])) ? $this->request->getPost('status') : "";
        $status = ($this->request->getPost('status') && !empty($this->request->getPost('status'))) ? $this->request->getPost('status') : 0;
        $orders = $orders_model->list(true, $search, $limit, $offset, $sort, $order, ['o.partner_id' => $partner_id, 'o.status' => $status], '', '', '', '', '', true);


        $partner_id = $this->request->getPost('partner_id');
        $filter = array();
        $filter['user_id'] = $partner_id;
        $filter['status'] = $status;
        $total = $orders['total'];
        unset($orders['total']);
        if (!empty($orders) && $total != 0) {
            $response = [
                'error' => false,
                'message' => 'Orders fetched successfully.',
                'total' => $total,
                'data' => $orders
            ];
            return $this->response->setJSON($response);
        } else {
            $response = [
                'error' => true,
                'message' => 'No data found',
                'data' => []
            ];
            return $this->response->setJSON($response);
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    public function register()
    {
        $request = \Config\Services::request();

        if (!isset($_POST)) {
            $response = [
                'error' => true,
                'message' => "Please use Post request",
                'data' => []
            ];
            return $this->response->setJSON($response);
        }
        $ionAuth    = new \IonAuth\Libraries\IonAuth();
        $validation =  \Config\Services::validation();
        $request = \Config\Services::request();
        $config = new \Config\IonAuth();
        $identity_column = $config->identity;
        $tables  = $config->tables;
        $partners_model = new Partners_model();

        $db      = \Config\Database::connect();
        $builder = $db->table('users u');
        $builder->select('u.*,ug.group_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('ug.group_id', "3")
            ->where('u.phone', $request->getPost('mobile'));;

        $user_record = $builder->orderBy('id', 'DESC')->limit(0, 0)->get()->getResultArray();

        //update
        if (exists(['phone' => $request->getPost('mobile')], 'users') && !empty($user_record)) {


            // $user_id = $this->user_details['id'];
            $userdata = fetch_details('users', ["phone" => $request->getPost('mobile')], ['id', 'username', 'email', 'balance', 'active', 'first_name', 'last_name', 'company', 'phone', 'country_code', 'fcm_id', 'image', 'city_id', 'city', 'latitude', 'longitude'])[0];
            //insert customer in user group table
            $group =  get_group('partners');
            $group_id = [
                'group_id' => 3
            ];




            $user_id =  ($userdata['id']);
            $userdata = fetch_details('users', ['id' => $user_id], ['id', 'username', 'email', 'balance', 'active', 'first_name', 'last_name', 'company', 'phone', 'country_code', 'fcm_id', 'image', 'city_id', 'city', 'latitude', 'longitude'])[0];
            $partnerData = fetch_details('partner_details', ['partner_id' => $user_id])[0];
            // print_r()
            //partner
            if (!empty($request->getPost('company_name'))) {
                $partner['company_name'] = $request->getPost('company_name');
            }
            if (!empty($request->getPost('type'))) {
                $partner['type'] = $request->getPost('type');
            }
            if (!empty($request->getPost('about_provider'))) {
                $partner['about'] = $request->getPost('about_provider');
            }
            if (!empty($request->getPost('visiting_charges'))) {
                $partner['visiting_charges'] = $request->getPost('visiting_charges');
            }
            if (!empty($request->getPost('advance_booking_days'))) {
                $partner['advance_booking_days'] = $request->getPost('advance_booking_days');
            }
            if (!empty($request->getPost('number_of_members'))) {
                $partner['number_of_members'] = $request->getPost('number_of_members');
            }
            if (!empty($request->getPost('tax_name'))) {
                $partner['tax_name'] = $request->getPost('tax_name');
            }
            if (!empty($request->getPost('tax_number'))) {
                $partner['tax_number'] = $request->getPost('tax_number');
            }
            if (!empty($request->getPost('account_number'))) {
                $partner['account_number'] = $request->getPost('account_number');
            }
            if (!empty($request->getPost('account_name'))) {
                $partner['account_name'] = $request->getPost('account_name');
            }
            if (!empty($request->getPost('bank_code'))) {
                $partner['bank_code'] = $request->getPost('bank_code');
            }
            if (!empty($request->getPost('swift_code'))) {
                $partner['swift_code'] = $request->getPost('swift_code');
            }
            if (!empty($request->getPost('bank_name'))) {
                $partner['bank_name'] = $request->getPost('bank_name');
            }
            if (!empty($request->getPost('address'))) {
                $partner['address'] = $request->getPost('address');
            }
            $IdProofs = fetch_details('partner_details', ['partner_id' => $user_id], ['national_id', 'address_id', 'passport', 'banner', 'other_images'])[0];
            $old_image = $userdata['image'];
            $old_banner = $IdProofs['banner'];
            $old_national_id = $IdProofs['national_id'];
            $old_address_id = $IdProofs['address_id'];
            $old_passport = $IdProofs['passport'];
            $old_other_images = $IdProofs['other_images'];

            if (!empty($_FILES['banner_image']) && isset($_FILES['banner_image'])) {
                $file =  $this->request->getFile('banner_image');
                $path =  './public/backend/assets/banner/';
                $path_db =  'public/backend/assets/banner/';
                if ($file->isValid()) {
                    if ($file->move($path)) {
                        if (file_exists($old_banner) && !empty($old_banner))
                            unlink(FCPATH . $old_banner);
                        $banner = $path_db . $file->getName();
                        $partner['banner'] = $banner;
                    }
                }
            }
            if (!empty($_FILES['national_id']) && isset($_FILES['national_id'])) {
                $file =  $this->request->getFile('national_id');
                $path =  './public/backend/assets/national_id/';
                $path_db =  'public/backend/assets/national_id/';
                if ($file->isValid()) {
                    if ($file->move($path)) {
                        if (file_exists($old_national_id) && !empty($old_national_id))
                            unlink($old_national_id);
                        $national_id = $path_db . $file->getName();
                        $partner['national_id'] = $national_id;
                    }
                }
            }
            if (!empty($_FILES['address_id']) && isset($_FILES['address_id'])) {
                $file =  $this->request->getFile('address_id');
                $path =  './public/backend/assets/address_id/';
                $path_db =  'public/backend/assets/address_id/';
                if ($file->isValid()) {
                    if ($file->move($path)) {
                        if (file_exists($old_address_id) && !empty($old_address_id))
                            unlink($old_address_id);
                        $address_id = $path_db . $file->getName();
                        $partner['address_id'] = $address_id;
                    }
                }
            }
            if (!empty($_FILES['passport']) && isset($_FILES['passport'])) {
                $file =  $this->request->getFile('passport');
                $path =  './public/backend/assets/passport/';
                $path_db =  'public/backend/assets/passport/';
                if ($file->isValid()) {
                    if ($file->move($path)) {
                        if (file_exists($old_passport) && !empty($old_passport))
                            unlink($old_passport);
                        $passport = $path_db . $file->getName();
                        $partner['passport'] = $passport;
                    }
                }
            }

            $uploaded_other_images = $this->request->getFiles('other_images');
            $other_image_names['name'] = [];
            $data['images'] = [];
            $path = "public/uploads/partner/";
            if (isset($uploaded_other_images['other_images'])) {
                foreach ($uploaded_other_images['other_images'] as $images) {
                    $validate_image = valid_image($images);
                    if ($validate_image == true) {
                        return response("Invalid Image", true, []);
                    }
                    $newName = $images->getRandomName();
                    if ($newName != null) {
                        move_file($images, $path, $newName);
                        if (!empty($old_other_images)) {
                            $old_other_images_array = json_decode($old_other_images, true); // Decode JSON string to associative array
                            foreach ($old_other_images_array as $old) {
                                if (file_exists(FCPATH . $old)) {
                                    unlink(FCPATH . $old);
                                }
                            }
                        }
                        $name = "public/uploads/partner/$newName";
                        array_push($other_image_names['name'], $name);
                    }
                }
                $other_images = json_encode($other_image_names['name']);
            }
            $partner['other_images'] =  isset($other_images) ? $other_images : $old_other_images;
            $partner['long_description'] = (isset($_POST['long_description'])) ? $_POST['long_description'] : "";


            //user
            if (!empty($request->getPost('city'))) {
                $user['city'] = $request->getPost('city');
            }
            if (!empty($request->getPost('latitude'))) {
                if (!preg_match('/^-?(90|[1-8][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('latitude'))) {
                    $response['error'] = true;
                    $response['message'] = "Please enter valid latitude";
                    return $this->response->setJSON($response);
                }
                $user['latitude'] = $request->getPost('latitude');
            }
            if (!empty($request->getPost('longitude'))) {
                if (!preg_match('/^-?(180(\.0{1,20})?|1[0-7][0-9](\.[0-9]{1,20})?|[1-9][0-9](\.[0-9]{1,20})?|[0-9](\.[0-9]{1,20})?)$/', $this->request->getPost('longitude'))) {
                // if (!preg_match('/^-?(180|1[1-7][0-9][.][0-9]{1,20}|[1-9][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('longitude'))) {
                    $response['error'] = true;
                    $response['message'] = "Please enter valid Longitude";
                    return $this->response->setJSON($response);
                }
                $user['longitude'] = $request->getPost('longitude');
            }
            if (!empty($request->getPost('username'))) {
                $user['username'] = $request->getPost('username');
            }
            if (!empty($request->getPost('email'))) {
                $user['email'] = $request->getPost('email');
            }
            if (!empty($_FILES['image']) && isset($_FILES['image'])) {
                $file =  $this->request->getFile('image');
                $path =  './public/backend/assets/profile/';
                $path_db =  'public/backend/assets/profile/';
                if ($file->isValid()) {
                    if ($file->move($path)) {
                        if (file_exists($old_image) && !empty($old_image))
                            unlink(FCPATH . $old_image);
                        $image = $path_db . $file->getName();
                        $user['image'] = $image;
                    }
                }
            }
            if (!empty($request->getPost('days'))) {
                $working_days = json_decode($request->getPost('days'), true);
                $tempRowDaysIsOpen = array();
                $rowsDays = array();
                $tempRowDays = array();
                $tempRowStartTime = array();
                $tempRowEndTime = array();
                foreach ($working_days as $row) {
                    $tempRowDaysIsOpen[] = $row['isOpen'];
                    $tempRowDays[] = $row['day'];
                    $tempRowStartTime[] = $row['start_time'];
                    $tempRowEndTime[] = $row['end_time'];
                }
                for ($i = 0; $i < count($tempRowStartTime); $i++) {
                    $partner_timing = [];
                    $partner_timing['day'] = $tempRowDays[$i];
                    if (isset($tempRowStartTime[$i])) {
                        $partner_timing['opening_time'] = $tempRowStartTime[$i];
                    }
                    if (isset($tempRowEndTime[$i])) {
                        $partner_timing['closing_time'] = $tempRowEndTime[$i];
                    }
                    $partner_timing['is_open'] = $tempRowDaysIsOpen[$i];
                    $partner_timing['partner_id'] = $userdata['id'];
                    update_details($partner_timing, ['partner_id' =>  $userdata['id'], 'day' => $tempRowDays[$i]], 'partner_timings');
                }
            }
            $update_user = update_details($user, ['id' => $user_id], "users", false);
            $update_partner = update_details($partner, ['partner_id' => $user_id], 'partner_details', false);
            $partner_id = $user_id;
            $IdProofsupdated = fetch_details('partner_details', ['partner_id' => $user_id], ['national_id', 'address_id', 'passport', 'banner', 'other_images'])[0];
            $userIdImage = fetch_details('users', ['id' => $user_id], ['image'])[0];
            if ($update_user && $update_partner) {
                $userdata = fetch_details('users', ['id' => $user_id], ['id', 'username', 'email', 'balance', 'active', 'first_name', 'last_name', 'company', 'phone', 'country_code', 'fcm_id', 'image', 'city_id', 'city', 'latitude', 'longitude'])[0];
                $partnerData = fetch_details('partner_details', ['partner_id' => $user_id])[0];
                if (file_exists($userIdImage['image'])) {
                    $userdata['image'] = (file_exists($userIdImage['image'])) ? base_url($userIdImage['image']) : "";
                }
                if (file_exists($IdProofsupdated['banner'])) {
                    $partnerData['banner'] = (file_exists($IdProofsupdated['banner'])) ? base_url($IdProofsupdated['banner']) : "";
                }
                if (file_exists($IdProofsupdated['address_id'])) {
                    $partnerData['address_id'] = (file_exists($IdProofsupdated['address_id'])) ? base_url($IdProofsupdated['address_id']) : "";
                }
                if (file_exists($IdProofsupdated['passport'])) {
                    $partnerData['passport'] = (file_exists($IdProofsupdated['passport'])) ? base_url($IdProofsupdated['passport']) : "";
                }
                if (file_exists($IdProofsupdated['national_id'])) {
                    $partnerData['national_id'] = (file_exists($IdProofsupdated['national_id'])) ? base_url($IdProofsupdated['national_id']) : "";
                }


                if (!empty($IdProofsupdated['other_images'])) {
                    $partnerData['other_images'] = array_map(function ($data) {
                        return base_url($data);
                    }, json_decode($partnerData['other_images'], true));
                }


                //location information
                $location_information['city'] = $userdata['city'];
                $location_information['latitude'] = $userdata['latitude'];
                $location_information['longitude'] = $userdata['longitude'];
                $location_information['longitude'] = $userdata['longitude'];
                $location_information['address'] = $partnerData['address'];
                $bank_information['tax_name'] = $partnerData['tax_name'];
                $bank_information['tax_number'] = $partnerData['tax_number'];
                $bank_information['account_number'] = $partnerData['account_number'];
                $bank_information['account_name'] = $partnerData['account_name'];
                $bank_information['bank_code'] = $partnerData['bank_code'];
                $bank_information['bank_code'] = $partnerData['bank_code'];
                $bank_information['swift_code'] = $partnerData['swift_code'];
                $bank_information['bank_name'] = $partnerData['bank_name'];

                $data['location_information'] = json_decode(json_encode($location_information), true);
                $subscription = fetch_details('partner_subscriptions', ['partner_id' => $partnerData['id']], [], 1, 0, 'id', 'DESC');

                $subscription_information['subscription_id'] = isset($subscription[0]['subscription_id']) ? $subscription[0]['subscription_id'] : "";

                $subscription_information['isSubscriptionActive'] = isset($subscription[0]['status']) ? $subscription[0]['status'] : "deactive";
                $subscription_information['created_at'] = isset($subscription[0]['created_at']) ? $subscription[0]['created_at'] : "";
                $subscription_information['updated_at'] = isset($subscription[0]['updated_at']) ? $subscription[0]['updated_at'] : "";
                $subscription_information['is_payment'] = isset($subscription[0]['is_payment']) ? $subscription[0]['is_payment'] : "";
                $subscription_information['id'] = isset($subscription[0]['id']) ? $subscription[0]['id'] : "";
                $subscription_information['partner_id'] = isset($subscription[0]['partner_id']) ? $subscription[0]['partner_id'] : "";
                $subscription_information['purchase_date'] = isset($subscription[0]['purchase_date']) ? $subscription[0]['purchase_date'] : "";
                $subscription_information['expiry_date'] = isset($subscription[0]['expiry_date']) ? $subscription[0]['expiry_date'] : "";
                $subscription_information['name'] = isset($subscription[0]['name']) ? $subscription[0]['name'] : "";
                $subscription_information['description'] = isset($subscription[0]['description']) ? $subscription[0]['description'] : "";
                $subscription_information['duration'] = isset($subscription[0]['duration']) ? $subscription[0]['duration'] : "";
                $subscription_information['price'] = isset($subscription[0]['price']) ? $subscription[0]['price'] : "";
                $subscription_information['discount_price'] = isset($subscription[0]['discount_price']) ? $subscription[0]['discount_price'] : "";
                $subscription_information['order_type'] = isset($subscription[0]['order_type']) ? $subscription[0]['order_type'] : "";
                $subscription_information['max_order_limit'] = isset($subscription[0]['max_order_limit']) ? $subscription[0]['max_order_limit'] : "";
                $subscription_information['is_commision'] = isset($subscription[0]['is_commision']) ? $subscription[0]['is_commision'] : "";
                $subscription_information['commission_threshold'] = isset($subscription[0]['commission_threshold']) ? $subscription[0]['commission_threshold'] : "";
                $subscription_information['commission_percentage'] = isset($subscription[0]['commission_percentage']) ? $subscription[0]['commission_percentage'] : "";
                $subscription_information['publish'] = isset($subscription[0]['publish']) ? $subscription[0]['publish'] : "";
                $subscription_information['tax_id'] = isset($subscription[0]['tax_id']) ? $subscription[0]['tax_id'] : "";
                $subscription_information['tax_type'] = isset($subscription[0]['tax_type']) ? $subscription[0]['tax_type'] : "";


                if (!empty($subscription[0])) {
                    $price = calculate_partner_subscription_price($subscription[0]['partner_id'], $subscription[0]['subscription_id'], $subscription[0]['id']);
                }
                $subscription_information['tax_value'] = isset($price[0]['tax_value']) ? $price[0]['tax_value'] : "";
                $subscription_information['price_with_tax']  = isset($price[0]['price_with_tax']) ? $price[0]['price_with_tax'] : "";
                $subscription_information['original_price_with_tax'] = isset($price[0]['original_price_with_tax']) ? $price[0]['original_price_with_tax'] : "";
                $subscription_information['tax_percentage'] = isset($price[0]['tax_percentage']) ? $price[0]['tax_percentage'] : "";

                $data['subscription_information'] = json_decode(json_encode($subscription_information), true);
                $data['user'] = json_decode(json_encode($userdata), true);
                unset($data['user']['city']);
                unset($data['user']['latitude']);
                unset($data['user']['longitude']);
                $data['provder_information'] = json_decode(json_encode($partnerData), true);
                unset($data['provder_information']['tax_name']);
                unset($data['provder_information']['tax_number']);
                unset($data['provder_information']['account_number']);
                unset($data['provder_information']['account_name']);
                unset($data['provder_information']['bank_code']);
                unset($data['provder_information']['swift_code']);
                unset($data['provder_information']['bank_name']);
                unset($data['provder_information']['address']);
                $data['bank_information'] = json_decode(json_encode($bank_information), true);
                if ($request->getPost('days')) {
                    $data['working_days'] = json_decode($request->getPost('days'), true);
                } else {
                    $partner_timing_details = fetch_details('partner_timings', ['partner_id' => $partner_id]);
                    foreach ($partner_timing_details as $k => $val) {
                        $partner_timing_details[$k]['isOpen'] = $partner_timing_details[$k]['is_open'];
                        unset($partner_timing_details[$k]['is_open']);
                        $partner_timing_details[$k]['start_time'] = $partner_timing_details[$k]['opening_time'];
                        unset($partner_timing_details[$k]['opening_time']);
                        $partner_timing_details[$k]['end_time'] = $partner_timing_details[$k]['closing_time'];
                        unset($partner_timing_details[$k]['closing_time']);
                        unset($partner_timing_details[$k]['id']);
                        unset($partner_timing_details[$k]['partner_id']);
                        unset($partner_timing_details[$k]['created_at']);
                        unset($partner_timing_details[$k]['updated_at']);
                    }
                    $data['working_days'] = json_decode(json_encode($partner_timing_details), true);
                }
                $response = [
                    'error' => false,
                    'message' => 'User Updated successfully',
                    'data' => $data,
                ];
                // send_web_notification('Provider Updated',  $request->getPost('company_name') . ' Updated details');
                send_web_notification('Provider Updated',  $request->getPost('company_name') . ' Updated details', null, 'https://edemand-test.thewrteam.in/admin/partners');
                $db      = \Config\Database::connect();
                $builder = $db->table('users u');
                $users = $builder->Select("u.id,u.fcm_id,u.username,u.email")
                    ->join('users_groups ug', 'ug.user_id=u.id')
                    ->where('ug.group_id', '1')
                    ->get()->getResultArray();
                $settings = get_settings('general_settings', true);
                $icon = $settings['logo'];
                $data = array(
                    'name' => $users[0]['username'],
                    'provider_name' => $request->getPost('company_name'),
                    'provider_email' =>   $request->getPost('email'),
                    'provoder_phone' => $request->getPost('mobile'),
                    'title' => "Update Provider Information",
                    'logo' => base_url("public/uploads/site/" . $icon),
                    'first_paragraph' => 'I am writing to inform you that a provider has recently updated their details on our platform. Here are the updated details:',
                    'second_paragraph' => 'Please review these changes and ensure that the user\'s account is up to date.',
                    'third_paragraph' => 'Thank you for your attention to this matter.',
                    'company_name' => $settings['company_title'],
                );
                email_sender($users[0]['email'], 'Update Provider Information', view('backend/admin/pages/provider_email', $data));
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => false,
                    'message' => 'Something went wrong',
                ];
            }
            return $this->response->setJSON($response);
        }
        //new provider
        else {

            // $db      = \Config\Database::connect();
            // $builder = $db->table('users u');
            // $builder->select('u.*,ug.group_id')
            //     ->join('users_groups ug', 'ug.user_id = u.id')
            //     ->where('ug.group_id', "2")
            //     ->where('u.phone', $request->getPost('mobile'));;

            // $user_record = $builder->orderBy('id', 'DESC')->limit(0, 0)->get()->getResultArray();


            // if (!empty($user_record)) {
            //     $response = [
            //         'error' => true,
            //         'message' => "User already registerd as customer using this mobile number.",

            //     ];
            //     return $this->response->setJSON($response);
            // }

            $validation->setRules(
                [
                    'company_name' => 'required',
                    'country_code' => 'required',

                    'username' => 'required',
                    'email' => 'required|valid_email|',
                    'mobile' => 'required|numeric|',
                    'password' => 'required|matches[password_confirm]',
                    'password_confirm' => 'required',
                ],
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $company_name = $request->getPost('company_name');
            $username = $request->getPost('username');
            $email = $request->getPost('email');
            $password = $request->getPost('password');
            $password_confirm = $request->getPost('password_confirm');
            $mobile = $request->getPost('mobile');
            $type =  ($request->getPost('type') && !empty($request->getPost('type'))) ? $request->getPost('type') : "";
            $about_provider = ($request->getPost('about_provider')) ? $request->getPost('about_provider') : "";
            $visiting_charges = ($request->getPost('visiting_charges')) ? $request->getPost('visiting_charges') : "";
            $advance_booking_days = ($request->getPost('advance_booking_days')) ? $request->getPost('advance_booking_days') : "";
            $number_of_members = ($request->getPost('number_of_members')) ? $request->getPost('number_of_members') : "";
            $current_location = ($request->getPost('current_location')) ? $request->getPost('current_location') : "";
            $city = ($request->getPost('city')) ? $request->getPost('city') : "";
            $latitude = ($request->getPost('latitude')) ? $request->getPost('latitude') : "";
            $longitude = ($request->getPost('longitude')) ? $request->getPost('longitude') : "";
            $address = ($request->getPost('address')) ? $request->getPost('address') : "";
            $tax_name = ($request->getPost('tax_name')) ? $request->getPost('tax_name') : "";
            $tax_number = ($request->getPost('tax_number')) ? $request->getPost('tax_number') : "";
            $account_name = ($request->getPost('account_name')) ? $request->getPost('account_name') : "";
            $account_number = ($request->getPost('account_number')) ? $request->getPost('account_number') : "";
            $bank_code = ($request->getPost('bank_code')) ? $request->getPost('bank_code') : "";
            $bank_name = ($request->getPost('bank_name')) ? $request->getPost('bank_name') : "";
            $swift_code = $request->getPost('swift_code');
            $fcm_id = ($request->getPost('fcm_id') && !empty($request->getPost('fcm_id'))) ? $request->getPost('fcm_id') : "";
            $friends_code = ($request->getPost('friends_code')) ? $request->getPost('friends_code') : "";
            $referral_code = ($request->getPost('referral_code')) ? $request->getPost('referral_code') : "";
            $city_id = ($request->getPost('city_id')) ? $request->getPost('city_id') : "";
            if (!empty($request->getPost('latitude'))) {
                if (!preg_match('/^-?(90|[1-8][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('latitude'))) {
                    $response['error'] = true;
                    $response['message'] = "Please enter valid latitude";
                    return $this->response->setJSON($response);
                }
                $user['latitude'] = $request->getPost('latitude');
            }
            if (!empty($request->getPost('longitude'))) {
                if (!preg_match('/^-?(180(\.0{1,20})?|1[0-7][0-9](\.[0-9]{1,20})?|[1-9][0-9](\.[0-9]{1,20})?|[0-9](\.[0-9]{1,20})?)$/', $this->request->getPost('longitude'))) {

                // if (!preg_match('/^-?(180|1[1-7][0-9][.][0-9]{1,20}|[1-9][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('longitude'))) {
                    $response['error'] = true;
                    $response['message'] = "Please enter valid Longitude";
                    return $this->response->setJSON($response);
                }
                $user['longitude'] = $request->getPost('longitude');
            }
            if (!empty($_FILES['image']) && isset($_FILES['image'])) {
                $file =  $this->request->getFile('image');
                $path =  './public/backend/assets/profile/';
                $path_db =  'public/backend/assets/profile/';
                if ($file->isValid()) {
                    if ($file->move($path)) {
                        $image = $path_db . $file->getName();
                    }
                }
            }
            if (!empty($_FILES['banner_image']) && isset($_FILES['banner_image'])) {
                $file =  $this->request->getFile('banner_image');
                $path =  './public/backend/assets/banner/';
                $path_db =  'public/backend/assets/banner/';
                if ($file->isValid()) {
                    if ($file->move($path)) {
                        $banner = $path_db . $file->getName();
                    }
                }
            }
            if (!empty($_FILES['national_id']) && isset($_FILES['national_id'])) {
                $file =  $this->request->getFile('national_id');
                $path =  './public/backend/assets/national_id/';
                $path_db =  'public/backend/assets/national_id/';
                if ($file->isValid()) {
                    if ($file->move($path)) {
                        $national_id = $path_db . $file->getName();
                    }
                }
            }
            if (!empty($_FILES['address_id']) && isset($_FILES['address_id'])) {
                $file =  $this->request->getFile('address_id');
                $path =  './public/backend/assets/address_id/';
                $path_db =  'public/backend/assets/address_id/';
                if ($file->isValid()) {
                    if ($file->move($path)) {
                        $address_id = $path_db . $file->getName();
                    }
                }
            }
            if (!empty($_FILES['passport']) && isset($_FILES['passport'])) {
                $file =  $this->request->getFile('passport');
                $path =  './public/backend/assets/passport/';
                $path_db =  'public/backend/assets/passport/';
                if ($file->isValid()) {
                    if ($file->move($path)) {
                        $passport = $path_db . $file->getName();
                    }
                }
            }

            $uploaded_other_images = $this->request->getFiles('other_images');
            $other_image_names['name'] = [];
            $data['images'] = [];
            $path = "public/uploads/partner/";
            if (isset($uploaded_other_images['other_images'])) {
                foreach ($uploaded_other_images['other_images'] as $images) {
                    $validate_image = valid_image($images);
                    if ($validate_image == true) {
                        return response("Invalid Image", true, []);
                    }
                    $newName = $images->getRandomName();
                    if ($newName != null) {
                        move_file($images, $path, $newName);
                        if (!empty($old_other_images)) {
                            $old_other_images_array = json_decode($old_other_images, true); // Decode JSON string to associative array
                            foreach ($old_other_images_array as $old) {
                                if (file_exists(FCPATH . $old)) {
                                    unlink(FCPATH . $old);
                                }
                            }
                        }
                        $name = "public/uploads/partner/$newName";
                        array_push($other_image_names['name'], $name);
                    }
                }
                $other_images = json_encode($other_image_names['name']);
            }
            $additional_data = [
                'username' => $username,
                'active' => '1',
                'phone' => $mobile,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'city' => $city_id,
                'image' => isset($image) ? $image : "",
                'country_code' => $request->getPost('country_code'),
            ];
            if ($request->getPost('fcm_id')) {
                $additional_data['fcm_id'] = $fcm_id;
            }
            //insert customer in user group table
            $group =  get_group('partners');
            $group_id = [
                'group_id' => 3
            ];


            if ($this->request->getPost() && $validation->withRequest($this->request)->run() && $user_id = $ionAuth->register($mobile, $password, $email, $additional_data, $group_id)) {
                $data = array();
                $token = generate_tokens($mobile, 3);
                //insert token data 
                $token_data['user_id'] = $user_id;
                $token_data['token'] = $token;
                if (isset($token_data) && !empty($token_data)) {
                    insert_details($token_data, 'users_tokens');
                }
                update_details(['api_key' => $token], ['username' => $username], "users");
                $data = fetch_details('users', ['id' => $user_id], $this->user_data)[0];
                //remove null value 
                $data = remove_null_values($data);
                $partner_id = $data['id'];
                $partner = [
                    'partner_id' => $partner_id,
                    'company_name' => $company_name,
                    'national_id' => isset($national_id) ? $national_id : "",
                    'address_id' => isset($address_id) ? $address_id : "",
                    'passport' => isset($passport) ? $passport : "",
                    'address' => $address,
                    'tax_name' => $tax_name,
                    'tax_number' => $tax_number,
                    'advance_booking_days' => $advance_booking_days,
                    'type' => $type,
                    'number_of_members' => $number_of_members,
                    'visiting_charges' => $visiting_charges,
                    'account_number' => $account_number,
                    'account_name' => $account_name,
                    'bank_name' => $bank_name,
                    'bank_code' => $bank_code,
                    'swift_code' => $swift_code,
                    'about' => $about_provider,
                    'ratings' => 0,
                    'number_of_ratings' => 0,
                    'is_approved' => ((defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0)) ? 1 : 2,
                    'banner' => isset($banner) ? $banner : "",
                    'other_images' => isset($other_images) ? $other_images : "",
                    'long_description' => (isset($_POST['long_description'])) ? $_POST['long_description'] : "",

                ];
                $partners_model->insert($partner);
                if ($request->getPost('days')) {
                    $working_days = json_decode($_POST['days'], true);
                    $tempRowDaysIsOpen = array();
                    $rowsDays = array();
                    $tempRowDays = array();
                    $tempRowStartTime = array();
                    $tempRowEndTime = array();
                    foreach ($working_days as $row) {
                        $tempRowDaysIsOpen[] = $row['isOpen'];
                        $tempRowDays[] = $row['day'];
                        $tempRowStartTime[] = $row['start_time'];
                        $tempRowEndTime[] = $row['end_time'];
                        // // print_r($row);
                        $rowsDays[] = $tempRowDays;
                    }
                    for ($i = 0; $i < count($tempRowStartTime); $i++) {
                        $partner_timing = [];
                        $partner_timing['day'] = $tempRowDays[$i];
                        if (isset($tempRowStartTime[$i])) {
                            $partner_timing['opening_time'] = $tempRowStartTime[$i];
                        }
                        if (isset($tempRowEndTime[$i])) {
                            $partner_timing['closing_time'] = $tempRowEndTime[$i];
                        }
                        $partner_timing['is_open'] = $tempRowDaysIsOpen[$i];
                        $partner_timing['partner_id'] = $data['id'];
                        insert_details($partner_timing, 'partner_timings');
                    }
                } else {
                    $tempRowDaysIsOpen = array(0, 0, 0, 0, 0, 0, 0);
                    $rowsDays = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
                    $tempRowDays = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
                    $tempRowStartTime = array('09:00:00', '09:00:00', '09:00:00', '09:00:00', '09:00:00', '09:00:00', '09:00:00');
                    $tempRowEndTime = array('10:00:00', '10:00:00', '10:00:00', '10:00:00', '10:00:00', '10:00:00', '10:00:00');
                    for ($i = 0; $i < count($tempRowStartTime); $i++) {
                        $partner_timing = [];
                        $partner_timing['day'] = $tempRowDays[$i];
                        if (isset($tempRowStartTime[$i])) {
                            $partner_timing['opening_time'] = $tempRowStartTime[$i];
                        }
                        if (isset($tempRowEndTime[$i])) {
                            $partner_timing['closing_time'] = $tempRowEndTime[$i];
                        }
                        $partner_timing['is_open'] = $tempRowDaysIsOpen[$i];
                        $partner_timing['partner_id'] = $data['id'];
                        insert_details($partner_timing, 'partner_timings');
                    }
                }
                $userdata = fetch_details('users', ['id' => $data['id']], ['id', 'username', 'email', 'balance', 'active', 'first_name', 'last_name', 'company', 'phone', 'country_code', 'fcm_id', 'image', 'city_id', 'city', 'latitude', 'longitude'])[0];
                $partnerData = fetch_details('partner_details', ['partner_id' => $data['id']])[0];
                if (file_exists($additional_data['image'])) {
                    $userdata['image'] = (file_exists($additional_data['image'])) ? base_url($additional_data['image']) : "";
                }
                if (file_exists($partner['banner'])) {
                    $partnerData['banner'] = (file_exists($partner['banner'])) ? base_url($partner['banner']) : "";
                }
                if (file_exists($partner['address_id'])) {
                    $partnerData['address_id'] = (file_exists($partner['address_id'])) ? base_url($partner['address_id']) : "";
                }
                if (file_exists($partner['passport'])) {
                    $partnerData['passport'] = (file_exists($partner['passport'])) ? base_url($partner['passport']) : "";
                }
                if (file_exists($partner['national_id'])) {
                    $partnerData['national_id'] = (file_exists($partner['national_id'])) ? base_url($partner['national_id']) : "";
                }




                if (!empty($partner['other_images'])) {
                    $partnerData['other_images'] = array_map(function ($data) {
                        return base_url($data);
                    }, json_decode($partnerData['other_images'], true));
                } else {
                    $partnerData['other_images'] = []; // Return an empty array
                }


                $location_information['city'] = $userdata['city'];
                $location_information['latitude'] = $userdata['latitude'];
                $location_information['longitude'] = $userdata['longitude'];
                $location_information['longitude'] = $userdata['longitude'];
                $location_information['address'] = $partnerData['address'];
                $bank_information['tax_name'] = $partnerData['tax_name'];
                $bank_information['tax_number'] = $partnerData['tax_number'];
                $bank_information['account_number'] = $partnerData['account_number'];
                $bank_information['account_name'] = $partnerData['account_name'];
                $bank_information['bank_code'] = $partnerData['bank_code'];
                $bank_information['bank_code'] = $partnerData['bank_code'];
                $bank_information['swift_code'] = $partnerData['swift_code'];
                $bank_information['bank_name'] = $partnerData['bank_name'];
                $data1['location_information'] = json_decode(json_encode($location_information), true);

                $data1['user'] = json_decode(json_encode($userdata), true);

                $subscription = fetch_details('partner_subscriptions', ['partner_id' => $partnerData['id']]);

                $subscription_information['subscription_id'] = isset($subscription[0]['subscription_id']) ? $subscription[0]['subscription_id'] : "";
                $subscription_information['isSubscriptionActive'] = isset($subscription[0]['status']) ? $subscription[0]['status'] : "deactive";
                $subscription_information['created_at'] = isset($subscription[0]['created_at']) ? $subscription[0]['created_at'] : "";
                $subscription_information['updated_at'] = isset($subscription[0]['updated_at']) ? $subscription[0]['updated_at'] : "";
                $subscription_information['is_payment'] = isset($subscription[0]['is_payment']) ? $subscription[0]['is_payment'] : "";
                $subscription_information['id'] = isset($subscription[0]['id']) ? $subscription[0]['id'] : "";
                $subscription_information['partner_id'] = isset($subscription[0]['partner_id']) ? $subscription[0]['partner_id'] : "";
                $subscription_information['purchase_date'] = isset($subscription[0]['purchase_date']) ? $subscription[0]['purchase_date'] : "";
                $subscription_information['expiry_date'] = isset($subscription[0]['expiry_date']) ? $subscription[0]['expiry_date'] : "";
                $subscription_information['name'] = isset($subscription[0]['name']) ? $subscription[0]['name'] : "";
                $subscription_information['description'] = isset($subscription[0]['description']) ? $subscription[0]['description'] : "";
                $subscription_information['duration'] = isset($subscription[0]['duration']) ? $subscription[0]['duration'] : "";
                $subscription_information['price'] = isset($subscription[0]['price']) ? $subscription[0]['price'] : "";
                $subscription_information['discount_price'] = isset($subscription[0]['discount_price']) ? $subscription[0]['discount_price'] : "";
                $subscription_information['order_type'] = isset($subscription[0]['order_type']) ? $subscription[0]['order_type'] : "";
                $subscription_information['max_order_limit'] = isset($subscription[0]['max_order_limit']) ? $subscription[0]['max_order_limit'] : "";
                $subscription_information['is_commision'] = isset($subscription[0]['is_commision']) ? $subscription[0]['is_commision'] : "";
                $subscription_information['commission_threshold'] = isset($subscription[0]['commission_threshold']) ? $subscription[0]['commission_threshold'] : "";
                $subscription_information['commission_percentage'] = isset($subscription[0]['commission_percentage']) ? $subscription[0]['commission_percentage'] : "";
                $subscription_information['publish'] = isset($subscription[0]['publish']) ? $subscription[0]['publish'] : "";
                $subscription_information['tax_id'] = isset($subscription[0]['tax_id']) ? $subscription[0]['tax_id'] : "";
                $subscription_information['tax_type'] = isset($subscription[0]['tax_type']) ? $subscription[0]['tax_type'] : "";



                if (!empty($subscription[0])) {

                    $price = calculate_partner_subscription_price($subscription[0]['partner_id'], $subscription[0]['subscription_id'], $subscription[0]['id']);
                }
                $subscription_information['tax_value'] = isset($price[0]['tax_value']) ? $price[0]['tax_value'] : "";
                $subscription_information['price_with_tax']  = isset($price[0]['price_with_tax']) ? $price[0]['price_with_tax'] : "";
                $subscription_information['original_price_with_tax'] = isset($price[0]['original_price_with_tax']) ? $price[0]['original_price_with_tax'] : "";
                $subscription_information['tax_percentage'] = isset($price[0]['tax_percentage']) ? $price[0]['tax_percentage'] : "";




                $data1['subscription_information'] = json_decode(json_encode($subscription_information), true);
                unset($data1['user']['city']);
                unset($data1['user']['latitude']);
                unset($data1['user']['longitude']);
                $data1['provder_information'] = json_decode(json_encode($partnerData), true);
                unset($data1['provder_information']['tax_name']);
                unset($data1['provder_information']['tax_number']);
                unset($data1['provder_information']['account_number']);
                unset($data1['provder_information']['account_name']);
                unset($data1['provder_information']['bank_code']);
                unset($data1['provder_information']['swift_code']);
                unset($data1['provder_information']['bank_name']);
                unset($data1['provder_information']['address']);
                $data1['bank_information'] = json_decode(json_encode($bank_information), true);
                if ($request->getPost('days')) {
                    $data1['working_days'] = json_decode($request->getPost('days'), true);
                } else {
                    $partner_timing_details = fetch_details('partner_timings', ['partner_id' => $partner_id]);
                    foreach ($partner_timing_details as $k => $val) {
                        $partner_timing_details[$k]['isOpen'] = $partner_timing_details[$k]['is_open'];
                        unset($partner_timing_details[$k]['is_open']);
                        $partner_timing_details[$k]['start_time'] = $partner_timing_details[$k]['opening_time'];
                        unset($partner_timing_details[$k]['opening_time']);
                        $partner_timing_details[$k]['end_time'] = $partner_timing_details[$k]['closing_time'];
                        unset($partner_timing_details[$k]['closing_time']);
                        unset($partner_timing_details[$k]['id']);
                        unset($partner_timing_details[$k]['partner_id']);
                        unset($partner_timing_details[$k]['created_at']);
                        unset($partner_timing_details[$k]['updated_at']);
                    }
                    $data1['working_days'] = json_decode(json_encode($partner_timing_details), true);
                }
                // die;
                $response = [
                    'error' => false,
                    'token' => $token,
                    'message' => 'User Registered successfully',
                    'data' => $data1,
                ];
                send_web_notification('New Provider',  $request->getPost('company_name') . ' Registered');
                $db      = \Config\Database::connect();
                $builder = $db->table('users u');
                $users = $builder->Select("u.id,u.fcm_id,u.username,u.email")
                    ->join('users_groups ug', 'ug.user_id=u.id')
                    ->where('ug.group_id', '1')
                    ->get()->getResultArray();
                $settings = get_settings('general_settings', true);
                $icon = $settings['logo'];
                $data = array(
                    'name' => $users[0]['username'],
                    'provider_name' => $company_name,
                    'provider_email' => $email,
                    'provoder_phone' => $mobile,
                    'title' => "New Provider Registartion",
                    'logo' => base_url("public/uploads/site/" . $icon),
                    'first_paragraph' => 'I would like to inform you that a new provider has just registered on our platform.Here are the details of their account:',
                    'second_paragraph' => 'Please take note of this information and ensure that their account is properly set up.',
                    'third_paragraph' => 'Thank you for your attention to this matter.',
                    'company_name' => $settings['company_title'],
                );
                email_sender($users[0]['email'], 'New Provider Registartion', view('backend/admin/pages/provider_email', $data));
                return $this->response->setJSON($response);
            } else {
                $msg = trim(preg_replace('/\r+/', '', preg_replace('/\n+/', '', preg_replace('/\t+/', ' ', strip_tags($ionAuth->errors())))));
                $response = [
                    'error' => true,
                    'message' => $msg,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        }
    }
    public function get_partner()
    {
        /*
            partner_id:163            // optional
            limit:10           // { default - 25 } optional
            offset:0            // { default - 0 } optional
            sort:               id / name
                                // { default -row_id } optional
            order:DESC/ASC      // { default - ASC } optional
            search:value        // { optional }
            status:awaiting     // { optional }
        */
        try {
            $validation =  \Config\Services::validation();
            if (!isset($_POST)) {
                $response = [
                    'error' => true,
                    'message' => "Please use Post request",
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $partner_model = new Partners_model();
            $partner_id = $this->user_details['id'];
            $status = !empty($this->request->getPost('status')) ? $this->request->getPost('status') : '';
            $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'DESC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $details = $partner_model->list(true, $search, $limit, $offset, $sort, $order, ['pd.partner_id' => $partner_id]);
            $total = $details['total'];
            unset($details['total']);
            if (!empty($details)) {
                $response = [
                    'error' => false,
                    'message' => 'Partner fetched successfully.',
                    'data' => $details
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => 'No data found',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function get_settings()
    {
        //     /* 
        //         variable:{variable Name}    {privacy_policy, general_settings} (optional)
        //     */
        // try {
        $validation =  \Config\Services::validation();
        $request = \Config\Services::request();
        $variable = (isset($_POST['variable']) && !empty($_POST['variable'])) ? $_POST['variable'] : 'all';
        $setting = array();
        $setting = fetch_details('settings', '', 'variable', '', '', '', 'ASC');
        if (isset($variable) && !empty($variable) && in_array(trim($variable), $this->allowed_settings)) {
            $setting_res[$variable] = get_settings($variable, true);
        } else {

            if (isset($this->user_details['id'])) {
                $setting_res['balance'] = fetch_details("users", ["id" => $this->user_details['id']], ['balance', 'payable_commision']);
                $setting_res['balance'] = (isset($setting_res['balance'][0]['balance'])) ? $setting_res['balance'][0]['balance'] : "0";
                $setting_res['demo_mode'] = (ALLOW_MODIFICATION == 0) ? "1" : "0";
                $setting_res['payable_commision'] = fetch_details("users", ["id" => $this->user_details['id']], ['balance', 'payable_commision']);
                $setting_res['payable_commision'] = (isset($setting_res['payable_commision'][0]['payable_commision'])) ? $setting_res['payable_commision'][0]['payable_commision'] : "0";
            }
            foreach ($setting as $type) {
                $notallowed_settings = ["languages", "email_settings", "country_codes", "api_key_settings", "test",];
                if (!in_array($type['variable'], $notallowed_settings)) {
                    $setting_res[$type['variable']] = get_settings($type['variable'], true);
                }
                $setting_res['general_settings']['at_store'] = isset($setting_res['general_settings']['at_store']) ? $setting_res['general_settings']['at_store'] : "1";
                $setting_res['general_settings']['at_doorstep'] = isset($setting_res['general_settings']['at_doorstep']) ? $setting_res['general_settings']['at_doorstep'] : "1";
            }
        }



        if (!empty($this->user_details['id'])) {
            $subscription = fetch_details('partner_subscriptions', ['partner_id' =>  $this->user_details['id']], [], 1, 0, 'id', 'DESC');
        }
        $subscription_information['subscription_id'] = isset($subscription[0]['subscription_id']) ? $subscription[0]['subscription_id'] : "";
        $subscription_information['isSubscriptionActive'] = isset($subscription[0]['status']) ? $subscription[0]['status'] : "deactive";
        $subscription_information['created_at'] = isset($subscription[0]['created_at']) ? $subscription[0]['created_at'] : "";
        $subscription_information['updated_at'] = isset($subscription[0]['updated_at']) ? $subscription[0]['updated_at'] : "";
        $subscription_information['is_payment'] = isset($subscription[0]['is_payment']) ? $subscription[0]['is_payment'] : "";
        $subscription_information['id'] = isset($subscription[0]['id']) ? $subscription[0]['id'] : "";
        $subscription_information['partner_id'] = isset($subscription[0]['partner_id']) ? $subscription[0]['partner_id'] : "";
        $subscription_information['purchase_date'] = isset($subscription[0]['purchase_date']) ? $subscription[0]['purchase_date'] : "";
        $subscription_information['expiry_date'] = isset($subscription[0]['expiry_date']) ? $subscription[0]['expiry_date'] : "";
        $subscription_information['name'] = isset($subscription[0]['name']) ? $subscription[0]['name'] : "";
        $subscription_information['description'] = isset($subscription[0]['description']) ? $subscription[0]['description'] : "";
        $subscription_information['duration'] = isset($subscription[0]['duration']) ? $subscription[0]['duration'] : "";
        $subscription_information['price'] = isset($subscription[0]['price']) ? $subscription[0]['price'] : "";
        $subscription_information['discount_price'] = isset($subscription[0]['discount_price']) ? $subscription[0]['discount_price'] : "";
        $subscription_information['order_type'] = isset($subscription[0]['order_type']) ? $subscription[0]['order_type'] : "";
        $subscription_information['max_order_limit'] = isset($subscription[0]['max_order_limit']) ? $subscription[0]['max_order_limit'] : "";
        $subscription_information['is_commision'] = isset($subscription[0]['is_commision']) ? $subscription[0]['is_commision'] : "";
        $subscription_information['commission_threshold'] = isset($subscription[0]['commission_threshold']) ? $subscription[0]['commission_threshold'] : "";
        $subscription_information['commission_percentage'] = isset($subscription[0]['commission_percentage']) ? $subscription[0]['commission_percentage'] : "";
        $subscription_information['publish'] = isset($subscription[0]['publish']) ? $subscription[0]['publish'] : "";
        $subscription_information['tax_id'] = isset($subscription[0]['tax_id']) ? $subscription[0]['tax_id'] : "";
        $subscription_information['tax_type'] = isset($subscription[0]['tax_type']) ? $subscription[0]['tax_type'] : "";

        if (!empty($subscription[0])) {

            $price = calculate_partner_subscription_price($subscription[0]['partner_id'], $subscription[0]['subscription_id'], $subscription[0]['id']);
        }
        $subscription_information['tax_value'] = isset($price[0]['tax_value']) ? $price[0]['tax_value'] : "";
        $subscription_information['price_with_tax']  = isset($price[0]['price_with_tax']) ? $price[0]['price_with_tax'] : "";
        $subscription_information['original_price_with_tax'] = isset($price[0]['original_price_with_tax']) ? $price[0]['original_price_with_tax'] : "";
        $subscription_information['tax_percentage'] = isset($price[0]['tax_percentage']) ? $price[0]['tax_percentage'] : "";


        $setting_res['subscription_information'] = json_decode(json_encode($subscription_information), true);

      

        if (array_key_exists('refund_policy', $setting_res)) {

      
            unset($setting_res['refund_policy']);
        }

        if (isset($setting_res) && !empty($setting_res)) {
            $response = [
                'error' => false,
                'message' => "setting recieved Successfully",
                'data' => $setting_res
            ];
        } else {
            $response = [
                'error' => true,
                'message' => "No data found in setting",
                'data' => $setting_res
            ];
        }

        return $this->response->setJSON($response);
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    public function get_categories()
    {
        try {
            $categories = new Category_model();
            $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
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
            $data = $categories->list(true, $search, $limit, $offset, $sort, $order, $where);
            if (!empty($data['data'])) {
                return response('Categories fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
            } else {
                return response('categories not found', false);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function get_sub_categories()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'category_id' => 'required',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $categories = new Category_model();
            $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
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
            if ($this->request->getPost('category_id')) {
                $where['parent_id'] = $this->request->getPost('category_id');
            }
            if (!exists(['parent_id' => $this->request->getPost('category_id')], 'categories')) {
                return response('no sub categories found');
            }
            $data = $categories->list(true, $search, $limit, $offset, $sort, $order, $where);
            if (!empty($data['data'])) {
                return response('Sub Categories fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
            } else {
                return response('Sub categories not found', false);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function update_fcm()
    {
        try {
            $validation =  \Config\Services::validation();
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
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $fcm_id = $this->request->getPost('fcm_id');
            $platform = $this->request->getPost('platform');

            if (update_details(['fcm_id' => $fcm_id, 'platform' => $platform], ['id' => $this->user_details['id']], 'users')) {
                return response('fcm id updated succesfully', false, ['fcm_id' => $fcm_id]);
            } else {
                return response();
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function get_taxes()
    {
        try {
            $taxes = new Tax_model();
            $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $where = [];
            if ($this->request->getPost('id')) {
                $where['id'] = $this->request->getPost('id');
            }
            $data = $taxes->list(true, $search, $limit, $offset, $sort, $order, $where);
            if (!empty($data['data'])) {
                return response('Taxes fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
            } else {
                return response('Taxes not found', false);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function get_services()
    {
        // try {
        $db      = \Config\Database::connect();
        $Service_model = new Service_model();
        $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
        $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
        $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
        $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
        $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
        $category_ids = $this->request->getPost('category_ids');
        $min_budget = $this->request->getPost('min_budget');
        $max_budget = $this->request->getPost('max_budget');
        $rating = $this->request->getPost('rating');
        // $where = [];
        $where_in = [];
        // $where['user_id'] = $this->user_details['id'];
        $additional_data = [];
        if (isset($category_ids) && !empty($category_ids)) {
            $where_in = explode(",", $category_ids);
        }
        $settings = get_settings('general_settings', true);
        if (($this->request->getPost('latitude') && !empty($this->request->getPost('latitude')) && ($this->request->getPost('longitude') && !empty($this->request->getPost('longitude'))))) {
            $additional_data = [
                'latitude' => $this->request->getPost('latitude'),
                'longitude' => $this->request->getPost('longitude'),
                'city_id' => $this->user_details['city_id'],
                'max_serviceable_distance' => $settings['max_serviceable_distance'],
            ];
        }
        $where = 's.user_id = ' . $this->user_details['id'] . ' ';
        if (isset($rating) && !empty($rating)) {
            $where .= '  AND s.rating >= \'' . $rating . '\'';
        }
        if (isset($min_budget) && !empty($min_budget) && isset($max_budget) && !empty($max_budget)) {
            if (isset($where)) {
                $where .= '  AND (`s`.`price` BETWEEN "' . $min_budget . '" AND "' . $max_budget . '" OR `s`.`discounted_price` BETWEEN "' . $min_budget . '" AND "' . $max_budget . '")';
            } else {
                $where = ' AND (`s`.`price` BETWEEN "' . $min_budget . '" AND "' . $max_budget . '" OR `s`.`discounted_price` BETWEEN "' . $min_budget . '" AND "' . $max_budget . '")';
            }
        } elseif (isset($min_budget) && !empty($min_budget)) {
            if (isset($where)) {
                $where .= ' AND (`s`.`price` >= "' . $min_budget . '" OR `s`.`discounted_price` >= "' . $min_budget . '")';
            } else {
                $where = '  AND (`s`.`price` >= "' . $min_budget . '" OR `s`.`discounted_price` >= "' . $min_budget . '")';
            }
        } elseif (isset($max_budget) && !empty($max_budget)) {
            if (isset($where)) {
                $where .= ' AND (`s`.`price` <= "' . $max_budget . '" OR `s`.`discounted_price` <= "' . $max_budget . '")';
            } else {
                $where = ' AND (`s`.`price` <= "' . $max_budget . '" OR `s`.`discounted_price` <= "' . $max_budget . '")';
            }
        }


        $at_store = 0;
        $at_doorstep = 0;
        $partner_details = fetch_details('partner_details', ['partner_id' =>  $this->user_details['id']]);
        if (isset($partner_details[0]['at_store']) && $partner_details[0]['at_store'] == 1) {
            $at_store = 1;
        }
        if (isset($partner_details[0]['at_doorstep']) && $partner_details[0]['at_doorstep'] == 1) {
            $at_doorstep = 1;
        }


        $data = $Service_model->list(true, $search, $limit, $offset, $sort, $order, $where, $additional_data, 'category_id', $where_in, $this->user_details['id'], '', '');
        if (isset($data['error'])) {
            return response($data['message']);
        }
        if (!empty($data['data'])) {
            return response(
                'services fetched successfully',
                false,
                $data['data'],
                200,
                [
                    'total' => $data['new_total'],
                    'min_price' => $data['new_min_price'],
                    'max_price' => $data['new_max_price'],
                    'min_discount_price' => $data['new_min_discount_price'],
                    'max_discount_price' => $data['new_max_discount_price'],
                ]
            );
        } else {
            return response('services not found', false);
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    public function delete_orders()
    {
        try {
            // order_id = required
            $validation =  \Config\Services::validation();
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
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $order_id = $this->request->getPost('order_id');
            $partner_id = $this->user_details['id'];
            $orders = fetch_details('orders', ['id' => $order_id, 'partner_id' => $partner_id]);
            if (empty($orders)) {
                $response = [
                    'error' => true,
                    'message' => 'No, Order Found',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $db      = \Config\Database::connect();
            $builder = $db->table('orders')->delete(['id' => $order_id, 'partner_id' => $partner_id]);
            if ($builder) {
                $builder = $db->table('order_services')->delete(['order_id' => $order_id]);
                if ($builder) {
                    $response = [
                        'error' => false,
                        'message' => 'Order deleted successfully!',
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'Order does not exist!',
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Order Not Found',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function get_promocodes()
    {
        try {
            // partner_id=50  = option param
            $model = new Promo_code_model();
            $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'DESC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $where = [];
            if ($this->user_details['id'] != '') {
                $where['partner_id'] = $this->user_details['id'];
            }
            $data = $model->list(true, $search, $limit, $offset, $sort, $order, $where);
            if (!empty($data['data'])) {
                return response('Promocode fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
            } else {
                return response('Promocode not found', false);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // public function manage_promocode()
    // {
    //     try {
    //         /* Post paramrter
    //     promo_code:WELCOME
    //     start_date: 2022/05/12 
    //     end_date: 2022/05/12
    //     minimum_order_amount:200    
    //     discount:10        
    //     discount_type: percentage /amount      
    //     max_discount_amount: 100          
    //     minimum_order_amount:500   
    //     repeat_usage  : 1/ 0  { optional }   
    //     no_of_repeat_usage  : 0  { optional }   
    //     status  : 1 
    //     no_of_users : 5
    //     image : url {optional}
    //     message : required
    //     */
    //         $db      = \Config\Database::connect();
    //         $this->validation =  \Config\Services::validation();
    //         $this->validation->setRules([
    //             'promo_code' => 'required',
    //             // 'partner_id' => 'required',
    //             'start_date' => 'required',
    //             'end_date' => 'required',
    //             'minimum_order_amount' => 'required|numeric',
    //             'discount' => 'required|numeric',
    //             'discount_type' => 'required',
    //             'max_discount_amount' => 'required|numeric',
    //             'status' => 'required',
    //             'message' => 'required',
    //         ]);
    //         $partner_id = $this->user_details['id'];
    //         $path = './public/uploads/promocodes/';
    //         if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
    //             $where['id'] = $_POST['promo_id'];
    //             $old_image = fetch_details('promo_codes', $where, 'image');
    //         }
    //         $image = "";
    //         if (!empty($_FILES['image']) && isset($_FILES['image'])) {
    //             $file =  $this->request->getFile('image');
    //             if ($file->isValid()) {
    //                 if ($file->move($path)) {
    //                     if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
    //                         if (file_exists($old_image) && !empty($old_image)) {
    //                             // unlink($old_image);
    //                         }
    //                     }
    //                     $image = 'public/uploads/promocodes/' . $file->getName();
    //                 }
    //             } else {
    //                 $image = $old_image;
    //             }
    //         }
    //         if (!$this->validation->withRequest($this->request)->run()) {
    //             $errors = $this->validation->getErrors();
    //             $response = [
    //                 'error' => true,
    //                 'message' => $errors,
    //                 'data' => []
    //             ];
    //             return $this->response->setJSON($response);
    //         } else {
    //             $promocode_model = new Promo_code_model();
    //             $status = ($this->request->getPost('status') && !empty($this->request->getPost('status'))) ? $this->request->getPost('status') : 1;
    //             $users = ($this->request->getPost('no_of_users') && !empty($this->request->getPost('no_of_users'))) ? $this->request->getPost('no_of_users') : 1;
    //             $repeat_usage = ($this->request->getPost('repeat_usage') && !empty($this->request->getPost('repeat_usage'))) ? $this->request->getPost('repeat_usage') : 0;
    //             $no_of_repeat_usage = ($this->request->getPost('no_of_repeat_usage') && !empty($this->request->getPost('no_of_repeat_usage'))) ? $this->request->getPost('no_of_repeat_usage') : 0;
    //             if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
    //                 $promo_id = $_POST['promo_id'];
    //             } else {
    //                 $promo_id = '';
    //             }
    //             $promocode = array(
    //                 'id' => $promo_id,
    //                 'partner_id' => $partner_id,
    //                 'promo_code' => $this->request->getVar('promo_code'),
    //                 'message' => $this->request->getVar('message'),
    //                 'start_date' => $this->request->getVar('start_date'),
    //                 'end_date' => $this->request->getVar('end_date'),
    //                 'no_of_users' => $users,
    //                 'minimum_order_amount' => $this->request->getVar('minimum_order_amount'),
    //                 'max_discount_amount' => $this->request->getVar('max_discount_amount'),
    //                 'discount' => $this->request->getVar('discount'),
    //                 'discount_type' => $this->request->getVar('discount_type'),
    //                 'repeat_usage' => $repeat_usage,
    //                 'no_of_repeat_usage' => $no_of_repeat_usage,
    //                 'image' => $image,
    //                 'status' => $status,
    //             );
    //             $promocode_model->save($promocode);
    //             if ($id = $db->insertID()) {
    //                 $data = fetch_details('promo_codes', ['id' => $id], ['promo_code', 'start_date', 'end_date', 'minimum_order_amount', 'discount', 'discount_type', 'max_discount_amount', 'repeat_usage', 'no_of_repeat_usage', 'no_of_users', 'message', 'status', 'image']);
    //                 $response = [
    //                     'error' => false,
    //                     'message' => 'Promocode saved successfully',
    //                     'data' => $data
    //                 ];
    //             } else {
    //                 $data = fetch_details('promo_codes', ['id' => $promo_id], ['promo_code', 'start_date', 'end_date', 'minimum_order_amount', 'discount', 'discount_type', 'max_discount_amount', 'repeat_usage', 'no_of_repeat_usage', 'no_of_users', 'message', 'status', 'image']);
    //                 $response = [
    //                     'error' => false,
    //                     'message' => 'Promocode updated successfully',
    //                     'data' => $data
    //                 ];
    //             }
    //             return $this->response->setJSON($response);
    //         }
    //     } catch (\Exception $th) {
    //         $response['error'] = true;
    //         $response['message'] = 'Something went wrong';
    //         return $this->response->setJSON($response);
    //     }
    // }
    public function manage_promocode()
    {
        // try {
        /* Post paramrter
        promo_code:WELCOME
        start_date: 2022/05/12 
        end_date: 2022/05/12
        minimum_order_amount:200    
        discount:10        
        discount_type: percentage /amount      
        max_discount_amount: 100          
        minimum_order_amount:500   
        repeat_usage  : 1/ 0  { optional }   
        no_of_repeat_usage  : 0  { optional }   
        status  : 1 
        no_of_users : 5
        image : url {optional}
        message : required
        */
        $db      = \Config\Database::connect();
        $this->validation =  \Config\Services::validation();
        $this->validation->setRules([
            'promo_code' => 'required',
            // 'partner_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'minimum_order_amount' => 'required|numeric',
            'discount' => 'required|numeric',
            'discount_type' => 'required',
            'max_discount_amount' => 'required|numeric',
            'status' => 'required',
            'message' => 'required',
        ]);
        $partner_id = $this->user_details['id'];
        $path = './public/uploads/promocodes/';
        if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
            $where['id'] = $_POST['promo_id'];
            $old_image = fetch_details('promo_codes', $where, 'image');
        }
        $image = "";
        if (!empty($_FILES['image']) && isset($_FILES['image'])) {
            $file =  $this->request->getFile('image');
            if ($file->isValid()) {
                if ($file->move($path)) {
                    if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
                        if (file_exists($old_image[0]['image']) && !empty($old_image[0]['image'])) {
                            // unlink($old_image);
                        }
                    }
                    $image = 'public/uploads/promocodes/' . $file->getName();
                }
            } else {
                $image = $old_image[0]['image'];
            }
        } else {
            $image = $old_image[0]['image'];
        }
        if (!$this->validation->withRequest($this->request)->run()) {
            $errors = $this->validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => []
            ];
            return $this->response->setJSON($response);
        } else {
            $promocode_model = new Promo_code_model();
            $status = ($this->request->getPost('status') && !empty($this->request->getPost('status'))) ? $this->request->getPost('status') : 1;
            $users = ($this->request->getPost('no_of_users') && !empty($this->request->getPost('no_of_users'))) ? $this->request->getPost('no_of_users') : 1;
            $repeat_usage = ($this->request->getPost('repeat_usage') && !empty($this->request->getPost('repeat_usage'))) ? $this->request->getPost('repeat_usage') : 0;
            $no_of_repeat_usage = ($this->request->getPost('no_of_repeat_usage') && !empty($this->request->getPost('no_of_repeat_usage'))) ? $this->request->getPost('no_of_repeat_usage') : 0;
            if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
                $promo_id = $_POST['promo_id'];
            } else {
                $promo_id = '';
            }
            $promocode = array(
                'id' => $promo_id,
                'partner_id' => $partner_id,
                'promo_code' => $this->request->getVar('promo_code'),
                'message' => $this->request->getVar('message'),
                'start_date' => $this->request->getVar('start_date'),
                'end_date' => $this->request->getVar('end_date'),
                'no_of_users' => $users,
                'minimum_order_amount' => $this->request->getVar('minimum_order_amount'),
                'max_discount_amount' => $this->request->getVar('max_discount_amount'),
                'discount' => $this->request->getVar('discount'),
                'discount_type' => $this->request->getVar('discount_type'),
                'repeat_usage' => $repeat_usage,
                'no_of_repeat_usage' => $no_of_repeat_usage,
                'image' => $image,
                'status' => $status,
            );
            $promocode_model->save($promocode);
            if ($id = $db->insertID()) {
                $data = fetch_details('promo_codes', ['id' => $id], ['promo_code', 'start_date', 'end_date', 'minimum_order_amount', 'discount', 'discount_type', 'max_discount_amount', 'repeat_usage', 'no_of_repeat_usage', 'no_of_users', 'message', 'status', 'image']);
                $response = [
                    'error' => false,
                    'message' => 'Promocode saved successfully',
                    'data' => $data
                ];
            } else {
                $data = fetch_details('promo_codes', ['id' => $promo_id], ['promo_code', 'start_date', 'end_date', 'minimum_order_amount', 'discount', 'discount_type', 'max_discount_amount', 'repeat_usage', 'no_of_repeat_usage', 'no_of_users', 'message', 'status', 'image']);
                $data[0]['image'] = base_url($data[0]['image']);
                $response = [
                    'error' => false,
                    'message' => 'Promocode updated successfully',
                    'data' => $data
                ];
            }
            return $this->response->setJSON($response);
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    public function delete_promocode()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'promo_id' => 'required|numeric',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $promo_id = $this->request->getPost('promo_id');
            $is_exist =  exists(['id' => $promo_id], 'promo_codes');
            if (!$is_exist) {
                $response = [
                    'error' => true,
                    'message' => 'Promo code does not exist!',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $db      = \Config\Database::connect();
            $builder = $db->table('promo_codes')->delete(['id' => $promo_id]);
            if ($builder) {
                $response = [
                    'error' => false,
                    'message' => 'Promocode deleted successfully!',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Promocode does not exist!',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function send_withdrawal_request()
    {
        try {
            /* Post paramrter
        user_id:50
        payment_address: acc no,banak  
        amount: 500
        user_type : partner / customer,
        remarks : text { optional}
        status  : 0/1/2 default 0
        */
            $this->validation =  \Config\Services::validation();
            $this->validation->setRules([
                'payment_address' => 'required',
                'amount' => 'required|numeric',
                'user_type' => 'required',
            ]);
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors = $this->validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                $model = new Payment_request_model();
                if (isset($_POST['id']) && !empty($_POST['id'])) {
                    $request_id = $_POST['id'];
                } else {
                    $request_id = '';
                }
                // print_r($this->user_details);
                // die;
                $user_id = ($this->request->getVar('user_id') != '') ? $this->request->getVar('user_id') : $this->user_details['id'];
                $amount = $this->request->getVar('amount');
                $payment_request = array(
                    'id' => $request_id,
                    'user_id' => $user_id,
                    'user_type' => $this->request->getVar('user_type'),
                    'payment_address' => $this->request->getVar('payment_address'),
                    'amount' => $amount,
                    'remarks' => $this->request->getVar('remarks'),
                    'status' => 0,
                );
                // if ($model->save($payment_request)) {
                $current_balance =  fetch_details('users', ['id' => $user_id], ['balance', 'username']);
                if ($current_balance[0]['balance'] > $amount) {
                    $model->save($payment_request);
                    update_balance($this->request->getVar('amount'), $user_id, 'deduct');
                    $balance = fetch_details("users", ["id" => $this->user_details['id']], ['balance']);
                    $response = [
                        'error' => false,
                        'message' => 'payment request sent!',
                        'balance' => $balance[0]['balance'],
                        'data' => []
                    ];
                    send_web_notification('Withdraw Request',  $current_balance[0]['username'] . ' Withdraw request for ' . $amount, null, 'https://edemand-test.thewrteam.in/admin/partners/payment_request');
                    $db      = \Config\Database::connect();
                    $builder = $db->table('users u');
                    $users = $builder->Select("u.id,u.fcm_id,u.username,u.email")
                        ->join('users_groups ug', 'ug.user_id=u.id')
                        ->where('ug.group_id', '1')
                        ->get()->getResultArray();
                    $settings = get_settings('general_settings', true);
                    $icon = $settings['logo'];
                    $data = array(
                        'name' => $users[0]['username'],
                        'title' => "Withdraw Request",
                        'logo' => base_url("public/uploads/site/" . $icon),
                        'first_paragraph' => 'I hope this email finds you well. I am writing to request a withdrawal from my account.',
                        'second_paragraph' => 'Please kindly process my request as soon as possible. If you require any further information, please do not hesitate to contact me. ',
                        'third_paragraph' => 'Thank you for your attention to this matter.',
                        'company_name' => $settings['company_title'],
                    );
                    email_sender($users[0]['email'], 'Withdraw Request', view('backend/admin/pages/provider_email', $data));
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'Insufficient Balance!',
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
                // } else {
                //     $response = [
                //         'error' => true,
                //         'message' => 'payment request failed!',
                //         'data' => []
                //     ];
                //     return $this->response->setJSON($response);
                // }
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function get_withdrawal_request()
    {
        try {
            // user_id = 50  = option param
            $model = new Payment_request_model();
            $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'p.id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'DESC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $where = [];
            if ($this->user_details['id'] !== '') {
                $where['user_id'] = $this->user_details['id'];
            }
            $data = $model->list(true, $search, $limit, $offset, $sort, $order, $where);
            if (!empty($data['data'])) {
                return response('Payment Request fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
            } else {
                return response('Payment Request not found', false);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function delete_withdrawal_request()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'id' => 'required|numeric',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $id = $this->request->getPost('id');
            $is_exist = fetch_details('payment_request', ['id' => $id, 'user_id' => $this->user_details['id']]);
            if (!empty($is_exist)) {
                $db      = \Config\Database::connect();
                $builder = $db->table('payment_request')->delete(['id' => $id]);
                $response = [
                    'error' => false,
                    'message' => 'Payment request deleted successfully!',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Payment request does not exist!',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function manage_service()
    {




        // try {

        $tax = get_settings('system_tax_settings', true);
        $this->validation =  \Config\Services::validation();
        $this->validation->setRules(
            [
                'title' => 'required',
                'description' => 'required',
                'price' => 'required|numeric|greater_than[0]',
                'duration' => 'required|numeric',
                'max_qty' => 'required|numeric|greater_than[0]',
                'tags' => 'required',
                'members' => 'required|numeric|greater_than_equal_to[1]',
                'categories' => 'required',
                'discounted_price' => "permit_empty|numeric",
                'is_cancelable' => 'numeric',
                'at_store' => 'required',
                'at_doorstep' => 'required',
            ],
        );
        if (!$this->validation->withRequest($this->request)->run()) {
            $errors = $this->validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => []
            ];
            return $this->response->setJSON($response);
        } else {


            if (isset($_POST['tags']) && !empty($_POST['tags'])) {
                $convertedTags =  implode(', ', $_POST['tags']);
            } else {
                $response = [
                    'error' => true,
                    'message' => "Tags required!",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        }





        $title = $this->removeScript($this->request->getPost('title'));
        $description = $this->removeScript($this->request->getPost('description'));
        $path = "./public/uploads/services/";
        if (isset($_POST['service_id']) && !empty($_POST['service_id'])) {
            $service_id = $_POST['service_id'];
            $old_icon = fetch_details('services', ['id' => $service_id], ['image'])[0]['image'];
            $old_files = fetch_details('services', ['id' => $service_id], ['files'])[0]['files'];
            $old_other_images = fetch_details('services', ['id' => $service_id], ['other_images'])[0]['other_images'];
        } else {
            $service_id = "";
            $old_icon = "";
            $old_files = "";
            $old_other_images = "";
            $old_files = "";
        }
        $image_name = "";
        if (!empty($_FILES['image']) && isset($_FILES['image'])) {
            $file =  $this->request->getFile('image');
            if ($file->isValid()) {
                if ($file->move($path)) {
                    if (file_exists($old_icon) && !empty($old_icon)) {
                        unlink($old_icon);
                    }
                    $image_name = 'public/uploads/services/' . $file->getName();
                }
            } else {
                $image_name = $old_icon;
            }
        } else {
            $image_name = $old_icon;
        }
        if (isset($_POST['sub_category']) && !empty($_POST['sub_category'])) {
            $category_id = $_POST['sub_category'];
        } else {
            $category_id = $_POST['categories'];
        }
        $discounted_price = $this->request->getPost('discounted_price');
        $price = $this->request->getPost('price');
        if ($discounted_price > $price) {
            $response = [
                'error' => true,
                'message' => "discounted price can not be higher than the price",
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash(),
                'data' => []
            ];
            return $this->response->setJSON($response);
        }
        if ($discounted_price == $price) {
            $response = [
                'error' => true,
                'message' => "discounted price can not equal to the price",
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash(),
                'data' => []
            ];
            return $this->response->setJSON($response);
        }
        $user_id = $this->user_details['id'];
        $tax_data = fetch_details('taxes', ['id' => $this->request->getVar('tax_id')], ['id', 'title', 'percentage']);
        //start
        $uploaded_images = $this->request->getFiles('files');
        $image_names['name'] = [];
        $data['images'] = [];
        $path = "public/uploads/services/";
        if (isset($uploaded_images['files'])) {
            foreach ($uploaded_images['files'] as $images) {
                $validate_image = valid_image($images);
                if ($validate_image == true) {
                    return response("Invalid Image", true, []);
                }

                // Replace symbols with "-"
                $newName = $images->getName();
                $newName = str_replace([' ', '_', '@', '#', '$', '%'], '-', $newName);
                // $newName = $images->getRandomName();
                if ($newName != null) {
                    move_file($images, $path, $newName);
                    if (!empty($old_files)) {
                        $old_files = ($old_files);
                        $old_files_images_array = json_decode($old_files, true); // Decode JSON string to associative array
                        foreach ($old_files_images_array as $old) {
                            if (file_exists(FCPATH . $old)) {
                                unlink(FCPATH . $old);
                            }
                        }
                    }
                    $name = "public/uploads/services/$newName";
                    array_push($image_names['name'], $name);
                    //
                }
            }
            $files_names = json_encode($image_names['name']);
        } else {
            $files_names = $old_files;
        }

        $uploaded_other_images = $this->request->getFiles('other_images');
        $other_image_names['name'] = [];
        $data['images'] = [];
        $path = "public/uploads/services/";
        if (isset($uploaded_other_images['other_images'])) {
            foreach ($uploaded_other_images['other_images'] as $images) {
                $validate_image = valid_image($images);
                if ($validate_image == true) {
                    return response("Invalid Image", true, []);
                }
                $newName = $images->getRandomName();
                if ($newName != null) {
                    move_file($images, $path, $newName);
                    if (!empty($old_other_images)) {
                        $old_other_images_array = json_decode($old_other_images, true); // Decode JSON string to associative array
                        foreach ($old_other_images_array as $old) {
                            if (file_exists(FCPATH . $old)) {
                                unlink(FCPATH . $old);
                            }
                        }
                    }
                    $name = "public/uploads/services/$newName";
                    array_push($other_image_names['name'], $name);
                }
            }
            $other_images = json_encode($other_image_names['name']);
        } else {
            $other_images = ($old_other_images);
        }


        $faqs = $this->request->getVar('faqs');



        if (isset($faqs)) {

            $array = json_decode(json_encode($faqs), true);

            $convertedArray = array_map(function ($item) {
                return [$item['question'], $item['answer']];
            }, $array);
        }


        //end
        $service = [
            'id' => $service_id,
            'user_id' => $user_id,
            'category_id' => $category_id,
            'tax_type' => ($this->request->getPost('tax_type') != '') ? $this->request->getPost('tax_type') : 'GST',
            'tax_id' => ($this->request->getVar('tax_id') != '') ? $this->request->getVar('tax_id') : '0',
            // 'tax' => ($this->request->getPost('tax') != '') ? $this->request->getPost('tax') : '0',
            'title' => $title,
            'description' => $description,
            'slug' => '',
            'tags' => $convertedTags,
            'price' => $price,
            'discounted_price' => ($discounted_price != '') ? $discounted_price : '00',
            'image' => $image_name,
            'number_of_members_required' => $this->request->getVar('members'),
            'duration' => $this->request->getVar('duration'),

            'rating' => 0,
            'number_of_ratings' => 0,
            'on_site_allowed' => ($this->request->getPost('on_site') == "on") ? 1 : 0,
            'is_pay_later_allowed' => ($this->request->getPost('is_pay_later_allowed') == 1) ? 1 : 0,
            'is_cancelable' => ($this->request->getPost('is_cancelable') == 1) ? 1 : 0,
            'cancelable_till' => ($this->request->getVar('cancelable_till') != "") ? $this->request->getVar('cancelable_till') : '00',
            'max_quantity_allowed' => $this->request->getPost('max_qty'),
            'long_description' => ($this->request->getVar('long_description')) ? ($this->request->getVar('long_description'))  : "",
            'files' => isset($files_names) ? $files_names : "",
            'other_images' => isset($other_images) ? $other_images : "",
            'faqs' => isset($convertedArray) ? json_encode($convertedArray) : "",
            'at_doorstep' => ($this->request->getPost('at_doorstep') == 1) ? 1 : 0,
            'at_store' => ($this->request->getPost('at_store') == 1) ? 1 : 0,
            'status' => ($this->request->getPost('status') == 1) ? 1 : 0,

        ];
        // print_r($service);
        $service_model = new Service_model;
        $db      = \Config\Database::connect();
        if ($service_model->save($service)) {
            if ($id = $db->insertID()) {
                $data = fetch_details('services', ['id' => $id], ['id', 'title', 'tags', 'description', 'price', 'duration', 'max_quantity_allowed', 'number_of_members_required', 'category_id', 'cancelable_till', 'is_pay_later_allowed', 'is_cancelable', 'discounted_price', 'tax_type', 'image']);
                $new_service_id = $id;
                $data[0]['image'] = base_url($data[0]['image']);
                $response = [
                    'error' => false,
                    'message' => "Service saved successfully!",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => $data
                ];
            } else {
                $new_service_id = $service_id;
                $data = fetch_details('services', ['id' => $service_id], ['id', 'title', 'tags', 'description', 'price', 'duration', 'max_quantity_allowed', 'number_of_members_required', 'category_id', 'cancelable_till', 'is_pay_later_allowed', 'is_cancelable', 'discounted_price', 'tax_type', 'image']);
                $data[0]['image'] = base_url($data[0]['image']);
                $response = [
                    'error' => false,
                    'message' => "Service updated successfully!",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => $data
                ];
            }



            $response = [
                'error' => false,
                'message' => "Service saved successfully!",
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash(),
                'data' => $data
            ];
            return $this->response->setJSON($response);
        } else {
            // if (!empty($data['data'])) {
            //     return response('services fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
            // } 
            $response = [
                'error' => true,
                'message' => "Service can not be Saved!",
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash(),
                'data' => []
            ];
            return $this->response->setJSON($response);
        }
    }
    // } catch (\Exception $th) {
    //     $response['error'] = true;
    //     $response['message'] = 'Something went wrong';
    //     return $this->response->setJSON($response);
    // }

    public function delete_service()
    {
        try {
            // service_id = required
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'service_id' => 'required|numeric',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $service_id = $this->request->getPost('service_id');
            $exist_service = fetch_details('services', ['id' => $service_id, 'user_id' => $this->user_details['id']], ['id']);
            if (!empty($exist_service)) {
                $db      = \Config\Database::connect();
                $builder = $db->table('services')->delete(['id' => $service_id, 'user_id' => $this->user_details['id']]);
                $builder2 = $this->db->table('cart')->delete(['service_id' => $service_id]);
                $builder3 = $this->db->table('services_ratings')->delete(['service_id' => $service_id]);
                if ($builder) {
                    $response = [
                        'error' => false,
                        'message' => 'Service deleted successfully!',
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'Service does not exist!',
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Service does not exist!',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function get_transactions()
    {
        try {
            /*
            partner_id = required
            limit:10            {optional}
            offset:0            {optional}
            sort:id             {optional}
            order:asc           {optional}
        */
            $transaction_model = new Transaction_model;
            $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $where = [];
            if ($this->user_details['id'] != '') {
                $where['partner_id'] = $this->user_details['id'];
            }
            $data = $transaction_model->list_transactions(true, $search, $limit, $offset, $sort, $order, $where);
            return response('Transactions received successfully.', false, $data, 200);
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function update_service_status()
    {
        try {
            $validation =  \Config\Services::validation();
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
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $service_id = $this->request->getPost('service_id');
            $status = $this->request->getPost('status');
            $exist_service = fetch_details('services', ['id' => $service_id, 'user_id' => $this->user_details['id']], ['id']);
            if (!empty($exist_service)) {
                $res = update_details(['status' => $status], ['id' => $service_id, 'user_id' => $this->user_details['id']], 'services');
                if ($res) {
                    $response = [
                        'error' => false,
                        'message' => 'Service status updated successfully!',
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'Service status cant be changed!',
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Service status cant be changed!',
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function update_order_status()
    {
        // try {
        /*
            order_id:42
            status:rescheduled
            date:2022-11-28 {optional} only enter when update status to rescheduled
            time:11:00:00 {optional} only enter when update status to rescheduled
            customer_id:1
        */
        $validation =  \Config\Services::validation();
        $validation->setRules(
            [
                'order_id' => 'required|numeric',
                'customer_id' => 'required|numeric',
                'status' => 'required',
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => []
            ];
            return $this->response->setJSON($response);
        }
        $order_id = $this->request->getPost('order_id');
        $status = $this->request->getPost('status');
        $customer_id = $this->request->getPost('customer_id');
        $date = $this->request->getPost('date');
        $selected_time = $this->request->getPost('time');
        $otp = $this->request->getPost('otp');
        $work_complete_files = $this->request->getFiles('work_complete_files');
        $work_started_files = $this->request->getFiles('work_started_files');
        if ($status == "rescheduled") {
            $res =  validate_status($order_id, $status, $date, $selected_time);
        } else {
            if ($status == "completed") {
                $res = validate_status($order_id, $status, '', '', $otp, isset($work_complete_files) ? $work_complete_files : "");
                $work_completed_files_data = [];
                $order_data = fetch_details('orders', ['id' => $order_id]);
                if (!empty($order_data)) {
                    if (!empty($order_data[0]['work_completed_proof'])) {
                        $work_completed_files_data = array_map(function ($data) {
                            return base_url($data);
                        }, json_decode(($order_data[0]['work_completed_proof']), true));
                    }
                }
            } elseif ($status == "started") {
                $work_started_files_data = [];
                $res = validate_status($order_id, $status, '', '', '', isset($work_started_files) ? $work_started_files : "");
                $order_data = fetch_details('orders', ['id' => $order_id]);
                if (!empty($order_data)) {
                    if (!empty($order_data[0]['work_started_proof'])) {
                        $work_started_files_data = array_map(function ($data) {
                            return base_url($data);
                        }, json_decode(($order_data[0]['work_started_proof']), true));
                    }
                }
            } else {
                $res =  validate_status($order_id, $status);
            }
        }
        if ($res['error']) {
            $response['error'] = true;
            $response['message'] = $res['message'];
            $response['data'] = array();
            return $this->response->setJSON($response);
        }
        if ($status == "rescheduled") {
            $user_no = fetch_details('users', ['id' => $customer_id], 'phone')[0]['phone'];
            $response = [
                'error' => false,
                'message' => "Order rescheduled successfully!",
                'contact' => "You can call on '.$user_no.' number to reschedule",
            ];
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
            $response = [
                'error' => false,
                'message' => "Order is cancelled!",
            ];
            return $this->response->setJSON($response);
        }
        if ($status == "completed") {
            $response = [
                'error' => false,
                'message' => "Order Completed successfully!",
                'data' => $work_completed_files_data
            ];
            return $this->response->setJSON($response);
        }
        if ($status == "started") {
            $response = [
                'error' => false,
                'message' => "Order Started successfully!",
                'data' =>   $work_started_files_data,
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
        } elseif ($status == 'started') {
            $type = ['type' => "customer_order_started"];
        } elseif ($status == 'completed') {
            $type = ['type' => "customer_order_completed"];
        }
        $custom_notification = fetch_details('notifications', $type);
        $app_name = isset($settings['company_title']) && !empty($settings['company_title']) ? $settings['company_title'] : '';
        $user_res = fetch_details('users', ['id' => $customer_id], 'username,fcm_id,platform');
        $customer_msg = (!empty($custom_notification)) ? $custom_notification[0]['message'] :  'Hello Dear ' . $user_res[0]['username'] . ' order status updated to ' . $status . ' for your order ID #' . $order_id . ' please take note of it! Thank you for shopping with us. Regards ' . $app_name . '';
        $fcm_ids = array();
        if (!empty($user_res[0]['fcm_id'])) {
            $fcmMsg = array(
                'title' => (!empty($custom_notification)) ? $custom_notification[0]['title'] : "Order status updated",
                'body' => $customer_msg,
                'type' => "order"
            );
            $fcm_ids['fcm_id'] = $user_res[0]['fcm_id'];
            $fcm_ids['platform'] = $user_res['platform'];

            send_notification($fcmMsg, $fcm_ids);
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    public function get_service_ratings()
    {
        // try {
        $db      = \Config\Database::connect();
        $this->validation =  \Config\Services::validation();
        $errors = $this->validation->getErrors();
        $response = [
            'error' => true,
            'message' => $errors,
            'data' => []
        ];
        $partner_id = $this->user_details['id'];
        // print_r($partner_id);
        $limit = (isset($_POST['limit']) && !empty($_POST['limit'])) ? $_POST['limit'] : 10;
        $offset = (isset($_POST['offset']) && !empty($_POST['offset'])) ? $_POST['offset'] : 0;
        $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $_POST['sort'] : 'id';
        $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $_POST['order'] : 'ASC';
        $search = (isset($_POST['search']) && !empty($_POST['search'])) ? $_POST['search'] : '';
        $Service_id = ($this->request->getPost('service_id') != '') ? $this->request->getPost('service_id') : '';
        // $partner_id = ($this->request->getPost('partner_id') != '') ? $this->request->getPost('partner_id') : '';
        if (!empty($this->request->getPost('service_id'))) {
            $where = " sr.service_id={$Service_id}";
        } else {
            $where = "s.user_id={$partner_id} OR sr.service_id={$Service_id}";
        }
        $ratings = new Service_ratings_model();
        if ($partner_id != '') {
            $data = $ratings->ratings_list(true, $search, $limit, $offset, $sort, $order, $where);
        } else {
            $data = $ratings->ratings_list(true, $search, $limit, $offset, $sort, $order, $where);
        }


        $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $_POST['sort'] : 'id';
        usort($data['data'], function ($a, $b) use ($sort) {
            switch ($sort) {
                case 'rating':
                    if ($a['rating'] === $b['rating']) {
                        return strtotime($b['rated_on']) - strtotime($a['rated_on']);
                    }
                    return $b['rating'] - $a['rating'];
                case 'created_at':
                    return strtotime($b['rated_on']) - strtotime($a['rated_on']);
                default:
                    return $a['id'] - $b['id'];
            }
        });
        
        if(!empty($Service_id)){
            $rate_data = get_service_ratings($Service_id);

            $average_rating = $db->table('services s')
                ->select(' 
                            (SUM(sr.rating) / count(sr.rating)) as average_rating
                            ')
                ->join('services_ratings sr', 'sr.service_id = s.id')
                ->where('s.id', $Service_id)
                ->get()->getResultArray();
        }else{
            $rate_data = get_ratings($partner_id);
        
            $average_rating = $db->table('services s')
                ->select(' 
                    (SUM(sr.rating) / count(sr.rating)) as average_rating
                    ')
                ->join('services_ratings sr', 'sr.service_id = s.id')
                ->where('s.user_id', $partner_id)
                ->orderBy('average_rating', 'desc') // Sort by average_rating in descending order
                ->orderBy('sr.created_at', 'desc') // Sort by created_at in descending order
                ->orderBy('s.id', 'asc') // Sort by id in ascending order
                ->get()->getResultArray();
        }
        
      



        $ratingData = array();
        $rows = array();
        $tempRow = array();
        foreach ($average_rating as $row) {
            $tempRow['average_rating'] = (isset($row['average_rating']) && $row['average_rating'] != "") ? $row['average_rating'] : 0;
        }
        foreach ($rate_data as $row) {
            $tempRow['total_ratings'] = (isset($row['total_ratings']) && $row['total_ratings'] != "") ? $row['total_ratings'] : 0;
            $tempRow['rating_5'] = (isset($row['rating_5']) && $row['rating_5'] != "") ? $row['rating_5'] : 0;
            $tempRow['rating_4'] = (isset($row['rating_4']) && $row['rating_4'] != "") ? $row['rating_4'] : 0;
            $tempRow['rating_3'] = (isset($row['rating_3']) && $row['rating_3'] != "") ? $row['rating_3'] : 0;
            $tempRow['rating_2'] = (isset($row['rating_2']) && $row['rating_2'] != "") ? $row['rating_2'] : 0;
            $tempRow['rating_1'] = (isset($row['rating_1']) && $row['rating_1'] != "") ? $row['rating_1'] : 0;
            $rows[] = $tempRow;
            // print_r($row['total_ratings']);
            // (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand";
        }
        $ratingData = $rows;
        // print_r($rate_data);
        $response = [
            'error' => false,
            'message' => "Data Retrieved successfully!",
            'ratings' => $ratingData,
            'total' => $data['total']  , 
            'data' => remove_null_values($data['data']),
        ];
        return $this->response->setJSON($response);
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
        // return response('Data Retrieved successfully', false, remove_null_values($data['data']), 200, ['total' => $data['total']]);
    }
    public function get_notifications()
    {
        try {
            /*
            id:10                   {optional}
            limit:10                {optional}
            offset:0                {optional}
            sort:id                 {optional}
            order:asc               {optional}
            search:test             {optional}
        */
            $partner_id = $this->user_details['id'];
            $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : 10;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'DESC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            // $partner_id = $this->request->getPost('id');
            $where = $additional_data = [];
            if ($this->request->getPost('id') && !empty($this->request->getPost('id'))) {
                $where['id'] = $this->request->getPost('id');
            }
            $where['user_id'] = $partner_id;
            $notifications = new Notification_model();
            $get_notifications = $notifications->list(true, $search, $limit, $offset, $sort, $order, $where);
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
            if (!empty($id)) {
                return $get_notifications['data'];
            }
            if (!empty($get_notifications['data'])) {
                return response('Notifications fetched successfully', false, remove_null_values($get_notifications['data']), 200, ['total' => $get_notifications['total']]);
            } else {
                return response('Notification Not Found');
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    // available slots api
    public function get_available_slots()
    {
        try {
            //     /*   
            //         date : 2022-11-01
            //     */
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'date' => 'required|valid_date[Y-m-d]',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
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
                'Sun' => 'sunday'
            ];
            // $partner_id = $this->request->getPost('partner_id');
            $partner_id = $this->user_details['id'];
            $date = $this->request->getPost('date');
            $time = $this->request->getPost('date');
            $date = new DateTime($date);
            $date = $date->format('Y-m-d');
            $day =  date('D', strtotime($date));
            $whole_day = $days[$day];
            $partner_data = fetch_details('partner_details', ['partner_id' => $partner_id], ['advance_booking_days']);
            $time_slots = get_available_slots($partner_id, $date);
            // print_R($time_slots);
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
            $partner_timing = fetch_details('partner_timings', ['partner_id' => $partner_id, "day" => $whole_day]);
            if (!empty($partner_data) && $partner_data[0]['advance_booking_days'] > 0) {
                $allowed_advanced_booking_days = $partner_data[0]['advance_booking_days'];
                $current_date = new DateTime();
                $max_available_date =  $current_date->modify("+ $allowed_advanced_booking_days day")->format('Y-m-d');
                if ($date > $max_available_date) {
                    $response = [
                        'error' => true,
                        'message' => "You'can not choose date beyond available booking days which is + $allowed_advanced_booking_days days",
                        'data' => []
                    ];
                    return $this->response->setJSON(remove_null_values($response));
                }
            } else if (!empty($partner_data) && $partner_data[0]['advance_booking_days'] == 0) {
                $current_date = new DateTime();
                if ($date > $current_date->format('Y-m-d')) {
                    $response = [
                        'error' => true,
                        'message' => "Advanced Booking for this partner is not available",
                        'data' => []
                    ];
                    return $this->response->setJSON(remove_null_values($response));
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => "No Partner Found",
                    'data' => []
                ];
                return $this->response->setJSON(remove_null_values($response));
            }
            // if (empty($partner_timing)) {
            //     $response = [
            //         'error' => true,
            //         'message' => "Partner Is closed on this day",
            //         'data' => []
            //     ];
            //     return $this->response->setJSON(remove_null_values($response));
            // }
            // if ($partner_timing[0]['is_open'] == 0) {
            //     $response = [
            //         'error' => true,
            //         'message' => "Partner Is closed on this day",
            //         'data' => []
            //     ];
            //     return $this->response->setJSON(remove_null_values($response));
            // }
            // if ($time < $partner_timing[0]['opening_time']) {
            //     $response = [
            //         'error' => true,
            //         'message' => "Partner is not available at this time",
            //         'data' => []
            //     ];
            //     return $this->response->setJSON(remove_null_values($response));
            // }
            // if ($time >= $partner_timing[0]['closing_time']) {
            //     $response = [
            //         'error' => true,
            //         'message' => "please Choose different time , Partner will be closed at this time",
            //         'data' => []
            //     ];
            //     return $this->response->setJSON(remove_null_values($response));
            // }
            if (!empty($time_slots)) {
                $response = [
                    'error' => $time_slots['error'],
                    'message' => ($time_slots['error'] == false) ? 'Found Time slots' : 'No slot available for this date',
                    'data' => [
                        'all_slots' => (!empty($time_slots) && $time_slots['error'] == false) ? $time_slots['all_slots'] : [],
                        // 'available_slots' => (!empty($time_slots) && $time_slots['error'] == false) ? $time_slots['available_slots'] : [],
                        // 'busy_slots' => (!empty($time_slots) && $time_slots['error'] == false) ? $time_slots['busy_slots'] : []
                    ]
                ];
                return $this->response->setJSON(remove_null_values($response));
            } else {
                $response = [
                    'error' => true,
                    'message' => 'No slot is available on this date!',
                    'data' => []
                ];
                return $this->response->setJSON(remove_null_values($response));
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function delete_provider_account()
    {
        // try {
        $user_id = $this->user_details['id'];
        if (!exists(['id' => $user_id], 'users')) {
            return response('user does not exist please enter valid user ID!', true);
        }
        $user_data = fetch_details('users_groups', ['user_id' => $user_id]);
        if (!empty($user_data) && isset($user_data[0]['group_id']) && !empty($user_data[0]['group_id']) && $user_data[0]['group_id'] == 3) {
            $user = fetch_details('users', ['id' => $user_id]);
            $partner_data = fetch_details('partner_details', ['partner_id' => $user_id]);
            $path = "/public/uploads/users/partners/";
            $profile_image = $user[0]['image'];
            $banner_path = "/public/uploads/users/partners/banner_images/";
            $banner_image = $partner_data[0]['banner'];
            $passport_path = "/public/uploads/users/passport/";
            $passport_image = $partner_data[0]['passport'];
            $profile_image = (file_exists(FCPATH . $path . $profile_image)) ? base_url($path . $profile_image) : ((file_exists(FCPATH . $profile_image)) ? base_url($profile_image) : ((!file_exists(FCPATH . $path . $profile_image)) ? base_url("public/backend/assets/profiles/default.png") : base_url($path . $profile_image)));
            $banner_image = (file_exists(FCPATH . $banner_path . $banner_image)) ? base_url($banner_path . $banner_image) : ((file_exists(FCPATH . $banner_image)) ? base_url($banner_image) : ((!file_exists(FCPATH . $banner_path . $banner_image)) ? base_url("public/backend/assets/profiles/default.png") : base_url($banner_path . $banner_image)));
            $passport_image = (file_exists(FCPATH . $passport_path . $passport_image)) ? base_url($passport_path . $passport_image) : ((file_exists(FCPATH . $passport_image)) ? base_url($passport_image) : ((!file_exists(FCPATH . $passport_path . $passport_image)) ? base_url("public/backend/assets/profiles/default.png") : base_url($passport_path . $passport_image)));
            if (!empty($partner_data[0]['passport'])) {
                if (check_exists(base_url('/public/uploads/users/partners/banner_images/' . $passport_image)) || check_exists($passport_image)) {
                    unlink($passport_image);
                }
            }
            if (!empty($user[0]['image'])) {
                if (check_exists(base_url('public/backend/assets/profiles/' . $profile_image)) || check_exists(base_url('/public/uploads/users/partners/' . $profile_image)) || check_exists($profile_image)) {
                    unlink($profile_image);
                }
            }
            if (!empty($partner_data[0]['banner'])) {
                if (check_exists(base_url('/public/uploads/users/partners/banner_images/' . $banner_image)) || check_exists($banner_image)) {
                    unlink($banner_image);
                }
            }
            if (delete_details(['id' => $user_id], 'users') && delete_details(['user_id' => $user_id], 'users_groups')) {
                delete_details(['user_id' => $user_id], 'users_tokens'); // delete tokens of deleted user
                delete_details(['partner_id' => $user_id], 'promo_codes'); // delete promo code of deleted partner
                $slider_data = fetch_details('sliders', ['type' => 'services'], 'type_id');
                foreach ($slider_data as $row) {
                    $data = fetch_details('services', ['id' => $row['type_id']], 'user_id');
                    if ($data[0]['user_id'] == $user_id) {
                        delete_details(['type_id' => $row['type_id']], 'sliders'); // delete slider of deleted user service
                    }
                }
                return response('User account deleted successfully', false);
            } else {
                return response('User account does not delete', true);
            }
        } else {
            return response("This user's account can't delete ", true);
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    public function change_password()
    {
        // try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'old' => 'required',
                    'new' => 'required',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $user_id = $this->user_details['id'];
            $user_data = fetch_details('users', ['id' => $user_id]);
            $identity = $user_data[0]['phone'];
            // $change = $this->ionAuth->changePassword($identity, $this->request->getPost('old'), $this->request->getPost('new'));
            $change = $this->ionAuth->changePassword($identity, $this->request->getPost('old'), $this->request->getPost('new'),$user_id);
            if ($change) {
                $this->ionAuth->logout();
                return $this->response->setJSON([
                    'error' => false,
                    'message' => "Password changes successfully",
                    "data" => $_POST,
                ]);
            } else {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => "Old password did not matched.",
                    "data" => $_POST,
                ]);
            }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    //forgot password
    public function forgot_password()
    {
        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'new_password' => 'required',
                    'mobile_number' => 'required',
                    'country_code' => 'required',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            // $user_id = $this->user_details['id'];
            $identity = $this->request->getPost('mobile_number');
            $user_data = fetch_details('users', ['phone' => $identity]);
            if (empty($user_data)) {
                return $this->response->setJSON([
                    'error' => false,
                    'message' => "User does not exist",
                    "data" => $_POST,
                ]);
            }
            if ((($user_data[0]['country_code'] == null) || ($user_data[0]['country_code'] == $this->request->getPost('country_code'))) && (($user_data[0]['phone'] == $identity))) {
                $change = $this->ionAuth->resetPassword($identity, $this->request->getPost('new_password'));
                if ($change) {
                    $this->ionAuth->logout();
                    return $this->response->setJSON([
                        'error' => false,
                        'message' => "Forgot Password  successfully",
                        "data" => $_POST,
                    ]);
                } else {
                    return $this->response->setJSON([
                        'error' => true,
                        'message' => $this->ionAuth->errors($this->validationListTemplate),
                        "data" => $_POST,
                    ]);
                }
                $change = $this->ionAuth->resetPassword($identity, $this->request->getPost('new_password'));
                if ($change) {
                    $this->ionAuth->logout();
                    return $this->response->setJSON([
                        'error' => false,
                        'message' => "Forgot Password  successfully",
                        "data" => $_POST,
                    ]);
                } else {
                    return $this->response->setJSON([
                        'error' => true,
                        'message' => $this->ionAuth->errors($this->validationListTemplate),
                        "data" => $_POST,
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => "Faorgot Password Failed",
                    "data" => $_POST,
                ]);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
    public function get_cash_collection()
    {
        // try {
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
        $where = ['partner_id' => $user_id];
        if (!empty($this->request->getPost('admin_cash_recevied'))) {
            $where['status'] = "admin_cash_recevied";
        }
        if (!empty($this->request->getPost('provider_cash_recevied'))) {
            $where['status'] = "provider_cash_recevied";
        }
        $res = fetch_details('cash_collection', $where, '', $limit, $offset, $sort, $order);
        $payable_commision = fetch_details("users", ["id" => $this->user_details['id']], ['payable_commision']);
        $total = count($res);
        if (!empty($res)) {
            $response = [
                'error' => false,
                'message' => 'Cash collection history recieved successfully.',
                'total' => strval($total),
                'payable_commision' => isset($payable_commision[0]['payable_commision']) ? $payable_commision[0]['payable_commision'] : "0",
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
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }
    public function get_settlement_history()
    {
        try {
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
            $res = fetch_details('settlement_history', ['provider_id' => $user_id], '', $limit, $offset, $sort, $order);

            $total = count($res);
            if (!empty($res)) {
                $response = [
                    'error' => false,
                    'message' => 'Settlement history recieved successfully.',
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
    public function get_all_categories()
    {
        try {
            $categories = new Category_model();
            $limit = !empty($this->request->getPost('limit')) ?  $this->request->getPost('limit') : '0';
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('soft'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'DESC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $where = [];
            if ($this->request->getPost('id')) {
                $where['id'] = $this->request->getPost('id');
            }
            if ($this->request->getPost('slug')) {
                $where['slug'] = $this->request->getPost('slug');
            }
            $data = $categories->list(true, $search, $limit, $offset, $sort, $order, $where);
            if (!empty($data['data'])) {
                return response('Categories fetched successfully', false, $data['data'], 200, ['total' => $data['total']]);
            } else {
                return response('categories not found', false);
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function get_subscription()
    {

        // try {

        $where = [];
        $subscription_id = $this->request->getPost('subscription_id');

        if (null !== $subscription_id) {
            $where['id'] = $subscription_id;
        }

        $where['status'] = 1;
        $where['publish'] = 1;

        $subscription_details = fetch_details('subscriptions', $where);
        foreach ($subscription_details as $row) {
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $tempRow['description'] = $row['description'];
            $tempRow['duration'] = $row['duration'];
            $tempRow['price'] = $row['price'];
            $tempRow['discount_price'] = $row['discount_price'];
            $tempRow['publish'] = $row['publish'];
            $tempRow['order_type'] = $row['order_type'];
            $tempRow['max_order_limit'] = ($row['order_type'] == "limited") ? $row['max_order_limit'] : "-";
            $tempRow['service_type'] = $row['service_type'];
            $tempRow['max_service_limit'] = $row['max_service_limit'];
            $tempRow['tax_type'] = $row['tax_type'];
            $tempRow['tax_id'] = $row['tax_id'];
            $tempRow['is_commision'] = $row['is_commision'];
            $tempRow['commission_threshold'] = $row['commission_threshold'];
            $tempRow['commission_percentage'] = $row['commission_percentage'];
            $tempRow['status'] = $row['status'];
            $taxPercentageData = fetch_details('taxes', ['id' => $row['tax_id']], ['percentage']);
            if (!empty($taxPercentageData)) {

                $taxPercentage = $taxPercentageData[0]['percentage'];
            } else {
                $taxPercentage = 0;
            }
            $tempRow['tax_percentage'] = $taxPercentage;

            if ($row['discount_price'] == "0") {
                if ($row['tax_type'] == "excluded") {
                    $tempRow['tax_value'] = number_format((intval(($row['price'] * ($taxPercentage) / 100))), 2);
                    $tempRow['price_with_tax']  = strval($row['price'] + ($row['price'] * ($taxPercentage) / 100));
                    $tempRow['original_price_with_tax'] = strval($row['price'] + ($row['price'] * ($taxPercentage) / 100));
                } else {
                    $tempRow['tax_value'] = "";
                    $tempRow['price_with_tax']  = strval($row['price']);
                    $tempRow['original_price_with_tax'] = strval($row['price']);
                }
            } else {
                if ($row['tax_type'] == "excluded") {
                    $tempRow['tax_value'] = number_format((intval(($row['discount_price'] * ($taxPercentage) / 100))), 2);
                    $tempRow['price_with_tax']  = strval($row['discount_price'] + ($row['discount_price'] * ($taxPercentage) / 100));
                    $tempRow['original_price_with_tax'] = strval($row['price'] + ($row['discount_price'] * ($taxPercentage) / 100));
                } else {
                    $tempRow['tax_value'] = "";
                    $tempRow['price_with_tax']  = strval($row['discount_price']);
                    $tempRow['original_price_with_tax'] = strval($row['price']);
                }
            }
            $rows[] = $tempRow;
        }



        if (!empty($rows)) {
            return response('Subscriptions fetched successfully', false, $rows, 200, ['total' => count($subscription_details)]);
        } else {
            return response('Subscriptions not found', false);
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }

    public function buy_subscription()
    {

        try {
            $validation =  \Config\Services::validation();
            $validation->setRules(
                [
                    'subscription_id' => 'required',
                ]
            );
            if (!$validation->withRequest($this->request)->run()) {
                $errors = $validation->getErrors();
                $response = [
                    'error' => true,
                    'message' => $errors,
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }

            $partner_id = $this->user_details['id'];
            $subscription_id = $this->request->getPost('subscription_id');

            // Check if partner already has an ongoing (active) subscription
            $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $partner_id, 'status' => 'active']);
            if (!empty($is_already_subscribe)) {
                return $this->response->setJSON([
                    'error' => false,
                    'message' => "Already have an active subscription",
                    'data' => []
                ]);
            }

            // Fetch subscription details
            $subscription_details = fetch_details('subscriptions', ['id' => $subscription_id]);
            $price = $subscription_details[0]['price'];
            $discount_price = $subscription_details[0]['discount_price'];

            $is_commission_based = $subscription_details[0]['is_commision'] == "yes";

            if ($price == "0") {

                $partner_subscriptions = [
                    'partner_id' =>  $partner_id,
                    'subscription_id' => $subscription_id,
                    'is_payment' => "1",
                    'status' => "active",
                    'purchase_date' => date('Y-m-d'),
                    'expiry_date' => date('Y-m-d'),
                    'name' => $subscription_details[0]['name'],
                    'description' => $subscription_details[0]['description'],
                    'duration' => $subscription_details[0]['duration'],
                    'price' => $subscription_details[0]['price'],
                    'discount_price' => $subscription_details[0]['discount_price'],
                    'publish' => $subscription_details[0]['publish'],
                    'order_type' => $subscription_details[0]['order_type'],
                    'max_order_limit' => $subscription_details[0]['max_order_limit'],
                    'service_type' => $subscription_details[0]['service_type'],
                    'max_service_limit' => $subscription_details[0]['max_service_limit'],
                    'tax_type' => $subscription_details[0]['tax_type'],
                    'tax_id' => $subscription_details[0]['tax_id'],
                    'is_commision' => $subscription_details[0]['is_commision'],
                    'commission_threshold' => $subscription_details[0]['commission_threshold'],
                    'commission_percentage' => $subscription_details[0]['commission_percentage'],
                ];
                insert_details($partner_subscriptions, 'partner_subscriptions');

                $commission = $is_commission_based ? $subscription_details[0]['commission_percentage'] : 0;
                update_details(['admin_commission' => $commission], ['partner_id' => $partner_id], 'partner_details');
            } else {

                $subscriptionDuration = $subscription_details[0]['duration'];
                $purchaseDate = date('Y-m-d');
                $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
                // $partner_subscriptions = [
                //     'partner_id' =>  $partner_id,
                //     'subscription_id' => $subscription_id,
                //     'is_payment' => "0",
                //     'status' => "pending",
                //     'purchase_date' => $purchaseDate,
                //     'expiry_date' => $expiryDate,
                // ];
                $details_for_subscription = fetch_details('subscriptions', ['id' => $subscription_id]);
                $subscriptionDuration = $details_for_subscription[0]['duration'];
                $partner_subscriptions = [
                    'partner_id' =>  $partner_id,
                    'subscription_id' => $subscription_id,
                    'is_payment' => "0",
                    'status' => "pending",
                    'purchase_date' => $purchaseDate,
                    'expiry_date' => $expiryDate,
                    'name' => $details_for_subscription[0]['name'],
                    'description' => $details_for_subscription[0]['description'],
                    'duration' => $details_for_subscription[0]['duration'],
                    'price' => $details_for_subscription[0]['price'],
                    'discount_price' => $details_for_subscription[0]['discount_price'],
                    'publish' => $details_for_subscription[0]['publish'],
                    'order_type' => $details_for_subscription[0]['order_type'],
                    'max_order_limit' => $details_for_subscription[0]['max_order_limit'],
                    'service_type' => $details_for_subscription[0]['service_type'],
                    'max_service_limit' => $details_for_subscription[0]['max_service_limit'],
                    'tax_type' => $details_for_subscription[0]['tax_type'],
                    'tax_id' => $details_for_subscription[0]['tax_id'],
                    'is_commision' => $details_for_subscription[0]['is_commision'],
                    'commission_threshold' => $details_for_subscription[0]['commission_threshold'],
                    'commission_percentage' => $details_for_subscription[0]['commission_percentage'],
                ];
                $data = insert_details($partner_subscriptions, 'partner_subscriptions');
            }

            $response = [
                'error' => false,
                'message' => 'Congratulations on your subscription! Now is the time to shine on eDEmand and seize new business opportunities. Welcome aboard and best of luck!',
                'data' => []
            ];
        } catch (Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
        }

        return $this->response->setJSON($response);
    }




    public function add_transaction()
    {
        // try {
        $validation = service('validation');
        $validation->setRules([
            'subscription_id' => 'required|numeric',
            'status' => 'required',
            'message' => 'required',
            'type' => 'required',
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
        $subscription_id = (int) $this->request->getVar('subscription_id');
        $status = $this->request->getVar('status');
        $message = $this->request->getVar('message');
        $type = $this->request->getVar('type');

        $user = fetch_details('users', ['id' => $this->user_details['id']]);
        if (empty($user)) {
            $response = [
                'error' => true,
                'message' => "User not found!",
                'data' => [],
            ];
            return $this->response->setJSON($response);
        }

        $subscription = fetch_details('subscriptions', ['id' => $this->request->getVar('subscription_id')]);

        $transaction_id = fetch_details('transactions', ['id' => $this->request->getVar('transaction_id')]);
        $price = $subscription[0]['price'];
        $discount_price = $subscription[0]['discount_price'];
        $is_commission_based = $subscription[0]['is_commision'] == "yes";

        if ($status != "success") {

            // Check if partner already has an ongoing (active) subscription
            $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->user_details['id'], 'status' => 'active']);
            if (!empty($is_already_subscribe)) {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => "Already have an active subscription",
                    'data' => []
                ]);
            }
        }

        if (!empty($subscription)) {

            if (!empty($transaction_id)) {
                $data1['status'] = $status;
                $data1['type'] = $type;
                $data1['message'] = $message;
                $subscription_data['status'] = ($status == "failed") ? 'deactive' : 'active';
                $subscription_data['is_payment'] = ($status == "failed") ? '2' : '1';
                $condition = ['subscription_id' => $subscription_id, 'partner_id' => $this->user_details['id'], 'transaction_id' => $this->request->getVar('transaction_id')];
                update_details($subscription_data, $condition, 'partner_subscriptions');

                update_details($data1, ['id' => $this->request->getVar('transaction_id')], 'transactions');

                $data['transaction'] = fetch_details('transactions', ['id' => $this->request->getVar('transaction_id') ?? null])[0];

                $subscription = fetch_details('partner_subscriptions', ['partner_id' => $transaction_id[0]['user_id'], 'subscription_id' => $transaction_id[0]['subscription_id']]);


                $subscription_information['subscription_id'] = isset($subscription[0]['subscription_id']) ? $subscription[0]['subscription_id'] : "";
                $subscription_information['isSubscriptionActive'] = isset($subscription[0]['status']) ? $subscription[0]['status'] : "deactive";
                $subscription_information['created_at'] = isset($subscription[0]['created_at']) ? $subscription[0]['created_at'] : "";
                $subscription_information['updated_at'] = isset($subscription[0]['updated_at']) ? $subscription[0]['updated_at'] : "";
                $subscription_information['is_payment'] = isset($subscription[0]['is_payment']) ? $subscription[0]['is_payment'] : "";
                $subscription_information['id'] = isset($subscription[0]['id']) ? $subscription[0]['id'] : "";
                $subscription_information['partner_id'] = isset($subscription[0]['partner_id']) ? $subscription[0]['partner_id'] : "";
                $subscription_information['purchase_date'] = isset($subscription[0]['purchase_date']) ? $subscription[0]['purchase_date'] : "";
                $subscription_information['expiry_date'] = isset($subscription[0]['expiry_date']) ? $subscription[0]['expiry_date'] : "";
                $subscription_information['name'] = isset($subscription[0]['name']) ? $subscription[0]['name'] : "";
                $subscription_information['description'] = isset($subscription[0]['description']) ? $subscription[0]['description'] : "";
                $subscription_information['duration'] = isset($subscription[0]['duration']) ? $subscription[0]['duration'] : "";
                $subscription_information['price'] = isset($subscription[0]['price']) ? $subscription[0]['price'] : "";
                $subscription_information['discount_price'] = isset($subscription[0]['discount_price']) ? $subscription[0]['discount_price'] : "";
                $subscription_information['order_type'] = isset($subscription[0]['order_type']) ? $subscription[0]['order_type'] : "";
                $subscription_information['max_order_limit'] = isset($subscription[0]['max_order_limit']) ? $subscription[0]['max_order_limit'] : "";
                $subscription_information['is_commision'] = isset($subscription[0]['is_commision']) ? $subscription[0]['is_commision'] : "";
                $subscription_information['commission_threshold'] = isset($subscription[0]['commission_threshold']) ? $subscription[0]['commission_threshold'] : "";
                $subscription_information['commission_percentage'] = isset($subscription[0]['commission_percentage']) ? $subscription[0]['commission_percentage'] : "";
                $subscription_information['publish'] = isset($subscription[0]['publish']) ? $subscription[0]['publish'] : "";
                $subscription_information['tax_id'] = isset($subscription[0]['tax_id']) ? $subscription[0]['tax_id'] : "";
                $subscription_information['tax_type'] = isset($subscription[0]['tax_type']) ? $subscription[0]['tax_type'] : "";

                if (!empty($subscription[0])) {

                    $price = calculate_partner_subscription_price($subscription[0]['partner_id'], $subscription[0]['subscription_id'], $subscription[0]['id']);
                }
                $subscription_information['tax_value'] = isset($price[0]['tax_percentage']) ? $price[0]['tax_percentage'] : "";
                $subscription_information['price_with_tax']  = isset($price[0]['price_with_tax']) ? $price[0]['price_with_tax'] : "";
                $subscription_information['original_price_with_tax'] = isset($price[0]['original_price_with_tax']) ? $price[0]['original_price_with_tax'] : "";

                $data['subscription_information'] = json_decode(json_encode($subscription_information), true);


                $response['error'] = false;
                $response['data'] = $data;

                $response['message'] = 'Transaction Updated successfully';
            } else {


                $taxPercentageData = fetch_details('taxes', ['id' => $subscription[0]['tax_id']], ['percentage']);
                if (!empty($taxPercentageData)) {

                    $taxPercentage = $taxPercentageData[0]['percentage'];
                } else {
                    $taxPercentage = 0;
                }


                if (!empty($subscription[0])) {

                    $price = calculate_subscription_price($subscription[0]['id']);
                }


                $trsansction_data = [
                    'transaction_type' => 'transaction',
                    'user_id' => $this->user_details['id'],
                    'partner_id' => "",
                    'order_id' => "0",
                    'type' => $type,
                    'txn_id' => "0",
                    'amount' =>  $price[0]['price_with_tax'],
                    'status' => $status,
                    'currency_code' => "",
                    'subscription_id' => $subscription_id,
                    'message' => $message,
                ];
                $insert = add_transaction($trsansction_data);





                if ($subscription[0]['price'] == "0") {
                    $subscriptionDuration = $subscription[0]['duration'];
                    if ($subscriptionDuration == "unlimited") {
                        $subscriptionDuration = 0;
                    }
                    $purchaseDate = date('Y-m-d');
                    $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
                    if ($subscriptionDuration == "unlimited") {
                        $subscriptionDuration = 0;
                    }
                    $partner_subscriptions = [
                        'partner_id' =>   $this->user_details['id'],
                        'subscription_id' => $subscription_id,
                        'is_payment' => "1",
                        'status' => "active",
                        'purchase_date' => date('Y-m-d'),
                        'expiry_date' => $expiryDate,
                        'name' => $subscription[0]['name'],
                        'description' => $subscription[0]['description'],
                        'duration' => $subscription[0]['duration'],
                        'price' => $subscription[0]['price'],
                        'discount_price' => $subscription[0]['discount_price'],
                        'publish' => $subscription[0]['publish'],
                        'order_type' => $subscription[0]['order_type'],
                        'max_order_limit' => $subscription[0]['max_order_limit'],
                        'service_type' => $subscription[0]['service_type'],
                        'max_service_limit' => $subscription[0]['max_service_limit'],
                        'tax_type' => $subscription[0]['tax_type'],
                        'tax_id' => $subscription[0]['tax_id'],
                        'is_commision' => $subscription[0]['is_commision'],
                        'commission_threshold' => $subscription[0]['commission_threshold'],
                        'commission_percentage' => $subscription[0]['commission_percentage'],
                        'transaction_id' => 0,
                        'tax_percentage' => $price[0]['tax_percentage'],
                    ];
                    $insert_subscription =  insert_details($partner_subscriptions, 'partner_subscriptions');

                    $commission = $is_commission_based ? $subscription[0]['commission_percentage'] : 0;
                    update_details(['admin_commission' => $commission], ['partner_id' =>   $this->user_details['id']], 'partner_details');
                } else {

                    $subscriptionDuration = $subscription[0]['duration'];
                    if ($subscriptionDuration == "unlimited") {
                        $subscriptionDuration = 0;
                    }
                    $purchaseDate = date('Y-m-d');
                    $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
                    if ($subscriptionDuration == "unlimited") {
                        $subscriptionDuration = 0;
                    }
                    $details_for_subscription = fetch_details('subscriptions', ['id' => $subscription_id]);
                    $partner_subscriptions = [
                        'partner_id' =>    $this->user_details['id'],
                        'subscription_id' => $subscription_id,
                        'is_payment' => "0",
                        'status' => "pending",
                        'purchase_date' => $purchaseDate,
                        'expiry_date' => $expiryDate,
                        'name' => $details_for_subscription[0]['name'],
                        'description' => $details_for_subscription[0]['description'],
                        'duration' => $details_for_subscription[0]['duration'],
                        'price' => $details_for_subscription[0]['price'],
                        'discount_price' => $details_for_subscription[0]['discount_price'],
                        'publish' => $details_for_subscription[0]['publish'],
                        'order_type' => $details_for_subscription[0]['order_type'],
                        'max_order_limit' => $details_for_subscription[0]['max_order_limit'],
                        'service_type' => $details_for_subscription[0]['service_type'],
                        'max_service_limit' => $details_for_subscription[0]['max_service_limit'],
                        'tax_type' => $details_for_subscription[0]['tax_type'],
                        'tax_id' => $details_for_subscription[0]['tax_id'],
                        'is_commision' => $details_for_subscription[0]['is_commision'],
                        'commission_threshold' => $details_for_subscription[0]['commission_threshold'],
                        'commission_percentage' => $details_for_subscription[0]['commission_percentage'],
                        'transaction_id' => $insert,
                        'tax_percentage' => $price[0]['tax_percentage'],

                    ];
                    $insert_subscription = insert_details($partner_subscriptions, 'partner_subscriptions');
                    if ($details_for_subscription[0]['is_commision'] == "yes") {
                        $commission = $details_for_subscription[0]['commission_percentage'];
                    } else {
                        $commission = 0;
                    }
                    update_details(['admin_commission' => $commission], ['partner_id' => $this->user_details['id']], 'partner_details');
                }
                $data['transaction'] = fetch_details('transactions', ['id' => $insert ?? null])[0];


                $subscription = fetch_details('partner_subscriptions', ['id' => $insert_subscription['id']]);


                $subscription_information['subscription_id'] = isset($subscription[0]['subscription_id']) ? $subscription[0]['subscription_id'] : "";

                $subscription_information['isSubscriptionActive'] = isset($subscription[0]['status']) ? $subscription[0]['status'] : "deactive";
                $subscription_information['created_at'] = isset($subscription[0]['created_at']) ? $subscription[0]['created_at'] : "";
                $subscription_information['updated_at'] = isset($subscription[0]['updated_at']) ? $subscription[0]['updated_at'] : "";
                $subscription_information['is_payment'] = isset($subscription[0]['is_payment']) ? $subscription[0]['is_payment'] : "";
                $subscription_information['id'] = isset($subscription[0]['id']) ? $subscription[0]['id'] : "";
                $subscription_information['partner_id'] = isset($subscription[0]['partner_id']) ? $subscription[0]['partner_id'] : "";
                $subscription_information['purchase_date'] = isset($subscription[0]['purchase_date']) ? $subscription[0]['purchase_date'] : "";
                $subscription_information['expiry_date'] = isset($subscription[0]['expiry_date']) ? $subscription[0]['expiry_date'] : "";
                $subscription_information['name'] = isset($subscription[0]['name']) ? $subscription[0]['name'] : "";
                $subscription_information['description'] = isset($subscription[0]['description']) ? $subscription[0]['description'] : "";
                $subscription_information['duration'] = isset($subscription[0]['duration']) ? $subscription[0]['duration'] : "";
                $subscription_information['price'] = isset($subscription[0]['price']) ? $subscription[0]['price'] : "";
                $subscription_information['discount_price'] = isset($subscription[0]['discount_price']) ? $subscription[0]['discount_price'] : "";
                $subscription_information['order_type'] = isset($subscription[0]['order_type']) ? $subscription[0]['order_type'] : "";
                $subscription_information['max_order_limit'] = isset($subscription[0]['max_order_limit']) ? $subscription[0]['max_order_limit'] : "";
                $subscription_information['is_commision'] = isset($subscription[0]['is_commision']) ? $subscription[0]['is_commision'] : "";
                $subscription_information['commission_threshold'] = isset($subscription[0]['commission_threshold']) ? $subscription[0]['commission_threshold'] : "";
                $subscription_information['commission_percentage'] = isset($subscription[0]['commission_percentage']) ? $subscription[0]['commission_percentage'] : "";
                $subscription_information['publish'] = isset($subscription[0]['publish']) ? $subscription[0]['publish'] : "";
                $subscription_information['tax_id'] = isset($subscription[0]['tax_id']) ? $subscription[0]['tax_id'] : "";
                $subscription_information['tax_type'] = isset($subscription[0]['tax_type']) ? $subscription[0]['tax_type'] : "";
                if (!empty($subscription[0])) {

                    $price = calculate_partner_subscription_price($subscription[0]['partner_id'], $subscription[0]['subscription_id'], $subscription[0]['id']);
                }
                $subscription_information['tax_value'] = isset($price[0]['tax_percentage']) ? $price[0]['tax_percentage'] : "";
                $subscription_information['price_with_tax']  = isset($price[0]['price_with_tax']) ? $price[0]['price_with_tax'] : "";
                $subscription_information['original_price_with_tax'] = isset($price[0]['original_price_with_tax']) ? $price[0]['original_price_with_tax'] : "";
                $subscription_information['tax_percentage'] = isset($price[0]['tax_percentage']) ? $price[0]['tax_percentage'] : "";



                $data['subscription_information'] = json_decode(json_encode($subscription_information), true);
                $param['client_id'] = $this->userId;
                $param['insert_id'] = $insert;
                $param['package_id'] =  isset($subscription[0]['subscription_id']) ? $subscription[0]['subscription_id'] : "";
                $param['net_amount'] =  isset($price[0]['price_with_tax']) ? $price[0]['price_with_tax'] : "";
                // ($payment_method == "paypal") ? base_url() . '/api/v1/paypal_transaction_webview?user_id=' . $this->user_details['id'] . '&order_id=' . $insert_order['id'] . '&amount=' . ceil(number_format(strval($total + $visiting_charges), 2)) . '' : "";
                $data['paypal_link'] = ($type == "paypal") ? base_url() . '/partner/api/v1/paypal_transaction_webview?client_id=' . $this->user_details['id'] . '&insert_id=' . $insert . '&package_id=' . $subscription[0]['subscription_id'] . '&net_amount=' . $price[0]['price_with_tax'] : "";



                $response['error'] = false;
                $response['data'] = $data;
                $response['message'] = 'Transaction addedd successfully';
            }
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        // }
        return $this->response->setJSON($response);
    }

    public function paypal_transaction_webview()
    {


        $this->paypal_lib = new Paypal();
        $insert_id = $_GET['insert_id'];
        $user_id = $_GET['client_id'];
        $net_amount = $_GET['net_amount'];
        $user = fetch_details('users', ['id' => $user_id]);
        $data['user'] = $user[0];
        $data['payment_type'] = "paypal";
        $returnURL = base_url() . '/partner/api/v1/app_payment_status';
        $cancelURL = base_url() . '/partner/api/v1/app_payment_status';
        $notifyURL = base_url() . '/api/webhooks/paypal';
        $payeremail = $data['user']['email'];   // Add fields to paypal form
        $this->paypal_lib->add_field('return', $returnURL);
        $this->paypal_lib->add_field('cancel_return', $cancelURL);
        $this->paypal_lib->add_field('notify_url', $notifyURL);
        $this->paypal_lib->add_field('item_name', 'Test');
        $this->paypal_lib->add_field('custom',  $insert_id . '|' . $payeremail.'|subscription');
        $this->paypal_lib->add_field('item_number', $insert_id);
        $this->paypal_lib->add_field('amount', $net_amount);
        $this->paypal_lib->paypal_auto_form();
    }

    public function app_payment_status()
    {


        $paypalInfo = $_GET;
        if (!empty($paypalInfo) && isset($_GET['st']) && strtolower($_GET['st']) == "completed") {
            $response['error'] = false;
            $response['message'] = "Payment Completed Successfully";
            $response['data'] = $paypalInfo;
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
    public function razorpay_create_order()
    {
        // try {
        $validation = \Config\Services::validation();
        $validation->setRules(
            [
                'subscription_id' => 'required|numeric',
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
        $subscription_id = $this->request->getPost('subscription_id');
        if ($this->request->getPost('subscription_id') && !empty($this->request->getPost('subscription_id'))) {
            $where['s.id'] = $this->request->getPost('subscription_id');
        }
        $subscription = new Subscription_model();
        $subscription_detail = $subscription->list(true, '', 10, 0, 's.id', 'DESC', $where);


        $settings = get_settings('payment_gateways_settings', true);
        if (!empty($subscription_detail) && !empty($settings)) {
            $currency = $settings['razorpay_currency'];
            $price = ($subscription_detail['data'][0]['discount_price'] == "0") ? $subscription_detail['data'][0]['price'] : $subscription_detail['data'][0]['discount_price'];
            $amount = intval($price * 100);
            $create_order = $this->razorpay->create_order($amount, $subscription_id, $currency);
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
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }

    public function get_subscription_history()
    {
        try {
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
            $res = fetch_details('partner_subscriptions', ['partner_id' => $user_id, 'status' => 'deactive', 'is_payment' => '1'], '', $limit, $offset, $sort, $order);

            foreach ($res as $key => $row) {
                $price = calculate_partner_subscription_price($row['partner_id'], $row['subscription_id'], $row['id']);
                $res[$key]['tax_value'] = $price[0]['tax_value'];
                $res[$key]['price_with_tax'] = $price[0]['price_with_tax'];
                $res[$key]['original_price_with_tax'] = $price[0]['original_price_with_tax'];
                $res[$key]['tax_percentage'] = $price[0]['tax_percentage'];

                // Rename the 'status' field to 'isSubscriptionActive'
                $res[$key]['isSubscriptionActive'] = $row['status'];
                unset($res[$key]['status']);
            }



            $total = fetch_details('partner_subscriptions', ['partner_id' => $user_id, 'status' => 'deactive', 'is_payment' => '1']);

            $total = count($total);
            if (!empty($res)) {
                $response = [
                    'error' => false,
                    'message' => 'Subscription history recieved successfully.',
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
}
