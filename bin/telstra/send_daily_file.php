#!/usr/bin/php
<?php

include "/var/www/simplicity/htdocs/setup.inc";

$key = '/home/telstra-out/.ssh/id_rsa';
$outbox = '/home/telstra-out/OUTBOX';
$sent = '/home/telstra-out/OUTBOX/sent';
$username = 'sr56784';
$host = '10.108.67.234';

$ssh_conn = ssh2_connect($host, 22);

if (ssh2_auth_pubkey_file($ssh_conn, $username, $key .  '.pub', $key)) {
	
	echo "Public Key Authentication Successful\n";
	
	// Get a list of files to send
	$files = scandir($outbox);
	
	while ($cel = each($files)) {
	
		if (substr($cel['value'], 0, 7) == '662TELW') {
			
			
			if (ssh2_scp_send($ssh_conn, $outbox . '/' . $cel['value'], $cel['value'], 0644)) {
				
				// File transferred
				rename($outbox . '/' . $cel['value'], $sent . '/' . $cel['value']);

				echo "File transferred - " . $cel['value'] . "\n";

			} else {
				die("Error transfeerring file - " . $cel['value'] . "\n");
			}
				
		} 	
	}
	
} else {
  die('Public Key Authentication Failed');
}

