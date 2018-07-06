<?php

/**
 * Creates a timeline element with milestones that consist of a date, icon or image and text
 * Can be displayed in a vertical order or as a horizontal carousel slider
 *
 * @author tinabillinger
 * @since 4.3
 */

// Don't load directly
if (!defined('ABSPATH')) {
    die('-1');
}

if (!class_exists('avia_sc_timeline')) {
    class avia_sc_timeline extends aviaShortcodeTemplate
    {
        /**
         * Create the config array for the shortcode button
         */
        function shortcode_insert_button()
        {
            $this->config['self_closing']	=	'no';

            $this->config['name'] = __('Timeline', 'avia_framework');
            $this->config['tab'] = __('Content Elements', 'avia_framework');
            $this->config['icon'] = AviaBuilder::$path['imagesURL'] . "sc-timeline.png";
            $this->config['order'] = 70;
            $this->config['target'] = 'avia-target-insert';
            $this->config['shortcode'] = 'av_timeline';
            $this->config['shortcode_nested'] = array('av_timeline_item');
            $this->config['tooltip'] = __('Creates a timeline', 'avia_framework');
            $this->config['preview'] = "large";
            $this->config['disabling_allowed'] = true;

        }

        function extra_assets()
        {
            wp_enqueue_style( 'avia-module-slideshow' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/slideshow/slideshow.css' , array('avia-layout'), false );
            wp_enqueue_style( 'avia-module-timeline' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/timeline/timeline.css' , array('avia-layout'), false );

            wp_enqueue_script( 'avia-module-slideshow' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/slideshow/slideshow.js' , array('avia-shortcodes'), false, TRUE );
            wp_enqueue_script( 'avia-module-timeline' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/timeline/timeline.js' , array('avia-shortcodes','avia-module-slideshow'), false , TRUE);

        }


        /**
         * Popup Elements
         *
         * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
         * opens a modal window that allows to edit the element properties
         *
         * @return void
         */
        function popup_elements()
        {
            $this->elements = array(
                array(
                    "type" => "tab_container", 'nodescription' => true
                ),

                array(
                    "type" => "tab",
                    "name" => __("Content", 'avia_framework'),
                    'nodescription' => true
                ),

                array(
                    "name" => __("Add/Edit Milestones", 'avia_framework'),
                    "desc" => __("Here you can add, remove and edit the milestones of your timeline.", 'avia_framework'),
                    "type" => "modal_group",
                    "id" => "content",
                    "modal_title" => __("Edit Milestone", 'avia_framework'),
                    "std" =>
                        array(
                            array('date' => __('2016'), 'title' => __('Milestone 1', 'avia_framework'), 'icon' => '4', 'content' => 'Enter content here'),
                            array('date' => __('2017'), 'title' => __('Milestone 2', 'avia_framework'), 'icon' => '47', 'content' => 'Enter content here'),
                            array('date' => __('2018'), 'title' => __('Milestone 3', 'avia_framework'), 'icon' => '62', 'content' => 'Enter content here'),
                        ),

                    'subelements' => array(
                        array(
                            "type" => "tab_container", 'nodescription' => true
                        ),

                        array(
                            "type" => "tab",
                            "name" => __("Content", 'avia_framework'),
                            'nodescription' => true
                        ),

                        array(
                            "name" => __("Milestone Date", 'avia_framework'),
                            "desc" => __("Enter the milestone date here (This can be any format)", 'avia_framework'),
                            "id" => "date",
                            "std" => '2018',
                            "type" => "input"
                        ),
                        array(
                            "name" => __("Milestone Title", 'avia_framework'),
                            "desc" => __("Enter the Milestone Title here", 'avia_framework'),
                            "id" => "title",
                            "std" => "Milestone Title",
                            "type" => "input"
                        ),
                        array(
                            "name" => __("Milstone Link?", 'avia_framework'),
                            "desc" => __("Do you want to apply  a link to the milestone?", 'avia_framework'),
                            "id" => "link",
                            "type" => "linkpicker",
                            "fetchTMPL" => true,
                            "std" => "",
                            "subtype" => array(
                                __('No Link', 'avia_framework') => '',
                                __('Set Manually', 'avia_framework') => 'manually',
                                __('Single Entry', 'avia_framework') => 'single',
                                __('Taxonomy Overview Page', 'avia_framework') => 'taxonomy',
                            ),
                            "std" => ""),

                        array(
                            "name" => __("Open in new window", 'avia_framework'),
                            "desc" => __("Do you want to open the link in a new window", 'avia_framework'),
                            "id" => "linktarget",
                            "required" => array('link', 'not', ''),
                            "type" => "select",
                            "std" => "no",
                            "subtype" => AviaHtmlHelper::linking_options()),

                        array(
                            "name" => __("Apply link", 'avia_framework'),
                            "desc" => __("Where do you want to apply the link?", 'avia_framework'),
                            "id" => "linkelement",
                            "required" => array('link', 'not', ''),
                            "type" => "select",
                            "std" => "",
                            "subtype" => array(
                                __('Apply link to the date, icon and headline', 'avia_framework') => 'all',
                                __('Apply link to the icon and date', 'avia_framework') => 'both',
                                __('Apply link to icon and headline', 'avia_framework') => 'icon_head',
                                __('Apply link to date and headline', 'avia_framework') => 'date_head',
                                __('Apply link to icon only', 'avia_framework') => 'icon_only'
                            )
                        ),
                        

                        array(
                            "name" => __("Vertical Alignment Title and Content", 'avia_framework'),
                            "desc" => __("Applies only for the vertical timeline.", 'avia_framework'),
                            "id" => "milestone_valign",
                            "type" => "select",
                            "std" => "baseline",
                            "subtype" => array(
                                __('Baseline (Default)', 'avia_framework') => 'baseline',
                                __('Center', 'avia_framework') => 'middle',
                            ),
                        ),

                        array(
                            "name" => __("Milestone Content", 'avia_framework'),
                            "desc" 	=> __("Enter some content here", 'avia_framework' ) ,
                            "id" => "content",
                            "type" => "tiny_mce",
                            "std" 	=> __("Milestone Content goes here", 'avia_framework'),
                        ),
                        array(
                            "type" => "close_div",
                            'nodescription' => true
                        ),
                        

                        array(
                            "type" => "tab",
                            "name" => __("Colors", 'avia_framework'),
                            'nodescription' => true
                        ),
                        
                        array(
                            "name" => __("Milestone Colors", 'avia_framework'),
                            "desc" => __("Either use the themes default colors or apply some custom ones", 'avia_framework'),
                            "id" => "milestone_color",
                            "type" => "select",
                            "std" => "",
                            "subtype" => array(__('Default', 'avia_framework') => '',
                                __('Define Custom Colors', 'avia_framework') => 'custom'),
                        ),
                        array(
                            "name" => __("Custom Milestone Icon Background Color", 'avia_framework'),
                            "desc" => __("Select a custom background color for the icon. Leave empty to use the default", 'avia_framework'),
                            "id" => "custom_milestone_color",
                            "type" => "colorpicker",
                            "std" => "",
                            "container_class" => 'av_half av_half_first',
                            "required" => array('milestone_color', 'equals', 'custom')
                        ),
                        
                         array(
                            "type" => "close_div",
                            'nodescription' => true
                        ),
                        

                        array(
                            "type" => "tab",
                            "name" => __("Icon/Image", 'avia_framework'),
                            'nodescription' => true
                        ),
                        

                        array(
                            "name" => __("Icon or Image", 'avia_framework'),
                            "desc" => __("Select an icon or an image for your milestone", 'avia_framework'),
                            "id" => "icon_image",
                            "type" => "select",
                            "std" => "icon",
                            "subtype" => array(
                                __('Add Icon', 'avia_framework') => 'icon',
                                __('Add Image', 'avia_framework') => 'image',
                            )
                        ),


                        array(
                            "name" => __("Milestone Icon", 'avia_framework'),
                            "desc" => __("Select an icon for your milestone below", 'avia_framework'),
                            "required" => array('icon_image', 'equals', 'icon'),
                            "id" => "icon",
                            "type" => "iconfont",
                            "std" => "",
                        ),

                        array(
                            "name" => __("Choose Image", 'avia_framework'),
                            "desc" => __("Either upload a new, or choose an existing image from your media library", 'avia_framework'),
                            "id" => "image",
                            "type" => "image",
                            "title" => __("Insert Image", 'avia_framework'),
                            "required" => array('icon_image', 'equals', 'image'),
                            "button" => __("Insert", 'avia_framework'),
                            "std" => AviaBuilder::$path['imagesURL'] . "placeholder.jpg"
                        ),
                        array(
                            "type" => "close_div",
                            'nodescription' => true
                        ),

                    )
                ),

                array(
                    "name" => __("Timeline Orientation", 'avia_framework'),
                    "desc" => __("Set the orientation of the timeline", 'avia_framework'),
                    "id" => "orientation",
                    "type" => "select",
                    "std" => "vertical",
                    "subtype" =>
                        array(
                            __('Vertical', 'avia_framework') => 'vertical',
                            __('Horizontal', 'avia_framework') => 'horizontal',
                        )
                ),
                array(
                    "name" => __("Milestone Placement", 'avia_framework'),
                    "desc" => __("Set the placement of the milestones on the timeline", 'avia_framework'),
                    "id" => "placement_v",
                    "type" => "select",
                    "std" => "alternate",
                    "required" => array('orientation', 'equals', 'vertical'),
                    "subtype" =>
                        array(
                            __('Alternate', 'avia_framework') => 'alternate',
                            __('Left', 'avia_framework') => 'left',
                            __('Right', 'avia_framework') => 'right',
                        )
                ),
                array(
                    "name" => __("Milestone Placement", 'avia_framework'),
                    "desc" => __("Set the placement of the milestones on the timeline", 'avia_framework'),
                    "id" => "placement_h",
                    "type" => "select",
                    "std" => "top",
                    "required" => array('orientation', 'equals', 'horizontal'),
                    "subtype" =>
                        array(
                            __('Alternate', 'avia_framework') => 'alternate',
                            __('Top', 'avia_framework') => 'top',
                            __('Bottom', 'avia_framework') => 'bottom',
                        )
                ),
                array(
                    "name" => __("Number of Milestones per Slide", 'avia_framework'),
                    "desc" => __("Set the number of the milestones visible at a time", 'avia_framework'),
                    "id" => "slides_num",
                    "type" => "select",
                    "std" => "3",
                    "required" => array('orientation', 'equals', 'horizontal'),
                    "subtype" =>
                        array(
                            __('1 Milestone', 'avia_framework') => '1',
                            __('2 Milestones', 'avia_framework') => '2',
                            __('3 Milestones', 'avia_framework') => '3',
                            __('4 Milestones', 'avia_framework') => '4',
                            __('5 Milestones', 'avia_framework') => '5',
                        )
                ),
                array(
                    "name" => __("Content Appearence", 'avia_framework'),
                    "desc" => __("Define the appearence of the content box", 'avia_framework'),
                    "id" => "content_appearence",
                    "type" => "select",
                    "std" => "",
                    "subtype" =>
                        array(
                            __('Plain (default)', 'avia_framework') => '',
                            __('Box Shadow with Arrow', 'avia_framework') => 'boxshadow',
                        )
                ),

                array(
                    "name" => __("Date Font Size", 'avia_framework'),
                    "desc" => __("Select a custom font size. Leave empty to use the default", 'avia_framework'),
                    "id" => "custom_date_size",
                    "type" => "select",
                    "std" => "",
                    "container_class" => 'av_half',
                    "subtype" => AviaHtmlHelper::number_array(10, 50, 1, array(__("Default Size", 'avia_framework') => ''), 'px'),
                ),
                array(
                    "name" => __("Title Font Size", 'avia_framework'),
                    "desc" => __("Select a custom font size. Leave empty to use the default", 'avia_framework'),
                    "id" => "custom_title_size",
                    "type" => "select",
                    "std" => "",
                    "container_class" => 'av_half',
                    "subtype" => AviaHtmlHelper::number_array(10, 50, 1, array(__("Default Size", 'avia_framework') => ''), 'px'),
                ),

                array(
                    "name" => __("Content Font Size", 'avia_framework'),
                    "desc" => __("Select a custom font size. Leave empty to use the default", 'avia_framework'),
                    "id" => "custom_content_size",
                    "type" => "select",
                    "std" => "",
                    "container_class" => 'av_half av_no_bottom',
                    "subtype" => AviaHtmlHelper::number_array(10, 50, 1, array(__("Default Size", 'avia_framework') => ''), 'px'),
                ),

                array(
                    "type" => "close_div",
                    'nodescription' => true
                ),

                array(
                    "type" => "tab",
                    "name" => __("Colors", 'avia_framework'),
                    'nodescription' => true
                ),

                array(
                    "name" => __("Content Box Background Color", 'avia_framework'),
                    "desc" => __("Either use the themes default or define a custom background color", 'avia_framework'),
                    "id" => "contentbox_bg_color",
                    "type" => "select",
                    "std" => "",
                    "subtype" => array(__('Default', 'avia_framework') => '',
                        __('Define Custom Colors', 'avia_framework') => 'custom'),
                ),

                array(
                    "name" => __("Custom Content Box Background Color", 'avia_framework'),
                    "desc" => __("Select a custom background color. Leave empty to use the default", 'avia_framework'),
                    "id" => "custom_contentbox_bg_color",
                    "type" => "colorpicker",
                    "std" => "",
                    "required" => array('contentbox_bg_color', 'equals', 'custom')
                ),


                array(
                    "name" => __("Font Colors", 'avia_framework'),
                    "desc" => __("Either use the themes default colors or apply some custom ones", 'avia_framework'),
                    "id" => "font_color",
                    "type" => "select",
                    "std" => "",
                    "subtype" => array(__('Default', 'avia_framework') => '',
                        __('Define Custom Colors', 'avia_framework') => 'custom'),
                ),

                array(
                    "name" => __("Custom Date Font Color", 'avia_framework'),
                    "desc" => __("Select a custom font color. Leave empty to use the default", 'avia_framework'),
                    "id" => "custom_date",
                    "type" => "colorpicker",
                    "std" => "",
                    "container_class" => 'av_third av_third_first',
                    "required" => array('font_color', 'equals', 'custom')
                ),
                array(
                    "name" => __("Custom Title Font Color", 'avia_framework'),
                    "desc" => __("Select a custom font color. Leave empty to use the default", 'avia_framework'),
                    "id" => "custom_title",
                    "type" => "colorpicker",
                    "std" => "",
                    "container_class" => 'av_third',
                    "required" => array('font_color', 'equals', 'custom')
                ),

                array(
                    "name" => __("Custom Content Font Color", 'avia_framework'),
                    "desc" => __("Select a custom font color. Leave empty to use the default", 'avia_framework'),
                    "id" => "custom_content",
                    "type" => "colorpicker",
                    "std" => "",
                    "container_class" => 'av_third',
                    "required" => array('font_color', 'equals', 'custom')
                ),

                array(
                    "name" => __("Icon Colors", 'avia_framework'),
                    "desc" => __("Either use the themes default colors or apply some custom ones", 'avia_framework'),
                    "id" => "icon_color",
                    "type" => "select",
                    "std" => "",
                    "subtype" => array(__('Default', 'avia_framework') => '',
                        __('Define Custom Colors', 'avia_framework') => 'custom'),

                ),

                array(
                    "name" => __("Custom Background Color", 'avia_framework'),
                    "desc" => __("Select a custom background color. Leave empty to use the default", 'avia_framework'),
                    "id" => "icon_custom_bg",
                    "type" => "colorpicker",
                    "std" => "",
                    "container_class" => 'av_third av_third_first',
                    "required" => array('icon_color', 'equals', 'custom')
                ),

                array(
                    "name" => __("Custom Font Color", 'avia_framework'),
                    "desc" => __("Select a custom font color. Leave empty to use the default or the accent color defined in individual Milestones.", 'avia_framework'),
                    "id" => "icon_custom_font",
                    "type" => "colorpicker",
                    "std" => "",
                    "container_class" => 'av_third',
                    "required" => array('icon_color', 'equals', 'custom')
                ),

                array(
                    "name" => __("Custom Border Color", 'avia_framework'),
                    "desc" => __("Select a custom border color. Leave empty to use the default", 'avia_framework'),
                    "id" => "icon_custom_border",
                    "type" => "colorpicker",
                    "std" => "",
                    "container_class" => 'av_third',
                    "required" => array('icon_color', 'equals', 'custom')
                ),

                array(
                    "type" => "close_div",
                    'nodescription' => true
                ),


                array(
                    "type" => "tab",
                    "name" => __("Screen Options", 'avia_framework'),
                    'nodescription' => true
                ),

                array(
                    "name" => __("Element Visibility", 'avia_framework'),
                    "desc" => __("Set the visibility for this element, based on the device screensize.", 'avia_framework'),
                    "type" => "heading",
                    "description_class" => "av-builder-note av-neutral",
                ),

                array(
                    "desc" => __("Hide on large screens (wider than 990px - eg: Desktop)", 'avia_framework'),
                    "id" => "av-desktop-hide",
                    "std" => "",
                    "container_class" => 'av-multi-checkbox',
                    "type" => "checkbox"),

                array(

                    "desc" => __("Hide on medium sized screens (between 768px and 989px - eg: Tablet Landscape)", 'avia_framework'),
                    "id" => "av-medium-hide",
                    "std" => "",
                    "container_class" => 'av-multi-checkbox',
                    "type" => "checkbox"),

                array(

                    "desc" => __("Hide on small screens (between 480px and 767px - eg: Tablet Portrait)", 'avia_framework'),
                    "id" => "av-small-hide",
                    "std" => "",
                    "container_class" => 'av-multi-checkbox',
                    "type" => "checkbox"),

                array(

                    "desc" => __("Hide on very small screens (smaller than 479px - eg: Smartphone Portrait)", 'avia_framework'),
                    "id" => "av-mini-hide",
                    "std" => "",
                    "container_class" => 'av-multi-checkbox',
                    "type" => "checkbox"
                ),

                array(
                    "name" => __("Date Font Size", 'avia_framework'),
                    "desc" => __("Set the font size for the date, based on the device screensize.", 'avia_framework'),
                    "type" => "heading",
                    "description_class" => "av-builder-note av-neutral",
                ),

                array(
                    "name" => __("Font Size for medium sized screens", 'avia_framework'),
                    "id" => "av-medium-font-size-title",
                    "type" => "select",
                    "subtype" => AviaHtmlHelper::number_array(10, 120, 1, array(__("Default", 'avia_framework') => '', __("Hidden", 'avia_framework') => 'hidden'), "px"),
                    "std" => ""
                ),

                array(
                    "name" => __("Font Size for small screens", 'avia_framework'),
                    "id" => "av-small-font-size-title",
                    "type" => "select",
                    "subtype" => AviaHtmlHelper::number_array(10, 120, 1, array(__("Default", 'avia_framework') => '', __("Hidden", 'avia_framework') => 'hidden'), "px"),
                    "std" => ""
                ),

                array(
                    "name" => __("Font Size for very small screens", 'avia_framework'),
                    "id" => "av-mini-font-size-title",
                    "type" => "select",
                    "subtype" => AviaHtmlHelper::number_array(10, 120, 1, array(__("Default", 'avia_framework') => '', __("Hidden", 'avia_framework') => 'hidden'), "px"),
                    "std" => ""
                ),

                array(
                    "name" => __("Title Font Size", 'avia_framework'),
                    "desc" => __("Set the font size for the title, based on the device screensize.", 'avia_framework'),
                    "type" => "heading",
                    "description_class" => "av-builder-note av-neutral",
                ),

                array(
                    "name" => __("Font Size for medium sized screens", 'avia_framework'),
                    "id" => "av-medium-font-size",
                    "type" => "select",
                    "subtype" => AviaHtmlHelper::number_array(10, 120, 1, array(__("Default", 'avia_framework') => '', __("Hidden", 'avia_framework') => 'hidden'), "px"),
                    "std" => ""
                ),

                array(
                    "name" => __("Font Size for small screens", 'avia_framework'),
                    "id" => "av-small-font-size",
                    "type" => "select",
                    "subtype" => AviaHtmlHelper::number_array(10, 120, 1, array(__("Default", 'avia_framework') => '', __("Hidden", 'avia_framework') => 'hidden'), "px"),
                    "std" => ""
                ),

                array(
                    "name" => __("Font Size for very small screens", 'avia_framework'),
                    "id" => "av-mini-font-size",
                    "type" => "select",
                    "subtype" => AviaHtmlHelper::number_array(10, 120, 1, array(__("Default", 'avia_framework') => '', __("Hidden", 'avia_framework') => 'hidden'), "px"),
                    "std" => ""
                ),
                array(
                    "type" => "close_div",
                    'nodescription' => true
                ),
                array(
                    "type" => "close_div",
                    'nodescription' => true
                ),

            );

        }


        /**
         * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
         * Works in the same way as Editor Element
         * @param array $params this array holds the default values for $content and $args.
         * @return $params the return array usually holds an innerHtml key that holds item specific markup.
         */
        function editor_sub_element($params)
        {
            $template  = $this->update_template("date", "{{date}}");
            $template2 = $this->update_template("title",": {{title}}");

            extract(av_backend_icon($params)); // creates $font and $display_char if the icon was passed as param "icon" and the font as "font"

            $params['innerHtml'] = "";
            $params['innerHtml'] .= "<div class='avia_title_container'>";
            $params['innerHtml'] .= "<span " . $this->class_by_arguments('font', $font) . ">";
            $params['innerHtml'] .= "<span data-update_with='icon_fakeArg' class='avia_tab_icon'>" . $display_char . "</span>";
            $params['innerHtml'] .= "</span>";
            $params['innerHtml'] .= "<span {$template}> " . $params['args']['date'] . "</span>" . "<span {$template2}>: ". $params['args']['title'] . "</span></div>";

            return $params;

        }

        /**
         * Frontend Shortcode Handler
         *
         * @param array $atts array of attributes
         * @param string $content text within enclosing form of shortcode element
         * @param string $shortcodename the shortcode found, when == callback name
         * @return string $output returns the modified html string
         */
        function shortcode_handler($atts, $content = "", $shortcodename = "", $meta = "")
        {

            $this->screen_options = AviaHelper::av_mobile_sizes($atts);
            extract($this->screen_options); //return $av_font_classes, $av_title_font_classes and $av_display_classes

            extract(shortcode_atts(
                array(
                    'orientation' => 'vertical',
                    'placement_v' => '',
                    'placement_h' => '',
                    'slides_num' => '',
                    'content_appearence' => '',

                    'custom_date_size' => '',
                    'custom_title_size' => '',
                    'custom_content_size' => '',

                    'font_color' => "",
                    'icon_color' => "",

                    'custom_date' => '',
                    'custom_title' => '',
                    'custom_content' => '',

                    'icon_custom_bg' => '',
                    'icon_custom_font' => '',
                    'icon_custom_border' => '',

                    'contentbox_bg_color' => '',
                    'custom_contentbox_bg_color' => ''

                ), $atts, $this->config['shortcode']));

            $this->orientation = $orientation;
            $this->placement_v = $placement_v;
            $this->placement_h = $placement_h;
            $this->slides_num = $slides_num;
            $this->appearence = $content_appearence;

            $this->date_styling = array();
            $this->date_styling_string = "";

            $this->title_styling = array();
            $this->title_styling_string = "";

            $this->content_styling = array();
            $this->content_styling_string = "";

            $this->icon_styling = array();
            $this->icon_styling_string = "";

            $this->article_styling = array();

            $this->milestone_indicator_styling = array();

            $this->contentbox_styling = array();
            $this->contentbox_styling_string = "";

            $this->placement = $this->orientation == 'vertical' ? 'av-milestone-placement-' . $placement_v : 'av-milestone-placement-' . $placement_h;

            /* custom font sizes */
            if ($custom_date_size) $this->date_styling['font-size'] = $custom_date_size;
            if ($custom_title_size) $this->title_styling['font-size'] = $custom_title_size;
            if ($custom_content_size) $this->content_styling['font-size'] = $custom_content_size;


            /* custom font colors */
            if ($font_color == 'custom') {
                if (!empty($custom_date)) $this->date_styling['color'] = $custom_date;
                if (!empty($custom_title)) $this->title_styling['color'] = $custom_title;
                if (!empty($custom_content)) $this->content_styling['color'] = $custom_content;
            }

            /* custom icon colors */
            if ($icon_color == 'custom') {

                if (!empty($icon_custom_bg)) {
                    $this->article_styling['background-color'] = $icon_custom_bg;
                    $this->milestone_indicator_styling['background-color'] = $icon_custom_bg;
                    $this->icon_styling['background-color'] = $icon_custom_bg;
                }

                if (!empty($icon_custom_font)) $this->icon_styling['color'] = $icon_custom_font;
                if (!empty($icon_custom_border)) $this->icon_styling['border-color'] = $icon_custom_border;

            }

            /* custom content box styling */

            if ($contentbox_bg_color == 'custom') {
                if ($custom_contentbox_bg_color) $this->contentbox_styling['background-color'] = $custom_contentbox_bg_color;
            }


            if (!empty($this->date_styling)) {
                if (array_key_exists('font-size',$this->date_styling)) {
                    $this->date_styling_string .= AviaHelper::style_string($this->date_styling, 'font-size', 'font-size', 'px');
                }
                if (array_key_exists('color',$this->date_styling)) {
                    $this->date_styling_string .= AviaHelper::style_string($this->date_styling, 'color', 'color');
                }
            }

            if (!empty($this->title_styling)) {
                if (array_key_exists('font-size',$this->title_styling)) {
                    $this->title_styling_string .= AviaHelper::style_string($this->title_styling, 'font-size', 'font-size', 'px');
                }
                if (array_key_exists('color',$this->title_styling)) {
                    $this->title_styling_string .= AviaHelper::style_string($this->title_styling, 'color', 'color');
                }
            }

            if (!empty($this->content_styling)){
                if (array_key_exists('font-size',$this->content_styling)){
                    $this->content_styling_string .= AviaHelper::style_string($this->content_styling,'font-size','font-size','px');
                }
                if (array_key_exists('color',$this->content_styling)){
                    $this->content_styling_string .= AviaHelper::style_string($this->content_styling,'color','color');
                }
            }

            if ($this->appearence !== '' && !empty($this->contentbox_styling)) {
                if (array_key_exists('background-color',$this->contentbox_styling)){
                    $this->contentbox_styling_string .= AviaHelper::style_string($this->contentbox_styling,'background-color','background-color');
                }
            }

            $this->i = 0;

            $slider_attribute = "";
            $slider_container_class = "";


            if ($this->orientation == 'horizontal') {
                $slider_attribute = "avia-data-slides='{$this->slides_num}'";
                $slider_container_class = "avia-slideshow-carousel";
            }

            $output = "";
            $output .= "<div id='avia-timeline-" . uniqid() . "' class='avia-timeline-container {$slider_container_class} {$av_display_classes} ".$meta['el_class']."' {$slider_attribute}>";
            $output .= "<ul class='avia-timeline avia-timeline-{$orientation} {$this->placement} avia-timeline-{$this->appearence} avia_animate_when_almost_visible'>";
            $output .= ShortcodeHelper::avia_remove_autop($content, true);
            $output .= "</ul>";


            if ($this->orientation == 'horizontal') {
                $output .= "<div class='av-timeline-nav {$av_display_classes}'>";
                $output .= "<a href='#prev' class='prev-slide av-timeline-nav-prev'><span " . av_icon_string('prev_big') . ">" . __('Previous', 'avia_framework') . "</span></a>";
                $output .= "<a href='#next' class='next-slide av-timeline-nav-next'><span " . av_icon_string('next_big') . ">" . __('Next', 'avia_framework') . "</span></a>";
                $output .= '</div>';
            }


            $output .= "</div>";

            return $output;

        }

        function av_timeline_item($atts, $content = "", $shortcodename = "")
        {

            extract($this->screen_options); //return $av_font_classes, $av_title_font_classes and $av_display_classes

            $atts = shortcode_atts(
                array(
                    'date' => '',
                    'title' => '',
                    'link' => '',
                    'icon_image' => '',
                    'image' => '',
                    'attachment' => '',
                    'attachment_size' => '',
                    'icon' => '',
                    'linkelement' => '',
                    'linktarget' => '',
                    'font' => '',
                    'milestone_color' => '',
                    'custom_milestone_color' => '',
                    'milestone_valign' => ''
                ),
                $atts, 'av_timeline_item');

            $display_char = av_icon($atts['icon'], $atts['font']);

            $date_styling = ($this->date_styling_string !== "") ? AviaHelper::style_string($this->date_styling_string) : "";
            $title_styling = ($this->title_styling_string !== "") ? AviaHelper::style_string($this->title_styling_string) : "";
            $content_styling = ($this->content_styling_string !== "") ? AviaHelper::style_string($this->content_styling_string) : "";
            $contentbox_styling = ($this->contentbox_styling_string !== "") ? AviaHelper::style_string($this->contentbox_styling_string) : "";

            $article_styling_arr = array();
            $article_styling_string = "";
            $article_styling = "";
            if (!empty($this->article_styling)) $article_styling_arr = array_merge($article_styling_arr, $this->article_styling);

            $milestone_indicator_styling_arr = array();
            $milestone_indicator_styling_string = "";
            $milestone_indicator_styling = "";
            if (!empty($this->milestone_indicator_styling)) $milestone_indicator_styling_arr = array_merge($milestone_indicator_styling_arr, $this->milestone_indicator_styling);

            $icon_styling_arr = array();
            $icon_styling_inner_string = "";
            $icon_styling_string = "";
            $icon_styling_inner = "";
            $icon_styling = "";
            $icon_extra_class = "";

            /* icon,image */
            $image_src = "";
            if ($atts['icon_image'] == 'image')
			{
                if( ! empty( $atts['attachment'] ) ) 
				{
					/**
					 * Allows e.g. WPML to reroute to translated image
					 */
					$posts = get_posts( array(
											'include'			=> $atts['attachment'],
											'post_status'		=> 'inherit',
											'post_type'			=> 'attachment',
											'post_mime_type'	=> 'image',
											'order'				=> 'ASC',
											'orderby'			=> 'post__in' )
										);

                    if( is_array( $posts ) && ! empty( $posts ) ) 
					{
						$attachment_entry = $posts[0];
						
                        $image_alt = get_post_meta($attachment_entry->ID, '_wp_attachment_image_alt', true);
                        $image_alt = !empty($alt) ? esc_attr($alt) : '';
                        $image_title = trim($attachment_entry->post_title) ? esc_attr($attachment_entry->post_title) : "";
                        if (!empty($atts['attachment_size'])) {
                            $image_src = wp_get_attachment_image_src($attachment_entry->ID, $atts['attachment_size']);
                            $image_src = !empty($image_src[0]) ? $image_src[0] : "";
                            $icon_styling_arr['background-image'] = $image_src;
                            $icon_extra_class = ' milestone-icon-hasborder';
                        }
                    }
                }
            }


            // assemble style strings from arrays
            if (!empty($this->icon_styling)) $icon_styling_arr = array_merge($icon_styling_arr, $this->icon_styling);

            if ($atts['milestone_color'] == 'custom') {
                $article_styling_arr['background-color'] = $atts['custom_milestone_color'];
                $milestone_indicator_styling_arr['background-color'] = $atts['custom_milestone_color'];
                $icon_styling_arr['background-color'] = $atts['custom_milestone_color'];
                if ($atts['icon_image'] == 'image') $icon_styling_arr['border-color'] = $atts['custom_milestone_color'];
            }

            if (!empty($article_styling_arr)){
                if(array_key_exists('background-color',$article_styling_arr)){
                    $article_styling_string = AviaHelper::style_string($article_styling_arr,'background-color','background-color');
                }
            }

            $article_styling = ($article_styling_string !== "") ? AviaHelper::style_string($article_styling_string) : "";

            if (!empty($milestone_indicator_styling_arr)){
                if(array_key_exists('background-color',$milestone_indicator_styling_arr)){
                    $milestone_indicator_styling_string = AviaHelper::style_string($milestone_indicator_styling_arr,'background-color','background-color');
                }
            }
            $milestone_indicator_styling = ($milestone_indicator_styling_string !== "") ? AviaHelper::style_string($milestone_indicator_styling_string) : "";

            if (!empty($icon_styling_arr)){
                if (array_key_exists('background-color',$icon_styling_arr)){
                    $icon_styling_inner_string .= AviaHelper::style_string($icon_styling_arr,'background-color','background-color');
                }
                if (array_key_exists('color',$icon_styling_arr)){
                    $icon_styling_inner_string .= AviaHelper::style_string($icon_styling_arr,'color','color');
                }
                if (array_key_exists('border-color',$icon_styling_arr)){
                    $icon_styling_string .= AviaHelper::style_string($icon_styling_arr,'border-color','background-color');
                    $icon_extra_class = ' milestone-icon-hasborder';
                }
                if (array_key_exists('background-image',$icon_styling_arr)) {
                    $icon_styling_inner_string .= AviaHelper::style_string($icon_styling_arr,'background-image','background-image');
                }
            }

            $icon_styling = ($icon_styling_string !== "") ? AviaHelper::style_string($icon_styling_string) : "";
            $icon_styling_inner = ($icon_styling_inner_string !== "") ? AviaHelper::style_string($icon_styling_inner_string) : "";

            $list_class = $this->i % 2 == 0 ? "av-milestone-odd" : "av-milestone-even";

            $linktitle = "";
            $linktarget = "";
            $link = "";

            if (!empty($atts['link'])) {
                $atts['link'] = aviaHelper::get_url($atts['link']);
                $linktitle = $atts['title'];
                $linktarget = (strpos($atts['linktarget'], '_blank') !== false || $atts['linktarget'] == 'yes') ? ' target="_blank" ' : "";
                $linktarget .= strpos($atts['linktarget'], 'nofollow') !== false ? ' rel="nofollow" ' : "";
            }

            $vertical_alignment = "";
            if ($this->orientation == 'vertical') {
                $vertical_alignment = $atts['milestone_valign'] ? ' av-milestone-valign-' .$atts['milestone_valign'] : "";
            }


            $output = "";
            $output .= "<li class='av-milestone av-animated-generic fade-in {$list_class} {$vertical_alignment}'>";


            $icon_wrapper = array(
                'start' => '<div class="av-milestone-icon-wrap">',
                'end' => '</div>',
            );

            if (in_array($atts['linkelement'], array('all', 'both', 'icon_head', 'icon_only')) &&  !empty($atts['link']) ) {
                $icon_wrapper['start'] = "<a class='av-milestone-icon-wrap' title='" . esc_attr($linktitle) . "' href='{$atts['link']}'>";
                $icon_wrapper['end'] = "</a>";
            }

            $icon = $icon_wrapper['start'];

            if ($atts['icon_image'] == 'image') {
                $icon .= "<span class='milestone_icon{$icon_extra_class}' {$icon_styling}>";
                $icon .= "<span class='milestone_inner' {$icon_styling_inner}>&nbsp;</span>";
                $icon .= '</span>';

            } else {
                $icon .= "<span class='milestone_icon{$icon_extra_class} avia-font-" . $atts['font'] . "' {$icon_styling}>";
                $icon .= "<span class='milestone_inner' {$icon_styling_inner}><i class='milestone-char' {$display_char}></i></span>";
                $icon .= "</span>";
            }
            $icon .= $icon_wrapper['end'];


            /* date */
            $title_sanitized = sanitize_title($atts['date']);
            $date_wrapper = array(
                'start' => "<h2 class='av-milestone-date{$av_title_font_classes}' {$date_styling} id='milestone-{$title_sanitized}'><strong>",
                'end' => "</strong></h2>",
            );

            if (in_array($atts['linkelement'], array('all', 'both', 'date_head')) &&  !empty($atts['link']) ) {
                $date_wrapper['start'] = "<h2 class='av-milestone-date{$av_title_font_classes}' {$date_styling} id='milestone-{$title_sanitized}'><a title='" . esc_attr($linktitle) . "' href='{$atts['link']}'>";
                $date_wrapper['end'] = "</a></h2>";
            }

            $date = "";
            $date .= $date_wrapper['start'];
            $date .= $atts['date'];
            $date .= "<span class='av-milestone-indicator'{$milestone_indicator_styling}></span>";
            $date .= $date_wrapper['end'];

            /* article */
            $article = "";
            $article .= "<article class='av-milestone-content-wrap'>";
            $article .= "<div class='av-milestone-contentbox' {$contentbox_styling}>";
            if (!empty($atts['title'])) {
                $title_class = $av_font_classes ? " class='{$av_font_classes}'" : "";

                $headline_wrap = array(
                    'start' => "<h4 {$title_styling}{$title_class}>",
                    'end' => "</h4>"
                );

                if (in_array($atts['linkelement'], array('all', 'icon_head', 'date_head')) &&  !empty($atts['link']) ) {
                    $headline_wrap['start'] = "<h4 {$title_styling}{$title_class}><a title='" . esc_attr($linktitle) . "' href='{$atts['link']}'>";
                    $headline_wrap['end'] = "</a></h4>";
                }

                $article .= '<header class="entry-content-header">';
                $article .= $headline_wrap['start'];
                $article .= $atts['title'];
                $article .= $headline_wrap['end'];
                $article .= '</header>';
            }
            $article .= "<div class='av-milestone-content{$av_font_classes}' {$content_styling}>";
            $article .= ShortcodeHelper::avia_apply_autop(ShortcodeHelper::avia_remove_autop($content));
            $article .= "</div>";
            $article .= "</div>";
            $article .= "<footer {$article_styling} class='entry-footer'></footer>";
            $article .= "</article>";

            switch ($this->placement) {
                case 'av-milestone-placement-left':
                    $output .= $date;
                    $output .= $icon;
                    $output .= $article;
                    break;

                case 'av-milestone-placement-right':
                    $output .= $date;
                    $output .= $article;
                    $output .= $icon;
                    $output .= $date;
                    break;

                case 'av-milestone-placement-alternate':
                    if ($this->i % 2 == 0) {
                        $output .= $date;
                        $output .= $icon;
                        $output .= $article;
                    } else {

                        if ($this->orientation == 'vertical') {
                            $output .= $date;
                        }

                        $output .= $article;
                        $output .= $icon;
                        $output .= $date;
                    }
                    break;

                case 'av-milestone-placement-top':
                    $output .= $date;
                    $output .= $icon;
                    $output .= $article;
                    break;

                case 'av-milestone-placement-bottom':
                    $output .= $article;
                    $output .= $icon;
                    $output .= $date;
                    break;

            }

            $output .= "</li>";
            $this->i++;

            return $output;

        }

    }
}
