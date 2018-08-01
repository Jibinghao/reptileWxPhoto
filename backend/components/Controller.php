<?php

namespace backend\components;
use Yii;

/**
 * Created by PhpStorm.
 * User: xm902
 * Date: 2018/7/31
 * Time: 11:31
 */
class Controller extends \yii\web\Controller
{
    function jsonOut($error, $msg, $xy = array(), $extend_array = array())
    {
        header('Content-type:application/json;charset=uft-8');


        //判断扩展数组是否是数组
        if ($extend_array) {
            if (!is_array($extend_array)) {
                return false;
            }
        }

        if ($xy) {
            $response_data = [
                'error' => $error,
                'msg' => $msg,
                'data' => $xy,
            ];
        } else {
            $response_data = array(
                'error' => $error,
                'msg' => $msg,
            );
        }

        $response_data = array_merge($response_data, $extend_array);

        exit(json_encode($response_data, JSON_UNESCAPED_UNICODE));

    }

    /**
     * 获取 get 参数，不存在返回 false
     */
    public function get($index = NULL, $default = '', $filter = array('addslashes', 'trim'))
    {
        return $this->magicVar(Yii::$app->request->get($index, $default), $filter);
    }

    /**
     * 安全过滤
     * @param var $val 需要过滤的变量
     * @param array $fiterFunArr 回调过滤函数
     */
    function magicVar($string, $fiterFunArr = array('stripslashes', 'trim'))
    {

        if (empty($string)) return $string;
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = $this->magicVar($val);
            }
        } else {
            foreach ($fiterFunArr as $func) {
                function_exists($func) && $string = $func($string);
            }
        }
        return $string;
    }
}