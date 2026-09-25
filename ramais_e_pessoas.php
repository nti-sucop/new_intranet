// 1. GERA O CARD INDIVIDUAL
function gerar_card_usuario_html($user) {
    $ramal = get_user_meta($user->ID, 'ramal', true);
    $cargo = get_user_meta($user->ID, 'cargo', true);
    
    $foto_acf = get_user_meta($user->ID, 'foto_perfil', true);
    if (is_numeric($foto_acf)) {
        $avatar_url = wp_get_attachment_image_url($foto_acf, 'medium');
    } elseif (!empty($foto_acf) && is_string($foto_acf)) {
        $avatar_url = $foto_acf;
    } else {
        $avatar_url = 'https://via.placeholder.com/150x150?text=Sem+Foto';
    }

    $html = '<article class="result-card-custom" data-search-term="' . esc_attr(strtolower($user->display_name . ' ' . $cargo . ' ' . $ramal)) . '">';
    
    // BLOCO AZUL NO TOPO (Cargo agora com efeito de letras maiúsculas)
    if (!empty($cargo)) {
        $html .= '  <div class="card-title-block" style="text-transform: uppercase;"><a href="#">' . esc_html($cargo) . '</a></div>';
    }
    
    // FOTO NO MEIO
    $html .= '  <div class="card-img-wrapper"><img src="' . esc_url($avatar_url) . '" alt="Foto" class="card-img-custom" /></div>';
    
    // TEXTO DISCRETO (Nome com DESTAQUE: Negrito, Maiúsculo e Fonte Maior)
    $html .= '  <div class="card-info-wrapper">';
    $html .= '      <div class="card-cargo" style="font-weight: 900; font-size: 14px; text-transform: uppercase;">' . esc_html($user->display_name) . '</div>';
    $html .= '  </div>';
    
    // RAMAL NO RODAPÉ
    if (!empty($ramal)) {
        $icone = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: sub; margin-right: 4px;"><rect x="3" y="5" width="18" height="14" rx="2" ry="2"></rect><line x1="8" y1="10" x2="8.01" y2="10"></line><line x1="12" y1="10" x2="16" y2="10"></line><line x1="12" y1="14" x2="16" y2="14"></line></svg>';
        $html .= '  <div class="card-ramal-block">' . $icone . esc_html($ramal) . '</div>';
    }
    
    $html .= '</article>';
    return $html;
}

