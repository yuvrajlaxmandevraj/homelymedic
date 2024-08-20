<?php

namespace App\Models;

use CodeIgniter\Model;



class Service_ratings_model extends Model
{
    protected $table = 'services_ratings';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'service_id', 'rating', 'comment', 'images'];

    public function ratings_list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'ASC', $where = [], $column_name = 'id', $whereIn = [], $additional_data = [])
    {
        $multipleWhere = '';
        $db      = \Config\Database::connect();

        $builder = $db->table('services_ratings sr');

        if ($search and $search != '') {
            $multipleWhere = [
                '`sr.id`' => $search,
                '`sr.user_id`' => $search,
                '`sr.rating`' => $search,
                '`sr.comment`' => $search,
                '`sr.created_at`' => $search,
                '`u.username`' => $search,
                '`s.title`' => $search,
            ];
        }

        $builder->select(' COUNT(sr.id) as `total` ')
            ->join('users u', 'u.id = sr.user_id')
            ->join('services s', 's.id = sr.service_id');

            if (isset($multipleWhere) && !empty($multipleWhere)) {
                $builder->orLike($multipleWhere);
            }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($whereIn) && !empty($whereIn)) {
            $builder->whereIn($column_name, $whereIn);
        }

        if (isset($_GET['rating_star_filter']) && $_GET['rating_star_filter'] != '') {
            // echo '1';
            $builder->where('sr.rating', $_GET['rating_star_filter']);
        }
        $ratings_total_count = $builder->get()->getResultArray();


        $total = $ratings_total_count[0]['total'];

        

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart()->orLike($multipleWhere)->groupEnd();
            // $builder->orLike($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($whereIn) && !empty($whereIn)) {
            $builder->whereIn($column_name, $whereIn);
        }

        $builder->select(
            'sr.*,
            u.image as profile_image, 
            u.username, 
            s.user_id as partner_id,
            s.title as service_name' 
        )
            ->join('users u', 'u.id = sr.user_id')
            ->join('services s', 's.id = sr.service_id');

            if (isset($_GET['rating_star_filter']) && $_GET['rating_star_filter'] != '') {
                // echo '1';
                $builder->where('sr.rating', $_GET['rating_star_filter']);
            }
            
        $rating_records = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();


        foreach ($rating_records as $row) {

         

            $partner_name = fetch_details('users', ['id' => $row['partner_id']], ['username'])[0]['username'];

            $tempRow['id'] = $row['id'];
            $tempRow['partner_id'] = $row['partner_id'];
            $tempRow['partner_name'] = $partner_name;
            $tempRow['user_name'] = $row['username'];

            // if ($row['profile_image'] != "") {
            //     if (check_exists(base_url('public/backend/assets/profiles/' . $row['profile_image'])) || check_exists(base_url('/public/uploads/users/partners/' . $row['profile_image'])) || check_exists($row['profile_image'])) {
            //         if (filter_var($row['profile_image'], FILTER_VALIDATE_URL)) {
            //             $tempRow['profile_image'] = base_url($row['profile_image']);
            //         } else {
            //             $row['image'] = (file_exists(FCPATH . 'public/backend/assets/profiles/' . $row['profile_image'])) ? base_url('public/backend/assets/profiles/' . $row['profile_image']) : ((file_exists(FCPATH . $row['profile_image'])) ? base_url($row['profile_image']) : ((!file_exists(FCPATH . "public/uploads/users/partners/" . $row['profile_image'])) ? base_url("public/backend/assets/profiles/default.png") : base_url("public/uploads/users/partners/" . $row['profile_image'])));
            //             $tempRow['profile_image'] = base_url($row['profile_image']);
            //         }
            //     }
            // } else {
            //     $tempRow['profile_image'] = base_url("public/backend/assets/profiles/default.png");
            // }


                        
            if (!empty($row['profile_image'])) {
                $imagePath =  'public/backend/assets/profiles/' . $row['profile_image'];
            
                if (check_exists(base_url($imagePath)) || check_exists(base_url('/public/uploads/users/partners/' . $row['profile_image'])) || check_exists($imagePath)) {
                    if (filter_var($row['profile_image'], FILTER_VALIDATE_URL)) {
                        $tempRow['profile_image'] = base_url($row['profile_image']);
                    } else {
                        $imagePath = (file_exists($imagePath)) ? $imagePath : FCPATH . $row['profile_image'];
                        $tempRow['profile_image'] = base_url($imagePath);
                    }
                }
            } else {
                $tempRow['profile_image'] = base_url("public/backend/assets/profiles/default.png");
            }
            
            

            // $rating_data = $db->table('services s')
            //     ->select('
            //     count(sr.rating) as number_of_rating, 
            //     SUM(sr.rating) as total_rating,
            //     (SUM(sr.rating) / count(sr.rating)) as average_rating
            //     ')
            //     ->join('services_ratings sr', 'sr.service_id = s.id')
            //     ->where('s.user_id', $row['partner_id'])
            //     ->get()->getResultArray();
                
            // $tempRow['profile_image'] = ($row['profile_image'] != "") ? base_url($row['profile_image']) : '';

            $tempRow['user_id'] = $row['user_id'];
            $tempRow['service_id'] = $row['service_id'];
            $tempRow['service_name'] = $row['service_name'];
            $tempRow['rating'] = $row['rating'];
            // $rating_data['total_rating'] = $row['total_rating'];
            // $rating_data['average_rating'] = $row['average_rating'];
            $tempRow['comment'] = ($row['comment'] != "") ? $row['comment'] : "";
            $tempRow['rated_on'] = $row['created_at'];
            $tempRow['rate_updated_on'] = $row['updated_at'];


            // if ($from_app == false) {

            //     $tempRow['ratings'] = '<i class="fa-solid fa-star text-warning"></i>(' . (($rating_data[0]['average_rating'] != "") ? sprintf('%0.1f', $rating_data[0]['average_rating']) : '0.0') . ')';

            //     // $tempRow['stars'] = '
            //     // <div class="partner-rating" id= "' . $row['id']  . '" data-value="' .  $tempRow['ratings']  . '"> </div>
            //     // <span> (' . number_format($tempRow['ratings'], 1)  . '/' .  $tempRow['number_of_ratings'] . ') </span>
            //     // ';
            // } else {
            //     $tempRow['ratings'] =  (($rating_data[0]['average_rating'] != "") ? sprintf('%0.1f', $rating_data[0]['average_rating']) : '0.0');
            // }

            if ($from_app == false) {
                $tempRow['stars'] = '<i class="fa-solid fa-star text-warning"></i>'.  $row['rating'];
                // $tempRow['operations'] = '
                // <button class="btn btn-danger delete_rating btn-sm" title="Delete Rating">
                // <i class="fa fa-trash" aria-hidden="true"></i>  
                // </button>';
                if ($row['images'] != "") {
                    $images =  rating_images($row['id'], false);
                    $tempRow['images'] = $images;
                } else {
                    $tempRow['images'] = array();
                }
            } else {
                if ($row['images'] != "") {
                    $images =  rating_images($row['id'], true);
                    $tempRow['images'] = $images;
                } else {
                    $tempRow['images'] = array();
                }
            }
            $rows[] = $tempRow;
        }
        if ($from_app) {
     
            $response['total'] = $total;
            $response['data'] = $rows;
            return $response;
        } else {
            $bulkData['rows'] = $rows;
        }

    
        return $bulkData;
    }
}
