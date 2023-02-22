<?php
	$_mysqli =  @new mysqli(DBHOST, DBUSERNAME, DBPW, DBNAME);

    if ($_mysqli->connect_errno) {
        die('Connect Error : '. $_mysqli->connect_errno);
    }
	
    $_mysqli->set_charset('utf8');
