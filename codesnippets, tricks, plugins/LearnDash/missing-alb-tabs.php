/*
*
* Add this snippet to the functions.php file of your child theme to fix hidden ALB tabs issue 
*
* Reported at https://kriesi.at/support/topic/alb-and-learndash-missing-content-and-media-elements/
*/

function ava_learndash_mod() {
    echo '<style>
			body.learndash-post-type.block-editor-page.avia-advanced-editor-enabled.avia-block-editor-expand .avia-expanded .avia-fixed-controls {
				top: 200px;
			}
			body.learndash-post-type.block-editor-page.avia-advanced-editor-enabled #avia_builder .avia-builder-main-wrap {
				padding: 180px 0 0 0;
			}
	    </style>';
}
add_action('admin_head', 'ava_learndash_mod');
