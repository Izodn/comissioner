<?php
	function getEnvVars() {
		$env = <<<ENV
DATABASE_LOCATION => localhost
DATABASE => TEST_DB
USER => TEST_USER
PASS => TEST_PASS
#
SUPERUSER => Username
DEVELOPMENT => 1
ERROR_REPORTING => 1
OutputBuffer => 1
DebugMode => 0
TrackPages => 1
ENV;
		return $env;
	}
?>