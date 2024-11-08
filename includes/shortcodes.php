<?php
/* State Shortcodes */
// Shortcode for displaying state variables
function iasb_state_variable_shortcode($atts) {
    $atts = shortcode_atts(array(
        'name' => '',
        'default' => '',
    ), $atts);

    $user_id = get_current_user_id();
    $story_id = get_the_ID();
    $character_id = 'default_character';
    $state_manager = new CYOA_State_Manager($user_id, $story_id, $character_id);
    
    $state = $state_manager->get_story_state();
    return isset($state['variables'][$atts['name']]) ? esc_html($state['variables'][$atts['name']]) : esc_html($atts['default']);
}
add_shortcode('state_variable', 'iasb_state_variable_shortcode');

// Shortcode for updating state
function iasb_character_attribute_shortcode($atts) {
    $atts = shortcode_atts(array(
        'name' => '',
        'default' => '',
    ), $atts);

    $user_id = get_current_user_id();
    $story_id = get_the_ID();
    $character_id = 'default_character';
    $state_manager = new CYOA_State_Manager($user_id, $story_id, $character_id);
    
    $attributes = $state_manager->get_all_character_attributes();
    return isset($attributes[$atts['name']]) ? esc_html($attributes[$atts['name']]) : esc_html($atts['default']);
}
add_shortcode('character_attribute', 'iasb_character_attribute_shortcode');

function iasb_display_strength_shortcode() {
    $user_id = get_current_user_id();
    $story_id = get_the_ID();
    $character_id = 'default_character';
    $state_manager = new CYOA_State_Manager($user_id, $story_id, $character_id);
    
    $attributes = $state_manager->get_all_character_attributes();
    
    $strength = $attributes['strength'] ?? 0;
    
    return "Your current strength is: " . esc_html($strength);
}
add_shortcode('display_strength', 'iasb_display_strength_shortcode');

/* Debug Shortcodes */
// Shortcode to display debug information about the state
function iasb_debug_state_shortcode($atts) {
    $user_id = get_current_user_id();
    $story_id = get_the_ID();
    $character_id = 'default_character';
    $state_manager = new CYOA_State_Manager($user_id, $story_id, $character_id);

    $state = $state_manager->get_story_state();
    
    $debug_output = "Current State:\n";
    foreach ($state as $key => $value) {
        if (is_array($value)) {
            $debug_output .= "$key: " . print_r($value, true) . "\n";
        } else {
            $debug_output .= "$key: $value\n";
        }
    }

    return '<pre>' . esc_html($debug_output) . '</pre>';
}
add_shortcode('debug_state', 'iasb_debug_state_shortcode');

// Shortcode to update quest progress
function iasb_update_quest_progress_shortcode($atts) {
    $atts = shortcode_atts(array(
        'quest' => '',
        'status' => '',
    ), $atts);

    if (empty($atts['quest']) || empty($atts['status'])) {
        return '';
    }

    $user_id = get_current_user_id();
    $story_id = get_the_ID();
    $character_id = 'default_character'; // Replace with the appropriate character ID
    $state_manager = new CYOA_Quest_Manager($user_id, $story_id);
    
    $state_manager->update_quest_progress($atts['quest'], $atts['status']);

    return ''; // This shortcode doesn't output anything
}
add_shortcode('update_quest_progress', 'iasb_update_quest_progress_shortcode');

// Shortcode to display quest progress
function iasb_display_quest_progress_shortcode($atts) {
    $atts = shortcode_atts(array(
        'quest' => '',
    ), $atts);

    if (empty($atts['quest'])) {
        return '';
    }

    $user_id = get_current_user_id();
    $story_id = get_the_ID();
    $character_id = 'default_character'; // Replace with the appropriate character ID
    $state_manager = new CYOA_State_Manager($user_id, $story_id, $character_id);
    
    $state = $state_manager->get_story_state();
    $quest_progress = $state['quests'][$atts['quest']] ?? 'Not started';

    return esc_html($quest_progress);
}
add_shortcode('display_quest_progress', 'iasb_display_quest_progress_shortcode');

function iasb_quest_progress_condition_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'quest' => '',
        'status' => 'complete',
    ), $atts);

    if (empty($atts['quest'])) {
        return '';
    }

    $user_id = get_current_user_id();
    $story_id = get_the_ID();
    $character_id = 'default_character';
    $state_manager = new CYOA_State_Manager($user_id, $story_id, $character_id);
    
    $state = $state_manager->get_story_state();
    $quest_progress = $state['quests'][$atts['quest']] ?? '';

    if ($quest_progress == $atts['status']) {
        return do_shortcode($content);
    }

    return '';
}
add_shortcode('quest_progress_condition', 'iasb_quest_progress_condition_shortcode');

