<?php

namespace app\modules\RawatInap\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use DateTime;

class DuranteOperasiController extends BaseController
{
    public $dateFrom;
    public $dateTo;
    public $tindakanId;
    public $statuscari;
    public $params = [];

    /**
     * Menampilkan halaman Durante Operasi
     */
    public function actionIndex()
    {
        $this->setupSearch();

        $dropdownselect = [
            'start'       => Yii::$app->request->get('date_from'),
            'to'          => Yii::$app->request->get('date_to'),
            'tindakan_id' => $this->tindakanId,
        ];

        return $this->render('index', [
            'dataProvider'   => $this->dataprovider(),
            'dropdownselect' => $dropdownselect,
            'listTindakan'   => $this->getOptTindakanOperasi(),
        ]);
    }

    public function getOptTindakanOperasi()
    {
        $sql = "
            SELECT DISTINCT 
                dm.daftartindakan_id, 
                dm.daftartindakan_kode, 
                dm.daftartindakan_nama
            FROM daftartindakan_m dm
            WHERE dm.daftartindakan_id IN (
                SELECT DISTINCT tt.daftartindakan_id 
                FROM rencanaoperasi_t rt 
                JOIN tindakanpelayanan_t tt ON tt.tindakanpelayanan_id = rt.tindakanpelayanan_id 
                WHERE tt.daftartindakan_id IS NOT NULL
            )
            OR (
                dm.daftartindakan_aktif = TRUE 
                AND (dm.kelompoktindakan_id = 2 OR dm.daftartindakan_kode LIKE 'OP%')
            )
            ORDER BY dm.daftartindakan_nama ASC
        ";
        $rows = Yii::$app->db->createCommand($sql)->queryAll();
        $opt = [];
        foreach ($rows as $r) {
            $kode = !empty($r['daftartindakan_kode']) ? '[' . trim($r['daftartindakan_kode']) . '] ' : '';
            $opt[$r['daftartindakan_id']] = $kode . trim($r['daftartindakan_nama']);
        }
        return $opt;
    }

    public function setupSearch()
    {
        $this->dateFrom   = Yii::$app->request->get('date_from');
        $this->dateTo     = Yii::$app->request->get('date_to');
        $this->tindakanId = Yii::$app->request->get('tindakan_id');

        if (!empty($this->dateFrom)) {
            $parsed = DateTime::createFromFormat('d-m-Y', $this->dateFrom);
            if ($parsed) {
                $this->dateFrom = $parsed->format('Y-m-d');
            }
        }

        if (!empty($this->dateTo)) {
            $parsed = DateTime::createFromFormat('d-m-Y', $this->dateTo);
            if ($parsed) {
                $this->dateTo = $parsed->format('Y-m-d');
            }
        }

        $cari = Yii::$app->request->get('cari');
        $this->statuscari = !empty($cari) ? true : false;
    }

