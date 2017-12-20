<?php
global $avia_config;

//avia pages holds the data necessary for backend page creation
$avia_pages = array(

	array( 'slug' => 'avia', 		'parent'=>'avia', 'icon'=>"new/spanner-screwdriver-7@3x.png" , 	'title' =>  __('Theme Options', 'avia_framework')),
	array( 'slug' => 'layout', 		'parent'=>'avia', 'icon'=>"new/window-within-7@3x.png",			'title' =>  __('General Layout', 'avia_framework')),
	array( 'slug' => 'styling', 	'parent'=>'avia', 'icon'=>"new/color-palette-7@3x.png", 		'title' =>  __('General Styling', 'avia_framework')),
	array( 'slug' => 'customizer', 	'parent'=>'avia', 'icon'=>"new/magic-wand-7@3x.png", 			'title' =>  __('Advanced Styling', 'avia_framework')),
	array( 'slug' => 'menu', 		'parent'=>'avia', 'icon'=>"new/custom-menu@3x.png", 				'title' =>  __('Main Menu', 'avia_framework')),
	array( 'slug' => 'header', 		'parent'=>'avia', 'icon'=>"new/layout-arrange-02-7@3x.png", 	'title' =>  __('Header', 'avia_framework')),
	array( 'slug' => 'sidebars', 	'parent'=>'avia', 'icon'=>"new/layout-arrange-13-7@3x.png", 	'title' =>  __('Sidebar Settings', 'avia_framework')),
	array( 'slug' => 'footer', 		'parent'=>'avia', 'icon'=>"new/layout-reverse@3x.png", 			'title' =>  __('Footer', 'avia_framework')),
	array( 'slug' => 'builder', 	'parent'=>'avia', 'icon'=>"new/window-three-7@3x.png", 			'title' =>  __('Layout Builder', 'avia_framework')),
	array( 'slug' => 'blog', 		'parent'=>'avia', 'icon'=>"new/note-write-7@3x.png", 			'title' =>  __('Blog Layout', 'avia_framework')),
	array( 'slug' => 'social', 		'parent'=>'avia', 'icon'=>"new/circle-user-7@3x.png", 			'title' =>  __('Social Profiles', 'avia_framework')),
	array( 'slug' => 'newsletter', 	'parent'=>'avia', 'icon'=>"new/newspaper-7@3x.png", 			'title' =>  __('Newsletter', 'avia_framework')),
	array( 'slug' => 'google', 		'parent'=>'avia', 'icon'=>"new/paper-map-7@3x.png", 			'title' =>  __('Google Services', 'avia_framework')),
);

if(class_exists( 'woocommerce' ))
{
	$avia_pages[] = array( 'slug' => 'shop', 'parent'=>'avia', 'icon'=>"new/shopping-cart-7@3x.png", 'title' =>  __('Shop Options','avia_framework') );
}


if(!current_theme_supports('avia_disable_import_export')){
	$avia_pages[] = array( 'slug' => 'demo', 		'parent'=>'avia', 'icon'=>"new/window-up-7@3x.png", 'title' => __('Demo Import', 'avia_framework'));
	$avia_pages[] = array( 'slug' => 'upload', 		'parent'=>'avia', 'icon'=>"new/connect-arrow-up-down-7@3x.png", 'title' => __('Import/Export', 'avia_framework'));
}





//required for the general styling color schemes
include('register-backend-styles.php');

//required for the general styling google fonts
include('register-backend-google-fonts.php');

//required for the advanced styling wizard
include('register-backend-advanced-styles.php');


/**
 * Allow to include a user defined file to add or alter backend styles
 * 
 * @since 4.2.1
 * @return string		full path to the include file ( not a relative path !!! )
 */
$custom_path = apply_filters( 'avf_register_custom_backend_styles', '' );
if( ! empty( $custom_path ) && file_exists( $custom_path ) )
{
	include_once $custom_path;
}


/*builder*/


$avia_elements[] = array(
		"name" 	=> __("Disable advance layout builder preview in backend", 'avia_framework'),
		"desc" 	=> __("Check to disable the live preview of your advanced layout builder elements", 'avia_framework'),
		"id" 	=> "preview_disable",
		"type" 	=> "checkbox",
		"std"	=> "",
		"slug"	=> "builder");


$avia_elements[] = array(
		"name" 	=> __("Show element options for developers", 'avia_framework'),
		"desc" 	=> __("If checked this will display developer options like custom CSS classes or IDs", 'avia_framework'),
		"id" 	=> "developer_options",
		"type" 	=> "checkbox",
		"std"	=> "",
		"slug"	=> "builder");



$loack_alb = "checkbox";

if(!current_user_can('switch_themes'))
{
	$loack_alb = "hidden";
}

$avia_elements[] = array(	"slug"	=> "builder", "type" => "visual_group_start", "id" => "avia_lock_alb", "nodescription" => true);

$avia_elements[] = array(
		"name" 	=> __("Lock advanced layout builder", 'avia_framework'),
		"desc" 	=> __("This removes the ability to move or delete existing template builder elements, or add new ones, for everyone who is not an administrator. The content of an existing element can still be changed by everyone who can edit that entry.", 'avia_framework'),
		"id" 	=> "lock_alb",
		"type" 	=> $loack_alb,
		"std"	=> "",
		"slug"	=> "builder");	


$avia_elements[] = array(
		"name" 	=> __("Lock advanced layout builder for admins as well?", 'avia_framework'),
		"desc" 	=> __("This will lock the elements for all administrators including you, to prevent accidental changing of a page layout. In order to change a page layout later, you will need to uncheck this option first", 'avia_framework'),
		"id" 	=> "lock_alb_for_admins",
		"type" 	=> $loack_alb,
		"std"	=> "",
		"required" => array('lock_alb','{true}'),
		"slug"	=> "builder");	

$avia_elements[] = array(	"slug"	=> "builder", "type" => "visual_group_end", "id" => "avia_lock_alb_close", "nodescription" => true);		



$avia_elements[] =	array(
					"slug"	=> "builder",
					"name" 	=> __("Automated Schema.org HTML Markup", 'avia_framework'),
					"desc" 	=> __("The theme adds generic HTML schema markup to your template builder elements to provide additional context for search engines. If you want to add your own specific markup via plugins or custom HTML code, you can deactivate this setting", 'avia_framework'),
					"id" 	=> "markup",
					"type" 	=> "select",
					"std" 	=> "",
					"no_first"=>true,
					"subtype" => array( __('Not activated', 'avia_framework') =>'inactive',
										__('Activated', 'avia_framework') =>'',
										));
	










/*menu*/
$iconSpan = "<span class='pr-icons'>
				<img src='".AVIA_IMG_URL."icons/social_facebook.png' alt='' />
				<img src='".AVIA_IMG_URL."icons/social_twitter.png' alt='' />
				<img src='".AVIA_IMG_URL."icons/social_flickr.png' alt='' />
			</span>";

$frontendheader_label = __("A rough layout preview of the main menu", 'avia_framework');
			
$avia_elements[] =	array(
					"slug"	=> "menu",
					"id" 	=> "main_menu_preview",
					"type" 	=> "target",
					"std" 	=> "
					<style type='text/css'>
					
					#avia_options_page #avia_main_menu_preview{background: #f8f8f8; padding: 30px;border-bottom: 1px solid #e5e5e5; margin-bottom: 25px;}
					#av-main-menu-preview-container{color:#999; border:1px solid #e1e1e1; padding:0px 45px; overflow:hidden; background-color:#fff; position: relative;}
					
					#avia_options_page #pr-main-area{line-height:69px; overflow:hidden;}
					
					.main-menu-wrap{float:right; height:70px; line-height:70px;}
					
					
					[data-av_set_global_tab_active='av_display_burger'] .av-header-area-preview-menu-only #av-menu-overlay{display:block;}
					[data-av_set_global_tab_active='av_display_burger'] .av-header-area-preview-menu-only #pr-burger-menu{display:block;}
					[data-av_set_global_tab_active='av_display_burger'] #pr-menu #pr-menu-inner{display:none;}
					
					
					#av-menu-overlay{position: absolute; left:31px; display:none; bottom: 31px; top: 54px; right: 31px; background: rgba(0,0,0,0.2); z-index: 1;}
					#av-menu-overlay .av-overlay-menu-item{display:block; padding:8px 20px; border-bottom: 1px solid #e1e1e1;}
					#av-menu-overlay .av-overlay-menu-item-sub{display:block; color:#999;}
					#av-menu-overlay-scroll{position:absolute; top:0; right:0; bottom:0; width:280px; background:#fff; padding-top:70px; color:#666;}
					[data-submenu_visibility*='av-submenu-hidden'] #av-menu-overlay .av-overlay-menu-item-sub{display:none;}
					[data-burger_size*='av-small-burger-icon'] #pr-burger-menu{    -ms-transform: scale(0.6); transform: scale(0.6);}
					
					
					[data-overlay_style='av-overlay-full'] #av-menu-overlay-scroll{background:transparent; color:#fff; width:100%; text-align: center;}
					[data-overlay_style='av-overlay-full'] #av-menu-overlay .av-overlay-menu-item{border:none; font-size:16px;}
					[data-overlay_style='av-overlay-full'] #av-menu-overlay{ background: rgba(0,0,0,0.8);}
					[data-av_set_global_tab_active='av_display_burger'] [data-overlay_style='av-overlay-full'] #pr-burger-menu span{border-color:#fff;}
					
					
					[data-overlay_style*='av-overlay-side-minimal'] #av-menu-overlay-scroll{display:table; height:100%;padding:0;}
					[data-overlay_style*='av-overlay-side-minimal'] #av-menu-overlay-scroll > *{display:table-cell; height:100%; vertical-align:middle;}
					[data-overlay_style*='av-overlay-side-minimal'] #av-menu-overlay .av-overlay-menu-item{border:none;}
					
					</style>
					<div class='av-header-area-preview av-header-area-preview-menu-only' >
					
						<div id='av-menu-overlay'>					
							<div id='av-menu-overlay-scroll'>
									<div id='av-menu-overlay-scroll-inner'>
									<span class='av-overlay-menu-item'>Home</span>
									<span class='av-overlay-menu-item'>About</span>
									<span class='av-overlay-menu-item av-overlay-menu-item-sub'>- Team</span>
									<span class='av-overlay-menu-item av-overlay-menu-item-sub'>- History</span>
									<span class='av-overlay-menu-item'>Contact</span>
								</div>
							</div>
						</div>
						
						<div id='pr-stretch-wrap' >
							<small class='live_bg_small'>{$frontendheader_label}</small>
							<div id='pr-header-style-wrap' >
								<div id='pr-phone-wrap' >
									<div id='pr-social-wrap' >
										<div id='pr-seconary-menu-wrap' >
											<div id='pr-menu-2nd'>{$iconSpan}<span class='pr-secondary-items'>Login | Signup | etc</span><span class='pr-phone-items'>Phone: 555-4432</span></div>
											<div id='avia_header_preview' >
												<div id='pr-main-area' >
													<img id='pr-logo' src='".AVIA_BASE_URL."images/layout/logo_modern.png' alt=''/>
													<div id='pr-main-icon'>{$iconSpan}</div>
													<div id='pr-menu'>
													
													
													<span id='pr-menu-inner'><span class='pr-menu-single pr-menu-single-first'>Home</span><span class='pr-menu-single'>About</span><span class='pr-menu-single'>Contact</span></span> <img id='search_icon' src='".AVIA_BASE_URL."images/layout/search.png'  alt='' />
													<div id='pr-burger-menu'>
														<span class='burger-top'></span>
														<span class='burger-mid'></span>
														<span class='burger-low'></span>
													</div>
													
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div id='pr-content-area'> Content / Slideshows / etc 
							<div class='inner-content'><p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. </p>
							
							<p>Donec quam felis, ultricies nec, pellentesque eu, pretium sem.Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium sem.</p>
							
							<p>Donec quam felis, ultricies nec, pellentesque eu, pretium sem.Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium sem.</p>
							
							</div>
							</div>
						</div>
					</div>
					",
					"nodescription" => true
					);

//START TAB CONTAINER
$avia_elements[] = array(	"slug"	=> "menu", "type" => "visual_group_start", "id" => "avia_tab1", "nodescription" => true, 'class'=>'avia_tab_container avia_set');

// Start TAB
$avia_elements[] = array(	"slug"	=> "menu", "type" => "visual_group_start", "id" => "avia_tab5", "nodescription" => true, 'class'=>'avia_tab avia_tab2','name'=>__('General', 'avia_framework'));


$avia_elements[] =	array(
					"slug"	=> "menu",
					"name" 	=> __("Menu Items for Desktop", 'avia_framework'),
					"desc" 	=> __("Choose how you want to display the menu items on desktop computers. If you choose to display the 'burger' icon on desktop computers it will also be used on tablets and mobile devices ", 'avia_framework'),
					"id" 	=> "menu_display",
					"type" 	=> "select",
					"std" 	=> "",
					//"required" => array('header_layout','{contains}main_nav_header'),
					"target" => array(".av-header-area-preview::#pr-menu::set_class"),
					"no_first"=>true,
					"subtype" => array( __('Display as text', 'avia_framework')  =>'',
										__('Display as icon', 'avia_framework') =>'burger_menu',
										));

$avia_elements[] =	array(
					"slug"	=> "menu",
					"name" 	=> __("Menu Items for mobile", 'avia_framework'),
					"desc" 	=> __("The mobile menu is usually displayed on smarthphone screensize only. If you have a lot of main menu items you might want to activate it for tablet screen size as well so it doesn't overlap the logo on tablets or small screens", 'avia_framework'),
					"id" 	=> "header_mobile_activation",
					"type" 	=> "select",
					"std" 	=> "mobile_menu_phone",
					"required" => array('menu_display',''),
					"no_first"=>true,
					"subtype" => array( __('Activate only for Smartphones (browser width below 768px)', 'avia_framework') =>'mobile_menu_phone',
										__('Activate for Smartphones and Tablets (browser width below 990px)', 'avia_framework') =>'mobile_menu_tablet',
										));	

$avia_elements[] =	array(
					"slug"	=> "menu",
					"name" 	=> __("Separator between menu items", 'avia_framework'),
					"desc" 	=> __("Choose if you want to display a border between menu items", 'avia_framework'),
					"id" 	=> "header_menu_border",
					"type" 	=> "select",
					"std" 	=> "",
					"target" => array(".av-header-area-preview::#pr-menu-inner::set_class"),
					"no_first"=>true,
					"required" => array('menu_display',''),
					"subtype" => array( __('No separator', 'avia_framework')  =>'',
										__('Small separator', 'avia_framework') =>'seperator_small_border',
										__('Large separator', 'avia_framework') =>'seperator_big_border',
										));

$avia_elements[] = array(
							"name" 	=> __("Append search icon to main menu", 'avia_framework'),
							"desc" 	=> __("If enabled a search Icon will be appended to the main menu that allows the users to perform an 'AJAX' Search", 'avia_framework'),
							"id" 	=> "header_searchicon",
							"type" 	=> "checkbox",
							"std"	=> "true",
							"target" => array(".av-header-area-preview::#search_icon::set_class"),
							"slug"	=> "menu");

// END TAB
$avia_elements[] = array(	"slug"	=> "menu", "type" => "visual_group_end", "id" => "avia_tab5_end", "nodescription" => true);










// Start TAB
$avia_elements[] = array(	"slug"	=> "menu", "type" => "visual_group_start", "id" => "avia_tab5", "nodescription" => true, 'class'=>'avia_tab avia_tab2','name'=>__('Burger/Mobile Menu', 'avia_framework'), "global_class" => 'av_display_burger');


