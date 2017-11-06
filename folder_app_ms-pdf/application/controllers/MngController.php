<?php

/**
 * 法要アプリプレミアム版管理システムを制御するコントローラクラス
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
require_once 'Zend/Session.php';
require_once 'Zend/Paginator.php';
require_once 'Zend/Paginator/Adapter/Array.php';

/*Xサーバーでサブドメインの場合*/
require_once 'application/common/comDefine.php';
require_once 'application/common/comValidate.php';
require_once 'application/common/comEncryption.php';
require_once 'application/common/comToken.php';
require_once 'application/smarty/Zend_View_Smarty.class.php';
require_once 'application/models/mngModel.php';
require_once 'application/models/logModel.php';
require_once 'application/common/comMail.php';
require_once 'application/common/comGuidanceIssue.php';
require_once 'application/common/common.php';

/*サブドメインでない場合
require_once '../application/common/comDefine.php';
require_once '../application/common/comValidate.php';
require_once '../application/common/comEncryption.php';
require_once '../application/common/comToken.php';
require_once '../application/smarty/Zend_View_Smarty.class.php';
require_once '../application/models/mngModel.php';
require_once '../application/models/logModel.php';
require_once '../application/common/comMail.php';
require_once '../application/common/comGuidanceIssue.php';
 require_once '../application/common/common.php';
 */

//定数
//Cookie名
define('COOKIE_ID', 'ci');
define('COOKIE_PASSWORD', 'cp');
//セッション有効時間（12時間）
define('SESSION_TIME', 60*60*12);
//Cookie有効期限（30日）
define('COOKIE_EXPIRATION', 60*60*24*30);
//通知情報画像MAXサイズ（10MB）
define('MAX_IMAGE_FILE_SIZE', 10485760);
//通知情報一覧1ページの表示件数
define('NOTICE_LIST_NUMBER', 15);

//通知情報　命日で検索時のテンプレート番号
define('NOTICE_TEMPNO_DEATHDAY', 1);
//通知情報　法要で検索時のテンプレート番号
define('NOTICE_TEMPNO_EVENT', 2);
//通知情報　初七日法要のテンプレート番号
define('NOTICE_TEMPNO_SEVENTH_DEATHDAY', 3);
//通知情報　二七日法要のテンプレート番号
define('NOTICE_TEMPNO_FOURTEENDAY_DEATHDAY', 4);
//通知情報　三七日法要のテンプレート番号
define('NOTICE_TEMPNO_TWENTYONEDAY_DEATHDAY', 5);
//通知情報　四七日法要のテンプレート番号
define('NOTICE_TEMPNO_TWENTYEIGHT_DEATHDAY', 6);
//通知情報　五七日法要のテンプレート番号
define('NOTICE_TEMPNO_THIRTYFIVE_DEATHDAY', 7);
//通知情報　六七日法要のテンプレート番号
define('NOTICE_TEMPNO_FORTYTWO_DEATHDAY', 8);
//通知情報　四十九日法要のテンプレート番号
define('NOTICE_TEMPNO_FORTYNINE_DEATHDAY', 9);

//故人様一覧1ページの表示件数
define('DECEASED_LIST_NUMBER', 25);
//ログ種別
define('LOG_KIND_OTHER', 0);
define('LOG_KIND_LOGIN', 1);
define('LOG_KIND_NOTICE_ENTRY', 2);
define('LOG_KIND_NOTICE_EDIT', 3);
define('LOG_KIND_NOTICE_DELETE', 4);
define('LOG_KIND_CHARGE_ADD', 5);
define('LOG_KIND_CHARGE_DELETE', 6);
define('LOG_KIND_PASSWORD_CHANGE', 7);
define('LOG_KIND_QR_ORDER', 8);
define('LOG_KIND_QR_CANCEL', 9);
define('LOG_KIND_QR_PDF_DOWNLOAD', 10);
define('LOG_KIND_DECEASED_LOGIC_DELETE', 11);
define('LOG_KIND_DECEASED_EDIT', 12);
define('LOG_KIND_DECEASE_COMP_DELETE', 13);

class MngController extends Zend_Controller_Action
{
    private $_session;                  //セッション
    private $_config;                   //設定情報
    private $_mngModel;                 //mngModelのインスタンス
    private $_logModel;                 //logModelのインスタンス
    private $_view;                     //Zend_View_Smartyのインスタンス
    private $_httpHeaderInfo;           //ヘッダー情報格納変数

    private $_yearList;                 //今年と来年の西暦を格納したarray
    private $_monthList;                //1～12月までの月名を格納したarray
    private $_memorialEvent;            //回忌法要名

    /**
     * 初期化処理
     */
    public function init()
    {
        //設定情報をロードする
        /*Xサーバーでサブドメインの場合*/
        $this->_config = new Zend_Config_Ini('application/config/config.ini', null);
        /**/
        /*サブドメインでない場合
        $this->_config = new Zend_Config_Ini('../application/config/config.ini', null);
        /**/

        //データベース関連の設定をレジストリに登録する
        Zend_Registry::set('database', $this->_config->datasource->database->toArray());

        //mngModelのインスタンスを生成する
        $this->_mngModel = new mngModel();
        //logModelのインスタンスを生成する
        $this->_logModel = new logModel();

        //Zend_View_Smartyを生成してviewを上書きする
        $this->_view = new Zend_View_Smarty();
        //ビュースクリプトのディレクトリを設定する
        $this->_view->setScriptPath(SMARTY_TEMP_PATH . 'templates');
        //ビュースクリプトのコンパイルディレクトリを設定する
        $this->_view->setCompilePath(SMARTY_TEMP_PATH . 'templates_c');

        //セッションを開始する
        $this->_session = new Zend_Session_Namespace('management');
        //セッションタイムアウトを設定する
        $this->_session->setExpirationSeconds(SESSION_TIME);

        //ヘッダー情報を取得
        $this->_httpHeaderInfo = 'HTTP_USER_AGENT：' . $this->_request->getServer('HTTP_USER_AGENT') . "\n" .
                                 'REMOTE_ADDR：' . $this->_request->getServer('REMOTE_ADDR') . "\n" .
                                 'REMOTE_HOST：' . $this->_request->getServer('REMOTE_HOST') . "\n" .
                                 'REMOTE_PORT：' . $this->_request->getServer('REMOTE_PORT');

        //今年来年の西暦を作成
        $this->_yearList = array("" => "");
        $thisYear = date('Y');
        $this->_yearList += array($thisYear => "今年");
        $thisYear = str_pad((int)$thisYear + 1, 2, '0', STR_PAD_LEFT);
        $this->_yearList += array($thisYear => "来年");

        //月名を作成
        $this->_monthList = array("" => "");
        for ($i=1; $i < 13; $i++) $this->_monthList += array((string)$i => (string)$i.'月');

        //法要名を作成
        $this->_memorialEvent = array('' => '', '1' => '一周忌', '3' => '三回忌', '7' => '七回忌',
                                     '13' => '十三回忌', '17' => '十七回忌', '23' => '二十三回忌',
                                     '25' => '二十五回忌', '27' => '二十七回忌', '33' => '三十三回忌',
                                     '37' => '三十七回忌', '50' => '五十回忌', '100' => '百回忌');
    }

    /**
     * chkSessionメソッド
     * ：セッションが有効か（ログイン済みか）チェックする
     * @return boolean true:有効 false:無効
     */
    private function chkSession()
    {
        $session = true;
        if (isset($this->_session->is_login) === false) {
            $session = false;
        }
        return $session;
    }

