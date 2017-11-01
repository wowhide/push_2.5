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
<script type="text/javascript" src="../../js/charge.js"></script>
</head>
<body>
<div id="container">
<div id="main">
{include file="include/mng_header.html"}
{include file="include/jsng.html"}
<div id="jsok" style="display:none;">
{include file="include/mng_menu.html"}
<p class="breadcrumb">設定 ＞ 担当者様</p>
<div id="contents">
<div id="charge">
<h2>担当者様一覧</h2>
<p class="message">{$message}</p>
<form method="post" action="../mng/addcharge" enctype="multipart/form-data">
    <input id="name" type="text" name="charge_name" value="" maxlength="50" style="ime-mode: active;" placeholder="担当者様名" />
    <input class="btn" type="submit" value="追加" />
</form>
<table>
    <tr><th>担当者様</th><th></th></tr>
{if count($chargeList) > 0}
    {foreach from = $chargeList item = "charge"}
    <tr>
        <td class="name">{$charge.charge_name|escape}　様</td>
        <td class="del_btn"><a class="btn_mini" href="../mng/delcharge?chargeno={$charge.charge_no}" onclick="return false;">削除</a></td>
    </tr>
    {/foreach}
{else}
    <tr><td colspan="2">登録されていません</td></tr>
{/if}
</table>
</div>
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
{include file="include/mng_footer.html"}
</div><!-- container -->

<div id="dialog" title="担当者削除">
<p>担当者様を削除します。<br />よろしいですか？</p>
</div><!-- dialog -->

</body>
</html>
