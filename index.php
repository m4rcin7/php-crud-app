<?php
    session_start();

    include 'connection.php';
    include 'tools.php';

    $response = null;
    if (isset($_SESSION['response'])) {
        $response = $_SESSION['response'];
        unset($_SESSION['response']);
    }

    $isEditing = false;
    $toolToEdit = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        $name = trim($_POST['name'] ?? '');
        $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
        $image_url = filter_var($_POST['image_url'] ?? '', FILTER_VALIDATE_URL);

        $message = 'All fields are required and must be valid.';

        switch ($action) {
            case 'add_tool':
                if (empty($name) || $price === false || $price < 0 || $image_url === false) {
                    $_SESSION['response'] = ['success' => false, 'message' => $message];
                } else {
                    $_SESSION['response'] = addTool($conn, $name, $price, $image_url);
                }
                break;

            case 'delete_tool':
                if ($id === false || $id <= 0) {
                    $_SESSION['response'] = ['success' => false, 'message' => 'Invalid tool ID.'];
                } else {
                    $_SESSION['response'] = deleteTool($conn, $id);
                }
                break;

            case 'update_tool':
                if ($id === false || $id <= 0 || empty($name) || $price === false || $price < 0 || $image_url === false) {
                    $_SESSION['response'] = ['success' => false, 'message' => $message];
                } else {
                    $_SESSION['response'] = updateTool($conn, $id, $name, $price, $image_url);
                }
                break;
        }
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'edit') {
        $id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

        if ($id === false || $id <= 0) {
            $_SESSION['response'] = ['success' => false, 'message' => 'Invalid tool ID for editing.'];
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit();
        }

        $toolToEdit = getToolById($conn, $id);

        if (!$toolToEdit) {
            $_SESSION['response'] = ['success' => false, 'message' => 'Tool with the given ID was not found.'];
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit();
        }
        $isEditing = true;
    }

    $tools = [];
    if (!$isEditing) {
        $tools = fetchTools($conn);
    }

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
        .buttons .button {
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body>
    <nav class="navbar is-dark" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="index.php">
                <img src="https://cdn.pixabay.com/photo/2016/08/22/05/10/ant-1611374_1280.png" alt="site-logo" style="max-height: 2.5rem;" />
                <span class="is-size-4 has-text-white ml-2">PHP-Crud-App</span>
            </a>
        </div>
        <?php if ($isEditing): ?>
        <div class="navbar-menu">
            <div class="navbar-start">
                <a class="navbar-item" href="index.php">
                    Back to Tools List
                </a>
            </div>
        </div>
        <?php endif; ?>
    </nav>

    <section class="section">
        <div class="container">
            <?php if ($response): ?>
                <div class="notification <?= $response['success'] ? 'is-success' : 'is-danger' ?>">
                    <?= htmlspecialchars($response['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($isEditing): ?>
                <h2 class="title is-3">Edit Tool: <?= htmlspecialchars($toolToEdit['name'] ?? 'Unknown') ?></h2>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_tool">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($toolToEdit['id'] ?? '') ?>">

                    <div class="field">
                        <label class="label" for="tool_name">Tool Name</label>
                        <div class="control">
                            <input class="input" type="text" id="tool_name" name="name" required
                                value="<?= htmlspecialchars($toolToEdit['name'] ?? '') ?>" placeholder="Enter tool name" />
                        </div>
                    </div>

                    <div class="field">
                        <label class="label" for="tool_price">Price per Day ($)</label>
                        <div class="control">
                            <input class="input" type="number" step="0.01" id="tool_price" name="price" required min="0"
                                value="<?= htmlspecialchars($toolToEdit['price'] ?? '') ?>" placeholder="0.00" />
                        </div>
                    </div>

                    <div class="field">
                        <label class="label" for="tool_image_url">Image URL</label>
                        <div class="control">
                            <input class="input" type="url" id="tool_image_url" name="image_url" required
                                value="<?= htmlspecialchars($toolToEdit['image_url'] ?? '') ?>" placeholder="https://example.com/image.jpg" />
                        </div>
                    </div>

                    <div class="field mt-4">
                        <button class="button is-primary is-fullwidth">Save Changes</button>
                        <a href="index.php" class="button is-link is-fullwidth mt-3">Cancel</a>
                    </div>
                </form>

            <?php else: ?>

                <h2 class="title is-3">Add New Tool</h2>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_tool">
                    <div class="field">
                        <label class="label" for="add_tool_name">Tool Name</label>
                        <div class="control">
                            <input class="input" type="text" id="add_tool_name" name="name" required placeholder="Enter tool name" />
                        </div>
                    </div>

                    <div class="field">
                        <label class="label" for="add_tool_price">Price per Day ($)</label>
                        <div class="control">
                            <input class="input" type="number" step="0.01" id="add_tool_price" name="price" required min="0" placeholder="0.00" />
                        </div>
                    </div>

                    <div class="field">
                        <label class="label" for="add_tool_image_url">Image URL</label>
                        <div class="control">
                            <input class="input" type="url" id="add_tool_image_url" name="image_url" required placeholder="https://example.com/image.jpg" />
                        </div>
                    </div>

                    <div class="field mt-4">
                        <button class="button is-primary is-fullwidth">Add Tool</button>
                    </div>
                </form>

                <h2 class="title is-3 mt-6">Available Tools</h2>

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

                                        <div class="buttons is-flex is-flex-direction-column">
                                            <a href="index.php?action=edit&id=<?= $tool['id'] ?>" class="button is-info is-fullwidth">
                                                Edit
                                            </a>

                                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this tool: <?= htmlspecialchars($tool['name']) ?>?');" class="is-fullwidth">
                                                <input type="hidden" name="action" value="delete_tool">
                                                <input type="hidden" name="id" value="<?= $tool['id'] ?>">
                                                <button type="submit" class="button is-danger is-fullwidth">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>
