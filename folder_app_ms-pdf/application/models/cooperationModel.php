<?php

/**
 * 法要アプリプレミアム版との連携に関わるDBアクセスを制御するクラス
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

require_once 'Zend/Db/Profiler.php';

/*Xサーバーでサブドメインの場合*/
require_once 'application/common/comEncryption.php';
/**/

/*サブドメインでない場合
require_once '../application/common/comEncryption.php';
/**/

class cooperationModel {
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
    
    public function beginTransaction() {
        $this->_db->beginTransaction();
    }
    
    public function commit() {
        $this->_db->commit();
    }
    
    public function rollBack() {
        $this->_db->rollBack();
    }
    
    /*
     * 故人情報を取得する
     * 
     * @param   string      $deceasedId     故人ID
     * @return  array       対象の故人情報
     */
    public function getDeceased($deceasedId) {
        // 対象の故人情報を取得する
        $sql = "SELECT 
                    * 
                FROM
                    m_deceased
                WHERE 
                    deceased_id = :deceased_id
                AND
                    issue_state_code = 3
               ";
        $deceased = $this->_db->fetchRow($sql, array('deceased_id' => $deceasedId));
        return $deceased;
    }
    
    /*
     * 利用者情報の登録チェック
     * 
     * @param   string  $morticianNo    葬儀社No
     * @param   string  $mailAddress    メールアドレス
     * @param   string  $deceasedId     故人ID
     * @return  boolean true:登録済み false:未登録
     */
    public function checkUserMail($morticianNo, $mailAddress, $deceasedId) {
        $sql = "SELECT
                    * 
                FROM
                    t_user_mail
                WHERE
                    mortician_no = :mortician_no
                AND
                    mail_address = :mail_address
                AND
                    deceased_id = :deceased_id
            ";
        $userMail = $this->_db->fetchRow($sql, array('mortician_no' => $morticianNo,
                                                     'mail_address' => comEncryption::encryption($mailAddress),
                                                     'deceased_id' => $deceasedId));
        if (empty($userMail)) {
            // 空の場合falseを返す
            return false;
        } else {
            // データが存在する場合trueを返す
            return true;
        }
    }

