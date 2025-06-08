document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.querySelector('.profile-form');
    const passwordFields = document.querySelectorAll('.password-change input');

    // Password validation
    function validatePasswords() {
        const newPass = document.getElementById('new_password').value;
        const confirmPass = document.getElementById('confirm_password').value;

        if (newPass && !confirmPass) return;
        
        if (newPass !== confirmPass) {
            document.getElementById('confirm_password').setCustomValidity('Passwords do not match');
        } else {
            document.getElementById('confirm_password').setCustomValidity('');
        }
    }

    passwordFields.forEach(field => {
        field.addEventListener('input', validatePasswords);
    });

    // Profile photo preview
    const photoInput = document.getElementById('profile_photo');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const currentPhoto = document.querySelector('.current-photo');
                    if (currentPhoto) {
                        currentPhoto.src = e.target.result;
                    }
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }
});
