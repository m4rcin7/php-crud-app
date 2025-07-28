<?php

function addTool(mysqli $conn, string $name, float $price, string $imageUrl): array
{
    $stmt = $conn->prepare("INSERT INTO tools (name, price, image_url) VALUES (?, ?, ?)");
    if ($stmt === false) {
        return ['success' => false, 'message' => 'Error preparing statement: ' . $conn->error];
    }

    $stmt->bind_param("sds", $name, $price, $imageUrl);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Tool was successfully added.'];
    } else {
        return ['success' => false, 'message' => 'Error adding tool: ' . $stmt->error];
    }
}

function deleteTool(mysqli $conn, int $id): array
{
    $stmt = $conn->prepare("DELETE FROM tools WHERE id = ?");
    if ($stmt === false) {
        return ['success' => false, 'message' => 'Error preparing statement: ' . $conn->error];
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            return ['success' => true, 'message' => 'Tool was successfully deleted.'];
        } else {
            return ['success' => false, 'message' => 'No tool found with the given ID or it cannot be deleted.'];
        }
    } else {
        return ['success' => false, 'message' => 'Error deleting tool: ' . $stmt->error];
    }
}

function updateTool(mysqli $conn, int $id, string $name, float $price, string $imageUrl): array
{
    $stmt = $conn->prepare("UPDATE tools SET name = ?, price = ?, image_url = ? WHERE id = ?");
    if ($stmt === false) {
        return ['success' => false, 'message' => 'Error preparing statement: ' . $conn->error];
    }

    $stmt->bind_param("sdsi", $name, $price, $imageUrl, $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            return ['success' => true, 'message' => 'Tool was successfully updated.'];
        } else {
            return ['success' => false, 'message' => 'No tool found with the given ID or no changes were made.'];
        }
    } else {
        return ['success' => false, 'message' => 'Error updating tool: ' . $stmt->error];
    }
}

function fetchTools(mysqli $conn): array
{
    $tools = [];
    $result = $conn->query("SELECT id, name, price, image_url FROM tools ORDER BY id DESC");

    if ($result === false) {
        error_log("Error fetching tools: " . $conn->error);
        return [];
    }

    while ($row = $result->fetch_assoc()) {
        $tools[] = $row;
    }

    return $tools;
}

function getToolById(mysqli $conn, int $id): ?array
{
    $stmt = $conn->prepare("SELECT id, name, price, image_url FROM tools WHERE id = ?");
    if ($stmt === false) {
        error_log("Error preparing statement (getToolById): " . $conn->error);
        return null;
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

?>
