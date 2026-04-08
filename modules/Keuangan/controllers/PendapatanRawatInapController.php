<?php

namespace app\modules\keuangan\controllers;

use app\controllers\BaseController;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yii;
use DateTime;

class PendapatanRawatInapController extends BaseController
{
    public $dateFrom, $dateTo;
    public $statuscari;
    public $filterRuangan;
    public $filterCaraBayar;

    public function actionIndex()
    {
        $this->setupSearch();

        $dropdownselect = [
            'start' => Yii::$app->request->get('date_from'),
            'to'    => Yii::$app->request->get('date_to'),
        ];

        $summaryCards = $summaryTable = $chartRevenue = $chartCaraBayar = $chartTrend = [];

        if ($this->statuscari) {
            $summaryCards   = $this->getSummaryCards();
            $summaryTable   = $this->getSummaryTable();
            $chartRevenue   = $this->getChartRevenue();
            $chartCaraBayar = $this->getChartCaraBayar();
            $chartTrend     = $this->getChartTrend();
        }

        return $this->render('index', [
            'dataProvider'    => $this->dataprovider(),
            'dropdownselect'  => $dropdownselect,
            'ruanganList'     => $this->getRuanganList(),
            'carabayarList'   => $this->getCaraBayarList(),
            'summaryCards'    => $summaryCards,
            'summaryTable'    => $summaryTable,
            'chartRevenue'    => $chartRevenue,
            'chartCaraBayar'  => $chartCaraBayar,
            'chartTrend'      => $chartTrend,
            'statuscari'      => $this->statuscari,
            'filterRuangan'   => $this->filterRuangan,
            'filterCaraBayar' => $this->filterCaraBayar,
        ]);
    }

    public function setupSearch()
    {
        $this->dateFrom       = Yii::$app->request->get('date_from');
        $this->dateTo         = Yii::$app->request->get('date_to');
        $this->filterRuangan  = Yii::$app->request->get('ruangan', []);
        if (!is_array($this->filterRuangan)) {
            $this->filterRuangan = $this->filterRuangan ? [$this->filterRuangan] : [];
        }
        $this->filterRuangan = array_filter($this->filterRuangan);
        $this->filterCaraBayar = Yii::$app->request->get('cara_bayar', '');

        if (!empty($this->dateFrom)) {
            $this->dateFrom = DateTime::createFromFormat('d-m-Y', $this->dateFrom)->format('Y-m-d');
        }
        if (!empty($this->dateTo)) {
            $this->dateTo = DateTime::createFromFormat('d-m-Y', $this->dateTo)->format('Y-m-d');
        }
        $this->statuscari = !empty(Yii::$app->request->get('cari'));
    }

    private function getBaseJoin(): string
    {
        return "
            FROM pembayaranpelayanan_t ppt
            JOIN pasien_m pm ON pm.pasien_id = ppt.pasien_id
            JOIN pendaftaran_t pt ON pt.pendaftaran_id = ppt.pendaftaran_id
            JOIN carabayar_m cm ON cm.carabayar_id = ppt.carabayar_id
            JOIN tandabuktibayar_t tbt ON tbt.tandabuktibayar_id = ppt.tandabuktibayar_id
            JOIN closingkasir_t ct ON ct.closingkasir_id = tbt.closingkasir_id
            JOIN ruangan_m rm ON rm.ruangan_id = pt.ruangan_id
            JOIN instalasi_m im ON im.instalasi_id = rm.instalasi_id
            JOIN pegawai_m pgm ON pgm.pegawai_id = pt.pegawai_id
            LEFT JOIN pasienadmisi_t pat ON pat.pasienadmisi_id = ppt.pasienadmisi_id
            LEFT JOIN ruangan_m rm2 ON rm2.ruangan_id = pat.ruangan_id
            LEFT JOIN instalasi_m im2 ON im2.instalasi_id = rm2.instalasi_id
        ";
    }

