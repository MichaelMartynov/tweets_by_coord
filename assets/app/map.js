ymaps.ready(init);
var myMap;

function init(){
	myMap = new ymaps.Map("map", {
		center: [53.2166, 50.1880],
		zoom: 11
	}, {
		balloonMaxWidth: 200,
	});
	myMap.controls.add('zoomControl');

	myMap.events.add('click', function (e){
		if (!myMap.balloon.isOpen()) {
			var coords = e.get('coordPosition');
			myMap.balloon.open(coords, {
				contentHeader: 'Координаты',
				contentBody: [
					coords[0].toPrecision(6),
					coords[1].toPrecision(6)
				].join(', '),
				contentFooter: 'Загрузка фотографии...'
			});
			Twitter.getByPoint(coords[0], coords[1]);
		}
		else {
			myMap.balloon.close();
		}
	});

	myMap.events.add('contextmenu', function (e){
		if (myMap.balloon.isOpen())
			myMap.balloon.close();
	});
}

var Twitter = {

	getByPoint: function (latitude, longitude){

		$.ajax({
			url: '/map/search',
			data: {
				latitude: latitude,
				longitude: longitude
			},
			success: function (response){
				if (response.status == 'ok') {
					$('#twitter img').attr('src', response.image);
					$('#twitter #clickCoord').html('Координаты карты: ' + latitude + ' ' + longitude);
					$('#twitter #imageCoord').html(response.latitude ? ('Координаты изображения: ' + response.latitude + ' ' + response.longitude) : ' Фото не найдено');
					$('#twitter').modal({show: true});
				}
				myMap.balloon.close();
			}
		});
	}
};