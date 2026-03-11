<?php
// エラーメッセージの表示
$form_html = '';

if (isset($_SESSION['form_errors']) && !empty($_SESSION['form_errors'])) {
    $form_html .= '<div class="form-errors">';
    foreach ($_SESSION['form_errors'] as $error) {
        $form_html .= '<p class="error-message">' . esc_html($error) . '</p>';
    }
    $form_html .= '</div>';
    // エラーメッセージを表示した後にセッションから削除
    unset($_SESSION['form_errors']);
}

// フォームのHTML構造を作成
// トークンの設定
$token = isset($_SESSION['token']) ? $_SESSION['token'] : '';

$form_html .= '<form id="imi-contact-form" method="post" action="" enctype="multipart/form-data">';
$form_html .= '<input type="hidden" name="token" value="' . esc_attr($token) . '">';

// ★ ハニーポット追加 - CSSで非表示にする
$form_html .= '<div style="position: absolute; left: -9999px;" aria-hidden="true">';
$form_html .= '<label for="imi_website">このフィールドは空のままにしてください</label>';
$form_html .= '<input type="text" name="imi_website" id="imi_website" value="" tabindex="-1" autocomplete="off">';
$form_html .= '</div>';

$form_html .= do_shortcode($content);



$form_html .= '<input type="hidden" name="imi_contact_form_id" value="' . esc_attr($form_id) . '">';
$form_html .= '</form>';

// 最終的にフォームを表示
echo $form_html;

?>