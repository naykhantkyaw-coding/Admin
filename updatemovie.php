<?php
session_start();
include('includes/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = $_POST['movie_id'] ?? '';
    $movie_title = $_POST['movie_title'] ?? '';
    $description = $_POST['description'] ?? '';
    $genrer = $_POST['genrer'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $release_date = $_POST['release_date'] ?? '';
    $status = $_POST['status'] ?? '';
    $remove_image = isset($_POST['remove_image']);
    
    // Image upload handling
    $image_url = '';
    
    try {
        // If editing existing movie, get current image
        if (!empty($movie_id)) {
            $stmt = $pdo->prepare("SELECT ImageUrl FROM Tbl_Movies WHERE MovieId = ?");
            $stmt->execute([$movie_id]);
            $current_movie = $stmt->fetch(PDO::FETCH_ASSOC);
            $image_url = $current_movie['ImageUrl'] ?? '';
        }
        
        // Handle image upload
        if (isset($_FILES['movie_image']) && $_FILES['movie_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'assets/img/movie/';
            
            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['movie_image']['name'], PATHINFO_EXTENSION);
            $file_name = 'movie_' . time() . '_' . uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            // Check file size (max 2MB)
            if ($_FILES['movie_image']['size'] > 2097152) {
                throw new Exception('Image size must be less than 2MB');
            }
            
            // Check if image file
            $check = getimagesize($_FILES['movie_image']['tmp_name']);
            if ($check === false) {
                throw new Exception('File is not an image');
            }
            
            // Allow certain file formats
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($file_extension), $allowed_types)) {
                throw new Exception('Only JPG, JPEG, PNG & GIF files are allowed');
            }
            
            // Upload file
            if (move_uploaded_file($_FILES['movie_image']['tmp_name'], $file_path)) {
                $image_url = $file_path;
                
                // Delete old image if exists
                if (!empty($current_movie['ImageUrl']) && $current_movie['ImageUrl'] != $image_url && file_exists($current_movie['ImageUrl'])) {
                    unlink($current_movie['ImageUrl']);
                }
            } else {
                throw new Exception('Sorry, there was an error uploading your file');
            }
        } elseif ($remove_image && !empty($movie_id)) {
            // Remove current image if checkbox is checked
            if (!empty($current_movie['ImageUrl']) && file_exists($current_movie['ImageUrl'])) {
                unlink($current_movie['ImageUrl']);
            }
            $image_url = '';
        }
        
        if (empty($movie_id)) {
            // Insert new movie
            $stmt = $pdo->prepare("INSERT INTO Tbl_Movies (MovieTitle, Description, Genrer, Duration, ReleaseDate, ImageUrl, Status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$movie_title, $description, $genrer, $duration, $release_date, $image_url, $status]);
            $_SESSION['success_message'] = 'Movie added successfully';
        } else {
            // Update existing movie
            if (!empty($image_url)) {
                $stmt = $pdo->prepare("UPDATE Tbl_Movies SET MovieTitle = ?, Description = ?, Genrer = ?, Duration = ?, ReleaseDate = ?, ImageUrl = ?, Status = ? WHERE MovieId = ?");
                $stmt->execute([$movie_title, $description, $genrer, $duration, $release_date, $image_url, $status, $movie_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE Tbl_Movies SET MovieTitle = ?, Description = ?, Genrer = ?, Duration = ?, ReleaseDate = ?, Status = ? WHERE MovieId = ?");
                $stmt->execute([$movie_title, $description, $genrer, $duration, $release_date, $status, $movie_id]);
            }
            $_SESSION['success_message'] = 'Movie updated successfully';
        }
        
        header('Location: moviemanagement.php');
        exit();
        
    } catch(Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        if (!empty($movie_id)) {
            header('Location: moviemanagement.php?edit_id=' . $movie_id);
        } else {
            header('Location: moviemanagement.php?add_new=1');
        }
        exit();
    }
} else {
    $_SESSION['error_message'] = 'Invalid request method';
    header('Location: moviemanagement.php');
    exit();
}