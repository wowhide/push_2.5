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
<p class="breadcrumb"><a href="../mng/dispentrynoticeinfodayafterdeath?ntype={$noticeTypeNumber}">{$noticeTypeTitle}登録表示</a> ＞{$noticeTypeTitle}通知情報編集完了</p>
<div id="contents">
<h2>{$noticeTypeTitle}通知情報編集完了</h2>
<p>下記の通り通知情報を更新しました。</p>
<h2>通知情報</h2>
<table id="notice_input">
    <tr>
        <th>タイトル</th>
        <td>{$noticeTitle|escape}</td>
    </tr>
{if $entryMethod == "1"}
    <tr><th>テンプレート</th>
        <td>{$template}</td>
        <input type="hidden" name="template_id" value="{$templateId}" />
    </tr>
    <tr>
        <th>本文</th>
        <td>{$noticeText|escape|nl2br}</td>
    </tr>
    <tr>
        <th>お知らせに表示<br />する画像</th>
        <td>
    {if $imageExistenceFlg == 1}
        <img src="../mng/readimage?{$cacheKey}" {$imgWH} />
    {else}
        －
    {/if}
        </td>
    </tr>
{/if}
    <tr>
{if $entryMethod == "1"}
        <th>お知らせからリンク<br />させたいURL</th>
{else}
        <th>お知らせに表示する<br />ページのURL</th>
{/if}
        <td>{$url}</td>
    </tr>
</table>
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
{include file="include/mng_footer.html"}
</div><!-- container -->
</body>
</html>
