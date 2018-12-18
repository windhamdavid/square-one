# Custom Widgets

Creating custom widgets is a fairly simple process and can take advantage of ACF meta fields when extending the abstract Tribe\Projects\Widgets\Widget class.

You start by creating a new file for your widget in the Tribe\Project\Widgets namespace.

Using a **Recent_Posts** widget as an example you would see:

- /wp-content/plugins/core/src/Widgets/Recent_Posts.php

This file should be in the Widgets namespace

```namespace Tribe\Project\Widgets;```

### Recent_posts.php

The {{Widget_Name}}.php file in our example case, Recent_Posts.php, must define the constant slug of the custom widget.  It must also include some required methods:

 - get_slug()
 - get_title()
 - get_options()
 - render_admin()
 - render_display()

At it's simplest, it will nearly always look like:

```php
namespace Tribe\Project\Widget\Recent_Posts;

class Recent_Posts extends Widget {

	const SLUG = 'recent_posts';

	public function get_slug() {
		return self::SLUG;
	}

	public function get_title() {
		return __( 'Recent Posts', 'tribe' );
	}

	public function get_options() {
		return [
			'classname' => self::SLUG,
			'description' => __( 'Display recent posts.', 'tribe' ),
		];
	}

	public function render_admin( $options ) {
		return '';
	}

	public function render_display( $args, $options ) {
		$template = new Template( $args['widget_id'], 'content/widgets/recent-posts.twig' );
		echo $template->render();
	}
	
}
```

## Render with Twig

## Registering A Widget
