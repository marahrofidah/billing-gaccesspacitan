<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - <?= $bill['invoice'] ?> a/n <?= $bill['name'] ?> Periode <?= indo_month($bill['month']) ?> <?= $bill['year'] ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/') ?>frontend/libraries/bootstrap/css/bootstrap.css">
    <link href="<?= base_url('assets/backend/') ?>vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
</head>

<body onload="window.print()">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #fff;
            font: 12pt "Tahoma";
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        * {
            box-sizing: border-box;
            -moz-box-sizing: border-box;
        }

        .page {
            width: 21cm;
            min-height: 29.7cm;
            padding: 2cm;
            margin: 1cm auto;
            border: 1px #D3D3D3 solid;
            border-radius: 5px;
            background: #fff;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .invoice h3 {
            margin-top: -40px;
            font-weight: bold;
            font-size: 25px;
        }

        .invoice h6 {
            margin-top: -20px;
            font-size: 16px;
        }

        .invoice span {
            margin-top: -55px;
            font-size: 12px;
        }

        .invoice img {
            margin-top: -40px;
            max-height: 60px;
        }

        .invoice-title h3 {
            margin-top: -15px;
            font-size: 40px;
            font-weight: bold;
            color: darkblue;
        }


        .fromto h5 {
            font-weight: bold;
            font-size: 20px;
        }

        .watermark-img {
            /* background: url("https://www.google.co.in/images/srpr/logo11w.png") center center no-repeat; */
            opacity: 0.6;

            position: absolute;

            margin-left: 25%;
            margin-right: 25%;


            display: block;

            z-index: -1;
        }

        .lunas {
            text-align: center;
            font-weight: bold;
            color: green;
            border-width: 2px;
            border-style: dashed solid;
            position: relative;
            margin: 1em 0;
            transform: rotate(-20deg);
            -ms-transform: rotate(-20deg);
            -webkit-transform: rotate(-20deg);
        }

        @page {
            size: A4;
            margin: 0;
        }

        @media print {
            body,
            .page {
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page {
                margin: 0;
                border: initial;
                border-radius: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                page-break-after: always;
            }
        }
    </style>

    <div class="book">
        <?php if ($package['invoice_no'] == 0) { ?>
            <div class="page">
                <div class="row invoice">
                    <div class="col-8">
                        <h3><?= $company['company_name'] ?></h3>
                        <br>
                        <h6>
                            <?= $company['sub_name'] ?>
                        </h6>
                        <span style="font-style: italic;">Alamat : <?= $company['address'] ?></span> <br>
                        <span>No HP : <?= $company['whatsapp'] ?> email : <?= $company['email'] ?></span>
                    </div>
                    <div class="col-4 text-right">
                        <img src="<?= base_url('assets/images/' . $company['logo']) ?>" alt="logo">

                    </div>
                </div>
                <hr>
                <div class="row invoice-title">
                    <div class="col text-right">
                        <h3>INVOICE</h3>
                    </div>
                </div>

                <div class="row fromto">
                    <div class="col-6">
                        Kepada :
                        <h5><?= $bill['name'] ?></h5>
                        <h6><?= indo_tlp($bill['no_wa']) ?></h6>
                        <h6><?= $bill['address'] ?></h6>
                    </div>
                    <div class="col-4 text-right">
                        <h6 style="font-weight:bold">ID Pelanggan : </h6>
                        <h6 style="font-weight:bold">No Invoice : </h6>
                        <h6 style="font-weight:bold">Tanggal Invoice :</h6>
                        <h6 style="font-weight:bold">Jatuh Tempo :</h6>
                    </div>
                    <div class="col-2 text-left" style="margin-left:-15 ;">
                        <h6><?= $bill['no_services'] ?></h6>
                        <h6><?= $bill['invoice'] ?></h6>
                        <h6><?= date('d-m-Y', $bill['created_invoice']) ?></h6>
                        <?php $duedate = strtotime($bill['inv_due_date']) ?>
                        <h6><?= date('d-m-Y', $duedate) ?></h6>
                    </div>
                </div>
                <br>
                <div class="row justify-content-between mb-2">
                    <div class="col-6">Periode <?= indo_month($bill['month']) ?> <?= $bill['year'] ?></div>
                    <div class="col-6 text-right"> <?php if ($bill['status'] == 'BELUM BAYAR') {   ?>
                            <h5 style="color: red;font-size:small; text-align: right; font-weight:bold;"> Belum Bayar</h5>
                        <?php } ?>
                    </div>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width:10px">No</th>
                            <th>Item</th>
                            <th style="text-align: center">Qty</th>
                            <th style="text-align: right">Harga</th>
                            <th style="text-align: center">Disc</th>
                            <th style="text-align: right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $month =  $bill['month'];
                        $year = $bill['year'];
                        $no_services = $bill['no_services'];
                        $query = "SELECT *, `invoice_detail`.`price` as `detail_price`
                            FROM `invoice_detail`
                            Join `package_item` ON `package_item`.`p_item_id` = `invoice_detail`.`item_id`
                                WHERE `invoice_detail`.`d_month` =  $month and
                               `invoice_detail`.`d_year` =  $year and
                               `invoice_detail`.`d_no_services` =  $no_services";
                        $queryTot = $this->db->query($query)->result(); ?>
                        <?php $subTotaldetail = 0;
                        foreach ($queryTot as  $dataa)
                            $subTotaldetail += (int) $dataa->total;
                        ?>


                        <?php if ($subtotal > 0) { ?>
                            <?php $no = 1;
                            foreach ($invoice_detail->result() as $c => $data) { ?>
                                <tr>
                                    <td style="text-align: center;"><?= $no++ ?>.</td>
                                    <td><?= $data->item_name ?> <br>
                                        <?= $data->remark ?></td>
                                    <td style="text-align: center"><?= $data->qty ?></td>
                                    <td style="text-align: right"><?= indo_currency($data->detail_price) ?></td>
                                    <td style="text-align: right">
                                        <?php if ($data->disc <= 0) { ?>
                                            -
                                        <?php } ?>
                                        <?php if ($data->disc > 0) { ?>
                                            <?= indo_currency($data->disc)  ?>
                                        <?php } ?>
                                    </td>
                                    <td style="text-align: right"><?= indo_currency($data->total) ?></td>

                                </tr>
                            <?php
                            }
                            ?>
                        <?php } ?>
                        <?php if ($subtotal <= 0) { ?>
                            <?php $no = 1;
                            foreach ($queryTot as  $data) { ?>
                                <tr>
                                    <td style="text-align: center;"><?= $no++ ?>.</td>
                                    <td><?= $data->name ?> <br>
                                        <?= $data->remark ?></td>
                                    <td style="text-align: center"><?= $data->qty ?></td>
                                    <td style="text-align: right"><?= indo_currency($data->detail_price) ?></td>
                                    <td style="text-align: right">
                                        <?php if ($data->disc <= 0) { ?>
                                            -
                                        <?php } ?>
                                        <?php if ($data->disc > 0) { ?>
                                            <?= indo_currency($data->disc)  ?>
                                        <?php } ?>
                                    </td>
                                    <td style="text-align: right"><?= indo_currency($data->total) ?></td>

                                </tr>
                            <?php
                            }
                            ?>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <!-- KODE UNIK -->
                        <?php if ($bill['codeunique'] == 1) { ?>
                            <?php $code_unique = $bill['code_unique'] ?>
                        <?php } ?>
                        <?php if ($bill['codeunique'] == 0) { ?>
                            <?php $code_unique = 0 ?>
                        <?php } ?>
                        <!-- END KODE UNIK -->

                        <?php if ($subtotal > 0) { ?>
                            <?php $ppn = $subtotal * ($bill['i_ppn'] / 100) ?>
                        <?php } ?>
                        <?php if ($subtotal <= 0) { ?>
                            <?php $ppn = $subTotaldetail * ($bill['i_ppn'] / 100) ?>

                        <?php } ?>
                        <?php if ($bill['i_ppn']  > 0) { ?>
                            <tr class="text-right" style="font-size: small;">
                                <th colspan="5">PPN (<?= $bill['i_ppn'] ?>%)</th>
                                <th><?= indo_currency($ppn) ?></th>
                            </tr>
                        <?php } ?>
                        <?php $discount = $bill['disc_coupon'] ?>
                        <?php if ($bill['disc_coupon'] > 0) { ?>
                            <tr class="text-right" style="font-size: small;">
                                <th colspan="5">Diskon</th>
                                <th><?= indo_currency($discount) ?></th>
                            </tr>
                        <?php } ?>
                        <?php if ($bill['codeunique'] == 1) { ?>
                            <tr class="text-right" style="font-size: small;">
                                <th colspan="5">Kode Unik</th>
                                <th><?= $code_unique ?></th>
                            </tr>
                        <?php } ?>
                        <?php if ($company['admin_fee'] > 0) { ?>
                            <tr class="text-right" style="font-size: small;">
                                <th colspan="5">Biaya Admin</th>
                                <th><?= $company['admin_fee'] ?></th>
                            </tr>
                        <?php } ?>
                        <tr style="text-align: right">
                            <th colspan="5">Total Tagihan</th>
                            <?php if ($subtotal > 0) { ?>
                                <th><?= indo_currency($subtotal + $code_unique + $ppn + $company['admin_fee'] - $discount)  ?></th>
                            <?php } ?>
                            <?php if ($subtotal <= 0) { ?>
                                <th><?= indo_currency($subTotaldetail + $code_unique + $ppn + $company['admin_fee'] - $discount)  ?></th>
                            <?php } ?>
                        </tr>
                    </tfoot>
                </table>
                <?php if ($company['watermark'] == 1) { ?>
                    <div class="watermark-img" style="text-align: center;">
                        <img src="<?= base_url('assets/images/' . $company['logo']) ?>" alt="" style="width: 450px;">
                    </div>
                <?php } ?>
                <?php if ($subtotal > 0) { ?>
                    <span style="font-style: italic; ">* Terbilang : <?= to_word($subtotal + $code_unique + $ppn + $company['admin_fee'] - $discount) ?> rupiah</span>
                <?php } ?>
                <?php if ($subtotal <= 0) { ?>
                    <span style="font-style: italic; ">* Terbilang : <?= to_word($subTotaldetail + $code_unique + $ppn + $company['admin_fee'] - $discount) ?> rupiah</span>
                <?php } ?>

                <br> <?php if ($bill['codeunique'] == 1) { ?>
                    <br>
                    <h6 style="font-weight: bold; color:red"> Catatan :</h6>
                    <?php if ($subtotal > 0) { ?>
                        Transfer tepat <b><?= indo_currency($subtotal + $code_unique + $ppn + $company['admin_fee'] - $discount)  ?></b> (Tagihan + Kode Unik) <?= $other['text_code_unique']; ?> <br>
                    <?php } ?>
                    <?php if ($subtotal <= 0) { ?>
                        Transfer tepat <b><?= indo_currency($subTotaldetail + $code_unique + $ppn + $company['admin_fee'] - $discount)  ?></b> (Tagihan + Kode Unik) <?= $other['text_code_unique']; ?> <br>
                    <?php } ?>
                <?php } ?>
                <?php if (count($bank) > 0) { ?>
                    <br> <b> Cara Pembayaran Bisa Transfer :</b> <br>
                <?php } ?>

                <?php
                foreach ($bank as $r => $data) { ?>
                    <?= $data->name ?> : <?= $data->no_rek ?> A/N <?= $data->owner ?>
                    <br>
                <?php } ?>
                <br><br>
                <b>Konfirmasi Pembayaran :</b> <br>
                Email : <?= $company['email'] ?>
                <br>
                WA : <?= $company['whatsapp'] ?>
                <div class="row text-center">
                    <div class="col-12">
                        <img src="<?= base_url('assets/images/qrcode/') ?><?= $bill['invoice'] ?>.png" alt="">
                    </div>
                </div>
                <?php if ($bill['status'] == 'SUDAH BAYAR') {
                ?>
                    <div class="row text-center justify-content-center">
                        <div class="col-6 lunas">
                            <h1>LUNAS</h1>
                        </div>
                    </div>
                <?php } ?>
                <style>
                    .container {
                        display: flex;
                        flex-direction: column;
                        height: 10vh;
                    }

                    footer {

                        flex-shrink: 0;
                    }

                    main {
                        flex: 1 0 auto;
                    }
                </style>
                <div class="container">

                    <main class="content">

                    </main>
                    <footer>
                        <div class="row">
                            <div class="col-5 text-right border-right border-primary">
                                <h3 style="margin-top: 5px;">Terimakasih</h3>
                            </div>
                            <div class="col-7 text-left">
                                <h6 style="color: red;">Syarat dan Ketentuan</h6>
                                <?php if ($bill['status'] == 'SUDAH BAYAR') { ?>
                                    <span>Terimakasih sudah melakukan pembayaran tepat waktu</span>
                                <?php } ?>
                                <?php if ($bill['status'] == 'BELUM BAYAR') { ?>
                                    <span>Mohon lakukan pembayaran tepat waktu</span>
                                <?php } ?>
                            </div>

                        </div>
                        <?php if ($bill['create_by'] != 0) { ?>
                            <?php
                            $user_id =  $bill['create_by'];
                            $query = "SELECT *
                            FROM `user` WHERE `user`.`id` =  $user_id";
                            $operator = $this->db->query($query)->row_array(); ?>
                            <div class="row mt-2">
                                <div class="col"> Diterima Oleh : <?= $operator['name'] ?> </div>
                                <div class="col">Pada Tanggal <?= date('d M Y', $bill['date_payment']) ?></div>
                            </div>
                        <?php } ?>
                    </footer>
                </div>
            </div>
        <?php } ?>

        <?php if ($package['invoice_no'] == 1) { ?>
            <div class="page">
                <div class="row">
                    <div class="col-6">
                        <img src="<?= base_url('assets/images/' . $company['logo']) ?>" alt="logo" style="width: 200px;">
                    </div>
                    <div class="col-6 text-right">
                        <?php $link = "www.$_SERVER[HTTP_HOST]"; ?>
                        <span> <?= $link; ?> <i class="fa fa-globe"></i></span> <br>
                        <span> <?= $company['email'] ?> <i class="fa fa-envelope"></i></span> <br>
                        <span> <?= $company['phonecode'] ?><?= $company['whatsapp'] ?> <i class="fa fa-phone"></i></span> <br>
                    </div>
                </div>
                <hr size="20px" color="blue">
                <h4 style="text-align: center; font-weight:bold"><ins>INVOICE</h4>
                <h6 style="text-align: center;">No : INV-<?= $bill['invoice'] ?></h6>

                <div class="row mt-3">
                    <div class="col-4">

                    </div>
                    <div class="col-4 text-right">
                        <h6 style="font-weight:bold">ID Pelanggan : </h6>
                        <h6 style="font-weight:bold">Tanggal Invoice :</h6>
                        <h6 style="font-weight:bold">Jatuh Tempo :</h6>
                    </div>
                    <div class="col-4 text-left">
                        <h6><?= $bill['no_services'] ?></h6>
                        <h6><?= date('d-m-Y', $bill['created_invoice']) ?></h6>
                        <?php $duedate = strtotime($bill['inv_due_date']) ?>
                        <h6><?= date('d-m-Y', $duedate) ?></h6>

                    </div>
                </div>


                <h6 style="font-weight:bold">Kepada Yth : </h6>

                <div class="invoice01" style="margin-left: 15;">
                    <h6 style="font-weight:bold"><?= $bill['name']; ?></h6>
                    <span><?= $bill['address']; ?></span> <br>
                </div>
                <div class="row justify-content-between mb-2 mt-3">
                    <div class="col-6">Periode <?= indo_month($bill['month']) ?> <?= $bill['year'] ?></div>

                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width:10px">No</th>
                            <th>Paket Layanan</th>
                            <th style="text-align: center">Qty</th>
                            <th style="text-align: right">Harga</th>
                            <th style="text-align: center">Disc</th>
                            <th style="text-align: right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $month =  $bill['month'];
                        $year = $bill['year'];
                        $no_services = $bill['no_services'];
                        $query = "SELECT *, `invoice_detail`.`price` as `detail_price`
                            FROM `invoice_detail`
                            Join `package_item` ON `package_item`.`p_item_id` = `invoice_detail`.`item_id`
                                WHERE `invoice_detail`.`d_month` =  $month and
                               `invoice_detail`.`d_year` =  $year and
                               `invoice_detail`.`d_no_services` =  $no_services";
                        $queryTot = $this->db->query($query)->result(); ?>
                        <?php $subTotaldetail = 0;
                        foreach ($queryTot as  $dataa)
                            $subTotaldetail += (int) $dataa->total;
                        ?>


                        <?php if ($subtotal > 0) { ?>
                            <?php $no = 1;
                            foreach ($invoice_detail->result() as $c => $data) { ?>
                                <tr>
                                    <td style="text-align: center;"><?= $no++ ?>.</td>
                                    <td><?= $data->item_name ?> <br>
                                        <?= $data->remark ?> </td>
                                    <td style="text-align: center"><?= $data->qty ?></td>
                                    <td style="text-align: right"><?= indo_currency($data->detail_price) ?></td>
                                    <td style="text-align: right">
                                        <?php if ($data->disc <= 0) { ?>
                                            -
                                        <?php } ?>
                                        <?php if ($data->disc > 0) { ?>
                                            <?= indo_currency($data->disc)  ?>
                                        <?php } ?>
                                    </td>
                                    <td style="text-align: right"><?= indo_currency($data->total) ?></td>

                                </tr>
                            <?php
                            }
                            ?>
                        <?php } ?>
                        <?php if ($subtotal <= 0) { ?>
                            <?php $no = 1;
                            foreach ($queryTot as  $data) { ?>
                                <tr>
                                    <td style="text-align: center;"><?= $no++ ?>.</td>
                                    <td><?= $data->name ?> <br>
                                        <?= $data->remark ?> </td>
                                    <td style="text-align: center"><?= $data->qty ?></td>
                                    <td style="text-align: right"><?= indo_currency($data->detail_price) ?></td>
                                    <td style="text-align: right">
                                        <?php if ($data->disc <= 0) { ?>
                                            -
                                        <?php } ?>
                                        <?php if ($data->disc > 0) { ?>
                                            <?= indo_currency($data->disc)  ?>
                                        <?php } ?>
                                    </td>
                                    <td style="text-align: right"><?= indo_currency($data->total) ?></td>

                                </tr>
                            <?php
                            }
                            ?>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <!-- KODE UNIK -->
                        <?php if ($bill['codeunique'] == 1) { ?>
                            <?php $code_unique = $bill['code_unique'] ?>
                        <?php } ?>
                        <?php if ($bill['codeunique'] == 0) { ?>
                            <?php $code_unique = 0 ?>
                        <?php } ?>
                        <!-- END KODE UNIK -->

                        <?php if ($subtotal > 0) { ?>
                            <?php $ppn = $subtotal * ($bill['i_ppn'] / 100) ?>
                        <?php } ?>
                        <?php if ($subtotal <= 0) { ?>
                            <?php $ppn = $subTotaldetail * ($bill['i_ppn'] / 100) ?>

                        <?php } ?>
                        <?php if ($bill['i_ppn']  > 0) { ?>
                            <tr class="text-right" style="font-size: small;">
                                <th colspan="5">PPN (<?= $bill['i_ppn'] ?>%)</th>
                                <th><?= indo_currency($ppn) ?></th>
                            </tr>
                        <?php } ?>
                        <?php if ($bill['codeunique'] == 1) { ?>
                            <tr class="text-right" style="font-size: small;">
                                <th colspan="5">Kode Unik</th>
                                <th><?= $code_unique ?></th>
                            </tr>
                        <?php } ?>
                        <?php if ($company['admin_fee'] > 0) { ?>
                            <tr class="text-right" style="font-size: small;">
                                <th colspan="5">Biaya Admin</th>
                                <th><?= $company['admin_fee'] ?></th>
                            </tr>
                        <?php } ?>
                        <tr style="text-align: right">
                            <th colspan="5">Total Tagihan</th>
                            <?php if ($subtotal > 0) { ?>
                                <th><?= indo_currency($subtotal + $code_unique + $ppn + $company['admin_fee'])  ?></th>
                            <?php } ?>
                            <?php if ($subtotal <= 0) { ?>
                                <th><?= indo_currency($subTotaldetail + $code_unique + $ppn + $company['admin_fee'])  ?></th>
                            <?php } ?>
                        </tr>
                    </tfoot>
                </table>
                <?php if ($subtotal > 0) { ?>
                    <span style="font-style: italic; ">* Terbilang : <?= to_word($subtotal + $code_unique + $ppn + $company['admin_fee']) ?> rupiah</span>
                <?php } ?>
                <?php if ($subtotal <= 0) { ?>
                    <span style="font-style: italic; ">* Terbilang : <?= to_word($subTotaldetail + $code_unique + $ppn + $company['admin_fee']) ?> rupiah</span>
                <?php } ?>
                <div class="row mt-3">
                    <div class="col"></div>
                    <div class="col" style="text-align: center;"><?= $company['company_name']; ?>
                        <br>
                        <br>
                        <br>
                        <br>
                        <?= $this->session->userdata('name'); ?>
                    </div>

                </div>
                <br> <b> No. Rekening Untuk Pembayaran :</b> <br>
                <?php
                foreach ($bank as $r => $data) { ?>
                    <?= $data->name ?> : <?= $data->no_rek ?> A/N <?= $data->owner ?>
                    <br>
                <?php } ?>
                <br>
                <b>Konfirmasi Pembayaran :</b> <br>
                Email : <?= $company['email'] ?>
                <br>
                WA : <?= indo_tlp($company['phonecode'] . $company['whatsapp']) ?>

                <style>
                    .footer {
                        background-color: #320ac4;
                        margin-top: 20px;
                        text-align: center;
                        color: white;
                        padding: 10px;
                        clear: both;
                        position: relative;


                    }
                </style>


                <main class="content">

                </main>
                <footer>
                    <div class="footer">

                        <?= $company['address'] ?>

                    </div>

                </footer>

            </div>
        <?php } ?>

    </div>
    <!-- end page -->
    <script src="<?= base_url('assets/') ?>frontend/libraries/jquery/jquery-3.4.1.min.js"></script>
    <script src="<?= base_url('assets/') ?>frontend/libraries/bootstrap/js/bootstrap.js"></script>
</body>

</html>