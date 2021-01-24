<?php	
	function inputContains($word) {
		global $input;

		return (strpos($input, $word) !== false);
	}

	function get($params) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $params['url']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		curl_close($ch);

		if ($params['return']) {
			if ($params['type'] === 'json') {
				return json_decode($response, true);
			} else {
				return $response;
			}
		}
	}

	function say($text, $ip) {
		if (!isset($ip) || $ip === '') {
			$ip = 'GOOGLE_HOME_DEVICE_LOCAL_IP';
		}

		$text = urlencode($text);

		get([
			'url' => 'RASPBERRY_PI_GOOGLE_HOME_NOTIFIER/say.php?input=ip:'.$ip.',text:'.$text,
			'return' => false
		]);
	}

	$input = strtolower($_GET['input']);

	if (inputContains('laundry')) {
		function sayLaundryState($machine, $label) {
			if ($machine['status'] === 'running') {
				say($label.' has '.$machine['minutes_remaining'].' minutes remaining');
			} else {
				say($label.' is '.$machine['status']);
			}
		}

		$laundryInfo = get([
			'url' => 'LAUNDRY_STATUS_INFO_API',
			'return' => true,
			'type' => 'json'
		]);

		if (
			$laundryInfo['MACHINE_ID_1']['status'] === 'available' && // Dryer 1
			$laundryInfo['MACHINE_ID_2']['status'] === 'available' && // Dryer 2
			$laundryInfo['MACHINE_ID_3']['status'] === 'available' && // Washer 1
			$laundryInfo['MACHINE_ID_4']['status'] === 'available' // Washer 2
		) {
			say('All washers and dryers are available!');
		} else {
			sayLaundryState($laundryInfo['MACHINE_ID_1'], 'Washer 1');
			sayLaundryState($laundryInfo['MACHINE_ID_2'], 'Washer 2');
			sayLaundryState($laundryInfo['MACHINE_ID_3'], 'Dryer 1');
			sayLaundryState($laundryInfo['MACHINE_ID_4'], 'Dryer 2');
		}
	} else if (inputContains('dinner')) {
		$options = [
			'Kimchi Fried Rice',
			'Vegan Mac and Cheese',
			'Salmon with Rice',
			'Chicken Cutlets',
			'Vegetarian Curry'
			'...'
		];

		$threeRandomKeys = array_rand($options, 3);

		say('How about '.$options[$threeRandomKeys[0]].'. or '.$options[$threeRandomKeys[1]].'. or maybe '.$options[$threeRandomKeys[2]].'?');
	}
?>
