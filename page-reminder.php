<?php
/**
 * @package Page Reminder
 * @author Brendan Nee - blinktag.com
 * @version 1
 */
/*
Plugin Name: Page Reminder
Plugin URI: http://blinktag.com
Description: Displays a reminder on the admin page to update posts
Author: Brendan Nee
Version: 1
Author URI: http://blinktag.com
*/


/**
 * Web-accessible wp-content directory.
 */
define('WP_CONTENT_WEB', get_bloginfo('wpurl') . '/wp-content');

/**
 * Web-accessible control panel page.
 */
define('WP_CPANEL', get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=page-reminder');

/**
 * Set absolute plugin directory.
 */
define('PAGE_REMINDER_PLUGINDIR', dirname(__FILE__));

/**
 * Get only the name of the plugin directory.
 */
define('PAGE_REMINDER_PLUGINDIR_NAME', pathinfo(dirname(__FILE__), PATHINFO_BASENAME));

/**
 * Web-accessible URL for the plugin directory.
 */
define('PAGE_REMINDER_PLUGINDIR_WEB', WP_CONTENT_WEB . '/plugins/' . PAGE_REMINDER_PLUGINDIR_NAME);

/**
 * Default cache directory.
 */
define('PAGE_REMINDER_CACHEDIR', dirname(dirname(PAGE_REMINDER_PLUGINDIR)) . '/cache');


ini_set('display_errors', 1); 
	
		
function wp_dashboard_page_reminder ( ) {
	global $wpdb;
	$pages = $wpdb->get_results("SELECT ID, post_title, post_modified FROM $wpdb->posts WHERE post_type='page' AND post_modified < DATE_SUB(NOW(),INTERVAL 90 DAY) AND ID <> '304' AND ID <> '306' ORDER BY post_modified ASC");

		
	if ( $pages && is_array( $pages ) ) {
		$list = array();
		foreach ( $pages as $page ) {
			
				$url = get_edit_post_link( $page->ID );
				$title = _draft_or_post_title( $page->ID );
				$item = "<div style='clear:both'><img src='" . PAGE_REMINDER_PLUGINDIR_WEB . "/warning.gif' alt='warning' style='float:left;padding:4px 10px;' /><h4 style='float:left;padding:4px 0;'><a href='$url' title='" . sprintf( __( 'Edit "%s"' ), attribute_escape( $title ) ) . "'>$title</a> <abbr title='" . date(get_option( 'date_format' ),strtotime($page->post_modified)) . "'>" . date(get_option( 'date_format' ),strtotime($page->post_modified)) . '</abbr></h4></div>';
				if ( $the_content = preg_split( '#\s#', strip_tags( $page->post_content ), 11, PREG_SPLIT_NO_EMPTY ) )
					$item .= '<p>' . join( ' ', array_slice( $the_content, 0, 10 ) ) . ( 10 < count( $the_content ) ? '&hellip;' : '' ) . '</p>';
				$list[] = $item;
		}
?>
	<ul>
		<li><?php echo join( "</li>\n<li>", $list ); ?></li>
	</ul>
	<div style="clear:both;"></div>
<?php
	} else {
		_e('All pages have been updated in the last three months.');
	}
}

function wp_dashboard_add_page_reminder() {
	wp_add_dashboard_widget('page_reminder', 'Pages that Haven\'t Been Updated Recently', 'wp_dashboard_page_reminder');	

	// Tries to put this widget first
	// Globalize the metaboxes array, this holds all the widgets for wp-admin

	global $wp_meta_boxes;
	
	// Get the regular dashboard widgets array 
	// (which has our new widget already but at the end)

	$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
	
	// Backup and delete our new dashbaord widget from the end of the array

	$example_widget_backup = array('page_reminder' => $normal_dashboard['page_reminder']);
	unset($normal_dashboard['page_reminder']);

	// Merge the two arrays together so our widget is at the beginning

	$sorted_dashboard = array_merge($example_widget_backup, $normal_dashboard);

	// Save the sorted array back into the original metaboxes 

	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
} 

add_action('wp_dashboard_setup', 'wp_dashboard_add_page_reminder' );

?>
