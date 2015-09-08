#! /bin/bash

<?php

require_once('./vendor/autoload.php');
require_once('./Yaml2Sql.php');

use Symfony\Component\Yaml\Yaml;

/**
 * Convert YAML file to sql statement
 * 
 * @return path to output sql file
 *
 * @package    extranet
 * @subpackage batch
 * @version    $Id$
 */

$shortopts = "";
$shortopts .= "f:";

$options = getopt($shortopts);

if (isset($options['f'])) {
	$yaml2Sql = new Yaml2Sql($options['f']);
	echo $yaml2Sql -> generateSql() . PHP_EOL;
} else {
	$msg = 'usage: generateSql.php []' . PHP_EOL;
	$msg .= '-f path to yaml file' . PHP_EOL;
	
	echo $msg;
}
