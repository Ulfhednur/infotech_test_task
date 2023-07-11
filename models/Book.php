<?php

namespace app\models;

use app\services\SmsPilotService;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\db\Transaction;
use yii\web\UploadedFile;


class Book extends ActiveRecord
{
    protected BookDescription|null $description;
    protected Transaction|null $transaction;
    protected array $authors;

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
        return 'books';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'default', 'value' => null],
            [['viewed'], 'default', 'value' => 0],
            [['created_by'], 'default', 'value' => \Yii::$app->user->identity->id],
            [['created_date'], 'default', 'value' => date('Y-m-d H:i:s')],
            [['isbn', 'year'], 'required'],
            [['isbn', 'year'], 'integer'],
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->description = new BookDescription();
        } else {
            $this->description = BookDescription::findOne(['id' => $this->id]);
        }
        $this->isbn = (int) preg_replace("/[^0-9]/", '', $this->isbn);
        $this->description->load(\Yii::$app->getRequest()->getBodyParams(), '');
        $this->description->isbn = $this->isbn;
        $this->description->imageFile = UploadedFile::getInstance($this->description, 'cover_image');

        if(!$this->description->validate()) {
            return false;
        }
        $this->transaction = \Yii::$app->db->beginTransaction();
        return parent::beforeSave($insert);
    }

    /**
     * тут я действую через модель
     * @throws \yii\db\Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        $saved = true;
        if ($insert) {
            $this->description->id = $this->id;
        } else {
            foreach ($this->getAuthors()->each() as $author) {
                $author->delete();
            }
        }

        foreach (\Yii::$app->getRequest()->post('author_id') as $authorId) {
            $author = new BookAuthor(['author_id' => $authorId, 'book_id' => $this->id]);
            if(!$author->save()){
                $this->errors['save'] = [$author->getErrorSummary()];
                $saved = false;
                break;
            }
        }
        if (!$this->description->save(false)) {
            if (empty($this->errors['save'])) {
                $this->errors['save'] = [];
            }
            $this->errors['save'][] = $this->description->getErrorSummary();
            $saved = false;
        }
        if ($saved) {
            try {
                $this->transaction->commit();
                if ($insert) {
                    //логика отправки СМС не должна заспамливать модель. Это вообще посторонняя логика.
                    SmsPilotService::notifySubscribers($this);
                }
            } catch (\yii\db\Exception $e) {
                $this->errors['save'] = $e->getMessage();
                $this->transaction->rollBack();
            }
        } else {
            $this->transaction->rollBack();
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function getDescription()
    {
        return $this->hasOne(BookDescription::class, ['id' => 'id']);
    }

    public function getAuthors()
    {
        return $this->hasMany(BookAuthor::class, ['book_id' => 'id'])
                    ->joinWith('authors');
    }

    public function beforeDelete()
    {
        $this->transaction = \Yii::$app->db->beginTransaction();
        return parent::beforeDelete();
    }

    /**
     * а тут я действую оптимально, через запросы
     * @throws \yii\db\Exception
     */
    public function afterDelete()
    {
        (new Query())
            ->createCommand()
            ->delete('books_description', ['id' => $this->id])
            ->execute();
        (new Query())
            ->createCommand()
            ->delete('book_author', ['book_id' => $this->id])
            ->execute();
        try {
            $this->transaction->commit();
            parent::afterDelete();
        } catch (\yii\db\Exception $e) {
            $this->errors['delete'] = $e->getMessage();
            $this->transaction->rollBack();
        }
    }
}
