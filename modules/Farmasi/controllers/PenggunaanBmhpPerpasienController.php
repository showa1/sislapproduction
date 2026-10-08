<?php

namespace app\modules\farmasi\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use DateTime;

class PenggunaanBmhpPerpasienController extends BaseController
{

    public $dateFrom, $dateTo, $totalCount;

    public $params = [];

    public $statuscari;

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $this->setupSearch();

        $dropdownselect = [
            'start' => Yii::$app->request->get('date_from'),
            'to'    => Yii::$app->request->get('date_to'),
        ];

        return $this->render('index', [
            'dataProvider'   => $this->dataprovider(),
            'dropdownselect' => $dropdownselect,
        ]);
    }

    public function setupSearch()
    {
        $this->dateFrom = Yii::$app->request->get('date_from');
        $this->dateTo   = Yii::$app->request->get('date_to');

        if (!empty($this->dateFrom)) {
            $this->dateFrom = DateTime::createFromFormat('d-m-Y', $this->dateFrom)->format('Y-m-d');
        }

        if (!empty($this->dateTo)) {
            $this->dateTo = DateTime::createFromFormat('d-m-Y', $this->dateTo)->format('Y-m-d');
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
                'pageSize' => 10,
            ],
        ]);
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
        $sheet       = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Penggunaan BMHP / Pasien');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A3', 'Periode : ' . $this->dateFrom . ' s/d ' . $this->dateTo);
        $sheet->getStyle('A3')->getFont()->setSize(11);

        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'No Pendaftaran');
        $sheet->setCellValue('C5', 'Tgl Pendaftaran');
        $sheet->setCellValue('D5', 'No Rekam Medik');
        $sheet->setCellValue('E5', 'Nama Pasien');
        $sheet->setCellValue('F5', 'Cara Bayar');
        $sheet->setCellValue('G5', 'Diagnosa');
        $sheet->setCellValue('H5', 'BMHP yang Digunakan');

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '002D72']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ];
        $sheet->getStyle('A5:H5')->applyFromArray($headerStyle);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(28);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(40);
        $sheet->getColumnDimension('H')->setWidth(55);

        $row = 6;
        $i   = 1;
        foreach ($models as $model) {
            $sheet->setCellValue('A' . $row, $i);
            $sheet->setCellValue('B' . $row, $model['no_pendaftaran'] ?? '');
            $sheet->setCellValue('C' . $row, $model['tgl_pendaftaran'] ?? '');
            $sheet->setCellValue('D' . $row, $model['no_rekam_medik'] ?? '');
            $sheet->setCellValue('E' . $row, $model['nama_pasien'] ?? '');
            $sheet->setCellValue('F' . $row, $model['carabayar_nama'] ?? '');
            $sheet->setCellValue('G' . $row, $model['diagnosa'] ?? '-');
            $sheet->setCellValue('H' . $row, $model['obat'] ?? '-');

            $sheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('H' . $row)->getAlignment()->setWrapText(true);

            $row++;
            $i++;
        }

        $sheet->getStyle('A5:H' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $writer   = new Xlsx($spreadsheet);
        $fileName = 'laporan-penggunaan-bmhp-per-pasien.xlsx';
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

    public function queryKosong()
    {
        return "SELECT pendaftaran_id FROM pendaftaran_t WHERE 1=0";
    }

    public function baseQuery()
    {
        // Set params untuk SqlDataProvider
        $this->queryFilter();

        $query = "
            SELECT
                pt.pendaftaran_id,
                pt.tgl_pendaftaran,
                pt.no_pendaftaran,
                pm.no_rekam_medik,
                pm.nama_pasien,
                cm.carabayar_nama,
                d.diagnosa,
                o.obat,
                o.total_harga_netto,
                o.total_harga_jual

            FROM pendaftaran_t pt

            JOIN pasien_m pm
                ON pm.pasien_id = pt.pasien_id

            JOIN carabayar_m cm
                ON cm.carabayar_id = pt.carabayar_id

            -- Subquery Diagnosa
            LEFT JOIN (
                SELECT
                    pmt.pendaftaran_id,
                    STRING_AGG(
                        DISTINCT dm.diagnosa_kode || ' - ' || dm.diagnosa_nama,
                        CHR(10)
                    ) AS diagnosa
                FROM pasienmorbiditas_t pmt
                LEFT JOIN diagnosa_m dm
                    ON dm.diagnosa_id = pmt.diagnosa_id
                GROUP BY pmt.pendaftaran_id
            ) d ON d.pendaftaran_id = pt.pendaftaran_id

            -- Subquery Obat/BHP (filter tanggal di dalam)
            JOIN (
                SELECT
                    opt.pendaftaran_id,
                    STRING_AGG(
                        CONCAT(
                            jm.jenisobatalkes_nama,
                            ' - ',
                            om.obatalkes_kode,
                            ' - ',
                            om.obatalkes_nama,
                            ' (Qty: ', opt.qty_oa,
                            ', Netto: ', ROUND((opt.harganetto_oa * opt.qty_oa)::numeric, 2),
                            ', Harga Satuan: ', ROUND(opt.hargasatuan_oa::numeric, 2),
                            ', Jual: ', ROUND((opt.hargajual_oa * opt.qty_oa)::numeric, 2),
                            ')'
                        ),
                        CHR(10)
                    ) AS obat,
                    SUM(COALESCE(opt.harganetto_oa, 0) * COALESCE(opt.qty_oa, 0))  AS total_harga_netto,
                    SUM(COALESCE(opt.hargajual_oa,  0) * COALESCE(opt.qty_oa, 0))  AS total_harga_jual
                FROM obatalkespasien_t opt
                JOIN obatalkes_m om
                    ON om.obatalkes_id = opt.obatalkes_id
                LEFT JOIN jenisobatalkes_m jm
                    ON jm.jenisobatalkes_id = om.jenisobatalkes_id
                WHERE jm.jenisobatalkes_nama = 'BHP'
                  AND DATE(opt.tglpelayanan) BETWEEN :datefrom AND :dateto
                GROUP BY opt.pendaftaran_id
            ) o ON o.pendaftaran_id = pt.pendaftaran_id

            ORDER BY pt.tgl_pendaftaran ASC
        ";

        return $query;
    }

    public function queryFilter()
    {
        $this->params = [
            ':datefrom' => $this->dateFrom,
            ':dateto'   => $this->dateTo,
        ];
    }

    public function countQuery()
    {
        // Count cukup hitung berapa pendaftaran_id yang masuk ke subquery obat
        $query = "
            SELECT COUNT(DISTINCT pt.pendaftaran_id)
            FROM pendaftaran_t pt
            JOIN (
                SELECT DISTINCT opt.pendaftaran_id
                FROM obatalkespasien_t opt
                JOIN obatalkes_m om
                    ON om.obatalkes_id = opt.obatalkes_id
                LEFT JOIN jenisobatalkes_m jm
                    ON jm.jenisobatalkes_id = om.jenisobatalkes_id
                WHERE jm.jenisobatalkes_nama = 'BHP'
                  AND DATE(opt.tglpelayanan) BETWEEN :datefrom AND :dateto
            ) o ON o.pendaftaran_id = pt.pendaftaran_id
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindValue(':datefrom', $this->dateFrom);
        $command->bindValue(':dateto', $this->dateTo);

        return $command->queryScalar();
    }
}