    private function buildWhere(): array
    {
        $where  = "WHERE DATE(ct.tglclosingkasir) BETWEEN :datefrom AND :dateto AND im2.instalasi_id = '4'";
        $params = [':datefrom' => $this->dateFrom, ':dateto' => $this->dateTo];

        if (!empty($this->filterRuangan)) {
            $placeholders = [];
            foreach (array_values($this->filterRuangan) as $idx => $r) {
                $key = ':ruangan' . $idx;
                $placeholders[] = $key;
                $params[$key] = $r;
            }
            $where .= ' AND rm2.ruangan_nama IN (' . implode(',', $placeholders) . ')';
        }
        if (!empty($this->filterCaraBayar)) {
            $where .= " AND cm.carabayar_nama = :carabayar";
            $params[':carabayar'] = $this->filterCaraBayar;
        }
        return ['where' => $where, 'params' => $params];
    }

    public function dataprovider()
    {
        if (!$this->statuscari) {
            return new SqlDataProvider([
                'sql'        => "SELECT tariftindakan_id FROM tariftindakan_m WHERE 1=0",
                'params'     => [],
                'totalCount' => 0,
                'pagination' => ['pageSize' => 15],
            ]);
        }
        $filter = $this->buildWhere();
        return new SqlDataProvider([
            'sql'        => $this->baseQuery(),
            'params'     => $filter['params'],
            'totalCount' => $this->countQuery(),
            'pagination' => ['pageSize' => 15],
        ]);
    }

    public function baseQuery(): string
    {
        $filter = $this->buildWhere();
        return "
            SELECT
                pt.tgl_pendaftaran, pt.no_pendaftaran,
                ppt.tglpembayaran, ppt.nopembayaran,
                ct.tglclosingkasir, ct.closingkasir_no,
                im.instalasi_nama AS instalasi_asal,
                pgm.nama_pegawai,
                rm.ruangan_nama AS ruangan_asal,
                im2.instalasi_nama AS instalasi_bayar,
                rm2.ruangan_nama AS ruangan_bayar,
                COALESCE(rm2.ruangan_nama, rm.ruangan_nama) AS ruangan_akhir,
                pm.no_rekam_medik, pm.nama_pasien,
                cm.carabayar_nama,
                ppt.totalbiayatindakan,
                ppt.totalbiayaoa,
                ppt.totalbiayapelayanan AS totalbiaya,
                ppt.totalppnfarmasi,
                (ppt.totalbiayapelayanan + COALESCE(ppt.totalppnfarmasi, 0)) AS totalpelayanan
            " . $this->getBaseJoin() . "
            " . $filter['where'] . "
            ORDER BY ct.tglclosingkasir DESC
        ";
    }

    public function countQuery()
    {
        $filter = $this->buildWhere();
        $sql = "SELECT COUNT(*) " . $this->getBaseJoin() . " " . $filter['where'];
        return Yii::$app->db->createCommand($sql, $filter['params'])->queryScalar();
    }

