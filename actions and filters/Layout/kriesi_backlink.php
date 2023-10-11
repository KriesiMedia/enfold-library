<?php
/**
 * Enfold Footer Text Modifications
 *
 * If you want to modify Enfold Footer Copyright and [nolink] is giving you
 * trouble with the link output
 *
 * @param string $link
 * @retrun string
 **/
function my_own_backlink( $link )
{
	//	original on line 1310  of enfold\framework\php\function-set-avia-frontend.php
	//	$link = " - <a {$no} href='http://www.kriesi.at'>{$theme_string}</a>";
	
	//	change the output string with e.g.
	
	$no = "rel='nofollow'";
	$theme_string = 'Your text to display';
	
	$link = " - <a {$no} href='http://www.your_domiain.xx'>{$theme_string}</a>";
	
	return $link;
}

add_filter( 'kriesi_backlink', 'my_own_backlink', 10, 1 );
