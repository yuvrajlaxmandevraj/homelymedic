<?php

namespace App\Models;
use CodeIgniter\Model;

class Offers_model extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'offers';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['type', 'type_id', 'image', 'status'];
    protected $useTimestamps = true;

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public $base, $admin_id, $db;

    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [])
    {
        $multipleWhere = '';
        $db      = \Config\Database::connect();
        $condition = [];
        $builder = $db->table('offers as o');
        $sortable_fields = ['id' => 'id', 'type' => 'type', 'type_id' => 'type_id', 'status' => 'status'];

        if (isset($search) and $search != '') {
            $multipleWhere = ['`id`' => $search, '`type`' => $search, '`status`' => $search];
        }

        $total  = $builder->select(' COUNT(id) as `total` ');
        if (isset($_GET['id']) && $_GET['id'] != '') {
            $builder->where($condition);
        }

        if ($from_app) {
            $where['status'] = 1;
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }

        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }


        $slider_count = $builder->get()->getResultArray();
        $total = $slider_count[0]['total'];

        $builder->select();
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }

        $slider_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($slider_record as $row) {
            if ($from_app == false) {
                if (check_exists(base_url('/public/uploads/sliders/' . $row['image']))) {
                    $image = '<a  href="' . base_url('/public/uploads/sliders/' . $row['image'])  . '" data-lightbox="image-1"><img height="80px" class="rounded" src="' . base_url("/public/uploads/sliders/" . $row['image']) . '" alt=""></a>';
                } else {
                    $image = 'nothing found';
                }
            } else {

                if (check_exists(base_url('/public/uploads/sliders/' . $row['image']))) {
                    $image = base_url('/public/uploads/sliders/' . $row['image']);
                } else {
                    $image = 'nothing found';
                }
            }
            $operations = '
                <button class="btn btn-success edite-slider" data-id="' . $row['id'] . '"  data-toggle="modal" data-target="#update_modal" onclick="update_slider(this)"
                title = "Update the slider"> <i class="fa fa-pen" aria-hidden="true"></i> </button> | 
                <button class="btn btn-danger delete-slider" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#delete_modal" onclick="category_id(this)"title = "Delete the slider"> <i class="fa fa-trash" aria-hidden="true"></i> </button> 
            ';

            $status =  ($row['status'] == 1) ? 'Active' : 'Deactive';

            $tempRow['id'] = $row['id'];
            $tempRow['type'] = $row['type'];
            $tempRow['type_id'] = $row['type_id'];
            $tempRow['offer_image'] = $image;
            if ($from_app == false) {
                $tempRow['status'] = $status;
                $tempRow['operations'] = $operations;
            }
            $tempRow['created_at'] = $row['created_at'];
            $rows[] = $tempRow;
        }

        if ($from_app) {
            // if request from app return array 
            $data['total'] = $total;
            $data['data'] = $rows;
            return $data;
        } else {
            // else return json
            $bulkData['rows'] = $rows;
            return json_encode($bulkData);
        }
    }
}
