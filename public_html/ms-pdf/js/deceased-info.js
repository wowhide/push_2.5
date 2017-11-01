$(function() {
    //削除確認ダイアログの初期化
    $('#dialog').dialog({
        autoOpen: false,
        draggable: false,
        modal: true,
        buttons: {
            'はい':function() {
                //削除処理を実行
                $('#del_form').submit();
            },
            'いいえ':function() {
                //ダイアログを閉じる
                $(this).dialog('close');
            }
        }
    });

    //削除ボタンクリック時
    $('#deceased_info_del').click(function() {
        //削除確認ダイアログを表示
        $('#dialog').dialog({
            autoOpen: true
        });
    });
});

/*二重送信防止*/
var set=0;
function double() {
    if(set==0){
        set=1;
    } else {
        alert("只今処理中です。\nそのままお待ちください。");
        return false;
    }
}
