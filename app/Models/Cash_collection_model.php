<?php

namespace App\Models;

use CodeIgniter\Model;

class Cash_collection_model extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'cities';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'user_id', 'message', 'status', 'total_amount','commison','status','date'
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
        $builder = $db->table('cash_collection c');
        $sortable_fields = ['id' => 'id'];
        $condition  = [];

        if (isset($search) and $search != '') {


            $multipleWhere = [
                '`c.id`' => $search,
                '`pd.company_name`' => $search,
             
            ];
          
        }
        
          if (isset($_GET['offset']))
            $offset = $_GET['offset'];

     
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'id') {
                $sort = "id";
            } else {
                $sort = $_GET['sort'];
            }
        }
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }

        if (isset($_POST['order'])) {
            $order = $_POST['order'];
        }


        if (isset($_GET['id']) && $_GET['id'] != '') {
            $builder->where($condition);
        }

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
    
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        
          if (isset($_GET['cash_collection_filter']) && $_GET['cash_collection_filter'] != '') {
            // echo '1';
            $builder->where('status', $_GET['cash_collection_filter']);
        }

        $total_count = $builder->select(' COUNT(c.id) as `total` ,c.*,pd.company_name')->join('partner_details pd', 'c.partner_id = pd.partner_id')->get()->getResultArray();



        $total = $total_count[0]['total'];

       if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        
          if (isset($_GET['cash_collection_filter']) && $_GET['cash_collection_filter'] != '') {
            // echo '1';
            $builder->where('status', $_GET['cash_collection_filter']);
        }

        $cash_collection_record = $builder->select('c.*,pd.company_name')->join('partner_details pd', 'c.partner_id = pd.partner_id')->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();

    
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($cash_collection_record as $row) {



            $parter_details=(fetch_details('partner_details', ['partner_id' => $row['partner_id']]));

           
         
            $operations = '<button class="btn btn-success btn-sm edit_cash_collection" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#update_modal"><i class="fa fa-pen" aria-hidden="true"></i> </button> '; 
         
            $tempRow['id'] = $row['id'];

            $tempRow['user_id'] = $row['user_id'];
            $tempRow['message'] = $row['message'];
            // $tempRow['total_amount'] = $row['total_amount'];
            $tempRow['commison'] = $row['commison'];
            $tempRow['status'] = $row['status'];
            $tempRow['partner_name'] = $parter_details[0]['company_name'];
            $tempRow['admin_commision_percentage'] = $parter_details[0]['admin_commission'].'%';
            $tempRow['date'] = $row['date'];
            $tempRow['order_id'] = $row['order_id'];
  
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
            return ($bulkData);
        }
    }

}
