<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href="../Design/Online_Form_Design.css">
    <title>Teacher Applications</title>
</head>
<body>
    <form action="../Back_End_Files/PHP_Files/teacher_enrollment_backend.php" 
      method="POST" enctype="multipart/form-data">

        <h1>CDONHS-SHS Teacher Application</h1>
        <img src="../Assets/LOGO.png" alt="CDONHS-SHS Logo">
        
        <h2>Personal Information</h2>
        
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

        <label for="houseNumberStreet">House Number & Street:</label>
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

        <label for="facebookProfile">Facebook Name or Link:</label>
        <input type="url" id="facebookProfile" name="facebookProfile"><br><br>

        <h2>Education Background</h2>

        <label for="currentSchool">Current/Last School Taught:</label>
        <input type="text" id="currentSchool" name="currentSchool" required><br><br>

        <label for="highestEducation">Highest Education:</label>
        <select id="highestEducation" name="highestEducation" required>
            <option value="">Select</option>
            <option value="Bachelors">Bachelors</option>
            <option value="Masters">Masters</option>
            <option value="Doctorate">Doctorate</option>
            <option value="Other">Other</option>
        </select><br><br>

        <label for="specialization">Subject Specialization:</label>
        <input type="text" id="specialization" name="specialization" required><br><br>

        <h2>Supporting Documents (Optional)</h2>
        <p>Upload if available:</p>

        <label for="resumeCV">Resume/CV:</label>
        <input type="file" id="resumeCV" name="resumeCV" accept=".pdf,.doc,.docx"><br><br>

        <label for="prcId">PRC ID (If Applicable):</label>
        <input type="file" id="prcId" name="prcId" accept=".pdf,.jpg,.png"><br><br>

        <label for="certifications">Certificates (Training/Seminars):</label>
        <input type="file" id="certifications" name="certifications" accept=".pdf,.jpg,.png" multiple><br><br>

        <label for="otherDocuments">
            Other Supporting Documents (Resume/CV, PRC ID, Certificates, Diploma/TOR, NBI, Government ID, etc. — merged in one file)
        </label>
        <input type="file" id="otherDocuments" name="otherDocuments" accept=".pdf,.doc,.docx,.jpg,.png">
<br><br>

        <button type="submit">Submit</button>
    </form>
</body>
</html>