<?php

/**
 * 法要アプリプレミアム版との連携機能を制御するコントローラクラス
 * 
 * LICENSE: 
 * 
 * @copyright   2014 Digtalspace WOW CO.,Ltd
 * @license     
 * @version     1.0.0
 * @link        
 * @since       File availabel since Release 1.0.0
 */

require_once 'Zend/Controller/Action.php';
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Registry.php';
require_once 'Zend/Json.php';
require_once 'Zend/Mail.php';
require_once 'Zend/Mail/Transport/Smtp.php';
require_once 'Zend/Date.php';

/*Xサーバーでサブドメインの場合*/
require_once 'application/models/cooperationModel.php';
require_once 'application/models/logModel.php';
require_once 'application/common/comMail.php';
require_once 'application/common/comDefine.php';
require_once 'application/common/common.php';
require_once 'application/smarty/Zend_View_Smarty.class.php';
require_once 'application/common/comEncryption.php';

/*サブドメインでない場合
require_once '../application/models/cooperationModel.php';
require_once '../application/models/logModel.php';
require_once '../application/common/comMail.php';
require_once '../application/common/comDefine.php';
require_once '../application/common/common.php';
require_once '../application/smarty/Zend_View_Smarty.class.php';
require_once '../application/common/comEncryption.php';
*/

//定数
//ログ種別
define('LOG_KIND_READ_DECEASED_DATA', 101);

class CooperationController extends Zend_Controller_Action
{
    private $_httpHeaderInfo;            //ヘッダー情報格納変数

    //初期化処理
    public function init()
    {
        // 設定情報をロードする
        /*Xサーバーでサブドメインの場合*/
        $this->_config = new Zend_Config_Ini('application/config/config.ini', null);
        /**/
        /*サブドメインでない場合
        $this->_config = new Zend_Config_Ini('../application/config/config.ini', null);
        /**/
        
        // データベース関連の設定をレジストリに登録する
        Zend_Registry::set('database', $this->_config->datasource->database->toArray());

        // Zend_View_Smartyを生成してviewを上書きする
        $this->view = new Zend_View_Smarty();
        // ビュースクリプトのディレクトリを設定する
        $this->view->setScriptPath(SMARTY_TEMP_PATH . 'templates');
        // ビュースクリプトのコンパイルディレクトリを設定する
        $this->view->setCompilePath(SMARTY_TEMP_PATH . 'templates_c');
        
        //ヘッダー情報を取得
        $this->_httpHeaderInfo = 'HTTP_USER_AGENT：' . $this->_request->getServer('HTTP_USER_AGENT') . "\n" .
                                 'REMOTE_ADDR：' . $this->_request->getServer('REMOTE_ADDR') . "\n" .
                                 'REMOTE_HOST：' . $this->_request->getServer('REMOTE_HOST') . "\n" .
                                 'REMOTE_PORT：' . $this->_request->getServer('REMOTE_PORT');
    }
    
    //故人データ読込
    public function readdeceaseddataAction() {
        // POST値から故人IDを取得
        $deceasedId = $this->getRequest()->getPost('deceasedId');           // 故人ID
//        $certification = $this->getRequest()->getPost('certification');     // 認証キー　使用しません
/*
        // GET値から故人IDと認証キーを取得（テスト用）
        $deceasedId = $this->_getParam('deceasedId');                       // 故人ID
        $certification = $this->_getParam('certification');                 // 認証キー
*/
        
        // 故人IDを検索キーに故人マスタから故人情報を取得
        $cooperationModel = new cooperationModel(); // Cooperationモデルインスタンス生成
        $cooperation = $cooperationModel->getDeceased($deceasedId);
        
        //故人データ読込のLogを書き込む
        $logModel = new logModel(); // Logモデルインスタンス生成
        $logModel->recordLog(LOG_KIND_READ_DECEASED_DATA, "", $deceasedId, $this->_httpHeaderInfo);
        
        // 故人情報をJSON形式に変換
        $jCooperation = Zend_Json::encode($cooperation);
        
        // JSON形式の故人情報を返す
        echo $jCooperation;
    }
    
    //故人データ読込（確認アプリ用）
    public function testreaddeceaseddataAction() {
        // POST値から故人IDを取得
        $deceasedId = $this->getRequest()->getPost('deceasedId');           // 故人ID
//        $certification = $this->getRequest()->getPost('certification');     // 認証キー　使用しません
/*
        // GET値から故人IDと認証キーを取得（テスト用）
        $deceasedId = $this->_getParam('deceasedId');                       // 故人ID
        $certification = $this->_getParam('certification');                 // 認証キー
*/
        
        // 故人IDを検索キーに故人マスタから故人情報を取得
        $cooperationModel = new cooperationModel(); // Cooperationモデルインスタンス生成
        $cooperation = $cooperationModel->getDeceased($deceasedId);
        
        // 故人情報をJSON形式に変換
        $jCooperation = Zend_Json::encode($cooperation);
        
        // JSON形式の故人情報を返す
        echo $jCooperation;
    }
    
    //写真ダウンロード
    public function downloadphotoAction() {
        // GET値からクエリ値を取得
        $deceasedId = $this->_getParam('deceasedId');                       // 故人ID
        $datakey = $this->_getParam('datakey');                             // アクセスキー
        $filename = $this->_getParam("filename");                           // ファイル名
//        $certification = $this->_getParam('certification');                 // 認証キー　使用しません

        // 設定されているクエリ値で取得先を変える
        if (empty($deceasedId) == false) {
            // 故人IDをキーに写真を取得
            $cooperationModel = new cooperationModel(); // Cooperationモデルインスタンス生成
            $deceased = $cooperationModel->getDeceased($deceasedId);
            $photoPath = APP_DATA_PATH . substr($deceased["issue_datetime"], 0, 4) .
                    "/" . $deceasedId . '/' . $deceasedId . '.jpg';            
        } elseif (empty($datakey) == false && empty($filename) == false) {
            // アクセスキーとファイル名をキーに写真を取得
            $photoPath = APP_BACKUP_PATH . $datakey . '/' . $filename;            
        }
        header('Content-type: image/jpeg');
        // ダウンロード対象のファイルが存在するかチェック
        if (file_exists($photoPath)) {      // 存在する
            readfile($photoPath);
        } else {                            // 存在しない
            // 写真のファイル存在しない場合、代替え写真を返す
            readfile(APP_DATA_PATH . 'dummy.jpg');
        }
    }
    
