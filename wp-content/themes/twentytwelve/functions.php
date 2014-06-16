<?php
/**
 * Twenty Twelve functions and definitions.
 *
 * Sets up the theme and provides some helper functions, which are used
 * in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development and
 * http://codex.wordpress.org/Child_Themes), you can override certain functions
 * (those wrapped in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before the parent
 * theme's file, so the child theme functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are instead attached
 * to a filter or action hook.
 *
 * For more information on hooks, actions, and filters, see http://codex.wordpress.org/Plugin_API.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

/**
 * Sets up the content width value based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) )
	$content_width = 625;

/**
 * Sets up theme defaults and registers the various WordPress features that
 * Twenty Twelve supports.
 *
 * @uses load_theme_textdomain() For translation/localization support.
 * @uses add_editor_style() To add a Visual Editor stylesheet.
 * @uses add_theme_support() To add support for post thumbnails, automatic feed links,
 * 	custom background, and post formats.
 * @uses register_nav_menu() To add support for navigation menus.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_setup() {
	/*
	 * Makes Twenty Twelve available for translation.
	 *
	 * Translations can be added to the /languages/ directory.
	 * If you're building a theme based on Twenty Twelve, use a find and replace
	 * to change 'twentytwelve' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'twentytwelve', get_template_directory() . '/languages' );

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// Adds RSS feed links to <head> for posts and comments.
	add_theme_support( 'automatic-feed-links' );

	// This theme supports a variety of post formats.
	add_theme_support( 'post-formats', array( 'aside', 'image', 'link', 'quote', 'status' ) );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menu( 'primary', __( 'Primary Menu', 'twentytwelve' ) );

	/*
	 * This theme supports custom background color and image, and here
	 * we also set up the default background color.
	 */
	add_theme_support( 'custom-background', array(
		'default-color' => 'e6e6e6',
	) );

	// This theme uses a custom image size for featured images, displayed on "standard" posts.
	add_theme_support( 'post-thumbnails' );
	//set_post_thumbnail_size( 624, 9999 ); // Unlimited height, soft crop
}
add_action( 'after_setup_theme', 'twentytwelve_setup' );

/**
 * Adds support for a custom header image.
 */
require( get_template_directory() . '/inc/custom-header.php' );

/**
 * Enqueues scripts and styles for front-end.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_scripts_styles() {
	global $wp_styles;

	/*
	 * Adds JavaScript to pages with the comment form to support
	 * sites with threaded comments (when in use).
	 */
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	/*
	 * Adds JavaScript for handling the navigation menu hide-and-show behavior.
	 */
	wp_enqueue_script( 'twentytwelve-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '1.0', true );

	/*
	 * Loads our special font CSS file.
	 *
	 * The use of Open Sans by default is localized. For languages that use
	 * characters not supported by the font, the font can be disabled.
	 *
	 * To disable in a child theme, use wp_dequeue_style()
	 * function mytheme_dequeue_fonts() {
	 *     wp_dequeue_style( 'twentytwelve-fonts' );
	 * }
	 * add_action( 'wp_enqueue_scripts', 'mytheme_dequeue_fonts', 11 );
	 */

	/* translators: If there are characters in your language that are not supported
	   by Open Sans, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Open Sans font: on or off', 'twentytwelve' ) ) {
		$subsets = 'latin,latin-ext';

		/* translators: To add an additional Open Sans character subset specific to your language, translate
		   this to 'greek', 'cyrillic' or 'vietnamese'. Do not translate into your own language. */
		$subset = _x( 'no-subset', 'Open Sans font: add new subset (greek, cyrillic, vietnamese)', 'twentytwelve' );

		if ( 'cyrillic' == $subset )
			$subsets .= ',cyrillic,cyrillic-ext';
		elseif ( 'greek' == $subset )
			$subsets .= ',greek,greek-ext';
		elseif ( 'vietnamese' == $subset )
			$subsets .= ',vietnamese';

		$protocol = is_ssl() ? 'https' : 'http';
		$query_args = array(
			'family' => 'Open+Sans:400italic,700italic,400,700',
			'subset' => $subsets,
		);
		wp_enqueue_style( 'twentytwelve-fonts', add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" ), array(), null );
	}

	/*
	 * Loads our main stylesheet.
	 */
	wp_enqueue_style( 'twentytwelve-style', get_stylesheet_uri() );

	/*
	 * Loads the Internet Explorer specific stylesheet.
	 */
	wp_enqueue_style( 'twentytwelve-ie', get_template_directory_uri() . '/css/ie.css', array( 'twentytwelve-style' ), '20121010' );
	$wp_styles->add_data( 'twentytwelve-ie', 'conditional', 'lt IE 9' );
}
add_action( 'wp_enqueue_scripts', 'twentytwelve_scripts_styles' );