$avia_elements[] =	array(
					"slug"	=> "menu",
					"name" 	=> __("Menu Icon Submenu items", 'avia_framework'),
					"desc" 	=> __("Choose how to display the submenu items of the icon menu", 'avia_framework'),
					"id" 	=> "submenu_visibility",
					"type" 	=> "select",
					"std" 	=> "",
					"target" => array("#avia_main_menu_preview::.avia_control_container::set_data"),
					"no_first"=>true,
					"subtype" => array( __('Always display submenu items', 'avia_framework')  =>'',
										__('Display submenu items on click', 'avia_framework') =>'av-submenu-hidden av-submenu-display-click',
										__('Display submenu items on hover', 'avia_framework') =>'av-submenu-hidden av-submenu-display-hover',
										));


$avia_elements[] =	array(
					"slug"	=> "menu",
					"name" 	=> __("Clone title menu items to submenu", 'avia_framework'),
					"desc" 	=> __("Since you selected to display submenu items on click or on hover, the parent menu item does no longer navigate to the URL it contains, but toggles the visibility of its submenu items. If you want users to be able to open the parent menu URL the theme can create a clone of that item in the submenu", 'avia_framework'),
					"id" 	=> "submenu_clone",
					"type" 	=> "select",
					"std" 	=> "",
					"no_first"=>true,
					"required" => array('submenu_visibility','{contains_array}av-submenu-display-click;av-submenu-display-hover'),
					"subtype" => array( __('Do not create a clone', 'avia_framework') =>'av-submenu-noclone',
										__('Create a clone for the title menu item', 'avia_framework') =>'av-submenu-clone',
										));

										
$avia_elements[] =	array(
					"slug"	=> "menu",
					"name" 	=> __("Menu Icon Style", 'avia_framework'),
					"desc" 	=> __("Set the style of the 'Burger' Icon", 'avia_framework'),
					"id" 	=> "burger_size",
					"type" 	=> "select",
					"std" 	=> "",
					"target" => array(".av-header-area-preview::#pr-stretch-wrap::set_data"),
					"no_first"=>true,
					"subtype" => array( __('Default', 'avia_framework')  =>'',
										__('Small', 'avia_framework') =>'av-small-burger-icon',
										));


$avia_elements[] =	array(
					"slug"	=> "menu",
					"name" 	=> __("Menu Overlay Style", 'avia_framework'),
					"desc" 	=> __("Set the style of the page overlay that appears when the burger menu is clicked", 'avia_framework'),
					"id" 	=> "overlay_style",
					"type" 	=> "select",
					"std" 	=> "av-overlay-side av-overlay-side-classic",
					"target" => array("#avia_main_menu_preview::.avia_control_container::set_data"),
					"no_first"=>true,
					"subtype" => array( __('Full Page Overlay Menu', 'avia_framework')  =>'av-overlay-full',
										__('Sidebar Flyout Menu (Classic)', 'avia_framework') =>'av-overlay-side av-overlay-side-classic',
										__('Sidebar Flyout Menu (Minimal)', 'avia_framework') =>'av-overlay-side av-overlay-side-minimal',
										));


// END TAB
$avia_elements[] = array(	"slug"	=> "menu", "type" => "visual_group_end", "id" => "avia_tab5_end", "nodescription" => true);













// Start TAB
$avia_elements[] = array(	"slug"	=> "menu", "type" => "visual_group_start", "id" => "avia_tab5", "nodescription" => true, 'class'=>'avia_tab avia_tab2','name'=>__('Burger/Mobile Menu styling', 'avia_framework'), "global_class" => 'av_display_burger');


$avia_elements[] =	array(
					"slug"	=> "menu",
					"name" 	=> __("Menu Icon Color", 'avia_framework'),
					"desc" 	=> __("Set a custom color of the 'Burger' Icon. Leave empty to use the default menu color", 'avia_framework'),
					"id" 	=> "burger_color",
					"type" 	=> "colorpicker",
					"class" => "",
					"std" 	=> ""
					);


$avia_elements[] =	array(
					"slug"	=> "menu",
					"name" 	=> __("Flyout width", 'avia_framework'),
					"desc" 	=> __("Set a custom width for the Flyout. Pixel and % values are allowed. Eg: 350px or 70%", 'avia_framework'),
					"id" 	=> "burger_flyout_width",
					"type" 	=> "text",
					"class" => "",
					"std" 	=> "350px"
					);


					
$avia_elements[] =	array(	"name" => __("Advanced color and styling options",'avia_framework'),
							"desc" => __("You can edit more and advanced color and styling options for the overlay/slideout menu items in").
							" <a href='#goto_customizer'>".
							__("Advanced Styling",'avia_framework').
							"</a>",
							"id" => "overlay_description",
							"std" => "",
							"slug"	=> "menu",
							"type" => "heading",
							"nodescription"=>true);
					
					
					
					
					
					
					
					
					
					
					


// END TAB
$avia_elements[] = array(	"slug"	=> "menu", "type" => "visual_group_end", "id" => "avia_tab5_end", "nodescription" => true);









//END TAB CONTAINER
$avia_elements[] = array(	"slug"	=> "menu", "type" => "visual_group_end", "id" => "avia_tab_container_end", "nodescription" => true);












										

							
							


/*google*/



$avia_elements[] =	array(
					"slug"	=> "google",
					"name" 	=> __("Google Analytics Tracking Code", 'avia_framework'),
					"desc" 	=> __("Enter your Google analytics tracking Code here. It will automatically be added so google can track your visitors behavior.", 'avia_framework'),
					"id" 	=> "analytics",
					"type" 	=> "textarea"
					);

$avia_elements[] = array("slug"	=> "google", "type" => "visual_group_start", "id" => "avia_google_maps_group", "nodescription" => true);	


$google_link = "https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend,places_backend&keyType=CLIENT_SIDE&reusekey=true";
$tutorial_link = "https://kriesi.at/documentation/enfold/how-to-register-a-google-maps-api-key/";


$avia_elements[] = array(	"name" => 	__("Google Maps", 'avia_framework'),
								"desc" => __("Google recently changed the way their map service works. New pages which want to use Google Maps need to register an API key for their website. Older pages should  work fine without this API key. If the google map elements of this theme do not work properly you need to register a new API key.", 'avia_framework')."<br><a href='{$google_link}' target='_blank'>".__("Register an API Key", 'avia_framework')."</a> | <a target='_blank' href='{$tutorial_link}'>".__("Tutorial: How to create an API key", 'avia_framework')."</a>",
								"std" => "",
								"slug"	=> "google",
								"type" => "heading",
								"nodescription"=>true);


$avia_elements[] =	array(
						"slug"	=>	"google",
						"name" 	=>	__("Enter a valid Google Maps API Key to use all map related theme functions", 'avia_framework'),
						"desc" 	=>	"",
						"id" 	=>	"gmap_api",
						"type" 	=> "verification_field",
						"ajax"  => "av_maps_api_check",
						"js_callback"  => "av_maps_js_api_check",
						"class" => "av_full_description",
						"button-label" => __('Check API Key', 'avia_framework'),
						"button-relabel" => __('Check API Key', 'avia_framework'),
						"std" 	=>	""
					);


$avia_elements[] = array("slug"	=> "google", "type" => "visual_group_end", "id" => "avia_google_maps_group_end", "nodescription" => true);

/*newsletter*/

$avia_elements[] = array(	"name" => 	__("Newsletter via Mailchimp", 'avia_framework'),
								"desc" => __("Mailchimp allows you to easily use newsletter functionality with this theme. In order to use the Newsletter features you need to create a Mailchimp account and enter your API key into the field below.", 'avia_framework')."<br/><br/><a href='https://admin.mailchimp.com/account/api' target='_blank'>".__("You can find your API key here", 'avia_framework')."</a>",
								"std" => "",
								"slug"	=> "newsletter",
								"type" => "heading",
								"nodescription"=>true);

$avia_elements[] =	array(	
						"slug"	=> "newsletter",
						"std"	=> "",
						"name" 	=> __("Enter a valid Mailchimp API Key to use all newsletter related theme functions", 'avia_framework'),
						"help" 	=> "",
						"desc" 	=> false,
						"id" 	=> "mailchimp_api",
						"type" 	=> "verification_field",
						"ajax"  => "av_mailchimp_check_ajax",
						"button-label" => __('Check API Key', 'avia_framework'),
						"button-relabel" => __('Check Key again & renew Lists', 'avia_framework')
						);	


/*shop*/

$avia_elements[] =	array(
					"slug"	=> "shop",
					"name" 	=> __("Header Shopping Cart Icon", 'avia_framework'),
					"desc" 	=> __("You can choose the appearance of the cart icon here", 'avia_framework'),
					"id" 	=> "cart_icon",
					"type" 	=> "select",
					"std" 	=> "",
					"no_first"=>true,
					"subtype" => array( __('Display Floating on the side, but only once product was added to the cart', 'avia_framework') =>'',
										__('Always Display floating on the side', 'avia_framework') =>'always_display',
										__('Always Display attached to the main menu', 'avia_framework') =>'always_display_menu',
										));


$avia_elements[] =	array(
					"slug"	=> "shop",
					"name" 	=> __("Product layout on overview pages", 'avia_framework'),
					"desc" 	=> __("You can choose the appearance of your products here", 'avia_framework'),
					"id" 	=> "product_layout",
					"type" 	=> "select",
					"std" 	=> "",
					"no_first"=>true,
					"subtype" => array( __('Default', 'avia_framework') =>'',
										__('Default without buttons', 'avia_framework') =>'no_button',
										__('Minimal (no borders or buttons)', 'avia_framework') =>'minimal',
										__('Minimal Overlay with centered text', 'avia_framework') =>'minimal-overlay',
										));

$avia_elements[] =	array(
					"slug"	=> "shop",
					"name" 	=> __("Product gallery", 'avia_framework'),
					"desc" 	=> __("You can choose the appearance of your product gallery here", 'avia_framework'),
					"id" 	=> "product_gallery",
					"type" 	=> "select",
					"std" 	=> "",
					"no_first"=>true,
					"subtype" => array( __('Default enfold product gallery', 'avia_framework') =>'',
										__('WooCommerce 3.0 product gallery', 'avia_framework') =>'wc_30_gallery',
										));

$avia_elements[] =	array(
					"slug"	=> "shop",
					"name" 	=> __("Main Shop Page Banner", 'avia_framework'),
					"desc" 	=> __("You can choose to display a parallax banner with description on the shop page", 'avia_framework'),
					"id" 	=> "shop_banner",
					"type" 	=> "select",
					"std" 	=> "",
					"no_first"=>true,
					"subtype" => array( __('No, display no banner', 'avia_framework') =>'',
										__('Yes, display a banner image', 'avia_framework') =>'av-active-shop-banner',
										));



					
					
$avia_elements[] =	array(
					"slug"	=> "shop",
					"name" 	=> __("Shop Banner Image", 'avia_framework'),
					"desc" 	=> __("Upload a large banner image which will be displayed as a background to the shop description", 'avia_framework'),
					"id" 	=> "shop_banner_image",
					"type" 	=> "upload",
					"required" => array('shop_banner','{contains}av-active-shop-banner'),
					"label"	=> __("Use Image as banner", 'avia_framework'));

$avia_elements[] =	array(
					"slug"	=> "shop",
					"name" 	=> __("Shop Banner Image Color Overlay", 'avia_framework'),
					"desc" 	=> __("Set a color to display a overlay above the banner image.", 'avia_framework'),
					"id" 	=> "shop_banner_overlay_color",
					"type" 	=> "colorpicker",
					"required" => array('shop_banner','{contains}av-active-shop-banner'),
					"class" => "av_2columns av_col_1",
					"std" 	=> "#000000"
					);
					
$avia_elements[] =	array(
						"slug"	=> "shop",
						"required" => array('shop_banner','{contains}av-active-shop-banner'),
						"class" => "av_2columns av_col_2",
						"name" 	=> __("Overlay Opacity", 'avia_framework'),
						"desc" 	=> __("Select the opacity of your colored banner overlay", 'avia_framework'),
						"id" 	=> "shop_banner_overlay_opacity",
						"type" 	=> "select",
						"std" 	=> "0.5",
						"no_first"=>true,
						"subtype" => array(
										'0.1' =>'0.1',
										'0.2' =>'0.2',
										'0.3' =>'0.3',
										'0.4' =>'0.4',
										'0.5' =>'0.5',
										'0.6' =>'0.6',
										'0.7' =>'0.7',
										'0.8' =>'0.8',
										'0.9' =>'0.9',
										'1' =>'1',
										
										));


$avia_elements[] =	array(
					"slug"	=> "shop",
					"name" 	=> __("Shop Description", 'avia_framework'),
					"desc" 	=> __("Enter a short description or welcome note for your default Shop Page", 'avia_framework'),
					"id" 	=> "shop_banner_message",
					"type" 	=> "textarea",
					"required" => array('shop_banner','{contains}av-active-shop-banner'),
					"class" => "av_2columns av_col_1",
					);

$avia_elements[] =	array(
					"slug"	=> "shop",
					"name" 	=> __("Shop Description Color", 'avia_framework'),
					"desc" 	=> __("Select the color of your shop description", 'avia_framework'),
					"id" 	=> "shop_banner_message_color",
					"type" 	=> "colorpicker",
					"required" => array('shop_banner','{contains}av-active-shop-banner'),
					"class" => "av_2columns av_col_2",
					"std" 	=> "#ffffff"
					);
					
$avia_elements[] =	array(
					"slug"	=> "shop",
					"name" 	=> __("Enable Banner for product category pages", 'avia_framework'),
					"desc" 	=> __("You can enable the shop banner for all categories as well. You can also set individual banners by editing the category", 'avia_framework'),
					"id" 	=> "shop_banner_global",
					"type" 	=> "checkbox",
					"required" => array('shop_banner','{contains}av-active-shop-banner'),
					"std"	=> false,
					);






/*layout*/



$frontend_label = __("A rough preview of the frontend.", 'avia_framework');

