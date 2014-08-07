<?php
/*
Plugin Name: Chamber Dashboard Business Directory
Plugin URI: http://chamberdashboard.com
Description: Create a database of the businesses in your chamber of commerce
Version: 0.1
Author: Morgan Kay
Author URI: http://wpalchemists.com
*/

/*  Copyright 2014 Morgan Kay and the Fremont Chamber of Commerce (email : info@chamberdashboard.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// ------------------------------------------------------------------------
// REQUIRE MINIMUM VERSION OF WORDPRESS:                                               
// ------------------------------------------------------------------------


function requires_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );

	if ( version_compare($wp_version, "3.8", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "'".$plugin_data['Name']."' requires WordPress 3.3 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
}
add_action( 'admin_init', 'requires_wordpress_version' );

// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------
// HOOKS TO SETUP DEFAULT PLUGIN OPTIONS, HANDLE CLEAN-UP OF OPTIONS WHEN
// PLUGIN IS DEACTIVATED AND DELETED, INITIALISE PLUGIN, ADD OPTIONS PAGE.
// ------------------------------------------------------------------------

// Set-up Action and Filter Hooks
register_activation_hook(__FILE__, 'cdash_add_defaults');
register_uninstall_hook(__FILE__, 'cdash_delete_plugin_options');
add_action('admin_init', 'cdash_init' );
add_action('admin_menu', 'cdash_add_options_page');
add_filter( 'plugin_action_links', 'cdash_plugin_action_links', 10, 2 );

// Require options stuff
require_once( plugin_dir_path( __FILE__ ) . 'options.php' );


// Initialize language so it can be translated
function cdash_language_init() {
  load_plugin_textdomain( 'cdash', false, 'cdash-business-directory/languages' );
}
add_action('init', 'cdash_language_init');

// Register Custom Taxonomy - Business Cateogory
function cdash_register_taxonomy_business_category() {

	$labels = array(
		'name'                       => _x( 'Business Categories', 'Taxonomy General Name', 'cdash' ),
		'singular_name'              => _x( 'Business Category', 'Taxonomy Singular Name', 'cdash' ),
		'menu_name'                  => __( 'Business Category', 'cdash' ),
		'all_items'                  => __( 'All Business Categories', 'cdash' ),
		'parent_item'                => __( 'Parent Business Category', 'cdash' ),
		'parent_item_colon'          => __( 'Parent Business Category:', 'cdash' ),
		'new_item_name'              => __( 'New Business Category Name', 'cdash' ),
		'add_new_item'               => __( 'Add New Business Category', 'cdash' ),
		'edit_item'                  => __( 'Edit Business Category', 'cdash' ),
		'update_item'                => __( 'Update Business Category', 'cdash' ),
		'separate_items_with_commas' => __( 'Separate Business Categories with commas', 'cdash' ),
		'search_items'               => __( 'Search Business Categories', 'cdash' ),
		'add_or_remove_items'        => __( 'Add or remove Business Category', 'cdash' ),
		'choose_from_most_used'      => __( 'Choose from the most used Business Categories', 'cdash' ),
		'not_found'                  => __( 'Not Found', 'cdash' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'business_category', array( 'business' ), $args );

}

add_action( 'init', 'cdash_register_taxonomy_business_category', 0 );

// Register Custom Taxonomy - Membership Level
function cdash_register_taxonomy_membership_level() {

	$labels = array(
		'name'                       => _x( 'Membership Levels', 'Taxonomy General Name', 'cdash' ),
		'singular_name'              => _x( 'Membership Level', 'Taxonomy Singular Name', 'cdash' ),
		'menu_name'                  => __( 'Membership Level', 'cdash' ),
		'all_items'                  => __( 'All Membership Levels', 'cdash' ),
		'parent_item'                => __( 'Parent Membership Level', 'cdash' ),
		'parent_item_colon'          => __( 'Parent Membership Level:', 'cdash' ),
		'new_item_name'              => __( 'New Membership Level Name', 'cdash' ),
		'add_new_item'               => __( 'Add New Membership Level', 'cdash' ),
		'edit_item'                  => __( 'Edit Membership Level', 'cdash' ),
		'update_item'                => __( 'Update Membership Level', 'cdash' ),
		'separate_items_with_commas' => __( 'Separate Membership Levels with commas', 'cdash' ),
		'search_items'               => __( 'Search Membership Levels', 'cdash' ),
		'add_or_remove_items'        => __( 'Add or remove Membership Level', 'cdash' ),
		'choose_from_most_used'      => __( 'Choose from the most used Membership Levels', 'cdash' ),
		'not_found'                  => __( 'Not Found', 'cdash' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'membership_level', array( 'business' ), $args );

}

add_action( 'init', 'cdash_register_taxonomy_membership_level', 0 );


// Register Custom Post Type - Businesses
function cdash_register_cpt_business() {

	$labels = array(
		'name'                => _x( 'Businesses', 'Post Type General Name', 'cdash' ),
		'singular_name'       => _x( 'Business', 'Post Type Singular Name', 'cdash' ),
		'menu_name'           => __( 'Businesses', 'cdash' ),
		'parent_item_colon'   => __( 'Parent Business:', 'cdash' ),
		'all_items'           => __( 'All Businesses', 'cdash' ),
		'view_item'           => __( 'View Business', 'cdash' ),
		'add_new_item'        => __( 'Add New Business', 'cdash' ),
		'add_new'             => __( 'Add New', 'cdash' ),
		'edit_item'           => __( 'Edit Business', 'cdash' ),
		'update_item'         => __( 'Update Business', 'cdash' ),
		'search_items'        => __( 'Search Businesses', 'cdash' ),
		'not_found'           => __( 'Not found', 'cdash' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'cdash' ),
	);
	$args = array(
		'label'               => __( 'business', 'cdash' ),
		'description'         => __( 'Businesses and Organizations', 'cdash' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes', ),
		'taxonomies'          => array( 'business_category', ' membership_level' ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-shop',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'business', $args );

}

add_action( 'init', 'cdash_register_cpt_business', 0 );


//Create metaboxes
include_once 'wpalchemy/MetaBox.php';
include_once 'wpalchemy/MediaAccess.php';
define( 'MYPLUGINNAME_PATH', plugin_dir_path(__FILE__) );

$wpalchemy_media_access = new WPAlchemy_MediaAccess();

// Add a stylesheet to the admin area to make meta boxes look nice
function cdash_metabox_stylesheet()
{
    if ( is_admin() )
    {
        wp_enqueue_style( 'wpalchemy-metabox', plugins_url() . '/cdash-business-directory/wpalchemy/meta.css' );
    }
}
add_action( 'init', 'cdash_metabox_stylesheet' );

// Create metabox for location/address information
$buscontact_metabox = new WPAlchemy_MetaBox(array
(
    'id' => 'buscontact_meta',
    'title' => 'Locations',
    'types' => array('business'),
    'template' => MYPLUGINNAME_PATH . '/wpalchemy/buscontact.php',
    'mode' => WPALCHEMY_MODE_EXTRACT,
    'prefix' => '_cdash_'
));

// Create metabox for business logo
$buslogo_metabox = new WPAlchemy_MetaBox(array
(
    'id' => 'buslogo_meta',
    'title' => 'Logo',
    'types' => array('business'),
    'template' => MYPLUGINNAME_PATH . '/wpalchemy/buslogo.php',
    'mode' => WPALCHEMY_MODE_EXTRACT,
    'prefix' => '_cdash_'
));

// Create metabox for internal notes
$busnotes_metabox = new WPAlchemy_MetaBox(array
(
    'id' => 'busnotes_meta',
    'title' => 'Notes',
    'types' => array('business'),
    'template' => MYPLUGINNAME_PATH . '/wpalchemy/busnotes.php',
    'mode' => WPALCHEMY_MODE_EXTRACT,
    'prefix' => '_cdash_'
));

/* TODO - make a metabox for custom fields */

