<?php

namespace App\Controllers\admin;

use App\Models\Country_code_model;
use CodeIgniter\Database\Query;

class Settings extends Admin
{
    private $db, $builder;
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->validation = \Config\Services::validation();
        $this->builder = $this->db->table('settings');
        $this->superadmin = $this->session->get('email');
    }

    public function __destruct()
    {
        $this->db->close();
        $this->data = [];
    }


    public function main_system_setting_page()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $this->data['title'] = 'System Settings | Admin Panel';
            $this->data['main_page'] = 'main_system_settings';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function general_settings()
    {



        helper('form');
        if ($this->isLoggedIn && $this->userIsAdmin) {
            if ($this->request->getPost('update')) {



                if ($this->superadmin == "rajasthantech.info@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {

                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = DEMO_MODE_ERROR;
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/general-settings')->withCookies();
                    }
                }

                $updatedData = $this->request->getPost();


                $flag = 0;
                $login_image = false;

                $favicon = false;

                $halfLogo = false;
                $logo = false;
                $partner_favicon = false;
                $partner_halfLogo = false;
                $partner_logo = false;
                $files = array();
                $data = get_settings('general_settings', true);




                if (!empty($_FILES['favicon'])) {

                    if ($_FILES['favicon']['name'] != "") {
                        if (!valid_image('favicon')) {
                            $flag = 1;
                        } else {
                            $favicon = true;
                        }
                    }
                }



                if (!empty($_FILES['halfLogo'])) {

                    if ($_FILES['halfLogo']['name'] != "") {
                        if (!valid_image('halfLogo')) {
                            $flag = 1;
                        } else {
                            $halfLogo = true;
                        }
                    }
                }



                if (!empty($_FILES['logo'])) {

                    if ($_FILES['logo']['name'] != "") {
                        if (!valid_image('logo')) {
                            $flag = 1;
                        } else {
                            $logo = true;
                        }
                    }
                }

                if (!empty($_FILES['logo'])) {

                    if ($_FILES['partner_favicon']['name'] != "") {
                        if (!valid_image('partner_favicon')) {
                            $flag = 1;
                        } else {
                            $partner_favicon = true;
                        }
                    }
                }

                if (!empty($_FILES['partner_halfLogo'])) {

                    if ($_FILES['partner_halfLogo']['name'] != "") {
                        if (!valid_image('partner_halfLogo')) {
                            $flag = 1;
                        } else {
                            $partner_halfLogo = true;
                        }
                    }
                }

                if (!empty($_FILES['partner_logo'])) {

                    if ($_FILES['partner_logo']['name'] != "") {
                        if (!valid_image('partner_logo')) {
                            $flag = 1;
                        } else {
                            $partner_logo = true;
                        }
                    }
                }

                if (!empty($_FILES['login_image'])) {



                    if ($_FILES['login_image']['name'] != "") {
                        if (!valid_image('login_image')) {
                            $flag = 1;
                        } else {
                            $login_image = true;
                        }
                    }
                }



                if ($login_image) {

                    $file = $this->request->getFile('login_image');
                    $path = FCPATH . 'public/frontend/retro/';


                    $image = "Login_BG.jpg";
                    $newName = "Login_BG.jpg";
                    // Check if the file exists
                    if (file_exists($path . $newName)) {

                        // If the file exists, unlink (delete) it
                        unlink($path . $newName);
                    }
                    $file->move($path, $newName);
                    $updatedData['login_image'] = $newName;
                } else {
                    $updatedData['login_image'] = isset($data['login_image']) ? $data['login_image'] : "";
                }



                if ($favicon) {

                    $file = $this->request->getFile('favicon');
                    $path = FCPATH . 'public/uploads/site/';
                    $image = $file->getName();
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                    $updatedData['favicon'] = $newName;
                } else {

                    $updatedData['favicon'] = isset($data['favicon']) ? $data['favicon'] : "";
                }

                if ($logo) {
                    $file = $this->request->getFile('logo');
                    $path = FCPATH . 'public/uploads/site/';
                    $image = $file->getName();
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                    $updatedData['logo'] = $newName;
                } else {

                    $updatedData['logo'] = isset($data['logo']) ? $data['logo'] : "";
                }
                if ($halfLogo) {
                    $file = $this->request->getFile('halfLogo');
                    $path = FCPATH . 'public/uploads/site/';
                    $image = $file->getName();
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                    $updatedData['half_logo'] = $newName;
                } else {

                    $updatedData['half_logo'] = isset($data['half_logo']) ? $data['half_logo'] : "";
                }
                if ($partner_favicon) {
                    $file = $this->request->getFile('partner_favicon');
                    $path = FCPATH . 'public/uploads/site/';
                    $image = $file->getName();
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                    $updatedData['partner_favicon'] = $newName;
                } else {

                    $updatedData['partner_favicon'] = isset($data['partner_favicon']) ? $data['partner_favicon'] : "";
                }
                if ($partner_logo) {
                    $file = $this->request->getFile('partner_logo');
                    $path = FCPATH . 'public/uploads/site/';
                    $image = $file->getName();
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                    $updatedData['partner_logo'] = $newName;
                } else {

                    $updatedData['partner_logo'] = isset($data['partner_logo']) ? $data['partner_logo'] : "";
                }



                if ($partner_halfLogo) {
                    $file = $this->request->getFile('partner_halfLogo');
                    $path = FCPATH . 'public/uploads/site/';
                    $image = $file->getName();
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                    $updatedData['partner_half_logo'] = $newName;
                } else {

                    $updatedData['partner_half_logo'] = isset($data['partner_half_logo']) ? $data['partner_half_logo'] : '';
                }


                if (!empty($updatedData['system_timezone_gmt'])) {

                    if ($updatedData['system_timezone_gmt'] == " 00:00") {
                        $updatedData['system_timezone_gmt'] = '+' . trim($updatedData['system_timezone_gmt']);
                    }
                }


                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);



                // app page settings 
                $updatedData['currency'] = (!empty($this->request->getPost('currency'))) ? $this->request->getPost('currency') : (isset($data['currency']) ? $data['currency'] : "");

                $updatedData['country_currency_code'] = (!empty($this->request->getPost('country_currency_code'))) ? $this->request->getPost('country_currency_code') : (isset($data['country_currency_code']) ? $data['country_currency_code'] : "");
                // $updatedData['decimal_point']=(!empty($this->request->getPost('decimal_point')))?$this->request->getPost('decimal_point'):(isset($data['decimal_point'])?$data['decimal_point']:"");

                if ($this->request->getPost('decimal_point') == 0) {

                    $updatedData['decimal_point'] = "0";
                } elseif (!empty($this->request->getPost('decimal_point'))) {


                    $updatedData['decimal_point'] = $this->request->getPost('decimal_point');
                } else {


                    $updatedData['decimal_point'] = $data['decimal_point'];
                }


                // $updatedData['decimal_point']=(($this->request->getPost('decimal_point')=="0")?"0":$this->request->getPost('decimal_point'));


                $updatedData['customer_current_version_android_app'] = (!empty($this->request->getPost('customer_current_version_android_app'))) ? $this->request->getPost('customer_current_version_android_app') : (isset($data['customer_current_version_android_app']) ? $data['customer_current_version_android_app'] : "");
                $updatedData['customer_current_version_ios_app'] = (!empty($this->request->getPost('customer_current_version_ios_app'))) ? $this->request->getPost('customer_current_version_ios_app') : (isset($data['customer_current_version_ios_app']) ? $data['customer_current_version_ios_app'] : "");
                $updatedData['customer_compulsary_update_force_update'] = (!empty($this->request->getPost('customer_compulsary_update_force_update'))) ? ($this->request->getPost('customer_compulsary_update_force_update')) : (isset($data['customer_compulsary_update_force_update']) ? $data['customer_compulsary_update_force_update'] : "");

                $updatedData['provider_current_version_android_app'] = (!empty($this->request->getPost('provider_current_version_android_app'))) ? $this->request->getPost('provider_current_version_android_app') : (isset($data['provider_current_version_android_app']) ? $data['provider_current_version_android_app'] : "");
                $updatedData['provider_current_version_ios_app'] = (!empty($this->request->getPost('provider_current_version_ios_app'))) ? $this->request->getPost('provider_current_version_ios_app') : (isset($data['provider_current_version_ios_app']) ? $data['provider_current_version_ios_app'] : "");
                $updatedData['provider_compulsary_update_force_update'] = (!empty($this->request->getPost('provider_compulsary_update_force_update'))) ? $this->request->getPost('provider_compulsary_update_force_update') : (isset($data['provider_compulsary_update_force_update']) ? $data['provider_compulsary_update_force_update'] : "");

                $updatedData['customer_app_maintenance_schedule_date'] = (!empty($this->request->getPost('customer_app_maintenance_schedule_date'))) ? $this->request->getPost('customer_app_maintenance_schedule_date') : (isset($data['customer_app_maintenance_schedule_date']) ? $data['customer_app_maintenance_schedule_date'] : "");
                $updatedData['message_for_customer_application'] = (!empty($this->request->getPost('message_for_customer_application'))) ? $this->request->getPost('message_for_customer_application') : (isset($data['message_for_customer_application']) ? $data['message_for_customer_application'] : "");
                $updatedData['customer_app_maintenance_mode'] = (!empty($this->request->getPost('customer_app_maintenance_mode'))) ? ($this->request->getPost('customer_app_maintenance_mode')) : (isset($data['customer_app_maintenance_mode']) ? $data['customer_app_maintenance_mode'] : "");

                $updatedData['provider_app_maintenance_schedule_date'] = (!empty($this->request->getPost('provider_app_maintenance_schedule_date'))) ? ($this->request->getPost('provider_app_maintenance_schedule_date')) : (isset($data['provider_app_maintenance_schedule_date']) ? $data['provider_app_maintenance_schedule_date'] : "");
                $updatedData['message_for_provider_application'] = (!empty($this->request->getPost('message_for_provider_application'))) ? $this->request->getPost('message_for_provider_application') : (isset($data['message_for_provider_application']) ? $data['message_for_provider_application'] : "");
                $updatedData['provider_app_maintenance_mode'] = (!empty($this->request->getPost('provider_app_maintenance_mode'))) ? $this->request->getPost('provider_app_maintenance_mode') : (isset($data['provider_app_maintenance_mode']) ? $data['provider_app_maintenance_mode'] : "");

                $updatedData['provider_location_in_provider_details'] = (!empty($this->request->getPost('provider_location_in_provider_details'))) ? $this->request->getPost('provider_location_in_provider_details') : (isset($data['provider_location_in_provider_details']) ? $data['provider_location_in_provider_details'] : "");



                // general page settings 
                $updatedData['company_title'] = (!empty($this->request->getPost('company_title'))) ? $this->request->getPost('company_title') : (isset($data['company_title']) ? ($data['company_title']) : "");
                $updatedData['support_name'] = (!empty($this->request->getPost('support_name'))) ? $this->request->getPost('support_name') : (isset($data['support_name']) ? ($data['support_name']) : "");
                $updatedData['support_email'] = (!empty($this->request->getPost('support_email'))) ? $this->request->getPost('support_email') : (isset($data['support_email']) ? ($data['support_email']) : "");
                $updatedData['phone'] = (!empty($this->request->getPost('phone'))) ? $this->request->getPost('phone') : (isset($data['phone']) ? ($data['phone']) : "");
                $updatedData['system_timezone_gmt'] = (!empty($this->request->getPost('system_timezone_gmt'))) ? $updatedData['system_timezone_gmt'] : (isset($data['system_timezone_gmt']) ? ($data['system_timezone_gmt']) : "");
                $updatedData['system_timezone'] = (!empty($this->request->getPost('system_timezone'))) ? $this->request->getPost('system_timezone') : (isset($data['system_timezone']) ? ($data['system_timezone']) : "");
                $updatedData['primary_color'] = (!empty($this->request->getPost('primary_color'))) ? $this->request->getPost('primary_color') : (isset($data['primary_color']) ? ($data['primary_color']) : "");
                $updatedData['secondary_color'] = (!empty($this->request->getPost('secondary_color'))) ? $this->request->getPost('secondary_color') : (isset($data['secondary_color']) ? ($data['secondary_color']) : "");
                $updatedData['primary_shadow'] = (!empty($this->request->getPost('primary_shadow'))) ? $this->request->getPost('primary_shadow') : (isset($data['primary_shadow']) ? ($data['primary_shadow']) : "");
                $updatedData['max_serviceable_distance'] = (!empty($this->request->getPost('max_serviceable_distance'))) ? $this->request->getPost('max_serviceable_distance') : (isset($data['max_serviceable_distance']) ? $data['max_serviceable_distance'] : "");
                $updatedData['address'] = (!empty($this->request->getPost('address'))) ? $this->request->getPost('address') : (isset($data['address']) ? ($data['address']) : "");
                $updatedData['short_description'] = (!empty($this->request->getPost('short_description'))) ? $this->request->getPost('short_description') : (isset($data['short_description']) ? ($data['short_description']) : "");
                $updatedData['copyright_details'] = (!empty($this->request->getPost('copyright_details'))) ? $this->request->getPost('copyright_details') : (isset($data['copyright_details']) ? ($data['copyright_details']) : "");
                $updatedData['booking_auto_cancle_duration'] = (!empty($this->request->getPost('booking_auto_cancle_duration'))) ? $this->request->getPost('booking_auto_cancle_duration') : (isset($data['booking_auto_cancle']) ? ($data['booking_auto_cancle_duration']) : "");

                // $updatedData['prepaid_booking_cancellation_time'] = (!empty($this->request->getPost('prepaid_booking_cancellation_time'))) ? $this->request->getPost('prepaid_booking_cancellation_time') : (isset($data['prepaid_booking_cancellation_time']) ? ($data['prepaid_booking_cancellation_time']) : "");

                $json_string = json_encode($updatedData);


                if ($flag == 0) {
                    if ($this->update_setting('general_settings', $json_string)) {
                        $_SESSION['toastMessage']  = 'Unable to update the settings.';
                        $_SESSION['toastMessageType']  = 'error';
                    } else {
                        $_SESSION['toastMessage'] = 'Settings has been successfuly updated.';
                        $_SESSION['toastMessageType']  = 'success';
                    }
                } else {
                    $_SESSION['toastMessage'] = 'please insert valid image.';
                    $_SESSION['toastMessageType']  = 'error';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/general-settings')->withCookies();
            }



            $this->builder->select('value');
            $this->builder->where('variable', 'general_settings');
            $query = $this->builder->get()->getResultArray();

            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                if (!empty($settings)) {
                    $this->data = array_merge($this->data, $settings);
                }
            }
            $this->data['timezones'] = get_timezone_array();
            $this->data['title'] = 'General Settings | Admin Panel';
            $this->data['main_page'] = 'general_settings';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function email_settings()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            if ($this->request->getGet('update')) {
                if ($this->superadmin == "rajasthantech.info@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {

                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = DEMO_MODE_ERROR;
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/email-settings')->withCookies();
                    }
                }
                $this->validation->setRules(
                    [
                        'smtpHost' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter SMTP Host"
                            ]
                        ],
                        'smtpUsername' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter SMTP Username"
                            ]
                        ],
                        'smtpPassword' => [
                            "rules" => 'required',
                            "errors" => [
                                "required" => "Please enter SMTP Password"
                            ]
                        ],
                        'smtpPort' => [
                            "rules" => 'required|numeric',
                            "errors" => [
                                "required" => "Please enter SMTP Port Number",
                                "numeric" => "Please enter numeric value for SMTP Port Number"
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
                // $this->validation->setRules(
                //     [
                //         'smtpHost' => 'required',
                //         'smtpUsername' => 'required',
                //         'smtpPassword' => 'required',
                //         'smtpPort' => 'required',
                //     ]
                // );
                // if (!$this->validation->withRequest($this->request)->run()) {
                //     $errors  = $this->validation->getErrors();
                //     $response['error'] = true;
                //     $response['message'] = $errors;

                //     // $response['csrfName'] = csrf_token();
                //     // $response['csrfHash'] = csrf_hash();
                //     $response['data'] = [];
                //     return $this->response->setJSON($response);
                // }

                $updatedData = $this->request->getGet();
                $json_string = json_encode($updatedData);

                if ($this->update_setting('email_settings', $json_string)) {
                    $_SESSION['toastMessage']  = 'Unable to update the email settings.';
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = 'Email settings has been successfuly updated.';
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/email-settings')->withCookies();
            }

            $this->builder->select('value');
            $this->builder->where('variable', 'email_settings');
            $query = $this->builder->get()->getResultArray();

            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }

            $this->data['title'] = 'Email Settings | Admin Panel';
            $this->data['main_page'] = 'email_settings';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function pg_settings()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {

            if ($this->request->getPost('update')) {
                if ($this->superadmin == "rajasthantech.info@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {

                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = DEMO_MODE_ERROR;
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/pg-settings')->withCookies();
                    }
                }

                $updatedData = $this->request->getPost();

                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);




                if ($this->update_setting('payment_gateways_settings', $json_string)) {
                    $_SESSION['toastMessage']  = 'Unable to update the payment gateways settings.';
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = 'Payment gate ways settings has been successfuly updated.';
                    $_SESSION['toastMessageType']  = 'success';
                    $this->session->markAsFlashdata('toastMessage');
                    $this->session->markAsFlashdata('toastMessageType');
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/pg-settings')->withCookies();
            } else {


                $this->builder->select('value');
                $this->builder->where('variable', 'payment_gateways_settings');
                $query = $this->builder->get()->getResultArray();
                if (count($query) == 1) {
                    $settings = $query[0]['value'];
                    $settings = json_decode($settings, true);
                    $this->data = array_merge($this->data, $settings);
                }


                $this->data['title'] = 'Payment Gateways Settings | Admin Panel';
                $this->data['main_page'] = 'payment_gateways';
                return view('backend/admin/template', $this->data);
            }
        } else {
            return redirect('admin/login');
        }
    }

    public function tts_settings()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            if ($this->request->getPost('update')) {
                if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                    $response['error'] = true;
                    $response['message'] = DEMO_MODE_ERROR;
                    $response['csrfName'] = csrf_token();
                    $response['csrfHash'] = csrf_hash();
                    return $this->response->setJSON($response);
                }

                $updatedData = $this->request->getPost();
                $json_string = json_encode($updatedData);

                if ($this->update_setting('tts_config', $json_string)) {
                    $_SESSION['toastMessage']  = 'Unable to update the text to speech configuratins.';
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = 'Text to Speech Configuratins has been successfuly updated.';
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/tts-settings')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'tts_config');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                if (!empty($query[0]['value'])) {

                    $settings = $query[0]['value'];
                    $settings = json_decode($settings, true);
                    $this->data = array_merge($this->data, $settings);
                }
            }

            $this->data['title'] = 'Text to Speech Settings | Admin Panel';
            $this->data['main_page'] = 'tts_config';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function privacy_policy()
    {

        if ($this->isLoggedIn && $this->userIsAdmin) {

            if ($this->request->getPost('update')) {
                if ($this->superadmin == "rajasthantech.info@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {

                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = DEMO_MODE_ERROR;
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/privacy-policy')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                unset($updatedData['update']);
                unset($updatedData['files']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);

                if ($this->update_setting('privacy_policy', $json_string)) {
                    $_SESSION['toastMessage']  = 'Unable to update the privacy policy.';
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = 'privacy Policy has been successfuly updated.';
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/privacy-policy')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'privacy_policy');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {

                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);

                $this->data = array_merge($this->data, $settings);
            }

            $this->data['title'] = 'Privacy Policy Settings | Admin Panel';
            $this->data['main_page'] = 'privacy_policy';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function customer_privacy_policy_page()
    {
        $settings = get_settings('general_settings', true);
        $this->data['title'] = 'Privacy Policy | ' . $settings['company_title'];
        $this->data['meta_description'] = 'Privacy Policy | ' . $settings['company_title'];
        $this->data['privacy_policy'] = get_settings('customer_privacy_policy', true);
        $this->data['settings'] =  $settings;
        return view('backend/admin/pages/customer_app_privacy_policy', $this->data);
    }


    public function customer_tearms_and_condition()
    {
        $settings = get_settings('general_settings', true);
        $this->data['title'] = 'Customer Terms & Condition  | ' . $settings['company_title'];
        $this->data['meta_description'] = 'Customer Terms & Condition  | ' . $settings['company_title'];
        $this->data['customer_terms_conditions'] = get_settings('customer_terms_conditions', true);
        $this->data['settings'] =  $settings;
        return view('backend/admin/pages/customer_terms_and_condition_page', $this->data);
    }


    public function provider_terms_and_condition()
    {
        $settings = get_settings('general_settings', true);
        $this->data['title'] = 'Provider Privacy Policy  | ' . $settings['company_title'];
        $this->data['meta_description'] = 'Provider Privacy Policy  | ' . $settings['company_title'];
        $this->data['privacy_policy'] = get_settings('privacy_policy', true);
        $this->data['settings'] =  $settings;
        return view('backend/admin/pages/provider_terms_and_condition_page', $this->data);
    }







    public function partner_privacy_policy_page()
    {
        $settings = get_settings('general_settings', true);
        $this->data['title'] = 'Privacy Policy | ' . $settings['company_title'];
        $this->data['meta_description'] = 'Privacy Policy | ' . $settings['company_title'];
        $this->data['privacy_policy'] = get_settings('privacy_policy', true);
        $this->data['settings'] =  $settings;
        return view('backend/admin/pages/partner_app_privacy_policy', $this->data);
    }

    public function customer_privacy_policy()
    {

        if ($this->isLoggedIn && $this->userIsAdmin) {

            if ($this->request->getPost('update')) {
                if ($this->superadmin == "rajasthantech.info@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {

                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = DEMO_MODE_ERROR;
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/customer-privacy-policy')->withCookies();
                    }
                }


                $updatedData = $this->request->getPost();
                unset($updatedData['update']);
                unset($updatedData['files']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);

                if ($this->update_setting('customer_privacy_policy', $json_string)) {
                    $_SESSION['toastMessage']  = 'Unable to update the privacy policy.';
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = 'privacy Policy has been successfuly updated.';
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/customer-privacy-policy')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'customer_privacy_policy');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {

                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);

                $this->data = array_merge($this->data, $settings);
            }

            $this->data['title'] = 'Privacy Policy Settings | Admin Panel';
            $this->data['main_page'] = 'customer_privacy_policy';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }


    public function refund_policy_page()
    {

        $settings = get_settings('general_settings', true);
        $this->data['title'] = 'Refund Policy | ' . $settings['company_title'];
        $this->data['meta_description'] = 'Refund Policy | ' . $settings['company_title'];
        $this->data['refund_policy'] = get_settings('refund_policy', true);
        $this->data['settings'] =  $settings;
        return view('backend/admin/pages/refund_policy_page', $this->data);
    }
    public function refund_policy()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {

            if ($this->request->getPost('update')) {
                if ($this->superadmin == "rajasthantech.info@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {

                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = DEMO_MODE_ERROR;
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/refund-policy')->withCookies();
                    }
                }

                $updatedData = $this->request->getPost();
                unset($updatedData['update']);
                unset($updatedData['files']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);

                if ($this->update_setting('refund_policy', $json_string)) {
                    $_SESSION['toastMessage']  = 'Unable to update the refund policy.';
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = 'refund Policy has been successfuly updated.';
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/refund-policy')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'refund_policy');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {

                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);

                $this->data = array_merge($this->data, $settings);
            }

            $this->data['title'] = 'Refund Policy Settings | Admin Panel';
            $this->data['main_page'] = 'refund_policy';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function updater()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {


            $this->data['title'] = 'Updater | Admin Panel';
            $this->data['main_page'] = 'updater';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function terms_and_conditions()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            if ($this->request->getPost('update')) {
                if ($this->superadmin == "rajasthantech.info@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {

                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = DEMO_MODE_ERROR;
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/terms-and-conditions')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                unset($updatedData['files']);
                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);

                if ($this->update_setting('terms_conditions', $json_string)) {
                    $_SESSION['toastMessage']  = 'Unable to update the terms & conditions.';
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = 'Terms & Conditions has been successfuly updated.';
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/terms-and-conditions')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'terms_conditions');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }

            $this->data['title'] = 'Terms & Conditions Settings - Admin Panel | eDemand';
            $this->data['main_page'] = 'terms_and_conditions';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function customer_terms_and_conditions()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            if ($this->request->getPost('update')) {
                if ($this->superadmin == "rajasthantech.info@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {

                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = DEMO_MODE_ERROR;
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/customer-terms-and-conditions')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                unset($updatedData['files']);
                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);

                if ($this->update_setting('customer_terms_conditions', $json_string)) {
                    $_SESSION['toastMessage']  = 'Unable to update the terms & conditions.';
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = 'Terms & Conditions has been successfuly updated.';
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/customer-terms-and-conditions')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'customer_terms_conditions');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }
            $this->data['title'] = 'Terms & Conditions Settings - Admin Panel | eDemand';
            $this->data['main_page'] = 'customer_terms_and_conditions';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function about_us()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            if ($this->request->getPost('update')) {
                if ($this->superadmin == "rajasthantech.info@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {

                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = DEMO_MODE_ERROR;
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/about-us')->withCookies();
                    }
                }

                $updatedData = $this->request->getPost();
                unset($updatedData['files']);
                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);

                if ($this->update_setting('about_us', $json_string)) {
                    $_SESSION['toastMessage']  = 'Unable to update about-us section.';
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = 'About-us section has been successfuly updated.';
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/about-us')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'about_us');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }

            $this->data['title'] = 'About us Settings | Admin Panel';
            $this->data['main_page'] = 'about_us';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function contact_us()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            if ($this->request->getPost('update')) {
                if ($this->superadmin == "rajasthantech.info@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {

                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = DEMO_MODE_ERROR;
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/contact-us')->withCookies();
                    }
                }


                $updatedData = $this->request->getPost();
                unset($updatedData['files']);
                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);
                $json_string = json_encode($updatedData);

                if ($this->update_setting('contact_us', $json_string)) {
                    $_SESSION['toastMessage']  = 'Unable to update contact-us section.';
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = 'Contact-us section has been successfuly updated.';
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/contact-us')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'contact_us');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }

            $this->data['title'] = 'Contact us Settings | Admin Panel';
            $this->data['main_page'] = 'contact_us';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function api_key_settings()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {

            if ($this->request->getPost('update')) {
                if ($this->superadmin == "rajasthantech.info@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {

                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = DEMO_MODE_ERROR;
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/api_key_settings')->withCookies();
                    }
                }

                $updatedData = $this->request->getPost();
                unset($updatedData['files']);
                unset($updatedData[csrf_token()]);
                unset($updatedData['update']);
                $json_string = json_encode($updatedData);


                if ($this->update_setting('api_key_settings', $json_string)) {
                    $_SESSION['toastMessage']  = 'Unable to update PAI key section.';
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = ' PAI key  section has been successfuly updated.';
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/api_key_settings')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'api_key_settings');
            $query = $this->builder->get()->getResultArray();

            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);



                $this->data = array_merge($this->data, $settings);
            }

            $this->data['title'] = 'API key Settings | Admin Panel';
            $this->data['main_page'] = 'api_key_settings';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    private function update_setting($variable, $value)
    {
        $this->builder->where('variable', $variable);
        if (exists(['variable' => $variable], 'settings')) {
            $this->db->transStart();
            $this->builder->update(['value' => $value]);
            $this->db->transComplete();
        } else {
            $this->db->transStart();
            $this->builder->insert(['variable' => $variable, 'value' => $value]);
            $this->db->transComplete();
        }

        return $this->db->transComplete() ? true : false;
    }

    public function themes()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            if ($this->request->getPost('update')) {
            }
            $this->data["themes"] = fetch_details('themes', [], [], null, '0', 'id', "ASC");

            $this->data['title'] = 'About us Settings | Admin Panel';
            $this->data['main_page'] = 'themes';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function system_tax_settings()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {

            if ($this->request->getPost('update')) {
                if ($this->superadmin == "rajasthantech.info@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {

                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = DEMO_MODE_ERROR;
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/system_tax_settings')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();
                unset($updatedData['files']);
                unset($updatedData[csrf_token()]);
                unset($updatedData['update']);
                $json_string = json_encode($updatedData);

                if ($this->update_setting('system_tax_settings', $json_string)) {
                    $_SESSION['toastMessage']  = 'Unable to update system tax settings.';
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = ' System Tax settings successfuly updated.';
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/system_tax_settings')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'system_tax_settings');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }

            $this->data['title'] = 'System Tax Settings | Admin Panel';
            $this->data['main_page'] = 'system_tax_settings';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function app_settings()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            if ($this->request->getPost('update')) {



                if ($this->superadmin == "rajasthantech.info@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {

                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = DEMO_MODE_ERROR;
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/app')->withCookies();
                    }
                }
                $updatedData = $this->request->getPost();


                $flag = 0;
                $favicon = false;
                $halfLogo = false;
                $logo = false;
                $partner_favicon = false;
                $partner_halfLogo = false;
                $partner_logo = false;
                $files = array();
                $data = get_settings('general_settings', true);





                if (!empty($_FILES['favicon'])) {

                    if ($_FILES['favicon']['name'] != "") {
                        if (!valid_image('favicon')) {
                            $flag = 1;
                        } else {
                            $favicon = true;
                        }
                    }
                }



                if (!empty($_FILES['halfLogo'])) {

                    if ($_FILES['halfLogo']['name'] != "") {
                        if (!valid_image('halfLogo')) {
                            $flag = 1;
                        } else {
                            $halfLogo = true;
                        }
                    }
                }



                if (!empty($_FILES['logo'])) {

                    if ($_FILES['logo']['name'] != "") {
                        if (!valid_image('logo')) {
                            $flag = 1;
                        } else {
                            $logo = true;
                        }
                    }
                }

                if (!empty($_FILES['logo'])) {

                    if ($_FILES['partner_favicon']['name'] != "") {
                        if (!valid_image('partner_favicon')) {
                            $flag = 1;
                        } else {
                            $partner_favicon = true;
                        }
                    }
                }

                if (!empty($_FILES['partner_halfLogo'])) {

                    if ($_FILES['partner_halfLogo']['name'] != "") {
                        if (!valid_image('partner_halfLogo')) {
                            $flag = 1;
                        } else {
                            $partner_halfLogo = true;
                        }
                    }
                }

                if (!empty($_FILES['partner_logo'])) {

                    if ($_FILES['partner_logo']['name'] != "") {
                        if (!valid_image('partner_logo')) {
                            $flag = 1;
                        } else {
                            $partner_logo = true;
                        }
                    }
                }
                if ($favicon) {

                    $file = $this->request->getFile('favicon');
                    $path = FCPATH . 'public/uploads/site/';
                    $image = $file->getName();
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                    $updatedData['favicon'] = $newName;
                } else {

                    $updatedData['favicon'] = isset($data['favicon']) ? $data['favicon'] : "";
                }
                // die;
                if ($logo) {
                    $file = $this->request->getFile('logo');
                    $path = FCPATH . 'public/uploads/site/';
                    $image = $file->getName();
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                    $updatedData['logo'] = $newName;
                } else {

                    $updatedData['logo'] = isset($data['logo']) ? $data['logo'] : "";
                }
                if ($halfLogo) {
                    $file = $this->request->getFile('halfLogo');
                    $path = FCPATH . 'public/uploads/site/';
                    $image = $file->getName();
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                    $updatedData['half_logo'] = $newName;
                } else {

                    $updatedData['half_logo'] = isset($data['half_logo']) ? $data['half_logo'] : "";
                }
                if ($partner_favicon) {
                    $file = $this->request->getFile('partner_favicon');
                    $path = FCPATH . 'public/uploads/site/';
                    $image = $file->getName();
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                    $updatedData['partner_favicon'] = $newName;
                } else {

                    $updatedData['partner_favicon'] = isset($data['partner_favicon']) ? $data['partner_favicon'] : "";
                }
                if ($partner_logo) {
                    $file = $this->request->getFile('partner_logo');
                    $path = FCPATH . 'public/uploads/site/';
                    $image = $file->getName();
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                    $updatedData['partner_logo'] = $newName;
                } else {

                    $updatedData['partner_logo'] = isset($data['partner_logo']) ? $data['partner_logo'] : "";
                }



                // if ($updatedData['system_timezone_gmt'] == " 00:00") {
                //     $updatedData['system_timezone_gmt'] = '+' . trim($updatedData['system_timezone_gmt']);
                // }
                if ($partner_halfLogo) {
                    $file = $this->request->getFile('partner_halfLogo');
                    $path = FCPATH . 'public/uploads/site/';
                    $image = $file->getName();
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                    $updatedData['partner_half_logo'] = $newName;
                } else {

                    $updatedData['partner_half_logo'] = isset($data['partner_half_logo']) ? $data['partner_half_logo'] : '';
                }


                if (!empty($updatedData['system_timezone_gmt'])) {

                    if ($updatedData['system_timezone_gmt'] == " 00:00") {
                        $updatedData['system_timezone_gmt'] = '+' . trim($updatedData['system_timezone_gmt']);
                    }
                }


                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);







                // app page settings 
                $updatedData['currency'] = (!empty($this->request->getPost('currency'))) ? $this->request->getPost('currency') : (isset($data['currency']) ? $data['currency'] : "");

                $updatedData['country_currency_code'] = (!empty($this->request->getPost('country_currency_code'))) ? $this->request->getPost('country_currency_code') : (isset($data['country_currency_code']) ? $data['country_currency_code'] : "");
                // $updatedData['decimal_point']=(!empty($this->request->getPost('decimal_point')))?$this->request->getPost('decimal_point'):(isset($data['decimal_point'])?$data['decimal_point']:"");

                if ($this->request->getPost('decimal_point') == 0) {

                    $updatedData['decimal_point'] = "0";
                } elseif (!empty($this->request->getPost('decimal_point'))) {


                    $updatedData['decimal_point'] = $this->request->getPost('decimal_point');
                } else {


                    $updatedData['decimal_point'] = $data['decimal_point'];
                }


                $updatedData['customer_current_version_android_app'] = (!empty($this->request->getPost('customer_current_version_android_app'))) ? $this->request->getPost('customer_current_version_android_app') : (isset($data['customer_current_version_android_app']) ? $data['customer_current_version_android_app'] : "");
                $updatedData['customer_current_version_ios_app'] = (!empty($this->request->getPost('customer_current_version_ios_app'))) ? $this->request->getPost('customer_current_version_ios_app') : (isset($data['customer_current_version_ios_app']) ? $data['customer_current_version_ios_app'] : "");
                // $updatedData['customer_compulsary_update_force_update'] = (!empty($this->request->getPost('customer_compulsary_update_force_update'))) ? ($this->request->getPost('customer_compulsary_update_force_update')) : (isset($data['customer_compulsary_update_force_update']) ? $data['customer_compulsary_update_force_update'] : "");

                $updatedData['provider_current_version_android_app'] = (!empty($this->request->getPost('provider_current_version_android_app'))) ? $this->request->getPost('provider_current_version_android_app') : (isset($data['provider_current_version_android_app']) ? $data['provider_current_version_android_app'] : "");
                $updatedData['provider_current_version_ios_app'] = (!empty($this->request->getPost('provider_current_version_ios_app'))) ? $this->request->getPost('provider_current_version_ios_app') : (isset($data['provider_current_version_ios_app']) ? $data['provider_current_version_ios_app'] : "");
                // $updatedData['provider_compulsary_update_force_update'] = (!empty($this->request->getPost('provider_compulsary_update_force_update'))) ? $this->request->getPost('provider_compulsary_update_force_update') : (isset($data['provider_compulsary_update_force_update']) ? $data['provider_compulsary_update_force_update'] : "");

                $updatedData['customer_app_maintenance_schedule_date'] = (!empty($this->request->getPost('customer_app_maintenance_schedule_date'))) ? $this->request->getPost('customer_app_maintenance_schedule_date') : (isset($data['customer_app_maintenance_schedule_date']) ? $data['customer_app_maintenance_schedule_date'] : "");
                $updatedData['message_for_customer_application'] = (!empty($this->request->getPost('message_for_customer_application'))) ? $this->request->getPost('message_for_customer_application') : (isset($data['message_for_customer_application']) ? $data['message_for_customer_application'] : "");
                // $updatedData['customer_app_maintenance_mode'] = (!empty($this->request->getPost('customer_app_maintenance_mode'))) ? ($this->request->getPost('customer_app_maintenance_mode')) : (isset($data['customer_app_maintenance_mode']) ? $data['customer_app_maintenance_mode'] : "");

                $updatedData['provider_app_maintenance_schedule_date'] = (!empty($this->request->getPost('provider_app_maintenance_schedule_date'))) ? ($this->request->getPost('provider_app_maintenance_schedule_date')) : (isset($data['provider_app_maintenance_schedule_date']) ? $data['provider_app_maintenance_schedule_date'] : "");
                $updatedData['message_for_provider_application'] = (!empty($this->request->getPost('message_for_provider_application'))) ? $this->request->getPost('message_for_provider_application') : (isset($data['message_for_provider_application']) ? $data['message_for_provider_application'] : "");
                // $updatedData['provider_app_maintenance_mode'] = (!empty($this->request->getPost('provider_app_maintenance_mode'))) ? $this->request->getPost('provider_app_maintenance_mode') : (isset($data['provider_app_maintenance_mode']) ? $data['provider_app_maintenance_mode'] : "");


                if ($this->request->getPost('customer_compulsary_update_force_update') == 0) {

                    $updatedData['customer_compulsary_update_force_update'] = "0";
                } elseif (!empty($this->request->getPost('customer_compulsary_update_force_update'))) {
                    $updatedData['customer_compulsary_update_force_update'] = $this->request->getPost('customer_compulsary_update_force_update');
                } else {


                    $updatedData['customer_compulsary_update_force_update'] = $data['customer_compulsary_update_force_update'];
                }


                if ($this->request->getPost('provider_compulsary_update_force_update') == 0) {

                    $updatedData['provider_compulsary_update_force_update'] = "0";
                } elseif (!empty($this->request->getPost('provider_compulsary_update_force_update'))) {
                    $updatedData['provider_compulsary_update_force_update'] = $this->request->getPost('provider_compulsary_update_force_update');
                } else {


                    $updatedData['provider_compulsary_update_force_update'] = $data['provider_compulsary_update_force_update'];
                }

                if ($this->request->getPost('provider_location_in_provider_details') == 0) {

                    $updatedData['provider_location_in_provider_details'] = "0";
                } elseif (!empty($this->request->getPost('provider_location_in_provider_details'))) {
                    $updatedData['provider_location_in_provider_details'] = $this->request->getPost('provider_location_in_provider_details');
                } else {


                    $updatedData['provider_location_in_provider_details'] = $data['provider_location_in_provider_details'];
                }
                if ($this->request->getPost('provider_app_maintenance_mode') == 0) {

                    $updatedData['provider_app_maintenance_mode'] = "0";
                } elseif (!empty($this->request->getPost('provider_app_maintenance_mode'))) {
                    $updatedData['provider_app_maintenance_mode'] = $this->request->getPost('provider_app_maintenance_mode');
                } else {


                    $updatedData['provider_app_maintenance_mode'] = $data['provider_app_maintenance_mode'];
                }



                if ($this->request->getPost('customer_app_maintenance_mode') == 0) {

                    $updatedData['customer_app_maintenance_mode'] = "0";
                } elseif (!empty($this->request->getPost('customer_app_maintenance_mode'))) {
                    $updatedData['customer_app_maintenance_mode'] = $this->request->getPost('customer_app_maintenance_mode');
                } else {


                    $updatedData['customer_app_maintenance_mode'] = $data['customer_app_maintenance_mode'];
                }




                // general page settings 
                $updatedData['company_title'] = (!empty($this->request->getPost('company_title'))) ? $this->request->getPost('company_title') : (isset($data['company_title']) ? ($data['company_title']) : "");
                $updatedData['support_name'] = (!empty($this->request->getPost('support_name'))) ? $this->request->getPost('support_name') : (isset($data['support_name']) ? ($data['support_name']) : "");
                $updatedData['support_email'] = (!empty($this->request->getPost('support_email'))) ? $this->request->getPost('support_email') : (isset($data['support_email']) ? ($data['support_email']) : "");
                $updatedData['phone'] = (!empty($this->request->getPost('phone'))) ? $this->request->getPost('phone') : (isset($data['phone']) ? ($data['phone']) : "");
                $updatedData['system_timezone_gmt'] = (!empty($this->request->getPost('system_timezone_gmt'))) ? $updatedData['system_timezone_gmt'] : (isset($data['system_timezone_gmt']) ? ($data['system_timezone_gmt']) : "");
                $updatedData['system_timezone'] = (!empty($this->request->getPost('system_timezone'))) ? $this->request->getPost('system_timezone') : (isset($data['system_timezone']) ? ($data['system_timezone']) : "");
                $updatedData['primary_color'] = (!empty($this->request->getPost('primary_color'))) ? $this->request->getPost('primary_color') : (isset($data['primary_color']) ? ($data['primary_color']) : "");
                $updatedData['secondary_color'] = (!empty($this->request->getPost('secondary_color'))) ? $this->request->getPost('secondary_color') : (isset($data['secondary_color']) ? ($data['secondary_color']) : "");
                $updatedData['primary_shadow'] = (!empty($this->request->getPost('primary_shadow'))) ? $this->request->getPost('primary_shadow') : (isset($data['primary_shadow']) ? ($data['primary_shadow']) : "");
                $updatedData['max_serviceable_distance'] = (!empty($this->request->getPost('max_serviceable_distance'))) ? $this->request->getPost('max_serviceable_distance') : (isset($data['max_serviceable_distance']) ? $data['max_serviceable_distance'] : "");
                $updatedData['address'] = (!empty($this->request->getPost('address'))) ? $this->request->getPost('address') : (isset($data['address']) ? ($data['address']) : "");
                $updatedData['short_description'] = (!empty($this->request->getPost('short_description'))) ? $this->request->getPost('short_description') : (isset($data['short_description']) ? ($data['short_description']) : "");
                $updatedData['copyright_details'] = (!empty($this->request->getPost('copyright_details'))) ? $this->request->getPost('copyright_details') : (isset($data['copyright_details']) ? ($data['copyright_details']) : "");

                $updatedData['booking_auto_cancle_duration'] = (!empty($this->request->getPost('booking_auto_cancle_duration'))) ? $this->request->getPost('booking_auto_cancle_duration') : (isset($data['booking_auto_cancle_duration']) ? ($data['booking_auto_cancle_duration']) : "");

                // $updatedData['prepaid_booking_cancellation_time'] = (!empty($this->request->getPost('prepaid_booking_cancellation_time'))) ? $this->request->getPost('prepaid_booking_cancellation_time') : (isset($data['prepaid_booking_cancellation_time']) ? ($data['prepaid_booking_cancellation_time']) : "");


                unset($updatedData['update']);
                unset($updatedData[csrf_token()]);

                $json_string = json_encode($updatedData);

                if ($this->update_setting('general_settings', $json_string)) {
                    $_SESSION['toastMessage']  = 'Unable to update the App settings.';
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = 'App settings has been successfuly updated.';
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/app')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'general_settings');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                if (!empty($settings)) {
                    $this->data = array_merge($this->data, $settings);
                }
            }
            $this->data['timezones'] = get_timezone_array();
            $this->data['title'] = 'App Settings | Admin Panel';
            $this->data['main_page'] = 'app';
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }



    public function firebase_settings()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {

            if ($this->request->getPost('update')) {
                if ($this->superadmin == "rajasthantech.info@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {

                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = DEMO_MODE_ERROR;
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/firebase_settings')->withCookies();
                    }
                }

                $updatedData = $this->request->getPost();



                unset($updatedData[csrf_token()]);
                unset($updatedData['update']);
                $json_string = json_encode($updatedData);


                if ($this->update_setting('firebase_settings', $json_string)) {
                    $_SESSION['toastMessage']  = 'Unable to update Firebase section.';
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = ' Firebase has been successfuly updated.';
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/firebase_settings')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'firebase_settings');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }

            $this->data['title'] = 'Firebase Settings | Admin Panel';
            $this->data['main_page'] = 'firebase_settings';

            // print_r($this->data);
            // die;
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }

    public function web_setting_page()
    {
        $this->data['title'] = 'Web Settings | Admin Panel';
        $this->data['main_page'] = 'web_settings';
        $this->builder->select('value');
        $this->builder->where('variable', 'web_settings');
        $query = $this->builder->get()->getResultArray();
        if (count($query) == 1) {
            $settings = $query[0]['value'];
            $settings = json_decode($settings, true);
            $this->data = array_merge($this->data, $settings);
        }
        // print_r($this->data);
        // die;
        return view('backend/admin/template', $this->data);
    }
    public function web_setting_update()
    {




        $social_media = [];



        $updatedData['social_media'] = ($social_media);
        $updatedData['web_title'] = $_POST['web_title'];
        $updatedData['web_tagline'] = $_POST['web_tagline'];
        $updatedData['short_description'] = $_POST['short_description'];

        $updatedData['playstore_url'] = $_POST['playstore_url'];
        $updatedData['app_section_status'] = isset($_POST['app_section_status']) ? 1 : 0;

        $updatedData['applestore_url'] = $_POST['applestore_url'];





        if ($this->isLoggedIn && $this->userIsAdmin) {

            if ($this->request->getPost('update')) {
                if ($this->superadmin == "rajasthantech.info@gmail.com") {
                    defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
                } else {

                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                        $_SESSION['toastMessage'] = DEMO_MODE_ERROR;
                        $_SESSION['toastMessageType']  = 'error';
                        $this->session->markAsFlashdata('toastMessage');
                        $this->session->markAsFlashdata('toastMessageType');
                        return redirect()->to('admin/settings/web_setting')->withCookies();
                    }
                }


                $data = get_settings('web_settings', true);

                $uploadPath = FCPATH . 'public/uploads/web_settings/';

                uploadFile($this->request, 'web_logo', $uploadPath, $updatedData, $data);
                uploadFile($this->request, 'web_favicon', $uploadPath, $updatedData, $data);
                uploadFile($this->request, 'web_half_logo', $uploadPath, $updatedData, $data);

                $updatedSocialMedia = []; // Create a new array to store the updated social media data

                // Iterate through existing entries and check if they exist in $_POST['social_media']

                if (!empty($data['social_media'])) {

                    $updatedSocialMedia = [];
                }


                foreach ($_POST['social_media'] as $i => $item) {


                    if (($item['exist_url'] == 'new') && ($item['exist_file'] == 'new')) {
                        $path = FCPATH . 'public/uploads/web_settings/';
                        $newName = $_FILES['social_media']['name'][$i]['file']; // This is just an example; generate a unique name
                        $fileFullPath = $path . $newName;



                        if (move_uploaded_file($_FILES['social_media']['tmp_name'][$i]['file'], $fileFullPath)) {

                            $updatedSocialMedia[] = [
                                'url' => $item['url'],
                                'file' => $newName
                            ];
                        }
                    } else {
                        if ($item['exist_url'] != $item['url'] || !empty($_FILES['social_media']['name'][$i]['file'])) {
                            $updatedData['url'] = $item['url'];
                        } else {
                            $updatedData['url'] = $item['exist_url'];
                        }

                        if (!empty($_FILES['social_media']['name'][$i]['file'])) {
                            if ($_FILES['social_media']['name'][$i]['file'] != $item['exist_file']) {
                                $updatedData['file'] = $_FILES['social_media']['name'][$i]['file'];
                            } else {
                                $updatedData['file'] = $item['exist_file'];
                            }
                        } else {
                            $updatedData['file'] = $item['exist_file'];
                        }

                        $updatedSocialMedia[] = $updatedData;
                    }
                }
                $updatedData['social_media'] = $updatedSocialMedia;
                unset($updatedData[csrf_token()]);
                unset($updatedData['update']);
                $json_string = json_encode($updatedData);

                if ($this->update_setting('web_settings', $json_string)) {
                    $_SESSION['toastMessage']  = 'Unable to update Web Settings.';
                    $_SESSION['toastMessageType']  = 'error';
                } else {
                    $_SESSION['toastMessage'] = ' Web Settings has been successfuly updated.';
                    $_SESSION['toastMessageType']  = 'success';
                }
                $this->session->markAsFlashdata('toastMessage');
                $this->session->markAsFlashdata('toastMessageType');
                return redirect()->to('admin/settings/web_setting')->withCookies();
            }
            $this->builder->select('value');
            $this->builder->where('variable', 'web_settings');
            $query = $this->builder->get()->getResultArray();
            if (count($query) == 1) {
                $settings = $query[0]['value'];
                $settings = json_decode($settings, true);
                $this->data = array_merge($this->data, $settings);
            }

            $this->data['title'] = 'Web Settings Settings | Admin Panel';
            $this->data['main_page'] = 'web_settings';

            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }


    public function contry_codes()
    {

        $this->data['title'] = 'Country Code Settings Settings | Admin Panel';
        $this->data['main_page'] = 'country_code';

        return view('backend/admin/template', $this->data);
    }

    public function add_contry_code()
    {
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



        if ($this->isLoggedIn && $this->userIsAdmin) {

            $this->validation->setRules(
                [
                    'name' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter name"
                        ]
                    ],
                    'code' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter code"
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

            $data['code'] = ($_POST['code']);
            $data['name'] = ($_POST['name']);

            $contry_code = new Country_code_model();


            if ($contry_code->save($data)) {
                $response = [
                    'error' => false,
                    'message' => "Country code added successfully",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return json_encode($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => "please try again....",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return json_encode($response);
            }
        } else {
            return redirect('unauthorised');
        }
    }


    public function fetch_contry_code()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $where = [];
        $from_app = false;


        $contry_code = new Country_code_model();
        $data = $contry_code->list($from_app, $search, $limit, $offset, $sort, $order, $where);

        return $data;
    }

    public function delete_contry_code()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $response['error'] = true;
                $response['message'] = DEMO_MODE_ERROR;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                return $this->response->setJSON($response);
            }
            $db = \Config\Database::connect();
            $id = $this->request->getVar('id');
            $builder = $db->table('country_codes');
            $builder->where('id', $id);
            $data = fetch_details("country_codes", ['id' => $id]);


            $settings = fetch_details('country_codes', ['is_default' => 1]);

            if ($settings[0]['id'] ==  $id) {

                $response = [
                    'error' => true,
                    'message' => 'Default country code cannot be removed.',
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            if ($builder->delete()) {
                $response = [
                    'error' => false,
                    'message' => 'Country code Removed successfully.',
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

    public function store_default_language()
    {



        $settings = fetch_details('country_codes', ['is_default' => 1]);


        if (!empty($settings)) {


            $country_codes = fetch_details('country_codes', ['is_default' => 1]);
            $Country_code_model = new Country_code_model();
            $data['is_default'] = 0;
            $Country_code_model->update($country_codes[0]['id'], $data);


            $data2['is_default'] = 1;
            $Country_code_model2 = new Country_code_model();
            $Country_code_model2->update($_POST['id'], $data2);
        }

        $response = [
            'error' => false,
            'message' => 'Default setted.',
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'data' => []
        ];
        return $this->response->setJSON($response);
    }

    public function update_country_codes()
    {
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



        if ($this->isLoggedIn && $this->userIsAdmin) {

            $this->validation->setRules(
                [
                    'name' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter name"
                        ]
                    ],
                    'code' => [
                        "rules" => 'required',
                        "errors" => [
                            "required" => "Please enter code"
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

            $data['code'] = ($_POST['code']);
            $data['name'] = ($_POST['name']);

            $contry_code = new Country_code_model();

            

            if ($contry_code->update($_POST['id'], $data)) {
                $response = [
                    'error' => false,
                    'message' => "Country code updated successfully",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return json_encode($response);
            } else {
                $response = [
                    'error' => true,
                    'message' => "please try again....",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return json_encode($response);
            }
        } else {
            return redirect('unauthorised');
        }
    }
}
