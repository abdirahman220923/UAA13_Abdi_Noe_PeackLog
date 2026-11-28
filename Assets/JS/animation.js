'use strict';

// Afficher seulement les champs des sports coches. Le PHP valide aussi ces choix.
document.querySelectorAll('[data-sport-toggle]').forEach((checkbox) => {
    const fieldset = document.querySelector(`[data-sport-fields="${checkbox.dataset.sportToggle}"]`);
    if (!fieldset) return;

    const updateFields = () => {
        fieldset.hidden = !checkbox.checked;
        fieldset.disabled = !checkbox.checked;
        const frequency = fieldset.querySelector('input[name$="_frequency"]');
        if (frequency) frequency.required = checkbox.checked;
    };
    checkbox.addEventListener('change', updateFields);
    updateFields();
});
