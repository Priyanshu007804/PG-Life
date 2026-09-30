<?php
session_start();
require "includes/database_connect.php";

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : NULL;
$property_id = isset($_GET['property_id']) ? (int)$_GET['property_id'] : 0;

if ($property_id <= 0) {
    header("Location: index.php");
    exit;
}

// Get property details — prepared statement
$stmt_1 = $conn->prepare("SELECT p.*, c.name as city_name FROM properties p INNER JOIN cities c ON p.city_id = c.id WHERE p.id = ?");
$stmt_1->bind_param("i", $property_id);
$stmt_1->execute();
$result_1 = $stmt_1->get_result();

if (!$result_1 || mysqli_num_rows($result_1) == 0) {
    echo "Property not found!";
    return;
}
$property = mysqli_fetch_assoc($result_1);
$stmt_1->close();

// Get interested users count and check if current user is interested
$stmt_2 = $conn->prepare("SELECT * FROM interested_users_properties WHERE property_id = ?");
$stmt_2->bind_param("i", $property_id);
$stmt_2->execute();
$result_2 = $stmt_2->get_result();
$interested_users = $result_2 ? mysqli_fetch_all($result_2, MYSQLI_ASSOC) : array();
$interested_count = count($interested_users);

$is_interested = false;
foreach ($interested_users as $iu) {
    if ($iu['user_id'] == $user_id) {
        $is_interested = true;
        break;
    }
}
$stmt_2->close();

// Calculate total rating
$total_rating = ($property['rating_clean'] + $property['rating_food'] + $property['rating_safety']) / 3;
$total_rating = round($total_rating, 1);

