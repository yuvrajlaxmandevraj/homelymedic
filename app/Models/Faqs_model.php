<?php

namespace App\Models;

use CodeIgniter\Model;

class Faqs_model extends Model
{
    protected $DBGroup = 'default';
    protected $table = ' faqs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['question', 'answer', '	status'];
    protected $useTimestamps = true;

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public $base, $admin_id, $db;

   
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC')
    {

        $db      = \Config\Database::connect();
        $builder = $db->table('faqs');
       

        if (isset($search) and $search != '') {
            $multipleWhere = ['`id`' => $search, '`question`' => $search, '`answer`' => $search, '`status`' => $search];
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        $Faq_count = $builder->select(' COUNT(id) as `total` ')->get()->getResultArray();
        // echo $db->lastQuery;

        $total = $Faq_count[0]['total'];

        $builder->select();
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
      
        $faq_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($faq_record as $row) {

            $tempRow['id'] = $row['id'];
            $tempRow['question'] = $row['question'];
            $tempRow['answer'] = $row['answer'];
            $tempRow['status'] = $row['status'];
            $tempRow['created_at'] =format_date( $row['created_at'],'d-m-Y');

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
