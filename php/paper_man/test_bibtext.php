<?php
/**
 * Created by PhpStorm.
 * User: Xiong Yuanjun
 * Date: 14-8-29
 * Time: 下午4:35
 */
require_once('./paper_man_model.class.php');
require_once('./paper_man.class.php');

$str = @file_get_contents('./include/example.bib');
//
//$msg = new \paper_man\PaperManMessage(1000,'success');
//$all_papers = \paper_man\paper_model::all();
//$msg->content = array('number'=>count($all_papers),
//    'papers'=>array_map(
//        function($paper){
//            return $paper->to_display();
//        },
//        $all_papers
//    )
//);
//
//echo(json_encode($msg));

//var_dump(\paper_man\paper_model::query_title("Spectral hashing"));
//
$pm = new \paper_man\PaperMan();
//$pm->addPaper($str);

$paper = $pm->getPaperByID(1);

$paper['title'] .= ' ok';


$pm->editPaper(1, $paper);

//echo($pm->getPaperByAuthor('Yuanjun Xiong', 'natbib'));

//var_dump(\paper_man\author_model::get_author('Yuanjun Xiong'));

//$author  = new \paper_man\author_model('Yuanjun Xiong');
//
//$author->put();
//
//var_dump($author);

//$author = \paper_man\author_model::get_author(['Yuanjun Xiong','alex']);
//
//var_dump($author);