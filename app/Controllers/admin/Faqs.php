<?php

namespace App\Controllers\admin;

use App\Models\Faqs_model;

class Faqs extends Admin
{
    public   $validation, $faqs, $creator_id;
    public function __construct()
    {
        parent::__construct();
        helper(['form', 'url']);
        $this->faqs = new Faqs_model();
        $this->validation = \Config\Services::validation();
        $this->creator_id = $this->userId;
        $this->superadmin = $this->session->get('email');
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'FAQs | Admin Panel';
            $this->data['main_page'] = 'faqs';
            $this->data['faqs'] = fetch_details('faqs');
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }
    public function add_faqs()
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
        $permission = is_permitted($this->creator_id, 'create', 'faq');
        if ($permission) {

            if ($this->isLoggedIn && $this->userIsAdmin) {
                $this->validation->setRules(
                    [
                        'question' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter question for FAQ"
                            ]
                        ],
                        'answer' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter answer for FAQ",
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
                }

                $question = trim($_POST['question']);
                $answer = ($_POST['answer']);
                $data['question'] = $question;
                $data['answer'] = $answer;
                if ($this->faqs->save($data)) {
                    $response = [
                        'error' => false,
                        'message' => "Faq added successfully",
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
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [])
    {
        $multipleWhere = '';
        $db      = \Config\Database::connect();

        $builder = $db->table('faqs');
        $sortable_fields = ['id' => 'id', 'question' => 'question', 'answer' => 'answer'];
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
            $multipleWhere = ['`id`' => $search, '`question`' => $search, '`answer`' => $search, '`status`' => $search];
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


            $operations = '<div class="dropdown">
            <a class="" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <button class="btn btn-secondary   btn-sm px-3"> <i class="fas fa-ellipsis-v "></i></button>
            </a><div class="dropdown-menu" aria-labelledby="dropdownMenuLink">';
            $operations .= '<a class="dropdown-item edit_faqs " data-id="' . $row['id'] . '"  data-toggle="modal" data-target="#update_modal" onclick="faqs_id(this)"><i class="fa fa-pen mr-1 text-primary"></i> Edit</a>';
            $operations .= '<a class="dropdown-item remove_faqs" data-id="' . $row['id'] . '" onclick="faqs_id(this)" data-toggle="modal" data-target="#delete_modal" title = "Delete the Faqs"> <i class="fa fa-trash text-danger mr-1"></i> Delete</a>';
            $operations .= '</div></div>';


            $tempRow['id'] = $row['id'];
            $tempRow['answer'] = $row['answer'];
            $tempRow['created_at'] = format_date($row['created_at'], 'd-m-Y');

            $tempRow['question'] = $row['question'];
            $tempRow['operations'] = $operations;

            $rows[] = $tempRow;
        }

        // else return json
        $bulkData['rows'] = $rows;
        return json_encode($bulkData);
    }
    public function remove_faqs()
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
        $permission = is_permitted($this->creator_id, 'delete', 'faq');
        if ($permission) {

            if ($this->isLoggedIn && $this->userIsAdmin) {

                $id = $this->request->getPost('id');
                $db      = \Config\Database::connect();
                $builder = $db->table('faqs');
                if ($builder->delete(['id' => $id])) {
                    $response = [
                        'error' => false,
                        'message' => 'FAQ deleted successfully',
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
    public function edit_faqs()
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
        $permission = is_permitted($this->creator_id, 'update', 'faq');
        if ($permission) {
            $this->validation->setRules(
                [
                    'question' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter question for FAQ"
                        ]
                    ],
                    'answer' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter answere for FAQ",
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
            }
            $id = $this->request->getPost('id');
            $db      = \Config\Database::connect();
            $builder = $db->table('faqs');
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $id = $this->request->getPost('id');
                $question = $this->request->getPost('question');
                $answer = $this->request->getPost('answer');
                $old_data = fetch_details('faqs', ['id' => $id]);

                $data['question'] = $question;
                $data['answer'] = $answer;

                if ($builder->update($data, ['id' => $id])) {
                    $response = [
                        'error' => false,
                        'message' => "Faq updated successfully",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "some error occuring",
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
}
