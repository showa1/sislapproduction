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
        $status = $request->get('status', '');
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-t'));
        $export = $request->get('export', '0');

        $baseJoinsAndWhere = "
            FROM pendaftaran_t pt 
            LEFT JOIN buatjanjipoli_t bt_direct ON bt_direct.pendaftaran_id = pt.pendaftaran_id
            LEFT JOIN (
                SELECT DISTINCT ON (pasien_id, DATE(tgljadwal))
                    pasien_id,
                    DATE(tgljadwal) AS tgl_jadwal,
                    buatjanjipoli_id,
                    is_jkn,
                    is_checkin
                FROM buatjanjipoli_t
                WHERE is_jkn = true
                  AND pendaftaran_id IS NULL
                ORDER BY pasien_id, DATE(tgljadwal), buatjanjipoli_id DESC
            ) bt_fallback 
                ON bt_direct.buatjanjipoli_id IS NULL 
               AND bt_fallback.pasien_id = pt.pasien_id 
               AND bt_fallback.tgl_jadwal = DATE(pt.tgl_pendaftaran)
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
            $baseJoinsAndWhere .= " AND DATE(pt.tgl_pendaftaran) BETWEEN :df AND :dt";
            $params[':df'] = $dateFrom;
            $params[':dt'] = $dateTo;
        }

        if (!empty($search)) {
            $baseJoinsAndWhere .= " AND (LOWER(pm.no_rekam_medik) LIKE LOWER(:search) OR LOWER(pm.nama_pasien) LIKE LOWER(:search))";
            $params[':search'] = '%' . $search . '%';
        }

        // Hitung ringkasan analisa (KPI Summary) sebelum filter dropdown status diterapkan
        $summarySql = "
            SELECT 
                COUNT(*) AS total_kunjungan,
                SUM(CASE WHEN COALESCE(bt_direct.is_jkn, bt_fallback.is_jkn, false) = true THEN 1 ELSE 0 END) AS total_mjkn,
                SUM(CASE WHEN COALESCE(bt_direct.is_jkn, bt_fallback.is_jkn, false) = true AND COALESCE(bt_direct.is_checkin, bt_fallback.is_checkin, false) = true THEN 1 ELSE 0 END) AS mjkn_berhasil_checkin,
                SUM(CASE WHEN COALESCE(bt_direct.is_jkn, bt_fallback.is_jkn, false) = true AND COALESCE(bt_direct.is_checkin, bt_fallback.is_checkin, false) = false THEN 1 ELSE 0 END) AS mjkn_gagal_checkin,
                SUM(CASE WHEN COALESCE(bt_direct.is_jkn, bt_fallback.is_jkn, false) = false THEN 1 ELSE 0 END) AS murni_onsite
            " . $baseJoinsAndWhere;

        try {
            $rawSummary = Yii::$app->db->createCommand($summarySql, $params)->queryOne();
        } catch (\Exception $e) {
            $rawSummary = [];
        }

        $totalKunjungan = (int)($rawSummary['total_kunjungan'] ?? 0);
        $totalMjkn      = (int)($rawSummary['total_mjkn'] ?? 0);
        $mjknBerhasil   = (int)($rawSummary['mjkn_berhasil_checkin'] ?? 0);
        $mjknGagal      = (int)($rawSummary['mjkn_gagal_checkin'] ?? 0);
        $murniOnsite    = (int)($rawSummary['murni_onsite'] ?? 0);

        $summary = [
            'total_kunjungan'        => $totalKunjungan,
            'total_mjkn'             => $totalMjkn,
            'mjkn_berhasil'          => $mjknBerhasil,
            'mjkn_gagal'             => $mjknGagal,
            'murni_onsite'           => $murniOnsite,
            'pct_mjkn_total'         => $totalKunjungan > 0 ? round(($totalMjkn / $totalKunjungan) * 100, 1) : 0,
            'pct_onsite_total'       => $totalKunjungan > 0 ? round(($murniOnsite / $totalKunjungan) * 100, 1) : 0,
            'pct_berhasil_dari_mjkn' => $totalMjkn > 0 ? round(($mjknBerhasil / $totalMjkn) * 100, 1) : 0,
            'pct_gagal_dari_mjkn'    => $totalMjkn > 0 ? round(($mjknGagal / $totalMjkn) * 100, 1) : 0,
            'pct_berhasil_total'     => $totalKunjungan > 0 ? round(($mjknBerhasil / $totalKunjungan) * 100, 1) : 0,
            'pct_gagal_total'        => $totalKunjungan > 0 ? round(($mjknGagal / $totalKunjungan) * 100, 1) : 0,
        ];

        $sql = "
            SELECT 
                pt.tgl_pendaftaran, 
                rm.ruangan_nama, 
                cm.carabayar_nama,
                pjm.penjamin_nama,
                pm.no_rekam_medik, 
                pm.nama_pasien, 
                COALESCE(bt_direct.buatjanjipoli_id, bt_fallback.buatjanjipoli_id, pt.buatjanjipoli_id) AS buatjanjipoli_id, 
                COALESCE(bt_direct.is_jkn, bt_fallback.is_jkn, false) AS is_jkn, 
                COALESCE(bt_direct.is_checkin, bt_fallback.is_checkin, false) AS is_checkin
            " . $baseJoinsAndWhere;

        $countSql = "SELECT COUNT(*) " . $baseJoinsAndWhere;

        if ($status === 'mjkn_no_checkin') {
            $cond = " AND COALESCE(bt_direct.is_jkn, bt_fallback.is_jkn, false) = true AND COALESCE(bt_direct.is_checkin, bt_fallback.is_checkin, false) = false";
            $sql .= $cond;
            $countSql .= $cond;
        } elseif ($status === 'mjkn_checkin') {
            $cond = " AND COALESCE(bt_direct.is_jkn, bt_fallback.is_jkn, false) = true AND COALESCE(bt_direct.is_checkin, bt_fallback.is_checkin, false) = true";
            $sql .= $cond;
            $countSql .= $cond;
        } elseif ($status === 'mjkn_all') {
            $cond = " AND COALESCE(bt_direct.is_jkn, bt_fallback.is_jkn, false) = true";
            $sql .= $cond;
            $countSql .= $cond;
        } elseif ($status === 'onsite') {
            $cond = " AND COALESCE(bt_direct.is_jkn, bt_fallback.is_jkn, false) = false";
            $sql .= $cond;
            $countSql .= $cond;
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
                if ($r['is_checkin']) {
                    $isCheckin = 'Ya';
                } elseif ($r['is_jkn']) {
                    $isCheckin = 'Tidak (Gagal Check-In / Daftar Onsite)';
                } else {
                    $isCheckin = 'Tidak';
                }

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
            'summary' => $summary,
            'search' => $search,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