    public function getSummaryCards(): array
    {
        $filter = $this->buildWhere();
        $sql = "
            SELECT
                SUM(ppt.totalbiayapelayanan + COALESCE(ppt.totalppnfarmasi, 0)) AS total_pendapatan,
                COUNT(DISTINCT ppt.pasien_id) AS total_pasien,
                COUNT(DISTINCT CASE WHEN LOWER(cm.carabayar_nama) LIKE '%bpjs%' OR LOWER(cm.carabayar_nama) LIKE '%jkn%' THEN ppt.pasien_id END) AS pasien_bpjs
            " . $this->getBaseJoin() . " " . $filter['where'];
        $row = Yii::$app->db->createCommand($sql, $filter['params'])->queryOne();

        $sqlTop = "
            SELECT COALESCE(rm2.ruangan_nama, rm.ruangan_nama) AS ruangan_akhir,
                   SUM(ppt.totalbiayapelayanan + COALESCE(ppt.totalppnfarmasi, 0)) AS total
            " . $this->getBaseJoin() . " " . $filter['where'] . "
            GROUP BY ruangan_akhir ORDER BY total DESC LIMIT 1
        ";
        $topRuangan = Yii::$app->db->createCommand($sqlTop, $filter['params'])->queryOne();

        $totalPendapatan = (float)($row['total_pendapatan'] ?? 0);
        $totalPasien     = (int)($row['total_pasien'] ?? 0);
        $pasienBpjs      = (int)($row['pasien_bpjs'] ?? 0);

        return [
            'total_pendapatan'    => $totalPendapatan,
            'total_pasien'        => $totalPasien,
            'arpp'                => $totalPasien > 0 ? ($totalPendapatan / $totalPasien) : 0,
            'ruangan_terlaris'    => $topRuangan['ruangan_akhir'] ?? '-',
            'ruangan_top_revenue' => (float)($topRuangan['total'] ?? 0),
            'pct_bpjs'            => $totalPasien > 0 ? round(($pasienBpjs / $totalPasien) * 100, 1) : 0,
            'pct_non_bpjs'        => $totalPasien > 0 ? round((($totalPasien - $pasienBpjs) / $totalPasien) * 100, 1) : 0,
        ];
    }

    public function getSummaryTable(): array
    {
        $filter = $this->buildWhere();
        $sql = "
            SELECT
                COALESCE(rm2.ruangan_nama, rm.ruangan_nama) AS ruangan_akhir,
                COUNT(DISTINCT ppt.pasien_id) AS jumlah_pasien,
                SUM(ppt.totalbiayatindakan) AS total_tindakan,
                SUM(ppt.totalbiayaoa) AS total_farmasi,
                SUM(ppt.totalbiayapelayanan + COALESCE(ppt.totalppnfarmasi, 0)) AS total_pendapatan
            " . $this->getBaseJoin() . " " . $filter['where'] . "
            GROUP BY ruangan_akhir ORDER BY total_pendapatan DESC
        ";
        return Yii::$app->db->createCommand($sql, $filter['params'])->queryAll();
    }

    public function getChartRevenue(): array
    {
        $filter = $this->buildWhere();
        $sql = "
            SELECT COALESCE(rm2.ruangan_nama, rm.ruangan_nama) AS label,
                   SUM(ppt.totalbiayapelayanan + COALESCE(ppt.totalppnfarmasi, 0)) AS value
            " . $this->getBaseJoin() . " " . $filter['where'] . "
            GROUP BY label ORDER BY value DESC LIMIT 10
        ";
        return Yii::$app->db->createCommand($sql, $filter['params'])->queryAll();
    }

    public function getChartCaraBayar(): array
    {
        $filter = $this->buildWhere();
        $sql = "
            SELECT cm.carabayar_nama AS label,
                   COUNT(DISTINCT ppt.pasien_id) AS jumlah,
                   SUM(ppt.totalbiayapelayanan + COALESCE(ppt.totalppnfarmasi, 0)) AS total
            " . $this->getBaseJoin() . " " . $filter['where'] . "
            GROUP BY cm.carabayar_nama ORDER BY total DESC
        ";
        return Yii::$app->db->createCommand($sql, $filter['params'])->queryAll();
    }

    public function getChartTrend(): array
    {
        $filter = $this->buildWhere();
        $sql = "
            SELECT TO_CHAR(ct.tglclosingkasir, 'YYYY-MM') AS label,
                   SUM(ppt.totalbiayapelayanan + COALESCE(ppt.totalppnfarmasi, 0)) AS value
            " . $this->getBaseJoin() . " " . $filter['where'] . "
            GROUP BY label ORDER BY label
        ";
        return Yii::$app->db->createCommand($sql, $filter['params'])->queryAll();
    }

