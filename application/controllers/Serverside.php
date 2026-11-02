<?php defined('BASEPATH') or exit('No direct script access allowed');

class Serverside extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        is_logged_in();
        $this->load->model(['income_m', 'report_m', 'customer_m', 'bill_m']);
    }

    public function index()
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        // $data['customer'] = $this->customer_m->getCustomeractive()->result();
        $this->template->load('backend', 'backend/customer/data-server', $data);
    }
    public function bill()
    {
        $data['title'] = 'Bill';
        $data['company'] = $this->db->get('company')->row_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->template->load('backend', 'backend/bill/all-server', $data);
    }

    public function getDataCustomer()
    {
        $result = $this->customer_m->getDataTable();
        $data = [];
        $no = $_POST['start'];
        foreach ($result as $result) {
            $query = "SELECT * FROM `services` WHERE `services`.`no_services` = $result->no_services";
            $querying = $this->db->query($query)->result();
            $subtotal = 0;
            foreach ($querying as  $dataa)
                $subtotal += (int) $dataa->total;
            $row = array();
            $row[] = ++$no;
            $row[] = $result->name . '<br> <a href="' . site_url('services/detail/') . $result->no_services . '" title="Rincian Paket" class="btn btn-success"  style="font-size: smaller">Rincian Paket</a> ';
            $row[] = $result->no_services;
            $row[] = $result->no_wa;
            $row[] = $result->address;
            $row[] = indo_currency($subtotal);
            $row[] = '
            <a href="' . site_url('customer/edit/') . $result->customer_id . '" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a> 
            <a href="#" id="delete" data-customer_id="' . $result->customer_id . '" data-notif="Apakah yakin akan hapus No Layanan ' . $result->no_services . ' A/N ' . $result->name . ' ?" data-toggle="modal" data-target="#DeleteModal"   title="Delete" ><i class="fa fa-trash" style="font-size:25px; color:red"></i></a> 
            ';
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->customer_m->count_all_data(),
            "recordsFiltered" => $this->customer_m->count_filtered_data(),
            "data" => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
    public function getDataInvoice()
    {
        $result = $this->bill_m->getDataInv();
        $data = [];
        $no = $_POST['start'];
        foreach ($result as $result) {
            $row = array();
            $row[] = ++$no;
            $row[] = '<input type=' . 'checkbox' . ' class=' . 'check-item' . ' id="ceklis" name=' . 'invoice[]' . ' value=' . $result->invoice . '>';
            if ($result->status == 'SUDAH BAYAR') {
                $row[] =
                    '<a href="' . site_url('bill/printinvoice/') . $result->invoice . '"   title="Cetak Invoice" style="color:green"><i class="fa fa-print"> A4</i></a>
                    <a href="' . site_url('bill/printinvoicethermal/') . $result->invoice . '"   title="Cetak Invoice" style="color:green"><i class="fa fa-print"> Thermal</i></a>';
            } else {
                $row[] =
                    '<a href="' . site_url('bill/printinvoice/') . $result->invoice . '"   title="Cetak Invoice" style="color:red"><i class="fa fa-print"> A4</i></a>
                        <a href="' . site_url('bill/printinvoicethermal/') . $result->invoice . '"   title="Cetak Invoice" style="color:red"><i class="fa fa-print"> Thermal</i></a>';
            }
            $row[] =  $result->invoice;
            $row[] =  $result->no_services;
            $row[] =  $result->name;
            $row[] = $result->no_wa;
            $row[] = indo_month($result->month) . ' ' . $result->year;
            if ($result->amount != 0) {
                $row[] = indo_currency($result->amount);
            }
            if ($result->amount == 0) {
                $query = "SELECT * FROM `invoice_detail` WHERE `invoice_detail`.`invoice_id` =  $result->invoice";
                $querying = $this->db->query($query)->result();
                $subTotal = 0;
                foreach ($querying as  $dataa)
                    $subTotal += (int) $dataa->total;
                if ($subTotal != 0) {
                    $ppn = $subTotal * ($result->i_ppn / 100);
                    $row[] = indo_currency($subTotal + $ppn);
                } else {
                    $query = "SELECT * FROM `invoice_detail` WHERE `invoice_detail`.`d_month` =  $result->month and
                                       `invoice_detail`.`d_year` =  $result->year and
                                       `invoice_detail`.`d_no_services` =  $result->no_services";
                    $queryTot = $this->db->query($query)->result();
                    $subTotaldetail = 0;
                    foreach ($queryTot as  $dataa)
                        $subTotaldetail += (int) $dataa->total;
                    $ppn = $subTotaldetail * ($result->i_ppn / 100);
                    $row[] = indo_currency($subTotaldetail + $ppn);
                }
            }
            if ($result->amount != 0) {
                $nominal = $result->amount;
            } else {
                $query = "SELECT * FROM `invoice_detail` WHERE `invoice_detail`.`invoice_id` =  $result->invoice";
                $querying = $this->db->query($query)->result();
                $subTotal = 0;
                foreach ($querying as  $dataa)
                    $subTotal += (int) $dataa->total;
                if ($subTotal != 0) {
                    $ppn = $subTotal * ($result->i_ppn / 100);
                    $nominal = $subTotal + $ppn;
                } else {
                    $query = "SELECT * FROM `invoice_detail` WHERE `invoice_detail`.`d_month` =  $result->month and
                                       `invoice_detail`.`d_year` =  $result->year and
                                       `invoice_detail`.`d_no_services` =  $result->no_services";
                    $queryTot = $this->db->query($query)->result();
                    $subTotaldetail = 0;
                    foreach ($queryTot as  $dataa)
                        $subTotaldetail += (int) $dataa->total;
                    $ppn = $subTotaldetail * ($result->i_ppn / 100);
                    $nominal = $subTotaldetail + $ppn;
                }
            }
            $other = $this->db->get('other')->row_array();
            $company = $this->db->get('company')->row_array();
            $bank = $this->db->get('bank')->result();
            if ($result->due_date != 0) {
                $due_date = $result->due_date;
            } else {
                $due_date = $company['due_date'];
            }
            $row[] = $result->status;
            $linkWA = "https://api.whatsapp.com/send?phone=";
            if ($result->status == 'SUDAH BAYAR') {
                $row[] = '<a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
                <a href="#" id="hapusmodal" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModal" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>
                <a href="' . $linkWA . indo_tlp($result->no_wa) . '&text=' . $other['thanks_wa'] .  $result->no_services . ' a/n ' . $result->name . ' Periode ' . indo_month($result->month) . ' ' . $result->year . '" target="blank"><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a> ';
            } else {
                $row[] = '<a href="#" id="bayar" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . indo_month($result->month) . '" data-invoice="' . $result->invoice . '" data-name="' . $result->name . '"  data-email_customer="' . $result->email . '"  data-periode="' . indo_month($result->month) . ' ' . $result->year . '" data-nominal="' . $nominal . '" data-toggle="modal" data-target="#ModalBayar"><i class="fas fa-credit-card"></i></a> <a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
                <a href="' . $linkWA . indo_tlp($result->no_wa) . '&text=' . $other['say_wa'] .  $result->no_services . ' a/n ' . $result->name . ' Periode ' . indo_month($result->month) . ' ' . $result->year . ' Sebesar ' . indo_currency($nominal) . '.' . ' Maks tgl ' . $due_date . ' ' . indo_month($result->month) . ' ' . $result->year .  '.' . ' ' . $other['body_wa'] . ' ' . '" target="blank"><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a>
                <a href="#" id="hapusmodal" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModal" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>';
            }

            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->bill_m->count_all_data(),
            "recordsFiltered" => $this->bill_m->count_filtered_data(),
            "data" => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
}
