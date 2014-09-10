<?php
/**
 * Created by PhpStorm.
 * User: Xiong Yuanjun
 * Date: 14-8-30
 * Time: 下午8:52
 */

namespace paper_man;

require_once('./include/BibTex.php');
require_once('./include/mysql_conn/DBConn.class.php');
require_once('./include/mysql_conn/DBError.class.php');
require_once('./db.php');
require_once('./include/lib_bibtex-printers-abbrv.inc.php');
require_once('./include/lib_bibtex-printers-natbib.inc.php');
require_once('./include/lib_bibtex-printers-numeric.inc.php');




class paper_model{

    private $paper_bibtex;




    public function __construct($bibtex=null){
        $this->paper_bibtex = new \Structures_BibTex();
        try{
            if($bibtex!=null){
                $this->paper_bibtex->parse_from_string($bibtex);
            }
        }catch (\Structures_BibTex_Exception $e){
            error_log('Paring string error');
            throw $e;
        }
    }

    public function put(){
        $conn = PaperDB::get_db_conn();

        $db_entry = $this->_to_db_entries();

        $fields = array_keys($db_entry[0]);

        $placeholder = array_map(function($str){
            return ':'.$str;
        }, $fields);

        $qstr = 'INSERT INTO pm_paper ('.join(',',$fields).') VALUES ('.join(',',$placeholder).')';


        foreach($db_entry as $entry){
            $param = array();
            foreach ($fields as $f) {
                array_push($param, array($f, $entry[$f], \PDO::PARAM_STR));
            }
            $conn->query($qstr, $param);

            $paper_id=PaperDB::last_insert_id();
            $author_list[$paper_id] = $this->_format_authors(true);
        }

        $author_names = array();
        $author_paper_bind = array();
        foreach (array_keys($author_list) as $pid) {
            $author_names = array_merge($author_names, $author_list[$pid]);

        }

        $author_names = array_unique($author_names);

        $author_dict = author_model::get_author($author_names);

        foreach($author_names as $name){
            if ($author_dict[$name] == null){
                $new_author = new author_model($name);
                $new_author->put();
                $author_dict[$name] = PaperDB::last_insert_id();
            }
        }

        foreach( array_keys($author_list) as $pid){
            foreach($author_list[$pid] as $author){
                array_push($author_paper_bind, array(intval($pid), intval($author_dict[$author])));
            }
        }

        $this->_save_author_paper_bind($author_paper_bind);



    }

    public function get_data($index=null){
        if ($index!=null){
            $raw =  $this->paper_bibtex->data;
            return $raw[$index];
        }else{
            return $this->paper_bibtex->data;
        }

    }

    public function update_data($data_array){

    }

    public static function  query_id($paper_id){

        $qstr = 'SELECT * FROM pm_paper WHERE paper_id = :paper_id';

        $param = array();
        array_push($param, array(':paper_id',intval($paper_id), \PDO::PARAM_INT));

        $qrst =  PaperDB::get_db_conn()->query($qstr, $param);

        $paper = new paper_model();
        $paper->_populate_from_db($qrst[0]);

        return $paper;

    }

        public static function query_title($title){

        $qstr = 'SELECT * FROM pm_paper WHERE title = :title';

        $param = array();
        array_push($param, array(':title', $title, \PDO::PARAM_STR, 255));

        $qrst =  PaperDB::get_db_conn()->query($qstr, $param);
        $paper = new paper_model();
        $paper->_populate_from_db($qrst[0]);

        return $paper;
    }

    public static function query_by_author($author_id){
        $qstr = 'SELECT pm_paper.*, pm_author_paper_bind.author_id FROM pm_paper, pm_author_paper_bind WHERE pm_paper.paper_id = pm_author_paper_bind.paper_id AND pm_author_paper_bind.author_id = :author_id';
        $param = array(
            array('author_id', intval($author_id), \PDO::PARAM_INT),
        );

        $ret = PaperDB::query($qstr, $param);
        $papers = array();
        foreach($ret as $entry){
            $paper = new paper_model();
            $paper->_populate_from_db($entry);
            array_push($papers, $paper);
        }
        return $papers;
    }


