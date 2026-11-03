<?php
ob_start();
defined('BASEPATH') or exit('No direct script access allowed');

class Mikrotik extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        is_logged_in();
        $this->load->model(['setting_m', 'customer_m', 'services_m', 'bill_m', 'mikrotik_m', 'coverage_m']);
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
        if ($role == 0) {
            $params = [
                'role_id' => $this->session->userdata('role_id'),
            ];
            $this->db->insert('role_management', $params);
        }
        return $role;
    }
    private function _coverage()
    {
        $role = $this->_role();
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $operator = $this->db->get_where('cover_operator', ['operator' => $this->session->userdata('id'), 'role_id' => $this->session->userdata('role_id')])->result();
            if ($this->session->userdata('role_id') != 1 && count($operator) == 0) {
                $this->session->set_flashdata('error', 'Tidak ada coverage untuk akun anda');

                $row[] = '';
            }
            foreach ($operator as $roww) {
                $row[] = $roww->coverage_id;
            };
        }

        return $row;
    }
    private function conn()
    {
        if ($this->session->userdata('router') == null) {
            $this->session->set_flashdata('error-sweet', 'Session belum dibuat, silahkan pilih router dan klik dashboard');
            redirect('router');
        } else {
            $router = $this->db->get_where('router', ['id' => $this->session->userdata('router')])->row_array();
            $user = $router['username'];
            $ip = $router['ip_address'];
            $pass = $router['password'];
            $port = $router['port'];
            $API = new Mikweb();
            $API->connect($ip, $user, $pass, $port);
            return $API;
        }
    }
    public function unsetuser()
    {
        $this->db->set('user_mikrotik', '');
        $this->db->set('mode_user', '');
        $this->db->update('customer');
    }
    public function index()
    {
        if ($this->session->userdata('router') == null) {
            $this->session->set_flashdata('error-sweet', 'Session belum dibuat, silahkan pilih router dan klik dashboard');
            redirect('router');
        } else {
            $conn = $this->conn();
            $resource = $conn->comm("/system/resource/print");
            $hotspotuser = $conn->comm("/ip/hotspot/user/print", array("count-only" => "",  "~active-address" => "1.1.",));
            $hotspotactive = $conn->comm("/ip/hotspot/active/print", array("count-only" => "",  "~active-address" => "1.1.",));
            $pppactive = count($conn->comm("/ppp/active/print", array('?service' => 'pppoe')));
            $pppsecret = count($conn->comm("/ppp/secret/print"));
            $interface = $conn->comm("/interface/print");

            $data = [
                'uptime' => $resource[0]['uptime'],
                'version' => $resource[0]['version'],
                'freememory' => $resource[0]['free-memory'],
                'freehdd' => $resource[0]['free-hdd-space'],
                'hotspotactive' => $hotspotactive,
                'hotspotuser' => $hotspotuser,
                'pppactive' => $pppactive,
                'pppsecret' => $pppsecret,
                'interface' => $interface,
                'title' => 'Mikrotik',
                'user' => $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(),
                'company' => $this->db->get('company')->row_array()
            ];
            $this->template->load('mikrotik', 'backend/mikrotik/dashboard', $data);
        }
    }
    public function interface()
    {
        if ($this->session->userdata('router') == null) {
            $this->session->set_flashdata('error-sweet', 'Session belum dibuat, silahkan pilih router dan klik dashboard');
            redirect('router');
        } else {
            $conn = $this->conn();
            $interface = $conn->comm("/interface/print");
            // var_dump($interface);
            // die;
            $data = [

                'interface' => $interface,
                'title' => 'Interface',
                'user' => $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(),
                'company' => $this->db->get('company')->row_array()
            ];
            $this->template->load('mikrotik', 'backend/mikrotik/interface', $data);
        }
    }

    public function customer()
    {
        $data['title'] = 'Customer Mikrotik';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        // $this->template->load('mikrotik', 'backend/mikrotik/customer/data', $data);
        $this->template->load('backend', 'backend/mikrotik/customer/data-customer', $data);
    }
    public function client($no_services)
    {
        $customer = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
        if ($customer['mode_user'] == '' or $customer['user_mikrotik'] == '') {
            $this->session->set_flashdata('error-sweet', 'Data pelanggan belum di sinkronkan dengan user mikrotik');
            echo "<script>window.location='" . site_url('customer/edit/' . $customer['customer_id']) . "'; </script>";
        } else {
            $data['title'] = 'Client | ' . $no_services;
            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
            $data['customer'] = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
            $data['company'] = $this->db->get('company')->row_array();

            $this->template->load('backend', 'backend/mikrotik/customer/client', $data);
        }
    }
    public function dashboardreload()
    {

        $conn = $this->conn();
        $resource = $conn->comm("/system/resource/print");
        $hotspotuser = $conn->comm("/ip/hotspot/user/print", array("count-only" => "",  "~active-address" => "1.1.",));
        $hotspotactive = $conn->comm("/ip/hotspot/active/print", array("count-only" => "",  "~active-address" => "1.1.",));
        $pppactive = count($conn->comm("/ppp/active/print", array('?service' => 'pppoe')));
        $pppsecret = count($conn->comm("/ppp/secret/print"));
        $interface = $conn->comm("/interface/print");
        $routerboard = $conn->comm("/system/routerboard/print");
        $thisD = date("d");
        $thisM = strtolower(date("M"));
        $thisY = date("Y");
        if (strlen($thisD) == 1) {
            $thisD = "0" . $thisD;
        } else {
            $thisD = $thisD;
        }

        $today = $thisM . "/" . $thisD . "/" . $thisY;
        $idbl = $thisM . $thisY;
        $result = $conn->comm("/system/script/print", array(
            "?owner" => "$idbl",
        ));
        $totalVcrMonth = count($result);

        foreach ($result as $row) {
            if ((explode("-|-", $row['name'])[0]) == $today) {
                $reportToday += explode("-|-", $row['name'])[3];
                $totalVcrToday += count($row['source']);
            }
            $reportMonth += explode("-|-", $row['name'])[3];
        }

        if ($totalVcrToday == '') {
            $totalVcrToday = 0;
        }
        if ($reportToday == '') {
            $reportToday = 0;
        }
        if ($totalVcrMonth == '') {
            $totalVcrMonth = 0;
        }
        if ($reportMonth == '') {
            $reportMonth = 0;
        }
        $data = [
            'uptime' => $resource[0]['uptime'],
            'version' => $resource[0]['version'],
            'freememory' => $resource[0]['free-memory'],
            'freehdd' => $resource[0]['free-hdd-space'],
            'hotspotactive' => $hotspotactive,
            'hotspotuser' => $hotspotuser,
            'pppactive' => $pppactive,
            'pppsecret' => $pppsecret,
            'interface' => $interface,
            'resource' => $resource[0],
            'routerboard' => $routerboard[0],
            'title' => 'Mikrotik',
            'user' => $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(),
            'company' => $this->db->get('company')->row_array(),
            // MIKHMON
            'totalVcrToday' => $totalVcrToday,
            'totalVcrMonth' => $totalVcrMonth,
            'reportToday' => $reportToday,
            'reportMonth' => $reportMonth,
        ];

        $this->load->view('backend/mikrotik/reloaddashboard', $data);
    }



    public function traffic($interface)
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();

        $conn = $this->conn();
        $getinterfacetraffic = $conn->comm("/interface/monitor-traffic", array(
            "interface" => $interface,
            "once" => "",
        ));
        $ftx = $getinterfacetraffic[0]['tx-bits-per-second'];
        $frx = $getinterfacetraffic[0]['rx-bits-per-second'];

        $rows['name'] = 'Tx';
        $rows['data'][] = $ftx;
        $rows2['name'] = 'Rx';
        $rows2['data'][] = $frx;
        $result = array();

        array_push($result, $rows);
        array_push($result, $rows2);
        print json_encode($result);
    }
    public function trafficclient($no_services)
    {
        $customer = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $router = $this->db->get_where('router', ['id' => $customer['router']])->row_array();
        $username = $router['username'];
        $ip = $router['ip_address'];
        $pass = $router['password'];
        $port = $router['port'];
        $conn = new Mikweb();
        $conn->connect($ip, $username, $pass, $port);
        $user = $customer['user_mikrotik'];
        if ($customer['mode_user'] == 'PPPOE') {
            $getinterfacetraffic = $conn->comm("/interface/monitor-traffic", array(
                "interface" => "<pppoe-$user>",
                "once" => "",
            ));
            $ftx = $getinterfacetraffic[0]['tx-bits-per-second'];
            $frx = $getinterfacetraffic[0]['rx-bits-per-second'];
        }
        if ($customer['mode_user'] == 'Hotspot') {
            $getinterfacetraffic =  $conn->comm("/queue/simple/print", array(
                "?name" => "<hotspot-$user>",
            ));
            $rate =  $getinterfacetraffic['0']['rate'];
            $updl = explode("/", $rate);
            $up = $updl['0'];
            $dl = $updl['1'];
            $ftx = $up;
            $frx = $dl;
        }
        if ($customer['mode_user'] == 'Static') {
            $getinterfacetraffic =  $conn->comm("/queue/simple/print", array(
                "?name" => "$user",
            ));
            $rate =  $getinterfacetraffic['0']['rate'];
            $updl = explode("/", $rate);
            $up = $updl['0'];
            $dl = $updl['1'];
            $ftx = $up;
            $frx = $dl;
        }
        if ($customer['mode_user'] == 'Standalone') {
            $getinterfacetraffic = $conn->comm("/interface/monitor-traffic", array(
                "interface" => $user,
                "once" => "",
            ));
            $ftx = $getinterfacetraffic[0]['tx-bits-per-second'];
            $frx = $getinterfacetraffic[0]['rx-bits-per-second'];
        }



        $rows['name'] = 'Tx';
        $rows['data'][] = $ftx;
        $rows2['name'] = 'Rx';
        $rows2['data'][] = $frx;
        $result = array();

        array_push($result, $rows);
        array_push($result, $rows2);
        print json_encode($result);
    }



    // HOTSPOT
    public function hotspotuser()
    {
        $conn = $this->conn();
        $hotspotuser = $conn->comm("/ip/hotspot/user/print");
        $getprofile = $conn->comm("/ip/hotspot/user/profile/print");
        $server = $conn->comm("/ip/hotspot/print");
        $data = [
            'hotspotuser' => $hotspotuser,
            'title' => 'Users',
            'server' => $server,
            'profile' => $getprofile,
            'user' => $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(),
            'company' => $this->db->get('company')->row_array()
        ];
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('mikrotik', 'backend/mikrotik/hotspot/user', $data);
    }
    public function addHotspotUser()
    {
        $conn = $this->conn();
        $post = $this->input->post(null, true);
        if ($post['timelimit'] == "") {
            $timelimit = "0";
        } else {
            $timelimit = $post['timelimit'];
        }

        if ($post['user'] == $post['password']) {
            $usermode = "vc-";
        } else {
            $usermode = "up-";
        }

        $comment = $usermode . $post['comment'];
        $conn->comm("/ip/hotspot/user/add", array(
            "server" => $post['server'],
            "name" => $post['user'],
            "password" => $post['password'],
            "profile" => $post['profile'],
            "disabled" => "no",
            "limit-uptime" => $timelimit,
            "comment" => $comment,
        ));
        redirect('mikrotik/hotspotuser');
    }
    public function delHotspotUser($id)
    {
        $conn = $this->conn();
        $conn->comm("/ip/hotspot/user/remove", array(
            ".id" => '*' . $id,
        ));
        redirect('mikrotik/hotspotuser');
    }

    // DISABLE USER HOTSPOT
    public function disablehotspotuser($id)
    {
        $conn = $this->conn();
        $conn->comm("/ip/hotspot/user/disable", array(
            ".id" => '*' . $id,
        ));
        redirect('mikrotik/hotspotuser');
    }
    public function enablehotspotuser($id)
    {
        $conn = $this->conn();
        $conn->comm("/ip/hotspot/user/enable", array(
            ".id" => '*' . $id,
        ));
        redirect('mikrotik/hotspotuser');
    }
    // Hotspot ACTIVE
    public function hotspotactive()
    {
        $conn = $this->conn();
        $hotspotactive = $conn->comm("/ip/hotspot/active/print");
        // var_dump($hotspotactive);
        $data = [
            'hotspotactive' => $hotspotactive,
            'title' => 'Active',
            'user' => $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(),
            'company' => $this->db->get('company')->row_array()
        ];
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('mikrotik', 'backend/mikrotik/hotspot/active', $data);
    }

    // Hotspot Profile
    public function hotspotprofile()
    {
        $conn = $this->conn();
        $hotspotprofile = $conn->comm("/ip/hotspot/user/profile/print");
        // var_dump($hotspotactive);
        $data = [
            'hotspotprofile' => $hotspotprofile,
            'title' => 'Profile',
            'user' => $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(),
            'company' => $this->db->get('company')->row_array()
        ];
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('mikrotik', 'backend/mikrotik/hotspot/profile', $data);
    }

    // Hotspot Binding
    public function hotspotbinding()
    {
        $conn = $this->conn();
        $hotspotbinding = $conn->comm("/ip/hotspot/ip-binding/print");
        // var_dump($hotspotactive);
        $data = [
            'hotspotbinding' => $hotspotbinding,
            'title' => 'Binding',
            'user' => $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(),
            'company' => $this->db->get('company')->row_array()
        ];
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('mikrotik', 'backend/mikrotik/hotspot/binding', $data);
    }

    // Hotspot Host
    public function hotspothost()
    {
        $conn = $this->conn();
        $hotspothost = $conn->comm("/ip/hotspot/host/print");
        // var_dump($hotspotactive);
        $data = [
            'hotspothost' => $hotspothost,
            'title' => 'Host',
            'user' => $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(),
            'company' => $this->db->get('company')->row_array()
        ];
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('mikrotik', 'backend/mikrotik/hotspot/host', $data);
    }
    // RESET COUNTER

    public function resethotspot($no_services)
    {
        $customer = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
        $router = $this->db->get_where('router', ['id' => $customer['router']])->row_array();
        $username = $router['username'];
        $ip = $router['ip_address'];
        $pass = $router['password'];
        $port = $router['port'];
        $conn = new Mikweb();
        $conn->connect($ip, $username, $pass, $port);
        $user = $customer['user_mikrotik'];
        $getuser = $conn->comm("/ip/hotspot/user/print", array(
            "?name" => $user,

        ));
        $id = $getuser[0]['.id'];
        $conn->comm("/ip/hotspot/user/reset-counters", array(
            ".id" =>  $id,
        ));
        redirect('mikrotik/client/' . $no_services);
    }
    public function resetstatic($no_services)
    {
        $customer = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
        $router = $this->db->get_where('router', ['id' => $customer['router']])->row_array();
        $username = $router['username'];
        $ip = $router['ip_address'];
        $pass = $router['password'];
        $port = $router['port'];
        $conn = new Mikweb();
        $conn->connect($ip, $username, $pass, $port);
        $user = $customer['user_mikrotik'];
        $getuser = $conn->comm("/queue/simple/print", array(
            "?name" => $user,

        ));
        $id = $getuser[0]['.id'];

        $conn->comm("/queue/simple/reset-counters", array(
            ".id" => $id,
        ));
        redirect('mikrotik/client/' . $no_services);
    }
    public function resetpppoe($no_services)
    {
        $customer = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
        $router = $this->db->get_where('router', ['id' => $customer['router']])->row_array();
        $username = $router['username'];
        $ip = $router['ip_address'];
        $pass = $router['password'];
        $port = $router['port'];
        $conn = new Mikweb();
        $conn->connect($ip, $username, $pass, $port);
        $user = $customer['user_mikrotik'];
        $getuser = $conn->comm("/ppp/secret/print", array(
            "?name" => $user,

        ));
        $id = $getuser[0]['.id'];
        $conn->comm("/ppp/secret/set", array(
            ".id" => $id,
            "comment" => 0,
        ));
        redirect('mikrotik/client/' . $no_services);
    }
    public function resetstandalone($no_services)
    {
        $customer = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
        $router = $this->db->get_where('router', ['id' => $customer['router']])->row_array();
        $username = $router['username'];
        $ip = $router['ip_address'];
        $pass = $router['password'];
        $port = $router['port'];
        $conn = new Mikweb();
        $conn->connect($ip, $username, $pass, $port);
        $user = $customer['user_mikrotik'];
        $getuser = $conn->comm("/interface/print", array(
            "?name" => $user,

        ));
        $id = $getuser[0]['.id'];
        $conn->comm("/interface/set", array(
            ".id" => $id,
            "comment" => 0,
        ));
        redirect('mikrotik/client/' . $no_services);
    }

    // GET USER 
    public function getUserMikrotik()
    {
        $mode = $this->input->post('mode');
        $idrouter = $this->input->post('router');
        $router = $this->db->get_where('router', ['id' => $idrouter])->row_array();
        $user = $router['username'];
        $ip = $router['ip_address'];
        $pass = $router['password'];
        $port = $router['port'];
        $conn = new Mikweb();
        $conn->connect($ip, $user, $pass, $port);

        if ($mode == 'Hotspot') {
            $user = $conn->comm("/ip/hotspot/user/print");
            echo "<option value=''>Pilih User Hotspot</option>";
            foreach ($user as $data) {
                echo "<option value='{$data['name']}'>{$data['name']}</option>";
            }
        }
        if ($mode == 'PPPOE') {
            $user = $conn->comm("/ppp/secret/print", array('?service' => 'pppoe'));
            echo "<option value=''>Pilih User PPPOE</option>";
            foreach ($user as $data) {
                echo "<option value='{$data['name']}'>{$data['name']}</option>";
            }
        }
        if ($mode == 'Static') {
            $user = $conn->comm("/queue/simple/print");
            echo "<option value=''>Pilih User Queue</option>";
            foreach ($user as $data) {
                echo "<option value='{$data['name']}'>{$data['name']}</option>";
            }
        }
        if ($mode == 'Standalone') {
            $user = $conn->comm("/interface/print");
            echo "<option value=''>Pilih Interface</option>";
            foreach ($user as $data) {
                echo "<option value='{$data['name']}'>{$data['name']}</option>";
            }
        }
    }


    // PPP
    public function pppsecret()
    {
        $conn = $this->conn();
        $pppsecret = $conn->comm("/ppp/secret/print");
        $profile = $conn->comm("/ppp/profile/print");
        $data = [
            'pppsecret' => $pppsecret,
            'profile' => $profile,
            'title' => 'PPP Secret',
            'user' => $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(),
            'company' => $this->db->get('company')->row_array()
        ];
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('mikrotik', 'backend/mikrotik/ppp/ppp-secret', $data);
    }

    // PPPOE

    public function pppoeactive()
    {
        $conn = $this->conn();
        $pppoe = $conn->comm("/ppp/active/print", array('?service' => 'pppoe'));
        $pppoeactive = $conn->comm("/ppp/active/print", array('?service' => 'pppoe'));
        $pppoedisable = $conn->comm("/ppp/secret/print", array('?service' => 'pppoe', '?disabled' => 'true'));
        $pppoeexpired = $conn->comm("/ppp/secret/print", array('?service' => 'pppoe', '?profile' => 'EXPIRED'));
        // var_dump($pppoeactive);
        // die;
        $profile = $conn->comm("/ppp/profile/print");
        $data = [
            'pppoe' => $pppoe,
            'pppoecount' => count($pppoe),
            'pppoeactive' => count($pppoeactive),
            'pppoeexpired' => count($pppoeexpired),
            'pppoedisable' => count($pppoedisable),

            'profile' => $profile,
            'title' => 'PPPOE Active',
            'user' => $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(),
            'company' => $this->db->get('company')->row_array()
        ];
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('mikrotik', 'backend/mikrotik/ppp/pppoe', $data);
    }
    public function pppoe()
    {
        $conn = $this->conn();
        $pppoe = $conn->comm("/ppp/secret/print", array('?service' => 'pppoe'));
        $pppoeactive = $conn->comm("/ppp/active/print", array('?service' => 'pppoe'));
        $pppoedisable = $conn->comm("/ppp/secret/print", array('?service' => 'pppoe', '?disabled' => 'true'));
        $pppoeexpired = $conn->comm("/ppp/secret/print", array('?service' => 'pppoe', '?profile' => 'EXPIRED'));
        // var_dump($pppoedisable);
        // die;
        $profile = $conn->comm("/ppp/profile/print");
        $data = [
            'pppoe' => $pppoe,
            'pppoecount' => count($pppoe),
            'pppoeactive' => count($pppoeactive),
            'pppoeexpired' => count($pppoeexpired),
            'pppoedisable' => count($pppoedisable),

            'profile' => $profile,
            'title' => 'PPPOE',
            'user' => $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(),
            'company' => $this->db->get('company')->row_array()
        ];
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('mikrotik', 'backend/mikrotik/ppp/pppoe', $data);
    }
    public function pppoedisable()
    {
        $conn = $this->conn();
        $pppoedisable = $conn->comm("/ppp/secret/print", array('?service' => 'pppoe', '?disabled' => 'true'));


        $pppoeexpired = $conn->comm("/ppp/secret/print", array('?service' => 'pppoe', '?profile' => 'EXPIRED'));

        $profile = $conn->comm("/ppp/profile/print");
        $data = [
            'pppoe' => $pppoedisable,

            'pppoeexpired' => $pppoeexpired,
            'pppoedisable' => $pppoedisable,

            'profile' => $profile,
            'title' => 'PPPOE Disable',
            'user' => $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(),
            'company' => $this->db->get('company')->row_array()
        ];

        foreach ($pppoedisable as $dataa) {
            $cekcustomer = $this->db->get_where('customer', ['mode_user' => 'PPPOE', 'user_mikrotik' => $dataa['name'], 'connection' => 0, 'action' => 0, 'router' => $this->session->userdata('router')])->row_array();
            if ($cekcustomer > 0) {
                $this->db->set('connection', 1);
                $this->db->where('customer_id', $cekcustomer['customer_id']);
                $this->db->update('customer');
            }
        }
        foreach ($pppoeexpired as $dataa) {
            $cekcustomer = $this->db->get_where('customer', ['mode_user' => 'PPPOE', 'user_mikrotik' => $dataa['name'], 'connection' => 0, 'action' => 1, 'router' => $this->session->userdata('router')])->row_array();
            if ($cekcustomer > 0) {
                $this->db->set('connection', 1);
                $this->db->where('customer_id', $cekcustomer['customer_id']);
                $this->db->update('customer');
            }
        }
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('mikrotik', 'backend/mikrotik/ppp/pppoedisable', $data);
    }
    public function addPppoeUser()
    {
        $conn = $this->conn();
        $post = $this->input->post(null, true);

        if ($conn->comm("/ppp/secret/add", array(
            'name' => $post['name'],
            'profile' => $post['profile'],
            'password' => $post['password'],
            'service' => 'pppoe',

            'comment' => $post['comment'],
        ))) {
            $this->session->set_flashdata('success-sweet', 'Success');
            redirect('mikrotik/pppoe');
        } else {
            $this->session->set_flashdata('error-sweet', 'Gagal');
            redirect('mikrotik/pppoe');
        }
    }
    public function delPppoeUser($id)
    {
        $conn = $this->conn();
        $conn->comm("/ppp/secret/remove", array(
            ".id" => '*' . $id,
        ));


        redirect('mikrotik/pppoe');
    }
    // DISABLE USER PPPOE
    public function disablepppoeuser($id)
    {
        $conn = $this->conn();
        $conn->comm("/ppp/secret/disable", array(
            ".id" => '*' . $id,
        ));
        redirect('mikrotik/pppoe');
    }
    public function enablepppoeuser($id)
    {
        $conn = $this->conn();
        $conn->comm("/ppp/secret/enable", array(
            ".id" => '*' . $id,
        ));
        redirect('mikrotik/pppoe');
    }

    // PROFILE PPP
    public function profileppp()
    {
        $conn = $this->conn();
        $profileppp = $conn->comm("/ppp/profile/print");
        $data = [
            'profileppp' => $profileppp,
            'title' => 'Profile PPP',
            'user' => $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(),
            'company' => $this->db->get('company')->row_array()
        ];
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->template->load('mikrotik', 'backend/mikrotik/ppp/ppp-profile', $data);
    }
    public function addprofileppp()
    {
        $conn = $this->conn();
        $post = $this->input->post(null, true);
        if ($post['ratelimit'] == "") {
            $ratelimit = '';
        } else {
            $ratelimit = $post['ratelimit'];
        }

        $conn->comm("/ppp/profile/add", array(
            'name' => $post['name'],
            'rate-limit' => $ratelimit,
            'only-one' => $post['onlyone'],
            'comment' => $post['comment'],

        )); {
            $this->session->set_flashdata('success-sweet', 'Success');
            redirect('mikrotik/profileppp');
        }
    }
    // GET USER PROFILE
    public function getUserProfileMikrotik()
    {
        $mode = $this->input->post('mode');
        $idrouter = $this->input->post('router');
        $router = $this->db->get_where('router', ['id' => $idrouter])->row_array();
        $user = $router['username'];
        $ip = $router['ip_address'];
        $pass = $router['password'];
        $port = $router['port'];
        $conn = new Mikweb();
        $conn->connect($ip, $user, $pass, $port);
        if ($mode == 'PPPOE') {

            $user = $conn->comm("/ppp/profile/print");

            foreach ($user as $data) {
                echo "<option value='{$data['name']}'>{$data['name']}</option>";
            }
        }
        if ($mode == 'Hotspot') {
            $user = $conn->comm("/ip/hotspot/user/profile/print");

            foreach ($user as $data) {
                echo "<option value='{$data['name']}'>{$data['name']}</option>";
            }
        }

        if ($mode == 'Static') {
            $user = $conn->comm("/queue/tree/print");
            foreach ($user as $data) {
                echo "<option value='{$data['name']}'>{$data['name']}</option>";
            }
        }
    }
    // CUSTOMER

    public function editcustomer()
    {
        $post = $this->input->post(null, TRUE);
        $this->mikrotik_m->editcustomer($post);
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

                $API->comm("/ppp/secret/add", array(
                    'name' => $post['user_mikrotik'],
                    'profile' => $post['profile'],
                    'password' => $post['passwordmikrotik'],
                    'service' => 'pppoe',
                    'comment' => '0',
                ));
            } elseif ($post['mode_user'] == 'Static') {
                $API->comm("/queue/simple/add", array(
                    'name' => $post['user_mikrotik'],
                    'target' => $post['target'],
                    'max-limit' => $post['uploadlimit'] . $post['upload'] . '/' . $post['downloadlimit'] . $post['download']
                ));
            } elseif ($post['mode_user'] == 'Standalone') {
                # code...
            }
        }
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success-sweet', 'Data User berhasil diperbaharui');
        }
        echo "<script>window.location='" . site_url('mikrotik/customer') . "'; </script>";
    }

    public function setting()
    {
        $data['title'] = 'Schedule';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['router'] = $this->db->get_where('router', ['id' => 1])->row_array();
        $data['other'] = $this->db->get('other')->row_array();
        $scheduleNames = ['ISOLIR-MY-WIFI', 'COUNT-PPPOE', 'GENERATE-BILL', 'REMINDER-BILL', 'REMINDER-BILL-TELEGRAM', 'BACKUP-DB'];
        $data['schedules'] = array_fill_keys($scheduleNames, []);
        $data['schedule_available'] = false;

        if (!$data['router']) {
            $data['schedule_error'] = 'Data MikroTik belum tersedia. Halaman schedule dibuka tanpa status dari router.';
        } else {
            $API = new Mikweb();
            $API->attempts = 1;
            $API->timeout = 1;
            $API->delay = 0;

            try {
                if ($API->connect($data['router']['ip_address'], $data['router']['username'], $data['router']['password'], $data['router']['port'])) {
                    $result = $API->comm('/system/scheduler/print');
                    foreach (is_array($result) ? $result : [] as $schedule) {
                        if (isset($schedule['name']) && array_key_exists($schedule['name'], $data['schedules'])) {
                            $data['schedules'][$schedule['name']] = $schedule;
                        }
                    }
                    $data['schedule_available'] = true;
                } else {
                    $data['schedule_error'] = 'Tidak dapat terhubung ke MikroTik. Halaman dibuka tanpa status schedule; pengaturan tidak dapat disimpan.';
                }
            } catch (RuntimeException | TypeError $exception) {
                log_message('error', 'Unable to load MikroTik schedules: ' . $exception->getMessage());
                $data['schedule_error'] = 'Koneksi MikroTik terputus saat membaca schedule. Halaman dibuka tanpa status schedule.';
            } finally {
                $API->disconnect();
            }
        }

        $this->template->load('backend', 'backend/mikrotik/setting/data', $data);
    }
    public function editsetting()
    {
        $post = $this->input->post(null, TRUE);
        $router = $this->db->get('router')->row_array();
        $ip = $router['ip_address'];
        $user = $router['username'];
        $pass = $router['password'];
        $port = $router['port'];
        $API = new Mikweb();
        if (!$API->connect($ip, $user, $pass, $port)) {
            $this->session->set_flashdata('error-sweet', 'Tidak dapat terhubung ke MikroTik. Pengaturan belum disimpan.');
            redirect('mikrotik/setting');
            return;
        }
        $getisolir = $API->comm("/system/scheduler/print", array('?name' => 'ISOLIR-MY-WIFI',));
        $idisolir = $getisolir[0]['.id'] ?? null;

        $getcountpppoe = $API->comm("/system/scheduler/print", array('?name' => 'COUNT-PPPOE',));
        $idcountpppoe = $getcountpppoe[0]['.id'] ?? null;


        $getcreatebill = $API->comm("/system/scheduler/print", array('?name' => 'GENERATE-BILL',));
        $idcreatebill = $getcreatebill[0]['.id'] ?? null;

        $getreminderduedate = $API->comm("/system/scheduler/print", array('?name' => 'REMINDER-BILL',));
        $idreminderduedate = $getreminderduedate[0]['.id'] ?? null;
        $getreminderduedatebefore = $API->comm("/system/scheduler/print", array('?name' => 'REMINDER-BEFORE-DUE',));
        $idreminderduedatebefore = $getreminderduedatebefore[0]['.id'] ?? null;


        $getreminderduedatetelegram = $API->comm("/system/scheduler/print", array('?name' => 'REMINDER-BILL-TELEGRAM',));
        $idreminderduedatetelegram = $getreminderduedatetelegram[0]['.id'] ?? null;


        $getbackupdb = $API->comm("/system/scheduler/print", array('?name' => 'BACKUP-DB',));
        $idbackupdb = $getbackupdb[0]['.id'] ?? null;





















        if (strlen($post['jamreminderduedatetelegram']) == 1) {
            $jamreminderduedatetelegram = '0' . $post['jamreminderduedatetelegram'];
        } else {
            $jamreminderduedatetelegram = $post['jamreminderduedatetelegram'];
        }
        if (strlen($post['menitreminderduedatetelegram']) == 1) {
            $menitreminderduedatetelegram = '0' . $post['menitreminderduedatetelegram'];
        } else {
            $menitreminderduedatetelegram = $post['menitreminderduedatetelegram'];
        }




        if (strlen($post['jambackupdb']) == 1) {
            $jambackupdb = '0' . $post['jambackupdb'];
        } else {
            $jambackupdb = $post['jambackupdb'];
        }
        if (strlen($post['menitbackupdb']) == 1) {
            $menitbackupdb = '0' . $post['menitbackupdb'];
        } else {
            $menitbackupdb = $post['menitbackupdb'];
        }


        if ($post['isolir'] == 'true') {
            if (count($getisolir) == 0) {
                $API->comm("/system/scheduler/add", array(
                    'name' => 'ISOLIR-MY-WIFI',
                    'comment' => 'ISOLIR-MY-WIFI',

                    'interval' => $post['intervalisolir'] . 'm',
                    'disabled' => 'yes',
                    'on-event' => '/tool fetch url="' . base_url('front/isolir/' . $post['key_apps']) . '" keep-result=no',

                ));
            } else {
                $API->comm("/system/scheduler/disable", array(
                    ".id" =>  $idisolir,
                ));
            }
        } else {
            if (count($getisolir) == 0) {
                $API->comm("/system/scheduler/add", array(
                    'name' => 'ISOLIR-MY-WIFI',
                    'comment' => 'ISOLIR-MY-WIFI',

                    'interval' => $post['intervalisolir'] . 'm',
                    'on-event' => '/tool fetch url="' . base_url('front/isolir/' . $post['key_apps']) . '" keep-result=no',

                ));
            } else {
                $API->comm("/system/scheduler/set", array(
                    ".id" =>  $idisolir,
                    "disabled" => 'no',
                    'interval' => $post['intervalisolir'] . 'm',
                    'on-event' => '/tool fetch url="' . base_url('front/isolir/' . $post['key_apps']) . '" keep-result=no',


                ));
            }
        }

        if ($post['countpppoe'] == 'true') {
            if (count($getcountpppoe) == 0) {
                $API->comm("/system/scheduler/add", array(
                    'name' => 'COUNT-PPPOE',
                    'comment' => 'COUNT-PPPOE',

                    'interval' => $post['intervalcountpppoe'] . 'm',
                    'disabled' => 'yes',
                    'on-event' => '/tool fetch url="' . base_url('front/countpppoe/' . $post['key_apps']) . '" keep-result=no',

                ));
            } else {
                $API->comm("/system/scheduler/disable", array(
                    ".id" =>  $idcountpppoe,
                ));
            }
        } else {
            if (count($getcountpppoe) == 0) {
                $API->comm("/system/scheduler/add", array(
                    'name' => 'COUNT-PPPOE',
                    'comment' => 'COUNT-PPPOE',

                    'interval' => $post['intervalcountpppoe'] . 'm',
                    'on-event' => '/tool fetch url="' . base_url('front/countpppoe/' . $post['key_apps']) . '" keep-result=no',

                ));
            } else {
                $API->comm("/system/scheduler/set", array(
                    ".id" =>  $idcountpppoe,
                    "disabled" => 'no',
                    'interval' => $post['intervalcountpppoe'] . 'm',
                    'on-event' => '/tool fetch url="' . base_url('front/countpppoe/' . $post['key_apps']) . '" keep-result=no',

                ));
            }
        }




        if ($post['createbill'] == 'true') {
            if (count($getcreatebill) == 0) {
                $API->comm("/system/scheduler/add", array(
                    'name' => 'GENERATE-BILL',
                    'comment' => 'GENERATE-BILL',

                    'interval' => '1d',
                    'disabled' => 'yes',
                    'on-event' => '/tool fetch url="' . base_url('front/createbill/' . $post['key_apps']) . '" keep-result=no',

                ));
            } else {
                $API->comm("/system/scheduler/disable", array(
                    ".id" =>  $idcreatebill,
                ));
            }
        } else {
            if (count($getcreatebill) == 0) {
                $API->comm("/system/scheduler/add", array(
                    'name' => 'GENERATE-BILL',
                    'comment' => 'GENERATE-BILL',

                    'interval' => '1d',
                    // 'disabled' => 'yes',
                    'on-event' => '/tool fetch url="' . base_url('front/createbill/' . $post['key_apps']) . '" keep-result=no',

                ));
            } else {
                $API->comm("/system/scheduler/set", array(
                    ".id" =>  $idcreatebill,
                    "disabled" => 'no',

                    'on-event' => '/tool fetch url="' . base_url('front/createbill/' . $post['key_apps']) . '" keep-result=no',
                ));
            }
        }


        if ($post['reminderduedate'] == 'true') {
            if (count($getreminderduedate) == 0) {
                $API->comm("/system/scheduler/add", array(
                    'name' => 'REMINDER-BILL',
                    'comment' => 'REMINDER-BILL',

                    'interval' => '1d',
                    'disabled' => 'yes',
                    'on-event' => '/tool fetch url="' . base_url('front/reminderduedate/' . $post['key_apps']) . '" keep-result=no',

                ));
                $API->comm("/system/scheduler/add", array(
                    'name' => 'REMINDER-BEFORE-DUE',
                    'comment' => 'REMINDER-BEFORE-DUE',

                    'interval' => '1d',
                    'disabled' => 'yes',
                    'on-event' => '/tool fetch url="' . base_url('front/reminder/' . $post['key_apps']) . '" keep-result=no',

                ));
            } else {
                $API->comm("/system/scheduler/disable", array(
                    ".id" =>  $idreminderduedate,
                ));
                $API->comm("/system/scheduler/disable", array(
                    ".id" =>  $idreminderduedatebefore,
                ));
            }
        } else {
            if (count($getreminderduedate) == 0) {
                $API->comm("/system/scheduler/add", array(
                    'name' => 'REMINDER-BILL',
                    'comment' => 'REMINDER-BILL',

                    'interval' => '1d',
                    // 'disabled' => 'yes',
                    'on-event' => '/tool fetch url="' . base_url('front/reminderduedate/' . $post['key_apps']) . '" keep-result=no',

                ));
                $API->comm("/system/scheduler/add", array(
                    'name' => 'REMINDER-BEFORE-DUE',
                    'comment' => 'REMINDER-BEFORE-DUE',

                    'interval' => '1d',
                    // 'disabled' => 'yes',
                    'on-event' => '/tool fetch url="' . base_url('front/reminder/' . $post['key_apps']) . '" keep-result=no',

                ));
            } else {
                $API->comm("/system/scheduler/set", array(
                    ".id" =>  $idreminderduedate,
                    "disabled" => 'no',
                    'on-event' => '/tool fetch url="' . base_url('front/reminderduedate/' . $post['key_apps']) . '" keep-result=no',

                ));
                $API->comm("/system/scheduler/set", array(
                    ".id" =>  $idreminderduedatebefore,
                    "disabled" => 'no',
                    'on-event' => '/tool fetch url="' . base_url('front/reminder/' . $post['key_apps']) . '" keep-result=no',

                ));
            }
        }

        if ($post['reminderduedatetelegram'] == 'true') {
            if (count($getreminderduedatetelegram) == 0) {
                $API->comm("/system/scheduler/add", array(
                    'name' => 'REMINDER-BILL-TELEGRAM',
                    'comment' => 'REMINDER-BILL-TELEGRAM',
                    'start-time' => '' . $jamreminderduedatetelegram . ':' . $menitreminderduedatetelegram . ':00',
                    'interval' => '1d',
                    'disabled' => 'yes',
                    'on-event' => '/tool fetch url="' . base_url('front/reminderduedatetelegram/' . $post['key_apps']) . '" keep-result=no',

                ));
            } else {
                $API->comm("/system/scheduler/disable", array(
                    ".id" =>  $idreminderduedatetelegram,
                ));
            }
        } else {
            if (count($getreminderduedatetelegram) == 0) {
                $API->comm("/system/scheduler/add", array(
                    'name' => 'REMINDER-BILL-TELEGRAM',
                    'comment' => 'REMINDER-BILL-TELEGRAM',
                    'start-time' => '' . $jamreminderduedatetelegram . ':' . $menitreminderduedatetelegram . ':00',
                    'interval' => '1d',
                    // 'disabled' => 'yes',
                    'on-event' => '/tool fetch url="' . base_url('front/reminderduedatetelegram/' . $post['key_apps']) . '" keep-result=no',

                ));
            } else {
                $API->comm("/system/scheduler/set", array(
                    ".id" =>  $idreminderduedatetelegram,
                    "disabled" => 'no',
                    'on-event' => '/tool fetch url="' . base_url('front/reminderduedatetelegram/' . $post['key_apps']) . '" keep-result=no',
                    'start-time' => '' . $jamreminderduedatetelegram . ':' . $menitreminderduedatetelegram . ':00',
                ));
            }
        }


        if ($post['backupdb'] == 'true') {
            if (count($getbackupdb) == 0) {
                $API->comm("/system/scheduler/add", array(
                    'name' => 'BACKUP-DB',
                    'comment' => 'BACKUP-DB',
                    'start-time' => '' . $jambackupdb . ':' . $menitbackupdb . ':00',
                    'interval' => '1d',
                    'disabled' => 'yes',
                    'on-event' => '/tool fetch url="' . base_url('front/backupdb/' . $post['key_apps']) . '" keep-result=no',

                ));
            } else {
                $API->comm("/system/scheduler/disable", array(
                    ".id" =>  $idbackupdb,
                ));
            }
        } else {
            if (count($getbackupdb) == 0) {
                $API->comm("/system/scheduler/add", array(
                    'name' => 'BACKUP-DB',
                    'comment' => 'BACKUP-DB',
                    'start-time' => '' . $jambackupdb . ':' . $menitbackupdb . ':00',
                    'interval' => '1d',
                    // 'disabled' => 'yes',
                    'on-event' => '/tool fetch url="' . base_url('front/backupdb/' . $post['key_apps']) . '" keep-result=no',

                ));
            } else {
                $API->comm("/system/scheduler/set", array(
                    ".id" =>  $idbackupdb,
                    "disabled" => 'no',
                    'on-event' => '/tool fetch url="' . base_url('front/backupdb/' . $post['key_apps']) . '" keep-result=no',
                    'start-time' => '' . $jambackupdb . ':' . $menitbackupdb . ':00',
                ));
            }
        }
        if ($post['isolir'] == 'false') {
            $this->db->set('sch_isolir', 1);
        } else {
            $this->db->set('sch_isolir', 0);
        }
        if ($post['createbill'] == 'false') {
            $this->db->set('sch_createbill', 1);
        } else {
            $this->db->set('sch_createbill', 0);
        }
        if ($post['reminderduedate'] == 'false') {
            $this->db->set('sch_due', 1);
            $this->db->set('sch_before_due', 1);
        } else {
            $this->db->set('sch_due', 0);
            $this->db->set('sch_before_due', 0);
        }
        $this->db->set('key_apps', $post['key_apps']);
        $this->db->set('date_create', $post['date_create']);
        $this->db->update('other');
        $this->session->set_flashdata('success-sweet', 'Pengaturan schedule berhasil disimpan.');
        redirect('mikrotik/setting');
    }

    // STANDALONE

    public function dashboard($id)
    {

        $this->session->set_userdata('router', $id);
        redirect('mikrotik');
    }

    public function config()
    {
        if ($this->session->userdata('router') == null) {
            $this->session->set_flashdata('error', 'Session belum dibuat');
            redirect('router');
        };
        $data['title'] = 'Router';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['router'] = $this->db->get_where('router', ['id' => $this->session->userdata('router')])->row_array();
        $this->template->load('backend', 'backend/router/config', $data);
    }
    public function editcs($noservices)
    {
        $query  = $this->customer_m->getNSCustomer($noservices);
        if ($query->num_rows() > 0) {
            $data['customer'] = $query->row();
            $data['title'] = 'Edit Customer';
            $data['company'] = $this->db->get('company')->row_array();
            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
            $this->template->load('mikrotik', 'backend/mikrotik/customer/edit-customer', $data);
        } else {
            echo "<script> alert ('Data tidak ditemukan');";
            echo "window.location='" . site_url('mikrotik/customer') . "'; </script>";
        }
    }

    public function refreshstandalone($no_services)
    {
        $customer = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
        $router = $this->db->get_where('router', ['id' => $customer['router']])->row_array();
        $user = $router['username'];
        $ip = $router['ip_address'];
        $pass = $router['password'];
        $port = $router['port'];
        $userclient = $customer['user_mikrotik'];
        $API = new Mikweb();
        $API->connect($ip, $user, $pass, $port);
        // RESET PPPOE
        $getusage = $API->comm("/interface/print", array(
            "?name" => "$userclient",
        ));
        $usage = $getusage['0']['tx-byte'] + $getusage['0']['rx-byte'];
        // $getuser = $API->comm("/interface/ethernet/print");

        $id = $getusage[0]['.id'];


        if ($getusage['0']['comment'] == '') {
            $API->comm("/interface/set", array(
                ".id" =>  $id,
                "comment" => $usage,
            ));
        } else {
            $API->comm("/interface/set", array(
                ".id" =>  $id,
                "comment" => $getusage['0']['comment'] + $usage,
            ));
        }

        $cekscript = $API->comm("/system/script/print", array('?name' => "reset-standalone-$userclient"));
        $id = $cekscript[0]['.id'];
        if (count($cekscript) == 0) {
            $API->comm("/system/script/add", array(
                "name" =>  "reset-standalone-$userclient",
                "source" => "/interface reset-counters $userclient",
            ));
        } else {
            $API->comm("/system/script/run", array(
                ".id" => $id,
            ));
        }
        redirect('mikrotik/client/' . $no_services);
    }
    public function refreshpppoe($no_services)
    {
        $customer = $this->db->get_where('customer', ['no_services' => $no_services])->row_array();
        $router = $this->db->get_where('router', ['id' => $customer['router']])->row_array();
        $user = $router['username'];
        $ip = $router['ip_address'];
        $pass = $router['password'];
        $port = $router['port'];
        $userclient = $customer['user_mikrotik'];
        $API = new Mikweb();
        $API->connect($ip, $user, $pass, $port);
        // RESET PPPOE
        $getusage = $API->comm("/interface/print", array(
            "?name" => "<pppoe-$userclient>",
        ));
        $usage = $getusage['0']['tx-byte'] + $getusage['0']['rx-byte'];
        // var_dump($usage);
        $getuser = $API->comm("/ppp/secret/print", array('?service' => 'pppoe', '?name' => $userclient,));
        $id = $getuser[0]['.id'];

        if ($getuser['0']['comment'] == '') {
            $API->comm("/ppp/secret/set", array(
                ".id" =>  $id,
                "comment" => $usage,
            ));
        } else {
            $API->comm("/ppp/secret/set", array(
                ".id" =>  $id,
                "comment" => $getuser['0']['comment'] + $usage,
            ));
        }

        $cekscript = $API->comm("/system/script/print", array('?name' => "reset-pppoe-$userclient"));
        $id = $cekscript[0]['.id'];
        if (count($cekscript) == 0) {
            $API->comm("/system/script/add", array(
                "name" =>  "reset-pppoe-$userclient",
                "source" => "/interface reset-counters <pppoe-$userclient>",
            ));
        } else {
            $API->comm("/system/script/run", array(
                ".id" => $id,
            ));
        }
        redirect('mikrotik/client/' . $no_services);
    }

    public function isolir()
    {
        $data['title'] = 'Isolir';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['customer'] = $this->customer_m->getCustomerIsolir()->result();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('mikrotik', 'backend/mikrotik/customer/old', $data);
    }

    public function openisolir()
    {
        $listrouter = $this->db->get('router')->result();
        foreach ($listrouter as $router) {
            $totalcustomer = $this->db->get_where('customer', ['router' => $router->id])->num_rows();
            if ($totalcustomer > 0) {
                $billpasca = $this->customer_m->getisolirpasca($router->id)->result();
                $user = $router->username;
                $ip = $router->ip_address;
                $pass = $router->password;
                $port = $router->port;
                $API = new Mikweb();
                $API->connect($ip, $user, $pass, $port);



                // OPEN ISOLIR
                $customerpppoe = $this->db->get_where('customer', ['c_status' => 'Aktif', 'mode_user' => 'PPPOE'])->num_rows();

                if ($customerpppoe > 0) {
                    $pppoedisable = $API->comm("/ppp/secret/print", array('?service' => 'pppoe', '?disabled' => 'true'));
                    // var_dump($pppoedisable);
                    if ($this->session->userdata('email') == 'ginginabdulgoni@gmail.com') {
                        echo 'Disable user';
                    }
                    foreach ($pppoedisable as $data) {
                        $customer = $this->db->get_where('customer', ['user_mikrotik' => $data['name'], 'router' => $router->id])->row_array();
                        // cek bill
                        $bill = $this->customer_m->getopenisolir($customer['router'], $customer['no_services'])->result();
                        // var_dump($bill);
                        if (count($bill) == 0) {
                            $API->comm("/ppp/secret/enable", array(
                                ".id" => $data['.id'],
                            ));
                        };
                        if ($this->session->userdata('email') == 'ginginabdulgoni@gmail.com') {
                            echo '<br>';
                            echo $data['name'];
                            echo '<br>';
                        }
                    }
                    $pppoeexpired = $API->comm("/ppp/secret/print", array('?service' => 'pppoe', '?profile' => 'EXPIRED'));
                    if ($this->session->userdata('email') == 'ginginabdulgoni@gmail.com') {
                        echo 'Expired user';
                    }
                    foreach ($pppoeexpired as $data) {
                        $customer = $this->db->get_where('customer', ['user_mikrotik' => $data['name'], 'router' => $router->id])->row_array();
                        // cek bill
                        $bill = $this->customer_m->getopenisolir($customer['router'], $customer['no_services'])->result();
                        // var_dump($bill);
                        if (count($bill) == 0) {
                            $API->comm("/ppp/secret/set", array(
                                ".id" => $data['.id'],
                                "profile" => $customer['user_profile'],
                            ));
                        };
                        if ($this->session->userdata('email') == 'ginginabdulgoni@gmail.com') {
                            echo '<br>';
                            echo $data['name'];
                            echo '<br>';
                        }
                    }
                }


                $customerhotspot = $this->db->get_where('customer', ['c_status' => 'Aktif', 'mode_user' => 'Hotspot'])->num_rows();
                if ($customerhotspot > 0) {
                    $hotspotdisable = $API->comm("/ip/hotspot/user/print", array('?disabled' => 'true'));
                    // var_dump($hotspotdisable);
                    if ($this->session->userdata('email') == 'ginginabdulgoni@gmail.com') {
                        echo 'Disable user Hotspot';
                    }
                    foreach ($hotspotdisable as $data) {
                        $customer = $this->db->get_where('customer', ['user_mikrotik' => $data['name'], 'router' => $router->id])->row_array();
                        // cek bill
                        $bill = $this->customer_m->getopenisolir($customer['router'], $customer['no_services'])->result();
                        // var_dump($bill);
                        if (count($bill) == 0) {
                            $API->comm("/ip/hotspot/user/enable", array(
                                ".id" => $data['.id'],
                            ));
                        };
                        if ($this->session->userdata('email') == 'ginginabdulgoni@gmail.com') {
                            echo '<br>';
                            echo $data['name'];
                            echo '<br>';
                        }
                    }
                    $hotspotexpired = $API->comm("/ip/hotspot/user/print", array('?profile' => 'EXPIRED'));
                    if ($this->session->userdata('email') == 'ginginabdulgoni@gmail.com') {
                        echo 'Expired user Hotspot';
                    }
                    foreach ($hotspotexpired as $data) {
                        $customer = $this->db->get_where('customer', ['user_mikrotik' => $data['name'], 'router' => $router->id])->row_array();
                        // cek bill
                        $bill = $this->customer_m->getopenisolir($customer['router'], $customer['no_services'])->result();
                        // var_dump($bill);
                        if (count($bill) == 0) {
                            $API->comm("/ip/hotspot/user/set", array(
                                ".id" => $data['.id'],
                                "profile" => $customer['user_profile'],
                            ));
                        };
                        if ($this->session->userdata('email') == 'ginginabdulgoni@gmail.com') {
                            echo '<br>';
                            echo $data['name'];
                            echo '<br>';
                        }
                    }
                }
                $customerstatic = $this->db->get_where('customer', ['c_status' => 'Aktif', 'mode_user' => 'Static'])->num_rows();
                if ($customerstatic > 0) {
                    // disable user
                    $userdrop = $API->comm("/ip/firewall/filter/print", array("?action" => 'drop'));
                    // var_dump($userdrop);
                    // die;

                    // echo  $router->id;
                    foreach ($userdrop as $data) {
                        $no_services = explode("|", $data['comment']);
                        if ($no_services['0'] == 'ISOLIR' && $no_services['1'] != '') {
                            $bill = $this->customer_m->getopenisolir($router->id, $no_services['1'])->result();
                            if (count($bill) == 0) {
                                // echo $data['.id'];
                                $API->comm("/ip/firewall/filter/remove", array(
                                    ".id" => $data['.id'],
                                    "?comment" => 'ISOLIR|' . $no_services['1'],
                                ));
                            };
                        }
                    }
                }
            }
        }
    }



    public function getuserprofile()
    {
        $post = $this->input->post(null, true);

        $router = $this->db->get_where('router', ['id' => $post['router']])->row_array();
        $user = $router['username'];
        $ip = $router['ip_address'];
        $pass = $router['password'];
        $port = $router['port'];
        $API = new Mikweb();
        $API->connect($ip, $user, $pass, $port);
        if ($post['mode'] == 'PPPOE') {
            $userprofile = $API->comm("/ppp/secret/print", array('?service' => 'pppoe', '?name' => $post['user'],));
            $data = [
                'profile' => $userprofile['0']['profile'],

            ];
            echo json_encode($data);
        }
        if ($post['mode'] == 'Hotspot') {
            $userprofile = $API->comm("/ip/hotspot/user/print", array('?name' => $post['user'],));
            $data = [
                'profile' => $userprofile['0']['profile'],

            ];
            echo json_encode($data);
        }
        if ($post['mode'] == 'Static') {

            $data = [
                'profile' => '',

            ];
            echo json_encode($data);
        }
    }

    // Server side
    public function getdatacustomer()
    {
        $result = $this->mikrotik_m->getdatacustomer();
        $data = [];
        $no = $_POST['start'];
        foreach ($result as $result) {
            if ($result->auto_isolir == 1) {
                $isolir = 'Aktif';
            } else {
                $isolir = 'Tidak Aktif';
            }
            if ($result->action == 1) {
                $action = 'Pindah Profile <br> Profile Default : ' . $result->user_profile;
            } else {
                $action = 'Disable User';
            }
            $row = array();
            $row[] = ++$no;
            $row[] = $result->no_services . '<br><a href="' . site_url('mikrotik/client/' . $result->no_services) . '" class="badge badge-success">Detail</a>';
            $row[] = $result->name;
            $row[] = $result->alias;
            $row[] = $result->mode_user;
            $row[] = $result->user_mikrotik;


            $usage = $this->mikrotik_m->usagethismonth($result->no_services)->result();
            $totalusage = 0;
            foreach ($usage as $c => $usage) {
                $totalusage += $usage->count_usage;
            }

            $last = $this->mikrotik_m->lastusage($result->no_services)->row_array();
            if ($last > 0) {
                $lastupdate = '<br>Last Update ' . date('d M Y - H:i:s', $last['last_update']);
            } else {
                $lastupdate = '';
            }
            if ($totalusage > 0) {

                $row[] = formatBites($totalusage, 2) . ' <a href="' . site_url('mikrotik/usage/' . $result->no_services) . '" title="Edit"><i class="fa fa-eye"></i></a> ' . $lastupdate;
            } else {
                $row[] = '';
            }
            $row[] = $result->due_date;
            $row[] = $isolir . '<br> ' . $action;
            $row[] = '<a href="' . site_url('customer/edit/' . $result->customer_id) . '" title="Edit"><i class="fa fa-edit" style="font-size:25px"></i></a>';
            // $row[] = $status;
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->mikrotik_m->count_all_data(),
            "recordsFiltered" => $this->mikrotik_m->count_filtered_data(),
            "data" => $data,
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
    public function syncfromdb($id)
    {
        $users = $this->db->get_where('customer', ['router => $id'])->result();
        $router = $this->db->get_where('router', ['id' => $id])->row_array();
        $user = $router['username'];
        $ip = $router['ip_address'];
        $pass = $router['password'];
        $port = $router['port'];
        $API = new Mikweb();
        $API->connect($ip, $user, $pass, $port);
        foreach ($users as $data) {
            echo $data->name;
            echo '<br>';
            $API->comm("/ip/hotspot/user/add", array(
                "server" => 'all',
                'name' => $data->user_mikrotik,
                'profile' => $data->user_profile,
                'password' => 12345,
                "disabled" => "no",

                'comment' => 0,
            ));
            // if ($data->mode_user == 'PPPOE') {
            //     $API->comm("/ppp/secret/add", array(
            //         'name' => $data->user_mikrotik,
            //         'profile' => $data->user_profile,
            //         'password' => 12345,
            //         'service' => 'pppoe',

            //         'comment' => 0,
            //     ));
            // }
            // if ($data->mode_user == 'Hotspot') {
            //     $API->comm("/ip/hotspot/user/add", array(
            //         "server" => 'all',
            //         'name' => $data->user_mikrotik,
            //         'profile' => $data->user_profile,
            //         'password' => 12345,
            //         "disabled" => "no",

            //         'comment' => 0,
            //     ));
            // }
        }
    }

    public function usage($noservices = null)
    {
        $data['title'] = 'Usage';

        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        if ($noservices != null) {
            $data['no_services'] = $noservices;
        }
        $role = $this->_role();
        // var_dump($role);
        // die;

        $cover = $this->_coverage();
        if ($role['role_id'] != 1 && $role['coverage_operator'] == 1) {
            $data['coverage'] = $this->coverage_m->getCoverage($cover)->num_rows();
            $data['customer'] = $this->customer_m->getCustomerAll($cover)->result();
        } else {
            $data['coverage'] = $this->coverage_m->getCoverage()->num_rows();
            $data['customer'] = $this->customer_m->getCustomerAll()->result();
        }
        $this->template->load('backend', 'backend/mikrotik/customer/usage', $data);
    }
    public function getusage()
    {
        $post = $this->input->post(null, TRUE);
        $data = [
            'no_services' => $post['no_services'],
            'month' => $post['month'],
            'year' => $post['year'],
        ];
        $this->load->view('backend/mikrotik/customer/data-usage', $data);
    }

    public function editUsage()
    {
        $post = $this->input->post(null, TRUE);
        $usage = $this->db->get_where('customer_usage', ['no_services' => $post['no_services'], 'date_usage' => $post['dateUsage']])->row_array();
        if ($usage > 0) {
            $this->db->set('count_usage', $post['usage']);
            $this->db->where('no_services', $post['no_services']);
            $this->db->where('date_usage', $post['dateUsage']);
            $this->db->update('customer_usage');
        } else {
            $this->db->set('count_usage', $post['usage']);
            $this->db->set('no_services', $post['no_services']);
            $this->db->set('date_usage', $post['dateUsage']);
            $this->db->insert('customer_usage');
        }

        redirect('mikrotik/usage/' . $post['no_services']);
    }
}
