<?php
$data = get_settings('general_settings', true);
isset($data['company_title']) && $data['company_title'] != "" ?  $company = $data['company_title'] : $company =  'company';
?>
<footer class="main-footer new-footer m-0 p-0 mt-4">
    <div class="mt-4">
    <?php $data = get_settings('general_settings', true); ?>
    <?= (isset($data['copyright_details']) && $data['copyright_details'] != "") ? $data['copyright_details']  : "edemand copyright" ?>
    </div>
    </div>
</footer>