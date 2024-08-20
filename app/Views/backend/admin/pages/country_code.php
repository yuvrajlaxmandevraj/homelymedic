<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('contry_codes', "Country codes") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>

                <div class="breadcrumb-item"><?= labels('contry_codes', "Country codes") ?></a></div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="container-fluid card">
                    <?= helper('form'); ?>
                    <h2 class='section-title'><?= labels('contry_codes', "Country codes") ?></h2>
                    <div class="card-body">
                        <?= form_open('admin/settings/add_contry_code', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_faqs', 'enctype' => "multipart/form-data"]); ?>
                        <div class="form-group">
                            <label for="code"><?= labels('code', "Code") ?></label>
                            <input id="code" class="form-control" type="text" name="code" placeholder="Enter the code here">
                        </div>
                        <div class="form-group">
                            <label for="name"><?= labels('name', "Name") ?></label>
                            <input id="name" class="form-control" type="text" name="name" placeholder="Enter the name here">
                        </div>


                        <div class=" d-flex justify-content-end">

                            <button type="submit" class="btn btn-primary"><?= labels('add_country_code', "Add Country code") ?></button>
                        </div>

                        <?= form_close(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-8">

                <div class="container-fluid card">
                    <div class="row">
                        <div class="col-lg">
                            <h2 class='section-title'><?= labels('contry_codes', "Country Codes") ?></h2>
                            <div class="card-body">
                                <div class="row mt-2">
                                    <div class="col-md-12">


                                        <div class="row pb-3 ">
                                            <div class="col-12">
                                                <div class="row mb-3 ">

                                                    <div class="col-md-4 col-sm-2 mb-2">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="customSearch" placeholder="Search here!" aria-label="Search" aria-describedby="customSearchBtn">
                                                            <div class="input-group-append">
                                                                <button class="btn btn-primary" type="button">
                                                                    <i class="fa fa-search d-inline"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                    </div>


                                                    <div class="dropdown d-inline ml-2">
                                                        <button class="btn export_download dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <?= labels('download', 'Download') ?>
                                                        </button>
                                                        <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                            <a class="dropdown-item" onclick="custome_export('pdf','Tax list','tax_list');"> <?= labels('pdf', 'PDF') ?></a>
                                                            <a class="dropdown-item" onclick="custome_export('excel','Tax list','tax_list');"> <?= labels('excel', 'Excel') ?></a>
                                                            <a class="dropdown-item" onclick="custome_export('csv','Tax list','tax_list')"> <?= labels('csv', 'CSV') ?></a>
                                                        </div>
                                                    </div>


                                                </div>

                                            </div>
                                        </div>
                                        <table class="table" data-pagination-successively-size="2" data-query-params="country_code_params" id="country_code_list" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/settings/fetch_contry_code") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="false" data-show-columns="false" data-show-columns-search="true" data-show-refresh="false" data-sort-name="id" data-sort-order="desc">
                                            <thead>
                                                <tr>
                                                    <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                                    <th data-field="name" class="text-center" data-sortable="true"><?= labels('name', 'Name') ?></th>
                                                    <th data-field="code" class="text-center" data-sortable="true"><?= labels('code', 'Code') ?></th>
                                                    <th data-field="default" class="text-center" data-sortable="true"><?= labels('default', 'Default') ?></th>

                                                    <th data-field="created_at" class="text-center" data-visible="false" data-sortable="true"><?= labels('created_at', 'Created At') ?></th>
                                                    <th data-field="operations" class="text-center" data-events="Countr_code_events"><?= labels('operations', 'Operations') ?></th>
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
        </div>

    </section>

    <!-- update modal -->
    <div class="modal fade" id="update_modal" tabindex="-1" aria-labelledby="update_modal_thing" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header m-0 p-0">
                    <div class="row pl-3 w-100">
                        <div class="col-12 " style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('update_country_code', 'Update Country Code') ?></div>

                        </div>

                    </div>

                </div>

                <div class="modal-body">

                    <?= form_open('admin/settings/update_country_codes', ['method' => "post", 'class' => 'form-submit-event', 'id' => 'add_Category', 'enctype' => "multipart/form-data"]); ?>
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code"><?= labels('code', "Code") ?></label>
                                <input id="edit_code" class="form-control" type="text" name="code" placeholder="Enter the code here">
                            </div>

                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name"><?= labels('name', "Name") ?></label>
                                <input id="edit_name" class="form-control" type="text" name="name" placeholder="Enter the name here">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="id" id="id">



                    <div class="modal-footer">
                        <button type="submit" class="btn bg-new-primary submit_btn"><?= labels('update_country_code', 'Update Country Code  ') ?></button>
                        <?php form_close() ?>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= labels('close', "Close") ?></button>
                    </div>
                </div>
            </div>
        </div>


    </div>

</div>
<script>
    $("#customSearch").on('keydown', function() {
        $('#country_code_list').bootstrapTable('refresh');
    });

    function country_code_params(p) {
        return {
            search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,

        };
    }


    $(document).ready(function() {
        window.Countr_code_events = {
            "click .delete-country_code": function(e, value, row, index) {
                console.log(row);
                var id = row.id;
                Swal.fire({
                    title: are_your_sure,
                    text: "You won't be able to revert this !",
                    icon: "error",
                    showCancelButton: true,
                    confirmButtonText: yes_proceed,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(
                            baseUrl + "/admin/settings/delete_contry_code", {
                                [csrfName]: csrfHash,
                                id: id,



                            },
                            function(data) {
                                csrfName = data.csrfName;
                                csrfHash = data.csrfHash;
                                console.log(data);
                                if (data.error == false) {
                                    showToastMessage(data.message, "success");
                                    setTimeout(() => {
                                        $("#country_code_list").bootstrapTable("refresh");

                                    }, 2000);
                                    return;
                                } else {
                                    return showToastMessage(data.message, "error");
                                }
                            }
                        );
                    }
                });
            },
            "click .edit_country_code": function(e, value, row, index) {

                $("#id").val(row.id);
                $("#edit_name").val(row.name);
                $("#edit_code").val(row.code);




            },
        };
    });
</script>

<script type="text/javascript">
    $(document).on('click', '.store_default_language', function() {
        var id = $(this).data("id");
        var base_url = baseUrl;

        $.ajax({
            url: baseUrl + "/admin/settings/store_default_language",
            type: "POST",
            dataType: "json",
            data: {
                id: id
            },
            success: function(result) {
                if (result) {
                     iziToast.success({
                        title: "Success",
                        message: result.message,
                        position: "topRight",
                    })
                    $("#country_code_list").bootstrapTable("refresh");
                }
            }
        });
    });
</script>