<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-18
 * Time: 下午2:37
 * 添加幻灯片菜单页面。
 */
//在functions.php中已经将类包含进来了
include("zy_slide_save_class.php");
include("zy_articles_help_class.php");
$zy_save_slide=new zy_slide_save_class();
$zy_articles_help=new zy_articles_help_class();
$post_id="";


global $wpdb;

?>
<form action="<?php echo admin_url()."edit.php?page=zy_slide_menu"; ?>" class="zy_form" method="post">
<!--头部(标题)-->
<header id="zy_header" class="zy_header">
    <span class="zy_upload_logo"></span>
    <h2>编辑幻灯片</h2>
</header>

<?php
//从连接上获取post_id，这里主要是从列表跳转过来
if(isset($_GET["post_id"])){
    $post_id=$_GET["post_id"];
}

//保存是否成功
if(isset($_POST["zy_action_flag"])){
    if($_POST["zy_action_flag"]=="zy_new"){
        $post_id=$zy_save_slide->zy_slide_new();
    }else{
        $post_id=$zy_save_slide->zy_slide_edit();
    }
    echo "<h4 class='zy_message_tip'>保存成功,请继续进行其他操作</h4>";
}

//判断是新增还是修改，来设置标志位
if($post_id){
    echo "<input type='hidden' name='zy_action_flag' value='zy_edit'>";

    //设置编辑标志
    if(($edit_time=get_post_meta($post_id,"_edit_lock",true))!=""){

        $time_array=explode(":",$edit_time);
        $old_user_id=$time_array[1];

        if($old_user_id!=get_current_user_id()){

            //如果不是单前用户在编辑,并且时间小于5分钟，进行提示
            if(time("mysql")-$time_array[0]<5*60){
                $user_info=get_userdata($old_user_id);
                $user_name=$user_info->display_name;

                $message = __( 'Warning: %s is currently editing this post' );
                $message = sprintf( $message, esc_html( $user_name ) );

                echo "<h4 class='zy_message_tip'>$message</h4>";
            }
        }

        //设置版本值，即数据库的锁定标志
        echo "<input type='hidden' name='_edit_lock' value='$edit_time'>";

    }
}else{
    echo "<input type='hidden' name='zy_action_flag' value='zy_new'>";
}
echo "<input type='hidden' id='post_ID' name='zy_slide_id' value='$post_id'>";
//$post_id=250;

?>
<!--导航栏-->
<nav id="zy_nav">
    <li><a href="#zy_attribute" class="zy_nav1 zy_active">基本设定</a></li>
    <li><a href="#zy_content" class="zy_nav2">内容编辑</a></li>
    <li><a href="#zy_preview" class="zy_nav3">预览并发布</a></li>
    <input id="zy_insert_btn" type="submit" class="zy_btn_insert zy_hidden" value="发布">
</nav>

