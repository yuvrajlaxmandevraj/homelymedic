<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('payment_gateway', "Payment Gateways") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item "><a href="<?= base_url('/admin/settings/system-settings') ?>"><?= labels('system_settings', "System Settings") ?></a></div>

                <div class="breadcrumb-item"></i> <?= labels('payment_gateways', 'Payment Gateways Settings') ?></div>
            </div>
        </div>
        <form method="POST" action="<?= base_url('admin/settings/pg-settings') ?>">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">


            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
                    <div class=" card  px-3">

                        <div class="row">
                            <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                                <div class='toggleButttonPostition'><?= labels('paypal', 'Paypal') ?></div>

                            </div>

                        </div>









                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for='paypal_status'><?= labels('status', 'Status') ?></label>
                                    <select class='form-control selectric' name='paypal_status' id='paypal_status'>
                                        <option value='disable' <?= isset($paypal_status) && $paypal_status === 'disable' ? 'selected' : '' ?>>Disable</option>
                                        <option value='enable' <?= isset($paypal_status) && $paypal_status === 'enable' ? 'selected' : '' ?>>Enable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-12">
                                <label for="">Payment Mode <small>[ sandbox / live ]</small>
                                </label>
                                <select name="paypal_mode" class="form-control" required>
                                    <option value="">Select Mode</option>
                                    <option value="sandbox" <?= (isset($paypal_mode) && $paypal_mode == 'sandbox') ? 'selected' : '' ?>>Sandbox ( Testing )</option>
                                    <option value="production" <?= (isset($paypal_mode) && $paypal_mode == 'production') ? 'selected' : '' ?>>Production ( Live )</option>
                                </select>
                            </div>
                            <div class="form-group col-12">
                                <label for="paypal_business_email">Paypal Business Email</label>
                                <input type="text" class="form-control" name="paypal_business_email" value="<?= (isset($paypal_business_email)) ? $paypal_business_email : '' ?>" placeholder="Paypal Business Email" />
                            </div>

                            <div class="form-group col-12">
                                <label for="currency_code">Currency code</label>
                                <select class="form-control" name="paypal_currency_code" ?>">
                                    <option value="AUD" <?= (isset($paypal_currency_code) && $paypal_currency_code == "AUD") ? "selected" : '' ?>>AUD</option>
                                    <option value="BRL" <?= (isset($paypal_currency_code) && $paypal_currency_code == "BRL") ? "selected" : '' ?>>BRL</option>
                                    <option value="CAD" <?= (isset($paypal_currency_code) && $paypal_currency_code == "CAD") ? "selected" : '' ?>>CAD</option>
                                    <option value="CNY" <?= (isset($paypal_currency_code) && $paypal_currency_code == "CNY") ? "selected" : '' ?>>CNY</option>
                                    <option value="CZK" <?= (isset($paypal_currency_code) && $paypal_currency_code == "CZK") ? "selected" : '' ?>>CZK</option>
                                    <option value="DKK" <?= (isset($paypal_currency_code) && $paypal_currency_code == "DKK") ? "selected" : '' ?>>DKK</option>
                                    <option value="EUR" <?= (isset($paypal_currency_code) && $paypal_currency_code == "EUR") ? "selected" : '' ?>>EUR</option>
                                    <option value="HKD" <?= (isset($paypal_currency_code) && $paypal_currency_code == "HKD") ? "selected" : '' ?>>HKD</option>
                                    <option value="HUF" <?= (isset($paypal_currency_code) && $paypal_currency_code == "HUF") ? "selected" : '' ?>>HUF</option>
                                    <option value="INR" <?= (isset($paypal_currency_code) && $paypal_currency_code == "INR") ? "selected" : '' ?>>INR</option>
                                    <option value="ILS" <?= (isset($paypal_currency_code) && $paypal_currency_code == "ILS") ? "selected" : '' ?>>ILS</option>
                                    <option value="JPY" <?= (isset($paypal_currency_code) && $paypal_currency_code == "JPY") ? "selected" : '' ?>>JPY</option>
                                    <option value="MYR" <?= (isset($paypal_currency_code) && $paypal_currency_code == "MYR") ? "selected" : '' ?>>MYR</option>
                                    <option value="MXN" <?= (isset($paypal_currency_code) && $paypal_currency_code == "MXN") ? "selected" : '' ?>>MXN</option>
                                    <option value="TWD" <?= (isset($paypal_currency_code) && $paypal_currency_code == "TWD") ? "selected" : '' ?>>TWD</option>
                                    <option value="NZD" <?= (isset($paypal_currency_code) && $paypal_currency_code == "NZD") ? "selected" : '' ?>>NZD</option>
                                    <option value="NOK" <?= (isset($paypal_currency_code) && $paypal_currency_code == "NOK") ? "selected" : '' ?>>NOK</option>
                                    <option value="PHP" <?= (isset($paypal_currency_code) && $paypal_currency_code == "PHP") ? "selected" : '' ?>>PHP</option>
                                    <option value="PLN" <?= (isset($paypal_currency_code) && $paypal_currency_code == "PLN") ? "selected" : '' ?>>PLN</option>
                                    <option value="GBP" <?= (isset($paypal_currency_code) && $paypal_currency_code == "GBP") ? "selected" : '' ?>>GBP</option>
                                    <option value="RUB" <?= (isset($paypal_currency_code) && $paypal_currency_code == "RUB") ? "selected" : '' ?>>RUB</option>
                                    <option value="SGD" <?= (isset($paypal_currency_code) && $paypal_currency_code == "SGD") ? "selected" : '' ?>>SGD</option>
                                    <option value="SEK" <?= (isset($paypal_currency_code) && $paypal_currency_code == "SEK") ? "selected" : '' ?>>SEK</option>
                                    <option value="CHF" <?= (isset($paypal_currency_code) && $paypal_currency_code == "CHF") ? "selected" : '' ?>>CHF</option>
                                    <option value="THB" <?= (isset($paypal_currency_code) && $paypal_currency_code == "THB") ? "selected" : '' ?>>THB</option>
                                    <option value="USD" <?= (isset($paypal_currency_code) && $paypal_currency_code == "USD") ? "selected" : '' ?>>USD</option>
                                </select>
                            </div>

                            <div class="form-group col-12 ">
                                <label>Notification URL <small>(Set this as IPN notification URL in you PayPal account)</small></label>
                                <input type="text" class="form-control" readonly value="<?= base_url('api/webhooks/paypal') ?>" />
                            </div>
                            <div class="form-group col-12 ">
                                <label>Client Key</label>
                                <input type="text" class="form-control" name="paypal_client_key" value="<?= (isset($paypal_client_key)) ? $paypal_client_key : '' ?>" />
                            </div>
                            <div class="form-group col-12 ">
                                <label>Secret Key</label>
                                <input type="text" class="form-control" name="paypal_secret_key" value="<?= (isset($paypal_secret_key)) ? $paypal_secret_key : '' ?>" />
                            </div>
                        </div>
                    </div>

                </div>



                <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12 d-flex">

                    <div class="card px-3">




                        <div class="row">
                            <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                                <div class='toggleButttonPostition'><?= labels('razorPay', 'RazorPay') ?></div>

                            </div>

                            <!-- <div class="col d-flex justify-content-end toggleButttonPostition   ">

                                    <input type="checkbox" id="switch3" switch="bool" />
                                    <label for="switch3" data-on-label="Active" data-off-label="Inactive"></label>
                  
                            </div> -->
                        </div>


                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for='razorpay_status'><?= labels('status', 'Status') ?></label>
                                    <select class='form-control selectric' name='razorpayApiStatus' id='razorpay_status'>
                                        <option value='enable' <?= isset($razorpayApiStatus) && $razorpayApiStatus === 'enable' ? 'selected' : '' ?>>Enable</option>
                                        <option value='disable' <?= isset($razorpayApiStatus) && $razorpayApiStatus === 'disable' ? 'selected' : '' ?>>Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="razorpayMode"><?= labels('mode', 'Mode') ?></label>
                                    <select class='form-control selectric' name='razorpay_mode' id='razorpay_mode'>
                                        <option value='test' <?= isset($razorpay_mode) && $razorpay_mode === 'test' ? 'selected' : '' ?>>Test</option>
                                        <option value='live' <?= isset($razorpay_mode) && $razorpay_mode === 'live' ? 'selected' : '' ?>>Live</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="razorpayMode"><?= labels('currency_code', 'Currency Code') ?></label>
                                    <!-- <input type="text" value="<?= isset($razorpay_currency) ? $razorpay_currency : '' ?>" name='razorpay_currency' id='razorpay_currency' placeholder='Enter Razorpay currency' class="form-control" /> -->
                                    <select class="form-control" name="razorpay_currency" id="">
                                        <option value="AED">United Arab Emirates Dirham</option>
                                        <option value="ALL" <?= (isset($razorpay_currency) && $razorpay_currency == "ALL") ? "selected" : '' ?>>Albanian lek</option>
                                        <option value="AMD" <?= (isset($razorpay_currency) && $razorpay_currency == "AMD") ? "selected" : '' ?>>Armenian dram</option>
                                        <option value="ARS" <?= (isset($razorpay_currency) && $razorpay_currency == "ARS") ? "selected" : '' ?>>Argentine peso</option>
                                        <option value="AUD" <?= (isset($razorpay_currency) && $razorpay_currency == "AUD") ? "selected" : '' ?>>Australian dollar</option>
                                        <option value="AWG" <?= (isset($razorpay_currency) && $razorpay_currency == "AWG") ? "selected" : '' ?>>Aruban florin</option>
                                        <option value="BBD" <?= (isset($razorpay_currency) && $razorpay_currency == "BBD") ? "selected" : '' ?>>Barbadian dollar</option>
                                        <option value="BDT" <?= (isset($razorpay_currency) && $razorpay_currency == "BDT") ? "selected" : '' ?>>Bangladeshi taka</option>
                                        <option value="BMD" <?= (isset($razorpay_currency) && $razorpay_currency == "BMD") ? "selected" : '' ?>>Bermudian dollar</option>
                                        <option value="BND" <?= (isset($razorpay_currency) && $razorpay_currency == "BND") ? "selected" : '' ?>>Brunei dollar</option>
                                        <option value="BOB" <?= (isset($razorpay_currency) && $razorpay_currency == "BOB") ? "selected" : '' ?>>Bolivian boliviano</option>
                                        <option value="BSD" <?= (isset($razorpay_currency) && $razorpay_currency == "BSD") ? "selected" : '' ?>>Bahamian dollar</option>
                                        <option value="BWP" <?= (isset($razorpay_currency) && $razorpay_currency == "BWP") ? "selected" : '' ?>>Botswana pula</option>
                                        <option value="BZD" <?= (isset($razorpay_currency) && $razorpay_currency == "BZD") ? "selected" : '' ?>>Belize dollar</option>
                                        <option value="CAD" <?= (isset($razorpay_currency) && $razorpay_currency == "CAD") ? "selected" : '' ?>>Canadian dollar</option>
                                        <option value="CHF" <?= (isset($razorpay_currency) && $razorpay_currency == "CHF") ? "selected" : '' ?>>Swiss franc</option>
                                        <option value="CNY" <?= (isset($razorpay_currency) && $razorpay_currency == "CNY") ? "selected" : '' ?>>Chinese yuan renminbi</option>
                                        <option value="COP" <?= (isset($razorpay_currency) && $razorpay_currency == "COP") ? "selected" : '' ?>>Colombian peso</option>
                                        <option value="CRC" <?= (isset($razorpay_currency) && $razorpay_currency == "CRC") ? "selected" : '' ?>>Costa Rican colon</option>
                                        <option value="CUP" <?= (isset($razorpay_currency) && $razorpay_currency == "CUP") ? "selected" : '' ?>>Cuban peso</option>
                                        <option value="CZK" <?= (isset($razorpay_currency) && $razorpay_currency == "CZK") ? "selected" : '' ?>>Czech koruna</option>
                                        <option value="DKK" <?= (isset($razorpay_currency) && $razorpay_currency == "DKK") ? "selected" : '' ?>>Danish krone</option>
                                        <option value="DOP" <?= (isset($razorpay_currency) && $razorpay_currency == "DOP") ? "selected" : '' ?>>Dominican peso</option>
                                        <option value="DZD" <?= (isset($razorpay_currency) && $razorpay_currency == "DZD") ? "selected" : '' ?>>Algerian dinar</option>
                                        <option value="EGP" <?= (isset($razorpay_currency) && $razorpay_currency == "EGP") ? "selected" : '' ?>>Egyptian pound</option>
                                        <option value="ETB" <?= (isset($razorpay_currency) && $razorpay_currency == "ETB") ? "selected" : '' ?>>Ethiopian birr</option>
                                        <option value="EUR" <?= (isset($razorpay_currency) && $razorpay_currency == "EUR") ? "selected" : '' ?>>European euro</option>
                                        <option value="FJD" <?= (isset($razorpay_currency) && $razorpay_currency == "FJD") ? "selected" : '' ?>>Fijian dollar</option>
                                        <option value="GBP" <?= (isset($razorpay_currency) && $razorpay_currency == "GBP") ? "selected" : '' ?>>Pound sterling</option>
                                        <option value="GHS" <?= (isset($razorpay_currency) && $razorpay_currency == "GHS") ? "selected" : '' ?>>Ghanian Cedi</option>
                                        <option value="GIP" <?= (isset($razorpay_currency) && $razorpay_currency == "GIP") ? "selected" : '' ?>>Gibraltar pound</option>
                                        <option value="GMD" <?= (isset($razorpay_currency) && $razorpay_currency == "GMD") ? "selected" : '' ?>>Gambian dalasi</option>
                                        <option value="GTQ" <?= (isset($razorpay_currency) && $razorpay_currency == "GTQ") ? "selected" : '' ?>>Guatemalan quetzal</option>
                                        <option value="GYD" <?= (isset($razorpay_currency) && $razorpay_currency == "GYD") ? "selected" : '' ?>>Guyanese dollar</option>
                                        <option value="HKD" <?= (isset($razorpay_currency) && $razorpay_currency == "HKD") ? "selected" : '' ?>>Hong Kong dollar</option>
                                        <option value="HNL" <?= (isset($razorpay_currency) && $razorpay_currency == "HNL") ? "selected" : '' ?>>Honduran lempira</option>
                                        <option value="HRK" <?= (isset($razorpay_currency) && $razorpay_currency == "HRK") ? "selected" : '' ?>>Croatian kuna</option>
                                        <option value="HTG" <?= (isset($razorpay_currency) && $razorpay_currency == "HTG") ? "selected" : '' ?>>Haitian gourde</option>
                                        <option value="HUF" <?= (isset($razorpay_currency) && $razorpay_currency == "HUF") ? "selected" : '' ?>>Hungarian forint</option>
                                        <option value="IDR" <?= (isset($razorpay_currency) && $razorpay_currency == "IDR") ? "selected" : '' ?>>Indonesian rupiah</option>
                                        <option value="ILS" <?= (isset($razorpay_currency) && $razorpay_currency == "ILS") ? "selected" : '' ?>>Israeli new shekel</option>
                                        <option value="INR" <?= (isset($razorpay_currency) && $razorpay_currency == "INR") ? "selected" : '' ?>>Indian rupee</option>
                                        <option value="JMD" <?= (isset($razorpay_currency) && $razorpay_currency == "JMD") ? "selected" : '' ?>>Jamaican dollar</option>
                                        <option value="KES" <?= (isset($razorpay_currency) && $razorpay_currency == "KES") ? "selected" : '' ?>>Kenyan shilling</option>
                                        <option value="KGS" <?= (isset($razorpay_currency) && $razorpay_currency == "KGS") ? "selected" : '' ?>>Kyrgyzstani som</option>
                                        <option value="KHR" <?= (isset($razorpay_currency) && $razorpay_currency == "KHR") ? "selected" : '' ?>>Cambodian riel</option>
                                        <option value="KYD" <?= (isset($razorpay_currency) && $razorpay_currency == "KYD") ? "selected" : '' ?>>Cayman Islands dollar</option>
                                        <option value="KZT" <?= (isset($razorpay_currency) && $razorpay_currency == "KZT") ? "selected" : '' ?>>Kazakhstani tenge</option>
                                        <option value="LAK" <?= (isset($razorpay_currency) && $razorpay_currency == "LAK") ? "selected" : '' ?>>Lao kip</option>
                                        <option value="LKR" <?= (isset($razorpay_currency) && $razorpay_currency == "LKR") ? "selected" : '' ?>>Sri Lankan rupee</option>
                                        <option value="LRD" <?= (isset($razorpay_currency) && $razorpay_currency == "LRD") ? "selected" : '' ?>>Liberian dollar</option>
                                        <option value="LSL" <?= (isset($razorpay_currency) && $razorpay_currency == "LSL") ? "selected" : '' ?>>Lesotho loti</option>
                                        <option value="MAD" <?= (isset($razorpay_currency) && $razorpay_currency == "MAD") ? "selected" : '' ?>>Moroccan dirham</option>
                                        <option value="MDL" <?= (isset($razorpay_currency) && $razorpay_currency == "MDL") ? "selected" : '' ?>>Moldovan leu</option>
                                        <option value="MKD" <?= (isset($razorpay_currency) && $razorpay_currency == "MKD") ? "selected" : '' ?>>Macedonian denar</option>
                                        <option value="MMK" <?= (isset($razorpay_currency) && $razorpay_currency == "MMK") ? "selected" : '' ?>>Myanmar kyat</option>
                                        <option value="MNT" <?= (isset($razorpay_currency) && $razorpay_currency == "MNT") ? "selected" : '' ?>>Mongolian tugrik</option>
                                        <option value="MOP" <?= (isset($razorpay_currency) && $razorpay_currency == "MOP") ? "selected" : '' ?>>Macanese pataca</option>
                                        <option value="MUR" <?= (isset($razorpay_currency) && $razorpay_currency == "MUR") ? "selected" : '' ?>>Mauritian rupee</option>
                                        <option value="MVR" <?= (isset($razorpay_currency) && $razorpay_currency == "MVR") ? "selected" : '' ?>>Maldivian rufiyaa</option>
                                        <option value="MWK" <?= (isset($razorpay_currency) && $razorpay_currency == "MWK") ? "selected" : '' ?>>Malawian kwacha</option>
                                        <option value="MXN" <?= (isset($razorpay_currency) && $razorpay_currency == "MXN") ? "selected" : '' ?>>Mexican peso</option>
                                        <option value="MYR" <?= (isset($razorpay_currency) && $razorpay_currency == "MYR") ? "selected" : '' ?>>Malaysian ringgit</option>
                                        <option value="NAD" <?= (isset($razorpay_currency) && $razorpay_currency == "NAD") ? "selected" : '' ?>>Namibian dollar</option>
                                        <option value="NGN" <?= (isset($razorpay_currency) && $razorpay_currency == "NGN") ? "selected" : '' ?>>Nigerian naira</option>
                                        <option value="NIO" <?= (isset($razorpay_currency) && $razorpay_currency == "NIO") ? "selected" : '' ?>>Nicaraguan cordoba</option>
                                        <option value="NOK" <?= (isset($razorpay_currency) && $razorpay_currency == "NOK") ? "selected" : '' ?>>Norwegian krone</option>
                                        <option value="NPR" <?= (isset($razorpay_currency) && $razorpay_currency == "NPR") ? "selected" : '' ?>>Nepalese rupee</option>
                                        <option value="NZD" <?= (isset($razorpay_currency) && $razorpay_currency == "NZD") ? "selected" : '' ?>>New Zealand dollar</option>
                                        <option value="PEN" <?= (isset($razorpay_currency) && $razorpay_currency == "PEN") ? "selected" : '' ?>>Peruvian sol</option>
                                        <option value="PGK" <?= (isset($razorpay_currency) && $razorpay_currency == "PGK") ? "selected" : '' ?>>Papua New Guinean kina</option>
                                        <option value="PHP" <?= (isset($razorpay_currency) && $razorpay_currency == "PHP") ? "selected" : '' ?>>Philippine peso</option>
                                        <option value="PKR" <?= (isset($razorpay_currency) && $razorpay_currency == "PKR") ? "selected" : '' ?>>Pakistani rupee</option>
                                        <option value="QAR" <?= (isset($razorpay_currency) && $razorpay_currency == "QAR") ? "selected" : '' ?>>Qatari riyal</option>
                                        <option value="RUB" <?= (isset($razorpay_currency) && $razorpay_currency == "RUB") ? "selected" : '' ?>>Russian ruble</option>
                                        <option value="SAR" <?= (isset($razorpay_currency) && $razorpay_currency == "SAR") ? "selected" : '' ?>>Saudi Arabian riyal</option>
                                        <option value="SCR" <?= (isset($razorpay_currency) && $razorpay_currency == "SCR") ? "selected" : '' ?>>Seychellois rupee</option>
                                        <option value="SEK" <?= (isset($razorpay_currency) && $razorpay_currency == "SEK") ? "selected" : '' ?>>Swedish krona</option>
                                        <option value="SGD" <?= (isset($razorpay_currency) && $razorpay_currency == "SGD") ? "selected" : '' ?>>Singapore dollar</option>
                                        <option value="SLL" <?= (isset($razorpay_currency) && $razorpay_currency == "SLL") ? "selected" : '' ?>>Sierra Leonean leone</option>
                                        <option value="SOS" <?= (isset($razorpay_currency) && $razorpay_currency == "SOS") ? "selected" : '' ?>>Somali shilling</option>
                                        <option value="SSP" <?= (isset($razorpay_currency) && $razorpay_currency == "SSP") ? "selected" : '' ?>>South Sudanese pound</option>
                                        <option value="SVC" <?= (isset($razorpay_currency) && $razorpay_currency == "SVC") ? "selected" : '' ?>>Salvadoran colón</option>
                                        <option value="SZL" <?= (isset($razorpay_currency) && $razorpay_currency == "SZL") ? "selected" : '' ?>>Swazi lilangeni</option>
                                        <option value="THB" <?= (isset($razorpay_currency) && $razorpay_currency == "THB") ? "selected" : '' ?>>Thai baht</option>
                                        <option value="TTD" <?= (isset($razorpay_currency) && $razorpay_currency == "TTD") ? "selected" : '' ?>>Trinidad and Tobago dollar </option>
                                        <option value="TZS" <?= (isset($razorpay_currency) && $razorpay_currency == "TZS") ? "selected" : '' ?>>Tanzanian shilling</option>
                                        <option value="USD" <?= (isset($razorpay_currency) && $razorpay_currency == "USD") ? "selected" : '' ?>>United States dollar</option>
                                        <option value="UYU" <?= (isset($razorpay_currency) && $razorpay_currency == "UYU") ? "selected" : '' ?>>Uruguayan peso</option>
                                        <option value="UZS" <?= (isset($razorpay_currency) && $razorpay_currency == "UZS") ? "selected" : '' ?>>Uzbekistani so'm</option>
                                        <option value="YER" <?= (isset($razorpay_currency) && $razorpay_currency == "YER") ? "selected" : '' ?>>Yemeni rial</option>
                                        <option value="ZAR" <?= (isset($razorpay_currency) && $razorpay_currency == "ZAR") ? "selected" : '' ?>>South African rand</option>
                                        <option value="TRY" <?= (isset($razorpay_currency) && $razorpay_currency == "TRY") ? "selected" : '' ?>>Turkish Lira</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="razorpay_secret"><?= labels('secret_key', 'Secret Key') ?></label>
                                    <input type="text" value="<?= isset($razorpay_secret) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $razorpay_secret) : '' ?>" name='razorpay_secret' id='razorpay_secret' placeholder='Enter Razor Pay secret key' class="form-control" />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="razorpay_key"><?= labels('API_key', 'API Key') ?></label>
                                    <input type="text" value="<?= isset($razorpay_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $razorpay_key) : '' ?>" name='razorpay_key' id='razorpay_key' placeholder='Enter Razor Pay API key' class="form-control" />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="endpoint"><?= labels('Payment_endpoint_URL', 'Payment Endpoint URL') ?><small>(<?= labels('set_this_as_endpoint_URL_in_your_razorpay_account', ' Set this as Endpoint URL in your razorpay account') ?>)</small></label>
                                    <input type="text" value="<?= base_url("/api/webhooks/razorpay") ?>" name='endpoint' id='endpoint' class="form-control" readonly />
                                </div>
                            </div>

                        </div>

                    </div>
                </div>


                <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12 d-flex">
                    <div class=" card px-3">
                        <div class="row">
                            <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                                <div class='toggleButttonPostition'><?= labels('paystack', 'Paystack') ?></div>

                            </div>

                            <!-- <div class="col d-flex justify-content-end toggleButttonPostition   ">

                                    <input type="checkbox" id="switch3" switch="bool" />
                                    <label for="switch3" data-on-label="Active" data-off-label="Inactive"></label>
                  
                            </div> -->
                        </div>


                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for='paystack_status'><?= labels('status', 'Status') ?></label>
                                    <select class='form-control selectric' name='paystack_status' id='paystack_status'>
                                        <option value='enable' <?= isset($paystack_status) && $paystack_status === 'enable' ? 'selected' : '' ?>>Enable</option>
                                        <option value='disable' <?= isset($paystack_status) && $paystack_status === 'disable' ? 'selected' : '' ?>>Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="paystack_mode"><?= labels('mode', 'Mode') ?></label>
                                    <select class='form-control selectric' name='paystack_mode' id='paystack_mode'>
                                        <option value='test' <?= isset($paystack_mode) && $paystack_mode === 'test' ? 'selected' : '' ?>>Test</option>
                                        <option value='live' <?= isset($paystack_mode) && $paystack_mode === 'live' ? 'selected' : '' ?>>Live</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="razorpayMode"><?= labels('currency_code', 'Currency Code') ?></label>
                                    <!-- <input type="text" value="<?= isset($paystack_currency) ? $paystack_currency : '' ?>" name='paystack_currency' id='paystack_currency' placeholder='Enter Paystack currency' class="form-control" /> -->
                                    <select class="form-control" name="paystack_currency" id="">
                                 
                                            <option value="GHS"   <?= (isset($paystack_currency) && $paystack_currency == "GHS") ? "selected" : '' ?>>Ghana (GHS)</option>
                                            <option value="NGN"   <?= (isset($paystack_currency) && $paystack_currency == "NGN") ? "selected" : '' ?>>Nigeria (NGN)</option>
                                            <option value="USD"   <?= (isset($paystack_currency) && $paystack_currency == "USD") ? "selected" : '' ?>>Nigeria (USD)</option>
                                            <option value="ZAR"   <?= (isset($paystack_currency) && $paystack_currency == "ZAR") ? "selected" : '' ?>>South Africa (ZAR)</option>
                                            <option value="KES"   <?= (isset($paystack_currency) && $paystack_currency == "KES") ? "selected" : '' ?>>Kenya (KES)</option>
                                            <option value="USD"   <?= (isset($paystack_currency) && $paystack_currency == "USD") ? "selected" : '' ?>>Kenya (USD)</option>
                                      

                                    </select>

                                </div>
                            </div>


                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="paystack_secret"><?= labels('secret_key', 'Secret Key') ?></label>
                                    <input type="text" value="<?= isset($paystack_secret) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $paystack_secret) : '' ?>" name='paystack_secret' id='paystack_secret' placeholder='Enter Razor Pay secret key' class="form-control" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="paystack_key"><?= labels('public_key', 'Public Key') ?></label>
                                    <input type="text" value="<?= isset($paystack_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $paystack_key) : '' ?>" name='paystack_key' id='paystack_key' placeholder='Enter Razor Pay API key' class="form-control" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="endpoint"><?= labels('Payment_endpoint_URL', 'Payment Endpoint URL') ?><small> (<?= labels('set_this_as_endpoint_URL_in_your_paystack_account', 'Set this as Endpoint URL in your paystack account') ?>)</small></label>
                                    <input type="text" value="<?= base_url("api/webhooks/paystack") ?>" name='endpoint' id='endpoint' class="form-control" readonly />
                                </div>
                            </div>
                        </div>

                    </div>
                </div>


                <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12    d-flex">

                    <div class="card px-3">
                        <div class="row">
                            <div class="col mb-3" style="border-bottom: solid 1px #e5e6e9;">
                                <div class='toggleButttonPostition'><?= labels('stripe', 'Stripe') ?></div>

                            </div>

                        </div>


                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for='stripe_status'><?= labels('status', 'Status') ?></label>
                                    <select class='form-control selectric' name='stripe_status' id='stripe_status'>
                                        <option value='enable' <?= isset($stripe_status) && $stripe_status === 'enable' ? 'selected' : '' ?>>Enable</option>
                                        <option value='disable' <?= isset($stripe_status) && $stripe_status === 'disable' ? 'selected' : '' ?>>Disable</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="razorpayMode"><?= labels('mode', 'Mode') ?></label>
                                    <select class='form-control selectric' name='stripe_mode' id='stripe_mode'>
                                        <option value='test' <?= isset($stripe_mode) && $stripe_mode === 'test' ? 'selected' : '' ?>>Test</option>
                                        <option value='live' <?= isset($stripe_mode) && $stripe_mode === 'live' ? 'selected' : '' ?>>Live</option>
                                    </select>
                                </div>
                            </div>
                            <!-- <div class="col-12">
                                <div class="form-group">
                                    <label for="razorpayMode"><?= labels('currency_code', 'Currency Code') ?></label>
                                    <input type="text" value="<?= isset($stripe_currency) ? $stripe_currency : '' ?>" name='stripe_currency' id='stripe_currency' placeholder='Enter stripe currency' class="form-control" />
                                </div>
                            </div> -->

                            <div class="col-12">
                                <div class="form-group">
                                    <label for="razorpayMode"><?= labels('currency_code', 'Currency Code') ?></label>
                                    <select name="stripe_currency" class="form-control mt-2">
                                        <option value="">Select Currency Code </option>
                                        <option value="INR" <?= (isset($stripe_currency) && $stripe_currency == "INR") ? "selected" : '' ?>>Indian rupee </option>
                                        <option value="USD" <?= (isset($stripe_currency) && $stripe_currency == "USD") ? "selected" : '' ?>>United States dollar </option>
                                        <option value="AED" <?= (isset($stripe_currency) && $stripe_currency == "AED") ? "selected" : '' ?>>United Arab Emirates Dirham </option>
                                        <option value="AFN" <?= (isset($stripe_currency) && $stripe_currency == "AFN") ? "selected" : '' ?>>Afghan Afghani </option>
                                        <option value="ALL" <?= (isset($stripe_currency) && $stripe_currency == "ALL") ? "selected" : '' ?>>Albanian Lek </option>
                                        <option value="AMD" <?= (isset($stripe_currency) && $stripe_currency == "AMD") ? "selected" : '' ?>>Armenian Dram </option>
                                        <option value="ANG" <?= (isset($stripe_currency) && $stripe_currency == "ANG") ? "selected" : '' ?>>Netherlands Antillean Guilder </option>
                                        <option value="AOA" <?= (isset($stripe_currency) && $stripe_currency == "AOA") ? "selected" : '' ?>>Angolan Kwanza </option>
                                        <option value="ARS" <?= (isset($stripe_currency) && $stripe_currency == "ARS") ? "selected" : '' ?>>Argentine Peso</option>
                                        <option value="AUD" <?= (isset($stripe_currency) && $stripe_currency == "AUD") ? "selected" : '' ?>> Australian Dollar</option>
                                        <option value="AWG" <?= (isset($stripe_currency) && $stripe_currency == "AWG") ? "selected" : '' ?>> Aruban Florin</option>
                                        <option value="AZN" <?= (isset($stripe_currency) && $stripe_currency == "AZN") ? "selected" : '' ?>> Azerbaijani Manat </option>
                                        <option value="BAM" <?= (isset($stripe_currency) && $stripe_currency == "BAM") ? "selected" : '' ?>> Bosnia-Herzegovina Convertible Mark </option>
                                        <option value="BBD" <?= (isset($stripe_currency) && $stripe_currency == "BBD") ? "selected" : '' ?>> Bajan dollar </option>
                                        <option value="BDT" <?= (isset($stripe_currency) && $stripe_currency == "BDT") ? "selected" : '' ?>> Bangladeshi Taka</option>
                                        <option value="BGN" <?= (isset($stripe_currency) && $stripe_currency == "BGN") ? "selected" : '' ?>> Bulgarian Lev </option>
                                        <option value="BIF" <?= (isset($stripe_currency) && $stripe_currency == "BIF") ? "selected" : '' ?>>Burundian Franc</option>
                                        <option value="BMD" <?= (isset($stripe_currency) && $stripe_currency == "BMD") ? "selected" : '' ?>> Bermudan Dollar</option>
                                        <option value="BND" <?= (isset($stripe_currency) && $stripe_currency == "BND") ? "selected" : '' ?>> Brunei Dollar </option>
                                        <option value="BOB" <?= (isset($stripe_currency) && $stripe_currency == "BOB") ? "selected" : '' ?>> Bolivian Boliviano </option>
                                        <option value="BRL" <?= (isset($stripe_currency) && $stripe_currency == "BRL") ? "selected" : '' ?>> Brazilian Real </option>
                                        <option value="BSD" <?= (isset($stripe_currency) && $stripe_currency == "BSD") ? "selected" : '' ?>> Bahamian Dollar </option>
                                        <option value="BWP" <?= (isset($stripe_currency) && $stripe_currency == "BWP") ? "selected" : '' ?>> Botswanan Pula </option>
                                        <option value="BZD" <?= (isset($stripe_currency) && $stripe_currency == "BZD") ? "selected" : '' ?>> Belize Dollar </option>
                                        <option value="CAD" <?= (isset($stripe_currency) && $stripe_currency == "CAD") ? "selected" : '' ?>> Canadian Dollar </option>
                                        <option value="CDF" <?= (isset($stripe_currency) && $stripe_currency == "CDF") ? "selected" : '' ?>> Congolese Franc </option>
                                        <option value="CHF" <?= (isset($stripe_currency) && $stripe_currency == "CHF") ? "selected" : '' ?>> Swiss Franc </option>
                                        <option value="CLP" <?= (isset($stripe_currency) && $stripe_currency == "CLP") ? "selected" : '' ?>> Chilean Peso </option>
                                        <option value="CNY" <?= (isset($stripe_currency) && $stripe_currency == "CNY") ? "selected" : '' ?>> Chinese Yuan </option>
                                        <option value="COP" <?= (isset($stripe_currency) && $stripe_currency == "COP") ? "selected" : '' ?>> Colombian Peso </option>
                                        <option value="CRC" <?= (isset($stripe_currency) && $stripe_currency == "CRC") ? "selected" : '' ?>> Costa Rican Colón </option>
                                        <option value="CVE" <?= (isset($stripe_currency) && $stripe_currency == "CVE") ? "selected" : '' ?>> Cape Verdean Escudo </option>
                                        <option value="CZK" <?= (isset($stripe_currency) && $stripe_currency == "CZK") ? "selected" : '' ?>> Czech Koruna </option>
                                        <option value="DJF" <?= (isset($stripe_currency) && $stripe_currency == "DJF") ? "selected" : '' ?>> Djiboutian Franc </option>
                                        <option value="DKK" <?= (isset($stripe_currency) && $stripe_currency == "DKK") ? "selected" : '' ?>> Danish Krone </option>
                                        <option value="DOP" <?= (isset($stripe_currency) && $stripe_currency == "DOP") ? "selected" : '' ?>> Dominican Peso </option>
                                        <option value="DZD" <?= (isset($stripe_currency) && $stripe_currency == "DZD") ? "selected" : '' ?>> Algerian Dinar </option>
                                        <option value="EGP" <?= (isset($stripe_currency) && $stripe_currency == "EGP") ? "selected" : '' ?>> Egyptian Pound </option>
                                        <option value="ETB" <?= (isset($stripe_currency) && $stripe_currency == "ETB") ? "selected" : '' ?>> Ethiopian Birr </option>
                                        <option value="EUR" <?= (isset($stripe_currency) && $stripe_currency == "EUR") ? "selected" : '' ?>> Euro </option>
                                        <option value="FJD" <?= (isset($stripe_currency) && $stripe_currency == "FJD") ? "selected" : '' ?>> Fijian Dollar </option>
                                        <option value="FKP" <?= (isset($stripe_currency) && $stripe_currency == "FKP") ? "selected" : '' ?>> Falkland Island Pound </option>
                                        <option value="GBP" <?= (isset($stripe_currency) && $stripe_currency == "GBP") ? "selected" : '' ?>> Pound sterling </option>
                                        <option value="GEL" <?= (isset($stripe_currency) && $stripe_currency == "GEL") ? "selected" : '' ?>> Georgian Lari </option>
                                        <option value="GIP" <?= (isset($stripe_currency) && $stripe_currency == "GIP") ? "selected" : '' ?>> Gibraltar Pound </option>
                                        <option value="GMD" <?= (isset($stripe_currency) && $stripe_currency == "GMD") ? "selected" : '' ?>> Gambian dalasi </option>
                                        <option value="GNF" <?= (isset($stripe_currency) && $stripe_currency == "GNF") ? "selected" : '' ?>> Guinean Franc </option>
                                        <option value="GTQ" <?= (isset($stripe_currency) && $stripe_currency == "GTQ") ? "selected" : '' ?>> Guatemalan Quetzal </option>
                                        <option value="GYD" <?= (isset($stripe_currency) && $stripe_currency == "GYD") ? "selected" : '' ?>> Guyanaese Dollar </option>
                                        <option value="HKD" <?= (isset($stripe_currency) && $stripe_currency == "HKD") ? "selected" : '' ?>> Hong Kong Dollar </option>
                                        <option value="HNL" <?= (isset($stripe_currency) && $stripe_currency == "HNL") ? "selected" : '' ?>> Honduran Lempira </option>
                                        <option value="HRK" <?= (isset($stripe_currency) && $stripe_currency == "HRK") ? "selected" : '' ?>> Croatian Kuna </option>
                                        <option value="HTG" <?= (isset($stripe_currency) && $stripe_currency == "HTG") ? "selected" : '' ?>> Haitian Gourde </option>
                                        <option value="HUF" <?= (isset($stripe_currency) && $stripe_currency == "HUF") ? "selected" : '' ?>> Hungarian Forint </option>
                                        <option value="IDR" <?= (isset($stripe_currency) && $stripe_currency == "IDR") ? "selected" : '' ?>> Indonesian Rupiah </option>
                                        <option value="ILS" <?= (isset($stripe_currency) && $stripe_currency == "ILS") ? "selected" : '' ?>> Israeli New Shekel </option>
                                        <option value="ISK" <?= (isset($stripe_currency) && $stripe_currency == "ISK") ? "selected" : '' ?>> Icelandic Króna </option>
                                        <option value="JMD" <?= (isset($stripe_currency) && $stripe_currency == "JMD") ? "selected" : '' ?>> Jamaican Dollar </option>
                                        <option value="JPY" <?= (isset($stripe_currency) && $stripe_currency == "JPY") ? "selected" : '' ?>> Japanese Yen </option>
                                        <option value="KES" <?= (isset($stripe_currency) && $stripe_currency == "KES") ? "selected" : '' ?>> Kenyan Shilling </option>
                                        <option value="KGS" <?= (isset($stripe_currency) && $stripe_currency == "KGS") ? "selected" : '' ?>> Kyrgystani Som </option>
                                        <option value="KHR" <?= (isset($stripe_currency) && $stripe_currency == "KHR") ? "selected" : '' ?>> Cambodian riel </option>
                                        <option value="KMF" <?= (isset($stripe_currency) && $stripe_currency == "KMF") ? "selected" : '' ?>> Comorian franc </option>
                                        <option value="KRW" <?= (isset($stripe_currency) && $stripe_currency == "KRW") ? "selected" : '' ?>> South Korean won </option>
                                        <option value="KYD" <?= (isset($stripe_currency) && $stripe_currency == "KYD") ? "selected" : '' ?>> Cayman Islands Dollar </option>
                                        <option value="KZT" <?= (isset($stripe_currency) && $stripe_currency == "KZT") ? "selected" : '' ?>> Kazakhstani Tenge </option>
                                        <option value="LAK" <?= (isset($stripe_currency) && $stripe_currency == "LAK") ? "selected" : '' ?>> Laotian Kip </option>
                                        <option value="LBP" <?= (isset($stripe_currency) && $stripe_currency == "LBP") ? "selected" : '' ?>> Lebanese pound </option>
                                        <option value="LKR" <?= (isset($stripe_currency) && $stripe_currency == "LKR") ? "selected" : '' ?>> Sri Lankan Rupee </option>
                                        <option value="LRD" <?= (isset($stripe_currency) && $stripe_currency == "LRD") ? "selected" : '' ?>> Liberian Dollar </option>
                                        <option value="LSL" <?= (isset($stripe_currency) && $stripe_currency == "LSL") ? "selected" : '' ?>>Lesotho loti </option>
                                        <option value="MAD" <?= (isset($stripe_currency) && $stripe_currency == "MAD") ? "selected" : '' ?>> Moroccan Dirham </option>
                                        <option value="MDL" <?= (isset($stripe_currency) && $stripe_currency == "MDL") ? "selected" : '' ?>> Moldovan Leu </option>
                                        <option value="MGA" <?= (isset($stripe_currency) && $stripe_currency == "MGA") ? "selected" : '' ?>> Malagasy Ariary </option>
                                        <option value="MKD" <?= (isset($stripe_currency) && $stripe_currency == "MKD") ? "selected" : '' ?>> Macedonian Denar </option>
                                        <option value="MMK" <?= (isset($stripe_currency) && $stripe_currency == "MMK") ? "selected" : '' ?>> Myanmar Kyat </option>
                                        <option value="MNT" <?= (isset($stripe_currency) && $stripe_currency == "MNT") ? "selected" : '' ?>> Mongolian Tugrik </option>
                                        <option value="MOP" <?= (isset($stripe_currency) && $stripe_currency == "MOP") ? "selected" : '' ?>> Macanese Pataca </option>
                                        <option value="MRO" <?= (isset($stripe_currency) && $stripe_currency == "MRO") ? "selected" : '' ?>> Mauritanian Ouguiya </option>
                                        <option value="MUR" <?= (isset($stripe_currency) && $stripe_currency == "MUR") ? "selected" : '' ?>> Mauritian Rupee</option>
                                        <option value="MVR" <?= (isset($stripe_currency) && $stripe_currency == "MVR") ? "selected" : '' ?>> Maldivian Rufiyaa </option>
                                        <option value="MWK" <?= (isset($stripe_currency) && $stripe_currency == "MWK") ? "selected" : '' ?>> Malawian Kwacha </option>
                                        <option value="MXN" <?= (isset($stripe_currency) && $stripe_currency == "MXN") ? "selected" : '' ?>> Mexican Peso </option>
                                        <option value="MYR" <?= (isset($stripe_currency) && $stripe_currency == "MYR") ? "selected" : '' ?>> Malaysian Ringgit </option>
                                        <option value="MZN" <?= (isset($stripe_currency) && $stripe_currency == "MZN") ? "selected" : '' ?>> Mozambican metical </option>
                                        <option value="NAD" <?= (isset($stripe_currency) && $stripe_currency == "NAD") ? "selected" : '' ?>> Namibian dollar </option>
                                        <option value="NGN" <?= (isset($stripe_currency) && $stripe_currency == "NGN") ? "selected" : '' ?>> Nigerian Naira </option>
                                        <option value="NIO" <?= (isset($stripe_currency) && $stripe_currency == "NIO") ? "selected" : '' ?>>Nicaraguan Córdoba </option>
                                        <option value="NOK" <?= (isset($stripe_currency) && $stripe_currency == "NOK") ? "selected" : '' ?>> Norwegian Krone </option>
                                        <option value="NPR" <?= (isset($stripe_currency) && $stripe_currency == "NPR") ? "selected" : '' ?>> Nepalese Rupee </option>
                                        <option value="NZD" <?= (isset($stripe_currency) && $stripe_currency == "NZD") ? "selected" : '' ?>> New Zealand Dollar </option>
                                        <option value="PAB" <?= (isset($stripe_currency) && $stripe_currency == "PAB") ? "selected" : '' ?>> Panamanian Balboa </option>
                                        <option value="PEN" <?= (isset($stripe_currency) && $stripe_currency == "PEN") ? "selected" : '' ?>> Sol </option>
                                        <option value="PGK" <?= (isset($stripe_currency) && $stripe_currency == "PGK") ? "selected" : '' ?>> Papua New Guinean Kina </option>
                                        <option value="PHP" <?= (isset($stripe_currency) && $stripe_currency == "PHP") ? "selected" : '' ?>>Philippine peso </option>
                                        <option value="PKR" <?= (isset($stripe_currency) && $stripe_currency == "PKR") ? "selected" : '' ?>> Pakistani Rupee </option>
                                        <option value="PLN" <?= (isset($stripe_currency) && $stripe_currency == "PLN") ? "selected" : '' ?>> Poland złoty </option>
                                        <option value="PYG" <?= (isset($stripe_currency) && $stripe_currency == "PYG") ? "selected" : '' ?>> Paraguayan Guarani </option>
                                        <option value="QAR" <?= (isset($stripe_currency) && $stripe_currency == "QAR") ? "selected" : '' ?>> Qatari Rial </option>
                                        <option value="RON" <?= (isset($stripe_currency) && $stripe_currency == "RON") ? "selected" : '' ?>>Romanian Leu </option>
                                        <option value="RSD" <?= (isset($stripe_currency) && $stripe_currency == "RSD") ? "selected" : '' ?>> Serbian Dinar </option>
                                        <option value="RUB" <?= (isset($stripe_currency) && $stripe_currency == "RUB") ? "selected" : '' ?>> Russian Ruble </option>
                                        <option value="RWF" <?= (isset($stripe_currency) && $stripe_currency == "RWF") ? "selected" : '' ?>> Rwandan franc </option>
                                        <option value="SAR" <?= (isset($stripe_currency) && $stripe_currency == "SAR") ? "selected" : '' ?>> Saudi Riyal </option>
                                        <option value="SBD" <?= (isset($stripe_currency) && $stripe_currency == "SBD") ? "selected" : '' ?>> Solomon Islands Dollar </option>
                                        <option value="SCR" <?= (isset($stripe_currency) && $stripe_currency == "SCR") ? "selected" : '' ?>>Seychellois Rupee </option>
                                        <option value="SEK" <?= (isset($stripe_currency) && $stripe_currency == "SEK") ? "selected" : '' ?>> Swedish Krona </option>
                                        <option value="SGD" <?= (isset($stripe_currency) && $stripe_currency == "SGD") ? "selected" : '' ?>> Singapore Dollar </option>
                                        <option value="SHP" <?= (isset($stripe_currency) && $stripe_currency == "SHP") ? "selected" : '' ?>> Saint Helenian Pound </option>
                                        <option value="SLL" <?= (isset($stripe_currency) && $stripe_currency == "SLL") ? "selected" : '' ?>> Sierra Leonean Leone </option>
                                        <option value="SOS" <?= (isset($stripe_currency) && $stripe_currency == "SOS") ? "selected" : '' ?>>Somali Shilling </option>
                                        <option value="SRD" <?= (isset($stripe_currency) && $stripe_currency == "SRD") ? "selected" : '' ?>> Surinamese Dollar </option>
                                        <option value="STD" <?= (isset($stripe_currency) && $stripe_currency == "STD") ? "selected" : '' ?>> Sao Tome Dobra </option>
                                        <option value="SZL" <?= (isset($stripe_currency) && $stripe_currency == "SZL") ? "selected" : '' ?>> Swazi Lilangeni </option>
                                        <option value="THB" <?= (isset($stripe_currency) && $stripe_currency == "THB") ? "selected" : '' ?>> Thai Baht </option>
                                        <option value="TJS" <?= (isset($stripe_currency) && $stripe_currency == "TJS") ? "selected" : '' ?>> Tajikistani Somoni </option>
                                        <option value="TOP" <?= (isset($stripe_currency) && $stripe_currency == "TOP") ? "selected" : '' ?>> Tongan Paʻanga </option>
                                        <option value="TRY" <?= (isset($stripe_currency) && $stripe_currency == "TRY") ? "selected" : '' ?>> Turkish lira </option>
                                        <option value="TTD" <?= (isset($stripe_currency) && $stripe_currency == "TTD") ? "selected" : '' ?>> Trinidad &amp; Tobago Dollar </option>
                                        <option value="TWD" <?= (isset($stripe_currency) && $stripe_currency == "TWD") ? "selected" : '' ?>> New Taiwan dollar </option>
                                        <option value="TZS" <?= (isset($stripe_currency) && $stripe_currency == "TZS") ? "selected" : '' ?>> Tanzanian Shilling </option>
                                        <option value="UAH" <?= (isset($stripe_currency) && $stripe_currency == "UAH") ? "selected" : '' ?>> Ukrainian hryvnia </option>
                                        <option value="UGX" <?= (isset($stripe_currency) && $stripe_currency == "UGX") ? "selected" : '' ?>> Ugandan Shilling </option>
                                        <option value="UYU" <?= (isset($stripe_currency) && $stripe_currency == "UYU") ? "selected" : '' ?>> Uruguayan Peso </option>
                                        <option value="UZS" <?= (isset($stripe_currency) && $stripe_currency == "UZS") ? "selected" : '' ?>> Uzbekistani Som </option>
                                        <option value="VND" <?= (isset($stripe_currency) && $stripe_currency == "VND") ? "selected" : '' ?>> Vietnamese dong </option>
                                        <option value="VUV" <?= (isset($stripe_currency) && $stripe_currency == "VUV") ? "selected" : '' ?>> Vanuatu Vatu </option>
                                        <option value="WST" <?= (isset($stripe_currency) && $stripe_currency == "WST") ? "selected" : '' ?>> Samoa Tala</option>
                                        <option value="XAF" <?= (isset($stripe_currency) && $stripe_currency == "XAF") ? "selected" : '' ?>> Central African CFA franc </option>
                                        <option value="XCD" <?= (isset($stripe_currency) && $stripe_currency == "XCD") ? "selected" : '' ?>> East Caribbean Dollar </option>
                                        <option value="XOF" <?= (isset($stripe_currency) && $stripe_currency == "XOF") ? "selected" : '' ?>> West African CFA franc </option>
                                        <option value="XPF" <?= (isset($stripe_currency) && $stripe_currency == "XPF") ? "selected" : '' ?>> CFP Franc </option>
                                        <option value="YER" <?= (isset($stripe_currency) && $stripe_currency == "YER") ? "selected" : '' ?>> Yemeni Rial </option>
                                        <option value="ZAR" <?= (isset($stripe_currency) && $stripe_currency == "ZAR") ? "selected" : '' ?>> South African Rand </option>
                                        <option value="ZMW" <?= (isset($stripe_currency) && $stripe_currency == "ZMW") ? "selected" : '' ?>> Zambian Kwacha </option>
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="publishable_key"><?= labels('stripe_publishable_key', 'Stripe Publishable key') ?></label>
                                    <input type="text" value="<?= isset($stripe_publishable_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $stripe_publishable_key) : '' ?>" name='stripe_publishable_key' id='stripe_publishable_key' placeholder='Enter Stripe Publishable key' class="form-control" />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="publishable_key"><?= labels('stripe_webhook_secret', 'Stripe Webhook secret') ?></label>
                                    <input type="text" value="<?= isset($stripe_webhook_secret_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $stripe_webhook_secret_key) : '' ?>" name='stripe_webhook_secret_key' id='stripe_webhook_secret_key' placeholder='Enter Stripe Publishable key' class="form-control" />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="razorpaySecretKey"><?= labels('stripe_secret_key', 'Stripe Secret key') ?></label>
                                    <input type="text" value="<?= isset($stripe_secret_key) ? ((defined('ALLOW_VIEW_KEYS') && ALLOW_VIEW_KEYS == 0) ? "asc****************adaca" : $stripe_secret_key) : '' ?>" name='stripe_secret_key' id='stripe_secret_key' placeholder='Enter Stripe secret key' class="form-control" />
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="endpoint"><?= labels('Payment_endpoint_URL', 'Payment Endpoint URL') ?><small> (<?= labels('set_this_as_endpoint_URL_in_your_stripe_account', 'Set this as Endpoint URL in your stripe account') ?>)</small></label>
                                    <input type="text" value="<?= site_url("api/webhooks/stripe") ?>" name='endpoint' id='endpoint' class="form-control" readonly />
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

            </div>


            <div class="row">
                <div class="col-md d-flex justify-content-lg-end m-1">
                    <div class="form-group">
                        <input type='submit' name='update' id='update' value='<?= labels('save_changes', "Save Changes") ?>' class='btn btn-primary' />

                    </div>
                </div>
            </div>


            <!-- Stripe Payment gateway code end -->
        </form>
    </section>
</div>