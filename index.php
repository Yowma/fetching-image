<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "powerguide");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all companies initially
$sql = "SELECT * FROM companies ORDER BY name ASC";
$companies = $conn->query($sql);

// Handle image filtering (if company is selected)
$company_id = isset($_GET['company_id']) ? $_GET['company_id'] : null;
$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';

$images = [];
if ($company_id) {
    $image_query = "SELECT * FROM company_images WHERE company_id = ?";
    if ($month && $year) {
        $image_query .= " AND MONTH(upload_date) = ? AND YEAR(upload_date) = ?";
        $stmt = $conn->prepare($image_query);
        $stmt->bind_param("iii", $company_id, $month, $year);
    } else {
        $stmt = $conn->prepare($image_query);
        $stmt->bind_param("i", $company_id);
    }
    $stmt->execute();
    $images = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Directory</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <h1 class="title">Company Directory</h1>

        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" id="search" placeholder="Search companies..." onkeyup="filterCompanies()">
        </div>

        <!-- Company List -->
        <div class="company-list" id="companyList">
            <?php while ($row = $companies->fetch_assoc()): ?>
                <div class="company-card <?php echo $company_id == $row['id'] ? 'selected' : ''; ?>" 
                     data-id="<?php echo $row['id']; ?>" 
                     onclick="hideOtherCompanies(<?php echo $row['id']; ?>)">
                    <?php echo htmlspecialchars($row['name']); ?>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Image Section -->
        <div class="image-section" id="imageSection" style="display: <?php echo $company_id ? 'block' : 'none'; ?>;">
            <h2>Images</h2>

            <!-- Date Filters -->
            <div class="filter-container">
                <select name="month" id="monthFilter" onchange="filterImages()">
                    <option value="">Month</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo $m == $month ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <select name="year" id="yearFilter" onchange="filterImages()">
                    <option value="">Year</option>
                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Image Gallery -->
            <div class="image-gallery" id="imageGallery">
                <?php if ($company_id && $images->num_rows > 0): ?>
                    <?php while ($img = $images->fetch_assoc()): ?>
                        <div class="image-wrapper">
                            <img src="<?php echo htmlspecialchars($img['image_path']); ?>" 
                                 alt="Company Image" 
                                 data-src="<?php echo htmlspecialchars($img['image_path']); ?>" 
                                 onclick="maximizeImage(this)">
                        </div>
                    <?php endwhile; ?>
                <?php elseif ($company_id): ?>
                    <p>No images found for this filter.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal for Maximized Image -->
        <div class="modal" id="imageModal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">×</span>
                <img id="modalImage" src="" alt="Maximized Image">
                <button onclick="downloadAsPDF()">Download as PDF</button>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>

<?php $conn->close(); ?>