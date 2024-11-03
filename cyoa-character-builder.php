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

// Enqueue Styles
function wp_character_builder_enqueue_styles() {
    wp_enqueue_style('character-builder-styles', plugin_dir_url(__FILE__) . 'character-builder.css');
}
add_action('wp_enqueue_scripts', 'wp_character_builder_enqueue_styles');

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
    <div class="character-builder-container">
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