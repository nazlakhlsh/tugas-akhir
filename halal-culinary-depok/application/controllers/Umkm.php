<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Form_validation $form_validation
 * @property CI_Upload $upload
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_DB_query_builder $db
 * @property Kuliner_model $kuliner
 * @property User_model $user
 */
class Umkm extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['form', 'url', 'file']);
        $this->load->library(['session', 'form_validation', 'upload']);
        $this->load->model('Kuliner_model', 'kuliner');
        $this->load->model('User_model', 'user');
        $this->load->database(); // Memuat library database

        // Keamanan: Wajib login
        if (!$this->session->userdata('is_logged_in')) {
            $this->session->set_flashdata('error', 'Silakan login terlebih dahulu.');
            redirect('auth/login');
        }

        // Keamanan: Wajib role 'umkm'
        if ($this->session->userdata('role') !== 'umkm') {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke halaman ini.');
            redirect('home');
        }
    }

    // Arahkan ke form tambah usaha secara default
    public function index()
    {
        $this->tambah_kuliner();
    }

    // Menampilkan daftar kuliner (Outlet Saya)
    public function list_kuliner()
    {
        $id_user = $this->session->userdata('id');
        $data = [
            'title'       => 'Outlet Saya',
            'user'        => $this->user->get_user_by_id($id_user),
            'outlets'     => $this->kuliner->get_kuliner_by_user($id_user),
            'active_page' => 'outlet_saya'
        ];
        $this->load->view('web/umkm-dashboard', $data);
    }

    // Method untuk menampilkan detail usaha
    public function detail($id)
    {
        $id_user = $this->session->userdata('id');
        $data_kuliner = $this->kuliner->get_kuliner_by_id_and_user($id, $id_user);

        if (!$data_kuliner) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
            redirect('umkm/list_kuliner');
            return;
        }

        $data = [
            'title'       => 'Detail Usaha Kuliner',
            'user'        => $this->user->get_user_by_id($id_user),
            'outlet'      => $data_kuliner,
            'active_page' => 'detail_usaha'
        ];
        $this->load->view('web/umkm-dashboard', $data);
    }

    // Menampilkan form tambah kuliner
    public function tambah_kuliner()
    {
        $data = [
            'title'          => 'Daftarkan Usaha Kuliner',
            'user'           => $this->user->get_user_by_id($this->session->userdata('id')),
            'active_page'    => 'daftar_usaha',
            'kategori_list'  => $this->db->get('tbl_kategori')->result(),
            'kecamatan_list' => $this->db->get('tbl_kecamatan')->result(),
            'harga_list'     => $this->db->get('tbl_rentang_harga')->result(),
        ];
        $this->load->view('web/umkm-dashboard', $data);
    }

    // Menampilkan form edit
    public function edit_kuliner($id)
    {
        $id_user = $this->session->userdata('id');
        $data_kuliner = $this->kuliner->get_kuliner_by_id_and_user($id, $id_user);

        if (!$data_kuliner) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
            redirect('umkm/list_kuliner');
            return;
        }

        $data = [
            'title'          => 'Edit Usaha Kuliner',
            'user'           => $this->user->get_user_by_id($id_user),
            'outlet'         => $data_kuliner,
            'active_page'    => 'edit_usaha',
            'kategori_list'  => $this->db->get('tbl_kategori')->result(),
            'kecamatan_list' => $this->db->get('tbl_kecamatan')->result(),
            'harga_list'     => $this->db->get('tbl_rentang_harga')->result(),
        ];
        $this->load->view('web/umkm-dashboard', $data);
    }

    // Proses menyimpan data kuliner BARU
    public function proses_simpan()
    {
        $this->form_validation->set_rules('nama_kuliner', 'Nama Kuliner', 'required|trim|is_unique[tbl_kuliner.nama_kuliner]');
        $this->form_validation->set_rules('alamat', 'Alamat', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->tambah_kuliner();
            return;
        }

        $config = [
            'upload_path'   => './uploads/kuliner/',
            'allowed_types' => 'jpg|png|jpeg',
            'max_size'      => 2048,
            'encrypt_name'  => TRUE,
        ];

        // --- Proses Upload Foto Utama ---
        $foto_utama_name = NULL;
        if (!empty($_FILES['foto_utama']['name'])) {
            $this->upload->initialize($config);

            if ($this->upload->do_upload('foto_utama')) {
                $foto_utama_name = $this->upload->data('file_name');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengunggah foto utama: ' . $this->upload->display_errors());
                redirect('umkm/tambah_kuliner');
                return;
            }
        }

        // --- Proses Upload Foto Menu ---
        $foto_menu_name = NULL;
        if (!empty($_FILES['foto_menu']['name'])) {
            $this->upload->initialize($config);

            if ($this->upload->do_upload('foto_menu')) {
                $foto_menu_name = $this->upload->data('file_name');
            } else {
                if ($foto_utama_name && file_exists('./uploads/kuliner/' . $foto_utama_name)) {
                    unlink('./uploads/kuliner/' . $foto_utama_name);
                }
                $this->session->set_flashdata('error', 'Gagal mengunggah foto menu: ' . $this->upload->display_errors());
                redirect('umkm/tambah_kuliner');
                return;
            }
        }

        // Data yang akan dikirim ke model
        $data = [
            'id_user'            => $this->session->userdata('id'),
            'nama_kuliner'       => $this->input->post('nama_kuliner', TRUE),
            'id_kategori'        => $this->input->post('id_kategori', TRUE),
            'id_kecamatan'       => $this->input->post('id_kecamatan', TRUE),
            'id_rentang_harga'   => $this->input->post('id_rentang_harga', TRUE),
            'latitude'           => $this->input->post('latitude', TRUE),
            'longitude'          => $this->input->post('longitude', TRUE),
            'alamat'             => $this->input->post('alamat', TRUE),
            'jam_buka'           => $this->input->post('jam_buka', TRUE),
            'link_gmaps'         => $this->input->post('link_gmaps', TRUE),
            'foto_utama'         => $foto_utama_name,
            'foto_menu'          => $foto_menu_name,
            'status_sertifikat'  => $this->input->post('status_sertifikat', TRUE),
            'nomor_sertifikat'   => $this->input->post('nomor_sertifikat', TRUE),
            'tanggal_berlaku'    => $this->input->post('tanggal_berlaku', TRUE)
        ];

        if ($this->kuliner->insert_kuliner($data)) {
            $this->session->set_flashdata('success', 'Usaha kuliner berhasil didaftarkan dan sedang menunggu verifikasi oleh Admin.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data. Silakan coba lagi.');
        }
        redirect('umkm/list_kuliner');
    }

    // Proses mengupdate data kuliner
    public function proses_update()
    {
        $id_kuliner = $this->input->post('id');
        $id_user = $this->session->userdata('id');

        $kuliner_lama = $this->kuliner->get_kuliner_by_id_and_user($id_kuliner, $id_user);
        if (!$kuliner_lama) {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('umkm/list_kuliner');
            return;
        }

        $is_unique = ($this->input->post('nama_kuliner') != $kuliner_lama->nama_kuliner) ? '|is_unique[tbl_kuliner.nama_kuliner]' : '';
        $this->form_validation->set_rules('nama_kuliner', 'Nama Kuliner', 'required|trim' . $is_unique);
        $this->form_validation->set_rules('alamat', 'Alamat', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->edit_kuliner($id_kuliner);
            return;
        }

        $data = [
            'nama_kuliner'       => $this->input->post('nama_kuliner', TRUE),
            'id_kategori'        => $this->input->post('id_kategori', TRUE),
            'id_kecamatan'       => $this->input->post('id_kecamatan', TRUE),
            'id_rentang_harga'   => $this->input->post('id_rentang_harga', TRUE),
            'latitude'           => $this->input->post('latitude', TRUE),
            'longitude'          => $this->input->post('longitude', TRUE),
            'alamat'             => $this->input->post('alamat', TRUE),
            'jam_buka'           => $this->input->post('jam_buka', TRUE),
            'link_gmaps'         => $this->input->post('link_gmaps', TRUE),
            'status_sertifikat'  => $this->input->post('status_sertifikat', TRUE),
            'nomor_sertifikat'   => $this->input->post('nomor_sertifikat', TRUE),
            'tanggal_berlaku'    => $this->input->post('tanggal_berlaku', TRUE)
        ];

        $config = [
            'upload_path'   => './uploads/kuliner/',
            'allowed_types' => 'jpg|png|jpeg',
            'max_size'      => 2048,
            'encrypt_name'  => TRUE,
        ];

        // Proses upload foto utama baru jika ada
        if (!empty($_FILES['foto_utama']['name'])) {
            $this->upload->initialize($config);

            if ($this->upload->do_upload('foto_utama')) {
                if ($kuliner_lama->foto_utama && file_exists('./uploads/kuliner/' . $kuliner_lama->foto_utama)) {
                    unlink('./uploads/kuliner/' . $kuliner_lama->foto_utama);
                }
                $data['foto_utama'] = $this->upload->data('file_name');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengunggah foto utama baru: ' . $this->upload->display_errors());
                redirect('umkm/edit_kuliner/' . $id_kuliner);
                return;
            }
        }

        // Proses upload foto menu baru jika ada
        if (!empty($_FILES['foto_menu']['name'])) {
            $this->upload->initialize($config);

            if ($this->upload->do_upload('foto_menu')) {
                if ($kuliner_lama->foto_menu && file_exists('./uploads/kuliner/' . $kuliner_lama->foto_menu)) {
                    unlink('./uploads/kuliner/' . $kuliner_lama->foto_menu);
                }
                $data['foto_menu'] = $this->upload->data('file_name');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengunggah foto menu baru: ' . $this->upload->display_errors());
                redirect('umkm/edit_kuliner/' . $id_kuliner);
                return;
            }
        }

        if ($this->kuliner->update_kuliner($id_kuliner, $data)) {
            $this->session->set_flashdata('success', 'Data berhasil diperbarui dan akan diverifikasi ulang oleh Admin.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('umkm/list_kuliner');
    }

    // Proses menghapus data
    public function hapus_kuliner($id)
    {
        $id_user = $this->session->userdata('id');
        $kuliner = $this->kuliner->get_kuliner_by_id_and_user($id, $id_user);

        if (!$kuliner) {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('umkm/list_kuliner');
            return;
        }

        // Hapus foto utama dari server
        if ($kuliner->foto_utama && file_exists('./uploads/kuliner/' . $kuliner->foto_utama)) {
            unlink('./uploads/kuliner/' . $kuliner->foto_utama);
        }

        // Hapus foto menu dari server
        if ($kuliner->foto_menu && file_exists('./uploads/kuliner/' . $kuliner->foto_menu)) {
            unlink('./uploads/kuliner/' . $kuliner->foto_menu);
        }

        if ($this->kuliner->delete_kuliner($id)) {
            $this->session->set_flashdata('success', 'Data usaha kuliner berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('umkm/list_kuliner');
    }
}
