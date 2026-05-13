/**
 * Booking Flow — Edit
 */
( function( wp ) {
    var el = wp.element.createElement;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var TextControl = wp.components.TextControl;
    var TextareaControl = wp.components.TextareaControl;

    wp.blocks.registerBlockType( 'cwc/booking-flow', {
        edit: function( props ) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            return [
                el( InspectorControls, { key: 'inspector' },
                    el( PanelBody, { title: 'Legal Content', initialOpen: true },
                        el( TextControl, {
                            label: 'Terms of Use Title',
                            value: attributes.termsTitle,
                            onChange: function( val ) { setAttributes( { termsTitle: val } ); }
                        } ),
                        el( TextareaControl, {
                            label: 'Terms of Use Content (HTML supported)',
                            value: attributes.termsContent,
                            onChange: function( val ) { setAttributes( { termsContent: val } ); },
                            help: 'Leave empty to use content from the "Terms and Conditions" page.'
                        } ),
                        el( TextControl, {
                            label: 'Privacy Policy Title',
                            value: attributes.privacyTitle,
                            onChange: function( val ) { setAttributes( { privacyTitle: val } ); }
                        } ),
                        el( TextareaControl, {
                            label: 'Privacy Policy Content (HTML supported)',
                            value: attributes.privacyContent,
                            onChange: function( val ) { setAttributes( { privacyContent: val } ); },
                            help: 'Leave empty to use content from the "Privacy Policy" page.'
                        } )
                    )
                ),
                el( 'div', { className: props.className + ' bf-editor-placeholder' },
                    el( 'div', { className: 'bf-editor-info' },
                        el( 'h3', {}, 'Booking Flow Block' ),
                        el( 'p', {}, 'The full booking interface will be visible on the frontend.' ),
                        el( 'p', { style: { fontSize: '12px', opacity: 0.7 } }, 'Edit Legal Content in the sidebar inspector.' )
                    )
                )
            ];
        },
        save: function() {
            return null; // Rendered via PHP
        },
    } );
} )( window.wp );
