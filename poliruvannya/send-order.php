<?php
	function clean_field($value) {
		$value = trim($value);
		$value = str_replace(["\r", "\n", "%0a", "%0d"], '', $value);
		// прибираємо "невидимі" юнікод-пробіли (nbsp, zero-width тощо),
		// які звичайний trim() не бачить — саме через них поле виглядає
		// порожнім у листі, але формально не дорівнює ''
		$value = preg_replace('/[\x{00A0}\x{200B}\x{200C}\x{200D}\x{FEFF}]+/u', '', $value);
		$value = trim($value);
		return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
	}

	function digits_count($value) {
		return strlen(preg_replace('/\D/', '', $value));
	}

	// Honeypot: приховане поле, яке бачать і заповнюють тільки боти
	if (!empty($_POST["website"])) {
		header('Location: index.html');
		exit;
	}

	$auto = isset($_POST["auto"]) ? clean_field($_POST["auto"]) : '';
	$service = isset($_POST["service"]) ? clean_field($_POST["service"]) : '';
	$name = isset($_POST["name"]) ? clean_field($_POST["name"]) : '';
	$phone_number = isset($_POST["phone_number"]) ? clean_field($_POST["phone_number"]) : '';

	if ($name === '' || $phone_number === '' || digits_count($phone_number) < 9) {
		header('Location: index.html');
		exit;
	}

	$to = "carlux.detailing.ua@gmail.com";
	$subject ="Хімчистка салону авто. Замовити хімчистку салону авто.";
	$headers = "From: Carlux <no-reply@carlux.com.ua>";
	$message = "Категорія: $auto \nВид хімчистки: $service \nІм'я замовника: $name \nТелефон замовника: $phone_number";
	mail($to, $subject, $message, $headers);
  	echo '<script type="text/javascript">
           window.location = "thank-you.html"
      </script>';
?>
