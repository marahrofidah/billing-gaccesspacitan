
<input type="hidden" id="status" value="<?= $post['status'] ?>">
<input type="hidden" id="coverage" value="<?= $post['coverage'] ?>">
<?php $coverage = $this->db->get_where('coverage', ['coverage_id' => $post['coverage']])->row_array() ?>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h6 class="m-0 font-weight-bold">Data Pelanggan Area <?= $coverage['c_name']; ?></h6>
            <div class="" id="cardestimation" style="display: none;">
                <?php if ($this->session->userdata('role_id') == 1) { ?>
                    <?php if ($post['status'] == 'Aktif') { ?>
                        <h6>Estimasi Pendapatan Area <?= $coverage['c_name']; ?><br><span id="estimation"></span> / Bulan <br>Belum Termasuk PPN</h6>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive mt-2">
            <form method="post" action="<?php echo base_url('bill/printinvoiceselected') ?>" id="submit-update">

                <table class="table table-bordered" id="example" width="100%" cellspacing="0">
                    <thead>
                        <tr style="text-align: center">
                            <th style="text-align: center; width:20px">No</th>
                            <th style="text-align: center; width:20px"> <input type='checkbox' class='check-item' id="selectAll"></th>
                            <th>Nama</th>
                            <th>No Layanan</th>
                            <th>No Telp.</th>
                            <th>Status</th>
                            <th>Ppn</th>
                            <?php if ($this->session->userdata('role_id') == 1) { ?>
                                <th style="width: 100px">Tagihan / Bulan</th>
                            <?php } ?>
                            <th>Alamat</th>
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
        var coverage = $("#coverage").val();
        var status = $("#status").val();
        // DataTable
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
                "url": "<?= base_url('customer/getfiltercoverage/') ?>",
                data: {
                    coverage: coverage,
                    status: status,
                },

                "type": "POST",
            },
            "columnDefs": [{
                "targets": [0],
                "orderable": false,
            }],
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
                "emptyTable": "No data available in table",
                "zeroRecords": "No data available in table",
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
                "targets": [0, 1, 8],
                "orderable": false
            }],

        });

    });
</script>
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
        var coverage = $("#coverage").val();
        $.ajax({
            type: 'post',
            url: '<?= site_url('customer/getestimationincomecoverage') ?>',
            data: {
                coverage: coverage,
            },
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
