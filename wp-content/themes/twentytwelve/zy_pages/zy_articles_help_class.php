<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-20
 * Time: 上午9:54
 * 主要是获取一些数据，比如说修改的时候要刷出来的数据
 */
class zy_articles_help_class
{
    const ZY_COMPRESS_SUFFIX="_480x480"; //常量，代表压缩文件的后缀

    /**
     * 获取原来的城市信息
     * @param int $post_id  文章或者幻灯片id
     * @param bool $show_flag  是否输出内容
     * @return string $zy_old_city 返回城市数据字符串（json格式）
     */
    public function zy_old_city($post_id){

        //获取原来的城市信息
        $cities=wp_get_object_terms($post_id,"zy_city");
        if(count($cities)!=0){
            foreach($cities as $city){
                echo '<span class="zy_selected_span"><input type="hidden" name="tax_input[zy_city][]" value="'.$city->name.'">'.
                    '<span class="zy_selected_span_content">'.$city->name.'</span><a class="zy_selected_span_delete" href="javascript:void(0)">X</a></span>';
                }
        }
    }

    /**
     * 获取xml中的城市信息，和old函数分开是因为在html的select插入一个input（hidden）字段会使html变形
     */
    public function zy_get_city(){
        $terms=get_terms("zy_city",array(
            'hide_empty'    => false,
        ));
        ?>
        <script type="text/javascript">
            var city_source=[
                <?php
                    foreach($terms as $tag){
                        echo "'$tag->name',";
                    }
                ?>
            ];
        </script>
    <?php

    }

    /**
     * 获取原来的公司信息
     * @param int $post_id 文章或者幻灯片id
     * @return string $zy_old_company 公司名称
     */
    public function zy_old_company($post_id){
        //获取原来的城市信息
        $companies=wp_get_object_terms($post_id,"zy_company");
        if(count($companies)!=0){
            foreach($companies as $company){
                echo '<span class="zy_selected_span"><input type="hidden" name="tax_input[zy_company][]" value="'.$company->name.'">'.
                    '<span class="zy_selected_span_content">'.$company->name.'</span><a class="zy_selected_span_delete" href="javascript:void(0)">X</a></span>';
            }
        }
    }

    /**
     * 从xml中获取公司数据
     */
    public function zy_get_company(){
        $terms=get_terms("zy_company",array(
            'hide_empty'    => false,
        ));
    ?>
        <script type="text/javascript">
            var company_source=[
                <?php
                    foreach($terms as $tag){
                        echo "'$tag->name',";
                    }
                ?>
            ];
        </script>
    <?php

    }

    /**
     * 获取原来的流派信息
     * @param int $post_id 文章或者幻灯片id
     * @return string $zy_old_genre 流派名称
     */
    public function zy_old_genre($post_id){

        //获取原来的城市信息
        $genres=wp_get_object_terms($post_id,"zy_genre");
        if(count($genres)!=0){
            foreach($genres as $genre){
                echo '<span class="zy_selected_span"><input type="hidden" name="tax_input[zy_genre][]" value="'.$genre->name.'">'.
                    '<span class="zy_selected_span_content">'.$genre->name.'</span><a class="zy_selected_span_delete" href="javascript:void(0)">X</a></span>';
            }
        }
    }

    /**
     * 从xml中获取流派信息
     */
    public function zy_get_genre(){

        $terms=get_terms("zy_genre",array(
            'hide_empty'    => false,
        ));
    ?>
        <script type="text/javascript">
            var genre_source=[
                <?php
                    foreach($terms as $tag){
                        echo "'$tag->name',";
                    }
                ?>
            ];
        </script>
    <?php

    }

    /**
     * 获取原来的流派信息
     * @param int $post_id 文章或者幻灯片id
     * @return string $zy_old_people 人物名称
     * */
    public function zy_old_people($post_id){

        //获取原来的城市信息
        $peoples=wp_get_object_terms($post_id,"zy_people");
        if(count($peoples)!=0){
            foreach($peoples as $people){
                echo '<span class="zy_selected_span"><input type="hidden" name="tax_input[zy_people][]" value="'.$people->name.'">'.
                    '<span class="zy_selected_span_content">'.$people->name.'</span><a class="zy_selected_span_delete" href="javascript:void(0)">X</a></span>';
            }
        }
    }

    /**
     *输出人物信息,输出到前端的一个数组中
     */
    public function zy_get_people(){

        $terms=get_terms("zy_people",array(
            'hide_empty'    => false,
        ));
    ?>
        <script type="text/javascript">
            var people_source=[
                <?php
                    foreach($terms as $tag){
                        echo "'$tag->name',";
                    }
                ?>
            ];
        </script>
    <?php

    }

