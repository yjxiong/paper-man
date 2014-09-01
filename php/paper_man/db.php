<?php
/**
 * Created by PhpStorm.
 * User: Xiong Yuanjun
 * Date: 14-8-30
 * Time: 下午9:39
 */

namespace paper_man;
require_once('./include/mysql_conn/DBConn.class.php');
require_once('./include/mysql_conn/DBError.class.php');

/**
 * Singleton class
 *
 */
final class PaperDB
{
    /**
     * Call this method to get singleton
     *
     * @return UserFactory
     */

    public  $db_conn;

    public  $config;

    public static function Get()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new PaperDB();
        }
        return $inst;
    }

    public static function  get_db_conn(){
        return PaperDB::Get()->db_conn;
    }

    public static function  test_conn(){
        if (PaperDB::Get()->db_conn){
            echo "OK";
        }
    }

    public static function last_insert_id(){
        return PaperDB::Get()->db_conn->last_insert_id();
    }


    /**
     * Private ctor so nobody else can instance it
     *
     */

    private function __construct()
    {
        include "./pm-config.php";

        $this->config = $config;

        try{
            $this->db_conn =  new \DBConn('mysql',$this->config['db_name'], $this->config['db_user'], $this->config['db_pw'],$this->config['db_host']);
        }
        catch (\DBError $e){
            error_log("DB Connection Error");
            throw $e;
        }

    }

    public  static function query($qstr, $param=array()){
        return PaperDB::get_db_conn()->query($qstr, $param);
    }




}