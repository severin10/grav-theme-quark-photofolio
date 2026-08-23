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
  var prevButton = viewer.querySelector('[data-viewer-prev]');
  var nextButton = viewer.querySelector('[data-viewer-next]');
  var lastTrigger = null;

  var triggers = Array.prototype.slice.call(document.querySelectorAll('.photo-trigger'));
  var currentIndex = -1;

  function showAt(index) {
    if (!triggers.length) return;
    currentIndex = (index + triggers.length) % triggers.length;
    var trigger = triggers[currentIndex];

    var full = trigger.getAttribute('data-full');
    var title = trigger.getAttribute('data-title') || '';
    if (!full) return;

    lastTrigger = trigger;
    viewerImage.src = full;
    viewerImage.alt = title;
    viewerCaption.textContent = title;
    viewerCaption.hidden = !title;
  }

  function openViewer(trigger) {
    showAt(triggers.indexOf(trigger));
    if (currentIndex === -1) return;

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
    else if (e.key === 'ArrowRight') showAt(currentIndex + 1);
    else if (e.key === 'ArrowLeft') showAt(currentIndex - 1);
  }

  triggers.forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      openViewer(trigger);
    });
  });

  closeButton.addEventListener('click', closeViewer);

  if (triggers.length > 1) {
    prevButton.addEventListener('click', function () { showAt(currentIndex - 1); });
    nextButton.addEventListener('click', function () { showAt(currentIndex + 1); });
  } else {
    prevButton.hidden = true;
    nextButton.hidden = true;
  }

  // Click anywhere outside the image itself (the backdrop) closes the viewer.
  viewer.addEventListener('click', function (e) {
    if (e.target === viewer) closeViewer();
  });
})();
