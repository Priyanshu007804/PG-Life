<?php
session_start();
require "includes/database_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("location: index.php");
    exit;
}
$user_id = (int)$_SESSION['user_id'];

// Get user profile — prepared statement
$stmt_1 = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt_1->bind_param("i", $user_id);
$stmt_1->execute();
$result_1 = $stmt_1->get_result();

if (!$result_1 || mysqli_num_rows($result_1) == 0) {
    echo "User not found!";
    return;
}
$user = mysqli_fetch_assoc($result_1);
$stmt_1->close();

// Get interested properties — prepared statement
$stmt_2 = $conn->prepare("SELECT p.* 
            FROM interested_users_properties iup
            INNER JOIN properties p ON iup.property_id = p.id
            WHERE iup.user_id = ?");
$stmt_2->bind_param("i", $user_id);
$stmt_2->execute();
$result_2 = $stmt_2->get_result();
$interested_properties = $result_2 ? mysqli_fetch_all($result_2, MYSQLI_ASSOC) : array();
$stmt_2->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | PG Life</title>

    <?php
    include "includes/head_links.php";
    ?>
    <link href="css/property_list.css?v=<?= time() ?>" rel="stylesheet" />
    <link href="css/dashboard.css?v=<?= time() ?>" rel="stylesheet" />
    <style>
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
                Dashboard
            </li>
        </ol>
    </nav>

    <div class="my-profile page-container">
        <h1>My Profile</h1>
        <div class="row">
            <div class="col-md-3 profile-img-container">
                <i class="fas fa-user profile-img"></i>
            </div>
            <div class="col-md-9">
                <div class="row no-gutters justify-content-between align-items-end">
                    <div class="profile">
                        <div class="name"><?= htmlspecialchars($user['full_name']) ?></div>
                        <div class="email"><?= htmlspecialchars($user['email']) ?></div>
                        <div class="phone"><?= htmlspecialchars($user['phone']) ?></div>
                        <div class="college"><?= htmlspecialchars($user['college_name'] ?: 'Not Specified') ?></div>
                    </div>
                    <div class="edit">
                        <div class="edit-profile">Edit Profile</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (count($interested_properties) > 0) {
    ?>
        <div class="my-interested-properties">
            <div class="page-container">
                <h1>My Interested Properties</h1>

                <?php
                foreach ($interested_properties as $property) {
                    $property_images = glob("img/properties/" . $property['id'] . "/*");
                    $image_src = (!empty($property_images)) ? $property_images[0] : "img/properties/1/1d4f0757fdb86d5f.jpg";
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
                                    <i class="is-interested-image fas fa-heart" property_id="<?= $property['id'] ?>" title="Remove from favorites"></i>
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
                                    <a href="property_detail.php?property_id=<?= $property['id'] ?>" class="btn btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    <?php
    } else {
    ?>
        <div class="page-container" style="padding: 40px 0; text-align: center; color: #777;">
            <h3>No Interested Properties Yet</h3>
            <p>Explore PGs across major cities and click the heart icon to save them to your dashboard!</p>
            <a href="index.php" class="btn btn-primary mt-2">Explore PGs</a>
        </div>
    <?php
    }
    ?>

    <?php
    include "includes/footer.php";
    ?>
    <script type="text/javascript" src="js/dashboard.js"></script>
</body>

</html>
