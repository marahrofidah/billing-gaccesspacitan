<!-- Page Heading -->
<style>
    .table thead th {
        background-color: white;
    }

    .table tfoot th {
        background-color: white;
    }
</style>
<?php $role = $this->db->get_where('role_management', ['role_id' => $this->session->userdata('role_id')])->row_array() ?>
<?php $menu = $this->db->get_where('role_menu', ['role_id' => $this->session->userdata('role_id')])->row_array() ?>
<?php if ($this->session->userdata('role_id') == 1  or $menu['customer_menu'] == 1) { ?>
    <div class="col-lg-3 col-sm-12 col-md-6">
        <div class="form-group">
            <select class="form-control select2" name="no_services" id="no_services" onchange="getdetailcustomer()" required>
                <option value="">-Pilih Nama Pelanggan-</option>
                <?php foreach ($customer as $r => $data) { ?>
                    <option value="<?= $data->no_services ?>"><?= $data->no_services ?> - <?= $data->name ?> - <?= $data->c_status; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
<?php } ?>

<div class="getdatacustomer"></div>
<div class="" id="contents">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">

        <?php
        $customerall = $this->db->get('customer')->result();
        $dataaa = [];
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
        <?php if ($this->session->userdata('role_id') == 1 or $role['add_customer'] == 1) { ?>

            <a href="<?= site_url('customer/add') ?>" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Tambah</a>

        <?php } ?>
        <?php if (count($duplicatess) == 0) { ?>
            <?php if ($company['import'] == 1) { ?>
                <a href="<?= site_url('customer/import') ?>" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm"><i class="fas fa-file-excel fa-sm text-white-50"></i> Import</a>
            <?php } ?>
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
    <?php $totalcustomer = $this->db->get_where('customer', ['c_status' => 'Aktif'])->num_rows(); ?>

     <div class="row">
                    <div class="col-md-4">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-12 control-label">Coverage Area</label>
                                <div class="col-sm-9">
                                    <select class="form-control select2" name="coverage[]" multiple="multiple" id="coverage" onchange="getdatacustomer()" required>
                                        <?php if ($this->session->userdata('customer_coverage') != null) { ?>

                                            <?php $getcoverage = $this->db->get_where('coverage', ['coverage_id' => $this->session->userdata('customer_coverage')])->row_array() ?>
                                            <option value="<?= $this->session->userdata('customer_coverage') ?>"><?= $getcoverage['c_name']; ?></option>
                                        <?php } ?>
                                        <?php if ($this->session->userdata('customer_coverage') == '') { ?>
                                            <option value="">Semua Area</option>
                                        <?php } ?>
                                        <option value="">Semua Area</option>

                                        <?php
                                        foreach ($coverage as $r => $data) { ?>
                                            <option value="<?= $data->coverage_id ?>"><?= $data->code_area ?> - <?= $data->c_name ?> </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-12 control-label">Status</label>
                                <div class="col-sm-9">
                                    <select class="form-control select2" name="status" id="status" onchange="getdatacustomer()" required>
                                        <?php if ($this->session->userdata('customer_status') != null) { ?>
                                            <option value="<?= $this->session->userdata('customer_status') ?>"><?= $this->session->userdata('customer_status'); ?></option>
                                        <?php } ?>
                                        <?php if ($this->session->userdata('customer_status') == null) { ?>
                                            <option value="Aktif">Aktif</option>
                                        <?php } ?>
                                        <option value="">Semua</option>
 <option value="Menunggu">Menunggu</option>
                                    <option value="Free">Free</option>
                                     <option value="Non-Aktif">Non-Aktif</option>
                                        <option value="Isolir">Isolir</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                
                 

                </div>

    <!-- Button trigger modal -->
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Update Data Pelanggan </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row text-center">
                    <div class="col">
                        <a href="" class="btn btn-outline-danger" id="createuser" style="font-size: smaller"><i class="fa fa-user"> Create User Login</i></a>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

            </div>

        </div>
    </div>
</div>
<!-- DataTales Example -->
<div class="loading"></div>
<div class="view_data"></div>
<div class="card shadow mb-4" id="tablecustomer" style="display:<?= $totalcustomer >= 500 ? 'none' : '' ?>">
    <div class="card-header py-3">
        <div class="d-sm-flex align-items-center justify-content-between mb-1">
            <h6 class="m-0 font-weight-bold">Data Pelanggan</h6>
            <div class="" id="cardestimation" style="display: none;">
                <?php if ($this->session->userdata('role_id') == 1) { ?>
                    <?php if ($title == 'Aktif') { ?>
                        <h6>Estimasi Pendapatan <span id="estimation"></span> / Bulan <br>Belum Termasuk PPN</h6>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="card-body">
        <?php if ($title == 'Isolir') { ?>


            Ini hanya tampilan sementara, silahkan klik Cek Koneksi untuk memastikan pelanggan nya sdh terisolir, jika masih aktif kemungkinan gagal isolir karena router / mikrotik down saat eksekusi.
            <br><br> <a href="<?= site_url('customer/openisolir') ?>" onclick="return confirm('Apakah anda yakin akan open isolir semua pelanggan ?')" class="btn btn-warning">Open Isolir Semua Pelanggan</a>
        <?php } ?>
        <?php if ($title == 'Customer') { ?>
            <a href="#" id="#filterbyModal" data-toggle="modal" data-target="#filterbyModal" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-cube fa-sm text-white-50"></i> Filter by</a>
        <?php } ?>
        <?php $rt = $this->db->get_where('router', ['id' => 1])->row_array() ?>
        <?php if ($rt['is_active'] == 1) { ?>
            <?php $cekprofile = $this->customer_m->cekuserchangeprofile()->result() ?>

            <!-- <a href="#" data-toggle="modal" data-target="#modalnotifsinkron" class="btn btn-outline-danger"> <?= count($cekprofile); ?> Pelanggan yang harus di sinkronisasi ulang</a> -->
            <div class="modal fade" id="modalnotifsinkron" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Warning Update Pelanggan </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">Nama</th>
                                        <th scope="col">No Layanan</th>
                                        <th scope="col">Mode</th>
                                        <th scope="col">User</th>
                                        <th scope="col">User Profile</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    foreach ($cekprofile as $data) { ?>
                                        <tr>
                                            <th scope="row"><?= $no++; ?></th>
                                            <td><?= $data->name; ?></td>
                                            <td><?= $data->no_services; ?></td>
                                            <td><?= $data->mode_user; ?></td>
                                            <td><?= $data->user_mikrotik; ?></td>
                                            <td><?= $data->user_profile; ?></td>

                                            <td>
                                                <a href="<?= site_url('customer/edit/' . $data->customer_id) ?>">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="table-responsive mt-2">
            <form method="post" action="<?php echo base_url('bill/printinvoiceselected') ?>" id="submit-update">
                <table class="table table-bordered" id="example" width="100%" cellspacing="0">
                    <!-- <input type="hidden" name='customer_id[]' id="result" size="60"> -->

                    <thead>
                        <tr style="text-align: center">
                            <th style="text-align: center; width:20px">No</th>
                            <th style="text-align: center; width:20px"> <input type='checkbox' class='check-item' id="selectAll"></th>
                            <th>Nama</th>
                            <th>No Layanan</th>
                            <th>No Telp.</th>
                            <th>Status</th>
                            <th>Auto Isolir</th>
                            <th>Keterangan</th>
                            <th>IP</th>
                            <?php if ($this->session->userdata('role_id') == 1) { ?>
                                <th style="width: 100px">Tagihan / Bulan</th>
                            <?php } ?>
                            <th>Alamat</th>
                            <th>Tanggal Daftar</th>
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
                <?php echo form_open_multipart('customer/filterby') ?>
                <div class="form-group">
                    <label for="status">Status Pelanggan</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="">-Pilih-</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Non-Aktif">Non-Aktif</option>
                        <option value="Menunggu">Menunggu</option>

                    </select>
                </div>
                <div class="form-group">
                    <label for="coverage">Coverage Area</label>
                    <select name="coverage" id="coverage" class="form-control">
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
<!-- Modal Hapus -->

<div class="modal fade" id="DeleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                <input type="hidden" name="customer_id" id="customer_id" class="form-control">

                Apakah and yakin hapus pelanggan <span id="no_services"></span> <span id="name"></span> ?
                <br>
                <br>
                <input type="checkbox" id="clickdelincome"> <label for="">Hapus Data Pemasukan</label>
                <input type="hidden" name="delincome" id="delincome">
                <br>
                <div id="formdelincome" style="display: none">
                    <span style="color: blue;">Penghapusan data pemasukan akan mempengaruhi Saldo Kas dan Data Pemasukan</span>
                    <br>
                </div>
                <span style="color: red;">
                    Penghapusan ini akan menghapus semua data riwayat tagihan pelanggan tsb</span>
                <br> <input type="checkbox" id="agree" required> <label for="">* Saya Setuju</label>
                <input type="hidden" name="iagree" id="iagree">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" id="click-me" class="btn btn-danger">Hapus</button>
                </div>
                <?php echo form_close() ?>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $("#selectAll").click(function() {
            if ($(this).is(":checked"))
                $(".check-item").prop("checked", true);
            else // Jika checkbox all tidak diceklis
                $(".check-item").prop("checked", false);
        });
        $("#createuser").click(function() {
            $('#submit-update').attr('action', '<?php echo base_url('customer/createuserlogin') ?>').attr('target', '_blank');
            Swal.fire({
                title: 'Apakah anda yakin ?',
                text: "Akan membuat user login untuk pelanggan yang dipilih, default password : 1234",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#submit-update").submit();
                }
            })
        });
        $("#setppn").click(function() {
            $('#submit-update').attr('action', '<?php echo base_url('customer/setppnselected') ?>');
            Swal.fire({
                title: 'Apakah anda yakin ?',
                text: "Akan aktifkan PPN untuk pelanggan yg dipilih",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#submit-update").submit();
                }
            })
        });
        $("#unsetppn").click(function() {
            $('#submit-update').attr('action', '<?php echo base_url('customer/unsetppnselected') ?>');
            Swal.fire({
                title: 'Apakah anda yakin ?',
                text: "Akan Non-Aktifkan PPN untuk pelanggan yg dipilih",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#submit-update").submit();
                }
            })
        });
        $("#setactive").click(function() {
            $('#submit-update').attr('action', '<?php echo base_url('customer/setactive') ?>');
            Swal.fire({
                title: 'Apakah anda yakin ?',
                text: "Akan Update Status Pelanggan Menjadi Aktif untuk pelanggan yg dipilih",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#submit-update").submit();
                }
            })
        });
        $("#setnonactive").click(function() {
            $('#submit-update').attr('action', '<?php echo base_url('customer/setnonactive') ?>');
            Swal.fire({
                title: 'Apakah anda yakin ?',
                text: "Akan Update Status Pelanggan Menjadi Non-Aktif untuk pelanggan yg dipilih",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#submit-update").submit();
                }
            })
        });
        $("#setchangeprofile").click(function() {
            $('#submit-update').attr('action', '<?php echo base_url('customer/setchangeprofile') ?>');
            Swal.fire({
                title: 'Apakah anda yakin ?',
                text: "Akan update action isolir menjadi Pindah Profile untuk pelanggan yg dipilih",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#submit-update").submit();
                }
            })
        });
        $("#setdisableuser").click(function() {
            $('#submit-update').attr('action', '<?php echo base_url('customer/setdisableuser') ?>');
            Swal.fire({
                title: 'Apakah anda yakin ?',
                text: "Akan update action isolir menjadi Disable User untuk pelanggan yg dipilih",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#submit-update").submit();
                }
            })
        });
        $.ajax({
            type: 'get',
            url: '<?= site_url('customer/delempty') ?>',
            cache: false,
            success: function(data) {

            }
        });
        return false;

    })
