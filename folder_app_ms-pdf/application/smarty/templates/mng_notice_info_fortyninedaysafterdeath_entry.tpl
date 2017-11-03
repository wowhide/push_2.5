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
<p class="breadcrumb"><a href="../mng/dispnoticeinfolist">通知</a> ＞ 四十九日後</p>
<div id="contents">
<div id="notice_entry">
<form method="post" action="../mng/confentrynoticeinfo" enctype="multipart/form-data" onSubmit="return double()">
    <!-- 故人様の検索条件設定テーブル -->
    <div id="notice_setting_area">
        <h2>四十九日後</h2>
        <table id="notice_setting">
            <tr><th><input type="radio" id="search_category0" name="search_category" value="0" {$settingChecked0} /></th>
                <td>全ての故人様</td>
            </tr>
            <tr><th><input type="radio" id="search_category1" name="search_category" value="1" {$settingChecked1} /></th>
                <td>
                    担当者名&nbsp;{html_options id=charge_name_combo name=charge_name_combo options=$chargeList selected=$chargeName}&nbsp;様
                </td>
            </tr>
            <tr><th><input type="radio" id="search_category2" name="search_category" value="2" {$settingChecked2} /></th>
                <td>
                    会館名&nbsp;<span style="margin-left:1em">{html_options id=hall_name_combo name=hall_name_combo options=$hallList selected=$hallName}</span>
                </td>
            </tr>
            <tr><th><input type="radio" id="search_category3" name="search_category" value="3" {$settingChecked3} /></th>
                <td>
                    故人様名&nbsp;<input type="text" id="search_name" name="search_name" value="{$searchName}" />&nbsp;様
                    <div id="search_deathday" style="margin-top: 5px;">
                        命日
                        <span style="margin-left: 2em">
                            <input type="text" id="search_year"  name="search_year"  value="{$searchYear}"  maxlength="4" style="ime-mode: disabled;" />&nbsp;年&nbsp;
                            <input type="text" id="search_month" name="search_month" value="{$searchMonth}" maxlength="2" style="ime-mode: disabled;" />&nbsp;月&nbsp;
                            <input type="text" id="search_day"   name="search_day"   value="{$searchDay}"   maxlength="2" style="ime-mode: disabled;" />&nbsp;日&nbsp;
                        </span>
                    </div>
                </td>
            </tr>
            <tr><th><input type="radio" id="search_category4" name="search_category" value="4" {$settingChecked4} /></th>
                <td>{html_options id=death_day_combo name=death_day_combo options=$deathMonthList selected=$deathMonth} が命日の故人様</td>
            </tr>
            <tr><th><input type="radio" id="search_category5" name="search_category" value="5" {$settingChecked5} /></th>
                <td>
                    {html_options id=memorial_year_combo name=memorial_year_combo options=$memorialYearList selected=$memorialYear}の
                    {html_options id=memorial_month_combo name=memorial_month_combo options=$memorialMonthList selected=$memorialMonth}に
                    {html_options id=memorial_combo name=memorial_combo options=$memorialList selected=$memorialEvent}法要の故人様
                </td>
            </tr>
        </table>
        <div id="search_btn"><input type="submit" name="search" value="この条件で検索" onclick="javascript:if(!searchExecute())return false;" /></div>
        <input type="hidden" id="selected_category" name="selected_category" value="{$selectedCategory}" />
    </div>
    <!-- 故人一覧表示テーブル -->
    <div id="deceased_table_area">
        <h2 style="float: left;">条件に一致する故人様： {count($deceasedInfoList)}名</h2>
        <span style="float: right;">
            <input type="button" id="checkall" value="全てチェック" style="margin-right: 5px;" />
            <input type="button" id="uncheckall" value="チェックをクリア"/>
        </span>
        <table id="deceased_table" style="clear:both;">
            <thead id="deceased_table_header">
                <tr>
                    <th id="checkbox_column"></th>
                    <th id="name_column">故人様名</th>
                    <th id="deathday_column">命日</th>
                </tr>
            </thead>
            <tbody id="deceased_table_body">
                {foreach from=$deceasedInfoList item="deceasedInfo"}
                <tr>
                    {if $deceasedInfo.selected}
                    <td id="deceased_selection"><input type="checkbox" name="deceased_checkbox[]" value="{$deceasedInfo.deceased_id}" checked="checked" /></td>
                    {else}
                    <td id="deceased_selection"><input type="checkbox" name="deceased_checkbox[]" value="{$deceasedInfo.deceased_id}" /></td>
                    {/if}
                    <td id="deceased_name">
                        <a href="../mng/dispdeceasedinfopopup?did={$deceasedInfo.deceased_id}" onclick="javascript:openDeceasedInfo(this.href);return false;">{$deceasedInfo.deceased_name}&nbsp;様</a>
                    </td>
                    <td id="deceased_deathday">{date('Y/m/d', strtotime($deceasedInfo.deceased_deathday))}</td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        ※チェックされている故人様にお知らせが配信されます
    </div>
    <!-- 通知情報入力 -->
    <h2>通知情報登録</h2>
    <p class="message">{$message}</p>
    <table id="notice_input">
        <tr><th>通知予定日（必須）</th>
            <td><input id="datepicker" type="text" name="notice_schedule" value="{$noticeSchedule}" readonly="readonly" /><br>
                <span class="input_caution">※指定できる予定日は翌日～2か月後までです。</span></td></tr>
        <tr><th>登録方法（必須）</th>
            <td><input type="radio" name="entry_method" value="1" {$checked1}>&nbsp;通知情報を直接入力する&nbsp;&nbsp;<input type="radio" name="entry_method" value="2" {$checked2}>&nbsp;通知情報としてWebページを表示する</td></tr>
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