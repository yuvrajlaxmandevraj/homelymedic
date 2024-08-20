<?php
/*
    ------------------------------------------------------------------------------------
    edemand helpers
    ------------------------------------------------------------------------------------
    1.  function curl($url, $method = 'GET', $header = ['Content-Type: application/x-www-form-urlencoded'], $data = [], $authorization = NULL)
    2.  function generate_token()
    3.  function verify_token()
    4.  function xss_clean($data)
    5.  function get_settings($type = 'system_settings', $is_json = false)
    6.  function output_escaping($array)
    7.  function escape_array($array)
    8.  function update_details($set, $where, $table, $escape = true)
    9.  function fetch_details($table, $where = [], $fields = [])
    10. function exists($where, $table)
    11. function active_plan($user_id)
    12. function update_characters($length, $user_id, $provider = "")
    13. function user_characters($user_id)
    14. function active_plan_type($user_id)
    15. function verify_voice($language  ,$voice  , $provider )
    16. function get_plans($plan_id = null)
    17. function get_subscription($user_id, $active = false)
    18. function get_subscription($user_id, $active = false)
    19. function add_subscription($user_id, $plan_id, $tenure, $transaction_id, $price, $starts_from = '', $start_now = false)
    20. function slugify($text, $divider = '-')
    21. function verify_payment_transaction($txn_id, $payment_method, $additional_data = [])
    22. function add_transaction($transaction_id, $amount, $payment_method, $user_id, $status = 'pending', $subscription_id = '', $message = '')
    23. function upcoming_plans($user_id)
    24. function subscription_status($subscription_id)
    25. function valid_image($image)
    26. function move_file($file, $path = 'public/uploads/images/', $name = '', $replace = false, $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'])
    27. function formatOffset($offset)
    28. function get_timezone()
    29. function get_timezone_array()
    30. function check_exists($file)
    31. function has_upcoming($user_id)
    32. function convert_active($user_id)
    33. function upcoming_plan($user_id)
    34. function numbers_initials($num)
    35. function mail_error($subject, $message, $trace = "")
    36. function mask_email($email)
    37. function get_system_update_info()
    38. function labels($label, $alt = '')
    39. function create_label($variable , $title = '')
    40. function get_currency()
    41. function console_log($data)
    42. function delete_directory($dir)
    43. function formate_number($number, $decimals = 0, $decimal_separator = '.', $thousand_separator = ',', $currency_symbol = '', $type = 'prefix')
    44. function email_sender($user_email, $subject, $message)
    45. create_unique_slug() ->not sure what i'll do
    46. insert_details
    47. remove_null_value
    48. response
    49. verify_app_request
    50. function fetch_cart($from_app = false, int $user_id = 0, string $search = '', int $limit = 10, int $offset = 0, string $sort = 'c.id', string $order = 'Desc', $where = [], $additional_data = [])
    */

use App\Libraries\Flutterwave;
use App\Libraries\Paypal;
use App\Libraries\Paystack;
use App\Libraries\Paytm;
use App\Libraries\Razorpay;
use App\Libraries\Stripe;
use App\Models\Orders_model;
use Razorpay\Api\Api;
// Load the necessary services
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

use function PHPUnit\Framework\returnSelf;

