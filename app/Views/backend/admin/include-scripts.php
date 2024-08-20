
<script src="<?= base_url('public/backend/assets/js/vendor/bootstrap-table.min.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/vendor/popper.min.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/vendor/summernote.min.js') ?>"></script>


<script src="<?= base_url('public/backend/assets/js/vendor/bootstrap.min.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/vendor/jquery.nicescroll.min.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/vendor/moment.min.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/stisla.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/vendor/iziToast.min.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/vendor/select2.min.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/vendor/cropper.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/vendor/bootstrap-colorpicker.min.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/vendor/daterangepicker.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/vendor/dropzone.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/vendor/sweetalert.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/vendor/lottie.js') ?>"></script>

<script type="text/javascript" src="<?= base_url('public/backend/assets/js/vendor/tinymce/tinymce.min.js') ?>"></script>
<?= '<script src="' . base_url('public/backend/assets/js/page/admin.js') . '"></script>' ?>

<?php
switch ($main_page) {
    case "dashboard":
        echo '<script  src="' . base_url('public/backend/assets/js/vendor/chart.min.js') . '"></script>';
        echo '<script  src="' . base_url('public/backend/assets/js/vendor/iconify.min.js') . '"></script>';
        break;

    case "subscription":
        echo '<script src="' . base_url('public/backend/assets/js/page/subscription.js') . '"></script>';
        break;

    case "plans":


        break;

    case "../../text_to_speech":
        echo '<script src="' . base_url('public/backend/assets/js/page/tts.js') . '"></script>';
        break;
}
$api_key = get_settings('api_key_settings', true);
$firebase_setting = get_settings('firebase_settings', true);
?>
<script type="text/javascript" defer src="https://maps.googleapis.com/maps/api/js?key=<?= $api_key['google_map_api'] ?>&libraries=places&callback=initautocomplete">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tagify/4.15.2/tagify.min.js"></script>

<script>
    let are_your_sure = "<?php echo  labels('are_your_sure', 'Are you sure?') ?>";
    let yes_proceed = "<?php echo  labels('yes_proceed', 'Yes, Proceed!') ?>";
    let you_wont_be_able_to_revert_this = "<?php echo  labels('you_wont_be_able_to_revert_this', 'You won\'t be able to revert this!') ?>";
    let are_you_sure_you_want_to_deactivate_this_user = "<?php echo labels('are_you_sure_you_want_to_deactivate_this_user', 'Are you sure you want to deactivate this user') ?>"
    let are_you_sure_you_want_to_delete_this_user = "<?php echo  labels('are_you_sure_you_want_to_delete_this_user', 'Are you sure you want to delete this user') ?>"
    let are_you_sure_you_want_to_activate_this_user = "<?php echo  labels('are_you_sure_you_want_to_activate_this_user', 'Are you sure you want to activate this user') ?>"

    let cancel = "<?php echo  labels('cancel', 'Cancel') ?>";
</script>



<!-- start :: include FilePond library -->
<script src="https://unpkg.com/filepond/dist/filepond.js"></script>
<script src="<?= base_url('public/backend/assets/js/filepond/dist/filepond.min.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-image-preview.min.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-pdf-preview.min.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-file-validate-size.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-file-validate-type.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-image-validate-size.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/filepond/dist/filepond.jquery.js') ?>"></script>

<!-- for media preview -->
<script src="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-media-preview.esm.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-media-preview.esm.min.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-media-preview.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/filepond/dist/filepond-plugin-media-preview.min.js') ?>"></script>
<!-- for end  media preview -->
<!-- end :: include FilePond library -->






<script src="<?= base_url('public/backend/assets/js/scripts.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/select2_register.js') ?>"></script>
<script src="<?= base_url('public/backend/assets/js/switch_component.js') ?>"></script>


<!-- for swithchery js start -->

<script src="<?= base_url('public/backend/assets/js/switchery.min.js') ?>"></script>

