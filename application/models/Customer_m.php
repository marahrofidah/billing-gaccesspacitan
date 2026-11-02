<?php
//==============================START=========================================//
/*
     *  Base Code   : Gingin Abdul Goni | Rosita Wulandari, S.Kom,.MTA
     *  Email       : ginginabdulgoni@gmail.com
     *  Instagram   : @ginginabdulgoni
     *  Telegram    : @ginginabdulgoni
     *
     *  Name        : My-Wifi
     *  Function    : Manage Client and Billing
     *  Manufacture : June 2021 
     *
     *  Please do not change this code
     *  All damage caused by editing we will not be responsible please think carefully,
     *
     */
//==============================START CODE=========================================//
?>
<?php defined('BASEPATH') or exit('No direct script access allowed');

class Customer_m extends CI_Model
{
    var $table = 'customer';

    var $order = array(null, null, 'name', 'no_services',   'email', 'no_wa', 'due_date', 'ppn', 'cust_amount', 'address');

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
    public function getCustomer($customer_id = null, $no_services = null)
    {
        $this->db->select('*');
        $this->db->from('customer');
        if ($customer_id != null) {
            $this->db->where('customer_id', $customer_id);
        }
        if ($no_services != null) {
            $this->db->where('no_services', $no_services);
        }
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query;
    }
    public function getCustomerpppoe($router)
    {
        $this->db->select('*');
        $this->db->from('customer');
        $this->db->where('router', $router);
        $this->db->where('mode_user', 'PPPOE');
        $this->db->where('user_mikrotik !=', '');
        $this->db->where('c_status', 'Aktif');
        $this->db->order_by('name', 'random');
        $query = $this->db->get();
        return $query;
    }
    public function getfilterby($post, $row = null)
    {
          $this->db->select('*');
        $this->db->from('customer');
        if ($post['status'] != null) {
            $this->db->where('c_status', $post['status']);
        }
        if ($post['router'] != 0) {
            $this->db->where('router', $post['router']);
        }
        if ($post['coverage'] != 0) {
            $this->db->where('coverage', $post['coverage']);
        }
        if ($row != null) {
            $this->db->where_in('coverage', $row);
        }
        if ($this->session->userdata('role_id') == 4) {
            $this->db->where('mitra', $this->session->userdata('id'));
        }
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query;
    }
    public function getfilterbydraf($post)
    {
        $this->db->select('*');
        $this->db->from('customer');
        $this->db->where('c_status', 'Aktif');
        if ($post['router'] != 0) {
            $this->db->where('router', $post['router']);
        }
        if ($post['coverage'] != 0) {
            $this->db->where('coverage', $post['coverage']);
        }
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query;
    }
    public function getCustomerIsolir($customer_id = null, $no_services = null)
    {
        $this->db->select('*');
        $this->db->from('customer');
        if ($customer_id != null) {
            $this->db->where('customer_id', $customer_id);
        }
        if ($no_services != null) {
            $this->db->where('no_services', $no_services);
        }
        $this->db->where('auto_isolir', 1);
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query;
    }
    public function getCustomerAll($row = null)
    {
        $this->db->select('*');
        $this->db->from('customer');
        if ($row != null) {
            $this->db->where_in('coverage', $row);
        }
        // $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query;
    }
    public function getCustomerActive($row = null)
    {
        $this->db->select('*');
        $this->db->from('customer');
        if ($row != null) {
            $this->db->where_in('coverage', $row);
        }
        $this->db->where('c_status', 'Aktif');
        $query = $this->db->get();
        return $query;
    }
    public function getCustomertiket($row = null)
    {
        $this->db->select('*');
        $this->db->from('customer');
        if ($row != null) {
            $this->db->where_in('coverage', $row);
        }
        $this->db->where('c_status', 'Aktif');
        $this->db->or_where('c_status', 'Menunggu');
        $this->db->or_where('c_status', 'Free');
        $query = $this->db->get();
        return $query;
    }
    public function getCustomerNonactive($row = null)
    {
        $this->db->select('*');
        $this->db->from('customer');
        if ($row != null) {
            $this->db->where_in('coverage', $row);
        }
        $this->db->where('c_status', 'Non-Aktif');
        $query = $this->db->get();
        return $query;
    }
    public function getCustomerWait($row = null)
    {
        $this->db->select('*');
        $this->db->from('customer');
        if ($row != null) {
            $this->db->where_in('coverage', $row);
        }
        $this->db->where('c_status', 'Menunggu');
        $query = $this->db->get();
        return $query;
    }
    public function getCustomerFree($row = null)
    {
        $this->db->select('*');
        $this->db->from('customer');
        if ($row != null) {
            $this->db->where_in('coverage', $row);
        }
        $this->db->where('c_status', 'Free');
        $query = $this->db->get();
        return $query;
    }
    public function getCustomerIso($row = null)
    {
        $this->db->select('*');
        $this->db->from('customer');
        if ($row != null) {
            $this->db->where_in('coverage', $row);
        }
        $this->db->where('connection', 1);
        $this->db->where('user_mikrotik !=', '');
        $this->db->where('mode_user !=', '');
        $query = $this->db->get();
        return $query;
    }
    public function getCustomerSelecteddraf($noservices = null)
    {
        $this->db->select('*');
        $this->db->from('customer');
        if ($noservices != null) {
            $this->db->where_in('no_services', $noservices);
        }
        $this->db->where('c_status', 'Aktif');
        $query = $this->db->get();
        return $query;
    }
    public function getgenerateupdate($post)
    {

        $this->db->select('*');
        $this->db->from('customer');
        $this->db->join('invoice', 'customer.no_services = invoice.no_services');
        $this->db->where('c_status', 'Aktif');
        $this->db->where('month !=', $post['month']);
        $this->db->where('year !=', $post['year']);

        $query = $this->db->get();
        return $query;
    }

