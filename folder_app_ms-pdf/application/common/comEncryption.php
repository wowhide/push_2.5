<?php
/**
 * 暗号化関連の処理を行う共通関数
 * 
 * LICENSE: 
 * 
 * @copyright   2012 Digtalspace WOW CO.,Ltd
 * @license     
 * @version     1.0.0
 * @link        
 * @since       File availabel since Release 1.0.0
 */

// 暗号化キー
define('CIPHER_KEY','MemorialService');
define('IV','27270861');

class comEncryption
{
    /**
     * 暗号化する
     * 
     * @param  string   $data 暗号化する値
     * @return string   暗号化された値
     */
    
    public static function encryption($data)
    {
        if (empty($data) === false) {
            //事前処理
            $resource = mcrypt_module_open(MCRYPT_BLOWFISH, '',  MCRYPT_MODE_CBC, '');

            //暗号化処理
            mcrypt_generic_init($resource, CIPHER_KEY, IV);
            $encrypted_data = mcrypt_generic($resource, base64_encode($data));
            mcrypt_generic_deinit($resource);

            //モジュールを閉じる
            mcrypt_module_close($resource);

            return base64_encode($encrypted_data);
        } else {
            return "";
        }
    }
    
    // 復号化処理
    /**
     * 復号化する
     * 
     * @param  string   $data 暗号化された値
     * @return string   復号化された値
     */
    public static function decryption($data)
    {
        if (empty($data) === false) {
            //事前処理
            $resource = mcrypt_module_open(MCRYPT_BLOWFISH, '',  MCRYPT_MODE_CBC, '');

            //復号処理
            mcrypt_generic_init($resource, CIPHER_KEY, IV);
            $base64_decrypted_data = mdecrypt_generic($resource, base64_decode($data));
            mcrypt_generic_deinit($resource);

            //モジュールを閉じる
            mcrypt_module_close($resource);

            return base64_decode($base64_decrypted_data);
        } else {
            return "";
        }
    }
    
    /**
     * ランダムな文字列を生成する。
     * 
     * @param   int     $nLengthRequired 必要な文字列長。省略すると 8 文字
     * @return  String  ランダムな文字列
     */
    public static function getRandomString($nLengthRequired = 8){
        $sCharList = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        mt_srand();
        $sRes = '';
        for($i = 0; $i < $nLengthRequired; $i++) {
            $sRes .= $sCharList{mt_rand(0, strlen($sCharList) - 1)};
        }
        return $sRes;
    }
}