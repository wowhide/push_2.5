<?php
/**
 * 入力データのチェックを行う共通関数
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
require_once 'Zend/Validate.php';
require_once 'Zend/Validate/Alnum.php';
require_once 'Zend/Validate/Between.php';
require_once 'Zend/Validate/Digits.php';
require_once 'Zend/Validate/Int.php';
require_once 'Zend/Validate/EmailAddress.php';
require_once 'Zend/Validate/NotEmpty.php';
require_once 'Zend/Validate/StringLength.php';
require_once 'Zend/Validate/Date.php';
require_once 'Zend_Validate_MbstringLength.php';

class comValidate
{
    /**
     * 半角英数字かチェックする
     * 
     * @param  char    $value チェックする値
     * @return boolean true:正しい false:正しくない
     */
    public static function chkAlnum($value)
    {
        $validator = new Zend_Validate_Alnum();
        return $validator->isValid($value);
    }
    
    /**
     * 指定した範囲内かチェックする
     * 
     * @param  char    $value チェックする値
     * @param  int     $min   最小値
     * @param  int     $max   最大値
     * @return boolean true:正しい false:正しくない
     */
    public static function chkBetween($value, $min, $max)
    {
        $validator = new Zend_Validate_Between(array('min' => $min, 'max' => $max));
        return $validator->isValid($value);
    }
    
    /**
     * 数字かチェックする
     * 
     * @param  char    $value チェックする値
     * @return boolean true:正しい false:正しくない
     */
    public static function chkDigits($value)
    {
        $validator = new Zend_Validate_Digits();
        return $validator->isValid($value);
    }
    
    /**
     * 整数型かチェックする
     * 
     * @param  char    $value チェックする値
     * @return boolean true:正しい false:正しくない
     */
    public static function chkInt($value)
    {
        $validator = new Zend_Validate_Int();
        return $validator->isValid($value);
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
     * URLの入力形式が正しいかチェックする
     * 
     * @param  char    $value チェックする値
     * @return boolean true:正しい false:正しくない
     */
    public static function chkUrl($value)
    {
        $regex = '/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/';
        if (preg_match($regex, $value, $matches)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 値が入力されているかチェックする
     * 
     * @param  char    $value チェックする値
     * @return boolean true:正しい false:正しくない
     */
    public static function chkNotEmpty($value)
    {
        $value = preg_replace('/(\s|　)/','',$value);
        $validator = new Zend_Validate_NotEmpty();
        return $validator->isValid($value);
    }
    
    /**
     * 文字数が設定した範囲内かチェックする
     * 
     * @param  char    $value チェックする値
     * @param  int     $min   チェックする値
     * @param  int     $max   チェックする値
     * @return boolean true:正しい false:正しくない
     */
    public static function chkStringLength($value, $min, $max)
    {
/*        ingiconv_set_encoding('internal_encoding', 'utf-8');*/
        $validator = new Zend_Validate_MbstringLength(array('min' => $min, 'max' => $max));
        return $validator->isValid($value);
    }
    
    /**
     * 日付型が正しいかチェックする
     * 
     * @param  char    $value チェックする値
     * @return boolean true:正しい false:正しくない
     */
    public static function chkDate($value)
    {
        $validate = new Zend_Validate_Date();
        $validate->setFormat('yyyy/MM/dd');

        if (!$validate->isValid($value)) {
            return false;
        } else {
            $dt = explode('/', $value);
            $year = $dt[0];
            $month = $dt[1];
            $day = $dt[2];
            if (!checkdate($month, $day, $year)) {
                return false;
            }
            return true;
        }
    }
    
    /**
     * ふりがなかチェックする
     * 
     * @param  char    $value チェックする値
     * @return boolean true:正しい false:正しくない
     */
    public static function chkFurigana($value)
    {
        mb_regex_encoding("UTF-8");
        if (preg_match("/^[ぁ-ん 　ー－]+$/u", $value)) {
            return true; 
        } else {
            return false;
        }
    }
    
    /**
     * 電話番号形式かチェックする
     * 
     * @param  char    $value チェックする値
     * @return boolean true:正しい false:正しくない
     */
    public static function chkTel($value)
    {
        $validate = new My_Validate_Telephone();
        return $validate->isValid($value);
    }
    
    /**
     * 郵便番号形式かチェックする
     * 
     * @param  char    $value チェックする値
     * @return boolean true:正しい false:正しくない
     */
    public static function chkPost($value)
    {
        $validate = new My_Validate_PostCode();
        return $validate->isValid($value);
    }

    /**
     * 半角英数記号（「-」「_」）をチェックする
     * 
     * @param  char    $value チェックする値
     * @return boolean true:正しい false:正しくない
     */
    public static function chkHankakuEisuKigo($value)
    {
        mb_regex_encoding("UTF-8");
        if (preg_match("/^[a-zA-Z0-9\-\_]+$/u", $value)) {
            return true; 
        } else {
            return false;
        }
    }
    
    /**
     * isJpegメソッド
     * ：ファイル形式がJpeg形式かチェックするメソッド
     *
     * @param   file    $file   ファイルオブジェクト
     * @return  boolean TRUE:正しい   FALSE:正しくない
     */
    public static function isJpeg($file)
    {
        if (!(file_exists($file) && ($type=exif_imagetype($file)))) {
            return false;
        }
        switch ($type) {
            case IMAGETYPE_JPEG:
                return true;
            default:
                return false;
        }
    }
    
    /**
     * chkDayLargeSmallメソッド
     * ：引数の没年月日と生年月日の大小をチェックする
     *
     * @param   char    $deathDay
     * @param   char    $birthDay   
     * @return  boolean TRUE:正しい(没年月日が大きい)
     *                   FALSE:正しくない（没年月日が小さい）
     */
    public static function chkDayLargeSmall($deathDay, $birthDay) {
        if (strtotime($deathDay) > strtotime($birthDay)) {
            return true;
        } else {
            return false;
        }
    }
}


