<!-- Start Signup Section-->
<link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/cropper.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/custom.css') ?>" />
    <div class="main-wrapper">
        
            <?= view('backend/admin/footer') ?>
            <?= view('backend/admin/include-scripts') ?>
        </div>
<section class="section">
    <section class="container mt-1" data-aos='fade-up'>



        <div class="row">
            <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-8 offset-lg-2 col-xl-8 offset-xl-2">
                <?php
                $data = get_settings('general_settings', true);
                ?>
           
                <div class="card card-primary shadow-lg p-3 mb-5 bg-white rounded">
                    <!-- <div class="card-header">
                        <h4>Register</h4>
                    </div> -->
                    <?php
                    if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                    ?>
                        <div class="col-sm-12">
                            <div class="alert alert-warning mb-0">
                                <b>Note:</b> If you cannot Register here, please close the codecanyon frame by clicking on <b>x Remove Frame</b> button from top right corner on the page or <a href="https://edemand.erestro.me/auth/create_user" target="_blank">&gt;&gt; Click here &lt;&lt;</a>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="row">
                        <div class="col-md-10"></div>
                        <div class="col-md-2">Step <span class="step">1</span> of 3</div>
                    </div>
                    <div class="col-md">
                        <div class="row offset-md-3">
                            <div class="col-md-4"> <img src=" <?= isset($data['logo']) && $data['logo'] != "" ? base_url("public/uploads/site/" . $data['logo']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" class="" alt="">
                            </div>
                        </div>
                        <div class="card-body" id="step_1">
                            <div id="send">
                                <div class="form-group">
                                    <label for="number">Phone Number</label>

                                    <?php $country_codes = get_settings('country_codes', true);
                                    $system_country_code = get_settings('country_code');
                                    ?>
                                    <div class="row">
                                        <div class="col-4">
                                            <select class="form-control col-4" name="country_code" id="country_code">
                                                <?php
                                                foreach ($country_codes['countries'] as $key => $country_code) {
                                                    $code = $country_code['code'];
                                                    $name = $country_code['name'];
                                                    $selected = ($system_country_code == $country_code['code']) ? "selected" : "";
                                                    echo "<option $selected value='$code'>$code || $name</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-8">
                                            <input id="number" class="form-control" type="number" name="number1" placeholder="Enter Mobile Number">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div id="rec"></div>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" id="sender" >Submit Number</button>
                                </div>
                            </div>
                            
                            <div class="otp_show">
                                <div class="form-group">
                                    <label for="otp">Received OTP</label>
                                    <input id="otp" class="form-control" type="number" name="otp">
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" id="check" onclick="codeverify()">Verify Otp</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="step_2">
                            <?= form_open('auth/create_partner', ['id' => 'register123']); ?>
                            <div class="row">
                                <div class="form-group">
                                    <label for='first_name' class="">User name</label>
                                    <input type="text" id="first_name" class="form-control" name='username' placeholder="First name" required />
                                </div>

                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label for='email' class="">Email</label>

                                    <input type="email" id="email" class="form-control" name='email' placeholder="Email Id" required />
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label for='phone' class="">Mobile Number</label>
                                    <input type="text" id="phone" class="form-control" name='phone' placeholder="Mobile Number" required min="0" readOnly />
                                    <input id="store_country_code" class="form-control" type="hidden" name="store_country_code">

                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-6">
                                    <label for='password' class=""> Password</label>
                                    <input type="password" id="password" class="form-control" name='password' placeholder="Password" required />
                                </div>
                                <div class="form-group col-6">
                                    <label for='password_confirm' class="">Confirm Password</label>
                                    <input type="password" id="password_confirm" class="form-control" name='password_confirm' placeholder="Confirm password" required />
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label for='company_name' class="">Company Name</label>
                                    <input type="text" id="company_name" class="form-control" name='company_name' placeholder="Company Name" required />
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg btn-block g-">
                                    Register
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md">
                            <i class="fa-solid fa-arrow-left-long"></i> Back To <a href="<?= base_url('admin/login') ?>" class="">Login</a><br>
                        </div>
                    </div>
                </div>
            </div>
    </section>

</section>


<!-- Signup Section End -->