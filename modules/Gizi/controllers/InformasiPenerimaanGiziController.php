<?php

namespace app\modules\Gizi\controllers;

use Yii;
use app\controllers\BaseController;
use yii\data\SqlDataProvider;

class InformasiPenerimaanGiziController extends BaseController
{
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $search = $request->get('search', '');
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo = $request->get('date_to', date('Y-m-t'));

        $sql = "
            SELECT 
                tt.nopenerimaanbahan, 
                tt.tglterimabahan,
                sm.supplier_nama,
                bm.bahanmakanan_id, 
                bm.namabahanmakanan,
                tbd.qty_terima,
                tbd.harganettobhn, 
                (tbd.qty_terima * tbd.harganettobhn) as harga_total
            FROM terimabahandetail_t tbd
            JOIN bahanmakanan_m bm ON bm.bahanmakanan_id = tbd.bahanmakanan_id
            JOIN terimabahanmakan_t tt ON tt.terimabahanmakan_id = tbd.terimabahanmakan_id
            JOIN supplier_m sm ON sm.supplier_id = tt.supplier_id
            WHERE 1=1
        ";

        $countSql = "
            SELECT COUNT(*) 
            FROM terimabahandetail_t tbd
            JOIN bahanmakanan_m bm ON bm.bahanmakanan_id = tbd.bahanmakanan_id
            JOIN terimabahanmakan_t tt ON tt.terimabahanmakan_id = tbd.terimabahanmakan_id
            JOIN supplier_m sm ON sm.supplier_id = tt.supplier_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($dateFrom) && !empty($dateTo)) {
            $sql .= " AND DATE(tt.tglterimabahan) BETWEEN :date_from AND :date_to";
            $countSql .= " AND DATE(tt.tglterimabahan) BETWEEN :date_from AND :date_to";
            $params[':date_from'] = $dateFrom;
            $params[':date_to'] = $dateTo;
        }

        if (!empty($search)) {
            $sql .= " AND (LOWER(sm.supplier_nama) LIKE LOWER(:search) OR LOWER(bm.namabahanmakanan) LIKE LOWER(:search) OR LOWER(tt.nopenerimaanbahan) LIKE LOWER(:search))";
            $countSql .= " AND (LOWER(sm.supplier_nama) LIKE LOWER(:search) OR LOWER(bm.namabahanmakanan) LIKE LOWER(:search) OR LOWER(tt.nopenerimaanbahan) LIKE LOWER(:search))";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY tt.tglterimabahan DESC";

        $export = $request->get('export', '0');

        if ($export === '1') {
            $data = Yii::$app->db->createCommand($sql, $params)->queryAll();

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $sheet->setCellValue('A1', 'Rumah Sakit Priscilla Medical Center');
            $sheet->setCellValue('A2', 'Laporan Informasi Penerimaan Gizi');
            $sheet->setCellValue('A3', 'Periode: ' . $dateFrom . ' s/d ' . $dateTo);
            $sheet->getStyle('A1:A3')->getFont()->setBold(true);

            $headers = ['No', 'No Penerimaan', 'Tanggal Terima', 'Supplier', 'Nama Bahan Makanan', 'Qty', 'Harga Netto', 'Total Harga'];
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . '5', $h);
                $col++;
            }
            $sheet->getStyle('A5:H5')->getFont()->setBold(true);

            $rowIdx = 6;
            $i = 1;
            foreach ($data as $r) {
                $sheet->setCellValue('A' . $rowIdx, $i);
                $sheet->setCellValueExplicit('B' . $rowIdx, $r['nopenerimaanbahan'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $rowIdx, $r['tglterimabahan'] ? date('d/m/Y H:i', strtotime($r['tglterimabahan'])) : '-');
                $sheet->setCellValue('D' . $rowIdx, $r['supplier_nama']);
                $sheet->setCellValue('E' . $rowIdx, $r['namabahanmakanan']);
                $sheet->setCellValue('F' . $rowIdx, (float)$r['qty_terima']);
                $sheet->setCellValue('G' . $rowIdx, (float)$r['harganettobhn']);
                $sheet->setCellValue('H' . $rowIdx, (float)$r['harga_total']);
                
                // Format accounting for prices
                $sheet->getStyle('G' . $rowIdx . ':H' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0.00');

                $rowIdx++;
                $i++;
            }

            $sheet->getStyle('A5:H'.($rowIdx - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            
            foreach (range('A', 'H') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = 'Penerimaan_Gizi.xlsx';
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
