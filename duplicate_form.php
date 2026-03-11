<?php
function duplicate_form() {
    if (!isset($_GET['post']) || !current_user_can('edit_posts')) {
        wp_die('権限がありません。');
    }

    $post_id = absint($_GET['post']);
    $post = get_post($post_id);

    if ($post->post_type != 'imi_contact_light') {
        wp_die('無効な投稿タイプです。');
    }

    $new_post_id = wp_insert_post(array(
        'post_title' => $post->post_title . ' - 複製',
        'post_type' => 'imi_contact_light',
        'post_status' => 'draft',
    ));

    // フォームHTMLの複製
    $form_html = get_post_meta($post_id, '_imi_contact_light_html', true);
    update_post_meta($new_post_id, '_imi_contact_light_html', $form_html);

    wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
    exit;
}
add_action('admin_action_duplicate_form', 'duplicate_form');
