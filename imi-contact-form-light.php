<?php
/*
Plugin Name: IMI Contact Form Light
Description: 完全自作のシンプルかつ柔軟性に優れた、お問い合わせフォームプラグイン。
Version: 1.0.0
Author: Ichi
License: GPL2
*/

// セッション開始
if (!session_id()) {
    session_start();
}

// 必要なファイルやコードのインクルード
include(plugin_dir_path(__FILE__) . 'includes/class-my-custom-plugin-updater.php');

defined('ABSPATH') or die('No script kiddies please!');

add_action('admin_menu', function () {
    if (!current_user_can('administrator')) {
        remove_menu_page('edit.php?post_type=imi_contact_light');
    }
});



class My_Contact_Form_Plugin
{

    public function __construct()
    {

        add_action('init', array($this, 'register_custom_post_type'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_form_data'));
        add_shortcode('imi_contact_form', array($this, 'render_contact_form'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp', array($this, 'handle_form_submission'));
        add_action('wpcf7_before_send_mail', 'my_plugin_handle_form_submission');

    }

    public function add_meta_boxes()
    {
        add_meta_box('imi_contact_light_meta_box', 'フォーム設定', array($this, 'render_meta_box'), 'imi_contact_light', 'normal', 'high');
        add_meta_box('imi_contact_light_complete_message_meta_box', '送信完了メッセージ', array($this, 'render_complete_message_meta_box'), 'imi_contact_light', 'normal', 'high');
        add_meta_box('imi_contact_light_email_settings_meta_box', '通知メール設定', array($this, 'render_email_settings_meta_box'), 'imi_contact_light', 'side', 'low');
        add_meta_box('imi_contact_light_autoreply_meta_box', '自動返信メール設定', array($this, 'render_autoreply_meta_box'), 'imi_contact_light', 'side', 'low');
        add_meta_box('imi_contact_light_confirmation_meta_box', '確認画面設定', array($this, 'render_confirmation_meta_box'), 'imi_contact_light', 'normal', 'high');
    }

    public function render_complete_message_meta_box($post)
    {
        $complete_message = get_post_meta($post->ID, '_imi_contact_light_complete_message', true);
        wp_nonce_field('save_imi_contact_light_complete_message', 'imi_contact_light_complete_message_nonce');
        echo '<textarea id="imi_contact_light_complete_message" name="imi_contact_light_complete_message" rows="5" style="width:100%;">' . esc_textarea($complete_message) . '</textarea>';
    }
    public function render_confirmation_meta_box($post)
    {
        $confirmation = get_post_meta($post->ID, '_imi_contact_light_confirmation', true);
        wp_nonce_field('save_imi_contact_light_confirmation', 'imi_contact_light_confirmation_nonce');

        // テンプレートの例を追加
        echo '<textarea id="imi_contact_light_confirmation" name="imi_contact_light_confirmation" rows="10" style="width:100%;">' . esc_textarea($confirmation) . '</textarea>';
        echo '<p>戻るボタン：<b>[return 戻る]</b>　、送信ボタン：<b>[submit 送信する]</b></p>';
        echo '<p>上記で設置することができます。ラベルは自由に変更可能です。</p>';
    }


    public function render_email_settings_meta_box($post)
    {
        $email_subject = get_post_meta($post->ID, '_imi_contact_light_email_subject', true);
        $email_message = get_post_meta($post->ID, '_imi_contact_light_email_message', true);
        $email_recipients = get_post_meta($post->ID, '_imi_contact_light_email_recipients', true);
        $email_cc = get_post_meta($post->ID, '_imi_contact_light_email_cc', true);

        wp_nonce_field('save_imi_contact_light_email_settings', 'imi_contact_light_email_settings_nonce');
        echo '<p><label for="imi_contact_light_email_recipients">送信先メールアドレス:</label>';
        echo '<input type="text" id="imi_contact_light_email_recipients" name="imi_contact_light_email_recipients" value="' . esc_attr($email_recipients) . '" style="width:100%;"></p>';
        echo '<p><label for="imi_contact_light_email_cc">CCメールアドレス:<br>複数ある場合は「,」区切りで設定ができます</label>';
        echo '<input type="text" id="imi_contact_light_email_cc" name="imi_contact_light_email_cc" value="' . esc_attr($email_cc) . '" style="width:100%;"></p>';
        echo '<p><label for="imi_contact_light_email_subject">メール件名:</label>';
        echo '<input type="text" id="imi_contact_light_email_subject" name="imi_contact_light_email_subject" value="' . esc_attr($email_subject) . '" style="width:100%;"></p>';
        echo '<p><label for="imi_contact_light_email_message">メール本文:<br>[]ありでフィールド名を記載すること</label>';
        echo '<textarea id="imi_contact_light_email_message" name="imi_contact_light_email_message" rows="10" style="width:100%;">' . esc_textarea($email_message) . '</textarea></p>';
    }

    public function render_autoreply_meta_box($post)
    {
        $autoreply_subject = get_post_meta($post->ID, '_imi_contact_light_autoreply_subject', true);
        $autoreply_message = get_post_meta($post->ID, '_imi_contact_light_autoreply_message', true);
        $autoreply_cc = get_post_meta($post->ID, '_imi_contact_light_autoreply_cc', true);
        $autoreply_to_field = get_post_meta($post->ID, '_imi_contact_light_autoreply_to_field', true);

        wp_nonce_field('save_imi_contact_light_autoreply_settings', 'imi_contact_light_autoreply_settings_nonce');
        echo '<p><label for="imi_contact_light_autoreply_to_field">自動返信先フィールド名:<br>[]無しでフィールド名を記載すること</label>';
        echo '<input type="text" id="imi_contact_light_autoreply_to_field" name="imi_contact_light_autoreply_to_field" value="' . esc_attr($autoreply_to_field) . '" style="width:100%;"></p>';
        echo '<p><label for="imi_contact_light_autoreply_cc">CCメールアドレス:<br>複数ある場合は「,」区切りで設定ができます</label>';
        echo '<input type="text" id="imi_contact_light_autoreply_cc" name="imi_contact_light_autoreply_cc" value="' . esc_attr($autoreply_cc) . '" style="width:100%;"></p>';
        echo '<p><label for="imi_contact_light_autoreply_subject">自動返信メール件名:</label>';
        echo '<input type="text" id="imi_contact_light_autoreply_subject" name="imi_contact_light_autoreply_subject" value="' . esc_attr($autoreply_subject) . '" style="width:100%;"></p>';
        echo '<p><label for="imi_contact_light_autoreply_message">自動返信メール本文:<br>[]ありでフィールド名を記載すること</label>';
        echo '<textarea id="imi_contact_light_autoreply_message" name="imi_contact_light_autoreply_message" rows="10" style="width:100%;">' . esc_textarea($autoreply_message) . '</textarea></p>';
    }

    public function register_custom_post_type()
    {
        $args = array(
            'public' => true,
            'label' => 'IMI Contact Form Light',
            'supports' => array('title'),
            'show_in_menu' => true,
        );
        register_post_type('imi_contact_light', $args);
    }




    public function render_meta_box($post)
    {
        $content = get_post_meta($post->ID, '_imi_contact_light_content', true);
        wp_nonce_field('save_imi_contact_light_data', 'imi_contact_light_nonce');

        // ボタンを追加
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

        // モーダルのHTML
        ?>
        <div id="form-element-modal" style="display:none;">
            <div class="modal-content">
                <span class="close-button">&times;</span>
                <h2>フォーム要素設定</h2>
                <form id="form-element-settings">
                    <!-- ここに設定オプションが動的に追加される -->
                    <div id="element-options"></div>
                    <button type="button" id="add-element">追加</button>
                </form>
            </div>
        </div>
        <?php
    }

    public function save_form_data($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        if (!isset($_POST['imi_contact_light_nonce']) || !wp_verify_nonce($_POST['imi_contact_light_nonce'], 'save_imi_contact_light_data'))
            return;

        // 権限チェックを追加
        if (!current_user_can('edit_post', $post_id))
            return;

        if (isset($_POST['imi_contact_light_content'])) {
            $content = wp_kses_post($_POST['imi_contact_light_content']);
            update_post_meta($post_id, '_imi_contact_light_content', $content);
        }

        if (isset($_POST['imi_contact_light_complete_message'])) {
            $complete_message = wp_kses_post($_POST['imi_contact_light_complete_message']);
            update_post_meta($post_id, '_imi_contact_light_complete_message', $complete_message);
        }

        if (isset($_POST['imi_contact_light_confirmation'])) {
            $confirmation = wp_kses_post($_POST['imi_contact_light_confirmation']);
            update_post_meta($post_id, '_imi_contact_light_confirmation', $confirmation);
        }

        if (isset($_POST['imi_contact_light_email_settings_nonce']) && wp_verify_nonce($_POST['imi_contact_light_email_settings_nonce'], 'save_imi_contact_light_email_settings')) {
            if (isset($_POST['imi_contact_light_email_subject'])) {
                $email_subject = wp_kses_post($_POST['imi_contact_light_email_subject']);
                update_post_meta($post_id, '_imi_contact_light_email_subject', $email_subject);
            }
            if (isset($_POST['imi_contact_light_email_message'])) {
                $email_message = wp_kses_post($_POST['imi_contact_light_email_message']);
                update_post_meta($post_id, '_imi_contact_light_email_message', $email_message);
            }
            if (isset($_POST['imi_contact_light_email_recipients'])) {
                $email_recipients = sanitize_text_field($_POST['imi_contact_light_email_recipients']);
                update_post_meta($post_id, '_imi_contact_light_email_recipients', $email_recipients);
            }
            if (isset($_POST['imi_contact_light_email_cc'])) {
                $email_cc = sanitize_text_field($_POST['imi_contact_light_email_cc']);
                update_post_meta($post_id, '_imi_contact_light_email_cc', $email_cc);
            }
        }

        if (isset($_POST['imi_contact_light_autoreply_settings_nonce']) && wp_verify_nonce($_POST['imi_contact_light_autoreply_settings_nonce'], 'save_imi_contact_light_autoreply_settings')) {
            if (isset($_POST['imi_contact_light_autoreply_subject'])) {
                $autoreply_subject = wp_kses_post($_POST['imi_contact_light_autoreply_subject']);
                update_post_meta($post_id, '_imi_contact_light_autoreply_subject', $autoreply_subject);
            }
            if (isset($_POST['imi_contact_light_autoreply_message'])) {
                $autoreply_message = wp_kses_post($_POST['imi_contact_light_autoreply_message']);
                update_post_meta($post_id, '_imi_contact_light_autoreply_message', $autoreply_message);
            }
            if (isset($_POST['imi_contact_light_autoreply_cc'])) {
                $autoreply_cc = sanitize_text_field($_POST['imi_contact_light_autoreply_cc']);
                update_post_meta($post_id, '_imi_contact_light_autoreply_cc', $autoreply_cc);
            }
            if (isset($_POST['imi_contact_light_autoreply_to_field'])) {
                $autoreply_to_field = sanitize_text_field($_POST['imi_contact_light_autoreply_to_field']);
                update_post_meta($post_id, '_imi_contact_light_autoreply_to_field', $autoreply_to_field);
            }
        }
    }

    public function render_contact_form($atts)
    {
        $atts = shortcode_atts(array('id' => ''), $atts);
        $form_id = intval($atts['id']);
        if (!$form_id)
            return ''; // IDが無効の場合は何も表示しない

        // フォームのコンテンツを取得
        $content = get_post_meta($form_id, '_imi_contact_light_content', true);
        $complete_message = get_post_meta($form_id, '_imi_contact_light_complete_message', true);
        $form_html = '';

        if ($content) {
            if (isset($_GET['action']) && $_GET['action'] === 'confirm') {
                // 確認画面を表示
                ob_start(); // バッファリング開始
                include(plugin_dir_path(__FILE__) . 'templates/form-confirmation.php');
                $form_html = ob_get_clean(); // バッファの内容を取得してクリア
            } elseif (isset($_GET['form_sent']) && $_GET['form_sent'] === 'true') {
                // 送信完了メッセージを表示
                ob_start();
                include(plugin_dir_path(__FILE__) . 'templates/form-sent.php');
                $form_html = ob_get_clean();
            } else {
                // 初回読み込みまたはリロード時にセッションをクリア
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    if (isset($_SESSION['form_data'])) {
                        unset($_SESSION['form_data']);
                        /* session_unset();  // セッション内のすべての変数をクリア
                        session_destroy(); // セッションを破棄 */
                    }
                }
                // 通常のフォームを表示
                ob_start();
                include(plugin_dir_path(__FILE__) . 'templates/form.php');
                $form_html = ob_get_clean();
            }
        }

        return $form_html; // ショートコード内にフォームを返す
    }


    public function enqueue_scripts($hook)
    {
        if ($hook === 'post.php' || $hook === 'post-new.php') {
            wp_enqueue_script('my-contact-form-editor', plugins_url('js/editor-script.js', __FILE__), array('jquery'), '1.0', true);
            wp_enqueue_style('my-contact-form-style', plugins_url('css/modal-style.css', __FILE__));
        }
    }

    function handle_form_submission()
    {
        // セッションが開始されていない場合は開始
        if (session_status() === PHP_SESSION_NONE) {
            unset($_SESSION['form_data']);
            unset($_SESSION['form_files']);
            unset($_SESSION['token']);
        }

        // セッションの有効期限を設定
        $timeout_duration = 1800; // 30分
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
            unset($_SESSION['form_data']);
            unset($_SESSION['form_files']);
            unset($_SESSION['LAST_ACTIVITY']);
            unset($_SESSION['token']);
        }
        $_SESSION['LAST_ACTIVITY'] = time(); // 最後のアクティビティ時間を更新

        if (!isset($_SESSION['token'])) {
            $_SESSION['token'] = bin2hex(random_bytes(32)); // 新しいトークンを生成
        }

        if (isset($_POST['imi_contact_light_confirm']) && isset($_POST['imi_contact_form_id'])) {

            // ★ ハニーポットチェック
            if (!empty($_POST['imi_website'])) {
                error_log('Honeypot triggered - Spam detected');
                // スパムとして処理を中断
                wp_redirect(home_url());
                exit;
            }

            // 確認画面の表示
            $form_id = intval($_POST['imi_contact_form_id']);
            $content = get_post_meta($form_id, '_imi_contact_light_content', true);

            if ($content) {
                // バリデーションチェック
                $errors = $this->validate_form_submission($content, $_POST, $_FILES);
                if (!empty($errors)) {
                    $_SESSION['form_errors'] = $errors;
                    wp_redirect(esc_url_raw(remove_query_arg(array('action', 'form_sent'))));
                    exit;
                }

                // トークンの検証 (CSRF対策)
                if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
                    wp_die('無効なトークンです。');
                }

                error_log('kakunin');
                $sanitized_data = array();
                foreach ($_POST as $key => $value) {
                    if (is_array($value)) {
                        // 配列のサニタイズ処理
                        $sanitized_data[$key] = array_map('sanitize_textarea_field', $value);
                    } else {
                        $sanitized_data[$key] = sanitize_textarea_field($value);
                    }
                }
                $_SESSION['form_data'] = $sanitized_data;

                // ファイルデータの処理
                if (!empty($_FILES['imi_contact_light_file']['tmp_name'])) {
                    // uploads ディレクトリのパスを取得
                    $upload_dir = wp_upload_dir();
                    $target_dir = $upload_dir['path'] . '/';

                    // ファイル名と拡張子の安全な取得
                    $original_file_name = sanitize_file_name($_FILES['imi_contact_light_file']['name']);

                    // ファイルの種類とサイズを検証
                    $allowed_types = array('image/jpeg', 'image/png', 'application/pdf'); // 許可するファイルタイプ
                    $file_info = wp_check_filetype($original_file_name);
                    $file_ext = $file_info['ext'];

                    // 実ファイルのMIMEタイプもチェック
                    $file_type = mime_content_type($_FILES['imi_contact_light_file']['tmp_name']);
                    $file_size = $_FILES['imi_contact_light_file']['size'];

                    $is_valid_type = in_array($file_type, $allowed_types) && in_array($file_ext, array('jpg', 'jpeg', 'png', 'pdf'));

                    // ファイルサイズが 2MB 以下
                    if ($is_valid_type && $file_size <= 2 * 1024 * 1024) {
                        // 一意なファイル名を生成
                        $unique_file_name = wp_unique_filename($target_dir, $original_file_name);
                        $target_file = $target_dir . $unique_file_name;

                        if (move_uploaded_file($_FILES['imi_contact_light_file']['tmp_name'], $target_file)) {
                            // アップロード成功
                            $_SESSION['form_files'] = array(
                                'name' => $unique_file_name,
                                'path' => $target_file
                            );
                            error_log('File uploaded successfully: ' . $target_file);
                        } else {
                            // アップロード失敗
                            error_log('Failed to upload file.');
                        }
                    } else {
                        error_log('Invalid file type or size.');
                    }
                }

                // 確認画面にリダイレクト
                wp_redirect(esc_url(add_query_arg('action', 'confirm', get_permalink())));
                exit;
            } else {
                error_log('フォームの内容が見つかりません。');
            }
        } elseif (isset($_POST['imi_contact_light_submit'])) {

            // ★ ハニーポットチェック
            if (!empty($_POST['imi_website'])) {
                error_log('Honeypot triggered - Spam detected');
                wp_redirect(home_url());
                exit;
            }

            if (isset($_POST['my_plugin_confirm']) && $_POST['my_plugin_confirm'] === 'true') {
                // トークンの検証 (CSRF対策)
                if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
                    wp_die('無効なトークンです。');
                }

                error_log('kakunin_soushin');

                // 確認画面からの送信処理
                if (isset($_SESSION['form_data'])) {
                    $form_data = $_SESSION['form_data'];
                    if (isset($form_data['imi_contact_form_id'])) {
                        $form_id = intval($form_data['imi_contact_form_id']);
                    }
                    error_log('form_data：' . print_r($form_data, true));

                    // メール送信処理
                    $this->send_form_email();
                } else {
                    error_log('Session data is not set.');
                }

                wp_redirect(esc_url(add_query_arg('form_sent', 'true', get_permalink())));
                exit;
            } else {
                error_log('tyokusetu');

                // 確認画面を経由せず直接送信された場合
                $form_id = intval($_POST['imi_contact_form_id']);
                $content = get_post_meta($form_id, '_imi_contact_light_content', true);

                // バリデーションチェック
                $errors = $this->validate_form_submission($content, $_POST, $_FILES);
                if (!empty($errors)) {
                    $_SESSION['form_errors'] = $errors;
                    wp_redirect(esc_url_raw(remove_query_arg(array('action', 'form_sent'))));
                    exit;
                }

                // トークンの検証 (CSRF対策)
                if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
                    wp_die('無効なトークンです。');
                }

                $sanitized_data = array();

                // 入力データをサニタイズしてセッションに保存
                foreach ($_POST as $key => $value) {
                    if (is_array($value)) {
                        // 配列の場合は、すべての値をサニタイズ
                        $sanitized_data[$key] = array_map('sanitize_textarea_field', $value);
                    } else {
                        // 通常のテキストフィールドはそのままサニタイズ
                        $sanitized_data[$key] = sanitize_textarea_field($value);
                    }
                }
                $_SESSION['form_data'] = $sanitized_data;

                // データを送信して保存
                $this->send_form_email(); // メール送信

                // 完了画面へリダイレクト
                wp_redirect(esc_url(add_query_arg('form_sent', 'true', get_permalink())));
                exit;
            }
        }
    }

    private function validate_form_submission($content, $post_data, $files_data)
    {
        $errors = array();

        // [form_element ...] を抽出
        if (preg_match_all('/\[form_element\s+([^\]]+)\]/', $content, $matches)) {
            foreach ($matches[1] as $atts_string) {
                // 属性をパース
                $atts = shortcode_parse_atts($atts_string);

                // fileフィールド以外でnameがない場合はスキップ
                if (!isset($atts['name']) && (!isset($atts['type']) || $atts['type'] !== 'file'))
                    continue;

                $name = isset($atts['name']) ? $atts['name'] : (isset($atts['id']) ? $atts['id'] : '');
                $type = isset($atts['type']) ? $atts['type'] : 'text';
                $label = isset($atts['label']) ? $atts['label'] : (isset($atts['placeholder']) && !empty($atts['placeholder']) ? $atts['placeholder'] : $name);

                $is_required = false;
                if (strpos($type, '*') === 0) {
                    $is_required = true;
                    $type = substr($type, 1);
                }

                if ($type === 'file') {
                    if ($is_required && empty($files_data['imi_contact_light_file']['name'])) {
                        $errors[] = "「ファイル」は必須です。";
                    }
                    continue;
                }

                // POSTデータから値を取得
                $value = isset($post_data[$name]) ? $post_data[$name] : '';

                // 空チェック
                $is_empty = is_array($value) ? empty($value) : trim((string) $value) === '';

                if ($is_required && $is_empty) {
                    $errors[] = "「{$label}」は必須項目です。";
                    continue;
                }

                // フォーマットチェック
                if (!$is_empty && !is_array($value)) {
                    if ($type === 'email' && !is_email($value)) {
                        $errors[] = "「{$label}」のメールアドレスの形式が正しくありません。";
                    } elseif ($type === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) {
                        $errors[] = "「{$label}」のURLの形式が正しくありません。";
                    } elseif ($type === 'tel' && !preg_match('/^[0-9\-+]+$/', $value)) {
                        $errors[] = "「{$label}」の電話番号の形式が正しくありません。（ハイフンなし、+あり等の形式に対応）";
                    } elseif ($type === 'number' && !is_numeric($value)) {
                        $errors[] = "「{$label}」には数値を入力してください。";
                    } elseif (isset($atts['pattern']) && !empty($atts['pattern'])) {
                        // カスタムパターンのチェック
                        $pattern = $atts['pattern'];
                        if (!preg_match("/^(?:{$pattern})$/u", $value)) {
                            $errors[] = "「{$label}」の入力形式が正しくありません。";
                        }
                    }
                }
            }
        }

        return $errors;
    }



    private function send_form_email()
    {
        $form_id = intval($_POST['imi_contact_form_id']);
        $subject = sanitize_text_field(get_post_meta($form_id, '_imi_contact_light_email_subject', true));
        $message_template = sanitize_textarea_field(get_post_meta($form_id, '_imi_contact_light_email_message', true));
        $recipients = sanitize_text_field(get_post_meta($form_id, '_imi_contact_light_email_recipients', true));
        $cc = sanitize_text_field(get_post_meta($form_id, '_imi_contact_light_email_cc', true));

        if (!$recipients) {
            $recipients = get_option('admin_email'); // デフォルトの送信先
        }

        $recipients = array_map('trim', explode(',', $recipients));
        $cc = !empty($cc) ? array_map('trim', explode(',', $cc)) : array();  // CCが空かどうかをチェック

        $form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : $_POST;

        // メール本文を作成
        $message = $message_template;
        foreach ($form_data as $key => $value) {
            if ($key !== 'imi_contact_light_submit' && $key !== 'imi_contact_form_id' && $key !== 'confirm' && $key !== 'imi_contact_light_confirm') {
                if (is_array($value)) {
                    // 配列（チェックボックスなど）の場合、カンマで区切って表示
                    $replacement = implode(', ', array_map('esc_html', $value));
                } else {
                    // 通常のテキストフィールド
                    $replacement = esc_html($value);
                }

                // プレースホルダーが空でない場合のみ置き換え
                if (!empty($replacement)) {
                    $message = str_replace('[' . esc_html($key) . ']', $replacement, $message);
                } else {
                    // 空の場合、プレースホルダーを削除
                    $message = str_replace('[' . esc_html($key) . ']', '', $message);
                }
            }
        }

        // テンプレート内のプレースホルダーをチェックして削除
        preg_match_all('/\[(.+?)\]/', $message_template, $placeholders);
        foreach ($placeholders[0] as $placeholder) {
            $key = trim($placeholder, '[]');
            if (!isset($form_data[$key]) || empty($form_data[$key])) {
                $message = str_replace($placeholder, '', $message);
            }
        }

        // 添付ファイルの準備
        $attachments = array();
        if (isset($_SESSION['form_files']['path']) && file_exists($_SESSION['form_files']['path'])) {
            $attachments[] = $_SESSION['form_files']['path'];
        } elseif (!empty($_FILES['imi_contact_light_file']['tmp_name'])) {
            $uploaded_file = $_FILES['imi_contact_light_file'];
            $allowed_types = array('image/jpeg', 'image/png', 'application/pdf');
            $file_type = mime_content_type($uploaded_file['tmp_name']);
            $file_size = $uploaded_file['size'];

            if (in_array($file_type, $allowed_types) && $file_size <= 2 * 1024 * 1024) {
                $tmp_dir = sys_get_temp_dir();
                $target_path = $tmp_dir . '/' . basename($uploaded_file['name']);

                if (move_uploaded_file($uploaded_file['tmp_name'], $target_path)) {
                    $attachments[] = $target_path;
                } else {
                    error_log('ファイルのアップロードに失敗しました。');
                }
            } else {
                error_log('無効なファイルタイプまたはサイズです。');
            }
        }

        // 通常のwp_mail()を使用した送信処理
        $site_url = get_site_url();
        $domain = parse_url($site_url, PHP_URL_HOST);
        $from_email = 'wordpress@' . $domain;
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . $from_email . '>',
            'Reply-To: ' . $from_email // リプライ先
        );
        if (!empty($cc)) {
            $cc = array_map('sanitize_email', $cc);
            $headers[] = 'Cc: ' . implode(',', $cc);
        }
        $recipients = array_map('sanitize_email', $recipients);
        $mail_sent = wp_mail($recipients, $subject, $message, $headers, $attachments);

        if ($mail_sent) {
            error_log('メール送信に成功しました。');
        } else {
            error_log('メール送信に失敗しました。');
        }

        // 自動返信メール送信
        $this->send_autoreply_email();

        // ファイルの後処理（不要になった一時ファイルを削除）
        foreach ($attachments as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $_SESSION['save_flag'] = 1;
    }

    private function send_autoreply_email()
    {
        $form_id = intval($_POST['imi_contact_form_id']);
        $autoreply_to_field = get_post_meta($form_id, '_imi_contact_light_autoreply_to_field', true);
        $subject = get_post_meta($form_id, '_imi_contact_light_autoreply_subject', true);
        $message_template = get_post_meta($form_id, '_imi_contact_light_autoreply_message', true);
        $autoreply_cc = get_post_meta($form_id, '_imi_contact_light_autoreply_cc', true);

        $cc = !empty($autoreply_cc) ? array_map('trim', explode(',', $autoreply_cc)) : array();

        $form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : $_POST;
        $recipient = isset($form_data[$autoreply_to_field]) ? sanitize_email($form_data[$autoreply_to_field]) : '';

        // メール本文を作成
        $message = $message_template;
        foreach ($form_data as $key => $value) {
            if ($key !== 'imi_contact_light_submit' && $key !== 'imi_contact_form_id' && $key !== 'confirm' && $key !== 'imi_contact_light_confirm') {
                if (is_array($value)) {
                    $replacement = implode(', ', array_map('esc_html', $value));
                } else {
                    $replacement = esc_html($value);
                }

                // プレースホルダーが空でない場合のみ置き換え
                if (!empty($replacement)) {
                    $message = str_replace('[' . esc_html($key) . ']', $replacement, $message);
                } else {
                    // 空の場合、プレースホルダーを削除
                    $message = str_replace('[' . esc_html($key) . ']', '', $message);
                }
            }
        }

        // テンプレート内のプレースホルダーをチェックして削除
        preg_match_all('/\[(.+?)\]/', $message_template, $placeholders);
        foreach ($placeholders[0] as $placeholder) {
            $key = trim($placeholder, '[]');
            if (!isset($form_data[$key]) || empty($form_data[$key])) {
                $message = str_replace($placeholder, '', $message);
            }
        }

        // 通常のwp_mail()を使用した送信処理
        $site_url = get_site_url();
        $domain = parse_url($site_url, PHP_URL_HOST);
        $from_email = 'wordpress@' . $domain;
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . $from_email . '>',
            'Reply-To: ' . $from_email // リプライ先
        );
        if (!empty($cc)) {
            $headers[] = 'Cc: ' . implode(',', $cc);
        }
        $mail_sent = wp_mail($recipient, $subject, $message, $headers);

        if ($mail_sent) {
            error_log('自動返信メール送信に成功しました。');
        } else {
            error_log('自動返信メール送信に失敗しました。');
        }
    }





}

