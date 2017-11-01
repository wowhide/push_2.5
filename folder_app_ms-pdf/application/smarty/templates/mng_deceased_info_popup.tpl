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
<script type="text/javascript" src="../../js/deceased-info.js"></script>
</head>
<body>
<div id="container">
<div id="main">
<div id="jsok" style="display:none;">
<div id="contents">
<div id="deceased">
<h2>故人様情報</h2>
<table>
    <tr><th>担当者名</th>
        <td>
            {if strlen($chargeName) > 0}
                {$chargeName|escape}　様
            {else}
                －
            {/if}
        </td>
    </tr>
    <tr><th>会館名</th>
        <td>
            {if strlen($hallName) > 0}
                {$hallName|escape}
            {else}
                －
            {/if}
        </td>
    </tr>
    <tr><th>故人様名</th><td>{$deceasedName|escape}　様</td></tr>
    <tr><th>生年月日</th><td>{date('Y/m/d', strtotime($deceasedBirthday))}</td></tr>
    <tr><th>没年月日</th><td>{date('Y/m/d', strtotime($deceasedDeathday))}</td></tr>
    <tr><th>没年齢</th><td>{$kyonenGyonen}&nbsp;{$deathAge}　歳</td></tr>
    <tr><th>写真</th>
        <td>
            {if $imageExistenceFlg == 1}
                <img src="../mng/readdeceasedimage?did={$deceasedId}&c={$cacheKey}" width=200 />
            {else}
                －
            {/if}
        </td>
    </tr>
</table>

<div style="text-align: center;">
<input class="btn" type="button" value="閉じる" onclick="window.close();" />
</div>

</div><!-- deceased -->
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
{include file="include/mng_footer.html"}
</div><!-- container -->
</body>
</html>
