
      const btnToggle = document.querySelector('#btn-toggle-form');
      const formTopo = document.querySelector('.form-topo');
      const iconeSeta = document.querySelector('#icone-seta');

      btnToggle.addEventListener('click', function() {
          formTopo.classList.toggle('escondido');
          iconeSeta.textContent = formTopo.classList.contains('escondido') ? '▼' : '▲';
      });

// Data-alvo: 31 de Outubro de 2026 às 20:00:00
const dataEvento = new Date('2026-10-31T20:00:00').getTime();

function atualizarContagem() {
    const agora = new Date().getTime();
    const diferenca = dataEvento - agora;

    // Se a data já passou
    if (diferenca <= 0) {
        document.querySelector('#countdown').innerHTML = "<p class='evento-iniciado'>O RITUAL COMEÇOU!</p>";
        return;
    }

    // Cálculos de dias, horas, minutos e segundos
    const dias = Math.floor(diferenca / (1000 * 60 * 60 * 24));
    const horas = Math.floor((diferenca % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutos = Math.floor((diferenca % (1000 * 60 * 60)) / (1000 * 60));
    const segundos = Math.floor((diferenca % (1000 * 60)) / 1000);

    // Atualiza os elementos na tela formatando com zero à esquerda
    document.querySelector('#dias').textContent = String(dias).padStart(2, '0');
    document.querySelector('#horas').textContent = String(horas).padStart(2, '0');
    document.querySelector('#minutos').textContent = String(minutos).padStart(2, '0');
    document.querySelector('#segundos').textContent = String(segundos).padStart(2, '0');
}

// Executa na hora e agenda atualizações a cada 1 segundo (1000ms)
atualizarContagem();
setInterval(atualizarContagem, 1000);

//navbar fixa no topo da tela
const navbar = document.querySelector('.navbar');

window.addEventListener('scroll', () => {
    // Adiciona a classe 'navbar-scrolled' se a rolagem passar de 50px
    if (window.scrollY > 50) {
        navbar.classList.add('navbar-scrolled');
    } else {
        navbar.classList.remove('navbar-scrolled');
    }
});


// Elementos da interface
const formInscricao = document.querySelector('#form-inscricao');
const statusMembro = document.querySelector('#status-membro');

// Função para atualizar a interface com o status do membro
function exibirStatusMembro(nome) {
    if (!statusMembro) return;
    statusMembro.innerHTML = `<p>⚡ Status: <strong>Membro Ativo</strong> (${nome})</p>`;
    statusMembro.classList.remove('escondido');
}
/*
//Ao carregar a página: verifica se existem dados salvos no LocalStorage
document.addEventListener('DOMContentLoaded', () => {
    const nomeSalvo = localStorage.getItem('metal_membro_nome');
    const corSalva = localStorage.getItem('metal_cor_tema');

    // Se houver cor salva, aplica na variável CSS do tema
    if (corSalva) {
        document.documentElement.style.setProperty('--cor-destaque', corSalva);
    }

    // Se houver membro salvo, exibe o crachá de membro ativo
    if (nomeSalvo) {
        exibirStatusMembro(nomeSalvo);
    }
});

//Ao submeter o formulário: grava as preferências do usuário no LocalStorage
if (formInscricao) {
    formInscricao.addEventListener('submit', (e) => {
        e.preventDefault(); // Impede o recarregamento da página

        const campoNome = document.querySelector('#fname');
        const campoCor = document.querySelector('#cor-tema');

        if (campoNome && campoCor) {
            const nome = campoNome.value.trim();
            const cor = campoCor.value;

            // Grava os dados no navegador do usuário
            localStorage.setItem('metal_membro_nome', nome);
            localStorage.setItem('metal_cor_tema', cor);

            // Atualiza o tema e o status em tempo real sem recarregar a página
            document.documentElement.style.setProperty('--cor-destaque', cor);
            exibirStatusMembro(nome);

            alert(`Membro registrado com sucesso! Bem-vindo à aliança, ${nome}.`);
        }
    });
}
*/
// Carrossel Automático
const carrossel = document.querySelector('.carrossel');

if (carrossel) {
    let carrosselInterval = null;
    const tempoTroca = 4000; // Tempo em milissegundos (4 segundos)

    function proximoSlide() {
        const item = carrossel.querySelector('.carrossel-item');
        if (!item) return;

        // Largura do item + o espaço da lacuna (gap)
        const larguraSlide = item.offsetWidth;
        const maxScroll = carrossel.scrollWidth - carrossel.clientWidth;

        // Se estiver no último slide, volta para o início. Caso contrário, avança um slide.
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

    // Inicializa a rotação automática
    iniciarCarrossel();

    // Pausa a rolagem no hover para permitir leitura confortável da legenda
    carrossel.addEventListener('mouseenter', pausarCarrossel);
    carrossel.addEventListener('mouseleave', iniciarCarrossel);
}


// Alternar Visibilidade da Senha
const campoSenha = document.querySelector('#senha');
const btnToggleSenha = document.querySelector('#btn-toggle-senha');

if (campoSenha && btnToggleSenha) {
    btnToggleSenha.addEventListener('click', () => {
        // Verifica o tipo atual do input
        const tipoAtual = campoSenha.getAttribute('type');

        if (tipoAtual === 'password') {
            campoSenha.setAttribute('type', 'text');
            btnToggleSenha.textContent = '🙈'; // Altera ícone para olho oculto/fechado
            btnToggleSenha.setAttribute('aria-label', 'Ocultar senha');
        } else {
            campoSenha.setAttribute('type', 'password');
            btnToggleSenha.textContent = '👁️'; // Altera ícone para olho visível
            btnToggleSenha.setAttribute('aria-label', 'Mostrar senha');
        }
    });
}

// Troca de Imagem no Hover (Birmingham -> Black Sabbath)
const imgBirmingham = document.querySelector('#img-birmingham');
const legendaBirmingham = document.querySelector('#legenda-birmingham');

if (imgBirmingham) {
    // Links e textos das duas imagens
    const imgOriginal = 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?q=80&w=800';
    const altOriginal = 'Fábricas industriais de Birmingham com atmosfera sombria';
    const textoLegendaOriginal = 'Paisagem industrial de Birmingham na década de 1960.';

    const imgHover = 'https://whiplash.net/imagens_promo_22/blacksabbath_classic_pb_21102020.jpg';
    const altHover = 'Apresentação histórica do Black Sabbath no início da carreira';
    const textoLegendaHover = 'Black Sabbath em uma de suas primeiras apresentações ao vivo.';

    // Ao passar o mouse: altera imagem, alt e legenda
    imgBirmingham.addEventListener('mouseenter', () => {
        imgBirmingham.src = imgHover;
        imgBirmingham.alt = altHover;
        if (legendaBirmingham) legendaBirmingham.textContent = textoLegendaHover;
    });

    // Ao retirar o mouse: restaura o estado original
    imgBirmingham.addEventListener('mouseleave', () => {
        imgBirmingham.src = imgOriginal;
        imgBirmingham.alt = altOriginal;
        if (legendaBirmingham) legendaBirmingham.textContent = textoLegendaOriginal;
    });
}

// Gerenciamento de Tooltips em JavaScript
const termosTooltip = document.querySelectorAll('.tooltip-term');

if (termosTooltip.length > 0) {
    // Criar o elemento container do tooltip dinamicamente no DOM
    const tooltipBox = document.createElement('div');
    tooltipBox.classList.add('custom-tooltip');
    document.body.appendChild(tooltipBox);

    termosTooltip.forEach(termo => {
        // Ao passar o mouse sobre o termo
        termo.addEventListener('mouseenter', (e) => {
            const texto = termo.getAttribute('data-tooltip');
            if (!texto) return;

            tooltipBox.textContent = texto;
            tooltipBox.classList.add('visivel');
        });

        // Conforme o mouse se move dentro do termo, o tooltip acompanha
        termo.addEventListener('mousemove', (e) => {
            // Posiciona o balão 15px abaixo e à direita do cursor
            tooltipBox.style.left = `${e.pageX + 15}px`;
            tooltipBox.style.top = `${e.pageY + 15}px`;
        });

        // Ao retirar o mouse do termo
        termo.addEventListener('mouseleave', () => {
            tooltipBox.classList.remove('visivel');
        });
    });
}