// Get property images with fallback
$property_images = glob("img/properties/" . $property_id . "/*");
if (empty($property_images)) {
    $property_images = glob("img/properties/1/*");
}
if (empty($property_images)) {
    $property_images = array("img/properties/1/1d4f0757fdb86d5f.jpg");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($property['name']) ?> | PG Life</title>

    <?php
    include "includes/head_links.php";
    ?>
    <link rel="stylesheet" href="css/property_detail.css?v=<?= time() ?>">
    <style>
        .is-interested-image {
            cursor: pointer;
            color: #e74c3c;
            font-size: 22px;
        }
    </style>
</head>

<body>
    <?php
    include "includes/header.php";
    ?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb py-2">
            <li class="breadcrumb-item">
                <a href="index.php">Home</a>
            </li>
            <li class="breadcrumb-item">
                <a href="property_list.php?city=<?= urlencode($property['city_name']) ?>"><?= htmlspecialchars($property['city_name']) ?></a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= htmlspecialchars($property['name']) ?>
            </li>
        </ol>
    </nav>

    <?php if (!empty($property_images)) { ?>
    <div id="property-images" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <?php for ($i = 0; $i < count($property_images); $i++) { ?>
                <li data-target="#property-images" data-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>"></li>
            <?php } ?>
        </ol>
        <div class="carousel-inner">
            <?php foreach ($property_images as $index => $img) { ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <img class="d-block w-100" src="<?= htmlspecialchars($img) ?>" alt="Property Image <?= $index + 1 ?>">
                </div>
            <?php } ?>
        </div>
        <a class="carousel-control-prev" href="#property-images" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#property-images" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
    <?php } ?>

    <div class="property-summary page-container">
        <div class="row no-gutters justify-content-between">
            <div class="star-container" title="<?= $total_rating ?>">
                <?php
                $rating = $total_rating;
                for ($i = 0; $i < 5; $i++) {
                    if ($rating >= $i + 0.8) {
                ?>
                        <i class="fas fa-star"></i>
                    <?php
                    } elseif ($rating >= $i + 0.3) {
                    ?>
                        <i class="fas fa-star-half-alt"></i>
                    <?php
                    } else {
                    ?>
                        <i class="far fa-star"></i>
                <?php
                    }
                }
                ?>
            </div>
            <div class="interested-container">
                <?php if ($is_interested) { ?>
                    <i class="is-interested-image fas fa-heart" property_id="<?= $property_id ?>"></i>
                <?php } else { ?>
                    <i class="is-interested-image far fa-heart" property_id="<?= $property_id ?>"></i>
                <?php } ?>
                <div class="interested-text">
                    <span class="interested-user-count"><?= $interested_count ?></span> interested
                </div>
            </div>
        </div>

        <div class="detail-container">
            <div class="property-name"><?= htmlspecialchars($property['name']) ?></div>
            <div class="property-address"><?= htmlspecialchars($property['address']) ?></div>
            <div class="property-gender">
                <?php
                if ($property['gender'] == "male") {
                ?>
                    <img src="img/male.png" alt="Male">
                <?php
                } elseif ($property['gender'] == "female") {
                ?>
                    <img src="img/female.png" alt="Female">
                <?php
                } else {
                ?>
                    <img src="img/unisex.png" alt="Unisex">
                <?php
                }
                ?>
            </div>
        </div>

        <div class="row no-gutters">
            <div class="rent-container col-6">
                <div class="rent">₹ <?= number_format($property['rent']) ?>/-</div>
                <div class="rent-unit">per month</div>
            </div>
            <div class="button-container col-6">
                <a href="mailto:support@pglife.com?subject=Inquiry for <?= urlencode($property['name']) ?>" class="btn btn-primary">Book Now</a>
            </div>
        </div>
    </div>

    <div class="property-amenities">
        <div class="page-container">
            <h1>Amenities</h1>
            <div class="row justify-content-between">
                <div class="col-md-auto">
                    <h5>Building</h5>
                    <div class="amenity-container">
                        <img src="img/amenities/powerbackup.svg" alt="Power Backup">
                        <span>Power backup</span>
                    </div>
                    <div class="amenity-container">
                        <img src="img/amenities/lift.svg" alt="Lift">
                        <span>Lift</span>
                    </div>
                    <div class="amenity-container">
                        <img src="img/amenities/cctv.svg" alt="CCTV">
                        <span>CCTV Security</span>
                    </div>
                </div>

                <div class="col-md-auto">
                    <h5>Common Area</h5>
                    <div class="amenity-container">
                        <img src="img/amenities/wifi.svg" alt="Wifi">
                        <span>High-Speed Wifi</span>
                    </div>
                    <div class="amenity-container">
                        <img src="img/amenities/tv.svg" alt="TV">
                        <span>TV & Lounge</span>
                    </div>
                    <div class="amenity-container">
                        <img src="img/amenities/rowater.svg" alt="Water Purifier">
                        <span>RO Water Purifier</span>
                    </div>
                    <div class="amenity-container">
                        <img src="img/amenities/dining.svg" alt="Dining">
                        <span>Dining Area</span>
                    </div>
                    <div class="amenity-container">
                        <img src="img/amenities/washingmachine.svg" alt="Washing Machine">
                        <span>Washing Machine</span>
                    </div>
                </div>

                <div class="col-md-auto">
                    <h5>Bedroom</h5>
                    <div class="amenity-container">
                        <img src="img/amenities/bed.svg" alt="Bed">
                        <span>Bed & Mattress</span>
                    </div>
                    <div class="amenity-container">
                        <img src="img/amenities/ac.svg" alt="AC">
                        <span>Air Conditioner</span>
                    </div>
                </div>

                <div class="col-md-auto">
                    <h5>Washroom</h5>
                    <div class="amenity-container">
                        <img src="img/amenities/geyser.svg" alt="Geyser">
                        <span>Geyser / Hot Water</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="property-about page-container">
        <h1>About the Property</h1>
        <p><?= nl2br(htmlspecialchars($property['description'] ?: 'Located in a safe and accessible neighborhood, this property offers all standard modern amenities for students and working professionals.')) ?></p>
    </div>

    <div class="property-rating">
        <div class="page-container">
            <h1>Property Rating</h1>
            <div class="row align-items-center justify-content-between">
                <div class="col-md-6">
                    <div class="rating-criteria row">
                        <div class="col-6">
                            <i class="rating-criteria-icon fas fa-broom"></i>
                            <span class="rating-criteria-text">Cleanliness</span>
                        </div>
                        <div class="rating-criteria-star-container col-6" title="<?= $property['rating_clean'] ?>">
                            <?php
                            $rating = $property['rating_clean'];
                            for ($i = 0; $i < 5; $i++) {
                                if ($rating >= $i + 0.8) {
                            ?>
                                    <i class="fas fa-star"></i>
                                <?php
                                } elseif ($rating >= $i + 0.3) {
                                ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php
                                } else {
                                ?>
                                    <i class="far fa-star"></i>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <div class="rating-criteria row">
                        <div class="col-6">
                            <i class="rating-criteria-icon fas fa-utensils"></i>
                            <span class="rating-criteria-text">Food Quality</span>
                        </div>
                        <div class="rating-criteria-star-container col-6" title="<?= $property['rating_food'] ?>">
                            <?php
                            $rating = $property['rating_food'];
                            for ($i = 0; $i < 5; $i++) {
                                if ($rating >= $i + 0.8) {
                            ?>
                                    <i class="fas fa-star"></i>
                                <?php
                                } elseif ($rating >= $i + 0.3) {
                                ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php
                                } else {
                                ?>
                                    <i class="far fa-star"></i>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <div class="rating-criteria row">
                        <div class="col-6">
                            <i class="rating-criteria-icon fa fa-lock"></i>
                            <span class="rating-criteria-text">Safety</span>
                        </div>
                        <div class="rating-criteria-star-container col-6" title="<?= $property['rating_safety'] ?>">
                            <?php
                            $rating = $property['rating_safety'];
                            for ($i = 0; $i < 5; $i++) {
                                if ($rating >= $i + 0.8) {
                            ?>
                                    <i class="fas fa-star"></i>
                                <?php
                                } elseif ($rating >= $i + 0.3) {
                                ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php
                                } else {
                                ?>
                                    <i class="far fa-star"></i>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="circle-container">
                        <div class="total-rating"><?= $total_rating ?></div>
                        <div class="circle-star-container">
                            <?php
                            $rating = $total_rating;
                            for ($i = 0; $i < 5; $i++) {
                                if ($rating >= $i + 0.8) {
                            ?>
                                    <i class="fas fa-star"></i>
                                <?php
                                } elseif ($rating >= $i + 0.3) {
                                ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php
                                } else {
                                ?>
                                    <i class="far fa-star"></i>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    include "includes/signup_modal.php";
    include "includes/login_modal.php";
    include "includes/footer.php";
    ?>
    <script type="text/javascript" src="js/property_detail.js"></script>
</body>

</html>
