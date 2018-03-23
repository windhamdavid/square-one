<?php


namespace Tribe\Project\Service_Providers;


use ModularContent\MetaBox;
use ModularContent\Plugin;
use ModularContent\Sets\Content_Types_Meta_Box;
use ModularContent\Sets\Panel_Set_Meta_Box;
use ModularContent\Sets\Post_Type_Configuration;
use ModularContent\Sets\Preview_Image_Meta_Box;
use ModularContent\Sets\Set;
use ModularContent\Sets\Template_Data;
use ModularContent\Sets\Template_Saver;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Panels;

class Panels_Provider implements ServiceProviderInterface {

	protected $panels = [
		Panels\Types\Hero::class,
		Panels\Types\Accordion::class,
		Panels\Types\CardGrid::class,
		Panels\Types\Gallery::class,
		Panels\Types\ImageText::class,
		Panels\Types\VideoText::class,
		Panels\Types\Interstitial::class,
		Panels\Types\MicroNavButtons::class,
		Panels\Types\Wysiwyg::class,
		Panels\Types\ContentSlider::class,
		Panels\Types\LogoFarm::class,
		Panels\Types\Testimonial::class,
		Panels\Types\PostLoop::class,
		Panels\Types\Super::class,
	];

	public function register( Container $container ) {
		$container['panels.plugin'] = function ( Container $container ) {
			return \ModularContent\Plugin::instance();
		};

		$container['panels.init'] = function ( $container ) {
			$init = new Panels\Initializer( $container['plugin_file'] );

			return $init;
		};

		add_action( 'plugins_loaded', function () use ( $container ) {
			$container['panels.init']->set_labels();

			foreach ( $this->panels as $panel ) {
				$container['panels.init']->add_panel_config( $panel );
			}
		}, 9, 0 );

		add_action( 'panels_init', function () use ( $container ) {
			$container['panels.init']->initialize_panels( $container['panels.plugin'] );
		}, 10, 0 );

		add_filter( 'panels_js_config', function ( $data ) use ( $container ) {
			return $container['panels.init']->modify_js_config( $data );
		}, 10, 1 );

		$container['panels.metabox'] = function ( $container ) {
			return new MetaBox();
		};

        add_filter( 'modular_content_always_has_title_field', '__return_false' );
		add_action( 'init', [ $this, 'register_capabilities' ] );
		add_action( 'init', [ $this, 'register_meta_boxes' ] );
		add_action( 'init', [ $this, 'register_template_data' ] );
		add_action( 'init', [ $this, 'register_template_saver' ] );
		add_action( 'wp_enqueue_scripts', function(){
            wp_enqueue_style( 'buttons' );
        });
		add_action( 'wp_footer', [ $this, 'register_panels_stuff_fe' ], 11 );
		add_action( 'wp_footer', function(){
		    echo "<script type='text/javascript' src='https://square1.tribe/wp-content/plugins/panel-builder/ui/dist/master.js?ver=1521778242'></script>";
        }, 999999);
	}

	public function register_panels_stuff_fe() {
        wp_enqueue_media();

		wp_enqueue_script( 'quicktags' );
		wp_enqueue_style( 'buttons' );

        wp_enqueue_script( 'editor' );


		wp_enqueue_script( 'wplink' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );

		wp_enqueue_script( 'wp-fullscreen-stub' );

		wp_enqueue_script( 'media-plupload' );
		wp_enqueue_script( 'media-models' );
        wp_enqueue_script( 'media-views' );
        wp_enqueue_script( 'media-audiovideo' );
        wp_enqueue_script( 'media-editor' );
        wp_enqueue_script( 'mediaelement' );
        wp_enqueue_script( 'mediaelement-core' );
        wp_enqueue_script( 'mediaelement-migrate' );


		add_thickbox();
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'wp-embed' );
		wp_enqueue_script( 'media-upload' );
		$metabox     = tribe_project()->container()['panels.metabox'];
		$app_scripts = Plugin::plugin_url( 'ui/dist/master.js' );

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$app_scripts = apply_filters( 'modular_content_js_dev_path', $app_scripts );
			wp_register_script( 'panels-admin-ui', $app_scripts, [ 'wp-util', 'media-upload', 'media-views' ], time(), false );
		} else {
			wp_register_script( 'panels-admin-ui', $app_scripts, [ 'wp-util', 'media-upload', 'media-views' ], time(), false );
		}
		wp_enqueue_style( 'panels-admin-ui', Plugin::plugin_url( 'ui/dist/master.css' ), [] );
        wp_register_style( 'font-awesome', Plugin::plugin_url('lib/Font-Awesome/css/font-awesome.css'), array(), '2.0' );
		wp_enqueue_style( 'modular-content-meta-box', Plugin::plugin_url( 'ui/dist/react-libs.css' ), [ 'font-awesome' ] );
