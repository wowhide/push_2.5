<?php /* Smarty version Smarty-3.1.19, created on 2016-06-24 17:10:16
         compiled from "../../folder_app_ms-pdf/application/smarty/templates/include/mng_menu.html" */ ?>
<?php /*%%SmartyHeaderCode:263792123576ceae8497f58-93389418%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e9a267675d4726d9625dabbebdc6a4d029f25b20' => 
    array (
      0 => '../../folder_app_ms-pdf/application/smarty/templates/include/mng_menu.html',
      1 => 1466578848,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '263792123576ceae8497f58-93389418',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_576ceae84da435_63882098',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_576ceae84da435_63882098')) {function content_576ceae84da435_63882098($_smarty_tpl) {?><div id="menu">
<ul>
    <li><a href="../mng/dispdeceasedlist">&gt;故人様一覧</a></li>
    <li><a href="../mng/dispnoticeinfolist">&gt;通知情報一覧</a></li>
    <li>設定
        <ul>
            <li><a href="../mng/dispchargelist">&gt;担当者様</a></li>
            <li><a href="../mng/disppasswordchange">&gt;ﾛｸﾞｲﾝﾊﾟｽﾜｰﾄﾞ</a></li>
        </ul>
    </li>
    <li><a href="../mng/logout">&gt;&gt;ログアウト</a></li>
</ul>
</div>
<div class="clear"></div><?php }} ?>
