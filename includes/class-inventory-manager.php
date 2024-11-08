<?php
// In class-inventory-manager.php
class CYOA_Inventory_Manager {
    private $user_id;
    private $story_id;

    public function __construct($user_id, $story_id) {
        $this->user_id = $user_id;
        $this->story_id = $story_id;
        
    }

    public function add_item($item_id, $quantity = 1) {
        // Implementation for adding an item to the inventory
    }

    public function remove_item($item_id, $quantity = 1) {
        // Implementation for removing an item from the inventory
    }

    public function get_inventory() {
        // Implementation for retrieving the entire inventory
    }

    public function display_inventory() {
        // Implementation for displaying the inventory
    }

    public function add_to_global_inventory($item, $quantity = 1) {
        $user_state = get_user_meta($this->user_id, 'iasb_user_state', true);
        
        if (!is_array($user_state)) {
            $user_state = array();
        }
        
        if (!isset($user_state['global_inventory'])) {
            $user_state['global_inventory'] = array();
        }
        
        if (!isset($user_state['global_inventory'][$item])) {
            $user_state['global_inventory'][$item] = 0;
        }
        
        $user_state['global_inventory'][$item] += $quantity;
        
        update_user_meta($this->user_id, 'iasb_user_state', $user_state);
    }

}

/**
 * Adds the given item to the current user's inventory, with the given quantity.
 * 
 * @param int $quantity The number of items to add. Defaults to 1.
 * 
 * @return string Message indicating the result of the operation.
 */
function iasb_add_to_inventory($atts) {
    $atts = shortcode_atts(array(
        'item' => '',
        'quantity' => 1,
    ), $atts);

    if (empty($atts['item'])) {
        return __('Error: No item specified.', 'story-builder');
    }

    $user_id = get_current_user_id();
    $story_id = get_the_ID();
    $transient_name = 'iasb_inventory_added_' . $user_id . '_' . $story_id . '_' . sanitize_title($atts['item']);

    // Check if the shortcode has already been executed for this item
    if (get_transient($transient_name)) {
        return ''; // Return empty if already executed
    }

    $character_id = 'default_character';
    $state_manager = new CYOA_State_Manager($user_id, $story_id, $character_id);
    
    // Add item to inventory
    $state_manager->add_to_global_inventory($atts['item'], $atts['quantity']);

    // Set the transient to indicate the shortcode has been executed for this item
    set_transient($transient_name, true, 30 * MINUTE_IN_SECONDS); // Expires after 30 minutes

    // Return a message
    return sprintf(__('Added %d %s to your inventory.', 'story-builder'), $atts['quantity'], $atts['item']);
}
add_shortcode('add_to_inventory', 'iasb_add_to_inventory');

function iasb_render_add_to_inventory_block($attributes) {
    $item = $attributes['item'] ?? '';
    $quantity = $attributes['quantity'] ?? 1;
    
    if (empty($item)) {
        return 'Error: No item specified.';
    }

    $user_id = get_current_user_id();
    $story_id = get_the_ID();
    $character_id = 'default_character'; // Replace with the appropriate character ID
    $state_manager = new CYOA_State_Manager($user_id, $story_id, $character_id);
    
    $state_manager->update_inventory($item, $quantity, 'add');
    
    return 'Added ' . $quantity . ' ' . $item . '(s) to your inventory.';
}