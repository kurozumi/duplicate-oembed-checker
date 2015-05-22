<?php
/*
Plugin Name: Duplicate oEmbed Checker
Version: 0.1-alpha
Description: This plugin provides alert message for duplicate oEmbed url when adding new post.
Author: kurozumi
Author URI: http://a-zumi.net
Plugin URI: http://a-zumi.net
Text Domain: duplicate-oembed-checker
Domain Path: /languages
*/

$Duplicate_oEmbed_Checker = new Duplicate_oEmbed_Checker;
$Duplicate_oEmbed_Checker->register();

class Duplicate_oEmbed_Checker{

	public function register()
	{
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}
	
	public function plugins_loaded()
	{
		add_action('wp_ajax_parse-embed', array($this, 'wp_ajax_parse_embed'), 0);
	}

	/**
	 * 投稿済みのワードプレス対応のoEmbedメディアかチェックする
	 * @global type $post
	 * @global type $wp_embed
	 * @return type
	 */
	public function wp_ajax_parse_embed()
	{
		global $post, $wp_embed, $wpdb;
		
		if ( ! $post = get_post( (int) $_POST['post_ID'] ) )
			return;

		if ( empty( $_POST['shortcode'] ) || ! current_user_can( 'edit_post', $post->ID ) )
			return;

		$shortcode = wp_unslash( $_POST['shortcode'] );
		$url = str_replace( '[embed]', '', str_replace( '[/embed]', '', $shortcode ) );
		
		$sql =<<< __EOS__
			SELECT
				ID
			FROM
				{$wpdb->posts}
			WHERE
				post_content LIKE '%%%s%%' AND
				post_status = %s
__EOS__;
		$post = $wpdb->get_row($wpdb->prepare($sql, array($url, 'publish')));

		if(isset($post->ID) && $post->ID != $_POST['post_ID']){
			wp_send_json_success( array(
				'body' => sprintf("<script>alert('%s');</script>%s", __("投稿済みのURLです。", "duplicate-oembed-checker"), $url)
			) );
		}
	}
}
