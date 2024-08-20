<?php

namespace App\Controllers\admin;

use App\Models\System_user_model;
use App\Models\user_permissions_model;
use App\Models\Users_model;
use IonAuth\Models\IonAuthModel;

class system_users extends Admin
{
    public   $validation, $system_users, $db,  $ionAuth, $user_permissions, $users;
    public function __construct()
    {
        parent::__construct();
        helper(['form', 'url']);
        $this->system_users = new System_user_model();
        $this->validation = \Config\Services::validation();
        $this->db      = \Config\Database::connect();
        $this->ionAuth = new \IonAuth\Libraries\IonAuth();
        $this->user_permissions = new user_permissions_model();
        $this->users = new Users_model();
        $this->superadmin = $this->session->get('email');
    }
    public function index()
    {
        
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'System Users | Admin Panel';
            $this->data['main_page'] = 'system_users';
            $this->data['categories_name'] = fetch_details('categories', [], ['id', 'name']);
            $this->data['users'] = fetch_details('users', [], ['id', 'username']);
            $this->data['notification'] = fetch_details('notifications');
            $edemand = new \Config\Edemand;
            $permissions = $edemand->permissions;
            $this->data['permissions'] =  $permissions;

            return view('backend/admin/template', $this->data);
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
        $data = $this->system_users->list(false, $search, $limit, $offset, $sort, $order);
       
        return $this->system_users->list(false, $search, $limit, $offset, $sort, $order);
    }

    public function deactivate_user()
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
            $user_id = $this->request->getPost('user_id');
            $operation =  $this->ionAuth->deactivate($user_id);

