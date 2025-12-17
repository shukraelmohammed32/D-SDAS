<?php
// public/logout.php
require_once '../bootstrap.php';
require_once '../src/Auth.php';

Auth::logout();

header("Location: index.php");
exit; 