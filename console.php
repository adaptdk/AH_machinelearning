#!/usr/bin/env php
<?php
  require_once __DIR__ . '/vendor/autoload.php';
  use Symfony\Component\Console\Application;
  use Console\ConvertCommand;
  use Console\FastTextCommand;

  $app = new Application();
  $app -> add(new ConvertCommand());
  $app -> add(new FastTextCommand());

  $app -> run();
