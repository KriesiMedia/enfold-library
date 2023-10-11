<?php

use Yoast\WP\SEO\Generators\Schema\Abstract_Schema_Piece;

/**
 * Class AviaYoastSchemaPiece.
 */
abstract class AviaYoastSchemaPiece extends Abstract_Schema_Piece
{
    /**
     * The current shortcode tag.
     *
     */
    public $shortcode_tag;

    /**
     * The child shortcode tag.
     *
     */
    public $shortcode_child_tag;

    /**
     * The current post.
     *
     */
    public $current_post;

    /**
     * The advance layout builder instance.
     *
     */
    public $avia_builder;

    /**
     * The builder content.
     *
     */
    public $avia_builder_content;

    public function __construct()
    {
        global $post;

        $this->current_post = $post;
        $this->shortcode_tag = null;
        $this->shortcode_child_tag = null;
        $this->avia_builder = Avia_Builder();
        $this->avia_builder_content = $this->get_alb_content();
    }

    /**
     * Get the advance layout builder content.
     *
     * @return string
     */
    public function get_alb_content()
    {
        if ($this->get_alb_status() == "unknown") {
            return false;
        }

        $this->alb_content = $this->avia_builder->get_posts_alb_content($this->current_post->ID);

        return $this->alb_content;
    }

    /**
     * Get the advance layout builder status.
     *
     * @return string
     */
    public function get_alb_status()
    {
        return $this->avia_builder->get_alb_builder_status($this->current_post->ID);
    }

    /**
     * Retrieve the shortcodes based on the current shortcode tag.
     *
     * @return array
     */
    public function get_matched_shortcodes()
    {
        $alb_shortcodes = [];
        $alb_shortcode_regex = get_shortcode_regex(array($this->shortcode_tag));

        if (preg_match_all('/' . $alb_shortcode_regex . '/s', $this->avia_builder_content, $matches)
            && array_key_exists(2, $matches)
            && in_array($this->shortcode_tag, $matches[2])
        ) {
            $alb_shortcodes = array_filter($matches[0], function ($value, $key) {
                return str_contains($value, $this->shortcode_tag);
            }, ARRAY_FILTER_USE_BOTH);
        }

        if ($this->shortcode_child_tag) {
            $alb_shortcodes = $this->get_matched_child_shortcodes($alb_shortcodes);
        }

        return $alb_shortcodes;
    }

    /**
     * Retrieve the inner or child shortcodes if available.
     *
     * @return array
     */
    public function get_matched_child_shortcodes($alb_parent_shortcodes)
    {
        $alb_shortcodes = [];
        $alb_shortcode_child_regex = get_shortcode_regex(array($this->shortcode_child_tag));

        foreach ($alb_parent_shortcodes as $key => $alb_child_shortcode) {
            if (preg_match_all('/' . $alb_shortcode_child_regex . '/s', $alb_child_shortcode, $matches)
                && array_key_exists(2, $matches)
                && in_array($this->shortcode_child_tag, $matches[2])
            ) {
                $alb_shortcodes[$key]['container'] = $alb_child_shortcode;
                $alb_shortcodes[$key]['entries'] = [];

                if (!empty($matches[0])) {
                    foreach ($matches[0] as $index => $shortcode) {
                        $alb_shortcodes[$key]['entries'][$index]['shortcode'] = $shortcode;
                        $alb_shortcodes[$key]['entries'][$index]['attributes'] = shortcode_parse_atts($shortcode);
                    }
                }
            }
        }

        return $alb_shortcodes;
    }
}
