<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css">
<!-- Custom styles for this page -->
<link href="<?= base_url('assets/backend/') ?>vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
<input type="hidden" id="status" value="<?= $post['status'] ?>">
<input type="hidden" id="coverage" value="<?= $post['coverage'] ?>">
<input type="hidden" id="month" value="<?= $post['month'] ?>">
<input type="hidden" id="year" value="<?= $post['year'] ?>">
<?php $coverage = $this->db->get_where('coverage', ['coverage_id' => $post['coverage']])->row_array() ?>

<div class="table-responsive">
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
<script>
    $(document).ready(function() {

        var coverage = $("#coverage").val();
        var status = $("#status").val();
        var month = $("#month").val();
        var year = $("#year").val();
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
                "url": "<?= base_url('bill/getfiltercoverage/') ?>",
                data: {
                    coverage: coverage,
                    status: status,
                    month: month,
                    year: year,
                },

                "type": "POST",
            },
            "columnDefs": [{
                "targets": [0, 9, 10, 11],
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
                searchPlaceholder: "<?= isset($post['status']) && $post['status'] === 'BELUM BAYAR' ? 'No Layanan, Nama, IP' : 'No Layanan, Nama' ?>"
            },
            "columnDefs": [{
                "targets": [0, 1, 8],
                "orderable": false
            }]

        });

    });
</script>


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
<script>
    $(document).ready(function() {
        $("#selectAll").click(function() {
            if ($(this).is(":checked"))
                $(".check-item").prop("checked", true);
            else // Jika checkbox all tidak diceklis
                $(".check-item").prop("checked", false);
        });
    });
</script>