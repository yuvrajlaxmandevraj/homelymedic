<?php

namespace App\Controllers\partner;

use App\Models\Service_model;

class Services extends Partner
{
    public $service, $validations, $db;
    public function __construct()
    {
        parent::__construct();
        $this->service = new Service_model();
        $this->validation = \Config\Services::validation();
        $this->db      = \Config\Database::connect();
    }

    public function index()
    {
        if ($this->isLoggedIn) {
            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }
            $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);       if (empty($is_already_subscribe)) {
                return redirect('partner/subscription');
            }

            $tax_details = fetch_details('taxes', ['status' => 1]);
            $this->data['title'] = 'Services | Partner Panel';
            $this->data['main_page'] = 'services';
            $this->data['tax_details'] = $tax_details;
            $this->data['tax'] = get_settings('system_tax_settings', true);
            $this->data['categories'] = fetch_details('categories', []);
            $tax_data = fetch_details('taxes', ['status' => '1'], ['id', 'title', 'percentage']);
            $this->data['tax_data'] = $tax_data;

            // print_r($this->data);
            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }
    public function add()
    {
        if ($this->isLoggedIn) {
            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }
            $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);
            if (empty($is_already_subscribe)) {
                return redirect('partner/subscription');
            }

            $this->data['title'] = 'Add Services | Partner Panel';
            $this->data['main_page'] = FORMS . 'add_services';
            $this->data['categories'] = fetch_details('categories', []);
            $this->data['tax'] = get_settings('system_tax_settings', true);
            $tax_details = fetch_details('taxes', ['status' => 1]);
            $this->data['tax_details'] = $tax_details;
            $tax_data = fetch_details('taxes', ['status' => '1'], ['id', 'title', 'percentage']);
            $this->data['tax_data'] = $tax_data;
            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }

    public function add_service()
    {

        
        if ($this->isLoggedIn) {
            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $response['error'] = true;
                $response['message'] = DEMO_MODE_ERROR;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                return $this->response->setJSON($response);
            }
            $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);

            if (empty($is_already_subscribe)) {
                return redirect('partner/subscription');
            }

            if (isset($_POST) && !empty($_POST)) {
                $price = $this->request->getPost('price');
                $this->validation->setRules(
                    [
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
                        // 'image' => [
                        //     "rules" => 'uploaded[image]|ext_in[image,png,jpg,gif,jpeg,webp]|max_size[image,8496]|is_image[image]'
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
                } else {
                    if (isset($_POST['tags'][0]) && !empty($_POST['tags'][0])) {
                        $base_tags = $this->request->getPost('tags');
                        $s_t = $base_tags;
                        $val = explode(',', str_replace(']', '', str_replace('[', '', $s_t[0])));
                        $tags = [];
                        foreach ($val as $s) {
                            $tags[] = json_decode($s, true)['value'];
                        }
                    }
                    $title = $this->removeScript($this->request->getPost('title'));
                    $description = $this->removeScript($this->request->getPost('description'));
                    $path = "./public/uploads/services/";
                    if (isset($_POST['service_id']) && !empty($_POST['service_id'])) {
                        $service_id = $_POST['service_id'];
                        $old_icon = fetch_details('services', ['id' => $service_id], ['image'])[0]['image'];
                    } else {
                        $service_id = "";
                        $old_icon = "";
                    }
                    // if (!empty($_FILES['image']) && isset($_FILES['image'])) {
                    //     $file =  $this->request->getFile('image');
                    //     if ($file->isValid()) {
                    //         if ($file->move($path)) {
                    //             if (!empty($old_icon)) {
                    //                 unlink($old_icon);
                    //             }
                    //             $image_name = 'public/uploads/services/' . $file->getName();
                    //         }
                    //     } else {
                    //         $image_name = $old_icon;
                    //     }
                    // }



                    //start


                    $uploadedFiles = $this->request->getFiles('filepond');



                    if (!empty($uploadedFiles)) {
                        $imagefile = $uploadedFiles['image'];
                        $files_selector = [];
                        $main_image_name = "";

                        if ($imagefile->isValid()) {
                            $name = $imagefile->getRandomName();
                            if ($imagefile->move($path, $name)) {
                                $main_image_name = 'public/uploads/services/' . $name;
                            }
                        }
                        // $service_image_selector = ['service_image_selector' => !empty($files_selector) ? json_encode($files_selector) : "",];
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

                    //end

                    if (isset($_POST['sub_category']) && !empty($_POST['sub_category'])) {
                        $category_id = $_POST['sub_category'];
                    } else {
                        $category_id = $_POST['categories'];
                    }
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
                    $partner_data = fetch_details('partner_details', ['partner_id' => $this->ionAuth->getUserId()]);

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


                    $user_id = $this->ionAuth->getUserId();

                    if (isset($_POST['is_cancelable']) && $_POST['is_cancelable'] == 'on') {
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
                    $faqs = $this->request->getPost('faqs');
                    if (!empty($faqs)) {
                        $faqData = ['faqs' => !empty($faqs) ? json_encode($faqs) : ""];
                    }

                    $status = ($this->request->getPost('status') == "on") ? "1" : "0";
                    $service = array(
                        'id' => $service_id,
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
                        'status' => $status,
                        'is_pay_later_allowed' => ($this->request->getPost('pay_later') == "on") ? 1 : 0,
                        'is_cancelable' => $is_cancelable,
                        'cancelable_till' => ($is_cancelable == "1") ? $this->request->getVar('cancelable_till') : '',
                        'max_quantity_allowed' => $this->request->getPost('max_qty'),
                        'long_description' => (isset($_POST['long_description'])) ? $_POST['long_description'] : "",
                        'files' => isset($files) ? $files : "",
                        'faqs' => isset($faqData) ? $faqData : "",
                        'at_store' => ($this->request->getPost('at_store') == "on") ? 1 : 0,
                        'at_doorstep' => ($this->request->getPost('at_doorstep') == "on") ? 1 : 0,


                    );


                    $service_model = new Service_model();
                    if ($service_model->save($service)) {


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
                }
            } else {
                return redirect()->to('partner/services');
            }
        } else {
            return redirect('partner/login');
        }
    }

    public function list()
    {

        

        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $service_model = new Service_model();
        $where['user_id'] = $_SESSION['user_id'];
        $services =  $service_model->list(false, $search, $limit, $offset, $sort, $order, $where);

   
        return $services;
    }

    public function update_service()
    {


        if ($this->isLoggedIn) {
            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $response['error'] = true;
                $response['message'] = DEMO_MODE_ERROR;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                return $this->response->setJSON($response);
            }
            $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);  if (empty($is_already_subscribe)) {
                return redirect('partner/subscription');
            }

            $price = $this->request->getPost('price');
            $this->validation->setRules(
                [
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
            } else {
                $base_tags = $this->request->getPost('tags');;
                $s_t = $base_tags;
                $val = explode(',', str_replace(']', '', str_replace('[', '', $s_t[0])));

                $tags = [];
                foreach ($val as $s) {
                    $tags[] = json_decode($s, true)['value'];
                }


                $service_image = $this->request->getFile('image');
                $id = $this->request->getPost('service_id');
                $og_image = fetch_details('services', ['id' => $id], ['image']);
                $files = fetch_details('services', ['id' => $id], ['files']);
                $other_images = fetch_details('services', ['id' => $id], ['other_images']);

                $path = "public/uploads/services/";
                $image_name = "";

                $og_image = fetch_details('services', ['id' => $id], ['image']);
                $old_files = fetch_details('services', ['id' => $id], ['files'])[0]['files'];
                $old_other_images = fetch_details('services', ['id' => $id], ['other_images'])[0]['other_images'];
                $old_icon = fetch_details('services', ['id' => $id], ['image'])[0]['image'];
                // if (!empty($file) && $file->getName() != "") {


                //     if ($file->isValid()) {
                //         $name = $file->getRandomName();
                //         if ($file->move($path, $name)) {
                //             if (file_exists(($og_image[0]['image'])) && !empty(($og_image[0]['image'])))
                //                 unlink(($og_image[0]['image']));
                //           $image_name = 'public/uploads/services/' . $file->getName();
                //         }
                //     }
                // } else {
                //     if (isset($og_image) && !empty($og_image)) {
                //         $image_name = $og_image['0']['image'];
                //     } else {
                //         $image_name = NULL;
                //     }
                // }



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
                    $files = ['files' => !empty($files_selector) ? json_encode($files_selector) : "",];
                } else {
                    if (isset($files) && !empty($files)) {
                        $files = $files['0']['files'];
                    } else {
                        $files = NULL;
                    }
                }



                $category = $this->request->getPost('categories');
                if ($category == "select_category" || $category == "Select Category") {
                    $response = [
                        'error' => true,
                        'message' => "Please select anything other than Select Category",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
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
                $user_id = $this->ionAuth->user()->row()->id;

                if (isset($_POST['is_cancelable']) && $_POST['is_cancelable'] == 'on') {
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

                $tax_data = fetch_details('taxes', ['id' => $this->request->getVar('edit_tax_id')], ['id', 'title', 'percentage']);
                $faqs = $this->request->getPost('faqs');

                if (!empty($faqs)) {

                    $faqData = ['faqs' => !empty($faqs) ? json_encode($faqs) : ""];
                }


                $data['category_id'] = $category;
                $data['tax_id'] = $this->request->getVar('tax_id');
                $data['tax'] = $this->request->getPost('tax');
                $data['tax_type'] = $this->request->getVar('tax_type');
                $data['title'] = $this->request->getPost('title');
                $data['slug'] = '';
                $data['description'] = $this->request->getPost('description');
                $data['tags'] =  implode(',', $tags);
                $data['price'] = $this->request->getPost('price');
                $data['discounted_price'] = $this->request->getPost('discounted_price');
                $data['image'] = $image_name;

                $data['other_images'] = $other_images[0]['other_images'];

                $data['number_of_members_required'] = $this->request->getPost('members');
                $data['duration'] = $this->request->getPost('duration');
                $data['rating'] = 0;
                $data['number_of_ratings'] = 0;
                $data['max_quantity_allowed'] = $this->request->getPost('max_qty');
                $data['is_pay_later_allowed'] = ($this->request->getPost('pay_later') == "on") ? 1 : 0;
                $data['status'] =  ($this->request->getPost('status') == "on") ? 1 : 0;
                $data['is_cancelable'] = $is_cancelable;
                $data['cancelable_till'] = ($is_cancelable == "1") ? $this->request->getVar('cancelable_till') : '';
                $data['long_description'] = (isset($_POST['long_description'])) ? $_POST['long_description'] : "";
                $data['files'] = isset($files) ? $files : "";
                $data['faqs'] = isset($faqData) ? $faqData : "";
                $data['at_store'] = ($this->request->getPost('at_store') == "on") ? 1 : 0;
                $data['at_doorstep'] = ($this->request->getPost('at_doorstep') == "on") ? 1 : 0;




            
                if ($this->db->table('services')->update($data, ['id' => $id])) {
                    $response = [
                        'error' => false,
                        'message' => "Service has been added",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                    // } else {
                    //     $response = [
                    //         'error' => true,
                    //         'message' => "can not insert Service some issue occurred",
                    //         'csrfName' => csrf_token(),
                    //         'csrfHash' => csrf_hash(),
                    //         'data' => []
                    //     ];
                    // }
                }
            }
        } else {
            return redirect('partner/login');
        }
    }

    public function delete()
    {
        if ($this->isLoggedIn) {
            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $response['error'] = true;
                $response['message'] = DEMO_MODE_ERROR;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                return $this->response->setJSON($response);
            }
            $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);
            if (empty($is_already_subscribe)) {
                return redirect('partner/subscription');
            }

            $id = $this->request->getPost('id');
            $db      = \Config\Database::connect();
            $builder = $db->table('services')->delete(['id' => $id]);
            $builder2 = $this->db->table('cart')->delete(['service_id' => $id]);

            if ($builder) {
                $response = [
                    'error' => false,
                    'message' => 'service deleted successfully',
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => 'service can not be deleted!',
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
            }
            return $this->response->setJSON($response);
        } else {
            return redirect('partner/login');
        }
    }

    public function edit_service()
    {
        helper('function');
        $uri = service('uri');
        if ($this->isLoggedIn) {
            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }
            $service_id = $uri->getSegments()[3];
            $this->data['title'] = 'Edit Services | Partner Panel';
            $this->data['main_page'] = FORMS . 'edit_service';
            $this->data['categories'] = fetch_details('categories', []);
            $this->data['tax'] = get_settings('system_tax_settings', true);
            $tax_details = fetch_details('taxes', ['status' => 1]);
            $this->data['tax_details'] = $tax_details;
            $tax_data = fetch_details('taxes', ['status' => '1'], ['id', 'title', 'percentage']);
            $this->data['service'] = fetch_details('services', ['id' => $service_id])[0];

            $this->data['tax_data'] = $tax_data;

            // echo "<pre/>";
            // print_r($this->data['service']);
            // die;

            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }
}
