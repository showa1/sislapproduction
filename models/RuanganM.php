<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class RuanganM extends ActiveRecord
{
    /**
     * {@inheritdoc}
     * Nama tabel yang terkait dengan model ini
     */
    public static function tableName()
    {
        return 'ruangan_m';
    }

    public static function dropdown()
    {
        return self::find()
                    ->select(['ruangan_id', 'ruangan_nama'])
                    ->where(['ruangan_aktif' => true])
                ->all();
    }

}