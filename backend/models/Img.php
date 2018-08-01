<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "img".
 *
 * @property int $id
 * @property string $img_path
 * @property string $article_id
 * @property string $ct_time
 * @property int $read_count
 */
class Img extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'img';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['img_path', 'article_id', 'ct_time', 'read_count'], 'required'],
            [['ct_time'], 'safe'],
            [['read_count'], 'integer'],
            [['img_path'], 'string', 'max' => 255],
            [['article_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'img_path' => 'Img Path',
            'article_id' => 'Article ID',
            'ct_time' => 'Ct Time',
            'read_count' => 'Read Count',
        ];
    }
}
