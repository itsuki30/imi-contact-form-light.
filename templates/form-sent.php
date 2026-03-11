<?php echo wp_kses_post($complete_message); ?>
<script>
    setTimeout(function () {
        var url = new URL(window.location.href);
        url.searchParams.delete('form_sent');
        window.location.href = url.href;
    }, 15000); // 15秒後にリダイレクト
</script>