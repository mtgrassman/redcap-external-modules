<?php
namespace ExternalModules;

$noAuth = isset($_GET['NOAUTH']);
if($noAuth){
	// This must be defined at the top before redcap_connect.php is required.
	define('NOAUTH', true);
}

require_once dirname(__FILE__) . '/classes/ExternalModules.php';

use Exception;

$page = rawurldecode(urldecode($_GET['page']));
$pid = @$_GET['pid'];

$prefix = $_GET['prefix'];
if(empty($prefix)){
	$prefix = ExternalModules::getPrefixForID($_GET['id']);
	if(empty($prefix)){
		throw new Exception("A module prefix must be specified as a query parameter!");
	}
}

$version = ExternalModules::getSystemSetting($prefix, ExternalModules::KEY_VERSION);
if(empty($version)){
	throw new Exception("The requested module is currently disabled systemwide.");
}

$config = ExternalModules::getConfig($prefix, $version);
if($noAuth && !in_array($page, $config['no-auth-pages'])){
	throw new Exception("The NOAUTH parameter is not allowed on this page.");
}

if($pid != null){
	$enabledGlobal = ExternalModules::getSystemSetting($prefix,ExternalModules::KEY_ENABLED);
	$enabled = ExternalModules::getProjectSetting($prefix, $pid, ExternalModules::KEY_ENABLED);
	if(!$enabled && !$enabledGlobal){
		throw new Exception("The requested module is currently disabled on this project.");
	}
}

if (preg_match("/^https:\/\//", $page) || preg_match("/^http:\/\//", $page)) {
	header( 'Location: '.$page ) ;
}

$pageExtension = strtolower(pathinfo($page, PATHINFO_EXTENSION));
$pagePath = ExternalModules::getModuleDirectoryPath($prefix, $version) . "/$page" . ($pageExtension == '' ? ".php" : "");
if(!file_exists($pagePath)){
	throw new Exception("The specified page does not exist for this module. $pagePath");
}

if($pageExtension == 'md'){
	$Parsedown = new \Parsedown();
	$html = $Parsedown->text(file_get_contents($pagePath));

	$search = '<img src="';
	$replace = $search . ExternalModules::getModuleDirectoryUrl($prefix, $version);
	$html = str_replace($search, $replace, $html);

	echo $html;
}
else{
	// This variable is not used here, but is intended for use inside the file required below.
	$module = ExternalModules::getModuleInstance($prefix, $version);
	require_once $pagePath;
}