<?php

namespace App\Controllers\admin;

use App\Models\Category_model;
use App\Models\Service_model;

class Categories extends Admin
{
    public $category,  $validation;
    public function __construct()
    {
        parent::__construct();
        $this->category = new Category_model();
        $this->validation = \Config\Services::validation();
        $this->service = new Service_model();
        $this->superadmin = $this->session->get('email');
    }
    public function index()
    {



        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Categories | Admin Panel';
            $this->data['main_page'] = 'categories';
            $this->data['categories'] = fetch_details('categories', [], ['id', 'name']);

            $this->data['parent_categories'] = fetch_details('categories', ['parent_id' => '0'], ['id', 'name']);

            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function add_category()
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


        $creator_id = $this->userId;




        $permission = is_permitted($creator_id, 'create', 'categories');



        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $t = time();
                $type = ($this->request->getPost('make_parent'));


                if (isset($type) && $type  == "1") {
                    $this->validation->setRules(
                        [
                            'parent_id' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please select parent category"
                                ]
                            ],
                            'name' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please enter name for category"
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
                            'name' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please enter name for category"
                                ]
                            ],

                            'image' => [
                                "rules" => 'uploaded[image]|ext_in[image,png,jpg,gif,jpeg,webp]|max_size[image,8496]|is_image[image]'
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

                $name = trim($_POST['name']);

                $Category_image = $this->request->getFile('image');
                $ext = $Category_image->getExtension();
                $image_name = $t . '.' . $ext;
                $data['name'] = $name;
                $data['image'] = $image_name;
                $slug = slugify($name);
                $data['slug_name'] = $slug;
                $data['admin_commission'] = "0";
                $data['parent_id'] = $_POST['parent_id'];

                if ($_POST['dark_theme_color'] != "#000000") {

                    $data['dark_color'] = $_POST['dark_theme_color'];
                } else {
                    $data['dark_color'] = "#2A2C3E";
                }

                if ($_POST['light_theme_color'] != "#000000") {

                    $data['light_color'] = $_POST['light_theme_color'];
                } else {
                    $data['light_color'] = "#FFFFFF";
                }
                $data['status'] = 1;

                // for Image



                $path = "/public/uploads/categories/";

                //   //check if directory already exists or not
                if (!is_dir(base_url($path))) {

                    mkdir(base_url($path), 0775, true);
                }
                // $data['status'] = (isset($_POST['changer']) && $_POST['changer'] == "on") ? 1 : 0;
                $image_data = move_file($Category_image, $path, $image_name);


                if (empty($image_data['error'])) {
                    $resize_old_image = $image_data['path'] . $image_data['file_name'];
                    $resize_new_image = $image_data['path'] . 'thumbnail/' . $image_data['file_name'];
                    $resize_thumbnail = $image_data['path'] . 'thumbnail/';
                    // resize_image($resize_old_image, $resize_new_image,$resize_thumbnail);
                }
                if ($this->category->save($data)) {
                    $response = [
                        'error' => false,
                        'message' => "Category added successfully",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "some error while addding category",
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

        die();
    }
    public function list()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $where = [];

        $from_app = false;
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $where['parent_id'] = $_POST['id'];
            $from_app = true;
        }


        $data = $this->category->list($from_app, $search, $limit, $offset, $sort, $order, $where);
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            if (!empty($data['data'])) {

                $response = [
                    'error' => false,
                    'message' => "Sub Categories fetched successfully",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => $data['data']
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Sub Categories not found on this category',
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => $data['data']
                ];
            }
            return $this->response->setJSON($response);
        }

