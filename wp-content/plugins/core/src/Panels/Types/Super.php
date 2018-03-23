<?php

namespace Tribe\Project\Panels\Types;

use ModularContent\Fields;

class Super extends Panel_Type_Config {

	const NAME = 'super';

	const FIELD_TITLE          = 'title';
	const FIELD_TEXTAREA       = 'text_area';
	const FIELD_TEXT_RICH      = 'text_rich';
	const FIELD_REPEATER       = 'repeater';
	const FIELD_COLUMN_CONTENT = 'column_content';

	protected function panel() {

		$panel = $this->handler->factory( self::NAME, '' );
		$panel->set_template_dir( $this->ViewFinder );
		$panel->set_label( __( 'Demo Panel', 'tribe' ) );
		$panel->set_description( __( 'Displays all fields in one panel', 'tribe' ) );
		$panel->set_thumbnail( $this->handler->thumbnail_url( 'super.svg' ) );

		$panel->add_field( new Fields\HTML( [
			'name'        => 'intro',
			'label'       => 'Welcome to Demo Panels',
			'description' => '<p>Welcome to the Demo Panels Site! Here you can see a list of all of the fields we have
available to us in Panels 3.0. Feel free to mess around with them and see how they work!.</p><h3>This is an HTML Field</h3><p>As it happens, this is actually
an example of the HTML Field. It allows us to display any HTML within the UI. It doesn\'t have any effect on the FE output, but is
very handy for things like helper messages.</p>',
		] ) );

		$text_inputs = new Fields\Accordion( [
			'name'  => 'text_inputs',
			'label' => 'Single Inputs',
		] );

		$text_inputs->add_field( new Fields\HTML( [
			'name'        => 'text_intro',
			'description' => '<p>Here we see the various Single Inputs. These simple inputs take a single value (or a range of values for Range). They typically 
get used for things like Titles, app keys, and various numbers.</p>',
		] ) );

		$text_inputs->add_field( new Fields\Text( [
			'label'       => __( 'Text', 'tribe' ),
			'name'        => 'demo_text',
			'description' => __( 'Simple text field', 'tribe' ),
		] ) );

		$text_inputs->add_field( new Fields\HTML( [
			'name'        => 'number_intro',
			'description' => '<p>The Number field can have min, max, and step parameters. Here, the field has a 
max of 200 and a step of "10", meaning it can only accept multiples of 10 (10, 20, 30) and must be less than 200.</p>',
		] ) );

		$text_inputs->add_field( new Fields\Numeric_Input( [
			'label'       => __( 'Number Field', 'tribe' ),
			'name'        => 'demo_number',
			'description' => __( 'Like a text field, but only for numbers.' ),
			'min'         => 0,
			'max'         => 200,
			'step'        => 10,
		] ) );

		$text_inputs->add_field( new Fields\HTML( [
			'name'        => 'range_intro',
			'description' => '<p>The Range field functions like a number field, but displays as a slider. It can either have a single 
handle for a single value, or multiple handles to return a range of values. It can also optionally display an input showing the current value,
though this is available for single values only.</p>',
		] ) );

		$text_inputs->add_field( new Fields\Range( [
			'label'     => __( 'Range Field - Single Value', 'tribe' ),
			'name'      => 'demo_range_single',
			'min'       => 0,
			'max'       => 200,
			'default'   => [ 20 ],
			'handles'   => [ 20 ],
			'has_input' => true,
		] ) );

		$text_inputs->add_field( new Fields\HTML( [
			'name'        => 'video',
			'description' => '<p>Video field is defunct.</p>',
		] ) );

		$text_inputs->add_field( new Fields\Video( [
			'label'       => __( 'Video Field', 'tribe' ),
			'name'        => 'demo_video',
			'description' => __( 'Never used, defunct field', 'tribe' ),
		] ) );

		$text_inputs->add_field( new Fields\HTML( [
			'name'        => 'toggle_intro',
			'description' => '<p>The Toggle field functions as a simple on/off switch. It can either display as a toggle component, or a simple checkbox.</p>',
		] ) );

		$text_inputs->add_field( new Fields\Toggle( [
			'label' => __( 'Toggle Field', 'tribe' ),
			'name'  => 'demo_toggle',
		] ) );

		$panel->add_field( $text_inputs );

		$textarea_fields = new Fields\Accordion( [
			'name'  => 'textareas',
			'label' => __( 'Textarea Fields' ),
		] );

		$textarea_fields->add_field( new Fields\HTML( [
			'name'        => 'textarea_inputs',
			'description' => '<p>TextArea fields are useful for adding large chunks of content. They can either be standard Textarea inputs (for 
adding things like raw code or other non-formatted text), or a full WYSIWYG field, just like the Content Editor.</p>',
		] ) );

