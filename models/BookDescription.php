<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\web\UploadedFile;

class BookDescription extends ActiveRecord
{
    public const UPLOAD_PATH = 'images';

    public UploadedFile|null $imageFile;
    public int $isbn;

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
        return 'books_description';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'default', 'value' => null],
            [['title', 'description'], 'required'],
            [['title', 'description', 'cover_image'], 'string'],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg']
        ];
    }

    public function beforeValidate()
    {
        if(!empty($this->imageFile)) {
            $this->cover_image = $this->upload();
        } elseif (!empty($this->oldAttributes['cover_image'])) {
            $this->cover_image = $this->oldAttributes['cover_image'];
        }
        return parent::beforeValidate();
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if(!empty($this->imageFile) && file_exists($this->imageFile->tempName)) {
            unlink($this->imageFile->tempName);
        }
    }

    /**
     * @return string|null
     */
    public function upload(): string|null
    {
        $filename = md5($this->isbn);
        $folder = self::UPLOAD_PATH . '/' . substr($filename, 0, 2);
        if (!is_dir($folder)) {
            mkdir($folder, 0755);
        }
        $path = $folder . '/' . $filename . '.' . $this->imageFile->extension;
        copy($this->imageFile->tempName, $path);
        chmod($path, 0644);
        return $path;
    }
}
