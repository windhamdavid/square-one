# Tribe Media Taxonomies

This plugin adds some useful filtering to the Media Uploader. Essentially it does two things:

- Adds the ability to filter by taxonomies applied to the Media items
- Adds the ability to filter by related posts

### Filter by Taxonomies

When a taxonomy has been added to the `attachment` post type, it will (provided it has any terms added to it) show up
as a link at the top of the Media Uploader. Clicking this link will enable a dropdown for selecting which term by which
to filter. Selecting a term will filter the Media items to only those tagged with that term.

Additionally, the plugin adds a form by which one may add terms to a Media item on-they-fly from the uploader. When a
media item is selected, an interface (very similar to the taxonomy metabox on the Edit screen) will show, allowing the 
user to add and remove taxonomy terms from the Media item at will.

Finally, the plugin also adds the ability to add new terms to the taxonomy from the same area, much like on the Edit 
post screen.

### Filter by Related Posts

When a new Posts 2 Posts connection is registered using `attachment` as the `from` parameter, a new dropdown is automatically
added to the Media Upload which allows the user to filter Media Items by post/page/whatever the `to` parameter is for that
connection. Selecting a post item will filter the Media items by any items connected to that post.

Additionally, the plugin allows relationships to be added/removed/modified from the Media Uploader dialog. Selecting a 
Media item will show the edit pane with an additional interface for handling relationships for that item. Any changes made
there will be saved on-the-fly.

### Notes/Caveats

- In order to have a connection appear on the Media Uploader, `attachment` must be set as the `from` parameter of the 
connection.
- If multiple post types are set as the `to` paramater of a connection, a distinct filter dropdown will be added for each 
post type on the Media Uploader.