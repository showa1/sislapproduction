<?php

namespace app\modules\keuangan\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use Yii;
use DateTime;
use yii\helpers\ArrayHelper;
use app\models\PegawaiM;
use app\models\RuanganM;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class JasaDokterPenunjangController extends BaseController
{
    public $dateFrom, $dateTo;
    public $params = [];
    public $statuscari;
    public $ruangan_id, $carabayar_id, $pegawai_id;

    /**
     * Menampilkan Laporan Jasa Dokter (Penunjang)
     */
    public function actionIndex()
    {
        $this->setupSearch();

        $dropdownselect = [
            'start' => Yii::$app->request->get('date_from', date('01-m-Y')),
            'to' => Yii::$app->request->get('date_to', date('d-m-Y')),
            'ruangan_id' => $this->ruangan_id,
            'carabayar_id' => $this->carabayar_id,
            'pegawai_id' => $this->pegawai_id,
        ];

        return $this->render('index', [
            'dataProvider' => $this->dataprovider(),
            'dropdownselect' => $dropdownselect,
            'stats' => $this->getSummaryStats(),
            'dataRuangan' => ArrayHelper::map(RuanganM::find()->where(['ruangan_aktif' => true])->orderBy('ruangan_nama')->all(), 'ruangan_id', 'ruangan_nama'),
            'dataCaraBayar' => ArrayHelper::map(Yii::$app->db->createCommand("SELECT carabayar_id, carabayar_nama FROM carabayar_m WHERE carabayar_aktif = true ORDER BY carabayar_nama")->queryAll(), 'carabayar_id', 'carabayar_nama'),
            'dataPegawai' => ArrayHelper::map(Yii::$app->db->createCommand("
                SELECT DISTINCT pg.pegawai_id, pg.nama_pegawai 
                FROM pendaftaran_t pt
                JOIN pegawai_m pg ON pg.pegawai_id = pt.pegawai_id
                WHERE pg.pegawai_aktif = true
                ORDER BY pg.nama_pegawai
            ")->queryAll(), 'pegawai_id', 'nama_pegawai'),
            'statuscari' => $this->statuscari,
        ]);
    }

    /**
     * Export ke Excel
     */
    public function actionExport()
    {
        $this->setupSearch();
        $sql = $this->baseQuery();
        $data = Yii::$app->db->createCommand($sql, $this->params)->queryAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jasa Dokter Penunjang');

        // Header Style
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '002D72']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];

        // Headers
        $headers = ['No', 'No. Pendaftaran', 'Tgl Pendaftaran', 'Ruangan', 'Pasien', 'No RM', 'Dokter', 'Total Penunjang', 'Total Resep', 'Detail Penunjang', 'Cara Bayar', 'No. Closing', 'Tgl Closing'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }
        $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

        // Data Rows
        $row = 2;
        foreach ($data as $i => $val) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $val['no_pendaftaran']);
            $sheet->setCellValue('C' . $row, date('d/m/Y H:i', strtotime($val['tgl_pendaftaran'])));
            $sheet->setCellValue('D' . $row, $val['ruangan_pendaftaran']);
            $sheet->setCellValue('E' . $row, $val['nama_pasien']);
            $sheet->setCellValue('F' . $row, $val['no_rekam_medik']);
            $sheet->setCellValue('G' . $row, $val['nama_pegawai']);
            $sheet->setCellValue('H' . $row, $val['total_penunjang']);
            $sheet->setCellValue('I' . $row, $val['total_resep']);
            
            // Format Detail Penunjang
            $detailsArr = !empty($val['detail_penunjang']) ? json_decode($val['detail_penunjang'], true) : [];
            $detailStr = "";
            if (!empty($detailsArr)) {
                foreach ($detailsArr as $unit => $tarif) {
                    $detailStr .= "{$unit}: " . number_format($tarif, 0, ',', '.') . "\n";
                }
            }
            $sheet->setCellValue('J' . $row, trim($detailStr));
            $sheet->getStyle('J' . $row)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('K' . $row, $val['carabayar']);
            $sheet->setCellValue('L' . $row, $val['closingkasir_no']);
            $sheet->setCellValue('M' . $row, $val['tglclosingkasir'] ? date('d/m/Y H:i', strtotime($val['tglclosingkasir'])) : '-');

            // Formats
            $sheet->getStyle('H' . $row . ':I' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('A' . $row . ':M' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            
            $row++;
        }

        // Output
        $filename = 'Jasa_Dokter_Penunjang_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    protected function setupSearch()
    {
        $this->dateFrom = Yii::$app->request->get('date_from', date('01-m-Y'));
        $this->dateTo = Yii::$app->request->get('date_to', date('d-m-Y'));

        $this->ruangan_id = Yii::$app->request->get('ruangan_id');
        $this->carabayar_id = Yii::$app->request->get('carabayar_id', 1); // Default PRIBADI
        $this->pegawai_id = Yii::$app->request->get('pegawai_id');

        $this->statuscari = !empty(Yii::$app->request->get('cari'));
        
        // Convert to Y-m-d for query
        $df = DateTime::createFromFormat('d-m-Y', $this->dateFrom);
        $this->dateFrom = $df ? $df->format('Y-m-d') : date('Y-m-01');
        
        $dt = DateTime::createFromFormat('d-m-Y', $this->dateTo);
        $this->dateTo = $dt ? $dt->format('Y-m-d') : date('Y-m-d');
    }

    protected function dataprovider()
    {
        $sql = $this->baseQuery();
        return new SqlDataProvider([
            'sql' => $sql,
            'params' => $this->params,
            'totalCount' => $this->countQuery($sql),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
    }

    protected function baseQuery()
    {
        // SQL provided by user
        $query = "
            WITH 
            penunjang_detail AS (
                SELECT 
                    pmp.pendaftaran_id, 
                    r.ruangan_nama, 
                    SUM(tp.tarif_tindakan) AS total_per_ruangan 
                FROM tindakanpelayanan_t tp 
                JOIN pasienmasukpenunjang_t pmp 
                    ON tp.pasienmasukpenunjang_id = pmp.pasienmasukpenunjang_id 
                JOIN ruangan_m r 
                    ON pmp.ruangan_id = r.ruangan_id 
                WHERE 
                    tp.pasienmasukpenunjang_id IS NOT NULL
                    AND pmp.kelaspelayanan_id = 6
                GROUP BY 
                    pmp.pendaftaran_id, 
                    r.ruangan_nama
            ), 

            penunjang AS (
                SELECT 
                    pendaftaran_id, 
                    SUM(total_per_ruangan) AS total_tarif, 
                    JSONB_OBJECT_AGG(ruangan_nama, total_per_ruangan) AS detail_penunjang 
                FROM penunjang_detail 
                GROUP BY pendaftaran_id
            ), 

            resep AS (
                SELECT 
                    pendaftaran_id, 
                    SUM(totalhargajual) AS total_resep 
                FROM penjualanresep_t 
                WHERE kelaspelayanan_id = 6
                GROUP BY pendaftaran_id
            ), 

            base AS (
                SELECT 
                    COALESCE(p.pendaftaran_id, r.pendaftaran_id) AS pendaftaran_id, 
                    COALESCE(p.total_tarif, 0) AS total_tarif, 
                    p.detail_penunjang, 
                    COALESCE(r.total_resep, 0) AS total_resep 
                FROM penunjang p 
                FULL JOIN resep r 
                    ON p.pendaftaran_id = r.pendaftaran_id 
                WHERE 
                    p.pendaftaran_id IS NOT NULL 
                    OR COALESCE(r.total_resep, 0) > 300000
            ) 

            SELECT 
                b.pendaftaran_id, 
                pt.no_pendaftaran, 
                pt.tgl_pendaftaran, 
                rm.ruangan_nama AS ruangan_pendaftaran, 
                ps.no_rekam_medik, 
                ps.nama_pasien, 
                pg.nama_pegawai,

                b.total_tarif AS total_penunjang, 
                b.total_resep, 
                b.detail_penunjang,

                cb.carabayar_nama AS carabayar,

                ck.closingkasir_no,
                ck.tglclosingkasir,
                ck.create_time

            FROM base b 

            JOIN pendaftaran_t pt ON b.pendaftaran_id = pt.pendaftaran_id 
            LEFT JOIN ruangan_m rm ON pt.ruangan_id = rm.ruangan_id
            LEFT JOIN pasien_m ps ON pt.pasien_id = ps.pasien_id 
            LEFT JOIN pegawai_m pg ON pt.pegawai_id = pg.pegawai_id
            LEFT JOIN pembayaranpelayanan_t pp ON pt.pendaftaran_id = pp.pendaftaran_id
            LEFT JOIN tandabuktibayar_t ttb ON pp.tandabuktibayar_id = ttb.tandabuktibayar_id
            LEFT JOIN closingkasir_t ck ON ttb.closingkasir_id = ck.closingkasir_id
            LEFT JOIN carabayar_m cb ON pp.carabayar_id = cb.carabayar_id
        ";

        $filters = [];
        $this->params = [];

        // Apply Date Range Filter (Using create_time from closingkasir_t)
        $filters[] = "ck.create_time >= :date_from";
        $filters[] = "ck.create_time < :date_to_plus";
        $this->params[':date_from'] = $this->dateFrom . ' 00:00:00';
        $this->params[':date_to_plus'] = date('Y-m-d', strtotime($this->dateTo . ' +1 day')) . ' 00:00:00';

        // Apply Cara Bayar Filter
        if (!empty($this->carabayar_id)) {
            $filters[] = "cb.carabayar_id = :carabayar_id";
            $this->params[':carabayar_id'] = $this->carabayar_id;
        } else {
            // Default to PRIBADI if explicitly requested by user in query (carabayar_id = 1)
            // But we allow "All" if user clears the filter. 
            // For now, keeping the filter dynamic is better.
        }

        // Apply Ruangan Filter
        if (!empty($this->ruangan_id)) {
            $filters[] = "pt.ruangan_id = :ruangan_id";
            $this->params[':ruangan_id'] = $this->ruangan_id;
        }

        // Apply Dokter Filter
        if (!empty($this->pegawai_id)) {
            $filters[] = "pt.pegawai_id = :pegawai_id";
            $this->params[':pegawai_id'] = $this->pegawai_id;
        }

        if (count($filters) > 0) {
            $query .= " WHERE " . implode(" AND ", $filters);
        }

        $query .= " ORDER BY pt.tgl_pendaftaran DESC";

        if (!$this->statuscari) {
            $query .= " LIMIT 0";
        }

        return $query;
    }

    protected function countQuery($sql)
    {
        $countSql = "SELECT COUNT(*) FROM ($sql) AS count_table";
        return Yii::$app->db->createCommand($countSql, $this->params)->queryScalar();
    }

    protected function getSummaryStats()
    {
        $sql = $this->baseQuery();
        $summarySql = "
            SELECT 
                COALESCE(SUM(total_penunjang), 0) as total_penunjang,
                COALESCE(SUM(total_resep), 0) as total_resep,
                COUNT(pendaftaran_id) as total_pasien
            FROM ($sql) AS stats_table
        ";
        return Yii::$app->db->createCommand($summarySql, $this->params)->queryOne();
    }
}
