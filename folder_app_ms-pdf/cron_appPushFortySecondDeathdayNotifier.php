<?php
// require_once 'AndroidPushNotifier.php';
require_once 'iosPushFortySecondDeathdayNotifier.php';


// $android = new AndroidPushNotifier();
// $android->push();
$iosSeventh = new iosPushFortySecondDeathdayNotifier();
$iosSeventh->push();
