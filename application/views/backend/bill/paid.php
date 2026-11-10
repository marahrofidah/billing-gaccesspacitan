<!-- Page Heading -->
<?php $role = $this->db->get_where('role_management', ['role_id' => $this->session->userdata('role_id')])->row_array() ?>
<?php $menu = $this->db->get_where('role_menu', ['role_id' => $this->session->userdata('role_id')])->row_array() ?>
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <?php if ($this->session->userdata('role_id') == 1) { ?>
        <!-- <a href="" id="#addModal" data-toggle="modal" data-target="#addModal" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Tambah</a> -->

    <?php } ?>
</div>
<?php $this->view('messages') ?>

<?php $totalcustomer = $this->db->get_where('customer', ['c_status' => 'Aktif'])->num_rows(); ?>
<?php if ($totalcustomer >= 500) { ?>
    <?php if ($this->session->userdata('role_id') == 1 or $menu['coverage_menu'] == 1) { ?>
        <div class="col-lg-12 col-sm-12 col-md-6">
            <div class="card shadow mb-2">
                <div class="card-header py-1">
                    <h6 class="m-0 font-weight-bold">Filter Tagihan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 col-sm-12 col-md-6">
                            <div class="d-sm-flex align-items-center justify-content-between ">
                                <div class="form-group">
                                    <label for="">Coverage Area</label>
                                    <select class="form-control" name="coverage" id="coverage" onchange="getdatabill()" required>
                                        <option value="all">Semua</option>
                                        <?php
                                        foreach ($coverage as $r => $data) { ?>
                                            <option value="<?= $data->coverage_id ?>"><?= $data->code_area ?> - <?= $data->c_name ?> </option>
                                        <?php } ?>
                                        <?php if ($this->session->userdata('role_id') == 1) { ?>
                                            <option value="">Tanpa Area</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-12 col-md-6">
                            <div class="d-sm-flex align-items-center justify-content-between ">
                                <div class="form-group">
                                    <label for="">Bulan</label>
                                    <select class="form-control" style="width: 100%;" name="month" id="month" onchange="getdatabill()">
                                        <option value="<?= date('m') ?>"><?= indo_month(date('m')); ?></option>
                                        <option value="01">Januari</option>
                                        <option value="02">Februari</option>
                                        <option value="03">Maret</option>
                                        <option value="04">April</option>
                                        <option value="05">Mei</option>
                                        <option value="06">Juni</option>
                                        <option value="07">Juli</option>
                                        <option value="08">Agustus</option>
                                        <option value="09">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                        <option value="all">Semua</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-12 col-md-6">
                            <div class="d-sm-flex align-items-center justify-content-between ">
                                <div class="form-group">
                                    <label for="">Tahun</label>
                                    <select class="form-control" style="width: 100%;" name="year" id="year" onchange="getdatabill()">
                                        <option value="<?= date('Y') ?>"><?= date('Y') ?></option>
                                        <?php if ($this->session->userdata('role_id') == 1) {  ?>
                                            <?php
                                            for ($i = date('Y') + 1; $i >= date('Y') - 2; $i -= 1) {
                                            ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if (date('m') == 12) {  ?>
                                            <?php
                                            for ($i = date('Y') + 1; $i >= date('Y') - 2; $i -= 1) {
                                            ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if (date('m') < 12) {  ?>
                                            <?php
                                            for ($i = date('Y'); $i >= date('Y') - 2; $i -= 1) {
                                            ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="status" value="SUDAH BAYAR">
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
<?php } ?>
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold"> Data Tagihan <span id="area"></span></h6>

    </div>
    <div class="card-body">
        <a href="#" id="#filterbyModal" data-toggle="modal" data-target="#filterbyModal" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-cube fa-sm text-white-50"></i> Filter by</a>
        <div class="row mb-2 mt-2">
            <div class="dropdown">
                <button class="btn btn-outline-success dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="false">
                    Action
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <button href="" class="btn btn-outline-secondary dropdown-item" id="btn-cetak"><i class="fa fa-print"></i> A4 Yang Dipilih</button>
                    <button href="" class="btn btn-outline-secondary dropdown-item" id="btn-cetak-matrix"><i class="fa fa-print"></i> Dot Metrix Yang Dipilih</button>
                    <button href="" class="btn btn-outline-secondary  dropdown-item" id="btn-cetak-thermal"><i class="fa fa-print"></i> Thermal Yang Dipilih</button>
                    <button href="" class="btn btn-outline-secondary  dropdown-item" id="btn-cetak-small"><i class="fa fa-print"></i> Small Yang Dipilih</button>

                    <?php if ($this->session->userdata('role_id') == 1 or $role['del_bill'] == 1) { ?>

                        <button href="" class="btn btn-outline-danger dropdown-item" id="btn-del-selected"><i class="fa fa-trash"></i> Hapus Yang Dipilih</button>

                    <?php } ?>
                </div>
            </div>

        </div>
        <div class="loading"></div>
        <div class="view_data"></div>
        <div class="table-responsive" id="tablebill" style="display:<?= $totalcustomer >= 500 ? 'none' : '' ?>">
            <form method="post" action="<?php echo base_url('bill/printinvoiceselected') ?>" id="submit-cetak">
                <form method="post" action="<?php echo base_url('bill/printinvoiceselected') ?>" id="submit-cetak-thermal">
                    <!-- <input type="hidden" name='invoice[]' id="result" size="60"> -->
                    <table class="table table-bordered" id="example" width="100%" cellspacing="0">
                        <thead>
                            <tr style="text-align: center">
                                <th style="text-align: center; width:20px">No</th>
                                <th>
                                    <input type='checkbox' class='check-item' id="selectAll">
                                </th>

                                <th>Nama Pelanggan</th>

                                <th>No Layanan - No Invoice</th>

                                <th>Periode</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Coverage</th>
                                <th>IP</th>
                                <th style="text-align: center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </form>
        </div>
    </div>
</div>
<div class="modal fade" id="filterbyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Filter By</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php echo form_open_multipart('bill/filter') ?>
                <div class="form-group">
                    <label for="month">Bulan</label>
                    <select class="form-control select2" style="width: 100%;" name="month" required>
                        <option value="<?= date('m') ?>"><?= indo_month(date('m')) ?></option>
                        <option value="01">Januari</option>
                        <option value="02">Februari</option>
                        <option value="03">Maret</option>
                        <option value="04">April</option>
                        <option value="05">Mei</option>
                        <option value="06">Juni</option>
                        <option value="07">Juli</option>
                        <option value="08">Agustus</option>
                        <option value="09">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="year">Tahun</label>
                    <select class="form-control " style="width: 100%;" type="text" id="year" name="year" autocomplete="off" required>
                        <option value="<?= date('Y') ?>"><?= date('Y') ?></option>
                        <?php if ($this->session->userdata('role_id') == 1) {  ?>
                            <?php
                            for ($i = date('Y') + 1; $i >= date('Y') - 2; $i -= 1) {
                            ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php } ?>
                        <?php } ?>
                        <?php if (date('m') == 12) {  ?>
                            <?php
                            for ($i = date('Y') + 1; $i >= date('Y') - 2; $i -= 1) {
                            ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php } ?>
                        <?php } ?>
                        <?php if (date('m') < 12) {  ?>
                            <?php
                            for ($i = date('Y'); $i >= date('Y') - 2; $i -= 1) {
                            ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control " style="width: 100%;" type="text" id="status" name="status" autocomplete="off">
                        <option value="">-Pilih-</option>
                        <option value="BELUM BAYAR">BELUM BAYAR</option>
                        <option value="SUDAH BAYAR">SUDAH BAYAR</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Jenis Bayar</label>
                    <select class="form-control " style="width: 100%;" type="text" id="jenis_bayar" name="jenis_bayar" autocomplete="off">
                        <option value="">-Pilih-</option>
                        <option value="Cash">CASH</option>
                        <option value="Transfer">TRANSFER</option>
                        <option value="Payment Gateway">PAYMENT GATEWAY</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="coverage">Coverage Area</label>
                    <select name="coverage[]" id="coverage" multiple="multiple" style="width: 100%;" class="form-control select2">
                        <option value="0">-Semua-</option>
                        <?php foreach ($coverage as $data) { ?>
                            <option value="<?= $data->coverage_id ?>"><?= $data->c_name ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <?php $listrouter = $this->db->get('router')->result() ?>
                    <label for="router">Router</label>
                    <select name="router" id="router" class="form-control">
                        <option value="0">-Semua-</option>
                        <?php foreach ($listrouter as $router) { ?>
                            <option value="<?= $router->id ?>"><?= $router->alias; ?></option>
                        <?php } ?>

                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
                <?php echo form_close() ?>
            </div>
        </div>
    </div>
</div>
<!-- Modal Add -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Tambah Tagihan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php echo form_open_multipart('bill/addBill') ?>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="name">Bulan</label>
                            <input type="" name="invoice" value="<?= $invoice ?>">
                            <select class="form-control select2" style="width: 100%;" name="month" required>
                                <option value="<?= date('m') ?>"><?= indo_month(date('m')) ?></option>
                                <option value="01">Januari</option>
                                <option value="02">Februari</option>
                                <option value="03">Maret</option>
                                <option value="04">April</option>
                                <option value="05">Mei</option>
                                <option value="06">Juni</option>
                                <option value="07">Juli</option>
                                <option value="08">Agustus</option>
                                <option value="09">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="name">Tahun</label>
                            <select class="form-control select2" style="width: 100%;" name="year" required>
                                <option value="<?= date('Y') ?>"><?= date('Y') ?></option>
                                <?php if ($this->session->userdata('role_id') == 1) {  ?>
                                    <?php
                                    for ($i = date('Y') + 1; $i >= date('Y') - 2; $i -= 1) {
                                    ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php } ?>
                                <?php } ?>
                                <?php if (date('m') == 12) {  ?>
                                    <?php
                                    for ($i = date('Y') + 1; $i >= date('Y') - 2; $i -= 1) {
                                    ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php } ?>
                                <?php } ?>
                                <?php if (date('m') < 12) {  ?>
                                    <?php
                                    for ($i = date('Y'); $i >= date('Y') - 2; $i -= 1) {
                                    ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="name">No Layanan - Nama Pelanggan</label>
                    <select class="form-control select2" name="no_services" id="no_services" onchange="cek_data()" style="width: 100%;" required>
                        <option value="">-Pilih-</option>
                        <?php
                        foreach ($customer as $r => $data) { ?>
                            <option value="<?= $data->no_services ?>"><?= $data->no_services ?> - <?= $data->name ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nominal">Rincian Tagihan</label>
                    <div class="loading"></div>
                    <div class="view_data"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
                <?php echo form_close() ?>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="ModalBayar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Bayar Tagihan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php echo form_open_multipart('bill/billpaid') ?>

                <input type="hidden" id="no_servicess" name="no_services" class="form-control">
                <input type="hidden" id="invoice_id" name="invoice_id" class="form-control">
                <input type="hidden" id="invoice" name="invoice" class="form-control">
                <input type="hidden" id="monthh" name="month" class="form-control">
                <input type="hidden" id="name" name="name" class="form-control">
                <input type="hidden" id="email_customer" name="email_customer" class="form-control">
                <input type="hidden" id="periode" name="periode" class="form-control">
                <input type="hidden" id="agen" name="agen" value="<?= $user['name'] ?>" class="form-control">
                <input type="hidden" id="email_agen" name="email_agen" value="<?= $this->session->userdata('email') ?>" class="form-control">
                <input type="hidden" id="to_email" name="to_email" value="<?= $company['email'] ?>" class="form-control">
                <input type="hidden" id="yearr" name="year" class="form-control">
                <input type="hidden" id="date_payment" name="date_payment" value="<?= date('Y-m-d') ?>" class="form-control">
                <!-- PPN -->

                <input type="hidden" id="nominal" name="nominal" class="form-control">

                Apakah yakin tagihan dengan no layanan <span id="servic"></span> a/n <span id="nam"></span> Periode <span id="peri"></span> sudah terbayarkan ?,

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Ya, Lanjutkan</button>
                </div>
                <?php echo form_close() ?>
            </div>

        </div>
    </div>
</div>
<!-- Modal Hapus -->


<div class="modal fade" id="PrintModalUnpaid" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cetak Invoice</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row container mb-3">
                    Pilih Ukuran Kertas
                </div>
                <div class="row text-center">
                    <div class="col">
                        <a id="noinvoice" href="<?= site_url('bill/printinvoice') ?>" target="blank" class="btn btn-outline-danger" style="font-size: smaller"><i class="fa fa-print"> A4</i></a>
                    </div>
                    <div class="col">
                        <a id="noinvoicethermal" href="<?= site_url('bill/printinvoicethermal') ?>" target="blank" class="btn btn-outline-danger" style="font-size: smaller"><i class="fa fa-print"> Thermal</i></a>
                    </div>
                    <div class="col">
                        <a id="noinvoicedot" href="<?= site_url('bill/printinvoicedotmatrix') ?>" target="blank" class="btn btn-outline-danger" style="font-size: smaller"><i class="fa fa-print"> Dot Matrix</i></a>
                    </div>
                    <div class="col">
                        <a id="noinvoicesmall" href="<?= site_url('bill/printinvoicesmall') ?>" target="blank" class="btn btn-outline-danger" style="font-size: smaller"><i class="fa fa-print"> Small</i></a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="PrintModalPaid" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cetak Invoice</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row container mb-3">
                    Pilih Ukuran Kertas
                </div>
                <div class="row text-center">
                    <div class="col">
                        <a id="printpaida4" href="<?= site_url('bill/printinvoicepaid') ?>" target="blank" class="btn btn-outline-success" style="font-size: smaller"><i class="fa fa-print"> A4</i></a>
                    </div>
                    <div class="col">
                        <a id="printpaidthermal" href="<?= site_url('bill/printinvoicepaidthermal') ?>" target="blank" class="btn btn-outline-success" style="font-size: smaller"><i class="fa fa-print"> Thermal</i></a>
                    </div>
                    <div class="col">
                        <a id="printpaiddot" href="<?= site_url('bill/printinvoicepaidthermal') ?>" target="blank" class="btn btn-outline-success" style="font-size: smaller"><i class="fa fa-print"> Dot Matrix</i></a>
                    </div>
                    <div class="col">
                        <a id="printpaidsmall" href="<?= site_url('bill/printinvoicesmall') ?>" target="blank" class="btn btn-outline-success" style="font-size: smaller"><i class="fa fa-print"> Small</i></a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="billGenerate" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Generate Tagihan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php echo form_open_multipart('bill/generateBill') ?>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="name">Bulan</label>
                            <input type="" name="invoice" value="<?= $invoice ?>">
                            <select class="form-control select2" style="width: 100%;" name="month" required>
                                <option value="<?= date('m') ?>"><?= indo_month(date('m')) ?></option>
                                <option value="01">Januari</option>
                                <option value="02">Februari</option>
                                <option value="03">Maret</option>
                                <option value="04">April</option>
                                <option value="05">Mei</option>
                                <option value="06">Juni</option>
                                <option value="07">Juli</option>
                                <option value="08">Agustus</option>
                                <option value="09">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="name">Tahun</label>
                            <select class="form-control select2" style="width: 100%;" name="year" required>
                                <option value="<?= date('Y') ?>"><?= date('Y') ?></option>
                                <?php if ($this->session->userdata('role_id') == 1) {  ?>
                                    <?php
                                    for ($i = date('Y') + 1; $i >= date('Y') - 2; $i -= 1) {
                                    ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php } ?>
                                <?php } ?>
                                <?php if (date('m') == 12) {  ?>
                                    <?php
                                    for ($i = date('Y') + 1; $i >= date('Y') - 2; $i -= 1) {
                                    ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php } ?>
                                <?php } ?>
                                <?php if (date('m') < 12) {  ?>
                                    <?php
                                    for ($i = date('Y'); $i >= date('Y') - 2; $i -= 1) {
                                    ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Ya, Lanjutkan</button>
                </div>
                <?php echo form_close() ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="DeleteModalBayar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Hapus Tagihan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php echo form_open_multipart('bill/delete') ?>
                <input type="hidden" id="invoice_iddd" name="invoice_id" class="form-control">
                <input type="hidden" id="invoiceee" name="invoice" class="form-control">
                <input type="hidden" id="monthhhh" name="month" class="form-control">
                <input type="hidden" id="yearrrr" name="year" class="form-control">
                <input type="hidden" id="no_servicessss" name="no_services" class="form-control">

                Apakah yakin akan hapus tagihan <span id="noservicess"></span> <span id="periodeee"> </span> Periode <span id="periodd"></span> A/N <span id="nameee"></span> ?
                <br>
                <br>
                <input type="checkbox" id="clickdelincome"> <label for="">Hapus Data Pemasukan</label>
                <input type="hidden" name="delincome" id="delincome">
                <br>
                <div id="formdelincome" style="display: none">
                    <span style="color: red;">Penghapusan data pemasukan akan mempengaruhi Saldo Kas dan Data Pemasukan</span>
                    <br>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
                <?php echo form_close() ?>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="DeleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Hapus Tagihan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php echo form_open_multipart('bill/delete') ?>
                <input type="hidden" id="invoice_idd" name="invoice_id" class="form-control">
                <input type="hidden" id="invoicee" name="invoice" class="form-control">
                <input type="hidden" id="monthhh" name="month" class="form-control">
                <input type="hidden" id="yearrr" name="year" class="form-control">
                <input type="hidden" id="no_servicesss" name="no_services" class="form-control">

                Apakah yakin akan hapus tagihan <span id="noservices"></span> <span id="periodee"> </span> Periode <span id="period"></span> A/N <span id="namee"></span> ?
                <br>
                <br>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
                <?php echo form_close() ?>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="cetakblmbayar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cetak Invoice</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row container mb-3">
                    Pilih Ukuran Kertas
                </div>
                <div class="row text-center">
                    <div class="col">
                        <a href="<?= site_url('bill/printinvoiceunpaid') ?>" class="btn btn-outline-danger" style="font-size: smaller"><i class="fa fa-print"> A4</i></a>
                    </div>
                    <div class="col">
                        <a href="<?= site_url('bill/printinvoiceunpaidthermal') ?>" class="btn btn-outline-danger" style="font-size: smaller"><i class="fa fa-print"> Thermal</i></a>
                    </div>
                    <div class="col">
                        <a href="<?= site_url('bill/printunpaid') ?>" class="btn btn-outline-danger" style="font-size: smaller"><i class="fa fa-print"> Print Table</i></a>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        //Initialize Select2 Elements
        $('.select2').select2()
    });

    function cek_data() {
        no_services = $('[name="no_services"]');
        $.ajax({
            type: 'POST',
            data: "cek_data=" + 1 + "&no_services=" + no_services.val(),
            url: '<?= site_url('bill/view_data') ?>',
            cache: false,

            beforeSend: function() {
                no_services.attr('disabled', true);
                $('.loading').html(` <div class="container">
        <div class="text-center">
            <div class="spinner-border text-primary" style="width: 5rem; height: 5rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>`);
            },
            success: function(data) {
                no_services.attr('disabled', false);
                $('.loading').html('');
                $('.view_data').html(data);
            }

        });
        return false;
    }
</script>
<script>
    $(document).ready(function() {
        $("#selectAll").click(function() {
            if ($(this).is(":checked"))
                $(".check-item").prop("checked", true);
            else // Jika checkbox all tidak diceklis
                $(".check-item").prop("checked", false);
        });


        $("#btn-cetak").click(function() {
            $('#submit-cetak').attr('action', '<?php echo base_url('bill/printinvoiceselected') ?> ').attr('target', '_blank');
            var confirm = window.confirm("Apakah Anda yakin ingin cetak tagihan yang terpilih ?");
            if (confirm) {
                $("#submit-cetak").submit();
                // $("#popup").modal("show");
            } else {

            }

        });
        $("#btn-cetak-matrix").click(function() {
            $('#submit-cetak').attr('action', '<?php echo base_url('bill/printinvoicedotmatrixselected') ?>').attr('target', '_blank');
            var confirm = window.confirm("Apakah Anda yakin ingin cetak tagihan yang terpilih ?");
            if (confirm) {
                $("#submit-cetak").submit();
                // $("#popup").modal("show");
            } else {

            }
        });
        $('#btn-cetak-thermal').click(function() {
            $('#submit-cetak').attr('action', '<?php echo base_url('bill/printinvoiceselectedthermal') ?>').attr('target', '_blank');
            var confirm = window.confirm("Apakah Anda yakin ingin cetak tagihan yang terpilih ?");
            if (confirm) {
                $("#submit-cetak").submit();
                // $("#popup").modal("show");
            } else {

            }
        });
        $('#btn-cetak-small').click(function() {
            $('#submit-cetak').attr('action', '<?php echo base_url('bill/printinvoiceselectedsmall') ?>').attr('target', '_blank');
            var confirm = window.confirm("Apakah Anda yakin ingin cetak tagihan yang terpilih ?");
            if (confirm) {
                $("#submit-cetak").submit();
                // $("#popup").modal("show");
            } else {

            }
        });
        $('#btn-pay-selected').click(function() {
            $('#submit-cetak').attr('action', '<?php echo base_url('bill/payselected') ?>').attr('target', '');
            var confirm = window.confirm("Apakah Anda yakin ingin cetak tagihan yang terpilih ?");
            if (confirm) {
                $("#submit-cetak").submit();
                $("#popup").modal("show");
            } else {

            }
        });
        $('#btn-del-selected').click(function() {
            $('#submit-cetak').attr('action', '<?php echo base_url('bill/delselected') ?>').attr('target', '');
            var confirm = window.confirm("Apakah Anda yakin ingin hapus tagihan yang terpilih ?");
            if (confirm) {
                $("#submit-cetak").submit();
                $("#popup").modal("show");
            } else {

            }
        });
    });
</script>
<?php if ($totalcustomer < 500) { ?>
    <script>
        $(document).ready(function() {
            $('#example').DataTable({
                "processing": true,
                "serverSide": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "info": true,
                "autoWidth": true,
                "responsive": false,
                "order": [],
                "ajax": {
                    "url": "<?= base_url('bill/getPaidInvoice'); ?>",
                    "type": "POST"
                },
                "lengthMenu": [
                    [50, 100, 250, 500, 1000],
                    [50, 100, 250, 500, 1000]
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

                "language": {
                    "emptyTable": "Tidak ada data",
                    "zeroRecords": "Tidak ada data",
                    "info": "Showing <b>_START_</b> to <b>_END_ of _TOTAL_</b> entries",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    },
                    search: "_INPUT_",
                    searchPlaceholder: "No Layanan, Nama, IP"
                },
                "columnDefs": [{
                    "targets": [0, 1, 8, 9],
                    "orderable": false
                }]
            });
        });
    </script> <?php } ?>
<script>
    $(document).on('click', '#bayar', function() {
        $('#no_servicess').val($(this).data('no_servicess'))
        $('#invoice_id').val($(this).data('invoice_id'))
        $('#invoice').val($(this).data('invoice'))
        $('#monthh').val($(this).data('month'))
        $('#name').val($(this).data('name'))
        $('#email_customer').val($(this).data('email_customer'))
        $('#periode').val($(this).data('periode'))
        $('#yearr').val($(this).data('yearr'))
        $('#nominal').val($(this).data('nominal'))
        $('#nam').html($(this).data('name'))
        $('#servic').html($(this).data('no_servicess'))
        $('#peri').html($(this).data('periode'))
    })
</script>
<script>
    $(document).on('click', '#hapusmodalbayar', function() {
        $('#no_servicessss').val($(this).data('no_servicess'))
        $('#invoice_iddd').val($(this).data('invoice_id'))
        $('#invoiceee').val($(this).data('invoice'))
        $('#monthhhh').val($(this).data('month'))
        $('#noservicess').html($(this).data('no_servicess'))
        $('#nameee').html($(this).data('name'))
        $('#periodd').html($(this).data('periode'))

        $('#yearrrr').val($(this).data('yearr'))

    })
    $(document).on('click', '#hapusmodal', function() {
        $('#no_servicesss').val($(this).data('no_servicess'))
        $('#invoice_idd').val($(this).data('invoice_id'))
        $('#invoicee').val($(this).data('invoice'))
        $('#monthhh').val($(this).data('month'))
        $('#noservices').html($(this).data('no_servicess'))
        $('#namee').html($(this).data('name'))
        $('#period').html($(this).data('periode'))

        $('#yearrr').val($(this).data('yearr'))

    })
    $(document).on('click', '#printinvoice', function() {
        var invoice = $(this).data('printinvoice')
        $('#noinvoice').attr('href', '<?php echo base_url('bill/printinvoice/') ?>' + invoice);
        $('#noinvoicethermal').attr('href', '<?php echo base_url('bill/printinvoicethermal/') ?>' + invoice);
        $('#noinvoicedot').attr('href', '<?php echo base_url('bill/printinvoicedotmatrix/') ?>' + invoice);
        $('#printpaida4').attr('href', '<?php echo base_url('bill/printinvoice/') ?>' + invoice);
        $('#printpaidthermal').attr('href', '<?php echo base_url('bill/printinvoicethermal/') ?>' + invoice);
        $('#printpaiddot').attr('href', '<?php echo base_url('bill/printinvoicedotmatrix/') ?>' + invoice);
        $('#printpaidsmall').attr('href', '<?php echo base_url('bill/printinvoicesmall/') ?>' + invoice);
    })
</script>
<script>
    $("#clickdelincome").click(function() {
        if ($(this).is(":checked")) {
            $("#delincome").val('1');
            $("#formdelincome").show();
        } else {
            $("#delincome").val('0');
            $("#formdelincome").hide();
        }
    });
</script>
<?php if ($totalcustomer >= 500) { ?>
    <script>
        $(document).ready(function() {

            var coverage = $("#coverage").val();
            var status = $("#status").val();
            var month = $("#month").val();
            var year = $("#year").val();
            $.ajax({
                type: 'POST',
                data: "&coverage=" + coverage + "&status=" + status + "&month=" + month + "&year=" + year,
                cache: false,
                url: '<?= site_url('bill/getdatabillcoverage') ?>',
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
                    $('#tablebill').hide();
                }
            });
            return false;

        });
    </script>
<?php } ?>
<script>
    function getdatabill() {
        var coverage = $("#coverage").val();
        var status = $("#status").val();
        var month = $("#month").val();
        var year = $("#year").val();
        // alert(month);
        // alert(year);
        $.ajax({
            type: 'POST',
            data: "&coverage=" + coverage + "&status=" + status + "&month=" + month + "&year=" + year,
            cache: false,
            url: '<?= site_url('bill/getdatabillcoverage') ?>',
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
                $('#tablebill').hide();
            }
        });
        return false;
    }
</script>