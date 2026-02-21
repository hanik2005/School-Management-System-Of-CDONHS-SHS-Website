<script>
       function printCertificates() {
            // Student data from PHP
            const students = <?php echo json_encode($honorList); ?>;
            const quarterLabel = <?php echo json_encode($quarterLabel); ?>;
            
            if (students.length === 0) {
                alert('No students to print certificates for.');
                return;
            }
            
            // Create print window
            const printWindow = window.open('', '', 'height=800,width=1000');
            
            // Certificate styles
            const certificateStyles = `
                <style>
                    @page {
                        size: letter portrait;
                        margin: 0;
                    }
                    
                    body {
                        margin: 0;
                        padding: 0;
                        font-family: 'Times New Roman', Times, serif;
                    }
                    
                    .certificate-page {
                        width: 8.5in;
                        height: 11in;
                        padding: 0.75in;
                        box-sizing: border-box;
                        page-break-after: always;
                        position: relative;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        background: linear-gradient(135deg, #fefefe 0%, #f8f9fa 100%);
                    }
                    
                    .certificate-page:last-child {
                        page-break-after: auto;
                    }
                    
                    /* Decorative Border */
                    .certificate-border {
                        position: absolute;
                        top: 0.4in;
                        left: 0.4in;
                        right: 0.4in;
                        bottom: 0.4in;
                        border: 3px double #1e3a8a;
                        pointer-events: none;
                    }
                    
                    .certificate-border::before {
                        content: '';
                        position: absolute;
                        top: 0.15in;
                        left: 0.15in;
                        right: 0.15in;
                        bottom: 0.15in;
                        border: 2px solid #fbbf24;
                    }
                    
                    /* Corner Decorations */
                    .corner-decoration {
                        position: absolute;
                        width: 60px;
                        height: 60px;
                        border: 3px solid #fbbf24;
                    }
                    
                    .corner-tl {
                        top: 0.5in;
                        left: 0.5in;
                        border-right: none;
                        border-bottom: none;
                    }
                    
                    .corner-tr {
                        top: 0.5in;
                        right: 0.5in;
                        border-left: none;
                        border-bottom: none;
                    }
                    
                    .corner-bl {
                        bottom: 0.5in;
                        left: 0.5in;
                        border-right: none;
                        border-top: none;
                    }
                    
                    .corner-br {
                        bottom: 0.5in;
                        right: 0.5in;
                        border-left: none;
                        border-top: none;
                    }
                    
                    /* Logo */
                    .logo-container {
                        margin-top: 0.3in;
                        margin-bottom: 0.2in;
                    }
                    
                    .logo-container img {
                        width: 100px;
                        height: 100px;
                        object-fit: contain;
                    }
                    
                    /* School Name */
                    .school-name {
                        text-align: center;
                        margin-bottom: 0.15in;
                    }
                    
                    .school-name h1 {
                        font-size: 18pt;
                        color: #1e3a8a;
                        margin: 0;
                        font-weight: bold;
                        text-transform: uppercase;
                        letter-spacing: 1px;
                    }
                    
                    .school-name h2 {
                        font-size: 14pt;
                        color: #1e3a8a;
                        margin: 5px 0 0 0;
                        font-weight: normal;
                    }
                    
                    /* Certificate Title */
                    .certificate-title {
                        text-align: center;
                        margin: 0.3in 0;
                    }
                    
                    .certificate-title h1 {
                        font-size: 28pt;
                        color: #fbbf24;
                        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                        margin: 0;
                        font-weight: bold;
                        letter-spacing: 3px;
                    }
                    
                    /* Certificate Content */
                    .certificate-content {
                        text-align: center;
                        flex: 1;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                    }
                    
                    .congratulations {
                        font-size: 14pt;
                        color: #333;
                        margin-bottom: 0.2in;
                    }
                    
                    .student-name {
                        font-size: 26pt;
                        color: #1e3a8a;
                        font-weight: bold;
                        margin: 0.15in 0;
                        text-transform: uppercase;
                        letter-spacing: 2px;
                        border-bottom: 2px solid #fbbf24;
                        display: inline-block;
                        padding-bottom: 5px;
                    }
                    
                    .honor-message {
                        font-size: 13pt;
                        color: #333;
                        margin: 0.15in 0;
                        line-height: 1.6;
                    }
                    
                    .details-box {
                        background: linear-gradient(135deg, #1e3a8a, #111827);
                        color: #fbbf24;
                        padding: 15px 40px;
                        margin: 0.2in auto;
                        border-radius: 8px;
                        text-align: center;
                    }
                    
                    .details-box p {
                        margin: 5px 0;
                        font-size: 12pt;
                    }
                    
                    .details-box .average {
                        font-size: 16pt;
                        font-weight: bold;
                    }
                    
                    /* Footer */
                    .certificate-footer {
                        text-align: center;
                        margin-top: auto;
                        padding-top: 0.3in;
                    }
                    
                    .date-issued {
                        font-size: 11pt;
                        color: #666;
                        margin-bottom: 0.3in;
                    }
                    
                    .signature-line {
                        width: 200px;
                        border-top: 1px solid #333;
                        margin: 0 auto;
                        padding-top: 5px;
                        font-size: 10pt;
                        color: #333;
                    }
                    
                    /* Print specific */
                    @media print {
                        body {
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                        }
                    }
                </style>
            `;
            
            // Get current date
            const today = new Date();
            const dateStr = today.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            // Build certificates HTML
            let certificatesHTML = '';
            
            students.forEach((student, index) => {
                const fullName = student.last_name + ', ' + student.first_name + 
                    (student.middle_name ? ' ' + student.middle_name.charAt(0) + '.' : '');
                
                certificatesHTML += `
                    <div class="certificate-page">
                        <div class="certificate-border"></div>
                        <div class="corner-decoration corner-tl"></div>
                        <div class="corner-decoration corner-tr"></div>
                        <div class="corner-decoration corner-bl"></div>
                        <div class="corner-decoration corner-br"></div>
                        
                        <div class="logo-container">
                            <img src="../../Assets/LOGO.png" alt="School Logo">
                        </div>
                        
                        <div class="school-name">
                            <h1>Cagayan De Oro National High School</h1>
                            <h2>Senior High School</h2>
                        </div>
                        
                        <div class="certificate-title">
                            <h1>CERTIFICATE</h1>
                        </div>
                        
                        <div class="certificate-content">
                            <p class="congratulations">This is to certify that</p>
                            
                            <p class="student-name">${fullName}</p>
                            
                            <p class="honor-message">
                                <strong>Congratulations!</strong> You are in the <strong>Honor List</strong><br>
                                for outstanding academic excellence.
                            </p>
                            
                            <div class="details-box">
                                <p><strong>${quarterLabel}</strong></p>
                                <p>Grade ${student.grade_level} - ${student.strand_name} - Section ${student.section_name}</p>
                                <p class="average">Average: ${parseFloat(student.average_grade).toFixed(2)}</p>
                            </div>
                        </div>
                        
                        <div class="certificate-footer">
                            <p class="date-issued">Given this ${dateStr}</p>
                            <p class="signature-line">Teacher / Adviser</p>
                        </div>
                    </div>
                `;
            });
            
            // Write to print window
            printWindow.document.write('<!DOCTYPE html>');
            printWindow.document.write('<html><head><title>Honor List Certificates - CDONHS-SHS</title>');
            printWindow.document.write(certificateStyles);
            printWindow.document.write('</head><body>');
            printWindow.document.write(certificatesHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            
            // Wait for images to load then print
            printWindow.focus();
            setTimeout(function() {
                printWindow.print();
                printWindow.close();
            }, 500);
        }
    </script>