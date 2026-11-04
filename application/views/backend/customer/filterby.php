<!-- Page Heading -->
<?php $role = $this->db->get_where('role_management', ['role_id' => $this->session->userdata('role_id')])->row_array() ?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">

    <?php
    $customerall = $this->db->get('customer')->result();
    foreach ($customerall as $result) {
        $row = array();
        $row = $result->no_services;
        $dataaa[] = $row;
    }
    $count_valuess = array_count_values($dataaa);
    function findDuplicatess($count)
    {
        return $count > 1;
    }
    $duplicatess = array_filter(array_count_values($dataaa), "findDuplicatess");
    // $noservices = json_encode($duplicatess);

    ?>
    <?php if (count($duplicatess) == 0) { ?>
        <?php if ($company['import'] == 1) { ?>
            <a href="<?= site_url('customer/import') ?>" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm"><i class="fas fa-file-excel fa-sm text-white-50"></i> Import</a>
        <?php } ?>
    <?php } ?>

    <?php if ($this->session->userdata('role_id') == 1 or $role['add_customer'] == 1) { ?>

        <a href="<?= site_url('customer/add') ?>" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Tambah</a>

    <?php } ?>

</div>
<?php if (count($duplicatess)  > 0) { ?>
    <div class="alert alert-danger alert-dismissible">
        <i class="icon fa fa-ban"></i> Ada No Layanan Yang Sama <br>
        <pre>
<?php
    $a = $duplicatess;
    print_r($a);
?>
 
</pre>
    </div>

<?php } ?>
<?php $this->view('messages') ?>
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h6 class="m-0 font-weight-bold">Data Pelanggan</h6>
            <?php $idcoverage = $this->input->post('coverage');
            $query = "SELECT *
