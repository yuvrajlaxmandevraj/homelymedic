<?php

namespace App\Controllers\admin;

use App\Models\Cash_collection_model;
use App\Models\Orders_model;
use App\Models\Partners_model;
use App\Models\Payment_request_model;
use App\Models\Promo_code_model;
use App\Models\Service_model;
use App\Models\Users_model;
use App\Models\Service_ratings_model;
use App\Models\Settelement_model;
use App\Models\Settlement_model;
use IonAuth\Models\IonAuthModel;

class Partners extends Admin
{
    public $partner,  $validation, $db, $ionAuth, $creator_id;
    public function __construct()
    {
        parent::__construct();
        $this->partner = new Partners_model();
        $this->users = new Users_model();
        $this->cash_collection = new Cash_collection_model();
        $this->settle_commission = new Settlement_model();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        $this->ionAuth = new \IonAuth\Libraries\IonAuth();
        $this->creator_id = $this->userId;
        $this->superadmin = $this->session->get('email');
    }
    public function index()
    {
        helper('function');
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Partners | Admin Panel';
            $this->data['main_page'] = 'partners';



            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function add_partner()
    {

        if ($this->isLoggedIn && $this->userIsAdmin) {
            $permission = is_permitted($this->creator_id, 'create', 'partner');
            if ($permission) {
                if ($this->isLoggedIn && $this->userIsAdmin) {
                    $this->data['title'] = 'Add Partners | Admin Panel';
                    $this->data['main_page'] = 'add_partner';
                    $partner_details = !empty(fetch_details('partner_details', ['partner_id' => $this->userId])) ? fetch_details('partner_details', ['partner_id' => $this->userId])[0] : [];
                    $partner_timings = !empty(fetch_details('partner_timings', ['partner_id' => $this->userId])) ? fetch_details('partner_timings', ['partner_id' => $this->userId]) : [];
                    $this->data['data'] = fetch_details('users', ['id' => $this->userId])[0];

                    $currency = get_settings('general_settings', true);

                    if (empty($currency)) {
                        $_SESSION['toastMessage'] = 'Please first add currency and basic details in general settings ';
                        $_SESSION['toastMessageType'] = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/general-settings')->withCookies();
                    }
                    $this->data['currency'] = $currency['currency'];

                    $this->data['partner_details'] = $partner_details;
                    $this->data['partner_timings'] = $partner_timings;
                    $this->data['city_name'] = fetch_details('cities', [], ['id', 'name']);


                    return view('backend/admin/template', $this->data);
                } else {
                    return redirect('admin/login');
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => "Sorry! you're not permitted to take this action",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } else {
            return redirect('admin/login');
        }

        // try {

        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }

    public function list()
    {
        // try {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 20;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';

        print_r(json_encode($this->partner->list(false, $search, $limit, $offset, $sort, $order)));
    }

    public function view_partner()
    {
        try {
            helper('function');
            $uri = service('uri');
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $partner_id = $uri->getSegments()[3];
                $data = fetch_details('partner_details', ['partner_id' => $partner_id]);
                if (empty($data)) {
                    return redirect('admin/partners');
                }
                $partner_details = $data[0];
                $user_details = fetch_details('users', ['id' => $partner_id])[0];
                // passing data
                $this->data['title'] = 'Partners | Admin Panel';
                $this->data['partner_details'] = $partner_details;
                $this->data['personal_details'] = $user_details;
                $this->data['main_page'] = 'view_partner';
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function edit_partner()
    {
        // try {
        helper('function');
        $uri = service('uri');
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $partner_id = $uri->getSegments()[3];
            $data = fetch_details('partner_details', ['partner_id' => $partner_id]);
            if (empty($data)) {
                return redirect('admin/partners');
            }
            $partner_details = $data[0];
            $user_details = fetch_details('users', ['id' => $partner_id])[0];
            $currency = get_settings('general_settings', true);
            $partner_timings = fetch_details('partner_timings', ['partner_id' => $partner_id], '', '', '', '', 'ASC');
            $this->data['currency'] = $currency['currency'];
            $this->data['title'] = 'Partners | Admin Panel';
            $this->data['partner_details'] = $partner_details;
            $this->data['personal_details'] = $user_details;
            $this->data['partner_timings'] = $partner_timings;
            $this->data['main_page'] = 'edit_partner';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }

    public function insert_partner()
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


        // try {

        $permission = is_permitted($this->creator_id, 'create', 'partner');
        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $t = time();

                $this->validation->setRules(
                    [
                        'company_name' => [
                            "rules" => 'required|trim',
                            "errors" => [
                                "required" => "Please enter company_name"
                            ]
                        ],
                        'city' => [
                            "rules" => 'required|trim',
                            "errors" => [
                                "required" => "Please enter city",
                            ]
                        ],
                        'address' => [
                            "rules" => 'required|trim',
                            "errors" => [
                                "required" => "Please enter address",
                            ]
                        ],
                        'partner_latitude' => [
                            "rules" => 'required|trim',
                            "errors" => [
                                "required" => "Please choose provider location",
                            ]
                        ],
                        'type' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please select provider's type",
                            ]
                        ],
                        'number_of_members' => [
                            "rules" => 'required|numeric',
                            "errors" => [
                                "required" => "Please enter number of members",
                                "numeric" => "Please enter numeric value for members"
                            ]
                        ],
                        'visiting_charges' => [
                            "rules" => 'required|numeric',
                            "errors" => [
                                "required" => "Please enter visiting charges",
                                "numeric" => "Please enter numeric value for visiting charges"
                            ]
                        ],
                        'advance_booking_days' => [
                            "rules" => 'required|numeric',
                            "errors" => [
                                "required" => "Please enter advance booking days",
                                "numeric" => "Please enter numeric advance booking days"
                            ]
                        ],
                        'start_time' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter provider's working days",
                            ]
                        ],
                        'end_time' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter provider's working properly ",
                            ]
                        ],
                        'username' => [
                            "rules" => 'required|trim',
                            "errors" => [
                                "required" => "Please enter provider's name",
                            ]
                        ],
                        'email' => [
                            "rules" => 'required|trim',
                            "errors" => [
                                "required" => "Please enter provider's email",
                            ]
                        ],
                        'phone' => [
                            "rules" => 'required|numeric',
                            "errors" => [
                                "required" => "Please enter provider's phone number",
                                "numeric" => "Please enter numeric phone number"
                            ]
                        ],
                        'password' => [
                            "rules" => 'required|trim',
                            "errors" => [
                                "required" => "Please enter password",
                            ]
                        ],
                        // 'admin_commission' => [
                        //     "rules" => 'required|numeric',
                        //     "errors" => [
                        //         "required" => "Please enter admin commission",
                        //         "numeric" => "Please enter numeric admin commission"
                        //     ]
                        // ],
                        'tax_name' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter tax_name",
                            ]
                        ],
                        'tax_number' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter admin tax number",
                            ]
                        ],
                        'account_number' => [
                            "rules" => 'required|numeric',
                            "errors" => [
                                "required" => "Please enter account number",
                                "numeric" => "Please enter numeric account number"
                            ]
                        ],
                        'account_name' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter account name",
                            ]
                        ],
                        'bank_code' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter bank code",
                            ]
                        ],
                        'bank_name' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter bank name",
                            ]
                        ],
                        'swift_code' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter swift code",
                            ]
                        ],

                        'image' => [
                            "rules" => 'uploaded[passport]|ext_in[image,png,jpg,gif,jpeg,webp]|max_size[image,8496]|is_image[image]'
                        ],
                        'banner_image' => [
                            "rules" => 'uploaded[passport]|ext_in[image,png,jpg,gif,jpeg,webp]|max_size[image,8496]|is_image[image]'
                        ],
                        'passport' => [
                            "rules" => 'uploaded[passport]|ext_in[image,png,jpg,gif,jpeg,webp]|max_size[image,8496]|is_image[image]'
                        ],
                        'national_id' => [
                            "rules" => 'uploaded[national_id]|ext_in[image,png,jpg,gif,jpeg,webp]|max_size[image,8496]|is_image[image]'
                        ],

                    ],
                );
                if (!$this->validation->withRequest($this->request)->run()) {
                    $errors = $this->validation->getErrors();
                    $response['error'] = true;
                    $response['message'] = $errors;
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    $response['data'] = [];

                    return $this->response->setJSON($response);
                }
                // $mobile_data  = fetch_details('users', ['phone' => $_POST['phone']]);


                $db      = \Config\Database::connect();
                $builder = $db->table('users u');
                $builder->select('u.*,ug.group_id')
                    ->join('users_groups ug', 'ug.user_id = u.id')
                    ->where('ug.group_id', 3)
                    ->where(['phone' =>  $_POST['phone']]);
                $mobile_data = $builder->get()->getResultArray();

                if (!empty($mobile_data) && $mobile_data[0]['phone']) {
                    $response['error'] = true;
                    $response['message'] = "Phone number already exists please use another one";
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    $response['data'] = [];
                    return $this->response->setJSON($response);
                }

                if (!preg_match('/^-?(90|[1-8][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('partner_latitude'))) {

                    $response['error'] = true;
                    $response['message'] = "Please enter valid latitude";
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    $response['data'] = [];
                    return $this->response->setJSON($response);
                }


                // if (!preg_match('/^-?(180|1[1-7][0-9][.][0-9]{1,20}|[1-9][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('partner_longitude'))) {
                //     $response['error'] = true;
                //     $response['message'] = "Please enter valid Longitude";
                //     $response['csrfName'] = csrf_token();
                //     $response['csrfHash'] = csrf_hash();
                //     $response['data'] = [];
                //     return $this->response->setJSON($response);
                // }

                if (!preg_match('/^-?(180(\.0{1,20})?|1[0-7][0-9](\.[0-9]{1,20})?|[1-9][0-9](\.[0-9]{1,20})?|[0-9](\.[0-9]{1,20})?)$/', $this->request->getPost('partner_longitude'))) {
                    $response['error'] = true;
                    $response['message'] = "Please enter a valid Longitude";
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    $response['data'] = [];
                    return $this->response->setJSON($response);
                }
                
                $ion_auth = new IonAuthModel();

                $username = trim($_POST['username']);
                $password = $_POST['password'];
                $email = strtolower($_POST['email']);
                $phone = $_POST['phone'];
                $country_code = $_POST['country_code'];

                $city = $_POST['city'];
                $is_approved = isset($_POST['is_approved']) ? 1 : 0;

                $partner_image = $this->request->getFile('image');
                $banner_image = $this->request->getFile('banner_image');
                $national_id_image = $this->request->getFile('national_id');
                $address_id_image = $this->request->getFile('address_id');
                $passport_image = $this->request->getFile('passport');


                $image_name = 'public/backend/assets/profile/' . $partner_image->getName();
                $banner_name = 'public/backend/assets/banner/' . $banner_image->getName();
                $national_id_name = 'public/backend/assets/national_id/' . $national_id_image->getName();
                $address_id_name = 'public/backend/assets/address_id/' . $address_id_image->getName();
                $passport_name = 'public/backend/assets/passport/' . $passport_image->getName();

                $users_details['username'] = $username;
                $users_details['password'] =  $ion_auth->hashPassword($password);
                $users_details['email'] = $email;
                $users_details['latitude'] = $this->request->getPost('partner_latitude');
                $users_details['longitude'] = $this->request->getPost('partner_longitude');
                $users_details['phone'] = $phone;
                $users_details['country_code'] =  $country_code;

                $users_details['city'] = $city;
                $users_details['image'] = $image_name;
                $users_details['is_approved'] = $is_approved;
                $users_details['active'] = 1;

                $path = "/public/uploads/users/partners/";
                $insert_id = $this->users->save($users_details);
                // print_r($users_details);
                // die;

                if ($insert_id) {
                    $partner_image->move('./public/backend/assets/profile/');

                    // move_file($partner_image, 'public/backend/assets/profile/', $image_name);

                    $uploadedFiles = $this->request->getFiles('filepond');
                    $path = "public/uploads/partner/";
                    if (!empty($uploadedFiles)) {
                        $imagefile = $uploadedFiles['other_service_image_selector'];
                        $other_service_image_selector = [];
                        foreach ($imagefile as $key => $img) {
                            if ($img->isValid()) {
                                $name = $img->getRandomName();
                                if ($img->move($path, $name)) {
                                    $image_name = $name;
                                    $other_service_image_selector[$key] = "public/uploads/partner/" . $image_name;
                                }
                            }
                        }
                        $other_images = ['other_images' => !empty($other_service_image_selector) ? json_encode($other_service_image_selector) : "",];
                    }

                    $partner_id = $this->users->getInsertID();
                    // 

                    // $banner_name = $banner_image->getRandomName();
                    // 
                    $company_name = trim($_POST['company_name']);
                    $address = trim($_POST['address']);
                    $tax_name = $_POST['tax_name'];
                    $tax_number = $_POST['tax_number'];
                    $bank_name = $_POST['bank_name'];
                    $account_number = $_POST['account_number'];
                    $account_name = $_POST['account_name'];
                    $bank_code = $_POST['bank_code'];
                    $swift_code = $_POST['swift_code'];
                    $advance_booking_days = $_POST['advance_booking_days'];
                    $about = $_POST['about'];
                    $admin_commission = 0;
                    $type = $_POST['type'];
                    $number_of_members = $_POST['number_of_members'];
                    $visiting_charges = $_POST['visiting_charges'];
                    $is_approved = isset($_POST['is_approved']) ? 1 : 0;
                    $national_id_image = $this->request->getFile('national_id');


                    $partners['partner_id'] = $partner_id;
                    $partners['banner'] = $banner_name;
                    $partners['company_name'] = $company_name;
                    $partners['national_id'] = $national_id_name;
                    $partners['address_id'] = $address_id_name;
                    $partners['passport'] =  $passport_name;
                    $partners['address'] = $address;
                    $partners['tax_name'] = $tax_name;
                    $partners['tax_number'] = $tax_number;
                    $partners['bank_name'] = $bank_name;
                    $partners['account_number'] = $account_number;
                    $partners['account_name'] = $account_name;
                    $partners['bank_code'] = $bank_code;
                    $partners['swift_code'] = $swift_code;
                    $partners['advance_booking_days'] = $advance_booking_days;
                    $partners['about'] = $about;
                    $partners['admin_commission'] = $admin_commission;
                    $partners['type'] = $type;
                    $partners['number_of_members'] = $number_of_members;
                    $partners['visiting_charges'] = $visiting_charges;
                    $partners['is_approved'] = $is_approved;
                    $partners['long_description'] = (isset($_POST['long_description'])) ? $_POST['long_description'] : "";
                    $partners['other_images'] = $other_images['other_images'];
                    $partners['at_store'] = (isset($_POST['at_store'])) ? 1 : 0;
                    $partners['at_doorstep'] = (isset($_POST['at_doorstep'])) ? 1 : 0;


                    if ($this->partner->save($partners)) {



                        $banner_image->move('./public/backend/assets/banner/');
                        $national_id_image->move('./public/backend/assets/national_id/');
                        $address_id_image->move('public/backend/assets/address_id/');
                        $passport_image->move('./public/backend/assets/passport/');



                        $days = [
                            0 => 'monday',
                            1 => 'tuesday',
                            2 => 'wednesday',
                            3 => 'thursday',
                            4 => 'friday',
                            5 => 'saturday',
                            6 => 'sunday'
                        ];


                        for ($i = 0; $i < count($_POST['start_time']); $i++) {
                            $partner_timing = [];
                            $partner_timing['day'] = $days[$i];
                            if (isset($_POST['start_time'][$i])) {
                                $partner_timing['opening_time'] = $_POST['start_time'][$i];
                            }
                            if (isset($_POST['end_time'][$i])) {
                                $partner_timing['closing_time'] = $_POST['end_time'][$i];
                            }
                            $partner_timing['is_open'] = (isset($_POST[$days[$i]])) ? 1 : 0;
                            $partner_timing['partner_id'] = $partner_id;

                            insert_details($partner_timing, 'partner_timings');
                        }
                        // group inserting hre                            
                        if (!exists(["user_id" => $partner_id, "group_id" => 3], 'users_groups')) {
                            $group_data['user_id'] = $partner_id;
                            $group_data['group_id'] = 3;
                            insert_details($group_data, 'users_groups');
                        }
                        $response = [
                            'error' => false,
                            'message' => "Congratulations! Partner Added",
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => []
                        ];
                        return $this->response->setJSON($response);
                    } else {
                        $response = [
                            'error' => true,
                            'message' => "some error while adding partner",
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => []
                        ];
                        return $this->response->setJSON($response);
                    }
                } else {
                    $response = [
                        'error' => true,
                        'message' => "some error while addding partner",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                    // 


                }
            } else {
                return redirect('admin/login');
            }
        } else {
            $response = [
                'error' => true,
                'message' => "Sorry! you're not permitted to take this action",
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash(),
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

    public function deactivate_partner()
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
        try {

            $permission = is_permitted($this->creator_id, 'update', 'partner');
            if ($permission) {
                if ($this->isLoggedIn && $this->userIsAdmin) {
                    $partner_id = $this->request->getPost('partner_id');
                    $partner_details = fetch_details('users', ['id' => $partner_id])[0];
                    $operation =  $this->ionAuth->deactivate($partner_id);


                    if ($operation) {
                        $response = [
                            'error' => false,
                            'message' => "successfully disabled",
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
                    return redirect('admin/login');
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => "Sorry! you're not permitted to take this action",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
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

    public function activate_partner()
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
        try {

            $permission = is_permitted($this->creator_id, 'update', 'partner');
            if ($permission) {
                if ($this->isLoggedIn && $this->userIsAdmin) {
                    $partner_id = $this->request->getPost('partner_id');
                    $partner_details = fetch_details('users', ['id' => $partner_id])[0];
                    $operation =  $this->ionAuth->activate($partner_id);

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
                    return redirect('admin/login');
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => "Sorry! you're not permitted to take this action",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
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

    public function approve_partner()
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
        try {

            $permission = is_permitted($this->creator_id, 'update', 'partner');
            if ($permission) {
                if ($this->isLoggedIn && $this->userIsAdmin) {
                    $partner_id = $this->request->getPost('partner_id');
                    $builder = $this->db->table('partner_details');
                    $partner_approval = $builder->set('is_approved', 1)->where('partner_id', $partner_id)->update();
                    $partner_details = fetch_details('partner_details', ['partner_id' => $partner_id])[0];

                    $fcm_server_key = get_settings('api_key_settings', true)['firebase_server_key'];
                    $to_send_id = $partner_id;
                    $builder = $this->db->table('users')->select('fcm_id,email,platform');
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
                        if ($partner_approval) {
                            $fcmMsg = array(
                                'content_available' => true,
                                'title' => "Approval of Registration Request",
                                'body' => "Your registration request has been approved. You can now access all features of our platform",
                                'type' => 'provider_request_status',
                                'status' => 'approve',
                                'type_id' => $to_send_id,
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            );

                            $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                            send_notification($fcmMsg, $registrationIDs_chunks);
                        }
                    }
                    if ($partner_approval) {
                        $response = [
                            'error' => false,
                            'message' => "Partner approved",
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => [$partner_approval]
                        ];

                        $settings = get_settings('general_settings', true);
                        $icon = $settings['logo'];
                        if (!empty($email)) {
                            $data = array(
                                'name' => $partner_details['company_name'],
                                'title' => "Approval of Registration Request",
                                'logo' => base_url("public/uploads/site/" . $icon),
                                'first_paragraph' => 'I am pleased to inform you that your request of registration  has been approved. After careful review and consideration, our team has determined that your request meets all the necessary criteria and is eligible for approval.',
                                'second_paragraph' => 'Once again, congratulations on your approval status! We look forward to working with you and supporting your goals.',
                                'third_paragraph' => 'If you have any questions or concerns, please do not hesitate to contact us.',
                                'company_name' => $settings['company_title'],
                            );

                            email_sender($email, 'Status Update on Your Request/Application', view('backend/admin/pages/provider_email', $data));
                        }

                        return $this->response->setJSON($response);
                    } else {
                        $response = [
                            'error' => false,
                            'message' => "Could not approve partner",
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => [$partner_approval]
                        ];

                        return $this->response->setJSON($response);
                    }
                } else {
                    return redirect('admin/login');
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => "Sorry! you're not permitted to take this action",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
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


    public function disapprove_partner()
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
        try {

            $permission = is_permitted($this->creator_id, 'update', 'partner');
            if ($permission) {
                if ($this->isLoggedIn && $this->userIsAdmin) {
                    $partner_id = $this->request->getPost('partner_id');
                    $builder = $this->db->table('partner_details');
                    $partner_approval = $builder->set('is_approved', 0)->where('partner_id', $partner_id)->update();
                    $partner_details = fetch_details('partner_details', ['partner_id' => $partner_id])[0];
                    $fcm_server_key = get_settings('api_key_settings', true)['firebase_server_key'];
                    $to_send_id = $partner_id;
                    $builder = $this->db->table('users')->select('fcm_id,email,platform');

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
                        $fcmMsg = array(
                            'content_available' => true,
                            'title' => "Rejection of Registration Request",
                            'body' => "Your registration request has been rejected. Please contact our customer support team if you have any questions or concerns.",
                            'type_id' => $to_send_id,
                            'type' => 'provider_request_status',
                            'status' => 'reject',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );

                        $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                        send_notification($fcmMsg, $registrationIDs_chunks);
                    }
                    if ($partner_approval) {
                        $response = [
                            'error' => false,
                            'message' => "Partner is disapproved",
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => [$partner_approval]
                        ];

                        $settings = get_settings('general_settings', true);
                        $icon = $settings['logo'];
                        if (!empty($email)) {
                            $data = array(
                                'name' => $partner_details['company_name'],
                                'title' => "Rejection of Registration Requestl",
                                'logo' => base_url("public/uploads/site/" . $icon),
                                'first_paragraph' => 'We regret to inform you that your request of registration has been disapproved. After thorough evaluation and consideration, our team has determined that your request does not meet the necessary criteria for approval.',
                                'second_paragraph' => 'We understand that this decision may be disappointing for you, but please know that we carefully reviewed your request and made the best decision based on our policies and guidelines.',
                                'third_paragraph' => 'If you have any questions or concerns regarding the decision, please do not hesitate to reach out to us. We would be happy to discuss any specific concerns that you may have.',
                                'company_name' => $settings['company_title'],
                            );

                            email_sender($email, 'Status Update on Your Request/Application', view('backend/admin/pages/provider_email', $data));
                        }


                        return $this->response->setJSON($response);
                    } else {
                        $response = [
                            'error' => false,
                            'message' => "Could not disapprove partner",
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => [$partner_approval]
                        ];
                        return $this->response->setJSON($response);
                    }
                } else {
                    return redirect('admin/login');
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => "Sorry! you're not permitted to take this action",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
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
    public function delete_partner()
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

        try {

            $permission = is_permitted($this->creator_id, 'delete', 'partner');
            if ($permission) {
                if ($this->isLoggedIn && $this->userIsAdmin) {
                    $partner_id = $this->request->getPost('partner_id');

                    $service_details = fetch_details('services', ['user_id' => $partner_id]);
                    $partner_timing_details = fetch_details('partner_timings', ['partner_id' => $partner_id]);
                    $partner_details = fetch_details('partner_details', ['partner_id' => $partner_id]);
                    $user_details = fetch_details('users', ['id' => $partner_id]);
                    $user_group_details = fetch_details('users_groups', ['user_id' => $partner_id]);
                    if (!empty($service_details)) {
                        $builder = $this->db->table('services');
                        $builder->delete(['user_id' => $partner_id]);
                    }
                    if (!empty($partner_timing_details)) {
                        $builder = $this->db->table('partner_timings');
                        $builder->delete(['partner_id' => $partner_id]);
                    }
                    if (!empty($user_group_details)) {
                        $builder = $this->db->table('users_groups');
                        $builder->delete(['user_id' => $partner_id]);
                    }
                    if (!empty($partner_details)) {
                        if (file_exists($partner_details[0]['banner'])) {
                            unlink(FCPATH . $partner_details[0]['banner']);
                        }
                        if (file_exists($partner_details[0]['address_id'])) {

                            unlink(FCPATH . $partner_details[0]['address_id']);
                        }
                        if (file_exists($partner_details[0]['passport'])) {

                            unlink(FCPATH . $partner_details[0]['passport']);
                        }

                        if (file_exists($partner_details[0]['national_id'])) {

                            unlink(FCPATH . $partner_details[0]['national_id']);
                        }

                        $builder = $this->db->table('partner_details');
                        $builder->delete(['partner_id' => $partner_id]);
                    }



                    if (!empty($user_details)) {
                        $builder = $this->db->table('users');






                        $partner_approval = $builder->delete(['id' => $partner_id]);

                        if ($partner_approval) {
                            $response = [
                                'error' => false,
                                'message' => "Partner is Removed",
                                'csrfName' => csrf_token(),
                                'csrfHash' => csrf_hash(),
                                'data' => [$partner_approval]
                            ];
                            return $this->response->setJSON($response);
                        } else {
                            $response = [
                                'error' => false,
                                'message' => "Could not Delete partner",
                                'csrfName' => csrf_token(),
                                'csrfHash' => csrf_hash(),
                                'data' => [$partner_approval]
                            ];
                            return $this->response->setJSON($response);
                        }
                    }
                } else {
                    return redirect('admin/login');
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => "Sorry! you're not permitted to take this action",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
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

    public function payment_request()
    {


        try {


            helper('function');
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $this->data['title'] = 'Partners | Admin Panel';
                $this->data['main_page'] = 'payment_request';

                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function payment_request_list()
    {

        try {
            $payment_requests = new Payment_request_model();
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'p.id';


            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';


            $data = $payment_requests->list(false, $search, $limit, $offset, $sort, $order);


            //   print_r($data);
            //   die;
            return $data;
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function pay_partner()
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
        // try {

            $admin_id =  $this->userId;

            $pr_id = $this->request->getPost('request_id');
            $user_id = $this->request->getPost('user_id');
            $reason = $this->request->getPost('reason');
            $amount = $this->request->getPost('amount');
            $status = $this->request->getPost('status');


            $partner_details  = fetch_details('users', ['id' => $user_id]);
            $admin_details  = fetch_details('users', ['id' => $admin_id]);




            if ($status == 1) {
                if (!empty($partner_details)) {
                    $update_request = update_details(
                        ['remarks' => $reason, 'status' => $status],
                        ['id' => $pr_id],
                        'payment_request'
                    );



                    $update_balance =  (int)$admin_details[0]['balance'] + $amount;
                    $update_admin = update_details(
                        ['balance' => $update_balance],
                        ['id' => $admin_id],
                        'users'
                    );
                    if ($update_admin) {
                        $response = [
                            'error' => false,
                            'message' => "debited amount $amount",
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => []
                        ];
                        $to_send_id = $user_id;

                        $builder = $this->db->table('users')->select('fcm_id,email,platform');
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
                            $fcmMsg = array(
                                'content_available' => true,
                                'title' => "Approval of Payment Request",
                                'body' => "Your Payment request has been approved.",
                                'type' => 'withdraw_request',
                                'status' => 'approve',
                                'type_id' => $to_send_id,
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            );

                            $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                            // $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
                            send_notification($fcmMsg, $registrationIDs_chunks);
                        }

                        $settings = get_settings('general_settings', true);
                        $icon = $settings['logo'];
                        if (!empty($email)) {
                            $partner_data = fetch_details('partner_details', ['partner_id' => $user_id], ['company_name']);
                            $data = array(
                                'name' => $partner_data[0]['company_name'],
                                'title' => "Withdrawal Request Approved",
                                'logo' => base_url("public/uploads/site/" . $icon),
                                'first_paragraph' => 'We are pleased to inform you that your withdrawal request has been approved',
                                'second_paragraph' => 'If you have any questions or concerns regarding this transaction, please do not hesitate to contact us. ',
                                'third_paragraph' => 'Thank you for choosing our services. We look forward to providing you with excellent service in the future.',
                                'company_name' => $settings['company_title'],
                            );

                            email_sender($email, 'Withdrawal Request Approved', view('backend/admin/pages/provider_email', $data));
                        }


                        return $this->response->setJSON($response);
                    }
                }
            } else {
                $update_balance =  (int)$partner_details[0]['balance'] + $amount;
                $update_id = update_details(['balance' => $update_balance], ['id' => $user_id], 'users');
                update_details(
                    [
                        'remarks' => $reason,
                        'status' => $status
                    ],
                    ['id' => $pr_id],
                    'payment_request'
                );
                if ($update_id) {
                    $response = [
                        'error' => false,
                        'message' => "Rejection occurred",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    $to_send_id = $user_id;

                    $builder = $this->db->table('users')->select('fcm_id,email,platform');
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
                        $fcmMsg = array(
                            'content_available' => true,
                            'title' => "Rejection of Payment Request",
                            'body' => "Your Payment request has been rejected.",
                            'type' => 'withdraw_request',
                            'status' => 'reject',
                            'type_id' => $to_send_id,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );

                        $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                        send_notification($fcmMsg, $registrationIDs_chunks);
                    }

                    $settings = get_settings('general_settings', true);
                    $icon = $settings['logo'];
                    if (!empty($email)) {
                        $partner_data = fetch_details('partner_details', ['partner_id' => $user_id], ['company_name']);
                        $data = array(
                            'name' => $partner_data[0]['company_name'],
                            'title' => " Withdrawal Request Disapproved",
                            'logo' => base_url("public/uploads/site/" . $icon),
                            'first_paragraph' => 'We regret to inform you that your withdrawal request has been disapproved. We apologize for any inconvenience this may have caused.',
                            'second_paragraph' => 'If you have any questions or concerns regarding this transaction, please do not hesitate to contact us. ',
                            'third_paragraph' => 'Thank you for choosing our services. We look forward to providing you with excellent service in the future.',
                            'company_name' => $settings['company_title'],
                        );

                        email_sender($email, ' Withdrawal Request Disapproved', view('backend/admin/pages/provider_email', $data));
                    }

                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "some error occurred",
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
    }

    public function delete_request()
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
        try {

            if ($this->isLoggedIn && $this->userIsAdmin) {
                $id = $this->request->getPost('id');

                $builder = $this->db->table('payment_request')->delete(['id' => $id]);
                if ($builder) {
                    $response = [
                        'error' => false,
                        'message' => "Deleted payment request success",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "Couldn't delete payment request",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
            } else {
                return redirect('admin/login');
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function partner_details()
    {
        try {
            helper('function');
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';

            print_r(json_encode($this->partner->list(false, $search, $limit, $offset, $sort, $order, ["pd.partner_id " => $partner_id])));
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function banking_details()
    {

        try {
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];


            $db      = \Config\Database::connect();
            $builder = $db->table('partner_details pd');
            $count = $builder->select('COUNT(pd.id) as total')
                ->where('pd.partner_id', $partner_id)->get()->getResultArray();
            $total = $count[0]['total'];
            $tempRow = array();
            $data =  $builder->select('pd.*, u.city')
                ->join('users u', 'u.id = pd.partner_id')
                ->where('pd.partner_id', $partner_id)->get()->getResultArray();
            $rows = [];
            foreach ($data as $row) {
                $tempRow['partner_id'] = $row['partner_id'];
                $tempRow['name'] = $row['city'];
                $tempRow['passport'] = $row['passport'];
                $tempRow['tax_name'] = $row['tax_name'];
                $tempRow['tax_number'] = $row['tax_number'];
                $tempRow['bank_name'] = $row['bank_name'];
                $tempRow['account_number'] = $row['account_number'];
                $tempRow['account_name'] = $row['account_name'];
                $tempRow['bank_code'] = $row['bank_code'];
                $tempRow['swift_code'] = $row['swift_code'];

                $rows[] = $tempRow;
            }
            $bulkData['total'] = $total;
            $bulkData['rows'] = $rows;
            return json_encode($bulkData);
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function timing_details()
    {
        try {
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];


            $db      = \Config\Database::connect();
            $builder = $db->table('partner_timings pt');
            $count = $builder->select('COUNT(pt.id) as total')
                ->where('pt.partner_id', $partner_id)->get()->getResultArray();
            $total = $count[0]['total'];
            $tempRow = array();
            $data =  $builder->select('pt.*,')
                ->where('pt.partner_id', $partner_id)->get()->getResultArray();
            $rows = [];
            // print_r($data);
            foreach ($data as $row) {
                $label = ($row['is_open'] == 1) ?
                    '<div class="badge badge-success projects-badge"> Open </div>' :
                    '<div class="badge badge-danger projects-badge"> Closed </div>';
                $tempRow['partner_id'] = $row['partner_id'];

                $label_new = ($row['is_open'] == 1) ?
                    "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Open
                    </div>" :
                    "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3'>Closed
                    </div>";


                $tempRow['partner_id'] = $row['partner_id'];
                $tempRow['day'] = $row['day'];
                $tempRow['opening_time'] = $row['opening_time'];
                $tempRow['closing_time'] = $row['closing_time'];
                $tempRow['is_open'] = $label;
                $tempRow['is_open_new'] = $label_new;
                $rows[] = $tempRow;
            }
            $bulkData['total'] = $total;
            $bulkData['rows'] = $rows;
            return json_encode($bulkData);
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function service_details()
    {
        try {
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $service_model = new Service_model();
            $where['user_id'] = $uri->getSegments()[3];
            $services =  $service_model->list(false, $search, $limit, $offset, $sort, $order, $where);
            return ($services);
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function settle_commission()
    {
        try {
            helper('function');
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $this->data['title'] = 'Commission Settlement | Admin Panel';
                $this->data['main_page'] = 'manage_commission';
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }



    public function cash_collection()
    {
        try {
            helper('function');
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $this->data['title'] = 'Cash Collection | Admin Panel';
                $this->data['main_page'] = 'cash_collection';
                return view('backend/admin/template', $this->data);
            } else {
                return redirect('admin/login');
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function commission_list()
    {
        try {
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';

            return json_encode($this->partner->unsettled_commission_list(false, $search, $limit, $offset, $sort, $order));
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function cash_collection_list()
    {
        try {

            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $data = json_encode($this->partner->list(false, $search, $limit, $offset, $sort, $order));
            print_r(json_encode($this->partner->list(false, $search, $limit, $offset, $sort, $order)));
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function commission_pay_out()
    {
        //  try {
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

            $order_id  = $this->request->getPost('id');
            $partner_id = $this->request->getPost('partner_id');
            $amount = $this->request->getPost('amount');

            $current_balance = fetch_details('users', ['id' => $partner_id], ['balance', 'email'])[0];
            $partner_data    = fetch_details('partner_details', ['partner_id' => $partner_id], ['company_name'])[0];


            $this->validation->setRules(
                [
                    'amount' => [
                        "rules" => 'required|numeric|less_than_equal_to[' . $current_balance['balance'] . ']',
                        "errors" => [
                            "required" => "Please enter commission",
                            "numeric" => "Please enter numeric value for commission",
                            "less_than" => "Amount must be less than current balance",
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

            $updated_balance = $current_balance['balance'] - $amount;
            $update = update_details(['balance' => $updated_balance], ['id' => $partner_id], 'users');
            $t = time();
            $data = [
                'transaction_type' => 'transaction',
                'user_id' => $this->userId,
                'partner_id' => $partner_id,
                'order_id' =>  "TXN-$t",
                'type' => 'fund_transfer',
                'txn_id' => '',
                'amount' =>  $amount,
                'status' => 'success',
                'currency_code' => NULL,
                'message' => 'commission settled'
            ];

            $settlement_history = [

                'provider_id' => $partner_id,
                'message' =>   $this->request->getPost('message'),
                'amount' =>  $amount,
                'status' => 'credit',
                'date' => date("Y-m-d H:i:s"),
            ];
            insert_details($settlement_history, 'settlement_history');
            if ($update) {
                if (add_transaction($data)) {
                    $response = [
                        'error' => false,
                        'message' => "Commission Settled Successfully",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    $to_send_id = $partner_id;
                    $builder = $this->db->table('users')->select('fcm_id,email,platform');
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
                            'title' => " Payment Settlement for " . $partner_data['company_name'],
                            'body' => "Payment Settlement Confirmation",
                            'type' => 'settlement',

                            'type_id' => $to_send_id,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                        send_notification($fcmMsg, $registrationIDs_chunks);
                    }

                    $settings = get_settings('general_settings', true);
                    $icon = $settings['logo'];
                    if (!empty($email)) {
                        $data = array(
                            'name' => $partner_data['company_name'],
                            'title' => " Payment Settlement for " . $partner_data['company_name'],
                            'logo' => base_url("public/uploads/site/" . $icon),
                            'first_paragraph' => 'I am writing to confirm that we have credited the agreed upon amount of ' . $amount . '  to your account, as per our agreement. This payment settles the outstanding balance for the services/products provided by your company.
                         We understand the importance of timely payments for maintaining a healthy business relationship and we strive to meet our payment obligations promptly. Please check your account and confirm that the payment has been received. If you have any questions or concerns, please do not hesitate to contact us.',
                            'second_paragraph' => 'Thank you for your prompt attention to this matter. We look forward to continuing our mutually beneficial partnership.',
                            'third_paragraph' => '',
                            'company_name' => $settings['company_title'],
                        );

                        email_sender($email, ' Payment Settlement for ' . $partner_data['company_name'], view('backend/admin/pages/provider_email', $data));
                    }

                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "Unsuccessful while adding transaction",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => "Unsuccessful while Updating settling status",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } else {
            return redirect('admin/login');
        }
        //  } catch (\Exception $th) {
        //      $response['error'] = true;
        //      $response['message'] = 'Something went wrong';
        //      return $this->response->setJSON($response);
        //  }

    }

    public function view_ratings()
    {
        try {

            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $ratings_model = new Service_ratings_model();
                $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
                $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
                $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
                $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
                $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';

                // $data =  $ratings_model->ratings_list(false, $search, $limit, $offset, $sort, $order, ['s.user_id' => $partner_id]);
                return json_encode($ratings_model->ratings_list(false, $search, $limit, $offset, $sort, $order, ['s.user_id' => $partner_id]));
            } else {
                return redirect('admin/login');
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function delete_rating()
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
        try {

            if ($this->isLoggedIn && $this->userIsAdmin) {
                $id = $this->request->getPost('id');
                $data = $this->db->table('services_ratings')->delete(['id' => $id]);
                if ($data) {
                    $response = [
                        'error' => false,
                        'message' => "Rating deleted successfully",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "unsuccessful in deletion of rating",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
            } else {
                return redirect('admin/login');
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }


    public function update_partner()
    {


        // try {

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

        if (isset($_POST) && !empty($_POST)) {

            $config = new \Config\IonAuth();
            $tables  = $config->tables;
            $this->validation->setRules(
                [

                    'username' => [
                        "rules" => 'required|trim',
                        "errors" => [
                            "required" => "Please enter username"
                        ]
                    ],
                    'email' => [
                        "rules" => 'required|trim',
                        "errors" => [
                            "required" => "Please enter provider's email",
                        ]
                    ],

                    'address' => [
                        "rules" => 'required|trim',
                        "errors" => [
                            "required" => "Please enter address",
                        ]
                    ],

                    'type' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please select provider's type",
                        ]
                    ],
                    'visiting_charges' => [
                        "rules" => 'required|numeric',
                        "errors" => [
                            "required" => "Please enter visiting charges",
                            "numeric" => "Please enter numeric value for visiting charges"
                        ]
                    ],
                    'advance_booking_days' => [
                        "rules" => 'required|numeric',
                        "errors" => [
                            "required" => "Please enter advance booking days",
                            "numeric" => "Please enter numeric advance booking days"
                        ]
                    ],
                    'start_time' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter provider's working days",
                        ]
                    ],
                    'end_time' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter provider's working properly ",
                        ]
                    ],
                    'account_number' => [
                        "rules" => 'required|numeric',
                        "errors" => [
                            "required" => "Please enter account number",
                            "numeric" => "Please enter numeric account number"
                        ]
                    ],
                    'account_name' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter account name",
                        ]
                    ],
                    'bank_code' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter bank code",
                        ]
                    ],
                    'bank_name' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter bank name",
                        ]
                    ],
                ],
            );
            if (!$this->validation->withRequest($this->request)->run()) {
                $errors = $this->validation->getErrors();
                $response['error'] = true;
                $response['message'] = $errors;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['data'] = [];

                return $this->response->setJSON($response);
            }

            if (!preg_match('/^-?(90|[1-8][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('partner_latitude'))) {

                $response['error'] = true;
                $response['message'] = "Please enter valid latitude";
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['data'] = [];
                return $this->response->setJSON($response);
            }


            // if (!preg_match('/^-?(180|1[1-7][0-9][.][0-9]{1,20}|[1-9][0-9][.][0-9]{1,20}|[0-9][.][0-9]{1,20})$/', $this->request->getPost('partner_longitude'))) {
            //     $response['error'] = true;
            //     $response['message'] = "Please enter valid Longitude";
            //     $response['csrfName'] = csrf_token();
            //     $response['csrfHash'] = csrf_hash();
            //     $response['data'] = [];
            //     return $this->response->setJSON($response);
            // }

            if (!preg_match('/^-?(180(\.0{1,20})?|1[0-7][0-9](\.[0-9]{1,20})?|[1-9][0-9](\.[0-9]{1,20})?|[0-9](\.[0-9]{1,20})?)$/', $this->request->getPost('partner_longitude'))) {
                $response['error'] = true;
                $response['message'] = "Please enter a valid Longitude";
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['data'] = [];
                return $this->response->setJSON($response);
            }





            $data = fetch_details('users', ['id' => $this->request->getPost('partner_id')], 'image')[0];
            $IdProofs = fetch_details('partner_details', ['partner_id' => $this->request->getPost('partner_id'),], ['national_id', 'address_id', 'passport', 'banner'])[0];
            $old_national_id = $IdProofs['national_id'];
            $old_address_id = $IdProofs['address_id'];
            $old_passport = $IdProofs['passport'];
            $old_banner = $IdProofs['banner'];
            $old_image = $data['image'];
            $other_images = fetch_details('partner_details', ['partner_id' => $this->request->getPost('partner_id')], ['other_images']);

            if (!empty($_FILES['image']) && isset($_FILES['image'])) {
                $file =  $this->request->getFile('image');
                $path =  './public/backend/assets/profile/';
                $path_db =  'public/backend/assets/profile/';
                if ($file->isValid()) {
                    if ($file->move($path)) {
                        if (file_exists($old_image) && !empty($old_image))
                            unlink($old_image);
                        $image = $path_db . $file->getName();
                    }
                } else {
                    $image = $old_image;
                }
            } else {
                $image = $old_image;
            }




            if (!empty($_FILES['banner_image']) && isset($_FILES['banner_image'])) {
                $file =  $this->request->getFile('banner_image');
                $path =  './public/backend/assets/banner/';
                $path_db =  'public/backend/assets/banner/';
                if ($file->isValid()) {
                    if ($file->move($path)) {
                        if (file_exists($old_banner) && !empty($old_banner))
                            unlink($old_banner);
                        $banner = $path_db . $file->getName();
                    }
                } else {
                    $banner = $old_banner;
                }
            } else {
                $banner = $old_banner;
            }





            if (!empty($_FILES['national_id']) && isset($_FILES['national_id'])) {




                $file =  $this->request->getFile('national_id');
                $path =  './public/backend/assets/national_id/';
                $path_db =  'public/backend/assets/national_id/';
                if ($file->isValid()) {

                    if ($file->move($path)) {
                        // echo '134e324 ';
                        // die;
                        if (file_exists($old_national_id) && !empty($old_national_id))

                            unlink($old_national_id);
                    }
                    $national_id = $path_db . $file->getName();
                } else {
                    $national_id = $old_national_id;
                }
            } else {
                $national_id = $old_national_id;
            }

            $uploadedFiles = $this->request->getFiles('filepond');

            $path = "public/uploads/partner/";
            if (!empty($uploadedFiles['other_service_image_selector_edit'][0]) && $uploadedFiles['other_service_image_selector_edit'][0]->getError() === UPLOAD_ERR_OK) {

                $imagefile = $uploadedFiles['other_service_image_selector_edit'];
                $other_service_image_selector = [];
                foreach ($imagefile as $key => $img) {
                    if ($img->isValid()) {
                        $name = $img->getRandomName();
                        if ($img->move($path, $name)) {
                            if (!empty($old_other_images)) {


                                $old_other_images_array = json_decode($old_other_images, true); // Decode JSON string to associative array

                                foreach ($old_other_images_array as $old) {
                                    if (file_exists(FCPATH . $old)) {
                                        unlink(FCPATH . $old);
                                    }
                                }
                            }

                            $other_image_name = $name;
                            $other_service_image_selector[$key] = "public/uploads/partner/" . $other_image_name;
                        }
                    }
                }
                $other_images[0] = ['other_images' => !empty($other_service_image_selector) ? json_encode($other_service_image_selector) : "",];
            } else {
                $other_images = ($other_images);
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
                    }
                } else {
                    $address_id = $old_address_id;
                }
            } else {
                $address_id = $old_address_id;
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
                    }
                } else {
                    $passport = $old_passport;
                }
            } else {
                $passport = $old_passport;
            }



            $partnerIDS = [
                'address_id' => $address_id,
                'national_id' => $national_id,
                'passport' => $passport,
                'banner' => $banner,
            ];

            if ($partnerIDS) {
                update_details($partnerIDS, ['partner_id' => $this->request->getPost('partner_id')], 'partner_details', false);
            }
            $phone = $_POST['phone'];
            $country_code = $_POST['country_code'];

            $userData = [
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'phone' => $phone,
                'country_code' => $country_code,
                'image' => $image,
                'latitude' => $this->request->getPost('partner_latitude'),
                'longitude' => $this->request->getPost('partner_longitude'),
                'city' => $this->request->getPost('city'),
            ];






            if ($userData) {
                update_details($userData, ['id' => $this->request->getPost('partner_id')], 'users');
            }


            // $admin_commission =0;
            $is_approved = isset($_POST['is_approved']) ? "1" : "0";



            $partner_details = [
                'company_name' => $this->request->getPost('company_name'),
                'type' => $this->request->getPost('type'),
                'visiting_charges' => $this->request->getPost('visiting_charges'),
                'about' => $this->request->getPost('about'),
                'advance_booking_days' => $this->request->getPost('advance_booking_days'),
                'bank_name' => $this->request->getPost('bank_name'),
                'account_number' => $this->request->getPost('account_number'),
                'account_name' => $this->request->getPost('account_name'),
                // 'admin_commission' => $admin_commission,
                'account_name' => $this->request->getPost('account_name'),
                'bank_code' => $this->request->getPost('bank_code'),
                'tax_name' => $this->request->getPost('bank_code'),
                'tax_number' => $this->request->getPost('tax_number'),
                'swift_code' => $this->request->getPost('swift_code'),
                'number_of_members' => $this->request->getPost('number_of_members'),
                'is_approved' => $is_approved,
                'other_images' => $other_images[0]['other_images'],
                'long_description' => (isset($_POST['long_description'])) ? $_POST['long_description'] : "",
                'address' => $this->request->getPost('address'),
                'at_store' => (isset($_POST['at_store'])) ? 1 : 0,
                'at_doorstep' => (isset($_POST['at_doorstep'])) ? 1 : 0

            ];
            if ($partner_details) {
                update_details($partner_details, ['partner_id' => $this->request->getPost('partner_id')], 'partner_details', false);
            }

            $days = [
                0 => 'monday',
                1 => 'tuesday',
                2 => 'wednesday',
                3 => 'thursday',
                4 => 'friday',
                5 => 'saturday',
                6 => 'sunday'
            ];

            for ($i = 0; $i < count($_POST['start_time']); $i++) {


                $partner_timing = [];
                $partner_timing['day'] = $days[$i];





                if (isset($_POST['start_time'][$i])) {
                    $partner_timing['opening_time'] = $_POST['start_time'][$i];
                }
                if (isset($_POST['end_time'][$i])) {
                    $partner_timing['closing_time'] = $_POST['end_time'][$i];
                }
                $partner_timing['is_open'] = (isset($_POST[$days[$i]])) ? 1 : 0;





                $timing_data = fetch_details('partner_timings', ['partner_id' => $this->request->getPost('partner_id'), 'day' => $days[$i]]);


                if (count($timing_data) > 0) {
                    update_details($partner_timing, ['partner_id' => $this->request->getPost('partner_id'), 'day' => $days[$i]], 'partner_timings');
                } else {
                    $partner_timing['partner_id'] = $this->request->getPost('partner_id');
                    insert_details($partner_timing, 'partner_timings');
                }
            }


            $msg = 'Partner updated successfully!';
            $response = [
                'error' => false,
                'message' => $msg,
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash(),
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
    public function cash_collection_deduct()
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
        $partner_id = $this->request->getPost('partner_id');

        $amount = $this->request->getPost('amount');
        $message = $this->request->getPost('message');
        $current_balance = fetch_details('users', ['id' => $partner_id], ['payable_commision', 'email'])[0];
        $this->validation->setRules(
            [
                'amount' => [
                    "rules" => 'required|numeric|less_than_equal_to[' . $current_balance['payable_commision'] . ']',
                    "errors" => [
                        "required" => "Please enter commission",
                        "numeric" => "Please enter numeric value for commission",
                        "less_than" => "Amount must be less than current payable commision",
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
        $cash_collecetion_data = [
            'user_id' => $this->userId,
            'message' => $message,
            'status' => 'admin_cash_recevied',
            'commison' => intval($amount),
            'partner_id' => $partner_id,
            'date' => date("Y-m-d"),


        ];
        insert_details($cash_collecetion_data, 'cash_collection');


        $updated_balance = $current_balance['payable_commision'] - intval($amount);
        $update = update_details(['payable_commision' => $updated_balance], ['id' => $partner_id], 'users');
        if ($update) {

            $response = [
                'error' => false,
                'message' => "Successfully collected commision",
                'data' => []
            ];
        } else {
            $response = [
                'error' => true,
                'message' => "Unsuccessful while Updating settling status",
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash(),
                'data' => []
            ];
        }

        return $this->response->setJSON($response);
    }


    public function cash_collection_history()
    {

        if ($this->isLoggedIn && $this->userIsAdmin) {

            $this->data['title'] = 'Cash Collection  | Admin Panel';
            $this->data['main_page'] = 'cash_collection_history';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function settle_commission_history()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {

            $this->data['title'] = 'Commision Settlement  | Admin Panel';
            $this->data['main_page'] = 'commision_history';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function manage_commission_history_list()
    {
        try {
            $this->data['title'] = 'Commission Settlement | Admin Panel';

            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';

            print_r(json_encode($this->settle_commission->list(false, $search, $limit, $offset, $sort, $order)));
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }

    public function cash_collection_history_list()
    {
        // try {
        $this->data['title'] = 'Cash Collectoion Settlement | Admin Panel';


        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        // $where['partner_id'] = $this->userId;

        print_r(json_encode($this->cash_collection->list(false, $search, $limit, $offset, $sort, $order)));
        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }

    public function payment_request_multiple_update()
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
        try {


            $db      = \Config\Database::connect();
            $builder = $db->table('payment_request');

            $count = true;

            for ($i = 0; $i < count($_POST['request_ids']); $i++) {

                $payment_request = fetch_details('payment_request', ['id' => $_POST['request_ids'][$i]]);

                foreach ($payment_request as $row) {
                    if (($row['status'] != $_POST['status'])) {
                        if (($row['status'] == "0" && ($_POST['status'] == "1" || $_POST['status'] == "2" || $_POST['status'] == "3"))) {
                            $builder->where('id', $row['id']);
                            $builder->update(['status' => $_POST['status']]);
                            $count = false;
                        } else if (($row['status'] == "1" && $_POST['status'] == "3")) {
                            $builder->where('id', $row['id']);
                            $builder->update(['status' => $_POST['status']]);
                            $count = false;
                        }
                    }

                    ($count == true) ? $response = [
                        'error' => true,
                        'message' => "Cannot Update",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ]

                        : $response = [
                            'error' => false,
                            'message' => "Bulk update successfully",
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => []
                        ];
                }
            }
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }

        return $this->response->setJSON($response);
    }

    public function payment_request_settement_status()
    {
        try {
            $db     = \Config\Database::connect();
            $builder = $db->table('payment_request');
            $builder->where('id', $_POST['id']);
            $builder->update(['status' => '3']);
            $response = [
                'error' => false,
                'message' => "Payment Request Settled Succssfully",
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash(),
                'data' => []
            ];
            return $this->response->setJSON($response);
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }


    public function bulk_commission_settelement()
    {
        try {
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

            $db      = \Config\Database::connect();
            $builder = $db->table('users');
            $count = true;

            for ($i = 0; $i < count($_POST['request_ids']); $i++) {
                $user_details = fetch_details('users', ['id' => $_POST['request_ids'][$i]]);
                if ($user_details[0]['balance'] > 0) {
                    $count = false;
                    $data = [
                        'balance' => 0,
                        // 'message'=>$_POST['message'],
                    ];
                    $builder->where('id', $_POST['request_ids'][$i]);


                    $builder->update($data);



                    $settlement_history = [

                        'provider_id' => $_POST['request_ids'][$i],
                        'message' =>   $this->request->getPost('message'),
                        'amount' =>  $user_details[0]['balance'],
                        'status' => 'credit',
                        'date' => date("Y-m-d H:i:s"),
                    ];
                    insert_details($settlement_history, 'settlement_history');
                }
            }
            ($count == true) ?
                $response = [
                    'error' => true,
                    'message' => "Can not perform Operation",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ] : $response = [
                    'error' => false,
                    'message' => "Bulk update successfully",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }

        return $this->response->setJSON($response);
    }

    public function bulk_cash_collection()
    {
        try {
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

            $db      = \Config\Database::connect();
            $builder = $db->table('users');
            $count = true;


            for ($i = 0; $i < count($_POST['request_ids']); $i++) {
                $user_details = fetch_details('users', ['id' => $_POST['request_ids'][$i]]);


                if ($user_details[0]['payable_commision'] > 0) {
                    $count = false;
                    $builder->where('id', $_POST['request_ids'][$i]);
                    $builder->update(['payable_commision' => 0]);



                    $cash_collecetion_data = [
                        'user_id' => $this->userId,
                        'message' => $this->request->getPost('message'),
                        'status' => 'admin_cash_recevied',
                        'commison' => intval($user_details[0]['payable_commision']),
                        'partner_id' => $_POST['request_ids'][$i],
                        'date' => date("Y-m-d"),


                    ];
                    insert_details($cash_collecetion_data, 'cash_collection');
                }
            }


            ($count == true) ?
                $response = [
                    'error' => true,
                    'message' => "Can not perform Operation",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ] : $response = [
                    'error' => false,
                    'message' => "Bulk update successfully",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }

        return $this->response->setJSON($response);
    }

    public function provider_details()
    {
        helper('function');
        $uri = service('uri');
        $partner_id = $uri->getSegments()[3];
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        // echo "<pre/>";
        // print_r(($this->partner->list(false, $search, $limit, $offset, $sort, $order, ["pd.partner_id " => $partner_id])));
        // die;
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Partner Detail | Admin Panel';
            $this->data['main_page'] = 'provider_details';



            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }


    public function general_outlook()
    {

        $uri = service('uri');

        helper('function');
        $partner_id = $uri->getSegments()[3];
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $this->data['partner'] = (($this->partner->list(false, $search, $limit, $offset, $sort, $order, ["pd.partner_id " => $partner_id])));






        $db = \Config\Database::connect();
        $id =  $uri->getSegments()[3];
        $builder = $db->table('orders o');
        $order_count = $builder->select('count(DISTINCT(o.id)) as total')->where(['o.partner_id' => $id])->get()->getResultArray();

        $total_services = $db->table('services s')->select('count(s.id) as `total`')->where(['user_id' => $id])->get()->getResultArray()[0]['total'];
        $total_balance = unsettled_commision($id);
        $total_promocodes = $db->table('promo_codes p')->select('count(p.id) as `total`')->where(['partner_id' => $id])->get()->getResultArray()[0]['total'];




        $provider_total_earning_chart = provider_total_earning_chart($id);

        $provider_already_withdraw_chart = provider_already_withdraw_chart($id);

        $provider_pending_withdraw_chart = provider_pending_withdraw_chart($id);
        $provider_withdraw_chart = provider_withdraw_chart($id);


        $promocode_model = new Promo_code_model();


        $where['partner_id'] =  $uri->getSegments()[3];

        $db = \Config\Database::connect();
        $id = $this->userId;
        $promo_codes = $db->table('promo_codes')->where(['partner_id' => $id])->where('start_date >', date('Y-m-d'))->orderBy('id', 'DESC')->limit(5, 0)->get()->getResultArray();


        $promocode_dates = array();
        $tempRow = array();
        $promocode_dates = array();
        foreach ($promo_codes as $promo_code) {
            $date = explode('-', $promo_code['start_date']);
            $newDate = $date[1] . '-' . $date[2];
            $newDate = explode(' ', $newDate);
            $newDate = $newDate[0];
            $tempRow['start_date'] = $newDate;
            $tempRow['promo_code'] = $promo_code['promo_code'];
            $tempRow['end_date'] = $promo_code['end_date'];

            $promocode_dates[] = $tempRow;
        }


        $ratings = new Service_ratings_model();
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 0;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $data = $ratings->ratings_list(true, $search, $limit, $offset, $sort, $order, ['s.user_id' =>  $uri->getSegments()[3]]);

        $total_review = $data['total'];


        $total_ratings = $db->table('partner_details p')->select('count(p.ratings) as `total`')->where(['id' => $id])->get()->getResultArray()[0]['total'];


        $already_withdraw = $db->table('payment_request p')->select('sum(p.amount) as total')->where(['user_id' => $id, "status" => 1])->get()->getResultArray()[0]['total'];
        $pending_withdraw = $db->table('payment_request p')->select('sum(p.amount) as total')->where(['user_id' => $id, "status" => 0])->get()->getResultArray()[0]['total'];
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






        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Partner General Outlook | Admin Panel';
            $this->data['main_page'] = 'partner_general_outlook';



            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function partner_company_information()
    {


        helper('function');

        $uri = service('uri');

        helper('function');
        $partner_id = $uri->getSegments()[3];
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $this->data['partner'] = (($this->partner->list(false, $search, $limit, $offset, $sort, $order, ["pd.partner_id " => $partner_id])));

        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Partner Company Information | Admin Panel';
            $this->data['main_page'] = 'partner_company_information';



            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }


    public function partner_service_details()
    {


        helper('function');

        $uri = service('uri');
        $partner_id = $uri->getSegments()[3];
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $this->data['partner'] = (($this->partner->list(false, $search, $limit, $offset, $sort, $order, ["pd.partner_id " => $partner_id])));

        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Partner Service List| Admin Panel';
            $this->data['main_page'] = 'partner_service_list';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }


    public function partner_order_details()
    {


        helper('function');

        $uri = service('uri');
        // Get all segments of the URI
        $segments = $uri->getSegments();
        $partner_id = end($segments);

       
        // $partner_id = $uri->getSegments()[3];
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $this->data['partner'] = (($this->partner->list(false, $search, $limit, $offset, $sort, $order, ["pd.partner_id " => $partner_id])));



        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Partner Order List| Admin Panel';
            $this->data['main_page'] = 'partner_order_list';



            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function partner_order_details_list()
    {

        helper('function');
        $uri = service('uri');
        $partner_id = $uri->getSegments()[3];
        $orders_model = new Orders_model();
        $where = ['o.partner_id' => $partner_id];
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'DESC';

        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';

        return $orders_model->list(false, $search, $limit, $offset, $sort, $order, $where, '', '', '', '', '', '');
    }




    public function partner_promocode_details()
    {


        helper('function');

        $uri = service('uri');

        $partner_id = $uri->getSegments()[3];
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $this->data['partner'] = (($this->partner->list(false, $search, $limit, $offset, $sort, $order, ["pd.partner_id " => $partner_id])));

        $partner_data = $this->db->table('users u')
            ->select('u.id,u.username,pd.company_name')
            ->join('partner_details pd', 'pd.partner_id = u.id')
            ->where('is_approved', '1')
            ->get()->getResultArray();

        $this->data['partner_name'] = $partner_data;


        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Partner Promocode List| Admin Panel';
            $this->data['main_page'] = 'partner_promocode_details';



            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function partner_promocode_details_list()
    {


        helper('function');
        $uri = service('uri');

        $partner_id = $uri->getSegments()[3];
        $promocode_model = new Promo_code_model();

        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $where['partner_id'] = $partner_id;
        // print_R($where);
        $promo_codes =  $promocode_model->list(false, $search, $limit, $offset, $sort, $order, $where);
        return $promo_codes;
    }




    public function partner_review_details()
    {

        if ($this->isLoggedIn && $this->userIsAdmin) {
            helper('function');

            $uri = service('uri');

            $partner_id = $uri->getSegments()[3];
            $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
            $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
            $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
            $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
            $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
            $this->data['partner'] = (($this->partner->list(false, $search, $limit, $offset, $sort, $order, ["pd.partner_id " => $partner_id])));

            $rate_data = get_ratings($partner_id);
            $db      = \Config\Database::connect();
            $average_rating = $db->table('services s')
                ->select(' 
                (SUM(sr.rating) / count(sr.rating)) as average_rating
                ')
                ->join('services_ratings sr', 'sr.service_id = s.id')
                ->where('s.user_id', $partner_id)
                ->get()->getResultArray();
            // print_r($average_rating);
            $ratingData = array();
            $rows = array();
            $tempRow = array();
            foreach ($average_rating as $row) {
                $tempRow['average_rating'] = (isset($row['average_rating']) &&  $row['average_rating'] != "") ?  number_format($row['average_rating'], 2) : 0;
            }

            foreach ($rate_data as $row) {
                $tempRow['total_ratings'] = (isset($row['total_ratings']) && $row['total_ratings'] != "") ? $row['total_ratings'] : 0;
                $tempRow['rating_5_percentage'] = (isset($row['rating_5']) && $row['rating_5'] != "") ? (($row['rating_5'] * 100) / $row['total_ratings']) : 0;
                $tempRow['rating_4_percentage'] = (isset($row['rating_4']) && $row['rating_4'] != "") ? (($row['rating_4'] * 100) / $row['total_ratings'])  : 0;
                $tempRow['rating_3_percentage'] = (isset($row['rating_3']) && $row['rating_3'] != "") ? (($row['rating_3'] * 100) / $row['total_ratings']) : 0;
                $tempRow['rating_2_percentage'] = (isset($row['rating_2']) && $row['rating_2'] != "") ? (($row['rating_2'] * 100) / $row['total_ratings']) : 0;
                $tempRow['rating_1_percentage'] = (isset($row['rating_1']) && $row['rating_1'] != "") ? (($row['rating_1'] * 100) / $row['total_ratings']) : 0;

                $tempRow['rating_5'] = (isset($row['rating_5']) && $row['rating_5'] != "") ? ($row['rating_5']) : 0;
                $tempRow['rating_4'] = (isset($row['rating_4']) && $row['rating_4'] != "") ?  ($row['rating_4'])  : 0;
                $tempRow['rating_3'] = (isset($row['rating_3']) && $row['rating_3'] != "") ?  ($row['rating_3']) : 0;
                $tempRow['rating_2'] = (isset($row['rating_2']) && $row['rating_2'] != "") ?  ($row['rating_2']) : 0;
                $tempRow['rating_1'] = (isset($row['rating_1']) && $row['rating_1'] != "") ? ($row['rating_1']) : 0;
                $rows[] = $tempRow;
                // print_r($row['total_ratings']);
                // (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand";
            }
            $ratingData = $rows;
            $this->data['ratingData'] = $ratingData;
            $this->data['title'] = 'Partner Review List| Admin Panel';
            $this->data['main_page'] = 'partner_review_details';



            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }

        // echo "<pre/>";
        // print_r($ratingData);
        // die;


        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Partner Review List| Admin Panel';
            $this->data['main_page'] = 'partner_review_details';



            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function partner_review_details_list()
    {


        helper('function');
        $uri = service('uri');

        $partner_id = $uri->getSegments()[3];

        $ratings_model = new Service_ratings_model();
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'pd.id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';


        return json_encode($ratings_model->ratings_list(false, $search, $limit, $offset, $sort, $order, ['s.user_id' => $partner_id]));
    }

    public function partner_fetch_sales()
    {
        helper('function');

        $uri = service('uri');

        $partner_id = $uri->getSegments()[3];
        if (!$this->isLoggedIn) {
            return redirect('admin/login');
        } else {
            $sales[] = array();
            $db = \Config\Database::connect();

            $month_res = $db->table('orders')
                ->select('SUM(final_total) AS total_sale,DATE_FORMAT(created_at,"%b") AS month_name ')
                ->where('partner_id', $partner_id)
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


    public function partner_subscription()
    {


        helper('function');
        $uri = service('uri');
        $db      = \Config\Database::connect();
        $builder = $db->table('partner_subscriptions ps');
        $partner_id = $uri->getSegments()[3];


        $active_subscription_details = fetch_details('partner_subscriptions', ['partner_id' => $partner_id, 'status' => 'active']);
        $symbol =   get_currency();
        $this->data['currency'] = $symbol;
        $this->data['active_subscription_details'] = $active_subscription_details;
        $this->data['partner_id'] = $partner_id;

        $subscription_details = fetch_details('subscriptions', ['status' => 1]);

        $this->data['subscription_details'] = $subscription_details;
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Partner Subscription| Admin Panel';
            $this->data['main_page'] = 'partner_subscription';



            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function assign_subscription_to_partner()
    {







        $partner_id = $_POST['partner_id'];
        $subscription_id = $_POST['subscription_id'];
        $subscription_details = fetch_details('subscriptions', ['id' => $subscription_id]);
        $db      = \Config\Database::connect();
        $is_already_subscribe_builder = $db->table('partner_subscriptions')
            ->where(['partner_id' => $partner_id, 'status' => 'active']);
        $active_subscriptions = $is_already_subscribe_builder->get()->getResult();

        if (!empty($active_subscriptions) && !empty($active_subscriptions[0])) {
            $subscriptionToDelete = $active_subscriptions[0];
            $db->table('partner_subscriptions')
                ->where('id', $subscriptionToDelete->id)
                ->delete();
        }
        $price = calculate_subscription_price($subscription_details[0]['id']);
        $purchaseDate = date('Y-m-d'); // Get the current date
        $subscriptionDuration = $subscription_details[0]['duration'];

        if ($subscriptionDuration == "unlimited") {
            $subscriptionDuration = 0;
        }
        $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
        $partner_subscriptions = [
            'partner_id' =>  $partner_id,
            'subscription_id' => $subscription_id,
            'is_payment' => "1",
            'status' => "active",
            'purchase_date' => date('Y-m-d'),
            'expiry_date' =>  $expiryDate,
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
            'transaction_id' => '0',
            'tax_percentage' => $price[0]['tax_percentage']
        ];
        if ($subscription_details[0]['is_commision'] == "yes") {
            $commission = $subscription_details[0]['commission_percentage'];
        } else {
            $commission = 0;
        }
        update_details(['admin_commission' => $commission], ['partner_id' => $partner_id], 'partner_details');


        $data = insert_details($partner_subscriptions, 'partner_subscriptions');
        $errorMessage = "Asssigned Subscription successfully";
        session()->setFlashdata('success', $errorMessage);
        return redirect()->to('admin/partners/partner_subscription/' . $partner_id);

        // return redirect()->back();
    }

    public function cancel_subscription_plan()
    {

        $partner_id = $_POST['partner_id'];

        $db      = \Config\Database::connect();
        $is_already_subscribe_builder = $db->table('partner_subscriptions')
            ->where(['partner_id' => $partner_id, 'status' => 'active']);
        $active_subscriptions = $is_already_subscribe_builder->get()->getResult();


        if (!empty($active_subscriptions) && !empty($active_subscriptions[0])) {
            $subscriptionToDelete = $active_subscriptions[0];

            $data['status'] = 'deactive';
            $res = update_details($data, ['id' => $subscriptionToDelete->id], 'partner_subscriptions', true);
            $db = \Config\Database::connect();
        }
        $errorMessage = "Subscription Cancelled Successfully";
        session()->setFlashdata('success', $errorMessage);
        return redirect()->to('admin/partners/partner_subscription/' . $partner_id);
    }

    public function all_subscription_list()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'All Subscription | Admin Panel';
            $this->data['main_page'] = 'all_subscription_list';
            $symbol =   get_currency();
            $this->data['currency'] = $symbol;
            $uri = service('uri');
            $partner_id = $uri->getSegments()[3];
            $this->data['partner_id'] = $partner_id;
            $subscription_details = fetch_details('subscriptions', ['status' => 1]);

            $this->data['subscription_details'] = $subscription_details;
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }
}
