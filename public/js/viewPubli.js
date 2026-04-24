const API_BASE = window.API_BASE + "/api";
// Récupérer le token depuis la variable globale injectée par Twig
const TOKEN = window.API_TOKEN || null;

// Vérifier si le token est présent pour les actions qui en ont besoin
if (!TOKEN && document.querySelector('[onclick*="toggleLike"]')) {
    console.warn(
        "Token non disponible - les actions nécessitant une authentification ne fonctionneront pas"
    );
}

// Carousel simple
let currentSlide = 0;

function moveCarousel(direction) {
    const items = document.querySelectorAll(".carousel-item");
    const indicators = document.querySelectorAll(".carousel-indicator");
    const counter = document.getElementById("current-slide");

    if (items.length === 0) return;

    items[currentSlide].classList.remove("active");
    if (indicators.length > 0)
        indicators[currentSlide].classList.remove("active");

    currentSlide = (currentSlide + direction + items.length) % items.length;

    items[currentSlide].classList.add("active");
    if (indicators.length > 0) indicators[currentSlide].classList.add("active");
    if (counter) counter.textContent = currentSlide + 1;
}

function goToSlide(index) {
    const items = document.querySelectorAll(".carousel-item");
    const indicators = document.querySelectorAll(".carousel-indicator");
    const counter = document.getElementById("current-slide");

    if (items.length === 0) return;

    items[currentSlide].classList.remove("active");
    if (indicators.length > 0)
        indicators[currentSlide].classList.remove("active");

    currentSlide = index;

    items[currentSlide].classList.add("active");
    if (indicators.length > 0) indicators[currentSlide].classList.add("active");
    if (counter) counter.textContent = currentSlide + 1;
}

// Navigation au clavier
document.addEventListener("keydown", function (e) {
    if (e.key === "ArrowLeft") moveCarousel(-1);
    if (e.key === "ArrowRight") moveCarousel(1);
});

// === LIKES & DISLIKES ===

async function toggleLike(id, type) {
    const endpoint =
        type === "publication"
            ? `/likes/publication/id/${id}`
            : `/likes/comment/id/${id}`;

    try {
        location.reload(); // Le rechargement ce fini après la requête

        const response = await fetch(API_BASE + endpoint, {
            method: "POST",
            headers: {
                Authorization: "Bearer " + TOKEN,
                "Content-Type": "application/json",
            },
        });

        if (!response.ok && response.status !== 409) {
            const error = await response.json();
            console.error("Erreur API:", error);
            alert("Erreur: " + (error.error || JSON.stringify(error)));
        }
    } catch (error) {
        console.error("Erreur fetch:", error);
        alert("Erreur de connexion: " + error.message);
    }
}

async function toggleDislike(id, type) {
    const endpoint =
        type === "publication"
            ? `/dislikes/publication/id/${id}`
            : `/dislikes/comment/id/${id}`;

    try {
        location.reload(); // Le rechargement ce fini après la requête

        const response = await fetch(API_BASE + endpoint, {
            method: "POST",
            headers: {
                Authorization: "Bearer " + TOKEN,
                "Content-Type": "application/json",
            },
        });

        if (!response.ok && response.status !== 409) {
            const error = await response.json();
            console.error("Erreur API:", error);
            alert("Erreur: " + (error.error || JSON.stringify(error)));
        }
    } catch (error) {
        console.error("Erreur fetch:", error);
        alert("Erreur de connexion: " + error.message);
    }
}

// === COMMENTAIRES ===

async function postComment(event, publicationId, originalCommentId) {
    event.preventDefault();

    const textarea = originalCommentId
        ? document.getElementById(`reply-input-${originalCommentId}`)
        : document.getElementById("comment-input");

    const content = textarea.value.trim();

    if (!content) {
        alert("Le commentaire ne peut pas être vide");
        return;
    }

    const payload = {
        id: publicationId,
        content: content,
    };

    if (originalCommentId) {
        payload.original_comment = originalCommentId;
    }

    try {
        const response = await fetch(API_BASE + "/comments", {
            method: "POST",
            headers: {
                Authorization: "Bearer " + TOKEN,
                "Content-Type": "application/json",
            },
            body: JSON.stringify(payload),
        });

        // Lire la réponse brute
        const responseText = await response.text();

        if (response.ok) {
            // Recharger la page pour afficher le nouveau commentaire
            location.reload();
        } else {
            // Essayer de parser comme JSON, sinon afficher le texte brut
            try {
                const error = JSON.parse(responseText);
                console.error("Erreur API:", error);
                alert("Erreur: " + (error.error || JSON.stringify(error)));
            } catch (e) {
                console.error("Réponse non-JSON:", responseText);
                alert("Erreur serveur: " + responseText.substring(0, 200));
            }
        }
    } catch (error) {
        console.error("Erreur fetch:", error);
        alert("Erreur de connexion: " + error.message);
    }
}

// === FORMULAIRE DE RÉPONSE ===

function showReplyForm(commentId, username) {
    // Masquer tous les autres formulaires de réponse
    document.querySelectorAll('[id^="reply-form-"]').forEach((form) => {
        form.style.display = "none";
    });

    // Afficher le formulaire de ce commentaire
    const form = document.getElementById(`reply-form-${commentId}`);
    form.style.display = "block";

    // Focus sur le textarea
    const textarea = document.getElementById(`reply-input-${commentId}`);
    textarea.focus();
}

function hideReplyForm(commentId) {
    const form = document.getElementById(`reply-form-${commentId}`);
    form.style.display = "none";

    // Vider le textarea
    const textarea = document.getElementById(`reply-input-${commentId}`);
    textarea.value = "";
}
