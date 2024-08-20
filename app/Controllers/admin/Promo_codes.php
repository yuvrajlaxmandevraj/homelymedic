<?php

namespace App\Controllers\admin;

use App\Models\Promo_code_model;

class Promo_codes extends Admin
{
    public $orders, $creator_id;
    public function __construct()
    {
        parent::__construct();
        $this->promo_codes = new Promo_code_model();
        $this->creator_id = $this->userId;
        $this->db = \Config\Database::connect();
        $this->ionAuth = new \IonAuth\Libraries\IonAuth();
        $this->validation = \Config\Services::validation();
        $this->creator_id = $this->userId;
        $this->superadmin = $this->session->get('email');
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Promo codes | Admin Panel';
            $this->data['main_page'] = 'promo_codes';
                
            $partner_data = $this->db->table('users u')
            ->select('u.id,u.username,pd.company_name')
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
        $promocode_model = new Promo_code_model();
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $data = $promocode_model->admin_list(false, $search, $limit, $offset, $sort, $order);
        return json_encode($data);
    }
    public function delete_promo_code()
    {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $response['error'] = true;
            $response['message'] = DEMO_MODE_ERROR;
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            return $this->response->setJSON($response);
        }
        $permission = is_permitted($this->creator_id, 'delete', 'promo_code');
        if ($permission) {

            if ($this->isLoggedIn && $this->userIsAdmin) {

                $id = $this->request->getPost('id');
                $db      = \Config\Database::connect();
                $builder = $db->table('promo_codes');
                if ($builder->delete(['id' => $id])) {
                    $response = [
                        'error' => false,
                        'message' => 'Promo Codes section deleted successfully',
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'An error occured during deleting this item',
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
    }


    public function add()
    {

        if (!$this->isLoggedIn && !$this->userIsPartner) {
            return redirect('admin/login');
        } else {
            $this->data['title'] = 'Promo codes | Admin Panel';
            $this->data['main_page'] =   'add_promocode'; 
              
            $partner_data = $this->db->table('users u')
            ->select('u.id,u.username,pd.company_name')
            ->join('partner_details pd', 'pd.partner_id = u.id')
            ->where('is_approved', '1')
            ->get()->getResultArray();
       
            $this->data['partner_name'] = $partner_data;


            return view('backend/admin/template', $this->data);
        }
    }
    public function save()
    {   

        
        if (!$this->isLoggedIn && !$this->userIsPartner) {
            return redirect('unauthorised');
        } else {
            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $response['error'] = true;
                $response['message'] = DEMO_MODE_ERROR;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                return $this->response->setJSON($response);
            }
            if (isset($_POST) && !empty($_POST)) {
                $repeat_usage = isset($_POST['repeat_usage']) ? $_POST['repeat_usage'] : '';
                $id = isset($_POST['promo_id']) ? $_POST['promo_id'] : '';
                if ($repeat_usage == 'on' && empty($id) && $id == '') {
                    $this->validation->setRules(
                        [
                            'promo_code' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please enter promo code name"
                                ]
                            ],
                            'start_date' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select start date"
                                ]
                            ],
                            'end_date' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select end date"
                                ]
                            ],
                            'no_of_users' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter number of users",
                                    "numeric" => "Please enter numeric value for number of users",
                                    "greater_than" => "number of users must be greater than 0",
                                ]
                            ],
                            'no_of_repeat_usage' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter number of repeat usage",
                                    "numeric" => "Please enter numeric value for number of repeat usage",
                                    "greater_than" => "number of repeat usage must be greater than 0",
                                ]
                            ],

                            'minimum_order_amount' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter minimum order amount",
                                    "numeric" => "Please enter numeric value for minimum order amount",
                                    "greater_than" => "minimum order amount must be greater than 0",
                                ]
                            ],
                            'discount' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter discount",
                                    "numeric" => "Please enter numeric value for discount",
                                    "greater_than" => "discount must be greater than 0",
                                ]
                            ],
                            'max_discount_amount' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter max discount amount",
                                    "numeric" => "Please enter numeric value for max discount amount",
                                    "greater_than" => "discount amount must be greater than 0",
                                ]
                            ],
                         
                        ],
                    );
                } else {
                    $this->validation->setRules(
                        [
                            'promo_code' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please enter promo code name"
                                ]
                            ],
                            'start_date' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select start date"
                                ]
                            ],
                            'end_date' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select end date"
                                ]
                            ],
                            'no_of_users' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter number of users",
                                    "numeric" => "Please enter numeric value for number of users",
                                    "greater_than" => "number of users must be greater than 0",
                                ]
                            ],

                            'minimum_order_amount' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter minimum order amount",
                                    "numeric" => "Please enter numeric value for minimum order amount",
                                    "greater_than" => "minimum order amount must be greater than 0",
                                ]
                            ],
                            'discount' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter discount",
                                    "numeric" => "Please enter numeric value for discount",
                                    "greater_than" => "discount must be greater than 0",
                                ]
                            ],
                            'max_discount_amount' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter max discount amount",
                                    "numeric" => "Please enter numeric value for max discount amount",
                                    "greater_than" => "discount amount must be greater than 0",
                                ]
                            ],
                            // 'image' => [
                            //     "rules" => 'uploaded[image]|ext_in[image,png,jpg,gif,jpeg,webp]|max_size[image,8496]|is_image[image]'
                            // ],
                        ],
                    );
                }
                if (!$this->validation->withRequest($this->request)->run()) {
                    $errors  = $this->validation->getErrors();
                    $response['error'] = true;
                    $response['message'] = $errors;
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    $response['data'] = [];
                    return $this->response->setJSON($response);
                } else {

                    if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
                        $promo_id = $_POST['promo_id'];
                        $old_image = fetch_details('promo_codes', ['id' => $_POST['promo_id']], ['image'])[0]['image'];
                    } else {
                        $promo_id = '';
                        $old_image = '';
                    }
                    $path = './public/uploads/promocodes/';
                    $image = "";
                    if (!empty($_FILES['image']) && isset($_FILES['image'])) {
                        $file =  $this->request->getFile('image');
                        if ($file->isValid()) {
                            if ($file->move($path)) {
                                if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
                                    if (!empty($old_image)) {
                                        if (file_exists(($old_image)) && !empty(($old_image))){

                                            unlink($old_image);
                                        }
                                    }
                                }
                                $image = 'public/uploads/promocodes/' . $file->getName();
                            } else {
                                $image = $old_image;
                            }
                        } else {
                            $image = $old_image;
                        }
                    } else {
                        $image = $old_image;
                    }
                    // print_r($image);
                    $promocode_model = new Promo_code_model();
                    // $partner_id = $_POST['user_id'];
                    if (isset($_POST['repeat_usage'])) {
                        $repeat_usage = "1";
                    } else {
                        $repeat_usage = "0";
                    }
                    if (isset($_POST['status'])) {
                        $status = "1";
                    } else {
                        $status = "0";
                    }
                    if (isset($_POST['no_of_users'])) {
                        $users = $this->request->getVar('no_of_users');
                    } else {
                        $users = "1";
                    }


                    $promocode = array(
                        'id' => $promo_id,
                        'partner_id' => $this->request->getVar('partner'),
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
                        'no_of_repeat_usage' => $this->request->getVar('no_of_repeat_usage'),
                        'image' => $image,
                        'status' => $status,
                    );

                    $promocode_model->save($promocode);
                    $response = [
                        'error' => false,
                        'message' => 'Promocode saved successfully',
                        'data' => []
                    ];
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    return $this->response->setJSON($response);
                }
            } else {
                return redirect()->back();
            }
        }
    }
    




    public function update()
    {   
       
        if (!$this->isLoggedIn && !$this->userIsPartner) {
            return redirect('unauthorised');
        } else {
            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $response['error'] = true;
                $response['message'] = DEMO_MODE_ERROR;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                return $this->response->setJSON($response);
            }
            if (isset($_POST) && !empty($_POST)) {
                $repeat_usage = isset($_POST['repeat_usage']) ? $_POST['repeat_usage'] : '';
                $id = isset($_POST['promo_id']) ? $_POST['promo_id'] : '';
                
                $id = isset($_POST['promo_id']) ? $_POST['promo_id'] : '';
                if ($repeat_usage == 'on') {
                    $this->validation->setRules(
                        [
                            'promo_code' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please enter promo code name"
                                ]
                            ],
                            'start_date' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select start date"
                                ]
                            ],
                            'end_date' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select end date"
                                ]
                            ],
                            'no_of_users' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter number of users",
                                    "numeric" => "Please enter numeric value for number of users",
                                    "greater_than" => "number of users must be greater than 0",
                                ]
                            ],
                            'no_of_repeat_usage' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter number of repeat usage",
                                    "numeric" => "Please enter numeric value for number of repeat usage",
                                    "greater_than" => "number of repeat usage must be greater than 0",
                                ]
                            ],

                            'minimum_order_amount' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter minimum order amount",
                                    "numeric" => "Please enter numeric value for minimum order amount",
                                    "greater_than" => "minimum order amount must be greater than 0",
                                ]
                            ],
                            'discount' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter discount",
                                    "numeric" => "Please enter numeric value for discount",
                                    "greater_than" => "discount must be greater than 0",
                                ]
                            ],
                            'max_discount_amount' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter max discount amount",
                                    "numeric" => "Please enter numeric value for max discount amount",
                                    "greater_than" => "discount amount must be greater than 0",
                                ]
                            ],
                            // 'image' => [
                            //     "rules" => 'uploaded[image]|ext_in[image,png,jpg,gif,jpeg,webp]|max_size[image,8496]|is_image[image]'
                            // ],
                        ],
                    );
                } else {
                    $this->validation->setRules(
                        [
                            'promo_code' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please enter promo code name"
                                ]
                            ],
                            'start_date' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select start date"
                                ]
                            ],
                            'end_date' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select end date"
                                ]
                            ],
                            'no_of_users' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter number of users",
                                    "numeric" => "Please enter numeric value for number of users",
                                    "greater_than" => "number of users must be greater than 0",
                                ]
                            ],

                            'minimum_order_amount' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter minimum order amount",
                                    "numeric" => "Please enter numeric value for minimum order amount",
                                    "greater_than" => "minimum order amount must be greater than 0",
                                ]
                            ],
                            'discount' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter discount",
                                    "numeric" => "Please enter numeric value for discount",
                                    "greater_than" => "discount must be greater than 0",
                                ]
                            ],
                            'max_discount_amount' => [
                                "rules" => 'required|numeric|greater_than[0]',
                                "errors" => [
                                    "required" => "Please enter max discount amount",
                                    "numeric" => "Please enter numeric value for max discount amount",
                                    "greater_than" => "discount amount must be greater than 0",
                                ]
                            ],
                            // 'image' => [
                            //     "rules" => 'uploaded[image]|ext_in[image,png,jpg,gif,jpeg,webp]|max_size[image,8496]|is_image[image]'
                            // ],
                        ],
                    );
                }
                if (!$this->validation->withRequest($this->request)->run()) {
                    $errors  = $this->validation->getErrors();
                    $response['error'] = true;
                    $response['message'] = $errors;
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    $response['data'] = [];
                    return $this->response->setJSON($response);
                } else {

                    if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
                        $promo_id = $_POST['promo_id'];
                        $old_image = fetch_details('promo_codes', ['id' => $_POST['promo_id']], ['image'])[0]['image'];
                    } else {
                        $promo_id = '';
                        $old_image = '';
                    }

                   
                    $path = './public/uploads/promocodes/';
                    $image = "";
                    if (!empty($_FILES['image']) && isset($_FILES['image'])) {
                        $file =  $this->request->getFile('image');
                        if ($file->isValid()) {
                            if ($file->move($path)) {
                                if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
                                    if (!empty($old_image)) {
                                        if (file_exists(($old_image)) && !empty(($old_image))){
                                        
                                            unlink($old_image);
                                        }
                                    }
                                }
                                $image = 'public/uploads/promocodes/' . $file->getName();
                            } else {
                                $image = $old_image;
                            }
                        } else {
                            $image = $old_image;
                        }
                    } else {
                        $image = $old_image;
                    }
                    // print_r($image);
                    $promocode_model = new Promo_code_model();
                    // $partner_id = $_POST['user_id'];
                    if (isset($_POST['repeat_usage'])) {
                        $repeat_usage = "1";
                    } else {
                        $repeat_usage = "0";
                    }
                    if (isset($_POST['status'])) {
                        $status = "1";
                    } else {
                        $status = "0";
                    }
                    if (isset($_POST['no_of_users'])) {
                        $users = $this->request->getVar('no_of_users');
                    } else {
                        $users = "1";
                    }


                    $promocode = array(
                        'id' => $promo_id,
                        'partner_id' => $this->request->getVar('partner'),
                        'promo_code' => $this->request->getVar('promo_code'),
                        'message' => $this->request->getVar('message'),
                        'start_date' => (format_date($this->request->getVar('start_date'), 'Y-m-d')),
                        'end_date' => (format_date($this->request->getVar('end_date'), 'Y-m-d')),
                        'no_of_users' => $users,
                        'minimum_order_amount' => $this->request->getVar('minimum_order_amount'),
                        'max_discount_amount' => $this->request->getVar('max_discount_amount'),
                        'discount' => $this->request->getVar('discount'),
                        'discount_type' => $this->request->getVar('discount_type'),
                        'repeat_usage' => $repeat_usage,
                        'no_of_repeat_usage' => $this->request->getVar('no_of_repeat_usage'),
                        'image' => $image,
                        'status' => $status,
                    );
                    

                    $promocode_model->save($promocode);
                    $response = [
                        'error' => false,
                        'message' => 'Promocode saved successfully',
                        'data' => []
                    ];
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    return $this->response->setJSON($response);
                }
            } else {
                return redirect()->back();
            }
        }
    }

}
