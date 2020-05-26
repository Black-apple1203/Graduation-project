<?php
//捷通
$return = md5(strtoupper(md5($password)).$randstr.C('PWDHASH'));