document.addEventListener("DOMContentLoaded", () => {

    const strandSelect  = document.getElementById("strand");
    const gradeSelect   = document.getElementById("grade_level");
    const sectionSelect = document.getElementById("section");
    const subjectsContainer = document.getElementById("subjects-container");

    if (!subjectsContainer) {
        console.error("subjects-container not found in HTML");
        return;
    }

    /* LOAD STRANDS */
    fetch("/SMS_CDONHS-SHS_WEBSITE/Back_End_Files/PHP_Files/get_strands.php")
        .then(res => res.json())
        .then(data => {
            data.forEach(strand => {
                strandSelect.innerHTML += 
                    `<option value="${strand.strand_id}">${strand.strand_name}</option>`;
            });
        });

    /* LOAD SECTIONS + SUBJECTS */
    function loadSections() {

          if (!subjectsContainer) return;

        const grade = gradeSelect.value;
        const strand = strandSelect.value;

        sectionSelect.innerHTML = `<option value="">Select Section</option>`;
        subjectsContainer.innerHTML = "";

        if (grade && strand) {

            /* Load Sections */
            fetch(`/SMS_CDONHS-SHS_WEBSITE/Back_End_Files/PHP_Files/get_sections.php?grade_level=${grade}&strand_id=${strand}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(section => {
                        sectionSelect.innerHTML += 
                            `<option value="${section.section_id}">${section.section_name}</option>`;
                    });
                });

            /* Load Subjects */
           fetch(`/SMS_CDONHS-SHS_WEBSITE/Back_End_Files/PHP_Files/get_subjects.php?grade_level=${grade}&strand_id=${strand}`)
    .then(res => res.json())
    .then(data => {

        // Check success
        if (!data.success) {
            subjectsContainer.innerHTML = `
                <tr>
                    <td colspan="2">${data.message}</td>
                </tr>
            `;
            return;
        }

        const subjects = data.subjects; // <-- use this array

        if (!Array.isArray(subjects) || subjects.length === 0) {
            subjectsContainer.innerHTML = `
                <tr>
                    <td colspan="2">No subjects available.</td>
                </tr>
            `;
            return;
        }

        subjectsContainer.innerHTML = "";

        subjects.forEach(sub => {
            let checked = sub.enrolled ? "checked" : "";
            subjectsContainer.innerHTML += `
                <tr>
                    <td>${sub.subject_name}</td>
                    <td>
                        <input type="checkbox" 
                               name="subjects[]" 
                               value="${sub.subject_id}" 
                               ${checked}>
                    </td>
                </tr>
            `;
        });

    })
    .catch(err => {
        subjectsContainer.innerHTML = `
            <tr>
                <td colspan="2">Error loading subjects.</td>
            </tr>
        `;
        console.error(err);
    });
        }
    }

    gradeSelect.addEventListener("change", loadSections);
    strandSelect.addEventListener("change", loadSections);

});


/* ========================= */
/* FORM SUBMIT */
/* ========================= */
document.getElementById("enlistment-form")
.addEventListener("submit", function(e) {

    e.preventDefault();

    const grade_level = document.getElementById("grade_level").value;
    const strand_id   = document.getElementById("strand").value;
    const section_id  = document.getElementById("section").value;
    
    // Get ALL subject checkboxes (both checked and unchecked)
    const allSubjectCheckboxes = document.querySelectorAll('input[name="subjects[]"]');
    const checkedSubjects = document.querySelectorAll('input[name="subjects[]"]:checked');

    if (!grade_level || !strand_id || !section_id) {
        alert("Please select grade level, strand, and section.");
        return;
    }

    if (checkedSubjects.length === 0) {
        alert("Please select at least one subject.");
        return;
    }

    // Build array with all subjects and their checked state
    const subjects = Array.from(allSubjectCheckboxes).map(cb => ({
        subject_id: cb.value,
        requested: cb.checked ? 1 : 0
    }));

    fetch("/SMS_CDONHS-SHS_WEBSITE/Back_End_Files/PHP_Files/save_enlistment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            grade_level,
            strand_id,
            section_id,
            subjects
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Enlistment successfully saved! Status is now pending for admin approval.");
            window.location.href = 
            "/SMS_CDONHS-SHS_WEBSITE/Website_Files/Student_Files/home.php";
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => console.error(err));

});
