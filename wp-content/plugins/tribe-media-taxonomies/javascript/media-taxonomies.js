jQuery( document ).ready( function( $ ) {

	var media = wp.media,
		nls = mt_taxonomy_l10n.tax_filters;

	/*
	 // for debug : trace every event triggered in the Region controller
	 var originalTrigger = wp.media.view.MediaFrame.prototype.trigger;
	 wp.media.view.MediaFrame.prototype.trigger = function(){
	 console.log('MediaFrame Event: ', arguments[0]);
	 originalTrigger.apply(this, Array.prototype.slice.call(arguments));
	 }; //

	 // for Network debug
	 var originalAjax = media.ajax;
	 media.ajax = function( action ) {
	 console.log( 'media.ajax: action = ' + JSON.stringify( action ) );
	 return originalAjax.apply(this, Array.prototype.slice.call(arguments));
	 };
	 */

	/**
	 * Extended Filters dropdown with taxonomy term selection values
	 */
	if ( media ) {

		var attachmentsBrowser = media.view.AttachmentsBrowser;

		media.view.AttachmentsBrowser = media.view.AttachmentsBrowser.extend( {

			toggleBarTemplate: _.template( '<div class="media-taxonomy-toggles"><ul class="group"><li><%= heading %></li></ul></div>' ),
			toggleTemplate   : _.template( '<li class="media-tax-toggle" data-target="<%= key %>"><%= label %></li>' ),

			initialize: function() {

				attachmentsBrowser.prototype.initialize.apply( this, arguments );

				_.bindAll( this, 'toggleFilter' );

				this.$el.on( 'click', '.media-tax-toggle', this.toggleFilter );
			},

			createToolbar: function() {

				attachmentsBrowser.prototype.createToolbar.apply( this, arguments );

				this.$el.addClass( 'has-taxonomy-toggles' );
				this.toolbar.$el.prepend( this.toggleBarTemplate( {heading: nls.toggle_heading} ) );
				this.toolbar.toggles = this.toolbar.$el.find( '.media-taxonomy-toggles ul' );

				this.toolbar.$el.find( '.media-toolbar-primary' ).detach().prependTo( this.toolbar.$el );

				this.toolbar.toggles
					.append( this.toggleTemplate( {
						key  : this.toolbar.secondary.$el.find( 'select' ).first().attr( 'class' ),
						label: nls.toggle_type
					} ) );

				$.each( mediaTaxonomies, function( key, label ) {

					this.toolbar.toggles
						.append( this.toggleTemplate( {
							key  : key,
							label: label
						} ) );

				}.bind( this ) );

			},

			toggleFilter: function( e ) {

				var $this = $( e.currentTarget ),
					$select = this.toolbar.secondary.$el.find( '.' + $this.attr( 'data-target' ) ),
					defaultVal = $select.find( 'option:first' ).val();

				if ( $this.is( '.active' ) ) {
					$this.removeClass( 'active' );
					if ( $select.val() !== defaultVal ) {
						$select.val( defaultVal ).trigger( 'change' );
					}
					$select.fadeOut( 300 );
				}
				else {
					$this.addClass( 'active' );
					$select.fadeIn( 300 );
				}

			}

		} );

		$.each( mediaTaxonomies, function( key, label ) {

			media.view.AttachmentFilters[key] = media.view.AttachmentFilters.extend( {
				className: key,

				createFilters: function() {
					var filters = {};

					_.each( mediaTerms[key] || {}, function( term ) {

						var query = {};

						query[key] = {
							taxonomy : key,
							term_id  : parseInt( term.id, 10 ),
							term_slug: term.slug
						};

						filters[ term.slug ] = {
							text : term.label,
							props: query
						};
					} );

					filters.all = {
						text:  'All',
						props: {
							[key]: '',
						},
						priority: 10
					};

					this.filters = filters;
				}


			} );

			/**
			 * Replace the media-toolbar with our own
			 */
			var myDrop = media.view.AttachmentsBrowser;

			media.view.AttachmentsBrowser = media.view.AttachmentsBrowser.extend( {
				createToolbar: function() {

					media.model.Query.defaultArgs.filterSource = 'filter-media-taxonomies';

					myDrop.prototype.createToolbar.apply( this, arguments );

					this.toolbar.set( key, new media.view.AttachmentFilters[key]( {
							controller: this.controller,
							model     : this.collection.props,
							priority  : -80
						} ).render()
					);
				}
			} );

		} );
	}

	function saveTaxonomyChanges( $target, emitEvent ){

		var container = $target.closest( '.media-terms' ),
			row = container.parent(),
			data = {
				action       : 'save-media-terms',
				term_ids     : [],
				attachment_id: container.data( 'id' ),
				taxonomy     : container.data( 'taxonomy' )
			};

		container.find( 'input:checked' ).each( function() {
			data.term_ids.push( $( this ).val() );
		} );

		row.addClass( 'media-save-terms' );
		container.find( 'input' ).prop( 'disabled', 'disabled' );

		$.post( ajaxurl, data, function( response ) {
			row.removeClass( 'media-save-terms' );
			container.find( 'input' ).removeProp( 'disabled' );
			if( emitEvent ){
				$( document ).trigger( 'mt_taxonomy_saved', $target );
			}
		} );

	}

	/* Save taxonomy */
	$( 'html' )
		.delegate( '.media-terms input', 'change', function() {
			saveTaxonomyChanges( $( this ), false );
		} );

	// can listen to an additional event with a success event fired for special cases, eg the taxonomy behavior media manager

	$( document )
		.on( 'mt_taxonomy_update', function( e, input ) {
			saveTaxonomyChanges( $( input ), true );
		} );


	$( 'input.hide-column-tog' ).on( 'click', function() {

		var $this = $( this ),
			tax = $this.val();

		if ( tax == 'tags' ) {
			tax = 'taxonomy-post_tag';
		}

		if ( $this.is( ':checked' ) ) {
			$( '#' + tax ).removeClass( 'hidden' );
		}
		else {
			$( '#' + tax ).addClass( 'hidden' );
		}

	} );
} );