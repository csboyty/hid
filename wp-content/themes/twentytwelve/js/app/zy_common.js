/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-13
 * Time: 上午11:10
 * 图文混排和幻灯片公用js,包含了一些常用函数
 * 数据字典
 * zy_json_to_Str json对象转化为字符串
 * zy_get_random 获取随机函数
 * zy_drag 拖拽函数
 * zy_create_thumb_uploader 上传缩略图句柄
 * zy_create__media_uploader    上传媒体文件句柄
 * zy_control_genre_event  流派和人物的事件句柄
 */
var zy_common = (function(){

    /**
     *
     * @param selectedContain
     * @param text
     * @param type
     */
    function zy_show_select(selectedContain,text,type){
        var result=false;

        //判断是否存在
        selectedContain.find(".zy_selected_span_content").each(function(index,m){
            if(jQuery(this).text().trim()==text){
                result=true;
            }
        });

        if(!result){
            var data={
                value:text,
                type:type
            };
            var tpl=jQuery("#zy_selected_tpl").html();
            var html=juicer(tpl,data);

            jQuery(html).appendTo(selectedContain);
        }
    }

    return {

        /*
         * json对象转字符串函数,主要用于提交最终的media数据
         * params o 需要转化的json对象
         * */
        "zy_json_to_Str":function (o) {

            var me=this;
            var arr = [];
            var fmt = function (s) {
                if (typeof s == 'object' && s != null) return me.zy_json_to_Str(s);
                return /^(string|number)$/.test(typeof s) ? '"' + s + '"' : s;
            };
            for (var i in o) arr.push('"' + i + '":' + fmt(o[i]));
            return '{' + arr.join(',') + '}';
        },

        /*
         * 产生随机数函数，根据当前日期，并且加上前缀，尾部4位随机数
         * */
        "zy_get_random":function(){

            var date=new Date();
            var retValue="";
            var mo=(date.getMonth()+1)<10?('0'+''+(date.getMonth()+1)):date.getMonth()+1;
            var dd=date.getDate()<10?('0'+''+date.getDate()):date.getDate();
            var hh=date.getHours()<10?('0'+''+date.getHours()):date.getHours();
            var mi=date.getMinutes()<10?('0'+''+date.getMinutes()):date.getMinutes();
            var ss=date.getSeconds()<10?('0'+''+date.getSeconds()):date.getSeconds();
            retValue=date.getFullYear()+''+mo+''+dd+''+hh+''+mi+''+ss+'';
            for(var j=0;j<4;j++){
                retValue+=''+parseInt(10*Math.random())+'';
            }
            if(arguments.length==1){
                return arguments[0]+''+retValue;
            }
            else
                return retValue;
        },

        /**
         * 公司、流派、人物的select响应事件
         * @param {String} type 类型，对应的select的id为type_select,tpl的id为type_selected_tpl,已选容器type_selected
         */
        zy_input_handler:function(type){
            var source=null;
            var selectedContain=jQuery("#"+type+"_selected");

            if(type=="zy_people"){
                source=people_source;
            }else if(type=="zy_genre"){
                source=genre_source;
            }else if(type=="zy_company"){
                source=company_source;
            }else if(type=="zy_city"){
                source=city_source;
            }

            //标签自定匹配
            jQuery("#"+type+"_input").autocomplete({
                minLength:2,
                source:source,
                select:function(event,ui){

                    //城市只能有一个
                    if(type=="zy_city"){
                        if(selectedContain.find(".zy_selected_span_content").length>=1){
                            return false;
                        }
                    }

                    zy_show_select(selectedContain,ui.item.label,type);

                    //阻止默认事件，默认会将值填写到输入框
                    event.preventDefault();
                }
            });
        },

        /**
         *
         * @param target
         * @param type
         */
        zy_add_customer_tag:function(target,type){
            var selectedContain=target.siblings(".zy_selected_contain");
            var text=target.siblings(".zy_autocomplete").val();

            zy_show_select(selectedContain,text,type);
        },



        /**
         * 删除已经选择了的元素
         */
        zy_delete_selected:function(){
            //删除人物事件
            jQuery(document).on("click","a.zy_selected_span_delete",function(event){
                jQuery(this).parent(".zy_selected_span").remove();
            });
        },

        /**
         * 获取已经选择了的元素，插入到一个input的hidden中，传到后台
         * @param {String} type 类型，对应的input的id为类型值，对应的已经选择了的容器id为type_selected
         */
        zy_get_selected:function(type){
            var array=[];
            if(type=="zy_people"){
                jQuery("#zy_people_selected .zy_selected_span_content").each(function (index, people) {
                    array.push(jQuery(people).text());
                });

                jQuery("#zy_people").val(array.join(","));
            }else if(type=="zy_company"){
                jQuery("#zy_company_selected .zy_selected_span_content").each(function (index, company) {
                    array.push(jQuery(company).text());
                });

                jQuery("#zy_company").val(array.join(","));
            }else if(type=="zy_genre"){
                jQuery("#zy_genre_selected .zy_selected_span_content").each(function (index, genre) {
                    array.push(jQuery(genre).text());
                });

                jQuery("#zy_genre").val(array.join(","));
            }else if(type=="zy_city"){
                jQuery("#zy_city_selected .zy_selected_span_content").each(function (index, city) {
                    array.push(jQuery(city).text());
                });

                jQuery("#zy_city").val(array.join(","));
            }
        },

        /*
         * 拖拽函数
         * */
        "zy_drag":function(){

            var targetOl=jQuery("#zy_uploaded_medias_ol")[0];//容器元素
            var eleDrag=null;//被拖动的元素

            jQuery("#zy_uploaded_medias_ol a").each(function(index,l){

                var target=jQuery(this)[0];

                //开始选择
                target.onselectstart=function(){

                    //阻止默认的事件
                    return false;
                };

                //拖拽开始
                target.ondragstart = function(ev) {
                    //拖拽效果
                    ev.dataTransfer.effectAllowed = "move";
                    eleDrag = ev.target;
                    return true;
                };

                //拖拽结束
                target.ondragend = function(ev) {
                    eleDrag = null;
                    return false;
                };
            });

            //在元素中滑过
            //ol作为最大的容器也要处理拖拽事件，当其中有li的时候放到li的前面，但没有的时候放到ol的最后面
            targetOl.ondragover=function(ev){
                ev.preventDefault();//阻止浏览器的默认事件
                return false;
            };

            //进入元素
            targetOl.ondragenter=function(ev){

                if(ev.toElement==targetOl){
                    targetOl.appendChild(jQuery(eleDrag).parents("li")[0]);
                }else{
                    targetOl.insertBefore(jQuery(eleDrag).parents("li")[0],jQuery(ev.toElement).parents("li")[0]);
                }
                return false;
            };
        },

        /*
         * 清除背景
         * */
        "zy_clear_background":function () {
            jQuery("#zy_upload_background_clear").click(function () {
                jQuery("#zy_background_content").remove();
                jQuery("#zy_background").val("");
                jQuery("#zy_background_percent").text("");
                jQuery("<img id='zy_background_content'  class='zy_background' src='" + zy_config.zy_template_url + "/images/app/zy_default_background.png'>").
                    appendTo(jQuery("#zy_background_container"));
            });
        },

        /*
         *上传背景模块
         * */
        "zy_create_background_uploader":function () {

            var uploader_background = new plupload.Uploader({
                runtimes:"html5",
                multi_selection:false,
                max_file_size:"20mb",
                browse_button:"zy_upload_background_button",
                container:"zy_background_container",
                //flash_swf_url:'../wp-includes/js/plupload/plupload.flash.swf',
                url:ajaxurl,
                filters:[
                    {title:"Background files", extensions:"jpg,gif,png,jpeg,mp4"}
                ],
                multipart_params:{
                    action:"uploadfile",
                    user_id:zy_config.zy_user_id,
                    file_type:"zy_background",
                    post_id:jQuery("#post_ID").val()
                }
            });

            //初始化
            uploader_background.init();

            //文件添加事件
            uploader_background.bind("FilesAdded", function (up, files) {
                var filename = files[0].name;
                var lastIndex = filename.lastIndexOf(".");
                filename = filename.substring(0, lastIndex);

                //只含有汉字、数字、字母、下划线不能以下划线开头和结尾
                var reg = /^(?!_)(?!.*?_$)[a-zA-Z0-9_\u4e00-\u9fa5]+$/;
                if (!reg.test(filename)) {
                    alert("文件名必须是数字下划线汉字字母,且不能以下划线开头。");

                    //删除文件
                    up.removeFile(files[0]);
                    return false;
                } else {
                    up.start();//开始上传
                }
            });

            //文件上传进度条事件
            uploader_background.bind("UploadProgress", function (up, file) {
                //$("#"+file.id+" b").html(file.percent + "%");
                jQuery("#zy_background_percent").html(file.percent + "%");
            });

            //出错事件
            uploader_background.bind("Error", function (up, err) {
                alert(err.message);
                up.refresh();
            });

            //上传完毕事件
            uploader_background.bind("FileUploaded", function (up, file, res) {
                //console.log(response.success+"路径："+response.url);
                var response = JSON.parse(res.response);
                if (response.success) {
                    var filename = response.data.filename;
                    var extension = filename.substr(filename.indexOf(".") + 1, filename.length - 1);
                    jQuery("#zy_background_content").remove();
                    jQuery("#zy_background_percent").text("");
                    var string = "";
                    if (extension == "mp4") {
                        string = "<video id='zy_background_content' class='zy_background' controls><source src='" + response.data.url + "' type='video/mp4' /></video>";
                        jQuery("#zy_background_container").append(string);
                    } else {
                        string = "<img id='zy_background_content' class='zy_background' src='" + response.data.url + "'>";
                        jQuery("#zy_background_container").append(string);
                    }
                    jQuery("#zy_background").val(filename);
                } else {
                    alert(response.data.message);
                }
            });
        },

        /*
         *上传缩略图模块
         * */
        "zy_create_thumb_uploader":function(){

            var uploader_thumb=new plupload.Uploader({
                runtimes:"html5",
                multi_selection:false,
                max_file_size:zy_config.zy_img_upload_size,
                browse_button:"zy_upload_thumb_button",
                container:"zy_thumb_container",
                //flash_swf_url: '../wp-includes/js/plupload/plupload.flash.swf',
                url: ajaxurl,
                filters : [
                    {title : "Image files", extensions : "jpg,gif,png,jpeg"}
                ],
                multipart_params: {
                    action: "uploadfile",
                    user_id:zy_config.zy_user_id,
                    file_type:"zy_thumb",
                    post_id:jQuery("#post_ID").val()
                }
            });

            //初始化
            uploader_thumb.init();

            //文件添加事件
            uploader_thumb.bind("FilesAdded",function(up,files){
                var filename=files[0].name;
                var lastIndex=filename.lastIndexOf(".");
                filename=filename.substring(0,lastIndex);

                //只含有汉字、数字、字母、下划线不能以下划线开头和结尾
                var reg=/^(?!_)(?!.*?_$)[a-zA-Z0-9_\u4e00-\u9fa5]+$/;
                //var reg=/^(\w+)ws([\u0391-\uFFE5]+)$/;
                if(!reg.test(filename)){
                    alert("文件名必须是数字下划线汉字字母,且不能以下划线开头。");

                    //删除文件
                    up.removeFile(files[0]);
                    return false;
                }else{
                    up.start();//开始上传
                }
            });

            //文件上传进度条事件
            uploader_thumb.bind("UploadProgress",function(up,file){
                //$("#"+file.id+" b").html(file.percent + "%");
            });

            //出错事件
            uploader_thumb.bind("Error",function(up,err){
                alert(err.message);
                up.refresh();
            });

            //上传完毕事件
            uploader_thumb.bind("FileUploaded",function(up,file,res){
                //console.log(response.success+"路径："+response.url);
                var response=JSON.parse(res.response);
                //console.log(response);
                if(response.success){

                    //显示压缩后的图片
                    var img_src=response.data.url;
                    var img_ext=img_src.substring(img_src.lastIndexOf("."),img_src.length);
                    var img_src_compress=img_src.substring(0,img_src.lastIndexOf("."))+zy_config.zy_compress_suffix+img_ext;
                    jQuery("#zy_uploaded_thumb").attr("src",img_src_compress);
                    jQuery("#zy_thumb").val(response.data.filename);
                }else{
                    alert(response.data.message);
                }
            });
        },

        /*
         * 上传媒体文件模块
         * params filtesrs 文件的格式筛选，upload_btn 绑定上传按钮的元素id，type 媒体文件的类型
         * filesize 文件大小
         * */
        "zy_create_media_uploader":function(filters,upload_btn,type,filesize){

            var me=this;//保存下this变量，以防止事件过程中改变

            //注意，在上传过程中container是不能隐藏的，在声明的时候containner也应该有内容，宽高度都有，不然无法申明
            //上传媒体模块
            var uploader_media=new plupload.Uploader({
                runtimes:"html5",
                multi_selection:true,
                multipart:true,
                max_file_size:filesize,
                browse_button:upload_btn,
                container:"zy_add_media_menu",
                //flash_swf_url: '../wp-includes/js/plupload/plupload.flash.swf',
                url: ajaxurl,
                filters : [
                    {title : "Media files", extensions : filters}
                ],
                multipart_params: {
                    action: "uploadfile",
                    user_id:zy_config.zy_user_id,
                    post_id:jQuery("#post_ID").val()
                }
            });


            //初始化
            uploader_media.init();

            //根据type生成zy_media_id,和iframe的页面
            var zy_media_ids={};//一个file.id和媒体media_id的关联hash，因为要传多个文件，需要记录下每个media_id
            var zy_iframe_page_names={};

            //文件添加事件
            uploader_media.bind("FilesAdded",function(up,files){
                var zy_media_id="";
                var zy_iframe_page_name="";
                var fileLength=files.length;

                for(var i=0;i<fileLength;i++){
                    var filename=files[i].name;
                    var lastIndex=filename.lastIndexOf(".");
                    var filename_noext=filename.substring(0,lastIndex);

                    //只含有汉字、数字、字母、下划线不能以下划线开头和结尾
                    var reg=/^(?!_)(?!.*?_$)[a-zA-Z0-9_\u4e00-\u9fa5]+$/;
                    if(!reg.test(filename_noext)){

                        alert("文件"+filename+"命名有误（只能数字汉字字母下划线，且不能以下划线开头）,将从上传列表中删除。");

                        //删除文件
                        up.removeFile(files[i]);
                    }else{

                        //给zy_media_id和iframe页面名称赋值
                        if(type=="zy_location_video"){
                            zy_media_id=me.zy_get_random("zy_location_");
                            zy_iframe_page_name="zy_set_location_video.html";
                            zy_media_ids[files[i]["id"]]=zy_media_id;
                            zy_iframe_page_names[files[i]["id"]]=zy_iframe_page_name;
                        }else if(type=="zy_3d"){
                            zy_media_id=me.zy_get_random("zy_3d_");
                            zy_iframe_page_name="zy_set_3d.html";
                            zy_media_ids[files[i]["id"]]=zy_media_id;
                            zy_iframe_page_names[files[i]["id"]]=zy_iframe_page_name;
                        }else if(type=="zy_ppt"){
                            zy_media_id=me.zy_get_random("zy_ppt_");
                            zy_iframe_page_name="zy_set_ppt.html";
                            zy_media_ids[files[i]["id"]]=zy_media_id;
                            zy_iframe_page_names[files[i]["id"]]=zy_iframe_page_name;
                        }else if(type=="zy_image"){
                            zy_media_id=me.zy_get_random("zy_image_");
                            zy_iframe_page_name="zy_set_image.html";
                            zy_media_ids[files[i]["id"]]=zy_media_id;
                            zy_iframe_page_names[files[i]["id"]]=zy_iframe_page_name;
                        }

                        //组装显示的数据
                        var data={
                            media_id:zy_media_id,
                            thumb_src:zy_config.zy_template_url+'/images/app/zy_small_thumb.png',
                            filename:filename
                        };

                        //显示列表项
                        var tpl=jQuery("#zy_uncomplete_tpl").html();
                        var html=juicer(tpl,data);
                        jQuery("#zy_uploaded_medias_ol").append(html);

                        //隐藏菜单栏
                        jQuery("#zy_add_media_menu").css("zIndex",1);
                    }
                }

                //开始上传
                up.start();

            });

            //文件上传进度条事件
            uploader_media.bind("UploadProgress",function(up,file){
                jQuery(".zy_uncomplete_li[data-zy-media-id='"+zy_media_ids[file.id]+"']").find(".zy_media_percent").html(file.percent+"%");

            });

            //出错事件
            uploader_media.bind("Error",function(up,err){
                //由于这里4个上传按钮放到一个面板中，会出现init错误，但是不影响使用，
                if(err.message.indexOf("size")!=-1){
                    alert(err.message);
                }
                up.refresh();
            });

            //上传完毕事件
            uploader_media.bind("FileUploaded",function(up,file,res){
                var response=JSON.parse(res.response);
                if(response.success){

                    //存在未完成的li，说明在上传过程中没有被删除，应该处理
                    var uncomplete_li=jQuery(".zy_uncomplete_li[data-zy-media-id='"+zy_media_ids[file.id]+"']");

                    if(uncomplete_li.length){
                        //移除上传时候的li
                        uncomplete_li.remove();


                        var classString="";
                        var thumb_src=zy_config.zy_template_url+"/images/app/zy_small_thumb.png";


                        if(type=="zy_image"){
                            thumb_src=response.data.url;
                        }

                        if(jQuery("#zy_uploaded_medias_ol .zy_media_list_active").length==0){
                            classString="class='zy_media_list_active'";

                            jQuery("#zy_media_iframe").attr("src",zy_config.zy_template_url+'/zy_pages/'+zy_iframe_page_names[file.id]+'?'+zy_media_ids[file.id]);
                            //jQuery("#zy_arrow_img").css("display","inline");//显示图标
                        }



                        //组装显示的数据
                        var data={
                            classString:classString,
                            media_type:type,
                            media_id:zy_media_ids[file.id],
                            iframe_src:zy_config.zy_template_url+'/zy_pages/'+zy_iframe_page_names[file.id]+'?'+zy_media_ids[file.id],
                            thumb_src:thumb_src,
                            filename:file.name
                        };

                        //显示列表项
                        var tpl=jQuery("#zy_complete_tpl").html();
                        var html=juicer(tpl,data);
                        jQuery("#zy_uploaded_medias_ol").append(html);

                        //设置zy_uploaded_medias
                        zy_uploaded_medias[zy_media_ids[file.id]]={

                            //声明一个空的对象，后续将内容全部加入
                        };

                        if(type=="zy_image"){

                            //如果是图片媒体，需要同时设置四个信息
                            zy_uploaded_medias[zy_media_ids[file.id]]["zy_media_thumb_filename"]=response.data.filename;
                            zy_uploaded_medias[zy_media_ids[file.id]]["zy_media_thumb_filepath"]=response.data.url;
                            zy_uploaded_medias[zy_media_ids[file.id]]["zy_media_filename"]=response.data.filename;
                            zy_uploaded_medias[zy_media_ids[file.id]]["zy_media_filepath"]=response.data.url;
                        }else{
                            zy_uploaded_medias[zy_media_ids[file.id]]["zy_media_filename"]=response.data.filename;
                            zy_uploaded_medias[zy_media_ids[file.id]]["zy_media_filepath"]=response.data.url;
                        }
                        zy_uploaded_medias[zy_media_ids[file.id]]["zy_media_type"]=type;

                        //执行一次拖拽,因为元素是动态添加的，应该在添加后添加拖拽事件
                        me.zy_drag();
                    }

                }else{
                    alert(response.data.message);
                }
            });

        }
    }
})();