$avia_elements[] =	array(
					"slug"	=> "layout",
					"id" 	=> "default_layout_target",
					"type" 	=> "target",
					"std" 	=> "
					<style type='text/css'>
						.avprev-layout-container, .avprev-layout-container *{ 
							-moz-box-sizing: border-box;
							-webkit-box-sizing: border-box;
							box-sizing: border-box;
						}
						#boxed .avprev-layout-container{ padding:0 23px; border:1px solid #e1e1e1; background-color: #555;}
						#av-framed-box .avprev-layout-container{ padding:23px; border:1px solid #e1e1e1; background-color: #555;}
						.avprev-layout-container-inner{border:none; overflow: hidden;}
						.avprev-layout-container-inner{border: 1px solid #e1e1e1; background:#fff;}
						.avprev-layout-content-container{overflow:hidden; margin:0 auto; position:relative;}
						.avprev-layout-container-sizer{margin:0 auto; position:relative; z-index:5;}
						.avprev-layout-content-container .avprev-layout-container-sizer{display:table;}
						.avprev-layout-content-container .avprev-layout-container-sizer .av-cell{display:table-cell; padding: 20px;}
						.avprev-layout-content-container .avprev-layout-container-sizer:after{ background: #F8F8F8; position: absolute; top: 0; left: 99%; width: 100%; height: 100%; content: ''; z-index:1;}
						.avprev-layout-header{border-bottom:1px solid #e1e1e1; padding:20px; overflow: hidden;}
						.avprev-layout-slider{border-bottom:1px solid #e1e1e1; padding:30px 20px; background:#3B740F url('".AVIA_IMG_URL."layout/diagonal-bold-light.png') top left repeat; color:#fff;}
						.avprev-layout-content{border-right:1px solid #e1e1e1; width:73%; }
						.avprev-layout-sidebar{border-left:1px solid #e1e1e1; background:#f8f8f8; left:-1px; position:relative; min-height:141px;}
						.avprev-layout-menu-description{float:left;}
						.avprev-layout-menu{float:right; color:#999;}
						
						
						#header_right .avprev-layout-header{border-left:1px solid #e1e1e1; width:130px; float:right; border-bottom:none;}
						#header_left .avprev-layout-header{border-right:1px solid #e1e1e1; width:130px; float:left; border-bottom:none;}
						
						#header_right .avprev-layout-content-container{border-right:1px solid #e1e1e1; right:-1px;}
						#header_left  .avprev-layout-content-container{border-left:1px solid #e1e1e1; left:-1px;}
						
						#header_left .avprev-layout-menu, #header_right .avprev-layout-menu{float:none; padding-top:23px; clear:both; }
						#header_left .avprev-layout-divider, #header_right .avprev-layout-divider{display:none;}
						#header_left .avprev-layout-menuitem, #header_right .avprev-layout-menuitem{display:block; border-bottom:1px dashed #e1e1e1; padding:3px;}
						#header_left .avprev-layout-menuitem-first, #header_right .avprev-layout-menuitem-first{border-top:1px dashed #e1e1e1;}
						#header_left .avprev-layout-header .avprev-layout-container-sizer, #header_right .avprev-layout-header .avprev-layout-container-sizer{width:100%!important;}
						
						
						.avprev-layout-container-widget{display:none; border:1px solid #e1e1e1; padding:7px; font-size:12px; margin-top:5px; text-align:center;}
						.avprev-layout-container-social{margin-top:5px; text-align:center;}
						.av-active .pr-icons{display:block; }
						
						#header_left .avprev-layout-container-widget.av-active, #header_right .avprev-layout-container-widget.av-active{display:block;}
						#header_left .avprev-layout-container-social.av-active, #header_right .avprev-layout-container-widget.av-social{display:block;}
						
						#av-framed-box .avprev-layout-container-inner{border:none;}
						#boxed .avprev-layout-container-inner{border:none;}
						
					</style>

					<small class='live_bg_small'>{$frontend_label}</small>
					<div class='avprev-layout-container'>
						<div class='avprev-layout-container-inner'>
							<div class='avprev-layout-header'>
								<div class='avprev-layout-container-sizer'>
									<strong class='avprev-layout-menu-description'>Logo + Main Menu Area</strong>
									<div class='avprev-layout-menu'>
									<span class='avprev-layout-menuitem avprev-layout-menuitem-first'>Home</span> 
									<span class='avprev-layout-divider'>|</span> 
									<span class='avprev-layout-menuitem'>About</span> 
									<span class='avprev-layout-divider'>|</span> 
									<span class='avprev-layout-menuitem'>Contact</span> 
									</div>
								</div>
								
								<div class='avprev-layout-container-social'>
									{$iconSpan}	
								</div>
								
								<div class='avprev-layout-container-widget'>
									<strong>Widgets</strong>
								</div>
								
							</div>
							
							<div class='avprev-layout-content-container'>
								<div class='avprev-layout-slider'>
									<strong>Fullwidth Area (eg: Fullwidth Slideshow)</strong>
								</div>
							
								<div class='avprev-layout-container-sizer'>
									<div class='avprev-layout-content av-cell'><strong>Content Area</strong><p>This is the content area. The content area holds all your blog entries, pages, products etc</p></div>
									<div class='avprev-layout-sidebar av-cell'><strong>Sidebar</strong><p>This area holds all your sidebar widgets</p>
									</div>
								</div>
							</div>
							
						</div>
					</div>
					

					",
					"nodescription" => true
					);

//START TAB CONTAINER
$avia_elements[] = array(	"slug"	=> "layout", "type" => "visual_group_start", "id" => "avia_tab_layout1", "nodescription" => true, 'class'=>'avia_tab_container avia_set');

$avia_elements[] = array(	"slug"	=> "layout", "type" => "visual_group_start", "id" => "avia_tab_layout5", "nodescription" => true, 'class'=>'avia_tab avia_tab2','name'=>__('Layout', 'avia_framework'));

$avia_elements[] =	array(
					"slug"	=> "layout",
					"name" 	=> __("Use stretched or boxed layout?", 'avia_framework'),
					"desc" 	=> __("The stretched layout expands from the left side of the viewport to the right.", 'avia_framework'),
					"id" 	=> "color-body_style",
					"type" 	=> "select",
					"std" 	=> "stretched",
					"class" => "av_2columns av_col_1",
					"no_first"=>true,
					"target" => array("default_slideshow_target, #avia_default_layout_target::.avia_control_container::set_id"),
					"subtype" => array(	__('Stretched layout'	, 'avia_framework') => 'stretched', 
										__('Boxed Layout'		, 'avia_framework') => 'boxed',
										__('Fixed Frame'		, 'avia_framework') => 'av-framed-box'
										)
										);

$numbers = array();
for($i = 1; $i <= 75; $i++)
{
	$numbers[$i."px"] = $i;
}

$avia_elements[] =	array(
					"slug"	=> "layout",
					"name" 	=> __("Frame Width", 'avia_framework'),
					"desc" 	=> __("Modify the frame color by changing the Body Background in",'avia_framework').
							" <a href='#goto_styling'>".
							__("General Styling",'avia_framework').
							"</a>",
					"id" 	=> "color-frame_width",
					"type" 	=> "select",
					"std" 	=> "20",
					"class" => "av_2columns av_col_2",
					"required" => array('color-body_style','{contains}framed'),
					"no_first"=>true,
					"subtype" => $numbers
					);




$avia_elements[] =	array(
					"slug"	=> "layout",
					"name" 	=> __("Logo and Main Menu", 'avia_framework'),
					"desc" 	=> __("You can place your logo and main menu at the top of your site or within a sidebar", 'avia_framework'),
					"id" 	=> "header_position",
					"type" 	=> "select",
					"std" 	=> "header_top",
					"class" => "av_2columns av_col_2",
					"target" => array("default_layout_target, #avia_default_slideshow_target::.avprev-layout-container, .avprev-design-container::set_id_single"),
					"no_first"=>true,
					"subtype" => array( __('Top Header', 'avia_framework') =>'header_top',
										__('Left Sidebar', 'avia_framework') =>'header_left header_sidebar',
										__('Right Sidebar', 'avia_framework') =>'header_right header_sidebar',
										));
										

$avia_elements[] =	array(
					"slug"	=> "layout",
					"name" 	=> __("Content Alignment", 'avia_framework'),
					"desc" 	=> __("If the window width exceeds the maximum content width, where do you want to place your content", 'avia_framework'),
					"id" 	=> "layout_align_content",
					"type" 	=> "select",
					"std" 	=> "content_align_center",
					"class" => "av_2columns av_col_1",
					"required" => array('header_position','{contains}header_sidebar'),
					"no_first"=>true,
					"subtype" => array( __('Center Content', 'avia_framework') =>'content_align_center',
										__('Position at the Left', 'avia_framework') 	=>'content_align_left',
										__('Position at the Right', 'avia_framework') 	=>'content_align_right',
										));

										

$avia_elements[] =	array(
					"slug"	=> "layout",
					"name" 	=> __("Sticky Sidebar menu", 'avia_framework'),
					"desc" 	=> __("You can choose if you want a sticky sidebar that does not scroll with the content", 'avia_framework'),
					"id" 	=> "sidebarmenu_sticky",
					"type" 	=> "select",
					"std" 	=> "conditional_sticky",
					"class" => "av_2columns av_col_2",
					"required" => array('header_position','{contains}header_left'),
					"no_first"=>true,
					"subtype" => array( __('Sticky if Sidebar is smaller than the screen height, scroll otherwise', 'avia_framework') =>'conditional_sticky',
										__('Always Sticky', 'avia_framework') 	=>'always_sticky',
										__('Never Sticky', 'avia_framework') 	=>'never_sticky',
										));





$avia_elements[] =	array(
					"slug"	=> "layout",
					"name" 	=> __("Main Menu Sidebar", 'avia_framework'),
					"desc" 	=> __("You can choose to use the main menu area to also display widget areas", 'avia_framework'),
					"id" 	=> "sidebarmenu_widgets",
					"type" 	=> "select_sidebar",
					"std" 	=> "",
					"no_first"=>true,
					"required" => array('header_position','{contains}header_sidebar'),
					"target" => array("default_layout_target::.avprev-layout-container-widget::set_active"),
					"exclude" 	=> array(), /*eg: 'Displayed Everywhere'*/
					"additions" => array('No widgets' => "", /* 'Display Widgets by page logic' => "av-auto-widget-logic", */ 'Display a specific Widget Area'=> '%result%'),
					);
					
					

$avia_elements[] = array(
		"name" 	=> 
				__("Display social icons below main menu? (You can set your social icons at", 'avia_framework').
				" <a href='#goto_social'>".
				__("Social Profiles", 'avia_framework').
				"</a>)"
		,
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "sidebarmenu_social",
		"type" 	=> "checkbox",
		"std"	=> "",
		"slug"	=> "layout",
		"target" => array("default_layout_target::.avprev-layout-container-social::set_active"),
		"required" => array('header_position','{contains}header_sidebar'),
		);	



// END TAB
$avia_elements[] = array(	"slug"	=> "layout", "type" => "visual_group_end", "id" => "avia_tab5ewwe_end", "nodescription" => true);
$avia_elements[] = array(	"slug"	=> "layout", "type" => "visual_group_start", "id" => "avia_tab5wewe", "nodescription" => true, 'class'=>'avia_tab avia_tab2','name'=>__('Dimensions', 'avia_framework'));
// START TAB


$avia_elements[] = array(
		"name" 	=> __("Responsive Site", 'avia_framework'),
		"desc" 	=> __("If enabled the size of your website will adapt and change the layout to fit smaller screens, like tablets or mobile phones", 'avia_framework'),
		"id" 	=> "responsive_active",
		"type" 	=> "checkbox",
		"std"	=> "enabled",
		"slug"	=> "layout",
		);	

$avia_elements[] =	array(
		"slug"	=> "layout",
		"name" 	=> __("Maximum Container width", 'avia_framework'),
		"desc" 	=> __("Enter the maximum content width for your site. Pixel and % are allowed eg: 1130px, 1310px, 100% ", 'avia_framework'),
		"id" 	=> "responsive_size",
		"type" 	=> "text",
		"std" 	=> "1310px",
		"required" => array('responsive_active','{contains}enabled'),
		);



$avia_elements[] =	array(
					"slug"	=> "layout",
					"name" 	=> __("Content | Sidebar Ratio", 'avia_framework'),
					"desc" 	=> __("Here you can choose the width of your content and sidebar. First Number indicates the content width, second number indicates sidebar width.", 'avia_framework') ."<br/><strong>".__("Note:", 'avia_framework') ."</strong> ".
					__( "If you want to disable sidebars you can do so in the", 'avia_framework').
					" <a href='#goto_sidebars'>".
					__( "Sidebar Settings", 'avia_framework').
					"</a>",
					"id" 	=> "content_width",
					"target" => array("default_layout_target::.avprev-layout-content::width"),
					"type" 	=> "select",
					"std" 	=> "73",
					"no_first"=>true,
					"subtype" => array( 
											'80% | 20%' =>'80',
											'79% | 21%' =>'79',
											'78% | 22%' =>'78',
											'77% | 23%' =>'77',
											'76% | 24%' =>'76',
											'75% | 25%' =>'75',
											'74% | 26%' =>'74',
											'73% | 27%' =>'73',
											'72% | 28%' =>'72',
											'71% | 29%' =>'71',
											
											'70% | 30%' =>'70',
											'69% | 31%' =>'69',
											'68% | 32%' =>'68',
											'67% | 33%' =>'67',
											'66% | 34%' =>'66',
											'65% | 35%' =>'65',
											'64% | 36%' =>'64',
											'63% | 37%' =>'63',
											'62% | 38%' =>'62',
											'61% | 39%' =>'61',
											
											'60% | 40%' =>'60',
											'59% | 41%' =>'59',
											'58% | 42%' =>'58',
											'57% | 43%' =>'57',
											'56% | 44%' =>'56',
											'55% | 45%' =>'55',
											'54% | 46%' =>'54',
											'53% | 47%' =>'53',
											'52% | 48%' =>'52',
											'51% | 49%' =>'51',
											'50% | 50%' =>'50',
										
																				));

$numbers = array();
for($i = 100; $i >= 50; $i--)
{
	$numbers[$i."%"] = $i;
}

$avia_elements[] =	array(
					"slug"	=> "layout",
					"name" 	=> __("Content + Sidebar width", 'avia_framework'),
					"desc" 	=> __("Here you can enter the combined width of content and sidebar", 'avia_framework'),
					"id" 	=> "combined_width",
					"target" => array("default_layout_target::.avprev-layout-container-sizer::width"),
					"type" 	=> "select",
					"std" 	=> "100",
					"no_first"=>true,
					"subtype" => $numbers
					);


// END TAB
$avia_elements[] = array(	"slug"	=> "layout", "type" => "visual_group_end", "id" => "avia_tab4543_end", "nodescription" => true);


//END TAB CONTAINER
$avia_elements[] = array(	"slug"	=> "layout", "type" => "visual_group_end", "id" => "avia_tab_container_end2", "nodescription" => true);
		
		
/*Frontpage Settings*/


if(is_child_theme()){
$avia_elements[] =	array(
					"slug"	=> "upload",
					"name" 	=> __("Import Settings from your Parent Theme", 'avia_framework'),
					"desc" 	=> __("We have detected that you are using a Child Theme. That's Great!. If you want to, we can import the settings of your Parent theme to your Child theme. Please be aware that this will overwrite your current child theme settings.", 'avia_framework'),
					"id" 	=> "parent_setting_import",
					"type" 	=> "parent_setting_import");
}


$avia_elements[] =	array(
    "slug"	=> "upload",
    "name" 	=> __("Export Theme Settings File", 'avia_framework'),
    "desc" 	=> __("Click the button to generate and download a config file which contains the theme settings. You can use the config file to import the theme settings on another sever.", 'avia_framework'),
    "id" 	=> "theme_settings_export",
    "type" 	=> "theme_settings_export");

$avia_elements[] =	array(
    "slug"		=> "upload",
    "name" 		=> __("Import Theme Settings File", 'avia_framework'),
    "desc" 		=> __("Upload a theme configuration file here. Note that the configuration file settings will overwrite your current configuration and you can't restore the current configuration afterwards.", 'avia_framework'),
    "id" 		=> "config_file_upload",
    "title" 	=> __("Upload Theme Settings File", 'avia_framework'),
    "button" 	=> __("Insert Settings File", 'avia_framework'),
    "trigger" 	=> "av_config_file_insert",
    // "fopen_check" 	=> "true",
    "std"	  	=> "",
    "file_extension" => "txt",
    "file_type"		=> "text/plain",
    "type" 		=> "file_upload");
    
    

    

  					
$avia_elements[] =	array(
	"slug"		=> "upload",
	"name" 		=> __("Iconfont Manager", 'avia_framework'),
	"desc" 		=> __("You can upload additional Iconfont Packages generated with", 'avia_framework') . " <a href='http://fontello.com/' target='_blank'>Fontello</a>  ".
	__("or use monocolored icon sets from", 'avia_framework') . " <a href='http://www.flaticon.com/' target='_blank'>Flaticon</a>. ".
	__("Those icons can then be used in your Layout Builder.", 'avia_framework') ."<br/><br/>".
	__("The 'Default Font' can't be deleted.", 'avia_framework') ."<br/><br/>".
	__("Make sure to delete any fonts that you are not using, to keep the loading time for your visitors low", 'avia_framework'),
	"id" 		=> "iconfont_upload",
	"title" 	=> __("Upload/Select Fontello Font Zip", 'avia_framework'),
	"button" 	=> __("Insert Zip File", 'avia_framework'),
	"trigger" 	=> "av_fontello_zip_insert",
	// "fopen_check" 	=> "true",
	"std"	  	=> "",
	"type" 		=> "file_upload",
	"file_extension" => "zip", //used to check if user can upload this file type
	"file_type"		=> "application/octet-stream, application/zip", //used for javascript gallery to display file types
	);	  
    
    
    

    


$avia_elements[] =	array(
					"slug"	=> "avia",
					"name" 	=> __("Frontpage Settings", 'avia_framework'),
					"desc" 	=> __("Select which page to display on your Frontpage. If left blank the Blog will be displayed", 'avia_framework'),
					"id" 	=> "frontpage",
					"type" 	=> "select",
					"subtype" => 'page');

$avia_elements[] =	array(
					"slug"	=> "avia",
					"name" 	=> __("And where do you want to display the Blog?", 'avia_framework'),
					"desc" 	=> __("Select which page to display as your Blog Page. If left blank no blog will be displayed", 'avia_framework'),
					"id" 	=> "blogpage",
					"type" 	=> "select",
					"subtype" => 'page',
					"required" => array('frontpage','{true}')
					);

$avia_elements[] =	array(
					"slug"	=> "avia",
					"name" 	=> __("Logo", 'avia_framework'),
					"desc" 	=> __("Upload a logo image, or enter the URL or ID of an image if its already uploaded. The themes default logo gets applied if the input field is left blank", 'avia_framework')."<br/><br/>".__("Logo Dimension: 340px * 156px (if your logo is larger you might need to change the Header size in your", 'avia_framework').
					" <a href='#goto_header'>".
					__( "Header Settings", 'avia_framework').
					"</a>",
					"id" 	=> "logo",
					"type" 	=> "upload",
					"label"	=> __("Use Image as logo", 'avia_framework'));

$avia_elements[] =	array(
					"slug"	=> "avia",
					"name" 	=> __("Favicon", 'avia_framework'),
					"desc" 	=> __("Specify a favicon for your site.", 'avia_framework')." <br/>".__("Accepted formats: .ico, .png, .gif", 'avia_framework')." <br/><br/>".
					__("What is a", 'avia_framework').
					" <a target='_blank' href='http://en.wikipedia.org/wiki/Favicon'>".
					__( "favicon", 'avia_framework').
					"?</a>",
					"id" 	=> "favicon",
					"type" 	=> "upload",
					"label"	=> __("Use Image as Favicon", 'avia_framework'));


$avia_elements[] = array(	"slug"	=> "avia", "type" => "visual_group_start", "id" => "avia_preload", "nodescription" => true);

$avia_elements[] =	array(
					"slug"	=> "avia",
					"name" 	=> __("Page Preloading", 'avia_framework'),
					"desc" 	=> __("Show a preloader when opening a page on your site.", 'avia_framework'),
					"id" 	=> "preloader",
					"type" 	=> "checkbox",
					"std"	=> false,
					);

$avia_elements[] =	array(
					"slug"	=> "avia",
					"name" 	=> __("Page Transitions", 'avia_framework'),
					"desc" 	=> __("Smooth page transition when navigating from one page to the next. Please disable if this causes problems with plugins when navigating ajax or otherwise dynamical created content", 'avia_framework'),
					"id" 	=> "preloader_transitions",
					"type" 	=> "checkbox",
					"std"	=> 'preloader_transitions',
					"required" => array("preloader",'preloader'),
					);

$avia_elements[] =	array(
					"slug"	=> "avia",
					"name" 	=> __("Custom Logo for preloader", 'avia_framework'),
					"desc" 	=> __("Upload an optional logo image for your preloader page", 'avia_framework'),
					"id" 	=> "preloader_logo",
					"type" 	=> "upload",
					"required" => array("preloader",'preloader'),
					"label"	=> __("Use Image as logo", 'avia_framework'));

$avia_elements[] = array(	"slug"	=> "avia", "type" => "visual_group_end", "id" => "avia_preload_end", "nodescription" => true);







$avia_elements[] = array(
		"name" 	=> __("Lightbox Modal Window", 'avia_framework'),
		"desc" 	=> __("Check to enable the default lightbox that opens once you click a link to an image. Uncheck only if you want to use your own modal window plugin", 'avia_framework'),
		"id" 	=> "lightbox_active",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"slug"	=> "avia");	
		







$avia_elements[] =	array(
					"slug"	=> "styling",
					"name" 	=> __("Select a predefined color scheme", 'avia_framework'),
					"desc" 	=> __("Choose a predefined color scheme here. You can edit the settings of the scheme below then.", 'avia_framework'),
					"id" 	=> "color_scheme",
					"type" 	=> "link_controller",
					"std" 	=> "Blue",
					"class"	=> "link_controller_list",
					"subtype" => $styles);
					



$avia_elements[] =	array(
					"slug"		=> "customizer",
					"name" 		=> __("Here you can select a number of different elements and change their default styling", 'avia_framework'),
					"desc" 		=> __("If a value is left empty or set to default then it will not be changed from the value defined in your CSS files", 'avia_framework')."<br/><br/><strong>".
									__("Attention", 'avia_framework').": </strong>".
									__("This feature is in active BETA! We will constantly add new elements to customize and need your help: If you got any suggestions on what to add please post them here:", 'avia_framework').
									" <a target='_blank' href='https://kriesi.at/support/enfold-feature-requests/'>".
									__("Enfold Feature Requests", 'avia_framework').
									"</a><br/><br/>"
									,
					"id" 		=> "advanced_styling",
					"type" 		=> "styling_wizard",
					"order" 	=> array(
									__("HTML Tags",'avia_framework'), 
									__("Headings",'avia_framework'), 
									__("Main Menu",'avia_framework'), 
									__("Main Menu (Icon)",'avia_framework'), 
									__("Misc",'avia_framework')),
					"std" 		=> "",
					"class"		=> "",
					"elements" => $advanced);




/*Styling Settings*/
$avia_elements[] =	array(
					"slug"	=> "styling",
					"id" 	=> "default_slideshow_target",
					"type" 	=> "target",
					"std" 	=> "
					<style type='text/css'>

						#boxed .live_bg_wrap{ padding:0 23px;   border:1px solid #e1e1e1; background-position: top center;}
						#av-framed-box .live_bg_wrap{ padding:23px;   border:1px solid #e1e1e1; background-position: top center;}
						.live_bg_small{font-size:10px; color:#999;     height: 23px; display: block;}
						.live_bg_wrap{ padding: 0; background:#f8f8f8; overflow:hidden; background-position: top center;}
						.live_bg_wrap div{overflow:hidden; position:relative;}
						#avia_options_page .live_bg_wrap h3{margin: 0 0 5px 0 ; color:inherit; font-size:25px;}
						#avia_options_page .live_bg_wrap .main_h3{font-weight:bold; font-size:25px;  }
						.border{border:1px solid; border-bottom-style:none; border-bottom-width:0; padding:13px; width:100%;}
						#av-framed-box .border{}

						.live_header_color {position: relative;width: 100%;left: }
						.bg2{border:1px solid; margin:4px; display:block; float:right; padding:15px; }
						.content_p{display:block; float:left; width: 100%;}
						.live-socket_color{font-size:11px;}
						.live-footer_color a{text-decoration:none;}
						.live-socket_color a{text-decoration:none;  position:absolute; top:28%; right:13px;}

						#avia_preview .webfont_google_webfont{  font-weight:normal; }
						.webfont_default_font{  font-weight:normal; font-size:13px; line-height:1.7em;}

						div .link_controller_list a{ width:113px; font-size:13px;}
						.avia_half{width: 50%; float:left; min-height:210px;}
						.avia_half .bg2{float:none; margin-left:0;}
						.avia_half_2{border-left:none; padding-left:14px;}
						#av-framed-box #header_left  .avia_half { width: 179px; height:250px;}
						.live-slideshow_color{text-align:center;}
						.text_small_outside{position:relative; top:-15px; display:block; left: 10px;}
						
						#header_left .live-header_color{ float:left;  width:30%; min-height: 424px; border-bottom:1px solid; border-right: none;}
						#header_right .live-header_color{float:right; width:30%; min-height: 424px; border-bottom:1px solid; border-left:  none;}
						#header_left .live-header_color .bg2, 
						#header_right .live-header_color .bg2,
						#header_right .av_header_block_1,
						#header_left .av_header_block_1{
							float:none;
							width:100%;
						}
						.av-sub-logo-area{overflow:hidden;}
						
						#boxed #header_left .live-header_color, #boxed #header_right .live-header_color{min-height: 424px; }
						#header_right .avia_half, #header_left .avia_half{min-height: 250px;}
						#boxed .live-socket_color{border-bottom:1px solid;}
						.av_header_block_1{width:70%; float:left;}
						.live-header_color .bg2{width:30%; margin: 15px 0 0 0;}
						#av-framed-box .live-socket_color.border{border-bottom-style:solid; border-bottom-width:1px;}
					</style>





					<small class='live_bg_small'>{$frontend_label}</small>

					<div id='avia_preview' class='live_bg_wrap webfont_default_font'>
					<div class='avprev-design-container'>
					<!--<small class='text_small_outside'>Next Event: in 10 hours 5 minutes.</small>-->


						<div class='live-header_color border'>
							<div class='av_header_block_1'>
								<h3 class='heading webfont_google_webfont'>Logo Area Heading</h3>
								<span class='text'>Active Menu item | </span>
								<span class='meta'>Inactive Menu item</span><br/>
								<a class='a_link' href='#'>custom text link</a>
								<a class='an_activelink' href='#'>hovered link</a>
							</div>
							<div class='bg2'>Highlight Background + Border Color</div>
						</div>
						
						<div class='av-sub-logo-area'>

						

						<div class='live-main_color border avia_half'>
							<h3 class='webfont_google_webfont main_h3 heading'>Main Content heading</h3>
								<p class='content_p'>This is default content with a default heading. Font color, headings and link colors can be choosen below. <br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
									<span class='meta'>Secondary Font</span>

								</p>

								<div class='bg2'>Highlight Background + Border Color</div>
						</div>



						<div class='live-alternate_color border avia_half avia_half_2'>
								<h3 class='webfont_google_webfont main_h3 heading'>Alternate Content Area</h3>
								<p class='content_p'>This is content of an alternate content area. Choose font color, headings and link colors below. <br/>
									<a class='a_link' href='#'>A link</a>
									<a class='an_activelink' href='#'>A hovered link</a>
									<span class='meta'>Secondary Font</span>

								</p>

								<div class='bg2'>Highlight Background + Border Color</div>
						</div>

						<div class='live-footer_color border'>
							<h3 class='webfont_google_webfont heading'>Demo heading (Footer)</h3>
							<p>This is text on the footer background</p>
							<a class='a_link' href='#'>Link | Link 2</a>
							<span class='meta'> | Secondary Font</span>

						</div>

						<div class='live-socket_color border'>Socket Text <a class='a_link' href='#'>Link | Link 2</a>
													<span class='meta'> | Secondary Font</span>

						</div>
					</div>	
					</div>
					</div>

					",
					"nodescription" => true
					);





$avia_elements[] = array(	"slug"	=> "styling", "type" => "visual_group_start", "id" => "avia_tab1", "nodescription" => true, 'class'=>'avia_tab_container avia_set');





//create color sets for #header, Main Content, Secondary Content, Footer, Socket, Slideshow

$colorsets = $avia_config['color_sets'];
$iterator = 1;

foreach($colorsets as $set_key => $set_value)
{
	$iterator ++;

	$avia_elements[] = array(	"slug"	=> "styling", "type" => "visual_group_start", "id" => "avia_tab".$iterator, "nodescription" => true, 'class'=>'avia_tab avia_tab'.$iterator,'name'=>$set_value);

	$avia_elements[] =	array(
					"slug"	=> "styling",
					"name" 	=> $set_value ." ". __("background color", 'avia_framework'),
					"id" 	=> "colorset-$set_key-bg",
					"type" 	=> "colorpicker",
					"class" => "av_2columns av_col_1",
					"std" 	=> "#ffffff",
					"desc" 	=> __("Default Background color", 'avia_framework'),
					"target" => array("default_slideshow_target::.live-$set_key::background-color"),
					);

	$avia_elements[] =	array(
					"slug"	=> "styling",
					"name" 	=> __("Alternate Background color", 'avia_framework'),
					"desc" 	=> __("Alternate Background for menu hover, tables etc", 'avia_framework'),
					"id" 	=> "colorset-$set_key-bg2",
					"type" 	=> "colorpicker",
					"class" => "av_2columns av_col_2",
					"std" 	=> "#f8f8f8",
					"target" => array("default_slideshow_target::.live-$set_key .bg2::background-color"),
					);

	$avia_elements[] =	array(
					"slug"	=> "styling",
					"name" 	=> __("Primary color", 'avia_framework'),
					"desc" 	=> __("Font color for links, dropcaps and other elements", 'avia_framework'),
					"id" 	=> "colorset-$set_key-primary",
					"type" 	=> "colorpicker",
					"class" => "av_2columns av_col_1",
					"std" 	=> "#719430",
					"target" => array("default_slideshow_target::.live-$set_key .a_link, .live-$set_key-wrap-top::color,border-color"),
					);


	$avia_elements[] =	array(
					"slug"	=> "styling",
					"name" 	=> __("Highlight color", 'avia_framework'),
					"desc" 	=> __("Secondary color for link and button hover, etc", 'avia_framework')."<br/>",
					"id" 	=> "colorset-$set_key-secondary",
					"type" 	=> "colorpicker",
					"class" => "av_2columns av_col_2",
					"std" 	=> "#8bba34",
					"target" => "default_slideshow_target::.live-$set_key .an_activelink::color",
					);


	$avia_elements[] =	array(
						"slug"	=> "styling",
						"name" 	=> $set_value." ". __("font color", 'avia_framework'),
						"id" 	=> "colorset-$set_key-color",
						"type" 	=> "colorpicker",
						"class" => "av_2columns av_col_1",
						"std" 	=> "#719430",
						"target" => array("default_slideshow_target::.live-$set_key::color"),
						);

	$avia_elements[] =	array(
						"slug"	=> "styling",
						"name" 	=> $set_value." ". __("secondary font color", 'avia_framework'),
						"id" 	=> "colorset-$set_key-meta",
						"type" 	=> "colorpicker",
						"class" => "av_2columns av_col_2",
						"std" 	=> "#719430",
						"target" => array("default_slideshow_target::.live-$set_key .meta::color"),
						);
	
		$avia_elements[] =	array(
						"slug"	=> "styling",
						"name" 	=> $set_value." ". __("Heading color", 'avia_framework'),
						"id" 	=> "colorset-$set_key-heading",
						"type" 	=> "colorpicker",
						"class" => "av_2columns av_col_1",
						"std" 	=> "#666666",
						"target" => array("default_slideshow_target::.live-$set_key .heading::color"),
						);


	$avia_elements[] =	array(
					"slug"	=> "styling",
					"name" 	=> __("Border colors", 'avia_framework'),
					"id" 	=> "colorset-$set_key-border",
					"type" 	=> "colorpicker",
					"class" => "av_2columns av_col_2",
					"std" 	=> "#e1e1e1",
					"target" => array("default_slideshow_target::.live-$set_key.border, .live-$set_key .bg2::border-color"),
					);






	$avia_elements[] = array(	"slug"	=> "styling", "type" => "hr", "id" => "hr".$set_key, "nodescription" => true);

	$avia_elements[] = array(
						"slug"	=> "styling",
						"id" 	=> "colorset-$set_key-img",
						"name" 	=> __("Background Image", 'avia_framework'),
						"desc" 	=> __("The background image of your", 'avia_framework')." ".$set_value."<br/>",
						"type" 	=> "select",
						"subtype" => array(__('No Background Image', 'avia_framework')=>'',__('Upload custom image', 'avia_framework')=>'custom'),
						"std" 	=> "",
						"no_first"=>true,
						"class" => "av_2columns av_col_1",
						"target" => array("default_slideshow_target::.live-$set_key::background-image"),
						"folder" => "images/background-images/",
						"folderlabel" => "",
						"group" => "Select predefined pattern",
						);


	$avia_elements[] =	array(
						"slug"	=> "styling",
						"name" 	=> __("Custom Background Image", 'avia_framework'),
						"desc" 	=> __("Upload a BG image for your", 'avia_framework')." ".$set_value."<br/>",
						"id" 	=> "colorset-$set_key-customimage",
						"type" 	=> "upload",
						"std" 	=> "",
						"class" => "set_blank_on_hide av_2columns av_col_2",
						"label"	=> __("Use Image", 'avia_framework'),
						"required" => array("colorset-$set_key-img",'custom'),
						"target" => array("default_slideshow_target::.live-$set_key::background-image"),
						);


	$avia_elements[] =	array(
						"slug"	=> "styling",
						"name" 	=> __("Position of the image", 'avia_framework'),
						"desc" 	=> "",
						"id" 	=> "colorset-$set_key-pos",
						"type" 	=> "select",
						"std" 	=> "top left",
						"no_first"=>true,
						"class" => "av_2columns av_col_1",
						"required" => array("colorset-$set_key-img",'{true}'),
						"target" => array("default_slideshow_target::.live-$set_key::background-position"),
						"subtype" => array(
											__('Top Left', 'avia_framework')		=>'top left',
											__('Top Center', 'avia_framework')		=>'top center',
											__(	'Top Right', 'avia_framework')		=>'top right',
											__('Bottom Left', 'avia_framework')		=>'bottom left',
											__('Bottom Center', 'avia_framework')	=>'bottom center',
											__(	'Bottom Right', 'avia_framework')	=>'bottom right',
											__(	'Center Left ', 'avia_framework')	=>'center left',
											__('Center Center', 'avia_framework')	=>'center center',
											__(	'Center Right', 'avia_framework')	=>'center right'));

	$avia_elements[] =	array(
						"slug"	=> "styling",
						"name" 	=> __("Repeat", 'avia_framework'),
						"desc" 	=> "",
						"id" 	=> "colorset-$set_key-repeat",
						"type" 	=> "select",
						"std" 	=> "no-repeat",
						"class" => "av_2columns av_col_2",
						"no_first"=>true,
						"required" => array("colorset-$set_key-img",'{true}'),
						"target" => array("default_slideshow_target::.live-$set_key::background-repeat"),
						"subtype" => array(
											__('no repeat', 'avia_framework') =>'no-repeat',
											__('Repeat', 'avia_framework') =>'repeat',
											__('Tile Horizontally', 'avia_framework') =>'repeat-x',
											__('Tile Vertically', 'avia_framework') =>'repeat-y',
/* 										    __('Stretch Fullscreen', 'avia_framework')=>'fullscreen' */
										    ));
	$avia_elements[] =	array(
						"slug"	=> "styling",
						"name" 	=> __("Attachment", 'avia_framework'),
						"desc" 	=> "",
						"id" 	=> "colorset-$set_key-attach",
						"type" 	=> "select",
						"std" 	=> "scroll",
						"class" => "av_2columns av_col_1",
						"no_first"=>true,
						"required" => array("colorset-$set_key-img",'{true}'),
						"target" => array("default_slideshow_target::.live-$set_key::background-attachment"),
						"subtype" => array(__('Scroll', 'avia_framework') =>'scroll',__('Fixed', 'avia_framework') =>'fixed'));







	$avia_elements[] = array(	"slug"	=> "styling", "type" => "visual_group_end", "id" => "avia_tab_end".$iterator, "nodescription" => true);
}




$avia_elements[] = array(	"slug"	=> "styling", "type" => "visual_group_start", "id" => "avia_tab54", "nodescription" => true, 'class'=>'avia_tab avia_tab2','name'=>__('Body background', 'avia_framework'),
							"required" => array("color-body_style",'{contains}box'),
							"inactive"	=> __("These options are only available if you select the 'boxed' or 'framed' layout. Your currently have a different layout selected", 'avia_framework'). "<br/><br/>".
							__("You can change that setting",'avia_framework').
							" <a href='#goto_layout'>".
							__("at General Layout",'avia_framework').
							"</a>");

$avia_elements[] =	array(
					"slug"	=> "styling",
					"name" 	=> __("Body Background color", 'avia_framework'),
					"desc" 	=> __("Background color for your site", 'avia_framework').
					"<br/>".
					 __("This is the color that is displayed behind your boxed content area", 'avia_framework'),
					"id" 	=> "color-body_color",
					"type" 	=> "colorpicker",
					"std" 	=> "#eeeeee",
/* 					"class" => "av_2columns av_col_2", */
					"target" => array("default_slideshow_target::.live_bg_wrap::background-color"),
					);



	$avia_elements[] = array(
						"slug"	=> "styling",
						"id" 	=> "color-body_img",
						"name" 	=> __("Background Image", 'avia_framework'),
						"desc" 	=> __("The background image of your Body", 'avia_framework')."<br/><br/>",
						"type" 	=> "select",
						"subtype" => array(__('No Background Image', 'avia_framework')=>'',__('Upload custom image', 'avia_framework')=>'custom'),
						"std" 	=> "",
						"no_first"=>true,
						"class" => "av_2columns av_col_1 set_blank_on_hide",
						"target" => array("default_slideshow_target::.live_bg_wrap::background-image"),
						"folder" => "images/background-images/",
						"folderlabel" => "",
						"required" => array("color-body_style",'boxed'),
						"group" => "Select predefined pattern",
						
						);


	$avia_elements[] =	array(
						"slug"	=> "styling",
						"name" 	=> __("Custom Background Image", 'avia_framework'),
						"desc" 	=> __("Upload a BG image for your Body", 'avia_framework')."<br/><br/>",
						"id" 	=> "color-body_customimage",
						"type" 	=> "upload",
						"std" 	=> "",
						"class" => "set_blank_on_hide av_2columns av_col_2",
						"label"	=> __("Use Image", 'avia_framework'),
						"required" => array("color-body_img",'custom'),
						"target" => array("default_slideshow_target::.live_bg_wrap::background-image"),
						);


	$avia_elements[] =	array(
						"slug"	=> "styling",
						"name" 	=> __("Position of the image", 'avia_framework'),
						"desc" 	=> "",
						"id" 	=> "color-body_pos",
						"type" 	=> "select",
						"std" 	=> "top left",
						"no_first"=>true,
						"class" => "av_2columns av_col_1",
						"required" => array("color-body_img",'{true}'),
						"target" => array("default_slideshow_target::.live_bg_wrap::background-position"),
						"subtype" => array(
							__('Top Left', 'avia_framework')		=>'top left',
							__('Top Center', 'avia_framework')		=>'top center',
							__(	'Top Right', 'avia_framework')		=>'top right',
							__('Bottom Left', 'avia_framework')		=>'bottom left',
							__('Bottom Center', 'avia_framework')	=>'bottom center',
							__(	'Bottom Right', 'avia_framework')	=>'bottom right',
							__(	'Center Left ', 'avia_framework')	=>'center left',
							__('Center Center', 'avia_framework')	=>'center center',
							__(	'Center Right', 'avia_framework')	=>'center right'));

	$avia_elements[] =	array(
						"slug"	=> "styling",
						"name" 	=> __("Repeat", 'avia_framework'),
						"desc" 	=> "",
						"id" 	=> "color-body_repeat",
						"type" 	=> "select",
						"std" 	=> "no-repeat",
						"class" => "av_2columns av_col_2",
						"no_first"=>true,
						"required" => array("color-body_img",'{true}'),
						"target" => array("default_slideshow_target::.live_bg_wrap::background-repeat"),
						"subtype" => array(
										__('no repeat', 'avia_framework')=>'no-repeat',
										__('Repeat', 'avia_framework')=>'repeat',
										__('Tile Horizontally', 'avia_framework')=>'repeat-x',
										__('Tile Vertically', 'avia_framework')=>'repeat-y',
										__('Stretch Fullscreen', 'avia_framework')=>'fullscreen'));

	$avia_elements[] =	array(
						"slug"	=> "styling",
						"name" 	=> __("Attachment", 'avia_framework'),
						"desc" 	=> "",
						"id" 	=> "color-body_attach",
						"type" 	=> "select",
						"std" 	=> "scroll",
						"class" => "av_2columns av_col_1",
						"no_first"=>true,
						"required" => array("color-body_img",'{true}'),
						"target" => array("default_slideshow_target::.live_bg_wrap::background-attachment"),
						"subtype" => array(__('Scroll', 'avia_framework')=>'scroll',__('Fixed', 'avia_framework')=>'fixed'));


$avia_elements[] = array(	"slug"	=> "styling", "type" => "visual_group_end", "id" => "avia_tab5_end", "nodescription" => true);










$avia_elements[] = array(	"slug"	=> "styling", "type" => "visual_group_start", "id" => "avia_tab6", "nodescription" => true, 'class'=>'avia_tab avia_tab2','name'=>__('Fonts', 'avia_framework'));


$avia_elements[] =		array(	"name" 	=> __("Heading Font", 'avia_framework'),
								"slug"	=> "styling",
								"desc" 	=> __("The Font heading utilizes google fonts and allows you to use a wide range of custom fonts for your headings", 'avia_framework'),
					            "id" 	=> "google_webfont",
					            "type" 	=> "select",
					            "no_first" => true,
					            "class" => "av_2columns av_col_1",
					            "onchange" => "avia_add_google_font",
					            "std" 	=> "Open Sans",
					            "subtype" =>  $google_fonts);

$avia_elements[] =	array(	"name" 	=> __("Defines the Font for your body text", 'avia_framework'),
							"slug"	=> "styling",
							"desc" 	=> __("Choose between web safe fonts (faster rendering) and google webkit fonts (more unqiue)", 'avia_framework')."<br/>",
				            "id" 	=> "default_font",
				            "type" 	=> "select",
				            "no_first" => true,
				            "class" => "av_2columns av_col_2",
				            "onchange" => "avia_add_google_font",
				            "std" 	=> "Helvetica-Neue,Helvetica-websave",
				            "subtype" => apply_filters('avf_google_content_font', array( __('Web save fonts', 'avia_framework') => array(
				            					'Arial'=>'Arial-websave',
				            					'Georgia'=>'Georgia-websave',
				            					'Verdana'=>'Verdana-websave',
				            					'Helvetica'=>'Helvetica-websave',
				            					'Helvetica Neue'=>'Helvetica-Neue,Helvetica-websave',
				            					'Lucida'=>'"Lucida-Sans",-"Lucida-Grande",-"Lucida-Sans-Unicode-websave"'),
				            					
				            					__('Google fonts', 'avia_framework') => array(
				            					'Arimo'=>'Arimo',
				            					'Cardo'=>'Cardo',
				            					'Droid Sans'=>'Droid Sans',
				            					'Droid Serif'=>'Droid Serif',
				            					'Kameron'=>'Kameron',
				            					'Maven Pro'=>'Maven Pro',
				            					'Open Sans'=>'Open Sans:400,600',
					            				'Lato'=>'Lato:300,400,700',
				            					'Lora'=>'Lora',

				            					))));


$avia_elements[] =	array(
						"slug"	=> "styling",
						"name" 	=> __("Default content font size", 'avia_framework'),
						"desc" 	=> __("The default font size for your content (eg: blog post content)", 'avia_framework'),
						"id" 	=> "color-default_font_size",
						"type" 	=> "select",
						"std" 	=> "",
						"no_first"=>true,
						"subtype" => array(
										__('Theme Default (13px)', 'avia_framework')=>'',
										'11px' =>'11px',
										'12px' =>'12px',
										'13px' =>'13px',
										'14px' =>'14px',
										'15px' =>'15px',
										'16px' =>'16px',
										'17px' =>'17px',
										'18px' =>'18px',
										'19px' =>'19px',
										'20px' =>'20px',
										'21px' =>'21px',
										'22px' =>'22px',
										'23px' =>'23px',
										'24px' =>'24px',
										'25px' =>'25px',
										));


$avia_elements[] = array(	"slug"	=> "styling", "type" => "visual_group_end", "id" => "avia_tabwe6_end", "nodescription" => true);


$avia_elements[] = array(	"slug"	=> "styling", "type" => "visual_group_end", "id" => "avia_tab_container_end", "nodescription" => true);


$avia_elements[] =	array(
					"slug"	=> "styling",
					"name" 	=> __("Quick CSS", 'avia_framework'),
					"desc" 	=> __("Just want to do some quick CSS changes? Enter them here, they will be applied to the theme. If you need to change major portions of the theme please use the custom.css file", 'avia_framework').
					" <a target='_blank' href='https://kriesi.at/documentation/enfold/using-a-child-theme/'>".
					__("or the Enfold Child theme.","avia_framework").
					"</a>"
					,
					"id" 	=> "quick_css",
					"type" 	=> "textarea"
					);




/*Sidebar*/




$avia_elements[] =	array(
					"slug"	=> "sidebars",
					"name" 	=> __("Sidebar on Archive Pages", 'avia_framework'),
					"desc" 	=> __("Choose the archive sidebar position here. This setting will be applied to all archive pages", 'avia_framework'),
					"id" 	=> "archive_layout",
					"type" 	=> "select",
					"std" 	=> "sidebar_right",
					"no_first"=>true,
					"subtype" => array( __('left sidebar', 'avia_framework') =>'sidebar_left',
										__('right sidebar', 'avia_framework') =>'sidebar_right',
										__('no sidebar', 'avia_framework') =>'fullsize'
										));




$avia_elements[] =	array(
					"slug"	=> "sidebars",
					"name" 	=> __("Sidebar on Blog Page", 'avia_framework'),
					"desc" 	=> __("Choose the blog sidebar position here. This setting will be applied to the blog page", 'avia_framework'),
					"id" 	=> "blog_layout",
					"type" 	=> "select",
					"std" 	=> "sidebar_right",
					"no_first"=>true,
					"subtype" => array( __('left sidebar', 'avia_framework') =>'sidebar_left',
										__('right sidebar', 'avia_framework') =>'sidebar_right',
										__('no sidebar', 'avia_framework') =>'fullsize'
										));




$avia_elements[] =	array(
					"slug"	=> "sidebars",
					"name" 	=> __("Sidebar on Single Post Entries", 'avia_framework'),
					"desc" 	=> __("Choose the blog post sidebar position here. This setting will be applied to single blog posts", 'avia_framework'),
					"id" 	=> "single_layout",
					"type" 	=> "select",
					"std" 	=> "sidebar_right",
					"no_first"=>true,
					"subtype" => array( __('left sidebar', 'avia_framework') =>'sidebar_left',
										__('right sidebar', 'avia_framework') =>'sidebar_right',
										__('no sidebar', 'avia_framework') =>'fullsize'
										));







$avia_elements[] =	array(
					"slug"	=> "sidebars",
					"name" 	=> __("Sidebar on Pages", 'avia_framework'),
					"desc" 	=> __("Choose the default page layout here. You can change the setting of each individual page when editing that page", 'avia_framework'),
					"id" 	=> "page_layout",
					"type" 	=> "select",
					"std" 	=> "sidebar_right",
					"no_first"=>true,
					"subtype" => array( __('left sidebar', 'avia_framework') =>'sidebar_left',
										__('right sidebar', 'avia_framework') =>'sidebar_right',
										__('no sidebar', 'avia_framework') =>'fullsize'
										));


$avia_elements[] =	array(
					"slug"	=> "sidebars",
					"name" 	=> __("Sidebar on Smartphones", 'avia_framework'),
					"desc" 	=> __("Show sidebar on smartphones (Sidebar is displayed then below the actual content)", 'avia_framework'),
					"id" 	=> "smartphones_sidebar",
					"type" 	=> "checkbox",
					"std" 	=> "",
					"no_first"=>true,
					"subtype" => array( __('Hide sidebar on smartphones', 'avia_framework') =>'',
										__('Show sidebar on smartphones', 'avia_framework')	=>'smartphones_sidebar_visible'
										));


$avia_elements[] =	array(
					"slug"	=> "sidebars",
					"name" 	=> __("Page Sidebar navigation", 'avia_framework'),
					"desc" 	=> __("Display a sidebar navigation for all nested subpages of a page automatically?", 'avia_framework'),
					"id" 	=> "page_nesting_nav",
					"type" 	=> "checkbox",
					"std" 	=> "true",
					"no_first"=>true,
					"subtype" => array( __('Display sidebar navigation', 'avia_framework') =>'true',
										__("Don't display Sidebar navigation", 'avia_framework') => ""
										));
$avia_elements[] =	array(
					"slug"	=> "sidebars",
					"name" 	=> __("Sidebar Separator Styling", 'avia_framework'),
					"desc" 	=> __("Do you want to separate the sidebar from your main content with a border?", 'avia_framework'),
					"id" 	=> "sidebar_styling",
					"type" 	=> "select",
					"std" 	=> "",
					"no_first"=>true,
					"subtype" => array( __('With Border', 'avia_framework') =>'',
										__('No Border', 'avia_framework') =>'no_sidebar_border',
										));

$avia_elements[] =	array(	"name" => __("Create new Sidebar Widget Areas", 'avia_framework'),
							"desc" => __("The theme supports the creation of custom widget areas. Simply open your", 'avia_framework') . " <a target='_blank' href='".admin_url('widgets.php')."'>".__('Widgets Page', 'avia_framework')."</a> ". 
									  __("and add a new Sidebar Area. Afterwards you can choose to display this Widget Area in the Edit Page Screen.", 'avia_framework'),
							"id" => "widgetdescription",
							"std" => "",
							"slug"	=> "sidebars",
							"type" => "heading",
							"nodescription"=>true);



/*Header Layout Settings*/


$avia_elements[] = array(	"slug"	=> "header", 
							"type" => "visual_group_start", 
							"id" => "header_conditional", 
							"nodescription" => true, 
							"required" => array('header_position','{contains}header_top'),
							"inactive"	=> __("These options are only available if you select a layout that has a main menu positioned at the top. You currently have your main menu placed in a sidebar",'avia_framework') ."<br/><br/>".
							__("You can change that setting",'avia_framework').
							" <a href='#goto_layout'>".
							__("at General Layout",'avia_framework').
							"</a>");




$frontendheader_label = __("A rough layout preview of the header area", 'avia_framework');
			
$avia_elements[] =	array(
					"slug"	=> "header",
					"id" 	=> "default_header_target",
					"type" 	=> "target",
					"std" 	=> "
					<style type='text/css'>
					
					#avia_options_page #avia_default_header_target{background: #f8f8f8;border: none;padding: 30px;border-bottom: 1px solid #e5e5e5; margin-bottom: 25px;}
					#avia_header_preview{color:#999; border:1px solid #e1e1e1; padding:0px 45px; overflow:hidden; background-color:#fff; position: relative;}
					
					#avia_options_page #pr-main-area{line-height:69px; overflow:hidden;}
					#pr-menu{float:right; font-size:12px; line-height: inherit;}	
					
					#pr-menu .pr-menu-single{display:inline-block; padding:0px 7px; position:relative; }
					#pr-menu .main_nav_header .pr-menu-single{padding:20px 7px;}
					
					#pr-menu-inner.seperator_small_border .pr-menu-single{display:inline; border-right: 1px solid #e1e1e1; padding:0px 7px;}
					#pr-menu-inner.seperator_big_border .pr-menu-single{ border-right: 1px solid #e1e1e1; width: 80px; text-align: center; padding: 25px 7px;}
					#pr-menu-inner.seperator_big_border .pr-menu-single-first{border-left:1px solid #e1e1e1;}
					
					
					.bottom_nav_header #pr-menu-inner.seperator_big_border .pr-menu-single{padding: 9px 7px;}
					
					#pr-logo{ max-width: 150px; max-height: 70px; float:left;}
					#avia_header_preview.large #pr-logo{ max-width: 215px; max-height: 115px; padding-top:0px;}
					#avia_header_preview.large .main_nav_header #pr-menu-inner.seperator_big_border .pr-menu-single{padding: 48px 7px;}
					#avia_options_page #avia_header_preview.large #pr-main-area{line-height:15px;}
					
					#search_icon{opacity:0.3; margin-left: 10px; top:26px; position:relative; display:none; z-index:10; height:16px;}
					#search_icon.header_searchicon{display:inline; top:4px;}
					#pr-content-area{display:block; clear:both; padding:15px 45px; overflow:hidden; background-color:#fcfcfc; text-align:center; border:1px solid #e1e1e1; border-top:none;}
					.logo_right #pr-logo{float:right}
					.logo_center{text-align:center;}
					.logo_center #pr-logo{float:none}
					.menu_left #pr-menu{float:left}
					#avia_options_page .bottom_nav_header#pr-main-area{line-height: 1em;}
					.bottom_nav_header #pr-menu{float:none; clear:both; line-height:36px; }
					.top_nav_header div#pr-menu { position: absolute; top: -1px; width: 100%; left: 0; }
					.top_nav_header#pr-main-area{margin-top:40px;}
					.bottom_nav_header #pr-menu:before { content: ''; border-top: 1px solid #e1e1e1; width: 150%; position:absolute; height: 1px; left: -50px;}
					.top_nav_header #pr-menu:before{ top: 36px; }
					.minimal_header .top_nav_header #pr-menu:before{opacity:0;}
					.minimal_header_shadow .top_nav_header #pr-menu:before{opacity:1; box-shadow: 0 1px 3px 0px rgba(0,0,0,0.1); }
					
					
					#pr-menu-2nd{height: 28px; color:#aaa; border:1px solid #e1e1e1; padding:5px 45px; overflow:hidden; background-color:#f8f8f8; border-bottom:none; display:none; font-size:11px;}
					.extra_header_active #pr-menu-2nd{display:block;}
					.pr-secondary-items{display:none;}
					.secondary_left .pr-secondary-items, .secondary_right .pr-secondary-items{display:block; float:left; margin:0 10px 0 0;}
					.secondary_right .pr-secondary-items{float:right; margin:0 0 0 10px;}
					
					.pr-icons{opacity:0.3; display:none; position:relative; top:1px;}
					.icon_active_left.extra_header_active #pr-menu-2nd .pr-icons{display:block; float:left; margin:0 10px 0 0;}
					.icon_active_right.extra_header_active #pr-menu-2nd .pr-icons{display:block; float:right; margin:0 0 0 10px ;}
					
					.icon_active_main #pr-main-icon{float:right; position:relative; line-height:inherit;}
					.icon_active_main #pr-main-icon .pr-icons{display:block; top: 3px; margin: 0 0 0 17px; line-height:inherit; width:66px;}					
					.icon_active_main .logo_right #pr-main-icon {left: 211px; float: left; width: 0px;}
					.icon_active_main .logo_right #pr-main-icon {left: 211px; float: left; width: 0px;}
					.icon_active_main .large .logo_right #pr-main-icon {left:-55px;}
					
					.icon_active_main .bottom_nav_header #pr-main-icon{top:23px;}
					.icon_active_main .large #pr-main-icon{top:46px;}
					
					.icon_active_main .logo_right.bottom_nav_header #pr-main-icon{float:left; left:-17px;}
					.icon_active_main .logo_center.bottom_nav_header #pr-main-icon{float: right; top: 0px; position: absolute; right: 24px;}
					.icon_active_main .large .logo_center.bottom_nav_header #pr-main-icon{top: 29px;}
					.icon_active_main .logo_center.bottom_nav_header #pr-main-icon .pr-icons{margin:0; top:35px;}
					.icon_active_main .large .logo_center.bottom_nav_header #pr-main-icon .pr-icons { top: 23px; }
										
					.pr-phone-items{display:none;}
					.phone_active_left  .pr-phone-items{display:block; float:left;}
					.phone_active_right .pr-phone-items{display:block; float:right;}
					
					.header_stretch #avia_header_preview, .header_stretch #pr-menu-2nd{ padding-left: 15px; padding-right: 15px; }
					.header_stretch .icon_active_main .logo_right.menu_left #pr-main-icon {left:-193px;}
					
					.inner-content{color:#999; text-align: justify; }
					
					#pr-breadcrumb{line-height:23px; color:#aaa; border:1px solid #e1e1e1; padding:5px 45px; overflow:hidden; background-color:#f8f8f8; border-top:none; font-size:16px;}
					#pr-breadcrumb .some-breadcrumb{float:right; font-size:11px; line-height:23px;}
					#pr-breadcrumb.title_bar .some-breadcrumb, #pr-breadcrumb.hidden_title_bar{ display:none; }
					
					.pr-menu-single.pr-menu-single-first:after {
					content: '';
					width: 90%;
					height: 1px;
					border-bottom: 2px solid #9cc2df;
					display: block;
					top: 85%;
					left: 7%;
					position: absolute;
					}
					
					.burger_menu #pr-menu-inner{
						display:none;
					}
										
					#pr-burger-menu{
						    display: none;
						    height: 40px;
						    width: 30px;
						    margin-top: 17px;
						    margin-left:20px;
						    float: right;
						    position: relative;
						    z-index:10;
					}
					
					#avia_header_preview.large #pr-burger-menu{margin-top: 39px;}
					
					#pr-burger-menu span{
						display:block;
						border-top:4px solid #aaa;
						margin-top: 6px;
					}
					
					.main_nav_header .burger_menu #pr-burger-menu{
						display:block;
					}
				
					.seperator_small_border .pr-menu-single.pr-menu-single-first:after { top: 145%; }
					.seperator_big_border .pr-menu-single.pr-menu-single-first:after { top: 98%; left: 0; width: 100%;}
					.bottom_nav_header .pr-menu-single.pr-menu-single-first:after { top: 92%; left: 0%; width:100%; }
					
					.minimal_header .pr-menu-single.pr-menu-single-first:after{display:none;}
					.minimal_header #avia_header_preview{border-bottom:none;}
					.minimal_header_shadow #avia_header_preview { box-shadow: 0 2px 8px 0px rgba(0,0,0,0.1); }
					
					.bottom_nav_header #search_icon.header_searchicon{float:right; top: 10px;}
					.burger_menu #pr-burger-menu{display:block;}
					#avia_header_preview .bottom_nav_header #pr-burger-menu{ margin:0; float:left; }
					.top_nav_header #search_icon, .top_nav_header #pr-burger-menu{margin:0px 10px;}
					
					</style>
					<div class='av-header-area-preview' >
						<div id='pr-stretch-wrap' >
							<small class='live_bg_small'>{$frontendheader_label}</small>
							<div id='pr-header-style-wrap' >
								<div id='pr-phone-wrap' >
									<div id='pr-social-wrap' >
										<div id='pr-seconary-menu-wrap' >
											<div id='pr-menu-2nd'>{$iconSpan}<span class='pr-secondary-items'>Login | Signup | etc</span><span class='pr-phone-items'>Phone: 555-4432</span></div>
											<div id='avia_header_preview' >
												<div id='pr-main-area' >
													<img id='pr-logo' src='".AVIA_BASE_URL."images/layout/logo_modern.png' alt=''/>
													<div id='pr-main-icon'>{$iconSpan}</div>
													<div id='pr-menu'>
													
													
													<span id='pr-menu-inner'><span class='pr-menu-single pr-menu-single-first'>Home</span><span class='pr-menu-single'>About</span><span class='pr-menu-single'>Contact</span></span> <img id='search_icon' src='".AVIA_BASE_URL."images/layout/search.png'  alt='' />
													<div id='pr-burger-menu'>
														<span class='burger-top'></span>
														<span class='burger-mid'></span>
														<span class='burger-low'></span>
													</div>
													
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div id='pr-breadcrumb'>Some Title <span class='some-breadcrumb'>Home  &#187; Admin  &#187; Header </span></div>
							<div id='pr-content-area'> Content / Slideshows / etc 
							<div class='inner-content'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium sem.</div>
							</div>
						</div>
					</div>
					",
					"nodescription" => true
					);

//START TAB CONTAINER
$avia_elements[] = array(	"slug"	=> "header", "type" => "visual_group_start", "id" => "avia_tab1", "nodescription" => true, 'class'=>'avia_tab_container avia_set');
$avia_elements[] = array(	"slug"	=> "header", "type" => "visual_group_start", "id" => "avia_tab5", "nodescription" => true, 'class'=>'avia_tab avia_tab2','name'=>__('Header layout', 'avia_framework'));
// START TAB

$avia_elements[] =	array(
					"slug"	=> "header",
					"name" 	=> __("Menu and Logo Position", 'avia_framework'),
					"desc" 	=> __("You can choose various different logo and main menu positions here", 'avia_framework'),
					"id" 	=> "header_layout",
					"type" 	=> "select",
					"std" 	=> "",
					"class" => "av_2columns av_col_1",
					"no_first"=>true,
					"target" => array(".av-header-area-preview::#pr-main-area::set_class"),
					"subtype" => array( __('Logo left, Menu right', 'avia_framework')  	=>'logo_left main_nav_header menu_right',
										__('Logo right, Menu Left', 'avia_framework')	 	=>'logo_right main_nav_header menu_left',
										__('Logo left, Menu below', 'avia_framework') 	=>'logo_left bottom_nav_header menu_left',
										__('Logo right, Menu below', 'avia_framework') 	=>'logo_right bottom_nav_header menu_center',
										__('Logo center, Menu below', 'avia_framework') 	=>'logo_center bottom_nav_header menu_right',
										__('Logo center, Menu above', 'avia_framework') 	=>'logo_center bottom_nav_header top_nav_header menu_center',
										));
										
$avia_elements[] =	array(
					"slug"	=> "header",
					"name" 	=> __("Header Size", 'avia_framework'),
					"desc" 	=> __("Choose a predefined header size. You can also apply a custom height to the header", 'avia_framework'),
					"id" 	=> "header_size",
					"type" 	=> "select",
					"std" 	=> "",
					"class" => "av_2columns av_col_2",
					"target" => array(".av-header-area-preview::#avia_header_preview::set_class"),
					"no_first"=>true,
					"subtype" => array( __('slim', 'avia_framework')  				=>'slim',
										__('large', 'avia_framework')	 			=>'large',
										__('custom pixel value', 'avia_framework') 	=>'custom',
										));				


$customsize = array();
for ($x=45; $x<=300; $x++){ $customsize[$x.'px'] = $x; }	
								
$avia_elements[] =	array(
					"slug"	=> "header",
					"name" 	=> __("Header Custom Height", 'avia_framework'),
					"desc" 	=> __("Choose a custom height in pixels (wont be reflected in the preview above, only on your actual page)", 'avia_framework'),
					"id" 	=> "header_custom_size",
					"type" 	=> "select",
					"std" 	=> "150",
					"required" => array('header_size','custom'),
					"no_first"=>true,
					"subtype" => $customsize);											


$avia_elements[] =	array(
					"slug"	=> "header",
					"name" 	=> __("Header Style", 'avia_framework'),
					"desc" 	=> __("Choose which header style you want to use", 'avia_framework'),
					"id" 	=> "header_style",
					"type" 	=> "select",
					"std" 	=> "",
					"target" => array(".av-header-area-preview::#pr-header-style-wrap::set_class"),
					"no_first"=>true,
					"subtype" => array( __('Default (with borders, active menu indicator and slightly transparent)', 'avia_framework')  =>'',
										__('Minimal (no borders, indicators or transparency)', 'avia_framework') =>'minimal_header',
										__('Minimal with drop shadow (no borders, indicators or transparency)', 'avia_framework') =>'minimal_header minimal_header_shadow',
										));



										
$avia_elements[] =	array(
					"slug"	=> "header",
					"name" 	=> __("Header Title and Breadcrumbs", 'avia_framework'),
					"desc" 	=> __("Choose if and how you want to display the Title and Breadcrumb of your page. This option can be overwritten when writing/editing a page", 'avia_framework'),
					"id" 	=> "header_title_bar",
					"type" 	=> "select",
					"std" 	=> "title_bar_breadcrumb",
					"target" => array(".av-header-area-preview::#pr-breadcrumb::set_class"),
					"no_first"=>true,
					"subtype" => array( __('Display title and breadcrumbs', 'avia_framework')  =>'title_bar_breadcrumb',
										__('Display only title', 'avia_framework')	 		 =>'title_bar',
										__('Display only breadcrumbs', 'avia_framework')	 =>'breadcrumbs_only',
										__('Hide both', 'avia_framework') 					 =>'hidden_title_bar',
										));											
										
										
																
// END TAB
$avia_elements[] = array(	"slug"	=> "header", "type" => "visual_group_end", "id" => "avia_tab5_end", "nodescription" => true);
$avia_elements[] = array(	"slug"	=> "header", "type" => "visual_group_start", "id" => "avia_tab5", "nodescription" => true, 'class'=>'avia_tab avia_tab2','name'=>__('Header behavior', 'avia_framework'));
// START TAB
										
$avia_elements[] = array(
							"name" 	=> __("Sticky Header", 'avia_framework'),
							"desc" 	=> __("If checked the header will stick to the top of your site if user scrolls down (ignored on smartphones)", 'avia_framework'),
							"id" 	=> "header_sticky",
							"type" 	=> "checkbox",
							"std"	=> "true",
							"slug"	=> "header");								

$avia_elements[] = array(
							"name" 	=> __("Shrinking Header", 'avia_framework'),
							"desc" 	=> __("If checked the sticky header will shrink once the user scrolls down (ignored on smartphones + tablets)", 'avia_framework'),
							"id" 	=> "header_shrinking",
							"type" 	=> "checkbox",
							"std"	=> "true",
							"required" => array('header_sticky','header_sticky'),
							"slug"	=> "header");

$avia_elements[] = array(
							"name" 	=> __("Unstick topbar", 'avia_framework'),
							"desc" 	=> __("If checked the small top bar above the header with social icons, secondary menu and extra information will no longer stick to the top", 'avia_framework'),
							"id" 	=> "header_unstick_top",
							"type" 	=> "checkbox",
							"std"	=> "",
							"required" => array('header_sticky','header_sticky'),
							"slug"	=> "header");


$avia_elements[] = array(
							"name" 	=> __("Let logo and menu position adapt to browser window", 'avia_framework'),
							"desc" 	=> __("If checked the elements in your header will always be placed at the browser window edge, instead of matching the content width", 'avia_framework'),
							"id" 	=> "header_stretch",
							"type" 	=> "checkbox",
							"std"	=> "",
							"target" => array(".av-header-area-preview::#pr-stretch-wrap::set_class"),
							"slug"	=> "header");

// END TAB
$avia_elements[] = array(	"slug"	=> "header", "type" => "visual_group_end", "id" => "avia_tab5_end", "nodescription" => true);


$avia_elements[] = array(	"slug"	=> "header", "type" => "visual_group_start", "id" => "avia_tab5", "nodescription" => true, 'class'=>'avia_tab avia_tab2','name'=>__('Extra Elements', 'avia_framework'));
// START TAB


$avia_elements[] =	array(
					"slug"	=> "header",
					"name" 	=> __("Header Social Icons", 'avia_framework'),
					"desc" 	=> __("Choose if and where to display social icons. You can define the icons at", 'avia_framework').
					" <a href='#goto_social'>".
					__("Social Profiles","avia_framework").
					"</a>"
					,
					"id" 	=> "header_social",
					"type" 	=> "select",
					"std" 	=> "",
					"class" => "av_2columns av_col_1",
					"target" => array(".av-header-area-preview::#pr-social-wrap::set_class"),
					"no_first"=>true,
					"subtype" => array( __('No social Icons', 'avia_framework')  		=>'',
										__('Display in top bar at the left', 'avia_framework')	 =>'icon_active_left extra_header_active',
										__('Display in top bar at the right', 'avia_framework')    =>'icon_active_right extra_header_active',
										__('Display in main header area', 'avia_framework')    	 =>'icon_active_main',
										));	

$avia_elements[] =	array(
					"slug"	=> "header",
					"name" 	=> __("Header Secondary Menu", 'avia_framework'),
					"desc" 	=> __("Choose if you want to display a secondary menu and where to display it", 'avia_framework'),
					"id" 	=> "header_secondary_menu",
					"type" 	=> "select",
					"std" 	=> "",
					"class" => "av_2columns av_col_2",
					"target" => array(".av-header-area-preview::#pr-seconary-menu-wrap::set_class"),
					"no_first"=>true,
					"subtype" => array( __('No Secondary Menu', 'avia_framework')  	=>'',
										__('Secondary Menu in top bar at the left', 'avia_framework')	 =>'secondary_left extra_header_active',
										__('Secondary Menu in top bar at the right', 'avia_framework') =>'secondary_right extra_header_active',
										));	

$avia_elements[] =	array(
					"slug"	=> "header",
					"name" 	=> __("Header Phone Number/Extra Info", 'avia_framework'),
					"desc" 	=> __("Choose if you want to display an additional phone number or some extra info in your header", 'avia_framework'),
					"id" 	=> "header_phone_active",
					"type" 	=> "select",
					"std" 	=> "",
					"class" => "av_2columns av_col_1",
					"target" => array(".av-header-area-preview::#pr-phone-wrap::set_class"),
					"no_first"=>true,
					"subtype" => array( __('No Phone Number/Extra Info', 'avia_framework') 		=>'',
										__('Display in top bar at the left', 'avia_framework')	 =>'phone_active_left extra_header_active',
										__('Display in top bar at the right', 'avia_framework')    =>'phone_active_right extra_header_active',
										));	

$avia_elements[] = array(
						"name" 	=> __("Phone Number or small info text", 'avia_framework'),
						"desc" 	=> __("Add the text that should be displayed in your header here", 'avia_framework'),
						"id" 	=> "phone",
						"type" 	=> "text",
						"std"	=> "",
						"class" => "av_2columns av_col_2",
						"required" => array('header_phone_active','{contains}phone_active'),
						"slug"	=> "header");
						
						
						


// END TAB
$avia_elements[] = array(	"slug"	=> "header", "type" => "visual_group_end", "id" => "avia_tab5_end", "nodescription" => true);
$avia_elements[] = array(	"slug"	=> "header", "type" => "visual_group_start", "id" => "avia_tab5", "nodescription" => true, 'class'=>'avia_tab avia_tab2','name'=>__('Transparency Options', 'avia_framework'));
// START TAB
$avia_elements[] =	array(	"name" => __("What is header transparency",'avia_framework'),
							"desc" => __("When creating/editing a page you can select to have the header be transparent and display the content (usually a fullwidth slideshow or a fullwidth image) beneath. In those cases you will usually need a different Logo and Main Menu color which can be set here.",'avia_framework')."<br/><a class='av-modal-image' href='".get_template_directory_uri()."/images/framework-helper/header_transparency.jpg'>".__('(Show example Screenshot)','avia_framework')."</a>",
							"id" => "transparency_description",
							"std" => "",
							"slug"	=> "header",
							"type" => "heading",
							"nodescription"=>true);
							
							
$avia_elements[] =	array(
					"slug"	=> "header",
					"name" 	=> __("Transparency Logo", 'avia_framework'),
					"desc" 	=> __("Upload a logo image, or enter the URL or ID of an image if its already uploaded. (Leave empty to use the default logo)", 'avia_framework'),
					"id" 	=> "header_replacement_logo",
					"type" 	=> "upload",
					"label"	=> __("Use Image as logo", 'avia_framework'));


$avia_elements[] =	array(
					"slug"	=> "header",
					"name" 	=> __("Transparency menu color", 'avia_framework'),
					"desc" 	=> __("Menu color for transparent header (Leave empty to use the default color)", 'avia_framework'),
					"id" 	=> "header_replacement_menu",
					"type" 	=> "colorpicker",
					"std" 	=> ""
					);

// END TAB
$avia_elements[] = array(	"slug"	=> "header", "type" => "visual_group_end", "id" => "avia_tab5_end", "nodescription" => true);



//END TAB CONTAINER
$avia_elements[] = array(	"slug"	=> "header", "type" => "visual_group_end", "id" => "avia_tab_container_end", "nodescription" => true);

								
// close conditional 
$avia_elements[] = array(	"slug"	=> "header", "type" => "visual_group_end", "id" => "header_conditional_close", "nodescription" => true);


/*social settings*/

$avia_elements[] =	array(	"name" => __("Your social profiles", 'avia_framework'),
							"desc" => __("You can enter links to your social profiles here. Afterwards you can choose where to display them by activating them in the respective area", 'avia_framework') ." (". __("e.g:", 'avia_framework') . " <a href='#goto_layout'>". __("General Layout", 'avia_framework') . "</a>, <a href='#goto_header'>". __("Header", 'avia_framework') . "</a>, <a href='#goto_footer'>". __("Footer", 'avia_framework') . "</a> )", 
							"id" => "socialdescription",
							"std" => "",
							"slug"	=> "social",
							"type" => "heading",
							"nodescription"=>true);



$avia_elements[] =	array(
					"type" 			=> "group",
					"id" 			=> "social_icons",
					"slug"			=> "social",
					"linktext" 		=> "+",
					"deletetext" 	=> "×",
					"blank" 		=> true,
					"nodescription" => true,
					"std"			=> array(
										array('social_icon'=>'twitter', 'social_icon_link'=>'http://twitter.com/kriesi'),
										array('social_icon'=>'dribbble', 'social_icon_link'=>'http://dribbble.com/kriesi'),
										),
					'subelements' 	=> array(

							array(
								"name" 	=> __("Social Icon", 'avia_framework'),
								"desc" 	=> "",
								"id" 	=> "social_icon",
								"type" 	=> "select",
								"slug"	=> "sidebar",
								"class" => "av_2columns av_col_1",
								"subtype" => apply_filters('avf_social_icons_options', array(

									'500px' 	=> 'five_100_px',
									'Behance' 	=> 'behance',
									'Dribbble' 	=> 'dribbble',
									'Facebook' 	=> 'facebook',
									'Flickr' 	=> 'flickr',
									'Google Plus' => 'gplus',
									'Instagram'  => 'instagram',
									'LinkedIn' 	=> 'linkedin',
									'Pinterest' 	=> 'pinterest',
									'Reddit' 	=> 'reddit',
									'Skype' 	=> 'skype',
									'Soundcloud'=> 'soundcloud',
									'Tumblr' 	=> 'tumblr',
									'Twitter' 	=> 'twitter',
									'Vimeo' 	=> 'vimeo',
									'Vk' 		=> 'vk',
									'Xing' 		=> 'xing',
									'YouTube'   => 'youtube',
									__('Special: RSS (add RSS URL, leave blank if you want to use default WordPress RSS feed)', 'avia_framework') => 'rss',
									__('Special: Email Icon (add your own URL to link to a contact form)', 'avia_framework') => 'mail',

								))),

							array(
								"name" 	=> __("Social Icon URL:", 'avia_framework'),
								"desc" 	=> "",
								"id" 	=> "social_icon_link",
								"type" 	=> "text",
								"slug"	=> "sidebar",
								"class" => "av_2columns av_col_2"),
						        )
						);




/*footer settings*/


$avia_elements[] =	array(
					"slug"	=> "footer",
					"name" 	=> __("Default Footer Widgets & Socket Settings", 'avia_framework'),
					"desc" 	=> __("Do you want to display the footer widgets & footer socket?", 'avia_framework'),
					"id" 	=> "display_widgets_socket",
					"type" 	=> "select",
					"std" 	=> "all",
					"no_first" => true,
					"subtype" => array(
				                    __('Display the footer widgets & socket', 'avia_framework') =>'all',
				                    __('Display only the footer widgets (no socket)', 'avia_framework') =>'nosocket',
				                    __('Display only the socket (no footer widgets)', 'avia_framework') =>'nofooterwidgets',
				                    __("Don't display the socket & footer widgets", 'avia_framework') =>'nofooterarea'
									)
					);




$avia_elements[] =	array(
					"slug"	=> "footer",
					"name" 	=> __("Footer Columns", 'avia_framework'),
					"desc" 	=> __("How many columns should be displayed in your footer", 'avia_framework'),
					"id" 	=> "footer_columns",
					"type" 	=> "select",
					"std" 	=> "4",
					"subtype" => array(
						__('1', 'avia_framework') =>'1',
						__('2', 'avia_framework') =>'2',
						__('3', 'avia_framework') =>'3',
						__('4', 'avia_framework') =>'4',
						__('5', 'avia_framework') =>'5'));

$avia_elements[] =	array(
					"slug"	=> "footer",
					"name" 	=> __("Copyright", 'avia_framework'),
					"desc" 	=> __("Add a custom copyright text at the bottom of your site. eg:", 'avia_framework')."<br/><strong>&copy; ".__('Copyright','avia_framework')."  - ".get_bloginfo('name')."</strong>",
					"id" 	=> "copyright",
					"type" 	=> "text",
					"std" 	=> ""

					);


$avia_elements[] = array(
		"name" 	=> __("Social Icons", 'avia_framework'),
		"desc" 	=> __("Check to display the social icons defined in", 'avia_framework').
				" <a href='#goto_social'>".
				__("Social Profiles", 'avia_framework').
				"</a> ".
				 __("in your socket", 'avia_framework'),
		"id" 	=> "footer_social",
		"type" 	=> "checkbox",
		"std"	=> "",
		"slug"	=> "footer");	



/*blog settings*/

$avia_elements[] =	array(
					"slug"	=> "blog",
					"name" 	=> __("Blog Styling", 'avia_framework' ),
					"desc" 	=> __("Choose the blog styling here.", 'avia_framework' ),
					"id" 	=> "blog_global_style",
					"type" 	=> "select",
					"std" 	=> "",
					"no_first"=>true,
					"subtype" => array( 
									__( 'Default (Business)', 'avia_framework' ) =>'',
									__( 'Elegant', 'avia_framework' ) =>'elegant-blog',
									__( 'Modern Business', 'avia_framework' ) =>'elegant-blog modern-blog',
										));




$avia_elements[] =	array(
					"slug"	=> "blog",
					"name" 	=> __("Blog Layout", 'avia_framework' ),
					"desc" 	=> __("Choose the default blog layout here.", 'avia_framework' )."<br/><br/>".__("You can either choose a predefined layout or build your own blog layout with the advanced layout editor", 'avia_framework' ),
					"id" 	=> "blog_style",
					"type" 	=> "select",
					"std" 	=> "single-small",
					"no_first"=>true,
					"subtype" => array( 
									__( 'Multi Author Blog (displays Gravatar of the article author beside the entry and feature images above)', 'avia_framework' ) =>'multi-big',
									__( 'Single Author, small preview Pic (no author picture is displayed, feature image is small)', 'avia_framework' ) =>'single-small',
									__( 'Single Author, big preview Pic (no author picture is displayed, feature image is big)', 'avia_framework' ) =>'single-big',
									__( 'Grid Layout', 'avia_framework' ) =>'blog-grid',
									__( 'Use the advance layout editor to build your own blog layout (simply edit the page you have chosen in Enfold->Theme Options as a blog page)', 'avia_framework') =>'custom',
										));



$avia_elements[] = array("slug"	=> "blog", "type" => "visual_group_start", "id" => "avia_share_links_start", "nodescription" => true);	
    
$avia_elements[] =	array(	"name" => __("Single Post Options", 'avia_framework'),
							"desc" => __("Here you can set options that affect your single blog post layout", 'avia_framework'),
							"id" => "widgetdescription",
							"std" => "",
							"slug"	=> "blog",
							"type" => "heading",
							"nodescription"=>true);
  
$avia_elements[] = array(
		"name" 	=> __("Disable the post navigation", 'avia_framework'),
		"desc" 	=> __("Check to disable the post navigation that links to the next/previous post on single entries ", 'avia_framework'),
		"id" 	=> "disable_post_nav",
		"type" 	=> "checkbox",
		"std"	=> "",
		"slug"	=> "blog");  
  
$avia_elements[] =	array(
    "slug"	=> "blog",
    "name" 	=> __("Single Post Style", 'avia_framework'),
    "desc" 	=> __("Choose the single post style here.", 'avia_framework'),
    "id" 	=> "single_post_style",
    "type" 	=> "select",
    "std" 	=> "single-big",
    "no_first"=>true,
    "subtype" => array( __('Single post with small preview image (featured image)', 'avia_framework') =>'single-small',
        __('Single post with big preview image (featured image)', 'avia_framework') =>'single-big',
        __('Multi Author Blog (displays Gravatar of the article author beside the entry and feature images above)', 'avia_framework') =>'multi-big'
    ));
    
    

$avia_elements[] =	array(
    "slug"	=> "blog",
    "name" 	=> __("Related Entries", 'avia_framework'),
    "desc" 	=> __("Choose if and how you want to display your related entries. (Related entries are based on tags. If a post does not have any tags then no related entries will be shown)", 'avia_framework'),
    "id" 	=> "single_post_related_entries",
    "type" 	=> "select",
    "std" 	=> "av-related-style-tooltip",
    "no_first"=>true,
    "subtype" => array( __('Show Thumnails and display post title by tooltip', 'avia_framework') =>'av-related-style-tooltip',
        				__('Show Thumbnail and post title by default', 'avia_framework') =>'av-related-style-full',
        				__('Disable related entries', 'avia_framework') =>'disabled'
    ));
    
    


    
$avia_elements[] =	array(	"name" => __("Blog meta elements", 'avia_framework'),
							"desc" => __("You can choose to hide some of the default Blog elements here:", 'avia_framework'),
							"id" => "widgetdescription",
							"std" => "",
							"slug"	=> "blog",
							"type" => "heading",
							"nodescription"=>true);


$avia_elements[] = array(
		"name" 	=> __("Blog Post Author", 'avia_framework'),
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "blog-meta-author",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"class" => "av_3col av_col_1",
		"slug"	=> "blog");	
		
		
$avia_elements[] = array(
		"name" 	=> __("Blog Post Comment Count", 'avia_framework'),
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "blog-meta-comments",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"class" => "av_3col av_col_2",
		"slug"	=> "blog");			
		
$avia_elements[] = array(
		"name" 	=> __("Blog Post Category", 'avia_framework'),
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "blog-meta-category",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"class" => "av_3col av_col_2",
		"slug"	=> "blog");	
		
		

$avia_elements[] = array(
		"name" 	=> __("Blog Post Date", 'avia_framework'),
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "blog-meta-date",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"class" => "av_3col av_col_1",
		"slug"	=> "blog");	
		
		
$avia_elements[] = array(
		"name" 	=> __("Blog Post Allowed HTML Tags", 'avia_framework'),
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "blog-meta-html-info",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"class" => "av_3col av_col_2",
		"slug"	=> "blog");	

$avia_elements[] = array(
		"name" 	=> __("Blog Post Tags", 'avia_framework'),
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "blog-meta-tag",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"class" => "av_3col av_col_3",
		"slug"	=> "blog");	


    

$avia_elements[] = array("slug"	=> "blog", "type" => "visual_group_end", "id" => "avia_share_links_start", "nodescription" => true);	

$avia_elements[] = array("slug"	=> "blog", "type" => "visual_group_start", "id" => "avia_share_links_start", "nodescription" => true);	
    
$avia_elements[] =	array(	"name" => __("Share links at the bottom of your blog post", 'avia_framework'),
							"desc" => __("The theme allows you to display share links to various social networks at the bottom of your blog posts. Check which links you want to display:", 'avia_framework'),
							"id" => "widgetdescription",
							"std" => "",
							"slug"	=> "blog",
							"type" => "heading",
							"nodescription"=>true);


$avia_elements[] = array(
		"name" 	=> __("Facebook link", 'avia_framework'),
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "share_facebook",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"class" => "av_3col av_col_1",
		"slug"	=> "blog");	
		
		
$avia_elements[] = array(
		"name" 	=> __("Twitter link", 'avia_framework'),
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "share_twitter",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"class" => "av_3col av_col_2",
		"slug"	=> "blog");			
		
$avia_elements[] = array(
		"name" 	=> __("Pinterest link ", 'avia_framework'),
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "share_pinterest",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"class" => "av_3col av_col_2",
		"slug"	=> "blog");	
		
				
		
		
$avia_elements[] = array(
		"name" 	=> __("Google Plus link", 'avia_framework'),
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "share_gplus",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"class" => "av_3col av_col_1",
		"slug"	=> "blog");	
		
		
$avia_elements[] = array(
		"name" 	=> __("Reddit link", 'avia_framework'),
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "share_reddit",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"class" => "av_3col av_col_2",
		"slug"	=> "blog");			
		
$avia_elements[] = array(
		"name" 	=> __("Linkedin link ", 'avia_framework'),
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "share_linkedin",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"class" => "av_3col av_col_2",
		"slug"	=> "blog");				
		

$avia_elements[] = array(
		"name" 	=> __("Tumblr link", 'avia_framework'),
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "share_tumblr",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"class" => "av_3col av_col_1",
		"slug"	=> "blog");	
		
$avia_elements[] = array(
		"name" 	=> __("VK link", 'avia_framework'),
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "share_vk",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"class" => "av_3col av_col_2",
		"slug"	=> "blog");	
	
		
$avia_elements[] = array(
		"name" 	=> __("Email link", 'avia_framework'),
		"desc" 	=> __("Check to display", 'avia_framework'),
		"id" 	=> "share_mail",
		"type" 	=> "checkbox",
		"std"	=> "true",
		"class" => "av_3col av_col_2",
		"slug"	=> "blog");				
		
		
		
		
		

		
$avia_elements[] = array("slug"	=> "blog", "type" => "visual_group_end", "id" => "avia_share_links_end", "nodescription" => true);	
		
		
		

$avia_elements[] =	array(	"name" => __("Import demo files", 'avia_framework'),
							"desc" => __("If you are new to wordpress or have problems creating posts or pages that look like the Theme Demo you can import dummy posts and pages here that will definitely help to understand how those tasks are done.", 'avia_framework')."<br/><br/><strong class='av-text-notice'>".
							__("Notice: If you want to completely remove a demo installation after importing it, you can use a plugin like", 'avia_framework')." <a target='_blank' href='https://wordpress.org/plugins/wordpress-reset/'>WordPress Reset</a></strong>"
							,
							"id" => "widgetdescription",
							"std" => "",
							"slug"	=> "demo",
							"type" => "heading",
							"nodescription"=>true);	
														
		
if(!current_theme_supports('avia_disable_dummy_import')){


$what_get 		= __("What you get:", 'avia_framework');
$online_demo 	= __("Online Demo", 'avia_framework');

$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Default Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li><a href='http://woocommerce.com/?ref=84' target='_blank'>WooCommerce</a> ".__("(for shop functionality)", 'avia_framework')."</li>"
								."<li><a href='https://wordpress.org/plugins/bbpress/' target='_blank'>BBPress</a> ".__("(for forum functionality)", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("A few", 'avia_framework')."</li>"
								."</ul>",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/default.jpg"
					);


$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Enfold 2017", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-2017/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li><a href='http://woocommerce.com/?ref=84' target='_blank'>WooCommerce</a> ".__("(for shop functionality)", 'avia_framework')."</li>"
								."<li><a href='https://wordpress.org/plugins/bbpress/' target='_blank'>BBPress</a> ".__("(for forum functionality)", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/enfold-2017",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/enfold-2017.jpg"
					);


$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Small Business - Flat Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-business-flat/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/business-flat",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/business-flat.jpg"
					);

$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Startup Business Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-startup/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/startup",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/startup.jpg"
					);


$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: One Page Portfolio Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-one-page-portfolio/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/portfolio-one-page",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/one-page-portfolio.jpg"
					);


$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Minimal Portfolio Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-minimal-portfolio/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/portfolio-minimal",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/portfolio-minimal.jpg"
					);




$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Photography Portfolio Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-photography/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li><a href='http://woocommerce.com/?ref=84' target='_blank'>WooCommerce</a> ".__("(if you want to sell photos online)", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/photography",
					"id" 	=> "import",
					"type" 	=> "import",
 					"image"	=> "includes/admin/demo_files/demo_images/photography.jpg"
					);
					
$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Minimal Photography Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-minimal-photography/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/minimal-photography",
					"id" 	=> "import",
					"type" 	=> "import",
 					"image"	=> "includes/admin/demo_files/demo_images/minimal-photography.jpg"
					);
					
					
$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Dark Photography Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-dark-photography/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/dark-photography",
					"id" 	=> "import",
					"type" 	=> "import",
 					"image"	=> "includes/admin/demo_files/demo_images/dark-photography.jpg"
					);					
					
					
					

					
