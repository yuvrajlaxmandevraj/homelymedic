    <div class="main-content">




        <!-- ------------------------------------------------------------------- -->
        <section class="section">
            <div class="section-header mt-2">
                <h1><?= labels('edit_provider', "Edit Provider") ?></h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                    <div class="breadcrumb-item active"><a href="<?= base_url('/admin/partners') ?>"><i class="fas fa-handshake text-warning"></i> <?= labels('provider', 'Provider') ?></a></div>
                    <div class="breadcrumb-item"><?= labels('edit_provider', " Edit Provider") ?></a></div>
                </div>
            </div>


            <?= form_open(
                'partner/update_partner',
                ['method' => "post", 'class' => 'form-submit-event', 'enctype' => "multipart/form-data"]
            ); ?>
            <input type="hidden" name="partner_id" id="partner_id" value="<?= $personal_details['id']; ?>">
            <div class="row">
                <div class="col-lg-8 col-md-12 col-sm-12 mb-30">
                    <div class="card h-100">

                        <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="col ">
                                <div class="toggleButttonPostition"><?= labels('provider_information', 'Provider Information') ?></div>

                            </div>
                            <div class="col d-flex justify-content-end mr-3 mt-4">
                                <?php

                                if ($partner_details['is_approved'] == "1") { ?>


                                    <input type="checkbox" class="status-switch" name="is_approved" checked>


                                <?php   } else { ?>

                                    <input type="checkbox" class="status-switch" name="is_approved">

                                <?php  }


                                ?>
                            </div>

                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="required" for="company"><?= labels('company_name', 'Company Name') ?></label>


                                        <input id="company_name" class="form-control" type="text" name="company_name" value="<?= isset($partner_details['company_name']) ? $partner_details['company_name'] : "" ?> " placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('company_name', 'the company name ') ?> <?= labels('here', ' Here ') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="required" for="type"><?= labels('type', 'Type') ?></label>
                                        <select class="select2" name="type" id="type" required>
                                            <option disabled><?= labels('select_type', 'Select Type') ?></option>
                                            <option value="0" <?php echo  isset($partner_details['type']) && $partner_details['type'] == '0' ? 'selected' : '' ?>><?= labels('individual', 'Individual') ?></option>
                                            <option value="1" <?php echo  isset($partner_details['type']) && $partner_details['type'] == '1' ? 'selected' : '' ?>><?= labels('organization', 'Organization') ?></option>
                                        </select>
                                    </div>
                                </div>

                            </div>


                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="required" for="visiting_charges"><?= labels('visiting_charges', 'Visiting Charges') ?><strong>( <?= $currency ?> )</strong></label>
                                        <input id="visiting_charges" min="0" oninput="this.value = Math.abs(this.value)" class="form-control" type="number" name="visiting_charges" value=<?= isset($partner_details['visiting_charges']) ? $partner_details['visiting_charges'] : "" ?> placeholder="<?= labels('enter', 'Enter') ?>
                                        <?= labels('visiting_charges', 'Visiting Charges') ?> <?= labels('here', ' Here ') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="required" for="advance_booking_days"><?= labels('advance_booking_days', 'Advance Booking Days') ?></label>
                                        <input id="advance_booking_days" min="0" oninput="this.value = Math.abs(this.value)" class="form-control" type="number" name="advance_booking_days" value=<?= isset($partner_details['advance_booking_days']) ? $partner_details['advance_booking_days'] : "" ?> placeholder="<?= labels('enter', 'Enter') ?> <?= labels('advance_booking_days', 'Advance Booking Days') ?> <?= labels('here', ' Here ') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="required" for="number_of_members"><?= labels('number_Of_members', 'Number of Members') ?></label>
                                        <input id="number_of_members" min="0" oninput="this.value = Math.abs(this.value)" class="form-control" type="text" name="number_of_members" value=<?= isset($partner_details['number_of_members']) ? $partner_details['number_of_members'] : "" ?> placeholder="<?= labels('enter', 'Enter') ?> <?= labels('number_Of_members', 'Number of Members') ?> <?= labels('here', ' Here ') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="required" for="number_of_members"><?= labels('at_store', 'At Store') ?></label>
                                        <input type="checkbox" class="status-switch" id="at_store" name="at_store" <?= $partner_details['at_store'] == "1" ? 'checked' : '' ?>>
                                    </div>

                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="required" class="" for="at_doorstep"><?= labels('at_doorstep', 'At Doorstep') ?></label>
                                        <input type="checkbox" id="at_doorstep" class="status-switch" name="at_doorstep" <?= $partner_details['at_doorstep'] == "1" ? 'checked' : '' ?>>
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="required" for="about"><?= labels('about_provider', 'About Provider') ?></label>
                                        <textarea id="about" style="min-height:60px" class="form-control" type="text" name="about" rowspan="10" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('about_provider', 'About Provider') ?> <?= labels('here', ' Here ') ?>"><?= isset($partner_details['about']) ? $partner_details['about'] : "" ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="" for="Description"><?= labels('description', 'Description') ?></label>
                                <textarea rows=10 class='form-control h-50 summernotes custome_reset' name="long_description"><?= isset($partner_details['long_description']) ? $partner_details['long_description'] : '' ?></textarea>
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
                                        <label class="required" for="name"><?= labels('image', 'Image') ?> </label><br>
                                        <input type="file" class="filepond" name="image" id="image" accept="image/*"   >
                                       
                                        <img alt="no image found" width="130px" style="border: solid 1; border-radius: 12px;" height="100px" class="mt-2" id="image_preview" src="<?= isset($personal_details['image']) ? base_url($personal_details['image']) : "" ?>">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="required" for="banner_image"><?= labels('banner_image', 'Banner Image') ?></label><br>
                                        <input type="file" class="filepond" name="banner_image" id="banner_image" accept="image/*"   >
                                        <img alt="no image found" width="130px" style="border: solid 1; border-radius: 12px;" height="100px" class="mt-2" id="banner_image_preview" src="<?= isset($partner_details['banner']) ? base_url($partner_details['banner']) : "" ?>">
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group"> <label class="" for="image"><?= labels('other_images', 'Other Image') ?></label>
                                            <input type="file"   name="other_service_image_selector_edit[]" class="filepond logo"  id="other_service_image_selector_edit" accept="image/*" multiple>
                                            <?php
                                            if (!empty($partner_details['other_images'])) {
                                                $partner_details['other_images'] = array_map(function ($data) {
                                                    return base_url($data);
                                                }, json_decode($partner_details['other_images'], true));
                                            } else {
                                                $partner_details['other_images'] = []; // Return an empty array
                                            }

                                            foreach ($partner_details['other_images'] as $image) { ?>
                                                <!-- <div class="bg-primary"> -->

                                                <img alt="no image found" width="130px" style="border: solid  #d6d6dd 1px; border-radius: 12px;margin:1px;padding:5px" height="100px" class="mt-2" id="image_preview" src="<?= isset($image) ? ($image) : "" ?>">
                                                <!-- </div> -->

                                            <?php }

                                            ?>
                                        </div>
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
                                            <h2 class='section-title'><?= labels('working_days', 'Working Days') ?></h2>



                                            <!--0- monday start -->
                                            <div class="row mb-3">
                                                <div class="col-md-2">
                                                    <label class="" for="0"><?= labels('monday', 'Monday') ?></label>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="time" required id="0" class="form-control start_time" name="start_time[]" value="<?= isset($partner_timings[0]['opening_time']) ? $partner_timings[0]['opening_time'] : '00:00' ?>">
                                                </div>
                                                <div class="col-md-1 text-center mt-2">
                                                    <?= labels('to', 'To') ?>
                                                </div>
                                                <div class="col-md-3 endTime">
                                                    <input type="time" id="0" required class="form-control end_time" name="end_time[]" value="<?= isset($partner_timings[0]['closing_time']) ? $partner_timings[0]['closing_time'] : '00:00' ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-check mt-4">

                                                        <div class="button b2 working-days_checkbox" id="button-11">

                                                            <?php

                                                            if ($partner_timings[0]['is_open'] == "1") { ?>


                                                                <input type="checkbox" class="checkbox check_box" name="monday" id="flexCheckDefault" checked />


                                                            <?php   } else { ?>

                                                                <input type="checkbox" class="checkbox check_box" name="monday" id="flexCheckDefault" />

                                                            <?php  } ?>


                                                            <div class="knobs">
                                                                <span></span>
                                                            </div>
                                                            <div class="layer"></div>
                                                        </div>
                                                    </div>


                                                </div>



                                            </div>
                                            <!-- monday end -->

                                        </div>

                                        <!-- Tuesday start -->

                                        <div class="row mb-3">

                                            <div class="col-md-2">
                                                <label class="" for="1"> <?= labels('tuesday', 'Tuesday') ?></label>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="time" id="1" class="form-control start_time" name="start_time[]" value="<?= isset($partner_timings[1]['opening_time']) ? $partner_timings[1]['opening_time'] : '00:00' ?>">
                                            </div>
                                            <div class="col-md-1 text-center mt-2">
                                                <?= labels('to', 'To') ?>
                                            </div>
                                            <div class="col-md-3 endTime">
                                                <input type="time" id="01" class="form-control end_time" name="end_time[]" value="<?= isset($partner_timings[1]['closing_time']) ? $partner_timings[1]['closing_time'] : '00:00' ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-check  mt-4">
                                                    <div class="button b2 working-days_checkbox" id="button-11">

                                                        <?php if ($partner_timings[1]['is_open'] == "1") { ?>


                                                            <input type="checkbox" class="checkbox check_box" name="tuesday" id="flexCheckDefault" checked />


                                                        <?php   } else { ?>

                                                            <input type="checkbox" class="checkbox check_box" name="tuesday" id="flexCheckDefault" />

                                                        <?php  } ?>


                                                        <div class="knobs">
                                                            <span></span>
                                                        </div>
                                                        <div class="layer"></div>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <!-- 1-Tuesday end -->


                                        <!-- 2-Wednesday start -->
                                        <div class="row mb-3">
                                            <div class="col-md-2">
                                                <label class="" for="2"> <?= labels('wednesday', 'Wednesday') ?></label>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="time" id="2" class="form-control start_time" name="start_time[]" value="<?= isset($partner_timings[2]['opening_time']) ? $partner_timings[2]['opening_time'] : '00:00' ?>">
                                            </div>
                                            <div class="col-md-1  text-center mt-2">
                                                <?= labels('to', 'To') ?>
                                            </div>
                                            <div class="col-md-3 endTime">
                                                <input type="time" id="02" class="form-control end_time" name="end_time[]" value="<?= isset($partner_timings[2]['closing_time']) ? $partner_timings[2]['closing_time'] : '00:00' ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-check  mt-4">
                                                    <div class="button b2 working-days_checkbox" id="button-11">

                                                        <?php if ($partner_timings[2]['is_open'] == "1") { ?>


                                                            <input type="checkbox" class="checkbox check_box" name="wednesday" id="flexCheckDefault" checked />


                                                        <?php   } else { ?>

                                                            <input type="checkbox" class="checkbox check_box" name="wednesday" id="flexCheckDefault" />

                                                        <?php  } ?>

                                                        <div class="knobs">
                                                            <span></span>
                                                        </div>
                                                        <div class="layer"></div>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>
                                        <!-- Wednesday end -->



                                        <!-- 3-Thursday start -->
                                        <div class="row mb-3">
                                            <div class="col-md-2">
                                                <label class="" for="3"> <?= labels('thursday', 'Thursday') ?></label>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="time" id="3" class="form-control start_time" name="start_time[]" value="<?= isset($partner_timings[3]['opening_time']) ? $partner_timings[3]['opening_time'] : '00:00' ?>">
                                            </div>
                                            <div class="col-md-1 text-center mt-2">
                                                <?= labels('to', 'To') ?>
                                            </div>
                                            <div class="col-md-3 endTime">
                                                <input type="time" class="form-control end_time" name="end_time[]" value="<?= isset($partner_timings[3]['closing_time']) ? $partner_timings[3]['closing_time'] : '00:00' ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-check  mt-4">
                                                    <div class="button b2 working-days_checkbox" id="button-11">
                                                        <?php if ($partner_timings[3]['is_open'] == "1") { ?>


                                                            <input type="checkbox" class="checkbox check_box" name="thursday" id="flexCheckDefault" checked />


                                                        <?php   } else { ?>

                                                            <input type="checkbox" class="checkbox check_box" name="thursday" id="flexCheckDefault" />

                                                        <?php  } ?>

                                                        <div class="knobs">
                                                            <span></span>
                                                        </div>
                                                        <div class="layer"></div>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>
                                        <!-- 3-Thursday end -->


                                        <!-- 4-Friday start -->

                                        <div class="row mb-3">
                                            <div class="col-md-2">
                                                <label class="" for="4"> <?= labels('friday', 'Friday') ?></label>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="time" id="4" class="form-control start_time" name="start_time[]" value="<?= isset($partner_timings[4]['opening_time']) ? $partner_timings[4]['opening_time'] : '00:00' ?>">
                                            </div>
                                            <div class="col-md-1 text-center mt-2">
                                                <?= labels('to', 'To') ?>
                                            </div>
                                            <div class="col-md-3 endTime">
                                                <input type="time" class="form-control end_time" name="end_time[]" value="<?= isset($partner_timings[4]['closing_time']) ? $partner_timings[4]['closing_time'] : '00:00' ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-check  mt-4">
                                                    <div class="button b2 working-days_checkbox" id="button-11">
                                                        <?php if ($partner_timings[4]['is_open'] == "1") { ?>


                                                            <input type="checkbox" class="checkbox check_box" name="friday" id="flexCheckDefault" checked />


                                                        <?php   } else { ?>

                                                            <input type="checkbox" class="checkbox check_box" name="friday" id="flexCheckDefault" />

                                                        <?php  } ?>
                                                        <div class="knobs">
                                                            <span></span>
                                                        </div>
                                                        <div class="layer"></div>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>
                                        <!-- 4-Friday end -->

                                        <!-- 5-Saturday start -->

                                        <div class="row mb-3">
                                            <div class="col-md-2">
                                                <label class="" for="5"> <?= labels('saturday', 'Saturday') ?></label>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="time" id="5" class="form-control start_time" name="start_time[]" value="<?= isset($partner_timings[5]['opening_time']) ? $partner_timings[5]['opening_time'] : '00:00' ?>">
                                            </div>
                                            <div class="col-md-1 text-center mt-2">
                                                <?= labels('to', 'To') ?>
                                            </div>
                                            <div class="col-md-3 endTime">
                                                <input type="time" class="form-control end_time" name="end_time[]" value="<?= isset($partner_timings[5]['closing_time']) ? $partner_timings[5]['closing_time'] : '00:00' ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-check  mt-4">
                                                    <div class="button b2 working-days_checkbox" id="button-11">
                                                        <?php if ($partner_timings[5]['is_open'] == "1") { ?>


                                                            <input type="checkbox" class="checkbox check_box" name="saturday" id="flexCheckDefault" checked />


                                                        <?php   } else { ?>

                                                            <input type="checkbox" class="checkbox check_box" name="saturday" id="flexCheckDefault" />

                                                        <?php  } ?>

                                                        <div class="knobs">
                                                            <span></span>
                                                        </div>
                                                        <div class="layer"></div>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>
                                        <!-- 5-Saturday end -->

                                        <!-- 6-Sunday start -->


                                        <div class="row mb-3">
                                            <div class="col-md-2">
                                                <label class="" for="6"> <?= labels('sunday', 'Sunday') ?></label>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="time" id="6" class="form-control start_time" name="start_time[]" value="<?= isset($partner_timings[6]['opening_time']) ? $partner_timings[6]['opening_time'] : '00:00' ?>">
                                            </div>
                                            <div class="col-md-1 text-center mt-2">
                                                <?= labels('to', 'To') ?>
                                            </div>
                                            <div class="col-md-3 endTime">
                                                <input type="time" class="form-control end_time" name="end_time[]" value="<?= isset($partner_timings[6]['closing_time']) ? $partner_timings[6]['closing_time'] : '00:00' ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-check  mt-4">
                                                    <div class="button b2 working-days_checkbox" id="button-11">
                                                        <?php if ($partner_timings[6]['is_open'] == "1") { ?>


                                                            <input type="checkbox" class="checkbox check_box" name="sunday" id="flexCheckDefault" checked />


                                                        <?php   } else { ?>

                                                            <input type="checkbox" class="checkbox check_box" name="sunday" id="flexCheckDefault" />

                                                        <?php  } ?>

                                                        <div class="knobs">
                                                            <span></span>
                                                        </div>
                                                        <div class="layer"></div>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <!-- 6-Sunday end -->

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
                                            <label class="required" for="name"><?= labels('name', 'Name') ?></label>
                                            <input id="username" class="form-control" type="text" name="username" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('name', 'Name') ?> <?= labels('here', ' Here ') ?>" required value=<?= isset($personal_details['username']) ? $personal_details['username'] : "" ?>>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="required" for="email"><?= labels('email', 'Email') ?></label>
                                            <input id="email" class="form-control" type="email" name="email" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('email', 'Email') ?> <?= labels('here', ' Here ') ?>" required value="<?= ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0)) ? "XXXX@gmail.com" : (isset($personal_details['email']) ? $personal_details['email'] : "") ?>">
                                        </div>
                                    </div>



                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="required" for="phone"><?= labels('phone_number', 'Phone Number') ?></label>

                                            <?php
                                         
                                            $country_codes =  fetch_details('country_codes');

                                            $system_country_code = fetch_details('country_codes', ['is_default' => 1])[0];
                                            $default_country_code = isset($personal_details['country_code'])?$personal_details['country_code']:"+91";

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

                                                <input id="phone" class="form-control" type="number"  min="4" maxlength="16" name="phone" value="<?= $personal_details['phone'] ?>" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('phone_number', 'Phone Number') ?> <?= labels('here', ' Here ') ?>" required>

                                            </div>
                                        </div>
                                    </div>


                                </div>


                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="required" for="passport"><?= labels('passport', 'Passport') ?></label><br>

                                            <input type="file" name="passport" class="filepond" id="passport" accept="image/*">

                                            <img alt="no image found" width="130px" style="border: solid 1; border-radius: 12px;" height="100px" class="mt-2" id="passport_preview" src="<?= isset($partner_details['passport']) ? base_url($partner_details['passport']) : "" ?>">


                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="required" for="national_id"><?= labels('national_identity', 'National Identity') ?></label><br>
                                            <input type="file" name="national_id" class="filepond" id="national_id" accept="image/*">
                                            <img alt="no image found" width="130px" style="border: solid 1; border-radius: 12px;" height="100px" class="mt-2" id="national_id_preview" src="<?= isset($partner_details['national_id']) ? base_url($partner_details['national_id']) : "" ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="required" for="national_id"><?= labels('address_id', 'Address Identity') ?></label><br>

                                            <input type="file" name="address_id" class="filepond" id="address_id" accept="image/*">

                                            <img alt="no image found" width="130px" style="border: solid 1; border-radius: 12px;" height="100px" class="mt-2" id="address_id_preview" src="<?= isset($partner_details['address_id']) ? base_url($partner_details['address_id']) : "" ?>">
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
                                        <label class="required" for="partner_location"><?= labels('current_location', 'Current Location') ?></label>
                                        <input id="partner_location" class="form-control" type="text" name="partner_location">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="cities" id="cities_select">
                                            <label class="required" for="city"><?= labels('city', 'City') ?></label>
                                            <input type="text" name="city" class="form-control" placeholder="<?= labels('enter_your_providers_city_name', 'Enter your provider\'s city name') ?>" value=<?= isset($personal_details['city']) ? $personal_details['city'] : "" ?> required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="required" for="partner_latitude"><?= labels('latitude', 'Latitude') ?></label>
                                        <input id="partner_latitude" class="form-control" type="text" name="partner_latitude" placeholder="<?= labels('latitude', 'Latitude') ?>" value=<?= isset($personal_details['latitude']) ? $personal_details['latitude'] : "" ?> required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="required" for="partner_longitude"><?= labels('longitude', 'Longitude') ?></label>
                                        <input id="partner_longitude" class="form-control" type="text" name="partner_longitude" placeholder="<?= labels('longitude', 'Longitude') ?>" required value=<?= isset($personal_details['longitude']) ? $personal_details['longitude'] : "" ?>>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="required" for="address"><?= labels('address', 'Address') ?></label>
                                        <textarea id="address" style="min-height:60px" class="form-control" name="address" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('address', 'Address') ?> <?= labels('here', ' Here ') ?>" required><?= isset($partner_details['address']) ? $partner_details['address'] : "" ?></textarea>
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
                                        <label class="required" for="tax_name"><?= labels('tax_name', 'Tax Name') ?></label>
                                        <input id="tax_name" class="form-control" type="text" name="tax_name" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('tax_name', 'Tax Name') ?> <?= labels('here', ' Here ') ?>" required value=<?= isset($partner_details['tax_name']) ? $partner_details['tax_name'] : "" ?>>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="required" for="tax_number"><?= labels('tax_number', 'Tax Number') ?></label>
                                        <input id="tax_number" class="form-control" type="text" name="tax_number" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('tax_number', 'Tax Number') ?> <?= labels('here', ' Here ') ?>" required value=<?= isset($partner_details['tax_number']) ? $partner_details['tax_number'] : "" ?>>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="required" for="account_number"><?= labels('account_number', 'Account Number') ?></label>
                                        <input id="account_number" class="form-control" type="text" name="account_number" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('account_number', 'Account Number') ?> <?= labels('here', ' Here ') ?>" required value=<?= isset($partner_details['account_number']) ? $partner_details['account_number'] : "" ?>>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="required" for="account_name"><?= labels('account_name', 'Account Name') ?></label>
                                        <input id="account_name" class="form-control" type="text" name="account_name" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('account_name', 'Account Name') ?> <?= labels('here', ' Here ') ?>" required value=<?= isset($partner_details['account_name']) ? $partner_details['account_name'] : "" ?>>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="required" for="bank_code"><?= labels('bank_code', 'Bank Code') ?></label>
                                        <input id="bank_code" class="form-control" type="text" name="bank_code" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('bank_code', 'Bank Code') ?> <?= labels('here', ' Here ') ?>" required value=<?= isset($partner_details['bank_code']) ? $partner_details['bank_code'] : "" ?>>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="required" for="bank_name"><?= labels('bank_name', 'Bank Name') ?></label>
                                        <input id="bank_name" class="form-control" type="text" name="bank_name" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('bank_name', 'Bank Name') ?> <?= labels('here', ' Here ') ?>" required value=<?= isset($partner_details['bank_name']) ? $partner_details['bank_name'] : "" ?>>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="required" for="swift_code"><?= labels('swift_code', 'Swift Code') ?></label>
                                        <input id="swift_code" class="form-control" type="text" name="swift_code" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('swift_code', 'Swift Code') ?> <?= labels('here', ' Here ') ?>" required value=<?= isset($partner_details['swift_code']) ? $partner_details['swift_code'] : "" ?>>
                                    </div>
                                </div>
                            </div>






                        </div>
                    </div>
                </div>
            </div>




            <div class="row">
                <div class="col-md d-flex justify-content-end">

                    <button type="submit" class="btn btn-lg bg-new-primary submit_btn"><?= labels('edit_provider', " Edit Provider") ?></button>
                    <?= form_close() ?>

                </div>
            </div>
        </section>


        <!-- ----------------------------------------------------------------------------------------------------- -->




    </div>
    <style>
    </style>
    <script>
        $('#image').bind('change', function() {
            var filename = $("#image").val();
            console.log(filename);
            if (/^\s*$/.test(filename)) {
                $(".file-upload").removeClass('active');
                $("#noFile").text("No file chosen...");
            } else {
                $(".file-upload").addClass('active');
                $("#noFile").text(filename.replace("C:\\fakepath\\", ""));
            }
        });
        $('#banner_image').bind('change', function() {
            var filename = $("#banner_image").val();
            if (/^\s*$/.test(filename)) {
                $(".file-upload").removeClass('active');
                $("#noFile2").text("No file chosen...");
            } else {
                $(".file-upload").addClass('active');
                $("#noFile2").text(filename.replace("C:\\fakepath\\", ""));
            }
        });
        $('#passport').bind('change', function() {
            var filename = $("#passport").val();
            if (/^\s*$/.test(filename)) {
                $(".file-upload").removeClass('active');
                $("#noFile3").text("No file chosen...");
            } else {
                $(".file-upload").addClass('active');
                $("#noFile3").text(filename.replace("C:\\fakepath\\", ""));
            }
        });
        $('#national_id').bind('change', function() {
            var filename = $("#national_id").val();
            if (/^\s*$/.test(filename)) {
                $(".file-upload").removeClass('active');
                $("#noFile4").text("No file chosen...");
            } else {
                $(".file-upload").addClass('active');
                $("#noFile4").text(filename.replace("C:\\fakepath\\", ""));
            }
        });
        $('#address_id').bind('change', function() {
            var filename = $("#address_id").val();
            if (/^\s*$/.test(filename)) {
                $(".file-upload").removeClass('active');
                $("#noFile5").text("No file chosen...");
            } else {
                $(".file-upload").addClass('active');
                $("#noFile5").text(filename.replace("C:\\fakepath\\", ""));
            }
        });



        $('#image').bind('change', function() {
            var filename = $("#image").val();
            console.log(filename);
            if (/^\s*$/.test(filename)) {
                $(".file-upload").removeClass('active');
                $("#noFile").text("No file chosen...");
            } else {
                $(".file-upload").addClass('active');
                $("#noFile").text(filename.replace("C:\\fakepath\\", ""));
            }
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


        function loadFileImage(event) {
            var image = document.getElementById('image_preview');
            image.src = URL.createObjectURL(event.target.files[0]);
        };


        function loadFileBannerImage(event) {
            var image = document.getElementById('banner_image_preview');
            image.src = URL.createObjectURL(event.target.files[0]);
        };

        function loadFileNationalID(event) {
            var image = document.getElementById('national_id_preview');
            image.src = URL.createObjectURL(event.target.files[0]);
        }


        function loadFilePassoport(event) {
            var image = document.getElementById('passport_preview');
            image.src = URL.createObjectURL(event.target.files[0]);
        }

        function loadFileAddressId(event) {
            var image = document.getElementById('address_id_preview');
            image.src = URL.createObjectURL(event.target.files[0]);
        }

        $(document).ready(function() {
            //for at_store
            <?php
            if ($partner_details['at_store'] == 1) { ?>
                $('#at_store').siblings('.switchery').addClass('active-content').removeClass('deactive-content');

            <?php   } else { ?>
                $('#at_store').siblings('.switchery').addClass('deactive-content').removeClass('active-content');

            <?php  }
            ?>


            //for doorstep
            <?php
            if ($partner_details['at_doorstep'] == 1) { ?>
                $('#at_doorstep').siblings('.switchery').addClass('active-content').removeClass('deactive-content');

            <?php   } else { ?>
                $('#at_doorstep').siblings('.switchery').addClass('deactive-content').removeClass('active-content');

            <?php  }
            ?>


            // function handleSwitchChange(checkbox) {
            //     var switchery = checkbox.nextElementSibling;
            //     if (checkbox.checked) {
            //         switchery.classList.add('active-content');
            //         switchery.classList.remove('deactive-content');
            //     } else {
            //         switchery.classList.add('deactive-content');
            //         switchery.classList.remove('active-content');
            //     }
            // }
            // var atStore = document.querySelector('#at_store');
            // atStore.onchange = function() {
            //     handleSwitchChange(atStore);
            // };

            // var atDoorstep = document.querySelector('#at_doorstep');
            // atDoorstep.onchange = function() {
            //     handleSwitchChange(atDoorstep);
            // };

            $(document).ready(function() {
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
        });

        

    </script>