<?php

// Load .env file
if(file_exists(__DIR__ . '/.env')) {
  $rows = explode("\n", file_get_contents(__DIR__ . '/.env'));

  foreach($rows as $row) {
      list($k, $v) = explode("=", $row, 2);
      define($k, trim($v, '"'));
  }
}

// Load environement variables
foreach($_ENV as $k => $v) {
  define($k, $v);
}