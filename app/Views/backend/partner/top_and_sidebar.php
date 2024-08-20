<?php
$data = get_settings('general_settings', true);

$user1 = fetch_details('users', ["phone" => $_SESSION['identity']],);
// $user_group = fetch_details('users_groups', ["user_id" => $user1[0]['id'],'group_id'=>3],);

$db      = \Config\Database::connect();

$builder = $db->table('users u');
$builder->select('u.*,ug.group_id')
    ->join('users_groups ug', 'ug.user_id = u.id')
    ->where('u.phone', $_SESSION['identity'])
    ->where('ug.group_id', "3");
$user1 = $builder->get()->getResultArray();



$provider = fetch_details('partner_details', ["partner_id" => $user1[0]['id']],);
$current_url = current_url();
?>
<nav class="navbar new_nav_bar navbar-expand-lg main-navbar">
    <form class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg text-new-primary"><i class="fas fa-bars"></i></a></li>
            <li><a href="#" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i class="fas fa-search"></i></a></li>
        </ul>
    </form>
    <ul class="navbar-nav navbar-right">
        <ul class="navbar-nav navbar-right">
            <li class="dropdown navbar_dropdown mr-2">
                <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">

                    <div class="d-inline-block"><?= strtoupper($current_lang) ?>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <?php foreach ($languages_locale as $language) { ?>
                        <span onclick="set_locale('<?= $language['code'] ?>')" class="dropdown-item has-icon <?= ($language['code'] == $current_lang) ? "text-primary" : "" ?>">
                            <?= strtoupper($language['code']) . " - "  . ucwords($language['language']) ?>
                        </span>
                    <?php } ?>
                </div>
            </li>
            <li class="dropdown navbar_dropdown ">
                <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">


                    <img src="<?= base_url($provider[0]['banner'])  ?>" class="sidebar_logo h-max-60px navbar_image" alt="no image">
                    <div class="d-inline-block"><?= labels('hello', 'Hi') ?> ,<?= $provider[0]['company_name'] ?>
                    </div>

                    <!-- <div class="d-inline-block"><?= labels('hello', 'Hi') ?> , <span id="header_name"><?= $provider[0]['company_name'] ?></span>
                </div> -->
                </a>


                <div class="dropdown-menu dropdown-menu-right">

                    <a href="<?= base_url('partner/profile') ?>" class="dropdown-item has-icon">
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
            <a href="<?= base_url('partner/') ?>">
                <img src="<?= isset($data['partner_logo']) && $data['partner_logo'] != "" ? base_url("public/uploads/site/" . $data['partner_logo']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" class="sidebar_logo h-max-60px" alt="Logo">
            </a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="<?= base_url('partner/') ?>">
                <img src="<?= isset($data['partner_half_logo']) && $data['partner_half_logo'] != "" ? base_url("public/uploads/site/" . $data['partner_half_logo']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" height="40px" alt="logo">
            </a>
        </div>
        <ul class="sidebar-menu">
            <li class="nav-item"><a class="nav-link" href="<?= base_url('/partner') ?>">      <span class="material-symbols-outlined mr-1">
                        home
                    </span>
 <span class="span"><?= labels('Dashboard', "Dashboard") ?></span></span></a></li>

            <label for="provider management" class="heading_lable"><?= labels('booking_management', 'BOOKING MANAGEMENT') ?></label>
            <li>
                <a class="nav-link" href="<?= base_url('partner/orders') ?>">
                    <span class="material-symbols-outlined">
                        list_alt
                    </span>
                    <span class="span"><?= labels('bookings', "Bookings") ?></span></span></a>
            </li>
            <label for="provider management" class="heading_lable"><?= labels('service_management', 'SERVICE MANAGEMENT') ?></label>


            <li class="dropdown <?= ($current_url ==   base_url('partner/services')|| $current_url == base_url('partner/services/add')|| $current_url==base_url('partner/categories')) ? 'active' : '' ?>">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                    <span class="material-symbols-outlined">
                        list
                    </span><span class="span"><?= labels('service', 'Service') ?></span>
                </a>
                <ul class="dropdown-menu  <?= ($current_url ==   base_url('partner/services')|| $current_url == base_url('partner/services/add')|| $current_url==base_url('partner/categories')) ? 'dropdown-active-open-menu' : '' ?>" style="display: none;">
                    <li>
                        <a class="nav-link" href="<?= base_url('partner/services') ?>">- <span><?= labels('service_list', 'Services List') ?></span></span></a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('partner/services/add'); ?>">- <span><?= labels('add_new_service', 'Add New Service') ?></span></a></li>
                    <li>
                        <a class="nav-link" href="<?= base_url('partner/categories') ?>">- <span><?= labels('service_categories', 'Service Categories') ?></span></span></a>
                    </li>
                </ul>
            </li>


            <label for="provider management" class="heading_lable"><?= labels('promotional_management', 'PROMOTIONAL MANAGEMENT') ?></label>
            <li class="dropdown <?= ($current_url ==   base_url('partner/promo_codes')|| $current_url ==  base_url('partner/promo_codes/add')) ? 'active' : '' ?>">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                    <span class="material-symbols-outlined">
                        sell
                    </span><span class="span"><?= labels('promocode', "Promo Codes") ?></span>

                </a>
                <ul class="dropdown-menu <?= ($current_url ==   base_url('partner/promo_codes')|| $current_url ==  base_url('partner/promo_codes/add')) ? 'dropdown-active-open-menu' : '' ?>" style="display: none;">
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('partner/promo_codes') ?>">- <span><?= labels('promocode', "Promo Codes") ?></span></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('partner/promo_codes/add'); ?>">- <span><?= labels('add_promocodes', 'Add Promocodes') ?></span></a></li>
                </ul>
            </li>


            <label for="provider management" class="heading_lable"><?= labels('review_managment', 'REVIEW MANAGEMENT') ?></label>
            <li>
                <a class="nav-link" href="<?= base_url('partner/review') ?>"><span class="material-symbols-outlined">
                        star
                    </span><span class="span"><?= labels('review', "Reviews") ?></span></span></a>
            </li>


            <label for="provider management" class="heading_lable"><?= labels('financial_management', 'FINANCIAL MANAGEMENT') ?></label>
            <li>
                <a class="nav-link" href="<?= base_url('partner/withdrawal_requests') ?>"><span class="material-symbols-outlined">
                        account_balance_wallet
                    </span><span class="span"><?= labels('withdraw_requests', "Withdraw Requests") ?></span></span></a>
            </li>


            <li>
                <a class="nav-link" href="<?= base_url('partner/cash_collection') ?>"><span class="material-symbols-outlined">
                        add_card
                    </span><span class="span"><?= labels('cash_collection', "Cash Collection ") ?></span></span></a>
            </li>

            <li>
                <a class="nav-link" href="<?= base_url('partner/settlement') ?>"><span class="material-symbols-outlined">
                        handshake
                    </span><span class="span"><?= labels('settlement', "Settlement") ?></span></span></a>
            </li>

            <label for="provider management" class="heading_lable"><?= labels('subscription_management', 'SUBSCRIPTION MANAGEMENT') ?></label>





            <li class="dropdown  <?= ($current_url ==    base_url('partner/subscription')|| $current_url ==  base_url('partner/subscription_history')) ? 'active' : '' ?>">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                    <span class="material-symbols-outlined">
                    package_2
                    </span><span class="span"><?= labels('subscription', "Subscription") ?></span>

                </a>
                <ul class="dropdown-menu <?= ($current_url ==    base_url('partner/subscription')|| $current_url ==  base_url('partner/subscription_history')) ? 'dropdown-active-open-menu' : '' ?>" style="display: none;">
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('partner/subscription') ?>">- <span><?= labels('subscription', "Subscription") ?></span></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('partner/subscription_history') ?>">- <span><?= labels('subscription_history', "Subscription History") ?></span></a></li>
                </ul>
            </li>






        </ul>
    </aside>
</div>