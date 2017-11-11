<?php
include '../library/ApnsPHP/Autoload.php';

class iosPushFourteenthDeathdayNotifier {

    public function push(){
        //本番環境DB
        $dsn    = 'mysql:dbname=hyamato_pdf;host=mysql1010.xserver.jp';
        $dbuser = 'hyamato_pdf';
        $dbpass = 'wow2784497';

        try
        {
            $pdo = new PDO($dsn, $dbuser, $dbpass, array(PDO::ATTR_EMULATE_PREPARES => false));
            $pdo->query("SET NAMES utf8");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            /*t_ios_device_tokenテーブルのデバイストークンが(null)のものと
              故人IDの桁数が1-2桁のデータを削除する（但し故人IDが空のデータは削除しない）
              （故人IDが1-2桁のデータはバックアップデータを展開した際に保存された
      　       デバイストークンであるため削除）*/
            $delUnnecessaryTokenSql = "DELETE FROM t_ios_device_token
                                       WHERE device_token = '(null)'
                                       OR (CHAR_LENGTH(deceased_id) > 0 AND CHAR_LENGTH(deceased_id) < 3);";
            $pdo->exec($delUnnecessaryTokenSql);

            //無効なデバイストークンを取得する
            // $feedback = new ApnsPHP_Feedback(ApnsPHP_Abstract::ENVIRONMENT_SANDBOX, 'certificates_dev/server_certificates_sandbox.pem');
            $feedback = new ApnsPHP_Feedback(ApnsPHP_Abstract::ENVIRONMENT_SANDBOX, 'certificates_dev/server_certificates_sandbox.pem');
            $feedback->setRootCertificationAuthority('certificates_dev/entrust_root_certification_authority.pem');
            $feedback->connect();
            $results = $feedback->receive();
            $feedback->disconnect();
            $strInvalidToken = "";
            foreach ($results as $result) {
                $strInvalidToken = $strInvalidToken . "'" . $result['deviceToken'] . "',";
            }

            //削除対象のデバイストークンがある場合
            if (empty($strInvalidToken) == false) {
                $strInvalidToken = substr($strInvalidToken, 0, -1);   //最後の「,」を削除
                //削除対象デバイストークンをテーブルから削除
                $delTokenSql = "DELETE FROM t_ios_device_token WHERE device_token IN (" . $strInvalidToken . ")";
                $pdo->exec($delTokenSql);
                //削除対象デバイストークンをログファイルに出力
                $delLogFile = "push_log_iOS/del_log_" . date("Ymd", time()) . ".txt";
                clearstatcache();
                //ファイルが存在しない場合は、空のファイルを作成
                if (is_file($delLogFile) === false) {
                    touch($delLogFile);
                }
                //テキスト追加書込モードでファイルを開く
                $delLogFhandle = fopen($delLogFile, "at");

                if ($delLogFhandle === false) {
                    echo 'ファイルが開けません';
                } else {
                    fwrite($delLogFhandle, $strInvalidToken . "\n");
                }
                fclose($delLogFhandle);
            }

            //Push通知準備
            // $push = new ApnsPHP_Push(ApnsPHP_Abstract::ENVIRONMENT_SANDBOX, 'certificates_dev/server_certificates_sandbox.pem');
            $push = new ApnsPHP_Push(ApnsPHP_Abstract::ENVIRONMENT_SANDBOX, 'certificates_dev/server_certificates_sandbox.pem');
            $push->setRootCertificationAuthority('certificates_dev/entrust_root_certification_authority.pem');
            $push->connect();

            $getTokenSql = "SELECT DISTINCT
                                device_token
                            FROM
                                t_ios_device_token
                            WHERE
                                deceased_id IN (

                                    SELECT DISTINCT
                                        deceased_id
                                    FROM
                                        c_notice_hoyo_info_list
                                        
                                )";
            $tokenList = $pdo->query($getTokenSql)->fetchAll();

            foreach ($tokenList as $token) {
                $message = new ApnsPHP_Message($token['device_token']);
                $message->setCustomIdentifier("MemorialServiceTest");
                $message->setText('KUYOからお知らせ');
                $message->setSound();
                //通知日をプロパティとして送る
                $message->setCustomProperty('notice_schedule', 14141414);
                //通知が無効になり破棄できるタイミング（秒）
                $message->setExpiry(60*60*24*7);
                $push->add($message);

                $strSendToken .= $token['device_token'] . "¥n";
            }

            // echo var_dump($push);

            //Push通知実行
            $push->send();
            $push->disconnect();

            //Examine the error message container
            $aErrorQueue = $push->getErrors();
            if (!empty($aErrorQueue)) {
                var_dump($aErrorQueue);
            }
            //送信結果をログに出力
            //送信対象のデバイストークンがある場合
            if (empty($strSendToken) == false) {
                //ログファイルに出力
                $sendLogFile = "push_log_iOS/send_log_" . date("Ymd", time()) . ".txt";
                clearstatcache();
                //ファイルが存在しない場合は、空のファイルを作成
                if (is_file($sendLogFile) === false) {
                    touch($sendLogFile);
                }
                //テキスト追加書込モードでファイルを開く
                $sendLogFhandle = fopen($sendLogFile, "at");
                if ($sendLogFhandle === false) {
                    echo 'ファイルが開けません';
                } else {
                    //デバイストークンを書き込み
                    fwrite($sendLogFhandle, $strSendToken . "\n");
                }
                fclose($sendLogFhandle);
            }
            //切断
            $pdo = null;
        } catch (PDOException $e) {
            exit("db error");
        }
    }
}
