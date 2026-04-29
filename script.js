// Sécurise le texte (évite injections HTML)
function escapeHtml(text) {
  return String(text)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

// Normalise les technologies
function normalizeTechnologies(technologies) {
  if (Array.isArray(technologies)) return technologies;

  if (typeof technologies === "string") {
    return technologies
      .split(",")
      .map(t => t.trim())
      .filter(Boolean);
  }

  return [];
}

// Carte projet
function createProjectCard(project) {
  const technologies = normalizeTechnologies(project.technologies);

  const entrepriseHtml = project.entreprise
    ? `<p>
        <a href="${escapeHtml(project.entreprise.lien)}" target="_blank" rel="noopener noreferrer">
          ${escapeHtml(project.entreprise.nom)}
        </a>
      </p>`
    : "";

  const technologiesHtml = technologies.length
    ? `<p><strong>Technologies :</strong> ${technologies
        .map(t => escapeHtml(t))
        .join(", ")}</p>`
    : "";

  const periodeHtml = project.periode
    ? `<p><strong>Période :</strong> ${escapeHtml(project.periode)}</p>`
    : "";

  const detailsHtml = project.details
    ? `<p>${escapeHtml(project.details)}</p>`
    : "";

  const projectLink = project.lienProjet || project.lien;
  const boutonHtml = projectLink
    ? `<a href="${escapeHtml(projectLink)}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary mt-2">
        Voir le projet
      </a>`
    : "";

  return `
    <article class="project-card">
      <h4>${escapeHtml(project.titre || "Projet")}</h4>
      ${entrepriseHtml}
      <p>${escapeHtml(project.description || "")}</p>
      ${technologiesHtml}
      ${periodeHtml}
      ${detailsHtml}
      ${boutonHtml}
    </article>
  `;
}

// Charger les projets
function loadProjects(jsonPath, containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;

  fetch(jsonPath)
    .then(res => {
      if (!res.ok) throw new Error("Erreur chargement : " + jsonPath);
      return res.json();
    })
    .then(data => {
      if (!Array.isArray(data)) throw new Error("JSON invalide");

      if (data.length === 0) {
        container.innerHTML = "<p>Aucun projet.</p>";
        return;
      }

      container.innerHTML = data.map(createProjectCard).join("");
    })
    .catch(err => {
      console.error(err);
      container.innerHTML = "<p>Erreur de chargement.</p>";
    });
}

// Formulaire contact
function setupContactForm() {
  const form = document.getElementById("contact-form");
  const feedback = document.getElementById("contact-feedback");

  if (!form) return;

  form.addEventListener("submit", function (event) {
    event.preventDefault();

    const nom = document.getElementById("nom").value.trim();
    const email = document.getElementById("email").value.trim();
    const message = document.getElementById("message").value.trim();

    if (!nom || !email || !message) {
      if (feedback) feedback.textContent = "Merci de remplir tous les champs.";
      return;
    }

    const destinataire = "salome.vaire0@gmail.com";
    const sujet = encodeURIComponent("Contact depuis le portfolio");
    const corps = encodeURIComponent(
      `Nom : ${nom}\nEmail : ${email}\n\nMessage :\n${message}`
    );

    window.location.href = `mailto:${destinataire}?subject=${sujet}&body=${corps}`;

    if (feedback) feedback.textContent = "Ouverture de votre messagerie...";
  });
}

// MENU HAMBURGER BOOTSTRAP (FERMETURE AUTO)
function setupNavbar() {
  const menu = document.getElementById("menuNavbar");
  const navLinks = document.querySelectorAll(".nav-link");

  if (!menu) return;

  const bsCollapse = new bootstrap.Collapse(menu, {
    toggle: false
  });

  navLinks.forEach(link => {
    link.addEventListener("click", () => {
      bsCollapse.hide();
    });
  });
}

// INIT GLOBAL
document.addEventListener("DOMContentLoaded", () => {
  loadProjects("data/projets-ecole.json", "school-projects");
  loadProjects("data/projets-perso.json", "personal-projects");
  loadProjects("data/projets-entreprise.json", "company-projects");

  setupContactForm();
  setupNavbar();
});
