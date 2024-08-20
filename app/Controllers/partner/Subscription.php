<?php

namespace App\Controllers\partner;

use App\Libraries\Stripe;
use App\Models\Partners_model;
use App\Models\Service_model;

class Subscription extends Partner
{
    public $partner, $validations, $db;
    public function __construct()
    {
        parent::__construct();
        $this->service = new Service_model();
        $this->validation = \Config\Services::validation();
        $this->db      = \Config\Database::connect();

        $this->stripe = new Stripe;
    }

    public function index()
    {
        if ($this->isLoggedIn) {
            $user_id = $this->ionAuth->user()->row()->id;
            $this->data['title'] = 'Subscription';
            $this->data['main_page'] = 'buy_subscription';
            $this->data['users'] = fetch_details('users', ['id' => $user_id]);
            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }


    // public function pre_payment_setup()
    // {
    //     $_POST['user_id'] = $this->ionAuth->user()->row()->id;

    //     if ($_POST['payment_method'] == "stripe") {
    //         $order = $this->stripe->create_payment_intent(array('amount' => (1000 * 100)));

    //         // print_R($order);
    //         $response['client_secret'] = $order['client_secret'];
    //         $response['id'] = $order['id'];
    //     }

    //     $response = [
    //         'error' => false,
    //         'message' => "Client Secret Get Successfully.!",
    //         'csrfName' => csrf_token(),
    //         'csrfHash' => csrf_hash(),
    //         'client_secret'=>$order['client_secret'],
    //         'id' => $order['id'],


    //         'data' => []
    //     ];
    //     print_r(json_encode($response));
    //     return false;
    // }

    public function pre_payment_setup()
    {
        $_POST['user_id'] = $this->ionAuth->user()->row()->id;

        if ($_POST['payment_method'] == "stripe") {
            $order = $this->stripe->create_payment_intent(array('amount' => (1000 * 100)));
            $response['client_secret'] = $order['client_secret'];
            $response['id'] = $order['id'];
        }

        $response = [
            'error' => false,
            'message' => "Client Secret Get Successfully.!",
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'data' => [],
            'client_secret' => $order['client_secret'],
            'id' => $order['id'],
        ];

        print_r(json_encode($response));
        return false;
    }

    public function subscription_payment()
    {
        if ($this->isLoggedIn) {

            $_POST['user_id'] = $this->ionAuth->user()->row()->id;
            $_POST['customer_email'] = $this->ionAuth->user()->row()->email;
            // $this->response['error'] = false;
            // $this->response['message'] = " Successfully.";
            // $this->response['data'] = "123";
            $response = [
                'error' => false,
                'message' => "Buy successfully!",
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash(),
                'data' => []
            ];
            // return $this->response->setJSON($response);
            print_r(json_encode($response));
            return false;
        }
    }
}