    public function to_display(){
        include('pm-config.php');
        $display_list = array();
        foreach($DISPLAY_FIELDS as $f){
            $display_list[$f] = $this->paper_bibtex->data[0][$f];
        }

        $entry_spec_fields = $ENTRY_TYPES[$this->paper_bibtex->data[0]['entryType']]['required'];

        $arena_array = array();
        foreach ($entry_spec_fields as $extra_f) {
            if (!(in_array($extra_f, $DISPLAY_FIELDS))){
                array_push($arena_array, $this->paper_bibtex->data[0][$extra_f]);
            }
        }
        $display_list['arena'] = join(',', $arena_array);
        $display_list['author'] = $this->_format_authors();

        return $display_list;

    }

    public function to_string(){
        include('pm-config.php');

        $entryType = $this->paper_bibtex->data[0]['entryType'];

        $paper_raw = $this->paper_bibtex->data[0];

        $bib_fields = $ENTRY_TYPES[$entryType]['required'];

        $parts = array();
        foreach ($bib_fields as $f) {
            $part = $paper_raw[$f];
            if($f == 'author'){
                $author_text = $this->_format_authors().'. ';
            }else{
                if($f == 'title'){
                    $part = '<strong>'.$part.'</strong>';
                }

                array_push($parts, $part);
            }

        }

        return $author_text.join('. ', $parts).'.';

    }

    public function print_bib(){

    }

    private function _format_authors($get_array=false){
        $authors = $this->paper_bibtex->data[0]['author'];

        $author_strs = array_map(function($a){return \Structures_BibTex::_formatAuthor($a);}, $authors);

        if ($get_array){
            return $author_strs;
        }
        else{
            return join(', ', $author_strs);
        }
    }


    public static function all(){
        $qstr = 'SELECT * FROM  pm_paper ORDER BY CAST(year AS UNSIGNED) DESC ';

        $conn = PaperDB::get_db_conn();

        $rst = $conn->query($qstr);

        $all_papers = array();
        foreach ($rst as $entry) {
            $paper = new paper_model();
            $paper->_populate_from_db($entry);
            array_push($all_papers, $paper);
        }

        return $all_papers;

    }

    /**
     * Private methods
     */

    private function _save_author_paper_bind($bind_array){

        $param = array();
        $placeholders = array_map( function($index){
            return '(:paper_id'.$index.', :author_id'.$index.')';
        },range(0, count($bind_array)-1));
        $qstr = 'INSERT INTO pm_author_paper_bind (paper_id, author_id) VALUES '.join(', ',$placeholders);
        for($i=0;$i<count($bind_array);$i++){
            array_push($param, array('paper_id'.$i, $bind_array[$i][0], \PDO::PARAM_INT));
            array_push($param, array('author_id'.$i, $bind_array[$i][1], \PDO::PARAM_INT));
        }

        PaperDB::query($qstr, $param);
    }

    private function _to_db_entries(){

        include('./pm-config.php');

        $papers = $this->paper_bibtex->data;
        $db_entries = array();
        foreach ($papers as $data) {
            $entry = array();
            $other_key = array();
            foreach(array_keys($data) as $key){
                if (in_array($key, $BIBTEX_STANDARD_FIELDS)){
                    if ($key != 'author'){
                        $entry[$key] = $data[$key];
                    }else{
                        $entry[$key] = json_encode($data[$key]);
                    }
                }else{
                    $other_key[$key] = json_encode($data[$key]);
                }
            }
            $entry['other_info'] = json_encode($other_key);
            array_push($db_entries, $entry);
        }
        return $db_entries;
    }

    private function _populate_from_db($db_entry){

        include('./pm-config.php');
        $this->paper_bibtex->data = array();
        $papers = array();
        foreach(array_keys($db_entry) as $key){
            if (in_array($key, $BIBTEX_STANDARD_FIELDS)){
                if ($key != 'author'){
                    $papers[$key] = $db_entry[$key];
                }else{
                    $papers[$key] = json_decode($db_entry[$key],true);
                }
            }else{
                if ($key == 'other_info'){
                    $other_keys = json_decode($db_entry[$key], true);
                    $papers = array_merge($papers, $other_keys);
                }else{
                    $papers[$key] = $db_entry[$key];
                }


            }
        }
        array_push($this->paper_bibtex->data, $papers);
    }

