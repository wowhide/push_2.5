<?php
require_once 'AndroidFourteenthDeathdayPushNotifier.php';
require_once 'iosPushFourteenthDeathdayNotifier.php';


$android = new AndroidFourteenthDeathdayPushNotifier();
$android->push();
$iosSeventh = new iosPushFourteenthDeathdayNotifier();
$iosSeventh->push();
