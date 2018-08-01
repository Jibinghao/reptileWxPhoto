<?php
/**
 * Created by PhpStorm.
 * User: xm902
 * Date: 2018/7/31
 * Time: 11:18
 */

namespace backend\controllers;


use app\models\Article;
use app\models\Img;
use backend\components\Controller;
use Yii;

class ImgController extends Controller
{

    function actionTest()
    {
        $htmlUrl = $this->getHtmlUrl('手机壁纸美图');
        if ($htmlUrl) {
            $article = $this->getArticleUrl($htmlUrl);
            $count = 0;
            foreach ($article as $item) {
                $count += $this->getImgUrl($item['url'], $item['article_id']);
            }
            $this->jsonOut(0, '共导入' . $count . '张图片');
        }
    }

    /**
     * 获取Html的链接
     */
    function getHtmlUrl($title)
    {
        $title = urlencode($title);
        $url = 'http://weixin.sogou.com/weixin?type=1&s_from=input&query=' . $title . '&ie=utf8&_sug_=n&_sug_type_=';
        $url_msg = file_get_contents($url);
        $regex = "/<a target=\"_blank\" uigs=\"account_name_0\".*?>.*?<\/a>/ism";
        /**     $regex2="/<li id=\"sogou_vr_11002301_box_0\".*?>.*?<\/li>/ism"; **/
        if (preg_match_all($regex, $url_msg, $matches)) {
            $url_msg = $matches[0][0];
        } else {
            $this->jsonOut(1001, "没有找到任何数据");
        }
        $first_position = stripos($url_msg, "href=\"");
        $end_postion = stripos($url_msg, "\"><em>");
        $url_msg = substr($url_msg, $first_position + 6, $end_postion - $first_position - 6);
        return str_replace(['/r', '/n', '/r/n', 'amp;'], '', trim($url_msg));


    }

    /**
     * @param $listFilter
     * @param $htmlUrl
     * @return array获取最新的文章链接
     */
    function getArticleUrl($htmlUrl)
    {


        $base_url = 'https://mp.weixin.qq.com';
        $pagecontent = file_get_contents($htmlUrl);

        //取出微信公众号id
        $regex_wx_id = "/<p class=\"profile_account\".*?>.*?<\/p>/ism";
        if (preg_match_all($regex_wx_id, $pagecontent, $matches)) {
            $wx_id = $matches[0][0];
            $wx_id = str_replace(['/r', '/n', '/r/n'], '', trim($wx_id));
            $wx_id_first_position = stripos($wx_id, "微信号: ");
            $wx_id_end_postion = stripos($wx_id, "</p>");
            $wx_id = substr($wx_id, $wx_id_first_position + 11, $wx_id_end_postion - $wx_id_first_position - 11);
        }
        //取出微信公众号昵称
        $regex_wx_name = "/<strong class=\"profile_nickname\".*?>.*?<\/strong>/ism";
        if (preg_match_all($regex_wx_name, $pagecontent, $matches)) {
            $wx_name = $matches[0][0];
            $wx_name = str_replace(['/r', '/n', '/r/n'], '', trim($wx_name));
            $wx_name_first_position = stripos($wx_name, "profile_nickname");
            $wx_name_end_postion = stripos($wx_name, "</strong>");
            $wx_name = trim(substr($wx_name, $wx_name_first_position + 18, $wx_name_end_postion - $wx_name_first_position - 18));
        }

        $first_position = stripos($pagecontent, "var msgList =");
        $end_postion = stripos($pagecontent, "seajs.use");
        $result = substr($pagecontent, $first_position, $end_postion - $first_position);
        $result = str_replace(['/r', '/n', '/r/n'], '', trim($result));
        $first_position = stripos($result, "{");
        $result = substr($result, $first_position, $end_postion - $first_position);
        $result = str_replace(']};', ']}', $result);//去掉尾部的;
        $result_list = json_decode($result, true)['list'];
        $url_list = array();
        if ($result_list) {
            foreach ($result_list as $item) {
                $item = $item['app_msg_ext_info'];
                //判断multi_app_msg_item_list有没有值，如果没有，证明只发一篇文章
                $item_child = $item['multi_app_msg_item_list'];
                if (count($item_child) > 0) {
                    foreach ($item_child as $item1) {
                        $item = array();
                        $item['title'] = str_replace(['/r', '/n', '/r/n', 'amp;'], '', trim($item1['title']));
                        $item['url'] = str_replace(['/r', '/n', '/r/n', 'amp;'], '', $base_url . trim($item1['content_url']));
//                        var_dump($item);
                        $url_list[] = $item;
                    }
                } else {
                    $item = array();
                    $item['title'] = str_replace(['/r', '/n', '/r/n', 'amp;'], '', trim($item1['title']));
                    $item['url'] = str_replace(['/r', '/n', '/r/n', 'amp;'], '', $base_url . trim($item1['content_url']));
                    $url_list[] = $item;
                }
            }
        }
        //筛选数据库的文章

        $results = array();
        foreach ($url_list as $url_list_item) {
            $article = $url_list_item['title'];
            $item = Article::findOne(['title' => $article]);
            if (!$item) {
                $article_item = new Article();
                $article_item['title'] = $article;
                $article_item['ct_time'] = date('Y-m-d H:i:s');
                $article_item['wx_id'] = $wx_id;
                $article_item['wx_title'] = $wx_name;
                $article_item->save();
                $url_list_item['article_id'] = $article_item->id;
                $results[] = $url_list_item;
            }
        }
        return $results;
//        $this->jsonOut(0,'请求成功',$url_list);
    }

    /**
     *
     */
    function getImgUrl($url, $article_id)
    {
        $pagecontent = file_get_contents($url);
        $imgpreg = "/(data-src)=([\"|']?)([^\"'>]+.())/i";
        preg_match_all($imgpreg, $pagecontent, $img_list);
        $results = $img_list[3];
        $count = 0;
        foreach ($results as $data) {
            if (strpos($data, 'gif')) {
                //去除那些gif图
            } else {
                $msg = substr($data, 0, count($data) - 2) . "&tp=webp&wxfrom=5&wx_lazy=1";
                $img = new Img();
                $img['img_path'] = $msg;
                $img['article_id'] = $article_id;
                $img['ct_time'] = date('Y-m-d H:i:s');
                $img['read_count'] = 0;
                $img->save();
                $count++;
            }
        }
        return $count;
    }

    //根据关键字查询壁纸
    function actionSearch()
    {
        $keyword = $this->get('keyword');
        if (!$keyword){
            $this->jsonOut(1003,"请输入关键字");

        }
        $sql= 'SELECT * FROM img WHERE img.article_id in (SELECT id FROM article WHERE title LIKE \'%'.$keyword.'%\')';
        $connection = Yii::$app->db;
        $results = $connection->createCommand($sql)->queryAll();
        if (count($results)>0){
            $this->jsonOut(0,"success",$results);
        }else{
            $this->jsonOut(1003,"暂无数据",$results);

        }
    }
}