function update_balance($amount, $partner_id, $action)
{
    $db = \Config\Database::connect();
    $builder = $db->table('users');
    if ($action == "add") {
        $builder->set('balance', 'balance+' . $amount, false);
    } elseif ($action == "deduct") {
        $builder->set('balance', 'balance-' . $amount, false);
    }
    return $builder->where('id', $partner_id)->update();
}
function get_catgory_name($category_id = '')
{
    if (!empty($category_id)) {
        $where['id'] = $category_id;
        $where['status'] = '1';
        $categories = fetch_details('categories', $where, ['name']);
        if (isset($categories) && !empty($categories)) {
            $name = $categories[0]['name'];
            return $name;
        } else {
            return 'general';
        }
    } else {
        return false;
    }
}
function get_subcatgory_name($category_id)
{
    $where['parent_id'] = $category_id;
    $where['status'] = '1';
    $subcategory = fetch_details('categories', $where, ['name']);
    if (isset($subcategory) && !empty($subcategory)) {
        return $subcategory[0]['name'];
    } else {
        return '';
    }
}
function get_city($city_id = '')
{
    if (!empty($city_id)) {
        if ($city = fetch_details('cities', ['id' => $city_id], ['name'])) {
            $name = $city[0]['name'];
            return $name;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
function order_details($order_id)
{
    $model = new Orders_model();
    $where_in_key = 'o.status';
    $where_in_value = ['awaiting', 'confirmed', 'rescheduled'];
    $data = [];
    $order_details = $model->list(false, '', 10, 0, '', '', ['o.id' => $order_id], $where_in_key, $where_in_value);
    if (isset($order_details) && !empty($order_details)) {
        $details = json_decode($order_details);
        $data['order'] = isset($details->rows[0]) ? $details->rows[0] : '';
        $services = isset($details->rows[0]->services) ? $details->rows[0]->services : '';
        $id = (!empty($services)) ? array_column($services, 'service_id') : "";
        $data['cancellable'] = fetch_details('services', [], ['duration', 'is_cancelable', 'cancelable_till'], null, '0', '', '', 'id', $id);
        unset($data['order']->services);
        return $data;
    } else {
        return new stdClass();
    }
}
function check_cancelable($date_of_service, $starting_time, $cancellable_befor_min)
{
    $today = strtotime(date('y-m-d H:i'));
    $format_date = date('y-m-d H:i', strtotime("$date_of_service $starting_time"));
    $service_date = strtotime($format_date);
    if ($service_date >= $today) {
        $i = ($service_date - $today) / 60;
        if (intval($cancellable_befor_min) > $i) {
            return false;
        } else {
            return true;
        }
    }
}
function test($order_id, $status, $table = '', $user_id = null)
{
    $db = \Config\Database::connect();
    $ionAuth = new \IonAuth\Libraries\IonAuth();
    $builder = $db->table('orders o');
    $error = 0;
    $cancelable_till = '';
    $is_already_cancelled = 0;
    $is_cancelable = 0;
    $cancelable_count = 0;
    $group = array('partner');
    $check_status = ['awaiting', 'confirmed', 'rescheduled', 'cancelled', 'completed'];
    if (in_array(($status), $check_status)) {
        $builder->select('o.status')->where('id', $order_id);
        $active_status = $builder->get()->getResultArray()[0]['status'];
        if ($active_status == 'cancelled' || $active_status == 'completed') {
            $response['error'] = true;
            $response['message'] = "You can't update status once item cancelled OR completed";
            $response['data'] = array();
            return $response;
        }
        $builder->select('o.status,os.service_id,s.is_cancelable,s.cancelable_till,s.duration')
            ->join('order_services os', 'os.order_id=o.id')
            ->join('services s', 's.id=os.service_id')
            ->where('o.id', $order_id);
        $service_data = $builder->get()->getResultArray();
        $priority_status = [
            'awaiting' => 0,
            'confirmed' => 1,
            'cancelled' => 2,
            'rescheduled' => 3,
            'completed' => 4,
        ];
        $is_posted_status_set = $canceling_completed_order = false;
        $is_posted_status_set_count = 0;
        for ($i = 0; $i < count($service_data); $i++) {
            /* check if there are any products returnable or cancellable products available in the list or not */
            if ($service_data[$i]['is_cancelable'] == 1) {
                $cancelable_count += 1;
            }
            if ($is_posted_status_set_count == count($service_data)) {
                $is_posted_status_set = true;
            }
            if (($status == "cancelled") && (in_array("completed", $check_status))) {
                $canceling_completed_order = true;
            }
        }
        $is_cancelable = ($cancelable_count >= 1) ? 1 : 0;
        for ($i = 0; $i < count($service_data); $i++) {
            if ($service_data[$i]['status'] == 'cancelled') {
                $error = 1;
                $is_already_cancelled = 1;
                break;
            }
            if ($status == 'rescheduled' && $service_data[$i]['is_rescheduled'] == 0) {
                $error = 1;
                break;
            }
            if ($status == 'returned' && $service_data[$i]['is_returnable'] == 1 && $priority_status[$service_data[$i]['active_status']] < 3) {
                $error = 1;
                $returnable_till = 'delivery';
                break;
            }
            if ($status == 'cancelled' && $service_data[$i]['is_cancelable'] == 1) {
                $max = $priority_status[$service_data[$i]['cancelable_till']];
                $min = $priority_status[$service_data[$i]['active_status']];
                if ($min > $max) {
                    $error = 1;
                    $cancelable_till = $service_data[$i]['cancelable_till'];
                    break;
                }
            }
            if ($status == 'cancelled' && $service_data[$i]['is_cancelable'] == 0) {
                $error = 1;
                break;
            }
        }
        if ($is_posted_status_set == true) {
            /* status posted is already present in any of the order item */
            $response['error'] = true;
            $response['message'] = "Order is already marked as $status. You cannot set it again!";
            $response['data'] = array();
            return $response;
        }
        if ($canceling_completed_order == true) {
            /* when user is trying cancel delivered order / item */
            $response['error'] = true;
            $response['message'] = "You cannot cancel completed service";
            $response['data'] = array();
            return $response;
        }
        for ($i = 0; $i < count($service_data); $i++) {
            if ($status == 'cancelled' && $service_data[$i]['is_cancelable'] == 1) {
                $max = $priority_status[$service_data[$i]['cancelable_till']];
                $min = $priority_status[$service_data[$i]['active_status']];
                if ($min > $max) {
                    $error = 1;
                    $cancelable_till = $service_data[$i]['cancelable_till'];
                    break;
                }
            }
            if ($status == 'cancelled' && $service_data[$i]['is_cancelable'] == 0) {
                $error = 1;
                break;
            }
        }
        if ($status == 'cancelled' && $error == 1 && !empty($cancelable_till) && !$ionAuth->loggedIn() && !$ionAuth->in_groupF($group, $user_id)) {
            $response['error'] = true;
            $response['message'] = (count($service_data) > 1) ? " One of the order item can be cancelled till " . $cancelable_till . " only " : "The order item can be cancelled till " . $cancelable_till . " only";
            $response['data'] = array();
            return $response;
        }
        if ($status == 'cancelled' && $error == 1 && !$ionAuth->loggedIn() && !$ionAuth->in_group($group, $user_id)) {
            $response['error'] = true;
            $response['message'] = (count($service_data) > 1) ? "One of the order item can't be cancelled !" : "The order item can't be cancelled !";
            $response['data'] = array();
            return $response;
        }
        if ($status == "cancellable" && $is_cancelable == 1) {
            echo "service is cancellable";
        }
        $response['error'] = false;
        $response['message'] = " ";
        $response['data'] = array();
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "Invalid Status Passed";
        $response['data'] = array();
        return $response;
    }
}
function get_service_details($order_id)
{
    $db = \Config\Database::connect();
    // $where = 'status != "cancelled"';
    $data = $db
        ->table(' order_services os')
        ->select('os.*', 'o.partner_id', 'o.')
        ->where('order_id', $order_id)
        ->where('status != ', 'cancelled')
        ->get()->getResultArray();
    $results = [];
    // $service_data = [];
    for ($i = 0; $i < sizeof($data); $i++) {
        $id = $data[$i]['service_id'];
        // print_r($id);
        $service_data = $db
            ->table('services')
            ->select('*')
            ->where('id', $id)
            ->get()->getResultArray();
        if (isset($service_data[0]) && !empty($service_data)) {
            array_push($results, $service_data[0]);
        }
    }
    // print_r($service_data);
    if (!empty($results)) {
        return $results;
    } else {
        $response['error'] = true;
        $response['message'] = "No such service found!";
        return $response;
    }
}
function validate_status($order_id, $status, $date = '', $selected_time = "", $otp = null, $work_proof = null)
{
    $check_status = ['awaiting', 'confirmed', 'rescheduled', 'cancelled', 'completed', 'started'];
    if (in_array(($status), $check_status)) {
        $db = \Config\Database::connect();
        $builder = $db->table('orders');
        $builder->select('status,payment_method,user_id,otp')->where('id', $order_id);
        $active_status1 = $builder->get()->getResultArray();
        $active_status = (isset($active_status1[0]['status'])) ? $active_status1[0]['status'] : "";
        if ($active_status == $status) {
            $response['error'] = true;
            $response['message'] = "You can't update the same status again";
            $response['data'] = array();
            return $response;
        }
        if ($active_status == 'cancelled' || $active_status == 'completed') {
            $response['error'] = true;
            $response['message'] = "You can't update status once item cancelled OR completed";
            $response['data'] = array();
            return $response;
        }


        if (in_array($active_status, ["started"]) && (($status == "rescheduled")|| ($status == "confirmed"))) {
            $response['error'] = true;
            $response['message'] = "Once you begin the booking process, you cannot change the booking time.";
            $response['data'] = array();
            return $response;
        }



        if (in_array($active_status, ["started"]) && (($status == "rescheduled") || ($status == "confirmed") || ($status == "awaiting") || ($status == "pending"))) {
            $response['error'] = true;
            $response['message'] = "You cannot alter the status that has already been marked as " . $status;
            $response['data'] = array();
            return $response;
        }





        // if (in_array($active_status, ["started"]) && (($status == "awaiting") || ($status == "pending"))) {
        //     $response['error'] = true;
        //     $response['message'] = "You can't change status  ! ";
        //     $response['data'] = array();
        //     return $response;
        // }
        if ($active_status == '') {
            $response['error'] = true;
            $response['message'] = "Invalid booking or status data";
            $response['data'] = array();
            return $response;
        }
        if (in_array($active_status, ["confirmed", "rescheduled"]) && $status == "awaiting") {
            $response['error'] = true;
            $response['message'] = "You cannot alter the status that has already been marked as " . $status;
            $response['data'] = array();
            return $response;
        }
        if (in_array($status, ["awaiting", "confirmed"])) {
            update_details(['status' => $status], ['id' => $order_id], 'orders');
            update_details(["status" => $status], ["order_id" => $order_id, "status!=" => "cancelled"], "order_services");
        }
        //if order status is completed
        if ($status == 'completed') {
            $settings = get_settings('general_settings', true);
            if (isset($settings['otp_system']) && $settings['otp_system'] == 1) {
                $settings['otp_system'] = 1;
            } else {
                $settings['otp_system'] = 0;
            }
            //   print_r($settings['otp_system']);
            //if otp system is enabled
            if ($settings['otp_system'] == "1") {
                //if otp is mathed then update status otherwise not
                if ($active_status1[0]['otp'] == $otp) {
                    $data = get_service_details($order_id);
                    $order_details = fetch_details('orders', ['id' => $order_id]);
                    update_details(['status' => $status], ['id' => $order_id], 'orders');
                    if ($order_details[0]['payment_method'] != "cod") {
                        $user_details = fetch_details('users', ['id' => $order_details[0]['partner_id']]);
                        $admin_commission_percentage = get_admin_commision($order_details[0]['partner_id']);
                        $admin_commission_amount = intval($admin_commission_percentage) / 100;
                        $total = $order_details[0]['final_total'];
                        $commision = intval($total) * $admin_commission_amount;
                        $unsettled_amount = $total - $commision;
                        update_details(["balance" => $user_details[0]['balance'] + $unsettled_amount], ["id" => $order_details[0]['partner_id']], "users");
                    }
                    if (($order_details[0]['payment_method']) == "cod") {
                        $admin_commission_percentage = get_admin_commision($order_details[0]['partner_id']);
                        $admin_commission_amount = intval($admin_commission_percentage) / 100;
                        $total = $order_details[0]['final_total'];
                        $commision = intval($total) * $admin_commission_amount;
                        $current_commision = fetch_details('users', ['id' => $order_details[0]['partner_id']], ['payable_commision', 'email'])[0];
                        $current_commision['payable_commision'] = ($current_commision['payable_commision'] == "") ? 0 : $current_commision['payable_commision'];
                        update_details(['payable_commision' => $current_commision['payable_commision'] + $commision], ['id' => $order_details[0]['partner_id']], 'users');
                        $cash_collecetion_data = [
                            'user_id' => $order_details[0]['user_id'],
                            'order_id' => $order_id,
                            'message' => "provider received cash",
                            'status' => 'provider_cash_recevied',
                            'commison' => intval($commision),
                            'partner_id' => $order_details[0]['partner_id'],
                            'date' => date("Y-m-d"),
                        ];
                        insert_details($cash_collecetion_data, 'cash_collection');
                    };
                    if (!empty($work_proof)) {
                        $imagefile = $work_proof['work_complete_files'];
                        $work_completed_images = [];
                        foreach ($imagefile as $key => $img) {
                            if ($img->isValid() && !$img->hasMoved()) {
                                $newName = $img->getName();
                                $fileNameParts = explode('.', $newName);
                                $ext = end($fileNameParts);
                                $newName = 'data_' . uniqid() . '.' . $ext;
                                $work_completed_images[$key] = "/public/backend/assets/provider_work_evidence/" . $newName;
                                $img->move('./public/backend/assets/provider_work_evidence/', $newName);
                            }
                        }
                        $dataToUpdate = [
                            'work_completed_proof' => !empty($work_completed_images) ? json_encode($work_completed_images) : "",
                        ];
                        update_details($dataToUpdate, ['id' => $order_id], 'orders', false);
                    }
                    update_details(["status" => $status], ["order_id" => $order_id], "order_services");
                } else {
                    $response['error'] = true;
                    $response['message'] = "OTP does not match!";
                    $response['data'] = [];
                    return $response;
                }
            }
            //if otp system is disabled
            else {
                $data = get_service_details($order_id);
                $order_details = fetch_details('orders', ['id' => $order_id]);
                update_details(['status' => $status], ['id' => $order_id], 'orders');
                if ($order_details[0]['payment_method'] != "cod") {
                    $user_details = fetch_details('users', ['id' => $order_details[0]['partner_id']]);
                    $admin_commission_percentage = get_admin_commision($order_details[0]['partner_id']);
                    $admin_commission_amount = intval($admin_commission_percentage) / 100;
                    $total = $order_details[0]['final_total'];
                    $commision = intval($total) * $admin_commission_amount;
                    $unsettled_amount = $total - $commision;
                    update_details(["status" => $status], ["order_id" => $order_id], "order_services");
                    update_details(["balance" => $user_details[0]['balance'] + $unsettled_amount], ["id" => $order_details[0]['partner_id']], "users");
                }
                if (($order_details[0]['payment_method']) == "cod") {
                    $admin_commission_percentage = get_admin_commision($order_details[0]['partner_id']);
                    $admin_commission_amount = intval($admin_commission_percentage) / 100;
                    $total = $order_details[0]['final_total'];
                    $commision = intval($total) * $admin_commission_amount;
                    $current_commision = fetch_details('users', ['id' => $order_details[0]['partner_id']], ['payable_commision', 'email'])[0];
                    $current_commision['payable_commision'] = ($current_commision['payable_commision'] == "") ? 0 : $current_commision['payable_commision'];
                    update_details(['payable_commision' => $current_commision['payable_commision'] + $commision], ['id' => $order_details[0]['partner_id']], 'users');
                    $cash_collecetion_data = [
                        'user_id' => $order_details[0]['user_id'],
                        'order_id' => $order_id,
                        'message' => "provider received cash",
                        'status' => 'provider_cash_recevied',
                        'commison' => intval($commision),
                        'partner_id' => $order_details[0]['partner_id'],
                        'date' => date("Y-m-d"),
                    ];
                    insert_details($cash_collecetion_data, 'cash_collection');
                };
                if (!empty($work_proof)) {
                    $imagefile = $work_proof['work_complete_files'];
                    $work_completed_images = [];
                    foreach ($imagefile as $key => $img) {
                        if ($img->isValid() && !$img->hasMoved()) {
                            $newName = $img->getName();
                            $fileNameParts = explode('.', $newName);
                            $ext = end($fileNameParts);
                            $newName = 'data_' . uniqid() . '.' . $ext;
                            $work_completed_images[$key] = "/public/backend/assets/provider_work_evidence/" . $newName;
                            $img->move('./public/backend/assets/provider_work_evidence/', $newName);
                        }
                    }
                    $dataToUpdate = [
                        'work_completed_proof' => !empty($work_completed_images) ? json_encode($work_completed_images) : "",
                    ];
                    update_details($dataToUpdate, ['id' => $order_id], 'orders', false);
                    update_details(["status" => $status], ["order_id" => $order_id], "order_services");
                }
            }
        }
        if ($status == 'started') {
            if (!empty($work_proof)) {
                $imagefile = $work_proof['work_started_files'];
                $work_started_images = [];
                foreach ($imagefile as $key => $img) {
                    if ($img->isValid() && !$img->hasMoved()) {
                        $newName = $img->getName();
                        $fileNameParts = explode('.', $newName);
                        $ext = end($fileNameParts);
                        $newName = 'data_' . uniqid() . '.' . $ext;
                        $work_started_images[$key] = "/public/backend/assets/provider_work_evidence/" . $newName;
                        $img->move('./public/backend/assets/provider_work_evidence/', $newName);
                    }
                }
            }
            $dataToUpdate = [
                'status' => 'started',
                'work_started_proof' => !empty($work_started_images) ? json_encode($work_started_images) : "",
            ];
            update_details($dataToUpdate, ['id' => $order_id], 'orders', false);
            update_details(["status" => $status], ["order_id" => $order_id], "order_services");
        }
        if ($status == 'rescheduled') {
            $data = get_service_details($order_id);
            $orders = fetch_details('orders', ['id' => $order_id]);
            $sub_orders = fetch_details('orders', ['parent_id' => $order_id]);
            $time_calc = 0;
            for ($i = 0; $i < count($data); $i++) {
                $time_calc += (int) $data[$i]['duration'];
            }
            $partner_id = $orders[0]['partner_id'];
            $date_of_service = $date;
            $starting_time = $selected_time;
            $availability =  checkPartnerAvailability($partner_id, $date_of_service . ' ' . $starting_time, $orders[0]['duration'], $date_of_service, $starting_time);
            $time_slots = get_available_slots($partner_id, $date_of_service, isset($service_total_duration) ? $service_total_duration : 0, $starting_time); //working
            $current_date = date('Y-m-d');
            if (isset($availability) && $availability['error'] == "0") {
                $service_total_duration = 0;
                $service_duration = 0;
                $service_total_duration = $orders[0]['duration'];
                if (!empty($sub_orders)) {
                    $service_total_duration = $service_total_duration + $sub_orders[0]['duration'];
                }
                $time_slots = get_slot_for_place_order($partner_id, $date_of_service, $service_total_duration, $starting_time);
                $timestamp = date('Y-m-d h:i:s '); // Example timestamp format: 2023-08-08 03:30:00 PM
                if ($time_slots['suborder'] && !empty($time_slots['suborder_data'])) {
                    $total = (sizeof($time_slots['order_data']) * 30) + (sizeof($time_slots['suborder_data']) * 30);
                } else {
                    $total = (sizeof($time_slots['order_data']) * 30);
                }
                if ($service_total_duration > $total) {
                    $response['error'] = false;
                    $response['message'] = "There are currently no available slots.";
                    $response['data'] = array();
                    return $response;
                }
                if ($time_slots['slot_avaialble']) {
                    if ($time_slots['suborder']) {
                        $end_minutes = strtotime($starting_time) + ((sizeof($time_slots['order_data']) * 30) * 60);
                        $ending_time = date('H:i:s', $end_minutes);
                        $day = date('l', strtotime($date_of_service));
                        $timings = getTimingOfDay($partner_id, $day);
                        $closing_time = $timings['closing_time']; // Replace with the actual closing time
                        if ($ending_time > $closing_time) {
                            $ending_time = $closing_time;
                        }
                        $start_timestamp = strtotime($starting_time);
                        $ending_timestamp = strtotime($ending_time);
                        $duration_seconds = $ending_timestamp - $start_timestamp;
                        $duration_minutes = $duration_seconds / 60;
                    }
                    $end_minutes = strtotime($starting_time) + ($service_total_duration * 60);
                    $ending_time = date('H:i:s', $end_minutes);
                    $day = date('l', strtotime($date_of_service));
                    $timings = getTimingOfDay($partner_id, $day);
                    $closing_time = $timings['closing_time']; // Replace with the actual closing time
                    if ($ending_time > $closing_time) {
                        $ending_time = $closing_time;
                    }
                    $start_timestamp = strtotime($starting_time);
                    $ending_timestamp = strtotime($ending_time);
                    $duration_seconds = $ending_timestamp - $start_timestamp;
                    $duration_minutes = $duration_seconds / 60;
                    update_details(
                        [
                            'status' => 'rescheduled',
                            'date_of_service' => $date,
                            'starting_time' => $selected_time,
                            'ending_time' => $ending_time,
                            'duration' => $duration_minutes,
                        ],
                        ['id' => $order_id],
                        'orders'
                    );
                }
                if ($time_slots['suborder']) {
                    $next_day_date = date('Y-m-d', strtotime($date_of_service . ' +1 day'));
                    // $t=100;
                    $t = ($service_total_duration);
                    $next_day_slots = get_next_days_slots($closing_time, $date_of_service, $partner_id, $t, $current_date);
                    $next_day_available_slots = $next_day_slots['available_slots'];
                    if (empty($next_day_available_slots)) {
                        $response['error'] = false;
                        $response['message'] = "A time slot is currently unavailable at the present moment.";
                        $response['data'] = array();
                        return $response;
                    }
                    $next_Day_minutes = strtotime($next_day_available_slots[0]) + (($service_total_duration - $duration_minutes) * 60);
                    $next_day_ending_time = date('H:i:s', $next_Day_minutes);
                    $is_update = true;
                    if (!empty($sub_orders)) {
                        update_details(
                            [
                                'status' => 'rescheduled',
                                'date_of_service' => $next_day_date,
                                'starting_time' => isset($next_day_available_slots[0]) ? $next_day_available_slots[0] : 00,
                                'ending_time' =>  $next_day_ending_time,
                                'duration' =>  $service_total_duration - $duration_minutes,
                            ],
                            ['parent_id' => $order_id],
                            'orders'
                        );
                    } else {
                        $sub_order = [
                            'partner_id' => $partner_id,
                            'user_id' => $orders[0]['user_id'],
                            'city' => $orders[0]['city_id'],
                            'total' => $orders[0]['total'],
                            'payment_method' => $orders[0]['payment_method'],
                            'address_id' => $orders[0]['address_id'],
                            'visiting_charges' => $orders[0]['visiting_charges'],
                            'address' => $orders[0]['address'],
                            'date_of_service' =>   $next_day_date,
                            'starting_time' => isset($next_day_available_slots[0]) ? $next_day_available_slots[0] : 00,
                            'ending_time' => $next_day_ending_time,
                            'duration' => $service_total_duration - $duration_minutes,
                            'status' => $status,
                            'remarks' => "sub_order",
                            'otp' => random_int(100000, 999999),
                            'parent_id' =>  $orders[0]['id'],
                            'order_latitude' =>  $orders[0]['order_latitude'],
                            'order_longitude' =>  $orders[0]['order_longitude'],
                            'created_at' => $timestamp,
                        ];
                        $sub_order['final_total'] = $orders[0]['final_total'];
                        $sub_order = insert_details($sub_order, 'orders');
                    }
                    set_time_limit(60);
                }


                $response['error'] = false;
                $response['message'] = "The booking has been successfully rescheduled.";
                $response['data'] = array();

                $db = \Config\Database::connect();
                $order_details = fetch_details('orders', ['id' => $order_id]);
                $order_details = json_encode($order_details);
                $details = (json_decode($order_details, true));
                $customer_id = $details[0]['user_id'];
                $data['order'] = isset($details[0]) ? $details[0] : '';
                $to_send_id = $customer_id;
                $builder = $db->table('users')->select('fcm_id,email,username,platform');
                $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                foreach ($users_fcm as $ids) {
                    if ($ids['fcm_id'] != "") {
                        $fcm_ids['fcm_id'] = $ids['fcm_id'];
                        $fcm_ids['platform'] = $ids['platform'];
                    }
                    $email = $ids['email'];
                    $username = $ids['username'];
                }
                if (!empty($fcm_ids)) {
                    $registrationIDs = $fcm_ids;
                    // $registrationIDs = array_chunk($users_fcm, 1000);
                    $fcmMsg = array(
                        'content_available' => true,
                        'title' => "Booking Status change",
                        'body' => "Your Booking status has been " . $status,
                        'type' => 'order',
                        'type_id' => $to_send_id,
                        'order_id' => $order_id,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    );
                    // $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
                    $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                    send_notification($fcmMsg, $registrationIDs_chunks);
                }
                return $response;
            } else {
                set_time_limit(60);
                $response['error'] = true;
                $response['message'] = $availability['message'];
                $response['data'] = array();
                return $response;
                return response($availability['message'], true);
            }
        }
        if ($status == 'cancelled') {
            $order_details = fetch_details('orders', ['id' => $order_id]);
            $order_details = json_encode($order_details);
            $details = json_decode($order_details);
            $data['order'] = isset($details[0]) ? $details[0] : '';
            $order_services = fetch_details('order_services', ['order_id' => $order_id]);
            foreach ($order_services as $row) {
                $services[] = $row['service_id'];
            }
            $data['cancellable'] = [];
            foreach ($services as $row) {
                $data_of_service = fetch_details('services', ['id' => $row], ['id', 'duration', 'is_cancelable', 'cancelable_till'], null, '0', '', '');
                foreach ($data_of_service as $data1) {
                    $cancellable[] = $data1;
                }
            }
            if (!empty($order_details)) {
                $order = $data['order'];
                $customer_id = $order->user_id;
                $date_of_service = $order->date_of_service;
                $starting_time = $order->starting_time;
                $cancellable = ($cancellable);
                $response = [];
                $response['status'] = $status;
                // print_r($cancellable)   ;
                // die;
                $can_cancle = false;
                foreach ($cancellable as $key) {
                    $can_cancle = ($key['is_cancelable'] == 1) ? true : false;
                    if ($key['is_cancelable'] == "1"  && $key['cancelable_till']) {
                        $is_cancelable = check_cancelable(date('y-m-d', strtotime($date_of_service)), $starting_time, $key['cancelable_till']);
                        if ($is_cancelable == true) {
                            if ($can_cancle == false) {
                                $response['error'] = true;
                                $response['message'] = "Booking is not cancelable!";
                                $response['data'] = [];
                                return $response;
                            } else {
                                update_details(['status' => $status], ['id' => $order_id], 'orders');
                                // update_details(["status" => $status], ["order_id" => $order_id], "order_services");
                                $refund = process_refund($order_id, $status, $customer_id);
                                $response['is_cancelable'] = true;
                                $response['error'] = false;
                                $response['message'] = "Booking updated successfully";
                                $response['data'] = $refund;
                                $db = \Config\Database::connect();
                                $order_details = fetch_details('orders', ['id' => $order_id]);
                                $order_details = json_encode($order_details);
                                $details = (json_decode($order_details, true));
                                $customer_id = $details[0]['user_id'];
                                $data['order'] = isset($details[0]) ? $details[0] : '';
                                $to_send_id = $customer_id;
                                $builder = $db->table('users')->select('fcm_id,email,username,platform');
                                $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
                                foreach ($users_fcm as $ids) {
                                    if ($ids['fcm_id'] != "") {
                                        $fcm_ids['fcm_id'] = $ids['fcm_id'];
                                        $fcm_ids['platform'] = $ids['platform'];
                                    }
                                    $email = $ids['email'];
                                    $username = $ids['username'];
                                }
                                if (!empty($fcm_ids)) {
                                    $registrationIDs = $fcm_ids;
                                    // $registrationIDs = array_chunk($users_fcm, 1000);
                                    $fcmMsg = array(
                                        'content_available' => true,
                                        'title' => "Booking Status change",
                                        'body' => "Your Booking status has been " . $status,
                                        'type' => 'order',
                                        'type_id' => $to_send_id,
                                        'order_id' => $order_id,
                                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                    );
                                    // $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
                                    $registrationIDs_chunks = array_chunk($users_fcm, 1000);
                                    send_notification($fcmMsg, $registrationIDs_chunks);
                                }
                                return $response;
                            }
                        } else {
                            $response['error'] = true;
                            $response['message'] = "Booking is not cancelable !";
                            $response['data'] = [];
                            return $response;
                        }
                    } else {
                        $response['error'] = true;
                        $response['message'] = "Booking is not cancelable!";
                        $response['data'] = [];
                        return $response;
                    }
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Booking data not found!";
                $response['data'] = [];
                return $response;
            }
        }
        $response['error'] = false;
        $response['message'] = "Booking updated successfully ";
        $response['data'] = [];
        $db = \Config\Database::connect();
        $order_details = fetch_details('orders', ['id' => $order_id]);
        $order_details = json_encode($order_details);
        $details = (json_decode($order_details, true));
        $customer_id = $details[0]['user_id'];
        $data['order'] = isset($details[0]) ? $details[0] : '';
        $to_send_id = $customer_id;
        $builder = $db->table('users')->select('fcm_id,email,username,platform');
        $users_fcm = $builder->where('id', $to_send_id)->get()->getResultArray();
        foreach ($users_fcm as $ids) {
            if ($ids['fcm_id'] != "") {
                $fcm_ids['fcm_id'] = $ids['fcm_id'];
                $fcm_ids['platform'] = $ids['platform'];
            }
            $email = $ids['email'];
            $username = $ids['username'];
        }
        if (!empty($fcm_ids)) {
            $registrationIDs = $fcm_ids;
            // $registrationIDs = array_chunk($users_fcm, 1000);
            $fcmMsg = array(
                'content_available' => true,
                'title' => "Booking Status change",
                'body' => "Your Booking status has been " . $status,
                'type' => 'order',
                'type_id' => $to_send_id,
                'order_id' => $order_id,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            );
            // $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
            $registrationIDs_chunks = array_chunk($users_fcm, 1000);
            send_notification($fcmMsg, $registrationIDs_chunks);
        }
        $settings = get_settings('general_settings', true);
        $icon = $settings['logo'];
        $data = array(
            'name' => (!empty($username)) ? $username : "",
            'title' => "Booking Status Update",
            'logo' => base_url("public/uploads/site/" . $icon),
            'first_paragraph' => 'We would like to inform you that the status of your Booking has been updated.',
            'second_paragraph' => 'Booking status is ' . $status,
            'third_paragraph' => 'Thank you for choosing our services. We look forward to providing you with excellent service in the future.',
            'company_name' => $settings['company_title'],
        );
        if (!empty($email)) {
            email_sender($email, 'Booking Status Update', view('backend/admin/pages/provider_email', $data));
        }
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "Invalid Status Passed";
        $response['data'] = array();
        return $response;
    }
}
function unsettled_commision($partner_id = '')
{
    $amount = fetch_details('orders', ['partner_id' => $partner_id, 'is_commission_settled' => '0', 'status' => 'completed'], ['sum(final_total) as total']);
    if (isset($amount) && !empty($amount)) {
        //  commission will be in % here
        $admin_commission_percentage = get_admin_commision($partner_id);
        $admin_commission_amount = intval($admin_commission_percentage) / 100;
        $total = $amount[0]['total'];
        $commision = intval($total) * $admin_commission_amount;
        $unsettled_amount = $total - $commision;
    }
    return $unsettled_amount;
}
function get_admin_commision($partner_id = '')
{
    $commision = fetch_details('partner_details', ['partner_id' => $partner_id], ['admin_commission'])[0]['admin_commission'];
    return $commision;
}
function process_refund($order_id, $status, $customer_id)
{
    $possible_status = array("cancelled");
    if (!in_array($status, $possible_status)) {
        $response['error'] = true;
        $response['message'] = 'Refund cannot be processed. Invalid status';
        $response['data'] = array();
        return $response;
    }
    /* if complete order is getting cancelled */
    $transaction = fetch_details('transactions', ['order_id' => $order_id, 'transaction_type' => 'transaction'], ['amount', 'txn_id', 'type', 'currency_code', 'status']);
    if (isset($transaction) && !empty($transaction)) {
        $type = $transaction[0]['type'];
        $currency = $transaction[0]['currency_code'];
        $txn_id = $transaction[0]['txn_id'];
        $amount = $transaction[0]['amount'];
        if ($type == 'flutterwave' && $transaction[0]['status'] == "successfull") {
            $flutterwave = new Flutterwave();
            $payment = $flutterwave->refund_payment($txn_id, $amount);
            if (isset($payment['status']) && $payment['status'] == 'success') {
                $data = [
                    'transaction_type' => 'refund',
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'flutterwave',
                    'txn_id' => $payment['id'],
                ];
                $success = insert_details($data, 'transactions');
                $response['error'] = false;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['message'] = "Payment Refund Successfully";
                if ($success) {
                    update_details(['status' => $status], ['id' => $order_id], 'orders');
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                }
            } else {
                $message = json_decode($payment, true);
                $response['error'] = true;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['message'] = $message['message'];
            }
        }
        if ($type == "stripe" && $transaction[0]['status'] == 'success') {
            $stripe = new Stripe();
            $payment = $stripe->refund($txn_id, $amount);
            if (isset($payment['status']) && $payment['status'] == "succeeded") {
                $amount = intval($payment['amount']);
                $data = [
                    'transaction_type' => $payment['object'],
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'stripe',
                    'txn_id' => $payment['payment_intent'],
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => $payment['status'],
                ];
                $success = insert_details($data, 'transactions');
                $response = [
                    'error' => false,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => "Payment Refund Successfully",
                ];
                if ($success) {
                    update_details(['status' => $status], ['id' => $order_id], 'orders');
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                }
                return $response;
            } else {
                $res = json_decode($payment['body']);
                $msg = $res->error->message;
                $response = [
                    'error' => true,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => $msg,
                ];
                return $response;
            }
        }
        if ($type == "razorpay" && $transaction[0]['status'] == "success") {
            $razorpay = new Razorpay();
            $payment = $razorpay->refund_payment($txn_id, $amount);
            if (isset($payment['status']) && $payment['status'] == "processed") {
                $amount = intval($payment['amount']) / 100;
                $data = [
                    'transaction_type' => $payment['entity'],
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'razorpay',
                    'txn_id' => $payment['payment_id'],
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => $payment['status'],
                ];
                $success = insert_details($data, 'transactions');
                if ($success) {
                    update_details(['status' => $status], ['id' => $order_id], 'orders');
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                } else {
                    $response = [
                        'error' => false,
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'message' => "Booking can not be cancelled",
                    ];
                    return $response;
                }
            } else {
                $res = json_decode($payment['body'], true);
                $msg = $res['error']['description'];
                $response = [
                    'error' => true,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => $msg,
                ];
                return $response;
            }
        }
        if ($type == "paystack" && $transaction[0]['status'] == "success") {
            $paystack = new Paystack();
            $payment = $paystack->refund($txn_id, $amount);
            $message = json_decode($payment, true);
            // print_R($payment);
            //  print_R("---------------------------------------------------------");
            // print_R($message);
            // die;
            if (isset($message['status']) && $message['status'] == 1) {
                update_details(['status' => $status], ['id' => $order_id], 'orders');
                $amount = intval($message['data']['amount']) / 100;
                $data = [
                    'transaction_type' => 'refund',
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'paystack',
                    'txn_id' => $message['data']['transaction']['id'],
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => $message['data']['status'],
                ];
                $success = insert_details($data, 'transactions');
                if ($success) {
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                } else {
                    $response = [
                        'error' => false,
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'message' => "Booking can not be cancelled",
                    ];
                    return $response;
                }
            } else {
                $res = json_decode($payment, true);
                $response = [
                    'error' => true,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => $res['message'],
                ];
                return $response;
            }
        }
        if ($type == "paypal" && $transaction[0]['status'] == 'success') {
            $paypal = new Paypal();
            $payment = $paypal->refund($txn_id, $amount, $transaction[0]['currency_code']);
            $message = json_decode($payment, true);
            if (isset($message['status']) && $message['status'] == "COMPLETED") {
                $data = [
                    'transaction_type' => 'refund',
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'paypal',
                    'txn_id' => $txn_id,
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => 'success',
                ];
                $success = insert_details($data, 'transactions');
                $response = [
                    'error' => false,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => "Payment Refund Successfully",
                ];
                if ($success) {
                    update_details(['status' => $status], ['id' => $order_id], 'orders');
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                }
                return $response;
            } else {
                $res = json_decode($payment['body']);
                $msg = $res->error->message;
                $response = [
                    'error' => true,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => $msg,
                ];
                return $response;
            }
        }
    } else {
        $response = [
            'error' => true,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'message' => 'No transactio found of this order!',
        ];
        return $response;
    }
}
function process_service_refund($order_id, $ordered_service_id, $status, $customer_id, $amount)
{
    $transaction = fetch_details('transactions', ['order_id' => $order_id, 'transaction_type' => 'transaction'], ['amount', 'txn_id', 'type', 'currency_code', 'status']);
    if (isset($transaction) && !empty($transaction)) {
        $service_id = $ordered_service_id;
        $type = $transaction[0]['type'];
        $currency = $transaction[0]['currency_code'];
        $txn_id = $transaction[0]['txn_id'];
        $amount = $amount;
        if ($type == 'flutterwave' && $transaction[0]['status'] == "successfull") {
            $flutterwave = new Flutterwave();
            $payment = $flutterwave->refund_payment($txn_id, $amount);
            if (isset($payment['status']) && $payment['status'] == 'success') {
                $data = [
                    'transaction_type' => 'refund',
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'flutterwave',
                    'txn_id' => $payment['id'],
                ];
                $success = insert_details($data, 'transactions');
                $response['error'] = false;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['message'] = "Payment Refund Successfully";
                if ($success) {
                    update_details(['status' => $status], ['id' => $order_id], 'orders');
                    $response = [
                        'error' => false,
                        'message' => "Order cancelled Successfully!",
                    ];
                    return $response;
                }
            } else {
                $message = json_decode($payment, true);
                $response['error'] = true;
                $response['csrfName'] = csrf_token();
                $response['csrfHash'] = csrf_hash();
                $response['message'] = $message['message'];
            }
        }
        if ($type == "stripe" && $transaction[0]['status'] == 'succeeded') {
            $stripe = new Stripe();
            $payment = $stripe->refund($txn_id, $amount);
            if (isset($payment['status']) && $payment['status'] == "succeeded") {
                $amount = intval($payment['amount']) / 100;
                $data = [
                    'transaction_type' => $payment['object'],
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'stripe',
                    'txn_id' => $payment['payment_intent'],
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => $payment['status'],
                ];
                $success = insert_details($data, 'transactions');
                $response = [
                    'error' => false,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => "Payment Refund Successfully",
                ];
                if ($success) {
                    update_details(['status' => $status], ['id' => $order_id], 'orders');
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                }
                return $response;
            } else {
                $res = json_decode($payment['body']);
                $msg = $res->error->message;
                $response = [
                    'error' => true,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => $msg,
                ];
                return $response;
            }
        }
        if ($type == "razorpay" && $transaction[0]['status'] == "captured") {
            $razorpay = new Razorpay();
            $payment = $razorpay->refund_payment($txn_id, $amount);
            if (isset($payment['status']) && $payment['status'] == "processed") {
                $amount = intval($payment['amount']) / 100;
                $data = [
                    'transaction_type' => $payment['entity'],
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'razorpay',
                    'txn_id' => $payment['payment_id'],
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => $payment['status'],
                ];
                $success = insert_details($data, 'transactions');
                if ($success) {
                    update_details(['status' => $status], ['id' => $order_id], 'orders');
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelle    d Successfully!",
                    ];
                    return $response;
                } else {
                    $response = [
                        'error' => false,
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'message' => "order can not be cancelled",
                    ];
                    return $response;
                }
            } else {
                $res = json_decode($payment['body'], true);
                $msg = $res['error']['description'];
                $response = [
                    'error' => true,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => $msg,
                ];
                return $response;
            }
        }
        if ($type == "paystack" && $transaction[0]['status'] == "success") {
            $paystack = new Paystack();
            $payment = $paystack->refund($txn_id, $amount);
            $message = json_decode($payment, true);
            if (isset($payment['status']) && $payment['status'] == "true") {
                update_details(['status' => $status], ['id' => $order_id], 'orders');
                $amount = intval($payment['amount']) / 100;
                $data = [
                    'transaction_type' => $payment['entity'],
                    'order_id' => $order_id,
                    'user_id' => $customer_id,
                    'type' => 'paystack',
                    'txn_id' => $payment['payment_id'],
                    'amount' => $amount,
                    'currency_code' => $currency,
                    'status' => $payment['status'],
                ];
                $success = insert_details($data, 'transactions');
                if ($success) {
                    $response = [
                        'error' => false,
                        'message' => "Booking cancelled Successfully!",
                    ];
                    return $response;
                } else {
                    $response = [
                        'error' => false,
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash(),
                        'message' => "Booking can not be cancelled",
                    ];
                    return $response;
                }
            } else {
                $res = json_decode($payment, true);
                $response = [
                    'error' => true,
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                    'message' => $res['message'],
                ];
                return $response;
            }
        }
    } else {
        $response = [
            'error' => true,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'message' => 'No transaction found of this order!',
        ];
        return $response;
    }
}
function curl($url, $method = 'GET', $header = ['Content-Type: application/x-www-form-urlencoded'], $data = [], $authorization = null)
{
    $ch = curl_init();
    $curl_options = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_HTTPHEADER => $header,
    );
    if (strtolower($method) == 'post') {
        $curl_options[CURLOPT_POST] = 1;
        $curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
    } else {
        $curl_options[CURLOPT_CUSTOMREQUEST] = 'GET';
    }
    curl_setopt_array($ch, $curl_options);
    $result = array(
        'body' => curl_exec($ch),
        'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
    );
    return $result;
}
function generate_token()
{
    $jwt = new App\Libraries\JWT();
    $payload = [
        'iat' => time(), /* issued at time */
        'iss' => 'edemand',
        'exp' => time() + (30 * 60), /* expires after 1 minute */
        'sub' => 'edemand_authentication',
    ];
    $token = $jwt->encode($payload, "my_secret");
    return $token;
}
function verify_token()
{
    // to verify the token from admin pannel
    $responses = \Config\Services::response();
    $jwt = new App\Libraries\JWT;
    // verify_ip();
    try {
        $token = $jwt->getBearerToken();
    } catch (\Exception $e) {
        $response['error'] = true;
        $response['message'] = $e->getMessage();
        print_r(json_encode($response));
        return false;
    }
    if (!empty($token)) {
        $api_keys = API_SECRET;
        if (empty($api_keys)) {
            $response['error'] = true;
            $response['message'] = 'No Client(s) Data Found !';
            print_r(json_encode($response));
            return $response;
        }
        $flag = true; //For payload indication that it return some data or throws an expection.
        $error = true; //It will indicate that the payload had verified the signature and hash is valid or not.
        $message = '';
        $user_token = " ";
        try {
            $user_id = $jwt->decode_unsafe($token)->user_id;
            $user_token = fetch_details('users', ['id' => $user_id])[0]['api_key'];
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        try {
            $payload = $jwt->decode($token, $api_keys, ['HS256']);
            if (isset($payload->iss)) {
                $error = false;
                $flag = false;
            } else {
                $error = true;
                $flag = false;
                $message = 'Invalid Hash';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        if ($flag) {
            $response['error'] = true;
            $response['message'] = $message;
            print_r(json_encode($response));
            return false;
        } else {
            if ($error == true) {
                $response['error'] = true;
                $response['message'] = $message;
                $responses->setStatusCode(401);
                print_r(json_encode($response));
                return false;
            } else {
                return $payload->user_id;
            }
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Unauthorized access not allowed";
        print_r(json_encode($response));
        return false;
    }
}
function xss_clean($data)
{
    $data = trim($data);
    // Fix &entity\n;
    $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
    $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
    $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
    $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
    // Remove any attribute starting with "on" or xmlns
    $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
    // Remove javascript: and vbscript: protocols
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
    // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
    // Remove namespaced elements (we do not need them)
    $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
    do {
        // Remove really unwanted tags
        $old_data = $data;
        $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
    } while ($old_data !== $data);
    // we are done...
    return $data;
}
function get_settings($type = 'system_settings', $is_json = false, $bool = false)
{
    $db = \Config\Database::connect();
    $builder = $db->table('settings');
    if ($type == 'all') {
        $res = $builder->select(' * ')->get()->getResultArray();
    } else {
        $res = $builder->select(' * ')->where('variable', $type)->get()->getResultArray();
    }
    if (!empty($res)) {
        if ($is_json) {
            return json_decode($res[0]['value'], true);
        } else {
            return $res[0]['value'];
        }
    } else {
        if ($bool) {
            return false;
        } else {
            return [];
        }
    }
}
function output_escaping($array)
{
    if (!empty($array)) {
        if (is_array($array)) {
            $data = array();
            foreach ($array as $key => $value) {
                if ($value != null) {
                    $data[$key] = stripcslashes($value);
                }
            }
            return $data;
        } else if (is_object($array)) {
            $data = new stdClass();
            foreach ($array as $key => $value) {
                $data->$key = stripcslashes($value);
            }
            return $data;
        } else {
            return stripcslashes($array);
        }
    }
}
function escape_array($array)
{
    $db = \Config\Database::connect();
    $posts = array();
    if (!empty($array)) {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $posts[$key] = $db->escapeString($value);
            }
        } else {
            return $db->escapeString($array);
        }
    }
    return $posts;
}
function update_details($set, $where, $table, $escape = true)
{
    $db = \Config\Database::connect();
    $db->transStart();
    if ($escape) {
        $set = escape_array($set);
    }
    $db->table($table)->update($set, $where);
    $db->transComplete();
    $response = false;
    if ($db->transStatus() === true) {
        $response = true;
    }
    return $response;
}
function fetch_details($table, $where = [], $fields = [], $limit = "", $offset = '0', $sort = 'id', $order = 'DESC', $where_in_key = '', $where_in_value = [], $or_like = [])
{
    $db = \Config\Database::connect();
    $builder = $db->table($table);
    if (!empty($fields)) {
        $builder = $builder->select($fields);
    }
    if (!empty($where)) {
        $builder = $builder->where($where)->select($fields);
    }
    if (!empty($where_in_key) && !empty($where_in_value)) {
        $builder = $builder->whereIn($where_in_key, $where_in_value);
    }
    if (isset($or_like) && !empty($or_like)) {
        $builder->groupStart();
        $builder->orLike($or_like);
        $builder->groupEnd();
    }
    if ($limit != null && $limit != "") {
        $builder = $builder->limit($limit, $offset);
    }
    $builder = $builder->orderBy($sort, $order);
    $res = $builder->get()->getResultArray();
    return $res;
}
function exists($where, $table)
{
    $db = \Config\Database::connect();
    $builder = $db->table($table);
    $builder = $builder->where($where);
    $res = count($builder->get()->getResultArray());
    if ($res > 0) {
        return true;
    } else {
        return false;
    }
}
function get_group($name = "")
{
    $db = \Config\Database::connect();
    $builder = $db->table("groups as g");
    $builder->select('ug.*,g.name');
    $builder->where('g.name', $name);
    $builder->join('users_groups as ug', 'g.id = ug.group_id ', "left");
    $group = $builder->get()->getResultArray();
    return $group;
}
function slugify($text, $divider = '-')
{
    $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, $divider);
    $text = preg_replace('~-+~', $divider, $text);
    $text = strtolower($text);
    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}
function verify_payment_transaction($txn_id, $payment_method, $additional_data = [])
{
    $db = \Config\Database::connect();
    if (empty(trim($txn_id))) {
        $response['error'] = true;
        $response['message'] = "Transaction ID is required";
        return $response;
    }
    $razorpay = new Razorpay;
    switch ($payment_method) {
        case 'razorpay':
            $payment = $razorpay->fetch_payments($txn_id);
            if (!empty($payment) && isset($payment['status'])) {
                if ($payment['status'] == 'authorized') {
                    $capture_response = $razorpay->capture_payment($payment['amount'], $txn_id, $payment['currency']);
                    if ($capture_response['status'] == 'captured') {
                        $response['error'] = false;
                        $response['message'] = "Payment captured successfully";
                        $response['amount'] = $capture_response['amount'] / 100;
                        $response['data'] = $capture_response;
                        $response['status'] = $payment['status'];
                        return $response;
                    } else if ($capture_response['status'] == 'refunded') {
                        $response['error'] = true;
                        $response['message'] = "Payment is refunded.";
                        $response['amount'] = $capture_response['amount'] / 100;
                        $response['data'] = $capture_response;
                        $response['status'] = $payment['status'];
                        return $response;
                    } else {
                        $response['error'] = true;
                        $response['message'] = "Payment could not be captured.";
                        $response['amount'] = (isset($capture_response['amount'])) ? $capture_response['amount'] / 100 : 0;
                        $response['data'] = $capture_response;
                        $response['status'] = $payment['status'];
                        return $response;
                    }
                } else if ($payment['status'] == 'captured') {
                    $status = 'captured';
                    $response['error'] = false;
                    $response['message'] = "Payment captured successfully";
                    $response['amount'] = $payment['amount'] / 100;
                    $response['status'] = $payment['status'];
                    $response['data'] = $payment;
                    return $response;
                } else if ($payment['status'] == 'created') {
                    $status = 'created';
                    $response['error'] = true;
                    $response['message'] = "Payment is just created and yet not authorized / captured!";
                    $response['amount'] = $payment['amount'] / 100;
                    $response['data'] = $payment;
                    $response['status'] = $payment['status'];
                    return $response;
                } else {
                    $status = 'failed';
                    $response['error'] = true;
                    $response['message'] = "Payment is " . ucwords($payment['status']) . "! ";
                    $response['amount'] = (isset($payment['amount'])) ? $payment['amount'] / 100 : 0;
                    $response['status'] = $payment['status'];
                    $response['data'] = $payment;
                    return $response;
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Payment not found by the transaction ID!";
                $response['amount'] = 0;
                $response['data'] = [];
                $response['status'] = 'failed';
                return $response;
            }
            break;
        case "paystack":
            $paystack = new Paystack;
            $payment = $paystack->verify_transation($txn_id);
            if (!empty($payment)) {
                $payment = json_decode($payment, true);
                if (isset($payment['data']['status']) && $payment['data']['status'] == 'success') {
                    $response['error'] = false;
                    $response['message'] = "Payment is successful";
                    $response['amount'] = (isset($payment['data']['amount'])) ? $payment['data']['amount'] / 100 : 0;
                    $response['data'] = $payment;
                    $response['status'] = $payment['data']['status'];
                    return $response;
                } elseif (isset($payment['data']['status']) && $payment['data']['status'] != 'success') {
                    $response['error'] = true;
                    $response['message'] = "Payment is " . ucwords($payment['data']['status']) . "! ";
                    $response['amount'] = (isset($payment['data']['amount'])) ? $payment['data']['amount'] / 100 : 0;
                    $response['data'] = $payment;
                    $response['status'] = $payment['data']['status'];
                    return $response;
                } else {
                    $response['error'] = true;
                    $response['message'] = "Payment is unsuccessful! ";
                    $response['amount'] = (isset($payment['data']['amount'])) ? $payment['data']['amount'] / 100 : 0;
                    $response['data'] = $payment;
                    return $response;
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Payment not found by the transaction ID!";
                $response['amount'] = 0;
                $response['data'] = [];
                $response['status'] = 'failed';
                return $response;
            }
            break;
        case 'paytm':
            $paytm = new Paytm;
            $payment = $paytm->transaction_status($txn_id);
            if (!empty($payment)) {
                $payment = json_decode($payment, true);
                if (
                    isset($payment['body']['resultInfo']['resultCode'])
                    && ($payment['body']['resultInfo']['resultCode'] == '01' && $payment['body']['resultInfo']['resultStatus'] == 'TXN_SUCCESS')
                ) {
                    $response['error'] = false;
                    $response['message'] = "Payment is successful";
                    $response['amount'] = (isset($payment['body']['txnAmount'])) ? $payment['body']['txnAmount'] : 0;
                    $response['data'] = $payment;
                    return $response;
                } elseif (
                    isset($payment['body']['resultInfo']['resultCode'])
                    && ($payment['body']['resultInfo']['resultStatus'] == 'TXN_FAILURE')
                ) {
                    $response['error'] = true;
                    $response['message'] = $payment['body']['resultInfo']['resultMsg'];
                    $response['amount'] = (isset($payment['body']['txnAmount'])) ? $payment['body']['txnAmount'] : 0;
                    $response['data'] = $payment;
                    return $response;
                } else if (
                    isset($payment['body']['resultInfo']['resultCode'])
                    && ($payment['body']['resultInfo']['resultStatus'] == 'PENDING')
                ) {
                    $response['error'] = true;
                    $response['message'] = $payment['body']['resultInfo']['resultMsg'];
                    $response['amount'] = (isset($payment['body']['txnAmount'])) ? $payment['body']['txnAmount'] : 0;
                    $response['data'] = $payment;
                    return $response;
                } else {
                    $response['error'] = true;
                    $response['message'] = "Payment is unsuccessful!";
                    $response['amount'] = (isset($payment['body']['txnAmount'])) ? $payment['body']['txnAmount'] : 0;
                    $response['data'] = $payment;
                    return $response;
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Payment not found by the Order ID!";
                $response['amount'] = 0;
                $response['data'] = [];
                return $response;
            }
            break;
    }
}
function add_transaction($transaction_details)
{
    $db = \Config\Database::connect();
    $insert = $db->table('transactions')->insert($transaction_details);
    if ($insert) {
        return $db->insertID();
    } else {
        return false;
    }
}
function valid_image($image)
{
    helper(['form', 'url']);
    $request = \Config\Services::request();
    if ($request->getFile($image)) {
        $file = $request->getFile($image);
        if (!$file->isValid()) {
            return false;
        }
        $type = $file->getMimeType();
        if ($type == 'image/jpeg' || $type == 'image/png' || $type == 'image/jpg' || $type == 'image/svg+xml' || $type = 'image/gif') {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
function move_file($file, $path = 'public/uploads/images/', $name = '', $replace = false, $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/svg+xml', 'image/gif', 'application/json', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf'])
{
    $type = $file->getMimeType();
    $p = FCPATH . $path;
    if (in_array($type, $allowed_types)) {
        if ($name == '') {
            $name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file->getName());
        }
        $ext = $file->guessExtension();
        if ($file->move($p, $name, $replace)) {
            $name = $file->getName();
            $response['error'] = false;
            $response['message'] = "File moved successfully";
            $response['file_name'] = $name;
            $response['extension'] = $ext;
            $response['file_size'] = $file->getSizeByUnit("kb");
            $response['path'] = $path;
            $response['full_path'] = $path . $name;
        } else {
            $response['error'] = true;
            $response['message'] = "File could not be moved!" . $file->getError();
            $response['file_name'] = $name;
            $response['extension'] = "";
            $response['file_size'] = "";
            $response['path'] = $path;
            $response['full_path'] = "";
        }
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "File could not be moved! Invalid file type uploaded";
        return $response;
    }
}
function formatOffset($offset)
{
    $hours = $offset / 3600;
    $remainder = $offset % 3600;
    $sign = $hours > 0 ? '+' : '-';
    $hour = (int) abs($hours);
    $minutes = (int) abs($remainder / 60);
    if ($hour == 0 and $minutes == 0) {
        $sign = ' ';
    }
    return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0');
}
function get_timezone()
{
    $list = DateTimeZone::listAbbreviations();
    $idents = DateTimeZone::listIdentifiers();
    $data = $offset = $added = array();
    foreach ($list as $abbr => $info) {
        foreach ($info as $zone) {
            if (
                !empty($zone['timezone_id'])
                and
                !in_array($zone['timezone_id'], $added)
                and
                in_array($zone['timezone_id'], $idents)
            ) {
                $z = new DateTimeZone($zone['timezone_id']);
                $c = new DateTime("", $z);
                $zone['time'] = $c->format('H:i a');
                $offset[] = $zone['offset'] = $z->getOffset($c);
                $data[] = $zone;
                $added[] = $zone['timezone_id'];
            }
        }
    }
    array_multisort($offset, SORT_ASC, $data);
    $options = array();
    foreach ($data as $key => $row) {
        $options[$row['timezone_id']] = $row['time'] . ' - '
            . formatOffset($row['offset'])
            . ' ' . $row['timezone_id'];
    }
    return $options;
}
function get_timezone_array()
{
    $list = DateTimeZone::listAbbreviations();
    $idents = DateTimeZone::listIdentifiers();
    $data = $offset = $added = array();
    foreach ($list as $abbr => $info) {
        foreach ($info as $zone) {
            if (
                !empty($zone['timezone_id'])
                and
                !in_array($zone['timezone_id'], $added)
                and
                in_array($zone['timezone_id'], $idents)
            ) {
                $z = new DateTimeZone($zone['timezone_id']);
                $c = new DateTime("", $z);
                $zone['time'] = $c->format('h:i A');
                $offset[] = $zone['offset'] = $z->getOffset($c);
                $data[] = $zone;
                $added[] = $zone['timezone_id'];
            }
        }
    }
    array_multisort($offset, SORT_ASC, $data);
    $i = 0;
    $temp = array();
    foreach ($data as $key => $row) {
        $temp[0] = $row['time'];
        $temp[1] = formatOffset($row['offset']);
        $temp[2] = $row['timezone_id'];
        $options[$i++] = $temp;
    }
    return $options;
}
function check_exists($file)
{
    // $file_headers = @get_headers($file);
    $target_path = FCPATH . $file;
    if (!file_exists($target_path)) {
        return true;
    } else {
        return false;
    }
}
function numbers_initials($num)
{
    if ($num > 1000) {
        $x = round($num);
        $x_number_format = number_format($x);
        $x_array = explode(',', $x_number_format);
        $x_parts = array('K', 'M', 'B', 'T');
        $x_count_parts = count($x_array) - 1;
        $x_display = $x;
        $x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
        $x_display .= $x_parts[$x_count_parts - 1];
        return $x_display;
    }
    return $num;
}
function mail_error($subject, $message, $trace = "")
{
}
function mask_email($email)
{
    $em = explode("@", $email);
    $name = implode('@', array_slice($em, 0, count($em) - 1));
    $len = floor(strlen($name) / 2);
    return substr($name, 0, $len) . str_repeat('*', $len) . "@" . end($em);
}
function get_system_update_info()
{
    $check_query = false;
    $query_path = "";
    $data['previous_error'] = false;
    $sub_directory = (file_exists(UPDATE_PATH . "update/updater.json")) ? "update/" : "";
    if (file_exists(UPDATE_PATH . "updater.json") || file_exists(UPDATE_PATH . "update/updater.json")) {
        $lines_array = file_get_contents(UPDATE_PATH . $sub_directory . "updater.json");
        $lines_array = json_decode($lines_array, true);
        $file_version = $lines_array['version'];
        $file_previous = $lines_array['previous'];
        $check_query = $lines_array['manual_queries'];
        $query_path = $lines_array['query_path'];
    } else {
        print_r("no json exists");
        die();
    }
    $db_version_data = fetch_details("updates");
    if (!empty($db_version_data) && isset($db_version_data[0]['version'])) {
        $db_current_version = $db_version_data[0]['version'];
    }
    if (!empty($db_current_version)) {
        $data['db_current_version'] = $db_current_version;
    } else {
        $data['db_current_version'] = $db_current_version = 1.0;
    }
    if ($db_current_version == $file_previous) {
        $data['file_current_version'] = $file_current_version = $file_version;
    } else {
        $data['previous_error'] = true;
        $data['file_current_version'] = $file_current_version = false;
    }
    if ($file_current_version != false && $file_current_version > $db_current_version) {
        $data['is_updatable'] = true;
    } else {
        $data['is_updatable'] = false;
    }
    $data['query'] = $check_query;
    $data['query_path'] = $query_path;
    return $data;
}
function labels($label, $alt = '')
{
    $label = trim($label);
    if (lang('Text.' . $label) != 'Text.' . $label) {
        if (lang('Text.' . $label) == '') {
            return $alt;
        }
        return trim(lang('Text.' . $label));
    } else {
        return trim($alt);
    }
}
function create_label($variable, $title = '')
{
    if ($title == '') {
        $title = $variable;
    }
    return '<div class="form-group col-md-6">
            <label>' . $title . '</label>
            <input type="text" name="' . $variable . '" value="' . labels($variable) . '" class="form-control">
        </div>';
}
function get_currency()
{
    try {
        $currency = get_settings('general_settings', true)['currency'];
        if ($currency == '') {
            $currency = '₹';
        }
    } catch (Exception $e) {
        $currency = '₹';
    }
    return $currency;
}
function console_log($data)
{
    if (is_array($data)) {
        $data = json_encode($data);
    } elseif (is_object($data)) {
        $data = json_encode($data);
    }
    echo "<script>console.log('$data')</script>";
}
function delete_directory($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") {
                    // return 'this is folder';
                    $dir_sec = $dir . "/" . $object;
                    if (is_dir($dir_sec)) {
                        $objects_sec = scandir($dir_sec);
                        foreach ($objects_sec as $object_sec) {
                            if ($object_sec != "." && $object_sec != "..") {
                                if (filetype($dir_sec . "/" . $object_sec) == "dir") {
                                    rmdir($dir_sec . "/" . $object_sec);
                                } else {
                                    unlink($dir_sec . "/" . $object_sec);
                                }
                            }
                        }
                        rmdir($dir_sec);
                    }
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        return rmdir($dir);
    }
}
function format_number($number, $decimals = 0, $decimal_separator = '.', $thousand_separator = ',', $currency_symbol = '', $type = 'prefix')
{
    $number = number_format($number, $decimals, $decimal_separator, $thousand_separator);
    $number = (!empty(trim($currency_symbol))) ? (($type == 'prefix') ? $currency_symbol . $number : $number . $currency_symbol) : $number;
    return $number;
}
function email_sender($user_email, $subject, $message)
{
    $email = \Config\Services::email();
    $email_settings = \get_settings('email_settings', true);
    $company_settings = \get_settings('general_settings', true);
    $smtpUsername = $email_settings['smtpUsername'];
    // $email_type = $email_settings['mailType'];
    $company_name = $company_settings['company_title'];
    $email->setFrom($smtpUsername, $company_name);
    $email->setTo($user_email);
    $email->setSubject($subject);
    $email->setMessage($message);
    // print_R($email->send());
    if ($email->send()) {
        // echo "mail send";
        // return true;
    } else {
        $data = $email->printDebugger(['headers']);
        return $data;
    }
}
// new functions
function insert_details(array $data, string $table): array
{
    $db = \Config\Database::connect();
    $status = $db->table($table)->insert($data);
    $id = $db->insertID();
    if (!$status) {
        return [
            "error" => true,
            "message" => UNKNOWN_ERROR_MESSAGE,
            "data" => [],
        ];
    }
    return [
        "error" => false,
        "message" => "Data inserted",
        "id" => $id,
        "data" => [],
    ];
}
//remove null value from array
function remove_null_values(array $data)
{
    // interger column names
    $integer = [
        'alternate_mobile' => 0,
        'range_wise_charges' => 0,
        'per_km_charge' => 0,
        'max_deliverable_distance' => 0,
        'fixed_charge' => 0,
        'discount' => 0,
    ];
    //enter emtpy array columnt
    $array = [];
    foreach ($data as $key => $value) {
        if (is_array($value) || is_object($value)) {
            $data[$key] = remove_null_values($value);
        } else {
            if (is_null($value)) {
                if (isset($integer[$key])) {
                    //add 0
                    $data[$key] = 0;
                } else if (isset($array[$key])) {
                    //add empty array
                    $data[$key] = [];
                } else {
                    //add empty string
                    $data[$key] = '';
                }
            }
        }
    }
    return $data;
}
function _response(string $message = UNKNOWN_ERROR_MESSAGE, bool $error = true, $data = [], int $status_code = 200, $additional_data = [])
{
    $response = \Config\Services::response();
    $send = [
        "error" => $error,
        "message" => $message,
        "data" => $data,
    ];
    $send = array_merge($send, $additional_data);
    return $response->setJSON($send)->setStatusCode($status_code);
}
function delete_details(array $data, string $table)
{
    $db = \Config\Database::connect();
    $builder = $db->table($table);
    if ($builder->delete($data)) {
        return true;
    }
    return false;
}
function validate_promo_code($user_id, $promo_code, $final_total)
{
    $db = \Config\Database::connect();
    $builder = $db->table('promo_codes pc');
    // $promo_code = $builder->select('pc.*,count(o.id) as promo_used_counter ,( SELECT count(user_id) from orders where user_id =' . $user_id . ' and promo_code ="' . $promo_code . '") as user_promo_usage_counter ')
    //     ->join('orders o', 'o.promo_code=pc.promo_code', 'left')
    //     ->where(['pc.promo_code' => $promo_code, 'pc.status' => '1', ' start_date <= ' => date('Y-m-d'), '  end_date >= ' => date('Y-m-d')])
    //     ->get()->getResultArray();
    $promo_code = $builder->select('pc.*,count(o.id) as promo_used_counter ,( SELECT count(user_id) from orders where user_id =' . $user_id . ' and promocode_id ="' . $promo_code . '") as user_promo_usage_counter ')
        ->join('orders o', 'o.promocode_id=pc.id', 'left')
        ->where(['pc.id' => $promo_code, 'pc.status' => '1', ' start_date <= ' => date('Y-m-d'), '  end_date >= ' => date('Y-m-d')])
        ->get()->getResultArray();
    if (!empty($promo_code[0]['id'])) {
        // echo "if";
        // die;
        if (intval($promo_code[0]['promo_used_counter']) < intval($promo_code[0]['no_of_users'])) {
            if ($final_total >= intval($promo_code[0]['minimum_order_amount'])) {
                if ($promo_code[0]['repeat_usage'] == 1 && ($promo_code[0]['user_promo_usage_counter'] <= $promo_code[0]['no_of_repeat_usage'])) {
                    if (intval($promo_code[0]['user_promo_usage_counter']) <= intval($promo_code[0]['no_of_repeat_usage'])) {
                        $response['error'] = false;
                        $response['message'] = 'The promo code is valid';
                        if ($promo_code[0]['discount_type'] == 'percentage') {
                            $promo_code_discount = floatval($final_total * $promo_code[0]['discount'] / 100);
                        } else {
                            if ($promo_code[0]['discount'] > $final_total) {
                                $promo_code_discount = $final_total;
                            } else {
                                $promo_code_discount = $promo_code[0]['discount'];
                            }
                        }
                        if ($promo_code_discount <= $promo_code[0]['max_discount_amount']) {
                            $total = floatval($final_total) - $promo_code_discount;
                        } else {
                            $total = floatval($final_total) - $promo_code[0]['max_discount_amount'];
                            $promo_code_discount = $promo_code[0]['max_discount_amount'];
                        }
                        $promo_code[0]['final_total'] = strval(floatval($total));
                        $promo_code[0]['final_discount'] = strval(floatval($promo_code_discount));
                        $response['data'] = $promo_code;
                        return $response;
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'This promo code cannot be redeemed as it exceeds the usage limit';
                        $response['data']['final_total'] = strval(floatval($final_total));
                        return $response;
                    }
                } else if ($promo_code[0]['repeat_usage'] == 0 && ($promo_code[0]['user_promo_usage_counter'] <= 0)) {
                    if (intval($promo_code[0]['user_promo_usage_counter']) <= intval($promo_code[0]['no_of_repeat_usage'])) {
                        $response['error'] = false;
                        $response['message'] = 'The promo code is valid';
                        if ($promo_code[0]['discount_type'] == 'percentage') {
                            $promo_code_discount = floatval($final_total * $promo_code[0]['discount'] / 100);
                        } else {
                            $promo_code_discount = floatval($final_total - $promo_code[0]['discount']);
                        }
                        if ($promo_code_discount <= $promo_code[0]['max_discount_amount']) {
                            $total = floatval($final_total) - $promo_code_discount;
                        } else {
                            $total = floatval($final_total) - $promo_code[0]['max_discount_amount'];
                            $promo_code_discount = $promo_code[0]['max_discount_amount'];
                        }
                        $promo_code[0]['final_total'] = strval(floatval($total));
                        $promo_code[0]['final_discount'] = strval(floatval($promo_code_discount));
                        $response['data'] = $promo_code;
                        return $response;
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'This promo code cannot be redeemed as it exceeds the usage limit';
                        $response['data']['final_total'] = strval(floatval($final_total));
                        return $response;
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = 'The promo has already been redeemed. cannot be reused';
                    $response['data']['final_total'] = strval(floatval($final_total));
                    return $response;
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'This promo code is applicable only for amount greater than or equal to ' . $promo_code[0]['minimum_order_amount'];
                $response['data']['final_total'] = strval(floatval($final_total));
                return $response;
            }
        } else {
            $response['error'] = true;
            $response['message'] = "promocode usage exceeded";
            $response['data']['final_total'] = strval(floatval($final_total));
            return $response;
        }
    } else {
        //      echo "else";
        // die;
        $response['error'] = true;
        $response['message'] = 'The promo code is not available or expired*******';
        $response['data']['final_total'] = strval(floatval($final_total));
        return $response;
    }
}
function get_near_partners($latitude, $longitude, $distance, $is_array = false)
{
    // $max_deliverable_distance = fetch_details('cities', ['id' => $city_id], ['max_deliverable_distance'])[0]['max_deliverable_distance'];
    $max_deliverable_distance = $distance;
    $db = \Config\Database::connect();
    $point = ($latitude > -90 && $latitude < 90) ? "POINT($latitude" : "POINT($latitude > 90";
    $point .= ($longitude > -180 && $longitude < 180) ? " $longitude)" : " $longitude > 180)";
    $builder = $db->table('users u');
    $partners = $builder->Select("u.latitude,u.longitude,u.id,st_distance_sphere(POINT($longitude, $latitude), POINT(`longitude`, `latitude` ))/1000  as distance")
        ->join('users_groups ug', 'ug.user_id=u.id')
        ->where('ug.group_id', '3')
        ->where('ABS((u.latitude)) > 180  or  ABS((u.longitude)) > 90')
        ->having('distance < ' . $max_deliverable_distance)
        ->orderBy('distance')
        ->get()->getResultArray();
    $ids = [];
    foreach ($partners as $key => $parnter) {
        $ids[] = $parnter['id'];
    }
    if ($is_array == false) {
        $ids = implode(',', $ids);
    }
    return $ids;
}
function fetch_cart($from_app = false, int $user_id = 0, string $search = '', $limit = 0, int $offset = 0, string $sort = 'c.id', string $order = 'Desc', $where = [], $additional_data = [], $reorder = null, $order_id = null)
{
    $db = \Config\Database::connect();
    $builder = $db->table('cart c');
    $sortable_fields = [
        'c.id' => 'c.id',
    ];
    if ($search and $search != '') {
        $multipleWhere = [
            '`s.id`' => $search, '`s.title`' => $search, '`s.description`' => $search, '`s.status`' => $search, '`s.tags`' => $search,
            '`s.price`' => $search, '`s.discounted_price`' => $search, '`s.rating`' => $search, '`s.number_of_ratings`' => $search,
            '`s.max_quantity_allowed`' => $search,
        ];
    }
    $total = $builder->select(' COUNT(c.id) as `total` ')->where('c.user_id', $user_id);
    if (isset($multipleWhere) && !empty($multipleWhere)) {
        $builder->orWhere($multipleWhere);
    }
    if (isset($where) && !empty($where)) {
        $builder->where($where);
    }
    $service_count = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
    $total = $service_count[0]['total'];
    if (isset($multipleWhere) && !empty($multipleWhere)) {
        $builder->orLike($multipleWhere);
    }
    if (isset($where) && !empty($where)) {
        $builder->where($where);
    }
    if ($reorder == 'yes' && !empty($order_id)) {
        $builder = $db->table('order_services os');
        $service_record = $builder
            ->select('os.id as cart_id,os.service_id,os.quantity as qty,s.*,s.title as service_name,p.username as partner_name,pd.visiting_charges as visiting_charges,cat.name as category_name')
            ->join('services s', 'os.service_id=s.id', 'left')
            ->join('orders o', 'o.id=os.order_id', 'left')
            ->join('users p', 'p.id=s.user_id', 'left')
            ->join('categories cat', 'cat.id=s.category_id', 'left')
            ->join('partner_details pd', 'pd.partner_id=s.user_id', 'left')
            ->where('os.order_id', $order_id)
            ->where('o.user_id', $user_id)->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
    } else {
        $service_record = $builder
            ->select('c.id as cart_id,c.service_id,c.qty,c.is_saved_for_later,s.*,s.title as service_name,p.username as partner_name,pd.visiting_charges as visiting_charges,cat.name as category_name')
            ->join('services s', 'c.service_id=s.id', 'left')
            ->join('users p', 'p.id=s.user_id', 'left')
            ->join('categories cat', 'cat.id=s.category_id', 'left')
            ->join('partner_details pd', 'pd.partner_id=s.user_id', 'left')
            ->where('c.user_id', $user_id)->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
    }
    $bulkData = $rows = $tempRow = array();
    $bulkData['total'] = $total;
    $tax = get_settings('system_tax_settings', true)['tax'];
    foreach ($service_record as $row) {
        // OPD area
        if ($from_app) {
            if (check_exists(base_url('/public/uploads/services/' . $row['image']))) {
                $images = base_url($row['image']);
            } else {
                $images = 'nothing found';
            }
        } else {
            if (check_exists(base_url('/public/uploads/services/' . $row['image']))) {
                $images = '<a  href="' . base_url('/public/uploads/services/' . $row['image']) . '" data-lightbox="image-1"><img height="80px" class="rounded-circle" src="' . base_url("/public/uploads/services/" . $row['image']) . '" alt="image of the services multiple will be here"></a>';
            } else {
                $images = 'nothing found';
            }
        }
        $status = ($row['status'] == 1) ? 'Enable' : 'Disable';
        $site_allowed = ($row['on_site_allowed'] == 1) ? 'Allowed' : 'Not Allowed';
        $pay_later = ($row['is_pay_later_allowed'] == 1) ? 'Allowed' : 'Not Allowed';
        $rating = $row['rating'] . "/5";
        // OPD area ends
        $tempRow['id'] = $row['cart_id'];
        $tempRow['service_id'] = $row['service_id'];
        $tempRow['service_id'] = $row['service_id'];
        $tempRow['is_saved_for_later'] = isset($row['is_saved_for_later']) ? $row['is_saved_for_later'] : "";
        $tempRow['qty'] = isset($row['qty']) ? $row['qty'] : 0;
        $tempRow['visiting_charges'] = $row['visiting_charges'];
        $tempRow['price'] = $row['price'];
        $tempRow['discounted_price'] = $row['discounted_price'];
        $taxPercentageData = fetch_details('taxes', ['id' => $row['tax_id']], ['percentage']);
        if (!empty($taxPercentageData)) {
            $taxPercentage = $taxPercentageData[0]['percentage'];
        } else {
            $taxPercentage = 0;
        }
        $tempRow['servic_details']['id'] = $row['id'];
        $tempRow['servic_details']['partner_id'] = $row['user_id'];
        $tempRow['servic_details']['category_id'] = $row['category_id'];
        $tempRow['servic_details']['category_name'] = $row['category_name'];
        $tempRow['servic_details']['partner_name'] = $row['partner_name'];
        $tempRow['servic_details']['tax_type'] = $row['tax_type'];
        $tempRow['servic_details']['tax_id'] = $row['tax_id'];
        $tempRow['servic_details']['current_tax_percentage'] = $taxPercentage;
        $tempRow['servic_details']['tax'] = $row['tax'];
        $tempRow['servic_details']['title'] = $row['title'];
        $tempRow['servic_details']['slug'] = $row['slug'];
        $tempRow['servic_details']['description'] = $row['description'];
        $tempRow['servic_details']['tags'] = $row['tags'];
        $tempRow['servic_details']['image_of_the_service'] = $images;
        $tempRow['servic_details']['price'] = $row['price'];
        $tempRow['servic_details']['discounted_price'] = $row['discounted_price'];
        $tempRow['servic_details']['number_of_members_required'] = $row['number_of_members_required'];
        $tempRow['servic_details']['duration'] = $row['duration'];
        $tempRow['servic_details']['tags'] = json_decode((string) $row['tags'], true);
        $tempRow['servic_details']['rating'] = $rating;
        $tempRow['servic_details']['number_of_ratings'] = $row['number_of_ratings'];
        $tempRow['servic_details']['on_site_allowed'] = $site_allowed;
        $tempRow['servic_details']['max_quantity_allowed'] = $row['max_quantity_allowed'];
        $tempRow['servic_details']['is_pay_later_allowed'] = $pay_later;
        $tempRow['servic_details']['status'] = $status;
        $tempRow['servic_details']['created_at'] = $row['created_at'];
        if ($row['discounted_price'] == "0") {
            if ($row['tax_type'] == "excluded") {
                $tempRow['servic_details']['price_with_tax'] = strval(str_replace(',', '', number_format(strval($row['price'] + ($row['price'] * ($taxPercentage) / 100)), 2)));
                $tempRow['tax_value'] = number_format((intval(($row['price'] * ($taxPercentage) / 100))), 2);
                $tempRow['servic_details']['original_price_with_tax'] = strval(str_replace(',', '', number_format(strval($row['price'] + ($row['price'] * ($taxPercentage) / 100)), 2)));
            } else {
                $tempRow['servic_details']['price_with_tax'] = strval(str_replace(',', '', number_format(strval($row['price']), 2)));
                $tempRow['tax_value'] = "";
                $tempRow['servic_details']['original_price_with_tax'] = strval(str_replace(',', '', number_format(strval($row['price']), 2)));
            }
        } else {
            if ($row['tax_type'] == "excluded") {
                $tempRow['servic_details']['price_with_tax'] = strval(str_replace(',', '', number_format(strval($row['discounted_price'] + ($row['discounted_price'] * ($taxPercentage) / 100)), 2)));
                $tempRow['tax_value'] = number_format((intval(($row['discounted_price'] * ($taxPercentage) / 100))), 2);
                $tempRow['servic_details']['original_price_with_tax'] = strval(str_replace(',', '', number_format(strval($row['price'] + ($row['price'] * ($taxPercentage) / 100)), 2)));
            } else {
                $tempRow['servic_details']['price_with_tax'] = $row['discounted_price'];
                $tempRow['tax_value'] = "";
                $tempRow['servic_details']['original_price_with_tax'] = strval(str_replace(',', '', number_format(strval($row['price']), 2)));
            }
        }
        $rows[] = $tempRow;
    }
    if ($from_app) {
        if (!empty($service_record)) {
            if (($reorder) == 'yes' && !empty($order_id)) {
                $builder = $db->table('order_services os');
                $order_record = $builder
                    ->select('os.id, os.service_id, os.quantity as qty')
                    ->join('orders o', 'o.id=os.order_id', 'left')
                    ->where('o.user_id', $user_id)
                    ->where('os.order_id', $order_id)
                    ->orderBy($sort, $order)
                    ->limit($limit, $offset)
                    ->get()
                    ->getResultArray();
                foreach ($order_record as $row) {
                    $array_ids[] = [
                        'service_id' => $row['service_id'],
                        'qty' => $row['qty'],
                    ];
                }
                // Now, $array_ids contains the desired structure
            } else {
                $array_ids = fetch_details('cart c', ['user_id' => $user_id], 'service_id,qty');
            }
            $s = [];
            $q = [];
            foreach ($array_ids as $ids) {
                array_push($s, $ids['service_id']);
                array_push($q, $ids['qty']);
            }
            $id = implode(',', $s);
            $qty = implode(',', $q);
            $builder = $db->table('services s');
            // $extra_data = $builder
            //     ->select('SUM(IF(s.discounted_price  > 0 , (s.discounted_price * c.qty) , (s.price * c.qty))) as subtotal,
            //     SUM(c.qty) as total_quantity,pd.visiting_charges as visiting_charges,SUM(s.duration * c.qty) as total_duration,pd.advance_booking_days as advance_booking_days,pd.company_name as company_name')
            //     ->join('cart c', 'c.service_id = s.id')
            //     ->join('partner_details pd', 'pd.partner_id=s.user_id')
            //     ->where('c.user_id', $user_id)
            //     ->whereIn('s.id', $s)->get()->getResultArray();
            if (($reorder) == 'yes' && !empty($order_id)) {
                $builder = $db->table('order_services os');
                $extra_data = $builder
                    ->select('SUM(IF(s.discounted_price  > 0 , (s.discounted_price * os.quantity) , (s.price * os.quantity))) as subtotal,
                SUM(os.quantity) as total_quantity,pd.visiting_charges as visiting_charges,SUM(s.duration * os.quantity) as total_duration,pd.at_store,pd.at_doorstep,pd.advance_booking_days as advance_booking_days,pd.company_name as company_name')
                    ->join('services s', 'os.service_id=s.id', 'left')
                    ->join('partner_details pd', 'pd.partner_id=s.user_id')
                    ->where('os.order_id', $order_id)
                    ->whereIn('s.id', $s)->get()->getResultArray();
            } else {
                $builder = $db->table('services s');
                $extra_data = $builder
                    ->select('SUM(IF(s.discounted_price  > 0 , (s.discounted_price * c.qty) , (s.price * c.qty))) as subtotal,
               SUM(c.qty) as total_quantity,pd.visiting_charges as visiting_charges,SUM(s.duration * c.qty) as total_duration,pd.at_store,pd.at_doorstep,pd.advance_booking_days as advance_booking_days,pd.company_name as company_name')
                    ->join('cart c', 'c.service_id = s.id')
                    ->join('partner_details pd', 'pd.partner_id=s.user_id')
                    ->where('c.user_id', $user_id)
                    ->whereIn('s.id', $s)->get()->getResultArray();
            }
            // $sub_total = $extra_data[0]['subtotal'];
            $tax_value = 0;
            $sub_total = 0;
            foreach ($service_record as $s1) {
                $taxPercentageData = fetch_details('taxes', ['id' => $s1['tax_id']], ['percentage']);
                if (!empty($taxPercentageData)) {
                    $taxPercentage = $taxPercentageData[0]['percentage'];
                } else {
                    $taxPercentage = 0;
                }
                if ($s1['discounted_price'] == "0") {
                    $tax_value = ($s1['tax_type'] == "excluded") ? number_format(((($s1['price'] * ($taxPercentage) / 100))), 2) : 0;
                    $price = number_format($s1['price'], 2);
                } else {
                    $tax_value = ($s1['tax_type'] == "excluded") ? number_format(((($s1['discounted_price'] * ($taxPercentage) / 100))), 2) : 0;
                    $price = number_format($s1['discounted_price'], 2);
                }
                $sub_total = $sub_total + (floatval(str_replace(",", "", $price)) + $tax_value) * $s1['qty'];
                //  $sub_total=$sub_total+($price+$tax_value)*$s1['qty'];
            }
            $data['total'] = (empty($total)) ? (string) count($rows) : $total;
            $data['advance_booking_days'] = isset($extra_data[0]['advance_booking_days']) ? $extra_data[0]['advance_booking_days'] : "";
            $data['visiting_charges'] = $extra_data[0]['visiting_charges'];
            $data['company_name'] = isset($extra_data[0]['company_name']) ? $extra_data[0]['company_name'] : "";
            $data['at_store'] = isset($extra_data[0]['at_store']) ? $extra_data[0]['at_store'] : "0";
            $data['at_doorstep'] = isset($extra_data[0]['at_doorstep']) ? $extra_data[0]['at_doorstep'] : "0";
            $data['service_ids'] = $id;
            $data['qtys'] = isset($qty) ? $qty : 0;
            $data['total_quantity'] = $extra_data[0]['total_quantity'];
            $data['total_duration'] = $extra_data[0]['total_duration'];
            $data['sub_total'] = strval(str_replace(',', '', number_format(strval($sub_total), 2)));
            $data['overall_amount'] = strval(str_replace(',', '', number_format(strval($sub_total + $data['visiting_charges']), 2)));
            $data['data'] = $rows;
            $provider_data = $db->table('services s');
            $providers = $provider_data
                ->select('u.username as provider_names, u.id as provider_id')
                ->join('users u', 'u.id = s.user_id')
                ->whereIn('s.id', $s)->get()->getResultArray();
            $pds = [];
            $pid = [];
            foreach ($providers as $provider) {
                array_push($pds, $provider['provider_names']);
                array_push($pid, $provider['provider_id']);
            }
            $unique_name = array_unique($pds);
            $unique_id = array_unique($pid);
            $names = implode(',', $unique_name);
            $ids = implode(',', $unique_id);
            $data['provider_names'] = $names;
            $data['provider_id'] = $ids;
            $pay_later_array = [];
            foreach ($service_record as $service_row) {
                array_push($pay_later_array, $service_row['is_pay_later_allowed']);
            }
            $active_partner_subscription = fetch_details('partner_subscriptions', ['partner_id' => $providers[0]['provider_id'], 'status' => 'active']);
            // $subscription_details = fetch_details('subscriptions', ['id' => $active_partner_subscription[0]['subscription_id']]);
            $provider_details = fetch_details('users', ['id' => $providers[0]['provider_id']]);
            if (!empty($active_partner_subscription)) {
                if ($active_partner_subscription[0]['is_commision'] == "yes") {
                    $commission_threshold = $active_partner_subscription[0]['commission_threshold'];
                } else {
                    $commission_threshold = 0;
                }
            } else {
                $commission_threshold = 0;
            }
            $payable_commision_of_provider = $provider_details[0]['payable_commision'];
            if (($payable_commision_of_provider >= $commission_threshold) && $commission_threshold != 0) {
                $data['is_pay_later_allowed'] = 0;
            } else {
                if (in_array(0, $pay_later_array)) {
                    $data['is_pay_later_allowed'] = 0;
                } else {
                    $data['is_pay_later_allowed'] = 1;
                }
            }
            return $data;
        } else {
            $data = [];
            return $data;
        }
    } else {
        // else return json
        $bulkData['rows'] = $rows;
        return json_encode($bulkData);
    }
}
function send_bulk_notifications($fcmMsg, $registrationIDs_chunks, $user_ids)
{
    $fcmFields = [];
    foreach ($registrationIDs_chunks as $registrationIDs) {
        $fcmFields = array(
            'registration_ids' => $registrationIDs, // expects an array of ids
            'priority' => 'high',
            'notification' => $fcmMsg,
            'data' => $fcmMsg,
        );
        $data = $fcmFields;
        unset($data['registration_ids']);
        $data = json_encode($data);
        // set json encoded data in Database
        $headers = array(
            'Authorization: key=' . get_settings('fcm_server_key'),
            'Content-Type: application/json',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmFields));
        $result = curl_exec($ch);
        curl_close($ch);
    }
    // print_r($fcmFields);
    return $fcmFields;
}
function get_taxable_amount($service_id)
{
    $service_details = fetch_details('services', ['id' => $service_id])[0];
    // print_r($service_details);
    // die;
    if ($service_details['tax_id'] != 0) {
        $tax_details = fetch_details('taxes', ['id' => $service_details['tax_id']])[0];
        $tax_percentage = strval(str_replace(',', '', number_format(strval($tax_details['percentage']), 2)));
    } else {
        $tax_percentage = 0;
    }
    // if ($service_details['tax'] != 0) {
    //     $tax_percentage = $service_details['tax'];
    // } else {
    //     $tax_percentage = 0;
    // }
    // $price = (!empty($service_details['discounted_price'] && $service_details['discounted_price'] < $service_details['price'])) ? $service_details['discounted_price'] : $service_details['price'];
    // strval(str_replace(',', '', number_format(strval( ($service_details['taxable_amount'] * ($qtys[$i]))), 2)))
    $taxable_amount = 0;
    if ($service_details['tax_type'] == "excluded") {
        if ($service_details['discounted_price'] == 0) {
            $tax_amount = (!empty($tax_percentage)) ? ($service_details['price'] * $tax_percentage) / 100 : 0;
            $taxable_amount = strval(str_replace(',', '', number_format(strval($service_details['price'] + ($tax_amount)), 2)));
        } else {
            $tax_amount = (!empty($tax_percentage)) ? ($service_details['discounted_price'] * $tax_percentage) / 100 : 0;
            $taxable_amount = strval(str_replace(',', '', number_format(strval($service_details['discounted_price'] + ($tax_amount)), 2)));
        }
    } else {
        if ($service_details['discounted_price'] == 0) {
            $tax_amount = (!empty($tax_percentage)) ? ($service_details['price'] * $tax_percentage) / 100 : 0;
            $taxable_amount = strval(str_replace(',', '', number_format(strval($service_details['price']), 2)));
        } else {
            $tax_amount = (!empty($tax_percentage)) ? ($service_details['discounted_price'] * $tax_percentage) / 100 : 0;
            $taxable_amount = strval(str_replace(',', '', number_format(strval($service_details['discounted_price']), 2)));
        }
    }
    $result = [
        'title' => $service_details['title'],
        'tax_percentage' => $tax_percentage,
        'tax_amount' => $tax_amount,
        'price' => $service_details['price'],
        'discounted_price' => $service_details['discounted_price'],
        'taxable_amount' => $taxable_amount ?? 0,
    ];
    return $result;
}
function get_partner_ids(string $type = '', string $column_name = 'id', array $ids = [], $is_array = false, array $fields_name = ['*'])
{
    $db = \Config\Database::connect();
    if ($type == 'service') {
        $builder = $db->table('services s');
        $partners = $builder->select('s.user_id as id')
            ->whereIn('s.' . $column_name, $ids)
            ->get()->getResultArray();
    } else if ($type == 'category') {
        $builder = $db->table('services s');
        $partners = $builder->select('s.user_id as id')
            ->whereIN('s.' . $column_name, $ids)
            ->get()->getResultArray();
        // echo $db->getLastQuery();
    } else {
        $builder = $db->table('users u');
        $partners = $builder->select($fields_name)
            ->join('users_groups ug', 'ug.user_id=u.id')
            ->where('ug.group_id', '3')
            ->whereIn($column_name, $ids)
            ->get()->getResultArray();
    }
    $ids = [];
    foreach ($partners as $key => $parnter) {
        $ids[] = $parnter['id'];
    }
    $ids = array_unique($ids);
    if ($is_array == false) {
        $ids = implode(',', $ids);
    }
    return $ids;
}
function check_partner_availibility(int $partner_id)
{
    $days = [
        'Mon' => 'monday',
        'Tue' => 'tuesday',
        'Wed' => 'wednsday',
        'Thu' => 'thursday',
        'Fri' => 'friday',
        'Sat' => 'staturday',
        'Sun' => 'sunday',
    ];
    $partner_timing = fetch_details('partner_timings', ['partner_id' => $partner_id, 'day' => $days[date('D')]]);
    if (empty($partner_timing)) {
        return false;
    }
    $partner_timing = $partner_timing[0];
    $time = new DateTime($partner_timing['opening_time']);
    $opening_time = $time->format('H:i');
    $time = new DateTime($partner_timing['closing_time']);
    $closing_time = $time->format('H:i');
    $current_time = date('H:i');
    if (($opening_time <= $current_time) or ($current_time >= $closing_time)) {
        return $partner_timing;
    } else {
        return false;
    }
    // print_r($partner_timing);
}
function get_booked_slot($service_id, $date)
{
    $db = \Config\Database::connect();
    $builder = $db->table('services s');
    $builder->select('s.user_id,o.starting_time,o.ending_time,o.date_of_service,o.duration');
    $builder->join('orders o', 'o.partner_id=s.user_id');
    $builder->where('o.date_of_service', $date);
    $builder->where('o.status', 1);
    $order_details = $builder->get()->getResultArray();
    $booked_slots = [];
    $i = 1;
    foreach ($order_details as $key => $order) {
        $slot_name = 'time_slot_' . $i;
        $booked_slots[$slot_name] = [
            'start_time' => $order['starting_time'],
            'end_time' => $order['ending_time'],
            'duration' => $order['duration'],
        ];
    }
    return $booked_slots;
}
function get_time_slot()
{
    $days = [
        'Mon' => 'monday',
        'Tue' => 'tuesday',
        'Wed' => 'wednsday',
        'Thu' => 'thursday',
        'Fri' => 'friday',
        'Sat' => 'staturday',
        'Sun' => 'sunday',
    ];
    $service_id = 16;
    $partner_id = 50;
    $start_times = "5:00";
    $end_time = "6:00";
    $qty = 2;
    $date = date('Y-m-d');
    $day = $days[date('D', strtotime($date))];
    $partner_timing = fetch_details('partner_timings', ['partner_id' => $partner_id, 'day' => $day]);
    $service_details = fetch_details('services', ['id' => $service_id]);
    $service_duration = $service_details[0]['duration'];
    $parnter_opening_time = $partner_timing[0]['opening_time'];
    $parnter_closing_time = $partner_timing[0]['closing_time'];
    $time1 = strtotime($parnter_opening_time);
    $time2 = strtotime($parnter_closing_time);
    $total_hours = round(abs($time2 - $time1) / 3600, 2);
    $time_slotes = [];
    $increament_time = $service_duration;
    $slote_start_time = $parnter_opening_time;
    $i = 0;
    do {
        $slot_name = "time_slot_" . $i;
        $slote_end_time = date('H:i:s', strtotime('+' . $increament_time . ' minutes', strtotime($parnter_opening_time)));
        $time_slotes[$slot_name] = [
            'start_time' => date('H:i:s', strtotime($slote_start_time)),
            'end_time' => $slote_end_time,
        ];
        $increament_time += $service_duration;
        $slote_start_time = $slote_end_time;
        $i++;
    } while ($slote_end_time != $parnter_closing_time);
    return $time_slotes;
}
function check_partner_type($partner_id)
{
    $data = fetch_details('partner_details', ['partner_id' => $partner_id]);
    if (isset($data[0]['type']) && $data[0]['type'] == '1') {
        return 'organization';
    } else {
        return 'single';
    }
}
function check_available_employee($partner_id)
{
    $db = \Config\Database::connect();
    $data = $db->table('orders o')
        ->select('COUNT(o.id) AS order_count,
                    SUM(os.quantity) AS quantity,
                    (COUNT(o.id) * SUM(os.quantity)) AS order_members,
                    pd.number_of_members,
                    (pd.number_of_members -(COUNT(o.id) * SUM(os.quantity))) AS available_members')
        ->join('partner_details pd', 'pd.partner_id = o.partner_id', 'left')
        ->join('order_services os', 'o.id = os.order_id', 'left')
        ->where("o.partner_id = $partner_id AND o.status IN('confirmed', 'rescheduled')")
        ->get()->getResultArray();
    $type = check_partner_type($partner_id);
    if (!empty($type) && $type == 'organization' && !empty($data[0]['order_count']) && $data[0]['available_members'] != 0) {
        $response['error'] = false;
        $response['message'] = "Partner is available";
        $response['data'] = $data;
    } else {
        $response['error'] = true;
        $response['message'] = "Partner is not available";
        $response['data'] = $data;
    }
    return $response;
}
function check_availability_old($service_ids, $date_of_service, $starting_time)
{
    // get day
    $date = strtotime($date_of_service);
    $day = date('l', $date);
    $db = \Config\Database::connect();
    $builder = $db->table('services s');
    $service_id = explode(",", $service_ids);
    // count total duration
    $total_duration = $builder
        ->select('sum(s.duration) as total')
        ->join('partner_details p', 'p.partner_id  = s.user_id')
        ->join('partner_timings pt', 'pt.partner_id  = p.partner_id')
        ->where('pt.day', $day)
        ->where('pt.is_open', 1)
        ->whereIn('s.id', $service_id)
        ->get()->getResultArray();
    // echo $db->lastQuery;
    $duration = $total_duration[0]['total'];
    // get end time
    $start_time = strtotime(date('H:i', strtotime("$starting_time")));
    $end_time = date('H:i', strtotime(date('H:i', $start_time) . ' +' . $duration . ' minute'));
    // get data
    $details = $builder
        ->select('s.id,s.title,s.user_id as partner_id,s.duration,p.company_name,p.type,p.number_of_members,pt.is_open,
            pt.day,pt.opening_time,pt.closing_time,o.starting_time as order_start_time,o.ending_time as order_end_time')
        ->join('partner_details p', 'p.partner_id  = s.user_id')
        ->join('partner_timings pt', 'pt.partner_id  = p.partner_id')
        ->join('orders o', 'o.partner_id  = s.user_id')
        ->whereIn('s.id', $service_id)
        ->where('pt.day', $day)
        ->where('pt.is_open', 1)
        ->where('pt.opening_time <= ', $starting_time)
        ->where('pt.closing_time >= ', $end_time)
        ->groupBy('s.id')
        ->get()->getResultArray();
    $response = '';
    if (isset($details) && !empty($details)) {
        foreach ($details as $detail) {
            // print_r($detail);
            $opening_time = new DateTime($detail['opening_time']);
            $opening_time = $opening_time->format('H:i');
            $closing_time = new DateTime($detail['closing_time']);
            $closing_time = $closing_time->format('H:i');
            if ($detail['type'] == 0) {
                // type of individual
                if (strtotime($date_of_service) > strtotime($opening_time)) {
                    if (strtotime($date_of_service) < strtotime($closing_time)) {
                        $response['error'] = false;
                        $response['message'] = "time slot is available";
                    } else {
                        $response['error'] = true;
                        $response['message'] = "time slot is not available";
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = "Service is not opening for that time";
                }
            } else {
                // type of organization
                $partner_id = $detail['partner_id'];
                $partner_data = check_available_employee($partner_id);
                if (isset($partner_data) && !empty($partner_data)) {
                    if (strtotime($date_of_service) > strtotime($opening_time)) {
                        if (strtotime($date_of_service) < strtotime($closing_time)) {
                            $response['error'] = true;
                            $response['message'] = "Partner is not available";
                        } else {
                            $response['error'] = false;
                            $response['message'] = "Partner is available";
                        }
                    } else {
                        return true;
                        // $response['error'] = true;
                        // $response['message'] = "Partner is not available for that time";
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = "Partner is not available";
                }
            }
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Service not available";
    }
    return $response;
}
function get_recommended_time_slot($duration)
{
    $db = \Config\Database::connect();
    $builder = $db->table('partner_timings pt');
}
function is_bookmarked($user_id, $partner_id)
{
    $db = \Config\Database::connect();
    $builder = $db->table('bookmarks');
    $data = $builder
        ->select('COUNT(id) as total')
        ->where('user_id', $user_id)
        ->where('partner_id', $partner_id)->get()->getResultArray();
    return $data;
}
function delete_bookmark($user_id, $partner_id)
{
    $db = \Config\Database::connect();
    $builder = $db->table('bookmarks');
    $data = $builder->where(['user_id' => $user_id, 'partner_id' => $partner_id])
        ->delete();
    if ($data) {
        return true;
    } else {
        return false;
    }
}
function send_notification($fcmMsg, $registrationIDs_chunks)
{



    $fcmFields = [];
    foreach ($registrationIDs_chunks[0] as $registrationIDs) {
        if ($registrationIDs['platform'] == "android") {
            $fcmFields = array(
                'registration_ids' => array($registrationIDs['fcm_id']), // expects an array of ids
                'priority' => 'high',
                'data' => $fcmMsg,
            );
        } elseif ($registrationIDs['platform'] == "ios") {
            $fcmFields = array(
                'registration_ids' => array($registrationIDs['fcm_id']), // expects an array of ids
                'priority' => 'high',
                'data' => $fcmMsg,
                "notification" => array(
                    "title" => $fcmMsg["title"],
                    "body" => $fcmMsg["body"],
                    "mutable_content" => true,
                    "sound" => $fcmMsg["type"] == "order" || $fcmMsg["type"] == "new_order" ?  "order_sound.aiff" : "default"
                )
            );
        }
        $headers = array(
            'Authorization: key=' . get_settings('api_key_settings', true)['firebase_server_key'],
            'Content-Type: application/json',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmFields));
        $result = curl_exec($ch);
        // return $result;
        curl_close($ch);
    }

    // echo "2334";
    // print_r($fcmFields);
    // die;
}
function get_permission($user_id)
{
    $db = \Config\Database::connect();
    $builder = $db->table('user_permissions');
    $builder->select('role,permissions');
    $builder->where('user_id', $user_id);
    $permissions = $builder->get()->getResultArray();
    // echo "<pre>";
    // print_R($user_id);
    // die;
    if (!empty($permissions[0]['permissions'])) {
        $permissions = json_decode($permissions[0]['permissions'], true);
    } else {
        $permissions = [
            'create' => [
                'order' => 0,
                'subscription' => 1,
                'categories' => 1,
                'sliders' => 1,
                'tax' => 1,
                'services' => 1,
                'promo_code' => 1,
                'featured_section' => 1,
                'partner' => 1,
                'customers' => 0,
                'send_notification' => 1,
                'faq' => 1,
                'settings' => 1,
                'system_user' => 1,
            ],
            'read' => [
                'orders' => 1,
                'subscription' => 1,
                'categories' => 1,
                'sliders' => 1,
                'tax' => 1,
                'services' => 1,
                'promo_code' => 1,
                'featured_section' => 1,
                'partner' => 1,
                'customers' => 1,
                'send_notification' => 1,
                'faq' => 1,
                'settings' => 1,
                'system_user' => 1,
            ],
            'update' => [
                'orders' => 1,
                'subscription' => 1,
                'categories' => 1,
                'sliders' => 1,
                'tax' => 1,
                'services' => 1,
                'promo_code' => 1,
                'featured_section' => 1,
                'partner' => 1,
                'customers' => 1,
                'city' => 1,
                'system_update' => 1,
                'settings' => 1,
                'system_user' => 1,
            ],
            'delete' => [
                'orders' => 1,
                'subscription' => 1,
                'categories' => 1,
                'offers' => 1,
                'sliders' => 1,
                'tax' => 1,
                'services' => 1,
                'promo_code' => 1,
                'featured_section' => 1,
                'partner' => 1,
                'customers' => 0, // Note: I added a default value here, adjust as needed
                'city' => 1,
                'faq' => 1,
                'send_notification' => 1,
                'support_tickets' => 1,
                'system_user' => 1,
            ],
        ];
    }
    return $permissions;
}
function is_permitted($user_id, $type_of_permission, $permit)
{
    $db = \Config\Database::connect();
    $builder = $db->table('user_permissions');
    $builder->select('role,permissions');
    $builder->where('user_id', $user_id);
    $permissions = $builder->get()->getResultArray();
    if ($permissions[0]['role'] == "1") {
        return true;
    } else {
        $permissions = json_decode($permissions[0]['permissions'], true);
        foreach ($permissions as $key => $val) {
            if ($key == $type_of_permission) { //to check if the given type of permission is valid
                if ($val[$permit] == "yes" || $val[$permit] == "1" || $val[$permit] == 1) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }
}
function get_category($category_id = '')
{
    if (!empty($category_id)) {
        $where['parent_id'] = $category_id;
    }
    $where['status'] = '1';
    $categories = fetch_details('categories', $where, ['name', 'id']);
    return $categories;
}
// returns booked slot on provider on given date between partner's openning hours
function booked_timings($partner_id, $date_of_service)
{
    $db = \config\Database::connect();
    $table = $db->table('orders o');
    $day = date('l', strtotime($date_of_service));
    $response = $table->select('o.starting_time,o.ending_time')
        ->join('order_services os', 'o.id = os.order_id', 'left')
        ->join('services s', 'os.service_id = s.id', 'left')
        ->join('partner_timings pt', 'pt.partner_id = o.partner_id')
        ->where(['o.partner_id' => $partner_id, 'o.date_of_service' => $date_of_service, 'pt.day' => $day, 'pt.is_open' => '1'])
        ->whereIn('o.status', ['confirmed', 'rescheduled', 'awaiting'])
        ->groupBy('o.id')
        ->orderBy('o.starting_time')
        ->get()->getResultArray();
    return $response;
}
function check_availability($partner_id, $booking_date, $time)
{
    $today = date('Y-m-d');
    if ($booking_date < $today) {
        $response['error'] = true;
        $response['message'] = "please select upcoming date!";
        return $response;
    }
    $db = \config\Database::connect();
    $table = $db->table('orders a');
    $day = date('l', strtotime($booking_date));
    $timings = getTimingOfDay($partner_id, $day);
    if (isset($timings) && !empty($timings)) {
        $opening_time = $timings['opening_time'];
        $closing_time = $timings['closing_time'];
        //     $booked_slots = $table->select('a.starting_time AS free_before, (a.starting_time + INTERVAL a.duration HOUR_MINUTE) AS free_after')
        //         ->where("NOT EXISTS ( SELECT 1 FROM orders b WHERE b.starting_time
        // BETWEEN (a.starting_time + INTERVAL a.duration HOUR_MINUTE) AND
        // (a.starting_time + INTERVAL a.duration HOUR_MINUTE) + INTERVAL 15 SECOND - INTERVAL 1 MICROSECOND) AND
        // (a.starting_time + INTERVAL a.duration HOUR_MINUTE) BETWEEN '$booking_date $opening_time' AND '$booking_date $closing_time' AND date_of_service = '$booking_date' AND status IN('awaiting','pending','confirmed','rescheduled')
        //  AND partner_id = '50'")
        //         ->groupBy('id')->orderBy('starting_time', 'ASC')->get()->getResultArray();
        $booked_slots = $table->select('a.starting_time AS free_before, (a.starting_time + INTERVAL a.duration HOUR_MINUTE) AS free_after')
            ->where("NOT EXISTS (
            SELECT 1
            FROM orders b
            WHERE b.starting_time BETWEEN (a.starting_time + INTERVAL a.duration HOUR_MINUTE)
                AND (a.starting_time + INTERVAL a.duration HOUR_MINUTE) + INTERVAL 15 SECOND - INTERVAL 1 MICROSECOND
        )")
            ->where("(a.starting_time + INTERVAL a.duration HOUR_MINUTE) BETWEEN '$booking_date $opening_time' AND '$booking_date $closing_time'")
            ->where('date_of_service', $booking_date)
            ->whereIn('status', ['awaiting', 'pending', 'confirmed', 'rescheduled'])
            ->where('partner_id', '50')
            ->groupBy('id')
            ->orderBy('starting_time', 'ASC')
            ->get()
            ->getResultArray();
        if (isset($booked_slots) && !empty($booked_slots)) {
            if ($time >= $opening_time && $time < $closing_time) {
                foreach ($booked_slots as $key => $val) {
                    $from = strtotime($val['free_before']);
                    $till = strtotime($val['free_after']);
                    $t = isBetween($from, $till, strtotime($time)); // if doesnt work remove strtotime
                    if (isset($t) && $t == true) {
                        $response['error'] = true;
                        $response['message'] = "provider is busy at this time select another slot";
                    } else {
                        if ($time >= $closing_time) {
                            $response['error'] = true;
                            $response['message'] = "Provider is closed at this time";
                        } else {
                            $response['error'] = false;
                            $response['message'] = "slot is available at this time";
                        }
                    }
                }
                return $response;
            } else {
                $response['error'] = true;
                $response['message'] = "Provider is closed at this time";
                return $response;
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Provider is closed at this time";
            return $response;
        }
    } else {
        $response['error'] = true;
        $response['message'] = "provider is closed on this day";
        return $response;
    }
}
function isBetween($from, $till, $input)
{
    if ($input >= $from && $input <= $till) {
        return true;
    } else {
        return false;
    }
}
function getTimingOfDay($partner_id, $day)
{
    $timings = fetch_details('partner_timings', ['partner_id' => $partner_id, 'day' => $day], ['opening_time', 'closing_time', 'is_open']);
    // if (!empty($timings) && isset($timings[0]) ) {
    if (!empty($timings) && isset($timings[0]) && $timings[0]['is_open'] == '1') {
        return $timings[0];
    } else {
        return false;
    }
}
// function get_available_slots($partner_id, $booking_date, $required_duration = null, $next_day_order = null)
// {
//     if (!empty($next_day_order)) {
//         $today = date('Y-m-d');
//         if ($booking_date < $today) {
//             $response['error'] = true;
//             $response['message'] = "please select upcoming date!";
//             return $response;
//         }
//         $db = \config\Database::connect();
//         $day = date('l', strtotime($booking_date));
//         $timings = getTimingOfDay($partner_id, $day);
//         if (isset($timings) && !empty($timings)) {
//             $opening_time = $timings['opening_time'];
//             $closing_time = $timings['closing_time'];
//             $booked_slots = booked_timings($partner_id, $booking_date);
//             $interval = 30 * 60;
//             $start_time = strtotime($next_day_order);
//             $current_time = time();
//             $end_time = strtotime($closing_time);
//             $count = count($booked_slots);
//             $current_date = date('Y-m-d');
//             $available_slots = [];
//             $busy_slots = [];
//             $available_flags = [];
//             if ($start_time >= $end_time) {
//                 $response['error'] = false;
//                 $response['available_slots'] = [];
//                 $response['busy_slots'] = [];
//                 return $response;
//             }
//             //if booked slot is not empty means that day no odrer no found
//             if (isset($booked_slots) && !empty($booked_slots)) {
//                 //here suggested time is created in gap of 30 minutes
//                 while ($start_time < $end_time) {
//                     $array_of_time[] = date("H:i:s", $start_time);
//                     $start_time += $interval;
//                 }
//                 $count_suggestion_slots = count($array_of_time);
//                 //loop on total booked slots
//                 for ($i = 0; $i < $count; $i++) {
//                     //loop on suggested time slots
//                     for ($j = 0; $j < $count_suggestion_slots; $j++) {
//                         if (strtotime($array_of_time[$j]) < strtotime($booked_slots[$i]['starting_time']) || strtotime($array_of_time[$j]) >= strtotime($booked_slots[$i]['ending_time'])) {
//                             if (!in_array($array_of_time[$j], $available_slots)) {
//                                 if (strtotime($array_of_time[$j]) > $current_time || strtotime($booking_date) != strtotime($current_date)) {
//                                     $available_slots[] = $array_of_time[$j];
//                                 } else {
//                                     if (!in_array($array_of_time[$j], $busy_slots)) {
//                                         $busy_slots[] = $array_of_time[$j];
//                                     }
//                                 }
//                             } else {
//                             }
//                         } else {
//                             if (!in_array($array_of_time[$j], $busy_slots)) {
//                                 $busy_slots[] = $array_of_time[$j];
//                             }
//                         }
//                     }
//                     $count_busy_slots = count($busy_slots);
//                     for ($k = 0; $k < $count_busy_slots; $k++) {
//                         if (($key = array_search($busy_slots[$k], $available_slots)) !== false) {
//                             unset($available_slots[$key]);
//                         }
//                     }
//                 }
//                 //here to continue the index of available_slots
//                 $available_slots = array_values($available_slots);
//                 // //-------------------------------------for next day order start--------------------------------------------------
//                 $before_end_time = date('H:i:s', strtotime($closing_time) - (30 * 60));
//                 $remaining_duration = $required_duration - 30;
//                 $next_day_date = date('Y-m-d', strtotime($booking_date . ' +1 day'));
//                 $next_day = date('l', strtotime($next_day_date));
//                 $next_day_timings = getTimingOfDay($partner_id, $next_day);
//                 $next_day_booked_slots = booked_timings($partner_id, $next_day_date);
//                 if (!empty($next_day_booked_slots)) {
//                     $next_day_opening_time = $next_day_timings['opening_time'];
//                     $next_day_ending_time = $next_day_timings['closing_time'];
//                     $next_start_time = strtotime($next_day_opening_time);
//                     $time = $next_day_opening_time;
//                     $ending_time_for_next_day_slot = date('H:i:s', strtotime($time . ' +' . $remaining_duration . ' minutes'));
//                     $next_start_time = strtotime($next_day_opening_time);
//                     $next_day_available_slots = [];
//                     $next_day_busy_slots = [];
//                     $next_day_array_of_time = [];
//                     while ($next_start_time <= strtotime($ending_time_for_next_day_slot)) {
//                         $next_day_array_of_time[] = date("H:i:s", $next_start_time);
//                         $next_start_time += $interval;
//                     }
//                     //check that main order date's last slot is available or not and remaining duration is grater than 30 min
//                     if (in_array($before_end_time, $available_slots) && $required_duration > 30) {
//                         //creating time slot for next day   
//                         //check that next day suggested slots are available or not
//                         //if next day has  orders
//                         if (count($next_day_booked_slots) > 0) {
//                             for ($i = 0; $i < count($next_day_booked_slots); $i++) {
//                                 //loop on suggested time slots
//                                 for ($j = 0; $j < count($next_day_array_of_time); $j++) {
//                                     //if suggested time slot is less than booked slot starting time or suggested time slot is greater than booked time slot starting time
//                                     if (strtotime($next_day_array_of_time[$j]) < strtotime($next_day_booked_slots[$i]['starting_time']) || strtotime($next_day_array_of_time[$j]) >= strtotime($next_day_booked_slots[$i]['ending_time'])) {
//                                         //check if suggested time slot is not  in array of avaialble slot
//                                         if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
//                                             // echo "suggested slot is not in avaiable slot<br/>";
//                                             $next_day_available_slots[] = $next_day_array_of_time[$j];
//                                         } else {
//                                             if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
//                                                 $next_day_busy_slots[] = $next_day_array_of_time[$j];
//                                             }
//                                         }
//                                     } else {
//                                         if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
//                                             $next_day_busy_slots[] = $next_day_array_of_time[$j];
//                                         }
//                                     }
//                                 }
//                                 $count_next_busy_slots = count($next_day_busy_slots);
//                                 for ($k = 0; $k < $count_next_busy_slots; $k++) {
//                                     if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
//                                         unset($next_day_available_slots[$key]);
//                                     }
//                                 }
//                             }
//                         } else {
//                             //loop on suggested time slots
//                             for ($j = 0; $j < count($next_day_array_of_time); $j++) {
//                                 //check if suggested time slot is not  in array of avaialble slot
//                                 if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
//                                     //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
//                                     if (strtotime($next_day_date) != strtotime($current_date)) {
//                                         $next_day_available_slots[] = $next_day_array_of_time[$j];
//                                     } else {
//                                         if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
//                                             $next_day_busy_slots[] = $next_day_array_of_time[$j];
//                                         }
//                                     }
//                                 }
//                             }
//                             $count_next_busy_slots = count($next_day_busy_slots);
//                             for ($k = 0; $k < $count_next_busy_slots; $k++) {
//                                 if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
//                                     unset($next_day_available_slots[$key]);
//                                 }
//                             }
//                         }
//                         $available_slots = array_values($available_slots);
//                         if (count($next_day_available_slots) < count($next_day_array_of_time)) {
//                             for ($k = 0; $k < count($available_slots); $k++) {
//                                 if (($key = array_search($before_end_time, $available_slots)) !== false) {
//                                     if (count($next_day_available_slots) < count($next_day_array_of_time)) {
//                                         unset($available_slots[$key]);
//                                         $busy_slots[] = $before_end_time;
//                                     }
//                                 }
//                             }
//                         }
//                     }
//                 }
//                 // //-------------------------------------for next day order end--------------------------------------------------
//                 $response['error'] = false;
//                 $response['available_slots'] = $available_slots;
//                 $response['busy_slots'] = $busy_slots;
//                 return $response;
//             } else {
//                 while ($start_time < $end_time) {
//                     $array_of_time[] = date("H:i:s", $start_time);
//                     $start_time += $interval;
//                 }
//                 if (strtotime($booking_date) == strtotime($current_date)) {
//                     foreach ($array_of_time as $row) {
//                         if (strtotime($row) < $current_time) {
//                             if (($key = array_search($row, $array_of_time)) !== false) {
//                                 unset($array_of_time[$key]);
//                                 $busy_slots[] = $row;
//                             }
//                         }
//                     }
//                 }
//                 //--------------------- next day start -----------------------
//                 $before_end_time = date('H:i:s', strtotime($closing_time) - (30 * 60));
//                 $remaining_duration = $required_duration - 30;
//                 $next_day_date = date('Y-m-d', strtotime($booking_date . ' +1 day'));
//                 $next_day = date('l', strtotime($next_day_date));
//                 $next_day_timings = getTimingOfDay($partner_id, $next_day);
//                 $next_day_booked_slots = booked_timings($partner_id, $next_day_date);
//                 if (!empty($next_day_booked_slots)) {
//                     $next_day_opening_time = $next_day_timings['opening_time'];
//                     $next_day_ending_time = $next_day_timings['closing_time'];
//                     $next_start_time = strtotime($next_day_opening_time);
//                     $time = $next_day_opening_time;
//                     $ending_time_for_next_day_slot = date('H:i:s', strtotime($time . ' +' . $remaining_duration . ' minutes'));
//                     $next_start_time = strtotime($next_day_opening_time);
//                     $next_day_available_slots = [];
//                     $next_day_busy_slots = [];
//                     $next_day_array_of_time = [];
//                     //creating time slot for next day
//                     while ($next_start_time <= strtotime($ending_time_for_next_day_slot)) {
//                         $next_day_array_of_time[] = date("H:i:s", $next_start_time);
//                         $next_start_time += $interval;
//                     }
//                     //check that main order date's last slot is available or not and remaining duration is grater than 30 min
//                     if (in_array($before_end_time, $array_of_time) && $required_duration > 30) {
//                         //if next day has  orders
//                         if (count($next_day_booked_slots) > 0) {
//                             for ($i = 0; $i < count($next_day_booked_slots); $i++) {
//                                 //loop on suggested time slots
//                                 for ($j = 0; $j < count($next_day_array_of_time); $j++) {
//                                     //if suggested time slot is less than booked slot starting time or suggested time slot is greater than booked time slot starting time
//                                     if (strtotime($next_day_array_of_time[$j]) < strtotime($next_day_booked_slots[$i]['starting_time']) || strtotime($next_day_array_of_time[$j]) >= strtotime($next_day_booked_slots[$i]['ending_time'])) {
//                                         //check if suggested time slot is not  in array of avaialble slot
//                                         if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
//                                             $next_day_available_slots[] = $next_day_array_of_time[$j];
//                                         } else {
//                                             if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
//                                                 $next_day_busy_slots[] = $next_day_array_of_time[$j];
//                                             }
//                                         }
//                                     } else {
//                                         if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
//                                             $next_day_busy_slots[] = $next_day_array_of_time[$j];
//                                         }
//                                     }
//                                 }
//                                 $count_next_busy_slots = count($next_day_busy_slots);
//                                 for ($k = 0; $k < $count_next_busy_slots; $k++) {
//                                     if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
//                                         unset($next_day_available_slots[$key]);
//                                     }
//                                 }
//                             }
//                         } else {
//                             //loop on suggested time slots
//                             for ($j = 0; $j < count($next_day_array_of_time); $j++) {
//                                 //check if suggested time slot is not  in array of avaialble slot
//                                 if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
//                                     //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
//                                     if (strtotime($next_day_date) != strtotime($current_date)) {
//                                         $next_day_available_slots[] = $next_day_array_of_time[$j];
//                                     } else {
//                                         if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
//                                             $next_day_busy_slots[] = $next_day_array_of_time[$j];
//                                         }
//                                     }
//                                 }
//                             }
//                             $count_next_busy_slots = count($next_day_busy_slots);
//                             for ($k = 0; $k < $count_next_busy_slots; $k++) {
//                                 if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
//                                     unset($next_day_available_slots[$key]);
//                                 }
//                             }
//                         }
//                         $array_of_time = array_values($array_of_time);
//                         if (count($next_day_available_slots) < count($next_day_array_of_time)) {
//                             for ($k = 0; $k < count($array_of_time); $k++) {
//                                 if (($key = array_search($before_end_time, $array_of_time)) !== false) {
//                                     if (count($next_day_available_slots) < count($next_day_array_of_time)) {
//                                         unset($array_of_time[$key]);
//                                         $busy_slots[] = $before_end_time;
//                                     }
//                                 }
//                             }
//                         }
//                     }
//                 }
//                 $response['error'] = false;
//                 $response['available_slots'] = $array_of_time;
//                 $response['busy_slots'] = $busy_slots;
//                 return $response;
//             }
//         } else {
//             $response['error'] = true;
//             $response['message'] = "provider is closed on this day";
//             return $response;
//         }
//     }
//     //=====================================================================================================
//     //=====================================================================================================
//     //=====================================================================================================
//     $today = date('Y-m-d');
//     if ($booking_date < $today) {
//         $response['error'] = true;
//         $response['message'] = "please select upcoming date!";
//         return $response;
//     }
//     $db = \config\Database::connect();
//     $day = date('l', strtotime($booking_date));
//     $timings = getTimingOfDay($partner_id, $day);
//     if (isset($timings) && !empty($timings)) {
//         $opening_time = $timings['opening_time'];
//         $closing_time = $timings['closing_time'];
//         $booked_slots = booked_timings($partner_id, $booking_date);
//         $interval = 30 * 60;
//         $start_time = strtotime($opening_time);
//         $current_time = time();
//         $end_time = strtotime($closing_time);
//         $count = count($booked_slots);
//         $current_date = date('Y-m-d');
//         $available_slots = [];
//         $busy_slots = [];
//         //if booked slot is not empty means that day no odrer no found
//         if (isset($booked_slots) && !empty($booked_slots)) {
//             //here suggested time is created in gap of 30 minutes
//             while ($start_time < $end_time) {
//                 $array_of_time[] = date("H:i:s", $start_time);
//                 $start_time += $interval;
//             }
//             $count_suggestion_slots = count($array_of_time);
//             //loop on total booked slots
//             for ($i = 0; $i < $count; $i++) {
//                 //loop on suggested time slots
//                 for ($j = 0; $j < $count_suggestion_slots; $j++) {
//                     //if suggested time slot is less than booked slot starting time or suggested time slot is greater than booked time slot starting time
//                     if (strtotime($array_of_time[$j]) < strtotime($booked_slots[$i]['starting_time']) || strtotime($array_of_time[$j]) >= strtotime($booked_slots[$i]['ending_time'])) {
//                         // echo "----------------------------------.<br/>";
//                         // echo "suggested time-slot ".$array_of_time[$j]."<br/>";
//                         // echo "booked time-slot ".$booked_slots[$i]['starting_time']."<br/>";
//                         //check if suggested time slot is not  in array of avaialble slot
//                         if (!in_array($array_of_time[$j], $available_slots)) {
//                             //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
//                             if (strtotime($array_of_time[$j]) > $current_time || strtotime($booking_date) != strtotime($current_date)) {
//                                 // echo $array_of_time[$j]." added to available time slot <br/>";
//                                 $available_slots[] = $array_of_time[$j];
//                                 // $available_flags[] = $array_of_time[$j];
//                             } else {
//                                 // echo $array_of_time[$j]." added to busy time slot<br/>";
//                                 if (!in_array($array_of_time[$j], $busy_slots)) {
//                                     $busy_slots[] = $array_of_time[$j];
//                                 }
//                             }
//                             // die;
//                         } else {
//                         }
//                     } else {
//                         if (!in_array($array_of_time[$j], $busy_slots)) {
//                             $busy_slots[] = $array_of_time[$j];
//                         }
//                     }
//                 }
//                 $count_busy_slots = count($busy_slots);
//                 for ($k = 0; $k < $count_busy_slots; $k++) {
//                     if (($key = array_search($busy_slots[$k], $available_slots)) !== false) {
//                         unset($available_slots[$key]);
//                     }
//                 }
//             }
//             //here to continue the index of available_slots
//             $available_slots = array_values($available_slots);
//             // creating chunks of countinuos time slots from available time slots
//             $all_continous_slot = [];
//             $continous_slot_number = 0;
//             for ($i = 0; $i < count($available_slots) - 1; $i++) {
//                 // echo "***************************************.<br/>";
//                 // echo "current  avaialble slot--" . $available_slots[$i];
//                 // echo "<br/>";
//                 //here we add 30 minutes to  available time slot
//                 $next_expected_time_slot = date("H:i:s", strtotime('+30 minutes', strtotime($available_slots[$i])));
//                 // echo "next expected time slot--" . $next_expected_time_slot;
//                 // echo "<br/>";
//                 // echo ($available_slots[$i + 1] == $next_expected_time_slot) ?  'true' : 'false';
//                 // echo "</br>";
//                 //here we check avaialable slot + 1  means if avaialbe slot is 9:00 then available slot +1 is 9:30 is same as expected time slot if yes then add to continue slot
//                 if (($available_slots[$i + 1] == $next_expected_time_slot)) {
//                     $all_continous_slot[$continous_slot_number][] = $available_slots[$i];
//                     if (count($available_slots) - 1 == $i + 1) {
//                         $all_continous_slot[$continous_slot_number][] = $available_slots[$i + 1];
//                     }
//                 } else {
//                     $all_continous_slot[$continous_slot_number][] = $available_slots[$i];
//                     $continous_slot_number++;
//                 }
//             }
//             //-------------------------------------for next day order start--------------------------------------------------
//             $before_end_time = date('H:i:s', strtotime($closing_time) - (30 * 60));
//             $remaining_duration = $required_duration - 30;
//             $next_day_date = date('Y-m-d', strtotime($booking_date . ' +1 day'));
//             $next_day = date('l', strtotime($next_day_date));
//             $next_day_timings = getTimingOfDay($partner_id, $next_day);
//             $next_day_booked_slots = booked_timings($partner_id, $next_day_date);
//             if (!empty($next_day_booked_slots)) {
//                 $next_day_opening_time = $next_day_timings['opening_time'];
//                 $next_day_ending_time = $next_day_timings['closing_time'];
//                 $next_start_time = strtotime($next_day_opening_time);
//                 $time = $next_day_opening_time;
//                 $next_end_time = strtotime($next_day_ending_time);
//                 $ending_time_for_next_day_slot = date('H:i:s', $next_end_time);
//                 $next_start_time = strtotime($next_day_opening_time);
//                 $next_day_available_slots = [];
//                 $next_day_busy_slots = [];
//                 $next_day_array_of_time = [];
//                 while ($next_start_time <= strtotime($ending_time_for_next_day_slot)) {
//                     $next_day_array_of_time[] = date("H:i:s", $next_start_time);
//                     $next_start_time += $interval;
//                 }
//                 //check that main order date's last slot is available or not and remaining duration is grater than 30 min
//                 if (in_array($before_end_time, $available_slots) && $required_duration > 30) {
//                     //creating time slot for next day   
//                     //check that next day suggested slots are available or not
//                     //if next day has  orders
//                     if (count($next_day_booked_slots) > 0) {
//                         for ($i = 0; $i < count($next_day_booked_slots); $i++) {
//                             //loop on suggested time slots
//                             for ($j = 0; $j < count($next_day_array_of_time); $j++) {
//                                 //if suggested time slot is less than booked slot starting time or suggested time slot is greater than booked time slot starting time
//                                 if (strtotime($next_day_array_of_time[$j]) < strtotime($next_day_booked_slots[$i]['starting_time']) || strtotime($next_day_array_of_time[$j]) >= strtotime($next_day_booked_slots[$i]['ending_time'])) {
//                                     //check if suggested time slot is not  in array of avaialble slot
//                                     if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
//                                         // echo "suggested slot is not in avaiable slot<br/>";
//                                         $next_day_available_slots[] = $next_day_array_of_time[$j];
//                                     } else {
//                                         if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
//                                             $next_day_busy_slots[] = $next_day_array_of_time[$j];
//                                         }
//                                     }
//                                 } else {
//                                     if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
//                                         $next_day_busy_slots[] = $next_day_array_of_time[$j];
//                                     }
//                                 }
//                             }
//                             $count_next_busy_slots = count($next_day_busy_slots);
//                             for ($k = 0; $k < $count_next_busy_slots; $k++) {
//                                 if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
//                                     unset($next_day_available_slots[$key]);
//                                 }
//                             }
//                         }
//                     } else {
//                         //loop on suggested time slots
//                         for ($j = 0; $j < count($next_day_array_of_time); $j++) {
//                             //check if suggested time slot is not  in array of avaialble slot
//                             if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
//                                 //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
//                                 if (strtotime($next_day_date) != strtotime($current_date)) {
//                                     $next_day_available_slots[] = $next_day_array_of_time[$j];
//                                 } else {
//                                     if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
//                                         $next_day_busy_slots[] = $next_day_array_of_time[$j];
//                                     }
//                                 }
//                             }
//                         }
//                         $count_next_busy_slots = count($next_day_busy_slots);
//                         for ($k = 0; $k < $count_next_busy_slots; $k++) {
//                             if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
//                                 unset($next_day_available_slots[$key]);
//                             }
//                         }
//                     }
//                     $available_slots = array_values($available_slots);
//                     if (count($next_day_available_slots) < count($next_day_array_of_time)) {
//                         for ($k = 0; $k < count($available_slots); $k++) {
//                             if (($key = array_search($before_end_time, $available_slots)) !== false) {
//                                 if (count($next_day_available_slots) < count($next_day_array_of_time)) {
//                                     unset($available_slots[$key]);
//                                     $busy_slots[] = $before_end_time;
//                                 }
//                             }
//                         }
//                     }
//                 }
//             } else {
//                 $next_day_opening_time = $next_day_timings['opening_time'];
//                 $next_day_ending_time = $next_day_timings['closing_time'];
//                 $next_start_time = strtotime($next_day_opening_time);
//                 $time = $next_day_opening_time;
//                 $ending_time_for_next_day_slot = date('H:i:s', strtotime($time . ' +' . $remaining_duration . ' minutes'));
//                 $next_start_time = strtotime($next_day_opening_time);
//                 $next_day_available_slots = [];
//                 $next_day_busy_slots = [];
//                 $next_day_array_of_time = [];
//                 while ($next_start_time <= strtotime($ending_time_for_next_day_slot)) {
//                     $next_day_array_of_time[] = date("H:i:s", $next_start_time);
//                     $next_start_time += $interval;
//                 }
//                 //loop on suggested time slots
//                 for ($j = 0; $j < count($next_day_array_of_time); $j++) {
//                     //check if suggested time slot is not  in array of avaialble slot
//                     if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
//                         //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
//                         if (strtotime($next_day_date) != strtotime($current_date)) {
//                             $next_day_available_slots[] = $next_day_array_of_time[$j];
//                         } else {
//                             if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
//                                 $next_day_busy_slots[] = $next_day_array_of_time[$j];
//                             }
//                         }
//                     }
//                 }
//                 $count_next_busy_slots = count($next_day_busy_slots);
//                 for ($k = 0; $k < $count_next_busy_slots; $k++) {
//                     if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
//                         unset($next_day_available_slots[$key]);
//                     }
//                 }
//             }
//             $available_slots = array_values($available_slots);
//             if (count($next_day_available_slots) < count($next_day_array_of_time)) {
//                 for ($k = 0; $k < count($available_slots); $k++) {
//                     if (($key = array_search($before_end_time, $available_slots)) !== false) {
//                         if (count($next_day_available_slots) < count($next_day_array_of_time)) {
//                             unset($available_slots[$key]);
//                             $busy_slots[] = $before_end_time;
//                         }
//                     }
//                 }
//             }
//             $ignore_last_slot = false;
//             foreach ($all_continous_slot as $index => $row) {
//                 if ($index == count($all_continous_slot) - 1) {
//                     if ((date("H:i:s", strtotime($row[count($row) - 1]))) == (date("H:i:s", strtotime($closing_time . " -30 minutes")))) {
//                         $ignore_last_slot = true;
//                     }
//                 }
//                 $continous_slot_doration = sizeof($row) * 30;
//                 if ($continous_slot_doration < $required_duration) {
//                     if ($ignore_last_slot == false) {
//                         foreach ($row as $child_slots) {
//                             if (($key = array_search($child_slots, $available_slots)) !== false) {
//                                 unset($available_slots[$key]);
//                                 $busy_slots[] = $child_slots;
//                             }
//                         }
//                     }
//                 } else {
//                     if ($ignore_last_slot == false) {
//                         $required_slots = ceil($required_duration / 30);
//                         $last_available_slot = count($row) - $required_slots + 1;
//                         for ($i = count($row) - 1; $i >= $last_available_slot; $i--) {
//                             if (($key = array_search($row[$i], $available_slots)) !== false) {
//                                 unset($available_slots[$key]);
//                                 $busy_slots[] = $row[$i];
//                             }
//                         }
//                     }
//                 }
//             }
//             //-------------------------------------for next day order end--------------------------------------------------
//             //---------------------------------  START ----------------------------------------------------------
//             // helper('date');
//             $db = \Config\Database::connect();
//             // Fetch order data from the database for the requested partner
//             $builder = $db->table('orders');
//             $builder->select('starting_time, ending_time, date_of_service');
//             $builder->where('partner_id', $partner_id);
//             $builder->where('date_of_service', $booking_date);
//             $builder->whereIn('status', ['awaiting', 'pending', 'confirmed', 'rescheduled']);
//             $booked_slots = $builder->get()->getResultArray();
//             $duration = $required_duration; // Duration of each service in minutes
//             foreach ($available_slots as $slot) {
//                 $slot_time = strtotime($slot);
//                 $slot_end_time = strtotime("+$duration minutes", $slot_time);
//                 $is_booked = false;
//                 foreach ($booked_slots as $booked_slot) {
//                     $booked_start_time = strtotime($booked_slot['starting_time']);
//                     $booked_end_time = strtotime($booked_slot['ending_time']);
//                     if (($slot_time >= $booked_start_time && $slot_time < $booked_end_time) ||
//                         ($slot_end_time > $booked_start_time && $slot_end_time <= $booked_end_time)
//                     ) {
//                         $is_booked = true;
//                         break;
//                     }
//                 }
//                 if ($is_booked) {
//                     $busy_slots[] = $slot;
//                     $index = array_search($slot, $available_slots);
//                     if ($index !== false) {
//                         unset($available_slots[$index]);
//                     }
//                 }
//             }
//             // //------------------------------------------------------- END------------------------------------------------------------------
//             $response['error'] = false;
//             $response['available_slots'] = $available_slots;
//             $response['busy_slots'] = $busy_slots;
//             return $response;
//         } else {
//             while ($start_time < $end_time) {
//                 $array_of_time[] = date("H:i:s", $start_time);
//                 $start_time += $interval;
//             }
//             if (strtotime($booking_date) == strtotime($current_date)) {
//                 foreach ($array_of_time as $row) {
//                     if (strtotime($row) < $current_time) {
//                         if (($key = array_search($row, $array_of_time)) !== false) {
//                             unset($array_of_time[$key]);
//                             $busy_slots[] = $row;
//                         }
//                     }
//                 }
//             }
//             //--------------------- next day start -----------------------
//             $before_end_time = date('H:i:s', strtotime($closing_time) - (30 * 60));
//             $remaining_duration = $required_duration - 30;
//             $next_day_date = date('Y-m-d', strtotime($booking_date . ' +1 day'));
//             $next_day = date('l', strtotime($next_day_date));
//             $next_day_timings = getTimingOfDay($partner_id, $next_day);
//             $next_day_booked_slots = booked_timings($partner_id, $next_day_date);
//             if (!empty($next_day_booked_slots)) {
//                 $next_day_opening_time = $next_day_timings['opening_time'];
//                 $next_day_ending_time = $next_day_timings['closing_time'];
//                 $next_start_time = strtotime($next_day_opening_time);
//                 $time = $next_day_opening_time;
//                 $ending_time_for_next_day_slot = date('H:i:s', strtotime($time . ' +' . $remaining_duration . ' minutes'));
//                 $next_start_time = strtotime($next_day_opening_time);
//                 $next_day_available_slots = [];
//                 $next_day_busy_slots = [];
//                 $next_day_array_of_time = [];
//                 //creating time slot for next day
//                 while ($next_start_time <= strtotime($ending_time_for_next_day_slot)) {
//                     $next_day_array_of_time[] = date("H:i:s", $next_start_time);
//                     $next_start_time += $interval;
//                 }
//                 //check that main order date's last slot is available or not and remaining duration is grater than 30 min
//                 if (in_array($before_end_time, $array_of_time) && $required_duration > 30) {
//                     //if next day has  orders
//                     if (count($next_day_booked_slots) > 0) {
//                         for ($i = 0; $i < count($next_day_booked_slots); $i++) {
//                             //loop on suggested time slots
//                             for ($j = 0; $j < count($next_day_array_of_time); $j++) {
//                                 //if suggested time slot is less than booked slot starting time or suggested time slot is greater than booked time slot starting time
//                                 if (strtotime($next_day_array_of_time[$j]) < strtotime($next_day_booked_slots[$i]['starting_time']) || strtotime($next_day_array_of_time[$j]) >= strtotime($next_day_booked_slots[$i]['ending_time'])) {
//                                     //check if suggested time slot is not  in array of avaialble slot
//                                     if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
//                                         $next_day_available_slots[] = $next_day_array_of_time[$j];
//                                     } else {
//                                         if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
//                                             $next_day_busy_slots[] = $next_day_array_of_time[$j];
//                                         }
//                                     }
//                                 } else {
//                                     if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
//                                         $next_day_busy_slots[] = $next_day_array_of_time[$j];
//                                     }
//                                 }
//                             }
//                             $count_next_busy_slots = count($next_day_busy_slots);
//                             for ($k = 0; $k < $count_next_busy_slots; $k++) {
//                                 if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
//                                     unset($next_day_available_slots[$key]);
//                                 }
//                             }
//                         }
//                     } else {
//                         //loop on suggested time slots
//                         for ($j = 0; $j < count($next_day_array_of_time); $j++) {
//                             //check if suggested time slot is not  in array of avaialble slot
//                             if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
//                                 //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
//                                 if (strtotime($next_day_date) != strtotime($current_date)) {
//                                     $next_day_available_slots[] = $next_day_array_of_time[$j];
//                                 } else {
//                                     if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
//                                         $next_day_busy_slots[] = $next_day_array_of_time[$j];
//                                     }
//                                 }
//                             }
//                         }
//                         $count_next_busy_slots = count($next_day_busy_slots);
//                         for ($k = 0; $k < $count_next_busy_slots; $k++) {
//                             if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
//                                 unset($next_day_available_slots[$key]);
//                             }
//                         }
//                     }
//                     $array_of_time = array_values($array_of_time);
//                     if (count($next_day_available_slots) < count($next_day_array_of_time)) {
//                         for ($k = 0; $k < count($array_of_time); $k++) {
//                             if (($key = array_search($before_end_time, $array_of_time)) !== false) {
//                                 if (count($next_day_available_slots) < count($next_day_array_of_time)) {
//                                     unset($array_of_time[$key]);
//                                     $busy_slots[] = $before_end_time;
//                                 }
//                             }
//                         }
//                     }
//                 }
//             }
//             $response['error'] = false;
//             $response['available_slots'] = $array_of_time;
//             $response['busy_slots'] = $busy_slots;
//             return $response;
//         }
//     } else {
//         $response['error'] = true;
//         $response['message'] = "provider is closed on this day";
//         return $response;
//     }
// }
function get_available_slots($partner_id, $booking_date, $required_duration = null, $next_day_order = null)
{
    $timezone = get_settings('general_settings', true);
    date_default_timezone_set($timezone['system_timezone']); // Added user timezone
    if (!empty($next_day_order)) {
        $today = date('Y-m-d');
        if ($booking_date < $today) {
            $response['error'] = true;
            $response['message'] = "please select upcoming date!";
            return $response;
        }
        $db = \config\Database::connect();
        $day = date('l', strtotime($booking_date));
        $timings = getTimingOfDay($partner_id, $day);
        if (isset($timings) && !empty($timings)) {
            $opening_time = $timings['opening_time'];
            $closing_time = $timings['closing_time'];
            $booked_slots = booked_timings($partner_id, $booking_date);
            $interval = 30 * 60;
            $start_time = strtotime($next_day_order);
            $current_time = time();
            $end_time = strtotime($closing_time);
            $count = count($booked_slots);
            $current_date = date('Y-m-d');
            $available_slots = [];
            $busy_slots = [];
            //if booked slot is not empty means that day no odrer no found
            while ($start_time < $end_time) {
                $array_of_time[] = date("H:i:s", $start_time);
                $start_time += $interval;
            }
            if (isset($booked_slots) && !empty($booked_slots)) {
                //here suggested time is created in gap of 30 minutes
                $count_suggestion_slots = count($array_of_time);
                //loop on total booked slots
                for ($i = 0; $i < $count; $i++) {
                    //loop on suggested time slots
                    for ($j = 0; $j < $count_suggestion_slots; $j++) {
                        //if suggested time slot is less than booked slot starting time or suggested time slot is greater than booked time slot starting time
                        if (strtotime($array_of_time[$j]) < strtotime($booked_slots[$i]['starting_time']) || strtotime($array_of_time[$j]) >= strtotime($booked_slots[$i]['ending_time'])) {
                            //check if suggested time slot is not  in array of avaialble slot
                            if (!in_array($array_of_time[$j], $available_slots)) {
                                //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
                                if (strtotime($array_of_time[$j]) > $current_time || strtotime($booking_date) != strtotime($current_date)) {
                                    // echo $array_of_time[$j]." added to available time slot <br/>";
                                    $available_slots[] = $array_of_time[$j];
                                } else {
                                    // echo $array_of_time[$j]." added to busy time slot11<br/>";
                                    if (!in_array($array_of_time[$j], $busy_slots)) {
                                        $busy_slots[] = $array_of_time[$j];
                                    }
                                }
                                // die;
                            } else {
                            }
                        } else {
                            //  echo $array_of_time[$j]." added to busy time slot22<br/>";
                            if (!in_array($array_of_time[$j], $busy_slots)) {
                                $busy_slots[] = $array_of_time[$j];
                            }
                        }
                    }
                    $count_busy_slots = count($busy_slots);
                    for ($k = 0; $k < $count_busy_slots; $k++) {
                        if (($key = array_search($busy_slots[$k], $available_slots)) !== false) {
                            unset($available_slots[$key]);
                        }
                    }
                }
                $available_slots = array_values($available_slots);
                $ignore_last_slot = false;
                $all_continous_slot = calculate_continuous_slots($available_slots);
                $next_day_slots = get_next_days_slots($closing_time, $booking_date, $partner_id, $required_duration, $current_date);
                // if(!empty($next_day_available_slots)){
                $next_day_available_slots = $next_day_slots['continous_available_slots'];
                $required_slots = ceil($required_duration / 30);
                if (isset($next_day_available_slots[0][0]) && $next_day_available_slots[0][0] === $opening_time) {
                    // echo "if1";
                    $next_day_fullfilled_slots = count($next_day_available_slots[0]);
                    if ($next_day_fullfilled_slots >= $required_slots) {
                        // echo "if2";
                        $ignore_last_slot = true;
                        $required_duration_for_last_slot = $next_day_fullfilled_slots * 30;
                    } else {
                        // echo "else";
                        $expected_remaining_duration_for_today = $required_duration - ($next_day_fullfilled_slots * 30);
                        // echo $expected_remaining_duration_for_today."<br>";
                        $last_contious_slot_of_current_day = $all_continous_slot[count($all_continous_slot) - 1];
                        // print_R($last_contious_slot_of_current_day);
                        $last_element_of_current_day = $last_contious_slot_of_current_day[count($last_contious_slot_of_current_day) - 1];
                        $last_element_of_current_day = date("H:i:s", strtotime('+30 minutes', strtotime($last_element_of_current_day)));
                        if ($last_element_of_current_day == $closing_time) {
                            // echo "if3";
                            $required_duration_for_last_slot = count($last_contious_slot_of_current_day) * 30;
                            if ($expected_remaining_duration_for_today < $required_duration_for_last_slot) {
                                // echo "if5";
                                $ignore_last_slot = true;
                            }
                        } else {
                            // echo "else2";
                            //Don't do anything here
                        }
                    }
                } else {
                    // echo "else3";
                    //Don't do anything here as the next function will handle the last available slot and all
                }
                //Disable all the chunks that are not required enough
                $continous_slot_doration = 0; // Initialize the variable before the loop
                foreach ($all_continous_slot as $index => $row) {
                    $ignore_last_slot_local = false;
                    if ($index === (count($all_continous_slot) - 1)) {
                        $ignore_last_slot_local = ($ignore_last_slot == false) ? false : true;
                    }
                    if ($ignore_last_slot_local) {
                        $continous_slot_doration = sizeof($row) * 30;
                        if ($continous_slot_doration < $required_duration) {
                            foreach ($row as $child_slots) {
                                if (($key = array_search($child_slots, $available_slots)) !== false) {
                                    unset($available_slots[$key]);
                                    $busy_slots[] = $child_slots;
                                }
                            }
                        }
                    }
                }
                $available_slots = array_values($available_slots);
                $all_continous_slot = calculate_continuous_slots($available_slots);
                $required_slots = ceil($required_duration / 30);
                foreach ($all_continous_slot as $index => $row) {
                    if ($index == count($all_continous_slot) - 1 && $ignore_last_slot == true) {
                        $required_slots = $required_slots - $next_day_fullfilled_slots + 1;
                    }
                    $last_available_slot  = (count($row) - $required_slots) + 1;
                    for ($i = count($row) - 1; $i > $last_available_slot; $i--) {
                        if ($i >= 0 && (($key = array_search($row[$i], $available_slots)) !== false)) {
                            unset($available_slots[$key]);
                            $busy_slots[] = $row[$i];
                        }
                    }
                }
                //---------------------------------  START ----------------------------------------------------------
                // Fetch order data from the database for the requested partner
                $builder = $db->table('orders');
                $builder->select('starting_time, ending_time, date_of_service');
                $builder->where('partner_id', $partner_id);
                $builder->where('date_of_service', $booking_date);
                $builder->whereIn('status', ['awaiting', 'pending', 'confirmed', 'rescheduled']);
                $booked_slots = $builder->get()->getResultArray();
                $duration = $required_duration; // Duration of each service in minutes
                foreach ($available_slots as $slot) {
                    $slot_time = strtotime($slot);
                    $slot_end_time = strtotime("+$duration minutes", $slot_time);
                    $is_booked = false;
                    foreach ($booked_slots as $booked_slot) {
                        $booked_start_time = strtotime($booked_slot['starting_time']);
                        $booked_end_time = strtotime($booked_slot['ending_time']);
                        if (($slot_time >= $booked_start_time && $slot_time < $booked_end_time) ||
                            ($slot_end_time > $booked_start_time && $slot_end_time <= $booked_end_time)
                        ) {
                            $is_booked = true;
                            break;
                        }
                    }
                    if ($is_booked) {
                        $busy_slots[] = $slot;
                        $index = array_search($slot, $available_slots);
                        if ($index !== false) {
                            unset($available_slots[$index]);
                        }
                    }
                }
                // print_R($available_slots);
                // die;
                // //------------------------------------------------------- END------------------------------------------------------------------
                $response['error'] = false;
                $response['available_slots'] = $available_slots;
                $response['busy_slots'] = $busy_slots;
                return $response;
            } else {
                if (strtotime($booking_date) == strtotime($current_date)) {
                    foreach ($array_of_time as $row) {
                        if (strtotime($row) < $current_time) {
                            if (($key = array_search($row, $array_of_time)) !== false) {
                                unset($array_of_time[$key]);
                                $busy_slots[] = $row;
                            }
                        }
                    }
                }
                //here to continue the index of available_slots
                $array_of_time = array_values($array_of_time);
                $ignore_last_slot = false;
                $all_continous_slot = calculate_continuous_slots($array_of_time);
                $next_day_slots = get_next_days_slots($closing_time, $booking_date, $partner_id, $required_duration, $current_date);
                // if(!empty($next_day_available_slots)){
                $next_day_available_slots = $next_day_slots['continous_available_slots'];
                $required_slots = ceil($required_duration / 30);
                if (isset($next_day_available_slots[0][0]) && $next_day_available_slots[0][0] === $opening_time) {
                    // echo "if1";
                    $next_day_fullfilled_slots = count($next_day_available_slots[0]);
                    if ($next_day_fullfilled_slots >= $required_slots) {
                        // echo "if2";
                        $ignore_last_slot = true;
                        $required_duration_for_last_slot = $next_day_fullfilled_slots * 30;
                    } else {
                        // echo "else";
                        $expected_remaining_duration_for_today = $required_duration - ($next_day_fullfilled_slots * 30);
                        // echo $expected_remaining_duration_for_today."<br>";
                        $last_contious_slot_of_current_day = $all_continous_slot[count($all_continous_slot) - 1];
                        // print_R($last_contious_slot_of_current_day);
                        $last_element_of_current_day = $last_contious_slot_of_current_day[count($last_contious_slot_of_current_day) - 1];
                        $last_element_of_current_day = date("H:i:s", strtotime('+30 minutes', strtotime($last_element_of_current_day)));
                        if ($last_element_of_current_day == $closing_time) {
                            // echo "if3";
                            $required_duration_for_last_slot = count($last_contious_slot_of_current_day) * 30;
                            if ($expected_remaining_duration_for_today < $required_duration_for_last_slot) {
                                // echo "if5";
                                $ignore_last_slot = true;
                            }
                        } else {
                            // echo "else2";
                            //Don't do anything here
                        }
                    }
                } else {
                    // echo "else3";
                    //Don't do anything here as the next function will handle the last available slot and all
                }
                //Disable all the chunks that are not required enough
                $continous_slot_doration = 0; // Initialize the variable before the loop
                foreach ($all_continous_slot as $index => $row) {
                    $ignore_last_slot_local = false;
                    if ($index === (count($all_continous_slot) - 1)) {
                        $ignore_last_slot_local = ($ignore_last_slot == false) ? false : true;
                    }
                    if ($ignore_last_slot_local) {
                        $continous_slot_doration = sizeof($row) * 30;
                        if ($continous_slot_doration < $required_duration) {
                            foreach ($row as $child_slots) {
                                if (($key = array_search($child_slots, $array_of_time)) !== false) {
                                    unset($array_of_time[$key]);
                                    $busy_slots[] = $child_slots;
                                }
                            }
                        }
                    }
                }
                $array_of_time = array_values($array_of_time);
                $all_continous_slot = calculate_continuous_slots($array_of_time);
                $required_slots = ceil($required_duration / 30);
                foreach ($all_continous_slot as $index => $row) {
                    if ($index == count($all_continous_slot) - 1 && $ignore_last_slot == true) {
                        $required_slots = $required_slots - $next_day_fullfilled_slots + 1;
                    }
                    $last_available_slot  = (count($row) - $required_slots) + 1;
                    for ($i = count($row) - 1; $i > $last_available_slot; $i--) {
                        if ($i >= 0 && (($key = array_search($row[$i], $array_of_time)) !== false)) {
                            unset($array_of_time[$key]);
                            $busy_slots[] = $row[$i];
                        }
                    }
                }
            }
            $response['error'] = false;
            $response['available_slots'] = $array_of_time;
            $response['busy_slots'] = $busy_slots;
            return $response;
        } else {
            $response['error'] = true;
            $response['message'] = "provider is closed on this day";
            return $response;
        }
    }
    //=====================================================================================================
    //=====================================================================================================
    //=====================================================================================================
    $today = date('Y-m-d');
    if ($booking_date < $today) {
        $response['error'] = true;
        $response['message'] = "please select upcoming date!";
        return $response;
    }
    $db = \config\Database::connect();
    $day = date('l', strtotime($booking_date));
    $timings = getTimingOfDay($partner_id, $day);
    if (isset($timings) && !empty($timings)) {
        $opening_time = $timings['opening_time'];
        $closing_time = $timings['closing_time'];
        $booked_slots = booked_timings($partner_id, $booking_date);
        $interval = 30 * 60;
        $start_time = strtotime($opening_time);
        $current_time = time();
        $end_time = strtotime($closing_time);
        $count = count($booked_slots);
        $current_date = date('Y-m-d');
        $available_slots = [];
        $busy_slots = [];
        //if booked slot is not empty means that day no odrer no found
        while ($start_time < $end_time) {
            $array_of_time[] = date("H:i:s", $start_time);
            $start_time += $interval;
        }
        if (isset($booked_slots) && !empty($booked_slots)) {
            //here suggested time is created in gap of 30 minutes
            $count_suggestion_slots = count($array_of_time);
            //loop on total booked slots
            for ($i = 0; $i < $count; $i++) {
                //loop on suggested time slots
                for ($j = 0; $j < $count_suggestion_slots; $j++) {
                    //if suggested time slot is less than booked slot starting time or suggested time slot is greater than booked time slot starting time
                    if (strtotime($array_of_time[$j]) < strtotime($booked_slots[$i]['starting_time']) || strtotime($array_of_time[$j]) >= strtotime($booked_slots[$i]['ending_time'])) {
                        //check if suggested time slot is not  in array of avaialble slot
                        if (!in_array($array_of_time[$j], $available_slots)) {
                            //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
                            if (strtotime($array_of_time[$j]) > $current_time || strtotime($booking_date) != strtotime($current_date)) {
                                // echo $array_of_time[$j]." added to available time slot <br/>";
                                $available_slots[] = $array_of_time[$j];
                            } else {
                                // echo $array_of_time[$j]." added to busy time slot11<br/>";
                                if (!in_array($array_of_time[$j], $busy_slots)) {
                                    $busy_slots[] = $array_of_time[$j];
                                }
                            }
                            // die;
                        } else {
                        }
                    } else {
                        //  echo $array_of_time[$j]." added to busy time slot22<br/>";
                        if (!in_array($array_of_time[$j], $busy_slots)) {
                            $busy_slots[] = $array_of_time[$j];
                        }
                    }
                }
                $count_busy_slots = count($busy_slots);
                for ($k = 0; $k < $count_busy_slots; $k++) {
                    if (($key = array_search($busy_slots[$k], $available_slots)) !== false) {
                        unset($available_slots[$key]);
                    }
                }
            }
            //here to continue the index of available_slots
            $available_slots = array_values($available_slots);
            $ignore_last_slot = false;
            $all_continous_slot = calculate_continuous_slots($available_slots);
            $next_day_slots = get_next_days_slots($closing_time, $booking_date, $partner_id, $required_duration, $current_date);
            // if(!empty($next_day_available_slots)){
            $next_day_available_slots = $next_day_slots['continous_available_slots'];
            $required_slots = ceil($required_duration / 30);
            if (isset($next_day_available_slots[0][0]) && $next_day_available_slots[0][0] === $opening_time) {
                // echo "if1";
                $next_day_fullfilled_slots = count($next_day_available_slots[0]);
                if ($next_day_fullfilled_slots >= $required_slots) {
                    // echo "if2";
                    $ignore_last_slot = true;
                    $required_duration_for_last_slot = $next_day_fullfilled_slots * 30;
                } else {
                    // echo "else";
                    $expected_remaining_duration_for_today = $required_duration - ($next_day_fullfilled_slots * 30);
                    // echo $expected_remaining_duration_for_today."<br>";
                    $last_contious_slot_of_current_day = $all_continous_slot[count($all_continous_slot) - 1];
                    // print_R($last_contious_slot_of_current_day);
                    $last_element_of_current_day = $last_contious_slot_of_current_day[count($last_contious_slot_of_current_day) - 1];
                    $last_element_of_current_day = date("H:i:s", strtotime('+30 minutes', strtotime($last_element_of_current_day)));
                    if ($last_element_of_current_day == $closing_time) {
                        // echo "if3";
                        $required_duration_for_last_slot = count($last_contious_slot_of_current_day) * 30;
                        if ($expected_remaining_duration_for_today < $required_duration_for_last_slot) {
                            // echo "if5";
                            $ignore_last_slot = true;
                        }
                    } else {
                        // echo "else2";
                        //Don't do anything here
                    }
                }
            } else {
                // echo "else3";
                //Don't do anything here as the next function will handle the last available slot and all
            }
            //Disable all the chunks that are not required enough
            $continous_slot_doration = 0; // Initialize the variable before the loop
            foreach ($all_continous_slot as $index => $row) {
                $ignore_last_slot_local = false;
                if ($index === (count($all_continous_slot) - 1)) {
                    $ignore_last_slot_local = ($ignore_last_slot == false) ? false : true;
                }
                if ($ignore_last_slot_local) {
                    $continous_slot_doration = sizeof($row) * 30;
                    if ($continous_slot_doration < $required_duration) {
                        foreach ($row as $child_slots) {
                            if (($key = array_search($child_slots, $available_slots)) !== false) {
                                unset($available_slots[$key]);
                                $busy_slots[] = $child_slots;
                            }
                        }
                    }
                }
            }
            $available_slots = array_values($available_slots);
            $all_continous_slot = calculate_continuous_slots($available_slots);
            $required_slots = ceil($required_duration / 30);
            foreach ($all_continous_slot as $index => $row) {
                if ($index == count($all_continous_slot) - 1 && $ignore_last_slot == true) {
                    $required_slots = $required_slots - $next_day_fullfilled_slots + 1;
                }
                $last_available_slot  = (count($row) - $required_slots) + 1;
                for ($i = count($row) - 1; $i > $last_available_slot; $i--) {
                    if ($i >= 0 && (($key = array_search($row[$i], $available_slots)) !== false)) {
                        unset($available_slots[$key]);
                        $busy_slots[] = $row[$i];
                    }
                }
            }
            //---------------------------------  START ----------------------------------------------------------
            // Fetch order data from the database for the requested partner
            $builder = $db->table('orders');
            $builder->select('starting_time, ending_time, date_of_service');
            $builder->where('partner_id', $partner_id);
            $builder->where('date_of_service', $booking_date);
            $builder->whereIn('status', ['awaiting', 'pending', 'confirmed', 'rescheduled']);
            $booked_slots = $builder->get()->getResultArray();
            $duration = $required_duration; // Duration of each service in minutes
            foreach ($available_slots as $slot) {
                $slot_time = strtotime($slot);
                $slot_end_time = strtotime("+$duration minutes", $slot_time);
                $is_booked = false;
                foreach ($booked_slots as $booked_slot) {
                    $booked_start_time = strtotime($booked_slot['starting_time']);
                    $booked_end_time = strtotime($booked_slot['ending_time']);
                    if (($slot_time >= $booked_start_time && $slot_time < $booked_end_time) ||
                        ($slot_end_time > $booked_start_time && $slot_end_time <= $booked_end_time)
                    ) {
                        $is_booked = true;
                        break;
                    }
                }
                if ($is_booked) {
                    $busy_slots[] = $slot;
                    $index = array_search($slot, $available_slots);
                    if ($index !== false) {
                        unset($available_slots[$index]);
                    }
                }
            }
            // //------------------------------------------------------- END------------------------------------------------------------------
            $response['error'] = false;
            $response['available_slots'] = $available_slots;
            $response['busy_slots'] = $busy_slots;
            return $response;
        } else {
            // print_r($array_of_time);
            if (strtotime($booking_date) == strtotime($current_date)) {
                foreach ($array_of_time as $row) {
                    if (strtotime($row) < $current_time) {
                        if (($key = array_search($row, $array_of_time)) !== false) {
                            unset($array_of_time[$key]);
                            $busy_slots[] = $row;
                        }
                    }
                }
            }
            //here to continue the index of available_slots
            $array_of_time = array_values($array_of_time);
            $ignore_last_slot = false;
            $all_continous_slot = calculate_continuous_slots($array_of_time);
            if (!empty($array_of_time)) {
                $next_day_slots = get_next_days_slots($closing_time, $booking_date, $partner_id, $required_duration, $current_date);
                $next_day_available_slots = $next_day_slots['continous_available_slots'];
                $required_slots = ceil($required_duration / 30);
                if (isset($next_day_available_slots[0][0]) && $next_day_available_slots[0][0] === $opening_time) {
                    // echo "if1";
                    $next_day_fullfilled_slots = count($next_day_available_slots[0]);
                    if ($next_day_fullfilled_slots >= $required_slots) {
                        // echo "if2";
                        $ignore_last_slot = true;
                        $required_duration_for_last_slot = $next_day_fullfilled_slots * 30;
                    } else {
                        // echo "else";
                        $expected_remaining_duration_for_today = $required_duration - ($next_day_fullfilled_slots * 30);
                        // echo $expected_remaining_duration_for_today."<br>";
                        $last_contious_slot_of_current_day = $all_continous_slot[count($all_continous_slot) - 1];
                        // print_R($last_contious_slot_of_current_day);
                        $last_element_of_current_day = $last_contious_slot_of_current_day[count($last_contious_slot_of_current_day) - 1];
                        $last_element_of_current_day = date("H:i:s", strtotime('+30 minutes', strtotime($last_element_of_current_day)));
                        if ($last_element_of_current_day == $closing_time) {
                            // echo "if3";
                            $required_duration_for_last_slot = count($last_contious_slot_of_current_day) * 30;
                            if ($expected_remaining_duration_for_today < $required_duration_for_last_slot) {
                                // echo "if5";
                                $ignore_last_slot = true;
                            }
                        } else {
                            // echo "else2";
                            //Don't do anything here
                        }
                    }
                } else {
                    // echo "else3";
                    //Don't do anything here as the next function will handle the last available slot and all
                }
            }
            //Disable all the chunks that are not required enough
            $continous_slot_doration = 0; // Initialize the variable before the loop
            foreach ($all_continous_slot as $index => $row) {
                $ignore_last_slot_local = false;
                if ($index === (count($all_continous_slot) - 1)) {
                    $ignore_last_slot_local = ($ignore_last_slot == false) ? false : true;
                }
                if ($ignore_last_slot_local) {
                    $continous_slot_doration = sizeof($row) * 30;
                    if ($continous_slot_doration < $required_duration) {
                        foreach ($row as $child_slots) {
                            if (($key = array_search($child_slots, $array_of_time)) !== false) {
                                unset($array_of_time[$key]);
                                $busy_slots[] = $child_slots;
                            }
                        }
                    }
                }
            }
            $array_of_time = array_values($array_of_time);
            $all_continous_slot = calculate_continuous_slots($array_of_time);
            $required_slots = ceil($required_duration / 30);
            foreach ($all_continous_slot as $index => $row) {
                if ($index == count($all_continous_slot) - 1 && $ignore_last_slot == true) {
                    $required_slots = $required_slots - $next_day_fullfilled_slots + 1;
                }
                $last_available_slot  = ((count($row)) - $required_slots) + 1;
                for ($i = count($row) - 1; $i > $last_available_slot; $i--) {
                    if ($i >= 0 && (($key = array_search($row[$i], $array_of_time)) !== false)) {
                        unset($array_of_time[$key]);
                        $busy_slots[] = $row[$i];
                    }
                }
            }
        }
        $response['error'] = false;
        $response['available_slots'] = $array_of_time;
        $response['busy_slots'] = $busy_slots;
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "provider is closed on this day";
        return $response;
    }
}
function get_available_slots_without_processing($partner_id, $booking_date, $required_duration = null, $next_day_order = null)
{
    $today = date('Y-m-d');
    if ($booking_date < $today) {
        $response['error'] = true;
        $response['message'] = "please select upcoming date!";
        return $response;
    }
    $db = \config\Database::connect();
    $day = date('l', strtotime($booking_date));
    $busy_slots = [];
    $timings = getTimingOfDay($partner_id, $day);
    if (isset($timings) && !empty($timings)) {
        $opening_time = $timings['opening_time'];
        $closing_time = $timings['closing_time'];
        $booked_slots = booked_timings($partner_id, $booking_date);
        $interval = 30 * 60;
        $start_time = strtotime($next_day_order);
        $current_time = time();
        $end_time = strtotime($closing_time);
        $count = count($booked_slots);
        $current_date = date('Y-m-d');
        $available_slots = [];
        $array_of_time = [];
        //here suggested time is created in gap of 30 minutes
        while ($start_time <= $end_time) {
            $array_of_time[] = date("H:i:s", $start_time);
            $start_time += $interval;
        }
        // addedd  start
        if (strtotime($booking_date) == strtotime($current_date)) {
            foreach ($array_of_time as $row) {
                if (strtotime($row) < $current_time) {
                    if (($key = array_search($row, $array_of_time)) !== false) {
                        unset($array_of_time[$key]);
                        $busy_slots[] = $row;
                    }
                }
            }
        }
        //addedd end
        //here to continue the index of available_slots
        $array_of_time = array_values($array_of_time);
        if (isset($booked_slots) && !empty($booked_slots)) {
            //here suggested time is created in gap of 30 minutes
            $count_suggestion_slots = count($array_of_time);
            //loop on total booked slots
            for ($i = 0; $i < $count; $i++) {
                //loop on suggested time slots
                for ($j = 0; $j < $count_suggestion_slots; $j++) {
                    //if suggested time slot is less than booked slot starting time or suggested time slot is greater than booked time slot starting time
                    if (strtotime($array_of_time[$j]) < strtotime($booked_slots[$i]['starting_time']) || strtotime($array_of_time[$j]) >= strtotime($booked_slots[$i]['ending_time'])) {
                        if (!in_array($array_of_time[$j], $available_slots)) {
                            //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
                            if (strtotime($array_of_time[$j]) > $current_time || strtotime($booking_date) != strtotime($current_date)) {
                                // echo $array_of_time[$j]." added to available time slot <br/>";
                                $available_slots[] = $array_of_time[$j];
                            } else {
                                // echo $array_of_time[$j]." added to busy time slot11<br/>";
                                if (!in_array($array_of_time[$j], $busy_slots)) {
                                    $busy_slots[] = $array_of_time[$j];
                                }
                            }
                            // die;
                        } else {
                        }
                    } else {
                        //  echo $array_of_time[$j]." added to busy time slot22<br/>";
                        if (!in_array($array_of_time[$j], $busy_slots)) {
                            $busy_slots[] = $array_of_time[$j];
                        }
                    }
                }
                $count_busy_slots = count($busy_slots);
                for ($k = 0; $k < $count_busy_slots; $k++) {
                    if (($key = array_search($busy_slots[$k], $available_slots)) !== false) {
                        unset($available_slots[$key]);
                    }
                }
            }
        }
        $all_continous_slot = calculate_continuous_slots($array_of_time);
        $response['error'] = false;
        $response['available_slots'] = $all_continous_slot;
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "provider is closed on this day";
        return $response;
    }
}
function get_service($service_id)
{
    if ($service_id != null) {
        return false;
    }
    $service = fetch_details('services', ['id' => $service_id]);
    if ($service != null && !empty($service)) {
        return response('Found data', false, $service);
    } else {
        return response('No Data Found', false, []);
    }
}
function has_ordered($user_id, $service_id)
{
    $db = \config\Database::connect();
    $services = fetch_details('services', ['id' => $service_id]);
    if (empty($services)) {
        $response['error'] = true;
        $response['message'] = "No Service Found";
        return $response;
    }
    $builder = $db
        ->table('orders o')
        ->select(' o.id,o.user_id,os.service_id')
        ->join('order_services os', 'os.order_id = o.id')
        ->where('user_id', $user_id)
        ->where('o.status', 'completed')
        ->where('os.service_id', $service_id)->get()->getResultArray();
    if (!empty($builder)) {
        $response['error'] = false;
        $response['message'] = "Has ordered";
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "Can not rate service  without Placing orders";
        return $response;
    }
}
function has_rated($user_id, $rate_id)
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('services_ratings sr')
        ->select('sr.*')
        ->where('sr.id', $rate_id)
        ->where('user_id', $user_id);
    $old_data = $builder->get()->getResultArray();
    if (!empty($old_data)) {
        $response['error'] = false;
        $response['message'] = "Found Rating";
        $response['data'] = $old_data;
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "No Rating Found";
        return $response;
    }
}
function get_ratings($user_id)
{
    $db = \config\Database::connect();
    //    echo "<pre>";
    $builder = $db
        ->table('services s')
        ->select("
                COUNT(sr.rating) as total_ratings,
                SUM( CASE WHEN sr.rating = ceil(5) THEN 1 ELSE 0 END) as rating_5,
                SUM( CASE WHEN sr.rating = ceil(4) THEN 1 ELSE 0 END) as rating_4,
                SUM( CASE WHEN sr.rating = ceil(3) THEN 1 ELSE 0 END) as rating_3,
                SUM( CASE WHEN sr.rating = ceil(2) THEN 1 ELSE 0 END) as rating_2,
                SUM( CASE WHEN sr.rating = ceil(1) THEN 1 ELSE 0 END) as rating_1
            ")
        ->join('services_ratings sr', 'sr.service_id = s.id')
        ->where('s.user_id', $user_id)
        ->join('users u', 'u.id = sr.user_id')
        ->get()->getResultArray();
    return $builder;
}
function update_ratings($service_id, $rate)
{
    $db = \config\Database::connect();
    $service_data = fetch_details('services', ['id' => $service_id]);
    if (!empty($service_data)) {
        $user_id = $service_data[0]['user_id'];
    }
    $partner_data = fetch_details('partner_details', ['partner_id' => $user_id]);
    if (!empty($partner_data)) {
        $partner_id = $partner_data[0]['partner_id'];
    }
    $service_ids = fetch_details('services', ['user_id' => $user_id], ['id']);
    $ids = [];
    foreach ($service_ids as $si) {
        array_push($ids, $si['id']);
    }
    $data = $db
        ->table('services_ratings sr')
        ->select(
            'count(sr.rating) as number_of_ratings,
                sum(sr.rating) as total_rating,
                (sum(sr.rating) /count(sr.rating)) as avg_rating'
        )
        ->whereIn('service_id', $ids)
        ->get()->getResultArray();
    if (!empty($data)) {
        $data[0]['number_of_ratings'] = $data[0]['number_of_ratings'];
        $data[0]['total_rating'] = $data[0]['total_rating'];
        $data[0]['avg_rating'] = $data[0]['total_rating'] / $data[0]['number_of_ratings'];
        $updated_data = update_details(['ratings' => $data[0]['avg_rating'], 'number_of_ratings' => $data[0]['number_of_ratings']], ['partner_id' => $partner_id], 'partner_details');
        $updated_data = update_details(['rating' => $data[0]['avg_rating'], 'number_of_ratings' => $data[0]['number_of_ratings']], ['id' => $service_id], 'services');
    } else {
        $updated_data = update_details(
            ['ratings' => $rate, 'number_of_ratings' => 1],
            ['partner_id' => $partner_id],
            'partner_details'
        );
        $updated_data = update_details(['rating' => $rate, 'number_of_ratings' => 1], ['id' => $service_id], 'services');
    }
    if ($updated_data != "") {
        return $response['error'] = false;
    } else {
        return $response['error'] = true;
    }
}
function rating_images($rating_id, $from_app = false)
{
    $rating_data = fetch_details('services_ratings', ['id' => $rating_id]);
    $d = ($from_app == false) ? 'for web' : 'for app';
    // print_r($d);
    // die();
    if (!empty($rating_data)) {
        $rating_images = json_decode($rating_data[0]['images'], true);
        // print_r($rating_images);
        $images_restored = [];
        foreach ($rating_images as $ri) {
            if ($from_app == false) {
                $image = '<a  href="' . base_url($ri) . '" data-lightbox="image-1"><img height="80px" class="rounded" src="' . base_url($ri) . '" alt=""></a>';
                array_push($images_restored, $image);
            } else {
                array_push($images_restored, base_url($ri));
            }
        }
    }
    return $images_restored;
}
function fetch_promo_codes($partner_id)
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('promo_codes pc')
        ->select('pc.*')
        ->where('pc.partner_id', $partner_id);
    $data = $builder->get()->getResultArray();
    return $data;
}
function fetch_number_of_orders($partner_id)
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('orders o')
        ->select('COUNT(o.id) as total_number_of_orders')
        ->where('o.partner_id', $partner_id);
    $data = $builder->get()->getResultArray();
    // return $data;
    if (!empty($data)) {
        return $data[0];
    } else {
        return $data;
    }
}
function is_favorite($user_id, $partner_id)
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('bookmarks b')
        ->select('b.*')
        ->where('b.user_id', $user_id)
        ->where('b.partner_id', $partner_id);
    $data = $builder->get()->getResultArray();
    if (!empty($data)) {
        return true;
    } else {
        return false;
    }
}
function favorite_list($user_id)
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('bookmarks b')
        ->select('b.partner_id')
        ->where('b.user_id', $user_id);
    $data = $builder->get()->getResultArray();
    $partner_ids = [];
    if (!empty($data)) {
        foreach ($data as $dt) {
            array_push($partner_ids, $dt['partner_id']);
        }
        return $partner_ids;
    } else {
        return false;
    }
}
function distance_finder($lat1, $lon1, $lat2, $lon2, $unit)
{
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
        return 0;
    } else {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);
        if ($unit == "K" || $unit == "KILOMETERS") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
}
function in_cart_qty($service_id, $user_id)
{
    $data = fetch_details('cart', ['user_id' => $user_id, 'service_id' => $service_id], ['qty']);
    $quantity = (!empty($data)) ? $data[0]['qty'] : '0';
    return $quantity;
}
function resize_image($image, $new_image, $thumbnail, $width = 300, $height = 300)
{
    if (file_exists(FCPATH . $image)) {
        if (!is_dir(base_url($thumbnail))) {
            mkdir(base_url($thumbnail), 0775, true);
        }
        \Config\Services::image('gd')
            ->withFile(FCPATH . $image)
            ->resize($width, $height, true, 'auto')
            ->save(FCPATH . $new_image);
        $response['error'] = false;
        $response['message'] = "File resizes successfully";
        return $response;
    } else {
        $response['error'] = true;
        $response['message'] = "File does not exist";
        return $response;
    }
}
function provider_total_earning_chart($partner_id = '')
{
    $amount = fetch_details('orders', ['partner_id' => $partner_id, 'is_commission_settled' => '0'], ['sum(final_total) as total']);
    $db = \config\Database::connect();
    $builder = $db
        ->table('orders')
        ->select('sum(final_total) as total')
        ->select('SUM(final_total) AS total_sale,DATE_FORMAT(created_at,"%b") AS month_name')
        ->where('partner_id', $partner_id)
        ->where('status', 'completed');
    // ->where('is_commission_settled', '0');
    $data = $builder->groupBy('created_at')->get()->getResultArray();
    $tempRow = array();
    $row1 = array();
    foreach ($data as $key => $row) {
        $tempRow = $row['total'];
        $row1[] = $tempRow;
    }
    // $provider_earning_total=array($data);
    if (isset($amount) && !empty($amount)) {
        //  commission will be in % here
        $admin_commission_percentage = get_admin_commision($partner_id);
        $admin_commission_amount = intval($admin_commission_percentage) / 100;
        $total = $amount[0]['total'];
        $commision = intval($total) * $admin_commission_amount;
        $unsettled_amount = $total - $commision;
    }
    $month_wise_sales['total_sale'] = array_map('intval', array_column($data, 'total_sale'));
    $month_wise_sales['month_name'] = array_column($data, 'month_name');
    $sales = $month_wise_sales;
    return $sales;
}
function provider_already_withdraw_chart($partner_id = '')
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('payment_request')
        ->select('sum(amount) as total')
        ->select('SUM(amount) AS total_withdraw,DATE_FORMAT(created_at,"%b") AS month_name')
        ->where('status', '1')
        ->where('user_id', $partner_id);
    $data = $builder->groupBy('created_at')->get()->getResultArray();
    $tempRow = array();
    $row1 = array();
    foreach ($data as $key => $row) {
        $tempRow = $row['total'];
        $row1[] = $tempRow;
    }
    $month_wise_sales['total_withdraw'] = array_map('intval', array_column($data, 'total_withdraw'));
    $month_wise_sales['month_name'] = array_column($data, 'month_name');
    $total_withdraw = $month_wise_sales;
    return $total_withdraw;
}
// return $row1;
function provider_pending_withdraw_chart($partner_id = '')
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('payment_request')
        ->select('sum(amount) as total')
        ->select('SUM(amount) AS pending_withdraw,DATE_FORMAT(created_at,"%b") AS month_name')
        ->where('status', '0')
        ->where('user_id', $partner_id);
    $data = $builder->groupBy('created_at')->get()->getResultArray();
    $month_wise_sales['pending_withdraw'] = array_map('intval', array_column($data, 'pending_withdraw'));
    $month_wise_sales['month_name'] = array_column($data, 'month_name');
    $pending_withdraw = $month_wise_sales;
    return $pending_withdraw;
    // return $row1;
}
function provider_withdraw_chart($partner_id = '')
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('payment_request')
        ->select('sum(amount) as total')
        ->select('SUM(amount) AS withdraw_request,DATE_FORMAT(created_at,"%b") AS month_name')
        ->where('user_id', $partner_id);
    $data = $builder->groupBy('created_at')->get()->getResultArray();
    $month_wise_sales['withdraw_request'] = array_map('intval', array_column($data, 'withdraw_request'));
    $month_wise_sales['month_name'] = array_column($data, 'month_name');
    $withdraw_request = $month_wise_sales;
    return $withdraw_request;
}
function income_revenue($partner_id = '')
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('payment_request')
        ->select('sum(amount) as total')
        ->select('SUM(amount) AS income_revenue,DATE_FORMAT(date_of_service,"%b") AS month_name')
        ->where('status', '0');
    $data = $builder->groupBy('MONTH(created_at), YEAR(created_at)')->get()->getResultArray();
    $month_wise_sales['income_revenue'] = array_map('intval', array_column($data, 'income_revenue'));
    $month_wise_sales['month_name'] = array_column($data, 'month_name');
    $income_revenue = $month_wise_sales;
    return $income_revenue;
    // return $row1;
}
function admin_income_revenue($partner_id = '')
{
    $db = \config\Database::connect();
    $builder =  $db
        ->table('orders o')
        ->select('
            o.final_total, pd.admin_commission,pd.*,
            SUM(( o.final_total * pd.admin_commission)/100) as total_admin_earning,DATE_FORMAT(o.date_of_service,"%b") AS month_name
        ')
        ->where('o.status', 'completed')
        ->join('partner_details pd', 'pd.partner_id = o.partner_id', 'left')
        ->groupBy('month_name');
    $data = $builder->get()->getResultArray();
    $month_wise_sales['income_revenue'] = array_map('intval', array_column($data, 'total_admin_earning'));
    $month_wise_sales['month_name'] = array_column($data, 'month_name');
    $admin_income_revenue = $month_wise_sales;
    return $admin_income_revenue;
    // return $row1;
}
function provider_income_revenue($partner_id = '')
{
    $db = \config\Database::connect();
    $builder =  $db
        ->table('orders o')
        ->select('
        o.final_total, pd.admin_commission,pd.*,
        SUM(o.final_total - (( o.final_total * pd.admin_commission)/100)) as total_partner_earning,DATE_FORMAT(o.date_of_service,"%b") AS month_name
        ')
        ->where('o.status', 'completed')
        // ->where('o.status','completed')
        ->join('partner_details pd', 'pd.partner_id = o.partner_id', 'left')
        ->groupBy('month_name');
    $data = $builder->get()->getResultArray();
    $month_wise_sales['income_revenue'] = array_map('intval', array_column($data, 'total_partner_earning'));
    $month_wise_sales['month_name'] = array_column($data, 'month_name');
    $provider_income_revenue = $month_wise_sales;
    return $provider_income_revenue;
    // return $row1;
}
function total_income_revenue($partner_id = '')
{
    $db = \config\Database::connect();
    $builder = $db
        ->table('orders o')
        ->select('SUM(o.final_total) AS total_earning, DATE_FORMAT(o.date_of_service, "%b") AS month_name')
        ->where('o.status', 'completed')
        ->join('partner_details pd', 'pd.partner_id = o.partner_id', 'left')
        ->groupBy('month_name');
    $data = $builder->get()->getResultArray();
    $month_wise_sales['income_revenue'] = array_map('intval', array_column($data, 'total_earning'));
    $month_wise_sales['month_name'] = array_column($data, 'month_name');
    return $month_wise_sales;
    // return $row1;
}
function fetch_top_trending_services($category_id = 'null')
{
    $db = \config\Database::connect();
    $builder = $db->table('order_services');
    $builder->select('service_id, COUNT(*) as count');
    $builder->where('status', 'completed');
    $builder->groupBy('service_id');
    $builder->orderBy('count', 'desc');
    $builder->limit(10);
    $trending_services = $builder->get()->getResultArray();
    $top_trending_services = array();
    $total_service_orders = array();
    foreach ($trending_services as $key => $trending_service) {
        if ($category_id != "null") {
            $where = ['id' => $trending_service['service_id'], 'category_id' => $category_id];
        } else {
            $where = ['id' => $trending_service['service_id']];
        }
        $services = fetch_details("services", $where, ['id', 'title', 'image', 'price', 'discounted_price', 'category_id'], '10');
        foreach ($services as $key => $row) {
            $total_service_orders = $db->table('order_services o')->select('count(o.id) as `total`')->where('status', 'completed')->where('o.service_id', $row['id'])->get()->getResultArray();
            $services[$key]['order_data'] = $total_service_orders[0]['total'];
        }
        $top_trending_services[] = (!empty($services[0])) ? $services[0] : "";
    }
    return (array_filter($top_trending_services));
}
function order_encrypt($user_id, $amount, $order_id)
{
    $simple_string = $user_id . "-" . $amount . "-" . $order_id;
    // Store the cipher method
    $ciphering = "AES-128-CTR";
    // Use OpenSSl Encryption method
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;
    // Non-NULL Initialization Vector for encryption
    $encryption_iv = '1234567891011121';
    // Store the encryption key
    $encryption_key = getenv('decryption_key');
    // Use openssl_encrypt() function to encrypt the data
    $encryption = openssl_encrypt(
        $simple_string,
        $ciphering,
        $encryption_key,
        $options,
        $encryption_iv
    );
    return $encryption;
}
function order_decrypt($order_id)
{
    $ciphering = "AES-128-CTR";
    $options = 0;
    // Use openssl_encrypt() function to encrypt the data
    $encryption = $order_id;
    // Non-NULL Initialization Vector for decryption
    $decryption_iv = '1234567891011121';
    // Store the decryption key
    $decryption_key = getenv('decryption_key');
    // Use openssl_decrypt() function to decrypt the data
    $decryption = openssl_decrypt(
        $encryption,
        $ciphering,
        $decryption_key,
        $options,
        $decryption_iv
    );
    $order_id = (explode("-", $decryption));
    return $order_id;
}
function is_file_uploaded($result = null)
{
    if ($result == true) {
        return true;
    } else {
        return false;
    }
}
function checkPartnerAvailability($partnerId, $requestedStartTime, $requestedDuration, $date_of_service, $starting_time)
{
    helper('date');
    $db = \Config\Database::connect();
    // Fetch order data from the database for the requested partner
    $builder = $db->table('orders');
    $builder->select('starting_time, ending_time, date_of_service');
    $builder->where('date_of_service', $date_of_service);
    $builder->where('partner_id', $partnerId);
    $builder->whereIn('status', ['awaiting', 'pending', 'confirmed', 'rescheduled']);
    $query = $builder->get()->getResultArray();
    $day = date('l', strtotime($requestedStartTime));
    $timings = getTimingOfDay($partnerId, $day);
    $date_of_service_timestamp = strtotime($date_of_service);
    $current_date_timestamp = time(); // Current date timestamp
    $date_of_service_date = date("Y-m-d", $date_of_service_timestamp);
    $current_date = date("Y-m-d", $current_date_timestamp);
    if ($date_of_service_date != $current_date && $date_of_service_timestamp < $current_date_timestamp) {
        // Date of service is in the past and not the same day as the current date
        $response['error'] = true;
        $response['message'] = "Please Select Upcoming date";
        return $response;
    }
    if (sizeof($query) > 0) {
        $orderTable = $query;
        $partnerClosingTime = $timings['closing_time']; // Replace with the actual closing time
        $requestedEndTime = date('Y-m-d H:i:s', strtotime($requestedStartTime) + $requestedDuration * 60);
        $provider_starting_time = date('H:i:s', strtotime($timings['opening_time']));
        $provider_closing_time = date('H:i:s', strtotime($partnerClosingTime));
        foreach ($orderTable as $order) {
            // Extract start time and end time from the order data
            $orderStartTime = $order['date_of_service'] . ' ' . $order['starting_time'];
            $orderEndTime = $order['date_of_service'] . ' ' . $order['ending_time'];
            // Check for conflicts
            if ($requestedStartTime >= $orderStartTime && $requestedStartTime < $orderEndTime) {
                // Requested start time falls within an existing order
                $response['error'] = true;
                $response['message'] = "The provider is currently unavailable during the requested time slot. Kindly propose an alternative time.";
                return $response;
            } elseif ($requestedEndTime > $orderStartTime && $requestedEndTime <= $orderEndTime) {
                $response['error'] = true;
                $response['message'] = "The provider is currently unavailable during the requested time slot. Kindly propose an alternative time.";
                return $response;
            } elseif ($requestedStartTime < $orderStartTime && $requestedEndTime > $orderEndTime) {
                $response['error'] = true;
                $response['message'] = "The provider is currently unavailable during the requested time slot. Kindly propose an alternative time.";
                return $response;
            }
        }
    }
    $time_slots = get_slot_for_place_order($partnerId, $date_of_service, $requestedDuration, $starting_time);
    if (isset($time_slots['closed']) && $time_slots['closed'] == "true") {
        $response['error'] = true;
        $response['message'] = "Provider is closed at this time";
        return $response;
    }
    $partnerClosingTime = $timings['closing_time']; // Replace with the actual closing time
    $requestedEndTime = date('Y-m-d H:i:s', strtotime($requestedStartTime) + $requestedDuration * 60);
    $provider_starting_time = date('H:i:s', strtotime($timings['opening_time']));
    $provider_closing_time = date('H:i:s', strtotime($partnerClosingTime));
    if ($starting_time < $provider_starting_time || $starting_time >= $provider_closing_time) {
        $response['error'] = true;
        $response['message'] = "Provider is closed at this time";
    } elseif (!$time_slots['slot_avaialble'] && !$time_slots['suborder']) {
        $response['error'] = true;
        $response['message'] = "Slot is not available at this time ";
    } else {
        $response['error'] = false;
        $response['message'] = "Slot is available at this time ";
    }
    return $response;
}
function next_day_available_slots($closing_time, $requestedDuration, $booking_date, $partner_id, $available_slots, $required_duration, $current_date, $busy_slots)
{
    // //-------------------------------------for next day order start--------------------------------------------------
    $before_end_time = date('H:i:s', strtotime($closing_time) - (30 * 60));
    $remaining_duration = $required_duration - 30;
    $next_day_date = date('Y-m-d', strtotime($booking_date . ' +1 day'));
    $next_day = date('l', strtotime($next_day_date));
    $next_day_timings = getTimingOfDay($partner_id, $next_day);
    $next_day_booked_slots = booked_timings($partner_id, $next_day_date);
    $interval = 30 * 60;
    $next_day_opening_time = $next_day_timings['opening_time'];
    $next_day_ending_time = $next_day_timings['closing_time'];
    $next_start_time = strtotime($next_day_opening_time);
    $time = $next_day_opening_time;
    $ending_time_for_next_day_slot = date('H:i:s', strtotime($time . ' +' . $remaining_duration . ' minutes'));
    $next_start_time = strtotime($next_day_opening_time);
    $next_day_available_slots = [];
    $next_day_busy_slots = [];
    $next_day_array_of_time = [];
    if (!empty($next_day_booked_slots)) {
        while ($next_start_time < strtotime($ending_time_for_next_day_slot)) {
            $next_day_array_of_time[] = date("H:i:s", $next_start_time);
            $next_start_time += $interval;
        }
        //check that main order date's last slot is available or not and remaining duration is grater than 30 min
        if (in_array($before_end_time, $available_slots) && $required_duration > 30) {
            //creating time slot for next day   
            //check that next day suggested slots are available or not
            //if next day has  orders
            if (count($next_day_booked_slots) > 0) {
                for ($i = 0; $i < count($next_day_booked_slots); $i++) {
                    //loop on suggested time slots
                    for ($j = 0; $j < count($next_day_array_of_time); $j++) {
                        //if suggested time slot is less than booked slot starting time or suggested time slot is greater than booked time slot starting time
                        if (strtotime($next_day_array_of_time[$j]) < strtotime($next_day_booked_slots[$i]['starting_time']) || strtotime($next_day_array_of_time[$j]) >= strtotime($next_day_booked_slots[$i]['ending_time'])) {
                            //check if suggested time slot is not  in array of avaialble slot
                            if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
                                // echo "suggested slot is not in avaiable slot<br/>";
                                $next_day_available_slots[] = $next_day_array_of_time[$j];
                            } else {
                                if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                                    $next_day_busy_slots[] = $next_day_array_of_time[$j];
                                }
                            }
                        } else {
                            if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                                $next_day_busy_slots[] = $next_day_array_of_time[$j];
                            }
                        }
                    }
                    $count_next_busy_slots = count($next_day_busy_slots);
                    for ($k = 0; $k < $count_next_busy_slots; $k++) {
                        if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
                            unset($next_day_available_slots[$key]);
                        }
                    }
                }
            } else {
                //loop on suggested time slots
                for ($j = 0; $j < count($next_day_array_of_time); $j++) {
                    //check if suggested time slot is not  in array of avaialble slot
                    if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
                        //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
                        if (strtotime($next_day_date) != strtotime($current_date)) {
                            $next_day_available_slots[] = $next_day_array_of_time[$j];
                        } else {
                            if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                                $next_day_busy_slots[] = $next_day_array_of_time[$j];
                            }
                        }
                    }
                }
                $count_next_busy_slots = count($next_day_busy_slots);
                for ($k = 0; $k < $count_next_busy_slots; $k++) {
                    if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
                        unset($next_day_available_slots[$key]);
                    }
                }
            }
            $available_slots = array_values($available_slots);
            $all_continous_slot = calculate_continuous_slots($available_slots);
            $all_continous_slot_last_slots = $all_continous_slot[count($all_continous_slot) - 1];
            $continous_slot_last_slots = ($all_continous_slot_last_slots[count($all_continous_slot_last_slots) - 1]);
            // die;
            if ($before_end_time == $continous_slot_last_slots);
            // print_R('endind slot is avaialble </br>');
            // die;
            $next_day_all_continue_slot = calculate_continuous_slots($next_day_available_slots);
            // print_r( $next_day_all_continue_slot);
            // die;
            $next_day_available_duration = (count($next_day_all_continue_slot) * 30);
            $past_day_available_slot = count($all_continous_slot_last_slots) * 30;
            //  print_r( $next_day_all_continue_slot );
            $past_day_expected_available_duration = $required_duration - $next_day_available_duration;
            // print_R('past_day_expected_available_duration--' .$past_day_expected_available_duration."</br>");
            // print_R('past_day_available_slot--' .$past_day_available_slot."</br>");
            if ($past_day_expected_available_duration < 0 ||  $past_day_expected_available_duration < $past_day_available_slot) {
                if (count($next_day_available_slots) < count($next_day_array_of_time)) {
                    for ($k = 0; $k < count($available_slots); $k++) {
                        if (($key = array_search($before_end_time, $available_slots)) !== false) {
                            if (count($next_day_available_slots) < count($next_day_array_of_time)) {
                                // unset($available_slots[$key]);
                                // $busy_slots[] = $before_end_time;
                            }
                        }
                    }
                }
            }
        } else {
            for ($j = 0; $j < count($next_day_array_of_time); $j++) {
                //check if suggested time slot is not  in array of avaialble slot
                if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
                    //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
                    if (strtotime($next_day_date) != strtotime($booking_date)) {
                        $next_day_available_slots[] = $next_day_array_of_time[$j];
                    } else {
                        if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                            $next_day_busy_slots[] = $next_day_array_of_time[$j];
                        }
                    }
                }
            }
            $count_next_busy_slots = count($next_day_busy_slots);
            for ($k = 0; $k < $count_next_busy_slots; $k++) {
                if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
                    unset($next_day_available_slots[$key]);
                }
            }
            $available_slots = array_values($available_slots);
            // if (count($next_day_available_slots) < count($next_day_array_of_time)) {
            //     for ($k = 0; $k < count($available_slots); $k++) {
            //         if (($key = array_search($before_end_time, $available_slots)) !== false) {
            //             if (count($next_day_available_slots) < count($next_day_array_of_time)) {
            //                 unset($available_slots[$key]);
            //                 $busy_slots[] = $before_end_time;
            //             }
            //         }
            //     }
            // }
            $all_continous_slot = calculate_continuous_slots($available_slots);
            $all_continous_slot_last_slots = $all_continous_slot[count($all_continous_slot) - 1];
            $continous_slot_last_slots = ($all_continous_slot_last_slots[count($all_continous_slot_last_slots) - 1]);
            // die;
            if ($before_end_time == $continous_slot_last_slots);
            $next_day_all_continue_slot = calculate_continuous_slots($next_day_available_slots);
            $next_day_available_duration = (count($next_day_all_continue_slot) * 30);
            $past_day_available_slot = count($all_continous_slot_last_slots) * 30;
            $past_day_expected_available_duration = $required_duration - $next_day_available_duration;
            if ($past_day_expected_available_duration < 0 || $past_day_available_slot < $past_day_expected_available_duration) {
                if (count($next_day_available_slots) < count($next_day_array_of_time)) {
                    for ($k = 0; $k < count($available_slots); $k++) {
                        if (($key = array_search($before_end_time, $available_slots)) !== false) {
                            if (count($next_day_available_slots) < count($next_day_array_of_time)) {
                                // unset($available_slots[$key]);
                                // $busy_slots[] = $before_end_time;
                            }
                        }
                    }
                }
            }
        }
        $response['error'] = false;
        $response['available_slots'] = $available_slots;
        $response['busy_slots'] = $busy_slots;
        return $response;
    }
}
function getTimingArray($start_time, $end_time, $interval)
{
    $timing_array = [];
    $current_time = strtotime($start_time);
    $end_time = strtotime($end_time);
    while ($current_time < $end_time) {
        $timing_array[] = date('H:i:s', $current_time);
        $current_time += $interval * 60;
    }
    return $timing_array;
}
function get_next_days_slots($closing_time, $booking_date, $partner_id, $required_duration, $current_date)
{
    // print_R($required_duration);
    // die;
    // //-------------------------------------for next day order start--------------------------------------------------
    // if ($required_duration == 30) {
    //     $remaining_duration = 30;
    // } else {
    $remaining_duration = $required_duration - 30;
    // }
    $next_day_date = date('Y-m-d', strtotime($booking_date . ' +1 day'));
    $next_day = date('l', strtotime($next_day_date));
    $next_day_timings = getTimingOfDay($partner_id, $next_day);
    $next_day_booked_slots = booked_timings($partner_id, $next_day_date);
    $interval = 30 * 60;
    if (!empty($next_day_timings)) {
        $next_day_opening_time = $next_day_timings['opening_time'];
        $next_day_ending_time = $next_day_timings['closing_time'];
        $next_start_time = strtotime($next_day_opening_time);
        $time = $next_day_opening_time;
        $ending_time_for_next_day_slot = date('H:i:s', strtotime($time . ' +' . $remaining_duration . ' minutes'));
        $next_start_time = strtotime($next_day_opening_time);
        $next_day_available_slots = [];
        $next_day_busy_slots = [];
        $next_day_array_of_time = [];
        while ($next_start_time < strtotime($ending_time_for_next_day_slot)) {
            $next_day_array_of_time[] = date("H:i:s", $next_start_time);
            $next_start_time += $interval;
        }
        if (!empty($next_day_booked_slots)) {
            //check that main order date's last slot is available or not and remaining duration is grater than 30 min
            //creating time slot for next day   
            //check that next day suggested slots are available or not
            //if next day has  orders
            if (count($next_day_booked_slots) > 0) {
                for ($i = 0; $i < count($next_day_booked_slots); $i++) {
                    // echo "-------------------------</br>";
                    //loop on suggested time slots
                    for ($j = 0; $j < count($next_day_array_of_time); $j++) {
                        //if suggested time slot is less than booked slot starting time or suggested time slot is greater than booked time slot starting time
                        if (strtotime($next_day_array_of_time[$j]) < strtotime($next_day_booked_slots[$i]['starting_time']) || strtotime($next_day_array_of_time[$j]) >= strtotime($next_day_booked_slots[$i]['ending_time'])) {
                            //check if suggested time slot is not  in array of avaialble slot
                            if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
                                // echo $next_day_array_of_time[$j]."--suggested slot is adding in avaiable slot<br/>";
                                $next_day_available_slots[] = $next_day_array_of_time[$j];
                            } else {
                                // echo $next_day_array_of_time[$j]."--suggested slot is adding in busy slot 1<br/>";
                                // if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                                //     $next_day_busy_slots[] = $next_day_array_of_time[$j];
                                // }
                            }
                        } else {
                            // echo $next_day_array_of_time[$j]."--suggested slot is adding in busy slot 2<br/>";
                            if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                                $next_day_busy_slots[] = $next_day_array_of_time[$j];
                            }
                        }
                    }
                    $count_next_busy_slots = count($next_day_busy_slots);
                    for ($k = 0; $k < $count_next_busy_slots; $k++) {
                        if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
                            unset($next_day_available_slots[$key]);
                        }
                    }
                }
            } else {
                //loop on suggested time slots
                for ($j = 0; $j < count($next_day_array_of_time); $j++) {
                    //check if suggested time slot is not  in array of avaialble slot
                    if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
                        //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
                        if (strtotime($next_day_date) != strtotime($current_date)) {
                            $next_day_available_slots[] = $next_day_array_of_time[$j];
                        } else {
                            if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                                $next_day_busy_slots[] = $next_day_array_of_time[$j];
                            }
                        }
                    }
                }
                $count_next_busy_slots = count($next_day_busy_slots);
                for ($k = 0; $k < $count_next_busy_slots; $k++) {
                    if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
                        unset($next_day_available_slots[$key]);
                    }
                }
            }
            $next_day_available_slots = array_values($next_day_available_slots);
            $all_continuos_slot = calculate_continuous_slots($next_day_available_slots);
            $response['error'] = false;
            $response['available_slots'] = $next_day_available_slots;
            $response['busy_slots'] = $next_day_busy_slots;
            $response['continous_available_slots'] = $all_continuos_slot;
            return $response;
        } else {
            //loop on suggested time slots
            for ($j = 0; $j < count($next_day_array_of_time); $j++) {
                //check if suggested time slot is not  in array of avaialble slot
                if (!in_array($next_day_array_of_time[$j], $next_day_available_slots)) {
                    //if suggested time slot is grater than current time and current date and booked date are not same then to available slot array otherwise busy slot array
                    if (strtotime($next_day_date) != strtotime($current_date)) {
                        $next_day_available_slots[] = $next_day_array_of_time[$j];
                    } else {
                        if (!in_array($next_day_array_of_time[$j], $next_day_busy_slots)) {
                            $next_day_busy_slots[] = $next_day_array_of_time[$j];
                        }
                    }
                }
            }
            $count_next_busy_slots = count($next_day_busy_slots);
            for ($k = 0; $k < $count_next_busy_slots; $k++) {
                if (($key = array_search($next_day_busy_slots[$k], $next_day_available_slots)) !== false) {
                    unset($next_day_available_slots[$key]);
                }
            }
            $next_day_available_slots = array_values($next_day_available_slots);
            $all_continuos_slot = calculate_continuous_slots($next_day_available_slots);
            $response['error'] = false;
            $response['available_slots'] = $next_day_available_slots;
            $response['busy_slots'] = $next_day_busy_slots;
            $response['continous_available_slots'] = $all_continuos_slot;
            return $response;
        }
    } else {
        $response['error'] = false;
        $response['available_slots'] = [];
        $response['busy_slots'] = [];
        $response['continous_available_slots'] = [];
        return $response;
    }
}


