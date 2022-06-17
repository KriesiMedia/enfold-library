<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Returns schema Toggles or Accordion data.
 */
class AviaYoastTogglesPiece extends AviaYoastSchemaPiece
{
    /**
     * Determines whether or not a piece should be added to the graph.
     *
     * @return bool
     */
    public function is_needed()
    {
        $this->shortcode_tag = 'av_toggle_container';
        $this->shortcode_child_tag = 'av_toggle';

        if ($this->get_alb_status() == 'unknown' || $this->get_alb_status() == 'inactive') {
            return false;
        }

        if (! has_shortcode($this->avia_builder_content, $this->shortcode_tag)) {
            return false;
        }

        return true;
    }

    /**
     * Render a list of questions based on the toggle shortcode, referencing them by ID.
     *
     * @return array Our Schema graph.
     */
    public function generate()
    {
        $toggle_containers = $this->get_matched_shortcodes();
        $graph = [];

        if (!empty($toggle_containers)) {
            foreach ($toggle_containers as $index => $toggle_container) {
                foreach ($toggle_container['entries'] as $index => $question) {
                    $graph[] = $this->generate_question_block($question['attributes'], ($index + 1));
                }
            }
        }
       
        return $graph;
    }

    /**
     * Generate a Question piece.
     *
     * @param array $question The question to generate schema for.
     * @param int   $position The position of the question.
     *
     * @return array Schema.org Question piece.
     */
    protected function generate_question_block($question, $position)
    {
        $url = $this->context->canonical . '#toggle-id-' . \esc_attr($position);

        $data = [
            '@type' => 'Question',
            '@id' => $url,
            'position' => $position,
            'url' => $url,
            'name' => $this->helpers->schema->html->smart_strip_tags($question['title']),
            'answerCount' => 1,
            'acceptedAnswer' => $this->add_accepted_answer_property($question),
        ];

        $data = $this->helpers->schema->language->add_piece_language($data);

        return $data;
    }

    /**
     * Adds the Questions `acceptedAnswer` property.
     *
     * @param array $question The question to add the acceptedAnswer to.
     *
     * @return array Schema.org Question piece.
     */
    protected function add_accepted_answer_property($question)
    {
        $answer = $this->extract_toggle_content($question);

        $data = [
            '@type' => 'Answer',
            'text' => $this->helpers->schema->html->sanitize($answer),
        ];

        $data = $this->helpers->schema->language->add_piece_language($data);

        return $data;
    }

    /**
     * Extracts the toggle content from shortcode attributes
     *
     * @param array $attributes The toggle attributes.
     *
     * @return string The formatted toggle content.
     */
    protected function extract_toggle_content($attributes)
    {
        $toggle_content = array_filter($attributes, function ($value, $key) {
            return is_int($key);
        }, ARRAY_FILTER_USE_BOTH);

        array_splice($toggle_content, 0, 2);
        array_splice($toggle_content, -1);

        return join(" ", $toggle_content);
    }
}
