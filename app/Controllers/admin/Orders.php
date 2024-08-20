<?php

namespace App\Controllers\admin;

use App\Models\Orders_model;
use App\Models\Transaction_model;
use App\Models\Users_model;

class Orders extends Admin
{
    public $orders, $creator_id, $transaction, $user_model;
    public function __construct()
    {
        parent::__construct();
        $this->orders = new Orders_model();
        $this->creator_id = $this->userId;
        $this->transaction = new Transaction_model();
        $this->user_model = new Users_model();
        helper(['form', 'url', 'upload']);
        $this->superadmin = $this->session->get('email');
        $this->db = \Config\Database::connect();
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Booking | Admin Panel';
            $this->data['main_page'] = 'orders';
            $partner_data = $this->db->table('users u')
                ->select('u.id,u.username,pd.company_name,pd.number_of_members')
                ->join('partner_details pd', 'pd.partner_id = u.id')
                ->where('is_approved', '1')
                ->get()->getResultArray();

            $this->data['partner_name'] = $partner_data;
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function list()
    {
        $orders_model = new Orders_model();
        return $orders_model->list();
    }

    public function view_orders()
    {



        $uri = service('uri');
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $order_id = $uri->getSegments()[3];
            $orders  = fetch_details('orders', ['id' => $order_id]);

            $sub_orders = fetch_details('orders', ['parent_id' => $order_id]);


            $this->data['title'] = $order_id . ' ID - Booking Details | Admin Panel';
            $this->data['main_page'] = 'order_details';
            if (isset($orders) && empty($orders)) {
                return redirect('admin/orders');
            } else {
                $orders = $orders[0];
            }
            $partner_id = $orders['partner_id'];
            $user_id = $orders['user_id'];

            // this is for partner data
            $partner  = fetch_details('partner_details', ['partner_id' => $partner_id])[0];


            $partner_personal_data  = fetch_details('users', ['id' => $partner_id])[0];


            // this is for customer data
            $customer  = fetch_details('users', ['id' => $user_id])[0];
            $payment  = fetch_details('transactions', ['order_id' => $order_id]);
            // this section is for service related data 
            $order_services = fetch_details('order_services', ['order_id' => $order_id, 'status !=  cancelled'])[0];


            $service_id = $order_services['service_id'];
            $service = fetch_details('services', ['id' => $service_id]);
            if (!empty($service)) {
                $service = $service[0];
            } else {
                $service = [];
            }


            $where = "o.id = $order_id";

            $order_details = $this->orders->list(true, '', 10, 0, '', '', $where);
            $where = "u.id = $user_id";
            $currency = get_settings('general_settings', true);
            $tax = get_settings('system_tax_settings', true);
            $this->data['currency'] = $currency['currency'];
            $this->data['tax'] = $tax['tax'];
            $this->data['order'] = $orders;
            $this->data['order_services'] = $order_services;

            $this->data['partner'] = $partner;
            $this->data['order_details'] = $order_details['data'][0];
            $this->data['personal_data'] = $partner_personal_data;
            $this->data['customer'] = $customer;
            $this->data['payment'] = $payment;
            $this->data['service'] = $service;
            $this->data['sub_order'] = $sub_orders;

            // echo "<pre/>";
            // print_r($this->data);
            // die;
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function view_user()
    {
        $uri = service('uri');
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $order_id = $uri->getSegments()[3];

            $orders  = fetch_details('orders', ['id' => $order_id]);
            if (isset($orders) && empty($orders)) {
                return redirect('admin/orders');
            } else {
                $orders = $orders[0];
            }

            $user_id = $orders['user_id'];
            $where['u.id'] = $user_id;
            $users  = fetch_details('users', ['id' => $user_id]);
            $user_details = $this->user_model->get_user($user_id, '');
            return json_encode($user_details);
        } else {
            return redirect('admin/login');
        }
    }

    public function view_payment_details()
    {
        $uri = service('uri');
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $order_id = $uri->getSegments()[3];
            $orders  = fetch_details('orders', ['id' => $order_id]);
            if (isset($orders) && empty($orders)) {
                return redirect('admin/orders');
            } else {
                $orders = $orders[0];
            }

            $order_id = $orders['id'];

            $db      = \Config\Database::connect();
            $where['t.order_id']  = $order_id;
            $payment_details  = $this->transaction->list_transactions(false, '', 10, 0, 't.id', 'desc', $where);
            return $payment_details;
        } else {
            return redirect('admin/login');
        }
    }
    public function customer_details($user_id = "")
    {
        $uri = service('uri');
        $order_id = $uri->getSegments()[3];

        $order_details = fetch_details('orders', ['id' => $order_id], ['user_id', 'city', 'address_id'])[0];
        $user_id = $order_details['user_id'];
        // $city_id = $order_details['city_id'];

        $address_id = $order_details['address_id'];
        //name , contact, email, address

        $db      = \Config\Database::connect();
        $builder = $db->table('orders o');
        $count = $builder->select('COUNT(o.id) as total')
            ->join('users u', "u.id = $user_id")
            // ->join('cities c', "c.id = $city_id")
            ->join('addresses a', "a.id =  $address_id")
            ->where('o.id', $order_id)->get()->getResultArray();
        $total = $count[0]['total'];
        $tempRow = array();
        $data =  $builder->select('u.username, u.email, u.phone, a.type, a.address,a.city')
            ->join('users u', "u.id = $user_id")
            // ->join('cities c', "c.id = $city_id")
            ->join('addresses a', "a.id =  $address_id")
            ->where('o.id', $order_id)->get()->getResultArray();
        $rows = [];
        foreach ($data as $row) {
            $tempRow['name'] = $row['username'];
            $tempRow['email'] = ($row['email'] != '') ? $row['email'] : '-';
            $tempRow['phone'] = $row['phone'];
            $tempRow['city_name'] = $row['city'];
            $tempRow['type'] = $row['type'];
            $tempRow['address'] = $row['address'];

            $rows[] = $tempRow;
        }
        $bulkData['total'] = $total;
        $bulkData['rows'] = $rows;
        return json_encode($bulkData);
    }

