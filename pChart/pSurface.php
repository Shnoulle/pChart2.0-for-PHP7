<?php
/*
pSurface - class to draw surface charts

Version     : 2.1.4
Made by     : Jean-Damien POGOLOTTI
Last Update : 19/01/2014

This file can be distributed under the license you can find at :

http://www.pchart.net/license

You can find the whole class documentation on the pChart web site.
*/

namespace pChart;

define("UNKNOWN", 0.123456789);
define("IGNORED", -1);
define("LABEL_POSITION_LEFT", 880001);
define("LABEL_POSITION_RIGHT", 880002);
define("LABEL_POSITION_TOP", 880003);
define("LABEL_POSITION_BOTTOM", 880004);

/* pStock class definition */
class pSurface
{
	var $GridSizeX;
	var $GridSizeY;
	var $Points = [];
	var $myPicture;
	
	/* Class creator */
	function __construct($pChartObject)
	{
		if (!($pChartObject instanceof pDraw)){
			die("pPie needs a pDraw object. Please check the examples.");
		}
		
		$this->myPicture = $pChartObject;
		#$this->GridSize = 10; # UNUSED
	}

	/* Define the grid size and initialise the 2D matrix */
	function setGrid($XSize = 10, $YSize = 10)
	{
		for ($X = 0; $X <= $XSize; $X++) {
			for ($Y = 0; $Y <= $YSize; $Y++) {
				$this->Points[$X][$Y] = UNKNOWN;
			}
		}

		$this->GridSizeX = $XSize;
		$this->GridSizeY = $YSize;
	}

	/* Add a point on the grid */
	function addPoint($X, $Y, $Value, $Force = TRUE)
	{
		if ($X < 0 || $X > $this->GridSizeX) {
			return 0;
		}

		if ($Y < 0 || $Y > $this->GridSizeY) {
			return 0;
		}

		if ($this->Points[$X][$Y] == UNKNOWN || $Force) {
			$this->Points[$X][$Y] = $Value;
		} elseif ($this->Points[$X][$Y] == UNKNOWN) {
			$this->Points[$X][$Y] = $Value;
		} else {
			$this->Points[$X][$Y] = ($this->Points[$X][$Y] + $Value) / 2;
		}
	}

	/* Write the X labels */
	function writeXLabels(array $Format = [])
	{
		$R = $this->myPicture->FontColorR;
		$G = $this->myPicture->FontColorG;
		$B = $this->myPicture->FontColorB;
		$Alpha = $this->myPicture->FontColorA;
		$Angle = 0;
		$Padding = 5;
		$Position = LABEL_POSITION_TOP;
		$Labels = NULL;
		$CountOffset = 0;
		
		/* Override defaults */
		extract($Format);
		
		if ($Labels != NULL && !is_array($Labels)) {
			$Labels = [$Labels];
		}

		$X0 = $this->myPicture->GraphAreaX1;
		$XSize = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1) / ($this->GridSizeX + 1);
		$Settings = ["Angle" => $Angle,"R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha];
		if ($Position == LABEL_POSITION_TOP) {
			$YPos = $this->myPicture->GraphAreaY1 - $Padding;
			$Settings["Align"] = ($Angle == 0) ? TEXT_ALIGN_BOTTOMMIDDLE : TEXT_ALIGN_MIDDLELEFT;
			
		} elseif ($Position == LABEL_POSITION_BOTTOM) {
			$YPos = $this->myPicture->GraphAreaY2 + $Padding;
			$Settings["Align"] = ($Angle == 0) ? TEXT_ALIGN_TOPMIDDLE : TEXT_ALIGN_MIDDLERIGHT;
			
		} else {
			return -1;
		}

		for ($X = 0; $X <= $this->GridSizeX; $X++) {
			$XPos = floor($X0 + $X * $XSize + $XSize / 2);
			$Value = ($Labels == NULL || !isset($Labels[$X])) ? $X + $CountOffset : $Labels[$X];
			$this->myPicture->drawText($XPos, $YPos, $Value, $Settings);
		}
	}

