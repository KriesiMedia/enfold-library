<?php

/*
 * Filesize is stored in a postmeta.
 * For new uploaded files this is set automatically and when viewing files in listview it is updated for already existing files.
 *
 * We do not create the filesize postmeta by default as this might lead to a timeout problem for sites which have thousands of files in the media gallery.
 *
 * To create the postmeta manually:
 *   - Goto Media Library -> List View
 *   - Click tab "Screen Options" on right top
 *   - Option "Number of items per page" enter a very high number
 *   - Click Button "Apply" - this reloads the page and all displayed media library items show filesize and postmeta is saved
 *   - Now load all other pages - this will save filesize postmeta for these elements
 *
 * @since 4.8.7.2
 */
add_filter( 'avf_media_gallery_sortable_filesize', '__return_true' );
