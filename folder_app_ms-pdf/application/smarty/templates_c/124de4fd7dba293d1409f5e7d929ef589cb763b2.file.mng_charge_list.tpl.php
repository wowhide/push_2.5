<?php /* Smarty version Smarty-3.1.19, created on 2016-06-25 12:24:48
         compiled from "../../folder_app_ms-pdf/application/smarty/templates/mng_charge_list.tpl" */ ?>
<?php /*%%SmartyHeaderCode:618605631576df98001b942-56489675%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '124de4fd7dba293d1409f5e7d929ef589cb763b2' => 
    array (
      0 => '../../folder_app_ms-pdf/application/smarty/templates/mng_charge_list.tpl',
      1 => 1466578841,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '618605631576df98001b942-56489675',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'message' => 0,
    'chargeList' => 0,
    'charge' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_576df980086012_13617756',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_576df980086012_13617756')) {function content_576df980086012_13617756($_smarty_tpl) {?><!DOCTYPE html>
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
<script type="text/javascript" src="../../js/charge.js"></script>
</head>
<body>
<div id="container">
<div id="main">
<?php echo $_smarty_tpl->getSubTemplate ("include/mng_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate ("include/jsng.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<div id="jsok" style="display:none;">
<?php echo $_smarty_tpl->getSubTemplate ("include/mng_menu.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<p class="breadcrumb">設定 ＞ 担当者様</p>
<div id="contents">
<div id="charge">
<h2>担当者様一覧</h2>
<p class="message"><?php echo $_smarty_tpl->tpl_vars['message']->value;?>
</p>
<form method="post" action="../mng/addcharge" enctype="multipart/form-data">
    <input id="name" type="text" name="charge_name" value="" maxlength="50" style="ime-mode: active;" placeholder="担当者様名" />
    <input class="btn" type="submit" value="追加" />
</form>
<table>
    <tr><th>担当者様</th><th></th></tr>
<?php if (count($_smarty_tpl->tpl_vars['chargeList']->value)>0) {?>
    <?php  $_smarty_tpl->tpl_vars["charge"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["charge"]->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['chargeList']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["charge"]->key => $_smarty_tpl->tpl_vars["charge"]->value) {
$_smarty_tpl->tpl_vars["charge"]->_loop = true;
?>
    <tr>
        <td class="name"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['charge']->value['charge_name'], ENT_QUOTES, 'UTF-8', true);?>
　様</td>
        <td class="del_btn"><a class="btn_mini" href="../mng/delcharge?chargeno=<?php echo $_smarty_tpl->tpl_vars['charge']->value['charge_no'];?>
" onclick="return false;">削除</a></td>
    </tr>
    <?php } ?>
<?php } else { ?>
    <tr><td colspan="2">登録されていません</td></tr>
<?php }?>
</table>
</div>
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
<?php echo $_smarty_tpl->getSubTemplate ("include/mng_footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

</div><!-- container -->

<div id="dialog" title="担当者削除">
<p>担当者様を削除します。<br />よろしいですか？</p>
</div><!-- dialog -->

</body>
</html>
<?php }} ?>
