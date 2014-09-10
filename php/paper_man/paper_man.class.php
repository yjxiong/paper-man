<?php
/**
 * Created by PhpStorm.
 * User: Xiong Yuanjun
 * Date: 14-8-29
 * Time: 下午3:52
 */



namespace paper_man;

require_once('./include/BibTex.php');
require_once('./include/mysql_conn/DBConn.class.php');
require_once('./include/mysql_conn/DBError.class.php');
require_once('./db.php');
require_once('./paper_man_model.class.php');
require('./include/password.php');
include('./pm-config.php');

class PaperManMessage{

    public $error_code;
    public $error_msg;
    public $content;

    public function __construct($error_code, $error_msg){
        $this->error_code = $error_code;
        $this->error_msg = $error_msg;
    }

    public function toJSON(){
        return json_encode($this);
    }

}

class PaperMan{



    public  $user;

    public  function __construct(){

    }

//    public function once_add_root($name, $pass){
//
//        $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
//        $qstr = 'INSERT INTO pm_admin (admin_name, admin_pw)
//                    values (:name, :pw_hash)';
//
//        $params = array();
//        array_push($params,array('name', $name, \PDO::PARAM_STR));
//        array_push($params,array('pw_hash', $pass_hash, \PDO::PARAM_STR));
//
//
//        $conn = $this->_get_db_conn();
//        $conn->query($qstr, $params);
//    }

    private function _verify_admin($name, $pass){
        $qstr = 'SELECT * FROM pm_admin WHERE admin_name = :name';

        $params = array();
        array_push($params,array('name', $name, \PDO::PARAM_STR));


        $conn = PaperDB::get_db_conn();
        $ret = $conn->query($qstr, $params);

        $user = $ret[0];

        if (\password_verify($pass, $user['admin_pw'])){
            return array('state'=>'success','id'=>$user['admin_id'],
                         'name'=>$user['admin_name']);
        }else{
            return array('state'=>'fail','id'=>-1,
                'name'=>'');
        }
    }

    public function adminLogin($username, $password, $remember = true) {

        $verify = $this->_verify_admin($username, $password);

        if ($verify['state']=='success') {
            if ($remember) {
                    /** These don't make sense*/
//                setcookie('pm_admin', $username, time() + 90 * 86400, '/');
//                setcookie('pm_pass', $password, time() + 90 * 86400, '/');
            }
            $this->user = $verify['name'];
            $_SESSION['pm'] = serialize($this);
            $msg = new PaperManMessage(1000, 'success');
            return $msg;
        } else {
            $msg = new PaperManMessage(1001, "user/password invalid");
            return $msg;
        }
    }

    public function adminLogout(){
        unset($_SESSION['pm']);

        $msg = new PaperManMessage(1000, 'logout success');
        return $msg;
    }

    private  function _isVerified(){
        if (($this->user == null)||($this->user =='')){
            return false;
        }else{
            return true;
        }
    }

    public function requireLogin(){
        if ($this->_isVerified()){
            return;
        }else{
            $msg = new \paper_man\PaperManMessage(2000,'unauthorized');
            $msg->content = array('user'=>'');
            echo($msg->toJSON());
            exit();
        }
    }

    public function parseBibtex($bibtex){

        $bib_obj = new paper_model($bibtex);

        return $bib_obj->get_data();
    }

    public function addPaper($bibtex){
        $bib_obj = new paper_model($bibtex);

        try{
            $bib_obj->put();
            $msg = new PaperManMessage(1000,'add success');
        }catch (\DBError $e){
//            $msg = new PaperManMessage(1099,'add failed, db error');
            error_log('in adding paper');
            throw $e;
        }
        return $msg;

    }

    public function getAllPapers(){
        $all_papers = \paper_man\paper_model::all();
        $ret = array('number'=>count($all_papers),
            'papers'=>array_map(
                function($paper){
                    return $paper->to_display();
                },
                $all_papers
            )
        );
        return $ret;
    }



    public function getPaperByAuthor($author_name, $format='text'){
        $author_dict = author_model::get_author(array($author_name));
        if($author_dict[$author_name] == null){
            echo('Unknown author');
            exit();
        }

        $author_id = $author_dict[$author_name];

        $papers = paper_model::query_by_author($author_id);

        if(($format=='text')||($format==null)){
            $out_str = array_map( function($paper){
                return '<p>'.$paper->to_string().'</p>';
            }, $papers);

            return join(' ', $out_str);
        }else{
            if ($format=='natbib'){


                $entries = array_map(
                    function($paper){
                        $raw = $paper->get_data();
                        $data = $raw[0];
                        $dsp = $paper->to_display();
                        $data['author'] = $dsp['author'];
                        $data['type'] = $data['entryType'];
                        $printer = new \AbbrvPrinter();
                        $ret = '<li>'.$printer->CitationStr($data).'</li>';
                        return preg_replace('/[{}]/', '', str_replace("\n","",$ret));
                    },
                    $papers);
                return join(' ',$entries);
            }
            return $papers;
        }
    }


    public function getPaperByID($paper_id, $format='json'){

        $paper = paper_model::query_id($paper_id);

        if ($format == 'json'){
            $raw = $paper->get_data();
            $data = $raw[0];
            $disp_list = $paper->to_display();
            $data['author'] = $disp_list['author'];

            return $data;

        } else{
            return $paper->to_string();
        }

    }

    public function editPaper($paper_id, $updated_paper){

    }







}