    //法要メール配信（Android版）
    public function sendmemorialmailAction() {
        // POST値からクエリ値を取得
        $fromName = $this->getRequest()->getPost('fromName');               // 配信者名
        $deceasedName = $this->getRequest()->getPost('deceasedName');       // 故人名
        $memorialday = $this->getRequest()->getPost('memorialday');         // 法要日
        $anniversary = $this->getRequest()->getPost('anniversary');         // 回忌数
        $toMail = $this->getRequest()->getPost('toMail');                   // 送信先メールアドレス
        $toName = $this->getRequest()->getPost('toName');                   // 送信先名
//        $certification = $this->getRequest()->getPost('certification');     // 認証キー　使用しません

/*
        // GET値からクエリ値を取得（テスト用）
        $fromName = $this->_getParam('fromName');               // 配信者名
        $deceasedName = $this->_getParam('deceasedName');       // 故人名
        $memorialday = $this->_getParam('memorialday');         // 法要日
        $anniversary = $this->_getParam('anniversary');         // 回忌数
        $toMail = $this->_getParam('toMail');                   // 送信先メールアドレス
        $toName = $this->_getParam('toName');                   // 送信先名
        //$certification = $this->_getParam('certification');     // 認証キー
*/
        // メールに表示する入力値を設定する
        $this->view->toName = $toName;
        $this->view->fromName = $fromName;
        $this->view->memorialday = $memorialday;
        $this->view->deceasedName = $deceasedName;
        $this->view->anniversary = $anniversary;

        // メール文を取得
        $body = $this->view->render('mail_notice_memorial.tpl');
        
        if (strlen($fromName) > 0) {
            // 送信メールの内容を取得
            $mailInfo = array(
                'username' => $this->_config->mail->info_mail,
                'password' => $this->_config->mail->info_password,
                'fromName' => $fromName,
                'fromMail' => $this->_config->mail->info_mail,
                'toName' => $toName . '様',
                'toMail' => $toMail,
                'subject' => $fromName . '様からご法要予定のお知らせ',
                'body' => $body
            );
        } else {
            // 送信メールの内容を取得
            $mailInfo = array(
                'username' => $this->_config->mail->info_mail,
                'password' => $this->_config->mail->info_password,
                'fromName' => 'KUYOアプリ',
                'fromMail' => $this->_config->mail->info_mail,
                'toName' => $toName . '様',
                'toMail' => $toMail,
                'subject' => 'ご法要予定のお知らせ',
                'body' => $body
            );
        }

        // メール送信
        comMail::sendMail($mailInfo);

    }
    
    //法要メール配信（iOS版）
    public function sendmemorialmailiosAction() {

        // POST値からクエリ値を取得
        $fromName = $this->getRequest()->getPost('fromName');               // 配信者名
        $deceasedName = $this->getRequest()->getPost('deceasedName');       // 故人名
        $memorialday = $this->getRequest()->getPost('memorialday');         // 法要日
        $anniversary = $this->getRequest()->getPost('anniversary');         // 回忌数
        $toMail = $this->getRequest()->getPost('toMail');                   // 送信先メールアドレス
        $toName = $this->getRequest()->getPost('toName');                   // 送信先名
//        $certification = $this->getRequest()->getPost('certification');     // 認証キー　使用しません
/*
        // GET値からクエリ値を取得（テスト用）
        $fromName = $this->_getParam('fromName');               // 配信者名
        $deceasedName = $this->_getParam('deceasedName');       // 故人名
        $memorialday = $this->_getParam('memorialday');         // 法要日
        $anniversary = $this->_getParam('anniversary');         // 回忌数
        $toMail = $this->_getParam('toMail');                   // 送信先メールアドレス
        $toName = $this->_getParam('toName');                   // 送信先名
        $certification = $this->_getParam('certification');     // 認証キー        
*/
        // メールに表示する入力値を設定する
        $this->view->toName = $toName;
        $this->view->fromName = $fromName;
        $this->view->memorialday = $memorialday;
        $this->view->deceasedName = $deceasedName;
        $this->view->anniversary = $anniversary;

        // メール文を取得
        $body = $this->view->render('mail_notice_memorial_ios.tpl');
      
        if (strlen($fromName) > 0) {
            // 送信メールの内容を取得
            $mailInfo = array(
                'username' => $this->_config->mail->info_mail,
                'password' => $this->_config->mail->info_password,
                'fromName' => $fromName,
                'fromMail' => $this->_config->mail->info_mail,
                'toName' => $toName . '様',
                'toMail' => $toMail,
                'subject' => $fromName . '様からご法要予定のお知らせ',
                'body' => $body
            );
        } else {
            // 送信メールの内容を取得
            $mailInfo = array(
                'username' => $this->_config->mail->info_mail,
                'password' => $this->_config->mail->info_password,
                'fromName' => 'KUYOアプリ',
                'fromMail' => $this->_config->mail->info_mail,
                'toName' => $toName . '様',
                'toMail' => $toMail,
                'subject' => 'ご法要予定のお知らせ',
                'body' => $body
            );
        }

        // メール送信
        comMail::sendMail($mailInfo);
    }

    //引継データ保存
    public function savetransferdataAction() {
        // POST値からクエリ値を取得
        $jsonUser = $this->getRequest()->getPost('user');                   // 利用者
        $jsonDeceased = $this->getRequest()->getPost('deceased');           // 故人
        $jsonNotice = $this->getRequest()->getPost('notice');               // 通知先
/*
        // GET値からクエリ値を取得（テスト用）
        $jsonUser = $this->_getParam('user');                               // 利用者
        $jsonDeceased = $this->_getParam('deceased');                       // 故人
        $jsonNotice = $this->_getParam('notice');                           // 通知先
*/
        $cooperationModel = new cooperationModel();     // Cooperationモデルインスタンス生成
        $cooperationModel->beginTransaction();          // トランザクション開始

        // アクセスキーを発行する
        $dataKeyFlg = false;
        while ($dataKeyFlg == false) {
            // アクセスキーを発行する
            $dataKey = common::getRandomString();
            if ($cooperationModel->checkDataKey($dataKey) == false) {
                $dataKeyFlg = true;
            }
        }

        // 利用者データを利用者引継テーブルに保存
        if (empty($jsonUser) == false) {
            // JSON形式のデータをデコード
            $user = Zend_Json::decode($jsonUser, Zend_Json::TYPE_ARRAY);
            if ($cooperationModel->insertTransferUser($dataKey, $user) == false) {
                echo "";
                exit();
            }
        }

        // 故人データを故人引継テーブルに保存
        if (empty($jsonDeceased) == false) {
            // JSON形式のデータをデコード
            $arrayDeceased = Zend_Json::decode($jsonDeceased, Zend_Json::TYPE_ARRAY);
            if ($cooperationModel->insertTransferDeceased($dataKey, $arrayDeceased) == false) {
                echo "";
                exit();
            }
        }

        // 通知先データを通知先引継テーブルに保存
        if (empty($jsonNotice) == false) {
            // JSON形式のデータをデコード
            $arrayNotice = Zend_Json::decode($jsonNotice, Zend_Json::TYPE_ARRAY);
            if ($cooperationModel->insertTransferNotice($dataKey, $arrayNotice) == false) {
                echo "";
                exit();
            }
        }

        $cooperationModel->commit();                        // コミット
        // アクセスキーを返す
        echo $dataKey;
    }

    //写真アップロード
    public function upphotoAction() {
        // POST値からクエリ値を取得
        $dataKey = $this->getRequest()->getPost('datakey');                   // アクセスキー

        // 保存用ディレクトリが存在するかチェックする
        $uploadDir  = APP_BACKUP_PATH . $dataKey . "/";  
        if (is_dir($uploadDir) == false) {
            // 存在しない場合、作成する
            if (mkdir($uploadDir) == false) {
                // 失敗した場合、NGを返す
                echo "NG";
            }
        }

        // ファイルを保存
        $uploadPath = $uploadDir . basename( $_FILES['upfile']['name']);  
        if (move_uploaded_file($_FILES['upfile']['tmp_name'], $uploadPath)) {  
            // 成功した場合、OKを返す
            echo "OK";
        } else {
            // 失敗した場合、NGを返す
            echo "NG";
        }
    }

