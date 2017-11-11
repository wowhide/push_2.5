<?php
// require_once 'AndroidPushNotifier.php';
require_once 'iosPushTwentyFirstDeathdayNotifier.php';


// $android = new AndroidPushNotifier();
// $android->push();
$iosSeventh = new iosPushTwentyFirstDeathdayNotifier();
$iosSeventh->push();
