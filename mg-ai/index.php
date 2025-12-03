<?php
$scheme=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
$host=isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost';
$origin=$scheme.'://'.$host; // no trailing slash
$rootPath=rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\'); // e.g. /mg-skill
$baseHref=$origin.$rootPath.'/';
$logo=$baseHref.'assets/images/logo.jpg';
$userAvatar=$baseHref.'assets/images/rahul-1.webp';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"/>
  <title>MG AI • Chat</title>
  <meta name="description" content="MG AI chat with a clean, Instagram-like interface in light mode."/>
  <link rel="preload" as="image" href="<?php echo htmlspecialchars($logo,ENT_QUOTES,'UTF-8'); ?>"/>
  <base href="<?php echo htmlspecialchars($baseHref,ENT_QUOTES,'UTF-8'); ?>">
  <style>
    *{box-sizing:border-box}
    html,body{height:100%}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Helvetica,Arial,'Noto Sans','Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';color:#0f1419;background:#ffffff}
    img{max-width:100%;display:block}
    a{text-decoration:none;color:inherit}
  </style>
</head>
<body>
  <?php require_once __DIR__.'/../includes/header.php'; ?>
  <style>
  :root{--brand:#1358db;--accent:#b2560a;--line:#e6e8ee;--muted:#6f7787}
  .ai{background:#fff}
  .site-footer{--cta-offset:80px}
  .ai-wrap{max-width:1200px;margin:0 auto;padding:8px 16px 12px;min-height:0}
  .ai-grid{display:grid;grid-template-columns:1.7fr 1fr;gap:16px}
  .chat-card{position:relative;border:1px solid var(--line);background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);border-radius:18px;overflow:hidden;height:calc(100dvh - 160px);display:grid;grid-template-rows:auto 1fr auto}
  .chat-head{display:flex;align-items:center;gap:12px;padding:12px 14px;border-bottom:1px solid var(--line);background:#fafbff}
  .brand-chip{display:flex;align-items:center;gap:10px}
  .brand-avatar{height:40px;width:40px;border-radius:10px;border:1px solid #d0d6e3;overflow:hidden}
  .brand-title{font-weight:800;color:#0b1020;margin:0}
  .accent-gradient{background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
  .sub{color:#6f7787;font-weight:600}
  .chat-scroll{overflow-y:auto;padding:16px;background:transparent;overscroll-behavior:contain;scroll-padding-bottom:12px}
  .day-divider{display:flex;align-items:center;gap:12px;margin:12px 0;color:#6f7787;font-weight:600}
  .day-divider:before,.day-divider:after{content:"";flex:1;height:1px;background:var(--line)}
  .msg{max-width:72%;display:flex;gap:10px;margin:10px 0}
  .msg.bot{align-items:flex-end}
  .msg.user{margin-left:auto;flex-direction:row-reverse}
  .bubble{padding:10px 12px;border-radius:18px;border:1px solid var(--line);background:#fff;color:#0f1419;line-height:1.5}
  .msg.user .bubble{background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);color:#fff;border-color:transparent;box-shadow:0 10px 20px rgba(91,97,255,.18)}
  .avatar{height:28px;width:28px;border-radius:10px;border:1px solid #d0d6e3;overflow:hidden;flex-shrink:0}
  .tools{display:flex;gap:8px;margin-left:auto}
  .tool{height:30px;width:30px;border-radius:10px;border:1px solid var(--line);background:#fff;display:flex;align-items:center;justify-content:center}
  .tool svg{height:16px;width:16px;stroke:#475569;fill:none;stroke-width:2}
  .typing{display:inline-flex;gap:4px}
  .dot{height:6px;width:6px;border-radius:999px;background:#b9c3d7;animation:blink 1s infinite}
  .dot:nth-child(2){animation-delay:.2s}.dot:nth-child(3){animation-delay:.4s}
  @keyframes blink{0%,80%,100%{opacity:.3}40%{opacity:.9}}
  .composer{background:#fff;border-top:1px solid var(--line);padding:10px}
  .compose{display:grid;grid-template-columns:auto auto 1fr auto;gap:8px;align-items:center}
  .attach,.mic{height:36px;width:36px;border-radius:10px;border:1px solid var(--line);background:#fff;display:flex;align-items:center;justify-content:center}
  .mic.is-rec{border-color:#cfe0ff;background:#f8fbff}
  .send{height:36px;padding:0 14px;border-radius:10px;background:#1358db;color:#fff;font-weight:800;border:0}
  .compose textarea{min-height:40px;max-height:140px;resize:vertical;border:1px solid var(--line);border-radius:12px;padding:10px;background:#fff;color:#0f1419}
  .sidebar{border:1px solid var(--line);background:#fff;border-radius:18px;overflow:hidden;display:grid;grid-template-rows:auto 1fr;height:calc(100dvh - 160px)}
  .sb-head{padding:12px 14px;border-bottom:1px solid var(--line);background:#fafbff;font-weight:800}
  .sb-body{padding:12px;overflow-y:auto;overscroll-behavior:contain}
  .prompt-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  .prompt{padding:10px 12px;border:1px solid var(--line);border-radius:12px;background:#fff;color:#2b313b;font-weight:700}
  .prompt:hover{border-color:#cfe0ff;background:#f8fbff}
  .chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
  .chip{padding:8px 12px;border:1px solid var(--line);border-radius:999px;background:#fff;color:#0a50c9;font-weight:700}
  .history{display:grid;gap:8px;margin-top:12px}
  .history-item{display:flex;gap:10px;align-items:center;padding:10px;border:1px solid var(--line);border-radius:12px}
  .history-item .h-title{font-weight:700;color:#0b1020}
  .history-item .h-sub{color:#6f7787}
  @media(max-width:1024px){.ai-grid{grid-template-columns:1fr}.sidebar{order:-1}}
  @media(max-width:640px){.msg{max-width:86%}.chat-card{height:calc(100dvh - 140px)}.sidebar{height:calc(100dvh - 140px)}.prompt-grid{grid-template-columns:1fr}}
  </style>
  <section class="ai" aria-label="MG AI">
    <div class="ai-wrap">
      <div class="ai-grid">
        <div class="chat-card" id="chat">
          <div class="chat-head">
            <div class="brand-chip">
              <div class="brand-avatar"><img src="<?php echo htmlspecialchars($logo,ENT_QUOTES,'UTF-8'); ?>" alt="MG"/></div>
              <div>
                <p class="brand-title"><span class="accent-gradient">MG AI</span></p>
                <span class="sub">Light mode • Fast & helpful</span>
              </div>
            </div>
            <div class="tools">
              <button class="tool" title="New chat" id="newChat"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></button>
              <button class="tool" title="Settings"><svg viewBox="0 0 24 24"><path d="M12 3l2 2 3-1 1 3 3 1-2 2 2 2-3 1-1 3-3-1-2 2-2-2-3 1-1-3-3-1 2-2-2-2 3-1 1-3 3 1z"/></svg></button>
            </div>
          </div>
          <div class="chat-scroll" id="chatScroll" aria-live="polite">
            <div class="day-divider">Today</div>
            <div class="msg bot">
              <div class="avatar"><img src="<?php echo htmlspecialchars($logo,ENT_QUOTES,'UTF-8'); ?>" alt="MG"/></div>
              <div class="bubble">Hi! I’m MG AI. Ask me anything — resumes, roadmaps, cover letters, or code help.</div>
            </div>
          </div>
          <div class="composer">
            <form class="compose" id="composeForm">
              <button type="button" class="attach" title="Attach"><svg viewBox="0 0 24 24"><path d="M21 8a5 5 0 0 0-10 0v7a3 3 0 0 0 6 0V9"/></svg></button>
              <button type="button" class="mic" id="micBtn" title="Voice"><svg viewBox="0 0 24 24"><path d="M12 3a3 3 0 0 1 3 3v6a3 3 0 0 1-6 0V6a3 3 0 0 1 3-3"/><path d="M19 10a7 7 0 0 1-14 0"/><path d="M12 19v3"/></svg></button>
              <textarea id="composeInput" placeholder="Message MG AI" aria-label="Message"></textarea>
              <button class="send" type="submit">Send</button>
            </form>
          </div>
        </div>
        <aside class="sidebar">
          <div class="sb-head">Suggested</div>
          <div class="sb-body">
            <div class="prompt-grid">
              <button class="prompt" data-t="Create a resume summary for a web developer">Resume summary</button>
              <button class="prompt" data-t="Write a cover letter for a junior developer role">Cover letter</button>
              <button class="prompt" data-t="Give me a 12-week MERN roadmap">MERN roadmap</button>
              <button class="prompt" data-t="Explain this code in simple steps">Explain code</button>
            </div>
            <div class="chips">
              <button class="chip" data-t="How to prepare for interviews?">Interviews</button>
              <button class="chip" data-t="Make a study plan for 2 hours daily">Study plan</button>
              <button class="chip" data-t="Summarize a long text I’ll paste">Summarize</button>
              <button class="chip" data-t="Generate SEO keywords for a course page">SEO keywords</button>
            </div>
            <div class="history" id="history"></div>
          </div>
        </aside>
      </div>
    </div>
  </section>
  <?php require_once __DIR__.'/../includes/footer.php'; ?>
  <script>
  (function(){
    var chatScroll=document.getElementById('chatScroll');
    var form=document.getElementById('composeForm');
    var input=document.getElementById('composeInput');
    var mic=document.getElementById('micBtn');
    var history=document.getElementById('history');
    var newChat=document.getElementById('newChat');
    var storeKey='mg-ai-chat';
    var data=[];
    function save(){try{localStorage.setItem(storeKey,JSON.stringify(data));}catch(e){}
      renderHistory();}
    function load(){try{var s=localStorage.getItem(storeKey);if(s){data=JSON.parse(s)||[]}}catch(e){}
      renderHistory();}
    function scrollBottom(){if(chatScroll){chatScroll.scrollTop=chatScroll.scrollHeight}}
    function bubble(role,text){var wrap=document.createElement('div');wrap.className='msg '+role;
      var av=document.createElement('div');av.className='avatar';var img=document.createElement('img');
      img.src=(role==='user')?'<?php echo htmlspecialchars($userAvatar,ENT_QUOTES,'UTF-8'); ?>':'<?php echo htmlspecialchars($logo,ENT_QUOTES,'UTF-8'); ?>';
      img.alt=role==='user'?'You':'MG';
      var b=document.createElement('div');b.className='bubble';b.textContent=text;
      if(role==='bot'){av.appendChild(img);wrap.appendChild(av);wrap.appendChild(b);}else{av.appendChild(img);wrap.appendChild(b);wrap.appendChild(av);}return wrap;}
    function typing(){var wrap=document.createElement('div');wrap.className='msg bot';
      var av=document.createElement('div');av.className='avatar';var img=document.createElement('img');img.src='<?php echo htmlspecialchars($logo,ENT_QUOTES,'UTF-8'); ?>';img.alt='MG';
      var b=document.createElement('div');b.className='bubble';var t=document.createElement('span');t.className='typing';
      var d1=document.createElement('span');d1.className='dot';var d2=document.createElement('span');d2.className='dot';var d3=document.createElement('span');d3.className='dot';
      t.appendChild(d1);t.appendChild(d2);t.appendChild(d3);b.appendChild(t);
      av.appendChild(img);wrap.appendChild(av);wrap.appendChild(b);return wrap;}
    function renderHistory(){if(!history)return;history.innerHTML='';
      data.slice().reverse().forEach(function(item){var el=document.createElement('div');el.className='history-item';
        var ic=document.createElement('div');ic.className='avatar';var im=document.createElement('img');im.src='<?php echo htmlspecialchars($logo,ENT_QUOTES,'UTF-8'); ?>';im.alt='MG';ic.appendChild(im);
        var tx=document.createElement('div');var t=document.createElement('div');t.className='h-title';t.textContent=item.q.slice(0,60);
        var s=document.createElement('div');s.className='h-sub';s.textContent=(item.a||'').slice(0,72);
        tx.appendChild(t);tx.appendChild(s);el.appendChild(ic);el.appendChild(tx);
        el.addEventListener('click',function(){input.value=item.q;input.focus()});history.appendChild(el);});}
    function speak(text){try{var a=new Audio('mg-ai/api/tts.php?text='+encodeURIComponent(text));a.addEventListener('error',function(){console.warn('TTS failed');});a.play();}catch(e){console.warn('TTS exception',e);}}
    function reply(q){var t=typing();chatScroll.appendChild(t);scrollBottom();
      fetch('mg-ai/api/chat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message:q,history:data,model:'anthropic/claude-3-haiku'})})
        .then(function(r){return r.json().then(function(j){return {ok:r.ok,status:r.status,body:j}})})
        .then(function(res){t.remove();
          if(!res.ok){var d=(res.body&&res.body.detail)?(' '+res.body.detail):'';var a='Server error ('+res.status+').'+d;chatScroll.appendChild(bubble('bot',a));scrollBottom();return;}
          var a=(res.body&&res.body.reply)||'';chatScroll.appendChild(bubble('bot',a));scrollBottom();data.push({q:q,a:a,ts:Date.now()});save();speak(a);
        })
        .catch(function(e){t.remove();var a='Network error. Please check connection.';chatScroll.appendChild(bubble('bot',a));scrollBottom();});
    }
    form.addEventListener('submit',function(e){e.preventDefault();var v=input.value.trim();if(!v)return;
      chatScroll.appendChild(bubble('user',v));scrollBottom();input.value='';reply(v);
    });
    input.addEventListener('keydown',function(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();form.dispatchEvent(new Event('submit'));}});
    (function(){var SR=window.SpeechRecognition||window.webkitSpeechRecognition;if(!SR){if(mic){mic.style.display='none';}return;}var rec=new SR();rec.lang='en-IN';rec.interimResults=false;rec.maxAlternatives=1;var on=false;function stop(){rec.stop();on=false;mic&&mic.classList.remove('is-rec');}rec.addEventListener('result',function(e){var t=e.results[0][0].transcript||'';input.value=t;form.dispatchEvent(new Event('submit'));stop();});rec.addEventListener('end',function(){on=false;mic&&mic.classList.remove('is-rec');});mic&&mic.addEventListener('click',function(){if(on){stop();return;}on=true;mic.classList.add('is-rec');rec.start();});})();
    document.querySelectorAll('[data-t]').forEach(function(btn){btn.addEventListener('click',function(){input.value=btn.getAttribute('data-t');input.focus()})});
    newChat.addEventListener('click',function(){data=[];save();chatScroll.innerHTML='<div class="day-divider">Today</div>';chatScroll.appendChild(bubble('bot','New chat started. How can I help?'));scrollBottom();});
    load();
  })();
  </script>
</body>
</html>
