<?php
function image_overlay_for_repost( $imageFile, $text, $targetFile, $minWidth=2000, $minHeight=1000 ) {
	$resource_logo = dirname( __FILE__ ). '/image_resources/UKM_logo.png';
	$resource_fade = dirname( __FILE__ ). '/image_resources/fade.png';
	$resource_font = dirname( __FILE__ ). '/image_resources/font/avantgarde-book.ttf';
	
	/************************************************************/
	/* CONFIG
	/************************************************************/
	$MIN_WIDTH = $minWidth;
	$MIN_HEIGHT = $minHeight;
	
	/************************************************************/
	/* CREATE PALETTES AND GET IMAGE INFOS
	/************************************************************/
	// TARGET IMAGE
	$image = new Imagick( $imageFile );
	$imageprops = $image->getImageGeometry();
	$image_width = $imageprops['width'];
	$image_height = $imageprops['height'];
	// THE FADE BENEATH THE LOGO
	$fade = new Imagick( $resource_fade );
	$fade->setImageColorspace( $image->getImageColorspace() ); 
	$fadeprops = $fade->getImageGeometry();
	$fade_width = $fadeprops['width'];
	$fade_height = $fadeprops['height'];
	// THE LOGO
	$logo = new Imagick( $resource_logo );
	$logo->setImageColorspace( $image->getImageColorspace() ); 
	$logoprops = $logo->getImageGeometry();
	$logo_width = $logoprops['width'];
	$logo_height = $logoprops['height'];
	// THE TEXT PALETTE
	$text_palette = new ImagickDraw();
	$text_palette->setFillColor('#ffffff');
	$text_palette->setFont( $resource_font );
	
	/************************************************************/
	/* SET THE TARGET IMAGE SIZES
	/************************************************************/
	$T_WIDTH = $image_width;
	$T_HEIGHT = $image_height;
	
	/************************************************************/
	/* MAKE SURE IMAGE IS WITHIN REQUIRED SIZE
	/************************************************************/
	if( $T_WIDTH < $MIN_WIDTH || $T_HEIGHT < $MIN_HEIGHT ) {
		$ratio = $image_width / $image_height;
		if ($ratio < 1) {
			$T_HEIGHT = $MIN_HEIGHT;
		    $T_WIDTH = $T_HEIGHT * $ratio;
		} else {
			$T_WIDTH = $MIN_WIDTH;
		    $T_HEIGHT = $T_WIDTH / $ratio;
		}
		$image->scaleImage($T_WIDTH, $T_HEIGHT);
	}
	
	/************************************************************/
	/* SCALE THE LOGO
	/************************************************************/
	$t_logo_width = $T_HEIGHT * 0.25;
	$t_logo_height = $t_logo_width / ($logo_width / $logo_height);
	$t_logo_offset = $T_HEIGHT * 0.05;
	$logo->scaleImage( $t_logo_width, $t_logo_height );
	
	/************************************************************/
	/* SCALE THE FADE
	/************************************************************/
	$t_fade_height = $t_logo_height*1.8;// + $t_logo_offset*2;
	$fade->scaleImage($T_WIDTH, $t_fade_height);
	
	/************************************************************/
	/* SCALE THE TEXT
	/************************************************************/
	// SET TEXT HEIGHT
	$TEXTHEIGHT = $T_HEIGHT * 0.08;
	$text_palette->setFontSize( $TEXTHEIGHT );
	// CALCULATE TEXT SIZE ON IMAGE
	$text_box = $image->queryFontMetrics( $text_palette, $text );
	$t_text_width = $text_box['textWidth'];
	$t_text_height = $text_box['textHeight'];
	$t_text_descender = $text_box['descender'];
	// TEXT OFFSETS
	$t_text_offset_bottom = $T_HEIGHT - ($t_logo_offset*1.4) - $t_text_descender;
	$t_text_offset_left = ($t_logo_offset*1.3) + $t_logo_width;
	
	/************************************************************/
	/* MERGE INTO ONE IMAGE
	/************************************************************/
	$image->compositeImage($fade, Imagick::COMPOSITE_DEFAULT, 0, ($T_HEIGHT-$t_fade_height)+$t_logo_offset);
	$image->compositeImage($logo, Imagick::COMPOSITE_DEFAULT, $t_logo_offset, $T_HEIGHT-$t_logo_offset-$t_logo_height);
	$image->annotateImage($text_palette, $t_text_offset_left, $t_text_offset_bottom, 0, $text);
	
	
	return $image->writeImage($targetFile);
}