//old 
// function calculate_continuous_slots($array_of_time)
// {
//     $available_slots = array_values($array_of_time);
//     // creating chunks of countinuos time slots from available time slots
//     $all_continous_slot = [];
//     $continous_slot_number = 0;
//     for ($i = 0; $i < count($available_slots) - 1; $i++) {
//         //here we add 30 minutes to  available time slot 
//         $next_expected_time_slot = date("H:i:s", strtotime('+30 minutes', strtotime($available_slots[$i])));
//         //here we check avaialable slot + 1  means if avaialbe slot is 9:00 then available slot +1 is 9:30 is same as expected time slot if yes then add to continue slot 
//         if (($available_slots[$i + 1] == $next_expected_time_slot)) {
//             $all_continous_slot[$continous_slot_number][] = $available_slots[$i];
//             if (count($available_slots) - 1 == $i + 1) {
//                 $all_continous_slot[$continous_slot_number][] = $available_slots[$i + 1];
//             }
//         } else {
//             $all_continous_slot[$continous_slot_number][] = $available_slots[$i];
//             $continous_slot_number++;
//         }
//     }
//     return $all_continous_slot;
// }

function calculate_continuous_slots($array_of_time)
{
    $available_slots = array_values($array_of_time);
    // creating chunks of countinuos time slots from available time slots
    $all_continous_slot = [];
    $continous_slot_number = 0;
    for ($i = 0; $i <= count($available_slots) - 1; $i++) {
        //here we add 30 minutes to  available time slot 
        $next_expected_time_slot = date("H:i:s", strtotime('+30 minutes', strtotime($available_slots[$i])));
        //here we check avaialable slot + 1  means if avaialbe slot is 9:00 then available slot +1 is 9:30 is same as expected time slot if yes then add to continue slot 
        // if (($available_slots[$i + 1] == $next_expected_time_slot)) {
        
        
                
        if (isset($available_slots[$i+1])&&($available_slots[$i+1] == $next_expected_time_slot)) {
            $all_continous_slot[$continous_slot_number][] = $available_slots[$i];
            if (count($available_slots) == $i ) {
                $all_continous_slot[$continous_slot_number][] = $available_slots[$i];
            }
        } else {
            $all_continous_slot[$continous_slot_number][] = $available_slots[$i];
            $continous_slot_number++;
        }
    }
  
    return $all_continous_slot;
}
function get_slot_for_place_order($partnerId, $date_of_service, $required_duration, $starting_time)
{
    // $day = date('l', strtotime($starting_time));
    $day = date('l', strtotime($date_of_service));
    $current_date = date('Y-m-d');
    $timings = getTimingOfDay($partnerId, $day);
    $response = [];
    if (isset($timings) && !empty($timings)) {
        $provider_closing_time = date('H:i:s', strtotime($timings['closing_time']));
        $expoloed_start_time = explode(':', $starting_time);
        $remaining_duration = $required_duration;
        $extra_minutes = '';
        if (($expoloed_start_time[1] > 15 && $expoloed_start_time[1] <= 30) || ($expoloed_start_time[1] > 45 && $expoloed_start_time[1] > 30)) {
            $rounded = date('H:i:s', ceil(strtotime($starting_time) / 1800) * 1800);
            $differenceBetweenRoundedTime = round(abs(strtotime($rounded) - strtotime($starting_time)) / 60, 2);
            $extra_minutes = 'deduct';
        } else {
            $rounded = date('H:i:s', floor(strtotime($starting_time) / 1800) * 1800);
            $differenceBetweenRoundedTime = round(abs(strtotime($starting_time) -  strtotime($rounded)) / 60, 2);
            $extra_minutes = 'add';
        }
        $time_slots = get_available_slots_without_processing($partnerId, $date_of_service, $required_duration, $rounded); //working
        if (!isset($time_slots['available_slots'][0])) {
            $response['suborder'] = false;
            $response['slot_avaialble'] = false;
            return $response;
        }
        $array_of_time = $time_slots['available_slots'][0];
        $array_of_time = array_values($array_of_time);
        if ($array_of_time[0] == $rounded) {
            $next_expected_time_slot = $rounded;
            foreach ($array_of_time as $row) {
                if ($row == $next_expected_time_slot && ($row < $provider_closing_time)) {
                    // print_R("row-- ".$row."</br>");
                    $next_expected_time_slot = date("H:i:s", strtotime('+30 minutes', strtotime($row)));
                    //  print_R("next slot -- ".$next_expected_time_slot."</br>");
                    $remaining_duration = $remaining_duration - 30;
                    // print_R("remaining duration -- ".$remaining_duration."</br>");
                }
            }
            if ($extra_minutes == "add") {
                $remaining_duration += $differenceBetweenRoundedTime;
            } else if ($extra_minutes == "deduct") {
                $remaining_duration -= $differenceBetweenRoundedTime;
            }
            // die;
            if ($remaining_duration <= 0) {
                $response['suborder'] = false;
                $response['slot_avaialble'] = true;
                $response['order_data'] =  $time_slots['available_slots'][0];
            } else {
                $next_day_slots = get_next_days_slots($provider_closing_time, $date_of_service, $partnerId, $required_duration, $current_date);
                $next_day_available_slots = $next_day_slots['available_slots'];
                if ((sizeof($next_day_available_slots) * 30) >= $remaining_duration) {
                    $response['suborder'] = true;
                    $response['suborder_data'] = $next_day_available_slots;
                    $response['order_data'] =  $time_slots['available_slots'][0];
                    $response['slot_avaialble'] = true;
                } else {
                    $response['suborder'] = false;
                    $response['slot_avaialble'] = false;
                }
            }
        } else {
            $response['suborder'] = false;
            $response['slot_avaialble'] = false;
        }
    } else {
        $response['closed'] = "true";
        $response['suborder'] = false;
        $response['slot_avaialble'] = false;
    }
    return $response;
}
function get_service_ratings($service_id)
{
    $db = \config\Database::connect();
    //    echo "<pre>";
    $builder = $db
        ->table('services s')
        ->select("
                COUNT(sr.rating) as total_ratings,
                SUM( CASE WHEN sr.rating = ceil(5) THEN 1 ELSE 0 END) as rating_5,
                SUM( CASE WHEN sr.rating = ceil(4) THEN 1 ELSE 0 END) as rating_4,
                SUM( CASE WHEN sr.rating = ceil(3) THEN 1 ELSE 0 END) as rating_3,
                SUM( CASE WHEN sr.rating = ceil(2) THEN 1 ELSE 0 END) as rating_2,
                SUM( CASE WHEN sr.rating = ceil(1) THEN 1 ELSE 0 END) as rating_1
            ")
        ->join('services_ratings sr', 'sr.service_id = s.id')
        ->where('sr.service_id', $service_id)
        ->get()->getResultArray();
    // print_r($builder);
    return $builder;
}
function calculate_subscription_price($subcription_id)
{
    $subscription_details = fetch_details('subscriptions', ['id' => $subcription_id]);
    $taxPercentageData = fetch_details('taxes', ['id' => $subscription_details[0]['tax_id']], ['percentage']);
    if (!empty($taxPercentageData)) {
        $taxPercentage = $taxPercentageData[0]['percentage'];
    } else {
        $taxPercentage = 0;
    }
    $subscription_details[0]['tax_percentage'] = $taxPercentage;
    if ($subscription_details[0]['discount_price'] == "0") {
        if ($subscription_details[0]['tax_type'] == "excluded") {
            $subscription_details[0]['tax_value'] = number_format((intval(($subscription_details[0]['price'] * ($taxPercentage) / 100))), 2);
            $subscription_details[0]['price_with_tax']  = strval($subscription_details[0]['price'] + ($subscription_details[0]['price'] * ($taxPercentage) / 100));
            $subscription_details[0]['original_price_with_tax'] = strval($subscription_details[0]['price'] + ($subscription_details[0]['price'] * ($taxPercentage) / 100));
        } else {
            $subscription_details[0]['tax_value'] = "";
            $subscription_details[0]['price_with_tax']  = strval($subscription_details[0]['price']);
            $subscription_details[0]['original_price_with_tax'] = strval($subscription_details[0]['price']);
        }
    } else {
        if ($subscription_details[0]['tax_type'] == "excluded") {
            $subscription_details[0]['tax_value'] = number_format((intval(($subscription_details[0]['discount_price'] * ($taxPercentage) / 100))), 2);
            $subscription_details[0]['price_with_tax']  = strval($subscription_details[0]['discount_price'] + ($subscription_details[0]['discount_price'] * ($taxPercentage) / 100));
            $subscription_details[0]['original_price_with_tax'] = strval($subscription_details[0]['price'] + ($subscription_details[0]['discount_price'] * ($taxPercentage) / 100));
        } else {
            $subscription_details[0]['tax_value'] = "";
            $subscription_details[0]['price_with_tax']  = strval($subscription_details[0]['discount_price']);
            $subscription_details[0]['original_price_with_tax'] = strval($subscription_details[0]['price']);
        }
    }
    return $subscription_details;
}
function calculate_partner_subscription_price($partner_id, $subscription_id, $id)
{
    $partner_subscriptions = fetch_details('partner_subscriptions', ['partner_id' => $partner_id, 'subscription_id' => $subscription_id, 'id' => $id]);
    $taxPercentage = $partner_subscriptions[0]['tax_percentage'];
    $partner_subscriptions[0]['tax_percentage'] = $taxPercentage;
    if ($partner_subscriptions[0]['discount_price'] == "0") {
        if ($partner_subscriptions[0]['tax_type'] == "excluded") {
            $partner_subscriptions[0]['tax_value'] = number_format((intval(($partner_subscriptions[0]['price'] * ($taxPercentage) / 100))), 2);
            $partner_subscriptions[0]['price_with_tax']  = strval($partner_subscriptions[0]['price'] + ($partner_subscriptions[0]['price'] * ($taxPercentage) / 100));
            $partner_subscriptions[0]['original_price_with_tax'] = strval($partner_subscriptions[0]['price'] + ($partner_subscriptions[0]['price'] * ($taxPercentage) / 100));
        } else {
            $partner_subscriptions[0]['tax_value'] = "";
            $partner_subscriptions[0]['price_with_tax']  = strval($partner_subscriptions[0]['price']);
            $partner_subscriptions[0]['original_price_with_tax'] = strval($partner_subscriptions[0]['price']);
        }
    } else {
        if ($partner_subscriptions[0]['tax_type'] == "excluded") {
            $partner_subscriptions[0]['tax_value'] = number_format((intval(($partner_subscriptions[0]['discount_price'] * ($taxPercentage) / 100))), 2);
            $partner_subscriptions[0]['price_with_tax']  = strval($partner_subscriptions[0]['discount_price'] + ($partner_subscriptions[0]['discount_price'] * ($taxPercentage) / 100));
            $partner_subscriptions[0]['original_price_with_tax'] = strval($partner_subscriptions[0]['price'] + ($partner_subscriptions[0]['discount_price'] * ($taxPercentage) / 100));
        } else {
            $partner_subscriptions[0]['tax_value'] = "";
            $partner_subscriptions[0]['price_with_tax']  = strval($partner_subscriptions[0]['discount_price']);
            $partner_subscriptions[0]['original_price_with_tax'] = strval($partner_subscriptions[0]['price']);
        }
    }
    return $partner_subscriptions;
}
function add_subscription($subscription_id, $partner_id, $insert_id = null)
{
    $settings = get_settings('general_settings', true);
    date_default_timezone_set($settings['system_timezone']); // Added user timezone
    $subscription_details = fetch_details('subscriptions', ['id' => $subscription_id]);
    if ($subscription_details[0]['price'] == "0") {
        $price = calculate_subscription_price($subscription_details[0]['id']);;
        $purchaseDate = date('Y-m-d');
        $subscriptionDuration = $subscription_details[0]['duration'];
        if ($subscriptionDuration == "unlimited") {
            $subscriptionDuration = 0;
        }
        $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days'));
        $partner_subscriptions = [
            'partner_id' =>  $partner_id,
            'subscription_id' => $subscription_id,
            'is_payment' => "1",
            'status' => "active",
            'purchase_date' => date('Y-m-d'),
            'expiry_date' =>  $expiryDate,
            'name' => $subscription_details[0]['name'],
            'description' => $subscription_details[0]['description'],
            'duration' => $subscription_details[0]['duration'],
            'price' => $subscription_details[0]['price'],
            'discount_price' => $subscription_details[0]['discount_price'],
            'publish' => $subscription_details[0]['publish'],
            'order_type' => $subscription_details[0]['order_type'],
            'max_order_limit' => $subscription_details[0]['max_order_limit'],
            'service_type' => $subscription_details[0]['service_type'],
            'max_service_limit' => $subscription_details[0]['max_service_limit'],
            'tax_type' => $subscription_details[0]['tax_type'],
            'tax_id' => $subscription_details[0]['tax_id'],
            'is_commision' => $subscription_details[0]['is_commision'],
            'commission_threshold' => $subscription_details[0]['commission_threshold'],
            'commission_percentage' => $subscription_details[0]['commission_percentage'],
            'transaction_id' => '0',
            'tax_percentage' => $price[0]['tax_percentage'],
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ];
        $data = insert_details($partner_subscriptions, 'partner_subscriptions');
        $inserted_subscription = fetch_details('partner_subscriptions', ['id' => $data['id']]);
        if ($inserted_subscription[0]['is_commision'] == "yes") {
            $commission = $inserted_subscription[0]['commission_percentage'];
        } else {
            $commission = 0;
        }
        update_details(['admin_commission' => $commission], ['partner_id' => $partner_id], 'partner_details');
        return true;
    } else {
        if ($subscription_details[0]['is_commision'] == "yes") {
            $commission = $subscription_details[0]['commission_percentage'];
        } else {
            $commission = 0;
        }
        update_details(['admin_commission' => $commission], ['partner_id' => $partner_id], 'partner_details');
        $details_for_subscription = fetch_details('subscriptions', ['id' => $subscription_id]);
        $subscriptionDuration = $details_for_subscription[0]['duration'];
        // Calculate the expiry date based on the current date and subscription duration
        $purchaseDate = date('Y-m-d'); // Get the current date
        if ($subscriptionDuration == "unlimited") {
            $subscriptionDuration = 0;
        }
        $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' + ' . $subscriptionDuration . ' days')); // Add the duration to the purchase date
        $taxPercentageData = fetch_details('taxes', ['id' => $details_for_subscription[0]['tax_id']], ['percentage']);
        if (!empty($taxPercentageData)) {
            $taxPercentage = $taxPercentageData[0]['percentage'];
        } else {
            $taxPercentage = 0;
        }
        $partner_subscriptions = [
            'partner_id' =>  $partner_id,
            'subscription_id' => $subscription_id,
            'is_payment' => "0",
            'status' => "pending",
            'purchase_date' => $purchaseDate,
            'expiry_date' => $expiryDate,
            'name' => $details_for_subscription[0]['name'],
            'description' => $details_for_subscription[0]['description'],
            'duration' => $details_for_subscription[0]['duration'],
            'price' => $details_for_subscription[0]['price'],
            'discount_price' => $details_for_subscription[0]['discount_price'],
            'publish' => $details_for_subscription[0]['publish'],
            'order_type' => $details_for_subscription[0]['order_type'],
            'max_order_limit' => $details_for_subscription[0]['max_order_limit'],
            'service_type' => $details_for_subscription[0]['service_type'],
            'max_service_limit' => $details_for_subscription[0]['max_service_limit'],
            'tax_type' => $details_for_subscription[0]['tax_type'],
            'tax_id' => $details_for_subscription[0]['tax_id'],
            'is_commision' => $details_for_subscription[0]['is_commision'],
            'commission_threshold' => $details_for_subscription[0]['commission_threshold'],
            'commission_percentage' => $details_for_subscription[0]['commission_percentage'],
            'transaction_id' => $insert_id,
            'tax_percentage' => $taxPercentage,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ];
        insert_details($partner_subscriptions, 'partner_subscriptions');
        return true;
    }
}
if (!function_exists('format_date')) {
    function format_date($dateString, $format = 'Y-m-d H:i:s')
    {
        $date = date_create($dateString);
        return date_format($date, $format);
    }
}
function uploadFile($request, $fieldName, $uploadPath, &$updatedData, $data)
{
    $file = $request->getFile($fieldName);
    if ($file->isValid()) {
        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);
        $updatedData[$fieldName] = $newName;
    } else {
        $updatedData[$fieldName] = isset($data[$fieldName]) ? $data[$fieldName] : "";
    }
}
function verify_transaction($order_id)
{
    $transaction = fetch_details('transactions', ['order_id' => $order_id]);
    if (!empty($transaction)) {
        if ($transaction[0]['type'] == "razorpay") {
            $razorpay = new Razorpay;
            $credentials = $razorpay->get_credentials();
            $secret = $credentials['secret'];
            $api = new Api($credentials['key'], $secret);
            $payment = $api->payment->fetch($transaction[0]['txn_id']);
            $status = $payment->status;
            if ($status != "captured") {
                update_details(['payment_status' => '1'], ['id' => $order_id], 'orders');
                $response['error'] = false;
                $response['message'] = 'Verified Successfully';
            } else if ($status != "captured") {
                update_details(['status' => 'cancelled'], ['id' => $order_id], 'orders');
                $response['error'] = true;
                $response['message'] = 'Order is cancelled due to pending payment .';
            }
        } elseif ($transaction[0]['type'] == "stripe") {
            $settings = get_settings('payment_gateways_settings', true);
            $secret_key = isset($settings['stripe_secret_key']) ? $settings['stripe_secret_key'] : "sk_test_51LERZeSCiHzi4IW1hODcT6ngl88bSZzN4SHqH58CFKJ7eEQKSzniJTXgVNXFQPXuKfu9pAOYVMOe6UeE2q7hY5J400qllsvrye";
            $http = service('curlrequest');
            $http->setHeader('Authorization', 'Bearer ' . $secret_key);
            $http->setHeader('Content-Type', 'application/x-www-form-urlencoded');
            $response = $http->get("https://api.stripe.com/v1/payment_intents/{$transaction[0]['txn_id']}");
            $responseData = json_decode($response->getBody(), true);
            $statusOfTransaction = $responseData['status'];
            if ($statusOfTransaction == "succeeded") {
                update_details(['payment_status' => '1'], ['id' => $order_id], 'orders');
                $response['error'] = false;
                $response['message'] = 'Verified Successfully';
            } else if ($statusOfTransaction != "succeeded") {
                update_details(['status' => 'cancelled'], ['id' => $order_id], 'orders');
                $response['error'] = true;
                $response['message'] = 'Order is cancelled due to pending payment .';
            }
        } else if ($transaction[0]['type'] = "paystack") {
            $paystack = new Paystack();
            $payment = $paystack->verify_transation($transaction[0]['reference']);
            $message = json_decode($payment, true);
            if ($message['status'] == "1" || $message['status'] == "success") {
                update_details(['payment_status' => '1'], ['id' => $order_id], 'orders');
                $response['error'] = false;
                $response['message'] = 'Verified Successfully';
            } else if ($message['status'] != "1" || $message['status'] != "success") {

                update_details(['status' => 'cancelled'], ['id' => $order_id], 'orders');
                $response['error'] = true;
                $response['message'] = 'Order is cancelled due to pending payment .';
            }
        }

        return $response;
    }
}

