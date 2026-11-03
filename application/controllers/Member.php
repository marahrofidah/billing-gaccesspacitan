<?php
ob_start();
defined('BASEPATH') or exit('No direct script access allowed');

use Xendit\Xendit;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

class Member extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        is_logged_in();
        $this->load->model(['member_m', 'product_m', 'logs_m', 'package_m',  'setting_m', 'services_m', 'customer_m', 'bill_m', 'income_m', 'mikrotik_m']);
    }

    public function index()
    {


        $data['title'] = 'Dashboard';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $no_services = $this->member_m->getmember($this->session->userdata('email'))->row();
        $pg = $this->db->get('payment_gateway')->row_array();
        if ($no_services != '') {
            $month = date('m');
            $year = date('Y');
            $billdetail = $this->bill_m->fixbilldetail($no_services->no_services, $month, $year)->result();
            foreach ($billdetail as $unit) {
                $item =  $this->bill_m->fixbilldetail($no_services->no_services, $month, $year, $unit->item_id)->num_rows();
                if ($item > 1) {
                    $lasitem =  $this->bill_m->lastitembilldetail($no_services->no_services, $month, $year, $unit->item_id)->row_array();
                    $this->db->where('detail_id', $lasitem['detail_id']);
                    $this->db->delete('invoice_detail');
                }
            }
            $data['CountServices'] = $this->services_m->getServices($no_services->no_services)->num_rows();
            $data['no_services'] = $no_services->no_services;
            $bill = $this->bill_m->getBillThisMonth($no_services->no_services)->row();
            $dashboardView = 'member/dashboard';
            if ($bill != '') {


                $data['totalBill'] = $this->bill_m->getBillThisMonth($no_services->no_services)->num_rows();
                $data['CountBill'] = $this->bill_m->getBillDetail($bill->invoice)->result();
                $data['bank'] = $this->setting_m->getBank()->result();
                $data['other'] = $this->db->get('other')->row_array();

                $data['invoice'] = $this->bill_m->getBillThisMonth($no_services->no_services)->row();
                $data['services'] = $this->services_m->getServices($no_services->no_services)->result();
            } else {
                $data['totalBill'] = 0;
                $data['CountBill'] = 0;
                $dashboardView = 'member/dashboard_empty';
            }
            $data['company'] = $this->db->get('company')->row_array();

            $this->template->load('member', $dashboardView, $data);
        } else {
            $this->session->set_flashdata('error', 'Email anda belum memiliki layanan yang aktif, silahkan hubungi kami untuk informasi lebih lanjut atau bantuan');
            redirect('member/about');
        }
    }
    public function status()
    {
        $data['title'] = 'Status';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $no_services = $this->member_m->getmember($this->session->userdata('email'))->row();
        if ($no_services != '') {
            $data['invoice'] = $this->bill_m->getBillbyNS($no_services->no_services)->result();
            $data['customer'] = $this->customer_m->getNSCustomer($no_services->no_services)->row_array();
            $data['services'] = $this->services_m->getServices($no_services->no_services)->result();
            $data['other'] = $this->db->get('other')->row_array();
            $data['company'] = $this->db->get('company')->row_array();
            $this->template->load('member', 'member/status', $data);
        } else {
            $this->session->set_flashdata('error', 'Email anda belum memiliki layanan yang aktif, silahkan hubungi kami untuk informasi lebih lanjut atau bantuan');
            redirect('member/about');
        }
    }
    public function history()
    {
        $data['title'] = 'History';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $no_services = $this->member_m->getmember($this->session->userdata('email'))->row();
        if ($no_services != '') {
            $data['invoice'] = $this->bill_m->getBillbyNS($no_services->no_services)->result();
            $data['company'] = $this->db->get('company')->row_array();
            $data['bank'] = $this->setting_m->getBank()->result();
            $data['other'] = $this->db->get('other')->row_array();
            $data['customer'] = $this->customer_m->getNSCustomer($no_services->no_services)->row_array();
            $data['services'] = $this->services_m->getServices($no_services->no_services)->result();
            $this->template->load('member', 'member/history', $data);
        } else {
            $this->session->set_flashdata('error', 'Email anda belum memiliki layanan yang aktif, silahkan hubungi kami untuk informasi lebih lanjut atau bantuan');
            redirect('member/about');
        }
    }
    public function profile()
    {
        $data['title'] = 'Profile';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('member', 'member/profile', $data);
    }
    public function account()
    {
        $data['title'] = 'Account';
        $config['upload_path'] = './assets/images/profile/';
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size']     = '2048';
        $config['file_name']  = 'profile-' . $this->input->post('name') . '-' . date('ymd') . '-' . substr(md5(rand()), 0, 10);

        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $this->form_validation->set_rules('name', 'Full Name', 'required|trim');
        $this->form_validation->set_rules('phone', 'Phone', 'required|trim|min_length[8]');
        $this->form_validation->set_rules('gender', 'Gender', 'required|trim');
        if ($this->form_validation->run() == false) {
            $this->template->load('member', 'member/account', $data);
        } else {
            $name = $this->input->post('name');
            $email = $this->input->post('email');
            $phone = $this->input->post('phone');
            $gender = $this->input->post('gender');
            $address = $this->input->post('address');
            $image1 = $this->input->post('image1');

            // cek jika ada gambar
            $upload_image = $_FILES['image']['name'];

            if ($upload_image) {
                $this->load->library('upload', $config);
                if ($this->upload->do_upload('image')) {
                    $old_image = $data['user']['image'];
                    if ($old_image != 'default.jpg') {
                        unlink(FCPATH . '/assets/images/profile/' . $old_image);
                    }
                    $new_image = $this->upload->data('file_name');
                } else {
                    $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
                    redirect('member/account');
                }
            }
            if ($new_image != null) {
                $this->db->set('image', $new_image);
            } else {
                $this->db->set('image', $image1);
            }
            $this->db->set('name', $name);
            $this->db->set('email', $email);
            $this->db->set('phone', $phone);
            $this->db->set('gender', $gender);
            $this->db->set('address', $address);
            $this->db->where('email', $email);
            $this->db->update('user');
            if ($this->db->affected_rows() > 0) {
                $customer = $this->db->get_where('customer', ['email' => $email])->row_array();
                if ($customer > 0) {
                    $this->db->set('name', $name);

                    $this->db->set('no_wa', $phone);
                    $this->db->set('address', $address);
                    $this->db->where('customer_id', $customer['customer_id']);
                    $this->db->update('customer');
                }
                $remarklog = 'Edit data profile';
                $this->logs_m->activitylogs('Activity', $remarklog);
                $this->session->set_flashdata('success-sweet', 'Your profile has been updated!');
            }
            redirect('member/account');
        }
    }
    public function changepassword()
    {
        $data['title'] = 'Ganti Password';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->form_validation->set_rules('current_password', 'Current Password', 'required|trim');
        $this->form_validation->set_rules('new_password1', 'New Password', 'required|trim|min_length[5]|matches[new_password2]');
        $this->form_validation->set_rules('new_password2', 'Confirm Password', 'required|trim|min_length[5]|matches[new_password1]');
        if ($this->form_validation->run() == false) {
            $data['company'] = $this->db->get('company')->row_array();
            $this->template->load('member', 'member/changepassword', $data);
        } else {
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password1');
            if (!password_verify($current_password, $data['user']['password'])) {
                $this->session->set_flashdata('error', 'Password lama salah !');
                redirect('member/changepassword');
            } else {
                if ($current_password == $new_password) {
                    $this->session->set_flashdata('error', 'Password baru tidak boleh sama dengan password lama!');
                    redirect('member/changepassword');
                } else {
                    // password benar
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                    $this->db->set('password', $password_hash);
                    $this->db->where('email', $this->session->userdata('email'));
                    $this->db->update('user');
                    if ($this->db->affected_rows() > 0) {
                        $this->session->set_flashdata('success-sweet', 'Password baru sudah diperbaharui!');

                        $this->logs_m->activitylogs('Activity', 'Ganti Password');
                    }
                    redirect('member/changepassword');
                }
            }
        }
    }

    public function about()
    {
        $data['title'] = 'Tentang';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('member', 'member/about', $data);
    }
    public function testimoni()
    {
        $data['title'] = 'Testimoni';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('member', 'member/testimoni', $data);
    }


    public function addTestimoni()
    {
        $post = $this->input->post(null, TRUE);
        $this->member_m->addTestimoni($post);
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Terimakasih atas testimoni yang anda berikan kepada kami');
        }
        echo "<script>window.location='" . site_url('member/testimoni') . "'; </script>";
    }

    public function editTestimoni()
    {
        $post = $this->input->post(null, TRUE);
        $this->member_m->editTestimoni($post);
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Terimakasih atas testimoni yang anda berikan kepada kami');
        }
        echo "<script>window.location='" . site_url('member/testimoni') . "'; </script>";
    }

    public function invoice($invoice, $a = null, $b = null, $c = null, $d = null)
    {
        // echo $invoice . '/' . $a . '/' . $b . '/' . $c .
        //     var_dump($invoice, $a, $b, $c);
        // die;
        if ($a == null) {
            $invoice =  $invoice;
        } elseif ($b == null) {
            $invoice = '' . $invoice . '/' . $a;
        } elseif ($c == null) {
            $invoice = '' . $invoice . '/' . $a . '/' . $b;
        } elseif ($d == null) {
            $invoice = '' . $invoice . '/' . $a . '/' . $b . '/' . $c;
        } else {
            $invoice = '' . $invoice . '/' . $a . '/' . $b . '/' . $c . '/' . $d;
        }
        $oldinvoicedetail = $this->db->get_where('invoice_detail', ['invoice_id' => $invoice])->row_array();
        $oldinvoice = $this->db->get_where('invoice', ['invoice' => $invoice])->row_array();
        if ($oldinvoicedetail['d_month'] == 0) {
            $update = [
                'd_month' => $oldinvoice['month'],
                'd_year' => $oldinvoice['year'],
                'd_no_services' => $oldinvoice['no_services'],
            ];
            $this->db->where('invoice_id', $invoice);
            $this->db->update('invoice_detail', $update);
        }
        // var_dump($invoice);
        // die;
        $data['title'] = 'Invoice';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['invoice'] = $this->bill_m->getBill($invoice);
        $data['invoice_detail'] = $this->bill_m->getDetailBill($invoice);
        $data['bill'] = $this->bill_m->getBill($invoice)->row_array();
        $data['other'] = $this->db->get('other')->row_array();
        $data['bank'] = $this->setting_m->getBank()->result();
        $data['p_item'] = $this->package_m->getPItem()->result();
        $this->load->library('ciqrcode'); //pemanggilan library QR CODE
        $config['cacheable']    = true; //boolean, the default is true
        $config['cachedir']     = './assets/'; //string, the default is application/cache/
        $config['errorlog']     = './assets/'; //string, the default is application/logs/
        $config['imagedir']     = './assets/images/qrcode/'; //direktori penyimpanan qr code
        $config['quality']      = true; //boolean, the default is true
        $config['size']         = '1024'; //interger, the default is 1024
        $config['black']        = array(224, 255, 255); // array, default is array(255,255,255)
        $config['white']        = array(70, 130, 180); // array, default is array(0,0,0)
        $inv = $this->db->get_where('invoice', ['invoice' => $invoice])->row_array();
        $this->ciqrcode->initialize($config);
        $image_name = $invoice . '.png'; //buat name dari qr code
        $params['data'] = $invoice . '-' . $inv['no_services']; //data yang akan di jadikan QR CODE
        $params['level'] = 'H'; //H=High
        $params['size'] = 10;
        $params['savename'] = FCPATH . $config['imagedir'] . $image_name; //simpan image QR CODE ke folder assets/images/
        $this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
        $this->logs_m->invoice($invoice);
        $this->load->view('backend/bill/invoice', $data);
    }

    public function confirmpayment($invoice, $a = null, $b = null, $c = null, $d = null)
    {
        if ($a == null) {
            $invoice =  $invoice;
        } elseif ($b == null) {
            $invoice = '' . $invoice . '/' . $a;
        } elseif ($c == null) {
            $invoice = '' . $invoice . '/' . $a . '/' . $b;
        } elseif ($d == null) {
            $invoice = '' . $invoice . '/' . $a . '/' . $b . '/' . $c;
        } else {
            $invoice = '' . $invoice . '/' . $a . '/' . $b . '/' . $c . '/' . $d;
        }
        $oldinvoicedetail = $this->db->get_where('invoice_detail', ['invoice_id' => $invoice])->row_array();
        $oldinvoice = $this->db->get_where('invoice', ['invoice' => $invoice])->row_array();
        if ($oldinvoicedetail['d_month'] == 0) {
            $update = [
                'd_month' => $oldinvoice['month'],
                'd_year' => $oldinvoice['year'],
                'd_no_services' => $oldinvoice['no_services'],
            ];
            $this->db->where('invoice_id', $invoice);
            $this->db->update('invoice_detail', $update);
        }
        $data['title'] = 'Konfirmasi Pembayaran';
        $cekinvoice = $this->db->get_where('invoice', ['invoice' => $invoice])->row_array();
        if ($cekinvoice <= 0) {
            $this->session->set_flashdata('error', 'Invoice tidak ditemukan');
            echo "<script>window.location='" . base_url('member/history') . "'; </script>";
        }
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['bill'] = $this->member_m->getInvoice($invoice)->row_array();
        $data['other'] = $this->db->get('other')->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('member', 'member/confirm-payment', $data);
    }




    // XENDIT
    public function xcheckout($invoice)
    {
        $data['title'] = 'Checkout';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['invoice'] =  $this->db->get_where('invoice', ['x_external_id' => $invoice])->row_array();
        $this->template->load('member', 'member/xendit-checkout', $data);
    }


    public function logs()
    {
        $data['title'] = 'Logs';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['logs'] = $this->logs_m->getlogsuser()->result();
        $this->template->load('member', 'member/logs', $data);
    }


    // Bantuan
    public function help()
    {
        $data['title'] = 'Help';
        $data['company'] = $this->db->get('company')->row_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('member', 'member/help/help', $data);
    }
    public function speedtest()
    {
        $data['title'] = 'Speedtest';
        $data['company'] = $this->db->get('company')->row_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('member', 'member/speedtest', $data);
    }


    public function pay($invoice)
    {
        $inv = $this->db->get_where('invoice', ['invoice' => "$invoice"])->row_array();
        $month =  $inv['month'];
        $year = $inv['year'];
        $no_services = $inv['no_services'];

        $query = "SELECT *, `invoice_detail`.`price` as `detail_price`
            FROM `invoice_detail`
                          WHERE `invoice_detail`.`d_month` =  $month and
               `invoice_detail`.`d_year` =  $year and
               `invoice_detail`.`d_no_services` =  $no_services";
        $detailinvoice = $this->db->query($query)->num_rows();
        if ($detailinvoice == 0) {
            $Detail = $this->services_m->getServicesDetail($inv['no_services'])->result();
            $data2 = [];
            foreach ($Detail as $c => $row) {
                array_push(
                    $data2,
                    array(
                        'invoice_id' => $invoice,
                        'item_id' => $row->item_id,
                        'category_id' => $row->category_id,
                        'price' => $row->services_price,
                        'qty' => $row->qty,
                        'disc' => $row->disc,
                        'remark' => $row->remark,
                        'total' => $row->total,
                        'd_month' => $month,
                        'd_year' => $year,
                        'd_no_services' => $no_services,
                    )
                );
            }
            $this->bill_m->add_bill_detail($data2);
        }
        $billdetail = $this->bill_m->fixbilldetail($no_services, $month, $year)->result();
        foreach ($billdetail as $unit) {
            $item =  $this->bill_m->fixbilldetail($no_services, $month, $year, $unit->item_id)->num_rows();
            if ($item > 1) {
                $lasitem =  $this->bill_m->lastitembilldetail($no_services, $month, $year, $unit->item_id)->row_array();
                $this->db->where('detail_id', $lasitem['detail_id']);
                $this->db->delete('invoice_detail');
            }
        }
        $pg = $this->db->get('payment_gateway')->row_array();
        $bill = $this->db->get_where('invoice', ['invoice' => $invoice])->row();
        // var_dump($bill);
        // die;
        if ($pg['vendor'] == 'Xendit') {
            if ($bill->x_id != '') {
                Xendit::setApiKey($pg['api_key']);
                $getInvoice = \Xendit\Invoice::retrieve($bill->x_id);
                if ($getInvoice['status'] != 'EXPIRED') {
                    redirect($getInvoice['invoice_url']);
                }
            }
        }
        $data['title'] = 'Checkout';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['invoice'] =  $this->db->get_where('invoice', ['invoice' => $invoice])->row();
        $this->template->load('member', 'member/pay', $data);
    }
    public function modem()
    {
        $data['title'] = 'Modem';
        $customer = $this->db->get_where('customer', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['customer'] = $this->db->get_where('customer', ['email' => $this->session->userdata('email')])->row_array();
        $data['modem'] = $this->member_m->getmodem($customer['customer_id'])->result();
        $this->template->load('member', 'member/modem', $data);
    }
    public function updatepassword()
    {
        $post = $this->input->post(null, TRUE);

        $bot = $this->db->get('bot_telegram')->row_array();
        $tokens = $bot['token']; // token bot
        $owner = $bot['id_group_teknisi'];
        $sendmessage = [
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        // ['text' => '✅ Konfirmasi', 'url' => base_url('confirmdetail/' . $post['no_invoice'])],

                    ]
                ]
            ]),
            'resize_keyboard' => true,
            'parse_mode' => 'html',
            'text' => "<b>Permintaan Ganti Password Modem</b>\nNama : $post[name]\nNo Layanan : $post[no_services]\nPassword Baru : $post[new_password]\nKeterangan : $post[remark]\n",
            'chat_id' => $owner
        ];

        file_get_contents("https://api.telegram.org/bot$tokens/sendMessage?" . http_build_query($sendmessage));
        $remarklog = 'Request Ganti Password Modem';
        $this->logs_m->activitylogs('Activity', $remarklog);
        $this->session->set_flashdata('success-sweet', 'Permintaan anda akan kami proses, mohon ditunggu');
        redirect('member');
    }
}
