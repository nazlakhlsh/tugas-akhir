<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kuliner_model extends CI_Model
{
    // REVISI: Nama tabel disesuaikan dengan skema database final
    protected $table = 'tbl_kuliner';
    protected $users_table = 'tbl_users';
    protected $sertifikat_table = 'tbl_sertifikat';
    protected $kategori_table = 'tbl_kategori';
    protected $kecamatan_table = 'tbl_kecamatan';
    protected $harga_table = 'tbl_rentang_harga';

    public function __construct()
    {
        parent::__construct();
    }

    /*
    |--------------------------------------------------------------------------
    | FUNGSI-FUNGSI UNTUK ADMIN & UMKM (INTERNAL)
    |--------------------------------------------------------------------------
    */

    /**
     * REVISI: Mengambil data kuliner lengkap dengan JOIN ke semua tabel terkait.
     * Digunakan untuk halaman detail & edit.
     * @param int $id_kuliner
     * @return object|null
     */
    public function get_kuliner_by_id($id_kuliner)
    {
        $this->db->select("
            {$this->table}.*, 
            {$this->users_table}.nama_lengkap as nama_pemilik,
            {$this->kategori_table}.nama_kategori,
            {$this->kecamatan_table}.nama_kecamatan,
            {$this->harga_table}.rentang_harga_teks,
            {$this->sertifikat_table}.status_sertifikat,
            {$this->sertifikat_table}.nomor_sertifikat,
            {$this->sertifikat_table}.tanggal_berlaku
        ");
        $this->db->from($this->table);
        $this->db->join($this->users_table, "{$this->users_table}.id = {$this->table}.id_user", 'left');
        $this->db->join($this->kategori_table, "{$this->kategori_table}.id = {$this->table}.id_kategori", 'left');
        $this->db->join($this->kecamatan_table, "{$this->kecamatan_table}.id = {$this->table}.id_kecamatan", 'left');
        $this->db->join($this->harga_table, "{$this->harga_table}.id = {$this->table}.id_rentang_harga", 'left');
        $this->db->join($this->sertifikat_table, "{$this->sertifikat_table}.id_kuliner = {$this->table}.id", 'left');
        $this->db->where("{$this->table}.id", $id_kuliner);

        return $this->db->get()->row();
    }

    /**
     * REVISI: Mengambil satu data kuliner untuk memastikan kepemilikan.
     * Digunakan untuk halaman edit/hapus oleh UMKM.
     * @param int $id
     * @param int $id_user
     * @return object|null
     */
    public function get_kuliner_by_id_and_user($id, $id_user)
    {
        // Memanggil fungsi yang sudah ada agar tidak duplikat kode
        $data = $this->get_kuliner_by_id($id);

        // Pastikan data ada dan dimiliki oleh user yang benar
        if ($data && $data->id_user == $id_user) {
            return $data;
        }
        return null;
    }

    /**
     * REVISI: Mengambil daftar kuliner dengan JOIN minimal untuk list.
     * @param string $status
     * @param bool $join_user
     * @return array
     */
    public function get_kuliner_by_status($status = 'all', $join_user = false)
    {
        $this->db->select("{$this->table}.*, {$this->kategori_table}.nama_kategori");
        if ($join_user) {
            $this->db->select("{$this->users_table}.nama_lengkap as nama_pemilik");
            $this->db->join($this->users_table, "{$this->users_table}.id = {$this->table}.id_user", 'left');
        }
        $this->db->join($this->kategori_table, "{$this->kategori_table}.id = {$this->table}.id_kategori", 'left');

        if ($status !== 'all' && in_array($status, ['pending', 'terverifikasi', 'ditolak'])) {
            $this->db->where('status_verifikasi', $status);
        }
        $this->db->order_by("{$this->table}.created_at", 'DESC');
        return $this->db->get($this->table)->result();
    }

    /**
     * REVISI: Mengambil semua kuliner milik user tertentu dengan JOIN minimal.
     * @param int $id_user
     * @return array
     */
    public function get_kuliner_by_user($id_user)
    {
        $this->db->select("{$this->table}.*, {$this->kategori_table}.nama_kategori");
        $this->db->join($this->kategori_table, "{$this->kategori_table}.id = {$this->table}.id_kategori", 'left');
        $this->db->where('id_user', $id_user);
        $this->db->order_by("{$this->table}.updated_at", 'DESC');
        return $this->db->get($this->table)->result();
    }

    public function count_kuliner_by_status($status = null)
    {
        if ($status !== null && in_array($status, ['pending', 'terverifikasi', 'ditolak'])) {
            $this->db->where('status_verifikasi', $status);
        }
        return $this->db->count_all_results($this->table);
    }

    /**
     * REVISI TOTAL: Menggunakan transaction untuk menyimpan data ke 2 tabel.
     * @param array $data Data gabungan dari form
     * @return bool
     */
    public function insert_kuliner($data)
    {
        // Pisahkan data untuk tabel kuliner dan tabel sertifikat
        $kuliner_data = [
            'id_user'          => $data['id_user'],
            'id_kategori'      => $data['id_kategori'],
            'id_kecamatan'     => $data['id_kecamatan'],
            'id_rentang_harga' => $data['id_rentang_harga'],
            'nama_kuliner'     => $data['nama_kuliner'],
            'alamat'           => $data['alamat'],
            'jam_buka'         => $data['jam_buka'],
            'latitude'         => $data['latitude'],
            'longitude'        => $data['longitude'],
            'link_gmaps'       => $data['link_gmaps'],
            'foto_utama'       => $data['foto_utama'],
            'foto_menu'        => $data['foto_menu'], // <-- TAMBAHKAN BARIS INI
        ];

        $sertifikat_data = [
            'status_sertifikat' => $data['status_sertifikat'],
            'nomor_sertifikat'  => $data['nomor_sertifikat'],
            'tanggal_berlaku'   => $data['tanggal_berlaku'],
        ];

        // Mulai transaction
        $this->db->trans_start();

        // 1. Insert ke tabel kuliner
        $this->db->insert($this->table, $kuliner_data);
        $id_kuliner = $this->db->insert_id();

        // 2. Jika ada data sertifikat, insert ke tabel sertifikat
        if (!empty($sertifikat_data['nomor_sertifikat'])) {
            $sertifikat_data['id_kuliner'] = $id_kuliner;
            $this->db->insert($this->sertifikat_table, $sertifikat_data);
        }

        // Selesaikan transaction
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * REVISI TOTAL: Menggunakan transaction untuk update data di 2 tabel.
     * @param int $id_kuliner
     * @param array $data Data gabungan dari form
     * @return bool
     */
    public function update_kuliner($id_kuliner, $data)
    {
        // Pisahkan data
        $kuliner_data = [
            'id_kategori'       => $data['id_kategori'],
            'id_kecamatan'      => $data['id_kecamatan'],
            'id_rentang_harga'  => $data['id_rentang_harga'],
            'nama_kuliner'      => $data['nama_kuliner'],
            'alamat'            => $data['alamat'],
            'jam_buka'          => $data['jam_buka'],
            'latitude'          => $data['latitude'],
            'longitude'         => $data['longitude'],
            'link_gmaps'        => $data['link_gmaps'],
            'status_verifikasi' => 'pending', // Wajib verifikasi ulang
        ];

        // Jika ada foto baru, tambahkan ke data
        if (isset($data['foto_utama'])) {
            $kuliner_data['foto_utama'] = $data['foto_utama'];
        }

        // <-- TAMBAHKAN BLOK KODE INI -->
        // Jika ada foto menu baru, tambahkan ke data
        if (isset($data['foto_menu'])) {
            $kuliner_data['foto_menu'] = $data['foto_menu'];
        }
        // <-- BATAS PENAMBAHAN -->

        $sertifikat_data = [
            'status_sertifikat' => $data['status_sertifikat'],
            'nomor_sertifikat'  => $data['nomor_sertifikat'],
            'tanggal_berlaku'   => $data['tanggal_berlaku'],
        ];

        $this->db->trans_start();

        // 1. Update tabel kuliner
        $this->db->where('id', $id_kuliner);
        $this->db->update($this->table, $kuliner_data);

        // 2. Update atau Insert tabel sertifikat
        $this->db->where('id_kuliner', $id_kuliner);
        $sertifikat_exist = $this->db->get($this->sertifikat_table)->row();

        if ($sertifikat_exist) {
            $this->db->where('id_kuliner', $id_kuliner);
            $this->db->update($this->sertifikat_table, $sertifikat_data);
        } elseif (!empty($sertifikat_data['nomor_sertifikat'])) {
            $sertifikat_data['id_kuliner'] = $id_kuliner;
            $this->db->insert($this->sertifikat_table, $sertifikat_data);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function delete_kuliner($id)
    {
        // ON DELETE CASCADE di database akan otomatis menghapus data di tbl_sertifikat juga
        return $this->db->delete($this->table, ['id' => $id]);
    }

    // Tambahkan fungsi ini di dalam class Kuliner_model

    /**
     * Mengupdate status verifikasi saja.
     * @param int $id_kuliner
     * @param string $status ('terverifikasi', 'ditolak')
     * @return bool
     */
    public function update_status_kuliner($id_kuliner, $status)
    {
        $this->db->where('id', $id_kuliner);
        return $this->db->update($this->table, ['status_verifikasi' => $status]);
    }


    /*
    |--------------------------------------------------------------------------
    | FUNGSI UNTUK PUBLIK (API, PETA, DLL)
    |--------------------------------------------------------------------------
    */

    /**
     * REVISI: Mengambil semua kuliner terverifikasi dengan data lengkap untuk peta.
     * @return array
     */
    public function get_all_for_map()
    {
        $this->db->select("
        {$this->table}.*, 
        {$this->kategori_table}.nama_kategori,
        {$this->kecamatan_table}.nama_kecamatan,
        {$this->harga_table}.rentang_harga_teks,
        {$this->sertifikat_table}.status_sertifikat,
        {$this->sertifikat_table}.nomor_sertifikat,
        {$this->sertifikat_table}.tanggal_berlaku
    ");
        $this->db->from($this->table);
        $this->db->join($this->kategori_table, "{$this->kategori_table}.id = {$this->table}.id_kategori", 'left');
        $this->db->join($this->kecamatan_table, "{$this->kecamatan_table}.id = {$this->table}.id_kecamatan", 'left');
        $this->db->join($this->harga_table, "{$this->harga_table}.id = {$this->table}.id_rentang_harga", 'left');
        $this->db->join($this->sertifikat_table, "{$this->sertifikat_table}.id_kuliner = {$this->table}.id", 'left');
        $this->db->where('status_verifikasi', 'terverifikasi');

        return $this->db->get()->result();
    }

    /**
     * REVISI: Pencarian diubah untuk join dengan tabel master.
     * @param string $keyword
     * @return array
     */
    public function search_verified_kuliner($keyword)
    {
        $this->db->select("{$this->table}.*, {$this->kategori_table}.nama_kategori, {$this->kecamatan_table}.nama_kecamatan");
        $this->db->from($this->table);
        $this->db->join($this->kategori_table, "{$this->kategori_table}.id = {$this->table}.id_kategori", 'left');
        $this->db->join($this->kecamatan_table, "{$this->kecamatan_table}.id = {$this->table}.id_kecamatan", 'left');
        $this->db->where('status_verifikasi', 'terverifikasi');

        $this->db->group_start();
        $this->db->like('nama_kuliner', $keyword);
        $this->db->or_like('alamat', $keyword);
        $this->db->or_like("{$this->kategori_table}.nama_kategori", $keyword); // Cari di nama kategori
        $this->db->or_like("{$this->kecamatan_table}.nama_kecamatan", $keyword); // Cari di nama kecamatan
        $this->db->group_end();

        return $this->db->get()->result();
    }

    /**
     * REVISI: Mengambil data Kategori dari tabel masternya.
     * @return array
     */
    public function get_active_categories()
    {
        return $this->db->get($this->kategori_table)->result_array();
    }

    /**
     * REVISI: Mengambil data Kecamatan dari tabel masternya.
     * @return array
     */
    public function get_active_districts()
    {
        return $this->db->get($this->kecamatan_table)->result_array();
    }

    public function get_verified_nearby_kuliner($lat, $lon, $radius = 10)
    {
        $haversine = "6371 * acos(cos(radians(" . $this->db->escape($lat) . ")) * cos(radians(latitude)) * cos(radians(longitude) - radians(" . $this->db->escape($lon) . ")) + sin(radians(" . $this->db->escape($lat) . ")) * sin(radians(latitude)))";
        $this->db->select("*, ($haversine) AS distance");
        $this->db->from($this->table);
        $this->db->where('status_verifikasi', 'terverifikasi');
        $this->db->having('distance <=', $radius);
        $this->db->order_by('distance', 'ASC');
        $this->db->limit(20);

        return $this->db->get()->result();
    }
}
