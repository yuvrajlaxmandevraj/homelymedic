<?php

namespace App\Controllers\admin;

use App\Models\City_model;
use App\Models\Partner_subscription_model;
use App\Models\Subscription_model;

class Subscription extends Admin
{
    public $cities,  $validation, $db;
    public function __construct()
    {
        parent::__construct();
        $this->subscription = new Subscription_model();
        $this->validation = \Config\Services::validation();
        $this->db      = \Config\Database::connect();
        $this->superadmin = $this->session->get('email');
        $this->partner_Subscription = new Partner_subscription_model();
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Subscription | Admin Panel';
            $this->data['main_page'] = 'subscription';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }

    public function add_ons_index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Add Ons | Admin Panel';
            $this->data['main_page'] = 'add_on';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }



    public function add_subscription()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Add Subscription | Admin Panel';
            $this->data['main_page'] = 'add_subscription';
            $tax_data = fetch_details('taxes', ['status' => '1'], ['id', 'title', 'percentage']);
            $this->data['tax_data'] = $tax_data;
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }

    public function edit_subscription_page()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {

            helper('function');
            $uri = service('uri');
            $subscription_id = $uri->getSegments()[3];
            $subscription_data = fetch_details('subscriptions', ['id' => $subscription_id]);
            $this->data['title'] = 'Edit Subscription | Admin Panel';
            $this->data['main_page'] = 'edit_subscription';
            $this->data['subscription_data'] = $subscription_data;
            $tax_data = fetch_details('taxes', ['status' => '1'], ['id', 'title', 'percentage']);
            $this->data['tax_data'] = $tax_data;

            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }

    public function edit_subscription()
    {




        if ($this->isLoggedIn && $this->userIsAdmin) {
            $price = $this->request->getPost('price');

            $this->validation->setRules(
                [
                    'name' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter name"
                        ]
                    ],
                    'description' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter  Description",
                        ]
                    ],
                    'price' => [
                        "rules" => 'required|numeric',
                        "errors" => [
                            "required" => "Please enter price",
                            "numeric" => "Please enter numeric value for price"
                        ]
                    ],
                    'discount_price' => [
                        "rules" => 'required|numeric',
                        "errors" => [
                            "required" => "Please enter discounted price",
                            "numeric" => "Please enter numeric value for discounted price",
                           
                        ]
                    ],


                ],
            );
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors  = $this->validation->getErrors();
                $response['error'] = true;
                $response['message'] = $errors;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['data'] = [];
                return $this->response->setJSON($response);
            }

            $discount_price = $this->request->getPost('discount_price');
            $price = $this->request->getPost('price');
            if ($discount_price == 0 && $price == 0) {
            } elseif ($discount_price >= $price && $discount_price == $price) {

                $response = [
                    'error' => true,
                    'message' => "discount price can not be higher than or equal to the price",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }


            // print_r($discount_price);
            // die;

            if ((isset($_POST["order_type"]))  && $_POST['order_type'] == "limited") {

                $order_type = "limited";
            } else {
                $order_type = "unlimited";
            }
            if ($order_type == "limited" && $this->request->getVar('max_order') == "") {
                $response = [
                    'error' => true,
                    'message' => "Please Add Maximum number of order ",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }

            // if ((isset($_POST["service_type"])) && $_POST['service_type'] == "limited") {

            //     $service_type = "limited";
            // } else {
            //     $service_type = "unlimited";
            // }


            // if ($service_type == "limited" && $this->request->getVar('max_service') == "") {
            //     $response = [
            //         'error' => true,
            //         'message' => "Please Add Maximum number of service ",
            //         'csrfName' => csrf_token(),
            //         'csrfHash' => csrf_hash(),
            //         'data' => []
            //     ];
            //     return $this->response->setJSON($response);
            // }


            if ((isset($_POST["commission_type"])) && ($_POST["commission_type"] == "yes")) {
                $commission_type = "yes";
            } else {
                $commission_type = "no";
            }

            if (($_POST["duration_type"] != "unlimited")) {
                $duration = $this->request->getVar('duration');
            } else {
                $duration = "unlimited";
            }


            if ((isset($_POST["publish"]))  && ($_POST["publish"]) == "on") {
                $publish = "1";
            } else {
                $publish = "0";
            }



            if ((isset($_POST["status"])) && ($_POST['status']) == "on") {
                $status = "1";
            } else {
                $status = "0";
            }

            if (($commission_type == "yes") && (($this->request->getVar('threshold') == "") || ($this->request->getVar('percentage') == ""))) {
                $response = [
                    'error' => true,
                    'message' => "Please Add commission fields.",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $subscription = [
                'name' => $this->removeScript($this->request->getVar('name')),
                'description' => $this->removeScript($this->request->getVar('description')),
                'duration' => $duration,
                'price' => $price,
                'discount_price' => $discount_price,
                'publish' => $publish,
                'order_type' => $order_type,
                'max_order_limit' => $this->request->getVar('max_order'),
                // 'service_type' => $service_type,
                'service_type' => "unlimited",

                'max_service_limit' => $this->request->getVar('max_service'),
                'tax_type' => $this->request->getVar('tax_type'),
                'tax_id' => $this->request->getVar('tax_id'),
                'is_commision' => $commission_type,
                'commission_threshold' => $this->request->getVar('threshold'),
                'commission_percentage' => $this->request->getVar('percentage'),
                'status' => $status,
            ];


            // print_r($subscription);
            // die;

            $subscription_id = $this->request->getPost('subscription_id');

            if ($this->subscription->update($subscription_id, $subscription)) {

                $response = [
                    'error' => false,
                    'message' => "Subscription Update successfully!",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => "Subscription can not be saved!",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return redirect('admin/subscription/');
                return $this->response->setJSON($response);
            }
        } else {
            return redirect('unauthorised');
        }
    }

    public function delete_subscription()
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

            $builder = $this->db->table('subscriptions')->delete(['id' => $id]);

            if ($builder) {
                $response = [
                    'error' => false,
                    'message' => "success in deleting the subscription",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => "Unsuccessful in deleting subscription",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } else {
            return redirect('partner/login');
        }
    }


    public function add_store_subscription()
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



        if ($this->isLoggedIn && $this->userIsAdmin) {
            $price = $this->request->getPost('price');

            $this->validation->setRules(
                [
                    'name' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter name"
                        ]
                    ],
                    'description' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter  Description",
                        ]
                    ],
                    'price' => [
                        "rules" => 'required|numeric',
                        "errors" => [
                            "required" => "Please enter price",
                            "numeric" => "Please enter numeric value for price"
                        ]
                    ],
                    'discount_price' => [
                        "rules" => 'required|numeric',
                        "errors" => [
                            "required" => "Please enter discounted price",
                            "numeric" => "Please enter numeric value for discounted price",
                      
                        ]
                    ],
                    // 'duration' => [
                    //     "rules" => 'required',
                    //     "errors" => [
                    //         "required" => "Please enter duration to perform task",

                    //     ]
                    // ],
                ],
            );
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors  = $this->validation->getErrors();
                $response['error'] = true;
                $response['message'] = $errors;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['data'] = [];
                return $this->response->setJSON($response);
            }

            $discount_price = $this->request->getPost('discount_price');
            $price = $this->request->getPost('price');


            if ($discount_price == 0 && $price == 0) {
            } elseif ($discount_price >= $price && $discount_price == $price) {

                $response = [
                    'error' => true,
                    'message' => "discount price can not be higher than or equal to the price",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }


            // print_r($discount_price);
            // die;


            if ($_POST['order_type'] == "limited") {
                $order_type = "limited";
            } else {
                $order_type = "unlimited";
            }
            if ($order_type == "limited" && $this->request->getVar('max_order') == "") {
                $response = [
                    'error' => true,
                    'message' => "Please Add Maximum number of order ",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }

            // if ($_POST['service_type'] == "limited") {

            //     $service_type = "limited";
            // } else {
            //     $service_type = "unlimited";
            // }



            if ($_POST['duration_type'] == "limited") {
                $duration_type = "limited";
            } else {
                $duration_type = "unlimited";
            }

            if ($duration_type == "limited" && ($this->request->getVar('duration') == "") || $this->request->getVar('duration') == 0) {
                $response = [
                    'error' => true,
                    'message' => "Please Add Duration ",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }


            if ($duration_type == "limited") {
                $duartion = $this->request->getVar('duration');
            } else {
                $duartion = "unlimited";
            }

            if ($order_type == "limited" && $this->request->getVar('max_order') == "") {
                $response = [
                    'error' => true,
                    'message' => "Please Add Maximum number of order ",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }



            if (($_POST["commission_type"]) == "yes") {
                $commission_type = "yes";
            } else {
                $commission_type = "no";
            }


            if (!empty($_POST["publish"]) && ($_POST["publish"]) == "on") {
                $publish = "1";
            } else {
                $publish = "0";
            }


            if (!empty($_POST["status"]) && ($_POST["status"]) == "on") {

                $status = "1";
            } else {
                $status = "0";
            }

            if (($commission_type == "yes") && (($this->request->getVar('threshold') == "") || ($this->request->getVar('percentage') == ""))) {
                $response = [
                    'error' => true,
                    'message' => "Please Add commission fields.",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $subscription = [
                'name' => $this->removeScript($this->request->getVar('name')),
                'description' => $this->removeScript($this->request->getVar('description')),
                'duration' => $duartion,
                'price' => $price,
                'discount_price' => $discount_price,
                'publish' => $publish,
                'order_type' => $order_type,
                'max_order_limit' => (!empty($this->request->getVar('max_order'))) ?  $this->request->getVar('max_order') : 0,
                // 'service_type' => $service_type,
                'service_type' => "limited",
                'max_service_limit' => (!empty($this->request->getVar('max_service'))) ? $this->request->getVar('max_service') : 0,
                'tax_type' => $this->request->getVar('tax_type'),
                'tax_id' => $this->request->getVar('tax_id'),
                'is_commision' => $commission_type,
                'commission_threshold' => (!empty($this->request->getVar('threshold'))) ? $this->request->getVar('threshold') : 0,
                'commission_percentage' => (!empty($this->request->getVar('percentage'))) ? $this->request->getVar('percentage') : 0,
                'status' => $status,
            ];




            if ($this->subscription->save($subscription)) {

                $response = [
                    'error' => false,
                    'message' => "Subscription saved successfully!",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];


                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => "Subscription can not be saved!",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } else {
            return redirect('unauthorised');
        }
    }

    public function list()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';

        print_r(json_encode($this->subscription->list(false, $search, $limit, $offset, $sort, $order)));
    }

    public function add_on_create_page()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Add Ons | Admin Panel';
            $this->data['main_page'] = 'create_add_ons';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }


    public function subscriber_list()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Subscriber List | Admin Panel';
            $this->data['main_page'] = 'subscriber_list';
            $db      = \Config\Database::connect();
            $totalSubscriptionCount = $db->table('partner_subscriptions')->countAll();
            $activeSubscriptionCount = $db->table('partner_subscriptions')
                ->where('status', 'active')
                ->countAllResults();
            $expiredSubscriptionCount = $db->table('partner_subscriptions')
                ->where('status', 'deactive')
                ->countAllResults();
            $expiringSoonSubscriptionCount = $db->table('partner_subscriptions')
                ->where('status', 'active')
                ->where('expiry_date <=', date('Y-m-d', strtotime('+7 days')))
                ->countAllResults();
            $this->data['totalSubscriptionCount'] = $totalSubscriptionCount;
            $this->data['activeSubscriptionCount'] = $activeSubscriptionCount;
            $this->data['expiredSubscriptionCount'] = $expiredSubscriptionCount;
            $this->data['expiringSoonSubscriptionCount'] = $expiringSoonSubscriptionCount;


            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }



    public function partner_subscription_list()
    {
        $this->data['title'] = 'Subscriber List | Provider Panel';

        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        print_r(json_encode($this->partner_Subscription->subscriber_list(false, $search, $limit, $offset, $sort, $order)));
    }
}
