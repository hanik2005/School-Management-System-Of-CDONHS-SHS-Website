// Generate random captcha
        function generateCaptcha() {
            var num1 = Math.floor(Math.random() * 10) + 1;
            var num2 = Math.floor(Math.random() * 10) + 1;
            var sum = num1 + num2;
            document.getElementById('captchaLabel').innerText = 'Solve Authentication: What is ' + num1 + ' + ' + num2 + '?';
            document.getElementById('correct_sum').value = sum;
        }
        // Generate on page load
        window.onload = generateCaptcha;