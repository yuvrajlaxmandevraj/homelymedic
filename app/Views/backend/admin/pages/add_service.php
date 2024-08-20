<div class="main-content">

    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('services', "Services") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/services') ?>"><i class="	fas fa-tools text-warning"></i> <?= labels('service', 'Service') ?></a></div>
                <div class="breadcrumb-item"><?= labels('add_services', 'Add Service') ?></a></div>

            </div>
        </div>
        <?= form_open('/admin/services/insert_service', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_service', 'enctype' => "multipart/form-data"]); ?>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('add_service_details', 'Add Service Details') ?></div>
                        </div>

                        <div class="col d-flex justify-content-end mr-3 mt-4">
                            <input type="checkbox" id="status" name="status" class="status-switch" checked>

                        </div>

                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <div class="jquery-script-clear"></div>
                                <div class="categories form-group" id="categories">

                                    <label for="partner" class="required"><?= labels('select_provider', 'Select Provider') ?></label> <br>
                                    <select id="partner" class="form-control w-100 select2" name="partner">
                                        <option value=""><?= labels('select_provider', 'Select Provider') ?></option>
                                        <?php foreach ($partner_name as $pn) : ?>
                                            <option value="<?= $pn['id'] ?>" data-members="<?= $pn['number_of_members'] ?>" data-at_store="<?= $pn['at_store'] ?>" data-at_doorstep="<?= $pn['at_doorstep'] ?>">
                                                <?= $pn['company_name'] . ' - ' . $pn['username'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- <div class="jquery-script-clear"></div> -->
                                <div class="categories form-group" id="categories">
                                    <label for="category_item" class="required"><?= labels('choose_a_category_for_your_service', 'Choose a Category for your service') ?></label>
                                    <select id="category_item" class="form-control select2" name="categories" style="margin-bottom: 20px;">
                                        <option value=""> <?= labels('select', 'Select') ?> <?= labels('category', 'Category') ?> </option>
                                        <?php foreach ($categories_name as $category) : ?>
                                            <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>




                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class="required"><?= labels('title_of_the_service', 'Title of the service') ?> </label>
                                    <input class="form-control" type="text" name="title">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tags" class="required"><?= labels('tags', 'Tags') ?></label><br>
                                    <input id="tags" style="border-radius: 0.25rem" class="w-100" type="text" name="tags[]" placeholder="press enter to add tag">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="short_description" class="required"><?= labels('short_description', "Short Description") ?></label>
                                    <textarea rows=4 class='form-control' style="min-height:60px" name="description"></textarea>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card h-100 ">
                    <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('perform_task', 'Perform Task') ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duration" class="required"><?= labels('duration_to_perform_task', 'Duration to Perform Task') ?></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text myDivClass" style="height: 42px;">
                                                <span class="mySpanClass"><?= labels('minutes', 'Minutes') ?></span>
                                            </div>
                                        </div>
                                        <input type="number" style="height: 42px;" class="form-control" name="duration" id="duration" min="0" oninput="this.value = Math.abs(this.value)" placeholder="<?= labels('duration_to_perform_task', 'Duration to Perform service') ?>" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="members" class="required"><?= labels('members_required_to_perform_task', 'Members Required to Perform Task') ?></label>
                                    <input id="members" class="form-control" type="number" name="members" min="0" oninput="this.value = Math.abs(this.value)" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('members_required_to_perform_task', 'Members Required to Perform Task') ?> <?= labels('here', ' Here ') ?>" min="0">
                                </div>
                            </div>


                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_qty" class="required"><?= labels('max_quantity_allowed_for_services', 'Max Quantity allowed for services') ?></label>
                                    <input id="max_qty" class="form-control" type="number" min="0" oninput="this.value = Math.abs(this.value)" name="max_qty" placeholder="<?= labels('max_quantity_allowed_for_services', 'Max Quantity allowed for services') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>


        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card card h-100 ">
                    <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('files', 'Files') ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group"> <label for="image" class="required"><?= labels('image', 'Image') ?></label>
                                    <input type="file" name="service_image_selector" class="filepond logo" id="service_image_selector" accept="image/*">

                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group"> <label for="image" class=""><?= labels('other_images', 'Other Image') ?></label>
                                    <input type="file" name="other_service_image_selector[]" class="filepond logo" id="other_service_image_selector" accept="image/*" multiple>

                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group"> <label for="image"><?= labels('files', 'Files') ?></label>
                                    <input type="file" name="files[]" class="filepond-docs" id="files" multiple>


                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-md-6">
                <div class="card card h-100 ">
                    <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('description', 'Description') ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="Description" class="required"><?= labels('description', 'Description') ?></label>
                                <textarea rows=10 class='form-control h-50 summernotes custome_reset' name="long_description"><?= isset($short_description) ? $short_description : '' ?></textarea>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card h-100">
                    <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('price_details', 'Price Details') ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tax_type" class="required"><?= labels('price', 'Price') ?> <?= labels('type', 'Type') ?></label>
                                    <select name="tax_type" id="tax_type" required class="form-control">
                                        <option value="excluded"><?= labels('tax_excluded_in_price', 'Tax Excluded In Price') ?></option>
                                        <option value="included"><?= labels('tax_included_in_price', 'Tax Included In Price') ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="jquery-script-clear"></div>
                                <div class="" id="">

                                    <label for="partner" class="required"><?= labels('select_tax', 'Select Tax') ?></label> <br>
                                    <select id="tax" name="tax_id" required class="form-control w-100" name="tax">
                                        <option value=""><?= labels('select_tax', 'Select Tax') ?></option>
                                        <?php foreach ($tax_data as $pn) : ?>
                                            <option value="<?= $pn['id'] ?>"><?= $pn['title'] ?>(<?= $pn['percentage'] ?>%)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="price" class="required"><?= labels('price', 'Price') ?></label>
                                    <input id="price" class="form-control" type="number" name="price" placeholder="price" min="1" oninput="this.value = Math.abs(this.value)">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="discounted_price" class="required"><?= labels('discounted_price', 'Discounted Price') ?></label>
                                    <input id="discounted_price" class="form-control" type="number" name="discounted_price" min="0" oninput="this.value = Math.abs(this.value)" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('discounted_price', 'Discounted Price') ?> <?= labels('here', ' Here ') ?>">
                                </div>
                            </div>

                        </div>


                    </div>
                </div>
            </div>


        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card h-100">
                    <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('Faqs', 'Faqs') ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="list_wrapper">
                                    <div class="row">

                                        <div class="col-xs-4 col-sm-4 col-md-4">

                                            <div class="form-group">
                                                <label for="question"><?= labels('question', "Quetion") ?></label>
                                                <input name="faqs[0][]" type="text" placeholder="Enter the question here" class="form-control" />

                                            </div>
                                        </div>

                                        <div class="col-xs-7 col-sm-7 col-md-4">
                                            <div class="form-group">
                                                <label for="answer"><?= labels('answer', "Answer") ?></label>
                                                <input autocomplete="off" name="faqs[0][]" type="text" placeholder="Enter the answer here" class="form-control" />
                                            </div>
                                        </div>

                                        <div class="col-xs-1 col-sm-1 col-md-2 mt-4">

                                            <button class="btn btn-primary list_add_button" type="button">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>


        </div>




        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card h-100">
                    <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('service_option', 'Service Options') ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">

                                <div class="form-group">
                                    <label class="" for="is_cancelable" class="required"><?= labels('is_cancelable_?', 'Is Cancelable ')  ?></label>
                                    <input type="checkbox" id="is_cancelable" name="is_cancelable" class="status-switch">

                                </div>
                            </div>




                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="" for="pay_later" class="required"><?= labels('pay_later_allowed', 'Pay Later Allowed') ?></label>
                                    <input type="checkbox" id="pay_later" name="pay_later" class="status-switch">

                                </div>

                            </div>


                            <div class="col-md-3" id="service_at_store">
                                <div class="form-group">
                                    <label class="" for="at_store" class="required">At Store</label>
                                    <input type="checkbox" id="at_store" name="at_store" class="status-switch">
                                </div>
                            </div>

                            <div class="col-md-3" id="service_at_doorstep">
                                <div class="form-group">
                                    <label class="" for="at_doorstep" class="required">At Doorstep</label>
                                    <input type="checkbox" id="at_doorstep" name="at_doorstep" class="status-switch">
                                </div>
                            </div>

                        </div>

                        <div class="row" id="cancel_order">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cancelable_till" class="required"><?= labels('cancelable_before', 'Cancelable before') ?></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text myDivClass" style="height: 42px;">
                                                <span class="mySpanClass"><?= labels('minutes', 'Minutes') ?></span>
                                            </div>
                                        </div>
                                        <input type="number" style="height: 42px;" class="form-control" name="cancelable_till" id="cancelable_till" placeholder="Ex. 30" min="0" value="">
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

                <button type="submit" class="btn btn-lg bg-new-primary submit_btn"><?= labels('add_services', 'Add Service') ?></button>
                <?= form_close() ?>

            </div>
        </div>



</div>



</section>
</div>

<script>
    $(document).ready(function() {

        $('#is_cancelable').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        $('#pay_later').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        $('#status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');
        $('#at_store').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        $('#at_doorstep').siblings('.switchery').addClass('deactive-content').removeClass('active-content');

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

        var isCancelable = document.querySelector('#is_cancelable');
        isCancelable.addEventListener('change', function() {
            handleSwitchChange(isCancelable);
        });

        var payLater = document.querySelector('#pay_later');
        payLater.addEventListener('change', function() {
            handleSwitchChange(payLater);
        });

        var status = document.querySelector('#status');
        status.addEventListener('change', function() {
            handleSwitchChange(status);
        });

        var atStore = document.querySelector('#at_store');
        atStore.addEventListener('change', function() {
            handleSwitchChange(atStore);
        });

        var atDoorstep = document.querySelector('#at_doorstep');
        atDoorstep.addEventListener('change', function() {
            handleSwitchChange(atDoorstep);
        });

    });

    function test() {
        var tax = document.getElementById("edit_tax").value;
        document.getElementById("update_service").reset();
        document.getElementById("edit_tax").value = tax;
        document.getElementById('edit_service_image').removeAttribute('src');


    }

    $('#service_image_selector').bind('change', function() {
        var filename = $("#service_image_selector").val();
        console.log(filename);
        if (/^\s*$/.test(filename)) {
            $(".file-upload").removeClass('active');
            $("#noFile").text("No file chosen...");
        } else {
            $(".file-upload").addClass('active');
            $("#noFile").text(filename.replace("C:\\fakepath\\", ""));
        }
    });
</script>
<script>
    $(document).ready(function()

        {
            var x = 0; //Initial field counter
            var list_maxField = 10; //Input fields increment limitation

            //Once add button is clicked
            $('.list_add_button').click(function() {
                //Check maximum number of input fields
                if (x < list_maxField) {
                    x++; //Increment field counter
                    var list_fieldHTML = '<div class="row"><div class="col-xs-4 col-sm-4 col-md-4"><div class="form-group"> <label for="question"><?= labels('question', "Quetion") ?></label><input name="faqs[' + x + '][]" type="text" placeholder="Enter the question here" class="form-control"/></div></div><div class="col-xs-7 col-sm-7 col-md-4"><div class="form-group">    <label for="question"><?= labels('answer', "Answer") ?></label><input name="faqs[' + x + '][]" type="text" placeholder="Enter the answer here" class="form-control"/></div></div><div class="col-xs-1 col-sm-7 col-md-1 mt-4"><a href="javascript:void(0);" class="list_remove_button btn btn-danger">-</a></div></div>'; //New input field html 
                    $('.list_wrapper').append(list_fieldHTML); //Add field html
                }
            });

            //Once remove button is clicked
            $('.list_wrapper').on('click', '.list_remove_button', function() {
                $(this).closest('div.row').remove(); //Remove field html
                x--; //Decrement field counter
            });
        });
</script>



<script>
    var partnerSelect = document.getElementById("partner");
    var membersInput = document.getElementById("members");

    partnerSelect.addEventListener("change", function() {
        var selectedOption = partnerSelect.options[partnerSelect.selectedIndex];

        var numberOfMembers = parseInt(selectedOption.getAttribute("data-members"), 10); // Parse as an integer

        membersInput.value = numberOfMembers;

        if (numberOfMembers === 1) {
            membersInput.readOnly = true;
        } else {
            membersInput.readOnly = false;
        }
        var at_store = parseInt(selectedOption.getAttribute("data-at_store"));
        var at_doorstep = parseInt(selectedOption.getAttribute("data-at_doorstep"));

        $("#service_at_store").toggle(at_store === 1);
        $("#service_at_doorstep").toggle(at_doorstep === 1);


    });
</script>