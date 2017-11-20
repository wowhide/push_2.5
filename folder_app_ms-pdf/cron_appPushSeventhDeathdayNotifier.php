<?php
require_once 'AndroidSeventhDeathdayPushNotifier.php';
require_once 'iosPushSeventhDeathdayNotifier.php';


$androidSeventh = new AndroidSeventhDeathdayPushNotifier();
$androidSeventh->push();
$iosSeventh = new iosPushSeventhDeathdayNotifier();
$iosSeventh->push();
