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
</head>
<body>
<div id="container">
<div id="main">
{include file="include/mng_header.html"}
{include file="include/jsng.html"}
<div id="jsok" style="display:none;">
{include file="include/mng_menu.html"}
<p class="breadcrumb">設定 ＞ ログインパスワード</p>
<div id="contents">
<div id="password_change">
<h2>ログインパスワード変更</h2>
<p>半角英数字6～10文字で設定してください</p>
<p class="message">{$message}</p>
<form method="post" action="../mng/confpasswordchange" enctype="multipart/form-data">
<table>
    <tr><th>現在のパスワード</th><td><input id="password" type="password" name="now_password" value="{$nowPassword}" maxlength="20"></input></td></tr>
    <tr><th>新しいパスワード</th><td><input id="password" type="password" name="new_password" value="{$newPassword}" maxlength="10"></input></td></tr>
    <tr><th>新しいパスワード（確認）</th><td><input id="password" type="password" name="conf_password" value="{$confPassword}" maxlength="10"></input></td></tr>
</table>
<input class="btn" type="submit" value="確認" />
</form>
</div>
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
{include file="include/mng_footer.html"}
</div><!-- container -->
</body>
</html>
