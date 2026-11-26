(() => {
    'use strict';
    const radians = Math.PI / 180;
    const half = 140 * radians;
    const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
    // Enhance only wide photos, including existing uploads and AJAX-loaded cards.
    const candidateSelector = 'a[data-ografi-panorama-candidate]';
    const panoramaIcon = '<svg viewBox="0 0 80 48" width="25" height="15" fill="none" aria-hidden="true" focusable="false"><text x="38" y="29" text-anchor="middle" font-size="29" font-weight="750" font-family="Arial,sans-serif" fill="currentColor">280</text><circle cx="63" cy="7" r="2.8" fill="currentColor"/><path d="M15 19C-6 25 1 39 36 40M65 19C88 26 78 40 43 40M31 34l7 6-7 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    const isSphere = value => /(?:^|\/)ografi-360-[^/?#]+\.(?:jpe?g|png|webp)(?:[?#]|$)/i.test(value || '');
    const sphereIcon = panoramaIcon.replace('>280</text>', '>360</text>');
    const eligible = (link, image = link?.querySelector('img')) => {
        if (!(link instanceof HTMLAnchorElement) || !link.matches(candidateSelector)) return false;
        if (!image?.naturalWidth || !image.naturalHeight) return false;
        return isSphere(link.href) ? Math.abs(image.naturalWidth/image.naturalHeight-2)<=.035 : image.naturalWidth / image.naturalHeight >= (link.dataset.ografiPanoramaCandidate === 'explicit' ? 2 : 3);
    };
    const prepare = link => {
        if (link.hasAttribute('data-ografi-panorama')) return;
        if (!eligible(link)) return;
        if (typeof HTMLDialogElement === 'undefined' || typeof HTMLDialogElement.prototype.showModal !== 'function') return;
        link.setAttribute('data-ografi-panorama', '');
        link.setAttribute('aria-haspopup', 'dialog');
        link.setAttribute('aria-label', 'Panoramik fotoğrafı büyüt');
        const label = document.createElement('span');
        label.className = 'ografi-panorama-overlay';
        label.setAttribute('aria-hidden', 'true');
        label.innerHTML = isSphere(link.href) ? sphereIcon : panoramaIcon;
        link.append(label);
    };
    // Use the same intrinsic aspect-ratio test in the composer and public viewer.
    // Works with object URLs, pasted/shared uploads, and dynamically inserted editor blocks.
    const previewSelector = '.ografi-pano-cover-tile, .post-composer-gallery-item, .post-existing-image-tile, .editor-inline-image-tile, .post-gallery-tile';
    const previewImageSelector = previewSelector.split(', ').map(selector => selector + ' > img').join(', ');
    const preparePreview = img => {
        if (!(img instanceof HTMLImageElement) || !img.matches(previewImageSelector)) return;
        if (!img.naturalWidth || !img.naturalHeight) return;
        const tile = img.parentElement;
        const badge = tile.querySelector(':scope > .ografi-pano-preview-badge');
        const spherical = img.dataset.ografiPhoto360 === '1' || isSphere(img.src);
        if (!spherical && img.naturalWidth / img.naturalHeight < 3) {
            badge?.remove();
            return;
        }
        if (badge) return;
        const label = document.createElement('span');
        label.className = 'ografi-pano-preview-badge';
        label.setAttribute('role', 'img');
        label.setAttribute('aria-label', 'Panoramik fotoğraf algılandı');
        label.title = 'Panoramik fotoğraf algılandı';
        label.innerHTML = spherical ? sphereIcon : panoramaIcon;
        tile.append(label);
    };
    const scan = root => {
        if (root instanceof Element && root.matches(candidateSelector)) prepare(root);
        if (root instanceof HTMLImageElement) preparePreview(root);
        if (root.querySelectorAll) {
            root.querySelectorAll(candidateSelector).forEach(prepare);
            root.querySelectorAll(previewImageSelector).forEach(preparePreview);
        }
    };
    document.addEventListener('load', event => {
        if (event.target instanceof HTMLImageElement) {
            const link = event.target.closest(candidateSelector);
            if (link) prepare(link);
            preparePreview(event.target);
        }
    }, true);
    scan(document);
    new MutationObserver(records => {
        for (const record of records) {
            for (const node of record.addedNodes) if (node instanceof Element) scan(node);
        }
    }).observe(document.body, {childList:true, subtree:true});

    // Draw the original-resolution image as a cylindrical perspective in one GPU pass.
    // No readback, JPEG recompression, or low-resolution intermediate image.
    function gpuRenderer(canvas, image, spherical=false) {
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
        const sphereFs = spherical ? compile(gl.FRAGMENT_SHADER, 'precision '+precision+' float; uniform sampler2D photo; uniform vec2 size; uniform float yaw; uniform float fov; uniform float pitch; void main(){float focal=size.x/(2.0*tan(fov/2.0));vec3 d=normalize(vec3((gl_FragCoord.x-size.x/2.0)/focal,(gl_FragCoord.y-size.y/2.0)/focal,1.0));d=vec3(d.x,d.y*cos(pitch)+d.z*sin(pitch),d.z*cos(pitch)-d.y*sin(pitch));float u=fract((atan(d.x,d.z)+yaw)/6.28318530718+0.5);float v=0.5-asin(clamp(d.y,-1.0,1.0))/3.14159265359;gl_FragColor=texture2D(photo,vec2(u,v));}') : null;
        const program = gl.createProgram();
        gl.attachShader(program, vs); gl.attachShader(program, sphereFs||fs); gl.linkProgram(program);
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
        const uniforms = Object.fromEntries(['size','yaw','fov','ratio','pitch'].map(key=>[key,gl.getUniformLocation(program,key)]));
        gl.uniform1f(uniforms.ratio,image.naturalWidth/image.naturalHeight);
        return {
            draw(w,h,yaw,fov,pitch=0){gl.viewport(0,0,w,h);gl.uniform2f(uniforms.size,w,h);gl.uniform1f(uniforms.yaw,yaw);gl.uniform1f(uniforms.fov,fov);if(uniforms.pitch)gl.uniform1f(uniforms.pitch,pitch);gl.drawArrays(gl.TRIANGLE_STRIP,0,4);},
            dispose(){gl.deleteTexture(texture);gl.deleteBuffer(buffer);gl.deleteProgram(program);gl.deleteShader(vs);gl.deleteShader(fs);if(sphereFs)gl.deleteShader(sphereFs);}
        };
    }
    const sessions = new WeakMap();
    const observed = new WeakSet();
    function stopPanorama(instance) {
        const session=sessions.get(instance);
        if (!session) return;
        sessions.delete(instance);
        session.dispose();
    }

    function openPanorama(instance, slide, control) {
        const container=instance.getContainer();
        const carousel=container?.querySelector('.fancybox__carousel');
        const viewport=carousel?.querySelector('.fancybox__viewport');
        const trigger=slide?.triggerEl;
        if (!carousel||!viewport||!eligible(trigger,trigger?.querySelector('img'))&&!eligible(trigger,slide?.el?.querySelector('img'))) return;
        const spherical=isSphere(trigger.href);
        const stage=document.createElement('div');
        stage.className='ografi-pano-inline';
        stage.setAttribute('role','region');
        stage.setAttribute('aria-label',spherical?'360° küresel panorama':'280° panorama');
        stage.setAttribute('data-draggable','');
        stage.tabIndex=0;
        stage.innerHTML='<div class="ografi-pano-surface"><canvas aria-hidden="true" hidden></canvas><img alt="Panoramik fotoğraf" draggable="false"><span class="ografi-pano-loading" role="status">Yükleniyor…</span></div>';
        carousel.append(stage);
        const surface=stage.querySelector('.ografi-pano-surface'), canvas=stage.querySelector('canvas'), photo=stage.querySelector('img'), status=stage.querySelector('[role="status"]');
        const wasInert=viewport.inert;
        let renderer=null,ready=false,disposed=false,frame=0,yaw=0,pitch=0,fov=(spherical?90:110)*radians,zoom=1,x=.5,y=.5;
        const pointers=new Map();
        const listeners=[];
        const on=(target,name,fn,options)=>{target.addEventListener(name,fn,options);listeners.push(()=>target.removeEventListener(name,fn,options));};
        const dimensions=()=>{
            const w=surface.clientWidth,h=surface.clientHeight,iw=photo.naturalWidth,ih=photo.naturalHeight;
            const fit=Math.min(w/iw,h/ih),fill=Math.max(w/iw,h/ih);
            const scale=Math.max(fit,Math.min(fill,Math.max(1,fit)))*zoom;
            return {w,h,dw:iw*scale,dh:ih*scale};
        };
        const bound=(v,view,size)=>size<=view?.5:clamp(v,view/(2*size),1-view/(2*size));
        function draw(){
            frame=0;
            if(!ready||disposed)return;
            const d=dimensions();
            if(!d.w||!d.h)return;
            if(renderer){
                const scale=Math.min(window.devicePixelRatio||1,Math.sqrt(3600000/(d.w*d.h)));
                const w=Math.max(1,Math.round(d.w*scale)),h=Math.max(1,Math.round(d.h*scale));
                if(canvas.width!==w)canvas.width=w;
                if(canvas.height!==h)canvas.height=h;
                if(spherical){yaw=((yaw+Math.PI)%(2*Math.PI)+2*Math.PI)%(2*Math.PI)-Math.PI;pitch=clamp(pitch,-Math.PI/2,Math.PI/2);}
                else yaw=clamp(yaw,-half+fov/2,half-fov/2);
                renderer.draw(w,h,yaw,fov,pitch);
            }else{
                x=bound(x,d.w,d.dw);y=bound(y,d.h,d.dh);
                photo.style.width=d.dw+'px';photo.style.height=d.dh+'px';
                photo.style.transform='translate3d('+(d.w/2-x*d.dw)+'px,'+(d.h/2-y*d.dh)+'px,0)';
            }
        }
        const render=()=>{if(!disposed&&!frame)frame=requestAnimationFrame(draw);};
        function changeZoom(factor){
            if(!ready)return;
            if(renderer)fov=clamp(fov/factor,45*radians,130*radians);
            else zoom=clamp(zoom*factor,1,4);
            render();
        }
        function pan(dx,dy){
            if(!ready)return;
            if(renderer){yaw-=dx*fov/Math.max(1,surface.clientWidth);if(spherical)pitch+=dy*fov/Math.max(1,surface.clientWidth);}
            else {const d=dimensions();x-=dx/d.dw;y-=dy/d.dh;}
            render();
        }
        function reset(){yaw=0;pitch=0;fov=(spherical?90:110)*radians;zoom=1;x=.5;y=.5;render();}
        on(surface,'pointerdown',e=>{
            if(!ready||e.button!==0)return;
            e.stopPropagation();pointers.set(e.pointerId,{x:e.clientX,y:e.clientY});
            surface.setPointerCapture(e.pointerId);
        });
        on(surface,'pointermove',e=>{
            const prev=pointers.get(e.pointerId);if(!prev)return;
            e.stopPropagation();
            const next={x:e.clientX,y:e.clientY};
            const other=[...pointers.entries()].find(([id])=>id!==e.pointerId)?.[1];
            if(other){
                const oldDist=Math.hypot(prev.x-other.x,prev.y-other.y),newDist=Math.hypot(next.x-other.x,next.y-other.y);
                if(oldDist>8&&newDist>8)changeZoom(newDist/oldDist);
                pan((next.x-prev.x)/2,(next.y-prev.y)/2);
            }else pan(next.x-prev.x,next.y-prev.y);
            pointers.set(e.pointerId,next);
        });
        for(const type of ['pointerup','pointercancel','lostpointercapture'])on(surface,type,e=>pointers.delete(e.pointerId));
        // Run before Fancybox's own wheel / keyboard / zoom handlers, but only
        // while this instance is in panorama mode.
        on(document,'wheel',e=>{
            if(!surface.contains(e.target))return;
            e.preventDefault();e.stopImmediatePropagation();changeZoom(e.deltaY<0?1.12:1/1.12);
        },{capture:true,passive:false});
        on(document,'keydown',e=>{
            if(!container.contains(e.target)||!['ArrowLeft','ArrowRight','ArrowUp','ArrowDown','+','-','Home'].includes(e.key))return;
            e.preventDefault();e.stopImmediatePropagation();
            if(e.key==='ArrowLeft')pan(75,0);if(e.key==='ArrowRight')pan(-75,0);
            if(e.key==='ArrowUp')pan(0,75);if(e.key==='ArrowDown')pan(0,-75);
            if(e.key==='+')changeZoom(1.25);if(e.key==='-')changeZoom(.8);if(e.key==='Home')reset();
        },true);
        on(document,'click',e=>{
            const action=e.target instanceof Element&&e.target.closest('.f-carousel__toolbar [data-panzoom-action]');
            if(!action||!container.contains(action))return;
            e.preventDefault();e.stopImmediatePropagation();
            const name=action.dataset.panzoomAction;
            if(name==='reset')reset();
            else if(name==='zoomOut')changeZoom(.8);
            else if(name==='toggleFull'||name==='toggle1to1'||name==='toggleZoom') {
                if(renderer?fov<95*radians:zoom>1.01)reset();else changeZoom(1.5);
            }else changeZoom(1.25);
        },true);
        on(canvas,'webglcontextlost',e=>{e.preventDefault();renderer=null;canvas.hidden=true;photo.hidden=false;render();});
        const observer=typeof ResizeObserver==='function'?new ResizeObserver(render):null;
        observer?.observe(surface);on(window,'resize',render);
        viewport.inert=true;
        container.classList.add('ografi-pano-active');
        control.setAttribute('aria-pressed','true');
        control.title='Normal fotoğrafa dön';
        control.setAttribute('aria-label','Normal fotoğrafa dön');
        instance.toggleIdle?.(false);
        const dispose=()=>{
            disposed=true;ready=false;photo.onload=photo.onerror=null;
            if(frame)cancelAnimationFrame(frame);
            observer?.disconnect();listeners.forEach(remove=>remove());
            renderer?.dispose();
            viewport.inert=wasInert;container.classList.remove('ografi-pano-active');
            control.setAttribute('aria-pressed','false');
            control.title='280° panorama';
            control.setAttribute('aria-label','280° panorama görünümüne geç');
            stage.remove();
        };
        sessions.set(instance,{slide,dispose});
        photo.onload=()=>{
            if(disposed)return;
            ready=true;status.hidden=true;
            try{renderer=gpuRenderer(canvas,photo,spherical);}catch(_){renderer=null;}
            canvas.hidden=!renderer;photo.hidden=!!renderer;
            render();
        };
        photo.onerror=()=>{if(!disposed){status.textContent='Panorama yüklenemedi';status.hidden=false;}};
        photo.src=trigger.href;
        stage.focus({preventScroll:true});
    }

    function syncToolbar(instance) {
        if (!instance) return;
        if(!observed.has(instance)){
            observed.add(instance);
            instance.on('close',()=>stopPanorama(instance));
            instance.on('destroy',()=>stopPanorama(instance));
        }
        const container=instance.getContainer();
        const toolbar=container?.querySelector('.f-carousel__toolbar__column.is-right');
        if (!toolbar) return;
        const slide=instance.getSlide();
        const session=sessions.get(instance);
        if(session&&session.slide!==slide)stopPanorama(instance);
        const trigger=slide?.triggerEl;
        const available=eligible(trigger)||eligible(trigger,slide?.el?.querySelector('img'));
        let control=toolbar.querySelector('[data-ografi-panorama-mode]');
        if(!available){if(control)control.hidden=true;return;}
        if(!control){
            control=document.createElement('button');
            control.type='button';
            control.className='f-button ografi-pano-fancy-button';
            control.setAttribute('data-ografi-panorama-mode','');
            control.setAttribute('aria-label','280° panorama görünümüne geç');
            control.setAttribute('aria-pressed','false');
            control.title='280° panorama';
            control.innerHTML=panoramaIcon;
            control.addEventListener('click',event=>{
                event.preventDefault();event.stopPropagation();
                if(sessions.has(instance)){
                    stopPanorama(instance);control.focus({preventScroll:true});return;
                }
                openPanorama(instance,instance.getSlide(),control);
            });
            toolbar.prepend(control);
        }
        const label=isSphere(trigger.href)?'360°':'280°';
        control.innerHTML=isSphere(trigger.href)?sphereIcon:panoramaIcon;
        if(!sessions.has(instance)){control.title=label+' panorama';control.setAttribute('aria-label',label+' panorama görünümüne geç');}
        control.hidden=false;
    }
    window.ografiPanoramaToolbar=syncToolbar;
    document.addEventListener('load',event=>{
        if(event.target instanceof HTMLImageElement)syncToolbar(window.Fancybox?.getInstance?.());
    },true);
    syncToolbar(window.Fancybox?.getInstance?.());
})();
