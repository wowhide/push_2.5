<?php

/**
 * QRコード受注明細を集計、出力するコントローラクラス
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
require_once 'Zend/Pdf.php';

/*Xサーバーでサブドメインの場合*/
require_once 'application/common/comDefine.php';
require_once 'application/common/comValidate.php';
require_once 'application/models/addupModel.php';
require_once 'application/smarty/Zend_View_Smarty.class.php';
require_once 'application/common/comEncryption.php';

/*サブドメインでない場合
require_once '../application/common/comDefine.php';
require_once '../application/common/comValidate.php';
require_once '../application/models/addupModel.php';
require_once '../application/smarty/Zend_View_Smarty.class.php';
require_once '../application/common/comEncryption.php';
*/

//セッション有効時間（1時間）
define('SESSION_TIME', 60*60);

//ログインパスワード
define('PASSWORD', 'wow2784497');

class AddupController extends Zend_Controller_Action
{
    private $_session;                  //セッション
    private $_config;                   //設定情報
    private $_addupModel;               //addupModelのインスタンス
    private $_view;                     //Zend_View_Smartyのインスタンス

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
        $this->_addupModel = new addupModel();
        
        //Zend_View_Smartyを生成してviewを上書きする
        $this->_view = new Zend_View_Smarty();
        //ビュースクリプトのディレクトリを設定する
        $this->_view->setScriptPath(SMARTY_TEMP_PATH . 'templates');
        //ビュースクリプトのコンパイルディレクトリを設定する
        $this->_view->setCompilePath(SMARTY_TEMP_PATH . 'templates_c');
        
        //セッションを開始する
        $this->_session = new Zend_Session_Namespace('addup');
        //セッションタイムアウトを設定する
        $this->_session->setExpirationSeconds(SESSION_TIME);
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
    
    //ログイン画面表示
    public function indexAction() {
        $this->dispLogin();
    }
    
    /**
     * ログイン画面表示
     * ：ログイン画面を表示する
     * 　引数が設定されている場合、それぞれ画面に設定して表示する
     * @param string $message   エラーメッセージ
     * @param string $id        入力された管理者ID
     * @param string $password  入力されたパスワード
     */
    public function dispLogin($message="", $id="", $password="") {
        //viewに空文字を設定
        $this->_view->message = $message;
        $this->_view->id = $id;
        $this->_view->password = $password;

        //ログイン画面を表示する
        echo $this->_view->render('addup_login.tpl');        
    }

    //ログイン
    public function loginAction() {
        //POST値を取得
        $id = $this->getRequest()->getPost('id');                   //ログインID
        $password = $this->getRequest()->getPost('password');       //パスワード
        
        //ログインチェック
        if ($this->checkLogin($id, $password)) {
            $this->_view->message = "";
            $this->_view->from = "";
            $this->_view->to = "";
            $this->_view->andloid_dl = "";
            $this->_view->ios_dl = "";

            //正しい場合、集計ダウンロードページを表示
            echo $this->_view->render('addup_download.tpl');
        } else {
            //不正な場合、ログイン画面に戻る
            $this->dispLogin("ID、パスワードの組み合わせが正しくありません。", $id, $password);
            exit();
        }
    }
    
