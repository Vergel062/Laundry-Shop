const password = document.getElementById('password');
        const confirmPw = document.getElementById('confirm_password');
        const feedback = document.getElementById('pw-feedback');
        const submitBtn = document.getElementById('submitBtn');

        function validatePassword() {
            // Huwag magpakita ng error kung wala pang laman ang confirm field
            if (confirmPw.value === "") {
                confirmPw.classList.remove('is-valid', 'is-invalid');
                feedback.innerHTML = "";
                return;
            }

            if (password.value === confirmPw.value) {
                // Match: Green light
                confirmPw.classList.remove('is-invalid');
                confirmPw.classList.add('is-valid');
                feedback.innerHTML = "✓ Passwords match!";
                feedback.style.color = "#22c55e";
                submitBtn.disabled = false;
            } else {
                // No Match: Red light
                confirmPw.classList.remove('is-valid');
                confirmPw.classList.add('is-invalid');
                feedback.innerHTML = "✗ Passwords do not match.";
                feedback.style.color = "#ef4444";
                submitBtn.disabled = true;
            }
        }

        // Mag-check tuwing nagta-type ang user
        password.addEventListener('input', validatePassword);
        confirmPw.addEventListener('input', validatePassword);