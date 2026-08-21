/*
 * Quark Photofolio — fullscreen photo viewer.
 */
(function () {
  'use strict';

  var viewer = document.getElementById('photo-viewer');
  if (!viewer) return;

  var viewerImage = viewer.querySelector('.photo-viewer__image');
  var viewerCaption = viewer.querySelector('.photo-viewer__caption');
  var closeButton = viewer.querySelector('[data-viewer-close]');
  var lastTrigger = null;

  function openViewer(trigger) {
    var full = trigger.getAttribute('data-full');
    var title = trigger.getAttribute('data-title') || '';
    if (!full) return;

    lastTrigger = trigger;
    viewerImage.src = full;
    viewerImage.alt = title;
    viewerCaption.textContent = title;
    viewerCaption.hidden = !title;

    viewer.hidden = false;
    document.body.classList.add('viewer-open');
    closeButton.focus();

    document.addEventListener('keydown', onKeydown);
  }

  function closeViewer() {
    viewer.hidden = true;
    document.body.classList.remove('viewer-open');
    viewerImage.src = '';
    document.removeEventListener('keydown', onKeydown);
    if (lastTrigger) lastTrigger.focus();
  }

  function onKeydown(e) {
    if (e.key === 'Escape') closeViewer();
  }

  document.querySelectorAll('.photo-trigger').forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      openViewer(trigger);
    });
  });

  closeButton.addEventListener('click', closeViewer);

  // Click anywhere outside the image itself (the backdrop) closes the viewer.
  viewer.addEventListener('click', function (e) {
    if (e.target === viewer) closeViewer();
  });
})();
