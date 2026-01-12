function showBanModal(userId, username) {
    const form = document.getElementById("ban-form");
    const baseUrl = form.getAttribute("data-ban-url");

    form.action = baseUrl.replace("/0", "/" + userId);

    document.getElementById("ban-username").textContent = username;
    document.getElementById("ban-modal").style.display = "flex";
}

function closeBanModal() {
    document.getElementById("ban-modal").style.display = "none";
}

// Fermer la modal en cliquant en dehors
document.getElementById("ban-modal").addEventListener("click", function (e) {
    if (e.target === this) {
        closeBanModal();
    }
});

// Fermer avec Escape
document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
        closeBanModal();
    }
});
