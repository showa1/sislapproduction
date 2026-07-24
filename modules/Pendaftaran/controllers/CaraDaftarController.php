<?php

namespace app\modules\Pendaftaran\controllers;

use Yii;
use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use yii\web\Response;

class CaraDaftarController extends BaseController
{
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $search = $request->get('search', '');
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-t'));
        $export = $request->get('export', '0');

        $sql = "
            SELECT 
                pt.tgl_pendaftaran, 
                rm.ruangan_nama, 
                cm.carabayar_nama,
                pjm.penjamin_nama,
                pm.no_rekam_medik, 
                pm.nama_pasien, 
                pt.buatjanjipoli_id, 
                bt.is_jkn, 
                bt.is_checkin 
            FROM pendaftaran_t pt 
            LEFT JOIN buatjanjipoli_t bt ON bt.pendaftaran_id = pt.pendaftaran_id
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            JOIN pasien_m pm ON pm.pasien_id = pt.pasien_id
            JOIN penjaminpasien_m pjm ON pjm.penjamin_id = pt.penjamin_id
            JOIN carabayar_m cm ON cm.carabayar_id = pt.carabayar_id
            WHERE pt.ruangan_id IN (
                SELECT ruangan_id FROM ruangan_m WHERE instalasi_id = '2'
            )
            AND pt.pasienbatalperiksa_id IS NULL
        ";

        $countSql = "
            SELECT COUNT(*) 
            FROM pendaftaran_t pt 
            LEFT JOIN buatjanjipoli_t bt ON bt.pendaftaran_id = pt.pendaftaran_id
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            JOIN pasien_m pm ON pm.pasien_id = pt.pasien_id
            JOIN penjaminpasien_m pjm ON pjm.penjamin_id = pt.penjamin_id
            JOIN carabayar_m cm ON cm.carabayar_id = pt.carabayar_id
            WHERE pt.ruangan_id IN (
                SELECT ruangan_id FROM ruangan_m WHERE instalasi_id = '2'
            )
            AND pt.pasienbatalperiksa_id IS NULL
        ";

        $params = [];

        if (!empty($dateFrom) && !empty($dateTo)) {
            $sql .= " AND DATE(pt.tgl_pendaftaran) BETWEEN :df AND :dt";
            $countSql .= " AND DATE(pt.tgl_pendaftaran) BETWEEN :df AND :dt";
            $params[':df'] = $dateFrom;
            $params[':dt'] = $dateTo;
        }

        if (!empty($search)) {
            $sql .= " AND (LOWER(pm.no_rekam_medik) LIKE LOWER(:search) OR LOWER(pm.nama_pasien) LIKE LOWER(:search))";
            $countSql .= " AND (LOWER(pm.no_rekam_medik) LIKE LOWER(:search) OR LOWER(pm.nama_pasien) LIKE LOWER(:search))";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY pt.tgl_pendaftaran ASC";

        if ($export === '1') {
            $data = Yii::$app->db->createCommand($sql, $params)->queryAll();

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
            $sheet->setCellValue('A2', 'Laporan Cara Daftar Pasien Rawat Jalan');
            $sheet->setCellValue('A3', 'Periode: ' . $dateFrom . ' s/d ' . $dateTo);
            $sheet->getStyle('A1:A3')->getFont()->setBold(true);

            $headers = ['No', 'Tgl Pendaftaran', 'Ruangan', 'Cara Bayar', 'Penjamin', 'No RM', 'Nama Pasien', 'Mobile JKN', 'Check In'];
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . '5', $h);
                $col++;
            }
            $sheet->getStyle('A5:I5')->getFont()->setBold(true);

            $rowIdx = 6;
            $i = 1;
            foreach ($data as $r) {
                $isJkn = $r['is_jkn'] ? 'Ya' : 'Tidak';
                $isCheckin = $r['is_checkin'] ? 'Ya' : 'Tidak';

                $sheet->setCellValue('A' . $rowIdx, $i);
                $sheet->setCellValue('B' . $rowIdx, $r['tgl_pendaftaran'] ? date('d/m/Y H:i', strtotime($r['tgl_pendaftaran'])) : '-');
                $sheet->setCellValue('C' . $rowIdx, $r['ruangan_nama']);
                $sheet->setCellValue('D' . $rowIdx, $r['carabayar_nama']);
                $sheet->setCellValue('E' . $rowIdx, $r['penjamin_nama']);
                $sheet->setCellValueExplicit('F' . $rowIdx, $r['no_rekam_medik'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('G' . $rowIdx, $r['nama_pasien']);
                $sheet->setCellValue('H' . $rowIdx, $isJkn);
                $sheet->setCellValue('I' . $rowIdx, $isCheckin);

                $rowIdx++;
                $i++;
            }

            $sheet->getStyle('A5:I'.($rowIdx - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            
            foreach (range('A', 'I') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = 'Laporan_Cara_Daftar.xlsx';
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
                'pageSize' => 10,
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
