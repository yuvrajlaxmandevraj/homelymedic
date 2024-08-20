<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><?= labels('tickets', "Tickets") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item">Services</a></div>
            </div>
        </div>
        <div class="container-fluid card">
            <h2 class='section-title'><?= labels('all_tickets', "All Tickets") ?></h2>
            <div class="row">
                <div class="col-lg">
                    <div class="row mt-4">
                        <div class="col-12">
                            <table class="table " id="ticket_list" data-detail-view="true" data-detail-formatter="user_formater" data-auto-refresh="true" data-toggle="table" data-url="<?= base_url("admin/show_tickets/list") ?>" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="true" data-show-columns="true" data-show-columns-search="true" data-show-refresh="true" data-sort-name="id" data-sort-order="desc">
                                <thead>
                                    <tr>
                                        <th data-field="id" class="text-center" data-sortable="true"><?= labels('id', 'ID') ?></th>
                                        <th data-field="user_id" class="text-center" data-visible="false" data-sortable="true"><?= labels('user_id', 'User Id') ?></th>
                                        <th data-field="ticket_type_id" class="text-center" data-visible="false" data-sortable="true"><?= labels('ticket_type_id', 'Ticket Type Id') ?></th>
                                        <th data-field="title" class="text-center" data-sortable="true"><?= labels('title', 'Type') ?></th>
                                        <th data-field="email" class="text-center" data-sortable="true"><?= labels('email ', 'Email') ?></th>
                                        <th data-field="username" class="text-center" data-sortable="true"><?= labels('username ', 'Username') ?></th>
                                        <th data-field="subject" class="text-center" data-sortable="true"><?= labels('subject ', 'Subject') ?></th>
                                        <th data-field="description" class="text-center" data-sortable="true"><?= labels('description ', 'Description') ?></th>
                                        <th data-field="status" class="text-center" data-sortable="true"><?= labels('status ', 'Status') ?></th>
                                        <th data-field="created_at" class="text-center" data-sortable="true"><?= labels('created_at', 'Created At') ?></th>
                                        <th data-field="operation" class="text-center" data-events="chat_events"><?= labels('operations', 'Operations') ?></th>
                                    </tr>
                                </thead>    
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="ticket_modal" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">

                    <div class="row h5 align-items-baseline">
                        <div class="col-md mr-n3">
                            <span>
                                Support Ticket of
                            </span>
                        </div>
                        <div class="col-md">
                            <span class="modal-title text-danger" id="user_chat">
                                User Name
                            </span>
                        </div>
                    </div>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="support_data d-flex" title="Support Detail Section">
                        <span class="text-muted" id="ticket_type" title="Support Issue">
                            SUPPORT ISSUE
                        </span>
                        <span class="card-title text-muted border badge ml-2" id="subject" title="Subject">
                            Subject
                        </span>
                        <span id="status" class="ml-2" title="Status">
                            status
                        </span>
                    </div>
                    <div class="support_description d-flex flex-column" title="Support Detail Section">
                        <label for="description" class="text-primary" title="Description Paragraph">
                            Description
                        </label>
                        <span class="card-title text-muted" id="description" title="Description Paragraph">
                            Description paragraph
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <div class="">
                            <label for="date_created" class="text-primary">
                                Date Created
                            </label>
                            <p id="date_created" class="text-muted">
                                2022-07-20
                            </p>
                        </div>
                        <div class="ml-5">
                            <label for="ticket-status" class="text-primary">
                                Ticket Status
                            </label>
                            <div class="form-group">
                                <select class="form-control" id="ticket-status">
                                    <option value="1">Change Ticket Status</option>
                                    <option value="2">OPEN</option>
                                    <option value="3">RESOLVE</option>
                                    <option value="4">CLOSE</option>
                                    <option value="5">REOPEN</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
                <?php
                $offset = 0;
                $limit = 10;
                ?>


                <div class="card mx-4">
                    <div id="chat-box">
                        <div class="ticket_msg" id="element" data-limit="<?= $limit ?>" data-offset="<?= $offset ?>" data-max-loaded="false">
                        </div>
                        <div class="scroll_div"></div>
                    </div>
                </div>
                <!-- <div class="row">
                </div> -->


                <div class="modal-footer" id="footer-custom">
                    <form action="<?= base_url('admin/show_tickets/send_message') ?>" method="post" id="send_message" enctype="multipart/form-data">
                        <div class="row" id="custom-row">
                            <input type="hidden" name="ticket_id" id="ticket_id" class="ticket_id">
                            <input type="hidden" name="user_id" id="user_id" class="user_id">
                            <input type="hidden" name="id" id="id">
                            <div class="col-md-10 col-sm-10" id="input_group">
                                <div class="form-group">
                                    <input type="text" name="message" id="message" class="form-control message" placeholder="Type message here...">
                                </div>
                            </div>
                            <div class="col-md-1 col-sm-1">
                                <label for="file_chat" id="file" class="bg-primary border rounded">
                                    <i class="fa-solid fa-paperclip text-white"></i>
                                </label>
                                <input type="file" name="file_chat[]" class="form-control d-none" id="file_chat" multiple>
                            </div>
                            <div class="col-md-1 col-sm-1">
                                <input type="submit" value="submit" id="send" class="btn btn-primary">
                            </div>
                        </div>
                        </form>
                </div>
            </div>
        </div>
    </div>
</div>

</div>


<style>

</style>