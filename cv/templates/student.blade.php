@php
$theme = [
  'accent' => '#22c55e',
  'font' => 'DejaVu Sans',
  'layout' => 'single',
  'order' => ['summary','education','projects','skills','experience','certifications','awards','volunteering','languages','additional'],
];
@endphp
@include('cv.templates._base', ['cv'=>$cv, 'theme'=>$theme])
