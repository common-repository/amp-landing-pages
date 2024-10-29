( function( blocks, components, i18n, element ) {
	var el = element.createElement;
	// var Ed				  = wp.blocks;
	var Ed				  = wp.editor;
	var ToggleControl     = Ed.InspectorControls.ToggleControl;
	var InspectorControls = Ed.InspectorControls;
	var AlignmentToolbar  = Ed.AlignmentToolbar;
	var __                = wp.i18n.__;

	blocks.registerBlockType(
		'tacg/tb', {
			title: __( 'Testimonial' ),
			description: __( 'A custom block for displaying testimonials with an author image. Edit rich text and image directly in the block.' ),
			icon: 'format-status',
			category: 'common',
			attributes: {
				title: {type: 'array', source: 'children', selector: 'h3'},
				subtitle: {type: 'array', source: 'children', selector: 'h4'},
				cont: {type: 'array', source: 'children', selector: 'p'},
				mediaID: {type: 'number'},
				mediaURL: {type: 'string', source: 'attribute', selector: 'img', attribute: 'src'},
				bgColor: {type: 'string', default:''},
				fontColor: {type: 'string', default:''},
			},
			edit: function( props ) {
				var attributes = props.attributes;
				return [
					el(
						Ed.BlockControls, { key: 'controls' },
						el(
							'div', { className: 'components-toolbar' },
							el(
								Ed.MediaUpload, {
									onSelect: function( media ) { return props.setAttributes( { mediaURL: media.url, mediaID: media.id, } ); },
									type: 'image',
									render: function( obj ) {
										return el(
											components.Button, {
												className: 'components-icon-button components-toolbar__control',
												onClick: obj.open
											},
											el(
												'svg', { className: 'dashicon dashicons-edit', width: '20', height: '20' },
												el( 'path', { d: "M2.25 1h15.5c.69 0 1.25.56 1.25 1.25v15.5c0 .69-.56 1.25-1.25 1.25H2.25C1.56 19 1 18.44 1 17.75V2.25C1 1.56 1.56 1 2.25 1zM17 17V3H3v14h14zM10 6c0-1.1-.9-2-2-2s-2 .9-2 2 .9 2 2 2 2-.9 2-2zm3 5s0-6 3-6v10c0 .55-.45 1-1 1H5c-.55 0-1-.45-1-1V8c2 0 3 4 3 4s1-3 3-3 3 2 3 2z" } )
											)
										);
									}
								}
							)
						),
					),

						! ! focus && el(
							InspectorControls,
							{ key: 'inspector' },
							el(
								wp.components.PanelColor, {
									colorValue: attributes.bgColor,
									initialOpen: false,
									title: __( 'Background Color' ),
								},
								el(
									wp.components.ColorPalette, {
										label: __( 'Background Color' ),
										onChange: function(pass){props.setAttributes( {bgColor: pass} );},
										colors: [{ color: '#00d1b2', name: 'teal' }, { color: '#3373dc', name: 'royal blue' }, { color: '#209cef', name: 'sky blue' }, { color: '#22d25f', name: 'green' }, { color: '#ffdd57', name: 'yellow' }, { color: '#ff3860', name: 'pink' }, { color: '#7941b6', name: 'purple' }, { color: '#392F43', name: 'black' }]
									}
								)
							),
							el(
								wp.components.PanelColor, {
									colorValue: attributes.fontColor,
									initialOpen: false,
									title: __( 'Font Color' ),
								},
								el(
									wp.components.ColorPalette, {
										label: __( 'Font Color' ),
										onChange: function(pass){props.setAttributes( {fontColor: pass} );},
										colors: [{ color: '#00d1b2', name: 'teal' }, { color: '#3373dc', name: 'royal blue' }, { color: '#209cef', name: 'sky blue' }, { color: '#22d25f', name: 'green' }, { color: '#ffdd57', name: 'yellow' }, { color: '#ff3860', name: 'pink' }, { color: '#7941b6', name: 'purple' }, { color: '#392F43', name: 'black' }]
									}
								)
							),
						),

						el(
							'div', { className: props.className , style: { backgroundColor: attributes.bgColor , color: attributes.fontColor } },
							el(
								'div', {
									className: attributes.mediaID ? 'tbimg image-active' : 'tbimg image-inactive',
									// style: attributes.mediaID ? { backgroundImage: 'url('+attributes.mediaURL+')' } : {}
								},
								el(
									Ed.MediaUpload, {
										onSelect: function( media ) { return props.setAttributes( { mediaURL: media.url, mediaID: media.id, } ); },
										type: 'image',
										value: attributes.mediaID,
										render: function( obj ) {
											return el(
												components.Button, {
													className: attributes.mediaID ? 'image-button' : 'button button-large',
													onClick: obj.open
												},
												! attributes.mediaID ? __( 'Upload Image' ) : el( 'img', { src : attributes.mediaURL } )
											);
										}
									}
								)
							),
							el(
								'div', {
									className: 'tbcon' },
								el(
									Ed.RichText, {
										tagName: 'h3',
										placeholder: 'Who are you?',
										keepPlaceholderOnFocus: true,
										value: attributes.title,
										isSelected: false,
										onChange: function( newTitle ) {
											props.setAttributes( { title: newTitle } );
										},
										style: { color: attributes.fontColor },
									}
								),
								el(
									Ed.RichText, {
										tagName: 'h4',
										placeholder: __( 'Subtitle' ),
										keepPlaceholderOnFocus: true,
										value: attributes.subtitle,
										isSelected: false,
										onChange: function( newSubtitle ) {
											props.setAttributes( { subtitle: newSubtitle } );
										},
										style: { color: attributes.fontColor },
									}
								),
								el( 'div' , {className: 'qt fa fa-quote-left', style: { color: attributes.fontColor }}, '' ),
								el( 'div' , {className: 'qt hr', style: { borderColor: attributes.fontColor } }, '' ),
								el( 'div' , {className: 'qt fa fa-quote-right', style: { color: attributes.fontColor }}, '' ),
								el( 'div' , {className: 'cf'}, '' ),
								el(
									Ed.RichText, {
										tagName: 'p',
										placeholder: __( 'Write a brief testimonial...' ),
										keepPlaceholderOnFocus: true,
										value: attributes.cont,
										onChange: function( newCont ) {
											props.setAttributes( { cont: newCont } );
										},
										style: { color: attributes.fontColor },
									}
								),
							),
						),
					];
			},
			save: function( props ) {
				var attributes = props.attributes;
				return (
					el(
						'div', {className: props.className , style: { backgroundColor: attributes.bgColor , color: attributes.fontColor } },
						el(
							'div', { className: 'tbimg'},
							el( 'img', {src: attributes.mediaURL , className: 'wp-image-' + attributes.mediaID} ),
						),
						el(
							'div', { className: 'tbcon' },
							el( 'h3' , {className: 'tbttl'}, attributes.title ),
							el( 'h4' , {className: 'tbsttl'}, attributes.subtitle ),
							el( 'div' , {className: 'qt fa fa-quote-left'}, '' ),
							// el( 'hr' , {className: 'qt hr'}, '' ),
							el( 'hr' , {className: 'qt hr' , style: { borderColor: attributes.fontColor } }, '' ),
							el( 'div' , {className: 'qt fa fa-quote-right'}, '' ),
							el( 'div' , {className: 'cf'}, '' ),
							el( 'p' , {className: 'tbcont'}, attributes.cont ),
						),
						el( 'div' , {className: 'cf'}, '' ),
					)
				);
			},
		}
	);

	blocks.registerBlockType(
		'tacg/ms', {
			title: __( 'Media Split' ),
			description: __( 'A custom block for displaying an image or video on one side, and text on the other.' ),
			icon: 'playlist-video',
			category: 'common',
			attributes: {
				title: {type: 'array', source: 'children', selector: 'h3'},
				subtitle: {type: 'array', source: 'children', selector: 'h4'},
				cont: {type: 'array', source: 'children', selector: 'p'},
				mediaID: {type: 'number'},
				mediaURL: {type: 'string', source: 'attribute', selector: 'img', attribute: 'src'},
				alignment: {type: 'string', default: ''},
				embedURL: {type: 'string', default: ''},
				// bgColor: {type: 'string', default: ''},
				// apstyle: {type: 'string', default: ''},
			},
			edit: function( props ) {
				var attributes = props.attributes;
				var alignment  = props.attributes.alignment;

				return [
					! ! focus && el(
						Ed.BlockControls, { key: 'controls' },
						el(
							'div', { className: 'components-toolbar' },
							el(
								Ed.MediaUpload, {
									onSelect: function( media ) { return props.setAttributes( { mediaURL: media.url, mediaID: media.id, } ); },
									type: 'image',
									render: function( obj ) {
										return el(
											components.Button, {
												className: 'components-icon-button components-toolbar__control',
												onClick: obj.open
											},
											el(
												'svg', { className: 'dashicon dashicons-edit', width: '20', height: '20' },
												el( 'path', { d: "M2.25 1h15.5c.69 0 1.25.56 1.25 1.25v15.5c0 .69-.56 1.25-1.25 1.25H2.25C1.56 19 1 18.44 1 17.75V2.25C1 1.56 1.56 1 2.25 1zM17 17V3H3v14h14zM10 6c0-1.1-.9-2-2-2s-2 .9-2 2 .9 2 2 2 2-.9 2-2zm3 5s0-6 3-6v10c0 .55-.45 1-1 1H5c-.55 0-1-.45-1-1V8c2 0 3 4 3 4s1-3 3-3 3 2 3 2z" } )
											)
										);
									}
								}
							),
							el(
								Ed.AlignmentToolbar, {
									value: alignment,
									onChange: function(pass){props.setAttributes( {alignment:pass} );},
								}
							),
						),
					),

					! ! focus && el(
						InspectorControls,
						{ key: 'inspector' },
						el( 'h2',{},__( 'Block Options' ) ),
						el(
							wp.components.TextControl, {
								type: 'url',
								label: __( 'Video/Social Media Embed URL' ),
								value: attributes.embedURL,
								onChange: function(pass){
									props.setAttributes( {embedURL: pass} );
									if (pass) {
										// props.setAttributes( { apstyle: 'emb' } );
										props.setAttributes( { mediaID: '' } );
										props.setAttributes( { mediaURL: '' } );
									} else {
										// props.setAttributes( { apstyle: '' } );
									}
								},
							}
						),
						el( 'p',{}, __( 'AMP supports video and/or social embeds from: YouTube, Facebook, Twitter, Instagram, Vimeo, Dailymotion, Imgur and Reddit. This will override the chosen image (if any) and display the embedded media in it\'s place.' ) ),
						// el( wp.components.PanelColor, {
						// colorValue: attributes.bgColor,
						// initialOpen: false,
						// title: __('Background Color'),
						// },
						// el( wp.components.ColorPalette, {
						// label: __('Background Color'),
						// onChange: function(pass){props.setAttributes({bgColor: pass});},
						// colors: [{ color: '#00d1b2', name: 'teal' }, { color: '#3373dc', name: 'royal blue' }, { color: '#209cef', name: 'sky blue' }, { color: '#22d25f', name: 'green' }, { color: '#ffdd57', name: 'yellow' }, { color: '#ff3860', name: 'pink' }, { color: '#7941b6', name: 'purple' }, { color: '#392F43', name: 'black' }]
						// }
						// )
						// ),
						// el( wp.components.ToggleControl, {
						// label: __('Apply Styles'),
						// checked: !!attributes.apstyle,
						// onChange: function(){
						// if ( attributes.apstyle ){
						// props.setAttributes( { apstyle: '' } );
						// } else {
						// props.setAttributes( { apstyle: 'styled' } );
						// }
						// },
						// }
						// ),
					),

					// el( 'div', { className: props.className+' '+attributes.alignment },
					el(
						'div', { className: props.className + ' ' + attributes.alignment + ' ' + attributes.apstyle },
						// el( 'div', { className: props.className+' '+attributes.alignment , style: { backgroundColor: attributes.bgColor } },
						attributes.embedURL && el(
							'div', {className:'tbimg'} ,
							// '[embed]'+attributes.embedURL+'[/embed]'
							el(
								'div',{className:'placeholderbox'},
								el( 'div',{className:'ttl cf'},__( 'AMP Video/Social Embed' ) ),
								el( 'div',{className:'msg cf'},__( 'Placeholder for AMP embed module using: "' + attributes.embedURL + '", AMP cannot be rendered in thadmin interface.' ) ),
							),
						) ||

						el(
							'div', {
								className: attributes.mediaID ? 'tbimg image-active' : 'tbimg image-inactive',
							},
							el(
								Ed.MediaUpload, {
									onSelect: function( media ) { return props.setAttributes( { mediaURL: media.url, mediaID: media.id, } ); },
									type: 'image',
									value: attributes.mediaID,
									render: function( obj ) {
										return el(
											components.Button, {
												className: attributes.mediaID ? 'image-button' : 'button button-large',
												onClick: obj.open
											},
											! attributes.mediaID ? __( 'Upload Image' ) : el( 'img', { src : attributes.mediaURL } )
										);
									}
									}
							)
						),
						el(
							'div', {
								className: 'tbcon' },
							el(
								Ed.RichText, {
									tagName: 'h3',
									placeholder: 'Title',
									keepPlaceholderOnFocus: true,
									value: attributes.title,
									isSelected: false,
									onChange: function( newTitle ) {
										props.setAttributes( { title: newTitle } );
									},
									}
							),
							el(
								Ed.RichText, {
									tagName: 'h4',
									placeholder: __( 'Subtitle' ),
									keepPlaceholderOnFocus: true,
									value: attributes.subtitle,
									isSelected: false,
									onChange: function( newSubtitle ) {
										props.setAttributes( { subtitle: newSubtitle } );
									},
								}
							),
							el(
								Ed.RichText, {
									tagName: 'p',
									placeholder: __( 'Enter your content...' ),
									keepPlaceholderOnFocus: true,
									value: attributes.cont,
									onChange: function( newCont ) {
										props.setAttributes( { cont: newCont } );
									},
								}
							),
						),
						el( 'div', {className: 'cf' }, '' ),
					)
				];
			},
			save: function( props ) {
				var attributes = props.attributes;
				return (
					el(
						'div', {className: attributes.alignment},
						// el( 'div', {className: attributes.alignment+' '+attributes.apstyle},
						// el( 'div', {className: attributes.alignment , style: { backgroundColor: attributes.bgColor } },
						el(
							'div', { className: 'tbimg' },
							// el( 'img', {src: attributes.mediaURL} ),
							attributes.embedURL && el(
								'div', {className:'wpembed'} ,
								// attributes.embedURL
								'[embed]' + attributes.embedURL + '[/embed]'
								// '[embed]'+attributes.embedURL+'[/embed]'
								// '<!-- wp:core-embed/youtube {"url":"'+attributes.embedURL+'","type":"rich","providerNameSlug":"embed-handler"} --><figure class="wp-block-embed-youtube wp-block-embed is-type-rich is-provider-embed-handler">'+attributes.embedURL+'</figure><!-- /wp:core-embed/youtube -->'
							) || el( 'img', {src: attributes.mediaURL , className: 'wp-image-' + attributes.mediaID } ),
						),
						el(
							'div', { className: 'tbcon' },
							el( 'h3' , {className: 'tbttl'}, attributes.title ),
							el( 'h4' , {className: 'tbsttl'}, attributes.subtitle ),
							el( 'p' , {className: 'tbcont'}, attributes.cont ),
						),
						el( 'div', {className: 'cf' }, '' ),
					)
				);
			},
		}
	);

} )(
	window.wp.blocks,
	window.wp.components,
	window.wp.i18n,
	window.wp.element,
);
