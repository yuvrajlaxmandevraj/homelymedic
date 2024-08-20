<div class="main-content">






    <!-- ------------------------------------------------------------------- -->
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('add_provider', "Add Provider") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/partners') ?>"><i class="fas fa-handshake text-warning"></i> <?= labels('provider', 'Provider') ?></a></div>
                <div class="breadcrumb-item"><?= labels('add_provider', " Add Provider") ?></a></div>
            </div>
        </div>


        <?= form_open('/admin/partner/insert_partner', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_partner', 'enctype' => "multipart/form-data"]); ?>

        <div class="row">
            <div class="col-lg-8 col-md-12 col-sm-12">
                <div class="card">

                    <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('provider_information', 'Provider Informartion') ?></div>

                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4">
                            <input type="checkbox" class="status-switch" name="is_approved" checked>
                        </div>

                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company " class="required"><?= labels('company_name', 'Company Name') ?></label>

                                    <input id="company_name" class="form-control" type="text" name="company_name" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('company_name', 'the company name ') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type" class="required"><?= labels('type', 'Type') ?></label>
                                    <select class="select2" name="type" id="type" required>
                                        <option disabled selected><?= labels('select_type', 'Select Type') ?></option>
                                        <option value="0"><?= labels('individual', 'Individual') ?></option>
                                        <option value="1"><?= labels('organization', 'Organization') ?></option>
                                    </select>
                                </div>
                            </div>

                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="visiting_charges " class="required"><?= labels('visiting_charges', 'Visiting Charges') ?><strong>( <?= $currency ?> )</strong></label>
                                    <input id="visiting_charges" class="form-control" type="number" name="visiting_charges" min="0" oninput="this.value = Math.abs(this.value)" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('visiting_charges', 'Visiting Charges') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="advance_booking_days" class="required"><?= labels('advance_booking_days', 'Advance Booking Days') ?></label>
                                    <input id="advance_booking_days" class="form-control" type="number" name="advance_booking_days" min="0" oninput="this.value = Math.abs(this.value)" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('advance_booking_days', 'Advance Booking Days') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="number_of_members" class="required"><?= labels('number_Of_members', 'Number of Members') ?></label>
                                    <input id="number_of_members" class="form-control" type="number" name="number_of_members" min="0" oninput="this.value = Math.abs(this.value)" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('number_Of_members', 'Number of Members') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="number_of_members" class="required"><?= labels('at_store', 'At Store') ?></label>
                                    <input type="checkbox" id="at_store" name="at_store" class="status-switch" checked>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="at_doorstep" class="required"><?= labels('at_doorstep', 'At Doorstep') ?></label>
                                    <input type="checkbox" id="at_doorstep" name="at_doorstep" class="status-switch" checked>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="about" class="required"><?= labels('about_provider', 'About Provider') ?></label>
                                    <textarea id="about" style="min-height:60px" class="form-control" required type="text" name="about" rowspan="10" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('about_provider', 'About Provider') ?> <?= labels('here', ' Here ') ?>"></textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label for="Description" class=""><?= labels('description', 'Description') ?></label>
                                <textarea rows=10 class='form-control h-50 summernotes custome_reset' name="long_description"><?= isset($service['long_description']) ? $service['long_description'] : '' ?></textarea>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 d-flex w-100">
                <div class="card w-100 ">
                    <div class="row pl-3">

                        <div class="col mb-3 " style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('images', 'Images') ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name" class="required"><?= labels('image', 'Image') ?> </label><br>
                                    <input type="file" class="filepond" name="image" id="image" accept="image/*" required     >

                                </div>
                            </div>


                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="banner_image" class="required"><?= labels('banner_image', 'Banner Image') ?></label><br>
                                    <input type="file" class="filepond" name="banner_image" id="banner_image" accept="image/*" required     >
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group"> <label for="image" class=""><?= labels('other_images', 'Other Image') ?></label>
                                    <input type="file" name="other_service_image_selector[]" class="filepond logo" id="other_service_image_selector" accept="image/*"     multiple>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>





        <div class="row">
            <div class="col-lg-8 col-md-12 col-sm-12">
                <div class="col-md-12 p-0">
                    <div class="card">
                        <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('working_days', 'Working Days') ?></div>

                        </div>

                        <div class="card-body">



                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">

                                        <div class="row mb-3">
                                            <div class="col-md-2">
                                                <label for="0"><?= labels('monday', 'Monday') ?></label>
                                            </div>
                                            <div class="col-md-3 col-sm-3 col-4 ">
                                                <input type="time" required id="0" class="form-control start_time" name="start_time[]" value="<?php
                                                                                                                                                echo (isset($partner_timings[0]['opening_time']) ? $partner_timings[0]['opening_time'] : '00:00'); ?>">

                                            </div>
                                            <div class="col-md-1 col-sm-2 mt-2 col-4 text-center">
                                                <?= labels('to', 'To') ?>
                                            </div>
                                            <div class="col-md-3 col-sm-3 col-4 endTime">
                                                <input type="time" id="0" required class="form-control end_time" name="end_time[]" value="<?php echo (isset($partner_timings[0]['closing_time']) ? $partner_timings[0]['closing_time'] : '00:00') ?>">
                                            </div>
                                            <div class="col-md-2 col-sm-3 m-sm-1 mt-3">
                                                <div class="form-check mt-3">
                                                    <div class="button b2 working-days_checkbox" id="button-11">

                                                        <input type="checkbox" class="checkbox check_box" name="monday" id="flexCheckDefault" />
                                                        <div class="knobs">
                                                            <span></span>
                                                        </div>
                                                        <div class="layer"></div>
                                                    </div>
                                                </div>


                                            </div>



                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-2">
                                            <label for="1"> <?= labels('tuesday', 'Tuesday') ?></label>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4">
                                            <input type="time" id="1" class="form-control start_time" name="start_time[]" value="<?php echo (isset($partner_timings[1]['opening_time']) ? $partner_timings[1]['opening_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-1 col-sm-2 mt-2 col-4 text-center">
                                            <?= labels('to', 'To') ?>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4 endTime">
                                            <input type="time" id="01" class="form-control end_time" name="end_time[]" value="<?php echo (isset($partner_timings[1]['closing_time']) ? $partner_timings[1]['closing_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-2 col-sm-3 m-sm-1 mt-3">
                                            <div class="form-check mt-3">
                                                <div class="button b2 working-days_checkbox" id="button-11">

                                                    <input type="checkbox" class="checkbox check_box" name="tuesday" id="flexCheckDefault" />
                                                    <div class="knobs">
                                                        <span></span>
                                                    </div>
                                                    <div class="layer"></div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-2">
                                            <label for="2"> <?= labels('wednesday', 'Wednesday') ?></label>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4">
                                            <input type="time" id="2" class="form-control start_time" name="start_time[]" value="<?php echo (isset($partner_timings[2]['opening_time']) ? $partner_timings[2]['opening_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-1 col-sm-2 mt-2 col-4 text-center">
                                            <?= labels('to', 'To') ?>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4 endTime">
                                            <input type="time" id="02" class="form-control end_time" name="end_time[]" value="<?php echo (isset($partner_timings[2]['closing_time']) ? $partner_timings[2]['closing_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-2 col-sm-3 m-sm-1 mt-3">
                                            <div class="form-check mt-3">
                                                <div class="button b2 working-days_checkbox" id="button-11">

                                                    <input type="checkbox" class="checkbox check_box" name="wednesday" id="flexCheckDefault" />
                                                    <div class="knobs">
                                                        <span></span>
                                                    </div>
                                                    <div class="layer"></div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-2">
                                            <label for="3"> <?= labels('thursday', 'Thursday') ?></label>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4">
                                            <input type="time" id="3" class="form-control start_time" name="start_time[]" value="<?php echo (isset($partner_timings[3]['opening_time']) ? $partner_timings[3]['opening_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-1 col-sm-2 mt-2 col-4 text-center">
                                            <?= labels('to', 'To') ?>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4 endTime">
                                            <input type="time" class="form-control end_time" name="end_time[]" value="<?php echo (isset($partner_timings[3]['closing_time']) ? $partner_timings[3]['closing_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-2 col-sm-3 m-sm-1 mt-4">
                                            <div class="form-check mt-3">
                                                <div class="button b2 working-days_checkbox" id="button-11">

                                                    <input type="checkbox" class="checkbox check_box" name="thursday" id="flexCheckDefault" />
                                                    <div class="knobs">
                                                        <span></span>
                                                    </div>
                                                    <div class="layer"></div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-2">
                                            <label for="4"> <?= labels('friday', 'Friday') ?></label>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4">
                                            <input type="time" id="4" class="form-control start_time" name="start_time[]" value="<?php echo (isset($partner_timings[4]['opening_time']) ? $partner_timings[4]['opening_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-1 col-sm-2 mt-2 col-4 text-center">
                                            <?= labels('to', 'To') ?>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4 endTime">
                                            <input type="time" class="form-control end_time" name="end_time[]" value="<?php echo (isset($partner_timings[4]['closing_time']) ? $partner_timings[4]['closing_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-2 col-sm-3 m-sm-1 mt-3">
                                            <div class="form-check mt-3">
                                                <div class="button b2 working-days_checkbox" id="button-11">

                                                    <input type="checkbox" class="checkbox check_box" name="friday" id="flexCheckDefault" />
                                                    <div class="knobs">
                                                        <span></span>
                                                    </div>
                                                    <div class="layer"></div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-2">
                                            <label for="5"> <?= labels('saturday', 'Saturday') ?></label>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4">
                                            <input type="time" id="5" class="form-control start_time" name="start_time[]" value="<?php (isset($partner_timings[5]['opening_time']) ? $partner_timings[5]['opening_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-1 col-sm-2 mt-2 col-4 text-center">
                                            <?= labels('to', 'To') ?>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4 endTime">
                                            <input type="time" class="form-control end_time" name="end_time[]" value="<?php echo (isset($partner_timings[5]['closing_time']) ? $partner_timings[5]['closing_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-2 col-sm-3 m-sm-1 mt-3">
                                            <div class="form-check mt-3">
                                                <div class="button b2 working-days_checkbox" id="button-11">

                                                    <input type="checkbox" class="checkbox check_box" name="saturday" id="flexCheckDefault" />
                                                    <div class="knobs">
                                                        <span></span>
                                                    </div>
                                                    <div class="layer"></div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-2">
                                            <label for="6"> <?= labels('sunday', 'Sunday') ?></label>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4">
                                            <input type="time" id="6" class="form-control start_time" name="start_time[]" value="<?php echo (isset($partner_timings[6]['opening_time']) ? $partner_timings[6]['opening_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-1 col-sm-2 mt-2 col-4 text-center">
                                            <?= labels('to', 'To') ?>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-4 endTime">
                                            <input type="time" class="form-control end_time" name="end_time[]" value="<?php echo (isset($partner_timings[6]['closing_time']) ? $partner_timings[6]['closing_time'] : '00:00') ?>">
                                        </div>
                                        <div class="col-md-2 col-sm-3 m-sm-1 mt-3">
                                            <div class="form-check mt-3">
                                                <div class="button b2 working-days_checkbox" id="button-11">

                                                    <input type="checkbox" class="checkbox check_box" name="sunday" id="flexCheckDefault" />
                                                    <div class="knobs">
                                                        <span></span>
                                                    </div>
                                                    <div class="layer"></div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </div>



                        </div>





                    </div>
                </div>


                <div class="col-md-12 p-0">
                    <div class="card">
                        <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('personal_details', 'Personal Details') ?> </div>

                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="name" class="required"><?= labels('name', 'Name') ?></label>
                                        <input id="username" class="form-control" type="text" name="username" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('name', 'Name') ?> <?= labels('here', ' Here ') ?>" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="email" class="required"><?= labels('email', 'Email') ?></label>
                                        <input id="email" class="form-control" type="email" name="email" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('email', 'Email') ?> <?= labels('here', ' Here ') ?>" required>
                                    </div>
                                </div>



                            </div>
                            <div class="row">


                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="required"><?= labels('phone_number', 'Phone Number') ?></label>

                                        <?php
                                        // $country_codes = get_settings('country_codes', true);
                                        // $system_country_code = get_settings('general_settings', true);
                                        // $default_country_code = isset($system_country_code['country_code'])?$system_country_code['country_code']:"+91";
                                        $country_codes =  fetch_details('country_codes');

                                        $system_country_code = fetch_details('country_codes', ['is_default' => 1])[0];
                                        $default_country_code = isset($system_country_code['code'])?$system_country_code['code']:"+91";

                                        ?>

                                        <div class="input-group">
                                            <select class=" col-md-3 form-control" name="country_code" id="country_code">
                                                <?php
                                                foreach ($country_codes as $key => $country_code) {
                                                    $code = $country_code['code'];
                                                    $name = $country_code['name'];
                                                    $selected = ($default_country_code == $country_code['code']) ? "selected" : "";
                                                    echo "<option $selected value='$code'>$code || $name</option>";
                                                }
                                                ?>
                                            </select>
                                            <input id="phone" class="form-control" type="text" min="4" maxlength="16" name="phone" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('phone_number', 'Phone Number') ?> <?= labels('here', ' Here ') ?>" required>

                                            <!-- <input id="phone" class="form-control" type="number" min="0" max="16" oninput="this.value = Math.abs(this.value)" name="phone" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('phone_number', 'Phone Number') ?> <?= labels('here', ' Here ') ?>" required> -->
                                        </div>
                                    </div>
                                </div>



                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password" class="required"><?= labels('password', 'Password') ?></label>
                                        <input id="password" class="form-control" type="password" name="password" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('password', 'Password') ?> <?= labels('here', ' Here ') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="passport" class="required"><?= labels('passport', 'Passport') ?></label><br>

                                        <input type="file" class="filepond" name="passport" id="passport" accept="image/*" required     >


                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="national_id" class="required"><?= labels('national_identity', 'National Identity') ?></label><br>
                                        <input type="file" class="filepond" name="national_id" id="national_id" accept="image/*" required     >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="national_id" class="required"><?= labels('address_id', 'Address Identity') ?></label><br>
                                        <input type="file" class="filepond" name="address_id" id="address_id" accept="image/*" required     >
                                    </div>
                                </div>
                            </div>





                        </div>
                    </div>

                </div>


            </div>

            <div class="col-lg-4 col-md-12 col-sm-12 mb-30">
                <div class="card w-100 h-100">
                    <div class="row pl-3">

                        <div class="col mb-3 " style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('provider_location_information', "Location Information") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div id="map_wrapper_div_partner">
                                    <div id="partner_map">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12  mt-3">
                                <div class="form-group">
                                    <label for="partner_location" class="required"><?= labels('current_location', 'Current Location') ?></label>
                                    <input id="partner_location" class="form-control" type="text" name="partner_location">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="cities" id="cities_select">
                                        <label for="city" class="required"><?= labels('city', 'City') ?></label>
                                        <input type="text" name="city" class="form-control" placeholder="<?= labels('enter_your_providers_city_name', 'Enter your provider\'s city name') ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="partner_latitude" class="required"> <?= labels('latitude', 'Latitude') ?></label>
                                    <input id="partner_latitude" class="form-control" type="text" name="partner_latitude" placeholder="<?= labels('latitude', 'Latitude') ?>" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="partner_longitude" class="required"><?= labels('longitude', 'Longitude') ?></label>
                                    <input id="partner_longitude" class="form-control" type="text" name="partner_longitude" placeholder="<?= labels('longitude', 'Longitude') ?>" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label for="address" class="required"><?= labels('address', 'Address') ?></label>
                                    <textarea id="address" class="form-control" style="min-height:60px" name="address" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('address', 'Address') ?> <?= labels('here', ' Here ') ?>" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>


        <div class="row ">
            <div class="col-md-12">
                <div class="card">
                    <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="toggleButttonPostition"><?= labels('bank_details', 'Bank Details') ?></div>

                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tax_name" class="required"><?= labels('tax_name', 'Tax Name') ?></label>
                                    <input id="tax_name" class="form-control" type="text" name="tax_name" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('tax_name', 'Tax Name') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tax_number" class="required"> <?= labels('tax_number', 'Tax Number') ?></label>
                                    <input id="tax_number" class="form-control" type="text" name="tax_number" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('tax_number', 'Tax Number') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="account_number" class="required"><?= labels('account_number', 'Account Number') ?></label>
                                    <input id="account_number" class="form-control" type="number" name="account_number" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('account_number', 'Account Number') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="account_name" class="required"><?= labels('account_name', 'Account Name') ?></label>
                                    <input id="account_name" class="form-control" type="text" name="account_name" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('account_name', 'Account Name') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bank_code" class="required"><?= labels('bank_code', 'Bank Code') ?></label>
                                    <input id="bank_code" class="form-control" type="text" name="bank_code" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('bank_code', 'Bank Code') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bank_name" class="required"><?= labels('bank_name', 'Bank Name') ?></label>
                                    <input id="bank_name" class="form-control" type="text" name="bank_name" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('bank_name', 'Bank Name') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="swift_code" class="required"><?= labels('swift_code', 'Swift Code') ?></label>
                                    <input id="swift_code" class="form-control" type="text" name="swift_code" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('swift_code', 'Swift Code') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                        </div>






                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md d-flex justify-content-end">

                <button type="submit" class="btn btn-lg bg-new-primary submit_btn"><?= labels('add_provider', 'Add Provider') ?></button>
                <?= form_close() ?>

            </div>
        </div>
    </section>


    <!-- ----------------------------------------------------------------------------------------------------- -->




</div>
<style>
</style>
<script>
  
</script>


<script>
  
    $(document).ready(function() {
        $('#at_store').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        $('#at_doorstep').siblings('.switchery').addClass('active-content').removeClass('deactive-content');


        function handleSwitchChange(checkbox) {
            var switchery = checkbox.nextElementSibling;
            if (checkbox.checked) {
                switchery.classList.add('active-content');
                switchery.classList.remove('deactive-content');
            } else {
                switchery.classList.add('deactive-content');
                switchery.classList.remove('active-content');
            }
        }

        var atStore = document.querySelector('#at_store');
        var atDoorstep = document.querySelector('#at_doorstep');
        atDoorstep.addEventListener('change', function() {

            console.log('doorstep');
            if (!atStore.checked && !atDoorstep.checked) {
                var switchery = atStore.nextElementSibling;
                switchery.classList.add('active-content');
                switchery.classList.remove('deactive-content');
                atStore.click();
                var switchery1 = atDoorstep.nextElementSibling;
                switchery1.classList.add('deactive-content');
                switchery1.classList.remove('active-content');
            } else {

                handleSwitchChange(atDoorstep);
            }
        });

        atStore.addEventListener('change', function() {
            if (!atStore.checked && !atDoorstep.checked) {
                var switchery = atDoorstep.nextElementSibling;
                switchery.classList.add('active-content');
                switchery.classList.remove('deactive-content');
                atDoorstep.click();
            } else {
                handleSwitchChange(atStore);
            }
        });
    });


    $('#type').change(function() {
        var doc = document.getElementById("type");

        if (doc.options[doc.selectedIndex].value == 0) {
            // console.log('0 selectc');
            $("#number_of_members").val('1');

            $("#number_of_members").attr("readOnly", "readOnly");
        } else if (doc.options[doc.selectedIndex].value == 1) {

            $("#number_of_members").val('');
            $("#number_of_members").removeAttr("readOnly");

        }
        // alert("You selected " + doc.options[doc.selectedIndex].value);
    });


    $('.start_time').change(function() {
        var doc = $(this).val();
        console.log(doc);
        $(this).parent().siblings(".endTime").children().attr('min', doc);

    });
</script>