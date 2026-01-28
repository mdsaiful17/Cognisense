@extends('layouts.app')

@section('content')
<style>
  /* ===========================
     Cognisense CV Builder (Scoped)
     =========================== */
  .cs-cv, .cs-cv * { box-sizing:border-box; }
  .cs-cv a { text-decoration:none; }
  .cs-cv img { max-width:100%; height:auto; display:block; }
  .cs-cv .muted { color:#6b7280; }

  .cs-cv{
    padding: 26px 18px;
    background: radial-gradient(900px 380px at 15% 0%, rgba(14,165,233,.12), transparent 55%),
                radial-gradient(900px 380px at 85% 0%, rgba(124,58,237,.12), transparent 55%),
                #f3f4f6;
    min-height: calc(100vh - 60px);
  }

  .cs-cv .wrap{ max-width:1120px; margin:0 auto; }

  .cs-cv .hero{
    position:relative;
    overflow:hidden;
    border-radius: 20px;
    padding: 20px 20px;
    color:#fff;
    background: linear-gradient(135deg,#0ea5e9 0%,#7c3aed 55%,#111827 100%);
    box-shadow: 0 18px 50px rgba(0,0,0,.18);
  }
  .cs-cv .hero:before{
    content:"";
    position:absolute; inset:-120px auto auto -120px;
    width: 340px; height: 340px;
    background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.22), transparent 60%);
    transform: rotate(-10deg);
  }

  .cs-cv .heroTop{
    display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:flex-start;
    position:relative; z-index:1;
  }

  .cs-cv .title{ font-size: 26px; margin:0; line-height:1.15; }
  .cs-cv .pill{
    font-size:12px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.22);
    padding: 4px 10px;
    border-radius: 999px;
    color:#fff;
    white-space: nowrap;
  }

  .cs-cv .card{
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius: 16px;
    padding: 16px;
    box-shadow: 0 10px 22px rgba(17,24,39,.06);
    margin-top: 14px;
  }

  .cs-cv .btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    border-radius:12px;
    padding:10px 14px;
    border:1px solid #e5e7eb;
    color:#111827;
    background:#fff;
    cursor:pointer;
    transition:.15s ease;
    white-space:nowrap;
  }
  .cs-cv .btn:hover{ transform:translateY(-1px); box-shadow: 0 10px 18px rgba(17,24,39,.08); }
  .cs-cv .btn.primary{ background:#2563eb; color:#fff; border-color:#2563eb; }
  .cs-cv .btn.danger{ background:#ef4444; color:#fff; border-color:#ef4444; }
  .cs-cv .btn.dark{ background:#111827; color:#fff; border-color:#111827; }
  .cs-cv .btn.small{ padding:7px 10px; border-radius:10px; font-size:13px; }

  .cs-cv .err{ border-left:6px solid #ef4444; }

  .cs-cv label{
    font-size: 13px;
    color:#374151;
    font-weight: 900;
    display:block;
    margin-bottom: 6px;
  }

  .cs-cv input, .cs-cv textarea, .cs-cv select{
    width:100%;
    max-width:100%;
    border:1px solid #e5e7eb;
    border-radius: 12px;
    padding: 10px 12px;
    font-size: 14px;
    outline:none;
    background:#fff;
  }
  .cs-cv textarea{ min-height: 92px; resize: vertical; }
  .cs-cv input:focus, .cs-cv textarea:focus, .cs-cv select:focus{
    border-color:#93c5fd;
    box-shadow:0 0 0 3px rgba(59,130,246,.15);
  }

  .cs-cv .hint{ font-size: 12px; color:#64748b; margin-top:6px; line-height:1.45; }

  .cs-cv .divider{ height:1px; background:#e5e7eb; margin: 14px 0; }

  .cs-cv .sectionHead{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
  }

  .cs-cv .grid{
    display:grid;
    grid-template-columns: repeat(12, minmax(0,1fr));
    gap: 12px;
    margin-top: 12px;
  }
  .cs-cv .grid > *{ min-width:0; }

  .cs-cv .col3{ grid-column: span 3; }
  .cs-cv .col4{ grid-column: span 4; }
  .cs-cv .col5{ grid-column: span 5; }
  .cs-cv .col6{ grid-column: span 6; }
  .cs-cv .col7{ grid-column: span 7; }
  .cs-cv .col8{ grid-column: span 8; }
  .cs-cv .col9{ grid-column: span 9; }
  .cs-cv .col12{ grid-column: span 12; }

  .cs-cv .twoCol{
    display:grid;
    grid-template-columns: 1.1fr .9fr;
    gap: 12px;
    margin-top: 12px;
    position:relative;
    z-index:1;
  }

  .cs-cv .tipsBox{
    background: rgba(255,255,255,.10);
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 16px;
    padding: 12px;
  }
  .cs-cv .tipsBox b{ display:block; margin-bottom: 6px; }
  .cs-cv .tipsBox ul{ margin: 8px 0 0; padding-left: 18px; }
  .cs-cv .tipsBox li{ margin: 7px 0; opacity:.95; }

  .cs-cv .item{
    border: 1px dashed #cbd5e1;
    border-radius: 14px;
    padding: 12px;
    background: #f8fafc;
    margin-top: 10px;
  }

  .cs-cv .stickyBar{
    position: sticky;
    bottom: 12px;
    z-index: 20;
    margin-top: 16px;
    background: rgba(255,255,255,.92);
    backdrop-filter: blur(10px);
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 12px;
    box-shadow: 0 10px 22px rgba(17,24,39,.10);
    display:flex;
    justify-content:space-between;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
  }

  /* prevent sticky bar covering last section */
  .cs-cv form{ padding-bottom: 96px; }

  @media(max-width:980px){
    .cs-cv .twoCol{ grid-template-columns: 1fr; }
    .cs-cv .col3,.cs-cv .col4,.cs-cv .col5,.cs-cv .col6,.cs-cv .col7,.cs-cv .col8,.cs-cv .col9{
      grid-column: span 12;
    }
    .cs-cv .title{ font-size: 22px; }
  }
</style>

<div class="cs-cv">
  <div class="wrap">

    <div class="hero">
      <div class="heroTop">
        <div style="max-width:72ch">
          <h1 class="title">Build Your CV — {{ $template->name }}</h1>
          <div style="opacity:.92;margin-top:6px;line-height:1.5">
            Fill once, generate a polished PDF instantly. Use achievement bullets (Action + Tool + Result).
          </div>
          <div style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <span class="pill">Template: {{ $template->slug }}</span>
            @if(!empty($template->accent_color))
              <span class="pill">Accent: {{ $template->accent_color }}</span>
            @endif
          </div>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <a class="btn dark" href="{{ route('cv.index') }}">← Back to Templates</a>
          <a class="btn" href="{{ route('dashboard') }}">← Dashboard</a>
        </div>
      </div>

      <div class="twoCol">
        <div class="tipsBox">
          <b>Winner CV formula</b>
          <ul>
            <li><b>Impact bullets:</b> “Built X using Y, improved Z by 30%”.</li>
            <li><b>ATS keywords:</b> match job skills/tools (honestly).</li>
            <li><b>Clean structure:</b> consistent dates + headings.</li>
            <li><b>Projects:</b> show stack + outcomes + links.</li>
          </ul>
        </div>
        <div class="tipsBox">
          <b>What Cognisense makes easy</b>
          <ul>
            <li>Premium templates + readable hierarchy</li>
            <li>Repeatable sections (Experience/Projects/etc.)</li>
            <li>Instant PDF open + download from CV panel</li>
            <li>One place to keep versions</li>
          </ul>
        </div>
      </div>
    </div>

    @if ($errors->any())
      <div class="card err">
        <b>Fix these issues</b>
        <ul style="margin:10px 0 0;padding-left:18px;color:#b91c1c">
          @foreach ($errors->all() as $error)
            <li style="margin:6px 0">{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if(session('error'))
      <div class="card err">
        <b>Error</b>
        <div class="muted" style="margin-top:6px">{{ session('error') }}</div>
      </div>
    @endif

    <form class="card" method="POST" action="{{ route('cv.generate', ['slug' => $template->slug]) }}">
      @csrf

      <div class="sectionHead">
        <h3 style="margin:0">Core Profile</h3>
        <span class="muted" style="font-size:13px">Tip: headline should be specific (Role · Domain · Tools)</span>
      </div>

      <div class="grid">
        <div class="col8">
          <label>CV Title (optional)</label>
          <input name="cv_title" value="{{ old('cv_title') }}" placeholder="e.g., John Doe - Software Engineer CV">
          <div class="hint">If empty, we auto-generate: “Your Name - CV”.</div>
        </div>

        <div class="col4">
          <label>Headline / Target Role</label>
          <input name="headline" value="{{ old('headline') }}" placeholder="Backend Engineer · Laravel · APIs">
        </div>

        <div class="col6">
          <label>Full Name *</label>
          <input name="full_name" required value="{{ old('full_name') }}" placeholder="Your full name">
        </div>

        <div class="col6">
          <label>Location</label>
          <input name="location" value="{{ old('location') }}" placeholder="Dhaka, Bangladesh">
        </div>

        <div class="col6">
          <label>Email</label>
          <input name="email" value="{{ old('email') }}" placeholder="you@email.com">
        </div>

        <div class="col6">
          <label>Phone</label>
          <input name="phone" value="{{ old('phone') }}" placeholder="+880...">
        </div>

        <div class="col4">
          <label>Website / Portfolio</label>
          <input name="website" value="{{ old('website') }}" placeholder="https://portfolio.com">
        </div>

        <div class="col4">
          <label>LinkedIn</label>
          <input name="linkedin" value="{{ old('linkedin') }}" placeholder="https://linkedin.com/in/...">
        </div>

        <div class="col4">
          <label>GitHub</label>
          <input name="github" value="{{ old('github') }}" placeholder="https://github.com/...">
        </div>

        <div class="col6">
          <label>Date of Birth (optional)</label>
          <input name="dob" value="{{ old('dob') }}" placeholder="e.g., 12 Jan 2003">
          <div class="hint">Optional (ATS doesn’t require it). Add only if needed in your region.</div>
        </div>

        <div class="col6">
          <label>Nationality (optional)</label>
          <input name="nationality" value="{{ old('nationality') }}" placeholder="Bangladeshi">
        </div>

        <div class="col12">
          <label>Other Links (optional; one per line)</label>
          <textarea name="other_links" placeholder="https://behance.net/...\nhttps://medium.com/@...">{{ old('other_links') }}</textarea>
        </div>

        <div class="col12">
          <label>Professional Summary</label>
          <textarea name="summary" placeholder="2–4 lines: what you do + strengths + proof (numbers)">{{ old('summary') }}</textarea>
          <div class="hint">
            Example: “Backend engineer with 2+ years building Laravel APIs. Improved query performance by 35% and shipped 10+ features...”
          </div>
        </div>
      </div>

      <div class="divider"></div>

      <div class="sectionHead">
        <h3 style="margin:0">Skills (ATS-Friendly)</h3>
        <span class="muted" style="font-size:13px">Comma or newline separated</span>
      </div>

      <div class="grid">
        <div class="col6">
          <label>Technical Skills</label>
          <textarea name="skills_technical" placeholder="Laravel, PHP, MySQL, REST, Docker, Git">{{ old('skills_technical') }}</textarea>
        </div>
        <div class="col6">
          <label>Soft Skills</label>
          <textarea name="skills_soft" placeholder="Communication, Teamwork, Problem Solving">{{ old('skills_soft') }}</textarea>
        </div>
        <div class="col12">
          <label>Keywords (optional: job-description keywords)</label>
          <textarea name="keywords" placeholder="Microservices, CI/CD, Unit Testing...">{{ old('keywords') }}</textarea>
        </div>
      </div>

      {{-- Repeaters --}}
      <div class="card" style="margin-top:16px">
        <div class="sectionHead">
          <h3 style="margin:0">Experience</h3>
          <button type="button" class="btn small primary" onclick="addItem('exp')">+ Add Experience</button>
        </div>
        <div class="muted" style="margin-top:6px;font-size:13px">
          Bullet format: <b>Action + Tool + Result</b>. One bullet per line.
        </div>
        <div id="exp-wrap"></div>
      </div>

      <div class="card" style="margin-top:16px">
        <div class="sectionHead">
          <h3 style="margin:0">Education</h3>
          <button type="button" class="btn small primary" onclick="addItem('edu')">+ Add Education</button>
        </div>
        <div id="edu-wrap"></div>
      </div>

      <div class="card" style="margin-top:16px">
        <div class="sectionHead">
          <h3 style="margin:0">Projects</h3>
          <button type="button" class="btn small primary" onclick="addItem('proj')">+ Add Project</button>
        </div>
        <div class="muted" style="margin-top:6px;font-size:13px">
          For Cognisense: include stack, what problem you solved, and measurable outcome.
        </div>
        <div id="proj-wrap"></div>
      </div>

      <div class="card" style="margin-top:16px">
        <div class="sectionHead">
          <h3 style="margin:0">Certifications</h3>
          <button type="button" class="btn small primary" onclick="addItem('cert')">+ Add Certification</button>
        </div>
        <div id="cert-wrap"></div>
      </div>

      <div class="card" style="margin-top:16px">
        <div class="sectionHead">
          <h3 style="margin:0">Awards</h3>
          <button type="button" class="btn small primary" onclick="addItem('award')">+ Add Award</button>
        </div>
        <div id="award-wrap"></div>
      </div>

      <div class="card" style="margin-top:16px">
        <div class="sectionHead">
          <h3 style="margin:0">Languages</h3>
          <button type="button" class="btn small primary" onclick="addItem('lang')">+ Add Language</button>
        </div>
        <div id="lang-wrap"></div>
      </div>

      <div class="card" style="margin-top:16px">
        <div class="sectionHead">
          <h3 style="margin:0">Volunteering</h3>
          <button type="button" class="btn small primary" onclick="addItem('vol')">+ Add Volunteering</button>
        </div>
        <div id="vol-wrap"></div>
      </div>

      <div class="card" style="margin-top:16px">
        <div class="sectionHead">
          <h3 style="margin:0">Additional Sections</h3>
          <button type="button" class="btn small primary" onclick="addItem('add')">+ Add Section</button>
        </div>
        <div class="muted" style="margin-top:6px;font-size:13px">
          Examples: Publications, Interests, References (optional), Extra Activities.
        </div>
        <div id="add-wrap"></div>
      </div>

      <div class="stickyBar">
        <div>
          <b>Ready?</b>
          <div class="muted" style="font-size:13px;margin-top:2px">Generate PDF, then open/download from CV panel.</div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <button class="btn primary" type="submit">✅ Generate My CV PDF</button>
          <a class="btn" href="{{ route('cv.index') }}">← Back to Templates</a>
        </div>
      </div>

    </form>
  </div>
</div>

{{-- Your JS stays the same (unchanged) --}}
<script>
  const counters = { exp:0, edu:0, proj:0, cert:0, award:0, lang:0, vol:0, add:0 };

  function esc(s){
    s = (s ?? '').toString();
    return s
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function removeItem(btn){ btn.closest('.item').remove(); }

  function addItem(type, data = null){
    const i = counters[type]++;
    data = data || {};

    if(type==='exp'){
      const html = `
        <div class="item">
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap">
            <b>Experience #${i+1}</b>
            <button type="button" class="btn small danger" onclick="removeItem(this)">Remove</button>
          </div>
          <div class="grid" style="margin-top:10px">
            <div class="col6">
              <label>Role / Position</label>
              <input name="experiences[${i}][role]" value="${esc(data.role)}" placeholder="Backend Engineer">
            </div>
            <div class="col6">
              <label>Company / Organization</label>
              <input name="experiences[${i}][company]" value="${esc(data.company)}" placeholder="Company name">
            </div>
            <div class="col4">
              <label>Location</label>
              <input name="experiences[${i}][location]" value="${esc(data.location)}" placeholder="Dhaka">
            </div>
            <div class="col4">
              <label>Start</label>
              <input name="experiences[${i}][start]" value="${esc(data.start)}" placeholder="Jan 2024">
            </div>
            <div class="col4">
              <label>End</label>
              <input name="experiences[${i}][end]" value="${esc(data.end)}" placeholder="Present">
            </div>
            <div class="col12">
              <label>Details (one bullet per line)</label>
              <textarea name="experiences[${i}][details]" placeholder="Built REST APIs in Laravel...\nReduced query time by 35%...\nImplemented JWT auth...">${esc(data.details)}</textarea>
              <div class="hint">Strong bullets include metrics: users, %, time, money, performance.</div>
            </div>
          </div>
        </div>
      `;
      document.getElementById('exp-wrap').insertAdjacentHTML('beforeend', html);
      return;
    }

    if(type==='edu'){
      const html = `
        <div class="item">
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap">
            <b>Education #${i+1}</b>
            <button type="button" class="btn small danger" onclick="removeItem(this)">Remove</button>
          </div>
          <div class="grid" style="margin-top:10px">
            <div class="col6">
              <label>Degree</label>
              <input name="education[${i}][degree]" value="${esc(data.degree)}" placeholder="BSc in Software Engineering">
            </div>
            <div class="col6">
              <label>Institution</label>
              <input name="education[${i}][institution]" value="${esc(data.institution)}" placeholder="Your University">
            </div>
            <div class="col4">
              <label>Start</label>
              <input name="education[${i}][start]" value="${esc(data.start)}" placeholder="2022">
            </div>
            <div class="col4">
              <label>End</label>
              <input name="education[${i}][end]" value="${esc(data.end)}" placeholder="2026">
            </div>
            <div class="col4">
              <label>CGPA / Result</label>
              <input name="education[${i}][cgpa]" value="${esc(data.cgpa)}" placeholder="3.80/4.00">
            </div>
            <div class="col12">
              <label>Highlights (optional; one per line)</label>
              <textarea name="education[${i}][details]" placeholder="Relevant coursework: DBMS, SE...\nThesis: ...">${esc(data.details)}</textarea>
            </div>
          </div>
        </div>
      `;
      document.getElementById('edu-wrap').insertAdjacentHTML('beforeend', html);
      return;
    }

    if(type==='proj'){
      const html = `
        <div class="item">
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap">
            <b>Project #${i+1}</b>
            <button type="button" class="btn small danger" onclick="removeItem(this)">Remove</button>
          </div>
          <div class="grid" style="margin-top:10px">
            <div class="col7">
              <label>Project Name</label>
              <input name="projects[${i}][name]" value="${esc(data.name)}" placeholder="Cognisense - Skills Trainer & Certifier">
            </div>
            <div class="col5">
              <label>Project Link (optional)</label>
              <input name="projects[${i}][link]" value="${esc(data.link)}" placeholder="https://github.com/... or live demo">
            </div>
            <div class="col6">
              <label>Role (optional)</label>
              <input name="projects[${i}][role]" value="${esc(data.role)}" placeholder="Full-stack Developer">
            </div>
            <div class="col6">
              <label>Tech Stack (comma or newline)</label>
              <input name="projects[${i}][tech]" value="${esc(data.tech)}" placeholder="Laravel, MySQL, JS, FastAPI">
            </div>
            <div class="col12">
              <label>Details (one bullet per line)</label>
              <textarea name="projects[${i}][details]" placeholder="Built CV generator module with templates...\nImplemented PDF export...\nImproved UX...">${esc(data.details)}</textarea>
              <div class="hint">Mention: problem → what you built → measurable outcome → link.</div>
            </div>
          </div>
        </div>
      `;
      document.getElementById('proj-wrap').insertAdjacentHTML('beforeend', html);
      return;
    }

    if(type==='cert'){
      const html = `
        <div class="item">
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap">
            <b>Certification #${i+1}</b>
            <button type="button" class="btn small danger" onclick="removeItem(this)">Remove</button>
          </div>
          <div class="grid" style="margin-top:10px">
            <div class="col6">
              <label>Name</label>
              <input name="certifications[${i}][name]" value="${esc(data.name)}" placeholder="AWS Cloud Practitioner">
            </div>
            <div class="col4">
              <label>Issuer</label>
              <input name="certifications[${i}][issuer]" value="${esc(data.issuer)}" placeholder="Amazon">
            </div>
            <div class="col2">
              <label>Year</label>
              <input name="certifications[${i}][year]" value="${esc(data.year)}" placeholder="2025">
            </div>
            <div class="col12">
              <label>Link (optional)</label>
              <input name="certifications[${i}][link]" value="${esc(data.link)}" placeholder="https://...">
            </div>
          </div>
        </div>
      `;
      document.getElementById('cert-wrap').insertAdjacentHTML('beforeend', html);
      return;
    }

    if(type==='award'){
      const html = `
        <div class="item">
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap">
            <b>Award #${i+1}</b>
            <button type="button" class="btn small danger" onclick="removeItem(this)">Remove</button>
          </div>
          <div class="grid" style="margin-top:10px">
            <div class="col8">
              <label>Award Name</label>
              <input name="awards[${i}][name]" value="${esc(data.name)}" placeholder="Best Project Award">
            </div>
            <div class="col4">
              <label>Year</label>
              <input name="awards[${i}][year]" value="${esc(data.year)}" placeholder="2026">
            </div>
            <div class="col12">
              <label>Details (optional)</label>
              <textarea name="awards[${i}][details]" placeholder="What it was for / impact">${esc(data.details)}</textarea>
            </div>
          </div>
        </div>
      `;
      document.getElementById('award-wrap').insertAdjacentHTML('beforeend', html);
      return;
    }

    if(type==='lang'){
      const html = `
        <div class="item">
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap">
            <b>Language #${i+1}</b>
            <button type="button" class="btn small danger" onclick="removeItem(this)">Remove</button>
          </div>
          <div class="grid" style="margin-top:10px">
            <div class="col7">
              <label>Language</label>
              <input name="languages[${i}][name]" value="${esc(data.name)}" placeholder="English">
            </div>
            <div class="col5">
              <label>Level</label>
              <input name="languages[${i}][level]" value="${esc(data.level)}" placeholder="Fluent / Professional / Native">
            </div>
          </div>
        </div>
      `;
      document.getElementById('lang-wrap').insertAdjacentHTML('beforeend', html);
      return;
    }

    if(type==='vol'){
      const html = `
        <div class="item">
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap">
            <b>Volunteering #${i+1}</b>
            <button type="button" class="btn small danger" onclick="removeItem(this)">Remove</button>
          </div>
          <div class="grid" style="margin-top:10px">
            <div class="col6">
              <label>Role</label>
              <input name="volunteering[${i}][role]" value="${esc(data.role)}" placeholder="Organizer">
            </div>
            <div class="col6">
              <label>Organization</label>
              <input name="volunteering[${i}][org]" value="${esc(data.org)}" placeholder="Club / NGO">
            </div>
            <div class="col4">
              <label>Year</label>
              <input name="volunteering[${i}][year]" value="${esc(data.year)}" placeholder="2024">
            </div>
            <div class="col8">
              <label>Details (optional)</label>
              <textarea name="volunteering[${i}][details]" placeholder="What you did + impact">${esc(data.details)}</textarea>
            </div>
          </div>
        </div>
      `;
      document.getElementById('vol-wrap').insertAdjacentHTML('beforeend', html);
      return;
    }

    if(type==='add'){
      const html = `
        <div class="item">
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap">
            <b>Section #${i+1}</b>
            <button type="button" class="btn small danger" onclick="removeItem(this)">Remove</button>
          </div>
          <div class="grid" style="margin-top:10px">
            <div class="col5">
              <label>Title</label>
              <input name="additional[${i}][title]" value="${esc(data.title)}" placeholder="Publications / Interests / References">
            </div>
            <div class="col7">
              <label>Details (one per line)</label>
              <textarea name="additional[${i}][details]" placeholder="Item 1...\nItem 2...">${esc(data.details)}</textarea>
            </div>
          </div>
        </div>
      `;
      document.getElementById('add-wrap').insertAdjacentHTML('beforeend', html);
      return;
    }
  }

  const oldExp   = @json(old('experiences', []));
  const oldEdu   = @json(old('education', []));
  const oldProj  = @json(old('projects', []));
  const oldCert  = @json(old('certifications', []));
  const oldAward = @json(old('awards', []));
  const oldLang  = @json(old('languages', []));
  const oldVol   = @json(old('volunteering', []));
  const oldAdd   = @json(old('additional', []));

  document.addEventListener('DOMContentLoaded', () => {
    if(Array.isArray(oldExp) && oldExp.length) oldExp.forEach(r => addItem('exp', r)); else addItem('exp');
    if(Array.isArray(oldEdu) && oldEdu.length) oldEdu.forEach(r => addItem('edu', r)); else addItem('edu');
    if(Array.isArray(oldProj) && oldProj.length) oldProj.forEach(r => addItem('proj', r)); else addItem('proj');

    if(Array.isArray(oldCert) && oldCert.length) oldCert.forEach(r => addItem('cert', r));
    if(Array.isArray(oldAward) && oldAward.length) oldAward.forEach(r => addItem('award', r));
    if(Array.isArray(oldLang) && oldLang.length) oldLang.forEach(r => addItem('lang', r));
    if(Array.isArray(oldVol) && oldVol.length) oldVol.forEach(r => addItem('vol', r));
    if(Array.isArray(oldAdd) && oldAdd.length) oldAdd.forEach(r => addItem('add', r));
  });
</script>
@endsection
