function togglePassword() {
    var passwordField = document.getElementById("password");
    var toggleIcon = document.getElementById("toggleIcon");
    if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleIcon.src = "../Assets/Visible.png";
    } else {
        passwordField.type = "password";
        toggleIcon.src = "../Assets/NotVisible.png";
    }
}
