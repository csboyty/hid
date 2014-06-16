<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-25
 * Time: 下午3:47
 * To change this template use File | Settings | File Templates.
 */
include("zy_articles_help_class.php");
class zy_post_box_class
{

    /**
     * 缩略图box
     * @param object $post 文章后者幻灯片对象
     */
    public function zy_thumb_box($post)
    {
        $zy_help = new zy_articles_help_class();

        //获取原来的缩略图
        $zy_old_thumb = $zy_help->zy_get_old_thumb($post->ID);

        //获取已经上传的媒体文件
        $zy_help->zy_get_post_medias($post->ID);

        $edit_time=get_post_meta($post->ID,"_edit_lock",true);

        if($edit_time){
            echo "<input type='hidden' name='_edit_lock' value='$edit_time'>";
        }
    ?>

    <input type="hidden" name="zy_medias" id="zy_medias">

    <div id='zy_thumb_container'>
        <div class="zy_post_div">
            限高宽比为1：1的jpg或png
            <input id="zy_upload_thumb_button" type="button" class="zy_post_button" value="上传">
        </div>
        <img id="zy_uploaded_thumb" src="<?php
            if ($zy_old_thumb) {

                //显示压缩后的图片
                $zy_help->zy_get_compress_thumb($zy_old_thumb["filepath"]);
            } else {
                echo get_template_directory_uri() . "/images/app/zy_default_thumb.png";
            }
            ?>" class="zy_post_img">
    </div>

    <?php
    }

    /**
     * 地理位置box  城市、时间、公司
     * @param object $post 文章或者幻灯片对象
     */
    public function zy_location_box($post)
    {

        $zy_old_start_year = get_post_meta($post->ID, "zy_start_year", true);

        if ($zy_old_start_year) {
            echo "<input type='hidden' name='zy_old_start_year' value='$zy_old_start_year'>";
        }

    ?>

    <label class="zy_post_label">时间</label>
    <input class="zy_post_input" value="<?php echo $zy_old_start_year ?>" type="text" name="zy_start_year"
           required="required" pattern="[0-9]{4}" title="请输入四位数字的起始年份" placeholder="起始年四位"/>

    <?php
    }

    /**
     * 标签box、流派、人物
     * @param object $post 文章或者幻灯片对象
     */
    public function zy_label_box($post)
    {
        $zy_help = new zy_articles_help_class();

        ?>

    <!--已选择html模版-->
    <script type="text/template" id="zy_selected_tpl">
            <span class="zy_selected_span">
                <input type="hidden" name="tax_input[${type}][]" value="${value}">
                <span class="zy_selected_span_content">${value}</span>
                <a class="zy_selected_span_delete" href="javascript:void(0)">X</a>
            </span>
    </script>

    <section class="zy_post_section">
        <label class="zy_post_label">城市</label>
        <input id="zy_city_input" class="zy_autocomplete" type="text">

            <?php

            //读取xml文件
            $zy_help->zy_get_city();
            ?>
        <label class="zy_post_label">已选城市：</label>
        <div id="zy_city_selected" class="zy_selected_contain">

            <?php

            //设置原来的people
            $zy_help->zy_old_city($post->ID);
            ?>

        </div>
    </section>

    <section class="zy_post_section">

        <label class="zy_post_label">公司</label>
        <input id="zy_company_input" class="zy_autocomplete" type="text">
        <input class="zy_add_customer_tag" type="button" value="添加" data-add-type="zy_company">

            <?php

                //读取xml文件
                $zy_help->zy_get_company();
            ?>

        <label class="zy_post_label">已选公司：</label>

        <div id="zy_company_selected" class="zy_selected_contain">

            <?php

                //设置原来的people
                $zy_help->zy_old_company($post->ID);
            ?>

        </div>
    </section>

    <section class="zy_post_section">
        <label class="zy_post_label">流派</label>
        <input id="zy_genre_input" class="zy_autocomplete" type="text">
        <input class="zy_add_customer_tag" type="button" value="添加" data-add-type="zy_genre">

            <?php

                //读取xml文件
                $zy_help->zy_get_genre();
            ?>
        <label class="zy_post_label">已选流派：</label>
        <div id="zy_genre_selected" class="zy_selected_contain">

            <?php

                //设置原来的people
                $zy_help->zy_old_genre($post->ID);
            ?>

        </div>
    </section>

    <section class="zy_post_section">
        <label class="zy_post_label">人物</label>
        <input id="zy_people_input" class="zy_autocomplete" type="text">
        <input class="zy_add_customer_tag" type="button" value="添加" data-add-type="zy_people">

            <?php

                //读取xml文件
                $zy_help->zy_get_people();
            ?>
        <label class="zy_post_label">已选人物：</label>
        <div id="zy_people_selected" class="zy_selected_contain">
            <?php

                //设置原来的people
                $zy_help->zy_old_people($post->ID);
            ?>

        </div>
    </section>

    <?php
    }

    /**
     * 背景box
     * @param object $post 文章或者幻灯片对象
     */
    public function zy_background_box($post)
    {

        $zy_help = new zy_articles_help_class();

    ?>

    <div id='zy_background_container'>
        <div class="zy_post_div">
            <input id="zy_upload_background_button" type="button" class="zy_post_button" value="上传">
            <input id="zy_upload_background_clear" type="button" class="zy_post_button" value="清除">
            <span style="display: block">限jpg、png、mp4，分辨率1024*768</span>
            <span id="zy_background_percent" class="zy_background_percent"></span>
        </div>

        <?php

            //获取原来的背景
            $zy_help->zy_get_old_background($post->ID);

        ?>

    </div>

    <?php

    }
}
