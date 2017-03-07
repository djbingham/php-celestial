<?php
return [
	'factoryClass' => 'Celestial\\Module\\Validation\\Factory',
	'options' => array(
		'validators' => array(
			'comparison.contains' => 'Celestial\\Module\\Validation\\Validator\\Comparison\\ContainsValidator',
			'comparison.equal' => 'Celestial\\Module\\Validation\\Validator\\Comparison\\EqualValidator',
			'comparison.unique' => 'Celestial\\Module\\Validation\\Validator\\Comparison\\UniqueValidator',
			'number.greaterThan' => 'Celestial\\Module\\Validation\\Validator\\Number\\GreaterThanValidator',
			'number.integer' => 'Celestial\\Module\\Validation\\Validator\\Number\\IntegerValidator',
			'number.number' => 'Celestial\\Module\\Validation\\Validator\\Number\\NumberValidator',
			'number.lessThan' => 'Celestial\\Module\\Validation\\Validator\\Number\\LessThanValidator',
			'number.maximumDecimalPlaces' => 'Celestial\\Module\\Validation\\Validator\\Number\\MaximumDecimalPlacesValidator',
			'number.maximumDigits' => 'Celestial\\Module\\Validation\\Validator\\Number\\MaximumDigitsValidator',
			'text.text' => 'Celestial\\Module\\Validation\\Validator\\Text\\TextValidator',
			'text.maximumLength' => 'Celestial\\Module\\Validation\\Validator\\Text\\MaximumLengthValidator',
			'text.minimumLength' => 'Celestial\\Module\\Validation\\Validator\\Text\\MinimumLengthValidator'
		)
	)
];
