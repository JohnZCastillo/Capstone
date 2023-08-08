<?php

date_default_timezone_set("Asia/Manila");


$date = new DateTime();

var_dump($date->format("M d, Y h:i:s a"));