<?php

namespace Tribe\Media;

use Pimple\Container;
use Tribe\Project\Service_Loader;
use Tribe\Libs\Assets\Asset_Loader;
use Tribe\Media\Providers\Asset_Provider;
use Tribe\Media\Providers\Taxonomies_Provider;
use Tribe\Media\Providers\P2P_Provider;
use Tribe\Media\Providers\Connections_Provider;

class Main {

    private static $_instance;

    private $project;

    public function __construct() {
        $this->project = new Container();
        $this->project['service_loader'] = function( $c ) {
          return new Service_Loader( $c );
        };
    }

    public function init() {
        $this->register_providers();
        $this->enqueue_services();
        $this->project['service_loader']->initialize_services();
    }

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

    private function enqueue_services() {
        $this->project['service_loader']->enqueue( 'connections', 'init' );
        $this->project['service_loader']->enqueue( 'assets', 'init' );
        $this->project['service_loader']->enqueue( 'taxonomies', 'init' );
        $this->project['service_loader']->enqueue( 'p2p', 'init' );
    }

    /**
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