    public function dataprovider()
    {
        return new SqlDataProvider([
            'sql'        => $this->statuscari ? $this->baseQuery() : $this->queryKosong(),
            'params'     => $this->params,
            'totalCount' => $this->statuscari ? $this->countQuery() : 0,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
    }

    public function queryKosong()
    {
        return "SELECT rt.rencanaoperasi_id FROM rencanaoperasi_t rt WHERE 1=0";
    }

    public function baseQuery()
    {
        $whereConditions = $this->buildWhereConditions();

        $query = "
            SELECT 
                pm.no_rekam_medik,
                pm.nama_pasien, 
                pt.no_pendaftaran,
                pt.tgl_pendaftaran,
                cm.carabayar_nama,
                dm.daftartindakan_kode,
                dm.daftartindakan_nama,
                tt.tgl_tindakan,
                tt.qty_tindakan,
                tt.tarif_satuan,
                tt.tarif_tindakan,
                rt.mulaioperasi,
                rt.selesaioperasi,
                rt.selesaioperasi - rt.mulaioperasi AS durasi_operasi
            FROM rencanaoperasi_t rt
            JOIN pasien_m pm ON pm.pasien_id = rt.pasien_id
            JOIN pendaftaran_t pt ON pt.pendaftaran_id = rt.pendaftaran_id
            JOIN carabayar_m cm ON cm.carabayar_id = pt.carabayar_id
            LEFT JOIN tindakanpelayanan_t tt ON tt.tindakanpelayanan_id = rt.tindakanpelayanan_id
            LEFT JOIN daftartindakan_m dm ON dm.daftartindakan_id = tt.daftartindakan_id
            WHERE {$whereConditions}
            ORDER BY tt.tgl_tindakan DESC, pt.tgl_pendaftaran DESC
        ";

        return $query;
    }

    public function buildWhereConditions()
    {
        $this->params = [];
        $where = [];

        if (!empty($this->dateFrom) && !empty($this->dateTo)) {
            $where[] = "DATE(tt.tgl_tindakan) BETWEEN :datefrom AND :dateto";
            $this->params[':datefrom'] = $this->dateFrom;
            $this->params[':dateto']   = $this->dateTo;
        } elseif (!empty($this->dateFrom)) {
            $where[] = "DATE(tt.tgl_tindakan) >= :datefrom";
            $this->params[':datefrom'] = $this->dateFrom;
        } elseif (!empty($this->dateTo)) {
            $where[] = "DATE(tt.tgl_tindakan) <= :dateto";
            $this->params[':dateto'] = $this->dateTo;
        } else {
            $where[] = "1=1";
        }

        if (!empty($this->tindakanId)) {
            $where[] = "dm.daftartindakan_id = :tindakanid";
            $this->params[':tindakanid'] = $this->tindakanId;
        }

        return implode(' AND ', $where);
    }

    public function countQuery()
    {
        $whereConditions = $this->buildWhereConditions();

        $query = "
            SELECT COUNT(*)
            FROM rencanaoperasi_t rt
            JOIN pasien_m pm ON pm.pasien_id = rt.pasien_id
            JOIN pendaftaran_t pt ON pt.pendaftaran_id = rt.pendaftaran_id
            JOIN carabayar_m cm ON cm.carabayar_id = pt.carabayar_id
            LEFT JOIN tindakanpelayanan_t tt ON tt.tindakanpelayanan_id = rt.tindakanpelayanan_id
            LEFT JOIN daftartindakan_m dm ON dm.daftartindakan_id = tt.daftartindakan_id
            WHERE {$whereConditions}
        ";

        $command = Yii::$app->db->createCommand($query);
        foreach ($this->params as $param => $val) {
            $command->bindValue($param, $val);
        }

        return $command->queryScalar();
    }

    public function actionExport()
    {
        $this->setupSearch();

        $dataProvider = new SqlDataProvider([
            'sql'        => $this->statuscari ? $this->baseQuery() : $this->queryKosong(),
            'params'     => $this->params,
            'pagination' => false,
        ]);

        $models = $dataProvider->getModels();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Durante Operasi');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $periodeText = (!empty($this->dateFrom) && !empty($this->dateTo))
            ? date('d-m-Y', strtotime($this->dateFrom)) . ' s/d ' . date('d-m-Y', strtotime($this->dateTo))
            : '-';
        $sheet->setCellValue('A3', 'Periode : ' . $periodeText);
        $sheet->getStyle('A3')->getFont()->setSize(11);

        if (!empty($this->tindakanId)) {
            $namaTindakan = Yii::$app->db->createCommand("
                SELECT CONCAT(COALESCE(CONCAT('[', daftartindakan_kode, '] '), ''), daftartindakan_nama) 
                FROM daftartindakan_m 
                WHERE daftartindakan_id = :id
            ")->bindValue(':id', $this->tindakanId)->queryScalar();
            if ($namaTindakan) {
                $sheet->setCellValue('A4', 'Tindakan Operasi : ' . $namaTindakan);
                $sheet->getStyle('A4')->getFont()->setSize(11)->setBold(true);
            }
        }

        $headers = [
            'A5' => 'No',
            'B5' => 'No Rekam Medik',
            'C5' => 'Nama Pasien',
            'D5' => 'No Pendaftaran',
            'E5' => 'Tgl Pendaftaran',
            'F5' => 'Cara Bayar',
            'G5' => 'Kode Tindakan',
            'H5' => 'Nama Tindakan',
            'I5' => 'Tgl Tindakan',
            'J5' => 'Qty',
            'K5' => 'Tarif Satuan',
            'L5' => 'Tarif Tindakan',
            'M5' => 'Mulai Operasi',
            'N5' => 'Selesai Operasi',
            'O5' => 'Durasi Operasi',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '002D72']],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            ],
        ];
        $sheet->getStyle('A5:O5')->applyFromArray($headerStyle);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(35);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(10);
        $sheet->getColumnDimension('K')->setWidth(18);
        $sheet->getColumnDimension('L')->setWidth(18);
        $sheet->getColumnDimension('M')->setWidth(20);
        $sheet->getColumnDimension('N')->setWidth(20);
        $sheet->getColumnDimension('O')->setWidth(18);

        $row = 6;
        $i = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['no_rekam_medik'] ?? '');
            $sheet->setCellValue('C' . $row, $model['nama_pasien'] ?? '');
            $sheet->setCellValue('D' . $row, $model['no_pendaftaran'] ?? '');
            $sheet->setCellValue('E' . $row, !empty($model['tgl_pendaftaran']) ? date('d-m-Y H:i', strtotime($model['tgl_pendaftaran'])) : '');
            $sheet->setCellValue('F' . $row, $model['carabayar_nama'] ?? '');
            $sheet->setCellValue('G' . $row, $model['daftartindakan_kode'] ?? '');
            $sheet->setCellValue('H' . $row, $model['daftartindakan_nama'] ?? '');
            $sheet->setCellValue('I' . $row, !empty($model['tgl_tindakan']) ? date('d-m-Y H:i', strtotime($model['tgl_tindakan'])) : '');
            $sheet->setCellValue('J' . $row, (int)($model['qty_tindakan'] ?? 0));
            $sheet->setCellValue('K' . $row, (float)($model['tarif_satuan'] ?? 0));
            $sheet->setCellValue('L' . $row, (float)($model['tarif_tindakan'] ?? 0));
            $sheet->setCellValue('M' . $row, !empty($model['mulaioperasi']) ? date('d-m-Y H:i', strtotime($model['mulaioperasi'])) : '');
            $sheet->setCellValue('N' . $row, !empty($model['selesaioperasi']) ? date('d-m-Y H:i', strtotime($model['selesaioperasi'])) : '');
            $sheet->setCellValue('O' . $row, $model['durasi_operasi'] ?? '');

            $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            $row++;
            $i++;
        }

        if ($row > 6) {
            $sheet->getStyle('A5:O' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'laporan-durante-operasi.xlsx';
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
