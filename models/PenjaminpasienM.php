<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class PenjaminpasienM extends ActiveRecord
{
    /**
     * {@inheritdoc}
     * Nama tabel yang terkait dengan model ini
     */
    public static function tableName()
    {
        return 'penjaminpasien_m';
    }

    public static function dropdown()
    {
        return self::find()
                    ->select(['penjamin_id', 'penjamin_nama'])
                    ->where(['penjamin_aktif' => true])
                ->all();
    }

}