FROM `services` JOIN `customer`  ON `customer`.`no_services` = `services`.`no_services` WHERE  `customer`.`coverage` = $idcoverage
";
            $querying = $this->db->query($query)->result(); ?>
            <?php $grandtotal = 0;
            foreach ($querying as  $dataa)
                $grandtotal += (int) $dataa->total;
            ?>
            <?php if ($this->session->userdata('role_id') == 1) { ?>
                <?php if ($this->input->post('coverage') != 0) { ?>
                    <h6>Estimasi Pendapatan <?= indo_currency($grandtotal); ?> / Bulan <br>Belum Termasuk PPN</h6>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="tablebt" width="100%" cellspacing="0">
                <thead>
                    <tr style="text-align: center">
                        <th style="text-align: center; width:20px">No</th>
                        <th>No Layanan</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Auto Isolir</th>
                        <th>Keterangan</th>
                        <th>No Telp.</th>
                        <th>Paket</th>
                        <?php if ($this->session->userdata('role_id') == 1) { ?>
                            <th style="width: 100px">Tagihan / Bulan</th>
                        <?php } ?>
                        <th>ID Card</th>
                        <th>Alamat</th>

                        <th style="text-align: center">Aksi</th>

                    </tr>
                </thead>

                <tbody>


                    <?php $no = 1;
                    foreach ($customer as $r => $data) { ?>
                        <tr>

                            <td style="text-align: center"><?= $no++ ?>.</td>
                            <td><?= $data->no_services ?>
                                <?php if ($this->session->userdata('role_id') == 1) { ?>
                                    <br>
                                    <a href="<?= site_url('services/detail/') ?><?= $data->no_services ?>" class="btn btn-success" style="font-size: smaller">Rincian Paket</a>
                                <?php  } ?>
                            </td>
                            <td><a href="#" data-toggle="modal" data-target="#detail<?= $data->no_services ?>" title="Detail">
                                    <div class="badge badge-primary"><?= $data->name ?></div>
                                </a></td>
                            <td><?= $data->c_status ?></td>
                            <td><?= $data->auto_isolir == 1 ? 'Aktif' : 'Non-Aktif' ?></td>
                            <td><?= html_escape($data->cust_description) ?></td>
                            <td><?= indo_tlp($data->no_wa) ?></td>
                            <td style="width: max-content;">
                                <?php if ($data->no_services != 0) { ?>
                                    <?php $query = "SELECT *
                                    FROM `services`
                                        WHERE `services`.`no_services` = $data->no_services";
                                    $querying = $this->db->query($query)->result(); ?>
                                    <?php $nomor = 1;
                                    foreach ($querying as  $dataa) { ?>
                                        <?php $item = $this->db->get_where('package_item', ['p_item_id' => $dataa->item_id])->row_array(); ?>
                                        <?= $nomor++; ?>. <?= $item['name']; ?> - <?= indo_currency($item['price']); ?> <br>
                                    <?php } ?>

                                <?php } ?>
                                <?php if ($data->no_services == 0) { ?>
                                    <?php $querying = $this->db->get_where('services', ['email' => $data->email])->result() ?>
                                    <?php $subtotal = 0;
                                    foreach ($querying as  $dataa)
                                        $subtotal += (int) $dataa->total;
                                    ?>
                                    <?php $ppn = $subtotal * ($company['ppn'] / 100) ?>
                                    <?php if ($data->ppn == 1) { ?>
                                        <?= indo_currency($subtotal) ?>
                                    <?php } ?>
                                    <?php if ($data->ppn != 1) { ?>
                                        <?= indo_currency($subtotal) ?>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                            <?php if ($this->session->userdata('role_id') == 1) { ?>
                                <td style="text-align:right; font-weight:bold ">
                                    <?php if ($data->no_services != 0) { ?>
                                        <?php $query = "SELECT *
                                    FROM `services`
                                        WHERE `services`.`no_services` = $data->no_services";
                                        $querying = $this->db->query($query)->result(); ?>
                                        <?php $subtotal = 0;
                                        foreach ($querying as  $dataa)
                                            $subtotal += (int) $dataa->total;
                                        ?>
                                        <?php $ppn = $subtotal * ($company['ppn'] / 100) ?>
                                        <?php if ($data->ppn == 1) { ?>
                                            <?= indo_currency($subtotal) ?>
                                        <?php } ?>
                                        <?php if ($data->ppn != 1) { ?>
                                            <?= indo_currency($subtotal) ?>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if ($data->no_services == 0) { ?>
                                        <?php $querying = $this->db->get_where('services', ['email' => $data->email])->result() ?>
                                        <?php $subtotal = 0;
                                        foreach ($querying as  $dataa)
                                            $subtotal += (int) $dataa->total;
                                        ?>
                                        <?php $ppn = $subtotal * ($company['ppn'] / 100) ?>
                                        <?php if ($data->ppn == 1) { ?>
                                            <?= indo_currency($subtotal) ?>
                                        <?php } ?>
                                        <?php if ($data->ppn != 1) { ?>
                                            <?= indo_currency($subtotal) ?>
                                        <?php } ?>
                                    <?php } ?>

                                </td>
                            <?php } ?>
                            <td><?= $data->type_id ?> - <?= $data->no_ktp ?></td>

                            <td><?= $data->address ?></td>
                            <td style="text-align: center">
                                <?php if ($this->session->userdata('role_id') == 1 or $role['edit_customer'] == 1) { ?>
                                    <a href="<?= site_url('customer/edit/') ?><?= $data->customer_id ?>" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a>
                                <?php } ?>
                                <?php if ($this->session->userdata('role_id') == 1 or $role['del_customer'] == 1) { ?>
                                    <a href="" data-toggle="modal" data-target="#DeleteModal<?= $data->customer_id ?>" title="Hapus"><i class="fa fa-trash" style="font-size:25px; color:red"></i></a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <?php if ($this->session->userdata('role_id') == 1) { ?>
                    <?php if ($title == 'Aktif') { ?>
                        <tfoot>
                            <tr style="text-align: right">

                            </tr>
                        </tfoot>
                    <?php } ?>
                <?php } ?>
            </table>
        </div>
    </div>
</div>
<?php foreach ($customer as $r => $data) { ?>
    <div class="modal fade" id="detail<?= $data->no_services ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Detail Pelanggan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-form-label">Nama:</label>
                        <input type="text" class="form-control" value="<?= $data->name ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label class="col-form-label">Email:</label>
                        <input type="text" class="form-control" value="<?= $data->email ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label class="col-form-label">No Telp:</label>
                        <input type="text" class="form-control" value="<?= $data->no_wa ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label class="col-form-label">No KTP:</label>
                        <input type="text" class="form-control" value="<?= $data->no_ktp ?>" readonly>
                        <img src="<?= base_url('assets/images/ktp/' . $data->ktp) ?>" alt="" style="width:400px;">
                    </div>
                    <div class="form-group">
                        <label for="message-text" class="col-form-label">Alamat:</label>
                        <textarea class="form-control" id="message-text" readonly><?= $data->address; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>

                </div>

            </div>
        </div>
    </div>
<?php } ?>
<!-- Modal Hapus -->
<?php
foreach ($customer as $r => $data) { ?>
    <div class="modal fade" id="DeleteModal<?= $data->customer_id ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Hapus Pelanggan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php echo form_open_multipart('customer/delete') ?>
                    <input type="hidden" name="customer_id" value="<?= $data->customer_id ?>" class="form-control">
                    <input type="hidden" name="no_services" value="<?= $data->no_services ?>" class="form-control">
                    Apakah yakin akan hapus No Layanan <?= $data->no_services ?> A/N <?= $data->name ?> ?.
                    <br>
                    <br>
                    <input type="checkbox" id="clickdelincome"> <label for="">Hapus Data Pemasukan dari <?= $data->name ?></label>
                    <input type="hidden" name="delincome" id="delincome">
                    <br>
                    <div id="formdelincome" style="display: none">
                        <span style="color: blue;">Penghapusan data pemasukan akan mempengaruhi Saldo Kas dan Data Pemasukan</span>
                        <br>
                    </div>
                    <span style="color: red;">
                        Penghapusan ini akan menghapus semua data riwayat tagihan pelanggan A/N <?= $data->name ?></span>
                    <br> <input type="checkbox" id="agree" required> <label for="">* Saya Setuju</label>
                    <input type="hidden" name="iagree" id="iagree">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<script>
    $("#clickdelincome").click(function() {
        if ($(this).is(":checked")) {
            $("#delincome").val('1');
            $("#formdelincome").show();
        } else {
            $("#delincome").val('0');
            $("#formdelincome").hide();
            document.getElementById("clickdelincome").checked = false;
        }
    });
</script>