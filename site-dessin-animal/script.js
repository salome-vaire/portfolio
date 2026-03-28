const canvas = document.getElementById('drawingCanvas');
const ctx = canvas.getContext('2d');
const analyzeBtn = document.getElementById('analyzeBtn');
const clearBtn = document.getElementById('clearBtn');
const resultText = document.getElementById('resultText');
const resultList = document.getElementById('resultList');

let isDrawing = false;
let points = [];

const animals = [
  { name: 'chat', environment: 'land', legs: 4, size: 'small', shape: 'compact', wings: false, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['petit mammifère domestique'] },
  { name: 'chien', environment: 'land', legs: 4, size: 'medium', shape: 'compact', wings: false, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['animal domestique courant'] },
  { name: 'lapin', environment: 'land', legs: 4, size: 'small', shape: 'compact', wings: false, fins: false, longEars: true, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['grandes oreilles'] },
  { name: 'poisson', environment: 'water', legs: 0, size: 'small', shape: 'elongated', wings: false, fins: true, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['animal aquatique'] },
  { name: 'serpent', environment: 'land', legs: 0, size: 'medium', shape: 'elongated', wings: false, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['corps très allongé'] },
  { name: 'oiseau', environment: 'air', legs: 2, size: 'small', shape: 'compact', wings: true, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['animal volant'] },
  { name: 'canard', environment: 'mixed', legs: 2, size: 'small', shape: 'compact', wings: true, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['eau et terre'] },
  { name: 'poule', environment: 'land', legs: 2, size: 'small', shape: 'compact', wings: true, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['animal de ferme'] },
  { name: 'hibou', environment: 'air', legs: 2, size: 'medium', shape: 'round', wings: true, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['oiseau nocturne'] },
  { name: 'papillon', environment: 'air', legs: 6, size: 'small', shape: 'round', wings: true, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['insecte ailé'] },
  { name: 'abeille', environment: 'air', legs: 6, size: 'small', shape: 'compact', wings: true, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['insecte très courant'] },
  { name: 'souris', environment: 'land', legs: 4, size: 'small', shape: 'compact', wings: false, fins: false, longEars: true, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['petit rongeur'] },
  { name: 'tortue', environment: 'mixed', legs: 4, size: 'small', shape: 'round', wings: false, fins: false, longEars: false, horns: false, longNeck: false, shell: true, trunk: false, spots: false, tags: ['carapace'] },
  { name: 'grenouille', environment: 'mixed', legs: 4, size: 'small', shape: 'compact', wings: false, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['amphibien'] },
  { name: 'cheval', environment: 'land', legs: 4, size: 'large', shape: 'elongated', wings: false, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['grand mammifère'] },
  { name: 'vache', environment: 'land', legs: 4, size: 'large', shape: 'elongated', wings: false, fins: false, longEars: false, horns: true, longNeck: false, shell: false, trunk: false, spots: true, tags: ['animal de ferme'] },
  { name: 'mouton', environment: 'land', legs: 4, size: 'medium', shape: 'round', wings: false, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['animal de ferme'] },
  { name: 'cochon', environment: 'land', legs: 4, size: 'medium', shape: 'round', wings: false, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['animal rose souvent représenté'] },
  { name: 'renard', environment: 'land', legs: 4, size: 'medium', shape: 'elongated', wings: false, fins: false, longEars: true, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['animal sauvage courant'] },
  { name: 'ours', environment: 'land', legs: 4, size: 'large', shape: 'compact', wings: false, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['corps massif'] },
  { name: 'lion', environment: 'land', legs: 4, size: 'large', shape: 'compact', wings: false, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['grand félin'] },
  { name: 'singe', environment: 'land', legs: 4, size: 'medium', shape: 'compact', wings: false, fins: false, longEars: false, horns: false, longNeck: false, shell: false, trunk: false, spots: false, tags: ['mammifère agile'] },
  { name: 'girafe', environment: 'land', legs: 4, size: 'large', shape: 'tall', wings: false, fins: false, longEars: false, horns: true, longNeck: true, shell: false, trunk: false, spots: true, tags: ['très long cou'] },
  { name: 'éléphant', environment: 'land', legs: 4, size: 'large', shape: 'compact', wings: false, fins: false, longEars: true, horns: false, longNeck: false, shell: false, trunk: true, spots: false, tags: ['trompe'] },
  { name: 'cerf', environment: 'land', legs: 4, size: 'large', shape: 'tall', wings: false, fins: false, longEars: false, horns: true, longNeck: false, shell: false, trunk: false, spots: false, tags: ['bois'] }
];

ctx.lineWidth = 4;
ctx.lineCap = 'round';
ctx.lineJoin = 'round';
ctx.strokeStyle = '#1f2a44';

function getCanvasPosition(event) {
  const rect = canvas.getBoundingClientRect();
  const scaleX = canvas.width / rect.width;
  const scaleY = canvas.height / rect.height;

  return {
    x: (event.clientX - rect.left) * scaleX,
    y: (event.clientY - rect.top) * scaleY
  };
}

function startDrawing(event) {
  isDrawing = true;
  const position = getCanvasPosition(event);
  ctx.beginPath();
  ctx.moveTo(position.x, position.y);
  points.push(position);
}

function draw(event) {
  if (!isDrawing) {
    return;
  }

  const position = getCanvasPosition(event);
  ctx.lineTo(position.x, position.y);
  ctx.stroke();
  points.push(position);
}

function stopDrawing() {
  isDrawing = false;
  ctx.beginPath();
}

function clearCanvas() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  points = [];
  resultText.textContent = 'Dessinez un animal pour commencer.';
  resultList.innerHTML = '';
}