/**
 * Creates a nicely formatted and more specific title element text
 * for output in head of document, based on current view.
 *
 * @since Twenty Twelve 1.0
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string Filtered title.
 */
function twentytwelve_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= get_bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( 'Page %s', 'twentytwelve' ), max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'twentytwelve_wp_title', 10, 2 );

/**
 * Makes our wp_nav_menu() fallback -- wp_page_menu() -- show a home link.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_page_menu_args( $args ) {
	if ( ! isset( $args['show_home'] ) )
		$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'twentytwelve_page_menu_args' );

/**
 * Registers our main widget area and the front page widget areas.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_widgets_init() {
	register_sidebar( array(
		'name' => __( 'Main Sidebar', 'twentytwelve' ),
		'id' => 'sidebar-1',
		'description' => __( 'Appears on posts and pages except the optional Front Page template, which has its own widgets', 'twentytwelve' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => __( 'First Front Page Widget Area', 'twentytwelve' ),
		'id' => 'sidebar-2',
		'description' => __( 'Appears when using the optional Front Page template with a page set as Static Front Page', 'twentytwelve' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => __( 'Second Front Page Widget Area', 'twentytwelve' ),
		'id' => 'sidebar-3',
		'description' => __( 'Appears when using the optional Front Page template with a page set as Static Front Page', 'twentytwelve' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
}
add_action( 'widgets_init', 'twentytwelve_widgets_init' );

if ( ! function_exists( 'twentytwelve_content_nav' ) ) :
/**
 * Displays navigation to next/previous pages when applicable.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_content_nav( $html_id ) {
	global $wp_query;

	$html_id = esc_attr( $html_id );

	if ( $wp_query->max_num_pages > 1 ) : ?>
		<nav id="<?php echo $html_id; ?>" class="navigation" role="navigation">
			<h3 class="assistive-text"><?php _e( 'Post navigation', 'twentytwelve' ); ?></h3>
			<div class="nav-previous alignleft"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentytwelve' ) ); ?></div>
			<div class="nav-next alignright"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentytwelve' ) ); ?></div>
		</nav><!-- #<?php echo $html_id; ?> .navigation -->
	<?php endif;
}
endif;

if ( ! function_exists( 'twentytwelve_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own twentytwelve_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
		// Display trackbacks differently than normal comments.
	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
		<p><?php _e( 'Pingback:', 'twentytwelve' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'twentytwelve' ), '<span class="edit-link">', '</span>' ); ?></p>
	<?php
			break;
		default :
		// Proceed with normal comments.
		global $post;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<header class="comment-meta comment-author vcard">
				<?php
					echo get_avatar( $comment, 44 );
					printf( '<cite class="fn">%1$s %2$s</cite>',
						get_comment_author_link(),
						// If current post author is also comment author, make it known visually.
						( $comment->user_id === $post->post_author ) ? '<span> ' . __( 'Post author', 'twentytwelve' ) . '</span>' : ''
					);
					printf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
						esc_url( get_comment_link( $comment->comment_ID ) ),
						get_comment_time( 'c' ),
						/* translators: 1: date, 2: time */
						sprintf( __( '%1$s at %2$s', 'twentytwelve' ), get_comment_date(), get_comment_time() )
					);
				?>
			</header><!-- .comment-meta -->

			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'twentytwelve' ); ?></p>
			<?php endif; ?>

			<section class="comment-content comment">
				<?php comment_text(); ?>
				<?php edit_comment_link( __( 'Edit', 'twentytwelve' ), '<p class="edit-link">', '</p>' ); ?>
			</section><!-- .comment-content -->

			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'twentytwelve' ), 'after' => ' <span>&darr;</span>', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
		</article><!-- #comment-## -->
	<?php
		break;
	endswitch; // end comment_type check
}
endif;

