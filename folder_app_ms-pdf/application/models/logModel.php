<?php

/**
 * 法要アプリプレミアム版管理システムのLogに関わるDBアクセスを制御するクラス
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
require_once 'Zend/Db.php';
require_once 'Zend/Registry.php';
require_once 'Zend/Date.php';
require_once 'Zend/Debug.php';

/*Xサーバーでサブドメインの場合*/
require_once 'application/common/comDefine.php';
/**/

/*サブドメインでない場合
require_once '../application/common/comDefine.php';
/**/



class logModel {
    private $_db;   // データベースアダプタのハンドル
    
    /**
     * コンストラクタ
     * 
     */
    public function __construct() 
    {
        // レジストリからデータを取得する
        if (Zend_Registry::isRegistered('database')) {
            $database = Zend_Registry::get('database');
        }

        // データベースアダプタを作成する
        $params = array('host'      => $database['host'],
                        'username'  => $database['username'],
                        'password'  => $database['password'],
                        'dbname'    => $database['name']
                  );

      
        // データベースアダプタを作成する
        $this->_db = Zend_Db::factory($database['type'], $params);
        
        // 文字コードをUTF-8に設定する
        $this->_db->query("set names 'utf8'");

        // データ取得形式を設定する
        $this->_db->setFetchMode(Zend_Db::FETCH_ASSOC);
    }    
    
    /**
     * Logを記録する
     * 
     * @param String    $logKind    
     * @param String    $loginManagerId 
     * @param String    $addInfo
     * @param String    $httpHeaderInfo
     */
    public function recordLog($logKind, $loginManagerId, $addInfo, $httpHeaderInfo)
    {           
        try {
            $sql = "INSERT INTO t_log (
                        log_kind,
                        login_manager_id,
                        add_info,
                        http_header_info
                    )
                    VALUES
                    (
                        :log_kind,
                        :login_manager_id,
                        :add_info,
                        :http_header_info
                    )
            ";

            $this->_db->query($sql, array(
                'log_kind' => $logKind,
                'login_manager_id' => $loginManagerId,
                'add_info' => $addInfo,
                'http_header_info' => $httpHeaderInfo)
            );
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }
    
}
