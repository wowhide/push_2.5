<?php
require_once 'AndroidFourteenthDeathdayPushNotifier.php';
require_once 'iosPushFourteenthDeathdayNotifier.php';


$androidFourteenth = new AndroidFourteenthDeathdayPushNotifier();
$androidFourteenth->push();
$iosFourteenth = new iosPushFourteenthDeathdayNotifier();
$iosFourteenth->push();
