<?php
class Easy_AI_Chat_Embed {
    public function run() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('easy_ai_chat', array($this, 'render_chatbot'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('rest_api_init', array($this, 'register_rest_api'));
    }

    /**
     * Enqueue the JavaScript that will render the chatbot.
     */
    public function enqueue_scripts() {
        // Check if the API key is set before enqueuing the script.
        $settings = get_option('easy_ai_chat_settings');
        if (empty($settings) || empty($settings['api_key'])) {
            return;
        }

        // Ensure the script path is correct.
        wp_enqueue_script('easy-ai-chat', plugins_url('assets/js/chatbot-embed.js', dirname(__FILE__)), array(), '1.0.0', true);

        // Localize script to pass PHP variables to JavaScript.
        wp_localize_script('easy-ai-chat', 'easyAIChatSettings', array(
            'plugin_url' => plugins_url('/', dirname(__FILE__)),
            'button_color' => $settings['button_color'] ?? '#238a9d',
            'api_key' => $settings['api_key'] ?? '',
            'terms_checkbox_enabled' => $settings['terms_checkbox_enabled'] ?? 1,
            'terms_text' => $settings['terms_text'] ?? 'I acknowledge that I have read and understood the disclaimer above. I understand that this chatbot provides general information only and not legal advice. I agree to the terms and conditions.'
        ));
    }

    /**
     * Render the chatbot by including the aiscript.html template.
     *
     * @return string The rendered HTML of the chatbot.
     */
    public function render_chatbot() {
        $file_path = plugin_dir_path(dirname(__FILE__)) . 'assets/js/aiscript.html';
        if (!file_exists($file_path)) {
            return '<p>Chatbot template not found. Path: ' . esc_html($file_path) . '</p>';
        }
        ob_start();
        include $file_path;
        return ob_get_clean();
    }

    public function add_admin_menu() {
        add_options_page(
            'Easy AI Chat Settings',
            'Easy AI Chat',
            'manage_options',
            'easy_ai_chat',
            array($this, 'settings_page')
        );
    }

    public function settings_init() {
        register_setting('easyAIChat', 'easy_ai_chat_settings', array($this, 'sanitize_settings'));

        add_settings_section(
            'easy_ai_chat_section',
            __('Chatbot Settings', 'easy-ai-chat-embed'),
            null,
            'easyAIChat'
        );

        add_settings_field(
            'button_color',
            __('Button Color', 'easy-ai-chat-embed'),
            array($this, 'render_color_field'),
            'easyAIChat',
            'easy_ai_chat_section',
            array(
                'label_for' => 'button_color',
                'class' => 'easy_ai_chat_row',
                'easy_ai_chat_custom_data' => 'custom',
            )
        );

        add_settings_field(
            'api_key',
            __('Google Gemini API Key', 'easy-ai-chat-embed'),
            array($this, 'render_api_key_field'),
            'easyAIChat',
            'easy_ai_chat_section',
            array(
                'label_for' => 'api_key',
                'class' => 'easy_ai_chat_row',
                'easy_ai_chat_custom_data' => 'custom',
            )
        );

    }

    public function render_color_field($args) {
        $options = get_option('easy_ai_chat_settings');
        ?>
        <input type="color" id="<?php echo esc_attr($args['label_for']); ?>" name="easy_ai_chat_settings[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($options[$args['label_for']] ?? ($args['label_for'] == 'button_color' ? '#238a9d' : '')); ?>" class="regular-text">
        <?php
    }

    public function render_api_key_field($args) {
        $options = get_option('easy_ai_chat_settings');
        ?>
        <input type="text" id="<?php echo esc_attr($args['label_for']); ?>" name="easy_ai_chat_settings[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($options[$args['label_for']] ?? ''); ?>" class="regular-text">
        <a href="https://aistudio.google.com/app/apikey" target="_blank">Get API Key</a>
        <?php
    }

    public function render_terms_checkbox_enabled_field($args) {
        $options = get_option('easy_ai_chat_settings');
        ?>
        <input type="checkbox" id="<?php echo esc_attr($args['label_for']); ?>" name="easy_ai_chat_settings[<?php echo esc_attr($args['label_for']); ?>]" value="1" <?php checked(1, $options[$args['label_for']] ?? 0); ?>>
        <?php
    }

    public function render_terms_text_field($args) {
        $options = get_option('easy_ai_chat_settings');
        ?>
        <textarea id="<?php echo esc_attr($args['label_for']); ?>" name="easy_ai_chat_settings[<?php echo esc_attr($args['label_for']); ?>]" class="large-text"><?php echo esc_textarea($options[$args['label_for']] ?? 'I acknowledge that I have read and understood the disclaimer above. I understand that this chatbot provides general information only and not legal advice. I agree to the terms and conditions.'); ?></textarea>
        <?php
    }

    public function settings_page() {
        ?>
        <div class="notice notice-info">
            <p><?php _e('Use the following shortcode to embed the chat: [easy_ai_chat]', 'easy-ai-chat-embed'); ?></p>
        </div>
        <form action="options.php" method="post">
            <?php
            settings_fields('easyAIChat');
            do_settings_sections('easyAIChat');
            submit_button('Save Settings');
            ?>
        </form>
        <a href="https://rankboost.pro/easy-ai-chat-embed-pro/" target="_blank" class="button button-primary">
            <?php _e('Remove Easy AI Chat Embed branding - GO PRO', 'easy-ai-chat-embed'); ?>
        </a>
        <?php
    }

    /**
     * Sanitize settings before saving to the database.
     *
     * @param array $input The unsanitized input.
     * @return array The sanitized input.
     */
    public function sanitize_settings($input) {
        $sanitized_input = array();
        if (isset($input['api_key'])) {
            $sanitized_input['api_key'] = sanitize_text_field($input['api_key']);
        }
        if (isset($input['button_color'])) {
            $sanitized_input['button_color'] = sanitize_hex_color($input['button_color']);
        }
        if (isset($input['terms_checkbox_enabled'])) {
            $sanitized_input['terms_checkbox_enabled'] = intval($input['terms_checkbox_enabled']);
        }
        if (isset($input['terms_text'])) {
            $sanitized_input['terms_text'] = sanitize_textarea_field($input['terms_text']);
        }
        return $sanitized_input;
    }

    public function register_rest_api() {
        register_rest_route('gemini-chat/v1', '/query', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_chat_query'),
            'permission_callback' => '__return_true',
        ));
    }

    public function handle_chat_query(WP_REST_Request $request) {
        // Get your API key from WordPress options
        $api_key = get_option('easy_ai_chat_settings')['api_key'];
        
        // Get the user's message from the request
        $parameters = $request->get_json_params();
        $user_message = sanitize_text_field($parameters['message']);
        
        // Optional: Add rate limiting based on IP address
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $rate_limit_key = 'gemini_rate_limit_' . $ip_address;
        $current_count = get_transient($rate_limit_key) ?: 0;
        
        if ($current_count > 50) { // 50 requests per hour
            return new WP_Error('rate_limit_exceeded', 'Rate limit exceeded. Please try again later.', array('status' => 429));
        }
        
        set_transient($rate_limit_key, $current_count + 1, HOUR_IN_SECONDS);
        
        // Prepare the request to Gemini API
        $response = wp_remote_post('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $api_key, array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'contents' => array(
                    array(
                        'parts' => array(
                            array(
                                'text' => $user_message
                            )
                        )
                    )
                )
            ))
        ));
        
        if (is_wp_error($response)) {
            return new WP_Error('api_error', 'Failed to connect to Gemini API', array('status' => 500));
        }
        
        $body = wp_remote_retrieve_body($response);
        return json_decode($body);
    }
}
