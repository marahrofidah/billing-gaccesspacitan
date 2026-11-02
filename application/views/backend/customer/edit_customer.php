<?php $this->view('messages') ?>
<div class="row">

    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Edit Pelanggan</h6>
            </div>
            <div class="card-body">
                <?php echo form_open_multipart('') ?>
                <div class="form-group row">
                    <div class="col-sm-6 mb-3 mb-sm-0">
                        <div class="form-group">
                            <label for="coverage">Coverage Area</label>
                            <select name="coverage" id="coverage" class="form-control" required onchange="selectcoverage(this);">
                                <?php if ($customer->coverage == 0) { ?>
                                    <option value="">-Pilih-</option>
                                <?php } ?>
                                <?php if ($customer->coverage != 0) { ?>
                                    <?php $datacoverage = $this->db->get_where('coverage', ['coverage_id' => $customer->coverage])->row_array(); ?>
                                    <option value="<?= $customer->coverage ?>"><?= $datacoverage['code_area'] ?> - <?= $datacoverage['c_name'] ?></option>
                                <?php } ?>
                                <?php foreach ($coverage as $data) { ?>
                                    <option value="<?= $data->coverage_id ?>"><?= $data->code_area ?> - <?= $data->c_name ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-3 mb-sm-0">

                        <div class="form-group">

                            <div class="form-group">

                                <label for="id_odc">ODC</label>


                                <select name="id_odc" id="id_odc" class="form-control select2" style="width: 100%;" onchange="selectodc(this);">

                                    <?php $odc = $this->db->get_where('m_odc', ['id_odc' => $customer->id_odc])->row_array() ?>

                                    <option value="<?= $customer->id_odc ?>"><?= $odc['code_odc']; ?></option>

                                </select>

                            </div>

                        </div>

                    </div>
                </div>
                <div class="form-group row">

                    <div class="col-sm-6 mb-3 mb-sm-0">

                        <div class="form-group">

                            <label for="id_odp">ODP</label>

                            <select name="id_odp" id="id_odp" class="form-control select2" style="width: 100%;" onchange="selectodp(this);">

                                <?php $odp = $this->db->get_where('m_odp', ['id_odp' => $customer->id_odp])->row_array() ?>

                                <option value="<?= $customer->id_odp ?>"><?= $odp['code_odp']; ?></option>

                            </select>

                        </div>

                    </div>

                    <div class="col-sm-6">


                        <div class="form-group">

                            <label for="no_port_odp">No Port ODP</label>

                            <select name="no_port_odp" id="port_odp" class="form-control " style="width: 100%;">

                                <?php if ($customer->no_port_odp > 0) { ?>

                                    <option value="<?= $customer->no_port_odp ?>"><?= $customer->no_port_odp ?></option>

                                <?php } ?>



                            </select>

                        </div>
                    </div>

                </div>
                <div class="form-group row">

                    <div class="col-sm-6 mb-3 mb-sm-0">
                        <?php if ($customer->no_services != 0) { ?>
                            <div class="form-group">
                                <label for="no_services">No Layanan</label>
                                <input type="text" id="no_servicess" name="no_services" class="form-control" value="<?= $customer->no_services ?>" readonly>
                            </div>
                        <?php } ?>
                        <?php if ($customer->no_services == 0) { ?>
                            <label for="no_services">No Layanan</label>

                            <div class="input-group mb-3">
                                <input type="text" id="no_services" min="1" name="no_services" class="form-control" value="<?= set_value('no_services') ?>" onchange="NoServices()" required>
                                <div class="input-group-append">
                                    <span id="generatenoservices" class="input-group-text">Generate</span>
                                </div>
                            </div>
                            <?= form_error('no_services', '<small class="text-danger pl-3 ">', '</small>') ?>
                        <?php } ?>
                    </div>
                    <div class="col-sm-6 mb-3 mb-sm-0">
                        <div class="form-group">
                            <label for="name">Nama Pelanggan</label>
                            <input type="hidden" name="customer_id" value="<?= $customer->customer_id ?>" readonly>
                            <input type="text" id="name" name="name" class="form-control" value="<?= $customer->name ?>">
                            <?= form_error('name', '<small class="text-danger pl-3 ">', '</small>') ?>
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <label for="register_date">Tanggal Daftar</label>
                    <input type="text" autocomplete="off" id="datepicker" name="register_date" class="form-control" value="<?= $customer->register_date ?>">
                    <?= form_error('register_date', '<small class="text-danger pl-3 ">', '</small>') ?>
                </div>
                <label for="no_ktp">ID Card</label>
                <div class="form-group row">
                    <div class="col-sm-4 mb-3 mb-sm-0">
                        <select name="type_id" id="type_id" class="form-control" required>
                            <option value="<?= $customer->type_id == '' ? 'KTP' : $customer->type_id; ?>"><?= $customer->type_id == '' ? 'KTP' : $customer->type_id; ?></option>
                            <option value="KTP">KTP</option>
                            <option value="SIM">SIM</option>
                            <option value="NPWP">NPWP</option>
                            <option value="Pasport">Pasport</option>
                        </select>
                    </div>
                    <div class="col-sm-8">
                        <input type="text" id="no_ktp" name="no_ktp" class="form-control" value="<?= $customer->no_ktp ?>">
                        <?= form_error('no_ktp', '<small class="text-danger pl-3 ">', '</small>') ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="no_wa">No Telp.</label>
                    <input type="tel" inputmode="numeric" id="no_wa" name="no_wa" class="form-control" value="<?= $customer->no_wa ?>">
                    <?= form_error('no_wa', '<small class="text-danger pl-3 ">', '</small>') ?>
                </div>

                <div class="form-group">
                    <label for="status">Status Berlanggan</label>
                    <select name="status" id="" class="form-control" required>
                        <?php if ($customer->c_status == '') { ?>
                            <option value="">-Pilih-</option>
                        <?php } ?>
                        <?php if ($customer->c_status != '') { ?>
                            <option value="<?= $customer->c_status ?>"><?= $customer->c_status ?></option>
                        <?php } ?>
                        <option value="Aktif">Aktif</option>
                        <option value="Non-Aktif">Non-Aktif</option>
                        <option value="Menunggu">Menunggu</option>
                        <option value="Free">Free</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ppn">PPN</label>
                    <select class="form-control" id="ppn" name="ppn">
                        <option value="<?= $customer->ppn; ?>"><?= $customer->ppn == 1 ? 'Yes' : 'No' ?></option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="address">Alamat</label>
                    <textarea id="address" name="address" class="form-control"><?= $customer->address ?></textarea>
                </div>
                <div class="form-group">
                    <label for="description">Keterangan</label>
                    <textarea name="cust_description" id="" class="form-control" cols="30" rows="10" placeholder="info yg berkaitan dengan pelanggan"><?= $customer->cust_description; ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="form-group row mt-2">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="type_payment">Jenis Pembayaran</label>
                            <select name="type_payment" id="type_payment" class="form-control" required>
                                <option value="<?= $customer->type_payment ?>"><?= $customer->type_payment == 0 ? 'Pascabayar' : 'Prabayar'; ?></option>
                                <option value="0">Pasca Bayar</option>
                                <option value="1">Prabayar</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4 mb-3 mb-sm-0">
                        <div class="form-group">
                            <label for="due_date">Jatuh Tempo</label> * 1 - 28
                            <input type="number" autocomplete="off" name="due_date" class="form-control" min="1" max="28" value="<?= $customer->due_date ?>" required>
                        </div>

                    </div>

                    <div class="col-sm-4 mb-3 mb-sm-0">
                        <div class="form-group">
                            <label for="month_due_date">Bulan Jatuh Tempo</label>
                            <select name="month_due_date" id="month_due_date" class="form-control" required>
                                <option value="<?= $customer->month_due_date ?>"><?= $customer->month_due_date == 0 ? 'Sesuai Periode Tagihan' : '1 bulan dari periode'; ?></option>
                                <option value="0">Sesuai Periode Tagihan</option>
                                <option value="1">1 bulan dari periode</option>
                            </select>
                        </div>

                    </div>
                </div>
                <?php $rt = $this->db->get_where('router', ['id' => 1])->row_array() ?>
                <?php if ($rt['is_active'] == 1) { ?>

                    <div class="form-group row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <div class="form-group">
                                <label for="auto_isolir">Auto Isolir</label>
                                <select name="auto_isolir" id="autoisolir" class="form-control" required onChange="selectisolir(this);">
                                    <option value="<?= $customer->auto_isolir ?>"><?= $customer->auto_isolir == 1 ? 'Yes' : 'No' ?></option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3 mb-sm-0" id="maxdueisolir" style="display: <?= $customer->auto_isolir == 1 ? '' : 'none' ?>">
                            <div class="form-group">
                                <label for="max_due_isolir">Max Isolir</label> <span> tambah hari dari jatuh tempo</span>
                                <input type="number" name="max_due_isolir" max="31" value="<?= $customer->max_due_isolir ?>" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div id="selectactionisolir" class="form-group" style="<?= $customer->auto_isolir == 1 ? 'display: block' : 'display: none' ?>">
                        <div class="row">
                            <div class="col"> <input type="checkbox" <?= $customer->action == 0 ? 'checked' : '' ?> name="disableuser" id="disableuser"> <label for="">Disable User</label></div>
                            <div class="col"> <input type="checkbox" <?= $customer->action == 1 ? 'checked' : '' ?> name="changeprofile" id="changeprofile"> <label for="">Ganti Profile</label></div>
                        </div>
                    </div>
                    <div class="row container" id="remarkchangeprofile" style="display: none">
                        <li> Action Ganti Profile ini khusus untuk mode PPPOE & Hotspot dan yang sudah menerapkan halaman isolir dengan webproxy </li>
                        <li>Jika anda menggunakan PPPOE dengan IP Static, silahkan pilih Type IP menjadi Static setelah pilih mode user dibawah ini menjadi PPPOE</li>
                        <li> Pastikan nama profile PPPOE / Hotspot nya <span style="color: red; font-weight:bold"> EXPIRED</span> </li>
                        <li> Untuk Mode Static & PPPOE Static, IP Target nya otomatis masuk ke address-list IP -> Firewall -> Address List </li>
                        <li> Untuk Mode Standalone akan otomatis disable Interface router pelanggan</li>
                        <br>
                    </div>
                    <div class="row container" id="remarkdisableuser" style="display: none">
                        <li> Disable user hanya untuk mode PPPOE & Hotspot </li>
                        <li> Untuk Mode Static, IP Target nya otomatis di <span style="color:red">DROP </span> ke IP -> Firewall -> Filter Rules </li>
                        <li> Untuk Mode Standalone akan otomatis disable Interface router pelanggan</li>
                        <br>
                    </div>
                    <input type="hidden" name="action" id="actionisolir" placeholder="Action Isolir" value="<?= $customer->action ?>" class="form-control">
                    <div class="form-group">
                        <div class="row">
                            <div class="col"> <input type="checkbox" name="changeconnection" id="changeconnection"> <label for="">Ganti Koneksi</label></div>
                        </div>
                    </div>
                    <div class="form-group" id="formrouter" style="display: none">
                        <?php $listrouter = $this->db->get('router')->result() ?>
                        <?php $router = $this->db->get_where('router', ['id' => $customer->router])->row_array() ?>
                        <label for="router">Router</label>
                        <select name="router" id="router" class="form-control" onChange="selectrouter(this);">
                            <?php if ($customer->router == 0) { ?>
                                <option value="">-Pilih-</option>
                            <?php } ?>
                            <?php if ($customer->router != 0) { ?>
                                <option value="<?= $customer->router ?>"><?= $router['alias']; ?></option>
                            <?php } ?>
                            <?php foreach ($listrouter as $router) { ?>
                                <option value="<?= $router->id ?>"><?= $router->alias; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group" id="statusrouter">
                        <?php if ($rt['is_active'] == 1) { ?>
                            <?php if ($customer->mode_user != '') { ?>
                                <?php
                                $router = $this->db->get_where('router', ['id' => $customer->router])->row_array();
                                $ip = $router['ip_address'];
                                $user = $router['username'];
                                $pass = $router['password'];
                                $port = $router['port'];
                                $API = new Mikweb();
                                $API->connect($ip, $user, $pass, $port);
                                $resource = $API->comm("/system/resource/print");
                                if ($resource['0']['uptime'] != null) {
                                    $status = 'Connected';
                                } else {
                                    $status = 'Disconnect';
                                } ?>
                                <label for="Status Router">Router Status :
                                </label>
                                <div class="connection" id="connection">

                                    <?php if ($status == 'Connected') { ?>
                                        <div class="badge badge-success">Connected</div>
                                    <?php } ?>
                                    <?php if ($status == 'Disconnect') { ?>
                                        <div class="badge badge-danger">Disconnect</div>
                                    <?php } ?>

                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                    <div class="form-group" id="btn-mikro" style="display: none">
                        <div class="row">
                            <div class="col"> <input type="checkbox" name="createusermikrotik" id="createusermikrotik"> <label for="">Create New</label></div>
                            <div class="col"> <input type="checkbox" name="sinkronisasi" id="sinkronisasi"> <label for="">Ganti Sinkronisasi</label></div>
                        </div>
                    </div>
                    <input type="hidden" name="changeconn" id="changeconn">
                    <input type="hidden" name="createnew" id="createnew">
                    <input type="hidden" name="sinkron" id="sinkron">
                    <div class="form-group">
                        <div id="modeuser" style="display: none">
                            <label for="mode_user">Mode User</label>
                            <select name="mode_user" id="mode_user" class="form-control" onChange="selectmode(this);">
                                <option value="">-Pilih-</option>
                                <option value="Hotspot">Hotspot</option>
                                <option value="PPPOE">PPPOE</option>
                                <option value="Static">Static</option>
                                <option value="Standalone">Standalone</option>
                            </select>
                        </div>
                    </div>

                    <div class="loading"></div>
                    <div class="form-group">
                        <div id="userprofile" style="display: none">
                            <label>User Profile</label>
                            <select name="profile" id="profile" class="form-control view_data select2" style="width: 100%;" onChange="selectprofile();">
                                <option value=""></option>

                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div id="usersinkron" style="display: none">
                            <label>User <span id="users"></span></label>
                            <select name="user_sinkron" id="user_sinkron" style="width: 100%;" class="form-control view_data_sinkron select2" onChange="selectuser(this);">
                            </select>
                        </div>
                        <div class="form-group">
                            <div id="type_ip" style="display: <?= $customer->mode_user == 'PPPOE' ? '' : 'none' ?>">
                                <label for="type_ip">Type IP</label>
                                <select name="type_ip" id="type_ip" class="form-control">

                                    <option value="<?= $customer->type_ip; ?>"><?= $customer->type_ip == 1 ? 'Static' : 'DHCP' ?></option>
                                    <option value="0">DHCP</option>
                                    <option value="1">Static</option> <span style="font-size: small;">From remote address</span>
                                </select>
                            </div>
                        </div>
                        <div id="formprofile" style="display: <?= $customer->action == '0' ? 'none' : '' ?> ">
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <div class="form-group">
                                        <label>User Profile</label>
                                        <input type="text" name="userprofilesinkron" readonly id="userprofilesinkron" value="<?= $customer->user_profile ?>" placeholder="User Profile Untuk Open Isolir" class="form-control">
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="form-group">
                        <div id="usermikrotik" style="display: none">
                            <label>User <span id="userm"></span></label>
                            <input type="text" autocomplete="off" id="user_mikrotik" name="user_mikrotik" class="form-control" value="<?= set_value('user_mikrotik') ?>">
                        </div>
                    </div>
                    <div id="detailconnection">
                        <div class="form-group row">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <div class="form-group">
                                    <label for="">Mode User</label>
                                    <input type="text" value="<?= $customer->mode_user ?>" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group" id="maxdueisolir">
                                    <label for="">User <?= $customer->mode_user; ?></label>
                                    <input type="text" class="form-control" value="<?= $customer->user_mikrotik; ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($customer->auto_isolir == 1) { ?>
                        <?php if ($customer->mode_user == 'Hotspot' or $customer->mode_user == 'PPPOE') { ?>
                            <div class="form-group">
                                <div id="profilesinkron">
                                    <?php if ($status == 'Connected') { ?>
                                        <?php
                                        if ($customer->mode_user == 'PPPOE') {
                                            $cekprofile = $API->comm("/ppp/profile/print", array(
                                                '?name' => $customer->user_profile,
                                            ));
                                            $getprofile = $API->comm("/ppp/profile/print");
                                        }
                                        // var_dump($cekprofile);
                                        if ($customer->mode_user == 'Hotspot') {
                                            $cekprofile = $API->comm("/ip/hotspot/profile/print", array(
                                                '?name' => $customer->user_profile,
                                            ));
                                            $getprofile = $API->comm("/ip/hotspot/profile/print");
                                        }
                                        ?>

                                    <?php } ?>


                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>


                    <div class="form-group">
                        <div id="passwordmikrotik" style="display: none">
                            <label>Password</label>
                            <input type="password" name="passwordmikrotik" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col">
                            <div id="localaddress" style="display: none">

                                <label>Local Address</label>
                                <input type="text" name="localaddress" class="form-control">
                            </div>
                        </div>
                        <div class="col">
                            <div id="remoteaddress" style="display: none">
                                <label>Remote Address</label>
                                <input type="text" name="remoteaddress" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col">
                            <div id="target" style="display: none">
                                <label>Target IP</label>
                                <input type="text" name="target" class="form-control">
                            </div>
                        </div>
                        <div class="col">
                            <div id="uploadlimit" style="display: none">
                                <label>Limit Upload</label>
                                <div class="input-group mb-3">
                                    <input type="number" min="1" name="uploadlimit" class="form-control">
                                    <div class="input-group-append">
                                        <select name="upload" id="upload" class="form-control">
                                            <option value="K">Kb</option>
                                            <option value="M">Mb</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col">
                            <div id="downloadlimit" style="display: none">
                                <label>Limit Download</label>
                                <div class="input-group mb-3">
                                    <input type="number" min="1" name="downloadlimit" class="form-control">
                                    <div class="input-group-append">
                                        <select name="download" id="download" class="form-control">
                                            <option value="K">Kb</option>
                                            <option value="M">Mb</option>

                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php } ?>
                <?php if ($rt['is_active'] != 1) { ?>
                    <input type="hidden" name="auto_isolir" value="0">
                    <input type="hidden" name="max_due_isolir" value="<?= $customer->max_due_isolir ?>">

                    <input type="hidden" name="action" value="0">

                <?php } ?>

                <div class="form-group">
                    <label for="codeunique">Code Unique</label> * hanya recehan jika ada pelanggan yg trf
                    <select name="codeunique" id="" class="form-control" required>
                        <option value="<?= $customer->codeunique ?>"><?= $customer->codeunique == 1 ? 'Active' : 'Non-Active' ?></option>
                        <option value="1">Active</option>
                        <option value="0">Non-Active</option>
                    </select>
                </div>






                <div class="modal-footer">
                    <button type="reset" class="btn btn-secondary" data-dismiss="modal">Reset</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
                <?php echo form_close() ?>
            </div>
        </div>
    </div>
</div>

<script>
    $("#changeprofile").click(function() {
        if ($(this).is(":checked")) {
            $("#actionisolir").val('1');
            $("#remarkchangeprofile").show();
            $("#profilesinkron").show();
            document.getElementById("disableuser").checked = false;
        } else {
            $("#actionisolir").val('0');
            $("#remarkchangeprofile").hide();
            $("#remarkdisableuser").hide();
            $("#profilesinkron").hide();
        }
    });
    $("#disableuser").click(function() {
        if ($(this).is(":checked")) {
            $("#remarkchangeprofile").hide();
            $("#remarkdisableuser").show();
            $("#actionisolir").val('0');
            $("#profilesinkron").hide();
            document.getElementById("changeprofile").checked = false;
        } else {
            $("#remarkdisableuser").hide();
            $("#profilesinkron").hide();
            $("#remarkchangeprofile").hide();
        }
    });

    function selectisolir(sel) {
        var action = $('#autoisolir').val();
        var type = $('#type_payment').val();
        if (action == 1) {
            $("#selectactionisolir").show();
            $("#maxdueisolir").show();
        } else {
            $("#selectactionisolir").hide();
            $("#actionisolir").val('0');
            $("#maxdueisolir").hide();
        }
    }

    function selectactionisolir(sel) {
        var action = $('#autoisolir').val();
        if (action == 1) {
            $("#actionisolir").val('1');


            $("#userprofilesinkron").show();
        } else {
            $("#actionisolir").val('0');

        }
    }

    function selectuser(sel) {
        var user = $('#user_sinkron').val();
        var usermode = $('#mode_user').val();
        var router = $("#router").val();
        $('#formprofile').hide();
        if (usermode == 'PPPOE') {
            var url = "<?= site_url('mikrotik/getuserprofile') ?>" + "/" + Math.random();
            $.ajax({
                type: 'POST',
                url: url,
                data: "&user=" + user + "&router=" + router + "&mode=" + usermode,
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
                    var c = jQuery.parseJSON(data);
                    $('.loading').html('');
                    if (c['profile'] != '') {
                        $('#formprofile').show();
                        $('#userprofilesinkron').val(c['profile']);
                    }
                }
            });
            return false;
        }
        if (usermode == 'Hotspot') {
            var url = "<?= site_url('mikrotik/getuserprofile') ?>" + "/" + Math.random();
            $.ajax({
                type: 'POST',
                url: url,
                data: "&user=" + user + "&router=" + router + "&mode=" + usermode,
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
                    var c = jQuery.parseJSON(data);
                    $('.loading').html('');
                    $('#userprofilesinkron').show();
                    $('#userprofilesinkron').val(c['profile']);
                }
            });
            return false;
        }


    }
    $(function() {
        $("#generatenoservices").click(function() {
            var m = new Date();
            var th = m.getFullYear().toString().substr(-2);
            var dateString =
                th +
                ("0" + (m.getMonth() + 1)).slice(-2) +
                ("0" + m.getDate()).slice(-2) +
                ("0" + m.getHours()).slice(-2) +
                ("0" + m.getMinutes()).slice(-2) +
                ("0" + m.getSeconds()).slice(-2);

            console.log(dateString);
            $("#no_services").val(dateString);
            $("#user_mikrotik").val(dateString);

        });
    });

    function selectcoverage() {
        var coverage = $("#coverage").val();
        var url = "<?= site_url('coverage/getcode') ?>" + "/" + Math.random();
        $.ajax({
            type: 'POST',
            url: url,
            data: "&coverage_id=" + coverage,
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
                $("#no_services").val(data);
                $("#no_services").focus();
                var url = "<?= site_url('odc/getodc') ?>" + "/" + Math.random();

                $.ajax({

                    type: 'POST',

                    url: url,

                    data: "&coverage_id=" + coverage,

                    cache: false,

                    success: function(data) {

                        $("#id_odc").html(data);

                    }

                });
            }
        });
        return false;

    }

    function selectodc() {

        var id_odc = $("#id_odc").val();

        var url = "<?= site_url('odp/getodp') ?>" + "/" + Math.random();

        $.ajax({

            type: 'POST',

            url: url,

            data: "&id_odc=" + id_odc,

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

                $("#id_odp").html(data);

                $("#id_odp").focus();

            }

        });

        return false;



    }



    function selectodp() {

        var id_odp = $("#id_odp").val();

        // alert(id_odp);

        var url = "<?= site_url('odp/getportodp') ?>" + "/" + Math.random();

        $.ajax({

            type: 'POST',

            url: url,

            data: "&id_odp=" + id_odp,

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

                $("#port_odp").html(data);



            }

        });

        return false;



    }
