<?php
class CSqlite
{
	# default settings
	protected $aConf = [
		# start searching from this path
		'vPath' => "/var/www/global",
		# we search for *.sqlite / *.db files
		'vSearch' => "#(.+\.sqlite|.+\.db)$#",
		# write access!
		'vPwdFile' => __DIR__ . "/CSqlite.pwd",
	];

	#----------------------------------------------------------------
	public function __construct($aOpt=[])
	#----------------------------------------------------------------
	{
		$this->aConf = $aOpt + $this->aConf;
	}


	#################################################################
	# Form Stuff
	#################################################################

	# Form login page
	# Drop down of found *.sqlite files,
	# password field and login button
	#----------------------------------------------------------------
	public function loginForm()
	#----------------------------------------------------------------
	{
		$aTxt = [
			'vPassword' => Adminer\lang('Password'),
			'vDatabase' => Adminer\lang('Database'),
			'vPermanent' => Adminer\lang('Permanent login'),
		];
		$vInput = $this->_mBuildDropDown();

		echo <<<EOD
		<form method='post'>
			<input type="hidden" name="auth[driver]" value="sqlite">
			<table class="layout">
				<tr>
					<th>{$aTxt['vDatabase']} (sqlite):</th>
					<td>$vInput</td>
				</tr>
				<tr>
					<th>{$aTxt['vPassword']}:</th>
					<td><input type='password' name='auth[password]'></td>
				</tr>
			</table>
			<p>
				<input type="submit" value="Login" class="">
				<label><input type="checkbox" name="auth[permanent]" value="1">{$aTxt['vPermanent']}</label>
			</p>
		</form>
EOD;

		# If these methods return a non-null value then it will be used instead of the original
		# (except dumpFormat, dumpOutput, editRowPrint, editFunctions where the return value is appended to the original). 
		return 'replace';
	}

	#----------------------------------------------------------------
	protected function _mBuildDropDown()
	#----------------------------------------------------------------
	{
		$vOption = '';
		$aFile = $this->_mScanDbFile();
		$vSelected = $_POST['auth']['db'] ?? false;

		foreach ( $aFile as $vFile )
		{
			$vSelect = $vSelected == $vFile ? 'selected' : '';
			$vTxt = str_replace($this->aConf['vPath'], '', $vFile);
			$vOption .= "<option value='$vFile'{$vSelected}>$vTxt</option>";
		}
		# No db files found, use text input
		if ( ! $vOption ) { $vInput = "<input type='text' name='auth[db]'>"; }
		else { $vInput = "<select name='auth[db]'>$vOption</select>"; }

		return $vInput;
	}

	#----------------------------------------------------------------
	protected function _mScanDbFile()
	#----------------------------------------------------------------
	{
		$oDir = new RecursiveDirectoryIterator($this->aConf['vPath']);
		$oIterator = new RecursiveIteratorIterator($oDir);
		$oFile = new RegexIterator($oIterator, $this->aConf['vSearch'], RegexIterator::GET_MATCH);
		$aFound = [];
		foreach($oFile as $vFile)
		{
			$aFound = array_merge($aFound, $vFile);
		}
		return array_unique($aFound);
	}

	public function databases()
	{
		# empty entry is set somewhere, would like to overwrite! Doesn't work
		// $aDatabase = ["" => "Create database"];
		$aFile = $this->_mScanDbFile();
		foreach ( $aFile as $vFile )
		{
			$vTxt = str_replace($this->aConf['vPath'], '', $vFile);
			$aDatabase[$vFile] = $vTxt;
		}
		return $aDatabase;
	}

	#################################################################
	# Login Stuff
	#################################################################

	#----------------------------------------------------------------
	public function credentials()
	#----------------------------------------------------------------
	{
		# clear text password (from post or session)
		$vPwd = Adminer\get_password();

		# case 1: pwd file exists
		if ( file_exists($this->aConf['vPwdFile']) )
		{
			if ( ! $vPwdHash = file_get_contents($this->aConf['vPwdFile']) )
			{
				throw new Error("Can't find password file ($this->aConf['vPwdFile'])");
			}
			password_verify($vPwd, $vPwdHash) && $vPwd = '';
		}

		# case 2: pwd is missing
		else if ( ! $vPwd )
		{
			$vPwd = 'missing';
		}

		# case 3: write new pwd file
		else
		{
			$vPwdHash = password_hash($vPwd, PASSWORD_DEFAULT);
			file_put_contents($this->aConf['vPwdFile'], $vPwdHash) ||
				throw new Error("Can't write password file ($this->aConf['vPwdFile'])");
			$vPwd = '';
		}
		return [Adminer\SERVER, $_GET["username"], $vPwd];
	}

	#----------------------------------------------------------------
	public function login($login, $vPwd)
	#----------------------------------------------------------------
	{
		if ($vPwd != "") { return true; }
	}

}