    /*
     * 利用者情報を登録する
     * 
     * @param   string  $morticianNo    葬儀社No
     * @param   string  $mailAddress    メールアドレス
     * @param   string  $deceasedId     故人ID
     * 
     */
    public function insertUserMail($morticianNo, $mailAddress, $deceasedId) {
        try {
            $sql = "INSERT INTO t_user_mail (
                        mortician_no,
                        mail_address,
                        deceased_id,
                        entry_datetime
                    )
                    VALUES
                    (
                        :mortician_no,
                        :mail_address,
                        :deceased_id,
                        :entry_datetime
                    )
                ";
            $this->_db->query($sql, array('mortician_no' => $morticianNo,
                                          'mail_address' => comEncryption::encryption($mailAddress),
                                          'deceased_id' => $deceasedId,
                                          'entry_datetime' => date('Y-m-d H:i:s')));
        } catch(Exception $e) {
            return false;
        }
        return true;
    }
    
    /*
     * データーキーをチェックする
     * 
     * @param   string  $dataKey    データーキー
     * @return  boolean true:登録済み false:未登録
     */
    public function checkDataKey($dataKey) {
        $sql = "SELECT
                    * 
                FROM 
                    t_transfer_user 
                WHERE 
                    data_key = :data_key
            ";
        $transferUser = $this->_db->fetchRow($sql, array('data_key' => $dataKey));
        if (empty($transferUser)) {
            // 空の場合falseを返す
            return false;
        } else {
            // データが存在する場合trueを返す
            return true;
        }
    }
    
    /**
     * 利用者引継テーブルにデータを登録する
     * 
     * @param String $dataKey
     * @param String[] $user
     * @return  boolean true:正常終了 false:エラー
     */
    public function insertTransferUser($dataKey, $user) {
        try {
            $sql = "INSERT INTO t_transfer_user (
                        data_key,
                        name,
                        mail_address,
                        notice_month_deathday_before,
                        notice_month_deathday,
                        notice_deathday_1week_before,
                        notice_deathday_before,
                        notice_deathday,
                        notice_memorial_3month_before,
                        notice_memorial_1month_before,
                        notice_memorial_1week_before,
                        notice_time,                        
                        install_datetime,
                        entry_datetime
                    )
                    VALUES
                    (
                        :data_key,
                        :name,
                        :mail_address,
                        :notice_month_deathday_before,
                        :notice_month_deathday,
                        :notice_deathday_1week_before,
                        :notice_deathday_before,
                        :notice_deathday,
                        :notice_memorial_3month_before,
                        :notice_memorial_1month_before,
                        :notice_memorial_1week_before,
                        :notice_time,                        
                        :install_datetime,
                        :entry_datetime
                    )
                ";
            $this->_db->query($sql, array('data_key' => $dataKey,
                                          'name' => $user["name"],
                                          'mail_address' => comEncryption::encryption($user["mail_address"]),
                                          'notice_month_deathday_before' => (int)$user["notice_month_deathday_before"],
                                          'notice_month_deathday' => (int)$user["notice_month_deathday"],
                                          'notice_deathday_1week_before' => (int)$user["notice_deathday_1week_before"],
                                          'notice_deathday_before' => (int)$user["notice_deathday_before"],
                                          'notice_deathday' => (int)$user["notice_deathday"],
                                          'notice_memorial_3month_before' => (int)$user["notice_memorial_3month_before"],
                                          'notice_memorial_1month_before' => (int)$user["notice_memorial_1month_before"],
                                          'notice_memorial_1week_before' => (int)$user["notice_memorial_1week_before"],
                                          'notice_time' => $user["notice_time"],                
                                          'install_datetime' => $user["install_datetime"],
                                          'entry_datetime' => $user["entry_datetime"]));
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            $this->_db->rollBack();
            return false;
        }
        return true;
    }

    /**
     * 故人引継テーブルにデータを登録する
     * 
     * @param String $dataKey
     * @param String[][] $arrayDeceased
     * @return  boolean true:正常終了 false:エラー
     */
    public function insertTransferDeceased($dataKey, $arrayDeceased) {

        try {
            foreach ($arrayDeceased as $deceased) {
                $sql = "INSERT INTO t_transfer_deceased (
                            data_key,
                            deceased_no,
                            deceased_id,
                            qr_flg,
                            deceased_name,
                            deceased_birthday,
                            deceased_deathday,
                            kyonen_gyonen_flg,
                            death_age,
                            deceased_photo_path,
                            entry_datetime,
                            timestamp
                        )
                        VALUES
                        (
                            :data_key,
                            :deceased_no,
                            :deceased_id,
                            :qr_flg,
                            :deceased_name,
                            :deceased_birthday,
                            :deceased_deathday,
                            :kyonen_gyonen_flg,
                            :death_age,
                            :deceased_photo_path,
                            :entry_datetime,
                            :timestamp
                        )
                    ";
                $this->_db->query($sql, array('data_key' => $dataKey,
                                              'deceased_no' => (int)$deceased["deceased_no"],
                                              'deceased_id' => $deceased["deceased_id"],
                                              'qr_flg' => (int)$deceased["qr_flg"],
                                              'deceased_name' => $deceased["deceased_name"],
                                              'deceased_birthday' => $deceased["deceased_birthday"],
                                              'deceased_deathday' => $deceased["deceased_deathday"],
                                              'kyonen_gyonen_flg' => (int)$deceased["kyonen_gyonen_flg"],
                                              'death_age' => (int)$deceased["death_age"],
                                              'deceased_photo_path' => $deceased["deceased_photo_path"],
                                              'entry_datetime' => $deceased["entry_datetime"],
                                              'timestamp' => $deceased["timestamp"]));
            }
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            $this->_db->rollBack();
            return false;
        }
        return true;
    }
    
    /**
     * 葬儀社引継テーブルにデータを登録する
     * 
     * @param String $dataKey
     * @param String[] $mortician
     * @return  boolean true:正常終了 false:エラー
     */
    public function insertTransferMortician($dataKey, $mortician) {
        try {
            $sql = "INSERT INTO t_transfer_mortician (
                        data_key,
                        mortician_no,
                        mortician_name,
                        mortician_post,
                        mortician_address,
                        mortician_tel,
                        mortician_mail,
                        mortician_url,
                        mortician_memorial_url,
                        entry_datetime
                    )
                    VALUES
                    (
                        :data_key,
                        :mortician_no,
                        :mortician_name,
                        :mortician_post,
                        :mortician_address,
                        :mortician_tel,
                        :mortician_mail,
                        :mortician_url,
                        :mortician_memorial_url,
                        :entry_datetime
                    )
                ";
            $this->_db->query($sql, array('data_key' => $dataKey,
                                          'mortician_no' => $mortician["mortician_no"],
                                          'mortician_name' => $mortician["mortician_name"],
                                          'mortician_post' => $mortician["mortician_post"],
                                          'mortician_address' => $mortician["mortician_address"],
                                          'mortician_tel' => $mortician["mortician_tel"],
                                          'mortician_mail' => $mortician["mortician_mail"],
                                          'mortician_url' => $mortician["mortician_url"],
                                          'mortician_memorial_url' => $mortician["mortician_memorial_url"],
                                          'entry_datetime' => $mortician["entry_datetime"]));
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            $this->_db->rollBack();
            return false;
        }
        return true;
    }
    
    /**
     * 通知先引継テーブルにデータを登録する
     * 
     * @param String $dataKey
     * @param String[][] $arrayNotice
     * @return  boolean true:正常終了 false:エラー
     */
    public function insertTransferNotice($dataKey, $arrayNotice) {
        try {
            foreach ($arrayNotice as $notice) {
                $sql = "INSERT INTO t_transfer_notice (
                            data_key,
                            deceased_no,
                            notice_no,
                            notice_name,
                            notice_address,
                            entry_datetime
                        )
                        VALUES
                        (
                            :data_key,
                            :deceased_no,
                            :notice_no,
                            :notice_name,
                            :notice_address,
                            :entry_datetime
                        )
                    ";
                $this->_db->query($sql, array('data_key' => $dataKey,
                                              'deceased_no' => (int)$notice["deceased_no"],
                                              'notice_no' => (int)$notice["notice_no"],
                                              'notice_name' => $notice["notice_name"],
                                              'notice_address' => comEncryption::encryption($notice["notice_address"]),
                                              'entry_datetime' => $notice["entry_datetime"]));
            }
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            $this->_db->rollBack();
            return false;
        }
        return true;
    }
    
    /**
     * 利用者引継テーブルからデータを取得する
     * 
     * @param   String      $dataKey
     * @return  array       利用者引継情報
     */
    public function selectTransferUser($dataKey) {
        // 対象の利用者引継情報を取得する
        $sql = "SELECT 
                    * 
                FROM
                    t_transfer_user
                WHERE 
                    data_key = :data_key
               ";
        $user = $this->_db->fetchRow($sql, array('data_key' => $dataKey));
        return $user;
    }
    

    /**
     * 故人引継テーブルからデータを取得する
     * 
     * @param   String      $dataKey
     * @return  array       故人引継情報
     */
    public function selectTransferDeceased($dataKey) {
        // 対象の故人引継情報を取得する
        $sql = "SELECT 
                    * 
                FROM
                    t_transfer_deceased
                WHERE 
                    data_key = :data_key
               ";
        $deceased = $this->_db->fetchAll($sql, array('data_key' => $dataKey));
        return $deceased;
    }
    
    /**
     * 葬儀社引継テーブルからデータを取得する
     * 
     * @param   String      $dataKey
     * @return  array       葬儀社引継情報
     */
    public function selectTransferMortician($dataKey) {
        // 対象の葬儀社引継情報を取得する
        $sql = "SELECT 
                    * 
                FROM
                    t_transfer_mortician
                WHERE 
                    data_key = :data_key
               ";
        $mortician = $this->_db->fetchRow($sql, array('data_key' => $dataKey));
        return $mortician;
    }
    
    /**
     * 通知先引継テーブルからデータを取得する
     * 
     * @param   String      $dataKey
     * @return  array       通知先引継情報
     */
    public function selectTransferNotice($dataKey) {
        // 対象の通知先引継情報を取得する
        $sql = "SELECT 
                    * 
                FROM
                    t_transfer_notice
                WHERE 
                    data_key = :data_key
               ";
        $notice = $this->_db->fetchAll($sql, array('data_key' => $dataKey));
        return $notice;
    }
    
    /*
     * 葬儀社情報を取得する
     * 
     * @param   string      $morticianNo    葬儀社No
     * @return  array       対象の葬儀社情報
     */
    public function getMortician($morticianNo) {
        // 対象の葬儀社情報を取得する
        $sql = "SELECT 
                    * 
                FROM
                    m_mortician
                WHERE 
                    mortician_no = :mortician_no
               ";
        $mortician = $this->_db->fetchRow($sql, array('mortician_no' => $morticianNo));
        return $mortician;
    }

    /*
     * 今日配信の通知情報を取得する
     * 
     * @return  array       対象の通知情報
     */
    public function getNoticeInfoToday() {
        $now = new Zend_Date();
        
        // 対象の通知情報を取得する
        $sql = "SELECT 
                    * 
                FROM
                    t_notice_info 
                WHERE 
                    search_category = 0
                    and
                    notice_schedule = :notice_schedule
               ";
        $noticeInfo = $this->_db->fetchAll($sql, array('notice_schedule' => $now->toString('yyyyMMdd')));
        return $noticeInfo;
    }
    
    /*
     * 配信済みの通知情報を取得する
     * 
     * @return  array       対象の通知情報
     */
    public function getNoticeInfoDelivered() {
        // 対象の通知情報を取得する
        $sql = "SELECT 
                    notice_info_no,
                    notice_schedule,
                    entry_method,
                    notice_title,
                    url
                FROM
                    t_notice_info 
                WHERE 
                    notice_flg = 1
                    and
                    search_category = 0
                ORDER BY
                    notice_schedule DESC
               ";
        $noticeInfo = $this->_db->fetchAll($sql);
        return $noticeInfo;
    }

    /*
     * 配信済みの通知情報をデバイストークンを指定して取得する
     * @param deviceToken デバイストークン
     * @return noticeInfo 通知情報配列 
     */
    public function getNoticeInfoDeliveredByToken($deviceToken) {
        // 通知情報を取得する
        $sql = "SELECT 
                    *
                FROM
                    c_notice_info_list
                WHERE 
                    notice_flg = 1 AND
                    deceased_id IN (
                        SELECT
                            deceased_id
                        FROM
                            t_ios_device_token
                        WHERE
                            device_token = :device_token
                    )
                ORDER BY
                    notice_schedule DESC";
        $noticeInfo = $this->_db->fetchAll($sql, array('device_token' => $deviceToken));

        return $noticeInfo;
    }
    
    /*
     * 配信済みの通知情報をレジストレーションIDを指定して取得する
     * @param registrationID レジストレーションID
     * @return noticeInfo 通知情報配列 
     */
    public function getNoticeInfoDeliveredByRegID($registrationID) {
        // 通知情報を取得する
        $sql = "SELECT
                    *
                FROM
                    c_notice_info_list
                WHERE
                    notice_flg = 1 AND
                    deceased_id IN (
                        SELECT
                            deceased_id
                        FROM
                            t_android_registration_id
                        WHERE
                            registration_id = :registration_id
                    )
                ORDER BY
                    notice_schedule DESC
                ";
        $noticeInfo = $this->_db->fetchAll($sql, array('registration_id' => $registrationID));

        return $noticeInfo;
    }

    /*
     * 通知日を指定して通知情報を取得する
     * 
     * @return  notceInfos      通知情報配列
     */
    public function getNoticeInfo($noticeSchedule) {
        // 対象の通知情報を取得する
        $sql = "SELECT 
                    notice_info_no,
                    entry_method,
                    url
                FROM
                    t_notice_info 
                WHERE 
                    notice_schedule = :notice_schedule
               ";
        $noticeInfo = $this->_db->fetchAll($sql, array('notice_schedule' => $noticeSchedule));
        return $noticeInfo;
    }

    /*
     * 通知日とデバイストークンを指定して通知情報を取得する
     *
     * @return noticeInfo 通知情報配列
     */
    public function getNoticeInfoByToken($noticeSchedule, $deviceToken) {
        // 通知情報を取得する
        $sql = "SELECT notice_info_no,
                       entry_method,
                       url
                FROM t_notice_info
                WHERE  notice_schedule = :notice_schedule AND
                       notice_info_no IN (SELECT notice_info_no
                                          FROM t_notice_target
                                          WHERE deceased_id IN (SELECT deceased_id
                                                                FROM t_ios_device_token
                                                                WHERE device_token = :device_token))";
        $noticeInfo = $this->_db->fetchAll($sql, array('notice_schedule' => $noticeSchedule,
                                                       'device_token'    => $deviceToken));
        return $noticeInfo;
    }

    /*
     * 通知日とデバイストークンを指定して通知情報＋個人情報を取得する（法要通知（初七日））
     *
     * @return noticeInfo 通知情報＋個人情報配列
     */
    public function getNoticeHoyoInfoAndDeceasedID($noticeSchedule,$deviceToken,$DeliveryDate) {
        $sql = "SELECT 
                    c.*, a.*
                FROM 
                    t_notice_info AS c, c_notice_hoyo_info_list AS a
                WHERE 
                    c.notice_schedule   = :notice_schedule
                AND
                    a.deceased_deathday = :deceased_deathday                 
                AND
                    a.deceased_id IN (
                        SELECT
                            deceased_id
                        FROM
                            c_ios_device_token
                        WHERE
                            device_token = :device_token
                            and
                            allow_push = 1
                    )
              ";
              
        $noticeInfo = $this->_db->fetchAll($sql, array( 'notice_schedule'   => $noticeSchedule,
                                                        'deceased_deathday' => $DeliveryDate,
                                                        'device_token'      => $deviceToken));
        return $noticeInfo;
    }


    /*
     * 通知日とデバイストークンを指定して通知情報＋個人情報を取得する
     *
     * @return noticeInfo 通知情報＋個人情報配列
     */
    public function getNoticeInfoAndDeceasedID($noticeSchedule, $deviceToken) {
        $sql = "SELECT
                    *
                FROM
                    c_notice_info_list
                WHERE
                    notice_schedule = :notice_schedule
                    and
                    deceased_id IN (
                        SELECT
                            deceased_id
                        FROM
                            c_ios_device_token
                        WHERE
                            device_token = :device_token
                            and
                            allow_push = 1
                    )";
        $noticeInfo = $this->_db->fetchAll($sql, array('notice_schedule' => $noticeSchedule,
                                                       'device_token'    => $deviceToken));
        return $noticeInfo;
    }

    /*
     * デバイストークンが登録済みかチェックする
     * 
     * @param   string  $deviceToken    デバイストークン
     * @param   string  $deceasedId     故人ID
     * @return  boolean true:登録済み false:未登録
     */
    public function checkDeviceToken($deviceToken, $deceasedId = NULL) {
        if (is_null($deceasedId)) {
            $sql = "SELECT * FROM
                        t_ios_device_token 
                    WHERE 
                        device_token = :device_token
                ";
            $deviceToken = $this->_db->fetchRow($sql, array('device_token' => $deviceToken));
        } else {
            $sql = "SELECT * FROM 
                        t_ios_device_token 
                    WHERE 
                        device_token = :device_token
                    AND
                        deceased_id = :deceased_id
                ";
            $deviceToken = $this->_db->fetchRow($sql, array('device_token' => $deviceToken,
                                                            'deceased_id' => $deceasedId));
        }
        if (empty($deviceToken)) {
            //空の場合falseを返す
            return false;
        } else {
            //データが存在する場合trueを返す
            return true;
        }
        return true;
    }

    /**
     * iOSデバイストークンテーブルにデータを登録する
     * 
     * @param String $deviceToken       デバイストークン
     * @param String $deceasedId        故人ID
     * @return  boolean true:正常終了 false:エラー
     */
    public function insertDeviceToken($deviceToken, $deceasedId = NULL) {
        try {
            if (is_null($deceasedId)) {
                $sql = "INSERT INTO t_ios_device_token (
                            device_token
                        )
                        VALUES
                        (
                            :device_token
                        )
                    ";
                $this->_db->query($sql, array('device_token' => $deviceToken));
            } else {
                $sql = "INSERT INTO t_ios_device_token (
                            device_token,
                            deceased_id
                        )
                        VALUES
                        (
                            :device_token,
                            :deceased_id
                        )
                    ";
                $this->_db->query($sql, array('device_token' => $deviceToken,
                                              'deceased_id' => $deceasedId));
            }
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            $this->_db->rollBack();
            return false;
        }
        return true;
    }

    /*
     * デバイストークンを取得する
     * 
     * @return  array       デバイストークンリスト
     */
    public function selectDeviceToken() {
        $sql = "SELECT device_token FROM t_ios_device_token";
        $deviceToken = $this->_db->fetchAll($sql);
        return $deviceToken;
    }
    
    /*
     * レジストレーションIDが登録済みかチェックする
     * 
     * @param   string  $registrationID レジストレーションID
     * @param   string  $deceasedId     故人ID
     * @return  boolean true:登録済み false:未登録
     */
    public function checkRegistrationID($registrationId, $deceasedId) {
        $sql = "SELECT * FROM 
                    t_android_registration_id 
                WHERE 
                    registration_id = :registration_id
                AND
                    deceased_id = :deceased_id
               ";
        $record = $this->_db->fetchRow($sql, array('registration_id' => $registrationId,
                                                   'deceased_id'     => $deceasedId));
        
        if (empty($record)) {
            //空の場合falseを返す
            return false;
        } else {
            //データが存在する場合trueを返す
            return true;
        }
    }
    
    /**
     * androidレジストレーションテーブルにデータを登録する
     * 
     * @param String $registrationID レジストレーションID
     * @param String $deceasedId     故人ID
     * @return  boolean true:正常終了 false:エラー
     */
    public function insertRegistrationID($registrationId, $deceasedId) {
        try {
            $sql = "INSERT INTO t_android_registration_id (
                        registration_id,
                        deceased_id
                    )
                    VALUES
                    (
                        :registration_id,
                        :deceased_id
                    )
                ";
            $this->_db->query($sql, array('registration_id' => $registrationId,
                                          'deceased_id'     => $deceasedId));
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        
        return true;
    }
    
    /*
     * レジストレーションIDを取得する
     * 
     * @return  array レジストレーションIDリスト
     */
    public function selectRegistrationID() {
        $sql = "SELECT registration_id FROM t_android_registration_id";
        $registrationID = $this->_db->fetchAll($sql);
        return $registrationID;
    }

    /*
     * レジストレーションIDを更新する
     *
     * @return  boolean true:正常終了 false:エラー
     */
    public function updateRegistrationID($old_id, $update_id) {
        try {
            $sql = "UPDATE t_android_registration_id SET registration_id=:update_id 
                    WHERE registration_id=:old_id";
            $this->_db->query($sql, array('update_id' => $update_id,
                                          'old_id'    => $old_id));
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }

        return true;
    }

    /*
     * お知らせ番号を指定してお知らせ情報を取得する
     *
     * @param $notice_no お知らせ番号
     * @return 結果オブジェクト
     */
    public function getNoticeInfoByNo($notice_no) {
        $sql = "SELECT * FROM t_notice_info WHERE notice_info_no=:notice_no";
        return $this->_db->fetchAll($sql, array('notice_no' => $notice_no));
    }
}
