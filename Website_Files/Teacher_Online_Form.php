<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Registration Application</title>
    <link rel="icon" href="../Assets/LOGO.png" type="image/jpg">
    <link rel="stylesheet" href="../Design/Online_Form_Design.css">
</head>
<body>

<form action="../Back_End_Files/PHP_Files/teacher_enrollment_backend.php" 
      method="POST" enctype="multipart/form-data">

    <!-- HEADER -->
    <div class="form-header">
        <img src="../Assets/LOGO.png" alt="School Logo">
        <div class="header-text">
            <h3>Republic of the Philippines</h3>
            <h3>Department of Education</h3>
            <h2>CAGAYAN DE ORO NATIONAL HIGH SCHOOL - SENIOR HIGH</h2>
            <h1>TEACHER REGISTRATION APPLICATION FORM</h1>
        </div>
    </div>

    <hr>

    <!-- PERSONAL INFORMATION -->
    <h2>PERSONAL INFORMATION</h2>

    <div class="form-grid">

        <div>
            <label>First Name</label>
            <input type="text" name="firstName" required>
        </div>

        <div>
            <label>Last Name</label>
            <input type="text" name="lastName" required>
        </div>

        <div>
            <label>Middle Name</label>
            <input type="text" name="middleName">
        </div>

        <div>
            <label>Extension Name</label>
            <input type="text" name="extensionName">
        </div>

        <div>
            <label>Date of Birth</label>
            <input type="date" name="dob" required>
        </div>

        <div>
            <label>Gender</label>
            <select name="gender" required>
                <option value="">Select</option>
                <option>Male</option>
                <option>Female</option>
                <option>Other</option>
            </select>
        </div>

        <div>
            <label>Civil Status</label>
            <select name="civilStatus" required>
                <option value="">Select</option>
                <option>Single</option>
                <option>Married</option>
                <option>Widowed</option>
            </select>
        </div>

        <div>
            <label>Contact Number</label>
            <input type="tel" name="contactNumber" required>
        </div>

        <div>
            <label>Email Address</label>
            <input type="email" name="email" required>
        </div>

        <div>
            <label>Facebook Profile (Optional)</label>
            <input type="url" name="facebookProfile">
        </div>

    </div>

    <!-- ADDRESS -->
    <h2>HOME ADDRESS</h2>

    <div class="form-grid">

        <div>
            <label>House No. / Street</label>
            <input type="text" name="houseNumberStreet" required>
        </div>

        <div>
            <label>Barangay</label>
            <input type="text" name="barangay" required>
        </div>

        <div>
            <label>City / Municipality</label>
            <input type="text" name="cityMunicipality" required>
        </div>

        <div>
            <label>Province</label>
            <input type="text" name="province" required>
        </div>

    </div>

    <!-- EDUCATION BACKGROUND -->
    <h2>EDUCATIONAL BACKGROUND</h2>

    <div class="form-grid">

        <div>
            <label>Current / Last School Taught</label>
            <input type="text" name="currentSchool" required>
        </div>

        <div>
            <label>Highest Educational Attainment</label>
            <select name="highestEducation" required>
                <option value="">Select</option>
                <option>Bachelor's Degree</option>
                <option>Master's Degree</option>
                <option>Doctorate Degree</option>
                <option>Other</option>
            </select>
        </div>

        <div>
            <label>Subject Specialization</label>
            <input type="text" name="specialization" required>
        </div>

    </div>

    <!-- SUPPORTING DOCUMENTS -->
    <h2>SUPPORTING DOCUMENTS (Optional)</h2>

    <div class="form-grid">

        <div>
            <label>Resume / Curriculum Vitae</label>
            <input type="file" name="resumeCV" accept=".pdf,.doc,.docx">
        </div>

        <div>
            <label>PRC ID (If Applicable)</label>
            <input type="file" name="prcId" accept=".pdf,.jpg,.png">
        </div>

        <div>
            <label>Certificates (Trainings / Seminars)</label>
            <input type="file" name="certifications" accept=".pdf,.jpg,.png" multiple>
        </div>

        <div>
            <label>Other Supporting Documents (Merged File)</label>
            <input type="file" name="otherDocuments" accept=".pdf,.doc,.docx,.jpg,.png">
        </div>

    </div>

    <button type="submit">SUBMIT REGISTRATION APPLICATION</button>

</form>

</body>
</html>
