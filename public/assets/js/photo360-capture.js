
(() => {
'use strict';
const R=Math.PI/180;
const clamp=(x,a,b)=>Math.max(a,Math.min(b,x));
const dot=(a,b)=>a.reduce((s,v,i)=>s+v*b[i],0);
const direction=(yaw,pitch)=>[Math.sin(yaw)*Math.cos(pitch),Math.cos(yaw)*Math.cos(pitch),Math.sin(pitch)];
const wrap=a=>((a+180)%360+360)%360-180;
function axes(e){
    const a=e.alpha*R,b=e.beta*R,g=e.gamma*R,ca=Math.cos(a),sa=Math.sin(a),cb=Math.cos(b),sb=Math.sin(b),cg=Math.cos(g),sg=Math.sin(g);
    return {right:[ca*cg-sa*sb*sg,sa*cg+ca*sb*sg,-cb*sg],up:[-sa*cb,ca*cb,sb],forward:[-ca*sg-sa*sb*cg,-sa*sg+ca*sb*cg,-cb*cg]};
}
function poseData(pose,origin){
    const f=pose.forward,yaw=Math.atan2(f[0],f[1]),pitch=Math.asin(clamp(f[2],-1,1));
    const right=[Math.cos(yaw),-Math.sin(yaw),0],up=[-Math.sin(yaw)*Math.sin(pitch),-Math.cos(yaw)*Math.sin(pitch),Math.cos(pitch)];
    return {yaw:wrap((yaw-origin)/R),pitch:pitch/R,roll:wrap(-Math.atan2(dot(pose.right,up),dot(pose.right,right))/R)};
}
const sleep=ms=>new Promise(resolve=>setTimeout(resolve,ms));
async function open(onFile){
    if(document.querySelector('.ografi-photo360-camera[open]'))return;
    if(!navigator.mediaDevices?.getUserMedia||!window.DeviceOrientationEvent){alert('HTTPS, kamera ve hareket sensörü destekli telefon gerekiyor.');return;}
    const permission=typeof DeviceOrientationEvent.requestPermission==='function'?DeviceOrientationEvent.requestPermission():Promise.resolve('granted');
    const dialog=document.createElement('dialog');dialog.className='ografi-photo360-camera';dialog.setAttribute('aria-label','360° fotoğraf çekimi');
    dialog.innerHTML='<video autoplay muted playsinline></video><img class="ografi-photo360-result" alt="360° önizleme" hidden><div class="ografi-photo360-top"><button type="button" data-close aria-label="Kapat">✕</button><strong>360° Fotoğraf</strong><span data-count>0 / 38</span></div><div class="ografi-photo360-ring" hidden><svg viewBox="0 0 200 200" aria-hidden="true"><circle cx="100" cy="100" r="97"/><ellipse cx="100" cy="100" rx="97" ry="39"/><ellipse cx="100" cy="100" rx="43" ry="97"/><g class="ografi-photo360-gridpoints"></g><path class="ografi-photo360-cross" d="M87 100h26M100 87v26"/></svg><span class="ografi-photo360-dot"></span></div><div class="ografi-photo360-flash"></div><div class="ografi-photo360-bottom"><p role="status">Kamera açılıyor…</p><progress value="0" max="1"></progress><button type="button" data-start hidden>Çekime başla</button><button type="button" data-shot hidden aria-label="Fotoğraf çek"><span></span></button><img class="ografi-photo360-last" alt="Son çekilen fotoğraf" hidden><button type="button" data-restart hidden>Yeniden çek</button><button type="button" data-finish hidden>Fotoğrafı kullan</button></div>';
    document.body.append(dialog);
    const q=s=>dialog.querySelector(s),video=q('video'),status=q('[role=status]'),ring=q('.ografi-photo360-ring'),point=q('.ografi-photo360-dot'),count=q('[data-count]'),progress=q('progress'),start=q('[data-start]'),shot=q('[data-shot]'),finish=q('[data-finish]'),restart=q('[data-restart]'),preview=q('.ografi-photo360-result'),last=q('.ografi-photo360-last'),flash=q('.ografi-photo360-flash');
    for(let i=0;i<12;i++){const a=i*Math.PI/6,p=document.createElementNS('http://www.w3.org/2000/svg','circle');p.setAttribute('cx',100+78*Math.cos(a));p.setAttribute('cy',100+78*Math.sin(a));p.setAttribute('r','2.1');q('.ografi-photo360-gridpoints').append(p);}
    let stream=null,current=null,closed=false,busy=false,ready=false,frames=[],targets=[],origin=0,id=null,resultFile=null,previewUrl=null,notice=0,lastSensor=0;
    const abort=new AbortController(),csrf=document.querySelector('meta[name="csrf-token"]')?.content||'';
    const root='/composer/photo360',engineVersion='hugin-enblend-v2';
    async function request(path,method='GET',body,signal=abort.signal){
        const response=await fetch(root+path,{method,body,signal,credentials:'same-origin',headers:{Accept:'application/json','X-CSRF-TOKEN':csrf},cache:'no-store'});
        if(!response.ok){let msg='İşlem başarısız ('+response.status+').';try{msg=(await response.json()).message||msg;}catch(_){}throw Error(msg);}
        return response;
    }
    function stopCamera(){stream?.getTracks().forEach(t=>t.stop());video.srcObject=null;stream=null;ready=false;}
    async function camera(){
        stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:'environment'},width:{ideal:1920},height:{ideal:2560}},audio:false});
        if(closed){stopCamera();return;}video.srcObject=stream;await video.play();ready=true;
        if(!current)status.textContent='Telefonu hareket ettirin. Sensör verisi bekleniyor.';
        else {start.hidden=false;status.textContent='Ortamı aydınlatın; telefonu aynı merkez etrafında döndürün.';}
    }
    const portrait=()=>!window.matchMedia('(orientation: landscape)').matches;
    function guide(){
        if(closed||busy||!ready||!current||!targets[frames.length]||Date.now()<notice)return;
        const t=targets[frames.length],z=dot(t,current.forward),x=dot(t,current.right),y=dot(t,current.up);
        const angle=Math.acos(clamp(z,-1,1))/R;
        point.style.left=(50+clamp(Math.atan2(x,Math.max(.12,z))/R*1.8,-42,42))+'%';
        point.style.top=(50-clamp(Math.atan2(y,Math.max(.12,z))/R*1.8,-42,42))+'%';
        ring.classList.toggle('is-aligned',angle<=10);
        status.textContent=!portrait()?'Telefonu dik konuma getirin.':angle<=10?'Hedef hazır. Lensi aynı noktada tutup çekin.':'Mavi noktayı merkeze getirin; yer değiştirmeyin.';
    }
    function orientation(e){
        if(![e.alpha,e.beta,e.gamma].every(Number.isFinite))return;
        current=axes(e);lastSensor=Date.now();
        if(ready&&!targets.length&&!busy){start.hidden=false;status.textContent='Telefonu dik tutun; aynı noktadan dönün.';}
        guide();
    }
    function cancelJob(){const old=id;id=null;if(old)request('/'+old,'DELETE',undefined,new AbortController().signal).catch(()=>{});}
    q('[data-close]').onclick=()=>dialog.close();
    dialog.addEventListener('close',()=>{closed=true;abort.abort();cancelJob();stopCamera();window.removeEventListener('deviceorientation',orientation);if(previewUrl)URL.revokeObjectURL(previewUrl);dialog.remove();},{once:true});
    dialog.showModal();
    try{
        if(await permission!=='granted')throw Error('Hareket sensörü izni gerekli.');
        if(closed)return;
        window.addEventListener('deviceorientation',orientation);
        const health=await (await request('/health')).json();
        if(health.engine!==engineVersion)throw Error('360° sunucu sürümü ile kamera sürümü eşleşmiyor. Sayfayı yenileyin veya yöneticiye bildirin.');
        dialog.querySelector('.ografi-photo360-top strong').textContent='360° Fotoğraf · Hugin';
        await camera();
    }catch(e){if(!closed)status.textContent=e.message||'Kamera açılamadı.';return;}
    const canvas=document.createElement('canvas'),ctx=canvas.getContext('2d',{alpha:false});
    const check=document.createElement('canvas');check.width=160;check.height=120;
    const qc=check.getContext('2d',{willReadFrequently:true});
    function qualityWarning(){
        qc.drawImage(video,0,0,check.width,check.height);
        const {data}=qc.getImageData(0,0,check.width,check.height),g=new Float32Array(check.width*check.height);
        let mean=0,dark=0,clipped=0;
        for(let i=0;i<g.length;i++){const v=.2126*data[i*4]+.7152*data[i*4+1]+.0722*data[i*4+2];g[i]=v;mean+=v;dark+=v<12;clipped+=v>247;}
        mean/=g.length;
        let sum=0,sq=0,n=0;
        for(let y=1;y<check.height-1;y++)for(let x=1;x<check.width-1;x++){
            const i=y*check.width+x,l=4*g[i]-g[i-1]-g[i+1]-g[i-check.width]-g[i+check.width];sum+=l;sq+=l*l;n++;
        }
        const sharpness=sq/n-(sum/n)**2;
        if(mean<15||dark/g.length>.92)return 'Çok karanlık. Işığı açıp yeniden çekin.';
        if(clipped/g.length>.8)return 'Kare aşırı aydınlık. Pozlamayı azaltıp yeniden çekin.';
        if(sharpness<4)return 'Görüntü çok bulanık veya ayrıntısız. Telefonu sabitleyin, ortamı aydınlatın.';
        return '';
    }
    start.onclick=()=>{
        if(!ready||!current||!video.videoWidth||busy)return;
        if(!portrait()){status.textContent='Telefonu dik konuma getirin.';return;}
        if(Math.abs(current.forward[2])>.25){status.textContent='Önce telefonu düz karşıya tutun.';notice=Date.now()+1800;return;}
        origin=Math.atan2(current.forward[0],current.forward[1]);frames=[];targets=[];
        // Dense overlaps make feature matching much more reliable.
        for(const [pitch,n] of [[0,16],[45,10],[-45,10],[90,1],[-90,1]])
            for(let i=0;i<n;i++)targets.push(direction(origin+i*2*Math.PI/n,pitch*R));
        start.hidden=true;ring.hidden=false;shot.hidden=false;restart.hidden=true;finish.hidden=true;last.hidden=true;preview.hidden=true;video.hidden=false;
        count.textContent='0 / '+targets.length;progress.value=0;guide();
    };
    async function process(){
        ring.hidden=true;shot.hidden=true;stopCamera();status.textContent='Çekimler sunucuya yükleniyor…';progress.value=0;
        const init=new FormData();init.append('count',String(frames.length));
        id=(await (await request('','POST',init)).json()).id;
        for(let i=0;i<frames.length;i++){
            if(closed)return;
            const f=frames[i],body=new FormData();body.append('index',String(i));body.append('frame',f.blob,'frame-'+i+'.jpg');
            for(const key of ['yaw','pitch','roll'])body.append(key,String(f.pose[key]));
            await request('/'+id+'/frame','POST',body);
            progress.value=(i+1)/frames.length;
            status.textContent='Kareler yükleniyor '+(i+1)+' / '+frames.length;
        }
        await request('/'+id+'/start','POST');
        progress.removeAttribute('value');
        // Server processing runs independently of this HTTP request.
        const until=Date.now()+16*60*1000;
        while(!closed&&Date.now()<until){
            const data=await (await request('/'+id)).json();status.textContent=data.message;
            if(data.state==='failed'||data.state==='cancelled')throw Error(data.message);
            if(data.state==='ready'){
                const blob=await (await request('/'+id+'/image')).blob();
                if(blob.type!=='image/jpeg'||blob.size>5*1024*1024)throw Error('Sunucu geçersiz fotoğraf döndürdü.');
                resultFile=new File([blob],'ografi-360-'+Date.now()+'.jpg',{type:'image/jpeg'});
                previewUrl=URL.createObjectURL(resultFile);preview.src=previewUrl;preview.hidden=false;video.hidden=true;
                progress.value=1;finish.hidden=false;restart.hidden=false;frames=[];return;
            }
            await sleep(2500);
        }
        throw Error('İşleme zaman aşımı. Sunucu kuyruğunu kontrol edin.');
    }
    shot.onclick=async()=>{
        if(busy||closed||!ready||!current||!targets[frames.length])return;
        if(!portrait()){status.textContent='Telefonu dik konuma getirin.';return;}
        if(Date.now()-lastSensor>1200){status.textContent='Hareket sensörü güncel değil. Telefonu hareket ettirin.';return;}
        if(dot(targets[frames.length],current.forward)<Math.cos(10*R)){
            notice=Date.now()+1500;status.textContent='Çekim için mavi noktayı merkeze yaklaştırın.';return;
        }
        busy=true;shot.disabled=true;
        try{
            const factor=Math.min(1,1920/Math.max(video.videoWidth,video.videoHeight));
            canvas.width=Math.round(video.videoWidth*factor);canvas.height=Math.round(video.videoHeight*factor);
            if(!canvas.width||!canvas.height)throw Error('Kamera henüz hazır değil.');
            if(canvas.width>canvas.height)throw Error('Kamera yatay kare gönderiyor. Telefonu dik konuma getirin ve kamerayı yeniden açın.');
            const warning=qualityWarning();
            if(warning)throw Error(warning);
            const pose=poseData(current,origin);
            ctx.drawImage(video,0,0,canvas.width,canvas.height);
            const blob=await new Promise(resolve=>canvas.toBlob(resolve,'image/jpeg',.95));
            if(!blob||blob.size>3*1024*1024)throw Error('Kare kaydedilemedi veya çok büyük.');
            if(closed)return;
            frames.push({blob,pose});last.src=canvas.toDataURL('image/jpeg',.55);last.hidden=false;
            flash.classList.remove('is-flashing');void flash.offsetWidth;flash.classList.add('is-flashing');
            navigator.vibrate?.(25);count.textContent=frames.length+' / '+targets.length;progress.value=frames.length/targets.length;
            if(frames.length===targets.length)await process();
            else {notice=Date.now()+650;status.textContent='Fotoğraf çekildi.';setTimeout(guide,700);}
        }catch(e){if(!closed){notice=Date.now()+3000;status.textContent=e.message||'Çekim başarısız.';if(!ready){cancelJob();restart.hidden=false;}else setTimeout(guide,3100);}}
        finally{busy=false;shot.disabled=false;guide();}
    };
    restart.onclick=async()=>{if(busy)return;busy=true;cancelJob();frames=[];targets=[];resultFile=null;if(previewUrl){URL.revokeObjectURL(previewUrl);previewUrl=null;}preview.hidden=true;video.hidden=false;finish.hidden=true;restart.hidden=true;progress.value=0;try{await camera();start.hidden=false;}catch(e){status.textContent=e.message;restart.hidden=false;}finally{busy=false;}};
    finish.onclick=()=>{if(!resultFile)return;onFile(resultFile);dialog.close();};
}
window.ografiPhoto360={open};
})();
