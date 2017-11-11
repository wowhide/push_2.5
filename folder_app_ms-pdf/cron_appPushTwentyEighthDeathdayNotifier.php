<?php
// require_once 'AndroidPushNotifier.php';
require_once 'iosPushTwentyEighthDeathdayNotifier.php';


// $android = new AndroidPushNotifier();
// $android->push();
$iosSeventh = new iosPushTwentyEighthDeathdayNotifier();
$iosSeventh->push();
