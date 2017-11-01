<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>KUYOアプリ管理システム</title>
<meta name="keywords" content="">
<meta name="description" content="">
<meta name="robots" content="noindex,nofollow"> 
<meta name="author" content="株式会社デジタルスペースワウ" />
</head>
<body>
<h1>KUYOアプリQRコード受注集計システム</h1>
<h2>受注明細ダウンロード　ログイン</h2>
<p>{$message}</p>
<form method="post" action="../addup/login">
    <p>ID<br>
    <input type="text" name="id" value="{$id}" maxlength="20" style="ime-mode: disabled;"></input></p>
    <p>パスワード<br>
    <input type="password" name="password" value="{$password}" maxlength="20"></input></p>
    <p><input type="submit" value="ログイン"></input></p>
</form>
</body>
</html>
