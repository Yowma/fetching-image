<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "powerguide");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch companies for the dropdown
$companies = $conn->query("SELECT * FROM companies ORDER BY name ASC");

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_id = $_POST['company_id'];
    $image = $_FILES['image'];

    // Validate inputs
    if (!$company_id || !$image['name']) {
        $message = "Please select a company and an image.";
    } else {
        // Define upload directory
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique file name
        $file_name = uniqid() . '_' . basename($image['name']);
        $image_path = $upload_dir . $file_name;

        // Move uploaded file
        if (move_uploaded_file($image['tmp_name'], $image_path)) {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO company_images (company_id, image_path, upload_date) VALUES (?, ?, NOW())");
            $stmt->bind_param("is", $company_id, $image_path);
            if ($stmt->execute()) {
                $message = "Image uploaded successfully!";
            } else {
                $message = "Error saving to database.";
                unlink($image_path); // Remove file if DB fails
            }
            $stmt->close();
        } else {
            $message = "Error uploading image.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Company Image</title>
    <link rel="stylesheet" href="styles.css"> <!-- Reuse the same CSS -->
</head>
<body>
    <div class="container">
        <h1 class="title">Upload Company Image</h1>

        <!-- Upload Form -->
        <form class="upload-form" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="company_id">Select Company:</label>
                <select name="company_id" id="company_id" required>
                    <option value="">-- Choose a Company --</option>
                    <?php while ($row = $companies->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Upload Image:</label>
                <input type="file" name="image" id="image" accept="image/*" required>
            </div>

            <button type="submit">Upload</button>
        </form>

        <!-- Message Display -->
        <?php if ($message): ?>
            <p class="message <?php echo strpos($message, 'success') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>