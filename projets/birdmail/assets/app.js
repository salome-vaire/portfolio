document.addEventListener("DOMContentLoaded", () => {
  const statusBox = document.getElementById("statusMessage");
  const audio = document.getElementById("themeMusic");
  const profileForm = document.getElementById("profileForm");
  const sendLetterForm = document.getElementById("sendLetterForm");
  const receivedContainer = document.getElementById("receivedLetters");
  const sentContainer = document.getElementById("sentLetters");
  const previewBird = document.getElementById("previewBird");
  const previewCage = document.getElementById("previewCage");
  const birdSelect = document.getElementById("bird_style");
  const cageSelect = document.getElementById("cage_style");
  const musicSelect = document.getElementById("music_style");
  const volumeSlider = document.getElementById("music_volume");
  const volumeLabel = document.getElementById("musicVolumeValue");
  const canvas = document.getElementById("letterCanvas");
  const clearCanvasBtn = document.getElementById("clearCanvasBtn");
  const drawingInput = document.getElementById("drawing_data");
  const speech = document.getElementById("birdSpeech");
  const toggleSettingsBtn = document.getElementById("toggleSettingsBtn");
  const settingsPanel = document.getElementById("settingsPanel");
  const mediaType = document.getElementById("media_type");
  const localMediaGroup = document.getElementById("localMediaGroup");
  const externalUrlGroup = document.getElementById("externalUrlGroup");

  const receivedPrevBtn = document.getElementById("receivedPrevBtn");
  const receivedNextBtn = document.getElementById("receivedNextBtn");
  const sentPrevBtn = document.getElementById("sentPrevBtn");
  const sentNextBtn = document.getElementById("sentNextBtn");
  const receivedPageInfo = document.getElementById("receivedPageInfo");
  const sentPageInfo = document.getElementById("sentPageInfo");

  let receivedPage = 1;
  let sentPage = 1;
  let lastReceivedCount = 0;
  let firstLoad = true;

  function birdSay(message) {
    if (!speech) return;
    speech.textContent = message;
  }

  function showStatus(message, kind = "info") {
    if (!statusBox) return;
    statusBox.className = `alert alert-${kind}`;
    statusBox.textContent = message;
    statusBox.classList.remove("d-none");
    clearTimeout(showStatus._timer);
    showStatus._timer = setTimeout(() => statusBox.classList.add("d-none"), 2500);
  }

  function isCanvasBlank(canvasEl) {
    const blank = document.createElement("canvas");
    blank.width = canvasEl.width;
    blank.height = canvasEl.height;
    return canvasEl.toDataURL() === blank.toDataURL();
  }

  function setVolume(value) {
    const normalized = Math.max(0, Math.min(1, Number(value) / 100));
    if (audio) audio.volume = normalized;
    if (volumeLabel) volumeLabel.textContent = `${Math.round(normalized * 100)}%`;
    localStorage.setItem("birdmail_volume", String(Math.round(normalized * 100)));
  }

  function getMusicSources(style) {
    return [
      `assets/music/${style}.m4a`,
      `assets/music/${style}.mp3`,
      `assets/music/${style}.ogg`
    ];
  }

  function playThemeMusic(style) {
    if (!audio || !style) return;
    const sources = getMusicSources(style);
    let index = 0;
    const tryNext = () => {
      if (index >= sources.length) return;
      audio.src = sources[index++];
      audio.load();
      const p = audio.play();
      if (p !== undefined) p.catch(() => tryNext());
    };
    tryNext();
  }

  function applyPreview() {
    if (previewBird && birdSelect) previewBird.src = `assets/birds/${birdSelect.value}.png`;
    if (previewCage && cageSelect) previewCage.src = `assets/cages/${cageSelect.value}.png`;
  }

  function updateMediaInputs() {
    if (!mediaType || !localMediaGroup || !externalUrlGroup) return;
    const type = mediaType.value;
    localMediaGroup.classList.toggle("d-none", type !== "file");
    externalUrlGroup.classList.toggle("d-none", type === "file");
  }

  function playNotificationSound() {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextClass) return;
    const ctx = new AudioContextClass();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = "triangle";
    osc.frequency.setValueAtTime(900, ctx.currentTime);
    osc.frequency.exponentialRampToValueAtTime(1300, ctx.currentTime + 0.15);
    gain.gain.setValueAtTime(0.001, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.08, ctx.currentTime + 0.02);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.22);
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.start();
    osc.stop(ctx.currentTime + 0.23);
  }

  async function refreshLetters() {
    try {
      const response = await fetch(`api/get_letters.php?received_page=${receivedPage}&sent_page=${sentPage}`, {
        headers: { "X-Requested-With": "fetch" }
      });
      const data = await response.json();
      if (!data.ok) return;

      if (receivedContainer) receivedContainer.innerHTML = data.received_html;
      if (sentContainer) sentContainer.innerHTML = data.sent_html;

      if (receivedPageInfo) receivedPageInfo.textContent = `Page ${data.received_page} / ${data.received_pages}`;
      if (sentPageInfo) sentPageInfo.textContent = `Page ${data.sent_page} / ${data.sent_pages}`;

      if (receivedPrevBtn) receivedPrevBtn.disabled = data.received_page <= 1;
      if (receivedNextBtn) receivedNextBtn.disabled = data.received_page >= data.received_pages;
      if (sentPrevBtn) sentPrevBtn.disabled = data.sent_page <= 1;
      if (sentNextBtn) sentNextBtn.disabled = data.sent_page >= data.sent_pages;

      if (!firstLoad && data.received_total > lastReceivedCount) {
        playNotificationSound();
        birdSay("Tu as reçu un nouveau message !");
        showStatus("Nouvelle lettre reçue.", "success");
      }

      lastReceivedCount = data.received_total;
      firstLoad = false;
    } catch (error) {
      console.error(error);
    }
  }

  if (receivedPrevBtn) receivedPrevBtn.addEventListener("click", () => { if (receivedPage > 1) { receivedPage--; refreshLetters(); }});
  if (receivedNextBtn) receivedNextBtn.addEventListener("click", () => { receivedPage++; refreshLetters(); });
  if (sentPrevBtn) sentPrevBtn.addEventListener("click", () => { if (sentPage > 1) { sentPage--; refreshLetters(); }});
  if (sentNextBtn) sentNextBtn.addEventListener("click", () => { sentPage++; refreshLetters(); });

  if (toggleSettingsBtn && settingsPanel) {
    toggleSettingsBtn.addEventListener("click", () => {
      settingsPanel.classList.toggle("d-none");
      birdSay(settingsPanel.classList.contains("d-none")
        ? "J’ai replié les paramètres pour te laisser plus de place."
        : "Voici tes paramètres. Tu peux changer mon apparence et la musique.");
    });
  }

  if (birdSelect) birdSelect.addEventListener("change", applyPreview);
  if (cageSelect) cageSelect.addEventListener("change", applyPreview);
  if (mediaType) mediaType.addEventListener("change", updateMediaInputs);
  updateMediaInputs();

  const savedVolume = localStorage.getItem("birdmail_volume") || "35";
  if (volumeSlider) {
    volumeSlider.value = savedVolume;
    volumeSlider.addEventListener("input", () => setVolume(volumeSlider.value));
  }
  setVolume(savedVolume);

  const savedMusic = localStorage.getItem("birdmail_music") || window.BIRDS_INITIAL_MUSIC || "breeze";
  if (musicSelect) {
    musicSelect.value = savedMusic;
    musicSelect.addEventListener("change", () => {
      localStorage.setItem("birdmail_music", musicSelect.value);
      playThemeMusic(musicSelect.value);
      birdSay(`J’ai changé la musique pour ${musicSelect.value}.`);
    });
  }
  playThemeMusic(savedMusic);

  document.addEventListener("click", () => {
    playThemeMusic(musicSelect ? musicSelect.value : savedMusic);
  }, { once: true });

  if (canvas) {
    const ctx = canvas.getContext("2d");
    let drawing = false;

    ctx.lineWidth = 3;
    ctx.lineCap = "round";
    ctx.lineJoin = "round";
    ctx.strokeStyle = "#2f3b4a";

    const getPos = (event) => {
      const rect = canvas.getBoundingClientRect();
      const x = event.touches ? event.touches[0].clientX : event.clientX;
      const y = event.touches ? event.touches[0].clientY : event.clientY;
      return {
        x: ((x - rect.left) / rect.width) * canvas.width,
        y: ((y - rect.top) / rect.height) * canvas.height
      };
    };

    const saveDrawing = () => {
      if (!drawingInput) return;
      drawingInput.value = isCanvasBlank(canvas) ? "" : canvas.toDataURL("image/png");
    };

    const start = (event) => {
      drawing = true;
      const pos = getPos(event);
      ctx.beginPath();
      ctx.moveTo(pos.x, pos.y);
      event.preventDefault();
    };

    const move = (event) => {
      if (!drawing) return;
      const pos = getPos(event);
      ctx.lineTo(pos.x, pos.y);
      ctx.stroke();
      saveDrawing();
      event.preventDefault();
    };

    const end = () => {
      drawing = false;
      ctx.closePath();
      saveDrawing();
    };

    canvas.addEventListener("mousedown", start);
    canvas.addEventListener("mousemove", move);
    window.addEventListener("mouseup", end);
    canvas.addEventListener("touchstart", start, { passive: false });
    canvas.addEventListener("touchmove", move, { passive: false });
    window.addEventListener("touchend", end);

    if (clearCanvasBtn) {
      clearCanvasBtn.addEventListener("click", () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        saveDrawing();
        birdSay("J’ai effacé le dessin.");
      });
    }
  }

  if (profileForm) {
    profileForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      const formData = new FormData(profileForm);

      try {
        const response = await fetch("api/update_profile.php", {
          method: "POST",
          body: formData,
          headers: { "X-Requested-With": "fetch" }
        });
        const data = await response.json();
        if (!data.ok) return showStatus(data.message || "Erreur lors de la sauvegarde.", "danger");

        applyPreview();
        if (musicSelect && data.music_style) {
          musicSelect.value = data.music_style;
          localStorage.setItem("birdmail_music", data.music_style);
          playThemeMusic(data.music_style);
        }
        birdSay("J’ai bien enregistré tes paramètres.");
        showStatus(data.message || "Profil mis à jour.", "success");
      } catch (error) {
        console.error(error);
        showStatus("Erreur réseau.", "danger");
      }
    });
  }

  if (sendLetterForm) {
    sendLetterForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      if (drawingInput && canvas) {
        drawingInput.value = isCanvasBlank(canvas) ? "" : canvas.toDataURL("image/png");
      }

      const formData = new FormData(sendLetterForm);

      try {
        const response = await fetch("api/send_letter.php", {
          method: "POST",
          body: formData,
          headers: { "X-Requested-With": "fetch" }
        });
        const data = await response.json();
        if (!data.ok) return showStatus(data.message || "Erreur lors de l'envoi.", "danger");

        sendLetterForm.reset();
        if (canvas) {
          const ctx = canvas.getContext("2d");
          ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
        if (drawingInput) drawingInput.value = "";
        updateMediaInputs();
        birdSay("Lettre envoyée ! Je l’apporte tout de suite.");
        showStatus(data.message || "Lettre envoyée.", "success");
        await refreshLetters();
      } catch (error) {
        console.error(error);
        showStatus("Erreur réseau.", "danger");
      }
    });
  }

  birdSay("Bonjour. J’attends tes lettres et je surveille l’arrivée de nouveaux messages.");
  refreshLetters();
  setInterval(refreshLetters, 4000);
});
