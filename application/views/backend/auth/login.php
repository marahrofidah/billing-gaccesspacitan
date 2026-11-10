<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="<?= base_url('assets/images/favicon.png') ?>">
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

        .login-page .text-center,
        .login-page .custom-control-label {
            color: #394059;
        }

        .login-page .form-control {
            background-color: #F4F7FC;
            color: #394059;
        }

        .login-page hr {
            border-color: rgba(57, 64, 89, 0.2);
        }

        .login-page .form-control:focus {
            border-color: #01BFD7;
            box-shadow: 0 0 0 0.2rem rgba(1, 191, 215, 0.2);
        }

        .login-page input[type="radio"],
        .login-page .custom-control-input:checked ~ .custom-control-label::before {
            accent-color: #46FF23;
        }

        .login-page .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #46FF23;
            border-color: #46FF23;
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

        .login-page a {
            color: #394059;
        }

        .login-page a:hover {
            color: #01BFD7;
        }
    </style>

</head>

<body class="login-page">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-lg-6">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg">
                                <div class="p-5">
                                    <div class="text-center">
                                        <img class="mb-3" style=" display: block;margin-left: auto;margin-right: auto;width: 100%;" src="<?= base_url('assets/images/') ?><?= $company['logo'] ?>" alt="">
                                        <?php $this->view('messages') ?>
                                    </div>
                                    <form action="" method="post" class="user">
                                        <div class="row mb-2">
                                            <div class="col">
                                                <?php $mode = isset($_COOKIE['loginMode']) ? $_COOKIE['loginMode'] : ''; ?>
                                                <input type="radio" <?php if ($mode == 'email' or $mode == '') {
                                                                        echo 'checked';
                                                                    } ?> name="mode" value="email" required> Email
                                            </div>
                                            <div class="col">
                                                <input type="radio" name="mode" <?php if ($mode == 'phone') {
                                                                                    echo 'checked';
                                                                                } ?> value="phone" required> Phone
                                            </div>

                                        </div>
                                        <div class="form-group">
                                            <input type="text" name="email" class="form-control form-control-user" value="<?php if (isset($_COOKIE["loginId"])) {
                                                                                                                                echo $_COOKIE["loginId"];
                                                                                                                            } ?>" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Enter Email or Phone...">
                                            <?= form_error('email', '<small class="text-danger pl-3 ">', '</small>') ?>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" name="password" class="form-control form-control-user" id="exampleInputPassword" placeholder="Password" value="<?php if (isset($_COOKIE["loginPass"])) {
                                                                                                                                                                                        echo $_COOKIE["loginPass"];
                                                                                                                                                                                    } ?>">
                                            <?= form_error('password', '<small class="text-danger pl-3 ">', '</small>') ?>
                                        </div>
                                        <div class="form-group my-4 text-left">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" name="remember" id="remember" <?php if (isset($_COOKIE["loginId"])) { ?> checked="checked" <?php } ?>>
                                                <label class="custom-control-label" for="remember">Remember Me</label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Masuk
                                        </button>
                                    </form>
                                    <hr>
                                    <?php $role = $this->db->get_where('role_management', ['role_id' => 2])->row_array() ?>
                                    <div class="text-center">
                                        <a class="small" href="<?= site_url('auth/forgotpassword') ?>" style="text-decoration: none">Lupa Password ?</a>
                                    </div>
                                    <?php if ($role['register_show'] == 1) { ?>

                                        <div class="text-center">
                                            <a class="small" href="<?= site_url('auth/register') ?>">Register</a>
                                        </div>
                                    <?php } ?>


                                </div>
                            </div>
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