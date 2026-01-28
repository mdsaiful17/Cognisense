@php
$theme = [
  'accent' => '#0f172a',
  'font' => 'DejaVu Sans',
  'layout' => 'single',
  'order' => ['summary','skills','experience','projects','education','certifications','awards','volunteering','languages','additional'],
];
@endphp
@include('cv.templates._base', ['cv'=>$cv, 'theme'=>$theme])
