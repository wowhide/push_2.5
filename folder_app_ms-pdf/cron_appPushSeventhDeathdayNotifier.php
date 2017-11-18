<?php
require_once 'AndroidSeventhDeathdayPushNotifier.php';
require_once 'iosPushSeventhDeathdayNotifier.php';


$android = new AndroidSeventhDeathdayPushNotifier();
$android->push();
$iosSeventh = new iosPushSeventhDeathdayNotifier();
$iosSeventh->push();
