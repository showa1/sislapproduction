<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class KategoritindakanM extends ActiveRecord
{
    /**
     * {@inheritdoc}
     * Nama tabel yang terkait dengan model ini
     */
    public static function tableName()
    {
        return 'kategoritindakan_m';
    }

    public static function dropdown()
    {
        return self::find()
                    ->select(['kategoritindakan_id', 'kategoritindakan_nama'])
                    ->where(['kategoritindakan_aktif' => true])
                    ->orderBy(['kategoritindakan_nama' => SORT_ASC])
                ->all();
    }

}