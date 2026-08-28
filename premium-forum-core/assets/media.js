(function () {
  'use strict';
  const config = window.pfcMedia || { maxFiles: 3, maxBytes: 5242880 };
  document.querySelectorAll('input[type="file"][name$="_images[]"]').forEach(function (input) {
    input.addEventListener('change', function () {
      const files = Array.from(input.files || []);
      const parent = input.closest('.atelier-media-upload');
      let message = parent && parent.querySelector('.pfc-media-client-message');
      if (!message && parent) { message = document.createElement('p'); message.className = 'pfc-media-client-message'; message.setAttribute('role', 'status'); parent.appendChild(message); }
      const invalid = files.filter(function (file) { return file.size > config.maxBytes || !/^image\/(jpeg|png|webp)$/.test(file.type); });
      if (message) message.textContent = files.length > config.maxFiles ? 'Sélectionnez 3 images maximum.' : (invalid.length ? 'Une ou plusieurs images ne respectent pas le format ou la taille autorisée.' : files.length + ' image(s) sélectionnée(s).');
      if (files.length > config.maxFiles || invalid.length) input.value = '';
    });
  });
  document.querySelectorAll('.pfc-media-carousel').forEach(function (carousel) {
    const slides = Array.from(carousel.querySelectorAll('.pfc-media-slide'));
    if (slides.length < 2) return;
    let index = 0;
    const status = carousel.querySelector('.pfc-media-status');
    const show = function (next) { index = (next + slides.length) % slides.length; slides.forEach(function (slide, i) { slide.hidden = i !== index; }); if (status) status.textContent = (index + 1) + ' sur ' + slides.length; };
    const prev = carousel.querySelector('.pfc-media-prev'); const next = carousel.querySelector('.pfc-media-next');
    if (prev) prev.addEventListener('click', function () { show(index - 1); });
    if (next) next.addEventListener('click', function () { show(index + 1); });
    carousel.addEventListener('keydown', function (event) { if (event.key === 'ArrowLeft') { event.preventDefault(); show(index - 1); } if (event.key === 'ArrowRight') { event.preventDefault(); show(index + 1); } });
    show(0);
  });
}());