    public static function  getSQLString(){
        include('./pm-config.php');
        $base_str = 'CREATE TABLE  `'.$config["db_name"].'`.`pm_paper` (`paper_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,`other_info` TEXT, ';
        $field_str = array();

        foreach ($BIBTEX_STANDARD_FIELDS as $f) {
            array_push($field_str, '`'.$f.'` VARCHAR( 255 )');
        }

        $field_str_full = join(', ',$field_str);

        $unique_constraint = ' UNIQUE(title) ';

        $field_str_full = join(',',array($field_str_full, $unique_constraint));


        $build_author_db_str = 'CREATE TABLE  `'.$config["db_name"].'`.`pm_author` (`author_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,`author_name` VARCHAR( 255 ) NOT NULL ,UNIQUE (`author_name`)) ENGINE = MYISAM;';

        $build_author_paper_str = ' CREATE TABLE '.$config["db_name"].'.pm_author_paper_bind(bind_id int NOT NULL AUTO_INCREMENT PRIMARY KEY , author_id int NOT NULL,  paper_id int NOT NULL,  Foreign Key (author_id) REFERENCES pm_author(author_id),  Foreign Key (paper_id) REFERENCES pm_paper(paper_id));';

        $build_paper_extra_str = 'CREATE TABLE  '.$config["db_name"].'.`pm_paper_extra` (`paper_id` INT NOT NULL ,`visible` BOOL NOT NULL ,`download_url` TEXT NOT NULL ,`project_site` TEXT NOT NULL ,`thumb_url` TEXT NOT NULL ,PRIMARY KEY (  `paper_id` ), Foreign Key (paper_id) REFERENCES pm_paper(paper_id)); ';

        $build_admin = 'CREATE TABLE '.$config["db_name"].'.`pm_admin` (`admin_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `admin_level` INT NOT NULL DEFAULT 0, `admin_name` VARCHAR(255), `admin_pw` VARCHAR(255), UNIQUE(`admin_name`)); ';

        $build_root_admin = 'INSERT INTO pm_admin (admin_name, admin_pw, admin_level) VALUES ("admin", "'.password_hash('142536', PASSWORD_DEFAULT).'", 10);';

        $ret_str = $base_str.$field_str_full.' ) ENGINE = MYISAM;  '.$build_author_db_str.$build_author_paper_str.$build_paper_extra_str.$build_admin.$build_root_admin;


        return $ret_str;

    }
}

class author_model{

    public $author_id;
    public $author_name;

    public function __construct($name, $id=null){
        $this->author_name = $name;

        if ($id){
            $this->author_id = $id;
        }
    }

    public static function get_author($names){

        $placeholders = array();
        $param = array();

        for ($i = 0; $i<count($names); $i++){
            array_push($placeholders, ':name'.$i);
            array_push($param, array('name'.$i, $names[$i], \PDO::PARAM_STR));
        }

        $qstr = 'SELECT * FROM pm_author WHERE author_name IN ('.join(',',$placeholders).')';

        $ret = PaperDB::query($qstr, $param);

        $author_dict =  array();


        foreach($ret as $a){
            $author_dict[$a['author_name']] = $a['author_id'];
        }
        $got_names = array_keys($author_dict);
        foreach ($names as $name) {
            if (!in_array($name, $got_names)){
                $author_dict[$name] = null;
            }
        }

        return $author_dict;

    }

    public function put(){
        $qstr = 'INSERT INTO pm_author (author_name) VALUES (:author_name)  ON DUPLICATE KEY UPDATE  author_name=:author_name';

        $param = array(array('author_name', $this->author_name, \PDO::PARAM_STR));

        PaperDB::query($qstr, $param);

        $this->author_id = PaperDB::last_insert_id();
    }
}

