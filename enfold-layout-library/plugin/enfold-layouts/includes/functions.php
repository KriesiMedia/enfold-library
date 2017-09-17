<?php
function wp_layout_featured_image($post_ID, $img_size = 'thumbnail') {
    $post_thumbnail_id = get_post_thumbnail_id($post_ID);
    if ($post_thumbnail_id) {
        $post_thumbnail_img = wp_get_attachment_image_src($post_thumbnail_id, $img_size);
        return $post_thumbnail_img[0];
    }
}

function wp_layout_get_layouts_info( $layout_slug = false ){

	$results = array();

	if( $layout_slug ){

		$args = array (
			'post_type'		=> array( 'wp_layout' ),
			'post_status'	=> array( 'publish' ),
			'meta_query'	=> array(
				array(
						'key'     => 'wp_layout_slug',
						'value'   => $layout_slug,
					),
			),
		);

		$query = new WP_Query( $args );
		$posts = get_posts( $args );

		if( $posts ){

			foreach( $posts as $k => $v ){

				$cats = array();
				$layout_cats = wp_get_post_terms( $v->ID, 'layout-category' );

				if( $layout_cats ){
					foreach ($layout_cats as $a => $b) {
						$cats[] = $b->name;
					}
				}

				$results[] = array(
					'id'	=> $v->ID,
					'title'	=> $v->post_title,
					'thumb'	=> wp_layout_featured_image( $v->ID, 'large' ),
					'cats'	=> implode( ',', $cats ),
				);

			}
		}
	}

	return $results;
}

function wp_layouts_get_layout_contents( $layout_id = false ){
	
	$results = array();

	if( $layout_id ){

		$args = array (
			'p'				=> $layout_id,
			'post_type'		=> array( 'wp_layout' ),
			'post_status'	=> array( 'publish' ),
		);

		$query = new WP_Query( $args );
		$posts = get_posts( $args );

		if( $posts ){

			$results = array(
				'title'			=> 	$posts[0]->post_title,
				'featured' 		=> 	wp_layout_featured_image( $posts[0]->ID ),
				'content'		=>	do_shortcode( $posts[0]->post_content ),
			);
		}
	}

	return $results;
}

