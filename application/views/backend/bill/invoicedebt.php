<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> a/n <?= $bill['name'] ?> Tanggal Cetak <?= date('d') ?> <?= indo_month(date('m')) ?> <?= date('Y') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/') ?>frontend/libraries/bootstrap/css/bootstrap.css">
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
        <div class="page">
            <div class="row invoice">
                <div class="col-8">
                    <h3><?= $company['company_name'] ?></h3>
                    <br>
                    <h6>
                        <?= $company['sub_name'] ?>
                    </h6>
                    <span>No HP : <?= $company['whatsapp'] ?> email : <?= $company['email'] ?></span> <br>
                    <span style="font-style: italic;">Alamat : <?= $company['address'] ?></span>
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
                    <h5><?= $customer['name'] ?></h5>
                    <h6><?= indo_tlp($customer['no_wa']) ?></h6>
                    <h6><?= $customer['address'] ?></h6>
                </div>
                <div class="col-4 text-right">
                    <h6 style="font-weight:bold">ID Pelanggan : </h6>
                    <h6 style="font-weight:bold">Periode : </h6>
                    <h6 style="font-weight:bold">Tanggal Cetak : </h6>

                </div>
                <div class="col-2 text-left" style="margin-left:-15 ;">
                    <h6><?= $customer['no_services'] ?></h6>
                    <h6><?= $bill ?> Bulan</h6>
                    <h6><?= date('d-m-Y') ?></h6>

                </div>
            </div>
            <br>
            <div class="row justify-content-center mb-2">
                <h5>Rekap Tagihan</h5>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th style="text-align: center; width:10px">No</th>
                        <th style="text-align: center">Periode Tagihan</th>
                        <th style="text-align: center">Tagihan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no_services = $customer['no_services'] ?>
                    <?php $query = "SELECT *
                                    FROM `invoice`
                                        WHERE `invoice`.`no_services` = $no_services and `invoice`.`status` = 'Belum Bayar'";
                    $querying = $this->db->query($query)->result();
                    ?>

                    <?php


                    $totalcu = 0;
                    foreach ($querying as  $dataa)
                        $totalcu += (int) $dataa->code_unique;
                    ?>
                    <?php $totaldisc = 0;
                    foreach ($querying as  $dataa)
                        $totaldisc += (int) $dataa->disc_coupon;
                    ?>
                    <?php $totalamount = 0;
                    foreach ($querying as  $dataa)
                        $totalamount += (int) $dataa->amount;
                    ?>
                    <?php $no = 1;
                    foreach ($querying as  $dataa) :
                    ?>
                        <tr>
                            <td style="text-align: center;"><?= $no++ ?></td>
                            <td><?= indo_month($dataa->month) ?> <?= $dataa->year ?> </td>
                            <?php $query = "SELECT *
                                    FROM `invoice`
                                        WHERE `invoice`.`no_services` = $no_services and `invoice`.`status` = 'Belum Bayar'  and `invoice`.`month` = $dataa->month  and `invoice`.`year` = $dataa->year";
                            $amount = $this->db->query($query)->row_array();
                            ?>
                            <td style="text-align: right">
                                <?= indo_currency($amount['amount'] - $amount['disc_coupon']); ?>
                            </td>
                        <?php endforeach ?>
                        </tr>
                </tbody>
                <tr style="text-align: right">
                    <th colspan="2">Total Tagihan</th>

                    <th><?= indo_currency($totalamount - $totaldisc) ?></th>

                </tr>
                </tfoot>
            </table>

            <span style="font-style: italic; ">* Terbilang : <?= to_word($totalamount - $totaldisc) ?> rupiah</span>
            <br>
            <br><b> Cara Pembayaran Bisa Transfer :</b> <br>
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
            <style>
                .container {
                    display: flex;
                    flex-direction: column;
                    height: 20vh;
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

                            <span>Mohon lakukan pembayaran tepat waktu</span>

                        </div>

                    </div>
                </footer>
            </div>
        </div>
    </div>
    <!-- end page -->
    <script src="<?= base_url('assets/') ?>frontend/libraries/jquery/jquery-3.4.1.min.js"></script>
    <script src="<?= base_url('assets/') ?>frontend/libraries/bootstrap/js/bootstrap.js"></script>
</body>

</html>