if ( ! function_exists( 'twentytwelve_entry_meta' ) ) :
/**
 * Prints HTML with meta information for current post: categories, tags, permalink, author, and date.
 *
 * Create your own twentytwelve_entry_meta() to override in a child theme.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_entry_meta() {
	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'twentytwelve' ) );

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', 'twentytwelve' ) );

	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'twentytwelve' ), get_the_author() ) ),
		get_the_author()
	);

	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	if ( $tag_list ) {
		$utility_text = __( 'This entry was posted in %1$s and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', 'twentytwelve' );
	} elseif ( $categories_list ) {
		$utility_text = __( 'This entry was posted in %1$s on %3$s<span class="by-author"> by %4$s</span>.', 'twentytwelve' );
	} else {
		$utility_text = __( 'This entry was posted on %3$s<span class="by-author"> by %4$s</span>.', 'twentytwelve' );
	}

	printf(
		$utility_text,
		$categories_list,
		$tag_list,
		$date,
		$author
	);
}
endif;

/**
 * Extends the default WordPress body class to denote:
 * 1. Using a full-width layout, when no active widgets in the sidebar
 *    or full-width template.
 * 2. Front Page template: thumbnail in use and number of sidebars for
 *    widget areas.
 * 3. White or empty background color to change the layout and spacing.
 * 4. Custom fonts enabled.
 * 5. Single or multiple authors.
 *
 * @since Twenty Twelve 1.0
 *
 * @param array Existing class values.
 * @return array Filtered class values.
 */
function twentytwelve_body_class( $classes ) {
	$background_color = get_background_color();

	if ( ! is_active_sidebar( 'sidebar-1' ) || is_page_template( 'page-templates/full-width.php' ) )
		$classes[] = 'full-width';

	if ( is_page_template( 'page-templates/front-page.php' ) ) {
		$classes[] = 'template-front-page';
		if ( has_post_thumbnail() )
			$classes[] = 'has-post-thumbnail';
		if ( is_active_sidebar( 'sidebar-2' ) && is_active_sidebar( 'sidebar-3' ) )
			$classes[] = 'two-sidebars';
	}

	if ( empty( $background_color ) )
		$classes[] = 'custom-background-empty';
	elseif ( in_array( $background_color, array( 'fff', 'ffffff' ) ) )
		$classes[] = 'custom-background-white';

	// Enable custom font class only if the font CSS is queued to load.
	if ( wp_style_is( 'twentytwelve-fonts', 'queue' ) )
		$classes[] = 'custom-font-enabled';

	if ( ! is_multi_author() )
		$classes[] = 'single-author';

	return $classes;
}
add_filter( 'body_class', 'twentytwelve_body_class' );

/**
 * Adjusts content_width value for full-width and single image attachment
 * templates, and when there are no active widgets in the sidebar.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_content_width() {
	if ( is_page_template( 'page-templates/full-width.php' ) || is_attachment() || ! is_active_sidebar( 'sidebar-1' ) ) {
		global $content_width;
		$content_width = 960;
	}
}
add_action( 'template_redirect', 'twentytwelve_content_width' );

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @since Twenty Twelve 1.0
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function twentytwelve_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
}
add_action( 'customize_register', 'twentytwelve_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 *
 * @since Twenty Twelve 1.0
 */
function twentytwelve_customize_preview_js() {
	wp_enqueue_script( 'twentytwelve-customizer', get_template_directory_uri() . '/js/theme-customizer.js', array( 'customize-preview' ), '20120827', true );
}
add_action( 'customize_preview_init', 'twentytwelve_customize_preview_js' );



/*--------------------------------------------------自定义功能代码===============================================*/
//引入文件
//include(get_template_directory()."/zy_pages/zy_common_class.php");

/*============================================加载自定义资源=====================================================*/
/*
 * 加载自定义的资源文件
 * */
