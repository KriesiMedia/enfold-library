<?php
/**
 * Create your custom sort of $tags array. Content of array is:
 *
 *			'Name of tag' => true | false
 *
 *
 * @added_by Günter
 * @since 5.9.10
 * @param array $tags
 * @param array $sc_atts
 * @return array
 */
function custom_avf_toggle_sorted_tags( array $tags, array $sc_atts )
{
	if( empty( $tags ) )
	{
		return $tags;
	}

	/**
	 * In this example we create an alphabetical sort order for german language (including Umlaut like ä,ö,ü)
	 *
	 * based on https://blog.digital-craftsman.de/order-values-in-alphabetical-order/
	 *
	 * Needs php extension https://www.php.net/manual/en/intl.setup.php
	 * See also https://www.php.net/manual/en/intl.installation.php
	 *
	 * for xampp Windows 11 add to php.ini - apache section:  extension=php_intl and restart server
	 */
	if( ! class_exists( 'Collator', false ) )
	{
		return $tags;
	}

	//	init for german
	$collator = new \Collator( 'de_DE' );
	if( ! $collator instanceof \Collator )
	{
		return $tags;
	}

	//	get keys to sort
	$values = array_keys( $tags );

	//	sort keys
	$collator->sort( $values );

	//	rebuild sorted array
	$sorted = [];

	foreach( $values as $value )
	{
		$sorted[ $value ] = $tags[ $value ];
	}

	return $sorted;
}


add_filter( 'avf_toggle_sorted_tags', 'custom_avf_toggle_sorted_tags', 10, 2 );