function iasb_add_to_inventory_shortcode($atts) {
    $atts = shortcode_atts(array(
        'item' => '',
        'quantity' => 1,
    ), $atts);

    if (empty($atts['item'])) {
        return 'Error: No item specified.';
    }

    $user_id = get_current_user_id();
    $story_id = get_the_ID();
    $character_id = 'default_character';
    $state_manager = new CYOA_State_Manager($user_id, $story_id, $character_id);
    
    $state_manager->add_to_global_inventory($atts['item'], $atts['quantity']);
    
    return 'Added ' . $atts['quantity'] . ' ' . $atts['item'] . '(s) to your inventory.';
}
add_shortcode('add_to_inventory', 'iasb_add_to_inventory_shortcode');

function iasb_remove_from_inventory($atts) {
    $atts = shortcode_atts(array(
        'item' => '',
        'quantity' => 1,
    ), $atts);

    if (empty($atts['item'])) {
        return __('Error: No item specified.', 'story-builder');
    }

    $user_id = get_current_user_id();
    $story_id = get_the_ID();
    $transient_name = 'iasb_inventory_removed_' . $user_id . '_' . $story_id . '_' . sanitize_title($atts['item']);

    // Check if the shortcode has already been executed for this item
    if (get_transient($transient_name)) {
        return ''; // Return empty if already executed
    }

    $character_id = 'default_character';
    $state_manager = new CYOA_Inventory_Manager($user_id, $story_id);
    
    // Remove item from inventory
    $removed = $state_manager->remove_from_inventory($atts['item'], $atts['quantity']) !== false;

    if ($removed) {
        // Set the transient to indicate the shortcode has been executed for this item
        set_transient($transient_name, true, 30 * MINUTE_IN_SECONDS); // Expires after 30 minutes

        // Return a message
        return sprintf(__('Removed %d %s from your inventory.', 'story-builder'), $atts['quantity'], $atts['item']);
    } else {
        return sprintf(__('Could not remove %d %s from your inventory. You may not have enough.', 'story-builder'), $atts['quantity'], $atts['item']);
    }
}

add_shortcode('remove_from_inventory', 'iasb_remove_from_inventory');

function iasb_render_inventory_block() {
    $user_id = get_current_user_id();
    $story_id = get_the_ID();
    $state_manager = new CYOA_State_Manager($user_id, $story_id, 'default_character');
    $inventory = $state_manager->get_inventory();

    $output = '<ul class="player-inventory">';
    if (empty($inventory)) {
        $output .= '<li>' . esc_html__('Your inventory is empty.', 'story-builder') . '</li>';
    } else {
        foreach ($inventory as $item_name => $quantity) {
            $output .= '<li>' . esc_html($item_name) . ': ' . esc_html($quantity) . '</li>';
        }
    }
    $output .= '</ul>';
    
    return $output;
}
add_shortcode('display_inventory', 'iasb_render_inventory_block');

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

/* Gutenberg Blocks */
// Register Gutenberg blocks
function iasb_character_builder_register_gutenberg_blocks() {
    // Check if Gutenberg is available
    if (!function_exists('register_block_type')) {
        return;
    }

    // Register Resume Reading block
    register_block_type('iasb/inventory-display', array(
        'editor_script' => 'iasb-inventory-display-editor',
        'editor_style' => 'iasb-inventory-display-editor',
        'render_callback' => 'iasb_render_inventory_block',
    ));

    // State Variable block
    register_block_type('iasb/state-variable', array(
        'attributes' => array(
            'name' => array('type' => 'string'),
        ),
        'render_callback' => 'iasb_state_variable_shortcode',
    ));

    // Update State block
    register_block_type('iasb/update-state', array(
        'attributes' => array(
            'action' => array('type' => 'string'),
            'value' => array('type' => 'string'),
        ),
        'render_callback' => 'iasb_update_state_shortcode',
    ));

    register_block_type('iasb/add-to-inventory', array(
        'attributes' => array(
            'item' => array('type' => 'string'),
            'quantity' => array('type' => 'number', 'default' => 1),
        ),
        'render_callback' => 'iasb_render_add_to_inventory_block',
    ));

    register_block_type('iasb/inventory-display', array(
        'render_callback' => 'iasb_render_inventory_block',
    ));
}
add_action('init', 'iasb_character_builder_register_gutenberg_blocks');