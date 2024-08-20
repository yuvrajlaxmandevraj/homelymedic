<?php

namespace App\Controllers\admin;

class Profile extends Admin
{
    public function __construct()
    {
        parent::__construct();
        $this->validation = \Config\Services::validation();
    }
    public function index()
    {
        helper('function');
        if ($this->isLoggedIn) {
            $this->data['title'] = 'Profile | Admin Panel';
            $this->data['main_page'] = 'profile';
            $this->data['data'] = fetch_details('users', ['id' => $this->userId])[0];
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function update()
    {




        if ($this->isLoggedIn) {
            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $response['error'] = true;
                $response['message'] = DEMO_MODE_ERROR;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                return $this->response->setJSON($response);
            }

            $config = new \Config\IonAuth();
            $tables = $config->tables;
            // $this->validation->setRules(
            //     [
            //         'username' => [
            //             "rules" => 'required|trim',
            //             "errors" => [
            //                 "required" => "Please enter username",
            //             ],
            //         ],
            //         'phone' => [
            //             "rules" => 'required|numeric|is_unique[' . $tables['users'] . '.phone,id,' . $this->userId . ']',
            //             "errors" => [
            //                 "required" => "Please enter admin's phone number",
            //                 "numeric" => "Please enter numeric phone number",

            //             ],
            //         ],
            //     ],
            // );

            // Add these lines before setting the validation rules
            $phoneNumber = $this->request->getPost('phone');
            $db = \Config\Database::connect();

            // Use Query Builder to check if the phone number exists in the database
            $query = $db->table('users');
            $query->selectCount('id');
            $query->where('phone', $phoneNumber);
            $query->where('id !=', $this->userId); // Exclude the current user's ID

            $count = $query->get()->getRow()->id;

            if ($count > 0) {
                // Phone number is not unique; handle the validation error
                $this->validation->setRules(
                    [
                        'username' => [
                            "rules" => 'required|trim',
                            "errors" => [
                                "required" => "Please enter username",
                            ],
                        ],
                        'phone' => [
                            "rules" => 'required|numeric',
                            "errors" => [
                                "required" => "Please enter admin's phone number",
                                "numeric" => "Please enter a numeric phone number",
                            ],
                        ],
                    ]
                );
            } else {
                // Phone number is unique; continue with your validation rules
                $this->validation->setRules(
                    [
                        'username' => [
                            "rules" => 'required|trim',
                            "errors" => [
                                "required" => "Please enter username",
                            ],
                        ],
                        'phone' => [
                            "rules" => 'required|numeric|is_unique[users.phone,id,' . $this->userId . ']',
                            "errors" => [
                                "required" => "Please enter admin's phone number",
                                "numeric" => "Please enter a numeric phone number",
                            ],
                        ],
                    ]
                );
            }


            if (!$this->validation->withRequest($this->request)->run()) {
                $errors = $this->validation->getErrors();
                $response['error'] = true;
                $response['message'] = $errors;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['data'] = [];

                return $this->response->setJSON($response);
            }
            $data = [
                'username' => $_POST['username'],
                'phone' => $_POST['phone'],
            ];
            $old_image = fetch_details('users', ['id' => $this->userId], ['image']);
            if (isset($_FILES['profile'])) {



                if (!empty($_FILES['profile']) && isset($_FILES['profile'])) {
                    $path =  './public/backend/assets/profiles/';
                    $path_db =  'public/backend/assets/profiles/';
                    $file =  $this->request->getFile('profile');

                    if ($file->isValid()) {
                        if ($file->move($path)) {
                            if (!empty($old_image[0]['image'])) {
                                if (file_exists(FCPATH . "/public/backend/assets/profiles/" . $old_image[0]['image']) && !empty(FCPATH . "/public/backend/assets/profiles/" . $old_image[0]['image'])) {
                                    unlink(FCPATH . "/public/backend/assets/profiles/" . $old_image[0]['image']);
                                }
                            }
                            $image =  $file->getName();
                        }
                    } else {
                        $image = $old_image[0]['image'];
                    }
                } else {
                    $image = $old_image[0]['image'];
                }

                $data['image'] = $image;
            } else {
                $data['image'] = $old_image[0]['image'];
            }

            $status = update_details(
                $data,
                ['id' => $this->userId],
                'users'
            );
            if ($status) {
                if (isset($_POST['old']) && isset($_POST['new']) && ($_POST['new'] != "") && ($_POST['old'] != "")) {

                    $identity = $this->session->get('identity');

                    


                    $change = $this->ionAuth->changePassword($identity, $this->request->getPost('old'), $this->request->getPost('new'),$this->userId);

                    if ($change) {

                        $this->ionAuth->logout();
                        return $this->response->setJSON([
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'error' => false,
                            'message' => "User updated successfully",
                            "data" => $_POST,
                        ]);
                    } else {
                        return $this->response->setJSON([
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'error' => true,
                            'message' => "Old password did not matched.",
                            "data" => $_POST,
                        ]);
                    }
                }

                $this->ionAuth->logout();
                return $this->response->setJSON([
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'error' => false,
                    'message' => "User updated successfully",
                    "data" => $_POST,
                ]);
            } else {
                return $this->response->setJSON([
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'error' => true,
                    'message' => "Something went wrong...",
                    "data" => [],
                ]);
            }
        } else {
            return $this->response->setJSON([
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash(),
                'error' => true,
                'message' => "unauthorized",
                "data" => [],
            ]);
        }
    }
}