    public function getRuanganList(): array
    {
        $sql = "
            SELECT DISTINCT rm2.ruangan_nama AS r
            " . $this->getBaseJoin() . "
            WHERE im2.instalasi_id = '4'
              AND rm2.ruangan_nama IS NOT NULL
            ORDER BY r
        ";
        $rows = Yii::$app->db->createCommand($sql)->queryAll();
        return array_column($rows, 'r', 'r');
    }

    public function getCaraBayarList(): array
    {
        $sql = "
            SELECT DISTINCT cm.carabayar_nama AS c
            " . $this->getBaseJoin() . "
            WHERE im2.instalasi_id = '4'
            ORDER BY c
        ";
        $rows = Yii::$app->db->createCommand($sql)->queryAll();
        return array_column($rows, 'c', 'c');
    }

    public function actionExport()
    {
        $this->setupSearch();
        $filter     = $this->buildWhere();
        $dataProvider = new SqlDataProvider([
            'sql'        => $this->statuscari ? $this->baseQuery() : "SELECT tariftindakan_id FROM tariftindakan_m WHERE 1=0",
            'params'     => $filter['params'],
            'pagination' => false,
        ]);
        $models = $dataProvider->getModels();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'RS Priscilla Medical Center');
        $sheet->setCellValue('A2', 'Laporan Pendapatan Rawat Inap');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);

        $headers = ['No','Tgl Pendaftaran','No Pendaftaran','Tgl Pembayaran','No Pembayaran',
            'Tgl Closing','No Closing','Ruangan Asal','Ruangan Akhir',
            'No RM','Nama Pasien','Cara Bayar',
            'Total Tindakan','Total Farmasi','Total Biaya','PPN Farmasi','Total Pelayanan'];
        $cols = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i].'4', $h);
        }
        $sheet->getStyle('A4:Q4')->getFont()->setBold(true);

        $row = 5; $i = 1;
        foreach ($models as $m) {
            $sheet->setCellValue('A'.$row, $i);
            $sheet->setCellValue('B'.$row, $m['tgl_pendaftaran']);
            $sheet->setCellValue('C'.$row, $m['no_pendaftaran']);
            $sheet->setCellValue('D'.$row, $m['tglpembayaran']);
            $sheet->setCellValue('E'.$row, $m['nopembayaran']);
            $sheet->setCellValue('F'.$row, $m['tglclosingkasir']);
            $sheet->setCellValue('G'.$row, $m['closingkasir_no']);
            $sheet->setCellValue('H'.$row, $m['ruangan_asal']);
            $sheet->setCellValue('I'.$row, $m['ruangan_akhir']);
            $sheet->setCellValue('J'.$row, $m['no_rekam_medik']);
            $sheet->setCellValue('K'.$row, $m['nama_pasien']);
            $sheet->setCellValue('L'.$row, $m['carabayar_nama']);
            $sheet->setCellValue('M'.$row, isset($m['totalbiayatindakan']) ? (float)$m['totalbiayatindakan'] : 0);
            $sheet->setCellValue('N'.$row, isset($m['totalbiayaoa']) ? (float)$m['totalbiayaoa'] : 0);
            $sheet->setCellValue('O'.$row, isset($m['totalbiaya']) ? (float)$m['totalbiaya'] : 0);
            $sheet->setCellValue('P'.$row, isset($m['totalppnfarmasi']) ? (float)$m['totalppnfarmasi'] : 0);
            $sheet->setCellValue('Q'.$row, isset($m['totalpelayanan']) ? (float)$m['totalpelayanan'] : 0);
            $row++; $i++;
        }
        $sheet->getStyle('M5:Q'.($row-1))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A4:Q'.($row-1))->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $fileName = 'lap-pendapatan-rawatinap.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tempFile);

        return Yii::$app->response->sendFile($tempFile, $fileName)->on(
            \yii\web\Response::EVENT_AFTER_SEND,
            function ($event) { unlink($event->data); },
            $tempFile
        );
    }
}
