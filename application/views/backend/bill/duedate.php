<?php $this->view('messages') ?>

<?php $wa = $this->db->get('whatsapp')->row_array() ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Data Pelanggan Jatuh Tempo</h6>
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
                    <!-- <a href="" data-toggle="modal" data-target="#cetakblmbayar" class="btn btn-outline-danger dropdown-item"><i class="fa fa-print"></i> Semua</a> -->
                    <button href="" class="btn btn-outline-primary dropdown-item" id="btn-pay-selected"><i class="fa fa-credit-card"></i> Bayar Yang Dipilih</button>
                    <?php $wa = $this->db->get('whatsapp')->row_array() ?>
                    <?php if ($wa['is_active'] == 1) { ?>
                        <button href="" class="dropdown-item " id="btn-wa-selected"> <i class="fab fa-whatsapp"></i> Kirim Info Jatuh Tempo Yang Dipilih</button>
                    <?php } ?>
                    <?php if ($this->session->userdata('role_id') == 1 or $role['del_bill'] == 1) { ?>
                        <button href="" class="btn btn-outline-danger dropdown-item" id="btn-del-selected"><i class="fa fa-trash"></i> Hapus Yang Dipilih</button>
                    <?php } ?>
                </div>
            </div>

        </div>
        <div class="table-responsive">
            <form method="post" action="<?php echo base_url('bill/printinvoiceselected') ?>" id="submit-cetak">
                <table class="table table-bordered" id="example" cellspacing="0">
                    <thead>
                        <tr style="text-align: center">
                            <th style="text-align: center; width:20px">No</th>
                            <th>
                                <input type='checkbox' class='check-item' id="selectAll">
                            </th>
                            <th style="text-align: center; width:100px">Aksi</th>
                            <th>Nama Pelanggan</th>
                            <th>No Layanan</th>
                            <th>Periode - Jatuh Tempo</th>
                            <th>Tagihan</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <!-- <tfoot>
                    <tr>
                        <th style="text-align: center; width:20px">No</th>
                        <th style="text-align: center; width:100px">Aksi</th>
                        <th>Nama Pelanggan</th>
                        <th>No Layanan</th>
                        <th>Periode - Jatuh Tempo</th>
                        <th>Tagihan</th>
                    </tr>
                </tfoot> -->
                </table>
            </form>
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
<script>
    $(function() {
        $('#example tfoot th').each(function() {
            var title = $(this).text();
            if (title) {
                // $(this).html('<input type="text"  placeholder="' + title + '" />');
            }
        });
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
                "url": "<?= base_url('bill/getduedate'); ?>",
                "type": "POST"
            },
            "lengthMenu": [
                [10, 25, 50, 100, 250, 500, 1000, ],
                [10, 25, 50, 100, 250, 500, 1000, ]
            ],
            dom: 'lBfrtip',
            "columnDefs": [{
                "targets": [0, 1, 2],
                "orderable": false
            }],
            // ajax: '<?= base_url('bill/getduedate'); ?>',
            columns: [{
                    data: 'no',
                    name: 'no',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'select',
                    name: 'select',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'no_services',
                    name: 'no_services',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'periode',
                    name: 'periode',
                    orderable: true,
                    searchable: true
                },

                {
                    data: 'amount',
                    name: 'amount',
                    orderable: true,
                    searchable: true
                },
            ],

            // initComplete: function() {
            //     // Apply the search
            //     this.api().columns().every(function() {
            //         var that = this;

            //         $('input', this.footer()).on('keyup change clear', function() {
            //             if (that.search() !== this.value) {
            //                 that
            //                     .search(this.value)
            //                     .draw();
            //             }
            //         });
            //     });
            // }
        });
    });
</script>

<script>
    $(document).ready(function() {

        $.ajax({
            type: 'get',
            url: '<?= site_url('bill/fixduedate') ?>',
            cache: false,
            success: function(data) {}
        });
        return false;
    })
    $("#selectAll").click(function() {
        if ($(this).is(":checked"))
            $(".check-item").prop("checked", true);
        else // Jika checkbox all tidak diceklis
            $(".check-item").prop("checked", false);
    });
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
            $('#submit-cetak').attr('action', '<?php echo base_url('bill/sendwaselectedduedate') ?>').attr('target', '');
            var confirm = window.confirm("Apakah Anda yakin akan kirim info jatuh tempo yang terpilih ?");
            if (confirm) {
                $("#submit-cetak").submit();
                $("#popup").modal("show");
            } else {

            }

        });

    });
</script>