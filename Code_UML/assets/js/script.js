document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', (event) => {
            let valid = true;
            const inputs = form.querySelectorAll('input[required]');
            inputs.forEach(input => {
                if (input.value.trim() === '') {
                    valid = false;
                }
            });
            if (!valid) {
                alert('Veuillez remplir tous les champs obligatoires.');
                event.preventDefault();
            }
        });
    });
});
