<?php

use yii\db\Migration;

/**
 * Class m230710_171729_tables_initial_create
 */
class m230710_171729_tables_initial_create extends Migration
{
    /**
     * {@inheritdoc}
     * Если речь идёт об оптимизации и более-менее высоких нагрузках, я бы создавал таблицы руками в базе, потому,
     * что, скажем unsigned конструктор миграций не поддерживает.
     */
    public function safeUp()
    {
        $this->createTable('{{%books}}', [
            'id' => $this->primaryKey(11)->notNull(),
            'isbn' => $this->bigInteger(13)->notNull(),
            'year' => $this->smallInteger(4)->notNull(),
            'created_by' => $this->integer(11)->notNull(),
            'created_date' => $this->dateTime()->notNull(),
            'viewed' => $this->integer(11)->notNull(),
        ]);
        /**
         * Разделено, потому, что MySQL (и MariaDB) будет независимо ни от чего более создавать временную таблицу
         * при операциях с аггрегацией, если в таблице есть поля text или blob.
         * На varchar разные сборки реагируют по разному.
         */
        $this->createTable('{{%books_description}}', [
            'id' => $this->primaryKey(11)->notNull(),
            'title' => $this->string(255)->notNull(),
            'cover_image' => $this->string(255)->notNull()->defaultValue(''),
            'description' => $this->text()->notNull(),
        ]);

        $this->createTable('{{%authors}}', [
            'id' => $this->primaryKey(11)->notNull(),
            'fio' => $this->string(255)->notNull(),
        ]);

        $this->createTable('{{%book_author}}', [
            'book_id' => $this->integer(11)->notNull(),
            'author_id' => $this->integer(11)->notNull(),
        ]);
        //если я не создам первичный ключ, MySQL создаст его сам. Это будет бесполезное численное поле с автоинкрементом.
        $this->addPrimaryKey('book_author_pk', 'book_author', ['book_id', 'author_id']);
        //что бы быстро искать книги по автору.
        $this->createIndex('book_author_author_book', 'book_author', ['author_id', 'book_id'], true);

        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(11)->notNull(),
            'username' => $this->string(255)->notNull(),
            'auth_key' => $this->string(255)->notNull()->defaultValue(''),
            'access_token' => $this->string(255)->notNull()->defaultValue(''),
            'password_hash' => $this->char(64)->notNull(),
        ]);
        //контроль уникальности и быстрый поиск по логину. Вдруг у нас миллион пользователей?
        $this->createIndex('users_username', 'users', ['username'], true);

        $this->createTable('{{%subscription}}', [
            'author_id' => $this->integer(11)->notNull(),
            'phone_num' => $this->bigInteger(15)->notNull(),
        ]);
        $this->addPrimaryKey('subscription_pk', 'subscription', ['author_id', 'phone_num']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%books}}');
        $this->dropTable('{{%books_description}}');
        $this->dropTable('{{%authors}}');
        $this->dropTable('{{%book_author}}');
        $this->dropTable('{{%users}}');
        $this->dropTable('{{%subscription}}');
    }
}
