<?php
	function clean_field($value) {
		$value = trim($value);
		$value = str_replace(["\r", "\n", "%0a", "%0d"], '', $value);
		return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
	}

	$phone_number = isset($_POST["phone_number"]) ? clean_field($_POST["phone_number"]) : '';

	if ($phone_number === '') {
		header('Location: index.html');
		exit;
	}

	$to = "carlux.detailing.ua@gmail.com";
	$subject = "Хімчистка салону авто. Замовити дзвінок.";
	$headers = "From: Carlux <no-reply@carlux.com.ua>";
	$message = "Телефон замовника: $phone_number";
	mail($to,$subject,$message,$headers);
	echo '<script type="text/javascript">
           window.location = "thank-you.html"
      </script>';
?>
