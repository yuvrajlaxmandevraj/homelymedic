<?php

namespace App\Controllers\admin;

use App\Models\Slider_model;

class Sliders extends Admin
{
    public $sliders, $creator_id;
    public function __construct()
    {
        parent::__construct();
        $this->sliders = new Slider_model();
        $this->creator_id = $this->userId;
        $this->db = \Config\Database::connect();
        $this->validation = \Config\Services::validation();
        $this->superadmin = $this->session->get('email');
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Sliders | Admin Panel';
            $this->data['main_page'] = 'sliders';
            $this->data['categories_name'] = fetch_details('categories', [], ['id', 'name']);
            $provider_data = fetch_details('partner_details', [], ['id', 'company_name']);
            $service_data = $this->db->table('services s')
                ->select('s.id,s.title')
                ->join('users u', 's.user_id = u.id')
                ->where('status', '1')
                ->get()->getResultArray();

            $this->data['services_title'] = $service_data;
            $this->data['provider_title'] = $provider_data;


            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function add_slider()
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
        $permission = is_permitted($this->creator_id, 'create', 'sliders');
        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $type = $this->request->getPost('type');

                if ($type == "Category") {
                    $this->validation->setRules(
                        [
                            'Category_item' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select category"
                                ]
                            ],
                            'image' => [
                                "rules" => 'uploaded[image]|ext_in[image,png,jpg,gif,jpeg,webp]|max_size[image,8496]|is_image[image]'
                            ],
                        ],
                    );
                } else if ($type == "provider") {
                    $this->validation->setRules(
                        [
                            'service_item' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select provider"
                                ]
                            ],
                            'image' => [
                                "rules" => 'uploaded[image]|ext_in[image,png,jpg,gif,jpeg,webp]|max_size[image,8496]|is_image[image]'
                            ],
                        ],
                    );
                } else {
                    $this->validation->setRules(
                        [
                            'type' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select type of slider"
                                ]
                            ],
                            'image' => [
                                'rules' => 'uploaded[image]|ext_in[image,png,jpg,gif,jpeg,webp,bmp,tiff,tif,ico]'
                            ],

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
                }
                $t = time();
                $name = $this->request->getPost('type');
                $image = $this->request->getFile('image');

                if ($name == "Category") {
                    $id = $this->request->getPost('Category_item');
                    $fc_title = fetch_details('categories', ['id' => $id], ['name']);
                } else if ($name == "provider") {
                    $id = $this->request->getPost('service_item');
                    $fc_title = fetch_details('services', ['id' => $id], ['title']);
                } else {
                    $id = "000";
                }
                $ext = $image->getExtension();
                $image_name = $t . '.' . $ext;
                // $image_name = $image->getName();
                $data['type'] = $name;
                $data['type_id'] = $id;
                $data['image'] = $image_name;
                $data['status'] = (isset($_POST['slider_switch'])) ? 1 : 0;
                $path = "/public/uploads/sliders/";
                if ($this->sliders->save($data)) {
                    move_file($image, $path, $image_name);
                    $response = [
                        'error' => false,
                        'message' => "slider added successfully",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "some error occrured",
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
    public function list()
    {

        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        print_r($this->sliders->list(false, $search, $limit, $offset, $sort, $order));
    }

    public function update_slider()
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
        $permission = is_permitted($this->creator_id, 'update', 'sliders');
        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {

                $type = $this->request->getPost('type_1');
                if ($type == "Category") {
                    $this->validation->setRules(
                        [
                            'Category_item_1' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select category"
                                ]
                            ],
                        ],
                    );
                } else if ($type == "services") {
                    $this->validation->setRules(
                        [
                            'service_item_1' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select service"
                                ]
                            ],
                        ],
                    );
                } else {
                    $this->validation->setRules(
                        [
                            'type_1' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please select type of slider"
                                ]
                            ],
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
                }
                $id = $this->request->getPost('id');
                $name = $this->request->getPost('type_1');
                $old_data = fetch_details('sliders', ['id' => $id]);
                $old_image = $old_data[0]['image'];
                // echo $old_image;
                if ($name == "Category") {
                    $type_id = $this->request->getPost('Category_item_1');
                    $fc_title = fetch_details('categories', ['id' => $id], ['name']);
                } else if ($name == "services") {
                    $type_id = $this->request->getPost('service_item_1');
                    $fc_title = fetch_details('services', ['id' => $id], ['title']);
                } else {
                    $type_id = "000";
                }

                $image = $this->request->getFile('image');

                $image_name = ($image->getName() == "") ? $old_image :  $image->getName();

                $data['type'] = $name;
                $data['type_id'] = $type_id;
                $data['image'] = $image_name;

               
                $path = "/public/uploads/sliders/";
                $data['status'] = (isset($_POST['edit_slider_switch'])) ? 1 : 0;
                // echo $type_id;
                $path = "/public/uploads/sliders/";
                $old_path = "public/uploads/sliders/" . $old_image;
                if (file_exists(base_url('/public/uploads/sliders/' . $image->getName()))) {
                    if ($image->getName() != '') {
                        unlink($old_path);
                    }
                }
                $upd =  $this->sliders->update($id, $data);
                if ($upd) {
                    if ($image->getName() == "") {
                        $response = [
                            'error' => false,
                            'message' => "slider updated successfully",
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => []
                        ];
                        return $this->response->setJSON($response);
                    } else {

                        if (move_file($image, $path, $image_name)) {
                            $response = [
                                'error' => false,
                                'message' => "slider updated successfully",
                                'csrfName' => csrf_token(),
                                'csrfHash' => csrf_hash(),
                                'data' => []
                            ];
                            return $this->response->setJSON($response);
                        } else {
                            $response = [
                                'error' => true,
                                'message' => "some error while uploading image",
                                'csrfName' => csrf_token(),
                                'csrfHash' => csrf_hash(),
                                'data' => []
                            ];
                            return $this->response->setJSON($response);
                        }
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
        // print_r($image_name);    

    }
    public function delete_sliders()
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
        $permission = is_permitted($this->creator_id, 'delete', 'sliders');
        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $db      = \Config\Database::connect();

                $id = $this->request->getPost('user_id');

                $old_data = fetch_details('sliders', ['id' => $id]);
                $old_image = $old_data[0]['image'];

                $old_path = "public/uploads/sliders/" . $old_image;
                $builder = $db->table('sliders');
                if ($builder->delete(['id' => $id])) {
                    unlink($old_path);
                    $response = [
                        'error' => false,
                        'message' => "Successfully deleted",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "some error occrured",
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
}
