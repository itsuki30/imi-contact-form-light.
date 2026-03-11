<?php
$content = get_post_meta($post->ID, '_imi_contact_light_content', true);
wp_nonce_field('save_imi_contact_light_data', 'imi_contact_light_nonce');

$buttons = array(
    'text' => 'テキストフィールド',
    'email' => 'メールアドレスフィールド',
    'url' => 'URLフィールド',
    'tel' => '電話番号フィールド',
    'number' => '数値フィールド',
    'date' => '日付フィールド',
    'textarea' => 'テキストエリア',
    'select' => 'ドロップダウンメニュー',
    'checkbox' => 'チェックボックス',
    'radio' => 'ラジオボタン',
    'hidden' => '隠しフィールド',
    'submit' => '送信ボタン',
    'confirm' => '確認ボタン'
);

foreach ($buttons as $type => $label) {
    echo '<button type="button" class="insert-shortcode-button button" data-type="' . esc_attr($type) . '">' . esc_html($label) . '</button> ';
}

echo '<textarea id="imi_contact_light_content" name="imi_contact_light_content" rows="10" style="width:100%;">' . esc_textarea($content) . '</textarea>';
?>

<div id="form-element-modal" style="display:none;">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>フォーム要素設定</h2>
        <form id="form-element-settings">
            <div id="element-options"></div>
            <button type="button" id="add-element">追加</button>
        </form>
    </div>
</div>