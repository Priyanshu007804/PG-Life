<?php
session_start();
require "includes/database_connect.php";

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : NULL;
$city_name = isset($_GET["city"]) ? trim($_GET["city"]) : '';

// Alias Bengaluru to Bangalore for matching DB
if (strcasecmp($city_name, 'Bengaluru') === 0) {
    $city_name = 'Bangalore';
}

$sort = isset($_GET["sort"]) ? strtolower(trim($_GET["sort"])) : '';
$gender_filter = isset($_GET["gender"]) ? strtolower(trim($_GET["gender"])) : '';

// Get city by name — prepared statement
$stmt_1 = $conn->prepare("SELECT * FROM cities WHERE LOWER(name) = LOWER(?)");
$stmt_1->bind_param("s", $city_name);
$stmt_1->execute();
$result_1 = $stmt_1->get_result();

if (!$result_1) {
    echo "Something went wrong!";
    return;
}
$city = mysqli_fetch_assoc($result_1);
if (!$city) {
    echo "Sorry! We do not have any PG listed in this city.";
    return;
}
$city_id = (int)$city['id'];
$city_display_name = $city['name'];
$stmt_1->close();

// Build query for properties with optional sorting and gender filter
$query = "SELECT * FROM properties WHERE city_id = ?";
$types = "i";
$params = array($city_id);

if ($gender_filter === 'male' || $gender_filter === 'female' || $gender_filter === 'unisex') {
    $query .= " AND gender = ?";
    $types .= "s";
    $params[] = $gender_filter;
}

if ($sort === 'asc') {
    $query .= " ORDER BY rent ASC";
} elseif ($sort === 'desc') {
    $query .= " ORDER BY rent DESC";
}

$stmt_2 = $conn->prepare($query);
$stmt_2->bind_param($types, ...$params);
$stmt_2->execute();
$result_2 = $stmt_2->get_result();

if (!$result_2) {
    echo "Something went wrong!";
    return;
}
$properties = mysqli_fetch_all($result_2, MYSQLI_ASSOC);
$stmt_2->close();

