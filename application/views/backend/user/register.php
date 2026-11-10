<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?= $title ?> | <?= $company['company_name'] ?></title>

    <!-- Custom fonts for this template-->
    <link href="<?= base_url('assets/backend/') ?>vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="<?= base_url('assets/backend/') ?>css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        body.login-page {
            background-color: #394059;
            color: #394059;
        }

        .login-page .card {
            background-color: #FFFFFF;
            border-top: 4px solid #46FF23;
            border-radius: 0.75rem;
            color: #394059;
        }

        .login-page .text-center {
            color: #394059;
        }

        .login-page .form-control {
            background-color: #F4F7FC;
            color: #394059;
        }

        .login-page .form-control:focus {
            border-color: #01BFD7;
            box-shadow: 0 0 0 0.2rem rgba(1, 191, 215, 0.2);
        }

        .login-page input[type="radio"] {
            accent-color: #46FF23;
        }

        .login-page .btn-primary {
            background-color: #01BFD7;
            border-color: #01BFD7;
            color: #394059;
        }

        .login-page .btn-primary:hover,
        .login-page .btn-primary:focus {
            background-color: #394059;
            border-color: #394059;
            color: #F4F7FC;
        }
    </style>

</head>


<body class="login-page">
    <div class="container">

        <div class="card o-hidden border-0 shadow-lg my-5 col-lg-5 mx-auto ">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg">
                        <div class="p-5">
                            <div class="text-center">
                                <img class="mb-3" style=" display: block;margin-left: auto;margin-right: auto;width: 100%;" src="<?= base_url('assets/images/') ?><?= $company['logo'] ?>" alt="">
                                <h1 class="h4 text-gray-900 mb-4">Tambah Pengguna </h1>
                            </div>
                            <?= $this->session->flashdata('message') ?>
                            <form class="user" method="post" action="<?= site_url('user/register') ?>">
                                <div class="form-group">
                                    <input type="text" class="form-control form-control-user" id="name" name="name" placeholder="Nama Lengkap" value="<?= set_value('name') ?>">
                                    <?= form_error('name', '<small class="text-danger pl-3 ">', '</small>') ?>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control form-control-user" id="Email" name="email" placeholder="Alamat Email" value="<?= set_value('email') ?>">
                                    <?= form_error('email', '<small class="text-danger pl-3 ">', '</small>') ?>
                                </div>
                                <label for="">* Hak Akses</label>
                                <div class="row mb-2">
                                    <div class="col">
                                        <input type="radio" name="role_id" value="1" required> Admin
                                    </div>
                                    <div class="col">
                                        <input type="radio" name="role_id" value="2" required> Pelanggan
                                    </div>
                                    <div class="col">
                                        <input type="radio" name="role_id" value="3" required> Operator
                                    </div>
                                    <div class="col">
                                        <input type="radio" name="role_id" value="5" required> Teknisi
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <input type="password" class="form-control form-control-user" id="Password1" name="password1" placeholder="Password">
                                        <?= form_error('password1', '<small class="text-danger pl-3 ">', '</small>') ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="password" class="form-control form-control-user" id="Password2" name="password2" placeholder="Konfirmasi Password">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-user btn-block">
                                    Simpan
                                </button>

                            </form>
                            <hr>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="<?= base_url('assets/backend/') ?>vendor/jquery/jquery.min.js"></script>
    <script src="<?= base_url('assets/backend/') ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= base_url('assets/backend/') ?>vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= base_url('assets/backend/') ?>js/sb-admin-2.min.js"></script>

</body>

</html>