$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Creative Studio Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-creative-studio/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/creative-studio",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/creative-studio.jpg"
					);	
					
					
					
					
			
					
$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Medical Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-medical/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/medical",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/medical.jpg"
					);

	

$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Shop Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-shop/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Required Plugins:", 'avia_framework')."</h4><ul>"
								."<li><a href='http://woocommerce.com/?ref=84' target='_blank'>WooCommerce</a> ".__("(needs to be active to install the demo)", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/shop",
					"id" 	=> "import",
					"type" 	=> "import",
					"exists" => array("WooCommerce",__("The WooCommerce Plugin is currently not active. Please install and activate it, then reload this page in order to be able to import this demo", 'avia_framework')),
					"image"	=> "includes/admin/demo_files/demo_images/shop.jpg"
					);






$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Restaurant Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-restaurant/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li><a href='http://woocommerce.com/?ref=84' target='_blank'>WooCommerce</a> ".__("(if you want to provide online ordering and delivery)", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/restaurant",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/restaurant.jpg"
					);

$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Restaurant One Page Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-restaurant-one-page/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li><a href='http://woocommerce.com/?ref=84' target='_blank'>WooCommerce</a> ".__("(if you want to provide online ordering and delivery)", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/restaurant-one-page",
					"id" 	=> "import",
					"type" 	=> "import",
 					"image"	=> "includes/admin/demo_files/demo_images/restaurant-onepage.jpg"
					);

