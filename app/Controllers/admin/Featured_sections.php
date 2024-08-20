<?php


namespace App\Controllers\admin;

use App\Models\Featured_sections_model;
use App\Models\Partners_model;

class Featured_sections extends Admin
{
    public   $validation, $sections, $creator_id;
    public function __construct()
    {
        parent::__construct();
        helper(['form', 'url']);
        $this->sections = new Featured_sections_model();
        $this->validation = \Config\Services::validation();
        $this->creator_id = $this->userId;
        $this->superadmin = $this->session->get('email');
    }
    public function index()
    {


        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Featured section | Admin Panel';
            $this->data['main_page'] = 'featured_sections';
            $this->data['categories_name'] = fetch_details('categories', [], ['id', 'name']);
            $this->data['partners'] = fetch_details('partner_details', []);


            // $this->data['partners'] = $partners->list(true, '', 1000)['data'];



            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }

    public function add_featured_section()
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

        $permission = is_permitted($this->creator_id, 'add', 'featured_section');
        if ($permission) {

            if ($this->isLoggedIn && $this->userIsAdmin) {

                $section_type = ($this->request->getPost('section_type')) ? $this->request->getPost('section_type') : "";
                if (isset($section_type) && $section_type  == 'partners') {
                    $this->validation->setRules(
                        [
                            'title' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please enter title for feature section "
                                ]
                            ],
                            'partners_ids' => [
                                "rules" => 'required',
                                "errors" => ["required" => "Please select atleast one partner"]
                            ],
                        ],
                    );
                } elseif (isset($section_type) && $section_type == 'categories') {
                    $this->validation->setRules(
                        [
                            'title' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please enter title for feature section "
                                ]
                            ],
                            'category_item' => [
                                "rules" => 'required',
                                "errors" => ["required" => "Please select atleast one category"]
                            ],
                        ],
                    );
                } else {
                    $this->validation->setRules(
                        [
                            'title' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please enter title for feature section "
                                ]
                            ],
                            'section_type' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please choose any Section Type "
                                ]
                            ],
                        ],
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

                $sections = fetch_details('sections');
                if (!empty($sections)) {

                    foreach ($sections as $row) {

                        if($section_type==$row['section_type'] ){

                            if ($row['section_type'] == "ongoing_order" || $row['section_type'] == "previous_order") {
                                $response = [
                                    'error' => true,
                                    'message' => "You may only include the  " . $section_type . " section once.",
                                    'csrfName' => csrf_token(),
                                    'csrfHash' => csrf_hash(),
                                    'data' => []
                                ];
                                return json_encode($response);
                            }
                        }
                    }
                }


                $title = $this->request->getPost('title');
                $data = [];
                $data['title'] = $title;
                $data['section_type'] = $section_type;
                if (isset($section_type) && $section_type  == 'partners') {
                    $data['partners_ids'] = implode(',', $_POST['partners_ids']);
                    $data['category_ids'] = null;
                } else  if (isset($section_type) && $section_type == 'categories') {
                    $data['category_ids'] = implode(',', $_POST['category_item']);
                    $data['partners_ids'] = NULL;
                } else  if (isset($section_type) && $section_type == 'top_rated_partner') {

                    $data['limit'] = $this->request->getPost('limit');;
                } else  if (isset($section_type) && $section_type == 'previous_order') {

                    $data['limit'] = $this->request->getPost('previous_order_limit');;
                } else  if (isset($section_type) && $section_type == 'ongoing_order') {

                    $data['limit'] = $this->request->getPost('ongoing_order_limit');;
                } else {
                    $data['partners_ids'] = NULL;
                    $data['category_ids'] = null;
                }
                $data['status'] = isset($_POST['status'])?1:0;



                $db      = \Config\Database::connect();
                $builder = $db->table('sections');
                $builder->selectMax('rank');
                $order = $builder->get()->getResultArray();

                $data['rank'] = ($order[0]['rank']) + 1;



                if ($this->sections->save($data)) {
                    $response = [
                        'error' => false,
                        'message' => "Featured section added successfully",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return json_encode($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "Please try again....",
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


    public function list()
    {
        $multipleWhere = '';
        $db      = \Config\Database::connect();

        $builder = $db->table('sections s');
        $sortable_fields = ['id' => 'id', 'rank' => 'rank', 'title' => 'title', 'categories' => 'categories', 'style' => 'style', 'service_type' => 'service_type'];
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
            $multipleWhere = ['`s.id`' => $search, '`s.title`' => $search, '`s.created_at`' => $search, 'section_type' => $search];
        }


        if (isset($_GET['feature_section_filter']) && $_GET['feature_section_filter'] != '') {

            $builder->where('s.status',  $_GET['feature_section_filter']);
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



        $builder->select();
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($_GET['feature_section_filter']) && $_GET['feature_section_filter'] != '') {

            $builder->where('s.status',  $_GET['feature_section_filter']);
        }



        $offer_recored = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $user1 = fetch_details('users', ["phone" => $_SESSION['identity']],);
        $permissions = get_permission($user1[0]['id']);
        foreach ($offer_recored as $row) {
            $operations = "";


            // $label = ($row['status'] == 1) ?
            //     '<div class="badge badge-success projects-badge"> Active </div>' :
            //     '<div class="badge badge-danger projects-badge"> Deactive </div>';


            $label = ($row['status'] == 1) ?


            "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>Active
            </div>" :
            "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 '>Deactive
            </div>";


            $operations = '<div class="dropdown">
<a class="" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
<button class="btn btn-secondary   btn-sm px-3"> <i class="fas fa-ellipsis-v "></i></button>
</a>
<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">';
            if ($permissions['update']['featured_section'] == 1) {

                $operations .= '<a class="dropdown-item update_featured_section "data-id="' . $row['id'] . '"  data-id="' . $row['id'] . '"  data-toggle="modal" data-target="#update_modal" onclick="feature_section_id(this)"><i class="fa fa-pen mr-1 text-primary"></i> Edit</a>';
            }

            if ($permissions['delete']['featured_section'] == 1) {

                $operations .= '<a class="dropdown-item delete-featured_section" data-id="' . $row['id'] . '" onclick="feature_section_id(this)" data-toggle="modal" data-target="#delete_modal"> <i class="fa fa-trash text-danger mr-1"></i> Delete </a>';
            }




            $operations .= '</div></div>';

            $tempRow['id'] = $row['id'];
            $tempRow['title'] = $row['title'];
            $tempRow['category_ids'] = $row['category_ids'];
            $tempRow['section_type'] = $row['section_type'];
            $tempRow['partners_ids'] = $row['partners_ids'];
            $tempRow['created_at'] = format_date($row['created_at'], 'd-m-Y');
            $tempRow['status'] = $row['status'];
            $tempRow['status_badge'] = $label;
            $tempRow['rank'] =  $row['rank'];
            $tempRow['limit'] =  $row['limit'];


            $tempRow['icon'] = '<i class="fas fa-sort text-new-primary" title="Hold to move"></i>';



            $tempRow['operations'] = $operations;

            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return json_encode($bulkData);
    }
    public function delete_featured_section()
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
        $permission = is_permitted($this->creator_id, 'delete', 'featured_section');
        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {

                $id = $this->request->getPost('id');
                $db      = \Config\Database::connect();
                $builder = $db->table('sections');
                if ($builder->delete(['id' => $id])) {
                    $response = [
                        'error' => false,
                        'message' => 'Featured section deleted successfully',
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
    public function update_featured_section()
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
        $id = $this->request->getPost('id');
        $db      = \Config\Database::connect();
        $builder = $db->table('sections');
        $permission = is_permitted($this->creator_id, 'update', 'featured_section');
        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {
                $section_type = ($this->request->getPost('section_type')) ? $this->request->getPost('section_type') : "";
                if (isset($section_type) && $section_type  == 'partners') {
                    $this->validation->setRules(
                        [
                            'title' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please enter title for feature section "
                                ]
                            ],
                            'edit_partners_ids' => [
                                "rules" => 'required',
                                "errors" => ["required" => "Please select atleast one partner"]
                            ],
                        ],
                    );
                } elseif (isset($section_type) && $section_type == 'categories') {
                    $this->validation->setRules(
                        [
                            'title' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please enter title for feature section "
                                ]
                            ],
                            'edit_Category_item' => [
                                "rules" => 'required',
                                "errors" => ["required" => "Please select atleast one category"]
                            ],
                        ],
                    );
                } else {
                    $this->validation->setRules(
                        [
                            'title' => [
                                "rules" => 'required|trim',
                                "errors" => [
                                    "required" => "Please enter title for feature section "
                                ]
                            ],
                            'section_type' => [
                                "rules" => 'required',
                                "errors" => [
                                    "required" => "Please choose any Section Type "
                                ]
                            ],
                        ],
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

                $partner_ids = $category_ids = null;
                $id = $this->request->getPost('id');
                $title = $this->request->getPost('title');

                $data['title'] = $title;
                $data['section_type'] = $_POST['section_type'];
                $data['category_ids'] = $category_ids;
                if ($_POST['section_type'] == 'partners') {
                    $partner_ids = implode(',', $_POST['edit_partners_ids']);
                    $data['partners_ids'] = $partner_ids;
                } elseif ($_POST['section_type'] == 'categories') {
                    $category_ids = implode(',', $_POST['edit_Category_item']);
                    $data['category_ids'] = $category_ids;
                } elseif ($_POST['section_type']  == 'previous_order') {

                    $data['limit'] = $this->request->getPost('previous_order_limit');;
                } else  if (isset($section_type) && $section_type == 'ongoing_order') {

                    $data['limit'] = $this->request->getPost('ongoing_order_limit');;
                }

                $data['status'] = isset($_POST['edit_status'])?1:0;

                if ($builder->update($data, ['id' => $id])) {
                    $response = [
                        'error' => false,
                        'message' => "Featured section updated successfully",
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


    public function change_order()
    {
        try {

            $ids = json_decode($this->request->getPost('ids'));
            $update = [];
            $db      = \Config\Database::connect();
            $builder = $db->table('sections');
            foreach ($ids as $key => $id) {
                $update = [
                    'id' => $id,
                    'rank' => ($key + 1)
                ];
                $builder->update($update, ['id' => $id]);
            }

            $response = [
                'error' => false,
                'message' => "Featured Section order set successfully",
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash(),
                'data' => []
            ];
            return $this->response->setJSON($response);
        } catch (\Exception $th) {
            $response['error'] = true;
            $response['message'] = 'Something went wrong';
            return $this->response->setJSON($response);
        }
    }
}
