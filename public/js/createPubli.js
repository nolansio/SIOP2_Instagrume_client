// Gestion des fichiers
let selectedFiles = [];

const dropZone = document.getElementById("drop-zone");
const fileInput = document.getElementById("images");
const previewContainer = document.getElementById("preview-container");
const previewGrid = document.getElementById("preview-grid");
const imageCount = document.getElementById("image-count");
const addMoreBtn = document.getElementById("add-more-btn");
const submitBtn = document.getElementById("submit-btn");

// Clic sur la zone entière pour ouvrir le sélecteur
dropZone.addEventListener("click", () => {
    fileInput.click();
});

// Bouton "Ajouter des images"
addMoreBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    fileInput.click();
});

// Hover effect
dropZone.addEventListener("mouseenter", () => {
    dropZone.style.borderColor = "var(--color-primary)";
    dropZone.style.background = "var(--amber-2)";
});

dropZone.addEventListener("mouseleave", () => {
    dropZone.style.borderColor = "var(--color-border)";
    dropZone.style.background = "var(--color-background-alt)";
});

// Drag & Drop
dropZone.addEventListener("dragover", (e) => {
    e.preventDefault();
    dropZone.style.borderColor = "var(--color-primary)";
    dropZone.style.background = "var(--amber-2)";
});

dropZone.addEventListener("dragleave", () => {
    dropZone.style.borderColor = "var(--color-border)";
    dropZone.style.background = "var(--color-background-alt)";
});

dropZone.addEventListener("drop", (e) => {
    e.preventDefault();
    dropZone.style.borderColor = "var(--color-border)";
    dropZone.style.background = "var(--color-background-alt)";

    const files = Array.from(e.dataTransfer.files).filter((file) =>
        file.type.startsWith("image/")
    );
    addFiles(files); // async, pas besoin d'await ici
});

// Sélection de fichiers
fileInput.addEventListener("change", (e) => {
    const files = Array.from(e.target.files);
    addFiles(files);
});

// Compresser une image avant l'upload
function compressImage(file, maxWidth = 1920, maxHeight = 1080, quality = 0.8) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);

        reader.onload = (e) => {
            const img = new Image();
            img.src = e.target.result;

            img.onload = () => {
                const canvas = document.createElement("canvas");
                let width = img.width;
                let height = img.height;

                // Calculer les nouvelles dimensions
                if (width > maxWidth || height > maxHeight) {
                    const ratio = Math.min(
                        maxWidth / width,
                        maxHeight / height
                    );
                    width = width * ratio;
                    height = height * ratio;
                }

                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, width, height);

                // Convertir en Blob
                canvas.toBlob(
                    (blob) => {
                        // Créer un nouveau File à partir du Blob
                        const compressedFile = new File([blob], file.name, {
                            type: "image/jpeg",
                            lastModified: Date.now(),
                        });
                        resolve(compressedFile);
                    },
                    "image/jpeg",
                    quality
                );
            };
        };
    });
}