// Enqueue stylesheet for single businesses
function cdash_single_business_style() {
	if(is_singular('business')) {
		wp_enqueue_style( 'cdash-business-directory', plugin_dir_url(__FILE__) . 'css/cdash-business-directory.css' );
	}
}

add_action( 'wp_enqueue_scripts', 'cdash_single_business_style' );

// Display single business (filter content)

function cdash_single_business($content) {
	if( is_singular('business') ) {
		$options = get_option('cdash_directory_options');

		// make location/address metabox data available
		global $buscontact_metabox;
		$contactmeta = $buscontact_metabox->the_meta();

		// make logo metabox data available
		global $buslogo_metabox;
		$logometa = $buslogo_metabox->the_meta();

		global $post;

		$business_content .= "<div id='business'>";
		if (($options['sv_thumb']) == "1") { 
			$business_content .= get_the_post_thumbnail( $post->ID, 'full');
		}
		if (($options['sv_logo']) == "1") { 
			$attr = array(
				'class'	=> 'alignleft logo',
			);
			$business_content .= wp_get_attachment_image($logometa['buslogo'], 'full', 0, $attr );
		}
		if (($options['sv_description']) == "1") { 
			$business_content .= $content;
		}
		if (($options['sv_memberlevel']) == "1") { 
			$id = get_the_id();
			$levels = get_the_terms( $id, 'membership_level');
			$business_content .= "<p class='membership'><span>Membership Level:</span>&nbsp;";
			$i = 1;
			foreach($levels as $level) {
				if($i !== 1) {
					$business_content .= ",&nbsp;";
				}
				$business_content .= $level->name;
				$i++;
			}
		}
		if (($options['sv_category']) == "1") { 
			$id = get_the_id();
			$levels = get_the_terms( $id, 'business_category');
			$business_content .= "<p class='categories'><span>Categories:</span>&nbsp;";
			$i = 1;
			foreach($levels as $level) {
				if($i !== 1) {
					$business_content .= ",&nbsp;";
				}
				$business_content .= $level->name;
				$i++;
			}
		}
		$locations = $contactmeta['location'];
		foreach($locations as $location) {
			if($location['donotdisplay'] == "1") {
				continue;
			} else {
				if (($options['sv_name']) == "1" && isset($location['altname'])) { 
					$business_content .= "<div class='location'>";
					$business_content .= "<h3>" . $location['altname'] . "</h3>";
				}
				if (($options['sv_address']) == "1") { 
					$business_content .= "<p class='address'>";
	 					if(isset($location['address'])) {
							$address = $location['address'];
							$business_content .= str_replace("\n", '<br />', $address);
						}
						if(isset($location['city'])) {
							$business_content .= "<br />" . $location['city'] . ",&nbsp;";
						}
						if(isset($location['state'])) {
							$business_content .= $location['state'] . "&nbsp";
						}
						if(isset($location['zip'])) {
							$business_content .= $location['zip'];
						} 
					$business_content .= "</p>";
				}
				if (($options['sv_url']) == "1") { 
					$business_content .= "<p class='website'><a href='" . $location['url'] . " target='_blank'>" . $location['url'] . "</a></p>";
				}
				if (($options['sv_phone']) == "1" && isset($location['phone'])) { 
					$business_content .= "<p class='phone'>";
						$i = 1;
						$phones = $location['phone'];
						foreach($phones as $phone) {
							if($i !== 1) {
								$business_content .= "<br />";
							}
							$business_content .= "<a href='tel:" . $phone['phonenumber'] . "'>" . $phone['phonenumber'] . "</a>";
							if(isset($phone['phonetype'])) {
								$business_content .= "&nbsp;(" . $phone['phonetype'] . "&nbsp;)";
							}
							$i++;
						}
					$business_content .= "</p>";
				}
				if (($options['sv_email']) == "1" && isset($location['email'])) { 
					$business_content .= "<p class='email'>";
						$i = 1;
						$emails = $location['email'];
						foreach($emails as $email) {
							if($i !== 1) {
								$business_content .= "<br />";
							}
							$business_content .= "<a href='mailto:" . $email['emailaddress'] . "'>" . $email['emailaddress'] . "</a>";
							if(isset($email['emailtype'])) {
								$business_content .= "&nbsp;(&nbsp;" . $email['emailtype'] . "&nbsp;)";
							}
							$i++;
						}
					$business_content .= "</p>";
				}
			}
		}
		$business_content .= "</div>";
	$content = $business_content;
	} 

	return $content;

}
add_filter('the_content', 'cdash_single_business');


