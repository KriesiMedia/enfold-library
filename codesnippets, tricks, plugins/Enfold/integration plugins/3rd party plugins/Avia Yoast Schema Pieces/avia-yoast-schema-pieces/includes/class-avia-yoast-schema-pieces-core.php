<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link       kriesi.at
 * @since      1.0.0
 * @package    Avia_Yoast_Schema_Pieces
 * @subpackage Avia_Yoast_Schema_Pieces/includes
 * @subpackage Avia_Yoast_Schema_Pieces/shortcodes
 * @author     Kriesi <wordpress@kriesi.at>
 */
class AviaYoastSchemaPiecesCore
{
    public function __construct()
    {
        $this->load_dependencies();

        add_filter('wpseo_schema_graph_pieces', [ $this, 'set_pieces' ], 11, 2);
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Avia_Yoast_Schema_Pieces_Loader. Orchestrates the hooks of the plugin.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The base class for the theme's schema pieces.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-avia-yoast-schema-pieces.php';

        /**
         * The base class for the theme's schema pieces.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'shortcodes/toggles.php';
    }

    /**
     * Adds pieces to the main Yoast Schema Graph.
     *
     * @param array                 $pieces  Graph pieces to output.
     * @param \WPSEO_Schema_Context $context Object with context variables.
     *
     * @return array Graph pieces to output.
     */
    public function set_pieces($pieces, $context)
    {
        $pieces[] = new AviaYoastTogglesPiece($context);

        return $pieces;
    }
}
