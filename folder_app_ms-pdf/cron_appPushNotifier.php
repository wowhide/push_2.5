<?php
require_once 'AndroidPushNotifier.php';
require_once 'iosPushNotifier.php';
require_once 'iosPushSeventhDeathdayNotifier.php';


$android = new AndroidPushNotifier();
$android->push();
$ios = new iosPushNotifier();
$ios->push();
$ios = new iosPushSeventhDeathdayNotifier();
$ios->push();
