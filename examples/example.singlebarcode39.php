<?php   
/* CAT:Barcode */

/* pChart library inclusions */
require_once("bootstrap.php");

use pChart\{
	pDraw,	
	pBarcode39
};

/* String to be written on the barcode */
$String = "This is a test";

/* Retrieve the barcode projected size */
$Settings = ["ShowLegend"=>TRUE,"DrawArea"=>TRUE];

/* Create the barcode 39 object */
/* that was the most elegant way I was able to figure out and keep these classes consistent */
$Size = (new pBarcode39(new pDraw(1,1)))->getSize($String, $Settings);

/* Create the pChart object */
$myPicture = new pDraw($Size["Width"], $Size["Height"]);

/* Set the font to use */
$myPicture->setFontProperties(["FontName"=>"pChart/fonts/GeosansLight.ttf"]);

/* Create the barcode 39 object */
$Barcode = new pBarcode39($myPicture);

/* Render the barcode */
$Barcode->draw($String,10,10,$Settings);

/* Render the picture (choose the best way) */
$myPicture->autoOutput("temp/example.singlebarcode39.png");

?>