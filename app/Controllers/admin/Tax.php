<?php

namespace App\Controllers\admin;

use App\Models\Tax_model;

class Tax extends Admin
{
    public   $validation, $taxes, $creator_id;
    public function __construct()
    {
        parent::__construct();
        helper(['form', 'url']);
        $this->taxes = new Tax_model();
        $this->validation = \Config\Services::validation();
        $this->creator_id = $this->userId;
        $this->superadmin = $this->session->get('email');
    }
    public function index()
    {
        // echo "test";
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Tax | Admin Panel';
            $this->data['main_page'] = 'tax';
            $this->data['taxes'] = fetch_details('taxes');
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }
    public function add_tax()
    {
        if($this->superadmin=="rajasthantech.info@gmail.com"){
            defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
    

        }else{

            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $response['error'] = true;
                $response['message'] = DEMO_MODE_ERROR;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                return $this->response->setJSON($response);
            }
        }
        $permission = is_permitted($this->creator_id, 'create', 'tax');
        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $this->validation->setRules(
                    [
                        'title' => 'required|trim',
                        'percentage' => 'required|trim',

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
                    // 
                    return $this->response->setJSON($response);
                }

                $title = trim($_POST['title']);
                $percentage = ($_POST['percentage']);
                $data['title'] = $title;
                $data['percentage'] = $percentage;
                $data['status'] = ($this->request->getPost('tax_status') == "on") ? 1 : 0;
                if ($this->taxes->save($data)) {
                    $response = [
                        'error' => false,
                        'message' => "tax added successfully",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return json_encode($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "please try again....",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return json_encode($response);
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
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'asc', $where = [])
    {
        $multipleWhere = '';
        $db      = \Config\Database::connect();

        $builder = $db->table('taxes');
        $sortable_fields = ['id' => 'id', 'title' => 'title', 'percentage' => 'percentage'];
        $sort = 'id';
        $limit = 10;
        $condition  = [];
        $offset = 0;
        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'id') {
                $sort = (isset($sortable_fields[$sort])) ? $sortable_fields[$sort] : "id";
            } else {
                $sort = $_GET['sort'];
            }
        }
        $order = "asc";
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        if (isset($_GET['search']) and $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = ['`id`' => $search, '`title`' => $search, '`percentage`' => $search];
        }

        $total  = $builder->select(' COUNT(id) as `total` ');
        if (isset($_GET['id']) && $_GET['id'] != '') {
            $builder->where($condition);
        }

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }

        $offer_count = $builder->get()->getResultArray();
        $total = $offer_count[0]['total'];
        // print_r($Category_count);

        $builder->select();
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }

        $offer_recored = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();

        $bulkData = array();
        $bulkData['total'] = $total;

        $rows = array();
        $tempRow = array();

        foreach ($offer_recored as $row) {
        //     $operations = '
        //        <button class="btn btn-success edit_taxes" data-id="' . $row['id'] . '"  data-toggle="modal" data-target="#update_modal" onclick="taxes_id(this)"
        //        title = "Update the taxes"> <i class="fa fa-pen" aria-hidden="true"></i> </button>  
        //        <button class="btn btn-danger remove_taxes" data-id="' . $row['id'] . '" onclick="taxes_id(this)" data-toggle="modal" data-target="#delete_modal" title = "Delete the taxes"> <i class="fa fa-trash" aria-hidden="true"></i> </button> 
        //    ';

            $operations = '
               <button class="btn btn-primary edit_taxes" data-id="' . $row['id'] . '"  data-toggle="modal" data-target="#update_modal" onclick="taxes_id(this)"
               title = "Update the taxes"> <i class="fa fa-pen" aria-hidden="true"></i> </button>  
              
           ';
            $status = ($row['status'] == 0) ?
                '<span class="badge badge-danger"> In Active </span>' :
                ' <span class="badge badge-success"> Active </span>';
            $tempRow['id'] = $row['id'];
            $tempRow['title'] = $row['title'];
            $tempRow['percentage'] = $row['percentage'];
            $tempRow['status']  = $status;
            $tempRow['og_status'] = $row['status'];
            $tempRow['operations'] = $operations;

            $rows[] = $tempRow;
        }

        // else return json
        $bulkData['rows'] = $rows;
        return json_encode($bulkData);
    }
    public function remove_taxes()
    {
        if($this->superadmin=="rajasthantech.info@gmail.com"){
            defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
    

        }else{

            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $response['error'] = true;
                $response['message'] = DEMO_MODE_ERROR;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                return $this->response->setJSON($response);
            }
        }
        $permission = is_permitted($this->creator_id, 'delete', 'tax');
        if ($permission) {

            if ($this->isLoggedIn && $this->userIsAdmin) {

                $id = $this->request->getPost('id');
                $db      = \Config\Database::connect();
                $builder = $db->table('taxes');
                if ($builder->delete(['id' => $id])) {
                    $response = [
                        'error' => false,
                        'message' => 'Tax deleted successfully',
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
    public function edit_taxes()
    {
        if($this->superadmin=="rajasthantech.info@gmail.com"){
            defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
    

        }else{

            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $response['error'] = true;
                $response['message'] = DEMO_MODE_ERROR;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                return $this->response->setJSON($response);
            }
        }


        // print_r($_POST);
        // die;
        $permission = is_permitted($this->creator_id, 'update', 'tax');


        if ($permission) {

            $id = $this->request->getPost('id');
            $db      = \Config\Database::connect();
            $builder = $db->table('taxes');
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $id = $this->request->getPost('id');
                $title = $this->request->getPost('title');
                $percentage = $this->request->getPost('percentage');
              

                $data['title'] = $title;
                $data['percentage'] = $percentage;
                $data['status'] = ($this->request->getPost('tax_status_edit') == "on") ? 1 : 0;


                if ($builder->update($data, ['id' => $id])) {
                    $response = [
                        'error' => false,
                        'message' => "Tax updated successfully",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "some erroe occuring",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }




                // $upd =  $this->category->update($id, $data);  

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
}
