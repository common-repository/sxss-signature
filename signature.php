<?php
/*
Plugin Name: sxss Signature
Plugin URI: http://sxss.nw.am
Description: Plugin displays a custom signature under posts and pages.
Author: sxss
Version: 1.4.1
Text Domain: sxss-signature
Domain Path: /languages
*/

// I18n
load_plugin_textdomain('sxss-signature', false, basename( dirname( __FILE__ ) ) . '/languages' );

// get signature
function sxss_signature_get( $args = false )
{
	$signature = get_option('sxss_signature');

	$signature = html_entity_decode($signature);

	$signature = stripslashes($signature);

	$meta = get_the_title();

	if( true == $args['replace'] )
	{
		$signature = str_replace( '[author]', get_the_author(), $signature );
		$signature = str_replace( '[title]', get_the_title(), $signature );
		$signature = str_replace( '[date]', get_the_date(), $signature );
		$signature = str_replace( '[permalink]', get_permalink(), $signature );
	}

	return $signature;
}

// filter signature
function sxss_signature_save()
{
	$signature = $_POST['sxss_signature'];

	$signature = wp_filter_post_kses($signature);

	return $signature;
}

// settingspage
function sxss_signature_settings()
{
	// save settings
	if ($_POST['action'] == 'update')
	{
		$signature = sxss_signature_save();

		update_option('sxss_signature', $signature);

        if( true == isset($_POST["sxss_signature_home"]) && $_POST["sxss_signature_home"] == 1 ) update_option('sxss_signature_home', 1);
		else update_option('sxss_signature_home', 0);

		if( true == isset($_POST["sxss_signature_posts"]) && $_POST["sxss_signature_posts"] == 1 ) update_option('sxss_signature_posts', 1);
		else update_option('sxss_signature_posts', 0);

		if( true == isset($_POST["sxss_signature_pages"]) && $_POST["sxss_signature_pages"] == 1 ) update_option('sxss_signature_pages', 1);
		else update_option('sxss_signature_pages', 0);

		if( true == isset($_POST["sxss_signature_feeds"]) && $_POST["sxss_signature_feeds"] == 1 ) update_option('sxss_signature_feeds', 1);
		else update_option('sxss_signature_feeds', 0);

		$message = '<div id="message" class="updated fade"><p><strong>' . __('Signature updated', 'sxss-signature') . '</strong></p></div>';
	}

	// get settings from db
	$signature = sxss_signature_get();
    if( get_option('sxss_signature_home') == 1 ) $sxss_home = "checked ";
	if( get_option('sxss_signature_posts') == 1 ) $sxss_posts = "checked ";
	if( get_option('sxss_signature_pages') == 1 ) $sxss_pages = "checked ";
	if( get_option('sxss_signature_feeds') == 1 ) $sxss_feeds = "checked ";

	// Setup TinyMCE Editor
	// Thanks for the suggestion to http://premium.wpmudev.org/blog/display-the-full-tinymce-editor-in-wordpress/
	function sxss_signature_tinemce_buttons($buttons)
	{
		$buttons[] = 'fontselect';
		$buttons[] = 'fontsizeselect';
		$buttons[] = 'styleselect';
		$buttons[] = 'backcolor';
		$buttons[] = 'newdocument';
		$buttons[] = 'cut';
		$buttons[] = 'copy';
		$buttons[] = 'charmap';
		$buttons[] = 'hr';
		$buttons[] = 'visualaid';

		return $buttons;
	}

	add_filter( 'mce_buttons_3', 'sxss_signature_tinemce_buttons' );

	function sxss_signature_myformatTinyMCE( $in )
	{
		$in['wordpress_adv_hidden'] = FALSE;
		return $in;
	}

	add_filter( 'tiny_mce_before_init', 'sxss_signature_myformatTinyMCE' );

	// Display Plugin Page

	echo '

	<div class="wrap">

		'.$message.'

		<div id="icon-options-general" class="icon32"><br /></div>

		<h2>' . __('Signature for posts, pages and feeds', 'sxss-signature') . '</h2>

		<form method="post" action="">

		<input type="hidden" name="action" value="update" />

        <p><input type="checkbox" name="sxss_signature_home" value="1" ' . $sxss_home . '/> ' . __('Display signature on the homepage', 'sxss-signature') . '</p>

		<p><input type="checkbox" name="sxss_signature_posts" value="1" ' . $sxss_posts . '/> ' . __('Display signature under posts', 'sxss-signature') . '</p>

		<p><input type="checkbox" name="sxss_signature_pages" value="1" ' . $sxss_pages . '/> ' . __('Display signature under pages', 'sxss-signature') . '</p>

		<p><input type="checkbox" name="sxss_signature_feeds" value="1" ' . $sxss_feeds . '/> ' . __('Display signature under RSS feed content', 'sxss-signature') . '</p>

		<p>' . __('The signature you enter here will be displayed under your posts and pages', 'sxss-signature') . ':</p>

		<style type="text/css" media="screen">

		#wp-sxss_signature-wrap { max-width: 90%; }
		.wp-editor-area		{ max-height: 100px; }

		</style>';

		the_editor($signature,'sxss_signature');

		echo '

		<p class="description" style="margin: 10px 0; clear: both;">

			' . __('You can use the following shortcodes: ', 'sxss-signature') . '[author], [title], [permalink], [date]

		</p>

		<input type="submit" class="button-primary" value="' . __('Save signature', 'sxss-signature') . '" />

		</form>

		<p align="right"><a target="_blank" title="sxss Plugins on wordpress.org" href="https://profiles.wordpress.org/sxss/"><img src="' . plugins_url( 'sxss-plugins.png' , __FILE__ ) . '"></a></p>

	</div>';
}

// register settings page
function sxss_signature_admin_menu()
{
	add_options_page(__('sxss Signature', 'sxss-signature'), __('sxss Signature', 'sxss-signature'), 9, 'sxss_signature', 'sxss_signature_settings');
}

add_action("admin_menu", "sxss_signature_admin_menu");

// display signature
function sxss_signature_anzeigen($content)
{
	// if post, page, feed, or home and signature is enabled for it
	if( ( true == is_single() && get_option('sxss_signature_posts') == 1 ) || ( true == is_page() && get_option('sxss_signature_pages') == 1 ) || ( true == is_feed() && get_option('sxss_signature_feeds') == 1 ) || ( true == is_home() && get_option('sxss_signature_home') == 1 ) )
	{
		// get signature from db
		$signature = sxss_signature_get( array( 'replace' => true ) );

		// if there is no signature
		if(false == isset($signature) || $signature == '')
		{
			return $content;
		}

		// style
		$signature = '<div id="sxss_signature" style="clear: both; margin: 20px 0 25px 0 !important; border-top: 1px solid #EFEFEF; padding-top: 5px;">'.$signature.'</div>';

		return $content . $signature;
	}
	else
	{
		return $content;
	}
}

add_action('the_content', 'sxss_signature_anzeigen');

?>
