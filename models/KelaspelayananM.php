<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class KelaspelayananM extends ActiveRecord
{
    /**
     * {@inheritdoc}
     * Nama tabel yang terkait dengan model ini
     */
    public static function tableName()
    {
        return 'kelaspelayanan_m';
    }

    public static function dropdown()
    {
        return self::find()
                    ->select(['kelaspelayanan_id', 'kelaspelayanan_nama'])
                    ->where(['kelaspelayanan_aktif' => true])
                    ->orderBy(['kelaspelayanan_nama' => SORT_ASC])
                ->all();
    }

}