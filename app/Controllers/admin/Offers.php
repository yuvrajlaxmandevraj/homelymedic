<?php

namespace App\Controllers\admin;

use App\Models\Offers_model;

// when recreating offers section just  remove redirect line from the first line of each function 
class Offers extends Admin
{
    public   $validation, $offer, $creator_id;
    public function __construct()
    {
        parent::__construct();
        $this->offer = new Offers_model();
        $this->validation = \Config\Services::validation();
        $this->creator_id = $this->userId;
    }
    public function index()
    {
        return redirect('admin/login');
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'offers | Admin Panel';
            $this->data['main_page'] = 'offers';
            $this->data['categories_name'] = fetch_details('categories', [], ['id', 'name']);
            $this->data['services_title'] = fetch_details('services', [], ['id', 'title']);
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function add_offer()
    {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $response['error'] = true;
            $response['message'] = DEMO_MODE_ERROR;
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            return $this->response->setJSON($response);
        }
        return redirect('admin/login');
        $permission = is_permitted($this->creator_id, 'create', 'offers');
        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {

                $name = $this->request->getPost('type');
                if ($name == "select_type" || $name == "Select Type") {
                    $response = [
                        'error' => true,
                        'message' => "Please select anything other than Select Type",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
                //
                $image = $this->request->getFile('image');

                if ($image->getName()  == '') {
                    $response = [
                        'error' => true,
                        'message' => "Please select Image before moving any further",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                }
                $id = ($name == "Category") ? $this->request->getPost('Category_item') : (($name == "services") ? $this->request->getPost('service_item') : 0);

                $image_name = $image->getName();
                $data['type'] = $name;
                $data['type_id'] = $id;
                $data['image'] = $image_name;
                $data['status'] = (isset($_POST['changer']) && $_POST['changer'] == "on") ? 1 : 0;
                // print_r($data);
                // die();
                $path = "/public/uploads/offers/";
                if ($this->offer->save($data)) {
                    move_file($image, $path, $image->getName());
                    $response = [
                        'error' => false,
                        'message' => "Offer added successfully",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "some error occrured",
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
    public function list()
    {
        return redirect('admin/login');

        $multipleWhere = '';
        $db      = \Config\Database::connect();

        $builder = $db->table('offers');
        $sortable_fields = ['id' => 'id', 'type' => 'type', 'type_id' => 'type_id', 'status' => 'status'];
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
            $multipleWhere = ['`id`' => $search, '`type`' => $search, '`status`' => $search];
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

        $offer_recored = $builder->orderBy($sort, $order)->limit($limit)->offset($offset)->get()->getResultArray();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($offer_recored as $row) {
            if (check_exists(base_url('/public/uploads/offers/' . $row['image']))) {
                $icon = '<a  href="' . base_url('/public/uploads/offers/' . $row['image'])  . '" data-lightbox="image-1"><img height="80px" class="rounded" src="' . base_url("/public/uploads/offers/" . $row['image']) . '" alt=""></a>';
            } else {
                $icon = 'nothing found';
            }
            $operations = '
                <button class="btn btn-success edit-offer" data-id="' . $row['id'] . '"  data-toggle="modal" data-target="#update_modal" onclick="update_slider(this)"
                title = "Update the slider"> <i class="fa fa-pen" aria-hidden="true"></i> </button>  
                <button class="btn btn-danger delete-offer" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#delete_modal" onclick="category_id(this)"title = "Delete the slider"> <i class="fa fa-trash" aria-hidden="true"></i> </button> 
            ';


            $status =  ($row['status'] == 1) ? 'Active' : 'Deactive';


            $tempRow['id'] = $row['id'];
            $tempRow['type'] = $row['type'];
            $tempRow['type_id'] = $row['type_id'];

            $tempRow['offer_image'] = $icon;
            $tempRow['active_status'] = ($row['status'] == 1) ? "<label class='badge badge-success'>Active</label>" : "<label class='badge badge-danger'>Deactive</label>";
            $tempRow['status'] = $status;
            $tempRow['created_at'] = $row['created_at'];
            $tempRow['operations'] = $operations;

            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return json_encode($bulkData);
    }
    public function update_offer()
    {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $response['error'] = true;
            $response['message'] = DEMO_MODE_ERROR;
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            return $this->response->setJSON($response);
        }
        return redirect('admin/login');

        $permission = is_permitted($this->creator_id, 'update', 'offers');
        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {

                $id = $this->request->getPost('id');
                $name = $this->request->getPost('type_1');
                $old_data = fetch_details('offers', ['id' => $id]);
                $old_image = $old_data[0]['image'];
                // echo $old_image;
                if ($name == "Category") {
                    $type_id = $this->request->getPost('Category_item_1');
                    $fc_title = fetch_details('categories', ['id' => $id], ['name']);
                } else if ($name == "services") {
                    $type_id = $this->request->getPost('service_item_1');
                    $fc_title = fetch_details('services', ['id' => $id], ['title']);
                } else {
                    $type_id = "000";
                }

                $image = $this->request->getFile('image');

                $image_name = ($image->getName() == "") ? $old_image :  $image->getName();

                $data['type'] = $name;
                $data['type_id'] = $type_id;
                $data['image'] = $image_name;
                $data['status'] = (isset($_POST['changer_1']) && $_POST['changer_1'] == "on") ? 1 : 0;

                // echo $type_id;
                $path = "/public/uploads/offers/";
                $old_path = "public/uploads/offers/" . $old_image;
                if ($image->getName() != '') {
                    unlink($old_path);
                }
                $upd =  $this->offer->update($id, $data);
                if ($upd) {
                    if ($image->getName() == "") {
                        $response = [
                            'error' => false,
                            'message' => "offer updated successfully",
                            'csrfName' => csrf_token(),
                            'csrfHash' => csrf_hash(),
                            'data' => []
                        ];
                        return $this->response->setJSON($response);
                    } else {

                        if (move_file($image, $path, $image_name)) {
                            $response = [
                                'error' => false,
                                'message' => "offer updated successfully",
                                'csrfName' => csrf_token(),
                                'csrfHash' => csrf_hash(),
                                'data' => []
                            ];
                            return $this->response->setJSON($response);
                        } else {
                            $response = [
                                'error' => true,
                                'message' => "some error while uploading image",
                                'csrfName' => csrf_token(),
                                'csrfHash' => csrf_hash(),
                                'data' => []
                            ];
                            return $this->response->setJSON($response);
                        }
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
    public function delete_offer()
    {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $response['error'] = true;
            $response['message'] = DEMO_MODE_ERROR;
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            return $this->response->setJSON($response);
        }
        return redirect('admin/login');
        $permission = is_permitted($this->creator_id, 'delete', 'offers');
        if ($permission) {
            if ($this->isLoggedIn && $this->userIsAdmin) {

                $db      = \Config\Database::connect();

                $id = $this->request->getPost('user_id');

                $old_data = fetch_details('offers', ['id' => $id]);
                $old_image = $old_data[0]['image'];

                $old_path = "public/uploads/offers/" . $old_image;
                $builder = $db->table('offers');
                if ($builder->delete(['id' => $id])) {
                    unlink($old_path);
                    $response = [
                        'error' => false,
                        'message' => "offer Successfully deleted",
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'data' => []
                    ];
                    return $this->response->setJSON($response);
                } else {
                    $response = [
                        'error' => true,
                        'message' => "some error occrured",
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
