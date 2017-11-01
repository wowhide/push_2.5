$(function() {
    //削除確認ダイアログの初期化
    $('#dialog').dialog({
        autoOpen: false,
        draggable: false,
        modal: true
    });

    //削除ボタンクリック時
    $('a.btn_mini').on('click', function() {
        var delUrl = $(this).attr("href");
        //削除確認ダイアログを表示
        $('#dialog').dialog({
            autoOpen: true,
            buttons: {
                'はい':function() {
                    //削除を実行
                    window.location.href = delUrl;
                },
                'いいえ':function() {
                    //ダイアログを閉じる
                    $('#dialog').dialog('close');
                }
            }
        })
    });
});