// 2. FUNÇÃO RECURSIVA DOM TREE
function renderizar_arvore_setores($nodos, $depth = 1) {
    if (empty($nodos)) return '';
    
    $ul_class = ($depth >= 4) ? 'arvore-nivel arvore-vertical' : 'arvore-nivel arvore-horizontal';
    
    $html = '<ul class="' . $ul_class . '">';
    foreach ($nodos as $nodo) {
        $html .= '<li class="arvore-nodo">';
        
        $setor = $nodo['nome'];
        $dados = $nodo['dados'];
        $equipe = $dados['equipe'];
        
        // LÓGICA INTELIGENTE PARA IDENTIFICAR O CHEFE
        $chefe = null;
        if (!empty($equipe)) {
            $primeiro = $equipe[0];
            $cargo_primeiro = strtolower(get_user_meta($primeiro->ID, 'cargo', true));
            $ordem_primeiro = (string) $primeiro->ordem_hierarquica;
            
            $tem_lideranca = preg_match('/(chefe|diretor|gerente|coordenador|superintendente|assessor|secretári|presidente)/i', $cargo_primeiro);
            $termina_em_00 = (substr($ordem_primeiro, -3) === '.00');
            
            $ordem_exclusiva = true;
            if (count($equipe) > 1 && $ordem_primeiro === (string) $equipe[1]->ordem_hierarquica) {
                $ordem_exclusiva = false;
            }
            
            if ($tem_lideranca || ($termina_em_00 && $ordem_exclusiva)) {
                $chefe = array_shift($equipe); 
            }
        }
        
        $chefe_html = '';
        $equipe_html = '';
        $qtd_membros = 0;
        
        if ($chefe) {
            $cargo_chefe = get_user_meta($chefe->ID, 'cargo', true);
            if (!empty($cargo_chefe)) {
                $chefe_html = gerar_card_usuario_html($chefe);
                $qtd_membros++;
            }
        }
        
        if (!empty($equipe)) {
            foreach ($equipe as $user) {
                $cargo_membro = get_user_meta($user->ID, 'cargo', true);
                if (!empty($cargo_membro)) {
                    $equipe_html .= '<div class="card-wrapper">' . gerar_card_usuario_html($user) . '</div>';
                    $qtd_membros++;
                }
            }
        }
        
        $texto_membros = ($qtd_membros === 1) ? '1 membro' : $qtd_membros . ' membros';
        
        $html .= '<div class="fluxograma-node-content" data-sector-name="' . esc_attr(strtolower($setor)) . '">';
        $html .= '  <details class="setor-accordion">'; 
        $html .= '    <summary class="setor-titulo">';
        $html .= '       <span class="btn-expandir"><svg class="setor-icone" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg></span>';
        $html .= '       <span class="setor-nome">' . esc_html($setor) . '</span>';
        $html .= '       <span class="badge-qtd">' . $texto_membros . '</span>';
        $html .= '    </summary>';
        
        $html .= '    <div class="setor-conteudo-interno">';
        
        if (!empty($chefe_html)) {
            $html .= '      <div class="hierarquia-chefe">';
            $html .= '         <div class="card-wrapper-chefe">' . $chefe_html . '</div>';
            $html .= '      </div>';
        }
        
        if (!empty($equipe_html)) {
            $classe_equipe = empty($chefe_html) ? 'hierarquia-equipe sem-chefe' : 'hierarquia-equipe';
            $html .= '      <div class="' . $classe_equipe . '">';
            $html .= $equipe_html;
            $html .= '      </div>';
        }
        
        $html .= '    </div>'; 
        $html .= '  </details>';
        $html .= '</div>';
        
        // LINHA DAS DIRETORIAS
        if (strtolower(trim($setor)) === 'gabinete' && !empty($nodo['children'])) {
            $assessorias = [];
            $diretorias  = [];
            
            foreach ($nodo['children'] as $id => $filho) {
                $nome_filho = strtolower($filho['nome']);
                if (strpos($nome_filho, 'diexo') !== false || strpos($nome_filho, 'diobe') !== false || strpos($nome_filho, 'dipro') !== false || strpos($nome_filho, 'diraf') !== false) {
                    $diretorias[$id] = $filho;
                } else {
                    $assessorias[$id] = $filho;
                }
            }
            
            $html .= renderizar_arvore_setores($assessorias, $depth + 1);
            
            if (!empty($diretorias)) {
                $html .= '<div class="linha-diretorias-inferior">';
                $html .= renderizar_arvore_setores($diretorias, $depth + 1);
                $html .= '</div>';
            }
        } else {
            $html .= renderizar_arvore_setores($nodo['children'], $depth + 1);
        }
        
        $html .= '</li>';
    }
    $html .= '</ul>';
    
    return $html;
}

// 3. MOTOR DE PROCESSAMENTO
add_shortcode('lista_de_ramais', 'renderiza_organograma_vertical');

