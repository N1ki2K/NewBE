<?php

// Function to get all posts
function getAllPosts($conn) {
    // SQL query to select all posts and join with users and categories tables
    $sql = "SELECT 
                p.id,
                p.title,
                p.content,
                p.image_url,
                p.created_at,
                u.name as author_name,
                c.name as category_name
            FROM 
                posts p
            LEFT JOIN 
                users u ON p.author_id = u.id
            LEFT JOIN 
                categories c ON p.category_id = c.id
            ORDER BY 
                p.created_at DESC";

    $result = $conn->query($sql);

    $posts = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    }

    // Set the Content-Type header to application/json
    header('Content-Type: application/json');
    // Return the posts as a JSON object
    echo json_encode($posts);
}

// Handle GET request for /api/posts
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    getAllPosts($conn);
}
?>