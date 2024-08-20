<?php $data = get_settings('general_settings', true);
// $user1 = fetch_details('users', ["phone" => $_SESSION['identity']],);
$db      = \Config\Database::connect();
$builder = $db->table('users u');
$builder->select('u.*,ug.group_id')
    ->join('users_groups ug', 'ug.user_id = u.id')
    ->where('ug.group_id', 1)
    ->where(['phone' => $_SESSION['identity']]);
$user1 = $builder->get()->getResultArray();


$permissions = get_permission($user1[0]['id']);
$current_url = current_url();
?>




<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
<!-- <div class="navbar-bg"></div> -->
<nav class="navbar new_nav_bar navbar-expand-lg main-navbar">
    <form class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars text-new-primary"></i></a></li>
            <?php
            if ($_SESSION['email'] == "rajasthantech.info@gmail.com") {
                defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1;
            } else if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) { ?>
                <li class="nav-item my-auto ml-2">
                    <span class="badge badge-danger" style="border-radius: 8px!important">Demo mode</span>
                </li>
            <?php  } ?>
            <li><a href="#" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i class="fas fa-search"></i></a></li>
        </ul>
    </form>
    <ul class="navbar-nav navbar-right">
        <li class="dropdown navbar_dropdown mr-2">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <div class="d-inline-block"><?= strtoupper($current_lang) ?>
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right ">
                <?php foreach ($languages_locale as $language) { ?>
                    <span onclick="set_locale('<?= $language['code'] ?>')" class="dropdown-item has-icon <?= ($language['code'] == $current_lang) ? "text-primary" : "" ?>">
                        <?= strtoupper($language['code']) . " - "  . ucwords($language['language']) ?>
                    </span>
                <?php } ?>
            </div>
        </li>
        <li class="dropdown navbar_dropdown">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <img src="<?= base_url("/public/backend/assets/profiles/" . $user1[0]['image']) ?>" class="sidebar_logo h-max-60px navbar_image" alt="no image">

                <div class="d-inline-block"><?= labels('hello', 'Hi') ?> , <?= $user1[0]['username'] ?>
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="<?= base_url('admin/profile') ?>" class="dropdown-item has-icon">
                    <i class="far fa-user"></i> <?= labels('profile', "Profile") ?>
                </a>
                <div class="dropdown-divider"></div>
                <a href="<?= base_url('auth/logout') ?>" class="dropdown-item has-icon text-danger">
                    <i class="fas fa-sign-out-alt"></i> <?= labels('logout', "Logout") ?>
                </a>
            </div>
        </li>
    </ul>
