<?php
// Register Custom Post Type.
function wp_layout_post_type() {

	$labels = array(
		'name'                  => _x( 'Layouts', 'Post Type General Name', WP_LAYOUTS_SLUG ),
		'singular_name'         => _x( 'Layout', 'Post Type Singular Name', WP_LAYOUTS_SLUG ),
		'menu_name'             => __( 'Layouts', WP_LAYOUTS_SLUG ),
		'name_admin_bar'        => __( 'Layout', WP_LAYOUTS_SLUG ),
		'archives'              => __( 'Layout Archives', WP_LAYOUTS_SLUG ),
		'parent_item_colon'     => __( 'Layout:', WP_LAYOUTS_SLUG ),
		'all_items'             => __( 'All Layouts', WP_LAYOUTS_SLUG ),
		'add_new_item'          => __( 'Add New Layout', WP_LAYOUTS_SLUG ),
		'add_new'               => __( 'Add New', WP_LAYOUTS_SLUG ),
		'new_item'              => __( 'New Layout', WP_LAYOUTS_SLUG ),
		'edit_item'             => __( 'Edit Layout', WP_LAYOUTS_SLUG ),
		'update_item'           => __( 'Update Layout', WP_LAYOUTS_SLUG ),
		'view_item'             => __( 'View Layout', WP_LAYOUTS_SLUG ),
		'search_items'          => __( 'Search Layout', WP_LAYOUTS_SLUG ),
		'not_found'             => __( 'Not found', WP_LAYOUTS_SLUG ),
		'not_found_in_trash'    => __( 'Not found in Trash', WP_LAYOUTS_SLUG ),
		'featured_image'        => __( 'Featured Image', WP_LAYOUTS_SLUG ),
		'set_featured_image'    => __( 'Set featured image', WP_LAYOUTS_SLUG ),
		'remove_featured_image' => __( 'Remove featured image', WP_LAYOUTS_SLUG ),
		'use_featured_image'    => __( 'Use as featured image', WP_LAYOUTS_SLUG ),
		'insert_into_item'      => __( 'Insert into code patch', WP_LAYOUTS_SLUG ),
		'uploaded_to_this_item' => __( 'Uploaded to this code patch', WP_LAYOUTS_SLUG ),
		'items_list'            => __( 'Layouts list', WP_LAYOUTS_SLUG ),
		'items_list_navigation' => __( 'Layouts list navigation', WP_LAYOUTS_SLUG ),
		'filter_items_list'     => __( 'Filter layout list', WP_LAYOUTS_SLUG ),
	);
	$args = array(
		'label'                 => __( 'Layout', WP_LAYOUTS_SLUG ),
		'description'           => __( 'Layout Description', WP_LAYOUTS_SLUG ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail' ),
		'taxonomies'			=> array( 'layout-category' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 75,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => false,		
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'capability_type'       => 'page',
	);
	register_post_type( 'wp_layout', $args );
}
add_action( 'init', 'wp_layout_post_type', 0 );

// Register Custom Taxonomy
function layout_category_taxonomy() {

	$labels = array(
		'name'                       => _x( 'Categories', 'Taxonomy General Name', WP_LAYOUTS_SLUG ),
		'singular_name'              => _x( 'Category', 'Taxonomy Singular Name', WP_LAYOUTS_SLUG ),
		'menu_name'                  => __( 'Categories', WP_LAYOUTS_SLUG ),
		'all_items'                  => __( 'All Categories', WP_LAYOUTS_SLUG ),
		'parent_item'                => __( 'Parent Category', WP_LAYOUTS_SLUG ),
		'parent_item_colon'          => __( 'Parent Category:', WP_LAYOUTS_SLUG ),
		'new_item_name'              => __( 'New Category Name', WP_LAYOUTS_SLUG ),
		'add_new_item'               => __( 'Add New Category', WP_LAYOUTS_SLUG ),
		'edit_item'                  => __( 'Edit Category', WP_LAYOUTS_SLUG ),
		'update_item'                => __( 'Update Category', WP_LAYOUTS_SLUG ),
		'view_item'                  => __( 'View Category', WP_LAYOUTS_SLUG ),
		'separate_items_with_commas' => __( 'Separate categories with commas', WP_LAYOUTS_SLUG ),
		'add_or_remove_items'        => __( 'Add or remove categories', WP_LAYOUTS_SLUG ),
		'choose_from_most_used'      => __( 'Choose from the most used', WP_LAYOUTS_SLUG ),
		'popular_items'              => __( 'Popular Categories', WP_LAYOUTS_SLUG ),
		'search_items'               => __( 'Search Categories', WP_LAYOUTS_SLUG ),
		'not_found'                  => __( 'Not Found', WP_LAYOUTS_SLUG ),
		'no_terms'                   => __( 'No categories', WP_LAYOUTS_SLUG ),
		'items_list'                 => __( 'Categories list', WP_LAYOUTS_SLUG ),
		'items_list_navigation'      => __( 'Categories list navigation', WP_LAYOUTS_SLUG ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => false,
	);
	register_taxonomy( 'layout-category', array( 'wp_layout' ), $args );
}
add_action( 'init', 'layout_category_taxonomy', 0 );

// Add new and reorder columns.
function wp_layout_columns_head( $defaults ) {

	$tmp = array(
		'title'	=> $defaults['title'],
		'theme_slug' => __( 'Theme Slug', WP_LAYOUTS_SLUG ),
		'featured_image' => __( 'Featured Image', WP_LAYOUTS_SLUG ),
		'layout_categories'	=> __( 'Categories', WP_LAYOUTS_SLUG ),
		'get_code'	=> __( 'Layout code', WP_LAYOUTS_SLUG ),
		'shortcode'	=> __( 'Shortcode', WP_LAYOUTS_SLUG ),
		'date'	=> $defaults['date'],
	);
    return $tmp;
}
add_filter( 'manage_wp_layout_posts_columns', 'wp_layout_columns_head' );

function wp_layout_columns_content( $column_name, $post_ID ) {

	$layoutSlug = get_post_meta( $post_ID, 'wp_layout_slug', true );

	if( '' === $layoutSlug ){
		$isValidPatch = false;
		$invalidMsg[] = __( '', WP_LAYOUTS_SLUG );
	}


    if( 'theme_slug' === $column_name ) {
    	if( ! $layoutSlug ){
    		echo '<strong class="error-msg">- ' . __( "Slug is missing" , WP_LAYOUTS_SLUG ) . ' -</strong>';
    	}
    	else{
    		echo '<strong>' . $layoutSlug . '</strong>';
    	}
    }

    if( 'layout_categories' === $column_name ){
    	$cats = wp_get_post_terms( $post_ID, 'layout-category' );

    	if( ! empty( $cats ) ){
    		$tmp = array();
    		foreach( $cats as $x => $y ){
    			$tmp[] = '<strong>' . $y->name . '</strong>';
    		}
    		echo implode(', ', $tmp);
    	}
    	else{
    		echo '-';
    	}
    }
	
    if( 'get_code' === $column_name && $layoutSlug ) {
    	$getCode_link = home_url() . '/wp-json/wp/v2/wp-layouts/code/' . $layoutSlug;
    	echo '<a href="' . $getCode_link . '" title="" target="_blank"><strong>' . __( "Get code", WP_LAYOUTS_SLUG ) . '</strong></a>';
    }

    if( 'shortcode' === $column_name ) {
    	if( $layoutSlug ){
    		echo '<small><input type="text" value="[wp_layout_' . $layoutSlug . ']" readonly /></small>';
    	}
    }
}
add_action( 'manage_wp_layout_posts_custom_column', 'wp_layout_columns_content', 10, 2 );

function wp_layout_meta_boxes() {
	
	// Add 'Patch data' metabox.
	add_meta_box( 'layout-data', __( 'Layout data', WP_LAYOUTS_SLUG ), 'wp_layout_data_form', 'wp_layout' );

	// Remove 'Slug' metabox.
	remove_meta_box( 'slugdiv', 'wp_layout', 'normal' );
}
add_action( 'add_meta_boxes', 'wp_layout_meta_boxes', 100 );

function save_wp_layout_meta_boxes( $post_id ) {
	if( isset( $_POST['post_type'] ) && 'wp_layout' === $_POST['post_type'] ){
	    if ( array_key_exists('wp-layouts-slug', $_POST ) ) {
	        update_post_meta( $post_id, 'wp_layout_slug', sanitize_title( trim( $_POST['wp-layouts-slug'] ) ) );
	    }
	}
}
add_action( 'save_post', 'save_wp_layout_meta_boxes' );

function wp_layout_data_form( $post ){
	global $WpeditLayout;
	$WpeditLayout = 'true';
	$layoutSlug = get_post_meta( $post->ID, 'wp_layout_slug', true );
	?>
	<div class="layout-data-wrap">
		<div class="field">
			<label for="wp-layouts-slug"><?php _e( 'Theme slug', WP_LAYOUTS_SLUG ); ?> : </label>
			<input type="text" name="wp-layouts-slug" id="wp-layouts-slug" value="<?php echo $layoutSlug; ?>" />
		</div>
  	</div>
	<?php
}