// Get interested users for properties in this city
$stmt_3 = $conn->prepare("SELECT * 
            FROM interested_users_properties iup
            INNER JOIN properties p ON iup.property_id = p.id
            WHERE p.city_id = ?");
$stmt_3->bind_param("i", $city_id);
$stmt_3->execute();
$result_3 = $stmt_3->get_result();
$interested_users_properties = $result_3 ? mysqli_fetch_all($result_3, MYSQLI_ASSOC) : array();
$stmt_3->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Best PG's in <?= htmlspecialchars($city_display_name) ?> | PG Life</title>

    <?php
    include "includes/head_links.php";
    ?>
    <link href="css/property_list.css?v=<?= time() ?>" rel="stylesheet" />
    <style>
        .filter-bar a.sort-link {
            color: inherit;
            text-decoration: none;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        .filter-bar a.sort-link:hover, .filter-bar a.active-sort {
            background-color: #f0f0f0;
            color: #2196f3;
            font-weight: 600;
        }
        .is-interested-image {
            cursor: pointer;
            color: #e74c3c;
            font-size: 20px;
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
            <li class="breadcrumb-item active" aria-current="page">
                <?= htmlspecialchars($city_display_name) ?>
            </li>
        </ol>
    </nav>

    <div class="page-container">
        <div class="filter-bar row justify-content-around">
            <div class="col-auto" data-toggle="modal" data-target="#filter-modal" style="cursor: pointer;">
                <img src="img/filter.png" alt="filter" />
                <span>Filter <?= $gender_filter ? '(' . ucfirst($gender_filter) . ')' : '' ?></span>
            </div>
            <a href="property_list.php?city=<?= urlencode($city_name) ?>&sort=desc<?= $gender_filter ? '&gender=' . urlencode($gender_filter) : '' ?>" class="col-auto sort-link <?= $sort === 'desc' ? 'active-sort' : '' ?>">
                <img src="img/desc.png" alt="sort-desc" />
                <span>Highest rent first</span>
            </a>
            <a href="property_list.php?city=<?= urlencode($city_name) ?>&sort=asc<?= $gender_filter ? '&gender=' . urlencode($gender_filter) : '' ?>" class="col-auto sort-link <?= $sort === 'asc' ? 'active-sort' : '' ?>">
                <img src="img/asc.png" alt="sort-asc" />
                <span>Lowest rent first</span>
            </a>
        </div>

        <?php
        foreach ($properties as $property) {
            $images = glob("img/properties/" . $property['id'] . "/*");
            $image_src = (!empty($images)) ? $images[0] : "img/properties/1/1d4f0757fdb86d5f.jpg";
        ?>
            <div class="property-card property-id-<?= $property['id'] ?> row">
                <div class="image-container col-md-4">
                    <img src="<?= htmlspecialchars($image_src) ?>" alt="<?= htmlspecialchars($property['name']) ?>" />
                </div>
                <div class="content-container col-md-8">
                    <div class="row no-gutters justify-content-between">
                        <?php
                        $total_rating = ($property['rating_clean'] + $property['rating_food'] + $property['rating_safety']) / 3;
                        $total_rating = round($total_rating, 1);
                        ?>
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
                            <?php
                            $interested_users_count = 0;
                            $is_interested = false;
                            foreach ($interested_users_properties as $interested_user_property) {
                                if ($interested_user_property['property_id'] == $property['id']) {
                                    $interested_users_count++;

                                    if ($interested_user_property['user_id'] == $user_id) {
                                        $is_interested = true;
                                    }
                                }
                            }

                            if ($is_interested) {
                            ?>
                                <i class="is-interested-image fas fa-heart" property_id="<?= $property['id'] ?>"></i>
                            <?php
                            } else {
                            ?>
                                <i class="is-interested-image far fa-heart" property_id="<?= $property['id'] ?>"></i>
                            <?php
                            }
                            ?>
                            <div class="interested-text">
                                <span class="interested-user-count"><?= $interested_users_count ?></span> interested
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
                                <img src="img/male.png" alt="Male" />
                            <?php
                            } elseif ($property['gender'] == "female") {
                            ?>
                                <img src="img/female.png" alt="Female" />
                            <?php
                            } else {
                            ?>
                                <img src="img/unisex.png" alt="Unisex" />
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
                            <a href="property_detail.php?property_id=<?= $property['id'] ?>" class="btn btn-primary">View</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }

        if (count($properties) == 0) {
        ?>
            <div class="no-property-container" style="padding: 40px; text-align: center; color: #777;">
                <h4>No PGs found in this city matching your criteria.</h4>
                <p><a href="property_list.php?city=<?= urlencode($city_name) ?>" class="btn btn-outline-primary btn-sm mt-2">Clear Filters</a></p>
            </div>
        <?php
        }
        ?>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filter-modal" tabindex="-1" role="dialog" aria-labelledby="filter-heading" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="filter-heading">Filters</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <h5>Gender</h5>
                    <hr />
                    <div>
                        <a href="property_list.php?city=<?= urlencode($city_name) ?><?= $sort ? '&sort=' . urlencode($sort) : '' ?>" class="btn btn-outline-dark <?= empty($gender_filter) ? 'active' : '' ?>">
                            No Filter
                        </a>
                        <a href="property_list.php?city=<?= urlencode($city_name) ?>&gender=unisex<?= $sort ? '&sort=' . urlencode($sort) : '' ?>" class="btn btn-outline-dark <?= $gender_filter === 'unisex' ? 'active' : '' ?>">
                            <i class="fas fa-venus-mars"></i> Unisex
                        </a>
                        <a href="property_list.php?city=<?= urlencode($city_name) ?>&gender=male<?= $sort ? '&sort=' . urlencode($sort) : '' ?>" class="btn btn-outline-dark <?= $gender_filter === 'male' ? 'active' : '' ?>">
                            <i class="fas fa-mars"></i> Male
                        </a>
                        <a href="property_list.php?city=<?= urlencode($city_name) ?>&gender=female<?= $sort ? '&sort=' . urlencode($sort) : '' ?>" class="btn btn-outline-dark <?= $gender_filter === 'female' ? 'active' : '' ?>">
                            <i class="fas fa-venus"></i> Female
                        </a>
                    </div>
                </div>

                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-secondary">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php
    include "includes/signup_modal.php";
    include "includes/login_modal.php";
    include "includes/footer.php";
    ?>
    <script type="text/javascript" src="js/property_list.js"></script>
</body>

</html>
