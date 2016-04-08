<?php

define('SESSION_DB_HOST', 'localhost');
define('SESSION_DB_USER', 'webclient');
define('SESSION_DB_PASS', 'webclient');
define('SESSION_DB_NAME', 'simplicity');

class PhpDbSession
{
	var $dbhSession;
	var $uxtLifetime;

	function connect()
	{
		if(!$this->dbhSession)
		{
			$this->dbhSession = mysql_connect(SESSION_DB_HOST, SESSION_DB_USER, SESSION_DB_PASS) or die('Session connect error!');

			mysql_select_db(SESSION_DB_NAME, $this->dbhSession) or die('Session database error!');
		}
	}

	function open($strSavePath, $strName)
	{
		// session.gc_maxlifetime is in minutes

		$this->uxtLifetime = ini_get("session.gc_maxlifetime") * 60;

		$this->connect();

		return true;
	}

	function close()
	{
		$this->connect();

		$this->garbageCollect(ini_get('session.gc_maxlifetime'));

		return @mysql_close($this->dbhSession);
	}
	
	function read($strName)
	{
		$this->connect();

		$strSql = "SELECT txtData FROM tblsession WHERE vchName = '$strName' AND uxtExpires > " . time();

		$refResult = mysql_query($strSql, $this->dbhSession);

		if($arrData = mysql_fetch_assoc($refResult))
		{
			return $arrData['txtData'];
		}
		else
		{
			return '';
		}
	}

	function write($strName, $unkData)
	{
		$this->connect();

		$unkData = addslashes($unkData); // Do we need this?

		$uxtExpire = time() + $this->uxtLifetime;

		$strSql = "SELECT * FROM tblsession WHERE vchName = '$strName'";

		$refResult = mysql_query($strSql, $this->dbhSession) or die('Session write error!' . mysql_error($this->dbhSession));

		if(mysql_num_rows($refResult))
		{
			$strSql = "UPDATE tblsession SET uxtExpires = '$uxtExpire', txtData = '$unkData' WHERE vchName = '$strName'";

			$refResult = mysql_query($strSql, $this->dbhSession) or die('Session update error!');
		}
		else
		{
			$strSql = "INSERT INTO tblsession (vchName, uxtExpires, txtData) VALUES ('$strName', '$uxtExpire', '$unkData')";

			$refResult = mysql_query($strSql, $this->dbhSession) or die('Session append error!');
		}

		return true;
	}

	function destroy($strName)
	{
		$this->connect();

		$strSql = "DELETE FROM tblsession WHERE vchName = '$strName'";

		$refResult = mysql_query($strSql, $this->dbhSession) or die('Session destroy error!');
	}

	function garbageCollect($uxtSessionMaxLifetime)
	{
		$this->connect();

		$loginSql = "UPDATE login_history, tblsession SET login_history.logout = login_history.last_activity WHERE login_history.sid = tblsession.VchName and tblsession.uxtExpires < '" . time() . "'";

		mysql_query($loginSql, $this->dbhSession) or die('Session login history maintenance error!');
		
		$strSql = "DELETE FROM tblsession WHERE uxtExpires < '" . time() . "'";
		
		$refResult = mysql_query($strSql, $this->dbhSession) or die('Session maintenance error!');
	}

	function setLifetime($intSeconds)
	{
		$this->uxtLifetime = $intSeconds;
	}
}

// We only want sessions for web things
if(isset($_SERVER['DOCUMENT_ROOT']))
{
	$objPhpSession = new PhpDbSession();
	
	session_set_save_handler(array(&$objPhpSession,"open"),
	                         array(&$objPhpSession,"close"),
	                         array(&$objPhpSession,"read"),
	                         array(&$objPhpSession,"write"),
	                         array(&$objPhpSession,"destroy"),
	                         array(&$objPhpSession,"garbageCollect"));
}
?>
