<?php
/*
pBubble - class to draw bubble charts

Version     : 2.2.2-dev
Made by     : Jean-Damien POGOLOTTI
Maintainedby: Momchil Bozhinov
Last Update : 01/01/2018

This file can be distributed under the license you can find at:
http://www.pchart.net/license

You can find the whole class documentation on the pChart web site.
*/

namespace pChart;

define("BUBBLE_SHAPE_ROUND", 700001);
define("BUBBLE_SHAPE_SQUARE", 700002);

/* pBubble class definition */
class pBubble
{

	var $myPicture;
	
	/* Class creator */
	function __construct($pChartObject)
	{
		if (!($pChartObject instanceof pDraw)){
			die("pBubble needs a pDraw object. Please check the examples.");
		}
		
		$this->myPicture = $pChartObject;
	}

	/* Prepare the scale */
	function bubbleScale(array $DataSeries, array $WeightSeries)
	{		
		/* Parse each data series to find the new min & max boundaries to scale */
		$NewPositiveSerie = [];
		$NewNegativeSerie = [];
		$MaxValues = 0;
		$LastPositive = 0;
		$LastNegative = 0;
		foreach($DataSeries as $Key => $SerieName) {
			$SerieWeightName = $WeightSeries[$Key];
			$this->myPicture->myData->setSerieDrawable($SerieWeightName, FALSE);
			if (count($this->myPicture->myData->Data["Series"][$SerieName]["Data"]) > $MaxValues) {
				$MaxValues = count($this->myPicture->myData->Data["Series"][$SerieName]["Data"]);
			}

			foreach($this->myPicture->myData->Data["Series"][$SerieName]["Data"] as $Key => $Value) {
				if ($Value >= 0) {
					$BubbleBounds = $Value + $this->myPicture->myData->Data["Series"][$SerieWeightName]["Data"][$Key];
					if (!isset($NewPositiveSerie[$Key])) {
						$NewPositiveSerie[$Key] = $BubbleBounds;
					} elseif ($NewPositiveSerie[$Key] < $BubbleBounds) {
						$NewPositiveSerie[$Key] = $BubbleBounds;
					}

					$LastPositive = $BubbleBounds;
				} else {
					$BubbleBounds = $Value - $this->myPicture->myData->Data["Series"][$SerieWeightName]["Data"][$Key];
					if (!isset($NewNegativeSerie[$Key])) {
						$NewNegativeSerie[$Key] = $BubbleBounds;
					} elseif ($NewNegativeSerie[$Key] > $BubbleBounds) {
						$NewNegativeSerie[$Key] = $BubbleBounds;
					}

					$LastNegative = $BubbleBounds;
				}
			}
		}

		/* Check for missing values and all the fake positive serie */
		if (!empty($NewPositiveSerie))
		{
			for ($i = 0; $i < $MaxValues; $i++) {
				if (!isset($NewPositiveSerie[$i])) {
					$NewPositiveSerie[$i] = $LastPositive;
				}
			}

			$this->myPicture->myData->addPoints($NewPositiveSerie, "BubbleFakePositiveSerie");
		}

		/* Check for missing values and all the fake negative serie */
		if (!empty($NewNegativeSerie))
		{
			for ($i = 0; $i < $MaxValues; $i++) {
				if (!isset($NewNegativeSerie[$i])) {
					$NewNegativeSerie[$i] = $LastNegative;
				}
			}

			$this->myPicture->myData->addPoints($NewNegativeSerie, "BubbleFakeNegativeSerie");
		}
	}

