<?php
// require_once 'AndroidPushNotifier.php';
require_once 'iosPushFourteenthDeathdayNotifier.php';


// $android = new AndroidPushNotifier();
// $android->push();
$iosSeventh = new iosPushFourteenthDeathdayNotifier();
$iosSeventh->push();