function zy_load_resource($hook){

    //只有添加幻灯片和编辑文章页面才加在这几个js


    //添加一个自定义的js变量,把模版地址刷给页面，自定义的js可以直接使用
    $zy_template_url=get_template_directory_uri();
    global $user_ID;

    wp_enqueue_script("jquery-ui-autocomplete");//标签自动匹配需要用到

    //新增、修改图文混排页面
    if("post-new.php"==$hook||($hook=="post.php"&&get_post($_GET["post"])->post_type=="post")){

        echo "<script type='text/javascript'>
        var zy_uploaded_medias={},
        zy_config={
            zy_template_url:'$zy_template_url',
            zy_user_id:'$user_ID',
            zy_img_upload_size:'2mb',
            zy_media_upload_size:'200mb',
            zy_compress_suffix:'_480x480'
        };
        Object.freeze(zy_config); //锁定对象
        </script>";

        wp_enqueue_script("zy_common",$zy_template_url.'/js/app/zy_common.js');
        wp_enqueue_script("zy_juicer_js",$zy_template_url.'/js/lib/juicer-min.js');

        //引入文章页面的js
        wp_enqueue_script("zy_post",$zy_template_url.'/js/app/zy_post.js');
        //引入自定义的css
        wp_enqueue_style("zy_main_css",$zy_template_url.'/css/app/zy_post.css');


        return ;

    }

    if( $_GET["page"]=="zy_slide_menu"){
        //幻灯片页面
        echo "<script type='text/javascript'>
        var zy_uploaded_medias={},
        zy_config={
            zy_template_url:'$zy_template_url',
            zy_user_id:'$user_ID',
            zy_img_upload_size:'2mb',
            zy_media_upload_size:'200mb',
            zy_compress_suffix:'_480x480'
        };
        Object.freeze(zy_config); //锁定对象
         </script>";

        wp_enqueue_script("zy_common",$zy_template_url.'/js/app/zy_common.js');
        wp_enqueue_script("zy_juicer_js",$zy_template_url.'/js/lib/juicer-min.js');
        wp_enqueue_script("plupload");
        wp_enqueue_script("plupload-html5");


        //引入文章页面的js
        wp_enqueue_script("zy_slide",$zy_template_url.'/js/app/zy_slide.js');

        //引入自定义的css
        wp_enqueue_style("zy_main_css",$zy_template_url.'/css/app/zy_slide.css');

        return ;

    }

    if($hook=="edit.php"){
        //禁用所有列表页的快速编辑、查看
        echo "<style type='text/css'>
            .row-actions .view,.row-actions .inline{display: none};
        </style>";

        return ;
    }
}
//admin_head,admin_print_scripts一般都只是输出，函数中用echo
add_action('admin_enqueue_scripts', 'zy_load_resource');


/*===============================================增加标签类型============================================*/

function create_post_customer_tax() {
    register_taxonomy(
        'zy_genre',
        'post',
        array(
            'label' => "流派标签",
            "labels"=>array(
                "edit_item"=>"更新流派",
                "add_new_item"=>"新增流派",
                "search_items"=>"搜索流派"
            ),
            "hierarchical"=>false
        )
    );
    register_taxonomy(
        'zy_people',
        'post',
        array(
            'label' => "人物标签",
            "labels"=>array(
                "edit_item"=>"更新人物",
                "add_new_item"=>"新增人物",
                "search_items"=>"搜索人物"
            ),
            "hierarchical"=>false
        )
    );
    register_taxonomy(
        'zy_company',
        'post',
        array(
            'label' => "公司标签",
            "labels"=>array(
                "edit_item"=>"更新公司",
                "add_new_item"=>"新增公司",
                "search_items"=>"搜索公司"
            ),
            "hierarchical"=>false
        )
    );
    register_taxonomy(
        'zy_city',
        'post',
        array(
            'label' => "城市标签",
            "labels"=>array(
                "edit_item"=>"更新城市",
                "add_new_item"=>"新增城市",
                "search_items"=>"搜索城市"
            ),
            "hierarchical"=>false
        )
    );
}

add_action( 'init', 'create_post_customer_tax',0);

/*============================================添加自定义菜单=====================================================*/
/*
 * 添加文章菜单栏下“幻灯片”菜单,添加设置菜单栏“打包数据”菜单
 * */
//为菜单添加展示页面的函数
function zy_slide_menu_page(){

    $url=get_template_directory();

    include($url."/zy_pages/zy_slide.php");
}

/*
 * 自定义数据表,如果作为插件的话只在插件启用的时候创建表格,
 * register_activation_hook( __FILE__,'insert_own_table');
 * 写在这里每次都会执行，效率不高，最好写到插件中
 * 主要是保存打包的id
 * */

function insert_own_table(){
    global $wpdb,$jal_db_version;

    $jal_db_version="1.0";

    $table_name=$wpdb->prefix."pack_ids";
    $table_pack_name=$wpdb->prefix."logs";
    //判断是否存在表格，如果不存在创建表格
    if($wpdb->get_var("show tables like '$table_name'")!=$table_name){
        $sql="CREATE TABLE  ".$table_name." (post_id bigint(20) PRIMARY KEY NOT NULL,
            pack_time tinytext,
            pack_lock int DEFAULT 0 NOT NULL
        )  DEFAULT CHARSET=utf8;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        add_option( "jal_db_version", $jal_db_version );
        //echo "The own table is created";
    }

    //建立打包程序需要的表
    if($wpdb->get_var("show tables like '$table_pack_name'")!=$table_pack_name){
        $sql="CREATE TABLE ".$table_pack_name." (
          `id` bigint(20) NOT NULL AUTO_INCREMENT,
          `type` varchar(32) NOT NULL,
          `level` char(10) NOT NULL,
          `message` varchar(2048) NOT NULL,
          `log_time` datetime NOT NULL,
          PRIMARY KEY (`id`)
        )  DEFAULT CHARSET=utf8;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        add_option( "jal_db_version", $jal_db_version );
        //echo "The own table is created";
    }
}
//hook
add_action("admin_init","insert_own_table");

