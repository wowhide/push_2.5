<?php
require_once 'AndroidTwentyFirstDeathdayPushNotifier.php';
require_once 'iosPushTwentyFirstDeathdayNotifier.php';


$androidTwentyFirst = new AndroidTwentyFirstDeathdayPushNotifier();
$androidTwentyFirst->push();
$iosTwentyFirst = new iosPushTwentyFirstDeathdayNotifier();
$iosTwentyFirst->push();
