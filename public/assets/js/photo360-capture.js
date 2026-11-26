(() => {
'use strict';
const R=Math.PI/180, W=1536,H=768,N=W*H;
const clamp=(v,a,b)=>Math.max(a,Math.min(b,v));
const dot=(a,b)=>a[0]*b[0]+a[1]*b[1]+a[2]*b[2];
const direction=(yaw,pitch)=>[Math.sin(yaw)*Math.cos(pitch),Math.cos(yaw)*Math.cos(pitch),Math.sin(pitch)];
// W3C device orientation: intrinsic Z-X'-Y'' rotation; rear camera looks along -Z.
function axes(e){
    const a=e.alpha*R,b=e.beta*R,g=e.gamma*R,ca=Math.cos(a),sa=Math.sin(a),cb=Math.cos(b),sb=Math.sin(b),cg=Math.cos(g),sg=Math.sin(g);
    return {right:[ca*cg-sa*sb*sg,sa*cg+ca*sb*sg,-cb*sg],up:[-sa*cb,ca*cb,sb],forward:[-ca*sg-sa*sb*cg,-sa*sg+ca*sb*cg,-cb*cg]};
}
const pause=()=>new Promise(resolve=>setTimeout(resolve,0));
async function open(onFile){
    if(document.querySelector('.ografi-photo360-camera[open]'))return;
    if(!navigator.mediaDevices?.getUserMedia||!window.DeviceOrientationEvent){alert('360° çekim için HTTPS, kamera ve hareket sensörü destekli bir telefon gerekir. Hazır 360° fotoğraf yükleyebilirsiniz.');return;}
    // Permission requests must start during the original button gesture.
    let sensorPermission;
    try{sensorPermission=typeof DeviceOrientationEvent.requestPermission==='function'?DeviceOrientationEvent.requestPermission():Promise.resolve('granted');}catch(_){sensorPermission=Promise.resolve('denied');}
    const dialog=document.createElement('dialog');dialog.className='ografi-photo360-camera';dialog.setAttribute('aria-label','360° fotoğraf çekimi');
    dialog.innerHTML='<video autoplay muted playsinline></video><div class="ografi-photo360-top"><button type="button" data-close aria-label="Kapat">✕</button><strong>360° Fotoğraf</strong><span data-count>0 / 0</span></div><div class="ografi-photo360-ring" hidden><span class="ografi-photo360-dot"></span></div><div class="ografi-photo360-bottom"><p role="status">Kamera ve hareket sensörü izni bekleniyor…</p><progress value="0" max="1"></progress><button type="button" data-start hidden>Çekime başla</button><button type="button" data-shot hidden disabled aria-label="Fotoğraf çek">●</button><button type="button" data-finish hidden>Fotoğrafı kullan</button></div>';
    document.body.append(dialog);
    const video=dialog.querySelector('video'),status=dialog.querySelector('[role=status]'),ring=dialog.querySelector('.ografi-photo360-ring'),targetDot=ring.firstElementChild,count=dialog.querySelector('[data-count]'),progress=dialog.querySelector('progress'),start=dialog.querySelector('[data-start]'),shot=dialog.querySelector('[data-shot]'),finish=dialog.querySelector('[data-finish]');
    let stream=null,closed=false,busy=false,current=null,targets=[],index=0,baseYaw=0,coverage=0;
    let output=null,best=null,vectors=null;
    const source=document.createElement('canvas'),sc=source.getContext('2d',{willReadFrequently:true});
    const result=document.createElement('canvas');result.width=W;result.height=H;const rc=result.getContext('2d');
    const portrait=()=>!window.matchMedia('(orientation: landscape)').matches;
    function guide(){
        if(closed||busy||!current||!targets[index])return;
        if(!portrait()){status.textContent='Telefonu dik konuma getirin.';shot.disabled=true;return;}
        const t=targets[index],forward=dot(t,current.forward),right=dot(t,current.right),up=dot(t,current.up);
        const error=Math.acos(clamp(forward,-1,1))/R;
        const horizontal=Math.atan2(right,Math.max(.12,forward)),vertical=Math.atan2(up,Math.max(.12,forward));
        targetDot.style.left=(50+clamp(horizontal/R*1.8,-42,42))+'%';
        targetDot.style.top=(50-clamp(vertical/R*1.8,-42,42))+'%';
        const aligned=error<=9;ring.classList.toggle('is-aligned',aligned);shot.disabled=!aligned;
        status.textContent=aligned?'Hedef tamam. Telefonu sabit tutup çekin.':forward<0?'Telefonu yavaşça çevirin.':'Mavi noktayı merkeze getirin.';
    }
    function orientation(e){
        if(e.alpha===null||e.beta===null||e.gamma===null)return;
        current=axes(e);
        if(!targets.length&&!busy){start.hidden=false;start.disabled=!portrait();status.textContent=portrait()?'Telefonu dik tutun. Aynı noktadan yavaşça dönün.':'Telefonu dik konuma getirin.';}
        guide();
    }
    function close(){if(dialog.open)dialog.close();}
    dialog.querySelector('[data-close]').onclick=close;
    dialog.addEventListener('close',()=>{closed=true;window.removeEventListener('deviceorientation',orientation);stream?.getTracks().forEach(t=>t.stop());video.srcObject=null;dialog.remove();},{once:true});
    dialog.showModal();
    try{
        if(await sensorPermission!=='granted')throw Error('Hareket sensörü izni gerekli.');
        if(closed)return;
        window.addEventListener('deviceorientation',orientation);
        stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:'environment'},width:{ideal:1280},height:{ideal:1920}},audio:false});
        if(closed){stream.getTracks().forEach(t=>t.stop());return;}
        video.srcObject=stream;await video.play();
        if(!current)status.textContent='Telefonu hareket ettirin. Sensör verisi bekleniyor.';
    }catch(e){if(!closed)status.textContent=e.message||'Kamera açılamadı. İzinleri kontrol edin.';return;}
    start.onclick=()=>{
        if(!current||!portrait()||!video.videoWidth||busy)return;
        baseYaw=Math.atan2(current.forward[0],current.forward[1]);
        targets=[];
        for(const [pitch,n] of [[0,12],[45,8],[-45,8],[90,1],[-90,1]])for(let i=0;i<n;i++)targets.push(direction(baseYaw+i*2*Math.PI/n,pitch*R));
        output=rc.createImageData(W,H);best=new Float32Array(N);vectors=new Float32Array(N*3);
        for(let row=0;row<H;row++){const p=(.5-(row+.5)/H)*Math.PI,cp=Math.cos(p),sp=Math.sin(p);for(let col=0;col<W;col++){const a=((col+.5)/W-.5)*2*Math.PI+baseYaw,k=(row*W+col)*3;vectors[k]=Math.sin(a)*cp;vectors[k+1]=Math.cos(a)*cp;vectors[k+2]=sp;}}
        count.textContent='0 / '+targets.length;start.hidden=true;ring.hidden=false;shot.hidden=false;guide();
    };
    shot.onclick=async()=>{
        if(closed||busy||shot.disabled||!current||!portrait())return;
        const pose=current;busy=true;shot.disabled=true;status.textContent='Fotoğraf birleştiriliyor…';
        try{
            // Camera field of view is estimated; browsers do not expose calibrated intrinsics.
            const factor=Math.min(1,1280/Math.max(video.videoWidth,video.videoHeight));
            source.width=Math.round(video.videoWidth*factor);source.height=Math.round(video.videoHeight*factor);
            const sw=source.width,sh=source.height;
            if(sw>sh)throw Error('Telefonun dikey kamera görüntüsü desteklenmiyor.');
            sc.drawImage(video,0,0,sw,sh);
            const data=sc.getImageData(0,0,sw,sh).data, focal=sw/(2*Math.tan(28*R)),out=output.data;
            for(let row=0;row<H;row++){
                if(closed)return;
                for(let col=0;col<W;col++){
                    const k=row*W+col,v=k*3,dx=vectors[v],dy=vectors[v+1],dz=vectors[v+2];
                    const z=dx*pose.forward[0]+dy*pose.forward[1]+dz*pose.forward[2];if(z<=.2)continue;
                    const u=(dx*pose.right[0]+dy*pose.right[1]+dz*pose.right[2])/z*focal+sw/2;
                    const t=sh/2-(dx*pose.up[0]+dy*pose.up[1]+dz*pose.up[2])/z*focal;
                    if(u<1||t<1||u>=sw-1||t>=sh-1)continue;
                    const score=z*Math.min(1,Math.min(u,sw-u,t,sh-t)/20);if(score<=best[k])continue;
                    if(!best[k])coverage++;
                    const j=(Math.floor(t)*sw+Math.floor(u))*4,o=k*4;
                    out[o]=data[j];out[o+1]=data[j+1];out[o+2]=data[j+2];out[o+3]=255;best[k]=score;
                }
                if(row%64===63)await pause();
            }
            index++;count.textContent=index+' / '+targets.length;progress.value=index/targets.length;
            if(index===targets.length){
                const percent=coverage/N;
                ring.hidden=true;shot.hidden=true;
                if(percent<.985){
                    status.textContent='Bazı yönler eksik kaldı (%'+Math.round(percent*100)+' kapsama). Tam küresel fotoğraf için yeniden deneyin.';
                    start.hidden=false;start.textContent='Yeniden başla';targets=[];index=0;coverage=0;progress.value=0;return;
                }
                // Small uncovered pixels remain black, never fabricate scene content.
                for(let k=0;k<N;k++)if(!best[k])output.data[k*4+3]=255;
                rc.putImageData(output,0,0);finish.hidden=false;status.textContent='360° fotoğraf hazır. '+Math.round(percent*100)+'% kapsama; dikiş izleri olabilir.';
                return;
            }
        }catch(e){status.textContent=e.message||'Fotoğraf işlenemedi.';}
        finally{busy=false;guide();}
    };
    finish.onclick=()=>{finish.disabled=true;result.toBlob(blob=>{if(closed)return;if(!blob){finish.disabled=false;status.textContent='Fotoğraf oluşturulamadı.';return;}onFile(new File([blob],'ografi-360-'+Date.now()+'.jpg',{type:'image/jpeg'}));close();},'image/jpeg',.92);};
}
window.ografiPhoto360={open};
})();
