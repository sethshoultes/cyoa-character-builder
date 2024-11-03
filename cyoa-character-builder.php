<?php
/*
Plugin Name: CYOA Character Builder for CYOA Adventure Game and CYOA Interactive Story Builder
Plugin URI: https://github.com/sethshoultes/cyoa-character-builder
Description: A character builder for CYOA Adventure Game and CYOA Interactive Story Builder. Use [adventure_game_character] to build and manage your character.
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