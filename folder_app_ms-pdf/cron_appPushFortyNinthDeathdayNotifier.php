<?php
// require_once 'AndroidPushNotifier.php';
require_once 'iosPushFortyNinthDeathdayNotifier.php';


// $android = new AndroidPushNotifier();
// $android->push();
$iosSeventh = new iosPushFortyNinthDeathdayNotifier();
$iosSeventh->push();
