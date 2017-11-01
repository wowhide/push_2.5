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
<div id="contents">
<h2>ログイン</h2>
<p class="message">{$message}</p>
<form method="post" action="../mng/login">
    <p>ID<br>
    <input id="id" type="text" name="id" value="{$id}" maxlength="20" style="ime-mode: disabled;"></input></p>
    <p>パスワード<br>
    <input id="password" type="password" name="password" value="{$password}" maxlength="20"></input></p>
    <p><input type="checkbox" name="autologin" {$checked}>&nbsp;次回から自動ログインする</input></p>
    <p class="note"><span class="aka">※</span>自動ログインにチェックを入れてログインすると、次回からログイン画面を通らずに当システムをご利用頂けます。<br>
    セキュリティにご注意下さい。</p>
    <p class="note"><span class="aka">※</span>ログアウトした場合は、再度ログインする必要があります。</p>
    <p class="note">
        <span class="aka">※</span>
        パスワードを忘れた場合は、<a href="../mng/dispinquirypassword">こちら</a>からお問い合わせ下さい。</p>
    <p><input class="btn" type="submit" value="ログイン"></input></p>
</form>


</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
{include file="include/mng_footer.html"}
</div><!-- container -->
</body>
</html>
