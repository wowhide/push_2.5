<?php
/*
 * QR発注の状態を管理する（9-18時の間、10分毎にcronで実行する）
 * 発行状態コードが発行依頼済みかつQR発注から10分以上経過していたら発行状態コードを発行中にする
 * 発行状態コードが発行中かつQR発注から30分以上経過していたら発行状態コードを発行完了にしPDFを添付してメールを送信する
 * [発行状態コード]
 * 0:キャンセル（発行前キャンセルしたデータ）
 * 1:発行依頼済み
 * 2:発行中
 * 3:発行完了
 * 4:削除（発行後削除したデータ）　　
 */

//本番環境DB
$dsn = 'mysql:dbname=hyamato_pdf;host=mysql1010.xserver.jp';
$dbuser = 'hyamato_pdf';
$dbpass = 'wow2784497';
try
{
    $pdo = new PDO($dsn, $dbuser, $dbpass, array(PDO::ATTR_EMULATE_PREPARES => false));
    $pdo->query("SET NAMES utf8");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //発行状態コードが発行中かつQR発注から30分以上経過していたら発行状態コードを
    //発行完了にする
    $issue_completion_sql = "
        UPDATE
            m_deceased 
        SET
            issue_state_code = 3, 
            entry_datetime = NOW()
        WHERE
            issue_state_code = 2
        AND
            NOW() > (issue_datetime + INTERVAL 30 MINUTE)
    ";
    $pdo->exec($issue_completion_sql);

    //発行状態コードが発行依頼済みかつQR発注から10分以上経過していたら発行状態コードを
    //発行中にする
    $issued_in_sql = "
        UPDATE
            m_deceased 
        SET
            issue_state_code = 2
        WHERE
            issue_state_code = 1
        AND
            NOW() > (issue_datetime + INTERVAL 10 MINUTE)
    ";
    $pdo->exec($issued_in_sql);

    //切断
    $pdo = null;
}
catch (PDOException $e)
{
    exit("db error");
}
