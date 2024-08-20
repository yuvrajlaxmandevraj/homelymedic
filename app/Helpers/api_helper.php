<?php

// function generate_tokens($identity)
// {

//     $jwt = new App\Libraries\JWT();
//     $db      = \Config\Database::connect();
//     $user_id = $db->table('users')->select('id')->where(['phone' => $identity])->get()->getResultArray()[0]['id'];



//     $payload = [
//         'iat' => time(), /* issued at time */
//         'iss' => 'edemand',
//         'exp' => time() + (60 * 60 * 24 * 365),
//         'sub' => 'edemand_authentication',
//         'user_id' => $user_id
//     ];
//     $token = $jwt->encode($payload, API_SECRET);
//     return $token;
// }

function generate_tokens($identity,$user_group)
{

    $jwt = new App\Libraries\JWT();
    $db      = \Config\Database::connect();
    $user_id = $db->table('users')->select('id')->where(['phone' => $identity])->get()->getResultArray()[0]['id'];
    $db      = \Config\Database::connect();

    $builder = $db->table('users u');
    $builder->select('u.*,ug.group_id')
        ->join('users_groups ug', 'ug.user_id = u.id')
        ->where('ug.group_id',$user_group)
        ->where(['phone' => $identity]);
    $user_id = $builder->get()->getResultArray()[0]['id'];


    $payload = [
        'iat' => time(), /* issued at time */
        'iss' => 'edemand',
        'exp' => time() + (60 * 60 * 24 * 365),
        'sub' => 'edemand_authentication',
        'user_id' => $user_id
    ];
    $token = $jwt->encode($payload, API_SECRET);
    return $token;
}
function verify_tokens()
{
    $responses = \Config\Services::response();

    $jwt = new App\Libraries\JWT;


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
            return false;
        }
        App\Libraries\JWT::$leeway = 60000000000000;
        $flag = true; //For payload indication that it return some data or throws an expection.
        $error = true; //It will indicate that the payload had verified the signature and hash is valid or not.

        $message = '';
        $user_token = "";
        try {
            $user_id = $jwt->decode_unsafe($token)->user_id;
            $user_token = fetch_details('users', ['id' => $user_id])[0]['api_key'];
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        if ($user_token == $token) {
            try {
                $payload = $jwt->decode($token, $api_keys, ['HS256']);

                if (isset($payload->iss) && $payload->iss == 'edemand') {

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
        } else {
            $error = true;
            $flag = false;
            $message = 'Token expired. Please login again';
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

function verify_app_request()
{
    // to verify the token from application
    $responses = \Config\Services::response();
    $jwt = new App\Libraries\JWT;
    try {
        $token = $jwt->getBearerToken();
    } catch (\Exception $e) {
        return [
            "error" => true,
            "message" => $e->getMessage(),
            "status" => 401,
            "data" => []
        ];
    }
    if (!empty($token)) {
        $api_keys = API_SECRET;

        if (empty($api_keys)) {
            return [
                "error" => true,
                "message" => 'No API found !',
                "status" => 401,
                "data" => []
            ];
        }

        $flag = true; //For payload indication that it return some data or throws an expection.
        $error = true; //It will indicate that the payload had verified the signature and hash is valid or not.

        $message = '';
        $status_code = 0;
        $user_token = [];
        try {

            $user_id = $jwt->decode_unsafe($token)->user_id;

            $user_data = fetch_details('users', ['id' => $user_id]);
            // $user_token = $user_data[0]['api_key'];



            $user_token = fetch_details('users_tokens', ['user_id' => $user_id]);
            $db = \Config\Database::connect();
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        foreach ($user_token as $row) {
            try {
                if ($row['token'] == $token) {


                    $payload = $jwt->decode($token, $api_keys, ['HS256']);

                    if (isset($payload->iss)) {
                        $error = false;
                        $flag = false;
                    } else {
                        $error = true;
                        $flag = false;
                        $message = 'Token Expired';
                        $status_code = 403;
                        break;
                    }
                } else {
                    $message = 'Token not verified !!';
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }
        }

        if ($flag) {
            return [
                "error" => true,
                "message" => $message,
                "status" => 401,
                "data" => []
            ];
        } else {
            if ($error == true) {

                return [
                    "error" => true,
                    "message" => $message,
                    "status" => 401,
                    "status_code" => 102,
                    "data" => []
                ];
            } else {
                return [
                    "error" => false,
                    "message" => "Token verified !!",
                    "status" => 200,
                    "data" => isset($user_data[0]) ? $user_data[0] : ''
                ];
            }
        }
    } else {
        return [
            "error" => true,
            "message" => "Unauthorized access not allowed",
            "status" => 401,
            "status_code" => 101,
            "data" => []
        ];
    }
}
function send_web_notification($title, $message, $partner_id = null, $click_action = null)
{
    $api_key = get_settings('api_key_settings', true);

    $db      = \Config\Database::connect();
    $builder = $db->table('users u');

    $users = $builder->Select("u.id,u.web_fcm_id")
        ->join('users_groups ug', 'ug.user_id=u.id')
        ->where('ug.group_id', '1')
        ->get()->getResultArray();
    if (!empty($partner_id)) {

        $partner = fetch_details('users', ['id' => $partner_id], ['web_fcm_id']);
    }

    $settings = get_settings('general_settings', true);
    $icon = $settings['logo'];
    foreach ($users as $key => $users) {
        $fcm_tokens[] = $users['web_fcm_id'];
    }
    $fcm_tokens = array_filter(($fcm_tokens));
    // array_push($fcm_tokens,$partner[0]['fcm_id']);
    if (!empty($partner_id)) {

        array_push($fcm_tokens, $partner[0]['web_fcm_id']);
    }

    $fcm_tokens = (array_values($fcm_tokens));

    //   print_r($fcm_tokens);
    //   die;
    $SERVER_API_KEY = $api_key['firebase_server_key'];
    $headers = [
        'Authorization: key=' . $SERVER_API_KEY,
        'Content-Type: application/json',
    ];

    $request = [
        'data' => ['type' => "new_order"],

        'registration_ids' => ($fcm_tokens),
        'notification' => [
            'title' => $title,
            'body' => $message,
            "click_action" => $click_action,
            "icon" => base_url('public/uploads/site/' . $icon),
            "sound" => 'default',

        ],
    ];
    $dataString = json_encode($request);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
    $res = curl_exec($ch);
    curl_close($ch);
    return false;
}
