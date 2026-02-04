document.addEventListener("DOMContentLoaded", () => {

    const strandSelect  = document.getElementById("strand");
    const gradeSelect   = document.getElementById("grade_level");
    const sectionSelect = document.getElementById("section");
    const subjectDashboard = document.querySelector(".subject-dashboard");

    /* LOAD STRANDS */
    fetch("/SMS_CDONHS-SHS_WEBSITE/Back_End_Files/PHP_Files/get_strands.php")
        .then(res => res.json())
        .then(data => {
            data.forEach(strand => {
                strandSelect.innerHTML += `<option value="${strand.strand_id}">${strand.strand_name}</option>`;
            });
        });

    /* LOAD SECTIONS */
    function loadSections() {
        const grade = gradeSelect.value;
        const strand = strandSelect.value;
        sectionSelect.innerHTML = `<option value="">Select Section</option>`;
        subjectDashboard.innerHTML = ""; // clear subjects when grade/strand changes

        if (grade && strand) {
            fetch(`/SMS_CDONHS-SHS_WEBSITE/Back_End_Files/PHP_Files/get_sections.php?grade_level=${grade}&strand_id=${strand}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(section => {
                        sectionSelect.innerHTML += `<option value="${section.section_id}">${section.section_name}</option>`;
                    });
                });

            // Load subjects for this grade and strand
            fetch(`/SMS_CDONHS-SHS_WEBSITE/Back_End_Files/PHP_Files/get_subjects.php?grade_level=${grade}&strand_id=${strand}`)
                .then(res => res.json())
                .then(subjects => {
                    if (subjects.error) {
                        subjectDashboard.innerHTML = "Error loading subjects";
                        return;
                    }

                    if (subjects.length === 0) {
                        subjectDashboard.innerHTML = "No subjects available for this strand.";
                        return;
                    }

                    // Display subjects with checkboxes
                    subjectDashboard.innerHTML = "";
                    subjects.forEach(sub => {
                        subjectDashboard.innerHTML += `
                            <label>
                                <input type="checkbox" name="subjects[]" value="${sub.subject_id}">
                                ${sub.subject_name}
                            </label><br>
                        `;
                    });
                });
        }
    }

    gradeSelect.addEventListener("change", loadSections);
    strandSelect.addEventListener("change", loadSections);

});

document.getElementById("enlistment-form").addEventListener("submit", function(e) {
    e.preventDefault(); // prevent normal form submission

    const grade_level = document.getElementById("grade_level").value;
    const strand_id   = document.getElementById("strand").value;
    const section_id  = document.getElementById("section").value;
    const subjectCheckboxes = document.querySelectorAll('input[name="subjects[]"]:checked');

    if (!grade_level || !strand_id || !section_id || subjectCheckboxes.length === 0) {
        alert("Please select grade level, strand, section, and at least one subject.");
        return;
    }

    const subjects = Array.from(subjectCheckboxes).map(cb => cb.value);

    // Send data via fetch to PHP
    fetch("/SMS_CDONHS-SHS_WEBSITE/Back_End_Files/PHP_Files/save_enlistment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            grade_level: grade_level,
            strand_id: strand_id,
            section_id: section_id,
            subjects: subjects
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Enlistment successfully saved!");
            window.location.href = "/SMS_CDONHS-SHS_WEBSITE/Website_Files/Student_Files/home.php";
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => console.error(err));
    console.log("Grade:", grade_level);
console.log("Strand:", strand_id);
console.log("Section:", section_id);
console.log("Subjects:", subjects);
});

