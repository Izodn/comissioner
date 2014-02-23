<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	function getEnvString() {
		$envString = <<<ENV
DATABASE_LOCATION = localhost
DATABASE = TEST_DB
USER = TEST_USER
PASS = TEST_PASS
SALT = test_str_HASH_sAlT
HASH_COST = 10
#
DEVELOPMENT = 1
DEBUGGING = 0
ENV;
	return $envString;
	}
?>