<!--第一步(设置属性等)-->
<article id="zy_attribute"  class="zy_attribute">
    <!--左边栏-->
    <section id="zy_left_bar">

        <section>
            <label class="zy_label">幻灯片主题</label>
            <input required id="zy_title" maxlength="150" value="<?php echo get_the_title($post_id) ?>" class="zy_item" type="text" name="zy_title"/>

        </section>

        <section class="zy_left_box">
            <label class="zy_label">描述</label>
            <textarea id="zy_memo" class="zy_descr zy_textarea" type="text" name="zy_memo"><?php echo get_post($post_id)->post_excerpt ?></textarea>
        </section>

        <section class="zy_left_box">
            <label class="zy_label">封面</label>

            <div id="zy_thumb_container" class="zy_thumb_toolbar">
               
                <input class="zy_upload_btn" type="button" id="zy_upload_thumb_button" value="上传"/>
 		        <p class="zy_tool_tips">限高宽比为1：1的jpg或png</p>
            </div>
            <?php
                //获取原来的缩略图
                $zy_old_thumb=$zy_articles_help->zy_get_old_thumb($post_id);
            ?>
            <img id="zy_uploaded_thumb" class="zy_cover_pic" src="<?php
                 if($zy_old_thumb){
                     //显示压缩后的图片
                     $zy_articles_help->zy_get_compress_thumb($zy_old_thumb["filepath"]);
                 }else{
                     echo get_template_directory_uri()."/images/app/zy_default_thumb.png";
                 }
                 ?>" class="zy_post_img">

        </section>

    </section>
    <!--右边栏-->
    <aside id="zy_right_bar">

        <section class="zy_right_box">
            <header class="zy_box_header">
                事件定位
            </header>
            <section class="zy_box_content">
                <label class="zy_box_label">时间</label>
                    <?php
                        $zy_old_start_year = get_post_meta($post_id, "zy_start_year", true);
                        if($zy_old_start_year){
                            echo "<input type='hidden' name='zy_old_start_year' value='$zy_old_start_year'>";
                        }

                    ?>
                    <input id="zy_start_year" class="zy_box_input" value="<?php echo $zy_old_start_year ?>" type="text" name="zy_start_year" required="required" pattern="[0-9]{4}"
                           title="请输入四位数字的起始年份" placeholder="起始年四位"/>

            </section>
        </section>

        <section class="zy_right_box">

            <header class="zy_box_header">

                分类

            </header>

            <section id="zy_category_list" class="zy_category_list">
                <ul>
                <!--获取幻灯片文章的类别-->

                <?php wp_category_checklist( $post_id, 0, false,

                    false, null, false); ?>
                </ul>

            </section>

        </section>

        <section class="zy_right_box">
            <!--已选择的html模版-->
            <script type="text/template" id="zy_selected_tpl">
                    <span class="zy_selected_span">
                         <input type="hidden" name="tax_input[${type}][]" value="${value}">
                        <span class="zy_selected_span_content">${value}</span>
                        <a class="zy_selected_span_delete" href="javascript:void(0)">X</a>
                    </span>
            </script>
            <header class="zy_box_header">
                标签
            </header>
            <section class="zy_box_content">
                <section class="zy_section">
                    <label class="zy_box_label">城市</label>
                    <input class="zy_autocomplete" id="zy_city_input" type="text">

                    <?php
                        //读取xml文件
                        $zy_articles_help->zy_get_city();
                    ?>
                    <label class="zy_box_label">已选城市：</label>
                    <div id="zy_city_selected" class="zy_selected_contain">

                        <?php

                            //输出原来的城市信息
                            $zy_articles_help->zy_old_city($post_id,true);
                        ?>

                    </div>
                </section>
                <section class="zy_section">
                    <label class="zy_box_label">公司</label>
                    <input class="zy_autocomplete" id="zy_company_input" type="text">
                    <input type="button" class="zy_add_customer_tag" value="添加" data-add-type="zy_company">
                        <?php
                            //读取xml文件
                            $zy_articles_help->zy_get_company();
                        ?>
                    <label class="zy_box_label">已选公司：</label>
                    <div id="zy_company_selected" class="zy_selected_contain">

                        <?php

                            //设置原来的people
                            $zy_articles_help->zy_old_company($post_id);
                        ?>

                    </div>
                </section>

                <section class="zy_section">
                    <label class="zy_box_label">流派</label>
                    <input class="zy_autocomplete" id="zy_genre_input" type="text">
                    <input type="button" class="zy_add_customer_tag" value="添加" data-add-type="zy_genre">
                        <?php
                            //读取xml文件
                           $zy_articles_help->zy_get_genre();
                        ?>
                    <label class="zy_box_label">已选流派：</label>
                    <div id="zy_genre_selected" class="zy_selected_contain">

                        <?php

                            //设置原来的people
                            $zy_articles_help->zy_old_genre($post_id);
                        ?>

                    </div>
                </section>

                <section class="zy_section">
                    <label class="zy_box_label">人物</label>
                    <input class="zy_autocomplete" id="zy_people_input" type="text">
                    <input type="button" class="zy_add_customer_tag" value="添加" data-add-type="zy_people">
                        <?php

                            //读取xml文件
                            $zy_articles_help->zy_get_people();
                        ?>
                    <label class="zy_box_label">已选人物：</label>
                    <div id="zy_people_selected" class="zy_selected_contain">

                        <?php

                            //设置原来的people
                            $zy_articles_help->zy_old_people($post_id);
                        ?>

                    </div>
                </section>
            </section>

        </section>

        <section class="zy_right_box">

            <header class="zy_box_header">

                背景

            </header>



            <section id="zy_background_container" style="text-align: right">

                <div class="zy_bg_toolbar">





                    <input id="zy_upload_background_clear" type="button" class="zy_upload_btn" value="清除">

                    <input class="zy_upload_btn" type="button"  id="zy_upload_background_button" value="上传"/>

                    <p class="zy_tool_tips">限jpg、png、mp4，分辨率1024*768</p>

                </div>

                <span id="zy_background_percent" class="zy_background_percent"></span>

                <?php

                    //获取原来的缩略图
                    $zy_articles_help->zy_get_old_background($post_id);

               ?>

            </section>
        </section>

    </aside>

</article>

