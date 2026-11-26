(() => {
'use strict';
const R=Math.PI/180, W=2048,H=1024,N=W*H;
const clamp=(v,a,b)=>Math.max(a,Math.min(b,v));
const dot=(a,b)=>a[0]*b[0]+a[1]*b[1]+a[2]*b[2];
const direction=(yaw,pitch)=>[Math.sin(yaw)*Math.cos(pitch),Math.cos(yaw)*Math.cos(pitch),Math.sin(pitch)];
// W3C device orientation: intrinsic Z-X'-Y'' rotation; rear camera looks along -Z.
function axes(e){
    const a=e.alpha*R,b=e.beta*R,g=e.gamma*R,ca=Math.cos(a),sa=Math.sin(a),cb=Math.cos(b),sb=Math.sin(b),cg=Math.cos(g),sg=Math.sin(g);
    return {right:[ca*cg-sa*sb*sg,sa*cg+ca*sb*sg,-cb*sg],up:[-sa*cb,ca*cb,sb],forward:[-ca*sg-sa*sb*cg,-sa*sg+ca*sb*cg,-cb*cg]};
}
// Bilinear interpolation avoids nearest-neighbour jagged edges.
function sample(data,w,h,u,v){
    if(u<0||v<0||u>=w-1||v>=h-1)return null;
    const x=Math.floor(u),y=Math.floor(v),a=u-x,b=v-y,i=(y*w+x)*4,j=i+w*4;
    return [0,1,2].map(c=>(data[i+c]*(1-a)+data[i+4+c]*a)*(1-b)+(data[j+c]*(1-a)+data[j+4+c]*a)*b);
}
const luma=(r,g,b)=>.2126*r+.7152*g+.0722*b;
const smooth=t=>{t=clamp(t,0,1);return t*t*(3-2*t);};
function project(dx,dy,dz,pose,focal,w,h){
    const z=dx*pose.forward[0]+dy*pose.forward[1]+dz*pose.forward[2];
    if(z<=.2)return null;
    return [(dx*pose.right[0]+dy*pose.right[1]+dz*pose.right[2])/z*focal+w/2,
        h/2-(dx*pose.up[0]+dy*pose.up[1]+dz*pose.up[2])/z*focal,z];
}
// Refine sensor orientation in 3D, including roll and focal length. A flat
// pixel shift cannot correctly align wide-angle camera frames on a sphere.
function rotation(yaw,pitch,roll){
    const a=yaw*R,b=pitch*R,c=roll*R,ca=Math.cos(a),sa=Math.sin(a),cb=Math.cos(b),sb=Math.sin(b),cc=Math.cos(c),sc=Math.sin(c);
    return [cc*ca-sc*sb*sa,-sc*cb,cc*sa+sc*sb*ca,
        sc*ca+cc*sb*sa,cc*cb,sc*sa-cc*sb*ca,
        -cb*sa,sb,cb*ca];
}
function rayToCamera(x,y,z,pose){
    return [x*pose.right[0]+y*pose.right[1]+z*pose.right[2],
        x*pose.up[0]+y*pose.up[1]+z*pose.up[2],
        x*pose.forward[0]+y*pose.forward[1]+z*pose.forward[2]];
}
function correctedProjection(x,y,z,m,focal,w,h){
    const a=m[0]*x+m[1]*y+m[2]*z,b=m[3]*x+m[4]*y+m[5]*z,c=m[6]*x+m[7]*y+m[8]*z;
    if(c<=.2)return null;
    return [w/2+a/c*focal,h/2-b/c*focal,c];
}
function alignFrame(samples,data,w,h,focal){
    const identity={yaw:0,pitch:0,roll:0,scale:1,matrix:rotation(0,0,0),gain:1,bias:0,confidence:0,reliable:false};
    if(samples.length<45)return identity;
    const evaluate=(yaw,pitch,roll,scale)=>{
        const matrix=rotation(yaw,pitch,roll);
        let n=0,sa=0,sb=0,saa=0,sbb=0,sab=0,la=0,lb=0,laa=0,lbb=0,lab=0;
        for(const p of samples){
            const uv=correctedProjection(p.x,p.y,p.z,matrix,focal*scale,w,h);
            if(!uv)continue;
            const rgb=sample(data,w,h,uv[0],uv[1]);if(!rgb)continue;
            const a=p.lum,b=luma(...rgb),aLog=Math.log(8+a),bLog=Math.log(8+b);
            n++;sa+=aLog;sb+=bLog;saa+=aLog*aLog;sbb+=bLog*bLog;sab+=aLog*bLog;
            la+=a;lb+=b;laa+=a*a;lbb+=b*b;lab+=a*b;
        }
        if(n<Math.max(35,samples.length*.72))return {...identity,score:Infinity};
        const va=saa-sa*sa/n,vb=sbb-sb*sb/n,cov=sab-sa*sb/n;
        const corr=va>.5&&vb>.5?cov/Math.sqrt(va*vb):-1;
        const rawVar=lbb-lb*lb/n;
        const gain=rawVar>40?clamp((lab-la*lb/n)/rawVar,.55,1.9):1;
        const bias=clamp((la-gain*lb)/n,-24,24);
        return {yaw,pitch,roll,scale,matrix,gain,bias,confidence:corr,
            score:1-corr+.0005*(yaw*yaw+pitch*pitch+roll*roll)+.09*Math.abs(scale-1),
            reliable:corr>=.52};
    };
    const baseline=evaluate(0,0,0,1);
    let winner=baseline;
    // A broad initial search compensates for typical phone FOV differences.
    for(const scale of [.76,.88,1,1.12,1.26])for(const yaw of [-9,-4.5,0,4.5,9])for(const pitch of [-9,-4.5,0,4.5,9]){
        const candidate=evaluate(yaw,pitch,0,scale);if(candidate.score<winner.score)winner=candidate;
    }
    for(const [angle,zoom] of [[3,.07],[1.5,.035],[.65,.015]]){
        for(let pass=0;pass<2;pass++){
            const center=winner;
            for(const axis of ['yaw','pitch','roll','scale'])for(const sign of [-1,1]){
                const p={yaw:center.yaw,pitch:center.pitch,roll:center.roll,scale:center.scale};
                p[axis]+=sign*(axis==='scale'?zoom:angle);
                if(Math.abs(p.yaw)>15||Math.abs(p.pitch)>15||Math.abs(p.roll)>9||p.scale<.68||p.scale>1.4)continue;
                const candidate=evaluate(p.yaw,p.pitch,p.roll,p.scale);
                if(candidate.score<winner.score)winner=candidate;
            }
        }
    }
    if(!winner.reliable)return {...identity,confidence:winner.confidence};
    if(baseline.reliable&&baseline.score-winner.score<.012)return baseline;
    return winner;
}
const pause=()=>new Promise(resolve=>setTimeout(resolve,0));
async function open(onFile){
    if(document.querySelector('.ografi-photo360-camera[open]'))return;
    if(!navigator.mediaDevices?.getUserMedia||!window.DeviceOrientationEvent){alert('360° çekim için HTTPS, kamera ve hareket sensörü destekli bir telefon gerekir. Hazır 360° fotoğraf yükleyebilirsiniz.');return;}
    // Permission requests must start during the original button gesture.
    let sensorPermission;
    try{sensorPermission=typeof DeviceOrientationEvent.requestPermission==='function'?DeviceOrientationEvent.requestPermission():Promise.resolve('granted');}catch(_){sensorPermission=Promise.resolve('denied');}
    const dialog=document.createElement('dialog');dialog.className='ografi-photo360-camera';dialog.setAttribute('aria-label','360° fotoğraf çekimi');
    dialog.innerHTML='<video autoplay muted playsinline></video><div class="ografi-photo360-top"><button type="button" data-close aria-label="Kapat">✕</button><strong>360° Fotoğraf</strong><span data-count>0 / 0</span></div><div class="ografi-photo360-ring" hidden><svg viewBox="0 0 200 200" aria-hidden="true"><circle cx="100" cy="100" r="97"/><ellipse cx="100" cy="100" rx="97" ry="39"/><ellipse cx="100" cy="100" rx="43" ry="97"/><g class="ografi-photo360-gridpoints"></g><path class="ografi-photo360-cross" d="M87 100h26M100 87v26"/></svg><span class="ografi-photo360-dot"></span></div><div class="ografi-photo360-flash"></div><div class="ografi-photo360-bottom"><p role="status">Kamera ve hareket sensörü izni bekleniyor…</p><progress value="0" max="1"></progress><button type="button" data-start hidden>Çekime başla</button><button type="button" data-shot hidden disabled aria-label="Fotoğraf çek"><span></span></button><img class="ografi-photo360-last" alt="Son çekilen fotoğraf" hidden><button type="button" data-restart hidden>Yeniden çek</button><button type="button" data-finish hidden>Fotoğrafı kullan</button></div>';
    document.body.append(dialog);
    const video=dialog.querySelector('video'),status=dialog.querySelector('[role=status]'),ring=dialog.querySelector('.ografi-photo360-ring'),targetDot=ring.querySelector('.ografi-photo360-dot'),count=dialog.querySelector('[data-count]'),progress=dialog.querySelector('progress'),start=dialog.querySelector('[data-start]'),shot=dialog.querySelector('[data-shot]'),finish=dialog.querySelector('[data-finish]');
    const last=dialog.querySelector('.ografi-photo360-last'),flash=dialog.querySelector('.ografi-photo360-flash'),grid=ring.querySelector('.ografi-photo360-gridpoints');
    for(let i=0;i<12;i++){
        const a=i*Math.PI/6,point=document.createElementNS('http://www.w3.org/2000/svg','circle');
        point.setAttribute('cx',100+78*Math.cos(a));point.setAttribute('cy',100+78*Math.sin(a));point.setAttribute('r','2.1');grid.append(point);
    }
    let stream=null,closed=false,busy=false,current=null,targets=[],index=0,baseYaw=0,coverage=0,shots=0,cameraReady=false,completed=false,lastNotice=0;
    let output=null,best=null,vectors=null,accum=null,quality=null,focalScale=1;
    const restart=dialog.querySelector('[data-restart]');
    let previewUrl=null;
    const preview=document.createElement('img');preview.className='ografi-photo360-result';preview.alt='Birleştirilen 360° fotoğraf';preview.hidden=true;dialog.prepend(preview);
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
        const aligned=error<=10;ring.classList.toggle('is-aligned',aligned);
        status.textContent=aligned?'Hedef hazır. Çekim yapın.':forward<0?'Telefonu yavaşça çevirin.':'Mavi noktayı merkeze getirin. İsterseniz şimdi de çekebilirsiniz.';
    }
    function orientation(e){
        if(e.alpha===null||e.beta===null||e.gamma===null)return;
        current=axes(e);
        if(cameraReady&&!targets.length&&!busy&&!completed){start.hidden=false;start.disabled=false;status.textContent='Telefonu dik tutun; kamerayı aynı noktada döndürün. Ortamı mümkünse aydınlatın.';}
        guide();
    }
    function close(){if(dialog.open)dialog.close();}
    dialog.querySelector('[data-close]').onclick=close;
    dialog.addEventListener('close',()=>{closed=true;window.removeEventListener('deviceorientation',orientation);stream?.getTracks().forEach(t=>t.stop());video.srcObject=null;if(previewUrl)URL.revokeObjectURL(previewUrl);dialog.remove();},{once:true});
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
        index=0;coverage=0;shots=0;completed=false;lastNotice=0;last.hidden=true;finish.hidden=true;restart.hidden=true;progress.value=0;
        preview.hidden=true;video.hidden=false;if(previewUrl){URL.revokeObjectURL(previewUrl);previewUrl=null;}
        baseYaw=Math.atan2(current.forward[0],current.forward[1]);
        targets=[];
        for(const [pitch,n] of [[0,12],[45,8],[-45,8],[90,1],[-90,1]])for(let i=0;i<n;i++)targets.push(direction(baseYaw+i*2*Math.PI/n,pitch*R));
        output=rc.createImageData(W,H);best=new Float32Array(N);vectors=new Float32Array(N*3);accum=new Float32Array(N*3);quality=new Float32Array(N);focalScale=1;
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
            const factor=Math.min(1,1600/Math.max(video.videoWidth,video.videoHeight));
            source.width=Math.max(1,Math.round(video.videoWidth*factor));source.height=Math.max(1,Math.round(video.videoHeight*factor));
            const sw=source.width,sh=source.height;
            // Some Android browsers deliver landscape frames even with a portrait UI.
            // Keep the actual video-frame orientation, rather than rejecting the shot.
            sc.drawImage(video,0,0,sw,sh);
            last.src=source.toDataURL('image/jpeg',.68);last.hidden=false;
            flash.classList.remove('is-flashing');void flash.offsetWidth;flash.classList.add('is-flashing');
            if(navigator.vibrate)navigator.vibrate(30);
            shots++;status.textContent='Fotoğraf alındı, birleştiriliyor…';
            const data=sc.getImageData(0,0,sw,sh).data, focal=Math.min(sw,sh)/(2*Math.tan(28*R))*focalScale,out=output.data;
            // Use the already fused sphere as a reference, sampling only textured overlap.
            const matches=[];
            if(coverage){
                for(let row=8;row<H-8;row+=13)for(let col=8;col<W-8;col+=13){
                    const k=row*W+col,v=k*3;
                    if(best[k]<.12||best[k-3]<.12||best[k+3]<.12||best[k-3*W]<.12||best[k+3*W]<.12)continue;
                    const p=project(vectors[v],vectors[v+1],vectors[v+2],pose,focal,sw,sh);
                    if(!p||p[0]<sw*.12||p[0]>sw*.88||p[1]<sh*.12||p[1]>sh*.88)continue;
                    const i=k*4,lum=luma(out[i],out[i+1],out[i+2]);
                    if(lum<7||lum>245)continue;
                    const neighbor=q=>luma(out[q*4],out[q*4+1],out[q*4+2]);
                    if(Math.max(Math.abs(neighbor(k-3)-neighbor(k+3)),Math.abs(neighbor(k-3*W)-neighbor(k+3*W)))<5)continue;
                    const ray=rayToCamera(vectors[v],vectors[v+1],vectors[v+2],pose);
                    matches.push({x:ray[0],y:ray[1],z:ray[2],lum});
                }
            }
            const stride=Math.max(1,Math.ceil(matches.length/480));
            const correction=alignFrame(matches.filter((_,i)=>i%stride===0),data,sw,sh,focal);
            if(matches.length>=100&&!correction.reliable&&correction.confidence<.18){
                // Strongly inconsistent overlap is more damaging than a missing frame.
                // Keep all previously captured pixels and let the user retake this view.
                shots--;lastNotice=Date.now()+2800;status.textContent='Fotoğraflar eşleşmedi. Telefonu aynı noktada sabit tutun, biraz önceki yöne dönüp tekrar çekin.';setTimeout(guide,2900);return;
            }
            if(correction.reliable)focalScale=clamp(focalScale*(1+.18*(correction.scale-1)),.68,1.4);
            const feather=Math.min(sw,sh)*.32;
            // Blend overlapping shots with smoothly tapered weights rather
            // than overwriting an entire region with the newest best frame.
            for(let row=0;row<H;row++){
                if(closed)return;
                for(let col=0;col<W;col++){
                    const k=row*W+col,v=k*3;
                    const dx=vectors[v],dy=vectors[v+1],dz=vectors[v+2];
                    const z=dx*pose.forward[0]+dy*pose.forward[1]+dz*pose.forward[2];
                    if(z<=.1)continue;
                    const q=rayToCamera(dx,dy,dz,pose);
                    const p=correctedProjection(q[0],q[1],q[2],correction.matrix,focal*correction.scale,sw,sh);
                    if(!p)continue;
                    const u=p[0],t=p[1];
                    if(u<1||t<1||u>=sw-1||t>=sh-1)continue;
                    const score=Math.max(.001,smooth(Math.min(u,sw-1-u,t,sh-1-t)/feather)*Math.pow(p[2],3));
                    const rgb=sample(data,sw,sh,u,t);if(!rgb)continue;
                    const o=k*4;
                    const corrected=rgb.map(c=>clamp(c*correction.gain+correction.bias,0,255));
                    let weight=score;
                    if(!best[k])coverage++;
                    else {
                        // Avoid double edges in regions affected by nearby objects,
                        // movement or parallax. Smoothly prefer a centred observation
                        // when the two registered colours disagree strongly.
                        const mismatch=Math.max(...corrected.map((c,i)=>Math.abs(c-out[o+i])));
                        const disagreement=smooth((mismatch-22)/48);
                        const preference=score/(score+quality[k]+.00001);
                        weight*=1-disagreement*(1-preference)*.94;
                        const keep=1-disagreement*preference*.88;
                        best[k]*=keep;
                        for(let c=0;c<3;c++)accum[v+c]*=keep;
                    }
                    const total=best[k]+weight;
                    for(let c=0;c<3;c++){
                        accum[v+c]+=corrected[c]*weight;
                        out[o+c]=accum[v+c]/total;
                    }
                    quality[k]=Math.max(quality[k],score);
                    best[k]=total;out[o+3]=255;
                }
                if(row%48===47)await pause();
            }
            const percent=coverage/N;
            // A manual shot always captures real pixels. Only a sufficiently
            // aligned frame completes the current target; no fake completion.
            if(dot(targets[index],pose.forward)>=Math.cos(15*R))index++;
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
                status.textContent='Birleştirme tamamlandı. Kenarları ve ışık geçişlerini kontrol edin.';
                restart.hidden=false;
                result.toBlob(blob=>{if(!blob||closed||!completed)return;previewUrl=URL.createObjectURL(blob);preview.src=previewUrl;preview.hidden=false;video.hidden=true;},'image/jpeg',.94);
                return;
            }
            lastNotice=Date.now()+900;
            status.textContent=shots+' fotoğraf çekildi · %'+Math.round(percent*100)+' kapsama';
            setTimeout(guide,950);
        }catch(e){lastNotice=Date.now()+3500;status.textContent=e.message||'Fotoğraf işlenemedi.';setTimeout(guide,3600);}
        finally{busy=false;shot.disabled=!cameraReady;guide();}
    };
    restart.onclick=()=>{finish.disabled=false;start.onclick();};
    finish.onclick=()=>{finish.disabled=true;result.toBlob(blob=>{if(closed)return;if(!blob){finish.disabled=false;status.textContent='Fotoğraf oluşturulamadı.';return;}onFile(new File([blob],'ografi-360-'+Date.now()+'.jpg',{type:'image/jpeg'}));close();},'image/jpeg',.94);};
}
window.ografiPhoto360={open};
})();
