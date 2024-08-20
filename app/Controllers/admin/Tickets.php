<?php

namespace App\Controllers\admin;

use App\Models\Tickets_model;

class Tickets extends Admin
{
    public   $validation, $ticket_types;
    public function __construct()
    {
        parent::__construct();
        helper(['form', 'url']);
        $this->ticket_types = new Tickets_model();
        $this->validation = \Config\Services::validation();
    }
    public function index()
    {
        // echo "test";
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Tickets | Admin Panel';
            $this->data['main_page'] = 'tickets';
            $this->data['ticket_types'] = fetch_details('ticket_types');
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');

            return redirect('unauthorised');
        }
    }
    public function add_tickets()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->validation->setRules(
                [
                    'title' => 'required|trim',

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

            $data['title'] = $title;

            if ($this->ticket_types->save($data)) {
                $response = [
                    'error' => false,
                    'message' => "ticket added successfully",
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
    }
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [])
    {
        $multipleWhere = '';
        $db      = \Config\Database::connect();

        $builder = $db->table('ticket_types');
        $sortable_fields = ['id' => 'id', 'title' => 'title'];
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
        $order = "ASC";
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        if (isset($_GET['search']) and $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = ['`id`' => $search, '`title`' => $search];
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
        $offer_count = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
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
            $operations = '
               <button class="btn btn-success edit_tickets" data-id="' . $row['id'] . '"  data-toggle="modal" data-target="#update_modal" onclick="tickets_id(this)"
               title = "Update the tickets"> <i class="fa fa-pen" aria-hidden="true"></i> </button>  
               <button class="btn btn-danger remove_tickets" data-id="' . $row['id'] . '" onclick="tickets_id(this)" data-toggle="modal" data-target="#delete_modal" title = "Delete the tickets"> <i class="fa fa-trash" aria-hidden="true"></i> </button> 
           ';
            $tempRow['id'] = $row['id'];
            $tempRow['title'] = $row['title'];
            $tempRow['operations'] = $operations;

            $rows[] = $tempRow;
        }

        // else return json
        $bulkData['rows'] = $rows;
        return json_encode($bulkData);
    }

    public function remove_tickets()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {

            $id = $this->request->getPost('id');
            $db      = \Config\Database::connect();
            $builder = $db->table('tickets');
            $is = $builder->delete(['id' => $id]);
            // print_r($is);
            if ($is) {
                $response = [
                    'error' => false,
                    'message' => 'Tickets deleted successfully',
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
    }

    public function edit_tickets()
    {
        $id = $this->request->getPost('id');
        $db      = \Config\Database::connect();
        $builder = $db->table('ticket_types');
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $id = $this->request->getPost('id');
            $title = $this->request->getPost('title');
            $old_data = fetch_details('ticket_types', ['id' => $id]);
            $data['title'] = $title;
            if ($builder->update($data, ['id' => $id])) {
                $response = [
                    'error' => false,
                    'message' => "Ticket updated successfully",
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
        } else {
            return redirect('unauthorised');
        }
    }
}

// if ($this->isLoggedIn && $this->userIsAdmin) {

//     $id = $this->request->getPost('id');
//     print_r($id);
//     die();
//     $db      = \Config\Database::connect();
//     $builder = $db->table('ticket_types');
//     if ($builder->delete(['id' => $id])) {
//         $response = [
//             'error' => false,
//             'message' => 'Tickets deleted successfully',
//             'csrfName' => csrf_token(),
//             'csrfHash' => csrf_hash(),
//             'data' => []
//         ];
//         return $this->response->setJSON($response);
//     } else {
//         $response = [
//             'error' => true,
//             'message' => 'An error occured during deleting this item',
//             'csrfName' => csrf_token(),
//             'csrfHash' => csrf_hash(),
//             'data' => []
//         ];
//         return $this->response->setJSON($response);
//     }
// } else {
//     return redirect('unauthorised');
// }