<style>
    .ografi-ny-hat{position:absolute;left:-4px;top:-7px;width:24px;height:23px;pointer-events:none;transform:rotate(-13deg);z-index:1}
    .ografi-ny-logo{position:relative}
    .ografi-ny-lights{position:absolute;left:0;right:0;bottom:0;height:3px;background:#4b647d;pointer-events:none;z-index:2}
    .ografi-ny-lights::after{content:"";position:absolute;top:0;left:0;right:0;height:9px;background:radial-gradient(ellipse 4px 6px at 12px 3px,#ff6d79 80%,transparent 95%),radial-gradient(ellipse 4px 6px at 36px 3px,#ffd36b 80%,transparent 95%),radial-gradient(ellipse 4px 6px at 60px 3px,#7fe4ba 80%,transparent 95%),radial-gradient(ellipse 4px 6px at 84px 3px,#80c8ff 80%,transparent 95%);background-size:96px 9px;filter:drop-shadow(0 2px 3px #92caff)}
    .ografi-ny-card{position:relative;isolation:isolate;overflow:hidden;box-sizing:border-box;width:100%;margin:0 0 16px;padding:24px 16px 30px;text-align:center;color:#fff;border-radius:16px;background:radial-gradient(ellipse at 50% 0%,#286cb3,#0b356e 58%,#102653);box-shadow:0 4px 16px #143e7626}
    .ografi-ny-card .ografi-ny-scene{position:absolute;inset:auto 0 0;width:100%;height:100%;z-index:-1;pointer-events:none}
    .ografi-ny-card .ografi-ny-heading{font-size:clamp(15px,2.6vw,23px);font-weight:600;margin:0;color:#fff}
    .ografi-ny-year{font-size:clamp(42px,8vw,70px);line-height:1.12;font-weight:850;letter-spacing:2px;color:#e1f8ff;text-shadow:0 0 20px #67d4ff;margin:3px 0 12px}
    .ografi-ny-clock{display:flex;justify-content:center;gap:7px;margin:auto;max-width:350px}
    .ografi-ny-clock>span{display:grid;gap:3px;min-width:0;flex:1;padding:8px 3px;border:1px solid #8dcaff80;border-radius:10px;background:#092957d9;color:white}
    .ografi-ny-clock b{font-size:clamp(16px,4vw,25px);font-variant-numeric:tabular-nums;line-height:1.15}
    .ografi-ny-clock small{font-size:11px;color:#dbeafe;white-space:nowrap}
    .ografi-ny-card [hidden]{display:none!important}
    .ografi-ny-layer{position:fixed;inset:0;overflow:hidden;pointer-events:none;z-index:35}
    .ografi-ny-flake{position:absolute;top:-20px;left:var(--x);width:var(--size);height:var(--size);border-radius:50%;background:#fff;box-shadow:0 0 5px #8db7df;opacity:.85;animation:ografi-ny-fall var(--duration) linear var(--delay) infinite}
    @keyframes ografi-ny-fall{to{transform:translate3d(var(--drift),calc(100vh + 30px),0)}}
    .ografi-ny-confetti{position:absolute;top:-20px;left:var(--x);width:7px;height:12px;border-radius:2px;background:var(--color);animation:ografi-ny-celebrate var(--duration) ease-in var(--delay) both}
    @keyframes ografi-ny-celebrate{to{transform:translate3d(var(--drift),calc(100vh + 30px),0) rotate(700deg);opacity:.4}}
    @media(max-width:600px){.ografi-ny-card{padding:19px 13px 25px;border-radius:13px}.ografi-ny-card .ografi-ny-scene{opacity:.75}.ografi-ny-clock{max-width:290px}}
    @media(prefers-reduced-motion:reduce){.ografi-ny-layer{display:none!important}}
</style>
