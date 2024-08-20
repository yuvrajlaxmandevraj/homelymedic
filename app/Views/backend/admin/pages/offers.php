<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><?= labels('offers', "Offers") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>">Dashboard</a></div>
                <div class="breadcrumb-item">offers</a></div>
            </div>
        </div>
        <div class="container-fluid card">
            <?= helper('form'); ?>
            <div class="row">
                <div class="col-md">
                    <h2 class='section-title'><?= labels('create_sliders', "Create Offer") ?></h2></label>
                    <div class="card-body">
                        <?= form_open('/admin/offers/add_offer', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_Category', 'enctype' => "multipart/form-data"]); ?>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="type">Type</label>
                                    <select id="type" class="form-control" name="type">
                                        <option value="select_type">Select Type </option>
                                        <option value="default">Default </option>
                                        <option value="Category">Category </option>
                                        <option value="services">Service </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="categories" id="categories_select">
                                        <label for="Category_item">Choose a Category</label>

                                        <select id="Category_item" class="form-control" name="Category_item">
                                            <?php foreach ($categories_name as $Category) : ?>
                                                <option value="<?= $Category['id'] ?>"><?= $Category['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="services" id="services_select">
                                        <label for="service_item">Choose a Service</label>

                                        <select id="service_item" class="form-control" name="service_item">
                                            <?php foreach ($services_title as $service) : ?>
                                                <option value="<?= $service['id'] ?>"><?= $service['title'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="input-group mb-4">
                                        <div class="mb-4">
                                            <label for="formFile" class="form-label">Choose Image</label>
                                            <input class="form-control" type="file" id="formFile" name="image">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="changer" name="changer" checked>
                                        <label class="custom-control-label" for="changer">To Active or Deactive Offers</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md">
                                <div class="form-group">
                                    <!-- <input type="submit" class="btn btn-success " id="upload_offer" value="Submit"> -->
                                    <button type="reset" class="btn btn-warning">Reset</button>
                                    <button type="submit" class="btn btn-success" id="upload_offer">Submit</button>
                                </div>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid card">
            <h2 class='section-title'>Offer details</h2>
            <div class="row">
                <div class="col-lg">
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="col-md">
                                <table class="table " id="user_list" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/offers/list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="true" data-show-columns="true" data-show-columns-search="true" data-show-refresh="true" data-sort-name="id" data-sort-order="DESC">
                                    <thead>
                                        <tr>
                                            <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                            <th data-field="type" class="text-center" data-sortable="true"><?= labels('type', 'Type') ?></th>
                                            <th data-field="type_id" class="text-center" data-sortable="true"><?= labels('type_id', 'Type ID') ?></th>
                                            <th data-field="offer_image" class="text-center"><?= labels('image', 'Image') ?></th>
                                            <th data-field="active_status" class="text-center" data-sortable="true"><?= labels('status', 'Status') ?></th>
                                            <th data-field="created_at" class="text-center" data-sortable="true"><?= labels('created_at', 'Created At') ?></th>
                                            <th data-field="operations" class="text-center" data-events="action_events"><?= labels('operations', 'Operations') ?></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
    </section>
    <!-- update Modal -->
    <div class="modal fade" id="update_modal" tabindex="-1" aria-labelledby="update_modal_thing" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Offers</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= form_open('/admin/offers/update_offer', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'update_offer', 'enctype' => "multipart/form-data"]); ?>
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <h6>select type from here</h6>
                                <select id="type_1" class="form-control" name="type_1">
                                    <option value="default">Default </option>
                                    <option value="Category">Category </option>
                                    <option value="services">Service </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group ">
                                <div class="categories" id="categories_select_1">
                                    <label for="Category_item">Choose a Category</label>

                                    <select id="Category_item" class="form-control" name="Category_item_1">
                                        <?php foreach ($categories_name as $Category) : ?>
                                            <option value="<?= $Category['id'] ?>"><?= $Category['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="services" id="services_select_1">
                                    <label for="service_item">Choose a Service</label>

                                    <select id="service_item_1" class="form-control" name="service_item_1">
                                        <?php foreach ($services_title as $service) : ?>
                                            <option value="<?= $service['id'] ?>"><?= $service['title'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md">
                            <img src="" alt="old_image" id="offer_image" class="w-50">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md">
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <div class="mb-3">
                                        <label for="formFile" class="form-label">Choose Image for Offers</label>
                                        <input class="form-control" type="file" id="formFile" name="image">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input changer_ed" id="changer_1" name="changer_1" checked>
                                    <label class="custom-control-label" for="changer_1">To Active or Deactive Offers </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    <?php form_close() ?>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>        
    </div>
</div>