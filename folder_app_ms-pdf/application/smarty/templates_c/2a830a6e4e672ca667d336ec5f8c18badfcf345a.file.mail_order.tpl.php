<?php /* Smarty version Smarty-3.1.19, created on 2016-06-25 12:26:19
         compiled from "../../folder_app_ms-pdf/application/smarty/templates/mail_order.tpl" */ ?>
<?php /*%%SmartyHeaderCode:977607424576df9db311286-88129396%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '2a830a6e4e672ca667d336ec5f8c18badfcf345a' => 
    array (
      0 => '../../folder_app_ms-pdf/application/smarty/templates/mail_order.tpl',
      1 => 1466578841,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '977607424576df9db311286-88129396',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'managerName' => 0,
    'chargeName' => 0,
    'souke' => 0,
    'deceasedName' => 0,
    'datetime' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_576df9db371e35_31663134',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_576df9db371e35_31663134')) {function content_576df9db371e35_31663134($_smarty_tpl) {?><?php echo $_smarty_tpl->tpl_vars['managerName']->value;?>
様よりQRコードの発注がありました。

担当者　：<?php echo $_smarty_tpl->tpl_vars['chargeName']->value;?>

葬家様　：<?php echo $_smarty_tpl->tpl_vars['souke']->value;?>

故人様　：<?php echo $_smarty_tpl->tpl_vars['deceasedName']->value;?>

発注日時：<?php echo $_smarty_tpl->tpl_vars['datetime']->value;?>

<?php }} ?>