    //アクセスキーメール送信（Android版）
    public function senddatakeymailAction() {
        $now = new Zend_Date();
        
        // POST値からクエリ値を取得
        $toMail = $this->getRequest()->getPost('toMail');                   // 送信先メールアドレス
        $toName = $this->getRequest()->getPost('toName');                   // 送信先名
        $dataKey = $this->getRequest()->getPost('datakey');                 // アクセスキー
//        $certification = $this->getRequest()->getPost('certification');     // 認証キー　使用しません
/*
        // GET値からクエリ値を取得（テスト用）
        $toMail = $this->_getParam('toMail');                               // 送信先メールアドレス
        $toName = $this->_getParam('toName');                               // 送信先名
        $dataKey = $this->_getParam('datakey');                             // アクセスキー
        $certification = $this->_getParam('certification');                 // 認証キー
*/
        // アクセスキー有効期限を取得する
        $now->add(7, Zend_Date::DAY);

        // メールに表示する入力値を設定する
        $this->view->toName = $toName;
        $this->view->dataKey = $dataKey;
        $this->view->deadline = $now->get('yyyy年MM月dd日');

        // メール文を取得
        $body = $this->view->render('mail_datakey.tpl');

        // 送信メールの内容を取得
        $mailInfo = array(
            'username' => $this->_config->mail->info_mail,
            'password' => $this->_config->mail->info_password,
            'fromName' => 'KUYOアプリ',
            'fromMail' => $this->_config->mail->info_mail,
            'toName' => $toName . '様',
            'toMail' => $toMail,
            'subject' => 'KUYOアプリからアクセスキーのお知らせ',
            'body' => $body
        );

        // メール送信
        comMail::sendMail($mailInfo);
    }

    //アクセスキーメール送信（iOS版）
    public function senddatakeymailiosAction() {
        $now = new Zend_Date();

        // POST値からクエリ値を取得
        $toMail = $this->getRequest()->getPost('toMail');                   // 送信先メールアドレス
        $toName = $this->getRequest()->getPost('toName');                   // 送信先名
        $dataKey = $this->getRequest()->getPost('datakey');                 // アクセスキー
//        $certification = $this->getRequest()->getPost('certification');     // 認証キー　使用しません

/*
        // GET値からクエリ値を取得（テスト用）
        $toMail = $this->_getParam('toMail');                               // 送信先メールアドレス
        $toName = $this->_getParam('toName');                               // 送信先名
        $dataKey = $this->_getParam('datakey');                             // アクセスキー
        $certification = $this->_getParam('certification');                 // 認証キー
*/
        // アクセスキー有効期限を取得する
        $now->add(7, Zend_Date::DAY);

        // メールに表示する入力値を設定する
        $this->view->toName = $toName;
        $this->view->dataKey = $dataKey;
        $this->view->deadline = $now->get('yyyy年MM月dd日');

        // メール文を取得
        $body = $this->view->render('mail_datakey_ios.tpl');

        // 送信メールの内容を取得
        $mailInfo = array(
            'username' => $this->_config->mail->info_mail,
            'password' => $this->_config->mail->info_password,
            'fromName' => 'KUYOアプリ',
            'fromMail' => $this->_config->mail->info_mail,
            'toName' => $toName . '様',
            'toMail' => $toMail,
            'subject' => 'KUYOアプリからアクセスキーのお知らせ',
            'body' => $body
        );

        // メール送信
        comMail::sendMail($mailInfo);
    }

    //引継データ読込
    public function readtranseferdataAction() {

        // POST値からアクセスキーを取得
        $datakey = $this->getRequest()->getPost('datakey');                 // アクセスキー
//        $certification = $this->getRequest()->getPost('certification');     // 認証キー　使用しません
/*
        // GET値からクエリ値を取得（テスト用）
        $datakey = $this->_getParam('datakey');                             // アクセスキー
        $certification = $this->_getParam('certification');                 // 認証キー
*/

        $jTranseferData = "";

        $cooperationModel = new cooperationModel(); // Cooperationモデルインスタンス生成

        // アクセスキーが存在しない場合、空文字を返す
        if ($cooperationModel->checkDataKey($datakey) == false) {
            echo $jTranseferData;
            exit();
        }

        // 利用者引継情報を取得してJSON形式に変換
        // アクセスキーを抽出条件に利用者引継情報テーブルから利用者引継情報を取得
        $user = $cooperationModel->selectTransferUser($datakey);
        $user["mail_address"] = comEncryption::decryption($user["mail_address"]);

        // 故人引継情報を取得してJSON形式に変換
        // アクセスキーを抽出条件に故人引継情報テーブルから故人引継情報を取得
        $deceased = $cooperationModel->selectTransferDeceased($datakey);

        // 通知先引継情報を取得してJSON形式に変換
        // アクセスキーを抽出条件に通知先引継情報テーブルから通知先引継情報を取得
        $arrayNotice = $cooperationModel->selectTransferNotice($datakey);

        foreach ($arrayNotice as &$notice) {
            $notice["notice_address"] = comEncryption::decryption($notice["notice_address"]);
        }

        // 取得した情報を一つの配列にまとめ、JSON形式に変換する
        $transeferData = array(
            'user' => $user,
            'deceased'  => $deceased,
            'notice' => $arrayNotice
        );

        $jTranseferData = Zend_Json::encode($transeferData);

        echo $jTranseferData;
    }

    //終活紹介ページ表示（Android版）
    public function syukatsuAction() {
        //テンプレートのメールアドレス、アンカータグ、エラーメッセージを初期化
        $this->view->email = "";
        $this->view->anchor = "first";
        $this->view->message = "";

        echo $this->view->render('syukatsu.tpl');
    }

    //終活紹介ページ表示（iOS版）
    public function syukatsuiosAction() {
        //テンプレートのメールアドレス、アンカータグ、エラーメッセージを初期化
        $this->view->email = "";
        $this->view->anchor = "first";
        $this->view->message = "";

        echo $this->view->render('syukatsu_ios.tpl');
    }

    //メールアドレスチェック（Android版）
    public function checkemailAction() {
        //POST値からメールアドレスを取得
        $email = $this->getRequest()->getPost('email');

        // メールアドレスの形式チェック
        if (Common::chkEmailAddress($email)) {
            // 正しい場合、メール送信処理を呼ぶ
            $this->_forward('sendemail', null, null, array('email' => $email));
        } else {
            // 正しくない場合、エラーメッセージを設定して入力画面を再表示
            $this->view->email = $email;
            $this->view->anchor = "re";
            $this->view->message = "メールアドレスの形式が正しくありません。";
            echo $this->view->render('syukatsu.tpl');
        }
    }

    //メールアドレスチェック（iOS版）
    public function checkemailiosAction() {
        //POST値からメールアドレスを取得
        $email = $this->getRequest()->getPost('email');

        // メールアドレスの形式チェック
        if (Common::chkEmailAddress($email)) {
            // 正しい場合、メール送信処理を呼ぶ
            $this->_forward('sendemailios', null, null, array('email' => $email));
        } else {
            // 正しくない場合、エラーメッセージを設定して入力画面を再表示
            $this->view->email = $email;
            $this->view->anchor = "re";
            $this->view->message = "メールアドレスの形式が正しくありません。";
            echo $this->view->render('syukatsu_ios.tpl');
        }
    }

