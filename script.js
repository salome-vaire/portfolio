function escapeHtml(text) {
  return String(text)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/\"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function normalizeTechnologies(technologies) {
  if (Array.isArray(technologies)) {
    return technologies;
  }

  if (typeof technologies === "string") {
    return technologies.split(",").map(item => item.trim()).filter(Boolean);
  }

  return [];
}

function createProjectCard(project) {
  const technologies = normalizeTechnologies(project.technologies);
  const type = project.type ? `<span class="project-badge">${escapeHtml(project.type)}</span>` : "";
  const periode = project.periode ? `<p class="mb-2"><strong>Période :</strong> ${escapeHtml(project.periode)}</p>` : "";
  const details = project.details ? `<p class="mb-2">${escapeHtml(project.details)}</p>` : "";
  const technologiesHtml = technologies.length > 0
    ? `<p class="mb-2"><strong>Technologies :</strong> ${technologies.map(tech => escapeHtml(tech)).join(", ")}</p>`
    : "";

  const linkHtml = project.lien && project.lien !== "#"
    ? `<a href="${escapeHtml(project.lien)}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary mt-2">Voir le projet</a>`
    : "";

  return `
    <article class="project-card">
      ${type}
      <h4>${escapeHtml(project.titre || "Projet")}</h4>
      <p class="mb-2">${escapeHtml(project.description || "")}</p>
      ${technologiesHtml}
      ${periode}
      ${details}
      ${linkHtml}
    </article>
  `;
}

function loadProjects(jsonPath, containerId) {
  const container = document.getElementById(containerId);

  if (!container) {
    return;
  }

  fetch(jsonPath)
    .then(response => {
      if (!response.ok) {
        throw new Error(`Impossible de charger ${jsonPath}`);
      }
      return response.json();
    })
    .then(data => {
      if (!Array.isArray(data)) {
        throw new Error(`Le fichier ${jsonPath} ne contient pas une liste valide.`);
      }

      if (data.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0">Aucun projet à afficher pour le moment.</p>';
        return;
      }

      container.innerHTML = data.map(project => createProjectCard(project)).join("");
    })
    .catch(error => {
      console.error("Erreur chargement projets :", error);
      container.innerHTML = '<p class="text-muted mb-0">Les projets ne peuvent pas être affichés pour le moment.</p>';
    });
}

document.addEventListener("DOMContentLoaded", function () {
  loadProjects("data/projets-ecole.json", "school-projects");
  loadProjects("data/projets-perso.json", "personal-projects");
  loadProjects("data/projets-entreprise.json", "company-projects");

  const contactForm = document.getElementById("contact-form");
  const contactMessage = document.getElementById("contact-message");

  if (contactForm && contactMessage) {
    contactForm.addEventListener("submit", function (event) {
      event.preventDefault();
      contactMessage.classList.remove("d-none", "text-danger");
      contactMessage.classList.add("text-success");
      contactMessage.textContent = "Ce formulaire est une démonstration du portfolio. Les messages ne sont pas envoyés automatiquement.";
    });
  }
});

document.addEventListener("DOMContentLoaded", () => {
  // Quand la page est chargée, on charge les projets depuis les fichiers JSON
  loadProjects("data/projets-ecole.json", "school-projects");
  loadProjects("data/projets-perso.json", "personal-projects");
  loadProjects("data/projets-entreprise.json", "company-projects");

  // On initialise le formulaire de contact
  setupContactForm();
});


// Fonction qui charge les projets depuis un fichier JSON
function loadProjects(jsonPath, containerId) {
  fetch(jsonPath)
    .then(response => {
      // Vérifie que le fichier est bien trouvé
      if (!response.ok) {
        throw new Error("Erreur lors du chargement du fichier : " + jsonPath);
      }
      return response.json();
    })
    .then(data => {
      const container = document.getElementById(containerId);
      if (!container) return;

      container.innerHTML = "";

      // Pour chaque projet dans le JSON
      data.forEach(project => {
        const card = document.createElement("div");
        card.classList.add("project-card", "mb-3");

        // Permet d'accepter soit un tableau, soit du texte simple
        let technologiesText = "";
        if (Array.isArray(project.technologies)) {
          technologiesText = project.technologies.join(", ");
        } else if (typeof project.technologies === "string") {
          technologiesText = project.technologies;
        }

        // Construction du HTML du projet
        card.innerHTML = `
          <h4>${project.titre}</h4>
          <p>${project.description}</p>
          ${technologiesText ? `<p><strong>Technologies :</strong> ${technologiesText}</p>` : ""}
          ${project.details ? `<p>${project.details}</p>` : ""}

          ${
            // Si un lien est présent, on ajoute un bouton
            project.lien
              ? `<a href="${project.lien}" target="_blank" class="btn btn-sm btn-primary mt-2">Voir le projet</a>`
              : ""
          }
        `;

        container.appendChild(card);
      });
    })
    .catch(error => {
      // En cas d'erreur, on affiche un message
      const container = document.getElementById(containerId);
      if (container) {
        container.innerHTML = `<p class="text-danger mb-0">Impossible de charger les projets.</p>`;
      }
      console.error(error);
    });
}


// Fonction qui gère le formulaire de contact
function setupContactForm() {
  const form = document.getElementById("contact-form");
  const feedback = document.getElementById("contact-feedback");

  if (!form) return;

  form.addEventListener("submit", function (event) {
    // Empêche le rechargement de la page
    event.preventDefault();

    const nom = document.getElementById("nom").value.trim();
    const email = document.getElementById("email").value.trim();
    const message = document.getElementById("message").value.trim();

    // Vérification simple des champs
    if (!nom || !email || !message) {
      if (feedback) {
        feedback.textContent = "Merci de remplir tous les champs.";
      }
      return;
    }

    const destinataire = "salome.vaire0@gmail.com";

    // encodeURIComponent permet d'éviter les erreurs avec les caractères spéciaux
    const sujet = encodeURIComponent("Contact depuis le portfolio de Salomé Vaire");

    const corps = encodeURIComponent(
      "Nom : " + nom + "\n" +
      "Email : " + email + "\n\n" +
      "Message :\n" + message
    );

    // Création du lien mailto avec les données du formulaire
    const mailtoLink = `mailto:${destinataire}?subject=${sujet}&body=${corps}`;

    if (feedback) {
      feedback.textContent = "Ouverture de votre messagerie...";
    }

    // Ouvre le client mail de l'utilisateur
    window.location.href = mailtoLink;
  });
}