$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: One Page Wedding Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-wedding/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/wedding",
					"id" 	=> "import",
					"type" 	=> "import",
 					"image"	=> "includes/admin/demo_files/demo_images/wedding.jpg"
					);		

$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Construction Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-construction/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/construction",
					"id" 	=> "import",
					"type" 	=> "import",
 					"image"	=> "includes/admin/demo_files/demo_images/construction.jpg"
					);	



$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Church Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-church/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Required Plugins:", 'avia_framework')."</h4><ul>"
								."<li><a href='https://wordpress.org/plugins/the-events-calendar/' target='_blank'>The Events Calendar</a> "
								.__("(needs to be active to install the demo)", 'avia_framework')."</li>"
								."<li>or <a href='http://mbsy.co/6cr37' target='_blank'>The Events Calendar PRO</a></li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
								
					'files' => "/includes/admin/demo_files/church",
					"id" 	=> "import",
					"type" 	=> "import",
					"exists" => array("Tribe__Events__Main",__("The Events Calendar Plugin is currently not active. Please install and activate it, then reload this page in order to be able to import this demo", 'avia_framework')),
					"image"	=> "includes/admin/demo_files/demo_images/church.jpg"
					);
					

$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Simple Blog Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-blog/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/blog",
					"id" 	=> "import",
					"type" 	=> "import",
 					"image"	=> "includes/admin/demo_files/demo_images/blog.jpg"
					);

					
		