    //ベストエンディング登録案内メール送信（Android版）
    public function sendemailAction() {
        //POST値からメールアドレスを取得
        $email = $this->_request->getParam('email');

        // 送信処理を実行
        // メールに表示する入力値を設定する
        $this->view->url = "https://bestending.net/entry/dispexplain?ak=4SZVYriQ";
        
        // メール文を取得
        $body = $this->view->render('mail_bestending_url.tpl');
        
        // 送信メールの内容を取得
        $mailInfo = array(
            'username' => $this->_config->mail->info_mail,
            'password' => $this->_config->mail->info_password,
            'fromName' => 'KUYOアプリ',
            'fromMail' => $this->_config->mail->info_mail,
            'toMail' => $email,
            'subject' => 'ベストエンディングご登録URLのご案内',
            'body' => $body
        );
        // メール送信
        comMail::sendMail($mailInfo);

        // 送信完了画面表示処理を呼ぶ
        echo $this->view->render('comp_url_send_mail.tpl');
    }

    //ベストエンディング登録案内メール送信（iOS版）
    public function sendemailiosAction() {
        //POST値からメールアドレスを取得
        $email = $this->_request->getParam('email');

        // 送信処理を実行
        // メールに表示する入力値を設定する
        $this->view->url = "https://bestending.net/entry/dispexplain?ak=4SZVYriQ";
        
        // メール文を取得
        $body = $this->view->render('mail_bestending_url_ios.tpl');
        
        // 送信メールの内容を取得
        $mailInfo = array(
            'username' => $this->_config->mail->info_mail,
            'password' => $this->_config->mail->info_password,
            'fromName' => 'KUYOアプリ',
            'fromMail' => $this->_config->mail->info_mail,
            'toMail' => $email,
            'subject' => 'ベストエンディングご登録URLのご案内',
            'body' => $body
        );
        // メール送信
        comMail::sendMail($mailInfo);

        // 送信完了画面表示処理を呼ぶ
        echo $this->view->render('comp_url_send_mail.tpl');
    }
    
    //本日配信用の通知情報を取得
    public function getnoticeinfotodayAction() {
        $cooperationModel = new cooperationModel(); // Cooperationモデルインスタンス生成
        
        // 通知情報を取得
        $arrayNoticeInfo = $cooperationModel->getNoticeInfoToday();

        //通知情報が存在する場合、URLを返し、無い場合は空文字を返す
        if (count($arrayNoticeInfo) > 0) {
            //URLの配列をJSON形式のデータに変換
            $noticeInfoData = array('noticeInfo' => $arrayNoticeInfo);
            $jNoticeInfoData = Zend_Json::encode($noticeInfoData);
            echo $jNoticeInfoData;
        } else {
            echo '';
        }
    }

    //配信済みの通知情報を取得
    public function getnoticeinfodeliveredAction() {
        $cooperationModel = new cooperationModel();     //Cooperationモデルインスタンス生成
        
        // 通知情報を取得
        $arrayNoticeInfo = $cooperationModel->getNoticeInfoDelivered();

        //通知情報が存在する場合、URLを返し、無い場合は空文字を返す
        if (count($arrayNoticeInfo) > 0) {
            //URLの配列をJSON形式のデータに変換
            $noticeInfoData = array('noticeInfo' => $arrayNoticeInfo);
            $jNoticeInfoData = Zend_Json::encode($noticeInfoData);
            echo $jNoticeInfoData;
        } else {
            echo '';
        }
    }

    //配信済みの通知情報をデバイストークンを元に取得
    public function getnoticeinfodeliveredbytokenAction() {
        // デバイストークンを取得
        $deviceToken = $this->getRequest()->getPost('deviceToken');

        // 通知情報配列を取得
        $cooperationModel = new cooperationModel();
        //法要通知情報が配信されているか
        //六日前
        $arrayNoticeHoyoInfo_SixDaysAgo             = $cooperationModel->getNoticeHoyoInfoSixdayagoDeliveredByToken($deviceToken);
        //十三日前
        $arrayNoticeHoyoInfo_ThirteenDaysAgo        = $cooperationModel->getNoticeHoyoInfoThirteendayagoDeliveredByToken($deviceToken);
        //二十日前
        $arrayNoticeHoyoInfo_TwentyDaysAgo          = $cooperationModel->getNoticeHoyoInfoTwentydayagoDeliveredByToken($deviceToken);
        //二十七日前
        $arrayNoticeHoyoInfo_TwentysevenDaysAgo     = $cooperationModel->getNoticeHoyoInfoTwentysevendayagoDeliveredByToken($deviceToken);
        //三十四日前
        $arrayNoticeHoyoInfo_ThirtyfourDaysAgo      = $cooperationModel->getNoticeHoyoInfoThirtyfourdayagoDeliveredByToken($deviceToken);
        //四十一日前
        $arrayNoticeHoyoInfo_FortyoneDaysAgo        = $cooperationModel->getNoticeHoyoInfoFortyonedayagoDeliveredByToken($deviceToken);        
         //四十八前
        $arrayNoticeHoyoInfo_FortyeightDaysAgo      = $cooperationModel->getNoticeHoyoInfoFortyeightdayagoDeliveredByToken($deviceToken);         
        //お知らせ
        $arrayNoticeInfoNotice  = $cooperationModel->getNoticeInfoDeliveredByToken($deviceToken);

        // //配列をマージ
        $arrayNoticeInfo = array_merge( $arrayNoticeHoyoInfo_FortyeightDaysAgo, 
                                        $arrayNoticeHoyoInfo_FortyoneDaysAgo,
                                        $arrayNoticeHoyoInfo_ThirtyfourDaysAgo,
                                        $arrayNoticeHoyoInfo_TwentysevenDaysAgo,
                                        $arrayNoticeHoyoInfo_TwentyDaysAgo,
                                        $arrayNoticeHoyoInfo_ThirteenDaysAgo,
                                        $arrayNoticeHoyoInfo_SixDaysAgo,
                                        $arrayNoticeInfoNotice);

        // 通知情報が存在する場合は通知情報を返し、ない場合は空文字を返す
        if (count($arrayNoticeInfo) > 0) {
                // URLの配列をJSON形式のデータに変換
                $noticeInfoData  = array('noticeInfo' => $this->adjustNoticeHoyoInfo($arrayNoticeInfo,$deviceToken));

                foreach ($noticeInfoData['noticeInfo'] as $key => $value) {
                  $id[$key] = $value['notice_schedule'];
                }                 
                // array_multisortで'notice_schedule'の列を日付順に並び替える
                array_multisort($id, SORT_DESC, $noticeInfoData['noticeInfo']);
                 
                $jNoticeInfoData = Zend_Json::encode($noticeInfoData);
                echo $jNoticeInfoData;

        }else{
                echo '';
        }

    }