		$textarea_fields->add_field( new Fields\TextArea( [
			'label'    => __( 'Textarea', 'tribe' ),
			'name'     => self::FIELD_TEXTAREA,
			'richtext' => false,
		] ) );

		$textarea_fields->add_field( new Fields\TextArea( [
			'label'    => __( 'Textarea With Richtext', 'tribe' ),
			'name'     => self::FIELD_TEXT_RICH,
			'richtext' => true,
		] ) );

		$panel->add_field( $textarea_fields );

		$multi_selects = new Fields\Accordion( [
			'name'  => 'multi_selects',
			'label' => __( 'Multi-Select Fields' ),
		] );

		$multi_selects->add_field( new Fields\HTML( [
			'name'        => 'multi_selects_intro',
			'description' => '<p>Here we have all of the option/multi-select fields. They each require a list of options to be set, which
the user can then choose from.</p>',
		] ) );

		$multi_selects->add_field( new Fields\Checkbox( [
			'name'        => 'demo_checkbox',
			'label'       => __( 'Checkbox' ),
			'description' => __( 'Some options to go through.' ),
			'options'     => array(
				'personal_information' => __( 'Personal Information' ),
				'practice_areas'       => __( 'Practice Areas' ),
				'bar_admissions'       => __( 'Bar Admissions' ),
			),
			'default'     => array(
				'personal_information' => 1,
				'practice_areas'       => 1,
			),
		] ) );

		$multi_selects->add_field( new Fields\Radio( [
			'name'        => 'demo_radio',
			'label'       => __( 'Radio' ),
			'description' => __( 'Some options to go through.' ),
			'options'     => array(
				'personal_information' => __( 'Personal Information' ),
				'practice_areas'       => __( 'Practice Areas' ),
				'bar_admissions'       => __( 'Bar Admissions' ),
			),
			'default'     => array(
				'practice_areas' => 1,
			),
		] ) );

		$multi_selects->add_field( new Fields\Select( [
			'name'        => 'demo_select',
			'label'       => __( 'Select', 'tribe' ),
			'description' => __( 'The muchly used select field', 'tribe' ),
			'options'     => [
				'one'   => __( 'Thin', 'tribe' ),
				'two'   => __( 'Regular', 'tribe' ),
				'three' => __( 'Semibold', 'tribe' ),
				'four'  => __( 'Bold', 'tribe' ),
			],
			'default'     => 'one',
		] ) );

		$multi_selects->add_field( new Fields\HTML( [
			'name'        => 'image_select_intro',
			'description' => '<p>The Image Select and Swatch Select fields function very similarly to a Radio input. However, they
give visual options instead of just a label.</p><p>In the case of the Image Select, each option must have a related image or icon. Typically these
are either SVG or PNG files. This isn\'t necessarily displayed on the FE, but is simply a way to make the select more visual in the Admin</p>',
		] ) );

		$multi_selects->add_field( new Fields\ImageSelect( [
			'name'        => 'demo_image_select',
			'label'       => __( 'Image Select', 'tribe' ),
			'description' => __( 'The image select field.' ),
			'options'     => [
				'left'      => [
					'src'   => $this->handler->layout_icon_url( 'alignment-left.svg' ),
					'label' => __( 'Left', 'tribe' ),
				],
				'center'    => [
					'src'   => $this->handler->layout_icon_url( 'alignment-center.svg' ),
					'label' => __( 'Center', 'tribe' ),
				],
				'right'     => [
					'src'   => $this->handler->layout_icon_url( 'alignment-right.svg' ),
					'label' => __( 'Right', 'tribe' ),
				],
				'justified' => [
					'src'   => $this->handler->layout_icon_url( 'alignment-justified.svg' ),
					'label' => __( 'Justified', 'tribe' ),
				],

			],
			'default'     => 'left',
		] ) );

		$multi_selects->add_field( new Fields\HTML( [
			'name'        => 'swatch_select_intro',
			'description' => '<p>The Swatch Select displays a color (or gradient) for each option. It\'s very useful for things like background color selectors.</p>',
		] ) );

