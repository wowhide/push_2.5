$(function() {
    //1分毎に故人様一覧をリロードする
    setInterval(function(){
        $.ajax({
            url: 'reloaddeceasedlist',
            success: function(data) {
                $('div#reload').html(data);
            }
        });
    },60000);

    //Datepickerの初期化
    $("#datepickerFrom").datepicker({
        showOn: 'both'
    });
    //Datepickerの初期化
    $("#datepickerTo").datepicker({
        showOn: 'both'
    });

    //検索条件の日付はBackspaceとDeleteで削除する
    $('#datepickerFrom').keyup(function(e) {
        if (e.keyCode == 46 || e.keyCode == 8){
            $(this).val("");
        }
    });
    $('#datepickerTo').keyup(function(e) {
        if (e.keyCode == 46 || e.keyCode == 8){
            $(this).val("");
        }
    });

    //キャンセル確認ダイアログの初期化
    $('#dialog').dialog({
        autoOpen: false,
        draggable: false,
        modal: true
    });

    $('#reload').on('click', '.cancel_btn', function() {
        var cancelUrl = $(this).attr("href");
        //キャンセル確認ダイアログを表示
        $('#dialog').dialog({
            autoOpen: true,
            buttons: {
                'はい':function() {
                    //キャンセルを実行
                    window.location.href = cancelUrl;
                },
                'いいえ':function() {
                    //ダイアログを閉じる
                    $('#dialog').dialog('close');
                }
            }
        });
    });
});
