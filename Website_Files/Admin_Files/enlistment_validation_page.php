<?php
include "../../Back_End_Files/PHP_Files/enlistment_validation_backend.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="../../Back_End_Files/JSCRIPT_Files/timer-logout.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Enlistment Validation</title>
    <link rel="icon" href="../../Assets/LOGO.png" type="image/jpg">
    <link rel="stylesheet" href="../../Design/main_design.css">
    <link rel="stylesheet" href="../../Design/profile_dropdown.css">
    <link rel="stylesheet" href="../../Design/dashboard_design.css">
    <link rel="stylesheet" href="../../Design/admin/enlistment_validation.css">
</head>
<body>
    <!-- header -->
    <div class="header">
        <div class="left">
            <img src="../../Assets/LOGO.png" alt="CDONSHS Logo">
            <span>CDONSHS-SHS</span>
        </div>
        <div class="center">
            Admin
        </div>
        <div class="right">
            <button class="profile-btn" type="button">
                <img src="../../Assets/admin_profile.png">
            </button>
            <div class="profile-dropdown">
                <a href="home.php">Home</a>
                <a href="../../Back_End_Files/PHP_Files/logout.php">Logout</a>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="back-button-container">
        <a href="home.php" class="back-button">← Back to Home</a>
    </div>

    <div class="enlistment-container">
        <div class="enlistment-box">
            <h2>Enlistment Validation</h2>

            <!-- ================= FILTER SECTION ================= -->
            <form method="GET">
                <div class="filter-section">
                    <div class="filter-group">
                        <label>Search by Name:</label>
                        <input type="text" name="search_name" class="filter-dropdown" placeholder="Enter student name..." value="<?= isset($_GET['search_name']) ? htmlspecialchars($_GET['search_name']) : '' ?>">
                    </div>

                    <div class="filter-group">
                        <label>Grade Level:</label>
                        <select name="grade_level" class="filter-dropdown">
                            <option value="">Select Grade Level</option>
                            <option value="11" <?= ($grade=='11')?'selected':'' ?>>11</option>
                            <option value="12" <?= ($grade=='12')?'selected':'' ?>>12</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Strand:</label>
                        <select name="strand_id" class="filter-dropdown" onchange="this.form.submit()">
                            <option value="">Select Strand</option>
                            <?php foreach ($strands as $s): ?>
                                <option value="<?= $s['strand_id'] ?>" <?= ($strand == $s['strand_id'])?'selected':'' ?>>
                                    <?= $s['strand_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Section:</label>
                        <select name="section_id" class="filter-dropdown" <?= empty($strand)?'disabled':'' ?>>
                            <option value="">Select Section</option>
                            <?php foreach ($sections as $sec): ?>
                                <option value="<?= $sec['section_id'] ?>" <?= ($section == $sec['section_id'])?'selected':'' ?>>
                                    <?= $sec['section_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-buttons">
                        <button type="submit" class="search-btn">Search</button>
                        <a href="enlistment_validation_page.php" class="clear-filter-btn">Clear</a>
                    </div>
                </div>
            </form>

            <!-- ================= TABLE SECTION ================= -->
            <form method="POST" action="../../Back_End_Files/PHP_Files/admin_enlistment_validation_backend.php" onsubmit="showLoadingModal()">
                <div class="table-container">
                    <table class="validation-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAllCheckbox"></th>
                                <th>No</th>
                                <th>LRN</th>
                                <th>Student Name</th>
                                <th>Enrollment Type</th>
                                <th>Grade Level</th>
                                <th>Strand</th>
                                <th>Section</th>
                                <th>Enlistment Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no = 1;

                        // Use the already prepared query with filters
                        $stmt = $connection->prepare($sql);

                        if (!empty($params)) {
                            $stmt->bind_param($types, ...$params);
                        }

                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                                // Combine first name + last name
                                $student_name = $row['first_name'] . ' ' . $row['last_name'];
                        ?>
                            <tr>
                                <td><input type="checkbox" class="row-checkbox" name="selected_students[]" value="<?= $row['student_id'] ?>"></td>
                                <td><?= $no++ ?></td>
                                <td><?= $row['lrn'] ?></td>
                                <td><?= $student_name ?></td>
                                <td><?= $row['enrollment_type'] ?? 'N/A' ?></td>
                                <td><?= $row['grade_level'] ?? '' ?></td>
                                <td><?= $row['strand_name'] ?? '' ?></td>
                                <td><?= $row['section_name'] ?? '' ?></td>
                                <td>
                                    <select name="status[<?= $row['student_id'] ?>]" class="status-dropdown">
                                        <option value="Pending" <?= $row['enlistment_status']=="Pending" ? "selected" : "" ?>>Pending</option>
                                        <option value="Enlisted" <?= $row['enlistment_status']=="Enlisted" ? "selected" : "" ?>>Enlisted</option>
                                        <option value="Rejected" <?= $row['enlistment_status']=="Rejected" ? "selected" : "" ?>>Rejected</option>
                                    </select>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="no-results">No pending enlistments found.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="validation-buttons">
                    <button type="submit" name="confirm" class="confirm-btn">Confirm</button>
                    <a href="enlistment_validation_page.php" class="clear-filter-btn">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="loading-modal">
        <div class="loading-content">
            <div class="spinner"></div>
            <p>Processing... Please wait.</p>
            <span class="loading-subtext">Sending notifications and updating records.</span>
        </div>
    </div>

    <!-- Success Modal -->
    <?php if (isset($_GET['success'])): ?>
    <div id="successModal" class="success-modal active">
        <div class="success-content">
            <div class="success-icon">&#10004;</div>
            <p>Students validated successfully!</p>
            <button type="button" class="success-btn" onclick="closeSuccessModal()">OK</button>
        </div>
    </div>
    <?php endif; ?>

    <!-- footer -->
    <div class="footer">
        © 2026 Cagayan De Oro National High School - Senior High School  
        <br>
        School Management System
    </div>
    <script src="../../Back_End_Files/JSCRIPT_Files/profile_dropdown_function.js"></script>
    <script>
    document.getElementById('selectAllCheckbox').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
    
    function showLoadingModal() {
        const loadingModal = document.getElementById('loadingModal');
        if (loadingModal) {
            loadingModal.classList.add('active');
        }
    }
    
    function closeSuccessModal() {
        const successModal = document.getElementById('successModal');
        if (successModal) {
            successModal.classList.remove('active');
        }
        // Remove the success parameter from URL without reloading
        const url = new URL(window.location.href);
        url.searchParams.delete('success');
        window.history.replaceState({}, document.title, url);
    }
    </script>
</body>
</html>
