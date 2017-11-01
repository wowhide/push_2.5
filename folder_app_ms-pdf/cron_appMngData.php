<?php
//通知情報テーブルの配信日時が今日より過去でかつ通知済フラグが0のレコードの通知済フラグを1に更新する
//本番環境DB
$dsn = 'mysql:dbname=hyamato_pdf;host=mysql1010.xserver.jp';
$dbuser = 'hyamato_pdf';
$dbpass = 'wow2784497';
try
{
    $pdo = new PDO($dsn, $dbuser, $dbpass, array(PDO::ATTR_EMULATE_PREPARES => false));
    $pdo->query("SET NAMES utf8");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "
        UPDATE
            t_notice_info 
        SET
            notice_flg=1
        WHERE
            notice_flg=0 
        AND
            notice_schedule <= date_format(now(), '%Y%m%d')
    ";
    $pdo->exec($sql);

    //切断
    $pdo = null;
}
catch (PDOException $e)
{
    exit("db error");
}

//画像一時保存フォルダ内で、1日以上過去のファイルを削除する
//タイムスタンプを取得
$today = time();
//1日前のタイムスタンプを算出(60秒×60分×24時間：60*60*24)
$onedayago = $today - 86400;

//通知情報写真一時ディレクトリ情報をセットして開く
$targetdir1 = opendir("./notice_info_temp_image/");
//存在するファイルの分だけループして情報を取得する
while (false !== ($targetfile1 = readdir($targetdir1))) {
    //自分自身と上位のフォルダの情報は除外する
    if ($targetfile1 != "." && $targetfile1 != "..") {
        //ファイルの場所をセット
        $filepath1 = './notice_info_temp_image/' . $targetfile1;
        //ファイルの最終更新日時を取得
        $filemtime1 = filemtime($filepath1);
        //1日以上前の更新日時だった場合にはファイルをフォルダから削除する
        if ($onedayago > $filemtime1){
            unlink($filepath1);
        }
    }
}
closedir($targetdir1);

//故人様写真一時ディレクトリ情報をセットして開く
$targetdir2 = opendir("./deceased_temp_data/");
//存在するファイルの分だけループして情報を取得する
while (false !== ($targetfile2 = readdir($targetdir2))) {
    //自分自身と上位のフォルダの情報は除外する
    if ($targetfile2 != "." && $targetfile2 != "..") {
        //ファイルの場所をセット
        $filepath2 = './deceased_temp_data/' . $targetfile2;
        //ファイルの最終更新日時を取得
        $filemtime2 = filemtime($filepath2);
        //1日以上前の更新日時だった場合にはファイルをフォルダから削除する
        if ($onedayago > $filemtime2){
            unlink($filepath2);
        }
    }
}
closedir($targetdir2);
