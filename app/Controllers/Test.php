<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;
use App\Models\Tickets_model;
use App\Controllers\Auth;
use App\Models\Partners_model;
use App\Models\Service_ratings_model;

class Test extends BaseController
{

    public function index()
    {
        echo "<pre>";
        $data =  get_service_details(41);
        // print_r($data);
        $time_calc = 0;
        for ($i = 0; $i < count($data); $i++) {
            // print_r($data[$i]['duration']);
            // echo $data[$i]['duration'] . "<br>";
            $time_calc    += (int)$data[$i]['duration'];
        }
        print_r($time_calc . "<br>");
        $db      = \Config\Database::connect();
        $order_data = $db->table('orders')->select('*')->where('id', '41')->get()->getResultArray();
        // print_r($order_data);
        $mins = "60";
        $end_time = $order_data[0]['ending_time'];
        $time_to_add = "+$mins minutes";
        $new_end_time = strtotime($time_to_add, strtotime($end_time));

        // print_r($new_end_time);
        echo date('h:i:s', $new_end_time);
        $partner_id = fetch_details('orders',['id'=>'41'],['partner_id'])[0]['partner_id'];
        print_r($partner_id);
    }

    public function test_update_ranking()
    {
        $service_id = 20;
        $rating = 4.5;
        $data = update_ratings($service_id, $rating);


        if ($data) {
            print_r('updated');
        } else {
            print_r('not');
        }
    }

    public function test_for_imgs()
    {
        echo "<pre>";
        $rating_id = 17;
        $data =  rating_images($rating_id, false);
        print_r($data);
    }

    public function fav_list()
    {
        $data =  favorite_list(1);
        print_r($data);
    }
    public function test_distance()
    {
        $user_cred = fetch_details('users', ['id' => '50'])[0];
        $lat1 = $user_cred['latitude'];
        $lon1 = $user_cred['longitude'];

        $Bhuj_creds = fetch_details('cities', ['id' => '18'])[0];
        $lat2 = $Bhuj_creds['latitude'];
        $lon2 = $Bhuj_creds['longitude'];
        $unit = 'k';

        $data =  distance_finder($lat1, $lon1, $lat2, $lon2, $unit);
        print_r($data);
    }
}
