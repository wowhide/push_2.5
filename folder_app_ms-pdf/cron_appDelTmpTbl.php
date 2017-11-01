<?php
// 本番環境DB
$dsn = 'mysql:dbname=hyamato_pdf;host=mysql1010.xserver.jp';
$dbuser = 'hyamato_pdf';
$dbpass = 'wow2784497';
try
{
    $pdo = new PDO($dsn, $dbuser, $dbpass, array(PDO::ATTR_EMULATE_PREPARES => false));
    $pdo->query("SET NAMES utf8");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // 削除対象のデータキーを取得
    $sql = "
        SELECT *  
        FROM t_transfer_user
        WHERE insert_datetime < DATE_SUB(NOW(),INTERVAL 8 DAY)
    ";
    
    foreach ($pdo->query($sql) as $row) {
        // 削除対象データキーの画像を削除
        $dir = './backup_data/' . $row['data_key'];
        removeForder($dir);
    }
    
    // t_transfer_userテーブル削除
    $sql = "
        DELETE 
        FROM t_transfer_user
        WHERE insert_datetime < DATE_SUB(NOW(),INTERVAL 8 DAY)
    ";
    $pdo->exec($sql);
/*
    // t_transfer_morticianテーブル削除
    $sql = "
        DELETE 
        FROM t_transfer_mortician 
        WHERE insert_datetime < DATE_SUB(NOW(),INTERVAL 8 DAY)
    ";
    $pdo->exec($sql);
*/
    // t_transfer_deceasedテーブル削除
    $sql = "
        DELETE 
        FROM t_transfer_deceased 
        WHERE insert_datetime < DATE_SUB(NOW(),INTERVAL 8 DAY)
    ";
    $pdo->exec($sql);
    
    // t_transfer_noticeテーブル削除
    $sql = "
        DELETE 
        FROM t_transfer_notice 
        WHERE insert_datetime < DATE_SUB(NOW(),INTERVAL 8 DAY)
    ";
    $pdo->exec($sql);
    
    // 切断
    $pdo = null;
}
catch (PDOException $e)
{
    exit("db error");
}

function removeForder($dir)
{
    if (!is_dir($dir)) {
        return;
    }
    if (!($dh = opendir($dir))) {
        return;
    }
    while (($file = readdir($dh)) !== false) {
        if (strpos($file,".") === 0) continue;
        if (is_dir($dir."/".$file)) {
            $this->removeForder($dir."/".$file);
            continue;
        }
        unlink($dir."/".$file);
    }
    closedir($dh);
    rmdir($dir);
    return;
}