</script>

<?php if ($totalcustomer >= 500) { ?>
    <script>
        $(document).ready(function() {

            var coverage = $("#coverage").val();
            var status = $("#status").val();
            $.ajax({
                type: 'POST',
                data: "&coverage=" + coverage + "&status=" + status,
                cache: false,
                url: '<?= site_url('customer/getdatacustomercoverage') ?>',
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
                    $('#tablecustomer').hide();
                }
            });
            return false;

        });
    </script>
<?php } ?>
<script>
    function getdatacustomer() {
        var coverage = $("#coverage").val();
        var status = $("#status").val();
        $.ajax({
            type: 'POST',
            data: "&coverage=" + coverage + "&status=" + status,
            cache: false,
            url: '<?= site_url('customer/getdatacustomercoverage') ?>',
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
                $('#tablecustomer').hide();
            }
        });
        return false;
    }
</script>
<?php if ($totalcustomer < 500) { ?>
    <?php if ($title == 'Customer') { ?>
        <script>
            $(document).ready(function() {

                var table = $('#example').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "paging": true,
                    "lengthChange": true,
                    "searching": true,
                    "info": true,
                    "autoWidth": true,
                    "responsive": true,
                    "order": [],
                    "ajax": {
                        "url": "<?= base_url('customer/getAllCustomer'); ?>",
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
                    "columnDefs": [{
                        "targets": [0, 1, 9, -2],
                        "orderable": false
                    }],

                });

            });
        </script>
    <?php } ?>
    <?php if ($title == 'Free') { ?>

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
                    "responsive": true,
                    "order": [],
                    "ajax": {
                        "url": "<?= base_url('customer/getCustomerfree'); ?>",
                        "type": "POST"
                    },
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
                        searchPlaceholder: "No Layanan, Nama"
                    },
                    "columnDefs": [{
                        "targets": [0, 1, 9, -2],
                        "orderable": false
                    }]
                });
            });
        </script>
    <?php } ?>
    <?php if ($title == 'Isolir') { ?>

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
                    "responsive": true,
                    "order": [],
                    "ajax": {
                        "url": "<?= base_url('customer/getCustomerisolir'); ?>",
                        "type": "POST"
                    },
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
                    "columnDefs": [{
                        "targets": [0, 1, 9, -2],
                        "orderable": false
                    }],
                });
            });
        </script>
    <?php } ?>
    <?php if ($title == 'Aktif') { ?>

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
                    "responsive": true,

                    "ajax": {
                        "url": "<?= base_url('customer/getActiveCustomer'); ?>",
                        "type": "POST"
                    },
                    "lengthMenu": [
                        [50, 100, 250, 500, 1000],
                        [50, 100, 250, 500, 1000]
                    ],
                    // fixedHeader: {
                    //     header: true,
                    //     footer: true
                    // },
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
                        searchPlaceholder: "No Layanan, Nama"
                    },
                    // "ordering": false,
                    "columnDefs": [{
                        "targets": [0, 1, 9, -2],
                        "orderable": false
                    }],

                });
            });
        </script>
    <?php } ?>
    <?php if ($title == 'Non-Aktif') { ?>

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
                    "responsive": true,
                    "order": [],
                    "ajax": {
                        "url": "<?= base_url('customer/getNonActiveCustomer'); ?>",
                        "type": "POST"
                    },
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
                    "columnDefs": [{
                        "targets": [0, 1, 9, -2],
                        "orderable": false
                    }]
                });
            });
        </script>
    <?php } ?>
    <?php if ($title == 'Waiting') { ?>

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
                    "responsive": true,
                    "order": [],
                    "ajax": {
                        "url": "<?= base_url('customer/getWaitCustomer'); ?>",
                        "type": "POST"
                    },
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
                    "columnDefs": [{
                        "targets": [0, 1, 9, -2],
                        "orderable": false
                    }]
                });
            });
        </script>
    <?php } ?>
    <script>
        $(document).ready(function() {
            $.ajax({
                type: 'get',
                url: '<?= site_url('customer/getestimationincome') ?>',
                cache: false,
                success: function(data) {
                    var c = jQuery.parseJSON(data);
                    $("#cardestimation").show();
                    $('#estimation').html(c['estimation']);
                }
            });
            return false;
        })
    </script>
<?php } ?>

<script>
    $(document).on('click', '#delete', function() {

        $('#customer_id').val($(this).data('customer_id'))
        $('#no_services').html($(this).data('no_services'))
        $('#name').html($(this).data('name'))


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
            document.getElementById("clickdelincome").checked = false;
        }
    });
</script>
<script>
    $(document).ready(function() {
        $.ajax({
            type: 'get',
            url: '<?= site_url('customer/delempty') ?>',
            cache: false,
            success: function(data) {

            }
        });
        return false;
    })
</script>