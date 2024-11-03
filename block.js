( function( blocks, element ) {
    var el = element.createElement;

    blocks.registerBlockType( 'cyoa/character-builder', {
        title: 'Character Builder',
        icon: 'admin-users',
        category: 'widgets',
        edit: function() {
            return el(
                'div',
                { className: 'wp-block-cyoa-character-builder' },
                'Character Builder will be displayed here.'
            );
        },
        save: function() {
            return null; // Render in PHP
        },
    } );
} )( window.wp.blocks, window.wp.element );