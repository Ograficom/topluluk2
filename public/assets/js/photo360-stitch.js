(() => {
'use strict';
// Keep each camera frame independent until the common camera model is fitted.
// Camera coordinates are right/up/forward; use the sensor only as a pose prior.
const rad=Math.PI/180, clamp=(x,a,b)=>Math.max(a,Math.min(b,x));
const dot=(a,b)=>a[0]*b[0]+a[1]*b[1]+a[2]*b[2];
const norm=a=>{const n=Math.hypot(...a)||1;return a.map(v=>v/n);};
const cross=(a,b)=>[a[1]*b[2]-a[2]*b[1],a[2]*b[0]-a[0]*b[2],a[0]*b[1]-a[1]*b[0]];
const delay=()=>new Promise(r=>setTimeout(r,0));
const rotate=(v,a)=>{
    const t=Math.hypot(...a);if(t<1e-10)return v;
    const k=a.map(x=>x/t),c=Math.cos(t),s=Math.sin(t),q=cross(k,v),d=dot(k,v)*(1-c);
    return v.map((x,i)=>x*c+q[i]*s+k[i]*d);
};
function ray(frame,x,y,f,correction=[0,0,0]){
    const u=(x-frame.w/2)/f,v=(frame.h/2-y)/f,p=frame.pose;
    return rotate(norm(p.forward.map((z,i)=>z+u*p.right[i]+v*p.up[i])),correction);
}
function project(frame,world,f){
    const z=dot(frame.pose.forward,world);
    if(z<=.15)return null;
    return [frame.w/2+f*dot(frame.pose.right,world)/z,frame.h/2-f*dot(frame.pose.up,world)/z];
}
function bilinear(g,w,h,x,y){
    if(x<0||y<0||x>=w-1||y>=h-1)return NaN;
    const i=Math.floor(x),j=Math.floor(y),a=x-i,b=y-j,k=j*w+i;
    return (g[k]*(1-a)+g[k+1]*a)*(1-b)+(g[k+w]*(1-a)+g[k+w+1]*a)*b;
}
function grayFrame(canvas,pose){
    const scale=Math.min(1,360/Math.max(canvas.width,canvas.height)),c=document.createElement('canvas');
    c.width=Math.round(canvas.width*scale);c.height=Math.round(canvas.height*scale);
    const ctx=c.getContext('2d',{willReadFrequently:true});ctx.drawImage(canvas,0,0,c.width,c.height);
    const rgba=ctx.getImageData(0,0,c.width,c.height).data,g=new Float32Array(c.width*c.height);
    for(let i=0;i<g.length;i++)g[i]=.2126*rgba[4*i]+.7152*rgba[4*i+1]+.0722*rgba[4*i+2];
    return {w:c.width,h:c.height,g,pose};
}
function corners(frame){
    const {g,w,h}=frame,points=[];
    // Spatially distributed, two-directional corners rather than noisy flat walls.
    for(let by=10;by<h-10;by+=20)for(let bx=10;bx<w-10;bx+=20){
        let best=null;
        for(let y=by;y<Math.min(by+20,h-8);y+=3)for(let x=bx;x<Math.min(bx+20,w-8);x+=3){
            let xx=0,yy=0,xy=0;
            for(let j=-2;j<=2;j++)for(let i=-2;i<=2;i++){
                const k=(y+j)*w+x+i,dx=g[k+1]-g[k-1],dy=g[k+w]-g[k-w];
                xx+=dx*dx;yy+=dy*dy;xy+=dx*dy;
            }
            const score=(xx+yy-Math.sqrt((xx-yy)**2+4*xy*xy))/50;
            if(score>15&&(!best||score>best.score))best={x,y,score};
        }
        if(best)points.push(best);
    }
    return points.sort((a,b)=>b.score-a.score).slice(0,130);
}
function matchPair(a,b,factor){
    const fa=Math.min(a.w,a.h)*factor,fb=Math.min(b.w,b.h)*factor,out=[];
    for(const p of a.corners){
        const center=project(b,ray(a,p.x,p.y,fa),fb);
        if(!center||center[0]<7||center[1]<7||center[0]>b.w-8||center[1]>b.h-8)continue;
        // Warp the reference patch using the sensor-based spherical projection.
        const samples=[];
        for(let y=-6;y<=6;y+=3)for(let x=-6;x<=6;x+=3){
            const q=project(b,ray(a,p.x+x,p.y+y,fa),fb);
            if(q)samples.push([q[0]-center[0],q[1]-center[1],a.g[(p.y+y)*a.w+p.x+x]]);
        }
        if(samples.length<25)continue;
        const mean=samples.reduce((s,q)=>s+q[2],0)/samples.length;
        let variance=0;for(const q of samples){q[2]-=mean;variance+=q[2]**2;}
        if(variance<900)continue;
        function score(x,y){
            let sum=0,sq=0,cov=0;
            for(const q of samples){const v=bilinear(b.g,b.w,b.h,x+q[0],y+q[1]);if(!Number.isFinite(v))return -2;sum+=v;sq+=v*v;cov+=v*q[2];}
            const v=sq-sum*sum/samples.length;
            return v>300?cov/Math.sqrt(v*variance):-2;
        }
        const radius=Math.round(Math.min(b.w,b.h)*.17),candidates=[];
        let best={x:center[0],y:center[1],s:-2};
        for(let dy=-radius;dy<=radius;dy+=4)for(let dx=-radius;dx<=radius;dx+=4){
            const x=center[0]+dx,y=center[1]+dy,s=score(x,y);
            if(s>.65)candidates.push({x,y,s});if(s>best.s)best={x,y,s};
        }
        const coarse=best;
        for(let dy=-4;dy<=4;dy++)for(let dx=-4;dx<=4;dx++){
            const x=coarse.x+dx,y=coarse.y+dy,s=score(x,y);if(s>best.s)best={x,y,s};
        }
        const alternate=candidates.filter(q=>Math.hypot(q.x-best.x,q.y-best.y)>7).reduce((s,q)=>Math.max(s,q.s),-2);
        if(best.s>=.83&&best.s-alternate>=.055)out.push({ax:p.x,ay:p.y,bx:best.x,by:best.y,confidence:best.s});
    }
    return out;
}
function loss(matches,frames,poses,factor){
    let sum=0;
    for(const m of matches){
        const a=frames[m.i],b=frames[m.j];
        const u=ray(a,m.ax,m.ay,Math.min(a.w,a.h)*factor,poses[m.i]);
        const v=ray(b,m.bx,m.by,Math.min(b.w,b.h)*factor,poses[m.j]);
        const e=Math.sqrt(Math.max(0,2-2*clamp(dot(u,v),-1,1)))/rad;
        // Huber-style robust loss: mismatched features must not move the sphere.
        sum+=m.confidence*(e<=1.5?e*e:3*e-2.25);
    }
    return sum;
}
function optimize(frames,matches){
    const poses=frames.map(()=>[0,0,0]),links=frames.map((_,i)=>matches.filter(m=>m.i===i||m.j===i));
    const initial=1/(2*Math.tan(28*rad));let factor=initial;
    for(const step of [4,2,1,.5,.25,.12]){
        for(let iter=0;iter<3;iter++){
            // One lens parameter for EVERY frame; never change focal length mid-fusion.
            let base=loss(matches,frames,poses,factor);
            for(const s of [-1,1]){
                const next=clamp(factor*Math.exp(s*step*.025),initial*.65,initial*1.55),v=loss(matches,frames,poses,next);
                if(v<base){factor=next;base=v;}
            }
            for(let i=1;i<frames.length;i++){
                if(links[i].length<5)continue;
                const cost=()=>loss(links[i],frames,poses,factor)+.12*poses[i].reduce((s,v)=>s+(v/rad)**2,0);
                let best=cost();
                for(let a=0;a<3;a++){
                    const original=poses[i][a];let chosen=original;
                    for(const s of [-1,1]){
                        poses[i][a]=clamp(original+s*step*rad,-18*rad,18*rad);
                        const v=cost();if(v<best){chosen=poses[i][a];best=v;}
                    }
                    poses[i][a]=chosen;
                }
            }
        }
    }
    return {poses,factor};
}
async function stitch(frames,canvas,baseYaw,notify,cancelled){
    const W=canvas.width,H=canvas.height,N=W*H;
    const initial=1/(2*Math.tan(28*rad));
    frames.forEach(f=>{f.corners=corners(f);});
    const pairs=[];
    for(let i=0;i<frames.length;i++)for(let j=i+1;j<frames.length;j++){
        const angle=Math.acos(clamp(dot(frames[i].pose.forward,frames[j].pose.forward),-1,1))/rad;
        if(angle<53&&angle>8)pairs.push({i,j,angle});
    }
    // Include closure and adjacent elevation rows, not only capture order.
    const selected=pairs.sort((a,b)=>a.angle-b.angle).filter((p,_,all)=>true);
    let matches=[],done=0;
    for(const p of selected){
        if(cancelled())throw Error('İşlem iptal edildi.');
        const found=matchPair(frames[p.i],frames[p.j],initial);
        if(found.length>=5)matches.push(...found.map(m=>({...m,i:p.i,j:p.j})));
        if(++done%2===0){notify('Kareler eşleştiriliyor '+done+'/'+selected.length);await delay();}
    }
    let fit=optimize(frames,matches);
    // Remove false matches once, then fit the entire graph again.
    matches=matches.filter(m=>{
        const a=frames[m.i],b=frames[m.j];
        const u=ray(a,m.ax,m.ay,Math.min(a.w,a.h)*fit.factor,fit.poses[m.i]);
        const v=ray(b,m.bx,m.by,Math.min(b.w,b.h)*fit.factor,fit.poses[m.j]);
        return dot(u,v)>Math.cos(3*rad);
    });
    fit=optimize(frames,matches);
    const connected=new Set(matches.flatMap(m=>[m.i,m.j]));
    const trusted=matches.length>=frames.length*5&&connected.size>=frames.length*.7;
    const ctx=canvas.getContext('2d'),out=ctx.createImageData(W,H);
    const weights=new Float32Array(N),rgb=new Float32Array(N*3),vectors=new Float32Array(N*3);
    for(let y=0;y<H;y++){
        const p=(.5-(y+.5)/H)*Math.PI,cp=Math.cos(p),sp=Math.sin(p);
        for(let x=0;x<W;x++){const a=((x+.5)/W-.5)*2*Math.PI+baseYaw,k=(y*W+x)*3;vectors[k]=Math.sin(a)*cp;vectors[k+1]=Math.cos(a)*cp;vectors[k+2]=sp;}
    }
    const tmp=document.createElement('canvas'),tc=tmp.getContext('2d',{willReadFrequently:true});
    let covered=0;
    for(let i=0;i<frames.length;i++){
        if(cancelled())throw Error('İşlem iptal edildi.');
        const frame=frames[i],correction=fit.poses[i],pose={};
        for(const key of ['right','up','forward'])pose[key]=rotate(frame.pose[key],correction);
        const url=URL.createObjectURL(frame.blob);
        try{
            const image=new Image();image.src=url;await image.decode();
            tmp.width=image.naturalWidth;tmp.height=image.naturalHeight;tc.drawImage(image,0,0);
            const w=tmp.width,h=tmp.height,data=tc.getImageData(0,0,w,h).data,f=Math.min(w,h)*fit.factor;
            const projectPixel=k=>{
                const v=k*3,x=vectors[v],y=vectors[v+1],z=vectors[v+2],d=x*pose.forward[0]+y*pose.forward[1]+z*pose.forward[2];
                if(d<=.2)return null;
                const u=w/2+f*(x*pose.right[0]+y*pose.right[1]+z*pose.right[2])/d,t=h/2-f*(x*pose.up[0]+y*pose.up[1]+z*pose.up[2])/d;
                if(u<1||t<1||u>=w-1||t>=h-1)return null;
                return [u,t,d];
            };
            // Exposure normalisation is measured after geometric registration.
            let a=0,b=0,n=0;
            for(let k=0;k<N;k+=197){if(weights[k]<.12)continue;const p=projectPixel(k);if(!p)continue;
                const j=(Math.round(p[1])*w+Math.round(p[0]))*4,v=k*3,old=(.2126*rgb[v]+.7152*rgb[v+1]+.0722*rgb[v+2])/weights[k],fresh=.2126*data[j]+.7152*data[j+1]+.0722*data[j+2];
                if(old>12&&fresh>12&&old<230&&fresh<230){a+=Math.log(old);b+=Math.log(fresh);n++;}
            }
            const gain=n>30?clamp(Math.exp((a-b)/n),.6,1.7):1;
            for(let y=0;y<H;y++){
                if(cancelled())throw Error('İşlem iptal edildi.');
                for(let x=0;x<W;x++){
                    const k=y*W+x,p=projectPixel(k);if(!p)continue;
                    const u=p[0],t=p[1],ix=Math.floor(u),iy=Math.floor(t),fx=u-ix,fy=t-iy,j=(iy*w+ix)*4,j2=j+w*4;
                    let edge=clamp(Math.min(u,w-1-u,t,h-1-t)/(Math.min(w,h)*.35),0,1);
                    edge=edge*edge*(3-2*edge);
                    const weight=Math.max(.0005,edge*p[2]**5),v=k*3;
                    if(!weights[k])covered++;
                    for(let c=0;c<3;c++){
                        const val=((data[j+c]*(1-fx)+data[j+4+c]*fx)*(1-fy)+(data[j2+c]*(1-fx)+data[j2+4+c]*fx)*fy)*gain;
                        rgb[v+c]+=clamp(val,0,255)*weight;
                    }
                    weights[k]+=weight;
                }
                if(y%64===63)await delay();
            }
        }finally{URL.revokeObjectURL(url);}
        notify('Küre oluşturuluyor '+(i+1)+'/'+frames.length);await delay();
    }
    for(let k=0;k<N;k++){const v=k*3,j=k*4,w=weights[k];if(w)for(let c=0;c<3;c++)out.data[j+c]=rgb[v+c]/w;out.data[j+3]=255;}
    ctx.putImageData(out,0,0);
    return {coverage:covered/N, trusted, matches:matches.length, connected:connected.size, frames:frames.length};
}
window.ografiSphereStitch={grayFrame,stitch};
})();
