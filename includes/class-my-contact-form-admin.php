<?php
class My_Contact_Form_Admin {

    public function __construct() {
        // メタボックス追加
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        // フォームの保存
        add_action('save_post', array($this, 'save_form_data'));
    }

    // メタボックスの追加
    public function add_meta_boxes() {
        add_meta_box('imi_contact_light_meta_box', 'フォーム設定', array($this, 'render_meta_box'), 'imi_contact_light', 'normal', 'high');
    }

    // メタボックスのレンダリング
    public function render_meta_box($post) {
        $form_html = get_post_meta($post->ID, '_imi_contact_light_html', true);
        echo '<label for="imi_contact_light_html">フォームHTML:</label>';
        echo '<textarea id="imi_contact_light_html" name="imi_contact_light_html" rows="10" cols="50" class="widefat">' . esc_textarea($form_html) . '</textarea>';
    }

    // フォームのデータを保存
    public function save_form_data($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (isset($_POST['imi_contact_light_html'])) {
            update_post_meta($post_id, '_imi_contact_light_html', sanitize_text_field($_POST['imi_contact_light_html']));
        }
    }
}

new My_Contact_Form_Admin();
