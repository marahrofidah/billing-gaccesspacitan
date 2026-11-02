<?php
ob_start();
defined('BASEPATH') or exit('No direct script access allowed');

class Package extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        is_logged_in();
        $this->load->model(['package_m', 'services_m', 'bill_m', 'logs_m']);
    }
    public function index()
    {
        $data['title'] = 'Paket';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/package', $data);
    }
    public function Item()
    {
        $data['title'] = 'Item Package';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['p_item'] = $this->package_m->getPItem()->result();
        $data['p_category'] = $this->package_m->getPCategory()->result();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/package/item', $data);
    }
    public function getpackage()
    {
        $package = $this->db->get_where('package_item', ['p_item_id' => $this->input->post('id')])->row_array();
        $category = $this->db->get_where('package_category', ['p_category_id' => $package['category_id']])->row_array();
        $data = [
            'price' => $package['price'],
            'category_id' => $package['category_id'],
            'category_name' => $category['name'],
        ];
        echo json_encode($data);
    }
    public function Itemnonactive()
    {
        $data['title'] = 'Item Package';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['p_item'] = $this->package_m->getPItemnonactive()->result();
        $data['p_category'] = $this->package_m->getPCategory()->result();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/package/item', $data);
    }
    public function Itemactive()
    {
        $this->db->set('is_active', 1);
        $this->db->update('package_item');
        redirect('package/item');
    }
    public function Category()
    {
        $data['title'] = 'Category Package';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['p_category'] = $this->package_m->getPCategory()->result();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/package/category', $data);
    }
    public function addPcategory()
    {
        $post = $this->input->post(null, TRUE);
        $this->package_m->addPCategory($post);
        if ($this->db->affected_rows() > 0) {

            $this->session->set_flashdata('success-sweet', 'Data berhasil disimpan');
        }
        redirect('package/category');
    }
    public function editPcategory()
    {
        $post = $this->input->post(null, TRUE);
        $this->package_m->editPCategory($post);
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success-sweet', 'Data berhasil diperbaharui');
        }
        redirect('package/category');
    }

    public function deletePCategory()
    {
        $id = $this->input->post('p_category_id');
        $query = $this->package_m->cekCategory($id);
        if ($query->num_rows() > 0) {
            $this->session->set_flashdata('error-sweet', 'Data Kategori tidak dapat dihapus dikarenakan masih digunakan di Item Layanan atau Detail Tagihan');
            redirect('package/category');
        } else {
            $this->package_m->deletePCategory($id);
            if ($this->db->affected_rows() > 0) {
                $this->session->set_flashdata('success-sweet', 'Data Kategori berhasil dihapus');
            }
        }
        redirect('package/category');
    }

    public function addPItem()
    {
        $post = $this->input->post(null, TRUE);
        $post['picture'] = null;
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] !== UPLOAD_ERR_NO_FILE) {
            $config['upload_path'] = './assets/images/package';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size'] = 10048;
            $config['file_name'] = 'package-' . date('ymd') . '-' . substr(md5(rand()), 0, 10);
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload('picture')) {
                $this->session->set_flashdata('error', $this->upload->display_errors());
                redirect('package/item', 'location', 303);
                return;
            }
            $post['picture'] = $this->upload->data('file_name');
        }
        $this->package_m->addPItem($post);
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success-sweet', 'Data Item Layanan berhasil disimpan');
        } else {
            $this->session->set_flashdata('error-sweet', 'Data Item Layanan gagal disimpan');
        }
        redirect('package/item', 'location', 303);
    }
    public function editPItem()
    {
        $post = $this->input->post(null, TRUE);
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] !== UPLOAD_ERR_NO_FILE) {
            $config['upload_path'] = './assets/images/package';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size'] = 10048;
            $config['file_name'] = 'package-' . date('ymd') . '-' . substr(md5(rand()), 0, 10);
            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('picture')) {
                $this->session->set_flashdata('error', $this->upload->display_errors());
                redirect('package/item', 'location', 303);
                return;
            }

            $package = $this->package_m->getPItem($post['p_item_id'])->row();
            if ($package && $package->picture) {
                $target_file = './assets/images/package/' . $package->picture;
                if (is_file($target_file)) {
                    unlink($target_file);
                }
            }
            $post['picture'] = $this->upload->data('file_name');
        }

        $this->package_m->editPItem($post);
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success-sweet', 'Data Item layanan berhasil diperbaharui');
        }
        redirect('package/item', 'location', 303);
    }
    public function deletePItem()
    {
        $p_item_id = $this->input->post('p_item_id');
        $query = $this->services_m->cekItem($p_item_id);
        if ($query->num_rows() > 0) {
            $this->session->set_flashdata('error-sweet', 'Data Layanan tidak dapat dihapus dikarenakan masih digunakan di Layanan Pelanggan atau Detail Tagihan');
            redirect('package/item');
        }
        $query = $this->bill_m->cekItem($p_item_id);
        if ($query->num_rows() > 0) {
            $this->session->set_flashdata('error-sweet', 'Data Item tidak dapat dihapus dikarenakan masih digunakan Detail Tagihan');
            redirect('package/Item');
        } else {
            $package = $this->package_m->getPItem($p_item_id)->row();
            if ($package->picture != null) {
                $target_file = './assets/images/package/' . $package->picture;
                unlink($target_file);
            }
        }

        $this->package_m->deletePitem($p_item_id);
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success-sweet', 'Data berhasil dihapus');
        }
        redirect('package/item');
    }
    public function updatepackagecustomer()
    {
        $post = $this->input->post(null, TRUE);
        if ($post['item'] == '') {
            $this->session->set_flashdata('error-sweet', 'Paket baru belum dipolih');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $item = $this->db->get_where('package_item', ['p_item_id' => $post['item']])->row_array();
            $services = $this->db->get_where('services', ['item_id' => $post['item']])->result();

            $no = 1;
            foreach ($services as $data) {
                if ($item['price'] != $data->price) {
                    $this->db->set('price', $item['price']);
                    $this->db->set('total', $item['price'] * $data->qty);
                    $this->db->where('services_id', $data->services_id);
                    $this->db->update('services');
                    $customer = $this->db->get_where('customer', ['no_services' => $data->no_services])->row_array();
                    // echo $no++ . '. ' . $customer['name'] . ' - ', $customer['no_services'];
                    // echo '<br>';
                    if ($this->db->affected_rows() > 0) {
                        $message = 'Update Paket Bulanan ' . $item['name'] . ' Pelanggan ' . $customer['no_services'] . ' A/N ' . $customer['name'];
                        $this->logs_m->activitylogs('Activity', $message);
                    }
                }
                $this->session->set_flashdata('success-sweet', 'Semua data pelanggan untuk paket ' . $item['name'] . ' sudah diperbaharui, silahkan cek di data pelanggan');
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
}
