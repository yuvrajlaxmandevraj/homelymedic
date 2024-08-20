<?php

namespace App\Models;

use CodeIgniter\Model;

class City_model extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'cities';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'name', 'latitude', 'longitude', 'delivery_charge_method',
        'fixed_charge', 'per_km_charge', 'range_wise_charges', 'time_to_travel',
        'geolocation_type', 'radius', 'boundary_points', 'max_deliverable_distance'
    ];
    protected $useTimestamps = true;

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public $base, $admin_id, $db;


    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [], $user_details = [])
    {

        $multipleWhere = '';
        $db      = \Config\Database::connect();
        $builder = $db->table('cities');
        $sortable_fields = ['id' => 'id', 'name' => 'name', 'delivery_charge_method	' => 'delivery_charge_method', 'fixed_charge' => 'fixed_charge'];
        $condition  = [];

        if (isset($search) and $search != '') {
            $multipleWhere = ['`id`' => $search, '`name`' => $search, '`latitude`' => $search, '`longitude`' => $search];
        }


        if (isset($_GET['id']) && $_GET['id'] != '') {
            $builder->where($condition);
        }

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }

        $Category_count = $builder->select(' COUNT(id) as `total` ')->get()->getResultArray();
        // echo $db->lastQuery;

        $total = $Category_count[0]['total'];

        $builder->select();
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }

        $category_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($category_record as $row) {

            $operations = '
            <button class="btn btn-success edit-city scr" onclick="scroll_to()"> <i class="fa fa-pen" aria-hidden="true"></i> </button>  
            <button class="btn btn-danger delete-city"> <i class="fa fa-trash" aria-hidden="true"></i> </button>';

            $tempRow['id'] = $row['id'];

            $tempRow['name'] = $row['name'];
            $tempRow['latitude'] = $row['latitude'];
            $tempRow['longitude'] = $row['longitude'];
            $tempRow['delivery_charge_method'] = $row['delivery_charge_method'];
            $tempRow['fixed_charge'] = $row['fixed_charge'];
            $tempRow['per_km_charge'] = $row['per_km_charge'];
            $tempRow['range_wise_charges'] = $row['range_wise_charges'];
            $tempRow['time_to_travel'] = $row['time_to_travel'];
            $tempRow['max_deliverable_distance'] = $row['max_deliverable_distance'];
            $tempRow['created_at'] = $row['created_at'];

            if ($from_app == false) {
                $tempRow['operations'] = $operations;
            }

            $rows[] = $tempRow;
        }
        if ($from_app) {
            // if request from app return array 
            $data['total'] = (empty($total)) ? (string) count($rows) : $total;
            $data['data'] = $rows;
            return $data;
        } else {
            // else return json
            $bulkData['rows'] = $rows;
            return json_encode($bulkData);
        }
    }
}
