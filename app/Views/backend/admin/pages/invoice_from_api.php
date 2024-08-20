<html>

<head>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        p {
            margin: 0pt;
        }

        table.items {
            /* border: 0.1mm solid #000000; */
        }

        td {
            vertical-align: top;
        }


        .items td {
            /* border-left: 0.1mm solid #000000; */
            /* border-right: 0.1mm solid #000000; */
        }

        table thead td {
            background-color: #2560FC;
            /* text-align: center; */
            color: #ffffff;
            font-size: larger;
            /* border: 0.1mm solid #000000; */
            /* font-variant: small-caps; */
        }

        .items td.blanktotal {
            background-color: #EEEEEE;
            border: 0.1mm solid #000000;
            background-color: #FFFFFF;
            border: 0mm none #000000;
            border-top: 0.1mm solid #000000;
            border-right: 0.1mm solid #000000;
        }

        .items td.totals {
            text-align: right;
            border: 0.1mm solid #000000;
        }

        .items td.cost {
            text-align: "." center;
        }

        .text-primary {
            color: #2560FC;
        }

        .provider-image {
            /* border-radius: 08mm;
            border: 0.1mm solid */
        }
    </style>
</head>

<body>
    <table width="100%" style="font-family: serif;" cellpadding="10">
        <tr>
            <td width="45%">
                <img height="100px" width="200px" class="mb-4" src="<?= isset($data['logo']) && $data['logo'] != "" ? base_url("public/uploads/site/" . $data['logo']) : base_url('public/backend/assets/img/news/img01.jpg') ?>" class="sidebar_logo w-max-90 h-max-60px" alt="">

            </td>
            <td width="10%">&nbsp;</td>
            <td width="45% " style="text-align: right">
                <br />
                <div class="row">
                    <div class="col-2">
                        <div class=" mr-5">
                            <h2 class="text-primary">
                                INVOICE
                            </h2>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-2">

                        <div class="text-datk mr-5">Invoice no :
                            #INVO-<?= $order['id'] ?>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <?php
                    $date1 =  $order['created_at'];
                    $dt = new DateTime($date1);
                    $date = $dt->format('d-m-Y');
                    ?>
                    <div class="col-2">
                        <div class="text-datk mr-5">Invoice Date :
                            <?= $date ?>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-2">
                        <div class="text-datk mr-5">Status :
                            <?= $order['status'] ?>
                        </div>
                    </div>

                </div>



            </td>
        </tr>
    </table>


    <table width="100%" style="font-family: serif;" cellpadding="10">
        <tr>
            <td width="45%">
                <br /><br> <span style="font-size: 12pt;  font-family: sans;">SERVICE BY:</span>
                <br /><br />
                <div class="row">
                    <div class="col-2">
                        <div class="text-datk mr-5">Name :
                            <?= $partner_details['company_name'] ?>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-2">
                        <div class="text-datk mr-5">Email :
                            <?= $partner_details['email'] ?>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-2">
                        <div class="text-datk mr-5">Phone :
                            <?= $partner_details['phone'] ?>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-2">
                        <div class="text-datk mr-5">Address :
                            <?= $partner_details['address'] ?>
                        </div>
                    </div>

                </div>

            </td>
            <td width="10%">&nbsp;</td>

            <td width="45%" style="text-align: right">

                <br /><br> <span style="font-size: 12pt;  font-family: sans;">BILLING ADDRESS :</span>

                <div class="row">
                    <div class="col-2">
                        <div class="text-datk mr-5">Name :
                            <?= $user_details['username'] ?>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-2">
                        <div class="text-datk mr-5">Email :
                            <?= $user_details['email'] ?>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-2">
                        <div class="text-datk mr-5">Phone :
                            <?= $user_details['phone'] ?>
                        </div>
                    </div>

                </div>

            </td>
        </tr>
    </table>
    <br />
    <table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse; " cellpadding="8">

        <tbody>
            <!-- ITEMS HERE -->


            <thead>
                <tr>
                    <td style="padding-right:25px;padding-left:25px" class="text-left">
                        <div class="th-inner ">Services</div>
                        <div class="fht-cell"></div>
                    </td>
                    <td style="padding-right:25px;padding-left:25px" class="text-left">
                        <div class="th-inner ">Price</div>
                        <div class="fht-cell"></div>
                    </td>
                    <td style="padding-right:25px;padding-left:25px" class="text-left">
                        <div class="th-inner ">Discount</div>
                        <div class="fht-cell"></div>
                    </td>
                    <td style="padding-right:25px;padding-left:25px;white-space:nowrap;" class="text-right">
                        <div class=" ">Net Amount</div>
                        <div class=""></div>
                    </td>
                    <td style="padding-right:25px;padding-left:25px" class="text-left">
                        <div class="th-inner ">Tax</div>
                        <div class="fht-cell"></div>
                    </td>
                    <td style="padding-right:25px;padding-left:25px;white-space:nowrap;" class="text-right">
                        <div class="th-inner ">Tax Amount</div>
                        <div class="fht-cell"></div>
                    </td>
                    <td style="padding-right:25px;padding-left:25px" class="text-left">
                        <div class="th-inner ">Quantity</div>
                        <div class="fht-cell"></div>
                    </td>
                    <td style="padding-right:25px;padding-left:25px;white-space:nowrap;"class="text-right">
                        <div class="th-inner ">Sub total (Including Tax)</div>
                        <div class="fht-cell"></div>
                    </td>
                    <td style="padding-right:25px;padding-left:25px;white-space:nowrap;" class="text-right">
                        <!-- <div class="th-inner ">Sub total (Including Tax)</div>
                        <div class="fht-cell"></div> -->
                    </td>
                </tr>
            </thead>
        <tbody>

            <?php

            foreach ($rows as $r) { ?>

                <tr data-index="1">
                    <td style="padding-right:25px;padding-left:25px;white-space:nowrap;" class="text-right"><?= $r['service_title']; ?></td>
                    <td style="padding-right:25px;padding-left:25px;white-space:nowrap;" class="text-right"><?= $r['price'] ?></td>
                    <td style="padding-right:25px;padding-left:25px;white-space:nowrap;" class="text-right"><?= $r['discount'] ?></td>
                    <td style="padding-right:25px;padding-left:25px;white-space:nowrap;" class="text-right"><?= $r['net_amount'] ?></td>
                    <td style="padding-right:25px;padding-left:25px;white-space:nowrap;" class="text-right"><?= $r['tax'] ?></td>
                    <td style="padding-right:25px;padding-left:25px;white-space:nowrap;" class="text-right"><?= $r['tax_amount'] ?></td>
                    <td style="padding-right:25px;padding-left:25px;white-space:nowrap;" class="text-right"><?= $r['quantity'] ?></td>
                    <td style="padding-left:25px;white-space:nowrap;" class="text-left"><?= $r['subtotal'] ?></td>
                </tr>
            <?php }

            ?>

            <tr data-index="3">
                <td></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
             
                <td class="text-right" style="padding-right:25px;padding-left:25px;" ><strong class="font-weight-bold text-dark"><b>Total</b></strong></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px;"><strong class="text-dark"><?php echo $currency . $order['total'] ?></strong></td>
            </tr>
            <tr data-index="4">
                <td></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>

                <td class="text-right" style="padding-right:25px;padding-left:25px;white-space:nowrap;"><strong class="text-dark ">Visiting Charges</strong></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px;white-space:nowrap;"><strong class="text-dark "><?php echo  $currency . $order['visiting_charges'] ?></strong></td>
            </tr>
            <tr data-index="5">
                <td></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
             
                <td class="text-right" style="padding-right:25px;padding-left:25px;white-space:nowrap;"><strong class="text-dark ">Promo Code Discount</strong></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px;white-space:nowrap;"><strong class="text-dark "><?php echo $currency . $order['promo_discount'] ?></strong></td>
            </tr>
            <tr data-index="6">
                <td></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
                <td class="text-right" style="padding-right:25px;padding-left:25px"></td>
 
                <td class="text-right" style="padding-right:25px;padding-left:25px;white-space:nowrap;"><strong class="text-dark ">
                        <h4>Final Total</h4>
                    </strong></td>
                <td class="text-left" style="padding-right:25px;padding-left:25px;white-space:nowrap;"><strong class="text-dark "><?php echo $currency . $order['final_total'] ?></strong></td>
            </tr>
        </tbody>
    </table>
    <img class="provider-image" height="80px" width="80px" src="<?= $partner_details['image'] ?>" alt="Partner LOGO">
    <div style=" "> <?= labels('thank_you_for_your_business', ' Thank you for your Business') ?></div>
</body>

</html>