	/* Write the Y labels */
	function writeYLabels(array $Format = [])
	{
		$R = $this->myPicture->FontColorR;
		$G = $this->myPicture->FontColorG;
		$B = $this->myPicture->FontColorB;
		$Alpha = $this->myPicture->FontColorA;
		$Angle = 0;
		$Padding = 5;
		$Position = LABEL_POSITION_LEFT;
		$Labels = NULL;
		$CountOffset = 0;
		
		/* Override defaults */
		extract($Format);
		
		if ($Labels != NULL && !is_array($Labels)) {
			$Labels = [$Labels];
		}

		$Y0 = $this->myPicture->GraphAreaY1;
		$YSize = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1) / ($this->GridSizeY + 1);
		$Settings = ["Angle" => $Angle,"R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha];
		
		if ($Position == LABEL_POSITION_LEFT) {
			$XPos = $this->myPicture->GraphAreaX1 - $Padding;
			$Settings["Align"] = TEXT_ALIGN_MIDDLERIGHT;
		} elseif ($Position == LABEL_POSITION_RIGHT) {
			$XPos = $this->myPicture->GraphAreaX2 + $Padding;
			$Settings["Align"] = TEXT_ALIGN_MIDDLELEFT;
		} else {
			return -1;
		}

		for ($Y = 0; $Y <= $this->GridSizeY; $Y++) {
			$YPos = floor($Y0 + $Y * $YSize + $YSize / 2);
			$Value = ($Labels == NULL || !isset($Labels[$Y])) ? $Y + $CountOffset : $Labels[$Y];
			$this->myPicture->drawText($XPos, $YPos, $Value, $Settings);
		}
	}

