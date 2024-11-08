(function(blocks, element, components, editor) {
    var el = element.createElement;
    var __ = wp.i18n.__;
    var RichText = editor.RichText;
    var InspectorControls = editor.InspectorControls;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var ToggleControl = components.ToggleControl;

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

 // Inventory Display block
    blocks.registerBlockType('iasb/inventory-display', {
        title: __('Player Inventory', 'story-builder'),
        icon: 'list-view',
        category: 'iasb-blocks',
        edit: function(props) {
            const [inventory, setInventory] = wp.element.useState([]);
            const [error, setError] = wp.element.useState(null);
        
            wp.element.useEffect(() => {
                wp.apiFetch({
                    path: '/iasb/v1/inventory',
                }).then(result => {
                    console.log('API response:', result);  // Log the entire response
                    if (Array.isArray(result)) {
                        setInventory(result);
                    } else {
                        setError('Invalid inventory data received');
                        console.error('Invalid inventory data:', result);
                    }
                }).catch(error => {
                    setError('Error fetching inventory');
                    console.error('Error fetching inventory:', error);
                });
            }, []);
        
            if (error) {
                return el('div', {className: props.className},
                    el('p', {}, __('Error: ', 'story-builder') + error)
                );
            }
        
            return el('div', {className: props.className},
                el('h3', {}, __('Player Inventory', 'story-builder')),
                inventory.length === 0 
                    ? el('p', {}, __('Loading inventory...', 'story-builder'))
                    : el('ul', {},
                        inventory.map(item => el('li', {key: item.name}, item.name + ': ' + item.quantity))
                      )
            );
        },
        save: function() {
            return null; // Dynamic block, render handled by PHP
        }
    });

     // Inventory Display block
     blocks.registerBlockType('iasb/inventory-display', {
        title: __('Player Inventory', 'story-builder'),
        icon: 'list-view',
        category: 'iasb-blocks',
        edit: function(props) {
            const [inventory, setInventory] = wp.element.useState([]);
            const [error, setError] = wp.element.useState(null);
        
            wp.element.useEffect(() => {
                wp.apiFetch({
                    path: '/iasb/v1/inventory',
                }).then(result => {
                    console.log('API response:', result);  // Log the entire response
                    if (Array.isArray(result)) {
                        setInventory(result);
                    } else {
                        setError('Invalid inventory data received');
                        console.error('Invalid inventory data:', result);
                    }
                }).catch(error => {
                    setError('Error fetching inventory');
                    console.error('Error fetching inventory:', error);
                });
            }, []);
        
            if (error) {
                return el('div', {className: props.className},
                    el('p', {}, __('Error: ', 'story-builder') + error)
                );
            }
        
            return el('div', {className: props.className},
                el('h3', {}, __('Player Inventory', 'story-builder')),
                inventory.length === 0 
                    ? el('p', {}, __('Loading inventory...', 'story-builder'))
                    : el('ul', {},
                        inventory.map(item => el('li', {key: item.name}, item.name + ': ' + item.quantity))
                      )
            );
        },
        save: function() {
            return null; // Dynamic block, render handled by PHP
        }
    });


})(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor
);