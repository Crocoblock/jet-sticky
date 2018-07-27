( function( $, elementor ) {

	'use strict';

	var JetSticky = {

		init: function() {
			elementor.hooks.addAction( 'frontend/element_ready/column', JetSticky.elementorColumn );
		},

		elementorColumn: function( $scope ) {
			var $target  = $scope,
				$window  = $( window ),
				columnId = $target.data( 'id' ),
				editMode = Boolean( elementor.isEditMode() ),
				settings = {},
				stickyInstance = null,
				stickyInstanceOptions = {
					topSpacing: 50,
					bottomSpacing: 50,
					containerSelector: '.elementor-row',
					innerWrapperSelector: '.elementor-column-wrap'
				};

			if ( ! editMode ) {
				settings = $target.data( 'settings' );

				if ( $target.hasClass( 'jet-sticky-column-sticky' ) ) {

					if ( -1 !== settings['stickyOn'].indexOf( elementorFrontend.getCurrentDeviceMode() ) ) {

						stickyInstanceOptions.topSpacing = settings['topSpacing'];
						stickyInstanceOptions.bottomSpacing = settings['bottomSpacing'];

						$target.data( 'stickyColumnInit', true );
						stickyInstance = new StickySidebar( $target[0], stickyInstanceOptions );

						$window.on( 'resize.JetStickyColumnSticky orientationchange.JetStickyColumnSticky', JetStickyTools.debounce( 50, resizeDebounce ) );
					}
				}
			} else {
				settings = JetSticky.columnEditorSettings( columnId );

				if ( 'true' === settings['sticky'] ) {
					$target.addClass( 'jet-sticky-column-sticky' );

					if ( -1 !== settings['stickyOn'].indexOf( elementorFrontend.getCurrentDeviceMode() ) ) {
						stickyInstanceOptions.topSpacing = settings['topSpacing'];
						stickyInstanceOptions.bottomSpacing = settings['bottomSpacing'];

						$target.data( 'stickyColumnInit', true );
						stickyInstance = new StickySidebar( $target[0], stickyInstanceOptions );

						$window.on( 'resize.JetStickyColumnSticky orientationchange.JetStickyColumnSticky', JetStickyTools.debounce( 50, resizeDebounce ) );
					}
				}
			}

			function resizeDebounce() {
				var currentDeviceMode = elementorFrontend.getCurrentDeviceMode(),
					availableDevices  = settings['stickyOn'] || [],
					isInit            = $target.data( 'stickyColumnInit' );

				if ( -1 !== availableDevices.indexOf( currentDeviceMode ) ) {

					if ( ! isInit ) {
						$target.data( 'stickyColumnInit', true );
						stickyInstance = new StickySidebar( $target[0], stickyInstanceOptions );
						stickyInstance.updateSticky();
					}
				} else {
					$target.data( 'stickyColumnInit', false );
					stickyInstance.destroy();
				}
			}

		},

		columnEditorSettings: function( columnId ) {
			var editorElements = null,
				columnData     = {};

			if ( ! window.elementor.hasOwnProperty( 'elements' ) ) {
				return false;
			}

			editorElements = window.elementor.elements;

			if ( ! editorElements.models ) {
				return false;
			}

			$.each( editorElements.models, function( index, obj ) {

				$.each( obj.attributes.elements.models, function( index, obj ) {
					if ( columnId == obj.id ) {
						columnData = obj.attributes.settings.attributes;
					}
				} );

			} );

			return {
				'sticky': columnData['jet_sticky_column_sticky_enable'] || false,
				'topSpacing': columnData['jet_sticky_column_sticky_top_spacing'] || 50,
				'bottomSpacing': columnData['jet_sticky_column_sticky_bottom_spacing'] || 50,
				'stickyOn': columnData['jet_sticky_column_sticky_enable_on'] || [ 'desktop', 'tablet', 'mobile']
			}
		}
	};

	$( window ).on( 'elementor/frontend/init', JetSticky.init );

	var JetStickyTools = {
		debounce: function( threshold, callback ) {
			var timeout;

			return function debounced( $event ) {
				function delayed() {
					callback.call( this, $event );
					timeout = null;
				}

				if ( timeout ) {
					clearTimeout( timeout );
				}

				timeout = setTimeout( delayed, threshold );
			};
		}
	}

}( jQuery, window.elementorFrontend ) );
