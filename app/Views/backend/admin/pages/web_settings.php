<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('web_settings', "Web settings") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>

                <div class="breadcrumb-item"><?= labels('web_settings', "Web settings") ?></div>
            </div>
        </div>

        <?= form_open_multipart(base_url('admin/settings/web_setting_update')) ?>



        <div class="row mb-4">
            <!-- App download Section -->
            <div class="col-md-6 col-sm-12 col-xl-6">
                <div class="card h-100">
                    <div class="row pl-3">
                        <div class="col " style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('app_download_section', "App download Section") ?></div>
                        </div>
                        <div class="col d-flex justify-content-end mr-3 mt-4" style="border-bottom: solid 1px #e5e6e9;">

                            <?php
                            $app_section_status = isset($app_section_status) ? $app_section_status : 0;

                            $label = ($app_section_status == 1) ?
                                '<input type="checkbox" id="app_section_status" name="app_section_status" class="status-switch" checked>' :
                                '<input type="checkbox" id="app_section_status" name="app_section_status" class="status-switch">';

                            echo $label;
                            ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for='web_title'><?= labels('title', "Title") ?></label>
                                    <input type='text' class="form-control custome_reset" name='web_title' id='web_title' value="<?= isset($web_title) ? $web_title : '' ?>" />
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for='web_tagline'><?= labels('tagline', "Tagline") ?></label>
                                    <input type='text' class="form-control custome_reset" name='web_tagline' id='web_tagline' value="<?= isset($web_tagline) ? $web_tagline : '' ?>" />
                                </div>
                            </div>


                            <div class="col-6">
                                <div class="form-group">
                                    <label for='playstore_url'><?= labels('playstore_url', "Playstore URL ") ?></label>
                                    <input type='text' class="form-control custome_reset" name='playstore_url' id='playstore_url' value="<?= isset($playstore_url) ? $playstore_url : '' ?>" />
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for='applestore_url'><?= labels('applestore_url', "Applestore URL") ?></label>
                                    <input type='text' class="form-control custome_reset" name='applestore_url' id='applestore_url' value="<?= isset($applestore_url) ? $applestore_url : '' ?>" />
                                </div>
                            </div>


                            <div class="col-md-12">

                                <label for="short_description"><?= labels('short_description', "Short Description") ?></label>
                                <textarea rows=10 class='form-control h-50 ' name="short_description"><?= isset($short_description) ? $short_description : '' ?></textarea>

                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- social media links -->
            <div class="col-md-6 col-sm-12 col-xl-6">
                <div class="card h-100">
                    <div class="row pl-3">
                        <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('social_media_links', "Social Media Links") ?></div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="list_wrapper">
                            <div class="row">
                                <div class="col-xs-4 col-sm-4 col-md-5">
                                    <div class="form-group">
                                        <label for="title"><?= labels('url', "URL") ?></label>
                                        <input name="social_media[0][url]" type="text" placeholder="Enter the url here" class="form-control social_media_url_change" />
                                        <input type="hidden" name="social_media[0][exist_url]" value="new">
                                    </div>
                                </div>
                                <div class="col-xs-7 col-sm-7 col-md-5">
                                    <div class="form-group">
                                        <label for="image"><?= labels('image', "Image") ?></label>
                                        <input name="social_media[0][file]" type="file" class="filepond logo "data-your-attribute="social_media_file" accept="image/*">
                                        <input type="hidden" name="social_media[0][exist_file]" value="new">
                                        <img class="settings_logo" src="">

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


        <!-- logos Section -->
        <div class="row mb-3">

            <div class="col-md-12 col-sm-12 col-xl-12">
                <div class="card h-100">
                    <div class="row pl-3">
                        <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                            <div class="toggleButttonPostition"><?= labels('logos', "Logos") ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 ">
                                <div class="form-group">
                                    <label for='logo'><?= labels('logo', "Logo") ?></label>
                                    <input type="file" name="web_logo" class="filepond logo" id="web_logo" accept="image/*">
                                    <img class="settings_logo" src="<?= isset($web_logo) && $web_logo != "" ? base_url("public/uploads/web_settings/" . $web_logo) : base_url('public/backend/assets/img/news/img01.jpg') ?>">

                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <div class="form-group">

                                    <label for='favicon'><?= labels('favicon', "Favicon") ?></label>
                                    <input type="file" name="web_favicon" class="filepond logo" id="web_favicon" accept="image/*">
                                    <img class="settings_logo" src="<?= isset($web_favicon) && $web_favicon != "" ? base_url("public/uploads/web_settings/" . $web_favicon) : base_url('public/backend/assets/img/news/img02.jpg') ?>">

                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <div class="form-group">
                                    <label for='halfLogo'><?= labels('half_logo', "Half Logo") ?></label>
                                    <input type="file" name="web_half_logo" class="filepond logo" id="web_half_logo" accept="image/*">
                                    <img class="settings_logo" src="<?= isset($web_half_logo) && $web_half_logo != "" ? base_url("public/uploads/web_settings/" . $web_half_logo) : base_url('public/backend/assets/img/news/img03.jpg') ?>">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>




        <div class="row mb-3">

            <div class="col-md d-flex justify-content-end">
                <input type='submit' name='update' id='update' value='<?= labels('save_changes', "Save") ?>' class='btn btn-lg bg-new-primary' />
                <?= form_close() ?>

            </div>

        </div>


    </section>