    /**
     * ログインチェック
     * ：ID、PWが正しいかチェックする
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
        $manager = $this->_addupModel->getManager(comEncryption::encryption($id));
        
        //取得した管理者情報と引数のPWが等しいかチェックする
        if (!$manager || PASSWORD !== $password) {
            return false;
        }

        //セッションにログイン状態を設定
        $this->_session->is_login = true;
        //セッションに管理者名を設定
        $this->_session->manager_name = $manager['manager_name'];

        return true;
    }

    //受注明細ダウンロード
    public function downloadAction() {
        if ($this->chkSession() === false) {
            //ログインしていない場合またはセッションタイムアウトした場合、ログイン画面を表示
            $this->dispLogin("タイムアウトしました。もう一度ログインしてください");
            exit();
        }
        
        //POST値を取得
        $from = $this->getRequest()->getPost('from');               //期間FROM
        $to = $this->getRequest()->getPost('to');                   //期間TO
        $androidDl = $this->getRequest()->getPost('android_dl');    //Androidダウンロード数
        $iosDl = $this->getRequest()->getPost('ios_dl');    //Androidダウンロード数
        
        //入力チェック
        $message = $this->checkDownload($from, $to, $androidDl, $iosDl); 
        if (empty($message) === false) {
            $this->_view->message = $message;
            $this->_view->from = $from;
            $this->_view->to = $to;
            $this->_view->android_dl = $androidDl;
            $this->_view->ios_dl = $iosDl;

            //正しい場合、集計ダウンロードページを表示
            echo $this->_view->render('addup_download.tpl');
            exit();
        }
        
        //データが存在するかチェック
        $deceasedList = $this->_addupModel->getDeceasedList($from, $to);
        if (count($deceasedList) === 0) {
            $this->_view->message = "指定した期間に発行したQRコードはありません。";
            $this->_view->from = $from;
            $this->_view->to = $to;
            $this->_view->android_dl = $androidDl;
            $this->_view->ios_dl = $iosDl;

            //正しい場合、集計ダウンロードページを表示
            echo $this->_view->render('addup_download.tpl');
            exit();
        }
        
        //DBから集計データを取得
        $this->downloadPdf($from, $to, $androidDl, $iosDl);
    }
    
    /**
     * 受注明細入力内容チェック
     * ：受注明細の生成に必要な情報が入力されているかチェックする
     * @param string $from      集計期間From
     * @param string $to        集計期間To
     * @param string $androidDl アンドロイドダウンロード数
     * @param string $iosDl     iOSダウンロード数
     * @return string   エラーメッセージ
     */
    private function checkDownload($from, $to, $androidDl, $iosDl) {
        $message = "";
        //期間が入力されているか
        if (comValidate::chkNotEmpty($from) === false || 
            comValidate::chkNotEmpty($to) === false) {
            $message = $message . "集計期間はFromとToの両方入力してください。<br>";
        } elseif ($to < $from) {    //期間FROMよりもTOの方が大きいか
            $message = $message . "集計期間のFromはToより過去の日付を入力してください。<br>";
        }
        if (comValidate::chkNotEmpty($androidDl) === false ||
            comValidate::chkNotEmpty($iosDl) === false) {
            $message = $message . "ダウンロード数を入力してください。<br>";
        }
        return $message;
    }
    