/*
 * 打包菜单处理函数
 * */
$timeline_term_id=19;//能够打包的分类
function zy_pack_menu_page(){

    global $wpdb,$timeline_term_id;



    echo "<br><br><br>正在打包中......<br><br><br>";

    $tablename=$wpdb->prefix."pack_ids";
    $zy_packing_ids=$wpdb->get_col("SELECT post_id FROM $tablename AS i,$wpdb->posts AS p,
        $wpdb->term_relationships AS ps WHERE i.pack_lock=0 AND p.ID=i.post_id AND p.post_status!='trash'
        AND ps.term_taxonomy_id=$timeline_term_id AND ps.object_id=i.post_id");

    if(count($zy_packing_ids)){

        $url=get_site_url()."/bundle-app/makeBundle";
        $zy_http_result=false;
        $zy_pack_time="";
        $ids=implode(",",$zy_packing_ids);//组成字符串
        //$ids="219";

        //更改数据库后，发送到打包程序
        for($i=0;$i<3;$i++){
            if(zy_common_class::zy_http_send($ids,$url)){
                $zy_http_result=true;

                $zy_pack_time=time();//记录时间，从1970到现在的秒数

                break;//跳出循环
            }
        }

        //设置显示值和是否锁定id
        if($zy_http_result){

            //将时间写入到数据库中
            $wpdb->query("UPDATE $tablename SET pack_time=$zy_pack_time WHERE post_id IN ($ids)");



            //显示成功信息
            echo "文章".$ids."打包数据成功，请选择其他操作。";

        }else{
            //显示错误信息
            echo "打包数据出错，本次打包未成功！请稍后再打包。";
        }

    }else{
        echo "没有新数据可以打包，请选择其他操作!";
    }
}
//添加菜单
function zy_add_menu(){
    //添加文章子菜单“幻灯片”
    add_posts_page("幻灯片","幻灯片",'manage_options','zy_slide_menu',"zy_slide_menu_page");
    //添加设置子菜单“打包数据”
    add_options_page("打包数据","打包数据","manage_options","zy_pack_menu","zy_pack_menu_page");
}
add_action("admin_menu","zy_add_menu");



/*===============================================================图文混排页面代码===============================*/

/*---------------------------------------------------添加右边栏输入项部分-------------------------------------------*/
/*
 *添加字段到图文混排页面右边
 * */
function zy_add_box(){
    include(get_template_directory()."/zy_pages/zy_post_box_class.php");
    add_meta_box("zy_location_id","定位",array("zy_post_box_class",'zy_location_box'),'post','side');
    add_meta_box("zy_label_id","自定义标签",array("zy_post_box_class",'zy_label_box'),'post','side');
    add_meta_box("zy_thumb_id","缩略图",array("zy_post_box_class",'zy_thumb_box'),'post','side');
    add_meta_box("zy_background_id","背景",array("zy_post_box_class",'zy_background_box'),'post','side');
}
add_action("add_meta_boxes",'zy_add_box');

function zy_remove_custom_taxonomy()
{
    remove_meta_box('tagsdiv-zy_genre', "post", 'side' );
    remove_meta_box('tagsdiv-zy_city', "post", 'side' );
    remove_meta_box('tagsdiv-zy_company', "post", 'side' );
    remove_meta_box('tagsdiv-zy_people', "post", 'side' );
}
add_action('admin_menu','zy_remove_custom_taxonomy');

/*--------------------------------------------------------图文混排保存数据部分---------------------------------*/
global $zy_post_save;
/*
 * 保存媒体文件
 * */
function zy_save_medias($post_id){
    global $zy_post_save;
    //引入类，必须在这一类函数的第一个执行的函数中引入，不然后面的类无法使用对象
    include(get_template_directory()."/zy_pages/zy_articles_save_class.php");
    $zy_post_save=new zy_articles_save_class();

    $new_medias=$_POST["zy_medias"];

    if(isset($_POST["zy_old_medias"])){
        //判断是否为修改
       if(!$zy_post_save->zy_edit_save_medias($post_id,$new_medias)){
           return false;
       }
    }else{
        //判断为新增
       if(!$zy_post_save->zy_new_save_medias($post_id,$new_medias)){
           return false;
       }
    }
    //返回值
    return true;
};

/*
 * 保存年份数据
 * */
function zy_save_year($post_id){
    global $zy_post_save;

    $start_year=$_POST["zy_start_year"];
    //判断是新增还是修改
    if(isset($_POST["zy_old_start_year"])){
        //修改
        $old_start_year=$_POST["zy_old_start_year"];
        if(!$zy_post_save->zy_edit_save_year($post_id,$start_year,$old_start_year)){
            return false;
        }
    }else{
        //新增
       if(!$zy_post_save->zy_new_save_year($post_id,$start_year)){
           return false;
       }
    }
    //返回值
    return true;
}


/*
 * 存储缩略图数据函数
 * */
function zy_save_thumb($post_id){
    global $zy_post_save;
    $filename=$_POST["zy_thumb"];

    //分为新建和修改两种类型
    if(isset($_POST["zy_old_thumb"])){
        $old_filename=$_POST["zy_old_thumb"];
        //如果是修改了文件
        if(!$zy_post_save->zy_edit_save_thumb($post_id,$filename,$old_filename)){
            return false;
        }
    }else{
        if(!$zy_post_save->zy_new_save_thumb($post_id,$filename)){
            return false;
        }
    }

    //返回值,让
    return true;
}

/*
 * 存储背景图数据函数
 * */
function zy_save_background($post_id){
    global $zy_post_save;
    $filename=$_POST["zy_background"];

    //分为新建和修改两种类型
    if(isset($_POST["zy_old_background"])){
        $old_filename=$_POST["zy_old_background"];
        //如果是修改了文件
        if(!$zy_post_save->zy_edit_save_background($post_id,$filename,$old_filename)){
            return false;
        }
    }else{
        if(!$zy_post_save->zy_new_save_background($post_id,$filename)){
            return false;
        }
    }

    //返回值,让
    return true;
}

/*
 * 保存自定义数据,所有的数据在一个函数保存
 * */
function zy_data_save( $post_id ) {

    /*
   * 需要判断是图文混排还是幻灯片，因为幻灯片的wp_insert_post也会出发publish_post
   * 由于有快速编辑，同样会进入，但是保存的时候会出错，所以要判断一下如果是快速编辑则不进入
   * 保存数据，快速编辑的时候是没有缩略图数据过来的。
   * */
     if(strpos(get_post($post_id)->post_mime_type,"zyslide")===false&&isset($_POST["zy_thumb"])){
         //设置页面编码
         header("content-type:text/html;charset=utf-8");

         /*存储媒体文件数据*/
         if(!zy_save_medias($post_id)){
             //提示错误
             die("保存媒体数据出错，请联系开发人员");
         }

         /*存储缩略图数据*/
         if(!zy_save_thumb($post_id)){
             //提示错误
             die("保存缩略图数据出错，请联系开发人员");
         }

         /*存储背景数据*/
         if(!zy_save_background($post_id)){
             //提示错误
             die("保存背景数据出错，请联系开发人员");
         }

         /*存储年份数据*/
         if(!zy_save_year($post_id)){
             //提示错误
             die("保存年份数据出错，请联系开发人员");
         }

         global $wpdb,$user_ID,$timeline_term_id;

         //保存打包数据
         $tablename=$wpdb->prefix."pack_ids";
         if(count($wpdb->get_col("SELECT post_id FROM $tablename WHERE post_id=$post_id"))){

             //存在的情况下，修改
             if($wpdb->update($wpdb->prefix."pack_ids",array("pack_lock"=>0,"pack_time"=>NULL),array("post_id"=>$post_id),array("%d","%s"))===false){
                 die("保存打包数据出错，请联系开发人员");
             }
         }else{

             //不存在的情况下新增
             if(!$wpdb->insert($wpdb->prefix."pack_ids",array("post_id"=>$post_id),array("%d"))){
                 die("保存打包数据出错，请联系开发人员");
             }
         }

         //删除临时存储文件夹
        /* $target_dir=wp_upload_dir();
         $target_dir=$target_dir["basedir"]."/tmp/".$user_ID;
         if(is_dir($target_dir)){
             zy_common_class::zy_deldir($target_dir);
         }*/

         //移动分类删除打包数据,不在timeline分类里的需要删除打包数据
         if($categories=get_the_category($post_id)){
             if(count($categories)>=2||(count($categories)==1&&$categories[0]->term_id!=$timeline_term_id)){

                 //发送数据给打包程序，删除zip包
                 $url=get_site_url()."/bundle-app/removeBundle";
                 $zy_http_result=false;

                 for($i=0;$i<3;$i++){
                     if(zy_common_class::zy_http_send($post_id,$url)){
                         $zy_http_result=true;
                         break;//跳出循环
                     }
                 }

                 //判断是否成功
                 if(!$zy_http_result){
                     die("删除打包文件失败，请将文章id".$post_id."告诉开发人员！");
                 }
             }
         }
     }

}
add_action('publish_post', 'zy_data_save');
//add_action('pre_post_update','zy_data_save');

/*===========================================处理ajax部分====================================*/
//引入类
include(get_template_directory()."/zy_pages/zy_ajax_class.php");
/*
 * 处理文件上传的ajax函数
 * */
add_action('wp_ajax_uploadfile', array("zy_ajax_class",'zy_action_uploadfile'));
//火狐里面这个地方不会带登陆标志过来，需要加下面这句或者前台上传插件使用html5引擎
//add_action('wp_ajax_nopriv_uploadfile', array("zy_ajax_class",'zy_action_uploadfile'));

/*
 * 打包程序接口,ajax请求，告知wordpress打包是否成功
 * */
//无需登陆，即可使用
add_action("wp_ajax_nopriv_zy_pack_unlock",array("zy_ajax_class","zy_pack_unlock_callback"));
add_action("wp_ajax_zy_pack_unlock",array("zy_ajax_class","zy_pack_unlock_callback"));


/*------------------------------------------自定义tinymce插件部分-------------------------------------------*/
/*
 * 添加自定义的tinymce插件
 * */
//添加tinyMCE插件函数
function zy_tinymce_plugins () {

    $plugins = array('zy_insert_media'); //Add any more plugins you want to load here

    $plugins_array = array();

    //Build the response - the key is the plugin name, value is the URL to the plugin JS
    foreach ($plugins as $plugin ) {
        $plugins_array[ $plugin ] = get_template_directory_uri() . '/tinymce/' . $plugin . '/editor_plugin.js';
    }

    return $plugins_array;
}
add_filter('mce_external_plugins', 'zy_tinymce_plugins');


/*
 * 禁用图文混排的自动保存草稿和修订版本
 * */

//取消保存修订版本，这个是在defalut-filters中加了一个action，在保存文章之前，先保存修订版本
remove_action("pre_post_update","wp_save_post_revision");
//remove_filter (  'the_content' ,  'wpautop'  );

//禁用自动保存草稿
function zy_disable_autosave(){
    wp_deregister_script("autosave");
}
add_action("wp_print_scripts","zy_disable_autosave");
//删除数据库多余的记录
function zy_delete_autodraft($post_id){
    global $wpdb;
    //在发布文章的时候删除掉除自己外的其他垃圾文章，除自己外是因为当没填写任何内容发布时，状态也是auto-draft
    $wpdb->query("DELETE FROM $wpdb->posts WHERE post_status = 'auto-draft'");
}

add_action("publish_post","zy_delete_autodraft");


/*===========================================================文章锁定的控制==================================*/
/*
 * 文章处于锁定阶段的判断
 * */
function zy_check_lock($post_id){
    global $wpdb;
    $tablename=$wpdb->prefix."pack_ids";
    $zy_pack=$wpdb->get_row("SELECT * FROM $tablename WHERE post_id=$post_id");
    if($zy_pack->pack_time){
        if(time()-$zy_pack->pack_time<1800&&$zy_pack->pack_lock==0){
            //打包时间在30分钟内，并且还没有设置打包标志为1的需要锁定
            header("content-type:text/html; charset=utf-8");
            die("文章正在被打包，请稍后进行操作，<a href='javascript:history.back()'>返回</a>进行其他操作");

        }
    }

    //如果提交的edit_lock和数据库中保存的不一样，那么要阻止提交
    $current_edit_lock=get_post_meta($post_id,"_edit_lock",true);
    $edit_lock=$_POST["_edit_lock"];
    if($current_edit_lock!=$edit_lock&&$edit_lock){
        header("content-type:text/html; charset=utf-8");
        die("其他人以先于你提交更改，请重新编辑后再提交，<a href='".site_url()."/wp-admin/edit.php'>返回</a>");
    }
}
add_action("pre_post_update","zy_check_lock");



/*===================================================数据清理=====================================*/
/**
 * 清除上传时产生的临时文件
 */
function zy_delete_tmp(){
    include(get_template_directory()."/zy_pages/zy_common_class.php");
    global $user_ID;
    $currentTimeS=time();
    $target_dir=wp_upload_dir();
    $target_dir=$target_dir["basedir"]."/tmp/".$user_ID;
    if(is_dir($target_dir)){
        $fileTimeS=filemtime($target_dir);
        if($currentTimeS-$fileTimeS>12*60*60){
            zy_common_class::zy_deldir($target_dir);
        }
    }
}
add_action("admin_init","zy_delete_tmp");

/*
 * 移入回收站的操作,通知打包程序删除文章
 * 不进行页面报错
 * */
function zy_trash_post($post_id){

    //include(get_template_directory()."/zy_pages/zy_common_class.php");
    header("content-type:text/html; charset=utf-8");

    //只有文章和幻灯片才发送请求去打包程序
    if(get_post($post_id)->post_type=="post"){

        //发送数据给打包程序，删除zip包
        $url=get_site_url()."/bundle-app/removeBundle";
        $zy_http_result=false;

        for($i=0;$i<3;$i++){
            if(zy_common_class::zy_http_send($post_id,$url)){
                $zy_http_result=true;
                break;//跳出循环
            }
        }

        //设置数据库的值
        global $wpdb;
        if($zy_http_result){
            if($wpdb->update($wpdb->prefix."pack_ids",array("pack_lock"=>0,"pack_time"=>NULL),array("post_id"=>$post_id),array("%d","%s"))===false){
                die("重置打包数据库失败，请将文章id".$post_id."告诉开发人员！");
            }
        }else{
            die("删除打包文件失败，请将文章id".$post_id."告诉开发人员！");
        }
    }
}
add_action('trashed_post', 'zy_trash_post');

/*
 * 删除时的操作函数
 * */
function zy_delete_post($post_id){
    //include(get_template_directory()."/zy_pages/zy_common_class.php");
    //设置页面编码
    header("content-type:text/html; charset=utf-8");
    global $wpdb,$zy_pa;
    $targetDir=wp_upload_dir();

    /*不管删除打包文件是否成功，都删除服务器的内容*/
    //删除打包表中的数据
    $sql_result=$wpdb->delete($wpdb->prefix."pack_ids",array("post_id"=>$post_id));
    $delete_file_result=true;

    //删除媒体文件
    if(is_dir($targetDir["basedir"]."/".$post_id)){

        //这里删除可能不会成功，所以出错后应该手动删除文件夹
        $delete_file_result=zy_common_class::zy_deldir($targetDir["basedir"]."/".$post_id);
    }


    if($sql_result===false){
        die("删除打包数据记录失败，请将文章id".$post_id."告诉开发人员！");
    }else if(!$delete_file_result){
        die("删除文件失败，请将文章id".$post_id."告诉开发人员！");
    }

}
add_action('deleted_post', 'zy_delete_post');
//add_action('delete_post',"zy_delete_post");

//删除之前判断文章是否在锁定期
add_action("before_delete_post","zy_check_lock");

/*================================================修改时的跳转===========================================*/
/*
 * 控制文章显示后的修改链接跳转。
 * */
function zy_page_template_redirect()
{

    if(isset($_GET["post"])){
        $post_id=$_GET["post"];
        if(strpos(get_post($post_id)->post_mime_type,"zyslide")!==false){
            wp_redirect(admin_url()."edit.php?page=zy_slide_menu&post_id=$post_id");
            exit();
        }
    }
    //echo $_SERVER["REQUEST_URI"];
}
//hook，在admin的初始化时admin_head,admin_init这两个每次页面都会检测，加重系统负担
add_action( 'add_meta_boxes', 'zy_page_template_redirect' );


/*==================================================添加重写规则，客户端播放视频的页面================*/
function add_zy_rewrite(){
    add_rewrite_rule('show_media/(\d+)/(\w+)$','index.php?pagename=show_media&zy_post_id=$matches[1]&media_id=$matches[2]','top');
    //如果不加下面两句，wordpress无法识别到自定义的参数
    add_rewrite_tag('%zy_post_id%','([^&]+)');
    add_rewrite_tag('%media_id%','([^&]+)');

};
add_action("init","add_zy_rewrite");