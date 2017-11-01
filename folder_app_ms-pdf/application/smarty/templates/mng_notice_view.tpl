<!DOCTYPE HTML>
<html lang="ja">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width" />
<link rel="stylesheet" type="text/css" media="all" href="../../css/default.css">
<link rel="stylesheet" type="text/css" media="all" href="../../css/view.css?20140527">
</head>
<body>
<div id="container">
<div id="main">
<div id="contents">
<h2>{$noticeTitle|escape}</h2>
<p id="text">{$noticeText|escape|nl2br}</p>
{if empty($url) == false}
    <p id="url"><a href="{$url}">{$url}</a></p>
{/if}
{if $imageExistenceFlg == 1}
    <p id="image"><img src="../mng/readimage?nino={$noticeInfoNo}" /></p>
{/if}
<hr>
<p id="funeral"><b>株式会社デジタルスペースワウ</b><br>
〒981-0923<br>
宮城県仙台市青葉区東勝山3-5-10<br>
<a href="tel:0227270861">022-727-0861</a><br>
<a href="http://memorial-site.net">http://memorial-site.net</a></p>
</div>
</div>
<div id="foot">
<p>&copy; 2015 株式会社デジタルスペースワウ</p>
</div>
</div>
</body>
</html>
