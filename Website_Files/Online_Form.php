<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Enrollment</title>
    <link rel="stylesheet" href="../Design/Online_Form_Design.css">
</head>
<body>
    <form action="../Back_End_Files/PHP_Files/testing.php" 
      method="POST" enctype="multipart/form-data">
        <h1>CDONHS-SHS Online Enrollment</h1>
        <img src="../Assets/LOGO.png" alt="CDONHS-SHS Logo">
        <h2>Student Personal Information</h2>
        <label for="firstName">First Name:</label>
        <input type="text" id="firstName" name="firstName" required><br><br>
        
        <label for="lastName">Last Name:</label>
        <input type="text" id="lastName" name="lastName" required><br><br>
        
        <label for="middleName">Middle Name:</label>
        <input type="text" id="middleName" name="middleName"><br><br>
        
        <label for="extensionName">Extension Name:</label>
        <input type="text" id="extensionName" name="extensionName"><br><br>
        
        <label for="dob">Date of Birth:</label>
        <input type="date" id="dob" name="dob" required><br><br>
        
        <label for="gender">Gender:</label>
        <select id="gender" name="gender" required>
            <option value="">Select</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select><br><br>
        
        <label for="civilStatus">Civil Status:</label>
        <select id="civilStatus" name="civilStatus" required>
            <option value="">Select</option>
            <option value="single">Single</option>
            <option value="married">Married</option>
            <option value="divorced">Divorced</option>
            <option value="widowed">Widowed</option>
        </select><br><br>
        
        <label for="houseNumberStreet">House Number Street:</label>
        <input type="text" id="houseNumberStreet" name="houseNumberStreet" required><br><br>
        
        <label for="barangay">Barangay:</label>
        <input type="text" id="barangay" name="barangay" required><br><br>
        
        <label for="cityMunicipality">City/Municipality:</label>
        <input type="text" id="cityMunicipality" name="cityMunicipality" required><br><br>
        
        <label for="province">Province:</label>
        <input type="text" id="province" name="province" required><br><br>
        
        <label for="contactNumber">Contact Number:</label>
        <input type="tel" id="contactNumber" name="contactNumber" required><br><br>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>
        
        <label for="facebookName">Facebook Name or Profile Link:</label>
        <input type="url" id="facebookName" name="facebookName"><br><br>
        
        <label for="currentSchool">Current School/Last School Attended:</label>
        <input type="text" id="currentSchool" name="currentSchool" required><br><br>
        
        <label for="schoolClassification">School Classification:</label>
        <select id="schoolClassification" name="schoolClassification" required>
            <option value="">Select</option>
            <option value="public">Public</option>
            <option value="private">Private</option>
        </select><br><br>
        
        <label for="yearGraduated">Year Graduated:</label>
        <input type="number" id="yearGraduated" name="yearGraduated" min="1900" max="2030" required><br><br>
        
        <label for="fatherGuardianName">Father or Guardian Name:</label>
        <input type="text" id="fatherGuardianName" name="fatherGuardianName" required><br><br>
        
        <label for="fatherGuardianContact">Father or Guardian Contact Number:</label>
        <input type="tel" id="fatherGuardianContact" name="fatherGuardianContact" required><br><br>
        
        <label for="motherGuardianName">Mother or Guardian Name:</label>
        <input type="text" id="motherGuardianName" name="motherGuardianName" required><br><br>
        
        <label for="motherGuardianContact">Mother or Guardian Contact Number:</label>
        <input type="tel" id="motherGuardianContact" name="motherGuardianContact" required><br><br>
        
        <h2>Enrollment Requirements</h2>
        <p>Please upload the following requirements below IF AVAILABLE.</p>
        <label for="psaBirthCertificate">PSA/NSO Birth Certificate:</label>
        <input type="file" id="psaBirthCertificate" name="psaBirthCertificate" accept=".pdf,.jpg,.png"><br><br>
        
        <label for="form138">Form138/Report Card:</label>
        <input type="file" id="form138" name="form138" accept=".pdf,.jpg,.png" ><br><br>
        
        <label for="studentID">Copy of Student Current ID:</label>
        <input type="file" id="studentID" name="studentID" accept=".pdf,.jpg,.png"><br><br>
        
        <button type="submit">Submit</button>
    </form>
</body>
</html>