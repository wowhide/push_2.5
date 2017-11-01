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
<script type="text/javascript" src="../../js/notice-info.js"></script>
</head>
<body>
<div id="container">
<div id="main">
<div id="jsok" style="display:none;">
<div id="contents">
<div id="notice_entry">
<!-- 故人一覧表示テーブル -->
<div id="deceased_table_area">
    <h2>通知対象の故人様： {count($deceasedInfoList)}名</h2>
    故人様名をクリックすると詳細を確認できます。
    <table id="deceased_table">
        <thead id="deceased_table_header">
            <tr>
                <th id="name_column">故人様名</th>
                <th id="deathday_column">命日</th>
            </tr>
        </thead>
        <tbody id="deceased_table_body" style="height: 160px;">
            {foreach from=$deceasedInfoList item="deceasedInfo"}
            <tr>
                <td id="deceased_name">
                    <a href="../mng/dispdeceasedinfopopup?did={$deceasedInfo.deceased_id}" onclick="javascript:openDeceasedInfo(this.href);return false;">{$deceasedInfo.deceased_name}&nbsp;様</a>
                </td>
                <td id="deceased_deathday">{date('Y/m/d', strtotime($deceasedInfo.deceased_deathday))}</td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>
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
