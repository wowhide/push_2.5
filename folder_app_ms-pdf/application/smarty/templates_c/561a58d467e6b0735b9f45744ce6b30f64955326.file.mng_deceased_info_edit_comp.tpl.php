<?php /* Smarty version Smarty-3.1.19, created on 2016-07-12 09:26:32
         compiled from "../../folder_app_ms-pdf/application/smarty/templates/mng_deceased_info_edit_comp.tpl" */ ?>
<?php /*%%SmartyHeaderCode:2811089357843938bac011-19978423%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '561a58d467e6b0735b9f45744ce6b30f64955326' => 
    array (
      0 => '../../folder_app_ms-pdf/application/smarty/templates/mng_deceased_info_edit_comp.tpl',
      1 => 1466578842,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2811089357843938bac011-19978423',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'deceasedId' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_57843938bdbdf7_65912351',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_57843938bdbdf7_65912351')) {function content_57843938bdbdf7_65912351($_smarty_tpl) {?><!DOCTYPE html>
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
<h2>故人様編集完了</h2>
<p>編集が完了しました。</p>
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
<?php echo $_smarty_tpl->getSubTemplate ("include/mng_footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

</div><!-- container -->
</body>
</html>
<?php }} ?>
