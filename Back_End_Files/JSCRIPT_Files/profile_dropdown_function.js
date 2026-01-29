document.addEventListener("DOMContentLoaded", () => {
    const profileBtn = document.querySelector(".profile-btn");
    const dropdown = document.querySelector(".profile-dropdown");

    profileBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        dropdown.style.display =
            dropdown.style.display === "flex" ? "none" : "flex";
    });

    // close when clicking outside
    document.addEventListener("click", () => {
        dropdown.style.display = "none";
    });
});
