<?php
class My_Contact_Form_Shortcode {

    public function __construct() {
        add_shortcode('imi_contact_form', array($this, 'render_form'));
    }

    // ショートコードによるフォームの表示
    public function render_form($atts) {
        $atts = shortcode_atts(array('id' => ''), $atts);
        $form_id = intval($atts['id']);
        if (!$form_id) return '';

        // フォームHTMLの取得
        $form_html = get_post_meta($form_id, '_imi_contact_light_html', true);
        if ($form_html) {
            return $form_html . $this->get_submit_button();
        }
        return '';
    }

    // 送信ボタンの追加
    private function get_submit_button() {
        return '<button type="submit">送信</button>';
    }
}

new My_Contact_Form_Shortcode();