//		wp_enqueue_script( 'panels-admin-ui' );
		wp_add_inline_script( 'media-upload', $this->inline_scripts(), 'before' );
		wp_localize_script( 'media-upload', 'ModularContentConfig', $metabox->js_config() );
		wp_localize_script( 'media-upload', 'ModularContentI18n', $metabox->js_i18n() );
		add_action( 'wp_header', array( $this, 'print_admin_theme_css' ), 10, 0 );
	}

	protected function inline_scripts() {
		$inline_scripts = 'window.noZensmooth = true';

		return $inline_scripts;
	}

	/**
	 * @see http://wordpress.stackexchange.com/questions/130943/wordpress-3-8-get-current-admin-color-scheme
	 * @return void
	 */
	public function print_admin_theme_css() {

		global $_wp_admin_css_colors;
		global $admin_colors;
		$admin_colors = $_wp_admin_css_colors;

		$user_color_scheme_name = get_user_meta( get_current_user_id(), 'admin_color', true );
		$user_color_scheme      = isset( $admin_colors[ $user_color_scheme_name ] ) ? $admin_colors[ $user_color_scheme_name ] : false;

		if ( $user_color_scheme ) {

			// This little guy gets the index of the most suitable primary color
			// depending on the actual color scheme
			switch ( $user_color_scheme_name ) {
				case 'coffee':
				case 'ectoplasm':
				case 'ocean':
				case 'sunrise':
					$primary_color_index = 2;
					break;
				default:
					$primary_color_index = 3;
					break;
			}
			?>
            <style id='panel-builder-colors'>
                .panel-builder-text-color {
                    color: <?php echo $user_color_scheme->colors[$primary_color_index]; ?>;
                }

                .panel-builder-bg-color {
                    background-color: <?php echo $user_color_scheme->colors[$primary_color_index]; ?>;
                }

                .panel-builder-border-color {
                    border-color: <?php echo $user_color_scheme->colors[$primary_color_index]; ?>;
                }
            </style>
			<?php
		}
	}

	public function register_post_type() {
		$configuration = new Post_Type_Configuration();
		$configuration->register_post_type();
		add_filter( 'post_updated_messages', [ $configuration, 'post_updated_messages' ], 10, 1 );
		add_filter( 'bulk_post_updated_messages', [ $configuration, 'bulk_edit_messages' ], 10, 2 );
	}

	public function register_image_sizes() {
		add_image_size( Set::IMAGE_SIZE_THUMBNAIL, 600, 800, false );
		add_image_size( Set::IMAGE_SIZE_PREVIEW, 500, 3000, false );
	}

	public function register_capabilities() {
		$code_version = 1;
		$db_version   = get_option( 'panel-set-capabilities-version', 0 );
		if ( version_compare( $code_version, $db_version, '<=' ) ) {
			return;
		}
		$capabilities = [
			'create',
			'read',
			'read_private',
			'edit',
			'edit_others',
			'edit_private',
			'edit_published',
			'delete',
			'delete_others',
			'delete_private',
			'delete_published',
			'publish',
		];
		foreach ( [ 'administrator', 'editor' ] as $role_name ) {
			$role = get_role( $role_name );
			if ( ! $role ) {
				continue;
			}
			foreach ( $capabilities as $cap ) {
				$role->add_cap( $cap . '_' . Set::POST_TYPE . 's' );
			}
		}
		update_option( 'panel-set-capabilities-version', 1 );
	}

	public function register_meta_boxes() {
		Panel_Set_Meta_Box::init();
		$content_types = new Content_Types_Meta_Box( Set::POST_TYPE, [
			'title'    => __( 'Supported Content Types', 'tribe' ),
			'context'  => 'side',
			'priority' => 'default',
		] );
		$content_types->hook();

		$preview_image = new Preview_Image_Meta_Box( Set::POST_TYPE, [
			'title'    => __( 'Image Preview', 'tribe' ),
			'context'  => 'side',
			'priority' => 'low',
		] );
		$preview_image->hook();
	}

	public function register_template_data() {
		$picker = new Template_Data();
		$picker->hook();
	}

	public function register_template_saver() {
		$saver = new Template_Saver();
		$saver->hook();
	}
}