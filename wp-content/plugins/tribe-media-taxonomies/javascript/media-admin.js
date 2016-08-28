(function ( $, media, FilterData ) {
	var attachmentCompat = media.view.AttachmentCompat;
	media.view.AttachmentCompat = media.view.AttachmentCompat.extend({
		save: function( event ) {
			var data = {};

			if ( event ) {
				event.preventDefault();
			}

			_.each( this.$el.serializeArray(), function ( pair ) {
				// if the pair.name is greater than 2 chars and [] is the last two chars
				if ( pair.name.length > 2 && '[]' === pair.name.substr( pair.name.length - 2 ) ) {
					// defined name as the name minus the []
					var name = pair.name.substr( 0, pair.name.length - 2 );
					if ( name in data ) {
						data[ name ].push( pair.value );
					} else {
						data[ name ] = [ pair.value ];
					}
				} else { // else if name does not end in []
					// send the value only
					data[ pair.name ] = pair.value;
				}

			} );

			this.controller.trigger( 'attachment:compat:waiting', [ 'waiting' ] );
			this.model.saveCompat( data ).always( _.bind( this.postSave, this ) );
		},

		initSelectize: function() {
			this.$el.find('.p2p-select').selectize({
				plugins: ['remove_button'],
			});
		},

		render: function() {
			attachmentCompat.prototype.render.apply(this, arguments);
			this.initSelectize();
			return this;
		},
	});

	var MediaLibraryTaxonomyFilter = media.view.AttachmentFilters.extend( {
		id: 'MediaLibraryTaxonomyFilter',
		createFilters: function () {
			var filters = {};
			var taxonomy = this.options.taxonomy;
			// Formats the 'terms' we've included via wp_localize_script()
			_.each( taxonomy.terms || [], function ( term, index ) {
				filters[ index ] = {
					text: term.name,
					props: {
						[taxonomy.query_var] : term.slug
					}
				};
			} );
			filters.all = {
				text: taxonomy.all_label,
				props: {
					[taxonomy.query_var] : ''
				},
				priority: 10
			};
			this.filters = filters;
		}
	} );
	var MediaLibraryP2PFilter = media.view.AttachmentFilters.extend( {
		id: 'MediaLibraryP2PFilter',
		createFilters: function () {
			var filters = {};
			var relationship = this.options.relationship;
			// Formats the 'terms' we've included via wp_localize_script()
			_.each( relationship.posts || [], function ( post, index ) {
				filters[ index ] = {
					text: post.post_title,
					props: {
						[relationship.query_var] : post.ID
					}
				};
			} );
			filters.all = {
				text: relationship.all_label,
				props: {
					[relationship.query_var] : 0
				},
				priority: 10
			};
			this.filters = filters;
		}
	} );

	var AttachmentsBrowser = media.view.AttachmentsBrowser;
	media.view.AttachmentsBrowser = media.view.AttachmentsBrowser.extend( {
		createToolbar: function () {
			// Make sure to load the original toolbar
			AttachmentsBrowser.prototype.createToolbar.call( this );

			var toolbar = this.toolbar,
				controller = this.controller,
				model = this.collection.props;

			_.each( FilterData.taxonomy || [], function ( filter, index ) {
				var id = 'TaxonomyFilter' + filter.name + index;
				toolbar.set( id, new MediaLibraryTaxonomyFilter( {
					id:         id,
					controller: controller,
					model:      model,
					priority:   -70,
					taxonomy:   filter
				} ).render() );
			} );

			_.each( FilterData.p2p || [], function ( filter, index ) {
				var id = 'P2PFilter' + filter.name + index;
				toolbar.set( id, new MediaLibraryP2PFilter( {
					id:           id,
					controller:   controller,
					model:        model,
					priority:     -70,
					relationship: filter
				} ).render() );
			} );
		}
	} );
})( jQuery, wp.media, TribeMediaAdminFilterData );
