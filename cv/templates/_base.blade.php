@php
  $theme = $theme ?? [];
  $accent = $theme['accent'] ?? '#2563eb';
  $font = $theme['font'] ?? 'DejaVu Sans';
  $layout = $theme['layout'] ?? 'single'; // single | two
  $order = $theme['order'] ?? ['summary','skills','experience','projects','education','certifications','awards','volunteering','languages','additional'];

  $profile = $cv['profile'] ?? [];
  $skills = $cv['skills'] ?? [];
  $meta = $cv['meta'] ?? [];

  $has = function($k) use ($cv){
    $v = $cv[$k] ?? null;
    if (is_array($v)) return count($v) > 0;
    return !empty($v);
  };

  $fmtRange = function($a, $b){
    $a = trim((string)$a); $b = trim((string)$b);
    if($a==='' && $b==='') return '';
    if($a!=='' && $b!=='') return $a.' — '.$b;
    return $a!=='' ? $a : $b;
  };

  $join = function($arr){
    if(!is_array($arr)) return '';
    $arr = array_filter(array_map('trim', $arr), fn($x)=>$x!=='');
    return implode(', ', $arr);
  };

  $contactParts = [];
  foreach(['email','phone','location'] as $k){
    if(!empty($profile[$k])) $contactParts[] = $profile[$k];
  }
