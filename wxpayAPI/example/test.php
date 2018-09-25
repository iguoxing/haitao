<?php
define('IN_ECS', true);
require('../../../mobile/includes/init.php');

error_reporting(E_ALL);

ini_set('display_errors',0);    //将错误记录到日志
ini_set('log_errors', 1);
ini_set('error_log','../weblog.txt');

require_once(ROOT_PATH . 'includes/lib_payment.php');

echo "string"

?>