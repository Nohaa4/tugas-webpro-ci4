<?php

namespace App\Controllers;

use App\Models\M_Transaksi;

class Transaksi extends BaseController
{
    // Tampilkan halaman utama transaksi
    public function master_data_transaksi()
    {
        if (!$this->isLoggedIn()) return; {

            $model = new M_Transaksi();
            $data['pages'] = 'data_transaksi_peminjaman';
            $data['web_title'] = 'Data Transaksi Peminjaman';
            $data['data_transaksi'] = $model->getDataTransaksi(['is_delete' => '0'])->getResultArray();

            echo view('Backend/Template/header', $data);
            echo view('Backend/Template/sidebar', $data);
            echo view('Backend/Transaksi/master-data-transaksi', $data);
            echo view('Backend/Template/footer', $data);
        }
    }

    // Tampilkan form input transaksi
    public function input_data_transaksi()
    {
        if (!$this->isLoggedIn()) return; {

            echo view('Backend/Template/header');
            echo view('Backend/Template/sidebar');
            echo view('Backend/Transaksi/input-transaksi');
            echo view('Backend/Template/footer');
        }
    }

    public function simpan_data_transaksi()
    {
        if (!$this->isLoggedIn()) {
        return;
    }

        $model = new M_Transaksi();

        $id_anggota       = $this->request->getPost('id_anggota');
        $id_buku          = $this->request->getPost('id_buku');
        $tanggal_pinjam   = $this->request->getPost('tanggal_pinjam');
        $tanggal_kembali  = $this->request->getPost('tanggal_kembali');
        $status           = $this->request->getPost('status_transaksi');

    // Validasi input
    if (empty($id_anggota) || empty($id_buku)) {
        session()->setFlashdata('error', 'Semua field wajib diisi!');
        echo "<script>history.go(-1);</script>";
        return;
    }

    // Generate ID transaksi otomatis
    $hasil = $model->autoNumber()->getRowArray();
    $id    = $hasil ? 'TRX' . sprintf("%03d", (int)substr($hasil['id_transaksi'], -3) + 1) : 'TRX001';

    $data = [
        'id_transaksi'     => $id,
        'id_anggota'       => $id_anggota,
        'id_buku'          => $id_buku,
        'tanggal_pinjam'   => $tanggal_pinjam,
        'tanggal_kembali'  => $tanggal_kembali,
        'status_transaksi' => $status,
        'is_delete'        => '0',
        'created_at'       => date('Y-m-d H:i:s'),
        'updated_at'       => date('Y-m-d H:i:s')
    ];

    // Simpan ke database
    if ($model->saveDataTransaksi($data)) {
        session()->setFlashdata('success', 'Data Transaksi Berhasil Ditambahkan!');
        echo "<script>document.location = '" . base_url('transaksi/data-transaksi-peminjaman') . "';</script>";
    } else {
        session()->setFlashdata('error', 'Gagal menambahkan data transaksi!');
        echo "<script>history.go(-1);</script>";
    }
}

    // Edit transaksi
    public function edit_data_transaksi()
    {
        if (!$this->isLoggedIn()) return;

        $id = service('uri')->getSegment(3);
        $model = new M_Transaksi();
        $dataTransaksi = $model->getDataTransaksi(['sha1(id_transaksi)' => $id])->getRowArray();

        if (!$dataTransaksi) {
            session()->setFlashdata('error', 'Data tidak ditemukan!');
            echo "<script>document.location = '" . base_url('transaksi/data-transaksi-peminjaman') . "';</script>";
            return;
        }

        session()->set(['idUpdateTransaksi' => $dataTransaksi['id_transaksi']]);

        $data['data_transaksi'] = $dataTransaksi;
        $data['web_title'] = 'Edit Transaksi';

        echo view('Backend/Template/header', $data);
        echo view('Backend/Template/sidebar', $data);
        echo view('Backend/Transaksi/edit-transaksi', $data);
        echo view('Backend/Template/footer', $data);
    }

    // Update transaksi
    public function update_data_transaksi()
    {
        if (!$this->isLoggedIn()) return;

        $model = new M_Transaksi();
        $id = session()->get('idUpdateTransaksi');

        if (!$id) {
            session()->setFlashdata('error', 'Sesi tidak valid!');
            echo "<script>document.location = '" . base_url('transaksi/data-transaksi-peminjaman') . "';</script>";
            return;
        }

        $data = [
            'id_anggota' => $this->request->getPost('id_anggota'),
            'id_buku' => $this->request->getPost('id_buku'),
            'tanggal_pinjam' => $this->request->getPost('tanggal_pinjam'),
            'tanggal_kembali' => $this->request->getPost('tanggal_kembali'),
            'status_transaksi' => $this->request->getPost('status_transaksi'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $update = $model->updateDataTransaksi($data, ['id_transaksi' => $id]);
        session()->remove('idUpdateTransaksi');

        if ($update) {
            session()->setFlashdata('success', 'Data Berhasil Diperbarui!');
            echo "<script>document.location = '" . base_url('transaksi/data-transaksi-peminjaman') . "';</script>";
        } else {
            session()->setFlashdata('error', 'Gagal memperbarui data!');
            echo "<script>history.go(-1);</script>";
        }
    }

    // Hapus transaksi (soft delete)
    public function hapus_data_transaksi()
    {
        if (!$this->isLoggedIn()) return;

        $model = new M_Transaksi();
        $id = service('uri')->getSegment(3);

        $hapus = $model->updateDataTransaksi([
            'is_delete' => '1',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['sha1(id_transaksi)' => $id]);

        session()->setFlashdata($hapus ? 'success' : 'error', $hapus ? 'Berhasil Dihapus!' : 'Gagal Menghapus!');
        echo "<script>document.location = '" . base_url('transaksi/data-transaksi-peminjaman') . "';</script>";
    }

    // Cek login
    private function isLoggedIn()
    {
        if (session()->get('ses_id') == "" || session()->get('ses_user') == "" || session()->get('ses_level') == "") {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu!');
            echo "<script>document.location = '" . base_url('transaksi/login-admin') . "';</script>";
            return false;
        }
        return true;
    }
}
