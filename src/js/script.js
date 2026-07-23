$(document).ready(function(){
  $(".tel").inputmask({"mask": "+38 (999) 99-99-999"});
  
  for(var i = 1; i <= 18; i++) {
    $(".photo-carousel").append(
      '<div class="gallery-img">' +
      '<a href="src/img/photos/p_' + i +'.jpg" rel="photo">' +
        // '<img src="src/img/photos/p_' + i + '.jpg" alt="p_' + i +'.jpg">' +
        '<div class="gal-img" style="background-image: url(src/img/photos/p_' + i + '.jpg)"></div>' +
      '</a>' +
    '</div>'
    );
  }

  $(".gallery-img a").colorbox();

  $('.video-carousel').slick({
    infinite: true,
    slidesToShow: 2,
    slidesToScroll: 1,
    responsive: [
      {
        breakpoint: 1024,
        settings: {
          slidesToShow: 1,
          arrows: true
        }
      }
    ]
  });

  $('.photo-carousel').slick({
    infinite: true,
    slidesToShow: 4,
    slidesToScroll: 1,
    rows: 2,
    autoplay: true,
    autoplaySpeed: 1000,
    pauseOnHover: true,
    responsive: [
      {
        breakpoint: 1024,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
          infinite: true,
          dots: true
        }
      },
      {
        breakpoint: 992,
        settings: {
          rows: 1,
          slidesToShow: 2,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 480,
        settings: {
          rows: 1,
          slidesToShow: 1,
          slidesToScroll: 1,
          arrows: true
        }
      }
    ]
  });

  $('.reviews-carousel').slick({
    infinite: true,
    slidesToShow: 1,
    slidesToScroll: 1,
    rows: 1,
    autoplay: true,
    pauseOnHover: true,
  });

  var nav = $('.menu-header');
 
	$(window).scroll(function () {
		if ($(this).scrollTop() > 80) {
			nav.addClass("fixed-top");
		} else {
			nav.removeClass("fixed-top");
		}
	});

  $("#navbarNavAltMarkup a").on('click', function(event) {
    if (this.hash !== "") {
      event.preventDefault();

      var hash = this.hash;

      $('html, body').animate({
        scrollTop: $(hash).offset().top
      }, 800, function(){});
    }  
  });

  $(".btn-pr-srv").on("click", function(){
    var a = $(this).attr("id").match(/\d+/);
    var auto = ["Клас Mini", "Клас Sedan", "Клас Crossover", "Клас Jeep"];
    $("#auto").val(auto[a-1]);
  });
});

window.onscroll = function() {
  if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
    document.getElementById("btn-to-top").style.display = "block";
  } else {
    document.getElementById("btn-to-top").style.display = "none";
  }
};

$('#btn-to-top').on("click", function(event) {
  $('html, body').animate({
    scrollTop: $('#banner').offset().top
  }, 800, function(){

  });
});