new My_Contact_Form_Plugin();


// フォーム要素ショートコードの処理
function render_form_element($atts)
{
    $atts = shortcode_atts(array(
        'type' => 'text',
        'name' => '',
        'id' => '',
        'class' => '', // class属性を追加
        'placeholder' => '',
        'options' => '',
        'label' => '',
        'valuetext' => '',
        'pattern' => '', // パターン属性を追加
    ), $atts);

    $required = '';
    if (strpos($atts['type'], '*') === 0) {
        $required = ' data-required="true"';
        $atts['type'] = substr($atts['type'], 1); // `*` を取り除く
    }

    $class_attr = esc_attr($atts['class']) ? ' class="' . esc_attr($atts['class']) . '"' : ''; // クラス属性の処理
    $pattern_attr = esc_attr($atts['pattern']) ? ' pattern="' . esc_attr($atts['pattern']) . '"' : ''; // パターン属性の処理

    switch ($atts['type']) {
        case 'text':
        case 'email':
        case 'url':
        case 'number':
            return '<input type="' . esc_attr($atts['type']) . '" name="' . esc_attr($atts['name']) . '" id="' . esc_attr($atts['id']) . '"' . $class_attr . $pattern_attr . $required . ' placeholder="' . esc_attr($atts['placeholder']) . '">';
        case 'date':
            return '<input type="date" name="' . esc_attr($atts['name']) . '" id="' . esc_attr($atts['id']) . '"' . $class_attr . $required . '>';
        case 'textarea':
            return '<textarea name="' . esc_attr($atts['name']) . '" id="' . esc_attr($atts['id']) . '"' . $class_attr . $required . ' placeholder="' . esc_attr($atts['placeholder']) . '"></textarea>';
        case 'select':
            $options = explode(',', $atts['options']);
            $optionsHtml = '<select name="' . esc_attr($atts['name']) . '" id="' . esc_attr($atts['id']) . '"' . $class_attr . $required . '>';
            foreach ($options as $option) {
                $optionsHtml .= '<option value="' . esc_attr(trim($option)) . '">' . esc_html(trim($option)) . '</option>';
            }
            $optionsHtml .= '</select>';
            return $optionsHtml;
        case 'checkbox':
        case 'radio':
            $options = explode(',', $atts['options']);
            $optionsHtml = '';
            foreach ($options as $option) {
                $value = esc_attr(trim($option));
                $label = esc_html(trim($option));
                if ($atts['type'] == 'checkbox') {
                    $optionsHtml .= '<label><input type="checkbox" name="' . esc_attr($atts['name']) . '" id="' . esc_attr($atts['id']) . '"' . $class_attr . $required . ' value="' . $value . '">' . $label . '</label>';
                } else {
                    $optionsHtml .= '<label><input type="radio" name="' . esc_attr($atts['name']) . '" id="' . esc_attr($atts['id']) . '"' . $class_attr . $required . ' value="' . $value . '">' . $label . '</label>';
                }
            }
            return $optionsHtml;
        case 'hidden':
            $valuetext = apply_filters('imi_contact_form_hidden_valuetext', $atts['valuetext'], $atts['name']);
            return '<input type="hidden" name="' . esc_attr($atts['name']) . '" id="' . esc_attr($atts['id']) . '" value="' . esc_attr($valuetext) . '">';
        case 'file':
            return '<input type="file" name="imi_contact_light_file" id="' . esc_attr($atts['id']) . '"' . $class_attr . $required . '>';
        case 'submit':
            return '<input type="submit" id="confirm-button" name="imi_contact_light_submit" value="' . esc_attr($atts['label']) . '"' . $class_attr . '>';
        case 'confirm':
            return '<input id="confirm-button" type="submit" name="imi_contact_light_confirm" value="' . esc_attr($atts['label']) . '"' . $class_attr . '>';
        case 'back':
            return '<input type="button" value="' . esc_attr($atts['label']) . '" onclick="' . ($atts['type'] == 'back' ? 'history.back()' : '') . '"' . $class_attr . '>';
        case 'tel':
            $tel_pattern = !empty($atts['pattern']) ? $pattern_attr : ' pattern="\d{2,3}\d{4}\d{4}"'; // デフォルトの電話番号のパターンを維持しつつ、上書き可能に
            return '<input type="tel" name="' . esc_attr($atts['name']) . '" id="' . esc_attr($atts['id']) . '"' . $class_attr . $tel_pattern . $required . ' placeholder="' . esc_attr($atts['placeholder']) . '">';
    }
}
add_shortcode('form_element', 'render_form_element');

