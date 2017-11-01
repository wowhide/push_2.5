<?php

/**
 * QRコード受注数集計のDBアクセスを制御するクラス
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

class addupModel {
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
     * 管理者マスタの情報を取得する
     * manager_idを指定して、管理者情報を取得する
     *
     * @param   string  $id         manager_id
     * @return  array   管理者情報
     */
    public function getManager($id)
    {
        $sql = "SELECT
                    *
                FROM
                    m_manager
                WHERE
                    manager_id = :manager_id
                ";
        $manager = $this->_db->fetchRow($sql, array(':manager_id' => $id));
        return $manager;
    }
    
    public function getDeceasedList($from, $to) {
        //日付文字列をSQL条件用に編集
        $from = date('Y-m-d 00:00:00', strtotime($from));
        $to = date('Y-m-d 23:59:59', strtotime($to));
        
        $sql = "SELECT 
                    deceased_name, issue_datetime 
                FROM 
                    m_deceased 
                WHERE 
                    (issue_state_code = 3 OR issue_state_code = 4) 
                AND
                    entry_datetime >= :from 
                AND 
                    entry_datetime <= :to 
                ORDER BY 
                    issue_datetime ASC, 
                    entry_datetime ASC";

        $deceasedList = $this->_db->fetchAll($sql, array(':from' => $from, ':to' => $to));
        return $deceasedList;
    }
}
