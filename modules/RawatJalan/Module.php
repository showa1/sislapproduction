<?php

namespace app\modules\rawatjalan;

use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\rawatjalan\controllers';

    public $menu = [
        ['label' => 'Tarif Rumah Sakit', 'url' => '/rawatjalan/tarif-rumah-sakit/index', 'icon' => 'bi bi-cash-stack'],
        ['label' => 'Waktu Pelayanan Dokter', 'url' => '/rawatjalan/waktu-pelayanan-dokter/index', 'icon' => 'bi bi-clock-history'],
        ['label' => 'Jumlah Pasien Per Dokter', 'url' => '/rawatjalan/jumlah-pasien-dokter/index', 'icon' => 'bi bi-people'],
        ['label' => 'Respontime Pasien Eksekutif', 'url' => '/rawatjalan/responsetime-pasien/index', 'icon' => 'bi bi-activity'],
        ['label' => 'Respontime Eksekutif Detail', 'url' => '/rawatjalan/responsetime-eksekutif/index', 'icon' => 'bi bi-file-earmark-medical'],
        ['label' => 'Respontime Pasien Eksekutif Per Poli', 'url' => '/rawatjalan/responsetime-pasien-per-poli/index', 'icon' => 'bi bi-diagram-3'],
        ['label' => 'Respontime Pasien Eksekutif Per Poli Dokter', 'url' => '/rawatjalan/responsetime-pasien-per-poli-dokter/index', 'icon' => 'bi bi-hospital'],
        ['label' => 'Respontime Pasien Eksekutif Per Dokter', 'url' => '/rawatjalan/responsetime-pasien-per-dokter/index', 'icon' => 'bi bi-person-badge'],
        ['label' => 'Data Kunjungan Eksekutif', 'url' => '/rawatjalan/kunjungan-eksekutif/index', 'icon' => 'bi bi-person-lines-fill'],
    ];
    
    public function init()
    {
        parent::init();
        // Tambahkan kode inisialisasi jika diperlukan
    }
}
