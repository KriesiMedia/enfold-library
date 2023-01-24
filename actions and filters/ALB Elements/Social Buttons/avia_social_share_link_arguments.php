<?php
/**
 * "avia_social_share_link_arguments" filter can be used to add custom social icons to Share Box.
 *
 * Following example is for TikTok share icon. 
 *
 * TikTok share icon has to be added before it can be added to Share Box: https://kriesi.at/documentation/enfold/social-share-buttons/#how-to-add-custom-social-icons-to-enfold-options
 *
 */
add_filter('avia_social_share_link_arguments', 'avia_add_social_share_link_arguments', 10, 1);

function avia_add_social_share_link_arguments($args){
  
    $tiktok = array('tiktok' => array("encode"=>true, "encode_urls"=>false, "pattern" => "https://www.tiktok.com/", 'label' => __("Share on TikTok",'avia_framework')));
  
    $args = array_merge($tiktok, $args);
  
    return $args;
}
