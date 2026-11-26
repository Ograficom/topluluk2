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
    dialog.innerHTML='<video autoplay muted playsinline></video><div class="ografi-photo360-top"><button type="button" data-close aria-label="Kapat">✕</button><strong>360° Fotoğraf</strong><span data-count>0 / 0</span></div><div class="ografi-photo360-ring" hidden><svg viewBox="0 0 200 200" aria-hidden="true"><circle cx="100" cy="100" r="97"/><ellipse cx="100" cy="100" rx="97" ry="39"/><ellipse cx="100" cy="100" rx="43" ry="97"/><g class="ografi-photo360-gridpoints"></g><path class="ografi-photo360-cross" d="M87 100h26M100 87v26"/></svg><span class="ografi-photo360-dot"></span></div><div class="ografi-photo360-flash"></div><div class="ografi-photo360-bottom"><p role="status">Kamera ve hareket sensörü izni bekleniyor…</p><progress value="0" max="1"></progress><button type="button" data-start hidden>Çekime başla</button><button type="button" data-shot hidden disabled aria-label="Fotoğraf çek"><span></span></button><img class="ografi-photo360-last" alt="Son çekilen fotoğraf" hidden><button type="button" data-finish hidden>Fotoğrafı kullan</button></div>';
    document.body.append(dialog);
    const video=dialog.querySelector('video'),status=dialog.querySelector('[role=status]'),ring=dialog.querySelector('.ografi-photo360-ring'),targetDot=ring.querySelector('.ografi-photo360-dot'),count=dialog.querySelector('[data-count]'),progress=dialog.querySelector('progress'),start=dialog.querySelector('[data-start]'),shot=dialog.querySelector('[data-shot]'),finish=dialog.querySelector('[data-finish]');
    const last=dialog.querySelector('.ografi-photo360-last'),flash=dialog.querySelector('.ografi-photo360-flash'),grid=ring.querySelector('.ografi-photo360-gridpoints');
    for(let i=0;i<12;i++){
        const a=i*Math.PI/6,point=document.createElementNS('http://www.w3.org/2000/svg','circle');
        point.setAttribute('cx',100+78*Math.cos(a));point.setAttribute('cy',100+78*Math.sin(a));point.setAttribute('r','2.1');grid.append(point);
    }
    let stream=null,closed=false,busy=false,current=null,targets=[],index=0,baseYaw=0,coverage=0,shots=0,cameraReady=false,completed=false,lastNotice=0;
    let output=null,best=null,vectors=null;
    const source=document.createElement('canvas'),sc=source.getContext('2d',{willReadFrequently:true});
    const result=document.createElement('canvas');result.width=W;result.height=H;const rc=result.getContext('2d');
    const portrait=()=>!window.matchMedia('(orientation: landscape)').matches;
    function guide(){
        if(closed||busy||completed||!current||!targets[index])return;
        shot.disabled=!cameraReady;
        if(!portrait()){status.textContent='Telefonu dik konuma getirin.';return;}
        if(Date.now()<lastNotice)return;
        const t=targets[index],forward=dot(t,current.forward),right=dot(t,current.right),up=dot(t,current.up);
        const error=Math.acos(clamp(forward,-1,1))/R;
        const horizontal=Math.atan2(right,Math.max(.12,forward)),vertical=Math.atan2(up,Math.max(.12,forward));
        targetDot.style.left=(50+clamp(horizontal/R*1.8,-42,42))+'%';
        targetDot.style.top=(50-clamp(vertical/R*1.8,-42,42))+'%';
        const aligned=error<=15;ring.classList.toggle('is-aligned',aligned);
        status.textContent=aligned?'Hedef hazır. Çekim yapın.':forward<0?'Telefonu yavaşça çevirin.':'Mavi noktayı merkeze getirin. İsterseniz şimdi de çekebilirsiniz.';
    }
    function orientation(e){
        if(e.alpha===null||e.beta===null||e.gamma===null)return;
        current=axes(e);
        if(cameraReady&&!targets.length&&!busy&&!completed){start.hidden=false;start.disabled=false;status.textContent='Telefonu dik tutun. Aynı noktadan yavaşça dönün.';}
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
        if(closed)return;
        cameraReady=true;
        if(current){start.hidden=false;start.disabled=false;status.textContent='Telefonu dik tutun ve çekime başlayın.';}
        else status.textContent='Telefonu hareket ettirin. Sensör verisi bekleniyor.';
    }catch(e){if(!closed)status.textContent=e.message||'Kamera açılamadı. İzinleri kontrol edin.';return;}
    start.onclick=()=>{
        if(!current||!cameraReady||!video.videoWidth||busy){status.textContent='Kamera veya sensör henüz hazır değil.';return;}
        if(!portrait()){status.textContent='Telefonu dik konuma getirin.';return;}
        index=0;coverage=0;shots=0;completed=false;lastNotice=0;last.hidden=true;finish.hidden=true;progress.value=0;
        baseYaw=Math.atan2(current.forward[0],current.forward[1]);
        targets=[];
        for(const [pitch,n] of [[0,12],[45,8],[-45,8],[90,1],[-90,1]])for(let i=0;i<n;i++)targets.push(direction(baseYaw+i*2*Math.PI/n,pitch*R));
        output=rc.createImageData(W,H);best=new Float32Array(N);vectors=new Float32Array(N*3);
        for(let row=0;row<H;row++){const p=(.5-(row+.5)/H)*Math.PI,cp=Math.cos(p),sp=Math.sin(p);for(let col=0;col<W;col++){const a=((col+.5)/W-.5)*2*Math.PI+baseYaw,k=(row*W+col)*3;vectors[k]=Math.sin(a)*cp;vectors[k+1]=Math.cos(a)*cp;vectors[k+2]=sp;}}
        count.textContent='0 / '+targets.length;start.hidden=true;ring.hidden=false;shot.hidden=false;shot.disabled=false;guide();
    };
    shot.onclick=async()=>{
        if(closed||busy||completed)return;
        if(!current||!cameraReady||!video.videoWidth){status.textContent='Kamera veya sensör henüz hazır değil.';return;}
        if(!portrait()){status.textContent='Telefonu dik konuma getirin.';return;}
        const pose=current;busy=true;shot.disabled=true;status.textContent='Fotoğraf çekiliyor…';
        try{
            // Camera field of view is estimated; browsers do not expose calibrated intrinsics.
            const factor=Math.min(1,1280/Math.max(video.videoWidth,video.videoHeight));
            source.width=Math.max(1,Math.round(video.videoWidth*factor));source.height=Math.max(1,Math.round(video.videoHeight*factor));
            const sw=source.width,sh=source.height;
            // Some Android browsers deliver landscape frames even with a portrait UI.
            // Keep the actual video-frame orientation, rather than rejecting the shot.
            sc.drawImage(video,0,0,sw,sh);
            last.src=source.toDataURL('image/jpeg',.68);last.hidden=false;
            flash.classList.remove('is-flashing');void flash.offsetWidth;flash.classList.add('is-flashing');
            if(navigator.vibrate)navigator.vibrate(30);
            shots++;status.textContent='Fotoğraf alındı, birleştiriliyor…';
            const data=sc.getImageData(0,0,sw,sh).data, focal=Math.min(sw,sh)/(2*Math.tan(28*R)),out=output.data;
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
            const percent=coverage/N;
            // A manual shot always captures real pixels. Only a sufficiently
            // aligned frame completes the current target; no fake completion.
            if(dot(targets[index],pose.forward)>=Math.cos(22*R))index++;
            if(index===targets.length&&percent<.985){
                // Continue at the centre of an uncovered region, instead of
                // throwing away the user's captures and restarting.
                let missing=-1,min=Infinity;
                for(let k=0;k<N;k+=17){if(best[k]===0){
                    const col=k%W,row=Math.floor(k/W);
                    const score=Math.abs(row-H/2)*.01+Math.abs(col-W/2)*.001;
                    if(score<min){min=score;missing=k;}
                }}
                if(missing<0)missing=best.findIndex(v=>v===0);
                if(missing>=0)targets.push([vectors[missing*3],vectors[missing*3+1],vectors[missing*3+2]]);
            }
            count.textContent=index+' / '+targets.length;progress.value=percent;
            if(percent>=.985){
                completed=true;ring.hidden=true;shot.hidden=true;
                for(let k=0;k<N;k++)if(!best[k])output.data[k*4+3]=255;
                rc.putImageData(output,0,0);finish.hidden=false;
                status.textContent='360° fotoğraf hazır. %'+Math.round(percent*100)+' kapsama. '+shots+' çekim.';
                return;
            }
            lastNotice=Date.now()+900;
            status.textContent=shots+' fotoğraf çekildi · %'+Math.round(percent*100)+' kapsama';
            setTimeout(guide,950);
        }catch(e){lastNotice=Date.now()+3500;status.textContent=e.message||'Fotoğraf işlenemedi.';setTimeout(guide,3600);}
        finally{busy=false;shot.disabled=!cameraReady;guide();}
    };
    finish.onclick=()=>{finish.disabled=true;result.toBlob(blob=>{if(closed)return;if(!blob){finish.disabled=false;status.textContent='Fotoğraf oluşturulamadı.';return;}onFile(new File([blob],'ografi-360-'+Date.now()+'.jpg',{type:'image/jpeg'}));close();},'image/jpeg',.92);};
}
window.ografiPhoto360={open};
})();
