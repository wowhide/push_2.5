<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>KUYOアプリ管理システム</title>
<meta name="keywords" content="">
<meta name="description" content="">
<meta name="robots" content="noindex,nofollow"> 
<meta name="author" content="株式会社デジタルスペースワウ" />
<link rel="stylesheet" type="text/css" media="screen" href="../../css/smoothness/jquery-ui-1.10.4.custom.min.css" />
<script type="text/javascript" src="../../js/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="../../js/jquery-ui-1.10.4.custom.min.js"></script>
<script type="text/javascript" src="../../js/jquery.ui.datepicker-ja.js"></script>
<script type="text/javascript">
//<![CDATA[
$(function(){
    //Datepickerの初期化
    $(".datepicker").datepicker({
        showOn: 'both'
    });
    //集計期間の日付はBackspaceとDeleteで削除する
    $('.datepicker').keyup(function(e) {
        if (e.keyCode == 46 || e.keyCode == 8){
            $(this).val("");
        }
    });
});
//]]>
</script>
</head>
<body>
<h1>KUYOアプリ版QRコード受注集計システム</h1>
<h2>受注明細ダウンロード</h2>
<p>{$message}</p>
<form method="post" action="../addup/download">
    <p>集計期間<br>
    <input class="datepicker" type="text" name="from" value="{$from}" readonly="readonly" placeholder="From" />
        ～
    <input class="datepicker" type="text" name="to" value="{$to}" readonly="readonly" placeholder="To" /></p>
    <p>アプリダウンロード数<br>
    Android版<br><input type="text" name="android_dl" value="{$android_dl}" maxlength="10" style="ime-mode: disabled;" /><br>
    iOS版<br><input type="text" name="ios_dl" value="{$ios_dl}" maxlength="10" style="ime-mode: disabled;" /></p>
    <p><input type="submit" value="ダウンロード"></p>
</form>
<form method="get" action="../addup/logout">
    <p><input type="submit" value="ログアウト"></p>
</form>
</body>
</html>