function imi_contact_light_enqueue_scripts()
{
    wp_enqueue_style('validation-css', plugin_dir_url(__FILE__) . 'css/validation.css');
    wp_enqueue_script('validation-js', plugin_dir_url(__FILE__) . 'js/validation.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'imi_contact_light_enqueue_scripts');




// プラグインが有効になったときにカラムを追加
add_filter('manage_edit-imi_contact_light_columns', 'add_shortcode_column');

function add_shortcode_column($columns)
{
    $columns['shortcode'] = 'ショートコード';
    return $columns;
}

// カラムにデータを表示
add_action('manage_imi_contact_light_posts_custom_column', 'show_shortcode_column', 10, 2);

function show_shortcode_column($column, $post_id)
{
    if ($column === 'shortcode') {
        echo '[imi_contact_form id="' . $post_id . '"]';
    }
}

// --- 説明画面の追加 --- //
function imi_contact_form_add_explanation_page()
{
    $parent_slug = 'edit.php?post_type=imi_contact_light';
    add_submenu_page(
        $parent_slug,
        'プラグイン説明',
        'プラグイン説明',
        'manage_options',
        'imi-contact-form-explanation',
        'imi_contact_form_render_explanation_page'
    );
}
add_action('admin_menu', 'imi_contact_form_add_explanation_page');

function imi_contact_form_render_explanation_page()
{
    ?>
    <div class="wrap" style="max-width: 800px;">
        <h1 class="wp-heading-inline">IMI Contact Form Light 説明画面</h1>
        <div
            style="background: #fff; padding: 30px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin-top: 20px;">

            <h2 style="border-bottom: 2px solid #0073aa; padding-bottom: 10px; margin-top: 0;">1. はじめに</h2>
            <p>本プラグインは、WordPress専用に開発されたお問い合わせフォームプラグインであり、他社製の「Contact Form 7」や「mw wp
                form」などと同様の機能を持ちながらも、より柔軟なスタイル設定を可能にすることを目指しています。<br>
                ユーザーが求める多様なニーズに応えられるよう、シンプルかつ直感的な操作性を重視し、誰でも簡単に利用できるよう設計されています。<br>
                これにより、個別のビジネスやサイトに最適なフォームを作成することができます。</p>

            <h2 style="border-bottom: 2px solid #0073aa; padding-bottom: 10px; margin-top: 40px;">2. 機能</h2>
            <p>本プラグインには、さまざまな便利な機能が搭載されています。以下に主な機能を詳しくご紹介します。</p>

            <h3 style="margin-top: 20px;">・複数のフォーム作成可能</h3>
            <p style="margin-bottom: 20px;">ユーザーは、異なる目的に応じて複数のフォームを簡単に作成できます。これにより、サイトの運営者はさまざまな問い合わせを効率的に管理できます。</p>

            <h3 style="margin-top: 20px;">・ショートコードによる設置</h3>
            <p style="margin-bottom: 20px;">作成したフォームはショートコードを使用して、任意の固定ページや投稿ページに設置できます。これにより、手間なくフォームをサイトに組み込むことが可能です。</p>

            <h3 style="margin-top: 20px;">・ボタン一つでフォーム作成</h3>
            <p style="margin-bottom: 20px;">フォーム作成のプロセスをシンプルにし、ボタン一つで新しいフォームを作成できる機能を搭載。技術的な知識がなくても簡単に利用可能です。</p>

            <h3 style="margin-top: 20px;">・確認画面と送信完了画面の作成</h3>
            <p style="margin-bottom: 20px;">
                フォーム送信時に確認画面を表示することができ、ユーザーは自分が入力した内容を再確認できます。また、送信完了後の画面も自由にカスタマイズ可能で、ブランドに合わせたメッセージを表示できます。</p>

            <h3 style="margin-top: 20px;">・HTMLとの組み合わせ</h3>
            <p style="margin-bottom: 20px;">
                本プラグインはHTMLと組み合わせることができ、柔軟なカスタマイズが可能です。デザインやレイアウトを自由に調整できるため、よりオリジナルなフォームを作成できます。</p>

            <h3 style="margin-top: 20px;">・管理者通知メール</h3>
            <p style="margin-bottom: 20px;">フォーム送信時に管理者に通知メールを送信する機能を備えており、迅速に問い合わせ内容を把握することができます。</p>

            <h3 style="margin-top: 20px;">・ユーザーへのメール通知</h3>
            <p style="margin-bottom: 20px;">メールアドレスフォームを設置することで、ユーザーにもメール通知を送信することができます。これにより、ユーザーとのコミュニケーションを円滑に行えます。</p>

            <h2 style="border-bottom: 2px solid #0073aa; padding-bottom: 10px; margin-top: 40px;">3. 使用方法</h2>

            <h3 style="margin-top: 20px;">フォーム作成</h3>
            <ol style="padding-left: 20px;">
                <li>プラグインを有効化し、「新規投稿の追加」をクリックします。</li>
                <li>フォームの各項目を設定後、「公開」ボタンを押します。<br>
                    ※必須項目はtype内の頭に「*」をつけることで設定できます。（例）type="*text"<br>
                    ※メール内容にフォームを反映させるには [（フォームname）] とすると可能です。</li>
                <li>作成したフォームのショートコードを一覧からコピーし、任意のページに設置します。</li>
                <li>フォームの編集や削除は、タイトルをクリックして編集が可能。削除はタイトルをホバーし、ゴミ箱に移動することで行えます。</li>
                <li>自動返信メール設定の「自動返信先フィールド名:」は「[ ]」無しのフォームnameを入力してください。</li>
            </ol>
            <h2 style="border-bottom: 2px solid #0073aa; padding-bottom: 10px; margin-top: 40px;">4. セキュリティに関して</h2>
            <p>プラグイン内にBOTによるアタックを防ぐ対策をしています。<br>
                メーラー側でのスパム対策設定などの併用もご検討ください。</p>

            <h2 style="border-bottom: 2px solid #0073aa; padding-bottom: 10px; margin-top: 40px;">5. 帰属に関して</h2>
            <p>本プラグインの著作権はすべて「Ichi」に帰属します。<br>
                プラグイン内のファイルやコードの変更、複製を行う際は、必ず事前に許可を得てください。<br>
                無断での販売や配布は厳禁です。<br>
                利用者の皆様には、適切な利用と遵守をお願い申し上げます。</p>
        </div>
    </div>
    <?php
}

// プラグインの初期化時にアップデートチェックを設定
add_action('init', function () {
    $plugin_slug = 'imi-contact-form-light'; // プラグインのスラッグ（フォルダ名）
    $plugin_version = '1.0.0'; // 現在のバージョン
    new My_Custom_Plugin_Updater($plugin_slug, $plugin_version);
});