$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Lifestyle Blog Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-lifestyle-blog/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/blog-lifestyle",
					"id" 	=> "import",
					"type" 	=> "import",
 					"image"	=> "includes/admin/demo_files/demo_images/blog-lifestyle.jpg"
					);


$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: 'Coming Soon' Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-coming-soon/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/coming_soon",
					"id" 	=> "import",
					"type" 	=> "import",
 					"image"	=> "includes/admin/demo_files/demo_images/coming-soon.jpg"
					);


$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: 'Landin Page' Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-landing-page/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/landing",
					"id" 	=> "import",
					"type" 	=> "import",
 					"image"	=> "includes/admin/demo_files/demo_images/landing.jpg"
					);


$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Travel Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-travel/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Required Plugins:", 'avia_framework')."</h4><ul>"
								."<li><a href='http://woocommerce.com/?ref=84' target='_blank'>WooCommerce</a> ".__("(needs to be active to install the demo)", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li><a href='https://woocommerce.com/products/woocommerce-bookings/?ref=84' target='_blank'>WooCommerce Bookings</a> ".__("(needs to be active to allow date based bookings)", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/travel",
					"id" 	=> "import",
					"type" 	=> "import",
					"exists" => array("WooCommerce",__("The WooCommerce Plugin is currently not active. Please install and activate it, then reload this page in order to be able to import this demo", 'avia_framework')),
					"image"	=> "includes/admin/demo_files/demo_images/travel.jpg"
					);


