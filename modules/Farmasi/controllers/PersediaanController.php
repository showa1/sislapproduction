<?php

namespace app\modules\farmasi\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use app\models\RuanganM;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use DateTime;
use DateInterval;

class PersediaanController extends BaseController
{    

    public $dateFrom, $dateTo, $totalCount, $dateMinSatu, $datePlusSatu;

    public $params = [];

    public $ruangan, $statuscari, $ruanganSelect, $namaObatAlkes;

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
            'ruangan' =>  $this->ruanganSelect,
            'nama_obatalkes' => $this->namaObatAlkes,
        ];

        return $this->render('index', [
            'dataProvider' => $this->dataprovider(),
            'listruangan' => $this->listruangan(),
            'dropdownselect' => $dropdownselect
        ]);

    }

    public function listruangan()
    {
        return [
            'Gudang Farmasi',
            'Apotek',
            'Unit Rumah Sakit',
            'Unit Lain',
        ];
    }

    public function setupRuangan()
    {
        $ruanganid = Yii::$app->request->get('ruangan');
        if(isset($ruanganid)) {
            $gudangFarmasi = 0;
            $apotek = 1;
            $unitrumahsakit = 2;
            $unitlain = 3;

            if ($ruanganid == $gudangFarmasi) {
                $this->ruangan = '58';
                $this->ruanganSelect = 0;
            }

            if ($ruanganid == $apotek) {
                $this->ruangan = '59';
                $this->ruanganSelect = 1;
            }

            if ($ruanganid == $unitlain) {
                $this->ruangan = 'unit lain';
                $this->ruanganSelect = 3;
            }

            if ($ruanganid == $unitrumahsakit) {
                $this->ruangan = 'unit rumah sakit';
                $this->ruanganSelect = 2;
            }
        }    
    }

    public function setupSearch()
    {
        $this->dateFrom = Yii::$app->request->get('date_from');
        $this->dateMinSatu = Yii::$app->request->get('date_from');
        $this->dateTo = Yii::$app->request->get('date_to');
        $this->datePlusSatu = Yii::$app->request->get('date_to');
        $this->namaObatAlkes = trim(Yii::$app->request->get('nama_obatalkes', ''));
        $this->setupRuangan();
        
        if (!empty($this->dateFrom)) {
            $this->dateFrom = DateTime::createFromFormat('d-m-Y', $this->dateFrom)->format('Y-m-d') . " 00:00:00";
            $this->dateMinSatu = DateTime::createFromFormat('d-m-Y', $this->dateMinSatu)->format('Y-m-d');
        }
        
        if (!empty($this->dateTo)) {
            $this->dateTo = DateTime::createFromFormat('d-m-Y', $this->dateTo)->format('Y-m-d') . " 23:59:59";
            $this->datePlusSatu = DateTime::createFromFormat('d-m-Y', $this->datePlusSatu)
                                ->add(new DateInterval('P1D'))
                                ->format('Y-m-d'); 
        }

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
            'pagination' => false,  // Disable pagination untuk ekspor semua data
        ]);
        
        $models = $dataProvider->getModels();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Persediaan');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
       
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Obat Alkes Aktif');
        $sheet->setCellValue('C4', 'Kode Obat Alkes');
        $sheet->setCellValue('D4', 'Jenis');
        $sheet->setCellValue('E4', 'Kategori');
        $sheet->setCellValue('F4', 'Kronis/ Non Kronis');
        $sheet->setCellValue('G4', 'Nama Obat Alkes');
        $sheet->setCellValue('H4', 'No Batch');
        $sheet->setCellValue('I4', 'Satuan Kecil');
        $sheet->setCellValue('J4', 'Satuan Terkecil');
        $sheet->setCellValue('K4', 'Kekuatan');
        $sheet->setCellValue('L4', 'Zat Aktif');
        $sheet->setCellValue('M4', 'Tanggal Kadaluarsa');
        $sheet->setCellValue('N4', 'Harga Netto');
        $sheet->setCellValue('O4', 'Discount');
        $sheet->setCellValue('P4', 'PPN Persen');
        $sheet->setCellValue('Q4', 'Harga Jual');
        $sheet->setCellValue('R4', 'Stok Awal');
        $sheet->setCellValue('S4', 'Penerimaan PBF');
        $sheet->setCellValue('T4', 'Mutasi Ruangan Masuk');
        $sheet->setCellValue('U4', 'Retur Resep');
        $sheet->setCellValue('V4', 'Adjustment Plus');
        $sheet->setCellValue('W4', 'Masuk');
        $sheet->setCellValue('X4', 'Adjustment Minus');
        $sheet->setCellValue('Y4', 'Keluar Pasien');
        $sheet->setCellValue('Z4', 'Mutasi Ruangan Keluar');
        $sheet->setCellValue('AA4', 'Pemakaian Ruangan');
        $sheet->setCellValue('AB4', 'Retur PBF');
        $sheet->setCellValue('AC4', 'Keluar');
        $sheet->setCellValue('AD4', 'Stok Sekarang');

        // Isi Data
        $row = 5; // Mulai dari baris kedua
        $i = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['obatalkes_aktif']);
            $sheet->setCellValue('C' . $row, $model['obatalkes_kode']);
            $sheet->setCellValue('D' . $row, $model['jenisobatalkes_nama']);
            $sheet->setCellValue('E' . $row, $model['obatalkes_kategori']);
            $sheet->setCellValue('F' . $row, $model['obatalkes_kronis']);
            $sheet->setCellValue('G' . $row, $model['obatalkes_nama']);
            $sheet->setCellValue('H' . $row, $model['nobatch']);
            $sheet->setCellValue('I' . $row, !empty($model['satuankecil_nama']) ? $model['satuankecil_nama'] : '');
            $sheet->setCellValue('J' . $row, !empty($model['satuanterkecilnama']) ? $model['satuanterkecilnama'] : '');
            $sheet->setCellValue('K' . $row, $model['kekuatan']);
            $sheet->setCellValue('L' . $row, $model['obatalkeszataktif_nama']);
            $sheet->setCellValue('M' . $row, $model['tglkadaluarsa']);
            $sheet->setCellValue('N' . $row, isset($model['harganetto']) ? (float) $model['harganetto'] : '');
            $sheet->setCellValue('O' . $row, isset($model['discount']) ? (float) $model['discount'] : '');
            $sheet->setCellValue('P' . $row, isset($model['ppn_persen']) ? (float) $model['ppn_persen'] : '');
            $sheet->setCellValue('Q' . $row, isset($model['hargajual']) ? (float) $model['hargajual'] : '');
            $sheet->setCellValue('R' . $row, isset($model['stok_awal']) ? (float) $model['stok_awal'] : '');
            $sheet->setCellValue('S' . $row, isset($model['penerimaan_pbf']) ? (float) $model['penerimaan_pbf'] : '');
            $sheet->setCellValue('T' . $row, isset($model['mutasi_ruangan_masuk']) ? (float) $model['mutasi_ruangan_masuk'] : '');
            $sheet->setCellValue('U' . $row, isset($model['retur_resep']) ? (float) $model['retur_resep'] : '');
            $sheet->setCellValue('V' . $row, isset($model['adjustman_plus']) ? (float) $model['adjustman_plus'] : '');
            $sheet->setCellValue('W' . $row, isset($model['masuk']) ? (float) $model['masuk'] : '');
            $sheet->setCellValue('X' . $row, isset($model['adjustman_minus']) ? (float) $model['adjustman_minus'] : '');
            $sheet->setCellValue('Y' . $row, isset($model['keluar_pasien']) ? (float) $model['keluar_pasien'] : '');
            $sheet->setCellValue('Z' . $row, isset($model['mutasi_ruangan_keluar']) ? (float) $model['mutasi_ruangan_keluar'] : '');
            $sheet->setCellValue('AA' . $row, isset($model['pemakaian_ruangan']) ? (float) $model['pemakaian_ruangan'] : '');
            $sheet->setCellValue('AB' . $row, isset($model['retur_pbf']) ? (float) $model['retur_pbf'] : '');
            $sheet->setCellValue('AC' . $row, isset($model['keluar']) ? (float) $model['keluar'] : '');
            $sheet->setCellValue('AD' . $row, isset($model['stok_sekarang']) ? (float) $model['stok_sekarang'] : '');
            // $sheet->setCellValue('J' . $row, isset($model['bpjskesehatan']) ? number_format($model['bpjskesehatan'], 2, ',', '.') : "");
            
            $row++;
            $i++;
        }

        $sheet->getStyle('M5:AD' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getStyle('A4:AD'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'laporan-persediaan.xlsx';
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

    public function cte()
    {
        $combine = '= :ruangan';
        if ($this->ruangan == 'unit lain') {
            $combine = 'in (select ruangan_id from ruangan_m where instalasi_id in(2, 3, 4, 5, 6, 7, 17, 38, 74, 76, 82)) ';
        }

        if ($this->ruangan == 'unit rumah sakit') {
            $combine = 'in (58, 59) ';
        }

        return "
                WITH stok_aggregated AS (
                SELECT 
                    obatalkes_id,
                    -- Stok Awal: Sebelum Tanggal Awal
                    SUM(CASE WHEN create_time < :dateminsatu THEN qtystok_in - qtystok_out ELSE 0 END) AS stok_awal,
                    -- Mutasi Masuk
                    SUM(CASE WHEN create_time BETWEEN :datefrom AND :dateto AND stokopnamedet_id IS NULL AND penerimaandetail_id IS NOT NULL THEN qtystok_in ELSE 0 END) AS penerimaan_pbf,
                    SUM(CASE WHEN create_time BETWEEN :datefrom AND :dateto AND terimamutasidetail_id IS NOT NULL AND tglstok_in IS NOT NULL THEN qtystok_in ELSE 0 END) AS mutasi_ruangan_masuk,
                    SUM(CASE WHEN create_time BETWEEN :datefrom AND :dateto AND stokopnamedet_id IS NOT NULL THEN qtystok_in ELSE 0 END) AS adjustman_plus,
                    SUM(CASE WHEN create_time BETWEEN :datefrom AND :dateto AND returresepdet_id IS NOT NULL AND tglstok_out IS NOT NULL THEN qtystok_out ELSE 0 END) AS retur_resep,
                    SUM(CASE WHEN create_time BETWEEN :datefrom AND :dateto THEN qtystok_in ELSE 0 END) AS masuk,
                    -- Mutasi Keluar
                    SUM(CASE WHEN create_time BETWEEN :datefrom AND :dateto AND stokopnamedet_id IS NOT NULL THEN qtystok_out ELSE 0 END) AS adjustman_minus,
                    SUM(CASE WHEN create_time BETWEEN :datefrom AND :dateto AND obatalkespasien_id IS NOT NULL THEN qtystok_out ELSE 0 END) AS keluar_pasien,
                    SUM(CASE WHEN create_time BETWEEN :datefrom AND :dateto AND terimamutasidetail_id IS NOT NULL AND tglstok_out IS NOT NULL THEN qtystok_out ELSE 0 END) AS mutasi_ruangan_keluar,
                    SUM(CASE WHEN create_time BETWEEN :datefrom AND :dateto AND returdetail_id IS NOT NULL AND tglstok_out IS NOT NULL THEN qtystok_out ELSE 0 END) AS retur_pbf,
                    SUM(CASE WHEN create_time BETWEEN :datefrom AND :dateto THEN qtystok_out ELSE 0 END) AS keluar,
                    SUM(CASE WHEN create_time BETWEEN :datefrom AND :dateto AND pemakaianobatdetail_id IS NOT NULL AND tglstok_out IS NOT NULL THEN qtystok_out ELSE 0 END) AS pemakaian_ruangan,
                    -- Stok Sekarang: Sampai Tanggal Akhir + 1 Hari
                    SUM(CASE WHEN create_time < :dateplussatu THEN qtystok_in - qtystok_out ELSE 0 END) AS stok_sekarang
                FROM stokobatalkes_t st
                WHERE st.ruangan_id ". $combine ."
                GROUP BY obatalkes_id
            ),
            zataktif_aggregated AS (
                SELECT 
                    obatalkes_id, 
                    COALESCE(STRING_AGG(obatalkeszataktif_nama, ', '), '') AS obatalkeszataktif_nama
                FROM obatalkeszataktif_m
                GROUP BY obatalkes_id
            )";
    }

    public function getWhereCondition()
    {
        $where = [];
        if (!empty($this->namaObatAlkes)) {
            $where[] = "(om.obatalkes_nama ILIKE :nama_obatalkes OR om.obatalkes_kode ILIKE :nama_obatalkes)";
        }
        
        if (!empty($where)) {
            return " WHERE " . implode(" AND ", $where);
        }
        return "";
    }

    public function baseQuery()
    {
        $whereSql = $this->getWhereCondition();

        $query =  $this->cte() . "
            SELECT 
                om.obatalkes_aktif,
                om.obatalkes_kode,
                jm.jenisobatalkes_nama,
                om.obatalkes_kategori,
                om.obatalkes_kronis,
                om.obatalkes_nama,
                om.nobatch,
                sm.satuankecil_nama,
                st.satuankecil_nama as satuanterkecilnama,
                om.kekuatan,
                za.obatalkeszataktif_nama,
                om.tglkadaluarsa,
                om.harganetto,
                om.discount,
                om.ppn_persen,
                om.hargajual,
                sa.stok_awal,
                sa.penerimaan_pbf,
                sa.mutasi_ruangan_masuk,
                sa.retur_resep,
                sa.adjustman_plus,
                sa.masuk,
                sa.adjustman_minus,
                sa.keluar_pasien,
                sa.mutasi_ruangan_keluar,
                sa.pemakaian_ruangan,
                sa.retur_pbf,
                sa.keluar,
                sa.stok_sekarang
            FROM obatalkes_m om
            INNER JOIN jenisobatalkes_m jm ON jm.jenisobatalkes_id = om.jenisobatalkes_id
            LEFT JOIN stok_aggregated sa ON sa.obatalkes_id = om.obatalkes_id
            LEFT JOIN satuankecil_m sm ON sm.satuankecil_id = om.satuankecil_id
            LEFT JOIN satuankecil_m st ON st.satuankecil_id = om.satuanterkecil_id
            LEFT JOIN zataktif_aggregated za ON za.obatalkes_id = om.obatalkes_id
            {$whereSql}
            ORDER BY om.obatalkes_kode
        ";

        return $this->queryFilter($query);

    }

    public function queryFilter($query)
    {
        $this->params = array_merge($this->params, [
            ':dateminsatu' => $this->dateMinSatu,
            ':datefrom' => $this->dateFrom,
            ':dateto' => $this->dateTo,
            ':dateplussatu' => $this->datePlusSatu,
        ]);

        if (in_array($this->ruangan, ['58', '59'])) {
            $this->params = array_merge($this->params, [
                ':ruangan' => $this->ruangan,
            ]);
        }

        if (!empty($this->namaObatAlkes)) {
            $this->params[':nama_obatalkes'] = '%' . $this->namaObatAlkes . '%';
        }

        return $query;
    }

    public function countQuery()
    {
        $whereSql = $this->getWhereCondition();

        $query = $this->cte() . "
                select count(1)
                FROM obatalkes_m om
                INNER JOIN jenisobatalkes_m jm ON jm.jenisobatalkes_id = om.jenisobatalkes_id
                LEFT JOIN stok_aggregated sa ON sa.obatalkes_id = om.obatalkes_id
                LEFT JOIN satuankecil_m sm ON sm.satuankecil_id = om.satuankecil_id
                LEFT JOIN satuankecil_m st ON st.satuankecil_id = om.satuanterkecil_id
                LEFT JOIN zataktif_aggregated za ON za.obatalkes_id = om.obatalkes_id
                {$whereSql}
        ";

        $query = $this->queryFilter($query);

        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':dateminsatu', $this->dateMinSatu);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);
        $command->bindValue(':dateplussatu', $this->datePlusSatu);

        if (in_array($this->ruangan, ['58', '59'])) {
            $command->bindValue(':ruangan', $this->ruangan);
        }

        if (!empty($this->namaObatAlkes)) {
            $command->bindValue(':nama_obatalkes', '%' . $this->namaObatAlkes . '%');
        }
        
        return $command->queryScalar();
    }

}
