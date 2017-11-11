<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>KUYOアプリ管理システム</title>
<meta name="keywords" content="">
<meta name="description" content="">
<meta name="robots" content="noindex,nofollow">
<meta name="author" content="株式会社デジタルスペースワウ" />
<link rel="stylesheet" type="text/css" media="all" href="../../css/default.css">
<link rel="stylesheet" type="text/css" media="all" href="../../css/layout.css">
<link rel="stylesheet" type="text/css" media="screen" href="../../css/smoothness/jquery-ui-1.10.4.custom.min.css" />
<script type="text/javascript" src="../../js/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="../../js/jquery-ui-1.10.4.custom.min.js"></script>
<script type="text/javascript" src="../../js/jquery.ui.datepicker-ja.js"></script>
<script type="text/javascript" src="../../js/notice-info.js"></script>
<script type="text/javascript">
//<![CDATA[
$(function() {
    //表示時にラジオボタンのクリックイベントを実行して入力欄の表示非表示を設定する
    var entryMethod = $('[name="entry_method"]:checked').val();
    if (entryMethod == 1) {
        $('[name="entry_method"]:eq(0)').click().change();
    } else {
        $('[name="entry_method"]:eq(1)').click().change();
    }

    //ラジオボタンの選択に合わせて検索部品を設定する
    updateNoticeSetting();

    //表示時に通知条件設定テーブルに合わせて故人一覧テーブルの高さを設定する
    var tableHeight = $('#notice_setting').height();
    var headerHeight = $('#deceased_table_header').height();
    $('#deceased_table_body').height(tableHeight - headerHeight);

    //全てチェックボタン押下で全ての故人様のチェックを付ける
    $('#checkall').on('click', function() {
        $('[name="deceased_checkbox[]"]').prop('checked', true);
    });

    //チェックをクリアボタン押下で全ての故人様のチェックを外す
    $('#uncheckall').on('click', function() {
        $('[name="deceased_checkbox[]"]').prop('checked', false);
    });

    //通知条件設定のラジオボタン選択時
    $('input[name="search_category"]:radio').change(function() {
        updateNoticeSetting();
    });

    //通知条件設定をラジオボタンに合わせて可不可切り替え
    function updateNoticeSetting() {
        $('#charge_name_combo').prop('disabled', true);
        $('#hall_name_combo').prop('disabled', true);
        $('#search_name').prop('disabled', true);
        $('#search_year').prop('disabled', true);
        $('#search_month').prop('disabled', true);
        $('#search_day').prop('disabled', true);
        $('#death_day_combo').prop('disabled', true);
        $('#memorial_year_combo').prop('disabled', true);
        $('#memorial_month_combo').prop('disabled', true);
        $('#memorial_combo').prop('disabled', true);

        switch($('input[name="search_category"]:checked').val()){
            case '1':
                $('#charge_name_combo').prop('disabled', false);
                break;
            case '2':
                $('#hall_name_combo').prop('disabled', false);
                break;
            case '3':
                $('#search_name').prop('disabled', false);
                $('#search_year').prop('disabled', false);
                $('#search_month').prop('disabled', false);
                $('#search_day').prop('disabled', false);
                break;
            case '4':
                $('#death_day_combo').prop('disabled', false);
                break;
            case '5':
                $('#memorial_year_combo').prop('disabled', false);
                $('#memorial_month_combo').prop('disabled', false);
                $('#memorial_combo').prop('disabled', false);
                break;
        }
    }
});
//]]>
</script>
</head>
<body>
<div id="container">
<div id="main">
{include file="include/mng_header.html"}
{include file="include/jsng.html"}
<div id="jsok" style="display:none;">
{include file="include/mng_menu.html"}
<p class="breadcrumb">{$noticeTypeTitle}登録</p>
<div id="contents">
<div id="notice_entry">
<form method="post" action="../mng/confentrynoticehoyoinfo" enctype="multipart/form-data" onSubmit="return double()">
    <!-- 故人様の検索条件設定テーブル -->
    <div id="notice_setting_area">
        <h2>{$noticeTypeTitle}</h2>
    </div>

    <!-- 通知情報入力 -->
    <h2>通知情報登録</h2>
    <p class="message">{$message}</p>
    <table id="notice_input">

        <input type="hidden" name="notice_type" value="{$noticeTypeNumber}" />

        {if $noticeTypeNumber == 7}
        <input type="hidden" name="notice_schedule" value="77777777" />
        {/if}
        {if $noticeTypeNumber == 14}
        <input type="hidden" name="notice_schedule" value="14141414" />
        {/if}
        {if $noticeTypeNumber == 21}
        <input type="hidden" name="notice_schedule" value="21212121" />
        {/if}
        {if $noticeTypeNumber == 28}
        <input type="hidden" name="notice_schedule" value="28282828" />
        {/if}
        {if $noticeTypeNumber == 35}
        <input type="hidden" name="notice_schedule" value="35353535" />
        {/if}
        {if $noticeTypeNumber == 42}
        <input type="hidden" name="notice_schedule" value="42424242" />
        {/if}
        {if $noticeTypeNumber == 49}
        <input type="hidden" name="notice_schedule" value="49494949" />
        {/if}

        <tr>
            <th>タイトル（必須）</th>
            <td><input id="title" type="text" name="notice_title" value="{$noticeTitle}" maxlength="50" style="ime-mode: active;" /><br>
                <span class="input_caution">※50文字以内</span></td>
        </tr>
        <tr class="display_control">
            <th>テンプレート<br>(自動入力)</th><td>{$template}</td>
            <input type="hidden" name="template_id" value="{$templateId}" />
        </tr>
        <tr class="display_control">
            <th>本文（必須）</th>
            <td><textarea id="text" name="notice_text" rows="15" maxlength="1000" style="ime-mode: active;">{$noticeText}</textarea><br>
                <span class="input_caution">※1000文字以内</span></td>
        </tr>
        <tr class="display_control">
            <th>お知らせに表示<br />する画像（任意）</th>
            <td>
                {if $imageExistenceFlg == 1}
                <p id="image"><img src="../mng/readimage?{$cacheKey}" {$imgWH} /><br></p>
                {/if}
                <input type="hidden" name="MAX_FILE_SIZE" value="10485760" />
                <input type="file" name="notice_image" /><br>
                <span class="input_caution">※10MB以内</span><br>
                <input id="image_clear" type="button" value="画像を削除する" />
                <input type="hidden" name="image_existence_flg" value="{$imageExistenceFlg}" />
            </td>
        </tr>
        <tr>
            <th id="url">お知らせからリンク<br />させたいURL（任意）</th>
            <td><input id="url" type="text" name="url" value="{$url}" maxlength="200" style="ime-mode: disabled;" /><br>
                <span class="input_caution">※URLは、「http://」もしくは「https://」から入力して下さい。</span><br>
                <span class="input_caution">※200文字以内</span></td>
        </tr>
    </table>
    <div class="btn_row">
        <input class="btn" type="submit" name="back" value="戻る" />
        <input class="btn" type="submit" name="conf" value="登録確認" onclick="javascript:undisabled();" />
    </div>
</form>
</div><!-- notice_entry -->
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
{include file="include/mng_footer.html"}
</div><!-- container -->
</body>
</html>
