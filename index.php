<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome | PG Life - Student & Professional Housing</title>

    <?php
    include "includes/head_links.php";
    ?>
    <link href="css/home.css?v=<?= time() ?>" rel="stylesheet" />
</head>

<body>
    <?php
    include "includes/header.php";
    ?>

    <div class="main-content">
        <div class="banner-container">
            <h1 class="banner-title">Happiness per Square Foot</h1>
            <p class="banner-subtitle">Explore hand-picked and verified paying guest accommodations</p>

            <form id="search-form" action="property_list.php" method="GET">
                <div class="city-search">
                    <i class="fas fa-map-marker-alt" style="color: #64748b; margin-left: 4px;"></i>
                    <input type="text" class="input-city" id='city' name='city' placeholder="Enter city name (e.g. Delhi, Mumbai, Bangalore...)" required />
                    <button type="submit" class="btn-search">
                        <i class="fa fa-search"></i>
                        <span>Search</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="cities-section">
            <h2 class="city-heading">Major Cities</h2>
            <p class="city-subheading">Find comfortable living spaces in top education and corporate hubs</p>

            <div class="city-grid">
                <a href="property_list.php?city=Delhi" class="city-card-link">
                    <div class="city-card-item">
                        <div class="city-circle">
                            <img src="img/delhi.png" class="city-img" alt="Delhi" />
                        </div>
                        <div class="city-name">Delhi</div>
                        <div class="city-tag">North & South Campus PGs</div>
                    </div>
                </a>

                <a href="property_list.php?city=Mumbai" class="city-card-link">
                    <div class="city-card-item">
                        <div class="city-circle">
                            <img src="img/mumbai.png" class="city-img" alt="Mumbai" />
                        </div>
                        <div class="city-name">Mumbai</div>
                        <div class="city-tag">Bandra, Andheri & Sea Face</div>
                    </div>
                </a>

                <a href="property_list.php?city=Bangalore" class="city-card-link">
                    <div class="city-card-item">
                        <div class="city-circle">
                            <img src="img/bangalore.png" class="city-img" alt="Bangalore" />
                        </div>
                        <div class="city-name">Bangalore</div>
                        <div class="city-tag">Koramangala, HSR & Electronic City</div>
                    </div>
                </a>

                <a href="property_list.php?city=Hyderabad" class="city-card-link">
                    <div class="city-card-item">
                        <div class="city-circle">
                            <img src="img/hyderabad.png" class="city-img" alt="Hyderabad" />
                        </div>
                        <div class="city-name">Hyderabad</div>
                        <div class="city-tag">HITEC City, Gachibowli & Madhapur</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <?php
    include "includes/signup_modal.php";
    include "includes/login_modal.php";
    include "includes/footer.php";
    ?>

</body>

</html>
