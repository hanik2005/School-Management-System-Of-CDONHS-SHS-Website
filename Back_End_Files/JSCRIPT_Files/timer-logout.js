let idleTimeLimit = 60 * 1000; // 1 minute before showing modal
let logoutTimeLimit = 60 * 1000; // 1 minute countdown before logout
let idleTimer, logoutTimer;
let countdownInterval;
let modal; // reference to dynamically created modal
let countdown;

function createModal() {
    // create modal container
    modal = document.createElement("div");
    modal.style.position = "fixed";
    modal.style.top = "0";
    modal.style.left = "0";
    modal.style.width = "100%";
    modal.style.height = "100%";
    modal.style.backgroundColor = "rgba(0, 0, 0, 0.6)";
    modal.style.display = "flex";
    modal.style.justifyContent = "center";
    modal.style.alignItems = "center";
    modal.style.zIndex = "1000";
    modal.style.animation = "fadeIn 0.3s ease";

    // modal content
    const content = document.createElement("div");
    content.style.backgroundColor = "#ffffff";
    content.style.padding = "30px 25px";
    content.style.borderRadius = "12px";
    content.style.textAlign = "center";
    content.style.minWidth = "350px";
    content.style.maxWidth = "90%";
    content.style.boxShadow = "0 8px 25px rgba(0,0,0,0.2)";
    content.style.fontFamily = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";

    const title = document.createElement("h3");
    title.textContent = "Session Timeout Warning";
    title.style.marginBottom = "15px";
    title.style.color = "#333";

    const message = document.createElement("p");
    message.innerHTML = 'You have been inactive.<br>Your session will close in <span id="countdown">60</span> seconds.';
    message.style.fontSize = "16px";
    message.style.color = "#555";
    message.style.lineHeight = "1.5";

    countdown = message.querySelector("#countdown");
    countdown.style.fontWeight = "bold";
    countdown.style.color = "#d9534f"; // red for warning

    const btnContainer = document.createElement("div");
    btnContainer.style.marginTop = "20px";
    btnContainer.style.display = "flex";
    btnContainer.style.justifyContent = "center";
    btnContainer.style.gap = "15px";

    const stayBtn = document.createElement("button");
    stayBtn.textContent = "Stay Logged In";
    stayBtn.style.padding = "10px 18px";
    stayBtn.style.border = "none";
    stayBtn.style.borderRadius = "6px";
    stayBtn.style.backgroundColor = "#5cb85c"; // green
    stayBtn.style.color = "#fff";
    stayBtn.style.cursor = "pointer";
    stayBtn.style.fontSize = "14px";
    stayBtn.style.transition = "background-color 0.3s ease";
    stayBtn.addEventListener("mouseenter", () => stayBtn.style.backgroundColor = "#4cae4c");
    stayBtn.addEventListener("mouseleave", () => stayBtn.style.backgroundColor = "#5cb85c");

    const logoutBtn = document.createElement("button");
    logoutBtn.textContent = "Logout";
    logoutBtn.style.padding = "10px 18px";
    logoutBtn.style.border = "none";
    logoutBtn.style.borderRadius = "6px";
    logoutBtn.style.backgroundColor = "#d9534f"; // red
    logoutBtn.style.color = "#fff";
    logoutBtn.style.cursor = "pointer";
    logoutBtn.style.fontSize = "14px";
    logoutBtn.style.transition = "background-color 0.3s ease";
    logoutBtn.addEventListener("mouseenter", () => logoutBtn.style.backgroundColor = "#c9302c");
    logoutBtn.addEventListener("mouseleave", () => logoutBtn.style.backgroundColor = "#d9534f");

    btnContainer.appendChild(stayBtn);
    btnContainer.appendChild(logoutBtn);
    content.appendChild(title);
    content.appendChild(message);
    content.appendChild(btnContainer);
    modal.appendChild(content);
    document.body.appendChild(modal);

    // Button actions
    stayBtn.addEventListener("click", () => {
        modal.remove();
        modal = null;
        resetIdleTimer();
    });

    logoutBtn.addEventListener("click", () => autoLogout());

    // Add fade-in animation keyframes
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes fadeIn {
            from {opacity: 0;}
            to {opacity: 1;}
        }
    `;
    document.head.appendChild(style);
}


function resetIdleTimer() {
    // Only reset timer if modal is NOT visible
    if (modal) return;

    clearTimeout(idleTimer);
    clearTimeout(logoutTimer);
    clearInterval(countdownInterval);

    idleTimer = setTimeout(showWarning, idleTimeLimit);
}

function showWarning() {
    createModal(); // dynamically create modal
    let timeLeft = 60;
    countdown.textContent = timeLeft;

    countdownInterval = setInterval(() => {
        timeLeft--;
        countdown.textContent = timeLeft;
        if (timeLeft <= 0) clearInterval(countdownInterval);
    }, 1000);

    logoutTimer = setTimeout(autoLogout, logoutTimeLimit);
}

function autoLogout() {
    window.location.href = "../../Back_End_Files/PHP_Files/logout.php";
}

// Detect user activity
["mousemove", "keydown", "scroll", "touchstart"].forEach(evt =>
    document.addEventListener(evt, resetIdleTimer)
);

// Start idle timer
resetIdleTimer();
