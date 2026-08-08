$(".btn-service").on("click", function() {
	$("#servicesModalLabel").empty();
	$("#servicesModal .modal-body").empty();
	var a = $(this).attr("id").match(/\d+/);

	if (a[0] == 1) {
		$("#servicesModalLabel").text("Полірування кузова авто");
		$("#servicesModal .modal-body").append('<p><strong>CarLux Detailing</strong> - це сучасна детейлінг студія із досвідом роботи понад 5 років.' +
			'<p><strong>Якість послуг</strong> гарантована нашим багаторічним досвідом роботи, та дотриманням європейських стандартів виконання.</p>' +
			'<p>Детальніше про CarLux Detailing:</p>' +
			'<p><strong>Наша робота</strong></p>' +
			'<li>щотижня виконуємо більше 25 комплексів замовлень з хімчистки та полірування авто</li>' +
			'<p><strong>Наш персонал</strong></p>' +
			'<li>Ми, це команда висококваліфікованих майстрів, які знають свою справу, використовуємо професійний інструмент, якісну хімію та власний досвід, щоб здивувати Вас, та довести що може бути не просто добре, а краще ніж Ви можете собі уявити.</li>' +
			'<p><strong>Наш інвентар та обладнання</strong></p>' +
			'<li>Використовуємо тільки професійну техніку та хімічні засоби від провідних світових виробників</li>' +
			'<p><strong>Увага!</strong> Ми використовуємо виключно гіпоалергенну хімію, що не містить шкіливих для здоров’я речовин.</p>' +
			'</p>&nbsp;</p>' +
			'<div class="fm text_box"><table align="center" border="1" cellpadding="5" cellspacing="0" dir="ltr" style="width:100%;"><colgroup><col /><col /></colgroup><tbody><tr><td style="text-align: center;"><strong>Категорія авто</strong></td><td style="text-align: center;"><strong>Вартість, грн</strong></td></tr><tr><td style="text-align: center;">Mini</td><td style="text-align: center;">6900</td></tr><tr><td style="text-align: center;">Sedan</td><td style="text-align: center;">7900</td></tr><tr><td style="text-align: center;">Crossover</td><td style="text-align: center;">8900</td></tr><tr><td style="text-align: center;">Jeep</td><td style="text-align: center;">9900</td></tr></tbody></table></div>');
	} else if (a[0] == 2) {
		$("#servicesModalLabel").text("Хімчистка салону авто");
		$("#servicesModal .modal-body").append('<strong>Хімчистка авто|CarLux</strong> - це сучасна технологія хімчистки яка передбачає використання професійної техніки та безпечних для навколішнього серидовища хімічних засобів вироблених у Німеччині та Італії. ' +
			'<p><strong>Хімчистка салону авто|CarLux</strong> - це чистка, дезінфекція та видалення плям, бруду, методом глибокої чистки внутрішньої та зовнішньої основи тканини оббивки та поролону.' +
			'Чому варто довірити професіоналам CarLux.</p>' +
			'<p><strong>Хімчистка салону</strong> - це складний процес, який не варто робити власноруч. Хімічні засоби, якими Ви зробите чистку, поглине тканина. Так Ви тільки пошкодите салон авто та спричините неприємний запах, випари хімічних засобів та шкоду Вашому здоровю.</p>' +
			'<p><strong>Як ми працюємо ::</p></strong>' +
			'<li><strong>1. Попередній запис</strong> – Клієнт прибуває на хімчистку салону у час згідно запису.</li>' +
			'<li><strong>2. Термін виконання</strong> – персонал якісно виконнає хімчистку від 4-6 год.</li>' +
			'<li><strong>3. Результат</strong> – салон авто сухий та чистий, плями видаленні, запах відсутній.</li>' +
			'<li><strong>4. Повна мийка авто у подарунок.</strong></li>' +
			'<p>&nbsp;</p>' +
			'<div class="fm text_box"><table align="center" border="1" cellpadding="5" cellspacing="0" dir="ltr" style="width:100%;"><colgroup><col /><col /></colgroup><tbody><tr><td style="text-align: center;"><strong>Категорія авто</strong></td><td style="text-align: center;"><strong>Вартість, грн</strong></td></tr><tr><td style="text-align: center;">Mini</td><td style="text-align: center;">5000</td></tr><tr><td style="text-align: center;">Sedan</td><td style="text-align: center;">6000</td></tr><tr><td style="text-align: center;">Crossover</td><td style="text-align: center;">7000</td></tr><tr><td style="text-align: center;">Jeep</td><td style="text-align: center;">8000</td></tr></tbody></table></div>');
	} else {
		console.log("Error. Please, try again.")
	}
});