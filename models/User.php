<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    // Konstanta status aktif dan non-aktif
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public $password;  // untuk form password yang belum di-hash

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'loginpemakai_k';  // Nama tabel di database
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama_pemakai', 'katakunci_pemakai'], 'required'],
        ];
    }

    /**
     * Validasi password custom mencocokan dengan hash custom yii 1
     */
    public function validatePassword($password)
    {
        // Ambil nama pengguna (atau atribut lain yang relevan)
        $namaPemakai = $this->nama_pemakai;

        // Ambil secret key dari params
        $secretKey = Yii::$app->params['seckey'];

        // Buat hash menggunakan algoritma yang sama
        $hashedValue = hash_hmac("sha256", $password . "&" . $namaPemakai, $secretKey, true);

        // Bandingkan dengan hash yang disimpan di database (decode dari Base64)
        return password_verify($hashedValue, base64_decode($this->katakunci_pemakai));
    }

    /**
     * Set password hash
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;  // Misalnya token autentikasi
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return null;  // Kunci autentikasi jika ada
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return null;
    }

    /**
     * Mencari User berdasarkan username
     */
    public static function findByUsername($username)
    {
        return static::findOne(['nama_pemakai' => $username]);
    }

    /**
     * Memeriksa apakah pengguna aktif
     */
    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }
}