</script>
<script>
    function selectrouter(sel) {
        var router = $("#router").val();
        $("#sinkron").val('');
        $("#createnew").val('');
        $("#userprofile").hide();
        $("#detailconnection").hide();
        // $("#userprofilesinkron").hide();
        $("#usersinkron").hide();
        $('#profile').val('');
        $("#modeuser").hide();
        $("#usersinkron").hide();
        $("#mode_user").val('');
        $("#localaddress").hide();
        $("#remoteaddress").hide();
        $("#target").hide();
        $("#uploadlimit").hide();
        $("#downloadlimit").hide();
        $("#passwordmikrotik").hide();
        document.getElementById("createusermikrotik").checked = false;
        document.getElementById("sinkronisasi").checked = false;
        var url = "<?= site_url('router/cekconnection') ?>" + "/" + Math.random();
        $.ajax({
            type: 'POST',
            url: url,
            data: "&router=" + router,
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
                $('.connection').html(data);
            }
        });
        return false;
    }

    function selectprofile() {
        $('#usermikrotik').focus();
    }

    function selectmode(sel) {
        var usermode = $('#mode_user').val();
        var router = $("#router").val();
        var createnew = $("#createnew").val();
        var sinkron = $("#sinkron").val();
        $("#userm").html(usermode);
        $("#users").html(usermode);
        if (usermode == 'PPPOE' && createnew == '1') {
            $("#userprofile").show();
            $("#profilesinkron").hide();
            $("#usermikrotik").show();
            $("#type_ip").show();
            $("#passwordmikrotik").show();
            $("#localaddress").show();
            $("#remoteaddress").show();
            $("#usersinkron").hide();
            $("#target").hide();
            $("#uploadlimit").hide();
            $("#downloadlimit").hide();
            var url = "<?= site_url('mikrotik/getUserProfileMikrotik/') ?>" + "/" + Math.random();
            $.ajax({
                type: 'POST',
                url: url,
                data: "&mode=" + usermode + "&router=" + router,
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
                    $('.view_data').html(data);
                    $("#profile").focus();
                }
            });
            return false;

        }
        if (usermode == 'PPPOE' && sinkron == '1') {
            $("#userprofile").hide();
            $("#profilesinkron").show();
            $("#type_ip").show();
            $("#usermikrotik").hide();
            $("#usersinkron").show();
            $("#passwordmikrotik").hide();
            $("#localaddress").hide();
            $("#remoteaddress").hide();
            $("#target").hide();
            $("#uploadlimit").hide();
            $("#downloadlimit").hide();
            var url = "<?= site_url('mikrotik/getUserMikrotik/') ?>" + "/" + Math.random();
            $.ajax({
                type: 'POST',
                url: url,
                data: "&mode=" + usermode + "&router=" + router,
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
                    $('.view_data_sinkron').html(data);
                    $("#user_sinkron").focus();
                }
            });
            return false;

        }
        if (usermode == 'Standalone' && createnew == '1') {
            $("#userprofile").hide();
            $("#type_ip").hide();
            $("#profilesinkron").hide();
            $("#usermikrotik").show();
            $("#passwordmikrotik").hide();
            $("#localaddress").hide();
            $("#remoteaddress").hide();
            $("#usersinkron").hide();
            $("#target").hide();
            $("#uploadlimit").hide();
            $("#downloadlimit").hide();
            var url = "<?= site_url('mikrotik/getUserMikrotik/') ?>" + "/" + Math.random();
            $.ajax({
                type: 'POST',
                url: url,
                data: "&mode=" + usermode + "&router=" + router,
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
                    $('.view_data').html(data);
                    $("#profile").focus();
                }
            });
            return false;

        }
        if (usermode == 'Standalone' && sinkron == '1') {
            $("#userprofile").hide();
            $("#type_ip").hide();
            $("#usermikrotik").hide();
            $("#passwordmikrotik").hide();
            $("#localaddress").hide();
            $("#remoteaddress").hide();
            $("#usersinkron").show();
            $("#target").hide();
            $("#uploadlimit").hide();
            $("#downloadlimit").hide();
            var url = "<?= site_url('mikrotik/getUserMikrotik/') ?>" + "/" + Math.random();
            $.ajax({
                type: 'POST',
                url: url,
                data: "&mode=" + usermode + "&router=" + router,
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
                    $('.view_data_sinkron').html(data);
                    $("#user_sinkron").focus();
                }
            });
            return false;

        }
        if (usermode == 'Hotspot' && createnew == '1') {
            $("#userprofile").show();
            $("#profilesinkron").hide();
            $("#type_ip").hide();
            $("#usermikrotik").show();
            $("#passwordmikrotik").show();
            $("#localaddress").hide();
            $("#remoteaddress").hide();
            $("#usersinkron").hide();
            $("#target").hide();
            $("#uploadlimit").hide();
            $("#downloadlimit").hide();
            var url = "<?= site_url('mikrotik/getUserProfileMikrotik/') ?>" + "/" + Math.random();
            $.ajax({
                type: 'POST',
                url: url,
                data: "&mode=" + usermode + "&router=" + router,
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
                    $('.view_data').html(data);
                    $("#profile").focus();
                }
            });
            return false;


        }
        if (usermode == 'Hotspot' && sinkron == '1') {
            $("#userprofile").hide();
            $("#profilesinkron").show();
            $("#usermikrotik").hide();
            $("#usersinkron").show();
            $("#passwordmikrotik").hide();
            $("#localaddress").hide();
            $("#type_ip").hide();
            $("#remoteaddress").hide();
            $("#target").hide();
            $("#uploadlimit").hide();
            $("#downloadlimit").hide();
            var url = "<?= site_url('mikrotik/getUserMikrotik/') ?>" + "/" + Math.random();
            $.ajax({
                type: 'POST',
                url: url,
                data: "&mode=" + usermode + "&router=" + router,
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
                    $('.view_data_sinkron').html(data);
                    $("#user_sinkkron").focus();
                }
            });
            return false;


        }
        if (usermode == 'Static' && createnew == '1') {
            $("#target").show();
            $("#profilesinkron").hide();
            $("#usermikrotik").show();
            $("#usermikrotik").focus();
            $("#uploadlimit").show();
            $("#type_ip").hide();
            $("#downloadlimit").show();
            $("#userprofile").hide();
            $("#usersinkron").hide();
            $("#passwordmikrotik").hide();
            $("#localaddress").hide();
            $("#remoteaddress").hide();
        }
        if (usermode == 'Static' && sinkron == '1') {
            $("#target").hide();
            $("#usermikrotik").hide();
            $("#usersinkron").show();
            $("#user_sinkron").focus();
            $("#uploadlimit").hide();
            $("#downloadlimit").hide();
            $("#type_ip").hide();
            $("#userprofile").hide();
            $("#passwordmikrotik").hide();
            $("#localaddress").hide();
            $("#remoteaddress").hide();
            var url = "<?= site_url('mikrotik/getUserMikrotik/') ?>" + "/" + Math.random();
            $.ajax({
                type: 'POST',
                url: url,
                data: "&mode=" + usermode + "&router=" + router,
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
                    $('.view_data_sinkron').html(data);
                    $("#user_sinkkron").focus();
                }
            });
            return false;
        }
        // $("#userprofile").hide();
        // $("#localaddress").hide();
        // $("#remoteaddress").hide();
        // $("#target").hide();
        // $("#uploadlimit").hide();
        // $("#downloadlimit").hide();
        // $("#passwordmikrotik").hide();


    }

    function NoServices() {
        var no_services = $('[name="no_services"]');
        $("#usermikrotik").val(no_services.val());
    }

    $(function() {
        $("#createusermikrotik").click(function() {
            var router = $("#router").val();
            if (router == '') {
                Swal.fire({
                    icon: 'error',
                    html: 'Router belum dipilih',
                    showConfirmButton: true,
                })
                $('#router').focus()
                document.getElementById("createusermikrotik").checked = false;
            } else {
                $("#modeuser").show();
                var modeuser = $("#mode_user").val();
                if ($(this).is(":checked")) {
                    $("#createnew").val('1');
                    $("#sinkron").val('');
                    $("#mode_user").val('');
                    $("#mode_user").focus();
                    $("#usermikrotik").hide();
                    $("#user_mikrotik").val('');
                    $("#userprofile").hide();
                    $("#usersinkron").hide();
                    $("#localaddress").hide();
                    $("#remoteaddress").hide();
                    $("#target").hide();
                    $("#uploadlimit").hide();
                    $("#downloadlimit").hide();
                    $("#passwordmikrotik").hide();
                    document.getElementById("sinkronisasi").checked = false;
                } else {
                    $("#createnew").val('');
                    $("#modeuser").hide();
                    $("#userprofile").hide();
                    $("#localaddress").hide();
                    $("#remoteaddress").hide();
                    $("#target").hide();
                    $("#usersinkron").hide();
                    $("#usermikrotik").hide();
                    $("#uploadlimit").hide();
                    $("#downloadlimit").hide();
                    $("#passwordmikrotik").hide();
                }
            }

        });
        $("#sinkronisasi").click(function() {
            var router = $("#router").val();
            if (router == '') {
                Swal.fire({
                    icon: 'error',
                    html: 'Router belum dipilih',
                    showConfirmButton: true,
                })
                $('#router').focus()
                document.getElementById("sinkronisasi").checked = false;
            } else {
                $("#modeuser").show();
                var modeuser = $("#mode_user").val();
                if ($(this).is(":checked")) {
                    $("#sinkron").val('1');
                    $("#createnew").val('');
                    $("#usermikrotik").hide();
                    $("#mode_user").val('');
                    $("#mode_user").focus();
                    $("#userprofile").hide();
                    $("#user_mikrotik").val('');
                    $("#localaddress").hide();
                    $("#usersinkron").hide();
                    $("#remoteaddress").hide();
                    $("#target").hide();
                    $("#uploadlimit").hide();
                    $("#downloadlimit").hide();
                    $("#passwordmikrotik").hide();
                    document.getElementById("createusermikrotik").checked = false;
                } else {
                    $("#sinkron").val('');
                    $("#modeuser").hide();
                    $("#userprofile").hide();
                    $("#localaddress").hide();
                    $("#remoteaddress").hide();
                    $("#target").hide();
                    $("#uploadlimit").hide();
                    $("#usersinkron").hide();
                    $("#downloadlimit").hide();
                    $("#passwordmikrotik").hide();
                }
            }

        });
        $("#changeconnection").click(function() {
            $("#formrouter").show();
            $("#btn-mikro").show();
            var modeuser = $("#mode_user").val();
            if ($(this).is(":checked")) {
                $("#changeconn").val('1');
                $("#sinkron").val('');
                $("#createnew").val('');
                $("#usermikrotik").hide();
                $("#mode_user").val('');
                $("#mode_user").focus();
                $("#userprofile").hide();
                $("#user_mikrotik").val('');
                $("#localaddress").hide();
                $("#usersinkron").hide();
                $("#remoteaddress").hide();
                $("#target").hide();
                $("#uploadlimit").hide();
                $("#downloadlimit").hide();
                $("#passwordmikrotik").hide();
                document.getElementById("createusermikrotik").checked = false;
            } else {
                $("#changeconn").val('');
                $("#sinkron").val('');
                $("#createnew").val('');
                $("#modeuser").hide();
                $("#userprofile").hide();
                $("#localaddress").hide();
                $("#remoteaddress").hide();
                $("#target").hide();
                $("#uploadlimit").hide();
                $("#usersinkron").hide();
                $("#downloadlimit").hide();
                $("#passwordmikrotik").hide();
                $("#formrouter").hide();
                $("#btn-mikro").hide();
                $("#statusrouter").hide();
            }
        });
    });
    $(function() {
        //Initialize Select2 Elements
        $('.select2').select2()
    });
</script>

<script>
    function selectolt(sel) {
        var olt = $("#olt").val();
        var url = "<?= site_url('olt/getonutable') ?>" + "/" + Math.random();
        $.ajax({
            type: 'POST',
            url: url,
            data: "&id=" + olt,
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
                $("#onutable").show();
                $('.view_data_onutable').html(data);
            }
        });
        return false;

    };
</script>