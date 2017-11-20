<?php
require_once 'AndroidFortySecondDeathdayPushNotifier.php';
require_once 'iosPushFortySecondDeathdayNotifier.php';


$androidFortySecond = new AndroidFortySecondDeathdayPushNotifier();
$androidFortySecond->push();
$iosFortySecond = new iosPushFortySecondDeathdayNotifier();
$iosFortySecond->push();
