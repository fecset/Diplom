document.addEventListener('DOMContentLoaded', () => {
    const menu=document.getElementById('menuToggle'), sidebar=document.getElementById('sidebar');
    sidebar?.querySelector('.sidebar__link.active')?.setAttribute('aria-current','page');
    const header=document.querySelector('#app > nav');
    if(header&&sidebar){
        const alignSidebar=()=>document.documentElement.style.setProperty('--app-nav-height',`${header.getBoundingClientRect().height}px`);
        alignSidebar();new ResizeObserver(alignSidebar).observe(header);
    }
    const closeMenu=()=>{sidebar?.classList.remove('active');menu?.classList.remove('active');menu?.setAttribute('aria-expanded','false');document.body.classList.remove('menu-open');};
    menu?.addEventListener('click',()=>{if(!sidebar)return;const open=!sidebar.classList.contains('active');sidebar.classList.toggle('active',open);menu.classList.toggle('active',open);menu.setAttribute('aria-expanded',String(open));document.body.classList.toggle('menu-open',open);});
    document.addEventListener('keydown',e=>{if(e.key==='Escape'){const open=sidebar?.classList.contains('active');closeMenu();if(open)menu.focus();}});
    document.addEventListener('click',e=>{if(sidebar&&!sidebar.contains(e.target)&&!menu.contains(e.target))closeMenu();});
    window.addEventListener('resize',()=>{if(window.innerWidth>900)closeMenu();});
    const attendance=document.getElementById('attendanceDialog');
    document.querySelectorAll('.attendance-edit').forEach(button=>button.addEventListener('click',()=>{
        for(const field of ['user_id','date','status','comment']) attendance.querySelector(`[name="${field}"]`).value=button.dataset[field==='user_id'?'user':field]||'';
        document.getElementById('attendanceDialogContext').textContent=`${button.dataset.name} · ${button.dataset.date.split('-').reverse().join('.')}`;
        attendance.showModal();
    }));
    document.querySelectorAll('[data-close-dialog]').forEach(button=>button.addEventListener('click',()=>button.closest('dialog').close()));
    const trigger=document.getElementById('notificationsIcon'), badge=document.getElementById('notificationsBadge');
    if(!trigger)return;
    document.getElementById('showAllNotifications')?.addEventListener('click',()=>trigger.click());
    const request=async(url, options={})=>{const response=await fetch(url,{credentials:'same-origin',headers:{'Accept':'application/json',...options.headers},...options});if(!response.ok)throw new Error('Не удалось выполнить запрос.');return response.json();};
    const setBadge=count=>{badge.textContent=String(count);badge.classList.toggle('has-notifications',count>0);trigger.setAttribute('aria-label',`Уведомления: ${count} непрочитанных`);};
    const refresh=async()=>setBadge((await request(trigger.dataset.url)).unread_count);
    refresh().catch(()=>{badge.textContent='!';trigger.setAttribute('aria-label','Уведомления: ошибка загрузки');});
    const element=(tag,text,cls)=>{const node=document.createElement(tag);if(text!==undefined)node.textContent=text;if(cls)node.className=cls;return node;};
    trigger.addEventListener('click',async()=>{
        const opener=document.activeElement;
        const dialog=element('dialog',undefined,'review-dialog notification-dialog');dialog.setAttribute('aria-labelledby','notificationDialogTitle');
        const header=element('div',undefined,'ui-dialog-header');const title=element('h2','Уведомления');title.id='notificationDialogTitle';header.append(title);dialog.append(header);
        const close=element('button','×','ui-icon-button');close.type='button';close.setAttribute('aria-label','Закрыть');close.addEventListener('click',()=>dialog.close());header.append(close);
        const toolbar=element('div',undefined,'ui-dialog-toolbar');const hint=element('span','Новые сообщения выделены оранжевой полосой.','ui-description');toolbar.append(hint);const all=element('button','Прочитать все','ui-button ui-button--secondary');all.type='button';toolbar.append(all);dialog.append(toolbar);
        const status=element('p','Загрузка…','ui-dialog-status');status.setAttribute('role','status');dialog.append(status);
        const body=element('div',undefined,'notification-modal__body');dialog.append(body);
        const more=element('button','Показать ещё','ui-button ui-button--secondary');more.type='button';more.hidden=true;dialog.append(more);
        let next=null;
        const mark=async(ids)=>{await request(trigger.dataset.readUrl,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify(ids?{notification_ids:ids}:{})});await refresh();};
        const load=async(url)=>{
            status.textContent='Загрузка…';more.disabled=true;
            try {
                const data=await request(url);setBadge(data.unread_count);
                data.data.forEach(item=>{
                    const article=element('article',undefined,'notification-item'+(item.is_read?' notification-item--read':''));
                    const content=element('div',undefined,'notification-item__content');
                    content.append(element('h3',item.title,'notification-item__title'),element('p',item.message,'notification-item__text'),element('time',new Date(item.created_at).toLocaleString('ru-RU'),'notification-item__date'));
                    article.append(content);
                    if(!item.is_read){const read=element('button','Прочитать','ui-button ui-button--secondary ui-button--compact');read.type='button';read.dataset.markRead='true';read.addEventListener('click',async()=>{read.disabled=true;try{await mark([item.id]);article.classList.add('notification-item--read');read.remove();}catch(e){status.textContent=e.message;read.disabled=false;}});article.append(read);}
                    body.append(article);
                });
                next=data.next_page_url;more.hidden=!next;status.textContent=body.childElementCount?'':'У вас нет уведомлений.';
            }catch(e){status.textContent=e.message;}finally{more.disabled=false;}
        };
        more.addEventListener('click',()=>{if(next)load(next);});
        all.addEventListener('click',async()=>{all.disabled=true;try{await mark();body.querySelectorAll('.notification-item').forEach(n=>n.classList.add('notification-item--read'));body.querySelectorAll('[data-mark-read]').forEach(n=>n.remove());}catch(e){status.textContent=e.message;}finally{all.disabled=false;}});
        dialog.addEventListener('close',()=>{dialog.remove();(opener?.isConnected?opener:trigger).focus();});document.body.append(dialog);dialog.showModal();await load(trigger.dataset.url);
    });
});
