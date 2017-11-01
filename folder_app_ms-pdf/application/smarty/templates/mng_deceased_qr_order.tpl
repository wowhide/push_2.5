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
<h2>故人様QR発注</h2>
<p class="note"><span class="aka">※</span>QRコードを掲載したアプリのご案内は、ご発注から約30分～40分でダウンロード可能となります。</p>
<p class="note"><span class="aka">※</span>QRコードの発行には営業時間内（9:00～18:00）で、約30～40分お時間を頂いております。<br>
営業時間外にご発注頂いた場合や発行時間が営業時間を超える場合、翌日の発行になりますのでご了承願います。</p>
<p class="input_caution">※パソコンで入力できない旧漢字等の文字や環境依存文字は使用できませんのでご了承ください。</p>
<p class="message">{$message}</p>
<form method="post" action="../mng/confdeceasedqrorder" enctype="multipart/form-data" onSubmit="return double()">
<table>
    <tr><th>担当者様（必須）</th>
        <td>
            {html_options name=charge_name options=$chargeList selected=$chargeSelected} 様
        </td>
    </tr>
    <tr><th>葬家様名（必須）</th>
        <td><input id="souke" type="text" name="souke" value="{$souke}" maxlength="10" style="ime-mode: active;" />　家様</td></tr>
    <tr><th>故人様名（必須）</th>
        <td>
            <input id="deceased_last_name" type="text" name="deceased_last_name" value="{$deceasedLastName}" maxlength="10" style="ime-mode: active;" />
            <input id="deceased_first_name" type="text" name="deceased_first_name" value="{$deceasedFirstName}" maxlength="10" style="ime-mode: active;" />　様
            <span style="margin-left:30px;">※苗字は編集できます。必要に応じて修正してください。</span>
        </td>
    </tr>
    <tr><th>生年月日（必須）</th>
        <td>
            西暦&nbsp;<input id="deceased_birthday_y" type="text" name="deceased_birthday_y" value="{$deceasedBirthdayY}" maxlength="4" style="ime-mode: disabled;" />&nbsp;年
            <input id="deceased_birthday_m" type="text" name="deceased_birthday_m" value="{$deceasedBirthdayM}" maxlength="2" style="ime-mode: disabled;" />&nbsp;月
            <input id="deceased_birthday_d" type="text" name="deceased_birthday_d" value="{$deceasedBirthdayD}" maxlength="2" style="ime-mode: disabled;" />&nbsp;日
        </td>
    </tr>
    <tr><th>没年月日（必須）</th>
        <td>
            西暦&nbsp;<input id="deceased_deathday_y" type="text" name="deceased_deathday_y" value="{$deceasedDeathdayY}" maxlength="4" style="ime-mode: disabled;" />&nbsp;年
            <input id="deceased_deathday_m" type="text" name="deceased_deathday_m" value="{$deceasedDeathdayM}" maxlength="2" style="ime-mode: disabled;" />&nbsp;月
            <input id="deceased_deathday_d" type="text" name="deceased_deathday_d" value="{$deceasedDeathdayD}" maxlength="2" style="ime-mode: disabled;" />&nbsp;日
        </td>
    </tr>
    <tr><th>享年行年（必須）</th>
        <td>
            <input type="radio" name="kyonen_gyonen_flg" value="1" {$checked1}>&nbsp;享年&nbsp;&nbsp;
            <input type="radio" name="kyonen_gyonen_flg" value="2" {$checked2}>&nbsp;行年&nbsp;&nbsp;
            <input type="radio" name="kyonen_gyonen_flg" value="3" {$checked3}>&nbsp;満&nbsp;&nbsp;
            <input type="radio" name="kyonen_gyonen_flg" value="4" {$checked4}>&nbsp;なし
        </td></tr>
    <tr><th>没年齢（必須）</th>
        <td><input id="age" type="text" name="death_age" value="{$deathAge}" maxlength="3" style="ime-mode: disabled;" />　歳<span style="margin-left:30px;">※自動で表示される没年齢は、<span style ="color:red;">満年齢</span>です。必要に応じて手入力してください。</span></td></tr>
    <tr><th>プッシュ通知(必須)</th>
        <td>
            <input type="radio" name="allow_push" value="1" {$checked5}>&nbsp;通知する&nbsp;&nbsp;
            <input type="radio" name="allow_push" value="2" {$checked6}>&nbsp;通知しない&nbsp;&nbsp;
        </td>
    </tr>
    <tr><th>会館（任意）</th>
        <td>
            <input id="hall_name" type="text" name="hall_name" value="{$hallName}" maxlength="127" style="ime-mode: active;" /></td></tr>
    <tr><th>写真（任意）</th>
        <td>
{if $imageExistenceFlg == 1}
            <p id="image"><img src="../mng/readdeceasedtempimage?{$cacheKey}" width=200 /><br></p>
{/if}
            <input type="hidden" name="MAX_FILE_SIZE" value="10485760" />
            <input type="file" name="deceased_image" /><br>
            <span class="input_caution">※10MB以内</span><br>
            <input id="image_clear" type="button" value="写真を削除する" />
            <input type="hidden" name="image_existence_flg" value="{$imageExistenceFlg}" />
        </td></tr>
</table>
<input class="btn" type="submit" name="back" value="戻る" />
<input class="btn" type="submit" name="send" value="確認" />
</form>
</div>
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
{include file="include/mng_footer.html"}
</div><!-- container -->
</body>
</html>
