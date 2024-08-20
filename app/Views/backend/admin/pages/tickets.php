<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><?= labels('tickets', "Ticket Types") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>">Dashboard</a></div>
                <div class="breadcrumb-item">Tickets</a></div>
            </div>
        </div>
        <div class="container-fluid card">
            <?= helper('form'); ?>
            <div class="row">
                <div class="col-md">
                    <h2 class='section-title'><?= labels('manage_tickets', "Manage Ticket Types") ?></h2>
                    <div class="card-body">
                        <?= form_open('admin/tickets/add_tickets', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_tickets', 'enctype' => "multipart/form-data"]); ?>
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input id="title" class="form-control" type="text" name="title" placeholder="Enter the Title here">
                        </div>
                        <button type="reset" class="btn btn-warning">Reset</button>
                        <button type="submit" class="btn btn-success">Submit</button>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid card">
            <div class="row">
                <div class="col-lg">
                    <h2 class='section-title'><?= labels('tickets', "Tickets") ?></h2>
                    <div class="card-body">
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <div class="card">
                                    <table class="table " id="user_list" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/tickets/list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="true" data-show-columns="true" data-show-columns-search="true" data-show-refresh="true" data-sort-name="id" data-sort-order="desc">
                                        <thead>
                                            <tr>
                                                <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                                <th data-field="title" class="text-center" data-sortable="true"><?= labels('title', 'Title') ?></th>
                                                <th data-field="created_at" class="text-center" data-visible="false" data-sortable="true"><?= labels('created_at', 'Created At') ?></th>
                                                <th data-field="operations" class="text-center" data-events="tickets_events"><?= labels('operations', 'Operations') ?></th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>


    <div class="modal fade" id="update_modal" tabindex="-1" aria-labelledby="update_modal_thing" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- <form action="" method="post"> -->
                    <?= form_open('admin/tickets/edit_tickets', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'edit_tickets', 'enctype' => "multipart/form-data"]); ?>
                    <input type="hidden" name="id" id="id">
                    <div class="form-group">
                        <label for="title">title</label>
                        <input id="edit_title" class="form-control" type="text" name="title" placeholder="Enter the title here">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="submit">Save changes</button>
                    <?php form_close() ?>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>