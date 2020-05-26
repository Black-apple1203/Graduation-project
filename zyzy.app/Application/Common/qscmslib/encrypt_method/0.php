<?php
$return = md5(md5($password).$randstr.C('PWDHASH'));