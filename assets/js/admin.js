(function(){
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.ai-generate-alt');
    if(!btn) return;
    e.preventDefault();

    const id = btn.dataset.attachmentId;
    if(!id) return;
    const statusEl = btn.parentElement.querySelector('.ai-alt-status');
    if(statusEl) statusEl.textContent = AiAltText.i18n.generating;
    btn.disabled = true;

    const formData = new FormData();
    formData.append('action', AiAltText.ajaxAction);
    formData.append('nonce', AiAltText.nonce);
    formData.append('attachment_id', id);

    fetch(AiAltText.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    }).then(r => r.json())
      .then(res => {
        if(res.success){
          if(statusEl) statusEl.textContent = '✔';
          const alt = res.data.alt;
          const selector = `input[name="attachments[${id}][_wp_attachment_image_alt]"]`;
          const altInput1 = document.querySelector(selector);
          const altInput2 = document.getElementById('_wp_attachment_image_alt');
          if(altInput1) altInput1.value = alt;
          if(altInput2) altInput2.value = alt;
          const row = document.getElementById('post-'+id);
          if(row){
            const altCell = row.querySelector('.column-alt');
            if(altCell) altCell.textContent = alt;
          }
          setTimeout(()=>location.reload(), 500);
        }else{
          if(statusEl) statusEl.textContent = AiAltText.i18n.error;
          btn.disabled = false;
        }
      })
      .catch(() => {
        if(statusEl) statusEl.textContent = AiAltText.i18n.error;
        btn.disabled = false;
      });
  });
})(); 