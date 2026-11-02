<?php
/** @var string $title */
/** @var array<string, mixed> $company */
/** @var array<string, mixed> $user */
/** @var string $contents */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?= $title ?> | <?= $company['company_name'] ?></title>
    <link rel="shortcut icon" href="<?= base_url('assets/images/favicon.png') ?>">
    <!-- Custom fonts for this template-->
    <link href="<?= base_url('assets/backend/') ?>vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="<?= base_url('assets/backend/') ?>css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css">
    <!-- Custom styles for this page -->

    <link href="<?= base_url('assets/backend/') ?>vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/backend/') ?>css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/backend/') ?>vendor/datatables/responsive.bootstrap4.min.css">
    <!-- bootstrap datepicker -->
    <link rel="stylesheet" href="<?= base_url('assets/backend') ?>/bootstrap-datepicker/css/bootstrap-datepicker.min.css">
    <style>
        .switch {
            display: inline-block;
            height: 34px;
            position: relative;
            width: 60px;

        }

        .switch input {
            display: none;
        }

        .slider {
            background-color: #aab3c5;
            bottom: 0;
            cursor: pointer;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            transition: .4s;
        }

        .slider:before {
            background-color: #394059;
            bottom: 4px;
            content: "";
            height: 26px;
            left: 4px;
            position: absolute;
            transition: .4s;
            width: 26px;
        }

        input:checked+.slider {
            background-color: #01BFD7;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>
    <style>
        /* navbar */
        .navbar-nav {
            background-color: #F8F8F8;
            border-color: #E7E7E7;
        }
    </style>
    <style>
        :root {
            --ui-slate: #394059;
            --ui-cyan: #01BFD7;
            --ui-green: #46FF23;
            --ui-surface: #F4F7FC;
            --ui-muted: #697386;
        }

        body {
            color: var(--ui-slate);
            background-color: var(--ui-surface);
        }

        #content-wrapper,
        #content {
            background-color: var(--ui-surface);
        }

        .navbar-nav {
            background-color: transparent;
            border-color: transparent;
        }

        #accordionSidebar {
            background-color: var(--ui-slate) !important;
            background-image: none !important;
            box-shadow: 4px 0 18px rgba(28, 35, 54, 0.12);
        }

        #accordionSidebar .sidebar-brand {
            color: #fff;
            min-height: 4.5rem;
        }

        #accordionSidebar .sidebar-brand-icon {
            color: var(--ui-cyan);
        }

        #accordionSidebar .sidebar-divider {
            border-top-color: rgba(244, 247, 252, 0.16);
        }

        #accordionSidebar .nav-item .nav-link {
            color: rgba(244, 247, 252, 0.82);
            margin: 0.15rem 0.65rem;
            padding: 0.8rem 0.85rem;
            border-radius: 0.55rem;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        #accordionSidebar .nav-item .nav-link i {
            color: var(--ui-cyan);
        }

        #accordionSidebar .nav-item .nav-link:hover,
        #accordionSidebar .nav-item.active .nav-link {
            color: var(--ui-slate);
            background-color: var(--ui-surface);
        }

        #accordionSidebar .nav-item .nav-link:hover i,
        #accordionSidebar .nav-item.active .nav-link i {
            color: #087f90;
        }

        #accordionSidebar .collapse-inner {
            background-color: #eef3fa !important;
        }

        #accordionSidebar .collapse-item {
            color: var(--ui-slate);
            border-radius: 0.35rem;
        }

        #accordionSidebar .collapse-item:hover,
        #accordionSidebar .collapse-item.active {
            color: var(--ui-slate);
            background-color: rgba(1, 191, 215, 0.18);
        }

        .topbar {
            background-color: #fff !important;
            box-shadow: 0 3px 16px rgba(57, 64, 89, 0.08) !important;
        }

        .topbar h5 {
            color: var(--ui-slate);
            font-weight: 700;
        }

        #time,
        .text-gray-500,
        .text-gray-600 {
            color: var(--ui-muted) !important;
        }

        .card {
            border: 1px solid #e5eaf2;
            border-radius: 0.7rem;
            box-shadow: 0 5px 18px rgba(57, 64, 89, 0.07);
        }

        .card-header {
            color: var(--ui-slate);
            background-color: #fff;
            border-bottom: 1px solid #e8edf4;
            font-weight: 700;
        }

        .table {
            color: var(--ui-slate);
        }

        .table thead th {
            color: var(--ui-slate);
            background-color: #eef3fa;
            border-bottom: 2px solid #dce5ef;
        }

        .table td,
        .table th {
            border-color: #e5eaf2;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(1, 191, 215, 0.07);
        }

        .form-control,
        .custom-select {
            color: var(--ui-slate);
            border-color: #d7dfeb;
            border-radius: 0.45rem;
        }

        .form-control:focus,
        .custom-select:focus {
            color: var(--ui-slate);
            border-color: var(--ui-cyan);
            box-shadow: 0 0 0 0.2rem rgba(1, 191, 215, 0.18);
        }

        .btn {
            border-radius: 0.45rem;
            font-weight: 600;
            transition: filter 0.2s ease, transform 0.2s ease;
        }

        .btn:hover {
            filter: brightness(0.96);
            transform: translateY(-1px);
        }

        .btn-primary,
        .bg-primary,
        .badge-primary,
        .page-item.active .page-link,
        .navbar.bg-primary {
            color: var(--ui-slate) !important;
            background-color: var(--ui-cyan) !important;
            background-image: none !important;
            border-color: var(--ui-cyan) !important;
        }

        .btn-success,
        .bg-success,
        .badge-success {
            color: var(--ui-slate) !important;
            background-color: var(--ui-green) !important;
            border-color: var(--ui-green) !important;
        }

        .btn-info,
        .bg-info,
        .badge-info {
            color: var(--ui-slate) !important;
            background-color: var(--ui-cyan) !important;
            border-color: var(--ui-cyan) !important;
        }

        a {
            color: #087f90;
        }

        a:hover {
            color: var(--ui-slate);
        }

        .page-link {
            color: var(--ui-slate);
            border-color: #dce5ef;
        }

        .page-link:hover {
            color: var(--ui-slate);
            background-color: rgba(1, 191, 215, 0.14);
            border-color: #c5e9ee;
        }

        .dropdown-menu,
        .modal-content {
            border: 1px solid #e5eaf2;
            border-radius: 0.65rem;
            box-shadow: 0 8px 24px rgba(57, 64, 89, 0.12);
        }

        .dropdown-header {
            color: var(--ui-slate);
            background-color: var(--ui-surface);
        }

        .dropdown-item {
            color: var(--ui-slate);
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            color: var(--ui-slate);
            background-color: rgba(1, 191, 215, 0.12);
        }

        .sticky-footer {
            background-color: var(--ui-surface);
            color: var(--ui-muted);
        }

        .scroll-to-top {
            background-color: var(--ui-slate);
        }

        .scroll-to-top:hover {
            background-color: #252c40;
        }

        input:checked + .slider {
            background-color: #01BFD7;
        }

        @media (max-width: 767.98px) {
            #accordionSidebar .nav-item .nav-link {
                margin-right: 0.45rem;
                margin-left: 0.45rem;
            }
        }
    </style>