</div>



<script>
    $(document).ready(function() {
        var x = 0; // Initial field counter
        var list_maxField = 10; // Input fields increment limitation

        // Once add button is clicked
        $('.list_add_button').click(function() {
            // Check maximum number of input fields
            if (x < list_maxField) {
                x++; // Increment field counter

                var list_fieldHTML = `
                <div class="row">
                    <div class="col-xs-4 col-sm-4 col-md-5">
                        <div class="form-group">
                            <label for="title"><?= labels('url', "URL") ?></label>
                            <input name="social_media[${x}][url]" type="text" placeholder="Enter the title here" class="form-control social_media_url_change"/>
                            <input type="hidden" name="social_media[${x}][exist_url]" value="social_media[${x}][url]">
                        </div>
                    </div>
                    <div class="col-xs-7 col-sm-7 col-md-5">
                        <div class="form-group">
                            <label for="image"><?= labels('image', "Image") ?></label>
                            <input name="social_media[${x}][file]" type="file" class="filepond logo new-row " accept="image/*" data-your-attribute="social_media_file" required>
                            <input type="hidden" name="social_media[${x}][exist_file]" value="social_media[${x}][file]">

                        </div>
                    </div>
                    <div class="col-xs-1 col-sm-1 col-md-2 mt-4">
                        <button class="list_remove_button btn btn-danger" type="button">-</button>
                    </div>
                </div>`;

                // Append field HTML
                $('.list_wrapper').append(list_fieldHTML);

                // Initialize FilePond only for the new row
                $('.list_wrapper .new-row').last().filepond({
                    credits: null,
                    allowFileSizeValidation: true,
                    maxFileSize: "25MB",
                    labelMaxFileSizeExceeded: "File is too large",
                    labelMaxFileSize: "Maximum file size is {filesize}",
                    allowFileTypeValidation: true,
                    acceptedFileTypes: ["image/*"],
                    labelFileTypeNotAllowed: "File of invalid type",
                    fileValidateTypeLabelExpectedTypes: "Expects {allButLastType} or {lastType}",
                    storeAsFile: true,
                    allowPdfPreview: true,
                    pdfPreviewHeight: 320,
                    pdfComponentExtraParams: "toolbar=0&navpanes=0&scrollbar=0&view=fitH",
                    allowVideoPreview: true,
                    allowAudioPreview: true,
                });
            }
        });

        // Once remove button is clicked
        $('.list_wrapper').on('click', '.list_remove_button', function() {
            $(this).closest('div.row').remove(); // Remove field HTML
            x--; // Decrement field counter
        });
    });
</script>
<script>
    var baseUrl = "<?= base_url() ?>"; // Define the base URL
    $(document).ready(function() {

        var app_section_status = document.querySelector('#app_section_status');
        app_section_status.addEventListener('change', function() {
            handleSwitchChange(app_section_status);
        });

        //for status
        <?php
        $app_section_status = isset($app_section_status) ? $app_section_status : 0;
        if ($app_section_status == 1) { ?>
            $('#app_section_status').siblings('.switchery').addClass('active-content').removeClass('deactive-content');

        <?php   } else { ?>
            $('#app_section_status').siblings('.switchery').addClass('deactive-content').removeClass('active-content');

        <?php  }
        ?>

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
    });
</script>


<script>
    $(document).ready(function() {
        <?php
        $social_media = isset($social_media) && is_array($social_media) ? $social_media : [];

        $social_media = array_values($social_media); // Ensure the array keys are sequential

        ?>

        var x = <?= count($social_media) ?>; // Initial field counter

        // Function to add a new row with input fields
        function addRow(url, file, exist_url) {
            var newRow = `
                <div class="row">
                    <div class="col-xs-4 col-sm-4 col-md-5">
                        <div class="form-group">
                            <label for="title"><?= labels('url', "URL") ?></label>
                            <input name="social_media[${x}][url]" type="text" value="${url}" class="form-control social_media_url_change" />
                            <input type="hidden" name="social_media[${x}][exist_url]" value="${exist_url}">
                            
                            </div>
                            </div>
                            <div class="col-xs-7 col-sm-7 col-md-5">
                            <div class="form-group">
                            <label for="image"><?= labels('image', "Image") ?></label>
                            <input name="social_media[${x}][file]" type="file" class="filepond logo " accept="image/*" >
                            <img class="settings_logo" src="${baseUrl}/public/uploads/web_settings/${file}" >
                            <input type="hidden" name="social_media[${x}][exist_file]" value="${file}">

                        </div>
                        
                    </div>
                    <div class="col-xs-1 col-sm-1 col-md-2 mt-4">
                        <button class="btn btn-danger list_remove_button" type="button">-</button>
                    </div>
                </div>
            `;

            $(".list_wrapper").append(newRow);
            x++; // Increment the field counter
        }

        // Loop through the social_media array and add rows for each entry
        <?php foreach ($social_media as $entry) : ?>

            addRow("<?= $entry['url'] ?>", "<?= $entry['file'] ?>", "<?= $entry['url'] ?>");
        <?php endforeach; ?>

        $(".social_media_url_change").change(function() {
            if($(this).val()==""){
                $(this).parent().parent().next().find('.filepond--browser').prop('required',false);
            }else{
                $(this).parent().parent().next().find('.filepond--browser').prop('required',true);

            }
        });
    });
</script>
