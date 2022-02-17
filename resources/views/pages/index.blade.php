@extends('app')

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
	integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
	crossorigin="" />
<style>
	:root {
		--bs-navbar-height: 64px;
	}

	html,
	body {
		background-color: var(--bs-dark, #333);
	}

	#map-container {
		width: 100%;
		height: calc(100vh - var(--bs-navbar-height));
		/* background: red; */
	}
</style>
@endsection

@section('script-head')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
	integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
	crossorigin=""></script>
@endsection

@section('main')
{{-- <x-navbar></x-navbar> --}}
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a href="/" class="navbar-brand">Faskes BPJS di Indonesia</a>
		<form class="row">
			<div class="col">
				<input type="text" name="q" placeholder="Cari Faskes" id="searchbar" class="form-control">
			</div>
			<div class="col">
				<select name="tipefaskes" id="tipefaskes" class="form-select">
					<option selected value="">Pilih Tipe Faskes</option>
					@foreach ($types as $item)
							<option value="{{$item}}">{{$item}}</option>
					@endforeach
				</select>
			</div>
			<div class="col-2">
				<button type="submit" style="width: 100%;" class="btn d-block btn-primary">Cari</button>
			</div>
		</form>
  </div>
</nav>

<main>
	<div id="map-container" />
</main>
@endsection

@section('script-body')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
	const map = L.map("map-container", {
		// center: [0, 0],
		center: [-6.9118, 107.6214],
		zoom: 14,
	});
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'}).addTo(map);

	let lat = -6.920164290834744,
			lng = 107.63331413269044,
			latNorth, latSouth, lngEast, lngWest;
	
	let deviceCoord = {lat, lng};
	let markers = [];

	const personIcon = L.icon({
		iconUrl: "{{asset('imgs/person_crop.png')}}",
		iconSize: [30,70],
		iconAnchor: [15, 60],
		tooltipAnchor: [15,-35],
	});
	let personMarker = L.marker([lat, lng], {
		icon: personIcon,
	});
	// console.log(personMarker);
	personMarker.bindTooltip("You").openTooltip();
	map.addLayer(personMarker);
	
	const form = document.querySelector("form");

	let polyLine = null;

	function fetchHospitals() {
		console.log("device", deviceCoord);
		const parameters = {
			lat: deviceCoord.lat || lat,
			lng: deviceCoord.lng || lng,
			lat_north: latNorth,
			lat_south: latSouth,
			lng_east: lngEast,
			lng_west: lngWest,
			q: form.q.value || "",
		}
		if(form.tipefaskes.value){
			parameters.tipefaskes = form.tipefaskes.value;
		}
		const queryUrl = Object.keys(parameters).map(x => {
			return x.concat("=", parameters[x] || "");
		}).join("&");


		axios.get("{{url('/api/hospitals')}}".concat("?", queryUrl)).then(res => {
			console.log(res);

			// Remove Markers
			for(const m of markers) {
				map.removeLayer(m);
			}
			markers = [];

			if(polyLine) {
				map.removeLayer(polyLine);
			}

			const hospitals = res.data;
			
			if(hospitals){
				// Point line to nearest facility
				const endpoints = [
					[deviceCoord.lat, deviceCoord.lng],
					[hospitals[0].lat, hospitals[0].lng],
					// ...hospitals.map(h => [h.lat, h.lng]),
				];

				polyLine = L.polyline(endpoints, {color: "red"});
				map.addLayer(polyLine);				
			}

			for(const h of hospitals){
				const lat = parseFloat(h.lat);
				const lng = parseFloat(h.lng);
				const marker = L.marker([lat,lng], {
					title: h.kodefaskes + " - " + h.namafaskes,
				});
				marker.bindTooltip(h.tipefaskes + " - " + h.namafaskes + " - " + h.distance + "km").openTooltip();

				map.addLayer(marker);
				markers.push(marker);
			}

		})
	}

	form.addEventListener("submit", e => {
		e.preventDefault();
		fetchHospitals();
	})

	map.on("load", e => {
		lat = map.getCenter().lat;
		lng = map.getCenter().lng;

		const {_southWest, _northEast} = map.getBounds();
		
		latNorth = _northEast.lat;
		lngEast = _northEast.lng;

		latSouth = _southWest.lat;
		lngEast = _southWest.lng;

		// console.log({lat,lng,latNorth,latSouth,lngEast,lngWest});
		fetchHospitals();
	});

	map.on("moveend", e => {
		lat = map.getCenter().lat;
		lng = map.getCenter().lng;

		const {_southWest, _northEast} = map.getBounds();
		
		latNorth = _northEast.lat;
		lngEast = _northEast.lng;

		latSouth = _southWest.lat;
		lngWest = _southWest.lng;

		console.log({lat,lng,latNorth,latSouth,lngEast,lngWest});

		// personMarker.setLatLng([lat, lng]);
		fetchHospitals();
	});
	map.on("move", e => {
		lat = map.getCenter().lat;
		lng = map.getCenter().lng;
		// personMarker.setLatLng([lat, lng]);
	});
	
	let firstTime = true;
	navigator.geolocation.watchPosition(pos => {
		console.log(pos);
		const lat = pos.coords.latitude, lng = pos.coords.longitude;
		
		deviceCoord.lat = lat;
		deviceCoord.lng = lng;
		
		personMarker.setLatLng([lat, lng]);
		if(firstTime){
			map.setView({lat, lng});
			firstTime = false;
		}
	}, () => {
		map.setView({lat: -6.91182, lng: 107.62141});
		// personMarker.setLatLng({lat: -6.91182, lng: 107.62141});
		map.removeLayer(personMarker)
	});

	window.map = map;
</script>
@endsection