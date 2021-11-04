<?php

/*
 * Filesize is stored in a postmeta.
 * For new uploaded files this is set automatically and when viewing files in listview it is updated for already existing files.
 *
 * We do not make that by default as this might lead to a timeout when a site has thousands of files in the media gallery.
 *
 * @since 4.8.7.2
 */
add_filter( 'avf_media_gallery_sortable_filesize', '__return_true' );