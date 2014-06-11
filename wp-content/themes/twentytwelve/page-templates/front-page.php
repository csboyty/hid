<?php
/**
 * Template Name: Front Page Template
 *
 * Description: A page template that provides a key component of WordPress as a CMS
 * by meeting the need for a carefully crafted introductory page. The front page template
 * in Twenty Twelve consists of a page content area for adding text, images, video --
 * anything you'd like -- followed by front-page-only widgets in one or two columns.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">


            <?php
               echo chr(195).chr(128);
                echo "<br>";
                $string="你好啊";
                $length=strlen($string);
                $result = array();
                for($i=0;$i<$length;$i++){
                    if(ord($string[$i])>127){
                        $result[] = ord($string[$i]).' '.ord($string[++$i]).' '.ord($string[++$i]);
                    }
                }
                print_r($result);

                $string1="ddddssfsss'ssss";
                echo "<br>".addcslashes($string1,"f");
                echo "<br>".addslashes($string1);
                echo "<br>".bin2hex($string1);

                echo "<br>";
                print_r(explode(",","nnn"));

                $keyword =KWYWORD;

                echo "<br>".$keyword;

                echo "<br>";
                print_r(get_terms("zy_people",array(
                    'hide_empty'    => false,
                )));

            ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar( 'front' ); ?>
<?php get_footer(); ?>