        return $data;
    }


    public function get_categories()
    {

        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $where = [];

        $from_app = false;
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $where['parent_id'] = $_POST['id'];
            $from_app = true;
        }


        $data = $this->category->list($from_app, $search, $limit, $offset, $sort, $order, $where);
        echo json_encode($data);
        return;
    }
    public function update_category()
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


        $creator_id = $this->userId;


        $permission = is_permitted($creator_id, 'update', 'categories');
        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {

                $type = ($this->request->getPost('edit_make_parent'));
                if (isset($type) && $type  == "1") {
                    $this->validation->setRules(
                        [
                            'name' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please enter name for category"
                                ]
                            ],

                            'edit_parent_id' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please select parent category"
                                ]
                            ],
                        ],
                    );
                } else {
                    $this->validation->setRules(
                        [
                            'name' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please enter name for category"
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


                if ($type == "1") {

                    $parent_id = $this->request->getPost(('edit_parent_id'));
                } else {
                    $parent_id = "0";
                }
                $name = $this->request->getPost('name');
                $old_data = fetch_details('categories', ['id' => $id]);
                $old_image = $old_data[0]['image'];


                $image = $this->request->getFile('image');

              
               
                $admin_commision = 0;
                if (!empty($image)) {

                    $image_name = ($image->getName() == "") ? $old_image :  $image->getName();
                } else {
                    $image_name = $old_image;
                }

                // print_R($image_name);
                // die;
                $data['parent_id'] = $parent_id;
                $data['name'] = $name;
                $data['image'] = $image_name;
                $data['admin_commission'] = $admin_commision;
                $data['dark_color'] = $_POST['edit_dark_theme_color'];
                $data['light_color'] = $_POST['edit_light_theme_color'];
                $data['status'] = 1;

                $path = "/public/uploads/categories/";

                $old_path = "public/uploads/categories/" . $old_image;

                if (!empty($image) || !is_null($image)) {

                    if (file_exists(base_url('/public/uploads/categories/' . $image->getName()))) {
                        if ($image->getName() != '') {
                            unlink($old_path);
                        }
                    }
                }
                $upd =  $this->category->update($id, $data);
                if ($upd) {
                    if ((!empty($image) || !is_null($image))) {

                        $image_data = move_file($image, $path, $image_name);
                        $response = [
                            'error' => false,
                            'message' => "Category updated successfully",
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => []
                        ];
                        return $this->response->setJSON($response);
                    } else {
                        $image_data = move_file($image, $path, $image_name);
                        // if (empty($image_data['error'])) {
                        //     // $resize_old_image = $image_data['path'] . $image_data['file_name'];
                        //     // $resize_new_image = $image_data['path'] . 'thumbnail/' . $image_data['file_name'];
                        //     // resize_image($resize_old_image, $resize_new_image);

                        $response = [
                            'error' => false,
                            'message' => "Category updated successfully",
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => []
                        ];
                        return $this->response->setJSON($response);
                        // } else {
                        //     $response = [
                        //         'error' => true,
                        //         'message' => "some error while uploading image",
                        //         'csrfName' => csrf_token(),
                        //         'csrfHash' => csrf_hash(),
                        //         'data' => []
                        //     ];
                        //     return $this->response->setJSON($response);
                        // }
                    }
                }
                // print_r($image_name);    

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

    public function remove_category()
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
        $creator_id = $this->userId;
        $permission = is_permitted($creator_id, 'delete', 'categories');
        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {

                $id = $this->request->getPost('user_id');




                $db      = \Config\Database::connect();
                $builder = $db->table('categories');


                $cart_builder = $db->table('cart');

                $icons = fetch_details('categories', ['id' => $id]);
                $subcategories = fetch_details('categories', ['parent_id' => $id], ['id', 'name']);

                $services = fetch_details('services', ['category_id' => $id], ['id']);



                // $builder->delete(['id' => $sb['id']]);
                foreach ($subcategories as $sb) {
                    $sb['status'] = 0;
                    $this->category->update($sb['id'], $sb);
                }
                foreach ($services as $s) {
                    $s['status'] = 0;
                    $this->service->update($s['id'], $s);
                    $cart_builder->delete(['service_id' => $s['id']]);
                }


                $category_image = $icons[0]['image'];

                if ($builder->delete(['id' => $id])) {
                    $path = "public/uploads/categories/" . $category_image;
                    if (unlink($path)) {
                        $response = [
                            'error' => false,
                            'message' => 'Category Removed successfully',
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => []
                        ];
                        return $this->response->setJSON($response);
                    }
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
}
