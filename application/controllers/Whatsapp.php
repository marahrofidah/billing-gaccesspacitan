<?php
ob_start();
defined('BASEPATH') or exit('No direct script access allowed');

class Whatsapp extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(['customer_m', 'package_m', 'services_m', 'setting_m', 'bill_m', 'income_m', 'mikrotik_m']);
    }
    public function index()
    {
        is_logged_in();

        $data['title'] = 'Whatsapp';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['whatsapp'] = $this->db->get('whatsapp')->row_array();
        $data['other'] = $this->db->get('other')->row_array();
        $this->template->load('backend', 'backend/whatsapp/data', $data);
    }
    // public function sendmsg($target, $message)
    // {
    //     $whatsapp = $this->db->get('whatsapp')->row_array();
    //     if ($whatsapp['is_active'] == 1) {
    //         if ($whatsapp['vendor'] == 'WAblas') {
    //             $curl = curl_init();
    //             $token = $whatsapp['token'];

    //             $data = [
    //                 'phone' => indo_tlp($target),
    //                 'message' => $message,
    //                 'secret' => false, // or true
    //                 'priority' => true, // or true
    //             ];

    //             curl_setopt(
    //                 $curl,
    //                 CURLOPT_HTTPHEADER,
    //                 array(
    //                     "Authorization: $token",
    //                 )
    //             );
    //             curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    //             curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //             curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    //             curl_setopt($curl, CURLOPT_URL, "$whatsapp[domain_api]/api/send-message");

    //             curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    //             curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    //             $result = curl_exec($curl);
    //             $result = json_decode($result, true);
    //             curl_close($curl);
    //             if ($result['status'] == 1) {
    //                 $this->session->set_flashdata('success-sweet', $result['message'] . '<br> Sisa Kuota : ' . $result['data']['quota']);
    //             } else {
    //                 $this->session->set_flashdata('error-sweet', $result['message']);
    //             }
    //         }
    //         if ($whatsapp['vendor'] == 'Starsender') {
    //             $apikey = $whatsapp['api_key'];
    //             $tujuan = indo_tlp($target);
    //             $pesan = $message;

    //             $curl = curl_init();

    //             curl_setopt_array($curl, array(
    //                 CURLOPT_URL => 'https://starsender.online/api/sendText?message=' . rawurlencode($pesan) . '&tujuan=' . rawurlencode($tujuan . '@s.whatsapp.net'),
    //                 CURLOPT_RETURNTRANSFER => true,
    //                 CURLOPT_ENCODING => '',
    //                 CURLOPT_MAXREDIRS => 10,
    //                 CURLOPT_TIMEOUT => 0,
    //                 CURLOPT_FOLLOWLOCATION => true,
    //                 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //                 CURLOPT_CUSTOMREQUEST => 'POST',
    //                 CURLOPT_HTTPHEADER => array(
    //                     'apikey: ' . $apikey
    //                 ),
    //             ));


    //             $result = curl_exec($curl);
    //             $result = json_decode($result, true);
    //             curl_close($curl);
    //             if ($result['status'] == 1) {
    //                 $this->session->set_flashdata('success-sweet', $result['message']);
    //             } else {
    //                 $this->session->set_flashdata('error-sweet', $result['message']);
    //             }
    //         }
    //     } else {
    //         $this->session->set_flashdata('error-sweet', 'Aktifkan dulu Whatsapp Gateway nya bos !');
    //     }
    // }
    public function bill()
    {
        $data['title'] = 'Kirim Tagihan';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['other'] = $this->db->get('other')->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $this->template->load('backend', 'backend/whatsapp/bill', $data);
    }

    public function sendtime()
    {
    }

    public function edit()
    {
        $post = $this->input->post(null, true);
        if (!empty($post['period'])) {
            $post['period'] = 0;
        } else {
            $post['period'] = 1;
        }
        $params = [
            'vendor' => $post['vendor'],
            'period' => $post['period'],
            'version' => $post['version'],
            'username' => $post['username'],
            'domain_api' => $post['domain_api'],
            'token' => $post['token'],
            'api_key' => $post['api_key'],
            'interval_message' => $post['interval_message'],
            'createinvoice' => $post['createinvoice'],
            'paymentinvoice' => $post['paymentinvoice'],
            'duedateinvoice' => $post['duedateinvoice'],
            'create_help' => $post['create_help'],
            'create_help_admin' => $post['create_help_admin'],
            'reminderinvoice' => $post['reminderinvoice'],
            'is_active' => $post['is_active'],
        ];
        $this->db->set('date_reminder',  $post['date_reminder']);

        $this->db->set('sch_due', $post['duedateinvoice']);
        $this->db->set('sch_resend', $post['sch_resend']);
        $this->db->set('sch_before_due', $post['reminderinvoice']);
        $this->db->update('other');
        $this->db->where('id',  $post['id']);
        $this->db->update('whatsapp', $params);


        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success-sweet', 'Whatsapp Gateway berhasil diperbaharui');
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function sendmessagecustomer()
    {
        $post = $this->input->post(null, TRUE);
        $whatsapp = $this->db->get('whatsapp')->row_array();
        $company = $this->db->get('company')->row_array();
        $customer = $this->db->get_where('customer', ['customer_id' => $post['target']])->row_array();
        $target = indo_tlp($customer['no_wa']);
        $search  = array('{name}', '{noservices}', '{companyname}',  '{slogan}', '{link}', '{e}', '{email}');
        $replace = array($customer['name'], $customer['no_services'],  $company['company_name'], $company['sub_name'], base_url(), '', $customer['email']);
        $subject = $post['message'];
        $message = str_replace($search, $replace, $subject);
        sendmsg($target, $message);
        // var_dump($message);
        // die;
        redirect('customer/whatsapp');
    }
    public function sendmessage()
    {
        $whatsapp = $this->db->get('whatsapp')->row_array();
        $company = $this->db->get('company')->row_array();
        $post = $this->input->post(null, TRUE);
        if ($whatsapp['is_active'] == 1) {
            if ($post['target'] == 'allcustomer') {
                $customer = $this->customer_m->getCustomerAll()->result();
            } elseif ($post['target'] == 'customeractive') {
                $customer = $this->db->get_where('customer', ['c_status' => 'Aktif'])->result();
                if (count($customer) == 0) {
                    $this->session->set_flashdata('error', 'Gagal, Tidak ada daftar pelanggan Aktif !');
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } elseif ($post['target'] == 'customernonactive') {
                $customer = $this->db->get_where('customer', ['c_status' => 'Non-Aktif'])->result();
                if (count($customer) == 0) {
                    $this->session->set_flashdata('error', 'Gagal, Tidak ada daftar pelanggan Non-Aktif !');
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } elseif ($post['target'] == 'customerwaiting') {
                $customer = $this->db->get_where('customer', ['c_status' => 'Menunggu'])->result();
                if (count($customer) == 0) {
                    $this->session->set_flashdata('error', 'Gagal, Tidak ada daftar pelanggan Menunggu !');
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            $no = 1;
            foreach ($customer as $data) {


                $target = indo_tlp($data->no_wa);
                $search  = array('{name}', '{noservices}', '{companyname}',  '{slogan}', '{link}', '{e}', '{email}');
                $replace = array($data->name, $data->no_services,  $company['company_name'], $company['sub_name'], base_url(), '', $data->email);
                $subject = $post['message'];
                $message = str_replace($search, $replace, $subject);
                $range = $no++ * $whatsapp['interval_message'];

                $jadwall = time() + (1  * $range);
                $time = date('Y-m-d H:i:s', $jadwall);

                sendmsgsch($target, $message, $time);
            }
            $this->session->set_flashdata('success-sweet', 'Eksekusi Berhasil, Silahkan cek di schedule WA Gateway Anda !');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            echo 'Whatsapp Gateway Non Aktif';
        }
    }
    public function sendmessagecoverage()
    {
        $whatsapp = $this->db->get('whatsapp')->row_array();
        $company = $this->db->get('company')->row_array();
        $post = $this->input->post(null, TRUE);
        if ($whatsapp['is_active'] == 1) {
            $coverage = $this->db->get_where('coverage', ['coverage_id' => $post['target']])->row_array();
            $customer = $this->db->get_where('customer', ['coverage' => $post['target']])->result();
            if (count($customer) == 0) {
                $this->session->set_flashdata('error-sweet', 'Gagal, Tidak ada daftar pelanggan di coverage area ' . $coverage['c_name']);
                redirect($_SERVER['HTTP_REFERER']);
            }
            $no = 1;
            foreach ($customer as $data) {
                $target = indo_tlp($data->no_wa);
                $search  = array('{name}', '{noservices}', '{companyname}',  '{slogan}', '{link}', '{e}', '{email}');
                $replace = array($data->name, $data->no_services,  $company['company_name'], $company['sub_name'], base_url(), '', $data->email);
                $subject = $post['message'];
                $message = str_replace($search, $replace, $subject);
                $range = $no++ * $whatsapp['interval_message'];

                $jadwall = time() + (1  * $range);
                $time = date('Y-m-d H:i:s', $jadwall);

                sendmsgsch($target, $message, $time);
            }
            $this->session->set_flashdata('success-sweet', 'Eksekusi Berhasil, Silahkan cek di schedule WA Gateway Anda !');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            echo 'Whatsapp Gateway Non Aktif';
        }
    }
    public function sendbill()
    {
        $whatsapp = $this->db->get('whatsapp')->row_array();
        $company = $this->db->get('company')->row_array();
        $post = $this->input->post(null, TRUE);

        if ($whatsapp['is_active'] == 1) {
            if ($post['target'] == 'customeractive') {
                $status = 'Aktif';
                $datainvoice = $this->bill_m->getInvoiceWhatsapp($status, $post['month'], $post['year'])->result();
                if (count($datainvoice) == 0) {
                    $this->session->set_flashdata('error-sweet', 'Gagal, Tidak ada daftar Tagihan di Pelanggan Aktif untuk Periode ' . indo_month($post['month']) . ' ' .  $post['year'] . ' !');
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } elseif ($post['target'] == 'customernonactive') {
                $status = 'Non-Aktif';
                $datainvoice = $this->bill_m->getInvoiceWhatsapp($status, $post['month'], $post['year'])->result();
                if (count($datainvoice) == 0) {
                    $this->session->set_flashdata('error-sweet', 'Gagal, Tidak ada daftar Tagihan di Pelanggan Non-Aktif untuk Periode ' . indo_month($post['month']) . ' ' .  $post['year'] . ' !');
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            $no = 1;
            foreach ($datainvoice as $wa) {
                $target = indo_tlp($wa->no_wa);
                if ($wa->codeunique == 1) {
                    $codeunique = $wa->codeunique;
                } else {
                    $codeunique = 0;
                }
                $nominal = $wa->amount + $codeunique - $wa->disc_coupon;
                $search  = array('{name}', '{noservices}', '{email}', '{invoice}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}', 'email');
                $replace = array($wa->name, $wa->no_services, $wa->email, $wa->invoice,  indo_month($wa->month), $wa->year, indo_month($wa->month) . ' ' . $wa->year, indo_date($wa->inv_due_date), indo_currency($nominal), $company['company_name'], $company['sub_name'], base_url(), '', $wa->email);
                $subject = $post['message'];
                $message = str_replace($search, $replace, $subject);

                $range = $no++ * $whatsapp['interval_message'];

                $jadwall = time() + (1  * $range);
                $time = date('Y-m-d H:i:s', $jadwall);

                sendmsgsch($target, $message, $time);
            }
            $this->session->set_flashdata('success-sweet', 'Eksekusi Kirim Tagihan ke Pelanggan ' . $status . ' Periode ' . indo_month($post['month']) . ' ' . $post['year'] . ' Berhasil, Silahkan cek di schedule WA Gateway Anda !');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            echo 'Whatsapp Gateway Non Aktif';
        }
    }
    public function sendbillpaid($inv)
    {
        $invoice = $this->db->get_where('invoice', ['invoice' => $inv])->row_array();
        $customer = $this->db->get_where('customer', ['no_services' => $invoice['no_services']])->row_array();
        $other = $this->db->get('other')->row_array();
        $company = $this->db->get('company')->row_array();
        if ($customer['codeunique'] == 1) {
            $codeunique = $invoice['code_unique'];
        } else {
            $codeunique = 0;
        }
        $nominalWA = indo_currency($invoice['amount'] - $invoice['disc_coupon'] + $codeunique);
        $search  = array('{name}', '{noservices}', '{email}', '{invoice}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}');
        $replace = array($customer['name'], $customer['no_services'], $customer['email'], $inv, indo_month($invoice['month']), $invoice['year'], indo_month($invoice['month']) . ' ' . $invoice['year'], indo_date($invoice['inv_due_date']), $nominalWA, $company['company_name'], $company['sub_name'], base_url(), '');
        $subject = $other['thanks_wa'];
        $target = indo_tlp($customer['no_wa']);
        $message = str_replace($search, $replace, $subject);
        sendmsgpaid($target, $message, $inv);
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function sendbillunpaid($inv)
    {
        $invoice = $this->db->get_where('invoice', ['invoice' => $inv])->row_array();
        $customer = $this->db->get_where('customer', ['no_services' => $invoice['no_services']])->row_array();
        $other = $this->db->get('other')->row_array();
        $company = $this->db->get('company')->row_array();
        if ($customer['codeunique'] == 1) {
            $codeunique = $invoice['code_unique'];
        } else {
            $codeunique = 0;
        }
        $nominalWA = indo_currency($invoice['amount'] - $invoice['disc_coupon'] + $codeunique);
        $search  = array('{name}', '{noservices}', '{email}', '{invoice}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}');
        $replace = array($customer['name'], $customer['no_services'], $customer['email'], $inv, indo_month($invoice['month']), $invoice['year'], indo_month($invoice['month']) . ' ' . $invoice['year'], indo_date($invoice['inv_due_date']), $nominalWA, $company['company_name'], $company['sub_name'], base_url(), '');
        $subject = $other['say_wa'];

        $message = str_replace($search, $replace, $subject);
        $target = indo_tlp($customer['no_wa']);
        $message = str_replace($search, $replace, $subject);
        sendmsgbill($target, $message, $inv);
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function sendbillduedate($inv)
    {
        $invoice = $this->db->get_where('invoice', ['invoice' => $inv])->row_array();
        $customer = $this->db->get_where('customer', ['no_services' => $invoice['no_services']])->row_array();
        $other = $this->db->get('other')->row_array();
        $company = $this->db->get('company')->row_array();
        if ($customer['codeunique'] == 1) {
            $codeunique = $invoice['code_unique'];
        } else {
            $codeunique = 0;
        }
        $nominalWA = $invoice['amount'] - $invoice['disc_coupon'] + $codeunique;
        $creator = $this->db->get_where('user', ['id' => $invoice['create_by']])->row_array();
        $search = array('{name}', '{noservices}', '{nova}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}', '{slogan}', '{link}', '{e}', '{receiver}');
        $replace = array($customer['name'], $customer['no_services'], $invoice['no_va'], $invoice['month'], $invoice['year'], indo_month($invoice['month']) . ' ' . $invoice['year'], indo_date($invoice['inv_due_date']), $nominalWA, $company['company_name'], $company['sub_name'], base_url(), '', $creator['name']);
        $message = str_replace($search, $replace, $other['body_wa']);
        sendmsgschduedate(indo_tlp($customer['no_wa']), $message, time() + 5, $inv);
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function lastweek()
    {
        $whatsapp = $this->db->get('whatsapp')->row_array();
        $token = $whatsapp['token'];
        $curl = curl_init();


        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                "Authorization: $token",
            )
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, "$whatsapp[domain_api]/api/report-realtime");
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($curl);
        curl_close($curl);

        echo "<pre>";
        print_r($result);
    }
    public function report()
    {
        $whatsapp = $this->db->get('whatsapp')->row_array();
        $token = $whatsapp['token'];
        $curl = curl_init();
        $last = date('Y-m-d');
        $start = date('Y-m-d', strtotime('-7 days', strtotime($last)));

        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                "Authorization: $token",
            )
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($curl, CURLOPT_URL, "$whatsapp[domain_api]/api/report?from=2021-05-21&to=2021-05-24&limit=10");
        curl_setopt($curl, CURLOPT_URL, "$whatsapp[domain_api]/api/report?from=$start&to=$last&limit=100");
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($curl);
        curl_close($curl);

        echo "<pre>";
        print_r($result);
    }

    public function sendfile()
    {
        $post = $this->input->post(null, TRUE);
        $whatsapp = $this->db->get('whatsapp')->row_array();
        $curl = curl_init();
        $token = $whatsapp['token'];
        $phone = $post['target'];
        $message = $post['message'];
        $config['upload_path']          = './assets/images/sendfile';
        $config['allowed_types']        = 'gif|jpg|png|jpeg';
        $config['max_size']             = 2048; // 2 Mb
        $config['overwrite'] = true; //overwrite user avatar
        $config['file_name']             = 'sendfile';
        $this->load->library('upload', $config);

        // var_dump($filename);
        // die;
        if (@FILES['picture']['name'] != null) {
            if ($this->upload->do_upload('picture')) {
                $data = [
                    'phone' => $phone,
                    'caption' => $message, // can be null
                    'image' => base_url('assets/images/sendfile/' . $this->upload->data('file_name')),
                    'secret' => true, // or true
                    'priority' => true, // or true
                ];
            }
        }

        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                "Authorization: $token",
            )
        );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_URL, "$whatsapp[domain_api]/api/send-image");
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($curl);
        curl_close($curl);

        echo "<pre>";
        print_r($result);
    }

    public function testsend()
    {
        $post = $this->input->post(null, TRUE);
        sendmsg($post['number'], $post['message']);
        redirect('whatsapp');
    }
    public function testschedule()
    {
        $post = $this->input->post(null, TRUE);
        $whatsapp = $this->db->get('whatsapp')->row_array();
        $APIkey = $whatsapp['api_key'];

        if ($whatsapp['vendor'] == 'WAblas') {
            $curl = curl_init();
            $token =  $whatsapp['token'];
            $payload = [
                "data" => [
                    [
                        'category' => 'text',
                        'phone' => '6285283935826',
                        'scheduled_at' => '2022-06-26 20:59:00',
                        'text' => 'test interval message',
                    ],


                ]
            ];
            curl_setopt(
                $curl,
                CURLOPT_HTTPHEADER,
                array(
                    "Authorization: $token",
                    "Content-Type: application/json"
                )
            );
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($curl, CURLOPT_URL, "$whatsapp[domain_api]/api/v2/schedule");
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            $result = curl_exec($curl);
            curl_close($curl);
            echo "<pre>";
            print_r($result);
        }
        if ($whatsapp['vendor'] == 'Starsender') {

            $apikey = $APIkey;
            $tujuan = '6282337481227';
            $pesan = 'test schedule';
            $jadwal = date('Y-m-d H:i:s');
            echo $jadwal;
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://starsender.online/api/v2/sendText?message=' . rawurlencode($pesan) . '&tujuan=' . rawurlencode($tujuan . '@s.whatsapp.net') . '&jadwal=' . rawurlencode($jadwal),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => array(
                    'apikey: ' . $apikey
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            echo $response;
        }
    }

    public function template()
    {
        $data['title'] = 'Template Whatsapp';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['company'] = $this->db->get('company')->row_array();
        $data['other'] = $this->db->get('other')->row_array();
        $this->template->load('backend', 'backend/whatsapp/template', $data);
    }

    public function settemplate()
    {
        $post = $this->input->post(null, TRUE);
        $this->db->set('say_wa', $post['say_wa']);
        $this->db->set('body_wa', $post['body_wa']);
        $this->db->set('footer_wa', $post['footer_wa']);
        $this->db->set('thanks_wa', $post['thanks_wa']);
        $this->db->set('checkout', $post['checkout']);
        $this->db->set('add_customer', $post['add_customer']);
        $this->db->set('reset_password', $post['reset_password']);
        $this->db->set('code_otp', $post['code_otp']);
        $this->db->set('create_help', $post['create_help']);
        $this->db->update('other');

        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success-sweet', 'Template Text Whatsapp Berhasil diperbaharui');
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function cekconnection()
    {
        $post = $this->input->post(null, TRUE);
        $company = $this->db->get('company')->row_array();
        $whatsapp = $this->db->get('whatsapp')->row_array();
        if ($whatsapp['vendor'] == 'WAblas') {
            if ($whatsapp['version'] == 0) {
                $curl = curl_init();
                $token = $whatsapp['token'];
                curl_setopt(
                    $curl,
                    CURLOPT_HTTPHEADER,
                    array(
                        "Authorization: $token",
                    )
                );
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_URL, "$whatsapp[domain_api]/api/device/info?token=$token");
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                $result = curl_exec($curl);
                $result = json_decode($result, true);
                curl_close($curl);
                if ($result['data']['whatsapp']['status'] == 'connected') {
                    echo "<div class='badge badge-success'>Connected</div> Sisa Kuota : " . $result['data']['whatsapp']['quota'] . " Expired : " . $result['data']['whatsapp']['expired'];
                } else {
                    echo "<div class='badge badge-danger'>Disconnect</div>";
                }
            } else {
                $curl = curl_init();
                $token = $whatsapp['token'];
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "$whatsapp[domain_api]/api/device/info?token=$token",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_POSTFIELDS => "phone=$company[phonecode]$company[whatsapp]&message=test",
                    CURLOPT_HTTPHEADER => array(),
                ));

                $response = curl_exec($curl);

                curl_close($curl);
                // echo $response;
                $result = json_decode($response, true);
                if ($result['data']['status'] == 'connected') {
                    echo "<div class='badge badge-success'>Connected</div> Sisa Kuota : " . $result['data']['quota'] . " Expired : " . $result['data']['expired_date'];
                } else {
                    echo "<div class='badge badge-danger'>Disconnect</div>";
                }
            }
        }
        if ($whatsapp['vendor'] == 'Starsender') {
            $apikey = $whatsapp['api_key'];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://starsender.online/api/v1/getDevice',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => array(
                    'apikey: ' . $apikey
                ),
            ));

            $result = curl_exec($curl);
            $result = json_decode($result, true);
            curl_close($curl);
            // echo $result['data']['0']['status'];
            // echo $response;
            if ($result['data']['0']['status'] == 'connected') {

                echo "<div class='badge badge-success'>Connected</div>";
            } else {
                echo "<div class='badge badge-danger'>Disconnect</div>";
            }
        }
    }

    public function relog()
    {
        $whatsapp = $this->db->get('whatsapp')->row_array();
        $apikey = $whatsapp['api_key'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://starsender.online/api/relogDevice',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'apikey: ' . $apikey
            ),
        ));

        $result = curl_exec($curl);
        $result = json_decode($result, true);
        curl_close($curl);
        if ($result['status'] == 'true') {
            $this->session->set_flashdata('success-sweet', $result['message']);
        } else {
            $this->session->set_flashdata('error-sweet', $result['message']);
        }

        redirect($_SERVER['HTTP_REFERER']);
    }
    public function getdevice()
    {
        $whatsapp = $this->db->get('whatsapp')->row_array();
        $apikey = $whatsapp['api_key'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://starsender.online/api/v1/getDevice',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'apikey: ' . $apikey
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }

    public function sendduedate()
    {
        $whatsapp = $this->db->get('whatsapp')->row_array();
        $other = $this->db->get('other')->row_array();
        if ($other['sch_due'] == 1) {
            $today = date('Y-m-d');
            $query = "SELECT *  FROM `invoice`
                                    Join `customer` ON `invoice`.`no_services` = `customer`.`no_services`
                                        WHERE `invoice`.`inv_due_date` =  '$today' and `invoice`.`send_due` = '' and  `invoice`.`status` =  'BELUM BAYAR'";
            $bill = $this->db->query($query)->result();
            if (count($bill) > 0) {
                if ($whatsapp['is_active'] == 1) {
                    if ($whatsapp['duedateinvoice'] == 1) {
                        $no = 1;
                        foreach ($bill as $data) {
                            echo $no++ . ' ' . $data->name . ' - ' . $data->no_services  . '<br>';
                            $range = $no++ * $whatsapp['interval_message'];

                            $jadwall = time() + (1  * $range);
                            $time = date('Y-m-d H:i:s', $jadwall);

                            $month = $data->month;
                            $year = $data->year;
                            $amount = $data->amount;
                            $other = $this->db->get('other')->row_array();
                            $company = $this->db->get('company')->row_array();
                            $target = indo_tlp($data->no_wa);

                            if ($data->codeunique > 0) {
                                $codeunique = $data->code_unique;
                            } else {
                                $codeunique = 0;
                            }
                            $nominalWA = indo_currency($amount - $data->disc_coupon + $codeunique);
                            $search  = array('{name}', '{noservices}', '{invoice}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}');
                            $replace = array($data->name, $data->no_services, $data->invoice, indo_month($month), $year, indo_month($month) . ' ' . $year, indo_date($data->inv_due_date), $nominalWA, $company['company_name'], $company['sub_name'], base_url(), '');
                            $subject = $other['body_wa'];
                            $message = str_replace($search, $replace, $subject);

                            sendmsgschduedate($target, $message, $time, $data->invoice);
                        }
                    } else {
                        echo 'Auto Reminder Non Active';
                    }
                } else {
                    echo 'Whatsapp Gateway Non Active';
                }
            } else {
                echo 'Tidak ada tagihan';
            }
        }
    }
    public function sendbeforeduedate()
    {
        $whatsapp = $this->db->get('whatsapp')->row_array();
        $other = $this->db->get('other')->row_array();
        if ($other['sch_before_due'] == 1) {
            $before        = mktime(0, 0, 0, date("n"), date("j") + $other['date_reminder'], date("Y"));
            $beforedue = date('Y-m-d', $before);
            $query = "SELECT *
                                    FROM `invoice`
                                    Join `customer` ON `invoice`.`no_services` = `customer`.`no_services`
                                        WHERE `invoice`.`inv_due_date` = '$beforedue' and `invoice`.`send_before_due` =''  and
                                       `invoice`.`status` =  'BELUM BAYAR'";
            $bill = $this->db->query($query)->result();
            if (count($bill) > 0) {
                if ($whatsapp['is_active'] == 1) {
                    if ($whatsapp['duedateinvoice'] == 1) {
                        $no = 1;
                        foreach ($bill as $data) {
                            echo $no++ . $data->name . $data->no_services  . '<br>';
                            $range = $no++ * $whatsapp['interval_message'];

                            $jadwall = time() + (1  * $range);
                            $time = date('Y-m-d H:i:s', $jadwall);

                            $month = $data->month;
                            $year = $data->year;
                            $amount = $data->amount;
                            $other = $this->db->get('other')->row_array();
                            $company = $this->db->get('company')->row_array();
                            $target = indo_tlp($data->no_wa);
                            if ($data->codeunique > 0) {
                                $codeunique = $data->code_unique;
                            } else {
                                $codeunique = 0;
                            }
                            $nominalWA = indo_currency($amount - $data->disc_coupon + $codeunique);
                            $search  = array('{name}', '{noservices}', '{invoice}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}');
                            $replace = array($data->name, $data->no_services,  $data->invoice, indo_month($month), $year, indo_month($month) . ' ' . $year, indo_date($data->inv_due_date), $nominalWA, $company['company_name'], $company['sub_name'], base_url(), '');
                            $subject = $other['footer_wa'];
                            $message = str_replace($search, $replace, $subject);

                            sendmsgschbeforedue($target, $message, $time, $data->invoice);
                        }
                    } else {
                        echo 'Auto Reminder Non Active';
                    }
                } else {
                    echo 'Whatsapp Gateway Non Active';
                }
            } else {
                echo 'Tidak ada tagihan';
            }
        }
    }
    public function resendbill()
    {
        $whatsapp = $this->db->get('whatsapp')->row_array();
        $other = $this->db->get('other')->row_array();
        if ($other['sch_resend'] == 1) {
            $month = date('m');
            $year = date('Y');
            $query = "SELECT *
                                    FROM `invoice`
                                    Join `customer` ON `invoice`.`no_services` = `customer`.`no_services`
                                        WHERE  `invoice`.`send_bill` =''  and
                                       `invoice`.`status` =  'BELUM BAYAR' and  `invoice`.`year` =  $year and `invoice`.`month` =  $month";
            $bill = $this->db->query($query)->result();
            if (count($bill) > 0) {
                if ($whatsapp['is_active'] == 1) {

                    $no = 1;
                    foreach ($bill as $data) {
                        echo $no++ . $data->name . $data->no_services  . '<br>';
                        $range = $no++ * $whatsapp['interval_message'];

                        $jadwall = time() + (1  * $range);
                        $time = date('Y-m-d H:i:s', $jadwall);

                        $month = $data->month;
                        $year = $data->year;
                        $amount = $data->amount;
                        $other = $this->db->get('other')->row_array();
                        $company = $this->db->get('company')->row_array();
                        $target = indo_tlp($data->no_wa);
                        if ($data->codeunique > 0) {
                            $codeunique = $data->code_unique;
                        } else {
                            $codeunique = 0;
                        }
                        $nominalWA = indo_currency($amount - $data->disc_coupon + $codeunique);
                        $search  = array('{name}', '{noservices}', '{invoice}', '{month}', '{year}', '{period}', '{duedate}', '{nominal}', '{companyname}',  '{slogan}', '{link}', '{e}', '{invoice}');
                        $replace = array($data->name, $data->no_services, $data->invoice, indo_month($month), $year, indo_month($month) . ' ' . $year, indo_date($data->inv_due_date), $nominalWA, $company['company_name'], $company['sub_name'], base_url(), '', $data->invoice);
                        $subject = $other['say_wa'];
                        $message = str_replace($search, $replace, $subject);

                        sendmsgschbill($target, $message, $time, $data->invoice);
                    }
                } else {
                    echo 'Whatsapp Gateway Non Active';
                }
            } else {
                echo 'Tidak ada tagihan';
            }
        }
    }

    public function schedule()
    {

        $curl = curl_init();
        $token = "MrKsJeZdDWbbDpUbRGra0um9YXUcRjU09ObrGJQ0yoK9bhMmALbIOPw8oxHknjQY";
        $payload = [
            "data" => [
                [
                    'category' => 'text',
                    'phone' => indo_tlp(6285283935826),
                    'scheduled_at' => '2022-05-20 13:20:00',
                    'text' => 'Hallo kakak',
                ]
            ]
        ];
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                "Authorization: $token",
                "Content-Type: application/json"
            )
        );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($curl, CURLOPT_URL, "https://solo.wablas.com/api/v2/send-schedule");
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($curl);
        curl_close($curl);
        echo "<pre>";
        print_r($result);
    }

    public function testv2()
    {
        $whatsapp = $this->db->get('whatsapp')->row_array();

        if ($whatsapp['vendor'] == 'WAblas') {
            if ($whatsapp['version'] == 1) {
                $token = $whatsapp['token'];
                $curl = curl_init();
                $payload[] = [
                    'phone' => indo_tlp(6285283234226),
                    'message' => 'test',
                    'secret' => false,
                    'retry' => false,
                    'isGroup' => false
                ];
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "$whatsapp[domain_api]/api/v2/send-message",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode(['data' => $payload]),
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: ' . $token,
                        'Content-Type: application/json'
                    ),
                ));
                $result = curl_exec($curl);
                $result = json_decode($result, true);
                curl_close($curl);
                echo $result['status'];
                echo '<br>';
                echo $result;
            } else {
                $curl = curl_init();
                $token = $whatsapp['token'];

                $data = [
                    'phone' => indo_tlp(6285283234226),
                    'message' => 'test',
                    'secret' => false, // or true
                    'priority' => true, // or true
                ];

                curl_setopt(
                    $curl,
                    CURLOPT_HTTPHEADER,
                    array(
                        "Authorization: $token",
                    )
                );
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($curl, CURLOPT_URL, "$whatsapp[domain_api]/api/send-message");
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                $result = curl_exec($curl);
                $result = json_decode($result, true);
                curl_close($curl);
                echo $result['status'];
                echo '<br>';
                echo $result;
            }
        }
        if ($whatsapp['vendor'] == 'Starsender') {
        }
    }
    public function testsch()
    {
        $whatsapp = $this->db->get('whatsapp')->row_array();
        if ($whatsapp['vendor'] == 'WAblas') {
            if ($whatsapp['version'] == 1) {
            } else {
                $time = time();
                $curl = curl_init();
                $token = $whatsapp['token'];
                $dateex = date('Y-m-d', $time);
                $timeex = date('H:i', $time);
                $data = [
                    'phone' => indo_tlp(6285283234226),
                    'message' => 'test',
                    'date' => $dateex,
                    'time' => $timeex,
                ];

                curl_setopt(
                    $curl,
                    CURLOPT_HTTPHEADER,
                    array(
                        "Authorization: $token",
                    )
                );
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($curl, CURLOPT_URL, "$whatsapp[domain_api]/api/schedule");
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl, CURLOPT_TIMEOUT, 0);
                $result = curl_exec($curl);
                $result = json_decode($result, true);
                curl_close($curl);
                echo $result['status'];
            }
        }
        if ($whatsapp['vendor'] == 'Starsender') {
        }
    }
}