    private function adjustNoticeHoyoInfo($arrayNoticeInfo,$deviceToken) {
        $adjusted = array();
        $cooperationModel = new cooperationModel();

        foreach ($arrayNoticeInfo as $noticeInfo) {

            //初七日通知情報取得　-> 日付を「notice_schedule」を法要日に設定
            if ($noticeInfo['notice_schedule'] == '77777777') 
            {
                $arrayNoticeTypeSeventh  = $cooperationModel->getNoticeHoyoInfoDeliveredDay($deviceToken,$noticeInfo['deceased_id'],7);
                $pushTime = "";
                foreach ( $arrayNoticeTypeSeventh as $notice) {
                   $pushTime = $notice['push_time'];
                }

                if (strlen($pushTime) > 0) {
                    $noticeInfo['notice_schedule'] = $pushTime;
                }
                $noticeInfo['search_category'] = 5;
                $adjusted[] = $noticeInfo;
                continue;
            }

            //二七日通知情報取得　-> 日付を「notice_schedule」を法要日に設定
            if ($noticeInfo['notice_schedule'] == '14141414') 
            {
                $arrayNoticeTypeSeventh  = $cooperationModel->getNoticeHoyoInfoDeliveredDay($deviceToken,$noticeInfo['deceased_id'],14);
                $pushTime = "";
                foreach ( $arrayNoticeTypeSeventh as $notice) {
                   $pushTime = $notice['push_time'];
                }

                if (strlen($pushTime) > 0) {
                    $noticeInfo['notice_schedule'] = $pushTime;
                }
                $noticeInfo['search_category'] = 5;
                $adjusted[] = $noticeInfo;
                continue;
            }

            //三七日通知情報取得　-> 日付を「notice_schedule」を法要日に設定
            if ($noticeInfo['notice_schedule'] == '21212121') 
            {
                $arrayNoticeTypeSeventh  = $cooperationModel->getNoticeHoyoInfoDeliveredDay($deviceToken,$noticeInfo['deceased_id'],21);
                $pushTime = "";
                foreach ( $arrayNoticeTypeSeventh as $notice) {
                   $pushTime = $notice['push_time'];
                }

                if (strlen($pushTime) > 0) {
                    $noticeInfo['notice_schedule'] = $pushTime;
                }
                $noticeInfo['search_category'] = 5;
                $adjusted[] = $noticeInfo;
                continue;
            }

            //四七日通知情報取得　-> 日付を「notice_schedule」を法要日に設定
            if ($noticeInfo['notice_schedule'] == '28282828') 
            {
                $arrayNoticeTypeSeventh  = $cooperationModel->getNoticeHoyoInfoDeliveredDay($deviceToken,$noticeInfo['deceased_id'],28);
                $pushTime = "";
                foreach ( $arrayNoticeTypeSeventh as $notice) {
                   $pushTime = $notice['push_time'];
                }

                if (strlen($pushTime) > 0) {
                    $noticeInfo['notice_schedule'] = $pushTime;
                }
                $noticeInfo['search_category'] = 5;
                $adjusted[] = $noticeInfo;
                continue;
            }

            //五七日通知情報取得　-> 日付を「notice_schedule」を法要日に設定
            if ($noticeInfo['notice_schedule'] == '35353535') 
            {
                $arrayNoticeTypeSeventh  = $cooperationModel->getNoticeHoyoInfoDeliveredDay($deviceToken,$noticeInfo['deceased_id'],35);
                $pushTime = "";
                foreach ( $arrayNoticeTypeSeventh as $notice) {
                   $pushTime = $notice['push_time'];
                }

                if (strlen($pushTime) > 0) {
                    $noticeInfo['notice_schedule'] = $pushTime;
                }
                $noticeInfo['search_category'] = 5;
                $adjusted[] = $noticeInfo;
                continue;
            }

            //六七日通知情報取得　-> 日付を「notice_schedule」を法要日に設定
            if ($noticeInfo['notice_schedule'] == '42424242') 
            {
                $arrayNoticeTypeSeventh  = $cooperationModel->getNoticeHoyoInfoDeliveredDay($deviceToken,$noticeInfo['deceased_id'],42);
                $pushTime = "";
                foreach ( $arrayNoticeTypeSeventh as $notice) {
                   $pushTime = $notice['push_time'];
                }

                if (strlen($pushTime) > 0) {
                    $noticeInfo['notice_schedule'] = $pushTime;
                }
                $noticeInfo['search_category'] = 5;
                $adjusted[] = $noticeInfo;
                continue;
            }

            //四十九日通知情報取得　-> 日付を「notice_schedule」を法要日に設定
            if ($noticeInfo['notice_schedule'] == '49494949') 
            {
                $arrayNoticeTypeSeventh  = $cooperationModel->getNoticeHoyoInfoDeliveredDay($deviceToken,$noticeInfo['deceased_id'],49);
                $pushTime = "";
                foreach ( $arrayNoticeTypeSeventh as $notice) {
                   $pushTime = $notice['push_time'];
                }

                if (strlen($pushTime) > 0) {
                    $noticeInfo['notice_schedule'] = $pushTime;
                }
                $noticeInfo['search_category'] = 5;
                $adjusted[] = $noticeInfo;
                continue;
            }

            $adjusted[] = $noticeInfo;

        }
        return $adjusted;
    }


    //配信済みの通知情報をレジストレーションIDを元に取得
    public function getnoticeinfodeliveredbyregidAction() {
        // レジストレーションIDを取得
        $registrationID = $this->getRequest()->getPost('registrationID');

        // 通知情報配列を取得
        $cooperationModel = new cooperationModel();
        //法要通知情報が配信されているか
        //六日前
        $arrayNoticeHoyoInfo_SixDaysAgo = $cooperationModel->getNoticeHoyoInfoSixdayagoDeliveredByregid($registrationID);
        //十三日前
        $arrayNoticeHoyoInfo_ThirteenDaysAgo        = $cooperationModel->getNoticeHoyoInfoThirteendayagoDeliveredByregid($registrationID);
        //二十日前
        $arrayNoticeHoyoInfo_TwentyDaysAgo          = $cooperationModel->getNoticeHoyoInfoTwentydayagoDeliveredByregid($registrationID);
        //二十七日前
        $arrayNoticeHoyoInfo_TwentysevenDaysAgo     = $cooperationModel->getNoticeHoyoInfoTwentysevendayagoDeliveredByregid($registrationID);
        //三十四日前
        $arrayNoticeHoyoInfo_ThirtyfourDaysAgo      = $cooperationModel->getNoticeHoyoInfoThirtyfourdayagoDeliveredByregid($registrationID);
        //四十一日前
        $arrayNoticeHoyoInfo_FortyoneDaysAgo        = $cooperationModel->getNoticeHoyoInfoFortyonedayagoDeliveredByregid($registrationID);        
         //四十八前
        $arrayNoticeHoyoInfo_FortyeightDaysAgo      = $cooperationModel->getNoticeHoyoInfoFortyeightdayagoDeliveredByregid($registrationID);  
        //お知らせ
        $arrayNoticeInfoNotice          = $cooperationModel->getNoticeInfoDeliveredByRegID($registrationID);

        //配列をマージ
        $arrayNoticeInfo = array_merge( $arrayNoticeHoyoInfo_SixDaysAgo, 
                                        $arrayNoticeHoyoInfo_ThirteenDaysAgo,
                                        $arrayNoticeHoyoInfo_TwentyDaysAgo,
                                        $arrayNoticeHoyoInfo_TwentysevenDaysAgo,
                                        $arrayNoticeHoyoInfo_ThirtyfourDaysAgo,
                                        $arrayNoticeHoyoInfo_FortyoneDaysAgo,
                                        $arrayNoticeHoyoInfo_FortyeightDaysAgo,
                                        $arrayNoticeInfoNotice);

        // 通知情報が存在する場合は通知情報を返し、ない場合は空文字を返す
        if (count($arrayNoticeInfo) > 0) {
            // URLの配列をJSON形式のデータに変換
                $noticeInfoData  = array('noticeInfo' => $this->adjustNoticeHoyoInfoRegid($arrayNoticeInfo,$registrationID));

                foreach ($noticeInfoData['noticeInfo'] as $key => $value) {
                  $id[$key] = $value['notice_schedule'];
                }                 
                // array_multisortで'notice_schedule'の列を日付順に並び替える
                array_multisort($id, SORT_DESC, $noticeInfoData['noticeInfo']);
                 
                $jNoticeInfoData = Zend_Json::encode($noticeInfoData);
                echo $jNoticeInfoData;
        } else {
            echo '';
        }

    }


