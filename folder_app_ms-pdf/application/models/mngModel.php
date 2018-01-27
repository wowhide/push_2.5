<?php

/**
 * 法要アプリプレミアム版管理システムのDBアクセスを制御するクラス
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

class mngModel {
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

    /**
     * ログイン成功時に管理者マスタの最終ログイン日時を更新する
     *
     * @param   string  $id         manager_id
     * @return  boolean　成否
     */
    public function updateLastLoginDateTime($id)
    {
        $now = new Zend_Date();

        try {
            $sql = "UPDATE m_manager SET
                        last_login_datetime = :last_login_datetime
                    WHERE
                        manager_id = :manager_id
            ";

            $this->_db->query($sql, array(
                'last_login_datetime' => $now,
                'manager_id' => $id)
            );
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }

    // ========　    ＱＲ読込かつ通知許可総数    ========== //

    /**
     * Android_端末識別番号取得
     *
     * @param   String   deceasedId      故人番号
     */
     public function getAndroidIdentificationNumberList(){
       $sql = "SELECT
            *
        FROM
            t_android_registration_id
        ";
        $memberList = $this->_db->fetchAll($sql);
        return $memberList;
     }


    /**
     * iOS_端末識別番号取得
     *
     * @param   String   deceasedId      故人番号
     */
     public function getIosIdentificationNumberList(){
       $sql = "SELECT
            *
        FROM
            t_ios_device_token
        ";
         $memberList = $this->_db->fetchAll($sql);
        return $memberList;
     }


    /**
     * 全ての端末識別番号取得
     *
     * @param   String   deceasedId      故人番号
     */
     public function getAllIdentificationNumberList(){
       $sql = "(SELECT
                *
            FROM
                t_android_registration_id
                )

        UNION ALL

            (SELECT
                *
            FROM
                t_ios_device_token

            )

            ORDER BY
                timestamp DESC
        ";
         $memberList = $this->_db->fetchAll($sql);
        return $memberList;
     }

    // ========　    定期通知    ========== //

  /**
    * 通知情報の登録の有無を取得する（追善法要）
    *
    * @return  array   通知情報リスト
    */
    public function getNoticeInfodayafterdeathEntryList($noticeType)
    {
        $sql = "SELECT
                    *
                FROM
                    t_notice_info
                WHERE
                    notice_type = :notice_type
                ";
        $notcieInfoList = $this->_db->fetchRow($sql, array('notice_type' => $noticeType));

        if (empty($notcieInfoList)) {
            //空の場合falseを返す
            return false;
        } else {
            //データが存在する場合trueを返す
            return true;
        }
        return true;
    }

    // ========     法要通知    ========== //

    /**
     * 通知情報を登録する（追善法要）
     *
     * @param   array   $noticeInfo    通知情報情報
     * @return  boolean　成否
     */
    public function insertNoticeHoyoInfo($noticeInfo)
    {
        $now = new Zend_Date();

        //日付の様式を変更するためにZend_Date型の変数を準備
        // $date = new Zend_Date($noticeInfo['notice_schedule']);

        try {
            if ($noticeInfo['entry_method'] == ENTRY_METHOD_INPUT) {
                $sql = "INSERT INTO t_notice_info (
                            notice_schedule,
                            entry_method,
                            notice_title,
                            template_id,
                            notice_text,
                            image_existence_flg,
                            notice_type,
                            url,
                            entry_datetime,
                            search_category,
                            charge_name,
                            hall_name,
                            search_name,
                            search_year,
                            search_month,
                            search_day,
                            death_month,
                            memorial_year,
                            memorial_month,
                            memorial_event
                        )
                        VALUES
                        (
                            :notice_schedule,
                            :entry_method,
                            :notice_title,
                            :template_id,
                            :notice_text,
                            :image_existence_flg,
                            :notice_type,
                            :url,
                            :entry_datetime,
                            :search_category,
                            :charge_name,
                            :hall_name,
                            :search_name,
                            :search_year,
                            :search_month,
                            :search_day,
                            :death_month,
                            :memorial_year,
                            :memorial_month,
                            :memorial_event
                        )
                ";

                $this->_db->query($sql, array(
                    'notice_schedule' => $noticeInfo['notice_schedule'],
                    'entry_method' => $noticeInfo['entry_method'],
                    'notice_title' => $noticeInfo['notice_title'],
                    'template_id' => $noticeInfo['template_id'],
                    'notice_text' => $noticeInfo['notice_text'],
                    'image_existence_flg' => $noticeInfo['image_existence_flg'],
                    'notice_type' => $noticeInfo['notice_type'],
                    'url' => $noticeInfo['url'],
                    'entry_datetime' => $now,
                    'search_category' => 0,
                    'charge_name' => "",
                    'hall_name' =>  "",
                    'search_name' =>  "",
                    'search_year' =>  "",
                    'search_month' =>  "",
                    'search_day' =>  "",
                    'death_month' =>  "",
                    'memorial_year' => "",
                    'memorial_month' =>  "",
                    'memorial_event' =>  "")
                );
            } else {
                $sql = "INSERT INTO t_notice_info (
                            notice_schedule,
                            entry_method,
                            notice_title,
                            notice_type,
                            url,
                            entry_datetime,
                            search_category,
                            charge_name,
                            hall_name,
                            search_name,
                            search_year,
                            search_month,
                            search_day,
                            death_month,
                            memorial_year,
                            memorial_month,
                            memorial_event
                        )
                        VALUES
                        (
                            :notice_schedule,
                            :entry_method,
                            :notice_title,
                            :notice_type,
                            :url,
                            :entry_datetime,
                            :search_category,
                            :charge_name,
                            :hall_name,
                            :search_name,
                            :search_year,
                            :search_month,
                            :search_day,
                            :death_month,
                            :memorial_year,
                            :memorial_month,
                            :memorial_event
                        )
                ";

                $this->_db->query($sql, array(
                    'notice_schedule' => $noticeInfo['notice_schedule'],
                    'entry_method' => $noticeInfo['entry_method'],
                    'notice_title' => $noticeInfo['notice_title'],
                    'notice_type' => $noticeInfo['notice_type'],
                    'url' => $noticeInfo['url'],
                    'entry_datetime' => $now,
                    'search_category' => 0,
                    'charge_name' =>  "",
                    'hall_name' =>  "",
                    'search_name' =>  "",
                    'search_year' =>  "",
                    'search_month' =>  "",
                    'search_day' =>  "",
                    'death_month' =>  "",
                    'memorial_year' =>  "",
                    'memorial_month' => "",
                    'memorial_event' =>  "")
                );
            }

            $noticeInfoNo = $this->getLastNoticeInfoNo();
            $this->insertNoticeTarget($noticeInfoNo['notice_info_no'], $noticeInfo['notice_target']);
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }

    /**
     * 通知情報を更新する（追善法要）
     *
     * @param   array   $noticeInfo    通知情報情報
     */
    public function updateNoticeHoyoInfo($noticeInfo)
    {
        //日付の様式を変更するためにZend_Date型の変数を準備
        // $date = new Zend_Date($noticeInfo['notice_schedule']);

        try {
            if ($noticeInfo['entry_method'] == ENTRY_METHOD_INPUT) {
                $sql = "UPDATE t_notice_info SET
                            notice_schedule = :notice_schedule,
                            entry_method = :entry_method,
                            notice_title = :notice_title,
                            template_id = :template_id,
                            notice_text = :notice_text,
                            notice_type = :notice_type,
                            image_existence_flg = :image_existence_flg,
                            url = :url,
                            search_category = :search_category,
                            charge_name = :charge_name,
                            hall_name = :hall_name,
                            search_name = :search_name,
                            search_year = :search_year,
                            search_month = :search_month,
                            search_day = :search_day,
                            death_month = :death_month,
                            memorial_year = :memorial_year,
                            memorial_month = :memorial_month,
                            memorial_event = :memorial_event
                        WHERE
                            notice_info_no = :notice_info_no
                        ";

                $this->_db->query($sql, array(
                    'notice_schedule' => $noticeInfo['notice_schedule'],
                    'entry_method' => $noticeInfo['entry_method'],
                    'notice_title' => $noticeInfo['notice_title'],
                    'template_id' => $noticeInfo['template_id'],
                    'notice_text' => $noticeInfo['notice_text'],
                    'notice_type' => $noticeInfo['notice_type'],
                    'image_existence_flg' => $noticeInfo['image_existence_flg'],
                    'url' => $noticeInfo['url'],
                    'search_category' => $noticeInfo['selected_category'],
                    'charge_name' => $noticeInfo['charge_name'],
                    'hall_name' => $noticeInfo['hall_name'],
                    'search_name' => $noticeInfo['search_name'],
                    'search_year' => $noticeInfo['search_year'],
                    'search_month' => $noticeInfo['search_month'],
                    'search_day' => $noticeInfo['search_day'],
                    'death_month' => $noticeInfo['death_month'],
                    'memorial_year' => $noticeInfo['memorial_year'],
                    'memorial_month' => $noticeInfo['memorial_month'],
                    'memorial_event' => $noticeInfo['memorial_event'],
                    'notice_info_no' => $noticeInfo['notice_info_no'])
                );
            } else {
                $sql = "UPDATE t_notice_info SET
                            notice_schedule = :notice_schedule,
                            entry_method = :entry_method,
                            notice_title = :notice_title,
                            template_id = '',
                            notice_text = '',
                            notice_type = '',
                            image_existence_flg = 0,
                            url = :url,
                            search_category = :search_category,
                            charge_name = :charge_name,
                            hall_name = :hall_name,
                            search_name = :search_name,
                            search_year = :search_year,
                            search_month = :search_month,
                            search_day = :search_day,
                            death_month = :death_month,
                            memorial_year = :memorial_year,
                            memorial_month = :memorial_month,
                            memorial_event = :memorial_event
                        WHERE
                            notice_info_no = :notice_info_no
                        ";

                $this->_db->query($sql, array(
                    'notice_schedule'   => $noticeInfo['notice_schedule'],
                    'entry_method'      => $noticeInfo['entry_method'],
                    'notice_title'      => $noticeInfo['notice_title'],
                    'notice_type'       => $noticeInfo['notice_type'],
                    'url'               => $noticeInfo['url'],
                    'search_category'   => $noticeInfo['selected_category'],
                    'charge_name'       => $noticeInfo['charge_name'],
                    'hall_name'         => $noticeInfo['hall_name'],
                    'search_name'       => $noticeInfo['search_name'],
                    'search_year'       => $noticeInfo['search_year'],
                    'search_month'      => $noticeInfo['search_month'],
                    'search_day'        => $noticeInfo['search_day'],
                    'death_month'       => $noticeInfo['death_month'],
                    'memorial_year'     => $noticeInfo['memorial_year'],
                    'memorial_month'    => $noticeInfo['memorial_month'],
                    'memorial_event'    => $noticeInfo['memorial_event'],
                    'notice_info_no'    => $noticeInfo['notice_info_no'])
                );
            }

            //通知あて先テーブルを更新
            $this->deleteNoticeTarget($noticeInfo['notice_info_no']);
            $this->insertNoticeTarget($noticeInfo['notice_info_no'], $noticeInfo['notice_target']);
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }


    // ========     通常通知    ========== //

    /**
     * 通知情報を登録する
     *
     * @param   array   $noticeInfo    通知情報情報
     * @return  boolean　成否
     */
    public function insertNoticeInfo($noticeInfo)
    {
        $now = new Zend_Date();

        //日付の様式を変更するためにZend_Date型の変数を準備
        $date = new Zend_Date($noticeInfo['notice_schedule']);

        try {
            if ($noticeInfo['entry_method'] == ENTRY_METHOD_INPUT) {
                $sql = "INSERT INTO t_notice_info (
                            notice_schedule,
                            entry_method,
                            notice_title,
                            template_id,
                            notice_text,
                            image_existence_flg,
                            url,
                            entry_datetime,
                            search_category,
                            charge_name,
                            hall_name,
                            search_name,
                            search_year,
                            search_month,
                            search_day,
                            death_month,
                            memorial_year,
                            memorial_month,
                            memorial_event
                        )
                        VALUES
                        (
                            :notice_schedule,
                            :entry_method,
                            :notice_title,
                            :template_id,
                            :notice_text,
                            :image_existence_flg,
                            :url,
                            :entry_datetime,
                            :search_category,
                            :charge_name,
                            :hall_name,
                            :search_name,
                            :search_year,
                            :search_month,
                            :search_day,
                            :death_month,
                            :memorial_year,
                            :memorial_month,
                            :memorial_event
                        )
                ";

                $this->_db->query($sql, array(
                    'notice_schedule' => $date->toString('yyyyMMdd'),
                    'entry_method' => $noticeInfo['entry_method'],
                    'notice_title' => $noticeInfo['notice_title'],
                    'template_id' => $noticeInfo['template_id'],
                    'notice_text' => $noticeInfo['notice_text'],
                    'image_existence_flg' => $noticeInfo['image_existence_flg'],
                    'url' => $noticeInfo['url'],
                    'entry_datetime' => $now,
                    'search_category' => $noticeInfo['selected_category'],
                    'charge_name' => $noticeInfo['charge_name'],
                    'hall_name' => $noticeInfo['hall_name'],
                    'search_name' => $noticeInfo['search_name'],
                    'search_year' => $noticeInfo['search_year'],
                    'search_month' => $noticeInfo['search_month'],
                    'search_day' => $noticeInfo['search_day'],
                    'death_month' => $noticeInfo['death_month'],
                    'memorial_year' => $noticeInfo['memorial_year'],
                    'memorial_month' => $noticeInfo['memorial_month'],
                    'memorial_event' => $noticeInfo['memorial_event'])
                );
            } else {
                $sql = "INSERT INTO t_notice_info (
                            notice_schedule,
                            entry_method,
                            notice_title,
                            url,
                            entry_datetime,
                            search_category,
                            charge_name,
                            hall_name,
                            search_name,
                            search_year,
                            search_month,
                            search_day,
                            death_month,
                            memorial_year,
                            memorial_month,
                            memorial_event
                        )
                        VALUES
                        (
                            :notice_schedule,
                            :entry_method,
                            :notice_title,
                            :url,
                            :entry_datetime,
                            :search_category,
                            :charge_name,
                            :hall_name,
                            :search_name,
                            :search_year,
                            :search_month,
                            :search_day,
                            :death_month,
                            :memorial_year,
                            :memorial_month,
                            :memorial_event
                        )
                ";

                $this->_db->query($sql, array(
                    'notice_schedule' => $date->toString('yyyyMMdd'),
                    'entry_method' => $noticeInfo['entry_method'],
                    'notice_title' => $noticeInfo['notice_title'],
                    'url' => $noticeInfo['url'],
                    'entry_datetime' => $now,
                    'search_category' => $noticeInfo['selected_category'],
                    'charge_name' => $noticeInfo['charge_name'],
                    'hall_name' => $noticeInfo['hall_name'],
                    'search_name' => $noticeInfo['search_name'],
                    'search_year' => $noticeInfo['search_year'],
                    'search_month' => $noticeInfo['search_month'],
                    'search_day' => $noticeInfo['search_day'],
                    'death_month' => $noticeInfo['death_month'],
                    'memorial_year' => $noticeInfo['memorial_year'],
                    'memorial_month' => $noticeInfo['memorial_month'],
                    'memorial_event' => $noticeInfo['memorial_event'])
                );
            }

            $noticeInfoNo = $this->getLastNoticeInfoNo();
            $this->insertNoticeTarget($noticeInfoNo['notice_info_no'], $noticeInfo['notice_target']);
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }




    /**
     * 最後に登録した通知情報Noの値を取得する
     *
     * @return   int   通知情報Noの値
     */
    public function getLastNoticeInfoNo()
    {
        $sql = "SELECT LAST_INSERT_ID() AS 'notice_info_no'";
        $noticeInfoNo = $this->_db->fetchRow($sql);
        return $noticeInfoNo;
    }

    /**
     * 通知情報を全件取得する
     * 全ての通知情報を取得する
     *
     * @return  array   通知情報リスト
     */
    public function getNoticeInfoList()
    {
        $sql = "SELECT
                    *
                FROM
                    t_notice_info
                WHERE
                    notice_type = 0
                ORDER BY
                    notice_schedule DESC
                ";
        $notcieInfoList = $this->_db->fetchAll($sql);
        return $notcieInfoList;
    }

    /**
     * 通知情報を取得する(追善法要)
     * 通知情報種類を条件に通知情報を取得する
     *
     * @return  array   通知情報
     */
    public function getNoticeHoyoInfo($noticeType)
    {
        //お知らせ情報
        $sql = "SELECT
                    *
                FROM
                    t_notice_info
                WHERE
                    notice_type = :notice_type
                ";
        $noticeInfo = $this->_db->fetchRow($sql, array(':notice_type' => $noticeType));

        return $noticeInfo;
    }


    /**
     * 通知情報を取得する
     * 通知情報Noを条件に通知情報を取得する
     *
     * @return  array   通知情報
     */
    public function getNoticeInfo($noticeInfoNo)
    {
        //お知らせ情報
        $sql = "SELECT
                    *
                FROM
                    t_notice_info
                WHERE
                    notice_info_no = :notice_info_no
                ";
        $noticeInfo = $this->_db->fetchRow($sql, array(':notice_info_no' => $noticeInfoNo));
        //通知先故人リスト
        $sql = "SELECT
                    deceased_id
                FROM
                    t_notice_target
                WHERE
                    notice_info_no = :notice_info_no
                ";
        $result = $this->_db->fetchAll($sql, array(':notice_info_no' => $noticeInfoNo));
        $targetList = array();
        foreach ($result as $value) $targetList[] = $value['deceased_id'];
        $noticeInfo['notice_target'] = $targetList;
        $noticeInfo['selected_category'] = $noticeInfo['search_category'];

        return $noticeInfo;
    }

    /**
     * 通知情報を更新する
     *
     * @param   array   $noticeInfo    通知情報情報
     */
    public function updateNoticeInfo($noticeInfo)
    {
        //日付の様式を変更するためにZend_Date型の変数を準備
        $date = new Zend_Date($noticeInfo['notice_schedule']);

        try {
            if ($noticeInfo['entry_method'] == ENTRY_METHOD_INPUT) {
                $sql = "UPDATE t_notice_info SET
                            notice_schedule = :notice_schedule,
                            entry_method = :entry_method,
                            notice_title = :notice_title,
                            template_id = :template_id,
                            notice_text = :notice_text,
                            image_existence_flg = :image_existence_flg,
                            url = :url,
                            search_category = :search_category,
                            charge_name = :charge_name,
                            hall_name = :hall_name,
                            search_name = :search_name,
                            search_year = :search_year,
                            search_month = :search_month,
                            search_day = :search_day,
                            death_month = :death_month,
                            memorial_year = :memorial_year,
                            memorial_month = :memorial_month,
                            memorial_event = :memorial_event
                        WHERE
                            notice_info_no = :notice_info_no
                        ";

                $this->_db->query($sql, array(
                    'notice_schedule' => $date->toString('yyyyMMdd'),
                    'entry_method' => $noticeInfo['entry_method'],
                    'notice_title' => $noticeInfo['notice_title'],
                    'template_id' => $noticeInfo['template_id'],
                    'notice_text' => $noticeInfo['notice_text'],
                    'image_existence_flg' => $noticeInfo['image_existence_flg'],
                    'url' => $noticeInfo['url'],
                    'search_category' => $noticeInfo['selected_category'],
                    'charge_name' => $noticeInfo['charge_name'],
                    'hall_name' => $noticeInfo['hall_name'],
                    'search_name' => $noticeInfo['search_name'],
                    'search_year' => $noticeInfo['search_year'],
                    'search_month' => $noticeInfo['search_month'],
                    'search_day' => $noticeInfo['search_day'],
                    'death_month' => $noticeInfo['death_month'],
                    'memorial_year' => $noticeInfo['memorial_year'],
                    'memorial_month' => $noticeInfo['memorial_month'],
                    'memorial_event' => $noticeInfo['memorial_event'],
                    'notice_info_no' => $noticeInfo['notice_info_no'])
                );
            } else {
                $sql = "UPDATE t_notice_info SET
                            notice_schedule = :notice_schedule,
                            entry_method = :entry_method,
                            notice_title = :notice_title,
                            template_id = '',
                            notice_text = '',
                            image_existence_flg = 0,
                            url = :url,
                            search_category = :search_category,
                            charge_name = :charge_name,
                            hall_name = :hall_name,
                            search_name = :search_name,
                            search_year = :search_year,
                            search_month = :search_month,
                            search_day = :search_day,
                            death_month = :death_month,
                            memorial_year = :memorial_year,
                            memorial_month = :memorial_month,
                            memorial_event = :memorial_event
                        WHERE
                            notice_info_no = :notice_info_no
                        ";

                $this->_db->query($sql, array(
                    'notice_schedule' => $date->toString('yyyyMMdd'),
                    'entry_method' => $noticeInfo['entry_method'],
                    'notice_title' => $noticeInfo['notice_title'],
                    'url' => $noticeInfo['url'],
                    'search_category' => $noticeInfo['selected_category'],
                    'charge_name' => $noticeInfo['charge_name'],
                    'hall_name' => $noticeInfo['hall_name'],
                    'search_name' => $noticeInfo['search_name'],
                    'search_year' => $noticeInfo['search_year'],
                    'search_month' => $noticeInfo['search_month'],
                    'search_day' => $noticeInfo['search_day'],
                    'death_month' => $noticeInfo['death_month'],
                    'memorial_year' => $noticeInfo['memorial_year'],
                    'memorial_month' => $noticeInfo['memorial_month'],
                    'memorial_event' => $noticeInfo['memorial_event'],
                    'notice_info_no' => $noticeInfo['notice_info_no'])
                );
            }

            //通知あて先テーブルを更新
            $this->deleteNoticeTarget($noticeInfo['notice_info_no']);
            $this->insertNoticeTarget($noticeInfo['notice_info_no'], $noticeInfo['notice_target']);
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }

    /**
     * 通知情報を削除する
     *
     * @param   String   $noticeInfoNo    通知情報No
     */
    public function deleteNoticeInfo($noticeInfoNo)
    {
        try {
            //通知情報テーブル
            $sql = "DELETE FROM
                        t_notice_info
                    WHERE
                        notice_info_no = :notice_info_no
                    ";

            $this->_db->query($sql, array('notice_info_no' => $noticeInfoNo));

            //通知あて先テーブル
            $sql = "DELETE FROM
                        t_notice_target
                    WHERE
                        notice_info_no = :notice_info_no
                    ";
            $this->_db->query($sql, array('notice_info_no' => $noticeInfoNo));

        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }

    /**
     * 通知あて先テーブルから削除する
     * @param noticeInfoNo 通地番号
     */
    public function deleteNoticeTarget($noticeInfoNo)
    {
        try {
            $sql = "DELETE FROM
                        t_notice_target
                    WHERE
                        notice_info_no = :notice_info_no
                    ";
            $this->_db->query($sql, array('notice_info_no' => $noticeInfoNo));
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }

    /**
     * 通知あて先テーブルに登録する
     *
     * @param noticeInfoNo 通知番号
     * @param deceasedList 故人IDの配列
     */
    public function insertNoticeTarget($noticeInfoNo, $deceasedList)
    {
        if(0 < count($deceasedList)){
            $sql = "INSERT INTO t_notice_target (notice_info_no, deceased_id) VALUES ";
            $values = array();
            foreach ($deceasedList as $deceasedId) {
                $values[] = "('" . $noticeInfoNo . "', '" . $deceasedId . "')";
            }
            $sql .= implode(',', $values);
            $this->_db->query($sql);
        }
    }

    /**
     * テンプレート情報を取得する
     *
     * @param integer $templateId   テンプレート番号
     * @return array   テンプレート情報
     */
    public function getTemplate($templateId){
        $sql = "SELECT
                    *
                FROM
                    t_notice_template
                WHERE
                    template_id = :template_id
                ";
        $template = $this->_db->fetchRow($sql, array('template_id' => $templateId));

        return $template;
    }

    /**
     * 担当者情報を全件取得する
     *
     * @return  array   担当者情報リスト
     */
    public function getChargeList()
    {
        $sql = "SELECT
                    *
                FROM
                    m_charge
                ORDER BY
                    charge_no ASC
                ";
        $chargeList = $this->_db->fetchAll($sql);
        return $chargeList;
    }

    /**
     * 故人様登録時に使用した担当者情報を取得する
     *
     * @return  array   担当者情報リスト
     */
    public function getChargeListUsed()
    {
        $sql = "SELECT DISTINCT
                    charge_name
                FROM
                    m_deceased
                ORDER BY
                    charge_name
                ";
        $chargeList = $this->_db->fetchAll($sql);
        return $chargeList;
    }

    /**
     * 担当者情報を追加する
     *
     * @param   array   $chargeName    担当者名
     */
    public function addCharge($chargeName)
    {
        try {
            $sql = "INSERT INTO m_charge (
                        charge_name
                    )
                    VALUES
                    (
                        :charge_name
                    )
            ";
            $this->_db->query($sql, array('charge_name' => $chargeName));
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }

    /**
     * 担当者情報を削除する
     *
     * @param   String   $chargeNo      担当者No
     */
    public function deleteCharge($chargeNo)
    {
        try {
            $sql = "DELETE FROM
                        m_charge
                    WHERE
                        charge_no = :charge_no
                    ";
            $this->_db->query($sql, array('charge_no' => $chargeNo));
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }

    /**
     * 担当者情報を削除する
     *
     * @param   String   $id        管理者ID
     * @param   String   $password  新しいパスワード
     *
     */
    public function updatePassword($id, $password)
    {
        try {
            $sql = "UPDATE m_manager SET
                        manager_password = :manager_password
                    WHERE
                        manager_id = :manager_id
            ";

            $this->_db->query($sql, array(
                'manager_password' => $password,
                'manager_id' => $id)
            );
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }

    /**
     * 故人情報を取得する
     *
     * @param   String   $searchFrom    検索条件：発注日From
     * @param   String   $searchTo      検索条件：発注日To
     * @return  array   故人情報リスト
     */
    public function getDeceasedList($searchFrom, $searchTo, $searchDeceasedName, $searchPersonInCharge)
    {
        //日付文字列をSQL条件用に編集
        if (strlen($searchFrom) > 0) {
            $searchFrom = date('Y-m-d 00:00:00', strtotime($searchFrom));
        }
        if (strlen($searchTo) > 0) {
            $searchTo = date('Y-m-d 23:59:59', strtotime($searchTo));
        }

        //From:○, To:○, deceased_name:○, charge_name:○
        if (strlen($searchFrom) > 0
            && strlen($searchTo) > 0
            && strlen($searchDeceasedName) > 0
            && strlen($searchPersonInCharge) > 0) {

            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    WHERE
                        issue_datetime >= :search_from
                    AND
                        issue_datetime <= :search_to
                    AND
                        deceased_name = :searchDeceasedName
                    AND
                        charge_name = :searchPersonInCharge
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql, array(':search_from' => $searchFrom,
                                                             ':search_to'   => $searchTo,
                                                             ':searchDeceasedName'   => $searchDeceasedName,
                                                             ':searchPersonInCharge'   => $searchPersonInCharge));

        //From:○, To:○, deceased_name:○, charge_name:X
        } elseif (strlen($searchFrom) > 0
            && strlen($searchTo) > 0
            && strlen($searchDeceasedName) > 0) {

            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    WHERE
                        issue_datetime >= :search_from
                    AND
                        issue_datetime <= :search_to
                    AND
                        deceased_name = :searchDeceasedName
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql, array(':search_from' => $searchFrom,
                                                             ':search_to'   => $searchTo,
                                                             ':searchDeceasedName'   => $searchDeceasedName));


        //From:○, To:○, deceased_name:X, charge_name:○
        }elseif (strlen($searchFrom) > 0
            && strlen($searchTo) > 0
            && strlen($searchPersonInCharge) > 0) {

            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    WHERE
                        issue_datetime >= :search_from
                    AND
                        issue_datetime <= :search_to
                    AND
                        charge_name = :searchPersonInCharge
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql, array(':search_from' => $searchFrom,
                                                             ':search_to'   => $searchTo,
                                                             ':searchPersonInCharge'   => $searchPersonInCharge));

        //From:○, To:X, deceased_name:○, charge_name:○
        }elseif (strlen($searchFrom) > 0
            && strlen($searchDeceasedName) > 0
            && strlen($searchPersonInCharge) > 0) {

            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    WHERE
                        issue_datetime >= :search_from
                    AND
                        deceased_name = :searchDeceasedName
                    AND
                        charge_name = :searchPersonInCharge
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql, array(':search_from' => $searchFrom,
                                                             ':searchDeceasedName'   => $searchDeceasedName,
                                                             ':searchPersonInCharge'   => $searchPersonInCharge));

        //From:X, To:○, deceased_name:○, charge_name:○
        }elseif (strlen($searchTo) > 0
            && strlen($searchDeceasedName) > 0
            && strlen($searchPersonInCharge) > 0) {

            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    WHERE
                        issue_datetime <= :search_to
                    AND
                        deceased_name = :searchDeceasedName
                    AND
                        charge_name = :searchPersonInCharge
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql, array(':search_to' => $searchTo,
                                                             ':searchDeceasedName'   => $searchDeceasedName,
                                                             ':searchPersonInCharge'   => $searchPersonInCharge));
        //From:X, To:X, deceased_name:○, charge_name:○
        }elseif (strlen($searchDeceasedName) > 0
            && strlen($searchPersonInCharge) > 0) {

            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    WHERE
                        deceased_name = :searchDeceasedName
                    AND
                        charge_name = :searchPersonInCharge
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql, array(':searchDeceasedName'   => $searchDeceasedName,

                                                             ':searchPersonInCharge'   => $searchPersonInCharge));
        //From:X, To:○, deceased_name:X, charge_name:○
        }elseif (strlen($searchTo) > 0
            && strlen($searchPersonInCharge) > 0) {

            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    WHERE
                        issue_datetime <= :search_to
                    AND
                        charge_name = :searchPersonInCharge
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql, array(':search_to'   => $searchTo,
                                                             ':searchPersonInCharge'   => $searchPersonInCharge));
        //From:X, To:○, deceased_name:○, charge_name:X
        }elseif (strlen($searchTo) > 0
            && strlen($searchDeceasedName) > 0) {

            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    WHERE
                        issue_datetime <= :search_to
                    AND
                        deceased_name = :searchDeceasedName
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql, array(':search_to'   => $searchTo,
                                                             ':searchDeceasedName'   => $searchDeceasedName));

        //From:○, To:○, deceased_name:X, charge_name:X
        }elseif (strlen($searchFrom) > 0
            && strlen($searchTo) > 0) {

            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    WHERE
                        issue_datetime >= :search_from
                    AND
                        issue_datetime <= :search_to
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql, array(':search_from'   => $searchFrom,
                                                             ':search_to'   => $searchTo));
        //From:○, To:X, deceased_name:o, charge_name:X
        }elseif (strlen($searchFrom) > 0
            && strlen($searchDeceasedName) > 0) {

            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    WHERE
                        issue_datetime >= :search_from
                    AND
                        deceased_name = :searchDeceasedName
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql, array(':search_from'   => $searchFrom,
                                                             ':searchDeceasedName'   => $searchDeceasedName));
        //From:○, To:X, deceased_name:X, charge_name:○
        }elseif (strlen($searchFrom) > 0
            && strlen($searchPersonInCharge) > 0) {

            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    WHERE
                        issue_datetime >= :search_from
                    AND
                        charge_name = :searchPersonInCharge
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql, array(':search_from'   => $searchFrom,
                                                             ':searchPersonInCharge'   => $searchPersonInCharge));
        //From:のみ
        }elseif (strlen($searchFrom) > 0) {
            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    WHERE
                        issue_datetime >= :search_from
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql, array(':search_from' => $searchFrom));

        //To:のみ
        } elseif (strlen($searchTo) > 0) {
            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    WHERE
                        issue_datetime <= :search_to
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql, array(':search_to' => $searchTo));

        //Deceased_Name:のみ
        } elseif (strlen($searchDeceasedName) > 0) {
            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    WHERE
                        deceased_name = :searchDeceasedName
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql, array(':searchDeceasedName' => $searchDeceasedName));

        //Charge_Name:のみ
        }  elseif (strlen($searchPersonInCharge) > 0) {
            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    WHERE
                        charge_name = :searchPersonInCharge
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql, array(':searchPersonInCharge' => $searchPersonInCharge));

        } else {
            $sql = "SELECT
                        *
                    FROM
                        m_deceased
                    ORDER BY
                        issue_datetime DESC
                    ";
            $deceasedList = $this->_db->fetchAll($sql);
        }

        return $deceasedList;
    }

    /**
     * 通知情報の検索条件を元に故人情報を取得する
     *
     * @param  array 通知情報
     * @return array 故人情報リスト
     */
    public function getDeceasedListByNotice($noticeInfo)
    {
        $whereBlock = '';

        switch ($noticeInfo['selected_category']) {
            case 1:     //担当者名
                $whereBlock = "AND charge_name='" . $noticeInfo['charge_name'] . "'";
                break;
            case 2:     //会館名
                $whereBlock = "AND hall_name='" . $noticeInfo['hall_name'] . "'";
                break;
            case 3:     //故人名＋命日
                //故人名
                if(strcmp($noticeInfo['search_name'], '') != 0){
                    $name = str_replace(' ', '', $noticeInfo['search_name']);
                    $name = str_replace('　', '', $name);
                    $whereBlock = "AND replace(replace(deceased_name, ' ', ''), '　', '') LIKE '%" . $name . "%'";
                }
                //命日
                //年
                $year = $noticeInfo['search_year'];
                if(strcmp($year, '') != 0) $year = str_pad($year, 4, "0", STR_PAD_LEFT);
                else $year = '____';
                //月
                $month = $noticeInfo['search_month'];
                if(strcmp($month, '') != 0) $month = str_pad($month, 2, "0", STR_PAD_LEFT);
                else $month = '__';
                //日
                $day = $noticeInfo['search_day'];
                if(strcmp($day, '') != 0) $day = str_pad($day, 2, "0", STR_PAD_LEFT);
                else $day = '__';
                //SQL作成
                if(strcmp($year . $month . $day, '________') != 0) {
                    $whereBlock .= " AND deceased_deathday LIKE '" . $year . $month . $day . "'";
                }
                break;
            case 4:     //命日月
                $month = $noticeInfo['death_month'];
                if(strcmp($month, '') != 0) {
                    $month = str_pad($month, 2, "0", STR_PAD_LEFT);
                    $whereBlock = "AND deceased_deathday LIKE '____" . $month . "__'";
                }
                break;
            case 5:     //法要
                $year  = $noticeInfo['memorial_year'];
                $month = $noticeInfo['memorial_month'];
                $event = $noticeInfo['memorial_event'];
                //どちらか未入力の場合は終了
                if(strcmp($year, '') == 0 || strcmp($month, '') == 0 || strcmp($event, '') == 0) break;
                //そうでない場合
                $year = (int)$year;
                if(strcmp($event, '1') == 0) $year -= 1;    //一周忌の場合
                else $year -= (int)$event - 1;              //それ以外の場合
                $whereBlock = "AND deceased_deathday LIKE '" . (string)$year . str_pad($month, 2, "0", STR_PAD_LEFT) . "__'";
                break;
            default:
                $whereBlock = '';
                break;
        }

        $sql = "SELECT * FROM m_deceased WHERE issue_state_code=3 " . $whereBlock . " ORDER BY deceased_deathday ASC";
        $deceasedList = $this->_db->fetchAll($sql);

        return $deceasedList;
    }

    /**
     * 故人情報を取得する
     * 故人IDを条件に故人情報を取得する
     *
     * @return  array   故人情報
     */
    public function getDeceased($deceasedId)
    {
        $sql = "SELECT
                    *
                FROM
                    m_deceased
                WHERE
                    deceased_id = :deceased_id
                ";
        $deceased = $this->_db->fetchRow($sql, array(':deceased_id' => $deceasedId));
        return $deceased;
    }

    /**
     * 故人ID配列をもとに故人情報を取得する
     *
     * @param array 故人ID配列
     */
    public function getDeceasedByIdList($deceasedIdList)
    {
        $sql = "SELECT
                    *
                FROM
                    m_deceased
                WHERE
                    deceased_id IN ('" . implode("','", $deceasedIdList) . "')
                ORDER BY deceased_deathday ASC";
        $deceasedList = $this->_db->fetchAll($sql);
        return $deceasedList;
    }

    /**
     * 故人情報を新規登録する（発行状態コード：1[発行依頼中]で登録）
     *
     * @param   array   $deceasedInfo    故人情報
     * @return  boolean　成否
     */
    public function insertDeceased(array $deceasedInfo)
    {
        $now = new Zend_Date();
        try {
            $sql = "INSERT INTO m_deceased (
                        deceased_id,
                        deceased_name,
                        deceased_birthday,
                        deceased_deathday,
                        kyonen_gyonen_flg,
                        death_age,
                        deceased_photo_path,
                        charge_name,
                        hall_name,
                        souke,
                        issue_datetime,
                        issue_state_code,
                        entry_datetime,
                        allow_push
                    )
                    VALUES
                    (
                        :deceased_id,
                        :deceased_name,
                        :deceased_birthday,
                        :deceased_deathday,
                        :kyonen_gyonen_flg,
                        :death_age,
                        :deceased_photo_path,
                        :charge_name,
                        :hall_name,
                        :souke,
                        :issue_datetime,
                        1,
                        :entry_datetime,
                        :allow_push
                    )
            ";
            $deceasedBirthday = $deceasedInfo['deceased_birthday_y'] .
                    sprintf("%02d", $deceasedInfo['deceased_birthday_m']) .
                    sprintf("%02d", $deceasedInfo['deceased_birthday_d']);
            $deceasedDeathday = $deceasedInfo['deceased_deathday_y'] .
                    sprintf("%02d", $deceasedInfo['deceased_deathday_m']) .
                    sprintf("%02d", $deceasedInfo['deceased_deathday_d']);
            $this->_db->query($sql, array(
                'deceased_id' => $deceasedInfo['deceased_id'],
                'deceased_name' => $deceasedInfo['deceased_last_name'] . '　' . $deceasedInfo['deceased_first_name'],
                'deceased_birthday' => $deceasedBirthday,
                'deceased_deathday' => $deceasedDeathday,
                'kyonen_gyonen_flg' => $deceasedInfo['kyonen_gyonen_flg'],
                'death_age' => $deceasedInfo['death_age'],
                'deceased_photo_path' => $deceasedInfo['image_existence_flg'],
                'charge_name' => $deceasedInfo['charge_name'],
                'hall_name' => $deceasedInfo['hall_name'],
                'souke' => $deceasedInfo['souke'],
                'issue_datetime' => $now,
                'entry_datetime' => $now,
                'allow_push' => $deceasedInfo['allow_push'])
            );
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }

    /**
     * 故人情報を削除する
     *
     * @param String $deceasedId 故人ID
     * @return boolean
     */
    public function deleteDeceased($deceasedId)
    {
        try {
            $sql = "DELETE FROM
                        m_deceased
                    WHERE
                        deceased_id = :deceased_id
                    AND
                        issue_state_code = 1
                    ";
            $this->_db->query($sql, array('deceased_id' => $deceasedId));
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }

    /**
     * 故人情報を論理削除する
     *
     * @param String $deceasedId 故人ID
     * @return boolean
     */
    public function logicDeleteDeceased($deceasedId)
    {
        try {
            $sql = "UPDATE
                        m_deceased
                    SET
                        issue_state_code = 4
                    WHERE
                        deceased_id = :deceased_id
                    ";
            $this->_db->query($sql, array('deceased_id' => $deceasedId));
        } catch (Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }

    /**
     * 故人情報を完全に削除する
     *
     * @param String $deceasedId 故人ID
     * @return boolean
     */
    public function compRemoveDeceased($deceasedId)
    {
        try {
            $sql = "DELETE FROM
                        m_deceased
                    WHERE
                        deceased_id = :deceased_id
                    ";
            $this->_db->query($sql, array('deceased_id' => $deceasedId));
        } catch (Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }

    /**
     * 故人情報を更新する
     *
     * @param   array   $deceasedInfo    故人情報
     * @return  boolean　成否
     */
    public function updateDeceased(array $deceasedInfo)
    {
        try {
            $sql = "UPDATE
                        m_deceased
                    SET
                        deceased_name = :deceased_name,
                        deceased_birthday = :deceased_birthday,
                        deceased_deathday = :deceased_deathday,
                        kyonen_gyonen_flg = :kyonen_gyonen_flg,
                        death_age = :death_age,
                        deceased_photo_path = :deceased_photo_path,
                        charge_name = :charge_name,
                        hall_name = :hall_name,
                        souke = :souke,
                        allow_push = :allow_push
                    WHERE
                        deceased_id = :deceased_id
            ";
            $deceasedBirthday = $deceasedInfo['deceased_birthday_y'] .
                    sprintf("%02d", $deceasedInfo['deceased_birthday_m']) .
                    sprintf("%02d", $deceasedInfo['deceased_birthday_d']);
            $deceasedDeathday = $deceasedInfo['deceased_deathday_y'] .
                    sprintf("%02d", $deceasedInfo['deceased_deathday_m']) .
                    sprintf("%02d", $deceasedInfo['deceased_deathday_d']);
            $this->_db->query($sql, array(
                'deceased_name' => $deceasedInfo['deceased_last_name'] . '　' . $deceasedInfo['deceased_first_name'],
                'deceased_birthday' => $deceasedBirthday,
                'deceased_deathday' => $deceasedDeathday,
                'kyonen_gyonen_flg' => $deceasedInfo['kyonen_gyonen_flg'],
                'death_age' => $deceasedInfo['death_age'],
                'deceased_photo_path' => $deceasedInfo['image_existence_flg'],
                'charge_name' => $deceasedInfo['charge_name'],
                'hall_name' => $deceasedInfo['hall_name'],
                'souke' => $deceasedInfo['souke'],
                'allow_push' => $deceasedInfo['allow_push'],
                'deceased_id' => $deceasedInfo['deceased_id'])
            );
        } catch(Exception $e) {
            Zend_Debug::dump($e->getMessage(), $label = null, $echo = true);
            return false;
        }
        return true;
    }

    /**
     * 会館名を取得する
     *
     * @return  array   会館名
     */
    public function getHallList()
    {
        $sql = "SELECT DISTINCT
                    hall_name
                FROM
                    m_deceased
                ORDER BY
                    hall_name
                ";
        $hallList = $this->_db->fetchAll($sql);
        return $hallList;
    }
}
