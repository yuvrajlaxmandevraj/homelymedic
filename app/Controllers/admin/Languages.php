<?php

namespace App\Controllers\admin;

use App\Models\Language_model;

class Languages extends Admin
{
    public function __construct()
    {
        parent::__construct();
        $this->langauge = new Language_model();
    }
    public function index()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $session = session();
            $lang = $session->get('lang');
            if (empty($lang)) {
                $lang = 'en';
            }
            $this->data['code'] = $lang;
            $this->data['title'] = 'Language';
            $this->data['main_page'] = 'languages';
            $this->data['languages'] = fetch_details('languages', [], [], null, '0', 'id', 'ASC');
            return view('backend/admin/template', $this->data);
        } else {
            return redirect('admin/login');
        }
    }
    public function change($lang)
    {
        $session = session();
        $session->remove('lang');
        $session->set('lang', $lang);
        return redirect()->to("admin/languages/");
    }
    public function insert()
    {
        helper('files');
        helper('filesystem');
        $db = \Config\Database::connect();
        $name = trim($_POST['language_name']);
        $code = $_POST['language_code'];
        $langauge_file = $this->request->getFile('language_json');
        $path = "/public/uploads/languages/";
        $ext = $langauge_file->getExtension();
        $image_name = $code . '.' . $ext;
        $my_lang = $code;
        move_file($langauge_file, $path, $image_name);
        $check = $db->table('languages')->insert($data = ['language' => $name, 'code' => $code]);
        if ($check) {
            $data = json_decode(file_get_contents(base_url($path . "/" . $code . ".json")), false);
            $my_lang = $code;
            $langstr = "\$lang['label_language'] = \"$my_lang\";" . "\n";
            $langstr_final = "<?php
            ";
            foreach ($data as $key => $val) {
                $langstr_final .= "\$lang['$key'] = \"$val\";" . "\n";
            }
            $langstr_final .= 'return $lang;';
            if (!is_dir('./app/Language/' . $my_lang . '/')) {
                mkdir('./app/Language/' . $my_lang . '/', 0777, true);
            }
            if (file_exists('./app/Language/' . $my_lang . '/Text.php')) {
                delete_files('./app/Language/' . $my_lang . '/Text.php');
                write_file('./app/Language/' . $my_lang . '/Text.php', $langstr_final);
            } else {
                write_file('./app/Language/' . $my_lang . '/Text.php', $langstr_final);
            }
            $_SESSION['toastMessage'] = "Language added..";
            $_SESSION['toastMessageType'] = 'success';
            $this->session->markAsFlashdata('toastMessage');
            $this->session->markAsFlashdata('toastMessageType');
            return redirect()->to('admin/languages')->withCookies();
        } else {
            $_SESSION['toastMessage'] = "Error..";
            $_SESSION['toastMessageType'] = 'success';
            $this->session->markAsFlashdata('toastMessage');
            $this->session->markAsFlashdata('toastMessageType');
            return redirect()->to('admin/languages')->withCookies();
        }
    }
    //----------------------------------------------------------------------------------------------------------------
    public function remove()
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
            $builder = $db->table('languages');
            $builder->where('id', $id);
            $data = fetch_details("languages", ['id' => $id]);
           
            if (empty($data)) {
                return redirect('admin/login');
            }

            $code = $data[0]['code'];
            $old_path = "public/uploads/languages/" . $code . '.json';
            if ($code == "en") {
             
                $response = [
                    'error' => true,
                    'message' => 'Default language cannot be removed',
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => []
                ];
                return $this->response->setJSON($response);
            }
            if ($builder->delete()) {

                unlink($old_path);
                delete_directory("app/Language/$code/");
                $response = [
                    'error' => false,
                    'message' => 'Langauge Removed successfully',
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
    public function language_sample()
    {
        $filePath = (FCPATH . '/public/test.json');
        $headers = ['Content-Type: application/json'];
        $fileName = 'en.json';
        if (file_exists($filePath)) {
            return $this->response->download($filePath, null)->setFileName($fileName);
        } else {
            $_SESSION['toastMessage'] = "Cannot download";
            $_SESSION['toastMessageType'] = 'error';
            $this->session->markAsFlashdata('toastMessage');
            $this->session->markAsFlashdata('toastMessageType');
            return redirect()->to('admin/languages')->withCookies();
        }
    }
    public function language_old()
    {
        $uri = service('uri');
        $code = $uri->getSegments()[1];
        $filePath = (FCPATH . '/public/uploads/languages/' . $code . '.json');
        if (file_exists($filePath)) {
            return $this->response->download($filePath, null)->setFileName($code . ".json");
        } else {
            $_SESSION['toastMessage'] = "Cannot download";
            $_SESSION['toastMessageType'] = 'error';
            $this->session->markAsFlashdata('toastMessage');
            $this->session->markAsFlashdata('toastMessageType');
            return redirect()->to('admin/languages')->withCookies();
        }
    }
    public function list()
    {
        $limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? $_GET['limit'] : 10;
        $offset = (isset($_GET['offset']) && !empty($_GET['offset'])) ? $_GET['offset'] : 0;
        $sort = (isset($_GET['sort']) && !empty($_GET['sort'])) ? $_GET['sort'] : 'id';
        $order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : 'ASC';
        $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : '';
        $where = [];
        $from_app = false;
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $from_app = true;
        }
        $data = $this->langauge->list($from_app, $search, $limit, $offset, $sort, $order, $where);
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            if (!empty($data['data'])) {
                $response = [
                    'error' => false,
                    'message' => "Language fetched successfully",
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => $data['data']
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Langauge not found on',
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'data' => $data['data']
                ];
            }
            return $this->response->setJSON($response);
        }
        return $data;
    }
    public function update()
    {
        if ($this->isLoggedIn && $this->userIsAdmin) {
            $id = $this->request->getPost('id');
            $db      = \Config\Database::connect();
            $builder = $db->table('languages');
            $builder->select('*')->where('id', $id);
            $language_record = $builder->get()->getRow();
            $old_code = $language_record->code;
            if (isset($_POST) && !empty($_POST)) {
                $code = $this->request->getPost('edit_code');
                $name = $this->request->getPost('edit_name');
                $filePath = (FCPATH . '/public/uploads/languages/' . $code . '.json');
                $langauge_file = $this->request->getFile('update_language_json');
                $path = "/public/uploads/languages/";
                $old_path = "public/uploads/languages/" . $old_code . '.json';
                $ext = $langauge_file->getExtension();
                $file_name = $code . '.' . $ext;
                if ($langauge_file->getName() == "") {
                    rename(FCPATH . $path .  $old_code . '.json', FCPATH . $path . $code . '.json');
                    $data = json_decode(file_get_contents(base_url($path . "/" . $code . ".json")), false);
                    $langstr = "\$lang['label_language'] = \"$code\";" . "\n";
                    $langstr_final = "<?php
                    ";
                    foreach ($data as $key => $val) {
                        $langstr_final .= "\$lang['$key'] = \"$val\";" . "\n";
                    }
                    $langstr_final .= 'return $lang;';
                    if (!is_dir('./app/Language/' . $code . '/')) {
                        mkdir('./app/Language/' . $code . '/', 0777, true);
                    }
                    if (file_exists('./app/Language/' . $old_code . '/Text.php')) {
                        delete_directory("app/Language/$old_code/");
                        write_file('./app/Language/' . $code . '/Text.php', $langstr_final);
                    } else {
                        write_file('./app/Language/' . $code . '/Text.php', $langstr_final);
                    }
                    $data1['language'] = $name;
                    $data1['code'] = $code;
                    $upd =  $this->langauge->update($id, $data1);
                    $_SESSION['toastMessage'] = "Language updated successfully";
                    $_SESSION['toastMessageType'] = 'success';
                    $this->session->markAsFlashdata('toastMessage');
                    $this->session->markAsFlashdata('toastMessageType');
                    return redirect()->to('admin/languages')->withCookies();
                } else {
                    if (file_exists(FCPATH . '/public/uploads/languages/' . $old_code . '.json')) {
                        unlink($old_path);
                        move_file($langauge_file, $path, $file_name);
                        $data['language'] = $name;
                        $data['code'] = $code;
                        $upd =  $this->langauge->update($id, $data);
                        if ($upd) {
                            $data = json_decode(file_get_contents(base_url($path . "/" . $code . ".json")), false);
                            $langstr = "\$lang['label_language'] = \"$code\";" . "\n";
                            $langstr_final = "<?php
                             ";
                            foreach ($data as $key => $val) {
                                $langstr_final .= "\$lang['$key'] = \"$val\";" . "\n";
                            }
                            $langstr_final .= 'return $lang;';
                            if (!is_dir('./app/Language/' . $code . '/')) {
                                mkdir('./app/Language/' . $code . '/', 0777, true);
                            }
                            if (file_exists('./app/Language/' . $code . '/Text.php')) {
                                delete_files('./app/Language/' . $code . '/Text.php');
                                // delete_directory("app/Language/$old_code/");
                                write_file('./app/Language/' . $code . '/Text.php', $langstr_final);
                            } else {
                                delete_directory("app/Language/$old_code/");
                                write_file('./app/Language/' . $code . '/Text.php', $langstr_final);
                            }
                            $_SESSION['toastMessage'] = "Language updated successfully";
                            $_SESSION['toastMessageType'] = 'success';
                            $this->session->markAsFlashdata('toastMessage');
                            $this->session->markAsFlashdata('toastMessageType');
                            return redirect()->to('admin/languages')->withCookies();
                        } else {
                            $_SESSION['toastMessage'] = "Something went wrong";
                            $_SESSION['toastMessageType'] = 'success';
                            $this->session->markAsFlashdata('toastMessage');
                            $this->session->markAsFlashdata('toastMessageType');
                            return redirect()->to('admin/languages')->withCookies();
                        }
                    } else {
                        echo '2';
                        move_file($langauge_file, $path, $file_name);
                        $data['language'] = $name;
                        $data['code'] = $code;
                        $upd =  $this->langauge->update($id, $data);
                        if ($upd) {
                            $_SESSION['toastMessage'] = "Language updated successfully";
                            $_SESSION['toastMessageType'] = 'success';
                            $this->session->markAsFlashdata('toastMessage');
                            $this->session->markAsFlashdata('toastMessageType');
                            return redirect()->to('admin/languages')->withCookies();
                        } else {
                            $_SESSION['toastMessage'] = "Something went wrong";
                            $_SESSION['toastMessageType'] = 'success';
                            $this->session->markAsFlashdata('toastMessage');
                            $this->session->markAsFlashdata('toastMessageType');
                            return redirect()->to('admin/languages')->withCookies();
                        }
                    }
                }
            }
        }
    }
}

