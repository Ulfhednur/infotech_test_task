<?php

namespace app\models;

use app\services\SmsPilotService;
use yii\db\ActiveRecord;

class Subscription extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public function formName()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'subscription';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['author_id', 'phone_num'], 'required'],
            [['author_id', 'phone_num'], 'integer'],
            [['author_id', 'phone_num'], 'unique', 'targetAttribute' => ['author_id', 'phone_num']],
            [['phone_num'],
                function($attribute, $params, $validator, $current){
                    return SmsPilotService::checkPhoneNum($current);
                }
            ]
        ];
    }

    public function beforeValidate()
    {
        $this->phone_num = (int) preg_replace("/[^0-9]/", '', $this->phone_num);
        return parent::beforeValidate();
    }

    public function getAuthors()
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }
}
