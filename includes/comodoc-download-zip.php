<?php
define('WP_USE_THEMES', false);
include($_SERVER["DOCUMENT_ROOT"] .'/wp-load.php');
$zipFilename = (isset($_REQUEST['zip-filename']) ? $_REQUEST['zip-filename'] : 'documents');
$zip = new ZipArchive();
$zipname = $zipFilename .'-'. time().'.zip'; // Zip name
$zip->open($zipname,  ZipArchive::CREATE);
$docID = (isset($_REQUEST['document-id']) ? $_REQUEST['document-id'] : '');
$docCat = (isset($_REQUEST['document-cat']) ? $_REQUEST['document-cat'] : '');
$docLimit = (isset($_REQUEST['document-limit']) ? $_REQUEST['document-limit'] : -1);
$args = array('post_type'=>'document','post_status'=>'publish');
if ($docID) { 
	$args['p'] = $docID; 
} else {
	if ($docCat) { $args['tax_query'] = array(array('taxonomy'=>'document-cat','field'=>'slug','terms'=>$docCat)); }
	$args['posts_per_page'] = $docLimit;
}
$query = new WP_Query( $args );
$docArray = array();
if ($query->have_posts()) { 
	while ($query->have_posts()) {
		$query->the_post(); 
		$pid = get_the_ID();
		$meta = get_post_meta($pid);
		$docID = get_post_meta($pid,'comodoc-file-id',true);
		$filepath = get_attached_file($docID);
		$filename = basename (get_attached_file($docID));
		$docArray[] = array('path'=>$filepath, 'name'=>$filename);
	}
}
if (count($docArray) > 0) {
	foreach ($docArray as $file) {
		if (file_exists($file['path'])) {
			$zip->addFile($file['path'],$file['name']);  
		}
	}
	$zip->close();
	header('Content-Type: application/zip');
	header('Content-disposition: attachment; filename='.$zipname);
	header('Content-Length: ' . filesize($zipname));
	readfile($zipname);
}
?>