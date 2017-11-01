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
{include file="include/mng_header.html"}
{include file="include/jsng.html"}
<div id="jsok" style="display:none;">
{include file="include/mng_menu.html"}
<p class="breadcrumb"><a href="../mng/dispdeceasedlist">故人様一覧</a> ＞ 故人様表示</p>
<div id="contents">
<div id="deceased">
<h2>故人様表示</h2>
<table>
    <tr><th>発注日時</th>
        <td>
            {if $issueStateCode == "1"}
                －
            {else}
                {date('Y/m/d G:i', strtotime($issueDatetime))}
            {/if}
        </td></tr>
    <tr><th>発行日</th>
        <td>
            {if strlen($entryDatetime > 0) }
                {date('Y/m/d', strtotime($entryDatetime))}
            {else}
                －
            {/if}
        </td>
    </tr>
    <tr><th>担当者名</th>
        <td>
            {if strlen($chargeName) > 0}
                {$chargeName|escape}　様
            {else}
                －
            {/if}
        </td>
    </tr>
    <tr><th>葬家様名</th>
        <td>
            {if strlen($souke) > 0}
                {$souke|escape}　家様
            {else}
                －
            {/if}
        </td>
    </tr>
    <tr><th>故人様名</th><td>{$deceasedName|escape}　様</td></tr>
    <tr><th>生年月日</th><td>{date('Y/m/d', strtotime($deceasedBirthday))}</td></tr>
    <tr><th>没年月日</th><td>{date('Y/m/d', strtotime($deceasedDeathday))}</td></tr>
    <tr><th>享年行年</th><td>{$kyonenGyonen}</td></tr>
    <tr><th>没年齢</th><td>{$deathAge}　歳</td></tr>
    <tr><th>プッシュ通知</th><td>{$allowPush}</td></tr>
    <tr><th>会館名</th>
        <td>
            {if strlen($hallName) > 0}
                {$hallName|escape}
            {else}
                －
            {/if}
        </td>
    </tr>
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

<div class="btn_row">
<form method="get" action="../mng/dispdeceasedreturn" onSubmit="return double()">
    <input class="btn" type="submit" value="戻る" />
</form>
{if $issueStateCode == "3"}
    <form id="del_form" method="post" action="../mng/deldeceasedinfo" onSubmit="return double()">
        <input type="hidden" name="deceased_id" value="{$deceasedId}">
        <input class="btn" id="deceased_info_del" type="button" value="削除" />
    </form>
    <form method="post" action="../mng/dispeditdeceasedinfo" onSubmit="return double()">
        <input type="hidden" name="deceased_id" value="{$deceasedId}">
        <input class="btn" type="submit" value="編集" />
    </form>
{/if}
</div><!-- btn_row -->

</div><!-- deceased -->
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
{include file="include/mng_footer.html"}
</div><!-- container -->

<div id="dialog" title="故人情報削除">
<p>故人情報を削除します。<br />よろしいですか？</p>
</div><!-- dialog -->

</body>
</html>
