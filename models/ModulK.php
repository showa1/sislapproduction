<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;

class ModulK extends ActiveRecord
{
    /**
     * {@inheritdoc}
     * Nama tabel yang terkait dengan model ini
     */
    public static function tableName()
    {
        return 'modul_k';
    }

    /**
     * mengambil modul yang aktif
     */
    public static function modulActive()
    {
        return (new Query())
            ->select(['modul_id', 'modul_namalainnya'])
            ->from('modul_k')
            ->where(['modul_aktif' => 1])->all();
    }

}