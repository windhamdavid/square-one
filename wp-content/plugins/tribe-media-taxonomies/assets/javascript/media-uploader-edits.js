jQuery(document).ready(function ($) {

	var media = wp.media,
		relay = {},
		mediaTaxonomies = window.mediaTaxonomies || {},
		nls = mt_media.l10n.meta_toggles,
		config = mt_media.config,
		deleteTaxMessage = mt_media.l10n.alerts.warnDeleteTax.replace(/%n/g, '\n');

	//overwrite some l10n strings

	if (typeof _wpMediaViewsL10n !== 'undefined' && _wpMediaViewsL10n.hasOwnProperty('warnDelete')) {
		_wpMediaViewsL10n.warnDelete = mt_media.l10n.alerts.warnDelete.replace(/%s/g, '\n');
	}

	if (typeof commonL10n !== 'undefined' && commonL10n.hasOwnProperty('warnDelete')) {
		commonL10n.warnDelete = mt_media.l10n.alerts.warnDelete.replace(/%s/g, '\n');
	}

	$('.delete-tag').on('click', function (e) {

		e.stopPropagation();

		var term = $(this).closest('.column-name').find('.row-title').text(),
			doDelete = confirm(deleteTaxMessage.replace(/%s/g, term));

		if (!doDelete) {
			e.preventDefault();
		}

	});

	if (media) {

		// check against ajax success events in the admin when media is present and determine if the action is of type save-attachment-compat.
		// harvest id if so from data in response

		$(document).ajaxSuccess(function (e, xhr, settings) {
			if (
				typeof settings !== 'undefined' &&
				typeof settings.data !== 'undefined' &&
				settings.data.search('action=save-attachment-compat') != -1
			) {
				$.post(ajaxurl, {
					media_id: xhr.responseJSON.data.id,
					action: 'media_url',
				}, function (response) {
					if (response != '') {
						$('label[data-setting="url"]').children('input').val(response);
					}
				});
			}
		});

		// hack to update model after attachment has been uploaded and get missing acf fields

		$.extend(wp.Uploader.prototype, {
			success: function (attachment) {
				attachment.fetch({
					success: function () {
						$(relay).trigger('checkAcfMeta');
					}
				});

				// force the uploaded file to be selected
				var selection = wp.media.frame.state().get('selection');
				selection.add(attachment);
			}
		});

		var attachmentsBrowser = media.view.AttachmentsBrowser,
			attachmentCompat = media.view.AttachmentCompat;

		media.view.AttachmentCompat = media.view.AttachmentCompat.extend({

			initialize: function () {
				attachmentCompat.prototype.initialize.apply(this, arguments);

				_.bindAll(this, 'refreshInitialView', 'saveCompat');

				$(relay).off('mt_update_attachment').on('mt_update_attachment', this.refreshInitialView);
				$(relay).off('mt_compat_save').on('mt_compat_save', this.saveCompat);

				// we'll override the models saveCompat and make it silent, so the ui doesnt rerender every time we update

				this.model.saveCompat = function( data, options ) {
					var model = this;

					// If we do not have the necessary nonce, fail immeditately.
					if ( ! this.get('nonces') || ! this.get('nonces').update ) {
						return $.Deferred().rejectWith( this ).promise();
					}

					return wp.media.post( 'save-attachment-compat', _.defaults({
						id:      this.id,
						nonce:   this.get('nonces').update,
						post_id: wp.media.model.settings.post.id
					}, data ) ).done( function( resp, status, xhr ) {
						model.set( model.parse( resp, xhr ), {silent: true} );
					});
				}
			},

			refreshInitialView: function (e, str) {
				var compatItem = this.model.get('compat').item;

				compatItem = compatItem.replace('data-taxonomy="post_tag"><ul>', 'data-taxonomy="post_tag"><ul>' + str);

				this.model.set({ compat: { item: compatItem } }, {silent: true});
			},

			saveCompat: function() {
				var data = {};

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

				this.controller.trigger( 'attachment:compat:waiting', ['waiting'] );
				this.model.saveCompat(data).always( _.bind( this.postSave, this ) );
			}
		});

		media.view.AttachmentsBrowser = media.view.AttachmentsBrowser.extend({

			// templates used in reworking the media sidebar

			addTagTemplate: _.template('' +
				'<input id="new-media-tag" class="newtag form-input-tip" type="text">' +
				'<input id="add-media-tag" type="button" value="Add" class="button">'
			),
			tagTemplate: _.template('' +
				'<li id="post_tag-<%= term_taxonomy_id %>">' +
				'<label class="selectit">' +
				'<input type="checkbox" checked=\'checked\' id="in-post_tag-<%= term_taxonomy_id %>" name="tax_input[<%= taxonomy %>][]" value="<%= term_taxonomy_id %>">' +
				'<%= name %>' +
				'</label>' +
				'</li>'),
			toggleACFTemplate: _.template('' +
				'<header class="acf-toggle-wrap">' +
				'<span class="alignleft"><%= title %></span>' +
				'<span class="acf-toggle accordion-toggle" data-alt="<%= hide %>"><%= show %></span>' +
				'</header>' +
				'<table class="acf-meta-toggle"></table>' +
				''),
			toggleAccordionTemplate: _.template('' +
				'<span class="accordion-toggle" data-alt="<%= hide %>"><%= show %></span>' +
				''),
			toggleContext: {
				hide: nls.sidebar_hide,
				show: nls.sidebar_show
			},

			initialize: function () {

				attachmentsBrowser.prototype.initialize.apply(this, arguments);

				_.bindAll(this, 'addNewTag', 'toggleAccordion', 'createSingle');

				// custom events for toggling sidebar items and suppressing some events

				this.$el
					.off('click.accordion')
					.on('click.accordion', '.accordion-toggle', this.toggleAccordion)
					.off('change.addtag')
					.on('change.addtag', '#new-media-tag', function (e) {
						e.stopPropagation();
					})
					.off('click.addtag')
					.on('click.addtag', '#add-media-tag', this.addNewTag);

				// hack for buggy acf in mm

				$(relay)
					.off('checkAcfMeta')
					.on('checkAcfMeta', this.createSingle);

			},

			addNewTag: function (e) {

				e.stopPropagation();
				e.preventDefault();

				// ajax request to add new tags from inside the media manager
				// and have the attachment immediately added to it
				// handler is in util\admin.php

				var self = this,
					$input = $(e.currentTarget).prev(),
					$container = $input.closest('.media-terms'),
					data = {
						action: 'media-add-tag',
						post_id: $container.data('id'),
						"tag-name": $input.val(),
						"_wpnonce_media-add-tag": config.add_tag_ajax_nonce
					};

				if ($input.val().length) {

					$container.find('input').prop('disabled', 'disabled');

					$.ajax({
						type: "POST",
						url: ajaxurl,
						data: data
					})
						.done(function (response) {
							$container.find('input').removeProp('disabled');
							if (response.success) {
								var str = self.tagTemplate(response.tag_data);
								$container
									.find('ul')
									.prepend(str);
								$(relay).trigger('mt_update_attachment', str);
							}
							else {
								console.error(response.message);
							}

						})
						.fail(function () {
							$container.find('input').removeProp('disabled');
							console.error('There was an error adding the tag.');
						});

				}

			},

			createSingle: function (e) {

				attachmentsBrowser.prototype.createSingle.apply(this, arguments);

				this.wrapTags();
				this.wrapTaxonomies();

				var $sidebarAccordions = this.sidebar.$el.find('.media-sidebar-accordion');

				$sidebarAccordions
					.first()
					.addClass('first-toggle');

				$sidebarAccordions
					.last()
					.addClass('last-toggle');
			},

			toggleAccordion: function (e) {

				var $this = $(e.currentTarget),
					altText = $this.attr('data-alt'),
					$taxonomy = $this.closest('.media-sidebar-accordion'),
					$field = ($this.is('.acf-toggle')) ? $taxonomy.find('.acf-meta-toggle') : $taxonomy.find('.field');

				$this.attr('data-alt', $this.text()).text(altText);

				if ($this.is('.active')) {
					$this.removeClass('active');
					$field.hide();
				}
				else {
					$this.addClass('active');
					$field.show();
				}

			},

			wrapTags: function () {

				var $tagWrap = this.sidebar.$el.find('.compat-field-post_tag');

				// Assemble the tags and add the input for editing in new ones.

				$tagWrap
					.addClass('media-sidebar-accordion media-taxonomy')
					.find('th')
					.append(this.toggleAccordionTemplate(this.toggleContext))
					.wrapInner('<header />');

				$tagWrap
					.find('.media-terms[data-taxonomy="post_tag"]')
					.prepend(this.addTagTemplate());

				// we need to intercept the default media manager save behavior in the case of tags
				// otherwise inconsistent saving behavior occurs due to the timing of the save events
				// and the relationship with bb events for the compat fields

				$tagWrap
					.off('change.checkbox')
					.on('change.checkbox', 'input[type="checkbox"]', function (e) {
						e.stopPropagation();
						$(document).trigger('mt_taxonomy_update', $(e.currentTarget));
					});

				// trigger save compat with a simple change event.

				$(document)
					.off('mt_taxonomy_saved')
					.on('mt_taxonomy_saved', function () {
						$(relay).trigger('mt_compat_save');
					});

			},

			wrapTaxonomies: function () {

				$.each(mediaTaxonomies, function (key) {

					this.sidebar.$el
						.find('.compat-field-' + key)
						.not('.compat-field-post_tag')
						.addClass('media-sidebar-accordion media-taxonomy')
						.find('th')
						.append(this.toggleAccordionTemplate(this.toggleContext))
						.wrapInner('<header />');


				}.bind(this));

			}

		});

		media.view.EditSelectedButton = media.view.Button.extend({
			initialize: function () {
				media.view.Button.prototype.initialize.apply(this, arguments);
				if (this.options.filters) {
					this.listenTo(this.options.filters.model, 'change', this.filterChange);
				}
				this.listenTo(this.controller, 'selection:toggle', this.toggleDisabled);
				this.listenTo(this.controller, 'selection:toggle', this.validateSelection);
			},

			toggleDisabled: function () {
				this.model.set('disabled', !this.controller.state().get('selection').length);
			},

			validateSelection: function () {
				// make sure user has proper permission on selected item
				var selection = this.controller.state().get('selection');
				var modal = selection.at(selection.length - 1);
				var id = modal.id;

				var data = {
					action: 'can-user-edit',
					id: id
				};

				$.post(ajaxurl, data, function (response) {
					var resp = response;
					if (resp == 'false') {
						selection.remove(modal);
					}
				});
			},

			render: function () {
				media.view.Button.prototype.render.apply(this, arguments);
				if (this.controller.isModeActive('select')) {
					this.$el.addClass('delete-selected-button');
				}
				else {
					this.$el.addClass('delete-selected-button hidden');
				}
				return this;
			}
		});
	}
});