</head>

<body id="page-top">

    <?php if ($user['email'] == '') {
        redirect('auth/logout');
    } ?>
    <?php if ($company['isolir'] == 1) { ?>
        <?php if ($user['email'] != 'ginginabdulgoni@gmail.com' && $user['role_id'] == 1) { ?>
            <?php if (date('Y-m-d') >= $company['expired']) {
                redirect('expired');
            } ?>
        <?php } ?>

        <?php if ($user['email'] != 'ginginabdulgoni@gmail.com' && $user['role_id'] != 1) { ?>
            <?php if (date('Y-m-d') >= $company['expired']) {
                redirect('front/maintenance');
            } ?>
        <?php } ?>
    <?php } ?>
    <?php if ($user['email'] != 'ginginabdulgoni@gmail.com') {
        if ($company['maintenance'] == 1) {
            # code...
            redirect('front/maintenance');
        }
    } ?>
    <?php if ($user['role_id'] == 2) {

        redirect('member');
    } ?>

    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php $role = $this->db->get_where('role_management', ['role_id' => $this->session->userdata('role_id')])->row_array() ?>
        <?php $menu = $this->db->get_where('role_menu', ['role_id' => $this->session->userdata('role_id')])->row_array() ?>
        <?php $wa = $this->db->get('whatsapp')->row_array() ?>
        <?php $pg = $this->db->get('payment_gateway')->row_array() ?>
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary  sidebar sidebar-dark accordion" id="accordionSidebar">
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= site_url('dashboard') ?>">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-wifi"></i>
                </div>
                <div class="sidebar-brand-text mx-3"><?= $company['apps_name']; ?></div>

            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">
            <!-- Nav Item - Dashboard -->
            <li class="nav-item <?= $title == 'Dashboard'  ? 'active' : '' ?>">
                <a class="nav-link" href="<?= site_url('dashboard') ?>">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Beranda</span></a>
            </li>
            <?php if ($this->session->userdata('role_id') == 1 or $menu['services_menu'] == 1) { ?>
                <li class="nav-item <?= $title == 'Item Package' | $title == 'Category Package'  ? 'active' : '' ?>">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                        <i class="fas fa-fw fa-sitemap"></i>
                        <span>Layanan</span>
                    </a>
                    <div id="collapseTwo" class="collapse <?= $title == 'Item Package' | $title == 'Category Package' ? 'show' : '' ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['services_item'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Item Package'  ? 'active' : '' ?>" href="<?= site_url('package/item') ?>">Paket</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['services_category'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Category Package'  ? 'active' : '' ?>" href="<?= site_url('package/category') ?>">Kategori</a>
                            <?php } ?>
                        </div>
                    </div>
                </li>
            <?php } ?>
            <!-- Nav Item - Pages Collapse Menu -->
            <?php if ($this->session->userdata('role_id') == 1 or $menu['customer_menu'] == 1) { ?>
                <li class="nav-item <?= $title == 'Customer' | $title == 'Isolir' | $title == 'Whatsapp Pelanggan' | $title == 'Add Customer' | $title == 'Free' | $title == 'Aktif' | $title == 'Non-Aktif' | $title == 'Waiting'   ? 'active' : '' ?>">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCustomer" aria-expanded="true" aria-controls="collapseTwo">
                        <i class="fas fa-fw fa-users"></i>
                        <span>Pelanggan</span>
                    </a>
                    <div id="collapseCustomer" class="collapse <?= $title == 'Aktif' | $title == 'Isolir' | $title == 'Add Customer' | $title == 'Free' | $title == 'Whatsapp Pelanggan' | $title == 'Non-Aktif' | $title == 'Maps' | $title == 'Customer' | $title == 'Waiting' ? 'show' : '' ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">

                            <?php if ($this->session->userdata('role_id') == 1 or $role['add_customer'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Add Customer'  ? 'active' : '' ?>" href="<?= site_url('customer/add') ?>">Tambah</a>
                            <?php } ?>
                       
                      
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['customer'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Customer'  ? 'active' : '' ?>" href="<?= site_url('customer') ?>">Semua</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['customer_whatsapp'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Kirim Whatsapp'  ? 'active' : '' ?>" href="<?= site_url('customer/whatsapp') ?>">Kirim Whatsapp</a>
                            <?php } ?>

                        </div>
                    </div>
                </li>
            <?php } ?>
            <?php if ($this->session->userdata('role_id') == 1 or $menu['coverage_menu'] == 1) { ?>
                <li class="nav-item <?= $title == 'Coverage Area' | $title == 'Coverage Maps' | $title == 'Add Coverage'  ? 'active' : '' ?>">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsecoverage" aria-expanded="true" aria-controls="collapseTwo">
                        <i class="fas fa-fw fa-sitemap"></i>
                        <span>Coverage</span>
                    </a>
                    <div id="collapsecoverage" class="collapse <?= $title == 'Coverage Area' | $title == 'Coverage Maps' | $title == 'Add Coverage' ? 'show' : '' ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['coverage_add'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Add Coverage'  ? 'active' : '' ?>" href="<?= site_url('coverage/add') ?>">Tambah</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['coverage'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Coverage Area'  ? 'active' : '' ?>" href="<?= site_url('coverage') ?>">Daftar Coverage</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['coverage_maps'] == 1) { ?>
                                <!-- <a class="collapse-item <?= $title == 'Coverage Maps'  ? 'active' : '' ?>" href="<?= site_url('coverage/maps') ?>">Maps Area</a> -->
                            <?php } ?>
                        </div>
                    </div>
                </li>
            <?php } ?>

            <?php if ($this->session->userdata('role_id') == 1 or $menu['bill_menu'] == 1) { ?>
                <li class="nav-item <?= $title == 'Belum Bayar' | $title == 'Sudah Bayar' | $title == 'Sharing Profit' |  $title == 'Jatuh Tempo' | $title == 'Bill'  ? 'active' : '' ?>">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#tagihan" aria-expanded="true" aria-controls="collapseTwo">
                        <i class="fas fa-fw fa-tasks"></i>
                        <span>Tagihan</span>
                    </a>
                    <div id="tagihan" class="collapse <?= $title == 'Belum Bayar' |  $title == 'Sudah Bayar' | $title == 'Sharing Profit' |  $title == 'Jatuh Tempo' | $title == 'Konfirmasi Pembayaran' |  $title == 'Tunggakan' |  $title == 'Hapus Tagihan' | $title == 'Bill Draf' | $title == 'Bill' ? 'show' : '' ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['bill_unpaid'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Belum Bayar'  ? 'active' : '' ?>" href="<?= site_url('bill/unpaid') ?>">Belum Bayar</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['bill_paid'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Sudah Bayar'  ? 'active' : '' ?>" href="<?= site_url('bill/paid') ?>">Sudah Bayar</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['bill_due_date'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Jatuh Tempo'  ? 'active' : '' ?>" href="<?= site_url('bill/duedate') ?>">Jatuh Tempo</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['bill'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Bill'  ? 'active' : '' ?>" href="<?= site_url('bill') ?>">Semua</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['bill_draf'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Bill Draf'  ? 'active' : '' ?>" href="<?= site_url('bill/draf') ?>">Tagihan Bulan Ini <sup style="color: red;">Draf</sup></a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['bill_debt'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Tunggakan'  ? 'active' : '' ?>" href="<?= site_url('bill/debt') ?>">Tunggakan</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['bill_confirm'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Konfirmasi Pembayaran'  ? 'active' : '' ?>" href="<?= site_url('confirm') ?>">Konfirmasi Pembayaran</a>
                            <?php } ?>

                            <?php if ($this->session->userdata('role_id') == 1 or $menu['bill_history'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Bill History'  ? 'active' : '' ?>" href="<?= site_url('bill/history') ?>">Riwayat Tagihan</a>
                            <?php } ?>


                            <?php if ($pg['vendor'] == 'Tripay') { ?>
                                <?php if ($pg['is_active'] == 1) { ?>
                                    <?php if ($this->session->userdata('role_id') == 1 or $menu['bill_code_coupon'] == 1) { ?>
                                        <a class="collapse-item <?= $title == 'Kode Kupon'  ? 'active' : '' ?>" href="<?= site_url('coupon') ?>">Kode Kupon</a>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['bill_delete'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Hapus Tagihan'  ? 'active' : '' ?>" href="" data-toggle="modal" data-target="#confirmdelbill"><span style="color: red;">Hapus Tagihan</span></a>
                            <?php } ?>
                            <!-- <?php if ($this->session->userdata('role_id') == 1 or $menu['bill_sharing_profit'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Sharing Profit'  ? 'active' : '' ?>" href="<?= site_url('bill/recapmitra') ?>">Sharing Profit</a>
                            <?php } ?> -->
                        </div>
                    </div>
                </li>
            <?php } ?>

            <?php if ($this->session->userdata('role_id') == 1 or $menu['finance_menu'] == 1) { ?>
                <li class="nav-item <?= $title == 'Income' | $title == 'Expenditure' | $title == 'Report'  ? 'active' : '' ?>">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReport" aria-expanded="true" aria-controls="collapseTwo">
                        <i class="fas fa-fw fa-dollar-sign"></i>
                        <span>Keuangan</span>
                    </a>
                    <div id="collapseReport" class="collapse <?= $title == 'Income' | $title == 'Expenditure' | $title == 'Report' ? 'show' : '' ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['finance_income'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Income'  ? 'active' : '' ?>" href="<?= site_url('income') ?>">Pemasukan</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['finance_expend'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Expenditure'  ? 'active' : '' ?>" href="<?= site_url('expenditure') ?>">Pengeluaran</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['finance_report'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Report'  ? 'active' : '' ?>" href="<?= site_url('income/kas') ?>">Laporan Keuangan</a>
                            <?php } ?>
                        </div>
                    </div>
                </li>
            <?php } ?>
            <?php if ($this->session->userdata('role_id') == 1 or $menu['help_menu'] == 1) { ?>
                <li class="nav-item <?= $title == 'Data Help'  | $title == 'Help Setting' ? 'active' : '' ?>">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsehelp" aria-expanded="true" aria-controls="collapseTwo">
                        <i class="fas fa-fw fa-info-circle"></i>
                        <span>Bantuan</span>
                    </a>
                    <div id="collapsehelp" class="collapse <?= $title == 'Data Help'  | $title == 'Help Setting' ? 'show' : '' ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['help'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Data Help'  ? 'active' : '' ?>" href="<?= site_url('help/data') ?>">Tiket</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['help_category'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Help Setting'  ? 'active' : '' ?>" href="<?= site_url('help/setting') ?>">Kategori & Solusi</a>
                            <?php } ?>
                        </div>
                    </div>
                </li>
            <?php } ?>
            <?php if ($this->session->userdata('role_id') == 1 or $menu['master_menu'] == 1) { ?>

                <li class="nav-item <?= $title == 'Odc' | $title == 'Odp'    ? 'active' : '' ?>">

                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsmaster" aria-expanded="true" aria-controls="collapseTwo">

                        <i class="fas fa-fw fa-cube"></i>

                        <span>Master</span>

                    </a>

                    <div id="collapsmaster" class="collapse <?= $title == 'Odc' | $title == 'Odp'    ? 'show' : '' ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">

                        <div class="bg-white py-2 collapse-inner rounded">
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['master_odc'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Odc'  ? 'active' : '' ?>" href="<?= site_url('odc') ?>">ODC</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['master_odp'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Odp'  ? 'active' : '' ?>" href="<?= site_url('odp') ?>">ODP</a>
                            <?php } ?>


                        </div>

                    </div>

                </li>

            <?php } ?>


            <?php if ($this->session->userdata('role_id') == 1 or $menu['router_menu'] == 1) { ?>

                <li class="nav-item <?= $title == 'Routers' | $title == 'Customer Mikrotik' | $title == 'Schedule' | $title == 'Usage'  ? 'active' : '' ?>">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseRouter" aria-expanded="true" aria-controls="collapseTwo">
                        <i class="fas fa-fw fa-wifi"></i>
                        <span>Router</span>
                    </a>
                    <div id="collapseRouter" class="collapse <?= $title == 'Routers' | $title == 'Customer Mikrotik' | $title == 'Schedule' | $title == 'Usage' ? 'show' : '' ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['router'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Routers'  ? 'active' : '' ?>" href="<?= site_url('router') ?>">Routers</a>
                            <?php } ?>
                            <?php $rt = $this->db->get_where('router', ['id' => 1])->row_array() ?>
                            <?php if ($rt['is_active'] == 0) { ?>
                                <?php if ($this->session->userdata('role_id') == 1 or $menu['router_schedule'] == 1) { ?>
                                    <a class="collapse-item <?= $title == 'Schedule'  ? 'active' : '' ?>" href="<?= site_url('setting/schedule') ?>">Shedule</a>
                                <?php } ?>
                            <?php } ?>
                            <?php if ($rt['is_active'] == 1) { ?>
                                <?php if ($this->session->userdata('role_id') == 1 or $menu['router_customer'] == 1) { ?>
                                    <a class="collapse-item <?= $title == 'Customer Mikrotik'  ? 'active' : '' ?>" href="<?= site_url('mikrotik/customer') ?>">Customer</a>
                                <?php } ?>
                                <?php if ($this->session->userdata('role_id') == 1 or $menu['router_customer'] == 1) { ?>
                                    <a class="collapse-item <?= $title == 'Usage'  ? 'active' : '' ?>" href="<?= site_url('mikrotik/usage') ?>">Pemakaian Internet</a>
                                <?php } ?>
                                <?php if ($this->session->userdata('role_id') == 1 or $menu['router_schedule'] == 1) { ?>
                                    <a class="collapse-item <?= $title == 'Schedule'  ? 'active' : '' ?>" href="<?= site_url('mikrotik/setting') ?>">Shedule</a>
                                <?php } ?>
                                <?php $other = $this->db->get('other')->row_array() ?>
                                <a class="collapse-item <?= $title == 'Schedule'  ? 'active' : '' ?>" href="<?= site_url('front/monitoring/' . $other['key_apps']) ?>" target="blank">Monitoring</a>

                            <?php } ?>

                        </div>
                    </div>
                </li>

            <?php } ?>

            <!-- Divider -->


            <!-- Divider -->
            <?php if ($this->session->userdata('role_id') == 1 or $menu['user_menu'] == 1) { ?>


                <li class="nav-item <?= $title == 'User' | $title == 'Admin' | $title == 'Mitra' | $title == 'Pelanggan' | $title == 'Operator' | $title == 'Teknisi' ? 'active' : '' ?>">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsuser" aria-expanded="true" aria-controls="collapseTwo">
                        <i class="fas fa-fw fa-users"></i>
                        <span>Pengguna</span>
                    </a>
                    <div id="collapsuser" class="collapse <?= $title == 'User' | $title == 'Admin' | $title == 'Mitra' | $title == 'Pelanggan' | $title == 'Operator' | $title == 'Teknisi' ? 'show' : '' ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['user_add'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Add User'  ? 'active' : '' ?>" href="<?= site_url('user/register') ?>">Tambah</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['user_admin'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Admin'  ? 'active' : '' ?>" href="<?= site_url('user/admin') ?>">Administrator</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['user_customer'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Pelanggan'  ? 'active' : '' ?>" href="<?= site_url('user/customer') ?>">Pelanggan</a>
                            <?php } ?>

                            <?php if ($this->session->userdata('role_id') == 1 or $menu['user_operator'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Operator'  ? 'active' : '' ?>" href="<?= site_url('user/operator') ?>">Operator</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['user_teknisi'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Teknisi'  ? 'active' : '' ?>" href="<?= site_url('user/teknisi') ?>">Teknisi</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['user'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'User'  ? 'active' : '' ?>" href="<?= site_url('user') ?>">Semua</a>
                            <?php } ?>
                        </div>
                    </div>
                </li>
            <?php } ?>

            <?php if ($this->session->userdata('role_id') == 1 or $menu['integration_menu'] == 1) { ?>
                <li class="nav-item <?= $title == 'Whatsapp'  | $title == 'OLT' | $title == 'BRI API'  | $title == 'Payment Gateway' | $title == 'Bot Telegram' | $title == 'OLT' | $title == 'SMS Gateway'  | $title == 'Email' | $title == 'Olt' | $title == 'Radius' ? 'active' : '' ?>">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapintegration" aria-expanded="true" aria-controls="collapseTwo">
                        <i class="fas fa-fw fa-cog"></i>
                        <span>Integrasi</span>
                    </a>
                    <div id="collapintegration" class="collapse <?= $title == 'Whatsapp'  | $title == 'OLT' | $title == 'BRI API'  | $title == 'Payment Gateway' | $title == 'Bot Telegram' | $title == 'OLT' | $title == 'SMS Gateway'  | $title == 'Email' | $title == 'Olt' | $title == 'Radius'  ? 'show' : '' ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['integration_email'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Email'  ? 'active' : '' ?>" href="<?= site_url('setting/email') ?>">Email</a>
                            <?php } ?>

                            <?php if ($this->session->userdata('role_id') == 1 or $menu['integration_payment_gateway'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Payment Gateway'  ? 'active' : '' ?>" href="<?= site_url('payment') ?>">Payment Gateway</a>
                            <?php } ?>

                            <?php if ($this->session->userdata('role_id') == 1 or $menu['integration_whatsapp'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Whatsapp'  ? 'active' : '' ?>" href="<?= site_url('whatsapp') ?>">Whatsapp Gateway</a>
                            <?php } ?>

                            <?php if ($this->session->userdata('role_id') == 1 or $menu['integration_telegram'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Bot Telegram'  ? 'active' : '' ?>" href="<?= site_url('setting/bottelegram') ?>">Telegram</a>
                            <?php } ?>

                        </div>
                    </div>
                </li>
            <?php } ?>
            <?php if ($this->session->userdata('role_id') == 1 or $menu['setting_menu'] == 1) { ?>
                <li class="nav-item <?= $title == 'Setting'  | $title == 'Syarat dan Ketentuan' |  $title == 'Logs'  | $title == 'About' | $title == 'Backup' ? 'active' : '' ?>">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSetting" aria-expanded="true" aria-controls="collapseTwo">
                        <i class="fas fa-fw fa-cog"></i>
                        <span>Pengaturan</span>
                    </a>
                    <div id="collapseSetting" class="collapse <?= $title == 'Setting'  | $title == 'Syarat dan Ketentuan' |  $title == 'Logs'  | $title == 'About' | $title == 'Backup'  ? 'show' : '' ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['setting_company'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Setting'  ? 'active' : '' ?>" href="<?= site_url('setting') ?>">Perusahaan</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['setting_about_company'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'About'  ? 'active' : '' ?>" href="<?= site_url('setting/about') ?>">Tentang Perusahaan</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['setting_bank_account'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Bank'  ? 'active' : '' ?>" href="<?= site_url('setting/bank') ?>">Rekening Bank</a>
                            <?php } ?>

                            <?php if ($this->session->userdata('role_id') == 1 or $menu['setting_terms_condition'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Syarat dan Ketentuan'  ? 'active' : '' ?>" href="<?= site_url('setting/terms') ?>">Syarat dan Ketentuan</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['setting_privacy_policy'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Kebijakan Privasi'  ? 'active' : '' ?>" href="<?= site_url('setting/policy') ?>">Kebijakan Privasi</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['setting_logs'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Logs'  ? 'active' : '' ?>" href="<?= site_url('logs') ?>">Logs</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['setting_backup'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Backup'  ? 'active' : '' ?>" href="<?= site_url('backup') ?>">Backup</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['setting_other'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Lainnya'  ? 'active' : '' ?>" href="<?= site_url('setting/other') ?>">Lainnya</a>
                            <?php } ?>

                        </div>
                    </div>
                </li>
            <?php } ?>

            <?php if ($this->session->userdata('role_id') == 1 or $menu['role_menu'] == 1) { ?>


                <li class="nav-item <?= $title == 'Role' | $title == 'Role Menu' ? 'active' : '' ?>">
                    <a class=" nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsrole" aria-expanded="true" aria-controls="collapseTwo">
                        <i class="fas fa-fw fa-cog"></i>
                        <span>Role Management</span>
                    </a>
                    <div id="collapsrole" class="collapse  <?= $title == 'Role' | $title == 'Role Menu' ? 'show' : '' ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['role_access'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Role'  ? 'active' : '' ?>" href="<?= site_url('role') ?>">Access</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['role_sub_menu'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Role Menu'  ? 'active' : '' ?>" href="<?= site_url('role/menu') ?>">Menu</a>
                            <?php } ?>
                        </div>
                    </div>
                </li>
            <?php } ?>
            <?php if ($this->session->userdata('role_id') == 1 or $menu['website_menu'] == 1) { ?>
                <li class="nav-item <?= $title == 'Slide' | $title == 'Product' ? 'active' : '' ?>">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseweb" aria-expanded="true" aria-controls="collapseTwo">
                        <i class="fas fa-fw fa-info-circle"></i>
                        <span>Webiste</span>
                    </a>
                    <div id="collapseweb" class="collapse <?= $title == 'Slide' | $title == 'Product' ? 'show' : '' ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['website_slide'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Slide'  ? 'active' : '' ?>" href="<?= site_url('slider') ?>">Slide Show</a>
                            <?php } ?>
                            <?php if ($this->session->userdata('role_id') == 1 or $menu['website_product'] == 1) { ?>
                                <a class="collapse-item <?= $title == 'Product'  ? 'active' : '' ?>" href="<?= site_url('product/data') ?>">Produk / Jasa</a>
                            <?php } ?>
                        </div>
                    </div>
                </li>
            <?php } ?>
            <?php if ($this->session->userdata('role_id') == 1) { ?>
                <!-- <li class="nav-item <?= $title == 'Changelog'  ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('about/changelog') ?>">
                        <i class="fas fa-fw fa-info-circle"></i>
                        <span>Changelog</span></a>
                </li> -->
            <?php } ?>
            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Search -->
                    <form class=" form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search d-none d-sm-block d-md-block">
                        <h5>
                            <?= $company['company_name'] ?>
                        </h5>

                    </form>

                    <div class="d-none d-sm-block d-md-block">
                        <p id="time"></p>
                    </div>
                    <!-- Topbar Navbar -->

                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- Counter - Alerts -->
                                <span class="badge badge-danger badge-counter"><?= $this->db->get_where('confirm_payment', ['status' => 'Pending'])->num_rows() ?></span>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Menunggu Konfirmasi Pembayaran
                                </h6>
                                <?php $query = "SELECT *
                                    FROM `confirm_payment`
                                    WHERE `status` =  'Pending'";
                                $pendingConfirm = $this->db->query($query)->result(); ?>
                                <?php foreach ($pendingConfirm as $data) : ?>
                                    <a class="dropdown-item d-flex align-items-center" href="<?= site_url('bill/confirmdetail/' . $data->invoice_id) ?>">
                                        <div>
                                            <?php $Customer = $this->db->get_where('customer', ['no_services' => $data->no_services])->row_array(); ?>
                                            <?php $bill = $this->db->get_where('invoice', ['no_services' => $data->no_services, 'invoice' => $data->invoice_id])->row_array(); ?>
                                            <span class="font-weight-bold"><?= $Customer['name'] ?> - <?= $data->no_services ?></span>
                                            <div class="small text-gray-500">#<?= $data->invoice_id ?> Periode <?= indo_month($bill['month']) ?> <?= $bill['year'] ?></div>
                                        </div>
                                    </a>
                                <?php endforeach ?>
                                <a class="dropdown-item text-center small text-gray-500" href="<?= site_url('confirm') ?>">Tampilkan Semua</a>
                            </div>
                        </li>

                        <!-- Nav Item - Messages -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-users fa-fw"></i>
                                <!-- Counter - Messages -->
                                <!-- Mitra -->
                                <?php if ($this->session->userdata('role_id') == 4) { ?>
                                    <?php $Capel = $this->db->get_where('customer', ['c_status' => 'Menunggu', 'mitra' => $this->session->userdata('id')]); ?>
                                <?php } ?>
                                <!-- Admin -->
                                <?php if ($this->session->userdata('role_id') == 1) { ?>
                                    <?php $Capel = $this->db->get_where('customer', ['c_status' => 'Menunggu']); ?>
                                <?php } ?>
                                <?php if ($this->session->userdata('role_id') == 5) { ?>
                                    <?php $Capel = $this->db->get_where('customer', ['c_status' => 'Menunggu']); ?>
                                <?php } ?>
                                <!-- Operator -->
                                <?php if ($this->session->userdata('role_id') == 3) { ?>

                                    <?php $Capel = $this->db->get_where('customer', ['c_status' => 'Menunggu']); ?>

                                <?php } ?>
                                <span class="badge badge-danger badge-counter"><?= $Capel->num_rows(); ?></span>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="messagesDropdown">
                                <h6 class="dropdown-header">
                                    Calon Pelanggan Baru
                                </h6>
                                <?php foreach ($Capel->result() as $capel) { ?>
                                    <a class="dropdown-item d-flex align-items-center" href="<?= site_url('customer/edit/' . $capel->customer_id) ?>">

                                        <div class="font-weight-bold">
                                            <div class="text-truncate"><?= $capel->name; ?></div>
                                            <div class="small text-gray-500"><?= date('d F Y', $capel->created); ?></div>
                                        </div>
                                    </a>
                                <?php } ?>


                                <a class="dropdown-item text-center small text-gray-500" href="<?= site_url('customer/wait') ?>">Tampilkan Semua</a>
                            </div>
                        </li>
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= $user['name']; ?></span>
                                <img class="img-profile rounded-circle" src="<?= base_url(''); ?>assets/images/profile/<?= $user['image']; ?>" alt="">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="<?= site_url('user/profile') ?>">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Akun
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Keluar
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->
                <!-- Bootstrap core JavaScript-->
                <script src="<?= base_url('assets/backend/') ?>vendor/jquery/jquery.min.js"></script>
                <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9.10.4/dist/sweetalert2.all.min.js"></script> -->
                <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <!-- Begin Page Content -->

                <div class="container-fluid">
                    <?php
                    $exp = $company['expired'];
                    $beforeexp = date('Y-m-d', strtotime('-5 day', strtotime($exp)));
                    ?>
                    <?php if ($company['isolir'] == 1) { ?>
                        <!-- <?= $beforeexp; ?> -->
                        <?php if ($beforeexp <= date('Y-m-d') && $role['role_id'] == 1) { ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i class="icon fa fa-bell"></i> Billing My-Wifi akan expired pada tanggal <?= indo_date($company['expired']); ?> <span id="countdown"></span>, silahkan lakukan perpanjangan sebelum terisolir, Update Paket Billing My-Wifi di <a href="https://mywifi.web.id" target="blnak">mywifi.web.id</a>.
                                <?php $date = date_create($company['expired']);
                                $expired = date_format($date, 'M d, Y H:i:s'); ?>
                                <input type="hidden" id="countdownexpired" value="<?= $expired ?>">
                            </div>
                        <?php } ?>
                    <?php } ?>

                    <?= $contents ?>

                </div>
                <div class="container">
                    <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds. </p>
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->
            <div class="modal fade" id="confirmdelbill" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Konfirmasi Password</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="<?= site_url('bill/confirmdelbill') ?>" method="POST">

                                <div class="form-group">
                                    <label for="password">Masukan Ulang Password Login Anda</label>
                                    <input type="password" name="password" id="password" class="form-control" autocapitalize="off" required>
                                </div>
                        </div>
                        <div class="modal-footer">

                            <button type="submit" class="btn btn-primary">Go</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Footer -->
            <footer class="sticky-footer">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; <?= $company['company_name'] ?> <?= date('Y') ?></span>
                        <div class="text-right">
                            <?php if ($company['isolir'] == 1) { ?>
                                Expired : <?= indo_date($company['expired']); ?>
                            <?php } ?>
                            <?php if ($user['email'] == 'ginginabdulgoni@gmail.com') { ?>
                                <a data-toggle="modal" data-target="#expired">
                                    <i class="fas fa-fw fa-cog"></i>
                                </a>
                                <?php
                                $ip = gethostbyname("$_SERVER[HTTP_HOST]");
                                echo $ip;
                                ?>
                            <?php } ?>

                        </div>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->
    <!-- Modal -->

    <!-- Scroll to Top Button-->
    <style>
        .scroll-to-top {
            position: fixed;
            left: 50%;
            right: 50%;
            bottom: 4rem;
            display: none;
            width: 2.75rem;
            height: 2.75rem;
            text-align: center;
            color: #fff;
            background: rgba(90, 92, 105, 0.5);
            line-height: 46px;
        }

        .scroll-to-top:focus,
        .scroll-to-top:hover {
            color: white;
        }

        .scroll-to-top:hover {
            background: #5a5c69;
        }

        .scroll-to-top i {
            font-weight: 800;
        }
    </style>
    <a class="scroll-to-top rounded" href="#page-top" title="Back To Top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <h5 class="modal-title" id="exampleModalLabel">Apakah anda yakin ?</h5>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="<?= site_url('auth/logout') ?>">Keluar</a>
                </div>
            </div>
        </div>
    </div>
    <?php if ($this->session->userdata('role_id') == 1) { ?>
        <nav class="navbar  bg-primary navbar-expand d-md-none d-lg-none d-xl-none fixed-bottom">
            <ul class="navbar-nav nav-justified w-100">
                <li class="nav-item">
                    <a href="<?= site_url('dashboard') ?>" class="nav-link <?= $title == 'Dashboard'  ? 'active' : '' ?>"> <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-house" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M2 13.5V7h1v6.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V7h1v6.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5zm11-11V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z" />
                            <path fill-rule="evenodd" d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z" />
                        </svg></a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('customer/active') ?>" title="Pelanggan" class="nav-link <?= $title == 'Aktif'  ? 'active' : '' ?>"><i class="fa fa-users"></i></a>
                </li>

                <li class="nav-item">
                    <a href="<?= site_url('bill') ?>" class="nav-link <?= $title == 'Bill'  ? 'active' : '' ?>" title="tagihan"><i class="fa fa-credit-card"></i></a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('user/profile') ?>" title="Profile" class="nav-link <?= $title == 'Profile'  ? 'active' : '' ?>"><i class="fa fa-user"></i></a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('setting') ?>" class="nav-link <?= $title == 'Setting'  ? 'active' : '' ?>" title="Pengaturan"><i class="fas fa-fw fa-cog"></i></a>
                </li>
            </ul>
        </nav>
    <?php } ?>
    <div class="modal fade" id="popup" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="spinner-grow text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-grow text-secondary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-grow text-success" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-grow text-danger" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-grow text-warning" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-grow text-info" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-grow text-light" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div class="spinner-grow text-dark" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
    <script src="<?= base_url('assets/backend/') ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>



    <script>
        // Set the date we're counting down to
        var dateexpired = $('#countdownexpired').val();
        var countDownDate = new Date(dateexpired).getTime();

        // Update the count down every 1 second
        var x = setInterval(function() {

            // Get today's date and time
            var now = new Date().getTime();

            // Find the distance between now and the count down date
            var distance = countDownDate - now;

            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Output the result in an element with id="demo"
            document.getElementById("countdown").innerHTML = "( " +
                days + "d " + hours + "h " +
                minutes + "m " + seconds + "s" + " )";

            // If the count down is over, write some text 
            if (distance < 0) {
                clearInterval(x);
                document.getElementById("countdown").innerHTML = "(EXPIRED)";
            }
        }, 1000);
    </script>

    <!-- Custom scripts for all pages-->
    <script src="<?= base_url('assets/backend/') ?>js/sb-admin-2.js"></script>
    <!-- Page level plugins -->
    <script src="<?= base_url('assets/backend/') ?>vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url('assets/backend/') ?>vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <!-- Page level custom scripts -->
    <script src="<?= base_url('assets/backend/') ?>js/demo/datatables-demo.js"></script>
    <script src="<?= base_url('assets/backend/') ?>js/select2.full.min.js"></script>
    <script src="<?= base_url('assets/backend/') ?>moment.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.2.2/js/dataTables.fixedHeader.min.js"></script>
    <script src="<?= base_url('assets/backend/') ?>vendor/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url('assets/backend/') ?>vendor/datatables/responsive.bootstrap4.min.js"></script>
    <!-- bootstrap datepicker -->
    <script src="<?= base_url('assets/backend') ?>/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>


    <script>
        $(document).ready(function() {
            $('#tablebt').DataTable({
                "lengthMenu": [
                    [10, 25, 50, 100, 250, 500, 1000],
                    [10, 25, 50, 100, 250, 500, 1000]
                ],
                dom: 'lBfrtip',
                buttons: [{
                        extend: ['copy'],
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: ['csv'],
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: ['excel'],
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: ['pdf'],
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: ['print'],
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    'colvis'
                ],

            });

        });
    </script>
    <script>
        $(document).ready(function() {
            oTable = jQuery('#dataTableDraf').dataTable({
                "bPaginate": false,
                "bLengthChange": false,
                "searching": true,
                "aoColumns": [{
                        "bSortable": true
                    },
                    {
                        "bSortable": true
                    },
                    {
                        "bSortable": true
                    },
                    {
                        "bSortable": true
                    },
                    {
                        "bSortable": true
                    },
                    {
                        "bSortable": true
                    },

                    {
                        "bSortable": false
                    },
                    {
                        "bSortable": false
                    },

                ]
            });
        })
    </script>


    <script type="text/javascript">
        $(document).ready(function() {
            $("#popup").modal({
                show: false,
                backdrop: 'static'
            });
        });
        $("#click-me").click(function() {
            if (this.form && !this.form.checkValidity()) {
                this.form.reportValidity();
                return;
            }
            $("#popup").modal("show");
            $("#billGenerate").modal("hide");
            $("#DeleteModal").modal("hide");
            $("#ModalBayar").modal("hide");
            $("#exampleModal").modal("hide");
            $("#add").modal("hide");

        });
        $(document).ready(function() {
            if ($(window).width() < 767) {
                $("body").toggleClass("sidebar-toggled");
                $(".sidebar").toggleClass("toggled");
                if ($(".sidebar").hasClass("toggled")) {
                    $('.sidebar .collapse').collapse('hide');
                };
            }
        });
        // document.addEventListener('contextmenu', event => event.preventDefault());
        // document.onkeydown = function(e) {
        //     if (e.ctrlKey &&
        //         (e.keyCode === 67 ||
        //             e.keyCode === 86 ||
        //             e.keyCode === 85 ||
        //             e.keyCode === 117)) {
        //         return false;
        //     } else {
        //         return true;
        //     }
        // };
        // $(document).keypress("u", function(e) {
        //     if (e.ctrlKey) {
        //         return false;
        //     } else {
        //         return true;
        //     }
        // });
        // $(document).keydown(function(event) {
        //     if (event.keyCode == 123) { // Prevent F12
        //         return false;
        //     } else if (event.ctrlKey && event.shiftKey && event.keyCode == 73) { // Prevent Ctrl+Shift+I        
        //         return false;
        //     }
        // });
    </script>

    <script>
        //Date picker
        $('#datepicker').datepicker({
            maxDate: '0',
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
        })
        $('#datepickerdisablefuture').datepicker({
            maxDate: '0',
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            endDate: new Date()
        });
    </script>
    <script>
        console.log('Ini adalah fitur browser yang ditujukan untuk developer. kami tidak bertanggung jawab jika anda mengubah script yg mengakibatkan error, dan mohon untuk tidak menjual kembali source code billing ini, jika terdeteksi maka tidak akan mendapatkan lagi support untuk update !');
        console.log('Ini adalah fitur browser yang ditujukan untuk pengguna. Jika seseorang meminta Anda untuk menyalin-menempel sesuatu di sini untuk mengaktifkan fitur Billing atau "meretas" akun seseorang, ini adalah penipuan dan akan memberikannya akses ke akun Anda.');
    </script>
</body>

</html>
<script>
    var timeDisplay = document.getElementById("time");
    var timezone = '<?= $company['timezone'] ?>'

    function refreshTime() {
        var dateString = new Date().toLocaleString("en-US", {
            timeZone: timezone,
            // weekday: 'short', // long, short, narrow
            // day: 'numeric', // numeric, 2-digit
            // year: 'numeric', // numeric, 2-digit
            // month: 'long', // numeric, 2-digit, long, short, narrow
            // hour: 'numeric', // numeric, 2-digit
            // minute: 'numeric', // numeric, 2-digit
            // second: 'numeric', // numeric, 2-digit
        });

        // console.log(dateString);
        var formattedString = moment(dateString).format('DD-MM-YYYY - H:m:s');
        timeDisplay.innerHTML = formattedString;
    }

    setInterval(refreshTime, 1000);
</script>

<!-- CEK ULANG ISOLIR -->

<?php
if (isset($this->load) && method_exists($this->load, 'model')) {
    if (!isset($this->customer_m) || !is_object($this->customer_m)) {
        $this->load->model('customer_m');
    }
}

$cekcs = (isset($this->customer_m) && is_object($this->customer_m))
    ? $this->customer_m->getisolirpasca()->num_rows()
    : 0;
?>

<?php if ($cekcs > 0) { ?>
    <?php $rt = $this->db->get_where('router', ['id' => 1])->row_array() ?>
    <?php if ($rt['is_active'] == 1) { ?>
        <?php $other = $this->db->get('other')->row_array() ?>


    <?php } ?>
<?php } ?>
<script>
    $(function() {
        //Initialize Select2 Elements
        $('.select2').select2()
    });
</script>
<script>
    function getdetailcustomer() {

        var no_services = $("#no_services").val();

        if (no_services == '') {

            alert('No Layanan Pelanggan belum dipilih');

            $('#no_services').focus();

        } else {
            $.ajax({
                type: 'POST',

                data: "&no_services=" + no_services,

                cache: false,

                url: '<?= site_url('customer/get_data') ?>',

                cache: false,





                beforeSend: function() {

                    $('.loading').html(` <div class="container">

        <div class="text-center">

            <div class="spinner-border text-primary" style="width: 5rem; height: 5rem;" role="status">

                <span class="sr-only">Loading...</span>

            </div>

        </div>

    </div>`);

                },

                success: function(data) {



                    $('.loading').html('');

                    $('.getdatacustomer').html(data);

                    $('#contents').hide();

                }



            });

        }

        return false;

    }
</script>
