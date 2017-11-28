<?php
set_include_path('.' . PATH_SEPARATOR . get_include_path());
require_once '../../classes/ExternalModules.php';
require_once APP_PATH_DOCROOT.'Classes/Files.php';

$pid = @$_GET['pid'];
$edoc = $_POST['edoc'];

$doc_name = "";
if (($edoc) && (is_numeric($edoc))) {
	$ary = Files::getEdocContentsAttributes((integer) $edoc);
	$doc_name = $ary[1];
}

header('Content-type: application/json');
echo json_encode(array(
        'edoc_id' => $edoc,
        'doc_name' => $doc_name,
        'status' => 'success'
));

?>
