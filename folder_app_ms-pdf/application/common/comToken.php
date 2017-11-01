<?php
/**
 * トークン関連の処理を行う共通関数
 * 
 * LICENSE: 
 * 
 * @copyright   2012 Digtalspace WOW CO.,Ltd
 * @license     
 * @version     1.0.0
 * @link        
 * @since       File availabel since Release 1.0.0
 */

class comToken
{
    /**
     * ワンタイムトークンを生成する関数
     * 
     * @param  string   $key        トークン発行用のキー
     * @return string   生成されたワンタイムトークン
     */
    public static function get_token($key)
    {
        return sha1($key);
    }
    
    /**
     * ワンタイムトークンをチェックする関数
     * 
     * @param  string   $token      フォームに設定したトークンの値
     * @param  string   $key        セッションに保存したkeyの値
     * @return boolean  TRUE:正しい　FALSE:正しくない
     */
    public static function check_token($token, $key)
    {
        return ($token === sha1($key));
    }
}