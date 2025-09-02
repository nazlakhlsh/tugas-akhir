<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Loader $load
 * @property CI_Input $input
 * @property CI_Output $output
 * @property Kuliner_model $kuliner
 */
class Api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->model('Kuliner_model', 'kuliner');

        // Set header untuk JSON response dan CORS
        $this->output->set_content_type('application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    }

    /**
     * Endpoint untuk mengambil SEMUA data kuliner yang sudah terverifikasi.
     * Digunakan untuk menampilkan semua pin di peta saat pertama kali dibuka.
     */
    public function get_all_verified()
    {
        try {
            $list_kuliner = $this->kuliner->get_all_for_map();

            // Format ulang path foto agar menjadi URL lengkap
            foreach ($list_kuliner as $kuliner) {
                // Proses foto_utama
                if (!empty($kuliner->foto_utama)) {
                    $kuliner->foto_utama = base_url('uploads/kuliner/' . $kuliner->foto_utama);
                }
                
                // REVISI: Tambahkan proses untuk foto_menu
                if (!empty($kuliner->foto_menu)) {
                    $kuliner->foto_menu = base_url('uploads/kuliner/' . $kuliner->foto_menu);
                }
            }

            $response = [
                'success' => true,
                'message' => 'Data kuliner terverifikasi berhasil diambil.',
                'total'   => count($list_kuliner),
                'data'    => $list_kuliner,
            ];
        } catch (Exception $e) {
            $response = $this->_build_error_response($e->getMessage());
        }

        $this->output->set_output(json_encode($response, JSON_PRETTY_PRINT));
    }

    /**
     * Endpoint untuk mencari kuliner terverifikasi berdasarkan keyword.
     */
    public function search()
    {
        try {
            $keyword = $this->input->get('keyword');
            if (empty($keyword)) {
                throw new Exception("Parameter 'keyword' diperlukan.");
            }

            $list_kuliner = $this->kuliner->search_verified_kuliner($keyword);

            // Format ulang path foto
            foreach ($list_kuliner as $kuliner) {
                if (!empty($kuliner->foto_utama)) {
                    $kuliner->foto_utama = base_url('uploads/kuliner/' . $kuliner->foto_utama);
                }

                // REVISI: Tambahkan proses untuk foto_menu
                if (!empty($kuliner->foto_menu)) {
                    $kuliner->foto_menu = base_url('uploads/kuliner/' . $kuliner->foto_menu);
                }
            }

            $response = [
                'success' => true,
                'message' => 'Hasil pencarian untuk "' . html_escape($keyword) . '".',
                'total'   => count($list_kuliner),
                'data'    => $list_kuliner,
            ];
        } catch (Exception $e) {
            $response = $this->_build_error_response($e->getMessage());
        }

        $this->output->set_output(json_encode($response, JSON_PRETTY_PRINT));
    }

    /**
     * Endpoint untuk mengambil detail satu kuliner terverifikasi.
     * CATATAN: Fungsi ini tampaknya tidak dipanggil oleh halaman peta utama Anda,
     * namun tetap saya perbaiki untuk konsistensi.
     */
    public function detail($id = null)
    {
        try {
            if (!$id || !is_numeric($id)) {
                throw new Exception('ID kuliner tidak valid.');
            }

            $kuliner = $this->kuliner->get_kuliner_by_id($id); // Menggunakan get_kuliner_by_id agar semua data terambil

            if (!$kuliner || $kuliner->status_verifikasi !== 'terverifikasi') {
                $this->output->set_status_header(404); // Not Found
                throw new Exception('Data kuliner tidak ditemukan atau belum diverifikasi.');
            }

            // Format ulang path foto
            if (!empty($kuliner->foto_utama)) {
                $kuliner->foto_utama = base_url('uploads/kuliner/' . $kuliner->foto_utama);
            }
            
            // REVISI: Tambahkan proses untuk foto_menu
            if (!empty($kuliner->foto_menu)) {
                $kuliner->foto_menu = base_url('uploads/kuliner/' . $kuliner->foto_menu);
            }

            $response = [
                'success' => true,
                'message' => 'Detail data berhasil diambil.',
                'data'    => $kuliner
            ];
        } catch (Exception $e) {
            $response = $this->_build_error_response($e->getMessage());
        }

        $this->output->set_output(json_encode($response, JSON_PRETTY_PRINT));
    }

    /**
     * Endpoint untuk mengambil daftar Kategori.
     */
    public function get_categories()
    {
        // Fungsi ini tidak perlu diubah
        try {
            $categories = $this->kuliner->get_active_categories();
            $response = [
                'success' => true,
                'message' => 'Daftar kategori berhasil diambil.',
                'data'    => $categories
            ];
        } catch (Exception $e) {
            $response = $this->_build_error_response($e->getMessage());
        }
        $this->output->set_output(json_encode($response, JSON_PRETTY_PRINT));
    }

    /**
     * Endpoint untuk mengambil daftar Kecamatan.
     */
    public function get_districts()
    {
        // Fungsi ini tidak perlu diubah
        try {
            $districts = $this->kuliner->get_active_districts();
            $response = [
                'success' => true,
                'message' => 'Daftar kecamatan berhasil diambil.',
                'data'    => $districts
            ];
        } catch (Exception $e) {
            $response = $this->_build_error_response($e->getMessage());
        }
        $this->output->set_output(json_encode($response, JSON_PRETTY_PRINT));
    }

    /**
     * Endpoint untuk menemukan kuliner terverifikasi terdekat.
     */
    public function find_nearby()
    {
        try {
            $lat = $this->input->get('lat');
            $lon = $this->input->get('lon');

            if ($lat === null || $lon === null || !is_numeric($lat) || !is_numeric($lon)) {
                throw new Exception('Latitude dan Longitude diperlukan.');
            }

            $list_kuliner = $this->kuliner->get_verified_nearby_kuliner((float)$lat, (float)$lon);

            // Format ulang path foto
            foreach ($list_kuliner as $kuliner) {
                if (!empty($kuliner->foto_utama)) {
                    $kuliner->foto_utama = base_url('uploads/kuliner/' . $kuliner->foto_utama);
                }

                // REVISI: Tambahkan proses untuk foto_menu
                if (!empty($kuliner->foto_menu)) {
                    $kuliner->foto_menu = base_url('uploads/kuliner/' . $kuliner->foto_menu);
                }
            }

            $message = count($list_kuliner) > 0
                ? 'Data kuliner terdekat berhasil ditemukan.'
                : 'Tidak ada kuliner yang ditemukan dalam radius 10 km dari lokasi Anda.';

            $response = [
                'success' => true,
                'message' => $message,
                'total'   => count($list_kuliner),
                'data'    => $list_kuliner,
            ];
        } catch (Exception $e) {
            $response = $this->_build_error_response($e->getMessage());
        }

        $this->output->set_output(json_encode($response, JSON_PRETTY_PRINT));
    }

    /**
     * Helper function untuk membuat response error standar.
     */
    private function _build_error_response($message)
    {
        return [
            'success' => false,
            'message' => $message,
            'data'    => null
        ];
    }
}