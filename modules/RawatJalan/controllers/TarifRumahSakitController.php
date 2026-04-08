<?php

namespace app\modules\rawatjalan\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use app\models\JenistarifM;
use app\models\KelaspelayananM;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;

class TarifRumahSakitController extends BaseController
{    

    public $startDate, $endDate, $totalCount, $jenistarif;

    public $params = [];

    public $kelaspelayanan, $namatindakan;

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {   
        $this->jenistarif = Yii::$app->request->get('jenistarif');
        $this->kelaspelayanan = Yii::$app->request->get('kelaspelayanan');
        $this->namatindakan = Yii::$app->request->get('namatindakan');

        $dropdownselect = [
            'jenistarif' =>  $this->jenistarif,
            'kelaspelayanan' =>  $this->kelaspelayanan,
            'namatindakan' =>  $this->namatindakan
        ];

        return $this->render('index', [
            'dataProvider' => $this->dataprovider(),
            'listjenis' => JenistarifM::dropdown(),
            'listpelayanan' => KelaspelayananM::dropdown(),
            'dropdownselect' => $dropdownselect
        ]);

    }

    public function dataprovider()
    {
        return new SqlDataProvider([
            'sql' => $this->baseQuery(),
            'params' => $this->params,
            'totalCount' => $this->countQuery(),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
    }

    public function actionExport()
    {
        $dataProvider = new SqlDataProvider([
            'sql' => $this->baseQuery(), // Query yang sama dengan GridView
            'pagination' => false,  // Disable pagination untuk ekspor semua data
        ]);
        
        $models = $dataProvider->getModels();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header Kolom
        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A3', 'No');
        $sheet->setCellValue('B3', 'Jenis Tarif');
        $sheet->setCellValue('C3', 'Kelompok Tindakan');
        $sheet->setCellValue('D3', 'Kode Tindakan');
        $sheet->setCellValue('E3', 'Nama Tindakan');
        $sheet->setCellValue('F3', 'Kelas Pelayanan');
        $sheet->setCellValue('G3', 'Jasa Asisten Anestesi');
        $sheet->setCellValue('H3', 'Jasa Administrasi');
        $sheet->setCellValue('I3', 'Jasa Instrumen');
        $sheet->setCellValue('J3', 'Billing Rawat Inap');
        $sheet->setCellValue('K3', 'Sewa Kamar Oprasi');
        $sheet->setCellValue('L3', 'Jasa Operator');
        $sheet->setCellValue('M3', 'Jasa Asisten Operator');
        $sheet->setCellValue('N3', 'Jasa Bidan');
        $sheet->setCellValue('O3', 'Jasa BHP');
        $sheet->setCellValue('P3', 'Sewa Alat');
        $sheet->setCellValue('Q3', 'Visite Dokter');
        $sheet->setCellValue('R3', 'Jasa Observasi');
        $sheet->setCellValue('S3', 'Viste Spa');
        $sheet->setCellValue('T3', 'Jasa Dokter Anastesi');
        $sheet->setCellValue('U3', 'Jasa Visite');
        $sheet->setCellValue('V3', 'Visite Spog');
        $sheet->setCellValue('W3', 'Bmhp');
        $sheet->setCellValue('X3', 'Jasa Rumah Sakit');
        $sheet->setCellValue('Y3', 'Total');
        $sheet->getStyle('A3:Y3')->getFont()->setBold(true);
      
        // Isi Data
        $row = 4; // Mulai dari baris kedua
        $i = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['jenistarif_nama']);
            $sheet->setCellValue('C' . $row, $model['kelompoktindakan_nama']);
            $sheet->setCellValue('D' . $row, $model['daftartindakan_kode']);
            $sheet->setCellValue('E' . $row, $model['daftartindakan_nama']);
            $sheet->setCellValue('F' . $row, $model['kelaspelayanan_nama']);
            $sheet->setCellValue('G' . $row, !empty($model['jasa_asisten_anastesi']) ? number_format($model['jasa_asisten_anastesi'], 2, ',', '.') : "");
            $sheet->setCellValue('H' . $row, !empty($model['jasa_administrasi']) ? number_format($model['jasa_administrasi'], 2, ',', '.') : '');
            $sheet->setCellValue('I' . $row, !empty($model['jasa_instrumen']) ? number_format($model['jasa_instrumen'], 2, ',', '.') : '');
            $sheet->setCellValue('J' . $row, !empty($model['billing_rawat_inap']) ? number_format($model['billing_rawat_inap'], 2, ',', '.') : '');
            $sheet->setCellValue('K' . $row, !empty($model['sewa_kamar_oprasi']) ? number_format($model['sewa_kamar_oprasi'], 2, ',', '.') : '');
            $sheet->setCellValue('L' . $row, !empty($model['jasa_operator']) ? number_format($model['jasa_operator'], 2, ',', '.') : '');
            $sheet->setCellValue('M' . $row, !empty($model['jasa_asisten_operator']) ? number_format($model['jasa_asisten_operator'], 2, ',', '.') : '');
            $sheet->setCellValue('N' . $row, !empty($model['jasa_bidan']) ? number_format($model['jasa_bidan'], 2, ',', '.') : '');
            $sheet->setCellValue('O' . $row, !empty($model['jasa_bhp']) ? number_format($model['jasa_bhp'], 2, ',', '.') : '');
            $sheet->setCellValue('P' . $row, !empty($model['sewa_alat']) ? number_format($model['sewa_alat'], 2, ',', '.') : '');
            $sheet->setCellValue('Q' . $row, !empty($model['visite_dokter']) ? number_format($model['visite_dokter'], 2, ',', '.') : '');
            $sheet->setCellValue('R' . $row, !empty($model['jasa_observasi']) ? number_format($model['jasa_observasi'], 2, ',', '.') : '');
            $sheet->setCellValue('S' . $row, !empty($model['visite_spa']) ? number_format($model['visite_spa'], 2, ',', '.') : '');
            $sheet->setCellValue('T' . $row, !empty($model['jasa_dokter_anastesi']) ? number_format($model['jasa_dokter_anastesi'], 2, ',', '.') : '');
            $sheet->setCellValue('U' . $row, !empty($model['jasa_visite']) ? number_format($model['jasa_visite'], 2, ',', '.') : '');
            $sheet->setCellValue('V' . $row, !empty($model['visite_spog']) ? number_format($model['visite_spog'], 2, ',', '.') : '');
            $sheet->setCellValue('W' . $row, !empty($model['bmhp']) ? number_format($model['bmhp'], 2, ',', '.') : '');
            $sheet->setCellValue('X' . $row, !empty($model['jasa_rumah_sakit']) ? number_format($model['jasa_rumah_sakit'], 2, ',', '.') : '');
            $sheet->setCellValue('Y' . $row, !empty($model['total']) ? number_format($model['total'], 2, ',', '.') : '');
            $row++;
            $i++;
        }

        $sheet->getStyle('A3:Y'.($row -1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // Simpan file ke response
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'exported-data.xlsx';
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

    public function baseQuery()
    {
        $query =  "
            select jm.jenistarif_nama,klm.kelompoktindakan_nama ,dm.daftartindakan_kode, dm.daftartindakan_nama, km.kelaspelayanan_nama,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '13') as jasa_asisten_anastesi,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '22') as jasa_administrasi,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '56') as jasa_instrumen,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '61') as billing_rawat_inap,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '74') as sewa_kamar_oprasi,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '21') as jasa_operator,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '23') as jasa_asisten_operator,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '64') as jasa_bidan,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '19') as jasa_bhp,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '75') as sewa_alat,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '76') as visite_dokter,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '78') as jasa_observasi,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '80') as visite_spa,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '12') as jasa_dokter_anastesi,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '5') as jasa_visite,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '79') as visite_spog,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '84') as bmhp,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '1') as jasa_rumah_sakit,
            sum( harga_tariftindakan) filter( WHERE komponentarif_id = '6') as total
            from tariftindakan_m tm
            inner join daftartindakan_m dm on dm.daftartindakan_id = tm.daftartindakan_id
            inner join kelaspelayanan_m km on km.kelaspelayanan_id = tm.kelaspelayanan_id
            inner join kelompoktindakan_m klm on klm.kelompoktindakan_id = dm.kelompoktindakan_id
            inner join jenistarif_m jm on jm.jenistarif_id = tm.jenistarif_id

        ";

        $query = $this->queryFilter($query);

        $query .= "
            group by jm.jenistarif_nama, dm.daftartindakan_kode, 
            dm.daftartindakan_nama, km.kelaspelayanan_nama,klm.kelompoktindakan_nama
            order by jm.jenistarif_nama,dm.daftartindakan_nama, km.kelaspelayanan_nama
        ";

        return $query;

    }

    public function queryFilter($query)
    {
        if (
            $this->jenistarif
            || $this->kelaspelayanan
            || $this->namatindakan
        ) {
            $query .= " where ";
        }

        $combine = "";

        if (!empty($this->jenistarif)) {
            $query .= "tm.jenistarif_id = :jenistarif ";
            $this->params = array_merge($this->params, [':jenistarif' => $this->jenistarif]);
            $combine = "and ";
        }
        
        if (!empty($this->kelaspelayanan)) {
            $query .= $combine . "km.kelaspelayanan_id = :kelaspelayanan ";
            $this->params = array_merge($this->params, [':kelaspelayanan' => $this->kelaspelayanan]);
            $combine =  !empty($this->namatindakan) ? "and " : $combine;
        }
        
        if (!empty($this->namatindakan)) {
            $query .= $combine . "UPPER(dm.daftartindakan_nama) LIKE :namatindakan";
            $this->params = array_merge($this->params, [':namatindakan' => '%'. strtoupper($this->namatindakan) .'%']);
        }

        return $query;
    }

    public function countQuery()
    {
        $query = "
            select count(*) as total from (
                select count(*)
                from tariftindakan_m tm
                inner join daftartindakan_m dm on dm.daftartindakan_id = tm.daftartindakan_id
                inner join kelaspelayanan_m km on km.kelaspelayanan_id = tm.kelaspelayanan_id
                inner join kelompoktindakan_m klm on klm.kelompoktindakan_id = dm.kelompoktindakan_id
                inner join jenistarif_m jm on jm.jenistarif_id = tm.jenistarif_id
        ";

        $query = $this->queryFilter($query);

        $query .= " 
                group by jm.jenistarif_nama, dm.daftartindakan_kode, 
                dm.daftartindakan_nama, km.kelaspelayanan_nama,klm.kelompoktindakan_nama
            ) subtotal";


        $command = Yii::$app->db->createCommand($query);
        if ($this->jenistarif) {
            $command->bindValue(':jenistarif', $this->jenistarif);
        }

        if ($this->kelaspelayanan) {
            $command->bindValue(':kelaspelayanan', $this->kelaspelayanan);
        }
        
        if ($this->namatindakan) {
            $command->bindValue(':namatindakan', '%'. strtoupper($this->namatindakan) .'%');
        }

        return $command->queryScalar();
    }

}
