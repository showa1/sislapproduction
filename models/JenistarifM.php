<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class JenistarifM extends ActiveRecord
{
    /**
     * {@inheritdoc}
     * Nama tabel yang terkait dengan model ini
     */
    public static function tableName()
    {
        return 'jenistarif_m';
    }

    public static function dropdown()
    {
        return self::find()
                    ->select(['jenistarif_id', 'jenistarif_nama'])
                    ->where(['jenistarif_aktif' => true])
                ->all();
    }

}