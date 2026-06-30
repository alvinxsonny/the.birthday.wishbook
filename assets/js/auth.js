/**
 * The Birthday Wishbook — Auth Form Validation
 * Client-side validation for signup, signin, and change-password forms
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ─── Signup Form Validation ───
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const confirm = document.getElementById('confirm_password');
            const birthday = document.getElementById('birthday');
            
            clearErrors();
            let hasError = false;
            
            // Username
            if (username.value.trim().length < 2) {
                showFieldError(username, 'Username must be at least 2 characters');
                hasError = true;
            }
            
            // Email
            if (!isValidEmail(email.value)) {
                showFieldError(email, 'Please enter a valid email');
                hasError = true;
            }
            
            // Birthday
            if (!birthday.value) {
                showFieldError(birthday, 'Please select your birthday');
                hasError = true;
            } else {
                const bday = new Date(birthday.value);
                if (bday > new Date()) {
                    showFieldError(birthday, 'Birthday cannot be in the future');
                    hasError = true;
                }
            }
            
            // Password
            if (password.value.length < 6) {
                showFieldError(password, 'Password must be at least 6 characters');
                hasError = true;
            }
            
            // Confirm
            if (password.value !== confirm.value) {
                showFieldError(confirm, 'Passwords do not match');
                hasError = true;
            }
            
            if (hasError) {
                e.preventDefault();
            }
        });
    }
    
    // ─── Signin Form Validation ───
    const signinForm = document.getElementById('signinForm');
    if (signinForm) {
        signinForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            
            clearErrors();
            let hasError = false;
            
            if (!isValidEmail(email.value)) {
                showFieldError(email, 'Please enter a valid email');
                hasError = true;
            }
            
            if (!password.value) {
                showFieldError(password, 'Please enter your password');
                hasError = true;
            }
            
            if (hasError) {
                e.preventDefault();
            }
        });
    }
    
    // ─── Change Password Form Validation ───
    const changeForm = document.getElementById('changePasswordForm');
    if (changeForm) {
        changeForm.addEventListener('submit', function(e) {
            const current = document.getElementById('current_password');
            const newPass = document.getElementById('new_password');
            const confirm = document.getElementById('confirm_password');
            
            clearErrors();
            let hasError = false;
            
            if (!current.value) {
                showFieldError(current, 'Please enter your current password');
                hasError = true;
            }
            
            if (newPass.value.length < 6) {
                showFieldError(newPass, 'New password must be at least 6 characters');
                hasError = true;
            }
            
            if (newPass.value !== confirm.value) {
                showFieldError(confirm, 'Passwords do not match');
                hasError = true;
            }
            
            if (newPass.value === current.value) {
                showFieldError(newPass, 'New password must be different');
                hasError = true;
            }
            
            if (hasError) {
                e.preventDefault();
            }
        });
    }
    
    // ─── Helpers ───
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    function showFieldError(input, message) {
        input.classList.add('error');
        const errorEl = document.createElement('p');
        errorEl.className = 'form-error';
        errorEl.textContent = message;
        input.parentNode.appendChild(errorEl);
        
        // Remove error on focus
        input.addEventListener('focus', function handler() {
            input.classList.remove('error');
            const err = input.parentNode.querySelector('.form-error');
            if (err) err.remove();
            input.removeEventListener('focus', handler);
        });
    }
    
    function clearErrors() {
        document.querySelectorAll('.form-input.error').forEach(el => el.classList.remove('error'));
        document.querySelectorAll('.form-error').forEach(el => el.remove());
    }
});
