<!-- Main Content -->
<div class="main-content">
    <section class="section" id="pill-about_us" role="tabpanel">
        <div class="section-header mt-2">
            <h1><?= labels('cash_collection', "Cash Collection") ?> <span class="breadcrumb-item p-3 pt-2 text-primary"><i data-content="Admin commission amounts for COD bookings will be managed here. The amount will be credited once the booking status is completed. For example, if a customer books a service for $100 as COD, the provider will receive the total booking amount of $100 in cash. The provider will then need to pay the admin their commission amount of 10%, which in this case is $10. The calculation of this amount will be managed here." class="fa fa-question-circle"></i></span></h1>




            <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="<?= base_url('partner/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>

                <div class="breadcrumb-item"><?= labels('cash_collection', "Cash Collection") ?></div>
                <div>

                </div>
            </div>
        </div>


        <div class="card">
            <div class="card-body">





                <div class="col-md-12">

                    <div class="row mt-4 mb-3">

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
                        <button class="btn btn-secondary  ml-2 filter_button" id="filterButton">
                            <span class="material-symbols-outlined mt-1">
                                filter_alt
                            </span>

                        </button>

                        <div class="dropdown d-inline ml-2">
                            <button class="btn export_download dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= labels('download', "Download") ?> 
                            </button>
                            <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                                <a class="dropdown-item" onclick="custome_export('pdf','Cash Collecttion list','cash_collection');"><?= labels('pdf', "PDF") ?> </a>
                                <a class="dropdown-item" onclick="custome_export('excel','Cash Collecttion list','cash_collection');"><?= labels('excel', "Excel") ?> </a>
                                <a class="dropdown-item" onclick="custome_export('csv','Cash Collecttion list','cash_collection')"><?= labels('csv', "CSV") ?> </a>
                            </div>
                        </div>


                     
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-borderd" data-pagination-successively-size="2" id="cash_collection" data-show-export="false" data-export-types="['txt','excel','csv']" data-export-options='{"fileName": "invoice-order-list","ignoreColumn": ["action"]}' data-auto-refresh="true" data-show-columns="false" data-search="false" data-show-refresh="false" data-toggle="table" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-side-pagination="server" data-pagination="true" data-url="<?= base_url('partner/cash_collection_list'); ?>" data-sort-name="id" data-sort-order="desc" data-query-params="cash_collection_query">
                            <thead>
                                <tr>

                                    <!-- <th data-field="partner_id" data-visible="false" data-sortable="true"> <?= labels('partner_id', 'Partner Id')  ?> </th> -->
                                    <th data-field="partner_name" data-visible="true"><?= labels('partner_name', 'Provider')  ?></th>
                                    <th data-field="message" data-visible="true"><?= labels('message', 'message')  ?></th>
                                    <th data-field="commison" data-visible="true"><?= labels('amount', 'amount')  ?></th>
                                    <th data-field="order_id" data-visible="true"><?= labels('order_id', 'Order ID')  ?></th>

                                    <th data-field="date" data-visible="true"><?= labels('date', 'date')  ?></th>


                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

    </section>
</div>
<div id="filterBackdrop"></div>

<div class="drawer" id="filterDrawer">
    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="bg-new-primary" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center;">
                        <div class="bg-white m-3 text-new-primary" style="box-shadow: 0px 8px 26px #00b9f02e; display: inline-block; padding: 10px; height: 45px; width: 45px; border-radius: 15px;">
                            <span class="material-symbols-outlined">
                                filter_alt
                            </span>
                        </div>

                        <h3 class="mb-0" style="display: inline-block; font-size: 16px; margin-left: 10px;"><?= labels('filters', "Filters") ?></h3>
                    </div>

                    <div id="cancelButton" style="cursor: pointer;">
                        <span class="material-symbols-outlined mr-2">
                            cancel
                        </span>
                    </div>
                </div>

                <!-- <div class="row"> -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="cash_collection_filter"><?= labels('filter_cash_collection', 'Filter Cash Collection History') ?></label>
                        <select name="cash_collection_filter" id="cash_collection_filter" class="form-control selectric">
                            <option value=""><?= labels('select', 'Select') ?>-</option>
                            <option value="provider_cash_recevied"><?= labels('provider_cash_recevied', 'Provider Cash Received') ?></option>
                            <option value="admin_cash_recevied"><?= labels('admin_cash_recevied', 'Admin Cash Received') ?></option>

                        </select>
                    </div>
                </div>


                <div class="row mt-4 mx-2">
                    <div class="col-md-12">
                        <div class="form-group ">
                            <label for="table_filters"><?= labels('table_filters', 'Table filters') ?></label>
                            <div id="columnToggleContainer">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4 ">
                    <div class="col-md-4 ml-2">
                        <button class="btn bg-new-primary d-block" id="filter">
                            <?= labels('apply_filter', 'Apply Filter') ?>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>
<script>
    var cash_collection_filter = "";
    $('#cash_collection_filter').on('change', function() {
        cash_collection_filter = $(this).find('option:selected').val();
    });
    $('#filter').on('click', function(e) {
        $('#cash_collection').bootstrapTable('refresh');
    });
    $("#customSearch").on('keydown', function() {
        $('#user_list').bootstrapTable('refresh');
    });


    function cash_collection_query(p) {
        return {
            search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            cash_collection_filter: cash_collection_filter,

        };
    }
    $(function() {
        $('.fa').popover({
            trigger: "hover"
        });
    })
</script>




<script>
    $(document).ready(function() {
        for_drawer("#filterButton", "#filterDrawer", "#filterBackdrop", "#cancelButton");
        var columns = [
            {
                field: 'partner_name',
                label: '<?= labels('partner_name', 'Provider')  ?>'
            },
            {
                field: 'message',
                label: '<?= labels('message', 'message')  ?>'
            },
            {
                field: 'commison',
                label: '<?= labels('amount', 'amount')  ?>',
              
            },

            {
                field: 'order_id',
                label: '<?= labels('order_id', 'Order ID')  ?>',
                visible: false

            },

            {
                field: 'date',
                label: '<?= labels('date', 'date')  ?>',
            },


        ];
        setupColumnToggle('cash_collection', columns, 'columnToggleContainer');
    });
</script>