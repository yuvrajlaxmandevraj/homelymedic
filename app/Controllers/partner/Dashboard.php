<?php

namespace App\Controllers\partner;

use App\Models\Orders_model;
use App\Models\Promo_code_model;
use App\Models\Service_ratings_model;

class Dashboard extends Partner
{
    public function __construct()
    {
        parent::__construct();
        helper('function');
    }

    public function index()
    {
        if ($this->isLoggedIn && $this->userIsPartner) {
            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }

         
            $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);
            if (empty($is_already_subscribe)) {
                return redirect('partner/subscription');
            }
    
            $db = \Config\Database::connect();
            $id = $this->userId;
            $builder = $db->table('orders o');
            $order_count = $builder->select('count(DISTINCT(o.id)) as total')->where(['o.partner_id'=>$id,'o.parent_id'=>"null"])->get()->getResultArray();
          
            $total_services = $db->table('services s')->select('count(s.id) as `total`')->where(['user_id' => $id])->get()->getResultArray()[0]['total'];
            $total_balance = unsettled_commision($id);

          
            $total_promocodes = $db->table('promo_codes p')->select('count(p.id) as `total`')->where(['partner_id' => $id])->get()->getResultArray()[0]['total'];



            $provider_total_earning_chart=provider_total_earning_chart($id);


            

         
         
          
            $provider_already_withdraw_chart=provider_already_withdraw_chart($id);

            $provider_pending_withdraw_chart=provider_pending_withdraw_chart($id);
            $provider_withdraw_chart=provider_withdraw_chart($id);


            $promocode_model = new Promo_code_model();

         
            $where['partner_id'] = $_SESSION['user_id'];
        
            $db = \Config\Database::connect();
            $id = $this->userId;
            $promo_codes = $db->table('promo_codes')->where(['partner_id' => $id])->where('start_date >', date('Y-m-d'))->orderBy('id', 'DESC')->limit(5, 0)->get()->getResultArray();

      
            $promocode_dates = array();
            $tempRow = array();
            $promocode_dates=array();
            foreach($promo_codes as $promo_code){
                $date=explode('-', $promo_code['start_date']);
                $newDate=$date[1].'-'.$date[2];
                $newDate=explode(' ',$newDate);
                $newDate=$newDate[0];
                $tempRow['start_date']=$newDate;
                $tempRow['promo_code']=$promo_code['promo_code'];
                $tempRow['end_date']=$promo_code['end_date'];
                
                $promocode_dates[]=$tempRow;
                

            }
          

            $ratings = new Service_ratings_model();
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 0;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $data = $ratings->ratings_list(true, $search, $limit, $offset, $sort, $order, ['s.user_id' => $this->userId]);
            
            $total_review=$data['total'];
            

            $total_ratings = $db->table('partner_details p')->select('count(p.ratings) as `total`')->where(['id' => $id])->get()->getResultArray()[0]['total'];


            $already_withdraw = $db->table('payment_request p')->select('sum(p.amount) as total')->where(['user_id' => $id,"status"=>1])->get()->getResultArray()[0]['total'];
            $pending_withdraw = $db->table('payment_request p')->select('sum(p.amount) as total')->where(['user_id' => $id,"status"=>0])->get()->getResultArray()[0]['total'];
            $total_withdraw_request = $db->table('payment_request p')->select('count(p.id) as `total`')->where(['user_id' => $id])->get()->getResultArray()[0]['total'];




            $number_or_ratings = $db->table('partner_details p')->select('count(p.number_of_ratings) as `total`')->where(['id' => $id])->get()->getResultArray()[0]['total'];
            $income = $db->table('orders o')->select('count(o.id) as `total`')->where(['user_id' => $id])->where("created_at >= DATE(now()) - INTERVAL 7 DAY")->get()->getResultArray()[0]['total'];
            $symbol =   get_currency();
            $this->data['total_services'] = $total_services;
            $this->data['total_orders'] = $order_count[0]['total'];
            $this->data['total_balance'] =  number_format($total_balance, 2, ".", "");
            $this->data['total_ratings'] = $total_ratings;
            $this->data['total_review'] = $total_review;

            $this->data['number_of_ratings'] = $number_or_ratings;
            $this->data['currency'] = $symbol;
            $this->data['total_promocodes'] = $total_promocodes;
            $this->data['already_withdraw'] = $already_withdraw;

            $this->data['pending_withdraw'] = $pending_withdraw;
            $this->data['total_withdraw_request'] = $total_withdraw_request;
            $this->data['promocode_dates'] = $promocode_dates;
            $this->data['provider_total_earning_chart'] = $provider_total_earning_chart;
        
            $this->data['provider_already_withdraw_chart'] = $provider_already_withdraw_chart;
            $this->data['provider_pending_withdraw_chart'] = $provider_pending_withdraw_chart;
            $this->data['provider_withdraw_chart'] = $provider_withdraw_chart;


            $this->data['income'] = number_format($income, 2, ".", "");
            $this->data['title'] = 'Dashboard | Partner Panel';
            $this->data['main_page'] = 'dashboard';

            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }

    public function fetch_sales()
    {
        if (!$this->isLoggedIn) {
            return redirect('partner/login');
        } else {
            $sales[] = array();
            $db = \Config\Database::connect();

            $month_res = $db->table('orders')
                ->select('SUM(final_total) AS total_sale,DATE_FORMAT(created_at,"%b") AS month_name ')
                ->where('partner_id', $_SESSION['user_id'])
                ->where('status', 'completed')

                ->groupBy('year(CURDATE()),MONTH(created_at)')
                ->orderBy('year(CURDATE()),MONTH(created_at)')
                ->get()->getResultArray();

            $month_wise_sales['total_sale'] = array_map('intval', array_column($month_res, 'total_sale'));
            $month_wise_sales['month_name'] = array_column($month_res, 'month_name');

            $sales = $month_wise_sales;

         
            print_r(json_encode($sales));
        }
    }

    public function fetch_data()
    {
        $db = \Config\Database::connect();
        $id = $this->userId;
        $res = $db->table('categories as c')
            ->select('c.name as category,count(c.id) as counter')
            ->join('services s', 's.category_id=c.id ')
            ->where(['s.user_id' => $id, 's.status' => '1', 'c.status' => '1'])
            ->groupBy('c.id')
            ->get()->getResultArray();
        $response['category'] = array_column($res, 'category');
        $response['counter'] = array_column($res, 'counter');
        print_r(json_encode($response));
    }

    
    public function fetch_total_earning()
    {
        
        if (!$this->isLoggedIn) {
            return redirect('partner/login');
        } else {
            $id = $this->userId;
            $data=provider_total_earning_chart($id);
        
      
            print_r(($data));
            die;    
      
        }
    }
}
