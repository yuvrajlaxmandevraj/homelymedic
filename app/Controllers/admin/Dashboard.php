<?php

namespace App\Controllers\admin;

use App\Libraries\Razorpay;
use App\Models\Orders_model;
use App\Models\Partners_model;
use App\Models\Service_ratings_model;
use Razorpay\Api\Api;

// use App\models\Partners_model;
class Dashboard extends Admin
{
    public function __construct()
    {

        parent::__construct();
        $this->user_model = new \App\Models\Users_model();
        $this->orders = new \App\Models\Orders_model();
    }



    public function cancle_elapsed_time_order()
    {





        //for stripe


        $currentDate = date('Y-m-d');
        $currentTimestamp = time();
        $currentTime = date('H:i', $currentTimestamp);
        //for prepaid  auto cancellation

        $prepaid_orders = fetch_details('orders', ['status' => 'awaiting', 'payment_status' => 0, 'date_of_service' => $currentDate]);
        $setting = get_settings('general_settings', true);
        $prepaid_booking_cancellation_time = (isset($setting['prepaid_booking_cancellation_time'])) ? intval($setting['prepaid_booking_cancellation_time']) : "30";


        foreach ($prepaid_orders as $order) {



            $serviceTime = strtotime($order['starting_time']);
            $checkTime = $serviceTime - ($prepaid_booking_cancellation_time * 60); // 1800 seconds = 30 minutes
            if ($checkTime <= strtotime($currentTime)) {


                verify_transaction($order['id']);
            }
        }
    }

    public function update_subscription_status()
    {


        $db = \Config\Database::connect();
        $builder1 = $db->table('users u1');
        $partners1 = $builder1->select("u1.username, u1.city, u1.latitude, u1.longitude, u1.id")
            ->join('users_groups ug1', 'ug1.user_id = u1.id')
            ->join('partner_subscriptions ps', 'ps.partner_id = u1.id')
            ->where('ps.status', 'active')
            ->where('ps.price !=', 0) // Add this line to filter where price is not equal to 0
            // ->where('ps.order_type', 'limited')
            ->where('ug1.group_id', '3')
            ->get()
            ->getResultArray();

        $ids = [];
        foreach ($partners1 as $key => $row1) {
            $ids[] = $row1['id'];
        }



        // Check order limit for each partner and deactivate subscription if reached
        foreach ($ids as $key => $id) {
            $partner_subscription_data = $db->table('partner_subscriptions ps');
            $partner_subscription_data = $partner_subscription_data->select('ps.*')->where('ps.status', 'active')->where('partner_id', $id)
                ->get()
                ->getRow();

            $subscription_order_limit = $partner_subscription_data->max_order_limit;


            // Fetch the count of orders placed after the purchase_date
            $orders_count = $db->table('orders')
                ->where('partner_id', $id)
                ->where('created_at >', $partner_subscription_data->updated_at)
                ->countAllResults();


            // print_r($id.'------number of order -' . $orders_count . '----max order limit----' . $subscription_order_limit);
            // echo "<br>";

            if ($partner_subscription_data->order_type == "limited") {
                if ($orders_count >= $subscription_order_limit) {
                    $data['status'] = 'deactive';
                    $where['partner_id'] = $id;
                    $where['status'] = 'active';
                    // $where['partner_subscription_data'] = 'active';

                    update_details($data, $where, 'partner_subscriptions');

                    log_message('error', 'updated');
                }
            }
        }


        $subscription_list = fetch_details('partner_subscriptions', ['status' => 'active',]);
        $currentTimestamp = date("H-i A");
        $current_date = date('Y-m-d');
        foreach ($subscription_list as $key => $row) {
            if ($row['duration'] != 'unlimited') {

                if ($row['expiry_date'] <= $current_date) {

                    if ($currentTimestamp == "11-59 PM") {
                        $data['status'] = 'deactive';
                        $where['id'] = $row['id'];
                        $where['status'] = 'active';
                        $where['duration !='] = 'unlimited';
                        update_details($data, $where, 'partner_subscriptions');
                        log_message('error', 'Subscription expired and updated to deactive');
                    }
                }
            }
        }

        $currentDate = date('Y-m-d');
        $currentTimestamp = time();
        $currentTime = date('H:i', $currentTimestamp);



        //booking auto cancellation
        $orders = fetch_details('orders', ['status' => 'awaiting', 'date_of_service' => $currentDate]);
        $setting = get_settings('general_settings', true);
        $booking_auto_cancle = (isset($setting['booking_auto_cancle_duration'])) ? intval($setting['booking_auto_cancle_duration']) : "30";


        foreach ($orders as $order) {



            $serviceTime = strtotime($order['starting_time']);
            $checkTime = $serviceTime - ($booking_auto_cancle * 60); // 1800 seconds = 30 minutes
            if ($checkTime <= strtotime($currentTime)) {
                $data = process_refund($order['id'], 'cancelled', $order['user_id']);
                update_details(['status' => 'cancelled'], ['id' => $order['id']], 'orders');
            }
        }





        // //for prepaid  auto cancellation

        // $prepaid_orders = fetch_details('orders', ['status' => 'awaiting', 'payment_status' => 0, 'date_of_service' => $currentDate]);
        // $setting = get_settings('general_settings', true);
        // $prepaid_booking_cancellation_time = (isset($setting['prepaid_booking_cancellation_time'])) ? intval($setting['prepaid_booking_cancellation_time']) : "30";


        // foreach ($prepaid_orders as $order) {



        //     $serviceTime = strtotime($order['starting_time']);
        //     $checkTime = $serviceTime - ($prepaid_booking_cancellation_time * 60); // 1800 seconds = 30 minutes
        //     if ($checkTime <= strtotime($currentTime)) {
        //         // $data = process_refund($order['id'], 'cancelled', $order['user_id']); 
        //         update_details(['status' => 'cancelled'], ['id' => $order['id']], 'orders');
        //     }
        // }
    }



