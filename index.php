<?php
session_start();

include 'connection.php';
include 'tools.php';

$addResponse = null;
$deleteResponse = null;

if (isset($_SESSION['add_response'])) {
    $addResponse = $_SESSION['add_response'];
    unset($_SESSION['add_response']);
}
if (isset($_SESSION['delete_response'])) {
    $deleteResponse = $_SESSION['delete_response'];
    unset($_SESSION['delete_response']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_tool') {
    $name = trim($_POST['name'] ?? '');
    $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
    $image_url = filter_var($_POST['image_url'] ?? '', FILTER_VALIDATE_URL);

    if (empty($name) || $price === false || $price < 0 || $image_url === false) {
        $_SESSION['add_response'] = ['success' => false, 'message' => 'All fields are required and must be valid.'];
    } else {
        $_SESSION['add_response'] = addTool($conn, $name, $price, $image_url);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_tool') {
    $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

    if ($id === false || $id <= 0) {
        $_SESSION['delete_response'] = ['success' => false, 'message' => 'Invalid tool ID.'];
    } else {
        $_SESSION['delete_response'] = deleteTool($conn, $id);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

$tools = fetchTools($conn);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PHP-Crud-App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css" />
    <style>
        .card-image img {
            object-fit: cover;
            height: 200px;
            width: 100%;
        }
        .notification {
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>
    <nav class="navbar is-dark" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="#">
                <img src="https://cdn.pixabay.com/photo/2016/08/22/05/10/ant-1611374_1280.png" alt="site-logo" style="max-height: 2.5rem;" />
                <span class="is-size-4 has-text-white ml-2">PHP-Crud-App</span>
            </a>
        </div>
    </nav>

    <section class="section">
        <div class="container">
            <h2 class="title is-3">Add New Tool</h2>
            <?php if ($addResponse): ?>
                <div class="notification <?= $addResponse['success'] ? 'is-success' : 'is-danger' ?>">
                    <?= htmlspecialchars($addResponse['message']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="action" value="add_tool">
                <div class="field">
                    <label class="label" for="tool_name">Tool Name</label>
                    <div class="control">
                        <input class="input" type="text" id="tool_name" name="name" required placeholder="Enter tool name" />
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="tool_price">Price per day ($)</label>
                    <div class="control">
                        <input class="input" type="number" step="0.01" id="tool_price" name="price" required min="0" placeholder="0.00" />
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="tool_image_url">Image URL</label>
                    <div class="control">
                        <input class="input" type="url" id="tool_image_url" name="image_url" required placeholder="https://example.com/image.jpg" />
                    </div>
                </div>

                <div class="field mt-4">
                    <button class="button is-primary is-fullwidth">Add Tool</button>
                </div>
            </form>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="title is-3">Available Tools</h2>

            <?php if ($deleteResponse): ?>
                <div class="notification <?= $deleteResponse['success'] ? 'is-success' : 'is-danger' ?>">
                    <?= htmlspecialchars($deleteResponse['message']) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($tools)): ?>
                <div class="notification is-info">
                    No tools available. Add a new tool above!
                </div>
            <?php else: ?>
                <div class="columns is-multiline">
                    <?php foreach ($tools as $tool): ?>
                        <div class="column is-one-quarter-desktop is-half-tablet is-full-mobile">
                            <div class="card">
                                <div class="card-image">
                                    <figure class="image is-4by3">
                                        <img src="<?= htmlspecialchars($tool['image_url']) ?>" alt="<?= htmlspecialchars($tool['name']) ?>"
                                            onerror="this.onerror=null;this.src='https://placehold.co/600x400/cccccc/333333?text=No+Image';" />
                                    </figure>
                                </div>
                                <div class="card-content">
                                    <p class="title is-5"><?= htmlspecialchars($tool['name']) ?></p>
                                    <p class="subtitle is-6 has-text-grey">$<?= number_format($tool['price'], 2) ?> / day</p>

                                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this tool: <?= htmlspecialchars($tool['name']) ?>?');">
                                        <input type="hidden" name="action" value="delete_tool">
                                        <input type="hidden" name="id" value="<?= $tool['id'] ?>">
                                        <button type="submit" class="button is-danger is-fullwidth mt-2">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>