	/* Prepare the scale */
	function drawBubbleChart(array $DataSeries, array $WeightSeries, array $Format = [])
	{
		$ForceAlpha = VOID;
		$DrawBorder = TRUE;
		$BorderWidth = 1;
		$Shape = BUBBLE_SHAPE_ROUND;
		$Surrounding = NULL;
		$BorderR = 0;
		$BorderG = 0;
		$BorderB = 0;
		$BorderAlpha = 30;
		$RecordImageMap = FALSE;
		
		/* Override defaults */
		extract($Format);
				
		$Data = $this->myPicture->myData->Data["Series"];
		$Orientation = $this->myPicture->myData->Data["Orientation"];
		
		if (isset($Data["BubbleFakePositiveSerie"])) {
			$this->myPicture->myData->setSerieDrawable("BubbleFakePositiveSerie", FALSE);
		}

		if (isset($Data["BubbleFakeNegativeSerie"])) {
			$this->myPicture->myData->setSerieDrawable("BubbleFakeNegativeSerie", FALSE);
		}

		$this->myPicture->myData->resetSeriesColors();
		list($XMargin, $XDivs) = $this->myPicture->myData->scaleGetXSettings();
		
		if ($XDivs == 0) {
			$XStep = 0;
		} else {
			if ($Orientation == SCALE_POS_LEFTRIGHT) {
				$XStep = ($this->myPicture->GraphAreaXdiff - $XMargin * 2) / $XDivs;		
			} elseif ($Orientation == SCALE_POS_TOPBOTTOM) {
				$XStep = ($this->myPicture->GraphAreaYdiff - $XMargin * 2) / $XDivs;
			}
		}

		foreach($DataSeries as $Key => $SerieName) {
			
			$X = $this->myPicture->GraphAreaX1 + $XMargin;
			$Y = $this->myPicture->GraphAreaY1 + $XMargin;
			
			$Color = $this->myPicture->myData->Palette[$Key];
			if ($ForceAlpha != VOID) {
				$Color["Alpha"] = $ForceAlpha;
			}

			if ($DrawBorder) {
				if ($BorderWidth != 1) {
					if ($Surrounding != NULL) {
						$BorderR = $Color["R"] + $Surrounding;
						$BorderG = $Color["G"] + $Surrounding;
						$BorderB = $Color["B"] + $Surrounding;
					} 
					if ($ForceAlpha != VOID) {
						$BorderAlpha = $ForceAlpha / 2;
					}

					$BorderColor = ["R" => $BorderR,"G" => $BorderG,"B" => $BorderB,"Alpha" => $BorderAlpha];
				} else {
					$Color["BorderAlpha"] = $BorderAlpha;
					if ($Surrounding != NULL) {
						$Color["BorderR"] = $Color["R"] + $Surrounding;
						$Color["BorderG"] = $Color["G"] + $Surrounding;
						$Color["BorderB"] = $Color["B"] + $Surrounding;
					} else {
						$Color["BorderR"] = $BorderR;
						$Color["BorderG"] = $BorderG;
						$Color["BorderB"] = $BorderB;
					}

					if ($ForceAlpha != VOID) {
						$Color["BorderAlpha"] = $ForceAlpha / 2;
					}
				}
			}
			
			if ($RecordImageMap) {
				$SerieDescription = (isset($Data[$SerieName]["Description"])) ? $Data[$SerieName]["Description"] : $SerieName;
				$ImageMapColor = $this->myPicture->toHTMLColor($Color["R"], $Color["G"], $Color["B"]);
			}

			foreach($Data[$SerieName]["Data"] as $iKey => $Point) {
				
				$DataWeightSeries = $Data[$WeightSeries[$Key]]["Data"][$iKey];
				$Weight = $this->myPicture->scaleComputeYSingle($Point + $DataWeightSeries, ["AxisID" => $Data[$SerieName]["Axis"]]);
				$Pos = $this->myPicture->scaleComputeYSingle($Point, ["AxisID" => $Data[$SerieName]["Axis"]]);
				$Radius = floor(abs($Pos - $Weight) / 2);
				
				if ($Orientation == SCALE_POS_LEFTRIGHT) {

					$Y = floor($Pos);
					if ($Shape == BUBBLE_SHAPE_SQUARE) {
						($RecordImageMap) AND $this->myPicture->addToImageMap("RECT", floor($X - $Radius).",".floor($Y - $Radius).",".floor($X + $Radius).",".floor($Y + $Radius), $ImageMapColor, $SerieDescription, $DataWeightSeries);
						($BorderWidth != 1) AND	$this->myPicture->drawFilledRectangle($X - $Radius - $BorderWidth, $Y - $Radius - $BorderWidth, $X + $Radius + $BorderWidth, $Y + $Radius + $BorderWidth, $BorderColor);
						$this->myPicture->drawFilledRectangle($X - $Radius, $Y - $Radius, $X + $Radius, $Y + $Radius, $Color);
					} elseif ($Shape == BUBBLE_SHAPE_ROUND) {
						($RecordImageMap) AND $this->myPicture->addToImageMap("CIRCLE", floor($X).",".floor($Y).",".floor($Radius), $ImageMapColor, $SerieDescription, $DataWeightSeries);
						($BorderWidth != 1) AND	$this->myPicture->drawFilledCircle($X, $Y, $Radius + $BorderWidth, $BorderColor);
						$this->myPicture->drawFilledCircle($X, $Y, $Radius, $Color);
					}

					$X = $X + $XStep;
					
				} elseif ($Orientation == SCALE_POS_TOPBOTTOM) {

					$X = floor($Pos);
					if ($Shape == BUBBLE_SHAPE_SQUARE) {
						($RecordImageMap) AND $this->myPicture->addToImageMap("RECT", floor($X - $Radius).",".floor($Y - $Radius).",".floor($X + $Radius).",".floor($Y + $Radius), $ImageMapColor, $SerieDescription, $DataWeightSeries);
						($BorderWidth != 1) AND	$this->myPicture->drawFilledRectangle($X - $Radius - $BorderWidth, $Y - $Radius - $BorderWidth, $X + $Radius + $BorderWidth, $Y + $Radius + $BorderWidth, $BorderColor);
						$this->myPicture->drawFilledRectangle($X - $Radius, $Y - $Radius, $X + $Radius, $Y + $Radius, $Color);
					} elseif ($Shape == BUBBLE_SHAPE_ROUND) {
						($RecordImageMap) AND $this->myPicture->addToImageMap("CIRCLE", floor($X).",".floor($Y).",".floor($Radius), $ImageMapColor, $SerieDescription, $DataWeightSeries);
						($BorderWidth != 1) AND	$this->myPicture->drawFilledCircle($X, $Y, $Radius + $BorderWidth, $BorderColor);
						$this->myPicture->drawFilledCircle($X, $Y, $Radius, $Color);
					}

					$Y = $Y + $XStep;
				}
			}
		}
	}

