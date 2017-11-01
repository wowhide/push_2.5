<?php /* Smarty version Smarty-3.1.19, created on 2016-08-08 11:40:39
         compiled from "../../folder_app_ms-pdf/application/smarty/templates/mng_notice_info_list.tpl" */ ?>
<?php /*%%SmartyHeaderCode:68141745757a7f1271e6042-66727415%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'bcb039b19b94941232c851d70f58740372d5778d' => 
    array (
      0 => '../../folder_app_ms-pdf/application/smarty/templates/mng_notice_info_list.tpl',
      1 => 1466578845,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '68141745757a7f1271e6042-66727415',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'now' => 0,
    'all' => 0,
    'total' => 0,
    'firstItemNumber' => 0,
    'lastItemNumber' => 0,
    'pagesInRange' => 0,
    'page' => 0,
    'noticeInfoList' => 0,
    'noticeInfo' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_57a7f1272f3659_10872793',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_57a7f1272f3659_10872793')) {function content_57a7f1272f3659_10872793($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_date_format')) include '/home/h-yamato/hyamato.net/library/Smarty/libs/plugins/modifier.date_format.php';
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
</head>
<body>
<div id="container">
<div id="main">
<?php echo $_smarty_tpl->getSubTemplate ("include/mng_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate ("include/jsng.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<div id="jsok" style="display:none;">
<?php echo $_smarty_tpl->getSubTemplate ("include/mng_menu.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<div id="contents">
<h2>通知情報一覧</h2>
<p class="entry_btn"><a class="btn" href="../mng/dispentrynoticeinfo">通知情報登録</a></p>

<p class="page"><?php echo $_smarty_tpl->tpl_vars['now']->value;?>
/<?php echo $_smarty_tpl->tpl_vars['all']->value;?>
ページ　<?php echo $_smarty_tpl->tpl_vars['total']->value;?>
件中<?php echo $_smarty_tpl->tpl_vars['firstItemNumber']->value;?>
～<?php echo $_smarty_tpl->tpl_vars['lastItemNumber']->value;?>
件を表示</p>
<p class="page">
<?php  $_smarty_tpl->tpl_vars["page"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["page"]->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['pagesInRange']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["page"]->key => $_smarty_tpl->tpl_vars["page"]->value) {
$_smarty_tpl->tpl_vars["page"]->_loop = true;
?>
    <?php if ($_smarty_tpl->tpl_vars['page']->value==$_smarty_tpl->tpl_vars['now']->value) {?>
        <?php echo $_smarty_tpl->tpl_vars['page']->value;?>
&nbsp;&nbsp;
    <?php } else { ?>
        <a href="../mng/dispnoticeinfolist?page=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['page']->value;?>
</a>&nbsp;&nbsp;
    <?php }?>
<?php } ?>
</p>

<table id="notice_list">
    <tr><th class="notice_day">通知（予定）日</th><th class="entry_method">登録方法</th><th class="notice_contents">タイトル</th><th class="entry_datetime">登録日時</th><th class="disp_link"></th></tr>
<?php  $_smarty_tpl->tpl_vars["noticeInfo"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["noticeInfo"]->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['noticeInfoList']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["noticeInfo"]->key => $_smarty_tpl->tpl_vars["noticeInfo"]->value) {
$_smarty_tpl->tpl_vars["noticeInfo"]->_loop = true;
?>
    <?php if ($_smarty_tpl->tpl_vars['noticeInfo']->value['notice_flg']=="0") {?>
    <tr>
    <?php } else { ?>
    <tr class="notice_done">    
    <?php }?>
        <td><?php echo smarty_modifier_date_format(strtotime(htmlspecialchars($_smarty_tpl->tpl_vars['noticeInfo']->value['notice_schedule'], ENT_QUOTES, 'UTF-8', true)),"%Y/%m/%d");?>
</td>
    <?php if ($_smarty_tpl->tpl_vars['noticeInfo']->value['entry_method']=="1") {?>
        <td>入力</td>
    <?php } else { ?>
        <td>URL指定</td>
    <?php }?>
        <td><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['noticeInfo']->value['notice_title'], ENT_QUOTES, 'UTF-8', true);?>
</td>
        <td><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['noticeInfo']->value['entry_datetime'],"%Y/%m/%d %H:%M");?>
</td>
        <td class="disp_link"><a class="btn_mini" href="../mng/dispnoticeinfo?nino=<?php echo $_smarty_tpl->tpl_vars['noticeInfo']->value['notice_info_no'];?>
">表示</a></td>
    </tr>
<?php } ?>
</table>
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
<?php echo $_smarty_tpl->getSubTemplate ("include/mng_footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

</div><!-- container -->
</body>
</html><?php }} ?>
