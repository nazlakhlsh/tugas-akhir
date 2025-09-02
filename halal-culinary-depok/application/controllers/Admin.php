<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Admin Controller untuk Dashboard Admin
 * Sistem Informasi Spasial Sebaran Outlet Kuliner Halal
 *
 * @property CI_Session $session
 * @property CI_Input $input
 * @property Kuliner_model $kuliner
 * @property User_model $user
 */
class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Kuliner_model', 'kuliner');
        $this->load->model('User_model', 'user');
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->helper('date');

        if (!$this->session->userdata('is_logged_in')) {
            $this->session->set_flashdata('error', 'Silakan login terlebih dahulu.');
            redirect('auth/login');
        }
        if ($this->session->userdata('role') !== 'admin') {
            $this->session->set_flashdata('error', 'Hanya admin yang dapat mengakses halaman ini.');
            redirect('/');
        }
    }

    /**
     * Halaman utama dashboard admin
     */
    public function index()
    {
        $data = array(
            'title' => 'Dashboard Admin',
            'logged_in_user' => $this->user->get_user_by_id($this->session->userdata('id')),
            'pending_kuliner' => $this->kuliner->get_kuliner_by_status('pending', true),
            'statistics' => array(
                'total_kuliner' => $this->kuliner->count_kuliner_by_status(),
                'verified_kuliner' => $this->kuliner->count_kuliner_by_status('terverifikasi'),
                'pending_kuliner' => $this->kuliner->count_kuliner_by_status('pending'),
                'rejected_kuliner' => $this->kuliner->count_kuliner_by_status('ditolak'),
                'total_users' => $this->user->count_all_users()
            )
        );
        $data['active_page'] = 'dashboard';
        $this->load->view('web/admin-dashboard', $data);
    }

    /**
     * Halaman manajemen kuliner
     */
    public function manajemen_kuliner($status = 'all')
    {
        $data = array(
            'title' => 'Manajemen Data Kuliner',
            'logged_in_user' => $this->user->get_user_by_id($this->session->userdata('id')),
            'list_kuliner' => $this->kuliner->get_kuliner_by_status($status, true),
            'current_status' => $status
        );
        $data['active_page'] = 'manajemen_kuliner';
        $this->load->view('web/admin-dashboard', $data);
    }

    /**
     * Detail kuliner untuk verifikasi
     */
    public function detail_kuliner($id_kuliner)
    {
        $data_kuliner = $this->kuliner->get_kuliner_by_id($id_kuliner);

        if (!$data_kuliner) {
            $this->session->set_flashdata('error', 'Data kuliner tidak ditemukan.');
            redirect('admin/manajemen_kuliner');
        }

        $data = array(
            'title' => 'Detail Kuliner: ' . $data_kuliner->nama_kuliner,
            'logged_in_user' => $this->user->get_user_by_id($this->session->userdata('id')),
            'kuliner' => $data_kuliner
        );
        $data['active_page'] = 'detail_kuliner';
        $this->load->view('web/admin-dashboard', $data);
    }

    /**
     * Fungsi untuk menyetujui (verify) data kuliner
     */
    public function verifikasi($id_kuliner)
    {
        $result = $this->kuliner->update_status_kuliner($id_kuliner, 'terverifikasi');

        if ($result) {
            $this->session->set_flashdata('success', 'Data kuliner berhasil diverifikasi.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memverifikasi data kuliner.');
        }
        redirect('admin');
    }

    /**
     * Fungsi untuk menolak (reject) data kuliner
     */
    public function tolak($id_kuliner)
    {
        $result = $this->kuliner->update_status_kuliner($id_kuliner, 'ditolak');

        if ($result) {
            $this->session->set_flashdata('success', 'Data kuliner telah ditolak.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menolak data kuliner.');
        }
        redirect('admin');
    }

    /**
     * Fungsi untuk menghapus data kuliner secara permanen
     */
    public function hapus_kuliner($id_kuliner)
    {
        $kuliner = $this->kuliner->get_kuliner_by_id($id_kuliner);

        if (!$kuliner) {
            $this->session->set_flashdata('error', 'Data kuliner tidak ditemukan.');
            redirect('admin/manajemen_kuliner');
        }

        // Hapus foto utama jika ada
        if (!empty($kuliner->foto_utama) && file_exists('./uploads/kuliner/' . $kuliner->foto_utama)) {
            unlink('./uploads/kuliner/' . $kuliner->foto_utama);
        }

        // --- TAMBAHAN START ---
        // Hapus foto menu jika ada
        if (!empty($kuliner->foto_menu) && file_exists('./uploads/kuliner/' . $kuliner->foto_menu)) {
            unlink('./uploads/kuliner/' . $kuliner->foto_menu);
        }
        // --- TAMBAHAN END ---

        $result = $this->kuliner->delete_kuliner($id_kuliner);

        if ($result) {
            $this->session->set_flashdata('success', 'Data kuliner "' . $kuliner->nama_kuliner . '" berhasil dihapus permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data kuliner.');
        }
        redirect('admin/manajemen_kuliner');
    }

    /**
     * Halaman manajemen user
     */
    public function manajemen_users()
    {
        $data = array(
            'title' => 'Manajemen User',
            'logged_in_user' => $this->user->get_user_by_id($this->session->userdata('id')),
            'users_list' => $this->user->get_all_users()
        );
        $data['active_page'] = 'manajemen_users';
        $this->load->view('web/admin-dashboard', $data);
    }

    /**
     * Fungsi untuk menghapus user
     */
    public function hapus_user($id_user)
    {
        if ($id_user == $this->session->userdata('id')) {
            $this->session->set_flashdata('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            redirect('admin/manajemen_users');
        }

        $result = $this->user->delete_user($id_user);

        if ($result) {
            $this->session->set_flashdata('success', 'User berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus user.');
        }
        redirect('admin/manajemen_users');
    }

    /**
     * Logout admin
     */
    public function logout()
    {
        $this->session->unset_userdata(array('id', 'nama_lengkap', 'email', 'role', 'is_logged_in'));
        $this->session->set_flashdata('success', 'Anda berhasil logout.');
        redirect('auth/login');
    }
}
