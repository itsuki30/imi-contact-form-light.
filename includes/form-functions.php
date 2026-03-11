<?php

// フォームの表示
function imi_contact_form_display() {
    ?>
    <div id="imi-contact-form" style="background-color: #f9f9f9; padding: 20px; margin: 20px 0; border: 1px solid #ddd;">
        <h2>お問い合わせフォーム</h2>
        <form action="" method="post">
            <label for="name">名前</label>
            <input type="text" name="name" placeholder="名前" required>

            <label for="email">メールアドレス</label>
            <input type="email" name="email" placeholder="メールアドレス" required>

            <label for="message">メッセージ</label>
            <textarea name="message" placeholder="メッセージを入力" required></textarea>

            <button type="submit">送信</button>
        </form>
    </div>
    <?php
}

// フォームの送信処理
add_action('init', 'imi_contact_form_process');

function imi_contact_form_process() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'], $_POST['message'])) {
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $message = sanitize_textarea_field($_POST['message']);

        // メール送信処理
        $to = '指定のメールアドレス@example.com';  // 管理者のメールアドレス
        $subject = '新しいお問い合わせ';
        $body = "名前: $name\nメール: $email\nメッセージ:\n$message";
        $headers = array('Content-Type: text/plain; charset=UTF-8');

        if (wp_mail($to, $subject, $body, $headers)) {
            echo '<p>お問い合わせありがとうございます。</p>';
            // メールが送信されたら、フォームを表示しないようにする
            update_option('imi_contact_form_email_sent', true);
        } else {
            echo '<p>送信に失敗しました。再度お試しください。</p>';
        }
    }
}
