<?php
/* @var $this TemperatureController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Current Weather',
);
?>

<h1>Current Weather</h1>

<div id="weather">
<!-- This div will contain the weather record data -->
</div>
<?php

/**
 * The 'ajaxButton' method receives 3 arguments:
 * 1. 'Update Data' this is the button's label
 * 2. CControler::createUrl, this method receives relative URL as input and
 * 	  generates an absolute URL, for example CController::createUrl('temperature/getWeather')
 * 	  creates the following URL '/index.php?r=temperature/getWeather'
 * 	  it will create the URL to which the AJAX request will be sent
 * 3. array('update' => '#weather') This is an indicator that will tell yii to put the AJAX results
 * 	  into the <div id="weather"></div> element (#weather means the HTML tag with id="weather")
 */
echo CHtml::ajaxButton('Get Weather!',
	CController::createUrl('temperature/getWeather'),
	array('update' => '#weather')); ?>
