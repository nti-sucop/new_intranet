<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.elementor-search-form__submit, button[type="submit"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (document.body.classList.contains('left')) {
                e.preventDefault(); // Cancela a busca vazia
                document.body.classList.remove('left'); // Abre a barra
                // Foca no campo de texto após 350ms
                setTimeout(() => btn.closest('form')?.querySelector('input[type="search"]')?.focus(), 350);
            }
        });
    });
});
</script>