function toggleCharacterDetails() {
    document.querySelectorAll('.character-bar').forEach(function (bar) {
        bar.addEventListener('click', function () {
            var characterId = this.getAttribute('data-character-id');
            var content = document.getElementById('character-' + characterId);
            content.style.display = content.style.display === 'none' || content.style.display === '' ? 'block' : 'none';
        });
    });
}

function addNewCharacterForm() {
    document.querySelectorAll('.character-content').forEach(function (c) {
        c.style.display = 'none';
    });

    const existingForm = document.getElementById('new-character-form');
    if (existingForm) {
        existingForm.remove();
        return;
    }

    const newCharacterForm = `
    <form id="new-character-form" class="tcal" style="width: 100%; margin-top: 3px;" enctype="multipart/form-data" method="POST">
        <tr class="character-content" style="display: table-row;">
            <td>
                <div style="overflow: hidden;">
                    <input type="text" name="name" placeholder="Charaktername" required />
                    <input type="file" class="button" name="avatar" />
                    <textarea name="description" placeholder="Beschreibung" required></textarea>
                    <input type="submit" value="Speichern" class="button" name="B_CREATE_CHARACTER" />
                </div>
            </td>
        </tr>
    </form>
    `;

    document.getElementById('charactersContainer').insertAdjacentHTML('beforeend', newCharacterForm);
}

function toggleEditFormVisibility(characterId) {
    const editForm = document.querySelector(`form.character-edit-form[data-character-id="${characterId}"]`);
    const characterContent = document.getElementById('character-' + characterId);
    if (editForm) {
        editForm.remove();
        characterContent.style.display = '';
    } else {
        characterContent.style.display = 'none';
        createEditCharacterForm(characterId, characterContent.dataset.characterName, characterContent.dataset.characterDescription);
    }
}

function createEditCharacterForm(characterId, characterName, characterDescription) {
    const formHtml = `
        <form class="character-edit-form" data-character-id="${characterId}" enctype="multipart/form-data" method="POST">
        <td>
        <div>
            <input type="hidden" name="character_id" value="${characterId}" />
            <input type="text" name="name" placeholder="Charaktername" required value="${characterName}" />
            <input type="file" name="avatar" />
            <textarea name="description" placeholder="Beschreibung" required>${characterDescription.replace(/<br\s*[\/]?>/gi, "\n")}</textarea>
            <input type="submit" value="Speichern" class="button" name="B_CHANGE_CHARACTER" />
        </div>
        </td>
            </form>
    `;
    document.getElementById('charactersContainer').insertAdjacentHTML('beforeend', formHtml);
}


document.addEventListener('DOMContentLoaded', function () {
    toggleCharacterDetails();

    const addButton = document.getElementById('addCharacterButton');
    if (addButton) {
        addButton.addEventListener('click', addNewCharacterForm);
    }

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('edit-character-button')) {
            const characterId = e.target.getAttribute('data-character-id');
            toggleEditFormVisibility(characterId);
        }

        if (e.target.classList.contains('delete-character-button')) {
            const characterId = e.target.getAttribute('data-character-id');
            const characterRow = document.querySelector(`tr.character-bar[data-character-id="${characterId}"]`);
            let deleteConfirmationRow = characterRow.nextElementSibling;

            if (deleteConfirmationRow && deleteConfirmationRow.classList.contains('delete-confirmation')) {
                deleteConfirmationRow.remove();
            } else {
                const confirmationHtml = `
                    <tr class="delete-confirmation">
                        <td>
                            Möchtest du den Charakter wirklich löschen?
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="character_id" value="${characterId}">
                                <input type="submit" value="Löschen" name="B_DELETE_CHARACTER" class="button">
                            </form>
                        </td>
                    </tr>
                `;
                characterRow.insertAdjacentHTML('afterend', confirmationHtml);
            }
        }
    });
});
