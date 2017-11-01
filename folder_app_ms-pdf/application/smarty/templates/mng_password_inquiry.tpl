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
<div id="contents">
<h2>パスワード問い合わせ</h2>
<p>IDを入力して「パスワードを送信する」ボタンをクリックして下さい。<br>
登録してあるメールアドレス宛にパスワードを送信します。</p>
<p class="message">{$message}</p>
<form method="post" action="../mng/compinquirypassword" enctype="multipart/form-data">
    <input type="hidden" name="token" value="{$token}">
    <p>
        <input id="id" type="text" name="id" value="{$id}" maxlength="20" style="ime-mode: disabled;" placeholder="ID"></input>
    </p>
    <p><input class="btn" type="submit" value="パスワードを送信する" /></p>
</form>
<p><a href="../mng">&lt;&lt;　ログイン画面に戻る</a></p>
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
{include file="include/mng_footer.html"}
</div><!-- container -->
</body>
</html>
