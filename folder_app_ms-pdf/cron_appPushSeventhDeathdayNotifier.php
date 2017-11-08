<?php
// require_once 'AndroidPushNotifier.php';
require_once 'iosPushSeventhDeathdayNotifier.php';


// $android = new AndroidPushNotifier();
// $android->push();
$iosSeventh = new iosPushSeventhDeathdayNotifier();
$iosSeventh->push();
