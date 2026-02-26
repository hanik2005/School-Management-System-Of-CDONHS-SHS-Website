<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Creation</title>
</head>
<body>
    <form action="../Back_End_Files/PHP_Files/admin_creation_backend.php" 
      method="POST" enctype="multipart/form-data">
         <div>
            <label>Username</label>
            <input type="text" name="username" required>
        </div>

         <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>


        <button type="submit">Submit</button>


    </form>
</body>
</html>