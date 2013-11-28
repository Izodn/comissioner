<?php
	function getEnvString() {
		$envString = <<<ENV
DATABASE_LOCATION = localhost
DATABASE = TEST_DB
USER = TEST_USER
PASS = TEST_PASS
#
DEVELOPMENT = 1
DEBUGGING = 0
ENV;
	return $envString;
	}
?>