<div class="main-content">
    <!-- ------------------------------------------------------------------- -->
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('edit_subscription', "Edit Subscription") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/subscription') ?>"><i class="fas fa-newspaper text-warning"></i> <?= labels('subscription', 'Subscription') ?></a></div>
                <div class="breadcrumb-item"><?= labels('edit_subscription', " Edit Subscription") ?></a></div>
            </div>
        </div>
        <?= form_open('/admin/subscription/edit_subscription', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'edit_subscription', 'enctype' => "multipart/form-data"]); ?>
        <input type="hidden" name="subscription_id" id="subscription_id" value=<?= $subscription_data[0]['id'] ?>>

        <div class="row mb-3">
            <div class="col-lg-8 col-md-12 col-sm-12">
                <div class="card m-0 p-0">
                    <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('subscription_information', 'Subscription Information') ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4">
                            <?php

                            if ($subscription_data[0]['status'] == "1") { ?>


                                <input type="checkbox" id="status" class="status-switch" name="status" checked>


                            <?php   } else { ?>

                                <input type="checkbox" id="status" class="status-switch" name="status">

                            <?php  }


                            ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company"><?= labels('name', ' Name') ?></label>
                                    <input id="name" class="form-control" type="text" value=<?= isset($subscription_data[0]['name']) ? $subscription_data[0]['name'] : "" ?> name="name" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('name', 'the name ') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                    
                            <div class="col-md-6">
                                <div class="form-group m-0 p-0">
                                    <label for="commission" class="required"><?= labels('duration', 'Duration') ?></label>
                                    <div class="radio-buttons">
                                        <label class="radio-inline">
                                            <input type="radio" name="duration_type" value="limited" <?= ($subscription_data[0]['duration'] != "unlimited") ? "checked " : "" ?>> Limited
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="duration_type" value="unlimited" <?= ($subscription_data[0]['duration'] == "unlimited") ? "checked " : "" ?>> Unlimited
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
                                            <input id="duration" class="form-control" type="number" min="0" oninput="this.value = Math.abs(this.value)" name="duration" value=<?= isset($subscription_data[0]['duration']) ? $subscription_data[0]['duration'] : "" ?> placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('duration', 'the duration in day  ') ?> <?= labels('here', ' Here ') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description" class="required"><?= labels('description', ' Description') ?></label>
                                    <textarea rows="5" style="min-height:60px" class="form-control" name="description"><?= isset($subscription_data[0]['description']) ? $subscription_data[0]['description'] : "" ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="required">Publish</label>
                                    <?php
                                    $publishChecked = ($subscription_data[0]['publish'] == "1") ? 'checked' : '';
                                    ?>

                                    <input type="checkbox" id="publish" class="status-switch" name="publish"  <?php echo $publishChecked; ?>>

                                </div>
                            </div>
                        </div> 

                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-md-4">
                <div class="card h-100 m-0 p-0">
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

                                    <input id="price" class="form-control" type="number" value="<?= isset($subscription_data[0]['price']) ? $subscription_data[0]['price'] : "" ?>" name="price" placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('price', 'the price   ') ?> <?= labels('here', ' Here ') ?>" required min="0">

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price" class="required"><?= labels('discount_price', 'Discount price') ?></label>
                                    <input id="discount_price" min="0" class="form-control" type="number" name="discount_price" value=<?= isset($subscription_data[0]['discount_price']) ? $subscription_data[0]['discount_price'] : "" ?> placeholder="<?= labels('enter', 'Enter ') ?> <?= labels('discount_price', 'the Discount price     ') ?> <?= labels('here', ' Here ') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">


                                <div class="form-group">
                                    <label for="tax_type" class="required"><?= labels('tax', 'Tax') ?> <?= labels('type', 'Type') ?></label>
                                    <select name="tax_type" id="tax_type" class="form-control">


                                        <option value="excluded" <?php echo  isset($subscription_data[0]['tax_type'])  && $subscription_data[0]['tax_type'] == "excluded"  ? 'selected' : '' ?>><?= labels('tax_excluded_in_price', 'Tax Excluded In Price') ?></option>
                                        <option value="included" <?php echo  isset($subscription_data[0]['tax_type'])  && $subscription_data[0]['tax_type'] == "included"  ? 'selected' : '' ?>><?= labels('tax_included_in_price', 'Tax Included In Price') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6" id="percentage_field_tax">
                                <div class="form-group">

                                    <label for="partner" class="required"><?= labels('select_tax', 'Select Tax') ?></label> <br>
                                    <select id="" name="tax_id" class="form-control w-100">
                                        <option value=""><?= labels('select_tax', 'Select Tax') ?></option>
                                        <?php foreach ($tax_data as $pn) : ?>
                                            <option value="<?= $pn['id'] ?>" <?php echo  isset($subscription_data[0]['tax_id'])  && $subscription_data[0]['tax_id'] ==  $pn['id'] ? 'selected' : '' ?>> <?= $pn['title'] ?>(<?= $pn['percentage'] ?>%)</option>
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
                                    <label for="Order" class="required"><?= labels('Order', ' Order') ?></label>
                                    <div class="radio-buttons">
                                        <label class="radio-inline">
                                            <input type="radio" name="order_type" value="limited" <?= ($subscription_data[0]['order_type'] == "limited") ? "checked " : "" ?>> Limited
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="order_type" value="unlimited" <?= ($subscription_data[0]['order_type'] == "unlimited") ? "checked " : "" ?>> Unlimited
                                        </label>
                                    </div>
                                    <div class="col-md-12">

                                        <div id="max_order" >
                                            <div class="form-group">
                                                <label for="cancelable_till"><?= labels('max_order', 'Maximum Order Number ') ?></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                    </div>
                                                    <input type="number" style="height: 42px;" class="form-control" name="max_order" value=<?= isset($subscription_data[0]['max_order_limit']) ? $subscription_data[0]['max_order_limit'] : "" ?> id="1" placeholder="Ex. 30" min="0" value="">
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
                                            <input type="radio" name="service_type" value="limited" <?= ($subscription_data[0]['service_type'] == "limited") ? "checked " : "" ?>> Limited
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="service_type" value="unlimited" <?= ($subscription_data[0]['service_type'] == "unlimited") ? "checked " : "" ?>> Unlimited
                                        </label>
                                    </div>

                                    <div class="col-md-12">
                                        <div id="max_service">
                                            <div class="form-group">
                                                <label for="max_service"><?= labels('max_service', 'Maximum Service Number') ?></label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="max_service" value=<?= isset($subscription_data[0]['max_service_limit']) ? $subscription_data[0]['max_service_limit'] : "" ?> id="1" placeholder="Ex. 30" min="0" value="">
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
                                            <input type="radio" name="commission_type" value="no" <?= ($subscription_data[0]['is_commision'] == "no") ? "checked " : "" ?>> No
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="commission_type" value="yes" <?= ($subscription_data[0]['is_commision'] == "yes") ? "checked " : "" ?>> Yes
                                        </label>
                                    </div>
                                    <div id="commission_fields">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="threshold" class="required"><?= labels('threshold', 'Threshold') ?></label>
                                                    <div class="input-group">
                                                        <input type="number" min="0" class="form-control" name="threshold" value=<?= isset($subscription_data[0]['commission_threshold']) ? $subscription_data[0]['commission_threshold'] : "" ?> id="threshold" placeholder="Threshold" min="0" value="">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="percentage" class="required"><?= labels('percentage', 'Percentage') ?></label>
                                                    <div class="input-group">
                                                        <input type="number" min="0" max="100" class="form-control" name="percentage" value=<?= isset($subscription_data[0]['commission_percentage']) ? $subscription_data[0]['commission_percentage'] : "" ?> id="percentage" placeholder="Percentage" min="0" max="100" value="">
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
                <button type="submit" id="redirectButton" class="btn btn-lg bg-new-primary submit_btn"><?= labels('edit_subscription', "Edit Subscription") ?></button>
                <!-- <button id="redirectButton">Redirect</button> -->
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

        if ("<?php echo $subscription_data[0]['service_type']; ?>" === "limited") {
            $("#max_service").show();
        } else {
            $("#max_service").hide();

        }

        if ("<?php echo $subscription_data[0]['order_type']; ?>" === "limited") {
            $("#max_order").show();
        } else {
            $("#max_order").hide();

        }

        if ("<?php echo $subscription_data[0]['is_commision']; ?>" === "yes") {

            $("#commission_fields").show();
            $('#percentage_field').show();

        } else {
            $("#commission_fields").hide();
            $('#percentage_field').hide();
        }


        if ("<?php echo $subscription_data[0]['duration']; ?>" === "unlimited") {

            $("#duration_fields").hide();


        } else {

            $("#duration_fields").show();
        }




        //for status
        <?php
        if ($subscription_data[0]['status'] == 1) { ?>
            $('#status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        <?php   } else { ?>
            $('#status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');

        <?php  }
        ?>




        <?php 
        if ($subscription_data[0]['publish'] == 1) { ?>
            $('#publish').siblings('.switchery').addClass('yes-content').removeClass('no-content');
        <?php } else { ?>
            $('#publish').siblings('.switchery').addClass('no-content').removeClass('yes-content');
        <?php } ?>



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


        var publish = document.querySelector('#publish');
        publish.onchange = function(e) {

            if (publish.checked) {
                $(this).siblings('.switchery').addClass('yes-content').removeClass('no-content');
            } else {
                $(this).siblings('.switchery').addClass('no-content').removeClass('yes-content');
            }
        };


        $('input[name="duration_type"]').change(function() {
            if ($(this).val() === "limited") {
                $("#duration_fields").show();
            } else {
                $("#duration_fields").hide();
            }
        });
    });
</script>

<script>
    // Detect button click event
    $(document).ready(function() {
        // $("#redirectButton").click(function() {

        //     // Redirect to the specified URL
        //     window.location.href = '<?= site_url("admin/subscription") ?>';
        // });
    });
</script>