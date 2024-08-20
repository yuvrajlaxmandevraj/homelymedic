<?php

namespace App\Controllers\admin;

use App\Models\Transaction_model;

class Transactions extends Admin
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Transactions | Admin Panel';
            $this->data['main_page'] = 'transactions';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function list_transactions()
    {
        $model = new Transaction_model();
        $data = $model->list_transactions(false, '', 10, 0, 't.id', 'DESC', [], '',  []);
        return $data;
    }
}
