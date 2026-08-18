
      const btnToggle = document.querySelector('#btn-toggle-form');
      const formTopo = document.querySelector('.form-topo');
      const iconeSeta = document.querySelector('#icone-seta');

      btnToggle.addEventListener('click', function() {
          formTopo.classList.toggle('escondido');
          iconeSeta.textContent = formTopo.classList.contains('escondido') ? '▼' : '▲';
      });