$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Hotel Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-hotel/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Required Plugins:", 'avia_framework')."</h4><ul>"
								."<li><a href='http://woocommerce.com/?ref=84' target='_blank'>WooCommerce</a> ".__("(needs to be active to install the demo)", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li><a href='https://woocommerce.com/products/woocommerce-bookings/?ref=84' target='_blank'>WooCommerce Bookings</a> ".__("(needs to be active to allow date based bookings)", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/hotel",
					"id" 	=> "import",
					"type" 	=> "import",
					"exists" => array("WooCommerce",__("The WooCommerce Plugin is currently not active. Please install and activate it, then reload this page in order to be able to import this demo", 'avia_framework')),
					"image"	=> "includes/admin/demo_files/demo_images/hotel.jpg"
					);


$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Spa Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-spa/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li><a href='http://woocommerce.com/?ref=84' target='_blank'>WooCommerce</a></li>"
								."<li><a href='https://woocommerce.com/products/woocommerce-bookings/?ref=84' target='_blank'>WooCommerce Bookings</a> ".__("(needs to be active to allow date based bookings)", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/spa",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/spa.jpg"
					);

$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Consulting Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-consulting/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/consulting",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/consulting.jpg"
					);
					

$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Résumé Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-resume/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/resume",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/resume.jpg"
					);


