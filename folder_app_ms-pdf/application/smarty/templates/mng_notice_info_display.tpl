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
</head>
<body>
<div id="container">
<div id="main">
{include file="include/mng_header.html"}
{include file="include/jsng.html"}
<div id="jsok" style="display:none;">
{include file="include/mng_menu.html"}
<p class="breadcrumb"><a href="../mng/dispnoticeinfolist">通知情報一覧</a> ＞ 通知情報表示</p>
<div id="contents">
<h2>通知情報表示</h2>
<table id="notice_disp">
    <tr><th>通知条件</th>
    {if $search_category == 0}
        <td> すべての故人様 </td>
    {/if}
    {if $search_category == 1}
        <td>担当者名：{$chargeName}&nbsp;様</td>
    {/if}
    {if $search_category == 2}
        <td>会館名：{$hallName}</td>
    {/if}
    {if $search_category == 3}
        <td>故人様名：{$searchName}&nbsp;&nbsp;命日：{$searchYMD}</td>
    {/if}
    {if $search_category == 4}
        <td>{$deathMonth}月が命日の故人様</td>
    {/if}
    {if $search_category == 5}
        <td>{$memorialYear}{$memorialMonth}に{$memorialEvent}法要の故人様</td>
    {/if}
    </tr>
    <tr><th>通知先故人様</th><td><a href="../mng/dispdeceasedlistpopup" onclick="javascript:openDeceasedList(this.href);return false;">{$targetCount}名様</a></td></tr>
    <tr><th>通知（予定）日</th><td>{$noticeSchedule|escape|strtotime|date_format:"%Y/%m/%d"}</td></tr>
{if $entryMethod == "1"}
    <tr><th>登録方法</th><td>通知情報を直接入力</td></tr>
    <tr><th>タイトル</th><td>{$noticeTitle|escape}</td></tr>
    <tr><th>テンプレート</th><td>{$template}</td></tr>
    <tr><th>本文</th><td>{$noticeText|escape|nl2br}</td></tr>
    <tr><th>お知らせに表示<br />する画像</th><td>
        {if $imageExistenceFlg == 1}
            <img src="../mng/readimage?nino={$noticeInfoNo}&{$cacheKey}" {$imgWH} />
        {else}
            －
        {/if}
    </td></tr>
    <tr><th>お知らせからリンク<br />させたいURL</th><td><a href="{$url}" target="_blank">{$url}</a></td></tr>
{else}
    <tr><th>登録方法</th><td>通知情報としてWebページを表示</td></tr>
    <tr><th>タイトル</th><td>{$noticeTitle|escape}</td></tr>
    <tr><th>お知らせに表示する<br />ページのURL</th><td><a href="{$url}" target="_blank">{$url}</a></td></tr>
{/if}
</table>
<div class="btn_row">
<form method="get" action="../mng/dispnoticeinfolist" onSubmit="return double()">
    <input type="hidden" name="page" value="{$page}">
    <input class="btn" type="submit" value="戻る" />
</form>
{if $noticeFlg == "0"}
<form id="del_form" method="post" action="../mng/delnoticeinfo" onSubmit="return double()">
    <input type="hidden" name="notice_info_no" value="{$noticeInfoNo}">
    <input class="btn" id="notice_info_del" type="button" value="削除" />
</form>
<form method="post" action="../mng/dispeditnoticeinfo" onSubmit="return double()">
    <input type="hidden" name="notice_info_no" value="{$noticeInfoNo}">
    <input class="btn" type="submit" value="編集" />
</form>
{/if}
{if $entryMethod == "1"}
<form>
    <input type="hidden" name="notice_info_no" value="{$noticeInfoNo}">
    <input id="preview" class="btn" type="button" value="プレビュー" />
</form>
{/if}
</div><!-- btn_row -->
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
{include file="include/mng_footer.html"}
</div><!-- container -->

<div id="dialog" title="通知情報削除">
<p>通知情報を削除します。<br />よろしいですか？</p>
</div><!-- dialog -->

</body>
</html>