<?php
// GCM
define('API_KEY',    'AIzaSyBnADa9umpHM2M7_o1fJBQcqjxwwjj3gwg');
define('GOOGLE_URL', 'https://android.googleapis.com/gcm/send');

// GCMエラーメッセージ
define('MISSING', 'MissingRegistration');
define('INVALID', 'InvalidRegistration');
define('UNREGISTERED', 'NotRegistered');

// 本番環境DB
define('DSN',     'mysql:dbname=hyamato_pdf;host=mysql1010.xserver.jp');
define('DB_USER', 'hyamato_pdf');
define('DB_PASS', 'wow2784497');

class AndroidFourteenthDeathdayPushNotifier {
    public function push() {
        try {
            $pdo = new PDO(DSN, DB_USER, DB_PASS, array(PDO::ATTR_EMULATE_PREPARES => false));
            $pdo->query("SET NAMES utf8");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // レジストラIDが「Error :SERVICE_NOT_AVAILABLE」のデータを削除する
            $delUnnecessaryRegistraSql = "DELETE FROM t_android_registration_id 
                                          WHERE registration_id = 'Error :SERVICE_NOT_AVAILABLE'";
            $pdo->exec($delUnnecessaryRegistraSql);

            // 今日配信のお知らせが存在するかチェック
            $noticeSQL = "SELECT * FROM t_notice_info WHERE notice_schedule = 14141414";
            $noticeList = $pdo->query($noticeSQL)->fetchAll();

            // 今日配信のお知らせが存在しない場合は終了
            if(count($noticeList) <= 0) return;

            foreach ($noticeList as $notice) {
                // ログ出力文字列を初期化
                $strSendRegistra = "二七日通知:" . $notice['notice_info_no'] . "\n";

                // 有効なレジストラIDを取得して取得した分繰り返し、メッセージを作成する
                    $getRegistraSQL = "SELECT DISTINCT
                                registration_id
                            FROM
                                 t_android_registration_id
                            WHERE
                                deceased_id IN (

                                    SELECT DISTINCT
                                        deceased_id
                                    FROM
                                        c_notice_hoyo_info_list
                                        
                                )";

                $registraResult = $pdo->query($getRegistraSQL);
                $registraList   = $registraResult->fetchAll();

                // 送信データ準備
                $payload = array('title'       => $notice['notice_title'],
                                 'no'          => $notice['notice_info_no'],
                                 'deceased_id' => '');

                if (count($registraList) > 0) {
                    if($notice['search_category'] == 4 || $notice['search_category'] == 5) {
                        foreach ($registraList as $registra) {
                            $registration_ids = array();
                            $registration_ids[] = $registra['registration_id'];
                            $strSendRegistra .= $registra['registration_id'] . "\n";
                            $payload['deceased_id'] = $registra['deceased_id'];

                            //送信
                            $response = $this->send($registration_ids, $payload);
                            $this->manageRegistrationID($registration_ids, $response);
                        }
                    } else {
                        $count= 0;
                        $registration_ids = array();
                        foreach ($registraList as $registra) {
                            // レジストラIDを設定
                            $registration_ids[] = $registra['registration_id'];
                            // var_dump($registra['registration_id']);
                            $strSendRegistra = $strSendRegistra . $registra['registration_id'] . "\n";
                            $count = $count + 1;

                            if ($count >= 999) {
                                $response = $this->send($registration_ids, $payload);
                                $this->manageRegistrationID($registration_ids, $response);

                                $count = 0;
                                $registration_ids = array();
                            }
                        }
                        if (count($registration_ids) > 0) {
                            $response = $this->send($registration_ids, $payload);
                            $this->manageRegistrationID($registration_ids, $response);
                        }
                        var_dump($registration_ids);
                    }
                }

                // 送信結果をログに出力
                if (empty($strSendRegistra) == false) $this->writeLog($strSendRegistra . "\n");
            }
        } catch (Exception $ex) {
            exit(var_dump($ex->getMessage()));
        }
    }

    // GCMサーバにメッセージを送信する
    private function send($registration_ids, $payload) {
        $dry_run = false;                   //true:実際にはメッセージを送信しない。開発時のテスト用。
        $time_to_live = 60 * 60 * 24 * 28;  //4 weeeks
        $delay_while_idle = false;          //端末がidle時はactiveになるまで送信を待つ
        $collapse_key = "";                 //message with payload

        $data = array('collapse_key'     => $collapse_key,
                      'time_to_live'     => $time_to_live,
                      'delay_while_idle' => $delay_while_idle,
                      'registration_ids' => $registration_ids,
                      'dry_run'          => $dry_run,
                      'data'             => $payload);

        $content = json_encode($data);

        $options = array('http' => array('method'  => 'POST',
                                         'header'  => array("Content-Type: application/json",
                                                            "Authorization: key=".API_KEY,
                                                            "Content-Length: ".strlen($content),),
                                         'content' => $content));

        $response = file_get_contents(GOOGLE_URL, false, stream_context_create($options));

        //レスポンス
        $res = json_decode($response);
        var_dump($res);
        return $res;
    }

    //GCMからのレスポンスに基づいてエラーになったレジストレーションIDを削除する
    private function manageRegistrationID($regIdArray, $response) {
        $resultArray = $response->results;

        $pdo = new PDO(DSN, DB_USER, DB_PASS, array(PDO::ATTR_EMULATE_PREPARES => false));
        $pdo->query("SET NAMES utf8");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        foreach ($resultArray as $result) {
            //レジストレーションIDを取得
            $regID = array_shift($regIdArray);
            if(is_null($regID)) break;

            //エラーメッセージを取り出す
            $errMsg = $result->error;
            if(is_null($errMsg)) continue;

            if(strcmp($errMsg, MISSING) == 0 || strcmp($errMsg, INVALID) == 0 || strcmp($errMsg, UNREGISTERED) == 0) {
                //データベースから該当するレジストレーションIDを削除する
                $deleteSQL = "DELETE FROM t_android_registration_id WHERE registration_id='".$regID."'";
                $pdo->query($deleteSQL);
            }

            //エラーログ出力
            $errorLog = "ERROR(" . date('Y/m/d H:i:s') . ")\n";
            $errorLog .= $errMsg . "\n" . $regID . "\n\n";
            $this->writeLog($errorLog);
        }

        $pdo = null;
    }

    // ログファイルに文字列を書き出す
    private function writeLog($src) {
        $sendLogFile = "push_log_Android/send_log_" . date("Ymd", time()) . ".txt";
        clearstatcache();

        // ファイルが存在しない場合は、空のファイルを作成
        if (is_file($sendLogFile) === false) touch($sendLogFile);

        // テキスト追加書込モードでファイルを開く
        $sendLogFhandle = fopen($sendLogFile, "at");
        if ($sendLogFhandle === false) {
            echo 'ファイルが開けません';
        } else {
            //書き込み
            fwrite($sendLogFhandle, $src);
        }

        fclose($sendLogFhandle);
    }
}