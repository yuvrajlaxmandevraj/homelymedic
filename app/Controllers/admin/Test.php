<?php

namespace App\Controllers\admin;

class Test extends Admin
{
    public function __construct()
    {
        parent::__construct();
        $this->validation = \Config\Services::validation();
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Test | Admin Panel';
            $this->data['main_page'] = 'test';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function test()
    {
        
    }
}
