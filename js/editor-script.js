jQuery(document).ready(function ($) {
    var modal = $('#form-element-modal');
    var closeButton = $('.close-button');
    var elementOptions = $('#element-options');
    var selectedType = '';

    $('.insert-shortcode-button').click(function () {
        selectedType = $(this).data('type');
        elementOptions.empty(); // 前回のオプションをクリア

        // フォーム要素の設定オプションを追加
        switch (selectedType) {
            case 'text':
            case 'email':
            case 'url':
            case 'tel':
            case 'number':
                elementOptions.append('<label>name: <input type="text" name="name" /></label>');
                elementOptions.append('<label>id: <input type="text" name="id" /></label>');
                elementOptions.append('<label>placeholder: <input type="text" name="placeholder" /></label>');
                elementOptions.append('<label>pattern (regex, optional): <input type="text" name="pattern" /></label>');
                break;
            case 'date':
                elementOptions.append('<label>name: <input type="text" name="name" /></label>');
                break;
            case 'textarea':
                elementOptions.append('<label>name: <input type="text" name="name" /></label>');
                elementOptions.append('<label>id: <input type="text" name="id" /></label>');
                elementOptions.append('<label>placeholder: <input type="text" name="placeholder" /></label>');
                break;
            case 'select':
            case 'checkbox':
            case 'radio':
                elementOptions.append('<label>name: <input type="text" name="name" /></label>');
                elementOptions.append('<label>id: <input type="text" name="id" /></label>');
                elementOptions.append('<label>options (comma separated): <input type="text" name="options" /></label>');
                break;
            case 'hidden':
                elementOptions.append('<label>name: <input type="text" name="name" /></label>');
                elementOptions.append('<label>id: <input type="text" name="id" /></label>');
                elementOptions.append('<label>value: <input type="text" name="valuetext" /></label>');
                break;
            case 'submit':
            case 'confirm':
            case 'back':
                elementOptions.append('<label>label: <input type="text" name="label" /></label>');
                break;
        }

        modal.show();
    });

    closeButton.click(function () {
        modal.hide();
    });

    $(window).click(function (event) {
        if ($(event.target).is('#form-element-modal')) {
            modal.hide();
        }
    });

    $('#add-element').click(function () {
        // モーダルで設定した情報を取得して、ショートコードを挿入
        var options = elementOptions.find('input').serializeArray();
        var shortcode = '[form_element type="' + selectedType + '"';
        $.each(options, function (index, field) {
            shortcode += ' ' + field.name + '="' + field.value + '"';
        });
        shortcode += ']';

        var contentArea = $('#imi_contact_light_content');
        contentArea.val(contentArea.val() + shortcode + '[/form_element]');
        modal.hide();
    });

    // 確認画面の送信ボタン
    $(document).on('click', '#confirm-submit', function () {
        $('form').submit();
    });

    // 確認画面のキャンセルボタン
    $(document).on('click', '#cancel-submit', function () {
        $('#confirmation-modal').hide();
    });
});
