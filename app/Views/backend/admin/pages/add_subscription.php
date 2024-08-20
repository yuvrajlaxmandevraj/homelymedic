<div class="main-content">
    <!-- ------------------------------------------------------------------- -->
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('add_subscription', "Add Subscription") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/subscription') ?>"><i class="fas fa-newspaper text-warning"></i> <?= labels('subscription', 'Subscription') ?></a></div>
                <div class="breadcrumb-item"><?= labels('add_subscription', " Add Subscription") ?></a></div>
            </div>
        </div>
        <?= form_open('/admin/subscription/add_store_subscription', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_subscription', 'enctype' => "multipart/form-data"]); ?>
        <div class="container-fluid">
            <div class="alert alert-danger col-md-12">
                <div class="alert-title"><?= labels('note', 'Note') ?>:</div>
                <div class="mt-2">
                    <ul class="pl-3 d-flex flex-column gap-2 instructions-list">
                        <li class="list-unstyled">
                            1. To create a free subscription, set both the price and discount price to zero.
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-lg-8 col-md-12 col-sm-12">
                    <div class="card m-0 p-0">
                        <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="col ">
                                <div class="toggleButttonPostition"><?= labels('subscription_information', 'Subscription Information') ?></div>
                            </div>
                            <div class="col d-flex justify-content-end mr-3 mt-4">
                                <input type="checkbox" class="status-switch" name="status" id="status">
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="company" class="required"> <?= labels('name', ' Name') ?></label>
                                        <input id="name" class="form-control" type="text" name="name" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('name', 'the name ') ?> <?= labels('here', ' Here ') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group m-0 p-0">
                                        <label for="commission" class="required"><?= labels('duration', 'Duration') ?></label>
                                        <div class="radio-buttons">
                                            <label class="radio-inline">
                                                <input type="radio" name="duration_type" value="limited" checked> <?= labels('limited', 'Limited') ?>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="duration_type" value="unlimited"> <?= labels('unlimited', 'Unlimited') ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div id="duration_fields">
                                        <div class="col-md-12 m-0 p-0">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <div class="input-group-text myDivClass">
                                                        <span class="mySpanClass"><?= labels('days', 'Days') ?></span>
                                                    </div>
                                                </div>
                                                <input id="duration" class="form-control" min="0" oninput="this.value = Math.abs(this.value)" type="number" name="duration" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('duration', 'the duration in day  ') ?> <?= labels('here', ' Here ') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="description" class="required"><?= labels('description', ' Description') ?></label>
                                        <textarea rows="5" style="min-height:60px" class="form-control" name="description"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="required"><?= labels('publish', 'Publish') ?></label>
                                        <input type="checkbox" id="publish" name="publish" class="status-switch">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 col-md-4">
                    <div class="card h-100 m-0 p-0 ">
                        <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="col ">
                                <div class="toggleButttonPostition"><?= labels('price_details', 'Price Details') ?></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="price" class="required"><?= labels('price', 'Price') ?></label>
                                        <input id="price" class="form-control" type="number" name="price" min="0" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('price', 'the price   ') ?> <?= labels('here', ' Here ') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="price" class="required"><?= labels('discount_price', 'Discount price') ?></label>
                                        <input id="discount_price" class="form-control" type="number" min="0" name="discount_price" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('discount_price', 'the Discount price     ') ?> <?= labels('here', ' Here ') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tax_type" class="required"><?= labels('tax', 'Tax') ?> <?= labels('type', 'Type') ?></label>
                                        <select name="tax_type" id="tax_type" class="form-control">
                                            <option value="included"><?= labels('tax_included_in_price', 'Tax Included In Price') ?></option>
                                            <option value="excluded"><?= labels('tax_excluded_in_price', 'Tax Excluded In Price') ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6" id="percentage_field">
                                    <div class="form-group">
                                        <label for="partner" class="required"><?= labels('select_tax', 'Select Tax') ?></label> <br>
                                        <select id="" name="tax_id" class="form-control w-100">
                                            <option value=""><?= labels('select_tax', 'Select Tax') ?></option>
                                            <?php foreach ($tax_data as $pn) : ?>
                                                <option value="<?= $pn['id'] ?>"><?= $pn['title'] ?>(<?= $pn['percentage'] ?>%)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="card">
                        <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="col ">
                                <div class="toggleButttonPostition"><?= labels('set_limit', 'Set Limit') ?></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="Order" class="required"><?= labels('order', ' Order') ?></label>
                                        <div class="radio-buttons">
                                            <label class="radio-inline">
                                                <input type="radio" name="order_type" value="limited"> <?= labels('limited', 'Limited') ?>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="order_type" value="unlimited" checked> <?= labels('unlimited', 'Unlimited') ?>
                                            </label>
                                        </div>
                                        <div class="col-md-12">
                                            <div id="max_order">
                                                <div class="form-group">
                                                    <label for="cancelable_till"><?= labels('max_order', 'Maximum Order Number ') ?></label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                        </div>
                                                        <input type="number" style="height: 42px;" class="form-control" name="max_order" id="1" placeholder="Ex. 30" min="0" value="">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="col-md-3">
                                <div class="form-group">
                                    <label for="Order"><?= labels('service', ' Service') ?></label>
                                    <div class="radio-buttons">
                                        <label class="radio-inline">
                                            <input type="radio" name="service_type" value="limited"> Limited
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="service_type" value="unlimited" checked> Unlimited
                                        </label>
                                    </div>
                                    <div class="col-md-12">
                                        <div id="max_service">
                                            <div class="form-group">
                                                <label for="max_service"><?= labels('max_service', 'Maximum Service Number') ?></label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="max_service" id="1" placeholder="Ex. 30" min="0" value="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="commission" class="required"><?= labels('commission', 'Commission') ?></label>
                                        <div class="radio-buttons">
                                            <label class="radio-inline">
                                                <input type="radio" name="commission_type" value="no" checked> <?= labels('no', 'No') ?>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="commission_type" value="yes"> <?= labels('yes', 'Yes') ?>
                                            </label>
                                        </div>
                                        <div id="commission_fields">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="threshold"><?= labels('threshold', 'Threshold') ?></label>
                                                        <div class="input-group">
                                                            <input type="number" min="0" class="form-control" min="0" name="threshold" id="threshold" placeholder="Threshold" min="0" value="">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="percentage"><?= labels('percentage', 'Percentage') ?></label>
                                                        <div class="input-group">
                                                            <input type="number" min="0" max="100" class="form-control" name="percentage" id="percentage" placeholder="Percentage" min="0" max="100" value="">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md d-flex justify-content-end">
                    <button type="submit" id="redirectButton" class="btn btn-lg bg-new-primary submit_btn"><?= labels('add_subscription', " Add Subscription") ?></button>
                    <!-- <button type="submit" id="redirectButton" class="btn btn-lg bg-new-primary"><?= labels('save', 'Save') ?></button> -->
                    <?= form_close() ?>
                </div>
            </div>
    </section>
    <!-- ----------------------------------------------------------------------------------------------------- -->
</div>
<style>
</style>
<script>
    $(document).ready(function() {
        $("#max_order").hide();
        $("#max_service").hide();
        $("#commission_fields").hide();
        $("#duration_type").hide();
        // $('#percentage_field').hide();
        $('#status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        var status = document.querySelector('#status');
        status.onchange = function(e) {
            if (status.checked) {
                $(this).siblings('.switchery').addClass('active-content').removeClass('deactive-content');
            } else {
                $(this).siblings('.switchery').addClass('deactive-content').removeClass('active-content');
            }
        };
        $('input[name="order_type"]').change(function() {
            if ($(this).val() === "limited") {
                $("#max_order").show();
            } else {
                $("#max_order").hide();
            }
        });
        $('input[name="service_type"]').change(function() {
            if ($(this).val() === "limited") {
                $("#max_service").show();
            } else {
                $("#max_service").hide();
            }
        });
        $('input[name="commission_type"]').change(function() {
            if ($(this).val() === "yes") {
                $("#commission_fields").show();
            } else {
                $("#commission_fields").hide();
            }
        });
        $('input[name="duration_type"]').change(function() {
            if ($(this).val() === "limited") {
                $("#duration_fields").show();
            } else {
                $("#duration_fields").hide();
            }
        });
        // $('#tax_type').change(function() {
        //     if ($(this).val() === "excluded") {
        //         $('#percentage_field').show();
        //     } else {
        //         $('#percentage_field').hide();
        //     }
        // });
        $('#publish').siblings('.switchery').addClass('no-content').removeClass('yes-content');
        var publish = document.querySelector('#publish');
        publish.onchange = function(e) {
            if (publish.checked) {
                $(this).siblings('.switchery').addClass('yes-content').removeClass('no-content');
            } else {
                $(this).siblings('.switchery').addClass('no-content').removeClass('yes-content');
            }
        };
    });
</script>
<script>
    // Detect button click event
    $(document).ready(function() {
        // $("#redirectButton").click(function() {
        //     window.location.href = '<?= site_url("admin/subscription") ?>';
        // });
    });
</script>