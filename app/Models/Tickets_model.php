<?php

namespace App\Models;

use CodeIgniter\Model;

class Tickets_model extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'ticket_types';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['title'];
    protected $useTimestamps = true;

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public $base, $admin_id, $db;






    public function ticket_list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [], $additional_data = [], $column_name = '', $whereIn = [])
    {
        $multipleWhere = '';
        $db      = \Config\Database::connect();

        $builder = $db->table('tickets t');
        $sortable_fields = [
            't.id' => 't.id', 't.ticket_type_id' => 't.ticket_type_id', 't.user_id' => 't.user_id', 't.email' => 't.email'
        ];


        if ($search and $search != '') {
            $multipleWhere = [
                't.id`' => $search, '`t.ticket_type_id`' => $search, '`t.user_id`' => $search, '`t.email`' => $search
            ];
        }


        $total  = $builder->select(' COUNT(id) as `total` ');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }

        if (!empty($whereIn)) {
            $builder->whereIn($column_name, $whereIn);
        }


        $tickets_count = $builder->get()->getResultArray();
        $total = $tickets_count[0]['total'];


        $builder->select('t.*,u.username,tt.title');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }

        if (!empty($whereIn)) {
            $builder->whereIn($column_name, $whereIn);
        }
        $tickets_record = $builder->join('users u', 'u.id=t.user_id')->join('ticket_types tt', 'tt.id=t.ticket_type_id')->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($tickets_record as $row) {

            $operations = '
                <button class="btn btn-success chat" data-toggle="modal" data-target="#ticket_modal"> 
                    <i class="fa fa-eye" aria-hidden="true"></i> 
                </button> 
                <button class="btn btn-danger remove_tickets"  title = "Delete the Fags"> 
                    <i class="fa fa-trash" aria-hidden="true"></i> 
                </button> 
           ';
            if ($row['status'] == "1" || $row['status'] == "0") {
                $status = '<label class="badge badge-secondary">PENDING</label>';
            } else if ($row['status'] == "2") {
                $status = '<label class="badge badge-info">OPENED</label>';
            } else if ($row['status'] == "3") {
                $status = '<label class="badge badge-success">RESOLVED</label>';
            } else if ($row['status'] == "4") {
                $status = '<label class="badge badge-danger">CLOSED</label>';
            } else if ($row['status'] == "5") {
                $status = '<label class="badge badge-warning">REOPENED</label>';
            } else {
                $status = $row['status'];
            }
            // OPD area ends
            $tempRow['id'] = $row['id'];
            $tempRow['user_id'] = $row['user_id'];
            $tempRow['ticket_type_id'] = $row['ticket_type_id'];
            $tempRow['title'] = $row['title'];
            $tempRow['email'] = $row['email'];
            $tempRow['username'] = $row['username'];
            $tempRow['subject'] = $row['subject'];
            $tempRow['description'] = $row['description'];
            $tempRow['status'] = $status;
            $tempRow['og_status'] = $row['status'];
            $tempRow['created_at'] = $row['created_at'];
            $tempRow['updated_at'] = $row['updated_at'];
            $tempRow['operation'] = $operations;

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

    public function get_message_list(
        $ticket_id,
        $from_app = false,
        $user_id = "",
        $search = "",
        $offset = 0,
        $limit = 50,
        $sort = "tm.date_created",
        $order = "DESC",
        $data = array(),
        $msg_id = ""
    ) {
        $multipleWhere = '';
        $db      = \Config\Database::connect();

        $builder = $db->table('ticket_messages tm');
        $sortable_fields = [
            'tm.id' => 'tm.id',
        ];

        if (!empty($ticket_id)) {
            $where['tm.ticket_id'] = $ticket_id;
        }




        $total  = $builder
            ->select(' COUNT(tm.id) as `total` ')
            ->join('users u', 'u.id=tm.user_id', 'left')
            ->join('tickets t', 't.id=tm.ticket_id', 'left')
            ->where('tm.ticket_id', $ticket_id);

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }




        $tickets_count = $builder->get()->getResultArray();
        $total = $tickets_count[0]['total'];


        $builder->select('t.id,u.username,tm.*');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }


        $tickets_record = $builder
            ->join('users u', 'u.id=tm.user_id', 'left')
            ->join('tickets t', 't.id=tm.ticket_id', 'left')
            ->where('tm.ticket_id', $ticket_id)
            ->orderBy($sort, $order)
            ->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($tickets_record as $row) {
            $tempRow['id'] = $row['id'];
            $tempRow['username'] = $row['username'];
            $tempRow['user_type'] = $row['user_type'];
            $tempRow['ticket_id'] = $row['ticket_id'];
            $tempRow['message'] = $row['message'];
            $tempRow['attachments'] = $row['attachments'];
            $tempRow['updated_at'] = $row['updated_at'];
            $tempRow['date_created'] = $row['date_created'];
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

    public function get_message($from_app = false, $ticket_id = "", $user_id = "", $msg_id = "", $search = "", $offset = "", $limit = "", $sort = "tm.id", $order = "DESC", $data = array())
    {
        $multipleWhere = '';
        $db      = \Config\Database::connect();

        $builder = $db->table('ticket_messages tm');
        $sortable_fields = [
            'tm.id' => 'tm.id',
        ];

        if (!empty($ticket_id)) {
            $where['tm.id'] = $msg_id;
        }
        if (!empty($ticket_id)) {
            $where['tm.user_id'] = $user_id;
        }
        if (!empty($ticket_id)) {
            $where['tm.ticket_id'] = $ticket_id;
        }




        $total  = $builder
            ->select(' COUNT(tm.id) as `total` ')
            ->join('users u', 'u.id=tm.user_id', 'left')
            ->join('tickets t', 't.id=tm.ticket_id', 'left')
            ->where('tm.ticket_id', $ticket_id);

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orWhere($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }




        $tickets_count = $builder->get()->getResultArray();
        $total = $tickets_count[0]['total'];


        $builder->select('t.id,u.username,tm.*');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->orLike($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }


        $tickets_record = $builder
            ->join('users u', 'u.id=tm.user_id', 'left')
            ->join('tickets t', 't.id=tm.ticket_id', 'left')
            ->where('tm.ticket_id', $ticket_id)
            ->orderBy($sort, $order)
            ->limit($limit, $offset)->get()->getResultArray();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($tickets_record as $row) {
            $tempRow['id'] = $row['id'];
            $tempRow['username'] = $row['username'];
            $tempRow['user_type'] = $row['user_type'];
            $tempRow['ticket_id'] = $row['ticket_id'];
            $tempRow['message'] = $row['message'];
            $tempRow['attachments'] = $row['attachments'];
            $tempRow['updated_at'] = $row['updated_at'];
            $tempRow['date_created'] = $row['date_created'];
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