<!-- table reorder rows start -->
<script src="https://unpkg.com/bootstrap-table@1.21.4/dist/extensions/reorder-rows/bootstrap-table-reorder-rows.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tablednd@1.0.5/dist/jquery.tablednd.min.js"></script>
<!-- table reorder rows end -->



<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css">


<!-- Swiper JS -->


<script src="https://cdn.jsdelivr.net/npm/chart.js@3.8.0/dist/chart.min.js"></script>


<?php
echo '<script src="' . base_url('public/backend/assets/js/vendor/chart.min.js') . '"></script>';

?>

<!-- Include jQuery (required by JSTree)
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<Include the JSTree JavaScript -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script> --> 
</head>
<script src="https://www.gstatic.com/firebasejs/8.2.0/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.2.0/firebase-messaging.js"></script>
<script src="<?= base_url('public/backend/assets/js/window_event.js') ?>"></script>

<script type="text/javascript" src="<?= base_url('public/backend/assets/js/custom.js') ?>"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script type="text/javascript" src="<?= base_url('public/backend/assets/js/tableExport.min.js') ?>"></script>



<script>
    var firebaseConfig = {

        apiKey: '<?= isset($firebase_setting['apiKey']) ? $firebase_setting['apiKey'] : '1' ?>',
        authDomain: '<?= isset($firebase_setting['authDomain']) ? ($firebase_setting['authDomain']) : 0 ?>',
        projectId: '<?= isset($firebase_setting['projectId']) ? $firebase_setting['projectId'] : 0 ?>',
        storageBucket: '<?= isset($firebase_setting['storageBucket']) ? $firebase_setting['storageBucket'] : 0 ?>',
        messagingSenderId: '<?= isset($firebase_setting['messagingSenderId']) ? $firebase_setting['messagingSenderId'] : 0 ?>',
        appId: '<?= isset($firebase_setting['appId']) ? $firebase_setting['appId'] : 0 ?>',
        measurementId: '<?= isset($firebase_setting['measurementId']) ? $firebase_setting['measurementId'] : 0 ?>'

    };

    // console.log(firebaseConfig);
    firebase.initializeApp(firebaseConfig);
    const fcm = firebase.messaging();
    fcm.getToken({
        vapidKey: "<?= isset($firebase_setting['vapidKey']) ? $firebase_setting['vapidKey'] : 0 ?>"
    }).then((token) => {
        // console.log('getToken- ' + token);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: baseUrl + '/save-web-token',
            type: 'POST',
            data: {
                token: token
            },
            dataType: 'JSON',
            success: function(response) {
                //alert('Token saved successfully.');.
                console.log('Device Token saved successfully');
            },
            error: function(err) {
                console.log('User Chat Token Error' + err);
            },
        });
    });

    fcm.onMessage((data) => {
        console.log('onMessageData - ', data);
        Notification.requestPermission((status) => {
           
            if (status === "granted") {

                let title = data['notification']['title'];
                let body = data['notification']['body'];

                new Notification(title, {
                    body: body,
                    icon: data['notification']['icon'],
                    click_action: data['notification']['click_action'],


                })
            }
        })
    })
</script>
<script>
    var firebaseConfig = {
        apiKey: '<?= isset($firebase_setting['apiKey']) ? $firebase_setting['apiKey'] : '1' ?>',
        authDomain: '<?= isset($firebase_setting['authDomain']) ? $firebase_setting['authDomain'] : 0 ?>',
        projectId: '<?= isset($firebase_setting['projectId']) ? $firebase_setting['projectId'] : 0 ?>',
        storageBucket: '<?= isset($firebase_setting['storageBucket']) ? $firebase_setting['storageBucket'] : 0 ?>',
        messagingSenderId: '<?= isset($firebase_setting['messagingSenderId']) ? $firebase_setting['messagingSenderId'] : 0 ?>',
        appId: '<?= isset($firebase_setting['appId']) ? $firebase_setting['appId'] : 0 ?>',
        measurementId: '<?= isset($firebase_setting['measurementId']) ? $firebase_setting['measurementId'] : 0 ?>'
    };

    render();

    function render() {

        window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('rec');
        recaptchaVerifier.render()
    }
</script>