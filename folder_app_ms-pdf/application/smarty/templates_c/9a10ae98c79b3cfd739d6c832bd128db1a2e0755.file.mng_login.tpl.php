<?php /* Smarty version Smarty-3.1.19, created on 2016-06-24 16:51:54
         compiled from "../../folder_app_ms-pdf/application/smarty/templates/mng_login.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1937685225576ce69ab93e30-02306842%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '9a10ae98c79b3cfd739d6c832bd128db1a2e0755' => 
    array (
      0 => '../../folder_app_ms-pdf/application/smarty/templates/mng_login.tpl',
      1 => 1466578844,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1937685225576ce69ab93e30-02306842',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'message' => 0,
    'id' => 0,
    'password' => 0,
    'checked' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_576ce69abf3411_90753287',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_576ce69abf3411_90753287')) {function content_576ce69abf3411_90753287($_smarty_tpl) {?><!DOCTYPE html>
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
<div id="contents">
<h2>ログイン</h2>
<p class="message"><?php echo $_smarty_tpl->tpl_vars['message']->value;?>
</p>
<form method="post" action="../mng/login">
    <p>ID<br>
    <input id="id" type="text" name="id" value="<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
" maxlength="20" style="ime-mode: disabled;"></input></p>
    <p>パスワード<br>
    <input id="password" type="password" name="password" value="<?php echo $_smarty_tpl->tpl_vars['password']->value;?>
" maxlength="20"></input></p>
    <p><input type="checkbox" name="autologin" <?php echo $_smarty_tpl->tpl_vars['checked']->value;?>
>&nbsp;次回から自動ログインする</input></p>
    <p class="note"><span class="aka">※</span>自動ログインにチェックを入れてログインすると、次回からログイン画面を通らずに当システムをご利用頂けます。<br>
    セキュリティにご注意下さい。</p>
    <p class="note"><span class="aka">※</span>ログアウトした場合は、再度ログインする必要があります。</p>
    <p class="note">
        <span class="aka">※</span>
        パスワードを忘れた場合は、<a href="../mng/dispinquirypassword">こちら</a>からお問い合わせ下さい。</p>
    <p><input class="btn" type="submit" value="ログイン"></input></p>
</form>


</div><!-- contents -->
</div><!-- jsok -->
</div><!-- main -->
<?php echo $_smarty_tpl->getSubTemplate ("include/mng_footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

</div><!-- container -->
</body>
</html>
<?php }} ?>
