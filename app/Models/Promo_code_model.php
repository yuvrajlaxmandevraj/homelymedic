<?php

namespace App\Models;

use CodeIgniter\Model;

class Promo_code_model extends Model
{
    protected $table = 'promo_codes';
    protected $primaryKey = 'id';
    protected $allowedFields = ['partner_id', 'promo_code', 'message', 'start_date', 'end_date', 'no_of_users', 'minimum_order_amount', 'discount', 'discount_type', 'max_discount_amount', 'repeat_usage', 'no_of_repeat_usage', 'image', 'status'];

    public function list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'DESC', $where = [])
    {

        $db      = \Config\Database::connect();
        $builder = $db->table('promo_codes pc');
        $multipleWhere = [];
        $condition = $bulkData = $rows = $tempRow = [];
        if ((isset($search) && !empty($search) && $search != "") || (isset($_GET['search']) && $_GET['search'] != '')) {
            $search = (isset($_GET['search']) && $_GET['search'] != '') ? $_GET['search'] : $search;
            $multipleWhere = [
                'pc.id' => $search,
                'pc.partner_id' => $search,
                'pc.promo_code' => $search,
                'pc.message' => $search,
                'pc.start_date' => $search,
                'pc.end_date' => $search,
            ];
        }


        if (isset($_GET['offset']))
            $offset = $_GET['offset'];

        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }

        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'pc.id') {
                $sort = "pc.id";
            } else {
                $sort = $_GET['sort'];
            }
        }
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }

        $count =  $builder->select(' COUNT(pc.id) as `total` ');
        if (isset($_GET['promocode_filter']) && $_GET['promocode_filter'] != '') {

            $builder->where('pc.status',  $_GET['promocode_filter']);
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }

        $count = $builder->get()->getResultArray();
        $total = $count[0]['total'];


        $builder->select('pc.*,p.username as partner_name');
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        if (isset($_GET['promocode_filter']) && $_GET['promocode_filter'] != '') {

            $builder->where('pc.status',  $_GET['promocode_filter']);
        }
        if ($from_app) {
            $service_record = $builder->join('users p', 'p.id=pc.partner_id', 'left')->orderBy($sort, $order)->get()->getResultArray();
        } else {
            $service_record = $builder->join('users p', 'p.id=pc.partner_id', 'left')->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        }

        
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        if ($from_app == false) {
            // $user1 = fetch_details('users', ["phone" => $_SESSION['identity']],);
            $db      = \Config\Database::connect();
            $builder = $db->table('users u');
            $builder->select('u.*,ug.group_id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->whereIn('ug.group_id', array(1, 3))
                ->where(['phone' => $_SESSION['identity']]);
            $user1 = $builder->get()->getResultArray();

            $permissions = get_permission($user1[0]['id']);
        }
        foreach ($service_record as $row) {
            $operations = "";
            // $image = "";
            // if ((isset($row['image']) && !empty($row['image']))) {
            //     if (check_exists(base_url($row['image']))) {
            //         $image = '<a  href="' . base_url($row['image'])  . '" data-lightbox="image-1"><img height="80px" class="rounded" src="' . base_url($row['image']) . '" alt=""></a>';
            //     }
            // }

            $image = "";
            if ((isset($row['image']) && !empty($row['image']))) {
                if (check_exists(base_url($row['image']))) {
                    $image = '<a  href="' .  base_url($row['image']) . '" data-lightbox="image-1"><img class="o-media__img images_in_card" src="' .  base_url($row['image']) . '" alt="' .     $row['id'] . '"></a>';
                } else {
                    $image = ' <a  href="' .  $row['image'] . '" data-lightbox="image-1"><img class="o-media__img images_in_card" src="' . base_url('public/backend/assets/profiles/default.png') . '"   alt="' .     $row['id'] . '"></a>';
                }
            } else {
                $image .=
                    '<a  href="' .  $row['image'] . '" data-lightbox="image-1"><img class="o-media__img images_in_card" src="' . base_url('public/backend/assets/profiles/default.png') . '"   alt="' .     $row['id'] . '"></a>';
            }

            // if ($from_app == false) {
            //     if ($permissions['delete']['promo_code'] == 1) {


            //         $operations = '<button class="btn btn-danger btn-sm delete-promo_codes delete"> <i class="fa fa-trash" aria-hidden="true"></i> </button> ';
            //     }
            // }
            // if ($from_app == false) {
            //     if ($permissions['update']['promo_code'] == 1) {

            //         if (isset($where['partner_id']) && !empty($where['partner_id'])) {
            //             $operations .= '<button class="btn btn-success btn-sm edit" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#update_modal"><i class="fa fa-pen" aria-hidden="true"></i> </button> ';
            //         }
            //     }
            // }

            
            $operations = "";

            $operations = '<div class="dropdown">
            <a class="" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <button class="btn btn-secondary   btn-sm px-3"> <i class="fas fa-ellipsis-v "></i></button>
            </a>         <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">';


            if ($from_app == false) {



                if ($permissions['update']['promo_code'] == 1) {
                    $operations .= '<a class="dropdown-item edit " data-id="' . $row['id'] . '" data-toggle="modal" data-target="#update_modal"><i class="fa fa-pen mr-1 text-primary"></i> Edit</a>';
                }

                if ($permissions['delete']['promo_code'] == 1) {

                    $operations .= '<a class="dropdown-item delete-promo_codes delete" > <i class="fa fa-trash text-danger mr-1"></i> Delete</a>';
                }
            }
            $operations .= '</div></div>';


            if ($from_app) {
                $status =  ($row['status'] == 1) ? 'Enable' : 'Disable';
                $image = (isset($row['image']) && !empty($row['image'])) ?  base_url($row['image']) : "";
            }

            $repeat_usage_badge = ($row['repeat_usage'] == 1) ?


            "<div class='  text-emerald-success  ml-3 mr-3 mx-5'>Yes
            </div>" :
            "<div class=' text-emerald-danger ml-3 mr-3 '>No
            </div>";


            $status =  ($row['status'] == 1) ? '1' : '0';
            $tempRow['id'] = $row['id'];
            $tempRow['partner_id'] = $row['partner_id'];
            $tempRow['partner_name'] = $row['partner_name'];
            $tempRow['promo_code'] = $row['promo_code'];
            $tempRow['message'] = $row['message'];
            $tempRow['start_date'] = $row['start_date'];
            $tempRow['end_date'] = $row['end_date'];
            $tempRow['no_of_users'] = $row['no_of_users'];
            $tempRow['minimum_order_amount'] = $row['minimum_order_amount'];
            $tempRow['discount'] = $row['discount'];
            $tempRow['discount_type'] = $row['discount_type'];
            $tempRow['max_discount_amount'] = $row['max_discount_amount'];
            $tempRow['repeat_usage'] = $row['repeat_usage'];
            $tempRow['repeat_usage_badge'] = $repeat_usage_badge;

            $tempRow['no_of_repeat_usage'] = $row['no_of_repeat_usage'];
            $tempRow['image'] = $image;
            $tempRow['status'] = $row['status'];
            $tempRow['status'] = $status;

            if (!$from_app) {

                $tempRow['created_at'] = format_date($row['created_at'], 'd-m-Y');;
            } else {
                $tempRow['created_at'] = $row['created_at'];
            }

            
            
            $status_badge = ($row['status'] == 1) ?


                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>Active
                </div>" :
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 '>Deactive
                </div>";
            $tempRow['status_badge'] = $status_badge;
            if (!$from_app) {
                $tempRow['operations'] = $operations;
            }
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        if ($from_app) {
            // if request from app return array 
            $data['total'] = $total;
            $data['data'] = $rows;
            return $data;
        } else {
            // else return json
            return json_encode($bulkData);
        }
    }
    public function admin_list($from_app = false, $search = '', $limit = 10, $offset = 0, $sort = 'id', $order = 'DESC', $where = [])
    {

        $multipleWhere = '';
        $db      = \Config\Database::connect();

        $builder = $db->table('promo_codes pc');
        $values = ['7'];
        if ($search and $search != '') {
            $multipleWhere = [
                'pc.id' => $search,
                'pc.partner_id' => $search,
                'pc.promo_code' => $search,
                'pc.message' => $search,
                'username' => $search,
                'pc.start_date' => $search,
                'pc.end_date' => $search,
            ];
        }









        $builder->select('COUNT(pc.id) as `total` ')->join('users p', 'p.id=pc.partner_id', 'left');
        if (isset($_GET['promocode_filter']) && $_GET['promocode_filter'] != '') {

            $builder->where('pc.status',  $_GET['promocode_filter']);
        }

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }

        $partner_count = $builder->get()->getResultArray();



        $total = $partner_count[0]['total'];






        if (isset($_GET['promocode_filter']) && $_GET['promocode_filter'] != '') {

            $builder->where('pc.status',  $_GET['promocode_filter']);
        }



        $builder->select('pc.*,p.username as partner_name')
            ->join('users p', 'p.id=pc.partner_id', 'left');


        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $builder->groupStart();
            $builder->orLike($multipleWhere);
            $builder->groupEnd();
        }
        if (isset($where) && !empty($where)) {
            $builder->where($where);
        }


        $partner_record = $builder->orderBy($sort, $order)->limit($limit, $offset)->get()->getResultArray();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($partner_record as $row) {




            $image = "";
            if ((isset($row['image']) && !empty($row['image']))) {
                if (check_exists(base_url($row['image']))) {
                    $image = '<a  href="' .  base_url($row['image']) . '" data-lightbox="image-1"><img class="o-media__img images_in_card" src="' .  base_url($row['image']) . '" alt="' .     $row['id'] . '"></a>';
                } else {
                    $image = ' <a  href="' .  $row['image'] . '" data-lightbox="image-1"><img class="o-media__img images_in_card" src="' . base_url('public/backend/assets/profiles/default.png') . '"   alt="' .     $row['id'] . '"></a>';
                }
            } else {
                $image .=
                    '<a  href="' .  $row['image'] . '" data-lightbox="image-1"><img class="o-media__img images_in_card" src="' . base_url('public/backend/assets/profiles/default.png') . '"   alt="' .     $row['id'] . '"></a>';
            }



            // if ($row['image'] != '') {




            //     if (check_exists(base_url($row['image']))) {

            //         $partner_promo_detail .= '<div class="o-media o-media--middle">
            //             <a  href="' .  base_url($row['image']). '" data-lightbox="image-1"><img class="o-media__img images_in_card" src="' .  base_url($row['image']) . '" alt="' .     $row['id'] . '"></a>';
            //     } else {
            //         $row['image'] =   base_url($row['image']);

            //         $partner_promo_detail .= '<div class="o-media o-media--middle">
            //             <a  href="' .  $row['image'] . '" data-lightbox="image-1"><img class="o-media__img images_in_card" src="' . base_url('public/backend/assets/profiles/default.png') . '"   alt="' .     $row['id'] . '"></a>';
            //     }
            // } else {
            //     $row['image'] =   base_url($row['image']);

            //     $partner_promo_detail .= '<div class="o-media o-media--middle">
            //         <a  href="' .  $row['image'] . '" data-lightbox="image-1"><img class="o-media__img images_in_card" src="' . base_url('public/backend/assets/profiles/default.png') . '"   alt="' .     $row['id'] . '"></a>';
            // }
            // if ($row['message'] != '') {
            //     // echo '3';
            //     $message =
            //         '<span>
            //         ' .  $row['message'] . '
            //     </span>';
            // } else {


            //     $message =   $row['message'];
            // }

            // $partner_promo_detail .= '<div class="o-media__body">
            //     <div class="provider_name_table">' .     $row['promo_code'] . '</div>
            //     <div class="provider_email_table">' . $message . '</div>
            //     </div>
            //     </div>';


            if ($from_app == false) {
                // $user1 = fetch_details('users', ["phone" => $_SESSION['identity']],);
                $db      = \Config\Database::connect();
                $builder = $db->table('users u');
                $builder->select('u.*,ug.group_id')
                    ->join('users_groups ug', 'ug.user_id = u.id')
                    ->whereIn('ug.group_id', [3, 1])
                    ->where(['phone' => $_SESSION['identity']]);
                $user1 = $builder->get()->getResultArray();

                $permissions = get_permission($user1[0]['id']);
            }








            $operations = "";

            $operations = '<div class="dropdown">
            <a class="" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <button class="btn btn-secondary   btn-sm px-3"> <i class="fas fa-ellipsis-v "></i></button>
            </a>         <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">';


            if ($from_app == false) {



                if ($permissions['update']['promo_code'] == 1) {
                    $operations .= '<a class="dropdown-item edit "data-id="' . $row['id'] . '" data-toggle="modal" data-target="#update_modal"><i class="fa fa-pen mr-1 text-primary"></i> Edit</a>';
                }

                if ($permissions['delete']['promo_code'] == 1) {

                    $operations .= '<a class="dropdown-item delete-promo_codes" > <i class="fa fa-trash text-danger mr-1"></i> Delete</a>';
                }
            }
            $operations .= '</div></div>';

            if ($from_app) {
                $status =  ($row['status'] == 1) ? 'Enable' : 'Disable';
                $image = (isset($row['image']) && !empty($row['image'])) ?  base_url($row['image']) : "";
            }


            $status_badge = ($row['status'] == 1) ?


                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-success text-emerald-success dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 mx-5'>Active
            </div>" :
                "<div class='tag border-0 rounded-md ltr:ml-2 rtl:mr-2 bg-emerald-danger text-emerald-danger dark:bg-emerald-500/20 dark:text-emerald-100 ml-3 mr-3 '>Deactive
            </div>";


            $status =  ($row['status'] == 1) ? 'Active' : 'Deactive';
            $tempRow['id'] = $row['id'];
            // $tempRow['partner_promo_detail'] = $partner_promo_detail;

            $tempRow['partner_id'] = $row['partner_id'];
            $tempRow['partner_name'] = $row['partner_name'];
            $tempRow['promo_code'] = $row['promo_code'];
            $tempRow['message'] = $row['message'];
            $tempRow['start_date'] = format_date($row['start_date'], 'd-m-Y');
            $tempRow['end_date'] = format_date($row['end_date'], 'd-m-Y');
            $tempRow['no_of_users'] = $row['no_of_users'];
            $tempRow['minimum_order_amount'] = $row['minimum_order_amount'];
            $tempRow['discount'] = $row['discount'];
            $tempRow['discount_type'] = $row['discount_type'];
            $tempRow['max_discount_amount'] = $row['max_discount_amount'];
            $tempRow['repeat_usage'] = $row['repeat_usage'];
            $tempRow['no_of_repeat_usage'] = $row['no_of_repeat_usage'];
            $tempRow['image'] = $image;
            // $tempRow['status'] = $row['status'];
            $tempRow['status'] = $status;
            $tempRow['status_badge'] = $status_badge;
            $tempRow['created_at'] = $row['created_at'];
            if (!$from_app) {
                $tempRow['operations'] = $operations;
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
