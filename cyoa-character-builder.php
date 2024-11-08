<?php
/*
Plugin Name: CYOA Character Builder
Plugin URI: https://github.com/sethshoultes/cyoa-character-builder
Description: A character builder for CYOA Adventure Game and CYOA Interactive Story Builder. Use [cyoa_character_builder] to build and manage your character.
Version: 1.0.0
Author: Seth Shoultes
Author URI: https://adventurebuildr.com/
License: GPL2
*/
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Initialize the plugin update checker.
 */
function wp_cyoa_character_builder_plugin_auto_update() {
    // Include the library if it's not already included
    if ( ! class_exists( '\\YahnisElsts\\PluginUpdateChecker\\PluginUpdateChecker' ) ) {
        require_once plugin_dir_path( __FILE__ ) . 'includes/plugin-update-checker/plugin-update-checker.php';
    }

    // Replace these variables with your own repository details
    $github_username   = 'sethshoultes';
    $github_repository = 'cyoa-character-builder';
    $plugin_slug       = 'cyoa-character-builder'; // This should match the plugin's folder name

    // Initialize the update checker
    $updateChecker = PucFactory::buildUpdateChecker(
        "https://github.com/{$github_username}/{$github_repository}/",
        __FILE__,
        $plugin_slug
    );

    /*
     * Create a new release using the "Releases" feature on GitHub. The tag name and release title don't matter. 
     * The description is optional, but if you do provide one, it will be displayed when the user clicks the 
     * "View version x.y.z details" link on the "Plugins" page. Note that PUC ignores releases marked as 
     * "This is a pre-release".
     *
     * If you want to use release assets, call the enableReleaseAssets() method after creating the update checker instance:
     */
    //$updateChecker->getVcsApi()->enableReleaseAssets();

    // Optional: Set the branch that contains the stable release
    $updateChecker->setBranch('main'); // Change 'main' to the branch you use

    // Optional: If your repository is private, add your access token
    // $updateChecker->setAuthentication('your_github_access_token');
}
add_action( 'init', 'wp_cyoa_character_builder_plugin_auto_update' );

require_once plugin_dir_path(__FILE__) . 'includes/class-character-profile.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-inventory-manager.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-state-manager.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-quest-manager.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';

// Enqueue Styles
function wp_character_builder_enqueue_styles() {
    wp_enqueue_style('character-builder-styles', plugin_dir_url(__FILE__) . 'character-builder.css');
    wp_enqueue_style('wp-character-builder-block', plugin_dir_url(__FILE__) . 'character-builder.css');
}
add_action('wp_enqueue_scripts', 'wp_character_builder_enqueue_styles');
add_action('enqueue_block_editor_assets', 'wp_character_builder_enqueue_styles');

// Process the form submission to save the character data
function wp_character_builder_process_character_form() {
    if (!is_user_logged_in() || !isset($_POST['adventure_game_action'])) {
        return;
    }

    $user_id = get_current_user_id();
    $redirect_url = remove_query_arg('message', wp_get_referer());

    if ($_POST['adventure_game_action'] === 'save_character') {
        if (!wp_verify_nonce($_POST['adventure_game_nonce'], 'adventure_game_action')) {
            wp_safe_redirect(add_query_arg('message', 'invalid_nonce', $redirect_url));
            exit;
        }

        $character_name = sanitize_text_field($_POST['character_name']);
        $character_race = sanitize_text_field($_POST['character_race']);
        $character_class = sanitize_text_field($_POST['character_class']);
        $attributes = [
            'Strength' => intval($_POST['strength']),
            'Intelligence' => intval($_POST['intelligence']),
            'Dexterity' => intval($_POST['dexterity']),
            'Luck' => intval($_POST['luck']),
        ];
        $skills = isset($_POST['skills']) ? array_map('sanitize_text_field', $_POST['skills']) : [];
        $backstory = sanitize_textarea_field($_POST['backstory']);

        if (empty($character_name) || empty($character_race) || empty($character_class)) {
            wp_safe_redirect(add_query_arg('message', 'missing_fields', $redirect_url));
            exit;
        }

        $character_data = [
            'Name' => $character_name,
            'Race' => $character_race,
            'Class' => $character_class,
            'Attributes' => $attributes,
            'Skills' => $skills,
            'Backstory' => $backstory,
        ];
        update_user_meta($user_id, 'adventure_game_character', $character_data);

        wp_safe_redirect(add_query_arg('message', 'character_saved', $redirect_url));
        exit;
    }

    if ($_POST['adventure_game_action'] === 'reset_character') {
        delete_user_meta($user_id, 'adventure_game_character');
        wp_safe_redirect(add_query_arg('message', 'character_reset', $redirect_url));
        exit;
    }
}
add_action('init', 'wp_character_builder_process_character_form');

