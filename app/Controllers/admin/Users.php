<?php

namespace App\Controllers\admin;

use App\Controllers\Auth;

class Users extends Admin
{
    public $user_model, $admin_id;
    public function __construct()
    {
        parent::__construct();
        $this->user_model = new \App\Models\Users_model();
        $this->ionAuth = new \IonAuth\Libraries\IonAuth();
        $this->admin_id = ($this->ionAuth->isAdmin()) ? $this->ionAuth->user()->row()->id : 0;
        $this->superadmin = $this->session->get('email');
    }

    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'User List | Admin Panel';
            $this->data['main_page'] = 'users';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function tts()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'Users TTS - Admin Panel | eDemand';
            $this->data['main_page'] = 'users_tts';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function list_user()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';

        $data = json_encode($this->user_model->list(false, $search, $limit, $offset, $sort, $order));

        return $data;
    }
    public function deactivate()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            if ($this->superadmin == "rajasthantech.info@gmail.com") {
                defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
            } else {

                if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                    $response['error'] = true;
                    $response['message'] = DEMO_MODE_ERROR;
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    return $this->response->setJSON($response);
                }
            }

            $id = $this->request->getVar('user_id');
            $userdata = fetch_details('users', ['id' => $id], ['email', 'username']);
            $settings = get_settings('general_settings', true);
            if(isset($userdata[0]['email'])){

                $icon = $settings['logo'];
                    $data = array(
                        'name' => $userdata[0]['username'],
                        'title' => "Account Deactivation Confirmation",
                        'logo' => base_url("public/uploads/site/" . $icon),
                        'first_paragraph' => 'We are sorry to inform you that your account has been deactivated.',
                        'second_paragraph' => 'If you have any questions or need any assistance feel free to contact us.',
                        'third_paragraph' => 'Thank you again for choosing our services. We look forward to doing business with you again.',
                        'company_name' => $settings['company_title'],
                    );
                   ( email_sender($userdata[0]['email'], 'Account Deactivation Confirmation', view('backend/admin/pages/provider_email', $data)));
            }

            $operations = $this->ionAuth->deactivate($id);
            if ($operations) {

                $response = [
                    'error' => false,
                    'message' => "Email sended to the user successfully and user disabled",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => "Could not deactivate User",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } else {
            return redirect('admin/login');
        }
    }

    public function activate()
    {


        if ($this->isLoggedIn && $this->userIsAdmin) {
            if ($this->superadmin == "rajasthantech.info@gmail.com") {
                defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
            } else {

                if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                    $response['error'] = true;
                    $response['message'] = DEMO_MODE_ERROR;
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    return $this->response->setJSON($response);
                }
            }

            $id = $this->request->getVar('user_id');



            $operations =   $this->ionAuth->activate($id);
            $userdata = fetch_details('users', ['id' => $id], ['email', 'username']);
            $settings = get_settings('general_settings', true);
            $icon = $settings['logo'];
            if ($operations) {
                if(isset($userdata[0]['email'])){
                
                    $data = array(
                        'name' => $userdata[0]['username'],
                        'title' => "Account activation confirmation",
                        'logo' => base_url("public/uploads/site/" . $icon),
                        'first_paragraph' => 'We are pleased to inform you that your account has been successfully activated. You can now log in to your account and start using our services.',
                        'second_paragraph' => 'If you have any questions or need any assistance feel free to contact us.',
                        'third_paragraph' => 'Thank you again for choosing our services. We look forward to doing business with you again.',
                        'company_name' => $settings['company_title'],
                    );
                    email_sender("wrteam.dimple@gmail.com", 'Account activation confirmation', view('backend/admin/pages/provider_email', $data));
                }

                $response = [
                    'error' => false,
                    'message' => "Email sended to the user successfully and user have been disabled",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => "Eroor may have occured",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
        } else {
            return redirect('admin/login');
        }
    }
}
