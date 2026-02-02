(function () {
    const container = document.getElementById('skills-container');
    const addBtn = document.getElementById('add-skill');

    container.addEventListener('click', e => {
        if (e.target.classList.contains('remove-skill')) {
            e.target.parentElement.remove();
            reindex();
        }
    });

    addBtn.addEventListener('click', () => {
        const i = container.children.length;
        const div = document.createElement('div');
        div.className = 'box skill-row';
        div.innerHTML = `
      <input name="skills[${i}]">
      <button type="button" class="remove-skill">Remove</button>
    `;
        container.appendChild(div);
    });

    function reindex() {
        [...container.children].forEach((row, i) => {
            row.querySelector('input').name = `skills[${i}]`;
        });
    }
})();