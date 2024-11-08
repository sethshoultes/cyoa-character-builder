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
    public function remove_from_inventory($item, $quantity = 1) {
        $user_state = get_user_meta($this->user_id, 'iasb_user_state', true) ?: array();
        
        if (isset($user_state['global_inventory'][$item])) {
            $user_state['global_inventory'][$item] = max(0, $user_state['global_inventory'][$item] - intval($quantity));
            if ($user_state['global_inventory'][$item] == 0) {
                unset($user_state['global_inventory'][$item]);
            }
            update_user_meta($this->user_id, 'iasb_user_state', $user_state);
        }
    }

    // Update inventory
    public function update_inventory($item, $quantity = 1, $operation = 'add') {
        $user_id = get_current_user_id();
        $inventory = get_user_meta($user_id, 'iasb_inventory', true);
        if (!is_array($inventory)) {
            $inventory = array();
        }

        if ($operation === 'add') {
            $inventory[$item] = ($inventory[$item] ?? 0) + $quantity;
        } elseif ($operation === 'remove') {
            $inventory[$item] = max(0, ($inventory[$item] ?? 0) - $quantity);
            if ($inventory[$item] == 0) {
                unset($inventory[$item]);
            }
        }

        update_user_meta($user_id, 'iasb_inventory', $inventory);
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