    public function payment_details($order_id = "")
    {
        $uri = service('uri');
        $order_id = $uri->getSegments()[3];

        $db      = \Config\Database::connect();
        $builder = $db->table('transactions t');
        $count = $builder->select('COUNT(t.id) as total')
            ->where('t.order_id', $order_id)->get()->getResultArray();
        $total = $count[0]['total'];
        $tempRow = array();
        $data =  $builder->select('t.*')
            ->where('t.order_id', $order_id)->get()->getResultArray();
        $rows = [];
        foreach ($data as $row) {
            $tempRow['transaction_type'] = $row['transaction_type'];
            $tempRow['payment_method'] = $row['type'];
            $tempRow['txn_id'] = $row['txn_id'];
            $tempRow['amount'] = $row['amount'];
            $tempRow['status'] = $row['status'];
            $tempRow['currency_code'] = $row['currency_code'];
            $tempRow['message'] = $row['message'];
            $tempRow['transaction_date'] = $row['transaction_date'];

            $rows[] = $tempRow;
        }
        $bulkData['total'] = $total;
        $bulkData['rows'] = $rows;
        return json_encode($bulkData);
    }

    public function partner_details($order_id = "")
    {
        $uri = service('uri');
        $order_id = $uri->getSegments()[3];

        $partner_id = fetch_details('orders', ['id' => $order_id], ['partner_id'])[0];

        $db      = \Config\Database::connect();
        $builder = $db->table('partner_details pd');
        $count = $builder->select('COUNT(pd.id) as total')
            ->where('pd.partner_id', $partner_id)->get()->getResultArray();
        $total = $count[0]['total'];
        $tempRow = array();
        $data =  $builder->select('pd.*, u.username, u.email, u.phone, u.image')
            ->join('users u', 'u.id = pd.partner_id')
            ->where('pd.partner_id', $partner_id)->get()->getResultArray();
        $rows = [];
        // print_r($data);
        foreach ($data as $row) {
            $profile = '<a  href="' . base_url($row['image'])  . '" data-lightbox="image-1"><img height="80px" class="rounded-circle" src="' . base_url($row['image']) . '" alt=""></a>';
            $tempRow['user_image'] = $profile;
            $tempRow['name'] = $row['username'];
            $tempRow['email'] = $row['email'];
            $tempRow['phone'] = $row['phone'];
            $tempRow['company_name'] = $row['company_name'];
            $tempRow['about'] = $row['about'];

            $rows[] = $tempRow;
        }
        $bulkData['total'] = $total;
        $bulkData['rows'] = $rows;
        return json_encode($bulkData);
    }