    public function getNSCustomer($no_services = null)
    {
        $this->db->select('*');
        $this->db->from('customer');
        if ($no_services != null) {
            $this->db->where('no_services', $no_services);
        }
        $query = $this->db->get();
        return $query;
    }
    public function getNSCustomerdraf($no_services = null)
    {
        $this->db->select('*');
        $this->db->from('customer');
        if ($no_services != null) {
            $this->db->where_in('no_services', $no_services);
        }
        $query = $this->db->get();
        return $query;
    }
    public function getInvoiceCustomer($no_services = null)
    {
        $this->db->select('*');
        $this->db->from('customer');
        if ($no_services != null) {
            $this->db->where('no_services', $no_services);
        }
        $query = $this->db->get();
        return $query;
    }
    public function add($post)
    {
        if ($post['mode_user'] == 'PPPOE') {
            $typeip = $post['type_ip'];
        } else {
            $typeip = 0;
        }
        $params = [
            'name' => htmlspecialchars($post['name']),
            'no_services' => $post['no_services'],
            'no_ktp' => $post['no_ktp'],
            'email' => $post['email'],
            'no_wa' => $post['no_wa'],
            'coverage' => $post['coverage'],
            'codeunique' => isset($post['codeunique']) ? $post['codeunique'] : 0,
            'type_id' => $post['type_id'],
            'auto_isolir' => $post['auto_isolir'],
            'due_date' => $post['due_date'],
            'cust_description' => $post['cust_description'],
            'type_payment' => $post['type_payment'],
            'month_due_date' => $post['month_due_date'],
            'max_due_isolir' => $post['max_due_isolir'],
            'register_date' => $post['register_date'],
            'ppn' => $post['ppn'],
            'id_odc' => $post['id_odc'],

            'id_odp' => $post['id_odp'],

            'no_port_odp' => $post['no_port_odp'],
            'type_ip' => $typeip,
            'action' => $post['action'],
            'c_status' => $post['status'],

            'address' => htmlspecialchars($post['address']),
            'created' => time(),
        ];
        if ($post['createnew'] == 1) {
            $params['user_mikrotik'] = $post['user_mikrotik'];
            $params['router'] = $post['router'];
            if ($post['profile'] != '') {
                $params['user_profile'] = $post['profile'];
            }
            $params['mode_user'] = $post['mode_user'];
        }
        if ($post['sinkron'] == 1) {
            $params['user_mikrotik'] = $post['user_sinkron'];
            if ($post['userprofilesinkron'] != '') {
                $params['user_profile'] = $post['userprofilesinkron'];
            }
            $params['router'] = $post['router'];
            $params['mode_user'] = $post['mode_user'];
        }
        $this->db->insert('customer', $params);
    }

