<div class="main-content">

    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('services', "Services") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><?= labels('services', 'Services') ?></a></div>
            </div>
        </div>
        <?= form_open('/admin/services/add_service', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_service', 'enctype' => "multipart/form-data"]); ?>





        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="row pl-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('add_service_details', 'Add Service Details') ?></div>
                        </div>
                    </div>
                    <div id="product_category_tree_view_html" class='category-tree-container'></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="jquery-script-clear"></div>
                                <div class="categories" id="categories">

                                    <label for="partner"><?= labels('select_provider', 'Select Provider') ?></label> <br>
                                    <select id="partner" class="form-control w-100" name="partner">
                                        <option value=""><?= labels('select_provider', 'Select Provider') ?></option>
                                        <?php foreach ($partner_name as $pn) : ?>
                                            <option value="<?= $pn['id'] ?>"><?= $pn['company_name'] . ' - ' . $pn['username'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="categories" id="categories">
                                    <label for="category_item"><?= labels('choose_a_category_for_your_service', 'Choose a Category for your service') ?></label>
                                    <div id="jstree"></div>
                                    <input type="hidden" id="selected_category" name="selected_category" />

                                </div>
                            </div>




                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class=""><?= labels('title_of_the_service', 'Title of the service') ?> </label>
                                    <input class="form-control" type="text" name="title">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tags"><?= labels('tags', 'Tags') ?></label><br>
                                    <input id="tags" style="border-radius: 0.25rem" class="w-100" type="text" name="tags[]" placeholder="press enter to add tag">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="short_description"><?= labels('short_description', "Short Description") ?></label>
                                    <textarea rows=4 class='form-control' name="description"></textarea>
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
                                    <label for="duration"><?= labels('duration_to_perform_task', 'Duration to Perform Task') ?></label>
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
                                    <label for="members"><?= labels('members_required_to_perform_task', 'Members Required to Perform Task') ?></label>
                                    <input id="members" class="form-control" type="number" name="members" min="0" oninput="this.value = Math.abs(this.value)" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('members_required_to_perform_task', 'Members Required to Perform Task') ?> <?= labels('here', ' Here ') ?>" min="0">
                                </div>
                            </div>


                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_qty"><?= labels('max_quantity_allowed_for_services', 'Max Quantity allowed for services') ?></label>
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
                                <div class="form-group"> <label for="image"><?= labels('image', 'Image') ?></label>
                                    <input type="file" name="service_image_selector" class="filepond logo" id="service_image_selector" accept="image/*">

                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group"> <label for="image"><?= labels('other_images', 'Other Image') ?></label>
                                    <input type="file" name="other_service_image_selector[]" class="filepond logo" id="other_service_image_selector" accept="image/*" multiple>

                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group"> <label for="image"><?= labels('files', 'Files') ?></label>
                                    <input type="file" name="files[]" class="filepond-docs logo" id="files" multiple>


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
                                <label for="Description"><?= labels('description', 'Description') ?></label>
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
                                    <label for="tax_type"><?= labels('price', 'Price') ?> <?= labels('type', 'Type') ?></label>
                                    <select name="tax_type" id="tax_type" class="form-control">
                                        <option value="excluded"><?= labels('tax_excluded_in_price', 'Tax Excluded In Price') ?></option>
                                        <option value="included"><?= labels('tax_included_in_price', 'Tax Included In Price') ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="jquery-script-clear"></div>
                                <div class="" id="">

                                    <label for="partner"><?= labels('select_tax', 'Select Tax') ?></label> <br>
                                    <select id="tax" name="tax_id" class="form-control w-100" name="tax">
                                        <option value=""><?= labels('select_tax', 'Select Tax') ?></option>
                                        <?php foreach ($tax_data as $pn) : ?>
                                            <option value="<?= $pn['id'] ?>"><?= $pn['title'] ?>(<?= $pn['percentage'] ?>%)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="price"><?= labels('price', 'Price') ?></label>
                                    <input id="price" class="form-control" type="number" name="price" placeholder="price" min="1" oninput="this.value = Math.abs(this.value)">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="discounted_price"><?= labels('discounted_price', 'Discounted Price') ?></label>
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
                            <div class="col-md-4">

                                <div class="form-group">
                                    <label class="" for="is_cancelable"><?= labels('is_cancelable_?', 'Is Cancelable ')  ?></label>
                                    <input type="checkbox" id="is_cancelable" name="is_cancelable" class="status-switch">

                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="" for="pay_later"><?= labels('pay_later_allowed', 'Pay Later Allowed') ?></label>
                                    <input type="checkbox" id="pay_later" name="pay_later" class="status-switch">

                                </div>

                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="" for="status"><?= labels('status', 'Status') ?></label>

                                    <input type="checkbox" id="status" name="status" class="status-switch">

                                </div>
                            </div>

                            <div class="row" id="cancel_order">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="cancelable_till"><?= labels('cancelable_before', 'Cancelable before') ?></label>
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
                        <div class="row">
                            <?php
                            $settings = get_settings('general_settings', true);
                            if (isset($settings)) {
                                if (isset($settings['at_store']) && $settings['at_store'] == 1) {
                                    echo '<div class="col-md-4">
                                            <div class="form-group">
                                                <label class="" for="at_store">' . labels('at_store', 'At Store') . '</label>
                                                <input type="checkbox" id="at_store" name="at_store" class="status-switch">
                                            </div>
                                        </div>';
                                }

                                if (isset($settings['at_doorstep']) && $settings['at_doorstep'] == 1) {
                                    echo '<div class="col-md-4">
                                            <div class="form-group">
                                                <label class="" for="at_doorstep">' . labels('at_doorstep', 'At Doorstep') . '</label>
                                                <input type="checkbox" id="at_doorstep" name="at_doorstep" class="status-switch">
                                            </div>
                                        </div>';
                                }
                            }
                            ?>

                        </div>



                    </div>
                </div>
            </div>


        </div>




        <div class="row mb-3">
            <div class="col-md d-flex justify-content-end">

                <button type="submit" class="btn btn-lg bg-new-primary"><?= labels('save', 'Save') ?></button>
                <input type="reset" class="btn btn-lg bg-danger text-white ml-2" value="<?= labels('Reset', 'Reset') ?>" class="btn btn-danger btn-block">
                <?= form_close() ?>

            </div>

        </div>
        <div class="container-fluid card">
            <h2 class='section-title'><?= labels('all_services', "All Services") ?></h2>
            <div class="row ">
                <div class="col-md-12">
                    <table class="table " id="service_list" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/services/list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="true" data-show-columns="true" data-show-columns-search="true" data-show-refresh="true" data-sort-name="id" data-sort-order="desc">
                        <thead>
                            <tr>
                                <!-- EVRY VISIBLE DATA HERE -->
                                <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                <th data-field="title" class="text-center" data-sortable="true"><?= labels('title', 'Title') ?></th>
                                <th data-field="tags" class="text-center" data-sortable="true" data-visible="false"><?= labels('tags ', 'Tags') ?></th>
                                <th data-field="image_of_the_service" class="text-center"><?= labels('image ', 'Image') ?></th>
                                <th data-field="price" class="text-center" data-sortable="true"><?= labels('price ', 'Price') ?></th>
                                <th data-field="discounted_price" class="text-center" data-sortable="true"><?= labels('discounted_price ', 'Discounted price') ?></th>
                                <th data-field="rating" class="text-center" data-sortable="true"><?= labels('rating ', 'Rating') ?></th>
                                <th data-field="status_badge" class="text-center" data-sortable="true"><?= labels('status ', 'Status') ?></th>
                                <th data-field="category_id" class="text-center" data-sortable="true" data-visible="false"><?= labels('category_id', 'Category ID') ?></th>
                                <!-- EVERY NONVISIBLE DATA HERE -->
                                <!-- <th data-field="tax_id" class="text-center" data-sortable="true" data-visible="false"><?= labels('tax_id ', 'Tax ID') ?></th> -->
                                <th data-field="tax_type" class="text-center" data-sortable="true" data-visible="false"><?= labels('taxe_type', 'Tax Type') ?></th>
                                <th data-field="number_of_members_required" class="text-center" data-sortable="true" data-visible="false"><?= labels('members_required ', 'Members required') ?></th>
                                <th data-field="duration " class="text-center" data-sortable="true" data-visible="false"><?= labels('duration ', 'Duration') ?></th>
                                <th data-field="number_of_ratings" class="text-center" data-sortable="true" data-visible="false"><?= labels('numbers_of_rating ', 'Numbers of Rating') ?></th>

                                <th data-field="max_quantity_allowed" class="text-center" data-sortable="true" data-visible="false"><?= labels('max_quantity_allowed ', 'Max Quantity Allowed') ?></th>
                                <th data-field="is_pay_later_allowed" class="text-center" data-sortable="true" data-visible="false"><?= labels('pay_later_allowed ', 'Pay Later Allowed') ?></th>
                                <th data-field="created_at" class="text-center" data-sortable="true"><?= labels('created_at', 'Created At') ?></th>
                                <th data-field="operations" class="text-center" data-events="services_events_admin"><?= labels('operations', 'Operations') ?></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(document).ready(function() {

        $('#is_cancelable').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        $('#pay_later').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        $('#status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        $('#at_store').siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        $('#at_doorstep').siblings('.switchery').addClass('deactive-content').removeClass('active-content');



        // var is_cancelable = document.querySelector('#is_cancelable');
        // is_cancelable.onchange = function(e) {

        //     if (is_cancelable.checked) {
        //         $(this).siblings('.switchery').addClass('active-content').removeClass('deactive-content');


        //     } else {

        //         $(this).siblings('.switchery').addClass('deactive-content').removeClass('active-content');


        //     }

        // };



        // var pay_later = document.querySelector('#pay_later');
        // pay_later.onchange = function(e) {

        //     if (pay_later.checked) {
        //         $(this).siblings('.switchery').addClass('active-content').removeClass('deactive-content');

        //     } else {
        //         $(this).siblings('.switchery').addClass('deactive-content').removeClass('active-content');

        //     }

        // };



        // var status = document.querySelector('#status');
        // status.onchange = function(e) {
        //     console.log(status.checked);
        //     if (status.checked) {
        //         $(this).siblings('.switchery').addClass('active-content').removeClass('deactive-content');

        //     } else {
        //         $(this).siblings('.switchery').addClass('deactive-content').removeClass('active-content');
        //     }
        // };


        // var at_store = document.querySelector('#at_store');
        // at_store.onchange = function(e) {

        //     if (at_store.checked) {
        //         $(this).siblings('.switchery').addClass('active-content').removeClass('deactive-content');


        //     } else {

        //         $(this).siblings('.switchery').addClass('deactive-content').removeClass('active-content');


        //     }

        // };

        // var at_doorstep = document.querySelector('#at_doorstep');
        // at_doorstep.onchange = function(e) {

        //     if (at_doorstep.checked) {
        //         $(this).siblings('.switchery').addClass('active-content').removeClass('deactive-content');


        //     } else {

        //         $(this).siblings('.switchery').addClass('deactive-content').removeClass('active-content');


        //     }

        // };

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
    $(document).ready(function() {
        // Initialize JSTree with checkboxes
        $('#jstree').jstree({
            'core': {
                'data': <?= json_encode($categories_tree) ?> // Replace this with your categories tree data
            },
            'plugins': ['wholerow', 'checkbox'], // Enable the checkbox plugin
            'checkbox': {
                'three_state': false, // Set to true if you want parent nodes to behave like checkboxes
                'cascade_to_hidden': false, // Set to true if you want to include checkbox values in the form submission
                'multiple': false // Set to false to allow only single selection
            }
        });

        // Handle category selection
        $('#jstree').on('changed.jstree', function(e, data) {
            var selectedNode = data.instance.get_selected(true);
            if (selectedNode.length > 0) {
                var selectedCategory = selectedNode[0].id;
                $('#selected_category').val(selectedCategory);
            } else {
                $('#selected_category').val('');
            }
        });
    });
</script>