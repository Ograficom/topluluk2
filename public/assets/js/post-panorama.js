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
        label.textContent = '↔  280° Keşfet';
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
        const trigger = event.target instanceof Element && event.target.closest('a[data-ografi-panorama]');
        if (!trigger || event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const dialog=document.createElement('dialog');
        if(typeof dialog.showModal!=='function') return;
        event.preventDefault();event.stopImmediatePropagation();
        dialog.className='ografi-panorama-dialog';
        dialog.setAttribute('aria-label','280 derece panorama');
        dialog.innerHTML='<div class="ografi-pano-stage"><canvas tabindex="0" aria-label="Panorama. Ok tuşlarıyla gezinin." hidden></canvas><img class="ografi-pano-original" alt="Orijinal panoramik fotoğraf" hidden><div class="ografi-pano-top"><span class="ografi-pano-name"><b>280°</b> Panorama</span><div class="ografi-pano-tools">'+button('overview','Tüm fotoğraf / panorama')+button('expand','Tam ekran')+button('close','Kapat')+'</div></div><div class="ografi-pano-bottom" hidden><div class="ografi-pano-drag">Sürükleyerek keşfet</div><div class="ografi-pano-bar">'+button('minus','Uzaklaştır')+button('plus','Yakınlaştır')+'<input type="range" min="-140" max="140" step="1" value="0" aria-label="Yatay görüş açısı"><output>0°</output>'+button('reset','Görünümü sıfırla')+'</div></div><div class="ografi-pano-status" role="status">Panorama yükleniyor…</div></div>';
        document.body.append(dialog);
        let canvas=dialog.querySelector('canvas'), renderer=null, gpu=false;
        const original=dialog.querySelector('.ografi-pano-original');
        const image=new Image(), status=dialog.querySelector('[role="status"]'), controls=dialog.querySelector('.ografi-pano-bottom'), range=dialog.querySelector('input'), output=dialog.querySelector('output');
        const overviewButton=dialog.querySelector('[data-overview]');
        let yaw=0, fov=120*radians, frame=0, ready=false, overview=false, pointer=null, lastX=0;
        const draw=()=>{
            frame=0;if(!ready||!dialog.open||overview||!renderer)return;
            const rect=canvas.getBoundingClientRect();if(!rect.width||!rect.height)return;
            const maxPixels=gpu?5000000:2200000;
            const scale=Math.min(window.devicePixelRatio||1,Math.sqrt(maxPixels/(rect.width*rect.height)));
            const w=Math.max(1,Math.round(rect.width*scale)),h=Math.max(1,Math.round(rect.height*scale));
            if(canvas.width!==w)canvas.width=w;if(canvas.height!==h)canvas.height=h;
            const limit=half-fov/2;yaw=clamp(yaw,-limit,limit);
            range.min=String(Math.ceil(-limit/radians));range.max=String(Math.floor(limit/radians));range.value=String(Math.round(yaw/radians));output.value=Math.round(yaw/radians)+'°';
            renderer.draw(w,h,yaw,fov);
        };
        const render=()=>{if(!frame)frame=requestAnimationFrame(draw);};
        const setOverview=value=>{
            overview=value;original.hidden=!value;canvas.hidden=value;controls.hidden=value;
            overviewButton.setAttribute('aria-pressed',String(value));
            if(!value)render();
        };
        const zoom=direction=>{if(overview)setOverview(false);fov=clamp(fov+direction*10*radians,45*radians,135*radians);render();};
        const reset=()=>{yaw=0;fov=120*radians;if(overview)setOverview(false);render();};
        const resize=typeof ResizeObserver==='function'?new ResizeObserver(render):null;
        const bindCanvas=()=>{
            canvas.addEventListener('pointerdown',e=>{if(pointer!==null||e.button!==0)return;pointer=e.pointerId;lastX=e.clientX;canvas.setPointerCapture(pointer);});
            canvas.addEventListener('pointermove',e=>{if(e.pointerId!==pointer)return;yaw-=(e.clientX-lastX)*fov/Math.max(1,canvas.clientWidth);lastX=e.clientX;render();});
            ['pointerup','pointercancel','lostpointercapture'].forEach(name=>canvas.addEventListener(name,e=>{if(pointer===e.pointerId)pointer=null;}));
            canvas.addEventListener('wheel',e=>{e.preventDefault();zoom(e.deltaY>0?1:-1);},{passive:false});
            canvas.addEventListener('keydown',e=>{
                if(!['ArrowLeft','ArrowRight','+','-','Home'].includes(e.key))return;e.preventDefault();
                if(e.key==='ArrowLeft')yaw-=8*radians;if(e.key==='ArrowRight')yaw+=8*radians;
                if(e.key==='+')zoom(-1);if(e.key==='-')zoom(1);if(e.key==='Home')reset();render();
            });
            canvas.addEventListener('webglcontextlost',e=>{e.preventDefault();if(!ready)return;setOverview(true);overviewButton.disabled=true;status.textContent='Panorama görüntüsü kesildi. Orijinal fotoğraf gösteriliyor.';status.hidden=false;});
        };
        range.addEventListener('input',()=>{yaw=Number(range.value)*radians;render();});
        dialog.querySelector('[data-minus]').addEventListener('click',()=>zoom(1));
        dialog.querySelector('[data-plus]').addEventListener('click',()=>zoom(-1));
        dialog.querySelector('[data-reset]').addEventListener('click',reset);
        overviewButton.addEventListener('click',()=>{if(ready)setOverview(!overview);});
        dialog.querySelector('[data-expand]').addEventListener('click',e=>{const expanded=dialog.classList.toggle('is-expanded');e.currentTarget.setAttribute('aria-pressed',String(expanded));render();});
        dialog.querySelector('[data-close]').addEventListener('click',()=>dialog.close());
        dialog.addEventListener('close',()=>{
            ready=false;image.onload=image.onerror=null;
            if(frame)cancelAnimationFrame(frame);resize?.disconnect();window.removeEventListener('resize',render);
            renderer?.dispose();original.removeAttribute('src');dialog.remove();if(trigger.isConnected)trigger.focus();
        },{once:true});
        try{dialog.showModal();}catch(_){dialog.remove();location.href=trigger.href;return;}
        image.onload=()=>{
            if(!dialog.open)return;
            original.src=image.src;
            if(image.naturalWidth<image.naturalHeight*2){original.hidden=false;overviewButton.disabled=true;status.textContent='Orijinal fotoğraf';return;}
            try{renderer=gpuRenderer(canvas,image);gpu=!!renderer;}catch(_){renderer=null;}
            if(!renderer){
                // A canvas cannot switch context types after a WebGL context was created.
                const replacement=canvas.cloneNode(false);canvas.replaceWith(replacement);canvas=replacement;
                renderer=cpuRenderer(canvas,image);
            }
            if(!renderer){original.hidden=false;overviewButton.disabled=true;status.textContent='Orijinal fotoğraf';return;}
            ready=true;bindCanvas();resize?.observe(canvas);window.addEventListener('resize',render);
            status.hidden=true;setOverview(false);
        };
        image.onerror=()=>{status.textContent='Panorama yüklenemedi.';overviewButton.disabled=true;};
        image.src=trigger.href;
    },true);
})();
