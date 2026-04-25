/* Tweaks + shared bits for Tinie Bakerie */
(function () {
  // Load persisted tweaks
  const saved = JSON.parse(localStorage.getItem('tinie-tweaks') || '{}');
  const palette = saved.palette || 'peach';
  const density = saved.density || 'aerated';
  document.documentElement.dataset.palette = palette;
  document.documentElement.dataset.density = density;

  // ==== Tweaks panel (edit-mode protocol) ====
  window.addEventListener('DOMContentLoaded', () => {
    const panel = document.createElement('div');
    panel.className = 'tweaks';
    panel.innerHTML = `
      <h4>Tweaks</h4>
      <div class="tweaks-row">
        <label>Palette</label>
        <div class="tweak-options" data-group="palette">
          <button class="tweak-swatch" data-value="peach" style="background: linear-gradient(135deg,#FDF6F1,#E89B7C);" aria-label="Peach"></button>
          <button class="tweak-swatch" data-value="sage" style="background: linear-gradient(135deg,#F5F2EC,#88A17A);" aria-label="Sage"></button>
          <button class="tweak-swatch" data-value="cream" style="background: linear-gradient(135deg,#FAF6EF,#C9974A);" aria-label="Cream"></button>
        </div>
      </div>
      <div class="tweaks-row">
        <label>Densité</label>
        <div class="tweak-options" data-group="density">
          <button class="tweak-pill" data-value="aerated">Aéré</button>
          <button class="tweak-pill" data-value="compact">Compact</button>
        </div>
      </div>
    `;
    document.body.appendChild(panel);

    const sync = () => {
      panel.querySelectorAll('[data-group="palette"] button').forEach(b => {
        b.setAttribute('aria-pressed', b.dataset.value === document.documentElement.dataset.palette);
      });
      panel.querySelectorAll('[data-group="density"] button').forEach(b => {
        b.setAttribute('aria-pressed', b.dataset.value === document.documentElement.dataset.density);
      });
    };
    sync();

    panel.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-value]');
      if (!btn) return;
      const group = btn.parentElement.dataset.group;
      const value = btn.dataset.value;
      document.documentElement.dataset[group] = value;
      const current = JSON.parse(localStorage.getItem('tinie-tweaks') || '{}');
      current[group] = value;
      localStorage.setItem('tinie-tweaks', JSON.stringify(current));
      sync();
    });

    // Edit mode messages
    window.addEventListener('message', (e) => {
      const d = e.data || {};
      if (d.type === '__activate_edit_mode') panel.classList.add('open');
      else if (d.type === '__deactivate_edit_mode') panel.classList.remove('open');
    });
    try { window.parent.postMessage({ type: '__edit_mode_available' }, '*'); } catch (_) {}
  });
})();
