/* Repost-compatible interaction helpers for OgraFi public UI. */
document.addEventListener('click',function(e){
 const trigger=e.target.closest('[data-rp-dropdown]');
 if(trigger){const id=trigger.getAttribute('aria-controls');const menu=id?document.getElementById(id):trigger.nextElementSibling;if(menu){const open=menu.hidden;document.querySelectorAll('[data-rp-menu]').forEach(m=>{m.hidden=true});menu.hidden=!open;trigger.setAttribute('aria-expanded',String(open));}return;}
 if(!e.target.closest('[data-rp-menu]')){document.querySelectorAll('[data-rp-menu]').forEach(m=>m.hidden=true);document.querySelectorAll('[data-rp-dropdown]').forEach(t=>t.setAttribute('aria-expanded','false'));}
});
document.addEventListener('keydown',function(e){if(e.key==='Escape'){document.querySelectorAll('[data-rp-menu]').forEach(m=>m.hidden=true);document.querySelectorAll('[data-rp-dropdown]').forEach(t=>t.setAttribute('aria-expanded','false'));}});
