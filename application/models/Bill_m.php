<?php defined('BASEPATH') or exit('No direct script access allowed');
class Bill_m extends CI_Model
{
    var $table = 'invoice';
    var $column_order = array(null, null, null,  'invoice', 'invoice.no_services', 'customer.name', 'customer.no_wa', 'inv_due_date', 'amount',  'status', 'address');
    var $order =  array(null, null, 'customer.name', 'customer.no_services',  'month',  'amount', 'status', 'coverage', null);
    var $orderfilter =  array(null, null, 'customer.name', 'customer.no_services',  'month', 'inv_due_date',  'amount', 'status', 'coverage', null);
    var $orderunpaid =  array(null, null, 'customer.name', 'customer.no_services',  'month', 'inv_due_date',  'amount', 'status', 'coverage', null, null, null);
    var $orderduedate =  array(null, null, 'customer.name', 'invoice.no_services',  'inv_due_date', 'amount');

    public function getInvoice($invoice_id = null)
    {
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        if ($invoice_id != null) {
            $this->db->where('invoice_id', $invoice_id);
        }
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getunpaid($no_services = null)
    {
        $this->db->select('*, invoice.created as created_invoice, invoice.no_services as noservices');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('invoice.no_services', $no_services);

        $this->db->where('status', 'BELUM BAYAR');
        $this->db->order_by('month', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getInvoiceduedate($row = null)
    {
        $datetoday = date('Y-m-d');
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        if ($row != null) {
            $this->db->where_in('coverage', $row);
        }
        $this->db->where('status', 'BELUM BAYAR');
        $this->db->where("invoice.inv_due_date BETWEEN '" . ('2018-01-01') . "' AND '" . ($datetoday) . "'");
        $query = $this->db->get();
        return $query;
    }
    public function getInvoiceDelete($post)
    {
        $this->db->select('*');
        $this->db->from('invoice');
        $this->db->where_in('status', $post['status']);
        $this->db->where_in('month', $post['month']);
        $this->db->where_in('year', $post['year']);
        $query = $this->db->get();
        return $query;
    }
    public function getRecentInv()
    {
        $this->db->select('*');
        $this->db->from('invoice');
        $this->db->limit(1);
        $this->db->order_by('invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getInvoiceUp($cover = null)
    {
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('status', 'BELUM BAYAR');
        if ($cover != null) {
            $this->db->where_in('coverage', $cover);
        }
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getallinvoicecoverage($cover = null)
    {
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        if ($cover != null) {
            $this->db->where_in('coverage', $cover);
        }
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getInvoicemonthyear($month, $year)
    {
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('month', $month);
        $this->db->where('year', $year);
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getInvoiceamount()
    {
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('amount', 0);
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getexportinvoice($post)

    {

        $this->db->select('*, invoice.created as created_invoice');

        $this->db->from('invoice');

        $this->db->join('customer', 'customer.no_services = invoice.no_services');

        if ($post['month'] != null) {

            $this->db->where('month', $post['month']);
        }

        if ($post['year'] != null) {

            $this->db->where('year',  $post['year']);
        }

        if ($post['status'] != null) {

            $this->db->where('status',  $post['status']);
        }

        $this->db->order_by('month', 'DESC');

        $query = $this->db->get();

        return $query;
    }
    public function getInvoiceFilter($post, $cover = null)
    {
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        if ($post['status'] != '') {
            $this->db->where('status', $post['status']);
            $this->db->where('month', $post['month']);
            $this->db->where('year', $post['year']);
            if ($post['router'] != 0) {
                $this->db->where('router', $post['router']);
            }
            if ($post['coverage'] != 0) {
                $this->db->where_in('coverage', $post['coverage']);
            }
        }
        if ($post['status'] == '') {
            $this->db->where('month', $post['month']);
            $this->db->where('year', $post['year']);
            if ($post['router'] != 0) {
                $this->db->where('router', $post['router']);
            }
            if ($post['coverage'] != 0) {
                $this->db->where_in('coverage', $post['coverage']);
            }
        }
        if ($cover != null) {
            $this->db->where_in('coverage', $cover);
        }
        if ($post['jenis_bayar'] != null) {
            $this->db->where('metode_payment', $post['jenis_bayar']);
        }
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }

    public function getInvoiceFilterUnpaid()
    {
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('month', $this->input->post('month'));
        $this->db->where('year', $this->input->post('year'));
        $this->db->where('status', 'BELUM BAYAR');
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getInvoiceFilterpaid()
    {
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('month', $this->input->post('month'));
        $this->db->where('year', $this->input->post('year'));
        $this->db->where('status', 'SUDAH BAYAR');
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getInvoiceP($cover = null)
    {
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('status', 'SUDAH BAYAR');
        if ($cover != null) {
            $this->db->where_in('coverage', $cover);
        }
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getBillThisMonth($no_services)
    {
        $this->db->select('*');
        $this->db->from('invoice');

        $this->db->where('no_services', $no_services);
        $this->db->where('month', date('m'));
        $this->db->where('year', date('Y'));
        $query = $this->db->get();
        return $query;
    }
    public function getBillbyNS($no_services = null)
    {
        $this->db->select('*');
        $this->db->from('invoice');
        $this->db->where('no_services', $no_services);

        $this->db->order_by('created', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getInvoiceThisMonth($month, $year)
    {
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('month', $month);
        $this->db->where('year', $year);
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getInvoicecoverage($post)
    {
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        if ($post['status'] != '') {
            $this->db->where('status', $post['status']);

            if ($post['router'] != 0) {
                $this->db->where('router', $post['router']);
            }
            if ($post['coverage'] != 0) {
                $this->db->where('coverage', $post['coverage']);
            }
        }
        if ($post['status'] == '') {

            if ($post['router'] != 0) {
                $this->db->where('router', $post['router']);
            }
            if ($post['coverage'] != 0) {
                $this->db->where('coverage', $post['coverage']);
            }
        }
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getInvoiceHistory($no_services, $year)
    {
        $this->db->select('*, invoice.created as created_invoice, customer.no_services as no_layanan');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('invoice.no_services', $no_services);
        $this->db->where('year', $year);
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getInvoiceUnpaid()
    {
        $this->db->select('*, customer.name as customer_name, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('status', 'BELUM BAYAR');
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getBillDetail($invoice = null)
    {
        $this->db->select('*, invoice_detail.price as detail_price, package_item.name as item_name, package_category.name as category_name');
        $this->db->from('invoice_detail');
        $this->db->join('package_item', 'package_item.p_item_id = invoice_detail.item_id');
        $this->db->join('package_category', 'package_category.p_category_id = invoice_detail.category_id');
        $this->db->where('invoice_id', $invoice);
        $query = $this->db->get();
        return $query;
    }
    public function fixbilldetail($no_services = null, $month = null, $year = null, $item = null)
    {
        $this->db->select('*, invoice_detail.price as detail_price, package_item.name as item_name, package_category.name as category_name');
        $this->db->from('invoice_detail');
        $this->db->join('package_item', 'package_item.p_item_id = invoice_detail.item_id');
        $this->db->join('package_category', 'package_category.p_category_id = invoice_detail.category_id');
        $this->db->where('d_no_services', $no_services);
        if ($item != null) {
            $this->db->where('item_id', $item);
        }
        $this->db->where('d_month', $month);
        $this->db->where('d_year', $year);
        $query = $this->db->get();
        return $query;
    }
    public function lastitembilldetail($no_services = null, $month = null, $year = null, $item = null)
    {
        $this->db->select('*, invoice_detail.price as detail_price, package_item.name as item_name, package_category.name as category_name');
        $this->db->from('invoice_detail');
        $this->db->join('package_item', 'package_item.p_item_id = invoice_detail.item_id');
        $this->db->join('package_category', 'package_category.p_category_id = invoice_detail.category_id');
        $this->db->where('d_no_services', $no_services);
        if ($item != null) {
            $this->db->where('item_id', $item);
        }
        $this->db->limit('1');
        $this->db->where('d_month', $month);
        $this->db->where('d_year', $year);
        $query = $this->db->get();
        return $query;
    }


    public function getInvoicePaid()
    {
        $this->db->select('*, customer.name as customer_name, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('status', 'SUDAH BAYAR');
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }

    public function getInvoiceSelected($invoice = null)
    {
        $this->db->select('*, customer.name as customer_name, invoice.created as created_invoice, invoice.no_services as noServices');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        if ($invoice != null) {
            $this->db->where_in('invoice', $invoice);
        }
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getInvoiceWhatsapp($status, $month, $year)
    {
        $this->db->select('*, customer.name as customer_name, invoice.created as created_invoice, invoice.no_services as noServices');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where_in('c_status', $status);
        $this->db->where_in('month', $month);
        $this->db->where_in('year', $year);
        $this->db->where_in('status', 'BELUM BAYAR');
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getInvoiceDetail($invoice = null)
    {
        $this->db->select('*');
        $this->db->from('invoice_detail');
        $this->db->where('invoice_id', $invoice);
        $query = $this->db->get();
        return $query;
    }
    public function InvoiceDetail($no_services, $month, $year, $item)
    {
        $this->db->select('*');
        $this->db->from('invoice_detail');
        $this->db->where('d_no_services', $no_services);
        $this->db->where('d_month', $month);
        $this->db->where('d_year', $year);
        $this->db->where('item_id', $item);
        $query = $this->db->get();
        return $query;
    }
    public function getBill($invoice = null)
    {
        $this->db->select('*, invoice.created as created_invoice, invoice.no_services as noServices');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        if ($invoice != null) {
            $this->db->where_in('invoice', $invoice);
        }
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getDetailBill($invoice = null)
    {
        $this->db->select('*, invoice_detail.price as detail_price, package_item.name as item_name,package_item.description as descriptionItem, package_category.name as category_name');
        $this->db->from('invoice_detail');
        $this->db->join('package_item', 'package_item.p_item_id = invoice_detail.item_id');
        $this->db->join('package_category', 'package_category.p_category_id = invoice_detail.category_id');
        $this->db->where('invoice_id', $invoice);
        $query = $this->db->get();
        return $query;
    }
    public function getEditInvoice($invoice)
    {
        $this->db->select('*, invoice_detail.price as detail_price, package_item.name as item_name, package_category.name as category_name');
        $this->db->from('invoice_detail');
        $this->db->join('package_item', 'package_item.p_item_id = invoice_detail.item_id');
        $this->db->join('package_category', 'package_category.p_category_id = invoice_detail.category_id');
        if ($invoice != null) {
            $this->db->where('invoice_id', $invoice);
        }
        $query = $this->db->get();
        return $query;
    }
    public function getPendingPayment($row = null)
    {

        $this->db->select('*, customer.name as customer_name, invoice.created as created_invoice, invoice.no_services as noServices');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('status', 'BELUM BAYAR');
        if ($row != null) {
            $this->db->where_in('coverage', $row);
        }
        $query = $this->db->get();
        return $query;
    }
    public function getTotalPendingPayment()
    {
        $this->db->select('*');
        $this->db->from('invoice_detail');
        $this->db->join('invoice', 'invoice.no_services = invoice_detail.d_no_services');
        $this->db->where('status', 'BELUM BAYAR');
        $query = $this->db->get();
        return $query;
    }

    public function cekItem($p_item_id = null)
    {
        $this->db->select('*');
        $this->db->from('invoice_detail');
        if ($p_item_id != null) {
            $this->db->where('item_id', $p_item_id);
        }
        $query = $this->db->get();
        return $query;
    }
    function invoice_no()
    {
        $tgl = date('ymd');
        $no = 001;
        $kode = ($tgl . '' .  str_pad($no, 3, "0", STR_PAD_LEFT));  //cek jika kode belum terdapat pada table
        $kodetampil = $kode;  //format kode
        return $kodetampil;
    }

    public function addBill($post, $invoice, $ppn, $amount)
    {
        $params = [
            'invoice' => $invoice,
            'month' => $post['month'],
            'year' => $post['year'],
            'i_ppn' => $ppn,
            'disc_coupon' => $post['discount'],
            'amount' => $amount,
            'inv_due_date' => $post['due_date'],
            'date_isolir' => $post['date_isolir'],
            'code_unique' => $post['code_unique'],
            'status' => 'BELUM BAYAR',
            'no_services' => $post['no_services'],
            'created' => time()
        ];

        $this->db->insert('invoice', $params);
    }
    public function add_bill_generate($params)
    {
        $this->db->insert_batch('invoice', $params);
    }
    public function add_bill_detail($params)
    {
        $this->db->insert_batch('invoice_detail', $params);
    }
    public function getCekInvoice($no_services = null)
    {
        $this->db->select('*');
        $this->db->from('invoice');
        if ($no_services != null) {
            $this->db->where('no_services', $no_services);
        }
        $query = $this->db->get();
        return $query;
    }
    public function delete($invoice)
    {
        $this->db->where('invoice', $invoice);
        $this->db->delete('invoice');
    }
    public function deleteselected($invoice)
    {
        $this->db->where_in('invoice', $invoice);
        $this->db->delete('invoice');
    }

    public function deleteDetailBill($post)
    {
        $this->db->where('d_month', $post['month']);
        $this->db->where('d_year', $post['year']);
        $this->db->where('d_no_services', $post['no_services']);
        $this->db->delete('invoice_detail');
    }
    public function deletedetailselected($month, $year, $no_services)
    {
        $this->db->where('d_month', $month);
        $this->db->where('d_year', $year);
        $this->db->where('d_no_services', $no_services);
        $this->db->delete('invoice_detail');
    }
    public function deleteDetailInvoice($invoice)
    {
        $this->db->where('invoice_id', $invoice);
        $this->db->delete('invoice_detail');
    }


    public function cekPeriode($no_services, $month, $year)
    {
        $this->db->select('*');
        $this->db->from('invoice');
        $this->db->where('no_services', $no_services);
        $this->db->where('month', $month);
        $this->db->where('year', $year);
        $query = $this->db->get();
        return $query;
    }
    public function cekPeriodeMonth($month, $year)
    {
        $this->db->select('*');
        $this->db->from('invoice');
        $this->db->where('month', $month);
        $this->db->where('year', $year);
        $query = $this->db->get();
        return $query;
    }
    public function cekInvoice($inv)
    {
        $this->db->select('*');
        $this->db->from('invoice');
        $this->db->where('invoice', $inv);
        $query = $this->db->get();
        return $query;
    }

    public function getDebt($no_services = null)
    {
        $this->db->select('*');
        $this->db->from('invoice');
        $this->db->where('no_services', $no_services);
        $this->db->where('status', 'BELUM BAYAR');
        $query = $this->db->get();
        return $query;
    }

    // PAYMENT 
    public function payment($post)
    {
        $params = [
            'status' => 'SUDAH BAYAR',
            'create_by' => $post['create_by'],
            'metode_payment' => $post['metode_payment'],
            'date_payment' => time(),
            // 'date_paid' => date('Y-m-d'),
        ];
        $this->db->where('invoice', $post['invoice']);
        $this->db->update('invoice', $params);
    }
    public function confirmPayment($post)
    {
        $params = [
            'status' => 'Pending',
            'picture' => $post['picture'],
            'invoice_id' => $post['no_invoice'],
            'no_services' => $post['no_services'],
            'metode_payment' => $post['metode_payment'],
            'date_payment' => $post['date_payment'],
            'remark' => $post['remark'],
            'date_created' => time(),
        ];
        $this->db->insert('confirm_payment', $params);
    }
    public function getPendingConfirm()
    {
        $this->db->select('*');
        $this->db->from('invoice');
        $this->db->where('status', 'BELUM BAYAR');
        $query = $this->db->get();
        return $query;
    }
    public function getConfirm()
    {
        $this->db->select('*');
        $this->db->from('confirm_payment');
        $query = $this->db->get();
        return $query;
    }
    public function UpdateConfirm($post)
    { {
            $params = [
                'status' => 'Terverifikasi'
            ];
            $this->db->where('confirm_id', $post['confirm_id']);
            $this->db->update('confirm_payment', $params);
        }
    }
    public function UpdateConfirmPayment($post)
    { {
            $params = [
                'status' => 'Terverifikasi'
            ];
            $this->db->where('invoice_id', $post['invoice']);
            $this->db->update('confirm_payment', $params);
        }
    }
    public function deleteconfirm($confirm_id)
    {
        $this->db->where('confirm_id', $confirm_id);
        $this->db->delete('confirm_payment');
    }

    // SERVER SIDE
    private function _get_data_query()
    {

        $role_id = $this->session->userdata('role_id');
        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();
        if ($this->session->userdata('role_id') == 3 && $role['coverage_operator'] == 1) {
            $operator = $this->db->get_where('cover_operator', ['operator' => $this->session->userdata('id')])->result();

            foreach ($operator as $roww) {
                $row[] = $roww->coverage_id;
            };
            // var_dump($row);
            // die;
            $this->db->select('*, invoice.created as created_invoice');
            $this->db->from('customer');
            $this->db->join('invoice', 'invoice.no_services = customer.no_services');
            $this->db->where_in('coverage', $row);
            if (isset($_POST['search']['value'])) {
                $this->db->like('name', $_POST['search']['value']);
            }
        } else {
            $this->db->select('*, invoice.created as created_invoice');
            $this->db->from('customer');
            $this->db->join('invoice', 'invoice.no_services = customer.no_services');
            if (isset($_POST['search']['value'])) {
                $this->db->like('invoice', $_POST['search']['value']);
                $this->db->or_like('invoice.no_services', $_POST['search']['value']);
                $this->db->or_like('name', $_POST['search']['value']);
                $this->db->or_like('no_wa', $_POST['search']['value']);
                $this->db->or_like('amount', $_POST['search']['value']);
                $this->db->or_like('month', $_POST['search']['value']);
                $this->db->or_like('year', $_POST['search']['value']);
                $this->db->or_like('status', $_POST['search']['value']);
            }
        }


        if (isset($_POST['order'])) {
            $this->db->order_by($this->order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else {
            $this->db->order_by('invoice', 'desc');
        }
    }
    private function _get_data_queryunpaid()
    {
        $role_id = $this->session->userdata('role_id');
        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();
        if ($role['coverage_operator'] == 1) {
            $operator = $this->db->get_where('cover_operator', ['operator' => $this->session->userdata('id')])->result();

            foreach ($operator as $roww) {
                $row[] = $roww->coverage_id;
            };
            // var_dump($row);
            // die;
            $this->db->select('*, invoice.created as created_invoice');
            $this->db->from('customer');
            $this->db->join('invoice', 'invoice.no_services = customer.no_services');
            $this->db->where('status', 'BELUM BAYAR');
            $this->db->where_in('coverage', $row);
            if (!empty($_POST['ip_search'])) {
                $customerIds = isset($_POST['ip_customer_ids']) ? $_POST['ip_customer_ids'] : [];
                if (empty($customerIds)) {
                    $this->db->where('customer.customer_id', 0);
                } else {
                    $this->db->where_in('customer.customer_id', $customerIds);
                }
            } elseif (isset($_POST['search']['value'])) {
                $this->db->like('invoice.no_services', $_POST['search']['value']);
                $this->db->or_like('name', $_POST['search']['value']);
                $this->db->where('status', 'BELUM BAYAR');
                $this->db->where_in('coverage', $row);
            }
        } else {
            $this->db->select('*, invoice.created as created_invoice');
            $this->db->from('customer');
            $this->db->join('invoice', 'invoice.no_services = customer.no_services');
            $this->db->where('status', 'BELUM BAYAR');
            if (!empty($_POST['ip_search'])) {
                $customerIds = isset($_POST['ip_customer_ids']) ? $_POST['ip_customer_ids'] : [];
                if (empty($customerIds)) {
                    $this->db->where('customer.customer_id', 0);
                } else {
                    $this->db->where_in('customer.customer_id', $customerIds);
                }
            } elseif (isset($_POST['search']['value'])) {
                $this->db->like('invoice.no_services', $_POST['search']['value']);
                $this->db->or_like('name', $_POST['search']['value']);
                $this->db->where('status', 'BELUM BAYAR');
            }
        }
        if (isset($_POST['order'])) {
            $this->db->order_by($this->orderunpaid[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        }


        $this->db->order_by('invoice', 'desc');
    }
    public function count_filtered_data_unpaid()
    {
        $this->_get_data_queryunpaid();
        $query = $this->db->get();
        return $query->num_rows();
    }
    public function count_filtered_data_duedate()
    {
        $this->_get_data_queryduedate();
        $query = $this->db->get();
        return $query->num_rows();
    }


    private function _get_data_querypaid()
    {
        $role_id = $this->session->userdata('role_id');
        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();
        if ($this->session->userdata('role_id') == 3 && $role['coverage_operator'] == 1) {
            $operator = $this->db->get_where('cover_operator', ['operator' => $this->session->userdata('id')])->result();

            foreach ($operator as $roww) {
                $row[] = $roww->coverage_id;
            };
            // var_dump($row);
            // die;
            $this->db->select('*, invoice.created as created_invoice, invoice.metode_payment');
            $this->db->from('customer');
            $this->db->join('invoice', 'invoice.no_services = customer.no_services');
            $this->db->where_in('coverage', $row);
            $this->db->where('status', 'SUDAH BAYAR');
            $this->db->where('month', date('m'));
            if (!empty($_POST['ip_search'])) {
                $customerIds = isset($_POST['ip_customer_ids']) ? $_POST['ip_customer_ids'] : [];
                if (empty($customerIds)) {
                    $this->db->where('customer.customer_id', 0);
                } else {
                    $this->db->where_in('customer.customer_id', $customerIds);
                }
            } elseif (isset($_POST['search']['value'])) {
                $this->db->like('invoice.no_services', $_POST['search']['value']);
                $this->db->or_like('name', $_POST['search']['value']);
                $this->db->where('status', 'SUDAH BAYAR');
                $this->db->where_in('coverage', $row);
                $this->db->where('month', date('m'));
            }
        } else {
            $this->db->select('*, invoice.created as created_invoice');
            $this->db->from('customer');
            $this->db->join('invoice', 'invoice.no_services = customer.no_services');
            $this->db->where('status', 'SUDAH BAYAR');
            $this->db->where('month', date('m'));
            if ($this->session->userdata('role_id') == 3) {
                $this->db->where('invoice.create_by', $this->session->userdata('id'));
            }
            if (!empty($_POST['ip_search'])) {
                $customerIds = isset($_POST['ip_customer_ids']) ? $_POST['ip_customer_ids'] : [];
                if (empty($customerIds)) {
                    $this->db->where('customer.customer_id', 0);
                } else {
                    $this->db->where_in('customer.customer_id', $customerIds);
                }
            } elseif (isset($_POST['search']['value'])) {
                $this->db->like('invoice.no_services', $_POST['search']['value']);
                $this->db->or_like('name', $_POST['search']['value']);
                $this->db->where('status', 'SUDAH BAYAR');
                $this->db->where('month', date('m'));
            }
        }
        if (isset($_POST['order'])) {
            $this->db->order_by($this->order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        }

        $this->db->order_by('invoice', 'desc');
    }
    public function count_filtered_data_paid()
    {
        $this->_get_data_querypaid();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all_data_paid()
    {
        $this->_get_data_querypaid();
        return $this->db->count_all_results();
    }
    public function count_all_data_unpaid()
    {
        $this->_get_data_queryunpaid();
        return $this->db->count_all_results();
    }
    public function count_all_data_duedate()
    {
        $this->_get_data_queryduedate();
        return $this->db->count_all_results();
    }
    public function getDataInv()
    {
        $this->_get_data_query();
        if ($_POST['lengt'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }
    public function getUnpaidInv()
    {
        $this->_get_data_queryunpaid();
        if ($_POST['lengt'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }
    public function getduedate()
    {
        $this->_get_data_queryduedate();

        $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }
    private function _get_data_queryduedate()
    {

        $role_id = $this->session->userdata('role_id');
        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();

        if ($this->session->userdata('role_id') != 1 && $role['coverage_operator'] == 1) {
            $operator = $this->db->get_where('cover_operator', ['operator' => $this->session->userdata('id')])->result();

            foreach ($operator as $roww) {
                $row[] = $roww->coverage_id;
            };
            // var_dump($row);
            // die;
            $this->db->select('*, invoice.created as created_invoice, customer.no_services as noservices');
            $this->db->from('customer');
            $this->db->join('invoice', 'invoice.no_services = customer.no_services');
            $this->db->where('status', 'BELUM BAYAR');
            $datetoday = date('Y-m-d');
            $this->db->where("invoice.inv_due_date BETWEEN '" . ('2018-01-01') . "' AND '" . ($datetoday) . "'");
            $this->db->where_in('coverage', $row);
            if (isset($_POST['search']['value'])) {
                $this->db->like('name', $_POST['search']['value']);
                $this->db->where('status', 'BELUM BAYAR');
                $this->db->where_in('coverage', $row);
            }
        } else {
            $this->db->select('*, invoice.created as created_invoice');
            $this->db->from('customer');
            $this->db->join('invoice', 'invoice.no_services = customer.no_services');
            $datetoday = date('Y-m-d');
            $this->db->where("invoice.inv_due_date BETWEEN '" . ('2018-01-01') . "' AND '" . ($datetoday) . "'");
            $this->db->where('status', 'BELUM BAYAR');
            if (isset($_POST['search']['value'])) {

                $this->db->like('name', $_POST['search']['value']);
                $this->db->where('status', 'BELUM BAYAR');
            }
        }
        if (isset($_POST['order'])) {
            $this->db->order_by($this->orderduedate[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        }


        $this->db->order_by('created_invoice', 'asc');
    }

    public function getPaidInv()
    {
        $this->_get_data_querypaid();
        if ($_POST['lengt'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function count_filtered_data()
    {
        $this->_get_data_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all_data()
    {
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }


    public function payselected($invoice = null)
    {

        if ($invoice != null) {
            $params = [
                'metode_payment' => 'Cash',
                'status' => 'SUDAH BAYAR',
                'create_by' => $this->session->userdata('id'),
                'date_payment' => time(),

            ];
            $this->db->where_in('invoice', $invoice);
            $this->db->update('invoice', $params);
        }
    }
    public function confirmselected($invoice = null)
    {

        $this->db->from('confirm_payment');
        if ($invoice != null) {
            $this->db->where_in('invoice_id', $invoice);
        }
        $this->db->order_by('date_payment', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getreceipt()
    {
        $this->db->select('*');
        $this->db->from('user');
        $this->db->where('email !=', 'ginginabdulgoni@gmail.com');
        $this->db->where('role_id !=', 2);
        $query = $this->db->get();
        return $query;
    }
    public function getisolirpasca($router = null)
    {

        $month = date('m');
        $year = date('Y');
        $this->db->select('*, invoice.created as created_invoice, invoice.no_services as noservices');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('status', 'BELUM BAYAR');
        $this->db->where('month', $month);
        $this->db->where('year', $year);

        $query = $this->db->get();
        return $query;
    }
    public function cekbill($noservices)
    {


        $this->db->select('*, invoice.created as created_invoice, invoice.no_services as noservices');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('status', 'BELUM BAYAR');
        $this->db->where('c_status', 'Aktif');
        $this->db->where('invoice.no_services', $noservices);
        $query = $this->db->get();
        return $query;
    }
    public function getInvoicemonthyeardouble($no_services, $month, $year)
    {
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->where('no_services', $no_services);
        $this->db->where('month', $month);
        $this->db->where('year', $year);
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getInvoicemonthyeardoubleget($no_services, $month, $year)
    {
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->where('no_services', $no_services);
        $this->db->where('month', $month);
        $this->db->where('year', $year);
        $this->db->limit('1');
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }

    public function getfixduedate()
    {
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('inv_due_date', '');
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
    public function getfixisolir()
    {
        $this->db->select('*');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('date_isolir', '');
        $query = $this->db->get();
        return $query;
    }

    public function fixincomethismonth($month, $year)
    {

        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('status', 'SUDAH BAYAR');
        $this->db->where('month', $month);
        $this->db->where('year', $year);
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }

    // Filter coverage 00:50 03-Maret-2022
    public function getfilterbycoverage($post)
    {
        $this->_get_data_queryfiltercoverage($post);
        if ($_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        // $query->result();
        // echo $this->db->last_query();die;
        // var_dump();die;
        return $query->result();
    }
    private function _get_data_queryfiltercoverage($post)
    {
        $post_coverage = $post['coverage'];
        
        $role_id = $this->session->userdata('role_id');
        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        // if ($post['status'] == 'BELUM BAYAR') {
        //         $this->db->where('status', 'BELUM BAYAR');
        //     } elseif ($post['status'] == 'SUDAH BAYAR') {
        //         $this->db->where('status', 'SUDAH BAYAR');
        //     } elseif ($post['status'] == 'Jatuh Tempo') {
        //     } elseif ($post['status'] == 'all') {
        //         # code...
        //     }
        //     if ($post['coverage'] == '') {
        //         $this->db->where('coverage', 0);
        //     } elseif ($post_coverage == 'all') {
        //         # code...
        //     } else {
        //         $this->db->where_in('coverage', $post_coverage);
        //     }
        // $query = $this->db->get();
        // var_dump($post['coverage']);die;
        
        if ($role['coverage_operator'] == 1) {
            //  echo 'query atas';die;
            $operator = $this->db->get_where('cover_operator', ['operator' => $this->session->userdata('id')])->result();

            foreach ($operator as $roww) {
                $row[] = $roww->coverage_id;
            };
            $this->db->select('*, invoice.created as created_invoice');
            $this->db->from('invoice');
            $this->db->join('customer', 'customer.no_services = invoice.no_services');
            if ($post['status'] == 'BELUM BAYAR') {
                $this->db->where('status', 'BELUM BAYAR');
            } elseif ($post['status'] == 'SUDAH BAYAR') {
                $this->db->where('status', 'SUDAH BAYAR');
            } elseif ($post['status'] == 'Jatuh Tempo') {
            } elseif ($post['status'] == 'all') {
                # code...
            }
            if ($post['coverage'] == '') {
                $this->db->where('coverage', 0);
            } elseif ($post['coverage'] == array('all')) {
                # code...
                $this->db->where_in('coverage', $row);
            } else {
                $this->db->where_in('coverage', $post_coverage);
            }
        } else {
            if ($post['status'] == 'BELUM BAYAR') {
                $this->db->where('status', 'BELUM BAYAR');
            } elseif ($post['status'] == 'SUDAH BAYAR') {
                $this->db->where('status', 'SUDAH BAYAR');
            } elseif ($post['status'] == 'Jatuh Tempo') {
            } elseif ($post['status'] == 'all') {
                # code...
            }
            if ($post['coverage'] == '') {
                $this->db->where('coverage', 0);
            } elseif ($post_coverage == array('all')) {
                # code...
            } else {
                $this->db->where_in('coverage', $post_coverage);
            }
        }

        if ($post['month'] != '') {
            $this->db->where('month', $post['month']);
        }
        $this->db->where('year', $post['year']);
        
        if (!empty($_POST['ip_search'])) {
            $customerIds = isset($_POST['ip_customer_ids']) ? $_POST['ip_customer_ids'] : [];
            if (empty($customerIds)) {
                $this->db->where('customer.customer_id', 0);
            } else {
                $this->db->where_in('customer.customer_id', $customerIds);
            }
        } elseif (isset($_POST['search']['value'])) {
            $this->db->like('invoice.no_services', $_POST['search']['value']);
            $this->db->or_like('name', $_POST['search']['value']);

            if ($role['coverage_operator'] == 1) {
                if ($post['status'] == 'BELUM BAYAR') {
                    $this->db->where('status', 'BELUM BAYAR');
                } elseif ($post['status'] == 'SUDAH BAYAR') {
                    $this->db->where('status', 'SUDAH BAYAR');
                } elseif ($post['status'] == 'Jatuh Tempo') {
                } elseif ($post['status'] == 'all') {
                    # code...
                }
                if ($post['coverage'] == '') {
                    $this->db->where('coverage', 0);
                } elseif ($post['coverage'] == array('all')) {
                    # code...
                    $this->db->where_in('coverage', $row);
                } else {
                    $this->db->where_in('coverage', $post_coverage);
                }
            } else {
                if ($post['status'] == 'BELUM BAYAR') {
                    $this->db->where('status', 'BELUM BAYAR');
                } elseif ($post['status'] == 'SUDAH BAYAR') {
                    $this->db->where('status', 'SUDAH BAYAR');
                } elseif ($post['status'] == 'Jatuh Tempo') {
                } elseif ($post['status'] == 'all') {
                    # code...
                }
                if ($post['coverage'] == '') {
                    $this->db->where('coverage', 0);
                } elseif ($post['coverage'] == array('all')) {
                    # code...
                } else {
                    $this->db->where_in('coverage', $post_coverage);
                }
            }
            if ($post['month'] != '') {
                $this->db->where('month', $post['month']);
            }
            $this->db->where('year', $post['year']);
        }
        if (isset($_POST['order'])) {
            $this->db->order_by($this->orderfilter[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else {
            $this->db->order_by('invoice', 'desc');
        }
    }
    public function count_filtered_data_coverage($post)
    {
        $this->_get_data_queryfiltercoverage($post);
        $query = $this->db->get();
        return $query->num_rows();
    }
    public function getBillFilter($post)
    {
        $role_id = $this->session->userdata('role_id');
        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();
        $this->db->select('*, invoice.created as created_invoice');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');

        if ($role['coverage_operator'] == 1) {
            $operator = $this->db->get_where('cover_operator', ['operator' => $this->session->userdata('id')])->result();

            foreach ($operator as $roww) {
                $row[] = $roww->coverage_id;
            };
            $this->db->select('*, invoice.created as created_invoice');
            $this->db->from('invoice');
            $this->db->join('customer', 'customer.no_services = invoice.no_services');
            if ($post['status'] == 'BELUM BAYAR') {
                $this->db->where('status', 'BELUM BAYAR');
            } elseif ($post['status'] == 'SUDAH BAYAR') {
                $this->db->where('status', 'SUDAH BAYAR');
            } elseif ($post['status'] == 'Jatuh Tempo') {
            } elseif ($post['status'] == 'all') {
                # code...
            }
            if ($post['coverage'] == '') {
                $this->db->where('coverage', 0);
            } elseif ($post['coverage'] == array('all')) {
                # code...
                $this->db->where_in('coverage', $row);
            } else {
                $this->db->where_in('coverage', $post['coverage']);
            }
        } else {
            if ($post['status'] == 'BELUM BAYAR') {
                $this->db->where('status', 'BELUM BAYAR');
            } elseif ($post['status'] == 'SUDAH BAYAR') {
                $this->db->where('status', 'SUDAH BAYAR');
            } elseif ($post['status'] == 'Jatuh Tempo') {
            } elseif ($post['status'] == 'all') {
                # code...
            }
            if ($post['coverage'] == '') {
                $this->db->where('coverage', 0);
            } elseif ($post['coverage'] == array('all')) {
                # code...
            } else {
                $this->db->where_in('coverage', $post['coverage']);
            }
        }
        if ($post['month'] != '') {
            $this->db->where('month', $post['month']);
        }
        $this->db->where('year', $post['year']);
        $this->db->order_by('created_invoice', 'DESC');
        $query = $this->db->get();
        return $query;
    }
}
