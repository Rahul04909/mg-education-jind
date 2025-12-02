<?php
?>
<style>
*{box-sizing:border-box}
.ai{background:#fff;overflow-x:hidden}
.ai-wrap{max-width:1200px;width:100%;margin:0 auto;padding:12px 16px 18px}
.ai-shell{display:grid;grid-template-columns:280px minmax(0,1fr);gap:16px}
.ai-side{border:1px solid #e6e8ee;border-radius:14px;background:linear-gradient(180deg,#f9fbff 0%,#f6f8ff 100%);position:sticky;top:88px;height:calc(100vh - 100px);display:flex;flex-direction:column;box-shadow:0 10px 30px rgba(11,16,32,0.06)}
.ai-brand{display:flex;align-items:center;gap:10px;padding:12px;border-bottom:1px solid #e6e8ee}
.ai-logo{height:36px;width:auto;border-radius:8px}
.ai-title{font-weight:800;color:#0b1020}
.ai-menu{flex:1;overflow:auto;padding:12px;display:grid;gap:10px}
.menu-link{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px;border:1px solid #e6e8ee;border-radius:12px;background:#fff;color:#1a1f27;font-weight:700;transition:all .2s ease;box-shadow:0 2px 6px rgba(11,16,32,0.04)}
.menu-link .icon{display:inline-flex;align-items:center;justify-content:center;height:28px;width:28px;border-radius:8px;background:#eef6ff;color:#0a50c9;flex-shrink:0}
.menu-link .label{flex:1;min-width:0}
.menu-link .chev{color:#8a90a3;font-size:18px;line-height:1}
.menu-link:hover{border-color:#cfe0ff;background:#f8fbff;transform:translateY(-1px);box-shadow:0 6px 14px rgba(11,16,32,0.08)}
.menu-link.active{background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);color:#fff;border-color:transparent}
.menu-link.active .icon{background:rgba(255,255,255,.2);color:#fff}
.menu-link.active .chev{color:#fff;opacity:.9}
.ai-main{border:1px solid #e6e8ee;border-radius:14px;background:#fff;display:flex;flex-direction:column;min-height:70vh;min-width:0}
.ai-head{display:flex;align-items:center;justify-content:space-between;padding:12px;border-bottom:1px solid #e6e8ee}
.ai-model{display:flex;align-items:center;gap:8px}
.pill{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;background:#eef6ff;color:#0a50c9;font-weight:700}
.ai-suggest{display:flex;gap:10px;padding:10px;border-bottom:1px solid #e6e8ee;overflow:auto}
.chip{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:12px;border:1px solid #e6e8ee;background:#f8fbff;color:#2b313b;font-weight:700;white-space:nowrap}
.ai-thread{flex:1;overflow:auto;padding:18px;display:grid;gap:10px;background:#fff}
.msg{display:flex;gap:10px;align-items:flex-end}
.msg.user{flex-direction:row-reverse}
.avatar{height:34px;width:34px;border-radius:999px;border:1px solid #e6e8ee;overflow:hidden}
.bubble{max-width:68%;border:1px solid #e6e8ee;border-radius:18px;padding:10px}
.bubble.assistant{background:#f8fbff;border-color:#cfe0ff;border-top-left-radius:6px}
.bubble.user{background:linear-gradient(90deg,#eaf1ff 0%,#f6f8ff 100%);border-color:#d6def7;border-top-right-radius:6px}
.bubble p{margin:0;color:#1a1f27}
.ai-compose{display:flex;gap:10px;padding:12px;border-top:1px solid #e6e8ee;background:#fff}
.input{flex:1;border:1px solid #e6e8ee;border-radius:12px;padding:12px;background:#f7f8fa;color:#0f1419}
.attach{padding:12px;border-radius:12px;background:#fff;color:#5b616e;border:1px solid #d0d6e3;font-weight:700}
.send{padding:12px 16px;border-radius:12px;background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);color:#fff;font-weight:800;border:0}
.stop{padding:12px 16px;border-radius:12px;background:#fff;color:#5b616e;border:1px solid #d0d6e3;font-weight:700}
.onboard-backdrop{position:fixed;inset:0;background:rgba(11,16,32,.4);backdrop-filter:blur(3px);display:flex;align-items:center;justify-content:center;z-index:999}
.onboard{width:92vw;max-width:520px;border-radius:16px;border:1px solid #e6e8ee;background:#fff;box-shadow:0 20px 40px rgba(11,16,32,.12);overflow:hidden}
.onboard-head{display:flex;align-items:center;justify-content:space-between;padding:16px;background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);color:#fff}
.onboard-title{font-weight:800}
.onboard-body{padding:16px;background:#f9fbff}
.form-row{display:grid;gap:8px;margin-bottom:12px}
.form-label{font-weight:700;color:#0b1020}
.form-input{border:1px solid #e6e8ee;border-radius:12px;padding:12px;background:#fff;color:#0f1419}
.form-input:focus{outline:none;border-color:#cfe0ff;box-shadow:0 0 0 3px rgba(19,88,219,.12)}
.form-input:invalid{border-color:#ff6b6b;background:#fff5f5}
.onboard-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:8px}
.start-btn{padding:12px 16px;border-radius:12px;background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);color:#fff;font-weight:800;border:0}
@media(max-width:1024px){.ai-shell{grid-template-columns:1fr}}
</style>
<section class="ai" role="region" aria-label="MG EDU AI">
  <div class="ai-wrap">
    <div class="ai-shell">
      <aside class="ai-side">
        <div class="ai-brand">
          <img src="../assets/images/mg-logo.jpg" alt="MG Skill" class="ai-logo"/>
          <div class="ai-title">MG EDU AI</div>
        </div>
        <div class="ai-menu">
          <a href="#" class="menu-link active">
            <span class="icon"><svg viewBox="0 0 24 24" width="18" height="18"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span>
            <span class="label">New Chat</span>
            <span class="chev">›</span>
          </a>
          <a href="#" class="menu-link">
            <span class="icon"><svg viewBox="0 0 24 24" width="18" height="18"><path d="M6 4h12v16l-6-4-6 4z" fill="currentColor"/></svg></span>
            <span class="label">Saved Prompts</span>
            <span class="chev">›</span>
          </a>
          <a href="#" class="menu-link">
            <span class="icon"><svg viewBox="0 0 24 24" width="18" height="18"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" fill="none"/><path d="M12 7v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span>
            <span class="label">Recent Chats</span>
            <span class="chev">›</span>
          </a>
          <a href="#" class="menu-link">
            <span class="icon"><svg viewBox="0 0 24 24" width="18" height="18"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="8" cy="10" r="2" fill="currentColor"/><path d="M21 17l-6-6-4 4-3-3-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/></svg></span>
            <span class="label">Images & Docs</span>
            <span class="chev">›</span>
          </a>
          <a href="#" class="menu-link">
            <span class="icon"><svg viewBox="0 0 24 24" width="18" height="18"><path d="M6 4v16" stroke="currentColor" stroke-width="2"/><path d="M12 8v12" stroke="currentColor" stroke-width="2"/><path d="M18 4v16" stroke="currentColor" stroke-width="2"/><circle cx="6" cy="10" r="2" fill="currentColor"/><circle cx="12" cy="6" r="2" fill="currentColor"/><circle cx="18" cy="14" r="2" fill="currentColor"/></svg></span>
            <span class="label">Settings</span>
            <span class="chev">›</span>
          </a>
        </div>
      </aside>
      <main class="ai-main">
        <div class="ai-head">
          <div class="ai-model"><span class="pill">MG‑GPT</span><span class="pill">Fast</span></div>
          <div class="ai-model"><span class="pill">New</span></div>
        </div>
        <div class="ai-suggest">
          <button class="chip" data-prompt="Suggest top 5 job‑ready skills">Suggest top 5 job‑ready skills</button>
          <button class="chip" data-prompt="Create a study plan for React & Next.js">Create a study plan for React & Next.js</button>
          <button class="chip" data-prompt="Write resume bullets for web developer">Write resume bullets for web developer</button>
          <button class="chip" data-prompt="Explain Docker & Kubernetes simply">Explain Docker & Kubernetes simply</button>
        </div>
        <div class="ai-thread" id="aiThread"></div>
        <div class="ai-compose">
          <button class="attach" id="aiAttach">Attach</button>
          <input type="file" id="aiImage" accept="image/*" style="display:none"/>
          <input class="input" id="aiInput" type="text" placeholder="Type your message"/>
          <button class="stop" id="aiStop">Stop</button>
          <button class="send" id="aiSend">Send</button>
        </div>
      </main>
    </div>
  </div>
  <div class="onboard-backdrop" id="aiOnboard" role="dialog" aria-modal="true" aria-label="Start Chat">
    <div class="onboard">
      <div class="onboard-head">
        <div class="onboard-title">Start Your Chat</div>
      </div>
      <form class="onboard-body" id="aiOnboardForm">
        <div class="form-row">
          <label class="form-label" for="userName">Name</label>
          <input class="form-input" id="userName" type="text" placeholder="Your name" required minlength="2"/>
        </div>
        <div class="form-row">
          <label class="form-label" for="userMobile">Mobile</label>
          <input class="form-input" id="userMobile" type="tel" placeholder="10-digit mobile" required pattern="[0-9]{10}"/>
        </div>
        <div class="form-row">
          <label class="form-label" for="userEmail">Email</label>
          <input class="form-input" id="userEmail" type="email" placeholder="you@example.com" required/>
        </div>
        <div class="onboard-actions">
          <button type="button" class="start-btn" id="startChat">Start Chat</button>
        </div>
      </form>
    </div>
  </div>
</section>
<script>
(function(){
  var chips=document.querySelectorAll('.ai .chip');
  var input=document.getElementById('aiInput');
  var send=document.getElementById('aiSend');
  var thread=document.getElementById('aiThread');
  var stop=document.getElementById('aiStop');
  var onboard=document.getElementById('aiOnboard');
  var form=document.getElementById('aiOnboardForm');
  var start=document.getElementById('startChat');
  var nameInput=document.getElementById('userName');
  var mobileInput=document.getElementById('userMobile');
  var emailInput=document.getElementById('userEmail');
  var typing=false; var timer=null; var userName=null; var controller=null; var attachedImage=null; var convo=[{role:'system',content:'You are MG EDU AI. Be concise, helpful, and friendly.'}];
  function add(role,text,img){
    var wrap=document.createElement('div');wrap.className='msg'+(role==='user'?' user':'');
    var av=document.createElement('img');av.className='avatar';av.alt=role==='user'?'You':'Assistant';
    av.src=role==='user'?'../assets/images/mg-logo.jpg':'../assets/images/mg-logo.jpg';
    var b=document.createElement('div');b.className='bubble'+(role==='assistant'?' assistant':' user');
    var p=document.createElement('p');p.textContent=text||'';
    b.appendChild(p);
    if(img){
      var im=document.createElement('img'); im.src=img; im.alt='attachment'; im.style.maxWidth='240px'; im.style.borderRadius='12px'; im.style.marginTop='6px'; im.style.border='1px solid #e6e8ee';
      b.appendChild(im);
    }
    wrap.appendChild(av);wrap.appendChild(b);thread.appendChild(wrap);thread.scrollTop=thread.scrollHeight;
  }
  function greet(n){
    add('assistant','Hi '+n+'! I’m MG EDU AI. Ask me anything about courses, careers, or skills.');
  }
  chips.forEach(function(c){c.addEventListener('click',function(){input.value=c.getAttribute('data-prompt')})});
  var imgInput=document.getElementById('aiImage'); var attach=document.getElementById('aiAttach');
  attach.addEventListener('click',function(){imgInput.click()});
  imgInput.addEventListener('change',function(){
    var f=imgInput.files&&imgInput.files[0]; if(!f)return;
    var r=new FileReader(); r.onload=function(){attachedImage=r.result}; r.readAsDataURL(f);
  });
  input.disabled=true; send.disabled=true; stop.disabled=true; chips.forEach(function(c){c.disabled=true});
  start.addEventListener('click',function(){
    if(!form.checkValidity()){form.reportValidity();return}
    var n=nameInput.value.trim();
    onboard.style.display='none';
    input.disabled=false; send.disabled=false; stop.disabled=false; chips.forEach(function(c){c.disabled=false});
    userName=n; convo.push({role:'system',content:'User name: '+n}); greet(n);
  });
  function reply(prompt){
    var accum=''; typing=true; controller=new AbortController();
    var userMsg={role:'user'};
    if(attachedImage){ userMsg.content=[{type:'text',text:prompt},{type:'image_url',image_url:{url:attachedImage}}]; }
    else { userMsg.content=prompt; }
    convo.push(userMsg); attachedImage=null;
    fetch('chat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({messages:convo,model:'openrouter/auto',stream:true}),signal:controller.signal}).then(function(resp){
      if(!resp.ok){
        resp.text().then(function(t){
          try{var j=JSON.parse(t); if(j.error==='missing_api_key'){ add('assistant','API key not configured. Set environment variable OPENROUTER_API_KEY and restart Apache/WAMP.'); }
          else { add('assistant','Server error: '+t.slice(0,200)); }}catch(e){ add('assistant','Network/setup error. Check internet and API key.'); }
          typing=false;
        });
        return;
      }
      var reader=resp.body.getReader(); var decoder=new TextDecoder('utf-8'); add('assistant',''); var buf=''; var gotDelta=false;
      function pump(){
        return reader.read().then(function(r){
          if(r.done){typing=false; if(!gotDelta){
            fetch('chat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({messages:convo,model:'openrouter/auto',stream:false})}).then(function(rr){return rr.json()}).then(function(j){
              if(j&&j.error){
                var e=j.error; var m;
                if(typeof e==='string'){m=e}
                else if(e&&typeof e==='object'){m=e.message||e.error||e.detail||JSON.stringify(e)}
                else{m='Unknown error'}
                thread.lastElementChild.querySelector('.bubble p').textContent='Error: '+String(m).slice(0,280); return;
              }
              var txt=(j&&j.choices&&j.choices[0]&&j.choices[0].message&&j.choices[0].message.content)||'No response';
              thread.lastElementChild.querySelector('.bubble p').textContent=txt; convo.push({role:'assistant',content:txt});
            }).catch(function(){thread.lastElementChild.querySelector('.bubble p').textContent='Network error';});
          } else { convo.push({role:'assistant',content:accum}); }
          return;}
          buf+=decoder.decode(r.value,{stream:true});
          var parts=buf.split('\n\n'); buf=parts.pop();
          for(var i=0;i<parts.length;i++){
            var line=parts[i].trim();
            if(line.indexOf('data:')===0){
              var s=line.slice(5).trim(); if(s==='[DONE]')continue; var o=null; try{o=JSON.parse(s)}catch(e){o=null}
              var d=o&&o.choices&&o.choices[0]&&o.choices[0].delta&&o.choices[0].delta.content?o.choices[0].delta.content:'';
              if(o&&o.error){
                var e=o.error; var msg;
                if(typeof e==='string'){msg=e}
                else if(e&&typeof e==='object'){msg=e.message||e.error||e.detail||e.type||JSON.stringify(e)}
                else{msg=s}
                thread.lastElementChild.querySelector('.bubble p').textContent='Error: '+String(msg).slice(0,280);
                typing=false; continue;
              }
              if(d){accum+=d; gotDelta=true; thread.lastElementChild.querySelector('.bubble p').textContent=accum; thread.scrollTop=thread.scrollHeight}
            }
          }
          return pump();
        });
      }
      return pump();
    }).catch(function(){typing=false});
  }
  send.addEventListener('click',function(){
    var v=input.value.trim(); if(!v&&!attachedImage)return; add('user',v,attachedImage); input.value=''; reply(v||'');
  });
  stop.addEventListener('click',function(){typing=false; if(controller){controller.abort()} if(timer){clearInterval(timer)}});
})();
</script>