    private function adjustNoticeHoyoInfoRegid($arrayNoticeInfo,$registrationID) {
        $adjusted = array();
        $cooperationModel = new cooperationModel();

        foreach ($arrayNoticeInfo as $noticeInfoList) {

            //初七日通知情報取得　-> 日付を「notice_schedule」を法要日に設定
            if ($noticeInfoList['notice_schedule'] == '77777777') 
            {
                $arrayNoticeTypeSeventh  = $cooperationModel->getNoticeHoyoInfoDeliveredDayRegstID($registrationID,$noticeInfoList['deceased_id'],7);
                $pushTime = "";
                foreach ( $arrayNoticeTypeSeventh as $notice) {
                   $pushTime = $notice['push_time'];
                }

                if (strlen($pushTime) > 0) {
                    $noticeInfoList['notice_schedule'] = $pushTime;
                }
                $noticeInfoList['search_category'] = 5;
                $adjusted[] = $noticeInfoList;
                continue;
            }

            //二七日通知情報取得　-> 日付を「notice_schedule」を法要日に設定
            if ($noticeInfoList['notice_schedule'] == '14141414') 
            {
                $arrayNoticeTypeSeventh  = $cooperationModel->getNoticeHoyoInfoDeliveredDayRegstID($registrationID,$noticeInfoList['deceased_id'],14);
                $pushTime = "";
                foreach ( $arrayNoticeTypeSeventh as $notice) {
                   $pushTime = $notice['push_time'];
                }

                if (strlen($pushTime) > 0) {
                    $noticeInfoList['notice_schedule'] = $pushTime;
                }
                $noticeInfoList['search_category'] = 5;
                $adjusted[] = $noticeInfoList;
                continue;
            }

            //三七日通知情報取得　-> 日付を「notice_schedule」を法要日に設定
            if ($noticeInfoList['notice_schedule'] == '21212121') 
            {
                $arrayNoticeTypeSeventh  = $cooperationModel->getNoticeHoyoInfoDeliveredDayRegstID($registrationID,$noticeInfoList['deceased_id'],21);
                $pushTime = "";
                foreach ( $arrayNoticeTypeSeventh as $notice) {
                   $pushTime = $notice['push_time'];
                }

                if (strlen($pushTime) > 0) {
                    $noticeInfoList['notice_schedule'] = $pushTime;
                }
                $noticeInfoList['search_category'] = 5;
                $adjusted[] = $noticeInfoList;
                continue;
            }

            //四七日通知情報取得　-> 日付を「notice_schedule」を法要日に設定
            if ($noticeInfoList['notice_schedule'] == '28282828') 
            {
                $arrayNoticeTypeSeventh  = $cooperationModel->getNoticeHoyoInfoDeliveredDayRegstID($registrationID,$noticeInfoList['deceased_id'],28);
                $pushTime = "";
                foreach ( $arrayNoticeTypeSeventh as $notice) {
                   $pushTime = $notice['push_time'];
                }

                if (strlen($pushTime) > 0) {
                    $noticeInfoList['notice_schedule'] = $pushTime;
                }
                $noticeInfoList['search_category'] = 5;
                $adjusted[] = $noticeInfoList;
                continue;
            }

            //五七日通知情報取得　-> 日付を「notice_schedule」を法要日に設定
            if ($noticeInfoList['notice_schedule'] == '35353535') 
            {
                $arrayNoticeTypeSeventh  = $cooperationModel->getNoticeHoyoInfoDeliveredDayRegstID($registrationID,$noticeInfoList['deceased_id'],35);
                $pushTime = "";
                foreach ( $arrayNoticeTypeSeventh as $notice) {
                   $pushTime = $notice['push_time'];
                }

                if (strlen($pushTime) > 0) {
                    $noticeInfoList['notice_schedule'] = $pushTime;
                }
                $noticeInfoList['search_category'] = 5;
                $adjusted[] = $noticeInfoList;
                continue;
            }

            //六七日通知情報取得　-> 日付を「notice_schedule」を法要日に設定
            if ($noticeInfoList['notice_schedule'] == '42424242') 
            {
                $arrayNoticeTypeSeventh  = $cooperationModel->getNoticeHoyoInfoDeliveredDayRegstID($registrationID,$noticeInfoList['deceased_id'],42);
                $pushTime = "";
                foreach ( $arrayNoticeTypeSeventh as $notice) {
                   $pushTime = $notice['push_time'];
                }

                if (strlen($pushTime) > 0) {
                    $noticeInfoList['notice_schedule'] = $pushTime;
                }
                $noticeInfoList['search_category'] = 5;
                $adjusted[] = $noticeInfoList;
                continue;
            }

            //四十九日通知情報取得　-> 日付を「notice_schedule」を法要日に設定
            if ($noticeInfoList['notice_schedule'] == '49494949') 
            {
                $arrayNoticeTypeSeventh  = $cooperationModel->getNoticeHoyoInfoDeliveredDayRegstID($registrationID,$noticeInfoList['deceased_id'],49);
                $pushTime = "";
                foreach ( $arrayNoticeTypeSeventh as $notice) {
                   $pushTime = $notice['push_time'];
                }

                if (strlen($pushTime) > 0) {
                    $noticeInfoList['notice_schedule'] = $pushTime;
                }
                $noticeInfoList['search_category'] = 5;
                $adjusted[] = $noticeInfoList;
                continue;
            }

            $adjusted[] = $noticeInfoList;

        }
        return $adjusted;
    }


    private function adjustNoticeInfo($arrayNoticeInfo) {
        $adjusted = array();
        $noticeInfoNoList = array();

        foreach ($arrayNoticeInfo as $noticeInfo) {
            if($noticeInfo['search_category'] == 4 || $noticeInfo['search_category'] == 5) {
                if($noticeInfo['entry_method'] == 1){
                    $adjusted[] = $noticeInfo;
                    $noticeInfoNoList[] = $noticeInfo['notice_info_no'];
                    continue;
                }
            }

            $noticeInfo['deceased_id'] = '';
            if(array_search($noticeInfo['notice_info_no'], $noticeInfoNoList) === false) {
                $adjusted[] = $noticeInfo;
                $noticeInfoNoList[] = $noticeInfo['notice_info_no'];
            }
        }

        return $adjusted;
    }

    // 撮影履歴サムネイル表示機能
    public function getthumbnailAction() {
        // GET値を取得
        $deceasedId = $this->_getParam('deceasedId');                 // お知らせNo

        // 画像ファイルのパスを指定
        $src = '../../folder_app_ms-dev/photo/' . $deceasedId . '.jpg' ;
        // 画像ファイルを出力
        header('Content-type: image/jpeg');
        readfile($src);
    }

