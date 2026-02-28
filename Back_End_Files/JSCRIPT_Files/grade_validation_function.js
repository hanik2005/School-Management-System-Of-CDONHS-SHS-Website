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
// SELECT ALL CHECKBOX FUNCTIONALITY
// ==============================
const selectAllCheckbox = document.getElementById("selectAllCheckbox");
const rowCheckboxes = document.querySelectorAll(".row-checkbox");

if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener("change", function() {
        const isChecked = this.checked;
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
        });
    });
}

// Update "select all" checkbox when individual checkboxes change
rowCheckboxes.forEach(checkbox => {
    checkbox.addEventListener("change", function() {
        const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
        const someChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
        
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }
    });
});

// ==============================
// PREVENT ROW CLICK WHEN CLICKING CHECKBOX
// ==============================
document.querySelectorAll(".row-checkbox").forEach(checkbox => {
    checkbox.addEventListener("click", function(e) {
        e.stopPropagation(); // Prevent row click when clicking checkbox
    });
});

// ==============================
// REMARKS COLUMN VISIBILITY
// ==============================
function updateRemarksVisibility() {
    const selects = document.querySelectorAll(".status-select");
    let hasRejected = false;
    
    selects.forEach(sel => {
        const row = sel.closest("tr");
        const remarksCell = row.querySelector(".remarks-cell");
        
        if (sel.value === "Rejected") {
            hasRejected = true;
            if (remarksCell) remarksCell.style.display = "";
        } else {
            if (remarksCell) remarksCell.style.display = "none";
        }
    });
    
    // Show/hide the remarks header
    const remarksHeader = document.querySelector(".remarks-header");
    if (remarksHeader) {
        remarksHeader.style.display = hasRejected ? "" : "none";
    }
}

// Attach change listeners to all status selects
document.querySelectorAll(".status-select").forEach(sel => {
    sel.addEventListener("change", updateRemarksVisibility);
});

// Initial check on page load
updateRemarksVisibility();

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
    row.addEventListener("click", function(e) {
        // Skip if clicking on checkbox
        if (e.target.classList.contains("row-checkbox")) return;
        // Skip if clicking on select or input
        if (e.target.tagName === "SELECT" || e.target.tagName === "INPUT") return;
        
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
// CONFIRM BUTTON - UPDATE STATUS (ONLY CHECKED ROWS)
// ==============================
document.getElementById("confirmBtn").addEventListener("click", function() {
    // Get all checked checkboxes
    const checkedCheckboxes = document.querySelectorAll(".row-checkbox:checked");
    
    if (checkedCheckboxes.length === 0) {
        alert("Please select at least one row to confirm.");
        return;
    }
    
    const updates = [];
    let hasValidationError = false;

    // Process only checked rows
    checkedCheckboxes.forEach(checkbox => {
        const row = checkbox.closest(".validation-row");
        const statusSelect = row.querySelector(".status-select");
        const remarksInput = row.querySelector(".remarks-input");
        const remarks = remarksInput ? remarksInput.value.trim() : "";
        
        // If status is Rejected, validate that remarks is provided
        if (statusSelect.value === "Rejected" && remarks === "") {
            hasValidationError = true;
            return; // Continue to next iteration
        }
        
        updates.push({
            grade: row.dataset.grade,
            section: row.dataset.section,
            quarter: row.dataset.quarter,
            status: statusSelect.value,
            remarks: statusSelect.value === "Rejected" ? remarks : ""
        });
    });

    if (hasValidationError) {
        alert("Please enter remarks for all rejected grades.");
        return;
    }

    if (updates.length === 0) {
        alert("No grades to update.");
        return;
    }

    console.log("Sending updates:", updates);

    // Show loading modal
    const loadingModal = document.getElementById("loadingModal");
    if (loadingModal) {
        loadingModal.classList.add("active");
    }

    fetch("../../Back_End_Files/PHP_Files/update_grade_status.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({updates})
    })
    .then(res => res.json())
    .then(resp => {
        console.log("Response:", resp);
        
        // Hide loading modal
        if (loadingModal) {
            loadingModal.classList.remove("active");
        }
        
        if(resp.success){
            alert(resp.message || "Grades updated successfully!");
            location.reload(); // Reload page to show updated statuses
        } else {
            alert(resp.message || "Failed to update grades.");
        }
    })
    .catch(err => {
        console.error("Failed to update grades:", err);
        
        // Hide loading modal on error
        if (loadingModal) {
            loadingModal.classList.remove("active");
        }
        
        alert("Error updating grades. Check console.");
    });
});

// ==============================
// CLEAR BUTTON - CLEAR DASHBOARD AND UNCHECK
// ==============================
document.getElementById("clearBtn").addEventListener("click", function() {
    clearDashboard();
    
    // Uncheck all checkboxes
    rowCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Reset select all checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }
});

// ==============================
// CONFIRM FILTER BUTTON (beside search)
// ==============================
document.getElementById("confirmFilterBtn").addEventListener("click", function() {
    // Trigger the same function as the main Confirm button
    document.getElementById("confirmBtn").click();
});