// Ajouter des fichiers (sans écraser) avec compression et validation
async function addFiles(newFiles) {
    const maxFileSize = 10 * 1024 * 1024; // 10 MB
    const maxTotalSize = 30 * 1024 * 1024; // 30 MB total
    let tooLargeFiles = [];
    let currentTotalSize = selectedFiles.reduce((sum, f) => sum + f.size, 0);

    // Afficher un indicateur de chargement si on a des gros fichiers
    const hasLargeFiles = newFiles.some((f) => f.size > 1024 * 1024);
    let loadingDiv = null;

    if (hasLargeFiles) {
        loadingDiv = document.createElement("div");
        loadingDiv.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: var(--spacing-lg);
            border-radius: var(--radius-md);
            z-index: 9999;
            text-align: center;
        `;
        loadingDiv.innerHTML = `
            <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">⏳</div>
            <div style="font-weight: 600;">Compression des images...</div>
            <div style="font-size: var(--font-size-sm); color: var(--amber-11); margin-top: var(--spacing-xs);">Veuillez patienter</div>
        `;
        document.body.appendChild(loadingDiv);
    }

    for (const file of newFiles) {
        // Vérifier que le fichier n'est pas déjà dans la liste
        const isDuplicate = selectedFiles.some(
            (f) => f.name === file.name && f.size === file.size
        );

        if (isDuplicate) {
            continue; // Ignorer les doublons
        }

        let fileToAdd = file;

        // Compresser si c'est une image et > 1MB
        if (file.type.startsWith("image/") && file.size > 1024 * 1024) {
            try {
                const compressed = await compressImage(file);
                const originalSize = (file.size / 1024 / 1024).toFixed(2);
                const compressedSize = (compressed.size / 1024 / 1024).toFixed(
                    2
                );
                const saved = ((1 - compressed.size / file.size) * 100).toFixed(
                    0
                );
                fileToAdd = compressed;
            } catch (error) {
                console.error("Erreur compression:", error);
                // En cas d'erreur, utiliser le fichier original
            }
        }

        // Vérifier la taille après compression
        if (fileToAdd.size > maxFileSize) {
            tooLargeFiles.push(
                file.name +
                    " (" +
                    (fileToAdd.size / 1024 / 1024).toFixed(2) +
                    " MB)"
            );
            continue; // Ignorer ce fichier
        }

        // Vérifier la taille totale
        if (currentTotalSize + fileToAdd.size > maxTotalSize) {
            alert(
                "⚠️ Taille totale trop grande !\n\nMaximum : " +
                    maxTotalSize / 1024 / 1024 +
                    " MB pour toutes les images.\nActuel : " +
                    (currentTotalSize / 1024 / 1024).toFixed(2) +
                    " MB"
            );
            break; // Arrêter l'ajout
        }

        selectedFiles.push(fileToAdd);
        currentTotalSize += fileToAdd.size;
    }

    // Retirer l'indicateur de chargement
    if (loadingDiv) {
        document.body.removeChild(loadingDiv);
    }

    // Afficher les erreurs
    if (tooLargeFiles.length > 0) {
        alert(
            "⚠️ Fichier(s) trop volumineux (max 10 MB) :\n\n" +
                tooLargeFiles.join("\n") +
                "\n\nLes autres fichiers ont été ajoutés."
        );
    }

    updatePreview();
    updateFileInput();
}

// Supprimer un fichier
function removeFile(index) {
    selectedFiles.splice(index, 1);
    updatePreview();
    updateFileInput();
}

// Mettre à jour l'input file avec les fichiers sélectionnés
function updateFileInput() {
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach((file) => {
        dataTransfer.items.add(file);
    });
    fileInput.files = dataTransfer.files;
}

// Mettre à jour la prévisualisation
function updatePreview() {
    const count = selectedFiles.length;

    // Afficher/masquer le container
    if (count === 0) {
        previewContainer.style.display = "none";
        dropZone.style.display = "block";
        submitBtn.disabled = true;
    } else {
        previewContainer.style.display = "block";
        dropZone.style.display = "none";
        submitBtn.disabled = false;
    }

    // Mettre à jour le compteur
    imageCount.textContent = count;

    // Vider COMPLÈTEMENT la grille
    previewGrid.innerHTML = "";

    // Recréer TOUTES les previews avec les bons indices
    selectedFiles.forEach((file, index) => {
        createPreviewItem(file, index);
    });
}

// Créer un élément de preview
function createPreviewItem(file, index) {
    const reader = new FileReader();

    reader.onload = (e) => {
        const previewItem = document.createElement("div");
        previewItem.style.cssText = `
            position: relative;
            padding-bottom: 100%;
            border-radius: var(--radius-md);
            overflow: hidden;
            background: var(--color-background-alt);
            border: 2px solid var(--color-border);
        `;

        previewItem.innerHTML = `
            <img 
                src="${e.target.result}" 
                alt="${file.name}"
                style="
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                "
            >
            <button 
                type="button"
                class="remove-image-btn"
                data-index="${index}"
                style="
                    position: absolute;
                    top: var(--spacing-xs);
                    right: var(--spacing-xs);
                    width: 28px;
                    height: 28px;
                    border-radius: var(--radius-full);
                    background: var(--color-error);
                    color: white;
                    border: 2px solid white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    font-size: 1rem;
                    font-weight: 700;
                    transition: all var(--transition-fast);
                    z-index: 10;
                "
                onmouseover="this.style.transform='scale(1.1)'"
                onmouseout="this.style.transform='scale(1)'"
                title="Supprimer cette image"
            >
                <i class="bi bi-x"></i>
            </button>
            <div style="
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                padding: var(--spacing-xs);
                background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
                color: white;
                font-size: var(--font-size-xs);
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            ">
                ${file.name}
            </div>
        `;

        // Attacher l'événement de suppression immédiatement
        const removeBtn = previewItem.querySelector(".remove-image-btn");
        removeBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            const idx = parseInt(removeBtn.dataset.index);
            removeFile(idx);
        });

        previewGrid.appendChild(previewItem);
    };

    reader.readAsDataURL(file);
}

// Validation avant soumission
document.getElementById("publication-form").addEventListener("submit", (e) => {
    if (selectedFiles.length === 0) {
        e.preventDefault();
        alert("Veuillez sélectionner au moins une image.");
        return false;
    }
});