            if ($operation) {
                $response = [
                    'error' => false,
                    'message' => "Successfully Deactivated",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => "unsuccessful attempt to disable the user",
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

    public function activate_user()
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
            $user_id = $this->request->getPost('user_id');
            $operation =  $this->ionAuth->activate($user_id);

            if ($operation) {
                $response = [
                    'error' => false,
                    'message' => "successfully activated",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => "unsuccessful attempt to disable the user",
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

    public function delete_user()
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




            $user_id = $this->request->getPost('user_id');



            if ($user_id == $this->userId) {

                $response = [
                    'error' => true,
                    'message' => "Can not delete",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            $builder_user_permisison = $this->db->table('user_permissions');
            $delete_user_permisison =  $builder_user_permisison->delete(['user_id' => $user_id]);


            $builder_user = $this->db->table('users');
            $delete_user =  $builder_user->delete(['id' => $user_id]);




            if ($delete_user_permisison && $delete_user) {
                $response = [
                    'error' => false,
                    'message' => "Success in delete user",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                if ($user_id == $this->userId) {
                    $this->ionAuth->logout();
                    return redirect()->to('/admin/login')->withCookies();
                }
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => "unsuccessful attempt to delete the user",
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

    public function add_user()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            
            $edemand = new \Config\Edemand;
            $permissions = $edemand->permissions;
            $this->data['title'] = 'Add System User | Admin Panel';
            $this->data['main_page'] = 'add_system_user';
            $builder = $this->db->table('users u')->select('u.id, username, ug.group_id')->join('users_groups ug', 'ug.user_id = u.id')->where('ug.group_id', 1)->get()->getResultArray();
            $users = $builder;
            $this->data['permissions'] =  $permissions;
            $this->data['users'] = $users;
            $this->data['notification'] = fetch_details('notifications');
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }
    public function permit()
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


            if ($this->request->getPost('user_type') == 'existing_user') {
                $this->validation->setRules(
                    [
                        'user' => 'required',
                        'role' => 'required',
                    ]
                );

                if (!$this->validation->withRequest($this->request)->run()) {
                    $errors  = $this->validation->getErrors();
                    $response['error'] = true;
                    foreach ($errors as $e) {
                        $response['message'] = $e;
                    }
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    $response['data'] = [];
                    return $this->response->setJSON($response);
                }
                $user_id = $this->request->getPost('user');
                $check_user  = exists(['user_id' => $user_id], 'user_permissions');
                if ($check_user) {
                    $response['error'] = true;
                    $response['message'] = "this user was already selected for permissions";
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    $response['data'] = [];
                    return $this->response->setJSON($response);
                }
                if ($this->request->getPost('user') == "default") {
                    $response['error'] = true;
                    $response['message'] = "Please select user";
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    $response['data'] = [];
                    return $this->response->setJSON($response);
                }
                if ($this->request->getPost('role') == "default") {
                    $response['error'] = true;
                    $response['message'] = "Please select role  ";
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    $response['data'] = [];
                    return $this->response->setJSON($response);
                }
            } else {
                $user_name = $this->request->getPost('new_user_name');
                $mobile = $this->request->getPost('phone');
                $email = $this->request->getPost('mail');
                $password  = $this->request->getPost('password');
                $confirm_password  = $this->request->getPost('confirm_password');
                $mobile_data  = fetch_details('users', ['phone' => $mobile]);
                $email_data = fetch_details('users', ['email' => $email]);

                if (!empty($mobile_data) && $mobile_data[0]['phone']) {
                    $response['error'] = true;
                    $response['message'] = "Phone number already exists please use another one";
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    $response['data'] = [];
                    return $this->response->setJSON($response);
                }
                if (!empty($email_data) && $email_data[0]['email']) {
                    $response['error'] = true;
                    $response['message'] = "Email already exists please use another one";
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    $response['data'] = [];
                    return $this->response->setJSON($response);
                }
                $ion_auth = new IonAuthModel();

                $data = [
                    'username' => $user_name,
                    'phone' => $mobile,
                    'email' => $email,
                    'active'=>isset($_POST['is_approved']) ? 1 : 0,
                    'password' => $ion_auth->hashPassword($password)
                ];
                $insert_id =  $this->users->save($data);
                $user_id = $this->users->getInsertID();
                $user_group = [
                    'user_id' => $user_id,
                    'group_id' => 1,

                ];
                insert_details($user_group, 'users_groups');
            }



            // create permission section
            $orders = ($this->request->getPost('orders_create') == "true") ? 1 : 0;
            $category = ($this->request->getPost('categories_create') == "true") ? 1 : 0;
            $subscription = ($this->request->getPost('subscription_create') == "true") ? 1 : 0;
            $sliders = ($this->request->getPost('sliders_create') == "true") ? 1 : 0;
            $tax = ($this->request->getPost('tax_create') == "true") ? 1 : 0;
            $service = ($this->request->getPost('services_create') == "true") ? 1 : 0;
            $promo_code = ($this->request->getPost('promo_code_create') == "true") ? 1 : 0;
            $featured_section = ($this->request->getPost('featured_section_create') == "true") ? 1 : 0;
            $partner = ($this->request->getPost('partner_create') == "true") ? 1 : 0;
            $customer = ($this->request->getPost('customers_create') == "true") ? 1 : 0;
            $notification = ($this->request->getPost('send_notification_create') == "true") ? 1 : 0;
            $faq = ($this->request->getPost('faq_create') == "true") ? 1 : 0;
            $settings = ($this->request->getPost('settings_create') == "true") ? 1 : 0;

            $system_users = ($this->request->getPost('system_user_create') == "true") ? 1 : 0;







            $create = [
                "order" => $orders,
                "categories" => $category,
                "subscription" => $subscription,
                "sliders" => $sliders,
                "tax" => $tax,
                "services" => $service,
                "promo_code" => $promo_code,
                "featured_section" => $featured_section,
                "partner" => $partner,
                "customers" => $customer,
                "send_notification" => $notification,
                "faq" => $faq,
                "settings" => $settings,
                "system_user" => $system_users,
            ];








            //  read permission section
            $orders = ($this->request->getPost('orders_read') == "true") ? 1 : 0;
            $category = ($this->request->getPost('categories_read') == "true") ? 1 : 0;
            $subscription = ($this->request->getPost('subscription_read') == "true") ? 1 : 0;
            $sliders = ($this->request->getPost('sliders_read') == "true") ? 1 : 0;
            $tax = ($this->request->getPost('tax_read') == "true") ? 1 : 0;
            $service = ($this->request->getPost('services_read') == "true") ? 1 : 0;
            $promo_code = ($this->request->getPost('promo_code_read') == "true") ? 1 : 0;
            $featured_section = ($this->request->getPost('featured_section_read') == "true") ? 1 : 0;
            $partner = ($this->request->getPost('partner_read') == "true") ? 1 : 0;
            $customer = ($this->request->getPost('customers_read') == "true") ? 1 : 0;
            $notification = ($this->request->getPost('send_notification_read') == "true") ? 1 : 0;
            $faq = ($this->request->getPost('faq_read') == "true") ? 1 : 0;
            $settings = ($this->request->getPost('settings_read') == "true") ? 1 : 0;
            $system_users = ($this->request->getPost('system_user_read') == "true") ? 1 : 0;

            $read = [
                "orders" => $orders,
                "categories" => $category,
                "subscription" => $subscription,
                "sliders" => $sliders,
                "tax" => $tax,
                "services" => $service,
                "promo_code" => $promo_code,
                "featured_section" => $featured_section,
                "partner" => $partner,
                "customers" => $customer,
                "send_notification" => $notification,
                "faq" => $faq,
                "settings" => $settings,
                "system_user" => $system_users,
            ];




            // update permission section
            $orders = ($this->request->getPost('orders_update') == "true") ? 1 : 0;
            $category = ($this->request->getPost('categories_update') == "true") ? 1 : 0;
            $subscription = ($this->request->getPost('subscription_update') == "true") ? 1 : 0;
            $sliders = ($this->request->getPost('sliders_update') == "true") ? 1 : 0;
            $tax = ($this->request->getPost('tax_update') == "true") ? 1 : 0;
            $service = ($this->request->getPost('services_update') == "true") ? 1 : 0;
            $promo_code = ($this->request->getPost('promo_code_update') == "true") ? 1 : 0;
            $featured_section = ($this->request->getPost('featured_section_update') == "true") ? 1 : 0;
            $partner = ($this->request->getPost('partner_update') == "true") ? 1 : 0;
            $customer = ($this->request->getPost('customers_update') == "true") ? 1 : 0;
            $notification = ($this->request->getPost('send_notification_read') == "true") ? 1 : 0;
            $faq = ($this->request->getPost('faq_update') == "true") ? 1 : 0;
            $system = ($this->request->getPost('system_update_update') == "true") ? 1 : 0;
            $settings = ($this->request->getPost('settings_update') == "true") ? 1 : 0;
            $system_users = ($this->request->getPost('system_user_update') == "true") ? 1 : 0;

            $update = [
                "orders" => $orders,
                "categories" => $category,
                "subscription" => $subscription,
                "sliders" => $sliders,
                "tax" => $tax,
                "services" => $service,
                "promo_code" => $promo_code,
                "featured_section" => $featured_section,
                "partner" => $partner,
                "customers" => $customer,
                "faq" => $faq,
                "system_update" => $system,
                "settings" => $settings,
                "system_user" => $system_users,
            ];




            // delete permission section

            $orders = ($this->request->getPost('orders_delete') == "true") ? 1 : 0;
            $category = ($this->request->getPost('categories_delete') == "true") ? 1 : 0;
            $subscription = ($this->request->getPost('subscription_delete') == "true") ? 1 : 0;
            $sliders = ($this->request->getPost('sliders_delete') == "true") ? 1 : 0;
            $tax = ($this->request->getPost('tax_delete') == "true") ? 1 : 0;
            $service = ($this->request->getPost('services_delete') == "true") ? 1 : 0;
            $promo_code = ($this->request->getPost('promo_code_delete') == "true") ? 1 : 0;
            $featured_section = ($this->request->getPost('featured_section_delete') == "true") ? 1 : 0;
            $partner = ($this->request->getPost('partner_delete') == "true") ? 1 : 0;
            $customer = ($this->request->getPost('customers_update') == "true") ? 1 : 0;
            $notification = ($this->request->getPost('send_notification_delete') == "true") ? 1 : 0;
            $faq = ($this->request->getPost('faq_delete') == "true") ? 1 : 0;
            // $system = ($this->request->getPost('support_tickets_delete') == "true") ? 1 : 0;
            // $system_users = ($this->request->getPost('system_user_update') == "true") ? 1 : 0;

            $system_users = ($this->request->getPost('system_user_delete') == "true") ? 1 : 0;

            $delete = [
                "orders" => $orders,
                "categories" => $category,
                "subscription" => $subscription,
                "sliders" => $sliders,
                "tax" => $tax,
                "services" => $service,
                "promo_code" => $promo_code,
                "featured_section" => $featured_section,
                "partner" => $partner,
                "customers" => $customer,
                "faq" => $faq,
                "send_notification" => $notification,
                "system_user" => $system_users,
                // "system" => $system,
            ];
            $permissions = ["create" => $create, "read" => $read, "update" => $update, "delete" => $delete];
            $permission = json_encode($permissions);
            $role = $this->request->getPost('role');
            $data = [
                'user_id' => $user_id,
                'role' => $this->request->getPost('role'),
                'permissions' => ($role == "1") ? NULL : $permission,
            ];
            $save_perms =  $this->user_permissions->save($data);

            if($role=="1"){

                $operation =  $this->ionAuth->activate($user_id);
            }
            if ($save_perms) {
                $response['error'] = false;
                $response['message'] = "Added Permissions";
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['data'] = [];
                return $this->response->setJSON($response);
            } else {
                $response['error'] = true;
                $response['message'] = "Could not add permission";
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['data'] = [];
                return $this->response->setJSON($response);
            }
        } else {
            return redirect('unauthorised');
        }
    }

    public function edit_permit()
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
            // create permission section
            $order = ($this->request->getPost('orders_create_edit') == "true") ? 1 : 0;
            $category = ($this->request->getPost('categories_create_edit') == "true") ? 1 : 0;
            $subscription = ($this->request->getPost('subscription_create_edit') == "true") ? 1 : 0;
            $sliders = ($this->request->getPost('sliders_create_edit') == "true") ? 1 : 0;
            $tax = ($this->request->getPost('tax_create_edit') == "true") ? 1 : 0;
            $service = ($this->request->getPost('services_create_edit') == "true") ? 1 : 0;
            $promo_code = ($this->request->getPost('promo_code_create_edit') == "true") ? 1 : 0;
            $featured_section = ($this->request->getPost('featured_section_create_edit') == "true") ? 1 : 0;
            $partner = ($this->request->getPost('partner_create_edit') == "true") ? 1 : 0;
            $customer = ($this->request->getPost('customers_create_edit') == "true") ? 1 : 0;
            $notification = ($this->request->getPost('send_notification_create_edit') == "true") ? 1 : 0;
            $faq = ($this->request->getPost('faq_create_edit') == "true") ? 1 : 0;
            $system = ($this->request->getPost('system_update_create_edit') == "true") ? 1 : 0;
            $settings = ($this->request->getPost('settings_create_edit') == "true") ? 1 : 0;
            $system_users = ($this->request->getPost('system_user_create_edit') == "true") ? 1 : 0;


            $create = [
                "order" => $order,
                "categories" => $category,
                "subscription" => $subscription,
                "sliders" => $sliders,
                "tax" => $tax,
                "services" => $service,
                "promo_code" => $promo_code,
                "featured_section" => $featured_section,
                "partner" => $partner,
                "customers" => $customer,
                "send_notification" => $notification,
                "faq" => $faq,
                "settings" => $settings,
                "system_user" => $system_users,
            ];

            //  read permission section
            $orders = ($this->request->getPost('orders_read_edit') == "true") ? 1 : 0;
            $category = ($this->request->getPost('categories_read_edit') == "true") ? 1 : 0;
            $subscription = ($this->request->getPost('subscription_read_edit') == "true") ? 1 : 0;
            $sliders = ($this->request->getPost('sliders_read_edit') == "true") ? 1 : 0;
            $tax = ($this->request->getPost('tax_read_edit') == "true") ? 1 : 0;
            $service = ($this->request->getPost('services_read_edit') == "true") ? 1 : 0;
            $promo_code = ($this->request->getPost('promo_code_read_edit') == "true") ? 1 : 0;
            $featured_section = ($this->request->getPost('featured_section_read_edit') == "true") ? 1 : 0;
            $partner = ($this->request->getPost('partner_read_edit') == "true") ? 1 : "";
            $customer = ($this->request->getPost('customers_read_edit') == "true") ? 1 : "";
            $notification = ($this->request->getPost('send_notification_read_edit') == "true") ? 1 : 0;
            $faq = ($this->request->getPost('faq_read_edit') == "true") ? 1 : 0;
            $settings = ($this->request->getPost('settings_read_edit') == "true") ? 1 : 0;
            $system = ($this->request->getPost('system_update_read_edit') == "true") ? 1 : 0;
            $system_users = ($this->request->getPost('system_user_read_edit') == "true") ? 1 : 0;


            $read = [
                "orders" => $orders,
                "categories" => $category,
                "subscription" => $subscription,
                "sliders" => $sliders,
                "tax" => $tax,
                "services" => $service,
                "promo_code" => $promo_code,
                "featured_section" => $featured_section,
                "partner" => $partner,
                "customers" => $customer,
                "send_notification" => $notification,
                "faq" => $faq,
                "settings" => $settings,
                "system_user" => $system_users,
            ];

            // update permission section
            $orders = ($this->request->getPost('orders_update_edit') == "true") ? 1 : 0;
            $category = ($this->request->getPost('categories_update_edit') == "true") ? 1 : 0;
            $subscription = ($this->request->getPost('subscription_update_edit') == "true") ? 1 : 0;
            $sliders = ($this->request->getPost('sliders_update_edit') == "true") ? 1 : 0;
            $tax = ($this->request->getPost('tax_update_edit') == "true") ? 1 : 0;
            $service = ($this->request->getPost('services_update_edit') == "true") ? 1 : 0;
            $promo_code = ($this->request->getPost('promo_code_update_edit') == "true") ? 1 : 0;
            $featured_section = ($this->request->getPost('featured_section_update_edit') == "true") ? 1 : 0;
            $partner = ($this->request->getPost('partner_update_edit') == "true") ? 1 : "";
            $customer = ($this->request->getPost('customers_update_edit') == "true") ? 1 : "";
            $notification = ($this->request->getPost('send_notification_update_edit') == "true") ? 1 : 0;
            $faq = ($this->request->getPost('faq_update_edit') == "true") ? 1 : 0;
            $system = ($this->request->getPost('system_update_update_edit') == "true") ? 1 : 0;
            $settings = ($this->request->getPost('settings_update_edit') == "true") ? 1 : 0;
            $system_users = ($this->request->getPost('system_user_update_edit') == "true") ? 1 : 0;

            $update = [
                "orders" => $orders,
                "categories" => $category,
                "subscription" => $subscription,
                "sliders" => $sliders,
                "tax" => $tax,
                "services" => $service,
                "promo_code" => $promo_code,
                "featured_section" => $featured_section,
                "partner" => $partner,
                "customers" => $customer,
                "faq" => $faq,
                "system_update" => $system,
                "settings" => $settings,
                "system_user" => $system_users,

            ];

            // delete permission section

            $orders = ($this->request->getPost('orders_delete_edit') == "true") ? 1 : 0;
            $category = ($this->request->getPost('categories_delete_edit') == "true") ? 1 : 0;
            $subscription = ($this->request->getPost('subscription_delete_edit') == "true") ? 1 : 0;
            $sliders = ($this->request->getPost('sliders_delete_edit') == "true") ? 1 : 0;
            $tax = ($this->request->getPost('tax_delete_edit') == "true") ? 1 : 0;
            $service = ($this->request->getPost('services_delete_edit') == "true") ? 1 : 0;
            $promo_code = ($this->request->getPost('promo_code_delete_edit') == "true") ? 1 : 0;
            $featured_section = ($this->request->getPost('featured_section_delete_edit') == "true") ? 1 : 0;
            $partner = ($this->request->getPost('partner_delete_edit') == "true") ? 1 : "";
            $customer = ($this->request->getPost('customers_delete_edit') == "true") ? 1 : "";
            $notification = ($this->request->getPost('send_notification_delete_edit') == "true") ? 1 : 0;
            $faq = ($this->request->getPost('faq_delete_edit') == "true") ? 1 : 0;
            $system = ($this->request->getPost('system_update_delete_edit') == "true") ? 1 : 0;
            $settings = ($this->request->getPost('settings_delete_edit') == "true") ? 1 : 0;

            $system_users = ($this->request->getPost('system_user_delete_edit') == "true") ? 1 : 0;

            $delete = [
                "orders" => $orders,
                "categories" => $category,
                "subscription" => $subscription,
                "sliders" => $sliders,
                "tax" => $tax,
                "services" => $service,
                "promo_code" => $promo_code,
                "featured_section" => $featured_section,
                "partner" => $partner,
                "customers" => $customer,
                "faq" => $faq,
                "send_notification" => $notification,
                "system_user" => $system_users,
            ];
            $permissions = ["create" => $create, "read" => $read, "update" => $update, "delete" => $delete];




            $permission = json_encode($permissions);

            $role = $this->request->getPost('edit_role');
            if ($this->request->getPost('edit_role') == '2') {
                $role = "2";
            } else if ($this->request->getPost('edit_role') == '1') {
                $role = "1";
            } else {
                $role = "3";
            }
            $user_id = $this->request->getPost('id');
            // 'user_id' => $this->request->getPost('id'),
            $data = [
                'role' => $role,
                'permissions' => ($role == "1") ? NULL : $permission,
            ];
            $builder = $this->db->table('user_permissions');
            $save_perms = $builder->update($data, ['user_id' => $user_id]);
            // $save_perms =  $this->user_permissions->update($user_id, $data);
            if ($save_perms) {
                $response['error'] = false;
                $response['message'] = "Added Permissions";
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['data'] = [];
                return $this->response->setJSON($response);
            } else {
                $response['error'] = true;
                $response['message'] = "Could not add permission";
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['data'] = [];
                return $this->response->setJSON($response);
            }
        } else {
            return redirect('unauthorised');
        }
    }
}
