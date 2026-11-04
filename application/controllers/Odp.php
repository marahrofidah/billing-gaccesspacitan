<?php

ob_start();

defined('BASEPATH') or exit('No direct script access allowed');



class Odp extends CI_Controller

{

    function __construct()

    {

        parent::__construct();

        is_logged_in();

        $this->load->model(['odp_m', 'coverage_m', 'odc_m', 'logs_m', 'customer_m']);
    }

    public function index()

    {
        $target_dir = "./assets/images/document";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777);
        }
        $table = 'm_odp';

        if ($this->db->table_exists($table)) {

            $data['title'] = 'Odp';

            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

            $data['company'] = $this->db->get('company')->row_array();

            $data['odp'] = $this->odp_m->get()->result();

            $data['coverage'] = $this->coverage_m->getCoverage()->result();

            $this->template->load('backend', 'backend/odp/odp', $data);
        } else {
            echo "<script>window.location='" . base_url('migration/createtable/' . $table) . "'; </script>";
        }
    }

    public function add()

    {

        is_logged_in();

        $role_id = $this->session->userdata('role_id');

        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();

        if ($role_id != 1 && $role['add_coverage'] == 0) {

            $this->session->set_flashdata('error', 'Akses dilarang');

            redirect('dashboard');
        }

        if ($this->session->userdata('role_id') == 2) {

            $this->session->set_flashdata('error', 'Akses dilarang');

            redirect('member/dashboard');
        }

        $this->form_validation->set_rules('code_odp', 'Kode odp', 'required|trim|is_unique[m_odp.code_odp]');

        $this->form_validation->set_message('is_unique', '%s Sudah dipakai, Silahkan ganti');

        if ($this->form_validation->run() == false) {

            $data['title'] = 'Add odp';

            $data['company'] = $this->db->get('company')->row_array();

            $data['coverage'] = $this->coverage_m->getCoverage()->result();

            $data['odc'] = $this->odc_m->get()->result();

            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

            $this->template->load('backend', 'backend/odp/add-odp', $data);
        } else {

            $post = $this->input->post(null, TRUE);
            $post['picture'] = null;

            if (isset($_FILES['picture']) && $_FILES['picture']['error'] !== UPLOAD_ERR_NO_FILE) {
                $config['upload_path'] = './assets/images/document';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size'] = 20480;
                $config['file_name'] = 'odp-' . date('ymd') . '-' . substr(md5(rand()), 0, 10);
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload('picture')) {
                    $this->session->set_flashdata('error', $this->upload->display_errors());
                    redirect('odp/add', 'location', 303);
                    return;
                }
                $post['picture'] = $this->upload->data('file_name');
            }

            $this->odp_m->add($post);
            if ($this->db->affected_rows() > 0) {
                $this->session->set_flashdata('success', 'Data ODP berhasil disimpan');
            } else {
                $this->session->set_flashdata('error', 'Data ODP gagal disimpan');
            }
            redirect('odp', 'location', 303);
        }
    }

    public function edit($id)

    {

        is_logged_in();

        $role_id = $this->session->userdata('role_id');

        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();

        if ($role_id != 1 && $role['edit_coverage'] == 0) {

            $this->session->set_flashdata('error', 'Akses dilarang');

            redirect('dashboard');
        }

        if ($this->session->userdata('role_id') == 2) {

            $this->session->set_flashdata('error', 'Akses dilarang');

            redirect('member/dashboard');
        }



        $this->form_validation->set_rules('code_odp', 'Kode odp', 'required|trim|callback_codeodp_check');

        if ($this->form_validation->run() == false) {

            $query  = $this->db->get_where('m_odp', ['id_odp' => $id]);

            if ($query->num_rows() > 0) {

                $data['title'] = 'Edit odp';

                $data['company'] = $this->db->get('company')->row_array();

                $data['coverage'] = $this->coverage_m->getCoverage()->result();

                $data['odp'] = $this->db->get_where('m_odp', ['id_odp' => $id])->row_array();

                $data['odc'] = $this->odc_m->get()->result();

                $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

                $this->template->load('backend', 'backend/odp/edit-odp', $data);
            } else {

                echo "<script> alert ('Data tidak ditemukan');";

                echo "window.location='" . site_url('odp') . "'; </script>";
            }
        } else {

            $post = $this->input->post(null, TRUE);

            if (isset($_FILES['picture']) && $_FILES['picture']['error'] !== UPLOAD_ERR_NO_FILE) {
                $config['upload_path'] = './assets/images/document';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size'] = 20480;
                $config['file_name'] = 'odp-' . date('ymd') . '-' . substr(md5(rand()), 0, 10);
                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('picture')) {
                    $this->session->set_flashdata('error', $this->upload->display_errors());
                    redirect('odp/edit/' . $id, 'location', 303);
                    return;
                }

                $post['picture'] = $this->upload->data('file_name');
                $odp = $this->db->get_where('m_odp', ['id_odp' => $id])->row();
                if ($odp && $odp->document) {
                    $target_file = './assets/images/document/' . $odp->document;
                    if (is_file($target_file)) {
                        unlink($target_file);
                    }
                }
            }

            $this->odp_m->edit($post);
            if ($this->db->affected_rows() > 0) {
                $this->session->set_flashdata('success', 'Data ODP berhasil diperbaharui');
            }
            redirect('odp', 'location', 303);
        }
    }

    function codeodp_check()

    {

        $post = $this->input->post(null, TRUE);

        $query = $this->db->query("SELECT * FROM m_odp WHERE code_odp = '$post[code_odp]' AND id_odp != '$post[id_odp]'");

        if ($query->num_rows() > 0) {

            $this->form_validation->set_message('codeodp_check', '%s Ini sudah dipakai, Silahkan ganti !');

            return FALSE;
        } else {

            return TRUE;
        }
    }

    public function getodp()

    {

        is_logged_in();

        $post = $this->input->post(null, TRUE);

        $odp = $this->db->get_where('m_odp', ['code_odc' => $post['id_odc']])->result();

        echo "<option value=''>-Pilih-</option>";

        foreach ($odp as $k) {

            echo "<option value='{$k->id_odp}'>{$k->code_odp}</option>";
        }
    }

    public function getportodp()

    {

        is_logged_in();

        $post = $this->input->post(null, TRUE);

        $odp = $this->db->get_where('m_odp', ['id_odp' => $post['id_odp']])->row_array();

        echo "<option value=''>-Pilih-</option>";

        $customerId = (int) $this->input->post('customer_id');
        $customersByPort = [];
        $customers = $this->db->get_where('customer', ['id_odp' => $post['id_odp']])->result_array();
        foreach ($customers as $customer) {
            $port = (int) $customer['no_port_odp'];
            if ($port > 0) {
                $customersByPort[$port][] = $customer;
            }
        }

        for ($x = 1; $x <= $odp['total_port']; $x += 1) {
            $portCustomers = isset($customersByPort[$x]) ? $customersByPort[$x] : [];
            $occupants = array_filter($portCustomers, function ($customer) use ($customerId) {
                return $customerId === 0 || (int) $customer['customer_id'] !== $customerId;
            });

            if (!empty($occupants)) {
                $labels = [];
                foreach ($occupants as $customer) {
                    $status = $customer['c_status'] !== '' ? $customer['c_status'] : 'Tidak diketahui';
                    $labels[] = html_escape($customer['no_services'] . ' A/N ' . $customer['name'] . ' - ' . $status);
                }
                $description = implode('; ', $labels);
                echo "<option value='$x' disabled>" . html_escape((string) $x) . " - Sudah digunakan - {$description}</option>";
            } else {

                echo "<option value='" . html_escape((string) $x) . "'>" . html_escape((string) $x) . "</option>";
            }
        }
    }



    public function delete()

    {

        $id_odp = $this->input->post('id_odp');

        $odp = $this->db->get_where('m_odp', ['id_odp' => $id_odp])->row_array();

        $customer = $this->db->get_where('customer', ['id_odp' => $id_odp])->row_array();

        if ($customer > 0) {

            $this->session->set_flashdata('error-sweet', 'Tidak bisa dihapus karena masih terdaftar di data pelanggan');
        } else {

            if ($odp->document != null) {

                $target_file = './assets/images/document/' . $odp->document;

                unlink($target_file);
            }

            $this->db->where('id_odp', $id_odp);

            $this->db->delete('m_odp');

            if ($this->db->affected_rows() > 0) {

                $this->session->set_flashdata('success', 'Data ODP berhasil dihapus');
            }
        }

        echo "<script>window.location='" . base_url('odp') . "'; </script>";
    }



    // DOKUMEN

    public function doc($id_odp)

    {




        $query  = $this->db->get_where('m_odp', ['id_odp' => $id_odp]);

        if ($query->num_rows() > 0) {

            $data['odp'] = $query->row();

            $data['title'] = 'Document ODP';

            $data['company'] = $this->db->get('company')->row_array();

            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

            $this->template->load('backend', 'backend/odp/doc_odp', $data);
        } else {

            echo "<script> alert ('Data tidak ditemukan');";

            echo "window.location='" . site_url('odp') . "'; </script>";
        }
    }

    public function adddoc()

    {

        is_logged_in();

        $role_id = $this->session->userdata('role_id');



        if ($this->session->userdata('role_id') == 2) {

            redirect('member/dashboard');
        }

        $post = $this->input->post(null, TRUE);

        $config['upload_path']          = './assets/images/document';

        if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0777, TRUE);
        $config['allowed_types']        = 'gif|jpg|png|jpeg';

        $config['max_size']             = 20480; // 2 Mb

        $config['file_name']             = 'odp-doc-' . date('ymd') . '-' . substr(md5(rand()), 0, 10);

        $this->load->library('upload', $config);

        if (@FILES['picture']['name'] != null) {

            if ($this->upload->do_upload('picture')) {

                $post['picture'] =  $this->upload->data('file_name');

                $this->odp_m->adddoc($post);

                if ($this->db->affected_rows() > 0) {

                    $message = 'Tambah data document ODP ' . $post['name'];

                    $this->logs_m->activitylogs('Activity', $message);

                    $this->session->set_flashdata('success-sweet', 'Data berhasil disimpan');
                }

                echo "<script>window.location='" . site_url('odp/doc/' . $post['odp_id']) . "'; </script>";
            } else {

                $post['picture'] =  null;

                $this->odp_m->adddoc($post);

                if ($this->db->affected_rows() > 0) {

                    $message = 'Tambah data document odp ' . $post['name'];

                    $this->logs_m->activitylogs('Activity', $message);

                    $this->session->set_flashdata('success-sweet', ' Data berhasil disimpan');
                }

                echo "<script>window.location='" . base_url('odp/doc/' . $post['odp_id']) . "'; </script>";
            }
        } else {

            $error = $this->upload->display_errors();

            $this->session->set_flashdata('error-sweet', $error);

            echo "<script>window.location='" . base_url('odp/doc/' . $post['odp_id']) . "'; </script>";
        }
    }

    public function editdoc()

    {

        $config['upload_path']          = './assets/images/document';

        $config['allowed_types']        = 'gif|jpg|png|jpeg';

        $config['max_size']             = 20048; // 10 Mb

        $config['file_name']             = 'odp-doc-' . date('ymd') . '-' . substr(md5(rand()), 0, 10);

        $this->load->library('upload', $config);

        $post = $this->input->post(null, TRUE);

        if (@FILES['picture']['name'] != null) {

            if ($this->upload->do_upload('picture')) {

                $document = $this->db->get_where('odp_doc', ['id' => $post['id']])->row();

                if ($document->document != null) {

                    $target_file = './assets/images/document/' . $document->document;

                    unlink($target_file);
                }

                $post['picture'] =  $this->upload->data('file_name');

                $this->odp_m->editdoc($post);

                if ($this->db->affected_rows() > 0) {

                    $message = 'Edit data document ODP ' . $post['name'];

                    $this->logs_m->activitylogs('Activity', $message);

                    $this->session->set_flashdata('success-sweet', 'Data document berhasil diperbaharui');
                }

                echo "<script>window.location='" . site_url('odp/doc/' . $post['odp_id']) . "'; </script>";
            } else {

                $post['picture'] =  null;

                $this->odp_m->editdoc($post);

                if ($this->db->affected_rows() > 0) {

                    $message = 'Edit data document ODP ' . $post['name'];

                    $this->logs_m->activitylogs('Activity', $message);

                    $this->session->set_flashdata('success-sweet', 'Data document berhasil diperbaharui');
                }

                echo "<script>window.location='" . base_url('odp/doc/' . $post['odp_id']) . "'; </script>";
            }
        } else {

            $error = $this->upload->display_errors();

            $this->session->set_flashdata('error-sweet', $error);

            echo "<script>window.location='" . base_url('odp/doc/' . $post['odp_id']) . "'; </script>";
        }
    }

    public function deletedoc()

    {

        $post = $this->input->post(null, TRUE);

        $document = $this->db->get_where('odp_doc', ['id' => $post['id']])->row();

        $odp = $this->db->get_where('m_odp', ['id_odp' =>  $post['odp_id']])->row();

        $customername = $odp->code_odp;

        if ($document->document != null) {

            $target_file = './assets/images/document/' . $document->document;

            unlink($target_file);
        }

        $this->odp_m->deletedoc($post);

        if ($this->db->affected_rows() > 0) {

            $message = 'Hapus data document ODP ' . $customername;

            $this->logs_m->activitylogs('Activity', $message);

            $this->session->set_flashdata('success-sweet', 'Data document berhasil dihapus');



            redirect('odp/doc/' . $post['odp_id']);
        }
    }



    public function updateportcustomer()

    {

        $post = $this->input->post(null, TRUE);

        $customer = $this->db->get_where('customer', ['customer_id' => $post['id']])->row_array();

        $customername = $customer['name'] . ' - ' . $customer['no_services'];

        $this->db->set('no_port_odp', $post['port_odp']);

        $this->db->where('customer_id', $post['id']);

        $this->db->update('customer');

        if ($this->db->affected_rows() > 0) {

            $message = 'Edit Port ODP Pelanggan ' . $customername;

            $this->logs_m->activitylogs('Activity', $message);

            $this->session->set_flashdata('success-sweet', 'Data Port ODP berhasil diperbaharui');
        }

        redirect($_SERVER['HTTP_REFERER']);
    }
}