    public function addregist($post)
    {

        $params = [
            'name' => htmlspecialchars($post['name']),
            'no_ktp' => $post['no_ktp'],
            'type_id' => $post['type_id'],
            'email' => $post['email'],
            'no_services' => $post['no_services'],
            'no_wa' => $post['no_wa'],
            'coverage' => $post['coverage'],

            'due_date' => $post['due_date'],
            'register_date' => date('Y-m-d'),
            'c_status' => 'Menunggu',
            'address' => htmlspecialchars($post['address']),
            'created' => time(),
        ];

        $this->db->insert('customer', $params);
    }
    // Fungsi untuk melakukan proses upload file
    public function upload_file($filename)
    {
        $this->load->library('upload'); // Load librari upload
        $config['upload_path'] = './assets/';
        $config['allowed_types'] = 'xlsx';
        $config['max_size']    = '5000';
        $config['overwrite'] = true;
        $config['file_name'] = $filename;

        $this->upload->initialize($config); // Load konfigurasi uploadnya
        if ($this->upload->do_upload('file')) { // Lakukan upload dan Cek jika proses upload berhasil
            // Jika berhasil :
            $return = array('result' => 'success', 'file' => $this->upload->data(), 'error' => '');
            return $return;
        } else {
            // Jika gagal :
            $return = array('result' => 'failed', 'file' => '', 'error' => $this->upload->display_errors());
            return $return;
        }
    }
    // Buat sebuah fungsi untuk melakukan insert lebih dari 1 data
    public function importfromexcel($data)
    {
        $this->db->insert_batch('customer', $data);
    }
    public function edit($post)
    {
        if ($post['mode_user'] == 'PPPOE') {
            $typeip = $post['type_ip'];
        } else {
            $typeip = 0;
        }
        $params = [
            'name' => htmlspecialchars($post['name']),
            'no_ktp' => $post['no_ktp'],
            'email' => $post['email'],
            'no_services' => $post['no_services'],
            'no_wa' => $post['no_wa'],
            'codeunique' => $post['codeunique'],
            'due_date' => $post['due_date'],
            'cust_description' => $post['cust_description'],
            'type_ip' => $typeip,
            'id_odc' => $post['id_odc'],

            'id_odp' => $post['id_odp'],

            'no_port_odp' => $post['no_port_odp'],
            'type_payment' => $post['type_payment'],
            'month_due_date' => $post['month_due_date'],
            'max_due_isolir' => $post['max_due_isolir'],
            'register_date' => $post['register_date'],
            'type_id' => $post['type_id'],
            'auto_isolir' => $post['auto_isolir'],
            'ppn' => $post['ppn'],
            'coverage' => $post['coverage'],


            'action' => $post['action'],

            'c_status' => $post['status'],
            'address' => htmlspecialchars($post['address']),


        ];


        if ($post['changeconn'] == 1) {
            $params['router'] = $post['router'];
            if ($post['createnew'] == 1) {
                $params['user_mikrotik'] = $post['user_mikrotik'];
                $params['mode_user'] = $post['mode_user'];
                if ($post['profile'] != '') {
                    $params['user_profile'] = $post['profile'];
                }
            }
            if ($post['sinkron'] == 1) {
                $params['user_mikrotik'] = $post['user_sinkron'];
                $params['mode_user'] = $post['mode_user'];
                if ($post['userprofilesinkron'] != '') {
                    $params['user_profile'] = $post['userprofilesinkron'];
                }
            }
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
        $this->db->where('customer_id', $post['customer_id']);
        $this->db->update('customer', $params);
    }
    public function delete($customer_id)
    {
        $this->db->where('customer_id', $customer_id);
        $this->db->delete('customer');
    }



    // CHART
    public function getCustomerchart()
    {
        $this->db->select('*');
        $this->db->from('customer_line');
        $query = $this->db->get();
        return $query;
    }
    public function getCustomerchart2()
    {
        $this->db->select('*');
        $this->db->from('customer_chart');
        $query = $this->db->get();
        return $query;
    }

    public function getlastcoverage($coverage_id)
    {
        $this->db->select('*');
        $this->db->from('customer');
        $this->db->where('coverage', $coverage_id);
        $this->db->order_by('created', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query;
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
            $this->db->from($this->table);
            $this->db->where_in('coverage', $row);
            if (isset($_POST['search']['value'])) {
                $this->db->like('name', $_POST['search']['value']);
                $this->db->or_like('no_services', $_POST['search']['value']);
                $this->db->where_in('coverage', $row);
            }
        } else {
            $this->db->from($this->table);

            if (isset($_POST['search']['value'])) {
                $this->db->like('name', $_POST['search']['value']);

                $this->db->or_like('no_services', $_POST['search']['value']);
                $this->db->or_like('no_wa', $_POST['search']['value']);
                $this->db->or_like('email', $_POST['search']['value']);
                $this->db->or_like('address', $_POST['search']['value']);
            }
        }




        if (isset($_POST['order'])) {
            $this->db->order_by($this->order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else {
            $this->db->order_by('name', 'ASC');
        }
    }
    private function _get_data_queryactive()
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
            $this->db->from($this->table);
            $this->db->where('c_status', 'Aktif');
            $this->db->where_in('coverage', $row);
            if (isset($_POST['search']['value'])) {
                $this->db->like('name', $_POST['search']['value']);
                $this->db->where('c_status', 'Aktif');
                $this->db->or_like('no_services', $_POST['search']['value']);
                $this->db->where_in('coverage', $row);
                // $this->db->or_like('no_services', $_POST['search']['value']);
            }
        } else {
            $this->db->from($this->table);
            $this->db->where('c_status', 'Aktif');
            if (isset($_POST['search']['value'])) {
                $this->db->like('name', $_POST['search']['value']);
                $this->db->or_like('no_services', $_POST['search']['value']);

                $this->db->where('c_status', 'Aktif');
            }
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($this->order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else {
            $this->db->order_by('name', 'ASC');
        }
    }
    public function getDataTable()
    {
        $this->_get_data_query();
        if ($_POST['lengt'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }
    public function getActive()
    {

        $this->_get_data_queryactive();
        if ($_POST['lengt'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }
    public function _get_data_querynonactive()
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
            $this->db->from($this->table);
            $this->db->where('c_status', 'Non-Aktif');
            $this->db->where_in('coverage', $row);
            if (isset($_POST['search']['value'])) {
                $this->db->like('name', $_POST['search']['value']);
                $this->db->where('c_status', 'Non-Aktif');
                $this->db->or_like('no_services', $_POST['search']['value']);
                $this->db->where_in('coverage', $row);
            }
        } else {
            $this->db->from($this->table);
            $this->db->where('c_status', 'Non-Aktif');
            if (isset($_POST['search']['value'])) {
                $this->db->like('name', $_POST['search']['value']);
                $this->db->or_like('no_services', $_POST['search']['value']);
                $this->db->where('c_status', 'Non-Aktif');
            }
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($this->order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else {
            $this->db->order_by('name', 'ASC');
        }
    }
    public function getNonActive()
    {

        $this->_get_data_querynonactive();
        if ($_POST['lengt'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }
    public function _get_data_querywaiting()
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
            $this->db->from($this->table);
            $this->db->where('c_status', 'Menunggu');
            $this->db->where_in('coverage', $row);
            if (isset($_POST['search']['value'])) {
                $this->db->like('name', $_POST['search']['value']);
                $this->db->or_like('no_services', $_POST['search']['value']);
                $this->db->where('c_status', 'Menunggu');
                $this->db->where_in('coverage', $row);
            }
        } else {
            $this->db->from($this->table);
            $this->db->where('c_status', 'Menunggu');
            if (isset($_POST['search']['value'])) {
                $this->db->like('name', $_POST['search']['value']);
                $this->db->or_like('no_services', $_POST['search']['value']);
                $this->db->where('c_status', 'Menunggu');
            }
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($this->order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else {
            $this->db->order_by('name', 'ASC');
        }
    }
    public function getWait()
    {
        $this->_get_data_querywaiting();

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
    public function count_filtered_data_active()
    {
        $this->_get_data_queryactive();
        $query = $this->db->get();
        return $query->num_rows();
    }
    public function count_filtered_data_nonactive()
    {
        $this->_get_data_querynonactive();
        return $this->db->count_all_results();
    }
    public function count_filtered_data_waiting()
    {
        $this->_get_data_querywaiting();
        return $this->db->count_all_results();
    }

    public function getmaps()
    {
        $this->db->select('*');
        $this->db->from('customer');
        $this->db->where('latitude !=', '');
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query;
    }
    public function unmaps()
    {
        $this->db->select('*');
        $this->db->from('customer');
        $this->db->where('latitude', '');
        $this->db->where('longitude', '');
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query;
    }

    public function getcustomerrouter()
    {
        $this->db->select('*');
        $this->db->from('customer');
        $this->db->where('router !=', 0);
        $this->db->where('c_status', 'Aktif');
        $this->db->where('user_mikrotik !=', '');
        $this->db->where('mode_user !=', 'Static');
        $this->db->where('mode_user !=', 'Standalone');
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query;
    }
    // OPERATOR
    public function getcapeloperator()
    {

        $operator = $this->db->get_where('cover_operator', ['operator' => $this->session->userdata('id')])->result();

        foreach ($operator as $roww) {
            $row[] = $roww->coverage_id;
        };
        // var_dump($row);
        // die;
        $this->db->from('customer');
        $this->db->where('c_status', 'Menunggu');
        $this->db->where_in('coverage', $row);
        $query = $this->db->get();
        return $query;
    }

    // server side customer free
    public function getfree()
    {

        $this->_get_data_queryfree();
        if ($_POST['lengt'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }
    private function _get_data_queryfree()
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
            $this->db->from($this->table);
            $this->db->where('c_status', 'Free');
            $this->db->where_in('coverage', $row);
            if (isset($_POST['search']['value'])) {
                $this->db->like('name', $_POST['search']['value']);
            }
        } else {
            $this->db->from($this->table);
            $this->db->where('c_status', 'Free');
            if (isset($_POST['search']['value'])) {
                $this->db->like('name', $_POST['search']['value']);
                $this->db->where('c_status', 'Free');
            }
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($this->orderactive[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else {
            $this->db->order_by('name', 'ASC');
        }
    }
    public function count_filtered_data_free()
    {
        $this->_get_data_queryfree();
        $query = $this->db->get();
        return $query->num_rows();
    }
    public function count_all_data_free()
    {
        $this->_get_data_queryfree();
        return $this->db->count_all_results();
    }
    // server side customer isolir
    public function getisolir()
    {

        $this->_get_data_queryisolir();
        if ($_POST['lengt'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }
    private function _get_data_queryisolir()
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
            $this->db->from($this->table);
            $this->db->where('connection', 1);
            $this->db->where('mode_user !=', '');
            $this->db->where('user_mikrotik !=', '');
            $this->db->where_in('coverage', $row);
            if (isset($_POST['search']['value'])) {
                $this->db->like('name', $_POST['search']['value']);
            }
        } else {
            $this->db->from($this->table);
            $this->db->where('connection', 1);
            $this->db->where('mode_user !=', '');
            $this->db->where('user_mikrotik !=', '');
            if (isset($_POST['search']['value'])) {
                $this->db->like('name', $_POST['search']['value']);


                $this->db->where('connection', 1);
                $this->db->where('mode_user !=', '');
                $this->db->where('user_mikrotik !=', '');
            }
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($this->orderactive[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else {
            $this->db->order_by('name', 'ASC');
        }
    }
    public function count_filtered_data_isolir()
    {
        $this->_get_data_queryisolir();
        $query = $this->db->get();
        return $query->num_rows();
    }
    public function count_all_data_isolir()
    {
        $this->_get_data_queryisolir();
        return $this->db->count_all_results();
    }
    public function getisolirpasca($router = null)
    {
        $gettime = strtotime("-1 day", strtotime(date("Y-m-d")));

        $date = date("d", $gettime);
        $month = date("m", $gettime);
        $year = date("Y", $gettime);
        $yesterday = $year . '-' . $month . '-' . $date;
        $this->db->select('*, invoice.created as created_invoice, invoice.no_services as noservices');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('status', 'BELUM BAYAR');
        $this->db->where('connection', 0);
        $this->db->where('auto_isolir', 1);
        $this->db->where("invoice.date_isolir BETWEEN '" . ('2020-01-01') . "' AND '" . ($yesterday) . "'");
        // $this->db->where('due_date', $date);
        // $this->db->where('month', $month);
        // $this->db->where('year', $year);
        $this->db->where('user_mikrotik !=', '');
        if ($router != null) {
            $this->db->where('router', $router);
        }
        $query = $this->db->get();
        return $query;
    }
    public function getisolirthisdate()
    {

        $today = date('Y-m-d');
        $this->db->select('*, invoice.created as created_invoice, invoice.no_services as noservices');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('status', 'BELUM BAYAR');
        $this->db->where('inv_due_date', $today);
        // $this->db->where('due_date', $date);
        // $this->db->where('month', $month);
        // $this->db->where('year', $year);
        // $this->db->where('user_mikrotik !=', '');

        $query = $this->db->get();
        return $query;
    }
    public function getopenisolir($router = null, $no_services = null)
    {
        $today = date('Y-m-d');
        $this->db->select('*, invoice.created as created_invoice, invoice.no_services as noservices');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('c_status', 'Aktif');
        // $this->db->or_where('c_status', 'Free');
        $this->db->where('invoice.status', 'BELUM BAYAR');
        $this->db->where("invoice.date_isolir BETWEEN '" . ('2020-01-01') . "' AND '" . ($today) . "'");


        if ($router != null) {
            $this->db->where('router', $router);
        }
        if ($no_services != null) {
            $this->db->where('invoice.no_services', $no_services);
        }
        $query = $this->db->get();
        return $query;
    }
    public function getrecheckisolir($router = null, $no_services = null)
    {
        $gettime = strtotime("-1 day", strtotime(date("Y-m-d")));

        $date = date("d", $gettime);
        $month = date("m", $gettime);
        $year = date("Y", $gettime);
        $yesterday = $year . '-' . $month . '-' . $date;
        $this->db->select('*, invoice.created as created_invoice, invoice.no_services as noservices');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('status', 'BELUM BAYAR');
        $this->db->where('connection', 1);
        $this->db->where('auto_isolir', 1);
        $this->db->where("invoice.date_isolir BETWEEN '" . ('2020-01-01') . "' AND '" . ($yesterday) . "'");
        $this->db->where('user_mikrotik !=', '');
        if ($router != null) {
            $this->db->where('router', $router);
        }
        if ($no_services != null) {
            $this->db->where('invoice.no_services', $no_services);
        }
        $this->db->order_by('name', 'random');
        $query = $this->db->get();
        return $query;
    }
    public function getrecheckagainisolir($router = null, $no_services = null)
    {

        $this->db->select('*, invoice.created as created_invoice, invoice.no_services as noservices');
        $this->db->from('invoice');
        $this->db->join('customer', 'customer.no_services = invoice.no_services');
        $this->db->where('status', 'BELUM BAYAR');
        $this->db->where('c_status', 'Aktif');
        $this->db->where('connection', 1);
        // $this->db->where('auto_isolir', 1);

        $this->db->where('user_mikrotik !=', '');
        if ($router != null) {
            $this->db->where('router', $router);
        }
        if ($no_services != null) {
            $this->db->where('invoice.no_services', $no_services);
        }
        $query = $this->db->get();
        return $query;
    }
    public function getusersinkron($router = null)
    {

        $this->db->select('*');
        $this->db->from('customer');
        $this->db->where('mode_user !=', '');
        $this->db->where('user_mikrotik !=', '');
        if ($router != null) {
            $this->db->where('router', $router);
        }
        $this->db->order_by('name', 'random');
        $query = $this->db->get();
        return $query;
    }

    public function getcustomeronly()
    {
        $this->db->select('*');
        $this->db->from('customer');
        $query = $this->db->get();
        return $query;
    }


    public function getfilterbycoverage($post)
    {
        $this->_get_data_queryfiltercoverage($post);
        if ($_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }
    private function _get_data_queryfiltercoverage($post)
    {
        $role = $this->_role();
        $cover = $this->_coverage();
        $this->db->from($this->table);

        if ($post['status'] == '') {
        } elseif ($post['status'] == 'Isolir') {
            $this->db->where('connection', 1);
        } else {
            $this->db->where('c_status', $post['status']);
        }
        if ($post['coverage'] == '') {
            if ($role['coverage_operator'] == 1) {
                $this->db->where_in('coverage', $cover);
            } else {
            }
        } else {
            $this->db->where_in('coverage', $post['coverage']);
        }
        if ($this->session->userdata('role_id') == 4) {
            $this->db->where('mitra', $this->session->userdata('id'));
        }
        if (isset($_POST['search']['value'])) {
            $this->db->like('name', $_POST['search']['value']);
            //   $this->db->or_like('address', $_POST['search']['value']);
            if ($this->session->userdata('role_id') == 4) {
                $this->db->where('mitra', $this->session->userdata('id'));
            }
            $this->db->or_like('no_services', $_POST['search']['value']);

            if ($post['coverage'] == '') {
                if ($role['coverage_operator'] == 1) {
                    $this->db->where_in('coverage', $cover);
                } else {
                }
            } else {
                $this->db->where_in('coverage', $post['coverage']);
            }

            if ($post['status'] == '') {
            } elseif ($post['status'] == 'Isolir') {
                $this->db->where('connection', 1);
            } else {
                $this->db->where('c_status', $post['status']);
            }
            // $this->db->or_like('address', $_POST['search']['value']);
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($this->order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else {
            $this->db->order_by('created', 'Desc');
        }
    }
    public function count_filtered_data_coverage($post)
    {
        $this->_get_data_queryfiltercoverage($post);
        $query = $this->db->get();
        return $query->num_rows();
    }
    public function getCustomerFilter($post)
    {
        $role = $this->_role();
        $cover = $this->_coverage();
        $this->db->select('*');
        $this->db->from('customer');
        if ($this->session->userdata('role_id') == 4) {
            $this->db->where('mitra', $this->session->userdata('id'));
        }
        if ($post['coverage'] == '') {
            if ($role['coverage_operator'] == 1) {
                $this->db->where_in('coverage', $cover);
            } else {
            }
        } else {
            $this->db->where_in('coverage', $post['coverage']);
        }
        if ($post['status'] == '') {
        } elseif ($post['status'] == 'Isolir') {
            $this->db->where('connection', 1);
        } else {
            $this->db->where('c_status', $post['status']);
        }
        $query = $this->db->get();
        return $query;
    }
    public function cekuserchangeprofile()
    {
        $this->db->select('*');
        $this->db->from('customer');
        $this->db->where('action', 1);
        $this->db->where('user_profile', '');

        // $this->db->or_where('mode_user', 'PPPOE');
        // $this->db->or_where('mode_user', 'Hotspot');
        // $this->db->or_where('mode_user', '');
        $this->db->where('user_profile', 'EXPIRED');
        $query = $this->db->get();
        return $query;
    }
    // SELECTED
    public function getCustomerSelected($customer_id = null)
    {
        $this->db->select('*');

        $this->db->from('customer');
        if ($customer_id != null) {
            $this->db->where_in('customer_id', $customer_id);
        }

        $query = $this->db->get();
        return $query;
    }
}