<!--第二步(上传媒体文件)-->

<article id="zy_content" class="zy_hidden zy_uploader">
    <input  id="zy_slide_content" class="zy_item zy_input" type="hidden" name="zy_content"/>
    <input  id="zy_medias" class="zy_item zy_input" type="hidden" name="zy_medias"/>
    <section id="zy_section_left" class="zy_uploader_column_left">
        <span class="zy_section_left_header" id="zy_add_medias_button"></span>
        <!--媒体文件类型的menu-->
        <div id="zy_add_media_menu" class="zy_add_media_menu">
            <ul>
                <li><a id="zy_add_image" class="zy_types1">图片</a></li>
                <!--弹出thickbox窗口，来进行网络视频输入-->
                <li><a title="网络视频" href="#TB_inline?width=150&height=200&inlineId=zy_thickbox_id" class="thickbox zy_types2">网络视频</a></li>
                <li><a id="zy_add_location_video" class="zy_types3">本地视频</a></li>
                <li><a id="zy_add_3d" class="zy_types4">3D文件</a></li>
                <li><a id="zy_add_ppt" class="zy_types5">ppt文件</a></li>
            </ul>
        </div>

        <!-- 媒体文件列表-->
        <!-- 上传未完成的html模版-->
        <script type="text/template" id="zy_uncomplete_tpl">
            <li data-zy-filename="${filename}" class="zy_uncomplete_li" data-zy-media-id="${media_id}">
                <img class="zy_small_thumb" src="${thumb_src}">
                <span class="zy_media_percent">0%</span>
                <span class="zy_uncomplete_delete"></span>
            </li>
        </script>
        <!-- 上传完成的html模版-->
        <script type="text/template" id="zy_complete_tpl">
            <li ${classString}><a class="zy_media_list" data-zy-media-type="${media_type}" data-zy-media-id='${media_id}' href="${iframe_src}" target="zy_media_iframe">
            <img class="zy_small_thumb" src="${thumb_src}">
            <span title='${filename}' draggable="true" class="zy_media_filename">${filename}</span><span class="zy_media_delete"></span></a>
            </li>
        </script>
        <ol id="zy_uploaded_medias_ol" class="zy_uploaded_medias_ol">
        <?php
        /*--------获取原来的绑定了的媒体文件，如果存在的情况下====================*/
            $zy_articles_help->zy_get_slide_medias($post_id);
        ?>
        </ol>


    </section>

    <section id="zy_section_right" class="zy_uploader_column_right">
        <header class="zy_section_right_header"><p><b id="zy_media_type">图片</b></p></header>
       
        <iframe id="zy_media_iframe" name="zy_media_iframe" class="zy_iframe">
        </iframe>
    </section>
	<div class="zy_clear_float"></div>
</article>

<!--第三步(预览)-->
<!--预览模版-->
<script type="text/template" id="zy_preview_tpl">
    <header class="slide-header">
        <h1 class="slide-title">${slide_title}</h1>
        <ul class="slide-tags">
            <li class="date">${slide_year}</li>
            <li class="genre">${slide_genre}</li>
            <li class="location">${slide_city}</li>
            <li class="people">${slide_people}</li>
            <li class="orgnization">${slide_company}</li>
        </ul>
    </header>
    <section id="zy_preview_content" class="all-slides">

        {@each slides as slide}

            <section class="slide">
                $${slide.content}
                <div class="slide-info">
                    <h3>${slide.title}</h3>
                    <p class="slide-description">${slide.memo}</p>
                </div>
            </section>

        {@/each}
    </section>
</script>

<article id="zy_preview" class="zy_hidden">


</article>
</form>

<!--第二步中，输入网络视频的弹出窗口,响应的事件在第二步上传文件的菜单中-->
<?php add_thickbox();//加载thickbox的js库 ?>
<div id="zy_thickbox_id" style="display:none;">
    <label class="zy_network_label">网络视频</label><span class="zy_network_tip">(请使用包含iframe标签的通用代码)</span>
    <div style="margin-top: 30px;">
        <input id="zy_network_input" title="请使用通用代码" class="zy_network_input">
        <input id="zy_network_input_ok" type="button" class="zy_upload_btn" value="确定">
    </div>
</div>
<!--<a href="#TB_inline?width=600&height=550&inlineId=zy_thickbox_id" class="thickbox">View my inline content!</a>-->

<!--第三步中，点击图片要进行媒体文件预览-->
<div id="zy_show_div" style="display:none;">
    <!--这里面一定要有html标签，不然打开的面板会为空-->
    <div>预览内容</div>
</div>