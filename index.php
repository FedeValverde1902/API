<?php
$apiKey = "d6688c5d054087e82e02539b82b16a42";
$weather = null;
$forecast = null;
$error = "";

function fetchWeather($city, $apiKey) {
    $cityEncoded = urlencode($city);
    $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?q=$cityEncoded&appid=$apiKey&units=metric";
    $forecastUrl = "https://api.openweathermap.org/data/2.5/forecast?q=$cityEncoded&appid=$apiKey&units=metric";

    $weatherResponse = file_get_contents($weatherUrl);
    $forecastResponse = file_get_contents($forecastUrl);

    if ($weatherResponse && $forecastResponse) {
        $weatherData = json_decode($weatherResponse, true);
        $forecastData = json_decode($forecastResponse, true);

        if ($weatherData["cod"] == 200 && $forecastData["cod"] == "200") {
            return [$weatherData, $forecastData];
        }
    }
    return [null, null];
}

if (isset($_GET['city'])) {
    [$weather, $forecast] = fetchWeather($_GET['city'], $apiKey);
    if (!$weather) {
        $error = "City not found or API error.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🌍 World Weather</title>
    <style>
    body {
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        padding: 0;
        background: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1950&q=80') no-repeat center center fixed;
        background-size: cover;
        color: #fff;
    }

    .container {
        max-width: 900px;
        margin: auto;
        padding: 2rem;
        background: rgba(0, 0, 0, 0.5);
        border-radius: 1rem;
    }

    h1 {
        text-align: center;
        color: #ffffff;
        margin-bottom: 2rem;
    }

    form {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    form input[type="text"] {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 1rem;
        width: 60%;
        font-size: 1rem;
    }

    form input[type="submit"], .geo-btn {
        padding: 0.5rem 1rem;
        background: #007BFF;
        color: white;
        border: none;
        border-radius: 1rem;
        cursor: pointer;
        font-size: 1rem;
    }

    .geo-btn {
        display: block;
        margin: 0.5rem auto 1rem;
        background: #28a745;
    }

    .weather-box {
        text-align: center;
        margin-top: 2rem;
    }

    .weather-box img {
        width: 80px;
    }

    .favorites {
        text-align: center;
        margin-top: 1rem;
    }

    .favorites span {
        cursor: pointer;
        margin: 0.3rem;
        padding: 0.3rem 0.7rem;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 1rem;
        display: inline-block;
        font-size: 0.9rem;
    }

    .forecast {
        margin-top: 2rem;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 1rem;
    }

    .card {
        width: 150px;
        background: linear-gradient(135deg, #6fb1fc, #4364f7);
        color: white;
        border-radius: 1rem;
        padding: 1rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        text-align: center;
        transition: transform 0.2s ease-in-out;
    }

    .card:hover {
        transform: scale(1.05);
    }

    .card img {
        width: 60px;
        margin-bottom: 0.5rem;
    }

    .card h3 {
        margin: 0;
        font-size: 1.1rem;
    }

    .card p {
        margin: 0.3rem 0;
        font-size: 0.9rem;
    }

    .error {
        background-color: rgba(255, 77, 77, 0.9);
        padding: 1rem;
        margin-top: 1rem;
        border-radius: 0.5rem;
        text-align: center;
    }
</style>
</head>
<body>
<div class="container">
    <h1>🌍 World Weather</h1>

    <form method="GET">
        <input type="text" name="city" placeholder="Enter city..." id="cityInput" required>
        <input type="submit" value="Search">
    </form>

    <button class="geo-btn" onclick="getLocation()">📍 Use my location</button>

    <div class="favorites" id="favorites">
        <h3>⭐ Favorites</h3>
    </div>

    <?php if ($weather): ?>
        <?php
            $timezoneOffset = $weather['timezone']; // in seconds
            $localTime = gmdate("H:i", time() + $timezoneOffset);
        ?>
        <div class="weather-box">
            <h2><?= htmlspecialchars($weather['name']) ?>, <?= $weather['sys']['country'] ?></h2>
            <p>🕒 Local Time: <?= $localTime ?></p>
            <img src="https://openweathermap.org/img/wn/<?= $weather['weather'][0]['icon'] ?>@2x.png" alt="">
            <p><strong><?= $weather['main']['temp'] ?>°C</strong></p>
            <p><?= ucfirst($weather['weather'][0]['description']) ?></p>
            <p>💧 Humidity: <?= $weather['main']['humidity'] ?>%</p>
            <p>💨 Wind: <?= $weather['wind']['speed'] ?> m/s</p>
            <button onclick="addFavorite('<?= $weather['name'] ?>')">⭐ Add to Favorites</button>
        </div>

        <div class="forecast">
            <?php
            $shownDays = [];
            foreach ($forecast['list'] as $item) {
                $dt = new DateTime($item['dt_txt']);
                if ($dt->format('H') == '12') {
                    $day = $dt->format('D');
                    $date = $dt->format('M j');
                    if (in_array($day, $shownDays)) continue;
                    $shownDays[] = $day;
                    echo '<div class="card">';
                    echo "<h3>$day</h3>";
                    echo "<p>$date</p>";
                    echo "<img src='https://openweathermap.org/img/wn/{$item['weather'][0]['icon']}@2x.png' alt=''>";
                    echo "<p><strong>" . round($item['main']['temp_min']) . "° / " . round($item['main']['temp_max']) . "°C</strong></p>";
                    echo "<p>" . ucfirst($item['weather'][0]['description']) . "</p>";
                    echo '</div>';
                }
                if (count($shownDays) >= 5) break;
            }
            ?>
        </div>
    <?php elseif ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
</div>

<script>
    function addFavorite(city) {
        let favorites = JSON.parse(localStorage.getItem("favorites")) || [];
        if (!favorites.includes(city)) {
            favorites.push(city);
            localStorage.setItem("favorites", JSON.stringify(favorites));
            loadFavorites();
        }
    }

    function loadFavorites() {
        let favorites = JSON.parse(localStorage.getItem("favorites")) || [];
        const container = document.getElementById("favorites");
        container.innerHTML = "<h3>⭐ Favorites</h3>";
        favorites.forEach(city => {
            const el = document.createElement("span");
            el.textContent = city;
            el.onclick = () => {
                window.location.href = `?city=${encodeURIComponent(city)}`;
            };
            container.appendChild(el);
        });
    }

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(position => {
                const { latitude, longitude } = position.coords;
                fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${latitude}&lon=${longitude}&appid=<?= $apiKey ?>&units=metric`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.name) {
                            window.location.href = `?city=${encodeURIComponent(data.name)}`;
                        }
                    });
            });
        } else {
            alert("Geolocation not supported.");
        }
    }

    window.onload = loadFavorites;
</script>
</body>
</html>
