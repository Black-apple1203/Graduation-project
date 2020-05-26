<?php
namespace JPush\Exceptions;
require_once dirname(__FILE__) . '/JPushException.php';
use \JPush\Exceptions\JPushException;
class APIConnectionException extends JPushException {
    function __toString() {
        return "\n" . __CLASS__ . " -- {$this->message} \n";
    }
}
