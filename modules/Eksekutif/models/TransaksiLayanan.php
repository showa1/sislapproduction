<?php

namespace app\modules\eksekutif\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model ActiveRecord untuk tabel transaksi_layanan (SQLite).
 *
 * @property int $id
 * @property string $tanggal
 * @property string $unit_bisnis
 * @property int $jumlah_pasien
 * @property string $jenis_pasien
 * @property string $cara_bayar
 * @property float $pendapatan
 * @property string $status_pemeriksaan
 * @property string $hari
 * @property string $jam
 */
class TransaksiLayanan extends ActiveRecord
{
    /**
     * Menggunakan db_dashboard sebagai koneksi database.
     */
    public static function getDb()
    {
        return Yii::$app->db_dashboard;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transaksi_layanan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tanggal', 'unit_bisnis', 'jenis_pasien', 'cara_bayar'], 'required'],
            [['jumlah_pasien'], 'integer'],
            [['pendapatan'], 'number'],
            [['tanggal'], 'safe'],
            [['unit_bisnis', 'cara_bayar'], 'string', 'max' => 50],
            [['jenis_pasien', 'status_pemeriksaan', 'hari'], 'string', 'max' => 20],
            [['jam'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tanggal' => 'Tanggal',
            'unit_bisnis' => 'Unit Bisnis',
            'jumlah_pasien' => 'Jumlah Pasien',
            'jenis_pasien' => 'Jenis Pasien',
            'cara_bayar' => 'Cara Bayar',
            'pendapatan' => 'Pendapatan',
            'status_pemeriksaan' => 'Status Pemeriksaan',
            'hari' => 'Hari',
            'jam' => 'Jam',
        ];
    }
}