// Add Shortcode for Adventure Game Character Builder
function wp_character_builder_character_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to manage your character.</p>';
    }

    $user_id = get_current_user_id();
    $existing_character = get_user_meta($user_id, 'adventure_game_character', true);
    $message = '';

    if (isset($_GET['message'])) {
        switch ($_GET['message']) {
            case 'character_saved':
                $message = '<p class="success">Character saved successfully.</p>';
                break;
            case 'character_reset':
                $message = '<p class="success">Character has been reset.</p>';
                break;
            case 'missing_fields':
                $message = '<p class="error">Please fill in all required fields.</p>';
                break;
            case 'invalid_nonce':
                $message = '<p class="error">Invalid form submission.</p>';
                break;
        }
    }

    ob_start();
    ?>
    <div class="character-builder-container wp-block-cyoa-character-builder">
        <?php echo $message; ?>
        <h2><?php echo $existing_character ? 'Edit Your Character' : 'Create Your Character'; ?></h2>
        <form method="POST" id="character-builder-form">
            <?php wp_nonce_field('adventure_game_action', 'adventure_game_nonce'); ?>
            <input type="hidden" name="adventure_game_action" value="save_character">

            <label for="character_name">Name:</label>
            <input type="text" id="character_name" name="character_name" value="<?php echo esc_attr($existing_character['Name'] ?? ''); ?>" required />

     
            <label for="character_race">Race:</label>
            <select id="character_race" name="character_race" required>
                <option value="">Select Race</option>
                <option value="Human" <?php selected($existing_character['Race'] ?? '', 'Human'); ?>>Human</option>
                <option value="Elf" <?php selected($existing_character['Race'] ?? '', 'Elf'); ?>>Elf</option>
                <option value="Dwarf" <?php selected($existing_character['Race'] ?? '', 'Dwarf'); ?>>Dwarf</option>
                <option value="Orc" <?php selected($existing_character['Race'] ?? '', 'Orc'); ?>>Orc</option>
            </select>

            <label for="character_class">Class:</label>
            <select id="character_class" name="character_class" required>
                <option value="">Select Class</option>
                <option value="Warrior" <?php selected($existing_character['Class'] ?? '', 'Warrior'); ?>>Warrior</option>
                <option value="Mage" <?php selected($existing_character['Class'] ?? '', 'Mage'); ?>>Mage</option>
                <option value="Rogue" <?php selected($existing_character['Class'] ?? '', 'Rogue'); ?>>Rogue</option>
                <option value="Cleric" <?php selected($existing_character['Class'] ?? '', 'Cleric'); ?>>Cleric</option>
            </select>

            <fieldset>
                <legend>Attributes</legend>
                <label for="strength">Strength:</label>
                <input type="number" id="strength" name="strength" min="1" max="20" value="<?php echo esc_attr($existing_character['Attributes']['Strength'] ?? 10); ?>" required />

                <label for="intelligence">Intelligence:</label>
                <input type="number" id="intelligence" name="intelligence" min="1" max="20" value="<?php echo esc_attr($existing_character['Attributes']['Intelligence'] ?? 10); ?>" required />

                <label for="dexterity">Dexterity:</label>
                <input type="number" id="dexterity" name="dexterity" min="1" max="20" value="<?php echo esc_attr($existing_character['Attributes']['Dexterity'] ?? 10); ?>" required />

                <label for="luck">Luck:</label>
                <input type="number" id="luck" name="luck" min="1" max="20" value="<?php echo esc_attr($existing_character['Attributes']['Luck'] ?? 10); ?>" required />
            </fieldset>

            <fieldset>
                <legend>Skills</legend>
                <label><input type="checkbox" name="skills[]" value="Persuasion" <?php if (in_array('Persuasion', $existing_character['Skills'] ?? [])) echo 'checked'; ?> /> Persuasion</label>
                <label><input type="checkbox" name="skills[]" value="Archery" <?php if (in_array('Archery', $existing_character['Skills'] ?? [])) echo 'checked'; ?> /> Archery</label>
                <label><input type="checkbox" name="skills[]" value="Stealth" <?php if (in_array('Stealth', $existing_character['Skills'] ?? [])) echo 'checked'; ?> /> Stealth</label>
                <label><input type="checkbox" name="skills[]" value="Alchemy" <?php if (in_array('Alchemy', $existing_character['Skills'] ?? [])) echo 'checked'; ?> /> Alchemy</label>
            </fieldset>

            <label for="backstory">Backstory:</label>
            <textarea id="backstory" name="backstory" rows="5" placeholder="Tell us about your character's history..."><?php echo esc_textarea($existing_character['Backstory'] ?? ''); ?></textarea>

            <input type="submit" value="Save Character" class="save-changes-button" />
        </form>

        <?php if ($existing_character): ?>
            <form method="POST" style="margin-top: 10px;">
                <input type="hidden" name="adventure_game_action" value="reset_character">
                <?php wp_nonce_field('adventure_game_action', 'adventure_game_nonce'); ?>
                <input type="submit" value="Reset Character" class="reset-character-button" />
            </form>
        <?php endif; ?>
    </div>
    <?php

    
    return ob_get_clean();
}
add_shortcode('cyoa_character_builder', 'wp_character_builder_character_shortcode');

