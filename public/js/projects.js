(function () {
    const container = document.getElementById('projects-container');
    const addBtn = document.getElementById('add-project');

    function updateIndexes() {
        const boxes = container.querySelectorAll('.project-box');
        boxes.forEach((box, index) => {
            box.querySelectorAll('input, textarea').forEach(el => {
                el.name = el.name.replace(/projects\[\d+\]/, `projects[${index}]`);
            });

            const fileInput = box.querySelector('input[type="file"]');
            if (fileInput) {
                fileInput.name = `project_image_${index}`;
            }
        });
    }

    container.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-project')) {
            e.target.closest('.project-box').remove();
            updateIndexes();
        }
    });

    addBtn.addEventListener('click', function () {
        const index = container.children.length;

        const div = document.createElement('div');
        div.className = 'box project-box';
        div.innerHTML = `
      <label>Title</label>
      <input name="projects[${index}][title]">

      <label>Description</label>
      <textarea name="projects[${index}][description]"></textarea>

      <label>Image</label>
      <input type="file" name="project_image_${index}" accept="image/*">

      <button type="button" class="remove-project">Remove</button>
    `;

        container.appendChild(div);
    });
})();
