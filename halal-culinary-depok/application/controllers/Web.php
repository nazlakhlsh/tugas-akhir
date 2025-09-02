<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Web extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        
        // REVISI: Tambahkan baris ini untuk memuat library database
        $this->load->database();
    }

    public function index()
    {
        // REVISI: Ambil data master dari database
        $data['kategori_list']  = $this->db->get('tbl_kategori')->result();
        $data['kecamatan_list'] = $this->db->get('tbl_kecamatan')->result();
        $data['harga_list']     = $this->db->get('tbl_rentang_harga')->result();

        // REVISI: Kirim variabel $data ke view
        $this->load->view('web/home', $data);
    }
}