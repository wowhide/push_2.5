<?php
require_once 'AndroidThirtyFifthDeathdayPushNotifier.php';
require_once 'iosPushThirtyFifthDeathdayNotifier.php';


$androidThirtyFifth = new AndroidThirtyFifthDeathdayPushNotifier();
$androidThirtyFifth->push();
$iosThirtyFifth = new iosPushThirtyFifthDeathdayNotifier();
$iosThirtyFifth->push();
