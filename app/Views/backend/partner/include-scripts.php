		<script src="<?= base_url('public/backend/assets/js/vendor/popper.min.js') ?>"></script>


		<script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>
		<script src="<?= base_url('public/backend/assets/js/vendor/bootstrap.min.js') ?>">
		</script>
		<script src="<?= base_url('public/backend/assets/js/vendor/jquery.nicescroll.min.js') ?>"></script>
		<script src="<?= base_url('public/backend/assets/js/vendor/moment.min.js') ?>"></script>
		<script src="<?= base_url('public/backend/assets/js/stisla.js') ?>"></script>
		<script src="<?= base_url('public/backend/assets/js/vendor/iziToast.min.js') ?>"></script>
		<script src="<?= base_url('public/backend/assets/js/vendor/bootstrap-table.min.js') ?>"></script>
		<script src="<?= base_url('public/backend/assets/js/vendor/select2.min.js') ?>"></script>
		<script src="<?= base_url('public/backend/assets/js/vendor/sweetalert.js') ?>"></script>
		<script src="<?= base_url('public/backend/assets/js/vendor/iconify.min.js') ?>"></script>
		<script src="<?= base_url('public/backend/assets/js/vendor/cropper.js') ?>"></script>
		
		<script src="<?= base_url('public/backend/assets/js/vendor/dropzone.js') ?>"></script>
		<script type="text/javascript" src="<?= base_url('public/backend/assets/js/vendor/tinymce/tinymce.min.js') ?>"></script>




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







		<!-- for swithchery js start -->

		<script src="http://abpetkov.github.io/switchery/dist/switchery.min.js"></script>
		<!-- for swithchery js -->


		<script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>


		<script src="https://js.stripe.com/v3/"></script>
		<script src="https://cdn.jsdelivr.net/npm/chart.js@3.8.0/dist/chart.min.js"></script>
		<script src="<?= base_url('public/backend/assets/js/vendor/daterangepicker.js') ?>"></script>
		<script src="<?= base_url('public/backend/assets/js/googleMap.js') ?>"></script>


		<?php
		echo '<script src="' . base_url('public/backend/assets/js/vendor/chart.min.js') . '"></script>';

		?>


		<?php
		switch ($main_page) {
			case "../../text_to_speech":
				echo '<script src="' . base_url('public/backend/assets/js/page/tts.js') . '"></script>';
				break;

			case "checkout":
				echo '<script src="https://checkout.razorpay.com/v1/checkout-frame.js"></script>';
				echo '<script src="' . base_url('public/backend/assets/js/vendor/paystack-v1.js') . '"></script>';
				echo '<script src="' . base_url('public/backend/assets/js/page/checkout.js') . '"></script>';
				echo `<script src="https://js.stripe.com/v3/"></script>`;
				echo `<script src="https://js.paystack.co/v1/inline.js"></script>`;
				break;

			case "plans":
				echo '<script src="' . base_url('public/backend/assets/js/page/admin_plans.js') . '"></script>';
				break;
		}
		?>
		<?php
		$api_key = get_settings('api_key_settings', true);
		?>

		<script>
			let are_your_sure = "<?php echo  labels('are_your_sure', 'Are you sure?') ?>";
			let yes_proceed = "<?php echo  labels('yes_proceed', 'Yes, Proceed!') ?>";
			let you_wont_be_able_to_revert_this = "<?php echo  labels('you_wont_be_able_to_revert_this', 'You won\'t be able to revert this!') ?>";
			let are_you_sure_you_want_to_deactivate_this_user = "<?php echo labels('are_you_sure_you_want_to_deactivate_this_user', 'Are you sure you want to deactivate this user') ?>"
			let are_you_sure_you_want_to_delete_this_user = "<?php echo  labels('are_you_sure_you_want_to_delete_this_user', 'Are you sure you want to delete this user') ?>"
			let are_you_sure_you_want_to_activate_this_user = "<?php echo  labels('are_you_sure_you_want_to_activate_this_user', 'Are you sure you want to activate this user') ?>"

			let cancel = "<?php echo  labels('cancel', 'Cancel') ?>";
		</script>
		<script defer src="https://maps.googleapis.com/maps/api/js?key=<?= $api_key['google_map_api'] ?>&libraries=places&callback=initautocomplete">
		</script>
		<script src="<?= base_url('public/backend/assets/js/partner_events.js') ?>"></script>
		<script src="<?= base_url('public/backend/assets/js/scripts.js') ?>"></script>
		<script src="<?= base_url('public/backend/assets/js/partner.js') ?>"></script>
		<script src="https://unpkg.com/@yaireo/tagify"></script>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
		<script type="text/javascript" src="<?= base_url('public/backend/assets/js/tableExport.min.js') ?>"></script>
		<script>
			// The DOM element you wish to replace with Tagify
			if (document.getElementById("service_tags") != null) {
				$(document).ready(function() {
					var input = document.querySelector('input[id=service_tags]');
					new Tagify(input)
				});
			}
			if (document.getElementById("service_tags_update") != null) {
				$(document).ready(function() {
					var input = document.querySelector('input[id=service_tags_update]');
					new Tagify(input)
				});
			}

			// initialize Tagify on the above input node reference
		</script>
		<script>

		</script>
		<!-- <script src="http://localhost/edemand/public/backend/assets/js/window_event.js"></script> -->
