<?php

namespace App\Controllers\admin;

use App\Models\Tickets_model;
use App\Models\ticket_message_modal;

use function PHPSTORM_META\type;

class Show_tickets  extends Admin
{
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
            $this->data['title'] = ' Show Tickets | Admin Panel';
            $this->data['main_page'] = 'show_tickets';
            $this->data['ticket_types'] = fetch_details('tickets');
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('unauthorised');
        }
    }
    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [], $additional_data = [], $column_name = '', $whereIn = [])
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $data = $this->ticket_types->ticket_list(false, $search, $limit, $offset, $sort, $order);
        return $data;
    }

    public function change_status()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $status = $this->request->getPost('status');
            $ticket_id = $this->request->getPost('id');

            $db      = \Config\Database::connect();
            $insert_id = $db->table('tickets')->update(['status' => $status], ['id' => $ticket_id]);
            if ($insert_id) {
                $response = [
                    'error' => false,
                    'message' => "Changed Status",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => "Could not change status",
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

    public function fetch_chat()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {

            $limit = $this->request->getPost('limit');
            $offset = $this->request->getPost('offset');

            $admin_id = $this->userId;
            $ticket_id = $this->request->getPost('ticket_type_id');
            $user_id = $this->request->getPost('user_id');

            $ticket_types = new Tickets_model();
            // print_r($ticket_id);
            // print_r($offset);
            // die();
            $msg_list = $ticket_types->get_message_list($ticket_id, false, $user_id, "", $offset, $limit);
            // print_r($msg_list);
            $records = json_decode($msg_list, true);
            $rows = $records['rows'];            
            if (!empty($rows)) {
                $response = [
                    'error' => false,
                    'message' => "Success finding data",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => $msg_list,
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => "Success finding data",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => [],
                ];
                return $this->response->setJSON($response);
            }
            // die();
        } else {
            return redirect('unauthorised');
        }
    }

    public function send_message()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {

            $message = ($this->request->getPost('message') != "") ? $this->request->getPost('message') : NULL;
            // $message = $this->request->getPost('message');
            $ticket_id = $this->request->getPost('ticket_id');
            $files = $this->request->getFileMultiple('file_chat');
            // $file->getName
            // $count = count($files);
            // print_r($files);
            // die();
            $file = "";
            $name_to_move = "";
            $names = [];
            foreach ($files as $file) {
                $t = time();
                $original_name = $file->getName();
                if ($original_name != "") {
                    $name = $file->getRandomName();
                    $ext = $file->getExtension();
                    $name_to_move =  $name;
                    $store_name = base_url('/public/backend/assets/chat-image/' . $name_to_move);
                    $names[] = $store_name;

                    $path = 'public/backend/assets/chat-image/';
                    $file->move($path, $name_to_move);
                }
            }

            $attachments =  (count($names) == 0) ? null : json_encode($names);
            // print_r($attachments);
            $data = [
                'user_type' => "admin",
                'ticket_id' => $ticket_id,
                'user_id' => $this->userId,
                'message' => $message,
                "attachments" => $attachments,
            ];
            // print_r($data);
            // die();
            $ticket_types = new Tickets_model();
            $ticket_message_modal = new ticket_message_modal();
            $ticket_message_modal->save($data);
            $insert_id = $ticket_message_modal->getInsertID();


            // echo "hell";
            if ($insert_id) {
                $msgs = $ticket_types->get_message(false, $ticket_id, $this->userId, $insert_id, "", 0, 5, "", "");
                $response = [
                    'error' => false,
                    'message' => "Success finding data",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => $msgs,
                ];
                return $this->response->setJSON($response);
            }
        } else {
            return redirect('unauthorised');
        }
    }
}