    // お知らせ画像表示機能
    public function storeimageAction() {
        // GET値を取得
        $deceasedId = $this->_getParam('deceasedId');                 // お知らせNo

        // 画像ファイルのパスを指定
        $src = '../../folder_app_ms-dev/store/' . $deceasedId . '.jpg' ;
        // 画像ファイルを出力
        header('Content-type: image/jpeg');
        readfile($src);
    }

/*
    //通知Noを元に通知情報を取得
    public function getnoticeinfoAction() {
        $cooperationModel = new cooperationModel();     //Cooperationモデルインスタンス生成

        //POST値から通知Noを取得
        $noticeInfoNo = $this->getRequest()->getPost('noticeInfoNo');
        // GET値からクエリ値を取得（テスト用）
//        $noticeInfoNo = $this->_getParam('noticeInfoNo');

        //通知情報を取得
        $noticeInfo = $cooperationModel->getNoticeInfo($noticeInfoNo);

        //故人情報をJSON形式に変換
        $jNoticeInfo = Zend_Json::encode($noticeInfo);

        //JSON形式の故人情報を返す
        echo $jNoticeInfo;
    }
*/
    
    //通知日を元に通知情報を取得
    public function getnoticeinfoAction() {
        $cooperationModel = new cooperationModel();     //Cooperationモデルインスタンス生成

        //POST値から通知日を取得
        $noticeSchedule = $this->getRequest()->getPost('noticeSchedule');
        // GET値からクエリ値を取得（テスト用）
       // $noticeSchedule = $this->_getParam('noticeSchedule');

        //通知情報を取得
        $arrayNoticeInfo = $cooperationModel->getNoticeInfo($noticeSchedule);

        //通知情報が存在する場合、URLを返し、無い場合は空文字を返す
        if (count($arrayNoticeInfo) > 0) {
            //URLの配列をJSON形式のデータに変換
            $noticeInfoData = array('noticeInfo' => $arrayNoticeInfo);
            $jNoticeInfoData = Zend_Json::encode($noticeInfoData);
            echo $jNoticeInfoData;
        } else {
            echo '';
        }
    }
    
    //通知日とデバイストークンを元に通知情報を取得
    public function getnoticeinfobytokenAction() {
        //POST値から通知日とデバイストークンを取得
        $noticeSchedule = $this->getRequest()->getPost('noticeSchedule');
        $deviceToken    = $this->getRequest()->getPost('deviceToken');

        $cooperationModel = new cooperationModel();
        $arrayNoticeInfo  = $cooperationModel->getNoticeInfoByToken($noticeSchedule, $deviceToken);

        if (count($arrayNoticeInfo) > 0) {
            $noticeInfoData  = array('noticeInfo' => $arrayNoticeInfo);
            $jNoticeInfoData = Zend_Json::encode($noticeInfoData);
            echo $jNoticeInfoData;
        } else {
            echo '';
        }
    }

    //通知日とデバイストークンを元に通知情報＋故人IDを取得
    public function getnoticeinfoanddeceasedidAction() {
        $noticeSchedule = $this->getRequest()->getPost('noticeSchedule');
        $deviceToken    = $this->getRequest()->getPost('deviceToken');

        $cooperationModel = new cooperationModel();


        //配信日付取得
        $DeliveryDate = "";

        //初七日の場合
        if ($noticeSchedule == '77777777') {
            // 6日前
        $DeliveryDate = date('Ymd', strtotime('-6 day', time()));
        }

        //二七日の場合
        if ($noticeSchedule == '14141414') {
            // 14日前
        $DeliveryDate = date('Ymd', strtotime('-13 day', time()));
        }

        //三七日の場合
        if ($noticeSchedule == '21212121') {
            // 20日前
        $DeliveryDate = date('Ymd', strtotime('-20 day', time()));
        }

        //四七日の場合
        if ($noticeSchedule == '28282828') {
            // 27日前
        $DeliveryDate = date('Ymd', strtotime('-27 day', time()));
        }

        //五七日の場合
        if ($noticeSchedule == '35353535') {
            // 34日前
        $DeliveryDate = date('Ymd', strtotime('-34 day', time()));
        }

        //六七日の場合
        if ($noticeSchedule == '42424242') {
            // 41日前
        $DeliveryDate = date('Ymd', strtotime('-41 day', time()));
        }
        
        //四十九日の場合
        if ($noticeSchedule == '49494949') {
            // 48日前
        $DeliveryDate = date('Ymd', strtotime('-48 day', time()));
        }


        //法要通知の場合
        if ($noticeSchedule == '77777777'||
            $noticeSchedule == '14141414'||
            $noticeSchedule == '21212121'||
            $noticeSchedule == '28282828'||
            $noticeSchedule == '35353535'||
            $noticeSchedule == '42424242'||
            $noticeSchedule == '49494949'
            ) {
            $arrayNoticeInfo  = $cooperationModel->getNoticeHoyoInfoAndDeceasedID($noticeSchedule,$deviceToken,$DeliveryDate);
        //お知らせ通知の場合
        }else{
            $arrayNoticeInfo  = $cooperationModel->getNoticeInfoAndDeceasedID($noticeSchedule, $deviceToken);
        }
        
        if (count($arrayNoticeInfo) > 0) {
            //法要通知の場合
            if ($noticeSchedule == '77777777'||
                $noticeSchedule == '14141414'||
                $noticeSchedule == '21212121'||
                $noticeSchedule == '28282828'||
                $noticeSchedule == '35353535'||
                $noticeSchedule == '42424242'||
                $noticeSchedule == '49494949'
                ) {
                    $noticeInfoData  = array('noticeInfo' => $arrayNoticeInfo);
                    $jNoticeInfoData = Zend_Json::encode($noticeInfoData);
            //お知らせの場合
            }else{
                    $noticeInfoData  = array('noticeInfo' => $this->adjustNoticeInfo($arrayNoticeInfo));
                    $jNoticeInfoData = Zend_Json::encode($noticeInfoData);
            }

            echo $jNoticeInfoData;
        } else {
            echo '';
        }
    }

    //デバイストークン保存処理
    public function saveiosdevicetokenAction() {
        //パラメータからデバイストークンを取得
        $deviceToken = $this->getRequest()->getPost('device_token');

        //テストコード
        //$deviceToken = $this->_request->getParam('dt');

        $cooperationModel = new cooperationModel();     //Cooperationモデルインスタンス生成

        //デバイストークンが存在しないかチェック
        if ($cooperationModel->checkDeviceToken($deviceToken) == false) {
            //存在しない場合保存する
            if ($cooperationModel->insertDeviceToken($deviceToken)) {
                echo 'OK';
            } else {
                echo '';
            }
        } else {
            echo 'OK';
        }
    }

