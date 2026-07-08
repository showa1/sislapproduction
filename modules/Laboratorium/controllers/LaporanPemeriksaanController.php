<?php

namespace app\modules\laboratorium\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use yii\helpers\Html;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;

class LaporanPemeriksaanController extends BaseController
{
    public function actionIndex()
    {
        $dateFrom = Yii::$app->request->get('date_from', date('01-m-Y'));
        $dateTo = Yii::$app->request->get('date_to', date('d-m-Y'));

        $df = date('Y-m-d', strtotime($dateFrom));
        $dt = date('Y-m-d', strtotime($dateTo));

        $sql = "
            SELECT
                dm.daftartindakan_kode,
                dm.daftartindakan_nama,

                COUNT(CASE WHEN pt.carabayar_id = 1 THEN 1 END) AS pribadi,
                COUNT(CASE WHEN pt.carabayar_id = 2 THEN 1 END) AS bpjs,
                COUNT(CASE WHEN pt.carabayar_id = 3 THEN 1 END) AS asuransi,
                COUNT(CASE WHEN pt.carabayar_id = 20 THEN 1 END) AS bpjs_ketenagakerjaan,

                COUNT(*) AS total

            FROM tindakanpelayanan_t tt
            JOIN pendaftaran_t pt
                ON pt.pendaftaran_id = tt.pendaftaran_id
            JOIN daftartindakan_m dm
                ON dm.daftartindakan_id = tt.daftartindakan_id

            WHERE tt.ruangan_id = 53
              AND DATE(tt.tgl_tindakan) BETWEEN :datefrom AND :dateto
              AND pt.pasienbatalperiksa_id IS NULL

            GROUP BY
                dm.daftartindakan_kode,
                dm.daftartindakan_nama

            ORDER BY
                dm.daftartindakan_nama
        ";

        $countSql = "
            SELECT COUNT(DISTINCT dm.daftartindakan_id)
            FROM tindakanpelayanan_t tt
            JOIN pendaftaran_t pt ON pt.pendaftaran_id = tt.pendaftaran_id
            JOIN daftartindakan_m dm ON dm.daftartindakan_id = tt.daftartindakan_id
            WHERE tt.ruangan_id = 53
              AND DATE(tt.tgl_tindakan) BETWEEN :datefrom AND :dateto
              AND pt.pasienbatalperiksa_id IS NULL
        ";

        $totalCount = Yii::$app->db->createCommand($countSql, [
            ':datefrom' => $df,
            ':dateto' => $dt
        ])->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => [
                ':datefrom' => $df,
                ':dateto' => $dt
            ],
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function actionExport()
    {
        $dateFrom = Yii::$app->request->get('date_from', date('01-m-Y'));
        $dateTo = Yii::$app->request->get('date_to', date('d-m-Y'));

        $df = date('Y-m-d', strtotime($dateFrom));
        $dt = date('Y-m-d', strtotime($dateTo));

        $sql = "
            SELECT
                dm.daftartindakan_kode,
                dm.daftartindakan_nama,

                COUNT(CASE WHEN pt.carabayar_id = 1 THEN 1 END) AS pribadi,
                COUNT(CASE WHEN pt.carabayar_id = 2 THEN 1 END) AS bpjs,
                COUNT(CASE WHEN pt.carabayar_id = 3 THEN 1 END) AS asuransi,
                COUNT(CASE WHEN pt.carabayar_id = 20 THEN 1 END) AS bpjs_ketenagakerjaan,

                COUNT(*) AS total

            FROM tindakanpelayanan_t tt
            JOIN pendaftaran_t pt
                ON pt.pendaftaran_id = tt.pendaftaran_id
            JOIN daftartindakan_m dm
                ON dm.daftartindakan_id = tt.daftartindakan_id

            WHERE tt.ruangan_id = 53
              AND DATE(tt.tgl_tindakan) BETWEEN :datefrom AND :dateto
              AND pt.pasienbatalperiksa_id IS NULL

            GROUP BY
                dm.daftartindakan_kode,
                dm.daftartindakan_nama

            ORDER BY
                dm.daftartindakan_nama
        ";

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => [
                ':datefrom' => $df,
                ':dateto' => $dt
            ],
            'pagination' => false,
        ]);

        $models = $dataProvider->getModels();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Jumlah Pemeriksaan Laboratorium');
        $sheet->setCellValue('A3', 'Periode: ' . $dateFrom . ' s/d ' . $dateTo);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3')->getFont()->setItalic(true);

        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'Kode Tindakan');
        $sheet->setCellValue('C5', 'Nama Tindakan');
        $sheet->setCellValue('D5', 'Pribadi');
        $sheet->setCellValue('E5', 'BPJS Kesehatan');
        $sheet->setCellValue('F5', 'Asuransi');
        $sheet->setCellValue('G5', 'BPJS Ketenagakerjaan');
        $sheet->setCellValue('H5', 'Total');
        
        $sheet->getStyle('A5:H5')->getFont()->setBold(true);

        $row = 6;
        $i = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['daftartindakan_kode']);
            $sheet->setCellValue('C' . $row, $model['daftartindakan_nama']);
            $sheet->setCellValue('D' . $row, $model['pribadi']);
            $sheet->setCellValue('E' . $row, $model['bpjs']);
            $sheet->setCellValue('F' . $row, $model['asuransi']);
            $sheet->setCellValue('G' . $row, $model['bpjs_ketenagakerjaan']);
            $sheet->setCellValue('H' . $row, $model['total']);
            $row++;
            $i++;
        }

        $sheet->getStyle('A5:H'.($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Auto size columns
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_Jumlah_Pemeriksaan_Lab.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return Yii::$app->response->sendFile($tempFile, $fileName)->on(
            \yii\web\Response::EVENT_AFTER_SEND,
            function ($event) {
                unlink($event->data);
            },
            $tempFile
        );
    }
}