</nav>
<div class="main-sidebar">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="<?= base_url('admin/') ?>">
                <img src=" <?= isset($data['logo']) && $data['logo'] != "" ? base_url("public/uploads/site/" . $data['logo']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" class="sidebar_logo h-max-60px" alt="">
            </a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="<?= base_url('admin/') ?>">
                <img src="<?= isset($data['half_logo']) && $data['half_logo'] != "" ? base_url("public/uploads/site/" . $data['half_logo']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" height="40px" alt="">
            </a>
        </div>
        <ul class="sidebar-menu">
            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('/admin/dashboard/') ?>">
                    <span class="material-symbols-outlined mr-1 ">
                        home
                    </span>


                    <span class="span"><?= labels('Dashboard', 'Dashboard') ?></span>
                </a>
            </li>




            <?php if ($permissions['read']['partner'] == 1) { ?>
                <label for="provider management" class="heading_lable"><?= labels('provider_management', 'PROVIDER MANAGEMENT') ?></label>
                <li class="dropdown <?= ($current_url == base_url('/admin/partners') || $current_url == base_url('/admin/partners/add_partner')) ? 'active' : '' ?>">
                    <a href="#" class="nav-link has-dropdown " data-toggle="dropdown">
                        <span class="material-symbols-outlined ">
                            engineering 
                        </span>
                        <span class="span hide-on-mini"><?= labels('providers', 'Providers') ?></span>
                    </a>
                    <ul class="dropdown-menu <?= ($current_url == base_url('/admin/partners') || $current_url == base_url('/admin/partners/add_partner')) ? 'dropdown-active-open-menu' : '' ?>">
                        <?php if ($permissions['read']['partner'] == 1) { ?>
                            <li><a class="nav-link" href="<?= base_url('/admin/partners'); ?>">- <span><?= labels('provider_list', 'Provider List') ?></span></a></li>
                        <?php } ?>
                        <?php if ($permissions['create']['partner'] == 1) { ?>
                            <li><a class="nav-link" href="<?= base_url('/admin/partners/add_partner'); ?>">- <span><?= labels('add_new_provider', 'Add New Providers') ?></span></a></li>
                        <?php } ?>
                    </ul>
                </li>

                <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/partners/payment_request'); ?>">
                        <span class="material-symbols-outlined">
                            payments
                        </span><span class="span"><?= labels('payment_request', "Payment Request") ?></span></a>
                </li>



            <?php }     ?>




            <li class="dropdown <?= ($current_url ==  base_url('admin/partners/settle_commission') || $current_url ==  base_url('admin/partners/manage_commission_history')) ? 'active' : '' ?>">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                    <span class="material-symbols-outlined">
                        receipt_long
                    </span><span class="span"><?= labels('manage_commission', "Settlements") ?></span>
                </a>
                <ul class="dropdown-menu <?= ($current_url ==  base_url('admin/partners/settle_commission') || $current_url ==  base_url('admin/partners/manage_commission_history')) ? 'dropdown-active-open-menu' : '' ?>">

                    <li>
                        <a class="nav-link" href="<?= base_url('admin/partners/settle_commission'); ?>">

                            <span class="span">- <?= labels('manage_commission', "Settlements") ?></span></a>
                    </li>

                    <li>
                        <a class="nav-link" href="<?= base_url('admin/partners/manage_commission_history') ?>">
                            <span class="span">- <?= labels('settlement_history', ' Settlement History') ?></span></a>
                    </li>
                </ul>
            </li>


            <li class="dropdown <?= ($current_url ==  base_url('admin/partners/cash_collection') || $current_url == base_url('admin/partners/cash_collection_history')) ? 'active' : '' ?>">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                    <span class="material-symbols-outlined">
                        universal_currency_alt</span>
                    <span class="span"><?= labels('cash_collection', "Cash Collection") ?></span>
                </a>
                <ul class="dropdown-menu <?= ($current_url ==  base_url('admin/partners/cash_collection') || $current_url == base_url('admin/partners/cash_collection_history')) ? 'dropdown-active-open-menu' : '' ?>" style="display: none;">
                    <li>
                        <a class="nav-link" href="<?= base_url('admin/partners/cash_collection') ?>">

                            <span class="span">- <?= labels('cash_collection', "Cash Collection") ?></span></a>
                    </li>

                    <li>
                        <a class="nav-link" href="<?= base_url('admin/partners/cash_collection_history') ?>">

                            <span class="span">- <?= labels('cash_collection_hs', "Cash Collection List") ?></span></a>
                    </li>



                </ul>
            </li>



            <?php if ($permissions['read']['orders'] == 1) { ?>
                <label for="provider management" class="heading_lable"><?= labels('booking_management', 'BOOKING MANAGEMENT') ?></label>

                <li class="nav-item"><a class="nav-link" href="<?= base_url('/admin/orders') ?>"><span class="material-symbols-outlined">
                            list_alt
                        </span><span class="span"> <?= labels('bookings', 'Bookings') ?></span></span></a></li>

            <?php } ?>





            <?php if ($permissions['read']['services'] == 1) { ?>

                <label for="provider management" class="heading_lable"><?= labels('service_management', 'SERVICE MANAGEMENT') ?></label>



                <li class="dropdown <?= ($current_url ==   base_url('/admin/services/add_service') || $current_url == base_url("admin/services") || $current_url == base_url("admin/categories")) ? 'active' : '' ?>">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <span class="material-symbols-outlined">
                            list
                        </span><span class="span"><?= labels('service', 'Service') ?></span>
                    </a>
                    <ul class="dropdown-menu <?= ($current_url ==   base_url('/admin/services/add_service') || $current_url == base_url("admin/services") || $current_url == base_url("admin/categories")) ? 'dropdown-active-open-menu' : '' ?>">
                        <li class="nav-item"><a class="nav-link" href="<?= base_url("admin/services"); ?>">- <span><?= labels('service_list', 'Services List') ?></span></a></li>
                        <?php if ($permissions['create']['services'] == 1) { ?>
                            <li class="nav-item"><a class="nav-link" href="<?= base_url('/admin/services/add_service'); ?>">- <span><?= labels('add_new_service', 'Add New Service') ?></span></a></li>
                        <?php } ?>
                        <?php if ($permissions['read']['categories'] == 1) { ?>

                            <li class="nav-item" class="span"><a class="nav-link" href="<?= base_url("admin/categories"); ?>">- <span><?= labels('service_categories', 'Service Categories') ?></span></a></li>
                        <?php } ?>

                    </ul>
                </li>
            <?php } ?>



            <label for="provider management" class="heading_lable"><?= labels('home_screen_management', 'HOME SCREEN MANAGEMENT') ?></label>
            <?php if ($permissions['read']['sliders'] == 1) { ?>

                <li class="nav-item"><a class="nav-link" href="<?= base_url('/admin/sliders'); ?>"><span class="material-symbols-outlined">
                            view_day
                        </span><span class="span"><?= labels('sliders', 'Sliders') ?></span></span></a></li>
            <?php } ?>

            <?php if ($permissions['read']['featured_section'] == 1) { ?>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/admin/Featured_sections') ?>"><span class="material-symbols-outlined">
                            view_comfy
                        </span> <span class="span"><?= labels('featured', 'Featured Section') ?></span></span></a></li>
            <?php } ?>


            <label for="provider management" class="heading_lable"><?= labels('promotional_management', 'PROMOTIONAL MANAGEMENT') ?></label>
            <?php if ($permissions['read']['promo_code'] == 1) { ?>

                <li class="nav-item"><a class="nav-link" href="<?= base_url('/admin/promo_codes'); ?>"><span class="material-symbols-outlined">
                            sell
                        </span><span class="span"><?= labels('promocode', 'Promo codes') ?>
                        </span></span></a>
                </li>

            <?php } ?>

            <?php if ($permissions['read']['send_notification'] == 1) { ?>

                <li>
                    <a class="nav-link" href="<?= base_url('/admin/notification'); ?>"><span class="material-symbols-outlined">
                            phone_iphone
                        </span><span class="span"><?= labels('send_notifications', "Send Notifications") ?></span></span></a>
                </li>
            <?php } ?>

            <label for="provider management" class="heading_lable"><?= labels('subscription_management', 'SUBSCRIPTION MANAGEMENT') ?></label>


            <?php if ($permissions['read']['subscription'] == 1) { ?>

                <li class="dropdown  <?= ($current_url ==   base_url('admin/subscription/') || $current_url == base_url('admin/subscription/subscriber_list') || $current_url == base_url('admin/subscription/add_subscription')) ? 'active' : '' ?>">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><span class="material-symbols-outlined">
                            package_2
                        </span> <span class="span"><?= labels('subscription', "Subscription") ?></span></a>
                    <ul class="dropdown-menu <?= ($current_url ==   base_url('admin/subscription/') || $current_url == base_url('admin/subscription/subscriber_list') || $current_url == base_url('admin/subscription/add_subscription')) ? 'dropdown-active-open-menu' : '' ?>" style="display: none;">

                        <li><a class="nav-link" href="<?= base_url('admin/subscription/') ?>"><span>-<?= labels('list_subscription', "List Subscription") ?></span></span></a></li>
                        <li><a class="nav-link" href="<?= base_url('admin/subscription/subscriber_list'); ?>">-<span><?= labels('subscriber_list', "Subscriber List") ?></span></span></a></li>
                        <?php if ($permissions['create']['subscription'] == 1) { ?>
                            <li><a class="nav-link" href="<?= base_url('admin/subscription/add_subscription'); ?>">-<span><?= labels('add_subscription', "Add Subscription") ?></span></span></a></li>
                        <?php } ?>
                    </ul>
                </li>


            <?php } ?>


            <label for="provider management" class="heading_lable"><?= labels('system_management', 'SYSTEM MANAGEMENT') ?></label>



            <?php if ($permissions['read']['settings'] == 1) { ?>

                <li>
                    <a class="nav-link" href="<?= base_url('admin/settings/system-settings') ?>"><span class="material-symbols-outlined">
                            settings
                        </span><span class="span"><?= labels('system_settings', "System Settings") ?></span></span></a>
                </li>
            <?php } ?>


            <?php if ($permissions['read']['faq'] == 1) { ?>

                <li>
                    <a class="nav-link" href="<?= base_url('admin/faqs') ?>"><span class="material-symbols-outlined">
                            help
                        </span><span class="span"><?= labels('faqs', "FAQs") ?></span></span></a>
                </li>
            <?php } ?>
            <?php if ($permissions['read']['system_user'] == 1) { ?>

                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('/admin/system_users'); ?>"><span class="material-symbols-outlined">
                            contact_emergency
                        </span><span class="span"><?= labels('system_user', 'System Users') ?></span></span></a>
                </li>

            <?php } ?>


            <label for="provider management" class="heading_lable"><?= labels('customer_management', 'CUSTOMER MANAGEMENT') ?></label>
            <?php if ($permissions['read']['customers'] == 1) { ?>
                <li><a class="nav-link" href="<?= base_url('/admin/users/'); ?>"><span class="material-symbols-outlined">
                            tv_signin
                        </span><span class="span"><?= labels('customers', "Customers") ?></span></span></a></li>
                <li><a class="nav-link" href="<?= base_url('/admin/transactions'); ?>"><span class="material-symbols-outlined">
                            receipt
                        </span><span class="span"><?= labels('transactions', "Transactions") ?></span></span></a></li>
                <li><a class="nav-link" href="<?= base_url('/admin/addresses'); ?>"><span class="material-symbols-outlined">
                            pin_drop
                        </span><span class="span"><?= labels('addresses', 'Addresses') ?></span></a></li>


            <?php } ?>


        </ul>
    </aside>
</div>