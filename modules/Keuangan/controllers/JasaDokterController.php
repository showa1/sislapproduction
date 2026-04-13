<?php

namespace app\modules\keuangan\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use DateTime;
use DateInterval;
use yii\helpers\ArrayHelper;
use app\models\PegawaiM;
use app\models\RuanganM;
use app\models\PenjaminpasienM;
use app\models\KategoritindakanM;

class JasaDokterController extends BaseController
{    

    public $dateFrom, $dateTo, $totalCount;

    public $params = [];

    public $statuscari;

    public $dokter_id, $ruangan_id, $penjamin_id, $kategoritindakan_id, $keyword, $status_verif;

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {   
        $this->setupSearch();

        $dropdownselect = [
            'start' =>  Yii::$app->request->get('date_from'),
            'to' =>  Yii::$app->request->get('date_to'),
            'dokter_id' => $this->dokter_id,
            'ruangan_id' => $this->ruangan_id,
            'penjamin_id' => $this->penjamin_id,
            'kategoritindakan_id' => $this->kategoritindakan_id,
            'keyword' => $this->keyword,
            'status_verif' => $this->status_verif,
        ];

        return $this->render('index', [
            'dataProvider' => $this->dataprovider(),
            'dropdownselect' => $dropdownselect,
            'stats' => $this->getSummaryStats(),
            'dataDokter' => ArrayHelper::map(Yii::$app->db->createCommand("
                SELECT DISTINCT pgm.pegawai_id, pgm.nama_pegawai 
                FROM tindakanpelayanan_t tp 
                JOIN pegawai_m pgm ON pgm.pegawai_id = tp.dokterpemeriksa1_id 
                ORDER BY pgm.nama_pegawai
            ")->queryAll(), 'pegawai_id', 'nama_pegawai'),
            'dataRuangan' => ArrayHelper::map(RuanganM::find()->where(['ruangan_aktif' => true])->orderBy('ruangan_nama')->all(), 'ruangan_id', 'ruangan_nama'),
            'dataPenjamin' => ArrayHelper::map(PenjaminpasienM::find()->where(['penjamin_aktif' => true])->orderBy('penjamin_nama')->all(), 'penjamin_id', 'penjamin_nama'),
            'dataKategori' => ArrayHelper::map(KategoritindakanM::find()->where(['kategoritindakan_aktif' => true])->orderBy('kategoritindakan_nama')->all(), 'kategoritindakan_id', 'kategoritindakan_nama'),
            'statuscari' => $this->statuscari,
        ]);

    }

    public function setupSearch()
    {
        $this->dateFrom = Yii::$app->request->get('date_from');
        $this->dateTo = Yii::$app->request->get('date_to');
        if (!empty($this->dateFrom)) {
            $date = DateTime::createFromFormat('d-m-Y', $this->dateFrom);
            if ($date) {
                $this->dateFrom = $date->format('Y-m-d');
            }
        }
        
        if (!empty($this->dateTo)) {
            $date = DateTime::createFromFormat('d-m-Y', $this->dateTo);
            if ($date) {
                $this->dateTo = $date->format('Y-m-d');
            }
        }

        $this->dokter_id = Yii::$app->request->get('dokter_id');
        $this->ruangan_id = Yii::$app->request->get('ruangan_id');
        $this->penjamin_id = Yii::$app->request->get('penjamin_id');
        $this->kategoritindakan_id = Yii::$app->request->get('kategoritindakan_id');
        $this->keyword = Yii::$app->request->get('keyword');
        $this->status_verif = Yii::$app->request->get('status_verif');

        $cari = Yii::$app->request->get('cari');
        
        $this->statuscari = !empty($cari) ? true : false;
    }

    public function dataprovider()
    {
        return new SqlDataProvider([
            'sql' => $this->statuscari ? $this->baseQuery() : $this->queryKosong(),
            'params' => $this->params,
            'totalCount' => $this->statuscari ? $this->countQuery() : 0,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        // $command = Yii::$app->db->createCommand($a->sql, $a->params);
        // echo "SQL Query: " . $command->getRawSql(); die;
    }

    public function actionExport()
    {
        $this->setupSearch();
        $dataProvider = new SqlDataProvider([
            'sql' => $this->statuscari ? $this->baseQuery() : $this->queryKosong(),
            'params' => $this->params,
            'pagination' => false,
        ]);
        
        $models = $dataProvider->getModels();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Jasa Dokter');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Tanggal Pendaftaran');
        $sheet->setCellValue('C4', 'No Pendaftaran');
        $sheet->setCellValue('D4', 'No Rekam Medik');
        $sheet->setCellValue('E4', 'Nama Pasien');
        $sheet->setCellValue('F4', 'Ruangan');
        $sheet->setCellValue('G4', 'No Pembayaran');
        $sheet->setCellValue('H4', 'Tanggal Pembayaran');
        $sheet->setCellValue('I4', 'Dokter');
        $sheet->setCellValue('J4', 'Kode Tindakan');
        $sheet->setCellValue('K4', 'Nama Tindakan');
        $sheet->setCellValue('L4', 'Nama Penjamin');
        $sheet->setCellValue('M4', 'Jumlah Komponen');
        $sheet->setCellValue('N4', 'List Komponen Tarif');
        $sheet->setCellValue('O4', 'Uang Per Komponen');
        $sheet->setCellValue('P4', 'Total Uang Tindakan');

        $i = 1;

        $data = [];
        foreach ($models as $model) {
            $data[] = [
                $i,
                $model['tgl_pendaftaran'] ?? '',
                $model['no_pendaftaran'] ?? '',
                $model['no_rekam_medik'] ?? '',
                $model['nama_pasien'] ?? '',
                $model['ruangan_nama'] ?? '',
                $model['nopembayaran'] ?? '',
                $model['tglpembayaran'] ?? '',
                $model['nama_pegawai'] ?? '',
                $model['daftartindakan_kode'] ?? '',
                $model['daftartindakan_nama'] ?? '',
                $model['penjamin_nama'] ?? '',
                $model['jumlah_komponen'] ?? '',
                $model['list_komponentarif'] ?? '',
                $model['uang_per_komponen'] ?? '',
                $model['total_uang_tindakan'] ?? ''
                
            ];
            $i++;
        }

        $startRow = 5;
        $sheet->fromArray($data, null, 'A' . $startRow);

        $lastRow = $startRow + count($data) - 1;

        $sheet->getStyle('A4:P' . $lastRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'laporan-jasa-dokter.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return Yii::$app->response->sendFile($tempFile, $fileName)->on(
            \yii\web\Response::EVENT_AFTER_SEND,
            function ($event) {
                unlink($event->data); // Hapus file sementara setelah diunduh
            },
            $tempFile
        );

    }

    public function queryKosong()
    {
        return "Select tariftindakan_id from tariftindakan_m where 1=0";
    }

    public function baseQuery()
    {
        $query = "                
            WITH komponen AS (
                SELECT
                    tk.tindakanpelayanan_id,
                    tk.komponentarif_id,
                    km.komponentarif_nama,
                    SUM(tk.tarif_tindakankomp) AS total_uang_komponen
                FROM tindakankomponen_t tk
                JOIN komponentarif_m km
                    ON km.komponentarif_id = tk.komponentarif_id
                WHERE tk.komponentarif_id in ('21','76','80','5')
                GROUP BY
                    tk.tindakanpelayanan_id,
                    tk.komponentarif_id,
                    km.komponentarif_nama
            ),
            rekap_komponen AS (
                SELECT
                    tindakanpelayanan_id,
                    COUNT(*) AS jumlah_komponen,

                    array_agg(komponentarif_id ORDER BY komponentarif_id)
                        AS list_komponentarif,

                    string_agg(
                        komponentarif_nama || ' (' || komponentarif_id || '): ' || total_uang_komponen,
                        ' | '
                        ORDER BY komponentarif_id
                    ) AS uang_per_komponen,

                    SUM(total_uang_komponen) AS total_uang_tindakan
                FROM komponen
                GROUP BY tindakanpelayanan_id
            )
            SELECT
                pd.tgl_pendaftaran,
                pd.no_pendaftaran,
                p.no_rekam_medik,
                p.nama_pasien,
                rm.ruangan_nama,
                pp.nopembayaran,
                pp.tglpembayaran,
                pgm.nama_pegawai,
                dt.daftartindakan_kode,
                dt.daftartindakan_nama,
                pm.penjamin_nama,
                pd.instalasi_id,                
                pp.pembayaranpelayanan_id,
                pp.tglpembayaran,
                rk.jumlah_komponen,
                rk.list_komponentarif,
                rk.uang_per_komponen,
                rk.total_uang_tindakan

            FROM tindakanpelayanan_t tp
            JOIN pendaftaran_t pd
                ON pd.pendaftaran_id = tp.pendaftaran_id
            JOIN pasien_m p
                ON p.pasien_id = tp.pasien_id
            JOIN daftartindakan_m dt
                ON dt.daftartindakan_id = tp.daftartindakan_id
            JOIN pembayaranpelayanan_t pp
                ON pp.pendaftaran_id = pd.pendaftaran_id
            JOIN rekap_komponen rk
                ON rk.tindakanpelayanan_id = tp.tindakanpelayanan_id
            join ruangan_m rm on rm.ruangan_id=tp.ruangan_id
            join penjaminpasien_m pm on pm.penjamin_id=pd.penjamin_id
            join pegawai_m pgm on pgm.pegawai_id=tp.dokterpemeriksa1_id 
        ";

        $query =  $this->queryFilter($query);

        return $query;
    }

    public function queryFilter($query)
    {
        $query .= " WHERE date(pp.tglpembayaran) between :datefrom AND :dateto ";
        $this->params = array_merge($this->params, [':datefrom' => $this->dateFrom, ':dateto' => $this->dateTo]);

        if (!empty($this->dokter_id)) {
            $query .= " AND tp.dokterpemeriksa1_id = :dokter_id ";
            $this->params[':dokter_id'] = $this->dokter_id;
        }
        if (!empty($this->ruangan_id)) {
            $query .= " AND tp.ruangan_id = :ruangan_id ";
            $this->params[':ruangan_id'] = $this->ruangan_id;
        }
        if (!empty($this->penjamin_id)) {
            $query .= " AND pd.penjamin_id = :penjamin_id ";
            $this->params[':penjamin_id'] = $this->penjamin_id;
        }
        if (!empty($this->kategoritindakan_id)) {
            $query .= " AND dt.kategoritindakan_id = :kategoritindakan_id ";
            $this->params[':kategoritindakan_id'] = $this->kategoritindakan_id;
        }
        if (!empty($this->keyword)) {
            $query .= " AND (p.nama_pasien ILIKE :keyword OR p.no_rekam_medik ILIKE :keyword) ";
            $this->params[':keyword'] = "%{$this->keyword}%";
        }
        if ($this->status_verif === '1') {
            $query .= " AND tp.verifikasitagihan_id IS NOT NULL ";
        } elseif ($this->status_verif === '0') {
            $query .= " AND tp.verifikasitagihan_id IS NULL ";
        }

        return $query;
    }

    public function getSummaryStats()
    {
        if (!$this->statuscari) {
            return [
                'total_jasa' => 0,
                'total_dokter' => 0,
                'tindakan_terbanyak' => '-'
            ];
        }

        $cte = "
            WITH komponen AS (
                SELECT
                    tk.tindakanpelayanan_id,
                    tk.komponentarif_id,
                    km.komponentarif_nama,
                    SUM(tk.tarif_tindakankomp) AS total_uang_komponen
                FROM tindakankomponen_t tk
                JOIN komponentarif_m km
                    ON km.komponentarif_id = tk.komponentarif_id
                WHERE tk.komponentarif_id in ('21','76','80','5')
                GROUP BY
                    tk.tindakanpelayanan_id,
                    tk.komponentarif_id,
                    km.komponentarif_nama
            ),
            rekap_komponen AS (
                SELECT
                    tindakanpelayanan_id,
                    SUM(total_uang_komponen) AS total_uang_tindakan
                FROM komponen
                GROUP BY tindakanpelayanan_id
            )
        ";

        $sqlStats = $cte . "
            SELECT 
                SUM(rk.total_uang_tindakan) as total_jasa,
                COUNT(DISTINCT tp.dokterpemeriksa1_id) as total_dokter
            FROM tindakanpelayanan_t tp
            JOIN pendaftaran_t pd ON pd.pendaftaran_id = tp.pendaftaran_id
            JOIN pasien_m p ON p.pasien_id = tp.pasien_id
            JOIN daftartindakan_m dt ON dt.daftartindakan_id = tp.daftartindakan_id
            JOIN pembayaranpelayanan_t pp ON pp.pendaftaran_id = pd.pendaftaran_id
            JOIN rekap_komponen rk ON rk.tindakanpelayanan_id = tp.tindakanpelayanan_id
        ";

        $sqlStats = $this->queryFilter($sqlStats);
        $stats = Yii::$app->db->createCommand($sqlStats, $this->params)->queryOne();

        $sqlTindakan = "
            SELECT dt.daftartindakan_nama, COUNT(*) as jumlah
            FROM tindakanpelayanan_t tp
            JOIN pendaftaran_t pd ON pd.pendaftaran_id = tp.pendaftaran_id
            JOIN pasien_m p ON p.pasien_id = tp.pasien_id
            JOIN daftartindakan_m dt ON dt.daftartindakan_id = tp.daftartindakan_id
            JOIN pembayaranpelayanan_t pp ON pp.pendaftaran_id = pd.pendaftaran_id
        ";
        $sqlTindakan = $this->queryFilter($sqlTindakan);
        $sqlTindakan .= " GROUP BY dt.daftartindakan_nama ORDER BY jumlah DESC LIMIT 1 ";
        $topTindakan = Yii::$app->db->createCommand($sqlTindakan, $this->params)->queryOne();

        return [
            'total_jasa' => $stats['total_jasa'] ?? 0,
            'total_dokter' => $stats['total_dokter'] ?? 0,
            'tindakan_terbanyak' => $topTindakan['daftartindakan_nama'] ?? '-'
        ];
    }

    public function countQuery()
    {
        $query = " 
                    WITH komponen AS (
                        SELECT
                            tk.tindakanpelayanan_id,
                            tk.komponentarif_id,
                            km.komponentarif_nama,
                            SUM(tk.tarif_tindakankomp) AS total_uang_komponen
                        FROM tindakankomponen_t tk
                        JOIN komponentarif_m km
                            ON km.komponentarif_id = tk.komponentarif_id
                        WHERE tk.komponentarif_id in ('21','76','80','5')
                        GROUP BY
                            tk.tindakanpelayanan_id,
                            tk.komponentarif_id,
                            km.komponentarif_nama
                    ),
                    rekap_komponen AS (
                        SELECT
                            tindakanpelayanan_id,
                            COUNT(*) AS jumlah_komponen,

                            array_agg(komponentarif_id ORDER BY komponentarif_id)
                                AS list_komponentarif,

                            string_agg(
                                komponentarif_nama || ' (' || komponentarif_id || '): ' || total_uang_komponen,
                                ' | '
                                ORDER BY komponentarif_id
                            ) AS uang_per_komponen,

                            SUM(total_uang_komponen) AS total_uang_tindakan
                        FROM komponen
                        GROUP BY tindakanpelayanan_id
                    )
                    select count(1)
                        FROM tindakanpelayanan_t tp
                    JOIN pendaftaran_t pd
                        ON pd.pendaftaran_id = tp.pendaftaran_id
                    JOIN pasien_m p
                        ON p.pasien_id = tp.pasien_id
                    JOIN daftartindakan_m dt
                        ON dt.daftartindakan_id = tp.daftartindakan_id
                    JOIN pembayaranpelayanan_t pp
                        ON pp.pendaftaran_id = pd.pendaftaran_id
                    JOIN rekap_komponen rk
                        ON rk.tindakanpelayanan_id = tp.tindakanpelayanan_id
                    join ruangan_m rm on rm.ruangan_id=tp.ruangan_id
                    join penjaminpasien_m pm on pm.penjamin_id=pd.penjamin_id
                    join pegawai_m pgm on pgm.pegawai_id=tp.dokterpemeriksa1_id 
            ";

        $query = $this->queryFilter($query);
        
        return Yii::$app->db->createCommand($query, $this->params)->queryScalar();
    }

}