$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: GYM Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-gym/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/gym",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/gym.jpg"
					);


$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Health Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-health-coach/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/health",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/health.jpg"
					);

$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: App Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-app/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/app",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/app.jpg"
					);

$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Gaming Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-gaming/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/gaming",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/gaming.jpg"
					);
					
$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: DJ Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-dj/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/dj",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/dj.jpg"
					);			
					
$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Band Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-band/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li><a href='http://woocommerce.com/?ref=84' target='_blank'>WooCommerce</a></li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/band",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/band.jpg"
					);		


$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Freelancer Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-freelancer/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/freelancer",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/freelancer.jpg"
					);	


$avia_elements[] =	array(
					"slug"	=> "demo",
					"name" 	=> __("Import: Visual Artist Demo", 'avia_framework'),
					"desc" 	=> 	 "<p><strong>{$what_get} <a href='https://kriesi.at/themes/enfold-visual-artist/' target='_blank'>{$online_demo}</a></strong></p>"
								."<h4 class='av-before-plugins'>".__("Recommended Plugins:", 'avia_framework')."</h4><ul>"
								."<li>".__("None", 'avia_framework')."</li>"
								."</ul>"
								."<h4 class='av-before-plugins'>".__("Demo Images included:", 'avia_framework')."</h4><ul>"
								."<li>".__("All", 'avia_framework')."</li>"
								."</ul>",
					'files' => "/includes/admin/demo_files/visual-artist",
					"id" 	=> "import",
					"type" 	=> "import",
					"image"	=> "includes/admin/demo_files/demo_images/visual-artist.jpg"
					);	


	
}		
		
		
		
		
		