// Create shortcode for displaying business directory

function cdash_business_directory_shortcode( $atts ) {
	// Set our default attributes
	extract( shortcode_atts(
		array(
			'format' => 'list',  // options: list, grid2, grid3, grid4
			'category' => '', // options: slug of any category
			'level' => '', // options: sluf of any membership level
			'text' => 'excerpt', // options: excerpt, description, none
			'display' => '', // options: address, url, phone, email, location_name, category, level
			'single_link' => 'yes', // options: yes, no
			'perpage' => '-1', // options: any number
			'orderby' => 'title', // options: date, modified, menu_order, rand
			'order' => 'ASC', //options: asc, desc
			'image' => 'logo', // options: logo, featured, none
		), $atts )
	);

	wp_enqueue_style( 'cdash-business-directory', plugin_dir_url(__FILE__) . 'css/cdash-business-directory.css' );
	if($format !== 'list') {
		wp_enqueue_script( 'cdash-business-directory', plugin_dir_url(__FILE__) . 'js/cdash-business-directory.js' );
	}

	// If user wants to display stuff other than the default, turn their display options into an array for parsing later
	if($display !== '') {
  		$displayopts = explode( ", ", $display);
  	}

  	$paged = get_query_var('paged') ? get_query_var('paged') : 1;

	$args = array( 
		'post_type' => 'business',
		'posts_per_page' => $perpage, 
		'paged' => $paged,
	    'order' => $order,
	    'orderby' => $orderby, 	
	    'business_category' => $category,	
	    'membership_level' => $level,								 
	);

	$businessquery = new WP_Query( $args );

	// The Loop
	if ( $businessquery->have_posts() ) :
		$business_list .= "<div id='businesslist' class='" . $format . "'>";
			while ( $businessquery->have_posts() ) : $businessquery->the_post();
				$business_list .= "<div class='business'>";
				if($single_link == "yes") {
					$business_list .= "<h3><a href='" . get_the_permalink() . "'>" . get_the_title() . "</a></h3>";
				} else {
					$business_list .= "<h3>" . get_the_title() . "</h3>";
				}
				$business_list .= "<div class='description'>";
			  	if($image == "logo") {
			  		global $buslogo_metabox;
					$logometa = $buslogo_metabox->the_meta();
				  	$logoattr = array(
						'class'	=> 'alignleft logo',
					);
					if($single_link == "yes") {
						$business_list .= "<a href='" . get_the_permalink() . "'>" . wp_get_attachment_image($logometa['buslogo'], 'thumb', 0, $logoattr ) . "</a>";
					} else {
						$business_list .= wp_get_attachment_image($logometa['buslogo'], 'thumb', 0, $logoattr );
					}
			  	} elseif($image == "featured") {
			  		$thumbattr = array(
						'class'	=> 'alignleft logo',
					);
			  		$business_list .= get_the_post_thumbnail( $post->ID, 'thumb', $thumbattr);
			  	} 
			  	if($text == "excerpt") {
			  		$business_list .= get_the_excerpt();
			  	} elseif($text == "description") {
			  		$business_list .= get_the_content();
			  	}
			  	$business_list .= "</div>";
			  	if($display !== '') {
			  		global $buscontact_metabox;
					$contactmeta = $buscontact_metabox->the_meta();
				  	$locations = $contactmeta['location'];
					foreach($locations as $location) {
						if($location['donotdisplay'] == "1") {
							continue;
						} else {
						  	if(in_array("location_name", $displayopts)) {
						  		$business_list .= "<p class='location-name'>" . $location['altname'] . "</p>";
						  	}
						  	if(in_array("address", $displayopts)) {
								$business_list .= "<p class='address'>";
				 					if(isset($location['address'])) {
										$address = $location['address'];
										$business_list .= str_replace("\n", '<br />', $address);
									}
									if(isset($location['city'])) {
										$business_list .= "<br />" . $location['city'] . ",&nbsp;";
									}
									if(isset($location['state'])) {
										$business_list .= $location['state'] . "&nbsp";
									}
									if(isset($location['zip'])) {
										$business_list .= $location['zip'];
									} 
								$business_list .= "</p>";
						  	}
						  	if(in_array("phone", $displayopts)) {
								$business_list .= "<p class='phone'>";
									$i = 1;
									$phones = $location['phone'];
									foreach($phones as $phone) {
										if($i !== 1) {
											$business_list .= "<br />";
										}
										$business_list .= "<a href='tel:" . $phone['phonenumber'] . "'>" . $phone['phonenumber'] . "</a>";
										if(isset($phone['phonetype'])) {
											$business_list .= "&nbsp;(" . $phone['phonetype'] . "&nbsp;)";
										}
										$i++;
									}
								$business_list .= "</p>";
						  	} 
						  	if(in_array("email", $displayopts)) {
								$business_list .= "<p class='email'>";
									$i = 1;
									$emails = $location['email'];
									foreach($emails as $email) {
										if($i !== 1) {
											$business_list .= "<br />";
										}
										$business_list .= "<a href='mailto:" . $email['emailaddress'] . "'>" . $email['emailaddress'] . "</a>";
										if(isset($email['emailtype'])) {
											$business_list .= "&nbsp;(&nbsp;" . $email['emailtype'] . "&nbsp;)";
										}
										$i++;
									}
								$business_list .= "</p>";
							}
					  	} 
					  	if(in_array("url", $displayopts)) {
					  		$business_list .= "<p class='website'><a href='" . $location['url'] . " target='_blank'>" . $location['url'] . "</a></p>";
					  	} 
			  		}
			  		if(in_array("category", $displayopts)) {
						$id = get_the_id();
						$levels = get_the_terms( $id, 'business_category');
						$business_list .= "<p class='categories'><span>Categories:</span>&nbsp;";
						$i = 1;
						foreach($levels as $level) {
							if($i !== 1) {
								$business_list .= ",&nbsp;";
							}
							$business_list .= $level->name;
							$i++;
						}
				  	}
				  	if(in_array("level", $displayopts)) {
						$id = get_the_id();
						$levels = get_the_terms( $id, 'membership_level');
						$business_list .= "<p class='membership'><span>Membership Level:</span>&nbsp;";
						$i = 1;
						foreach($levels as $level) {
							if($i !== 1) {
								$business_list .= ",&nbsp;";
							}
							$business_list .= $level->name;
							$i++;
						}
				  	}
			  	}
			  	$business_list .= "</div>";
			endwhile;

			// pagination links
			$total_pages = $businessquery->max_num_pages;
			if ($total_pages > 1){
				$current_page = max(1, get_query_var('paged'));
   				$business_list .= "<div class='pagination'>";
			  	$business_list .= paginate_links(array(
			      'base' => get_pagenum_link(1) . '%_%',
			      'format' => '/page/%#%',
			      'current' => $current_page,
			      'total' => $total_pages,
			    ));
			    $business_list .= "</div>";
			}

		$business_list .= "</div>";
	endif;

	return $business_list;
	wp_reset_postdata();
}
add_shortcode( 'business_directory', 'cdash_business_directory_shortcode' );
