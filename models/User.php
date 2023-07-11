<?php

namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord implements \yii\web\IdentityInterface
{

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::find()
            ->where(['id' => (int) $id])
            ->one();
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'default', 'value' => null],
            [['username', 'password_hash'], 'required'],
            [['username'], 'unique', 'targetAttribute' => 'username']
        ];
    }

    private static function hashPassword(string $password): string
    {
        $salt = \Yii::$app->params['passwordSalt'];
        return hash('sha256', $salt.'_'.$password);
    }

    public function beforeSave($insert)
    {
        if($insert || array_key_exists('password_hash', $this->dirtyAttributes)){
            $this->password_hash = self::hashPassword($this->password_hash);
        }
        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::find()
            ->where(['access_token' => (string) $token])
            ->one();
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return null|array|ActiveRecord
     */
    public static function findByUsername($username)
    {
        return static::find()
            ->where(['username' => (string) $username])
            ->one();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->password_hash;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->password_hash === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password_hash === self::hashPassword($password);
    }
}
