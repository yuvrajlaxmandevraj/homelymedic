<?php

namespace App\Controllers\admin;

use App\Models\Service_model;

class Services extends Admin
{
    public $validation, $db, $ionAuth, $creator_id, $service;
    public function __construct()
    {
        parent::__construct();
        $this->service = new Service_model();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        $this->ionAuth = new \IonAuth\Libraries\IonAuth();
        $this->creator_id = $this->userId;
        $this->superadmin = $this->session->get('email');
    }
    public function index()
    {

        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Services | Admin Panel';
            $this->data['main_page'] = 'services';
            $this->data['categories_name'] = fetch_details('categories', [], ['id', 'name']);
            // Fetch the hierarchical categories and build the tree
            $this->data['categories_tree'] = $this->getCategoriesTree();

            $partner_data = $this->db->table('users u')
                ->select('u.id,u.username,pd.company_name,pd.number_of_members')
                ->join('partner_details pd', 'pd.partner_id = u.id')
                ->where('is_approved', '1')
                ->get()->getResultArray();

            $this->data['partner_name'] = $partner_data;

            $tax_data = fetch_details('taxes', ['status' => '1'], ['id', 'title', 'percentage']);
            $this->data['tax_data'] = $tax_data;
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    // In your controller or model
    function getCategoriesTree()
    {
        $categories = $this->db->table('categories')->get()->getResultArray();

        // Assuming your 'categories' table has a parent_id field that represents the hierarchical relationship.
        $tree = [];
        foreach ($categories as $category) {
            if (!$category['parent_id']) {
                $tree[] = $this->buildTree($categories, $category);
            }
        }

        return $tree;
    }

    function buildTree(&$categories, $currentCategory)
    {
        $tree = [
            'id' => $currentCategory['id'],
            'text' => $currentCategory['name'],
        ];

        $children = [];
        foreach ($categories as $category) {
            if ($category['parent_id'] == $currentCategory['id']) {
                $children[] = $this->buildTree($categories, $category);
            }
        }

        if (!empty($children)) {
            $tree['children'] = $children;
        }

        return $tree;
    }

    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [], $additional_data = [], $column_name = '', $whereIn = [])
    {
        $Service_model = new Service_model();
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $data = $Service_model->list(false, $search, $limit, $offset, $sort, $order);
        return $data;
    }
    public function add_service()
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

