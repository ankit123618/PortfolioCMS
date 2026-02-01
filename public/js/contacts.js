(function () {
    const container = document.getElementById('contact-container');
    const addBtn = document.getElementById('add-contact');

    container.addEventListener('click', e => {
        if (e.target.classList.contains('remove-contact')) {
            e.target.parentElement.remove();
            reindex();
        }
    });

    addBtn.addEventListener('click', () => {
        const i = container.children.length;
        const div = document.createElement('div');
        div.className = 'box contact-row';
        div.innerHTML = `
      <input name="contact[${i}][name]" placeholder="Label">
      <input name="contact[${i}][value]" placeholder="Value">
      <button type="button" class="remove-contact">Remove</button>
    `;
        container.appendChild(div);
    });

    function reindex() {
        [...container.children].forEach((row, i) => {
            row.querySelectorAll('input')[0].name = `contact[${i}][name]`;
            row.querySelectorAll('input')[1].name = `contact[${i}][value]`;
        });
    }
})();