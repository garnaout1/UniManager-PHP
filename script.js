var map = L.map('map').setView([51.505, -0.09], 13);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
L.marker([51.505, -0.09]).addTo(map).bindPopup("<b>University Campus</b>").openPopup();
