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
<script type="text/javascript" src="../../js/deceased-input.js"></script>
</head>
<body>
<div id="container">
<div id="main">
{include file="include/mng_header.html"}
{include file="include/jsng.html"}
<div id="jsok" style="display:none;">
{include file="include/mng_menu.html"}
<p class="breadcrumb"><a href="../mng/dispdeceasedlist">故人様一覧</a> ＞ 故人様QR発注</p>
<div id="contents">
<div id="qr_order">
<h2>故人様QR発注確認</h2>
<p>下記内容で発注します。<br />よろしければ送信ボタンをクリックして下さい。</p>
<form method="post" action="../mng/compdeceasedqrorder" enctype="multipart/form-data" onSubmit="return double()">
    <table>
        <tr><th>担当者名</th><td>{$chargeName}　様</td></tr>
        <tr><th>葬家様名</th><td>{$souke}　家様</td></tr>
        <tr><th>故人様名</th><td>{$deceasedLastName}　{$deceasedFirstName}　様</td></tr>
        <tr><th>生年月日</th><td>{$deceasedBirthday}</td></tr>
        <tr><th>没年月日</th><td>{$deceasedDeathday}</td></tr>
        <tr><th>享年行年</th><td>{$kyonenGyonen}</td></tr>
        <tr><th>没年齢</th><td>{$deathAge}　歳</td></tr>
        <tr><th>プッシュ通知</th><td>{$allowPush}</td></tr>
        <tr><th>会館名</th><td>{$hallName}</td></tr>
        <tr><th>写真</th>
            <td>
{if $imageExistenceFlg == 1}
                <img src="../mng/readdeceasedtempimage?{$cacheKey}" width=200 />
{else}
                －
{/if}
            </td></tr>
    </table>
    <input class="btn" type="submit" name="back" value="戻る" />
    <input class="btn" type="submit" name="send" value="送信" />
    <input type="hidden" name="token" value="{$token}">
</form>
</div>
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
{include file="include/mng_footer.html"}
</div><!-- container -->
</body>
</html>
