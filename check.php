<?php
session_start();
echo "<h1>Test session</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";