    public function delete_orders()
    {


        if ($this->superadmin == "rajasthantech.info@gmail.com") {
            defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
        } else {

            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $response['error'] = true;
                $response['message'] = DEMO_MODE_ERROR;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                return $this->response->setJSON($response);
            }
        }
        // $permission = is_permitted($this->creator_id, 'delete', 'order');


        // if ($permission) {

        if ($this->isLoggedIn) {
            $order_id = $this->request->getPost('id');
            $db      = \Config\Database::connect();

            $builder = $db->table('orders')->where('id', $order_id)
                ->orWhere('parent_id', $order_id)
                ->delete();

            // $builder = $db->table('orders')->delete(['id' => $order_id]);
            $builder = $db->table('order_services')->delete(['order_id' => $order_id]);


            if ($builder) {
                $response = [
                    'error' => false,
                    'message' => 'order deleted successfully',
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => 'oredr does not exist!',
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
            }
            return $this->response->setJSON($response);
        } else {
            return redirect('admin/login');
        }
        // } else {
        //     $response = [
        //         'error' => true,
        //         'message' => "Sorry! you're not permitted to take this action",
        //         'csrfName' => csrf_token(),
        //         'csrfHash' => csrf_hash(),
        //         'data' => []
        //     ];
        //     return $this->response->setJSON($response);
        // }
    }
    public function view_details()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'View Booking | Admin Panel';
            $this->data['main_page'] = 'order_details';

            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function invoice()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $db      = \Config\Database::connect();

            $this->data['title'] = 'View Invoice | Admin Panel';
            $this->data['main_page'] = 'invoice';
            $uri = service('uri');
            $order_id = $uri->getSegments()[3];
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
                // ->join('partner_details pd', 'pd.partner_id=u.id ')
                // ->join('cities c', 'c.id = u.city_id')
                ->where('u.id', $user_id)
                ->get()->getResultArray();

            $data = get_settings('general_settings', true);
            $this->data['currency'] = $data['currency'];
            $this->data['order'] = $order_details;
            $this->data['partner_details'] = $partner_details[0];
            $this->data['user_details'] = $user_details[0];



            // echo"<pre>";
            // print_r($this->data['order']);
            // die;



