<?php 
defined('BASEPATH') OR exit('No direct script access allowed'); 

class Home extends CI_Controller { 

    public function __construct() {
        parent::__construct();
        // PERUBAHAN: Memuat library session agar bisa digunakan di view
        // Ini memungkinkan view untuk memeriksa $this->session->userdata('is_logged_in')
        $this->load->library('session');
        $this->load->helper('url'); // Juga muat URL helper untuk site_url() di view
    }

    public function index() 
    { 
        $data = array( 
            'judul' => 'HalalCulinary Depok', // Judul bisa disesuaikan
            'content' => 'peta_leaflet' 
        ); 
        $this->load->view('layout/viewunion', $data, FALSE); 
    } 
} 
/* End of file Home.php */