		$multi_selects->add_field( new Fields\Swatch_Select( [
			'name'        => 'demo_swatch',
			'label'       => __( 'Swatch Select', 'tribe' ),
			'description' => __( 'Ye olde swatch select', 'tribe' ),
			'options'     => [
				'blue'  => [
					'color' => '#c95539',
					'label' => 'Rust',
				],
				'blue2' => [
					'color' => '#e7b74d',
					'label' => 'Gold',
				],
				'blue3' => [
					'color' => '#f2e8b5',
					'label' => 'Blonde',
				],
				'blue4' => [
					'color' => '#b2e1ec',
					'label' => 'Sky',
				],
				'blue5' => [
					'color' => '#516165',
					'label' => 'Twilight',
				],
				'blue6' => [
					'color' => 'linear-gradient(113.59deg, rgba(186, 191, 16, 1) 0%, rgba(169, 189, 36, 1) 12.24%, rgba(126, 185, 88, 1) 37.36%, rgba(57, 179, 171, 1) 72.79%, rgba(0, 174, 239, 1) 100%)',
					'label' => 'Gradient',
				],
			],
			'default'     => 'one',
		] ) );

		$panel->add_field( $multi_selects );

		$image_fields = new Fields\Accordion( [
			'name'  => 'image_fields',
			'label' => __( 'Image Fields' ),
		] );

		$image_fields->add_field( new Fields\HTML( [
			'name'        => 'image_intro',
			'description' => '<p>The Image Fields are used to allow the user to either Upload or Choose an existing Image from the Media Library.</p>',
		] ) );

		$image_fields->add_field( new Fields\Image( [
			'label'       => __( 'Image', 'tribe' ),
			'name'        => 'demo_image',
			'description' => __( 'An image field.' ),
		] ) );

		$image_fields->add_field( new Fields\HTML( [
			'name'        => 'gallery_intro',
			'description' => '<p>The gallery field offers a similar experience to Image, but uses WordPress\'s Gallery functionality to choose multiple images. 
Normally a repeater is used instead of this field, but for simple galleries it works well.</p>',
		] ) );

		$image_fields->add_field( new Fields\ImageGallery( [
			'label'       => __( 'Image Gallery', 'tribe' ),
			'name'        => 'demo_image_gallery',
			'description' => __( 'The rarely used gallery.' ),
		] ) );

		$panel->add_field( $image_fields );

		$misc_fields = new Fields\Accordion( [
			'name'  => 'misc_fields',
			'label' => __( 'Miscellaneous Fields' ),
		] );

		$misc_fields->add_field( new Fields\HTML( [
			'name'        => 'cp_intro',
			'description' => '<p>The Color Picker field is used to select a specific, arbitrary color. It uses the React Color Picker library, 
which allows for a myriad of UI experiences. See <a href="https://casesandberg.github.io/react-color/" target="_blank">The Project Page</a> for more info.</p>',
		] ) );

		$misc_fields->add_field( new Fields\Color_Picker( [
			'name'         => 'demo_color',
			'label'        => __( 'Color Picker (Sketch Mode)', 'tribe' ),
			'description'  => __( 'Its a color picker.' ),
			'default'      => '',
			'picker_type'  => 'SketchPicker',
			'color_mode'   => 'rgb',
			'swatches'     => [ '#000000', '#fcfcfc', '#dddddd' ],
			'allow_clear'  => true,
			'input_active' => true,
		] ) );

		$misc_fields->add_field( new Fields\HTML( [
			'name'        => 'link_intro',
			'description' => '<p>Link Field allows users to input URLs with a label and defined target (either same window or new window). It\'s 
most often used when a CTA needs to be added to a panel or when a button is required.</p>',
		] ) );

		$misc_fields->add_field( new Fields\Link( [
			'label'       => __( 'Link', 'tribe' ),
			'name'        => 'demo_link',
			'description' => __( 'The beautiful link field.' ),
		] ) );

		$misc_fields->add_field( new Fields\HTML( [
			'name'        => 'post_list_intro',
			'description' => '<p>The Post List field is a powerful (and potentially confusing) field. It allows the user to 
choose to display specific existing posts, query for posts based on criteria such as Taxonomy, or create a "psuedo-post" from custom content.</p>
<p>If content is created, it\'s not actually added as a real Post. Rather, it\'s simply stored within the Panel content for display. This can be useful 
for allowing users to display external news articles or posts without having to actually create a post within their system.</p><p>
The Post List field can have minimums and maximuns, as well as a suggested # of posts to choose. It can allow all post types or subset, and can
be filtered by Taxonomies or other various filters.
</p>',
		] ) );

