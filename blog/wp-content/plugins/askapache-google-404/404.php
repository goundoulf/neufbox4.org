<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 *
 * created by AskApache.com for http://www.askapache.com/seo/404-google-wordpress-plugin.html
 */
?>
<?php ob_start(); get_header();?>
<?php if(function_exists('aa_google_404')) aa_google_404(); ?>
<?php get_sidebar();?>
<?php get_footer(); ?>
<?php exit; exit(); ?>