    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/bootstrap.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/bootstrap-table.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/iziToast.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/daterangepicker.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/select2.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/jquery.treeview.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/dropzone.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css">
    <link rel="stylesheet" href="<?= base_url('public/fontawesome/css/all.css') ?>" />
    <?php $data = get_settings('general_settings', true); ?>
    <!-- Template CSS -->
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/style.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/googleMap.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/components.css') ?>" />


    <!-- Site Identity -->
    <link href="<?= isset($data['partner_favicon']) && $data['partner_favicon'] != "" ? base_url("public/uploads/site/" . $data['partner_favicon']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" rel="icon" />
    <link href="<?= base_url("public/frontend/retro/img/site/apple-touch-icon.png") ?>" rel="apple-touch-icon" />

    <script src="<?= base_url('public/backend/assets/js/vendor/jquery.min.js') ?>"></script>

    <script>
        var baseUrl = '<?= base_url() ?>';
        var csrfName = '<?= csrf_token() ?>';
        var csrfHash = '<?= csrf_hash() ?>';
    </script>
    <link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/cropper.css') ?>" />

    <link rel="stylesheet" href="https://unpkg.com/@yaireo/tagify/dist/tagify.css" />
    <link rel="stylesheet" href="<?= base_url("public/backend/assets/css/custom.css") ?>">
    

    <style>
        .tagify {
            width: 100%;
            max-width: 700px;
        }
    </style>

<link href = "https://fonts.googleapis.com/icon?family=Material+Icons" rel = "stylesheet">                              



<link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">

<!-- filepond Css -->
<link href="<?= base_url('public/backend/assets/js/filepond/dist/filepond.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-image-preview.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-pdf-preview.min.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-media-preview.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-media-preview.min.css') ?>" rel="stylesheet" type="text/css" />
<!-- filepond Css -->


<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
<!-- switchery css -->
<link href="http://abpetkov.github.io/switchery/dist/switchery.min.css" rel="stylesheet" />
<!-- switchery css -->




