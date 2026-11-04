<?php



ob_start();
defined('BASEPATH') or exit('No direct script access allowed');



class customer extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        is_logged_in();

        $this->load->model(['customer_m', 'services_m', 'bill_m', 'coverage_m', 'mikrotik_m', 'logs_m']);
    }

    private function _menu()
    {
        $menu = $this->db->get_where('role_menu', ['role_id' => $this->session->userdata('role_id')])->row_array();
        if ($menu == 0) {
            $params = [
                'role_id' => $this->session->userdata('role_id'),
            ];
            $this->db->insert('role_menu', $params);
        }
        return $menu;
    }
    private function _role()
    {
        $role_id = $this->session->userdata('role_id');
        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();
        return $role;
    }
    private function _coverage()
    {
        $role = $this->_role();
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $operator = $this->db->get_where('cover_operator', ['operator' => $this->session->userdata('id'), 'role_id' => $this->session->userdata('role_id')])->result();
            if ($this->session->userdata('role_id') != 1 && count($operator) == 0) {
                echo "<script> alert ('Tidak ada coverage untuk akun anda')</script>";
                $row[] = '';
            }
            foreach ($operator as $roww) {
                $row[] = $roww->coverage_id;
            };
            return $row;
        }
    }
    public function index()
    {
        $menu = $this->_menu();
        $role = $this->_role();
        $cover = $this->_coverage();
        if ($menu['role_id'] != 1 && $menu['customer_menu'] == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $data['coverage'] = $this->coverage_m->getCoverage($cover)->result();
        } else {
            $data['coverage'] = $this->coverage_m->getCoverage()->result();
        }
        $data['title'] = 'Customer';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['customer'] = $this->customer_m->getCustomer()->result();
        $data['company'] = $this->db->get('company')->row_array();

        $this->template->load('backend', 'backend/customer/data', $data);
    }
    public function active()
    {
        $menu = $this->_menu();
        $role = $this->_role();
        $rolecoverage = $this->_coverage();
        if ($menu['role_id'] != 1  && $menu['customer_active'] == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        $cover = $this->_coverage();
        if ($role['role_id'] != 1  && $role['coverage_operator'] == 1) {
            $data['coverage'] = $this->coverage_m->getCoverage($rolecoverage)->result();
            $data['customer'] = $this->customer_m->getCustomerAll($cover)->result();
        } else {
            $data['coverage'] = $this->coverage_m->getCoverage()->result();
            $data['customer'] = $this->customer_m->getCustomerAll()->result();
        }
        $data['title'] = 'Aktif';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/customer/data', $data);
    }
    public function whatsapp()
    {
        $menu = $this->_menu();
        $role = $this->_role();
        $rolecoverage = $this->_coverage();
        if ($menu['role_id'] != 1  && $menu['customer_whatsapp'] == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($role['role_id'] != 1  && $role['coverage_operator'] == 1) {

            $data['customer'] = $this->customer_m->getCustomerAll($rolecoverage)->result();
            $data['coverage'] = $this->coverage_m->getCoverage($rolecoverage)->result();
        } else {
            $data['customer'] = $this->customer_m->getCustomerAll()->result();
            $data['coverage'] = $this->coverage_m->getCoverage()->result();
        }
        $data['title'] = 'Whatsapp Pelanggan';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/customer/whatsapp', $data);
    }
    public function nonactive()
    {
        $menu = $this->_menu();
        $role = $this->_role();
        $rolecoverage = $this->_coverage();
        $cover = $this->_coverage();
        if ($menu['role_id'] != 1 && $menu['customer_non_active'] == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($role['role_id'] != 1  && $role['coverage_operator'] == 1) {
            $data['coverage'] = $this->coverage_m->getCoverage($rolecoverage)->result();
            $data['customer'] = $this->customer_m->getCustomerAll($cover)->result();
        } else {
            $data['coverage'] = $this->coverage_m->getCoverage()->result();
            $data['customer'] = $this->customer_m->getCustomerAll()->result();
        }
        $data['title'] = 'Non-Aktif';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/customer/data', $data);
    }
    public function wait()
    {
        $menu = $this->_menu();
        $role = $this->_role();
        $cover = $this->_coverage();
        if ($menu['role_id'] != 1 && $menu['customer_waiting'] == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $data['coverage'] = $this->coverage_m->getCoverage($cover)->result();
            $data['customer'] = $this->customer_m->getCustomerAll($cover)->result();
        } else {
            $data['coverage'] = $this->coverage_m->getCoverage()->result();
            $data['customer'] = $this->customer_m->getCustomerAll()->result();
        }
        $data['title'] = 'Waiting';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/customer/data', $data);
    }
    public function free()
    {
        $menu = $this->_menu();
        $role = $this->_role();
        $cover = $this->_coverage();
        if ($menu['role_id'] != 1 && $menu['customer_waiting'] == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $data['coverage'] = $this->coverage_m->getCoverage($cover)->result();
            $data['customer'] = $this->customer_m->getCustomerAll($cover)->result();
        } else {
            $data['coverage'] = $this->coverage_m->getCoverage()->result();
            $data['customer'] = $this->customer_m->getCustomerAll()->result();
        }
        $data['title'] = 'Free';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/customer/data', $data);
    }
    public function isolir()
    {
        $menu = $this->_menu();
        $role = $this->_role();
        $cover = $this->_coverage();
        if ($menu['role_id'] != 1 && $menu['customer_isolir'] == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $data['coverage'] = $this->coverage_m->getCoverage($cover)->result();
            $data['customer'] = $this->customer_m->getCustomerAll()->result();
        } else {
            $data['coverage'] = $this->coverage_m->getCoverage()->result();
            $data['customer'] = $this->customer_m->getCustomerAll()->result();
        }
        $data['title'] = 'Isolir';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/customer/data', $data);
    }

    public function filterby()
    {
        $cover = $this->_coverage();
        $post = $this->input->post(null, TRUE);
        $data['title'] = 'Customer Filter';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['customer'] = $this->customer_m->getfilterby($post, $cover)->result();
        $data['ip_by_customer'] = $this->getCustomerIpMap($data['customer']);
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/customer/filterby', $data);
    }
    public function fixnoservices()
    {
        $customerall = $this->db->get('customer')->result();
        foreach ($customerall as $data) {
            $old = $data->no_services;
            $new = filter_var($old, FILTER_SANITIZE_NUMBER_INT);
            $this->db->set('no_services', $new);
            $this->db->where('customer_id', $data->customer_id);
            $this->db->update('customer');
        };
        $servicesall = $this->db->get('services')->result();
        foreach ($servicesall as $data) {
            $old = $data->no_services;
            $new = filter_var($old, FILTER_SANITIZE_NUMBER_INT);
            $this->db->set('no_services', $new);
            $this->db->where('services_id', $data->services_id);
            $this->db->update('services');
        };
        redirect('customer');
    }
    public function fixnumber()
    {
        $customerall = $this->db->get('customer')->result();
        foreach ($customerall as $data) {
            $old = $data->no_wa;
            $cek = substr($old, 0, 1);
            if ($cek != 0) {
                $new = '0' . $old;
                $this->db->set('no_wa', $new);
                $this->db->where('customer_id', $data->customer_id);
                $this->db->update('customer');
            }
        };

        redirect('customer');
    }
    public function disablenonactive()
    {
        $customer = $this->db->get_where('customer', ['c_status' => 'Non-Aktif'])->result();
        foreach ($customer as $data) {
            isolir($data->no_services, $data->router);
            echo $data->name . ' - ' . $data->no_services;
            echo '<br>';
        };
    }
    public function setstatus($status)
    {



        $this->db->set('c_status', $status);
        $this->db->update('customer');


        redirect('customer');
    }
    public function add()
    {

        $role_id = $this->session->userdata('role_id');
        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();
        if ($role_id != 1 && $role['add_customer'] == 0) {
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->session->userdata('role_id') == 2) {
            $this->session->set_flashdata('error', 'Akses dilarang');
            redirect('member/dashboard');
        }
        $post = $this->input->post(null, TRUE);
        if (!isset($post['email'])) {
            $post['email'] = '';
        }


        // die;
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('no_ktp', 'ID Card', 'trim');
        $this->form_validation->set_rules('no_services', 'No Service', 'required|trim|is_unique[customer.no_services]');
        $this->form_validation->set_rules('no_wa', 'No Whatsapp', 'trim');
        if ($post['email'] !== '') {
            $this->form_validation->set_rules('email', 'Email', 'trim|valid_email|is_unique[customer.email]');
        }

        $this->form_validation->set_message('required', '%s Tidak boleh kosong, Silahkan isi');
        $this->form_validation->set_message('is_unique', '%s Sudah dipakai, Silahkan ganti');
        if ($this->form_validation->run() == false) {
            $data['title'] = 'Add Customer';
            $data['company'] = $this->db->get('company')->row_array();
            $data['customer'] = (object) ['coverage' => 0];
            $data['package'] = $this->db->get('package')->row_array();
            if ($this->session->userdata('role_id') == 3 && $role['coverage_operator'] == 1) {
                $operator = $this->db->get_where('cover_operator', ['operator' => $this->session->userdata('id')])->result();
                if (count($operator) == 0) {
                    $this->session->set_flashdata('error-sweet', 'Tidak ada daftar Coverage untuk akun anda');
                    redirect($_SERVER['HTTP_REFERER']);
                }
                foreach ($operator as $roww) {
                    $row[] = $roww->coverage_id;
                };

                $data['coverage'] = $this->coverage_m->getCoverage($row)->result();
            } else {
                $data['coverage'] = $this->db->get('coverage')->result();
            }
            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
            $this->template->load('backend', 'backend/customer/add_customer', $data);
        } else {
            $email = $post['email'];
            $password = isset($post['password1']) ? $post['password1'] : '';
            $cekCs = $email !== '' ? $this->db->get_where('user', ['email' => $email])->num_rows() : 0;
            if ($cekCs > 0 || $email === '' || $password === '') {
                $this->customer_m->add($post);
                if ($this->db->affected_rows() > 0) {
                    $item = $this->db->get_where('package_item', ['p_item_id' => $post['paket']])->row_array();
                    $datapaket = [
                        'item_id' => $item['p_item_id'],
                        'category_id' => $item['category_id'],
                        'no_services' => $post['no_services'],
                        'qty' => 1,
                        'disc' => 0,
                        'price' => $item['price'],
                        'total' => $item['price'],
                        'services_create' => time(),
                    ];
                    $this->db->insert('services', $datapaket);
                    if (!empty($this->input->post("createbill"))) {
                        if (!empty($this->input->post("nextmonth"))) {
                            $month = date('m', strtotime('first day of +1 month'));
                        } else {
                            $month = date('m');
                        }
                        $year = date('Y');
                        $no = 001;
                        $company = $this->db->get_where('company')->row_array();
                        $inv = date('ymd') . '' .  str_pad($no, 3, "0", STR_PAD_LEFT);

                        if ($post['ppn'] > 0) {
                            $ppn = $company['ppn'];
                        } else {
                            $ppn = 0;
                        }

                        $cekinvoice = $this->bill_m->cekInvoice($inv);
                        $getInv = $this->bill_m->getRecentInv()->row();
                        if ($cekinvoice->num_rows() > 0) {
                            if ($getInv > 0) {
                                $invoice = $getInv->invoice + 1;
                            }
                        } else {
                            $invoice = $inv;
                        }
                        if (!empty($this->input->post("proratabill"))) {
                            if ($post['countdate'] > 0) {
                                $dayprice = $item['price'] / 30;
                                $price = $dayprice * $post['countdate'];
                                $remarkinvoice = 'Prorata pemakaian ' . $post['countdate'] . ' Hari';
                            } else {
                                $dayprice = $item['price'];
                                $price = $dayprice;
                                $remarkinvoice = '';
                            }


                            $amount = $price + $price * ($ppn / 100);
                            $datainvoice = [
                                'invoice' => $invoice,
                                'month' => $month,
                                'year' => $year,
                                'i_ppn' => $ppn,
                                'amount' => $amount,
                                'code_unique' => substr(intval(rand()), 0, 3),
                                'status' => 'BELUM BAYAR',
                                'no_services' => $post['no_services'],
                                'created' => time()
                            ];
                            $this->db->insert('invoice', $datainvoice);

                            $datainvoicedetail = [
                                'invoice_id' => $invoice,
                                'item_id' => $item['p_item_id'],
                                'category_id' => $item['category_id'],
                                'price' => $price,
                                'qty' => 1,
                                'total' => $price,
                                'd_month' => $month,
                                'd_year' => $year,
                                'remark' => $remarkinvoice,
                                'd_no_services' =>  $post['no_services'],
                            ];
                            $this->db->insert('invoice_detail', $datainvoicedetail);
                        } else {
                            $amount = $item['price'] + $item['price'] * ($ppn / 100);
                            $datainvoice = [
                                'invoice' => $invoice,
                                'month' => $month,
                                'year' => $year,
                                'i_ppn' => $ppn,
                                'amount' => $amount,
                                'code_unique' => substr(intval(rand()), 0, 3),
                                'status' => 'BELUM BAYAR',
                                'no_services' => $post['no_services'],
                                'created' => time()
                            ];
                            $this->db->insert('invoice', $datainvoice);

                            $datainvoicedetail = [
                                'invoice_id' => $invoice,
                                'item_id' => $item['p_item_id'],
                                'category_id' => $item['category_id'],
                                'price' => $item['price'],
                                'qty' => 1,
                                'total' => $item['price'],
                                'd_month' => $month,
                                'd_year' => $year,
                                'd_no_services' =>  $post['no_services'],
                            ];
                            $this->db->insert('invoice_detail', $datainvoicedetail);
                        }
                        // if (!empty($this->input->post("fullbill"))) {

                        // }
                    }
                    if ($post['createnew'] == 1) {
                        $router = $this->db->get_where('router', ['id' => $post['router']])->row_array();
                        $user = $router['username'];
                        $ip = $router['ip_address'];
                        $pass = $router['password'];
                        $port = $router['port'];
                        $API = new Mikweb();
                        $API->connect($ip, $user, $pass, $port);
                        if ($post['mode_user'] == 'Hotspot') {
                            $API->comm("/ip/hotspot/user/add", array(
                                "name" => $post['user_mikrotik'],
                                "password" => $post['passwordmikrotik'],
                                "profile" => $post['profile'],
                                "disabled" => "no",
                                "comment" => $post['name'] . '-' . $post['no_services'],
                            ));
                        } elseif ($post['mode_user'] == 'PPPOE') {

                            $remoteaddress = $post['remoteaddress'];
                            $localaddress = $post['localaddress'];
                            if ($post['remoteaddress'] == "" && $post['localaddress'] == "") {
                                $API->comm("/ppp/secret/add", array(
                                    'name' => $post['user_mikrotik'],
                                    'profile' => $post['profile'],
                                    'password' => $post['passwordmikrotik'],
                                    'service' => 'pppoe',


                                ));
                            }
                            if ($post['remoteaddress'] != "" && $post['localaddress'] != "") {
                                $API->comm("/ppp/secret/add", array(
                                    'name' => $post['user_mikrotik'],
                                    'profile' => $post['profile'],
                                    'password' => $post['passwordmikrotik'],
                                    'service' => 'pppoe',

                                    'remote-address' => $remoteaddress,
                                    'local-address' => $localaddress,

                                ));
                            }
                            if ($post['remoteaddress'] != "" && $post['localaddress'] == "") {
                                $API->comm("/ppp/secret/add", array(
                                    'name' => $post['user_mikrotik'],
                                    'profile' => $post['profile'],
                                    'password' => $post['passwordmikrotik'],
                                    'service' => 'pppoe',

                                    'remote-address' => $remoteaddress,
                                ));
                            }
                            if ($post['remoteaddress'] == "" && $post['localaddress'] != "") {
                                $API->comm("/ppp/secret/add", array(
                                    'name' => $post['user_mikrotik'],
                                    'profile' => $post['profile'],
                                    'password' => $post['passwordmikrotik'],
                                    'service' => 'pppoe',

                                    'local-address' => $localaddress,
                                ));
                            }
                            if ($post['type_ip'] == 1) {
                                $API->comm("/queue/simple/add", array(
                                    'name' => $post['user_mikrotik'],
                                    'target' => $post['target'],
                                    'max-limit' => $post['uploadlimit'] . $post['upload'] . '/' . $post['downloadlimit'] . $post['download']
                                ));
                            }
                        } elseif ($post['mode_user'] == 'Static') {
                            if ($post['parent'] != "") {
                                $API->comm("/queue/simple/add", array(
                                    'name' => $post['user_mikrotik'],
                                    'target' => $post['target'],
                                    'parent' => $post['parent'],
                                    'max-limit' => $post['uploadlimit'] . $post['upload'] . '/' . $post['downloadlimit'] . $post['download']
                                ));
                            } else {

                                $API->comm("/queue/simple/add", array(
                                    'name' => $post['user_mikrotik'],
                                    'target' => $post['target'],
                                    'max-limit' => $post['uploadlimit'] . $post['upload'] . '/' . $post['downloadlimit'] . $post['download']
                                ));
                            }
                        } elseif ($post['mode_user'] == 'Standalone') {
                            # code...
                        }
                    }
                    if (!empty($post['sendwapelanggan']) && $email !== '' && $password !== '') {
                        // WA GATEWAY
                        // get database wa gateway
                        $whatsapp = $this->db->get('whatsapp')->row_array();
                        // get database other 
                        $other = $this->db->get('other')->row_array();
                        // get database company
                        $company = $this->db->get('company')->row_array();
                        $search  = array('{name}', '{noservices}', '{month}', '{year}',  '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}', '{email}', '{password}', '{phone}');
                        $replace = array($post['name'], $post['no_services'], date('m'), date('Y'), $post['due_date'], $item['price'], $company['company_name'], $company['sub_name'], base_url(), '', $email, $password, $post['no_wa']);
                        $subject = $other['add_customer'];
                        $message = str_replace($search, $replace, $subject);
                        if ($whatsapp['is_active'] == 1) {
                            $target = indo_tlp($post['no_wa']);
                            sendmsg($target, $message);
                        }
                    }
                    $this->session->set_flashdata('success', 'Data Pelanggan berhasil disimpan, silahkan isi paket pelanggan');
                }
                echo "<script>window.location='" . site_url('services/detail/' . $this->input->post('no_services')) . "'; </script>";
            } else {
                $this->customer_m->add($post);
                if ($this->db->affected_rows() > 0) {
                    $email = $this->input->post('email', true);
                    $data = [
                        'name' => htmlspecialchars($this->input->post('name', true)),
                        'email' => htmlspecialchars($email),
                        'image' => 'default.jpg',
                        'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
                        'role_id' => 2,
                        'phone' => $this->input->post('no_wa', true),
                        'address' => htmlspecialchars($this->input->post('address', true)),
                        'is_active' => 1,
                        'date_created' => time()
                    ];

                    $this->db->insert('user', $data);

                    $item = $this->db->get_where('package_item', ['p_item_id' => $post['paket']])->row_array();
                    $datapaket = [
                        'item_id' => $item['p_item_id'],
                        'category_id' => $item['category_id'],
                        'no_services' => $post['no_services'],
                        'qty' => 1,
                        'disc' => 0,
                        'price' => $item['price'],
                        'total' => $item['price'],
                        'services_create' => time(),
                    ];
                    $this->db->insert('services', $datapaket);
                    // CREATE BILL
                    // var_dump($this->input->post("fullbill"));
                    // die;
                    if (!empty($this->input->post("createbill"))) {
                        if (!empty($this->input->post("nextmonth"))) {
                            $month = date('m', strtotime('first day of +1 month'));
                        } else {
                            $month = date('m');
                        }
                        $year = date('Y');
                        $no = 001;
                        $company = $this->db->get_where('company')->row_array();
                        $inv = date('ymd') . '' .  str_pad($no, 3, "0", STR_PAD_LEFT);

                        if ($post['ppn'] > 0) {
                            $ppn = $company['ppn'];
                        } else {
                            $ppn = 0;
                        }

                        $cekinvoice = $this->bill_m->cekInvoice($inv);
                        $getInv = $this->bill_m->getRecentInv()->row();
                        if ($cekinvoice->num_rows() > 0) {
                            if ($getInv > 0) {
                                $invoice = $getInv->invoice + 1;
                            }
                        } else {
                            $invoice = $inv;
                        }
                        if (!empty($this->input->post("proratabill"))) {
                            if ($post['countdate'] > 0) {
                                $dayprice = $item['price'] / 30;
                                $price = $dayprice * $post['countdate'];
                                $remarkinvoice = 'Prorata pemakaian ' . $post['countdate'] . ' Hari';
                            } else {
                                $dayprice = $item['price'];
                                $price = $dayprice;
                                $remarkinvoice = '';
                            }


                            $amount = $price + $price * ($ppn / 100);
                            $datainvoice = [
                                'invoice' => $invoice,
                                'month' => $month,
                                'year' => $year,
                                'i_ppn' => $ppn,
                                'amount' => $amount,
                                'code_unique' => substr(intval(rand()), 0, 3),
                                'status' => 'BELUM BAYAR',
                                'no_services' => $post['no_services'],
                                'created' => time()
                            ];
                            $this->db->insert('invoice', $datainvoice);

                            $datainvoicedetail = [
                                'invoice_id' => $invoice,
                                'item_id' => $item['p_item_id'],
                                'category_id' => $item['category_id'],
                                'price' => $price,
                                'qty' => 1,
                                'total' => $price,
                                'd_month' => $month,
                                'd_year' => $year,
                                'remark' => $remarkinvoice,
                                'd_no_services' =>  $post['no_services'],
                            ];
                            $this->db->insert('invoice_detail', $datainvoicedetail);
                        } else {
                            $amount = $item['price'] + $item['price'] * ($ppn / 100);
                            $datainvoice = [
                                'invoice' => $invoice,
                                'month' => $month,
                                'year' => $year,
                                'i_ppn' => $ppn,
                                'amount' => $amount,
                                'code_unique' => substr(intval(rand()), 0, 3),
                                'status' => 'BELUM BAYAR',
                                'no_services' => $post['no_services'],
                                'created' => time()
                            ];
                            $this->db->insert('invoice', $datainvoice);

                            $datainvoicedetail = [
                                'invoice_id' => $invoice,
                                'item_id' => $item['p_item_id'],
                                'category_id' => $item['category_id'],
                                'price' => $item['price'],
                                'qty' => 1,
                                'total' => $item['price'],
                                'd_month' => $month,
                                'd_year' => $year,
                                'd_no_services' =>  $post['no_services'],
                            ];
                            $this->db->insert('invoice_detail', $datainvoicedetail);
                        }
                        // if (!empty($this->input->post("fullbill"))) {

                        // }
                    }

                    if ($post['createnew'] == 1) {
                        $router = $this->db->get_where('router', ['id' => $post['router']])->row_array();
                        $user = $router['username'];
                        $ip = $router['ip_address'];
                        $pass = $router['password'];
                        $port = $router['port'];
                        $API = new Mikweb();
                        $API->connect($ip, $user, $pass, $port);
                        if ($post['mode_user'] == 'Hotspot') {
                            $API->comm("/ip/hotspot/user/add", array(
                                "name" => $post['user_mikrotik'],
                                "password" => $post['passwordmikrotik'],
                                "profile" => $post['profile'],
                                "disabled" => "no",
                                "comment" => $post['name'] . '-' . $post['no_services'],
                            ));
                        } elseif ($post['mode_user'] == 'PPPOE') {

                            $remoteaddress = $post['remoteaddress'];
                            $localaddress = $post['localaddress'];
                            if ($post['remoteaddress'] == "" && $post['localaddress'] == "") {
                                $API->comm("/ppp/secret/add", array(
                                    'name' => $post['user_mikrotik'],
                                    'profile' => $post['profile'],
                                    'password' => $post['passwordmikrotik'],
                                    'service' => 'pppoe',


                                ));
                            }
                            if ($post['remoteaddress'] != "" && $post['localaddress'] != "") {
                                $API->comm("/ppp/secret/add", array(
                                    'name' => $post['user_mikrotik'],
                                    'profile' => $post['profile'],
                                    'password' => $post['passwordmikrotik'],
                                    'service' => 'pppoe',

                                    'remote-address' => $remoteaddress,
                                    'local-address' => $localaddress,

                                ));
                            }
                            if ($post['remoteaddress'] != "" && $post['localaddress'] == "") {
                                $API->comm("/ppp/secret/add", array(
                                    'name' => $post['user_mikrotik'],
                                    'profile' => $post['profile'],
                                    'password' => $post['passwordmikrotik'],
                                    'service' => 'pppoe',

                                    'remote-address' => $remoteaddress,
                                ));
                            }
                            if ($post['remoteaddress'] == "" && $post['localaddress'] != "") {
                                $API->comm("/ppp/secret/add", array(
                                    'name' => $post['user_mikrotik'],
                                    'profile' => $post['profile'],
                                    'password' => $post['passwordmikrotik'],
                                    'service' => 'pppoe',

                                    'local-address' => $localaddress,
                                ));
                            }
                            if ($post['type_ip'] == 1) {
                                $API->comm("/queue/simple/add", array(
                                    'name' => $post['user_mikrotik'],
                                    'target' => $post['target'],
                                    'max-limit' => $post['uploadlimit'] . $post['upload'] . '/' . $post['downloadlimit'] . $post['download']
                                ));
                            }
                        } elseif ($post['mode_user'] == 'Static') {
                            if ($post['parent'] != "") {
                                $API->comm("/queue/simple/add", array(
                                    'name' => $post['user_mikrotik'],
                                    'target' => $post['target'],
                                    'parent' => $post['parent'],
                                    'max-limit' => $post['uploadlimit'] . $post['upload'] . '/' . $post['downloadlimit'] . $post['download']
                                ));
                            } else {

                                $API->comm("/queue/simple/add", array(
                                    'name' => $post['user_mikrotik'],
                                    'target' => $post['target'],
                                    'max-limit' => $post['uploadlimit'] . $post['upload'] . '/' . $post['downloadlimit'] . $post['download']
                                ));
                            }
                        } elseif ($post['mode_user'] == 'Standalone') {
                            # code...
                        }
                    }
                    if ($this->agent->is_browser()) {
                        $agent = $this->agent->browser() . ' ' . $this->agent->version();
                    } elseif ($this->agent->is_mobile()) {
                        $agent = $this->agent->mobile();
                    }
                    $params = [
                        'datetime' => time(),
                        'category' => 'Activity',
                        'name' => $this->session->userdata('name'),
                        'role_id' => $this->session->userdata('role_id'),
                        'user_id' => $this->session->userdata('id'),
                        'remark' => 'Tambah Pelanggan' . ' ' . $post['name'] . ' ' . $post['no_services'] . ' ' . 'dari' . ' ' . $this->agent->platform() . ' ' . $this->input->ip_address() . ' ' . $agent,
                    ];
                    $this->db->insert('logs', $params);
                    if (!empty($post['sendwapelanggan']) && $email !== '' && $password !== '') {

                        // WA GATEWAY
                        // get database wa gateway

                        $whatsapp = $this->db->get('whatsapp')->row_array();
                        // get database other 
                        $other = $this->db->get('other')->row_array();
                        // get database company


                        $company = $this->db->get('company')->row_array();
                        $search  = array('{name}', '{noservices}', '{month}', '{year}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}', '{email}', '{password}');
                        $replace = array($post['name'], $post['no_services'], date('m'), date('Y'), $post['due_date'], $item['price'], $company['company_name'], $company['sub_name'], base_url(), '', $email, $password);
                        $subject = $other['add_customer'];
                        $message = str_replace($search, $replace, $subject);
                        if ($whatsapp['is_active'] == 1) {
                            $target = indo_tlp($post['no_wa']);
                            sendmsg($target, $message);
                        }
                    }
                    $this->session->set_flashdata('success-sweet', 'Data Pelanggan berhasil disimpan, <a class="btn btn-success" href="' . site_url('customer/add') . '">Tambah Pelanggan Lagi ?</a>&nbsp; <a class="btn btn-primary" href="' . site_url('customer') . '">Data Pelanggan</a>');
                    echo "<script>window.location='" . site_url('services/detail/' . $this->input->post('no_services')) . "'; </script>";
                }
            }
        }
    }

    public function edit($customer_id)
    {
        is_logged_in();
        $role_id = $this->session->userdata('role_id');
        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();
        if ($role_id != 1 && $role['edit_customer'] == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->session->userdata('role_id') == 2) {
            $this->session->set_flashdata('error', 'Akses dilarang');
            redirect('member/dashboard');
        }
        $this->form_validation->set_rules('name', 'Name', 'required|trim');

        $this->form_validation->set_rules('no_wa', 'No Whatsapp', 'required|trim');
        $this->form_validation->set_rules('no_services', 'No Services', 'required|trim|callback_no_services_check');
        $this->form_validation->set_message('required', '%s Tidak boleh kosong, Silahkan isi');
        $this->form_validation->set_message('is_unique', '%s Sudah dipakai, Silahkan ganti');
        if ($this->form_validation->run() == false) {
            $query  = $this->db->get_where('customer', ['customer_id' => $customer_id]);
            if ($query->num_rows() > 0) {
                if ($this->session->userdata('role_id') == 3 && $role['coverage_operator'] == 1) {
                    $operator = $this->db->get_where('cover_operator', ['operator' => $this->session->userdata('id')])->result();
                    if (count($operator) == 0) {
                        $this->session->set_flashdata('error-sweet', 'Tidak ada daftar Coverage untuk akun anda');
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    foreach ($operator as $roww) {
                        $row[] = $roww->coverage_id;
                    };

                    $data['coverage'] = $this->coverage_m->getCoverage($row)->result();
                } else {
                    $data['coverage'] = $this->db->get('coverage')->result();
                }
                $data['customer'] = $query->row();
                $data['title'] = 'Edit Customer';
                $data['company'] = $this->db->get('company')->row_array();
                $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
                $this->template->load('backend', 'backend/customer/edit_customer', $data);
            } else {
                echo "<script> alert ('Data tidak ditemukan');";
                echo "window.location='" . site_url('customer') . "'; </script>";
            }
        } else {
            $post = $this->input->post(null, TRUE);
            $customer = $this->db->get_where('customer', ['customer_id' => $post['customer_id']])->row();
            if (!$customer) {
                $this->session->set_flashdata('error-sweet', 'Data pelanggan tidak ditemukan');
                redirect('customer');
                return;
            }
            $oldNoServices = (string) $customer->no_services;
            $newNoServices = (string) $post['no_services'];
            $hasServicesToMove = $oldNoServices !== $newNoServices && $newNoServices !== '0';
            if ($hasServicesToMove && $oldNoServices === '0') {
                $servicesAtZero = $this->db
                    ->where('no_services', $oldNoServices)
                    ->count_all_results('services');
                $otherCustomersAtZero = $this->db
                    ->where('no_services', $oldNoServices)
                    ->where('customer_id !=', $customer->customer_id)
                    ->count_all_results('customer');
                if ($servicesAtZero > 0 && $otherCustomersAtZero > 0) {
                    $this->session->set_flashdata('error-sweet', 'Paket belum bisa dipindahkan: beberapa pelanggan masih memakai nomor layanan 0. Pastikan paket lama bisa diidentifikasi dengan nomor layanan terlebih dahulu.');
                    redirect('customer/edit/' . $customer->customer_id);
                    return;
                }
            }
            $post['email'] = $customer->email;
            $this->customer_m->edit($post);
            if ($this->db->affected_rows() > 0) {
                $logamessage = 'Edit Pelanggan' . ' ' . $post['name'] . ' ' . $post['no_services'] . ' ';
                $this->logs_m->activitylogs('Activity', $logamessage);
                $this->session->set_flashdata('success-sweet', 'Data Pelanggan berhasil diperbaharui');
            }
            if ($hasServicesToMove) {
                $this->db->set('no_services', $post['no_services']);
                $this->db->where('no_services', $customer->no_services);
                $this->db->update('services');
            }
            echo "<script>window.location='" . site_url('customer') . "'; </script>";
        }
    }

    public function detail($no_services)
    {
    }

    function email_check()
    {
        $post = $this->input->post(null, TRUE);
        $query = $this->db->query("SELECT * FROM customer WHERE email = '$post[email]' AND customer_id != '$post[customer_id]'");
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message('email_check', '%s Ini sudah dipakai, Silahkan ganti !');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function no_wa_check()
    {
        $post = $this->input->post(null, TRUE);
        $query = $this->db->query("SELECT * FROM customer WHERE no_wa = '$post[no_wa]' AND customer_id != '$post[customer_id]'");
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message('no_wa_check', '%s Ini sudah dipakai, Silahkan ganti !');
            return FALSE;
        } else {
            return TRUE;
        }
    }
    function no_services_check()
    {
        $post = $this->input->post(null, TRUE);
        $query = $this->db->query("SELECT * FROM customer WHERE no_services = '$post[no_services]' AND customer_id != '$post[customer_id]'");
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message('no_services_check', '%s Ini sudah dipakai, Silahkan ganti !');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function no_ktp_check()
    {
        $post = $this->input->post(null, TRUE);
        $query = $this->db->query("SELECT * FROM customer WHERE no_ktp = '$post[no_ktp]' AND customer_id != '$post[customer_id]'");
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message('no_ktp_check', '%s Ini sudah dipakai, Silahkan ganti !');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function delete()
    {
        $role_id = $this->session->userdata('role_id');
        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();
        if ($role_id != 1 && $role['del_customer'] == 0) {
            $this->session->set_flashdata('error', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->session->userdata('role_id') == 2) {
            $this->session->set_flashdata('error', 'Akses dilarang');
            redirect('member/dashboard');
        }
        $post = $this->input->post(null, TRUE);
        $customer_id = $this->input->post('customer_id');
        $customer = $this->db->get_where('customer', ['customer_id' => $customer_id])->row_array();

        if (empty($customer)) {
            $this->session->set_flashdata('error', 'Pelanggan tidak ditemukan');
            redirect('customer');
        }

        $no_services = $customer['no_services'];
        $customername = $customer['name'];

        $cekConfir = $this->db->get_where('confirm_payment', ['no_services' => $no_services])->row_array();
        if ($cekConfir > 0) {
            $this->session->set_flashdata('error', 'Pelanggan tidak bisa dihapus dikarenakan masih ada di konfirmasi pembayaran');
            redirect('customer');
        }
        $this->customer_m->delete($customer_id);
        if ($this->db->affected_rows() > 0) {
            $company = $this->db->get('company')->row_array();
            $cekInvoice = $this->bill_m->getCekInvoice($no_services)->result();
            foreach ($cekInvoice as $data) {
                // HAPUS SEMUA PEMASUKAN
                if ($post['delincome'] == 1) {
                    // $this->db->where_in('invoice_id', $data->invoice);
                    $this->db->where_in('no_services', $no_services);

                    $this->db->delete('income');
                }
                // HAPUS SEMUA DETAIL TAGIHAN
                $this->bill_m->deletedetailselected($data->month, $data->year, $data->no_services);
                $this->bill_m->deleteDetailInvoice($data->invoice);
                // HAPUS QRCODE
                $target_file = './assets/images/qrcode/' . $data->invoice . '.png';
                if (is_file($target_file)) {
                    unlink($target_file);
                }
                // HAPUS SEMUA INVOICE TAGIHAN
                // $this->db->where_in('invoice', $data->invoice);
                $this->db->where_in('no_services', $no_services);
                $this->db->delete('invoice');
            }
            // HAPUS PAKET JIKA NO LAYANAN 0
            if ($customer['no_services'] == 0) {
                $this->db->where('email', $customer['email']);
                $this->db->delete('services');
            } else {
                // HAPUS SEMUA PAKET
                $this->db->where_in('no_services', $no_services);
                $this->db->delete('services');
            }

            // HAPUS PROFILE
            $user = $this->db->get_where('user', ['email' => $customer['email']])->row_array();
            if (!empty($user['image'])) {
                $target_file = './assets/images/profile/' . $user['image'];
                if (is_file($target_file)) {
                    unlink($target_file);
                }
            }
            // HAPUS EMAIL PENGGUNA
            if ($customer['email'] != $this->session->userdata('email')) {
                $this->db->where('email', $customer['email']);
                $this->db->delete('user');
            }

            // HAPUS KTP
            if (!empty($customer['ktp'])) {
                $target_file = './assets/images/ktp/' . $customer['ktp'];
                if (is_file($target_file)) {
                    unlink($target_file);
                }
            }



            if ($this->agent->is_browser()) {
                $agent = $this->agent->browser() . ' ' . $this->agent->version();
            } elseif ($this->agent->is_mobile()) {
                $agent = $this->agent->mobile();
            }
            $params = [
                'datetime' => time(),
                'category' => 'Activity',
                'name' => $this->session->userdata('name'),
                'role_id' => $this->session->userdata('role_id'),
                'user_id' => $this->session->userdata('id'),
                'remark' => 'Hapus Pelanggan' . ' ' . $customer['name'] . ' ' . $customer['no_services'] . ' ' . 'dari' . ' ' . $this->agent->platform() . ' ' . $this->input->ip_address() . ' ' . $agent,
            ];
            $this->db->insert('logs', $params);

            $this->session->set_flashdata('success', 'Data berhasil dihapus');
        }

        redirect('customer');
    }
    public function setcodeunique()
    {
        $this->db->set('codeunique', 1);
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function unsetcodeunique()
    {
        $this->db->set('codeunique', 0);
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function setppn()
    {
        $this->db->set('ppn', 1);
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function unsetppn()
    {
        $this->db->set('ppn', 0);
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }

    public function setduedate()
    {
        $company = $this->db->get('company')->row_array();
        $this->db->set('due_date', $company['due_date']);
        $this->db->where('due_date', 0);
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function setisolir()
    {

        $this->db->set('auto_isolir', 1);
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function unsetisolir()
    {

        $this->db->set('auto_isolir', 0);
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function setnextmonth()
    {

        $this->db->set('month_due_date', 1);
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function setperiodmonth()
    {

        $this->db->set('month_due_date', 0);
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function setmaxisolir($day)
    {

        $this->db->set('max_due_isolir', $day);
        // $this->db->where('due_date', 0);
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function setmode($mode)
    {

        $this->db->set('mode_user', $mode);

        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function unsetuserprofile()
    {

        $this->db->set('user_profile', '');
        $this->db->where('user_profile', 'test');
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function userprofile()
    {

        $this->db->set('user_profile', '15 Mbps');
        $this->db->like('user_profile', '{"profile":"15 Mbps"}');
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function unsetconnection()
    {
        $company = $this->db->get('company')->row_array();
        $this->db->set('connection', 0);
        // $this->db->where('due_date', 0);
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    // public function setnowa()
    // {
    //     $company = $this->db->get('company')->row_array();
    //     $this->db->set('no_wa', '089699642598');
    //     // $this->db->where('due_date', 0);
    //     $this->db->update('customer');
    //     if ($this->db->affected_rows() > 0) {
    //         $this->session->set_flashdata('success', 'Berhasil');
    //     }
    //     redirect('customer');
    // }
    public function setaction()
    {

        $this->db->set('action', 1);
        // $this->db->where('due_date', 0);
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function setidmikrotik($id)
    {

        $this->db->set('router', $id);
        $this->db->set('mode_user', 'PPPOE');
        // $this->db->where('due_date', 0);
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function setusermikrotik()
    {
        $customer = $this->db->get('customer')->result();
        foreach ($customer as $data) {
            $this->db->set('user_mikrotik', $data->name);
            $this->db->where('customer_id', $data->customer_id);
            $this->db->update('customer');
        }

        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function setdue($date)
    {

        $this->db->set('due_date', $date);

        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function delempty()
    {

        $this->db->where('no_services', '');
        $this->db->where('email', '');
        $this->db->delete('customer');
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Berhasil');
        }
        redirect('customer');
    }
    public function import()
    {
        $data = array(); // Buat variabel $data sebagai array

        if (isset($_POST['preview'])) { // Jika user menekan tombol Preview pada form
            // lakukan upload file dengan memanggil function upload yang ada di SiswaModel.php
            $upload = $this->customer_m->upload_file('import_data');

            if ($upload['result'] == "success") { // Jika proses upload sukses
                // Load plugin PHPExcel nya
                include APPPATH . 'third_party/PHPExcel/PHPExcel.php';

                $excelreader = new PHPExcel_Reader_Excel2007();
                $loadexcel = $excelreader->load('assets/import_data.xlsx'); // Load file yang tadi diupload ke folder excel
                $sheet = $loadexcel->getActiveSheet()->toArray(null, true, true, true);

                // Masukan variabel $sheet ke dalam array data yang nantinya akan di kirim ke file form.php
                // Variabel $sheet tersebut berisi data-data yang sudah diinput di dalam excel yang sudha di upload sebelumnya
                $data['sheet'] = $sheet;
            } else { // Jika proses upload gagal
                $data['upload_error'] = $upload['error']; // Ambil pesan error uploadnya untuk dikirim ke file form dan ditampilkan
                $this->session->set_flashdata('error-sweet', $upload['error']);
                redirect('customer/import');
            }
        }
        $data['title'] = 'Customer';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        // $this->load->view('form', $data);
        $this->template->load('backend', 'backend/excel/data', $data);
    }

    public function importtodatabase()
    {
        // Load plugin PHPExcel nya
        include APPPATH . 'third_party/PHPExcel/PHPExcel.php';

        $excelreader = new PHPExcel_Reader_Excel2007();
        $loadexcel = $excelreader->load('assets/import_data.xlsx'); // Load file yang telah diupload ke folder excel
        $sheet = $loadexcel->getActiveSheet()->toArray(null, true, true, true);

        // Buat sebuah variabel array untuk menampung array data yg akan kita insert ke database
        $data = array();

        $numrow = 1;
        foreach ($sheet as $row) {
            // Cek $numrow apakah lebih dari 1
            // Artinya karena baris pertama adalah nama-nama kolom
            // Jadi dilewat saja, tidak usah diimport
            if ($numrow > 1) {
                // Kita push (add) array data ke variabel data
                array_push($data, array(
                    'no_services' => trim($row['A']), // Insert data nis dari kolom A di excel
                    'name' => trim($row['B']), // Insert data nama dari kolom B di excel
                    'email' => trim($row['C']), // Insert data jenis kelamin dari kolom C di excel
                    'c_status' => trim($row['D']), // Insert data alamat dari kolom D di excel
                    'no_wa' => trim($row['E']), // Insert data alamat dari kolom D di excel
                    'address' => $row['F'], // Insert data alamat dari kolom D di excel
                    'no_ktp' => trim($row['G']), // Insert data alamat dari kolom D di excel
                    'ppn' => trim($row['H']),
                    'due_date' => trim($row['I']), // Insert data alamat dari kolom D di excel
                    'router' => trim($row['K']), // Insert data alamat dari kolom D di excel
                    'mode_user' => trim($row['L']), // Insert data alamat dari kolom D di excel
                    'user_mikrotik' => trim($row['M']), // Insert data alamat dari kolom D di excel
                    'user_profile' => trim($row['N']), // Insert data alamat dari kolom D di excel
                    'auto_isolir' => trim($row['O']), // Insert data alamat dari kolom D di excel
                    'coverage' => trim($row['P']), // Insert data alamat dari kolom D di excel
                ));
            }
            $item = $this->db->get_where('package_item', ['p_item_id' => trim($row['J'])])->row_array();
            $datapaket = [
                'item_id' => $item['p_item_id'],
                'category_id' => $item['category_id'],
                'no_services' => trim($row['A']),
                'qty' => 1,
                'disc' => 0,
                'price' => $item['price'],
                'total' => $item['price'],
                'services_create' => time(),
            ];
            $this->db->insert('services', $datapaket);
            $numrow++; // Tambah 1 setiap kali looping

        }
        $this->db->where('no_services', '');
        $this->db->where('email', '');
        $this->db->delete('customer');
        // Panggil fungsi insert_multiple yg telah kita buat sebelumnya di model
        $this->customer_m->importfromexcel($data);
        $target_file = './assets/import_data.xlsx';
        unlink($target_file);
        redirect("customer"); // Redirect ke halaman awal (ke controller siswa fungsi index)
    }

    // SERVER SIDE
    private function getCustomerIpMap($customers)
    {
        $ipByCustomer = [];
        $customersByRouter = [];

        foreach ($customers as $customer) {
            $ipByCustomer[$customer->customer_id] = '-';
            if (!empty($customer->router) && !empty($customer->user_mikrotik)) {
                $customersByRouter[$customer->router][] = $customer;
            }
        }

        foreach ($customersByRouter as $routerId => $routerCustomers) {
            $router = $this->db->get_where('router', ['id' => $routerId])->row_array();
            if (empty($router)) {
                foreach ($routerCustomers as $customer) {
                    $ipByCustomer[$customer->customer_id] = 'Router tidak ditemukan';
                }
                continue;
            }

            $API = new Mikweb();
            $API->attempts = 1;
            $API->timeout = 1;
            $API->delay = 0;
            if (!$API->connect($router['ip_address'], $router['username'], $router['password'], $router['port'])) {
                foreach ($routerCustomers as $customer) {
                    $ipByCustomer[$customer->customer_id] = 'Router tidak terhubung';
                }
                $API->disconnect();
                continue;
            }

            try {
                $modes = [];
                foreach ($routerCustomers as $customer) {
                    $modes[$customer->mode_user] = true;
                }

                $pppoeActiveByUser = [];
                $pppoeSecretByUser = [];
                $hotspotActiveByUser = [];
                $staticQueueByName = [];

                if (isset($modes['PPPOE'])) {
                    $pppoeActives = $API->comm('/ppp/active/print', ['?service' => 'pppoe']);
                    foreach ((array) $pppoeActives as $active) {
                        if (!empty($active['name']) && !empty($active['address'])) {
                            $pppoeActiveByUser[$active['name']] = $active['address'];
                        }
                    }

                    $pppoeSecrets = $API->comm('/ppp/secret/print', ['?service' => 'pppoe']);
                    foreach ((array) $pppoeSecrets as $secret) {
                        if (!empty($secret['name']) && !empty($secret['remote-address'])) {
                            $pppoeSecretByUser[$secret['name']] = $secret['remote-address'];
                        }
                    }
                }

                if (isset($modes['Hotspot'])) {
                    $hotspotActives = $API->comm('/ip/hotspot/active/print');
                    foreach ((array) $hotspotActives as $active) {
                        if (!empty($active['user']) && !empty($active['address'])) {
                            $hotspotActiveByUser[$active['user']] = $active['address'];
                        }
                    }
                }

                if (isset($modes['Static'])) {
                    $queues = $API->comm('/queue/simple/print');
                    foreach ((array) $queues as $queue) {
                        if (!empty($queue['name']) && !empty($queue['target'])) {
                            $staticQueueByName[$queue['name']] = trim(explode('/', $queue['target'], 2)[0]);
                        }
                    }
                }

                foreach ($routerCustomers as $customer) {
                    $username = $customer->user_mikrotik;
                    if ($customer->mode_user === 'PPPOE') {
                        $ip = isset($pppoeActiveByUser[$username])
                            ? $pppoeActiveByUser[$username]
                            : (isset($pppoeSecretByUser[$username]) ? $pppoeSecretByUser[$username] : '-');
                    } elseif ($customer->mode_user === 'Hotspot') {
                        $ip = isset($hotspotActiveByUser[$username]) ? $hotspotActiveByUser[$username] : '-';
                    } elseif ($customer->mode_user === 'Static') {
                        $ip = isset($staticQueueByName[$username]) ? $staticQueueByName[$username] : '-';
                    } else {
                        $ip = '-';
                    }

                    $ipByCustomer[$customer->customer_id] = html_escape($ip);
                }
            } catch (RuntimeException $exception) {
                log_message('error', 'Failed to retrieve customer IPs from router ' . $routerId . ': ' . $exception->getMessage());
                foreach ($routerCustomers as $customer) {
                    $ipByCustomer[$customer->customer_id] = 'Gagal mengambil IP';
                }
            } finally {
                $API->disconnect();
            }
        }

        return $ipByCustomer;
    }

    private function getCustomerMonthlyAmount($customer)
    {
        $this->db->from('services');
        if (!empty($customer->no_services) && (int) $customer->no_services !== 0) {
            $this->db->where('no_services', $customer->no_services);
        } elseif (!empty($customer->email)) {
            $this->db->where('email', $customer->email);
        } else {
            return (int) ($customer->cust_amount ?? 0);
        }
        $services = $this->db->get()->result();

        $subtotal = 0;
        foreach ($services as $service) {
            $subtotal += (int) $service->total;
        }

        if ($subtotal == 0 && !empty($customer->cust_amount)) {
            return (int) $customer->cust_amount;
        }

        return $subtotal;
    }

    public function getAllCustomer()
    {
        $result = $this->customer_m->getDataTable();
        $ipByCustomer = $this->getCustomerIpMap($result);
        $company = $this->db->get('company')->row_array();
        $data = [];
        $no = $_POST['start'];
        foreach ($result as $result) {
            if ($result->no_services != 0) {
                $query = "SELECT * FROM `services` WHERE `services`.`no_services` = $result->no_services";
            } else {
                $query = "SELECT * FROM `services` WHERE `services`.`email` = '$result->email'";
            }
            $querying = $this->db->query($query)->result();
            $subtotal = 0;
            foreach ($querying as  $dataa)
                $subtotal += $dataa->total;
            $row = array();
            $row[] = ++$no;
            $row[] = '<input type=' . 'checkbox' . ' class=' . 'check-item' . ' id="ceklis" name=' . 'customer_id[]' . ' value=' . $result->customer_id . '>';
            $row[] = $result->name;
            $row[] = $result->no_services;

            if ($result->phonecode == 0) {
                $phone = $company['phonecode'] . '' . $result->no_wa;
            } else {
                $phone =  $result->phonecode . '' . $result->no_wa;
            }
            $row[] = indo_tlp($result->no_wa);
            $row[] = $result->c_status;
            $row[] = $result->auto_isolir == 1 ? 'Aktif' : 'Non-Aktif';
            $row[] = html_escape($result->cust_description);
            $row[] = $ipByCustomer[$result->customer_id];
            if ($this->session->userdata('role_id') == 1) {
                $row[] = indo_currency($subtotal);
            }
            $row[] = $result->address;
            $rt = $this->db->get_where('router', ['id' => 1])->row_array();
            if ($rt['is_active'] == 1) {
                $row[] =
                    '<a href="' . site_url('services/detail/') . $result->no_services . '"    title="Detail Paket" style="font-size: smaller"><i class="fa fa-eye" style="font-size:25px; color:gray"></i></a> 
                    <a href="' . site_url('customer/print/') . $result->no_services . '"    title="Cetak" target="blank" style="font-size: smaller"><i class="fa fa-print" style="font-size:25px; color:brown"></i></a> 
<a href="' . site_url('customer/edit/') . $result->customer_id . '" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a> 
<a href="' . site_url('mikrotik/client/') . $result->no_services . '" title="Koneksi"><i class="fa fa-random" style="font-size:25px;color:green"></i></a>                     
                <a href="#" id="delete" data-customer_id="' . $result->customer_id . '" data-no_services="' . $result->no_services . ' " data-name=' . $result->name . ' data-toggle="modal" data-target="#DeleteModal"   title="Delete" ><i class="fa fa-trash" style="font-size:25px; color:red"></i></a> 
                ';
            } else {
                $row[] = '<a href="' . site_url('services/detail/') . $result->no_services . '"    title="Detail Paket" style="font-size: smaller"><i class="fa fa-eye" style="font-size:25px; color:gray"></i></a> 
                <a href="' . site_url('customer/edit/') . $result->customer_id . '" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a> 
                <a href="#" id="delete" data-customer_id="' . $result->customer_id . '" data-no_services="' . $result->no_services . ' " data-name=' . $result->name . ' data-toggle="modal" data-target="#DeleteModal"   title="Delete" ><i class="fa fa-trash" style="font-size:25px; color:red"></i></a> 
                ';
            }


            $data[] = $row;
        }
        $role = $this->_role();
        $cover = $this->_coverage();

        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $coverage = $this->customer_m->getCustomerAll($cover)->num_rows();
        } else {
            $coverage =  $this->customer_m->getCustomerAll()->num_rows();
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $coverage,
            "recordsFiltered" => $this->customer_m->count_filtered_data(),
            "data" => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
    public function getActiveCustomer()
    {
        $result = $this->customer_m->getActive();
        $ipByCustomer = $this->getCustomerIpMap($result);
        $data = [];
        $company = $this->db->get('company')->row_array();
        $no = $_POST['start'];
        foreach ($result as $result) {
            if ($result->no_services != 0) {
                $query = "SELECT * FROM `services` WHERE `services`.`no_services` = $result->no_services";
            } else {
                $query = "SELECT * FROM `services` WHERE `services`.`email` = '$result->email'";
            }
            $querying = $this->db->query($query)->result();
            $subtotal = 0;
            foreach ($querying as  $dataa)
                $subtotal += $dataa->total;
            $row = array();
            $row[] = ++$no;
            $row[] = '<input type=' . 'checkbox' . ' class=' . 'check-item' . ' id="ceklis" name=' . 'customer_id[]' . ' value=' . $result->customer_id . '>';
            $row[] = $result->name;
            $row[] = $result->no_services;

            if ($result->phonecode == 0) {
                $phone = $company['phonecode'] . '' . $result->no_wa;
            } else {
                $phone =  $result->phonecode . '' . $result->no_wa;
            }
            $row[] = indo_tlp($result->no_wa);
            $row[] = $result->c_status;
            $row[] = $result->auto_isolir == 1 ? 'Aktif' : 'Non-Aktif';
            $row[] = html_escape($result->cust_description);
            $row[] = $ipByCustomer[$result->customer_id];
            if ($this->session->userdata('role_id') == 1) {
                $row[] = indo_currency($subtotal);
            }
            $row[] = $result->address;
            $rt = $this->db->get_where('router', ['id' => 1])->row_array();
            if ($rt['is_active'] == 1) {
                $row[] = '<a href="' . site_url('services/detail/') . $result->no_services . '"    title="Detail Paket" style="font-size: smaller"><i class="fa fa-eye" style="font-size:25px; color:gray"></i></a> 
<a href="' . site_url('customer/edit/') . $result->customer_id . '" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a> 
<a href="' . site_url('mikrotik/client/') . $result->no_services . '" title="Koneksi"><i class="fa fa-random" style="font-size:25px;color:green"></i></a>                     
                <a href="#" id="delete" data-customer_id="' . $result->customer_id . '" data-no_services="' . $result->no_services . ' " data-name=' . $result->name . ' data-toggle="modal" data-target="#DeleteModal"   title="Delete" ><i class="fa fa-trash" style="font-size:25px; color:red"></i></a> 
                ';
            } else {
                $row[] = '<a href="' . site_url('services/detail/') . $result->no_services . '"    title="Detail Paket" style="font-size: smaller"><i class="fa fa-eye" style="font-size:25px; color:gray"></i></a> 
                <a href="' . site_url('customer/edit/') . $result->customer_id . '" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a> 
                <a href="#" id="delete" data-customer_id="' . $result->customer_id . '" data-no_services="' . $result->no_services . ' " data-name=' . $result->name . ' data-toggle="modal" data-target="#DeleteModal"   title="Delete" ><i class="fa fa-trash" style="font-size:25px; color:red"></i></a> 
                ';
            }


            $data[] = $row;
        }
        $role = $this->_role();
        $cover = $this->_coverage();

        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $coverage = $this->customer_m->getCustomerActive($cover)->num_rows();
        } else {
            $coverage =  $this->customer_m->getCustomerActive()->num_rows();
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $coverage,
            "recordsFiltered" => $this->customer_m->count_filtered_data_active(),
            "data" => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
    public function getNonActiveCustomer()
    {
        $result = $this->customer_m->getNonActive();
        $ipByCustomer = $this->getCustomerIpMap($result);
        $data = [];
        $no = $_POST['start'];
        foreach ($result as $result) {
            $subtotal = $this->getCustomerMonthlyAmount($result);
            $row = array();
            $row[] = ++$no;
            $row[] = '<input type=' . 'checkbox' . ' class=' . 'check-item' . ' id="ceklis" name=' . 'customer_id[]' . ' value=' . $result->customer_id . '>';
            $row[] = $result->name;
            $row[] = $result->no_services;
            $company = $this->db->get('company')->row_array();
            if ($result->phonecode == 0) {
                $phone = $company['phonecode'] . '' . $result->no_wa;
            } else {
                $phone =  $result->phonecode . '' . $result->no_wa;
            }
            $row[] = $phone;
            $row[] = $result->c_status;
            $row[] = $result->auto_isolir == 1 ? 'Aktif' : 'Non-Aktif';
            $row[] = html_escape($result->cust_description);
            $row[] = $ipByCustomer[$result->customer_id];

            if ($this->session->userdata('role_id') == 1) {

                $row[] = indo_currency($subtotal);
            }
            $row[] = $result->address;

            $rt = $this->db->get_where('router', ['id' => 1])->row_array();
            if ($rt['is_active'] == 1) {
                $row[] = '<a href="' . site_url('services/detail/') . $result->no_services . '"    title="Detail Paket" style="font-size: smaller"><i class="fa fa-eye" style="font-size:25px; color:gray"></i></a> 
<a href="' . site_url('customer/edit/') . $result->customer_id . '" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a> 
<a href="' . site_url('mikrotik/client/') . $result->no_services . '" title="Koneksi"><i class="fa fa-random" style="font-size:25px;color:green"></i></a>                     
                <a href="#" id="delete" data-customer_id="' . $result->customer_id . '" data-no_services="' . $result->no_services . ' " data-name=' . $result->name . ' data-toggle="modal" data-target="#DeleteModal"   title="Delete" ><i class="fa fa-trash" style="font-size:25px; color:red"></i></a> 
                ';
            } else {
                $row[] = '<a href="' . site_url('services/detail/') . $result->no_services . '"    title="Detail Paket" style="font-size: smaller"><i class="fa fa-eye" style="font-size:25px; color:gray"></i></a> 
                <a href="' . site_url('customer/edit/') . $result->customer_id . '" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a> 
                <a href="#" id="delete" data-customer_id="' . $result->customer_id . '" data-no_services="' . $result->no_services . ' " data-name=' . $result->name . ' data-toggle="modal" data-target="#DeleteModal"   title="Delete" ><i class="fa fa-trash" style="font-size:25px; color:red"></i></a> 
                ';
            }

            $data[] = $row;
        }
        $role = $this->_role();
        $cover = $this->_coverage();

        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $coverage = $this->customer_m->getCustomerNonactive($cover)->num_rows();
        } else {
            $coverage =  $this->customer_m->getCustomerNonactive()->num_rows();
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $coverage,
            "recordsFiltered" => $this->customer_m->count_filtered_data_nonactive(),
            "data" => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
    public function getCustomerfree()
    {
        $result = $this->customer_m->getfree();
        $ipByCustomer = $this->getCustomerIpMap($result);
        $data = [];
        $company = $this->db->get('company')->row_array();
        $no = $_POST['start'];
        foreach ($result as $result) {
            $subtotal = $this->getCustomerMonthlyAmount($result);
            $row = array();
            $row[] = ++$no;
            $row[] = '<input type=' . 'checkbox' . ' class=' . 'check-item' . ' id="ceklis" name=' . 'customer_id[]' . ' value=' . $result->customer_id . '>';
            $row[] = $result->name;
            $row[] = $result->no_services;

            if ($result->phonecode == 0) {
                $phone = $company['phonecode'] . '' . $result->no_wa;
            } else {
                $phone =  $result->phonecode . '' . $result->no_wa;
            }
            $row[] = indo_tlp($result->no_wa);
            $row[] = $result->c_status;
            $row[] = $result->auto_isolir == 1 ? 'Aktif' : 'Non-Aktif';
            $row[] = html_escape($result->cust_description);
            $row[] = $ipByCustomer[$result->customer_id];

            if ($this->session->userdata('role_id') == 1) {
                $row[] = indo_currency($subtotal);
            }
            $row[] = $result->address;
            $rt = $this->db->get_where('router', ['id' => 1])->row_array();
            if ($rt['is_active'] == 1) {
                $row[] = '<a href="' . site_url('services/detail/') . $result->no_services . '"    title="Detail Paket" style="font-size: smaller"><i class="fa fa-eye" style="font-size:25px; color:gray"></i></a> 
<a href="' . site_url('customer/edit/') . $result->customer_id . '" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a> 
<a href="' . site_url('mikrotik/client/') . $result->no_services . '" title="Koneksi"><i class="fa fa-random" style="font-size:25px;color:green"></i></a>                     
                <a href="#" id="delete" data-customer_id="' . $result->customer_id . '" data-no_services="' . $result->no_services . ' " data-name=' . $result->name . ' data-toggle="modal" data-target="#DeleteModal"   title="Delete" ><i class="fa fa-trash" style="font-size:25px; color:red"></i></a> 
                ';
            } else {
                $row[] = '<a href="' . site_url('services/detail/') . $result->no_services . '"    title="Detail Paket" style="font-size: smaller"><i class="fa fa-eye" style="font-size:25px; color:gray"></i></a> 
                <a href="' . site_url('customer/edit/') . $result->customer_id . '" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a> 
                <a href="#" id="delete" data-customer_id="' . $result->customer_id . '" data-no_services="' . $result->no_services . ' " data-name=' . $result->name . ' data-toggle="modal" data-target="#DeleteModal"   title="Delete" ><i class="fa fa-trash" style="font-size:25px; color:red"></i></a> 
                ';
            }
            $data[] = $row;
        }
        $role = $this->_role();
        $cover = $this->_coverage();

        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $coverage = $this->customer_m->getCustomerFree($cover)->num_rows();
        } else {
            $coverage =  $this->customer_m->getCustomerFree()->num_rows();
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $coverage,
            "recordsFiltered" => $this->customer_m->count_filtered_data_free(),
            "data" => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
    public function getCustomerisolir()
    {
        $result = $this->customer_m->getisolir();
        $ipByCustomer = $this->getCustomerIpMap($result);
        $data = [];
        $company = $this->db->get('company')->row_array();
        $no = $_POST['start'];
        foreach ($result as $result) {
            $subtotal = $this->getCustomerMonthlyAmount($result);
            $row = array();
            $row[] = ++$no;
            $row[] = '<input type=' . 'checkbox' . ' class=' . 'check-item' . ' id="ceklis" name=' . 'customer_id[]' . ' value=' . $result->customer_id . '>';
            $row[] = $result->name;
            $row[] = $result->no_services;

            if ($result->phonecode == 0) {
                $phone = $company['phonecode'] . '' . $result->no_wa;
            } else {
                $phone =  $result->phonecode . '' . $result->no_wa;
            }
            $row[] = indo_tlp($result->no_wa);
            $row[] = $result->c_status;
            $row[] = $result->auto_isolir == 1 ? 'Aktif' : 'Non-Aktif';
            $row[] = html_escape($result->cust_description);
            $row[] = $ipByCustomer[$result->customer_id];

            if ($this->session->userdata('role_id') == 1) {
                $row[] = indo_currency($subtotal);
            }
            $row[] = $result->address;
            $rt = $this->db->get_where('router', ['id' => 1])->row_array();
            if ($rt['is_active'] == 1) {
                $row[] = '<a href="' . site_url('services/detail/') . $result->no_services . '"    title="Detail Paket" style="font-size: smaller"><i class="fa fa-eye" style="font-size:25px; color:gray"></i></a> 
<a href="' . site_url('customer/edit/') . $result->customer_id . '" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a> 
<a href="' . site_url('mikrotik/client/') . $result->no_services . '" title="Koneksi"><i class="fa fa-random" style="font-size:25px;color:green"></i></a>                     
                <a href="#" id="delete" data-customer_id="' . $result->customer_id . '" data-no_services="' . $result->no_services . ' " data-name=' . $result->name . ' data-toggle="modal" data-target="#DeleteModal"   title="Delete" ><i class="fa fa-trash" style="font-size:25px; color:red"></i></a> 
                ';
            } else {
                $row[] = '<a href="' . site_url('services/detail/') . $result->no_services . '"    title="Detail Paket" style="font-size: smaller"><i class="fa fa-eye" style="font-size:25px; color:gray"></i></a> 
                <a href="' . site_url('customer/edit/') . $result->customer_id . '" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a> 
                <a href="#" id="delete" data-customer_id="' . $result->customer_id . '" data-no_services="' . $result->no_services . ' " data-name=' . $result->name . ' data-toggle="modal" data-target="#DeleteModal"   title="Delete" ><i class="fa fa-trash" style="font-size:25px; color:red"></i></a> 
                ';
            }



            $data[] = $row;
        }
        $role = $this->_role();
        $cover = $this->_coverage();

        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $coverage = $this->customer_m->getCustomerIso($cover)->num_rows();
        } else {
            $coverage =  $this->customer_m->getCustomerIso()->num_rows();
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $coverage,
            "recordsFiltered" => $this->customer_m->count_filtered_data_isolir(),
            "data" => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
    public function getWaitCustomer()
    {
        $result = $this->customer_m->getWait();
        $ipByCustomer = $this->getCustomerIpMap($result);
        $data = [];
        $no = $_POST['start'];
        foreach ($result as $result) {
            if ($result->no_services != 0) {
                $query = "SELECT * FROM `services` WHERE `services`.`no_services` = $result->no_services";
            } else {
                $query = "SELECT * FROM `services` WHERE `services`.`email` = '$result->email'";
            }
            $querying = $this->db->query($query)->result();
            $subtotal = 0;
            foreach ($querying as  $dataa)
                $subtotal +=  $dataa->total;
            $row = array();
            $row[] = ++$no;
            $row[] = '<input type=' . 'checkbox' . ' class=' . 'check-item' . ' id="ceklis" name=' . 'customer_id[]' . ' value=' . $result->customer_id . '>';
            $row[] = $result->name;
            $row[] = $result->no_services;

            $company = $this->db->get('company')->row_array();
            if ($result->phonecode == 0) {
                $phone = $company['phonecode'] . '' . $result->no_wa;
            } else {
                $phone =  $result->phonecode . '' . $result->no_wa;
            }
            $row[] = indo_tlp($result->no_wa);
            $row[] = $result->c_status;
            $row[] = $result->auto_isolir == 1 ? 'Aktif' : 'Non-Aktif';
            $row[] = html_escape($result->cust_description);
            $row[] = $ipByCustomer[$result->customer_id];
            if ($this->session->userdata('role_id') == 1) {

                $row[] = indo_currency($subtotal);
            }
            $row[] = $result->address;

            $row[] = ' <a href="' . site_url('services/detail/') . $result->no_services . '"    title="Detail Paket" style="font-size: smaller"><i class="fa fa-eye" style="font-size:25px; color:green"></i></a> 
                   <a href="' . site_url('customer/edit/') . $result->customer_id . '" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a> 
                   <a href="#" id="delete" data-customer_id="' . $result->customer_id . '" data-no_services="' . $result->no_services . ' " data-name=' . $result->name . ' data-toggle="modal" data-target="#DeleteModal"   title="Delete" ><i class="fa fa-trash" style="font-size:25px; color:red"></i></a> 
                   ';

            $data[] = $row;
        }
        $role = $this->_role();
        $cover = $this->_coverage();

        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $coverage = $this->customer_m->getCustomerWait($cover)->num_rows();
        } else {
            $coverage =  $this->customer_m->getCustomerWait()->num_rows();
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $coverage,
            "recordsFiltered" => $this->customer_m->count_filtered_data_waiting(),
            "data" => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }

    // MAPS
    public function maps()
    {
        $data['title'] = 'Maps';
        $data['company'] = $this->db->get('company')->row_array();
        $data['tes'] = $this->customer_m->getMaps()->result();
        $data['customer'] = $this->customer_m->unmaps()->result();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('backend', 'backend/customer/maps', $data);
        // $this->load->view('backend/customer/maps', $data);
    }
    public function getmaps()
    {
        $maps = $this->customer_m->getmaps()->result();
        echo json_encode($maps);
    }




    // Sinkron profile
    public function sinkron($id)
    {
        $customer = $this->db->get_where('customer', ['customer_id' => $id])->row_array();
        $router = $this->db->get_where('router', ['id' => $customer['router']])->row_array();
        $ip = $router['ip_address'];
        $user = $router['username'];
        $pass = $router['password'];
        $port = $router['port'];
        $API = new Mikweb();
        $API->connect($ip, $user, $pass, $port);
        $usermikrotik = $customer['user_mikrotik'];
        $resource = $API->comm("/system/resource/print");
        if ($resource['0']['uptime'] != null) {
            if ($customer['mode_user'] == 'PPPOE') {
                $getuser = $API->comm("/ppp/secret/print", array('?service' => 'pppoe', '?name' => $usermikrotik));

                if (count($getuser) > 0) {
                    $this->db->set('user_profile', $getuser[0]['profile']);
                    $this->db->where('customer_id', $id);
                    $this->db->update('customer');
                    if ($this->db->affected_rows() > 0) {
                        $this->session->set_flashdata('success-sweet', 'User Profile sudah disinkronkan');
                    }
                } else {
                    $this->session->set_flashdata('error-sweet', 'User ' . $usermikrotik . ' Tidak terdaftar di secret PPP');
                }
            }
            if ($customer['mode_user'] == 'Hotspot') {
                $getuser = $API->comm("/ip/hotspot/user/print", array('?name' => $usermikrotik));
                if (count($getuser) > 0) {
                    $this->db->set('user_profile', $getuser[0]['profile']);
                    $this->db->where('customer_id', $id);
                    $this->db->update('customer');
                    if ($this->db->affected_rows() > 0) {
                        $this->session->set_flashdata('success-sweet', 'User Profile sudah disinkronkan');
                    }
                } else {
                    $this->session->set_flashdata('error-sweet', 'User ' . $usermikrotik . ' Tidak terdaftar di user Hotspot');
                }
            }
        } else {
            $this->session->set_flashdata('error-sweet', 'Router Disconnect, silahkan periksa kembali koneksi anda');
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function sinkronprofile()
    {
        $customer = $this->customer_m->getcustomerrouter()->result();
        // var_dump($customer);
        // die;
        foreach ($customer as  $data) {
            $router = $this->db->get_where('router', ['id' => $data->router])->row_array();
            $ip = $router['ip_address'];
            $user = $router['username'];
            $pass = $router['password'];
            $port = $router['port'];
            $API = new Mikweb();
            $usermikrotik = $data->user_mikrotik;
            $API->connect($ip, $user, $pass, $port);
            if ($data->mode_user == 'PPPOE') {
                $getuser = $API->comm("/ppp/secret/print", array('?service' => 'pppoe', '?name' => $usermikrotik));
                if (count($getuser) > 0) {
                    $this->db->set('user_profile', $getuser[0]['profile']);
                    $this->db->where('customer_id', $data->customer_id);
                    $this->db->update('customer');
                }
            }
            if ($data->mode_user == 'Hotspot') {
                $getuser = $API->comm("/ip/hotspot/user/print", array('?name' => $usermikrotik));
                if (count($getuser) > 0) {
                    $this->db->set('user_profile', $getuser[0]['profile']);
                    $this->db->where('customer_id', $data->customer_id);
                    $this->db->update('customer');
                }
            }
        }
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success-sweet', 'User Profile sudah disinkronkan');
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function getestimationincome()
    {

        $query = "SELECT *
        FROM `services` JOIN `customer`  ON `customer`.`no_services` = `services`.`no_services` WHERE  `customer`.`c_status` = 'Aktif' 
        ";
        $querying = $this->db->query($query)->result();
        $grandtotal = 0;
        foreach ($querying as  $dataa)
            $grandtotal += (int) $dataa->total;

        $data = [
            'estimation' => indo_currency($grandtotal),
        ];
        echo json_encode($data);
    }
    public function getestimationincomecoverage()
    {
        $post = $this->input->post(null, TRUE);
        $coverage = $post['coverage'];
        $query = "SELECT *
        FROM `services` JOIN `customer`  ON `customer`.`no_services` = `services`.`no_services` WHERE  `customer`.`c_status` = 'Aktif' and  `customer`.`coverage` = $coverage
        ";
        $querying = $this->db->query($query)->result();
        $grandtotal = 0;
        foreach ($querying as  $dataa)
            $grandtotal += (int) $dataa->total;

        $data = [
            'estimation' => indo_currency($grandtotal),
        ];
        echo json_encode($data);
    }

    public function cekbill()
    {
        $customer = $this->db->get_where('customer', ['connection' => 1])->result();
        foreach ($customer as $data) {
            if ($data->c_status == 'Aktif') {
                if ($data->auto_isolir == 1) {
                    $bill = $this->db->get_where('invoice', ['no_services' => $data->no_services, 'status' => 'BELUM BAYAR'])->row_array();
                    if ($bill == 0) {
                        openisolir($data->no_services, $data->router, 0);
                    }
                } else {
                    // openisolir($data->no_services, $data->router, 0);
                }
            }
        }
    }
    public function openisolir()
    {
        $customer = $this->db->get_where('customer', ['connection' => 1])->result();
        foreach ($customer as $data) {
            if ($data->c_status == 'Aktif') {
                if ($data->auto_isolir == 1) {

                    openisolir($data->no_services, $data->router, 0);
                } else {
                    openisolir($data->no_services, $data->router, 0);
                }
            }
        }
    }
    public function recekbill()
    {
        $listrouter = $this->db->get('router')->result();
        foreach ($listrouter as $router) {
            $totalcustomer = $this->db->get_where('customer', ['router' => $router->id])->num_rows();
            if ($totalcustomer > 0) {
                $billpasca = $this->customer_m->getrecheckisolir($router->id)->result();

                if (count($billpasca) > 0) {
                    $user = $router->username;
                    $ip = $router->ip_address;
                    $pass = $router->password;
                    $port = $router->port;
                    $API = new Mikweb();
                    $API->connect($ip, $user, $pass, $port);
                    foreach ($billpasca as $data) {
                        $userclient = $data->user_mikrotik;
                        if ($data->mode_user == 'PPPOE') {
                            if ($data->action == 0) {
                                $getuser = $API->comm("/ppp/secret/print", array(
                                    '?service' => 'pppoe',
                                    '?disabled' => 'no',
                                ));
                                foreach ($getuser as $pppoe) {
                                    if ($pppoe['name'] == $userclient) {
                                        $API->comm("/ppp/secret/disable", array(
                                            ".id" =>  $pppoe['.id'],
                                        ));
                                        $this->db->set('connection', 1);
                                        $this->db->where('customer_id', $data->customer_id);
                                        $this->db->update('customer');
                                    }
                                };
                            } else {
                                $getuser = $API->comm("/ppp/secret/print", array(
                                    '?service' => 'pppoe',
                                ));
                                if ($data->type_ip == 0) {
                                    foreach ($getuser as $pppoe) {
                                        if ($pppoe['name'] == $userclient) {
                                            $API->comm("/ppp/secret/set", array(
                                                ".id" => $pppoe['.id'],
                                                "profile" => 'EXPIRED'
                                            ));
                                            $this->db->set('connection', 1);
                                            $this->db->where('customer_id', $data->customer_id);
                                            $this->db->update('customer');
                                        }
                                    };
                                } else {
                                    foreach ($getuser as $pppoe) {
                                        if ($pppoe['name'] == $userclient) {
                                            $ipstatic = $pppoe['remote-address'];
                                            $API->comm("/ip/firewall/address-list/add", array(
                                                'list' => 'EXPIRED',
                                                'address' => $ipstatic,
                                                'comment' => 'ISOLIR|' . $data->no_services . '',

                                            ));

                                            $API->comm("/ppp/secret/set", array(
                                                ".id" => $pppoe['.id'],
                                                "profile" => 'EXPIRED'
                                            ));
                                            $this->db->set('connection', 1);
                                            $this->db->where('customer_id', $data->customer_id);
                                            $this->db->update('customer');
                                        }
                                    };
                                }
                            }
                            $getactive = $API->comm("/ppp/active/print", array(
                                '?name' => $userclient,
                            ));
                            $idactive = $getactive[0]['.id'];
                            $API->comm("/ppp/active/remove", array(
                                ".id" =>  $idactive,
                            ));
                        }
                        if ($data->mode_user == 'Hotspot') {
                            if ($data->action == 0) {
                                $getuser = $API->comm("/ip/hotspot/user/print", array(
                                    "?disabled" => 'no',
                                ));
                                foreach ($getuser as $hotspot) {
                                    if ($hotspot['name'] == $userclient) {
                                        $API->comm("/ip/hotspot/user/disable", array(
                                            ".id" =>  $hotspot['.id'],
                                        ));
                                        $this->db->set('connection', 1);
                                        $this->db->where('customer_id', $data->customer_id);
                                        $this->db->update('customer');
                                    }
                                };
                            } else {
                                $getuser = $API->comm("/ip/hotspot/user/print", array(
                                    "?disabled" => 'no',
                                ));
                                foreach ($getuser as $hotspot) {
                                    if ($hotspot['name'] == $userclient) {
                                        $API->comm("/ip/hotspot/user/set", array(
                                            ".id" =>  $hotspot['.id'],
                                            "profile" => 'EXPIRED'
                                        ));
                                        $this->db->set('connection', 1);
                                        $this->db->where('customer_id', $data->customer_id);
                                        $this->db->update('customer');
                                    }
                                };
                            }

                            $getactive = $API->comm("/ip/hotspot/active/print", array(
                                "?user" => $userclient,
                            ));
                            $idactive = $getactive[0]['.id'];
                            $API->comm("/ip/hotspot/active/remove", array(
                                ".id" => $idactive,
                            ));
                            // var_dump($getactive);
                        }
                        if ($data->mode_user == 'Static') {
                            $result = isolir($data->no_services, $data->router);
                            if (empty($result['success'])) {
                                $message = is_array($result) ? ($result['message'] ?? 'Isolir gagal tanpa keterangan dari helper.') : 'Isolir helper tidak mengembalikan hasil.';
                                log_message('error', 'Automatic isolation failed for service ' . $data->no_services . ': ' . $message);
                            }
                        }
                        echo  $data->name;
                        echo '<br>';
                    }
                }
            }
        }
    }

    public function get_data()
    {

        $data['customer'] =  $this->customer_m->getNSCustomer($this->input->post('no_services'))->row_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['bill'] =  $this->bill_m->getunpaid($this->input->post('no_services'))->result();
        $this->load->view('backend/customer/getdata', $data);
    }
    public function getdatacustomercoverage()
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['customer'] = $this->customer_m->getCustomer()->result();
        $data['post'] =  $this->input->post(null, TRUE);
        $this->load->view('backend/customer/get-data-customer', $data);
    }
    public function getfiltercoverage()
    {
        $post = $this->input->post(null, TRUE);
        $result = $this->customer_m->getfilterbycoverage($post);
        $ipByCustomer = $this->getCustomerIpMap($result);
        $data = [];
        $rt = $this->db->get('router')->row_array();
        $no = $_POST['start'];
        foreach ($result as $result) {
            $subtotal = $this->getCustomerMonthlyAmount($result);
            $row = array();
            $row[] = ++$no;
            $row[] = '<input type=' . 'checkbox' . ' class=' . 'check-item' . ' id="ceklis" name=' . 'customer_id[]' . ' value=' . $result->customer_id . '>';
            $row[] = $result->name;

            $row[] = $result->no_services;

            $row[] = indo_tlp($result->no_wa);
            $row[] = $result->c_status . '<br>Jatuh Tempo : ' . $result->due_date;
            $row[] = $result->auto_isolir == 1 ? 'Aktif' : 'Non-Aktif';
            $row[] = html_escape($result->cust_description);
            $row[] = $ipByCustomer[$result->customer_id];

            if ($this->session->userdata('role_id') == 1) {
                $row[] = indo_currency($subtotal);
            }
            $row[] = $result->address;
            if ($rt['is_active'] == 1) {
                $row[] = '<a href="' . site_url('services/detail/') . $result->no_services . '"    title="Detail Paket" style="font-size: smaller"><i class="fa fa-eye" style="font-size:25px; color:gray"></i></a> 
<a href="' . site_url('customer/edit/') . $result->customer_id . '" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a> 
<a href="' . site_url('mikrotik/client/') . $result->no_services . '" title="Koneksi"><i class="fa fa-random" style="font-size:25px;color:green"></i></a>                     
                <a href="#" id="delete" data-customer_id="' . $result->customer_id . '" data-no_services="' . $result->no_services . ' " data-name=' . $result->name . ' data-toggle="modal" data-target="#DeleteModal"   title="Delete" ><i class="fa fa-trash" style="font-size:25px; color:red"></i></a> 
                ';
            } else {
                $row[] = '<a href="' . site_url('services/detail/') . $result->no_services . '"    title="Detail Paket" style="font-size: smaller"><i class="fa fa-eye" style="font-size:25px; color:gray"></i></a> 
                <a href="' . site_url('customer/edit/') . $result->customer_id . '" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a> 
                <a href="#" id="delete" data-customer_id="' . $result->customer_id . '" data-no_services="' . $result->no_services . ' " data-name=' . $result->name . ' data-toggle="modal" data-target="#DeleteModal"   title="Delete" ><i class="fa fa-trash" style="font-size:25px; color:red"></i></a> 
                ';
            }

            $data[] = $row;
        }



        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->customer_m->getCustomerFilter($post)->num_rows(),
            "recordsFiltered" => $this->customer_m->count_filtered_data_coverage($post),
            "data" => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }

    public function updateuserprofile()
    {
        $post = $this->input->post(null, TRUE);

        $this->db->set('user_profile', $post['userprofile']);
        $this->db->where('no_services', $post['no_services']);
        $this->db->update('customer');
        if ($this->db->affected_rows() > 0) {
            $customer = $this->db->get_where('customer', ['no_services' => $post['no_services']])->row_array();
            if ($customer['mode_user'] == 'PPPOE') {
                $router = $this->db->get_where('router', ['id' => $customer['router']])->row_array();
                $ip = $router['ip_address'];
                $user = $router['username'];
                $pass = $router['password'];
                $port = $router['port'];
                $API = new Mikweb();
                $usermikrotik = $customer['user_mikrotik'];
                $API->connect($ip, $user, $pass, $port);
                $getuser = $API->comm("/ppp/secret/print", array('?service' => 'pppoe', '?name' => $usermikrotik));
                $id = $getuser[0]['.id'];
                $API->comm("/ppp/secret/set", array(
                    ".id" =>  $id,
                    "profile" => $post['userprofile'],
                ));
            }
            if ($customer['mode_user'] == 'Hotspot') {
                $router = $this->db->get_where('router', ['id' => $customer['router']])->row_array();
                $ip = $router['ip_address'];
                $user = $router['username'];
                $pass = $router['password'];
                $port = $router['port'];
                $API = new Mikweb();
                $usermikrotik = $customer['user_mikrotik'];
                $API->connect($ip, $user, $pass, $port);
                $getuserhotspot =  $API->comm("/ip/hotspot/user/print", array("?name" => $usermikrotik));
                $id = $getuserhotspot[0]['.id'];
                $API->comm("/ip/hotspot/user/set", array(
                    ".id" =>  $id,
                    "profile" => $post['userprofile'],
                ));
            }



            $data = [
                'status' => 1,

            ];
        } else {
            $data = [
                'status' => 0,
            ];
        }
        echo json_encode($data);
    }


    // SELECTED
    public function createuserlogin()
    {
        $customer_id = $_POST['customer_id'];

        if ($customer_id == null) {
            $this->session->set_flashdata('error-sweet', 'Pelanggan Belum dipilih');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $customer = $this->customer_m->getcustomerSelected($customer_id)->result();
            $no = 1;
            foreach ($customer as $data) {
                $user = $this->db->get_where('user', ['email' => $data->email])->row_array();
                if ($user > 0) {
                    echo $no++ . '. ' . $data->name . ' - ' . $data->no_services . ' - <span class="badge badge-danger">Sudah Terdaftar di akun pengguna</span><br>';
                } else {
                    $userlogin = [
                        'name' => htmlspecialchars($data->name),
                        'email' => htmlspecialchars($data->email),
                        'image' => 'default.jpg',
                        'password' => password_hash(1234, PASSWORD_DEFAULT),
                        'role_id' => 2,
                        'phone' => $data->no_wa,
                        'address' => htmlspecialchars($data->address),
                        'is_active' => 1,
                        'date_created' => time()
                    ];

                    $this->db->insert('user', $userlogin);
                    if ($this->db->affected_rows() > 0) {
                        $message = 'Create User Login ' . $data->name . ' - ' . $data->no_services;
                        $this->logs_m->activitylogs('Activity', $message);
                        echo $no++ . '. ' . $data->name . ' - ' . $data->no_services . ' - <span class="badge badge-success">Akun Berhasil dibuat</span><br>';
                        $whatsapp = $this->db->get('whatsapp')->row_array();
                        $other = $this->db->get('other')->row_array();
                        // get database company
                        $old = $data->no_wa;
                        $cek = substr($old, 0, 1);
                        if ($cek != 0) {
                            $nowa = '0' . $old;
                        } else {
                            $nowa = $old;
                        }
                        $company = $this->db->get('company')->row_array();
                        $search  = array('{name}', '{noservices}', '{month}', '{year}',  '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}', '{email}', '{password}', '{phone}');
                        $replace = array($data->name, $data->no_services, date('m'), date('Y'), $data->due_date, '', $company['company_name'], $company['sub_name'], base_url(), '', $data->email, '1234', $nowa);
                        $subject = $other['add_customer'];
                        $message = str_replace($search, $replace, $subject);
                        if ($whatsapp['is_active'] == 1) {
                            $target = indo_tlp($data->no_wa);
                            sendmsg($target, $message);
                        }
                    }
                }
            }
        }
    }
    public function setppnselected()
    {
        $customer_id = $_POST['customer_id'];

        if ($customer_id == null) {
            $this->session->set_flashdata('error-sweet', 'Pelanggan Belum dipilih');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $customer = $this->customer_m->getcustomerSelected($customer_id)->result();

            foreach ($customer as $data) {
                $this->db->set('ppn', 1);
                $this->db->where('customer_id', $data->customer_id);
                $this->db->update('customer');
                if ($this->db->affected_rows() > 0) {
                    $message = 'Aktifkan PPN ' . $data->name . ' - ' . $data->no_services;
                    $this->logs_m->activitylogs('Activity', $message);
                }
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function unsetppnselected()
    {
        $customer_id = $_POST['customer_id'];

        if ($customer_id == null) {
            $this->session->set_flashdata('error-sweet', 'Pelanggan Belum dipilih');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $customer = $this->customer_m->getcustomerSelected($customer_id)->result();

            foreach ($customer as $data) {
                $this->db->set('ppn', 0);
                $this->db->where('customer_id', $data->customer_id);
                $this->db->update('customer');
                if ($this->db->affected_rows() > 0) {
                    $message = 'Non-Aktifkan PPN ' . $data->name . ' - ' . $data->no_services;
                    $this->logs_m->activitylogs('Activity', $message);
                }
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function setpasca()
    {
        $customer_id = $_POST['customer_id'];

        if ($customer_id == null) {
            $this->session->set_flashdata('error-sweet', 'Pelanggan Belum dipilih');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $customer = $this->customer_m->getcustomerSelected($customer_id)->result();

            foreach ($customer as $data) {
                $this->db->set('type_payment', 0);
                $this->db->where('customer_id', $data->customer_id);
                $this->db->update('customer');
                if ($this->db->affected_rows() > 0) {
                    $message = 'Update ke Pascabayar ' . $data->name . ' - ' . $data->no_services;
                    $this->logs_m->activitylogs('Activity', $message);
                }
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function setpra()
    {
        $customer_id = $_POST['customer_id'];

        if ($customer_id == null) {
            $this->session->set_flashdata('error-sweet', 'Pelanggan Belum dipilih');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $customer = $this->customer_m->getcustomerSelected($customer_id)->result();

            foreach ($customer as $data) {
                $this->db->set('type_payment', 1);
                $this->db->where('customer_id', $data->customer_id);
                $this->db->update('customer');
                if ($this->db->affected_rows() > 0) {
                    $message = 'Update ke Prabayar ' . $data->name . ' - ' . $data->no_services;
                    $this->logs_m->activitylogs('Activity', $message);
                }
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function setactive()
    {
        $customer_id = $_POST['customer_id'];

        if ($customer_id == null) {
            $this->session->set_flashdata('error-sweet', 'Pelanggan Belum dipilih');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $customer = $this->customer_m->getcustomerSelected($customer_id)->result();

            foreach ($customer as $data) {
                $this->db->set('c_status', 'Aktif');
                $this->db->where('customer_id', $data->customer_id);
                $this->db->update('customer');
                if ($this->db->affected_rows() > 0) {
                    $message = 'Update status Pelanggan jadi Aktif ' . $data->name . ' - ' . $data->no_services;
                    $this->logs_m->activitylogs('Activity', $message);
                }
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function setnonactive()
    {
        $customer_id = $_POST['customer_id'];

        if ($customer_id == null) {
            $this->session->set_flashdata('error-sweet', 'Pelanggan Belum dipilih');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $customer = $this->customer_m->getcustomerSelected($customer_id)->result();

            foreach ($customer as $data) {
                $this->db->set('c_status', 'Non-Aktif');
                $this->db->where('customer_id', $data->customer_id);
                $this->db->update('customer');
                if ($this->db->affected_rows() > 0) {
                    $message = 'Update status Pelanggan jadi Non-Aktif ' . $data->name . ' - ' . $data->no_services;
                    $this->logs_m->activitylogs('Activity', $message);
                }
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function setchangeprofile()
    {
        $customer_id = $_POST['customer_id'];

        if ($customer_id == null) {
            $this->session->set_flashdata('error-sweet', 'Pelanggan Belum dipilih');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $customer = $this->customer_m->getcustomerSelected($customer_id)->result();

            foreach ($customer as $data) {
                $this->db->set('action', 1);
                $this->db->where('customer_id', $data->customer_id);
                $this->db->update('customer');
                if ($this->db->affected_rows() > 0) {
                    $message = 'Update action isolir menjadi Pindah Profile ' . $data->name . ' - ' . $data->no_services;
                    $this->logs_m->activitylogs('Activity', $message);
                }
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function setdisableuser()
    {
        $customer_id = $_POST['customer_id'];

        if ($customer_id == null) {
            $this->session->set_flashdata('error-sweet', 'Pelanggan Belum dipilih');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $customer = $this->customer_m->getcustomerSelected($customer_id)->result();

            foreach ($customer as $data) {
                $this->db->set('action', 0);
                $this->db->where('customer_id', $data->customer_id);
                $this->db->update('customer');
                if ($this->db->affected_rows() > 0) {
                    $message = 'Update action isolir menjadi Disable User ' . $data->name . ' - ' . $data->no_services;
                    $this->logs_m->activitylogs('Activity', $message);
                }
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function print($no_services)
    {

        $data = [
            'title' => 'Print ',
            'company' => $this->db->get('company')->row_array(),
            'services' =>  $this->services_m->getServices($no_services),
            'customer' => $this->db->get_where('customer', ['no_services' => $no_services])->row_array(),
        ];
        $this->load->view('backend/customer/print-customer', $data);
    }
}
