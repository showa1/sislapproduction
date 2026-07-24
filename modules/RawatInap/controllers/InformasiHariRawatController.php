<?php

namespace app\modules\RawatInap\controllers;

use app\controllers\BaseController;
use Yii;

class InformasiHariRawatController extends BaseController
{
    public function actionIndex()
    {
        $dateFrom = Yii::$app->request->get('date_from', date('Y-m-01'));
        $dateTo = Yii::$app->request->get('date_to', date('Y-m-d'));
        $caraBayar = Yii::$app->request->get('cara_bayar', '');
        $diagnosaFilter = Yii::$app->request->get('diagnosa', '');

        $export = Yii::$app->request->get('export', '0');

        $sql = "
            SELECT
                pt.tgl_pendaftaran,
                pt.no_pendaftaran,
                cm.carabayar_nama,
                pm.no_rekam_medik,
                pm.nama_pasien,

                STRING_AGG(dm.diagnosa_kode || ' - ' || dm.diagnosa_nama, E'\n') AS diagnosa,

                pat.tgladmisi AS tgl_nginap,
                pat.tglpulang,
                STRING_AGG(
                    DISTINCT kr.kamarruangan_nokamar,
                    ' -> '
                    ORDER BY kr.kamarruangan_nokamar
                ) AS riwayat_kamar,
                EXTRACT(EPOCH FROM (pat.tglpulang - pat.tgladmisi)) / 3600 AS lama_menginap

            FROM pasienadmisi_t pat
            JOIN pendaftaran_t pt
                ON pt.pendaftaran_id = pat.pendaftaran_id
            JOIN pasien_m pm
                ON pm.pasien_id = pat.pasien_id
            JOIN carabayar_m cm
                ON cm.carabayar_id = pat.carabayar_id
            LEFT JOIN pasienmorbiditas_t pst
                ON pst.pendaftaran_id = pt.pendaftaran_id
            LEFT JOIN diagnosa_m dm
                ON dm.diagnosa_id = pst.diagnosa_id
            LEFT JOIN masukkamar_t mk
                ON mk.pasienadmisi_id = pat.pasienadmisi_id
            LEFT JOIN kamarruangan_m kr
                ON kr.kamarruangan_id = mk.kamarruangan_id
            WHERE DATE(pat.tglpulang) >= :df
              AND DATE(pat.tglpulang) <= :dt
              AND pt.pasienbatalperiksa_id IS NULL
        ";

        $params = [
            ':df' => $dateFrom,
            ':dt' => $dateTo
        ];

        if (!empty($caraBayar)) {
            $sql .= " AND cm.carabayar_nama = :cb ";
            $params[':cb'] = $caraBayar;
        }

        $sql .= "
            GROUP BY
                pt.tgl_pendaftaran,
                pt.no_pendaftaran,
                cm.carabayar_nama,
                pm.no_rekam_medik,
                pm.nama_pasien,
                pat.tgladmisi,
                pat.tglpulang
        ";

        if (!empty($diagnosaFilter)) {
            $sql .= " HAVING STRING_AGG(dm.diagnosa_kode || ' - ' || dm.diagnosa_nama, E'\n') ILIKE :diag ";
            $params[':diag'] = '%' . $diagnosaFilter . '%';
        }

        $sql .= " ORDER BY lama_menginap DESC ";

        $data = Yii::$app->db->createCommand($sql, $params)->queryAll();

        if ($export === '1') {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
            $sheet->setCellValue('A2', 'Laporan Informasi Hari Rawat');
            $sheet->setCellValue('A3', 'Periode: ' . $dateFrom . ' s/d ' . $dateTo);
            $sheet->getStyle('A1:A3')->getFont()->setBold(true);

            $headers = ['No', 'No. RM', 'Nama Pasien', 'Tgl Pendaftaran', 'No Pendaftaran', 'Cara Bayar', 'Diagnosa', 'Riwayat Kamar', 'Tgl Menginap', 'Tgl Pulang', 'Lama Dirawat'];
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . '5', $h);
                $col++;
            }
            $sheet->getStyle('A5:K5')->getFont()->setBold(true);

            $rowIdx = 6;
            $i = 1;
            foreach ($data as $r) {
                $lamaJam = $r['lama_menginap'] ?? 0;
                $hari = floor($lamaJam / 24);
                if ($hari > 0) {
                    $lamaStr = $hari . ' Hari';
                } else {
                    $lamaStr = 'Hari ini';
                }

                $sheet->setCellValue('A' . $rowIdx, $i);
                $sheet->setCellValue('B' . $rowIdx, $r['no_rekam_medik']);
                $sheet->setCellValue('C' . $rowIdx, $r['nama_pasien']);
                $sheet->setCellValue('D' . $rowIdx, $r['tgl_pendaftaran'] ? date('d/m/Y H:i', strtotime($r['tgl_pendaftaran'])) : '-');
                $sheet->setCellValue('E' . $rowIdx, $r['no_pendaftaran']);
                $sheet->setCellValue('F' . $rowIdx, $r['carabayar_nama']);
                $sheet->setCellValue('G' . $rowIdx, str_replace("\n", ", ", $r['diagnosa'] ?? '-'));
                $sheet->setCellValue('H' . $rowIdx, $r['riwayat_kamar'] ?? '-');
                $sheet->setCellValue('I' . $rowIdx, $r['tgl_nginap'] ? date('d/m/Y H:i', strtotime($r['tgl_nginap'])) : '-');
                $sheet->setCellValue('J' . $rowIdx, $r['tglpulang'] ? date('d/m/Y H:i', strtotime($r['tglpulang'])) : '-');
                $sheet->setCellValue('K' . $rowIdx, $lamaStr);
                $rowIdx++;
                $i++;
            }

            $sheet->getStyle('A5:J'.($rowIdx - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            
            foreach (range('A', 'J') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = 'Laporan_Hari_Rawat.xlsx';
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

        // Get options for Cara Bayar dropdown
        $optCaraBayar = Yii::$app->db->createCommand("
            SELECT DISTINCT carabayar_nama 
            FROM carabayar_m 
            ORDER BY carabayar_nama
        ")->queryColumn();

        return $this->render('index', [
            'data' => $data,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'caraBayar' => $caraBayar,
            'diagnosaFilter' => $diagnosaFilter,
            'optCaraBayar' => $optCaraBayar
        ]);
    }
}
