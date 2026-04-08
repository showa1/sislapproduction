<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class PegawaiM extends ActiveRecord
{
    /**
     * {@inheritdoc}
     * Nama tabel yang terkait dengan model ini
     */
    public static function tableName()
    {
        return 'pegawai_m';
    }

    public static function dropdown()
    {
        return self::find()
                    ->select(['pegawai_id', 'nama_pegawai'])
                    ->where(['like', 'gelardepan', 'dr%', false])
                ->all();
    }

}