    //iOSデバイストークン&故人ID保存処理
    public function saveiosdevicetokenanddeceasedidAction() {
        //パラメータからデバイストークンを取得
        $deviceToken = $this->getRequest()->getPost('device_token');
        //パラメータから故人IDを取得
        $deceasedId = $this->getRequest()->getPost('deceased_id');

        //テストコード
//        $deviceToken = $this->_request->getParam('device_token');
//        $deceasedId = $this->_request->getParam('deceased_id');

        $cooperationModel = new cooperationModel();     //Cooperationモデルインスタンス生成

        //デバイストークンが存在しないかチェック
        if ($cooperationModel->checkDeviceToken($deviceToken, $deceasedId) == false) {
            //存在しない場合保存する
            if ($cooperationModel->insertDeviceToken($deviceToken, $deceasedId)) {

                //故人情報取得
                $deceased = $cooperationModel->getDeceased($deceasedId);
                //故人没年月日をDate型に変換
                $deceasedDeathday = date("Y-m-d",strtotime($deceased['deceased_deathday']));
                //多次元連想配列に値を代入
                $noticetypeList[] = array('noticetype'=>7,  'pushtime'=>'+6 day');
                $noticetypeList[] = array('noticetype'=>14, 'pushtime'=>'+13 day');
                $noticetypeList[] = array('noticetype'=>21, 'pushtime'=>'+20 day');
                $noticetypeList[] = array('noticetype'=>28, 'pushtime'=>'+27 day');
                $noticetypeList[] = array('noticetype'=>35, 'pushtime'=>'+34 day');
                $noticetypeList[] = array('noticetype'=>42, 'pushtime'=>'+41 day');
                $noticetypeList[] = array('noticetype'=>49, 'pushtime'=>'+48 day');

                //法要スケジュール(初七日)が登録されているか
                $isHoyoNoticeschedule = $cooperationModel->getNoticeHoyoInfoDeliveredDay($deviceToken,$deceasedId,7);
                
                if (is_array($isHoyoNoticeschedule) && !empty($isHoyoNoticeschedule)) {
                //法要スケジュール（初七日）登録済みの場合
                    //法要スケジュールをＤＢに格納処理（アップデート）
                    foreach ($noticetypeList as $hoyonoticeinfo) {
                        if ($cooperationModel->updateHoyoNoticeschedule($deviceToken, 
                                                                        $deceasedId, 
                                                                        $hoyonoticeinfo['noticetype'], 
                                                                        date("Ymd",strtotime($deceasedDeathday . $hoyonoticeinfo['pushtime']))))
                                                                        {
                        }else{
                            //データ更新に失敗した場合
                            echo '';
                            break;
                        }

                    } 

                }else{
                //法要スケジュール（初七日）未登録の場合
                    //法要スケジュールをＤＢに格納処理（インサート）
                    foreach ($noticetypeList as $hoyonoticeinfo) {
                        if ($cooperationModel->insertHoyoNoticeschedule($deviceToken, 
                                                                        $deceasedId, 
                                                                        $hoyonoticeinfo['noticetype'], 
                                                                        date("Ymd",strtotime($deceasedDeathday . $hoyonoticeinfo['pushtime']))))
                                                                        {
                        }else{
                            //データ格納に失敗した場合
                            echo '';
                            break;
                        }

                    } 
                }
                // echo 'OK';
            } else {
                echo '';
            }
        } else {

            echo 'OK';
        }
    }
    
    //デバイストークン取得処理
    public function getiosdevicetokenallAction() {
        //デバイストークンを取得
        $cooperationModel = new cooperationModel();     //Cooperationモデルインスタンス生成
        $deviceToken = $cooperationModel->selectDeviceToken();
        
        //デバイストークンをJSON形式に変換
        $jDeviceToken = Zend_Json::encode($deviceToken);
        
        // JSON形式のデバイストークンを返す
        echo $jDeviceToken;
    }
    
    //AndroidレジストレーションID保存処理
    public function saveandroidregistrationAction() {
        //レジストレーションIDと故人IDを取得
        $registration = $this->getRequest()->getPost('registration_id');
        $deceasedId = $this->getRequest()->getPost('deceased_id');
        
        $cooperationModel = new cooperationModel();
        $is_exist = $cooperationModel->checkRegistrationID($registration, $deceasedId);
        
        if(!$is_exist) {
            //データベースに未登録の場合は登録
            if ($cooperationModel->insertRegistrationID($registration, $deceasedId)) {
                //故人情報取得
                $deceased = $cooperationModel->getDeceased($deceasedId);
                //故人没年月日をDate型に変換
                $deceasedDeathday = date("Y-m-d",strtotime($deceased['deceased_deathday']));
                //多次元連想配列に値を代入
                $noticetypeList[] = array('noticetype'=>7,  'pushtime'=>'+6 day');
                $noticetypeList[] = array('noticetype'=>14, 'pushtime'=>'+13 day');
                $noticetypeList[] = array('noticetype'=>21, 'pushtime'=>'+20 day');
                $noticetypeList[] = array('noticetype'=>28, 'pushtime'=>'+27 day');
                $noticetypeList[] = array('noticetype'=>35, 'pushtime'=>'+34 day');
                $noticetypeList[] = array('noticetype'=>42, 'pushtime'=>'+41 day');
                $noticetypeList[] = array('noticetype'=>49, 'pushtime'=>'+48 day');

                //法要スケジュール(初七日)が登録されているか
                $isHoyoNoticeschedule = $cooperationModel->getNoticeHoyoInfoDeliveredDayRegstID($registration,$deceasedId,7);

                if (is_array($isHoyoNoticeschedule) && !empty($isHoyoNoticeschedule)) {
                //法要スケジュール（初七日）登録済みの場合
                    //法要スケジュールをＤＢに格納処理（アップデート）
                    foreach ($noticetypeList as $hoyonoticeinfo) {
                        if ($cooperationModel->updateHoyoNoticescheduleRegistId($registration, 
                                                                                $deceasedId, 
                                                                                $hoyonoticeinfo['noticetype'], 
                                                                                date("Ymd",strtotime($deceasedDeathday . $hoyonoticeinfo['pushtime']))))
                                                                                {
                        }else{
                            //データ更新に失敗した場合
                            echo '';
                            break;
                        }

                    } 
                }else{
                //法要スケジュール（初七日）未登録の場合
                    //法要スケジュールをＤＢに格納処理（インサート） 
                    foreach ($noticetypeList as $hoyonoticeinfo) {
                        if ($cooperationModel->insertHoyoNoticescheduleRegistId($registration, 
                                                                                $deceasedId, 
                                                                                $hoyonoticeinfo['noticetype'], 
                                                                                date("Ymd",strtotime($deceasedDeathday . $hoyonoticeinfo['pushtime']))))
                                                                                {
                        }else{
                            //データ格納に失敗した場合
                            echo '';
                            break;
                        }

                    } 
                }


            }
        }
    }

    //AndroidレジストレーションID更新処理
    public function updateandroidregistrationAction() {
        //新旧IDを取得
        $old_id = $this->getRequest()->getPost('old_id');
        $update_id = $this->getRequest()->getPost('update_id');
        
        $cooperationModel = new cooperationModel();
        $cooperationModel->updateRegistrationID($old_id, $update_id);
    }

    // お知らせ番号からURLを取得する
    public function getnoticebynumberAction() {
        //Post値からお知らせ番号を取得
        $notice_no = $this->getRequest()->getPost('notice_no');
        $deceased_id = $this->getRequest()->getPost('deceased_id');
        
        $cooperationModel = new cooperationModel();
        $record = $cooperationModel->getNoticeInfoByNo($notice_no);

        $target = array_shift($record);
        if(is_null($target)) return;

        if(strcmp($target["entry_method"], '1') == 0) {
            // お知らせが手入力の場合
            $result = empty($_SERVER["HTTPS"]) ? "http://" : "https://";
            $result .= $_SERVER["HTTP_HOST"].'/mng/viewnoticeinfo?nino='.$notice_no;
            if(!empty($deceased_id)) {
                $result .= "&deceased_id=".$deceased_id;
            }
        } else {
            // お知らせがURL指定の場合
            $result = $target["url"];
        }

        echo $result;
    }
}
