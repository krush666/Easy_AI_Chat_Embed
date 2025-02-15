<?php
/**
 * Plugin Name: Easy AI Chat Embed
 * Description: A easy to use plugin to embed an AI chatbot powered by Google Gemini.
 * Version: 1.0.1
 * Author: <a href="https://rankboost.pro/easy-ai-chat-embed-pro/">Rank Boost Pro</a>
 * Text Domain: easy-ai-chat-embed
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Include the core plugin class.
require_once plugin_dir_path(__FILE__) . 'includes/class-plugin-name.php';

// Run the plugin.
function run_easy_ai_chat_embed() {
    $plugin = new Easy_AI_Chat_Embed();
    $plugin->run();
}
run_easy_ai_chat_embed();
?>