<?php
require_once 'AndroidPushNotifier.php';
require_once 'iosPushNotifier.php';

$android = new AndroidPushNotifier();
$android->push();
$ios = new iosPushNotifier();
$ios->push();