<?php

namespace App\Controllers\partner;

use App\Models\Payment_request_model;

class Withdrawal_requests  extends Partner
{
    public function __construct()
    {
        parent::__construct();
        $this->validation = \Config\Services::validation();
    }
    public function index()
    {
        if (!$this->isLoggedIn && !$this->userIsPartner) {
            return redirect('partner/login');
        } else {
            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }

            $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);
            if (empty($is_already_subscribe)) {
                return redirect('partner/subscription');
            }
            $this->data['title'] = 'Payment request | Partner Panel';
            $this->data['main_page'] = 'withdrawal_requests';

            return view('backend/partner/template', $this->data);
        }
    }

    public function send()
    {
        
        if (!$this->isLoggedIn && !$this->userIsPartner) {
            return redirect('partner/login');
        } else {
            $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);
            if (empty($is_already_subscribe)) {
                return redirect('partner/subscription');
            }
            $this->data['title'] = 'Withdrawal request | Partner Panel';
            $this->data['main_page'] = FORMS . 'send_withdrawal_request';
            $user_id = $this->ionAuth->getUserId();
            $balance = fetch_details('users', ['id' => $user_id], 'balance');
            $this->data['balance'] = $balance[0]['balance'];
            $settings = get_settings('general_settings', true);
            $this->data['currency'] = $settings['currency'];
            $this->data['partnerId'] = $user_id;
            return view('backend/partner/template', $this->data);
        }
    }

    public function save()
    {
        if (!$this->isLoggedIn && !$this->userIsPartner) {
            return redirect('partner/login');
        } else {
            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $response['error'] = true;
                $response['message'] = DEMO_MODE_ERROR;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                return $this->response->setJSON($response);
            }
            $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);
            if (empty($is_already_subscribe)) {
                return redirect('partner/subscription');
            }
            if (isset($_POST) && !empty($_POST)) {
                $balance = intval(fetch_details('users', ['id' => $this->userId], ['balance'])[0]['balance']);
                $this->validation->setRules(
                    [
                        'payment_address' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter payment address"
                            ]
                        ],
                        'amount' => [
                            "rules" => 'required|numeric|less_than_equal_to['.$balance.']|greater_than[0]',
                            "errors" => [
                                "required" => "Please enter amount",
                                "numeric" => "Please enter numeric value for amount",
                                "greater_than" => "amount must be greater than 0",
                                "less_than_equal_to" => "amount must be less than or equal to balance",
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

                if (!$this->validation->withRequest($this->request)->run()) {
                    $errors = $this->validation->getErrors();
                    $response = [
                        'error' => true,
                        'message' => $errors,
                        'data' => []
                    ];
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    return $this->response->setJSON($response);
                } else {
                    $payment_request_model = new Payment_request_model();
                    if ($this->userIsPartner) {
                        $userType = "partner";
                    } else {
                        $userType = "customer";
                    }
                    if (isset($_POST['request_id']) && !empty($_POST['request_id'])) {
                        $rquest_id = $this->request->getVar('request_id');
                    } else {
                        $rquest_id = '';
                    }
                    $data = array(
                        'id' => $rquest_id,
                        'user_id' => $this->request->getVar('user_id'),
                        'user_type' => $userType,
                        'payment_address' => $this->request->getVar('payment_address'),
                        'amount' => $this->request->getVar('amount'),
                        'remarks' => $this->request->getVar('remarks'),
                        'status' => 0,
                    );
                    if ($payment_request_model->save($data)) {
                        update_balance($this->request->getVar('amount'), $this->request->getVar('user_id'), 'deduct');

                        $response = [
                            'error' => false,
                            'message' => 'Request Sent!',
                            'data' => []
                        ];
                        $response['csrfName'] = csrf_token();
                        $response['csrfHash'] = csrf_hash();
                        return $this->response->setJSON($response);
                    }
                }
            } else {
                return redirect()->back();
            }
        }
    }

    
    public function list()
    {
      
        $model = new Payment_request_model();
        $where['p.user_id'] = $_SESSION['user_id'];
    $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
    $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
    $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'p.id';

    $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
    $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
    $data = $model->list(false, $search, $limit, $offset, $sort, $order,$where);
    return $data;
    // return $model->list(false, '', 10, 0, '', 'p.id', $where);
    // public function list($from_app = false, $search = '', $limit = 20, $offset = 0, $sort = 'p.id', $order = 'DESC', $where = [])
        //  $model->list(false, '', 10, 0, 'p.id', 'p.id', $where);

        // $db = \Config\Database::connect();
        // // your queries here
        // $query = $db->getLastQuery();
        // $sql = $query->getQuery();
        // echo $sql;
    }

    public function delete()
    {
        if ($this->isLoggedIn) {
            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $response['error'] = true;
                $response['message'] = DEMO_MODE_ERROR;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                return $this->response->setJSON($response);
            }
            $id = $this->request->getPost('id');
            $db      = \Config\Database::connect();
            $builder = $db->table('payment_request')->delete(['id' => $id]);
            if ($builder) {
                $response = [
                    'error' => false,
                    'message' => 'Payment Request deleted successfully',
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Payment Request can not be deleted!',
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
            }
            return $this->response->setJSON($response);
        } else {
            return redirect('partner/login');
        }
    }
}