            // return  view('backend/admin/pages/invoice_from_api', $this->data);
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function invoice_table()
    {


        if ($this->isLoggedIn && $this->userIsAdmin) {

            $uri = service('uri');
            $order_id = $uri->getSegments()[3];
            $orders  = fetch_details('orders', ['id' => $order_id]);
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

                    $rows[$i] = [
                        'service_title' => ucwords($service['service_title']),
                        'price' => $currency . number_format($service['price']),
                        'discount' => ($service['discount_price'] == 0) ? "0" : $currency . (($service['price'] - $service['discount_price'])),
                        'net_amount' => ($service['discount_price'] != 0) ? $currency . number_format($service['discount_price']) : $currency . ($service['price']),
                        'tax' => ($service['tax_type'] == "excluded") ? $service['tax_percentage'] . '%' : '0%',
                        'tax_amount' => ($service['tax_type'] == "excluded") ? $currency . ($service['tax_amount']) : '$0',
                        'quantity' => ucwords($service['quantity']),
                        'subtotal' => $currency . (number_format(($service['sub_total']), 2))
                    ];
                    $i++;
                }


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
                    'quantity' => "<strong class='text-dark'>Total</strong>",
                    'subtotal' => "<strong class='text-dark '>" . $currency . strval(str_replace(',', '', number_format(strval((($orders['total']))), 2))) . "</strong>",

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

                if ($orders['visiting_charges'] != "0") {
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
                }
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

                array_push($rows, $empty_row);
                array_push($rows, $row);
                // array_push($rows, $tax);
                if ($orders['visiting_charges'] != "0") {

                    array_push($rows, $visiting_charges);
                }

                array_push($rows, $promo_code_discount);
                array_push($rows, $final_total);
                $array['total'] = $total;
                $array['rows'] = $rows;


                return json_encode($array);
            } else {
                return redirect('admin/login');
            }
        }
    }

    public function change_order_status()
    {



        if ($this->isLoggedIn && $this->userIsAdmin) {
            if ($this->superadmin == "rajasthantech.info@gmail.com") {
                defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
            } else {

                if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                    $response['error'] = true;
                    $response['message'] = DEMO_MODE_ERROR;
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    return $this->response->setJSON($response);
                }
            }
            $uri = service('uri');

            $order_id = $this->request->getPost('order_id');
            $status = $this->request->getPost('status');
            $otp = $this->request->getPost('otp');
            $uploadedFiles = $this->request->getFiles('filepond');



            $date = $this->request->getPost('rescheduled_date');


            $selected_time = $this->request->getPost('reschedule');

            $partner_id = fetch_details('orders', ['id' => $order_id], ['partner_id'])[0]['partner_id'];




            if ($status == "rescheduled" && $selected_time == "") {
                $response = [
                    'error' => true,
                    'message' => ' Please select reschedule timing!',
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }

            $orders = fetch_details('orders', ['id' => $order_id]);
            // print_r($orders);
            // die;
            $service_total_duration = 0;
            $service_duration = 0;
            foreach ($orders as $row) {
                // print_r($row);
                //     die;
                $service_duration = ($row['duration']);
                $service_total_duration = $service_total_duration + $service_duration;
            }


            // $is_provider_available = check_availability($partner_id, $date, $selected_time);
            $availability =  checkPartnerAvailability($partner_id, $date . ' ' . $selected_time, $service_total_duration, $date, $selected_time);



            if ($status == "rescheduled" && $availability['message'] = "Slot is available at this time") {
                $response = validate_status($order_id, $status, $date, $selected_time);
                return json_encode($response);
            } else {

                if ($status == "completed") {
                    $response = validate_status($order_id, $status, '', '', $otp, isset($uploadedFiles) ? $uploadedFiles : "");
                } elseif ($status == "started") {



                    $response = validate_status($order_id, $status, '', '', '', isset($uploadedFiles) ? $uploadedFiles : "");
                } else {
                    $response =  validate_status($order_id, $status);
                }



                return json_encode($response);
            }
        } else {
            return redirect('admin/login');
        }
    }

    public function upload_file()
    {

        // print_r( $_FILES);
        // die;
        // echo 'function is called ..</br>';
        try {
            $order_id = $this->request->getPost('order_id');
            $status = $this->request->getPost('status');
            $imagefile = $this->request->getFiles();
            // echo 'order Id :: '.$order_id;
            // echo 'status '.$status;


            $is_completed = $this->request->getPost('is_completed');
            // echo "is_comepleted - ".$is_completed;

            $db      = \Config\Database::connect();
            $builder = $db->table('orders');
            $builder->select('status,payment_method,user_id,otp')->where('id', $order_id);
            $active_status1 = $builder->get()->getResultArray();


            $active_status = (isset($active_status1[0]['status'])) ? $active_status1[0]['status'] : "";



            if ($active_status == $status) {
                $response['error'] = true;
                $response['message'] = "You can't update the same status again";
                $response['data'] = array();
                return $response;
            }


            if ($active_status == 'cancelled' || $active_status == 'completed') {
                $response['error'] = true;
                $response['message'] = "You can't update status once item cancelled OR completed";
                $response['data'] = array();
                return $response;
            }
            $work_started_images = [];
            $imagefile = $_FILES('documents');

            foreach ($_FILES['documents'] as $key => $img) {


                if ($img->isValid() && !$img->hasMoved()) {

                    $newName = $img->getName();


                    echo $newName;

                    $fileNameParts = explode('.', $newName);
                    $ext = end($fileNameParts);

                    $newName = 'data_' . uniqid() . "." . $ext;

                    $work_started_images[$key]  = "/public/backend/assets/provider_work_evidence/" . $newName;
                    $img->move('./public/backend/assets/provider_work_evidence/', $newName);
                } else {
                    echo "file is not valid";
                }
            }




            if (isset($is_completed)) {
                $update = update_details(
                    [

                        'work_completed_proof' => json_encode((object)$work_started_images),

                    ],
                    ['id' => $order_id],
                    'orders',
                    false
                );
            } else {
                $update = update_details(
                    [

                        'work_started_proof' => json_encode((object)$work_started_images),

                    ],
                    ['id' => $order_id],
                    'orders',
                    false
                );
            }

            // print_r("update ".$update);
            if ($update) {
                $response['error'] = False;
                $response['message'] = "Success";
                $response['data'] = array();
                return $response;
            } else {
                $response['error'] = true;
                $response['message'] = "Falied";
                $response['data'] = array();
                return $response;
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = $th;
            return $this->response->setJSON($response);
        }
    }

    public function view_ordered_services()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Booking | Admin Panel';
            $this->data['main_page'] = 'view_ordered_services';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function view_ordered_services_list()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'desc';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $this->orders = new Orders_model();
            $data = $this->orders->ordered_services_list(false, $search, $limit, $offset, $sort, $order);
            return $data;
        } else {
            return redirect('admin/login');
        }
    }

    public function cancel_order_service()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {


            if ($this->superadmin == "rajasthantech.info@gmail.com") {
                defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
            } else {

                if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                    $response['error'] = true;
                    $response['message'] = DEMO_MODE_ERROR;
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    return $this->response->setJSON($response);
                }
            }
            $id = $this->request->getPost('id');
            $service_id = $this->request->getPost('service_id');

            $service_details =  fetch_details('services', ['id' => $service_id]);
            $order_service_data = fetch_details('order_services', ['id' => $id]);
            $order_id = $order_service_data[0]['order_id'];
            $order_data = fetch_details('orders', ['id' => $order_id]);
            if (!empty($service_details)) {
                $date_of_service  = $order_service_data[0]['created_at'];
                $starting_time = (!empty($order_data)) ? $order_data[0]['starting_time'] : '';
                $cancelable_before = $service_details[0]['cancelable_till'];
                // $data =  check_cancelable($date_of_service, $starting_time, $cancelable_before);
                $change = update_details(['status' => "cancelled"], ['id' => $id], 'order_services');
                if ($change) {
                    $sub_total = $order_service_data[0]['sub_total'];
                    $amount = floatval($order_data[0]['total']) -  floatval($order_service_data[0]['sub_total']);

                    $final_total = $order_data[0]['final_total'] - floatval($order_service_data[0]['sub_total']);
                    $promo_code_discount = ($order_data[0]['promo_discount'] != '' || $order_data[0]['promo_discount'] > 0) ? $order_data[0]['promo_discount'] : '';

                    $customer_id = $order_data[0]['user_id'];

                    if ($promo_code_discount != '' && $promo_code_discount > 0) {
                        $discountable_amount =  ($final_total * $promo_code_discount) / 100;
                        $final_total = $final_total - $discountable_amount;
                    }

                    $change =  update_details(['total' => $amount, 'final_total' => $final_total], ['id' => $order_id], 'orders');


                    if ($change) {
                        $refund_process = process_service_refund($order_id, $service_id, 'cancelled', $customer_id,  $sub_total);
                        // $response = [
                        //     'error' => false,
                        //     'message' => 'order status changed',
                        //     'csrfName' => csrf_token(),
                        //     'csrfHash' => csrf_hash(),
                        //     'data' => []
                        // ];
                        return $this->response->setJSON($refund_process);
                    } else {
                        $response = [
                            'error' => true,
                            'message' => 'could not change order status',
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => []
                        ];
                        return $this->response->setJSON($response);;
                    }
                }
                // if ($data) {
                // } else {
                //     $response = [
                //         'error' => true,
                //         'message' => 'this order is no longer cancelable',
                //         'csrfName' => csrf_token(),
                //         'csrfHash' => csrf_hash(),
                //         'data' => []
                //     ];
                //     return $this->response->setJSON($response);;
                // }
            }
        } else {
            return redirect('admin/login');
        }
    }

    public function get_slots()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {

            $order_id = $this->request->getPost('id');
            $date = $this->request->getPost('date');

            $partner_id =  fetch_details('orders', ['id' => $order_id], ['partner_id'])[0];

            $slots =  get_available_slots($partner_id, $date);
            return $this->response->setJSON($slots);
        } else {
            return redirect('admin/login');
        }
    }
}
