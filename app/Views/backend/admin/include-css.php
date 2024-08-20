<link rel="stylesheet" href="<?= base_url('public/backend/assets/css/admin_css.css') ?>" />
<link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/bootstrap-table.css') ?>" />
<link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/bootstrap.min.css') ?>" />
<link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/summernote.min.css') ?>" />
<link rel="stylesheet" href="<?= base_url('public/fontawesome/css/all.css') ?>" />
<!-- Template CSS -->

<link rel="stylesheet" href="<?= base_url('public/backend/assets/css/style.css') ?>" />
<link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/iziToast.min.css') ?>" />
<link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/daterangepicker.css') ?>" />


<link rel="stylesheet" href="<?= base_url('public/backend/assets/css/switchery.min.css') ?>" />

<link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">

<?php $data = get_settings('general_settings', true); ?>
<!-- Site Identity -->
<link href="<?= isset($data['favicon']) && $data['favicon'] != "" ? base_url("public/uploads/site/" . $data['favicon']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" rel="icon" />
<link href="<?= base_url("public/frontend/retro/img/site/apple-touch-icon.png") ?>" rel="apple-touch-icon" />
<link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/select2.min.css') ?>" />

<script src="<?= base_url('public/backend/assets/js/vendor/jquery.min.js') ?>"></script>
<link rel="stylesheet" href="<?= base_url('public/backend/assets/css/components.css') ?>" />
<link rel="stylesheet" href="<?= base_url('public/backend/assets/css/vendor/dropzone.css') ?>" />


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tagify/4.15.2/tagify.css" />

 <!-- Include the JSTree CSS -->
 <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" rel="stylesheet" /> -->


<!-- filepond Css -->
<link href="<?= base_url('public/backend/assets/js/filepond/dist/filepond.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-image-preview.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-pdf-preview.min.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-media-preview.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-media-preview.min.css') ?>" rel="stylesheet" type="text/css" />
<!-- filepond Css -->





<!-- Link Swiper's CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/css/intlTelInput.min.css" rel="stylesheet"/>















<link href="https://unpkg.com/bootstrap-table@1.21.4/dist/extensions/reorder-rows/bootstrap-table-reorder-rows.css" rel="stylesheet">




<!-- for star -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />





<script>
    var baseUrl = '<?= base_url() ?>';
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
</script>


<script>
    <?php $firebase_setting = get_settings('firebase_settings', true); ?>
    let apiKey = "<?= isset($firebase_setting['apiKey']) ? $firebase_setting['apiKey'] : '1' ?>"
    let authDomain = "<?= isset($firebase_setting['authDomain']) ? ($firebase_setting['authDomain']) : 0 ?>"
    let projectId = "<?= isset($firebase_setting['projectId']) ? $firebase_setting['projectId'] : 0 ?>"
    let storageBucke = "<?= isset($firebase_setting['storageBucket']) ? $firebase_setting['storageBucket'] : 0 ?>"
    let messagingSenderId = "<?= isset($firebase_setting['messagingSenderId']) ? $firebase_setting['messagingSenderId'] : 0 ?>"
    let appId = "<?= isset($firebase_setting['appId']) ? $firebase_setting['appId'] : 0 ?>"
    let measurementId = "<?= isset($firebase_setting['measurementId']) ? $firebase_setting['measurementId'] : 0 ?>"

</script>