	function writeBubbleLabel(string $SerieName, string $SerieWeightName, int $Point, array $Format = [])
	{
		$Data = $this->myPicture->myData->Data;
		
		if (!isset($Data["Series"][$SerieName]) || !isset($Data["Series"][$SerieWeightName])) {
			throw pException::BubbleInvalidInputException("Serie name or Weight is invalid!");
		}

		list($XMargin, $XDivs) = $this->myPicture->myData->scaleGetXSettings();
		
		$AxisID = $Data["Series"][$SerieName]["Axis"];
		$Value = $Data["Series"][$SerieName]["Data"][$Point];
		$Pos = $this->myPicture->scaleComputeYSingle($Value, ["AxisID" => $AxisID]);
		$Value = $this->myPicture->scaleFormat($Value, $Data["Axis"][$AxisID]["Display"], $Data["Axis"][$AxisID]["Format"], $Data["Axis"][$AxisID]["Unit"]);
		$Description = (isset($Data["Series"][$SerieName]["Description"])) ? $Data["Series"][$SerieName]["Description"] : "No description";
		$Abscissa = (isset($Data["Abscissa"]) && isset($Data["Series"][$Data["Abscissa"]]["Data"][$Point])) ? $Data["Series"][$Data["Abscissa"]]["Data"][$Point]." : " : "";
		$Series = ["Format" => $Data["Series"][$SerieName]["Color"],"Caption" => $Abscissa . $Value . " / " . $Data["Series"][$SerieWeightName]["Data"][$Point]];
		
		$X = $this->myPicture->GraphAreaX1 + $XMargin;
		$Y = $this->myPicture->GraphAreaY1 + $XMargin;
		
		if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
			$XStep = ($XDivs == 0) ? 0 : ($this->myPicture->GraphAreaXdiff - $XMargin * 2) / $XDivs;
			$X = floor($X + $Point * $XStep);
			$Y = floor($Pos);
		} else {
			$YStep = ($XDivs == 0) ? 0 :($this->myPicture->GraphAreaYdiff - $XMargin * 2) / $XDivs;
			$X = floor($Pos);
			$Y = floor($Y + $Point * $YStep);
		}
		
		$DrawPoint = isset($Format["DrawPoint"]) ? $Format["DrawPoint"] : LABEL_POINT_BOX;
		if ($DrawPoint == LABEL_POINT_CIRCLE) {
			$this->myPicture->drawFilledCircle($X, $Y, 3, ["R" => 255,"G" => 255,"B" => 255,"BorderR" => 0,"BorderG" => 0,"BorderB" => 0]);
		} elseif ($DrawPoint == LABEL_POINT_BOX) {
			$this->myPicture->drawFilledRectangle($X - 2, $Y - 2, $X + 2, $Y + 2, ["R" => 255,"G" => 255,"B" => 255,"BorderR" => 0,"BorderG" => 0,"BorderB" => 0]);
		}

		$this->myPicture->drawLabelBox($X, $Y - 3, $Description, $Series, $Format);
	}
}

?>