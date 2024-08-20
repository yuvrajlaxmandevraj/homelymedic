<!-- Main Content new-->
<div class="main-content">
    <section class="section" id="pill-general_settings" role="tabpanel">
        <div class="section-header mt-2">
            <h1><?= labels('general_settings', 'General Settings') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('admin/settings/general-settings') ?>"><?= labels('general_settings', "General Settings") ?></a></div>
            </div>
        </div>
        <ul class="justify-content-start nav nav-fill nav-pills pl-3 py-2 setting" id="gen-list">
            <div class="row">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="<?= base_url('admin/settings/general-settings') ?>" id="pills-general_settings-tab" aria-selected="true">
                        <?= labels('general_settings', "General Settings") ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/settings/about-us') ?>" id="pills-about_us" aria-selected="false">
                        <?= labels('about_us', "About Us") ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('admin/settings/contact-us') ?>" id="pills-about_us" aria-selected="false">
                        <?= labels('contact_us', "Contact Us") ?></a>
                </li>
            </div>
        </ul>
        <?= form_open_multipart(base_url('admin/settings/general-settings')) ?>
        <div class="row mb-3 mb-sm-3 mb-md-3 mb-xxs-12">
            <!-- company settings  -->
            <div class="col-lg-4 col-md-12 col-sm-12 col-xl-4 mb-md-3 mb-sm-3  mb-3">
                <div class="card h-100 ">
                    <div class="row pl-3">
                        <div class="col mb-3 " style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('company_settings', 'Company Settings') ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for='company_title'><?= labels('company_title', "Company Title") ?></label>
                                    <input type='text' class="form-control custome_reset" name='company_title' id='company_title' value="<?= isset($company_title) ? $company_title : '' ?>" />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for='support_name'><?= labels('support_name', "Support Name") ?></label>
                                    <input type='text' class="form-control custome_reset" name='support_name' id='support_name' value="<?= isset($support_name) ? $support_name : '' ?>" />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for='support_email'><?= labels('support_email', "support Email") ?></label>
                                    <input type='email' class="form-control custome_reset" name='support_email' id='support_email' value="<?= isset($support_email) ? $support_email : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="phone"><?= labels('mobile', "Phone") ?></label>
                                    <input type="number" min="0" class="form-control custome_reset" name="phone" id="phone" value="<?= isset($phone) ? $phone : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <input type="hidden" id="set" value="<?= isset($system_timezone) ? $system_timezone : 'Asia/Kolkata' ?>">
                                    <input type="hidden" name="system_timezone_gmt" value="<?= isset($system_timezone_gmt) ? $system_timezone_gmt : '' ?>" id="system_timezone_gmt" value="<?= isset($system_timezone_gmt) ? $system_timezone_gmt : '+05:30' ?>" />
                                    <label for='timezone'><?= labels('select_time_zone', "Select Time Zone") ?></label>
                                    <select class='form-control selectric ' name='system_timezone' id='timezone' value="">
                                        <option value="">-- <?= labels('select_time_zone', "Select Time Zone") ?> --</option>
                                        <?php foreach ($timezones as $row) { ?>
                                            <option value="<?= $row[2] ?>" data-gmt="<?= $row[1] ?>"><?= $row[1] ?> - <?= $row[0] ?> - <?= $row[2] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_serviceable_distance"><?= labels('max_Serviceable_distance_in_kms', "Max Serviceable Distance (in Kms)") ?></label>
                                    <input type="number" class="form-control custome_reset" name="max_serviceable_distance" id="max_serviceable_distance" value="<?= isset($max_serviceable_distance) ? $max_serviceable_distance : '' ?>" />
                                    <label for="max_serviceable_distance" class="text-danger"><?= labels('note_this_distance_is_used_while_search_nearby_partner_for_customer', " This distance is used while search nearby partner for customer") ?></label>
                                </div>
                            </div>
                          
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="primary_color"><?= labels('primary_color', "Primary Color") ?></label>
                                    <input type="text" onkeyup="change_color('change_color',this)" oninput="change_color('change_color',this)" class=" form-control" name="primary_color" id="primary_color" value="<?= isset($primary_color) ? $primary_color : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="secondary_color"><?= labels('secondary_color', "Secondary Color") ?></label>
                                    <input type="text" class=" form-control" name="secondary_color" id="secondary_color" value="<?= isset($secondary_color) ? $secondary_color : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="primary_shadow"><?= labels('primary_shadow_color', "Primary Shadow Color") ?></label>
                                    <input type="text" class=" form-control" name="primary_shadow" id="primary_shadow" value="<?= isset($primary_shadow) ? $primary_shadow : '' ?>" />
                                </div>
                            </div>
                            <div class="col-md-6 mt-2">
                                <div class="form-group">
                                    <div class="control-label"><?= labels('otp_system', "OTP System") ?> <span class="breadcrumb-item p-3 pt-2 text-primary"><i data-content="If enabled, both the provider and admin need to obtain an OTP from the customer in order to mark the booking as completed. Otherwise, if no OTP verification is required, the booking can be directly marked as completed." class="fa fa-question-circle" data-original-title="" title=""></i></span></div>
                                    <select name="otp_system" class="form-control">
                                        <option value="0" <?php echo  isset($otp_system) && $otp_system == '0' ? 'selected' : '' ?>>Disable</option>
                                        <option value="1" <?php echo  isset($otp_system) && $otp_system == '1' ? 'selected' : '' ?>>Enable</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mt-2">
                                <div class="form-group">
                                    <div class="control-label"><?= labels('booking_auto_cancle', "Booking auto cancle Duration") ?> <span class="breadcrumb-item p-3 pt-2 text-primary">
                                            <i data-content=" If the booking is not accepted by the provider before the added cancelable duration from the actual booking time, the booking will be automatically canceled. If the booking is pre-paid, the amount will be credited to the customer’s bank account.
For example, if a customer books a service at 4:00 PM, and the cancelable duration is 30 minutes, if the provider does not accept the booking by 3:30 PM, the booking will be canceled." class="fa fa-question-circle" data-original-title="" title=""></i></span></div>
                                    <input type="number" class="form-control" name="booking_auto_cancle_duration" id="booking_auto_cancle_duration" value="<?= isset($booking_auto_cancle_duration) ? $booking_auto_cancle_duration : '30' ?>" />


                                </div>
                            </div>

                            <!-- <div class="col-md-6 mt-2">
                                <div class="form-group">
                                    <div class="control-label"><?= labels('prepaid_booking_cancellation_time', "Prepaid Booking auto cancle Duration") ?>
                                        <span class="breadcrumb-item p-3 pt-2 text-primary"><i data-content=" If you don't complete the payment for a prepaid booking before the cancellation deadline, the system will cancel the booking automatically. For instance, if you book a service at 4:00 PM with a 30-minute cancellation window, and the payment is still pending by 3:30 PM, the booking will be canceled automatically.." class="fa fa-question-circle" data-original-title="" title=""></i></span>
                                    </div>
                                    <input type="number" class="form-control" name="prepaid_booking_cancellation_time" id="prepaid_booking_cancellation_time" value="<?= isset($prepaid_booking_cancellation_time) ? $prepaid_booking_cancellation_time : '30' ?>" />


                                </div>
                            </div> -->

                            <div class="col-md-12 ">
                                <div class="form-group">
                                    <label for='logo'><?= labels('login_image', "Login Image") ?></label>
                                    <!-- <div class="gallery">
                                        </div>
                                    -->
                                    <input type="file" name="login_image" class="filepond logo" id="login_image" accept="image/*">
                                    <img class="settings_logo" style="border-radius: 8px" src="<?= isset($login_image) && $login_image != "" ? base_url("public/frontend/retro/" . $login_image) : base_url('public/backend/assets/img/news/img01.jpg') ?>">
                                    <!-- <input type='file' class='form-control-file' name='logo' id='logo' accept="image/*" /> -->
                                </div>
                            </div>
                        </div>
                    </div>
              
            </div>
            <!-- admin logos  -->
            <div class="col-lg-4 col-md-12 col-sm-12 col-xl-4 mb-md-3 mb-sm-3 mb-3">
                <div class="card h-100">
                    <div class="row pl-3">
                        <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('admin_logos', "Admin Logos") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 ">
                                <div class="form-group">
                                    <label for='logo'><?= labels('logo', "Logo") ?></label>
                                    <!-- <div class="gallery">
                                        </div>
                                    -->
                                    <input type="file" name="logo" class="filepond logo" id="file" accept="image/*">
                                    <img class="settings_logo" src="<?= isset($logo) && $logo != "" ? base_url("public/uploads/site/" . $logo) : base_url('public/backend/assets/img/news/img01.jpg') ?>">
                                    <!-- <input type='file' class='form-control-file' name='logo' id='logo' accept="image/*" /> -->
                                </div>
                            </div>
                            <div class="col-md-12 ">
                                <div class="form-group">
                                    <label for='favicon'><?= labels('favicon', "Favicon") ?></label>
                                    <input type="file" name="favicon" class="filepond logo" id="favicon" accept="image/*">
                                    <img class="settings_logo" src="<?= isset($favicon) && $favicon != "" ? base_url("public/uploads/site/" . $favicon) : base_url('public/backend/assets/img/news/img02.jpg') ?>">
                                </div>
                            </div>
                            <div class="col-md-12 ">
                                <div class="form-group">
                                    <label for='halfLogo'><?= labels('half_logo', "Half Logo") ?></label>
                                    <!-- <input type='file' class='form-control-file' name='halfLogo' id='halfLogo' accept="image/*" /> -->
                                    <input type="file" name="halfLogo" class="filepond logo" id="halfLogo" accept="image/*">
                                    <img class="settings_logo" src="<?= isset($half_logo) && $half_logo != "" ? base_url("public/uploads/site/" . $half_logo) : base_url('public/backend/assets/img/news/img03.jpg') ?>">
                                    <!-- <input type='file' class='form-control-file' name='favicon' id='favicon' accept="image/*" /> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- provider logos  -->
            <div class="col-lg-4 col-md-12 col-sm-12 col-xl-4 mb-md-3 mb-sm-3 mb-3">
                <div class="card h-100">
                    <div class="row pl-3">
                        <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('provider_logos', "Provider Logos") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 ">
                                <div class="form-group">
                                    <label for='logo'><?= labels('logo', "Logo") ?></label>
                                    <input type="file" name="partner_logo" class="filepond logo" id="partner_logo" accept="image/*">
                                    <img class="settings_logo" src="<?= isset($partner_logo) && $partner_logo != "" ? base_url("public/uploads/site/" . $partner_logo) : base_url('public/backend/assets/img/news/img01.jpg') ?>">
                                    <!-- <input type='file' class='form-control-file' name='partner_logo' id='logo' accept="image/*" /> -->
                                </div>
                            </div>
                            <div class="col-md-12 ">partner_halfLogo
                                <label for='favicon'><?= labels('favicon', "Favicon") ?></label>
                                <input type="file" name="partner_favicon" class="filepond logo" id="partner_favicon" accept="image/*">
                                <img class="settings_logo" src="<?= isset($partner_favicon) && $partner_favicon != "" ? base_url("public/uploads/site/" . $partner_favicon) : base_url('public/backend/assets/img/news/img02.jpg') ?>">
                                <!-- <input type='file' class='form-control-file' name='partner_favicon' id='favicon' accept="image/*" /> -->
                            </div>
                        </div>
                        <div class="col-md-12 ">
                            <div class="form-group">
                                <label for='halfLogo'><?= labels('half_logo', "Half Logo") ?></label>
                                <input type="file" name="partner_halfLogo" class="filepond logo" id="partner_halfLogo" accept="image/*">
                                <img class="settings_logo" src="<?= isset($partner_half_logo) && $partner_half_logo != "" ? base_url("public/uploads/site/" . $partner_half_logo) : base_url('public/backend/assets/img/news/img03.jpg') ?>">
                                <!-- <input type='file' class='form-control-file' name='partner_halfLogo' id='halfLogo' accept="image/*" /> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <!-- Company Address  -->
            <div class="col-lg-6 col-md-12 col-sm-12 col-xl-6 mb-md-3 mb-sm-3 mb-3">
                <div class="card h-100">
                    <div class="row pl-3">
                        <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('company_address', "Company Address") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md">
                                <label for="address"><?= labels('address', "Address") ?></label>
                                <textarea style="min-height:60px" rows=3 class='form-control h-50 summernotes custome_reset' name="address"><?= isset($address) ? $address : 'Enter Address' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Company Short Description  -->
            <div class="col-lg-6 col-md-12 col-sm-12 col-xl-6 mb-md-3 mb-sm-3 mb-3">
                <div class="card h-100">
                    <div class="row pl-3">
                        <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('company_short_description', "Company Short Description") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md">
                                <label for="short_description"><?= labels('short_description', "Short Description") ?></label>
                                <textarea rows=10 class='form-control h-50 summernotes custome_reset' name="short_description"><?= isset($short_description) ? $short_description : 'Enter Short Description' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <!-- Copyright Details  -->
            <div class="col-lg-6 col-md-12 col-sm-12 col-xl-6 mb-md-3 mb-sm-3 mb-3">
                <div class="card h-100">
                    <div class="row pl-3">
                        <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('copyright_details', "Copyright Details") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md">
                                <label for="copyright_details"><?= labels('copyright_details', "Copyright Details") ?></label>
                                <textarea rows=10 class='form-control h-50 summernotes custome_reset' name="copyright_details"><?= isset($copyright_details) ? $copyright_details : 'Enter Copyright details' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Company Support Hours  -->
            <div class="col-lg-6 col-md-12 col-sm-12 col-xl-6 mb-md-3 mb-sm-3 mb-3">
                <div class="card h-100">
                    <div class="row pl-3">
                        <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('support_hours', "Support Hours") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md">
                                <label for="support_hours"><?= labels('support_hours', "Support Hours") ?></label>
                                <textarea rows=10 class='form-control h-50 summernotes custome_reset' name="support_hours"><?= isset($support_hours) ? $support_hours : 'Enter Support Hours' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md d-flex justify-content-end">
                <input type='submit' name='update' id='update' value='<?= labels('save_changes', "Save") ?>' class='btn btn-lg bg-new-primary' />
                <!-- <button type="submit" name="update" class="btn btn-lg bg-new-primary "><?= labels('save', 'Save') ?></button> -->
                <?= form_close() ?>
            </div>
        </div>
    </section>
</div>
<script>
    function test() {
        $('.custome_reset').attr('value', '');
        // document.getElementById("company_title").val('');
    }
    $('#customer_compulsary_update_force_update').on('change', function() {
        this.value = this.checked ? 1 : 0;
    }).change();
    $('#provider_compulsary_update_force_update').on('change', function() {
        this.value = this.checked ? 1 : 0;
    }).change();
    $('#customer_maintenance_mode').on('change', function() {
        this.value = this.checked ? 1 : 0;
    }).change();
    $('#provider_maintenance_mode').on('change', function() {
        this.value = this.checked ? 1 : 0;
    }).change();
    $('#otp_system').on('change', function() {
        this.value = this.checked ? 1 : 0;
    }).change();
</script>
<script>
    $(function() {
        $('.fa').popover({
            trigger: "hover"
        });
    })
</script>

