<?php
/**
 * **********************************************************************************************
 * @since 5.6.9 integrated as a core feature
 * **********************************************************************************************
 * 
 * @snippet  Duplicate posts and pages without plugins
 * @author   Misha Rudrastyh
 * @url      https://rudrastyh.com/wordpress/duplicate-post.html
 */

// Add the duplicate link to action list for post_row_actions
// for "post" and custom post types
add_filter("post_row_actions", "rd_duplicate_post_link", 10, 2);
// for "page" post type
add_filter("page_row_actions", "rd_duplicate_post_link", 10, 2);

function rd_duplicate_post_link($actions, $post)
{
    if (!current_user_can("edit_posts")) {
        return $actions;
    }

    $url = wp_nonce_url(
        add_query_arg(
            [
                "action" => "rd_duplicate_post_as_draft",
                "post" => $post->ID,
            ],
            "admin.php"
        ),
        basename(__FILE__),
        "duplicate_nonce"
    );
    /****
		Modificamos en https://oxygenados.com para evitar creación de enlaces
		en snippets Advanced Scripts, Scripts Organizer.....
	**/
    $miCat = get_the_category($post->ID);
    $tipoPost = get_post_type($post->ID);
    if ($tipoPost == "page" || $miCat[0]->name != "") {
        /** Cambios por https://oxygenados.com **/

        $actions["duplicate"] =
            '<a href="' .
            $url .
            '" title="Duplicar esta entrada o página" rel="permalink">Duplicar</a>';
    }
    return $actions;
}

/*
 * Function creates post duplicate as a draft and redirects then to the edit post screen
 */
add_action(
    "admin_action_rd_duplicate_post_as_draft",
    "rd_duplicate_post_as_draft"
);

function rd_duplicate_post_as_draft()
{
    // check if post ID has been provided and action
    if (empty($_GET["post"])) {
        wp_die("No post to duplicate has been provided!");
    }

    // Nonce verification
    if (
        !isset($_GET["duplicate_nonce"]) ||
        !wp_verify_nonce($_GET["duplicate_nonce"], basename(__FILE__))
    ) {
        return;
    }

    // Get the original post id
    $post_id = absint($_GET["post"]);

    // And all the original post data then
    $post = get_post($post_id);

    /*
     * if you don't want current user to be the new post author,
     * then change next couple of lines to this: $new_post_author = $post->post_author;
     */
    $current_user = wp_get_current_user();
    $new_post_author = $current_user->ID;

    // if post data exists (I am sure it is, but just in a case), create the post duplicate
    if ($post) {
        // new post data array
        $args = [
            "comment_status" => $post->comment_status,
            "ping_status" => $post->ping_status,
            "post_author" => $new_post_author,
            "post_content" => $post->post_content,
            "post_excerpt" => $post->post_excerpt,
            "post_name" => $post->post_name,
            "post_parent" => $post->post_parent,
            "post_password" => $post->post_password,
            "post_status" => "draft",
            "post_title" => $post->post_title,
            "post_type" => $post->post_type,
            "to_ping" => $post->to_ping,
            "menu_order" => $post->menu_order,
        ];

        // insert the post by wp_insert_post() function
        $new_post_id = wp_insert_post($args);

        /*
         * get all current post terms ad set them to the new post draft
         */
        $taxonomies = get_object_taxonomies(get_post_type($post)); // returns array of taxonomy names for post type, ex array("category", "post_tag");
        if ($taxonomies) {
            foreach ($taxonomies as $taxonomy) {
                $post_terms = wp_get_object_terms($post_id, $taxonomy, [
                    "fields" => "slugs",
                ]);
                wp_set_object_terms(
                    $new_post_id,
                    $post_terms,
                    $taxonomy,
                    false
                );
            }
        }

        // duplicate all post meta
        $post_meta = get_post_meta($post_id);
        if ($post_meta) {
            foreach ($post_meta as $meta_key => $meta_values) {
                if ("_wp_old_slug" == $meta_key) {
                    // do nothing for this meta key
                    continue;
                }

                foreach ($meta_values as $meta_value) {
                    add_post_meta($new_post_id, $meta_key, $meta_value);
                }
            }
        }

        // finally, redirect to the edit post screen for the new draft
        // wp_safe_redirect(
        // 	add_query_arg(
        // 		array(
        // 			'action' => 'edit',
        // 			'post' => $new_post_id
        // 		),
        // 		admin_url( 'post.php' )
        // 	)
        // );
        // exit;
        // or we can redirect to all posts with a message
        wp_safe_redirect(
            add_query_arg(
                [
                    "post_type" =>
                        "post" !== get_post_type($post)
                            ? get_post_type($post)
                            : false,
                    "saved" => "post_duplication_created", // just a custom slug here
                ],
                admin_url("edit.php")
            )
        );
        exit();
    } else {
        wp_die("Ha fallado la creación de la copia :(( ");
    }
}

/*
 * In case we decided to add admin notices
 */
add_action("admin_notices", "rudr_duplication_admin_notice");

function rudr_duplication_admin_notice()
{
    // Get the current screen
    $screen = get_current_screen();

    if ("edit" !== $screen->base) {
        return;
    }

    //Checks if settings updated
    if (isset($_GET["saved"]) && "post_duplication_created" == $_GET["saved"]) {
        echo '<div class="notice notice-success is-dismissible"><p>Se ha creado la copia.</p></div>';
    }
}
