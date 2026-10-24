document.addEventListener('click', function(e){
  const confirmEl = e.target.closest('[data-confirm]');
  if (confirmEl && !confirm(confirmEl.getAttribute('data-confirm') || 'آیا مطمئن هستید؟')) e.preventDefault();
});

document.addEventListener('click', function(e){ if(e.target.closest('[data-menu-toggle]')) document.body.classList.toggle('menu-open'); });