    /**
     * 获取幻灯片已经上传的内容
     * @param int $slide_id 幻灯片id
     */
    public function zy_get_slide_medias($slide_id){
        if($slide_id){

            /*
           * 需要通过获取内容中的media_id，搜索出所有的绑定了媒体文件，
           * 这个顺序才是页面上已上传列表的顺序
           * */
            $doc=new DOMDocument();
            $content=html_entity_decode(get_post($slide_id)->post_content);
            $doc->loadXML("<as>".$content."</as>");
            $imgs=$doc->getElementsByTagName("a");
            $results_ids=array();

            foreach($imgs as $i){
                $img=$i->getElementsByTagName("img")->item(0);
                $img_id=$img->getAttribute("data-zy-media-id");
                $results_ids[$img_id]=json_decode(get_post_meta($slide_id,$img_id,true),true);
            }

            //循环输出内容
            foreach($results_ids as $key=>$value_array){
                $iframe_page_src="";

                //获取要跳转的iframe页面,strpos这里返回值是0
                if(strpos($key,"zy_location_")!==false){
                    $iframe_page_src=get_template_directory_uri()."/zy_pages/zy_set_location_video.html?$key";
                }else if(strpos($key,"zy_3d_")!==false){
                    $iframe_page_src=get_template_directory_uri()."/zy_pages/zy_set_3d.html?$key";
                }else if(strpos($key,"zy_network_")!==false){
                    $iframe_page_src=get_template_directory_uri()."/zy_pages/zy_set_network_video.html?$key";
                }else if(strpos($key,"zy_ppt_")!==false){
                    $iframe_page_src=get_template_directory_uri()."/zy_pages/zy_set_ppt.html?$key";
                }else if(strpos($key,"zy_image_")!==false){
                    $iframe_page_src=get_template_directory_uri()."/zy_pages/zy_set_image.html?$key";
                }

                //获取类型和缩略图等
                $media_type=$value_array["zy_media_type"];
                $media_name=$value_array["zy_media_filename"]?$value_array["zy_media_filename"]:$value_array["zy_media_thumb_filename"];
                $media_name=htmlentities($media_name,ENT_QUOTES,"UTF-8");
                $thumb_path=$value_array["zy_media_thumb_filepath"];

                echo '<li><a class="zy_media_list" data-zy-media-type="'.$media_type.'" data-zy-media-id="'.$key.'" href="'.$iframe_page_src.'" target="zy_media_iframe">'.
                    '<img class="zy_small_thumb" src="'.$thumb_path.'">'.
                    '<span  title="'.$media_name.'" draggable="true" class="zy_media_filename">'.$media_name.'</span><span class="zy_media_delete"></span></a></li>';
            }

            $filter_medias_string=json_encode($results_ids);

            if($filter_medias_string!="[]"){
                echo "<script type='text/javascript'>zy_uploaded_medias=$filter_medias_string</script>";
                echo "<input type='hidden' name='zy_old_medias' value='1'> ";//代表是修改
            }
        }
    }

    /**
     * 获取图文混排已经上传的媒体文件
     * @param int $post_id 文章id
     */
    public function zy_get_post_medias($post_id){
        $old_medias=get_post_meta($post_id);
        $filter_medias=array();
        foreach($old_medias as $key=>$value){
            if(strpos($key,"zy_location_")!==false||strpos($key,"zy_3d_")!==false||strpos($key,"zy_ppt_")!==false||strpos($key,"zy_network_")!==false){
                $filter_medias[$key]=json_decode($value[0],true);
            }
        }
        $filter_medias_string=json_encode($filter_medias);

        if($filter_medias_string!="[]"){
            echo "<script type='text/javascript'>zy_uploaded_medias=$filter_medias_string</script>";
            echo "<input type='hidden' name='zy_old_medias' value='1'> ";
        }
    }

    /**
     * 获取封面图路径,此路径是系统压缩后的路径
     * @param string $filepath 封面图路径
     */
    public function zy_get_compress_thumb($filepath){
        $pathinfo=pathinfo($filepath);
        $dir=$pathinfo["dirname"];

        //中文为自首的文件会是空
        $filename=substr($filepath,strrpos($filepath, '/')+1,strrpos($filepath, '.')-strrpos($filepath, '/')-1);

        $ext=$pathinfo["extension"];
        $zy_old_thumb_compress=$filename.self::ZY_COMPRESS_SUFFIX.".".$ext;
        echo $dir."/".$zy_old_thumb_compress;
    }

    /**
     * 获取原来的封面图
     * @param int $post_id 文章或者幻灯片id
     * @return array|mixed $zy_old_thumb 原来封面图数据的数组
     */
    public function zy_get_old_thumb($post_id){

        //获取原来的缩略图
        $zy_old_thumb=get_post_meta($post_id,"zy_thumb",true);
        $zy_thumb_filename="";

        if($zy_old_thumb){
            $zy_old_thumb=json_decode($zy_old_thumb,true);
            $zy_thumb_filename=$zy_old_thumb["filename"];
            echo "<input type='hidden' name='zy_old_thumb' value='$zy_thumb_filename'>";
        }

        //将值设置为原始值，不存在的话也会是空
        echo "<input type='hidden' value='$zy_thumb_filename' name='zy_thumb' id='zy_thumb'>";

        return $zy_old_thumb;
    }

    /**
     * 获取原来的背景图
     * @param int $post_id 文章后者幻灯片id
     */
    public function zy_get_old_background($post_id){
        $zy_old_background=get_post_meta($post_id,"zy_background",true);
        $zy_background_filename="";

        if($zy_old_background){
            $zy_old_background=json_decode($zy_old_background,true);
            $zy_background_filename=$zy_old_background["filename"];
            echo "<input type='hidden' name='zy_old_background' value='$zy_background_filename'>";
        }

        if($zy_old_background){
            $filepath=$zy_old_background["filepath"];
            if($zy_old_background["type"]=="mp4"){
                echo "<video id='zy_background_content'  class='zy_background' controls><source src='$filepath' type='video/mp4' /></video>";
            }else{
                echo "<img id='zy_background_content' class='zy_background' src='$filepath'>";
            }

        }else{
            echo "<img id='zy_background_content' class='zy_background' src='".get_template_directory_uri()."/images/app/zy_default_background.png'>";
        }

        echo "<input type='hidden' value='$zy_background_filename' name='zy_background' id='zy_background'>";
    }
}
