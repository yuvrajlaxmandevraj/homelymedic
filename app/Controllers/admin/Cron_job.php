<?php

namespace App\Controllers\admin;

use App\Models\Addresses_model;

class Cron_job extends Admin
{
    public $addresses,  $validation;
    public function __construct()
    {
        parent::__construct();
        $this->addresses = new Addresses_model();
        $this->validation = \Config\Services::validation();
    }
    public function index()
    {
    }
}
