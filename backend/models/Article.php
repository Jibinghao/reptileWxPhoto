<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "article".
 *
 * @property int $id
 * @property string $title
 * @property string $ct_time
 * @property string $wx_id 公众号id
 * @property string $wx_title 公众号名称
 */
class Article extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'ct_time'], 'required'],
            [['ct_time'], 'safe'],
            [['title', 'wx_id', 'wx_title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'ct_time' => 'Ct Time',
            'wx_id' => 'Wx ID',
            'wx_title' => 'Wx Title',
        ];
    }
}
