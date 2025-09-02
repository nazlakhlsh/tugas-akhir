<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model
{
    // REVISI: Nama tabel disesuaikan menjadi 'tbl_users'
    protected $table = 'tbl_users';

    /**
     * Menyimpan user baru.
     * @param array $data
     * @return int|bool
     */
    public function register_user($data)
    {
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Mengambil data user berdasarkan email.
     * @param string $email
     * @return object|null
     */
    public function get_user_by_email($email)
    {
        return $this->db->get_where($this->table, ['email' => $email])->row();
    }

    /**
     * Mengambil data user berdasarkan ID.
     * Fungsi ini dipanggil di banyak controller.
     * @param int $id
     * @return object|null
     */
    public function get_user_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    /**
     * Mengambil semua user. Dipakai di halaman manajemen user admin.
     * @return array
     */
    public function get_all_users()
    {
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get($this->table)->result();
    }

    /**
     * Mengupdate data user.
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update_user($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Menghapus user. Dipakai oleh Admin.
     * @param int $id
     * @return bool
     */
    public function delete_user($id)
    {
        return $this->db->delete($this->table, ['id' => $id]);
    }

    /**
     * Menghitung semua user. Dipakai di dashboard admin untuk statistik.
     * @return int
     */
    public function count_all_users()
    {
        return $this->db->count_all($this->table);
    }

    /**
     * REVISI: Validasi login disederhanakan.
     * Hanya mengambil data user berdasarkan email. Pengecekan password ada di controller.
     * @param string $email
     * @return object|null
     */
    public function validate($email)
    {
        // Fungsi ini cukup dengan mengambil data user berdasarkan email
        // karena pengecekan status sudah tidak relevan dengan struktur DB.
        $user = $this->db->get_where($this->table, ['email' => $email])->row();

        return $user;
    }

    /**
     * Mengupdate waktu login terakhir.
     * @param int $id
     * @return bool
     */
    public function update_last_login($id)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, ['last_login' => date('Y-m-d H:i:s')]);
    }
}
