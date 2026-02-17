// ==============================
// Grade Validation Functionality
// ==============================
console.log("Hello from grade_validation_function.js");
// Clear dashboard helper
function clearDashboard() {
    document.getElementById("dashboardContent").innerHTML =
        "<p style='text-align:center;'>Click a row to display grades</p>";
}

// ==============================
// FILTER FORM - SEARCH & CLEAR
// ==============================
const filterForm = document.getElementById("filterForm");
filterForm.addEventListener("submit", function(e) {
    // Allow normal submission (reloads page with filtered data)
});

document.getElementById("clearFilters").addEventListener("click", function() {
    document.getElementById("grade_level").value = '';
    document.getElementById("quarter").value = '';
    document.getElementById("status").value = '';
    filterForm.submit(); // reloads page with empty filters
});

// ==============================
// CLICK VALIDATION ROW TO DISPLAY GRADES
// ==============================
// Validation Row Click
document.querySelectorAll(".validation-row").forEach(row => {
    row.addEventListener("click", function(){
        let grade   = this.dataset.grade;
        let section = this.dataset.section;
        let quarter = this.dataset.quarter; 

        console.log("GRADE:", grade);
        console.log("SECTION:", section);
        console.log("QUARTER:", quarter);

        fetch(`../../Back_End_Files/PHP_Files/fetch_teacher_grades.php?grade=${grade}&section=${section}&quarter=${quarter}`)
        .then(res => res.json())
        .then(data => {
            const dashboard = document.getElementById("dashboardContent");
            dashboard.innerHTML = ""; // clear old content

            // If backend returned an error
            if (data.error) {
                dashboard.innerHTML = `<p style="text-align:center;color:red;">${data.error}</p>`;
                return;
            }

            // If not an array, show no grades
            if (!Array.isArray(data) || data.length === 0) {
                dashboard.innerHTML = `<p style="text-align:center;">No grades available</p>`;
                return;
            }

            // Group by subject
            let subjects = {};
            data.forEach(item => {
                if(!subjects[item.subject_name]) subjects[item.subject_name] = [];
                subjects[item.subject_name].push(item);
            });

            // Create table per subject
            Object.keys(subjects).forEach(subject => {
                let tableContainer = document.createElement("div");
                tableContainer.classList.add("subject-table-container");

                let title = document.createElement("h3");
                title.textContent = subject;
                tableContainer.appendChild(title);

                let table = document.createElement("table");
                table.classList.add("dashboard-subject-table");
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Student Name</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                `;

                let tbody = table.querySelector("tbody");
                subjects[subject].forEach((student, index) => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${student.last_name}, ${student.first_name}</td>
                            <td>${student.grade}</td>
                        </tr>
                    `;
                });

                tableContainer.appendChild(table);
                dashboard.appendChild(tableContainer);
            });
        })
        .catch(err => {
            console.error("Failed to fetch grades:", err);
            document.getElementById("dashboardContent").innerHTML = `<p style="text-align:center;color:red;">Failed to fetch grades</p>`;
        });
    });
});


// ==============================
// CONFIRM BUTTON - UPDATE STATUS
// ==============================
document.getElementById("confirmBtn").addEventListener("click", function() {
    const selects = document.querySelectorAll(".status-select");
    const updates = [];

    selects.forEach(sel => {
        const row = sel.closest("tr");
        updates.push({
            grade: row.dataset.grade,
            section: row.dataset.section,
            quarter: row.dataset.quarter,
            status: sel.value
        });
    });

    if (updates.length === 0) {
        alert("No grades to update.");
        return;
    }

    fetch("../../Back_End_Files/PHP_Files/update_grade_status.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({updates})
    })
    .then(res => res.json())
    .then(resp => {
        if(resp.success){
            alert(resp.message || "Grades updated successfully!");
            clearDashboard();
        } else {
            alert(resp.message || "Failed to update grades.");
        }
    })
    .catch(err => {
        console.error("Failed to update grades:", err);
        alert("Error updating grades. Check console.");
    });
});

// ==============================
// CLEAR BUTTON - CLEAR DASHBOARD
// ==============================
document.getElementById("clearBtn").addEventListener("click", clearDashboard);