            if (isset($_POST) && !empty($_POST)) {
                $price = $this->request->getPost('price');
                $this->validation->setRules(
                    [
                        'partner' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please select provider"
                            ]
                        ],
                        'title' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter service title"
                            ]
                        ],
                        'categories' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please select category"
                            ]
                        ],
                        'tags' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter service tag"
                            ]
                        ],
                        'description' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter description"
                            ]
                        ],
                        'long_description' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter long description"
                            ]
                        ],
                        'price' => [
                            "rules" => 'required|numeric',
                            "errors" => [
                                "required" => "Please enter price",
                                "numeric" => "Please enter numeric value for price"
                            ]
                        ],
                        'discounted_price' => [
                            "rules" => 'required|numeric|less_than[' . $price . ']',
                            "errors" => [
                                "required" => "Please enter discounted price",
                                "numeric" => "Please enter numeric value for discounted price",
                                "less_than" => "Discounted price should be less than price"
                            ]
                        ],
                        'members' => [
                            "rules" => 'required|numeric',
                            "errors" => [
                                "required" => "Please enter required member for service",
                                "numeric" => "Please enter numeric value for required member"
                            ]
                        ],
                        'duration' => [
                            "rules" => 'required|numeric',
                            "errors" => [
                                "required" => "Please enter duration to perform task",
                                "numeric" => "Please enter numeric value for duration of task"
                            ]
                        ],
                        'max_qty' => [
                            "rules" => 'required|numeric',
                            "errors" => [
                                "required" => "Please enter max quantity allowed for services",
                                "numeric" => "Please enter numeric value for max quantity allowed for services"
                            ]
                        ],
                        'service_image_selector' => [
                            "rules" => 'uploaded[service_image_selector]|ext_in[service_image_selector,png,jpg,gif,jpeg,webp]|max_size[service_image_selector,8496]|is_image[service_image_selector]'
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
                if (isset($_POST['tags'][0]) && !empty($_POST['tags'][0])) {
                    $base_tags = $this->request->getPost('tags');
                    $s_t = $base_tags;
                    $val = explode(',', str_replace(']', '', str_replace('[', '', $s_t[0])));
                    $tags = [];
                    foreach ($val as $s) {
                        $tags[] = json_decode($s, true)['value'];
                    }
                } else {
                    $response = [
                        'error' => true,
                        'message' => "Tags required!",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
                $title = $this->removeScript($this->request->getPost('title'));
                $description = $this->removeScript($this->request->getPost('description'));
                $path = "public/uploads/services/";


                $uploadedFiles = $this->request->getFiles('filepond');



                if (!empty($uploadedFiles)) {
                    $imagefile = $uploadedFiles['service_image_selector'];
                    $files_selector = [];
                    $main_image_name = "";

                    if ($imagefile->isValid()) {
                        $name = $imagefile->getRandomName();
                        if ($imagefile->move($path, $name)) {
                            $main_image_name = 'public/uploads/services/' . $name;
                        }
                    }
                }


                if (!empty($uploadedFiles)) {
                    $imagefile = $uploadedFiles['files'];
                    $files_selector = [];
                    foreach ($imagefile as $key => $img) {
                        if ($img->isValid()) {
                            $name = $img->getName();

                            // Replace symbols with "-"
                            $name = str_replace([' ', '_', '@', '#', '$', '%'], '-', $name);
                            if ($img->move($path, $name)) {
                                $image_name = $name;
                                $files_selector[$key] = "public/uploads/services/" . $image_name;
                            }
                        }
                    }
                    $files = ['files' => !empty($files_selector) ? json_encode($files_selector) : "",];
                }

                if (!empty($uploadedFiles)) {
                    $imagefile = $uploadedFiles['other_service_image_selector'];
                    $other_service_image_selector = [];
                    foreach ($imagefile as $key => $img) {
                        if ($img->isValid()) {
                            $name = $img->getRandomName();
                            if ($img->move($path, $name)) {
                                $image_name = $name;
                                $other_service_image_selector[$key] = "public/uploads/services/" . $image_name;
                            }
                        }
                    }
                    $other_images = ['other_images' => !empty($other_service_image_selector) ? json_encode($other_service_image_selector) : "",];
                }

                $category_id = $this->request->getPost('categories');
                $discounted_price = $this->request->getPost('discounted_price');


                if ($discounted_price >= $price && $discounted_price == $price) {
                    $response = [
                        'error' => true,
                        'message' => "discounted price can not be higher than or equal to the price",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
                $user_id = $this->request->getPost('partner');

                $partner_data = fetch_details('partner_details', ['partner_id' => $this->request->getPost('partner')]);

                if ($this->request->getVar('members') > $partner_data[0]['number_of_members']) {
                    $response = [
                        'error' => true,
                        'message' => "Number Of member could not greater than " . $partner_data[0]['number_of_members'],
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }


                $faqs = $this->request->getPost('faqs');
                if (!empty($faqs)) {
                    $faqData = ['faqs' => !empty($faqs) ? json_encode($faqs) : ""];
                }
                $is_cancelable = (isset($_POST['is_cancelable'])) ? 1 : 0;
                $service = [
                    'user_id' => $user_id,
                    'category_id' => $category_id,
                    'tax_type' => $this->request->getVar('tax_type'),
                    'tax_id' => $this->request->getVar('tax_id'),
                    // 'tax' => $this->request->getPost('tax'),
                    'title' => $title,
                    'description' => $description,
                    'slug' => '',
                    'tags' =>  implode(',', $tags),
                    'price' => $price,
                    'discounted_price' => $discounted_price,
                    'image' => $main_image_name,
                    'other_images' => $other_images['other_images'],
                    'number_of_members_required' => $this->request->getVar('members'),
                    'duration' => $this->request->getVar('duration'),
                    'rating' => 0,
                    'number_of_ratings' => 0,
                    'on_site_allowed' => ($this->request->getPost('on_site') == "on") ? 1 : 0,
                    'is_pay_later_allowed' => ($this->request->getPost('pay_later') == "on") ? 1 : 0,
                    'is_cancelable' => $is_cancelable,
                    'cancelable_till' => $this->request->getVar('cancelable_till'),
                    'max_quantity_allowed' => $this->request->getPost('max_qty'),
                    'status' => (isset($_POST['status'])) ? 1 : 0,
                    'long_description' => (isset($_POST['long_description'])) ? $_POST['long_description'] : "",
                    'files' => isset($files) ? $files : "",
                    'faqs' => isset($faqData) ? $faqData : "",
                    'at_store' => (isset($_POST['at_store'])) ? 1 : 0,
                    'at_doorstep' => (isset($_POST['at_doorstep'])) ? 1 : 0,



                ];




                if ($this->service->save($service)) {

                    $response = [
                        'error' => false,
                        'message' => "Service saved successfully!",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "Service can not be saved!",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
            } else {
                return redirect()->to('partner/services');
            }
        } else {
            return redirect('partner/login');
        }
    }
    public function delete_service()
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
            $old_data = fetch_details('services', ['id' => $id], ['image']);
            if ($old_data[0]['image'] != NULL &&  file_exists($old_data[0]['image'])) {
                unlink($old_data[0]['image']);
            }
            $builder = $this->db->table('services')->delete(['id' => $id]);

            $builder2 = $this->db->table('cart')->delete(['service_id' => $id]);
            $builder3 = $this->db->table('services_ratings')->delete(['service_id' => $id]);

            if ($builder) {
                $response = [
                    'error' => false,
                    'message' => "success in deleting the service",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => "Unsuccessful in deleting services",
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
    public function update_service()
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
            if (isset($_POST) && !empty($_POST)) {
                $price = $this->request->getPost('price');
                $rules = [
                    'partner' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please select provider"
                        ]
                    ],
                    'title' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter service title"
                        ]
                    ],
                    'categories' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please select category"
                        ]
                    ],
                    'tags' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter service tag"
                        ]
                    ],
                    'description' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter description"
                        ]
                    ],
                    'price' => [
                        "rules" => 'required|numeric',
                        "errors" => [
                            "required" => "Please enter price",
                            "numeric" => "Please enter numeric value for price"
                        ]
                    ],
                    'discounted_price' => [
                        "rules" => 'required|numeric|less_than[' . $price . ']',
                        "errors" => [
                            "required" => "Please enter discounted price",
                            "numeric" => "Please enter numeric value for discounted price",
                            "less_than" => "Discounted price should be less than price"
                        ]
                    ],
                    'members' => [
                        "rules" => 'required|numeric',
                        "errors" => [
                            "required" => "Please enter required member for service",
                            "numeric" => "Please enter numeric value for required member"
                        ]
                    ],
                    'duration' => [
                        "rules" => 'required|numeric',
                        "errors" => [
                            "required" => "Please enter duration to perform task",
                            "numeric" => "Please enter numeric value for duration of task"
                        ]
                    ],
                    'max_qty' => [
                        "rules" => 'required|numeric',
                        "errors" => [
                            "required" => "Please enter max quantity allowed for services",
                            "numeric" => "Please enter numeric value for max quantity allowed for services"
                        ]
                    ],
                ];
                if (isset($_FILES['service_image_selector']) && $_FILES['service_image_selector']['size'] > 0) {
                    $rules['service_image_selector'] = [
                        "rules" => 'uploaded[service_image_selector]|ext_in[service_image_selector,png,jpg,gif,jpeg,webp]|max_size[service_image_selector,8496]|is_image[service_image_selector]'
                    ];
                }
                $this->validation->setRules($rules);
                if (!$this->validation->withRequest($this->request)->run()) {
                    $errors  = $this->validation->getErrors();
                    $response['error'] = true;
                    $response['message'] = $errors;
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    $response['data'] = [];
                    return $this->response->setJSON($response);
                }
                $Service_id = $this->request->getPost('service_id');
                $old_files = fetch_details('services', ['id' => $Service_id], ['files'])[0]['files'];
                $old_other_images = fetch_details('services', ['id' => $Service_id], ['other_images'])[0]['other_images'];
                $old_icon = fetch_details('services', ['id' => $Service_id], ['image'])[0]['image'];
                if (isset($_POST['tags'][0]) && !empty($_POST['tags'][0])) {
                    $base_tags = $this->request->getPost('tags');
                    $s_t = $base_tags;
                    $val = explode(',', str_replace(']', '', str_replace('[', '', $s_t[0])));
                    $tags = [];
                    foreach ($val as $s) {
                        $tags[] = json_decode($s, true)['value'];
                    }
                } else {
                    $response = [
                        'error' => true,
                        'message' => "Tags required!",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
                $title = $this->removeScript($this->request->getPost('title'));
                $description = $this->removeScript($this->request->getPost('description'));
                $path = "public/uploads/services/";
                $image_name = "";
                $og_image = fetch_details('services', ['id' => $Service_id], ['image']);
                $files = fetch_details('services', ['id' => $Service_id], ['files']);
                $other_images = fetch_details('services', ['id' => $Service_id], ['other_images']);





                $faqs = $this->request->getPost('faqs');

                if (!empty($faqs)) {

                    $faqData = ['faqs' => !empty($faqs) ? json_encode($faqs) : ""];
                }

                $uploadedFiles = $this->request->getFiles('filepond');


                if (!empty($uploadedFiles['service_image_selector_edit']) && $uploadedFiles['service_image_selector_edit']->getError() === UPLOAD_ERR_OK) {


                    $imagefile = $uploadedFiles['service_image_selector_edit'];


                    if ($imagefile->isValid()) {
                        $name = $imagefile->getRandomName();
                        if ($imagefile->move($path, $name)) {

                            if (file_exists(FCPATH . $old_icon)) {
                                unlink(FCPATH . $old_icon);
                            }
                            $image_name = 'public/uploads/services/' . $name;
                        }
                    }
                } else {

                    if (isset($og_image) && !empty($og_image)) {
                        $image_name = $og_image['0']['image'];
                    } else {
                        $image_name = NULL;
                    }
                }




                if (!empty($uploadedFiles['other_service_image_selector_edit'][0]) && $uploadedFiles['other_service_image_selector_edit'][0]->getError() === UPLOAD_ERR_OK) {

                    // if (!empty($uploadedFiles['other_service_image_selector_edit'])  && $uploadedFiles['other_service_image_selector_edit']->getError() === UPLOAD_ERR_OK) {


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
                                $other_service_image_selector[$key] = "public/uploads/services/" . $other_image_name;
                            }
                        }
                    }
                    $other_images[0] = ['other_images' => !empty($other_service_image_selector) ? json_encode($other_service_image_selector) : "",];
                } else {
                    $other_images = $other_images;
                }

                if (!empty($uploadedFiles['files_edit'][0]) && $uploadedFiles['files_edit'][0]->getError() === UPLOAD_ERR_OK) {
                    $imagefile = $uploadedFiles['files_edit'];
                    $files_selector = [];

                    foreach ($imagefile as $key => $img) {
                        if ($img->isValid()) {
                            $name = $img->getName();

                            // Replace symbols with "-"
                            $name = str_replace([' ', '_', '@', '#', '$', '%'], '-', $name);

                            if ($img->move($path, $name)) {
                                if (!empty($old_files)) {
                                    $old_files_images_array = json_decode($old_files, true); // Decode JSON string to associative array
                                    foreach ($old_files_images_array as $old) {
                                        if (file_exists(FCPATH . $old)) {
                                            unlink(FCPATH . $old);
                                        }
                                    }
                                }
                                $file_image_name = $name;
                                $files_selector[$key] = "public/uploads/services/" . $file_image_name;
                            }
                        }
                    }

                    $files = ['files' => !empty($files_selector) ? json_encode($files_selector) : ""];
                } else {
                    if (isset($files) && !empty($files)) {
                        $files = $files['0']['files'];
                    } else {
                        $files = NULL;
                    }
                }




                $category_id = $_POST['categories'];
                $discounted_price = $this->request->getPost('discounted_price');
                $price = $this->request->getPost('price');
                if ($discounted_price >= $price && $discounted_price == $price) {
                    $response = [
                        'error' => true,
                        'message' => "discounted price can not be higher than or equal to the price",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
                $user_id = $this->request->getPost('partner');
                if (isset($_POST['is_cancelable'])) {
                    $is_cancelable = "1";
                } else {
                    $is_cancelable = "0";
                }
                if ($is_cancelable == "1" && $this->request->getVar('cancelable_till') == "") {
                    $response = [
                        'error' => true,
                        'message' => "Please Add Minutes",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }

                $service = [
                    'user_id' => $user_id,
                    'category_id' => $category_id,
                    'tax_type' => $this->request->getPost('tax_type'),
                    'tax_id' => $this->request->getPost('tax_id'),
                    'tax' => $this->request->getPost('tax'),
                    'title' => $title,
                    'description' => $description,
                    'slug' => '',
                    'tags' =>  implode(',', $tags),
                    'price' => $price,
                    'discounted_price' => $discounted_price,
                    'image' => $image_name,
                    'other_images' => $other_images[0]['other_images'],
                    'number_of_members_required' => $this->request->getPost('members'),
                    'duration' => $this->request->getPost('duration'),
                    'rating' => 0,
                    'number_of_ratings' => 0,
                    'files' => isset($files) ? $files : "",
                    'is_pay_later_allowed' => ($this->request->getPost('pay_later') == "on") ? 1 : 0,
                    'is_cancelable' => $is_cancelable,
                    'cancelable_till' => $this->request->getPost('cancelable_till'),
                    'max_quantity_allowed' => $this->request->getPost('max_qty'),
                    'status' => ($this->request->getPost('status') == "on") ? 1 : 0,
                    'long_description' => (isset($_POST['long_description'])) ? $_POST['long_description'] : "",
                    'faqs' => isset($faqData) ? $faqData : "",
                    'at_store' => (isset($_POST['at_store'])) ? 1 : 0,
                    'at_doorstep' => (isset($_POST['at_doorstep'])) ? 1 : 0,

                ];



                if ($this->service->update($Service_id, $service)) {
                    $response = [
                        'error' => false,
                        'message' => "Service saved successfully!",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "Service can not be Save!",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
            } else {
                return redirect()->to('partner/services');
            }
        } else {
            return redirect('partner/login');
        }
    }



    public function edit_service()
    {
        helper('function');
        $uri = service('uri');
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $service_id = $uri->getSegments()[3];
            // operations sections
            $this->data['title'] = 'Services | Admin Panel';
            $this->data['main_page'] = 'services';
            $this->data['categories_name'] = fetch_details('categories', [], ['id', 'name']);

            $this->data['service'] = fetch_details('services', ['id' => $service_id])[0];


            $partner_data = $this->db->table('users u')
                ->select('u.id,u.username,pd.company_name,at_store,at_doorstep')
                ->join('partner_details pd', 'pd.partner_id = u.id')
                ->where('is_approved', '1')
                ->get()->getResultArray();
            $this->data['partner_name'] = $partner_data;
            $tax_data = fetch_details('taxes', ['status' => '1'], ['id', 'title', 'percentage']);
            $this->data['tax_data'] = $tax_data;
            $this->data['main_page'] = 'edit_service';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function add_service_view()
    {

        if ($this->isLoggedIn && $this->userIsAdmin) {


            // try {
            $permission = is_permitted($this->creator_id, 'create', 'services');
            if ($permission) {
                if ($this->isLoggedIn && $this->userIsAdmin) {
                    $this->data['title'] = 'Add Service | Admin Panel';
                    $this->data['main_page'] = 'add_service';
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
                    $this->data['categories_name'] = fetch_details('categories', [], ['id', 'name']);
                    // Fetch the hierarchical categories and build the tree
                    $this->data['categories_tree'] = $this->getCategoriesTree();

                    $partner_data = $this->db->table('users u')
                        ->select('u.id,u.username,pd.company_name,pd.number_of_members,pd.at_store,pd.at_doorstep')
                        ->join('partner_details pd', 'pd.partner_id = u.id')
                        ->where('is_approved', '1')
                        ->get()->getResultArray();

                    $this->data['partner_name'] = $partner_data;

                    $tax_data = fetch_details('taxes', ['status' => '1'], ['id', 'title', 'percentage']);
                    $this->data['tax_data'] = $tax_data;

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

        // } catch (\Exception $th) {
        //     $response['error'] = true;
        //     $response['message'] = 'Something went wrong';
        //     return $this->response->setJSON($response);
        // }
    }

    public function service_detail()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $uri = service('uri');
            $service_id = $uri->getSegments()[3];

            $this->data['title'] = 'Services | Admin Panel';
            $this->data['main_page'] = 'service_details';
            $this->data['categories_name'] = fetch_details('categories', [], ['id', 'name']);
            // Fetch the hierarchical categories and build the tree
            $this->data['categories_tree'] = $this->getCategoriesTree();

            $partner_data = $this->db->table('users u')
                ->select('u.id,u.username,pd.company_name,pd.number_of_members')
                ->join('partner_details pd', 'pd.partner_id = u.id')
                ->where('is_approved', '1')
                ->get()->getResultArray();

            $this->data['partner_name'] = $partner_data;
            $tax_data = fetch_details('taxes', ['status' => '1'], ['id', 'title', 'percentage']);
            $service = fetch_details('services', ['id' => $service_id]);
            $this->data['service'] = $service;
        //      echo "<pre>";
        //    print_R($service);
        //      die;
            $this->data['tax_data'] = $tax_data;
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
}
