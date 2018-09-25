<?php
if(!defined('InEmpireBak'))
{
	exit();
}

//Database
$phome_db_ver="5.0";
$phome_db_server="127.0.0.1";
$phome_db_port="";
$phome_db_username="kaifa_seenne_co";
$phome_db_password="TjCyKiKiAH";
$phome_db_dbname="";
$baktbpre="";
$phome_db_char="";

//USER
$set_username="admin";
$set_password="0a36826372c2e33b3338761037add782";
$set_loginauth="ghwvipcom";
$set_loginrnd="YFfd33mV2MrKwDenkecYWZETWgUwMV";
$set_outtime="60";
$set_loginkey="1";

//COOKIE
$phome_cookiedomain="";
$phome_cookiepath="/";
$phome_cookievarpre="ebak_";

//LANGUAGE
$langr=ReturnUseEbakLang();
$ebaklang=$langr['lang'];
$ebaklangchar=$langr['langchar'];

//BAK
$bakpath="bdata";
$bakzippath="zip";
$filechmod="1";
$phpsafemod="";
$php_outtime="1000";
$limittype="";
$canlistdb="";

//------------ SYSTEM ------------
HeaderIeChar();
?>