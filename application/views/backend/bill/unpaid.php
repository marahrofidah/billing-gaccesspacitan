<!-- Page Heading -->
<?php $role = $this->db->get_where('role_management', ['role_id' => $this->session->userdata('role_id')])->row_array() ?>
<?php $menu = $this->db->get_where('role_menu', ['role_id' => $this->session->userdata('role_id')])->row_array() ?>
<?php $billFilter = isset($bill_filter) && is_array($bill_filter) ? $bill_filter : []; ?>
<?php $selectedMonth = isset($billFilter['month']) ? (string) $billFilter['month'] : date('m'); ?>
<?php $selectedYear = isset($billFilter['year']) ? (string) $billFilter['year'] : date('Y'); ?>
<?php $selectedCoverage = isset($billFilter['coverage']) && is_array($billFilter['coverage']) ? $billFilter['coverage'] : []; ?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <?php if ($this->session->userdata('role_id') == 1 or $role['add_bill'] == 1) { ?>
        <a href="" id="#addModal" data-toggle="modal" data-target="#addModal" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Tambah</a>
        <!-- <a href="#" id="createva" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Create VA BRI</a> -->
    <?php } ?>
</div>
<?php $this->view('messages') ?>
<?php $totalcustomer = $this->db->get_where('customer', ['c_status' => 'Aktif'])->num_rows(); ?>


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
                                    <select class="form-control select2" name="coverage[]" multiple="multiple" id="coverage" onchange="getdatabill()" required>
                                        <option value="all">Semua</option>
                                        <?php
                                        foreach ($coverage as $r => $data) { ?>
                                            <option value="<?= $data->coverage_id ?>" <?= in_array((string) $data->coverage_id, $selectedCoverage, true) ? 'selected' : '' ?>><?= $data->code_area ?> - <?= $data->c_name ?> </option>
                                        <?php } ?>
                                        <?php if ($this->session->userdata('role_id') == 1) { ?>
                                            <option value="" <?= in_array('', $selectedCoverage, true) ? 'selected' : '' ?>>Tanpa Area</option>
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
                                        <option value="<?= date('m') ?>" <?= $selectedMonth === date('m') ? 'selected' : '' ?>><?= indo_month(date('m')); ?></option>
                                        <option value="01" <?= $selectedMonth === '01' ? 'selected' : '' ?>>Januari</option>
                                        <option value="02" <?= $selectedMonth === '02' ? 'selected' : '' ?>>Februari</option>
                                        <option value="03" <?= $selectedMonth === '03' ? 'selected' : '' ?>>Maret</option>
                                        <option value="04" <?= $selectedMonth === '04' ? 'selected' : '' ?>>April</option>
                                        <option value="05" <?= $selectedMonth === '05' ? 'selected' : '' ?>>Mei</option>
                                        <option value="06" <?= $selectedMonth === '06' ? 'selected' : '' ?>>Juni</option>
                                        <option value="07" <?= $selectedMonth === '07' ? 'selected' : '' ?>>Juli</option>
                                        <option value="08" <?= $selectedMonth === '08' ? 'selected' : '' ?>>Agustus</option>
                                        <option value="09" <?= $selectedMonth === '09' ? 'selected' : '' ?>>September</option>
                                        <option value="10" <?= $selectedMonth === '10' ? 'selected' : '' ?>>Oktober</option>
                                        <option value="11" <?= $selectedMonth === '11' ? 'selected' : '' ?>>November</option>
                                        <option value="12" <?= $selectedMonth === '12' ? 'selected' : '' ?>>Desember</option>
                                        <option value="" <?= $selectedMonth === '' ? 'selected' : '' ?>>Semua</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-12 col-md-6">
                            <div class="d-sm-flex align-items-center justify-content-between ">
                                <div class="form-group">
                                    <label for="">Tahun</label>
                                    <select class="form-control" style="width: 100%;" name="year" id="year" onchange="getdatabill()">
                                        <option value="<?= date('Y') ?>" <?= $selectedYear === date('Y') ? 'selected' : '' ?>><?= date('Y') ?></option>
                                        <?php if ($this->session->userdata('role_id') == 1) {  ?>
                                            <?php
                                            for ($i = date('Y') + 1; $i >= date('Y') - 2; $i -= 1) {
                                            ?>
                                                <option value="<?= $i ?>" <?= $selectedYear === (string) $i ? 'selected' : '' ?>><?= $i ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if (date('m') == 12) {  ?>
                                            <?php
                                            for ($i = date('Y') + 1; $i >= date('Y') - 2; $i -= 1) {
                                            ?>
                                                <option value="<?= $i ?>" <?= $selectedYear === (string) $i ? 'selected' : '' ?>><?= $i ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if (date('m') < 12) {  ?>
                                            <?php
                                            for ($i = date('Y'); $i >= date('Y') - 2; $i -= 1) {
                                            ?>
                                                <option value="<?= $i ?>" <?= $selectedYear === (string) $i ? 'selected' : '' ?>><?= $i ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="status" value="BELUM BAYAR">
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

<!-- DataTales Example -->
<div class="card shadow mb-2">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold"> Data Tagihan <span id="area"></span></h6>
    </div>

    <div class="card-body">
        <div class="row mb-2">
            <div class="dropdown">
                <button class="btn btn-outline-danger dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="false">
                    Action
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <button href="" class="btn btn-outline-secondary dropdown-item" id="btn-cetak"><i class="fa fa-print"></i> A4 Yang Dipilih</button>
                    <button href="" class="btn btn-outline-secondary dropdown-item" id="btn-cetak-matrix"><i class="fa fa-print"></i> Dot Metrix Yang Dipilih</button>
                    <button href="" class="btn btn-outline-secondary  dropdown-item" id="btn-cetak-thermal"><i class="fa fa-print"></i> Thermal Yang Dipilih</button>
                    <button href="" class="btn btn-outline-secondary  dropdown-item" id="btn-cetak-small"><i class="fa fa-print"></i> Small Yang Dipilih</button>
                    <a href="" data-toggle="modal" data-target="#cetakblmbayar" class="btn btn-outline-danger dropdown-item"><i class="fa fa-print"></i> Semua</a>
                    <button href="" class="btn btn-outline-primary dropdown-item" id="btn-pay-selected"><i class="fa fa-credit-card"></i> Bayar Yang Dipilih</button>
                    <?php $wa = $this->db->get('whatsapp')->row_array() ?>
                    <?php if ($wa['is_active'] == 1) { ?>
                        <button href="" class="dropdown-item " id="btn-wa-selected"> <i class="fab fa-whatsapp"></i> Kirim Tagihan Yang Dipilih</button>
                    <?php } ?>
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
                            <th>Jatuh Tempo</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Coverage</th>
                            <th>Keterangan</th>
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
                            <input type="hidden" name="invoice" value="<?= $invoice ?>">
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
                                <?php if (date('m') == 12 or $this->session->userdata('role_id') != 1) {  ?>
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
                    <label for="">Diskon</label>
                    <input type="number" name="discount" id="discount" class="form-control">
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
                    <div class="view_data_bill"></div>
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
                <?php echo form_open_multipart('bill/billpaid', ['id' => 'bill-paid-form']) ?>

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
                <input type="hidden" id="return_coverage" name="return_coverage">
                <input type="hidden" id="return_month" name="return_month">
                <input type="hidden" id="return_year" name="return_year">
                <input type="hidden" id="return_search" name="return_search">
                <!-- PPN -->



                Apakah yakin tagihan dengan no layanan <span id="servic"></span> a/n <span id="nam"></span> Periode <span id="peri"></span> sudah terbayarkan ?,


                <div class="form-group mt-2">
                    <label for="">Nominal</label>
                    <input type="hidden" id="nominal" name="nominal" class="form-control">
                    <input type="text" id="shownominal" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label for="">Metode Pembayaran</label>
                    <select name="metode_payment" id="" class="form-control" required>
                        <option value="">-Pilih-</option>
                        <option value="Cash"> Cash </option>
                        <option value="Transfer">Transfer</option>
                        <option value="Payment Gateway">Payment Gateway</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="">Kategori</label>
                    <select name="category" id="" class="form-control" required>
                        <?php $category = $this->db->get('cat_income')->result() ?>
                        <?php foreach ($category as $data) { ?>
                            <option value="<?= $data->category_id ?>"><?= $data->name ?></option>
                        <?php } ?>
                    </select>
                </div>
                <?php if ($this->session->userdata('role_id') == 1) { ?>
                    <div class="form-group">
                        <label for="">Diterima Oleh</label>
                        <select name="create_by" class="form-control select2" style="width: 100%;" required>
                            <option value="<?= $this->session->userdata('id') ?>"><?= $this->session->userdata('name') ?></option>
                            <?php $receipt = $this->bill_m->getreceipt()->result() ?>
                            <?php foreach ($receipt as $data) { ?>
                                <option value="<?= $data->id ?>"><?= $data->name ?> -
                                    <?= $data->role_id == 1 ? 'Admin' : '' ?>
                                    <?= $data->role_id == 2 ? 'Pelanggan' : '' ?>
                                    <?= $data->role_id == 3 ? 'Operator' : '' ?>
                                    <?= $data->role_id == 4 ? 'Mitra' : '' ?>
                                    <?= $data->role_id == 5 ? 'Teknisi' : '' ?>
                                    <?= $data->role_id == 6 ? 'Outlet' : '' ?>
                                    <?= $data->role_id == 7 ? 'Kolektor' : '' ?>
                                    <?= $data->role_id == 8 ? 'Finance' : '' ?></option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>
                <?php if ($this->session->userdata('role_id') != 1) { ?>
                    <input type="hidden" name="create_by" value="<?= $this->session->userdata('id') ?>">
                <?php } ?>
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
                        <a id="printpaida4" href="<?= site_url('bill/printinvoicepaid') ?>" class="btn btn-outline-success" style="font-size: smaller"><i class="fa fa-print"> A4</i></a>
                    </div>
                    <div class="col">
                        <a id="printpaidthermal" href="<?= site_url('bill/printinvoicepaidthermal') ?>" class="btn btn-outline-success" style="font-size: smaller"><i class="fa fa-print"> Thermal</i></a>
                    </div>
                    <div class="col">
                        <a id="printpaiddot" href="<?= site_url('bill/printinvoicepaidthermal') ?>" class="btn btn-outline-success" style="font-size: smaller"><i class="fa fa-print"> Dot Matrix</i></a>
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
                    <!-- <div class="col">
                        <a href="<?= site_url('bill/printunpaid') ?>" class="btn btn-outline-danger" style="font-size: smaller"><i class="fa fa-print"> Print Table</i></a>
                    </div> -->

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>
    <script>
        $(document).ready(function() {

            var unpaidBillFilter = <?= json_encode($billFilter, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
            var restoreUnpaidSearch = Object.keys(unpaidBillFilter).length > 0;
            var initialSearch = restoreUnpaidSearch ? (unpaidBillFilter.search || '') : '';
            if (restoreUnpaidSearch) {
                $('#coverage').val(unpaidBillFilter.coverage || []);
                $('#month').val(unpaidBillFilter.month);
                $('#year').val(unpaidBillFilter.year);
            }
            var coverage = $("#coverage").val();
            var status = $("#status").val();
            var month = $("#month").val();
            var year = $("#year").val();
            $.ajax({
                type: 'POST',
                data: {
                    coverage: coverage,
                    status: status,
                    month: month,
                    year: year,
                    search: initialSearch
                },
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
                    prepareUnpaidFilteredTable();
                    $('.view_data').html(data);
                    $('#tablebill').hide();
                }
            });
            return false;

        });
    </script>

<script>
    $(function() {
        //Initialize Select2 Elements
        $('.select2').select2()
    });

    function cek_data() {
        var no_services = $('#no_services').val();
        var discount = $('#discount').val();

        $.ajax({
            type: 'POST',
            data: "no_services=" + no_services + "&discount=" + discount,
            url: '<?= site_url('bill/view_data') ?>',
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
                $('.view_data_bill').html(data);
            }

        });
        return false;
    }
</script>
<script>
    $(document).ready(function() {

        $("#click-me").click(function() {
            $("#popup").modal("show");
            $("#billGenerate").modal("hide");
            $("#DeleteModal").modal("hide");
            $("#ModalBayar").modal("hide");

        });
        $("#selectAll").click(function() {
            if ($(this).is(":checked"))
                $(".check-item").prop("checked", true);
            else // Jika checkbox all tidak diceklis
                $(".check-item").prop("checked", false);
        });


        $("#btn-cetak").click(function() {
            $('#submit-cetak').attr('action', '<?php echo base_url('bill/printinvoiceselected') ?>').attr('target', '_blank');
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
        $('#btn-wa-selected').click(function() {
            $('#submit-cetak').attr('action', '<?php echo base_url('bill/sendwaselected') ?>').attr('target', '');
            var confirm = window.confirm("Apakah Anda yakin akan kirim tagihan yang terpilih ?");
            if (confirm) {
                $("#submit-cetak").submit();
                $("#popup").modal("show");
            } else {

            }

        });

    });
</script>
<script>
    $(document).on('submit', '#bill-paid-form', function() {
        var searchInput = $('.view_data #example_filter input');
        if (!searchInput.length) {
            searchInput = $('#tablebill #example_filter input');
        }
        $('#return_coverage').val(JSON.stringify($('#coverage').val() || []));
        $('#return_month').val($('#month').val());
        $('#return_year').val($('#year').val());
        $('#return_search').val(searchInput.val() || '');
        $('#popup').modal({
            backdrop: 'static',
            keyboard: false
        });
        $('#ModalBayar').modal('hide');
    });

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
        $('#shownominal').val($(this).data('shownominal'))
        $('#nam').html($(this).data('name'))
        $('#servic').html($(this).data('no_servicess'))
        $('#peri').html($(this).data('periode'))
    })
</script>
<script>
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
        $('#noinvoicesmall').attr('href', '<?php echo base_url('bill/printinvoicesmall/') ?>' + invoice);
        $('#printpaida4').attr('href', '<?php echo base_url('bill/printinvoice/') ?>' + invoice);
        $('#printpaidthermal').attr('href', '<?php echo base_url('bill/printinvoicethermal/') ?>' + invoice);
        $('#printpaiddot').attr('href', '<?php echo base_url('bill/printinvoicedotmatrix/') ?>' + invoice);
    })
</script>
<script>
    function prepareUnpaidFilteredTable() {
        $('#tablebill #example, .view_data #example').each(function() {
            if ($.fn.DataTable.isDataTable(this)) {
                $(this).DataTable().destroy();
            }
        });
        $('#tablebill').remove();
    }

    function getdatabill() {
        var coverage = $("#coverage").val();
        var status = $("#status").val();
        var month = $("#month").val();
        var year = $("#year").val();
        prepareUnpaidFilteredTable();
        // alert(month);
        // alert(year);
        $.ajax({
            type: 'POST',
            data: {
                coverage: coverage,
                status: status,
                month: month,
                year: year
            },
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
                prepareUnpaidFilteredTable();
                $('.view_data').html(data);
                $('#tablebill').hide();
            }
        });
        return false;
    }
</script>
<script>
    $(document).ready(function() {
        $.ajax({
            type: 'get',
            url: '<?= site_url('bill/fixduedate') ?>',
            cache: false,
            success: function(data) {

            }
        });
        $.ajax({
            type: 'get',
            url: '<?= site_url('whatsapp/sendduedate') ?>',
            cache: false,
            success: function(data) {}
        });
        $.ajax({
            type: 'get',
            url: '<?= site_url('whatsapp/sendbeforeduedate') ?>',
            cache: false,
            success: function(data) {}
        });
        $.ajax({
            type: 'get',
            url: '<?= site_url('whatsapp/resendbill') ?>',
            cache: false,
            success: function(data) {}
        });

        return false;
    })
</script>