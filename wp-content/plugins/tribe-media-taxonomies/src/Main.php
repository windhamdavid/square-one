<?php
/**
 * The main plugin class. Initializes the other required classes.
 *
 * @package Tribe\Media
 * @version 1.0
 * @since 2.0
 */

namespace Tribe\Media;

use Pimple\Container;
use Tribe\Project\Service_Loader;
use Tribe\Libs\Assets\Asset_Loader;
use Tribe\Media\Providers\Asset_Provider;
use Tribe\Media\Providers\Taxonomies_Provider;
use Tribe\Media\Providers\P2P_Provider;
use Tribe\Media\Providers\Connections_Provider;

/**
 * Class Main
 */
class Main {

    /**
     * @var Main
     */
    private static $_instance;

    /**
     * @var Container
     */
    private $project;

    /**
     * Main constructor.
     */
    public function __construct() {
        $this->project = new Container();
        $this->project['service_loader'] = function( $c ) {
          return new Service_Loader( $c );
        };
    }

    /**
     * Initialize the providers.
     */
    public function init() {
        $this->register_providers();
        $this->enqueue_services();
        $this->project['service_loader']->initialize_services();
    }

    /**
     * Register the various providers into the Container.
     */
    private function register_providers() {
        $this->project['connections'] = function() {
            return new Connections_Provider();
        };

        $this->project['asset_loader'] = function() {
            return new Asset_Loader( Tribe_Media_Path );
        };

        $this->project['assets'] = function( $c ) {
            return new Asset_Provider( $c );
        };

        $this->project['taxonomies'] = function() {
            return new Taxonomies_Provider();
        };

        $this->project['p2p'] = function( $c ) {
            return new P2P_Provider( $c );
        };
    }

    /**
     * Enqueue any services from the Container providers.
     */
    private function enqueue_services() {
        $this->project['service_loader']->enqueue( 'connections', 'init' );
        $this->project['service_loader']->enqueue( 'assets', 'init' );
        $this->project['service_loader']->enqueue( 'taxonomies', 'init' );
        $this->project['service_loader']->enqueue( 'p2p', 'init' );
    }

    /**
     * Get the class instance.
     *
     * @return Main
     */
    public static function instance() {
        if ( ! isset( self::$_instance ) ) {
            $className       = __CLASS__;
            self::$_instance = new $className();
        }

        return self::$_instance;
    }

}