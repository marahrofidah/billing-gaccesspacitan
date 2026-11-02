<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $title ?> | <?= $company['company_name'] ?> | <?= $company['sub_name'] ?></title>
    <link href="https://fonts.googleapis.com/css?family=Assistant:200,300,400,600,700,800|Playfair+Display:400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/') ?>frontend/libraries/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="<?= base_url('assets/') ?>frontend/styles/main.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9.10.4/dist/sweetalert2.all.min.js"></script>
    <link rel="shortcut icon" href="<?= base_url('assets/images/favicon.png') ?>">
    <style>
        :root {
            --ui-slate: #394059;
            --ui-cyan: #01BFD7;
            --ui-green: #46FF23;
            --ui-surface: #F4F7FC;
            --ui-muted: #697386;
            --ui-link: #087f90;
        }

        body {
            color: var(--ui-slate);
            background-color: var(--ui-surface);
            line-height: 1.6;
        }

        main {
            flex: 1 0 auto;
            background-color: var(--ui-surface);
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .modal-title {
            color: var(--ui-slate);
        }

        a {
            color: var(--ui-link);
            transition: color 0.2s ease;
        }

        a:hover,
        a:focus {
            color: var(--ui-slate);
        }

        .menu-bar {
            position: relative;
            z-index: 10;
            background-color: var(--ui-slate);
            box-shadow: 0 4px 16px rgba(28, 35, 54, 0.16);
        }

        .navbar-light .navbar-nav .nav-link {
            color: rgba(244, 247, 252, 0.86);
            padding: 0.7rem 0.85rem;
            border-radius: 0.45rem;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .navbar-light .navbar-nav .nav-link:hover,
        .navbar-light .navbar-nav .nav-link:focus,
        .navbar-light .navbar-nav .nav-link.active {
            color: #fff !important;
            background-color: rgba(1, 191, 215, 0.18);
        }

        .navbar-light .navbar-nav .nav-link.active {
            font-weight: 700;
            box-shadow: inset 0 -2px 0 var(--ui-cyan);
        }

        .navbar-toggler {
            border: 1px solid rgba(1, 191, 215, 0.65) !important;
            background-color: var(--ui-surface);
            border-radius: 0.5rem;
        }

        .navbar-toggler:focus {
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(1, 191, 215, 0.28);
        }

        .btn,
        .card__btn {
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .btn:hover,
        .card__btn:hover {
            transform: translateY(-1px);
        }

        .btn-login,
        a.card__btn {
            color: var(--ui-slate);
            background-color: var(--ui-cyan);
            border: 1px solid var(--ui-cyan);
        }

        .btn-login:hover,
        .btn-login:focus,
        a.card__btn:hover,
        a.card__btn:focus {
            color: var(--ui-slate);
            background-color: #00a9bf;
            border-color: #00a9bf;
        }

        .btn-navbar-right {
            min-height: 70px;
            height: auto;
            margin-top: -15px !important;
            margin-bottom: -8px !important;
            margin-right: -30px !important;
            border-radius: 0;
            border-width: 0 0 0 1px;
            border-color: rgba(244, 247, 252, 0.2);
        }

        .btn-primary,
        .bg-primary,
        .badge-primary {
            color: var(--ui-slate) !important;
            background-color: var(--ui-cyan) !important;
            border-color: var(--ui-cyan) !important;
        }

        .btn-success,
        .bg-success,
        .badge-success {
            color: var(--ui-slate) !important;
            background-color: var(--ui-green) !important;
            border-color: var(--ui-green) !important;
        }

        .card,
        .modal-content {
            color: var(--ui-slate);
            background-color: #fff;
            border: 1px solid #e3e9f2;
            border-radius: 0.75rem;
            box-shadow: 0 8px 24px rgba(57, 64, 89, 0.08);
        }

        .card-header {
            color: var(--ui-slate);
            background-color: #eef3fa;
            border-bottom: 1px solid #e3e9f2;
        }

        .card__title,
        .card__text,
        .card__content {
            color: var(--ui-slate);
        }

        .table {
            color: var(--ui-slate);
        }

        .table thead th {
            color: var(--ui-slate);
            background-color: #eef3fa;
            border-color: #dce5ef;
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
            background-color: #fff;
            border-color: #d7dfeb;
            border-radius: 0.45rem;
        }

        .form-control:focus,
        .custom-select:focus {
            color: var(--ui-slate);
            border-color: var(--ui-cyan);
            box-shadow: 0 0 0 0.2rem rgba(1, 191, 215, 0.18);
        }

        .text-muted {
            color: var(--ui-muted) !important;
        }

        .footer {
            color: var(--ui-surface);
            background-color: var(--ui-slate);
            border-top: 3px solid var(--ui-cyan);
            margin-top: 2rem;
            padding: 1.25rem;
        }

        @media (max-width: 991.98px) {
            .navbar-collapse {
                padding: 0.5rem 0 0.75rem;
            }

            .navbar-light .navbar-nav .nav-link {
                margin: 0.1rem 0;
            }

            .btn-navbar-right {
                min-height: auto;
                height: auto;
                margin: 0.5rem 0 0 !important;
                border: 1px solid var(--ui-cyan);
                border-radius: 0.5rem;
            }
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php $this->view('messages') ?>
    <!-- Navbar -->
    <?php $role = $this->db->get_where('role_management', ['role_id' => 2])->row_array() ?>
    <div class="menu-bar">
        <div class="container">
            <nav class="row navbar navbar-expand-lg navbar-light ">
                <a href="<?= site_url('front') ?>" class="navbar-brand">
                    <img src="<?= base_url('assets/images/' . $company['logo']) ?>" alt="logo" width="100%">
                </a>
                <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navb">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navb">
                    <ul class="navbar-nav ml-auto mr-3">
                        <li class="nav-item mx-md-2">
                            <a href="<?= site_url('front') ?>" class="nav-link <?= $title == 'Home' ? 'active' : '' ?>">Beranda</a>
                        </li>
                        <li>
                            <a href="<?= site_url('layanan.html') ?>" class="nav-link <?= $title == 'Produk Layanan' | $title == 'Detail Layanan' ? 'active' : '' ?>">Produk Layanan</a>
                        </li>


                        <?php if ($role['cek_usage'] == 1) { ?>
                            <li>
                                <a href="<?= site_url('front/usage') ?>" class="nav-link <?= $title == 'Usage'  ? 'active' : '' ?>">Usage</a>
                            </li>
                        <?php } ?>






                        <li>
                            <a href="<?= site_url('front/speedtest') ?>" class="nav-link <?= $title == 'Speed Test' ? 'active' : '' ?>">Speed Test</a>
                        </li>
                        <li>
                            <a href="<?= site_url('tentang-kami.html') ?>" class="nav-link <?= $title == 'Tentang Kami' ? 'active' : '' ?>">About Us</a>
                        </li>
                        <?php if ($role['register_show'] == 1) { ?>
                            <li>
                                <a href="<?= site_url('auth/register') ?>" class="nav-link <?= $title == 'Register' ? 'active' : '' ?>">Daftar</a>
                            </li>
                        <?php } ?>
                        <!-- Mobile Button -->
                        <a href="<?= site_url('auth') ?>" style="text-decoration: none">
                            <div class="form-inline d-sm-block d-md-none">
                                <button class="btn btn-login  my-2 my-sm-0 px-4">Masuk</button>
                            </div>
                        </a>
                        <!-- Desktop Button -->
                        <a href="<?= site_url('auth') ?>">
                            <div class="form-inline my-2 my-lg-0 d-none d-md-block">
                                <button class="btn btn-login btn-navbar-right my-2 my-sm-0 px-4">Masuk</button>
                            </div>
                        </a>
                    </ul>
                </div>
            </nav>
        </div>
    </div>

    <main>
        <script src="<?= base_url('assets/') ?>frontend/libraries/jquery/jquery-3.4.1.min.js"></script>
        <?= $contents ?>
    </main>
    <div class="footer mt-auto">
        <div class="container">
            Copyright &copy; <?= date('Y') ?> <?= $company['company_name'] ?>
        </div>
    </div>


    <script src="<?= base_url('assets/') ?>frontend/libraries/bootstrap/js/bootstrap.js"></script>

</body>

</html>
<?php $no_wa = indo_tlp($company['phonecode'] . $company['whatsapp']); ?>
<script>
    console.log('Ini adalah fitur browser yang ditujukan untuk developer. kami tidak bertanggung jawab jika anda mengubah script yg mengakibatkan error, dan mohon untuk tidak menjual kembali source code billing ini, jika terdeteksi maka tidak akan mendapatkan lagi support untuk update !');
    console.log('Ini adalah fitur browser yang ditujukan untuk pengguna. Jika seseorang meminta Anda untuk menyalin-menempel sesuatu di sini untuk mengaktifkan fitur Billing atau "meretas" akun seseorang, ini adalah penipuan dan akan memberikannya akses ke akun Akun Billing Anda.');
</script>
<!-- GetButton.io widget -->
<script type="text/javascript">
    (function() {
        var options = {
            whatsapp: "<?php echo "$no_wa" ?>", // WhatsApp number
            call_to_action: "Kontak Kami", // Call to action
            position: "left", // Position may be 'right' or 'left'
        };
        var proto = document.location.protocol,
            host = "getbutton.io",
            url = proto + "//static." + host;
        var s = document.createElement('script');
        s.type = 'text/javascript';
        s.async = true;
        s.src = url + '/widget-send-button/js/init.js';
        s.onload = function() {
            WhWidgetSendButton.init(host, proto, options);
        };
        var x = document.getElementsByTagName('script')[0];
        x.parentNode.insertBefore(s, x);
    })();
</script>
<!-- /GetButton.io widget -->
<!--Start of Tawk.to Script-->
<script type="text/javascript">
    var Tawk_API = Tawk_API || {},
        Tawk_LoadStart = new Date();
    (function() {
        var s1 = document.createElement("script"),
            s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = '<?= $company['tawk']; ?>';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();
</script>
<!--End of Tawk.to Script-->
<?php
if (!isset($this->customer_m)) {
    $this->load->model('customer_m');
}
$cekcs = $this->customer_m->getisolirpasca()->num_rows();
?>
<?php if ($cekcs > 0) { ?>
    <?php $rt = $this->db->get_where('router', ['id' => 1])->row_array() ?>
    <?php if ($rt['is_active'] == 1) { ?>
        <?php $other = $this->db->get('other')->row_array() ?>
        <!-- <script>
            setInterval("isolir();", 120000);

            function isolir() {
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('front/isolir/' . $other['key_apps']) ?>',
                    cache: false,
                    success: function(data) {}
                });
                console.log('getisolir');
            }
            // return false;
        </script> -->
    <?php } ?>
<?php } ?>