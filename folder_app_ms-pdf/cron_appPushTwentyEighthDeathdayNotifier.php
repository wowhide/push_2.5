<?php
require_once 'AndroidTwentyEighthDeathdayPushNotifier.php';
require_once 'iosPushTwentyEighthDeathdayNotifier.php';


$androidTwentyEighth = new AndroidTwentyEighthDeathdayPushNotifier();
$androidTwentyEighth->push();
$iosTwentyEighth = new iosPushTwentyEighthDeathdayNotifier();
$iosTwentyEighth->push();
