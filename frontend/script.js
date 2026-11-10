document.addEventListener('DOMContentLoaded', () => {

  /* ==========================================================================
     1. BANNER E CONTAGEM REGRESSIVA
     ========================================================================== */
  const dataEvento = new Date(2027, 3, 24, 20, 0, 0).getTime();

  function atualizarContagem() {
    const elCountdown = document.querySelector('#countdown');
    if (!elCountdown) return;

    const agora = new Date().getTime();
    const diferenca = dataEvento - agora;

    if (isNaN(diferenca) || diferenca <= 0) {
      elCountdown.innerHTML = "<p class='evento-iniciado'>O RITUAL COMEÇOU!</p>";
      return;
    }

    const dias = Math.floor(diferenca / (1000 * 60 * 60 * 24));
    const horas = Math.floor((diferenca % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutos = Math.floor((diferenca % (1000 * 60 * 60)) / (1000 * 60));
    const segundos = Math.floor((diferenca % (1000 * 60)) / 1000);

    const elDias = document.querySelector('#dias');
    const elHoras = document.querySelector('#horas');
    const elMinutos = document.querySelector('#minutos');
    const elSegundos = document.querySelector('#segundos');

    if (elDias) elDias.textContent = String(dias).padStart(2, '0');
    if (elHoras) elHoras.textContent = String(horas).padStart(2, '0');
    if (elMinutos) elMinutos.textContent = String(minutos).padStart(2, '0');
    if (elSegundos) elSegundos.textContent = String(segundos).padStart(2, '0');
  }

  atualizarContagem();
  setInterval(atualizarContagem, 1000);


  /* ==========================================================================
     2. NAVBAR STICKY
     ========================================================================== */
  const navbar = document.querySelector('.navbar');
  if (navbar) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 50) {
        navbar.classList.add('navbar-scrolled');
      } else {
        navbar.classList.remove('navbar-scrolled');
      }
    });
  }


  /* ==========================================================================
     3. FORMULÁRIOS E ABAS DE INTERFACE
     ========================================================================== */
  const btnToggle = document.querySelector('#btn-toggle-form');
  const containerAuthForms = document.querySelector('#container-auth-forms');
  const iconeSeta = document.querySelector('#icone-seta');

  if (btnToggle && containerAuthForms) {
    btnToggle.addEventListener('click', () => {
      containerAuthForms.classList.toggle('escondido');
      if (iconeSeta) {
        iconeSeta.textContent = containerAuthForms.classList.contains('escondido') ? '▼' : '▲';
      }
    });
  }

  const abaLogin = document.querySelector('#aba-login');
  const abaCadastro = document.querySelector('#aba-cadastro');
  const formLogin = document.querySelector('#form-login');
  const formCadastro = document.querySelector('#form-cadastro');

  if (abaLogin && abaCadastro && formLogin && formCadastro) {
    abaLogin.addEventListener('click', () => {
      formLogin.classList.remove('escondido');
      formCadastro.classList.add('escondido');
      abaLogin.classList.add('ativa');
      abaCadastro.classList.remove('ativa');
    });

    abaCadastro.addEventListener('click', () => {
      formCadastro.classList.remove('escondido');
      formLogin.classList.add('escondido');
      abaCadastro.classList.add('ativa');
      abaLogin.classList.remove('ativa');
    });
  }

  const radiosPerfil = document.querySelectorAll('input[name="tipo_perfil"]');
  const camposHeadbanger = document.querySelector('#campos-headbanger');
  const camposBanda = document.querySelector('#campos-banda');

  function atualizarCamposPerfil() {
    const perfilSelecionado = document.querySelector('input[name="tipo_perfil"]:checked')?.value;
    if (perfilSelecionado === 'banda') {
      if (camposBanda) camposBanda.classList.remove('escondido');
      if (camposHeadbanger) camposHeadbanger.classList.add('escondido');
    } else {
      if (camposHeadbanger) camposHeadbanger.classList.remove('escondido');
      if (camposBanda) camposBanda.classList.add('escondido');
    }
  }

  if (radiosPerfil.length > 0) {
    radiosPerfil.forEach(radio => {
      radio.addEventListener('change', atualizarCamposPerfil);
    });
    atualizarCamposPerfil();
  }

  const campoSenha = document.querySelector('#senha');
  const btnToggleSenha = document.querySelector('#btn-toggle-senha');

  if (campoSenha && btnToggleSenha) {
    btnToggleSenha.addEventListener('click', () => {
      const tipoAtual = campoSenha.getAttribute('type');
      if (tipoAtual === 'password') {
        campoSenha.setAttribute('type', 'text');
        btnToggleSenha.textContent = '🙈';
      } else {
        campoSenha.setAttribute('type', 'password');
        btnToggleSenha.textContent = '👁️';
      }
    });
  }

  const btnEditarMinhaBanda = document.querySelector('#btn-editar-minha-banda');
  const containerEdicaoBanda = document.querySelector('#container-edicao-banda');
  const formEditarBanda = document.querySelector('#form-editar-banda');

  if (btnEditarMinhaBanda && containerEdicaoBanda) {
    btnEditarMinhaBanda.addEventListener('click', () => {
      containerEdicaoBanda.classList.toggle('escondido');
      if (!containerEdicaoBanda.classList.contains('escondido')) {
        carregarDadosMinhaBanda();
      }
    });
  }


  /* ==========================================================================
     4. REQUISIÇÕES HTTP (LOGIN, CADASTRO, LOGOUT E BANDA)
     ========================================================================== */
  if (formLogin) {
    formLogin.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(formLogin);
      const payload = Object.fromEntries(formData.entries());

      try {
        const resposta = await fetch('backend/api/auth/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const textoBruto = await resposta.text();
        let resultado;
        try { resultado = JSON.parse(textoBruto); } catch(e) {}

        if (resposta.ok && resultado) {
          alert(resultado.mensagem || 'Login realizado com sucesso!');
          formLogin.reset();
          exibirPainelMembro(resultado.usuario);
        } else {
          alert(`Erro (${resultado?.status || 400}): ${resultado?.erro || 'Credenciais inválidas.'}`);
        }
      } catch (erro) {
        alert('Erro ao conectar com o servidor Apache.');
      }
    });
  }

  if (formCadastro) {
    formCadastro.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(formCadastro);
      const payload = Object.fromEntries(formData.entries());
      payload.novidades = document.querySelector('#novidades')?.checked || false;

      try {
        const resposta = await fetch('backend/api/auth/register.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const textoBruto = await resposta.text();
        let resultado;
        try { resultado = JSON.parse(textoBruto); } catch(e) {}

        if (resposta.ok && resultado) {
          alert(resultado.mensagem || 'Cadastro realizado com sucesso!');
          formCadastro.reset();
          atualizarCamposPerfil();
          if (abaLogin) abaLogin.click();
        } else {
          alert(`Erro (${resultado?.status || 400}): ${resultado?.erro || 'Falha no cadastro.'}`);
        }
      } catch (erro) {
        alert('Erro ao conectar com o servidor Apache.');
      }
    });
  }

  const btnLogout = document.querySelector('#btn-logout');
  if (btnLogout) {
    btnLogout.addEventListener('click', async () => {
      try {
        const resposta = await fetch('backend/api/auth/logout.php', { method: 'POST' });
        const resultado = await resposta.json();
        alert(resultado.mensagem || 'Sessão encerrada.');
        window.location.reload();
      } catch (erro) {
        console.error('Erro no logout:', erro);
      }
    });
  }

  if (formEditarBanda) {
    formEditarBanda.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(formEditarBanda);
      const payload = Object.fromEntries(formData.entries());

      try {
        const resposta = await fetch('backend/api/bandas/salvar.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const resultado = await resposta.json();
        if (resposta.ok) {
          alert(resultado.mensagem);
          if (containerEdicaoBanda) containerEdicaoBanda.classList.add('escondido');
          carregarListaBandas();
        } else {
          alert(`Erro (${resultado.status}): ${resultado.erro}`);
        }
      } catch (erro) {
        alert('Falha ao comunicar com o servidor.');
      }
    });
  }

  function verificarPermissaoBanda(perfil) {
    if (btnEditarMinhaBanda) {
      if (perfil === 'banda' || perfil === 'admin') {
        btnEditarMinhaBanda.classList.remove('escondido');
      } else {
        btnEditarMinhaBanda.classList.add('escondido');
      }
    }
  }

  function exibirPainelMembro(usuario) {
    const statusMembro = document.querySelector('#status-membro');
    const containerAuthForms = document.querySelector('#container-auth-forms');
    const nomeLogado = document.querySelector('#nome-usuario-logado');
    const perfilLogado = document.querySelector('#perfil-usuario-logado');

    if (statusMembro && containerAuthForms) {
      statusMembro.classList.remove('escondido');
      containerAuthForms.classList.add('escondido');
      if (nomeLogado) nomeLogado.textContent = usuario.nome;
      if (perfilLogado) perfilLogado.textContent = usuario.perfil.toUpperCase();
    }
    verificarPermissaoBanda(usuario.perfil);
  }

  async function carregarDadosMinhaBanda() {
    try {
      const resposta = await fetch('backend/api/bandas/minha_banda.php');
      if (resposta.ok) {
        const dados = await resposta.json();
        const banda = dados.banda;

        const inputNome = document.querySelector('#edit-nome-banda');
        const selectSubgenero = document.querySelector('#edit-subgenero-banda');
        const inputLink = document.querySelector('#edit-link-media');
        const textareaBio = document.querySelector('#edit-biografia');

        if (inputNome) inputNome.value = banda.nome_banda || '';
        if (selectSubgenero) selectSubgenero.value = banda.subgenero || 'heavy';
        if (inputLink) inputLink.value = banda.link_media || '';
        if (textareaBio) textareaBio.value = banda.biografia || '';
      }
    } catch (erro) {
      console.error('Erro ao carregar dados da banda:', erro);
    }
  }

  async function checarSessaoAtiva() {
    try {
      const resposta = await fetch('backend/api/auth/me.php');
      if (resposta.ok) {
        const dados = await resposta.json();
        if (dados.autenticado) {
          exibirPainelMembro(dados.usuario);
        }
      }
    } catch (err) {
      // Usuário não autenticado
    }
  }

  async function carregarListaBandas() {
    const gridBandas = document.querySelector('#grid-bandas');
    if (!gridBandas) return;

    try {
      const resposta = await fetch('backend/api/bandas/listar.php');
      if (resposta.ok) {
        const dados = await resposta.json();
        const bandas = dados.bandas || [];

        if (bandas.length === 0) {
          gridBandas.innerHTML = '<p style="color:#aaa;">Nenhuma banda cadastrada no momento.</p>';
          return;
        }

        gridBandas.innerHTML = bandas.map(b => `
          <div class="card-banda-item" style="background:#121212; border:1px solid #4a0000; padding:15px; border-radius:8px;">
            <h3 style="color:#ff4444; margin-top:0;">${escapeHTML(b.nome_banda)}</h3>
            <p style="font-size:0.85rem; color:#888;">Gênero: <strong>${(b.subgenero || '').toUpperCase()}</strong></p>
            <p style="font-size:0.9rem; color:#ddd;">${escapeHTML(b.biografia || 'Sem biografia informada.')}</p>
            ${b.link_media ? `<a href="${escapeHTML(b.link_media)}" target="_blank" class="link-gotico" style="font-size:0.85rem;">Ouvir / Mídia ➔</a>` : ''}
          </div>
        `).join('');
      }
    } catch (erro) {
      console.error('Erro ao carregar lista de bandas:', erro);
    }
  }

  // Higienização contra XSS usando o parser nativo do DOM
  function escapeHTML(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }


  /* ==========================================================================
     5. MÍDIA E GALERIA (CARROSSEL, HOVER E TOOLTIPS)
     ========================================================================== */
  const carrossel = document.querySelector('.carrossel');
  if (carrossel) {
    let carrosselInterval = null;
    const tempoTroca = 4000;

    function proximoSlide() {
      const item = carrossel.querySelector('.carrossel-item');
      if (!item) return;

      const larguraSlide = item.offsetWidth;
      const maxScroll = carrossel.scrollWidth - carrossel.clientWidth;

      if (carrossel.scrollLeft >= maxScroll - 10) {
        carrossel.scrollTo({ left: 0, behavior: 'smooth' });
      } else {
        carrossel.scrollBy({ left: larguraSlide, behavior: 'smooth' });
      }
    }

    function iniciarCarrossel() {
      carrosselInterval = setInterval(proximoSlide, tempoTroca);
    }

    function pausarCarrossel() {
      clearInterval(carrosselInterval);
    }

    iniciarCarrossel();
    carrossel.addEventListener('mouseenter', pausarCarrossel);
    carrossel.addEventListener('mouseleave', iniciarCarrossel);
  }

  const imgBirmingham = document.querySelector('#img-birmingham');
  const legendaBirmingham = document.querySelector('#legenda-birmingham');

  if (imgBirmingham) {
    const imgOriginal = 'Imagens/imagem-birmingham.webp';
    const altOriginal = 'Fábricas industriais de Birmingham com atmosfera sombria';
    const textoLegendaOriginal = 'Paisagem industrial de Birmingham na década de 1960.';

    const imgHover = 'https://whiplash.net/imagens_promo_22/blacksabbath_classic_pb_21102020.jpg';
    const altHover = 'Apresentação histórica do Black Sabbath no início da carreira';
    const textoLegendaHover = 'Black Sabbath em uma de suas primeiras apresentações ao vivo.';

    imgBirmingham.addEventListener('mouseenter', () => {
      imgBirmingham.src = imgHover;
      imgBirmingham.alt = altHover;
      if (legendaBirmingham) legendaBirmingham.textContent = textoLegendaHover;
    });

    imgBirmingham.addEventListener('mouseleave', () => {
      imgBirmingham.src = imgOriginal;
      imgBirmingham.alt = altOriginal;
      if (legendaBirmingham) legendaBirmingham.textContent = textoLegendaOriginal;
    });
  }

  const termosTooltip = document.querySelectorAll('.tooltip-term');
  if (termosTooltip.length > 0) {
    const tooltipBox = document.createElement('div');
    tooltipBox.classList.add('custom-tooltip');
    document.body.appendChild(tooltipBox);

    termosTooltip.forEach(termo => {
      termo.addEventListener('mouseenter', () => {
        const texto = termo.getAttribute('data-tooltip');
        if (!texto) return;

        tooltipBox.textContent = texto;
        tooltipBox.classList.add('visivel');
      });

      termo.addEventListener('mousemove', (e) => {
        tooltipBox.style.left = `${e.pageX + 15}px`;
        tooltipBox.style.top = `${e.pageY + 15}px`;
      });

      termo.addEventListener('mouseleave', () => {
        tooltipBox.classList.remove('visivel');
      });
    });
  }


  /* ==========================================================================
     6. EXECUÇÕES INICIAIS
     ========================================================================== */
  checarSessaoAtiva();
  carregarListaBandas();
});
