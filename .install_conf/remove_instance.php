<?php

$etc_dir = trim($GLOBALS['argv'][1]);
$instance_name = trim($GLOBALS['argv'][2]);
$no_backup = $GLOBALS['argv'][3] == '--no_backup';

if ($etc_dir == "")
	die("Please supply the configuration directory as the first argument.\n");
if ($instance_name == "")
	die("Please supply the instance name as the second argument.\n");

// include install_conf.php file
require_once("$etc_dir/install_conf.php");
require_once($Campsite['BIN_DIR'] . "/cli_script_lib.php");

if (!$no_backup) {
	// backup instance
	$cmd = $Campsite['BIN_DIR'] . "/backup_instance \$'$instance_name' --silent_exit";
	exec_command($cmd);
}

// remove WWW directory
$www_dir = $Campsite['WWW_DIR'] . "/$instance_name";
$cmd = "rm -fr \$'$www_dir'";
exec_command($cmd);

// check if the database conf file exists
$database_conf_file = "$etc_dir/$instance_name/database_conf.php";
if (!is_file($database_conf_file))
	exit(0);

// drop database
require_once($database_conf_file);
if (connect_to_database() != 0)
	exit_with_error($res);
mysql_query("DROP DATABASE $instance_name");

// remove etc directory
$cmd = "rm -fr \$'$etc_dir/$instance_name'";
exec_command($cmd);

?>