function renderiza_organograma_vertical() {
    $args = ['meta_query' => [['key' => 'ordem_hierarquica', 'compare' => 'EXISTS']]];
    $usuarios = get_users($args);
    $usuarios_validos = [];

    foreach ($usuarios as $user) {
        $ordem = get_user_meta($user->ID, 'ordem_hierarquica', true);
        if (empty($ordem)) continue; 
        
        $user->ordem_hierarquica = $ordem; 
        $user->setor_nome = get_user_meta($user->ID, 'setor', true);
        $usuarios_validos[] = $user;
    }

    usort($usuarios_validos, function($a, $b) {
        if ($a->ordem_hierarquica === $b->ordem_hierarquica) {
            return strcasecmp($a->display_name, $b->display_name);
        }
        return version_compare($a->ordem_hierarquica, $b->ordem_hierarquica, '<') ? -1 : 1;
    });

    $setores_agrupados = [];
    foreach ($usuarios_validos as $user) {
        $nome_setor = !empty($user->setor_nome) ? $user->setor_nome : 'Chefia / Geral';
        if (!isset($setores_agrupados[$nome_setor])) {
            $setores_agrupados[$nome_setor] = ['ordem_minima' => $user->ordem_hierarquica, 'equipe' => []];
        }
        $setores_agrupados[$nome_setor]['equipe'][] = $user;
    }

    $setores_formatados = [];
    foreach ($setores_agrupados as $nome => $dados) {
        $base_id = preg_replace('/(\.00)+(\.01)?$/', '', $dados['ordem_minima']);
        $setores_formatados[$base_id] = [
            'nome' => $nome,
            'dados' => $dados,
            'children' => []
        ];
    }

    uksort($setores_formatados, function($a, $b) {
        return strlen($a) - strlen($b);
    });

    $arvore_mestre = [];
    foreach ($setores_formatados as $base_id => &$nodo) {
        $parent_id = '';
        if (strpos($base_id, '.') !== false) {
            $parent_id = substr($base_id, 0, strrpos($base_id, '.'));
            
            while ($parent_id !== '' && !isset($setores_formatados[$parent_id])) {
                $last_dot = strrpos($parent_id, '.');
                if ($last_dot !== false) {
                    $parent_id = substr($parent_id, 0, $last_dot);
                } else {
                    $parent_id = '';
                }
            }
        }
        
        if ($parent_id !== '' && isset($setores_formatados[$parent_id])) {
            $setores_formatados[$parent_id]['children'][$base_id] = &$nodo;
        } else {
            $arvore_mestre[$base_id] = &$nodo;
        }
    }

    // ==========================================================================
    // BARRA IHC E RENDERIZAÇÃO
    // ==========================================================================
    $output = '
    <div class="ihc-toolbar-container">
        <div class="ihc-search-wrapper">
            <div class="ihc-search-box">
                <input type="text" id="ihcSearchInput" placeholder="Encontrar..." aria-label="Pesquisar no organograma" />
                <span class="ihc-search-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </span>
            </div>
            <div id="ihcSearchFeedback" class="ihc-feedback-msg" style="display:none;"></div>
        </div>
        
        <div class="ihc-zoom-box">
            <button type="button" id="ihcBtnCollapseAll" class="ihc-btn-secondary" title="Fechar todos os setores" style="margin-right: 10px;">
                ⏫ Fechar Cards
            </button>
            <span class="ihc-zoom-label">ZOOM:</span>
            <button type="button" class="ihc-btn-primary" onclick="ihcChangeZoom(-0.1)" title="Diminuir">&#9644;</button>
            <span id="ihcZoomDisplay" class="ihc-zoom-value" title="Tamanho atual">100%</span>
            <button type="button" class="ihc-btn-primary" onclick="ihcChangeZoom(0.1)" title="Aumentar">&#10010;</button>
            <button type="button" class="ihc-btn-secondary" onclick="ihcChangeZoom(0)" title="Voltar ao original">🔄</button>
        </div>
    </div>';

    $output .= '<div class="fluxograma-container" id="ihc-target-container" style="transition: zoom 0.2s ease;">';
    $output .= renderizar_arvore_setores($arvore_mestre, 1);
    $output .= '</div>';

    // CSS GERAL
    $output .= '
    <style>
/* BARRA IHC */
.ihc-toolbar-container { display: flex; flex-wrap: wrap; justify-content: center; align-items: flex-start; gap: 15px; margin-bottom: 20px; font-family: sans-serif; width: 100%; box-sizing: border-box; }
.ihc-search-wrapper { display: flex; flex-direction: column; gap: 8px; flex: 1 1 500px; max-width: 250px; }
.ihc-search-box { display: flex; align-items: center; background: #fff; border: 1px solid #a0bcdc; border-radius: 4px; overflow: hidden; height: 38px; }
#ihcSearchInput { flex: 1; border: none; padding: 0 12px; font-size: 14px; color: #495057; outline: none; height: 100%; }
#ihcSearchInput::placeholder { color: #adb5bd; }
.ihc-search-icon { display: flex; align-items: center; justify-content: center; width: 40px; height: 100%; color: #6c757d; border-left: 1px solid #e2e8f0; background: #f8f9fa; }
.ihc-feedback-msg { font-size: 12px; font-weight: bold; padding: 6px 10px; border-radius: 4px; display: inline-block; }
.ihc-feedback-error { background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.ihc-feedback-success { background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.ihc-zoom-box { display: flex; align-items: center; gap: 8px; background: #fff; padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0; flex-wrap: wrap; height: 38px; box-sizing: border-box; }
.ihc-zoom-label { font-size: 12px; font-weight: bold; color: #0073ce; margin-right: 4px; text-transform: uppercase; }
.ihc-btn-primary { background: #0073ce; color: #ffffff; border: 1px solid #005a9e; border-radius: 4px; padding: 0 12px; height: 26px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); transition: background 0.2s; }
.ihc-btn-primary:hover { background: #005a9e; }
.ihc-btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 4px; padding: 0 12px; height: 26px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: background 0.2s; }
.ihc-btn-secondary:hover { background: #e2e8f0; }
.ihc-zoom-value { font-size: 14px; font-weight: bold; color: #1e293b; min-width: 45px; text-align: center; }
.ihc-highlight { box-shadow: 0 0 0 3px #10b981 !important; border-color: #10b981 !important; transition: all 0.3s ease; }
.ihc-dimmed { opacity: 0.25; filter: grayscale(100%); transition: all 0.3s ease; }

/* CONTAINER E ESTRUTURA HORIZONTAL */
.fluxograma-container { width: 100%; max-width: 100%; overflow-x: auto; overflow-y: hidden; text-align: center; padding: 20px 0 40px 0; box-sizing: border-box; font-family: sans-serif; scrollbar-width: thin; scrollbar-color: #0073ce #f1f1f1; }
.fluxograma-container::-webkit-scrollbar { height: 12px; }
.fluxograma-container::-webkit-scrollbar-track { background: #e0e0e0; border-radius: 8px; margin: 0 10%; }
.fluxograma-container::-webkit-scrollbar-thumb { background: #0073ce; border-radius: 8px; }

.fluxograma-node-content { position: relative; z-index: 2; width: max-content; }

.arvore-horizontal { display: flex; justify-content: center; padding-top: 40px; position: relative; padding-left: 0; margin: 0 auto; width: max-content; min-width: 100%; box-sizing: border-box; }
.arvore-horizontal::before { content: ""; position: absolute; top: 0; left: 50%; border-left: 2px solid #0073ce; width: 0; height: 40px; transform: translateX(-50%); }
.fluxograma-container > .arvore-horizontal::before { display: none; }
.fluxograma-container > .arvore-horizontal { padding-top: 0; }

.arvore-horizontal > .arvore-nodo { flex: 0 0 auto; display: flex; flex-direction: column; align-items: center; list-style-type: none; position: relative; padding: 40px 10px 0 10px; box-sizing: border-box; }
.arvore-horizontal > .arvore-nodo::before, .arvore-horizontal > .arvore-nodo::after { content: ""; position: absolute; top: 0; width: 50%; height: 40px; border-top: 2px solid #0073ce; box-sizing: border-box; }
.arvore-horizontal > .arvore-nodo::before { right: 50%; }
.arvore-horizontal > .arvore-nodo::after { left: 50%; border-left: 2px solid #0073ce; }
.arvore-horizontal > .arvore-nodo:only-child::after, .arvore-horizontal > .arvore-nodo:only-child::before { display: none; }
.arvore-horizontal > .arvore-nodo:only-child { padding-top: 0; }
.arvore-horizontal > .arvore-nodo:first-child::before, .arvore-horizontal > .arvore-nodo:last-child::after { border-top: none; }
.arvore-horizontal > .arvore-nodo:last-child::before { border-right: 2px solid #0073ce; border-radius: 0 6px 0 0; }
.arvore-horizontal > .arvore-nodo:first-child::after { border-radius: 6px 0 0 0; }

/* LINHA SEPARADA PARA DIRETORIAS */
.linha-diretorias-inferior { display: flex; justify-content: center; width: 100%; margin-top: 40px; }
.linha-diretorias-inferior > .arvore-horizontal { padding-top: 0; }
.linha-diretorias-inferior > .arvore-horizontal::before { top: -140px !important; height: 180px !important; z-index: 0; }

/* ESTRUTURA VERTICAL E CASCATA (CORRIGIDA SINTAXE) */
.arvore-vertical { display: flex; flex-direction: column; padding-top: 30px; position: relative; padding-left: 0; margin: 0; width: 100%; box-sizing: border-box; }
.arvore-vertical::before { content: ""; position: absolute; top: 0; left: calc(50% - 1px); width: 2px; height: 30px; background: #0073ce; }
.arvore-vertical > .arvore-nodo { display: block; position: relative; padding: 0; margin: 0; width: 100%; list-style-type: none; }
.arvore-vertical > .arvore-nodo > .fluxograma-node-content { padding-top: 15px; padding-bottom: 15px; margin-left: calc(50% + 25px); width: max-content; }
.arvore-vertical > .arvore-nodo::before { content: ""; position: absolute; top: 0; left: calc(50% - 1px); width: 26px; height: 35px; border-left: 2px solid #0073ce; border-bottom: 2px solid #0073ce; border-top: none; border-right: none; border-radius: 0 0 0 6px; z-index: 1; box-sizing: border-box; }
.arvore-vertical > .arvore-nodo:not(:last-child)::after { content: ""; position: absolute; top: 35px; left: calc(50% - 1px); bottom: 0; width: 2px; background: #0073ce; border: none; z-index: 1; }

.arvore-nodo > .arvore-vertical { padding-top: 0; }
.arvore-vertical .arvore-vertical::before { display: none; }
.arvore-vertical .arvore-vertical > .arvore-nodo > .fluxograma-node-content { margin-left: calc(50% + 75px); }
.arvore-vertical .arvore-vertical > .arvore-nodo::before { left: calc(50% + 45px); width: 30px; }
.arvore-vertical .arvore-vertical > .arvore-nodo:not(:last-child)::after { left: calc(50% + 45px); }
.arvore-vertical .arvore-vertical .arvore-vertical > .arvore-nodo > .fluxograma-node-content { margin-left: calc(50% + 125px); }
.arvore-vertical .arvore-vertical .arvore-vertical > .arvore-nodo::before { left: calc(50% + 95px); width: 30px; }
.arvore-vertical .arvore-vertical .arvore-vertical > .arvore-nodo:not(:last-child)::after { left: calc(50% + 95px); }

/* DESIGN DOS SETORES E CARDS */
.setor-accordion { border: 2px solid #0073ce; border-radius: 6px; background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: max-content; min-width: 220px; overflow: visible !important; }
.setor-titulo { background: #0073ce; color: #fff; padding: 12px 16px; cursor: pointer; display: flex; gap: 8px; justify-content: space-between; align-items: center; list-style: none; transition: 0.2s; text-transform: uppercase; }
.setor-titulo::-webkit-details-marker { display: none; }
.setor-titulo:hover { background: #005a9e; }
.btn-expandir { background: rgba(0, 0, 0, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 4px; padding: 4px; display: flex; align-items: center; justify-content: center; }
.setor-nome { text-align: center; font-size: 13px; font-weight: bold; line-height: 1.2; flex-grow: 1; }
.setor-icone { transition: transform 0.3s ease; }
.setor-accordion[open] .setor-icone { transform: rotate(180deg); }
.badge-qtd { background: rgba(255,255,255,0.2); font-size: 11px; padding: 4px 8px; border-radius: 20px; white-space: nowrap; }

.setor-conteudo-interno { background: #fdfdfd; padding: 25px 15px; border-top: 3px solid #00457a; display: flex; flex-direction: column; align-items: center; width: max-content; min-width: 100%; box-sizing: border-box; }

.hierarquia-chefe { position: relative; padding-bottom: 25px; width: 100%; display: flex; justify-content: center; z-index: 2; }
.hierarquia-chefe::after { content: ""; position: absolute; bottom: 0; left: 50%; width: 2px; height: 25px; background: #0073ce; transform: translateX(-50%); }
.hierarquia-chefe:last-child::after { display: none; }

.hierarquia-equipe { display: flex; flex-wrap: wrap; justify-content: center; gap: 15px; padding-top: 25px; position: relative; width: max-content; max-width: 800px; min-width: 100%; box-sizing: border-box; }
.hierarquia-equipe::before { content: ""; position: absolute; top: 0; left: 80px; right: 80px; height: 2px; background: #0073ce; }
.hierarquia-equipe .card-wrapper { position: relative; padding-top: 25px; flex: 0 0 180px; width: 180px; display: flex; justify-content: center; }
.hierarquia-equipe .card-wrapper::before { content: ""; position: absolute; top: 0; left: 50%; width: 2px; height: 25px; background: #0073ce; transform: translateX(-50%); z-index: 1; }

/* NOVA REGRA: SE NÃO TEM CHEFE, REMOVE AS LINHAS SUPERIORES ÓRFÃS */
.hierarquia-equipe.sem-chefe { padding-top: 10px; }
.hierarquia-equipe.sem-chefe::before { display: none; }
.hierarquia-equipe.sem-chefe .card-wrapper { padding-top: 10px; }
.hierarquia-equipe.sem-chefe .card-wrapper::before { display: none; }

.card-wrapper-chefe, .card-wrapper { width: 100%; max-width: 220px; }
.result-card-custom { width: 100%; background: #fff; border: 1px solid #ddd; padding: 12px; display: flex; flex-direction: column; align-items: center; text-align: center; box-shadow: 2px 2px 8px rgba(0,0,0,0.1); border-radius: 6px; }
.card-title-block { background: #b9d7ea; width: 100%; padding: 8px; border-radius: 4px; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; min-height: 44px; }
.card-title-block a { color: #112a46; font-size: 13px; font-weight: 900; text-decoration: none; line-height: 1.2; word-wrap: break-word; }
.card-img-wrapper { width: 100%; height: 140px; border-radius: 4px; margin-bottom: 10px; flex-shrink: 0; background: #f9f9f9; display: flex; align-items: center; justify-content: center; border: 1px solid #eee; overflow: hidden; }
.card-img-custom { width: 100%; height: 100%; object-fit: contain; }
.card-cargo { color: #112a46; font-size: 13px; margin-bottom: 10px; line-height: 1.2; }
.card-ramal-block { background: #b9d7ea; width: 100%; padding: 8px; border-radius: 4px; color: #112a46; font-size: 16px; font-weight: 900; display: flex; justify-content: center; align-items: center; margin-top: auto; }

/* RESPONSIVIDADE MOBILE (CORRIGIDA SINTAXE) */
@media (max-width: 800px) {
    .arvore-horizontal::before { left: 20px !important; }
    .arvore-horizontal > .arvore-nodo { padding: 0 !important; display: block !important; width: 100%; }
    .arvore-horizontal > .arvore-nodo::before, .arvore-horizontal > .arvore-nodo::after { display: none !important; }
    .arvore-horizontal > .arvore-nodo > .fluxograma-node-content { margin-left: 45px !important; padding-top: 15px; padding-bottom: 15px; }
    .arvore-horizontal > .arvore-nodo::before { content: "" !important; display: block !important; position: absolute; top: 0; left: 20px !important; width: 25px; height: 35px !important; border-left: 2px solid #0073ce !important; border-bottom: 2px solid #0073ce !important; border-top: none !important; border-right: none !important; border-radius: 0 0 0 6px !important; z-index: 1; }
    .arvore-horizontal > .arvore-nodo:not(:last-child)::after { content: "" !important; display: block !important; position: absolute; top: 35px !important; left: 20px !important; bottom: 0; width: 2px; background: #0073ce !important; border: none !important; z-index: 1; }

    .arvore-vertical::before { left: 20px !important; }
    .arvore-vertical > .arvore-nodo > .fluxograma-node-content { margin-left: 45px !important; }
    .arvore-vertical > .arvore-nodo::before { left: 20px !important; width: 25px !important; }
    .arvore-vertical > .arvore-nodo:not(:last-child)::after { left: 20px !important; }
    .arvore-vertical .arvore-vertical > .arvore-nodo > .fluxograma-node-content { margin-left: 70px !important; }
    .arvore-vertical .arvore-vertical > .arvore-nodo::before { left: 45px !important; width: 25px !important; }
    .arvore-vertical .arvore-vertical > .arvore-nodo:not(:last-child)::after { left: 45px !important; }
    .arvore-vertical .arvore-vertical .arvore-vertical > .arvore-nodo > .fluxograma-node-content { margin-left: 95px !important; }
    .arvore-vertical .arvore-vertical .arvore-vertical > .arvore-nodo::before { left: 70px !important; width: 25px !important; }
    .arvore-vertical .arvore-vertical .arvore-vertical > .arvore-nodo:not(:last-child)::after { left: 70px !important; }
}

/* =========================================================
DESKTOP — CENTRALIZAÇÃO SEGURA (SAFE CENTERING)
========================================================= */
@media (min-width: 801px) {
    .fluxograma-container {
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        display: block !important;
        text-align: center !important;
        white-space: nowrap !important;
    }

    .fluxograma-container > .arvore-horizontal { 
        width: max-content !important; 
        min-width: 100% !important; 
        margin: 0 auto !important; 
        display: inline-flex !important; 
        justify-content: center !important; 
        align-items: flex-start !important; 
        zoom: 0.80; 
        transform: none !important; 
        padding-left: 20px !important;
        padding-right: 150px !important; 
    }
}
</style>
    ';

    // JAVASCRIPT: Lógica Corrigida de Fechamento de Busca
    $output .= '
    <script>
    let currentIhcZoom = 1.0; 
    
    function ihcChangeZoom(step) {
        const container = document.getElementById("ihc-target-container");
        const statusLabel = document.getElementById("ihcZoomDisplay");
        if(step === 0) { currentIhcZoom = 1.0; } else { currentIhcZoom += step; }
        currentIhcZoom = Math.max(0.4, Math.min(currentIhcZoom, 2.0));
        container.style.zoom = currentIhcZoom;
        statusLabel.textContent = Math.round(currentIhcZoom * 100) + "%";
    }

    document.addEventListener("DOMContentLoaded", function() {
        const input = document.getElementById("ihcSearchInput");
        const feedback = document.getElementById("ihcSearchFeedback");
        const cards = document.querySelectorAll(".result-card-custom");
        const setores = document.querySelectorAll(".fluxograma-node-content");
        const detailsElements = document.querySelectorAll("details.setor-accordion");

        input.addEventListener("input", function(e) {
            const query = e.target.value.toLowerCase().trim();
            
            document.querySelectorAll(".ihc-highlight").forEach(el => el.classList.remove("ihc-highlight"));
            document.querySelectorAll(".ihc-dimmed").forEach(el => el.classList.remove("ihc-dimmed"));

            if (!query) {
                feedback.style.display = "none";
                return;
            }

            detailsElements.forEach(det => det.removeAttribute("open"));

            let foundCount = 0;

            cards.forEach(card => {
                const term = card.getAttribute("data-search-term") || "";
                if (term.includes(query)) {
                    card.classList.add("ihc-highlight");
                    foundCount++;
                    const parentDetails = card.closest("details");
                    if (parentDetails) parentDetails.setAttribute("open", "true");
                } else {
                    card.classList.add("ihc-dimmed");
                }
            });

            setores.forEach(setor => {
                const sectorName = setor.getAttribute("data-sector-name") || "";
                if (sectorName.includes(query)) {
                    const acordeao = setor.querySelector(".setor-accordion");
                    if(acordeao) {
                        acordeao.classList.add("ihc-highlight");
                        foundCount++;
                    }
                }
            });

            feedback.style.display = "inline-block";
            if (foundCount === 0) {
                feedback.textContent = "⚠️ Busca não encontrada! Tente outro termo.";
                feedback.className = "ihc-feedback-msg ihc-feedback-error";
            } else {
                feedback.textContent = "✅ " + foundCount + (foundCount === 1 ? " resultado encontrado." : " resultados encontrados.");
                feedback.className = "ihc-feedback-msg ihc-feedback-success";
            }
        });

        // BOTÃO FECHAR TODOS (Forçado)
        const btnCollapseAll = document.getElementById("ihcBtnCollapseAll");
        if (btnCollapseAll) {
            btnCollapseAll.addEventListener("click", function(e) {
                e.preventDefault(); 
                
                document.querySelectorAll("details.setor-accordion").forEach(function(det) {
                    det.removeAttribute("open");
                });
                
                input.value = "";
                feedback.style.display = "none";
                document.querySelectorAll(".ihc-highlight").forEach(el => el.classList.remove("ihc-highlight"));
                document.querySelectorAll(".ihc-dimmed").forEach(el => el.classList.remove("ihc-dimmed"));
            });
        }
    });
    </script>
    ';

    return $output;
}
