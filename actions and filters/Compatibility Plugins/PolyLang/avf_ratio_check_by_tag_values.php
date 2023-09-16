/**
 * Polylang masonry tags portrait & landscape
 *
 * Use the "avf_ratio_check_by_tag_values" filter to adjust the language tag when using the polylang plugin.
 * When the tags are translated the language code is added on such as portrait-en & landscape-en and then will not work without this filter.
 * Related thread: https://kriesi.at/support/topic/masonry-tag-portrait-does-not-work-in-mulitlingual-pages/#post-1415656
 *
 **/

/** Add the following snippet in the functions.php file. **/

add_filter('avf_ratio_check_by_tag_values', 'avf_ratio_check_by_tag_values_mod', 10, 1);
function avf_ratio_check_by_tag_values_mod($tags) {
    $lang = pll_current_language();

	if($lang == 'en') $tags = array('portrait' => 'portrait-en', 'landscape' => 'landscape-en');
    return $tags;
}
