<?php /* Smarty version Smarty-3.1.19, created on 2016-06-28 14:37:01
         compiled from "../../folder_app_ms-pdf/application/smarty/templates/mng_deceased_info_edit.tpl" */ ?>
<?php /*%%SmartyHeaderCode:143405215357720cfd93e7e8-33350215%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0334294de962c80feb70ab5613ef5f05d9b579fb' => 
    array (
      0 => '../../folder_app_ms-pdf/application/smarty/templates/mng_deceased_info_edit.tpl',
      1 => 1466578842,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '143405215357720cfd93e7e8-33350215',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'deceasedId' => 0,
    'message' => 0,
    'chargeList' => 0,
    'chargeSelected' => 0,
    'souke' => 0,
    'deceasedLastName' => 0,
    'deceasedFirstName' => 0,
    'deceasedBirthdayY' => 0,
    'deceasedBirthdayM' => 0,
    'deceasedBirthdayD' => 0,
    'deceasedDeathdayY' => 0,
    'deceasedDeathdayM' => 0,
    'deceasedDeathdayD' => 0,
    'checked1' => 0,
    'checked2' => 0,
    'checked3' => 0,
    'checked4' => 0,
    'deathAge' => 0,
    'checked5' => 0,
    'checked6' => 0,
    'hallName' => 0,
    'imageExistenceFlg' => 0,
    'cacheKey' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_57720cfda833b3_97529966',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_57720cfda833b3_97529966')) {function content_57720cfda833b3_97529966($_smarty_tpl) {?><?php if (!is_callable('smarty_function_html_options')) include '/home/h-yamato/hyamato.net/library/Smarty/libs/plugins/function.html_options.php';
?><!DOCTYPE html>
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
<script type="text/javascript" src="../../js/deceased-input.js"></script>
</head>
<body>
<div id="container">
<div id="main">
<?php echo $_smarty_tpl->getSubTemplate ("include/mng_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate ("include/jsng.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<div id="jsok" style="display:none;">
<?php echo $_smarty_tpl->getSubTemplate ("include/mng_menu.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<p class="breadcrumb"><a href="../mng/dispdeceasedlist">故人様一覧</a> ＞ <a href="../mng/dispdeceasedinfo?did=<?php echo $_smarty_tpl->tpl_vars['deceasedId']->value;?>
">故人様表示</a> ＞ 故人様編集</p>
<div id="contents">
<div id="deceased_edit">
<h2>故人様編集</h2>
<p class="input_caution">※パソコンで入力できない旧漢字等の文字や環境依存文字は使用できませんのでご了承ください。</p>
<p class="message"><?php echo $_smarty_tpl->tpl_vars['message']->value;?>
</p>
<form method="post" action="../mng/confeditdeceasedinfo" enctype="multipart/form-data" onSubmit="return double()">
<table>
    <tr><th>担当者様（必須）</th>
        <td>
            <?php echo smarty_function_html_options(array('name'=>'charge_name','options'=>$_smarty_tpl->tpl_vars['chargeList']->value,'selected'=>$_smarty_tpl->tpl_vars['chargeSelected']->value),$_smarty_tpl);?>
 様
        </td>
    </tr>
    <tr><th>葬家様名（必須）</th>
        <td><input id="souke" type="text" name="souke" value="<?php echo $_smarty_tpl->tpl_vars['souke']->value;?>
" maxlength="10" style="ime-mode: active;" />　家様</td></tr>
    <tr><th>故人様名（必須）</th>
        <td>
            <input id="deceased_last_name" type="text" name="deceased_last_name" value="<?php echo $_smarty_tpl->tpl_vars['deceasedLastName']->value;?>
" maxlength="10" style="ime-mode: active;" />
            <input id="deceased_first_name" type="text" name="deceased_first_name" value="<?php echo $_smarty_tpl->tpl_vars['deceasedFirstName']->value;?>
" maxlength="10" style="ime-mode: active;" />　様
            <span style="margin-left:30px;">※苗字は編集できます。必要に応じて修正してください。</span>
        </td>
    </tr>
    <tr><th>生年月日（必須）</th>
        <td>
            西暦&nbsp;<input id="deceased_birthday_y" type="text" name="deceased_birthday_y" value="<?php echo $_smarty_tpl->tpl_vars['deceasedBirthdayY']->value;?>
" maxlength="4" style="ime-mode: disabled;" />&nbsp;年
            <input id="deceased_birthday_m" type="text" name="deceased_birthday_m" value="<?php echo $_smarty_tpl->tpl_vars['deceasedBirthdayM']->value;?>
" maxlength="2" style="ime-mode: disabled;" />&nbsp;月
            <input id="deceased_birthday_d" type="text" name="deceased_birthday_d" value="<?php echo $_smarty_tpl->tpl_vars['deceasedBirthdayD']->value;?>
" maxlength="2" style="ime-mode: disabled;" />&nbsp;日
        </td>
    </tr>
    <tr><th>没年月日（必須）</th>
        <td>
            西暦&nbsp;<input id="deceased_deathday_y" type="text" name="deceased_deathday_y" value="<?php echo $_smarty_tpl->tpl_vars['deceasedDeathdayY']->value;?>
" maxlength="4" style="ime-mode: disabled;" />&nbsp;年
            <input id="deceased_deathday_m" type="text" name="deceased_deathday_m" value="<?php echo $_smarty_tpl->tpl_vars['deceasedDeathdayM']->value;?>
" maxlength="2" style="ime-mode: disabled;" />&nbsp;月
            <input id="deceased_deathday_d" type="text" name="deceased_deathday_d" value="<?php echo $_smarty_tpl->tpl_vars['deceasedDeathdayD']->value;?>
" maxlength="2" style="ime-mode: disabled;" />&nbsp;日
        </td>
    </tr>
    <tr><th>享年行年（必須）</th>
        <td>
            <input type="radio" name="kyonen_gyonen_flg" value="1" <?php echo $_smarty_tpl->tpl_vars['checked1']->value;?>
>&nbsp;享年&nbsp;&nbsp;
            <input type="radio" name="kyonen_gyonen_flg" value="2" <?php echo $_smarty_tpl->tpl_vars['checked2']->value;?>
>&nbsp;行年&nbsp;&nbsp;
            <input type="radio" name="kyonen_gyonen_flg" value="3" <?php echo $_smarty_tpl->tpl_vars['checked3']->value;?>
>&nbsp;満&nbsp;&nbsp;
            <input type="radio" name="kyonen_gyonen_flg" value="4" <?php echo $_smarty_tpl->tpl_vars['checked4']->value;?>
>&nbsp;なし
        </td></tr>
    <tr><th>没年齢（必須）</th>
        <td><input id="age" type="text" name="death_age" value="<?php echo $_smarty_tpl->tpl_vars['deathAge']->value;?>
" maxlength="3" style="ime-mode: disabled;" />　歳<span style="margin-left:30px;">※自動で表示される没年齢は、<span style ="color:red;">満年齢</span>です。必要に応じて手入力してください。</span></td></tr>
    <tr><th>プッシュ通知(必須)</th>
        <td>
            <input type="radio" name="allow_push" value="1" <?php echo $_smarty_tpl->tpl_vars['checked5']->value;?>
>&nbsp;通知する&nbsp;&nbsp;
            <input type="radio" name="allow_push" value="2" <?php echo $_smarty_tpl->tpl_vars['checked6']->value;?>
>&nbsp;通知しない&nbsp;&nbsp;
        </td>
    </tr>
    <tr><th>会館（任意）</th>
        <td>
            <input id="hall_name" type="text" name="hall_name" value="<?php echo $_smarty_tpl->tpl_vars['hallName']->value;?>
" maxlength="127" style="ime-mode: active;" /></td></tr>
    <tr><th>写真（任意）</th>
        <td>
<?php if ($_smarty_tpl->tpl_vars['imageExistenceFlg']->value==1) {?>
            <p id="image"><img src="../mng/readdeceasedtempimage?<?php echo $_smarty_tpl->tpl_vars['cacheKey']->value;?>
" width=200 /><br></p>
<?php }?>
            <input type="hidden" name="MAX_FILE_SIZE" value="10485760" />
            <input type="file" name="deceased_image" /><br>
            <span class="input_caution">※10MB以内</span><br>
            <input id="image_clear" type="button" value="写真を削除する" />
            <input type="hidden" name="image_existence_flg" value="<?php echo $_smarty_tpl->tpl_vars['imageExistenceFlg']->value;?>
" />
        </td></tr>
</table>

<div class="btn_row">
<input type="hidden" name="deceased_id" value="<?php echo $_smarty_tpl->tpl_vars['deceasedId']->value;?>
" />
<input class="btn" type="submit" name="back" value="戻る" />
<input class="btn" type="submit" name="confirm" value="確認" />
</form>
</div><!-- btn_row -->

</div>
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
<?php echo $_smarty_tpl->getSubTemplate ("include/mng_footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

</div><!-- container -->
</body>
</html>
<?php }} ?>
