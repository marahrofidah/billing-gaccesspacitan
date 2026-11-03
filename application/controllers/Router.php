<?php
ob_start();
defined('BASEPATH') or exit('No direct script access allowed');

class Router extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        is_logged_in();
        $this->load->model(['router_m']);
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
        if ($menu['role_id'] != 1 && $menu['router_menu'] == 0) {
            $this->session->set_flashdata('error', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->session->userdata('role_id') == 2) {
            $this->session->set_flashdata('error', 'Akses dilarang');
            redirect('member/dashboard');
        }
        $data['title'] = 'Routers';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['router'] = $this->router_m->get()->result();
        $this->template->load('backend', 'backend/router/router', $data);
    }
    public function add()
    {
        $menu = $this->_menu();
        $role = $this->_role();
        $role_id = $this->session->userdata('role_id');
        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();
        if ($menu['role_id'] != 1 && $role['add_router'] == 0) {
            $this->session->set_flashdata('error', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->session->userdata('role_id') == 2) {
            $this->session->set_flashdata('error', 'Akses dilarang');
            redirect('member/dashboard');
        }

        $post = $this->input->post(null, TRUE);
        $this->router_m->add($post);
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Router berhasil disimpan');
        }
        redirect('router');
    }

    public function edit()
    {
        $menu = $this->_menu();
        $role = $this->_role();
        $role_id = $this->session->userdata('role_id');
        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();
        if ($menu['role_id'] != 1 && $role['edit_router'] == 0) {
            $this->session->set_flashdata('error', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->session->userdata('role_id') == 2) {
            $this->session->set_flashdata('error', 'Akses dilarang');
            redirect('member/dashboard');
        }
        $post = $this->input->post(null, TRUE);
        $this->router_m->edit($post);
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Router berhasil di perbaharui');
        }
        redirect('router');
    }

    public function delete()
    {
        $menu = $this->_menu();
        $role = $this->_role();
        $role_id = $this->session->userdata('role_id');
        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();
        if ($menu['role_id'] != 1 && $role['del_router'] == 0) {
            $this->session->set_flashdata('error', 'Akses dilarang');
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->session->userdata('role_id') == 2) {
            $this->session->set_flashdata('error', 'Akses dilarang');
            redirect('member/dashboard');
        }
        $id = $this->input->post('id');
        $customer = $this->db->get_where('customer', ['router' => $id]);
        if ($id == 1) {
            $this->session->set_flashdata('error-sweet', 'Router master tidak bisa dihapus');
        } else {
            if ($customer->num_rows() > 0) {
                $this->session->set_flashdata('error-sweet', 'Router ini masih ada di data pelanggan');
            } else {
                $this->router_m->del($id);
                if ($this->db->affected_rows() > 0) {
                    $this->session->set_flashdata('success', 'Router berhasil dihapus');
                }
            }
        }

        redirect('router');
    }

    public function editconfig()
    {
        $post = $this->input->post(null, TRUE);
        $router = $this->db->get_where('router', ['id' => $this->session->userdata('router')])->row_array();
        $ip = $router['ip_address'];
        $user = $router['username'];
        $pass = $router['password'];
        $port = $router['port'];
        $API = new Mikweb();
        $API->connect($ip, $user, $pass, $port);
        $getcounttraffic = $API->comm("/system/scheduler/print", array('?name' => 'COUNT-INTERFACE',));
        $idcounttraffic = $getcounttraffic['0']['.id'];
        // var_dump($getcounttraffic);
        // die;
        if ($post['counttraffic'] == 'true') {
            if (count($getcounttraffic) == 0) {
                $API->comm("/system/scheduler/add", array(
                    'name' => 'COUNT-INTERFACE',
                    'comment' => 'COUNT-INTERFACE',
                    'start-time' => '00:30:00',
                    'interval' => '1d',
                    // 'disabled' => 'yes',
                    'on-event' => '/tool fetch url="' . base_url('countinterface') . '" keep-result=no',

                ));
            } else {
                $API->comm("/system/scheduler/disable", array(
                    ".id" =>  $idcounttraffic,
                ));
            }
        } else {
            if (count($getcounttraffic) == 0) {
                $API->comm("/system/scheduler/add", array(
                    'name' => 'COUNT-INTERFACE',
                    'comment' => 'COUNT-INTERFACE',
                    'start-time' => '00:30:00',
                    'interval' => '1d',
                    'disabled' => 'yes',
                    'on-event' => '/tool fetch url="' . base_url('countinterface') . '" keep-result=no',

                ));
            } else {
                $API->comm("/system/scheduler/enable", array(
                    ".id" =>  $idcounttraffic,
                ));
            }
        }
        $this->db->set('date_reset', $post['date_reset']);
        $this->db->where('id', $this->session->userdata('router'));
        $this->db->update('router');
        $this->db->set('user_mikrotik', $post['interface']);
        $this->db->where('router', $this->session->userdata('router'));
        $this->db->update('customer');
        redirect('mikrotik/config');
    }

    public function setusertoprimary()
    {
        $this->db->set('router', 1);
        $this->db->where('mode_user !=', '');
        $this->db->update('customer');
    }

    public function testconnection($id)
    {
        $router = $this->db->get_where('router', ['id' => $id])->row_array();
        if (!$router) {
            $this->session->set_flashdata('error-sweet', 'Data router tidak ditemukan');
            redirect('router');
            return;
        }
        $ip = $router['ip_address'];
        $user = $router['username'];
        $pass = $router['password'];
        $port = $router['port'];
        $API = new Mikweb();
        $API->attempts = 1;
        $API->timeout = 3;
        $API->delay = 0;
        try {
            if (!$API->connect($ip, $user, $pass, $port)) {
                $this->session->set_flashdata('error-sweet', 'Gagal terhubung. Periksa IP, port API, username, password, dan akses jaringan ke MikroTik.');
            } else {
                $resource = $API->comm('/system/resource/print');
                if (isset($resource[0]['uptime'])) {
                    $this->session->set_flashdata('success-sweet', 'Connected');
                } else {
                    $this->session->set_flashdata('error-sweet', 'Terhubung, tetapi gagal membaca data router. Periksa izin akun API.');
                }
            }
        } catch (Throwable $e) {
            $diagnostic = get_class($e) . ': ' . $e->getMessage();
            foreach (array_filter([$pass, $user]) as $secret) {
                $diagnostic = str_replace($secret, '[hidden]', $diagnostic);
            }
            error_log(date('c') . ' [Router API] ' . $diagnostic . PHP_EOL, 3, APPPATH . 'logs/router-api.log');
            $this->session->set_flashdata('error-sweet', 'Tes koneksi gagal: ' . htmlspecialchars(str_replace(chr(92), '/', $diagnostic), ENT_QUOTES, 'UTF-8'));
        } finally {
            $API->disconnect();
        }
        redirect('router');
    }
    public function cekconnection()
    {

        $id = $this->input->post('router');
        if ($id == null) {
            echo "<div class='badge badge-danger'>Router belum dipilih</div>";
        } else {
            $router = $this->db->get_where('router', ['id' => $id])->row_array();
            $ip = $router['ip_address'];
            $user = $router['username'];
            $pass = $router['password'];
            $port = $router['port'];
            $API = new Mikweb();
            $API->connect($ip, $user, $pass, $port);
            $resource = $API->comm("/system/resource/print");
            if ($resource['0']['uptime'] != null) {
                echo "<div class='badge badge-success'>Connected</div>";
            } else {
                echo "<div class='badge badge-danger'>Disconnect</div>";
            }
        }
    }
    public function reboot($id)
    {
        $router = $this->db->get_where('router', ['id' => $id])->row_array();
        $ip = $router['ip_address'];
        $user = $router['username'];
        $pass = $router['password'];
        $port = $router['port'];
        $API = new Mikweb();
        $API->connect($ip, $user, $pass, $port);
        $resource = $API->comm("/system/resource/print");
        if ($resource['0']['uptime'] != null) {
            $API->comm("/system/reboot");
            $this->session->set_flashdata('success-sweet', 'Router Berhasil di Reboot.');
        } else {
            $this->session->set_flashdata('error-sweet', 'Disconnect, silahkan periksa kembali koneksi anda');
        }
        redirect($_SERVER['HTTP_REFERER']);
    }
    function active()
    {
        $this->db->set('is_active', 1);
        $this->db->where('id', 1);
        $this->db->update('router');
        redirect($_SERVER['HTTP_REFERER']);
    }
    function nonactive()
    {
        $this->db->set('is_active', 0);
        $this->db->where('id', 1);
        $this->db->update('router');
        redirect($_SERVER['HTTP_REFERER']);
    }

    // OPEN ISOLIR 
    public function openisolir($no_services)
    {
        $customer = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
        openisolir($customer['no_services'], $customer['router']);
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function isolir($no_services)
    {
        $customer = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
        try {
            $result = isolir($customer['no_services'], $customer['router']);
            if (is_array($result) && isset($result['success'], $result['message'])) {
                $this->load->view('backend/router/isolation_result', [
                    'success' => $result['success'],
                    'message' => $result['message'],
                ]);
                return;
            }
        } catch (Throwable $e) {
            log_message('error', 'Router isolation failed for service ' . $no_services . ': ' . $e->getMessage());
            $this->load->view('backend/router/isolation_result', [
                'success' => false,
                'message' => 'Isolir gagal karena terjadi kesalahan saat berkomunikasi dengan router. Periksa log aplikasi.',
            ]);
            return;
        }

        redirect($_SERVER['HTTP_REFERER']);
    }
    public function kick($no_services)
    {
        $customer = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
        kick($customer['no_services'], $customer['router']);

        redirect($_SERVER['HTTP_REFERER']);
    }
}