    //初期表示
    public function indexAction()
    {
        if ($this->chkSession() === true) {
            //ログイン済みの場合
            //故人様一覧画面表示処理に遷移
            return $this->_forward('dispdeceasedlist');
        }

        //CookieからIDとPWを取得する
        $cookieId = comEncryption::decryption(filter_input(INPUT_COOKIE, COOKIE_ID));
        $cookiePassword = comEncryption::decryption(filter_input(INPUT_COOKIE, COOKIE_PASSWORD));

        //CookieのIDとPWをチェック
        if ($this->checkLogin($cookieId, $cookiePassword)) {
            //CookieのIDとPWが正しい場合
            //CookieにIDとPWを再設定
            setcookie(COOKIE_ID, comEncryption::encryption($cookieId), time() + COOKIE_EXPIRATION, '/mng/', $this->_config->domain->url);
            setcookie(COOKIE_PASSWORD, comEncryption::encryption($cookiePassword), time() + COOKIE_EXPIRATION, '/mng/', $this->_config->domain->url);
            //最終ログイン日時を更新
            $this->_mngModel->updateLastLoginDateTime($this->_session->manager_id);
            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_LOGIN, $this->_session->manager_id, "Auto Login Success", $this->_httpHeaderInfo);
            //ログイン画面を介さずに故人様一覧画面を表示
            return $this->_forward('dispdeceasedlist');
        } else {
            //CookieのIDとPWが正しくない場合
            //IDとPWをviewに設定する
            $this->_view->message = "";
            $this->_view->id = $cookieId;
            $this->_view->password = $cookiePassword;
            $this->_view->checked = "";
            //ログイン画面を表示
            echo $this->_view->render('mng_login.tpl');
        }
    }

    //ログイン実行
    public function loginAction()
    {
        //POST値を取得
        $id = $this->getRequest()->getPost('id');                   //ログインID
        $password = $this->getRequest()->getPost('password');       //パスワード
        $autoLogin = $this->getRequest()->getPost('autologin');     //自動ログイン

        //IDとPWの入力チェック
        if ($this->checkLogin($id, $password)) {
            //正しい場合
            //自動ログインにチェックがある場合、ログイン情報をCookieに保存する
            if ($autoLogin === "on") {
                setcookie(COOKIE_ID, comEncryption::encryption($id), time() + COOKIE_EXPIRATION, '/mng/', $this->_config->domain->url);
                setcookie(COOKIE_PASSWORD, comEncryption::encryption($password), time() + COOKIE_EXPIRATION, '/mng/', $this->_config->domain->url);
            }
            //最終ログイン日時を更新
            $this->_mngModel->updateLastLoginDateTime($this->_session->manager_id);
            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_LOGIN, $this->_session->manager_id, "Success", $this->_httpHeaderInfo);
            //故人様一覧画面を表示
            return $this->_forward('dispdeceasedlist');
        } else {
            //不正な場合
            //エラーメッセージと入力したID、PWをviewに設定
            $this->_view->message = "入力したIDまたはパスワードが不正です。";
            $this->_view->id = $id;
            $this->_view->password = $password;
            if ($autoLogin === "on") {
                $this->_view->checked = "checked";
            } else {
                $this->_view->checked = "";
            }
            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_LOGIN, "", "Failure", $this->_httpHeaderInfo);
            //ログイン画面に戻る
            echo $this->_view->render('mng_login.tpl');
        }
    }

    //ログアウト実行
    public function logoutAction()
    {
        //Cookieを削除
        setcookie(COOKIE_ID, '', time() - COOKIE_EXPIRATION, '/mng/', $this->_config->domain->url);
        setcookie(COOKIE_PASSWORD, '', time() - COOKIE_EXPIRATION, '/mng/', $this->_config->domain->url);

        // セッションをクリアする
        Zend_Session::destroy();

        //メッセージと入力したID、PWをviewに設定
        $this->_view->message = "ログアウトしました。";
        $this->_view->id = "";
        $this->_view->password = "";
        $this->_view->checked = "";

        //ログイン画面を表示する
        echo $this->_view->render('mng_login.tpl');
    }

    //再ログイン画面
    public function dispreloginAction()
    {
        //CookieからIDとPWを取得する
        $cookieId = comEncryption::decryption(filter_input(INPUT_COOKIE, COOKIE_ID));
        $cookiePassword = comEncryption::decryption(filter_input(INPUT_COOKIE, COOKIE_PASSWORD));

        //エラーメッセージと入力したID、PWをviewに設定
        $this->_view->message = "ページの有効期限が切れた為、ログアウトしました。<br />お手数ですがもう一度ログインしてください。";
        $this->_view->id = $cookieId;
        $this->_view->password = $cookiePassword;
        $this->_view->checked = "";

        //ログイン画面を表示する
        echo $this->_view->render('mng_login.tpl');
    }

    //通知情報一覧画面表示
    public function dispnoticeinfolistAction()
    {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //DBから通知情報を取得
        $noticeInfoList = $this->_mngModel->getNoticeInfoList();

        // ページング処理
        // ページ番号のリクエストがあれば取得（無ければ1ページ目とする）
        $pagenum = $this->getRequest()->getParam('page', 1);
        //セッションにページ番号を格納
        $this->_session->page = $pagenum;
        // ページネーターを取得
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($noticeInfoList));
        // 現在ページを設定
        $paginator->setCurrentPageNumber($pagenum);
        // 1ページあたりの表示件数を設定
        $paginator->setItemCountPerPage(NOTICE_LIST_NUMBER);
        // テンプレートにページネーター（通知情報一覧）を設定
        $this->_view->noticeInfoList = $paginator;
        // ページ情報を取得
        $pager = $paginator->getPages();
        $this->_view->total = $pager->totalItemCount;                // 全データ数
        $this->_view->all = $pager->pageCount;                       // 全ページ数
        $this->_view->now = $pager->current;                         // 現ページ数
        $this->_view->firstItemNumber = $pager->firstItemNumber;     // 現ページの最初の項目数
        $this->_view->lastItemNumber = $pager->lastItemNumber;       // 現ページの最後の項目数
        $this->_view->pagesInRange = $pager->pagesInRange;           // ページの配列

        //通知情報一覧画面表示
        echo $this->_view->render('mng_notice_info_list.tpl');
    }

    //通知情報登録画面表示
    public function dispentrynoticeinfoAction()
    {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }
        //通知情報登録画面表示
        $this->dispEntryNoticeInfo("");
        echo $this->_view->render('mng_notice_info_entry.tpl');
    }


    //通知情報登録画面表示(追善法要)
    public function dispentrynoticeinfodayafterdeathAction()
    {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //GET値から法要通知種類を取得
        $noticeTypeNo = $this->getRequest()->getQuery('ntype');

        //通知登録の有無を調べる
        $noticeInfoList = $this->_mngModel->getNoticeInfodayafterdeathEntryList($noticeTypeNo);

        if ($noticeInfoList) {
            //登録済みの場合
                // //通知情報登録画面表示
                // $this->dispEntryNoticeInfo("");
                // echo $this->_view->render('mng_notice_hoyo_info_edit.tpl');

                //POST値から通知Noを取得
                    //通知情報を取得
                    $noticeInfo = $this->_mngModel->getNoticeHoyoInfo($noticeTypeNo);

                    //セッションに画像のパスを設定
                    $this->_session->image_path = NOTICE_IMG_PATH . $noticeInfo['notice_info_no'] . '.jpg';

                    //通知情報編集画面表示
                    if(empty($noticeInfo) === false){
                        $this->dispEntryNoticeHoyoInfo($noticeInfo['notice_type'],"", $noticeInfo);
                        $this->_view->noticeInfoNo = $noticeInfo['notice_info_no'];
                        echo $this->_view->render('mng_notice_hoyo_info_edit.tpl');
                    }else{
                        echo $this->_view->render('mng_error.tpl');
                    }
        }else{
            //登録未の場合
                //通知情報登録画面表示
                $this->dispEntryNoticeHoyoInfo($noticeTypeNo,"");
                echo $this->_view->render('mng_notice_hoyo_info_entry.tpl');
        }
    }


    /**
     * 通知情報登録画面表示処理(定期通知：追善法要)
     * ：通知情報登録画面を表示する
     * @param string    $message    エラーメッセ―ジ
     * @param array     $noticeInfo 通知情報
     */

    private function dispEntryNoticeHoyoInfo($noticeType, $message, array $noticeInfo = null){

        //メッセージをviewに設定
        $this->_view->message = $message;
        if (is_null($noticeInfo)) {
            //viewを設定
            $this->_view->noticeTitle    = "";

            switch ($noticeType) {
                case 7:
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_SEVENTH_DEATHDAY);
                    $this->_view->template = common::makeTemplate($template['template_text'],
                                                                  '[お名前]',
                                                                  '',
                                                                  '');
                    $this->_view->templateId        = NOTICE_TEMPNO_SEVENTH_DEATHDAY;
                    $this->_view->noticeTypeTitle   = "初七日法要";
                    $this->_view->noticeTypeNumber  = 7;

                    break;

                case 14:
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_FOURTEENDAY_DEATHDAY);
                    $this->_view->template = common::makeTemplate($template['template_text'],
                                                                  '[お名前]',
                                                                  '',
                                                                  '');
                    $this->_view->templateId        = NOTICE_TEMPNO_FOURTEENDAY_DEATHDAY;
                    $this->_view->noticeTypeTitle   = "二七日法要";
                    $this->_view->noticeTypeNumber  = 14;

                    break;

                case 21:
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_TWENTYONEDAY_DEATHDAY);
                    $this->_view->template = common::makeTemplate($template['template_text'],
                                                                  '[お名前]',
                                                                  '',
                                                                  '');
                    $this->_view->templateId        = NOTICE_TEMPNO_TWENTYONEDAY_DEATHDAY;
                    $this->_view->noticeTypeTitle   = "三七日法要";
                    $this->_view->noticeTypeNumber  = 21;

                    break;

                case 28:
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_TWENTYEIGHT_DEATHDAY);
                    $this->_view->template = common::makeTemplate($template['template_text'],
                                                                  '[お名前]',
                                                                  '',
                                                                  '');
                    $this->_view->templateId        = NOTICE_TEMPNO_TWENTYEIGHT_DEATHDAY;
                    $this->_view->noticeTypeTitle   = "四七日法要";
                    $this->_view->noticeTypeNumber  = 28;
                    break;

                case 35:
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_THIRTYFIVE_DEATHDAY);
                    $this->_view->template = common::makeTemplate($template['template_text'],
                                                                  '[お名前]',
                                                                  '',
                                                                  '');
                    $this->_view->templateId        = NOTICE_TEMPNO_THIRTYFIVE_DEATHDAY;
                    $this->_view->noticeTypeTitle   = "五七日法要";
                    $this->_view->noticeTypeNumber  = 35;
                    break;

                case 42:
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_FORTYTWO_DEATHDAY);
                    $this->_view->template = common::makeTemplate($template['template_text'],
                                                                  '[お名前]',
                                                                  '',
                                                                  '');
                    $this->_view->templateId        = NOTICE_TEMPNO_FORTYTWO_DEATHDAY;
                    $this->_view->noticeTypeTitle   = "六七日法要";
                    $this->_view->noticeTypeNumber  = 42;
                    break;

                case 49:
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_FORTYNINE_DEATHDAY);
                    $this->_view->template = common::makeTemplate($template['template_text'],
                                                                  '[お名前]',
                                                                  '',
                                                                  '');
                    $this->_view->templateId        = NOTICE_TEMPNO_FORTYNINE_DEATHDAY;
                    $this->_view->noticeTypeTitle   = "四十九日法要";
                    $this->_view->noticeTypeNumber  = 49;
                    break;
            }

            $this->_view->noticeText     = "";
            $this->_view->imageExistenceFlg = 0;
            $this->_view->url = "";
        } else {
            //キャッシュ対策日時
            $date = new Zend_Date();
            //viewに入力値を設定
            //通知条件設定
            // $this->_view->selectedCategory = $noticeInfo['selected_category'];
            // switch ($noticeInfo['selected_category']) {
            //     case 0:
            //         $this->_view->settingChecked0 = "checked";
            //         $this->_view->template = "なし";
            //         $this->_view->templateId = "";
            //         break;
            //     case 1:
            //         $this->_view->settingChecked1 = "checked";
            //         $this->_view->chargeName = $noticeInfo['charge_name'];
            //         $this->_view->template = "なし";
            //         $this->_view->templateId = "";
            //         break;
            //     case 2:
            //         $this->_view->settingChecked2 = "checked";
            //         $this->_view->hallName = $noticeInfo['hall_name'];
            //         $this->_view->template = "なし";
            //         $this->_view->templateId = "";
            //         break;
            //     case 3:
            //         $this->_view->settingChecked3 = "checked";
            //         $this->_view->searchName  = $noticeInfo['search_name'];
            //         $this->_view->searchYear  = $noticeInfo['search_year'];
            //         $this->_view->searchMonth = $noticeInfo['search_month'];
            //         $this->_view->searchDay   = $noticeInfo['search_day'];
            //         $this->_view->template = "なし";
            //         $this->_view->templateId = "";
            //         break;
            //     case 4:
            //         $this->_view->settingChecked4 = "checked";
            //         $this->_view->deathMonth = $noticeInfo['death_month'];
            //         $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_DEATHDAY);
            //         $this->_view->template = common::makeTemplate($template['template_text'],
            //                                                       '[お名前]',
            //                                                       $noticeInfo['death_month'],
            //                                                       '');
            //         $this->_view->templateId = NOTICE_TEMPNO_DEATHDAY;
            //         break;
            //     case 5:
            //         $this->_view->settingChecked5 = "checked";
            //         $this->_view->memorialYear  = $noticeInfo['memorial_year'];
            //         $this->_view->memorialMonth = $noticeInfo['memorial_month'];
            //         $this->_view->memorialEvent = $noticeInfo['memorial_event'];
            //         $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_EVENT);
            //         $this->_view->template  = common::makeTemplate($template['template_text'],
            //                                                        '[お名前]',
            //                                                        $noticeInfo['memorial_month'],
            //                                                        $this->_memorialEvent[$noticeInfo['memorial_event']]);
            //         $this->_view->templateId = NOTICE_TEMPNO_EVENT;
            //         break;
            // }
            //通知情報
            // $this->_view->noticeSchedule = $noticeInfo['notice_schedule'];
            // if ($noticeInfo['entry_method'] == ENTRY_METHOD_INPUT) {
            //     $this->_view->checked1 = "checked";
            //     $this->_view->checked2 = "";
            // } elseif ($noticeInfo['entry_method'] == ENTRY_METHOD_URL) {
            //     $this->_view->checked1 = "";
            //     $this->_view->checked2 = "checked";
            // }

            switch ($noticeType) {
                case 7:
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_SEVENTH_DEATHDAY);
                    $this->_view->template = common::makeTemplate($template['template_text'],
                                                                  '[お名前]',
                                                                  '',
                                                                  '');
                    $this->_view->templateId = NOTICE_TEMPNO_SEVENTH_DEATHDAY;
                    $this->_view->noticeTypeTitle = "初七日法要";
                    break;

                case 14:
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_FOURTEENDAY_DEATHDAY);
                    $this->_view->template = common::makeTemplate($template['template_text'],
                                                                  '[お名前]',
                                                                  '',
                                                                  '');
                    $this->_view->templateId = NOTICE_TEMPNO_FOURTEENDAY_DEATHDAY;
                    $this->_view->noticeTypeTitle = "二七日法要";
                    break;

                case 21:
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_TWENTYONEDAY_DEATHDAY);
                    $this->_view->template = common::makeTemplate($template['template_text'],
                                                                  '[お名前]',
                                                                  '',
                                                                  '');
                    $this->_view->templateId = NOTICE_TEMPNO_TWENTYONEDAY_DEATHDAY;
                    $this->_view->noticeTypeTitle = "三七日法要";
                    break;

                case 28:
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_TWENTYEIGHT_DEATHDAY);
                    $this->_view->template = common::makeTemplate($template['template_text'],
                                                                  '[お名前]',
                                                                  '',
                                                                  '');
                    $this->_view->templateId = NOTICE_TEMPNO_TWENTYEIGHT_DEATHDAY;
                    $this->_view->noticeTypeTitle = "四七日法要";
                    break;

                case 35:
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_THIRTYFIVE_DEATHDAY);
                    $this->_view->template = common::makeTemplate($template['template_text'],
                                                                  '[お名前]',
                                                                  '',
                                                                  '');
                    $this->_view->templateId = NOTICE_TEMPNO_THIRTYFIVE_DEATHDAY;
                    $this->_view->noticeTypeTitle = "五七日法要";
                    break;

                case 42:
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_FORTYTWO_DEATHDAY);
                    $this->_view->template = common::makeTemplate($template['template_text'],
                                                                  '[お名前]',
                                                                  '',
                                                                  '');
                    $this->_view->templateId = NOTICE_TEMPNO_FORTYTWO_DEATHDAY;
                    $this->_view->noticeTypeTitle = "六七日法要";
                    break;

                case 49:
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_FORTYNINE_DEATHDAY);
                    $this->_view->template = common::makeTemplate($template['template_text'],
                                                                  '[お名前]',
                                                                  '',
                                                                  '');
                    $this->_view->templateId = NOTICE_TEMPNO_FORTYNINE_DEATHDAY;
                    $this->_view->noticeTypeTitle = "四十九日法要";
                    break;
            }

            $this->_view->noticeTitle = $noticeInfo['notice_title'];
            $this->_view->noticeText = $noticeInfo['notice_text'];
            $this->_view->imageExistenceFlg = $noticeInfo['image_existence_flg'];
            $this->_view->cacheKey = $date->get("yyyyMMddHHmmss");
            $this->_view->url = $noticeInfo['url'];
            //画像が設定してある場合、画像の幅高さを取得してviewに設定する
            if ($noticeInfo['image_existence_flg'] == IMAGE_EXISTENCE_FLG_YES) {
                $this->_view->imgWH = $this->getImageSizeAttr($this->_session->image_path);
            }
        }
    }


    //通知情報登録確認画面表示(定期通知：追善法要)
    public function confentrynoticehoyoinfoAction()
    {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        if($this->getRequest()->getPost('back')) {
            //戻るボタン押下の場合は通知情報一覧画面に戻る
            return $this->dispnoticeinfolistAction();
        }

        $this->confNoticeHoyoInfo('mng_notice_hoyo_info_entry.tpl', 'mng_notice_hoyo_info_entry_conf.tpl');
    }


        //通知情報登録確認画面表示処理
    private function confNoticeHoyoInfo($inputpage_tpl, $confpage_tpl)
    {
        //入力値を取得
        $noticeInfo = array(
            // 'search_category'     => (int)$this->getRequest()->getPost('search_category'),
            'selected_category'   => 0,
            'charge_name'         => '',
            'hall_name'           => '',
            'search_name'         => '',
            'search_year'         => '',
            'search_month'        => '',
            'search_day'          => '',
            'death_month'         => '',
            'memorial_year'       => '',
            'memorial_month'      => '',
            'memorial_event'      => '',
            // 'notice_target'       => $this->getRequest()->getPost('deceased_checkbox'),
            // 'notice_schedule'     => $this->getRequest()->getPost('notice_schedule'),
            'notice_type'         => $this->getRequest()->getPost('notice_type'),
            'entry_method'        => 1,
            'notice_title'        => $this->getRequest()->getPost('notice_title'),
            'template_id'         => $this->getRequest()->getPost('template_id'),
            'notice_text'         => $this->getRequest()->getPost('notice_text'),
            'image_existence_flg' => $this->getRequest()->getPost('image_existence_flg'),
            'url'                 => $this->getRequest()->getPost('url')
        );

        $noticeInfoNo = $this->getRequest()->getPost('notice_info_no');
        if(!is_null($noticeInfoNo)) $noticeInfo['notice_info_no'] = $noticeInfoNo;

        // if ($this->getRequest()->getPost('search')) {
        //     //故人検索のとき
        //     $queryCategory = $noticeInfo['search_category'];
        // } else {
        //     //検索以外のとき
        //     $queryCategory = $noticeInfo['selected_category'];
        // }

        // switch ($queryCategory) {
        //     case 1:
        //         $noticeInfo['charge_name'] = $this->getRequest()->getPost('charge_name_combo');
        //         break;
        //     case 2:
        //         $noticeInfo['hall_name'] = $this->getRequest()->getPost('hall_name_combo');
        //         break;
        //     case 3:
        //         $noticeInfo['search_name']  = $this->getRequest()->getPost('search_name');
        //         $noticeInfo['search_year']  = $this->getRequest()->getPost('search_year');
        //         $noticeInfo['search_month'] = $this->getRequest()->getPost('search_month');
        //         $noticeInfo['search_day'] = $this->getRequest()->getPost('search_day');
        //         break;
        //     case 4:
        //         $noticeInfo['death_month'] = $this->getRequest()->getPost('death_day_combo');
        //         break;
        //     case 5:
        //         $noticeInfo['memorial_year']  = $this->getRequest()->getPost('memorial_year_combo');
        //         $noticeInfo['memorial_month'] = $this->getRequest()->getPost('memorial_month_combo');
        //         $noticeInfo['memorial_event'] = $this->getRequest()->getPost('memorial_combo');
        //         break;
        // }

        //画像が選択されている場合、一時フォルダに保存
        if (is_uploaded_file($_FILES["notice_image"]["tmp_name"])) {
            //選択されている場合、フラグを1にする
            $noticeInfo['image_existence_flg'] = IMAGE_EXISTENCE_FLG_YES;
            //一時保存用ファイル名を生成し、既にファイルが存在しないか重複チェック
            do {
                //一時保存用ファイル名を生成
                $fileName = comEncryption::getRandomString() . ".jpg";
                $uploadFile = NOTICE_IMG_TEMP_PATH . $fileName;
            } while(file_exists($uploadFile));
            //一時フォルダに保存
            //800×800に収まるように画像を作成
            //サーバ版
            exec('/usr/bin/convert -define jpeg:size=800x800 -resize 800x800 ' . $_FILES['notice_image']['tmp_name'] . ' ' .  $uploadFile);
            //パーミッションを変更
            chmod($uploadFile, 0644);
            //ファイルパスをセッションに設定
            $this->_session->image_path = $uploadFile;
        }

        // if($this->getRequest()->getPost('search')) {
        //     //検索ボタン押下の場合
        //     $noticeInfo['selected_category'] = $noticeInfo['search_category'];
        //     //検索結果を表示
        //     $this->dispEntryNoticeInfo("", $noticeInfo);
        //     echo $this->_view->render($inputpage_tpl);
        //     return;
        // }

        //入力値チェック
        $message = $this->checkNoticeHoyoInfo($noticeInfo);
        //ファイルアップロードチェック
        $message = $message . $this->checkNoticeImage();
        if (comValidate::chkNotEmpty($message) === false) {
            //入力値が正しい場合、確認画面を表示する
            //入力値をセッションに設定
            $this->_session->notice_info = $noticeInfo;

            //キャッシュ対策日時
            $date = new Zend_Date();
            //viewを設定
            // $this->_view->search_category = $noticeInfo['selected_category'];
            // switch ($noticeInfo['selected_category']) {
            //     case 1:
            //         $this->_view->chargeName = $noticeInfo['charge_name'];
            //         break;
            //     case 2:
            //         $this->_view->hallName = $noticeInfo['hall_name'];
            //         break;
            //     case 3:
            //         $this->_view->searchName = $noticeInfo['search_name'].' 様';
            //         $ymd = '';
            //         if(strcmp($noticeInfo['search_year'], '') == 0) $ymd = '－/';
            //         else $ymd = str_pad($noticeInfo['search_year'], 4, '0', STR_PAD_LEFT).'/';
            //         if(strcmp($noticeInfo['search_month'], '') == 0) $ymd .= '－/';
            //         else $ymd .= str_pad($noticeInfo['search_month'], 2, '0', STR_PAD_LEFT).'/';
            //         if(strcmp($noticeInfo['search_day'], '') == 0) $ymd .= '－';
            //         else $ymd .= str_pad($noticeInfo['search_day'], 2, '0', STR_PAD_LEFT);
            //         $this->_view->searchYMD = $ymd;
            //         break;
            //     case 4:
            //         $this->_view->deathMonth = $noticeInfo['death_month'];
            //         break;
            //     case 5:
            //         $this->_view->memorialYear  = $this->_yearList[$noticeInfo['memorial_year']];
            //         $this->_view->memorialMonth = $this->_monthList[$noticeInfo['memorial_month']];
            //         $this->_view->memorialEvent = $this->_memorialEvent[$noticeInfo['memorial_event']];
            //         break;
            // }
            // $this->_view->targetCount    = count($noticeInfo['notice_target']);
            // $this->_view->noticeSchedule = $noticeInfo['notice_schedule'];
            $this->_view->entryMethod    = 1;
            $this->_view->noticeTitle    = $noticeInfo['notice_title'];
            $this->_view->noticeText     = $noticeInfo['notice_text'];
            $this->_view->template          = $this->getTemplateString($noticeInfo);
            $this->_view->templateId        = $noticeInfo['template_id'];
            $this->_view->imageExistenceFlg = $noticeInfo['image_existence_flg'];
            $this->_view->cacheKey          = $date->get("yyyyMMddHHmmss");
            $this->_view->url               = $noticeInfo['url'];
            //画像が設定してある場合、画像の幅高さを取得してviewに設定する
            if ($noticeInfo['image_existence_flg'] == IMAGE_EXISTENCE_FLG_YES) {
                $this->_view->imgWH = $this->getImageSizeAttr($this->_session->image_path);
            }

            // ワンタイムトークンを発行し、セッションとフォームに設定
            $this->_session->key = session_id() . '_' . microtime();
            $this->_view->token = comToken::get_token($this->_session->key);

            //確認画面を表示
            echo $this->_view->render($confpage_tpl);
        } else {
            //入力値に不正がある場合、メッセージ、入力値を設定して入力画面に戻る
            $this->dispEntryNoticeHoyoInfo($this->getRequest()->getPost('template_id'), $message, $noticeInfo);
            echo $this->_view->render($inputpage_tpl);
        }
    }

   //通知情報登録完了画面表示(定期通知：追善法要)
    public function compentrynoticehoyoinfoAction()
    {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //セッションから入力値を取得
        $noticeInfo = $this->_session->notice_info;

        // 押されたボタンを判定する
        if ($this->getRequest()->getPost('back')) {              //戻るボタンの場合
            // 利用申し込み画面表示
            $this->dispEntryNoticeHoyoInfo($noticeInfo['template_id'],"", $noticeInfo);
            echo $this->_view->render('mng_notice_hoyo_info_entry.tpl');
            return;
        } elseif ($this->getRequest()->getPost('entry')) {       //登録ボタンの場合
            // ワンタイムトークンが正しいかチェックする
            if (comToken::check_token($this->getRequest()->getPost('token'), $this->_session->key) === false) {
                // エラー画面を表示
                echo $this->_view->render('mng_error.tpl');
                exit();
            }

            // セッション内のワンタイムトークを削除する
            if (isset($this->_session->key) === true) {
                unset($this->_session->key);
            }

            //DBに通知情報を保存
            if ($this->_mngModel->insertNoticeHoyoInfo($noticeInfo)) {
                //画像を選択している場合、一時フォルダから正式なフォルダに移動する
                if ($noticeInfo['image_existence_flg'] == IMAGE_EXISTENCE_FLG_YES) {
                    //今追加した通知情報の通知情報Noを取得する
                    $noticeInfoNo = $this->_mngModel->getLastNoticeInfoNo();
                    //一時フォルダの仮アップ画像を画像フォルダに移動
                    $imagePath = NOTICE_IMG_PATH . $noticeInfoNo['notice_info_no'] . '.jpg';
                    if (rename($this->_session->image_path, $imagePath)) {
                        //移動できたらセッションの画像パスに移動先のパスを指定
                        $this->_session->image_path = $imagePath;
                    } else {
                        echo "ファイルを移動出来ませんでした。";
                    }
                }

                $this->compNoticeInfo($noticeInfo);

                //ログ出力
                $this->_logModel->recordLog(LOG_KIND_NOTICE_ENTRY, $this->_session->manager_id, "Success", $this->_httpHeaderInfo);
                //完了画面を表示
                echo $this->_view->render('mng_notice_hoyo_info_entry_comp.tpl');
            } else {
                //ログ出力
                $this->_logModel->recordLog(LOG_KIND_NOTICE_ENTRY, $this->_session->manager_id, "Failure", $this->_httpHeaderInfo);
                //DB保存エラーの場合、エラー画面を表示
                echo $this->_view->render('mng_error.tpl');
            }
        }
    }



    //通知情報編集確認画面表示（追善法要）
    public function confeditnoticehoyoinfoAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        $this->_view->noticeInfoNo = $this->getRequest()->getPost('notice_info_no');
        $this->confNoticeInfo('mng_notice_hoyo_info_edit.tpl', 'mng_notice_hoyo_info_edit_conf.tpl');
    }

    /**
     * 通知情報チェック
     * ：通知情報が正しいかチェックするメソッド
     * @param   array   通知情報
     * @return  string  正しい場合空文字、不正な場合エラーメッセージ
     */
    private function checkNoticeHoyoInfo(array $noticeInfo)
    {
        $message = "";          //エラーメッセージ格納用
        // //通知予定日
        // //入力されているか
        // if (comValidate::chkNotEmpty($noticeInfo['notice_schedule']) === false) {
        //     $message = "・通知予定日が入力されていません。<br>";
        // } elseif(comValidate::chkDate($noticeInfo['notice_schedule']) === false) {    //日付形式が正しいか
        //     $message = "・通知予定日はYYYY/MM/DD形式（例：2014/03/10）で入力してください。<br>";
        // }

        //タイトル
        //入力されているか
        if (comValidate::chkNotEmpty($noticeInfo['notice_title']) === false) {
            $message = $message . "・タイトルが入力されていません。<br>";
        }

        //入力されているか
        if (comValidate::chkNotEmpty($noticeInfo['notice_text']) === false) {
            $message = $message . "・本文が入力されていません。<br>";
        }

        //URL
        //URLが入力されている場合、入力形式が正しいか
        if (comValidate::chkNotEmpty($noticeInfo['url']) &&
            comValidate::chkUrl($noticeInfo['url']) === false) {
            $message = $message . "・URLの入力形式が正しくありません。<br>";
        }

        // //登録方法が入力の場合
        // if ($noticeInfo['entry_method'] === 1) {
        //     //本文
        //     //入力されているか
        //     if (comValidate::chkNotEmpty($noticeInfo['notice_text']) === false) {
        //         $message = $message . "・本文が入力されていません。<br>";
        //     }

        //     //URL
        //     //URLが入力されている場合、入力形式が正しいか
        //     if (comValidate::chkNotEmpty($noticeInfo['url']) &&
        //         comValidate::chkUrl($noticeInfo['url']) === false) {
        //         $message = $message . "・URLの入力形式が正しくありません。<br>";
        //     }
        // //登録方法がURLの場合
        // } elseif ($noticeInfo['entry_method'] === 2) {
        //     //URL
        //     //入力されているか
        //     if (comValidate::chkNotEmpty($noticeInfo['url']) === false) {
        //         $message = $message . "・URLが入力されていません。<br>";
        //     } elseif (comValidate::chkUrl($noticeInfo['url']) === false) {  //URL形式が正しいか
        //         $message = $message . "・URLの入力形式が正しくありません。<br>";
        //     }
        // }

        return $message;
    }


    /**
     * 通知情報登録画面表示処理
     * ：通知情報登録画面を表示する
     * @param string    $message    エラーメッセ―ジ
     * @param array     $noticeInfo 通知情報
     */
    private function dispEntryNoticeInfo($message, array $noticeInfo = null) {
        //通知条件設定
        //担当者名
        $chargeList = $this->_mngModel->getChargeList();
        $chargeNames = $this->makeSelectboxSource($chargeList, 'charge_name');
        $this->_view->chargeList = $chargeNames;
        //会館名
        $hallList = $this->_mngModel->getHallList();
        $hallNames = $this->makeSelectboxSource($hallList, 'hall_name');
        $this->_view->hallList = $hallNames;
        //命日
        $this->_view->deathMonthList = $this->_monthList;
        //法要
        $this->_view->memorialYearList = $this->_yearList;
        $this->_view->memorialMonthList = $this->_monthList;
        $this->_view->memorialList = $this->_memorialEvent;

        //故人様一覧にセット
        $deceasedInfoList = $this->_mngModel->getDeceasedListByNotice($noticeInfo);
        if(is_null($noticeInfo) || $this->getRequest()->getPost('search')){
            //通知情報が空の時or検索ボタン押下時は全てチェックを付ける
            foreach ($deceasedInfoList as &$deceasedInfo) {
                $deceasedInfo['selected'] = true;
            }
        }else{
            //それ以外
            $selectedList = $noticeInfo['notice_target'];
            foreach ($deceasedInfoList as &$deceasedInfo) {
                $result = array_search($deceasedInfo['deceased_id'], $selectedList);
                if($result === FALSE || is_null($result) === TRUE) $deceasedInfo['selected'] = false;
                else $deceasedInfo['selected'] = true;
            }
            unset($deceasedInfo);
        }

        $this->_view->deceasedInfoList = $deceasedInfoList;

        //メッセージをviewに設定
        $this->_view->message = $message;
        if (is_null($noticeInfo)) {
            //viewを設定
            $this->_view->settingChecked0  = "checked";
            $this->_view->settingChecked1  = "";
            $this->_view->settingChecked2  = "";
            $this->_view->settingChecked3  = "";
            $this->_view->settingChecked4  = "";
            $this->_view->settingChecked5  = "";
            $this->_view->selectedCategory = 0;
            $this->_view->chargeName     = reset($chargeNames);
            $this->_view->hallName       = reset($hallNames);
            $this->_view->searchName     = "";
            $this->_view->searchYear     = "";
            $this->_view->searchMonth    = "";
            $this->_view->searchDay      = "";
            $this->_view->deathMonth     = reset($this->_monthList);
            $this->_view->memorialYear   = reset($this->_yearList);
            $this->_view->memorialMonth  = reset($this->_monthList);
            $this->_view->memorialEvent  = reset($this->_memorialEvent);
            $this->_view->noticeSchedule = "";
            $this->_view->checked1       = "checked";
            $this->_view->checked2       = "";
            $this->_view->noticeTitle    = "";
            $this->_view->template       = "なし";
            $this->_view->templateId     = "";
            $this->_view->noticeText     = "";
            $this->_view->imageExistenceFlg = 0;
            $this->_view->url = "";
        } else {
            //キャッシュ対策日時
            $date = new Zend_Date();
            //viewに入力値を設定
            //通知条件設定
            $this->_view->settingChecked0  = "";
            $this->_view->settingChecked1  = "";
            $this->_view->settingChecked2  = "";
            $this->_view->settingChecked3  = "";
            $this->_view->settingChecked4  = "";
            $this->_view->settingChecked5  = "";
            $this->_view->selectedCategory = $noticeInfo['selected_category'];
            switch ($noticeInfo['selected_category']) {
                case 0:
                    $this->_view->settingChecked0 = "checked";
                    $this->_view->template = "なし";
                    $this->_view->templateId = "";
                    break;
                case 1:
                    $this->_view->settingChecked1 = "checked";
                    $this->_view->chargeName = $noticeInfo['charge_name'];
                    $this->_view->template = "なし";
                    $this->_view->templateId = "";
                    break;
                case 2:
                    $this->_view->settingChecked2 = "checked";
                    $this->_view->hallName = $noticeInfo['hall_name'];
                    $this->_view->template = "なし";
                    $this->_view->templateId = "";
                    break;
                case 3:
                    $this->_view->settingChecked3 = "checked";
                    $this->_view->searchName  = $noticeInfo['search_name'];
                    $this->_view->searchYear  = $noticeInfo['search_year'];
                    $this->_view->searchMonth = $noticeInfo['search_month'];
                    $this->_view->searchDay   = $noticeInfo['search_day'];
                    $this->_view->template = "なし";
                    $this->_view->templateId = "";
                    break;
                case 4:
                    $this->_view->settingChecked4 = "checked";
                    $this->_view->deathMonth = $noticeInfo['death_month'];
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_DEATHDAY);
                    $this->_view->template = common::makeTemplate($template['template_text'],
                                                                  '[お名前]',
                                                                  $noticeInfo['death_month'],
                                                                  '');
                    $this->_view->templateId = NOTICE_TEMPNO_DEATHDAY;
                    break;
                case 5:
                    $this->_view->settingChecked5 = "checked";
                    $this->_view->memorialYear  = $noticeInfo['memorial_year'];
                    $this->_view->memorialMonth = $noticeInfo['memorial_month'];
                    $this->_view->memorialEvent = $noticeInfo['memorial_event'];
                    $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_EVENT);
                    $this->_view->template  = common::makeTemplate($template['template_text'],
                                                                   '[お名前]',
                                                                   $noticeInfo['memorial_month'],
                                                                   $this->_memorialEvent[$noticeInfo['memorial_event']]);
                    $this->_view->templateId = NOTICE_TEMPNO_EVENT;
                    break;
            }
            //通知情報
            $this->_view->noticeSchedule = $noticeInfo['notice_schedule'];
            if ($noticeInfo['entry_method'] == ENTRY_METHOD_INPUT) {
                $this->_view->checked1 = "checked";
                $this->_view->checked2 = "";
            } elseif ($noticeInfo['entry_method'] == ENTRY_METHOD_URL) {
                $this->_view->checked1 = "";
                $this->_view->checked2 = "checked";
            }
            $this->_view->noticeTitle = $noticeInfo['notice_title'];
            $this->_view->noticeText = $noticeInfo['notice_text'];
            $this->_view->imageExistenceFlg = $noticeInfo['image_existence_flg'];
            $this->_view->cacheKey = $date->get("yyyyMMddHHmmss");
            $this->_view->url = $noticeInfo['url'];
            //画像が設定してある場合、画像の幅高さを取得してviewに設定する
            if ($noticeInfo['image_existence_flg'] == IMAGE_EXISTENCE_FLG_YES) {
                $this->_view->imgWH = $this->getImageSizeAttr($this->_session->image_path);
            }
        }
    }

    //通知情報登録確認画面表示
    public function confentrynoticeinfoAction()
    {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        if($this->getRequest()->getPost('back')) {
            //戻るボタン押下の場合は通知情報一覧画面に戻る
            return $this->dispnoticeinfolistAction();
        }

        $this->confNoticeInfo('mng_notice_info_entry.tpl', 'mng_notice_info_entry_conf.tpl');
    }

    //通知情報登録確認画面表示処理
    private function confNoticeInfo($inputpage_tpl, $confpage_tpl)
    {
        //入力値を取得
        $noticeInfo = array(
            'search_category'     => (int)$this->getRequest()->getPost('search_category'),
            'selected_category'   => (int)$this->getRequest()->getPost('selected_category'),
            'charge_name'         => '',
            'hall_name'           => '',
            'search_name'         => '',
            'search_year'         => '',
            'search_month'        => '',
            'search_day'          => '',
            'death_month'         => '',
            'memorial_year'       => '',
            'memorial_month'      => '',
            'memorial_event'      => '',
            'notice_target'       => $this->getRequest()->getPost('deceased_checkbox'),
            'notice_schedule'     => $this->getRequest()->getPost('notice_schedule'),
            'entry_method'        => (int)$this->getRequest()->getPost('entry_method'),
            'notice_title'        => $this->getRequest()->getPost('notice_title'),
            'template_id'         => $this->getRequest()->getPost('template_id'),
            'notice_text'         => $this->getRequest()->getPost('notice_text'),
            'image_existence_flg' => $this->getRequest()->getPost('image_existence_flg'),
            'url'                 => $this->getRequest()->getPost('url')
        );

        $noticeInfoNo = $this->getRequest()->getPost('notice_info_no');
        if(!is_null($noticeInfoNo)) $noticeInfo['notice_info_no'] = $noticeInfoNo;

        if ($this->getRequest()->getPost('search')) {
            //故人検索のとき
            $queryCategory = $noticeInfo['search_category'];
        } else {
            //検索以外のとき
            $queryCategory = $noticeInfo['selected_category'];
        }

        switch ($queryCategory) {
            case 1:
                $noticeInfo['charge_name'] = $this->getRequest()->getPost('charge_name_combo');
                break;
            case 2:
                $noticeInfo['hall_name'] = $this->getRequest()->getPost('hall_name_combo');
                break;
            case 3:
                $noticeInfo['search_name']  = $this->getRequest()->getPost('search_name');
                $noticeInfo['search_year']  = $this->getRequest()->getPost('search_year');
                $noticeInfo['search_month'] = $this->getRequest()->getPost('search_month');
                $noticeInfo['search_day'] = $this->getRequest()->getPost('search_day');
                break;
            case 4:
                $noticeInfo['death_month'] = $this->getRequest()->getPost('death_day_combo');
                break;
            case 5:
                $noticeInfo['memorial_year']  = $this->getRequest()->getPost('memorial_year_combo');
                $noticeInfo['memorial_month'] = $this->getRequest()->getPost('memorial_month_combo');
                $noticeInfo['memorial_event'] = $this->getRequest()->getPost('memorial_combo');
                break;
        }

        //画像が選択されている場合、一時フォルダに保存
        if (is_uploaded_file($_FILES["notice_image"]["tmp_name"])) {
            //選択されている場合、フラグを1にする
            $noticeInfo['image_existence_flg'] = IMAGE_EXISTENCE_FLG_YES;
            //一時保存用ファイル名を生成し、既にファイルが存在しないか重複チェック
            do {
                //一時保存用ファイル名を生成
                $fileName = comEncryption::getRandomString() . ".jpg";
                $uploadFile = NOTICE_IMG_TEMP_PATH . $fileName;
            } while(file_exists($uploadFile));
            //一時フォルダに保存
            //800×800に収まるように画像を作成
            //サーバ版
            exec('/usr/bin/convert -define jpeg:size=800x800 -resize 800x800 ' . $_FILES['notice_image']['tmp_name'] . ' ' .  $uploadFile);
            //パーミッションを変更
            chmod($uploadFile, 0644);
            //ファイルパスをセッションに設定
            $this->_session->image_path = $uploadFile;
        }

        if($this->getRequest()->getPost('search')) {
            //検索ボタン押下の場合
            $noticeInfo['selected_category'] = $noticeInfo['search_category'];
            //検索結果を表示
            $this->dispEntryNoticeInfo("", $noticeInfo);
            echo $this->_view->render($inputpage_tpl);
            return;
        }

        //入力値チェック
        $message = $this->checkNoticeInfo($noticeInfo);
        //ファイルアップロードチェック
        $message = $message . $this->checkNoticeImage();
        if (comValidate::chkNotEmpty($message) === false) {
            //入力値が正しい場合、確認画面を表示する
            //入力値をセッションに設定
            $this->_session->notice_info = $noticeInfo;

            //キャッシュ対策日時
            $date = new Zend_Date();
            //viewを設定
            $this->_view->search_category = $noticeInfo['selected_category'];
            switch ($noticeInfo['selected_category']) {
                case 1:
                    $this->_view->chargeName = $noticeInfo['charge_name'];
                    break;
                case 2:
                    $this->_view->hallName = $noticeInfo['hall_name'];
                    break;
                case 3:
                    $this->_view->searchName = $noticeInfo['search_name'].' 様';
                    $ymd = '';
                    if(strcmp($noticeInfo['search_year'], '') == 0) $ymd = '－/';
                    else $ymd = str_pad($noticeInfo['search_year'], 4, '0', STR_PAD_LEFT).'/';
                    if(strcmp($noticeInfo['search_month'], '') == 0) $ymd .= '－/';
                    else $ymd .= str_pad($noticeInfo['search_month'], 2, '0', STR_PAD_LEFT).'/';
                    if(strcmp($noticeInfo['search_day'], '') == 0) $ymd .= '－';
                    else $ymd .= str_pad($noticeInfo['search_day'], 2, '0', STR_PAD_LEFT);
                    $this->_view->searchYMD = $ymd;
                    break;
                case 4:
                    $this->_view->deathMonth = $noticeInfo['death_month'];
                    break;
                case 5:
                    $this->_view->memorialYear  = $this->_yearList[$noticeInfo['memorial_year']];
                    $this->_view->memorialMonth = $this->_monthList[$noticeInfo['memorial_month']];
                    $this->_view->memorialEvent = $this->_memorialEvent[$noticeInfo['memorial_event']];
                    break;
            }
            $this->_view->targetCount    = count($noticeInfo['notice_target']);
            $this->_view->noticeSchedule = $noticeInfo['notice_schedule'];
            $this->_view->entryMethod    = $noticeInfo['entry_method'];
            $this->_view->noticeTitle    = $noticeInfo['notice_title'];
            $this->_view->noticeText     = $noticeInfo['notice_text'];
            $this->_view->template          = $this->getTemplateString($noticeInfo);
            $this->_view->templateId        = $noticeInfo['template_id'];
            $this->_view->imageExistenceFlg = $noticeInfo['image_existence_flg'];
            $this->_view->cacheKey          = $date->get("yyyyMMddHHmmss");
            $this->_view->url               = $noticeInfo['url'];
            //画像が設定してある場合、画像の幅高さを取得してviewに設定する
            if ($noticeInfo['image_existence_flg'] == IMAGE_EXISTENCE_FLG_YES) {
                $this->_view->imgWH = $this->getImageSizeAttr($this->_session->image_path);
            }

            // ワンタイムトークンを発行し、セッションとフォームに設定
            $this->_session->key = session_id() . '_' . microtime();
            $this->_view->token = comToken::get_token($this->_session->key);

            //確認画面を表示
            echo $this->_view->render($confpage_tpl);
        } else {
            //入力値に不正がある場合、メッセージ、入力値を設定して入力画面に戻る
            $this->dispEntryNoticeInfo($message, $noticeInfo);
            echo $this->_view->render($inputpage_tpl);
        }
    }

    //通知情報登録完了画面表示
    public function compentrynoticeinfoAction()
    {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //セッションから入力値を取得
        $noticeInfo = $this->_session->notice_info;

        // 押されたボタンを判定する
        if ($this->getRequest()->getPost('back')) {              //戻るボタンの場合
            // 利用申し込み画面表示
            $this->dispEntryNoticeInfo("", $noticeInfo);
            echo $this->_view->render('mng_notice_info_entry.tpl');
            return;
        } elseif ($this->getRequest()->getPost('entry')) {       //登録ボタンの場合
            // ワンタイムトークンが正しいかチェックする
            if (comToken::check_token($this->getRequest()->getPost('token'), $this->_session->key) === false) {
                // エラー画面を表示
                echo $this->_view->render('mng_error.tpl');
                exit();
            }

            // セッション内のワンタイムトークを削除する
            if (isset($this->_session->key) === true) {
                unset($this->_session->key);
            }

            //DBに通知情報を保存
            if ($this->_mngModel->insertNoticeInfo($noticeInfo)) {
                //画像を選択している場合、一時フォルダから正式なフォルダに移動する
                if ($noticeInfo['image_existence_flg'] == IMAGE_EXISTENCE_FLG_YES) {
                    //今追加した通知情報の通知情報Noを取得する
                    $noticeInfoNo = $this->_mngModel->getLastNoticeInfoNo();
                    //一時フォルダの仮アップ画像を画像フォルダに移動
                    $imagePath = NOTICE_IMG_PATH . $noticeInfoNo['notice_info_no'] . '.jpg';
                    if (rename($this->_session->image_path, $imagePath)) {
                        //移動できたらセッションの画像パスに移動先のパスを指定
                        $this->_session->image_path = $imagePath;
                    } else {
                        echo "ファイルを移動出来ませんでした。";
                    }
                }

                $this->compNoticeInfo($noticeInfo);

                //ログ出力
                $this->_logModel->recordLog(LOG_KIND_NOTICE_ENTRY, $this->_session->manager_id, "Success", $this->_httpHeaderInfo);
                //完了画面を表示
                echo $this->_view->render('mng_notice_info_entry_comp.tpl');
            } else {
                //ログ出力
                $this->_logModel->recordLog(LOG_KIND_NOTICE_ENTRY, $this->_session->manager_id, "Failure", $this->_httpHeaderInfo);
                //DB保存エラーの場合、エラー画面を表示
                echo $this->_view->render('mng_error.tpl');
            }
        }
    }

    //通知情報登録完了画面表示処理
    private function compNoticeInfo($noticeInfo)
    {
        //キャッシュ対策日時
        $date = new Zend_Date();
        //viewを設定
        $this->_view->search_category   = $noticeInfo['selected_category'];
        switch ($noticeInfo['selected_category']) {
            case 1:
                $this->_view->chargeName = $noticeInfo['charge_name'];
                break;
            case 2:
                $this->_view->hallName = $noticeInfo['hall_name'];
                break;
            case 3:
                $this->_view->searchName = $noticeInfo['search_name'].' 様';
                $ymd = '';
                if(strcmp($noticeInfo['search_year'], '') == 0) $ymd = '－/';
                else $ymd = str_pad($noticeInfo['search_year'], 4, '0', STR_PAD_LEFT).'/';
                if(strcmp($noticeInfo['search_month'], '') == 0) $ymd .= '－/';
                else $ymd .= str_pad($noticeInfo['search_month'], 2, '0', STR_PAD_LEFT).'/';
                if(strcmp($noticeInfo['search_day'], '') == 0) $ymd .= '－';
                else $ymd .= str_pad($noticeInfo['search_day'], 2, '0', STR_PAD_LEFT);
                $this->_view->searchYMD = $ymd;
                break;
            case 4:
                $this->_view->deathMonth = $noticeInfo['death_month'];
                break;
            case 5:
                $this->_view->memorialYear  = $this->_yearList[$noticeInfo['memorial_year']];
                $this->_view->memorialMonth = $this->_monthList[$noticeInfo['memorial_month']];
                $this->_view->memorialEvent = $this->_memorialEvent[$noticeInfo['memorial_event']];
                break;
        }
        $this->_view->targetCount    = count($noticeInfo['notice_target']);
        $this->_view->noticeSchedule = $noticeInfo['notice_schedule'];
        $this->_view->entryMethod    = $noticeInfo['entry_method'];
        $this->_view->noticeTitle    = $noticeInfo['notice_title'];
        $this->_view->template          = $this->getTemplateString($noticeInfo);
        $this->_view->templateId        = $noticeInfo['template_id'];
        $this->_view->noticeText        = $noticeInfo['notice_text'];
        $this->_view->imageExistenceFlg = $noticeInfo['image_existence_flg'];
        $this->_view->cacheKey          = $date->get("yyyyMMddHHmmss");
        $this->_view->url               = $noticeInfo['url'];
        //画像が設定してある場合、画像の幅高さを取得してviewに設定する
        if ($noticeInfo['image_existence_flg'] == IMAGE_EXISTENCE_FLG_YES) {
            $this->_view->imgWH = $this->getImageSizeAttr($this->_session->image_path);
        }
    }

    //通知情報表示画面表示
    public function dispnoticeinfoAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //GET値から通知Noを取得
        $noticeInfoNo = $this->getRequest()->getQuery('nino');
        //通知情報表示画面表示
        $this->dispNoticeInfo($noticeInfoNo);
    }

    /**
     * 通知情報表示画面表示処理
     * ：通知情報表示画面を表示する
     * @param int $noticeInfoNo 通知情報No
     */
    private function dispNoticeInfo($noticeInfoNo) {
        //通知Noを元にDBから通知情報を取得する
        $noticeInfo = $this->_mngModel->getNoticeInfo($noticeInfoNo);
        //sessionに通知情報を設定
        $this->_session->notice_info = $noticeInfo;
        if (empty($noticeInfo) === false) {
            //キャッシュ対策日時
            $date = new Zend_Date();

            //画像が設定してある場合、画像の幅高さを取得してviewに設定する
            if ($noticeInfo['image_existence_flg'] == IMAGE_EXISTENCE_FLG_YES) {
                $this->_view->imgWH = $this->getImageSizeAttr(NOTICE_IMG_PATH . $noticeInfoNo . '.jpg');
            }

            //viewを設定
            $this->_view->search_category = $noticeInfo['search_category'];
            switch ($noticeInfo['search_category']) {
                case 1:
                    $this->_view->chargeName = $noticeInfo['charge_name'];
                    break;
                case 2:
                    $this->_view->hallName = $noticeInfo['hall_name'];
                    break;
                case 3:
                    $this->_view->searchName = $noticeInfo['search_name'].' 様';
                    $ymd = '';
                    if(strcmp($noticeInfo['search_year'], '') == 0) $ymd = '－/';
                    else $ymd = str_pad($noticeInfo['search_year'], 4, '0', STR_PAD_LEFT).'/';
                    if(strcmp($noticeInfo['search_month'], '') == 0) $ymd .= '－/';
                    else $ymd .= str_pad($noticeInfo['search_month'], 2, '0', STR_PAD_LEFT).'/';
                    if(strcmp($noticeInfo['search_day'], '') == 0) $ymd .= '－';
                    else $ymd .= str_pad($noticeInfo['search_day'], 2, '0', STR_PAD_LEFT);
                    $this->_view->searchYMD = $ymd;
                    break;
                case 4:
                    $this->_view->deathMonth = $noticeInfo['death_month'];
                    break;
                case 5:
                    $this->_view->memorialYear  = $this->_yearList[$noticeInfo['memorial_year']];
                    $this->_view->memorialMonth = $this->_monthList[$noticeInfo['memorial_month']];
                    $this->_view->memorialEvent = $this->_memorialEvent[$noticeInfo['memorial_event']];
                    break;
            }
            $this->_view->targetCount       = count($noticeInfo['notice_target']);
            $this->_view->noticeInfoNo      = $noticeInfo['notice_info_no'];
            $this->_view->noticeSchedule    = $noticeInfo['notice_schedule'];
            $this->_view->entryMethod       = $noticeInfo['entry_method'];
            $this->_view->noticeTitle       = $noticeInfo['notice_title'];
            $this->_view->template          = $this->getTemplateString($noticeInfo);
            $this->_view->noticeText        = $noticeInfo['notice_text'];
            $this->_view->imageExistenceFlg = $noticeInfo['image_existence_flg'];
            $this->_view->cacheKey          = $date->get("yyyyMMddHHmmss");
            $this->_view->url               = $noticeInfo['url'];
            $this->_view->noticeFlg         = $noticeInfo['notice_flg'];
            $this->_view->page              = $this->_session->page;

            //表示画面を表示
            echo $this->_view->render('mng_notice_info_display.tpl');
        } else {
            //エラー画面表示
            echo $this->_view->render('mng_error.tpl');
        }
    }

    //通知情報表示
    public function viewnoticeinfoAction() {
        //GET値から取得
        $noticeInfoNo = $this->getRequest()->getQuery('nino');          //通知No
        $deceasedId   = $this->getRequest()->getQuery('deceased_id');   //故人ID
        $isPreview    = $this->getRequest()->getQuery('ispreview');     //プレビューフラグ

        //通知Noを元にDBから通知情報を取得する
        $noticeInfo = $this->_mngModel->getNoticeInfo($noticeInfoNo);
        //テンプレートの内容を取得する
        if(!empty($noticeInfo['template_id'])){
            $template = $this->_mngModel->getTemplate($noticeInfo['template_id']);
            $templateText = '';
            if(!is_null($deceasedId)){
                $deceasedInfo = $this->_mngModel->getDeceased($deceasedId);
                $name = $deceasedInfo['deceased_name'];
                $name = str_replace('　', ' ', $name);
                $templateText = $this->getTemplateString($noticeInfo, $name)."\n";
            }
            if($isPreview == 1){
                $templateText = $this->getTemplateString($noticeInfo, '[お名前]')."\n";
            }
        }else{
            $templateText = '';
        }

        if (empty($noticeInfo) === false) {
            //viewを設定
            $this->_view->noticeInfoNo = $noticeInfo['notice_info_no'];
            $this->_view->noticeTitle = $noticeInfo['notice_title'];
            $this->_view->noticeText = $templateText . $noticeInfo['notice_text'];
            $this->_view->imageExistenceFlg = $noticeInfo['image_existence_flg'];
            $this->_view->url = $noticeInfo['url'];

            //表示画面を表示
            echo $this->_view->render('mng_notice_view.tpl');
        } else {
            //エラー画面表示
            echo $this->_view->render('mng_notice_view_error.tpl');
        }
    }

    //通知情報編集画面表示
    public function dispeditnoticeinfoAction()
    {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //POST値から通知Noを取得
        $noticeInfoNo = $this->getRequest()->getPost('notice_info_no');
        //通知情報を取得
        $noticeInfo = $this->_mngModel->getNoticeInfo($noticeInfoNo);
        //セッションに画像のパスを設定
        $this->_session->image_path = NOTICE_IMG_PATH . $noticeInfoNo . '.jpg';

        //通知情報編集画面表示
        if(empty($noticeInfo) === false){
            $this->dispEntryNoticeInfo("", $noticeInfo);
            $this->_view->noticeInfoNo = $noticeInfoNo;
            echo $this->_view->render('mng_notice_info_edit.tpl');
        }else{
            echo $this->_view->render('mng_error.tpl');
        }
    }

    //通知情報編集確認画面表示
    public function confeditnoticeinfoAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        // 押されたボタンを判定する
        if ($this->getRequest()->getPost('back')) {             //戻るボタンの場合
            $this->dispNoticeInfo($this->getRequest()->getPost('notice_info_no'));
            return;
        }

        $this->_view->noticeInfoNo = $this->getRequest()->getPost('notice_info_no');
        $this->confNoticeInfo('mng_notice_info_edit.tpl', 'mng_notice_info_edit_conf.tpl');
    }

    //通知情報編集確認画面表示
    public function compeditnoticeinfoAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //セッションから入力値を取得
        $noticeInfo = $this->_session->notice_info;
        // 押されたボタンを判定する
        if ($this->getRequest()->getPost('back')) {              //戻るボタンの場合
            //通知情報編集画面表示
            $this->dispEntryNoticeInfo("", $noticeInfo);
            $this->_view->noticeInfoNo = $noticeInfo['notice_info_no'];
            echo $this->_view->render('mng_notice_info_edit.tpl');
        } elseif ($this->getRequest()->getPost('edit')) {        //編集ボタンの場合
            // ワンタイムトークンが正しいかチェックする
            if (comToken::check_token($this->getRequest()->getPost('token'), $this->_session->key) === false) {
                // エラー画面を表示
                echo $this->_view->render('mng_error.tpl');
                exit();
            }

            // セッション内のワンタイムトークを削除する
            if (isset($this->_session->key) === true) {
                unset($this->_session->key);
            }

            //DBに通知情報を保存
            if ($this->_mngModel->updateNoticeInfo($noticeInfo)) {
                $imagePath = NOTICE_IMG_PATH . $noticeInfo['notice_info_no'] . '.jpg';

                if ($noticeInfo['image_existence_flg'] == IMAGE_EXISTENCE_FLG_YES) {
                    //画像を選択している場合、一時フォルダから正式なフォルダに移動する
                    //一時フォルダの仮アップ画像を画像フォルダに移動
                    if (rename($this->_session->image_path, $imagePath)) {
                        //移動できたらセッションの画像パスに移動先のパスを指定
                        $this->_session->image_path = $imagePath;
                    } else {
                        echo "ファイルを移動出来ませんでした。";
                    }
                } else {
                    //画像を選択していない場合、ファイルが存在する場合、削除する
                    if (file_exists($imagePath)) {
                        //ファイルが存在する場合、削除する
                        unlink($imagePath);
                    }
                }

                $this->compNoticeInfo($noticeInfo);

                //お知らせ番号設定
                $this->_view->noticeInfoNo = $noticeInfo['notice_info_no'];
                //ログ出力
                $this->_logModel->recordLog(LOG_KIND_NOTICE_EDIT, $this->_session->manager_id, "Success", $this->_httpHeaderInfo);
                //完了画面を表示
                echo $this->_view->render('mng_notice_info_edit_comp.tpl');
            } else {
                //ログ出力
                $this->_logModel->recordLog(LOG_KIND_NOTICE_EDIT, $this->_session->manager_id, "Failure", $this->_httpHeaderInfo);
                //DB保存エラーの場合、エラー画面を表示
                echo $this->_view->render('mng_error.tpl');
            }
        }
    }

    //通知情報削除処理実行
    public function delnoticeinfoAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //POST値から通知Noを取得
        $noticeInfoNo = $this->getRequest()->getPost('notice_info_no');

        //通知情報を取得する
        $noticeInfo = $this->_mngModel->getNoticeInfo($noticeInfoNo);

        //画像が存在する場合、画像を削除
        if ($noticeInfo['image_existence_flg']  == IMAGE_EXISTENCE_FLG_YES) {
            //画像のパスを取得
            $imagePath = NOTICE_IMG_PATH . $noticeInfoNo . '.jpg';
            if (file_exists($imagePath)) {
                //ファイルが存在する場合、削除する
                unlink($imagePath);
            }
        }

        //削除処理実行
        if ($this->_mngModel->deleteNoticeInfo($noticeInfoNo)) {
            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_NOTICE_DELETE, $this->_session->manager_id, "Success", $this->_httpHeaderInfo);
            //一覧画面に遷移
            return $this->_forward('dispnoticeinfolist');
        } else {
            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_NOTICE_DELETE, $this->_session->manager_id, "Failure", $this->_httpHeaderInfo);
            //エラー画面表示
            echo $this->_view->render('mng_error.tpl');
        }
    }

    //アップロード画像表示
    public function readimageAction() {
        //GET値から通知Noを取得
        $noticeInfoNo = $this->getRequest()->getQuery('nino');

        if (empty($noticeInfoNo)) {
            //通知NoをGETで受け取らなかった場合は、セッションから画像パスを取得
            $imagePath = $this->_session->image_path;
        } else {
            //通知NoをGETで受け取った場合は、画像パスを生成する
            $imagePath = NOTICE_IMG_PATH . $noticeInfoNo . '.jpg';
        }

        if (file_exists($imagePath)) {
            $fp   = fopen($imagePath,'rb');
            $size = filesize($imagePath);
            $img  = fread($fp, $size);
            fclose($fp);

            header('Content-Type: image/jpeg');
            echo $img;
        }
    }

    /**
     * ログインチェック
     * ：ID、PWが正しいかチェックするメソッド
     * @param   string  $id     ID
     * @param   string  $pass   PW
     * @return  boolean TRUE:正しい   FALSE:正しくない
     */
    private function checkLogin($id, $password)
    {
        //ログインIDとPWをチェックする
        if (comValidate::chkNotEmpty($id) === false) {
            return false;
        }
        if (comValidate::chkNotEmpty($password) === false) {
            return false;
        }

        //引数のIDを条件に、管理者情報を取得する
        $manager = $this->_mngModel->getManager(comEncryption::encryption($id));

        //取得した管理者情報と引数のPWが等しいかチェックする
        if (!$manager || (comEncryption::decryption($manager['manager_password']) !== $password)) {
            return false;
        }

        //セッションにログイン状態を設定
        $this->_session->is_login = true;
        //セッションに管理者IDを設定
        $this->_session->manager_id = $manager['manager_id'];

        return true;
    }

    /**
     * 通知情報チェック
     * ：通知情報が正しいかチェックするメソッド
     * @param   array   通知情報
     * @return  string  正しい場合空文字、不正な場合エラーメッセージ
     */
    private function checkNoticeInfo(array $noticeInfo)
    {
        $message = "";          //エラーメッセージ格納用
        //通知予定日
        //入力されているか
        if (comValidate::chkNotEmpty($noticeInfo['notice_schedule']) === false) {
            $message = "・通知予定日が入力されていません。<br>";
        } elseif(comValidate::chkDate($noticeInfo['notice_schedule']) === false) {    //日付形式が正しいか
            $message = "・通知予定日はYYYY/MM/DD形式（例：2014/03/10）で入力してください。<br>";
        }

        //タイトル
        //入力されているか
        if (comValidate::chkNotEmpty($noticeInfo['notice_title']) === false) {
            $message = $message . "・タイトルが入力されていません。<br>";
        }

        //登録方法が入力の場合
        if ($noticeInfo['entry_method'] === 1) {
            //本文
            //入力されているか
            if (comValidate::chkNotEmpty($noticeInfo['notice_text']) === false) {
                $message = $message . "・本文が入力されていません。<br>";
            }

            //URL
            //URLが入力されている場合、入力形式が正しいか
            if (comValidate::chkNotEmpty($noticeInfo['url']) &&
                comValidate::chkUrl($noticeInfo['url']) === false) {
                $message = $message . "・URLの入力形式が正しくありません。<br>";
            }
        //登録方法がURLの場合
        } elseif ($noticeInfo['entry_method'] === 2) {
            //URL
            //入力されているか
            if (comValidate::chkNotEmpty($noticeInfo['url']) === false) {
                $message = $message . "・URLが入力されていません。<br>";
            } elseif (comValidate::chkUrl($noticeInfo['url']) === false) {  //URL形式が正しいか
                $message = $message . "・URLの入力形式が正しくありません。<br>";
            }
        }

        return $message;
    }

    /**
     * 通知情報画像チェック
     * ：通知情報の画像ファイルが正しいかチェックするメソッド
     * @return  string  正しい場合空文字、不正な場合エラーメッセージ
     */
    private function checkNoticeImage() {
        $message = "";          //エラーメッセージ格納用

        //不正なファイルでないか
        if (!isset($_FILES["notice_image"]["error"]) ||
            !is_int($_FILES["notice_image"]["error"])) {
            $message = $message . "・写真のファイルが不正です。<br>";
        }
        //$_FILES['notice_image']['error'] の値を確認
        switch ($_FILES["notice_image"]["error"]) {
            case UPLOAD_ERR_OK:         //OK
                break;
            case UPLOAD_ERR_NO_FILE:    //ファイル未選択
                break;
            case UPLOAD_ERR_INI_SIZE:   //php.ini定義の最大サイズ超過
            case UPLOAD_ERR_FORM_SIZE:  //フォーム定義の最大サイズ超過
                $message = $message . "・写真のファイルサイズが大きすぎます。<br>";
                break;
            default:
                $message = $message . "・予期せぬエラーが発生しました。<br>";
        }

        //選択されている場合、チェックする
        if ($_FILES["notice_image"]["error"] === UPLOAD_ERR_OK) {
            //プロジェクトで定義するサイズ上限(10MB)のオーバーチェック
            if ($_FILES["notice_image"]["size"] > MAX_IMAGE_FILE_SIZE) {
                $message = $message . "・写真のファイルサイズが大きすぎます。<br>";
            }
            //拡張子をチェックする
            if (comValidate::isJpeg($_FILES["notice_image"]["tmp_name"]) === false) {
                $message = $message . "・写真はJPEG形式のファイルを選択して下さい。<br>";
            }
        }

        return $message;
    }

    /**
     * 画像縦横サイズ取得
     * ：画像ファイルの高さと幅を取得して、width、height属性を返す
     * @param type $imagePath 画像のパス
     * @return string imgタグのwidth、height属性
     */
    private function getImageSizeAttr($imagePath) {
        $imageInfo = getimagesize($imagePath);
        if ($imageInfo) {
            $imageSizeAttr = 'width=' . '"' . $imageInfo[0] * 0.35 . '" ' . 'height=' . '"' . $imageInfo[1] * 0.35 . '"';
        } else {
            $imageSizeAttr = '';
        }
        return $imageSizeAttr;
    }

    //担当者一覧画面表示
    public function dispchargelistAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }
        $this->dispChargeList();
    }

    /**
     * 担当者一覧表示処理
     * ：担当者一覧を表示する
     * @param string $message エラーメッセージ
     */
    private function dispChargeList($message="") {
        //DBから担当者一覧を取得
        $chargeList = $this->_mngModel->getChargeList();

        //テンプレートに担当者一覧を設定
        $this->_view->chargeList = $chargeList;

        //テンプレートにメッセージを設定
        $this->_view->message = $message;

        //担当者一覧表示
        echo $this->_view->render('mng_charge_list.tpl');
    }

    //担当者追加
    public function addchargeAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //POST値から担当者名を取得
        $chargeName = $this->getRequest()->getPost('charge_name');

        //入力チェック
        if (comValidate::chkNotEmpty($chargeName) == false) {
            //担当者一覧表示
            $this->dispChargeList("担当者名が入力されていません");
            return;
        }

        //DBに保存
        //DBに通知情報を保存
        if ($this->_mngModel->addCharge($chargeName)) {
            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_CHARGE_ADD, $this->_session->manager_id, "Success", $this->_httpHeaderInfo);
            //担当者一覧表示
            $this->dispChargeList();
        } else {
            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_CHARGE_ADD, $this->_session->manager_id, "Failure", $this->_httpHeaderInfo);
            //DB保存エラーの場合、エラー画面を表示
            echo $this->_view->render('mng_error.tpl');
        }
    }

    //担当者削除
    public function delchargeAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }
        //GET値から担当者Noを取得
        $chargeNo = $this->getRequest()->getQuery('chargeno');
        //削除処理実行
        if ($this->_mngModel->deleteCharge($chargeNo)) {
            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_CHARGE_DELETE, $this->_session->manager_id, "Success", $this->_httpHeaderInfo);
            //担当者一覧表示
            $this->dispChargeList();
        } else {
            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_CHARGE_DELETE, $this->_session->manager_id, "Failure", $this->_httpHeaderInfo);
            //エラー画面表示
            echo $this->_view->render('mng_error.tpl');
        }
    }

    //パスワード変更画面表示
    public function disppasswordchangeAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }
        $this->dispPasswordChange();
    }

    /**
     * パスワード変更画面表示処理
     * ：パスワード変更画面を表示する
     * @param string $nowPassword   入力した現在のパスワード
     * @param string $newPassword   入力した新しいパスワード
     * @param string $confPassword  入力した新しいパスワード（確認用）
     * @param string $message       エラーメッセージ
     */
    private function dispPasswordChange($nowPassword="", $newPassword="", $confPassword="", $message="") {
        //テンプレートにメッセージを設定
        $this->_view->message = $message;
        //テンプレートに入力値を設定
        $this->_view->nowPassword = $nowPassword;
        $this->_view->newPassword = $newPassword;
        $this->_view->confPassword = $confPassword;
        //パスワード変更画面表示
        echo $this->_view->render('mng_password_change.tpl');
    }

    //パスワード変更確認画面
    public function confpasswordchangeAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //POSTから入力値を取得
        $nowPassword = $this->getRequest()->getPost('now_password');
        $newPassword = $this->getRequest()->getPost('new_password');
        $confPassword = $this->getRequest()->getPost('conf_password');

        //入力チェック
        $message = "";
        //現在のパスワードが正しいかチェック
        //セッションから管理者IDを取得
        $managerId = $this->_session->manager_id;
        //引数のIDを条件に、管理者情報を取得する
        $manager = $this->_mngModel->getManager($managerId);
        //取得した管理者情報と入力されたパスワードが等しいかチェックする
        if (!$manager || (comEncryption::decryption($manager['manager_password']) !== $nowPassword)) {
            $message = $message . "現在のパスワードが違います。<br>";
        }
        //新しいパスワードが半角英数字かチェック
        if (comValidate::chkNotEmpty($newPassword) === false) {
            $message = $message . "新しいパスワードが入力されていません。";
        } elseif (comValidate::chkAlnum($newPassword) === false) {    //新しいパスワードが半角英数字かチェック
            $message = $message . "新しいパスワードは半角英数字で設定して下さい。";
        } elseif (comValidate::chkStringLength($newPassword, 6, 10) === false) {
            $message = $message . "新しいパスワードは6-10文字で設定して下さい。";
        } elseif ($newPassword !== $confPassword) {         //新しいパスワードと新しいパスワード（確認）が等しいかチェック
            $message = $message . "新しいパスワードと新しいパスワード（確認）の値が違います。";
        }
        if (empty($message)) {
            //正しい場合確認画面を表示
            //セッションに新しいパスワードを設定
            $this->_session->new_password = $newPassword;
            // ワンタイムトークンを発行し、セッションとフォームに設定
            $this->_session->key = session_id() . '_' . microtime();
            $this->_view->token = comToken::get_token($this->_session->key);
            //パスワード変更確認画面表示
            echo $this->_view->render('mng_password_change_conf.tpl');
        } else {
            //不正な場合入力画面の戻る
            $this->dispPasswordChange($nowPassword, $newPassword, $confPassword, $message);
        }
    }

    //パスワード変更完了画面
    public function comppasswordchangeAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //セッションから新しいパスワードを取得
        $newPassword = $this->_session->new_password;

        // 押されたボタンを判定する
        if ($this->getRequest()->getPost('cancel')) {           //キャンセルボタンの場合
            // パスワード変更画面に戻る
            $this->dispPasswordChange();
        } elseif ($this->getRequest()->getPost('change')) {     //変更するボタンの場合
            // ワンタイムトークンが正しいかチェックする
            if (comToken::check_token($this->getRequest()->getPost('token'), $this->_session->key) === false) {
                // エラー画面を表示
                echo $this->_view->render('mng_error.tpl');
                exit();
            }

            // セッション内のワンタイムトークを削除する
            if (isset($this->_session->key) === true) {
                unset($this->_session->key);
            }

            //DBのパスワードを変更
            if ($this->_mngModel->updatePassword($this->_session->manager_id, comEncryption::encryption($newPassword))) {
                //自動ログインを設定している場合、Cookieのパスワードを変更
                $cookiePassword = comEncryption::decryption(filter_input(INPUT_COOKIE, COOKIE_PASSWORD));
                if (empty($cookiePassword) === false) {
                    setcookie(COOKIE_PASSWORD, comEncryption::encryption($newPassword), time() + COOKIE_EXPIRATION, '/mng/', $this->_config->domain->url);
                }
                //ログ出力
                $this->_logModel->recordLog(LOG_KIND_PASSWORD_CHANGE, $this->_session->manager_id, "Success", $this->_httpHeaderInfo);
                //完了画面を表示
                echo $this->_view->render('mng_password_change_comp.tpl');
            } else {
                //ログ出力
                $this->_logModel->recordLog(LOG_KIND_PASSWORD_CHANGE, $this->_session->manager_id, "Failure", $this->_httpHeaderInfo);
                //DB保存エラーの場合、エラー画面を表示
                echo $this->_view->render('mng_error.tpl');
            }
        }
    }

    //パスワード問い合わせ画面表示
    public function dispinquirypasswordAction() {
        $this->dispInquiryPassword();
    }

    /**
     * パスワード問い合わせ画面表示処理
     * ：パスワード問い合わせ画面を表示する
     * @param string $id        管理者ID
     * @param string $message   エラーメッセージ
     */
    private function dispInquiryPassword($id="", $message="") {
        //メッセージを設定
        $this->_view->message = $message;
        //idを設定
        $this->_view->id = $id;
        // ワンタイムトークンを発行し、セッションとフォームに設定
        $this->_session->key = session_id() . '_' . microtime();
        $this->_view->token = comToken::get_token($this->_session->key);
        echo $this->_view->render('mng_password_inquiry.tpl');
    }

    //パスワード問い合わせ完了
    public function compinquirypasswordAction() {
        //POSTから入力値を取得
        $id = $this->getRequest()->getPost('id');

        // ワンタイムトークンが正しいかチェックする
        if (comToken::check_token($this->getRequest()->getPost('token'), $this->_session->key) === false) {
            // エラー画面を表示
            echo $this->_view->render('mng_error.tpl');
            exit();
        }

        // セッション内のワンタイムトークを削除する
        if (isset($this->_session->key) === true) {
            unset($this->_session->key);
        }

        //IDが存在するかチェック
        //引数のIDを条件に、管理者情報を取得する
        $manager = $this->_mngModel->getManager(comEncryption::encryption($id));
        if ($manager) {
            //IDが存在する場合、登録されているメールアドレス宛にパスワードを案内する

            // メールに表示する入力値を設定する
            $this->_view->toName = $manager['manager_name'];
            $this->_view->password = comEncryption::decryption($manager['manager_password']);

            // メール文を取得
            $body = $this->_view->render('mail_password_information.tpl');

            // 送信メールの内容を取得
            $mailInfo = array(
                'username' => $this->_config->inquiry_mail->inquiry_mail,
                'password' => $this->_config->inquiry_mail->inquiry_password,
                'fromName' => $this->_config->inquiry_mail->inquiry_name,
                'fromMail' => $this->_config->inquiry_mail->inquiry_mail,
                'toName' => $manager['manager_name'] . '様',
                'toMail' => $manager['manager_mail'],
                'subject' => '法要アプリ管理システムパスワードのご案内',
                'body' => $body
            );

            // メール送信
            comMail::sendMail($mailInfo);

            $this->_view->mail = $manager['manager_mail'];

            //メール送信完了画面表示
            echo $this->_view->render('mng_password_inquiry_comp.tpl');
        } else {
            //IDが存在しない場合、前の画面に戻る
            $this->dispInquiryPassword($id, "IDが不正です");
        }
    }

    //故人様一覧画面表示
    public function dispdeceasedlistAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        // ページング処理
        // ページ番号のリクエストがあれば取得（無ければ1ページ目とする）
        $pagenum = $this->getRequest()->getParam('page', 1);
        //セッションの検索条件とページ番号を初期化
        $this->_session->search_deceased_list_from = "";
        $this->_session->search_deceased_list_to = "";
        $this->_session->search_deceased_name = "";
        $this->_session->search_person_in_charge = "";
        $this->_session->page_deceased_list = 1;

        $this->dispDeceasedList("", "","", "", 1);
    }

    //故人様一覧画面定期更新
    public function reloaddeceasedlistAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            echo '<p class="message">ページの有効期限が切れた為、ログアウトしました。<br>お手数ですがもう一度ログインしてください。</p>';
            exit();
        }

        //検索条件と現在のページをセッションから取得
        $searchFrom = $this->_session->search_deceased_list_from;
        $searchTo = $this->_session->search_deceased_list_to;
        $searchDeceasedName = $this->_session->search_deceased_name;
        $searchPersonInCharge = $this->_session->search_person_in_charge;
        $pagenum = $this->_session->page_deceased_list;

        //DBから故人情報を取得
        $deceasedInfoList = $this->_mngModel->getDeceasedList($searchFrom, $searchTo,$searchDeceasedName,$searchPersonInCharge);

        //ページング処理
        //ページネーターを取得
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($deceasedInfoList));
        //現在ページを設定
        $paginator->setCurrentPageNumber($pagenum);
        //1ページあたりの表示件数を設定
        $paginator->setItemCountPerPage(DECEASED_LIST_NUMBER);

        //テンプレートにページネーター（故人様一覧）を設定
        $this->_view->deceasedInfoList = $paginator;
        //ページ情報を設定
        $pager = $paginator->getPages();
        $this->_view->total = $pager->totalItemCount;                // 全データ数
        $this->_view->all = $pager->pageCount;                       // 全ページ数
        $this->_view->now = $pager->current;                         // 現ページ数
        $this->_view->firstItemNumber = $pager->firstItemNumber;     // 現ページの最初の項目数
        $this->_view->lastItemNumber = $pager->lastItemNumber;       // 現ページの最後の項目数
        $this->_view->pagesInRange = $pager->pagesInRange;           // ページの配列

        echo $this->_view->render('mng_deceased_list_reload.tpl');
    }

    //故人様一覧検索表示
    public function dispdeceasedsearchAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //Get値から検索条件を取得
        $searchFrom = $this->getRequest()->getParam('search_from', "");
        $searchTo = $this->getRequest()->getParam('search_to', "");
        $searchDeceasedName = $this->getRequest()->getParam('search_deceased_name', "");
        $searchPersonInCharge = $this->getRequest()->getParam('search_deceased_personincharge', "");

        // 押されたボタンがクリアボタンだった場合、検索条件をクリアする
        if ($this->getRequest()->getParam('clear')) {                //クリアボタンの場合
            $searchFrom = "";
            $searchTo = "";
            $searchDeceasedName = "";
            $searchPersonInCharge = "";
        }

        //セッションに検索条件とページを設定
        $this->_session->search_deceased_list_from = $searchFrom;
        $this->_session->search_deceased_list_to = $searchTo;
        $this->_session->search_deceased_name = $searchDeceasedName;
        $this->_session->search_person_in_charge = $searchPersonInCharge ;
        $this->_session->page_deceased_list = 1;

        $this->dispDeceasedList($searchFrom, $searchTo, $searchDeceasedName,$searchPersonInCharge,1);
    }

    //故人様一覧画面戻る表示
    public function dispdeceasedreturnAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //セッションから検索条件とページを取得
        $searchFrom = $this->_session->search_deceased_list_from;
        $searchTo = $this->_session->search_deceased_list_to;
        $searchDeceasedName = $this->_session->search_deceased_name;
        $searchPersonInCharge = $this->_session->search_person_in_charge;
        $page = $this->_session->page_deceased_list;

        $this->dispDeceasedList($searchFrom, $searchTo,$searchDeceasedName,$searchPersonInCharge, $page);
    }

    //故人様一覧画面ページ指定表示
    public function dispdeceasedpagingAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //Get値からページ番号を取得
        $page = $this->getRequest()->getParam('page', 1);

        //セッションから検索条件を取得
        $searchFrom = $this->_session->search_deceased_list_from;
        $searchTo = $this->_session->search_deceased_list_to;
        $searchDeceasedName = $this->_session->search_deceased_name;
        $searchPersonInCharge = $this->_session->search_person_in_charge;

        //セッションにページを設定
        $this->_session->page_deceased_list = $page;

        $this->dispDeceasedList($searchFrom, $searchTo,$searchDeceasedName,$searchPersonInCharge, $page);
    }

    /**
     * 故人様一覧画面表示処理
     * ：故人様一覧画面を表示する
     * @param string $searchFrom    検索条件発注日From
     * @param string $searchTo      検索条件発注日To
     * @param string $pagenum       表示するページ
     */
    private function dispDeceasedList($searchFrom, $searchTo, $searchDeceasedName,$searchPersonInCharge,$pagenum) {
        //検索条件を画面に設定
        $this->_view->searchFrom = $searchFrom;
        $this->_view->searchTo = $searchTo;
        $this->_view->searchDeceasedName = $searchDeceasedName;
        $this->_view->searchPersonInCharge = $searchPersonInCharge;

        //DBから故人情報を取得
        $deceasedInfoList = $this->_mngModel->getDeceasedList($searchFrom, $searchTo,$searchDeceasedName,$searchPersonInCharge);

        //ページング処理
        //ページネーターを取得
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($deceasedInfoList));
        //現在ページを設定
        $paginator->setCurrentPageNumber($pagenum);
        //1ページあたりの表示件数を設定
        $paginator->setItemCountPerPage(DECEASED_LIST_NUMBER);

        //テンプレートにページネーター（故人様一覧）を設定
        $this->_view->deceasedInfoList = $paginator;
        //ページ情報を設定
        $pager = $paginator->getPages();
        $this->_view->total = $pager->totalItemCount;                // 全データ数
        $this->_view->all = $pager->pageCount;                       // 全ページ数
        $this->_view->now = $pager->current;                         // 現ページ数
        $this->_view->firstItemNumber = $pager->firstItemNumber;     // 現ページの最初の項目数
        $this->_view->lastItemNumber = $pager->lastItemNumber;       // 現ページの最後の項目数
        $this->_view->pagesInRange = $pager->pagesInRange;           // ページの配列

        echo $this->_view->render('mng_deceased_list.tpl');
    }

    //故人様QR発注フォーム画面表示
    public function dispdeceasedqrorderAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }
        $deceasedInfo = array(
            'charge_name' => "",
            'souke' => "",
            'deceased_last_name' => "",
            'deceased_first_name' => "",
            'deceased_birthday_y' => "",
            'deceased_birthday_m' => "",
            'deceased_birthday_d' => "",
            'deceased_deathday_y' => "",
            'deceased_deathday_m' => "",
            'deceased_deathday_d' => "",
            'kyonen_gyonen_flg' => 1,
            'death_age' => "",
            'allow_push' => 1,
            'hall_name' => "",
            'image_existence_flg' => "",
            'cachekey' => "");
        $this->dispDeceasedQrOrder($deceasedInfo);
    }

    /**
     * 故人様QR発注フォーム画面表示処理
     * ：故人様QR発注フォームを表示する
     * @param array $deceasedInfo   入力した故人様情報
     * @param string $message       エラーメッセージ
     */
    private function dispDeceasedQrOrder(array $deceasedInfo, $message = "") {
        //ビューに値を設定する
        $this->_view->message = $message;
        $this->_view->souke = $deceasedInfo['souke'];
        $this->_view->deceasedLastName = $deceasedInfo['deceased_last_name'];
        $this->_view->deceasedFirstName = $deceasedInfo['deceased_first_name'];
        $this->_view->deceasedBirthdayY = $deceasedInfo['deceased_birthday_y'];
        $this->_view->deceasedBirthdayM = $deceasedInfo['deceased_birthday_m'];
        $this->_view->deceasedBirthdayD = $deceasedInfo['deceased_birthday_d'];
        $this->_view->deceasedDeathdayY = $deceasedInfo['deceased_deathday_y'];
        $this->_view->deceasedDeathdayM = $deceasedInfo['deceased_deathday_m'];
        $this->_view->deceasedDeathdayD = $deceasedInfo['deceased_deathday_d'];
        $this->_view->checked1 = "";
        $this->_view->checked2 = "";
        $this->_view->checked3 = "";
        $this->_view->checked4 = "";
        $this->_view->checked5 = "";
        $this->_view->checked6 = "";
        switch ($deceasedInfo['kyonen_gyonen_flg']) {
            case 0:
                $this->_view->checked4 = "checked";
                break;
            case 1:
                $this->_view->checked1 = "checked";
                break;
            case 2:
                $this->_view->checked2 = "checked";
                break;
            case 3:
                $this->_view->checked3 = "checked";
                break;
        }
        $this->_view->deathAge = $deceasedInfo['death_age'];
        switch ($deceasedInfo['allow_push']) {
            case 1:
                $this->_view->checked5 = "checked";
                break;
            case 2:
                $this->_view->checked6 = "checked";
                break;
        }
        $this->_view->hallName = $deceasedInfo['hall_name'];
        $this->_view->imageExistenceFlg = $deceasedInfo['image_existence_flg'];
        $date = new Zend_Date();    //キャッシュ対策日時
        $this->_view->cacheKey = $date->get("yyyyMMddHHmmss");

        //担当者一覧を取得してビューに設定する
        $chargeList = $this->makeSelectboxSource($this->_mngModel->getChargeList(), 'charge_name');
        $this->_view->chargeList = $chargeList;
        $this->_view->chargeSelected = $deceasedInfo['charge_name'];

        echo $this->_view->render('mng_deceased_qr_order.tpl');
    }

    //故人様仮アップロード画像表示
    public function readdeceasedtempimageAction() {
        //セッションから画像パスを取得
        $imagePath = $this->_session->deceased_temp_data_path;

        if (file_exists($imagePath)) {
            $fp   = fopen($imagePath,'rb');
            $size = filesize($imagePath);
            $img  = fread($fp, $size);
            fclose($fp);

            header('Content-Type: image/jpeg');
            echo $img;
        }
    }

    //故人様アップロード画像表示
    public function readdeceasedimageAction() {
        //GET値から故人IDを取得
        $deceasedId = $this->getRequest()->getQuery('did');

        //故人IDをGETで受け取った場合は、画像パスを生成する
        $deceased = $this->_mngModel->getDeceased($deceasedId);
        $imagePath = DECEASED_DATA_PATH . substr($deceased["issue_datetime"], 0, 4) .
                '/' . $deceasedId . '/' . $deceasedId . '.jpg';

        if (file_exists($imagePath)) {
            $fp   = fopen($imagePath,'rb');
            $size = filesize($imagePath);
            $img  = fread($fp, $size);
            fclose($fp);

            header('Content-Type: image/jpeg');
            echo $img;
        }
    }

    //QR発注フォーム確認画面表示
    public function confdeceasedqrorderAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //戻るボタンが押された場合
        if ($this->getRequest()->getPost('back')) {
            //故人様一覧画面を表示
            return $this->dispdeceasedlistAction();
        }

        //入力値を取得
        $deceasedInfo = array(
            'charge_name' => $this->getRequest()->getPost('charge_name'),
            'souke' => $this->getRequest()->getPost('souke'),
            'deceased_last_name' => common::delSpace($this->getRequest()->getPost('deceased_last_name')),
            'deceased_first_name' => common::delSpace($this->getRequest()->getPost('deceased_first_name')),
            'deceased_birthday_y' => $this->getRequest()->getPost('deceased_birthday_y'),
            'deceased_birthday_m' => $this->getRequest()->getPost('deceased_birthday_m'),
            'deceased_birthday_d' => $this->getRequest()->getPost('deceased_birthday_d'),
            'deceased_deathday_y' => $this->getRequest()->getPost('deceased_deathday_y'),
            'deceased_deathday_m' => $this->getRequest()->getPost('deceased_deathday_m'),
            'deceased_deathday_d' => $this->getRequest()->getPost('deceased_deathday_d'),
            'kyonen_gyonen_flg' => $this->getRequest()->getPost('kyonen_gyonen_flg'),
            'death_age' => $this->getRequest()->getPost('death_age'),
            'allow_push' => $this->getRequest()->getPost('allow_push'),
            'hall_name' => $this->getRequest()->getPost('hall_name'),
            'image_existence_flg' => $this->getRequest()->getPost('image_existence_flg'));

        //画像を保存
        //画像が選択されている場合、一時フォルダに保存
        if (is_uploaded_file($_FILES["deceased_image"]["tmp_name"])) {
            //選択されている場合、フラグを1にする
            $deceasedInfo['image_existence_flg'] = IMAGE_EXISTENCE_FLG_YES;
            //一時保存用ファイル名を生成し、既にファイルが存在しないか重複チェック
            do {
                //一時保存用ファイル名を生成
                $fileName = comEncryption::getRandomString() . ".jpg";
                $uploadFile = DECEASED_DATA_TEMP_PATH . $fileName;
            } while(file_exists($uploadFile));
            //一時フォルダに保存
            //800×800に収まるように画像を作成
            //サーバ版
            exec('/usr/bin/convert -define jpeg:size=1200x1200 -resize 1200x1200 ' . $_FILES['deceased_image']['tmp_name'] . ' ' .  $uploadFile);
            //パーミッションを変更
            chmod($uploadFile, 0644);

            //ファイルパスをセッションに設定
            $this->_session->deceased_temp_data_path = $uploadFile;
        }

        //入力チェック
        $message = $this->checkDeceasedInfo($deceasedInfo);
        $message = $message . $this->checkDeceasedImage();
        if (comValidate::chkNotEmpty($message) === false) {
            //入力値をセッションに設定
            $this->_session->deceased_info = $deceasedInfo;

            //viewを設定
            $this->_view->chargeName = $deceasedInfo['charge_name'];
            $this->_view->souke = $deceasedInfo['souke'];
            $this->_view->deceasedLastName = $deceasedInfo['deceased_last_name'];
            $this->_view->deceasedFirstName = $deceasedInfo['deceased_first_name'];
            $deceasedBirthday = $deceasedInfo['deceased_birthday_y'] . "/" .
                    sprintf("%02d", $deceasedInfo['deceased_birthday_m']) . "/" .
                    sprintf("%02d", $deceasedInfo['deceased_birthday_d']);
            $this->_view->deceasedBirthday = $deceasedBirthday;
            $deceasedDeathday = $deceasedInfo['deceased_deathday_y'] . "/" .
                    sprintf("%02d", $deceasedInfo['deceased_deathday_m']) . "/" .
                    sprintf("%02d", $deceasedInfo['deceased_deathday_d']);
            $this->_view->deceasedDeathday = $deceasedDeathday;
            switch ($deceasedInfo['kyonen_gyonen_flg']) {
                case 0:
                    $this->_view->kyonenGyonen = "－";
                    break;
                case 1:
                    $this->_view->kyonenGyonen = "享年";
                    break;
                case 2:
                    $this->_view->kyonenGyonen = "行年";
                    break;
                case 3:
                    $this->_view->kyonenGyonen = "満";
                    break;
            }
            $this->_view->deathAge = $deceasedInfo['death_age'];
            switch ($deceasedInfo['allow_push']) {
                case 1:
                    $this->_view->allowPush = "通知する";
                    break;
                case 2:
                    $this->_view->allowPush = "通知しない";
                    break;
            }
            if (strcmp($deceasedInfo['hall_name'], '') != 0) {
                $this->_view->hallName = $deceasedInfo['hall_name'];
            } else {
                $this->_view->hallName = "－";
            }
            $this->_view->imageExistenceFlg = $deceasedInfo['image_existence_flg'];
            $date = new Zend_Date();    //キャッシュ対策日時
            $this->_view->cacheKey = $date->get("yyyyMMddHHmmss");
            // ワンタイムトークンを発行し、セッションとフォームに設定
            $this->_session->key = session_id() . '_' . microtime();
            $this->_view->token = comToken::get_token($this->_session->key);

            //入力値が正しい場合、確認画面を表示
            echo $this->_view->render('mng_deceased_qr_order_conf.tpl');
        } else {
            //入力値が不正な場合、入力画面に戻る
            $this->dispDeceasedQrOrder($deceasedInfo, $message);
        }
    }

    //QR発注キャンセル
    public function canceldeceasedqrorderAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }
        //GET値から故人IDを取得
        $deceasedId = $this->getRequest()->getQuery('did');

        //故人情報フォルダのパスを取得
        $deceased = $this->_mngModel->getDeceased($deceasedId);
        $deceasedDataPath = DECEASED_DATA_PATH .
                substr($deceased["issue_datetime"], 0, 4) . '/' . $deceasedId;

        //DBからデータを削除
        //削除処理実行
        if ($this->_mngModel->deleteDeceased($deceasedId)) {
            if (file_exists($deceasedDataPath)) {
                //ファイルが存在する場合、削除する
                common::removeDirectory($deceasedDataPath);
            }
            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_QR_CANCEL, $this->_session->manager_id, "Success", $this->_httpHeaderInfo);
            //故人一覧表示
            return $this->_forward('dispdeceasedreturn');
        } else {
            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_QR_CANCEL, $this->_session->manager_id, "Failure", $this->_httpHeaderInfo);
            //エラー画面表示
            echo $this->_view->render('mng_error.tpl');
        }
    }

    //法要アプリのご案内ダウンロード
    public function downloadqrpdfAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }
        //GET値から故人IDを取得
        $deceasedId = $this->getRequest()->getQuery('did');

        //故人情報を取得
        $deceasedInfo = $this->_mngModel->getDeceased($deceasedId);

        if (empty($deceasedInfo) === false) {
            if ($deceasedInfo['issue_state_code'] == ISSUE_STATE_CODE_COMP) {
                //ファイル名を取得
                $fileName = sprintf(PDF_FILE_NAME, str_replace("　", "", $deceasedInfo['deceased_name']));

                //ファイルのパスを取得
                $fileDir = DECEASED_DATA_PATH . substr($deceasedInfo["issue_datetime"], 0, 4) .
                        '/' . $deceasedId;
                $pdfPath = $fileDir . '/' . $fileName;
                $pdfPath = mb_convert_encoding($pdfPath, 'Shift_JIS', 'UTF-8');

                //ファイルの存在チェック
                if (!file_exists($pdfPath)) {
                    //ファイルが無い場合、法要アプリのご案内を発行する
                    comGuidanceIssue::issue($deceasedInfo, $fileDir);
                }

                //オープンできるかチェック
                if (!($fp = fopen($pdfPath, "r"))) {
                    //ログ出力
                    $this->_logModel->recordLog(LOG_KIND_QR_PDF_DOWNLOAD, $this->_session->manager_id, "Failure", $this->_httpHeaderInfo);
                    //エラー画面表示
                    echo $this->_view->render('mng_error.tpl');
                    exit();
                }
                fclose($fp);

                //ファイルサイズの確認
                if (($content_length = filesize($pdfPath)) == 0) {
                    //ログ出力
                    $this->_logModel->recordLog(LOG_KIND_QR_PDF_DOWNLOAD, $this->_session->manager_id, "Failure", $this->_httpHeaderInfo);
                    //エラー画面表示
                    echo $this->_view->render('mng_error.tpl');
                    exit();
                }

                //ダウンロード用のHTTPヘッダ送信
                header("Content-type: application/pdf");
                // IE8用にキャッシュコントロールをpublicに設定（nocashだとhttpsの場合、ダウンロードできない為）
                header("Cache-Control: public");
                header("Pragma:");
                header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($fileName));
                header("Content-Length: ".$content_length);

                //ファイルを読んで出力
                if (!readfile($pdfPath)) {
                    //ログ出力
                    $this->_logModel->recordLog(LOG_KIND_QR_PDF_DOWNLOAD, $this->_session->manager_id, "Failure", $this->_httpHeaderInfo);
                    //エラー画面表示
                    echo $this->_view->render('mng_error.tpl');
                    exit();
                }
            } else {
                //エラー画面表示
                echo $this->_view->render('mng_error.tpl');
            }
        } else {
            //エラー画面表示
            echo $this->_view->render('mng_error.tpl');
        }
    }

    //故人様表示
    public function dispdeceasedinfoAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //GET値から故人IDを取得
        $deceasedId = $this->getRequest()->getQuery('did');

        //故人様表示画面表示
        if($this->dispDeceasedInfo($deceasedId)){
            echo $this->_view->render('mng_deceased_info_display.tpl');
        }else{
            echo $this->_view->render('mng_error.tpl');
        }
    }

    //故人様ポップアップ表示
    public function dispdeceasedinfopopupAction() {
        //GET値から故人IDを取得
        $deceasedId = $this->getRequest()->getQuery('did');

        //故人様表示画面表示
        if($this->dispDeceasedInfo($deceasedId)){
            echo $this->_view->render('mng_deceased_info_popup.tpl');
        }else{
            echo $this->_view->render('mng_error_popup.tpl');
        }
    }

    public function dispdeceasedlistpopupAction() {
        //選択済みのID配列をどっかから調達
        $noticeInfo = $this->_session->notice_info;
        //画面を表示
        $list = $this->_mngModel->getDeceasedByIdList($noticeInfo['notice_target']);
        $this->_view->deceasedInfoList = $list;
        echo $this->_view->render('mng_deceased_list_popup.tpl');
    }

    private function dispDeceasedInfo($deceasedId) {
        //故人情報を取得
        $deceasedInfo = $this->_mngModel->getDeceased($deceasedId);

        if (empty($deceasedInfo) === false) {
            if ($deceasedInfo['issue_state_code'] == ISSUE_STATE_CODE_SUBMIT ||
                $deceasedInfo['issue_state_code'] == ISSUE_STATE_CODE_COMP) {
                //viewを設定
                $this->_view->issueStateCode   = $deceasedInfo['issue_state_code'];
                $this->_view->issueDatetime    = $deceasedInfo['issue_datetime'];
                $this->_view->entryDatetime    = $deceasedInfo['entry_datetime'];
                $this->_view->chargeName       = $deceasedInfo['charge_name'];
                $this->_view->souke            = $deceasedInfo['souke'];
                $this->_view->deceasedId       = $deceasedInfo['deceased_id'];
                $this->_view->deceasedName     = $deceasedInfo['deceased_name'];
                $this->_view->deceasedBirthday = $deceasedInfo['deceased_birthday'];
                $this->_view->deceasedDeathday = $deceasedInfo['deceased_deathday'];
                switch ($deceasedInfo['kyonen_gyonen_flg']) {
                        case 0:
                            $this->_view->kyonenGyonen = "－";
                            break;
                        case 1:
                            $this->_view->kyonenGyonen = "享年";
                            break;
                        case 2:
                            $this->_view->kyonenGyonen = "行年";
                            break;
                        case 3:
                            $this->_view->kyonenGyonen = "満";
                            break;
                }
                $this->_view->deathAge          = $deceasedInfo['death_age'];
                switch ($deceasedInfo['allow_push']) {
                    case 1:
                        $this->_view->allowPush = "通知する";
                        break;
                    case 2:
                        $this->_view->allowPush = "通知しない";
                        break;
                }
                $this->_view->hallName          = $deceasedInfo['hall_name'];
                $this->_view->imageExistenceFlg = $deceasedInfo['deceased_photo_path'];
                $date = new Zend_Date();    //キャッシュ対策日時
                $this->_view->cacheKey       = $date->get("yyyyMMddHHmmss");
                $this->_view->issueStateCode = $deceasedInfo['issue_state_code'];

                //画面を表示
                // echo $this->_view->render('mng_deceased_info_display.tpl');
            } else {
                //エラー画面表示
                // echo $this->_view->render('mng_error.tpl');
                return false;
            }
        } else {
            //エラー画面表示
            // echo $this->_view->render('mng_error.tpl');
            return false;
        }

        return true;
    }

    //故人様削除
    public function deldeceasedinfoAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }
        //POST値から故人IDを取得
        $deceasedId = $this->getRequest()->getPost('deceased_id');
        //故人情報を論理削除する
        if ($this->_mngModel->logicDeleteDeceased($deceasedId)) {
            //故人情報フォルダのパスを取得
            $deceased = $this->_mngModel->getDeceased($deceasedId);
            $deceasedDataPath = DECEASED_DATA_PATH .
                    substr($deceased["issue_datetime"], 0, 4) . '/' . $deceasedId;
            if (file_exists($deceasedDataPath)) {
                //ファイルが存在する場合、削除する
                common::removeDirectory($deceasedDataPath);
            }
            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_DECEASED_LOGIC_DELETE, $this->_session->manager_id, "Success", $this->_httpHeaderInfo);
            //故人様一覧に戻る
            return $this->_forward('dispdeceasedreturn');
        } else {
            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_DECEASED_LOGIC_DELETE, $this->_session->manager_id, "Failure", $this->_httpHeaderInfo);
            //エラー画面表示
            echo $this->_view->render('mng_error.tpl');
            exit();
        }
    }

    //故人情報編集画面表示
    public function dispeditdeceasedinfoAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //POST値から故人IDを取得
        $deceasedId = $this->getRequest()->getPost('deceased_id');

        //故人情報を取得
        $deceasedInfo = $this->_mngModel->getDeceased($deceasedId);
        //編集用に名前を苗字と下の名前に分けて配列に設定
        $arrayDeceasedName = common::splitDeceasedName($deceasedInfo['deceased_name']);
        $deceasedInfo['deceased_last_name'] = $arrayDeceasedName[0];
        $deceasedInfo['deceased_first_name'] = $arrayDeceasedName[1];
        //編集用に誕生日を年月日に分けて配列に設定
        $deceasedInfo['deceased_birthday_y'] = substr($deceasedInfo['deceased_birthday'], 0, 4);
        $deceasedInfo['deceased_birthday_m'] = substr($deceasedInfo['deceased_birthday'], 4, 2);
        $deceasedInfo['deceased_birthday_d'] = substr($deceasedInfo['deceased_birthday'], 6, 2);
        //編集用に没年月日を年月日に分けて配列に設定
        $deceasedInfo['deceased_deathday_y'] = substr($deceasedInfo['deceased_deathday'], 0, 4);
        $deceasedInfo['deceased_deathday_m'] = substr($deceasedInfo['deceased_deathday'], 4, 2);
        $deceasedInfo['deceased_deathday_d'] = substr($deceasedInfo['deceased_deathday'], 6, 2);
        //写真選択フラグにDBに保存されている初期値を配列に設定
        $deceasedInfo['image_existence_flg'] = $deceasedInfo['deceased_photo_path'];

        //写真があるデータの場合編集用に写真を一時フォルダにコピーする
        if ($deceasedInfo['image_existence_flg'] == "1") {
            //一時保存用ファイル名を生成し、既にファイルが存在しないか重複チェック
            do {
                //一時保存用ファイル名を生成
                $fileName = comEncryption::getRandomString() . ".jpg";
                $tempFile = DECEASED_DATA_TEMP_PATH . $fileName;
            } while(file_exists($tempFile));
            $photoPath = DECEASED_DATA_PATH . substr($deceasedInfo["issue_datetime"], 0, 4) .
                    "/" . $deceasedInfo['deceased_id'] . "/" . $deceasedInfo['deceased_id'] . ".jpg";
            if (copy($photoPath, $tempFile)) {
                //セッションに写真のパスを設定
                $this->_session->deceased_temp_data_path = $tempFile;
            } else {
                echo "ファイルをコピー出来ませんでした。";
                exit();
            }
        }

        //故人様編集画面を表示
        $this->dispEditDeceasedInfo($deceasedInfo);
    }

    private function dispEditDeceasedInfo(array $deceasedInfo, $message = "") {
        //viewを設定
        $this->_view->message = $message;
        $this->_view->chargeSelected = $deceasedInfo['charge_name'];
        $this->_view->souke = $deceasedInfo['souke'];
        $this->_view->deceasedLastName = $deceasedInfo['deceased_last_name'];
        $this->_view->deceasedFirstName = $deceasedInfo['deceased_first_name'];
        $this->_view->deceasedBirthdayY = $deceasedInfo['deceased_birthday_y'];
        $this->_view->deceasedBirthdayM = $deceasedInfo['deceased_birthday_m'];
        $this->_view->deceasedBirthdayD = $deceasedInfo['deceased_birthday_d'];
        $this->_view->deceasedDeathdayY = $deceasedInfo['deceased_deathday_y'];
        $this->_view->deceasedDeathdayM = $deceasedInfo['deceased_deathday_m'];
        $this->_view->deceasedDeathdayD = $deceasedInfo['deceased_deathday_d'];
        $this->_view->checked1 = "";
        $this->_view->checked2 = "";
        $this->_view->checked3 = "";
        $this->_view->checked4 = "";
        $this->_view->checked5 = "";
        $this->_view->checked6 = "";
        switch ($deceasedInfo['kyonen_gyonen_flg']) {
                case 0:
                    $this->_view->checked4 = "checked";
                    break;
                case 1:
                    $this->_view->checked1 = "checked";
                    break;
                case 2:
                    $this->_view->checked2 = "checked";
                    break;
                case 3:
                    $this->_view->checked3 = "checked";
                    break;
        }
        $this->_view->deathAge = $deceasedInfo['death_age'];
        switch ($deceasedInfo['allow_push']) {
            case 1:
                $this->_view->checked5 = "checked";
                break;
            case 2:
                $this->_view->checked6 = "checked";
                break;
        }
        $this->_view->hallName = $deceasedInfo['hall_name'];
        $this->_view->imageExistenceFlg = $deceasedInfo['image_existence_flg'];
        $date = new Zend_Date();    //キャッシュ対策日時
        $this->_view->cacheKey = $date->get("yyyyMMddHHmmss");
        $this->_view->issueStateCode = $deceasedInfo['issue_state_code'];
        $this->_view->deceasedId = $deceasedInfo['deceased_id'];

        //担当者一覧を取得してビューに設定する
        $chargeList = $this->makeSelectboxSource($this->_mngModel->getChargeList(), 'charge_name');
        $this->_view->chargeList = $chargeList;

        //画面を表示
        echo $this->_view->render('mng_deceased_info_edit.tpl');
    }

    //故人様情報編集確認
    public function confeditdeceasedinfoAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //POST値から故人IDを取得
        $deceasedId = $this->getRequest()->getPost('deceased_id');

        // 押されたボタンを判定する
        if ($this->getRequest()->getPost('back')) {              //戻るボタンの場合
            //故人様表示画面表示
            // $this->dispDeceasedInfo($deceasedId);
            if($this->dispDeceasedInfo($deceasedId)){
                echo $this->_view->render('mng_deceased_info_display.tpl');
            }else{
                echo $this->_view->render('mng_error.tpl');
            }
        } elseif ($this->getRequest()->getPost('confirm')) {     //確認ボタンの場合
            //入力値を取得
            $deceasedInfo = array(
                'deceased_id' => $deceasedId,
                'charge_name' => $this->getRequest()->getPost('charge_name'),
                'souke' => $this->getRequest()->getPost('souke'),
                'deceased_last_name' => common::delSpace($this->getRequest()->getPost('deceased_last_name')),
                'deceased_first_name' => common::delSpace($this->getRequest()->getPost('deceased_first_name')),
                'deceased_birthday_y' => $this->getRequest()->getPost('deceased_birthday_y'),
                'deceased_birthday_m' => $this->getRequest()->getPost('deceased_birthday_m'),
                'deceased_birthday_d' => $this->getRequest()->getPost('deceased_birthday_d'),
                'deceased_deathday_y' => $this->getRequest()->getPost('deceased_deathday_y'),
                'deceased_deathday_m' => $this->getRequest()->getPost('deceased_deathday_m'),
                'deceased_deathday_d' => $this->getRequest()->getPost('deceased_deathday_d'),
                'kyonen_gyonen_flg' => $this->getRequest()->getPost('kyonen_gyonen_flg'),
                'death_age' => $this->getRequest()->getPost('death_age'),
                'allow_push' => $this->getRequest()->getPost('allow_push'),
                'hall_name' => $this->getRequest()->getPost('hall_name'),
                'image_existence_flg' => $this->getRequest()->getPost('image_existence_flg'));
            //画像を保存
            //画像が選択されている場合、一時フォルダに保存
            if (is_uploaded_file($_FILES["deceased_image"]["tmp_name"])) {
                //選択されている場合、フラグを1にする
                $deceasedInfo['image_existence_flg'] = IMAGE_EXISTENCE_FLG_YES;
                //一時保存用ファイル名を生成し、既にファイルが存在しないか重複チェック
                do {
                    //一時保存用ファイル名を生成
                    $fileName = comEncryption::getRandomString() . ".jpg";
                    $uploadFile = DECEASED_DATA_TEMP_PATH . $fileName;
                } while(file_exists($uploadFile));
                //一時フォルダに保存
                //800×800に収まるように画像を作成
                //サーバ版
                exec('/usr/bin/convert -define jpeg:size=1200x1200 -resize 1200x1200 ' . $_FILES['deceased_image']['tmp_name'] . ' ' .  $uploadFile);
                //パーミッションを変更
                chmod($uploadFile, 0644);
                //ファイルパスをセッションに設定
                $this->_session->deceased_temp_data_path = $uploadFile;
            }
            //入力チェック
            $message = $this->checkDeceasedInfo($deceasedInfo);
            $message = $message . $this->checkDeceasedImage();
            if (comValidate::chkNotEmpty($message) === false) {
                //入力値をセッションに設定
                $this->_session->deceased_info = $deceasedInfo;

                //viewを設定
                $this->_view->chargeName = $deceasedInfo['charge_name'];
                $this->_view->souke = $deceasedInfo['souke'];
                $this->_view->deceasedLastName = $deceasedInfo['deceased_last_name'];
                $this->_view->deceasedFirstName = $deceasedInfo['deceased_first_name'];
                $deceasedBirthday = $deceasedInfo['deceased_birthday_y'] . "/" .
                        sprintf("%02d", $deceasedInfo['deceased_birthday_m']) . "/" .
                        sprintf("%02d", $deceasedInfo['deceased_birthday_d']);
                $this->_view->deceasedBirthday = $deceasedBirthday;
                $deceasedDeathday = $deceasedInfo['deceased_deathday_y'] . "/" .
                        sprintf("%02d", $deceasedInfo['deceased_deathday_m']) . "/" .
                        sprintf("%02d", $deceasedInfo['deceased_deathday_d']);
                $this->_view->deceasedDeathday = $deceasedDeathday;
                switch ($deceasedInfo['kyonen_gyonen_flg']) {
                    case 0:
                        $this->_view->kyonenGyonen = "－";
                        break;
                    case 1:
                        $this->_view->kyonenGyonen = "享年";
                        break;
                    case 2:
                        $this->_view->kyonenGyonen = "行年";
                        break;
                    case 3:
                        $this->_view->kyonenGyonen = "満";
                        break;
                }
                $this->_view->deathAge = $deceasedInfo['death_age'];
                switch ($deceasedInfo['allow_push']) {
                    case 1:
                        $this->_view->allowPush = "通知する";
                        break;
                    case 2:
                        $this->_view->allowPush = "通知しない";
                        break;
                }
                $this->_view->hallName = $deceasedInfo['hall_name'];
                $this->_view->deceasedId = $deceasedInfo['deceased_id'];
                $this->_view->imageExistenceFlg = $deceasedInfo['image_existence_flg'];
                $date = new Zend_Date();    //キャッシュ対策日時
                $this->_view->cacheKey = $date->get("yyyyMMddHHmmss");
                // ワンタイムトークンを発行し、セッションとフォームに設定
                $this->_session->key = session_id() . '_' . microtime();
                $this->_view->token = comToken::get_token($this->_session->key);
                //入力値が正しい場合、確認画面を表示
                echo $this->_view->render('mng_deceased_info_edit_conf.tpl');
            } else {
                //入力値が不正な場合、入力画面に戻る
                $this->dispEditDeceasedInfo($deceasedInfo, $message);
            }
        }
    }

    public function compeditdeceasedinfoAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //セッションから入力値を取得
        $deceasedInfo = $this->_session->deceased_info;

        // 押されたボタンを判定する
        if ($this->getRequest()->getPost('back')) {              //戻るボタンの場合
            //入力画面に戻る
            $this->dispEditDeceasedInfo($deceasedInfo);
        } elseif ($this->getRequest()->getPost('save')) {        //保存ボタンの場合
            // ワンタイムトークンが正しいかチェックする
            if (comToken::check_token($this->getRequest()->getPost('token'), $this->_session->key) === false) {
                // エラー画面を表示
                echo $this->_view->render('mng_error.tpl');
                exit();
            }

            // セッション内のワンタイムトークを削除する
            if (isset($this->_session->key) === true) {
                unset($this->_session->key);
            }

            //更新処理
            if ($this->_mngModel->updateDeceased($deceasedInfo)) {
                //遺影写真保存先デイレクトリのフォルダ取得
                $deceased = $this->_mngModel->getDeceased($deceasedInfo['deceased_id']);
                $fileDir = DECEASED_DATA_PATH . substr($deceased["issue_datetime"], 0, 4) .
                        '/' . $deceasedInfo['deceased_id'];
                //遺影写真のパス
                $imagePath = $fileDir . '/' . $deceasedInfo['deceased_id'] . '.jpg';
                if ($deceasedInfo['image_existence_flg'] == IMAGE_EXISTENCE_FLG_YES) {
                    //画像を選択している場合、一時フォルダから正式なフォルダに移動する
                    //ディレクトリが存在するかチェック
                    if (file_exists($fileDir)) {
                        //一時フォルダの仮アップ画像を画像フォルダに移動
                        if (rename($this->_session->deceased_temp_data_path, $imagePath) === false) {
                            //失敗した場合
                            echo "ファイルを移動出来ませんでした。";
                        }
                    } else {
                        if (mkdir($fileDir, 0755, true)) {
                            //一時フォルダの仮アップ画像を画像フォルダに移動
                            if (rename($this->_session->deceased_temp_data_path, $imagePath) === false) {
                                //失敗した場合
                                echo "ファイルを移動出来ませんでした。";
                            }
                        }
                    }
                } else {
                    //画像を選択していない場合、画像を削除する
                    if (file_exists($imagePath)) {
                        //ファイルが存在する場合、削除する
                        unlink($imagePath);
                    }
                }
                //フルネームのフィールドを追加する
                $deceasedInfo['deceased_name'] =
                        $deceasedInfo['deceased_last_name'] . "　" . $deceasedInfo['deceased_first_name'];
                //法要アプリのご案内を発行する
                comGuidanceIssue::issue($deceasedInfo, $fileDir);
            }

            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_DECEASED_EDIT, $this->_session->manager_id, "Success", $this->_httpHeaderInfo);

            //viewを設定
            $this->_view->deceasedId = $deceasedInfo['deceased_id'];

            //完了画面を表示
            echo $this->_view->render('mng_deceased_info_edit_comp.tpl');
        }
    }

    /**
     * 担当者一覧HTML生成
     * ：担当者一覧を取得し、表示用HTMLを生成するメソッド
     * return   string      担当者一覧表示用HTML
     */
    private function getChargeHtml() {
        $strHtml = "";
        // 担当者一覧を取得する
        $chargeList = $this->_mngModel->getChargeList();
        foreach($chargeList as $charge) {
            $strHtml = $strHtml . "<li>" . $charge['charge_name'] . "</li>\n";
        }
        return $strHtml;
    }

    /**
     * 故人様情報チェック
     * ：故人様情報が正しいかチェックする
     * @param array $deceasedInfo
     * @return string 正しい場合空文字、不正な場合エラーメッセージ
     */
    private function checkDeceasedInfo(array $deceasedInfo) {
        $message = "";          //エラーメッセージ格納用

        //担当者様
        //入力されているか
        if (comValidate::chkNotEmpty($deceasedInfo['charge_name']) === false) {
            $message = "・担当者様名が入力されていません。<br>";
        }
        //葬家様
        //入力されているか
        if (comValidate::chkNotEmpty($deceasedInfo['souke']) === false) {
            $message = $message . "・葬家様名が入力されていません。<br>";
        }
        //故人様名
        //入力されているか
        if (comValidate::chkNotEmpty($deceasedInfo['deceased_last_name']) === false ||
            comValidate::chkNotEmpty($deceasedInfo['deceased_first_name']) === false) {
            $message = $message . "・故人様名が入力されていません。<br>";
        }
        //生年月日
        $deceasedBirthday = $deceasedInfo['deceased_birthday_y'] . "/" .
                $deceasedInfo['deceased_birthday_m'] . "/" . $deceasedInfo['deceased_birthday_d'];
        if(comValidate::chkDate($deceasedBirthday) === false) {    //日付形式が正しいか
            $message = $message . "・生年月日はYYYY/M/D形式（例：2014/3/10）で入力してください。<br>";
        } elseif($deceasedInfo['deceased_birthday_y'] < 1900) {
            $message = $message . "・生年月日は1900年以降で入力してください。<br>";
        }

        //没年月日
        $deceasedDeathday = $deceasedInfo['deceased_deathday_y'] . "/" .
                $deceasedInfo['deceased_deathday_m'] . "/" . $deceasedInfo['deceased_deathday_d'];
        if(comValidate::chkDate($deceasedDeathday) === false) {    //日付形式が正しいか
            $message = $message . "・没年月日はYYYY/M/D形式（例：2014/3/10）で入力してください。<br>";
        }

        //生年月日＜没年月日か
        if (comValidate::chkDayLargeSmall($deceasedDeathday, $deceasedBirthday) === false) {
            $message = $message . "・没年月日が生年月日より過去になっています。<br>";
        }
        //没年齢
        //入力されているか
        if (comValidate::chkNotEmpty($deceasedInfo['death_age']) === false) {
            $message = $message . "・没年齢が入力されていません。<br>";
        } elseif(comValidate::chkInt($deceasedInfo['death_age']) === false) {    //数字か
            $message = $message . "・没年齢は数字で入力してください。<br>";
        }
        return $message;
    }

    /**
     * checkDeceasedImageメソッド
     * ：故人様の画像ファイルが正しいかチェックするメソッド
     * @return  string  正しい場合空文字、不正な場合エラーメッセージ
     */
    private function checkDeceasedImage() {
        $message = "";          //エラーメッセージ格納用

        //不正なファイルでないか
        if (!isset($_FILES["deceased_image"]["error"]) ||
            !is_int($_FILES["deceased_image"]["error"])) {
            $message = $message . "・写真のファイルが不正です。<br>";
        }
        //$_FILES['deceased_image']['error'] の値を確認
        switch ($_FILES["deceased_image"]["error"]) {
            case UPLOAD_ERR_OK:         //OK
                break;
            case UPLOAD_ERR_NO_FILE:    //ファイル未選択
                break;
            case UPLOAD_ERR_INI_SIZE:   //php.ini定義の最大サイズ超過
            case UPLOAD_ERR_FORM_SIZE:  //フォーム定義の最大サイズ超過
                $message = $message . "・写真のファイルサイズが大きすぎます。<br>";
                break;
            default:
                $message = $message . "・予期せぬエラーが発生しました。<br>";
        }

        //選択されている場合、チェックする
        if ($_FILES["deceased_image"]["error"] === UPLOAD_ERR_OK) {
            //プロジェクトで定義するサイズ上限(10MB)のオーバーチェック
            if ($_FILES["deceased_image"]["size"] > MAX_IMAGE_FILE_SIZE) {
                $message = $message . "・写真のファイルサイズが大きすぎます。<br>";
            }
            //拡張子をチェックする
            if (comValidate::isJpeg($_FILES["deceased_image"]["tmp_name"]) === false) {
                $message = $message . "・写真はJPEG形式のファイルを選択して下さい。<br>";
            }
        }
        return $message;
    }

    //QR発注フォーム完了画面表示
    public function compdeceasedqrorderAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //セッションから入力値を取得
        $deceasedInfo = $this->_session->deceased_info;

        // 押されたボタンを判定する
        if ($this->getRequest()->getPost('back')) {              //戻るボタンの場合
            //QR発注フォーム入力画面に戻る
            $this->dispDeceasedQrOrder($deceasedInfo);
        } elseif ($this->getRequest()->getPost('send')) {        //送信ボタンの場合
            // ワンタイムトークンが正しいかチェックする
            if (comToken::check_token($this->getRequest()->getPost('token'), $this->_session->key) === false) {
                // エラー画面を表示
                echo $this->_view->render('mng_error.tpl');
                exit();
            }

            // セッション内のワンタイムトークを削除する
            if (isset($this->_session->key) === true) {
                unset($this->_session->key);
            }

            //故人IDを発行
            $duplicateFlg = true;
            do {
                //故人IDを発行
                $deceasedId = comEncryption::getRandomString(10);
                //重複するIDが存在しないか
                $deceased = $this->_mngModel->getDeceased($deceasedId);
                if (!$deceased) {
                    $duplicateFlg = false;
                }
            } while($duplicateFlg);

            //配列に追加
            $deceasedInfo['deceased_id'] = $deceasedId;
            //ファイルディレクトリパス作成
            $fileDir = DECEASED_DATA_PATH . date("Y") . '/' . $deceasedInfo['deceased_id'];
            //DBに故人情報を発行状態コードを発行依頼済みで保存
            if ($this->_mngModel->insertDeceased($deceasedInfo)) {
                //画像を選択している場合、一時フォルダから正式なフォルダに移動する
                if ($deceasedInfo['image_existence_flg'] == IMAGE_EXISTENCE_FLG_YES) {
                    //ディレクトリ生成

                    if (mkdir($fileDir, 0755, true)) {
                        //一時フォルダの仮アップ画像を画像フォルダに移動
                        $imagePath = $fileDir . '/' . $deceasedInfo['deceased_id'] . '.jpg';
                        if (rename($this->_session->deceased_temp_data_path, $imagePath) === false) {
                            //失敗した場合
                            echo "ファイルを移動出来ませんでした。";
                        }
                    }
                }
                //フルネームのフィールドを追加する
                $deceasedInfo['deceased_name'] =
                        $deceasedInfo['deceased_last_name'] . "　" . $deceasedInfo['deceased_first_name'];
                //法要アプリのご案内を発行する
                comGuidanceIssue::issue($deceasedInfo, $fileDir);

                //発注があった旨qr@wow.ne.jpにメールする
                //管理者情報取得
                $manager = $this->_mngModel->getManager($this->_session->manager_id);
                //メールに表示する入力値を設定する
                $this->_view->managerName = $manager['manager_name'];       //管理者名取得
                $this->_view->chargeName = $deceasedInfo['charge_name'];    //担当者名
                $this->_view->souke = $deceasedInfo['souke'];               //葬家様
                $this->_view->deceasedName =
                        $deceasedInfo['deceased_last_name'] . '　' .
                        $deceasedInfo['deceased_first_name'];               //故人様
                $this->_view->datetime = date("Y-m-d H:i:s");               //発注日時

                //メール文を取得
                $body = $this->_view->render('mail_order.tpl');

                //送信メールの内容を設定
                $mailInfo = array(
                    'username' => $this->_config->inquiry_mail->inquiry_mail,
                    'password' => $this->_config->inquiry_mail->inquiry_password,
                    'fromName' => $this->_config->inquiry_mail->inquiry_name,
                    'fromMail' => $this->_config->inquiry_mail->inquiry_mail,
                    'toName' => 'hyamato0@gmail.com',
                    'toMail' => 'hyamato0@gmail.com',
                    'subject' => 'ほこだてPDF修正テスト_QRコード受注のお知らせ',
                    'body' => $body
                );

                //メール送信
                comMail::sendMail($mailInfo);
            }

            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_QR_ORDER, $this->_session->manager_id, "Success", $this->_httpHeaderInfo);

            //完了画面を表示
            echo $this->_view->render('mng_deceased_qr_order_comp.tpl');
        }
    }

    //故人様情報物理削除
    //画面上のリンクからは実行できません
    //テストデータを削除する際にwowの管理者が実行する特殊な処理です
    public function compremovedeceasedAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            return $this->_forward('disprelogin');
        }

        //GET値から故人IDを取得
        $deceasedId = $this->getRequest()->getQuery('did');

        //故人情報フォルダのパスを取得
        $deceased = $this->_mngModel->getDeceased($deceasedId);
        $deceasedDataPath = DECEASED_DATA_PATH .
                substr($deceased["issue_datetime"], 0, 4) . "/" . $deceasedId;

        //DBからデータを削除
        //削除処理実行
        if ($this->_mngModel->compRemoveDeceased($deceasedId)) {
            if (file_exists($deceasedDataPath)) {
                //ファイルが存在する場合、削除する
                common::removeDirectory($deceasedDataPath);
            }
            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_DECEASE_COMP_DELETE, $this->_session->manager_id, "Success", $this->_httpHeaderInfo);
            echo "削除しました";
        } else {
            //ログ出力
            $this->_logModel->recordLog(LOG_KIND_DECEASE_COMP_DELETE, $this->_session->manager_id, "Failure", $this->_httpHeaderInfo);
            echo "削除に失敗しました";
        }
    }

    public function __call($method, $args)
    {
        $response = $this->getResponse();
        $errorMessage = mb_convert_encoding('アクションがありません。', "SJIS", "UTF-8");
        $response->setBody($errorMessage);
    }

    /**
     * makeSelectboxSourceメソッド
     * :smartyのhtml_optionsに設定する配列を作成するメソッド
     * @param   $source DBから取得した配列
     * @param   $columnName コンボボックスに表示するデータのカラム名
     * @return  array  コンボボックスに表示するデータを格納した配列
     */
    private function makeSelectboxSource(array $source, $columnName)
    {
        $list = array("" => "");
        foreach ($source as $row) {
            $list += array($row[$columnName] => $row[$columnName]);
        }

        return $list;
    }

    /**
     * makeDeceasedNameListメソッド
     * :故人名一覧を取得する
     * @return  array  故人IDをキーとした故人名配列
     */
    private function makeDeceasedNameList()
    {
        $deceasedArray = $this->_mngModel->getDeceasedList();
        $deceasedList = array("" => "");
        foreach ($deceasedArray as $row) {
            $label = $row['deceased_name'] . "  (ID:" . $row['deceased_id'] . ")";
            $deceasedList += array($row['deceased_id'] => $label);
        }

        return $deceasedList;
    }

    /**
     * checkNoticeSettingEmptyメソッド
     * :通知条件が空かどうかチェックする
     * @return 空の場合はtrue それ以外はfalse
     */
    private function checkNoticeSettingEmpty($noticeInfo)
    {
        if(!empty($noticeInfo['charge_name']))  return false;
        if(!empty($noticeInfo['hall_name']))    return false;
        if(!empty($noticeInfo['deceased_id']))  return false;
        if(!empty($noticeInfo['death_month']))  return false;
        if(!empty($noticeInfo['memorial_month']) && !empty($noticeInfo['memorial_event'])) return false;

        return true;
    }

    /**
     * テンプレート文字列を取得する
     *
     * @param array $noticeInfo   通知情報
     * @return string テンプレート文字列
     */
    private function getTemplateString($noticeInfo, $name = '[お名前]')
    {
        $templateText = '';

        if(empty($noticeInfo['template_id'])){
            //テンプレートIDが空文字の場合
            $templateText = 'なし';
        }elseif($noticeInfo['template_id'] == NOTICE_TEMPNO_DEATHDAY){
            $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_DEATHDAY);
            $templateText = common::makeTemplate($template['template_text'],
                                                 $name,
                                                 $noticeInfo['death_month'], '');
        }elseif($noticeInfo['template_id'] == NOTICE_TEMPNO_EVENT) {
            $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_EVENT);
            $templateText = common::makeTemplate($template['template_text'],
                                                 $name,
                                                 $noticeInfo['memorial_month'],
                                                 $this->_memorialEvent[$noticeInfo['memorial_event']]);
        //初七日法要
        }elseif($noticeInfo['template_id'] == NOTICE_TEMPNO_SEVENTH_DEATHDAY) {
            $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_SEVENTH_DEATHDAY);
            $templateText = common::makeTemplate($template['template_text'],
                                                 $name,
                                                 "",
                                                 "");
        //二七日法要
        }elseif($noticeInfo['template_id'] == NOTICE_TEMPNO_FOURTEENDAY_DEATHDAY) {
            $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_FOURTEENDAY_DEATHDAY);
            $templateText = common::makeTemplate($template['template_text'],
                                                 $name,
                                                 "",
                                                 "");
        //三七日法要
        }elseif($noticeInfo['template_id'] == NOTICE_TEMPNO_TWENTYONEDAY_DEATHDAY) {
            $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_TWENTYONEDAY_DEATHDAY);
            $templateText = common::makeTemplate($template['template_text'],
                                                 $name,
                                                 "",
                                                 "");
        //四七日法要
        }elseif($noticeInfo['template_id'] == NOTICE_TEMPNO_TWENTYEIGHT_DEATHDAY) {
            $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_TWENTYEIGHT_DEATHDAY);
            $templateText = common::makeTemplate($template['template_text'],
                                                 $name,
                                                 "",
                                                 "");

        //五七日法要
        }elseif($noticeInfo['template_id'] == NOTICE_TEMPNO_THIRTYFIVE_DEATHDAY) {
            $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_THIRTYFIVE_DEATHDAY);
            $templateText = common::makeTemplate($template['template_text'],
                                                 $name,
                                                 "",
                                                 "");
        //六七日法要
        }elseif($noticeInfo['template_id'] == NOTICE_TEMPNO_FORTYTWO_DEATHDAY) {
            $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_FORTYTWO_DEATHDAY);
            $templateText = common::makeTemplate($template['template_text'],
                                                 $name,
                                                 "",
                                                 "");
        //四十九日法要
        }elseif($noticeInfo['template_id'] == NOTICE_TEMPNO_FORTYNINE_DEATHDAY) {
            $template = $this->_mngModel->getTemplate(NOTICE_TEMPNO_FORTYNINE_DEATHDAY);
            $templateText = common::makeTemplate($template['template_text'],
                                                 $name,
                                                 "",
                                                 "");
        }

        return $templateText;
    }

//Zend_Debug::dump($user, $label = null, $echo = true);
}