function create_stripe_payment_intent()
{
    $settings = get_settings('payment_gateways_settings', true);
    $secret_key = $settings['stripe_secret_key'] ?? "sk_test_51LERZeSCiHzi4IW1hODcT6ngl88bSZzN4SHqH58CFKJ7eEQKSzniJTXgVNXFQPXuKfu9pAOYVMOe6UeE2q7hY5J400qllsvrye";

    $data = [
        'amount' => 100,
        'currency' => 'usd',
        'description' => 'Test',
        'payment_method_types' => ['card'],
        'metadata' => [
            'user_id' => 1, // Replace with CI4 helper function to get the user ID
            'competition_id' => 1,
        ],
        'shipping' => [
            'name' => 'TEST', // Replace with CI4 helper function to get user data
            'address' => [
                'country' => "in",
            ],
        ],
    ];

    $body = http_build_query($data);

    $response = \Config\Services::curlrequest()
        ->setHeader('Authorization', 'Bearer ' . $secret_key)
        ->setHeader('Content-Type', 'application/x-www-form-urlencoded')
        ->setBody($body)
        ->post('https://api.stripe.com/v1/payment_intents');

    $responseData = json_decode($response->getBody(), true);
    return $responseData;
}

function add_transaction_for_place_order($user_id, $order_id, $type, $amount, $txn_id, $reference = Null)
{
    $data = [
        'transaction_type' => 'transaction',
        'user_id' => $user_id,
        'partner_id' => "",
        'order_id' => $order_id,
        'type' => $type,
        'txn_id' => $txn_id,
        'amount' => $amount,
        'status' => 'pending',
        'currency_code' => "",
        'reference' => $reference ?? "",
        'message' => 'order is placed',
    ];
    $insert_id = add_transaction($data);
    return $insert_id;
}


