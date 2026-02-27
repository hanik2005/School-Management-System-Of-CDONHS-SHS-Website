<?php
session_start();

$feature_name = isset($_GET['feature']) ? htmlspecialchars($_GET['feature']) : 'this feature';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - CDONHS-SHS</title>
    <link rel="icon" href="Assets/LOGO.png" type="image/jpg">
    <link rel="stylesheet" href="Design/main_design.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .access-denied-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            margin: 20px;
        }
        
        .access-denied-container h1 {
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        .access-denied-container p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .back-btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .back-btn:hover {
            background-color: #0056b3;
        }
        
        .warning-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="access-denied-container">
        <div class="warning-icon">🚫</div>
        <h1>Access Denied</h1>
        <p>
            Sorry, <strong><?php echo $feature_name; ?></strong> is currently disabled.<br>
            Please contact the administrator for more information.
        </p>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php 
            $role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : 0;
            $home_link = '';
            if ($role_id == 2) {
                $home_link = '/SMS_CDONHS-SHS_WEBSITE/Website_Files/Admin_Files/home.php';
            } elseif ($role_id == 3) {
                $home_link = '/SMS_CDONHS-SHS_WEBSITE/Website_Files/Teacher_Files/home.php';
            } elseif ($role_id == 1) {
                $home_link = '/SMS_CDONHS-SHS_WEBSITE/Website_Files/Student_Files/home.php';
            }else{
                $home_link = '/SMS_CDONHS-SHS_WEBSITE/Website_Files/guest_page.php';
            }
            ?>
            <a href="<?php echo $home_link; ?>" class="back-btn">Go to Home</a>
        <?php else: ?>
            <a href="/SMS_CDONHS-SHS_WEBSITE/Website_Files/guest_page.php" class="back-btn">Go to Guest Page</a>
        <?php endif; ?>
    </div>
</body>
</html>
