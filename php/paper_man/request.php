<?php
/**
 * Created by PhpStorm.
 * User: Xiong Yuanjun
 * Date: 14-8-29
 * Time: 下午11:26
 *
 * Basic Router of Requests
 *
 */

require_once('./paper_man.class.php');

session_start();

if(isset($_SESSION['pm'])){
    $pm = unserialize($_SESSION['pm']);
}else{
    $pm = new \paper_man\PaperMan();
}

if (isset($_GET['action'])) header('Content-Type: application/json');


if (isset($_GET['action'])){
    switch ($_GET['action']){
        case "init":
            $msg = new \paper_man\PaperManMessage(1000,'initialized');
            $msg->content = array('user'=>$pm->user);
            break;
        case "adminLogin":
            /**
             * Action routing: login
             */
            $user = $_POST['username'];
            $pw = $_POST['password'];

            $msg = $pm->adminLogin($user, $pw);

            break;
        case "adminLogout":
            /**
             * Logout admin
             */
            $pm->requireLogin();
            $msg = $pm->adminLogout();
            break;

        case "getPapers":
            /**
             *  Give paper list
             *
             *  DEBUG: ENABLED
             */
            $author_name = $_GET['author'];
            $format = $_GET['response'];
            $display = $_GET['display'];
            $str = $pm->getPaperByAuthor($author_name, $format);

            if ($display==null){
                echo('document.write("'.$str.'");');
            }
            exit();
            break;

        case "addPaper":
            $pm->requireLogin();
            $bib = $_POST['bibtex'];
            $msg = $pm->addPaper($bib);
            break;

        case "editPaper":
            $pm->requireLogin();
            break;

        case "deletePaper":
            $pm->requireLogin();
            break;

        case "parseBibtex":
            $pm->requireLogin();
            /**
             * parse posted bibtex
             */

            $bib = $_POST['bibtex'];

            $msg = new \paper_man\PaperManMessage(1000,'parse');
            $msg->content = $pm->parseBibtex($bib);

            break;

        case 'getSQL':
            echo(\paper_man\paper_model::getSQLString());
            exit();
            break;

        case 'getAllPapers':
            $pm->requireLogin();
            $msg = new \paper_man\PaperManMessage(1000,'success');
            $msg->content = $pm->getAllPapers();
            break;
        default:
            $msg = new \paper_man\PaperManMessage(1099, 'unknown error');
    }

    echo($msg->toJSON());
}