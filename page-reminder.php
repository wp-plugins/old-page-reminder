<?php
/**
 * @package Old Page Reminder
 * @author Brendan Nee - blinktag.com
 * @version 0.2.1
 */
/*
Plugin Name: Old Page Reminder
Plugin URI: http://blinktag.com
Description: Displays a reminder on the admin page to update posts
Author: Brendan Nee
Version: 0.2.1
Author URI: http://blinktag.com
*/


/**
 * Web-accessible wp-content directory.
 */
define('WP_CONTENT_WEB', get_bloginfo('wpurl') . '/wp-content');

/**
 * Web-accessible control panel page.
 */
define('WP_CPANEL', get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=old-page-reminder');

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


function oldpage_init($days=90)
{

    add_option('oldpage-days', 90);

	add_action('wp_dashboard_setup', 'wp_dashboard_add_page_reminder' );

	// Hook for adding admin menus
	add_action('admin_menu', 'mt_add_pages');
}

// action function for above hook
function mt_add_pages() {
    // Add a new submenu under Options:
    add_options_page('Old Page Reminder', 'Old Page Reminder', 'administrator', 'old-page-reminder', 'mt_options_page');
}

// mt_options_page() displays the page content for the Test Options submenu
function mt_options_page() {
	
 // variables for the field and option names 
    $opt_name = 'oldpage-days';
    $hidden_field_name = 'mt_submit_hidden';
    $data_field_name = 'oldpage-days';

    // Read in existing option value from database
    $opt_val = get_option( $opt_name );

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val = $_POST[ $data_field_name ];

        // Save the posted value in the database
        update_option( $opt_name, $opt_val );

        // Put an options updated message on the screen

?>
	<div class="updated"><p><strong><?php _e('Options saved.', 'mt_trans_domain' ); ?></strong></p></div>
	<?php

	    }

	    // Now display the options editing screen

	    echo '<div class="wrap">';

	    // header

	    echo "<h2>" . __( 'Old Page Reminder', 'mt_trans_domain' ) . "</h2>";

	    // options form

	    ?>

	<form name="form1" method="post" action="">
	<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

	<p><?php _e("Number of Days:", 'mt_trans_domain' ); ?> 
	<input type="text" name="<?php echo $data_field_name; ?>" value="<?php echo $opt_val; ?>" size="20"><br>
	<?php _e("How old should posts be before they show up on the dashboard Old Page Reminder box?"); ?>
	</p><hr />

	<p class="submit">
	<input type="submit" name="Submit" class="button-primary" value="<?php _e('Update Options', 'mt_trans_domain' ) ?>" />
	</p>

	</form>
	</div>

	<?php
}
		
function wp_dashboard_page_reminder ( ) {
	global $wpdb;
	
	$days = get_option('oldpage-days');
	$pages = $wpdb->get_results("SELECT ID, post_title, post_modified FROM $wpdb->posts WHERE post_type='page' AND post_modified < DATE_SUB(NOW(),INTERVAL $days DAY) AND ID <> '304' AND ID <> '306' ORDER BY post_modified ASC");

		
	if ( $pages && is_array( $pages ) ) {
		$list = array();
		foreach ( $pages as $page ) {
			
				$url = get_edit_post_link( $page->ID );
				$title = _draft_or_post_title( $page->ID );
				$item = "<div style='clear:both'><h4 style='float:left;padding:4px 0;'><a href='$url' title='" . sprintf( __( 'Edit "%s"' ), attribute_escape( $title ) ) . "'><img src='" . PAGE_REMINDER_PLUGINDIR_WEB . "/warning.gif' alt='warning' style='float:left;padding-right: 10px; width:16px;' /> $title</a></h4><div style='float:left;padding:4px 3px;'>" . date(get_option( 'date_format' ),strtotime($page->post_modified)) . '</div></div>';
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
		_e("All pages have been updated in the last $days days.");
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

oldpage_init();

?>
