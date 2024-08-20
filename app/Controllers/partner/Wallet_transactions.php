<?php

namespace App\Controllers\partner;

use App\Models\Service_model;

class Wallet_transactions extends Partner
{
    public $service, $validations, $db;
    public function __construct()
    {
        parent::__construct();
        $this->service = new Service_model();
        $this->validation = \Config\Services::validation();
        $this->db      = \Config\Database::connect();
    }
    public function index()
    {
        if ($this->isLoggedIn) {
            $this->data['title'] = 'Wallet Transactions | Partner Panel';
            $this->data['main_page'] = 'wallet_transactions';
            $this->data['categories'] = fetch_details('categories', [], ['id', 'name']);
            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }
}
