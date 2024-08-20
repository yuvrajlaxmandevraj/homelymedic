<?php

namespace App\Controllers\admin;

use App\Models\Notification_model;

class Notification extends Admin
{
    public   $validation, $notification, $db;
    public function __construct()
    {
        parent::__construct();
        helper(['form', 'url']);
        $this->notification = new Notification_model();
        $this->validation = \Config\Services::validation();
        $this->db      = \Config\Database::connect();
        $this->superadmin = $this->session->get('email');
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Send Notification | Admin Panel';
            $this->data['main_page'] = 'notification';
            $this->data['categories_name'] = fetch_details('categories', [], ['id', 'name']);
            $this->data['users'] = fetch_details('users', [], ['id', 'username']);
            $this->data['partners'] = fetch_details('partner_details', []);
            // $this->data['partners'] = fetch_details('partner_details', []);
            // echo "<pre/>";
            // print_r(($this->data['partners']));
            // die;
            $this->data['notification'] = fetch_details('notifications');
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }

    public function add_notification()
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
            $type = ($this->request->getPost('type'));
            if (isset($type) && $type  == "specific_user") {
                $this->validation->setRules(
                    [
                        'title' => [
                            "rules" => 'required|trim',
                            "errors" => [
                                "required" => "Please enter title for notification"
                            ]
                        ],
                        'user_ids' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please select atleast one user",
                            ]
                        ],
                        // 'image' => [
                        //     "rules" => 'uploaded[image]|ext_in[image,png,jpg,gif,jpeg,webp]|max_size[image,8496]|is_image[image]'
                        // ],
                        'message' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter message for notification"
                            ]
                        ],
                    ],
                );
            } else {
                $this->validation->setRules(
                    [
                        'type' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please select type of notification"
                            ]
                        ],
                        'title' => [
                            "rules" => 'required|trim',
                            "errors" => [
                                "required" => "Please enter title for notification"
                            ]
                        ],
                        // 'image' => [
                        //     "rules" => 'uploaded[image]|ext_in[image,png,jpg,gif,jpeg,webp]|max_size[image,8496]|is_image[image]'
                        // ],
                        'message' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter message for notification"
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
            $t = time();
            $user_type = $this->request->getPost('user_type');
            // print_r($user_type);
            // die;
            $name = $this->request->getPost('type');
            $image_data = $this->request->getFile('image');
            $image = ($image_data->getName() != "") ? $image_data : '';
            $title = $this->request->getPost('title');
            $message = $this->request->getPost('message');


            if ($user_type == "all_users") {
                $data['user_id'] = ['0'];
                $data['target'] = "all_users";
            } else if ($user_type == "specific_user") {
                $data['user_id'] =  json_encode($_POST['user_ids']);
                $data['target'] = "specific_user";
            } elseif ($user_type == "provider") {
                $data['target'] = "provider";
            } elseif ($user_type == "customer") {
                $data['user_id'] = ['0'];
                $data['target'] = "customer";
            } else {
                $id = "000";
            }
            $ext = ($image != "") ? $image->getExtension() : '';
            $image_name = ($image != "") ? $t . '.' . $ext : '';


            $data['title'] = $title;
            $data['message'] = $message;
            $data['type'] = $name;


            if ($name == "general") {
                $data['type_id'] = "-";
            } else if ($name == "provider") {
                $data['type_id'] = $_POST['partner_id'];
                $data['user_id'] =  json_encode($_POST['partner_id']);
            } else if ($name == "category") {
                $data['type_id'] = $_POST['category_id'];
            } else if ($name == "url") {
                $data['type_id'] = "0";
            }
            if ($name == "general") {
                $data['notification_type'] = "general";
            } else if ($name == "provider") {
                $data['notification_type'] = "provider";
            } else if ($name == "category") {
                $data['notification_type'] = "category";
            } else if ($name == "url") {
                $data['notification_type'] = "url";
            }
            $data['image'] = $image_name;
            $path = "/public/uploads/notification/";
            if ($ext != '') {
                move_file($image, $path, $image_name);
            }
            $fcm_server_key = get_settings('api_key_settings', true)['firebase_server_key'];



            if ($this->notification->save($data)) {

                //if user type is all users
                if ($user_type == "all_users") {
                    if (empty($fcm_server_key)) {
                        $response = [
                            'error' => true,
                            'message' => "No FCM key Found Please provide server key",
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => []
                        ];
                        return $this->response->setJSON($response);
                    }
                    $where = "fcm_id IS NOT NULL AND fcm_id != '' AND platform IS NOT NULL AND platform!=''";
                    $users_fcm = $this->db->table('users')->select('fcm_id,platform')->where($where)->get()->getResultArray();
                    // print_R($users_fcm);
                    // die;

                    $fcm_ids = [];
                    foreach ($users_fcm as $ids) {
                        if ($ids['fcm_id'] != "") {
                            $fcm_ids['fcm_id'] = $ids['fcm_id'];
                            $fcm_ids['platform'] = $ids['platform'];
                        }
                        $registrationIDs[] = $fcm_ids;
                    }
                }
                //if user type is specifc user
                else if ($user_type == "specific_user") {
                    $to_send_id = $_POST['user_ids'];
                    $builder = $this->db->table('users')->select('fcm_id,platform');
                    $users_fcm = $builder->whereIn('id', $to_send_id)->get()->getResultArray();


                    if (empty($fcm_server_key)) {
                        $response = [
                            'error' => true,
                            'message' => "No FCM key Found Please provide server key",
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => []
                        ];
                        return $this->response->setJSON($response);
                    }
                    foreach ($users_fcm as $ids) {
                        if ($ids['fcm_id'] != "") {
                            $fcm_ids['fcm_id'] = $ids['fcm_id'];
                            $fcm_ids['platform'] = $ids['platform'];
                        }
                        $registrationIDs[] = $fcm_ids;
                    }

                    // if (!empty($fcm_ids)) {
                    //     $registrationIDs = $fcm_ids;
                    // } else {
                    //     $registrationIDs = array();
                    // }
                }

                //if user type is provider
                else if ($user_type == "provider") {


                    $partner = fetch_details('partner_details', ['partner_id' => $_POST['partner_id']]);

                    foreach ($partner as $row) {
                        $to_send_id[] = $row['partner_id'];
                    }

                    $builder = $this->db->table('users')->select('fcm_id,platform');
                    $users_fcm = $builder->whereIn('id', $to_send_id)->get()->getResultArray();


                    if (empty($fcm_server_key)) {
                        $response = [
                            'error' => true,
                            'message' => "No FCM key Found Please provide server key",
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => []
                        ];
                        return $this->response->setJSON($response);
                    }
                    foreach ($users_fcm as $ids) {
                        if ($ids['fcm_id'] != "") {
                            $fcm_ids['fcm_id'] = $ids['fcm_id'];
                            $fcm_ids['platform'] = $ids['platform'];
                        }
                        $registrationIDs[] = $fcm_ids;
                    }
                }
                //if user type is customer 
                else if ($user_type == "customer") {
                    $db      = \Config\Database::connect();
                    $builder = $db->table('users u');
                    $builder->select('u.*,ug.group_id')
                        ->join('users_groups ug', 'ug.user_id = u.id')
                        ->where('ug.group_id', "2");
                    $user_record = $builder->orderBy('id', 'DESC')->limit(0, 0)->get()->getResultArray();
                    foreach ($user_record as $row) {
                        $to_send_id[] = $row['id'];
                    }
                    $users_fcm = $builder->whereIn('id', $to_send_id)->get()->getResultArray();
                    foreach ($users_fcm as $ids) {
                        if ($ids['fcm_id'] != "") {
                            $fcm_ids['fcm_id'] = $ids['fcm_id'];
                            $fcm_ids['platform'] = $ids['platform'];
                        }
                        $registrationIDs[] = $fcm_ids;
                    }

                    // if (!empty($fcm_ids)) {
                    //     $registrationIDs = $fcm_ids;
                    // } else {
                    //     $registrationIDs = array();
                    // }
                }



                //if notification type is general

                if ($name == "general") {
                    if ($ext != '') {
                        $fcmMsg = array(
                            'content_available' => true,
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'type_id' => $data['type_id'],
                            'image' => base_url($path) . '/' . $data['image'],
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    } else {
                        $fcmMsg = array(
                            'content_available' => true,
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'type_id' => $data['type_id'],
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    }
                    $registrationIDs_chunks = array_chunk($registrationIDs, 1000);




                    $not_data =  send_notification($fcmMsg, $registrationIDs_chunks);
                    $response = [
                        'error' => false,
                        'message' => "Send notification successfully",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => [$not_data]
                    ];
                    return $this->response->setJSON($response);
                } else if ($name == "provider") {
                    $provider_builder = $this->db->table('partner_details');
                    $provider_data = $provider_builder->where('partner_id', $_POST['partner_id'])->get()->getResultArray();

                    if ($ext != '') {
                        $fcmMsg = array(
                            'content_available' => true,
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'provider_id' => $provider_data[0]['partner_id'],
                            'provider_name' => $provider_data[0]['company_name'],
                            'type_id' => $data['type_id'],
                            'image' => base_url($path) . '/' . $data['image'],
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    } else {
                        $fcmMsg = array(
                            'content_available' => true,
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'provider_id' => $data['type_id'],
                            'provider_name' => $provider_data[0]['company_name'],
                            'type_id' => $data['type_id'],
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    }
                    $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
                    $not_data =  send_notification($fcmMsg, $registrationIDs_chunks);
                    $response = [
                        'error' => false,
                        'message' => "Send notification successfully",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => [$not_data]
                    ];
                    return $this->response->setJSON($response);
                } elseif ($name == "category") {
                    $builder = $this->db->table('categories')->select('id,name,parent_id');
                    $category_data = $builder->where('id', $_POST['category_id'])->get()->getResultArray();

                    if ($ext != '') {
                        $fcmMsg = array(
                            'content_available' => true,
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'category_id' => $data['type_id'],
                            'parent_id' => $category_data[0]['parent_id'],
                            'category_name' => $category_data[0]['name'],
                            'type_id' => $data['type_id'],
                            'image' => base_url($path) . '/' . $data['image'],
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    } else {
                        $fcmMsg = array(
                            'content_available' => true,
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'category_id' => $data['type_id'],
                            'parent_id' => $category_data[0]['parent_id'],
                            'category_name' => $category_data[0]['name'],
                            'type_id' => $data['type_id'],
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    }

                    $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
                    $not_data =  send_notification($fcmMsg, $registrationIDs_chunks);
                    $response = [
                        'error' => false,
                        'message' => "Send notification successfully",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => [$not_data]
                    ];
                    return $this->response->setJSON($response);
                } elseif ($name == "url") {
                    if ($ext != '') {
                        $fcmMsg = array(
                            'content_available' => true,
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'url' => $_POST['url'],
                            'type_id' => $data['type_id'],
                            'image' => base_url($path) . '/' . $data['image'],
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    } else {
                        $fcmMsg = array(
                            'content_available' => true,
                            'title' => "$title",
                            'body' => "$message",
                            'type' => $name,
                            'url' => $_POST['url'],
                            'type_id' => $data['type_id'],
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        );
                    }


                    $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
                    $not_data =  send_notification($fcmMsg, $registrationIDs_chunks);
                    $response = [
                        'error' => false,
                        'message' => "Send notification successfully",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => [$not_data]
                    ];
                    return $this->response->setJSON($response);
                }
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
        } else {
            return redirect('admin/login');
        }
    }
    public function list()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $data = $this->notification->list(false, $search, $limit, $offset, $sort, $order);
        return $data;
    }
    public function  delete_notification()
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
            $id = $this->request->getPost('user_id');
            $icons = fetch_details('notifications', ['id' => $id]);
            $image = ($icons[0] != '') ? $icons[0]['image'] : '';
            $db      = \Config\Database::connect();
            $builder = $db->table('notifications');
            if ($builder->delete(['id' => $id])) {
                $path = ($image != "") ? "public/uploads/notification/" . $image : '';
                if ($image != "") {
                    unlink($path);
                }
                $response = [
                    'error' => false,
                    'message' => 'Notification
                    deleted successfully',
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
    }
}
