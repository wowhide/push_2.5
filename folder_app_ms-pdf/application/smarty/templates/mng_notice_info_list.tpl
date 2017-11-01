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
</head>
<body>
<div id="container">
<div id="main">
{include file="include/mng_header.html"}
{include file="include/jsng.html"}
<div id="jsok" style="display:none;">
{include file="include/mng_menu.html"}
<div id="contents">
<h2>通知情報一覧</h2>
<p class="entry_btn"><a class="btn" href="../mng/dispentrynoticeinfo">通知情報登録</a></p>

<p class="page">{$now}/{$all}ページ　{$total}件中{$firstItemNumber}～{$lastItemNumber}件を表示</p>
<p class="page">
{foreach from = $pagesInRange item = "page"}
    {if $page == $now}
        {$page}&nbsp;&nbsp;
    {else}
        <a href="../mng/dispnoticeinfolist?page={$page}">{$page}</a>&nbsp;&nbsp;
    {/if}
{/foreach}
</p>

<table id="notice_list">
    <tr><th class="notice_day">通知（予定）日</th><th class="entry_method">登録方法</th><th class="notice_contents">タイトル</th><th class="entry_datetime">登録日時</th><th class="disp_link"></th></tr>
{foreach from = $noticeInfoList item = "noticeInfo"}
    {if $noticeInfo.notice_flg == "0"}
    <tr>
    {else}
    <tr class="notice_done">    
    {/if}
        <td>{$noticeInfo.notice_schedule|escape|strtotime|date_format:"%Y/%m/%d"}</td>
    {if $noticeInfo.entry_method == "1"}
        <td>入力</td>
    {else}
        <td>URL指定</td>
    {/if}
        <td>{$noticeInfo.notice_title|escape}</td>
        <td>{$noticeInfo.entry_datetime|date_format:"%Y/%m/%d %H:%M"}</td>
        <td class="disp_link"><a class="btn_mini" href="../mng/dispnoticeinfo?nino={$noticeInfo.notice_info_no}">表示</a></td>
    </tr>
{/foreach}
</table>
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
{include file="include/mng_footer.html"}
</div><!-- container -->
</body>
</html>