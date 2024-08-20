<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><?= labels('services', "Services") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item"><?= labels('services', 'Services') ?></a></div>
            </div>
        </div>
        <?= form_close() ?>
        <div class="row mb-3">
            <div class="col-lg-6 col-sm-12 col-xxs-12">
                <div class="card   h-100">
                    <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="toggleButttonPostition"><?= labels('add_service_details', 'Add Service Details') ?></div>

                    </div>
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
                                <!-- <div class="jquery-script-clear"></div> -->
                                <div class="categories" id="categories">
                                    <label for="category_item"><?= labels('choose_a_category_for_your_service', 'Choose a Category for your service') ?></label>
                                    <select id="category_item" class="form-control" name="categories" style="margin-bottom: 20px;">
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
                                    <label for="title" class=""><?= labels('title_of_the_service', 'Title of the service') ?> </label>
                                    <input class="form-control" type="text" name="title">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_tags"><?= labels('tags', 'Tags') ?></label>
                                    <input id="edit_service_tags" class="w-100 " type="text" name="tags[]" placeholder="press enter to add tag">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="Description"><?= labels('description', 'Description') ?></label>
                                    <textarea style="min-height:60px" rows=2 class='form-control' name="description"></textarea>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-file">
                                    <div class="form-group">
                                        <?= labels('service_image', "Service Image") ?>
                                        <input type="file" class="custom-file-input" id="edit_service_image_selector" name="service_image_selector" accept='image/*' onchange="loadFile(event)">
                                        <input type="hidden" class="form-control" name="old_icon" id="old_icon">
                                        <label class="custom-file-label mt-4" for="edit_service_image_selector"><?= labels('choose_file', "Choose file") ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mt-4" id="service_image_section">
                                <div class="form-group image">
                                    <img src="<?= base_url('public/backend/assets/img/news/img01.jpg') ?>" alt="Service Image" width="30%" id="edit_service_image">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-sm-12 col-xxs-12">

                <div class="card h-100">
                    <div class="row pl-2">
                        <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('perform_task', 'Perform Task') ?></div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duration"><?= labels('duration_to_perform_task', 'Duration to Perform Task') ?></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend  ">
                                            <span class="mySpanClass input-group-text"><?= labels('minutes', 'Minutes') ?></span>
                                        </div>
                                        <input type="number" style="height: 42px;" class="form-control" name="duration" id="duration" placeholder="<?= labels('duration_to_perform_task', 'Duration to Perform service') ?>" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="members"><?= labels('members_required_to_perform_task', 'Members Required to Perform Task') ?></label>
                                    <input id="members" class="form-control" type="number" name="members" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('members_required_to_perform_task', 'Members Required to Perform Task') ?> <?= labels('here', ' Here ') ?>" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="max_qty"><?= labels('max_quantity_allowed_for_services', 'Max Quantity allowed for services') ?></label>
                                    <input id="max_qty" class="form-control" type="number" name="max_qty" placeholder="<?= labels('max_quantity_allowed_for_services', 'Max Quantity allowed for services') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-lg-12 col-sm-12 col-xxs-12">
                <div class="card   h-100">
                    <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="toggleButttonPostition"><?= labels('price_details', 'Price Details') ?></div>

                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tax_type"><?= labels('price', 'Price') ?> <?= labels('type', 'Type') ?></label>
                                    <select name="tax_type" id="tax_type" class="form-control">
                                        <option value="excluded"><?= labels('tax_excluded_in_price', 'Tax Excluded In Price') ?></option>
                                        <option value="included"><?= labels('tax_included_in_price', 'Tax Included In Price') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
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
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="discounted_price"><?= labels('discounted_price', 'Discounted Price') ?></label>
                                    <input id="discounted_price" class="form-control" type="number" name="discounted_price" placeholder="<?= labels('enter', 'Enter') ?> <?= labels('discounted_price', 'Discounted Price') ?> <?= labels('here', ' Here ') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price"><?= labels('price', 'Price') ?></label>
                                    <input id="price" class="form-control" type="number" name="price" placeholder="price">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="row mb-3">
            <div class="col-lg-12 col-sm-12 col-xxs-12">
                <div class="card   h-100">
                    <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                        <div class="toggleButttonPostition"><?= labels('perform_task', 'Perform Task') ?></div>

                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_cancelable" name="is_cancelable">
                                    <label class="custom-control-label" for="is_cancelable"><?= labels('is_cancelable_?', 'Is Cancelable ')  ?></label>
                                </div>


                                <!-- <?= labels('is_cancelable_?', 'Is Cancelable ')  ?>
                                <label class="toggle">
                                    
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                    <span class="labels" data-on="Active" data-off="Inactive"></span>
                                </label> -->
                            </div>


                            <div class="col-md-4">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="pay_later" name="pay_later">
                                    <label class="custom-control-label" for="pay_later"><?= labels('pay_later_allowed', 'Pay Later Allowed') ?></label>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="cancel_order">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cancelable_till"><?= labels('cancelable_before', 'Cancelable before') ?></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend  ">
                                            <span class="mySpanClass input-group-text">Minutes</span>
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

        <?= form_close() ?>




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
<!-- update Service -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?= labels('update_service', 'Update Service') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open(
                    '/admin/services/update_service',
                    ['method' => "post", 'class' => 'form-submit-event', 'id' => 'update_service', 'enctype' => "multipart/form-data"]
                ); ?>
                <input type="hidden" name="service_id" id="service_id">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="edit_partner"><?= labels('select_provider', 'Select Provider') ?></label> <br>
                        <select id="edit_partner" class="form-control w-100" name="partner">
                            <?php foreach ($partner_name as $pn) : ?>
                                <option value="<?= $pn['id'] ?>"><?= $pn['company_name']  ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_title"><?= labels('title_of_the_service', 'Title of the service') ?></label>
                            <input class="form-control" type="text" name="title" id="edit_title">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="jquery-script-clear"></div>
                        <div class="categories" id="categories">
                            <label for="edit_category_item"><?= labels('choose_a_category_for_your_service', 'Choose a Category for your service') ?></label>
                            <select id="edit_category_item" class="form-control" name="categories">
                                <?php foreach ($categories_name as $category) : ?>
                                    <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_tax_type"><?= labels('tax_type', 'Tax Type') ?></label>
                            <select name="tax_type" id="edit_tax_type" class="form-control">
                                <option value="excluded"><?= labels('excluded', 'Excluded') ?></option>
                                <option value="included"><?= labels('included', 'Included') ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="jquery-script-clear"></div>
                        <div class="" id="">
                            <label for="partner"><?= labels('select_tax', 'Select Tax') ?></label> <br>
                            <select id="edit_tax" name="edit_tax_id" class="form-control w-100">
                                <option value=""><?= labels('select_tax', 'Select Tax') ?></option>
                                <?php foreach ($tax_data as $pn) : ?>
                                    <option value="<?= $pn['id'] ?>"><?= $pn['title'] ?>(<?= $pn['percentage'] ?>%)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_price"><?= labels('price', 'Price') ?></label>
                            <input id="edit_price" class="form-control" type="number" name="price" placeholder="<?= labels('price', 'Price') ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_discounted_price"><?= labels('discounted_price', 'Discounted Price') ?></label>
                            <input id="edit_discounted_price" class="form-control" type="number" name="discounted_price" placeholder="<?= labels('discounted_price', 'Discounted Price') ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_members"><?= labels('members_required_to_perform_task', 'Number of Members Required to Perform Task') ?></label>
                            <input id="edit_members" class="form-control" type="number" name="members" placeholder="<?= labels('members_required_to_perform_task', 'Number of Members Required to Perform Task') ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_max_qty"><?= labels('max_quantity_allowed_for_services', 'Max Quantity allowed for services') ?></label>
                            <input id="edit_max_qty" class="form-control" type="number" name="max_qty" placeholder="<?= labels('max_quantity_allowed_for_services', 'Max Quantity allowed for services') ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_duration"><?= labels('duration_to_perform_task', 'Duration to Perform Task') ?></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <span><?= labels('minutes', 'Minutes') ?></span>
                                    </div>
                                </div>
                                <input type="number" style="height: 42px;" class="form-control" name="duration" id="edit_duration" placeholder="Duration of the Service" min="0" value="">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-file">
                            <div class="form-group">
                                <?= labels('service_image', "Service Image") ?>
                                <input type="file" class="custom-file-input" id="edit_service_image_selector" name="service_image_selector" accept='image/*' onchange="loadFile(event)">
                                <input type="hidden" class="form-control" name="old_icon" id="old_icon">
                                <label class="custom-file-label mt-4" for="edit_service_image_selector"><?= labels('choose_file', "Choose file") ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mt-3" id="service_image_section">
                        <div class="form-group image">
                            <img src="<?= base_url('public/backend/assets/img/news/img01.jpg') ?>" alt="Service Image" width="30%" id="edit_service_image">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="edit_pay_later" name="pay_later">
                            <label class="custom-control-label" for="edit_pay_later"><?= labels('pay_later_allowed', 'Pay Later Allowed') ?></label>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="edit_is_cancelable" name="edit_is_cancelable">
                            <label class="custom-control-label" for="edit_is_cancelable"><?= labels('cancelable_before', 'Cancelable before') ?></label>
                        </div>
                    </div>
                    <div class="col-lg-3 edit_cancelable-till" id="edit_cancelable_till">
                        <div class="form-group">
                            <label for="edit_cancelable_till"><?= labels('cancelable_before', 'Cancelable before ?') ?></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <span><?= labels('minutes', 'Minutes') ?></span>
                                    </div>
                                </div>
                                <input type="number" id="edit_cancelable_till_value" class="form-control" style="height: 42px;" name="edit_cancelable_till" placeholder="Ex. 30" min="0" value="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_description"><?= labels('description', 'Description') ?></label>
                            <textarea rows='2' cols='30' class='form-control h-50' name="description" id="edit_description"></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mt-4">
                            <label for="edit_tags"><?= labels('tags', 'Tags') ?></label>
                            <input id="edit_service_tags" class="w-100" type="text" name="tags[]" placeholder="press enter to add tag">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Status<span class="text-danger text-sm">*</span></label>
                            <br>
                            <div id="edit_status" class="btn-group col-sm-8" style="margin-left: -11px;">
                                <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                    <input type="radio" name="edit_status" id="edit_status_active" value="1">Active </label>
                                <label class="btn btn-danger" data-toggle-class="btn-danger" data-toggle-passive-class="btn-default">
                                    <input type="radio" name="edit_status" id="edit_status_deactive" value="0" checked=""> Deactive </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <input type="submit" value="<?= labels('update_service', 'Update Service') ?>" id="service_submit" class="btn btn-success btn-block">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <input type="button" onclick="test()" value="<?= labels('Reset', 'Reset') ?>" class="btn btn-danger btn-block">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal"><?= labels('close', 'Close') ?></button>
                        </div>
                    </div>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<script>
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