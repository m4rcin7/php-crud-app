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

?>