		$misc_fields->add_field( new Fields\Post_List( [
			'name'        => 'demo_post_list',
			'label'       => __( 'Post List', 'tribe' ),
			'description' => __( 'The post list field.' ),
			'max'         => 3,
			'suggested'   => 3,
		] ) );

		$panel->add_field( $misc_fields );

		$tabbed_fields = new Fields\Accordion( [
			'name'  => 'tabbed_fields',
			'label' => __( 'Tabbed Fields' ),
		] );

		$tabbed_fields->add_field( new Fields\HTML( [
			'name'        => 'tabbed_intro',
			'description' => '<p>Individual fields can be organized into a tabbed interface as seen below. Each tab can have an icon and label if required.</p>',
		] ) );

		$tab_group = new Fields\Tab_Group( [
			'name' => 'tab_group',
		] );

		$tab = new Fields\Tab( [
			'name' => 'desktop',
			'icon' => $this->handler->layout_icon_url( 'tab-icon-desktop.svg' ),
		] );

		$tab->add_field( new Fields\Text( [
			'name'  => 'desktop_title',
			'label' => 'Desktop Title',
		] ) );

		$tab->add_field( new Fields\Image( [
			'name'  => 'desktop_image',
			'label' => 'Desktop Image',
		] ) );

		$tab_group->add_field( $tab );

		$tab = new Fields\Tab( [
			'name' => 'tablet',
			'icon' => $this->handler->layout_icon_url( 'tab-icon-tablet.svg' ),
		] );

		$tab->add_field( new Fields\Text( [
			'name'  => 'tablet_title',
			'label' => 'Tablet Title',
		] ) );

		$tab->add_field( new Fields\Image( [
			'name'  => 'tablet_image',
			'label' => 'Tablet Image',
		] ) );

		$tab_group->add_field( $tab );

		$tab = new Fields\Tab( [
			'name' => 'mobile',
			'icon' => $this->handler->layout_icon_url( 'tab-icon-mobile.svg' ),
		] );

		$tab->add_field( new Fields\Text( [
			'name'  => 'mobile_title',
			'label' => 'Mobile Title',
		] ) );

		$tab->add_field( new Fields\Image( [
			'name'  => 'mobile_image',
			'label' => 'Mobile Image',
		] ) );

		$tab_group->add_field( $tab );

		$tabbed_fields->add_field( $tab_group );

		$panel->add_field( $tabbed_fields );

		$panel->add_field( new Fields\HTML( [
			'name'        => 'repeater_intro',
			'description' => '<p>The Repeater Field is used to allow the user to add an arbitrary number of field "groups" to a panel. For 
instance, it can be used to add "Rows", each of which is made up of a handful of fields. The rows can then be looped over and displayed in the FE.</p><p>
The repeater can have a min/max # of items (1 and 3 for this example). It takes "child" fields, which can be any of the Panel Fields seen here.</p>',
		] ) );

		$group = new Fields\Repeater( [
			'label'            => __( 'Repeater Field', 'tribe' ),
			'name'             => self::FIELD_REPEATER,
			'min'              => 1,
			'max'              => 3,
			'new_button_label' => __( 'Add Row', 'tribe' ),
			'strings'          => [
				'label.row_index' => __( 'Row %{index} |||| Row %{index}', 'tribe' ),
				'button.delete'   => __( 'Delete Row', 'tribe' ),
			],
		] );

		$group->add_field( new Fields\Text( [
			'label' => __( 'Row Title', 'tribe' ),
			'name'  => 'row_title',
		] ) );

		$group->add_field( new Fields\TextArea( [
			'label'    => __( 'Row Content', 'tribe' ),
			'name'     => 'row_content',
			'richtext' => false,
		] ) );

		$panel->add_field( $group );

		$panel->add_field( new Fields\HTML( [
			'name'        => 'setup_intro',
			'description' => '<p>Panel Fields can be organized into any number of Tabs (here, Content and Setup) for organization. This allows fields 
to be grouped intelligently.</p>',
		] ), 'setup' );

		$panel->add_field( new Fields\Text( [
			'label' => __( 'API Key', 'tribe' ),
			'name'  => 'api_key',
		] ), 'setup' );

		$panel->add_field( new Fields\Image( [
			'label'       => __( 'Header Image', 'tribe' ),
			'name'        => 'header_image',
			'description' => __( 'An image field.' ),
		] ), 'setup' );

		return $panel;
	}
}