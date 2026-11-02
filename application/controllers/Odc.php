<?php

ob_start();

defined('BASEPATH') or exit('No direct script access allowed');



class Odc extends CI_Controller

{

    function __construct()

    {

        parent::__construct();

        is_logged_in();
        $this->load->model(['odc_m', 'coverage_m', 'logs_m', 'customer_m']);
        $this->load->dbforge();
    }

    public function index()

    {
        $target_dir = "./assets/images/document";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777);
        }
        $table = 'm_odc';

        if ($this->db->table_exists($table)) {
            $data['title'] = 'Odc';

            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

            $data['company'] = $this->db->get('company')->row_array();

            $data['odc'] = $this->odc_m->get()->result();

            $data['coverage'] = $this->coverage_m->getCoverage()->result();

            $this->template->load('backend', 'backend/odc/odc', $data);
        } else {

            echo "<script>window.location='" . base_url('migration/createtable/' . $table) . "'; </script>";
        }
    }

    public function add()

    {

        is_logged_in();

        $role_id = $this->session->userdata('role_id');

        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();

        if ($role_id == 3 && $role['add_coverage'] == 0) {

            $this->session->set_flashdata('error', 'Akses dilarang');

            redirect('dashboard');
        }

        if ($this->session->userdata('role_id') == 2) {

            $this->session->set_flashdata('error', 'Akses dilarang');

            redirect('member/dashboard');
        }

        $this->form_validation->set_rules('code_odc', 'Kode ODC', 'required|trim|is_unique[m_odc.code_odc]');

        $this->form_validation->set_message('is_unique', '%s Sudah dipakai, Silahkan ganti');

        if ($this->form_validation->run() == false) {

            $data['title'] = 'Add ODC';

            $data['company'] = $this->db->get('company')->row_array();

            $data['coverage'] = $this->coverage_m->getCoverage()->result();

            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

            $this->template->load('backend', 'backend/odc/add-odc', $data);
        } else {

            $post = $this->input->post(null, TRUE);
            $post['picture'] = null;

            if (isset($_FILES['picture']) && $_FILES['picture']['error'] !== UPLOAD_ERR_NO_FILE) {
                $config['upload_path'] = './assets/images/document';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size'] = 20480;
                $config['file_name'] = 'odc-' . date('ymd') . '-' . substr(md5(rand()), 0, 10);
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload('picture')) {
                    $this->session->set_flashdata('error', $this->upload->display_errors());
                    redirect('odc/add', 'location', 303);
                    return;
                }
                $post['picture'] = $this->upload->data('file_name');
            }

            $this->odc_m->add($post);
            if ($this->db->affected_rows() > 0) {
                $this->session->set_flashdata('success', 'Data ODC berhasil disimpan');
            } else {
                $this->session->set_flashdata('error', 'Data ODC gagal disimpan');
            }
            redirect('odc', 'location', 303);
        }
    }

    public function edit($id)

    {

        is_logged_in();

        $role_id = $this->session->userdata('role_id');

        $role = $this->db->get_where('role_management', ['role_id' => $role_id])->row_array();

        if ($role_id == 3 && $role['edit_coverage'] == 0) {

            $this->session->set_flashdata('error', 'Akses dilarang');

            redirect('dashboard');
        }

        if ($this->session->userdata('role_id') == 2) {

            $this->session->set_flashdata('error', 'Akses dilarang');

            redirect('member/dashboard');
        }



        $this->form_validation->set_rules('code_odc', 'Kode ODC', 'required|trim|callback_codeodc_check');

        if ($this->form_validation->run() == false) {

            $query  = $this->db->get_where('m_odc', ['id_odc' => $id]);

            if ($query->num_rows() > 0) {

                $data['title'] = 'Edit ODC';

                $data['company'] = $this->db->get('company')->row_array();

                $data['coverage'] = $this->coverage_m->getCoverage()->result();

                $data['odc'] = $this->db->get_where('m_odc', ['id_odc' => $id])->row_array();

                $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

                $this->template->load('backend', 'backend/odc/edit-odc', $data);
            } else {

                echo "<script> alert ('Data tidak ditemukan');";

                echo "window.location='" . site_url('odc') . "'; </script>";
            }
        } else {

            $post = $this->input->post(null, TRUE);

            if (isset($_FILES['picture']) && $_FILES['picture']['error'] !== UPLOAD_ERR_NO_FILE) {
                $config['upload_path'] = './assets/images/document';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size'] = 20480;
                $config['file_name'] = 'odc-' . date('ymd') . '-' . substr(md5(rand()), 0, 10);
                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('picture')) {
                    $this->session->set_flashdata('error', $this->upload->display_errors());
                    redirect('odc/edit/' . $id, 'location', 303);
                    return;
                }

                $post['picture'] = $this->upload->data('file_name');
                $odc = $this->db->get_where('m_odc', ['id_odc' => $id])->row();
                if ($odc && $odc->document) {
                    $target_file = './assets/images/document/' . $odc->document;
                    if (is_file($target_file)) {
                        unlink($target_file);
                    }
                }
            }

            $this->odc_m->edit($post);
            if ($this->db->affected_rows() > 0) {
                $this->session->set_flashdata('success', 'Data ODC berhasil diperbaharui');
            }
            redirect('odc', 'location', 303);
        }
    }

    function codeodc_check()

    {

        $post = $this->input->post(null, TRUE);

        $query = $this->db->query("SELECT * FROM m_odc WHERE code_odc = '$post[code_odc]' AND id_odc != '$post[id_odc]'");

        if ($query->num_rows() > 0) {

            $this->form_validation->set_message('codeodc_check', '%s Ini sudah dipakai, Silahkan ganti !');

            return FALSE;
        } else {

            return TRUE;
        }
    }



    public function getodc()

    {

        is_logged_in();

        $post = $this->input->post(null, TRUE);

        $odc = $this->db->get_where('m_odc', ['coverage_odc' => $post['coverage_id']])->result();

        echo "<option value=''>-Pilih-</option>";

        foreach ($odc as $k) {

            echo "<option value='{$k->id_odc}'>{$k->code_odc}</option>";
        }
    }

    public function delete()

    {

        $id_odc = $this->input->post('id_odc');

        $odc = $this->db->get_where('m_odc', ['id_odc' => $id_odc])->row_array();

        $customer = $this->db->get_where('customer', ['id_odc' => $id_odc])->row_array();

        $odp = $this->db->get_where('m_odp', ['code_odc' => $id_odc])->row_array();

        if ($customer > 0) {

            $this->session->set_flashdata('error-sweet', 'Tidak bisa dihapus karena masih terdaftar di data pelanggan');
        } elseif ($odp > 0) {

            $this->session->set_flashdata('error-sweet', 'Tidak bisa dihapus karena masih terdaftar di data ODP');
        } else {

            if ($odc->document != null) {

                $target_file = './assets/images/document/' . $odc->document;

                unlink($target_file);
            }

            $this->db->where('id_odc', $id_odc);

            $this->db->delete('m_odc');

            if ($this->db->affected_rows() > 0) {

                $this->session->set_flashdata('success', 'Data ODC berhasil dihapus');
            }
        }

        echo "<script>window.location='" . base_url('odc') . "'; </script>";
    }



    // DOKUMEN

    public function doc($id_odc)

    {



        $query  = $this->db->get_where('m_odc', ['id_odc' => $id_odc]);

        if ($query->num_rows() > 0) {

            $data['odc'] = $query->row();

            $data['title'] = 'Document ODC';

            $data['company'] = $this->db->get('company')->row_array();

            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

            $this->template->load('backend', 'backend/odc/doc_odc', $data);
        } else {

            echo "<script> alert ('Data tidak ditemukan');";

            echo "window.location='" . site_url('odc') . "'; </script>";
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

        $config['allowed_types']        = 'gif|jpg|png|jpeg';

        $config['max_size']             = 20480; // 2 Mb

        $config['file_name']             = 'odc-doc-' . date('ymd') . '-' . substr(md5(rand()), 0, 10);

        $this->load->library('upload', $config);

        if (@FILES['picture']['name'] != null) {

            if ($this->upload->do_upload('picture')) {

                $post['picture'] =  $this->upload->data('file_name');

                $this->odc_m->adddoc($post);

                if ($this->db->affected_rows() > 0) {

                    $message = 'Tambah data document ODC ' . $post['name'];

                    $this->logs_m->activitylogs('Activity', $message);

                    $this->session->set_flashdata('success-sweet', 'Data berhasil disimpan');
                }

                echo "<script>window.location='" . site_url('odc/doc/' . $post['odc_id']) . "'; </script>";
            } else {

                $post['picture'] =  null;

                $this->odc_m->adddoc($post);

                if ($this->db->affected_rows() > 0) {

                    $message = 'Tambah data document ODC ' . $post['name'];

                    $this->logs_m->activitylogs('Activity', $message);

                    $this->session->set_flashdata('success-sweet', ' Data berhasil disimpan');
                }

                echo "<script>window.location='" . base_url('odc/doc/' . $post['odc_id']) . "'; </script>";
            }
        } else {

            $error = $this->upload->display_errors();

            $this->session->set_flashdata('error-sweet', $error);

            echo "<script>window.location='" . base_url('odc/doc/' . $post['odc_id']) . "'; </script>";
        }
    }

    public function editdoc()

    {

        $config['upload_path']          = './assets/images/document';

        $config['allowed_types']        = 'gif|jpg|png|jpeg';

        $config['max_size']             = 20048; // 10 Mb

        $config['file_name']             = 'odc-doc-' . date('ymd') . '-' . substr(md5(rand()), 0, 10);

        $this->load->library('upload', $config);

        $post = $this->input->post(null, TRUE);

        if (@FILES['picture']['name'] != null) {

            if ($this->upload->do_upload('picture')) {

                $document = $this->db->get_where('odc_doc', ['id' => $post['id']])->row();

                if ($document->document != null) {

                    $target_file = './assets/images/document/' . $document->document;

                    unlink($target_file);
                }

                $post['picture'] =  $this->upload->data('file_name');

                $this->odc_m->editdoc($post);

                if ($this->db->affected_rows() > 0) {

                    $message = 'Edit data document ODC ' . $post['name'];

                    $this->logs_m->activitylogs('Activity', $message);

                    $this->session->set_flashdata('success-sweet', 'Data document berhasil diperbaharui');
                }

                echo "<script>window.location='" . site_url('odc/doc/' . $post['odc_id']) . "'; </script>";
            } else {

                $post['picture'] =  null;

                $this->odc_m->editdoc($post);

                if ($this->db->affected_rows() > 0) {

                    $message = 'Edit data document ODC ' . $post['name'];

                    $this->logs_m->activitylogs('Activity', $message);

                    $this->session->set_flashdata('success-sweet', 'Data document berhasil diperbaharui');
                }

                echo "<script>window.location='" . base_url('odc/doc/' . $post['odc_id']) . "'; </script>";
            }
        } else {

            $error = $this->upload->display_errors();

            $this->session->set_flashdata('error-sweet', $error);

            echo "<script>window.location='" . base_url('odc/doc/' . $post['odc_id']) . "'; </script>";
        }
    }

    public function deletedoc()

    {

        $post = $this->input->post(null, TRUE);

        $document = $this->db->get_where('odc_doc', ['id' => $post['id']])->row();

        $odc = $this->db->get_where('m_odc', ['id_odc' =>  $post['odc_id']])->row();

        $customername = $odc->code_odc;

        if ($document->document != null) {

            $target_file = './assets/images/document/' . $document->document;

            unlink($target_file);
        }

        $this->odc_m->deletedoc($post);

        if ($this->db->affected_rows() > 0) {

            $message = 'Hapus data document ODC ' . $customername;

            $this->logs_m->activitylogs('Activity', $message);

            $this->session->set_flashdata('success-sweet', 'Data document berhasil dihapus');



            redirect('odc/doc/' . $post['odc_id']);
        }
    }
}
