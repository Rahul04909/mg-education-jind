<?php
?>
<style>
.desc{background:#fff}
.desc-wrap{max-width:1200px;margin:0 auto;padding:12px 16px 18px}
.desc-card{border:1px solid #e6e8ee;background:#fff;border-radius:18px;padding:18px}
.desc-head{display:flex;align-items:center;gap:12px;margin-bottom:10px}
.desc-title{margin:0;color:#0b1020;font-size:22px;font-weight:800}
.desc-toggle{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:12px;background:#0f1419;color:#fff;font-weight:800;border:0}
.desc-footer{display:flex;align-items:center;justify-content:flex-start;margin-top:10px}
.desc-content{position:relative;color:#2b313b}
.collapsed{max-height:260px;overflow:hidden}
.collapsed::after{content:"";position:absolute;left:0;right:0;bottom:0;height:60px;background:linear-gradient(180deg,rgba(255,255,255,0) 0%, #fff 100%)}
.desc-content p{margin:10px 0}
.desc-content ul{margin:10px 0 0 0;padding-left:18px}
.desc-content li{margin:8px 0}
@media(max-width:900px){.desc-title{font-size:20px}.desc-wrap{padding:12px 12px 16px}}
@media(max-width:640px){.desc-title{font-size:18px}.desc-card{padding:14px}.desc-toggle{padding:8px 10px;font-weight:700}}
</style>
<section class="desc" aria-label="Course description">
  <div class="desc-wrap">
    <div class="desc-card">
      <div class="desc-head">
        <h3 class="desc-title">Description</h3>
      </div>
      <div id="descContent" class="desc-content collapsed">
        <p>Unlock the potential of Generative AI with our comprehensive course, "Gen AI Masters 2025 - From Python To LLMs and Deployment" This course is designed for both beginners and seasoned developers looking to deepen their understanding of the rapidly evolving field of artificial intelligence.</p>
        <p>Learn how to build Generative AI applications using Python and LLMs. Understand prompt engineering, explore vector databases like FAISS, and deploy real-world AI chatbots using RAG architecture.</p>
        <p>In this course, you will explore a wide range of essential topics, including:</p>
        <p><strong>Python Programming:</strong> Learn the fundamentals of Python, the go-to language for AI development, and become proficient in data manipulation using libraries like Pandas and NumPy.</p>
        <p><strong>Natural Language Processing (NLP):</strong> Dive into the world of NLP, mastering techniques for text processing, feature extraction, and leveraging powerful libraries like NLTK and SpaCy.</p>
        <p><strong>Deep Learning and Transformers:</strong> Understand the architecture of Transformer models, which are at the heart of many state-of-the-art AI applications. Discover the principles of deep learning and how to implement neural networks using TensorFlow and PyTorch.</p>
        <p><strong>Large Language Models (LLMs):</strong> Gain insights into LLMs, their training, fine-tuning processes (including PEFT, LoRA, and QLoRA), and learn how to effectively use these models in various applications, from chatbots to content generation.</p>
        <p><strong>Retrieval-Augmented Generation (RAGs):</strong> Explore the innovative concept of RAG, which combines retrieval techniques with generative models to enhance AI performance. You'll also learn about RAG evaluation methods, including the RAGAS framework, BLEU, ROUGE, BARScore, and BERTScore.</p>
        <p><strong>Prompt Engineering:</strong> Master the art of crafting effective prompts to improve interactions with LLMs and optimize outputs for specific tasks.</p>
        <p><strong>Vector Databases:</strong> Discover how to implement and utilize vector databases for storing and retrieving high-dimensional data, a crucial skill in managing AI-generated content.</p>
        <p>The course culminates in a Capstone Project, where you will apply everything you've learned to solve a real-world problem using Generative AI techniques.</p>
        <p><strong>Projects List:</strong></p>
        <ul>
          <li>AI Career Coach</li>
          <li>AI Powered Automated Claims Processing</li>
          <li>Chat Scholar Chatbot + Essay Grading System</li>
          <li>Research RAG Chatbot</li>
          <li>Sustainability Chatbot (GROK AI)</li>
        </ul>
        <p>If you have a specific project idea in mind, feel free to share it, and we will do our best to bring your vision to life.</p>
        <p>By the end of this course, you will have a solid foundation in Generative AI and the skills to implement complex AI solutions. Whether you're looking to enhance your career, transition into AI development, or simply explore this fascinating field, this course is your gateway to mastering Generative AI.</p>
        <p>Enroll now and take the first step toward becoming an expert in Generative AI!</p>
        <p><strong>Who this course is for:</strong></p>
        <ul>
          <li>Individuals passionate about AI and ML who want to expand their knowledge and skills in generative AI applications.</li>
          <li>Professionals looking to enhance their expertise in building and deploying generative AI models</li>
          <li>Developers interested in integrating advanced AI capabilities into their applications and learning about the deployment and optimization of AI models.</li>
        </ul>
      </div>
      <div class="desc-footer">
        <button id="descToggle" class="desc-toggle" type="button" aria-expanded="false" aria-controls="descContent">Show More</button>
      </div>
    </div>
  </div>
</section>
<script>
var btn=document.getElementById('descToggle');
var box=document.getElementById('descContent');
if(btn&&box){
  btn.addEventListener('click',function(){
    var ex=box.classList.toggle('collapsed');
    var expanded=!ex;
    btn.textContent=expanded?'Show Less':'Show More';
    btn.setAttribute('aria-expanded',expanded?'true':'false');
  });
}
</script>
