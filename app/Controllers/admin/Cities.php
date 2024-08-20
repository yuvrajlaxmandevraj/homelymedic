<?php

namespace App\Controllers\admin;

use App\Models\City_model;

class Cities extends Admin
{
    public $cities,  $validation, $db;
    public function __construct()
    {
        parent::__construct();
        $this->cities = new City_model();
        $this->validation = \Config\Services::validation();
        $this->db      = \Config\Database::connect();
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Cities | Admin Panel';
            $this->data['main_page'] = 'cities';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }
    public function add_city()
    {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $response['error'] = true;
            $response['message'] = DEMO_MODE_ERROR;
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            return $this->response->setJSON($response);
        }
        $creator_id = $this->userId;
        $permission = is_permitted($creator_id, 'create', 'city');
        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {                
                $this->validation->setRules(
                    [
                        'city_id' => 'permit_empty',
                        'latitude' => 'required|trim',
                        'longitude' => 'required',
                        'city_name' => 'required',
                        'time_to_travel' => 'required',
                        'maximum_delivrable_distance' => 'required',
                        'delivery_charge_method' => 'required'
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
                $latitude = $this->request->getPost('latitude');
                $longitude = $this->request->getPost('longitude');
                $city_data =  fetch_details('cities',['latitude' => $latitude , 'longitude' => $longitude],['id']);

                if(!empty($city_data)){
                    $response = [
                        'error' => true,
                        'message' => 'City Is already inserted',
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }

                $city_id = (isset($_POST['city_id']) && !empty($_POST['city_id'])) ? $_POST['city_id'] : "";
                $data['name'] = $_POST['city_name'];
                $data['time_to_travel'] = $_POST['time_to_travel'];
                $data['max_deliverable_distance'] = $_POST['maximum_delivrable_distance'];
                $data['latitude'] = $_POST['latitude'];
                $data['longitude'] = $_POST['longitude'];
                $data['delivery_charge_method'] = $_POST['delivery_charge_method'];
                if ($data['delivery_charge_method'] == 'fixed_charge') {
                    $data['fixed_charge'] = $_POST['fixed_charge'];
                } else if ($data['delivery_charge_method'] == 'per_km_charge') {
                    $data['per_km_charge'] = $_POST['per_km_charge'];
                } else if ($data['delivery_charge_method'] == 'range_wise_charges') {
                    $range_wise_chargse = [];
                    foreach ($_POST['from_range'] as $key => $range) {
                        if (!empty($_POST['from_range'][$key]) && !empty($_POST['to_range'][$key]) && !empty($_POST['price'][$key])) {
                            $temp = [];
                            $temp['from_range'] = $_POST['from_range'][$key];
                            $temp['to_range'] = $_POST['to_range'][$key];
                            $temp['price'] = $_POST['price'][$key];
                            $range_wise_chargse[] = $temp;
                        }
                    }
                    $data['range_wise_charges'] = json_encode($range_wise_chargse);
                }
                if (!empty($city_id)) {
                    $action = update_details($data, ['id' => $city_id], 'cities');
                    if ($action) {
                        $error = false;
                        $message = "City updated successfully";
                    } else {
                        $error = true;
                        $message = "Some thing get wrong";
                    }
                } else {
                    if (exists(['name' => $_POST['city_name']], 'cities')) {
                        $response = [
                            'error' => true,
                            'message' => 'City Already Exist',
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => []
                        ];
                        return $this->response->setJSON($response);
                    }
                    $action = insert_details($data, 'cities');
                    if ($action) {
                        $error = false;
                        $message = "City added successfully";
                    } else {
                        $error = true;
                        $message = "Some thing get wrong";
                    }
                }


                $response = [
                    'error' => $error,
                    'message' => $message,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                return redirect('unauthorised');
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
        $data = $this->cities->list(false, $search, $limit, $offset, $sort, $order);
        return $data;
    }



    public function edit_city()
    {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $response['error'] = true;
            $response['message'] = DEMO_MODE_ERROR;
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            return $this->response->setJSON($response);
        }
        $creator_id = $this->userId;
        $permission = is_permitted($creator_id, 'update', 'city');
        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $city_id =  $this->request->getPost('id');
                $data['name'] = $this->request->getPost('name');
                $data['latitude'] = $this->request->getPost('latitude');
                $data['longitude'] = $this->request->getPost('longitude');
                $data['delivery_charge_method'] = $this->request->getPost('delivery_charge_method');
                // 

                if ($data['delivery_charge_method'] == 'fixed_charge') {
                    $this->validation->setRules(
                        [
                            'fixed_charge' => 'required',
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
                    $data['fixed_charge'] =  $this->request->getPost('fixed_charge');
                } else if ($data['delivery_charge_method'] == 'per_km_charge') {
                    $this->validation->setRules(
                        [
                            'per_km_charge' => 'required',
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
                    $data['per_km_charge'] =  $this->request->getPost('per_km_charge');
                } else if ($data['delivery_charge_method'] == 'range_wise_charges') {

                    $range_wise_chargse = [];
                    foreach ($this->request->getPost('from_range') as $key => $range) {
                        if (!empty($_POST['from_range'][$key]) && !empty($_POST['to_range'][$key]) && !empty($_POST['price'][$key])) {
                            $temp = [];
                            $temp['from_range'] = $_POST['from_range'][$key];
                            $temp['to_range'] = $_POST['to_range'][$key];
                            $temp['price'] = $_POST['price'][$key];
                            $range_wise_chargse[] = $temp;
                        }
                    }
                    $data['range_wise_charges'] = json_encode($range_wise_chargse);
                }
                $data['time_to_travel'] = $this->request->getPost('time_to_travel');
                $data['max_deliverable_distance'] = $this->request->getPost('maximum_delivrable_distance');


                if ($this->cities->update($city_id, $data)) {
                    $response = [
                        'error' => false,
                        'message' => "Updated the city",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "Could not delete city some error received",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
            } else {
                return redirect('unauthorised');
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

    public function remove_city()
    {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $response['error'] = true;
            $response['message'] = DEMO_MODE_ERROR;
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            return $this->response->setJSON($response);
        }
        $creator_id = $this->userId;
        $permission = is_permitted($creator_id, 'update', 'city');
        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $city_id =  $this->request->getVar('city_id');
                $builder = $this->db->table('cities');
                $builder->where('id', $city_id);
                $data =  $builder->delete();
                if ($data) {
                    $response = [
                        'error' => false,
                        'message' => "Deleted successfully",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => [
                            'id' => $city_id
                        ]
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "Could not delete city some error received",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => [
                            'id' => $city_id
                        ]
                    ];
                    return $this->response->setJSON($response);
                }
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
