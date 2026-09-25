<script>
document.addEventListener('DOMContentLoaded', function() {
    // Seleciona o contêiner que tem a classe 'my-sticky-header'
    const header = document.querySelector('.my-sticky-header');
    
    if (header) {
        window.addEventListener('scroll', function() {
            // Se rolar mais de 50px, adiciona a classe 'sticky'
            if (window.scrollY > 50) {
                header.classList.add('sticky');
            } else {
                // Se voltar ao topo, remove
                header.classList.remove('sticky');
            }
        });
    }
});
</script>