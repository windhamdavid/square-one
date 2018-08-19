<?php


namespace Tribe\Project\Service_Providers;


use ModularContent\MetaBox;
use ModularContent\PanelCollection;
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
use Tribe\Project\Components_Docs\Ajax;
use Tribe\Project\Components_Docs\Component_Item;
use Tribe\Project\Components_Docs\Panel_Item;
use Tribe\Project\Components_Docs\Registry;
use Tribe\Project\Components_Docs\Router;
use Tribe\Project\Components_Docs\Templates\Component_Docs;
use Tribe\Project\Components_Docs\Theme\Assets;
use Tribe\Project\Panels\Types\CardGrid;
use Tribe\Project\Panels\Types\ContentSlider;
use Tribe\Project\Panels\Types\Gallery;
use Tribe\Project\Panels\Types\ImageText;
use Tribe\Project\Panels\Types\Interstitial;
use Tribe\Project\Panels\Types\LogoFarm;
use Tribe\Project\Panels\Types\MicroNavButtons;
use Tribe\Project\Panels\Types\PostLoop;
use Tribe\Project\Panels\Types\Testimonial;
use Tribe\Project\Panels\Types\VideoText;
use Tribe\Project\Panels\Types\Wysiwyg;
use Tribe\Project\Templates\Components\Accordion;
use Tribe\Project\Templates\Components\Breadcrumbs;
use Tribe\Project\Templates\Components\Button;
use Tribe\Project\Templates\Components\Card;
use Tribe\Project\Templates\Components\Content_Block;
use Tribe\Project\Templates\Components\Image;
use Tribe\Project\Templates\Components\Pagination;
use Tribe\Project\Templates\Components\Quote;
use Tribe\Project\Panels\Types\Accordion as Accordion_Panel;
use Tribe\Project\Panels\Types\Hero;
use Tribe\Project\Templates\Components\Search as Search_Component;
use Tribe\Project\Templates\Components\Slider;
use Tribe\Project\Templates\Components\Text;
use Tribe\Project\Templates\Components\Title;
use Tribe\Project\Templates\Components\Video;

class Components_Docs_Provider implements ServiceProviderInterface {

	protected $panels = [
		Accordion_Panel::class,
		Hero::class,
		CardGrid::class,
		Gallery::class,
		ImageText::class,
		VideoText::class,
		Interstitial::class,
		MicroNavButtons::class,
		Wysiwyg::class,
		ContentSlider::class,
		LogoFarm::class,
		Testimonial::class,
		PostLoop::class,
	];

	protected $components = [
		Accordion::class,
		Button::class,
		Card::class,
		Content_Block::class,
		Quote::class,
		Image::class,
		Text::class,
		Title::class,
		Search_Component::class,
		Breadcrumbs::class,
		Slider::class,
		Video::class,
		Pagination::class,
	];

	public function register( Container $container ) {

		$this->add_template_paths( $container );

		$container['components_docs.router'] = function () {
			return new Router();
		};

		$container['components_docs.registry'] = function () {
			return new Registry();
		};

		$container['components_docs.assets'] = function ( $container ) {
			return new Assets( $container['components_docs.router'] );
		};

		$container['components_docs.ajax'] = function ( $container ) {
			return new Ajax( $container['components_docs.registry'] );
		};

		foreach ( $this->components as $component ) {
			$component_item = new Component_Item( $component );
			$container['components_docs.registry']->add_item( $component_item->get_slug(), $component_item );
		}

		add_action( 'wp', function () use ( $container ) {
			$this->add_panel_items( $container );
		} );

		$container['component_docs.template'] = function ( $container ) {
			$twig_path = 'main.twig';
			return new Component_Docs( $twig_path, null, $container['components_docs.registry'] );
		};

		add_action( 'init', function () use ( $container ) {
			$container['components_docs.router']->add_rewrite_rule();
			$container['components_docs.ajax']->add_ajax_actions();
		}, 10, 0 );

		add_filter( 'core_js_config', function ( $data ) use ( $container ) {
			return $container['components_docs.ajax']->add_config_items( $data );
		}, 10, 1 );

		add_filter( 'template_include', function ( $template ) use ( $container ) {
			return $container['components_docs.router']->show_components_docs_page( $template );
		}, 10, 1 );

		add_action( 'wp_enqueue_scripts', function () use ( $container ) {
			$container['components_docs.assets']->enqueue_scripts();
			$container['components_docs.assets']->enqueue_styles();
		}, 10, 0 );

		$container['panels.metabox'] = function ( $container ) {
			return new MetaBox();
		};

		if ( is_admin() || ! $this->is_docs_page() ) {
			return;
		}

		add_action( 'init', [ $this, 'register_capabilities' ] );
		add_action( 'init', [ $this, 'register_meta_boxes' ] );
		add_action( 'init', [ $this, 'register_template_data' ] );
		add_action( 'init', [ $this, 'register_template_saver' ] );

		add_action( 'wp_enqueue_scripts', function () {
			wp_enqueue_style( 'buttons' );
		} );

		add_action( 'wp_footer', [ $this, 'register_panels_stuff_fe' ], 11 );

		add_action( 'wp_footer', function () {
			echo "<script type='text/javascript' src='" . home_url() . "/wp-content/plugins/panel-builder/ui/dist/master.js?ver=1521778242'></script>";
		}, 999999 );
	}

	private function is_docs_page() {
		return strpos( $_SERVER['REQUEST_URI'], 'components_docs/panel_' ) !== false;
	}

	protected function add_panel_items( Container $container ) {
		$collection = PanelCollection::find_by_post_id( 2 );
		foreach ( $collection->panels() as $panel ) {
			$panel_type_obj = $panel->get_type_object();
			$class          = get_class( $panel_type_obj );
			$component_item = new Panel_Item( $class, $panel );
			$container['components_docs.registry']->add_item( 'panel_' . $component_item->get_slug(), $component_item, 'Panels' );
		}
	}

	protected function add_template_paths( Container $container ) {
		add_filter( 'tribe/project/twig', function ( $twig ) use ( $container ) {
			$template_path = dirname( dirname( __FILE__ ) ) . '/Components_Docs/Twig/';
			$container['twig.loader']->addPath( $template_path );

			$twig = new \Twig_Environment( $container['twig.loader'], $container['twig.options'] );
			$twig->addExtension( $container['twig.extension'] );

			return $twig;
		}, 10, 1 );
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
		wp_register_style( 'font-awesome', Plugin::plugin_url( 'lib/Font-Awesome/css/font-awesome.css' ), array(), '2.0' );
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
		$admin_colors           = $_wp_admin_css_colors;
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