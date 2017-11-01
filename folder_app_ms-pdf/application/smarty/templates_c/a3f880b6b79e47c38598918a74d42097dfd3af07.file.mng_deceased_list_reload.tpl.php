<?php /* Smarty version Smarty-3.1.19, created on 2016-06-24 17:10:16
         compiled from "../../folder_app_ms-pdf/application/smarty/templates/mng_deceased_list_reload.tpl" */ ?>
<?php /*%%SmartyHeaderCode:2027837735576ceae84dd655-01167332%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a3f880b6b79e47c38598918a74d42097dfd3af07' => 
    array (
      0 => '../../folder_app_ms-pdf/application/smarty/templates/mng_deceased_list_reload.tpl',
      1 => 1466578843,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2027837735576ceae84dd655-01167332',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'deceasedInfoList' => 0,
    'deceasedInfo' => 0,
    'now' => 0,
    'all' => 0,
    'total' => 0,
    'firstItemNumber' => 0,
    'lastItemNumber' => 0,
    'pagesInRange' => 0,
    'page' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_576ceae85f98d9_56740513',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_576ceae85f98d9_56740513')) {function content_576ceae85f98d9_56740513($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_date_format')) include '/home/h-yamato/hyamato.net/library/Smarty/libs/plugins/modifier.date_format.php';
?><table id="deceased_list">
    <tr>
        <th class="deceased_name">故人様名</th><th class="deathday">命日</th><th class="issue_day">発注日時</th><th class="entry_day">発行日</th><th class="state">発行状態</th><th class="charge">担当者</th><th class="operate1"></th><th class="operate2"></th>
    </tr>
<?php  $_smarty_tpl->tpl_vars["deceasedInfo"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["deceasedInfo"]->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['deceasedInfoList']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["deceasedInfo"]->key => $_smarty_tpl->tpl_vars["deceasedInfo"]->value) {
$_smarty_tpl->tpl_vars["deceasedInfo"]->_loop = true;
?>
    
<?php if ($_smarty_tpl->tpl_vars['deceasedInfo']->value['issue_state_code']=="1") {?>
    <tr class="issue_data">
<?php } elseif ($_smarty_tpl->tpl_vars['deceasedInfo']->value['issue_state_code']=="2") {?>
    <tr class="issued_data">
<?php } elseif ($_smarty_tpl->tpl_vars['deceasedInfo']->value['issue_state_code']=="4") {?>
    <tr class="del_data">
<?php } else { ?>
    <tr>
<?php }?>
        <td><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['deceasedInfo']->value['deceased_name'], ENT_QUOTES, 'UTF-8', true);?>
&nbsp;様</td>
        <td><?php echo date('Y/m/d',strtotime($_smarty_tpl->tpl_vars['deceasedInfo']->value['deceased_deathday']));?>
</td>
        <td><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['deceasedInfo']->value['issue_datetime'],"%Y/%m/%d %H:%M");?>
</td>
        <td>
<?php if ($_smarty_tpl->tpl_vars['deceasedInfo']->value['issue_state_code']=="1"||$_smarty_tpl->tpl_vars['deceasedInfo']->value['issue_state_code']=="2") {?>
            －
<?php } else { ?>
            <?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['deceasedInfo']->value['entry_datetime'],"%Y/%m/%d");?>

<?php }?>
        </td>
        <td>
<?php if ($_smarty_tpl->tpl_vars['deceasedInfo']->value['issue_state_code']=="1") {?>
            発行依頼済み
<?php } elseif ($_smarty_tpl->tpl_vars['deceasedInfo']->value['issue_state_code']=="2") {?>
            発行中
<?php } elseif ($_smarty_tpl->tpl_vars['deceasedInfo']->value['issue_state_code']=="3") {?>
            発行完了
<?php } elseif ($_smarty_tpl->tpl_vars['deceasedInfo']->value['issue_state_code']=="4") {?>
            削除
<?php }?>
        </td>
        <td>
<?php if (strlen($_smarty_tpl->tpl_vars['deceasedInfo']->value['charge_name'])>0) {?>
            <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['deceasedInfo']->value['charge_name'], ENT_QUOTES, 'UTF-8', true);?>
&nbsp;様
<?php } else { ?>
            －
<?php }?>
        </td>
<?php if ($_smarty_tpl->tpl_vars['deceasedInfo']->value['issue_state_code']=="1") {?>
        <td class="operate">
            <a class="btn_mini" href="../mng/dispdeceasedinfo?did=<?php echo $_smarty_tpl->tpl_vars['deceasedInfo']->value['deceased_id'];?>
">表示</a>
        </td>
        <td class="operate">
            <a class="cancel_btn btn_mini" href="../mng/canceldeceasedqrorder?did=<?php echo $_smarty_tpl->tpl_vars['deceasedInfo']->value['deceased_id'];?>
" onclick="return false;" >キャンセル</a>
        </td>
<?php } elseif ($_smarty_tpl->tpl_vars['deceasedInfo']->value['issue_state_code']=="2") {?>
        <td class="operate">－</td>
        <td class="operate">－</td>
<?php } elseif ($_smarty_tpl->tpl_vars['deceasedInfo']->value['issue_state_code']=="3") {?>
        <td class="operate">
            <a class="btn_mini" href="../mng/dispdeceasedinfo?did=<?php echo $_smarty_tpl->tpl_vars['deceasedInfo']->value['deceased_id'];?>
">表示</a>
        </td>
        <td class="operate">
            <a class="btn_mini" href="../mng/downloadqrpdf?did=<?php echo $_smarty_tpl->tpl_vars['deceasedInfo']->value['deceased_id'];?>
">ダウンロード</a>
        </td>
<?php } elseif ($_smarty_tpl->tpl_vars['deceasedInfo']->value['issue_state_code']=="4") {?>
        <td class="operate">－</td>
        <td class="operate">－</td>
<?php }?>
        </td>
    </tr>
<?php } ?>
</table>

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
        <a href="../mng/dispdeceasedpaging?page=<?php echo $_smarty_tpl->tpl_vars['page']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['page']->value;?>
</a>&nbsp;&nbsp;
    <?php }?>
<?php } ?>
</p><?php }} ?>
