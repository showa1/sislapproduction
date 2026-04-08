<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class LoginForm extends Model
{
    public $nama_pemakai;
    public $katakunci_pemakai;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['nama_pemakai', 'katakunci_pemakai'], 'required'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->katakunci_pemakai)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            if ($user !== null) {
                $password = $this->cekPassword($user);
                if ($password) {
                    return Yii::$app->user->login($user, 0);
                }
            }  

            $this->addError('username or password', 'Username/Password Tidak ditemukan.');
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->nama_pemakai);
        }

        return $this->_user;
    }

    public function cekPassword($user)
    {
        return $user->validatePassword($this->katakunci_pemakai);
    }

}