function razorpay_create_order_for_place_order($order_id)
{
    $order_id = $order_id;
    if ($order_id && !empty($order_id)) {
        $where['o.id'] = $order_id;
    }
    $orders = new Orders_model();
    $order_detail = $orders->list(true, "", null, null, "", "", $where);
    $settings = get_settings('payment_gateways_settings', true);
    if (!empty($order_detail) && !empty($settings)) {
        $currency = $settings['razorpay_currency'];
        $price = $order_detail['data'][0]['final_total'];
        $amount = intval($price * 100);
        $razorpay = new Razorpay();

        $create_order = $razorpay->create_order($amount, $order_id, $currency);
        if (!empty($create_order)) {
            $response = [
                'error' => false,
                'message' => 'razorpay order created',
                'data' => $create_order,
            ];
        } else {
            $response = [
                'error' => true,
                'message' => 'razorpay order not created',
                'data' => [],
            ];
        }
    } else {
        $response = [
            'error' => true,
            'message' => 'details not found"',
            'data' => [],
        ];
    }
    return $response;
}

function create_order_paypal_for_place_order()
{


    // // Set the request payload
    // $payload = [
    //     'intent' => 'AUTHORIZE',
    //     'purchase_units' => [
    //         [
    //             'amount' => [
    //                 'currency_code' => 'USD',
    //                 'value' => '100.00',
    //             ],
    //             'custom_id' => 'my custom string',
    //             'redirect_urls' => [
    //                 'return_url' => 'https://webhook.site/05c7f797-f4f1-4f47-be35-12a3fd63e1b2',
    //                 'cancel_url' => 'https://webhook.site/05c7f797-f4f1-4f47-be35-12a3fd63e1b2',
    //                 'notify_url' => 'https://edemand-test.thewrteam.in/api/webhooks/paypal'
    //             ],
    //             ''
    //         ],
    //     ],
    // ];

    // $http = service('curlrequest');
    // $http->setHeader('Authorization', 'Bearer A21AAI09hcsvRm84WKXoJhxlYCrbVb1WzEwkSLnXs43Wvv7OlGthdO3eEu8ReoQov4r8BgypNxPA6R43SbPKkfX9yjpM0ksNg');
    // $http->setJson($payload);
    // $http->setHeader('Content-Type', 'application/x-www-form-urlencoded');
    // $response = $http->post(
    //     'https://api-m.sandbox.paypal.com/v2/checkout/orders'
    // );
    // $responseData = json_decode($response->getBody(), true);


    // try {
    //     $client = new Client();

    //     $uri = 'https://api-m.sandbox.paypal.com/v2/checkout/orders';

    $clientId = 'AapTUKyB6toRWxfn8KktiAq9wUSkxclOGKJBBaQj7OCDs9Ns';
    $secret = 'EBuyLyX_CIph79rhwRbzV4-D_CHSgvB-JP9lqjfS78Og62cwlbYCSWEZuicvx7yjdwK5HQgSrIRt6N1r';

    //     $payload = [
    //         'intent' => 'CAPTURE',
    //         'purchase_units' => [
    //             [
    //                 'amount' => [
    //                     'currency_code' => 'USD',
    //                     'value' => '100.00',
    //                 ]
    //             ]
    //         ],
    //         'headers' => [
    //             'Accept' => 'application/json',
    //             'Accept-Language' => 'en_US',
    //             'Content-Type' => 'application/json',
    //         ],
    //         'auth' => [$clientId, $secret, 'basic'],
    //     ];

    //     $response = $client->request('POST', $uri, [
    //         'json' => $payload
    //     ]);

    //     $data = json_decode($response->getBody(), true);

    //     echo "<pre>";
    //     print_r($data);
    //     echo "</pre>";

    // } catch (ClientException $e) {
    //     // Handle 401 Unauthorized error
    //     echo 'Error Code: ' . $e->getCode() . "\n";
    //     echo 'Error Message: ' . $e->getMessage() . "\n";
    //     // You can also get detailed error response from $e->getResponse()
    // }
    // $link = $responseData['links'][1]['href'];
    // print_R($link);
    // add_transaction_for_place_order(1, 2, 'paypal', '1000', $responseData['id']);


    // Step 1: Generate a new access token
    $clientId = 'AapTUKyB6toRWxfn8KktiAq9wUSkxclOGKJBBaQj7OCDs9Ns';
    $secret = 'EBuyLyX_CIph79rhwRbzV4-D_CHSgvB-JP9lqjfS78Og62cwlbYCSWEZuicvx7yjdwK5HQgSrIRt6N1r';

    $client = new Client();

    try {
        $tokenResponse = $client->request('POST', 'https://api-m.sandbox.paypal.com/v1/oauth2/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
            'auth' => [$clientId, $secret],
        ]);

        $tokenData = json_decode($tokenResponse->getBody(), true);
        $accessToken = $tokenData['access_token'];


        print_r($accessToken);
        die;

        // Step 2: Make the API request with the new access token
        $uri = 'https://api-m.sandbox.paypal.com/v2/checkout/orders';

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => '100.00',
                    ]
                ]
            ],
        ];

        $response = $client->request('POST', $uri, [
            'json' => $payload,
            'headers' => [
                'Accept' => 'application/json',
                'Accept-Language' => 'en_US',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        echo "<pre>";
        print_r($data);
        echo "</pre>";
    } catch (ClientException $e) {
        // Handle 401 Unauthorized error
        echo 'Error Code: ' . $e->getCode() . "\n";
        echo 'Error Message: ' . $e->getMessage() . "\n";
        // You can also get detailed error response from $e->getResponse()
    }
}
