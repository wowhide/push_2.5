<?php
// require_once 'AndroidPushNotifier.php';
require_once 'iosPushThirtyFifthDeathdayNotifier.php';


// $android = new AndroidPushNotifier();
// $android->push();
$iosSeventh = new iosPushThirtyFifthDeathdayNotifier();
$iosSeventh->push();
