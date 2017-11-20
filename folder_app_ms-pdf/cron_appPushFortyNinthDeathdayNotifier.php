<?php
require_once 'AndroidFortyNinthDeathdayPushNotifier.php';
require_once 'iosPushFortyNinthDeathdayNotifier.php';


$androidFortyNinth = new AndroidFortyNinthDeathdayPushNotifier();
$androidFortyNinth->push();
$iosFortyNinth = new iosPushFortyNinthDeathdayNotifier();
$iosFortyNinth->push();
