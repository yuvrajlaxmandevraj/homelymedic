<?php

namespace App\Controllers\partner;

use App\Models\Category_model;

class Categories extends Partner
{
    public $partner, $validations, $db;
    public function __construct()
    {
        parent::__construct();
        $this->category = new Category_model();
        $this->validation = \Config\Services::validation();
        $this->db      = \Config\Database::connect();
    }

    public function index()
    {
        if ($this->isLoggedIn) {
            if (!exists(['partner_id' => $this->userId, 'is_approved' => 1], 'partner_details')) {
                return redirect('partner/profile');
            }
            $is_already_subscribe = fetch_details('partner_subscriptions', ['partner_id' => $this->userId, 'status' => 'active']);
        if (empty($is_already_subscribe)) {
            return redirect('partner/subscription');
        }

            $this->data['title'] = 'Categories | Partner Panel';
            $this->data['main_page'] = 'categories';
            return view('backend/partner/template', $this->data);
        } else {
            return redirect('partner/login');
        }
    }
}
