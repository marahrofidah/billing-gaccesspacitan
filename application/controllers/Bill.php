<?php
ob_start();
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Bill extends CI_Controller
{
    private $customerIpRouterModeCache = [];

    function __construct()
    {
        parent::__construct();
        is_logged_in();
        $this->load->model(['customer_m', 'package_m', 'services_m', 'setting_m', 'bill_m', 'income_m', 'logs_m', 'coverage_m']);
    }

    private function getInvoiceCustomerIpMap($customers)
    {
        $ipByCustomer = [];
        $customersByRouter = [];

        foreach ($customers as $customer) {
            $ipByCustomer[$customer->customer_id] = '-';
            if (!empty($customer->router) && !empty($customer->user_mikrotik)) {
                $customersByRouter[$customer->router][] = $customer;
            }
        }

        $this->load->driver('cache', ['adapter' => 'file', 'backup' => 'dummy']);

        foreach ($customersByRouter as $routerId => $routerCustomers) {
            $router = $this->db->get_where('router', ['id' => $routerId])->row_array();
            if (empty($router)) {
                foreach ($routerCustomers as $customer) {
                    $ipByCustomer[$customer->customer_id] = 'Router tidak ditemukan';
                }
                continue;
            }

            $modes = [];
            foreach ($routerCustomers as $customer) {
                if (in_array($customer->mode_user, ['PPPOE', 'Hotspot', 'Static'], true)) {
                    $modes[$customer->mode_user] = true;
                }
            }

            $routerModeData = [];
            $missingModes = [];
            foreach (array_keys($modes) as $mode) {
                $localCacheKey = $routerId . ':' . $mode;
                if (isset($this->customerIpRouterModeCache[$localCacheKey])) {
                    $routerModeData[$mode] = $this->customerIpRouterModeCache[$localCacheKey];
                    continue;
                }

                $cacheKey = 'customer_ip_' . (int) $routerId . '_' . md5($mode);
                $cachedModeData = $this->cache->get($cacheKey);
                if (is_array($cachedModeData)) {
                    $routerModeData[$mode] = $cachedModeData;
                    $this->customerIpRouterModeCache[$localCacheKey] = $cachedModeData;
                } else {
                    $missingModes[] = $mode;
                }
            }

            if (!empty($missingModes)) {
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
                    foreach ($missingModes as $mode) {
                        $modeData = [];
                        if ($mode === 'PPPOE') {
                            $modeData = ['active' => [], 'secret' => []];
                            $actives = $API->comm('/ppp/active/print', ['?service' => 'pppoe']);
                            foreach ((array) $actives as $active) {
                                if (!empty($active['name']) && !empty($active['address'])) {
                                    $modeData['active'][$active['name']] = $active['address'];
                                }
                            }

                            $secrets = $API->comm('/ppp/secret/print', ['?service' => 'pppoe']);
                            foreach ((array) $secrets as $secret) {
                                if (!empty($secret['name']) && !empty($secret['remote-address'])) {
                                    $modeData['secret'][$secret['name']] = $secret['remote-address'];
                                }
                            }
                        } elseif ($mode === 'Hotspot') {
                            $actives = $API->comm('/ip/hotspot/active/print');
                            foreach ((array) $actives as $active) {
                                if (!empty($active['user']) && !empty($active['address'])) {
                                    $modeData[$active['user']] = $active['address'];
                                }
                            }
                        } elseif ($mode === 'Static') {
                            $queues = $API->comm('/queue/simple/print');
                            foreach ((array) $queues as $queue) {
                                if (!empty($queue['name']) && !empty($queue['target'])) {
                                    $modeData[$queue['name']] = trim(explode('/', $queue['target'], 2)[0]);
                                }
                            }
                        }

                        $routerModeData[$mode] = $modeData;
                        $localCacheKey = $routerId . ':' . $mode;
                        $this->customerIpRouterModeCache[$localCacheKey] = $modeData;
                        $this->cache->save('customer_ip_' . (int) $routerId . '_' . md5($mode), $modeData, 15);
                    }
                } catch (RuntimeException $exception) {
                    log_message('error', 'Failed to retrieve invoice customer IPs from router ' . $routerId . ': ' . $exception->getMessage());
                    foreach ($routerCustomers as $customer) {
                        $ipByCustomer[$customer->customer_id] = 'Gagal mengambil IP';
                    }
                } finally {
                    $API->disconnect();
                }
            }

            foreach ($routerCustomers as $customer) {
                $username = $customer->user_mikrotik;
                if ($customer->mode_user === 'PPPOE') {
                    $modeData = isset($routerModeData['PPPOE']) ? $routerModeData['PPPOE'] : [];
                    $ip = isset($modeData['active'][$username])
                        ? $modeData['active'][$username]
                        : (isset($modeData['secret'][$username]) ? $modeData['secret'][$username] : '-');
                } elseif ($customer->mode_user === 'Hotspot') {
                    $modeData = isset($routerModeData['Hotspot']) ? $routerModeData['Hotspot'] : [];
                    $ip = isset($modeData[$username]) ? $modeData[$username] : '-';
                } elseif ($customer->mode_user === 'Static') {
                    $modeData = isset($routerModeData['Static']) ? $routerModeData['Static'] : [];
                    $ip = isset($modeData[$username]) ? $modeData[$username] : '-';
                } else {
                    $ip = '-';
                }

                $ipByCustomer[$customer->customer_id] = html_escape($ip);
            }
        }

        return $ipByCustomer;
    }

    private function prepareUnpaidInvoiceIpSearch()
    {
        $searchValue = isset($_POST['search']['value']) && is_string($_POST['search']['value'])
            ? trim($_POST['search']['value'])
            : '';
        if ($searchValue === '' || !preg_match('/^[0-9a-fA-F:.]+$/', $searchValue) || (strpos($searchValue, '.') === false && strpos($searchValue, ':') === false)) {
            return;
        }

        $customers = $this->db
            ->where('router !=', 0)
            ->where('user_mikrotik !=', '')
            ->get('customer')
            ->result();
        $ipByCustomer = $this->getInvoiceCustomerIpMap($customers);
        $matchingCustomerIds = [];

        foreach ($ipByCustomer as $customerId => $ip) {
            if (strpos($ip, $searchValue) !== false) {
                $matchingCustomerIds[] = $customerId;
            }
        }

        $_POST['ip_search'] = true;
        $_POST['ip_customer_ids'] = $matchingCustomerIds;
    }

    private function _menu()
    {
        $menu = $this->db->get_where('role_menu', ['role_id' => $this->session->userdata('role_id')])->row_array();
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
                $this->session->set_flashdata('error-sweet', 'Tidak ada coverage untuk akun anda');
                $row[] = '';
            }
            foreach ($operator as $roww) {
                $row[] = $roww->coverage_id;
            };
            return $row;
        }
    }

    private function getCustomerMonthlyAmount($customer)
    {
        $noServices = is_array($customer) ? ($customer['no_services'] ?? 0) : ($customer->no_services ?? 0);
        $email = is_array($customer) ? ($customer['email'] ?? '') : ($customer->email ?? '');
        $custAmount = is_array($customer) ? ($customer['cust_amount'] ?? 0) : ($customer->cust_amount ?? 0);

        if (!empty($noServices) && (int) $noServices !== 0) {
            $services = $this->db->get_where('services', ['no_services' => $noServices])->result();
        } elseif (!empty($email)) {
            $services = $this->db->get_where('services', ['email' => $email])->result();
        } else {
            return (int) $custAmount;
        }

        $subtotal = 0;
        foreach ($services as $service) {
            $subtotal += (int) $service->total;
        }

        return $subtotal > 0 ? $subtotal : (int) $custAmount;
    }
    public function update($before, $after)
    {
        if ($this->session->userdata('email') == 'ginginabdulgoni@gmail.com') {
            $this->db->set('month', $before);
            $this->db->where('month', $after);
            $this->db->update('invoice');
            redirect('bill/history');
        } else {
            $this->session->set_flashdata('error-sweet', 'akses dilarang bos');
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function updatedetail($before, $after)
    {
        if ($this->session->userdata('email') == 'ginginabdulgoni@gmail.com') {
            $this->db->set('d_month', $before);
            $this->db->where('d_month', $after);
            $this->db->update('invoice_detail');
            redirect('bill/history');
        } else {
            $this->session->set_flashdata('error-sweet', 'akses dilarang bos');
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function index()
    {
        $role = $this->_role();
        $cover = $this->_coverage();
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $data['customer'] = $this->customer_m->getCustomeractive($cover)->result();
            $data['coverage'] = $this->coverage_m->getCoverage($cover)->result();
        } else {
            $data['customer'] = $this->customer_m->getCustomeractive()->result();
            $data['coverage'] = $this->coverage_m->getCoverage()->result();
        }
        $data['title'] = 'Bill';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $data['company'] = $this->db->get('company')->row_array();
        $data['invoice'] =  $this->bill_m->invoice_no();

        $data['other'] = $this->db->get('other')->row_array();
        $this->template->load('backend', 'backend/bill/bill', $data);
    }
    public function exportbill()

    {



        $post = $this->input->post(null, TRUE);





        $data['title'] = 'Data Tagihan ' . indo_month($post['month']) . ' ' . $post['year'];

        // $data['bill'] = $this->bill_m->getInvoicemonthyear($month, $year)->result();

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');

        $sheet->setCellValue('B1', 'Nama');

        $sheet->setCellValue('C1', 'No Layanan');

        $sheet->setCellValue('D1', 'No Invoice');

        $sheet->setCellValue('E1', 'Periode');

        $sheet->setCellValue('F1', 'Nominal');

        $sheet->setCellValue('G1', 'Jatuh Tempo');

        $sheet->setCellValue('H1', 'Status');

        // $sheet->setCellValue('I1', 'BCA VA');



        $customer = $this->bill_m->getexportinvoice($post)->result();

        $no = 1;

        $x = 2;

        foreach ($customer as $row) {

            $sheet->setCellValue('A' . $x, $no++);

            $sheet->setCellValue('B' . $x, $row->name);

            $sheet->setCellValue('C' . $x, $row->no_services);

            $sheet->setCellValue('D' . $x, $row->invoice);

            $sheet->setCellValue('E' . $x, indo_month($row->month) . ' ' . $row->year);

            $sheet->setCellValue('F' . $x, $row->amount);

            $sheet->setCellValue('G' . $x, $row->inv_due_date);

            $sheet->setCellValue('H' . $x, $row->status);

            // $sheet->setCellValue('I' . $x, $row->no_va);



            $x++;
        }

        // Set width kolom

        $sheet->getColumnDimension('A')->setWidth(5); // Set width kolom A

        $sheet->getColumnDimension('B')->setWidth(15); // Set width kolom B

        $sheet->getColumnDimension('C')->setWidth(25); // Set width kolom C

        $sheet->getColumnDimension('D')->setWidth(20); // Set width kolom D

        $sheet->getColumnDimension('E')->setWidth(30); // Set width kolom E

        $sheet->getColumnDimension('F')->setWidth(30); // Set width kolom E

        $sheet->getColumnDimension('G')->setWidth(30); // Set width kolom E

        $sheet->getColumnDimension('H')->setWidth(30); // Set width kolom E

        $sheet->getColumnDimension('I')->setWidth(30); // Set width kolom E

        $writer = new Xlsx($spreadsheet);

        $filname = 'Data Tagihan Bulan ' . indo_month($post['month']) . ' ' . $post['year'] . ' Cetak ' .  date('d-M-Y') . ' ' . date('H:i:s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        header('Content-Disposition: attachment; filename="' . $filname . '"');

        ob_end_clean();

        $writer->save('php://output');
    }

    public function duedate()
    {

        $menu = $this->_menu();
        $role = $this->_role();
        $cover = $this->_coverage();
        if ($menu['role_id'] != 1 && $menu['bill_menu'] == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $data['customer'] = $this->customer_m->getCustomeractive($cover)->result();
            $data['coverage'] = $this->coverage_m->getCoverage($cover)->result();
        } else {
            $data['customer'] = $this->customer_m->getCustomeractive()->result();
            $data['coverage'] = $this->coverage_m->getCoverage()->result();
        }
        $data['title'] = 'Jatuh Tempo';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();

        $data['other'] = $this->db->get('other')->row_array();
        $this->template->load('backend', 'backend/bill/duedate', $data);
    }
    public function getduedate()
    {
        $result = $this->bill_m->getduedate();
        $data = [];
        $no = $_POST['start'];

        foreach ($result as $result) {
            if ($result->codeunique == 1) {
                $codeunique = $result->code_unique;
            } else {
                $codeunique = 0;
            }

            $other = $this->db->get('other')->row_array();
            $company = $this->db->get('company')->row_array();
            $getuser = $this->db->get_where('user', ['id' => $result->create_by])->row_array();
            $search  = array('{name}', '{noservices}', '{nova}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}', '{receiver}');
            $replace = array($result->name, $result->no_services, $result->no_va, $result->month, $result->year, indo_month($result->month) . ' ' . $result->year, indo_date($result->inv_due_date), $result->amount - $result->disc_coupon + $codeunique, $company['company_name'], $company['sub_name'], base_url(), '%0A', $getuser['name']);
            $subject = $other['body_wa'];
            $subjectthanks = $other['thanks_wa'];
            $message = str_replace($search, $replace, $subject);
            $messagethanks = str_replace($search, $replace, $subjectthanks);
            $linkWA = "https://api.whatsapp.com/send?phone=";
            $whatsapp = $this->db->get('whatsapp')->row_array();
            if ($whatsapp['is_active'] == 1) {
                if ($result->status == 'SUDAH BAYAR') {
                    $action = '<a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
        <a href="#" id="hapusmodalbayar" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModalBayar" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>
        <a href="' . site_url('whatsapp/sendbillpaid/' . $result->invoice) . '"><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a> ';
                } else {
                    $action = '<a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
        <a href="' . site_url('whatsapp/sendbillduedate/' . $result->invoice) . '" ><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a>
        <a href="#" id="hapusmodal" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModal" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>';
                }
            } else {
                if ($result->status == 'SUDAH BAYAR') {
                    $action = '<a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
        <a href="#" id="hapusmodalbayar" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModalBayar" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>
        <a href="' . $linkWA . indo_tlp($result->no_wa) . '&text=' . $messagethanks . '" target="blank"><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a> ';
                } else {
                    $action = ' <a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
        <a href="' . $linkWA . indo_tlp($result->no_wa) . '&text=' . $message   . '" target="blank"><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a>
        <a href="#" id="hapusmodal" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModal" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>';
                }
            };
            $row = [
                'no' => ++$no,
                'name' => $result->name,
                'no_services' => $result->no_services,
                'periode' => indo_month($result->month) . ' ' .  $result->year . '<br>' . indo_date($result->inv_due_date),
                'action' => $action,
                'amount' => indo_currency($result->amount),

                'select' => '<input type=' . 'checkbox' . ' class=' . 'check-item' . ' id="ceklis" name=' . 'invoice[]' . ' value=' . $result->invoice . '>',
            ];
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->bill_m->count_all_data_duedate(),
            "recordsFiltered" => $this->bill_m->count_filtered_data_duedate(),
            "data" => $data,
        );

        echo json_encode($output);
    }
    public function draf()
    {
        $data['title'] = 'Bill Draf';
        $menu = $this->_menu();
        $role = $this->_role();
        $cover = $this->_coverage();
        if ($menu['role_id'] != 1 && $menu['bill_draf'] == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $data['coverage'] = $this->coverage_m->getCoverage($cover)->result();
            $data['customer'] = $this->customer_m->getCustomerActive($cover)->result();
        } else {
            $data['coverage'] = $this->coverage_m->getCoverage()->result();
            $data['customer'] = $this->customer_m->getCustomerActive()->result();
        }
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $data['invoice'] = $this->bill_m->invoice_no();
        $data['other'] = $this->db->get('other')->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/bill/draf', $data);
    }
    public function filterdraf()
    {
        $post = $this->input->post(null, TRUE);
        $data['title'] = 'Filter Draf';
        $role = $this->_role();
        $cover = $this->_coverage();

        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $data['coverage'] = $this->coverage_m->getCoverage($cover)->result();
        } else {
            $data['coverage'] = $this->coverage_m->getCoverage()->result();
        }
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['invoice'] = $this->bill_m->invoice_no();
        // $data['customer'] = $this->customer_m->getfilterby($post)->result();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/bill/draf', $data);
    }
    public function filter()
    {
        $post = $this->input->post(null, TRUE);
        $role = $this->_role();
        $cover = $this->_coverage();

        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $data['coverage'] = $this->coverage_m->getCoverage($cover)->result();
        } else {
            $data['coverage'] = $this->coverage_m->getCoverage()->result();
        }
        $data['title'] = 'Bill';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['bill'] = $this->bill_m->getInvoiceFilter($post, $cover)->result();
        // $data['income'] = $this->_getestimationincome($post['coverage'], $post['jenis_bayar'], $post['year'], $post['month']);
        $data['invoice'] = $this->bill_m->invoice_no();

        $data['company'] = $this->db->get('company')->row_array();

        $this->template->load('backend', 'backend/bill/billFilter', $data);
    }
    public function filterunpaid()
    {
        if ($this->input->post('month') <= 0) {
            echo "<script>window.location='" . site_url('bill/unpaid') . "'; </script>";
        }
        $post = $this->input->post(null, TRUE);
        $data['title'] = 'Belum Bayar';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['customer'] = $this->customer_m->getCustomer()->result();
        $data['bill'] = $this->bill_m->getInvoiceFilterUnpaid($post)->result();
        $data['detail'] = $this->bill_m->getInvoiceDetail()->result();
        $data['invoice'] = $this->bill_m->invoice_no();
        $data['bank'] = $this->setting_m->getBank()->row_array();
        $data['other'] = $this->db->get('other')->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/bill/unpaid', $data);
    }
    public function filterpaid()
    {
        if ($this->input->post('month') <= 0) {
            echo "<script>window.location='" . site_url('bill/paid') . "'; </script>";
        }
        $post = $this->input->post(null, TRUE);
        $data['title'] = 'Sudah Bayar';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['customer'] = $this->customer_m->getCustomer()->result();
        $data['bill'] = $this->bill_m->getInvoiceFilterpaid($post)->result();
        $data['detail'] = $this->bill_m->getInvoiceDetail()->result();
        $data['invoice'] = $this->bill_m->invoice_no();
        $data['bank'] = $this->setting_m->getBank()->row_array();
        $data['other'] = $this->db->get('other')->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/bill/paid', $data);
    }
    public function filtercoverage()
    {
        $post = $this->input->post(null, TRUE);
        $data['title'] = 'Bill Filter';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['bill'] = $this->bill_m->getfilterby($post)->result();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/bill/billfilter', $data);
    }
    public function unpaid()
    {
        $menu = $this->_menu();
        $role = $this->_role();
        $cover = $this->_coverage();
        if ($menu['role_id'] != 1 && $menu['bill_menu'] == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {

            $data['coverage'] = $this->coverage_m->getCoverage($cover)->result();
            $data['customer'] = $this->customer_m->getCustomeractive($cover)->result();
        } else {

            $data['coverage'] = $this->coverage_m->getCoverage()->result();
            $data['customer'] = $this->customer_m->getCustomeractive()->result();
        }
        $data['title'] = 'Belum Bayar';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $data['invoice'] = $this->bill_m->invoice_no();
        $data['company'] = $this->db->get('company')->row_array();

        $this->template->load('backend', 'backend/bill/unpaid', $data);
    }
    public function paid()
    {
        $menu = $this->_menu();
        $role = $this->_role();
        $cover = $this->_coverage();
        if ($menu['role_id'] != 1 && $menu['bill_menu'] == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $data['customer'] = $this->customer_m->getCustomeractive($cover)->result();
            $data['coverage'] = $this->coverage_m->getCoverage($cover)->result();
        } else {
            $data['customer'] = $this->customer_m->getCustomeractive()->result();
            $data['coverage'] = $this->coverage_m->getCoverage()->result();
        }
        $data['title'] = 'Sudah Bayar';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/bill/paid', $data);
    }


    public function view_data()
    {

        $data['services'] =  $this->services_m->getServicesDetail($this->input->post('no_services'));
        $this->load->view('backend/bill/detail_bill', $data);
    }



    public function addBill()
    {

        $post = $this->input->post(null, TRUE);
        $company = $this->db->get('company')->row_array();
        $no_services = $this->input->post('no_services');
        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $cekperiode = $this->bill_m->cekPeriode($no_services, $month, $year);
        $inv = $this->input->post('invoice');
        $cekCustomer = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
        if ($cekCustomer['ppn'] > 0) {
            $ppn = $company['ppn'];
        } else {
            $ppn = 0;
        }
        if (strlen($cekCustomer['due_date']) == 1) {
            $date = '0' . $cekCustomer['due_date'];
        } else {
            $date =  $cekCustomer['due_date'];
        }
        if ($cekCustomer['month_due_date'] == 0) {
            $post['due_date'] = $year . '-' . $month . '-' . $date;
        } else {

            $newduedate = $year . '-' . $month . '-' . $date;
            $nextmonth = date('m', strtotime('next month', strtotime($newduedate)));
            $nextyear = date('Y', strtotime('next month', strtotime($newduedate)));
            $post['due_date'] = $nextyear . '-' . $nextmonth . '-' .  $cekCustomer['due_date'];
        }

        $max = $cekCustomer['max_due_isolir'];
        if ($cekCustomer['max_due_isolir'] != 0) {
            $gettime = strtotime("+$max day", strtotime($post['due_date']));
            $date = date("d", $gettime);
            $monthisolir = date("m", $gettime);
            $yearisolir = date("Y", $gettime);
            $enddate = $yearisolir . '-' . $monthisolir . '-' . $date;
            $post['date_isolir'] = $enddate;
        } else {

            $post['date_isolir'] = $post['due_date'];
        }
        $post['code_unique'] = substr(intval(rand()), 0, 3);
        $cekinvoice = $this->bill_m->cekInvoice($inv);
        $getInv = $this->bill_m->getRecentInv()->row();

        if ($cekinvoice->num_rows() > 0) {
            $tgl = date('ymd');
            $invoice = $tgl . substr(intval(rand()), 0, 3) + 1;
            if ($getInv > 0) {
                $invoice = $getInv->invoice + 1;
            }
        } else {
            $invoice = $this->input->post('invoice');
        }
        $amountt = $this->getCustomerMonthlyAmount($cekCustomer);
        $amount = $amountt + ($amountt * ($ppn / 100));
        if ($amount <= 0) {
            $this->session->set_flashdata('error-sweet', 'Tagihan pelanggan bernilai 0. Lengkapi nomor layanan, email, atau nominal cust_amount.');
            redirect($_SERVER['HTTP_REFERER']);
            return;
        }

        if ($cekperiode->num_rows() > 0) {
            $this->session->set_flashdata('error-sweet', 'Gagal, Tagihan untuk periode tersebut sudah tersedia, mohon dicek kembali !');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->bill_m->addBill($post, $invoice, $ppn, $amount);

            if ($this->db->affected_rows() > 0) {
                $Detail = $this->services_m->getServicesDetail($this->input->post('no_services'))->result();
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
                            'd_no_services' => $row->no_services,
                        )
                    );
                }
                $this->bill_m->add_bill_detail($data2);

                $whatsapp = $this->db->get('whatsapp')->row_array();
                if ($whatsapp['is_active'] == 1) {
                    $other = $this->db->get('other')->row_array();
                    $company = $this->db->get('company')->row_array();
                    // echo  $timeex;
                    if ($whatsapp['createinvoice'] == 1) {
                        $cekperiode = $this->bill_m->cekPeriode($no_services, $month, $year)->row_array();
                        $target = indo_tlp($cekCustomer['no_wa']);
                        if ($cekCustomer['codeunique'] == 1) {
                            $codeunique = $cekperiode['code_unique'];
                        } else {
                            $codeunique = 0;
                        }
                        $nominalWA = indo_currency($cekperiode['amount'] + $codeunique);
                        $search  = array('{name}', '{noservices}', '{email}', '{invoice}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}');
                        $replace = array($cekCustomer['name'], $cekCustomer['no_services'], $cekCustomer['email'], $cekperiode['invoice'], indo_month($month), $year, indo_month($month) . ' ' . $year, indo_date($cekperiode['inv_due_date']), $nominalWA, $company['company_name'], $company['sub_name'], base_url(), '');
                        $subject = $other['say_wa'];
                        $message = str_replace($search, $replace, $subject);
                        $target = indo_tlp($cekCustomer['no_wa']);
                        sendmsgbill($target, $message, $cekperiode['invoice']);
                    }
                }
                $customer = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
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
                    'remark' => 'Tambah Tagihan' . ' ' . $customer['name'] . ' ' . $customer['no_services'] . ' Periode ' . indo_month($month) . ' ' . $year . ' dari' . ' ' . $this->agent->platform() . ' ' . $this->input->ip_address() . ' ' . $agent,
                ];
                $this->db->insert('logs', $params);
                $this->session->set_flashdata('success-sweet', 'Tagihan berhasil dibuat');
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function addBillDraf()
    {
        $post = $this->input->post(null, TRUE);
        $redirect = $this->agent->referrer();
        if (empty($redirect)) {
            $redirect = site_url('bill/draf');
        }

        $company = $this->db->get('company')->row_array();
        if (!$company || !isset($company['ppn']) || !is_numeric($company['ppn'])) {
            $this->session->set_flashdata('error-sweet', 'Lengkapi dan simpan pengaturan perusahaan serta PPN sebelum membuat tagihan.');
            redirect('setting');
            return;
        }
        $no_services = isset($post['no_services']) ? trim($post['no_services']) : '';
        $month = isset($post['month']) ? $post['month'] : '';
        $year = isset($post['year']) ? $post['year'] : '';
        if (!ctype_digit($no_services) || !checkdate((int) $month, 1, (int) $year)) {
            $this->session->set_flashdata('error-sweet', 'Data layanan atau periode tagihan tidak valid.');
            redirect($redirect);
            return;
        }

        $cekCustomer = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
        if (!$cekCustomer) {
            $this->session->set_flashdata('error-sweet', 'Data pelanggan tidak ditemukan.');
            redirect($redirect);
            return;
        }

        $cekperiode = $this->bill_m->cekPeriode($no_services, $month, $year);
        if ($cekperiode->num_rows() > 0) {
            $this->session->set_flashdata('error-sweet', 'Gagal, Tagihan untuk periode tersebut sudah tersedia, mohon dicek kembali !');
            redirect($redirect);
            return;
        }

        $getInv = $this->bill_m->getRecentInv()->row();
        $tgl = date('ymd');
        $no = 001;
        if (!$getInv || $getInv->invoice == 0) {
            $invoice = ($tgl . '' .  str_pad($no, 3, "0", STR_PAD_LEFT));
        } else {
            $invoice = $getInv->invoice + 1;
        }
        if ($cekCustomer['ppn'] > 0) {
            $ppn = $company['ppn'];
        } else {
            $ppn = 0;
        }
        if (strlen($cekCustomer['due_date']) == 1) {
            $date = '0' . $cekCustomer['due_date'];
        } else {
            $date =  $cekCustomer['due_date'];
        }
        if ($cekCustomer['month_due_date'] == 0) {
            $post['due_date'] = $year . '-' . $month . '-' . $date;
        } else {

            $newduedate = $year . '-' . $month . '-' . $date;
            $nextmonth = date('m', strtotime('next month', strtotime($newduedate)));
            $nextyear = date('Y', strtotime('next month', strtotime($newduedate)));
            $post['due_date'] = $nextyear . '-' . $nextmonth . '-' .  $cekCustomer['due_date'];
        }

        $max = $cekCustomer['max_due_isolir'];
        if ($cekCustomer['max_due_isolir'] != 0) {
            $gettime = strtotime("+$max day", strtotime($post['due_date']));
            $date = date("d", $gettime);
            $monthisolir = date("m", $gettime);
            $yearisolir = date("Y", $gettime);
            $enddate = $yearisolir . '-' . $monthisolir . '-' . $date;
            $post['date_isolir'] = $enddate;
        } else {

            $post['date_isolir'] = $post['due_date'];
        }

        $post['code_unique'] = substr(intval(rand()), 0, 3);

        $amountt = $this->getCustomerMonthlyAmount($cekCustomer);
        $amount = $amountt + $amountt * ($ppn / 100);
        if ($amount <= 0) {
            $this->session->set_flashdata('error-sweet', 'Tagihan pelanggan bernilai 0. Lengkapi nomor layanan, email, atau nominal cust_amount.');
            redirect($redirect);
            return;
        }
        $qrError = null;
        if (function_exists('imagecreate')) {
            try {
                $this->load->library('ciqrcode');
                $config['cacheable'] = true;
                $config['cachedir'] = './assets/';
                $config['errorlog'] = './assets/';
                $config['imagedir'] = './assets/images/qrcode/';
                $config['quality'] = true;
                $config['size'] = '1024';
                $config['black'] = [224, 255, 255];
                $config['white'] = [70, 130, 180];
                $this->ciqrcode->initialize($config);
                $params = [
                    'data' => $invoice . '-' . $no_services,
                    'level' => 'H',
                    'size' => 10,
                    'savename' => FCPATH . $config['imagedir'] . $invoice . '.png',
                ];
                $this->ciqrcode->generate($params);
            } catch (Throwable $e) {
                log_message('error', 'Gagal membuat QR tagihan ' . $invoice . ': ' . $e->getMessage());
                $qrError = 'Tagihan tersimpan, tetapi QR code gagal dibuat.';
            }
        } else {
            log_message('error', 'Ekstensi GD tidak aktif; QR tagihan ' . $invoice . ' tidak dibuat.');
            $qrError = 'Tagihan tersimpan, tetapi QR code tidak dibuat karena ekstensi GD belum aktif.';
        }
        if ($cekperiode->num_rows() > 0) {
            $this->session->set_flashdata('error-sweet', 'Gagal, Tagihan untuk periode tersebut sudah tersedia, mohon dicek kembali !');
            redirect($redirect);
            return;
        } else {
            $this->bill_m->addBill($post, $invoice, $ppn, $amount);
            if ($this->db->affected_rows() > 0) {
                $Detail = $this->services_m->getServicesDetail($this->input->post('no_services'))->result();
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
                            'd_no_services' => $row->no_services,
                        )
                    );
                }
                $this->bill_m->add_bill_detail($data2);
                $whatsapp = $this->db->get('whatsapp')->row_array();
                if ($whatsapp['is_active'] == 1) {
                    $other = $this->db->get('other')->row_array();
                    $company = $this->db->get('company')->row_array();

                    if ($whatsapp['createinvoice'] == 1) {
                        $cekperiode = $this->bill_m->cekPeriode($no_services, $month, $year)->row_array();
                        $target = indo_tlp($cekCustomer['no_wa']);
                        if ($cekCustomer['codeunique'] == 1) {
                            $codeunique = $cekperiode['code_unique'];
                        } else {
                            $codeunique = 0;
                        }
                        $nominalWA = indo_currency($cekperiode['amount'] + $codeunique);
                        $search  = array('{name}', '{noservices}', '{email}', '{invoice}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}');
                        $replace = array($cekCustomer['name'], $cekCustomer['no_services'], $cekCustomer['email'], $cekperiode['invoice'], indo_month($month), $year, indo_month($month) . ' ' . $year, indo_date($cekperiode['inv_due_date']), $nominalWA, $company['company_name'], $company['sub_name'], base_url(), '');
                        $subject = $other['say_wa'];
                        $message = str_replace($search, $replace, $subject);

                        $target = indo_tlp($cekCustomer['no_wa']);
                        sendmsgbill($target, $message, $cekperiode['invoice']);
                    }
                }
                $customer = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
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
                    'remark' => 'Tambah Tagihan' . ' ' . $customer['name'] . ' ' . $customer['no_services'] . ' Periode ' . indo_month($month) . ' ' . $year . ' dari' . ' ' . $this->agent->platform() . ' ' . $this->input->ip_address() . ' ' . $agent,
                ];
                $this->db->insert('logs', $params);
                $this->session->set_flashdata('success-sweet', 'Tagihan berhasil dibuat');
            } else {
                $this->session->set_flashdata('error-sweet', 'Tagihan gagal disimpan. Silakan coba lagi.');
            }
            if ($qrError !== null) {
                $this->session->set_flashdata('error-sweet', $qrError);
            }
            redirect($redirect);
        }
    }
    public function generateBill()
    {
        $no_services = $this->customer_m->getCustomerActive()->result();
        if (count($no_services) == 0) {
            $this->session->set_flashdata('error-sweet', 'Gagal, Tidak ada daftar pelanggan Aktif !');
            redirect($_SERVER['HTTP_REFERER']);
        }
        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $inv = $this->input->post('invoice');
        $company = $this->db->get('company')->row_array();
        $cekperiode = $this->bill_m->cekPeriodeMonth($month, $year);
        $cekinvoice = $this->bill_m->cekInvoice($inv);
        $getInv = $this->bill_m->getRecentInv()->row();
        if ($cekinvoice->num_rows() > 0) {
            $kode = $getInv->invoice + 1;
        } else {
            $tgl = date('ymd');
            $no = 001;
            $kode = ($tgl . '' .  str_pad($no, 3, "0", STR_PAD_LEFT));
        }

        if ($cekperiode->num_rows() > 0) {
            $this->session->set_flashdata('error-sweet', 'Gagal, Tagihan untuk periode ini sudah tersedia disalah satu pelanggan, mohon dicek kembali  !');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $dataNS = [];
            foreach ($no_services as $c => $row) {

                if ($row->ppn != 0) {
                    $ppn = $company['ppn'];
                } else {
                    $ppn = 0;
                }
                if (strlen($row->due_date) == 1) {
                    $date = '0' . $row->due_date;
                } else {
                    $date = $row->due_date;
                }


                if ($row->month_due_date == 0) {
                    $duedate = $year . '-' . $month . '-' . $date;
                } else {
                    $newduedate = $year . '-' . $month . '-' . $date;
                    $nextmonth = date('m', strtotime('next month', strtotime($newduedate)));
                    $nextyear = date('Y', strtotime('next month', strtotime($newduedate)));
                    $duedate = $nextyear . '-' . $nextmonth . '-' . $date;
                }


                $max = $row->max_due_isolir;
                if ($row->max_due_isolir != 0) {
                    $gettime = strtotime("+$max day", strtotime($duedate));
                    $datenew = date("d", $gettime);
                    $monthisolir = date("m", $gettime);
                    $yearisolir = date("Y", $gettime);
                    $enddate = $yearisolir . '-' . $monthisolir . '-' . $datenew;
                } else {
                    $enddate = $duedate;
                }
                $amountt = $this->getCustomerMonthlyAmount($row);
                $amount = $amountt + $amountt * ($ppn / 100);
                if ($amount <= 0) {
                    continue;
                }
                array_push(
                    $dataNS,
                    array(
                        'no_services' => $row->no_services,
                        'invoice' => $kode++,
                        'month' => $month,
                        'i_ppn' => $ppn,
                        'amount' => $amount,
                        'year' => $year,
                        'code_unique' => substr(intval(rand()), 0, 3),
                        'status' => 'BELUM BAYAR',
                        'inv_due_date' => $duedate,
                        'date_isolir' => $enddate,
                        'created' => time()
                    )
                );
            }
            $this->bill_m->add_bill_generate($dataNS);

            if ($this->db->affected_rows() > 0) {
                $detail = $this->services_m->getServicesActive()->result();
                $data2 = [];
                foreach ($detail as $c => $row) {
                    array_push(
                        $data2,
                        array(
                            // 'invoice_id' => $kode1++,
                            'item_id' => $row->item_id,
                            'category_id' => $row->category_id,
                            'price' => $row->services_price,
                            'qty' => $row->qty,
                            'disc' => $row->disc,
                            'remark' => $row->remark,
                            'total' => $row->total,
                            'd_month' => $month,
                            'd_year' => $year,
                            'd_no_services' => $row->no_services,
                        )
                    );
                }
                $this->bill_m->add_bill_detail($data2);
                $whatsapp = $this->db->get('whatsapp')->row_array();
                if ($whatsapp['is_active'] == 1) {

                    $other = $this->db->get('other')->row_array();
                    $company = $this->db->get('company')->row_array();
                    $no = 1;
                    foreach ($no_services as $wa) {
                        $range = $no++ * $whatsapp['interval_message'];

                        $jadwall = time() + (1  * $range);
                        $time = date('Y-m-d H:i:s', $jadwall);

                        if ($whatsapp['createinvoice'] == 1) {
                            $cekperiode = $this->bill_m->cekPeriode($wa->no_services, $month, $year)->row_array();
                            if ($cekperiode > 0) {
                                $target = indo_tlp($wa->no_wa);
                                if ($wa->codeunique == 1) {
                                    $codeunique = $cekperiode['code_unique'];
                                } else {
                                    $codeunique = 0;
                                }
                                $nominalWA = indo_currency($cekperiode['amount'] + $codeunique);
                                $search  = array('{name}', '{noservices}', '{email}', '{invoice}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}');
                                $replace = array($wa->name, $wa->no_services,  $wa->email, $cekperiode['invoice'], indo_month($month), $year, indo_month($month) . ' ' . $year, indo_date($cekperiode['inv_due_date']), $nominalWA, $company['company_name'], $company['sub_name'], base_url(), '');
                                $subject = $other['say_wa'];
                                $message = str_replace($search, $replace, $subject);
                                sendmsgschbill($target, $message, $time, $cekperiode['invoice']);
                            }
                        }
                    }
                }
                $this->session->set_flashdata('success-sweet', 'Generate Tagihan berhasil dibuat');
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function generateselected()
    {
        $noservices = $this->input->post('noservices');
        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $company = $this->db->get('company')->row_array();
        if (empty($noservices)) {
            $this->session->set_flashdata('error-sweet', 'Tagihan belum dipilih');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $selectedCustomers = $this->customer_m->getCustomerSelecteddraf($noservices)->result();
            $customersToGenerate = [];
            $noservices = [];
            foreach ($selectedCustomers as $customer) {
                $existingInvoice = $this->db->get_where('invoice', [
                    'no_services' => $customer->no_services,
                    'month' => $month,
                    'year' => $year,
                ])->row_array();
                if (empty($existingInvoice)) {
                    $customersToGenerate[] = $customer;
                    $noservices[] = $customer->no_services;
                }
            }
            if (empty($customersToGenerate)) {
                $this->session->set_flashdata('error-sweet', 'Tagihan pelanggan yang dipilih sudah tersimpan');
                redirect($_SERVER['HTTP_REFERER']);
            }

            $getInv = $this->bill_m->getRecentInv()->row();
            $tgl = date('ymd');
            $no = 001;
            if (!$getInv || $getInv->invoice == 0) {
                $kode = ($tgl . '' .  str_pad($no, 3, "0", STR_PAD_LEFT));
            } else {
                $kode = $getInv->invoice + 1;
            }
            $dataNS = [];
            foreach ($customersToGenerate as $c => $row) {

                if ($row->ppn != 0) {
                    $ppn = $company['ppn'];
                } else {
                    $ppn = 0;
                }

                if (strlen($row->due_date) == 1) {
                    $date = '0' . $row->due_date;
                } else {
                    $date = $row->due_date;
                }


                if ($row->month_due_date == 0) {
                    $duedate = $year . '-' . $month . '-' . $date;
                } else {
                    $newduedate = $year . '-' . $month . '-' . $date;
                    $nextmonth = date('m', strtotime('next month', strtotime($newduedate)));
                    $nextyear = date('Y', strtotime('next month', strtotime($newduedate)));
                    $duedate = $nextyear . '-' . $nextmonth . '-' . $date;
                }


                $max = $row->max_due_isolir;
                if ($row->max_due_isolir != 0) {
                    $gettime = strtotime("+$max day", strtotime($duedate));
                    $datenew = date("d", $gettime);
                    $monthisolir = date("m", $gettime);
                    $yearisolir = date("Y", $gettime);
                    $enddate = $yearisolir . '-' . $monthisolir . '-' . $datenew;
                } else {
                    $enddate = $duedate;
                }
                $amountt = $this->getCustomerMonthlyAmount($row);
                $amount = $amountt + $amountt * ($ppn / 100);
                if ($amount <= 0) {
                    continue;
                }
                array_push(
                    $dataNS,
                    array(
                        'no_services' => $row->no_services,
                        'invoice' => $kode++,
                        'month' => $month,
                        'i_ppn' => $ppn,
                        'amount' => $amount,
                        'year' => $year,
                        'code_unique' => substr(intval(rand()), 0, 3),
                        'status' => 'BELUM BAYAR',
                        'inv_due_date' => $duedate,
                        'date_isolir' => $enddate,
                        'created' => time()
                    )
                );
            }
            $this->bill_m->add_bill_generate($dataNS);

            if ($this->db->affected_rows() > 0) {
                $detail = $this->services_m->getServicesActiveSelected($noservices)->result();
                $data2 = [];
                foreach ($detail as $c => $row) {
                    array_push(
                        $data2,
                        array(
                            // 'invoice_id' => $kode1++,
                            'item_id' => $row->item_id,
                            'category_id' => $row->category_id,
                            'price' => $row->services_price,
                            'qty' => $row->qty,
                            'disc' => $row->disc,
                            'remark' => $row->remark,
                            'total' => $row->total,
                            'd_month' => $month,
                            'd_year' => $year,
                            'd_no_services' => $row->no_services,
                        )
                    );
                }
                $this->bill_m->add_bill_detail($data2);

                $whatsapp = $this->db->get('whatsapp')->row_array();
                if ($whatsapp['is_active'] == 1) {
                    $noservices = $_POST['noservices'];
                    $customer = $this->customer_m->getCustomerSelecteddraf($noservices)->result();

                    $other = $this->db->get('other')->row_array();
                    $company = $this->db->get('company')->row_array();
                    $no = 1;
                    foreach ($customer as $wa) {
                        $range = $no++ * $whatsapp['interval_message'];

                        $jadwall = time() + (1  * $range);
                        $time = date('Y-m-d H:i:s', $jadwall);


                        if ($whatsapp['createinvoice'] == 1) {
                            $cekperiode = $this->bill_m->cekPeriode($wa->no_services, $month, $year)->row_array();
                            if ($cekperiode > 0) {
                                $target = indo_tlp($wa->no_wa);
                                if ($wa->codeunique == 1) {
                                    $codeunique = $cekperiode['code_unique'];
                                } else {
                                    $codeunique = 0;
                                }

                                $nominalWA = indo_currency($cekperiode['amount'] + $codeunique);
                                $search  = array('{name}', '{noservices}', '{email}', '{invoice}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}');
                                $replace = array($wa->name, $wa->no_services,  $wa->email, $cekperiode['invoice'], indo_month($month), $year, indo_month($month) . ' ' . $year, indo_date($cekperiode['inv_due_date']), $nominalWA, $company['company_name'], $company['sub_name'], base_url(), '');
                                $subject = $other['say_wa'];
                                $message = str_replace($search, $replace, $subject);
                                sendmsgschbill($target, $message, $time, $cekperiode['invoice']);
                            }
                        }
                    }
                }
                $this->session->set_flashdata('success-sweet', 'Generate Tagihan yang dipilih berhasil dibuat');
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function detail($invoice, $a = null, $b = null, $c = null, $d = null)
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
        $this->fixamount($invoice);
        $data['title'] = 'Detail Bill';
        $data['invoice'] = $this->bill_m->getEditInvoice($invoice);
        $data['p_item'] = $this->package_m->getPItem()->result();
        $data['bill'] = $this->bill_m->getBill($invoice)->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['other'] = $this->db->get('other')->row_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('backend', 'backend/bill/invoice_detail', $data);
    }
    public function updateduedate()
    {
        $post = $this->input->post(null, TRUE);

        $this->db->set('inv_due_date', $post['due_date']);
        $this->db->where('invoice', $post['invoice']);
        $this->db->update('invoice');
        if ($this->db->affected_rows() > 0) {
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
    public function updatedateisolir()
    {
        $post = $this->input->post(null, TRUE);

        $this->db->set('date_isolir', $post['date_isolir']);
        $this->db->where('invoice', $post['invoice']);
        $this->db->update('invoice');
        if ($this->db->affected_rows() > 0) {
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
    public function updatecreateby()
    {
        $post = $this->input->post(null, TRUE);
        $invoice = $this->db->get_where('invoice', ['invoice' => $post['invoice']])->row_array();
        $income = $this->db->get_where('income', ['invoice_id' => $post['invoice'], 'no_services' => $invoice['no_services']])->row_array();
        $this->db->set('create_by', $post['create_by']);
        $this->db->where('invoice', $post['invoice']);
        $this->db->update('invoice');
        if ($this->db->affected_rows() > 0) {
            if ($income > 0) {
                $this->db->set('create_by', $post['create_by']);
                $this->db->where('income_id', $income['income_id']);
                $this->db->update('income');
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
    public function additem()
    {
        $post = $this->input->post(null, TRUE);
        $item = $this->db->get_where('package_item', ['p_item_id' => $post['item_id']])->row_array();
        $inv = $this->db->get_where('invoice', ['invoice' => $post['invoice']])->row_array();
        $cekitem = $this->bill_m->InvoiceDetail($inv['no_services'], $inv['month'], $inv['year'], $post['item_id'])->row_array();
        if ($cekitem > 0) {
            $this->session->set_flashdata('error-sweet', 'Gagal, Item sudah ada');
        } else {
            $params = [
                'invoice_id' => $post['invoice'],
                'd_month' => $inv['month'],
                'd_year' => $inv['year'],
                'd_no_services' => $inv['no_services'],
                'price' => $item['price'],
                'qty' => $post['qty'],
                'total' => $post['qty'] * $item['price'],
                'item_id' => $post['item_id'],
                'category_id' => $item['category_id'],
            ];

            $this->db->insert('invoice_detail', $params);
            if ($this->db->affected_rows() > 0) {
                $this->fixamount($inv['invoice']);
                $this->session->set_flashdata('success-sweet', 'Daftar paket berhasil ditambahkan');
            }
        }

        echo "<script>window.location='" . site_url('bill/detail/' . $post['invoice']) . "'; </script>";
    }
    public function updateitem()
    {

        $post = $this->input->post(null, TRUE);
        $total = ($post['price'] * $post['qty']) - $post['disc'];
        $inv = $this->db->get_where('invoice', ['invoice' => $post['invoice']])->row_array();
        $this->db->set('qty', $post['qty']);
        $this->db->set('price', $post['price']);
        $this->db->set('total', $total);
        $this->db->set('disc', $post['disc']);
        $this->db->set('remark', $post['remark']);
        $this->db->where('detail_id', $post['detail_id']);
        $this->db->update('invoice_detail');
        if ($this->db->affected_rows() > 0) {
            $this->fixamount($inv['invoice']);
            $message = 'Edit Detail Tagihan ' . $inv['no_services'] . ' Periode ' . indo_month($inv['month']) . ' ' . $inv['year'];
            $this->logs_m->activitylogs('Activity', $message);
            $this->session->set_flashdata('success-sweet', 'Data Item Berhasil diperbaharui');
        }
        redirect('bill/detail/' . $post['invoice']);
    }
    public function deliteminv($id)
    {
        $invoice = $this->db->get_where('invoice_detail', ['detail_id' => $id])->row_array();
        $this->db->where('detail_id', $id);
        $this->db->delete('invoice_detail');
        if ($this->db->affected_rows() > 0) {
            $message = 'Hapus Detail Tagihan ' . $invoice['no_services'] . ' Periode ' . indo_month($invoice['d_month']) . ' ' . $invoice['d_year'];
            $this->logs_m->activitylogs('Activity', $message);
            $this->session->set_flashdata('success-sweet', 'Data Item Berhasil dihapus');
        }
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function editstatus()
    {
        $post = $this->input->post(null, TRUE);
        $inv = $this->db->get_where('invoice', ['invoice' => $post['invoice']])->row_array();
        $this->db->set('status', 'BELUM BAYAR');
        $this->db->where('invoice', $post['invoice']);
        $this->db->update('invoice');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('invoice_id', $post['invoice']);
            $this->db->where('no_services', $inv['no_services']);
            $this->db->delete('income');
            $message = 'Edit status Tagihan ' . $inv['no_services'] . ' Periode ' . indo_month($inv['month']) . ' ' . $inv['year'] . ' Menjadi belum bayar';
            $this->logs_m->activitylogs('Activity', $message);
            $this->session->set_flashdata('success-sweet', 'Data Item Berhasil diperbaharui');
        }
        redirect('bill/detail/' . $post['invoice']);
    }
    public function setppn($invoice, $ppn)
    {
        $this->db->set('i_ppn', $ppn);
        $this->db->where('invoice', $invoice);
        $this->db->update('invoice');
        $this->fixamount($invoice);
        redirect('bill/detail/' . $invoice);
    }
    public function setisolir($date)
    {
        $this->db->set('date_isolir', $date);
        $this->db->where('status', 'BELUM BAYAR');
        $this->db->update('invoice');
        redirect('bill');
    }
    public function openisolir()
    {
        $allisolir = $this->customer_m->getrecheckagainisolir()->result();
        foreach ($allisolir as $data) {

            openisolir($data->no_services, $data->router);
        }
        redirect('bill');
    }

    public function donation()
    {
        $data['title'] = 'Donasi';
        $data['customer'] = $this->customer_m->getCustomer()->result();
        $data['bank'] = $this->setting_m->getBank()->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('backend', 'backend/bill/donation', $data);
    }
    public function view_donation()
    {
        $month = $this->input->post('month');
        $year = $this->input->post('year');
        if (isset($_POST['cek_bill'])) {
            $data['bill'] = $this->bill_m->getInvoiceThisMonth($month, $year)->result();
            $this->load->view('backend/bill/tampil_donation', $data);
        } else {
            echo "Not Found";
        }
    }
    public function delete()
    {
        $role_id = $this->session->userdata('role_id');
        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();
        if ($role_id == 3 && $role['del_bill'] == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect('dashboard');
        }
        if ($this->session->userdata('role_id') == 2) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect('member/dashboard');
        }
        $post = $this->input->post(null, TRUE);
        $invoice = $this->input->post('invoice');
        $cekConfir = $this->db->get_where('confirm_payment', ['invoice_id' => $invoice])->row_array();
        $inv = $this->db->get_where('invoice', ['invoice' => $invoice])->row_array();
        $customer = $this->db->get_where('customer', ['no_services' => $inv['no_services']])->row_array();
        if ($cekConfir > 0) {
            $this->session->set_flashdata('error-sweet', 'Tagihan tidak bisa dihapus dikarenakan masih ada di konfirmasi pembayaran');
        } else {
            $this->bill_m->delete($invoice);

            if ($this->db->affected_rows() > 0) {
                $this->bill_m->deleteDetailInvoice($invoice);
                $this->bill_m->deleteDetailBill($post);
                if ($post['delincome'] == 1) {
                    $this->db->where('invoice_id', $invoice);
                    $this->db->where('no_services', $inv['no_services']);
                    $this->db->delete('income');
                }
                $target_file = './assets/images/qrcode/' . $invoice . '.png';
                unlink($target_file);
                $message = 'Hapus Tagihan Invoice ' . $invoice . ' Periode ' . indo_month($inv['month']) . ' ' . $inv['year'] . ' Pelanggan ' . $customer['name'] . ' ' . $inv['no_services'];
                $this->logs_m->activitylogs('Activity', $message);

                $this->session->set_flashdata('success-sweet', 'Data Tagihan berhasil dihapus');
            }
        }
        redirect($_SERVER['HTTP_REFERER']);
    }



    public function printinvoice($invoice, $a = null, $b = null, $c = null, $d = null)
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
        $this->fixamount($invoice);
        $inv = $this->db->get_where('invoice', ['invoice' => $invoice])->row_array();
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
        $data['bank'] = $this->setting_m->getBank()->result();
        $data['p_item'] = $this->package_m->getPItem()->result();
        $data['other'] = $this->db->get('other')->row_array();
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
        $this->load->view('backend/bill/invoice', $data);
    }
    public function printinvoicethermal($invoice, $a = null, $b = null, $c = null, $d = null)
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
        $this->fixamount($invoice);
        $inv = $this->db->get_where('invoice', ['invoice' => $invoice])->row_array();
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

        // $this->load->view('backend/bill/invoiceThermal', $data);



        $data['title'] = 'Invoice';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['invoice'] = $this->bill_m->getBill($invoice);
        $data['invoice_detail'] = $this->bill_m->getDetailBill($invoice);
        $data['bill'] = $this->bill_m->getBill($invoice)->row_array();

        $data['p_item'] = $this->package_m->getPItem()->result();
        $data['other'] = $this->db->get('other')->row_array();
        $filename = 'INVOICE-' . $invoice . '-' . $no_services;
        $other = $this->db->get('other')->row_array();
        if ($other['inv_thermal'] == 1) {
            $this->load->library('Pdf');

            $this->pdf->load_view($filename, array(0, 0, 249, 660), 'potrait', 'backend/bill/invoiceThermal', $data);
        } else {
            $this->load->view('backend/bill/invoiceThermal', $data);
        }
    }
    public function printinvoiceselected()
    {
        $invoice = $_POST['invoice'];
        if ($invoice == null) {
            $this->session->set_flashdata('error-sweet', 'Tagihan belum dipilih');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $data['title'] = 'Invoice';
            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
            $data['company'] = $this->db->get('company')->row_array();
            $data['invoice'] = $this->bill_m->getBill($invoice)->result();
            $bill = $this->bill_m->getInvoiceSelected($invoice)->result();
            foreach ($bill as $fix) {
                $this->fixamount($fix->invoice);
                $billdetail = $this->bill_m->fixbilldetail($fix->no_services, $fix->month, $fix->year)->result();
                foreach ($billdetail as $unit) {
                    $item =  $this->bill_m->fixbilldetail($fix->no_services, $fix->month, $fix->year, $unit->item_id)->num_rows();
                    if ($item > 1) {
                        $lasitem =  $this->bill_m->lastitembilldetail($fix->no_services, $fix->month, $fix->year, $unit->item_id)->row_array();
                        $this->db->where('detail_id', $lasitem['detail_id']);
                        $this->db->delete('invoice_detail');
                    }
                }
            }


            $data['bill'] = $this->bill_m->getInvoiceSelected($invoice)->result();
            $data['other'] = $this->db->get('other')->row_array();
            $data['bank'] = $this->setting_m->getBank()->result();
            $data['p_item'] = $this->package_m->getPItem()->result();
            $this->load->view('backend/bill/invoiceselected', $data);
        }
    }
    public function printinvoiceselectedthermal()
    {
        $invoice = $_POST['invoice'];
        if ($invoice == null) {
            $this->session->set_flashdata('error-sweet', 'Tagihan belum dipilih');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $data['title'] = 'Invoice';
            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
            $data['company'] = $this->db->get('company')->row_array();
            $data['invoice'] = $this->bill_m->getBill($invoice)->result();
            $bill = $this->bill_m->getInvoiceSelected($invoice)->result();
            foreach ($bill as $fix) {
                $this->fixamount($fix->invoice);
                $billdetail = $this->bill_m->fixbilldetail($fix->no_services, $fix->month, $fix->year)->result();
                foreach ($billdetail as $unit) {
                    $item =  $this->bill_m->fixbilldetail($fix->no_services, $fix->month, $fix->year, $unit->item_id)->num_rows();
                    if ($item > 1) {
                        $lasitem =  $this->bill_m->lastitembilldetail($fix->no_services, $fix->month, $fix->year, $unit->item_id)->row_array();
                        $this->db->where('detail_id', $lasitem['detail_id']);
                        $this->db->delete('invoice_detail');
                    }
                }
            }
            $data['bill'] = $this->bill_m->getInvoiceSelected($invoice)->result();
            $data['other'] = $this->db->get('other')->row_array();
            $data['bank'] = $this->setting_m->getBank()->result();
            $data['p_item'] = $this->package_m->getPItem()->result();
            $this->load->view('backend/bill/invoiceselectedthermal', $data);
        }
    }
    public function printinvoiceselectedsmall()
    {
        $invoice = $_POST['invoice'];
        if ($invoice == null) {
            $this->session->set_flashdata('error-sweet', 'Tagihan belum dipilih');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $data['title'] = 'Invoice';
            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
            $data['company'] = $this->db->get('company')->row_array();
            $data['invoice'] = $this->bill_m->getBill($invoice)->result();
            $bill = $this->bill_m->getInvoiceSelected($invoice)->result();
            foreach ($bill as $fix) {
                $this->fixamount($fix->invoice);
                $billdetail = $this->bill_m->fixbilldetail($fix->no_services, $fix->month, $fix->year)->result();
                foreach ($billdetail as $unit) {
                    $item =  $this->bill_m->fixbilldetail($fix->no_services, $fix->month, $fix->year, $unit->item_id)->num_rows();
                    if ($item > 1) {
                        $lasitem =  $this->bill_m->lastitembilldetail($fix->no_services, $fix->month, $fix->year, $unit->item_id)->row_array();
                        $this->db->where('detail_id', $lasitem['detail_id']);
                        $this->db->delete('invoice_detail');
                    }
                }
            }
            $data['bill'] = $this->bill_m->getInvoiceSelected($invoice)->result();
            $data['other'] = $this->db->get('other')->row_array();
            $data['bank'] = $this->setting_m->getBank()->result();
            $data['p_item'] = $this->package_m->getPItem()->result();
            $this->load->view('backend/bill/invoiceselectedsmall', $data);
        }
    }
    public function printinvoiceunpaid()
    {
        $data['title'] = 'Invoice';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['bill'] = $this->bill_m->getInvoiceUnpaid()->result();
        $data['bank'] = $this->setting_m->getBank()->result();
        $data['p_item'] = $this->package_m->getPItem()->result();
        $this->load->view('backend/bill/invoiceselected', $data);
    }
    public function printinvoiceunpaidthermal()
    {
        $data['title'] = 'Invoice';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['bill'] = $this->bill_m->getInvoiceUnpaid()->result();
        $data['bank'] = $this->setting_m->getBank()->result();
        $data['p_item'] = $this->package_m->getPItem()->result();
        $this->load->view('backend/bill/invoiceselectedthermal', $data);
    }
    public function printinvoicepaid()
    {
        $data['title'] = 'Invoice';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['bill'] = $this->bill_m->getInvoicePaid()->result();
        $data['bank'] = $this->setting_m->getBank()->result();
        $data['p_item'] = $this->package_m->getPItem()->result();
        $this->load->view('backend/bill/invoiceselected', $data);
    }
    public function printinvoicepaidthermal()
    {
        $data['title'] = 'Invoice';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['bill'] = $this->bill_m->getInvoicePaid()->result();
        $data['bank'] = $this->setting_m->getBank()->result();
        $data['p_item'] = $this->package_m->getPItem()->result();
        $this->load->view('backend/bill/invoiceselectedthermal', $data);
    }
    public function debt()
    {
        $menu = $this->_menu();
        $role = $this->_role();
        $cover = $this->_coverage();
        if ($menu['role_id'] != 1 && $menu['bill_debt'] == 0) {
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
        $data['title'] = 'Tunggakan';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/bill/debt', $data);
    }
    public function getdebt()
    {
        $result = $this->bill_m->getUnpaidInv();
        $data = [];
        $no = $_POST['start'];

        foreach ($result as $result) {
            $cekbill = $this->bill_m->getunpaid($result->no_services)->result();
            if (count($cekbill) > 0) {
                $row = [
                    'no' => ++$no,
                    'name' => $result->name,
                    'no_services' => $result->no_services,
                    'countbill' => count($cekbill) . ' Bulan',
                    'action' => '<a target="blank" href="' . site_url('bill/printdebt/' . $result->no_services) . '" ><i class="fas fa-print" style="font-size:20px; color:gray"></i></a>',
                ];
                $data[] = $row;
            } else {
                # code...
            }
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->bill_m->count_all_data_unpaid(),
            "recordsFiltered" => $this->bill_m->count_filtered_data_unpaid(),
            "data" => $data,
        );

        echo json_encode($output);
    }
    public function printdebt($no_services)
    {
        $data['title'] = 'Akumulasi Tagihan ' . $no_services;
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['customer'] = $this->customer_m->getNSCustomer($no_services)->row_array();
        $data['bill'] = $this->bill_m->getDebt($no_services)->num_rows();
        $data['bank'] = $this->setting_m->getBank()->result();

        $this->load->view('backend/bill/invoicedebt', $data);
    }
    public function billpaid()
    {
        $post = $this->input->post(null, TRUE);
        $invoice = $this->input->post('invoice');
        $invoicedetail =  $this->db->get_where('invoice', ['invoice' => $post['invoice']])->row_array();
        $cekConfirm = $this->db->get_where('confirm_payment', ['invoice_id' => $post['invoice']])->row_array();
        $customer = $this->db->get_where('customer', ['no_services' => $post['no_services']])->row_array();
        $this->bill_m->payment($post);

        if ($this->db->affected_rows() > 0) {
            $this->income_m->addPaymentFast($post);
            if ($cekConfirm > 0) {
                $this->bill_m->UpdateConfirmPayment($post);
            }
            $rt = $this->db->get_where('router', ['id' => 1])->row_array();
            if ($rt['is_active'] == 1) {
                if ($customer['auto_isolir'] == 1) {
                    if ($customer['user_mikrotik'] != '') {
                        openisolir($customer['no_services'], $customer['router']);
                    }
                }
            }


            $whatsapp = $this->db->get('whatsapp')->row_array();
            if ($whatsapp['is_active'] == 1) {
                $other = $this->db->get('other')->row_array();
                $company = $this->db->get('company')->row_array();
                // echo  $timeex;
                if ($whatsapp['paymentinvoice'] == 1) {
                    $other = $this->db->get('other')->row_array();
                    $company = $this->db->get('company')->row_array();
                    $getuser = $this->db->get_where('user', ['id' => $post['create_by']])->row_array();
                    $target = indo_tlp($customer['no_wa']);
                    $nominalWA = indo_currency($post['nominal']);
                    $search  = array('{name}', '{noservices}', '{email}', '{invoice}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}', '{invoice}', '{receiver}');
                    $replace = array($customer['name'], $customer['no_services'], $customer['email'], $invoice, $post['month'], $post['year'], $post['month'] . ' ' . $post['year'], $invoicedetail['inv_due_date'], $nominalWA, $company['company_name'], $company['sub_name'], base_url(), '', $invoice, $getuser['name']);
                    $subject = $other['thanks_wa'];
                    $message = str_replace($search, $replace, $subject);
                    sendmsgpaid($target, $message, $invoice);
                }
            }
            $this->session->set_flashdata('success-payment', '<h3>Tagihan berhasil terbayarkan</h3> <a class="btn btn-success" target="blank" href="' . site_url('bill/printinvoice/' . $invoice) . '">Cetak A4</a>&nbsp; <a class="btn btn-success" target="blank" href="' . site_url('bill/printinvoicethermal/' . $invoice) . '">Cetak Thermal</a>&nbsp; <a class="btn btn-success" target="blank" href="' . site_url('bill/printinvoicedotmatrix/' . $invoice) . '">Cetak Dot Matrix</a>');
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
                'remark' => 'Bayar Tagihan' . ' ' . $customer['name'] . ' ' . $customer['no_services'] . ' Periode ' . indo_month($invoicedetail['month']) . ' ' . $invoicedetail['year'] . ' dari' . ' ' . $this->agent->platform() . ' ' . $this->input->ip_address() . ' ' . $agent,
            ];

            $this->db->insert('logs', $params);
        }
        redirect('bill/unpaid');
    }
    public function delbillyear($year)
    {
        if ($this->session->userdata('email') == 'ginginabdulgoni@gmail.com') {
            // dell invoice
            $this->db->where('year', $year);
            $this->db->delete('invoice');
            // dell detail invoice
            $this->db->where('d_year', $year);
            $this->db->delete('invoice_detail');
        }
        redirect('bill');
    }
    // PAYMENT
    public function payment()
    {
        $post = $this->input->post(null, TRUE);
        $invoice = $this->input->post('invoice');
        $invoicedetail = $this->db->get_where('invoice', ['invoice' => $post['invoice']])->row_array();
        $cekConfirm = $this->db->get_where('confirm_payment', ['invoice_id' => $post['invoice']])->row_array();
        if ($cekConfirm > 0) {
            $this->bill_m->UpdateConfirmPayment($post);
        }
        $this->bill_m->payment($post);
        if ($this->db->affected_rows() > 0) {
            $this->income_m->addPayment($post);
            $customer = $this->db->get_where('customer', ['no_services' => $post['no_services']])->row_array();
            $rt = $this->db->get_where('router', ['id' => 1])->row_array();
            if ($rt['is_active'] == 1) {
                if ($customer['auto_isolir'] == 1) {
                    if ($customer['user_mikrotik'] != '') {
                        openisolir($customer['no_services'], $customer['router']);
                    }
                }
            }


            $whatsapp = $this->db->get('whatsapp')->row_array();
            if ($whatsapp['is_active'] == 1) {
                $other = $this->db->get('other')->row_array();
                $company = $this->db->get('company')->row_array();


                if ($whatsapp['paymentinvoice'] == 1) {
                    $other = $this->db->get('other')->row_array();
                    $company = $this->db->get('company')->row_array();
                    $getuser = $this->db->get_where('user', ['id' => $post['create_by']])->row_array();
                    $target = indo_tlp($customer['no_wa']);
                    $nominalWA = indo_currency($post['nominal']);
                    $search  = array('{name}', '{noservices}', '{email}', '{invoice}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}', '{invoice}', '{receiver}');
                    $replace = array($customer['name'], $customer['no_services'], $customer['email'], $invoice, $post['month'], $post['year'], $post['month'] . ' ' . $post['year'], $invoicedetail['inv_due_date'], $nominalWA, $company['company_name'], $company['sub_name'], base_url(), '', $invoice, $getuser['name']);
                    $subject = $other['thanks_wa'];
                    $message = str_replace($search, $replace, $subject);
                    sendmsgpaid($target, $message, $invoice);
                }
            }
            $this->session->set_flashdata('success-payment', '<h3>Tagihan berhasil terbayarkan</h3> <a class="btn btn-success" target="blank" href="' . site_url('bill/printinvoice/' . $invoice) . '">Cetak A4</a>&nbsp; <a class="btn btn-success" target="blank" href="' . site_url('bill/printinvoicethermal/' . $invoice) . '">Cetak Thermal</a>&nbsp; <a class="btn btn-success" target="blank" href="' . site_url('bill/printinvoicedotmatrix/' . $invoice) . '">Cetak Dot Matrix</a>');
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
                'remark' => 'Bayar Tagihan' . ' ' . $customer['name'] . ' ' . $customer['no_services'] . ' Periode ' . indo_month($invoicedetail['month']) . ' ' . $invoicedetail['year'] . ' dari' . ' ' . $this->agent->platform() . ' ' . $this->input->ip_address() . ' ' . $agent,
            ];
            $this->db->insert('logs', $params);
        }
        echo "<script>window.location='" . site_url('bill/detail/' . $invoice) . "'; </script>";
    }
    public function history()
    {
        $data['title'] = 'Bill History';
        $menu = $this->_menu();
        $role = $this->_role();
        $cover = $this->_coverage();
        if ($menu['role_id'] != 1 && $menu['bill_history'] == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $data['customer'] = $this->customer_m->getCustomerAll($cover)->result();
        } else {
            $data['customer'] = $this->customer_m->getCustomerAll()->result();
        }
        $data['bank'] = $this->setting_m->getBank()->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('backend', 'backend/bill/history', $data);
    }
    public function view_history()
    {
        $no_services = $this->input->post('no_services');
        $year = $this->input->post('year');
        if (isset($_POST['cek_bill'])) {
            $data['bill'] = $this->bill_m->getInvoiceHistory($no_services, $year)->result();
            $data['customer'] = $this->customer_m->getCustomer()->result();
            $data['no_services'] = $this->input->post('no_services');
            $data['year'] = $this->input->post('year');
            if ($no_services != null) {
                $this->load->view('backend/bill/tampil_history', $data);
            } else {
                $this->load->view('backend/bill/historyall', $data);
            }
        } else {
            echo "Not Found";
        }
    }
    public function confirmPayment()
    {
        $config['upload_path']          = './assets/images/confirm';
        $config['allowed_types']        = 'gif|jpg|png|jpeg';
        $config['max_size']             = 2048; // 2 Mb
        $config['file_name']             = 'confirm-' . date('ymd') . '-' . substr(md5(rand()), 0, 10);
        $this->load->library('upload', $config);
        $post = $this->input->post(null, TRUE);
        if (@FILES['picture']['name'] != null) {
            if ($this->upload->do_upload('picture')) {
                $post['picture'] =  $this->upload->data('file_name');
                $this->bill_m->confirmPayment($post);
                $bot = $this->db->get('bot_telegram')->row_array();
                $tokens = $bot['token']; // token bot
                $owner = $bot['id_telegram_owner'];

                $sendmessage = [
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [
                                ['text' => '✅ Konfirmasi', 'url' => base_url('confirmdetail/' . $post['no_invoice'])],

                            ]
                        ]
                    ]),
                    'resize_keyboard' => true,
                    'parse_mode' => 'html',
                    'text' => "<b>KONFIRMASI PEMBAYARAN</b>\nNama : $post[name]\nNo Layanan : $post[no_services]\nTagihan : $post[nominal]\nPeriode : $post[periode]\nTanggal Bayar : $post[date_payment]\nMetode Pembayaran : $post[metode_payment]\n",
                    'chat_id' => $owner
                ];

                file_get_contents("https://api.telegram.org/bot$tokens/sendMessage?" . http_build_query($sendmessage));
                if ($this->db->affected_rows() > 0) {
                    $this->session->set_flashdata('success-sweet', 'Konfirmasi pembayaran sudah terkirim, mohon tunggu untuk di verifikasi');
                }
                echo "<script>window.location='" . site_url('member/history') . "'; </script>";
            } else {
                $error = $this->upload->display_errors();
                $this->session->set_flashdata('error-sweet', $error);
                echo "<script>window.location='" . base_url('member/history') . "'; </script>";
            }
        }
    }

    public function confirm()
    {
        $data['title'] = 'Konfirmasi Pembayaran';
        $data['confirm'] = $this->bill_m->getConfirm()->result();
        $data['company'] = $this->db->get('company')->row_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('backend', 'backend/bill/confirm', $data);
    }


    public function confirmdetail($invoice, $a = null, $b = null, $c = null, $d = null)
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
        $data['title'] = 'Detail Konfirmasi Pembayaran';
        $cekInvoice = $this->db->get_where('invoice', ['invoice' => $invoice])->row_array();
        if ($cekInvoice != null) {
            $data['invoice'] = $this->bill_m->getEditInvoice($invoice);
            $data['p_item'] = $this->package_m->getPItem()->result();
            $data['bill'] = $this->bill_m->getBill($invoice)->row_array();
            $data['company'] = $this->db->get('company')->row_array();
            $data['other'] = $this->db->get('other')->row_array();
            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
            $this->template->load('backend', 'backend/bill/confirm-detail', $data);
        } else {
            $this->session->set_flashdata('error-sweet', 'Invoice tidak ditemukan');
            echo "<script>window.location='" . base_url('confirm') . "'; </script>";
        }
    }
    public function confirmUpdate()
    {
        $post = $this->input->post(null, TRUE);
        $invoice = $this->input->post('invoice');
        $invoicedetail =  $this->db->get_where('invoice', ['invoice' => $post['invoice']])->row_array();
        $customer = $this->db->get_where('customer', ['no_services' => $post['no_services']])->row_array();
        $rt = $this->db->get_where('router', ['id' => $customer['router']])->row_array();
        $this->bill_m->payment($post);

        if ($this->db->affected_rows() > 0) {
            $this->income_m->addPayment($post);
            $this->bill_m->UpdateConfirm($post);
            $rt = $this->db->get_where('router', ['id' => 1])->row_array();
            if ($rt['is_active'] == 1) {
                if ($customer['auto_isolir'] == 1) {
                    if ($customer['user_mikrotik'] != '') {
                        openisolir($customer['no_services'], $customer['router']);
                    }
                }
            }

            $whatsapp = $this->db->get('whatsapp')->row_array();
            if ($whatsapp['is_active'] == 1) {
                $other = $this->db->get('other')->row_array();
                $company = $this->db->get('company')->row_array();

                if ($whatsapp['paymentinvoice'] == 1) {
                    $other = $this->db->get('other')->row_array();
                    $company = $this->db->get('company')->row_array();

                    $target = indo_tlp($customer['no_wa']);
                    $getuser = $this->db->get_where('user', ['id' => $invoicedetail['create_by']])->row_array();
                    $nominalWA = indo_currency($post['nominal']);
                    $search  = array('{name}', '{noservices}', '{email}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}', '{receiver}');
                    $replace = array($customer['name'], $customer['no_services'], $customer['email'], indo_month($post['month']), $post['year'], $post['month'] . ' ' . $post['year'], $customer['due_date'], $nominalWA, $company['company_name'], $company['sub_name'], base_url(), '', $getuser['name']);
                    $subject = $other['thanks_wa'];
                    $message = str_replace($search, $replace, $subject);


                    sendmsgpaid($target, $message, $invoice);
                }
            }
            $this->session->set_flashdata('success-payment', '<h3>Pembayaran berhasil diverifikasi</h3> <a class="btn btn-success" target="blank" href="' . site_url('bill/printinvoice/' . $invoice) . '">Cetak A4</a>&nbsp; <a class="btn btn-success" target="blank" href="' . site_url('bill/printinvoicethermal/' . $invoice) . '">Cetak Thermal</a>&nbsp; <a class="btn btn-success" target="blank" href="' . site_url('bill/printinvoicedotmatrix/' . $invoice) . '">Cetak Dot Matrix</a>');
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
                'remark' => 'Verifikasi Pemabayaran ' . ' ' . $customer['name'] . ' ' . $customer['no_services'] . ' Periode ' . indo_month($invoicedetail['month']) . ' ' . $invoicedetail['year'] . ' dari' . ' ' . $this->agent->platform() . ' ' . $this->input->ip_address() . ' ' . $agent,
            ];
            $this->db->insert('logs', $params);
        }
        echo "<script>window.location='" . site_url('confirm') . "'; </script>";
    }
    public function deleteconfirm()
    {
        $confirm_id = $this->input->post('confirm_id');
        $confirm = $this->db->get_where('confirm_payment', ['confirm_id' => $confirm_id])->row_array();
        $target_file = './assets/images/confirm/' . $confirm['picture'];
        $this->bill_m->deleteconfirm($confirm_id);
        unlink($target_file);
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success-sweet', 'Data konfirmasi berhasil dihapus');
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    // SMS GATEWAY

    // KIRIM EMAIL
    private function _sendEmail($type)
    {
        $invoice_id =  $this->input->post('invoice');
        $to_email = $this->input->post('to_email');
        $no_services = $this->input->post('no_services');
        $email_customer = $this->input->post('email_customer');
        $name = $this->input->post('name');
        $agen = $this->input->post('agen');
        $email_agen = $this->input->post('email_agen');
        $date_payment = $this->input->post('date_payment');
        $periode = $this->input->post('periode');
        $total = $this->input->post('nominal');
        $email = $this->db->get('email')->row_array();
        $company = $this->db->get('company')->row_array();
        $config = [
            'protocol'  => $email['protocol'],
            'smtp_host' => $email['host'],
            'smtp_user' => $email['email'], // isi Alamat email
            'smtp_pass' => $email['password'], // Isi Password email
            'smtp_port' => $email['port'],
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'newline'   => "\r\n"
        ];
        $this->email->initialize($config);
        $this->load->library('email', $config);
        $this->email->from($email['email'], $email['name']); // isi Alamat email dan nama pengirim
        if ($type == 'verify-payment') {
            $this->email->to($to_email);
            $this->email->subject('Verifikasi Pembayaran');
            $this->email->message('Yth. Pelanggan <b>' . $company['company_name'] . '</b> <br> berikut kami sampaikan status transaksi <b>SUKSES</b> <br>
                      No invoice : <b>' . $invoice_id . ' </b> <br>
                      No layanan : <b>' . $no_services . '</b> <br>
                      Nama Pelanggan : ' . $name . ' <br>
                      Periode : <b>' . $periode . '</b> <br>
                      Total Tagihan : <b>' . $total . '</b> <br>
                      Terimakasih telah menggunakan layanan <b>' . $company['company_name'] . '</b> <br><br>
                      Salam,');
        } elseif ($type == 'payment-success') {
            $this->email->to([$to_email, $email_agen, $email_customer]);
            $this->email->subject('Informasi Pembayaran');
            $this->email->message('<b>' . $company['company_name'] . '</b> <br> berikut kami sampaikan status transaksi <b>SUKSES</b> <br>
                      No invoice : <b>' . $invoice_id . ' </b> <br>
                      No layanan : <b>' . $no_services . '</b> <br>
                      Nama Pelanggan : ' . $name . ' <br>
                      Periode : <b>' . $periode . '</b> <br>
                      Total Tagihan : <b>' . indo_currency($total) . '</b> <br>
                      Diterima Oleh : <b>' . $agen . ' | ' . $email_agen . '</b> <br>
                      Tanggal : <b>' . $date_payment . '</b> <br>
                      <br>
                      <a href="' . base_url() . 'bill/printinvoice/' . $invoice_id . '">Cetak A4</a>
                      <br>
                      <a href="' . base_url() . 'bill/printinvoicethermal/' . $invoice_id . '">Cetak Thermal</a>
                      <br><br>
                      <b>' . $company['company_name'] . '</b> <br><br>
                      Salam,');
        }
        if ($this->email->send()) {
            return true;
        } else {
            echo $this->email->print_debugger();
            die;
        }
    }


    // SERVER SIDE
    public function getDataInvoice()
    {
        $result = $this->bill_m->getDataInv();
        $data = [];
        $no = $_POST['start'];
        foreach ($result as $result) {
            $row = array();
            $row[] = ++$no;
            $row[] = '<input type=' . 'checkbox' . ' class=' . 'check-item' . ' id="ceklis" name=' . 'invoice[]' . ' value=' . $result->invoice . '>';

            $invoice = $result->invoice;


            $other = $this->db->get('other')->row_array();
            $company = $this->db->get('company')->row_array();
            if ($result->codeunique > 0) {
                $codeunique = $result->code_unique;
            } else {
                $codeunique = 0;
            }
            $row[] =  $result->name;
            $row[] =  $result->no_services . '<br>' . $result->invoice;

            if ($result->status == 'SUDAH BAYAR') {
                $row[] = indo_month($result->month) . ' ' . $result->year;
            } else {
                $row[] = indo_month($result->month) . ' ' . $result->year . '<br>Jatuh Tempo : ' . indo_date($result->inv_due_date);
            }

            $row[] = indo_currency($result->amount - $result->disc_coupon + $codeunique);
            $nominal = indo_currency($result->amount - $result->disc_coupon + $codeunique);
            $nominalbayar = $result->amount - $result->disc_coupon;
            if ($result->status == 'BELUM BAYAR') {
                $row[] = '<span class="badge badge-danger">' . $result->status . '</span>'  . '<br>Pesan Terikirim : ' . $result->send_bill . '<br>Sebelum Jatuh Tempo : ' . $result->send_before_due . '<br>Jatuh Tempo : ' . $result->send_due;
            } else {
                $row[] = '<span class="badge badge-success">' . $result->status . '</span> <br>Tgl Bayar : ' . date('Y-m-d H:i:s', $result->date_payment) . '<br>Pesan Terikirim : ' . $result->send_paid;
            }
            $coverage = $this->db->get_where('coverage', ['coverage_id' => $result->coverage])->row_array();
            if ($coverage > 0) {
                $row[] = 'Area : ' . $coverage['c_name'] . '<br> Alamat : ' . $result->address;
            } else {
                $row[] = 'Area : Belum Terdafar <br> Alamat : ' . $result->address;
            }
            $getuser = $this->db->get_where('user', ['id' => $result->create_by])->row_array();
            $search  = array('{name}', '{noservices}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}', '{slogan}', '{link}', '{e}', '{receiver}');
            $replace = array($result->name, $result->no_services, indo_month($result->month), $result->year, indo_month($result->month) . ' ' . $result->year, indo_date($result->inv_due_date), $nominal, $company['company_name'], $company['sub_name'], base_url(), '%0A', $getuser['name']);
            $subject = $other['say_wa'];
            $subjectthanks = $other['thanks_wa'];
            $message = str_replace($search, $replace, $subject);
            $messagethanks = str_replace($search, $replace, $subjectthanks);
            $linkWA = "https://api.whatsapp.com/send?phone=";
            $whatsapp = $this->db->get('whatsapp')->row_array();

            if ($whatsapp['is_active'] == 1) {
                if ($result->status == 'SUDAH BAYAR') {
                    $row[] = '<a href="#" id="printinvoice" data-printinvoice="' . $invoice . '"   title="Cetak Invoice"  style="color:green" data-toggle="modal" data-target="#PrintModalPaid"><i class="fa fa-print"></i></a> <a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
                    <a href="#" id="hapusmodalbayar" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModalBayar" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>
                    <a href="' . site_url('whatsapp/sendbillpaid/' . $result->invoice) . '"><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a> ';
                } else {
                    $row[] = '<a href="#" id="bayar" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . indo_month($result->month) . '" data-invoice="' . $result->invoice . '" data-name="' . $result->name . '"  data-email_customer="' . $result->email . '"  data-periode="' . indo_month($result->month) . ' ' . $result->year . '" data-nominal="' . $nominalbayar . '"data-shownominal="' . indo_currency($result->amount - $result->disc_coupon) . '" data-toggle="modal" data-target="#ModalBayar"><i class="fas fa-credit-card"></i></a> 
                    <a href="#" id="printinvoice" data-printinvoice="' . $invoice . '"  title="Cetak Invoice" style="color:red" data-toggle="modal" data-target="#PrintModalUnpaid"><i class="fa fa-print"></i></a> <a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
                    <a href="' . site_url('whatsapp/sendbillunpaid/' . $result->invoice) . '" ><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a>
                    <a href="#" id="hapusmodal" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModal" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>';
                }
            } else {
                if ($result->status == 'SUDAH BAYAR') {
                    $row[] = '<a href="#" id="printinvoice" data-printinvoice="' . $invoice . '"   title="Cetak Invoice"  style="color:green" data-toggle="modal" data-target="#PrintModalPaid"><i class="fa fa-print"></i></a> <a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
                    <a href="#" id="hapusmodalbayar" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModalBayar" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>
                    <a href="' . $linkWA . indo_tlp($result->no_wa) . '&text=' . $messagethanks . '" target="blank"><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a> ';
                } else {
                    $row[] = '<a href="#" id="bayar" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . indo_month($result->month) . '" data-invoice="' . $result->invoice . '" data-name="' . $result->name . '"  data-email_customer="' . $result->email . '"  data-periode="' . indo_month($result->month) . ' ' . $result->year . '" data-nominal="' . $nominalbayar . '"data-shownominal="' . indo_currency($result->amount - $result->disc_coupon) . '" data-toggle="modal" data-target="#ModalBayar"><i class="fas fa-credit-card"></i></a>
                    <a href="#" id="printinvoice" data-printinvoice="' . $invoice . '"  title="Cetak Invoice" style="color:red" data-toggle="modal" data-target="#PrintModalUnpaid"><i class="fa fa-print"></i></a>  <a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
                    <a href="' . $linkWA . indo_tlp($result->no_wa) . '&text=' . $message   . '" target="blank"><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a>
                    <a href="#" id="hapusmodal" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModal" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>';
                }
            };

            $data[] = $row;
        }

        $role = $this->_role();
        $cover = $this->_coverage();
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $coverage = $this->bill_m->getallinvoicecoverage($cover)->num_rows();
        } else {
            $coverage =  $this->bill_m->getallinvoicecoverage()->num_rows();
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $coverage,
            "recordsFiltered" => $this->bill_m->count_filtered_data(),
            "data" => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
    public function getUnpaidInvoice()
    {
        $this->prepareUnpaidInvoiceIpSearch();
        $result = $this->bill_m->getUnpaidInv();
        $ipByCustomer = $this->getInvoiceCustomerIpMap($result);
        $data = [];
        $no = $_POST['start'];
        foreach ($result as $result) {
            $row = array();
            $row[] = ++$no;
            $row[] = '<input type=' . 'checkbox' . ' class=' . 'check-item' . ' id="ceklis" name=' . 'invoice[]' . ' value=' . $result->invoice . '>';

            $invoice = $result->invoice;


            $other = $this->db->get('other')->row_array();
            $company = $this->db->get('company')->row_array();
            if ($result->codeunique > 0) {
                $codeunique = $result->code_unique;
            } else {
                $codeunique = 0;
            }
            $row[] =  $result->name;

            $row[] =  $result->no_services . '<br>' . $result->invoice;

            $row[] = indo_month($result->month) . ' ' . $result->year;
            $row[] = indo_date($result->inv_due_date) . '<br>Tgl Isolir : ' . indo_date($result->date_isolir);
            $row[] = indo_currency($result->amount - $result->disc_coupon + $codeunique);
            $nominal = indo_currency($result->amount - $result->disc_coupon + $codeunique);
            $nominalbayar = $result->amount - $result->disc_coupon;
            $row[] = '<span class="badge badge-danger">' . $result->status . '</span>'  . '<br>Pesan Terikirim : ' . $result->send_bill . '<br>Sebelum Jatuh Tempo : ' . $result->send_before_due . '<br>Jatuh Tempo : ' . $result->send_due;
            $coverage = $this->db->get_where('coverage', ['coverage_id' => $result->coverage])->row_array();
            if ($coverage > 0) {
                $row[] = 'Area : ' . $coverage['c_name'] . '<br> Alamat : ' . $result->address;
            } else {
                $row[] = 'Area : Belum Terdafar <br> Alamat : ' . $result->address;
            }
            $row[] = html_escape($result->cust_description);
            $row[] = isset($ipByCustomer[$result->customer_id]) ? $ipByCustomer[$result->customer_id] : '-';

            $search  = array('{name}', '{noservices}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}');
            $replace = array($result->name, $result->no_services, indo_month($result->month), $result->year, indo_month($result->month) . ' ' . $result->year, indo_date($result->inv_due_date), $nominal, $company['company_name'], $company['sub_name'], base_url(), '%0A');
            $subject = $other['say_wa'];

            $message = str_replace($search, $replace, $subject);

            $linkWA = "https://api.whatsapp.com/send?phone=";
            $whatsapp = $this->db->get('whatsapp')->row_array();

            if ($whatsapp['is_active'] == 1) {

                $row[] = '<a href="#" id="bayar" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . indo_month($result->month) . '" data-invoice="' . $result->invoice . '" data-name="' . $result->name . '"  data-email_customer="' . $result->email . '"  data-periode="' . indo_month($result->month) . ' ' . $result->year . '" data-nominal="' . $nominalbayar . '"data-shownominal="' . indo_currency($result->amount - $result->disc_coupon) . '" data-toggle="modal" data-target="#ModalBayar"><i class="fas fa-credit-card"></i></a>
                    <a href="#" id="printinvoice" data-printinvoice="' . $invoice . '"  title="Cetak Invoice" style="color:red" data-toggle="modal" data-target="#PrintModalUnpaid"><i class="fa fa-print"></i></a>
                       <a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
                    <a href="' . site_url('whatsapp/sendbillunpaid/' . $result->invoice) . '" ><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a>
                    <a href="#" id="hapusmodal" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModal" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>';
            } else {

                $row[] = '<a href="#" id="bayar" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . indo_month($result->month) . '" data-invoice="' . $result->invoice . '" data-name="' . $result->name . '"  data-email_customer="' . $result->email . '"  data-periode="' . indo_month($result->month) . ' ' . $result->year . '" data-nominal="' . $nominalbayar . '"data-shownominal="' . indo_currency($result->amount - $result->disc_coupon) . '" data-toggle="modal" data-target="#ModalBayar"><i class="fas fa-credit-card"></i></a> 
                    <a href="#" id="printinvoice" data-printinvoice="' . $invoice . '"  title="Cetak Invoice" style="color:red" data-toggle="modal" data-target="#PrintModalUnpaid"><i class="fa fa-print"></i></a>
                      <a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
                    <a href="' . $linkWA . indo_tlp($result->no_wa) . '&text=' . $message   . '" target="blank"><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a>
                    <a href="#" id="hapusmodal" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModal" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>';
            };

            $data[] = $row;
        }

        $role = $this->_role();
        $cover = $this->_coverage();
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $coverage = $this->bill_m->getInvoiceUp($cover)->num_rows();
        } else {
            $coverage =  $this->bill_m->getInvoiceUp()->num_rows();
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $coverage,
            "recordsFiltered" => $this->bill_m->count_filtered_data_unpaid(),
            "data" => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
    public function getPaidInvoice()
    {
        $result = $this->bill_m->getPaidInv();
        // var_dump($result);die;
        $data = [];
        $no = $_POST['start'];
        foreach ($result as $result) {
            $row = array();
            $row[] = ++$no;
            $row[] = '<input type=' . 'checkbox' . ' class=' . 'check-item' . ' id="ceklis" name=' . 'invoice[]' . ' value=' . $result->invoice . '>';

            $invoice = $result->invoice;

            $other = $this->db->get('other')->row_array();
            $company = $this->db->get('company')->row_array();
            if ($result->codeunique > 0) {
                $codeunique = $result->code_unique;
            } else {
                $codeunique = 0;
            }
            $row[] =  $result->name;

            $row[] =  $result->metode_payment . '<br>' . $result->no_services . '<br>' . $result->invoice;

            $row[] = indo_month($result->month) . ' ' . $result->year;
            $row[] = indo_currency($result->amount - $result->disc_coupon + $codeunique);
            $nominal = indo_currency($result->amount - $result->disc_coupon + $codeunique);

            $row[] = '<span class="badge badge-success">' . $result->status . '</span> <br>Tgl Bayar : ' . date('Y-m-d H:i:s', $result->date_payment) . '<br>Pesan Terikirim : ' . $result->send_paid;
            $coverage = $this->db->get_where('coverage', ['coverage_id' => $result->coverage])->row_array();
            if ($coverage > 0) {
                $row[] = 'Area : ' . $coverage['c_name'] . '<br> Alamat : ' . $result->address;
            } else {
                $row[] = 'Area : Belum Terdafar <br> Alamat : ' . $result->address;
            }
            $getuser = $this->db->get_where('user', ['id' => $result->create_by])->row_array();
            $search  = array('{name}', '{noservices}', '{email}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}', '{receiver}');
            $replace = array($result->name, $result->no_services, $result->email, indo_month($result->month), $result->year, indo_month($result->month) . ' ' . $result->year, indo_date($result->inv_due_date), $nominal, $company['company_name'], $company['sub_name'], base_url(), '%0A', $getuser['name']);
            $subject = $other['say_wa'];
            $subjectthanks = $other['thanks_wa'];

            $messagethanks = str_replace($search, $replace, $subjectthanks);
            $linkWA = "https://api.whatsapp.com/send?phone=";
            $whatsapp = $this->db->get('whatsapp')->row_array();

            if ($whatsapp['is_active'] == 1) {
                if ($result->status == 'SUDAH BAYAR') {
                    $row[] = '<a href="#" id="printinvoice" data-printinvoice="' . $invoice . '"   title="Cetak Invoice"  style="color:green" data-toggle="modal" data-target="#PrintModalPaid"><i class="fa fa-print"></i></a> <a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
                    <a href="#" id="hapusmodalbayar" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModalBayar" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>
                    <a href="' . site_url('whatsapp/sendbillpaid/' . $result->invoice) . '"><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a> ';
                }
            } else {
                if ($result->status == 'SUDAH BAYAR') {
                    $row[] = '<a href="#" id="printinvoice" data-printinvoice="' . $invoice . '"   title="Cetak Invoice"  style="color:green" data-toggle="modal" data-target="#PrintModalPaid"><i class="fa fa-print"></i></a><a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
                    <a href="#" id="hapusmodalbayar" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModalBayar" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>
                    <a href="' . $linkWA . indo_tlp($result->no_wa) . '&text=' . $messagethanks . '" target="blank"><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a> ';
                }
            };
            $data[] = $row;
        }
        $role = $this->_role();
        $cover = $this->_coverage();
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $coverage = $this->bill_m->getInvoicep($cover)->num_rows();
        } else {
            $coverage =  $this->bill_m->getInvoicep()->num_rows();
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" =>  $this->bill_m->count_all_data_paid(),
            "recordsFiltered" => $this->bill_m->count_filtered_data_paid(),
            "data" => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
    public function getbilldraf()
    {
        $company = $this->db->get('company')->row_array();
        $month = date('m');
        $year = date('Y');
        $result = $this->customer_m->getActive();
        $data = [];
        $no = $_POST['start'];
        foreach ($result as $result) {
            $querybill = "SELECT *FROM `invoice`    WHERE `invoice`.`no_services` = $result->no_services and `invoice`.`month` = $month and `invoice`.`year` = $year";
            $querying = $this->db->query($querybill)->row_array();

            $subtotal = $this->getCustomerMonthlyAmount($result);
            $row = array();
            $row[] = ++$no;

            $row[] = '<input type=' . 'checkbox' . ' class=' . 'check-item' . ' name=' . 'noservices[]' . ' value=' . $result->no_services . '>';

            $row[] = $result->no_services;
            $row[] = $result->name;
            if ($result->ppn == 1) {
                $ppn = $subtotal * ($company['ppn'] / 100);
                $row[] = indo_currency($subtotal + $ppn);
            } else {
                $row[] = indo_currency($subtotal);
            }
            // cek tagihan
            if (!empty($querying)) {
                $row[] = indo_date($querying['inv_due_date']);
            } else {
                if ($result->month_due_date == 0) {
                    $month = date('m');
                } else {
                    $month = date('m', strtotime("next month"));
                }
                $row[] = $result->due_date . ' ' . indo_month($month) . ' ' . date('Y');
            }
            if (!empty($querying)) {
                if ($querying['status'] == 'BELUM BAYAR') {
                    $row[] = '<div class="badge badge-danger">' . $querying['status'] . '</div>';
                } else {
                    $row[] = '<div class="badge badge-success">' . $querying['status'] . '</div>';
                }
            } else {
                $row[] = 'Tagihan Belum Disimpan';
            }

            if (empty($querying)) {
                $row[] = ' <a href="#" id="savebill" data-no_services="' . $result->no_services . '"   data-name="' . $result->name . '"    data-toggle="modal" data-target="#SaveModal" ><i class="fas fa-save" style="font-size:20px; color:blue"></i></a>  
                <a href="' . site_url('services/detail/' . $result->no_services) . '" title="Detail Paket"     ><i class="fas fa-clone" style="font-size:20px; color:green"></i></a>';
            } else {
                $row[] = '<a  href="' . site_url('bill/detail/' . $querying['invoice']) . '"><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a>';
            }
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->db->get_where('customer', ['c_status' => 'Aktif'])->num_rows(),
            "recordsFiltered" => $this->customer_m->count_filtered_data_active(),
            "data" => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
    public function getfilterbilldraf()
    {
        $post = $this->input->post(null, TRUE);
        $company = $this->db->get('company')->row_array();
        $month = date('m');
        $year = date('Y');
        $result = $this->customer_m->getfilterbydraf($post);
        $data = [];
        $no = $_POST['start'];
        foreach ($result->result() as $result) {
            $querybill = "SELECT *FROM `invoice`    WHERE `invoice`.`no_services` = $result->no_services and `invoice`.`month` = $month and `invoice`.`year` = $year";
            $querying = $this->db->query($querybill)->row_array();

            $subtotal = $this->getCustomerMonthlyAmount($result);
            $row = array();
            $row[] = ++$no;
            $row[] = '<input type=' . 'checkbox' . ' class=' . 'check-item' . ' name=' . 'noservices[]' . ' value=' . $result->no_services . '>';
            $row[] = $result->no_services;
            $row[] = $result->name;
              if ($result->ppn == 1) {
                $ppn = $subtotal * ($company['ppn'] / 100);
                $row[] = indo_currency($subtotal + $ppn);
            } else {
                $row[] = indo_currency($subtotal);
            }
            // cek tagihan
            if (!empty($querying)) {
                $row[] = indo_date($querying['inv_due_date']);
            } else {
                if ($result->month_due_date == 0) {
                    $month = date('m');
                } else {
                    $month = date('m', strtotime("next month"));
                }
                $row[] = $result->due_date . ' ' . indo_month($month) . ' ' . date('Y');
            }
              if (!empty($querying)) {
                if ($querying['status'] == 'BELUM BAYAR') {
                    $row[] = '<div class="badge badge-danger">' . $querying['status'] . '</div>';
                } else {
                    $row[] = '<div class="badge badge-success">' . $querying['status'] . '</div>';
                }
            } else {
                $row[] = 'Tagihan Belum Disimpan';
            }
               if (empty($querying)) {
                $row[] = ' <a href="#" id="savebill" data-no_services="' . $result->no_services . '"   data-name="' . $result->name . '"    data-toggle="modal" data-target="#SaveModal" ><i class="fas fa-save" style="font-size:20px; color:blue"></i></a>  
                <a href="' . site_url('services/detail/' . $result->no_services) . '" title="Detail Paket"     ><i class="fas fa-clone" style="font-size:20px; color:green"></i></a>';
            } else {
                $row[] = '<a  href="' . site_url('bill/detail/' . $querying['invoice']) . '"><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a>';
            }
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->customer_m->getfilterbydraf($post)->num_rows(),
            "recordsFiltered" => $this->customer_m->getfilterbydraf($post)->num_rows(),
            "data" => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
    public function payselected()
    {
        $invoice = $_POST['invoice'];
        if ($invoice == null) {
            $this->session->set_flashdata('error-sweet', 'Silahkan pilih dulu Invoice');
        } else {
            $datainvoice = $this->bill_m->getInvoiceSelected($invoice)->result();

            $dataIncome = [];
            foreach ($datainvoice as $key => $row) {
                if ($row->amount == 0) {
                    $query = "SELECT * FROM `invoice_detail` WHERE `invoice_detail`.`invoice_id` =  $row->invoice";
                    $querying = $this->db->query($query)->result();
                    $subTotal = 0;
                    foreach ($querying as  $dataa)
                        $subTotal += (int) $dataa->total;
                    if ($subTotal != 0) {
                        $ppn = $subTotal * ($row->i_ppn / 100);
                        $nominal = $subTotal + $ppn;
                    } else {
                        $query = "SELECT * FROM `invoice_detail` WHERE `invoice_detail`.`d_month` =  $row->month and
                                           `invoice_detail`.`d_year` =  $row->year and
                                           `invoice_detail`.`d_no_services` =  $row->no_services";
                        $queryTot = $this->db->query($query)->result();
                        $subTotaldetail = 0;
                        foreach ($queryTot as  $dataa)
                            $subTotaldetail += (int) $dataa->total;
                        $ppn = $subTotaldetail * ($row->i_ppn / 100);
                        $nominal = $subTotaldetail + $ppn;
                    }
                } else {
                    $nominal = $row->amount;
                }
                array_push(
                    $dataIncome,
                    array(
                        'nominal' => $nominal,
                        'invoice_id' => $row->invoice,
                        'no_services' => $row->no_services,
                        'mode_payment' => 'Cash',
                        'date_payment' => date('Y-m-d'),
                        'category' => 1,
                        'remark' => 'Pembayaran Tagihan no layanan' . ' ' . $row->no_services . ' ' . 'a/n' . ' ' . $row->name . ' ' . 'Periode' . ' ' . indo_month($row->month) . ' ' . $row->year,
                        'create_by' => $this->session->userdata('id')
                    )
                );
            }
            $this->income_m->payselected($dataIncome);
            $this->bill_m->payselected($invoice);
            $rt = $this->db->get_where('router', ['id' => 1])->row_array();
            if ($rt['is_active'] == 1) {
                foreach ($datainvoice as  $row) {

                    if ($row->auto_isolir = 1) {
                        if ($row->user_mikrotik != '') {
                            openisolir($row->no_services, $row->router);
                        }
                    }
                }
            }
            $whatsapp = $this->db->get('whatsapp')->row_array();

            if ($whatsapp['is_active'] == 1) {
                $other = $this->db->get('other')->row_array();
                $company = $this->db->get('company')->row_array();
                $no = 1;
                foreach ($datainvoice as $wa) {
                    $range = $no++ * $whatsapp['interval_message'];

                    $jadwall = time() + (1  * $range);
                    $time = date('Y-m-d H:i:s', $jadwall);

                    $month = $wa->month;
                    $year = $wa->year;
                    $amount = $wa->amount - $wa->disc_coupon;
                    if ($whatsapp['paymentinvoice'] == 1) {
                        $other = $this->db->get('other')->row_array();
                        $company = $this->db->get('company')->row_array();
                        $target = indo_tlp($wa->no_wa);
                        $nominalWA = indo_currency($amount);
                        $getuser = $this->db->get_where('user', ['id' => $wa->create_by])->row_array();
                        $search  = array('{name}', '{noservices}',  '{email}', '{invoice}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}', '{invoice}', '{receiver}');
                        $replace = array($wa->name, $wa->no_services, $wa->email, $wa->invoice, indo_month($month), $year, indo_month($month) . ' ' . $year, $wa->due_date, $nominalWA, $company['company_name'], $company['sub_name'], base_url(), '', $wa->invoice, $getuser['name']);
                        $subject = $other['thanks_wa'];
                        $message = str_replace($search, $replace, $subject);
                        sendmsgschbillpaid($target, $message, $time, $wa->invoice);
                    }
                }
            }
            if ($this->db->affected_rows() > 0) {

                $this->session->set_flashdata('success-sweet', 'Tagihan berhasil dibayarkan');
            }
        }

        redirect($_SERVER['HTTP_REFERER']);
    }

    public function delselected()
    {
        $role_id = $this->session->userdata('role_id');
        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();
        if ($role_id == 3 && $role['del_bill'] == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect('dashboard');
        }
        if ($this->session->userdata('role_id') == 2) {
            $this->session->set_flashdata('error-sweet', 'Akses dilarang');
            redirect('member/dashboard');
        }
        $invoice =  $_POST['invoice'];
        if ($invoice == null) {
            $this->session->set_flashdata('error-sweet', 'Belum ada tagihan yang dipilih');
        } else {
            $cekConfir = $this->bill_m->confirmselected($invoice)->result();
            $cekIncome = $this->income_m->incomeselected($invoice)->result();

            if (count($cekConfir) > 0) {
                $this->session->set_flashdata('error-sweet', 'Tagihan tidak bisa dihapus dikarenakan masih ada di konfirmasi pembayaran');
            } elseif (count($cekIncome) > 0) {
                $this->session->set_flashdata('error-sweet', 'Tagihan tidak bisa dihapus dikarenakan masih ada di data pendapadatan');
                # code...
            } else {
                $datainvoice = $this->bill_m->getInvoiceSelected($invoice)->result();
                foreach ($datainvoice as $key => $row) {
                    $month = $row->month;
                    $year = $row->year;
                    $invoiceqr = $row->invoice;
                    $no_services = $row->no_services;
                    $this->bill_m->deletedetailselected($month, $year, $no_services);
                    $target_file = './assets/images/qrcode/' . $invoiceqr . '.png';
                    unlink($target_file);
                }
                $this->bill_m->deleteselected($invoice);

                if ($this->db->affected_rows() > 0) {
                    $this->session->set_flashdata('success-sweet', 'Data Tagihan berhasil dihapus');
                }
            }
        }

        redirect($_SERVER['HTTP_REFERER']);
    }
    public function deletebill()
    {
        if ($this->session->userdata('confirmdelbill') == 0) {
            $this->session->set_flashdata('error-sweet', 'Akses Dilarang !');
            redirect('dashboard');
        } else {
            $data['title'] = 'Hapus Tagihan';
            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
            $data['other'] = $this->db->get('other')->row_array();
            $data['company'] = $this->db->get('company')->row_array();
            $this->template->load('backend', 'backend/bill/deletebill', $data);
        }
    }

    public function dellbill()
    {
        $post = $this->input->post(null, TRUE);
        $datainvoice = $this->bill_m->getInvoiceDelete($post)->result();
        if (count($datainvoice) == 0) {
            $this->session->set_flashdata('error-sweet', 'Data Tagihan Tidak Tersedia !');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $month = indo_month($post['month']);
            $year = $post['year'];
            $status = $post['status'];
            $action  = $this->session->userdata('name');
            $server = base_url();
            // var_dump($action);
            // die;
            $company = $this->db->get('company')->row_array();
            $bot = $this->db->get('bot_telegram')->row_array();
            $this->load->dbutil();
            $this->load->helper('file');
            $config = array(
                'format'    => 'zip',
                'filename'    => 'Backup-My-Wifi-' . $company['company_name'] . '-' . date("YmdHis") . '-db.sql'
            );
            $backup = $this->dbutil->backup($config);
            $filename = 'Backup-My-Wifi-' . date("ymdHis") . '.zip';
            $save = FCPATH . './assets/' . $filename;
            write_file($save, $backup);
            $token = $bot['token'];
            $send = "https://api.telegram.org/bot" . $token;
            $params  = [
                'chat_id' => $bot['id_telegram_owner'],
                'document' => base_url('assets/' . $filename),
                'caption' => 'Backup My-Wifi Sebelum Hapus semua data tagihan ' . $status . ' bulan ' . $month . ' tahun ' . $year . ' ' . date('d-m-Y H:i:s') .  ' Oleh ' . $action  . ' ' . $server,
                'parse_mode' => 'html',
            ];
            $ch = curl_init($send . '/sendDocument');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            curl_close($ch);
            // SEND KE OFFICIAL MY-WIFI
            $token = '1577575670:AAF20lnVQsYawwmgXu-BXYZhq8FLZMtYVo4';
            $send = "https://api.telegram.org/bot" . $token;
            $params  = [
                'chat_id' => '-581904381',
                'document' => base_url('assets/' . $filename),
                'caption' => 'Backup My-Wifi Sebelum Hapus semua data tagihan ' . $status . ' bulan ' . $month . ' tahun ' . $year . ' ' . date('d-m-Y H:i:s') .  ' Oleh ' . $action  . ' ' . $server,
                'parse_mode' => 'html',
            ];
            $ch = curl_init($send . '/sendDocument');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            curl_close($ch);
            // PHP program to delete all
            // file from a folder
            // Folder path to be flushed
            $folder_path = "./assets/";
            // List of name of files inside
            // specified folder
            $files = glob($folder_path . '/*');
            // Deleting all the files in the list
            foreach ($files as $file) {
                if (is_file($file))
                    // Delete the given file
                    unlink($file);
            }

            $target_file = './assets/' . $filename;
            unlink($target_file);
            foreach ($datainvoice as $data) {
                if ($post['delincome'] == 1) {
                    $this->db->where_in('invoice_id', $data->invoice);
                    $this->db->delete('income');
                }
                $this->bill_m->deletedetailselected($data->month, $data->year, $data->no_services);
                $this->db->where_in('invoice', $data->invoice);
                $this->db->delete('invoice');
            }
            if ($this->db->affected_rows() > 0) {
                $this->session->set_flashdata('success-sweet', 'Data Tagihan berhasil dihapus');
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function confirmdelbill()
    {
        $email = $this->session->userdata('email');
        $password = $this->input->post('password');
        $user = $this->db->get_where('user', ['email' => $email])->row_array(); // select * where user email = email
        if ($user) {
            // jika user active
            if ($user['is_active'] == 1) {
                # cek password dan verifikasi dengan input
                if (password_verify($password, $user['password'])) {
                    # jika sama
                    $data = [
                        'confirmdelbill' => 1,
                    ];
                    $this->session->set_userdata($data);
                    redirect('bill/deletebill');
                } else {

                    $this->session->set_flashdata('error-sweet', 'Password Salah ! ');
                    $data = [
                        'confirmdelbill' => 0,
                    ];
                    $this->session->set_userdata($data);
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                $data = [
                    'confirmdelbill' => 0,
                ];
                $this->session->set_userdata($data);
                $this->session->set_flashdata('error-sweet', 'Alamat email belum di aktivasi, silahkan hubungi admin ! ');
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            // jika tidak ada
            $this->session->set_flashdata('error-sweet', ' Alamat email belum terdaftar ! ');
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function sendwaselected()
    {

        $invoice =  $_POST['invoice'];
        if ($invoice == null) {
            $this->session->set_flashdata('error-sweet', 'Belum ada tagihan yang dipilih');
            redirect('bill/unpaid');
        } else {
            $other = $this->db->get('other')->row_array();
            $company = $this->db->get('company')->row_array();
            $whatsapp = $this->db->get('whatsapp')->row_array();
            $datainvoice = $this->bill_m->getInvoiceSelected($invoice)->result();
            if ($whatsapp['is_active'] == 1) {
                $no = 1;
                foreach ($datainvoice as  $wa) {
                    $range = $no++ * $whatsapp['interval_message'];

                    $jadwall = time() + (1  * $range);
                    $time = date('Y-m-d H:i:s', $jadwall);

                    $target = indo_tlp($wa->no_wa);

                    if ($wa->codeunique > 0) {
                        $codeunique = $wa->code_unique;
                    } else {
                        $codeunique = 0;
                    }

                    $nominalWA = indo_currency($wa->amount + $codeunique - $wa->codeunique);
                    $search  = array('{name}', '{noservices}', '{email}', '{invoice}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}');
                    $replace = array($wa->name, $wa->no_services,  $wa->email, $wa->invoice, indo_month($wa->month), $wa->year, indo_month($wa->month) . ' ' . $wa->year, indo_date($wa->inv_due_date), $nominalWA, $company['company_name'], $company['sub_name'], base_url(), '');
                    $subject = $other['say_wa'];
                    $message = str_replace($search, $replace, $subject);
                    sendmsgschbill($target, $message, $time, $wa->invoice);
                }
                $this->session->set_flashdata('success-sweet', 'Eksekusi Berhasil, Silahkan cek di schedule Whatsapp Gateway Anda !');
            } else {
                $this->session->set_flashdata('error-sweet', 'Whatsapp Gateway Non-Aktif');
            }
        }

        redirect($_SERVER['HTTP_REFERER']);
    }

    public function sendwaselectedduedate()
    {
        $invoices = $this->input->post('invoice');
        if (empty($invoices)) {
            $this->session->set_flashdata('error-sweet', 'Belum ada tagihan yang dipilih');
            redirect('bill/duedate');
            return;
        }

        $other = $this->db->get('other')->row_array();
        $company = $this->db->get('company')->row_array();
        $whatsapp = $this->db->get('whatsapp')->row_array();
        if ($whatsapp['is_active'] != 1) {
            $this->session->set_flashdata('error-sweet', 'Whatsapp Gateway Non-Aktif');
            redirect($_SERVER['HTTP_REFERER']);
            return;
        }

        $invoices = array_values(array_unique($invoices));
        $billRows = $this->bill_m->getInvoiceSelected($invoices)->result();
        $delay = (int) $whatsapp['interval_message'];
        $sequence = 0;
        foreach ($billRows as $bill) {
            if ($bill->status !== 'BELUM BAYAR') {
                continue;
            }

            $codeunique = $bill->codeunique > 0 ? $bill->code_unique : 0;
            $nominal = $bill->amount - $bill->disc_coupon + $codeunique;
            $creator = $this->db->get_where('user', ['id' => $bill->create_by])->row_array();
            $search = array('{name}', '{noservices}', '{nova}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}', '{slogan}', '{link}', '{e}', '{receiver}');
            $replace = array($bill->name, $bill->no_services, $bill->no_va, $bill->month, $bill->year, indo_month($bill->month) . ' ' . $bill->year, indo_date($bill->inv_due_date), $nominal, $company['company_name'], $company['sub_name'], base_url(), '', $creator['name']);
            $message = str_replace($search, $replace, $other['body_wa']);
            $sendAt = date('Y-m-d H:i:s', time() + (++$sequence * $delay));
            sendmsgschduedate(indo_tlp($bill->no_wa), $message, $sendAt, $bill->invoice);
        }

        $this->session->set_flashdata('success-sweet', 'Info jatuh tempo berhasil dijadwalkan untuk dikirim.');
        redirect($_SERVER['HTTP_REFERER']);
    }



    public function printinvoicedotmatrix($invoice, $a = null, $b = null, $c = null, $d = null)
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
        $this->fixamount($invoice);
        $inv = $this->db->get_where('invoice', ['invoice' => $invoice])->row_array();
        $month =  $inv['month'];
        $year = $inv['year'];
        $no_services = $inv['no_services'];
        $billdetail = $this->bill_m->fixbilldetail($no_services, $month, $year)->result();
        foreach ($billdetail as $unit) {
            $item =  $this->bill_m->fixbilldetail($no_services, $month, $year, $unit->item_id)->num_rows();
            if ($item > 1) {
                $lasitem =  $this->bill_m->lastitembilldetail($no_services, $month, $year, $unit->item_id)->row_array();
                $this->db->where('detail_id', $lasitem['detail_id']);
                $this->db->delete('invoice_detail');
            }
        }
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
        $data['title'] = 'Invoice Dot Matrix';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['invoice'] = $this->bill_m->getBill($invoice);
        $data['invoice_detail'] = $this->bill_m->getDetailBill($invoice);
        $data['bill'] = $this->bill_m->getBill($invoice)->row_array();
        $data['bank'] = $this->setting_m->getBank()->result();
        $data['p_item'] = $this->package_m->getPItem()->result();

        $data['other'] = $this->db->get('other')->row_array();
        $this->load->view('backend/bill/invoicedotmatrix', $data);
    }
    public function printinvoicesmall($invoice, $a = null, $b = null, $c = null, $d = null)
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
        $this->fixamount($invoice);
        $inv = $this->db->get_where('invoice', ['invoice' => $invoice])->row_array();
        $month =  $inv['month'];
        $year = $inv['year'];
        $no_services = $inv['no_services'];
        $billdetail = $this->bill_m->fixbilldetail($no_services, $month, $year)->result();
        foreach ($billdetail as $unit) {
            $item =  $this->bill_m->fixbilldetail($no_services, $month, $year, $unit->item_id)->num_rows();
            if ($item > 1) {
                $lasitem =  $this->bill_m->lastitembilldetail($no_services, $month, $year, $unit->item_id)->row_array();
                $this->db->where('detail_id', $lasitem['detail_id']);
                $this->db->delete('invoice_detail');
            }
        }
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
        $data['title'] = 'Invoice Dot Matrix';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['invoice'] = $this->bill_m->getBill($invoice);
        $data['invoice_detail'] = $this->bill_m->getDetailBill($invoice);
        $data['bill'] = $this->bill_m->getBill($invoice)->row_array();
        $data['bank'] = $this->setting_m->getBank()->result();
        $data['p_item'] = $this->package_m->getPItem()->result();

        $data['other'] = $this->db->get('other')->row_array();
        $this->load->view('backend/bill/invoicesmall', $data);
    }
    public function printinvoicedotmatrixselected()
    {
        $invoice = $_POST['invoice'];
        if ($invoice == null) {
            $this->session->set_flashdata('error-sweet', 'Tagihan belum dipilih');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $data['title'] = 'Invoice Dot Metrix Selected';
            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
            $data['company'] = $this->db->get('company')->row_array();
            $data['invoice'] = $this->bill_m->getBill($invoice)->result();
            $bill = $this->bill_m->getInvoiceSelected($invoice)->result();
            foreach ($bill as $fix) {
                $billdetail = $this->bill_m->fixbilldetail($fix->no_services, $fix->month, $fix->year)->result();
                foreach ($billdetail as $unit) {
                    $item =  $this->bill_m->fixbilldetail($fix->no_services, $fix->month, $fix->year, $unit->item_id)->num_rows();
                    if ($item > 1) {
                        $lasitem =  $this->bill_m->lastitembilldetail($fix->no_services, $fix->month, $fix->year, $unit->item_id)->row_array();
                        $this->db->where('detail_id', $lasitem['detail_id']);
                        $this->db->delete('invoice_detail');
                    }
                }
            }
            $data['bill'] = $this->bill_m->getInvoiceSelected($invoice)->result();
            $data['other'] = $this->db->get('other')->row_array();
            $data['bank'] = $this->setting_m->getBank()->result();
            $data['p_item'] = $this->package_m->getPItem()->result();

            $this->load->view('backend/bill/invoicedotmatrixselected', $data);
        }
    }

    public function fixbill($month, $year)
    {
        $data['bill'] = $this->bill_m->getInvoicemonthyear($month, $year)->result();
        $this->load->view('backend/bill/fixbill', $data);
    }
    public function fixdouble()
    {
        $month = date('m');
        $year = date('Y');
        $customer = $this->customer_m->getCustomerActive()->result();
        foreach ($customer as $data) {
            $cekdouble = $this->bill_m->getInvoicemonthyeardouble($data->no_services, $month, $year)->num_rows();
            // var_dump($cekdouble);
            // die;
            echo $data->name . ' ' . $cekdouble;
            echo '<br>';
            if ($cekdouble > 1) {
                $getlastdouble = $this->bill_m->getInvoicemonthyeardoubleget($data->no_services, $month, $year)->row_array();
                echo $getlastdouble['invoice_id'];
                $this->db->where('invoice_id', $getlastdouble['invoice_id']);
                $this->db->delete('invoice');
            };
        }
    }
    public function fixoldbill()
    {
        $bill =  $this->bill_m->getInvoice()->result();
        foreach ($bill as $r => $data) {
            $inv = $this->db->get_where('invoice', ['invoice' => $data->invoice])->row_array();
            $invdetail = $this->db->get_where('invoice_detail', ['invoice_id' => $data->invoice])->row_array();

            $month =  $inv['month'];
            $year = $inv['year'];
            $no_services = $inv['no_services'];
            if ($invdetail['d_month'] == 0) {
                $this->db->set('d_month', $month);
                $this->db->set('d_year', $year);
                $this->db->set('d_no_services', $no_services);
                $this->db->where('invoice_id', $inv['invoice']);
                $this->db->update('invoice_detail');
            }
            echo $data->name;
        }
    }
    public function unsetduedate()
    {
        $this->db->set('inv_due_date', '');
        $this->db->where('status', 'BELUM BAYAR');

        $this->db->update('invoice');
    }
    public function unsetisolir()
    {
        $this->db->set('date_isolir', '');
        $this->db->where('status', 'BELUM BAYAR');
        $this->db->update('invoice');
    }
    public function fixduedate()
    {
        $bill =  $this->bill_m->getfixduedate()->result();
        if (count($bill) > 0) {
            foreach ($bill as $r => $data) {
                $inv = $this->db->get_where('invoice', ['invoice' => $data->invoice])->row_array();
                $month =  $inv['month'];
                $year = $inv['year'];
                if (strlen($data->due_date) == 1) {
                    $date = '0' . $data->due_date;
                } else {
                    $date = $data->due_date;
                }


                if ($data->month_due_date == 0) {
                    $duedate = $year . '-' . $month . '-' . $date;
                } else {
                    $newduedate = $year . '-' . $month . '-' . $date;
                    $nextmonth = date('m', strtotime('next month', strtotime($newduedate)));
                    $nextyear = date('Y', strtotime('next month', strtotime($newduedate)));
                    $duedate = $nextyear . '-' . $nextmonth . '-' . $date;
                }


                $max = $data->max_due_isolir;
                if ($data->max_due_isolir != 0) {
                    $gettime = strtotime("+$max day", strtotime($duedate));
                    $datenew = date("d", $gettime);
                    $monthisolir = date("m", $gettime);
                    $yearisolir = date("Y", $gettime);
                    $enddate = $yearisolir . '-' . $monthisolir . '-' . $datenew;
                } else {
                    $enddate = $duedate;
                }

                $this->db->set('inv_due_date', $duedate);
                $this->db->where('invoice', $inv['invoice']);
                $this->db->update('invoice');

                echo $data->name;
                echo '<br';
            }
        }
        $isolir =  $this->bill_m->getfixisolir()->result();
        if (count($isolir) > 0) {
            foreach ($isolir as $r => $data) {
                $inv = $this->db->get_where('invoice', ['invoice' => $data->invoice])->row_array();
                $month =  $inv['month'];
                $year = $inv['year'];
                if (strlen($data->due_date) == 1) {
                    $date = '0' . $data->due_date;
                } else {
                    $date = $data->due_date;
                }


                if ($data->month_due_date == 0) {
                    $duedate = $year . '-' . $month . '-' . $date;
                } else {
                    $newduedate = $year . '-' . $month . '-' . $date;
                    $nextmonth = date('m', strtotime('next month', strtotime($newduedate)));
                    $nextyear = date('Y', strtotime('next month', strtotime($newduedate)));
                    $duedate = $nextyear . '-' . $nextmonth . '-' . $date;
                }


                $max = $data->max_due_isolir;
                if ($data->max_due_isolir != 0) {
                    $gettime = strtotime("+$max day", strtotime($duedate));
                    $datenew = date("d", $gettime);
                    $monthisolir = date("m", $gettime);
                    $yearisolir = date("Y", $gettime);
                    $enddate = $yearisolir . '-' . $monthisolir . '-' . $datenew;
                } else {
                    $enddate = $duedate;
                }
                $this->db->set('date_isolir', $enddate);
                $this->db->where('invoice', $inv['invoice']);
                $this->db->update('invoice');

                echo $data->name;
                echo '<br';
            }
        }
    }


    public function gethistorybill()
    {
        $noservices = $this->input->post('no_services');
        $year = $this->input->post('year');
        $data['year'] = $this->input->post('year');
        $data['bill'] = $this->bill_m->getInvoiceHistory($noservices, $year)->result();
        $data['no_services'] = $this->input->post('no_services');
        $this->load->view('backend/bill/tampil_history', $data);
    }

    private function fixamount($invoice)
    {
        $inv = $this->db->get_where('invoice', ['invoice' => $invoice])->row_array();

        $month =  $inv['month'];
        $year = $inv['year'];
        $no_services = $inv['no_services'];
        $query = "SELECT *, `invoice_detail`.`price` as `detail_price`
                FROM `invoice_detail`
                              WHERE `invoice_detail`.`d_month` =  $month and
                   `invoice_detail`.`d_year` =  $year and
                   `invoice_detail`.`d_no_services` =  $no_services";
        $detailinvoice = $this->db->query($query)->result();


        foreach ($detailinvoice as $value) {
            if ($value->invoice_id == null) {
                $this->db->set('invoice_id', $invoice);
                $this->db->where('d_no_services', $no_services);
                $this->db->where('d_month', $month);
                $this->db->where('d_year', $year);
                $this->db->where('detail_id', $value->detail_id);
                $this->db->update('invoice_detail');
            }
        }
        $amountt = 0;
        foreach ($detailinvoice as $detail) {
            $amountt += (int) $detail->total;
        }
        if ($inv['i_ppn'] > 0) {
            $ppn = $amountt * ($inv['i_ppn'] / 100);
            $amount =  $amountt + $ppn;
        } else {
            $amount =  $amountt;
        }
        $this->db->set('amount', $amount);
        $this->db->where('invoice', $inv['invoice']);
        $this->db->update('invoice');
    }

    public function isolirduedate()
    {
        $listrouter = $this->db->get('router')->result();
        foreach ($listrouter as $router) {
            $totalcustomer = $this->db->get_where('customer', ['router' => $router->id])->num_rows();
            if ($totalcustomer > 0) {
                $billpasca = $this->customer_m->getCustomerfromisolir($router->id)->result();

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
    public function conversenddue()
    {
        $query = "SELECT *
        FROM `invoice`
                   WHERE `invoice`.`send_before_due`!='' and  `invoice`.`status` =  'BELUM BAYAR'";
        $bill = $this->db->query($query)->result();
        print_r($bill);
        foreach ($bill as $data) {
            $int = strtotime($data->send_before_due);
            $this->db->set('send_before_due', $int);
            $this->db->where('invoice_id', $data->invoice_id);
            $this->db->update('invoice');
        }
    }
    
    // data income
    public function _getestimationincome($coverage, $cara_bayar, $year, $month)
    {

        // $query = "SELECT sum(`invoice`.`amount`) as `jumlah`
        // FROM `customer` JOIN `invoice` ON `invoice`.`code_unique` = `customer`.`customer_id` WHERE  `customer`.`c_status` = 'Aktif' AND `customer`.`coverage` = '$coverage' AND `invoice`.`metode_payment` = '$cara_bayar' AND `invoice`.`year` = '$year' AND `invoice`.`month` = '$month'
        // ";
        
        $query = "SELECT sum(i.amount) as jumlah FROM customer as a LEFT JOIN invoice as i ON a.customer_id = i.code_unique WHERE a.coverage = '$coverage' AND i.metode_payment = '$cara_bayar' AND i.year = '$year' AND i.month = '$month'";
        $querying = $this->db->query($query)->row();
        // echo $this->db->last_query();die;
        // $grandtotal = 0;
        // foreach ($querying as  $dataa)
        //     $grandtotal += (int) $dataa->amount;
            
        // $data = [
        //     'estimation' => indo_currency($grandtotal),
        // ];
        // var_dump($querying->jumlah);die;
        return $querying->jumlah;
        // echo  indo_currency($grandtotal);
        // echo json_encode($data);
    }
    
    // Filter Coverage 00:45 03-Maret-2022
    public function getdatabillcoverage()
    {
        // var_dump($this->input->post('coverage', TRUE));die;
        $data['post'] =  $this->input->post(null, TRUE);
        $this->load->view('backend/bill/get-data-bill', $data);
    }
    public function getfiltercoverage()
    {
        // $post_area = implode(',',$this->input->post('coverage', TRUE));
        // var_dump($post_area);die;
        $post = $this->input->post(null, TRUE);
        if (isset($post['status']) && $post['status'] === 'BELUM BAYAR') {
            $this->prepareUnpaidInvoiceIpSearch();
        }
        $result = $this->bill_m->getfilterbycoverage($post);
        $ipByCustomer = $this->getInvoiceCustomerIpMap($result);
        $data = [];
        $company = $this->db->get('company')->row_array();
        $no = $_POST['start'];
        foreach ($result as $result) {
            $row = array();
            $row[] = ++$no;
            $row[] = '<input type=' . 'checkbox' . ' class=' . 'check-item' . ' id="ceklis" name=' . 'invoice[]' . ' value=' . $result->invoice . '>';

            $invoice = $result->invoice;


            $other = $this->db->get('other')->row_array();
            $company = $this->db->get('company')->row_array();
            if ($result->codeunique > 0) {
                $codeunique = $result->code_unique;
            } else {
                $codeunique = 0;
            }
            $row[] =  $result->name;
            $row[] =  '<span class="badge badge-warning">' . $result->metode_payment . '</span> <br>' . $result->no_services . '<br>' . $result->invoice;


            $row[] = indo_month($result->month) . ' ' . $result->year;
            $row[] = indo_date($result->inv_due_date) . '<br>Tgl Isolir : ' . indo_date($result->date_isolir);

            $row[] = indo_currency($result->amount - $result->disc_coupon + $codeunique);
            $nominal = indo_currency($result->amount - $result->disc_coupon + $codeunique);
            $nominalbayar = $result->amount - $result->disc_coupon;
            if ($result->status == 'BELUM BAYAR') {
                $row[] = '<span class="badge badge-danger">' . $result->status . '</span>'  . '<br>Pesan Terikirim : ' . $result->send_bill . '<br>Sebelum Jatuh Tempo : ' . $result->send_before_due . '<br>Jatuh Tempo : ' . $result->send_due;
            } else {
                $row[] = '<span class="badge badge-success">' . $result->status . '</span> <br>Tgl Bayar : ' . date('Y-m-d H:i:s', $result->date_payment) . '<br>Pesan Terikirim : ' . $result->send_paid;
            }
            $coverage = $this->db->get_where('coverage', ['coverage_id' => $result->coverage])->row_array();
            if ($coverage > 0) {
                $row[] = 'Area : ' . $coverage['c_name'] . '<br> Alamat : ' . $result->address;
            } else {
                $row[] = 'Area : Belum Terdafar <br> Alamat : ' . $result->address;
            }
            $row[] = html_escape($result->cust_description);
            $row[] = isset($ipByCustomer[$result->customer_id]) ? $ipByCustomer[$result->customer_id] : '-';

            $getuser = $this->db->get_where('user', ['id' => $result->create_by])->row_array();
            $search  = array('{name}', '{email}', '{noservices}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}', '{slogan}', '{link}', '{e}', '{receiver}');
            $replace = array($result->name, $result->email, $result->no_services, indo_month($result->month), $result->year, indo_month($result->month) . ' ' . $result->year, indo_date($result->inv_due_date), $nominal, $company['company_name'], $company['sub_name'], base_url(), '%0A', $getuser['name']);
            $subject = $other['say_wa'];
            $subjectthanks = $other['thanks_wa'];
            $message = str_replace($search, $replace, $subject);
            $messagethanks = str_replace($search, $replace, $subjectthanks);
            $linkWA = "https://api.whatsapp.com/send?phone=";
            $whatsapp = $this->db->get('whatsapp')->row_array();

            if ($whatsapp['is_active'] == 1) {
                if ($result->status == 'SUDAH BAYAR') {
                    $row[] = '<a href="#" id="printinvoice" data-printinvoice="' . $invoice . '"   title="Cetak Invoice"  style="color:green" data-toggle="modal" data-target="#PrintModalPaid"><i class="fa fa-print"></i></a> <a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
                    <a href="#" id="hapusmodalbayar" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModalBayar" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>
                    <a href="' . site_url('whatsapp/sendbillpaid/' . $result->invoice) . '"><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a> ';
                } else {
                    $row[] = '<a href="#" id="bayar" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . indo_month($result->month) . '" data-invoice="' . $result->invoice . '" data-name="' . $result->name . '"  data-email_customer="' . $result->email . '"  data-periode="' . indo_month($result->month) . ' ' . $result->year . '" data-nominal="' . $nominalbayar . '"data-shownominal="' . indo_currency($result->amount - $result->disc_coupon) . '" data-toggle="modal" data-target="#ModalBayar"><i class="fas fa-credit-card"></i></a> <a href="#" id="printinvoice" data-printinvoice="' . $invoice . '"  title="Cetak Invoice" style="color:red" data-toggle="modal" data-target="#PrintModalUnpaid"><i class="fa fa-print"></i></a><a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
                    <a href="' . site_url('whatsapp/sendbillunpaid/' . $result->invoice) . '" ><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a>
                    <a href="#" id="hapusmodal" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModal" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>';
                }
            } else {
                if ($result->status == 'SUDAH BAYAR') {
                    $row[] = '<a href="#" id="printinvoice" data-printinvoice="' . $invoice . '"   title="Cetak Invoice"  style="color:green" data-toggle="modal" data-target="#PrintModalPaid"><i class="fa fa-print"></i></a> <a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
                    <a href="#" id="hapusmodalbayar" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModalBayar" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>
                    <a href="' . $linkWA . indo_tlp($result->no_wa) . '&text=' . $messagethanks . '" target="blank"><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a> ';
                } else {
                    $row[] = '<a href="#" id="bayar" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . indo_month($result->month) . '" data-invoice="' . $result->invoice . '" data-name="' . $result->name . '"  data-email_customer="' . $result->email . '"  data-periode="' . indo_month($result->month) . ' ' . $result->year . '" data-nominal="' . $nominalbayar . '"data-shownominal="' . indo_currency($result->amount - $result->disc_coupon) . '" data-toggle="modal" data-target="#ModalBayar"><i class="fas fa-credit-card"></i></a> <a href="#" id="printinvoice" data-printinvoice="' . $invoice . '"  title="Cetak Invoice" style="color:red" data-toggle="modal" data-target="#PrintModalUnpaid"><i class="fa fa-print"></i></a><a href="' . site_url('bill/detail/' . $result->invoice) . '" ><i class="fas fa-eye" style="font-size:20px; color:gray"></i></a> 
                    <a href="' . $linkWA . indo_tlp($result->no_wa) . '&text=' . $message   . '" target="blank"><i class="fab fa-whatsapp" style="font-size:20px; color:green"></i></a>
                    <a href="#" id="hapusmodal" data-no_servicess="' . $result->no_services . '" data-invoice_id="' . $result->invoice_id . '" data-yearr="' . $result->year . '" data-month="' . $result->month . '" data-name="' . $result->name . '" data-invoice="' . $result->invoice . '" data-periode="' . indo_month($result->month) . ' ' . $result->year . '"  data-toggle="modal" data-target="#DeleteModal" ><i class="fas fa-trash" style="font-size:20px; color:red"></i></a>';
                }
            };

            $data[] = $row;
        }
        $role = $this->_role();
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $coverage = $this->bill_m->getBillFilter($post)->num_rows();
        } else {
            $coverage =  $this->bill_m->getBillFilter($post)->num_rows();
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $coverage,
            "recordsFiltered" => $this->bill_m->count_filtered_data_coverage($post),
            "data" => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
    // reset paymet gateway
    public function resetpaymentgateway()
    {
        $inv = $this->input->get('invoice');
        $invoice = $this->db->get_where('invoice', ['invoice' => $inv])->row_array();
        $pg = $this->db->get('payment_gateway')->row_array();
        if ($pg['vendor'] == 'Xendit') {
            $this->db->set('x_id', '');
            $this->db->set('x_external_id', '');
            $this->db->set('x_amount', '');
            $this->db->set('payment_url', '');
            $this->db->where('invoice', $inv);
            $this->db->update('invoice');
        }
        if ($pg['vendor'] == 'Tripay') {
            $this->db->set('transaction_time', '');
            $this->db->set('x_method', '');
            $this->db->set('x_external_id', '');
            $this->db->set('x_amount', '');
            $this->db->set('x_account_number', '');
            $this->db->set('reference', '');
            $this->db->set('expired', '');
            $this->db->set('payment_url', '');
            $this->db->where('invoice', $inv);
            $this->db->update('invoice');
        }
        if ($this->db->affected_rows() > 0) {
            $message = 'Reset Data Payment Gateway Invoice ' . $inv . ' Pelanggan ' . $invoice['no_services'];
            $this->logs_m->activitylogs('Activity', $message);
            $this->session->set_flashdata('success-sweet', 'Data Payment Gateway di Tagihan ini berhasil di reset');
        }
        redirect('bill/detail/' . $inv);
    }
}
