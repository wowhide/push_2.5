<?php
/**
 * 共通関数を定義
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
require_once 'Zend/Validate/EmailAddress.php';

class common
{
    /**
     * ランダムな文字列を生成する。
     * 
     * @param   int     $nLengthRequired 必要な文字列長。省略すると 8 文字
     * @return  String  ランダムな文字列
     */
    public static function getRandomString($nLengthRequired = 8){
        $sCharList = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        mt_srand();
        $sRes = '';
        for($i = 0; $i < $nLengthRequired; $i++)
            $sRes .= $sCharList{mt_rand(0, strlen($sCharList) - 1)};
        return $sRes;
    }

    /**
     * メールアドレスかチェックする
     * 
     * @param  char    $value チェックする値
     * @return boolean true:正しい false:正しくない
     */
    public static function chkEmailAddress($value)
    {
        $validator = new Zend_Validate_EmailAddress();
        return $validator->isValid($value);
    }
    
    /**
     * ディレクトリを削除する
     * 
     * @param  string    $dir   削除するディレクトリ
     */
    public static function removeDirectory($dir) {
        if ($handle = opendir("$dir")) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$dir/$item")) {
                        remove_directory("$dir/$item");
                    } else {
                        unlink("$dir/$item");
                    }
                }
            }
            closedir($handle);
            rmdir($dir);
        }
    }
    
    /**
     * 故人様のお名前を全角スペース、半角スペースで分割する
     * 
     * @param string $deceasedName
     * @return array 分割した故人様のお名前
     */
    public static function splitDeceasedName($deceasedName) {
        //全角スペースで分割
        $arrayFullSplit = explode('　', $deceasedName);
        if (count($arrayFullSplit) >= 2) {
            return $arrayFullSplit;
        } else {
            //半角スペースで分割
            $arrayHalfSplit = explode(' ', $deceasedName);
            return $arrayHalfSplit;
        }
    }
    
    /**
     * 文字列中の全角スペース、半角スペースを削除する
     * 
     * @param string text
     * @return string 全角スペース、半角スペースを削除した文字
     */
    public static function delSpace($text) {
        $text = preg_replace('/(\s|　)/','',$text);
        return $text;
    }

    /**
     * 通知テンプレートの変数部分に文字列を入れ込む
     *
     * @param string $templateText   テンプレートテキスト
     * @param string $name   お名前
     * @param string $month   月
     * @param string $event   法要名
     * @return string 置換後のテンプレートテキスト
     */
    public static function makeTemplate($templateText, $name, $month, $event) {
        $resultText = str_replace('{tpl_name}', $name, $templateText);
        $resultText = str_replace('{tpl_month}', $month, $resultText);
        $resultText = str_replace('{tpl_event}', $event, $resultText);

        return $resultText;
    }
}
