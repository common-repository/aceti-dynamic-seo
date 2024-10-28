<?php
/*
Plugin Name: Aceti Dynamic SEO
Plugin URI: http://www.citrinedesign.net/blog/projects/
Description: Aceti Dynamic SEO is the easiest to use and the highest performaning SEO solution. Simply install and activate to gain universal control over your code-level SEO.
Version: 1.0
Author: Citrine Design
Author URI: http://www.citrinedesign.net/blog/projects/
License: GPL2

	Copyright 2011  Thomas J Meier  (email : aceti@citrinedesign.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

define('ACETI_VERSION', '1.0');

/**
 *	Containing access.
 */
if ( !function_exists( 'add_action' ) ) {
	echo __("Sorry, I need to be loaded with Wordpress.");
	exit;
}

/**
 *	Checking compatibility. Issue error if not greater than 3.1.
 */
global $wp_db_version;
if ( $wp_db_version < 17056 )
	ac_notice( __('Careful, this plugin is intended for Wordpress 3.1 and newer.') );

/**
 * Load settings.
 */
function get_ac_settings () {

	$settings = get_option( 'ac_settings' );
	
	if ( $settings ) {
		
		$decodedsettings = base64_decode($settings);
		$extractedsettings = unserialize($decodedsettings);

		return $extractedsettings;
		
	} else {

		ac_install();
		
	}

}
$ac_settings = get_ac_settings();
/**
 * Save settings.
 */
function ac_save_settings ( $settings ) {

	$base64settings = base64_encode(serialize($settings));
	update_option( 'ac_settings', $base64settings );
	
}
/**
 *	Installation function.
 */
function ac_install () {
	
	global $ac_settings;
	
	//	Installed yet?
	if ( $ac_settings ) {
	
		//	Do nothing. After 1.0 I'll have to update this.
	
	} else {
		
		//	Set initial settings.
		include_once( 'aceti_admin.php' );
		$ac_default_settings = ac_defaults ();
		ac_save_settings( $ac_default_settings );
		
	}
	
}
register_activation_hook( __FILE__, 'ac_install' );

/**
 *	Take care of business.
 */
if ( !is_admin() ) {

	function that () {
		
		global $ac_settings;
		if (!$ac_settings->activated) return;
		
		include_once( 'AcetiSEO.php' );
		$spOps = new AcetiSEO;
		$spOps->operate();

	}
	add_action('wp', 'that');
	
	function ac_head () {
		do_action('aceti_head');
	}
	
}

/**
 *	Aceti SEO Admin interface.
 */
if ( is_admin() ) {

	include_once( 'aceti_admin.php' );
	
	/**
	 *	Notice and error reporter.
	 */
	$ac_out = '';
	function ac_notice ( $ac_message = '' ) {
		global $ac_out;
		if ( $ac_message != '' ) {
			$ac_out = '<div class="updated fade"><p>' . $ac_message . '</p></div>';
			add_action( 'admin_notices', 'ac_notice');
		} else {
			echo $ac_out;
		}
	}
}

?>