function wp_layouts_get_layout_code( $layoutSlug = false ){

	$className = 'WP_Layouts_' . str_replace('-', '_', $layoutSlug );

	return 'class ' . $className . '{

	private $slug;
	private $serviceUrl = "' . home_url() . '/wp-json/wp/v2/wp-layouts";
	private $layoutsSlug = "' . $layoutSlug . '";
	private $installConfirmMsg;
	private $installSucessMsg;

	function __construct() {
		
		$this->slug = "wp-layouts-" . $this->layoutsSlug;
		
		$this->installConfirmMsg = __( "You are going to created a new page based on layout \'\'%layout_title%\'\'. Proceed?", $this->slug );
		$this->installSucessMsg = __( "Page created successful!", $this->slug );
		$this->installErrorMsg = __( "An error occured on page creation! Please, try again.", $this->slug );

		add_action( "wp_ajax_create_layout_page", array( $this, "create_layout_page" ) );
	}

	public function layouts_shortcode( $attrs = array(), $content = "" ) {
        return $this->load_admin_page_content();
    }

	public function load_admin_page_content() {

		$layoutCats = array();
		$theData = false;
		$response = $this->exe_curl_get( $this->serviceUrl . "/layouts/" . $this->layoutsSlug  );

		if( isset( $response["result"]["layouts_data"] ) ){

			$theData =  $response["result"]["layouts_data"];

			foreach ( $theData as $k => $v ) {
				if( "" !== $v["cats"] ){
					$t = explode(",", $v["cats"]);
					$layoutCats = array_merge($layoutCats, $t);
				}
			}

			if( ! empty( $layoutCats ) ){
				$layoutCats = array_unique( $layoutCats );
			}
		}

		if( $theData ){
		?>
		
		<style>
			.wp-layouts{
				width:98%;
				display:inline-block;
			}

			.wp-layouts,
			.wp-layouts *{
				-webkit-box-sizing: border-box;
				-moz-box-sizing: border-box;
				box-sizing: border-box;
			}

			.wp-layouts ul.filter{
				display:inline-block;
				width:100%;
				margin:15px 0 0;
				line-height:1em;
			}

			.wp-layouts ul.filter li{
				position:relative;
				width:auto;
				float:left;
				display: inline-block;
				margin:0 15px 15px 0;
			}

			.wp-layouts ul.filter li a{
				display:inline-block;
				min-width: 60px;
				padding:10px 15px 11px;
				text-align:center;
				font-size:13px;
				font-weight: 600;
				text-decoration: none;
				background-color:#fff;
				-webkit-box-shadow: 0 1px 1px rgba(0,0,0,.02);
				box-shadow: 0 1px 1px rgba(0,0,0,.02);
			}

			.wp-layouts ul.filter li a:hover{
				-webkit-box-shadow: 0 1px 0px rgba(0,0,0,.04);
				box-shadow: 0 1px 0px rgba(0,0,0,.04);
			}

			.wp-layouts ul.filter li a.active{
				color:#f1f1f1;
				background-color:#444;
				-webkit-box-shadow: 0;
				box-shadow: 0;
			}

			.wp-layouts .layouts-wrap{
				margin: 0 -10px;
			}

			.wp-layout{
				position:relative;
				float:left;
				display:inline-block;
                width: 25%;
			}

			.wp-layout.hidden-layout{
				display:none;
				opacity:0;
			}

			.wp-layouts-inner{
				margin:8px;
				padding:0 0 10px;
				border:1px solid #e7e7e7;
				background-color:#fff;
				-webkit-box-shadow: 0 1px 1px rgba(0,0,0,.04);
				box-shadow: 0 1px 1px rgba(0,0,0,.04);
			}

			.wp-layouts-inner .layout-image{
				position:relative;
				content: "";
				width:100%;
				display:block;
				padding-top: 35%;
			}

			.wp-layouts-inner .layout-image span{
				position: absolute;
			    top: 0;
			    left: 0;
			    right: 0;
			    bottom: 0;
			    background-repeat:no-repeat;
				background-position:center;
				background-size:cover;
			}

			.wp-layouts-inner .layout-title{
				display:block;
				padding:5px 10px;
				font-size: 14px;
				font-weight: 600;
				line-height: 1.5em;
			}

			.wp-layouts-inner .actions{
				text-align: right;
				min-height:25px;
				padding:5px 10px 0;
			}

			.wp-layouts-inner .actions > img{
				display:none;
				padding-top:5px;
				padding-right:5px;
			}

			.wp-layouts-inner .button{
				text-decoration: none;
			}

			.wp-layouts-inner .actions.loading > img{
				display:inline-block;
			}

			.wp-layouts-inner .actions.loading .button{
				display:none;
			}

 			@media only screen and (max-width:1023px){
 				.wp-layout{
 					width:50%;
 				}
 			}

 			@media only screen and (max-width:767px){
 				.wp-layout{
 					width:100%;
 				}
 			}
 		</style>

		<script>
			(function( $ ) {

				"use strict";

				jQuery(function() {

					function wp_filter_layouts( val ) {

						var x = jQuery(".wp-layout[data-categories*=\"" + val + "\"]");

						val = val && x.length ? val : "all";

						switch( val ){
							case "all":
								jQuery(".wp-layout").removeClass("hidden-layout");
							break;
							default:
								x.removeClass("hidden-layout");
								jQuery(".wp-layout").not( "[data-categories*=\"" + val + "\"]" ).addClass("hidden-layout");
							break;
						}

						jQuery(".wp-layouts ul.filter li a[data-filter=\"" + val + "\"]" ).addClass("active");
						jQuery(".wp-layouts ul.filter li a").not( "[data-filter=\"" + val + "\"]" ).removeClass("active");

						localStorage.setItem( "wp_layout[?php echo $this->layoutsSlug; ?>]", val );
					}

					jQuery(".wp-layouts ul.filter li a").on("click", function(){
						wp_filter_layouts( jQuery( this ).data("filter") );
						return false;
					});

					jQuery(".install-layout").on("click", function(){

						var $this = jQuery(this),
							lId = $this.data("layoutid"),
							lTitle = $this.data("layouttitle"),
							confirmMsg = "<?php echo $this->installConfirmMsg; ?>";

						if( window.confirm( confirmMsg.replace( "%layout_title%", lTitle ) ) ) {

							$this.parent().addClass("loading");

							jQuery.ajax({
					            type: "POST",
					            url: "<?php echo admin_url( "admin-ajax.php" ); ?>",
					            dataType: "json",
					            data: { 
					            	action: "create_layout_page",
					            	nonce: "<?php echo wp_create_nonce( "wp-layout" ); ?>",
					            	id: lId,
					            	title: lTitle,
					            },
					            success: function (data, textStatus, XMLHttpRequest) {
					            	if( "undefined" !== typeof( data[0] ) && "ok" === data[0] ){
					            		alert( "<?php echo $this->installSucessMsg; ?>" );
					            	}
					            	else{
					            		alert( "<?php echo $this->installErrorMsg; ?>" );
					            	}
					            	$this.parent().removeClass("loading");
					            },
					            error: function (data, textStatus, XMLHttpRequest) {
					            	alert( "<?php echo $this->installErrorMsg; ?>" );
					            	$this.parent().removeClass("loading");
					                console.log( "An error occurred when try to create layout page." );
					            }
					        });

						}

						return false;
					});

					wp_filter_layouts(localStorage.getItem("wp_layout[?php echo $this->layoutsSlug; ?>]"));
				});

			})( jQuery );

		</script>

		<?php } ?>

		<div class="wp-layouts wrap">

			<?php if( $theData ){ ?>

				<ul class="filter">
					<?php 
					if( count( $layoutCats ) > 1 && count( $theData ) > 1 ) { ?>
						<li><a href="#" title="" data-filter="all"><?php _e( "All", $this->slug ); ?></a></li><?php 
					}
					foreach ( $layoutCats as $k => $v ) {
						?><li><a href="#" title="" data-filter="<?php echo $v; ?>"><?php echo $v; ?></a></li><?php
					}
					?>
				</ul>

				<div class="layouts-wrap">
					<?php
					foreach( $theData as $x => $y ){
						?>
						<div class="wp-layout hidden-layout" data-categories="<?php echo $y["cats"]; ?>">
							<div class="wp-layouts-inner">
								<span class="layout-image"><span style="background-image:url( <?php echo $y["thumb"]; ?>);"></span></span>
								<span class="layout-title"><?php echo $y["title"]; ?></span>
								<div class="actions">
									<img src="<?php echo admin_url( "/images/wpspin_light.gif" ); ?>" alt="" />
									<button class="button button-primary install-layout" data-layouttitle="<?php echo $y["title"]; ?>" data-layoutid="<?php echo $y["id"]; ?>"><?php _e( "Install", $this->slug ); ?></button>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			<?php 
			}
			else{
				echo "<br/>" . __( "Didn\'t found any available layout." );
			} ?>
			<br/>
		</div>

		<?php
	}

	public function create_layout_page() {

		check_ajax_referer( "wp-layouts", "nonce" );

		$return = array();

		$layoutId = isset( $_POST["id"] ) ? $_POST["id"] : false;
		$layoutTitle = isset( $_POST["title"] ) ? $_POST["title"] : false;

		if( $layoutId && $layoutTitle ){
			$layoutContents = $this->exe_curl_get( $this->serviceUrl . "/layout/" . $layoutId );

			if( isset( $layoutContents["result"]["layout_content"] ) ){

				$newPage = array(
						"post_title"	=>	$layoutContents["result"]["layout_content"]["title"],
						"post_content"	=>	$layoutContents["result"]["layout_content"]["content"],
						"post_type"		=> "page",
					);

				wp_insert_post( $newPage );

				$return = array( "ok" );
			}
		}

		print_r( json_encode( $return )  );
		wp_die();
	}

	public function exe_curl_get( $url = "" ){
	    $ret = array();
	    if( "" !== $url ){
	        $curl = curl_init( $url );
	        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	        $ret["result"] = json_decode( curl_exec($curl), true );
	        $ret["status"] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	    }
	    return $ret;
	}

}

$' . $className . ' = new ' . $className . '();
add_shortcode( "wp_layout_' . $layoutSlug . '" , array( $' . $className . ', "layouts_shortcode" ) );';
}
