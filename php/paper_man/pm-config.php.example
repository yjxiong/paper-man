<?php
/**
 * Created by PhpStorm.
 * User: Xiong Yuanjun
 * Date: 14-8-29
 * Time: 下午9:02
 */

$config["db_name"] = "YOUR DB NAME";
$config["db_host"] = "YOUR DB HOST";
$config["db_user"] = "YOUR DB USER NAME";
$config["db_pw"] = "YOUR DB PASSWORD";


$BIBTEX_STANDARD_FIELDS = array(
    "address",
    "annote",
    "author",
    "booktitle",
    "chapter",
    "crossref",
    "edition",
    "editor",
    "howpublished",
    "institution",
    "journal",
    "key",
    "month",
    "note",
    "number",
    "organization",
    "pages",
    "publisher",
    "school",
    "series",
    "title",
    "type",
    "volume",
    "year",
    "entryType"
);

$ENTRY_TYPES = array(
    'article' => array(
        'required' => array('author', 'title', 'journal', 'year'),
        'optional' => array('volume', 'number', 'pages', 'month', 'note')
    ),
    'book' => array(
        'required' => array('author', 'title', 'publisher', 'year'),
        'optional' => array('volumn', 'series', 'address', 'edition', 'month', 'note')
    ),
    'booklet' => array(
        'required' => array('title'),
        'optional' => array('author', 'howpublished', 'address', 'month', 'year', 'note')
    ),
    'conference' => array(
        'required' => array('author', 'title', 'booktitle', 'year'),
        'optional' => array('editor', 'volume', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note')
    ),
    'inbook' => array(
        'required' => array('author', 'title', 'pages', 'publisher', 'year'),
        'optional' => array('volume', 'series', 'type', 'address', 'edition', 'month', 'note')
    ),
    'incollection' => array(
        'required' => array('author', 'title', 'booktitle', 'publisher', 'year'),
        'optional' => array( 'editor', 'volume', 'series', 'type', 'chapter', 'pages', 'address', 'edition', 'month', 'note')
    ),
    'inproceedings' => array(
        'required' => array('author', 'title', 'booktitle', 'year'),
        'optional' => array('editor', 'volume', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note')
    ),
    'manual' => array(
        'required' => array('title'),
        'optional' => array('author', 'organization', 'address', 'editor', 'month', 'year', 'note')
    ),
    'mastersthesis' => array(
        'required' => array('author', 'title', 'school', 'year'),
        'optional' => array('type', 'address', 'month', 'note')
    ),
    'misc' => array(
        'required' => array(),
        'optional' => array('author', 'title', 'howpublished', 'month', 'year', 'note')
    ),
    'phdthesis' => array(
        'required' => array('author', 'title', 'school', 'year'),
        'optional' => array('type', 'address', 'month', 'note')
    ),
    'proceedings' => array(
        'required' => array('title', 'year'),
        'optional' => array('editor', 'volume', 'series', 'address', 'month', 'publisher', 'organization', 'note')
    ),
    'techreport' => array(
        'required' => array('author', 'title', 'institution', 'year'),
        'optional' => array('type', 'number', 'address', 'month', 'note')
    ),
    'unpublished' => array(
        'required' => array('author', 'title', 'note'),
        'optional' => array('month', 'year')
    )
);

$DISPLAY_FIELDS = array(
    "author",
    "title",
    "year",
    "paper_id"
);