<?php
/**
 * メール送信関連の処理を行う共通関数
 * 
 * LICENSE: 
 * 
 * @copyright   2014 Digtalspace WOW CO.,Ltd
 * @license     
 * @version     1.0.0
 * @link        
 * @since       File availabel since Release 1.0.0
 */

// コンポーネントをロードする
require_once 'Zend/Mail.php';
require_once 'Zend/Mail/Transport/Smtp.php';
require_once 'Zend/Config/Ini.php';

class comMail
{
    /**
     * sendMailメソッド
     * ：メールを送信するメソッド
     * 
     * @param   array       infoMail   
     * @return  boolean     TRUE:成功   FALSE:失敗
     */
    public static function sendMail($infoMail)
    {
        // 文字コード設定
        $mailCharset = 'ISO-2022-JP';

        // メールサーバーの情報を設定
        $username = $infoMail["username"];
        $password = $infoMail["password"];
        
        // メールの内容を設定
        $fromName = $infoMail["fromName"];
        $fromMail = $infoMail["fromMail"];
        $toName   = $infoMail["toName"];
        $toMail   = $infoMail["toMail"];
        $subject  = $infoMail["subject"];
        $body     = $infoMail["body"];

        // 文字コードを「ISO-2022-JP」に変換
        $fromName = mb_encode_mimeheader(mb_convert_encoding($fromName, 'JIS', 'auto'), $mailCharset);
        $toName = mb_encode_mimeheader(mb_convert_encoding($toName, 'JIS', 'auto'), $mailCharset);
        $subject  = mb_convert_encoding($subject, $mailCharset, 'auto');
        $body     = mb_convert_encoding($body, $mailCharset, 'auto');

        $mail_config = array('auth'     => 'login',
                             'username' => $username,
                             'password' => $password,
                             'port' => 25
                        );

        // 使用するメールサーバを設定する
        $smtp = new Zend_Mail_Transport_Smtp('photo-cube.xsrv.jp', $mail_config);
        Zend_Mail::setDefaultTransport($smtp);

        // インスタンスを生成
        $mail = new Zend_Mail($mailCharset);

        // メールを作成する
        $mail->setFrom($fromMail, $fromName);
        $mail->addTo($toMail, $toName);
        $mail->setSubject($subject);
        $mail->setBodyText($body);

        // メールを送信する
        $mail->send();
    }
}