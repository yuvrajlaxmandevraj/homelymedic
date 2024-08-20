<?php

namespace App\Models;

use CodeIgniter\Model;
use DateTime;

class Orders_model extends Model
{

    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $allowedFields = ['partner_id', 'user_id', 'city_id', 'city', 'total', 'promo_code', 'promo_discount', 'final_total', 'payment_method', 'admin_earnings', 'visiting_charges', 'partner_earnings', 'address_id', 'address', 'date_of_service', 'starting_time', 'ending_time', 'duration', 'status', 'remarks', 'payment_status', 'otp'];

    public function __construct()
    {
        $ionAuth = new \IonAuth\Libraries\IonAuth();
        $this->admin_id = ($ionAuth->isAdmin()) ? $ionAuth->user()->row()->id : 0;
        $this->ionAuth = new \IonAuth\Libraries\IonAuth();
    }

    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [], $where_in_key = '', $where_in_value = [], $addition_data = '', $download_invoice = false, $newUI = false, $is_provider = false)
    {





        if ($newUI == true || $newUI == 1) {



            $db      = \Config\Database::connect();
            $builder = $db->table('orders o');


            $multipleWhere = [];
            $bulkData = $rows = $tempRow = [];

            if (isset($_GET['limit'])) {
                $limit = $_GET['limit'];
            }


            if (isset($_GET['sort'])) {
                if ($_GET['sort'] == 'o.id') {
                    $sort = "o.id";
                } else {
                    $sort = $_GET['sort'];
                }
            }
            if (isset($_GET['order'])) {
                $order = $_GET['order'];
            }
            if (isset($_GET['offset']))
                $offset = $_GET['offset'];

            if ((isset($search) && !empty($search) && $search != "") || (isset($_GET['search']) && $_GET['search'] != '')) {
                $search = (isset($_GET['search']) && $_GET['search'] != '') ? $_GET['search'] : $search;
                $multipleWhere = [
                    '`o.id`' => $search,
                    '`o.user_id`' => $search,
                    '`o.partner_id`' => $search,
                    '`o.total`' => $search,
                    '`o.address`' => $search,
                    '`o.date_of_service`' => $search,
                    '`o.starting_time`' => $search,
                    '`o.ending_time`' => $search,
                    '`o.duration`' => $search,
                    '`o.status`' => $search,
                    '`o.remarks`' => $search,
                    '`up.username`' => $search,
                    '`u.username`' => $search,
                    '`os.service_title`' => $search,
                    '`os.status`' => $search,
                ];
            }






            $order_count = $builder->select('count(DISTINCT(o.id)) as total')
                ->join('order_services os', 'os.order_id=o.id')
                ->join('users u', 'u.id=o.user_id')
                ->join('users up', 'up.id=o.partner_id')
                ->join('partner_details pd', 'o.partner_id = pd.partner_id');
            // ->join('transactions t', 't.order_id = o.id');

            if (isset($_GET['order_status_filter']) && $_GET['order_status_filter'] != '') {
                // echo '1';
                $builder->where('o.status', $_GET['order_status_filter']);
            }

            if (isset($_GET['filter_date']) && $_GET['filter_date'] != '') {

                $builder->where('o.date_of_service', $_GET['filter_date']);
            }





            if (isset($where) && !empty($where)) {
                $builder->where($where);
            }
            if (isset($where_in_key) && !empty($wherwhere_in_key) && isset($where_in_value) && !empty($where_in_value)) {
                $builder->whereIn($where_in_key, $where_in_value);
            }
            if (isset($multipleWhere) && !empty($multipleWhere)) {
                $builder->groupStart();
                $builder->orLike($multipleWhere);
                $builder->groupEnd();
            }
            $order_count = $builder->get()->getResultArray();






            $total = $order_count[0]['total'];
            //  selecting actual data

            // $builder->select('o.*,t.status as payment_status,a.lattitude as order_lattitude,a.longitude as order_longitude ,pd.advance_booking_days,u.id as customer_id,u.username as user_name,u.image as user_image,u.phone as customer_no,u.latitude as 	latitude,u.longitude as longitude  ,up.image as provider_profile_image,u.email as customer_email,up.username as partner_name,up.phone as partner_no,u.balance as user_wallet, pd.company_name,o.visiting_charges,pd.address as partner_address')
            //     ->join('order_services os', 'os.order_id=o.id')
            //     ->join('users u', 'u.id=o.user_id')
            //     ->join('addresses a', 'a.id=o.address_id','left')
            //     ->join('users up', 'up.id=o.partner_id')
            //     ->join('partner_details pd', 'o.partner_id = pd.partner_id')
            //     ->join('transactions t', 't.order_id = o.id', 'left');


            $builder->select('o.*,t.status as payment_status,o.order_latitude as order_latitude,o.order_longitude as order_longitude ,pd.advance_booking_days,u.id as customer_id,u.username as user_name,u.image as user_image,u.phone as customer_no,u.latitude as 	latitude,u.longitude as longitude  ,up.image as provider_profile_image,u.email as customer_email,up.username as partner_name,up.phone as partner_no,u.balance as user_wallet, pd.company_name,o.visiting_charges,pd.address as partner_address')
                ->join('order_services os', 'os.order_id=o.id')
                ->join('users u', 'u.id=o.user_id')
                ->join('addresses a', 'a.id=o.address_id', 'left')
                ->join('users up', 'up.id=o.partner_id')
                ->join('partner_details pd', 'o.partner_id = pd.partner_id')
                ->join('transactions t', 't.order_id = o.id', 'left');

            if (isset($_GET['limit'])) {
                $limit = $_GET['limit'];
            }


            if (isset($_GET['sort'])) {
                if ($_GET['sort'] == 'o.id') {
                    $sort = "o.id";
                } else if ($_GET['sort'] == 'customer') {
                    $sort = "u.id";
                } else {
                    $sort = $_GET['sort'];
                }
            }
            if (isset($_GET['order'])) {
                $order = $_GET['order'];
            }
            if (isset($_GET['offset']))
                $offset = $_GET['offset'];
            if (isset($where) && !empty($where)) {
                $builder->where($where);
            }


            if (isset($where_in_key) && !empty($wherwhere_in_key) && isset($where_in_value) && !empty($where_in_value)) {
                $builder->whereIn($where_in_key, $where_in_value);
            }
            if (isset($multipleWhere) && !empty($multipleWhere)) {
                $builder->groupStart();
                $builder->orLike($multipleWhere);
                $builder->groupEnd();
            }
            if (isset($_GET['order_status_filter']) && $_GET['order_status_filter'] != '') {
                // echo '1';
                $builder->where('o.status', $_GET['order_status_filter']);
            }
            if (isset($_GET['filter_date']) && $_GET['filter_date'] != '') {

                $builder->where('o.date_of_service', $_GET['filter_date']);
            }

            if (isset($_POST['status']) && $_POST['status'] != '') {
                $builder->where('o.status', $_POST['status']);
            }


            $order_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->groupBy('o.id')->get()->getResultArray();

            $bulkData = array();
            $bulkData['total'] = $total;
            $rows = array();
            $tempRow = array();
            if (empty($order_record)) {
                $bulkData = array();
            } else {
                foreach ($order_record as $row) {
                    $builder = $db->table('order_services os');
                    $services = $builder->select('
                    os.id,
                    os.order_id,
                    os.service_id,
                    os.service_title,
                    os.tax_percentage,
                    os.discount_price,
                    os.tax_amount,
                    os.price,
                    os.quantity,
                    os.sub_total,
                    os.status,                
                    ,s.tags,s.duration,s.category_id,s.is_cancelable,s.cancelable_till,s.title,s.tax_type,s.tax_id,s.image,sr.rating,sr.comment,sr.images')
                        ->where('os.order_id', $row['id'])
                        ->join('services as s', 's.id=os.service_id', 'left')
                        ->join('services_ratings as sr', 'sr.service_id=os.service_id AND sr.user_id=' . $row["user_id"] . '', 'left')->get()->getResultArray();





                    $order_record['order_services'] = $services;





                    foreach ($order_record['order_services'] as $key => $os) {


                        $taxPercentageData = fetch_details('taxes', ['id' =>  $os['tax_id']], ['percentage']);
                        if (!empty($taxPercentageData)) {

                            $taxPercentage = $taxPercentageData[0]['percentage'];
                        } else {
                            $taxPercentage = 0;
                        }

                        if ($os['discount_price'] == "0") {
                            //   (str_replace(',','',$row['final_total']))
                            if ($os['tax_type'] == "excluded") {
                                $order_record['order_services'][$key]['price_with_tax']  = (str_replace(',', '', number_format(strval($os['price'] + ($os['price'] * ($taxPercentage) / 100)), 2)));
                                $order_record['order_services'][$key]['tax_value'] = (str_replace(',', '', number_format(((($os['price'] * ($taxPercentage) / 100))), 2)));
                                $order_record['order_services'][$key]['original_price_with_tax'] = (str_replace(',', '', number_format(strval($os['price'] + ($os['price'] * ($taxPercentage) / 100)), 2)));
                            } else {
                                $order_record['order_services'][$key]['price_with_tax']  = (str_replace(',', '', number_format(strval($os['price']), 2)));
                                $order_record['order_services'][$key]['tax_value'] = "";
                                $order_record['order_services'][$key]['original_price_with_tax'] = (str_replace(',', '', number_format(strval($os['price']), 2)));
                            }
                        } else {
                            if ($os['tax_type'] == "excluded") {
                                $order_record['order_services'][$key]['price_with_tax']  = (str_replace(',', '', number_format(strval($os['discount_price'] + ($os['discount_price'] * ($taxPercentage) / 100)), 2)));
                                $order_record['order_services'][$key]['tax_value'] = number_format(((($os['discount_price'] * ($taxPercentage) / 100))), 2);

                                // $order_record['order_services'][$key]['original_price_with_tax'] = number_format(strval($os['price'] + ($os['price'] * ($taxPercentage) / 100)),2);
                                $order_record['order_services'][$key]['original_price_with_tax'] = (str_replace(',', '', number_format(strval($os['price'] + ($os['price'] * ($taxPercentage) / 100)), 2)));
                            } else {
                                $order_record['order_services'][$key]['price_with_tax']  = (str_replace(',', '', number_format(strval($os['discount_price']), 2)));
                                $order_record['order_services'][$key]['tax_value'] = "";
                                // $order_record['order_services'][$key]['original_price_with_tax'] = number_format(strval($os['price']),2) ;
                                $order_record['order_services'][$key]['original_price_with_tax'] = (str_replace(',', '', number_format(strval($os['price']), 2)));
                            }
                        }
                    }



                    if ($from_app == false) {
                        $operations = '<a href="' . site_url('partner/orders/veiw_orders/' . $row['id']) . '" class="btn  btn-sm action-button p-2" title="view the order"><o class="material-symbols-outlined">
                        more_vert
                        </o> </a>';



                        if (($row['status'] == 'awaiting')) {
                            $status = " <div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>awaiting
                            </div>";
                        } elseif (($row['status'] == 'confirmed')) {
                            $status = " <div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2  bg-emerald-purple text-emerald-purple dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>confirmed
                            </div>";
                        } elseif (($row['status'] == 'rescheduled')) {
                            $status = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-blue text-emerald-blue dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>rescheduled
                            </div>";
                        } elseif (($row['status'] == 'cancelled')) {
                            $status = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>cancelled
                            </div>";
                        } elseif (($row['status'] == 'completed')) {
                            $status = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>completed
                            </div>";
                        } elseif (($row['status'] == 'started')) {
                            $status = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2  bg-emerald-grey text-emerald-grey dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>started
                            </div>";
                        } elseif (($row['status'] == 'pending')) {
                            $status = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2  bg-emerald-grey text-emerald-grey dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>pending
                            </div>";
                        } else {
                            $status = "status not defined";
                        }
                    } else {
                        $status = $row['status'];
                    }

                    $tax_amount = 0;



                    foreach ($order_record['order_services'] as $order_data) {

                        $tax_amount = ($order_data['tax_type'] == "excluded") ? number_format($tax_amount, 2) + ((($order_data['tax_amount']))  * $order_data['quantity']) : number_format($tax_amount, 2);
                    }


                    $s = [];
                    foreach ($order_record['order_services'] as $service_data) {
                        $array_ids =  fetch_details('services s', ['id' => $service_data['service_id']], 'is_cancelable');
                        foreach ($array_ids as $ids) {
                            array_push($s, $ids['is_cancelable']);
                        }
                    }
                    // $address_data = fetch_details('addresses', ['id' => $row["address_id"]], 'address,area,pincode,city,state,country');
                    // // $city_name = fetch_details('cities', ['id' => $row['city_id']], 'name');

                    // if (isset($address_data[0])) {
                    //     $res =  array_slice($address_data[0], 0, 2, true) +
                    //         array("city" => $address_data[0]['city']) +
                    //         array_slice($address_data[0], 2, count($address_data[0]) - 1, true);
                    //     $address = implode(",", $res);
                    // } else {
                    //     $address = "";
                    // }



                    if ($is_provider == true) {



                        if ((file_exists(FCPATH . 'public/backend/assets/profiles/' . $row['user_image']))) {



                            $row['user_image'] = base_url('public/backend/assets/profiles/' . $row['user_image']);
                        } else {
                            $row['user_image'] =  base_url("public/backend/assets/profiles/default.png");
                        }
                    } else {


                        if ((file_exists(FCPATH . '/public/uploads/users/partners/' . $row['provider_profile_image']))) {

                            $row['provider_profile_image'] =  base_url('public/uploads/users/partners/' . $row['provider_profile_image']);
                        } else {
                            $row['provider_profile_image'] =  base_url("public/backend/assets/profiles/default.png");
                        }
                    }
                    $tempRow['id'] = $row['id'];
                    $tempRow['customer'] = $row['user_name'];
                    $tempRow['customer_id'] = $row['customer_id'];

                    $tempRow['customer_latitude'] = $row['latitude'];
                    $tempRow['customer_longitude'] = $row['longitude'];
                    $tempRow['latitude'] = $row['order_latitude'];
                    $tempRow['longitude'] = $row['order_longitude'];


                    $tempRow['advance_booking_days'] = $row['advance_booking_days'];
                    $tempRow['customer_no'] = $row['customer_no'];
                    $tempRow['customer_email'] = $row['customer_email'];
                    $tempRow['user_wallet'] = $row['user_wallet'];
                    $tempRow['payment_method'] = $row['payment_method'];
                    $tempRow['payment_status'] = $row['payment_status'];
                    $tempRow['partner'] = $row['partner_name'];
                    $tempRow['profile_image'] = ($is_provider == true) ? $row['user_image'] : $row['provider_profile_image'];
                    $tempRow['user_id'] = $row['user_id'];
                    $tempRow['partner_id'] = $row['partner_id'];
                    $tempRow['city_id'] = $row['city'];
                    $tempRow['total'] = (str_replace(',', '', number_format($row['total'], 2)));
                    // $tempRow['tax_percentage'] = $tax['tax'];
                    $tempRow['tax_amount'] = strval(number_format($tax_amount, 2));
                    $tempRow['promo_code'] = $row['promo_code'];
                    $tempRow['promo_discount'] = $row['promo_discount'];
                    // $tempRow['final_total'] = strval(number_format($row['final_total'],2));
                    $tempRow['final_total'] = ceil(str_replace(',', '', $row['final_total']));


                    $tempRow['admin_earnings'] = $row['admin_earnings'];
                    $tempRow['partner_earnings'] = $row['partner_earnings'];
                    $tempRow['address_id'] = $row['address_id'];
                    // $tempRow['address'] = $address;

                    $tempRow['address'] = $row['address'];
                    $tempRow['date_of_service'] = date("d-M-Y", strtotime($row['date_of_service']));
                    $tempRow['starting_time'] = date("h:i A", strtotime($row['starting_time']));
                    $tempRow['ending_time'] = date("h:i A", strtotime($row['ending_time']));
                    $tempRow['duration'] = $row['duration'];
                    $tempRow['partner_address'] = $row['partner_address'];
                    $tempRow['partner_no'] = $row['partner_no'];
                    // $tempRow['service_image']="https://demandium-admin.6amtech.com/admin/provider/details/f0503769-804a-4e78-b1ad-634f23fffb33?web_page=overvie";

                    $tempRow['service_image'] = "frg";


                    if (in_array(0, $s)) {
                        $tempRow['is_cancelable'] = 0;
                    } else {
                        // Assuming the order date and start time are provided
                        $order_date = strtotime($order_record[0]['date_of_service']); // Order date in Unix timestamp format
                        $start_time = strtotime($order_record[0]['starting_time']); // Start time in Unix timestamp format

                        // Set the cancellation time window to 10 minutes
                        $cancellation_window = (intval($order_record['order_services'][0]['cancelable_till'])); // Cancellation window in minutes

                        $order_timestamp = strtotime(date('Y-m-d', $order_date) . ' ' . date('H:i:s', $start_time));
                        $cancellation_time = $order_timestamp - ($cancellation_window * 60);
                        $current_time = time();
                        if ($current_time <= $cancellation_time) {
                            // Action is cancelable
                            $tempRow['is_cancelable'] = 1;
                        } else {
                            // Action is not cancelable
                            $tempRow['is_cancelable'] = 0;
                        }
                    }



                    $tempRow['status'] = $status;
                    $tempRow['remarks'] = $row['remarks'];
                    $tempRow['created_at'] =  date("d-M-Y h:i A", strtotime($row['created_at']));
                    $tempRow['company_name'] = $row['company_name'];



                    $tempRow['visiting_charges'] = (str_replace(',', '', number_format($row['visiting_charges'], 2)));





                    $tempRow['services'] = $order_record['order_services'];

                    $tempRow['invoice_no'] = 'INV-' . $row['id'];


                    if (!$from_app) {
                        $tempRow['operations'] = $operations;
                        unset($tempRow['updated_at']);
                    }
                    $rows[] = $tempRow;
                }
            }

            $bulkData['rows'] = $rows;






            if ($from_app) {

                $data['total'] = $total;
                $data['data'] = $rows;
                return $data;
            } else {

                return json_encode($bulkData);
            }
        }

        $db      = \Config\Database::connect();
        $builder = $db->table('orders o');


        $multipleWhere = [];
        $bulkData = $rows = $tempRow = [];

        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }



        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'o.id') {
                $sort = "o.id";
            } else if ($_GET['sort'] == 'customer') {
                $sort = "u.id";
            } else {
                $sort = $_GET['sort'];
            }
        }


        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }




        if (isset($_GET['offset']))
            $offset = $_GET['offset'];



        if ((isset($search) && !empty($search) && $search != "") || (isset($_GET['search']) && $_GET['search'] != '')) {
            $search = (isset($_GET['search']) && $_GET['search'] != '') ? $_GET['search'] : $search;
            $multipleWhere = [
                '`o.id`' => $search,
                '`o.user_id`' => $search,
                '`o.partner_id`' => $search,
                '`o.total`' => $search,
                '`o.address`' => $search,
                '`o.date_of_service`' => $search,
                '`o.starting_time`' => $search,
                '`o.ending_time`' => $search,
                '`o.duration`' => $search,
                '`o.status`' => $search,
                '`o.remarks`' => $search,
                '`up.username`' => $search,
                '`u.username`' => $search,
                '`os.service_title`' => $search,
                '`os.status`' => $search,
            ];
        }






        $order_count = $builder->select('count(DISTINCT(o.id)) as total')
            ->join('order_services os', 'os.order_id=o.id')
            ->join('users u', 'u.id=o.user_id')
            ->join('users up', 'up.id=o.partner_id')
            ->join('partner_details pd', 'o.partner_id = pd.partner_id');
        // ->join('transactions t', 't.order_id = o.id');

        if (isset($_GET['filter_date']) && $_GET['filter_date'] != '') {

            $builder->where('o.created_at', $_GET['filter_date']);
        }



        if (isset($_GET['order_status_filter']) && $_GET['order_status_filter'] != '') {
            // echo '1';
            $builder->where('o.status', $_GET['order_status_filter']);
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }


        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'o.id') {
                $sort = "o.id";
            } else {
                $sort = $_GET['sort'];
            }
        }



        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }



        if (isset($_GET['offset']))
            $offset = $_GET['offset'];

        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($where_in_key) && !empty($wherwhere_in_key) && isset($where_in_value) && !empty($where_in_value)) {
            $builder->whereIn($where_in_key, $where_in_value);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        $order_count = $builder->get()->getResultArray();







        $total = $order_count[0]['total'];
      
        $builder
            ->select('o.*, t.status as payment_status, pd.advance_booking_days,
            u.id as customer_id, u.username as user_name, u.image as user_image, u.phone as customer_no,
            u.latitude as latitude, u.longitude as longitude, partner_subscriptions.name as subscription_name,partner_subscriptions.id as partner_subscription_id,partner_subscriptions.status as subscription_status,
            up.image as provider_profile_image, u.email as customer_email, up.username as partner_name,
            up.phone as partner_no, u.balance as user_wallet, up.latitude as partner_latitude,
            up.longitude as partner_longitude, pd.company_name, o.visiting_charges, pd.address as partner_address')
            ->join('order_services os', 'os.order_id = o.id')
            ->join('users u', 'u.id = o.user_id')
            ->join('users up', 'up.id = o.partner_id')
            ->join('partner_details pd', 'o.partner_id = pd.partner_id')
            ->join('(SELECT partner_id, MAX(created_at) AS latest_subscription_date 
                FROM partner_subscriptions 
                GROUP BY partner_id) latest_subscriptions', 'latest_subscriptions.partner_id = pd.partner_id')
            ->join('partner_subscriptions', 'partner_subscriptions.partner_id = latest_subscriptions.partner_id AND partner_subscriptions.created_at = latest_subscriptions.latest_subscription_date', 'left')
            ->join('transactions t', 't.order_id = o.id', 'left');


        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($where_in_key) && !empty($wherwhere_in_key) && isset($where_in_value) && !empty($where_in_value)) {
            $builder->whereIn($where_in_key, $where_in_value);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        if (isset($_GET['order_status_filter']) && $_GET['order_status_filter'] != '') {
            // echo '1';
            $builder->where('o.status', $_GET['order_status_filter']);
        }

        if (isset($_GET['order_provider_filter']) && $_GET['order_provider_filter'] != '') {

            $builder->where('o.partner_id', $_GET['order_provider_filter']);
        }



        if (isset($_POST['status']) && $_POST['status'] != '') {
            $builder->where('o.status', $_POST['status']);
        }
        $builder->where('o.parent_id', null);
        $order_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->groupBy('o.id')->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        if (empty($order_record)) {
            $bulkData = array();
        } else {
            foreach ($order_record as $row) {



                $builder = $db->table('order_services os');
                $services = $builder->select('
                os.id,
                os.order_id,
                os.service_id,
                os.service_title,
                os.tax_percentage,
                os.discount_price,
                os.tax_amount,
                os.price,
                os.quantity,
                os.sub_total,
                os.status,                
                ,s.tags,s.duration,s.category_id,s.is_cancelable,s.cancelable_till,s.title,s.tax_type,s.tax_id,s.image,sr.rating,sr.comment,sr.images')
                    ->where('os.order_id', $row['id'])
                    ->join('services as s', 's.id=os.service_id', 'left')
                    ->join('services_ratings as sr', 'sr.service_id=os.service_id AND sr.user_id=' . $row["user_id"] . '', 'left')->get()->getResultArray();





                $order_record['order_services'] = $services;


                foreach ($order_record['order_services'] as $key => $os) {

                    

                    $taxPercentageData = fetch_details('taxes', ['id' =>  $os['tax_id']], ['percentage']);
                    if (!empty($taxPercentageData)) {

                        $taxPercentage = $taxPercentageData[0]['percentage'];
                    } else {
                        $taxPercentage = 0;
                    }

                    if ($os['discount_price'] == "0") {
                        //   (str_replace(',','',$row['final_total']))
                        if ($os['tax_type'] == "excluded") {
                            $order_record['order_services'][$key]['price_with_tax']  = strval(str_replace(',', '', number_format(strval($os['price'] + ($os['price'] * ($taxPercentage) / 100)), 2)));
                            $order_record['order_services'][$key]['tax_value'] = strval(str_replace(',', '', number_format(((($os['price'] * ($taxPercentage) / 100))), 2)));
                            $order_record['order_services'][$key]['original_price_with_tax'] = strval(str_replace(',', '', number_format(strval($os['price'] + ($os['price'] * ($taxPercentage) / 100)), 2)));
                        } else {
                            $order_record['order_services'][$key]['price_with_tax']  = strval(str_replace(',', '', number_format(strval($os['price']), 2)));
                            $order_record['order_services'][$key]['tax_value'] = "";
                            $order_record['order_services'][$key]['original_price_with_tax'] = strval(str_replace(',', '', number_format(strval($os['price']), 2)));
                        }
                    } else {
                        if ($os['tax_type'] == "excluded") {
                            $order_record['order_services'][$key]['price_with_tax']  = strval(str_replace(',', '', number_format(strval($os['discount_price'] + ($os['discount_price'] * ($taxPercentage) / 100)), 2)));
                            $order_record['order_services'][$key]['tax_value'] = number_format(((($os['discount_price'] * ($taxPercentage) / 100))), 2);
                            $order_record['order_services'][$key]['original_price_with_tax'] = strval(str_replace(',', '', number_format(strval($os['price'] + ($os['price'] * ($taxPercentage) / 100)), 2)));
                        } else {
                            $order_record['order_services'][$key]['price_with_tax']  = strval(str_replace(',', '', number_format(strval($os['discount_price']), 2)));
                            $order_record['order_services'][$key]['tax_value'] = "";
                            $order_record['order_services'][$key]['original_price_with_tax'] = strval(str_replace(',', '', number_format(strval($os['price']), 2)));
                        }
                    }
                    
                    
                    if ((file_exists(FCPATH . $os['image']))) {

                      $order_record['order_services'][$key]['image'] =  (!empty($os['image']))?base_url($os['image']): base_url("public/backend/assets/profiles/default.png");
                    } else {
                    $order_record['order_services'][$key]['image'] =  base_url("public/backend/assets/profiles/default.png");
                    }
                }


                if ($from_app == false) {
                    // $user1 = fetch_details('users', ["phone" => $_SESSION['identity']],);
                    $db      = \Config\Database::connect();
                    $builder = $db->table('users u');
                    $builder->select('u.*,ug.group_id')
                        ->join('users_groups ug', 'ug.user_id = u.id')
                        ->whereIn('ug.group_id', [1, 3])
                        ->where(['phone' => $_SESSION['identity']]);
                    $user1 = $builder->get()->getResultArray();

                    $permissions = get_permission($user1[0]['id']);
                }
                $operations = '';
                if ($from_app == false) {
                    if ($from_app == false) {

                        $operations = '<div class="dropdown">
    <a class="" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <button class="btn btn-secondary   btn-sm px-3"> <i class="fas fa-ellipsis-v "></i></button>
    </a>
    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">';

                        // if ($permissions['read']['orders'] == 1) {
                        //     $operations .= '<a class="dropdown-item" href="orders/veiw_orders/' . $row['id'] . '">  <i class="fa fa-eye text-primary mr-1" aria-hidden="true"></i> View the booking</a>';
                        // }


                        if ($permissions['read']['orders'] == 1) {
                            $base_url = base_url();
                            // print_r($base_url);
                            // die;
                            if ($this->ionAuth->isAdmin()) {
                                $operations .= '<a class="dropdown-item" href="' . $base_url . '/admin/orders/veiw_orders/'.$row['id'] .'"><i class="fa fa-eye text-primary mr-1" aria-hidden="true"></i> View the booking</a>';

                            }else{
                                
                                $operations .= '<a class="dropdown-item" href="' . $base_url . '/partner/orders/veiw_orders/'.$row['id'] .'"><i class="fa fa-eye text-primary mr-1" aria-hidden="true"></i> View the booking</a>';

                            }

                        }
                                             
                        if ($row['status'] != 'cancelled' && $permissions['read']['orders'] == 1) {
                            $operations .= '<a class="dropdown-item" href="orders/invoice/' . $row['id'] . '"> <i class="fa fa-receipt text-success mr-1" ></i> Invoice</a>';
                        }

                        if ($permissions['delete']['orders'] == 1) {
                            $operations .= '<a class="dropdown-item delete_orders" data-id="' . $row['id'] . '" onclick="order_id(this)" data-toggle="modal" data-target="#delete_modal"> <i class="fa fa-trash text-danger mr-1"></i> Delete booking</a>';
                        }

                        $operations .= '</div></div>';
                    }
                    if (($row['status'] == 'awaiting')) {
                        $status =   "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-warning text-emerald-warning dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Awaiting
                        </div>";
                    } elseif (($row['status'] == 'confirmed')) {
                        $status = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-blue text-emerald-blue dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Confirmed
                        </div>";
                    } elseif (($row['status'] == 'rescheduled')) {
                        $status = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-grey text-emerald-grey dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Rescheduled
                        </div>";
                    } elseif (($row['status'] == 'cancelled')) {
                        $status = " <div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Cancelled
                        </div>";
                    } elseif (($row['status'] == 'completed')) {
                        $status = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Completed
                        </div>";
                    } elseif (($row['status'] == 'pending')) {
                        $status = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-grey text-emerald-grey dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Pending
                        </div>";
                    } elseif (($row['status'] == 'started')) {
                        $status = "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-warning text-emerald-warning dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>started
                        </div>";
                    } else {
                        $status = "status not defined";
                    }
                } else {
                    $status = $row['status'];
                }

                $tax_amount = 0;



                foreach ($order_record['order_services'] as $order_data) {


                    $tax_amount = ($order_data['tax_type'] == "excluded") ? number_format($tax_amount, 2) + ((($order_data['tax_amount']))  * $order_data['quantity']) : number_format($tax_amount, 2);
                }


                $s = [];
                foreach ($order_record['order_services'] as $service_data) {
                    $array_ids =  fetch_details('services s', ['id' => $service_data['service_id']], 'is_cancelable');
                    foreach ($array_ids as $ids) {
                        array_push($s, $ids['is_cancelable']);
                    }
                }
                $address_data = fetch_details('addresses', ['id' => $row["address_id"]], 'address,area,pincode,city,state,country');
                if (isset($address_data[0])) {
                    $res =  array_slice($address_data[0], 0, 2, true) +
                        array("city" => $address_data[0]['city']) +
                        array_slice($address_data[0], 2, count($address_data[0]) - 1, true);
                    $address = implode(",", $res);
                } else {
                    $address = "";
                }


                if ($is_provider == true) {


                    if ((file_exists(FCPATH . 'public/backend/assets/profiles/' . $row['user_image']))) {



                        $row['user_image'] = base_url('public/backend/assets/profiles/' . $row['user_image']);
                    } else {
                        $row['user_image'] =  base_url("public/backend/assets/profiles/default.png");
                    }
                } else {




                    if ((file_exists(FCPATH . $row['provider_profile_image']))) {

                        $row['provider_profile_image'] =  base_url($row['provider_profile_image']);
                    } else {
                        $row['provider_profile_image'] =  base_url("public/backend/assets/profiles/default.png");
                    }
                }




                $tempRow['id'] = $row['id'];
                $tempRow['customer'] = $row['user_name'];
                $tempRow['customer_id'] = $row['customer_id'];
                $tempRow['latitude'] = $row['order_latitude'];
                $tempRow['longitude'] = $row['order_longitude'];
                $tempRow['partner_latitude'] = $row['partner_latitude'];
                $tempRow['partner_longitude'] = $row['partner_longitude'];
                $tempRow['advance_booking_days'] = $row['advance_booking_days'];
                $tempRow['customer_no'] = $row['customer_no'];
                $tempRow['customer_email'] = $row['customer_email'];
                $tempRow['user_wallet'] = $row['user_wallet'];
                $tempRow['payment_method'] = $row['payment_method'];
                $tempRow['payment_status'] = $row['payment_status'];
                $tempRow['partner'] = $row['partner_name'];
                $tempRow['profile_image'] = ($is_provider == true) ? $row['user_image'] : $row['provider_profile_image'];
                $tempRow['user_id'] = $row['user_id'];
                $tempRow['partner_id'] = $row['partner_id'];
                $tempRow['city_id'] = $row['city'];
                $tempRow['total'] = (str_replace(',', '', number_format($row['total'], 2)));
                $tempRow['tax_amount'] = strval(number_format($tax_amount, 2));
                $tempRow['promo_code'] = $row['promo_code'];
                $tempRow['promo_discount'] = $row['promo_discount'];
                $tempRow['final_total'] = (str_replace(',', '', $row['final_total']));
                $tempRow['admin_earnings'] = $row['admin_earnings'];
                $tempRow['partner_earnings'] = $row['partner_earnings'];
                $tempRow['address_id'] = $row['address_id'];
                $tempRow['address'] = $row['address'];
                // $tempRow['date_of_service'] = date("d-M-Y", strtotime($row['date_of_service']));
                if (!$from_app) {

                    $tempRow['date_of_service'] =  format_date($row['date_of_service'], 'd-m-Y ');
                } else {
                    $tempRow['date_of_service'] = $row['date_of_service'];
                }

                // $tempRow['starting_time'] = date("h:i A", strtotime($row['starting_time']));
                // $tempRow['ending_time'] = date("h:i A", strtotime($row['ending_time']));

                $tempRow['starting_time'] = ($row['starting_time']);
                $tempRow['ending_time'] = ($row['ending_time']);



                $tempRow['duration'] = $row['duration'];
                $tempRow['partner_address'] = $row['partner_address'];
                $tempRow['partner_no'] = $row['partner_no'];
                $tempRow['service_image'] = "frg";
                $tempRow['otp'] = $row['otp'];

                if (!empty($row['work_started_proof'])) {
                    $row['work_started_proof'] = array_map(function ($data) {
                        return base_url($data);
                    }, json_decode(($row['work_started_proof']), true));
                }



                if (!empty($row['work_completed_proof'])) {
                    $row['work_completed_proof'] = array_map(function ($data) {
                        return base_url($data);
                    }, json_decode(($row['work_completed_proof']), true));
                }


                $tempRow['work_started_proof'] = !empty($row['work_started_proof']) ? ($row['work_started_proof']) : [];
                $tempRow['work_completed_proof'] = !empty($row['work_completed_proof']) ? ($row['work_completed_proof']) : [];


                if($row['subscription_status']=="active"){
                    $tempRow['is_reorder_allowed']="1";
                }else{
                    $tempRow['is_reorder_allowed']="0";

                }

                $tempRow['status'] = $status;
                $tempRow['remarks'] = $row['remarks'];



                // $tempRow['created_at'] =  date("d-M-Y h:i A", strtotime($row['created_at']));
                $tempRow['created_at'] =  $row['created_at'];

                $tempRow['company_name'] = $row['company_name'];
                // $tempRow['visiting_charges'] = number_format($row['visiting_charges'], 2);

                $tempRow['visiting_charges'] = (str_replace(',', '', $row['visiting_charges']));
                $tempRow['services'] = $order_record['order_services'];
                $settings = \get_settings('general_settings', true);

                $tempRow['is_otp_enalble'] = (!empty($settings['otp_system'])) ? $settings['otp_system'] : "0";

                // $maxCancelableMinutes = 0; // Initialize to a low value

                // foreach ($order_record['order_services'] as $service) {
                //     // Convert 'cancelable_till' value from minutes to an integer
                //     $cancelableTillMinutes = (int)$service['cancelable_till'];

                //     // Update the maximum 'cancelable_till' value if the current service has a higher value
                //     if ($cancelableTillMinutes > $maxCancelableMinutes) {
                //         $maxCancelableMinutes = $cancelableTillMinutes;
                //     }

                //     // If any service is not cancellable, set $allServicesCancelable to false
                //     if ($service['is_cancelable'] != 1) {
                //         $allServicesCancelable = false;
                //     }
                // }

                // // Calculate the current time and starting time of the order
                // $currentTime = strtotime('now');
                // $startingTime = strtotime($row['date_of_service'] . ' ' . $row['starting_time']);

                // // Calculate the remaining time until the highest 'cancelable_till' becomes non-cancelable in minutes
                // $remainingMinutes = ($maxCancelableMinutes - ($startingTime - $currentTime)) / 60;

                // // If the remaining time is greater than or equal to 0, set is_cancelable to 1; otherwise, set it to 0
                // if ($remainingMinutes >= 0 && $allServicesCancelable && $row['status'] !== 'completed') {
                //     $tempRow['is_cancelable'] = 1;
                // } else {
                //     $tempRow['is_cancelable'] = 0;
                // }


                // Initialize outer is_cancelable to 1
                $outerIsCancelable = 1;

                // Find the highest cancelable_till among all services
                $highestCancelableTill = 0;
                foreach ($order_record["order_services"] as $service) {
                    $cancelableTill = (int)$service["cancelable_till"];
                    if ($cancelableTill > $highestCancelableTill) {
                        $highestCancelableTill = $cancelableTill;
                    }

                    // Check if any inner is_cancelable is 0, if so, set outerIsCancelable to 0
                    if ($service["is_cancelable"] == 0) {
                        $outerIsCancelable = 0;
                    }
                }

                // Check the order status
                if ($row["status"] == "completed") {
                    $outerIsCancelable = 0;
                }

                // Calculate the time for the condition (6:30 PM - highestCancelableTill minutes)
                $currentDateTime = new \DateTime("now"); // Use \DateTime to reference PHP's built-in class
                $targetDateTime = new \DateTime($row["date_of_service"] . " " . $row["starting_time"]); // Use \DateTime here as well
                $targetDateTime->sub(new \DateInterval("PT" . $highestCancelableTill . "M")); // Use \DateInterval



                // Check if the current time is greater than or equal to the calculated time (5:30 PM)
                if ($currentDateTime >= $targetDateTime) {
                    $outerIsCancelable = 0;
                }
                $tempRow['is_cancelable'] = $outerIsCancelable;


                $tempRow['new_start_time_with_date'] =  format_date($row['date_of_service'], 'd-m-Y') . ' ' . format_date(($row['starting_time']), 'h:i A');

                $temprow_for_suborder = [];


                $builder_sub_order = $db->table('orders o');
                $builder_sub_order->where('o.parent_id', $row['id']);
                $sub_order_record = $builder_sub_order->orderBy($sort, $order)->limit($limit, $offset)->groupBy('o.id')->get()->getResultArray();


                // $tempRow['new_end_time_with_date'] = date("d-M-Y", strtotime($row['date_of_service'])) . ' ' . date("h:i A", strtotime($row['ending_time']));
                $tempRow['new_end_time_with_date'] =  format_date($row['date_of_service'], 'd-m-Y') . ' ' . format_date(($row['ending_time']), 'h:i A');



                if (empty($sub_order_record)) {
                    $tempRow['multiple_days_booking'] = [];
                }



                foreach ($sub_order_record as $key => $sub_row) {


                    if (!$from_app) {

                        $temprow_for_suborder[$key]['multiple_day_date_of_service'] = date("d-M-Y", strtotime($sub_row['date_of_service']));
                        $temprow_for_suborder[$key]['multiple_day_starting_time'] = date("h:i A", strtotime($sub_row['starting_time']));
                        $temprow_for_suborder[$key]['multiple_ending_time'] = date("h:i A", strtotime($sub_row['ending_time']));;
                    } else {
                        $temprow_for_suborder[$key]['multiple_day_date_of_service'] = $sub_row['date_of_service'];
                        $temprow_for_suborder[$key]['multiple_day_starting_time'] = $sub_row['starting_time'];
                        $temprow_for_suborder[$key]['multiple_ending_time'] = $sub_row['ending_time'];
                    }

                    $tempRow['multiple_days_booking'] = $temprow_for_suborder;
                }


                if (!empty($sub_order_record)) {
                    $tempRow['new_end_time_with_date'] = date("d-M-Y", strtotime($sub_order_record[0]['date_of_service'])) . ' ' . date("h:i A", strtotime($sub_order_record[0]['ending_time']));
                }



                $tempRow['invoice_no'] = 'INV-' . $row['id'];


                if (!$from_app) {
                    $tempRow['operations'] = $operations;
                    unset($tempRow['updated_at']);
                }
                $rows[] = $tempRow;
            }
        }
        $bulkData['rows'] = $rows;







        if ($from_app) {

            $data['total'] = $total;
            $data['data'] = $rows;
            return $data;
        } else {

            return json_encode($bulkData);
        }
    }

    public function invoice($order_id)
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('orders o');
        $tempRow = array();
        // $builder->select('o.*,u.username as customer,u.phone as customer_no,u.email as customer_email,up.username as partner_name,up.phone as partner_no,u.balance as user_wallet,
        // pd.visiting_charges,pd.address,pd.company_name,pd.tax_name,pd.tax_number')
        //     ->join('order_services os', 'os.order_id=o.id', 'left')
        //     ->join('users u', 'u.id=o.user_id', 'left')
        //     ->join('services s', 's.id=os.service_id', 'left')
        //     ->join('users up', 'up.id=o.partner_id', 'left')
        //     ->join('partner_details pd', 'o.partner_id = pd.partner_id', 'left');
        // $builder->where('o.id', $order_id)->where("os.status != 'cancelled'");

        $builder->select('o.*,u.username as customer,u.phone as customer_no,u.email as customer_email,up.username as partner_name,up.phone as partner_no,u.balance as user_wallet,
        o.visiting_charges,pd.address,pd.company_name,pd.tax_name,pd.tax_number')
            ->join('order_services os', 'os.order_id=o.id', 'left')
            ->join('users u', 'u.id=o.user_id', 'left')
            ->join('services s', 's.id=os.service_id', 'left')
            ->join('users up', 'up.id=o.partner_id', 'left')
            ->join('partner_details pd', 'o.partner_id = pd.partner_id', 'left');
        $builder->where('o.id', $order_id)->where("os.status != 'cancelled'");

        $order_record = $builder->get()->getResultArray();


        foreach ($order_record as $row) {
            $builder = $db->table('order_services os');
            $services = $builder->select('os.*,s.tags,s.tax_type,s.duration,s.category_id,s.image as service_image')
                ->where('os.order_id', $row['id'])
                // ->where('os.status', 'cancelled')
                ->join('services as s', 's.id=os.service_id', 'left')->get()->getResultArray();
            $tempRow['order'] = $order_record[0];
            $tempRow['order']['services'] = $services;
        }

        return $tempRow;
    }

    public function ordered_services_list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'o.id', $order = 'DESC', $where = [], $where_in_key = '', $where_in_value = [])
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('order_services os');

        $multipleWhere = [];
        $condition = $bulkData = $rows = $tempRow = [];

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];

        if ((isset($search) && !empty($search) && $search != "") || (isset($_GET['search']) && $_GET['search'] != '')) {
            $search = (isset($_GET['search']) && $_GET['search'] != '') ? $_GET['search'] : $search;
            $multipleWhere = [
                '`os.id`' => $search,
                '`os.order_id`' => $search,
                '`os.service_id`' => $search,
                '`os.service_title`' => $search,
                '`os.quantity`' => $search,
                '`os.status`' => $search
            ];
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        $sort = "id";
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'id') {
                $sort = "id";
            } else {
                $sort = $_GET['sort'];
            }
        }
        $order = "ASC";
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }

        if ($from_app) {
            $where['status'] = 1;
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }

        $builder->select('COUNT(os.id) as `total` ')
            ->join('services s', 's.id = os.service_id', 'left');
        $order_count = $builder->get()->getResultArray();
        $total = $order_count[0]['total'];

        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }



        $builder->select('os.*,s.is_cancelable, s.cancelable_till')
            ->join('services s', 's.id = os.service_id', 'left');
        $taxes = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();

        $bulkData = array();
        $bulkData['total'] = $total;



        $rows = array();
        $tempRow = array();


        foreach ($taxes as $row) {

            $tempRow['id'] = $row['id'];
            $tempRow['order_id'] = $row['order_id'];
            $tempRow['service_id'] = $row['service_id'];
            $tempRow['service_title'] = $row['service_title'];
            $tempRow['tax_percentage'] = $row['tax_percentage'];
            $tempRow['tax_amount'] = $row['tax_amount'];
            $tempRow['price'] = $row['price'];
            $tempRow['quantity'] = $row['quantity'];
            $tempRow['sub_total'] = $row['sub_total'];

            // service table section
            $tempRow['is_cancelable'] = ($row['is_cancelable'] == 1) ?
                "<span class='badge badge-success'>Yes</span>" : "<span class='badge badge-danger'>No</span>";

            $tempRow['cancelable_till'] = ($row['cancelable_till'] != '') ? $row['cancelable_till'] : 'Not cancelable';
            // 
            $tempRow['status'] = $row['status'];
            if ($row['is_cancelable'] == 1) {
                if ($row['status'] == 'completed') {
                    $tempRow['operations'] = '';
                } else if ($row['status'] == 'cancelled') {
                    $tempRow['operations'] = '';
                } else {
                    $tempRow['operations'] = '
                    <button type="button" class="btn btn-danger btn-sm cancel_order" title="Cancel Order">
                        <i class="fas fa-times"></i>
                    </button>
                    ';

                    // $tempRow['operations'] .= '
                    //     <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#change_order_status" 
                    //     title="Reschedule Order">
                    //       <i class="fas fa-clock"></i>
                    //     </button>
                    // ';
                }
            } else {
                $tempRow['operations'] = '-';
            }


            $rows[] = $tempRow;
        }
        if ($from_app) {
            // if request from app return array 
            $data['total'] = $total;
            $data['data'] = $rows;
            return $data;
        } else {
            // else return json
            $bulkData['rows'] = $rows;

            return json_encode($bulkData);
        }
    }
}
