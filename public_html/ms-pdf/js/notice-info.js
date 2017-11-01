$(function() {
    //Datepickerの初期化
    $("#datepicker").datepicker({
        showOn: 'both',
        minDate: '+1d',
        maxDate: '+2m'
    });
    
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
    
    //登録方法変更時の画面制御
    $('[name="entry_method"]').change(function() {
        if ($(this).val() === "1") {
            //タイトル、本文、画像選択を表示
            $('.display_control').show();
            //URL入力欄のタイトルを変更
            $('#url').html("お知らせからリンク<br />させたいURL（任意）");
        } else {
            //タイトル、本文、画像選択を非表示
            $('.display_control').hide();
            //本文の入力値をクリア
            $('[name="notice_text"]').val("");
            //画像表示を削除する
            $('#image').remove();
            //画像の入力値をクリア
            $('[name="notice_image"]').replaceWith('<input type="file" name="notice_image" />');
            //画像選択のフラグ値をクリア
            $('[name="image_existence_flg"]').val("0");
            //URL入力欄のタイトルを変更
            $('#url').html("お知らせに表示する<br />ページのURL（必須）");
        }
    });
    
    //画像削除ボタンクリック時（通知情報登録時）
    $('#image_clear').click(function() {
        //画像の入力値をクリア
        $('[name="notice_image"]').replaceWith('<input type="file" name="notice_image" />');
        //画像選択のフラグ値をクリア
        $('[name="image_existence_flg"]').val("0");
        //画像表示を削除する
        $('#image').remove();
    });

    //削除ボタンクリック時
    $('#notice_info_del').click(function() {
        //削除確認ダイアログを表示
        $('#dialog').dialog({
            autoOpen: true
        });
    });
    
    //プレビューボタンクリック時
    $('#preview').click(function() {
        var url = "../mng/viewnoticeinfo?nino=" + $('[name="notice_info_no"]').val() + "&ispreview=1";
        var x = (screen.width - 320) / 2;
        var y = (screen.height - 480) / 2;
        var option = "width=320,height=480," + "left=" + x + ",top=" + y + 
                     ",menubar=no,toolbar=no,location=no,resizable=yes,scrollbars=yes";
	window.open(url,"お知らせ",option);
	return false;
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

/* 検索ボタンクリック時のエラー処理 */
function searchExecute(){
    if(document.getElementById("search_category3").checked){
        //命日の入力チェック
        var result = true;
        if(!document.getElementById("search_year").value.match(/^[0-9]*$/)) result = false;
        if(!document.getElementById("search_month").value.match(/^[0-9]*$/)) result = false;
        if(!document.getElementById("search_day").value.match(/^[0-9]*$/)) result = false;
        
        if(!result){
            alert("命日には数字を入力してください。");
            return false;
        }
    }else if(document.getElementById("search_category4").checked){
        var deathMonth = document.getElementById("death_day_combo").value;
        if(deathMonth === ""){
            alert("命日月を選択してください。");
            return false;
        }
    }else if(document.getElementById("search_category5").checked){
        var inputStat = true;
        var memorialYear  = document.getElementById("memorial_year_combo").value;
        var memorialMonth = document.getElementById("memorial_month_combo").value;
        var memorialEvent = document.getElementById("memorial_combo").value
        if(memorialYear  === "") inputStat = false;
        if(memorialMonth === "") inputStat = false;
        if(memorialEvent === "") inputStat = false;

        if(!inputStat){
            alert("法要を条件に検索する場合は、3つの項目をすべて選択してください。");
            return false;
        }

        var currentDate = new Date();
        if(parseInt(memorialYear) == currentDate.getFullYear()){
            if(parseInt(memorialMonth) < currentDate.getMonth() + 1){
                alert("過去の法要予定月を条件に故人様を検索しました。\n検索条件をご確認ください。");
            }
        }
    }

    return true;
}

function undisabled() {
    document.getElementById('charge_name_combo').disabled = null;
    document.getElementById('hall_name_combo').disabled =null;
    document.getElementById('search_name').disabled = null;
    document.getElementById('search_year').disabled = null;
    document.getElementById('search_month').disabled = null;
    document.getElementById('search_day').disabled = null;
    document.getElementById('death_day_combo').disabled = null;
    document.getElementById('memorial_year_combo').disabled = null;
    document.getElementById('memorial_month_combo').disabled = null;
    document.getElementById('memorial_combo').disabled = null;
}

/* 故人情報ポップアップ表示処理 */
function openDeceasedInfo(targetUrl) {
    window.open(targetUrl,'','width=500,height=650,scrollbars=yes');
}

/* 故人様リストポップアップ表示処理 */
function openDeceasedList(targetUrl){
    window.open(targetUrl, '', 'width=550,height=400,scrollbars=yes');
}
