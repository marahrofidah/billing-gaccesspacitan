<h3>Server Side</h3>
<table id="example" class="display" style="width:100%">
    <thead>
        <tr>
            <th>No</th>
            <th>Name</th>
            <th>No Services</th>
            <th>No WA</th>
            <th>Address</th>
            <th>Tagihan / Bln</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    </tbody>

</table>
<script>
    $(document).ready(function() {
        $('#example').DataTable({
            "processing": true,
            "serverSide": true,
            "order": [],
            "ajax": {
                "url": "<?= base_url('serverside/getDataCustomer'); ?>",
                "type": "POST"
            },
            "columDefs": [{
                "target": [-1],
                "orderable": false,
            }]
        });
    });
</script>

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

                <div class="" id="notif"></div>
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
    $(document).on('click', '#delete', function() {

        $('#customer_id').val($(this).data('customer_id'))
        $('#notif').html($(this).data('notif'));

    })
</script>