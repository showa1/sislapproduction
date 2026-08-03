<?php

namespace app\modules\Pendaftaran\controllers;

use Yii;
use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use yii\web\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class MonitoringPasienController extends BaseController
{
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $search = $request->get('search', '');
        $dateFrom = $request->get('date_from', date('Y-m-d'));
        $dateTo = $request->get('date_to', date('Y-m-d'));
        $export = $request->get('export', '0');

        $sql = "
            SELECT 
                pt.tgl_pendaftaran, 
                pt.no_pendaftaran,
                pm.no_rekam_medik, 
                pm.nama_pasien, 
                rm.ruangan_nama, 
                cm.carabayar_nama,
                pjm.penjamin_nama,
                CASE 
                    WHEN pt.pasienbatalperiksa_id IS NOT NULL THEN 'Batal'
                    WHEN bt.is_checkin = true THEN 'Check-In'
                    ELSE 'Terdaftar'
                END AS status_pasien
            FROM pendaftaran_t pt 
            LEFT JOIN buatjanjipoli_t bt ON bt.pendaftaran_id = pt.pendaftaran_id
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            JOIN pasien_m pm ON pm.pasien_id = pt.pasien_id
            LEFT JOIN penjaminpasien_m pjm ON pjm.penjamin_id = pt.penjamin_id
            LEFT JOIN carabayar_m cm ON cm.carabayar_id = pt.carabayar_id
            WHERE pt.ruangan_id IN (
                SELECT ruangan_id FROM ruangan_m WHERE instalasi_id = '2'
            )
        ";

        $countSql = "
            SELECT COUNT(*) 
            FROM pendaftaran_t pt 
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            JOIN pasien_m pm ON pm.pasien_id = pt.pasien_id
            WHERE pt.ruangan_id IN (
                SELECT ruangan_id FROM ruangan_m WHERE instalasi_id = '2'
            )
        ";

        $params = [];

        if (!empty($dateFrom) && !empty($dateTo)) {
            $sql .= " AND DATE(pt.tgl_pendaftaran) BETWEEN :df AND :dt";
            $countSql .= " AND DATE(pt.tgl_pendaftaran) BETWEEN :df AND :dt";
            $params[':df'] = $dateFrom;
            $params[':dt'] = $dateTo;
        }

        if (!empty($search)) {
            $sql .= " AND (LOWER(pm.no_rekam_medik) LIKE LOWER(:search) OR LOWER(pm.nama_pasien) LIKE LOWER(:search) OR LOWER(pt.no_pendaftaran) LIKE LOWER(:search))";
            $countSql .= " AND (LOWER(pm.no_rekam_medik) LIKE LOWER(:search) OR LOWER(pm.nama_pasien) LIKE LOWER(:search) OR LOWER(pt.no_pendaftaran) LIKE LOWER(:search))";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY pt.tgl_pendaftaran DESC";

        if ($export === '1') {
            $data = Yii::$app->db->createCommand($sql, $params)->queryAll();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
            $sheet->setCellValue('A2', 'Laporan Monitoring Pasien Pendaftaran');
            $sheet->setCellValue('A3', 'Periode: ' . $dateFrom . ' s/d ' . $dateTo);
            $sheet->getStyle('A1:A3')->getFont()->setBold(true);

            $headers = ['No', 'Tgl Pendaftaran', 'No Pendaftaran', 'No RM', 'Nama Pasien', 'Ruangan/Poli', 'Cara Bayar', 'Penjamin', 'Status Pasien'];
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . '5', $h);
                $col++;
            }
            $sheet->getStyle('A5:I5')->getFont()->setBold(true);

            $rowIdx = 6;
            $i = 1;
            foreach ($data as $r) {
                $sheet->setCellValue('A' . $rowIdx, $i);
                $sheet->setCellValue('B' . $rowIdx, $r['tgl_pendaftaran'] ? date('d/m/Y H:i', strtotime($r['tgl_pendaftaran'])) : '-');
                $sheet->setCellValue('C' . $rowIdx, $r['no_pendaftaran']);
                $sheet->setCellValueExplicit('D' . $rowIdx, $r['no_rekam_medik'], DataType::TYPE_STRING);
                $sheet->setCellValue('E' . $rowIdx, $r['nama_pasien']);
                $sheet->setCellValue('F' . $rowIdx, $r['ruangan_nama']);
                $sheet->setCellValue('G' . $rowIdx, $r['carabayar_nama']);
                $sheet->setCellValue('H' . $rowIdx, $r['penjamin_nama']);
                $sheet->setCellValue('I' . $rowIdx, $r['status_pasien']);

                $rowIdx++;
                $i++;
            }

            $sheet->getStyle('A5:I'.($rowIdx - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            
            foreach (range('A', 'I') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = 'Laporan_Monitoring_Pasien.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($tempFile);

            return Yii::$app->response->sendFile($tempFile, $fileName)->on(
                Response::EVENT_AFTER_SEND,
                function ($event) {
                    unlink($event->data);
                },
                $tempFile
            );
        }

        try {
            $totalCount = Yii::$app->db->createCommand($countSql, $params)->queryScalar();
        } catch (\Exception $e) {
            $totalCount = 0;
        }

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params,
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