    /**
     * 受注明細PDF生成ダウンロード
     * ：受注明細PDFを生成してダウンロードする
     * @param string $from      集計期間From
     * @param string $to        集計期間To
     * @param string $androidDl アンドロイドダウンロード数
     * @param string $iosDl     iOSダウンロード数
     */
    private function downloadPdf($from, $to, $androidDl, $iosDl) {
        //PDFテンプレートを読み込む
        //Zend_Pdfのインスタンス生成
        $pdf = new Zend_Pdf();
        //テンプレートファイルの読み込み
        $pdf = Zend_Pdf::load(PDF_PATH . 'addup.pdf');
        //フォントの指定
        $font = Zend_Pdf_Font::fontWithPath(PDF_PATH . 'font/ipamp.ttf');
        $pdf->pages[0]->setFont($font, 15);

        //故人情報取得
        $deceasedList = $this->_addupModel->getDeceasedList($from, $to);

        //データ数取得
        $deceasedCount = count($deceasedList);
        //ページ数計算
        if (($deceasedCount % 40) != 0) {
            $pageCount = floor($deceasedCount / 40) + 1;
        } else {
            $pageCount = floor($deceasedCount / 40);
        }

        for ($i=1; $i <= $pageCount; $i++) {
            //ページコピー
            $pdf->pages[] = new Zend_Pdf_Page($pdf->pages[0]);
            //フォントサイズ変更
            $pdf->pages[$i]->setFont($font, 10);
            //ページ数設定
            $pdf->pages[$i]->drawText($i . "／" . $pageCount . "ページ" , 500, 800, 'UTF-8');
            //フォントサイズ変更
            $pdf->pages[$i]->setFont($font, 15);
            //葬儀社名設定
            $pdf->pages[$i]->drawText($this->_session->manager_name . "　様", 54, 748, 'UTF-8');
            //期間設定
            $pdf->pages[$i]->drawText(date('Y年n月j日', strtotime($from)) . "　～　" . 
                    date('Y年n月j日', strtotime($to)) . "　納品分" , 54, 722, 'UTF-8');
            //受注日、故人様名設定
            //フォントサイズ変更
            $pdf->pages[$i]->setFont($font, 13);
            //故人情報リスト取得開始要素数取得
            $startElement = (($i-1) * 40);
            //出力位置
            $outY = 660;
            //改行カウント
            $dataCount = 0;
            for ($j=$startElement; $j < $deceasedCount; $j++) {
                $dataCount = $dataCount + 1;
                if ($dataCount <= 20) {
                    $pdf->pages[$i]->drawText(date('n月j日', strtotime($deceasedList[$j]['issue_datetime'])), 57, $outY, 'UTF-8');
                    $pdf->pages[$i]->drawText($deceasedList[$j]['deceased_name'] , 127, $outY, 'UTF-8');
                    $pdf->pages[$i]->drawText("様" , 255, $outY, 'UTF-8');
                } else {
                    $pdf->pages[$i]->drawText(date('n月j日', strtotime($deceasedList[$j]['issue_datetime'])), 317, $outY, 'UTF-8');
                    $pdf->pages[$i]->drawText($deceasedList[$j]['deceased_name'] , 387, $outY, 'UTF-8');
                    $pdf->pages[$i]->drawText("様" , 515, $outY, 'UTF-8');
                }
                $outY = $outY - 25;
                //データが20件を越えた場合、出力位置を上に戻す
                if ($dataCount == 20) {
                    $outY = 660;
                }
                //40件出力したら次ページに進む
                if ($dataCount == 40) {
                    break;
                }
            }
            
            //最後のページに出力
            if ($i == $pageCount) {
                //アプリダウンロード累計数設定
                $pdf->pages[$i]->drawText("アプリダウンロード累計数　" . date('n月j日') . "現在" , 57, 76, 'UTF-8');
                $pdf->pages[$i]->drawLine(55,71,300,71);
                $pdf->pages[$i]->drawText("Android版", 57, 56, 'UTF-8');
                $pdf->pages[$i]->drawText($androidDl, 130, 56, 'UTF-8');
                $pdf->pages[$i]->drawText("iOS版", 57, 38, 'UTF-8');
                $pdf->pages[$i]->drawText($iosDl, 130, 38, 'UTF-8');
                //合計設定
                $pdf->pages[$i]->drawText("合計", 385, 100, 'UTF-8');
                $pdf->pages[$i]->drawText($deceasedCount . "件", 455, 100, 'UTF-8');
                $pdf->pages[$i]->drawLine(380,95,555,95);
                $pdf->pages[$i]->drawLine(380,93,555,93);
            }
/*
            // 出力位置目安出力
            $pdf->pages[$i]->setFont($font, 10);
            for ($m=1; $m<=11; $m++) {
                $x = $m * 50;
                $pdf->pages[$i]->drawText('x:' . $x, $x, 5, 'UTF-8');
            }
            for ($n=1; $n<=16; $n++) {
                $y = $n * 50;
                $pdf->pages[$i]->drawText('y:' . $y, 5, $y, 'UTF-8');
            }
*/
        }

        //テンプレートページを削除
        unset($pdf->pages[0]);
        
        // 出力
        // HTTPヘッダ：PDFを出力
        header("Content-type: application/pdf");
        // ファイル名の文字コードをSJISに変換
/*        $outputFileName = mb_convert_encoding("法要アプリ受注明細_" . 
                $this->_session->manager_name . "様.pdf", "SJIS", "UTF-8");*/
        $outputFileName = "法要アプリ受注明細_" . $this->_session->manager_name . "様.pdf";
        // IE8用にキャッシュコントロールをpublicに設定（nocashだとhttpsの場合、ダウンロードできない為）
        header("Cache-Control: public");
        header("Pragma:");
        // HTTPヘッダ：ファイル名設定
        header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($outputFileName));
        // ドキュメントを出力
        echo $pdf->render();
    }
    
    //ログアウト
    public function logoutAction() {
        // セッションをクリアする
        Zend_Session::destroy();
        
        //ログイン画面を表示する
        $this->dispLogin();
    }

    public function __call($method, $args)
    {
        $response = $this->getResponse();
        $errorMessage = mb_convert_encoding('アクションがありません。', "SJIS", "UTF-8");
        $response->setBody($errorMessage);
    }

//Zend_Debug::dump($user, $label = null, $echo = true);
}
