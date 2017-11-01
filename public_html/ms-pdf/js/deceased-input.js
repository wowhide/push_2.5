$(function() {
    // 担当者リストを非表示にする
    $('ul#dropdown').hide();
    // 担当者リストコンボの表示制御
    $('input#charge_name').click(function() {
        $('ul#dropdown').toggle();
    });
    $('input#charge_name').blur(function() {
        $('ul#dropdown').hide();
    });
    $('input#charge_name').keydown(function() {
        $('ul#dropdown').hide();
    });
    // 担当者リストを選択した時のイベント
    $('ul#dropdown li').on('mousedown', function() {
        // リストを非表示にする
        $('ul#dropdown').hide();
        // テキスト値取得
        var value = $(this).text();
        // 担当者入力欄に選択値を設定
        $('input#charge_name').val(value);
    });
    //画像削除ボタンクリック時（通知情報登録時）
    $('#image_clear').click(function() {
        //画像の入力値をクリア
        $('[name="deceased_image"]').replaceWith('<input type="file" name="deceased_image" />');
        //画像選択のフラグ値をクリア
        $('[name="image_existence_flg"]').val("");
        //画像表示を削除する
        $('#image').remove();
    });
    //葬家様入力時、故人様の苗字を自動入力する
    $('#souke').change(function() {
        if ($('#deceased_last_name').val() == "") {
            $('#deceased_last_name').val($('#souke').val());
        }
    });
    //生年月日、没年月日変更時に没年齢を計算する
    $('#deceased_birthday_y').change(function() {
        calcAge();
    });
    $('#deceased_birthday_m').change(function() {
        calcAge();
    });
    $('#deceased_birthday_d').change(function() {
        calcAge();
    });
    $('#deceased_deathday_y').change(function() {
        calcAge();
    });
    $('#deceased_deathday_m').change(function() {
        calcAge();
    });
    $('#deceased_deathday_d').change(function() {
        calcAge();
    });
    
    //没年月に初期値を設定する
    if ($('#deceased_deathday_y').val() === "") {
        now = new Date();
        $('#deceased_deathday_y').val(now.getFullYear());
    }
    if ($('#deceased_deathday_m').val() === "") {
        now = new Date();
        $('#deceased_deathday_m').val(now.getMonth()+1);
    }
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

/*没年齢計算*/
function calcAge() {
    //没年月日－生年月日を計算する
    //((YYYYMMDD)-(YYYYMMDD)/10000)
    var birthdayY = parseInt($('#deceased_birthday_y').val());
    var birthdayM = parseInt($('#deceased_birthday_m').val());
    var birthdayD = parseInt($('#deceased_birthday_d').val());
    var deathdayY = parseInt($('#deceased_deathday_y').val());
    var deathdayM = parseInt($('#deceased_deathday_m').val());
    var deathdayD = parseInt($('#deceased_deathday_d').val());

    if ($.isNumeric(birthdayY) &&
        $.isNumeric(birthdayM) &&
        $.isNumeric(birthdayD) &&
        $.isNumeric(deathdayY) &&
        $.isNumeric(deathdayM) &&
        $.isNumeric(deathdayD)) {
        birthday = [("0000" + birthdayY).slice(-4),
                    ("00" + birthdayM).slice(-2),
                    ("00" + birthdayD).slice(-2)].join("");
        deathday = [("0000" + deathdayY).slice(-4),
                    ("00" + deathdayM).slice(-2),
                    ("00" + deathdayD).slice(-2)].join("");
        $('#age').val(Math.floor((deathday - birthday) / 10000 ));
    }
}
