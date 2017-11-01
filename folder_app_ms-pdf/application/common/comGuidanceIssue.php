<?php
/**
 * 法要アプリのご案内PDF生成関連の処理を行う関数
 * 
 * LICENSE: 
 * 
 * @copyright   2014 Digtalspace WOW CO.,Ltd
 * @license     
 * @version     1.0.0
 * @link        
 * @since       File availabel since Release 1.0.0
 */

//コンポーネントをロードする
require_once 'Zend/Pdf.php';

/*Xサーバーでサブドメインの場合*/
require_once 'application/common/comDefine.php';

/*サブドメインでない場合
require_once '../application/common/comDefine.php';
 */

//定数
//QR生成URL
define('QR_GENERATED_URL', 'http://memorial-site.net/qr_img/php/qr_img.php?d=%s&s=8&t=J');
//PDF固定文
define('PDF_FIXED_SENTENCE_01', "%s 家様");
define('PDF_FIXED_SENTENCE_02', "この度は、%s家様のご葬儀をお手伝いさ");
define('PDF_FIXED_SENTENCE_03', "故 %s 様の");
define('PDF_FIXED_SENTENCE_04', "ップし、下記故%s様のＱＲコードを");
define('PDF_FIXED_SENTENCE_05', "故%s様　ＱＲコード");

class comGuidanceIssue
{
    /**
     * issueメソッド
     * ：PDFを発行するメソッド
     * 
     * @param   array       $deceasedInfo   故人様情報
     * @param   string      $fileDir        保存先のフォルダ
     * @return  boolean TRUE:正しい   FALSE:正しくない
     */
    public static function issue(array $deceasedInfo, $fileDir) {
        //QRコード保存フォルダを作成する
        if (file_exists($fileDir) === false) {         //ディレクトリが存在しない場合
            if (mkdir($fileDir, 0755, true) === false) {     //ディレクトリの作成に失敗した場合
                return false;
                exit();
            }
        }
        //QRコードを生成する
        $qrGetURL = sprintf(QR_GENERATED_URL, 
                            $deceasedInfo['deceased_id'] . "," . 
                            urlencode($deceasedInfo['deceased_name']));
        $qr_img = ImageCreateFromJPEG($qrGetURL);
        ImageJPEG($qr_img, $fileDir . '/qr.jpg', 100);

        //PDFテンプレートを読み込む
        //Zend_Pdfのインスタンス生成
        $pdf = new Zend_Pdf();
        //テンプレートファイルの読み込み
        $pdf = Zend_Pdf::load(PDF_PATH . 'guidance.pdf');
        //フォントの指定
        $font = Zend_Pdf_Font::fontWithPath(PDF_PATH . 'font/ipaexg.ttf');

        //テンプレートに情報を入れ込む
        //葬家様名
        $pdf->pages[0]->setFont($font, 14);
        $pdf->pages[0]->drawText(
                sprintf(PDF_FIXED_SENTENCE_01, $deceasedInfo['souke']), 39, 724, 'UTF-8');
        //あいさつ文
        $pdf->pages[0]->setFont($font, 12);
        // $pdf->pages[0]->drawText(
        //         sprintf(PDF_FIXED_SENTENCE_02, $deceasedInfo['souke']), 39, 707, 'UTF-8');
        $pdf->pages[0]->drawText(
                sprintf(PDF_FIXED_SENTENCE_03, $deceasedInfo['deceased_name']), 39, 593, 'UTF-8');
        //故人様名
        // $pdf->pages[0]->drawText(
        //         sprintf(PDF_FIXED_SENTENCE_04, 
        //         $deceasedInfo['deceased_name']), 340, 370, 'UTF-8');
        $pdf->pages[0]->setFont($font, 10);
        $pdf->pages[0]->drawText(
                sprintf(PDF_FIXED_SENTENCE_05, 
                $deceasedInfo['deceased_name']), 330, 320, 'UTF-8');

        //QRコードを描画する
        $image = Zend_Pdf_Image::imageWithPath($fileDir . '/qr.jpg');
        $pdf->pages[0]->drawImage($image, 500, 280, 570, 355);

/*        // 出力位置目安出力
        $pdf->pages[0]->setFont($font, 10);
        for ($i=1; $i<=11; $i++) {
            $x = $i * 50;
            $pdf->pages[0]->drawText('x:' . $x, $x, 5, 'UTF-8');
        }
        for ($j=1; $j<=16; $j++) {
            $y = $j * 50;
            $pdf->pages[0]->drawText('y:' . $y, 5, $y, 'UTF-8');
        }*/
        
        //ファイル名
        $fileName = sprintf(PDF_FILE_NAME,  str_replace("　", "", $deceasedInfo['deceased_name']));

        //PDFパス
        $pdfPath = $fileDir . '/' . $fileName;
        $pdfPath = mb_convert_encoding($pdfPath,  'Shift_JIS', 'UTF-8');

        //古いPDFを削除する
        $delPath = $fileDir . '/*.pdf';
        foreach (glob($delPath) as $val) {
            unlink($val);
        }

        //保存する
        $pdf->save($pdfPath);
    }
}