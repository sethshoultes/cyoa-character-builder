(function(blocks, element, components, editor) {
    var el = element.createElement;
    var __ = wp.i18n.__;

    blocks.registerBlockType('cyoa/character-builder', {
        title: __('Character Builder', 'cyoa-character-builder'),
        icon: 'admin-users',
        category: 'iasb-blocks', // Use the existing category slug
        edit: function(props) {
            return el(
                'div',
                { className: props.className },
                __('Character Builder Block', 'cyoa-character-builder')
            );
        },
        save: function() {
            return null; // Render in PHP
        },
    });
    blocks.registerBlockType('cyoa/character-profile', {
        title: __('Character Profile', 'cyoa-character-builder'),
        icon: 'id',
        category: 'iasb-blocks',
        edit: function(props) {
            return el(
                'div',
                { className: props.className },
                __('Character Profile Block', 'cyoa-character-builder')
            );
        },
        save: function() {
            return null; // Render in PHP
        },
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor
);