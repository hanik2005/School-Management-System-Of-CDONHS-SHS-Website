<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Online Enrollment</title>
    <link rel="icon" href="../Assets/LOGO.png" type="image/jpg">
    <link rel="stylesheet" href="../Design/Online_Form_Design.css">
</head>
<body>

<form action="../Back_End_Files/PHP_Files/student_enrollment_backend.php" 
      method="POST" enctype="multipart/form-data">

    <!-- HEADER -->
    <div class="form-header">
        <img src="../Assets/LOGO.png" alt="School Logo">
        <div class="header-text">
            <h3>Republic of the Philippines</h3>
            <h3>Department of Education</h3>
            <h2>CAGAYAN DE ORO NATIONAL HIGH SCHOOL - SENIOR HIGH</h2>
            <h1>ONLINE ENROLLMENT FORM</h1>
        </div>
    </div>

    <hr>

    <!-- STUDENT INFORMATION -->
    <h2>STUDENT PERSONAL INFORMATION</h2>

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
            <label>Learner Reference Number (LRN)</label>
            <input type="text" name="lrn" maxlength="12" pattern="[0-9]{12}" placeholder="Example: 123456789012" title="Enter 12-digit LRN (numbers only)" required>
        </div>

        <div>
            <label>Date of Birth</label>
            <input type="date" name="dob" required>
        </div>

        <div>
            <label>Sex</label>
            <select name="sex" required>
                <option value="">Select</option>
                <option>Male</option>
                <option>Female</option>
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
            <input type="tel" name="contactNumber" pattern="^09[0-9]{9}$" maxlength="11" minlength="11" placeholder="Example: 09123456789" title="Enter 11-digit mobile number starting with 09" required>
        </div>

        <div>
            <label>Email</label>
            <input type="email" name="email" required>
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

    <!-- SCHOOL INFORMATION -->
    <h2>PREVIOUS SCHOOL INFORMATION</h2>

    <div class="form-grid">

        <div>
            <label>Last School Attended</label>
            <input type="text" name="currentSchool" required>
        </div>

        <div>
            <label>School Classification</label>
            <select name="schoolClassification" required>
                <option value="">Select</option>
                <option>Public</option>
                <option>Private</option>
            </select>
        </div>

        <div>
            <label>Enrollment Type</label>
            <select name="enrollmentType" required>
                <option value="New">New</option>
                <option value="Transferee">Transferee</option>
                <option value="Balik-Eskwela">Balik-Eskwela</option>
            </select>
        </div>


        <div>
            <label>Year Graduated</label>
            <input type="number" name="yearGraduated" min="1900" max="2026" required>
        </div>

    </div>

    <!-- PARENT INFORMATION -->
    <h2>PARENT / GUARDIAN INFORMATION</h2>

    <div class="form-grid">

        <div>
            <label>Father/Guardian Name</label>
            <input type="text" name="fatherGuardianName" required>
        </div>

        <div>
            <label>Father/Guardian Contact</label>
            <input type="tel" name="fatherGuardianContact" pattern="^09[0-9]{9}$" maxlength="11" minlength="11" placeholder="Example: 09123456789" title="Enter 11-digit mobile number starting with 09" required>
        </div>

        <div>
            <label>Mother/Guardian Name</label>
            <input type="text" name="motherGuardianName" required>
        </div>

        <div>
            <label>Mother/Guardian Contact</label>
            <input type="tel" name="motherGuardianContact" pattern="^09[0-9]{9}$" maxlength="11" minlength="11" placeholder="Example: 09123456789" title="Enter 11-digit mobile number starting with 09" required>
        </div>

    </div>

    <!-- REQUIREMENTS -->
    <h2>ENROLLMENT REQUIREMENTS (If Available)</h2>

    <div class="form-grid">

        <div>
            <label>PSA Birth Certificate</label>
            <input type="file" name="psaBirthCertificate" accept=".pdf,.jpg,.png">
        </div>

        <div>
            <label>Form 138 / Report Card</label>
            <input type="file" name="form138" accept=".pdf,.jpg,.png">
        </div>

        <div>
            <label>Student ID</label>
            <input type="file" name="studentID" accept=".pdf,.jpg,.png">
        </div>

    </div>

    <button type="submit">SUBMIT ENROLLMENT</button>

</form>

</body>
</html>