	/* Draw the area arround the specified Threshold */
	function drawContour($Threshold, array $Format = [])
	{
		$R = 0;
		$G = 0;
		$B = 0;
		$Alpha = 100;
		$Ticks = 3;
		$Padding = 0;
		
		/* Override defaults */
		extract($Format);
		
		$X0 = $this->myPicture->GraphAreaX1;
		$Y0 = $this->myPicture->GraphAreaY1;
		$XSize = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1) / ($this->GridSizeX + 1);
		$YSize = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1) / ($this->GridSizeY + 1);
		$Color = ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha,"Ticks" => $Ticks];
		for ($X = 0; $X <= $this->GridSizeX; $X++) {
			for ($Y = 0; $Y <= $this->GridSizeY; $Y++) {
				$Value = $this->Points[$X][$Y];
				if ($Value != UNKNOWN && $Value != IGNORED && $Value >= $Threshold) {
					$X1 = floor($X0 + $X * $XSize) + $Padding;
					$Y1 = floor($Y0 + $Y * $YSize) + $Padding;
					$X2 = floor($X0 + $X * $XSize + $XSize);
					$Y2 = floor($Y0 + $Y * $YSize + $YSize);
					if ($X > 0 && $this->Points[$X - 1][$Y] != UNKNOWN && $this->Points[$X - 1][$Y] != IGNORED && $this->Points[$X - 1][$Y] < $Threshold){
						$this->myPicture->drawLine($X1, $Y1, $X1, $Y2, $Color);
					}
					if ($Y > 0 && $this->Points[$X][$Y - 1] != UNKNOWN && $this->Points[$X][$Y - 1] != IGNORED && $this->Points[$X][$Y - 1] < $Threshold){
						$this->myPicture->drawLine($X1, $Y1, $X2, $Y1, $Color);
					}
					if ($X < $this->GridSizeX && $this->Points[$X + 1][$Y] != UNKNOWN && $this->Points[$X + 1][$Y] != IGNORED && $this->Points[$X + 1][$Y] < $Threshold){
						$this->myPicture->drawLine($X2, $Y1, $X2, $Y2, $Color);
					}
					if ($Y < $this->GridSizeY && $this->Points[$X][$Y + 1] != UNKNOWN && $this->Points[$X][$Y + 1] != IGNORED && $this->Points[$X][$Y + 1] < $Threshold){
						$this->myPicture->drawLine($X1, $Y2, $X2, $Y2, $Color);
					}
				}
			}
		}
	}

	/* Draw the surface chart */
	function drawSurface(array $Format = [])
	{
		$Palette = NULL;
		$ShadeR1 = 77;
		$ShadeG1 = 205;
		$ShadeB1 = 21;
		$ShadeA1 = 40;
		$ShadeR2 = 227;
		$ShadeG2 = 135;
		$ShadeB2 = 61;
		$ShadeA2 = 100;
		$Border = FALSE;
		$BorderR = 0;
		$BorderG = 0;
		$BorderB = 0;
		$Surrounding = -1;
		$Padding = 1;
		
		/* Override defaults */
		extract($Format);
		
		$X0 = $this->myPicture->GraphAreaX1;
		$Y0 = $this->myPicture->GraphAreaY1;
		$XSize = ($this->myPicture->GraphAreaX2 - $this->myPicture->GraphAreaX1) / ($this->GridSizeX + 1);
		$YSize = ($this->myPicture->GraphAreaY2 - $this->myPicture->GraphAreaY1) / ($this->GridSizeY + 1);
		for ($X = 0; $X <= $this->GridSizeX; $X++) {
			for ($Y = 0; $Y <= $this->GridSizeY; $Y++) {
				$Value = $this->Points[$X][$Y];
				if ($Value != UNKNOWN && $Value != IGNORED) {
					
					$X1 = floor($X0 + $X * $XSize) + $Padding;
					$Y1 = floor($Y0 + $Y * $YSize) + $Padding;
					$X2 = floor($X0 + $X * $XSize + $XSize);
					$Y2 = floor($Y0 + $Y * $YSize + $YSize);
					
					if ($Palette != NULL) {
						$R = (isset($Palette[$Value]) && isset($Palette[$Value]["R"])) ? $Palette[$Value]["R"] : 0;
						$G = (isset($Palette[$Value]) && isset($Palette[$Value]["G"])) ? $Palette[$Value]["G"] : 0;
						$B = (isset($Palette[$Value]) && isset($Palette[$Value]["B"])) ? $Palette[$Value]["B"] : 0;
						$Alpha = (isset($Palette[$Value]) && isset($Palette[$Value]["Alpha"])) ? $Palette[$Value]["Alpha"] : 1000;
						
					} else {
						$R = (($ShadeR2 - $ShadeR1) / 100) * $Value + $ShadeR1;
						$G = (($ShadeG2 - $ShadeG1) / 100) * $Value + $ShadeG1;
						$B = (($ShadeB2 - $ShadeB1) / 100) * $Value + $ShadeB1;
						$Alpha = (($ShadeA2 - $ShadeA1) / 100) * $Value + $ShadeA1;
					}

					$Settings = ["R" => $R,"G" => $G,"B" => $B,"Alpha" => $Alpha];
					
					if ($Border) {
						$Settings["BorderR"] = $BorderR;
						$Settings["BorderG"] = $BorderG;
						$Settings["BorderB"] = $BorderB;
					}

					if ($Surrounding != - 1) {
						$Settings["BorderR"] = $R + $Surrounding;
						$Settings["BorderG"] = $G + $Surrounding;
						$Settings["BorderB"] = $B + $Surrounding;
					}

					$this->myPicture->drawFilledRectangle($X1, $Y1, $X2 - 1, $Y2 - 1, $Settings);
				}
			}
		}
	}

	/* Compute the missing points */
	function computeMissing()
	{
		$Missing = [];
		for ($X = 0; $X <= $this->GridSizeX; $X++) {
			for ($Y = 0; $Y <= $this->GridSizeY; $Y++) {
				if ($this->Points[$X][$Y] == UNKNOWN) {
					$Missing[] = [$X, $Y];
				}
			}
		}

		shuffle($Missing);
		foreach($Missing as $Pos) {
			$X = $Pos[0];
			$Y = $Pos[1];
			if ($this->Points[$X][$Y] == UNKNOWN) {
				$NearestNeighbor = $this->getNearestNeighbor($X, $Y);
				$Value = 0;
				$Points = 0;
				for ($Xi = $X - $NearestNeighbor; $Xi <= $X + $NearestNeighbor; $Xi++) {
					for ($Yi = $Y - $NearestNeighbor; $Yi <= $Y + $NearestNeighbor; $Yi++) {
						if ($Xi >= 0 && $Yi >= 0 && $Xi <= $this->GridSizeX && $Yi <= $this->GridSizeY && $this->Points[$Xi][$Yi] != UNKNOWN && $this->Points[$Xi][$Yi] != IGNORED) {
							$Value = $Value + $this->Points[$Xi][$Yi];
							$Points++;
						}
					}
				}

				if ($Points != 0) {
					$this->Points[$X][$Y] = $Value / $Points;
				}
			}
		}
	}

	/* Return the nearest Neighbor distance of a point */
	function getNearestNeighbor($Xp, $Yp)
	{
		$Nearest = UNKNOWN;
		for ($X = 0; $X <= $this->GridSizeX; $X++) {
			for ($Y = 0; $Y <= $this->GridSizeY; $Y++) {
				if ($this->Points[$X][$Y] != UNKNOWN && $this->Points[$X][$Y] != IGNORED) {
					$DistanceX = max($Xp, $X) - min($Xp, $X);
					$DistanceY = max($Yp, $Y) - min($Yp, $Y);
					$Distance = max($DistanceX, $DistanceY);
					if ($Distance < $Nearest || $Nearest == UNKNOWN) {
						$Nearest = $Distance;
					}
				}
			}
		}

		return $Nearest;
	}
}

?>