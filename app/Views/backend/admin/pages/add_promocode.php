<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('add_promocodes', 'Add Promocode') ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"> <i class="fas fa-home-alt text-primary"></i><?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><a href="<?= base_url('admin/promo_codes') ?>"> <?= labels('promocode', 'Promocodes') ?></a></div>
            </div>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">


                    
                        <form method="post" action="<?= base_url('admin/promo_codes/save') ?>" id="promo_code_form" class="form-submit-event">
                            <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                                <div class="col ">
                                    <div class="toggleButttonPostition"><?= labels('add_promocodes', 'Add Promocode') ?></div>

                                </div>
                                <div class="col d-flex justify-content-end mr-3 mt-4">
                                    <input type="checkbox" id="promocode_status" name="status" class="status-switch">
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">


                                    <div class="col-md-4">
                                        <div class="jquery-script-clear"></div>
                                        <div class="categories" id="categories">

                                            <label for="partner" class="required"><?= labels('select_provider', 'Select Provider') ?></label> <br>
                                            <select id="partner" class="form-control w-100 select2" name="partner">
                                                <option value=""><?= labels('select_provider', 'Select Provider') ?></option>
                                                <?php foreach ($partner_name as $pn) : ?>
                                                    <option value="<?= $pn['id'] ?>"><?= $pn['company_name'] . ' - ' . $pn['username'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="promo_code" class="required"><?= labels('promocode', 'Promocode') ?></label>
                                            <input type="text" class="form-control" id="promo_code" name="promo_code">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="minimum_order_amount" class="required"><?= labels('minimum_order_amount', 'Minimum order amount') ?></label>
                                            <input type="text" class="form-control" id="minimum_order_amount" name="minimum_order_amount" min="0" oninput="this.value = 
 !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
                                        </div>
                                    </div>


                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="start_date" class="required"><?= labels('start_date', 'Start Date') ?></label>
                                            <input type="text" class="form-control datepicker" id="start_date" name="start_date">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="end_date" class="required"><?= labels('end_date', 'End Date') ?></label>
                                            <input type="text" class="form-control datepicker" id="end_date" name="end_date">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="no_of_users" class="required"><?= labels('no_of_users', 'No. of users') ?></label>
                                            <input type="text" class="form-control" id="no_of_users" name="no_of_users" min="0" oninput="this.value = 
 !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
                                        </div>
                                    </div>

                                </div>


                                <div class="row">

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="discount" class="required"><?= labels('discount', 'Discount') ?></label>
                                            <input type="text" class="form-control" id="discount" name="discount" min="0" oninput="this.value = 
 !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="discount_type" class="required"><?= labels('discount_type', 'Discount Type') ?></label>
                                            <select name="discount_type" id="discount_type" class="form-control select2">
                                                <option value="amount"><?= labels('amount', 'Amount') ?></option>
                                                <option value="percentage"><?= labels('percentage', 'Percentage') ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="max_discount_amount" class="required"><?= labels('max_discount_amount', 'Max Discount Amount') ?></label>
                                            <input type="text" class="form-control" id="max_discount_amount" name="max_discount_amount" min="0" oninput="this.value = 
 !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
                                        </div>
                                    </div>



                                </div>
                                <div class="row">

                                    <div class="col-md-4 form-group">
                                        <label class=" mt-2" class="required">
                                            <span class=""><?= labels('repeat_usage', 'Repeat Usage ?') ?></span>
                                            <input type="checkbox" id="repeat_usage" name="repeat_usage" class="status-switch editRepeatUsageInModel">
                                        </label>
                                    </div>

                                    <div class="col-md-4 repeat_usage">
                                        <div class="form-group">
                                            <label for="no_of_repeat_usage" class="required"><?= labels('no_of_repeat_usage', 'No. of repeat usage') ?></label>
                                            <input type="number" class="form-control" id="no_of_repeat_usage" name="no_of_repeat_usage" min="0" oninput="this.value = 
 !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null">
                                        </div>
                                    </div>


                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <div class="form-group">
                                                <label for="message" class="required"> <?= labels('message', 'Message') ?> </label>
                                                <textarea id="message" class="form-control h-25 border" name="message"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">

                                            <label for="image " class="required"><?= labels('image', 'Image') ?></label>
                                            <input type="file" class="filepond" name="image" id="image" accept="image/*" required>
                                            <!-- <input type="file" class="form-control" id="image" name="image" accept="image/*"> -->
                                            <input type="hidden" name="old_image" id="old_image" value="">
                                        </div>
                                    </div>
                                </div>

                                <div class="row d-flex justify-content-end mr-1">

                                    <button type="submit" class="btn bg-new-primary submit_btn" id=""><?= labels('add_promocodes', 'Add Promocode') ?></button>
                                </div>


                        </form>
                    </div>
                </div>

            </div>
        </div>

</div>
</section>
</div>
<script>
    $('#start_date').change(function() {
        var doc = $('#start_date').val();

        $("#end_date").daterangepicker({
            locale: {
                format: "YYYY-MM-DD",
            },
            minDate: new Date(doc),
            singleDatePicker: true,
        });
    });




    $(document).ready(function() {

        $('#repeat_usage').siblings('.switchery').addClass('not_allowed-content').removeClass('allowed-content');
        $('#promocode_status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');

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

        function handleRepeatSwitchChange(checkbox) {
            var switchery1 = checkbox.nextElementSibling;
            if (checkbox.checked) {
                switchery1.classList.add('allowed-content');
                switchery1.classList.remove('not_allowed-content');
            } else {
                switchery1.classList.add('not_allowed-content');
                switchery1.classList.remove('allowed-content');
            }
        }

        var repeat_usage = document.querySelector('#repeat_usage');
        repeat_usage.addEventListener('change', function() {
            handleRepeatSwitchChange(repeat_usage);
        });

        var promocode_status = document.querySelector('#promocode_status');
        promocode_status.addEventListener('change', function() {
            handleSwitchChange(promocode_status);
        });


    });
</script>