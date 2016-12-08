<?php
ini_set('display_errors', true);	
require_once('ukmwp_image_overlay.php');

/************************************************************/
/* SELECT INPUTS
/************************************************************/
$TEXT = 'Sogn og Fjordane Øst';
switch( $_GET['img'] ) {
	case 2:
		$INPUT = 'dev/HannaEllingseter.png';
		break;
	case 3:
		$INPUT = 'dev/Busk.jpg';
		break;
	case 4:
		$INPUT = 'dev/Fosen.jpg';
		break;
	default:
		$INPUT = 'dev/SF-2500.jpg';
}
/************************************************************/
/* OUTPUT
/************************************************************/
header('Content-type: image/jpg');
echo image_overlay_for_repost( $INPUT, $TEXT );