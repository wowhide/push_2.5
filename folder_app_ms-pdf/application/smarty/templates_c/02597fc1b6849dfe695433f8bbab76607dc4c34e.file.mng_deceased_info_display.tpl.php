<?php /* Smarty version Smarty-3.1.19, created on 2016-06-28 14:36:52
         compiled from "../../folder_app_ms-pdf/application/smarty/templates/mng_deceased_info_display.tpl" */ ?>
<?php /*%%SmartyHeaderCode:54680172957720cf4c16c93-59550825%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '02597fc1b6849dfe695433f8bbab76607dc4c34e' => 
    array (
      0 => '../../folder_app_ms-pdf/application/smarty/templates/mng_deceased_info_display.tpl',
      1 => 1466578841,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '54680172957720cf4c16c93-59550825',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'issueStateCode' => 0,
    'issueDatetime' => 0,
    'entryDatetime' => 0,
    'chargeName' => 0,
    'souke' => 0,
    'deceasedName' => 0,
    'deceasedBirthday' => 0,
    'deceasedDeathday' => 0,
    'kyonenGyonen' => 0,
    'deathAge' => 0,
    'allowPush' => 0,
    'hallName' => 0,
    'imageExistenceFlg' => 0,
    'deceasedId' => 0,
    'cacheKey' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_57720cf4d2dc53_94414270',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_57720cf4d2dc53_94414270')) {function content_57720cf4d2dc53_94414270($_smarty_tpl) {?><!DOCTYPE html>
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
<?php echo $_smarty_tpl->getSubTemplate ("include/mng_header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate ("include/jsng.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<div id="jsok" style="display:none;">
<?php echo $_smarty_tpl->getSubTemplate ("include/mng_menu.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<p class="breadcrumb"><a href="../mng/dispdeceasedlist">故人様一覧</a> ＞ 故人様表示</p>
<div id="contents">
<div id="deceased">
<h2>故人様表示</h2>
<table>
    <tr><th>発注日時</th>
        <td>
            <?php if ($_smarty_tpl->tpl_vars['issueStateCode']->value=="1") {?>
                －
            <?php } else { ?>
                <?php echo date('Y/m/d G:i',strtotime($_smarty_tpl->tpl_vars['issueDatetime']->value));?>

            <?php }?>
        </td></tr>
    <tr><th>発行日</th>
        <td>
            <?php if (strlen($_smarty_tpl->tpl_vars['entryDatetime']->value>0)) {?>
                <?php echo date('Y/m/d',strtotime($_smarty_tpl->tpl_vars['entryDatetime']->value));?>

            <?php } else { ?>
                －
            <?php }?>
        </td>
    </tr>
    <tr><th>担当者名</th>
        <td>
            <?php if (strlen($_smarty_tpl->tpl_vars['chargeName']->value)>0) {?>
                <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['chargeName']->value, ENT_QUOTES, 'UTF-8', true);?>
　様
            <?php } else { ?>
                －
            <?php }?>
        </td>
    </tr>
    <tr><th>葬家様名</th>
        <td>
            <?php if (strlen($_smarty_tpl->tpl_vars['souke']->value)>0) {?>
                <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['souke']->value, ENT_QUOTES, 'UTF-8', true);?>
　家様
            <?php } else { ?>
                －
            <?php }?>
        </td>
    </tr>
    <tr><th>故人様名</th><td><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['deceasedName']->value, ENT_QUOTES, 'UTF-8', true);?>
　様</td></tr>
    <tr><th>生年月日</th><td><?php echo date('Y/m/d',strtotime($_smarty_tpl->tpl_vars['deceasedBirthday']->value));?>
</td></tr>
    <tr><th>没年月日</th><td><?php echo date('Y/m/d',strtotime($_smarty_tpl->tpl_vars['deceasedDeathday']->value));?>
</td></tr>
    <tr><th>享年行年</th><td><?php echo $_smarty_tpl->tpl_vars['kyonenGyonen']->value;?>
</td></tr>
    <tr><th>没年齢</th><td><?php echo $_smarty_tpl->tpl_vars['deathAge']->value;?>
　歳</td></tr>
    <tr><th>プッシュ通知</th><td><?php echo $_smarty_tpl->tpl_vars['allowPush']->value;?>
</td></tr>
    <tr><th>会館名</th>
        <td>
            <?php if (strlen($_smarty_tpl->tpl_vars['hallName']->value)>0) {?>
                <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['hallName']->value, ENT_QUOTES, 'UTF-8', true);?>

            <?php } else { ?>
                －
            <?php }?>
        </td>
    </tr>
    <tr><th>写真</th>
        <td>
            <?php if ($_smarty_tpl->tpl_vars['imageExistenceFlg']->value==1) {?>
                <img src="../mng/readdeceasedimage?did=<?php echo $_smarty_tpl->tpl_vars['deceasedId']->value;?>
&c=<?php echo $_smarty_tpl->tpl_vars['cacheKey']->value;?>
" width=200 />
            <?php } else { ?>
                －
            <?php }?>
        </td>
    </tr>
</table>

<div class="btn_row">
<form method="get" action="../mng/dispdeceasedreturn" onSubmit="return double()">
    <input class="btn" type="submit" value="戻る" />
</form>
<?php if ($_smarty_tpl->tpl_vars['issueStateCode']->value=="3") {?>
    <form id="del_form" method="post" action="../mng/deldeceasedinfo" onSubmit="return double()">
        <input type="hidden" name="deceased_id" value="<?php echo $_smarty_tpl->tpl_vars['deceasedId']->value;?>
">
        <input class="btn" id="deceased_info_del" type="button" value="削除" />
    </form>
    <form method="post" action="../mng/dispeditdeceasedinfo" onSubmit="return double()">
        <input type="hidden" name="deceased_id" value="<?php echo $_smarty_tpl->tpl_vars['deceasedId']->value;?>
">
        <input class="btn" type="submit" value="編集" />
    </form>
<?php }?>
</div><!-- btn_row -->

</div><!-- deceased -->
</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
<?php echo $_smarty_tpl->getSubTemplate ("include/mng_footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

</div><!-- container -->

<div id="dialog" title="故人情報削除">
<p>故人情報を削除します。<br />よろしいですか？</p>
</div><!-- dialog -->

</body>
</html>
<?php }} ?>
