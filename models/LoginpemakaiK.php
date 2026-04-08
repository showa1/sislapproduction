<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
class LoginpemakaiK extends ActiveRecord
{
    /**
     * {@inheritdoc}
     * Nama tabel yang terkait dengan model ini
     */
    public static function tableName()
    {
        return 'loginpemakai_k';
    }

}