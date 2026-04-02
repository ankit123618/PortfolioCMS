  const renderers = {
  hero: renderHero,
  navigation: renderNavigation,
  content: renderContent,
  projects: renderProjects,
  footer: renderFooter,
  skills: renderSkills,
  contact: renderContact
};

const app = document.getElementById('app');

fetch('/../public/api/get_page.php?slug=home')
  .then(r => r.json())
  .then(page => {
    if (!page.sections) return;

    page.sections.forEach(section => {
      if (section.enabled === false) return;

      if (!validateSection(section)) return;

      const render = renderers[section.type];
      console.log(render);
      
      if (render) render(section);
    });


  })
  .catch(err => console.error('Render error:', err));


function renderHero(section) {
  const div = document.createElement('section');
  div.className = 'hero';

  const tagline = section.data.tagline
    ? section.data.tagline.replace(/\n/g, '<br>')
    : '';

  div.innerHTML = `
    <div class="hero-text">
      <h1>${section.data.title}</h1>
      <p>${tagline}</p>
    </div>
    <div class="hero-photo">
      <img src="../uploads/${section.data.photo}" alt="">
    </div>
  `;

  app.appendChild(div);
}


function renderNavigation(section) {
  const nav = document.createElement('nav');
  nav.className = 'main-nav';

  nav.innerHTML = section.data.links
    .map(l => `<a href="${l.href}">${l.text}</a>`)
    .join('');

  app.appendChild(nav);
}

function renderProjects(section) {
  const container = document.getElementById('app');
  if (!container) return;

  // Section wrapper
  const sectionEl = document.createElement('section');
  sectionEl.id = section.id;
  sectionEl.className = 'projects-section';

  // Title
  if (section.data.title) {
    const h2 = document.createElement('h2');
    h2.textContent = section.data.title;
    sectionEl.appendChild(h2);
  }

  // Cards container
  const grid = document.createElement('div');
  grid.className = 'projects-grid';

  // Projects loop
  section.data.items.forEach(project => {
    const card = document.createElement('div');
    card.className = 'project-card';

    // Image
    if (project.image) {
      const img = document.createElement('img');
      img.src = `../uploads/${project.image}`;
      img.alt = project.title || '';
      card.appendChild(img);
    }

    // Title
    if (project.title) {
      const h3 = document.createElement('h3');
      h3.textContent = project.title;
      card.appendChild(h3);
    }

    // Description
    if (project.description) {
      const p = document.createElement('p');
      p.textContent = project.description;
      card.appendChild(p);
    }

    grid.appendChild(card);
  });

  sectionEl.appendChild(grid);
  container.appendChild(sectionEl);
}

function renderSkills(section) {
  const div = document.createElement('section');
  div.id = section.id;
  div.className = 'skills-section';

  const skillsList = section.data.items
    .map(skill => `<li>${skill}</li>`)
    .join('');

  div.innerHTML = `
    <h2>Skills</h2>
    <ul class="skills-list">
      ${skillsList}
    </ul>
  `;

  app.appendChild(div);
}


// Render Content Section - two fields: title, text
function renderContent(section) {
  
  
  const div = document.createElement('section');
  div.id = section.id;
  div.className = 'content-section';

  div.innerHTML = `
    <h2>${section.data.title}</h2>
    <div class="card">${section.data.text}</div>
  `;

  app.appendChild(div);
  console.log(div);
  
}

function renderContact(section) {
  const container = document.getElementById('app');
  if (!container) return;

  const sectionEl = document.createElement('section');
  sectionEl.id = section.id;
  sectionEl.className = 'contact-section';

  // Title
  if (section.data.title) {
    const h2 = document.createElement('h2');
    h2.textContent = section.data.title;
    sectionEl.appendChild(h2);
  }

  // Contact list
  const list = document.createElement('div');
  list.className = 'contact-list';

  section.data.items.forEach(item => {
    const row = document.createElement('div');
    row.className = 'contact-item';

    const label = document.createElement('span');
    label.className = 'contact-label';
    label.textContent = item.name;

    const value = document.createElement('span');
    value.className = 'contact-value';

    // Smart rendering
    if (item.name.toLowerCase() === 'email') {
      const a = document.createElement('a');
      a.href = `mailto:${item.value}`;
      a.textContent = item.value;
      value.appendChild(a);
    } else if (item.value.startsWith('http')) {
      const a = document.createElement('a');
      a.href = item.value;
      a.target = '_blank';
      a.rel = 'noopener';
      a.textContent = item.value;
      value.appendChild(a);
    } else {
      value.textContent = item.value;
    }

    row.appendChild(label);
    row.appendChild(value);
    list.appendChild(row);
  });

  sectionEl.appendChild(list);
  container.appendChild(sectionEl);
}


function renderFooter(section) {
  const footer = document.createElement('footer');
  footer.innerText = section.data.text;
  app.appendChild(footer);
}

function validateSection(section) {
  const contract = SECTION_CONTRACTS[section.type];
  if (!contract) return false;

  return contract.required.every(
    key => section.data && section.data[key] !== undefined
  );
}

