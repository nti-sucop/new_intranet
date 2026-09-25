<style>
  /* Container principal */
  .busca-container {
    display: flex;
    align-items: center;
    font-family: system-ui, -apple-system, sans-serif;
  }

  /* Estilo da primeira lupa (SVG) */
  .btn-lupa {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 5px;
    color: #ffffff;
    display: flex;
    align-items: center;
  }

  .btn-lupa:hover {
    color: #0073ce;
  }

  /* Estado inicial do Formulário (Escondido) */
  .caixa-pesquisa {
    display: flex;
    align-items: center;
    width: 0;
    opacity: 0;
    overflow: hidden; 
    transition: all 0.3s ease; 
    background-color: transparent;
    border-radius: 8px; 
    margin: 0;
  }

  /* Estado do Formulário quando aberto */
  .busca-container.aberto .caixa-pesquisa {
    width: 230px; 
    opacity: 1;
    background-color: #fff;
    border: 1px solid #d1d5db; 
    box-shadow: 0 1px 2px rgba(0,0,0,0.05); 
    margin-left: 10px;
    box-sizing: border-box;
  }

  /* O campo onde você digita */
  .input-busca {
    flex: 1; 
    border: none;
    padding: 8px 12px;
    background: transparent;
    outline: none; 
    width: 100%; 
    box-sizing: border-box; 
    font-size: 16px;
    color: #333;
  }

  /* Estilo da segunda lupa (A de confirmar busca) */
  .btn-lupa-interna {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 5px 12px;
    color: #6b7280;
    display: flex;
    align-items: center;
  }

  .btn-lupa-interna:hover {
    color: #0073ce;
  }
</style>

<div class="busca-container" id="busca-container">
  
  <button type="button" id="btn-lupa-abrir" class="btn-lupa" aria-label="Abrir busca">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
    </svg>
  </button>
  
    <form role="search" method="get" action="" class="caixa-pesquisa" id="form-busca">
    
    <input type="search" id="input-busca" class="input-busca" placeholder="Pesquisa global..." value="" name="s" autocomplete="on">

    <!-- Linha adicionada para forçar a busca global em todos os Custom Post Types -->
    <input type="hidden" name="post_type" value="any">
    
    <button type="submit" id="btn-lupa-buscar" class="btn-lupa-interna" aria-label="Pesquisar">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="20" height="20">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
      </svg>
    </button>
  </form>

</div>

<script>
  // Trocamos para 'load', que espera a página inteira (imagens, scripts, elementor) carregar
  window.addEventListener("load", function() {
    
    // Damos um fôlego extra de meio segundo (500ms) para o Elementor montar o menu
    setTimeout(function() {
      const containerBusca = document.getElementById('busca-container');
      
      // 1. Agora miramos na <ul> (a lista real de botões do menu HFE)
      const menuNav = document.querySelector('ul.hfe-nav-menu') || document.querySelector('.hfe-nav-menu__layout-horizontal ul'); 

      if(containerBusca && menuNav) {
        // 2. Criamos uma "capa" de item de menu (<li>) para a lupa ser aceita na fila perfeitamente
        const liCapa = document.createElement('li');
        liCapa.style.display = 'flex';
        liCapa.style.alignItems = 'center';
        liCapa.style.marginLeft = '10px'; // Espaçamento da Intranet Antiga
        
        // Colocamos a busca dentro dessa capa e jogamos no menu!
        liCapa.appendChild(containerBusca);
        menuNav.appendChild(liCapa);
        
        console.log("Sucesso: A lupa foi teletransportada para o menu!");
      } else {
        console.log("Aviso: O script não encontrou o menuNav ou a lupa na tela.");
      }

      // 3. O código de abrir/fechar continua igual
      const btnAbrir = document.getElementById('btn-lupa-abrir');
      const input = document.getElementById('input-busca');

      if(containerBusca && btnAbrir) {
        btnAbrir.addEventListener('click', function(e) {
          e.preventDefault(); 
          e.stopPropagation(); 
          
          containerBusca.classList.toggle('aberto');
          if (containerBusca.classList.contains('aberto')) {
            input.focus(); 
          }
        });

        document.addEventListener('click', function(e) {
          if (containerBusca.classList.contains('aberto') && !containerBusca.contains(e.target)) {
            containerBusca.classList.remove('aberto'); 
          }
        });
      }
    }, 500); // Fim do atraso de 500ms
  });
</script>