function distance(pointA, pointB) {
  const dx = pointA.x - pointB.x;
  const dy = pointA.y - pointB.y;
  return Math.sqrt(dx * dx + dy * dy);
}

function getDrawingProfile() {
  let minX = points[0].x;
  let maxX = points[0].x;
  let minY = points[0].y;
  let maxY = points[0].y;

  for (const point of points) {
    if (point.x < minX) minX = point.x;
    if (point.x > maxX) maxX = point.x;
    if (point.y < minY) minY = point.y;
    if (point.y > maxY) maxY = point.y;
  }

  const width = maxX - minX;
  const height = maxY - minY;
  const ratio = width / Math.max(height, 1);
  const diagonal = Math.sqrt(width * width + height * height);
  const closureDistance = distance(points[0], points[points.length - 1]);
  const closedShape = closureDistance < diagonal * 0.25;

  let shape = 'compact';
  if (ratio > 1.8) {
    shape = 'elongated';
  } else if (height > width * 1.45) {
    shape = 'tall';
  } else if (closedShape && ratio > 0.8 && ratio < 1.2) {
    shape = 'round';
  }

  let size = 'medium';
  if (diagonal < 180) {
    size = 'small';
  } else if (diagonal > 320) {
    size = 'large';
  }

  return { width, height, ratio, diagonal, closedShape, shape, size };
}

function getUserHints() {
  return {
    environment: document.getElementById('environment').value,
    legs: document.getElementById('legs').value,
    size: document.getElementById('size').value,
    shapeHint: document.getElementById('shapeHint').value,
    hasWings: document.getElementById('hasWings').checked,
    hasFins: document.getElementById('hasFins').checked,
    hasLongEars: document.getElementById('hasLongEars').checked,
    hasHorns: document.getElementById('hasHorns').checked,
    hasLongNeck: document.getElementById('hasLongNeck').checked,
    hasShell: document.getElementById('hasShell').checked,
    hasTrunk: document.getElementById('hasTrunk').checked,
    hasSpots: document.getElementById('hasSpots').checked
  };
}

function scoreAnimal(animal, profile, hints) {
  let score = 0;

  if (animal.shape === profile.shape) {
    score += 18;
  } else if (animal.shape === 'compact' && profile.shape === 'round') {
    score += 10;
  }

  if (animal.size === profile.size) {
    score += 10;
  }

  if (hints.environment !== 'unknown' && animal.environment === hints.environment) {
    score += 22;
  }

  if (hints.environment === 'mixed' && (animal.environment === 'mixed' || animal.environment === 'water')) {
    score += 12;
  }

  if (hints.legs !== 'unknown' && animal.legs === Number(hints.legs)) {
    score += 16;
  }

  if (hints.size !== 'unknown' && animal.size === hints.size) {
    score += 14;
  }

  if (hints.shapeHint !== 'unknown' && animal.shape === hints.shapeHint) {
    score += 18;
  }

  const booleanChecks = [
    ['wings', 'hasWings'],
    ['fins', 'hasFins'],
    ['longEars', 'hasLongEars'],
    ['horns', 'hasHorns'],
    ['longNeck', 'hasLongNeck'],
    ['shell', 'hasShell'],
    ['trunk', 'hasTrunk'],
    ['spots', 'hasSpots']
  ];

  for (const [animalKey, hintKey] of booleanChecks) {
    if (hints[hintKey] && animal[animalKey]) {
      score += 26;
    } else if (hints[hintKey] && !animal[animalKey]) {
      score -= 8;
    }
  }

  if (profile.shape === 'elongated' && animal.name === 'serpent') {
    score += 10;
  }

  if (profile.shape === 'round' && ['cochon', 'mouton', 'hibou', 'tortue'].includes(animal.name)) {
    score += 6;
  }

  if (profile.shape === 'tall' && ['girafe', 'cerf'].includes(animal.name)) {
    score += 12;
  }

  return score;
}

function analyzeDrawing() {
  if (points.length < 12) {
    resultText.textContent = 'Le dessin est trop petit ou incomplet pour être analysé.';
    resultList.innerHTML = '';
    return;
  }

  const profile = getDrawingProfile();
  const hints = getUserHints();

  const ranked = animals
    .map((animal) => ({
      ...animal,
      score: scoreAnimal(animal, profile, hints)
    }))
    .sort((a, b) => b.score - a.score)
    .slice(0, 5);

  const best = ranked[0];

  resultText.innerHTML = `<strong>Résultat principal :</strong> le dessin ressemble surtout à un <strong>${best.name}</strong>.`;

  resultList.innerHTML = ranked.map((animal, index) => `
    <div class="result-rank">
      <div class="d-flex justify-content-between align-items-center gap-3">
        <strong>${index + 1}. ${animal.name}</strong>
        <span class="result-score">score : ${animal.score}</span>
      </div>
      <div class="text-muted mt-1">${animal.tags.join(', ')}</div>
    </div>
  `).join('');
}

canvas.addEventListener('mousedown', startDrawing);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDrawing);
canvas.addEventListener('mouseleave', stopDrawing);

canvas.addEventListener('touchstart', (event) => {
  event.preventDefault();
  startDrawing(event.touches[0]);
}, { passive: false });

canvas.addEventListener('touchmove', (event) => {
  event.preventDefault();
  draw(event.touches[0]);
}, { passive: false });

canvas.addEventListener('touchend', stopDrawing);

analyzeBtn.addEventListener('click', analyzeDrawing);
clearBtn.addEventListener('click', clearCanvas);
