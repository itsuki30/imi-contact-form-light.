<?php
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : array();
$confirmation_template = get_post_meta($form_id, '_imi_contact_light_confirmation', true);

// プレースホルダーのパターンをすべて検索
preg_match_all('/\[([^\s\]]+)(?:\s+([^\]]+))?\]/', $confirmation_template, $matches);

// データをプレースホルダーに埋め込む
if (!empty($form_data)) {
    foreach ($matches[1] as $index => $key) {
        if (isset($form_data[$key]) && is_array($form_data[$key])) {
            // チェックボックスや複数選択肢の場合、カンマ区切りで表示
            $checkbox_values = implode(', ', array_map('esc_html', $form_data[$key]));
            $confirmation_template = str_replace('[' . $key . ']', $checkbox_values, $confirmation_template);
        } elseif (isset($form_data[$key]) && !empty($form_data[$key])) {
            // 単一の値を表示
            $confirmation_template = str_replace('[' . $key . ']', nl2br(esc_html($form_data[$key])), $confirmation_template);
        } else {
            // データがない場合はその行を削除
            $confirmation_template = preg_replace('/.*\[' . $key . '\].*\n?/', '', $confirmation_template);
        }
    }

    $token = isset($_SESSION['token']) ? $_SESSION['token'] : '';

    // 確認画面に表示
    $form_html = '<form id="imi-contact-form" method="post" action="" enctype="multipart/form-data">';
    $form_html .= '<input type="hidden" name="token" value="' . esc_attr($token) . '">';
    // 隠しフィールドを追加
    $form_html .= '<input type="hidden" name="imi_contact_form_id" value="' . esc_attr($form_id) . '">';
    $form_html .= '<input type="hidden" name="my_plugin_confirm" value="true">';

    // プレースホルダーをボタンに置き換える
    foreach ($matches[1] as $index => $type) {
        if ($type === 'return') {
            // プレースホルダーからボタンのラベルを取得
            $button_label = isset($matches[2][$index]) ? $matches[2][$index] : '戻る'; // デフォルトラベル
            $button_html = '<input type="button" value="' . esc_attr($button_label) . '" onclick="history.back()" style="margin-right: 10px;">';
            // プレースホルダーをボタンに置き換え
            $confirmation_template = str_replace($matches[0][$index], $button_html, $confirmation_template);
        } elseif ($type === 'submit') {
            // プレースホルダーからボタンのラベルを取得
            $button_label = isset($matches[2][$index]) ? $matches[2][$index] : '送信'; // デフォルトラベル
            $button_html = '<input type="submit" name="imi_contact_light_submit" value="' . esc_attr($button_label) . '" id="confirm-submit">';
            // プレースホルダーをボタンに置き換え
            $confirmation_template = str_replace($matches[0][$index], $button_html, $confirmation_template);
        }
    }

    // 更新されたテンプレートを表示
    $form_html .= trim($confirmation_template); // trimを使用して余分な空白を削除

    $form_html .= '</form>';
} else {
    $form_html = '<p>フォームの入力がありません。</p>';
}

echo $form_html;
?>