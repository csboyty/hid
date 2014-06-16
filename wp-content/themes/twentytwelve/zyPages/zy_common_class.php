<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-20
 * Time: 上午10:54
 * 此类包含一些常用的函数,都是使用静态方法
 */

class zy_common_class{

    /**
     * 定义删除目录函数
     * @static
     * @param string $dir 需要删除的目录
     * @return bool true|false 删除是否成功
     */
    public static function zy_deldir($dir){

        //先删除目录下的文件：
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {               
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    if(!unlink($fullpath)){
                        return false;
                    }
                } else {

                    //嵌套调用
                    if(!self::zy_deldir($fullpath)){
                        return false;
                    }
                }
            }
        }
        closedir($dh);

        //删除当前文件夹：
        if(!rmdir($dir)) {
            return false;
        }

        return true;
    }

    /**
     * @static
     * 由于json_encode转化中文的时候，会转成unicode编码，写一个函数代码
     * @param array $array 需要转化的数组
     * @return string 转化后的json字符串
     */
    public static function zy_array_to_string($array){
        //$array是一维的键值数组
        $string=array();
        foreach($array as $key=>$value){
            array_push($string,'"'.$key.'":"'.$value.'"');
        }
        return '{'.implode(",",$string).'}';
    }

    /**
     * @static
     * 检测是否有现有的term，在保存公司、城市、流派、人物时使用到
     * @param string $term 需要检测的term值
     * @return mixed taxonomy_id|false taxonomy_id（最小值为1）可以让文章直接绑定，表示存在，false表示不存在
     */
    public static function zy_has_term($term){
        global $wpdb;
        $results=$wpdb->get_col("SELECT a.term_taxonomy_id FROM $wpdb->term_taxonomy AS a,$wpdb->terms AS b WHERE b.name = '$term' AND b.term_id=a.term_id");
        if(count($results)>0){
            return $results[0];//返回taxonomy_id，好让文章绑定
        }else{
            return false;
        }
    }

    /**
     * 发送http请求的函数函
     * 数在成功的情况下返回信息，不成功的情况下返回false，不成功有两种情况（code为5xx、返回的信息为failure）
     * @static
     * @param $ids
     * @param $url
     * @return bool
     */
    public static function zy_http_send($ids,$url){

        //发送http请求
        $response = wp_remote_post( $url, array(
                'method' => 'POST',
                'timeout' => 30,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => array( 'docId' => $ids),
                'cookies' => array()
            )
        );

        //获取结果
        //$response_code = wp_remote_retrieve_response_code( $response );
        //$response_message = wp_remote_retrieve_response_message( $response );
        $response_body=wp_remote_retrieve_body($response);
        if ($response_body=="success"){
            return true;
        }else{
            return false;
        }
    }
}
