<style>
    /* --- Estilos para o Botão de Busca (O bloco com o ícone) --- */
    .search-icon-block {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .search-icon-block svg {
        transition: color 0.3s ease;
    }
    
    /* --- Efeito de HOVER no botão de busca (bloco do ícone) --- */
    .search-icon-block:hover {
        background-color: #1d78f2 !important;
    }
    
    /* O ícone muda para branco no hover */
    .search-icon-block:hover svg {
        color: #ffffff !important;
    }
</style>

<!-- Contêiner principal com a borda azul (#1d78f2) -->
<div style="display: flex; align-items: stretch; border: 1px solid #1d78f2; border-radius: 4px; overflow: hidden; width: 100%; background: #ffffff;">
    
    <!-- Campo de digitação -->
    <input type="text" id="filtro-sistemas" placeholder="Encontrar..." style="flex: 1; border: none; outline: none; padding: 12px 16px; font-size: 16px; color: #333333; background: #ffffff;">

    <!-- Bloco do ícone com fundo cinza e linha divisória azul (#1d78f2) -->
    <div class="search-icon-block" style="background-color: #f4f5f7; border-left: 1px solid #1d78f2; padding: 0 16px; display: flex; align-items: center; justify-content: center;">
        <svg style="color: #6b7280;" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
    </div>
</div>

<script>
(function() {
    function iniciarBusca() {
        const inputFiltro = document.getElementById('filtro-sistemas');
        if(!inputFiltro) return; 

        inputFiltro.addEventListener('input', function() {
            const termoBusca = this.value.toLowerCase();
            
            // Procura todos os grupos maiores (secções) marcados
            const grupos = document.querySelectorAll('.grupo-links-sistemas');

            grupos.forEach(function(grupo) {
                // Dentro de cada grupo, procura os botões gerados pelo Elementor
                const caixas = grupo.querySelectorAll('.elementor-widget-icon-box');
                let temResultadoVisivel = false;

                caixas.forEach(function(caixa) {
                    const textoCaixa = caixa.textContent.toLowerCase();
                    
                    if(textoCaixa.includes(termoBusca)) {
                        caixa.style.display = '';
                        temResultadoVisivel = true; // Encontrou pelo menos um!
                    } else {
                        caixa.style.display = 'none';
                    }
                });

                // UX Aprimorada: Se o grupo não tiver nenhum botão visível, esconde o grupo inteiro (título e espaços)
                if (temResultadoVisivel || termoBusca === '') {
                    grupo.style.display = '';
                } else {
                    grupo.style.display = 'none';
                }
            });
        });
    }

    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(iniciarBusca, 1);
    } else {
        document.addEventListener("DOMContentLoaded", iniciarBusca);
    }
})();
</script>