@endphp

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 28px; }
    body { font-family: {{ $font }}; font-size: 12px; color:#111827; }
    .h1 { font-size: 22px; font-weight: 800; margin:0; }
    .h2 { font-size: 12px; font-weight: 900; letter-spacing: .8px; color: {{ $accent }}; margin: 18px 0 8px; text-transform: uppercase; }
    .muted { color:#6b7280; }
    .line { height:1px; background:#e5e7eb; margin:10px 0 0; }
    .top { margin-bottom: 10px; }
    .headline { font-size: 13px; font-weight: 700; margin:4px 0 0; }
    .contacts { margin-top: 6px; }
    .link a { color:#111827; text-decoration:none; border-bottom:1px solid #e5e7eb; }
    .item { margin-bottom: 10px; }
    .itemTitle { font-weight: 900; font-size: 13px; margin:0; }
    .itemMeta { margin-top:2px; }
    ul { margin:6px 0 0 16px; padding:0; }
    li { margin:3px 0; }
    .tag { display:inline-block; padding:2px 8px; border:1px solid #e5e7eb; border-radius:999px; margin:2px 6px 2px 0; }
    .twoCol { width:100%; }
    .left { width: 64%; vertical-align: top; padding-right: 14px; }
    .right { width: 36%; vertical-align: top; padding-left: 14px; border-left:1px solid #e5e7eb; }
  </style>
</head>
<body>
  <div class="top">
    <div class="h1">{{ $profile['full_name'] ?? 'Your Name' }}</div>
    @if(!empty($profile['headline']))
      <div class="headline">{{ $profile['headline'] }}</div>
    @endif

    <div class="contacts muted">
      {{ implode(' · ', $contactParts) }}
      @php
        $links = [];
        foreach(['website','linkedin','github'] as $k){
          if(!empty($profile[$k])) $links[] = $profile[$k];
        }
        $otherLinks = $profile['other_links'] ?? [];
        if(is_array($otherLinks)) $links = array_merge($links, $otherLinks);
      @endphp
      @if(count($links))
        <div class="link" style="margin-top:6px">
          @foreach($links as $lnk)
            <span style="margin-right:10px"><a href="{{ $lnk }}">{{ $lnk }}</a></span>
          @endforeach
        </div>
      @endif
    </div>

    <div class="line"></div>
  </div>

  @if(($layout ?? 'single') === 'two')
    <table class="twoCol" cellspacing="0" cellpadding="0">
      <tr>
        <td class="left">
          {{-- MAIN (left) --}}
          @foreach($order as $sec)
            @if($sec==='summary' && !empty($profile['summary']))
              <div class="h2">Summary</div>
              <div>{{ $profile['summary'] }}</div>
            @endif

            @if($sec==='experience' && $has('experience'))
              <div class="h2">Experience</div>
              @foreach(($cv['experience'] ?? []) as $e)
                <div class="item">
                  <div class="itemTitle">
                    {{ $e['role'] ?? '' }}
                    @if(!empty($e['company'])) — {{ $e['company'] }} @endif
                  </div>
                  <div class="itemMeta muted">
                    {{ $fmtRange($e['start'] ?? '', $e['end'] ?? '') }}
                    @if(!empty($e['location'])) · {{ $e['location'] }} @endif
                  </div>
                  @if(!empty($e['bullets']) && is_array($e['bullets']))
                    <ul>
                      @foreach($e['bullets'] as $b)
                        <li>{{ $b }}</li>
                      @endforeach
                    </ul>
                  @endif
                </div>
              @endforeach
            @endif

            @if($sec==='projects' && $has('projects'))
              <div class="h2">Projects</div>
              @foreach(($cv['projects'] ?? []) as $p)
                <div class="item">
                  <div class="itemTitle">
                    {{ $p['name'] ?? '' }}
                    @if(!empty($p['link'])) <span class="muted">·</span> <span class="link"><a href="{{ $p['link'] }}">{{ $p['link'] }}</a></span> @endif
                  </div>
                  <div class="itemMeta muted">
                    @if(!empty($p['role'])) {{ $p['role'] }} @endif
                    @if(!empty($p['tech_list']) && is_array($p['tech_list'])) · {{ $join($p['tech_list']) }} @endif
                  </div>
                  @if(!empty($p['bullets']) && is_array($p['bullets']))
                    <ul>
                      @foreach($p['bullets'] as $b)
                        <li>{{ $b }}</li>
                      @endforeach
                    </ul>
                  @endif
                </div>
              @endforeach
            @endif

            @if($sec==='education' && $has('education'))
              <div class="h2">Education</div>
              @foreach(($cv['education'] ?? []) as $ed)
                <div class="item">
                  <div class="itemTitle">
                    {{ $ed['degree'] ?? '' }}
                    @if(!empty($ed['institution'])) — {{ $ed['institution'] }} @endif
                  </div>
                  <div class="itemMeta muted">
                    {{ $fmtRange($ed['start'] ?? '', $ed['end'] ?? '') }}
                    @if(!empty($ed['cgpa'])) · CGPA: {{ $ed['cgpa'] }} @endif
                  </div>
                  @if(!empty($ed['bullets']) && is_array($ed['bullets']))
                    <ul>
                      @foreach($ed['bullets'] as $b)
                        <li>{{ $b }}</li>
                      @endforeach
                    </ul>
                  @endif
                </div>
              @endforeach
            @endif

            @if($sec==='additional' && $has('additional'))
              <div class="h2">Additional</div>
              @foreach(($cv['additional'] ?? []) as $a)
                <div class="item">
                  <div class="itemTitle">{{ $a['title'] ?? '' }}</div>
                  @php
                    $lines = [];
                    $d = $a['details'] ?? '';
                    if(is_array($d)) $lines = $d;
                    else $lines = preg_split("/\r\n|\n|\r/", (string)$d);
                    $lines = array_values(array_filter(array_map('trim', $lines), fn($x)=>$x!==''));
                  @endphp
                  @if(count($lines))
                    <ul>
                      @foreach($lines as $ln)
                        <li>{{ $ln }}</li>
                      @endforeach
                    </ul>
                  @endif
                </div>
              @endforeach
            @endif
          @endforeach
        </td>

        <td class="right">
          {{-- SIDEBAR (right) --}}
          <div class="h2">Skills</div>
          @foreach(['technical'=>'Technical','soft'=>'Soft','keywords'=>'Keywords'] as $k => $label)
            @php $arr = $skills[$k] ?? []; @endphp
            @if(is_array($arr) && count($arr))
              <div style="margin-bottom:8px">
                <div style="font-weight:900">{{ $label }}</div>
                <div style="margin-top:4px">
                  @foreach($arr as $s)
                    <span class="tag">{{ $s }}</span>
                  @endforeach
                </div>
              </div>
            @endif
          @endforeach

          @if($has('certifications'))
            <div class="h2">Certifications</div>
            @foreach(($cv['certifications'] ?? []) as $c)
              <div style="margin-bottom:8px">
                <div style="font-weight:900">{{ $c['name'] ?? '' }}</div>
                <div class="muted">{{ ($c['issuer'] ?? '') }} @if(!empty($c['year'])) · {{ $c['year'] }} @endif</div>
              </div>
            @endforeach
          @endif

          @if($has('languages'))
            <div class="h2">Languages</div>
            @foreach(($cv['languages'] ?? []) as $l)
              <div style="margin-bottom:6px">
                <div style="font-weight:900">{{ $l['name'] ?? '' }}</div>
                @if(!empty($l['level'])) <div class="muted">{{ $l['level'] }}</div> @endif
              </div>
            @endforeach
          @endif

          @if($has('awards'))
            <div class="h2">Awards</div>
            @foreach(($cv['awards'] ?? []) as $a)
              <div style="margin-bottom:6px">
                <div style="font-weight:900">{{ $a['name'] ?? '' }}</div>
                <div class="muted">@if(!empty($a['year'])){{ $a['year'] }}@endif</div>
              </div>
            @endforeach
          @endif

          @if($has('volunteering'))
            <div class="h2">Volunteering</div>
            @foreach(($cv['volunteering'] ?? []) as $v)
              <div style="margin-bottom:8px">
                <div style="font-weight:900">{{ $v['role'] ?? '' }}</div>
                <div class="muted">
                  {{ $v['org'] ?? '' }}
                  @if(!empty($v['year'])) · {{ $v['year'] }} @endif
                </div>
              </div>
            @endforeach
          @endif
        </td>
      </tr>
    </table>

  @else
    {{-- SINGLE COLUMN --}}
    @foreach($order as $sec)
      @if($sec==='summary' && !empty($profile['summary']))
        <div class="h2">Summary</div>
        <div>{{ $profile['summary'] }}</div>
      @endif

      @if($sec==='skills')
        <div class="h2">Skills</div>
        @foreach(['technical'=>'Technical','soft'=>'Soft','keywords'=>'Keywords'] as $k => $label)
          @php $arr = $skills[$k] ?? []; @endphp
          @if(is_array($arr) && count($arr))
            <div style="margin-bottom:8px">
              <span style="font-weight:900">{{ $label }}:</span>
              <span class="muted">{{ $join($arr) }}</span>
            </div>
          @endif
        @endforeach
      @endif

      @if($sec==='experience' && $has('experience'))
        <div class="h2">Experience</div>
        @foreach(($cv['experience'] ?? []) as $e)
          <div class="item">
            <div class="itemTitle">
              {{ $e['role'] ?? '' }}
              @if(!empty($e['company'])) — {{ $e['company'] }} @endif
            </div>
            <div class="itemMeta muted">
              {{ $fmtRange($e['start'] ?? '', $e['end'] ?? '') }}
              @if(!empty($e['location'])) · {{ $e['location'] }} @endif
            </div>
            @if(!empty($e['bullets']) && is_array($e['bullets']))
              <ul>
                @foreach($e['bullets'] as $b)
                  <li>{{ $b }}</li>
                @endforeach
              </ul>
            @endif
          </div>
        @endforeach
      @endif

      @if($sec==='projects' && $has('projects'))
        <div class="h2">Projects</div>
        @foreach(($cv['projects'] ?? []) as $p)
          <div class="item">
            <div class="itemTitle">
              {{ $p['name'] ?? '' }}
              @if(!empty($p['link'])) <span class="muted">·</span> <span class="link"><a href="{{ $p['link'] }}">{{ $p['link'] }}</a></span> @endif
            </div>
            <div class="itemMeta muted">
              @if(!empty($p['role'])) {{ $p['role'] }} @endif
              @if(!empty($p['tech_list']) && is_array($p['tech_list'])) · {{ $join($p['tech_list']) }} @endif
            </div>
            @if(!empty($p['bullets']) && is_array($p['bullets']))
              <ul>
                @foreach($p['bullets'] as $b)
                  <li>{{ $b }}</li>
                @endforeach
              </ul>
            @endif
          </div>
        @endforeach
      @endif

      @if($sec==='education' && $has('education'))
        <div class="h2">Education</div>
        @foreach(($cv['education'] ?? []) as $ed)
          <div class="item">
            <div class="itemTitle">
              {{ $ed['degree'] ?? '' }}
              @if(!empty($ed['institution'])) — {{ $ed['institution'] }} @endif
            </div>
            <div class="itemMeta muted">
              {{ $fmtRange($ed['start'] ?? '', $ed['end'] ?? '') }}
              @if(!empty($ed['cgpa'])) · CGPA: {{ $ed['cgpa'] }} @endif
            </div>
            @if(!empty($ed['bullets']) && is_array($ed['bullets']))
              <ul>
                @foreach($ed['bullets'] as $b)
                  <li>{{ $b }}</li>
                @endforeach
              </ul>
            @endif
          </div>
        @endforeach
      @endif

      @if($sec==='certifications' && $has('certifications'))
        <div class="h2">Certifications</div>
        @foreach(($cv['certifications'] ?? []) as $c)
          <div class="item">
            <div class="itemTitle">{{ $c['name'] ?? '' }}</div>
            <div class="muted">{{ $c['issuer'] ?? '' }} @if(!empty($c['year'])) · {{ $c['year'] }} @endif</div>
            @if(!empty($c['link'])) <div class="link"><a href="{{ $c['link'] }}">{{ $c['link'] }}</a></div> @endif
          </div>
        @endforeach
      @endif

      @if($sec==='awards' && $has('awards'))
        <div class="h2">Awards</div>
        @foreach(($cv['awards'] ?? []) as $a)
          <div class="item">
            <div class="itemTitle">{{ $a['name'] ?? '' }}</div>
            <div class="muted">@if(!empty($a['year'])){{ $a['year'] }}@endif</div>
            @if(!empty($a['details'])) <div>{{ $a['details'] }}</div> @endif
          </div>
        @endforeach
      @endif

      @if($sec==='volunteering' && $has('volunteering'))
        <div class="h2">Volunteering</div>
        @foreach(($cv['volunteering'] ?? []) as $v)
          <div class="item">
            <div class="itemTitle">{{ $v['role'] ?? '' }}</div>
            <div class="muted">{{ $v['org'] ?? '' }} @if(!empty($v['year'])) · {{ $v['year'] }} @endif</div>
            @if(!empty($v['details'])) <div>{{ $v['details'] }}</div> @endif
          </div>
        @endforeach
      @endif

      @if($sec==='languages' && $has('languages'))
        <div class="h2">Languages</div>
        @foreach(($cv['languages'] ?? []) as $l)
          <div class="item">
            <div class="itemTitle">{{ $l['name'] ?? '' }}</div>
            @if(!empty($l['level'])) <div class="muted">{{ $l['level'] }}</div> @endif
          </div>
        @endforeach
      @endif

      @if($sec==='additional' && $has('additional'))
        <div class="h2">Additional</div>
        @foreach(($cv['additional'] ?? []) as $a)
          <div class="item">
            <div class="itemTitle">{{ $a['title'] ?? '' }}</div>
            @php
              $lines = [];
              $d = $a['details'] ?? '';
              if(is_array($d)) $lines = $d;
              else $lines = preg_split("/\r\n|\n|\r/", (string)$d);
              $lines = array_values(array_filter(array_map('trim', $lines), fn($x)=>$x!==''));
            @endphp
            @if(count($lines))
              <ul>
                @foreach($lines as $ln)
                  <li>{{ $ln }}</li>
                @endforeach
              </ul>
            @endif
          </div>
        @endforeach
      @endif
    @endforeach
  @endif
</body>
</html>
