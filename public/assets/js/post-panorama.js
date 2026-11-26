(() => {
    'use strict';
    const radians = Math.PI / 180;
    const half = 140 * radians;
    const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
    // Enhance only wide photos, including existing uploads and AJAX-loaded cards.
    const candidateSelector = 'a[data-ografi-panorama-candidate]';
    const prepare = link => {
        if (link.hasAttribute('data-ografi-panorama')) return;
        const img = link.querySelector('img');
        if (!img || !img.naturalWidth || !img.naturalHeight) return;
        const minimum = link.dataset.ografiPanoramaCandidate === 'explicit' ? 2 : 3;
        if (img.naturalWidth / img.naturalHeight < minimum) return;
        if (typeof HTMLDialogElement === 'undefined' || typeof HTMLDialogElement.prototype.showModal !== 'function') return;
        link.setAttribute('data-ografi-panorama', '');
        link.setAttribute('aria-haspopup', 'dialog');
        link.setAttribute('aria-label', '280° panoramayı keşfet');
        const label = document.createElement('span');
        label.className = 'ografi-panorama-overlay';
        label.setAttribute('aria-hidden', 'true');
        label.innerHTML = '<svg viewBox="0 0 80 48" width="44" height="27" fill="none" aria-hidden="true" focusable="false"><text x="38" y="29" text-anchor="middle" font-size="29" font-weight="750" font-family="Arial,sans-serif" fill="currentColor">280</text><circle cx="63" cy="7" r="2.8" fill="currentColor"/><path d="M15 19C-6 25 1 39 36 40M65 19C88 26 78 40 43 40M31 34l7 6-7 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        link.append(label);
    };
    const scan = root => {
        if (root instanceof Element && root.matches(candidateSelector)) prepare(root);
        if (root.querySelectorAll) root.querySelectorAll(candidateSelector).forEach(prepare);
    };
    document.addEventListener('load', event => {
        if (event.target instanceof HTMLImageElement) {
            const link = event.target.closest(candidateSelector);
            if (link) prepare(link);
        }
    }, true);
    scan(document);
    new MutationObserver(records => {
        for (const record of records) {
            for (const node of record.addedNodes) if (node instanceof Element) scan(node);
        }
    }).observe(document.body, {childList:true, subtree:true});

    const icon = name => {
        const paths = {
            expand:'<path d="M8 3H3v5m13-5h5v5M3 16v5h5m13-5v5h-5"/>',
            close:'<path d="m6 6 12 12M18 6 6 18"/>',
            minus:'<path d="M5 12h14"/>', plus:'<path d="M5 12h14m-7-7v14"/>',
            reset:'<path d="M3 12a9 9 0 1 0 3-7M3 4v6h6"/>',
            overview:'<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 15 5-5 4 4 3-3 6 6"/>'
        };
        return '<svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'+paths[name]+'</svg>';
    };
    const button = (name,label) => '<button type="button" data-'+name+' aria-label="'+label+'" title="'+label+'">'+icon(name)+'</button>';

    // Draw the original-resolution image as a cylindrical perspective in one GPU pass.
    // No readback, JPEG recompression, or low-resolution intermediate image.
    function gpuRenderer(canvas, image) {
        const gl = canvas.getContext('webgl', {alpha:false, antialias:false, preserveDrawingBuffer:false});
        if (!gl) return null;
        const compile = (type, source) => {
            const shader = gl.createShader(type);
            gl.shaderSource(shader, source); gl.compileShader(shader);
            if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) throw Error(gl.getShaderInfoLog(shader));
            return shader;
        };
        const vs = compile(gl.VERTEX_SHADER, 'attribute vec2 a; void main(){gl_Position=vec4(a,0.0,1.0);}');
        const precision = gl.getShaderPrecisionFormat(gl.FRAGMENT_SHADER, gl.HIGH_FLOAT)?.precision ? 'highp' : 'mediump';
        const fs = compile(gl.FRAGMENT_SHADER, 'precision '+precision+' float; uniform sampler2D photo; uniform vec2 size; uniform float yaw; uniform float fov; uniform float ratio; void main(){float focal=size.x/(2.0*tan(fov/2.0)); float a=atan((gl_FragCoord.x-size.x/2.0)/focal); float u=(yaw+a)/4.8869219056+0.5; float v=0.5-(gl_FragCoord.y-size.y/2.0)/focal*cos(a)*ratio/4.8869219056; if(u<0.0||u>1.0||v<0.0||v>1.0){gl_FragColor=vec4(0.035,0.045,0.06,1.0);}else{gl_FragColor=texture2D(photo,vec2(u,v));}}');
        const program = gl.createProgram();
        gl.attachShader(program, vs); gl.attachShader(program, fs); gl.linkProgram(program);
        if (!gl.getProgramParameter(program, gl.LINK_STATUS)) throw Error(gl.getProgramInfoLog(program));
        gl.useProgram(program);
        const buffer = gl.createBuffer(); gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
        gl.bufferData(gl.ARRAY_BUFFER,new Float32Array([-1,-1,1,-1,-1,1,1,1]),gl.STATIC_DRAW);
        const a = gl.getAttribLocation(program,'a'); gl.enableVertexAttribArray(a); gl.vertexAttribPointer(a,2,gl.FLOAT,false,0,0);
        const texture=gl.createTexture(); gl.bindTexture(gl.TEXTURE_2D,texture);
        gl.texParameteri(gl.TEXTURE_2D,gl.TEXTURE_WRAP_S,gl.CLAMP_TO_EDGE); gl.texParameteri(gl.TEXTURE_2D,gl.TEXTURE_WRAP_T,gl.CLAMP_TO_EDGE);
        gl.texParameteri(gl.TEXTURE_2D,gl.TEXTURE_MIN_FILTER,gl.LINEAR); gl.texParameteri(gl.TEXTURE_2D,gl.TEXTURE_MAG_FILTER,gl.LINEAR);
        if (Math.max(image.naturalWidth,image.naturalHeight)>gl.getParameter(gl.MAX_TEXTURE_SIZE)) throw Error('Texture size limit');
        gl.texImage2D(gl.TEXTURE_2D,0,gl.RGBA,gl.RGBA,gl.UNSIGNED_BYTE,image);
        if(gl.getError()!==gl.NO_ERROR) throw Error('Texture upload failed');
        const uniforms = Object.fromEntries(['size','yaw','fov','ratio'].map(key=>[key,gl.getUniformLocation(program,key)]));
        gl.uniform1f(uniforms.ratio,image.naturalWidth/image.naturalHeight);
        return {
            draw(w,h,yaw,fov){gl.viewport(0,0,w,h);gl.uniform2f(uniforms.size,w,h);gl.uniform1f(uniforms.yaw,yaw);gl.uniform1f(uniforms.fov,fov);gl.drawArrays(gl.TRIANGLE_STRIP,0,4);},
            dispose(){gl.deleteTexture(texture);gl.deleteBuffer(buffer);gl.deleteProgram(program);gl.deleteShader(vs);gl.deleteShader(fs);}
        };
    }
    function cpuRenderer(canvas,image){
        const ctx=canvas.getContext('2d',{alpha:false});
        if(!ctx) return null;
        return {draw(w,h,yaw,fov){
            const focal=w/(2*Math.tan(fov/2)), ppr=image.naturalWidth/(2*half);
            ctx.fillStyle='#090c10';ctx.fillRect(0,0,w,h);
            ctx.imageSmoothingEnabled=true;ctx.imageSmoothingQuality='high';
            for(let x=0;x<w;x++){
                const a=Math.atan((x-w/2)/focal), b=Math.atan((x+1-w/2)/focal);
                const sx=clamp((yaw+a+half)*ppr,0,image.naturalWidth-1);
                const sw=Math.min(image.naturalWidth-sx,Math.max(.001,(b-a)*ppr));
                const projected=image.naturalHeight*focal/(ppr*Math.cos((a+b)/2));
                ctx.drawImage(image,sx,0,sw,image.naturalHeight,x,(h-projected)/2,1,projected);
            }
        },dispose(){}};
    }

    document.addEventListener('click', event => {
        const trigger=event.target instanceof Element && event.target.closest('a[data-ografi-panorama]');
        if(!trigger||event.defaultPrevented||event.ctrlKey||event.metaKey||event.shiftKey||event.altKey)return;
        const dialog=document.createElement('dialog');
        if(typeof dialog.showModal!=='function')return;
        event.preventDefault();event.stopImmediatePropagation();
        dialog.className='ografi-panorama-dialog';
        dialog.setAttribute('aria-label','Panorama görüntüleyici');
        dialog.innerHTML='<div class="ografi-pano-header"><div class="ografi-pano-heading"><span class="ografi-pano-brand">280°</span><strong>Panorama</strong></div><div class="ografi-pano-tools">'+button('expand','Tam ekran')+button('close','Kapat')+'</div></div><div class="ografi-pano-stage" tabindex="0" aria-label="Fotoğrafı sürükleyin veya ok tuşlarını kullanın"><img class="ografi-pano-original" alt="Panoramik fotoğraf" draggable="false" hidden><canvas aria-hidden="true" hidden></canvas><div class="ografi-pano-status" role="status">Fotoğraf yükleniyor…</div><span class="ografi-pano-gesture" hidden>↔ Sürükleyerek keşfet</span></div><div class="ografi-pano-footer" hidden><div class="ografi-pano-modes" role="group" aria-label="Görünüm"><button type="button" data-mode="natural" aria-pressed="true">Doğal</button><button type="button" data-mode="perspective" aria-pressed="false">3B</button><button type="button" data-mode="overview" aria-pressed="false">Tümü</button></div><div class="ografi-pano-map" role="group" aria-label="Panorama önizlemesi"><img alt="" draggable="false"><div class="ografi-pano-window"></div><input type="range" min="0" max="1000" value="500" aria-label="Fotoğrafta yatay gezin"></div><div class="ografi-pano-zoom">'+button('minus','Uzaklaştır')+button('plus','Yakınlaştır')+button('reset','Sıfırla')+'</div></div>';
        document.body.append(dialog);
        const stage=dialog.querySelector('.ografi-pano-stage'), photo=stage.querySelector('img'), canvas=stage.querySelector('canvas');
        const footer=dialog.querySelector('.ografi-pano-footer'), status=dialog.querySelector('[role="status"]'), hint=dialog.querySelector('.ografi-pano-gesture');
        const map=dialog.querySelector('.ografi-pano-map'), mapImage=map.querySelector('img'), mapWindow=map.querySelector('.ografi-pano-window'), range=map.querySelector('input');
        const modes=[...dialog.querySelectorAll('[data-mode]')];
        let mode='natural',ready=false,renderer=null,frame=0,hintTimer=0,renderWidth=0,renderHeight=0;
        let zoom=1,centerX=.5,centerY=.5,yaw=0,fov=85*radians;
        const pointers=new Map();
        const dimensions=()=>{
            const w=stage.clientWidth,h=stage.clientHeight,iw=photo.naturalWidth,ih=photo.naturalHeight;
            const fit=Math.min(w/iw,h/ih),fill=Math.max(w/iw,h/ih);
            // Do not force the photo to fill an oversized viewport; preserve source aspect.
            const scale=mode==='overview'?fit:Math.max(fit,Math.min(fill,Math.max(1,fit)))*zoom;
            return {w,h,iw,ih,scale,dw:iw*scale,dh:ih*scale};
        };
        const boundCenter=(value,view,drawn)=>drawn<=view?.5:clamp(value,view/(2*drawn),1-view/(2*drawn));
        const draw=()=>{
            frame=0;if(!ready||!dialog.open)return;
            const d=dimensions();if(!d.w||!d.h)return;
            let visible,left;
            if(mode==='perspective'&&renderer){
                const limit=half-fov/2;yaw=clamp(yaw,-limit,limit);
                const scale=Math.min(devicePixelRatio||1,Math.sqrt(3200000/(d.w*d.h)));
                const w=Math.max(1,Math.round(d.w*scale)),h=Math.max(1,Math.round(d.h*scale));
                if(renderWidth!==w||renderHeight!==h){canvas.width=w;canvas.height=h;renderWidth=w;renderHeight=h;}
                renderer.draw(w,h,yaw,fov);
                visible=fov/(2*half);left=(yaw+half-fov/2)/(2*half);
            }else{
                centerX=boundCenter(centerX,d.w,d.dw);centerY=boundCenter(centerY,d.h,d.dh);
                const x=(d.w/2)-centerX*d.dw,y=(d.h/2)-centerY*d.dh;
                photo.style.width=d.dw+'px';photo.style.height=d.dh+'px';
                photo.style.transform='translate3d('+x+'px,'+y+'px,0)';
                visible=Math.min(1,d.w/d.dw);left=clamp(centerX-visible/2,0,1-visible);
            }
            mapWindow.style.left=(left*100)+'%';mapWindow.style.width=(visible*100)+'%';
            range.value=String(Math.round((visible>=.999?.5:left/(1-visible))*1000));
            range.disabled=visible>=.999;
            dialog.querySelector('[data-minus]').disabled=mode==='overview'||(mode==='perspective'?fov>=105*radians:zoom<=1.001);
            dialog.querySelector('[data-plus]').disabled=mode==='perspective'?fov<=45*radians:zoom>=4;
        };
        const render=()=>{if(!frame)frame=requestAnimationFrame(draw);};
        const setMode=next=>{
            if(!ready)return;
            if(next==='perspective'&&!renderer){
                try{renderer=gpuRenderer(canvas,photo);}catch(_){renderer=null;}
                if(!renderer){modes.find(b=>b.dataset.mode==='perspective').disabled=true;return;}
            }
            mode=next;photo.hidden=mode==='perspective';canvas.hidden=mode!=='perspective';
            if(mode==='overview'){centerX=.5;centerY=.5;zoom=1;}
            modes.forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.mode===mode)));
            render();
        };
        const changeZoom=(factor,atX=.5,atY=.5)=>{
            if(mode==='overview')setMode('natural');
            if(mode==='perspective'){fov=clamp(fov/factor,45*radians,105*radians);render();return;}
            const before=dimensions();
            const next=clamp(zoom*factor,1,4);if(next===zoom)return;
            zoom=next;const after=dimensions();
            centerX+=(atX-.5)*before.w*(1/before.dw-1/after.dw);
            centerY+=(atY-.5)*before.h*(1/before.dh-1/after.dh);render();
        };
        const reset=()=>{centerX=.5;centerY=.5;zoom=1;yaw=0;fov=85*radians;render();};
        const pan=(dx,dy)=>{
            if(mode==='overview')return;
            if(mode==='perspective'){yaw-=dx*fov/Math.max(1,stage.clientWidth);}
            else{const d=dimensions();centerX-=dx/d.dw;centerY-=dy/d.dh;}
            render();
        };
        stage.addEventListener('pointerdown',e=>{
            if(!ready||e.button!==0)return;
            pointers.set(e.pointerId,{x:e.clientX,y:e.clientY});
            stage.setPointerCapture(e.pointerId);hint.hidden=true;
        });
        stage.addEventListener('pointermove',e=>{
            const prev=pointers.get(e.pointerId);if(!prev)return;
            const others=[...pointers.entries()].filter(([id])=>id!==e.pointerId);
            const now={x:e.clientX,y:e.clientY};
            if(others.length){
                const p=others[0][1],oldDist=Math.hypot(prev.x-p.x,prev.y-p.y),newDist=Math.hypot(now.x-p.x,now.y-p.y),rect=stage.getBoundingClientRect();
                if(oldDist>8&&newDist>8)changeZoom(newDist/oldDist,((now.x+p.x)/2-rect.left)/rect.width,((now.y+p.y)/2-rect.top)/rect.height);
                pan((now.x-prev.x)/2,(now.y-prev.y)/2);
            }else{pan(now.x-prev.x,now.y-prev.y);}
            pointers.set(e.pointerId,now);
        });
        ['pointerup','pointercancel','lostpointercapture'].forEach(type=>stage.addEventListener(type,e=>pointers.delete(e.pointerId)));
        stage.addEventListener('wheel',e=>{if(!ready)return;e.preventDefault();const r=stage.getBoundingClientRect();changeZoom(e.deltaY<0?1.12:1/1.12,(e.clientX-r.left)/r.width,(e.clientY-r.top)/r.height);},{passive:false});
        stage.addEventListener('keydown',e=>{
            if(!ready||!['ArrowLeft','ArrowRight','ArrowUp','ArrowDown','+','-','Home'].includes(e.key))return;
            e.preventDefault();
            if(e.key==='ArrowLeft')pan(70,0);if(e.key==='ArrowRight')pan(-70,0);
            if(e.key==='ArrowUp')pan(0,70);if(e.key==='ArrowDown')pan(0,-70);
            if(e.key==='+')changeZoom(1.25);if(e.key==='-')changeZoom(.8);if(e.key==='Home')reset();
        });
        range.addEventListener('input',()=>{
            const v=Number(range.value)/1000;
            if(mode==='perspective'){const limit=half-fov/2;yaw=(v*2-1)*limit;}
            else{const d=dimensions(),margin=Math.min(.5,d.w/(2*d.dw));centerX=margin+v*(1-2*margin);}
            render();
        });
        modes.forEach(b=>b.addEventListener('click',()=>setMode(b.dataset.mode)));
        dialog.querySelector('[data-minus]').addEventListener('click',()=>changeZoom(.8));
        dialog.querySelector('[data-plus]').addEventListener('click',()=>changeZoom(1.25));
        dialog.querySelector('[data-reset]').addEventListener('click',reset);
        dialog.querySelector('[data-expand]').addEventListener('click',e=>{const expanded=dialog.classList.toggle('is-expanded');e.currentTarget.setAttribute('aria-pressed',String(expanded));render();});
        dialog.querySelector('[data-close]').addEventListener('click',()=>dialog.close());
        canvas.addEventListener('webglcontextlost',e=>{e.preventDefault();renderer=null;setMode('natural');modes.find(b=>b.dataset.mode==='perspective').disabled=true;});
        const resize=typeof ResizeObserver==='function'?new ResizeObserver(render):null;
        const oldOverflow=document.body.style.overflow;
        dialog.addEventListener('close',()=>{
            ready=false;photo.onload=photo.onerror=null;clearTimeout(hintTimer);if(frame)cancelAnimationFrame(frame);
            resize?.disconnect();window.removeEventListener('resize',render);renderer?.dispose();document.body.style.overflow=oldOverflow;
            dialog.remove();if(trigger.isConnected)trigger.focus();
        },{once:true});
        try{dialog.showModal();}catch(_){dialog.remove();location.href=trigger.href;return;}
        document.body.style.overflow='hidden';
        photo.onload=()=>{
            if(!dialog.open)return;
            ready=true;photo.hidden=false;mapImage.src=photo.src;status.hidden=true;footer.hidden=false;
            hint.hidden=false;hintTimer=setTimeout(()=>{hint.hidden=true;},2500);
            resize?.observe(stage);window.addEventListener('resize',render);render();
        };
        photo.onerror=()=>{status.textContent='Fotoğraf yüklenemedi.';};
        photo.src=trigger.href;
    },true);
})();