    public function index()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('orders');
        $orders = $builder->select('promo_code')->where("promo_code IS NOT NULL AND promo_code != ''")->get()->getResultArray();
        $builder = $db->table('orders');

        foreach ($orders as $row) {


            $data = fetch_details('promo_codes', ['promo_code' => $row]);
            if (!empty($data)) {

                $order['promocode_id'] = $data[0]['id'];
                $builder->update($order, ['promo_code' => $row]);
            }
        }


        if ($this->isLoggedIn && $this->userIsAdmin) {


            $db = \Config\Database::connect();

            $ionAuth = new \IonAuth\Libraries\IonAuth();
            $id = $ionAuth->user()->row()->id;
            $admin = 1;
            $cust_id = 2;
            $partner_id = 3;




            $total_users = $db->table('users u')->select('count(u.id) as `total`')->get()->getResultArray()[0]['total'];
            $total_admin = $db->table('users_groups ug')->select('count(ug.group_id) as `total`')->where(['group_id' => $admin])->get()->getResultArray()[0]['total'];

            $total_cutsomers = $db->table('users_groups ug')->select('count(ug.group_id) as `total`')->where(['group_id' => $cust_id])->get()->getResultArray()[0]['total'];



            $total_partners = $db->table('users_groups ug')->select('count(ug.group_id) as `total`')->where(['group_id' => $partner_id])->get()->getResultArray()[0]['total'];
            $total_balance = $db->table('users u')->select('sum(u.balance) as `total`')->where(['id' => $id])->get()->getResultArray()[0]['total'];
            $total_on_sale = $db->table('offers of')->select('count(of.id) as `total`')->get()->getResultArray()[0]['total'];
            // service section
            $total_services = $db->table('services s')->select('count(s.id) as `total`')->get()->getResultArray()[0]['total'];

            $service_condition =  "created_at > CURRENT_DATE - INTERVAL 30 day";

            $new_services = $db->table('services s')->select('count(s.id) as `total`')->where($service_condition)->get()->getResultArray()[0]['total'];
            $total_on_sale_service = $db->table('services s')->select('count(s.id) as `total`')->where(['discounted_price >=' => 0])->get()->getResultArray()[0]['total'];

            $top_rated_service_condition = 'rating >= 4 and number_of_ratings >= 10';
            $total_top_rated = $db->table('services s')->select('count(s.id) as `total`')->where($top_rated_service_condition)->get()->getResultArray()[0]['total'];

            $available_services = $db->table('services s')->select('count(s.id) as `total`')->where('status', 1)->get()->getResultArray()[0]['total'];
            //  ends here 

            //  passing service data 
            $this->data['total_services'] = $total_services;
            $this->data['total_on_sale_service'] = $total_on_sale_service;
            $this->data['new_services'] = $new_services;
            $this->data['available_services'] = $available_services;
            // 

            // order related_data
            $total_orders = $db->table('orders o')->select('count(o.id) as `total`')->where('o.parent_id  IS NULL')->get()->getResultArray()[0]['total'];
            $total_awaiting = $db->table('orders o')->select('count(o.id) as `total`')->where(['status' => 'awaiting'])->get()->getResultArray()[0]['total'];
            $total_confirmed = $db->table('orders o')->select('count(o.id) as `total`')->where(['status' => 'confirmed'])->get()->getResultArray()[0]['total'];
            $total_completed = (!empty($db->table('orders o')->select('count(o.id) as `total`')->where(['status' => 'completed'])->get()->getResultArray())) ? $db->table('orders o')->select('count(o.id) as `total`')->where(['status' => 'completed'])->get()->getResultArray()[0]['total'] : 0;
            $total_rescheduled = (!empty($db->table('orders o')->select('count(o.id) as `total`')->where(['status' => 'rescheduled'])->get()->getResultArray())) ? $db->table('orders o')->select('count(o.id) as `total`')->where(['status' => 'rescheduled'])->get()->getResultArray()[0]['total'] : 0;
            $total_cancelled = (!empty($db->table('orders o')->select('count(o.id) as `total`')->where(['status' => 'cancelled'])->get()->getResultArray())) ? $db->table('orders o')->select('count(o.id) as `total`')->where(['status' => 'cancelled'])->get()->getResultArray()[0]['total'] : 0;



            //             
            // echo "<pre>";
            $earning =  $db
                ->table('orders o')
                ->select('
                       o.final_total, pd.admin_commission,pd.*,
                        SUM(o.final_total - (( o.final_total * pd.admin_commission)/100)) as total_partner_earning,
                        SUM(( o.final_total * pd.admin_commission)/100) as total_admin_earning,
                        SUM(o.final_total) as total_earning
                ')->where('o.status', 'completed')
                ->join('partner_details pd', 'pd.partner_id = o.partner_id', 'left')
                ->get()->getResultArray();


            $symbol =   get_currency();
            $this->data['title'] = 'Admin Panel';
            $this->data['total_admin'] = $total_admin;
            $this->data['total_customers'] = $total_cutsomers;

            $this->data['total_partners'] = $total_partners;
            $this->data['total_orders'] = $total_orders;
            $this->data['total_users']  = $total_users;
            $this->data['total_balance'] = $total_balance;
            $this->data['total_awaiting'] = $total_awaiting;
            $this->data['total_rescheduled'] = $total_rescheduled;
            $this->data['total_cancelled'] = $total_cancelled;
            $this->data['total_confirmed'] = $total_confirmed;
            $this->data['total_completed'] = $total_completed;
            $this->data['total_on_sale'] = $total_on_sale;
            $this->data['currency'] = $symbol;

            $this->data['total_top_rated'] = $total_top_rated;
            $this->data['admin_earning'] = (!empty($earning[0]['total_admin_earning'])) ? number_format($earning[0]['total_admin_earning'], 2, ".", "") : '00';
            $this->data['partner_earnings'] = (!empty($earning[0]['total_partner_earning'])) ? number_format($earning[0]['total_partner_earning'], 2, ".", "") : '00';
            $this->data['total_earning'] = (!empty($earning[0]['total_earning'])) ? number_format($earning[0]['total_earning'], 2, ".", "") : '00';



            $this->data['main_page'] = 'dashboard';
            $Partners_model = new Partners_model();
            $limit = 5;
            $offset = ($this->request->getPost('offset') && !empty($this->request->getPost('offset'))) ? $this->request->getPost('offset') : 0;
            $sort = ($this->request->getPost('sort') && !empty($this->request->getPost('sort'))) ? $this->request->getPost('sort') : 'id';
            $order = ($this->request->getPost('order') && !empty($this->request->getPost('order'))) ? $this->request->getPost('order') : 'ASC';
            $search = ($this->request->getPost('search') && !empty($this->request->getPost('search'))) ? $this->request->getPost('search') : '';
            $filter = ($this->request->getPost('filter') && !empty($this->request->getPost('filter'))) ? $this->request->getPost('filter') : '';
            $where = [];
            $rating_data = $Partners_model->list(true, $search, $limit, $offset, 'number_of_orders', 'desc', $where, 'partner_id', [], '');

            $income_revenue = total_income_revenue();
            $this->data['income_revenue'] = $income_revenue;
            $admin_income_revenue = admin_income_revenue();
            $this->data['admin_income_revenue'] = $admin_income_revenue;

            $provider_income_revenue = provider_income_revenue();
            $this->data['provider_income_revenue'] = $provider_income_revenue;



            $this->data['rating_data'] = $rating_data;
            $rating_wise_rating_data = $Partners_model->list(true, $search, $limit, $offset, ' pd.ratings', 'desc', $where, 'pd.partner_id', [], '');
            $this->data['rating_wise_rating_data'] = $rating_wise_rating_data;



            $top_trending_services = $this->top_trending_services();
            $this->data['top_trending_services'] = $top_trending_services;
            $this->data['categories'] = fetch_details('categories', [], ['id', 'name']);



            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }


    public function top_trending_services()
    {


        $top_trending_services = fetch_top_trending_services((!empty($this->request->getPost('data_trending_filter'))) ? $this->request->getPost('data_trending_filter') : "null");
        if ($this->request->isAJAX()) {
            $response = array('error' => false, 'data' => $top_trending_services);
            print_r(json_encode($response));
        } else {
            return $top_trending_services;
        }
    }
    public function fetch_details()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $sales[] = array();
            $db = \Config\Database::connect();

            $month_total_earning = $db->table('orders o')
                ->select('sum(o.final_total) AS total_earning,DATE_FORMAT(created_at,"%b") AS month_name')
                ->where(['status' => 4])
                ->groupBy('year(CURDATE()),MONTH(created_at)')
                ->orderBy('year(CURDATE()),MONTH(created_at)')
                ->get()->getResultArray();
            $month_wise_earning['total_earning'] = array_map('intval', array_column($month_total_earning, 'total_earning'));
            $month_wise_earning['month_name'] = array_column($month_total_earning, 'month_name');
            $sales = $month_total_earning;
            print_r(json_encode($sales));
        }
    }
    public function list()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';

        print_r(json_encode($this->partner->list(false, $search, $limit, $offset, $sort, $order)));
    }

    public function save_web_token()
    {

        $user = fetch_details('users', ['id' => $this->userId], ['id', 'web_fcm_id']);

        $token = $this->request->getPost('token');

        // print_r($token);
        // die;
        update_details(['web_fcm_id' => $token,], ['id' => $user[0]['id']], 'users');


        print_r(json_encode("token saved"));
    }




    public function test()
    {
        return view('main_system_settings');
    }
    public function forgot_password()
    {
        $this->data['title'] = 'Commission Settlement | Admin Panel';
        $this->data['main_page'] = 'manage_commission';
        // return view('backend/admin/template', $this->data);
        return view('backend/forgot_password_otp');
    }

    public function recent_orders()
    {
        $orders_model = new Orders_model();

        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 7;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';

        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $where = [];        // $search, $limit, $offset, $sort, $order, $where
        print_r($orders_model->list(false, $search, $limit, $offset, $sort, $order, $where, '', '', '', '', '', ''));
        die;
        // return
    }

    public function NotFoundController()
    {

        // Load the 404 view
        return view('404');
    }
}
