<?php


class DBError extends \Exception {
    public function __construct($msg) {
        parent::__construct($msg);
    }
}