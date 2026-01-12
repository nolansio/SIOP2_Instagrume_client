// Preview de l'avatar
function previewAvatar(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview = document.getElementById("avatar-preview");
            const img = document.getElementById("avatar-preview-img");
            img.src = e.target.result;
            preview.style.display = "block";
        };
        reader.readAsDataURL(file);
    }
}

// Validation du formulaire
document.querySelector("form").addEventListener("submit", function (e) {
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm_password").value;

    if (password && password !== confirmPassword) {
        e.preventDefault();
        alert("Les mots de passe ne correspondent pas.");
        return false;
    }

    if (password && password.length < 6) {
        e.preventDefault();
        alert("Le mot de passe doit contenir au moins 6 caractères.");
        return false;
    }
});