function wp_character_builder_register_block() {
    if (!function_exists('register_block_type')) {
        return;
    }
    wp_register_script(
        'wp-character-builder-block-editor',
        plugins_url('block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'block.js')
    );
    register_block_type('cyoa/character-builder', array(
        'editor_script' => 'wp-character-builder-block-editor',
        'editor_style'  => 'wp-character-builder-block-editor',
        'style'         => 'wp-character-builder-block',
        'render_callback' => 'wp_character_builder_character_shortcode'
    ));
    register_block_type('cyoa/character-profile', array(
        'editor_script' => 'wp-character-builder-block-editor',
        'editor_style'  => 'wp-character-builder-block-editor',
        'style'         => 'wp-character-builder-block',
        'render_callback' => 'wp_character_builder_display_profile_shortcode'
    ));
}
add_action('init', 'wp_character_builder_register_block');

function wp_character_builder_enqueue_block_editor_assets() {
    wp_enqueue_script(
        'wp-character-builder-block-editor',
        plugins_url( 'block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element' )
    );
}
add_action( 'enqueue_block_editor_assets', 'wp_character_builder_enqueue_block_editor_assets' );

// Add Shortcode to Display Character Profile
function wp_character_builder_display_profile_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to view your character profile.</p>';
    }

    $user_id = get_current_user_id();
    $character = get_user_meta($user_id, 'adventure_game_character', true);

    if (!$character) {
        return '<p>No character profile found. Please create your character first.</p>';
    }

    // Get the associated story ID (you may need to adjust this based on how you're storing this information)
    $story_id = get_user_meta($user_id, 'associated_story', true);
    $quests = maybe_unserialize(get_user_meta($user_id, 'quest_progress', true));
    // Initialize the State Manager
    if (class_exists('IASB_State_Manager')) {
        $state_manager = new IASB_State_Manager($user_id, $story_id, 'default_character');
        $user_id = get_current_user_id();
        $story_id = get_the_ID();
        $inventory = $state_manager->get_inventory();
    } else {
        $inventory = [];
    }

    ob_start();
    ?>
    <div class="character-profile-container">
        <h2>Character Profile</h2>
        <p><strong>Name:</strong> <?php echo esc_html($character['Name']); ?></p>
        <p><strong>Race:</strong> <?php echo esc_html($character['Race']); ?></p>
        <p><strong>Class:</strong> <?php echo esc_html($character['Class']); ?></p>
        
        <h3>Attributes</h3>
        <ul>
            <?php foreach ($character['Attributes'] as $attribute => $value): ?>
                <li><strong><?php echo esc_html($attribute); ?>:</strong> <?php echo esc_html($value); ?></li>
            <?php endforeach; ?>
        </ul>
        
        <h3>Skills</h3>
        <ul>
            <?php foreach ($character['Skills'] as $skill): ?>
                <li><?php echo esc_html($skill); ?></li>
            <?php endforeach; ?>
        </ul>
        
        <h3>Inventory</h3>
        <?php 
        if (!empty($inventory)): ?>
            <ul>
            <?php foreach ($inventory as $item => $quantity): ?>
                <li><?php echo esc_html($item); ?>: <?php echo esc_html($quantity); ?></li>
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Your inventory is empty.</p>
        <?php endif; ?>
        <h3>Quests</h3>
        <?php 
       
        if (!empty($quests)): ?>
            <ul>
                <?php foreach ($quests as $quest => $progress): ?>
                    <li><?php echo esc_html($quest); ?>: <?php echo esc_html($progress); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>You have no active quests.</p>
        <?php endif; ?>
        
        <h3>Backstory</h3>
        <p><?php echo nl2br(esc_html($character['Backstory'])); ?></p>
    </div>
    <?php
    do_action('iasb_character_profile');
    $output = ob_get_clean();

    // Wrap the output in a div with a class for styling
    return '<div class="wp-block-cyoa-character-profile">' . $output . '</div>';
}
add_shortcode('cyoa_character_profile', 'wp_character_builder_display_profile_shortcode');


add_action('rest_api_init', function () {
    register_rest_route('iasb/v1', '/inventory', array(
        'methods' => 'GET',
        'callback' => 'iasb_get_inventory',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        }
    ));
});

function iasb_get_inventory() {
    $user_id = get_current_user_id();
    $state_manager = new IASB_State_Manager($user_id, get_the_ID(), 'default_character');
    $inventory = $state_manager->get_inventory();
    
    // Convert associative array to array of objects
    $formatted_inventory = array_map(function($name, $quantity) {
        return array('name' => $name, 'quantity' => $quantity);
    }, array_keys($inventory), $inventory);
    
    return $formatted_inventory;
}