/**
 * Schilderij Visualizer — canvas logica
 *
 * Werking:
 * 1. Klant upload kamerfoto  → tekent op <canvas>
 * 2. Klant klikt op canvas   → plaatst schilderij op die positie
 * 3. Klant sleept / schaalt  → past schilderij aan
 * 4. Knop "Foto opslaan"     → download als PNG
 */
(function () {
  'use strict';

  /* ---- initialiseer elke .pv-wrap op de pagina ---- */
  document.querySelectorAll('.pv-wrap').forEach(initVisualizer);

  function initVisualizer(wrap) {
    const REST_URL      = (typeof PV !== 'undefined') ? PV.rest_url : '/wp-json/pv/v1/';

    /* DOM-elementen */
    const selectEl      = wrap.querySelector('.pv-select-schilderij');
    const previewThumb  = wrap.querySelector('.pv-preview-thumb');
    const dropzone      = wrap.querySelector('#pv-dropzone');
    const fileInput     = wrap.querySelector('#pv-file-input');
    const canvasWrap    = wrap.querySelector('.pv-canvas-wrap');
    const canvas        = wrap.querySelector('#pv-canvas');
    const ctx           = canvas.getContext('2d');
    const scaleSlider   = wrap.querySelector('#pv-scale');
    const scaleVal      = wrap.querySelector('#pv-scale-val');
    const resetBtn      = wrap.querySelector('#pv-reset');
    const downloadBtn   = wrap.querySelector('#pv-download');
    const koopLink      = wrap.querySelector('#pv-koop-link');

    /* State */
    let kamerImg        = null;   // HTMLImageElement van de kamerfoto
    let schilderijImg   = null;   // HTMLImageElement van het schilderij
    let schilderijData  = null;   // { id, breedte, hoogte, koop_url, ... }

    let pos     = { x: 0, y: 0 };   // canvas-coördinaten middelpunt schilderij
    let schaal  = 1.0;               // huidige schaalfactor (slider)
    let sleept  = false;
    let offset  = { x: 0, y: 0 };

    /* ================================================
       1. Laad schilderijen in dropdown (indien aanwezig)
       ================================================ */
    if (selectEl) {
      fetch(REST_URL + 'schilderijen')
        .then(r => r.json())
        .then(lijst => {
          lijst.forEach(s => {
            const opt = document.createElement('option');
            opt.value       = s.id;
            opt.textContent = s.titel;
            selectEl.appendChild(opt);
          });
        })
        .catch(console.error);

      selectEl.addEventListener('change', () => {
        const id = parseInt(selectEl.value);
        if (id) laadSchilderij(id);
      });
    } else {
      /* id staat als data-attribuut op de wrap */
      const id = parseInt(wrap.dataset.schilderijId);
      if (id) laadSchilderij(id);
    }

    /* ================================================
       2. Schilderij data + afbeelding laden
       ================================================ */
    function laadSchilderij(id) {
      fetch(REST_URL + 'schilderijen/' + id)
        .then(r => r.json())
        .then(data => {
          schilderijData = data;
          if (koopLink) koopLink.href = data.koop_url;

          if (previewThumb && data.afbeelding) {
            previewThumb.src   = data.afbeelding;
            previewThumb.style.display = 'block';
          }

          schilderijImg = new Image();
          schilderijImg.onload = () => {
            if (kamerImg) tekenCanvas();
          };
          schilderijImg.onerror = () => console.error('PV: afbeelding kon niet laden:', data.afbeelding);
          schilderijImg.src = data.afbeelding;
        })
        .catch(console.error);
    }

    /* ================================================
       3. Kamerfoto upload
       ================================================ */
    dropzone && dropzone.addEventListener('dragover', e => {
      e.preventDefault();
      dropzone.classList.add('pv-drag-over');
    });
    dropzone && dropzone.addEventListener('dragleave', () => {
      dropzone.classList.remove('pv-drag-over');
    });
    dropzone && dropzone.addEventListener('drop', e => {
      e.preventDefault();
      dropzone.classList.remove('pv-drag-over');
      const file = e.dataTransfer.files[0];
      if (file && file.type.startsWith('image/')) verwerkFoto(file);
    });

    fileInput && fileInput.addEventListener('change', () => {
      if (fileInput.files[0]) verwerkFoto(fileInput.files[0]);
    });

    function verwerkFoto(file) {
      const reader = new FileReader();
      reader.onload = e => {
        kamerImg = new Image();
        kamerImg.onload = () => {
          /* stel canvas in op afmetingen kamer (max 900px breed) */
          const MAX = 900;
          let w = kamerImg.naturalWidth;
          let h = kamerImg.naturalHeight;
          if (w > MAX) { h = Math.round(h * MAX / w); w = MAX; }
          canvas.width  = w;
          canvas.height = h;

          /* beginpositie schilderij: midden van canvas */
          pos.x = w / 2;
          pos.y = h / 2;

          canvasWrap.style.display = 'block';
          tekenCanvas();
        };
        kamerImg.src = e.target.result;
      };
      reader.readAsDataURL(file);
    }

    /* ================================================
       4. Canvas tekenen
       ================================================ */
    function tekenCanvas() {
      if (!kamerImg) return;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(kamerImg, 0, 0, canvas.width, canvas.height);

      if (schilderijImg && schilderijData) {
        tekenSchilderij();
      }
    }

    function tekenSchilderij() {
      /*
       * Werkelijke muurgrootte inschatten:
       * We nemen aan dat een standaard deur ~210 cm hoog is en
       * 10% van de canvashoogte inneemt (grove schatting).
       * Hieruit berekenen we pixels-per-cm.
       * De gebruiker kan dit bijstellen met de slider.
       */
      const PX_PER_CM = (canvas.height * 0.35) / 250;  // 250 cm ≈ plafond hoogte

      const breedtePx = schilderijData.breedte * PX_PER_CM * schaal;
      const hoogtePx  = schilderijData.hoogte  * PX_PER_CM * schaal;

      const x = pos.x - breedtePx / 2;
      const y = pos.y - hoogtePx  / 2;

      /* Subtiele schaduw voor realisme */
      ctx.save();
      ctx.shadowColor   = 'rgba(0,0,0,0.45)';
      ctx.shadowBlur    = 18;
      ctx.shadowOffsetX = 4;
      ctx.shadowOffsetY = 6;
      ctx.drawImage(schilderijImg, x, y, breedtePx, hoogtePx);
      ctx.restore();

      /* Selectierand (dun, zichtbaar voor positionering) */
      ctx.save();
      ctx.strokeStyle = 'rgba(255,255,255,0.8)';
      ctx.lineWidth   = 1.5;
      ctx.setLineDash([6, 4]);
      ctx.strokeRect(x, y, breedtePx, hoogtePx);
      ctx.restore();
    }

    /* ================================================
       5. Muisinteractie: klik = plaatsen, sleep = verplaatsen
       ================================================ */
    function canvasCoord(e) {
      const rect  = canvas.getBoundingClientRect();
      const scaleX = canvas.width  / rect.width;
      const scaleY = canvas.height / rect.height;
      const client = e.touches ? e.touches[0] : e;
      return {
        x: (client.clientX - rect.left) * scaleX,
        y: (client.clientY - rect.top)  * scaleY,
      };
    }

    function isOpSchilderij(cx, cy) {
      if (!schilderijData) return false;
      const PX_PER_CM = (canvas.height * 0.35) / 250;
      const hw = (schilderijData.breedte * PX_PER_CM * schaal) / 2;
      const hh = (schilderijData.hoogte  * PX_PER_CM * schaal) / 2;
      return Math.abs(cx - pos.x) <= hw && Math.abs(cy - pos.y) <= hh;
    }

    canvas.addEventListener('mousedown', e => {
      const c = canvasCoord(e);
      if (isOpSchilderij(c.x, c.y)) {
        sleept  = true;
        offset  = { x: c.x - pos.x, y: c.y - pos.y };
      } else {
        /* klik buiten schilderij = verplaats naar klik */
        pos = c;
        tekenCanvas();
      }
    });

    canvas.addEventListener('mousemove', e => {
      if (!sleept) return;
      const c = canvasCoord(e);
      pos = { x: c.x - offset.x, y: c.y - offset.y };
      tekenCanvas();
    });

    canvas.addEventListener('mouseup',    () => { sleept = false; });
    canvas.addEventListener('mouseleave', () => { sleept = false; });

    /* Touch-events (mobiel) */
    canvas.addEventListener('touchstart', e => {
      e.preventDefault();
      const c = canvasCoord(e);
      if (isOpSchilderij(c.x, c.y)) {
        sleept  = true;
        offset  = { x: c.x - pos.x, y: c.y - offset.y };
      } else {
        pos = c; tekenCanvas();
      }
    }, { passive: false });

    canvas.addEventListener('touchmove', e => {
      e.preventDefault();
      if (!sleept) return;
      const c = canvasCoord(e);
      pos = { x: c.x - offset.x, y: c.y - offset.y };
      tekenCanvas();
    }, { passive: false });

    canvas.addEventListener('touchend', () => { sleept = false; });

    /* ================================================
       6. Schaalslider
       ================================================ */
    scaleSlider && scaleSlider.addEventListener('input', () => {
      schaal = scaleSlider.value / 100;
      scaleVal.textContent = scaleSlider.value + '%';
      tekenCanvas();
    });

    /* ================================================
       7. Knoppen
       ================================================ */
    resetBtn && resetBtn.addEventListener('click', () => {
      kamerImg = null;
      canvasWrap.style.display = 'none';
      if (fileInput) fileInput.value = '';
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    });

    downloadBtn && downloadBtn.addEventListener('click', () => {
      /* Verberg selectierand voor de download */
      const tempDash = ctx.getLineDash();
      ctx.setLineDash([]);
      tekenSchilderij();   // herteken zonder rand

      const link = document.createElement('a');
      link.download = 'mijn-kamer-met-schilderij.png';
      link.href = canvas.toDataURL('image/png');
      link.click();

      ctx.setLineDash(tempDash);
      tekenCanvas();       // herstel met rand
    });
  }
})();
