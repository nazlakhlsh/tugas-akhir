<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Form_validation $form_validation
 * @property CI_Session $session
 * @property CI_Input $input
 * @property User_model $user_model
 */
class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->library(['form_validation', 'session']);
        $this->load->helper('url');
    }

    /**
     * Menampilkan halaman login
     */
    public function login()
    {
        if ($this->session->userdata('is_logged_in')) {
            redirect('web'); // Atau 'home', sesuaikan dengan controller utama Anda
        }
        $this->load->view('auth/login');
    }

    /**
     * Menampilkan halaman registrasi
     */
    public function register()
    {
        $this->load->view('auth/register');
    }

    /**
     * Memproses data dari form registrasi
     */
    public function process_register()
    {
        // Aturan validasi
        $this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap', 'required|trim');

        // =======================================================
        // REVISI DI SINI: Mengubah `users.email` menjadi `tbl_users.email`
        // =======================================================
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[tbl_users.email]', [
            'is_unique' => 'Email ini sudah terdaftar. Silakan gunakan email lain.'
        ]);
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('passconf', 'Konfirmasi Password', 'required|matches[password]');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('auth/register');
        } else {
            // Data untuk disimpan ke database
            $data = [
                'nama_lengkap' => html_escape($this->input->post('nama_lengkap')),
                'email'        => html_escape($this->input->post('email')),
                'password'     => password_hash($this->input->post('password'), PASSWORD_BCRYPT),
                'role'         => 'umkm' // Default role untuk pendaftar baru
            ];

            if ($this->user_model->register_user($data)) {
                $this->session->set_flashdata('success', 'Registrasi berhasil! Silakan login.');
                redirect('auth/login');
            } else {
                $this->session->set_flashdata('error', 'Terjadi kesalahan saat registrasi. Silakan coba lagi.');
                $this->load->view('auth/register');
            }
        }
    }

    /**
     * Memproses data dari form login
     */
    public function process_login()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        // Panggil fungsi validate dari model
        $user = $this->user_model->validate($email);

        // Verifikasi user dan password
        if ($user && password_verify($password, $user->password)) {
            // Data untuk disimpan ke session
            $session_data = [
                'id'           => $user->id,
                'nama_lengkap' => $user->nama_lengkap,
                'email'        => $user->email,
                'role'         => $user->role,
                'is_logged_in' => TRUE
            ];
            $this->session->set_userdata($session_data);

            // Update waktu login terakhir
            $this->user_model->update_last_login($user->id);

            // Logika redirect disederhanakan.
            redirect('web'); // Pastikan 'web' adalah controller halaman utama Anda

        } else {
            $this->session->set_flashdata('error', 'Email atau password salah, atau akun Anda tidak aktif.');
            redirect('auth/login');
        }
    }

    /**
     * Proses logout
     */
    public function logout()
    {
        $this->session->sess_destroy();
        redirect(base_url()); // Redirect ke halaman utama publik
    }
}
