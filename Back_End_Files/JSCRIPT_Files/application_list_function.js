// ==============================
// Application List Functionality
// ==============================

document.addEventListener("DOMContentLoaded", function() {
    // Initialize batch update functionality

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
            
            // Show/hide batch update fields based on selection
            const row = checkbox.closest(".application-row");
            if (row) {
                const batchFields = row.querySelector(".batch-update-fields");
                if (batchFields) {
                    batchFields.style.display = checkbox.checked ? "block" : "none";
                }
            }
        });
    });

    // ==============================
    // CONFIRM BATCH BUTTON
    // ==============================
    const confirmBatchBtn = document.getElementById("confirmBatchBtn");

    if (confirmBatchBtn) {
        confirmBatchBtn.addEventListener("click", function() {
            // Get all checked checkboxes
            const checkedCheckboxes = document.querySelectorAll(".row-checkbox:checked");
            
            if (checkedCheckboxes.length === 0) {
                alert("Please select at least one row to confirm.");
                return;
            }
            
            // Collect data from checked rows
            const updates = [];
            let hasValidationError = false;
            
            checkedCheckboxes.forEach(checkbox => {
                const row = checkbox.closest(".application-row");
                if (!row) return;
                
                const applicationId = row.dataset.applicationId;
                const batchFields = row.querySelector(".batch-update-fields");
                
                if (batchFields) {
                    const remarks = batchFields.querySelector(".batch-remarks")?.value.trim() || "";
                    const status = batchFields.querySelector(".batch-status")?.value || "Pending";
                    const advisorySelect = batchFields.querySelector(".batch-advisory");
                    const advisory = advisorySelect ? advisorySelect.value : null;
                    
                    // Validate: if status is Rejected, remarks are required
                    if (status === "Rejected" && remarks === "") {
                        hasValidationError = true;
                        return;
                    }
                    
                    const updateData = {
                        application_id: applicationId,
                        remarks: remarks,
                        status: status,
                        advisory: advisory
                    };
                    
                    updates.push(updateData);
                }
            });
            
            if (hasValidationError) {
                alert("Please enter remarks for all rejected applications.");
                return;
            }
            
            if (updates.length === 0) {
                alert("No applications to update.");
                return;
            }
            
            // Determine type based on the page
            const isTeacherPage = document.body.innerHTML.includes('Teacher Applications') || document.body.innerHTML.includes('Teacher Application List');
            const type = isTeacherPage ? 'teacher' : 'student';
            
            // Select the appropriate backend file
            const backendFile = isTeacherPage 
                ? "../../Back_End_Files/PHP_Files/teacher_update_remarks.php"
                : "../../Back_End_Files/PHP_Files/student_update_remarks.php";
            
            // Show loading modal
            const loadingModal = document.getElementById("loadingModal");
            if (loadingModal) {
                loadingModal.classList.add("active");
            }

            // Send to backend
            fetch(backendFile, {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({
                    type: type,
                    updates: updates
                })
            })
            .then(res => res.json())
            .then(resp => {
                // Hide loading modal
                if (loadingModal) {
                    loadingModal.classList.remove("active");
                }
                
                if (resp.success) {
                    showSuccessModal(resp.message || "Applications updated successfully!");
                } else {
                    alert(resp.message || "Failed to update applications.");
                }
            })
            .catch(err => {
                // Hide loading modal on error
                if (loadingModal) {
                    loadingModal.classList.remove("active");
                }
                
                alert("Error updating applications. Please try again.");
            });
        });
    }

    // ==============================
    // SUCCESS MODAL FUNCTIONS
    // ==============================
    function showSuccessModal(message) {
        const successModal = document.getElementById("successModal");
        const successMessage = document.getElementById("successMessage");
        
        if (successMessage) {
            successMessage.textContent = message;
        }
        
        if (successModal) {
            successModal.classList.add("active");
        }
    }

    function closeSuccessModal() {
        const successModal = document.getElementById("successModal");
        if (successModal) {
            successModal.classList.remove("active");
        }
        
        // Reload page to show updated statuses
        location.reload();
    }

    // Close modal when clicking outside
    window.addEventListener("click", function(event) {
        const successModal = document.getElementById("successModal");
        if (successModal && event.target === successModal) {
            closeSuccessModal();
        }
    });
});
