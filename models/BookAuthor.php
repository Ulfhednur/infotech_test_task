<?php

namespace app\models;

use yii\db\ActiveRecord;

class BookAuthor extends ActiveRecord
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
        return 'book_author';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['book_id', 'author_id'], 'required'],
            [['book_id', 'author_id'], 'integer'],
            [['book_id', 'author_id'], 'unique', 'targetAttribute' => ['book_id', 'author_id']],
        ];
    }

    public function getAuthors()
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }
}
